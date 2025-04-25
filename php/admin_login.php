<?php
session_start();
include 'db.php';

$email = $_POST['email'];
$password = $_POST['password'];

// Search for admin user
$sql = "SELECT * FROM users WHERE email='$email' AND role='admin'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    if ($row['password'] == $password) {
        $_SESSION['admin_id'] = $row['id'];
        header("Location: ../dashboard/admin.html");
        exit();
    } else {
        echo "Wrong password.";
    }
} else {
    echo "Admin not found.";
}

$conn->close();
?>
