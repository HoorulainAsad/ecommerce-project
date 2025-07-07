<?php
//admin/includes/functions.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!defined('ADMIN_ID_SESSION_KEY')) {
    define('ADMIN_ID_SESSION_KEY', 'admin_id');
}
if (!defined('ADMIN_ROLE_SESSION_KEY')) {
    define('ADMIN_ROLE_SESSION_KEY', 'admin_role');
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function redirectTo($location) {
    header("Location: " . BASE_URL . $location);
    exit();
}

function redirectToAdmin($location) {
    header("Location: " . BASE_URL . "admin/" . $location);
    exit();
}

function isLoggedIn() {
    return isset($_SESSION[ADMIN_SESSION_KEY]) && $_SESSION[ADMIN_SESSION_KEY] === true;
}

function isSuperAdmin() {
    return isLoggedIn() && isset($_SESSION[ADMIN_ROLE_SESSION_KEY]) && $_SESSION[ADMIN_ROLE_SESSION_KEY] === 'super_admin';
}

function displayMessage($message, $type = 'info') {
    echo "<div class='message-box message-box-$type'>";
    echo htmlspecialchars($message) . "</div>";
}
