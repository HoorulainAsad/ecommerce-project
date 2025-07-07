<?php
// C:\xampp\htdocs\msgm_clothing\includes\functions.php (Frontend Functions)


if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

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


function redirectTo($location) {
    if (ob_get_length()) {
        ob_end_clean(); // Clear any accidental output
    }
    header("Location: " . WEB_ROOT_URL . $location);
    exit();
}


function isUserLoggedIn() {
    // Check if session is active and user_id is set
    return (session_status() == PHP_SESSION_ACTIVE) && isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
}


function getLoggedInUsername() {
    return (session_status() == PHP_SESSION_ACTIVE && isset($_SESSION['username'])) ? $_SESSION['username'] : 'Guest';
}


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

