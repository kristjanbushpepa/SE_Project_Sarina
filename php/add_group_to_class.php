<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    die("Access denied.");
}

$class_id = $_POST['class_id'];
$class_number = $_POST['class_number'];
$class_letter = $_POST['class_letter'];

// Get student IDs from group
$stmt = $conn->prepare("
  SELECT user_id FROM students 
  WHERE class_number = ? AND class_letter = ?
");
$stmt->bind_param("is", $class_number, $class_letter);
$stmt->execute();
$students = $stmt->get_result();

// Insert each into class_students
$insert = $conn->prepare("INSERT IGNORE INTO class_students (student_id, class_id) VALUES (?, ?)");
while ($s = $students->fetch_assoc()) {
    $insert->bind_param("ii", $s['user_id'], $class_id);
    $insert->execute();
}

header("Location: ../dashboard/class_page.php?class_id=$class_id&tab=students");
