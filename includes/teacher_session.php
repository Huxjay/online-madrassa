<?php
// -------------------------------
// Secure Session Initialization
// -------------------------------
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_httponly', 1); // Prevent JS access to cookies
ini_set('session.cookie_secure', isset($_SERVER['HTTPS'])); // Use only if HTTPS
ini_set('session.use_only_cookies', 1); // Force cookies only

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// -------------------------------
// Prevent Caching
// -------------------------------
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
header("Pragma: no-cache");

// -------------------------------
// Access Control: Only Teachers
// -------------------------------
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header("Location: ../../index.html");
    exit();
}

// -------------------------------
// Auto Logout After 15 Minutes
// -------------------------------
$timeout = 900; // 15 minutes

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout) {
    session_unset();
    session_destroy();
    header("Location: ../../index.html?timeout=1");
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time();

// -------------------------------
// Regenerate Session ID Every 5 Min
// -------------------------------
if (!isset($_SESSION['CREATED'])) {
    $_SESSION['CREATED'] = time();
} elseif (time() - $_SESSION['CREATED'] > 300) {
    session_regenerate_id(true);
    $_SESSION['CREATED'] = time();
}
?>