<?php
// checkout.php (Checkout Page - Collects User Info & Payment Method)

require_once __DIR__ . '/includes/header.php'; // Includes functions.php and starts session
require_once __DIR__ . '/classes/CartManager.php';

$cartManager = new CartManager();
$cartItems = $cartManager->getCartItems();
$cartTotal = $cartManager->getCartTotal();

// Redirect to cart if empty or if any item is out of stock/insufficient quantity
$hasOutOfStockItems = false;
foreach ($cartItems as $item) {
    if ($item['is_out_of_stock']) {
        $hasOutOfStockItems = true;
        break;
    }
}
if (empty($cartItems) || $hasOutOfStockItems) {
    displayMessage("Your cart is empty or contains out-of-stock items. Please adjust your cart before checkout.", "error");
    redirectTo('cart.php');
}

$message = '';
$message_type = '';

// Pre-fill form if user is logged in
$customerName = isUserLoggedIn() ? getLoggedInUsername() : '';
$customerEmail = isUserLoggedIn() ? $_SESSION['email'] : '';
$customerPhone = $_SESSION['checkout_data']['customer_phone'] ?? '';
$shippingAddress = $_SESSION['checkout_data']['shipping_address'] ?? '';
$city = $_SESSION['checkout_data']['city'] ?? '';
$postalCode = $_SESSION['checkout_data']['postal_code'] ?? '';
$paymentMethod = $_SESSION['checkout_data']['payment_method'] ?? 'Cash on Delivery'; // Default COD

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $customerName = sanitizeInput($_POST['customer_name'] ?? '');
    $customerEmail = sanitizeInput($_POST['customer_email'] ?? '');
    $customerPhone = sanitizeInput($_POST['customer_phone'] ?? '');
    $shippingAddress = sanitizeInput($_POST['shipping_address'] ?? '');
    $city = sanitizeInput($_POST['city'] ?? '');
    $postalCode = sanitizeInput($_POST['postal_code'] ?? '');
    $paymentMethod = sanitizeInput($_POST['payment_method'] ?? '');

    if (empty($customerName) || empty($customerEmail) || empty($shippingAddress) || empty($city) || empty($postalCode) || empty($paymentMethod)) {
        $message = "Please fill in all required fields.";
        $message_type = 'error';
    } elseif (!filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
        $message_type = 'error';
    } else {
        // Store form data in session for processing on next step (process_order.php)
        $_SESSION['checkout_data'] = [
            'customer_name' => $customerName,
            'customer_email' => $customerEmail,
            'customer_phone' => $customerPhone,
            'shipping_address' => $shippingAddress,
            'city' => $city,
            'postal_code' => $postalCode,
            'payment_method' => $paymentMethod,
            'order_total' => $cartTotal, // Use the real-time cart total
            'user_id' => isUserLoggedIn() ? $_SESSION['user_id'] : null,
            'cart_items_snapshot' => $cartItems // Store snapshot of cart items at checkout for processing
        ];
        redirectTo('process_order.php'); // Proceed to payment simulation/order finalization
    }
}

?>

<div class="container-fluid container-xl py-5 page-content">
    <h1 class="text-center mb-5 text-primary-custom">Checkout</h1>

    <?php if ($message): ?>
        <div class="row mb-4 justify-content-center">
            <div class="col-lg-8">
                <?php displayMessage($message, $message_type); ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm p-4" style="border-radius: 15px;">
                <h3 class="card-title text-primary-custom mb-4">Shipping Information</h3>
                <form action="<?php echo BASE_URL; ?>checkout.php" method="POST">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="customer_name" class="form-label text-dark-custom">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="customer_name" name="customer_name" value="<?php echo htmlspecialchars($customerName); ?>" required style="border-radius: 8px;">
                        </div>
                        <div class="col-md-6">
                            <label for="customer_email" class="form-label text-dark-custom">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="customer_email" name="customer_email" value="<?php echo htmlspecialchars($customerEmail); ?>" required style="border-radius: 8px;">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="customer_phone" class="form-label text-dark-custom">Phone Number <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" id="customer_phone" name="customer_phone" value="<?php echo htmlspecialchars($customerPhone); ?>" required style="border-radius: 8px;">
                    </div>
                    <div class="mb-3">
                        <label for="shipping_address" class="form-label text-dark-custom">Shipping Address <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="shipping_address" name="shipping_address" value="<?php echo htmlspecialchars($shippingAddress); ?>" required style="border-radius: 8px;">
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="city" class="form-label text-dark-custom">City <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="city" name="city" value="<?php echo htmlspecialchars($city); ?>" required style="border-radius: 8px;">
                        </div>
                        <div class="col-md-6">
                            <label for="postal_code" class="form-label text-dark-custom">Postal Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="postal_code" name="postal_code" value="<?php echo htmlspecialchars($postalCode); ?>" required style="border-radius: 8px;">
                        </div>
                    </div>

                    <h3 class="card-title text-primary-custom mb-4 mt-5">Payment Method</h3>
                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="cashOnDelivery" value="Cash on Delivery" <?php echo ($paymentMethod == 'Cash on Delivery' ? 'checked' : ''); ?> required>
                            <label class="form-check-label text-dark-custom" for="cashOnDelivery">
                                Cash on Delivery (COD)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="creditCard" value="Credit Card" disabled>
                            <label class="form-check-label text-muted-gray" for="creditCard">
                                Credit Card (Coming Soon)
                            </label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-5">
                        <a href="<?php echo BASE_URL; ?>cart.php" class="btn btn-outline-secondary-custom" style="border-radius: 10px;">Back to Cart</a>
                        <button type="submit" class="btn btn-primary-custom btn-lg" style="border-radius: 10px;">Place Order ($<?php echo htmlspecialchars(number_format($cartTotal, 2)); ?>)</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
