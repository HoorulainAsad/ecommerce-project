<?php
// includes/functions.php (Frontend Functions)

// Ensure session is started for all pages
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Define BASE_URL for consistent paths (important for assets and links)
// Adjust this if your project is not directly in htdocs root
define('BASE_URL', '/msgm_clothing/'); // Adjust this if your project lives in a subfolder

// Include database connection (assuming it's in a separate file)
require_once __DIR__ . '/database.php';

/**
 * Sanitizes input data to prevent XSS.
 * @param string $data The input string to sanitize.
 * @return string The sanitized string.
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Redirects the user to a specified location.
 * @param string $location The URL to redirect to (relative to BASE_URL).
 */
function redirectTo($location) {
    header("Location: " . BASE_URL . $location);
    exit();
}

/**
 * Checks if a user is logged in.
 * @return bool True if logged in, false otherwise.
 */
function isUserLoggedIn() {
    return isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
}

/**
 * Gets the username of the logged-in user.
 * @return string The username or 'Guest' if not logged in.
 */
function getLoggedInUsername() {
    return $_SESSION['username'] ?? 'Guest';
}

/**
 * Displays a message (e.g., success, error) to the user.
 * For now, this is a simple echo. In a real app, you might use session flashes.
 * @param string $message The message content.
 * @param string $type The type of message (success, error, info).
 */
function displayMessage($message, $type = 'info') {
    $alertClass = '';
    switch ($type) {
        case 'success':
            $alertClass = 'alert-success';
            break;
        case 'error':
            $alertClass = 'alert-danger';
            break;
        case 'info':
            $alertClass = 'alert-info';
            break;
        default:
            $alertClass = 'alert-secondary';
    }
    echo '<div class="alert ' . $alertClass . ' alert-dismissible fade show" role="alert">';
    echo htmlspecialchars($message);
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    echo '</div>';
}

// You might also need a database.php for the frontend that connects to the same database.
// Make sure your includes/database.php is correctly set up for MySQLi connection.
/*
Example includes/database.php content:
<?php
// includes/database.php
function getDbConnection() {
    $servername = "localhost";
    $username = "root"; // Your MySQL username
    $password = ""; // Your MySQL password
    $dbname = "msgm_clothing"; // Your database name

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}
?>
*/

// Add common frontend JavaScript at the end of functions.php
// This will be for things like the image slider
?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Image Slider Logic for Hero Section
        const slides = document.querySelectorAll('.hero-slide');
        const indicatorsContainer = document.querySelector('.hero-indicators');
        let currentSlide = 0;
        let slideInterval;

        function showSlide(index) {
            slides.forEach((slide, i) => {
                slide.classList.remove('active');
                if (indicatorsContainer) {
                    indicatorsContainer.children[i].classList.remove('active');
                }
            });
            slides[index].classList.add('active');
            if (indicatorsContainer) {
                indicatorsContainer.children[index].classList.add('active');
            }
        }

        function nextSlide() {
            currentSlide = (currentSlide + 1) % slides.length;
            showSlide(currentSlide);
        }

        function startSlider() {
            if (slides.length > 0) {
                showSlide(currentSlide); // Show initial slide
                slideInterval = setInterval(nextSlide, 5000); // Change slide every 5 seconds
            }
        }

        function stopSlider() {
            clearInterval(slideInterval);
        }

        if (slides.length > 0) {
            // Create indicators
            slides.forEach((_, i) => {
                const indicator = document.createElement('div');
                indicator.classList.add('hero-indicator');
                indicator.addEventListener('click', () => {
                    stopSlider();
                    currentSlide = i;
                    showSlide(currentSlide);
                    startSlider();
                });
                if (indicatorsContainer) {
                    indicatorsContainer.appendChild(indicator);
                }
            });
            startSlider();

            // Pause slider on hover
            const heroSection = document.querySelector('.hero-section');
            if (heroSection) {
                heroSection.addEventListener('mouseenter', stopSlider);
                heroSection.addEventListener('mouseleave', startSlider);
            }
        }
    });
</script>
