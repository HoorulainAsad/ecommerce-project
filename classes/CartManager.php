<?php
// In C:\xampp\htdocs\msgm_clothing\classes\CartManager.php
require_once __DIR__ . '/../admin/includes/database.php';
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

        
        $stmt = $this->conn->prepare("SELECT id FROM cart WHERE product_id = ? AND size = ? AND (user_id = ? OR session_id = ?)");
        $stmt->bind_param("isis", $productId, $size, $userId, $sessionId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $cartId = $row['id'];
            $stmt = $this->conn->prepare("UPDATE cart SET quantity = quantity + ?, is_checked = 1 WHERE id = ?"); // Set to checked when adding more
            $stmt->bind_param("ii", $quantity, $cartId);
        } else {
            $stmt = $this->conn->prepare("INSERT INTO cart (product_id, quantity, size, user_id, session_id, is_checked) VALUES (?, ?, ?, ?, ?, 1)");
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
            ORDER BY c.id ASC
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

    
    public function getCheckedCartItems() {
        
        $userId = $_SESSION['user_id'] ?? null;
        $sessionId = session_id();

        $stmt = $this->conn->prepare("SELECT c.product_id, c.quantity, p.price, p.name 
                                     FROM cart c
                                     JOIN products p ON c.product_id = p.id
                                     WHERE (c.user_id = ? OR c.session_id = ?) AND c.is_checked = 1");
        $stmt->bind_param("is", $userId, $sessionId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }


   
    public function getCheckedCartTotal() {
        $checkedItems = $this->getCheckedCartItems();
        $total = 0.0;

        foreach ($checkedItems as $item) {
            $rawPrice = str_replace(',', '', $item['price']);
            $price = floatval($rawPrice);                     
            $quantity = intval($item['quantity']);           
            $total += $price * $quantity;
        }

        return $total;
    }


   
    public function updateItemCheckedStatus($cartId, $isChecked) {
        $status = $isChecked ? 1 : 0;
        $stmt = $this->conn->prepare("UPDATE cart SET is_checked = ? WHERE id = ?");
        $stmt->bind_param("ii", $status, $cartId);
        return $stmt->execute();
    }

    /**
     * Updates the quantity of a cart item.
     * Also ensures that the item is checked by default if its quantity is increased.
     * @param int $cartId
     * @param string $action 'increase' or 'decrease'
     * @return bool
     */
    public function updateQuantity($cartId, $action) {
        if ($action === 'increase') {
            $stmt = $this->conn->prepare("UPDATE cart SET quantity = quantity + 1, is_checked = 1 WHERE id = ?"); // Set to checked on increase
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

    public function getTotalCartItemCount() {
        $count = 0;
        $userId = $_SESSION['user_id'] ?? null;
        $sessionId = session_id();

        if ($userId) {
            $stmt = $this->conn->prepare("SELECT SUM(quantity) AS total FROM cart WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
        } else {
            $stmt = $this->conn->prepare("SELECT SUM(quantity) AS total FROM cart WHERE session_id = ?");
            $stmt->bind_param("s", $sessionId);
        }
        
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        return $count ?? 0;
    }
}