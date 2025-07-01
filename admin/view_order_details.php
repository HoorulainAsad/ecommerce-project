<?php
// admin/view_order_details.php

// Corrected path: Assuming database.php is in the 'includes' subdirectory of the current 'admin' directory.
require_once __DIR__ . '/includes/database.php'; // Corrected path for database.php

$orderFound = false;
$errorMessage = "";
$order = null;
$itemsResult = null;
$subtotal_items = 0; // Initialize subtotal for items

if (!isset($_GET['id'])) {
    // Handle case where order ID is not provided
    $errorMessage = "Order ID not provided. Please provide a valid order ID in the URL (e.g., ?id=123).";
} else {
    $orderId = intval($_GET['id']);
    $conn = getDbConnection(); // Assuming this function returns a mysqli connection

    if ($conn->connect_error) {
        $errorMessage = "Database connection failed: " . $conn->connect_error;
    } else {
        // Fetch order details
        // UPDATED: Fetched 'id' instead of 'order_id', 'order_status' instead of 'status', and added shipping fields.
        $orderQuery = $conn->prepare("SELECT id, customer_name, customer_email, order_date, order_status, total_amount, shipping_address, city, postal_code FROM orders WHERE id = ?");
        if ($orderQuery === false) {
            $errorMessage = "Failed to prepare order query: " . $conn->error;
        } else {
            $orderQuery->bind_param("i", $orderId);
            $orderQuery->execute();
            $orderResult = $orderQuery->get_result();

            if ($orderResult->num_rows === 0) {
                $errorMessage = "Order with ID #{$orderId} not found.";
            } else {
                $order = $orderResult->fetch_assoc();
                $orderFound = true;

                // Fetch ordered items
                // Assuming 'products' table has 'id' (as primary key), 'name'
                // Assuming 'order_items' table has 'order_id', 'product_id', 'quantity', 'price'
                $itemsQuery = $conn->prepare("
                    SELECT products.name, order_items.quantity, order_items.price
                    FROM order_items
                    JOIN products ON order_items.product_id = products.id -- CORRECTED: Changed products.product_id to products.id
                    WHERE order_items.order_id = ?
                ");
                if ($itemsQuery === false) {
                    // If items query fails, still show order details but indicate items issue
                    $errorMessage = "Failed to prepare items query: " . $conn->error;
                } else {
                    $itemsQuery->bind_param("i", $orderId);
                    $itemsQuery->execute();
                    $itemsResult = $itemsQuery->get_result();

                    // Calculate subtotal from items if they are successfully fetched
                    if ($itemsResult && $itemsResult->num_rows > 0) {
                        // Store items in an array to iterate multiple times if needed, or just calculate subtotal
                        // For simplicity, we'll just calculate subtotal here
                        $tempItems = [];
                        while ($item = $itemsResult->fetch_assoc()) {
                            $itemSubtotal = $item['quantity'] * $item['price'];
                            $subtotal_items += $itemSubtotal;
                            $tempItems[] = $item; // Store for later display
                        }
                        // Reset pointer for displaying in HTML
                        $itemsResult->data_seek(0);
                    }
                }
            }
            $orderQuery->close();
        }
        // The closeDbConnection() function registered by register_shutdown_function in database.php will handle closing the connection.
        // No explicit $conn->close() here.
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts: Lora and Anonymous Pro -->
    <link href="https://fonts.googleapis.com/css2?family=Anonymous+Pro:ital,wght@0,400;0,700;1,400;1,700&family=Lora:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
    <style>
        /* Custom styles based on the provided image */
        body {
            font-family: 'Lora', serif; /* Primary font from the image */
            background-color: #f8f5ed; /* Light beige background from the image */
        }
        h1, h2, h3, th {
            font-family: 'Anonymous Pro', monospace; /* Heading/table header font from the image */
            color: #4a0000; /* Dark red/maroon color from the image */
        }
        .bg-white {
            background-color: #ffffff; /* Keep card background white for contrast */
        }
        .text-gray-800 {
            color: #4a0000; /* Apply dark red to main headings */
        }
        .text-gray-700 {
            color: #6b0000; /* Slightly lighter red for subheadings */
        }
        .text-gray-600 {
            color: #8a2b2b; /* Even lighter red for general text */
        }
        .text-green-600 { /* For status, if it's a success color */
            color: #228B22; /* Forest Green */
        }
        .bg-blue-500 { /* Back button */
            background-color: #8a0000; /* Dark red for buttons */
        }
        .hover\:bg-blue-600:hover { /* Back button hover */
            background-color: #6b0000; /* Darker red on hover */
        }
        .border-b {
            border-color: #d1c4b3; /* Lighter brown/beige for borders */
        }
        .bg-gray-100 {
            background-color: #f0e9df; /* Light beige for table header background */
        }
        .border-gray-200 {
            border-color: #e0d9cc; /* Lighter beige for table row borders */
        }
        .hover\:bg-gray-50:hover {
            background-color: #f5efe6; /* Slightly darker beige on table row hover */
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg p-6 md:p-8 w-full max-w-3xl">
        <div class="flex items-center justify-between mb-6 border-b pb-4">
            <h1 class="text-3xl font-bold text-gray-800">Order Details</h1>
            <button onclick="history.back()" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg shadow transition duration-300 ease-in-out">
                &larr; Back to Orders
            </button>
        </div>

        <?php if (!$orderFound): ?>
            <div class="text-center text-red-600 text-xl font-semibold py-8">
                <?php echo htmlspecialchars($errorMessage); ?>
            </div>
        <?php else: ?>
            <!-- Order Summary Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div>
                    <h2 class="text-xl font-semibold text-gray-700 mb-3">Order Summary</h2>
                    <div class="space-y-2 text-gray-600">
                        <p><span class="font-medium">Order ID:</span> #<?php echo htmlspecialchars($order['id']); ?></p>
                        <p><span class="font-medium">Order Date:</span> <?php echo htmlspecialchars($order['order_date']); ?></p>
                        <p><span class="font-medium">Status:</span> <span class="text-green-600 font-semibold"><?php echo htmlspecialchars($order['order_status']); ?></span></p>
                        <p><span class="font-medium">Total Amount:</span> $<?php echo htmlspecialchars(number_format($order['total_amount'], 2)); ?></p>
                    </div>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-700 mb-3">Customer Information</h2>
                    <div class="space-y-2 text-gray-600">
                        <p><span class="font-medium">Customer Name:</span> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                        <p><span class="font-medium">Customer Email:</span> <?php echo htmlspecialchars($order['customer_email']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Shipping Information Section -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-700 mb-3">Shipping Information</h2>
                <div class="space-y-2 text-gray-600">
                    <p><span class="font-medium">Address:</span> <?php echo htmlspecialchars($order['shipping_address']); ?></p>
                    <p><span class="font-medium">City:</span> <?php echo htmlspecialchars($order['city']); ?></p>
                    <p><span class="font-medium">Postal Code:</span> <?php echo htmlspecialchars($order['postal_code']); ?></p>
                </div>
            </div>

            <!-- Order Items Section -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">Ordered Items</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white rounded-lg shadow-sm">
                        <thead>
                            <tr class="bg-gray-100 text-left text-gray-600 uppercase text-sm leading-normal">
                                <th class="py-3 px-6">Product</th>
                                <th class="py-3 px-6">Quantity</th>
                                <th class="py-3 px-6">Price</th>
                                <th class="py-3 px-6 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700 text-sm font-light">
                            <?php
                            // Check if $itemsResult is valid and has rows
                            if ($itemsResult && $itemsResult->num_rows > 0) {
                                // Reset pointer to display items after subtotal calculation
                                $itemsResult->data_seek(0);
                                while ($item = $itemsResult->fetch_assoc()):
                                    $itemSubtotal = $item['quantity'] * $item['price'];
                            ?>
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="py-3 px-6 whitespace-nowrap">
                                        <span><?php echo htmlspecialchars($item['name']); ?></span>
                                    </td>
                                    <td class="py-3 px-6"><?php echo htmlspecialchars($item['quantity']); ?></td>
                                    <td class="py-3 px-6">$<?php echo htmlspecialchars(number_format($item['price'], 2)); ?></td>
                                    <td class="py-3 px-6 text-right">$<?php echo htmlspecialchars(number_format($itemSubtotal, 2)); ?></td>
                                </tr>
                            <?php
                                endwhile;
                            } else {
                                echo '<tr><td colspan="4" class="py-3 px-6 text-center text-gray-500">No items found for this order.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Totals Section -->
            <div class="flex justify-end">
                <div class="w-full md:w-1/2 lg:w-1/3 space-y-2 text-gray-700">
                    <div class="flex justify-between">
                        <span>Subtotal (Items):</span>
                        <span class="font-medium">$<?php echo htmlspecialchars(number_format($subtotal_items, 2)); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Shipping:</span>
                        <span class="font-medium">$0.00</span> <!-- Assuming shipping is always $0.00 for this example -->
                    </div>
                    <div class="flex justify-between border-t pt-2 mt-2 font-bold text-lg text-gray-800">
                        <span>Total:</span>
                        <span>$<?php echo htmlspecialchars(number_format($order['total_amount'], 2)); ?></span>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
