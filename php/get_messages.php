<?php
session_start();
include 'db.php';

$current_user = $_SESSION['user_id'] ?? null;
$chat_with = $_GET['chat_with'] ?? null;

if (!$current_user || !$chat_with) {
    echo json_encode([]);
    exit;
}

$query = $conn->prepare("
  SELECT m.*, u.name AS sender_name
  FROM messages m
  JOIN users u ON u.id = m.sender_id
  WHERE (sender_id = ? AND receiver_id = ?)
     OR (sender_id = ? AND receiver_id = ?)
  ORDER BY timestamp ASC
");
$query->bind_param("iiii", $current_user, $chat_with, $chat_with, $current_user);
$query->execute();
$res = $query->get_result();

$messages = [];
while ($row = $res->fetch_assoc()) {
    $messages[] = $row;
}

header('Content-Type: application/json');
echo json_encode($messages);
