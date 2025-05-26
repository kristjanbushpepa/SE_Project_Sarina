<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    die("Access denied.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assignment_id = $_POST['assignment_id'] ?? null;
    $class_id = $_POST['class_id'] ?? null;

    if (!$assignment_id || !$class_id) {
        die("Missing data.");
    }

    // Optional: Get the file name first to delete it from disk
    $file_query = $conn->prepare("SELECT attachment FROM assignments WHERE id = ?");
    $file_query->bind_param("i", $assignment_id);
    $file_query->execute();
    $file_result = $file_query->get_result()->fetch_assoc();

    if ($file_result && $file_result['attachment']) {
        $file_path = '../uploads/' . $file_result['attachment'];
        if (file_exists($file_path)) {
            unlink($file_path); // delete file
        }
    }

    // Delete the assignment from DB
    $delete = $conn->prepare("DELETE FROM assignments WHERE id = ?");
    $delete->bind_param("i", $assignment_id);
    $delete->execute();

    header("Location: ../dashboard/class_page.php?class_id=$class_id&tab=assignments");
    exit();
} else {
    echo "Invalid request.";
}
?>
