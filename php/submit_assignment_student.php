<?php
session_start();
include '../php/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    die("Unauthorized access.");
}

$student_id = $_SESSION['user_id'];
$assignment_id = $_POST['assignment_id'] ?? null;

if (!$assignment_id || !isset($_FILES['submission_file'])) {
    die("Missing assignment or file.");
}

// File upload config
$upload_dir = '../uploads/';
$original_name = basename($_FILES['submission_file']['name']);
$ext = pathinfo($original_name, PATHINFO_EXTENSION);
$allowed_extensions = ['pdf', 'doc', 'docx', 'zip'];

if (!in_array(strtolower($ext), $allowed_extensions)) {
    die("Invalid file type.");
}

$unique_name = 'submission_' . uniqid() . '.' . $ext;
$target_path = $upload_dir . $unique_name;

if (!move_uploaded_file($_FILES['submission_file']['tmp_name'], $target_path)) {
    die("File upload failed.");
}

// Check if student has already submitted
$check = $conn->prepare("SELECT id FROM submissions WHERE student_id = ? AND assignment_id = ?");
$check->bind_param("ii", $student_id, $assignment_id);
$check->execute();
$existing = $check->get_result()->fetch_assoc();

if ($existing) {
    // Update previous submission
    $stmt = $conn->prepare("UPDATE submissions SET file = ?, submitted_at = NOW() WHERE student_id = ? AND assignment_id = ?");
    $stmt->bind_param("sii", $unique_name, $student_id, $assignment_id);
} else {
    // Insert new submission
    $stmt = $conn->prepare("INSERT INTO submissions (student_id, assignment_id, file) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $student_id, $assignment_id, $unique_name);
}

if ($stmt->execute()) {
    header("Location: ../dashboard/student.php?tab=assignments&success=1");
    exit;
} else {
    die("Failed to save submission.");
}
?>
