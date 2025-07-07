<?php
//admin/classes/CategoryManager.php
require_once __DIR__ . '/../includes/database.php';

class CategoryManager {
    private $conn;

    public function __construct() {
        $this->conn = getDbConnection();
    }

    public function addCategory($name) {
        if (empty($name)) {
            error_log("CategoryManager::addCategory - Category name cannot be empty.");
            return false;
        }
        
        $checkSql = "SELECT id FROM categories WHERE name = ?";
        $stmtCheck = $this->conn->prepare($checkSql);
        if (!$stmtCheck) {
            error_log("CategoryManager::addCategory - Check prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return false;
        }
        $stmtCheck->bind_param("s", $name);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();
        if ($resultCheck->num_rows > 0) {
            $stmtCheck->close();
            return false;
        }
        $stmtCheck->close();

        $sql = "INSERT INTO categories (name) VALUES (?)";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("CategoryManager::addCategory - Add prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("s", $name);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

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

    public function getCategoryById($categoryId) {
        $sql = "SELECT * FROM categories WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("CategoryManager::getCategoryById - Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return null;
        }
        $stmt->bind_param("i", $categoryId);
        $stmt->execute();
        $result = $stmt->get_result();
        $category = $result->fetch_assoc();
        $stmt->close();
        return $category;
    }

    public function updateCategory($id, $name) {
        if (empty($name)) {
            error_log("CategoryManager::updateCategory - Category name cannot be empty.");
            return false;
        }

        $checkSql = "SELECT id FROM categories WHERE name = ? AND id != ?";
        $stmtCheck = $this->conn->prepare($checkSql);
        if (!$stmtCheck) {
            error_log("CategoryManager::updateCategory - Update check prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return false;
        }
        $stmtCheck->bind_param("si", $name, $id);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();
        if ($resultCheck->num_rows > 0) {
            $stmtCheck->close();
            return false;
        }
        $stmtCheck->close();

        $sql = "UPDATE categories SET name = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("CategoryManager::updateCategory - Update prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("si", $name, $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function deleteCategory($categoryId) {
        $sql = "DELETE FROM categories WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("CategoryManager::deleteCategory - Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("i", $categoryId);
        try {
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1451) {
                error_log("Cannot delete category ID {$categoryId}: Products are still assigned to it. Please reassign or delete products first.");
                return false;
            }
            error_log("CategoryManager::deleteCategory - Delete failed: " . $e->getMessage());
            return false;
        }
    }
}
