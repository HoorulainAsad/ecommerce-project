<?php
// includes/functions.php (Frontend Functions)

// Ensure session is started for all pages
// This MUST be the very first thing in this file, with no whitespace before <?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Define BASE_URL for consistent paths (important for assets and links)
// Adjust this if your project is not directly in htdocs root
define('BASE_URL', '/msgm_clothing/'); // Adjust this if your project lives in a subfolder

// Include database connection (assuming it's in a separate file)
require_once __DIR__ . '/database.php'; // Path should be relative to functions.php

/**
 * Sanitizes input data to prevent XSS.
 * @param string $data The input string to sanitize.
 * @return string The sanitized string.
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Redirects the user to a specified location.
 * @param string $location The URL to redirect to (relative to BASE_URL).
 */
function redirectTo($location) {
    // Ensure no output buffer is active before header
    if (ob_get_length()) {
        ob_end_clean(); // Clear any accidental output
    }
    header("Location: " . BASE_URL . $location);
    exit();
}

/**
 * Checks if a user is logged in.
 * @return bool True if logged in, false otherwise.
 */
function isUserLoggedIn() {
    // Check if session is active and user_id is set
    return (session_status() == PHP_SESSION_ACTIVE) && isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
}

/**
 * Gets the username of the logged-in user.
 * @return string The username or 'Guest' if not logged in.
 */
function getLoggedInUsername() {
    return (session_status() == PHP_SESSION_ACTIVE && isset($_SESSION['username'])) ? $_SESSION['username'] : 'Guest';
}

/**
 * Displays a message (e.g., success, error) to the user using Bootstrap alerts.
 * @param string $message The message content.
 * @param string $type The type of message (success, error, info).
 */
function displayMessage($message, $type = 'info') {
    $alertClass = '';
    switch ($type) {
        case 'success':
            $alertClass = 'alert-success';
            break;
        case 'error':
            $alertClass = 'alert-danger';
            break;
        case 'info':
            $alertClass = 'alert-info';
            break;
        default:
            $alertClass = 'alert-secondary';
    }
    echo '<div class="alert ' . $alertClass . ' alert-dismissible fade show" role="alert">';
    echo htmlspecialchars($message);
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    echo '</div>';
}

// Add common frontend JavaScript for hero slider (should be within HTML body or footer)
// Moved this part to the footer to ensure it's loaded after DOM.
// If it's directly included in functions.php, it will be echoed too early.
?>
