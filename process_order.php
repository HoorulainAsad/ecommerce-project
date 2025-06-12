<?php
// process_order.php (Handles final order creation and sends email)

// No HTML output on this page until redirection for faster processing.
// Errors will be redirected back to checkout or displayed on confirmation page.

require_once __DIR__ . '/includes/functions.php'; // Ensures session is started and basic functions are available
require_once __DIR__ . '/classes/CartManager.php';
require_once __DIR__ . '/classes/OrderFrontendManager.php';
require_once __DIR__ . '/classes/EmailManager.php';

$cartManager = new CartManager();
$orderManager = new OrderFrontendManager();
$emailManager = new EmailManager();

$message = '';
$message_type = '';
$orderId = null;

// Ensure checkout data is in session and cart is not empty
if (!isset($_SESSION['checkout_data']) || empty($cartManager->getCartItems())) {
    // If no checkout data or cart is empty, redirect back to cart
    displayMessage("No checkout data found or your cart is empty. Please try again.", "error");
    redirectTo('cart.php');
    exit(); // Stop script execution
}

// Retrieve data from session
$checkoutData = $_SESSION['checkout_data'];
// Use the cart_items_snapshot from checkoutData for order creation to ensure consistency
// The createOrder method in OrderFrontendManager will re-validate stock.
$cartItemsSnapshot = $checkoutData['cart_items_snapshot'];


// Attempt to create the order
$newOrderId = $orderManager->createOrder(
    $checkoutData['user_id'],
    $checkoutData['customer_name'],
    $checkoutData['customer_email'],
    $checkoutData['customer_phone'],
    $checkoutData['shipping_address'],
    $checkoutData['city'],
    $checkoutData['postal_code'],
    $checkoutData['order_total'],
    $checkoutData['payment_method']
);

if ($newOrderId) {
    $orderId = $newOrderId;
    $message = "Your order has been placed successfully! Order ID: #" . $orderId . ".";
    $message_type = 'success';

    // Fetch complete order details for email (using the order ID returned)
    $orderDetails = $orderManager->getOrderDetails($orderId);

    if ($orderDetails) {
        // Send confirmation email
        if ($emailManager->sendOrderConfirmationEmail($orderDetails['customer_email'], $orderDetails)) {
            $message .= " A confirmation email has been sent to " . htmlspecialchars($orderDetails['customer_email']) . ".";
        } else {
            $message .= " However, there was an issue sending the confirmation email. Please check your spam folder or contact support if you don't receive it.";
        }
    } else {
        $message .= " Could not retrieve order details for email confirmation.";
    }

    // Clear checkout data from session after successful order processing
    unset($_SESSION['checkout_data']);

    // Redirect to order confirmation page with messages
    redirectTo('order_confirmation.php?order_id=' . $orderId . '&msg=' . urlencode($message) . '&type=' . $message_type);

} else {
    // Order creation failed (e.g., due to stock issues caught by transaction)
    $message = "Failed to place your order. One or more items might be out of stock or unavailable. Please review your cart.";
    $message_type = 'error';
    // Redirect back to checkout or cart with error message
    redirectTo('checkout.php?msg=' . urlencode($message) . '&type=' . $message_type);
}

// In case redirection fails or script execution continues unexpectedly,
// provide a minimal visual indication.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processing Order...</title>
    <!-- Bootstrap CSS for basic styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f8f4f0;
            color: #333;
            font-family: 'Inter', sans-serif;
            flex-direction: column;
        }
        .spinner-border {
            width: 3rem;
            height: 3rem;
            color: #7f0e10; /* Primary custom color */
        }
        .message-container {
            margin-top: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="spinner-border" role="status">
        <span class="visually-hidden">Processing Order...</span>
    </div>
    <div class="message-container">
        <p class="mt-3 fs-5 text-dark-custom">Processing your order. Please wait...</p>
        <?php if (!empty($message)): // Only show if PHP execution somehow continues here ?>
            <div class="alert alert-<?php echo $message_type; ?> mt-3">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
