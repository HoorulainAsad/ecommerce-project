<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/classes/CartManager.php';

$cartManager = new CartManager();
$cartItems = $cartManager->getCartItems();
$initialCheckedGrandTotal = $cartManager->getCheckedCartTotal(); // Get initial total for checked items
?>

<div class="container py-5">
    <h2>Your Cart</h2>
    <?php if (empty($cartItems)): ?>
        <p>Your cart is empty.</p>
    <?php else: ?>
        <table class="table table-bordered cart-table"> <thead>
                <tr>
                    <th></th> <th>Image</th>
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
                    <tr data-cart-id="<?php echo $item['id']; ?>" data-price="<?php echo $item['price']; ?>">
                        <td>
                            <input type="checkbox" class="cart-item-checkbox"
                                   data-cart-id="<?php echo $item['id']; ?>"
                                   <?php echo $item['is_checked'] ? 'checked' : ''; ?>>
                        </td>
                        <td><img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="Product" width="60" height="60" onerror="this.src='https://placehold.co/60x60';"></td>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td><?php echo htmlspecialchars($item['size']); ?></td>
                        <td>
                            <form method="POST" action="update_cart.php" style="display:inline-block;" class="update-quantity-form">
                                <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                <input type="hidden" name="action" value="decrease">
                                <button type="submit" class="btn btn-sm btn-outline-secondary">-</button>
                            </form>

                            <span class="mx-2 item-quantity"><?php echo $item['quantity']; ?></span>

                            <form method="POST" action="update_cart.php" style="display:inline-block;" class="update-quantity-form">
                                <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                <input type="hidden" name="action" value="increase">
                                <button type="submit" class="btn btn-sm btn-outline-secondary">+</button>
                            </form>
                        </td>
                        <td>$<span class="item-price"><?php echo number_format($item['price'], 2); ?></span></td>
                        <td>$<span class="item-total"><?php echo number_format($item['price'] * $item['quantity'], 2); ?></span></td>
                        <td>
                            <form method="POST" action="update_cart.php" class="delete-item-form">
                                <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Remove this item?');">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="text-end fw-bold">Grand Total (Selected Items):</td>
                    <td colspan="2" class="fw-bold">$<span id="grand-total"><?php echo number_format((float)$initialCheckedGrandTotal, 2, '.', ''); ?></span>
</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        <div class="d-flex justify-content-end mt-4">
            <form action="checkout.php" method="POST">
    <input type="hidden" name="proceed_checkout" value="1">
    <button type="submit" id="checkout-button" class="btn btn-primary-custom btn-lg">
        Proceed to Checkout
    </button>
</form>


        </div>

    <?php endif; ?>
</div>



<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

<script>
$(document).ready(function() {
    function updateGrandTotalDisplay(newTotal) {
        $('#grand-total').text(parseFloat(newTotal).toFixed(2));
        if (parseFloat(newTotal) > 0) {
            $('#checkout-button').removeClass('disabled');
        } else {
            $('#checkout-button').addClass('disabled');
        }
    }

    // Handle checkbox change
    $('.cart-item-checkbox').on('change', function() {
        const cartId = $(this).data('cart-id');
        const isChecked = $(this).is(':checked');

        $.ajax({
            url: 'update_cart.php', // Your existing update_cart.php
            method: 'POST',
            data: {
                action: 'update_checked_status',
                cart_id: cartId,
                is_checked: isChecked
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    updateGrandTotalDisplay(response.grand_total);
                } else {
                    alert('Failed to update item selection.');
                    // Revert checkbox state if update failed on server
                    $(this).prop('checked', !isChecked);
                }
            },
            error: function() {
                alert('Error communicating with the server.');
                // Revert checkbox state on network error
                $(this).prop('checked', !isChecked);
            }
        });
    });

    // Enhance quantity update forms to use AJAX
    $('.update-quantity-form').on('submit', function(e) {
        e.preventDefault(); // Prevent default form submission

        const form = $(this);
        const cartId = form.find('input[name="cart_id"]').val();
        const action = form.find('input[name="action"]').val();
        const currentRow = form.closest('tr');
        const currentQuantitySpan = currentRow.find('.item-quantity');
        const currentItemTotalSpan = currentRow.find('.item-total');
        const itemPrice = parseFloat(currentRow.data('price'));
        let currentQuantity = parseInt(currentQuantitySpan.text());

        $.ajax({
            url: 'update_cart.php',
            method: 'POST',
            data: form.serialize(), // Serialize form data
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Update quantity display
                    if (action === 'increase') {
                        currentQuantity++;
                    } else if (action === 'decrease' && currentQuantity > 1) {
                        currentQuantity--;
                    }
                    currentQuantitySpan.text(currentQuantity);

                    // Update individual item total display
                    currentItemTotalSpan.text((itemPrice * currentQuantity).toFixed(2));

                    // Update grand total display (from server response)
                    updateGrandTotalDisplay(response.grand_total);

                    // If quantity becomes 0 (after decrease to 1 then another decrease)
                    // You might want to remove the row from the DOM
                    if (currentQuantity === 0 && action === 'decrease') {
                        currentRow.remove();
                        // Re-check if cart is empty after removal (optional, but good UX)
                        if ($('.cart-item-checkbox').length === 0) {
                            $('.container.py-5').html('<p>Your cart is empty.</p>');
                        }
                    }
                } else {
                    alert('Failed to update quantity.');
                }
            },
            error: function() {
                alert('Error communicating with the server for quantity update.');
            }
        });
    });

    // Enhance delete item form to use AJAX
    $('.delete-item-form').on('submit', function(e) {
        e.preventDefault(); // Prevent default form submission

        if (!confirm('Remove this item?')) {
            return; // User cancelled
        }

        const form = $(this);
        const currentRow = form.closest('tr');

        $.ajax({
            url: 'update_cart.php',
            method: 'POST',
            data: form.serialize(), // Serialize form data (cart_id, action=delete)
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    currentRow.remove(); // Remove the row from the table

                    // Update grand total display (from server response)
                    updateGrandTotalDisplay(response.grand_total);

                    // Check if cart is now empty
                    if ($('.cart-item-checkbox').length === 0) {
                        $('.container.py-5').html('<p>Your cart is empty.</p>');
                    }
                } else {
                    alert('Failed to delete item.');
                }
            },
            error: function() {
                alert('Error communicating with the server for item deletion.');
            }
        });
    });

    // Initial check for checkout button state
    updateGrandTotalDisplay(parseFloat($('#grand-total').text()));
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>