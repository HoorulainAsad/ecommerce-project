<?php
// category.php (Category Listing Page)

require_once __DIR__ . '/includes/header.php'; // Includes functions.php and starts session
require_once __DIR__ . '/classes/ProductFrontendManager.php';
require_once __DIR__ . '/classes/CategoryFrontendManager.php'; // To get category name

$productManager = new ProductFrontendManager();
$categoryManager = new CategoryFrontendManager();

$filter = sanitizeInput($_GET['name'] ?? 'all'); // Get filter from URL, default to 'all'
$products = [];
$pageTitle = "All Products";

switch (strtolower($filter)) {
    case 'bridal':
    case 'formal':
    case 'partywear':
        $categoryInfo = $categoryManager->getCategoryByName($filter);
        if ($categoryInfo) {
            $products = $productManager->getFilteredProducts($filter);
            $pageTitle = htmlspecialchars($categoryInfo['name']) . " Collection";
        } else {
            $pageTitle = "Category Not Found";
        }
        break;
    case 'trendy':
        $products = $productManager->getFilteredProducts('trendy');
        $pageTitle = "Trendy Collection";
        break;
    case 'new_arrivals':
        $products = $productManager->getFilteredProducts('new_arrivals');
        $pageTitle = "New Arrivals";
        break;
    case 'all':
    default:
        $products = $productManager->getFilteredProducts('all');
        $pageTitle = "All Dresses";
        break;
}

?>

<div class="container-fluid container-xl py-5 page-content">
    <h1 class="text-center mb-5 text-primary-custom"><?php echo $pageTitle; ?></h1>

    <?php if (empty($products)): ?>
        <div class="alert alert-info text-center" role="alert">
            No products found for this category at the moment. Please check back later!
            <?php if (strtolower($filter) !== 'all'): ?>
                <a href="<?php echo BASE_URL; ?>category.php?name=all" class="alert-link">View all products</a>.
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="product-grid">
    <?php foreach ($products as $product): ?>
        <div class="product-card">
            <div class="product-card-img-wrapper">
                <img src="<?php echo BASE_URL . htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" onerror="this.onerror=null;this.src='https://placehold.co/400x300/E0E0E0/555555?text=No+Image';">
            </div>
            <div class="product-card-body">
                <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                <p class="price">$<?php echo htmlspecialchars(number_format($product['price'], 2)); ?></p>
                <?php if ($product['is_out_of_stock']): ?>
                    <span class="out-of-stock-badge">Out of Stock</span>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>product_detail.php?id=<?php echo htmlspecialchars($product['id']); ?>" class="btn btn-details">View Details</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
