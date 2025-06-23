<?php
// checkout_prepare.php

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/classes/CartManager.php';

$cartManager = new CartManager();
$checkedItems = $cartManager->getCheckedCartItems();
$total = $cartManager->getCheckedCartTotal();

if (empty($checkedItems)) {
    displayMessage("Please select items before proceeding to checkout.", "error");
    redirectTo('cart.php');
    exit;
}

$_SESSION['checkout_data'] = [
    'user_id' => $_SESSION['user_id'] ?? 0,
    'customer_name' => $_SESSION['user_name'] ?? 'Guest User',
    'customer_email' => $_SESSION['user_email'] ?? 'guest@example.com',
    'customer_phone' => $_SESSION['user_phone'] ?? '0000000000',
    'shipping_address' => $_POST['shipping_address'] ?? '',
    'city' => $_POST['city'] ?? '',
    'postal_code' => $_POST['postal_code'] ?? '',
    'order_total' => $total,
    'payment_method' => $_POST['payment_method'] ?? 'cod',
    'cart_items_snapshot' => $checkedItems
];

redirectTo('process_order.php');
exit;
