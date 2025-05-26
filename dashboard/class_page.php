  <?php
  session_start();
  include '../php/db.php';

  if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
      die("Access denied.");
  }

  $class_id = $_GET['class_id'] ?? null;
  if (!$class_id) die("No class selected.");

  // Get class info
  $class_query = $conn->prepare("SELECT class_name, course_name, weekly_lessons FROM classes WHERE id = ?");
  $class_query->bind_param("i", $class_id);
  $class_query->execute();
  $class_result = $class_query->get_result();
  $class = $class_result->fetch_assoc();

  if (!$class) {
      die("Class not found.");
  }

  $class_name = $class['class_name'];
  $course_name = $class['course_name'];
  $weekly_lessons = (int)$class['weekly_lessons'];

  // Assigned students
  $assigned_query = $conn->prepare("
      SELECT users.id, users.name 
      FROM users 
      JOIN class_students ON users.id = class_students.student_id 
      WHERE class_students.class_id = ?
      ORDER BY users.name ASC
  ");
  $assigned_query->bind_param("i", $class_id);
  $assigned_query->execute();
  $assigned_students = $assigned_query->get_result();

  // Attendance week
  $week_start = date('Y-m-d', strtotime('monday this week'));
  ?>

  <!DOCTYPE html>
  <html lang="en">
  <head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($course_name . ' ' . $class_name) ?></title>
    <link rel="stylesheet" href="../css/styles.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>   
 :root {
  --primary: #007bff;
  --hover-bg: #e0f0ff;
  --light-bg: #ffffff;
  --dark-bg: #121212;
  --box-dark: #1e1e1e;
  --border: #ccc;
  --border-dark: #444;
  --text: #333;
  --text-light: #f1f1f1;
}

body {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background-color: var(--light-bg);
  color: var(--text);
  margin: 0;
  padding: 20px;
  transition: background-color 0.3s ease, color 0.3s ease;
}

.container {
  max-width: 1100px;
  margin: 0 auto;
  padding: 20px;
  background: var(--light-bg);
  border-radius: 10px;
  box-shadow: 0 0 15px rgba(0,0,0,0.05);
}

/* Headings */
h2, h3 {
  text-align: center;
  margin-bottom: 25px;
  color: var(--primary);
}

/* Tabs */
.tab-menu {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 10px;
  margin-bottom: 25px;
}

.tab-button {
  padding: 10px 18px;
  font-size: 15px;
  font-weight: 500;
  background-color: #f0f0f0;
  border: 1px solid #ccc;
  color: #333;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.25s ease-in-out;
}

.tab-button:hover {
  background-color: #e6e6e6;
  color: #000;
}

.tab-button.active {
  background-color: var(--primary);
  color: #fff;
  border-color: var(--primary);
  font-weight: 600;
  box-shadow: 0 2px 6px rgba(0, 123, 255, 0.3);
}

.tab-content {
  display: none;
  animation: fadeIn 0.3s ease-in-out;
}

.tab-content.active {
  display: block;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Settings */
.settings-wrapper {
  position: relative;
}

.settings-button {
  background: none;
  border: none;
  font-size: 20px;
  margin-left: 8px;
  cursor: pointer;
  color: inherit;
}

.settings-dropdown {
  display: none;
  position: absolute;
  right: 0;
  top: 35px;
  background: var(--light-bg);
  border: 1px solid var(--border);
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  z-index: 999;
  min-width: 180px;
}

.settings-dropdown ul {
  margin: 0;
  padding: 0;
  list-style: none;
}

.settings-dropdown li {
  border-bottom: 1px solid #eee;
}

.settings-dropdown li:last-child {
  border-bottom: none;
}

.settings-dropdown a,
.settings-dropdown button {
  display: block;
  width: 100%;
  padding: 10px 15px;
  background: none;
  border: none;
  text-align: left;
  color: var(--text);
  cursor: pointer;
  font-size: 14px;
  transition: background 0.2s ease;
}

.settings-dropdown a:hover,
.settings-dropdown button:hover {
  background-color: var(--hover-bg);
}

/* Class Header */
.class-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

/* Forms & Inputs */
input,
select,
textarea {
  font-size: 14px;
  border-radius: 6px;
  padding: 10px;
  border: 1px solid #ccc;
  width: 100%;
  box-sizing: border-box;
}

button {
  background-color: var(--primary);
  color: #fff;
  padding: 10px 16px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
}

button:hover {
  background-color: #0056b3;
}

/* Table */
table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 15px;
}

th, td {
  padding: 10px;
  border: 1px solid #dee2e6;
  text-align: center;
}

th {
  background-color: #f1f1f1;
}

/* Grades */
.grades-section {
  max-width: 900px;
  margin: 0 auto 30px auto;
  padding: 20px 25px;
  background-color: #ffffff;
  border: 1px solid #ddd;
  border-radius: 10px;
  box-shadow: 0 4px 8px rgba(0,0,0,0.05);
}

.grades-section label {
  display: block;
  margin-top: 10px;
  margin-bottom: 5px;
  font-weight: 500;
}

.grades-section strong {
  display: block;
  margin-bottom: 15px;
  font-size: 1.1rem;
  color: var(--primary);
}

/* Chat */
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

.chat-time {
  font-style: italic;
  font-size: 0.8em;
  color: #888;
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
  background-color: var(--primary);
  color: white;
}

/* Chat Sidebar */
.chat-user-list {
  padding: 0;
  list-style: none;
}

.chat-user-list li {
  margin-bottom: 8px;
}

.chat-user-list li a {
  display: block;
  padding: 10px 18px;
  font-size: 15px;
  font-weight: 500;
  background-color: #f0f0f0;
  border: 1px solid #ccc;
  color: #333;
  border-radius: 6px;
  text-decoration: none;
  cursor: pointer;
  transition: all 0.25s ease-in-out;
}

.chat-user-list li a:hover {
  background-color: #e6e6e6;
  color: #000;
}

.chat-user-list li a.active {
  background-color: var(--primary);
  color: white;
  border-color: var(--primary);
  font-weight: 600;
  box-shadow: 0 2px 6px rgba(0, 123, 255, 0.3);
}

/* DARK MODE =========================================== */
body.dark {
  background-color: var(--dark-bg);
  color: var(--text-light);
}

body.dark .container,
body.dark .grades-section,
body.dark .assignment-form-wrapper {
  background-color: var(--box-dark);
  border-color: var(--border-dark);
  color: var(--text-light);
}

/* Headings */
body.dark h2,
body.dark h3 {
  color: var(--text-light);
}

/* Tabs */
body.dark .tab-button {
  background-color: #2c2c2c;
  color: var(--text-light);
  border: 1px solid var(--border-dark);
}

body.dark .tab-button.active,
body.dark .tab-button:hover {
  background-color: var(--primary);
  color: white;
  border-color: var(--primary);
}

/* Dropdown */
.settings-wrapper {
  position: relative;
  display: inline-block;
}

.settings-button {
  background: none;
  border: none;
  font-size: 20px;
  margin-left: 8px;
  cursor: pointer;
  color: inherit;
}

.settings-dropdown {
  position: absolute;
  right: 0;
  top: 40px;
  background: var(--light-bg);
  border: 1px solid var(--border);
  border-radius: 10px;
  box-shadow: 0 8px 18px rgba(0,0,0,0.15);
  min-width: 200px;
  z-index: 999;
  display: none;
  overflow: hidden;
  transition: all 0.2s ease;
}

.settings-dropdown ul {
  margin: 0;
  padding: 0;
  list-style: none;
}

.settings-dropdown li {
  border-bottom: 1px solid rgba(0, 0, 0, 0.08);
}

.settings-dropdown li:last-child {
  border-bottom: none;
}

.settings-dropdown a,
.settings-dropdown button {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 16px;
  width: 100%;
  background: none;
  border: none;
  font-size: 15px;
  font-weight: 500;
  text-align: left;
  color: var(--text);
  text-decoration: none;
  cursor: pointer;
  transition: background 0.2s ease, color 0.2s ease;
}

.settings-dropdown a:hover,
.settings-dropdown button:hover {
  background-color: var(--hover-bg);
}

/* Special styling for destructive actions */
.settings-dropdown .delete-btn {
  color: #e74c3c;
  font-weight: 600;
}

/* Dark Mode */
body.dark .settings-dropdown {
  background-color: #1f1f1f;
  border-color: #333;
}

body.dark .settings-dropdown li {
  border-bottom: 1px solid #2c2c2c;
}

body.dark .settings-dropdown a,
body.dark .settings-dropdown button {
  color: #f1f1f1;
}

body.dark .settings-dropdown a:hover,
body.dark .settings-dropdown button:hover {
  background-color: #2a2a2a;
}

body.dark .settings-dropdown .delete-btn {
  color: #ff6b6b;
}


/* Inputs */
body.dark input,
body.dark select,
body.dark textarea {
  background-color: #2a2a2a;
  color: var(--text-light);
  border: 1px solid #555;
}

/* Tables */
body.dark table,
body.dark th,
body.dark td {
  background-color: #1a1a1a;
  color: #f1f1f1;
  border-color: var(--border-dark);
}

/* Chat */
body.dark .chat-box {
  background-color: #1e1e1e;
  border-color: var(--border-dark);
}

body.dark .chat-bubble {
  background-color: #333;
  color: #f1f1f1;
}

body.dark .chat-message.mine .chat-bubble {
  background-color: var(--primary);
  color: #fff;
}

body.dark .chat-user-list li a {
  background-color: #2a2a2a;
  color: #eee;
  border-color: #444;
}

body.dark .chat-user-list li a.active {
  background-color: var(--primary);
  color: white;
}



</style>


   
  </head>
  <body>

  <div class="container">
    <div class="class-header">
      <h2>Class: <?= htmlspecialchars($course_name . ' ' . $class_name) ?></h2>
      <div class="settings-wrapper">
        <button onclick="toggleSettings()" class="settings-button">⚙️</button>
        <button onclick="toggleDarkMode()" class="settings-button" title="Toggle Theme">🌓</button>
        <div id="settingsDropdown" class="settings-dropdown">
          <ul>
            <li><a href="teacher.php">🏠 Back to Dashboard</a></li>
            <li><a href="add_student.php?class_id=<?= $class_id ?>">➕ Add Student</a></li>
            <li><a href="remove_student.php?class_id=<?= $class_id ?>">➖ Remove Student</a></li>
            <li><a href="add_group.php?class_id=<?= $class_id ?>">👥 Add Group</a></li>
            <li>
              <form action="../php/delete_class.php" method="POST" onsubmit="return confirm('Are you sure?');">
                <input type="hidden" name="class_id" value="<?= $class_id ?>">
                <button type="submit" class="delete-btn">❌ Delete Class</button>
              </form>
            </li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Tabs -->
    <div class="tab-menu">
  <button class="tab-button active" onclick="showTab('attendance', this)">Attendance</button>
  <button class="tab-button" onclick="showTab('grades', this)">Grades</button>
  <button class="tab-button" onclick="showTab('assignments', this)">Assignments</button>
  <button class="tab-button" onclick="showTab('messages', this)">Messages</button>
  <button class="tab-button" onclick="showTab('students', this)">Students</button>
<button class="tab-button" onclick="showTab('materials', this)">Materials</button>

</div>


    <!-- Attendance Tab -->
    <div class="tab-content active" id="attendance">
      <h3>Weekly Attendance</h3>
      <?php
  $monday = date('d-m-Y', strtotime('monday this week'));
  $sunday = date('d-m-Y', strtotime('sunday this week'));
?>
<p>Week: <?= $monday ?> to <?= $sunday ?></p>


      <form method="POST" action="../php/update_attendance.php">
        <input type="hidden" name="class_id" value="<?= $class_id ?>">
        <input type="hidden" name="week_start" value="<?= $week_start ?>">

        <table>
          <tr>
            <th>Student</th>
            <?php for ($i = 1; $i <= $weekly_lessons; $i++): ?>
              <th>Session <?= $i ?></th>
            <?php endfor; ?>
            <th>Attendance %</th>
          </tr>

          <?php
  $assigned_query->execute();
  $students = $assigned_query->get_result();

  while ($s = $students->fetch_assoc()):
    $student_id = $s['id'];
    $today = date('Y-m-d');
    
    // Get today's attendance per session for this student
    $att = $conn->prepare("
      SELECT session_number, status 
      FROM attendance 
      WHERE student_id = ? AND class_id = ? AND date = ?
    ");
    $att->bind_param("iis", $student_id, $class_id, $today);
    $att->execute();
    $res = $att->get_result();
    
    $todayData = [];
    while ($row = $res->fetch_assoc()) {
        $todayData[(int)$row['session_number']] = $row['status'];
    }
    


      // --- Calculate all-time attendance percentage
      $stats = $conn->prepare("
      SELECT 
        COUNT(DISTINCT date) as total_days,
        COUNT(DISTINCT CASE WHEN status = 'present' THEN CONCAT(date, '-', session_number) END) as present_sessions
      FROM attendance 
      WHERE student_id = ? AND class_id = ?
    ");
    $stats->bind_param("ii", $student_id, $class_id);
    $stats->execute();
    $result = $stats->get_result()->fetch_assoc();
    
    $total_sessions = (int)($result['total_days'] ?? 0) * $weekly_lessons;
    $total_present = (int)($result['present_sessions'] ?? 0);
    
    $percent = $total_sessions > 0 ? round(($total_present / $total_sessions) * 100) : 0;
    $color = $percent < 75 ? 'red' : 'var(--text-color)';

    
  ?>

          <tr>
            <td><?= htmlspecialchars($s['name']) ?></td>
            <?php for ($i = 1; $i <= $weekly_lessons; $i++): ?>
  <td style="text-align: center;">
    <input type="checkbox" name="attendance[<?= $student_id ?>][<?= $i ?>]" value="1"
      <?= (isset($todayData[$i]) && $todayData[$i] === 'present') ? 'checked' : '' ?>>
  </td>
<?php endfor; ?>

            <td style="color: <?= $color ?>;"><?= $percent ?>%</td>
          </tr>
          <?php endwhile; ?>
        </table>

        <br>
        <button type="submit">Save Attendance</button>
      </form>
    </div>


<!-- Grades Tab -->
<div class="tab-content" id="grades">
  <h3>Insert Grades by Semester</h3>

  <form method="GET">
  <input type="hidden" name="class_id" value="<?= $class_id ?>">
  <input type="hidden" name="tab" value="grades">
  <label for="selected_semester">Select Semester:</label>
  <select name="selected_semester" id="selected_semester" onchange="this.form.submit()">
    <option value="1" <?= ($_GET['selected_semester'] ?? '') == '1' ? 'selected' : '' ?>>Semester 1</option>
    <option value="2" <?= ($_GET['selected_semester'] ?? '') == '2' ? 'selected' : '' ?>>Semester 2</option>
    <option value="3" <?= ($_GET['selected_semester'] ?? '') == '3' ? 'selected' : '' ?>>Semester 3</option>
  </select>
</form>



  <br>

  <?php
    $selected_semester = $_GET['selected_semester'] ?? 1;

    $assigned_query->execute();
    $students = $assigned_query->get_result();

    while ($s = $students->fetch_assoc()):
      $student_id = $s['id'];

      // Fetch existing grades if they exist
      $grade_q = $conn->prepare("SELECT participation, project, exam FROM grades WHERE student_id = ? AND class_id = ? AND semester = ?");
      $grade_q->bind_param("iii", $student_id, $class_id, $selected_semester);
      $grade_q->execute();
      $grades = $grade_q->get_result()->fetch_assoc();

      $participation = $grades['participation'] ?? '';
      $project = $grades['project'] ?? '';
      $exam = $grades['exam'] ?? '';
  ?>

<form method="POST" action="../php/insert_grade.php?tab=grades" class="grades-section">
  <input type="hidden" name="student_id" value="<?= $student_id ?>">
  <input type="hidden" name="class_id" value="<?= $class_id ?>">
  <input type="hidden" name="semester" value="<?= $selected_semester ?>">

  <strong><?= htmlspecialchars($s['name']) ?> (Semester <?= $selected_semester ?>)</strong>

  <label for="participation">Participation Grade:</label>
  <input type="number" step="0.01" min="4" max="10" name="participation" value="<?= $participation ?>">

  <label for="project">Project Grade:</label>
  <input type="number" step="0.01" min="4" max="10" name="project" value="<?= $project ?>">

  <label for="exam">Exam Grade:</label>
  <input type="number" step="0.01" min="4" max="10" name="exam" value="<?= $exam ?>">

  <button type="submit">Save Grades</button>
</form>

    <hr>

  <?php endwhile; ?>
</div>



<!-- Assignments Tab -->
<div class="tab-content" id="assignments">
  <div class="assignments-section">
    <h3>Assignments</h3>

    <!-- Post New Assignment -->
    <div class="assignment-form-wrapper">
      <form method="POST" action="../php/create_assignment.php" enctype="multipart/form-data">
        <input type="hidden" name="class_id" value="<?= $class_id ?>">

        <label for="title">Title:</label>
        <input type="text" name="title" id="title" required>

        <label for="description">Description:</label>
        <textarea name="description" id="description" rows="3" required></textarea>

        <label for="due_date">Due Date:</label>
        <input type="date" name="due_date" id="due_date" required>

        <label for="attachment">Attachment (optional):</label>
        <input type="file" name="attachment" id="attachment" accept=".pdf,.doc,.docx">

        <button type="submit">Post Assignment</button>
      </form>
    </div>

    <hr>

    <h4>Posted Assignments</h4>
    <div class="assignment-grid">
      <?php
      $stmt = $conn->prepare("SELECT * FROM assignments WHERE class_id = ? ORDER BY due_date DESC");
      $stmt->bind_param("i", $class_id);
      $stmt->execute();
      $res = $stmt->get_result();

      while ($a = $res->fetch_assoc()):
      ?>
        <div class="assignment-card">
          <h4><?= htmlspecialchars($a['title']) ?></h4>
          <p><strong>Due:</strong> <?= date("F j, Y", strtotime($a['due_date'])) ?></p>
          <p class="description"><?= nl2br(htmlspecialchars($a['description'])) ?></p>
          <div class="assignment-actions">
            <a class="btn-view" href="assignment_details.php?assignment_id=<?= $a['id'] ?>&class_id=<?= $class_id ?>">View Details</a>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </div>
</div> <!-- ✅ End of assignments tab -->


<!-- Messages Tab -->
<div class="tab-content" id="messages">
  <h3>Direct Messages</h3>

  <div style="display: flex; gap: 20px;">
    <!-- Sidebar: Chat List -->
    <div style="width: 250px; border-right: 1px solid #ccc;">
      <h4>Users</h4>
      <ul class="chat-user-list">
        <?php
        $current_user = $_SESSION['user_id'];
        $chat_with = $_GET['chat_with'] ?? null;

        $user_query = $conn->prepare("
          SELECT id, name FROM users 
          WHERE id != ?
            AND (role = 'student' OR role = 'parent' OR role = 'teacher')
          ORDER BY name ASC
        ");
        $user_query->bind_param("i", $current_user);
        $user_query->execute();
        $users = $user_query->get_result();

        while ($u = $users->fetch_assoc()):
        ?>
          <li>
            <a href="?class_id=<?= $class_id ?>&tab=messages&chat_with=<?= $u['id'] ?>"
               class="chat-link <?= $chat_with == $u['id'] ? 'active' : '' ?>">
              <?= htmlspecialchars($u['name']) ?>
            </a>
          </li>
        <?php endwhile; ?>
      </ul>
    </div>

    <!-- Message Thread + Form -->
    <div style="flex: 1;">
      <?php if ($chat_with): ?>
        <div class="chat-box" id="chatBox">
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
          $messages = $msg_query->get_result();?>
          <?php if ($messages->num_rows > 0): ?>
            <?php while ($m = $messages->fetch_assoc()):
              $mine = $m['sender_id'] == $current_user;
            ?>
              <div class="chat-message <?= $mine ? 'mine' : 'theirs' ?>">
                <div class="chat-meta">
                  <span class="chat-time"><?= date("M j, g:i A", strtotime($m['timestamp'])) ?></span>
                </div>
                <div class="chat-meta">
                  <strong><?= htmlspecialchars($m['sender_name']) ?></strong>
                </div>
                <div class="chat-bubble"><?= nl2br(htmlspecialchars($m['message'])) ?></div>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <p style="text-align:center; color:#666; margin-top: 20px;">Start the conversation</p>
          <?php endif; ?>
          
          
        </div>

        <!-- Message form -->
        <form action="../php/send_dm.php" method="POST" style="margin-top: 10px;">
          <input type="hidden" name="receiver_id" value="<?= $chat_with ?>">
          <input type="hidden" name="class_id" value="<?= $class_id ?>">
          <input type="hidden" name="tab" value="messages">
          <textarea name="message" rows="3" required placeholder="Type a message..." style="width: 100%; border-radius: 6px; padding: 8px;"></textarea>
          <button type="submit" style="margin-top: 5px;">Send</button>
        </form>

        <script>
        function scrollToBottom() {
          const chatBox = document.getElementById('chatBox');
          if (chatBox) {
            chatBox.scrollTop = chatBox.scrollHeight;
          }
        }

        // Auto-scroll on page load
        document.addEventListener('DOMContentLoaded', () => {
        setTimeout(scrollToBottom, 50);
        });


        // Auto-scroll after form submission (delay to wait for message render)
        document.querySelector('form[action*="send_dm.php"]')?.addEventListener('submit', function () {
          setTimeout(scrollToBottom, 200); // Adjust delay if needed
        });
      </script>


      <?php else: ?>
        <p>Select a user to start chatting.</p>
      <?php endif; ?>
    </div>
  </div>
</div>





<!-- ✅ Students Tab -->
<div class="tab-content" id="students">
  <h3>Assigned Students</h3>
  <ul class="student-list">
    <?php while ($s = $assigned_students->fetch_assoc()): ?>
      <li><?= htmlspecialchars($s['name']) ?></li>
    <?php endwhile; ?>
  </ul>
</div>

<!-- Class Materials Tab -->
<div class="tab-content" id="materials">
  <h3>Class Materials</h3>

  <!-- Upload Form -->
  <form method="POST" action="../php/upload_material.php" enctype="multipart/form-data">
    <input type="hidden" name="class_id" value="<?= $class_id ?>">
    <label>Title:</label>
    <input type="text" name="title" required>
    <label>Upload File:</label>
    <input type="file" name="material_file" accept=".pdf,.doc,.docx,.ppt,.pptx" required>
    <button type="submit">Upload</button>
  </form>

  <hr>

  <!-- List of Uploaded Materials -->
  <h4>Uploaded Files</h4>
  <ul>
    <?php
    $mat_stmt = $conn->prepare("SELECT * FROM class_materials WHERE class_id = ? ORDER BY uploaded_at DESC");
    $mat_stmt->bind_param("i", $class_id);
    $mat_stmt->execute();
    $materials = $mat_stmt->get_result();

    while ($m = $materials->fetch_assoc()):
    ?>
      <li>
        <?= htmlspecialchars($m['title']) ?> — 
        <a href="../uploads/<?= $m['file_name'] ?>" target="_blank">Download</a>
        <form action="../php/delete_material.php" method="POST" style="display:inline;" onsubmit="return confirm('Delete this material?');">
          <input type="hidden" name="material_id" value="<?= $m['id'] ?>">
          <input type="hidden" name="class_id" value="<?= $class_id ?>">
          <input type="hidden" name="tab" value="materials">
          <button type="submit" style="color: red;">Delete</button>
        </form>
      </li>
    <?php endwhile; ?>
  </ul>
</div>

  
  

 <script>
  function showTab(tabId, buttonElement = null) {
    // Remove active class from all tabs and buttons
    document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
    document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));

    // Activate selected tab and button
    const activeTab = document.getElementById(tabId);
    if (activeTab) activeTab.classList.add('active');
    if (buttonElement) buttonElement.classList.add('active');

    // Update URL without reloading
    const url = new URL(window.location);
    url.searchParams.set('tab', tabId);
    window.history.replaceState(null, '', url);
  }

  function toggleSettings() {
    const el = document.getElementById("settingsDropdown");
    el.style.display = (el.style.display === "block") ? "none" : "block";
  }

  function toggleDarkMode() {
    document.body.classList.toggle("dark");
    localStorage.setItem("theme", document.body.classList.contains("dark") ? "dark" : "light");
  }

  // Close settings menu when clicking outside
  document.addEventListener("click", function(e) {
    if (!e.target.closest(".settings-wrapper")) {
      document.getElementById("settingsDropdown").style.display = "none";
    }
  });

  // Initial setup on page load
  window.addEventListener("DOMContentLoaded", () => {
    // Apply theme
    if (localStorage.getItem("theme") === "dark") {
      document.body.classList.add("dark");
    }

    // Load tab from URL or default to attendance
    const params = new URLSearchParams(window.location.search);
    const activeTab = params.get("tab") || "attendance";

    // Simulate click on the corresponding button
    const tabBtn = document.querySelector(`.tab-button[onclick*="'${activeTab}'"]`);
    if (tabBtn) {
      showTab(activeTab, tabBtn);
    }
  });



  </script>

  </body>
  </html>
