<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/classes/CartManager.php';

$cartManager = new CartManager();
$cartItems = $cartManager->getCartItems();
?>

<div class="container py-5">
    <h2>Your Cart</h2>
    <?php if (empty($cartItems)): ?>
        <p>Your cart is empty.</p>
    <?php else: ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Product</th>
                    <th>Size</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cartItems as $item): ?>
                    <tr>
                        <td><img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="Product" width="60" height="60" onerror="this.src='https://placehold.co/60x60';"></td>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td><?php echo htmlspecialchars($item['size']); ?></td>
                        <td>
                            <form method="POST" action="update_cart.php" style="display:inline-block;">
                                <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                <input type="hidden" name="action" value="decrease">
                                <button type="submit" class="btn btn-sm btn-outline-secondary">-</button>
                            </form>

                            <span class="mx-2"><?php echo $item['quantity']; ?></span>

                            <form method="POST" action="update_cart.php" style="display:inline-block;">
                                <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                <input type="hidden" name="action" value="increase">
                                <button type="submit" class="btn btn-sm btn-outline-secondary">+</button>
                            </form>
                        </td>
                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                        <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                        <td>
                            <form method="POST" action="update_cart.php">
                                <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Remove this item?');">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
