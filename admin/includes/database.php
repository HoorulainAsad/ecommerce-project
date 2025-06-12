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
 * @return mysqli The database connection object.
 */
function getDbConnection() {
    global $conn_instance; // Access the static variable in the global scope

    // If connection instance already exists and is not null, return it
    if ($conn_instance instanceof mysqli && $conn_instance->ping()) {
        return $conn_instance;
    }

    // Otherwise, create a new connection
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // Check connection
    if ($conn->connect_error) {
        // Log this error in production, but for development, we die with the error.
        error_log("Database Connection failed: " . $conn->connect_error);
        die("Database Connection failed: " . $conn->connect_error);
    }

    // Set charset for proper data handling
    $conn->set_charset("utf8mb4");

    // Store the new connection instance
    $conn_instance = $conn;

    return $conn_instance;
}

/**
 * Explicitly closes the global database connection.
 * Call this once at the very end of your script if you need to.
 */
function closeDbConnection() {
    global $conn_instance;
    if ($conn_instance instanceof mysqli && $conn_instance->ping()) {
        $conn_instance->close();
        $conn_instance = null; // Reset the instance
    }
}

// Optional: Register closeDbConnection to run at script shutdown
// This is generally a good practice for web requests.
register_shutdown_function('closeDbConnection');

?>
