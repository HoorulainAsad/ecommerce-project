<?php
// C:\xampp\htdocs\msgm_clothing\admin\includes\database.php

require_once __DIR__ . '/config.php';


static $conn_instance = null;


if (!function_exists('getDbConnection')) { 
    function getDbConnection() {
        global $conn_instance; 

        if ($conn_instance instanceof mysqli && $conn_instance->ping()) {
            return $conn_instance;
        }

        
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
           
        }

        // Store the new connection instance globally
        $conn_instance = $conn;

        return $conn_instance;
    }
}

if (!function_exists('closeDbConnection')) { 
    function closeDbConnection() {
        global $conn_instance;

        if ($conn_instance instanceof mysqli) {
           
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


register_shutdown_function('closeDbConnection');
?>
