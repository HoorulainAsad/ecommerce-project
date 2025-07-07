<?php
// classes/OrderFrontendManager.php

require_once __DIR__ . '/../admin/includes/database.php';

class OrderFrontendManager {
    private $conn;

    public function __construct() {
        $this->conn = getDbConnection();
    }

    public function placeOrder($name, $email, $address, $city, $postalCode, $paymentMethod, $total) {
        $stmt = $this->conn->prepare("INSERT INTO orders (customer_name, customer_email, shipping_address, city, postal_code, payment_method, total_amount, order_status)
                                      VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("ssssssd", $name, $email, $address, $city, $postalCode, $paymentMethod, $total);
        $stmt->execute();
        return $stmt->insert_id;
    }

    public function insertOrderItem($orderId, $productId, $name, $quantity, $price) {
    $stmt = $this->conn->prepare("INSERT INTO order_items (order_id, product_id, name, quantity, price)
                                  VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisid", $orderId, $productId, $name, $quantity, $price);
    $stmt->execute();
}

    public function clearCheckedCartItems($sessionId) {
        $stmt = $this->conn->prepare("DELETE FROM cart WHERE session_id = ? AND is_checked = 1");
        $stmt->bind_param("s", $sessionId);
        $stmt->execute();
    }

    public function getOrderDetails($orderId) {
        $orderDetails = [];

        $stmt = $this->conn->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            return false;
        }
        $orderDetails = $result->fetch_assoc();
        $stmt->close();

        $stmt = $this->conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $itemsResult = $stmt->get_result();
        $orderDetails['items'] = $itemsResult->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $orderDetails;
    }

    public function getOrderItemsWithProductInfo($orderId) {
    $stmt = $this->conn->prepare("
        SELECT 
            oi.product_id,
            p.name,
            oi.quantity,
            oi.price
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();

    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }

    return $items;
}

}
