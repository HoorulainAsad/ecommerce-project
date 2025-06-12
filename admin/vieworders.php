<?php
// admin/vieworders.php

require_once __DIR__ . '/includes/functions.php'; // Includes config and starts session
require_once __DIR__ . '/classes/OrderManager.php';

// Check if admin is logged in
if (!isLoggedIn()) {
    redirectTo('login.php');
}

$orderManager = new OrderManager();
$orders = $orderManager->getAllOrders();

// No explicit unset($orderManager) needed here, destructor will handle it.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Orders - MSGM Bridal Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Anonymous+Pro:ital,wght@0,400;0,700;1,400;1,700&family=Lora:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/styles.css">
    
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
                                <td data-label="Total Amount">$<?php echo htmlspecialchars(number_format($order['total_amount'], 2)); ?></td>
                                <td data-label="Order Date"><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($order['order_date']))); ?></td>
                                <td data-label="Status">
                                    <span class="status-<?php echo strtolower(htmlspecialchars($order['status'])); ?>">
                                        <?php echo htmlspecialchars($order['status']); ?>
                                    </span>
                                </td>
                                <td data-label="Actions" class="action-links">
                                    <a href="view_order_details.php?id=<?php echo $order['id']; ?>" title="View Details"><i class="fas fa-eye"></i></a>
                                    <!-- Add more actions like edit status, delete -->
                                    <a href="#" onclick="alert('This feature is not yet implemented.'); return false;" title="Edit Status"><i class="fas fa-edit"></i></a>
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
