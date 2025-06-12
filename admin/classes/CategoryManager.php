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
     * @return bool True on success, false on failure.
     */
    public function addCategory($name) {
        $sql = "INSERT INTO categories (name) VALUES (?)";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("CategoryManager: Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
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
     * Deletes a category by its ID.
     * @param int $id The ID of the category to delete.
     * @return bool True on success, false on failure.
     */
    public function deleteCategory($id) {
        $sql = "DELETE FROM categories WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("CategoryManager: Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * Closes the database connection.
     */
    public function __destruct() {
        $this->conn->close();
    }
}
?>
