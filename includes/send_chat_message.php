<?php
header('Content-Type: application/json');


session_start();
include('../includes/db.php');

// Make sure user is logged in
$user_id = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['user_role'] ?? null;


// Access check
if (!$user_id || $user_role !== 'parent') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);
$message = trim($data['message'] ?? '');

if (empty($message)) {
    echo json_encode(['status' => 'error', 'message' => 'Message cannot be empty']);
    exit;
}

// Insert message into database
$stmt = $conn->prepare("INSERT INTO chat_messages (sender_id, message) VALUES (?, ?)");
$stmt->bind_param("is", $user_id, $message);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}
?>