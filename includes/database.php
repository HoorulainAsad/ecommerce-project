<?php
// C:\xampp\htdocs\msgm_clothing\includes\database.php (YOUR MAIN DATABASE CONFIG)


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

$conn->set_charset("utf8mb4");
?>