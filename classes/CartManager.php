<?php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/ProductFrontendManager.php';

class CartManager {
    private $conn;
    private $productManager;

    public function __construct() {
        $this->conn = getDbConnection();
        $this->productManager = new ProductFrontendManager();
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function addToCart($productId, $quantity, $size) {
        $userId = $_SESSION['user_id'] ?? null;
        $sessionId = session_id();

        // Check if product already in cart
        $stmt = $this->conn->prepare("SELECT id FROM cart WHERE product_id = ? AND size = ? AND (user_id = ? OR session_id = ?)");
        $stmt->bind_param("isis", $productId, $size, $userId, $sessionId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            // Update quantity
            $cartId = $row['id'];
            $stmt = $this->conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE id = ?");
            $stmt->bind_param("ii", $quantity, $cartId);
        } else {
            // Insert new
            $stmt = $this->conn->prepare("INSERT INTO cart (product_id, quantity, size, user_id, session_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iisis", $productId, $quantity, $size, $userId, $sessionId);
        }

        return $stmt->execute();
    }

    public function getCartItems() {
        $userId = $_SESSION['user_id'] ?? null;
        $sessionId = session_id();

        $stmt = $this->conn->prepare("
            SELECT c.*, p.name, p.price, p.image_url 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE (c.user_id = ? OR c.session_id = ?)
        ");
        $stmt->bind_param("is", $userId, $sessionId);
        $stmt->execute();
        $result = $stmt->get_result();

        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }

        return $items;
    }

    public function updateQuantity($cartId, $action) {
        if ($action === 'increase') {
            $stmt = $this->conn->prepare("UPDATE cart SET quantity = quantity + 1 WHERE id = ?");
        } elseif ($action === 'decrease') {
            $stmt = $this->conn->prepare("UPDATE cart SET quantity = quantity - 1 WHERE id = ? AND quantity > 1");
        } else {
            return false;
        }
        $stmt->bind_param("i", $cartId);
        return $stmt->execute();
    }

    public function deleteItem($cartId) {
        $stmt = $this->conn->prepare("DELETE FROM cart WHERE id = ?");
        $stmt->bind_param("i", $cartId);
        return $stmt->execute();
    }
}
?>
