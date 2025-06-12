<?php
// order_confirmation.php (Order Confirmation Page)

require_once __DIR__ . '/includes/header.php'; // Includes functions.php and starts session
require_once __DIR__ . '/classes/OrderFrontendManager.php';

$orderManager = new OrderFrontendManager();

$orderId = filter_var($_GET['order_id'] ?? 0, FILTER_VALIDATE_INT);
$message = sanitizeInput($_GET['msg'] ?? ''); // Message passed from process_order.php
$message_type = sanitizeInput($_GET['type'] ?? ''); // Message type

$orderDetails = null;
if ($orderId > 0) {
    $orderDetails = $orderManager->getOrderDetails($orderId);
}

// Display an error if no order ID or order not found
if (!$orderDetails):
    $message = "Order details could not be retrieved. " . ($orderId ? "Order ID: #" . htmlspecialchars($orderId) . "." : "No Order ID provided.");
    $message_type = 'error';
endif;
?>

<div class="container-fluid container-xl py-5 page-content">
    <h1 class="text-center mb-5 text-primary-custom">Order Confirmation</h1>

    <?php if ($message): ?>
        <div class="row mb-4 justify-content-center">
            <div class="col-lg-8">
                <?php displayMessage($message, $message_type); ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($orderDetails): ?>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm p-4" style="border-radius: 15px;">
                    <h3 class="card-title text-primary-custom mb-4">Your Order Details (Order #<?php echo htmlspecialchars($orderDetails['id']); ?>)</h3>

                    <div class="mb-3">
                        <p class="mb-1"><strong>Order Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($orderDetails['created_at'])); ?></p>
                        <p class="mb-1"><strong>Order Status:</strong> <span class="badge bg-success"><?php echo htmlspecialchars($orderDetails['order_status']); ?></span></p>
                        <p class="mb-1"><strong>Total Amount:</strong> <span class="fw-bold fs-5 text-primary-custom">$<?php echo htmlspecialchars(number_format($orderDetails['order_total'], 2)); ?></span></p>
                        <p class="mb-1"><strong>Payment Method:</strong> <?php echo htmlspecialchars($orderDetails['payment_method']); ?></p>
                    </div>

                    <h4 class="text-dark-custom mb-3 mt-4">Shipping Information:</h4>
                    <address class="mb-4 text-muted-gray">
                        <strong><?php echo htmlspecialchars($orderDetails['customer_name']); ?></strong><br>
                        <?php echo htmlspecialchars($orderDetails['shipping_address']); ?><br>
                        <?php echo htmlspecialchars($orderDetails['city']); ?>, <?php echo htmlspecialchars($orderDetails['postal_code']); ?><br>
                        Phone: <?php echo htmlspecialchars($orderDetails['customer_phone']); ?><br>
                        Email: <?php echo htmlspecialchars($orderDetails['customer_email']); ?>
                    </address>

                    <h4 class="text-dark-custom mb-3">Items Ordered:</h4>
                    <ul class="list-group list-group-flush mb-4">
                        <?php foreach ($orderDetails['items'] as $item): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="fw-bold text-dark-custom"><?php echo htmlspecialchars($item['product_name']); ?></span>
                                    <small class="text-muted d-block">Size: <?php echo htmlspecialchars($item['size']); ?></small>
                                </div>
                                <div>
                                    <span class="text-primary-custom"><?php echo htmlspecialchars($item['quantity']); ?> x $<?php echo htmlspecialchars(number_format($item['price'], 2)); ?></span>
                                    <span class="ms-3 fw-bold">$<?php echo htmlspecialchars(number_format($item['quantity'] * $item['price'], 2)); ?></span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <div class="text-center mt-4">
                        <a href="<?php echo BASE_URL; ?>index.php" class="btn btn-primary-custom btn-lg" style="border-radius: 10px;">Continue Shopping</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
