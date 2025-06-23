<?php
require_once __DIR__ . '/classes/OrderManager.php';

if (isset($_GET['id'])) {
    $orderId = (int) $_GET['id'];
    $orderManager = new OrderManager();
    $orderManager->deleteOrder($orderId);
}

header("Location: vieworders.php");
exit;
?>
