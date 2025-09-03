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

$email = $_SESSION['email_data']['email'] ?? '';
$username = $_SESSION['email_data']['username'] ?? '';
$plain_token = $_SESSION['email_data']['plain_token'] ?? '';
$role = $_SESSION['email_data']['role'] ?? '';

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
    $mail->Subject = 'Confirm Your Admin Account';
    $mail->Body = "
    <html>
    <head>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
            }

            .content {
                padding: 20px;

            }

            .footer {
                padding-top: 20px;
                border-top: 1px solid #eee;
                font-size: 0.9em;
                color: #777;
            }

            p {
                margin: 0 0 15px 0;
            }
        </style>
        </head>

        <body>
            <div class='content'>
                <p>Hello {$username},</p>
                <p>You've requested an admin account for Opulence Haven.</p>

                <p>Here are your account details:</p>
                <ul>
                    <li><strong>Username:</strong> {$username}</li>
                    <li><strong>Email:</strong> {$email}</li>
                    <li><strong>Role:</strong> {$role}</li>
                </ul>

                <p>To complete registration, click the following link:</p>

                <p>
                    <a href='http://localhost/OpulenceHaven/Admin/confirm_success.php?token=" . urlencode($plain_token) . "'>
                        http://localhost/OpulenceHaven/Admin/confirm_success.php?token=" . urlencode($plain_token) . "
                    </a>
                </p>

                <p>If you didn't request this, please ignore this email.</p>
                <p><strong>Note:</strong> This link expires in 24 hours.</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " Opulence Haven. All rights reserved.</p>
            </div>
        </body>
        </html>
    ";

    $mail->AltBody = "Confirm Your Admin Account\n\nHello $username,\n\nYou've requested an admin account for Opulence Haven. To complete registration, click the following link:\n\nhttps://opulencehaven.com/confirm-admin?token=$token\n\nIf you didn't request this, please ignore this email.\n\nNote: This link expires in 24 hours.\n\n© " . date('Y') . " Opulence Haven. All rights reserved.";

    $mail->send();

    // Clear the session data
    unset($_SESSION['email_data']);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
    echo json_encode(['success' => false, 'message' => 'Failed to send email']);
}
