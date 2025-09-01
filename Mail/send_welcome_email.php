<?php
session_start();
require_once('../config/db_connection.php');
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

if (!isset($_SESSION['email_data'])) {
    echo json_encode(['success' => false, 'message' => 'No email data found']);
    exit;
}

$email = $_SESSION['email_data']['email'];
$username = $_SESSION['email_data']['username'];

try {
    $mail = new PHPMailer(true);

    // Server settings
    $mailConfig = require __DIR__ . '/../config/mail.php';

    $mail->isSMTP();
    $mail->Host       = $mailConfig['host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $mailConfig['username'];
    $mail->Password   = $mailConfig['password'];
    $mail->SMTPSecure = $mailConfig['encryption'];
    $mail->Port       = $mailConfig['port'];

    // Recipients
    $mail->setFrom('opulencehaven25@gmail.com', 'Opulence Haven');
    $mail->addAddress($email);

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Welcome to Opulence Haven!';
    $mail->Body    = "
    <html>
    <head>
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
            h1 {
                color: #172B4D;
                font-size: 24px;
                margin-top: 0;
            }
            p {
                margin-bottom: 16px;
            }
            .footer {
                padding: 20px;
                text-align: center;
                font-size: 12px;
                color: #5E6C84;
                border-top: 1px solid #DFE1E6;
            }
            .account-details {
                background-color: #F4F5F7;
                border-radius: 3px;
                padding: 12px;
                margin: 16px 0;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <div class='logo'>OPULENCE HAVEN</div>
            </div>
            
            <div class='content'>
                <h1>Welcome to Opulence Haven, $username!</h1>
                
                <p>Thank you for creating an account with us. We're excited to have you as part of our community.</p>
                
                <div>
                    <strong>Here are your account details:</strong>
                    <div class='account-details'>
                        <ul>
                            <li><strong>Username:</strong> $username</li>
                            <li><strong>Email:</strong> $email</li>
                        </ul>
                    </div>
                </div>
                
                <p>You can now enjoy all the benefits of being a member, including:</p>
                <div>
                    <ul>
                        <li>Exclusive room booking offers</li>
                        <li>Access to your bookings from any device</li>
                        <li>Special member discounts</li>
                    </ul>
                </div>
                
                <p>If you have any questions, please don't hesitate to contact our support team.</p>
                
                <p>Thanks,<br>The Opulence Haven Team</p>
            </div>
            
            <div class='footer'>
                <p>You are receiving this email because you created an account with Opulence Haven.</p>
                <p>Copyright © " . date('Y') . " Opulence Haven. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";

    $mail->AltBody = "Welcome to Opulence Haven, $username!\n\nThank you for creating an account with us. We're excited to have you as part of our community.\n\nAccount details:\nUsername: $username\nEmail: $email\n\nYou can now enjoy all the benefits of being a member. If you have any questions, please contact our support team.\n\n© " . date('Y') . " Opulence Haven. All rights reserved.";

    $mail->send();

    // Clear the session data
    unset($_SESSION['email_data']);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
    echo json_encode(['success' => false, 'message' => 'Failed to send email']);
}
