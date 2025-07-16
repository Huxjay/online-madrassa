<?php
include 'db.php';

$student_id = $_GET['id'] ?? 0;
$response = ['success' => false];

$stmt = $conn->prepare("SELECT id, name, age, gender, specialization FROM students WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $response['success'] = true;
    $response['child'] = $row;
}

header('Content-Type: application/json');
echo json_encode($response);