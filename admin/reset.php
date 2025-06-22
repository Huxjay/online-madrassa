<?php
// reset.php

include('../includes/admin_session.php');

// Array of table names in the correct truncate order
$tables = [
    'attendance',
    'submissions',
    'class_enrollments',
    'teachers',
    'students',
    'messages',
    'users',
    'locations',
    'videos',
    'classes',
    // Add more if needed: 'lessons', 'assignments', 'chats'
];

$errors = [];

$conn->query("SET FOREIGN_KEY_CHECKS = 0");

foreach ($tables as $table) {
    $truncate = $conn->query("TRUNCATE TABLE $table");
    if (!$truncate) {
        $errors[] = "❌ Failed to truncate table $table: " . $conn->error;
    }
}

$conn->query("SET FOREIGN_KEY_CHECKS = 1");

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Database - Online Madrassa</title>
  <style>
    body {
      background: #1e272e;
      color: #fff;
      font-family: 'Segoe UI', sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      flex-direction: column;
    }
    .message {
      background: rgba(0,255,100,0.1);
      padding: 20px 30px;
      border-left: 5px solid #00ff99;
      border-radius: 8px;
      font-size: 18px;
      margin-bottom: 20px;
    }
    .error {
      background: rgba(255,0,0,0.15);
      border-left: 5px solid #ff6b6b;
      color: #ff6b6b;
    }
    a {
      color: #00cec9;
      text-decoration: none;
      margin-top: 20px;
      font-size: 16px;
    }
  </style>
</head>
<body>
  <?php if (empty($errors)): ?>
    <div class="message">✅ All data reset successfully.</div>
  <?php else: ?>
    <div class="message error">
      <strong>Some errors occurred:</strong><br>
      <?php foreach ($errors as $err): echo $err . "<br>"; endforeach; ?>
    </div>
  <?php endif; ?>
  <a href="index.php">⬅ Back to Home</a>
</body>
</html>