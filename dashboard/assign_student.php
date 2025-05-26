<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../php/db.php';

// Fetch students with names from users table
$students = $conn->query("
  SELECT students.id AS student_id, users.name 
  FROM students 
  JOIN users ON students.user_id = users.id 
  ORDER BY users.name
");

$class_numbers = range(1, 12);
$class_letters = ['A', 'B', 'C'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Assign Student to Class</title>
  <link rel="stylesheet" href="../css/admin.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    .form-box {
      max-width: 600px;
      margin: 40px auto;
      background: #fff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }
    .form-box h2 {
      text-align: center;
      margin-bottom: 20px;
    }
  </style>
</head>
<body>

<div class="form-box">
  <h2>Assign Student to Class</h2>

  <?php if (!empty($_SESSION['success'])): ?>
    <p style="color:green;"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p>
  <?php elseif (!empty($_SESSION['error'])): ?>
    <p style="color:red;"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
  <?php endif; ?>

  <form action="process_assign_student.php" method="POST">
    <label for="student">Select Student:</label>
    <select name="student_id" required>
      <option value="">-- Select Student --</option>
      <?php while ($s = $students->fetch_assoc()): ?>
        <option value="<?= $s['student_id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
      <?php endwhile; ?>
    </select>

    <label>Class Number:</label>
    <select name="class_number" required>
      <option value="">Select Class Number</option>
      <?php foreach ($class_numbers as $num): ?>
        <option value="<?= $num ?>"><?= $num ?></option>
      <?php endforeach; ?>
    </select>

    <label>Class Letter:</label>
    <select name="class_letter" required>
      <option value="">Select Letter</option>
      <?php foreach ($class_letters as $letter): ?>
        <option value="<?= $letter ?>"><?= $letter ?></option>
      <?php endforeach; ?>
    </select>

    <button type="submit">Assign Class</button>
  </form>

  <br>

</div>

</body>
</html>
