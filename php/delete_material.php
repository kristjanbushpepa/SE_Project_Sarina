<?php
session_start();
include 'db.php';

$material_id = $_POST['material_id'];
$class_id = $_POST['class_id'];
$tab = $_POST['tab'];

$stmt = $conn->prepare("SELECT file_name FROM class_materials WHERE id = ?");
$stmt->bind_param("i", $material_id);
$stmt->execute();
$file = $stmt->get_result()->fetch_assoc();

if ($file && file_exists("../uploads/" . $file['file_name'])) {
  unlink("../uploads/" . $file['file_name']);
}

$delete = $conn->prepare("DELETE FROM class_materials WHERE id = ?");
$delete->bind_param("i", $material_id);
$delete->execute();

header("Location: ../dashboard/class_page.php?class_id=$class_id&tab=$tab");
exit;
