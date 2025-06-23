<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/classes/OrderFrontendManager.php';
require_once __DIR__ . '/classes/CartManager.php';
require_once __DIR__ . '/classes/EmailManager.php';
require_once __DIR__ . '/admin/classes/ProductManager.php'; // Add this line


$cartManager = new CartManager();
$orderManager = new OrderFrontendManager();
$emailManager = new EmailManager();
$productManager = new ProductManager(); // Add this line

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect customer details from form
    $name = $_POST['customer_name'] ?? '';
    $email = $_POST['customer_email'] ?? '';
    $address = $_POST['shipping_address'] ?? '';
    $city = $_POST['city'] ?? '';
    $postalCode = $_POST['postal_code'] ?? '';
    $paymentMethod = $_POST['payment_method'] ?? 'cod';

    // Validate inputs
    if (!$name || !$email || !$address || !$city || !$postalCode) {
        displayMessage("All fields are required.", "error");
        redirectTo("checkout.php");
        exit;
    }

    // Get selected cart items
    $items = $cartManager->getCheckedCartItems();
    if (empty($items)) {
        displayMessage("No items selected for checkout.", "error");
        redirectTo("cart.php");
        exit;
    }

    // Calculate total amount
    $total = $cartManager->getCheckedCartTotal();

    // Place order and get new order ID
    $orderId = $orderManager->placeOrder($name, $email, $address, $city, $postalCode, $paymentMethod, $total);

    // Insert order items (using 'name' field instead of 'size')
    foreach ($items as $item) {
        $productName = $item['name'] ?? 'Unknown Product';
        $orderManager->insertOrderItem($orderId, $item['product_id'], $productName, $item['quantity'], $item['price']);
        $productManager->updateProductStock($item['product_id'], $item['quantity']); 
    }

    // Clear only checked cart items
    $orderManager->clearCheckedCartItems(session_id());

    // Send confirmation email
    $orderDetails = $orderManager->getOrderDetails($orderId);
$orderItems = $orderManager->getOrderItemsWithProductInfo($orderId); // You'll add this method below
$orderDetails['items'] = $orderItems;

if ($orderDetails) {
     
    $emailManager->sendOrderConfirmationEmail($email, $orderDetails);
}


    // Redirect to order confirmation
    $msg = urlencode("Order placed successfully! You will receive a confirmation email soon.");
    redirectTo("order_confirmation.php?type=success&msg=$msg");
    exit;
} else {
    displayMessage("Invalid request.", "error");
    redirectTo("cart.php");
    exit;
}
