<?php 
session_start();
include '../php/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die("Access denied.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Panel</title>
  <link rel="stylesheet" href="../css/admin.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    .tab-menu {
      display: flex;
      gap: 10px;
      margin-bottom: 20px;
      flex-wrap: wrap;
    }
    .tab-button {
      padding: 10px 20px;
      cursor: pointer;
      background-color: #eee;
      border: 1px solid #ccc;
    }
    .tab-button.active {
      background-color: #007bff;
      color: white;
    }
    .tab-content {
      display: none;
    }
    .tab-content.active {
      display: block;
    }
    form input,
    form select,
    form textarea {
      margin-bottom: 10px;
      width: 100%;
      padding: 8px;
    }
    form button {
      padding: 10px 15px;
    }
    .event-item {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  background: #fff;
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 20px;
  margin-bottom: 15px;
  position: relative;
  box-shadow: 0 2px 6px rgba(0,0,0,0.05);
  transition: background 0.3s ease;
}

.event-item:hover {
  background: #f9f9f9;
}

.event-left {
  flex: 2;
}

.event-left h4 {
  margin: 0 0 8px;
  font-size: 16px;
  color: #222;
}

.event-left p {
  margin: 0;
  font-size: 14px;
  color: #555;
  line-height: 1.5;
}

/* Right side image */
.event-image {
  flex: 1;
  max-width: 150px;
  margin-left: 20px;
}

.event-image img {
  width: 100%;
  height: auto;
  border-radius: 6px;
  object-fit: cover;
  border: 1px solid #ccc;
}

/* X delete button */
.event-delete {
  position: absolute;
  top: 10px;
  right: 10px;
  background: transparent;
  border: none;
  font-size: 20px;
  color: #dc3545;
  cursor: pointer;
  font-weight: bold;
  padding: 0;
  line-height: 1;
  outline: none;
  box-shadow: none;
  appearance: none; /* Reset styling in WebKit and others */
  -webkit-appearance: none;
  transition: color 0.2s ease;
}

/* Remove hover effect */
.event-delete:hover {
  color: #a71d2a;
  background: transparent;
  outline: none;
  box-shadow: none;
}

/* Remove focus ring (keyboard focus) */
.event-delete:focus {
  outline: none;
  box-shadow: none;
}

/* Optional: Remove active (click) effects */
.event-delete:active {
  background: transparent;
  outline: none;
  box-shadow: none;
}


  </style>
</head>
<body>

<div class="login-box">
  <h2>Admin Dashboard</h2>

  <!-- Tab Menu -->
  <div class="tab-menu">
    <button class="tab-button active" onclick="showTab('manage_users')">Create & Manage Users</button>
    <button class="tab-button" onclick="showTab('student_assign')">Assign Students</button>
    <button class="tab-button" onclick="showTab('admin_events')">Post Events</button>
  </div>
  
