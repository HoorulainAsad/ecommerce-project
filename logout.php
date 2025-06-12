<?php
// logout.php (Frontend User Logout Page)

require_once __DIR__ . '/includes/functions.php'; // Ensures session is started

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to the homepage or login page after logout
redirectTo('index.php');
exit();
?>
