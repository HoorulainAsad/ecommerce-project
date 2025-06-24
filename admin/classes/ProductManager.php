<?php
// admin/classes/ProductManager.php

require_once __DIR__ . '/../includes/database.php';

class ProductManager {
    private $conn;

    public function __construct() {
        $this->conn = getDbConnection();
    }

    // IMPORTANT: Ensure this __destruct() method is ABSENT or commented out if it tries to close the connection.
    /*
    public function __destruct() {
        // Do NOT close the connection here. It's managed globally.
    }
    */

    /**
     * Adds a new product to the database.
     * @param string $name
     * @param string $description
     * @param float $price
     * @param int $categoryId
     * @param int $stock
     * @param string $imageUrl
     * @return bool True on success, false on failure.
     */
    public function addProduct($name, $description, $price, $categoryId, $stock, $imageUrl) {
        $sql = "INSERT INTO products (name, description, price, category_id, stock, image_url, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("ProductManager::addProduct - Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("ssdiis", $name, $description, $price, $categoryId, $stock, $imageUrl);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * Retrieves all products from the database.
     * @return array An array of product associative arrays.
     */
    public function getAllProducts() {
        $sql = "SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.name ASC";
        $result = $this->conn->query($sql);
        $products = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        }
        return $products;
    }

    /**
     * Retrieves a product by its ID.
     * @param int $productId
     * @return array|null An associative array of product data, or null if not found.
     */
    public function getProductById($productId) {
        $sql = "SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("ProductManager::getProductById - Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return null;
        }
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        $stmt->close();
        return $product;
    }

    /**
     * Updates an existing product.
     * @param int $id
     * @param string $name
     * @param string $description
     * @param float $price
     * @param int $categoryId
     * @param int $stock
     * @param string|null $imageUrl The new image URL, or null to keep existing.
     * @return bool True on success, false on failure.
     */
    public function updateProduct($id, $name, $description, $price, $categoryId, $stock, $imageUrl = null) {
        $sql = "UPDATE products SET name = ?, description = ?, price = ?, category_id = ?, stock = ?, updated_at = NOW()";
        $types = "ssdii";
        $params = [$name, $description, $price, $categoryId, $stock];

        if ($imageUrl !== null) { // Only update image_url if a new one is provided
            $sql .= ", image_url = ?";
            $types .= "s";
            $params[] = $imageUrl;
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;
        $types .= "i";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("ProductManager::updateProduct - Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return false;
        }
        // Use call_user_func_array for bind_param with dynamic parameters
        $bind_names = array($types);
        for ($i = 0; $i < count($params); $i++) {
            $bind_name = 'bind' . $i;
            $$bind_name = &$params[$i]; // Create a variable reference
            $bind_names[] = &$$bind_name;
        }
        call_user_func_array(array($stmt, 'bind_param'), $bind_names);

        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * Deletes a product by its ID.
     * @param int $productId
     * @return bool True on success, false on failure.
     */
    public function deleteProduct($productId) {
        // Optional: Get product details to delete image file from server
        $product = $this->getProductById($productId);
        if ($product && !empty($product['image_url'])) {
            // Construct the absolute path to the image file
            $imagePath = realpath(__DIR__ . '/../' . $product['image_url']);

            if ($imagePath && file_exists($imagePath) && is_file($imagePath)) {
                // Ensure the path is within the allowed uploads directory as a security measure
                if (strpos($imagePath, realpath(__DIR__ . '/../uploads/products/')) === 0) {
                    unlink($imagePath); // Delete the actual file
                } else {
                    error_log("ProductManager::deleteProduct - Attempt to delete file outside of uploads directory: " . $imagePath);
                }
            } else {
                error_log("ProductManager::deleteProduct - Image file not found or invalid path: " . ($imagePath ?: $product['image_url']));
            }
        }

        $sql = "DELETE FROM products WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("ProductManager::deleteProduct - Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("i", $productId);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * Get count of products with stock less than or equal to 0.
     * @return int
     */
    public function getOutOfStockProductCount() {
        $sql = "SELECT COUNT(*) AS count FROM products WHERE stock <= 0";
        $result = $this->conn->query($sql);
        if ($result) {
            $row = $result->fetch_assoc();
            return $row['count'];
        }
        return 0;
    }

    /**
     * Get count of all products.
     * @return int
     */
    public function getTotalProductCount() {
        $sql = "SELECT COUNT(*) AS count FROM products";
        $result = $this->conn->query($sql);
        if ($result) {
            $row = $result->fetch_assoc();
            return (int)$row['count']; // Cast to int for safety
        }
        return 0;
    }

    /**
     * Get count of new arrivals (products added in the last 30 days).
     * @return int
     */
    public function getNewArrivalsCount() {
        $sql = "SELECT COUNT(*) AS count FROM products WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $result = $this->conn->query($sql);
        if ($result) {
            $row = $result->fetch_assoc();
            return (int)$row['count']; // Cast to int for safety
        }
        return 0;
    }

    /**
     * Retrieves new arrival products (products added in the last 30 days).
     * @return array An array of product associative arrays.
     */
    public function getNewArrivalProducts() {
        $sql = "SELECT p.*, c.name AS category_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                ORDER BY p.created_at DESC";
        $result = $this->conn->query($sql);
        $products = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        }
        return $products;
    }

    /**
     * Retrieves products belonging to a specific category ID, with category name.
     * @param int $categoryId The ID of the category.
     * @return array An array of product associative arrays.
     */
    public function getProductsByCategoryId($categoryId) {
        $sql = "SELECT p.id, p.name, p.description, p.price, p.stock, p.image_url, c.name AS category_name
                 FROM products p
                 JOIN categories c ON p.category_id = c.id
                 WHERE p.category_id = ?
                 ORDER BY p.name ASC";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("ProductManager::getProductsByCategoryId - Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return [];
        }
        $stmt->bind_param("i", $categoryId);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        }
        $stmt->close();
        return $products;
    }

    /**
     * Decrements the stock of a product by a given quantity.
     * @param int $productId
     * @param int $quantity The amount to decrement the stock by.
     * @return bool True on success, false on failure.
     */
    public function updateProductStock($productId, $quantity) {
        $sql = "UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("ProductManager::updateProductStock - Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return false;
        }
        // Ensure stock doesn't go below zero (optional, but good practice)
        // By checking stock >= quantity, we prevent negative stock.
        // If you want to allow negative stock, remove the 'AND stock >= ?' and the third bind_param.
        $stmt->bind_param("iii", $quantity, $productId, $quantity);
        $result = $stmt->execute();

        if (!$result) {
            error_log("ProductManager::updateProductStock - Execute failed: (" . $stmt->errno . ") " . $stmt->error);
        }

        $stmt->close();
        return $result;
    }

    ---
    ## New Method for Trending Products
    ---

    /**
     * Fetches the top N trending products based on total quantity ordered within a time frame.
     * Requires 'order_items' and 'orders' tables.
     *
     * @param int $limit The number of trending products to retrieve (default: 3).
     * @param int $days The number of days to look back for orders (default: 30).
     * @return array An array of trending product data.
     */
    public function getTrendyProducts($limit = 3, $days = 30) {
        // It's crucial to use LEFT JOIN for 'categories' in case a product has no category assigned.
        // For 'order_items' and 'orders', INNER JOIN is generally correct if you only want products that HAVE been ordered.
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
                    products p
                JOIN
                    order_items oi ON p.id = oi.product_id
                JOIN
                    orders o ON oi.order_id = o.id
                LEFT JOIN
                    categories c ON p.category_id = c.id
                WHERE
                    o.order_date >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY
                    p.id, p.name, p.description, p.price, p.image_url, p.stock, c.name
                ORDER BY
                    total_ordered_quantity DESC
                LIMIT ?";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("ProductManager::getTrendyProducts - Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return []; // Return empty array on error
        }

        // Bind parameters: 'ii' for two integers ($days, $limit)
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
?>