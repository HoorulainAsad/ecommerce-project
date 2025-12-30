<?php
// admin/classes/OrderManager.php

require_once __DIR__ . '/../includes/database.php'; 
class OrderManager {
    private $conn;
    private $userReferenceColumn;

    public function __construct() {
        $this->conn = getDbConnection(); 
        if (!$this->conn) {
            throw new Exception("OrderManager: Could not establish database connection.");
        }
        $this->determineUserReferenceColumn();
    }

    
    private function determineUserReferenceColumn() {
        $possibleColumns = ['user_id', 'customer_id', 'client_id', 'buyer_id'];

        foreach ($possibleColumns as $column) {
            $check = $this->conn->query("SHOW COLUMNS FROM orders LIKE '$column'");
            if ($check && $check->num_rows > 0) {
                $this->userReferenceColumn = $column;
                return;
            }
        }

        $this->userReferenceColumn = null;
        error_log("OrderManager: Could not determine user reference column in orders table");
    }

    
    public function getAllOrders() {
       $sql = "SELECT id, customer_name, customer_email, total_amount, order_date, order_status
         FROM orders
         ORDER BY created_at DESC";

        $result = $this->conn->query($sql);

        if (!$result) {
            error_log("OrderManager Error: " . $this->conn->error);
            return [];
        }

        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        return $orders;
    }

    
    public function getTotalOrdersCount() {
        $sql = "SELECT COUNT(id) AS total_orders FROM orders";
        $result = $this->conn->query($sql);
        if ($result && $row = $result->fetch_assoc()) {
            return (int)$row['total_orders'];
        }
        error_log("OrderManager Error: " . $this->conn->error);
        return 0;
    }

    public function getTrendyProductsCount() {
        $sql = "SELECT COUNT(DISTINCT product_id) AS trendy_count FROM order_items";
        $result = $this->conn->query($sql);
        if ($result && $row = $result->fetch_assoc()) {
            return (int)$row['trendy_count'];
        }
        error_log("OrderManager Error: " . $this->conn->error);
        return 0;
    }

    public function getOrderById($orderId) {
        $sql = "SELECT * FROM orders WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("OrderManager: Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return null;
        }
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        $order = $result->fetch_assoc();
        $stmt->close();
        return $order;
    }

    public function getOrderItems($orderId) {
        $sql = "SELECT oi.*, p.name AS product_name, p.price AS unit_price
                FROM order_items oi
                LEFT JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = ?";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("OrderManager: Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return [];
        }
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        $items = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $items[] = [
                    'product_id' => $row['product_id'],
                    'name' => $row['product_name'], 
                    'quantity' => $row['quantity'],
                    'price' => $row['price_at_purchase'] ?? $row['unit_price'], 
                ];
            }
        }
        $stmt->close();
        return $items;
    }

    public function updateOrderStatus($orderId, $newStatus) {
        $sql = "UPDATE orders SET order_status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("OrderManager: Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("si", $newStatus, $orderId);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function deleteOrder($orderId) {
        $this->conn->begin_transaction();
        try {
            $sqlDeleteItems = "DELETE FROM order_items WHERE order_id = ?";
            $stmtItems = $this->conn->prepare($sqlDeleteItems);
            if (!$stmtItems) {
                throw new Exception("Prepare delete order items failed: " . $this->conn->error);
            }
            $stmtItems->bind_param("i", $orderId);
            $stmtItems->execute();
            $stmtItems->close();

            $sqlDeleteOrder = "DELETE FROM orders WHERE id = ?";
            $stmtOrder = $this->conn->prepare($sqlDeleteOrder);
            if (!$stmtOrder) {
                throw new Exception("Prepare delete order failed: " . $this->conn->error);
            }
            $stmtOrder->bind_param("i", $orderId);
            $stmtOrder->execute();
            $stmtOrder->close();

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Failed to delete order (ID: {$orderId}): " . $e->getMessage());
            return false;
        }
    }

   
    public function getOrderDetailsForEmail($orderId) {
        $orderDetails = null;

       
        $stmt = $this->conn->prepare("SELECT o.id, o.customer_name, o.customer_email, o.order_status,
                                              o.created_at, o.payment_method, o.total_amount,
                                              o.shipping_address, o.city, o.postal_code,
                                              o.order_date
                                       FROM orders o WHERE o.id = ?");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $orderDetails = $result->fetch_assoc();
        
            if (!isset($orderDetails['created_at']) && isset($orderDetails['order_date'])) {
                $orderDetails['created_at'] = $orderDetails['order_date'];
            }
        }
        $stmt->close();

        if ($orderDetails) {
          
            $items = $this->getOrderItems($orderId); 
            $orderDetails['items'] = $items;
        }

        return $orderDetails;
    }

   public function getTrendyProducts($limit = 10, $days = 30) {
    $sql = "SELECT
                p.id,
                p.name,
                p.description,
                p.price,
                p.image_url,
                p.stock,
                c.name AS category_name,
                SUM(oi.quantity) AS total_ordered_quantity
            FROM
                order_items oi
            JOIN
                products p ON oi.product_id = p.id
            LEFT JOIN
                categories c ON p.category_id = c.id
            JOIN
                orders o ON oi.order_id = o.id
            WHERE
                o.order_date >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY
                p.id, p.name, p.description, p.price, p.image_url, p.stock, c.name
            ORDER BY
                total_ordered_quantity DESC
            LIMIT ?";

    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
        error_log("OrderManager::getTrendyProducts - Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
        return [];
    }

    $stmt->bind_param("ii", $days, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $trendyProducts = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $trendyProducts[] = $row;
        }
    }
    $stmt->close();
    return $trendyProducts;
}

}