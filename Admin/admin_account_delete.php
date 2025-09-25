<?php
session_start();
require_once('../config/db_connection.php');
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (isset($_SESSION['AdminID'])) {
    $admin_id = $_SESSION['AdminID'];

    // Get user details before deletion
    $adminQuery = "SELECT * FROM admintb WHERE AdminID = '$admin_id'";
    $adminResult = $connect->query($adminQuery);

    if ($adminResult && $adminResult->num_rows > 0) {
        $user = $adminResult->fetch_assoc();

        $email = $user['AdminEmail'];
        $username = $user['UserName'];

        // Delete the user account
        $accountDeleteQuery = "DELETE FROM admintb WHERE AdminID = '$admin_id'";
        if (!$connect->query($accountDeleteQuery)) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to delete account.']);
            exit;
        }

        // Send account deletion email
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
            $mail->addAddress($email, $username);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Opulence Haven Admin Account Deletion Confirmation';
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
                            border-radius: 5px;
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
                        h1 {
                            color: #172B4D;
                            font-size: 22px;
                            margin-top: 0;
                        }
                        p {
                            margin-bottom: 16px;
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
                        .footer {
                            padding: 20px;
                            text-align: center;
                            font-size: 12px;
                            color: #5E6C84;
                            border-top: 1px solid #DFE1E6;
                        }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <div class='logo'>OPULENCE HAVEN</div>
                        </div>
                        
                        <div class='content'>
                            <h1>Admin Account Deleted</h1>
                            
                            <p>Hi <strong>$username</strong>,</p>
                            
                            <p>This is a confirmation that your admin account for <strong>Opulence Haven</strong> has been permanently deleted. 
                            You will no longer be able to sign in or manage the system using this account.</p>
                            
                            <div class='warning-box'>
                                <strong>Important:</strong> All admin privileges and access rights associated with this account have been revoked. This action cannot be undone.
                            </div>
                            
                            <p>Deleted account details:</p>
                            
                            <div class='account-details'>
                                <ul>
                                    <li><strong>Username:</strong> $username</li>
                                    <li><strong>Email:</strong> $email</li>
                                </ul>
                            </div>
                            
                            <p>If this deletion was not authorized, please contact <a href='mailto:support@opulencehaven.com'>support@opulencehaven.com</a> immediately.</p>
                            
                            <p>Thank you,<br>The Opulence Haven Team</p>
                        </div>
                        
                        <div class='footer'>
                            <p>You are receiving this message because it concerns your admin account status. This type of message cannot be unsubscribed from.</p>                     
                            <p>© " . date('Y') . " Opulence Haven. All rights reserved.</p>
                        </div>
                    </div>
                </body>
                </html>
            ";

            $mail->AltBody = "Hi $username,\n\nYour Opulence Haven admin account has been permanently deleted.\n\nIf this was not authorized, contact support immediately.\n\n© " . date('Y') . " Opulence Haven.";

            $mail->send();
        } catch (Exception $e) {
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
