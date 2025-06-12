<?php
// admin/classes/CategoryManager.php

require_once __DIR__ . '/../includes/database.php';

class CategoryManager {
    private $conn;

    public function __construct() {
        $this->conn = getDbConnection();
    }

    /**
     * Adds a new category to the database.
     * @param string $name The name of the category.
     * @return bool True on success, false on failure (e.g., category already exists).
     */
    public function addCategory($name) {
        // Check if category already exists
        $checkSql = "SELECT id FROM categories WHERE name = ?";
        $stmtCheck = $this->conn->prepare($checkSql);
        if (!$stmtCheck) {
            error_log("CategoryManager: Check prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return false;
        }
        $stmtCheck->bind_param("s", $name);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();
        if ($resultCheck->num_rows > 0) {
            // Category with this name already exists
            $stmtCheck->close();
            return false;
        }
        $stmtCheck->close();

        $sql = "INSERT INTO categories (name) VALUES (?)";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("CategoryManager: Add prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("s", $name);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * Retrieves all categories from the database.
     * @return array An array of category associative arrays.
     */
    public function getAllCategories() {
        $sql = "SELECT * FROM categories ORDER BY name ASC";
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
     * Retrieves a category by its ID.
     * @param int $categoryId
     * @return array|null An associative array of category data, or null if not found.
     */
    public function getCategoryById($categoryId) {
        $sql = "SELECT * FROM categories WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("CategoryManager: Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return null;
        }
        $stmt->bind_param("i", $categoryId);
        $stmt->execute();
        $result = $stmt->get_result();
        $category = $result->fetch_assoc();
        $stmt->close();
        return $category;
    }

    /**
     * Updates an existing category.
     * @param int $id The ID of the category.
     * @param string $name The new name for the category.
     * @return bool True on success, false on failure.
     */
    public function updateCategory($id, $name) {
        // Check if a category with the new name already exists (excluding the current category being updated)
        $checkSql = "SELECT id FROM categories WHERE name = ? AND id != ?";
        $stmtCheck = $this->conn->prepare($checkSql);
        if (!$stmtCheck) {
            error_log("CategoryManager: Update check prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return false;
        }
        $stmtCheck->bind_param("si", $name, $id);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();
        if ($resultCheck->num_rows > 0) {
            // A different category with this name already exists
            $stmtCheck->close();
            return false;
        }
        $stmtCheck->close();

        $sql = "UPDATE categories SET name = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("CategoryManager: Update prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("si", $name, $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * Deletes a category by its ID.
     * Note: This will fail if there are products associated with this category
     * due to foreign key constraints (ON DELETE RESTRICT).
     * You would need to reassign products or delete them first.
     * @param int $categoryId
     * @return bool True on success, false on failure.
     */
    public function deleteCategory($categoryId) {
        $sql = "DELETE FROM categories WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("CategoryManager: Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("i", $categoryId);
        try {
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        } catch (mysqli_sql_exception $e) {
            // Catch foreign key constraint violation
            if ($e->getCode() == 1451) { // Error code for "Cannot delete or update a parent row: a foreign key constraint fails"
                error_log("Cannot delete category ID {$categoryId}: Products are still assigned to it. Please reassign or delete products first.");
                // You might return a specific error code or message to the UI here
                return false;
            }
            error_log("CategoryManager: Delete failed: " . $e->getMessage());
            return false;
        }
    }

    // Removed __destruct() method to prevent multiple connection closes.
    // The connection will be handled by the global getDbConnection/closeDbConnection functions.
}
?>
