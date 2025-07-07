<?php
// admin/add_admin.php

require_once __DIR__ . '/includes/functions.php'; 
require_once __DIR__ . '/includes/database.php'; // Include for database connection

// Check if admin is logged in AND has 'super_admin' role
if (!isLoggedIn() || !isSuperAdmin()) {
    redirectToAdmin('login.php'); 
}

$message = '';
$error = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $email = sanitizeInput($_POST['email'] ?? ''); 
    $role = sanitizeInput($_POST['role'] ?? 'admin'); 

    if (empty($username) || empty($password) || empty($confirm_password) || empty($email)) {
        $message = "All fields are required.";
        $error = true;
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
        $error = true;
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters long.";
        $error = true;
    } else {
        $conn = getDbConnection();

        // Check if username or email already exists
        $check_sql = "SELECT id FROM admin_users WHERE username = ? OR email = ?";
        $stmt_check = $conn->prepare($check_sql);
        $stmt_check->bind_param("ss", $username, $email);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $message = "Username or Email already exists.";
            $error = true;
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $insert_sql = "INSERT INTO admin_users (username, password, email, role) VALUES (?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($insert_sql);
            
            if ($stmt_insert) {
                $stmt_insert->bind_param("ssss", $username, $hashed_password, $email, $role);
                if ($stmt_insert->execute()) {
                    $message = "New admin '$username' added successfully!";
                    $error = false;
                   
                    $_POST = array();
                } else {
                    $message = "Error adding admin: " . $stmt_insert->error;
                    $error = true;
                }
                $stmt_insert->close();
            } else {
                $message = "Database error preparing statement: " . $conn->error;
                $error = true;
            }
        }
        $stmt_check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Admin - MSGM Bridal Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Anonymous+Pro:ital,wght@0,400;0,700;1,400;1,700&family=Lora:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>admin/assets/css/styles.css?v=3">
    <style>
       
        body {
           font-family: "Lora" , monospace;
            background-color: #e9e3ce;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: flex-start; 
            min-height: 100vh;
            padding-top: 50px; 
        }
        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            margin-bottom: 50px;
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 25px;
            font-family: 'Anonymous Pro', monospace;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }
        input[type="text"],
        input[type="password"],
        input[type="email"],
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box; 
            font-size: 1rem;
        }
        input[type="text"]:focus,
        input[type="password"]:focus,
        input[type="email"]:focus,
        select:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        .add-admin-button {
            background-color: #28a745; 
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.1rem;
            width: 100%;
            transition: background-color 0.3s ease;
        }
        .add-admin-button:hover {
            background-color: #218838;
        }
        .error-message {
            color: #dc3545;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        .success-message {
            color: #155724;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        .form-text {
            font-size: 0.875em;
            color: #6c757d;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Add New Administrator</h2>
        <?php if ($message): ?>
            <?php displayMessage($message, $error); ?>
        <?php endif; ?>
        <form action="add_admin.php" method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required autocomplete="off" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="email">Email Address:</label>
                <input type="email" id="email" name="email" required autocomplete="off" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required autocomplete="new-password">
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password">
            </div>
            <div class="form-group">
                <label for="role">Admin Role:</label>
                <select id="role" name="role" required>
                    <option value="admin" <?php echo (($_POST['role'] ?? '') === 'admin') ? 'selected' : ''; ?>>Admin</option>
                    <option value="super_admin" <?php echo (($_POST['role'] ?? '') === 'super_admin') ? 'selected' : ''; ?>>Super Admin</option>
                </select>
                <small class="form-text">A 'Super Admin' can add other administrators.</small>
            </div>
            <button type="submit" class="add-admin-button">Add Admin</button>
        </form>
    </div>
</body>
</html>
