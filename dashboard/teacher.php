<?php
session_start();
include '../php/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'teacher') {
    header("Location: ../login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];
$teacher_name = $_SESSION['user_name'] ?? 'Teacher';

// Fetch teacher's classes
$stmt = $conn->prepare("SELECT * FROM classes WHERE teacher_id = ?");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Teacher Dashboard</title>
  <link rel="stylesheet" href="../css/styles.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    /* Base */
body {
  font-family: 'Inter', sans-serif;
  background-color: #f9f9f9;
  color: #222;
  margin: 0;
  padding-top: 30px;
  transition: background-color 0.3s ease, color 0.3s ease;
}

/* Container */
.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 60px 20px;
}

/* Top Bar */
.top-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  
  margin-bottom: 30px;
}

/* Settings */
.settings-wrapper {
  position: relative;
  display: inline-block;
}

.settings-button {
  font-size: 18px;
  background: none;
  border: none;
  cursor: pointer;
  margin-left: 10px;
  filter: invert(0%);
  transition: filter 0.3s;
}


.dark-mode .settings-button {
  filter: invert(80%);
}

/* Dropdown */
.settings-dropdown {
  position: absolute;
  right: 0;
  top: 35px;
  background: #fff;
  border: 1px solid #ccc;
  border-radius: 10px;
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
  min-width: 180px;
  z-index: 999;
  display: none;
  padding: 8px 0;
}

.settings-dropdown ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.settings-dropdown li {
  margin: 0;
}

.settings-dropdown a,
.settings-dropdown button {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 14px;
  width: 100%;
  background: none;
  border: none;
  text-align: left;
  font-size: 14px;
  color: #333;
  text-decoration: none;
  cursor: pointer;
  border-radius: 6px;
  transition: background 0.2s;
}

.settings-dropdown a:hover,
.settings-dropdown button:hover {
  background-color: #f0f0f0;
}

/* Class Grid */
.class-container {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  margin-top: 30px;
}

.container h2 {
  padding-left: 10px ;
  color: rgb(14, 156, 227);
  font-weight: 1000;

}
.dark-mode h2 {
  color:rgb(255, 255, 255);
}
.class-card {
  background-color:rgb(14, 156, 227);
  color: white;
  padding: 20px;
  width: 200px; /* Fixed width for uniformity */
  height: 60px; /* Fixed height for consistency */
  text-align: center;
  border-radius: 10px;
  cursor: pointer;
  transition: 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}

.class-card:hover {
  background-color: #004080;
}


/* Create Button */
.create-btn {
  padding: 10px 20px;
  background-color: #28a745;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

.create-btn:hover {
  background-color: #1e7e34;
}

/* Dark Mode Theme */
body.dark-mode {
  background-color: #0e0e11;
  color: #e0e0e0;
}

/* Dropdown in Dark Mode */
.dark-mode .settings-dropdown {
  background-color: #1a1b1f;
  border-color: #2c2c33;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
}

.dark-mode .settings-dropdown a,
.dark-mode .settings-dropdown button {
  color: #f0f0f0;
}

.dark-mode .settings-dropdown a:hover,
.dark-mode .settings-dropdown button:hover {
  background-color: #2a2a30;
  color: #ffffff;
}

/* Class Cards in Dark Mode */
.dark-mode .class-card {
  background-color: #1b1c22;
  border: 1px solid #2a2b30;
  color: #f5f5f5;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
}

.dark-mode .class-card:hover {
  background-color: #292a30;
  transform: scale(1.02);
}

  </style>
  

</head>
<body>

<div class="container">
  <div class="top-bar">
    <h2>Welcome, <?= htmlspecialchars($teacher_name) ?>!</h2>

    <div class="settings-wrapper">
      <button onclick="toggleSettings()" class="settings-button">⚙️</button>
      <button onclick="toggleDarkMode()" class="settings-button" title="Toggle Dark Mode">🌓</button>
      <div id="settingsDropdown" class="settings-dropdown">
        <ul>
        <li><a href="create_class.php">➕ <span>Create Class</span></a></li>

          <li><form method="POST" action="../php/logout.php"><button type="submit">🚪 Log Out</button></form></li>
        </ul>
      </div>
    </div>
  </div>

  <div class="login-box">
    <div class="class-container">
      <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <?php
            $course = trim($row['course_name']);
            $groups = preg_replace('/\\s*\\+\\s*/', '+', trim($row['class_name']));
            $label = htmlspecialchars("$course $groups");
          ?>
          <div class="class-card" onclick="window.location.href='class_page.php?class_id=<?= $row['id'] ?>'">
            <?= $label ?>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p>You don't have any classes yet. Click "Create Class" to get started.</p>
      <?php endif; ?>
    </div>
  </div>

</div>

<script>
function toggleSettings() {
  const dropdown = document.getElementById("settingsDropdown");
  dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
}

document.addEventListener("click", function(e) {
  if (!e.target.closest(".settings-wrapper")) {
    document.getElementById("settingsDropdown").style.display = "none";
  }
});


function toggleDarkMode() {
  document.body.classList.toggle("dark-mode");
  localStorage.setItem("theme", document.body.classList.contains("dark-mode") ? "dark" : "light");
}



window.addEventListener("DOMContentLoaded", () => {
  if (localStorage.getItem("theme") === "dark") {
    document.body.classList.add("dark-mode");
  }
});
</script>
</body>
</html>
