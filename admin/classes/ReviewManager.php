<?php
// admin/classes/ReviewManager.php

require_once __DIR__ . '/../includes/database.php';

class ReviewManager {
    private $conn;

    public function __construct() {
        $this->conn = getDbConnection();
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
     * Updates the status of a review.
     * @param int $reviewId The ID of the review.
     * @param string $newStatus The new status ('approved', 'rejected', 'pending').
     * @return bool True on success, false on failure.
     */
    public function updateReviewStatus($reviewId, $newStatus) {
        $sql = "UPDATE reviews SET status = ?, updated_at = NOW() WHERE id = ?";
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
     * Deletes a review.
     * @param int $reviewId The ID of the review to delete.
     * @return bool True on success, false on failure.
     */
    public function deleteReview($reviewId) {
        $sql = "DELETE FROM reviews WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("ReviewManager: Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("i", $reviewId);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * Retrieves the count of reviews by a specific status.
     * @param string $status The status to count (e.g., 'pending', 'approved', 'rejected').
     * @return int The number of reviews with the given status.
     */
    public function getReviewCountByStatus($status) {
        $sql = "SELECT COUNT(*) AS count FROM reviews WHERE status = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("ReviewManager: Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return 0;
        }
        $stmt->bind_param("s", $status);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            $row = $result->fetch_assoc();
            $stmt->close();
            return $row['count'];
        }
        $stmt->close();
        return 0;
    }

    /**
     * Retrieves the count of pending reviews. (Legacy, use getReviewCountByStatus('Pending'))
     * @return int The number of pending reviews.
     */
    public function getPendingReviewCount() {
        return $this->getReviewCountByStatus('Pending');
    }

    // No __destruct() here, as database connection is handled globally.
}
?>
