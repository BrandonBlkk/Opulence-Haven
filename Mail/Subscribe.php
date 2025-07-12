<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php'; // Load Composer's autoloader

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Validate the email
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Create a new PHPMailer instance
        $mail = new PHPMailer(true);

        try {
            $mailConfig = require __DIR__ . '/../config/mail.php';

            $mail->isSMTP();
            $mail->Host       = $mailConfig['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $mailConfig['username'];
            $mail->Password   = $mailConfig['password'];
            $mail->SMTPSecure = $mailConfig['encryption'];
            $mail->Port       = $mailConfig['port'];


            // Recipients
            $mail->setFrom('opulencehaven25@gmail.com', 'Opulence Haven'); // Sender
            $mail->addAddress($email); // Recipient

            // Content
            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = 'Thank You for Subscribing to Opulence Haven Newsletter!';

            // Enhanced email body
            $mail->Body = '
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; color: #333; }
                        .header { background-color: #f4f4f4; padding: 20px; text-align: center; }
                        .content { padding: 20px; }
                        .footer { background-color: #f4f4f4; padding: 10px; text-align: center; font-size: 12px; }
                        a { color: #007BFF; text-decoration: none; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>Welcome to Opulence Haven!</h1>
                    </div>
                    <div class="content">
                        <p>Dear Subscriber,</p>
                        <p>Thank you for subscribing to our newsletter. We are thrilled to have you as part of our community!</p>
                        <p>At Opulence Haven, we offer luxurious accommodations, world-class amenities, and exceptional service to make your stay unforgettable. Here are some highlights:</p>
                        <ul>
                            <li><strong>Exclusive Offers:</strong> Enjoy special discounts and packages available only to our subscribers.</li>
                            <li><strong>Upcoming Events:</strong> Join us for exciting events, including live music, gourmet dinners, and wellness workshops.</li>
                            <li><strong>Loyalty Program:</strong> Earn points with every stay and redeem them for free nights, upgrades, and more.</li>
                        </ul>
                        <p>Stay tuned for our latest updates, promotions, and insider tips to make the most of your stay with us.</p>
                        <p>If you have any questions or need assistance, feel free to <a href="mailto:info@opulencehaven.com">contact us</a>.</p>
                        <p>Warm regards,</p>
                        <p>The Opulence Haven Team</p>
                    </div>
                    <div class="footer">
                        <p>&copy; 2025 Opulence Haven. All rights reserved.</p>
                        <p><a href="http://localhost/OpulenceHaven/User/home_page.php">Visit our website</a></p>
                    </div>
                </body>
                </html>
            ';

            // Send the email
            $mail->send();
            // Redirect back to the previous page
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit(); // Ensure no further code is executed
        } catch (Exception $e) {
            echo "There was an error sending the email. Please try again later. Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "Invalid email address. Please enter a valid email address.";
    }
} else {
    echo "Invalid request method.";
}
