<?php
// admin/setup_database.php

// IMPORTANT: Run this file ONCE to set up your database and tables.
// After successful execution, it's recommended to delete or rename this file for security.

// Include configuration for database connection details
require_once __DIR__ . '/includes/config.php';

// --- Database Connection for Initial Creation ---
// Connect to MySQL server without specifying a database first,
// as we need to create the database itself.
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected to MySQL server successfully.<br>";

// --- Create Database ---
$sql_create_db = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if ($conn->query($sql_create_db) === TRUE) {
    echo "Database '" . DB_NAME . "' created or already exists.<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
    $conn->close();
    exit();
}

// Select the newly created database
$conn->select_db(DB_NAME);

// --- Create Categories Table ---
$sql_create_categories_table = "
CREATE TABLE IF NOT EXISTS categories (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($sql_create_categories_table) === TRUE) {
    echo "Table 'categories' created or already exists.<br>";
} else {
    echo "Error creating table 'categories': " . $conn->error . "<br>";
}

// --- Insert/Ensure ONLY Main Categories ---
// First, delete any categories that are NOT the main ones (like 'TRENDY', 'NEW ARRIVALS')
$main_categories = ['FORMAL', 'PARTYWEAR', 'BRIDAL'];
$delete_sql = "DELETE FROM categories WHERE name NOT IN ('" . implode("','", $main_categories) . "')";
if ($conn->query($delete_sql) === TRUE) {
    echo "Removed non-main categories (if any).<br>";
} else {
    echo "Error removing non-main categories: " . $conn->error . "<br>";
}


foreach ($main_categories as $category_name) {
    // Check if category already exists to prevent duplicates on re-run
    $check_sql = "SELECT id FROM categories WHERE name = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("s", $category_name);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
        $insert_sql = "INSERT INTO categories (name) VALUES (?)";
        $stmt_insert = $conn->prepare($insert_sql);
        $stmt_insert->bind_param("s", $category_name);
        if ($stmt_insert->execute()) {
            echo "Main Category '" . $category_name . "' inserted.<br>";
        } else {
            echo "Error inserting category '" . $category_name . "': " . $stmt_insert->error . "<br>";
        }
        $stmt_insert->close();
    } else {
        echo "Main Category '" . $category_name . "' already exists.<br>";
    }
    $stmt->close();
}


// --- Create Products Table ---
$sql_create_products_table = "
CREATE TABLE IF NOT EXISTS products (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    category_id INT(11),
    stock INT(11) DEFAULT 0,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
)";
if ($conn->query($sql_create_products_table) === TRUE) {
    echo "Table 'products' created or already exists.<br>";
} else {
    echo "Error creating table 'products': " . $conn->error . "<br>";
}

// --- Create Orders Table ---
$sql_create_orders_table = "
CREATE TABLE IF NOT EXISTS orders (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(255) NOT NULL,
    customer_email VARCHAR(255),
    total_amount DECIMAL(10, 2) NOT NULL,
    order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(50) DEFAULT 'Pending', -- e.g., Pending, Processing, Shipped, Delivered, Cancelled
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($sql_create_orders_table) === TRUE) {
    echo "Table 'orders' created or already exists.<br>";
} else {
    echo "Error creating table 'orders': " . $conn->error . "<br>";
}

// --- Create Admin Users Table (for admin login) ---
$sql_create_admin_users_table = "
CREATE TABLE IF NOT EXISTS admin_users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- Store hashed passwords
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($sql_create_admin_users_table) === TRUE) {
    echo "Table 'admin_users' created or already exists.<br>";

    // Insert a default admin user if one doesn't exist
    $default_username = 'admin';
    $default_password = password_hash('password123', PASSWORD_DEFAULT); // Hashed password

    $check_admin_sql = "SELECT id FROM admin_users WHERE username = ?";
    $stmt_check_admin = $conn->prepare($check_admin_sql);
    $stmt_check_admin->bind_param("s", $default_username);
    $stmt_check_admin->execute();
    $stmt_check_admin->store_result();

    if ($stmt_check_admin->num_rows == 0) {
        $insert_admin_sql = "INSERT INTO admin_users (username, password) VALUES (?, ?)";
        $stmt_insert_admin = $conn->prepare($insert_admin_sql);
        $stmt_insert_admin->bind_param("ss", $default_username, $default_password);
        if ($stmt_insert_admin->execute()) {
            echo "Default admin user ('admin'/'password123') created. Please change this password immediately!<br>";
        } else {
            echo "Error creating default admin user: " . $stmt_insert_admin->error . "<br>";
        }
        $stmt_insert_admin->close();
    } else {
        echo "Default admin user already exists.<br>";
    }
    $stmt_check_admin->close();

} else {
    echo "Error creating table 'admin_users': " . $conn->error . "<br>";
}

// --- Create Users Table (for customer accounts on the main website) ---
$sql_create_users_table = "
CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) UNIQUE, -- Can be email or a chosen username
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- Hashed password
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($sql_create_users_table) === TRUE) {
    echo "Table 'users' created or already exists.<br>";
} else {
    echo "Error creating table 'users': " . $conn->error . "<br>";
}

// --- Create Reviews Table (for customer feedback) ---
$sql_create_reviews_table = "
CREATE TABLE IF NOT EXISTS reviews (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    product_id INT(11), -- Optional: Link to a product
    user_id INT(11),    -- Optional: Link to a registered user
    customer_name VARCHAR(255) NOT NULL,
    customer_email VARCHAR(255),
    rating INT(1),      -- 1 to 5 stars
    comment TEXT NOT NULL,
    status VARCHAR(50) DEFAULT 'Pending', -- e.g., Pending, Approved, Rejected
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
)";
if ($conn->query($sql_create_reviews_table) === TRUE) {
    echo "Table 'reviews' created or already exists.<br>";
} else {
    echo "Error creating table 'reviews': " . $conn->error . "<br>";
}

// --- NEW: Create Order_Items Table ---
$sql_create_order_items_table = "
CREATE TABLE IF NOT EXISTS order_items (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    order_id INT(11) NOT NULL,
    product_id INT(11) NOT NULL,
    quantity INT(11) NOT NULL,
    price_at_time_of_order DECIMAL(10, 2) NOT NULL, -- Price when ordered (can differ from current product price)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
)";
if ($conn->query($sql_create_order_items_table) === TRUE) {
    echo "Table 'order_items' created or already exists.<br>";
} else {
    echo "Error creating table 'order_items': " . $conn->error . "<br>";
}


$conn->close();
echo "<br>Database setup complete. You can now access your admin panel via login.php.<br>";
echo "Remember to delete or rename 'setup_database.php' for security.";
?>
