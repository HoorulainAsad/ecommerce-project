<?php
// admin/managecategory.php

require_once __DIR__ . '/includes/functions.php'; // Includes config and starts session
require_once __DIR__ . '/classes/CategoryManager.php';
require_once __DIR__ . '/classes/ProductManager.php'; // Needed to fetch products by category

// Check if admin is logged in
if (!isLoggedIn()) {
    redirectTo('login.php');
}

$categoryManager = new CategoryManager();
$productManager = new ProductManager(); // Initialize ProductManager

$message = '';
$message_type = ''; // success or error

// Define the main categories that are allowed to be managed
$allowed_main_categories = ['FORMAL', 'PARTYWEAR', 'BRIDAL'];

// Handle Add Category Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $categoryName = sanitizeInput(strtoupper($_POST['category_name'] ?? '')); // Convert to uppercase for consistency

    if (empty($categoryName)) {
        $message = "Category name cannot be empty.";
        $message_type = 'error';
    } elseif (!in_array($categoryName, $allowed_main_categories)) {
        $message = "Only 'FORMAL', 'PARTYWEAR', and 'BRIDAL' can be added as main categories through this panel.";
        $message_type = 'error';
    } else {
        if ($categoryManager->addCategory($categoryName)) {
            $message = "Category '" . htmlspecialchars($categoryName) . "' added successfully!";
            $message_type = 'success';
        } else {
            $message = "Error adding category. It might already exist.";
            $message_type = 'error';
        }
    }
}

// Handle Delete Category
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $categoryIdToDelete = (int)$_GET['id'];
    $categoryToDelete = null;
    $allCategories = $categoryManager->getAllCategories();
    foreach ($allCategories as $cat) {
        if ($cat['id'] == $categoryIdToDelete) {
            $categoryToDelete = $cat;
            break;
        }
    }

    if ($categoryToDelete && in_array(strtoupper($categoryToDelete['name']), $allowed_main_categories)) {
        // Prevent deletion of main categories from the UI
        $message = "Main categories (FORMAL, PARTYWEAR, BRIDAL) cannot be deleted from this interface to maintain structure.";
        $message_type = 'error';
    } else {
        // Check if there are products linked to this category before deleting
        $linkedProducts = $productManager->getProductsByCategoryId($categoryIdToDelete);
        if (!empty($linkedProducts)) {
            $message = "Cannot delete category because it has " . count($linkedProducts) . " products linked to it. Please reassign or delete linked products first.";
            $message_type = 'error';
        } else {
            if ($categoryManager->deleteCategory($categoryIdToDelete)) {
                $message = "Category deleted successfully!";
                $message_type = 'success';
            } else {
                $message = "Error deleting category.";
                $message_type = 'error';
            }
        }
    }
    // Redirect to clear GET parameters after deletion
    redirectTo('managecategory.php?msg=' . urlencode($message) . '&type=' . $message_type);
}

// Check for messages from redirects (e.g., after deletion)
if (isset($_GET['msg']) && isset($_GET['type'])) {
    $message = sanitizeInput($_GET['msg']);
    $message_type = sanitizeInput($_GET['type']);
}

// Fetch all main categories for display and looping through them
$allMainCategories = $categoryManager->getAllCategories();
$mainCategoriesToDisplay = array_filter($allMainCategories, function($cat) use ($allowed_main_categories) {
    return in_array(strtoupper($cat['name']), $allowed_main_categories);
});

