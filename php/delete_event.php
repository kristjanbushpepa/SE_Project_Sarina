<?php
session_start();
include 'db.php';

// Make sure only admin can delete
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die("Access denied.");
}

$event_id = $_POST['id'] ?? null;

if (!$event_id) {
    die("Invalid event ID.");
}

// Optional: delete the image file too
$image_query = $conn->prepare("SELECT image_path FROM events WHERE id = ?");
$image_query->bind_param("i", $event_id);
$image_query->execute();
$image_result = $image_query->get_result();
if ($row = $image_result->fetch_assoc()) {
    $image_path = $row['image_path'];
    if ($image_path && file_exists($image_path)) {
        unlink($image_path);
    }
}

// Delete event from database
$stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();

// Redirect back to admin panel
header("Location: ../dashboard/admin.php");
exit;
