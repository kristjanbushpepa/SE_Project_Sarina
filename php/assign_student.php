<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'teacher') {
    die("Access denied.");
}

$class_id = $_POST['class_id'];
$student_id = $_POST['student_id'];

// Check if already assigned
$check = $conn->prepare("SELECT * FROM class_students WHERE class_id = ? AND student_id = ?");
$check->bind_param("ii", $class_id, $student_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows === 0) {
    $stmt = $conn->prepare("INSERT INTO class_students (class_id, student_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $class_id, $student_id);
    $stmt->execute();
}

header("Location: ../dashboard/class_page.php?class_id=" . $class_id);
exit();
?>
