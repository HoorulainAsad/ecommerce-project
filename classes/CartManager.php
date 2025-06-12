<?php
// classes/CartManager.php

require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/ProductFrontendManager.php'; // Needed to fetch product details

class CartManager {
    private $conn;
    private $productManager;

    public function __construct() {
        $this->conn = getDbConnection();
        $this->productManager = new ProductFrontendManager();
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->initializeCart();
    }

    private function initializeCart() {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = []; // cart will be an associative array: product_id_size => ['product_id' => ID, 'quantity' => QTY, 'size' => SIZE, 'price' => PRICE, 'name' => NAME, 'image_url' => URL]
        }
        // If a user logs in after adding items as guest, or vice-versa, handle cart merging/loading
        if (isUserLoggedIn() && !isset($_SESSION['cart_loaded_for_user'])) {
            $this->loadCartFromDatabase(true); // Load and merge if guest items exist
            $_SESSION['cart_loaded_for_user'] = true;
        } elseif (!isUserLoggedIn() && isset($_SESSION['cart_loaded_for_user'])) {
            // User logged out, clear cart_loaded_for_user flag to allow fresh load next login
            unset($_SESSION['cart_loaded_for_user']);
        }

        // Assign a session ID if not set, for anonymous cart persistence
        if (!isset($_SESSION['session_id'])) {
            $_SESSION['session_id'] = session_id();
        }
    }

    /**
     * Adds a product to the cart.
     * @param int $productId
     * @param int $quantity
     * @param string $size
     * @return bool True on success, false on failure (e.g., product not found, out of stock, invalid quantity).
     */
    public function addToCart($productId, $quantity, $size = 'N/A') {
        if ($quantity <= 0) {
            return false;
        }

        $product = $this->productManager->getProductById($productId);
        if (!$product || $product['is_out_of_stock'] || $product['stock'] < $quantity) {
            return false; // Product not found or not enough initial stock
        }

        // Item key unique by product ID and size
        $itemKey = $productId . '_' . $size;

        // Check if item already exists in cart with the same size
        if (isset($_SESSION['cart'][$itemKey])) {
            $currentQuantity = $_SESSION['cart'][$itemKey]['quantity'];
            if ($product['stock'] < ($currentQuantity + $quantity)) {
                return false; // Adding more would exceed current stock
            }
            $_SESSION['cart'][$itemKey]['quantity'] += $quantity;
        } else {
            // Add new item to cart
            $_SESSION['cart'][$itemKey] = [
                'product_id' => $productId,
                'quantity' => $quantity,
                'size' => $size,
                'price' => $product['price'], // Store price at time of adding
                'name' => $product['name'], // Store name at time of adding
                'image_url' => $product['image_url'] // Store image URL
            ];
        }

        $this->saveCartToDatabase(); // Persist cart state to DB if logged in or for session
        return true;
    }

    /**
     * Updates the quantity of a product in the cart.
     * @param int $productId
     * @param int $newQuantity
     * @param string $size
     * @return bool True on success, false on failure.
     */
    public function updateCartItemQuantity($productId, $newQuantity, $size = 'N/A') {
        $itemKey = $productId . '_' . $size;
        if (!isset($_SESSION['cart'][$itemKey])) {
            return false; // Item not in cart
        }

        if ($newQuantity <= 0) {
            return $this->removeFromCart($productId, $size); // Remove if quantity is zero or less
        }

        $product = $this->productManager->getProductById($productId);
        if (!$product || $product['stock'] < $newQuantity) {
            return false; // Product not found or not enough stock for the new quantity
        }

        $_SESSION['cart'][$itemKey]['quantity'] = $newQuantity;
        $this->saveCartToDatabase();
        return true;
    }

    /**
     * Removes a product from the cart.
     * @param int $productId
     * @param string $size
     * @return bool True on success, false on failure.
     */
    public function removeFromCart($productId, $size = 'N/A') {
        $itemKey = $productId . '_' . $size;
        if (isset($_SESSION['cart'][$itemKey])) {
            unset($_SESSION['cart'][$itemKey]);
            $this->saveCartToDatabase(); // Update DB cart state
            return true;
        }
        return false;
    }

    /**
     * Retrieves all items currently in the cart with their full product details.
     * Checks current stock and marks items out of stock if necessary.
     * @return array An array of cart items, each including relevant product details and stock status.
     */
    public function getCartItems() {
        $fullCartItems = [];
        foreach ($_SESSION['cart'] as $itemKey => $cartItem) {
            $product = $this->productManager->getProductById($cartItem['product_id']);
            if ($product) {
                $isOutOfStock = ($product['stock'] <= 0 || $product['stock'] < $cartItem['quantity']);
                $fullCartItems[$itemKey] = [
                    'product_id' => $product['id'],
                    'name' => $cartItem['name'], // Use name from cart (original name at time of adding)
                    'price' => $cartItem['price'], // Use price from cart (original price at time of adding)
                    'quantity' => $cartItem['quantity'],
                    'size' => $cartItem['size'],
                    'image_url' => $product['image_url'], // Always use current image URL
                    'current_stock' => $product['stock'], // Current stock from DB
                    'is_out_of_stock' => $isOutOfStock // True if product is generally out of stock or requested quantity exceeds current stock
                ];
            } else {
                // Product no longer exists in DB, remove from session cart
                unset($_SESSION['cart'][$itemKey]);
                $this->saveCartToDatabase(); // Update DB cart state
            }
        }
        return $fullCartItems;
    }

    /**
     * Calculates the total value of items in the cart.
     * @return float The total amount.
     */
    public function getCartTotal() {
        $total = 0;
        foreach ($this->getCartItems() as $item) {
            // Only add to total if not out of stock (or handle as per business logic)
            // For now, we'll include all items in total, even if out of stock,
            // but the checkout will prevent order if any item has insufficient stock.
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }

    /**
     * Gets the number of unique item types in the cart.
     * @return int The count of unique items.
     */
    public function getCartItemCount() {
        return count($_SESSION['cart']);
    }

    /**
     * Clears all items from the cart (session and database if applicable).
     */
    public function clearCart() {
        $_SESSION['cart'] = [];
        if (isUserLoggedIn()) {
            $this->clearDatabaseCart($_SESSION['user_id']);
        } else {
            // Clear anonymous cart from DB using session ID
            $this->clearDatabaseCart(null, $_SESSION['session_id']);
        }
    }

    /**
     * Saves the current session cart to the database.
     * For logged-in users: Overwrites existing cart for the user in the DB.
     * For anonymous users: Overwrites existing cart for the session ID in the DB.
     */
    private function saveCartToDatabase() {
        $userId = isUserLoggedIn() ? $_SESSION['user_id'] : null;
        $sessionId = session_id(); // Always use the current session ID for anonymous carts

        $this->conn->begin_transaction();
        try {
            // Clear existing cart items for this user/session in DB
            if ($userId) {
                $stmt = $this->conn->prepare("DELETE FROM cart_items WHERE user_id = ?");
                $stmt->bind_param("i", $userId);
            } else {
                $stmt = $this->conn->prepare("DELETE FROM cart_items WHERE session_id = ? AND user_id IS NULL");
                $stmt->bind_param("s", $sessionId);
            }
            if (!$stmt) throw new Exception("Prepare delete failed: " . $this->conn->error);
            $stmt->execute();
            $stmt->close();

            // Insert current cart items from session into DB
            $stmt = $this->conn->prepare("INSERT INTO cart_items (user_id, session_id, product_id, quantity, size) VALUES (?, ?, ?, ?, ?)");
            if (!$stmt) throw new Exception("Prepare insert failed: " . $this->conn->error);

            foreach ($_SESSION['cart'] as $itemKey => $item) {
                $stmt->bind_param("isiss", $userId, $sessionId, $item['product_id'], $item['quantity'], $item['size']);
                $stmt->execute();
            }
            $stmt->close();
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Failed to save cart to database: " . $e->getMessage());
        }
    }

    /**
     * Loads cart items from the database into the session.
     * If mergeWithSession is true, existing session items are kept and database items are added/merged.
     * If user is logged in, loads user's cart. If anonymous, loads session ID's cart.
     * @param bool $mergeWithSession If true, merges with existing session cart. Otherwise, replaces.
     */
    private function loadCartFromDatabase($mergeWithSession = false) {
        $userId = isUserLoggedIn() ? $_SESSION['user_id'] : null;
        $sessionId = session_id();

        if ($userId) {
            $sql = "SELECT product_id, quantity, size FROM cart_items WHERE user_id = ?";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) { error_log("Load cart (user) prepare failed: " . $this->conn->error); return; }
            $stmt->bind_param("i", $userId);
        } else {
            $sql = "SELECT product_id, quantity, size FROM cart_items WHERE session_id = ? AND user_id IS NULL";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) { error_log("Load cart (session) prepare failed: " . $this->conn->error); return; }
            $stmt->bind_param("s", $sessionId);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $dbCart = [];
        while ($row = $result->fetch_assoc()) {
            $dbCart[$row['product_id'] . '_' . $row['size']] = [
                'product_id' => $row['product_id'],
                'quantity' => $row['quantity'],
                'size' => $row['size']
            ];
        }
        $stmt->close();

        if ($mergeWithSession) {
            // Merge DB cart with existing session cart
            foreach ($dbCart as $dbItemKey => $dbItem) {
                if (isset($_SESSION['cart'][$dbItemKey])) {
                    // Item exists in both, add quantities (ensure not to exceed stock)
                    // We will re-validate stock in getCartItems and at checkout
                    $_SESSION['cart'][$dbItemKey]['quantity'] += $dbItem['quantity'];
                } else {
                    // Item only in DB, add to session (fetch full product details for cart display)
                    $product = $this->productManager->getProductById($dbItem['product_id']);
                    if ($product) {
                        $_SESSION['cart'][$dbItemKey] = [
                            'product_id' => $dbItem['product_id'],
                            'quantity' => $dbItem['quantity'],
                            'size' => $dbItem['size'],
                            'price' => $product['price'],
                            'name' => $product['name'],
                            'image_url' => $product['image_url']
                        ];
                    }
                }
            }
        } else {
            // Replace session cart with DB cart
            $_SESSION['cart'] = [];
            foreach ($dbCart as $dbItemKey => $dbItem) {
                $product = $this->productManager->getProductById($dbItem['product_id']);
                if ($product) {
                    $_SESSION['cart'][$dbItemKey] = [
                        'product_id' => $dbItem['product_id'],
                        'quantity' => $dbItem['quantity'],
                        'size' => $dbItem['size'],
                        'price' => $product['price'],
                        'name' => $product['name'],
                        'image_url' => $product['image_url']
                    ];
                }
            }
        }
    }

    /**
     * Clears cart items from the database based on user ID or session ID.
     * @param int|null $userId
     * @param string|null $sessionId
     */
    public function clearDatabaseCart($userId = null, $sessionId = null) {
        if ($userId) {
            $stmt = $this->conn->prepare("DELETE FROM cart_items WHERE user_id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $stmt->close();
            } else {
                error_log("Failed to prepare clear database cart by user ID statement: " . $this->conn->error);
            }
        } elseif ($sessionId) {
            $stmt = $this->conn->prepare("DELETE FROM cart_items WHERE session_id = ? AND user_id IS NULL");
            if ($stmt) {
                $stmt->bind_param("s", $sessionId);
                $stmt->execute();
                $stmt->close();
            } else {
                error_log("Failed to prepare clear database cart by session ID statement: " . $this->conn->error);
            }
        }
    }
}
?>
