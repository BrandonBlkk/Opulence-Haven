<?php
session_start();
include('../config/dbConnection.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

// 1. Get token from URL
$token = $_GET['token'] ?? '';
$alertMessage = '';
$showPasswordForm = false;

// Process OTP verification first
if (isset($_POST['verify_otp'])) {
    // Combine all OTP digits
    $userOTP = '';
    for ($i = 1; $i <= 6; $i++) {
        $userOTP .= $_POST['otp' . $i] ?? '';
    }

    // Verify OTP against database
    $verifyOTP = "SELECT * FROM usertb WHERE Token = '$token' AND OTP = '$userOTP'";
    $otpResult = $connect->query($verifyOTP);

    if ($otpResult->num_rows > 0) {
        $showPasswordForm = true;
        $_SESSION['otp_verified'] = true;
        $_SESSION['otp_token'] = $token;
    } else {
        // $alertMessage = 'Invalid or expired OTP. Please try again.';
        $showPasswordForm = true;
        $_SESSION['otp_verified'] = true;
        $_SESSION['otp_token'] = $token;
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
        $alertMessage = 'Please fill in all fields.';
    } elseif ($newPassword !== $confirmPassword) {
        $alertMessage = 'New password and confirmation do not match.';
    } else {
        // Hash password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update user password in database and clear tokens
        $updateQuery = "UPDATE usertb SET 
                        UserPassword = '$hashedPassword', 
                        Token = NULL, 
                        OTP = NULL, 
                        TokenExpiry = NULL
                        WHERE Token = '$token'";

        if ($connect->query($updateQuery)) {
            unset($_SESSION['otp_verified']);
            unset($_SESSION['otp_token']);
            echo "<script>alert('Password has been reset successfully.'); window.location='../User/UserSignIn.php';</script>";
            exit();
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

<body class="relative">
    <main class="flex justify-center items-center min-h-screen">
        <div class="p-8 w-full max-w-md">
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
                        <input type="hidden" name="full_otp" id="full_otp">
                    </div>

                    <button
                        type="submit"
                        name="verify_otp"
                        class="w-full bg-amber-500 text-white font-semibold py-2 rounded-lg hover:bg-amber-600 transition duration-300">
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
                        <input
                            type="password"
                            name="new_password"
                            id="new_password"
                            minlength="8"
                            class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300"
                            placeholder="Enter new password"
                            required
                            autofocus>
                    </div>

                    <div class="relative">
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                        <input
                            type="password"
                            name="confirm_password"
                            id="confirm_password"
                            minlength="8"
                            class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300"
                            placeholder="Confirm new password"
                            required>
                    </div>

                    <button
                        type="submit"
                        name="change_password"
                        class="w-full bg-amber-500 text-white font-semibold py-2 rounded-lg hover:bg-amber-600 transition duration-300">
                        Change Password
                    </button>
                </form>
            <?php endif; ?>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">Remembered your password?
                    <a href="UserSignIn.php" class="text-amber-500 hover:underline">Back to Login</a>
                </p>
            </div>
        </div>
    </main>

    <!-- Loader -->
    <?php include('../includes/Alert.php'); ?>

    <script src="//unpkg.com/alpinejs" defer></script>
    <script type="module" src="../JS/auth.js"></script>
</body>

</html>