<?php
// admin/index.php (Dashboard)

require_once __DIR__ . '/includes/functions.php'; // Includes config and starts session
require_once __DIR__ . '/classes/ProductManager.php'; // Crucial: This line must be correct and file must exist
require_once __DIR__ . '/classes/OrderManager.php';
require_once __DIR__ . '/classes/UserManager.php';
require_once __DIR__ . '/classes/ReviewManager.php';

if (!isLoggedIn()) {
    redirectToAdmin('login.php');
}

$adminUsername = $_SESSION[ADMIN_USERNAME_SESSION_KEY] ?? 'Admin';
$adminRole = $_SESSION[ADMIN_ROLE_SESSION_KEY] ?? 'admin';

$productManager = new ProductManager();
$orderManager = new OrderManager();
$userManager = new UserManager();
$reviewManager = new ReviewManager();

$totalProducts = $productManager->getTotalProductCount();
$totalOrders = $orderManager->getTotalOrdersCount();
$totalUsers = $userManager->getTotalUsersCount();
$newArrivalsCount = $productManager->getNewArrivalsCount();
$trendyCollectionCount = $orderManager->getTrendyProductsCount();
$pendingReviews = $reviewManager->getReviewCountByStatus('Pending');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - MSGM Bridal</title>

    <link href="https://fonts.googleapis.com/css2?family=Anonymous+Pro:ital,wght@0,400;0,700;1,400;1,700&family=Lora:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>admin/assets/css/styles.css?v=2">

    <style>
        .admin-info {
            background-color: #e9ecef;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            text-align: left;
            border-left: 5px solid #7f0e10;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .admin-info p {
            margin: 0;
            font-size: 1.1em;
            color: #343a40;
        }
        .admin-info strong {
            color: #7f0e10;
        }
        .admin-info .role-badge {
            background-color: #7f0e10;
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.85em;
            font-weight: bold;
            text-transform: capitalize;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'sidepanel.php'; ?>

        <div class="main-content-area">
            <?php include 'navbar.php'; ?>

            <h1 class="page-header">Dashboard</h1>

            <div class="admin-info">
                <p>Logged in as: <strong><?php echo htmlspecialchars($adminUsername); ?></strong></p>
                <span class="role-badge"><?php echo htmlspecialchars(ucfirst($adminRole)); ?></span>
            </div>

            <div class="dashboard-stats">
                <a href="<?php echo BASE_URL; ?>admin/viewproducts.php" class="stat-card-link">
                    <i class="fas fa-tshirt icon"></i>
                    <div class="value"><?php echo $totalProducts; ?></div>
                    <div class="label">Total Products</div>
                </a>
                <a href="<?php echo BASE_URL; ?>admin/vieworders.php" class="stat-card-link">
                    <i class="fas fa-box-open icon"></i>
                    <div class="value"><?php echo $totalOrders; ?></div>
                    <div class="label">Total Orders</div>
                </a>
                <a href="<?php echo BASE_URL; ?>admin/userdetails.php" class="stat-card-link">
                    <i class="fas fa-users icon"></i>
                    <div class="value"><?php echo $totalUsers; ?></div>
                    <div class="label">Total Users</div>
                </a>
                <a href="<?php echo BASE_URL; ?>admin/viewproducts.php?filter=new_arrivals" class="stat-card-link">
                    <i class="fas fa-star icon"></i>
                    <div class="value"><?php echo $newArrivalsCount; ?></div>
                    <div class="label">New Arrivals (Last 30 Days)</div>
                </a>
                <a href="<?php echo BASE_URL; ?>admin/viewproducts.php?filter=trendy" class="stat-card-link">
                    <i class="fas fa-fire icon"></i>
                    <div class="value"><?php echo $trendyCollectionCount; ?></div>
                    <div class="label">Trendy Products (Ordered)</div>
                </a>
                <a href="<?php echo BASE_URL; ?>admin/customerfeedback.php?filter=pending" class="stat-card-link">
                    <i class="fas fa-comments icon"></i>
                    <div class="value"><?php echo $pendingReviews; ?></div>
                    <div class="label">Reviews</div>
                </a>
            </div>

            <div class="dashboard-actions">
                <?php if (isSuperAdmin()): ?>
                    <a href="<?php echo BASE_URL; ?>admin/add_admin.php" class="action-button primary">
                        <i class="fas fa-user-plus"></i> Add New Admin
                    </a>
                <?php endif; ?>
                <a href="<?php echo BASE_URL; ?>admin/addproduct.php" class="action-button primary">
                    <i class="fas fa-plus-circle"></i> Add New Product
                </a>
                <a href="<?php echo BASE_URL; ?>admin/viewproducts.php" class="action-button primary">
                    <i class="fas fa-tshirt"></i> View Products
                </a>
                <a href="<?php echo BASE_URL; ?>admin/managecategory.php" class="action-button secondary">
                    <i class="fas fa-list-alt"></i> Manage Categories
                </a>
                <a href="<?php echo BASE_URL; ?>admin/vieworders.php" class="action-button link-view-orders">
                    <i class="fas fa-shopping-cart"></i> View Orders
                </a>
                <a href="<?php echo BASE_URL; ?>admin/userdetails.php" class="action-button primary">
                    <i class="fas fa-users"></i> View Users
                </a>
                <a href="<?php echo BASE_URL; ?>admin/customerfeedback.php" class="action-button secondary">
                    <i class="fas fa-comments"></i> Manage Reviews
                </a>
            </div>
        </div>
    </div>
</body>
</html>
