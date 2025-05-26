<?php
session_start();
include '../php/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die("Access denied.");
}

$student_id = $_POST['student_id'] ?? null;
$class_number = $_POST['class_number'] ?? null;
$class_letter = $_POST['class_letter'] ?? null;

if (!$student_id || !$class_number || !$class_letter) {
    $_SESSION['error'] = "All fields are required.";
    header("Location: assign_student.php");
    exit;
}

$stmt = $conn->prepare("UPDATE students SET class_number = ?, class_letter = ? WHERE id = ?");
$stmt->bind_param("isi", $class_number, $class_letter, $student_id);

if ($stmt->execute()) {
    $_SESSION['success'] = "Student assigned to class successfully.";
} else {
    $_SESSION['error'] = "Failed to assign student.";
}

header("Location: admin.php");
exit;
