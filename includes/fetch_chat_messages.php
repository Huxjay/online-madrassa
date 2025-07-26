<?php
header('Content-Type: application/json');
session_start();
include('../includes/db.php');

// Validate session
$user_id = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['user_role'] ?? null;

if (!$user_id || !$user_role) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// Fetch the last 50 messages with reply info (if any)
$query = "
    SELECT 
        m.id, 
        m.message, 
        m.created_at, 
        m.reply_to_id,
        u.name AS sender_name, 
        u.role AS sender_role,
        r.message AS reply_text,
        ru.name AS reply_sender
    FROM chat_messages m
    JOIN users u ON m.sender_id = u.id
    LEFT JOIN chat_messages r ON m.reply_to_id = r.id
    LEFT JOIN users ru ON r.sender_id = ru.id
    ORDER BY m.created_at DESC
    LIMIT 50
";

$result = $conn->query($query);
$messages = [];

while ($row = $result->fetch_assoc()) {
    $message = [
        'id' => $row['id'],
        'message' => $row['message'],
        'time' => date('H:i', strtotime($row['created_at'])),
        'sender' => $row['sender_name'],
        'role' => $row['sender_role']
    ];

    // If replying to another message, include that reference
    if (!empty($row['reply_to_id']) && $row['reply_text'] && $row['reply_sender']) {
        $message['reply_to_text'] = "{$row['reply_sender']}: {$row['reply_text']}";
    }

    $messages[] = $message;
}

// Reverse so oldest messages are on top
$messages = array_reverse($messages);

echo json_encode([
    'status' => 'success',
    'messages' => $messages
]);