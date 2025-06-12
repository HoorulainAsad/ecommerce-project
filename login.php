<?php
// login.php (Frontend User Login Page)

require_once __DIR__ . '/includes/header.php'; // Includes functions.php and starts session
require_once __DIR__ . '/classes/UserFrontendManager.php';

// If user is already logged in, redirect to home or profile
if (isUserLoggedIn()) {
    redirectTo('index.php');
}

$userFrontendManager = new UserFrontendManager();
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? ''; // Do not sanitize password before hashing/verification

    if (empty($email) || empty($password)) {
        $message = "Please enter both email and password.";
        $message_type = 'error';
    } else {
        $user = $userFrontendManager->authenticateUser($email, $password);
        if ($user) {
            // Set session variables for logged-in user
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            redirectTo('index.php'); // Redirect to homepage or user dashboard
        } else {
            $message = "Invalid email or password.";
            $message_type = 'error';
        }
    }
}
?>

<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card p-4 shadow-lg" style="max-width: 450px; width: 100%; border-radius: 15px;">
        <h2 class="card-title text-center mb-4 text-primary-custom">Login to Your Account</h2>

        <?php if ($message): ?>
            <?php displayMessage($message, $message_type); ?>
        <?php endif; ?>

        <form action="<?php echo BASE_URL; ?>login.php" method="POST">
            <div class="mb-3">
                <label for="email" class="form-label text-dark-custom">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" required autocomplete="email" style="border-radius: 8px;">
            </div>
            <div class="mb-4">
                <label for="password" class="form-label text-dark-custom">Password</label>
                <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password" style="border-radius: 8px;">
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary-custom btn-lg" style="border-radius: 10px;">Login</button>
            </div>
        </form>
        <p class="text-center mt-4 text-muted-gray">Don't have an account? <a href="<?php echo BASE_URL; ?>register.php" class="text-primary-custom fw-bold">Register here</a></p>
    </div>
</div>

<?php
// No footer include here as the body is handled by the container styling
?>
