<?php
session_start();
include '../php/db.php';

if ($_SESSION['user_role'] !== 'admin') {
    die("Access denied.");
}

$title = $_POST['title'];
$description = $_POST['description'];
$image_path = null;

if (!empty($_FILES['image']['tmp_name'])) {
    $upload_dir = "../uploads/";
    $filename = time() . "_" . basename($_FILES['image']['name']);
    $target = $upload_dir . $filename;
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        $image_path = $target;
    }
}

$stmt = $conn->prepare("INSERT INTO events (title, description, image_path) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $title, $description, $image_path);
$stmt->execute();

header("Location: ../dashboard/admin.php");
exit;
