<?php 
session_start();
include 'db.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Access denied. Not logged in.");
}

$sender_id = $_SESSION['user_id'];
$receiver_id = isset($_POST['receiver_id']) ? intval($_POST['receiver_id']) : 0;
$message = trim($_POST['message'] ?? '');
$tab = $_POST['tab'] ?? 'messages1';
$role = $_SESSION['user_role'] ?? 'student'; 
$class_id = $_POST['class_id'] ?? '';

// Validate inputs
if (!$receiver_id || $message === '') {
    die("Missing receiver or message content.");
}

// Sanitize message
$sanitized_message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

// Insert message into the database
$stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $sender_id, $receiver_id, $sanitized_message);
$stmt->execute();

// Redirect user back to appropriate dashboard
switch ($role) {
    case 'student':
        header("Location: ../dashboard/student.php?tab=$tab&chat_with=$receiver_id");
        break;
    case 'parent':
        header("Location: ../dashboard/parent.php?tab=$tab&chat_with=$receiver_id");
        break;
    default:
        // Assume teacher
        header("Location: ../dashboard/class_page.php?class_id=$class_id&tab=$tab&chat_with=$receiver_id");
        break;
}
exit;
