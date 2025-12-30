<?php
// admin/login.php

require_once __DIR__ . '/includes/functions.php'; 
require_once __DIR__ . '/includes/database.php'; 

// Check if admin is already logged in, redirect to dashboard
if (isLoggedIn()) {
    redirectToAdmin('index.php');
}

$message = '';
$error = false;

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? ''; 

    if (empty($username) || empty($password)) {
        $message = "Please enter both username and password.";
        $error = true;
    } else {
        $conn = getDbConnection(); 
        $sql = "SELECT id, username, password, role FROM admin_users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                // Verify the hashed password
                if (password_verify($password, $user['password'])) {
                    $_SESSION[ADMIN_SESSION_KEY] = true;
                    $_SESSION[ADMIN_ID_SESSION_KEY] = $user['id']; // Store admin ID
                    $_SESSION[ADMIN_USERNAME_SESSION_KEY] = $user['username'];
                    $_SESSION[ADMIN_ROLE_SESSION_KEY] = $user['role']; // Store the admin's role
                    $stmt->close();
                    
                    redirectToAdmin('index.php');
                } else {
                    $message = "Invalid username or password.";
                    $error = true;
                }
            } else {
                $message = "Invalid username or password.";
                $error = true;
            }
            $stmt->close();
        } else {
            $message = "Database error: " . $conn->error;
            $error = true;
        }
       
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - MSGM Bridal</title>
    <link href="https://fonts.googleapis.com/css2?family=Anonymous+Pro:ital,wght@0,400;0,700;1,400;1,700&family=Lora:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
    <!-- Link to your external stylesheet -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>admin/assets/css/styles.css?v=3">
    
</head>
<body class="login-body">
    <div class="login-container">
        <h2>Admin Login</h2>
        <?php if ($message): ?>
            <?php displayMessage($message, $error); ?>
        <?php endif; ?>
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required autocomplete="username">
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>
            <button type="submit"  class="login-button">Login</button>
        </form>
    </div>
</body>
</html>
