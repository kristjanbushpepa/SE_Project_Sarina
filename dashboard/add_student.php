<?php
session_start();
include '../php/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    die("Access denied.");
}

$class_id = $_GET['class_id'] ?? null;
if (!$class_id) die("Missing class ID.");

$students = $conn->query("
  SELECT u.id, u.name 
  FROM users u
  JOIN students s ON s.user_id = u.id
  WHERE u.role = 'student' AND u.id NOT IN (
    SELECT student_id FROM class_students WHERE class_id = $class_id
  )
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Student to Class</title>
  <link rel="stylesheet" href="../css/styles.css">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f5f5f5;
      color: #333;
      margin: 0;
      padding: 30px;
    }

    .container {
      max-width: 600px;
      margin: auto;
      background: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
      position: relative;
    }

    h2 {
      text-align: center;
      color: #007bff;
      margin-bottom: 30px;
    }

    form {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    select, button {
      padding: 10px;
      font-size: 16px;
      border: 1px solid #ccc;
      border-radius: 6px;
    }

    button {
      background-color: #007bff;
      color: white;
      cursor: pointer;
    }

    button:hover {
      background-color: #0056b3;
    }

    .settings-wrapper {
      position: absolute;
      top: 20px;
      right: 20px;
    }

    .settings-button {
      background: none;
      border: none;
      font-size: 18px;
      cursor: pointer;
    }

    .settings-dropdown {
      display: none;
      position: absolute;
      right: 0;
      top: 35px;
      background: white;
      border: 1px solid #ccc;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      min-width: 140px;
      padding: 4px 0;
      font-size: 14px;
      z-index: 999;
    }

    .settings-dropdown a, .settings-dropdown button {
      display: block;
      width: 100%;
      text-align: left;
      padding: 8px 12px;
      background: none;
      border: none;
      cursor: pointer;
      color: #333;
      text-decoration: none;
    }

    .settings-dropdown a:hover,
    .settings-dropdown button:hover {
      background-color: #f0f0f0;
    }

    /* Dark mode support */
    body.dark-mode {
      background-color: #121212;
      color: #e0e0e0;
    }

    .dark-mode .container {
      background-color: #1e1e1e;
      border-color: #333;
    }

    .dark-mode select, .dark-mode button {
      background-color: #2a2a2a;
      color: white;
      border-color: #555;
    }

    .dark-mode .settings-dropdown {
      background-color: #2a2a2a;
      border-color: #444;
    }

    .dark-mode .settings-dropdown a,
    .dark-mode .settings-dropdown button {
      color: #f1f1f1;
    }

    .dark-mode .settings-dropdown a:hover,
    .dark-mode .settings-dropdown button:hover {
      background-color: #3a3a3a;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="settings-wrapper">
      <button onclick="toggleSettings()" class="settings-button">⚙️</button>
      <div id="settingsDropdown" class="settings-dropdown">
        <a href="teacher.php">🏠 Dashboard</a>
        <button onclick="toggleDarkMode()">🌓 Dark Mode</button>
        <form method="POST" action="../php/logout.php">
          <button type="submit">🚪 Log Out</button>
        </form>
      </div>
    </div>

    <h2>Add Student to Class</h2>
    <form method="POST" action="../php/add_student_to_class.php">
      <input type="hidden" name="class_id" value="<?= $class_id ?>">
      <select name="student_id" required>
        <option value="">Select student</option>
        <?php while ($s = $students->fetch_assoc()): ?>
          <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
        <?php endwhile; ?>
      </select>
      <button type="submit">Add</button>
    </form>
  </div>

  <script>
    function toggleSettings() {
      const menu = document.getElementById("settingsDropdown");
      menu.style.display = (menu.style.display === "block") ? "none" : "block";
    }

    function toggleDarkMode() {
      document.body.classList.toggle("dark-mode");
      localStorage.setItem("theme", document.body.classList.contains("dark-mode") ? "dark" : "light");
    }

    document.addEventListener("click", function(e) {
      if (!e.target.closest(".settings-wrapper")) {
        document.getElementById("settingsDropdown").style.display = "none";
      }
    });

    window.addEventListener("DOMContentLoaded", () => {
      if (localStorage.getItem("theme") === "dark") {
        document.body.classList.add("dark-mode");
      }
    });
  </script>
</body>
</html>
