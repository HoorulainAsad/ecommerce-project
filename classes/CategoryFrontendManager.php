<?php
// classes/CategoryFrontendManager.php

// In C:\xampp\htdocs\msgm_clothing\classes\CartManager.php
require_once __DIR__ . '/../admin/includes/database.php';

class CategoryFrontendManager {
    private $conn;

    public function __construct() {
        $this->conn = getDbConnection();
    }

    /**
     * Retrieves all categories from the database.
     * @return array An array of category associative arrays.
     */
    public function getAllCategories() {
        $sql = "SELECT id, name FROM categories ORDER BY name ASC";
        $result = $this->conn->query($sql);
        $categories = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $categories[] = $row;
            }
        }
        return $categories;
    }

    /**
     * Retrieves a single category by its name.
     * @param string $categoryName The name of the category.
     * @return array|null An associative array of category data, or null if not found.
     */
    public function getCategoryByName($categoryName) {
        $sql = "SELECT id, name FROM categories WHERE UPPER(name) = UPPER(?)";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("CategoryFrontendManager: Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return null;
        }
        $stmt->bind_param("s", $categoryName);
        $stmt->execute();
        $result = $stmt->get_result();
        $category = $result->fetch_assoc();
        $stmt->close();
        return $category;
    }

    
}
?>
