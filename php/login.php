<?php
// Start session to store user data
session_start();

// Connect to the database
include 'db.php';

// Get the data from the login form
$email = $_POST['email'];
$password = $_POST['password'];
$role = $_POST['role'];

// Search for user with matching email, password, and role
$sql = "SELECT * FROM users WHERE email='$email' AND role='$role'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // User found
    $row = $result->fetch_assoc();

    // Check password (we assume password is plain text for now)
    if ($row['password'] == $password) {
        // Login successful
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['user_role'] = $row['role'];

        // Redirect based on role
        if ($role == 'student') {
            header("Location: ../dashboard/student.html");
        } elseif ($role == 'teacher') {
            header("Location: ../dashboard/teacher.html");
        } elseif ($role == 'parent') {
            header("Location: ../dashboard/parent.html");
        }
        exit();
    } else {
        echo "Wrong password.";
    }
} else {
    echo "User not found.";
}

// Close connection
$conn->close();
?>
