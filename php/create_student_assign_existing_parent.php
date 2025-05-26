<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sname = $_POST['student_name'];
    $parent_id = $_POST['parent_id'];

    // Create student user
    $dummy_email = "student_" . time() . "@school.local";
    $stmt1 = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, '', 'student')");
    $stmt1->bind_param("ss", $sname, $dummy_email);
    $stmt1->execute();
    $student_user_id = $conn->insert_id;

    // Create student record
    $stmt2 = $conn->prepare("INSERT INTO students (user_id, parent_id) VALUES (?, ?)");
    $stmt2->bind_param("ii", $student_user_id, $parent_id);
    $stmt2->execute();

    header("Location: ../dashboard/admin.php?success=student-added");
}
