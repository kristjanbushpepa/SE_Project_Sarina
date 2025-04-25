<?php
// Database connection settings
$servername = "localhost";  // Your server (localhost if on your computer)
$username = "root";          // Your MySQL username (default: root)
$password = "";              // Your MySQL password (default: empty)
$database = "sarina_highschool"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
