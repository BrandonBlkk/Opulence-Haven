<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
require_once('../config/db_connection.php');

if (isset($_GET['id'])) {
    $orderId = mysqli_real_escape_string($connect, $_GET['id']);
    $response = ['success' => false];

    // Get order and user info
    $query = "
        SELECT o.OrderID, u.UserEmail, u.UserName
        FROM ordertb o
        JOIN usertb u ON o.UserID = u.UserID
        WHERE o.OrderID = '$orderId'
        LIMIT 1
    ";
    $result = $connect->query($query);

    if ($result && $result->num_rows > 0) {
        $order = $result->fetch_assoc();
        $userEmail = $order['UserEmail'];
        $userName  = $order['UserName'] ?? "Customer";

        // Get return and product details
        $returnDetailsQuery = "
            SELECT 
                r.ReturnID,
                r.ProductID,
                r.ActionType,
                r.ReturnQuantity,
                r.Status,
                r.RequestDate,
                r.Remarks,
                p.Title,
                p.Price,
                p.DiscountPrice,
                pt.ProductType
            FROM returntb r
            JOIN producttb p ON r.ProductID = p.ProductID
            LEFT JOIN producttypetb pt ON p.ProductTypeID = pt.ProductTypeID
            WHERE r.OrderID = '$orderId'
            ORDER BY r.RequestDate DESC
        ";

        $returnResult = $connect->query($returnDetailsQuery);

        $returnDetailsHTML = "";
        if ($returnResult && $returnResult->num_rows > 0) {
            $returnDetailsHTML .= '
            <h3 style="color:#172B4D;">Return Details</h3>
            <table style="width:100%; border-collapse:collapse; margin-top:10px;">
                <thead>
                    <tr style="background-color:#F4F5F7;">
                        <th style="border:1px solid #DFE1E6; padding:8px; text-align:left;">Product</th>
                        <th style="border:1px solid #DFE1E6; padding:8px; text-align:left;">Type</th>
                        <th style="border:1px solid #DFE1E6; padding:8px; text-align:left;">Action</th>
                        <th style="border:1px solid #DFE1E6; padding:8px; text-align:left;">Quantity</th>
                        <th style="border:1px solid #DFE1E6; padding:8px; text-align:left;">Status</th>
                        <th style="border:1px solid #DFE1E6; padding:8px; text-align:left;">Remarks</th>
                    </tr>
                </thead>
                <tbody>
            ';
            while ($row = $returnResult->fetch_assoc()) {
                $productTitle = htmlspecialchars($row['Title'] ?? 'N/A');
                $productType = htmlspecialchars($row['ProductType'] ?? 'N/A');
                $actionType = htmlspecialchars($row['ActionType']);
                $quantity = htmlspecialchars($row['ReturnQuantity']);
                $status = htmlspecialchars($row['Status']);
                $remarks = htmlspecialchars($row['Remarks']);

                $returnDetailsHTML .= "
                    <tr>
                        <td style='border:1px solid #DFE1E6; padding:8px;'>$productTitle</td>
                        <td style='border:1px solid #DFE1E6; padding:8px;'>$productType</td>
                        <td style='border:1px solid #DFE1E6; padding:8px;'>$actionType</td>
                        <td style='border:1px solid #DFE1E6; padding:8px;'>$quantity</td>
                        <td style='border:1px solid #DFE1E6; padding:8px;'>$status</td>
                        <td style='border:1px solid #DFE1E6; padding:8px;'>$remarks</td>
                    </tr>
                ";
            }
            $returnDetailsHTML .= "</tbody></table>";
        } else {
            $returnDetailsHTML .= "<p>No return details found for this order.</p>";
        }

        try {
            $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../config');
            $dotenv->load();

            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = $_ENV['MAIL_HOST'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['MAIL_USERNAME'];
            $mail->Password   = $_ENV['MAIL_PASSWORD'];
            $mail->SMTPSecure = $_ENV['MAIL_ENCRYPTION'];
            $mail->Port       = $_ENV['MAIL_PORT'];

            $mail->setFrom('opulencehaven25@gmail.com', 'Opulence Haven');
            $mail->addAddress($userEmail, $userName);

            $mail->isHTML(true);
            $mail->Subject = "Return Shipping Instructions for Order #{$orderId}";
            $mail->Body = '
            <html>
            <head>
                <meta charset="UTF-8">
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; background-color: #F4F5F7; color: #172B4D; }
                    .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
                    .header { padding: 20px; border-bottom: 1px solid #DFE1E6; }
                    .logo { color: #FBA311; font-weight: bold; font-size: 20px; }
                    .content { padding: 20px; }
                    .footer { padding: 10px; text-align: center; font-size: 12px; color: #5E6C84; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <div class="logo">OPULENCE HAVEN</div>
                    </div>
                    <div class="content">
                        <h2>Dear ' . htmlspecialchars($userName) . ',</h2>
                        <p>We have received your return request for order <strong>#' . htmlspecialchars($orderId) . '</strong>.</p>
                        ' . $returnDetailsHTML . '
                        <p>Please send your item(s) back to the following return address:</p>
                        <p style="font-weight:bold; color:#172B4D;">
                            Opulence Haven Returns Department<br>
                            No. 123, Merchant Road<br>
                            Yangon, Myanmar<br>
                            Phone: +95 9 123 456 789<br>
                            Email: returns@opulencehaven.com
                        </p>
                        <p>Once your return is received and inspected, our team will process your ' . htmlspecialchars(ucfirst($_GET['type'] ?? 'request')) . ' promptly.</p>
                        <p>Thank you for choosing <strong>Opulence Haven</strong>. We appreciate your trust in us.</p>
                        <p>Warm regards,<br>The Opulence Haven Team</p>
                    </div>
                    <div class="footer">
                        <p>&copy; ' . date('Y') . ' Opulence Haven. All rights reserved.</p>
                        <p><a href="http://localhost/OpulenceHaven/User/home_page.php">Visit our website</a></p>
                    </div>
                </div>
            </body>
            </html>
            ';

            $mail->send();
            $response['success'] = true;
        } catch (Exception $e) {
            $response['success'] = false;
            $response['error'] = $mail->ErrorInfo;
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
