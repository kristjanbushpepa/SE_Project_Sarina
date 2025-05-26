<?php
session_start();
include 'db.php';

// Get the data from the login form
$email = $_POST['email'];
$password = $_POST['password'];
$role = $_POST['role'];

// Search for user with matching email, password, and role
$sql = "SELECT * FROM users WHERE email=? AND role=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $email, $role);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    if ($row['password'] == $password) {
        // ✅ SET SESSION VARIABLES AFTER FETCHING USER
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['user_role'] = $row['role'];
        $_SESSION['user_name'] = $row['name'];

        // Redirect based on role
        if ($role == 'student') {
            header("Location: ../dashboard/student.php");
        } elseif ($role == 'teacher') {
            header("Location: ../dashboard/teacher.php");
        } elseif ($role == 'parent') {
            header("Location: ../dashboard/parent.php");
        }
        exit();
    } else {
        echo "Wrong password.";
    }
} else {
    echo "User not found.";
}

$conn->close();
?>
