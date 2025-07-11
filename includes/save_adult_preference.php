<?php
include('../includes/db.php');
session_start();

$parent_id = $_SESSION['user_id'] ?? null;

$mode = $_POST['learning_mode'] ?? '';
$specialization = $_POST['specialization'] ?? '';
$teacher_id = $_POST['assigned_teacher_id'] ?? null;
$join_meetings = $_POST['join_meetings'] ?? '0';

// === Validation ===
if (!$parent_id || !$mode || !$specialization || !$teacher_id) {
  http_response_code(400);
  echo "Missing required data.";
  exit;
}

// === Sanitize Inputs ===
$mode = trim($mode);
$specialization = trim($specialization);
$join_meetings = (int)$join_meetings;

// === Confirm User is Parent ===
$check = $conn->prepare("SELECT id FROM users WHERE id = ? AND role = 'parent'");
$check->bind_param("i", $parent_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows === 0) {
  http_response_code(403);
  echo "Unauthorized user.";
  exit;
}

// === Check if record already exists ===
$existsStmt = $conn->prepare("SELECT id FROM adult_learners WHERE parent_id = ?");
$existsStmt->bind_param("i", $parent_id);
$existsStmt->execute();
$existsResult = $existsStmt->get_result();

if ($existsResult->num_rows > 0) {
  // === UPDATE existing preference ===
  $updateStmt = $conn->prepare("
    UPDATE adult_learners
    SET learning_mode = ?, specialization = ?, assigned_teacher_id = ?, join_meetings = ?
    WHERE parent_id = ?
  ");
  $updateStmt->bind_param("ssiii", $mode, $specialization, $teacher_id, $join_meetings, $parent_id);

  if ($updateStmt->execute()) {
    echo "updated";
  } else {
    echo "error: " . $updateStmt->error;
  }

} else {
  // === INSERT new preference ===
  $insertStmt = $conn->prepare("
    INSERT INTO adult_learners (parent_id, learning_mode, specialization, assigned_teacher_id, join_meetings)
    VALUES (?, ?, ?, ?, ?)
  ");
  $insertStmt->bind_param("issii", $parent_id, $mode, $specialization, $teacher_id, $join_meetings);

  if ($insertStmt->execute()) {
    echo "inserted";
  } else {
    echo "error: " . $insertStmt->error;
  }
}
?>