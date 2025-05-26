<?php
session_start();
include '../php/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    die("Access denied.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_name = trim($_POST['course_name']);
    $weekly_lessons = intval($_POST['weekly_lessons']);
    $groups = $_POST['groups'] ?? [];
    $teacher_id = $_SESSION['user_id'];

    if (empty($course_name) || empty($groups)) {
        echo "<script>alert('Please fill all required fields.');</script>";
    } else {
        // Class name like "11A+11B"
        $class_name = implode('+', array_map('strtoupper', $groups));

        // Prevent duplicates
        $check = $conn->prepare("SELECT id FROM classes WHERE class_name = ? AND course_name = ?");
        $check->bind_param("ss", $class_name, $course_name);
        $check->execute();
        $exists = $check->get_result();

        if ($exists->num_rows > 0) {
            echo "<script>alert('Class already exists!'); window.location.href='teacher.php';</script>";
            exit();
        }

        // Insert class
        $stmt = $conn->prepare("INSERT INTO classes (class_name, course_name, teacher_id, weekly_lessons) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssii", $class_name, $course_name, $teacher_id, $weekly_lessons);
        $stmt->execute();
        $class_id = $stmt->insert_id;

        // Auto-assign students from selected groups
        foreach ($groups as $group) {
            if (preg_match('/(\d+)([A-C])/i', $group, $matches)) {
                $number = intval($matches[1]);
                $letter = strtoupper($matches[2]);

                $query = $conn->prepare("SELECT user_id FROM students WHERE class_number = ? AND class_letter = ?");
                $query->bind_param("is", $number, $letter);
                $query->execute();
                $res = $query->get_result();

                $insert = $conn->prepare("INSERT INTO class_students (student_id, class_id) VALUES (?, ?)");
                while ($row = $res->fetch_assoc()) {
                    $insert->bind_param("ii", $row['user_id'], $class_id);
                    $insert->execute();
                }
            }
        }

        header("Location: teacher.php?message=Class+Created");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create Class</title>
  <link rel="stylesheet" href="../css/styles.css">
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>
<body>
<div class="container">
  <h2>Create a New Class</h2>
  <form method="POST" action="">

    <label>Course Name:</label><br>
    <input type="text" name="course_name" required><br>

    <label>Select Class Groups (e.g. 11A, 11B):</label><br>
    <select name="groups[]" multiple required>
      <?php
        for ($i = 1; $i <= 12; $i++) {
          foreach (['A', 'B', 'C'] as $letter) {
            $val = "$i$letter";
            echo "<option value='$val'>$val</option>";
          }
        }
      ?>
    </select>
    <small>Hold CTRL (or ⌘ on Mac) to select multiple.</small><br><br>

    <label>Number of Weekly Lessons:</label><br>
    <select name="weekly_lessons" required>
      <?php for ($i = 1; $i <= 7; $i++): ?>
        <option value="<?= $i ?>"><?= $i ?> time<?= $i > 1 ? 's' : '' ?> per week</option>
      <?php endfor; ?>
    </select><br><br>

    <button type="submit">Create Class</button>
  </form>

  <br><a href="teacher.php">← Back to Dashboard</a>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
  $(document).ready(function() {
    $("select[name='groups[]']").select2({ placeholder: "Select group(s)", width: '100%' });
  });
</script>
</body>
</html>
