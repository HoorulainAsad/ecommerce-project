<?php
require_once __DIR__ . '/includes/header.php'; // header and session
require_once __DIR__ . '/includes/functions.php'; // For displayMessage()

// Get message from URL
$message = sanitizeInput($_GET['msg'] ?? 'Order placed successfully! Thank you for ordering. You will receive a confirmation email soon.');
$message_type = sanitizeInput($_GET['type'] ?? 'success');
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 text-center">
            <div class="card p-5 shadow-sm" style="border-radius: 20px;">
                <h1 class="text-success mb-4">Thank You!</h1>
                <?php displayMessage($message, $message_type); ?>
                <p class="lead">Your order has been placed successfully.</p>
                <p>You will receive a confirmation email shortly.</p>
                <a href="<?php echo BASE_URL; ?>index.php" class="btn btn-primary-custom mt-4">Continue Shopping</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
