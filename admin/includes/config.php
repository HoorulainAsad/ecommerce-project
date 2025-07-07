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
    define('DB_NAME', 'msgm_database'); 
}

define('ADMIN_SESSION_KEY', 'admin_logged_in');
define('ADMIN_USERNAME_SESSION_KEY', 'admin_username');


if (!defined('BASE_URL')) {
    define('BASE_URL', '/msgm_clothing/'); 
}


if (!defined('WEB_ROOT_URL')) {
    define('WEB_ROOT_URL', BASE_URL); 
}


if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


if (!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', 'development'); 
}

if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}