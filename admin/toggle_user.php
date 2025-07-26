<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('../includes/db.php');

// Ensure only admin can perform this
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo "Access denied";
    exit;
}

$user_id = $_GET['id'] ?? null;
$status = $_GET['status'] ?? null;

if (!$user_id || !is_numeric($status)) {
    echo "Invalid request";
    exit;
}

// Update the user's approved status
$stmt = $conn->prepare("UPDATE users SET approved = ? WHERE id = ?");
$stmt->bind_param("ii", $status, $user_id);
$stmt->execute();
$stmt->close();

// Redirect back correctly via admin panel
header("Location: index.php?page=users");
exit;