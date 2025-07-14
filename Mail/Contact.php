<?php
session_start();
require_once('../config/db_connection.php');
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

if (!isset($_SESSION['contact_data'])) {
    echo json_encode(['success' => false, 'message' => 'No email data found']);
    exit;
}

$email = $_SESSION['contact_data']['useremail'];
$username = $_SESSION['contact_data']['username'];
$response = $_SESSION['contact_data']['response'];

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
    $mail->Subject = 'Response to your inquiry';
    $mail->Body    = "
        <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background-color: #f8f8f8; padding: 20px; text-align: center; }
                    .content { padding: 20px; }
                    .response { background-color: #f9f9f9; padding: 15px; border-left: 4px solid #f0ad4e; margin: 15px 0; }
                    .footer { margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; font-size: 0.9em; color: #777; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>Your Inquiry Response</h2>
                    </div>
                    <div class='content'>
                        <p>Dear $username,</p>
                        <p>Thank you for contacting us. Here is our response to your inquiry:</p>
                        
                        <div class='response'>
                            <p><strong>Your original message:</strong></p>
                            <p>{$contact['ContactMessage']}</p>
                            <p><strong>Our response:</strong></p>
                            <p>{$response}</p>
                        </div>
                        
                        <p>If you have any further questions, please don't hesitate to contact us again.</p>
                    </div>
                    <div class='footer'>
                        <p>Best regards,</p>
                        <p>Your Company Team</p>
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
