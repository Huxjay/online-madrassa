<?php
include('../includes/db.php');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    echo "<p style='color:red;'>Access Denied</p>";
    exit;
}

$teacher_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $topic = trim($_POST['topic']);
    $description = trim($_POST['description']);
    $date = $_POST['date'];
    $start = $_POST['start_time'];
    $end = $_POST['end_time'];
    $link = trim($_POST['meeting_link']);

    if (!$topic || !$date || !$start || !$end || !$link) {
        $error = 'Please fill all required fields.';
    } else {
        $stmt = $conn->prepare("INSERT INTO online_classes (teacher_id, topic, description, date, start_time, end_time, meeting_link) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssss", $teacher_id, $topic, $description, $date, $start, $end, $link);
        if ($stmt->execute()) {
            $success = "Class created successfully!";
        } else {
            $error = "Failed to create class.";
        }
    }
}
?>

<!-- HTML Form -->
<div class="form-container">
  <h2>📅 Create Online Class</h2>

  <?php if ($success): ?>
    <p class="success"><?= $success ?></p>
  <?php endif; ?>
  <?php if ($error): ?>
    <p class="error"><?= $error ?></p>
  <?php endif; ?>

  <form method="POST">
    <input type="text" name="topic" placeholder="Class Topic *" required />
    <textarea name="description" placeholder="Description (optional)" rows="3"></textarea>
    <input type="date" name="date" required />
    <input type="time" name="start_time" required />
    <input type="time" name="end_time" required />
    <input type="url" name="meeting_link" placeholder="Meeting Link (Zoom/Google Meet)" required />
    <button type="submit">Add Class</button>
  </form>
</div>

<!-- Styling -->
<style>
  .form-container {
    max-width: 600px;
    margin: 30px auto;
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(10px);
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.1);
    color: #333;
  }
  .form-container h2 {
    text-align: center;
    margin-bottom: 16px;
  }
  .form-container form {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }
  .form-container input,
  .form-container textarea {
    padding: 10px;
    border-radius: 8px;
    border: none;
    font-size: 14px;
  }
  .form-container button {
    background: #007bff;
    color: white;
    padding: 10px;
    font-size: 15px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
  }
  .form-container button:hover {
    background: #0056b3;
  }
  .success {
    color: green;
    text-align: center;
  }
  .error {
    color: red;
    text-align: center;
  }
</style>