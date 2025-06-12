<?php
// register.php (Frontend User Registration Page)

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
    $username = sanitizeInput($_POST['username'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        $message = "All fields are required.";
        $message_type = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
        $message_type = 'error';
    } elseif ($password !== $confirmPassword) {
        $message = "Passwords do not match.";
        $message_type = 'error';
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters long.";
        $message_type = 'error';
    } else {
        if ($userFrontendManager->registerUser($username, $email, $password)) {
            $message = "Registration successful! You can now log in.";
            $message_type = 'success';
            // Optional: Redirect to login page after successful registration
            // redirectTo('login.php?msg=' . urlencode($message) . '&type=' . $message_type);
        } else {
            $message = "Registration failed. Email or username might already be taken.";
            $message_type = 'error';
        }
    }
}
?>

<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card p-4 shadow-lg" style="max-width: 450px; width: 100%; border-radius: 15px;">
        <h2 class="card-title text-center mb-4 text-primary-custom">Create Your Account</h2>

        <?php if ($message): ?>
            <?php displayMessage($message, $message_type); ?>
        <?php endif; ?>

        <form action="<?php echo BASE_URL; ?>register.php" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label text-dark-custom">Username</label>
                <input type="text" class="form-control" id="username" name="username" required autocomplete="username" style="border-radius: 8px;">
            </div>
            <div class="mb-3">
                <label for="email" class="form-label text-dark-custom">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" required autocomplete="email" style="border-radius: 8px;">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label text-dark-custom">Password</label>
                <input type="password" class="form-control" id="password" name="password" required autocomplete="new-password" style="border-radius: 8px;">
            </div>
            <div class="mb-4">
                <label for="confirm_password" class="form-label text-dark-custom">Confirm Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required autocomplete="new-password" style="border-radius: 8px;">
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary-custom btn-lg" style="border-radius: 10px;">Register</button>
            </div>
        </form>
        <p class="text-center mt-4 text-muted-gray">Already have an account? <a href="<?php echo BASE_URL; ?>login.php" class="text-primary-custom fw-bold">Login here</a></p>
    </div>
</div>

<?php
// No footer include here as the body is handled by the container styling
?>
