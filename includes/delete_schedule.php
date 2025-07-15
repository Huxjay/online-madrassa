<?php
session_start();
include 'db.php';

$teacher_id = $_SESSION['user_id'] ?? null;
$schedule_id = $_POST['id'] ?? null;

if (!$teacher_id || !$schedule_id) {
    http_response_code(400);
    echo "Missing teacher ID or schedule ID.";
    exit;
}

// Make sure the schedule belongs to this teacher
$stmt = $conn->prepare("DELETE FROM class_schedule WHERE id = ? AND teacher_id = ?");
$stmt->bind_param("ii", $schedule_id, $teacher_id);

if ($stmt->execute()) {
    echo "success";
} else {
    http_response_code(500);
    echo "Error deleting schedule: " . $stmt->error;
}

$stmt->close();