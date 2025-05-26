<?php
session_start();
include '../php/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    die("Access denied.");
}

$assignment_id = $_GET['assignment_id'] ?? null;
$class_id = $_GET['class_id'] ?? null;

if (!$assignment_id || !$class_id) {
    die("Missing parameters.");
}

$stmt = $conn->prepare("SELECT title, description, due_date, attachment FROM assignments WHERE id = ?");
$stmt->bind_param("i", $assignment_id);
$stmt->execute();
$assignment = $stmt->get_result()->fetch_assoc();

if (!$assignment) {
    die("Assignment not found.");
}

$sub_stmt = $conn->prepare("
    SELECT s.id, u.name AS student_name, s.file, s.submitted_at 
    FROM submissions s
    JOIN users u ON s.student_id = u.id
    WHERE s.assignment_id = ?
    ORDER BY s.submitted_at DESC
");
$sub_stmt->bind_param("i", $assignment_id);
$sub_stmt->execute();
$submissions = $sub_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($assignment['title']) ?> - Details</title>
  <link rel="stylesheet" href="../css/styles.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    :root {
      --box-bg: #ffffff;
      --text-color: #222;
      --border-color: #ccc;
      --hover-bg: #f0f0f0;
      --primary-color: #007bff;
    }

    body.dark {
      --box-bg: #1e1e1e;
      --text-color: #e0e0e0;
      --border-color: #444;
      --hover-bg: #2a2a2a;
      --primary-color: #66b2ff;
    }

    body {
      background-color: var(--box-bg);
      color: var(--text-color);
      font-family: 'Segoe UI', sans-serif;
      margin: 0;
      padding: 0;
    }

    .container {
      max-width: 1000px;
      margin: 40px auto;
      background: var(--box-bg);
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
      position: relative;
    }
    h2, h3 {
      color: var(--primary-color);
      text-align: center;
    }
    .settings-wrapper {
      position: absolute;
      top: 20px;
      right: 20px;
    }
    .settings-button {
      background: none;
      border: none;
      font-size: 20px;
      cursor: pointer;
      color: var(--text-color);
    }
    .settings-dropdown {
      display: none;
      position: absolute;
      right: 0;
      top: 35px;
      background: var(--box-bg);
      border: 1px solid var(--border-color);
      border-radius: 8px;
      box-shadow: 0 8px 16px rgba(0,0,0,0.1);
      min-width: 160px;
      z-index: 1000;
    }
    .settings-dropdown ul {
      list-style: none;
      margin: 0;
      padding: 0;
    }
    .settings-dropdown li {
      border-bottom: 1px solid var(--border-color);
    }
    .settings-dropdown li:last-child {
      border-bottom: none;
    }
    .settings-dropdown a, .settings-dropdown button {
      display: block;
      padding: 10px 15px;
      width: 100%;
      background: none;
      border: none;
      text-align: left;
      font-size: 14px;
      color: var(--text-color);
      text-decoration: none;
      cursor: pointer;
    }
    .settings-dropdown a:hover, .settings-dropdown button:hover {
      background-color: var(--hover-bg);
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
      background-color: var(--box-bg);
    }
    th, td {
      padding: 12px;
      text-align: left;
      border-bottom: 1px solid var(--border-color);
      color: var(--text-color);
    }
    th {
      background-color: var(--hover-bg);
    }
    .tab-button {
      background-color: var(--primary-color);
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 5px;
      text-decoration: none;
      margin-right: 10px;
    }
    .delete-btn {
      background-color: #dc3545;
      border: none;
      color: white;
      padding: 10px 20px;
      border-radius: 5px;
      cursor: pointer;
    }
    .delete-btn:hover {
      background-color: #a71d2a;
    }
    /* Table Header Cells */
th {
  background-color: var(--hover-bg);
  color: var(--text-color);
  font-weight: bold;
  font-size: 15px;
  border-bottom: 1px solid var(--border-color);
  padding: 12px;
}

/* Optional - improve visibility in dark mode */
body.dark th {
  background-color: #2a2a2a;
  color: #f0f0f0;
}

  </style>
</head>
<body>

<div class="container">
  <div class="settings-wrapper">
    <button onclick="toggleSettings()" class="settings-button">⚙️</button>
    <button onclick="toggleDarkMode()" class="settings-button" title="Toggle Dark Mode">🌓</button>
    <div id="settingsDropdown" class="settings-dropdown">
      <ul>
        <li><a href="class_page.php?class_id=<?= $class_id ?>">← Back to Class</a></li>
        <li><form method="POST" action="../php/logout.php"><button type="submit">🚪 Log Out</button></form></li>
      </ul>
    </div>
  </div>

  <h2><?= htmlspecialchars($assignment['title']) ?></h2>
  <p><strong>Due Date:</strong> <?= htmlspecialchars($assignment['due_date']) ?></p>

  <?php if (!empty($assignment['attachment'])): ?>
    <p><strong>Attachment:</strong> <a href="../uploads/<?= htmlspecialchars($assignment['attachment']) ?>" target="_blank">Download</a></p>
  <?php endif; ?>

  <p><strong>Description:</strong><br><?= nl2br(htmlspecialchars($assignment['description'])) ?></p>
  <hr>

  <h3>Submissions</h3>
  <?php if ($submissions->num_rows > 0): ?>
    <table>
      <thead>
        <tr>
          <th>Student</th>
          <th>File</th>
          <th>Submitted At</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $submissions->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['student_name']) ?></td>
            <td><a href="../uploads/<?= htmlspecialchars($row['file']) ?>" target="_blank">View</a></td>
            <td><?= htmlspecialchars($row['submitted_at']) ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>No submissions yet.</p>
  <?php endif; ?>

  <br>
  <form method="POST" action="../php/delete_assignment.php" onsubmit="return confirm('Are you sure you want to delete this assignment?');" style="display:inline;">
    <input type="hidden" name="assignment_id" value="<?= $assignment_id ?>">
    <input type="hidden" name="class_id" value="<?= $class_id ?>">
    <button type="submit" class="delete-btn">Delete Assignment</button>
  </form>
</div>

<script>
function toggleSettings() {
  const dropdown = document.getElementById("settingsDropdown");
  dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
}

function toggleDarkMode() {
  document.body.classList.toggle("dark");
  localStorage.setItem("theme", document.body.classList.contains("dark") ? "dark" : "light");
}

window.addEventListener("DOMContentLoaded", () => {
  if (localStorage.getItem("theme") === "dark") {
    document.body.classList.add("dark");
  }
  document.addEventListener("click", function(e) {
    if (!e.target.closest(".settings-wrapper")) {
      document.getElementById("settingsDropdown").style.display = "none";
    }
  });
});
</script>

</body>
</html>