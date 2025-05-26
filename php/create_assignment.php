<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_id = $_POST['class_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $due_date = $_POST['due_date'];
    $filename = '';

    if (!empty($_FILES['attachment']['name'])) {
        $upload_dir = '../uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        $filename = uniqid() . '_' . basename($_FILES['attachment']['name']);
        $filepath = $upload_dir . $filename;

        if (!move_uploaded_file($_FILES['attachment']['tmp_name'], $filepath)) {
            die("Failed to upload file.");
        }
    }

    $stmt = $conn->prepare("INSERT INTO assignments (class_id, title, description, due_date, attachment) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $class_id, $title, $description, $due_date, $filename);
    $stmt->execute();

    header("Location: ../dashboard/class_page.php?class_id=$class_id&tab=assignments");
    exit();
}
?>
