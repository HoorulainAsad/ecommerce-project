<?php
// admin/index.php (Dashboard)

require_once __DIR__ . '/includes/functions.php'; // Includes config and starts session
require_once __DIR__ . '/classes/ProductManager.php'; // Crucial: This line must be correct and file must exist
require_once __DIR__ . '/classes/OrderManager.php';
require_once __DIR__ . '/classes/UserManager.php';
require_once __DIR__ . '/classes/ReviewManager.php';

// Check if admin is logged in, otherwise redirect to login page
if (!isLoggedIn()) {
    redirectTo('login.php');
}

// Instantiate managers to fetch data for dashboard counts
$productManager = new ProductManager();
$orderManager = new OrderManager();
$userManager = new UserManager();
$reviewManager = new ReviewManager();

// Fetch dashboard data
// Ensure these methods exist in your manager classes
$totalProducts = $productManager->getTotalProductCount();
$totalOrders = $orderManager->getTotalOrdersCount();
$totalUsers = $userManager->getTotalUsersCount();
$newArrivalsCount = $productManager->getNewArrivalsCount();
$trendyCollectionCount = $orderManager->getTrendyProductsCount();
$pendingReviews = $reviewManager->getReviewCountByStatus('Pending');

// No explicit unset calls needed here, as the database.php's register_shutdown_function will handle closing.

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - MSGM Bridal</title>

    <link href="https://fonts.googleapis.com/css2?family=Anonymous+Pro:ital,wght@0,400;0,700;1,400;1,700&family=Lora:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Link to your external stylesheet -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/styles.css?v=2">

</head>
<body>
    <div class="admin-wrapper">
        <?php include 'sidepanel.php'; ?>

        <div class="main-content-area">
            <?php include 'navbar.php'; ?>

            <h1 class="page-header">Dashboard</h1>

            <div class="dashboard-stats">
                <a href="<?php echo BASE_URL; ?>viewproducts.php" class="stat-card-link">
                    <i class="fas fa-tshirt icon"></i>
                    <div class="value"><?php echo $totalProducts; ?></div>
                    <div class="label">Total Products</div>
                </a>
                <a href="<?php echo BASE_URL; ?>vieworders.php" class="stat-card-link">
                    <i class="fas fa-box-open icon"></i>
                    <div class="value"><?php echo $totalOrders; ?></div>
                    <div class="label">Total Orders</div>
                </a>
                <a href="<?php echo BASE_URL; ?>userdetails.php" class="stat-card-link">
                    <i class="fas fa-users icon"></i>
                    <div class="value"><?php echo $totalUsers; ?></div>
                    <div class="label">Total Users</div>
                </a>
                <a href="<?php echo BASE_URL; ?>viewproducts.php?filter=new_arrivals" class="stat-card-link">
                    <i class="fas fa-star icon"></i>
                    <div class="value"><?php echo $newArrivalsCount; ?></div>
                    <div class="label">New Arrivals (Last 30 Days)</div>
                </a>
                <a href="<?php echo BASE_URL; ?>viewproducts.php?filter=trendy" class="stat-card-link">
                    <i class="fas fa-fire icon"></i>
                    <div class="value"><?php echo $trendyCollectionCount; ?></div>
                    <div class="label">Trendy Products (Ordered)</div>
                </a>
                <a href="<?php echo BASE_URL; ?>customerfeedback.php?filter=pending" class="stat-card-link">
                    <i class="fas fa-comments icon"></i>
                    <div class="value"><?php echo $pendingReviews; ?></div>
                    <div class="label">Pending Reviews</div>
                </a>
            </div>

            <div class="dashboard-actions">
                <a href="<?php echo BASE_URL; ?>addproduct.php" class="action-button primary">
                    <i class="fas fa-plus-circle"></i> Add New Product
                </a>
                <a href="<?php echo BASE_URL; ?>viewproducts.php" class="action-button primary">
                    <i class="fas fa-tshirt"></i> View Products
                </a>
                <a href="<?php echo BASE_URL; ?>managecategory.php" class="action-button secondary">
                    <i class="fas fa-list-alt"></i> Manage Categories
                </a>
                <a href="<?php echo BASE_URL; ?>vieworders.php" class="action-button link-view-orders">
                    <i class="fas fa-shopping-cart"></i> View Orders
                </a>
                <a href="<?php echo BASE_URL; ?>userdetails.php" class="action-button primary">
                    <i class="fas fa-users"></i> View Users
                </a>
                <a href="<?php echo BASE_URL; ?>customerfeedback.php" class="action-button secondary">
                    <i class="fas fa-comments"></i> Manage Reviews
                </a>
            </div>
        </div>
    </div>
</body>
</html>
