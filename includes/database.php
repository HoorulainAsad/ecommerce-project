<?php
// includes/database.php (Frontend)

// Database connection constants for the frontend application
// Make sure these match your actual MySQL database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');    // Your MySQL username (default for XAMPP)
define('DB_PASS', '');        // Your MySQL password (default for XAMPP is empty)
define('DB_NAME', 'msgm_database'); // Your database name

/**
 * Establishes and returns a new database connection.
 * @return mysqli The database connection object.
 */
function getDbConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // Check connection
    if ($conn->connect_error) {
        // Log the error in production, but for development, we die with the error.
        error_log("Database Connection failed: " . $conn->connect_error);
        die("Database Connection failed: " . $conn->connect_error);
    }
    return $conn;
}
?>
