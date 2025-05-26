<?php
session_start();
include '../php/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    die("Access denied.");
}

$class_id = $_GET['class_id'] ?? null;
if (!$class_id) die("Missing class ID.");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Group to Class</title>
  <link rel="stylesheet" href="../css/styles.css"> <!-- Use your global stylesheet -->
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f9f9f9;
      margin: 0;
      padding: 20px;
    }

    .container {
      max-width: 600px;
      margin: auto;
      background: #fff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
      position: relative;
    }

    h2 {
      text-align: center;
      color: #007bff;
      margin-bottom: 20px;
    }

    label {
      display: block;
      margin-top: 15px;
      font-weight: bold;
    }
    .dark-mode label {
      display: block;
      margin-top: 15px;
      font-weight: bold;
      color: #ffffff;
    }
    select {
      width: 100%;
      padding: 10px;
      font-size: 16px;
      margin-top: 5px;
      border-radius: 6px;
      border: 1px solid #ccc;
    }
    button {
  margin-top: 20px;
  padding: 10px 20px;
  background: rgb(14, 156, 227); /* Unified blue */
  border: none;
  color: white;
  border-radius: 6px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

button:hover {
  background: #005fa3; /* Slightly darker for hover */
}

/* Settings Button */
.settings-wrapper {
  position: absolute;
  top: 20px;
  right: 20px;
  z-index: 1000;
}

.settings-button {
  font-size: 18px;
  background: none;
  border: none;
  cursor: pointer;
  padding: 4px 8px;
  color: inherit;
}

/* Dropdown Menu */

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
    .dark-mode .label{
      color: white;
    }   
    
    </style>
</head>
<body>
  <div class="container">
    <div class="settings-wrapper">
      <button onclick="toggleSettings()" class="settings-button">⚙️</button>
      <div id="settingsDropdown" class="settings-dropdown">
        <ul>
          <li><a href="#" onclick="toggleDarkMode()">🌓 Toggle Dark Mode</a></li>
          <li>
            <form action="../php/logout.php" method="POST">
              <button type="submit" style="background: none; border: none; color: inherit; cursor: pointer;">🚪 Log Out</button>
            </form>
          </li>
        </ul>
      </div>
    </div>

    <h2>Add Group to Class</h2>
    <form method="POST" action="../php/add_group_to_class.php">
      <input type="hidden" name="class_id" value="<?= $class_id ?>">

      <label>Class Number:</label>
      <select name="class_number" required>
        <?php for ($i = 1; $i <= 12; $i++): ?>
          <option value="<?= $i ?>"><?= $i ?></option>
        <?php endfor; ?>
      </select>

      <label>Letter:</label>
      <select name="class_letter" required>
        <?php foreach (['A', 'B', 'C'] as $l): ?>
          <option value="<?= $l ?>"><?= $l ?></option>
        <?php endforeach; ?>
      </select>

      <button type="submit">Add Group</button>
    </form>
  </div>

  <script>
    function toggleSettings() {
      const dropdown = document.getElementById('settingsDropdown');
      dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
    }

    document.addEventListener('click', function(e) {
      if (!e.target.closest('.settings-wrapper')) {
        document.getElementById('settingsDropdown').style.display = 'none';
      }
    });

    function toggleDarkMode() {
      document.body.classList.toggle('dark-mode');
      localStorage.setItem('theme', document.body.classList.contains('dark-mode') ? 'dark' : 'light');
    }

    window.addEventListener('DOMContentLoaded', () => {
      if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-mode');
      }
    });
  </script>
</body>
</html>
