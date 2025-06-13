<?php
// admin/includes/database.php

require_once __DIR__ . '/config.php'; // Ensure config is loaded

/**
 * Stores the single database connection instance.
 * Using a static variable to ensure only one connection is opened per request.
 * @var mysqli|null
 */
static $conn_instance = null;

/**
 * Establishes and returns a new database connection (or returns the existing one).
 * This function ensures only one mysqli connection object is created per script execution.
 * @return mysqli The database connection object.
 */
function getDbConnection() {
    global $conn_instance; // Access the static variable in the global scope

    // If connection instance already exists, check if it's still alive.
    // If it's an instance of mysqli and still pings successfully, return it.
    if ($conn_instance instanceof mysqli && $conn_instance->ping()) {
        return $conn_instance;
    }

    // If connection is not active or doesn't exist, create a new one
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // Check connection
    if ($conn->connect_error) {
        // Log this error in production, but for development, we die with the error.
        error_log("Database Connection failed: " . $conn->connect_error);
        die("Database Connection failed: " . $conn->connect_error);
    }

    // Set charset for proper data handling
    $conn->set_charset("utf8mb4");

    // Store the new connection instance globally
    $conn_instance = $conn;

    return $conn_instance;
}

/**
 * Explicitly closes the global database connection.
 * This function is registered to run at script shutdown.
 */
function closeDbConnection() {
    global $conn_instance;

    if ($conn_instance instanceof mysqli) {
        try {
            $conn_instance->close();
        } catch (Exception $e) {
            // Connection is already closed
            error_log("Connection cleanup error: " . $e->getMessage());
        }
    }
    $conn_instance = null;
}

// Always register cleanup function to run at script shutdown
register_shutdown_function('closeDbConnection');
?>
