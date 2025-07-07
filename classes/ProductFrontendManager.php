<?php
// classes/ProductFrontendManager.php

require_once __DIR__ . '/../admin/includes/database.php';

class ProductFrontendManager {
    private $conn;

    public function __construct() {
        $this->conn = getDbConnection();
    }

    /**
     * Retrieves products based on filter (all, category, new arrivals, trendy).
     * @param string $filter 'all', 'bridal', 'formal', 'partywear', 'new_arrivals', 'trendy'.
     * @param int|null $limit Optional limit for number of products. Default is 10 for trendy if not specified.
     * @param int $days Optional number of days to look back for trendy products (default 30).
     * @return array An array of product associative arrays.
     */
    public function getFilteredProducts($filter = 'all', $limit = null, $days = 30) {
        $sql = ""; 
        $whereClause = "";
        $params = [];
        $types = "";
        $orderBy = ""; 
        $groupBy = ""; 

        switch (strtolower($filter)) {
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
                        $sql = "SELECT p.id, p.name, p.description, p.price, p.stock, p.image_url, c.name AS category_name, c.id AS category_id
                                FROM products p LEFT JOIN categories c ON p.category_id = c.id";
                        $whereClause = " WHERE p.category_id = ?";
                        $params[] = $cat['id'];
                        $types .= "i";
                        $orderBy = "p.name ASC";
                    } else {
                        // Category not found, return empty set
                        return [];
                    }
                } else {
                    error_log("ProductFrontendManager: Category lookup prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
                    return [];
                }
                break;

            case 'new_arrivals':
                $sql = "SELECT p.id, p.name, p.description, p.price, p.stock, p.image_url, c.name AS category_name, c.id AS category_id
                        FROM products p LEFT JOIN categories c ON p.category_id = c.id";
                $whereClause = " WHERE p.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                $orderBy = "p.created_at DESC";
                break;

            case 'trendy':
                $sql = "SELECT p.id, p.name, p.description, p.price, p.stock, p.image_url, c.name AS category_name, c.id AS category_id,
                               SUM(oi.quantity) AS total_ordered_quantity
                         FROM products p
                         LEFT JOIN categories c ON p.category_id = c.id
                         JOIN order_items oi ON p.id = oi.product_id
                         JOIN orders o ON oi.order_id = o.id"; 
                
                $whereClause = " WHERE o.order_date >= DATE_SUB(NOW(), INTERVAL ? DAY)"; // Date filter
                $params[] = $days; 
                $types .= "i";

                $groupBy = " GROUP BY p.id, p.name, p.description, p.price, p.stock, p.image_url, c.name, c.id";
                $orderBy = "total_ordered_quantity DESC";
                
                if ($limit === null) {
                    $limit = 3; 
                }
                break;

            case 'all':
            default:
                $sql = "SELECT p.id, p.name, p.description, p.price, p.stock, p.image_url, c.name AS category_name, c.id AS category_id
                        FROM products p LEFT JOIN categories c ON p.category_id = c.id";
                $orderBy = "p.name ASC"; 
                break;
        }

        $fullSql = $sql . $whereClause . $groupBy;
        
        if (!empty($orderBy)) { 
            $fullSql .= " ORDER BY " . $orderBy;
        }

        if ($limit !== null && is_int($limit) && $limit > 0) {
            $fullSql .= " LIMIT ?";
            $params[] = $limit;
            $types .= "i";
        }

        $stmt = $this->conn->prepare($fullSql);
        if (!$stmt) {
            error_log("ProductFrontendManager: Prepare failed for filter '{$filter}' - (" . $this->conn->errno . ") " . $this->conn->error . " SQL: " . $fullSql);
            return [];
        }

        if (!empty($params)) {
            $bind_names = array($types);
            for ($i = 0; $i < count($params); $i++) {
                $bind_name = 'bind' . $i;
                $$bind_name = &$params[$i]; // Create a variable reference
                $bind_names[] = &$$bind_name;
            }
            call_user_func_array(array($stmt, 'bind_param'), $bind_names);
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
}
?>