// Also fetch any other (non-main) categories for displaying and potential deletion
$otherCategoriesToDisplay = array_filter($allMainCategories, function($cat) use ($allowed_main_categories) {
    return !in_array(strtoupper($cat['name']), $allowed_main_categories);
});


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - MSGM Bridal Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Anonymous+Pro:ital,wght@0,400;0,700;1,400;1,700&family=Lora:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/styles.css">
    <!-- <style>
        .category-section {
            background-color: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        .category-section h3 {
            color: #A0522D;
            margin-bottom: 20px;
            font-size: 22px;
            font-weight: 600;
        }
        .category-section h4 {
            color: #8B4513;
            margin-top: 30px;
            margin-bottom: 15px;
            font-size: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
    </style> -->
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'sidepanel.php'; ?>

        <div class="main-content-area">
            <?php include 'navbar.php'; ?>

            <h1 class="page-header">Manage Categories</h1>

            <?php if ($message): ?>
                <?php displayMessage($message, $message_type); ?>
            <?php endif; ?>

            <div class="form-section">
                <h3>Add New Main Category</h3>
                <form action="managecategory.php" method="POST">
                    <div class="form-group">
                        <label for="category_name">Category Name (FORMAL, PARTYWEAR, BRIDAL only):</label>
                        <input type="text" id="category_name" name="category_name" required>
                    </div>
                    <button type="submit" name="add_category" class="submit-btn">Add Category</button>
                </form>
            </div>

            <?php if (!empty($mainCategoriesToDisplay)): ?>
                <div class="category-section">
                    <h3>Main Categories and Their Products</h3>
                    <?php foreach ($mainCategoriesToDisplay as $category): ?>
                        <h4><?php echo htmlspecialchars($category['name']); ?> Products (ID: <?php echo htmlspecialchars($category['id']); ?>)</h4>
                        <?php
                            $productsInCategory = $productManager->getProductsByCategoryId($category['id']);
                            if (empty($productsInCategory)):
                        ?>
                            <p>No products found in this category. <a href="<?php echo BASE_URL; ?>addproduct.php">Add one now!</a></p>
                        <?php else: ?>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Price</th>
                                        <th>Stock Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($productsInCategory as $product): ?>
                                        <tr>
                                            <td data-label="Image">
                                                <?php if (!empty($product['image_url'])): ?>
                                                    <img src="<?php echo BASE_URL . htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image-thumb" onerror="this.onerror=null;this.src='https://placehold.co/80x80/E0E0E0/555555?text=No+Image';">
                                                <?php else: ?>
                                                    <img src="https://placehold.co/80x80/E0E0E0/555555?text=No+Image" alt="No Image" class="product-image-thumb">
                                                <?php endif; ?>
                                            </td>
                                            <td data-label="Name"><?php echo htmlspecialchars($product['name']); ?></td>
                                            <td data-label="Price">$<?php echo htmlspecialchars(number_format($product['price'], 2)); ?></td>
                                            <td data-label="Stock Status">
                                                <?php if ($product['stock'] <= 0): ?>
                                                    <span class="out-of-stock-label">Out of Stock</span>
                                                <?php else: ?>
                                                    <span class="in-stock-label"><?php echo htmlspecialchars($product['stock']); ?> In Stock</span>
                                                <?php endif; ?>
                                            </td>
                                            <td data-label="Actions" class="action-links">
                                                <a href="<?php echo BASE_URL; ?>editproduct.php?id=<?php echo $product['id']; ?>" title="Edit"><i class="fas fa-edit"></i></a>
                                                <a href="<?php echo BASE_URL; ?>viewproducts.php?action=delete&id=<?php echo $product['id']; ?>" onclick="return confirm('Are you sure you want to delete this product?');" title="Delete"><i class="fas fa-trash-alt"></i></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No main categories found. Please add FORMAL, PARTYWEAR, and BRIDAL categories above.</p>
            <?php endif; ?>

            <?php if (!empty($otherCategoriesToDisplay)): ?>
                <div class="category-section">
                    <h3>Other Categories (Not Main)</h3>
                    <p>These categories are not considered main product categories and can be deleted if no longer needed.</p>
                     <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Category Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($otherCategoriesToDisplay as $category): ?>
                                <tr>
                                    <td data-label="ID"><?php echo htmlspecialchars($category['id']); ?></td>
                                    <td data-label="Category Name"><?php echo htmlspecialchars($category['name']); ?></td>
                                    <td data-label="Actions" class="action-links">
                                        <a href="<?php echo BASE_URL; ?>managecategory.php?action=delete&id=<?php echo $category['id']; ?>" onclick="return confirm('Are you sure you want to delete this category? Products linked to it will have their category set to NULL.');" title="Delete"><i class="fas fa-trash-alt"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
