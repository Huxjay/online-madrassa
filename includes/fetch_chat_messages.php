<?php

header('Content-Type: application/json');

session_start();
include('../includes/db.php');


// Make sure user is logged in
$user_id = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['user_role'] ?? null;

if (!$user_id || !$user_role) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// Fetch last 50 messages with sender name and role
$query = "
    SELECT m.id, m.message, m.created_at, u.name AS sender_name, u.role AS sender_role
    FROM chat_messages m
    JOIN users u ON m.sender_id = u.id
    ORDER BY m.created_at DESC
    LIMIT 50
";

$result = $conn->query($query);

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = [
        'id' => $row['id'],
        'message' => $row['message'],
        'time' => date('H:i', strtotime($row['created_at'])),
        'sender' => $row['sender_name'],
        'role' => $row['sender_role']
    ];
}

// Return in reverse order (oldest at top)
$messages = array_reverse($messages);

echo json_encode(['status' => 'success', 'messages' => $messages]);