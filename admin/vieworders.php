<?php
// admin/vieworders.php

require_once __DIR__ . '/includes/functions.php'; // Includes config and starts session
require_once __DIR__ . '/classes/OrderManager.php';

// Check if admin is logged in
if (!isLoggedIn()) {
    redirectToAdmin('login.php');
}

$orderManager = new OrderManager();
$orders = $orderManager->getAllOrders();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Orders - MSGM Bridal Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Anonymous+Pro:ital,wght@0,400;0,700;1,400;1,700&family=Lora:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>admin/assets/css/styles.css">
    
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'sidepanel.php'; ?>

        <div class="main-content-area">
            <?php include 'navbar.php'; ?>

            <h1 class="page-header">View Orders</h1>

            <?php if (empty($orders)): ?>
                <p>No orders found.</p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer Name</th>
                            <th>Email</th>
                            <th>Total Amount</th>
                            <th>Order Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                       <?php foreach ($orders as $order): ?>
    <tr>
        <td data-label="Order ID"><?php echo htmlspecialchars($order['id']); ?></td>
        <td data-label="Customer Name"><?php echo htmlspecialchars($order['customer_name']); ?></td>
        <td data-label="Email"><?php echo htmlspecialchars($order['customer_email']); ?></td>
        <td data-label="Total Amount">Rs.<?php echo htmlspecialchars(number_format($order['total_amount'], 2)); ?></td>
        <td data-label="Order Date"><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($order['order_date']))); ?></td>
        <td data-label="Status">
            <form method="post" action="update_order_status.php">
                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                <select name="new_status" onchange="this.form.submit()" class="form-control">
                    <?php
                    $statuses = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
                    foreach ($statuses as $status) {
                        $selected = ($order['order_status'] === $status) ? 'selected' : '';
                        echo "<option value=\"$status\" $selected>$status</option>";
                    }
                    ?>
                </select>
            </form>
        </td>
        <td data-label="Actions" class="action-links">
            <a href="view_order_details.php?id=<?php echo $order['id']; ?>" title="View Details"><i class="fas fa-eye"></i></a>
            <a href="delete_order.php?id=<?php echo $order['id']; ?>" title="Delete" onclick="return confirm('Are you sure you want to delete this order?');"><i class="fas fa-trash-alt text-danger"></i></a>
        </td>
    </tr>
<?php endforeach; ?>

                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
