<?php
// cart.php (Shopping Cart Page)

require_once __DIR__ . '/includes/header.php'; // Includes functions.php and starts session
require_once __DIR__ . '/classes/CartManager.php';

$cartManager = new CartManager();
$message = '';
$message_type = '';

// Handle cart actions (update quantity, remove item)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_quantity'])) {
        $productId = filter_var($_POST['product_id'] ?? 0, FILTER_VALIDATE_INT);
        $size = sanitizeInput($_POST['size'] ?? 'N/A');
        $newQuantity = filter_var($_POST['quantity'] ?? 0, FILTER_VALIDATE_INT);

        if ($productId > 0 && $newQuantity >= 0) {
            if ($cartManager->updateCartItemQuantity($productId, $newQuantity, $size)) {
                $message = "Cart updated successfully.";
                $message_type = 'success';
            } else {
                $message = "Failed to update cart. Please check stock availability or entered quantity.";
                $message_type = 'error';
            }
        } else {
            $message = "Invalid product ID or quantity.";
            $message_type = 'error';
        }
    } elseif (isset($_POST['remove_item'])) {
        $productId = filter_var($_POST['product_id'] ?? 0, FILTER_VALIDATE_INT);
        $size = sanitizeInput($_POST['size'] ?? 'N/A');

        if ($productId > 0) {
            if ($cartManager->removeFromCart($productId, $size)) {
                $message = "Item removed from cart.";
                $message_type = 'success';
            } else {
                $message = "Failed to remove item from cart.";
                $message_type = 'error';
            }
        } else {
            $message = "Invalid product ID.";
            $message_type = 'error';
        }
    }
    // Re-fetch cart items and total after any action
    $cartItems = $cartManager->getCartItems();
    $cartTotal = $cartManager->getCartTotal();
} else {
    // Initial load, just get cart items
    $cartItems = $cartManager->getCartItems();
    $cartTotal = $cartManager->getCartTotal();
}
?>

<div class="container-fluid container-xl py-5 page-content">
    <h1 class="text-center mb-5 text-primary-custom">Your Shopping Cart</h1>

    <?php if ($message): ?>
        <div class="row mb-4 justify-content-center">
            <div class="col-lg-8">
                <?php displayMessage($message, $message_type); ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (empty($cartItems)): ?>
        <div class="alert alert-info text-center" role="alert">
            Your cart is empty. <a href="<?php echo BASE_URL; ?>index.php" class="alert-link text-decoration-underline">Start shopping now!</a>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4" style="border-radius: 15px;">
                    <div class="card-header bg-primary-custom text-white" style="border-top-left-radius: 15px; border-top-right-radius: 15px;">
                        <h4 class="mb-0">Cart Items (<?php echo $cartManager->getCartItemCount(); ?> unique items)</h4>
                    </div>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($cartItems as $itemKey => $item):
                            $productDisplayPrice = htmlspecialchars(number_format($item['price'], 2));
                            $itemSubtotal = htmlspecialchars(number_format($item['price'] * $item['quantity'], 2));
                        ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap py-3">
                                <div class="d-flex align-items-center flex-grow-1 mb-2 mb-md-0">
                                    <img src="<?php echo BASE_URL . htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="img-thumbnail me-3" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">
                                    <div>
                                        <h5 class="my-0 text-dark-custom"><?php echo htmlspecialchars($item['name']); ?></h5>
                                        <small class="text-muted">Size: <?php echo htmlspecialchars($item['size']); ?></small><br>
                                        <span class="text-primary-custom fw-bold">$<?php echo $productDisplayPrice; ?></span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center mt-2 mt-md-0">
                                    <form action="<?php echo BASE_URL; ?>cart.php" method="POST" class="d-flex align-items-center me-2">
                                        <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($item['product_id']); ?>">
                                        <input type="hidden" name="size" value="<?php echo htmlspecialchars($item['size']); ?>">
                                        <input type="number" name="quantity" value="<?php echo htmlspecialchars($item['quantity']); ?>" min="1" max="<?php echo htmlspecialchars($item['current_stock']); ?>" class="form-control text-center" style="width: 80px; border-radius: 8px;">
                                        <button type="submit" name="update_quantity" class="btn btn-sm btn-outline-primary-custom ms-2">Update</button>
                                    </form>
                                    <form action="<?php echo BASE_URL; ?>cart.php" method="POST">
                                        <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($item['product_id']); ?>">
                                        <input type="hidden" name="size" value="<?php echo htmlspecialchars($item['size']); ?>">
                                        <button type="submit" name="remove_item" class="btn btn-sm btn-outline-danger" style="border-radius: 8px;">
                                            <i class="fas fa-trash-alt me-1"></i> Remove
                                        </button>
                                    </form>
                                </div>
                                <?php if ($item['is_out_of_stock']): ?>
                                    <div class="w-100 text-end mt-2">
                                        <span class="out-of-stock-badge">Out of stock or insufficient quantity!</span>
                                    </div>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card shadow-sm" style="border-radius: 15px;">
                    <div class="card-header bg-primary-custom text-white" style="border-top-left-radius: 15px; border-top-right-radius: 15px;">
                        <h4 class="mb-0">Order Summary</h4>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush mb-3">
                            <li class="list-group-item d-flex justify-content-between align-items-center text-dark-custom">
                                Subtotal: <span class="fw-bold">$<?php echo htmlspecialchars(number_format($cartTotal, 2)); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center text-dark-custom">
                                Shipping: <span>Free</span> <!-- For now, hardcoded as Free -->
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center total-amount text-primary-custom fw-bold">
                                <strong>Total:</strong> <strong class="fs-4">$<?php echo htmlspecialchars(number_format($cartTotal, 2)); ?></strong>
                            </li>
                        </ul>
                        <?php
                        $canProceedToCheckout = true;
                        foreach ($cartItems as $item) {
                            if ($item['is_out_of_stock']) {
                                $canProceedToCheckout = false;
                                break;
                            }
                        }
                        ?>
                        <?php if ($canProceedToCheckout): ?>
                            <a href="<?php echo BASE_URL; ?>checkout.php" class="btn btn-primary-custom btn-lg w-100" style="border-radius: 10px;">Proceed to Checkout</a>
                        <?php else: ?>
                            <button class="btn btn-secondary btn-lg w-100" disabled style="border-radius: 10px;">Cannot Checkout (Items Out of Stock)</button>
                            <small class="text-danger mt-2 d-block text-center">Please adjust quantities or remove out-of-stock items.</small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
