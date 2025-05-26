<?php
session_start();
include 'db.php';

$class_id = $_POST['class_id'];
$title = trim($_POST['title']);
$file = $_FILES['material_file'];

if (!$class_id || !$title || !$file) {
  die("Missing fields.");
}

$upload_dir = '../uploads/';
$filename = time() . "_" . basename($file['name']);
$target_path = $upload_dir . $filename;

if (move_uploaded_file($file['tmp_name'], $target_path)) {
  $stmt = $conn->prepare("INSERT INTO class_materials (class_id, title, file_name) VALUES (?, ?, ?)");
  $stmt->bind_param("iss", $class_id, $title, $filename);
  $stmt->execute();
}

header("Location: ../dashboard/class_page.php?class_id=$class_id&tab=materials");
exit;
