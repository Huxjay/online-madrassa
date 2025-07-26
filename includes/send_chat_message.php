<?php
header('Content-Type: application/json');
session_start();
include('../includes/db.php');

// Check if user is logged in
$user_id = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['user_role'] ?? null;

if (!$user_id || !in_array($user_role, ['parent', 'teacher', 'admin'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

// Read incoming JSON
$data = json_decode(file_get_contents('php://input'), true);

// Validate input
$message = trim($data['message'] ?? '');
$reply_to_id = isset($data['reply_to_id']) && is_numeric($data['reply_to_id']) ? intval($data['reply_to_id']) : null;

if (empty($message)) {
    echo json_encode(['status' => 'error', 'message' => 'Message cannot be empty']);
    exit;
}

// Insert into DB
if ($reply_to_id) {
    $stmt = $conn->prepare("INSERT INTO chat_messages (sender_id, message, reply_to_id) VALUES (?, ?, ?)");
    $stmt->bind_param("isi", $user_id, $message, $reply_to_id);
} else {
    $stmt = $conn->prepare("INSERT INTO chat_messages (sender_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $message);
}

// Execute and return result
if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'message_id' => $stmt->insert_id
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}