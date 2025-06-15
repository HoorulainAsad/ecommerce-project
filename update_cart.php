<?php
require_once __DIR__ . '/classes/CartManager.php';

$cartManager = new CartManager();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $cartId = intval($_POST['cart_id'] ?? 0);

    if ($action === 'increase' || $action === 'decrease') {
        $cartManager->updateQuantity($cartId, $action);
    } elseif ($action === 'delete') {
        $cartManager->deleteItem($cartId);
    }
}

header('Location: cart.php');
exit;
