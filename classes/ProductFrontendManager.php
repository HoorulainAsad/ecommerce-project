<?php
// classes/ProductFrontendManager.php

require_once __DIR__ . '/../includes/database.php';

class ProductFrontendManager {
    private $conn;

    public function __construct() {
        $this->conn = getDbConnection();
    }

    /**
     * Retrieves products based on filter (all, category, new arrivals, trendy).
     * @param string $filter 'all', 'bridal', 'formal', 'partywear', 'new_arrivals', 'trendy'.
     * @param int|null $limit Optional limit for number of products.
     * @return array An array of product associative arrays.
     */
    public function getFilteredProducts($filter = 'all', $limit = null) {
        $sql = "SELECT p.id, p.name, p.description, p.price, p.stock, p.image_url, c.name AS category_name, c.id AS category_id
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id";

        $whereClause = "";
        $params = [];
        $types = "";
        $orderBy = "p.created_at DESC"; // Default order

        switch ($filter) {
            case 'bridal':
            case 'formal':
            case 'partywear':
                // Get category ID dynamically
                $categorySql = "SELECT id FROM categories WHERE UPPER(name) = ?";
                $stmtCat = $this->conn->prepare($categorySql);
                if ($stmtCat) {
                    $upperFilter = strtoupper($filter);
                    $stmtCat->bind_param("s", $upperFilter);
                    $stmtCat->execute();
                    $resultCat = $stmtCat->get_result();
                    $cat = $resultCat->fetch_assoc();
                    $stmtCat->close();
                    if ($cat) {
                        $whereClause = " WHERE p.category_id = ?";
                        $params[] = $cat['id'];
                        $types .= "i";
                    }
                }
                break;
            case 'new_arrivals':
                $whereClause = " WHERE p.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                $orderBy = "p.created_at DESC";
                break;
            case 'trendy':
                // For trendy, we need to join with order_items and count
                $sql = "SELECT p.id, p.name, p.description, p.price, p.stock, p.image_url, c.name AS category_name, c.id AS category_id,
                               SUM(oi.quantity) AS total_ordered_quantity
                        FROM products p
                        LEFT JOIN categories c ON p.category_id = c.id
                        LEFT JOIN order_items oi ON p.id = oi.product_id
                        GROUP BY p.id, p.name, p.description, p.price, p.stock, p.image_url, c.name, c.id
                        ORDER BY total_ordered_quantity DESC";
                $orderBy = null; // Overridden by custom order by in the query itself
                break;
            case 'all':
            default:
                // No specific where clause for 'all'
                $orderBy = "p.name ASC"; // Default alphabetical for 'all' products
                break;
        }

        $fullSql = $sql . $whereClause;
        if ($orderBy) {
            $fullSql .= " ORDER BY " . $orderBy;
        }
        if ($limit !== null && is_int($limit) && $limit > 0) {
            $fullSql .= " LIMIT ?";
            $params[] = $limit;
            $types .= "i";
        }


        $stmt = $this->conn->prepare($fullSql);
        if (!$stmt) {
            error_log("ProductFrontendManager: Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return [];
        }

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $products = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Add 'is_out_of_stock' flag for easy display
                $row['is_out_of_stock'] = ($row['stock'] <= 0);
                $products[] = $row;
            }
        }
        $stmt->close();
        return $products;
    }

    /**
     * Retrieves a single product by its ID.
     * @param int $productId The ID of the product.
     * @return array|null An associative array of product data, or null if not found.
     */
    public function getProductById($productId) {
        $sql = "SELECT p.id, p.name, p.description, p.price, p.stock, p.image_url, c.name AS category_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("ProductFrontendManager: Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return null;
        }
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        $stmt->close();

        if ($product) {
            $product['is_out_of_stock'] = ($product['stock'] <= 0);
        }
        return $product;
    }

    /**
     * Closes the database connection.
     */
    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
?>
