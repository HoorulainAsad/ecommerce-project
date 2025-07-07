<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/classes/CartManager.php';

$cartManager = new CartManager();
$cartItems = $cartManager->getCheckedCartItems();
$cartTotal = $cartManager->getCheckedCartTotal();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['customer_name'] ?? '';
    $email = $_POST['customer_email'] ?? '';
    $address = $_POST['shipping_address'] ?? '';
    $city = $_POST['city'] ?? '';
    $postal = $_POST['postal_code'] ?? '';

    if (empty($name) || empty($email)) {
        displayMessage("Please enter all required fields.", "error");
        redirectTo('checkout.php');
        exit;
    }

}

if (empty($cartItems)) {
    displayMessage("No items selected. Please go back to your cart.", "error");
    redirectTo('cart.php');
    exit;
}
?>

<div class="checkout-page-main">
    <div class="checkout-container">
        <h2>Checkout</h2>

        <form method="POST" action="process_order.php" class="checkout-form">
            <div class="form-group">
                <label for="customer_name">Full Name *</label>
                <input type="text" id="customer_name" name="customer_name" required>
            </div>

            <div class="form-group">
                <label for="customer_email">Email *</label>
                <input type="email" id="customer_email" name="customer_email" required>
            </div>

            <div class="form-group">
                <label for="shipping_address">Shipping Address *</label>
                <input type="text" id="shipping_address" name="shipping_address" required>
            </div>

            <div class="form-group">
                <label for="city">City *</label>
                <input type="text" id="city" name="city" required>
            </div>

            <div class="form-group">
                <label for="postal_code">Postal Code *</label>
                <input type="text" id="postal_code" name="postal_code" required>
            </div>

            <div class="form-group">
                <label for="payment_method">Payment Method *</label>
                <select name="payment_method" id="payment_method" required>
                    <option value="cod" selected>Cash on Delivery</option>
                    <option value="card" disabled>Card Payment (Coming Soon)</option>
                </select>
            </div>

            <button type="submit" class="place-order-btn">Proceed to Checkout</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
