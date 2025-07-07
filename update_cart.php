<?php
require_once __DIR__ . '/classes/CartManager.php';
require_once __DIR__ . '/includes/functions.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cartManager = new CartManager();

    $cartId = filter_var($_POST['cart_id'] ?? 0, FILTER_VALIDATE_INT);
    $action = sanitizeInput($_POST['action'] ?? '');

    if ($cartId > 0) {
        $success = false;
        if ($action === 'increase' || $action === 'decrease') {
            $success = $cartManager->updateQuantity($cartId, $action);
        } elseif ($action === 'delete') {
            $success = $cartManager->deleteItem($cartId);
        } elseif ($action === 'update_checked_status') { 
            $isChecked = isset($_POST['is_checked']) && $_POST['is_checked'] === 'true'; 
            $success = $cartManager->updateItemCheckedStatus($cartId, $isChecked);
        }

        if ($action === 'update_checked_status' || $action === 'increase' || $action === 'decrease' || $action === 'delete') {
            // For AJAX requests, send JSON response
            header('Content-Type: application/json');
                echo json_encode([
                    'success' => $success,
                    'grand_total' => (float)$cartManager->getCheckedCartTotal()
                ]);
                exit;
        }

        // For non-AJAX requests (like the existing quantity forms)
        if ($success) {
            displayMessage("Cart updated successfully.", "success");
        } else {
            displayMessage("Failed to update cart.", "error");
        }
    } else {
        displayMessage("Invalid cart item.", "error");
    }

    redirectTo('cart.php'); // Redirect after processing, for non-AJAX
} else {
    redirectTo('cart.php'); // Redirect if not a POST request
}