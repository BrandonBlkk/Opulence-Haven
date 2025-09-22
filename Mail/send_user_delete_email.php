<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
require_once('../config/db_connection.php');

if (isset($_GET['email']) && isset($_GET['name'])) {
    $userEmail = $_GET['email'];
    $userName  = $_GET['name'] ?: "User";

    $response = ['success' => false];

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

        // Recipients
        $mail->setFrom('opulencehaven25@gmail.com', 'Opulence Haven');
        $mail->addAddress($userEmail, $userName);

        // Email Content
        $mail->isHTML(true);
        $mail->Subject = "Account Deletion Notice";

        $mail->Body = '
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #172B4D; background-color: #F4F5F7; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 0 auto; background-color: #FFFFFF; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
                .header { padding: 20px; border-bottom: 1px solid #DFE1E6; }
                .logo { color: #FBA311; font-weight: bold; font-size: 20px; }
                .content { padding: 20px; }
                .content h2 { font-size: 20px; margin-top: 0; color: #172B4D; }
                .footer { padding: 10px; text-align: center; font-size: 12px; color: #5E6C84; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header"><div class="logo">OPULENCE HAVEN</div></div>
                <div class="content">
                    <h2>Dear ' . htmlspecialchars($userName) . ',</h2>
                    <p>We regret to inform you that your account with <strong>Opulence Haven</strong> has been permanently deleted by an administrator.</p>
                    <p>All personal information and related data linked to your account have been removed from our system.</p>
                    <p>If you believe this was done in error or have any questions, please <a href="mailto:info@opulencehaven.com">contact our support team</a>.</p>
                    <p>Thank you for being a part of <strong>Opulence Haven</strong>.</p>
                    <p>Warm regards,<br>The Opulence Haven Team</p>
                </div>
                <div class="footer">
                    <p>&copy; ' . date('Y') . ' Opulence Haven. All rights reserved.</p>
                    <p><a href="http://localhost/OpulenceHaven/User/home_page.php">Visit our website</a></p>
                </div>
            </div>
        </body>
        </html>';

        $mail->send();
        $response['success'] = true;
    } catch (Exception $e) {
        $response['success'] = false;
        $response['error'] = $mail->ErrorInfo;
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
