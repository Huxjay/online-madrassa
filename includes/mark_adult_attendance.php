<?php
include('../includes/db.php');

$learner_id = $_POST['learner_id'] ?? null;
$status = $_POST['status'] ?? '';
$today = date('Y-m-d');

if (!$learner_id || !$status) {
  echo "❌ Invalid request";
  exit;
}

// Check if record already exists for today
$check = $conn->prepare("SELECT id FROM attendance WHERE student_id = ? AND date = ?");
$check->bind_param("is", $learner_id, $today);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
  echo "⚠ Already marked for today.";
  exit;
}

$remarks = "adult"; // Identify this record as adult learner
$created_at = date('Y-m-d H:i:s');

$stmt = $conn->prepare("INSERT INTO attendance (student_id, date, status, remarks, created_at) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("issss", $learner_id, $today, $status, $remarks, $created_at);

if ($stmt->execute()) {
  echo "✅ Attendance marked as $status";
} else {
  echo "❌ Error saving attendance";
}
?>