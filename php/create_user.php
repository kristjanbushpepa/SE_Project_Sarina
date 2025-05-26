<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check for duplicate email
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $_SESSION['error'] = "Email '$email' is already in use.";
        header("Location: ../dashboard/admin.php");
        exit();
    }

    // Insert main user
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $password, $role);
    $stmt->execute();
    $user_id = $stmt->insert_id;

    // ----------- PARENT LOGIC -------------
    if ($role === 'parent') {
        $parent_id = $user_id;

        // Optional: also store in `parents` table for legacy
        $stmt = $conn->prepare("INSERT INTO parents (username, password, full_name) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $email, $password, $name);
        $stmt->execute();

        // Create linked student if provided
        if (!empty($_POST['linked_student_email'])) {
            $sname = trim($_POST['linked_student_name']);
            $semail = trim($_POST['linked_student_email']);
            $spass = password_hash($_POST['linked_student_password'], PASSWORD_DEFAULT);
            $cnum = $_POST['linked_class_number'] ?? null;
            $clet = $_POST['linked_class_letter'] ?? null;

            // Check student email
            $checkS = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $checkS->bind_param("s", $semail);
            $checkS->execute();
            if ($checkS->get_result()->num_rows > 0) {
                $_SESSION['error'] = "Student email '$semail' already exists.";
                header("Location: ../dashboard/admin.php");
                exit();
            }

            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'student')");
            $stmt->bind_param("sss", $sname, $semail, $spass);
            $stmt->execute();
            $student_user_id = $stmt->insert_id;

            $stmt = $conn->prepare("INSERT INTO students (user_id, parent_id, class_number, class_letter) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiis", $student_user_id, $parent_id, $cnum, $clet);
            $stmt->execute();
        }

        $_SESSION['success'] = "Parent created successfully.";
        header("Location: ../dashboard/admin.php");
        exit();
    }

    // ----------- STUDENT LOGIC -------------
    if ($role === 'student') {
        $student_user_id = $user_id;
        $parent_id = null;

        // Link to existing parent
        if (!empty($_POST['existing_parent_id'])) {
            $parent_id = (int)$_POST['existing_parent_id'];
        }
        // Or create new parent
        elseif (!empty($_POST['new_parent_email'])) {
            $pname = trim($_POST['new_parent_name']);
            $pemail = trim($_POST['new_parent_email']);
            $ppass = password_hash($_POST['new_parent_password'], PASSWORD_DEFAULT);

            // Check parent email
            $checkP = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $checkP->bind_param("s", $pemail);
            $checkP->execute();
            if ($checkP->get_result()->num_rows > 0) {
                $_SESSION['error'] = "Parent email '$pemail' already exists.";
                header("Location: ../dashboard/admin.php");
                exit();
            }

            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'parent')");
            $stmt->bind_param("sss", $pname, $pemail, $ppass);
            $stmt->execute();
            $parent_id = $stmt->insert_id;

            // Optional legacy insert
            $stmt = $conn->prepare("INSERT INTO parents (username, password, full_name) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $pemail, $ppass, $pname);
            $stmt->execute();
        }

        $cnum = $_POST['student_class_number'] ?? null;
        $clet = $_POST['student_class_letter'] ?? null;

        // Link student
        $stmt = $conn->prepare("INSERT INTO students (user_id, parent_id, class_number, class_letter) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $student_user_id, $parent_id, $cnum, $clet);
        $stmt->execute();

        $_SESSION['success'] = "Student created successfully.";
        header("Location: ../dashboard/admin.php");
        exit();
    }

    // Admin/Teacher fallback
    $_SESSION['success'] = ucfirst($role) . " created.";
    header("Location: ../dashboard/admin.php");
    exit();
}
?>
