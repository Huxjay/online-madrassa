<?php
include 'includes/db.php';

// Fetch all users
$result = $conn->query("SELECT id, password FROM users");
while ($user = $result->fetch_assoc()) {
    $hashedPassword = password_hash($user['password'], PASSWORD_DEFAULT);
    $conn->query("UPDATE users SET password = '$hashedPassword' WHERE id = {$user['id']}");
}

echo "Passwords updated successfully.";
$conn->close();
?>
