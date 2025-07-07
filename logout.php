<?php
// logout.php (Frontend User Logout Page)

require_once __DIR__ . '/includes/functions.php'; // Ensures session is started

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$_SESSION = array();

session_destroy();

redirectTo('index.php');
exit();
?>
