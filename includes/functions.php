<?php
// C:\xampp\htdocs\msgm_clothing\includes\functions.php (Frontend Functions)

// Ensure session is started for all pages
// This MUST be the very first thing in this file, with no whitespace before <?php
// Session start is now handled in admin/includes/config.php, but this check is harmless.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// IMPORTANT: Do NOT define BASE_URL here. It should be defined ONLY in admin/includes/config.php.
// The previous 'define('BASE_URL', ...)' line is REMOVED to prevent "Constant BASE_URL already defined" warnings.

// Include the main database connection file, which in turn includes your central config.php.
// This will make BASE_URL, WEB_ROOT_URL, DB_HOST, etc., available for frontend use.
require_once __DIR__ . '/../admin/includes/database.php';


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
 * Uses WEB_ROOT_URL for absolute paths, which is best for redirects.
 * @param string $location The URL to redirect to (relative to WEB_ROOT_URL).
 */
function redirectTo($location) {
    // Ensure no output buffer is active before header
    if (ob_get_length()) {
        ob_end_clean(); // Clear any accidental output
    }
    // WEB_ROOT_URL should now be defined via admin/includes/database.php -> admin/includes/config.php
    header("Location: " . WEB_ROOT_URL . $location);
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

