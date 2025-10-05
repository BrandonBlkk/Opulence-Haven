<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
require_once('../config/db_connection.php');

if (isset($_GET['id'])) {
    $reservationId = mysqli_real_escape_string($connect, $_GET['id']);
    $response = ['success' => false];

    // Fetch user and reservation details
    $query = "
        SELECT r.ReservationID, rd.CheckInDate, rd.CheckOutDate, r.TotalPrice, u.UserEmail, u.UserName
        FROM reservationtb r
        JOIN reservationdetailtb rd ON r.ReservationID = rd.ReservationID
        JOIN usertb u ON r.UserID = u.UserID
        WHERE r.ReservationID = '$reservationId'
        LIMIT 1
    ";
    $result = $connect->query($query);

    if ($result && $result->num_rows > 0) {
        $reservation = $result->fetch_assoc();
        $userEmail = $reservation['UserEmail'];
        $userName  = $reservation['UserName'] ?? "Customer";
        $totalPrice = number_format((float)$reservation['TotalPrice'], 2);
        $checkInDate = date('F j, Y', strtotime($reservation['CheckInDate']));
        $checkOutDate = date('F j, Y', strtotime($reservation['CheckOutDate']));

        try {
            // Load .env
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

            // Recipients
            $mail->setFrom('opulencehaven25@gmail.com', 'Opulence Haven');
            $mail->addAddress($userEmail, $userName);

            // Email Content
            $mail->isHTML(true);
            $mail->Subject = "Reservation #{$reservationId} Cancellation Confirmation!";
            $mail->Body = '
            <html>
            <head>
                <meta charset="UTF-8">
                <style>
                    body { 
                        font-family: Arial, sans-serif; 
                        line-height: 1.6; 
                        color: #172B4D; 
                        margin: 0; 
                        padding: 0; 
                        background-color: #F4F5F7; 
                    }
                    .container {
                        max-width: 600px; 
                        margin: 0 auto; 
                        background-color: #FFFFFF; 
                        border-radius: 8px; 
                        overflow: hidden; 
                        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                    }
                    .header {
                        padding: 20px;
                        border-bottom: 1px solid #DFE1E6;
                    }
                    .logo {
                        color: #FBA311;
                        font-weight: bold;
                        font-size: 20px;
                    }
                    .content {
                        padding: 20px;
                    }
                    .content h2 {
                        font-size: 20px; 
                        margin-top: 0; 
                        color: #172B4D;
                    }
                    .footer {
                        padding: 10px; 
                        text-align: center; 
                        font-size: 12px; 
                        color: #5E6C84; 
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <div class="logo">OPULENCE HAVEN</div>
                    </div>
                    <div class="content">
                        <h2>Dear ' . htmlspecialchars($userName) . ',</h2>
                        <p>Your reservation <strong>#' . htmlspecialchars($reservationId) . '</strong> has been successfully cancelled.</p>
                        <p><strong>Check-in Date:</strong> ' . $checkInDate . '<br>
                           <strong>Check-out Date:</strong> ' . $checkOutDate . '</p>
                        <p><strong>Total Amount Paid:</strong> $' . $totalPrice . '</p>
                        <p>The refund (if applicable) will be processed according to our cancellation policy. Thank you for choosing <strong>Opulence Haven</strong>.</p>
                        <p>If you have any questions, feel free to <a href="mailto:info@opulencehaven.com">contact us</a>.</p>
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
