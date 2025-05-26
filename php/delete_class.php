<?php
session_start();
include 'db.php';

// Check if teacher
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    die("Access denied.");
}

if (!isset($_POST['class_id'])) {
    die("Invalid request.");
}

$class_id = $_POST['class_id'];

// Verify that this class belongs to the logged-in teacher
$teacher_id = $_SESSION['user_id'];
$check = $conn->prepare("SELECT * FROM classes WHERE id = ? AND teacher_id = ?");
$check->bind_param("ii", $class_id, $teacher_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows === 0) {
    die("Something went wrong trying to delete this class.");
}

// Delete the class (CASCADE will delete related data)
$delete = $conn->prepare("DELETE FROM classes WHERE id = ?");
$delete->bind_param("i", $class_id);

if ($delete->execute()) {
    header("Location: /Sarina/dashboard/teacher.php?message=Class+Deleted");
    exit();
} else {
    echo "Error deleting class: " . $delete->error;
}
?>
