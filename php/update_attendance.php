<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    die("Access denied.");
}

$class_id = $_POST['class_id'] ?? null;
$attendance = $_POST['attendance'] ?? [];
$today = date('Y-m-d');

if (!$class_id) die("Missing class ID");

// Get all students assigned to the class
$students_stmt = $conn->prepare("SELECT student_id FROM class_students WHERE class_id = ?");
$students_stmt->bind_param("i", $class_id);
$students_stmt->execute();
$students_result = $students_stmt->get_result();

while ($s = $students_result->fetch_assoc()) {
    $student_id = $s['student_id'];

    for ($session = 1; $session <= 10; $session++) { // assuming up to 10 possible sessions
        if (!isset($attendance[$student_id][$session]) && attendanceExists($conn, $student_id, $class_id, $today, $session)) {
            updateAttendance($conn, $student_id, $class_id, $today, $session, 'absent');
        } elseif (isset($attendance[$student_id][$session])) {
            updateAttendance($conn, $student_id, $class_id, $today, $session, 'present');
        }
    }
}

function attendanceExists($conn, $student_id, $class_id, $date, $session_number) {
    $stmt = $conn->prepare("SELECT id FROM attendance WHERE student_id = ? AND class_id = ? AND date = ? AND session_number = ?");
    $stmt->bind_param("iisi", $student_id, $class_id, $date, $session_number);
    $stmt->execute();
    $res = $stmt->get_result();
    return $res->num_rows > 0;
}

function updateAttendance($conn, $student_id, $class_id, $date, $session_number, $status) {
    if (attendanceExists($conn, $student_id, $class_id, $date, $session_number)) {
        $update = $conn->prepare("UPDATE attendance SET status = ? WHERE student_id = ? AND class_id = ? AND date = ? AND session_number = ?");
        $update->bind_param("siisi", $status, $student_id, $class_id, $date, $session_number);
        $update->execute();
    } else {
        $insert = $conn->prepare("INSERT INTO attendance (student_id, class_id, date, session_number, status) VALUES (?, ?, ?, ?, ?)");
        $insert->bind_param("iisis", $student_id, $class_id, $date, $session_number, $status);
        $insert->execute();
    }
}

header("Location: ../dashboard/class_page.php?class_id=" . $class_id);
exit;
?>
