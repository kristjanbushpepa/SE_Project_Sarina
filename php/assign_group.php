<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'teacher') {
    die("Access denied.");
}

$class_id = $_POST['class_id'];
$class_number = $_POST['class_number'];
$class_letter = $_POST['class_letter'];

// Find all students matching class_number and class_letter
$sql = "SELECT students.user_id 
        FROM students 
        JOIN users ON students.user_id = users.id 
        WHERE students.class_number = ? AND students.class_letter = ? AND users.role = 'student'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $class_number, $class_letter);
$stmt->execute();
$result = $stmt->get_result();

// Assign each found student into class_students table
while ($row = $result->fetch_assoc()) {
    $student_id = $row['user_id'];

    // Check if already assigned (to avoid duplicates)
    $check = $conn->prepare("SELECT * FROM class_students WHERE class_id = ? AND student_id = ?");
    $check->bind_param("ii", $class_id, $student_id);
    $check->execute();
    $existing = $check->get_result();

    if ($existing->num_rows === 0) {
        $insert = $conn->prepare("INSERT INTO class_students (class_id, student_id) VALUES (?, ?)");
        $insert->bind_param("ii", $class_id, $student_id);
        $insert->execute();
    }
}

// Redirect back to the class page
header("Location: ../dashboard/class_page.php?class_id=" . $class_id);
exit();
?>
