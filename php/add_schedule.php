<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die("Access denied.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_number = $_POST['class_number'];
    $class_letter = strtoupper($_POST['class_letter']);
    $day_of_week = $_POST['day_of_week'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $course_name = $_POST['course_name'];

    $stmt = $conn->prepare("INSERT INTO schedule (class_number, class_letter, day_of_week, start_time, end_time, course_name)
                            VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $class_number, $class_letter, $day_of_week, $start_time, $end_time, $course_name);
    $stmt->execute();

    header("Location: ../dashboard/admin.php?message=Schedule+Added");
    exit();
}
