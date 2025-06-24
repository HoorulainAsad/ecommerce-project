<?php
// admin/viewproducts.php (Previously manageproducts.php)

require_once __DIR__ . '/includes/functions.php'; // Includes config and starts session
require_once __DIR__ . '/classes/ProductManager.php';
require_once __DIR__ . '/classes/CategoryManager.php';
require_once __DIR__ . '/classes/OrderManager.php'; // Needed for trendy products

// Check if admin is logged in
if (!isLoggedIn()) {
    redirectToAdmin('login.php');
}

$productManager = new ProductManager();
$categoryManager = new CategoryManager();
$orderManager = new OrderManager();

$message = '';
$message_type = ''; // success or error

// Define the directory where uploaded product images are stored
$uploadDir = __DIR__ . '/uploads/products/';

// --- Handle Edit Product Form Submission ---
// This logic was moved from manageproducts.php, but it will be slightly different now.
// For simplicity, I'm assuming 'edit' action takes you to a separate 'editproduct.php'
// or you'll handle it via a modal here. For now, let's keep it simple:
// if you click edit, it will take you to a pre-filled Add Product page.
// In a full system, you might have an editproduct.php similar to addproduct.php but with update logic.

// --- Handle Delete Product ---
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $productIdToDelete = (int)$_GET['id'];
    // Optional: Delete the actual image file from the server when product is deleted
    $productToDelete = $productManager->getProductById($productIdToDelete);
    if ($productToDelete && !empty($productToDelete['image_url'])) {
        $filePath = __DIR__ . '/' . $productToDelete['image_url']; // Construct full path
        if (file_exists($filePath) && is_file($filePath)) {
            unlink($filePath); // Delete the file
        }
    }

    if ($productManager->deleteProduct($productIdToDelete)) {
        $message = "Product deleted successfully!";
        $message_type = 'success';
    } else {
        $message = "Error deleting product.";
        $message_type = 'error';
    }
    // Redirect to clear GET parameters after deletion
    redirectToAdmin('viewproducts.php?msg=' . urlencode($message) . '&type=' . $message_type);
}

// Check for messages from redirects (e.g., after deletion or add product)
if (isset($_GET['msg']) && isset($_GET['type'])) {
    $message = sanitizeInput($_GET['msg']);
    $message_type = sanitizeInput($_GET['type']);
}

// --- Filtering Products ---
$filter = sanitizeInput($_GET['filter'] ?? '');
$pageTitle = "All Products"; // Default title

if ($filter === 'new_arrivals') {
    $products = $productManager->getNewArrivalProducts(); // Get only new arrivals
    $pageTitle = "New Arrival Products";
} elseif ($filter === 'trendy') {
    $products = $orderManager->getTrendyProducts(20); // Get top 20 trendy products
    $pageTitle = "Trendy Products";
} else {
    $products = $productManager->getAllProducts(); // Get all products by default
}
// --- End Filtering Products ---

// Fetch only the main categories for the dropdown (not needed here anymore for product management form)
// $allCategories = $categoryManager->getAllCategories();
// $main_categories_names = ['FORMAL', 'PARTYWEAR', 'BRIDAL'];
// $categoriesForDropdown = array_filter($allCategories, function($cat) use ($main_categories_names) {
//     return in_array(strtoupper($cat['name']), $main_categories_names);
// });

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - MSGM Bridal Admin</title><link href="https://fonts.googleapis.com/css2?family=Anonymous+Pro:ital,wght@0,400;0,700;1,400;1,700&family=Lora:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet"><link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>admin/assets/css/styles.css">
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'sidepanel.php'; ?>

        <div class="main-content-area">
            <?php include 'navbar.php'; ?>

            <h1 class="page-header"><?php echo $pageTitle; ?></h1>

            <?php if ($message): ?>
                <?php displayMessage($message, $message_type); ?>
            <?php endif; ?>

            <?php if ($filter): // Show filter info if filter is active ?>
                <div class="filter-header">
                    <i class="fas fa-filter"></i> Displaying: <?php echo htmlspecialchars($pageTitle); ?>
                    <a href="<?php echo BASE_URL; ?>admin/viewproducts.php" style="margin-left: 15px;">Clear Filter</a>
                </div>
            <?php endif; ?>

            <h3 class="page-header" style="font-size: 22px;">
                <?php echo ($filter === 'new_arrivals' || $filter === 'trendy') ? 'List of ' . $pageTitle : 'Existing Products'; ?>
            </h3>
            <?php if (empty($products)): ?>
                <p>No products found <?php echo $filter ? 'matching this filter.' : '.'; ?>
                <?php if ($filter === 'new_arrivals'): ?>
                    <a href="<?php echo BASE_URL; ?>admin/addproduct.php">Add a new product</a> to see it here.
                <?php elseif ($filter === 'trendy'): ?>
                    Once orders are placed, products will appear here.
                <?php endif; ?>
                </p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock Status</th>
                            <?php if ($filter === 'trendy'): ?>
                                <th>Ordered Quantity</th>
                            <?php endif; ?>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td data-label="Image">
                                    <?php if (!empty($product['image_url'])): ?>
                                        <img src="<?php echo WEB_ROOT_URL . htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image-thumb" onerror="this.onerror=null;this.src='https://placehold.co/80x80/E0E0E0/555555?text=No+Image';">
                                    <?php else: ?>
                                        <img src="https://placehold.co/80x80/E0E0E0/555555?text=No+Image" alt="No Image" class="product-image-thumb">
                                    <?php endif; ?>
                                </td>
                                <td data-label="Name"><?php echo htmlspecialchars($product['name']); ?></td>
                                <td data-label="Category"><?php echo htmlspecialchars($product['category_name'] ?? 'N/A'); ?></td>
                                <td data-label="Price">$<?php echo htmlspecialchars(number_format($product['price'], 2)); ?></td>
                                <td data-label="Stock Status">
                                <?php
                                // Use the null coalescing operator to safely access 'stock', defaulting to 0 if not set.
                                // This prevents the "Undefined array key" warning.
                                $current_stock = $product['stock'] ?? 0;

                                if ($current_stock <= 0):
                                ?>
                                    <span class="out-of-stock-label">Out of Stock</span>
                                <?php else: ?>
                                    <span class="in-stock-label"><?php echo htmlspecialchars($current_stock); ?> In Stock</span>
                                <?php endif; ?>
                            </td>
                                <?php if ($filter === 'trendy'): ?>
                                    <td data-label="Ordered Quantity"><?php echo htmlspecialchars($product['total_ordered_quantity'] ?? 0); ?></td>
                                <?php endif; ?>
                                <td data-label="Actions" class="action-links">
                                    <a href="<?php echo BASE_URL; ?>admin/editproduct.php?id=<?php echo $product['id']; ?>" title="Edit"><i class="fas fa-edit"></i></a>
                                    <a href="<?php echo BASE_URL; ?>admin/viewproducts.php?action=delete&id=<?php echo $product['id']; ?>" onclick="return confirm('Are you sure you want to delete this product?');" title="Delete"><i class="fas fa-trash-alt"></i></a>
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
