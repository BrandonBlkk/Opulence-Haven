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
            $mail->setFrom('opulencehaven25@gmail.com', 'Opulence Haven'); // Sender
            $mail->addAddress($email); // Recipient

            // Content
            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = 'Thank You for Subscribing to Opulence Haven Newsletter!';

            // Enhanced email body (delete-email style)
            $mail->Body = '
            <html>
            <head>
                <meta charset="UTF-8">
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
                        border-radius: 8px; 
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
                    .highlight {
                        background-color: #F4F5F7;
                        border-radius: 3px;
                        padding: 12px;
                        margin: 16px 0;
                    }
                    .content {
                        padding: 20px;
                    }
                    .content h2 {
                        font-size: 20px; 
                        margin-top: 0; 
                        color: #172B4D;
                    }
                    .content p {
                        margin-bottom: 16px;
                    }
                    .footer {
                        padding: 10px; 
                        text-align: center; 
                        font-size: 12px; 
                        color: #5E6C84; 
                    }
                    a { color: #007BFF; text-decoration: none; }
                    a:hover { text-decoration: underline; }
                    ul { padding-left: 20px; margin: 0 0 16px 0; }
                    li { margin-bottom: 8px; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <div class="logo">OPULENCE HAVEN</div>
                    </div>
                    <div class="content">
                        <h2>Hello Subscriber,</h2>
                        <p>Thank you for subscribing to our newsletter. We are thrilled to have you as part of our community!</p>
                        <div>
                            <p>Here are some highlights you can enjoy:</p>
                        </div>
                        <ul class="highlight">
                            <li><strong>Exclusive Offers:</strong> Enjoy special discounts and packages available only to our subscribers.</li>
                            <li><strong>Upcoming Events:</strong> Join us for exciting events, including live music, gourmet dinners, and wellness workshops.</li>
                            <li><strong>Loyalty Program:</strong> Earn points with every stay and redeem them for free nights, upgrades, and more.</li>
                        </ul>
                        <p>Stay tuned for our latest updates, promotions, and insider tips to make the most of your stay with us.</p>
                        <p>If you have any questions or need assistance, feel free to <a href="mailto:info@opulencehaven.com">contact us</a>.</p>
                        <p>Warm regards,<br>The Opulence Haven Team</p>
                    </div>
                    <div class="footer">
                        <p>&copy; ' . date('Y') . ' Opulence Haven. All rights reserved.</p>
                        <p><a href="http://localhost/OpulenceHaven/User/home_page.php">Visit our website</a></p>
                    </div>
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
