<?php
// review_submission.php (in MSGM_CLOTHING root)

// Ensure common functions and configuration are loaded
require_once __DIR__ . '/admin/includes/database.php';
require_once __DIR__ . '/admin/classes/ReviewManager.php';
require_once __DIR__ . '/classes/ProductFrontendManager.php'; // To get product details
require_once __DIR__ . '/admin/includes/config.php'; // For WEB_ROOT_URL
require_once __DIR__ . '/includes/functions.php'; // For sanitizeInput, displayMessage (if you have it)

// Initialize messages
$message = '';
$message_type = ''; // 'success' or 'error'

// Retrieve and sanitize order and product IDs from URL
$orderId = isset($_GET['order_id']) ? filter_var($_GET['order_id'], FILTER_VALIDATE_INT) : null;
$productId = isset($_GET['product_id']) ? filter_var($_GET['product_id'], FILTER_VALIDATE_INT) : null;

// Determine if the request is valid for showing the form
$validRequestForForm = ($orderId !== false && $orderId !== null && $productId !== false && $productId !== null);

// --- Handle Form Submission (POST Request) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validRequestForForm) {
    // Re-validate IDs from POST as hidden fields could be manipulated
    $postOrderId = filter_var($_POST['order_id'] ?? null, FILTER_VALIDATE_INT);
    $postProductId = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT);

    // Ensure IDs from GET and POST match and are valid
    if ($postOrderId === $orderId && $postProductId === $productId) {
        $customerName = htmlspecialchars(trim($_POST['customer_name'] ?? ''));
        $customerEmail = htmlspecialchars(trim($_POST['customer_email'] ?? ''));
        $rating = filter_var($_POST['rating'] ?? 0, FILTER_VALIDATE_INT);
        $comment = htmlspecialchars(trim($_POST['comment'] ?? ''));

        if (empty($customerName) || $rating < 1 || $rating > 5 || empty($comment)) {
            $message = "Please fill in all required fields (Name, Rating, Comment).";
            $message_type = 'error';
        } else {
            $reviewManager = new ReviewManager();
            // Assuming addReview needs customer_id, it's null here. If you have user sessions, pass actual ID.
            if ($reviewManager->addReview($orderId, $customerName, $rating, $comment, $customerEmail, null, $productId)) {
                $message = "Thank you for your review! It has been submitted for approval.";
                $message_type = 'success';
                // Optionally clear the form fields by redirecting or resetting variables
                // header("Location: review_submission.php?order_id=$orderId&product_id=$productId&msg=" . urlencode($message) . "&type=success");
                // exit();
            } else {
                $message = "Error submitting your review. Please try again later.";
                $message_type = 'error';
            }
        }
    } else {
        $message = "Security error: Mismatched order/product IDs.";
        $message_type = 'error';
        $validRequestForForm = false; // Invalidate the request to prevent form display
    }
}

// --- Fetch Product Name for Display (if valid GET request) ---
$productName = '';
if ($validRequestForForm) { // Only try to fetch product name if IDs are valid
    $productManager = new ProductFrontendManager();
    $product = $productManager->getProductById($productId);
    if ($product) {
        $productName = htmlspecialchars($product['name']);
    } else {
        $message = "Product not found for the given ID. Invalid review link.";
        $message_type = 'error';
        $validRequestForForm = false; // Invalidate the request if product not found
    }
} else {
    // This message is for the initial load if IDs are missing/invalid
    if (empty($message)) { // Don't override message from POST errors
        $message = "Invalid review link. Missing or invalid order ID or product ID.";
        $message_type = 'error';
    }
}

// --- Display HTML ---
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Your Review - MSGM Bridal</title>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { font-family: 'Lora', serif; background-color: #f8f4f2; color: #333; line-height: 1.6; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 40px auto; background-color: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h1 { text-align: center; color: #7f0e10; margin-bottom: 30px; }
        .message { padding: 10px; margin-bottom: 20px; border-radius: 5px; text-align: center; }
        .message.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: bold; color: #555; }
        input[type="text"], input[type="email"], textarea, select {
            width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; font-size: 16px;
        }
        textarea { resize: vertical; min-height: 100px; }
        .star-rating-input { display: flex; justify-content: center; gap: 5px; }
        .star-rating-input input[type="radio"] { display: none; }
        .star-rating-input label {
            cursor: pointer; font-size: 30px; color: #ccc; transition: color 0.2s;
        }
        .star-rating-input label:hover,
        .star-rating-input label:hover ~ label,
        .star-rating-input input[type="radio"]:checked ~ label {
            color: #FFD700;
        }
        .star-rating-input label:hover { transform: scale(1.1); }
        button {
            display: block; width: 100%; padding: 15px; background-color: #7f0e10; color: #fff;
            border: none; border-radius: 5px; font-size: 18px; cursor: pointer; transition: background-color 0.3s ease;
        }
        button:hover { background-color: #5d0b0d; }
    </style>
</head>
<body>
<div class="container">
    <h1>Submit Your Review for Order #<?php echo htmlspecialchars($orderId ?? 'N/A'); ?></h1>

    <?php if ($productName): ?>
        <p style="text-align:center; font-size:18px;">Product: <strong><?php echo $productName; ?></strong></p>
    <?php endif; ?>

    <?php if ($message): ?>
        <div class="message <?php echo $message_type; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <?php if ($validRequestForForm): ?>
        <form action="review_submission.php?order_id=<?php echo htmlspecialchars($orderId); ?>&product_id=<?php echo htmlspecialchars($productId); ?>" method="POST">
            <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($orderId); ?>">
            <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($productId); ?>">

            <div class="form-group">
                <label for="customer_name">Your Name:</label>
                <input type="text" id="customer_name" name="customer_name" required>
            </div>
            <div class="form-group">
                <label for="customer_email">Your Email (Optional):</label>
                <input type="email" id="customer_email" name="customer_email">
            </div>
            <div class="form-group">
                <label>Your Rating:</label>
                <div class="star-rating-input">
                    <input type="radio" id="star5" name="rating" value="5" required><label for="star5" title="5 stars"><i class="fas fa-star"></i></label>
                    <input type="radio" id="star4" name="rating" value="4"><label for="star4" title="4 stars"><i class="fas fa-star"></i></label>
                    <input type="radio" id="star3" name="rating" value="3"><label for="star3" title="3 stars"><i class="fas fa-star"></i></label>
                    <input type="radio" id="star2" name="rating" value="2"><label for="star2" title="2 stars"><i class="fas fa-star"></i></label>
                    <input type="radio" id="star1" name="rating" value="1"><label for="star1" title="1 star"><i class="fas fa-star"></i></label>
                </div>
            </div>
            <div class="form-group">
                <label for="comment">Your Comment:</label>
                <textarea id="comment" name="comment" rows="5" required></textarea>
            </div>
            <button type="submit">Submit Review</button>
        </form>
    <?php else: ?>
        <?php endif; ?>
</div>
</body>
</html>