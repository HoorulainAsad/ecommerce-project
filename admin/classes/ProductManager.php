<?php
// admin/classes/ProductManager.php

require_once __DIR__ . '/../includes/database.php'; // Ensure this path is correct

class ProductManager {
    private $conn;

    public function __construct() {
        $this->conn = getDbConnection(); // Get the single, shared database connection
    }

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
        $types = "ssdiis";
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
        $stmt->bind_param($types, ...$params);
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
            // BASE_URL for admin is usually relative to htdocs/msgm_clothing/admin/
            // Product image URLs are saved as 'admin/uploads/products/filename.jpg'
            // So, from admin/, it's '../admin/uploads/products/filename.jpg'
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

    // IMPORTANT: Removed __destruct() method to prevent "mysqli object is already closed" errors.
    // The database connection is now managed globally by admin/includes/database.php.
}
?>
