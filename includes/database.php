<?php
// C:\xampp\htdocs\msgm_clothing\includes\database.php (YOUR MAIN DATABASE CONFIG)

// Database connection constants - wrapped in defined() for safety
// This ensures constants are defined only once, even if included multiple times.
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost'); // Your MySQL host
}
if (!defined('DB_USER')) {
    define('DB_USER', 'root');      // Your MySQL username
}
if (!defined('DB_PASS')) {
    define('DB_PASS', '');          // Your MySQL password (default for XAMPP is empty)
}
if (!defined('DB_NAME')) {
    define('DB_NAME', 'msgm_database'); // Your database name (ensure this is correct for all parts of your app)
}

/**
 * Establishes and returns a single database connection.
 * Uses a static variable to ensure only one connection is made per request.
 * @return mysqli The database connection object.
 * @throws Exception If database connection fails.
 */
$conn->set_charset("utf8mb4");
?>