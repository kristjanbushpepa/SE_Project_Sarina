<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    die("Access denied.");
}

$class_id = $_POST['class_id'];
$student_id = $_POST['student_id'];

$stmt = $conn->prepare("INSERT IGNORE INTO class_students (student_id, class_id) VALUES (?, ?)");
$stmt->bind_param("ii", $student_id, $class_id);
$stmt->execute();

header("Location: ../dashboard/class_page.php?class_id=$class_id&tab=students");
