<?php
// C:\xampp\htdocs\msgm_clothing\admin\update_order_status.php

session_start();

require_once __DIR__ . '/classes/OrderManager.php';

require_once __DIR__ . '/../classes/EmailManager.php';



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
        
        $orderDetails = $orderManager->getOrderDetailsForEmail($orderId); 

        if ($orderDetails) {
           
            $orderDetails['order_status'] = $newStatus; 
            $customerEmail = $orderDetails['customer_email'] ?? ''; 

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
    header("Location: vieworders.php"); 
    exit;
}