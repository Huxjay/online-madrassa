<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? 0;

$response = [
    "unread" => 0,
    "items" => []
];

if ($user_id) {
    $countQ = $conn->prepare("SELECT COUNT(*) AS unread FROM notifications WHERE user_id = ? AND is_read = 0");
    $countQ->bind_param("i", $user_id);
    $countQ->execute();
    $countQ->bind_result($unread);
    $countQ->fetch();
    $countQ->close();
    $response['unread'] = $unread;

    $stmt = $conn->prepare("SELECT message, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        $response['items'][] = [
            "message" => $row['message'],
            "time" => date("M j, H:i", strtotime($row['created_at']))
        ];
    }

    $stmt->close();
}

echo json_encode($response);