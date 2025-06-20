<?php
// Database configuration
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'online-madrassa';

// Create a connection
$conn = new mysqli($host, $user, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Set character set to UTF-8 for Arabic support
$conn->set_charset('utf8mb4');
?>
