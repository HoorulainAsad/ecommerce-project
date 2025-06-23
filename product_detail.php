<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/classes/ProductFrontendManager.php';
require_once __DIR__ . '/classes/CartManager.php';

$productManager = new ProductFrontendManager();
$cartManager = new CartManager();

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if ($product && !$product['is_out_of_stock']) {
        $selectedSize = sanitizeInput($_POST['size'] ?? 'N/A');
        $added = $cartManager->addToCart($productId, 1, $selectedSize);
        if ($added) {
            $message = htmlspecialchars($product['name']) . " (Size: " . $selectedSize . ") added to cart!";
            $message_type = 'success';
        } else {
            $message = "Failed to add product to cart.";
            $message_type = 'error';
        }
    } else {
        $message = "Product is not available.";
        $message_type = 'error';
    }
}
?>

<!-- 🔽 Main Content Wrapper Start -->
<main class="main-content">
    <div class="container-xl my-5">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'danger'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($product): ?>
            <div class="product-details-container">
                <!-- Product Image -->
                <div class="main-image-wrapper" id="mainImageWrapper">
                    <img id="mainImage" src="<?php echo BASE_URL . htmlspecialchars($product['image_url']); ?>"
                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                         onerror="this.onerror=null;this.src='https://placehold.co/600x700/E0E0E0/555555?text=Product+Image';">
                </div>

                <!-- Product Info -->
                <div class="product-info-details">
                    <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                    <p class="description"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                    <p class="price">Rs. <?php echo number_format($product['price'], 0); ?></p>

                    <p><strong>Category:</strong> <?php echo htmlspecialchars($product['category_name'] ?? 'N/A'); ?></p>
                    <p><strong>Availability:</strong>
                        <?php if ($product['is_out_of_stock']): ?>
                            <span class="badge bg-danger">Out of Stock</span>
                        <?php else: ?>
                            <span class="badge bg-success">In Stock (<?php echo htmlspecialchars($product['stock']); ?>)</span>
                        <?php endif; ?>
                    </p>

                    <form method="POST">
                        <!-- Size Options -->
                        <div class="size-options mb-3">
                            <button type="submit" name="size" value="Small"
                                    class="size-btn <?php if ($_POST['size'] ?? '' === 'Small') echo 'active'; ?>">Small</button>
                            <button type="submit" name="size" value="Medium"
                                    class="size-btn <?php if ($_POST['size'] ?? '' === 'Medium') echo 'active'; ?>">Medium</button>
                            <button type="submit" name="size" value="Large"
                                    class="size-btn <?php if ($_POST['size'] ?? '' === 'Large') echo 'active'; ?>">Large</button>
                        </div>

                        <!-- Add to Cart -->
                        <?php if (!$product['is_out_of_stock']): ?>
                            <button type="submit" name="add_to_cart" class="btn-add-to-cart mb-3">
                                <i class="fas fa-cart-plus me-2"></i> ADD TO CART
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn btn-secondary" disabled>Out of Stock</button>
                        <?php endif; ?>

                        <!-- Size Guide -->
                        <br>
                        <button type="button" class="size-guide-btn" data-bs-toggle="modal" data-bs-target="#sizeGuideModal">
                            SIZE GUIDE
                        </button>
                    </form>
                </div>
            </div> <!-- .product-details-container -->

        <?php else: ?>
            <div class="text-center">
                <p class="text-muted"><?php echo htmlspecialchars($message); ?></p>
                <a href="<?php echo BASE_URL; ?>index.php" class="btn btn-primary">Back to Home</a>
            </div>
        <?php endif; ?>
    </div>
</main> <!-- 🔼 Main Content Wrapper End -->

<!-- Size Guide Modal -->
<div class="modal fade" id="sizeGuideModal" tabindex="-1" aria-labelledby="sizeGuideModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="sizeGuideModalLabel">Size Guide</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <p>Please refer to the chart below for accurate sizing:</p>
                <img src="<?php echo BASE_URL; ?>assets/img/size_chart.png" class="img-fluid rounded-3" alt="Size Chart"
                     onerror="this.onerror=null;this.src='https://placehold.co/800x600/E0E0E0/555555?text=Size+Chart+Placeholder';">
                <small class="text-muted mt-3 d-block">Measurements are in inches. Slight variations may occur.</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Image Zoom Script -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    const wrapper = document.getElementById('mainImageWrapper');
    const image = document.getElementById('mainImage');
    let zoomedIn = false;

    wrapper.addEventListener('click', function (e) {
        zoomedIn = !zoomedIn;
        if (zoomedIn) {
            const rect = wrapper.getBoundingClientRect();
            const x = ((e.clientX - rect.left) / rect.width) * 100;
            const y = ((e.clientY - rect.top) / rect.height) * 100;
            image.style.transform = 'scale(2)';
            image.style.transformOrigin = `${x}% ${y}%`;
            wrapper.style.cursor = 'zoom-out';
        } else {
            image.style.transform = 'scale(1)';
            wrapper.style.cursor = 'zoom-in';
        }
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