<!-- END .login-box -->
  <!-- Manage Users Tab -->
  <div class="tab-content active" id="manage_users">
    <?php if (!empty($_SESSION['error'])): ?>
      <div style="color:red; margin-bottom:10px;">
        <?= htmlspecialchars($_SESSION['error']) ?>
      </div>
      <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['success'])): ?>
      <div style="color:green; margin-bottom:10px;">
        <?= htmlspecialchars($_SESSION['success']) ?>
      </div>
      <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <h3>Create User (Staff / Parent / Student)</h3>
    <form action="../php/create_user.php" method="POST" id="userForm">
      <input type="text" name="name" placeholder="Name" required>
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Password" required>

      <select name="role" id="roleSelect" required>
        <option value="">Select Role</option>
        <option value="admin">Admin</option>
        <option value="teacher">Teacher</option>
        <option value="parent">Parent</option>
        <option value="student">Student</option>
      </select>

      <!-- Parent creates student -->
      <div id="parentPlusStudentFields" style="display: none;">
        <h4>Student Details (Linked to this Parent)</h4>
        <input type="text" name="linked_student_name" placeholder="Student Name">
        <input type="email" name="linked_student_email" placeholder="Student Email">
        <input type="password" name="linked_student_password" placeholder="Student Password">
        <label>Class Group:</label>
        <select name="linked_class_number">
          <option value="">Select Class Number</option>
          <?php for ($i = 1; $i <= 12; $i++) echo "<option value='$i'>$i</option>"; ?>
        </select>
        <select name="linked_class_letter">
          <option value="">Select Letter</option>
          <option value="A">A</option>
          <option value="B">B</option>
          <option value="C">C</option>
        </select>
      </div>

      <!-- Student needs parent -->
      <div id="studentPlusParentFields" style="display: none;">
        <h4>Assign to Parent</h4>
        <input type="text" name="new_parent_name" placeholder="New Parent Name">
        <input type="email" name="new_parent_email" placeholder="New Parent Email">
        <input type="password" name="new_parent_password" placeholder="New Parent Password">

        <label>Or Select Existing Parent</label>
        <select name="existing_parent_id">
          <option value="">-- Select --</option>
          <?php
          $parents = $conn->query("SELECT id, full_name FROM parents");
          while ($p = $parents->fetch_assoc()) {
            echo "<option value='{$p['id']}'>" . htmlspecialchars($p['full_name']) . "</option>";
          }
          ?>
        </select>

        <label>Class Group:</label>
        <select name="student_class_number">
          <option value="">Select Class Number</option>
          <?php for ($i = 1; $i <= 12; $i++) echo "<option value='$i'>$i</option>"; ?>
        </select>
        <select name="student_class_letter">
          <option value="">Select Letter</option>
          <option value="A">A</option>
          <option value="B">B</option>
          <option value="C">C</option>
        </select>
      </div>

      <button type="submit">Create User</button>
    </form>
  </div>

  <!-- Assign Students Tab -->
  <div class="tab-content" id="student_assign">
    <h3>Assign Student to Class</h3>
    <?php include 'assign_student.php'; ?>
  </div>

  <!-- Post Events Tab -->
  
  <div class="tab-content" id="admin_events">
    <h3>Post an Event</h3>
    <form action="../php/post_event.php" method="POST" enctype="multipart/form-data">
      <input type="text" name="title" placeholder="Event Title" required>
      <textarea name="description" placeholder="Event Description (max 200 words)" maxlength="1500" rows="5" required></textarea>
      <input type="file" name="image" accept="image/*">
      <button type="submit">Post Event</button>
    </form>

    <hr>
    <h3>All Events</h3>
    <?php
    $events = $conn->query("SELECT * FROM events ORDER BY created_at DESC");
    while ($e = $events->fetch_assoc()):
    ?>
  <div class="event-item">
  <form action="../php/delete_event.php" method="POST" class="delete-form">
    <input type="hidden" name="id" value="<?= $e['id'] ?>">
    <button type="submit" class="event-delete">×</button>
  </form>

  <div class="event-left">
    <h4><?= htmlspecialchars($e['title']) ?></h4>
    <p><?= nl2br(htmlspecialchars($e['description'])) ?></p>
  </div>

  <?php if (!empty($e['image_path'])): ?>
    <div class="event-image">
      <img src="<?= $e['image_path'] ?>" alt="Event Image">
    </div>
  <?php endif; ?>
</div>
    <?php endwhile; ?>
   
<!-- #region -->


  </div>
  <hr>
  <a href="admin_login.html">&larr; Log Out</a>
  </div>
  

<script>
function showTab(id) {
  // Hide all tab contents
  document.querySelectorAll('.tab-content').forEach(div => div.classList.remove('active'));
  document.querySelectorAll('.tab-content').forEach(div => div.style.display = 'none');

  // Remove active class from buttons
  document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));

  // Show the selected tab
  const tab = document.getElementById(id);
  if (tab) {
    tab.classList.add('active');
    tab.style.display = 'block';
  }

  // Activate the corresponding button
  const clickedBtn = [...document.querySelectorAll('.tab-button')].find(btn =>
    btn.getAttribute('onclick')?.includes(id)
  );
  if (clickedBtn) clickedBtn.classList.add('active');

  // Save selected tab to localStorage
  localStorage.setItem('activeTab', id);
}
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const savedTab = localStorage.getItem('activeTab') || 'manage_users';
  showTab(savedTab);
});
</script>

</body>
</html>
