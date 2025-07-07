<?php
// admin/editproduct.php

require_once __DIR__ . '/includes/functions.php'; // Includes config and starts session
require_once __DIR__ . '/classes/ProductManager.php';
require_once __DIR__ . '/classes/CategoryManager.php';

// Check if admin is logged in
if (!isLoggedIn()) {
    
    redirectToAdmin('login.php');
}

$productManager = new ProductManager();
$categoryManager = new CategoryManager();

$message = '';
$message_type = ''; 


$uploadDir = dirname(__DIR__) . '/uploads/products/';

// Ensure the directory exists and has correct permissions
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true); 
}

// Get product ID from GET parameter for editing
$productId = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
if ($productId <= 0) {
    redirectToAdmin('viewproducts.php?msg=' . urlencode('No product ID provided for editing.') . '&type=error');
}

$editProduct = $productManager->getProductById($productId);
if (!$editProduct) {
    redirectToAdmin('viewproducts.php?msg=' . urlencode('Product not found for editing.') . '&type=error');
}

// Pre-fill form variables
$name = $editProduct['name'];
$description = $editProduct['description'];
$price = $editProduct['price'];
$categoryId = $editProduct['category_id'];
$stock = $editProduct['stock'];
$currentImageUrl = $editProduct['image_url']; 

// Handle form submission for update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $price = filter_var($_POST['price'] ?? 0, FILTER_VALIDATE_FLOAT);
    $categoryId = filter_var($_POST['category_id'] ?? 0, FILTER_VALIDATE_INT);
    $stock = filter_var($_POST['stock'] ?? 0, FILTER_VALIDATE_INT);

    $imageUrlToSave = $currentImageUrl; 

    // --- File Upload Handling ---
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['product_image'];
        $fileName = basename($file['name']);
        $fileTmpName = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileError = $file['error'];
        $fileType = $file['type'];

        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($fileExt, $allowed)) {
            if ($fileError === 0) {
                if ($fileSize < 5000000) { // Max 5MB file size
                    $newFileName = uniqid('product_', true) . "." . $fileExt; 
                    $fileDestination = $uploadDir . $newFileName;

                    if (move_uploaded_file($fileTmpName, $fileDestination)) {
                        
                        if (!empty($currentImageUrl) && file_exists(dirname(__DIR__) . '/' . $currentImageUrl)) {
                            $oldFilePath = dirname(__DIR__) . '/' . $currentImageUrl;
                            if (is_file($oldFilePath)) { // Double-check it's a file
                                unlink($oldFilePath);
                            }
                        }
                       
                        $imageUrlToSave = 'uploads/products/' . $newFileName;
                    } else {
                        $message = "Error uploading new file. Check folder permissions.";
                        $message_type = 'error';
                        
                        $imageUrlToSave = $currentImageUrl;
                    }
                } else {
                    $message = "Your new file is too large (max 5MB).";
                    $message_type = 'error';
                    // Keep current image if new upload failed validation
                    $imageUrlToSave = $currentImageUrl;
                }
            } else {
                $message = "There was an error uploading your new file. Error code: " . $fileError;
                $message_type = 'error';
                // Keep current image if new upload had an error
                $imageUrlToSave = $currentImageUrl;
            }
        } else {
            $message = "You cannot upload files of this type. Only JPG, JPEG, PNG, GIF allowed.";
            $message_type = 'error';
            // Keep current image if new upload failed validation
            $imageUrlToSave = $currentImageUrl;
        }
    } else if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        
        $message = "File upload error: " . $_FILES['product_image']['error'] . ". Please check file size.";
        $message_type = 'error';
        // Keep current image if an upload error occurred
        $imageUrlToSave = $currentImageUrl;
    }
    // --- End File Upload Handling ---

    // Basic validation for other fields
    if (empty($name) || $price === false || $price <= 0 || $categoryId === false || $categoryId <= 0 || $stock === false || $stock < 0) {
        $message = "Please fill all required fields correctly.";
        $message_type = 'error';
    } else {
       
        if ($message_type === 'error' && isset($_FILES['product_image']) && $_FILES['product_image']['error'] !== UPLOAD_ERR_NO_FILE) {
            
        } else {
            if ($productManager->updateProduct($productId, $name, $description, $price, $categoryId, $stock, $imageUrlToSave)) {
                $message = "Product updated successfully!";
                $message_type = 'success';
                
                $editProduct = $productManager->getProductById($productId);
                $currentImageUrl = $editProduct['image_url'];
            } else {
                $message = "Error updating product. Database error?";
                $message_type = 'error';
            }
        }
    }
}


$allCategories = $categoryManager->getAllCategories();
$main_categories_names = ['FORMAL', 'PARTYWEAR', 'BRIDAL'];
$categoriesForDropdown = array_filter($allCategories, function($cat) use ($main_categories_names) {
    return in_array(strtoupper($cat['name']), $main_categories_names);
});

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - MSGM Bridal Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Anonymous+Pro:ital,wght@0,400;0,700;1,400;1,700&family=Lora:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>admin/assets/css/styles.css">
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'sidepanel.php'; ?>

        <div class="main-content-area">
            <?php include 'navbar.php'; ?>

            <h1 class="page-header">Edit Product</h1>

            <?php if ($message): ?>
                <?php displayMessage($message, $message_type); ?>
            <?php endif; ?>

            <div class="form-section">
                <h3>Product Details (ID: <?php echo htmlspecialchars($productId); ?>)</h3>
                <form action="editproduct.php?id=<?php echo htmlspecialchars($productId); ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($productId); ?>">

                    <div class="form-group">
                        <label for="name">Product Name:</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description:</label>
                        <textarea id="description" name="description"><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="price">Price:</label>
                        <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo htmlspecialchars($price ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="category_id">Category:</label>
                        <select id="category_id" name="category_id" required>
                            <option value="">Select a category</option>
                            <?php foreach ($categoriesForDropdown as $category): ?>
                                <option value="<?php echo htmlspecialchars($category['id']); ?>"
                                    <?php echo ($categoryId == $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="stock">Stock Quantity:</label>
                        <input type="number" id="stock" name="stock" min="0" value="<?php echo htmlspecialchars($stock ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="product_image">Product Image:</label>
                        <input type="file" id="product_image" name="product_image" accept="image/*">
                        <small>Upload a new image (JPG, JPEG, PNG, GIF, max 5MB). Leave blank to keep current image.</small>

                        <?php if (!empty($currentImageUrl)): ?>
                            <div class="image-preview-container">
                                <img src="<?php echo BASE_URL . htmlspecialchars($currentImageUrl); ?>?t=<?php echo time(); ?>" 
                                     alt="Current Image" 
                                     onerror="this.onerror=null;this.src='https://placehold.co/100x100/E0E0E0/555555?text=No+Image';">
                                <p>Current Image</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="edit-product-buttons">
                        <button type="submit" class="submit-btn">Update Product</button>
                        <a href="<?php echo BASE_URL; ?>admin/viewproducts.php" class="submit-btn" style="background-color: #6c757d; margin-left: 10px; text-decoration: none;">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>