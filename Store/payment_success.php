<?php
session_start();
require_once('../config/db_connection.php');
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Handle payment success callback
if (isset($_GET['payment']) && $_GET['payment'] == 'success' && isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];
    $userID = $_SESSION['UserID'];

    // Verify the order belongs to the current user
    $verify_order = $connect->prepare("SELECT OrderID FROM ordertb WHERE OrderID = ? AND UserID = ?");
    $verify_order->bind_param("ss", $order_id, $userID);
    $verify_order->execute();
    $order_result = $verify_order->get_result();

    if ($order_result->num_rows > 0) {

        // Calculate subtotal and prepare item details
        $subtotal = 0;
        $items_html = '';
        $order_items_query = $connect->prepare("
            SELECT p.Title, s.Size, od.OrderUnitQuantity, od.OrderUnitPrice 
            FROM orderdetailtb od
            JOIN producttb p ON od.ProductID = p.ProductID
            LEFT JOIN sizetb s ON od.SizeID = s.SizeID
            WHERE od.OrderID = ?
        ");
        $order_items_query->bind_param("s", $order_id);
        $order_items_query->execute();
        $items_result = $order_items_query->get_result();

        while ($item = $items_result->fetch_assoc()) {
            $line_total = $item['OrderUnitQuantity'] * $item['OrderUnitPrice'];
            $subtotal += $line_total;
            $size_display = $item['Size'] ? $item['Size'] : 'N/A';
            $items_html .= "
                <tr>
                    <td style='padding: 12px; border: 1px solid #ddd;'>{$item['Title']}</td>
                    <td style='padding: 12px; border: 1px solid #ddd;'>{$size_display}</td>
                    <td style='padding: 12px; border: 1px solid #ddd;'>{$item['OrderUnitQuantity']}</td>
                    <td style='padding: 12px; border: 1px solid #ddd;'>$" . number_format($item['OrderUnitPrice'], 2) . "</td>
                    <td style='padding: 12px; border: 1px solid #ddd;'>$" . number_format($line_total, 2) . "</td>
                </tr>
            ";
        }

        // Calculate tax (10%) and total
        $deliveryFee = 5.00;
        $tax = $subtotal * 0.10;
        $total_price = $subtotal + $tax + $deliveryFee;

        // Update order status, total price, and tax
        $update_status = $connect->prepare("UPDATE ordertb SET Status = 'Processing', TotalPrice = ?, OrderTax = ? WHERE OrderID = ?");
        $update_status->bind_param("dds", $total_price, $tax, $order_id);

        if ($update_status->execute()) {
            // Send email confirmation to user via PHPMailer
            if (isset($_SESSION['UserEmail'])) {
                $email = $_SESSION['UserEmail'];
                $username = $_SESSION['Username'] ?? 'Customer';

                try {
                    $mail = new PHPMailer(true);

                    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../config');
                    $dotenv->load();

                    $mail->isSMTP();
                    $mail->Host       = $_ENV['MAIL_HOST'];
                    $mail->SMTPAuth   = true;
                    $mail->Username   = $_ENV['MAIL_USERNAME'];
                    $mail->Password   = $_ENV['MAIL_PASSWORD'];
                    $mail->SMTPSecure = $_ENV['MAIL_ENCRYPTION'];
                    $mail->Port       = $_ENV['MAIL_PORT'];

                    // Recipients
                    $mail->setFrom('opulencehaven25@gmail.com', 'Opulence Haven');
                    $mail->addAddress($email);

                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = "Payment Confirmation - Order #{$order_id}";
                    $mail->Body = "
                    <html>
                    <head>
                        <style>
                            body {
                                font-family: Arial, sans-serif;
                                line-height: 1.6;
                                background-color: #f9f9f9;
                                color: #333;
                            }
                            .content {
                                background: #ffffff;
                                padding: 20px;
                                border-radius: 8px;
                                max-width: 600px;
                                margin: auto;
                                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                            }
                            .footer {
                                padding-top: 20px;
                                border-top: 1px solid #eee;
                                font-size: 0.9em;
                                color: #777;
                                text-align: center;
                            }
                            h2 {
                                color: #444;
                            }
                            table {
                                border-collapse: collapse;
                                width: 100%;
                                margin-top: 20px;
                            }
                            th, td {
                                padding: 12px;
                                border: 1px solid #ddd;
                                text-align: left;
                            }
                            th {
                                background-color: #f4f4f4;
                                font-weight: bold;
                                color: #444;
                            }
                        </style>
                    </head>
                    <body>
                        <div class='content'>
                            <h2>Payment Confirmation</h2>
                            <p>Hello {$username},</p>
                            <p>Thank you for your purchase! Your payment for <strong>Order #{$order_id}</strong> was successful.</p>
                            <h3>Order Details</h3>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Size</th>
                                        <th>Quantity</th>
                                        <th>Unit Price</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {$items_html}
                                </tbody>
                            </table>
                            <p style='margin-top: 20px;'>
                                <strong>Subtotal:</strong> $" . number_format($subtotal, 2) . "<br>
                                <strong>Tax (10%):</strong> $" . number_format($tax, 2) . "<br>
                                <strong>Delivery Fee:</strong> $" . number_format($deliveryFee, 2) . "<br>
                                <strong>Total:</strong> $" . number_format($total_price, 2) . "
                            </p>
                            <p>Your order is now being processed. We will notify you once it ships.</p>
                            <p>Thank you for shopping with us!</p>
                        </div>
                        <div class='footer'>
                            <p>&copy; " . date('Y') . " Opulence Haven. All rights reserved.</p>
                        </div>
                    </body>
                    </html>
                    ";

                    $mail->AltBody = "Hello {$username},\n\nThank you for your purchase! Your payment for Order #{$order_id} was successful.\n\nOrder Details:\n\n" . strip_tags($items_html) . "\n\nSubtotal: $" . number_format($subtotal, 2) . "\nTax: $" . number_format($tax, 2) . "\nDelivery Fee: $" . number_format($deliveryFee, 2) . "\nTotal: $" . number_format($total_price, 2) . "\n\nYour order is now being processed. Thank you for shopping with us!";

                    $mail->send();
                } catch (Exception $e) {
                    error_log("Payment confirmation email failed: {$mail->ErrorInfo}");
                }
            }

            // Redirect after success
            header("Location: modify_order.php?order_id=" . $order_id . "&payment=success");
            exit();
        } else {
            die("Error updating order status and total: " . $connect->error);
        }
    } else {
        // Order doesn't belong to user or doesn't exist
        die("Invalid order");
    }
} else {
    // Invalid access to this page
    header("Location: modify_order.php?order_id=" . $order_id . "&payment=cancel");
    exit();
}
