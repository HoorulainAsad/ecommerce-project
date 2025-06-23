<?php
// C:\xampp\htdocs\msgm_clothing\admin\includes\functions.php

// 1. Load the central configuration file FIRST.
// This ensures all constants like ADMIN_SESSION_KEY, BASE_URL, WEB_ROOT_URL are available.
require_once __DIR__ . '/config.php';

// 2. Load the database connection handler (which itself loads config.php).
// This makes getDbConnection() available.
require_once __DIR__ . '/database.php';


// Session start is handled in config.php now, so it's not strictly needed here,
// but leaving the check is harmless if config.php isn't always the first file loaded.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Sanitizes input data to prevent XSS.
 * @param string $data The input string to sanitize.
 * @return string The sanitized string.
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8'); // Use ENT_QUOTES for single and double quotes
    return $data;
}

/**
 * Redirects the user to a specified location.
 * Uses WEB_ROOT_URL for absolute paths, which is best for redirects.
 * @param string $location The URL to redirect to (relative path after WEB_ROOT_URL).
 */
function redirectTo($location) {
    header("Location: " . BASE_URL . $location);
    exit();
}

function redirectToAdmin($location) {
    // Assuming admin specific pages are always under /msgm_clothing/admin/
    // and $location is something like 'login.php' or 'viewproducts.php'
    header("Location: " . BASE_URL . "admin/" . $location);
    exit();
}

/**
 * Checks if an admin user is currently logged in.
 * Relies on ADMIN_SESSION_KEY being defined in config.php.
 * @return bool True if logged in, false otherwise.
 */
function isLoggedIn() {
    return isset($_SESSION[ADMIN_SESSION_KEY]) && $_SESSION[ADMIN_SESSION_KEY] === true;
}

/**
 * Displays a message box (simple div for now, could be a modal).
 * @param string $message The message to display.
 * @param string $type The type of message (e.g., 'success', 'error', 'info').
 */
function displayMessage($message, $type = 'info') {
    echo "<div class='message-box message-box-$type'>";
    echo htmlspecialchars($message) . "</div>";
}


// Add this JavaScript at the end of functions.php for mobile sidepanel toggle
?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const hamburger = document.querySelector('.hamburger');
        const sidepanel = document.querySelector('.sidepanel');
        const mainContentArea = document.querySelector('.main-content-area');

        if (hamburger && sidepanel && mainContentArea) {
            hamburger.addEventListener('click', function() {
                sidepanel.classList.toggle('hidden-mobile');
            });

            // Hide sidepanel if clicked outside on mobile (optional, but good UX)
            mainContentArea.addEventListener('click', function(event) {
                if (window.innerWidth <= 768 && !sidepanel.contains(event.target) && !hamburger.contains(event.target) && !sidepanel.classList.contains('hidden-mobile')) {
                    sidepanel.classList.add('hidden-mobile');
                }
            });

            // Adjust sidepanel visibility on resize (for desktop vs. mobile)
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    sidepanel.classList.remove('hidden-mobile'); // Always visible on desktop
                } else {
                    sidepanel.classList.add('hidden-mobile'); // Hidden by default on mobile load
                }
            });

            // Initial state check for mobile
            if (window.innerWidth <= 768) {
                sidepanel.classList.add('hidden-mobile');
            }
        }
    });
</script>