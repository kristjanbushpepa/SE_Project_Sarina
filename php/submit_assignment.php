<?php
session_start();
include '../php/db.php';

if ($_SESSION['user_role'] !== 'student') die("Access denied");

$assignment_id = $_POST['assignment_id'];
$student_id = $_SESSION['user_id'];
$content = $_POST['content'];

$check = $conn->prepare("SELECT id FROM submissions WHERE assignment_id = ? AND student_id = ?");
$check->bind_param("ii", $assignment_id, $student_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    $update = $conn->prepare("UPDATE submissions SET content = ?, submitted_at = NOW() WHERE assignment_id = ? AND student_id = ?");
    $update->bind_param("sii", $content, $assignment_id, $student_id);
    $update->execute();
} else {
    $insert = $conn->prepare("INSERT INTO submissions (assignment_id, student_id, content) VALUES (?, ?, ?)");
    $insert->bind_param("iis", $assignment_id, $student_id, $content);
    $insert->execute();
}

header("Location: student.php?tab=assignments");
?>
