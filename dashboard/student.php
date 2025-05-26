<?php
session_start();
include '../php/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    die("Access denied.");
}

$student_id = $_SESSION['user_id'];
$student_name = $_SESSION['user_name'] ?? 'Student';

// Get all classes
$classes = $conn->prepare("
    SELECT c.id, c.class_name, c.course_name 
    FROM class_students cs
    JOIN classes c ON cs.class_id = c.id
    WHERE cs.student_id = ?
");
$classes->bind_param("i", $student_id);
$classes->execute();
$class_result = $classes->get_result();
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Student Dashboard</title>
  <link rel="stylesheet" href="../css/student_dashboard.css">

  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f8f9fa;
      margin: 0;
      padding: 20px;
      color: #333;
    }

    .container {
      max-width: 1100px;
      margin: 0 auto;
    }

    h2, h3 {
      text-align: center;
      margin-bottom: 25px;
    }

    .tab-menu {
      text-align: center;
      margin-bottom: 30px;
    }

    .tab-button {
      background-color: #e9ecef;
      border: none;
      padding: 10px 20px;
      margin: 0 5px;
      font-size: 16px;
      cursor: pointer;
      border-radius: 6px;
      transition: background-color 0.3s, color 0.3s;
    }

    .tab-button.active,
    .tab-button:hover {
      background-color: #007bff;
      color: white;
    }

    .tab-content {
      display: none;
      animation: fadeIn 0.3s ease-in-out;
    }

    .tab-content.active {
      display: block;
    }

    .class-box {
      background-color: white;
      border: 1px solid #dee2e6;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
      padding: 20px;
      margin-bottom: 30px;
      border-radius: 10px;
      transition: transform 0.2s ease;
    }

    .class-box:hover {
      transform: translateY(-3px);
    }

    .class-box h4 {
      margin-top: 0;
      color: #007bff;
    }

    .class-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 10px;
    }

    .attendance {
      font-size: 14px;
      color: #555;
    }
    body.dark-mode {
  background-color: #121212;
  color: #e0e0e0;
}

.dark-mode .class-box,
.dark-mode .settings-dropdown {
  background-color: #1e1e1e;
  border-color: #333;
  color: #e0e0e0;
}

.dark-mode .tab-button,
.dark-mode .sub-tab-button {
  background-color: #2c2c2c;
  color: #e0e0e0;
}

.dark-mode .tab-button.active,
.dark-mode .sub-tab-button.active,
.dark-mode .tab-button:hover,
.dark-mode .sub-tab-button:hover {
  background-color: #007bff;
  color: #ffffff;
}

.dark-mode .semester-select,
.dark-mode input,
.dark-mode select,
.dark-mode textarea {
  background-color: #2a2a2a;
  color: #f1f1f1;
  border: 1px solid #555;
}

.dark-mode a {
  color: #66b2ff;
}

