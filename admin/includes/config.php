<?php

if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
}
if (!defined('DB_USER')) {
    define('DB_USER', 'root');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', '');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', 'msgm_database'); // Ensure this is the correct database name
}

define('ADMIN_SESSION_KEY', 'admin_logged_in');
define('ADMIN_USERNAME_SESSION_KEY', 'admin_username');

// --- CRITICAL CHANGES BELOW ---

// Application Base URL (for frontend and shared assets like styles.css)
// This should be the path to your 'msgm_clothing' folder, relative to your web server's document root.
// If your website is accessed at http://localhost/msgm_clothing/
if (!defined('BASE_URL')) {
    define('BASE_URL', '/msgm_clothing/'); // <--- Make sure this line is exactly like this
}

// Web Root URL (for absolute redirects, full path including http://)
// This is used for header redirects and image URLs. It should match BASE_URL conceptually
// but might be used for absolute file paths on the server or full URL links.
// For image URLs in HTML, it should typically be the same as BASE_URL.
if (!defined('WEB_ROOT_URL')) {
    define('WEB_ROOT_URL', BASE_URL); // For consistency, let's keep this aligned with BASE_URL for now.
}

// You commented out session start and error reporting. Re-enable them for development!
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Define Environment (e.g., 'development' or 'production')
if (!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', 'development'); // Change to 'production' when live
}

// Set error reporting based on environment
if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0); // Turn off all error reporting in production
}