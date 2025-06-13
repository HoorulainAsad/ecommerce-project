<?php
// includes/functions.php

// Ensure session is started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Define BASE_URL
define('BASE_URL', '/msgm_clothing/');

// Include DB connection
require_once __DIR__ . '/database.php';

/**
 * Sanitizes input data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Redirects user
 */
function redirectTo($location) {
    header("Location: " . BASE_URL . $location);
    exit();
}

/**
 * Checks login status
 */
function isUserLoggedIn() {
    return isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
}

/**
 * Gets logged-in username
 */
function getLoggedInUsername() {
    return $_SESSION['username'] ?? 'Guest';
}

/**
 * Displays a message
 */
function displayMessage($message, $type = 'info') {
    $alertClass = '';
    switch ($type) {
        case 'success': $alertClass = 'alert-success'; break;
        case 'error': $alertClass = 'alert-danger'; break;
        case 'info': $alertClass = 'alert-info'; break;
        default: $alertClass = 'alert-secondary';
    }
    echo '<div class="alert ' . $alertClass . ' alert-dismissible fade show" role="alert">';
    echo htmlspecialchars($message);
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    echo '</div>';
}
