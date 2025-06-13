<?php
// admin/classes/OrderManager.php

require_once __DIR__ . '/../includes/database.php';

class OrderManager {
    private $conn;
    private $userReferenceColumn;

    public function __construct() {
        $this->conn = getDbConnection();
        $this->determineUserReferenceColumn();
    }

    /**
     * Determines the correct column name that references users in orders table
     */
    private function determineUserReferenceColumn() {
        $possibleColumns = ['user_id', 'customer_id', 'client_id', 'buyer_id'];
        
        foreach ($possibleColumns as $column) {
            $check = $this->conn->query("SHOW COLUMNS FROM orders LIKE '$column'");
            if ($check && $check->num_rows > 0) {
                $this->userReferenceColumn = $column;
                return;
            }
        }
        
        // If we get here, no standard column was found
        $this->userReferenceColumn = null;
        error_log("OrderManager: Could not determine user reference column in orders table");
    }

    /**
     * Retrieves all orders from the database.
     * @return array An array of order associative arrays.
     */
    public function getAllOrders() {
        if (!$this->userReferenceColumn) {
            error_log("OrderManager: Cannot get orders - no user reference column found");
            return [];
        }

        $sql = "SELECT o.*, u.username AS customer_username 
                FROM orders o 
                LEFT JOIN users u ON o.{$this->userReferenceColumn} = u.id 
                ORDER BY o.created_at DESC";
        
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

    /**
     * Gets the count of all orders.
     * @return int The total number of orders.
     */
    public function getTotalOrdersCount() {
        $sql = "SELECT COUNT(id) AS total_orders FROM orders";
        $result = $this->conn->query($sql);
        if ($result && $row = $result->fetch_assoc()) {
            return (int)$row['total_orders'];
        }
        error_log("OrderManager Error: " . $this->conn->error);
        return 0;
    }

    /**
     * Gets the count of unique products that are considered "trendy".
     * @return int The count of trendy products.
     */
    public function getTrendyProductsCount() {
        $sql = "SELECT COUNT(DISTINCT product_id) AS trendy_count FROM order_items";
        $result = $this->conn->query($sql);
        if ($result && $row = $result->fetch_assoc()) {
            return (int)$row['trendy_count'];
        }
        error_log("OrderManager Error: " . $this->conn->error);
        return 0;
    }

    /**
     * Retrieves a single order by its ID.
     * @param int $orderId The ID of the order.
     * @return array|null An associative array of order data, or null if not found.
     */
    public function getOrderById($orderId) {
        if (!$this->userReferenceColumn) {
            error_log("OrderManager: Cannot get order - no user reference column found");
            return null;
        }

        $sql = "SELECT o.*, u.username AS customer_username 
                FROM orders o 
                LEFT JOIN users u ON o.{$this->userReferenceColumn} = u.id 
                WHERE o.id = ?";
                
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

    /**
     * Retrieves all items for a given order ID.
     * @param int $orderId The ID of the order.
     * @return array An array of order item associative arrays.
     */
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
                $items[] = $row;
            }
        }
        $stmt->close();
        return $items;
    }

    /**
     * Updates the status of an order.
     * @param int $orderId The ID of the order.
     * @param string $newStatus The new status (e.g., 'Processing', 'Shipped').
     * @return bool True on success, false on failure.
     */
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

    /**
     * Deletes an order and its associated order items.
     * @param int $orderId The ID of the order to delete.
     * @return bool True on success, false on failure.
     */
    public function deleteOrder($orderId) {
        $this->conn->begin_transaction();
        try {
            // Delete order items first
            $sqlDeleteItems = "DELETE FROM order_items WHERE order_id = ?";
            $stmtItems = $this->conn->prepare($sqlDeleteItems);
            if (!$stmtItems) {
                throw new Exception("Prepare delete order items failed: " . $this->conn->error);
            }
            $stmtItems->bind_param("i", $orderId);
            $stmtItems->execute();
            $stmtItems->close();

            // Delete the order
            $sqlDeleteOrder = "DELETE FROM orders WHERE id = ?";
            $stmtOrder = $this->conn->prepare($sqlDeleteOrder);
            if (!$stmtOrder) {
                throw new Exception("Prepare delete order failed: " . $this->conn->error);
            }
            $stmtOrder->bind_param("i", $orderId);
            $result = $stmtOrder->execute();
            $stmtOrder->close();

            $this->conn->commit();
            return $result;

        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Failed to delete order (ID: {$orderId}): " . $e->getMessage());
            return false;
        }
    }
}
?>