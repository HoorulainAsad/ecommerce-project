<?php
// admin/includes/functions.php

require_once __DIR__ . '/config.php'; // For ADMIN_SESSION_KEY and BASE_URL

// Start session if not already started
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
 * @param string $location The URL to redirect to.
 */
function redirectTo($location) {
    header("Location: " . BASE_URL . $location);
    exit();
}

/**
 * Checks if an admin user is currently logged in.
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
    // You can enhance this to use session flashes or a more complex modal system.
    // For now, it's a simple echo.
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
