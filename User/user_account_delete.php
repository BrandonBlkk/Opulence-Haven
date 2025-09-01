<?php
session_start();
require_once('../config/db_connection.php');
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (isset($_SESSION['UserID'])) {
    $user_id = $_SESSION['UserID'];

    // Get user details before deletion
    $userQuery = "SELECT * FROM usertb WHERE UserID = '$user_id'";
    $userResult = $connect->query($userQuery);

    if ($userResult && $userResult->num_rows > 0) {
        $user = $userResult->fetch_assoc();

        // FIX: Use correct column names as per your database schema
        $email = $user['UserEmail'];
        $username = $user['UserName'];

        // Delete the user account
        $accountDeleteQuery = "DELETE FROM usertb WHERE UserID = '$user_id'";
        if (!$connect->query($accountDeleteQuery)) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to delete account.']);
            exit;
        }

        // Send account deletion email
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
            $mail->Subject = 'Your Opulence Haven Account Has Been Deleted';
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
                        .warning-box {
                            background-color: #FFF5F2; 
                            border: 1px solid #F5A89A; 
                            border-radius: 3px;
                            padding: 12px;
                            margin: 16px 0;
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
                            <h1>Your account is being deleted</h1>
                            
                            <p>Hi $username,</p>
                            
                            <p>We're reaching out to let you know that your Opulence Haven account has been successfully deleted. You will no longer be able to access your account, and your data will be permanently deleted from our systems.</p>
                            
                            <div class='warning-box'>
                                <strong>Important:</strong> All your subscriptions and bookings have been cancelled. This action cannot be undone.
                            </div>
                            
                            <p>Account details that were deleted:</p>
                            
                            <div class='account-details'>
                                <ul>
                                    <li><strong>Username:</strong> $username</li>
                                    <li><strong>Email:</strong> $email</li>
                                </ul>
                            </div>
                            
                            <p>If this was a mistake or if you need assistance, please contact our support team immediately.</p>
                            
                            <p>Thanks,<br>The Opulence Haven Team</p>
                        </div>
                        
                        <div class='footer'>
                            <p>You are receiving this email because this is an important message regarding your account. You are not allowed to unsubscribe from this type of message.</p>                     
                            <p>Copyright © " . date('Y') . " Opulence Haven. All rights reserved.</p>
                        </div>
                    </div>
                </body>
                </html>
            ";

            $mail->AltBody = "Dear $username,\n\nYour account associated with $email has been deleted.\n\nIf this was not you, please contact support.\n\n© " . date('Y') . " Opulence Haven.";

            $mail->send();
        } catch (Exception $e) {
            // Log email error but continue account deletion
            error_log("Account deletion email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }
    }
}

// Destroy the session
session_unset();
session_destroy();

http_response_code(200);
echo json_encode(['success' => true, 'message' => 'Account deleted and email sent if possible.']);
exit();
