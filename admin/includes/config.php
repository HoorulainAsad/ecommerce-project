<?php
// admin/includes/config.php

// Database Credentials
define('DB_HOST', 'localhost'); // Your database host
define('DB_USER', 'root');     // Your database username
define('DB_PASS', '');         // Your database password
define('DB_NAME', 'msgm_database'); // Your database name

// Admin User Session Key
define('ADMIN_SESSION_KEY', 'admin_logged_in');
define('ADMIN_USERNAME_SESSION_KEY', 'admin_username');

// Base URL for redirects (adjust if your admin panel is in a subfolder)
// Example: if your admin folder is at http://localhost/ecommerce/admin/
// define('BASE_URL', '/ecommerce/admin/');
define('BASE_URL', '/msgm_clothing/admin/'); // Adjust this based on your server setup
?>
