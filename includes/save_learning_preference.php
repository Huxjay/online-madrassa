<?php
include('../includes/db.php');
session_start();

$parent_id = $_SESSION['user_id'] ?? null;

$student_id = $_POST['student_id'] ?? null;
$mode = $_POST['learning_mode'] ?? '';
$specialization = $_POST['specialization'] ?? '';
$teacher_id = $_POST['assigned_teacher_id'] ?? null;
$join_meetings = $_POST['join_meetings'] ?? '0';

// Basic validation
if (!$parent_id || !$student_id || !$mode || !$specialization || !$teacher_id) {
  http_response_code(400);
  echo "Missing required data.";
  exit;
}

// Sanitize input
$mode = trim($mode);
$specialization = trim($specialization);
$join_meetings = (int)$join_meetings;

// Check if this student belongs to the parent
$check = $conn->prepare("SELECT id FROM students WHERE id = ? AND parent_id = ?");
$check->bind_param("ii", $student_id, $parent_id);
$check->execute();
$check_result = $check->get_result();

if ($check_result->num_rows === 0) {
  http_response_code(403);
  echo "Unauthorized or invalid student.";
  exit;
}

// Update preferences
$stmt = $conn->prepare("
  UPDATE students
  SET learning_mode = ?, specialization = ?, assigned_teacher_id = ?, join_meetings = ?
  WHERE id = ? AND parent_id = ?
");
$stmt->bind_param("ssiiii", $mode, $specialization, $teacher_id, $join_meetings, $student_id, $parent_id);

if ($stmt->execute()) {
  echo "success";
} else {
  echo "error: " . $stmt->error;
}
?>