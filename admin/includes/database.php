<?php
// C:\xampp\htdocs\msgm_clothing\admin\includes\database.php
// THIS FILE HANDLES THE GLOBAL DATABASE CONNECTION

// Always load the central configuration first to get DB constants and other settings.
require_once __DIR__ . '/config.php';

/**
 * Stores the single database connection instance.
 * Using a global static variable to ensure only one connection is opened per request.
 * @var mysqli|null
 */
// Use global here to ensure it's accessible across different includes if needed.
static $conn_instance = null;

/**
 * Establishes and returns a new database connection (or returns the existing one).
 * This function ensures only one mysqli connection object is created per script execution.
 * It's wrapped in function_exists() to prevent "Cannot redeclare function" fatal errors.
 * @return mysqli The database connection object.
 */
if (!function_exists('getDbConnection')) { // Crucial check to prevent redeclaration
    function getDbConnection() {
        global $conn_instance; // Access the global static variable

        // If connection instance already exists and is still active, return it.
        if ($conn_instance instanceof mysqli && $conn_instance->ping()) {
            return $conn_instance;
        }

        // If connection is not active or doesn't exist, create a new one
        // @ suppresses connection errors, handle them manually below.
        $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        // Check connection
        if ($conn->connect_error) {
            error_log("Database Connection failed: " . $conn->connect_error);

            if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
                die("Database Connection failed: " . $conn->connect_error);
            } else {
                die("We are experiencing technical difficulties. Please try again later.");
            }
        }

        // Set charset for proper data handling
        if ($conn->set_charset("utf8mb4") === false) {
            error_log("Failed to set character set: " . $conn->error);
            // Optionally, die or throw an exception if charset cannot be set
        }

        // Store the new connection instance globally
        $conn_instance = $conn;

        return $conn_instance;
    }
}

/**
 * Explicitly closes the global database connection.
 * This function is registered to run at script shutdown.
 */
if (!function_exists('closeDbConnection')) { // Crucial check to prevent redeclaration
    function closeDbConnection() {
        global $conn_instance;

        if ($conn_instance instanceof mysqli) {
            // Only try to close if it's not already closed and is still active
            if ($conn_instance->thread_id) { // Check if the connection is still active
                try {
                    $conn_instance->close();
                } catch (Exception $e) {
                    error_log("Database connection close error: " . $e->getMessage());
                }
            }
        }
        $conn_instance = null; // Ensure the variable is reset after closing
    }
}

// Always register cleanup function to run at script shutdown
// This ensures the database connection is properly closed when the script finishes.
register_shutdown_function('closeDbConnection');
?>