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
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .header { background-color: #f8f1e5; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .footer { background-color: #f8f1e5; padding: 10px; text-align: center; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>Welcome to Opulence Haven, $username!</h1>
            </div>
            <div class='content'>
                <p>Thank you for creating an account with us. We're excited to have you as part of our community.</p>
                <p>Here are your account details:</p>
                <ul>
                    <li><strong>Username:</strong> $username</li>
                    <li><strong>Email:</strong> $email</li>
                </ul>
                <p>You can now enjoy all the benefits of being a member, including:</p>
                <ul>
                    <li>Exclusive room booking offers</li>
                    <li>Access to your bookings from any device</li>
                    <li>Special member discounts</li>
                </ul>
                <p>If you have any questions, please don't hesitate to contact our support team.</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " Opulence Haven. All rights reserved.</p>
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
