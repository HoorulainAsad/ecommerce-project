<?php
// test_email.php

// ⚠️ IMPORTANT: Adjust this path to correctly include your EmailManager.php file!
// If test_email.php is in your project root and classes/EmailManager.php is your path:
require_once 'classes/EmailManager.php';

// --- Create dummy order details for testing ---
$testOrderDetails = [
    'id' => 'TEST' . uniqid(), // Unique ID for each test
    'customer_name' => 'Test Customer',
    'customer_email' => 'hoorulainmahmood583@gmail.com', // 🚨 CHANGE THIS to YOUR actual email address! 🚨
    'order_status' => 'Pending',
    'created_at' => date('Y-m-d H:i:s'),
    'order_total' => 599.99,
    'payment_method' => 'Cash on Delivery',
    'shipping_address' => '123 Testing Lane',
    'city' => 'Anytown',
    'postal_code' => '12345',
    'customer_phone' => '03XX-XXXXXXX',
    'items' => [
        ['product_name' => 'Sample Dress', 'size' => 'S', 'quantity' => 1, 'price' => 450.00],
        ['product_name' => 'Matching Veil', 'size' => 'One Size', 'quantity' => 1, 'price' => 149.99],
    ]
];

// Instantiate your EmailManager class
$emailManager = new EmailManager();

echo "Attempting to send test emails...<br><br>";

// --- Test Order Confirmation Email ---
echo "Sending Order Confirmation Email... ";
if ($emailManager->sendOrderConfirmationEmail($testOrderDetails['customer_email'], $testOrderDetails)) {
    echo "<span style='color: green;'>SUCCESS!</span> Check your inbox.<br>";
} else {
    echo "<span style='color: red;'>FAILED.</span> Please check the Apache and PHP error logs.<br>";
}

echo "<br>";

// --- Test Order Status Update Email (e.g., to 'Shipped') ---
echo "Sending Order Status Update (Shipped)... ";
if ($emailManager->sendOrderStatusUpdateEmail($testOrderDetails['customer_email'], $testOrderDetails['id'], 'Shipped')) {
    echo "<span style='color: green;'>SUCCESS!</span> Check your inbox.<br>";
} else {
    echo "<span style='color: red;'>FAILED.</span> Please check the Apache and PHP error logs.<br>";
}

echo "<br>Test script finished. Double-check your email inbox or Mailtrap for messages.";

?>