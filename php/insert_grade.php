<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    die("Access denied.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'] ?? null;
    $class_id = $_POST['class_id'] ?? null;
    $semester = $_POST['semester'] ?? null;
    $participation = floatval($_POST['participation'] ?? 0);
    $project = floatval($_POST['project'] ?? 0);
    $exam = floatval($_POST['exam'] ?? 0);

    if (!$student_id || !$class_id || !$semester) {
        die("Missing required fields.");
    }

    // Grade limits check
    foreach ([$participation, $project, $exam] as $grade) {
        if ($grade < 4 || $grade > 10) {
            die("Error: All grades must be between 4 and 10.");
        }
    }

    // Check if grade already exists
    $check = $conn->prepare("SELECT id FROM grades WHERE student_id = ? AND class_id = ? AND semester = ?");
    $check->bind_param("iii", $student_id, $class_id, $semester);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        // Update existing grade
        $update = $conn->prepare("UPDATE grades SET participation = ?, project = ?, exam = ? WHERE student_id = ? AND class_id = ? AND semester = ?");
        $update->bind_param("dddiii", $participation, $project, $exam, $student_id, $class_id, $semester);
        $update->execute();
    } else {
        // Insert new grade
        $insert = $conn->prepare("INSERT INTO grades (student_id, class_id, semester, participation, project, exam) VALUES (?, ?, ?, ?, ?, ?)");
        $insert->bind_param("iiiddd", $student_id, $class_id, $semester, $participation, $project, $exam);
        $insert->execute();
    }

    // Redirect back to class page
    header("Location: ../dashboard/class_page.php?class_id=$class_id&tab=grades");
exit();

} else {
    echo "Invalid request.";
}
?>
