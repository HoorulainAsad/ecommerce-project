<?php
// admin/customerfeedback.php

require_once __DIR__ . '/includes/functions.php'; 
require_once __DIR__ . '/classes/ReviewManager.php';

// Check if admin is logged in
if (!isLoggedIn()) {
    redirectToAdmin('login.php');
}

$reviewManager = new ReviewManager();

$message = '';
$message_type = ''; 
// Handle review status update or deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_action'])) {
    $reviewId = filter_var($_POST['review_id'] ?? 0, FILTER_VALIDATE_INT);
    $action = sanitizeInput($_POST['review_action']);

    if ($reviewId > 0) {
        if ($action === 'approve') {
            if ($reviewManager->updateReviewStatus($reviewId, 'Approved')) {
                $message = "Review approved successfully!";
                $message_type = 'success';
            } else {
                $message = "Error approving review.";
                $message_type = 'error';
            }
        } elseif ($action === 'reject') {
            if ($reviewManager->updateReviewStatus($reviewId, 'Rejected')) {
                $message = "Review rejected successfully!";
                $message_type = 'success';
            } else {
                $message = "Error rejecting review.";
                $message_type = 'error';
            }
        } elseif ($action === 'delete') {
            if ($reviewManager->deleteReview($reviewId)) {
                $message = "Review deleted successfully!";
                $message_type = 'success';
            } else {
                $message = "Error deleting review.";
                $message_type = 'error';
            }
        }
    } else {
        $message = "Invalid review ID.";
        $message_type = 'error';
    }
}

// Check for messages from redirects (e.g., after deletion/update)
if (isset($_GET['msg']) && isset($_GET['type'])) {
    $message = sanitizeInput($_GET['msg']);
    $message_type = sanitizeInput($_GET['type']);
}

$reviews = $reviewManager->getAllReviews();

// No explicit unset($reviewManager) needed here, destructor will handle it.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Feedback - MSGM Bridal Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Anonymous+Pro:ital,wght@0,400;0,700;1,400;1,700&family=Lora:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>admin/assets/css/styles.css"> <!-- Link to external stylesheet -->
    
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'sidepanel.php'; ?>

        <div class="main-content-area">
            <?php include 'navbar.php'; ?>

            <h1 class="page-header">Customer Feedback & Reviews</h1>

            <?php if ($message): ?>
                <?php displayMessage($message, $message_type); ?>
            <?php endif; ?>

            <?php if (empty($reviews)): ?>
                <p>No customer reviews found.</p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Product</th>
                            <th>Customer</th>
                            <th>Rating</th>
                            <th>Comment</th>
                            <th>Submitted On</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reviews as $review): ?>
                            <tr>
                                <td data-label="ID"><?php echo htmlspecialchars($review['id']); ?></td>
                                <td data-label="Product">
                                    <?php echo htmlspecialchars($review['product_name'] ?? 'N/A'); ?>
                                    <?php if ($review['product_id']): ?><br><small>(ID: <?php echo htmlspecialchars($review['product_id']); ?>)</small><?php endif; ?>
                                </td>
                                <td data-label="Customer">
                                    <?php echo htmlspecialchars($review['customer_name']); ?>
                                    <?php if ($review['user_id']): ?><br><small>(User: <?php echo htmlspecialchars($review['customer_username']); ?>)</small><?php endif; ?>

                                    <?php if ($review['customer_email']): ?><br><small>(<?php echo htmlspecialchars($review['customer_email']); ?>)</small><?php endif; ?>
                                </td>
                                <td data-label="Rating">
                                    <span class="star-rating">
                                        <?php for ($i = 0; $i < $review['rating']; $i++): ?><i class="fas fa-star"></i><?php endfor; ?>
                                        <?php for ($i = $review['rating']; $i < 5; $i++): ?><i class="far fa-star"></i><?php endfor; ?>
                                    </span>
                                </td>
                                <td data-label="Comment"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></td>
                                <td data-label="Submitted On"><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($review['created_at']))); ?></td>
                                <td data-label="Status">
                                    <span class="review-status-<?php echo strtolower(htmlspecialchars($review['status'])); ?>">
                                        <?php echo htmlspecialchars($review['status']); ?>
                                    </span>
                                </td>
                                <td data-label="Actions" class="review-actions">
                                    <?php if ($review['status'] === 'Pending'): ?>
                                        <form action="customerfeedback.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="review_id" value="<?php echo htmlspecialchars($review['id']); ?>">
                                            <input type="hidden" name="review_action" value="approve">
                                            <button type="submit" title="Approve"><i class="fas fa-check-circle"></i></button>
                                        </form>
                                        <form action="customerfeedback.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="review_id" value="<?php echo htmlspecialchars($review['id']); ?>">
                                            <input type="hidden" name="review_action" value="reject">
                                            <button type="submit" title="Reject"><i class="fas fa-times-circle"></i></button>
                                        </form>
                                    <?php endif; ?>
                                    <form action="customerfeedback.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this review?');">
                                        <input type="hidden" name="review_id" value="<?php echo htmlspecialchars($review['id']); ?>">
                                        <input type="hidden" name="review_action" value="delete">
                                        <button type="submit" class="delete-btn" title="Delete"><i class="fas fa-trash-alt"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
