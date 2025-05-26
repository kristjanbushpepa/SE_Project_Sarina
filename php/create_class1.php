<?php
session_start();
include 'db.php';

// Ensure user is a logged-in teacher
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    die("Access denied.");
}

$teacher_id = $_SESSION['user_id'];

// Validate and sanitize input
$course_name = isset($_POST['course_name']) ? trim($_POST['course_name']) : '';
$weekly_lessons = isset($_POST['weekly_lessons']) ? intval($_POST['weekly_lessons']) : 0;
$selected_groups = isset($_POST['groups']) ? $_POST['groups'] : [];

if (empty($course_name) || $weekly_lessons <= 0 || empty($selected_groups)) {
    echo "<script>alert('All fields are required.'); window.location.href='../dashboard/create_class.php';</script>";
    exit();
}

// Format class name (e.g., "11A+11B")
$class_name = implode('+', array_map('strtoupper', $selected_groups));

// Check if class already exists
$check = $conn->prepare("SELECT id FROM classes WHERE course_name = ? AND class_name = ?");
$check->bind_param("ss", $course_name, $class_name);
$check->execute();
$check_result = $check->get_result();

if ($check_result->num_rows > 0) {
    echo "<script>alert('Class already exists!'); window.location.href='../dashboard/create_class.php';</script>";
    exit();
}

// Insert class
$insert = $conn->prepare("INSERT INTO classes (class_name, teacher_id, course_name, weekly_lessons) VALUES (?, ?, ?, ?)");
$insert->bind_param("sssi", $class_name, $teacher_id, $course_name, $weekly_lessons);
if (!$insert->execute()) {
    die("Error inserting class: " . $conn->error);
}

$class_id = $conn->insert_id;

// Prepare statement to assign students to class
$assign = $conn->prepare("INSERT INTO class_students (student_id, class_id) VALUES (?, ?)");

// Loop through selected groups (e.g., '11A', '10B')
foreach ($selected_groups as $group) {
    if (preg_match('/(\d+)([A-C])/', strtoupper($group), $matches)) {
        $number = intval($matches[1]);
        $letter = strtoupper($matches[2]);

        // Get student IDs from students table
        $student_query = $conn->prepare("SELECT user_id FROM students WHERE class_number = ? AND class_letter = ?");
        $student_query->bind_param("is", $number, $letter);
        $student_query->execute();
        $students = $student_query->get_result();

        while ($s = $students->fetch_assoc()) {
            $student_id = $s['user_id'];

            // Avoid duplicate entries
            $check_assign = $conn->prepare("SELECT id FROM class_students WHERE student_id = ? AND class_id = ?");
            $check_assign->bind_param("ii", $student_id, $class_id);
            $check_assign->execute();
            $exists = $check_assign->get_result();

            if ($exists->num_rows === 0) {
                $assign->bind_param("ii", $student_id, $class_id);
                $assign->execute();
            }
        }
    }
}

header("Location: /Sarina/dashboard/class_page.php?class_id=" . $class_id);
exit();
?>
