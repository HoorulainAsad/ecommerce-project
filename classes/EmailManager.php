<?php
// classes/EmailManager.php


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require_once __DIR__ . '/../libs/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../libs/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../libs/PHPMailer/src/SMTP.php';


require_once __DIR__ . '/../admin/includes/config.php';

class EmailManager {

    
    private function getCommonEmailHeader($subject) {
        return "
        <html>
        <head>
            <meta charset='utf-8'>
            <title>" . htmlspecialchars($subject) . "</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4; }
                .container { max-width: 600px; margin: 20px auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
                .header { background: #7f0e10; /* Your brand color */ color: #fff; padding: 10px 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { padding: 20px 0; }
                .footer { text-align: center; font-size: 0.8em; color: #666; margin-top: 20px; padding-top: 10px; border-top: 1px solid #eee; }
                .button { display: inline-block; padding: 10px 20px; margin-top: 15px; background-color: #007bff; color: #fff; text-decoration: none; border-radius: 5px; }
                table { width: 100%; border-collapse: collapse; margin-top: 15px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
                .total-row { font-weight: bold; background-color: #e9e3ce; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>MSGM Bridal</h2>
                </div>
                <div class='content'>"; // Closes with '</div>' in getCommonEmailFooter()
    }

   
    private function getCommonEmailFooter() {
        return "
                </div><!-- .content -->
                <div class='footer'>
                    <p>&copy; " . date('Y') . " MSGM Bridal. All rights reserved.</p>
                    <p>If you have any questions, please contact us at support@msgmbridal.com</p>
                </div>
            </div><!-- .container -->
        </body>
        </html>";
    }

    // --- PHPMailer Integration ---

    /**
     * Private helper function to configure and send email using PHPMailer.
     * @param string $recipientEmail The email address of the recipient.
     * @param string $subject The subject line of the email.
     * @param string $body The HTML body of the email.
     * @return bool True on success, false on failure.
     */
    private function sendEmailWithPHPMailer($recipientEmail, $subject, $body) {
        $mail = new PHPMailer(true); // Enable exceptions for better error handling

        try {
          
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'dummymail@gmail.com';
            $mail->Password   = 'dummy123'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
            $mail->Port       = 587; // or 465 for SSL

            $mail->setFrom('dummymail@gmail.com', 'MSGM Bridal'); // Your store's email address and name
            $mail->addAddress($recipientEmail); 

            // Content
            $mail->isHTML(true); 
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = strip_tags($body);

            $mail->send();
            return true; // Email sent successfully
        } catch (Exception $e) {
            error_log("Message could not be sent to {$recipientEmail}. Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }

    // --- Public Email Sending Methods ---

    public function sendOrderConfirmationEmail($recipientEmail, $orderDetails) {
        $subject = "Order Confirmation - MSGM Bridal #" . htmlspecialchars($orderDetails['id']);

        $messageBody = $this->getCommonEmailHeader($subject);

        // Specific content for order confirmation
        $messageBody .= "
            <p>Dear " . htmlspecialchars($orderDetails['customer_name']) . ",</p>
            <p>Thank you for your order with MSGM Bridal!</p>
            <p>Your order #<strong>" . htmlspecialchars($orderDetails['id']) . "</strong> has been placed successfully and is currently <strong>" . htmlspecialchars(ucfirst($orderDetails['order_status'])) . "</strong>.</p>
            <p>We'll send you another email when your order ships.</p>

            <h3>Order Details:</h3>
            <p><strong>Order Date:</strong> " . date('F j, Y, g:i a', strtotime($orderDetails['created_at'])) . "</p>
            <p><strong>Payment Method:</strong> " . htmlspecialchars(strtoupper($orderDetails['payment_method'])) . "</p>

            <h3>Items in Your Order:</h3>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>";

        // Loop through order items
        if (isset($orderDetails['items']) && is_array($orderDetails['items'])) {
            foreach ($orderDetails['items'] as $item) {
                $productName = htmlspecialchars($item['name'] ?? 'N/A');
                $quantity = htmlspecialchars($item['quantity'] ?? 0);
                $price = number_format($item['price'] ?? 0, 2);
                $subtotal = number_format(($item['quantity'] ?? 0) * ($item['price'] ?? 0), 2);

                $messageBody .= "
                        <tr>
                            <td>{$productName}</td>
                            <td>{$quantity}</td>
                            <td>PKR {$price}</td>
                            <td>PKR {$subtotal}</td>
                        </tr>";
            }
        } else {
             $messageBody .= "<tr><td colspan='4'>No items found for this order.</td></tr>";
        }


        $messageBody .= "
                </tbody>
                <tfoot>
                    <tr class='total-row'>
                        <td colspan='3' style='text-align: right;'><strong>Order Total:</strong></td>
                        <td><strong>PKR " . number_format($orderDetails['total_amount'] ?? 0, 2) . "</strong></td>
                    </tr>
                </tfoot>
            </table>

            <h3>Shipping Information:</h3>
            <p>
                " . htmlspecialchars($orderDetails['customer_name']) . "<br>
                " . htmlspecialchars($orderDetails['shipping_address']) . "<br>
                " . htmlspecialchars($orderDetails['city']) . ", " . htmlspecialchars($orderDetails['postal_code']) . "<br>";
                if (isset($orderDetails['customer_phone'])):
                    $messageBody .= "Phone: " . htmlspecialchars($orderDetails['customer_phone']);
                endif;
        $messageBody .= "</p>
            <p>You can track your order status by logging into your account or by visiting <a href=\"" . WEB_ROOT_URL . "track_order.php?order_id=" . htmlspecialchars($orderDetails['id']) . "\">this link</a>.</p>";

        $messageBody .= $this->getCommonEmailFooter();

        return $this->sendEmailWithPHPMailer($recipientEmail, $subject, $messageBody);
    }

    
    public function sendOrderStatusUpdateEmail($recipientEmail, $orderDetails) {
        if (!is_array($orderDetails) || !isset($orderDetails['id'], $orderDetails['order_status'])) {
            error_log("sendOrderStatusUpdateEmail: Missing or invalid order details array.");
            return false;
        }

        $subject = "Order Status Updated - MSGM Bridal #" . htmlspecialchars($orderDetails['id']);

        $messageBody = $this->getCommonEmailHeader($subject);

        $messageBody .= "
            <p>Dear " . htmlspecialchars($orderDetails['customer_name'] ?? 'Customer') . ",</p>
            <p>Your order #<strong>" . htmlspecialchars($orderDetails['id']) . "</strong> status has been updated to: <span style='color:#7f0e10; font-weight: bold;'>" . htmlspecialchars(ucfirst($orderDetails['order_status'])) . "</span></p>

            <p>You can expect further updates soon. Thank you for shopping with MSGM Bridal.</p>";

        if (isset($orderDetails['order_status']) && strtolower($orderDetails['order_status']) === 'delivered') {
            $messageBody .= "
            <h3>Review Your Items:</h3>
            <p>We'd love to hear your feedback on the products you received!</p>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Review</th>
                    </tr>
                </thead>
                <tbody>";

            
            if (isset($orderDetails['items']) && is_array($orderDetails['items']) && !empty($orderDetails['items'])) {
                foreach ($orderDetails['items'] as $item) {
                    $productId = htmlspecialchars($item['product_id'] ?? '');
                    $productName = htmlspecialchars($item['name'] ?? 'N/A');
                    $price = number_format($item['price'] ?? 0, 2);

                    
                    global $WEB_ROOT_URL; // Access the global WEB_ROOT_URL constant
                    $reviewLink = ($WEB_ROOT_URL ?? 'http://192.168.100.14/msgm_clothing/') . "review_submission.php?order_id=" . htmlspecialchars($orderDetails['id']) . "&product_id={$productId}";

                    $messageBody .= "
                        <tr>
                            <td>{$productName}</td>
                            <td>PKR {$price}</td>
                            <td>
                                <a href='{$reviewLink}'
                                   style='display: inline-block; padding: 8px 15px; background-color: #7f0e10; color: #fff; text-decoration: none; border-radius: 5px; font-size: 0.9em;'>
                                    Leave a Review
                                </a>
                            </td>
                        </tr>";
                }
            } else {
                $messageBody .= "<tr><td colspan='3'>No items found for review.</td></tr>";
            }

            $messageBody .= "
                </tbody>
            </table>";
        }

        $messageBody .= $this->getCommonEmailFooter();

        // Call the private PHPMailer sending function
        return $this->sendEmailWithPHPMailer($recipientEmail, $subject, $messageBody);
    }

   
    public function sendOrderCancellationEmail($recipientEmail, $orderDetails) {
        if (!is_array($orderDetails) || !isset($orderDetails['id'], $orderDetails['customer_name'], $orderDetails['reason'])) {
            error_log("sendOrderCancellationEmail: Missing or invalid order details for cancellation.");
            return false;
        }

        $subject = "Order Cancellation - MSGM Bridal #" . htmlspecialchars($orderDetails['id']);

        $messageBody = $this->getCommonEmailHeader($subject);

        $messageBody .= "
            <p>Dear " . htmlspecialchars($orderDetails['customer_name']) . ",</p>
            <p>We regret to inform you that your order with ID <strong>#" . htmlspecialchars($orderDetails['id']) . "</strong> has been cancelled.</p>
            <p><strong>Reason for Cancellation:</strong> " . htmlspecialchars($orderDetails['reason']) . "</p>
            <p>If you have any questions or would like to re-place your order, please contact us.</p>";

        $messageBody .= $this->getCommonEmailFooter();

        return $this->sendEmailWithPHPMailer($recipientEmail, $subject, $messageBody);
    }
}