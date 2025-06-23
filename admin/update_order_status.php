<?php
// C:\xampp\htdocs\msgm_clothing\admin\update_order_status.php

// Start session if you are using $_SESSION for messages
session_start();

// Correct path to OrderManager.php
require_once __DIR__ . '/classes/OrderManager.php';

// Correct path to EmailManager.php
require_once __DIR__ . '/../classes/EmailManager.php';

// REMOVED: require_once __DIR__ . '/../includes/database.php';
// OrderManager's constructor already handles including database.php,
// so this line is redundant and causes "Constant already defined" warnings.


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['new_status'])) {
    $orderId = (int) $_POST['order_id'];
    $newStatus = trim($_POST['new_status']);

    if ($orderId <= 0 || empty($newStatus)) {
        $_SESSION['error_message'] = "Invalid order ID or status provided.";
        header("Location: vieworders.php");
        exit;
    }

    $orderManager = new OrderManager();
    $emailManager = new EmailManager();

    // 1. Update the order status in the database
    $updateSuccess = $orderManager->updateOrderStatus($orderId, $newStatus);

    if ($updateSuccess) {
        // --- CRUCIAL CHANGE: Fetch the COMPLETE order details including items ---
        $orderDetails = $orderManager->getOrderDetailsForEmail($orderId); // Call the new method

        if ($orderDetails) {
            // IMPORTANT: Make sure the 'order_status' in $orderDetails reflects the NEW status
            $orderDetails['order_status'] = $newStatus; // Override with the new status

            // Send status update email using the fully prepared $orderDetails array
            $customerEmail = $orderDetails['customer_email'] ?? ''; // Get email from fetched details

            if (!empty($customerEmail)) {
                if ($emailManager->sendOrderStatusUpdateEmail($customerEmail, $orderDetails)) {
                    $_SESSION['success_message'] = "Order status updated and email sent successfully!";
                } else {
                    $_SESSION['warning_message'] = "Order status updated, but email could not be sent. Check server error logs for details.";
                }
            } else {
                $_SESSION['warning_message'] = "Order status updated, but customer email not found to send notification.";
            }
        } else {
            $_SESSION['error_message'] = "Failed to retrieve complete order details for email notification.";
        }
    } else {
        $_SESSION['error_message'] = "Failed to update order status in the database.";
    }

    // Redirect back to the orders view page
    header("Location: vieworders.php");
    exit;
} else {
    // If not a POST request or missing data, redirect or show an error
    header("Location: vieworders.php"); // Or wherever appropriate
    exit;
}