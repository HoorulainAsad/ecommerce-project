<?php
// admin/classes/OrderManager.php

require_once __DIR__ . '/../includes/database.php';

class OrderManager {
    private $conn;

    public function __construct() {
        $this->conn = getDbConnection();
    }

    /**
     * Retrieves all orders from the database.
     * @return array An array of order associative arrays.
     */
    public function getAllOrders() {
        // Updated query to include customer_username from the frontend 'users' table
        $sql = "SELECT o.*, u.username AS customer_username FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC";
        $result = $this->conn->query($sql);
        $orders = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $orders[] = $row;
            }
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
        return 0;
    }

    /**
     * Gets the count of unique products that are considered "trendy" (e.g., top N most ordered).
     * This implementation counts products that have been ordered at least once.
     * You might refine this to, for example, only count top 10 most ordered products.
     * @return int The count of trendy products.
     */
    public function getTrendyProductsCount() {
        // This query counts distinct products that have appeared in any order item.
        // A more sophisticated "trendy" logic might involve recent orders, or top N by quantity.
        $sql = "SELECT COUNT(DISTINCT product_id) AS trendy_count FROM order_items";
        $result = $this->conn->query($sql);
        if ($result && $row = $result->fetch_assoc()) {
            return (int)$row['trendy_count'];
        }
        return 0;
    }

    // No __destruct() here, as database connection is handled globally.
}
?>
