<?php
// admin/includes/config.php

// Database Credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'msgm_database'); // Ensure this matches your database name

// Admin User Session Keys (CRITICAL for login functionality)
define('ADMIN_SESSION_KEY', 'admin_logged_in');
define('ADMIN_USERNAME_SESSION_KEY', 'admin_username');

// =======================================================================
// Base URL Definitions:
//
// 1. BASE_URL: For links and assets within the ADMIN PANEL itself.
//    This should be the web-accessible path to your 'admin' folder.
//    Based on your XAMPP setup (C:\xampp\htdocs\msgm_clothing\admin\),
//    this will be /msgm_clothing/admin/
define('BASE_URL', '/msgm_clothing/admin/');

// 2. WEB_ROOT_URL: For images and assets that are referenced from the
//    main website's root. This is used in the admin panel to correctly
//    display product images stored relative to the main site's root.
//    This should be the web-accessible path to your 'msgm_clothing' folder.
define('WEB_ROOT_URL', '/msgm_clothing/');
// =======================================================================

// Session cookie settings (optional but good practice for security)
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
session_set_cookie_params([
    'lifetime' => 3600, // 1 hour
    'path' => '/',
    'domain' => '', // Leave empty for localhost, or set to your domain (e.g., 'yourdomain.com')
    'secure' => false, // Set to true if using HTTPS
    'httponly' => true,
    'samesite' => 'Lax'
]);

?>
