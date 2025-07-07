<?php
// admin/logout.php

require_once __DIR__ . '/includes/functions.php'; // Includes config and starts session


$_SESSION = array();

session_destroy();

redirectToAdmin('login.php');
?>
