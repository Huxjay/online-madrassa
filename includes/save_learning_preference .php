<?php
include('db.php');
session_start();

$student_id = $_POST['student_id'];
$teacher_id = $_POST['assigned_teacher_id'];
$mode = $_POST['learning_mode'];
$join_meetings = $_POST['join_meetings'];

// Save everything in one update
$stmt = $conn->prepare("UPDATE students SET assigned_teacher_id = ?, learning_mode = ?, join_meetings = ? WHERE id = ?");
$stmt->bind_param("issi", $teacher_id, $mode, $join_meetings, $student_id);
$stmt->execute();
$stmt->close();

echo "success";
?>