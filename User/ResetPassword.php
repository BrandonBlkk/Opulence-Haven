<?php
session_start();
include('../config/dbConnection.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

// 1. Get token from URL
$token = $_GET['token'] ?? '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($newPassword === $confirmPassword) {
        // Hash password
        // $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update user password in database
        $updateQuery = "UPDATE usertb SET UserPassword = '$confirmPassword', Token = NULL WHERE Token = '$token'";
        if ($connect->query($updateQuery)) {
            echo "<script>alert('Password has been reset successfully.'); window.location='../User/UserSignIn.php';</script>";
        } else {
            echo "<script>alert('Failed to reset password. Try again.');</script>";
        }
    } else {
        echo "<script>alert('Passwords do not match.');</script>";
    }
}
?>

<!-- HTML Form -->
<form method="POST">
    <h2>Reset Your Password</h2>
    <label>New Password:</label><br>
    <input type="password" name="new_password" required><br><br>

    <label>Confirm New Password:</label><br>
    <input type="password" name="confirm_password" required><br><br>

    <button type="submit">Change Password</button>
</form>