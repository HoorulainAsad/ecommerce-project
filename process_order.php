<?php
// product_detail.php (Product Detail Page)

require_once __DIR__ . '/includes/header.php'; // Includes functions.php and starts session
require_once __DIR__ . '/classes/ProductFrontendManager.php';
require_once __DIR__ . '/classes/CartManager.php'; // Make sure CartManager is included

$productManager = new ProductFrontendManager();
$cartManager = new CartManager(); // Instantiate CartManager here

$productId = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
$product = null;
$message = '';
$message_type = '';

if ($productId > 0) {
    $product = $productManager->getProductById($productId);
    if (!$product) {
        $message = "Product not found.";
        $message_type = 'error';
    }
} else {
    $message = "No product ID provided.";
    $message_type = 'error';
}

// Handle Add to Cart submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if ($product) {
        $selectedSize = sanitizeInput($_POST['size'] ?? 'N/A');
        $quantity = 1; // Assuming adding one item at a time from product detail page

        if ($cartManager->addToCart($productId, $quantity, $selectedSize)) {
            $message = htmlspecialchars($product['name']) . " (Size: " . htmlspecialchars($selectedSize) . ") added to cart!";
            $message_type = 'success';
            // Optionally redirect to cart page after adding
            // redirectTo('cart.php');
        } else {
            $message = "Could not add " . htmlspecialchars($product['name']) . " to cart. It might be out of stock, or requested quantity exceeds available stock.";
            $message_type = 'error';
        }
    } else {
        $message = "Invalid product or action.";
        $message_type = 'error';
    }
}

// Update cart item count in navbar (JavaScript)
echo "<script>document.getElementById('cart-item-count').innerText = '" . $cartManager->getCartItemCount() . "';</script>";

?>

<div class="container-fluid container-xl py-5 page-content">
    <?php if ($message): ?>
        <div class="row mb-4">
            <div class="col-12">
                <?php displayMessage($message, $message_type); ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($product): ?>
        <div class="row gx-5">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="product-detail-image-container bg-white rounded-3 shadow-sm p-3">
                    <img src="<?php echo BASE_URL . htmlspecialchars($product['image_url']); ?>" class="img-fluid rounded-3" alt="<?php echo htmlspecialchars($product['name']); ?>" onerror="this.onerror=null;this.src='https://placehold.co/600x700/E0E0E0/555555?text=Product+Image';">
                </div>
            </div>
            <div class="col-lg-6">
                <div class="product-details-content">
                    <h1 class="text-primary-custom mb-3"><?php echo htmlspecialchars($product['name']); ?></h1>
                    <p class="lead text-dark-custom mb-4">$<?php echo htmlspecialchars(number_format($product['price'], 2)); ?></p>

                    <p class="text-muted-gray mb-4"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>

                    <div class="mb-4">
                        <span class="fw-bold me-2 text-dark-custom">Category:</span>
                        <span class="text-muted-gray"><?php echo htmlspecialchars($product['category_name'] ?? 'N/A'); ?></span>
                    </div>

                    <div class="mb-4">
                        <span class="fw-bold me-2 text-dark-custom">Availability:</span>
                        <?php if ($product['stock'] <= 0): // Use actual stock level here ?>
                            <span class="out-of-stock-badge">Out of Stock</span>
                        <?php else: ?>
                            <span class="badge bg-success">In Stock (<?php echo htmlspecialchars($product['stock']); ?> available)</span>
                        <?php endif; ?>
                    </div>

                    <form action="<?php echo BASE_URL; ?>product_detail.php?id=<?php echo htmlspecialchars($product['id']); ?>" method="POST" class="mb-4">
                        <div class="mb-3">
                            <label class="form-label text-dark-custom fw-bold">Select Size:</label><br>
                            <div class="btn-group" role="group" aria-label="Product sizes">
                                <input type="radio" class="btn-check" name="size" id="size-small" value="Small" autocomplete="off" checked>
                                <label class="btn btn-outline-primary-custom" for="size-small">Small</label>

                                <input type="radio" class="btn-check" name="size" id="size-medium" value="Medium" autocomplete="off">
                                <label class="btn btn-outline-primary-custom" for="size-medium">Medium</label>

                                <input type="radio" class="btn-check" name="size" id="size-large" value="Large" autocomplete="off">
                                <label class="btn btn-outline-primary-custom" for="size-large">Large</label>
                            </div>
                        </div>

                        <?php if ($product['stock'] > 0): ?>
                            <button type="submit" name="add_to_cart" class="btn btn-primary-custom btn-lg me-3">
                                <i class="fas fa-cart-plus me-2"></i> Add to Cart
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn btn-secondary btn-lg" disabled>Out of Stock</button>
                        <?php endif; ?>

                        <button type="button" class="btn btn-outline-secondary-custom btn-lg" data-bs-toggle="modal" data-bs-target="#sizeGuideModal">
                            <i class="fas fa-ruler-horizontal me-2"></i> Size Guide
                        </button>
                    </form>
                </div>
            </div>
        </div>
    <?php else: ?>
        <p class="text-center text-muted-gray"><?php echo htmlspecialchars($message); ?></p>
        <div class="text-center mt-4">
            <a href="<?php echo BASE_URL; ?>index.php" class="btn btn-primary-custom">Back to Home</a>
        </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="sizeGuideModal" tabindex="-1" aria-labelledby="sizeGuideModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="sizeGuideModalLabel">Size Guide</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <p>Please refer to the chart below for accurate sizing:</p>
                <img src="<?php echo BASE_URL; ?>assets/img/size_chart.png" class="img-fluid rounded-3" alt="Size Chart" onerror="this.onerror=null;this.src='https://placehold.co/800x600/E0E0E0/555555?text=Size+Chart+Placeholder';">
                <small class="text-muted mt-3 d-block">Measurements are in inches, unless otherwise specified. Please allow for slight variations.</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary-custom" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>