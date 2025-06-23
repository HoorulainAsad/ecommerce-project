<?php
// C:\xampp\htdocs\msgm_clothing\classes\ReviewManager.php

require_once __DIR__ . '/../includes/database.php'; // Correct path to your database.php

class ReviewManager {
    private $conn;

    public function __construct() {
        $this->conn = getDbConnection();
        if (!$this->conn) {
            throw new Exception("ReviewManager: Could not establish database connection.");
        }
    }

    /**
     * Adds a new customer review.
     * @param int $orderId The ID of the order this review is for.
     * @param string $customerName The name of the customer.
     * @param int $rating The star rating (1-5).
     * @param string $comment The review comment.
     * @param string|null $customerEmail Optional: The email of the customer.
     * @param int|null $userId Optional: The ID of the registered user.
     * @param int|null $productId Optional: The ID of the product being reviewed.
     * @return bool True on success, false on failure.
     */
    public function addReview($orderId, $customerName, $rating, $comment, $customerEmail = null, $userId = null, $productId = null) {
        $sql = "INSERT INTO reviews (order_id, product_id, customer_name, rating, comment, customer_email, user_id, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("ReviewManager: Prepare failed to add review: (" . $this->conn->errno . ") " . $this->conn->error);
            return false;
        }

        // Note: Check your 'reviews' table columns. If 'user_id' or 'product_id' are not nullable
        // and you're passing null, it might cause issues.
        // Assuming 'i' for order_id, 'i' for product_id, 's' for customer_name, 'i' for rating,
        // 's' for comment, 's' for customer_email, 'i' for user_id.
        $stmt->bind_param("iisisss", $orderId, $productId, $customerName, $rating, $comment, $customerEmail, $userId);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * Updates the status of a review.
     * @param int $reviewId The ID of the review.
     * @param string $newStatus The new status ('approved', 'rejected', 'pending').
     * @return bool True on success, false on failure.
     */
    public function updateReviewStatus($reviewId, $newStatus) {
        $sql = "UPDATE reviews SET status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("ReviewManager: Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("si", $newStatus, $reviewId);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * Retrieves all reviews, optionally filtered by status.
     * @param string $statusFilter 'all', 'pending', 'approved', 'rejected'.
     * @return array An array of review associative arrays.
     */
    public function getAllReviews($statusFilter = 'all') {
        $sql = "SELECT r.*, p.name AS product_name, u.username AS customer_username
                FROM reviews r
                LEFT JOIN products p ON r.product_id = p.id
                LEFT JOIN users u ON r.user_id = u.id";
        $whereClause = "";
        $params = [];
        $types = "";

        if ($statusFilter !== 'all') {
            $whereClause = " WHERE r.status = ?";
            $params[] = $statusFilter;
            $types = "s";
        }

        $sql .= $whereClause . " ORDER BY r.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("ReviewManager: Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return [];
        }

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $reviews = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $reviews[] = $row;
            }
        }
        $stmt->close();
        return $reviews;
    }

    /**
     * Gets the count of reviews by status.
     * @param string $status The status to count (e.g., 'pending', 'approved').
     * @return int The total number of reviews with that status.
     */
    public function getReviewCountByStatus($status = 'pending') {
        $sql = "SELECT COUNT(*) as total FROM reviews WHERE status = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("ReviewManager: Failed to prepare getReviewCountByStatus: " . $this->conn->error);
            return 0;
        }

        $stmt->bind_param("s", $status);
        $stmt->execute();
        $stmt->bind_result($total);
        $stmt->fetch();
        $stmt->close();

        return $total;
    }

    /**
     * Deletes a review from the database.
     * THIS IS THE MISSING METHOD!
     * @param int $reviewId The ID of the review to delete.
     * @return bool True on success, false on failure.
     */
    public function deleteReview($reviewId) {
        $sql = "DELETE FROM reviews WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("ReviewManager: Prepare failed to delete review: (" . $this->conn->errno . ") " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("i", $reviewId);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
}
?>