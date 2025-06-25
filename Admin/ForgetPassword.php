<?php
session_start();
include('../config/dbConnection.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Timezone 
date_default_timezone_set('Asia/Yangon');

$alertMessage = '';
// $resetPasswordSuccess = false;
$response = ['success' => false, 'message' => ''];

if (isset($_POST['reset'])) {
    $email = $_POST['email'];

    // Check if email exists
    $checkQuery = "SELECT * FROM admintb WHERE AdminEmail = '$email'";
    $result = $connect->query($checkQuery);

    if (empty($_POST["email"])) {
        $response['message'] = "Enter your email to receive a password reset email";
    } else if ($result->num_rows > 0) {
        $token = bin2hex(random_bytes(16));
        $otp = rand(100000, 999999);
        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Store the token and expiry in the database
        $updateQuery = "UPDATE admintb SET OTP = '$otp', Token = '$token', TokenExpiry = '$expiry' WHERE AdminEmail = '$email'";
        $connect->query($updateQuery);

        $mail = new PHPMailer(true);
        try {
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
            $mail->Subject = 'Reset Your Password - Opulence Haven';
            $mail->Body = "
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset='UTF-8'>
                    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    <title>Password Reset</title>
                    <style>
                        body {
                            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                            line-height: 1.6;
                            color: #333333;
                            max-width: 600px;
                            margin: 0 auto;
                            padding: 20px;
                        }
                        .content {
                            background-color: #ffffff;
                            padding: 30px;
                            border-radius: 8px;
                            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
                        }
                        .button {
                            display: inline-block;
                            padding: 12px 24px;
                            background-color: #F59E0B;
                            color: #ffffff !important;
                            text-decoration: none;
                            border-radius: 6px;
                            font-weight: 600;
                            margin: 20px 0;
                        }
                        .footer {
                            margin-top: 30px;
                            padding-top: 20px;
                            border-top: 1px solid #e5e7eb;
                            font-size: 14px;
                            color: #6b7280;
                            text-align: center;
                        }
                        .expiry-note {
                            background-color: #f9fafb;
                            padding: 12px;
                            border-radius: 6px;
                            font-size: 14px;
                            margin: 20px 0;
                        }
                        .support {
                            margin-top: 15px;
                            font-size: 14px;
                        }
                    </style>
                </head>
                <body>    
                    <div class='content'>
                        <h2 style='margin-top: 0; color: #111827;'>Password Reset Request</h2>
                        
                        <p>Hello,</p>
                        
                        <p>We received a request to reset the password for your Opulence Haven account associated with this email address.</p>

                        <p> Your verification code: <strong>$otp</strong> </p>
                        
                        <div style='text-align: center;'>
                            <a href='http://localhost/OpulenceHaven/Admin/ResetPassword.php?token=$token' class='button'>
                                Reset Your Password
                            </a>
                        </div>
                        
                        <div class='expiry-note'>
                            <strong>Important:</strong> This link will expire in 1 hour or after one use for security reasons.
                        </div>
                        
                        <p>If you didn't request this password reset, please ignore this email or contact our support team if you have concerns about your account security.</p>
                        
                        <div class='support'>
                            <p>Need help? <a href='mailto:support@opulencehaven.com' style='color: #4F46E5;'>Contact our support team</a></p>
                        </div>
                    </div>
                    
                    <div class='footer'>
                        <p>© " . date('Y') . " Opulence Haven. All rights reserved.</p>
                        <p>
                            <a href='http://localhost/OpulenceHaven/Policies/PrivacyPolicy.php' style='color: #6b7280; text-decoration: none; margin: 0 10px;'>Privacy Policy</a>
                            <a href='http://localhost/OpulenceHaven/Policies/TermsOfUse.php' style='color: #6b7280; text-decoration: none; margin: 0 10px;'>Terms of Service</a>
                        </p>
                        <p>Opulence Haven, 459 Pyay Road, Kamayut Township , 11041 Yangon, Myanmar</p>
                    </div>
                </body>
                </html>
                ";

            $mail->send();
            $response['success'] = true;
        } catch (Exception $e) {
            $response['message'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        $response['message'] = "Invalid email address. Please enter a valid email address";
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opulence Haven</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../CSS/output.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../CSS/input.css?v=<?php echo time(); ?>">
</head>

<body class="relative min-w-[380px]">
    <!DOCTYPE html>
    <html lang="en">

    <main class="flex justify-center items-center min-h-screen">
        <div class="p-8 w-full max-w-md">
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Forgot Password</h1>
                <p class="text-gray-600">Enter your email to reset your password and regain access.</p>
            </div>

            <form id="resetPasswordForm" action="<?php $_SERVER["PHP_SELF"] ?>" method="POST" class="space-y-4">
                <!-- Email Input -->
                <div class="relative">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300"
                        placeholder="Enter your email">
                </div>

                <input type="hidden" name="reset" value="1">

                <!-- Submit Button -->
                <button
                    type="submit" name="reset"
                    class="w-full bg-amber-500 text-white font-semibold py-2 rounded-md hover:bg-amber-600 transition duration-300 select-none">
                    Send Reset Email
                </button>
            </form>
            <div class="mt-6 text-center text-sm text-gray-500">
                <p>If the email you entered is linked to an account, you will receive a password reset email shortly.</p>
            </div>
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">Remembered your password?
                    <a href="AdminSignIn.php" class="text-amber-500 hover:underline">Back to Sign In</a>
                </p>
            </div>
        </div>
    </main>

    <!-- Loader -->
    <?php
    include('../includes/Loader.php');
    include('../includes/Alert.php');
    ?>

    <script src="//unpkg.com/alpinejs" defer></script>
    <script type="module" src="../JS/adminAuth.js"></script>
</body>

</html>