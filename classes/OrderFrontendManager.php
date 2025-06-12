<?php
// classes/OrderFrontendManager.php

require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/CartManager.php'; // To clear cart after order
require_once __DIR__ . '/ProductFrontendManager.php'; // To update product stock and re-verify details

class OrderFrontendManager {
    private $conn;
    private $cartManager;
    private $productManager;

    public function __construct() {
        $this->conn = getDbConnection();
        // CartManager and ProductFrontendManager are instantiated here
        // to ensure they use the same database connection if needed, or manage their own.
        // It's crucial for atomic operations like order creation.
        $this->cartManager = new CartManager();
        $this->productManager = new ProductFrontendManager();
    }

    /**
     * Creates a new order and its items, handles stock deduction, and clears cart.
     * This is a transactional operation.
     * @param int|null $userId User ID if logged in, null if guest.
     * @param string $customerName
     * @param string $customerEmail
     * @param string $customerPhone
     * @param string $shippingAddress
     * @param string $city
     * @param string $postalCode
     * @param float $orderTotal The final calculated total for the order.
     * @param string $paymentMethod
     * @return int|false Order ID on success, false on failure (e.g., stock issues, DB error).
     */
    public function createOrder($userId, $customerName, $customerEmail, $customerPhone,
                                $shippingAddress, $city, $postalCode, $orderTotal,
                                $paymentMethod) {

        // Get fresh cart items from CartManager to ensure latest stock checks
        $cartItems = $this->cartManager->getCartItems();

        if (empty($cartItems)) {
            error_log("Attempted to create order with empty cart.");
            return false;
        }

        $this->conn->begin_transaction();
        try {
            // 1. Validate stock again before inserting (race condition prevention)
            foreach ($cartItems as $item) {
                $product = $this->productManager->getProductById($item['product_id']);
                if (!$product || $product['stock'] < $item['quantity']) {
                    throw new Exception("Insufficient stock for product '{$item['name']}' (ID: {$item['product_id']}). Available: {$product['stock']}, Requested: {$item['quantity']}.");
                }
            }

            // 2. Insert into orders table
            $sqlOrder = "INSERT INTO orders (user_id, customer_name, customer_email, customer_phone, shipping_address, city, postal_code, order_total, payment_method, order_status, created_at)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())";
            $stmtOrder = $this->conn->prepare($sqlOrder);
            if (!$stmtOrder) {
                throw new Exception("Prepare order insert failed: " . $this->conn->error);
            }
            $stmtOrder->bind_param("issssssds",
                $userId, $customerName, $customerEmail, $customerPhone, $shippingAddress, $city, $postalCode, $orderTotal, $paymentMethod
            );
            $stmtOrder->execute();
            $orderId = $this->conn->insert_id;
            $stmtOrder->close();

            // 3. Insert into order_items table and update product stock
            $sqlOrderItem = "INSERT INTO order_items (order_id, product_id, product_name, quantity, price, size) VALUES (?, ?, ?, ?, ?, ?)";
            $stmtOrderItem = $this->conn->prepare($sqlOrderItem);
            if (!$stmtOrderItem) {
                throw new Exception("Prepare order item insert failed: " . $this->conn->error);
            }

            foreach ($cartItems as $item) {
                $productId = $item['product_id'];
                $productName = $item['name'];
                $quantity = $item['quantity'];
                $price = $item['price']; // Price at time of adding to cart
                $size = $item['size'];

                $stmtOrderItem->bind_param("iisids", $orderId, $productId, $productName, $quantity, $price, $size);
                $stmtOrderItem->execute();

                // Update product stock: decrement stock
                $sqlUpdateStock = "UPDATE products SET stock = stock - ? WHERE id = ?";
                $stmtUpdateStock = $this->conn->prepare($sqlUpdateStock);
                if (!$stmtUpdateStock) {
                    throw new Exception("Prepare stock update failed: " . $this->conn->error);
                }
                $stmtUpdateStock->bind_param("ii", $quantity, $productId);
                $stmtUpdateStock->execute();
                $stmtUpdateStock->close();
            }
            $stmtOrderItem->close();

            // 4. Clear the user's cart (session and database)
            $this->cartManager->clearCart(); // Clears both session and DB cart

            $this->conn->commit();
            return $orderId;

        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Order creation failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetches complete order details for a given order ID, including items.
     * @param int $orderId
     * @return array|null Order details including 'items' array, or null if not found.
     */
    public function getOrderDetails($orderId) {
        $order = null;
        $orderItems = [];

        // Fetch main order details
        $sqlOrder = "SELECT * FROM orders WHERE id = ?";
        $stmtOrder = $this->conn->prepare($sqlOrder);
        if ($stmtOrder) {
            $stmtOrder->bind_param("i", $orderId);
            $stmtOrder->execute();
            $resultOrder = $stmtOrder->get_result();
            $order = $resultOrder->fetch_assoc();
            $stmtOrder->close();
        }

        if ($order) {
            // Fetch order items
            $sqlItems = "SELECT * FROM order_items WHERE order_id = ?";
            $stmtItems = $this->conn->prepare($sqlItems);
            if ($stmtItems) {
                $stmtItems->bind_param("i", $orderId);
                $stmtItems->execute();
                $resultItems = $stmtItems->get_result();
                while ($item = $resultItems->fetch_assoc()) {
                    $orderItems[] = $item;
                }
                $stmtItems->close();
            }
            $order['items'] = $orderItems;
        }

        return $order;
    }

    public function __destruct() {
        // The connection is managed by getDbConnection(), so we don't explicitly close it here.
    }
}
?>