.dark-mode hr {
  border-top-color: #444;
}

    .semester-select {
      padding: 6px 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 14px;
    }

    .semester {
      margin: 10px 0;
    }

    ul {
      padding-left: 20px;
    }

    li {
      margin-bottom: 15px;
      line-height: 1.6;
    }

    a {
      color: #007bff;
      text-decoration: none;
    }

    a:hover {
      text-decoration: underline;
    }

    hr {
      margin: 10px 0;
      border: none;
      border-top: 1px solid #eee;
    }

    .sub-tab-menu {
      text-align: center;
      margin-bottom: 20px;
    }

    .sub-tab-button {
      background-color: #e9ecef;
      border: none;
      padding: 8px 16px;
      margin: 0 5px;
      font-size: 15px;
      cursor: pointer;
      border-radius: 6px;
    }

    .sub-tab-button.active,
    .sub-tab-button:hover {
      background-color: #007bff;
      color: #fff;
    }

    .assignment-subtab {
      display: none;
    }

    .assignment-subtab.active {
      display: block;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .settings-wrapper {
  position: absolute;
  top: 20px;
  right: 20px;
  z-index: 1000;
}

.settings-button {
  background: none;
  border: none;
  font-size: 20px;
  cursor: pointer;
  color: inherit;
}

.settings-dropdown {
  display: none;
  position: absolute;
  top: 35px;
  right: 0;
  background-color: var(--box-bg, white);
  border: 1px solid var(--border-color, #ccc);
  border-radius: 8px;
  box-shadow: 0 8px 16px rgba(0,0,0,0.1);
  min-width: 160px;
}

.settings-dropdown ul {
  list-style: none;
  margin: 0;
  padding: 0;
}

.settings-dropdown li {
  border-bottom: 1px solid var(--border-color, #eee);
}

.settings-dropdown li:last-child {
  border-bottom: none;
}

.settings-dropdown a {
  display: block;
  padding: 10px 15px;
  color: var(--text-color, #333);
  text-decoration: none;
  transition: background 0.2s;
}

.settings-dropdown a:hover {
  background-color: var(--hover-bg, #f0f0f0);
}
.dashboard-header {
  text-align: center;
  margin-bottom: 30px;
}

.dashboard-logo {
  height: 80px;
  max-width: 100%;
  object-fit: contain;
  transition: filter 0.3s ease;
}

/* Optional: make the logo adjust for dark mode */
body.dark-mode .dashboard-logo {
  filter: invert(1) hue-rotate(180deg);
}
.chat-box {
  max-height: 400px;
  overflow-y: auto;
  padding: 10px;
  background-color: #fdfdfd;
  border: 1px solid #ddd;
  border-radius: 8px;
}

.chat-message {
  margin-bottom: 15px;
  max-width: 75%;
  clear: both;
}

.chat-message.mine {
  text-align: right;
  margin-left: auto;
}

.chat-message.theirs {
  text-align: left;
  margin-right: auto;
}

.chat-meta {
  font-size: 0.9em;
  color: #555;
  margin-bottom: 3px;
  display: flex;
  justify-content: space-between;
}

.chat-message.mine .chat-meta {
  justify-content: flex-end;
  text-align: right;
}
.chat-message.mine .chat-meta {
  margin-right: 10px;
}

.chat-meta.time-first {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 3px;
}

.chat-message.mine .chat-meta.time-first {
  align-items: flex-end;
}

.chat-time {
  font-style: italic;
  font-size: 0.8em;
  color: #888;
  margin-left: 10px;
}

.chat-bubble {
  display: inline-block;
  padding: 10px 14px;
  background-color: #e9ecef;
  border-radius: 15px;
  color: #333;
  white-space: pre-wrap;
  word-break: break-word;
}

.chat-message.mine .chat-bubble {
  background-color: #007bff;
  color: white;
}
.chat-user-list li a {
  display: block;
  padding: 10px 12px;
  margin-bottom: 5px;
  background-color: #f1f1f1;
  border-radius: 6px;
  color: #333;
  text-decoration: none;
  transition: background-color 0.2s ease;
}

.chat-user-list li a:hover {
  background-color: #e2e6ea;
}

.chat-user-list li a.active {
  background-color: #007bff;
  color: white;
  font-weight: bold;
}

  </style>
</head>
<body>
<div class="dashboard-header">
  <img src="../images/logo.png" alt="Logo" class="dashboard-logo">
</div>

<div class="settings-wrapper">
  <button onclick="toggleDropdown()" class="settings-button">⚙️</button>
  <div id="settingsMenu" class="settings-dropdown">
    <a href="#" onclick="toggleDarkMode(); return false;">🌓 Toggle Dark Mode</a>
    <a href="../php/logout.php">🚪 Log Out</a>
  </div>
</div>
<div class="container">
  <h2>Welcome, <?= htmlspecialchars($student_name) ?>!</h2>

  <div class="tab-menu">
    <button class="tab-button active" onclick="showTab('dashboard', this)">Dashboard</button>
    <button class="tab-button" onclick="showTab('materials', this)">Materials</button>

    <button class="tab-button" onclick="showTab('assignments', this)">Assignments</button>
    <button class="tab-button" onclick="showTab('messages1', this)">Messages</button>

  </div>

    <!-- Dashboard: Attendance + Grades -->
    <div class="tab-content active" id="dashboard">
    <h3>Your Class Overview</h3>
    <?php
    $classes->execute();
    $class_result = $classes->get_result();
    while ($class = $class_result->fetch_assoc()):
      $class_id = $class['id'];

      // Attendance
      $att = $conn->prepare("SELECT COUNT(*) AS total, SUM(status = 'present') AS present FROM attendance WHERE student_id = ? AND class_id = ?");
      $att->bind_param("ii", $student_id, $class_id);
      $att->execute();
      $att_data = $att->get_result()->fetch_assoc();
      $percent = ($att_data['total'] ?? 0) > 0 ? round($att_data['present'] / $att_data['total'] * 100) : 0;

      // Grades
      $grades = $conn->prepare("SELECT * FROM grades WHERE student_id = ? AND class_id = ?");
      $grades->bind_param("ii", $student_id, $class_id);
      $grades->execute();
      $grade_result = $grades->get_result();
    ?>
    <div class="class-box">
      <div class="class-header">
        <div>
          <h4><?= htmlspecialchars($class['course_name'] . ' ' . $class['class_name']) ?></h4>
          <span class="attendance">Attendance:</span>
          <span class="attendance-percent"><?= $percent ?>%</span>

        </div>
       
      </div>

      <?php if ($grade_result->num_rows > 0): ?>
        <ul>
          <?php while ($g = $grade_result->fetch_assoc()): ?>
            <li class="semester sem<?= $g['semester'] ?>">
              <strong>Semester <?= $g['semester'] ?></strong> —
              Participation: <?= $g['participation'] ?>,
              Project: <?= $g['project'] ?>,
              Exam: <?= $g['exam'] ?>
            </li>
          <?php endwhile; ?>
        </ul>
      <?php else: ?>
        <p>No grades available.</p>
      <?php endif; ?>
    </div>
    <?php endwhile; ?>
  </div>


        
          
  <!-- Assignments Tab -->
<div class="tab-content" id="assignments">
  <h3>Assignments</h3>

  <!-- Sub-tab Buttons -->
  <div class="sub-tab-menu">
    <button class="sub-tab-button active" onclick="showSubTab('upcoming')">Upcoming (Next 5 Days)</button>
    <button class="sub-tab-button " onclick="showSubTab('all')">All Assignments</button>
    <button class="sub-tab-button" onclick="showSubTab('done')">Submitted</button>
  </div>

  <!-- Upcoming Assignments -->
  <div class="assignment-subtab active" id="upcoming">
    <?php
    $today = date('Y-m-d');
    $future = date('Y-m-d', strtotime('+5 days'));
    $classes->execute();
    $class_result = $classes->get_result();
    $hasUpcoming = false;

    while ($class = $class_result->fetch_assoc()):
      $class_id = $class['id'];

      $assignments = $conn->prepare("
        SELECT * FROM assignments 
        WHERE class_id = ? AND due_date BETWEEN ? AND ?
        ORDER BY due_date ASC
      ");
      $assignments->bind_param("iss", $class_id, $today, $future);
      $assignments->execute();
      $res = $assignments->get_result();

      if ($res->num_rows > 0):
        $hasUpcoming = true;
    ?>
      <div class="class-box">
        <h4><?= htmlspecialchars($class['course_name'] . ' ' . $class['class_name']) ?></h4>
        <ul>
          <?php while ($a = $res->fetch_assoc()):
            $assignment_id = $a['id'];
            $submission = $conn->prepare("SELECT * FROM submissions WHERE student_id = ? AND assignment_id = ?");
            $submission->bind_param("ii", $student_id, $assignment_id);
            $submission->execute();
            $submitted = $submission->get_result()->fetch_assoc();
          ?>
            <li>
              <strong><?= htmlspecialchars($a['title']) ?></strong> – Due: <?= $a['due_date'] ?><br>
              <?= nl2br(htmlspecialchars($a['description'])) ?><br>

              <?php if ($a['attachment']): ?>
                <a href="../uploads/<?= $a['attachment'] ?>" target="_blank">📎 Download Attachment</a><br>
              <?php endif; ?>

              <?php if ($submitted): ?>
                <p style="color: green;"><strong>✔ Submitted</strong> on <?= $submitted['submitted_at'] ?></p>
                <p>File: <a href="../uploads/<?= $submitted['file'] ?>" target="_blank"><?= $submitted['file'] ?></a></p>
              <?php else: ?>
                <p style="color: red;"><strong>⏳ Not submitted yet</strong></p>
              <?php endif; ?>

              <form method="POST" action="../php/submit_assignment_student.php" enctype="multipart/form-data">
                <input type="hidden" name="assignment_id" value="<?= $assignment_id ?>">
                <input type="file" name="submission_file" accept=".pdf,.doc,.docx,.zip" required>
                <button type="submit"><?= $submitted ? 'Re-upload' : 'Submit' ?></button>
              </form>
              <hr>
            </li>
          <?php endwhile; ?>
        </ul>
      </div>
    <?php endif; endwhile; ?>

    <?php if (!$hasUpcoming): ?>
      <p style="text-align:center; color:#777;">No upcoming assignments due in the next 5 days.</p>
    <?php endif; ?>
  </div>
   

  <!-- All Assignments -->
  <div class="assignment-subtab" id="all">
  <?php
  $classes->execute();
  $class_result = $classes->get_result();

  while ($class = $class_result->fetch_assoc()):
    $class_id = $class['id'];

    $assignments = $conn->prepare("SELECT * FROM assignments WHERE class_id = ? ORDER BY due_date DESC");
    $assignments->bind_param("i", $class_id);
    $assignments->execute();
    $res = $assignments->get_result();

    $assignment_items = '';

    while ($a = $res->fetch_assoc()):
      $assignment_id = $a['id'];
      $due = $a['due_date'];

      $submission = $conn->prepare("SELECT id FROM submissions WHERE student_id = ? AND assignment_id = ?");
      $submission->bind_param("ii", $student_id, $assignment_id);
      $submission->execute();
      $submitted = $submission->get_result()->fetch_assoc();

      if ($submitted) continue; // Skip submitted assignments

      ob_start(); // buffer the HTML output
      ?>
        <li>
          <strong><?= htmlspecialchars($a['title']) ?></strong> – Due: <?= $due ?><br>
          <?= nl2br(htmlspecialchars($a['description'])) ?><br>
          <?php if ($a['attachment']): ?>
            <a href="../uploads/<?= $a['attachment'] ?>" target="_blank">Download</a><br>
          <?php endif; ?>
          <form method="POST" action="../php/submit_assignment_student.php" enctype="multipart/form-data">
            <input type="hidden" name="assignment_id" value="<?= $assignment_id ?>">
            <input type="file" name="submission_file" accept=".pdf,.doc,.docx,.zip" required>
            <button type="submit">Submit</button>
          </form>
          <hr>
        </li>
      <?php
      $assignment_items .= ob_get_clean(); // store the HTML if it's unsubmitted
    endwhile;

    if (!empty($assignment_items)):
  ?>
      <div class="class-box">
        <h4><?= htmlspecialchars($class['course_name'] . ' ' . $class['class_name']) ?></h4>
        <ul><?= $assignment_items ?></ul>
      </div>
  <?php
    endif;
  endwhile;
  ?>
</div>



  <!-- Submitted Assignments -->
  <div class="assignment-subtab" id="done">
<h4>Your Submitted Assignments</h4>
<?php
$classes->execute();
$class_result = $classes->get_result();
$hasDone = false;

while ($class = $class_result->fetch_assoc()):
  $class_id = $class['id'];

  $assignments = $conn->prepare("SELECT * FROM assignments WHERE class_id = ? ORDER BY due_date DESC");
  $assignments->bind_param("i", $class_id);
  $assignments->execute();
  $res = $assignments->get_result();

  while ($a = $res->fetch_assoc()):
    $assignment_id = $a['id'];
    $due = $a['due_date'];

    $submission = $conn->prepare("SELECT * FROM submissions WHERE student_id = ? AND assignment_id = ?");
    $submission->bind_param("ii", $student_id, $assignment_id);
    $submission->execute();
    $sub = $submission->get_result()->fetch_assoc();

    if ($sub):
      $hasDone = true;
      $is_late = strtotime($sub['submitted_at']) > strtotime($due . ' 23:59:59');
?>
  <div class="class-box">
    <h4><?= htmlspecialchars($class['course_name'] . ' ' . $class['class_name']) ?> – <?= htmlspecialchars($a['title']) ?></h4>
    <p><strong>Due:</strong> <?= $due ?></p>
    <p><strong>Submitted:</strong> <?= $sub['submitted_at'] ?>
      <?php if ($is_late): ?>
        <span style="color:red;">(Late)</span>
      <?php endif; ?>
    </p>

    <form method="POST" action="../php/submit_assignment_student.php" enctype="multipart/form-data">
      <input type="hidden" name="assignment_id" value="<?= $assignment_id ?>">
      <label for="submission_file_<?= $assignment_id ?>">Re-upload your work:</label>
      <input type="file" name="submission_file" id="submission_file_<?= $assignment_id ?>" accept=".pdf,.doc,.docx,.zip" required>

      <?php if (date('Y-m-d') > $due): ?>
        <p style="color:darkred;"><strong>Warning:</strong> Re-uploading now will be marked as <strong>late</strong>.</p>
        <button type="submit" style="background-color:#dc3545; color:white; padding:6px 14px; border:none; border-radius:5px;">Re-upload (Late)</button>
      <?php else: ?>
        <button type="submit" style="background-color:#007bff; color:white; padding:6px 14px; border:none; border-radius:5px;">Re-upload</button>
      <?php endif; ?>
    </form>
  </div>
<?php
    endif;
  endwhile;
endwhile;

if (!$hasDone): ?>
  <p style="text-align:center; color:#777;">No submitted assignments yet.</p>
<?php endif; ?>
</div>
</div> <!-- End of Assignments Tab -->

<!-- Messages Tab -->
<div class="tab-content" id="messages1">
  <h3>Direct Messages</h3>

  <div style="display: flex; gap: 20px;">
    <!-- Sidebar: Teachers List -->
    <div style="width: 250px; border-right: 1px solid #ccc;">
      <h4>Teachers</h4>
      <ul class="chat-user-list">
        <?php
        $current_user = $_SESSION['user_id'];
        $chat_with = $_GET['chat_with'] ?? null;

        $stmt = $conn->prepare("
          SELECT DISTINCT u.id, u.name
          FROM users u
          JOIN classes c ON c.teacher_id = u.id
          JOIN class_students cs ON cs.class_id = c.id
          WHERE cs.student_id = ? AND u.role = 'teacher'
        ");
        $stmt->bind_param("i", $current_user);
        $stmt->execute();
        $teachers = $stmt->get_result();

        while ($t = $teachers->fetch_assoc()):
        ?>
          <li>
            <a href="?tab=messages1&chat_with=<?= $t['id'] ?>"
              <?= $chat_with == $t['id'] ? 'style="font-weight: bold;"' : '' ?>>
              <?= htmlspecialchars($t['name']) ?>
            </a>
          </li>
        <?php endwhile; ?>
      </ul>
    </div>

    <!-- Message Thread + Form -->
    <div style="flex: 1;">
      <?php if ($chat_with): ?>
        <div class="chat-box">
          <?php
          $msg_query = $conn->prepare("
            SELECT m.*, u.name AS sender_name
            FROM messages m
            JOIN users u ON u.id = m.sender_id
            WHERE (sender_id = ? AND receiver_id = ?)
              OR (sender_id = ? AND receiver_id = ?)
            ORDER BY timestamp ASC
          ");
          $msg_query->bind_param("iiii", $current_user, $chat_with, $chat_with, $current_user);
          $msg_query->execute();
          $messages = $msg_query->get_result();

          while ($m = $messages->fetch_assoc()):
            $mine = $m['sender_id'] == $current_user;
          ?>
            <div class="chat-message <?= $mine ? 'mine' : 'theirs' ?>">
            <div class="chat-meta time-first">
            <div class="chat-time"><?= date("M j, g:i A", strtotime($m['timestamp'])) ?></div>
              <strong><?= htmlspecialchars($m['sender_name']) ?></strong>
            </div>

              <div class="chat-bubble"><?= nl2br(htmlspecialchars($m['message'])) ?></div>
            </div>
          <?php endwhile; ?>
        </div>

        <!-- Message form -->
        <form action="../php/send_dm.php" method="POST" style="margin-top: 10px;">
          <input type="hidden" name="receiver_id" value="<?= $chat_with ?>">
          <input type="hidden" name="class_id" value="student">
          <input type="hidden" name="tab" value="messages1">
          <textarea name="message" rows="3" required placeholder="Type a message..." style="width: 100%; border-radius: 6px; padding: 8px;"></textarea>
          <button type="submit" style="margin-top: 5px;">Send</button>
        </form>

      <?php else: ?>
        <p>Select a teacher to start chatting.</p>
      <?php endif; ?>
    </div>
  </div>
</div>


<!-- Materials Tab -->
<div class="tab-content" id="materials">
  <h3>Class Materials</h3>
  <?php
  $classes->execute();
  $class_result = $classes->get_result();

  while ($class = $class_result->fetch_assoc()):
    $class_id = $class['id'];
    $materials = $conn->prepare("SELECT * FROM class_materials WHERE class_id = ? ORDER BY uploaded_at DESC");
    $materials->bind_param("i", $class_id);
    $materials->execute();
    $result = $materials->get_result();

    if ($result->num_rows > 0):
  ?>
    <div class="class-box">
      <h4><?= htmlspecialchars($class['course_name'] . ' ' . $class['class_name']) ?></h4>
      <ul>
        <?php while ($mat = $result->fetch_assoc()): ?>
          <li>
            <strong><?= htmlspecialchars($mat['title']) ?></strong>
            (Uploaded: <?= date("M j, Y", strtotime($mat['uploaded_at'])) ?>)<br>
            <a href="../uploads/<?= htmlspecialchars($mat['file_name']) ?>" target="_blank">Download</a>
            <?php if (!empty($mat['description'])): ?>
              <p style="margin: 5px 0;"><?= nl2br(htmlspecialchars($mat['description'])) ?></p>
            <?php endif; ?>
            <hr>
          </li>
        <?php endwhile; ?>
      </ul>
    </div>
  <?php
    endif;
  endwhile;
  ?>
</div>


<script>
function showTab(tabId, buttonElement) {
  // Hide all tab contents and deactivate buttons
  document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
  document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));

  // Show selected tab and activate its button
  const selectedTab = document.getElementById(tabId);
  if (selectedTab) selectedTab.classList.add('active');
  if (tabId === 'assignments') {
  showSubTab('upcoming');
}

  if (buttonElement) buttonElement.classList.add('active');

  // ⛔ Reset sub-tabs when leaving 'assignments'
  if (tabId !== 'assignments') {
    document.querySelectorAll('.assignment-subtab').forEach(st => st.classList.remove('active'));
  }

  // Update URL with current tab (and chat_with if needed)
  const params = new URLSearchParams(window.location.search);
  params.set('tab', tabId);
  if (tabId === 'messages1') {
    const chatWith = new URLSearchParams(window.location.search).get('chat_with');
    if (chatWith) params.set('chat_with', chatWith);
  } else {
    params.delete('chat_with');
  }
  history.replaceState(null, '', '?' + params.toString());
}


function showSubTab(tabId) {
  document.querySelectorAll('.assignment-subtab').forEach(tab => tab.classList.remove('active'));
  document.querySelectorAll('.sub-tab-button').forEach(btn => btn.classList.remove('active'));

  document.getElementById(tabId).classList.add('active');
  const activeBtn = Array.from(document.querySelectorAll('.sub-tab-button')).find(btn =>
    btn.textContent.toLowerCase().includes(tabId)
  );
  if (activeBtn) activeBtn.classList.add('active');
}

function loadChat(teacherId, teacherName) {
  document.getElementById('chatHeader').innerHTML = `<strong>Chat with ${teacherName}</strong>`;
  document.getElementById('currentTeacherId').value = teacherId;

  fetch(`../php/get_messages.php?teacher_id=${teacherId}`)
    .then(response => response.json())
    .then(messages => {
      const chatBox = document.getElementById('chatHistory');
      chatBox.innerHTML = '';
      messages.forEach(msg => {
        const div = document.createElement('div');
        div.className = 'chat-bubble ' + (msg.from_student ? 'from-student' : 'from-teacher');
        div.textContent = msg.message;
        chatBox.appendChild(div);
      });
      chatBox.scrollTop = chatBox.scrollHeight;
    });
}

function sendMessage(event) {
  event.preventDefault();
  const teacherId = document.getElementById('currentTeacherId').value;
  const message = document.getElementById('chatInput').value;
  if (!teacherId || !message) return;

  fetch('../php/send_message.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `teacher_id=${teacherId}&message=${encodeURIComponent(message)}`
  }).then(() => {
    document.getElementById('chatInput').value = '';
    loadChat(teacherId, document.getElementById('chatHeader').textContent.replace('Chat with ', ''));
  });
}

function toggleDarkMode() {
  document.body.classList.toggle('dark-mode');
  localStorage.setItem('theme', document.body.classList.contains('dark-mode') ? 'dark' : 'light');
  updateLogoForTheme();
}

function toggleDropdown() {
  const menu = document.getElementById('settingsMenu');
  menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
}

function updateLogoForTheme() {
  const logo = document.getElementById('mainLogo');
  if (!logo) return;
  const isDark = document.body.classList.contains('dark-mode');
  logo.src = isDark ? '../images/logo-dark.png' : '../images/logo-light.png';
}

// Initial setup on page load
window.addEventListener('DOMContentLoaded', () => {
  // Apply dark mode if remembered
  if (localStorage.getItem('theme') === 'dark') {
    document.body.classList.add('dark-mode');
  }

  // Show correct tab from URL
  const params = new URLSearchParams(window.location.search);
  const activeTab = params.get('tab') || 'dashboard';
  const tabBtn = document.querySelector(`.tab-button[onclick*="${activeTab}"]`);
  if (tabBtn) showTab(activeTab, tabBtn);

  // Hide dropdown if clicking outside
  document.addEventListener('click', function(e) {
    if (!e.target.closest('.settings-wrapper')) {
      document.getElementById('settingsMenu').style.display = 'none';
    }
  });

  updateLogoForTheme();
});
</script>

</body>
</html>



