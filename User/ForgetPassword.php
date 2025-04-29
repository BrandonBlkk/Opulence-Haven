<?php
session_start();
include('../config/dbConnection.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (isset($_POST['reset'])) {
    $email = $_POST['email'];

    // Check if email exists
    $checkQuery = "SELECT * FROM usertb WHERE UserEmail = '$email'";
    $result = $connect->query($checkQuery);

    if ($result->num_rows > 0) {
        $token = bin2hex(random_bytes(16));

        // Store the token in the database
        $updateQuery = "UPDATE usertb SET Token = '$token' WHERE UserEmail = '$email'";
        $connect->query($updateQuery);

        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'opulencehaven25@gmail.com';
            $mail->Password   = 'xzjd xttt bhwd gilx';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

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
            color: #333333;
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
            background-color: #4F46E5;
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
        
        <div style='text-align: center;'>
            <a href='http://localhost/OpulenceHaven/User/ResetPassword.php?token=$token' class='button'>
                Reset Your Password
            </a>
        </div>
        
        <div class='expiry-note'>
            <strong>Important:</strong> This link will expire in 24 hours or after one use for security reasons.
        </div>
        
        <p>If you didn't request this password reset, please ignore this email or contact our support team if you have concerns about your account security.</p>
        
        <div class='support'>
            <p>Need help? <a href='mailto:support@opulencehaven.com' style='color: #4F46E5;'>Contact our support team</a></p>
        </div>
    </div>
    
    <div class='footer'>
        <p>© " . date('Y') . " Opulence Haven. All rights reserved.</p>
        <p>
            <a href='https://yourdomain.com/privacy' style='color: #6b7280; text-decoration: none; margin: 0 10px;'>Privacy Policy</a>
            <a href='https://yourdomain.com/terms' style='color: #6b7280; text-decoration: none; margin: 0 10px;'>Terms of Service</a>
        </p>
        <p>Opulence Haven, 123 Luxury Lane, Suite 100, Prestige City</p>
    </div>
</body>
</html>
";

            $mail->send();
            echo "<script>alert('Password reset link has been sent to your email.')</script>";
        } catch (Exception $e) {
            echo "<script>alert('Message could not be sent. Mailer Error: {$mail->ErrorInfo}')</script>";
        }
    } else {
        echo "<script>alert('Invalid email address. Please enter a valid email address.')</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opulence Haven</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="CSS/output.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="CSS/input.css?v=<?php echo time(); ?>">
</head>

<body class="relative">
    <!DOCTYPE html>
    <html lang="en">

    <main class="flex justify-center items-center min-h-screen">
        <div class="p-8 w-full max-w-md">
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Forgot Password</h1>
                <p class="text-gray-600">Enter your email to reset your password and regain access.</p>
            </div>

            <form action="<?php $_SERVER["PHP_SELF"] ?>" method="POST" class="space-y-6">
                <!-- Email Input -->
                <div class="relative">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300"
                        placeholder="Enter your email">
                </div>

                <!-- Submit Button -->
                <button
                    type="submit" name="reset"
                    class="w-full bg-amber-500 text-white font-semibold py-3 rounded-lg hover:bg-amber-600 transition duration-300">
                    Send Reset Link
                </button>
            </form>
            <div class="mt-6 text-center text-sm text-gray-500">
                <p>If the email you entered is linked to an account, you will receive a password reset link shortly.</p>
            </div>
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">Remembered your password?
                    <a href="UserSignIn.php" class="text-amber-500 hover:underline">Back to Login</a>
                </p>
            </div>
        </div>
    </main>

    <script src="//unpkg.com/alpinejs" defer></script>
    <script src="./JS/index.js"></script>
</body>

</html>