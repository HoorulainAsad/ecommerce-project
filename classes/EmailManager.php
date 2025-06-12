<?php
// classes/EmailManager.php

class EmailManager {

    /**
     * Sends an order confirmation email to the customer.
     * @param string $recipientEmail The email address of the customer.
     * @param array $orderDetails An associative array containing all order details, including the 'items' array.
     * @return bool True if the email was successfully accepted for delivery by the local mail server, false otherwise.
     */
    public function sendOrderConfirmationEmail($recipientEmail, $orderDetails) {
        // Subject of the email
        $subject = "Order Confirmation - MSGM Bridal #" . $orderDetails['id'];

        // Build the HTML message for the email
        $message = "
        <html>
        <head>
            <title>Order Confirmation - MSGM Bridal</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 20px auto; border: 1px solid #ddd; border-radius: 8px; padding: 20px; background-color: #f9f9f9; }
                h2 { color: #7f0e10; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
                th { background-color: #e9e3ce; color: #2e2e2e; }
                .total { font-weight: bold; color: #7f0e10; }
                .footer { margin-top: 30px; font-size: 0.9em; color: #666; text-align: center; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>Thank you for your order, " . htmlspecialchars($orderDetails['customer_name']) . "!</h2>
                <p>Your order #<strong>" . htmlspecialchars($orderDetails['id']) . "</strong> has been placed successfully and is currently <strong>" . htmlspecialchars($orderDetails['order_status']) . "</strong>.</p>
                <p>We'll send you another email when your order ships.</p>

                <h3>Order Details:</h3>
                <p><strong>Order Date:</strong> " . date('F j, Y, g:i a', strtotime($orderDetails['created_at'])) . "</p>
                <p><strong>Order Total:</strong> $" . htmlspecialchars(number_format($orderDetails['order_total'], 2)) . "</p>
                <p><strong>Payment Method:</strong> " . htmlspecialchars($orderDetails['payment_method']) . "</p>

                <h3>Shipping Address:</h3>
                <p>
                    " . htmlspecialchars($orderDetails['customer_name']) . "<br>
                    " . htmlspecialchars($orderDetails['shipping_address']) . "<br>
                    " . htmlspecialchars($orderDetails['city']) . ", " . htmlspecialchars($orderDetails['postal_code']) . "<br>
                    Phone: " . htmlspecialchars($orderDetails['customer_phone']) . "
                </p>

                <h3>Items in Your Order:</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Size</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>";
        foreach ($orderDetails['items'] as $item) {
            $message .= "
                        <tr>
                            <td>" . htmlspecialchars($item['product_name']) . "</td>
                            <td>" . htmlspecialchars($item['size']) . "</td>
                            <td>" . htmlspecialchars(number_format($item['quantity'])) . "</td>
                            <td>$" . htmlspecialchars(number_format($item['price'], 2)) . "</td>
                            <td>$" . htmlspecialchars(number_format($item['quantity'] * $item['price'], 2)) . "</td>
                        </tr>";
        }
        $message .= "
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan='4' style='text-align: right;' class='total'>Order Total:</td>
                            <td class='total'>$" . htmlspecialchars(number_format($orderDetails['order_total'], 2)) . "</td>
                        </tr>
                    </tfoot>
                </table>

                <p class='footer'>
                    If you have any questions, please contact us at support@msgmbridal.com.<br>
                    Thank you for shopping with MSGM Bridal!
                </p>
            </div>
        </body>
        </html>
        ";

        // Headers for HTML email
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: MSGM Bridal <noreply@yourdomain.com>' . "\r\n"; // IMPORTANT: Replace 'noreply@yourdomain.com' with a valid email address that exists on your server or SMTP configuration. Otherwise, emails might not send or go to spam.

        // Attempt to send the email
        // Note: For mail() to work, your PHP environment must be configured for email sending (e.g., sendmail on Linux, or SMTP settings in php.ini for Windows).
        // If it doesn't work, this function will return false, but won't throw an error directly.
        // For production, use a dedicated email library like PHPMailer or a transactional email service.
        return mail($recipientEmail, $subject, $message, $headers);
    }
}
?>
