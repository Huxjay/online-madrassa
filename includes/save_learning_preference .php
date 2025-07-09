<?php
include('db.php');
session_start();

$student_id = $_POST['student_id'];
$teacher_id = $_POST['assigned_teacher_id'];
$mode = $_POST['learning_mode'];
$join_meetings = $_POST['join_meetings'];

// Update assigned teacher
$conn->query("UPDATE students SET assigned_teacher_id = $teacher_id WHERE id = $student_id");

// Save mode and meetings (optional: you can add a preferences table)
$conn->query("UPDATE students SET learning_mode = '$mode', join_meetings = $join_meetings WHERE id = $student_id");

// (Optional: add feedback, email, or log entry)

echo "success";