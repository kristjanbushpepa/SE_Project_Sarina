<?php
session_start();
include '../php/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'parent') {
    header("Location: ../login.php");
    exit();
}

$parent_id = $_SESSION['user_id'];
$parent_name = $_SESSION['user_name'] ?? 'Parent';
$selected_student_id = $_GET['student_id'] ?? null;

// Get children and user_id link
$stmt = $conn->prepare("
    SELECT students.id AS student_id, students.user_id AS user_id, users.name AS student_name
    FROM students
    JOIN users ON students.user_id = users.id
    WHERE students.parent_id = ?
");
$stmt->bind_param("i", $parent_id);
$stmt->execute();
$result = $stmt->get_result();

$children_list = [];
$student_user_id = null;

while ($child = $result->fetch_assoc()) {
    $children_list[] = $child;
    if ($selected_student_id == $child['student_id']) {
        $student_user_id = $child['user_id'];
    }
}

$grades = $attendance = $teachers = [];
$total_sessions = $total_present = 0;
$attendance_by_class = [];

if ($student_user_id) {
    $stmt = $conn->prepare("SELECT g.*, c.course_name, c.class_name FROM grades g JOIN classes c ON g.class_id = c.id WHERE g.student_id = ?");
    $stmt->bind_param("i", $student_user_id);
    $stmt->execute();
    $grades_result = $stmt->get_result();

    // Group grades by class
    $grades_by_class = [];
    while ($grade = $grades_result->fetch_assoc()) {
        $label = $grade['course_name'] . ' ' . $grade['class_name'];
        $grades_by_class[$label][] = $grade;
    }

    $stmt = $conn->prepare("SELECT a.*, c.course_name, c.class_name FROM attendance a JOIN classes c ON a.class_id = c.id WHERE a.student_id = ?");
    $stmt->bind_param("i", $student_user_id);
    $stmt->execute();
    $attendance_result = $stmt->get_result();

    while ($row = $attendance_result->fetch_assoc()) {
        $label = $row['course_name'] . ' ' . $row['class_name'];
        $attendance_by_class[$label]['total'] = ($attendance_by_class[$label]['total'] ?? 0) + 1;
        if ($row['status'] === 'present') {
            $attendance_by_class[$label]['present'] = ($attendance_by_class[$label]['present'] ?? 0) + 1;
        }
    }

    foreach ($attendance_by_class as $entry) {
        $total_sessions += $entry['total'];
        $total_present += $entry['present'];
    }

    $stmt = $conn->prepare("
        SELECT DISTINCT u.id, u.name AS teacher_name
        FROM users u
        JOIN classes c ON c.teacher_id = u.id
        JOIN class_students cs ON cs.class_id = c.id
        WHERE cs.student_id = ? AND u.role = 'teacher'
    ");
    $stmt->bind_param("i", $student_user_id);
    $stmt->execute();
    $teachers = $stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Parent Dashboard</title>
  <link rel="stylesheet" href="../css/styles.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body { margin: 0; padding: 20px; font-family: 'Segoe UI', sans-serif; background: #f9f9f9; color: #333; }
    .container { max-width: 960px; margin: auto; background: #fff; border-radius: 10px; padding: 30px; box-shadow: 0 0 15px rgba(0,0,0,0.05); }
    .logo { display: block; margin: 0 auto 20px; max-height: 60px; }
  
    form { text-align: center; }
    select { padding: 10px; max-width: 300px; margin: 0 auto 20px; font-size: 16px; border: 1px solid #ccc; border-radius: 6px; }
    .card { background: #f0f4f8; padding: 10px 15px; border-radius: 8px; margin: 6px 0; box-shadow: 0 2px 4px rgba(0,0,0,0.05); text-align: center; }
    .message-form textarea { width: 100%; padding: 8px; margin-top: 8px; border-radius: 5px; border: 1px solid #ccc; resize: vertical; }
    .message-form button { margin-top: 10px; padding: 8px 14px; background: #007bff; color: white; border: none; border-radius: 6px; cursor: pointer; }
    
    .tab-menu { display: flex; justify-content: center; gap: 10px; margin-bottom: 20px; }
    .tab-button { padding: 10px 20px; cursor: pointer; border: none; background: #ccc; border-radius: 6px; }
 
    .tab-section { display: none; }
    .tab-section.active { display: block; }
    h4::before { display: none; }
    body.dark-mode {
    background-color: #121212;
    color: #e0e0e0;
  }

  body.dark-mode .container,
  body.dark-mode .card,
  body.dark-mode select,
  body.dark-mode textarea,
  body.dark-mode input {
    background-color: #1e1e1e;
    color: #e0e0e0;
    border-color: #444;
  }

  body.dark-mode .tab-button {
    background-color: #333;
    color: #fff;
  }

  body.dark-mode .tab-button.active {
    background-color: #007bff;
    color: #fff;
  }

  body.dark-mode .message-form button {
    background-color: #444;
  }
  body.dark-mode .logo {
  filter: invert(1) hue-rotate(180deg);
}

.container {
  position: relative; /* IMPORTANT */
  max-width: 960px;
  margin: auto;
  background: #fff;
  border-radius: 10px;
  padding: 30px;
  box-shadow: 0 0 15px rgba(0,0,0,0.05);
}

.settings-wrapper {
  position: absolute;
  top: 20px;
  right: 20px;
  z-index: 10;
}

.settings-button {
  background: none;
  border: none;
  font-size: 20px;
  cursor: pointer;
  color: inherit;
}

/* Dropdown Styles (from previous message, or adjust) */
.settings-dropdown {
  display: none;
  position: absolute;
  top: 35px;
  right: 0;
  background-color: white;
  border: 1px solid #ccc;
  border-radius: 8px;
  box-shadow: 0 8px 16px rgba(0,0,0,0.1);
  min-width: 160px;
}

/* Dark mode support (optional) */
body.dark-mode .settings-dropdown {
  background-color: #1e1e1e;
  border-color: #333;
}

body.dark-mode .settings-dropdown a,
body.dark-mode .logout-button {
  color: #eee;
}


  </style>
  <script>
    function showTab(tabId) {
      document.querySelectorAll('.tab-section').forEach(tab => tab.classList.remove('active'));
      document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
      document.getElementById(tabId).classList.add('active');
      document.querySelector('[data-tab="' + tabId + '"]').classList.add('active');
    }

function toggleDarkMode() {
  document.body.classList.toggle('dark-mode');
  localStorage.setItem('theme', document.body.classList.contains('dark-mode') ? 'dark' : 'light');
}

// Load dark mode on page load
window.addEventListener('DOMContentLoaded', () => {
  if (localStorage.getItem('theme') === 'dark') {
    document.body.classList.add('dark-mode');
  }
});

function toggleSettings() {
  const dropdown = document.getElementById("settingsDropdown");
  dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
}

document.addEventListener("click", function (e) {
  if (!e.target.closest(".settings-wrapper")) {
    document.getElementById("settingsDropdown").style.display = "none";
  }
});

  </script>
</head>
<body>
  <div class="container">
  <div class="settings-wrapper">
    <button onclick="toggleSettings()" class="settings-button">⚙️</button>
    <div id="settingsDropdown" class="settings-dropdown">
      <ul>
        <li><a href="#" onclick="toggleDarkMode()">🌓 Toggle Dark Mode</a></li>
        <li>
          <form action="../php/logout.php" method="POST" style="margin:0;">
            <button type="submit" class="logout-button">🚪 Log Out</button>
          </form>
        </li>
      </ul>
    </div>
  </div>

  <!-- rest of your content -->

    <img src="../images/logo.png" alt="Logo" class="logo">
    

    <h2>Welcome, <?= htmlspecialchars($parent_name) ?>!</h2>

    <div class="section">
      <h3>Select a Child</h3>
      <form method="GET" action="">
        <select name="student_id" onchange="this.form.submit()" required>
          <option value="">-- Select a student --</option>
          <?php foreach ($children_list as $child): ?>
            <option value="<?= $child['student_id'] ?>" <?= $selected_student_id == $child['student_id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($child['student_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </form>
    </div>

    <?php if ($student_user_id): ?>
      <div class="tab-menu">
        <button class="tab-button active" data-tab="grades" onclick="showTab('grades')">Grades</button>
        <button class="tab-button" data-tab="attendance" onclick="showTab('attendance')">Attendance</button>
        <button class="tab-button" data-tab="messages" onclick="showTab('messages')">Messages</button>
      </div>

      <div class="tab-section active" id="grades">
        <h3>Grades</h3>
        <?php if (!empty($grades_by_class)): ?>
          <?php foreach ($grades_by_class as $class_label => $class_grades): ?>
            <h4><?= htmlspecialchars($class_label) ?></h4>
            <?php foreach ($class_grades as $grade): ?>
              <div class="card">
                Semester <?= $grade['semester'] ?>: Participation <?= $grade['participation'] ?>, Exam <?= $grade['exam'] ?>, Project <?= $grade['project'] ?>
              </div>
            <?php endforeach; ?>
          <?php endforeach; ?>
        <?php else: ?>
          <p style="text-align:center; color:#777;">No grades available.</p>
        <?php endif; ?>
      </div>

      <div class="tab-section" id="attendance">
        <h3>Attendance</h3>
        <?php if (!empty($attendance_by_class)): ?>
          <?php foreach ($attendance_by_class as $label => $data): ?>
            <div class="card">
              <?= htmlspecialchars($label) ?> – Attendance: <?= round($data['present'] / $data['total'] * 100) ?>%
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p style="text-align:center; color:#777;">No attendance records found.</p>
        <?php endif; ?>
      </div>

      <div class="tab-section" id="messages">
        <h3>Message Teachers</h3>
        <?php if ($teachers->num_rows > 0): ?>
          <?php while ($teacher = $teachers->fetch_assoc()): ?>
            <div class="card">
              <strong><?= htmlspecialchars($teacher['teacher_name']) ?></strong>
              <form method="POST" action="../php/send_message.php" class="message-form">
                <input type="hidden" name="teacher_id" value="<?= $teacher['id'] ?>">
                <input type="hidden" name="student_id" value="<?= $student_user_id ?>">
                <textarea name="message" placeholder="Write your message..." required></textarea>
                <button type="submit">Send</button>
              </form>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <p style="text-align:center; color:#777;">No teachers linked to this student.</p>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>