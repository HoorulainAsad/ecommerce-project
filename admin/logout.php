<?php
// admin/logout.php

require_once __DIR__ . '/includes/functions.php'; // Includes config and starts session

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to the login page
redirectTo('login.php');
?>
