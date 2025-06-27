<?php
// search.php (Search Results Page)

require_once __DIR__ . '/includes/header.php'; // Includes functions.php and starts session
require_once __DIR__ . '/classes/ProductFrontendManager.php';

$productManager = new ProductFrontendManager();

// Sanitize the search query from GET request
$searchQuery = sanitizeInput($_GET['q'] ?? '');
$products = [];
$pageTitle = "Search Results for '" . htmlspecialchars($searchQuery) . "'";

if (!empty($searchQuery)) {
    // Get database connection. The getDbConnection() function should ideally handle
    // connection persistence and reconnection (e.g., using mysqli_ping()).
    $db = getDbConnection();

    // Check if the connection was successfully established.
    if (!$db) {
        displayMessage("Could not connect to the database. Please try again later.", "error");
    } else {
        // Prepare the SQL query to search for products by name or description.
        // Using LIKE for partial matches.
        $sql = "SELECT p.id, p.name, p.description, p.price, p.stock, p.image_url, c.name AS category_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.name LIKE ? OR p.description LIKE ?
                ORDER BY p.name ASC";

        $stmt = $db->prepare($sql); // Prepare the SQL statement

        if ($stmt) {
            $searchTerm = '%' . $searchQuery . '%';
            // Bind parameters to the prepared statement. "ss" means two string parameters.
            $stmt->bind_param("ss", $searchTerm, $searchTerm);

            // Execute the prepared statement. This is where the original error occurred.
            // If getDbConnection() implements mysqli_ping(), it should help prevent "server gone away"
            // errors by attempting to reconnect if the connection has died.
            if ($stmt->execute()) {
                $result = $stmt->get_result(); // Get the result set
                // Fetch all matching products
                while ($row = $result->fetch_assoc()) {
                    $row['is_out_of_stock'] = ($row['stock'] <= 0); // Determine stock status
                    $products[] = $row;
                }
                $result->free(); // Free the result set
            } else {
                // Log and display error if statement execution fails
                error_log("Search query execution failed: " . $stmt->error);
                displayMessage("A database error occurred during your search. Please try again.", "error");
            }
            $stmt->close(); // Close the statement
        } else {
            // Log and display error if statement preparation fails
            error_log("Search query preparation failed: " . $db->error);
            displayMessage("Failed to prepare search query. Please try again later.", "error");
        }
    }
} else {
    $pageTitle = "Search Products";
    displayMessage("Please enter a search term to find products.", "info"); // More specific message
}

?>

<div class="container-fluid container-xl py-5 page-content">
    <h1 class="text-center mb-5 text-primary-custom"><?php echo $pageTitle; ?></h1>

    <?php if (empty($products) && !empty($searchQuery)): ?>
        <div class="alert alert-warning text-center" role="alert">
            No products found matching your search for "<strong><?php echo htmlspecialchars($searchQuery); ?></strong>". Please try a different query.
        </div>
    <?php elseif (empty($products) && empty($searchQuery)): ?>
        <!-- Message already displayed by displayMessage() function above -->
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4">
            <?php foreach ($products as $product): ?>
                <div class="col">
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
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
