<?php
session_start();
include('../config/dbConnection.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

// Timezone 
date_default_timezone_set('Asia/Yangon');

// 1. Get token from URL
$token = $_GET['token'] ?? '';
$alertMessage = '';
$showPasswordForm = false;
$showCompleteMessage = false;

if (isset($_POST['verify_otp'])) {
    // Get and sanitize token from URL
    $token = trim($_GET['token'] ?? '');
    if (empty($token)) {
        die("Invalid token access");
    }

    // Combine all OTP digits
    $userOTP = '';
    for ($i = 1; $i <= 6; $i++) {
        $userOTP .= $_POST['otp' . $i] ?? '';
    }

    // Verify OTP & Token expiry against database
    $verifyOTP = "SELECT * FROM usertb WHERE Token = ? AND OTP = ?";
    $stmt = $connect->prepare($verifyOTP);
    $stmt->bind_param("ss", $token, $userOTP);
    $stmt->execute();
    $otpResult = $stmt->get_result();

    if ($otpResult->num_rows > 0) {
        $userData = $otpResult->fetch_assoc();

        // Check if Token is expired (compare with current time)
        $currentTime = date('Y-m-d H:i:s');
        $tokenExpiry = $userData['TokenExpiry'];

        if ($tokenExpiry < $currentTime) {
            $alertMessage = 'Token has expired. Please request a new link.';
        } else {
            // Token is valid, proceed
            $showPasswordForm = true;
            $_SESSION['otp_verified'] = true;
            $_SESSION['otp_token'] = $token;
        }
    } else {
        $alertMessage = 'Invalid or expired OTP. Please try again.';
    }
}

// Process password reset form
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    if (!isset($_SESSION['otp_verified']) || !isset($_SESSION['otp_token']) || $_SESSION['otp_token'] !== $token) {
        echo "<script>alert('Session expired. Please restart the password reset process.')</script>";
        echo "<script>window.location = 'ForgetPassword.php'</script>";
        exit();
    }

    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if (empty($newPassword) || empty($confirmPassword)) {
        $showPasswordForm = true;
        $alertMessage = 'Please fill in all fields.';
    } elseif ($newPassword !== $confirmPassword) {
        $showPasswordForm = true;
        $alertMessage = 'New password and confirmation do not match.';
    } else {
        // Hash password
        // $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update user password in database and clear tokens
        $updateQuery = "UPDATE usertb SET 
                        UserPassword = '$newPassword', 
                        Token = NULL, 
                        OTP = NULL, 
                        TokenExpiry = NULL
                        WHERE Token = '$token'";

        if ($connect->query($updateQuery)) {
            $showCompleteMessage = true;
            unset($_SESSION['otp_verified']);
            unset($_SESSION['otp_token']);
        } else {
            echo "<script>alert('Failed to reset password. Try again.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Opulence Haven</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../CSS/output.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../CSS/input.css?v=<?php echo time(); ?>">
    <style>
        .otp-input {
            width: 3rem;
            height: 3rem;
            text-align: center;
            font-size: 1.2rem;
            margin: 0 0.25rem;
        }

        .otp-container {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
        }
    </style>
</head>

<body class="relative min-w-[380px]">
    <main class="flex justify-center items-center min-h-screen">
        <div class="p-8 w-full max-w-md">
            <?php if (!$showCompleteMessage): ?>
                <div class="text-center mb-6">
                    <h1 class="text-2xl font-bold text-gray-800">Reset Your Password</h1>
                    <p class="text-gray-600"><?php echo $showPasswordForm ? 'Create a new password for your account' : 'Enter the OTP sent to your email'; ?></p>
                </div>

                <?php if (!empty($alertMessage)): ?>
                    <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
                        <?php echo $alertMessage; ?>
                    </div>
                <?php endif; ?>

                <?php if (!$showPasswordForm): ?>
                    <!-- OTP Verification Form -->
                    <form method="POST" class="space-y-4">
                        <div class="relative">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Verification Code</label>
                            <div class="otp-container">
                                <?php for ($i = 1; $i <= 6; $i++): ?>
                                    <input
                                        type="text"
                                        name="otp<?php echo $i; ?>"
                                        id="otp<?php echo $i; ?>"
                                        maxlength="1"
                                        pattern="[0-9]"
                                        inputmode="numeric"
                                        class="otp-input border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300"
                                        <?php if ($i == 1) echo 'autofocus'; ?>
                                        oninput="moveToNext(this, <?php echo $i; ?>)">
                                <?php endfor; ?>
                            </div>
                        </div>

                        <button
                            type="submit"
                            name="verify_otp"
                            class="w-full bg-amber-500 text-white font-semibold py-2 rounded-lg hover:bg-amber-600 transition duration-300 select-none">
                            Verify OTP
                        </button>

                        <div class="text-center text-sm text-gray-500 mt-4">
                            <p>Didn't receive code? <a href="ForgetPassword.php" class="text-amber-500 hover:underline">Request again</a></p>
                        </div>
                    </form>

                    <script>
                        function moveToNext(current, position) {
                            if (current.value.length === 1) {
                                if (position < 6) {
                                    document.getElementById('otp' + (position + 1)).focus();
                                }
                            }
                        }
                    </script>
                <?php else: ?>
                    <!-- Password Reset Form -->
                    <form method="POST" class="space-y-4">
                        <div class="relative">
                            <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                            <div class="flex items-center justify-between border rounded">
                                <input
                                    type="password"
                                    name="new_password"
                                    id="new_password"
                                    minlength="8"
                                    class="w-full p-2 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300"
                                    placeholder="Enter new password"
                                    autofocus>
                                <i id="confirmtogglePassword3" class="absolute right-1 ri-eye-line p-2 cursor-pointer"></i>
                            </div>
                        </div>

                        <div class="relative">
                            <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                            <div class="flex items-center justify-between border rounded">
                                <input
                                    type="password"
                                    name="confirm_password"
                                    id="confirm_password"
                                    minlength="8"
                                    class="w-full p-2 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300"
                                    placeholder="Confirm new password">
                                <i id="confirmtogglePassword4" class="absolute right-1 ri-eye-line p-2 cursor-pointer"></i>
                            </div>
                        </div>

                        <button
                            type="submit"
                            name="change_password"
                            class="w-full bg-amber-500 text-white font-semibold py-2 rounded-lg hover:bg-amber-600 transition duration-300 select-none">
                            Change Password
                        </button>
                    </form>
                <?php endif; ?>
            <?php else: ?>
                <!-- Success Completion Message -->
                <div class="text-center relative overflow-hidden" id="confetti-container">
                    <!-- Animated Checkmark Circle - Faster timing -->
                    <div class="mx-auto relative flex items-center justify-center h-16 w-16 z-10">
                        <!-- Background circle -->
                        <div class="absolute h-16 w-16 rounded-full"></div>

                        <!-- Faster border animation (0.6s) -->
                        <svg class="absolute h-16 w-16 -rotate-90" viewBox="0 0 100 100">
                            <circle
                                cx="50"
                                cy="50"
                                r="45"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="4"
                                stroke-linecap="round"
                                stroke-dasharray="283"
                                stroke-dashoffset="283"
                                class="text-green-500 animate-[borderFill_0.6s_ease-out_0.3s_1_forwards]" />
                        </svg>

                        <!-- Faster checkmark animation (0.5s) with shorter delay -->
                        <svg class="h-8 w-8 text-green-600 opacity-0 animate-[appearAndSpin_0.5s_ease-out_0.9s_1_forwards]"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            id="success-checkmark">
                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>

                    <style>
                        @keyframes borderFill {
                            0% {
                                stroke-dashoffset: 283;
                            }

                            100% {
                                stroke-dashoffset: 0;
                            }
                        }

                        @keyframes appearAndSpin {
                            0% {
                                opacity: 0;
                                transform: scale(0.5) rotate(-90deg);
                            }

                            70% {
                                opacity: 1;
                                transform: scale(1.1) rotate(10deg);
                            }

                            100% {
                                opacity: 1;
                                transform: scale(1) rotate(0deg);
                            }
                        }

                        /* Modified Confetti styles for full page drop */
                        .confetti {
                            position: fixed;
                            width: 10px;
                            height: 10px;
                            opacity: 0;
                            animation: confetti-fall 3s ease-in-out forwards;
                            top: -10px;
                            z-index: 1;
                        }

                        @keyframes confetti-fall {
                            0% {
                                transform: translateY(0) rotate(0deg);
                                opacity: 1;
                            }

                            100% {
                                transform: translateY(100vh) rotate(360deg);
                                opacity: 0;
                            }
                        }
                    </style>

                    <!-- Heading -->
                    <h3 class="mt-4 text-xl font-medium text-gray-900 relative z-20">Password Reset Successful!</h3>

                    <!-- Message -->
                    <div class="mt-3 text-base text-gray-500 relative z-20">
                        <p>You can now login with your new password.</p>
                    </div>

                    <!-- Button -->
                    <div class="mt-8 relative z-20">
                        <a href="../User/UserSignIn.php"
                            class="w-full flex justify-center items-center px-6 py-3 text-base font-medium text-white bg-amber-500 hover:bg-amber-600 rounded-lg transition duration-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 select-none">
                            Continue to Login
                        </a>
                    </div>

                    <script>
                        // Start confetti when circle animation begins (after 0.3s delay)
                        setTimeout(() => {
                            createConfetti();
                        }, 300); // Matches the borderFill animation delay

                        function createConfetti() {
                            const colors = ['#f44336', '#e91e63', '#9c27b0', '#673ab7', '#3f51b5', '#2196f3', '#03a9f4', '#00bcd4', '#009688', '#4CAF50', '#8BC34A', '#CDDC39', '#FFEB3B', '#FFC107', '#FF9800', '#FF5722'];
                            const container = document.getElementById('confetti-container');

                            // Initial burst
                            for (let i = 0; i < 50; i++) {
                                createConfettiPiece();
                            }

                            // Continuous smaller bursts during animation
                            const burstInterval = setInterval(() => {
                                for (let i = 0; i < 10; i++) {
                                    createConfettiPiece();
                                }
                            }, 200);

                            // Stop after 2 seconds
                            setTimeout(() => {
                                clearInterval(burstInterval);
                            }, 2000);

                            function createConfettiPiece() {
                                const confetti = document.createElement('div');
                                confetti.className = 'confetti';

                                // Random properties
                                const color = colors[Math.floor(Math.random() * colors.length)];
                                const size = Math.random() * 10 + 5;
                                const left = Math.random() * 100;
                                const animationDuration = Math.random() * 2 + 2;
                                const delay = Math.random() * 0.5;

                                confetti.style.backgroundColor = color;
                                confetti.style.width = `${size}px`;
                                confetti.style.height = `${size}px`;
                                confetti.style.left = `${left}%`;
                                confetti.style.animationDuration = `${animationDuration}s`;
                                confetti.style.animationDelay = `${delay}s`;
                                confetti.style.borderRadius = Math.random() > 0.5 ? '50%' : '0';

                                container.appendChild(confetti);

                                // Remove confetti after animation completes
                                confetti.addEventListener('animationend', function() {
                                    confetti.remove();
                                });
                            }
                        }
                    </script>
                </div>
            <?php endif; ?>

            <?php if (!$showCompleteMessage): ?>
                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">Remembered your password?
                        <a href="UserSignIn.php" class="text-amber-500 hover:underline">Back to Login</a>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="//unpkg.com/alpinejs" defer></script>
    <script type="module" src="../JS/auth.js"></script>
</body>

</html>