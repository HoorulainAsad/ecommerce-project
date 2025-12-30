<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/classes/OrderFrontendManager.php';
require_once __DIR__ . '/classes/CartManager.php';
require_once __DIR__ . '/classes/EmailManager.php';
require_once __DIR__ . '/admin/classes/ProductManager.php'; 


$cartManager = new CartManager();
$orderManager = new OrderFrontendManager();
$emailManager = new EmailManager();
$productManager = new ProductManager(); 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $name = $_POST['customer_name'] ?? '';
    $email = $_POST['customer_email'] ?? '';
    $address = $_POST['shipping_address'] ?? '';
    $city = $_POST['city'] ?? '';
    $postalCode = $_POST['postal_code'] ?? '';
    $paymentMethod = $_POST['payment_method'] ?? 'cod';

    
    if (!$name || !$email || !$address || !$city || !$postalCode) {
        displayMessage("All fields are required.", "error");
        redirectTo("checkout.php");
        exit;
    }

    $items = $cartManager->getCheckedCartItems();
    if (empty($items)) {
        displayMessage("No items selected for checkout.", "error");
        redirectTo("cart.php");
        exit;
    }

    $total = $cartManager->getCheckedCartTotal();

    $orderId = $orderManager->placeOrder($name, $email, $address, $city, $postalCode, $paymentMethod, $total);

    foreach ($items as $item) {
        $productName = $item['name'] ?? 'Unknown Product';
        $orderManager->insertOrderItem($orderId, $item['product_id'], $productName, $item['quantity'], $item['price']);
        $productManager->updateProductStock($item['product_id'], $item['quantity']); 
    }

    $orderManager->clearCheckedCartItems(session_id());

    $orderDetails = $orderManager->getOrderDetails($orderId);
$orderItems = $orderManager->getOrderItemsWithProductInfo($orderId); 
$orderDetails['items'] = $orderItems;

if ($orderDetails) {
     
    $emailManager->sendOrderConfirmationEmail($email, $orderDetails);
}


    $msg = urlencode("Order placed successfully! You will receive a confirmation email soon.");
    redirectTo("order_confirmation.php?type=success&msg=$msg");
    exit;
} else {
    displayMessage("Invalid request.", "error");
    redirectTo("cart.php");
    exit;
}
