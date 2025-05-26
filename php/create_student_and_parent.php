<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pname = $_POST['parent_name'];
    $pemail = $_POST['parent_email'];
    $ppass = password_hash($_POST['parent_password'], PASSWORD_DEFAULT);
    $sname = $_POST['student_name'];

    // Create user for parent
    $stmt1 = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'parent')");
    $stmt1->bind_param("sss", $pname, $pemail, $ppass);
    $stmt1->execute();
    $parent_user_id = $conn->insert_id;

    // Create parent profile
    $stmt2 = $conn->prepare("INSERT INTO parents (username, password, full_name) VALUES (?, ?, ?)");
    $stmt2->bind_param("sss", $pemail, $ppass, $pname);
    $stmt2->execute();
    $parent_id = $conn->insert_id;

    // Create user for student
    $dummy_email = "student_" . time() . "@school.local";
    $stmt3 = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, '', 'student')");
    $stmt3->bind_param("ss", $sname, $dummy_email);
    $stmt3->execute();
    $student_user_id = $conn->insert_id;

    // Create student record
    $stmt4 = $conn->prepare("INSERT INTO students (user_id, parent_id) VALUES (?, ?)");
    $stmt4->bind_param("ii", $student_user_id, $parent_id);
    $stmt4->execute();

    header("Location: ../dashboard/admin.php?success=student-parent-created");
}
