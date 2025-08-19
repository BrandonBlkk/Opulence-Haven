<?php
session_start();
require_once('../config/db_connection.php');

// Check if token exists in URL
if (!isset($_GET['token']) || empty($_GET['token'])) {
    showErrorPage("Invalid activation link. Token is missing.");
    exit();
}

$plain_token = $_GET['token'];

try {
    // Prepare SQL to find the admin with pending status
    $stmt = $connect->prepare("SELECT AdminID, Token, TokenExpiry FROM admintb WHERE AccountStatus = 'pending'");
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        showErrorPage("Invalid activation link. Token not found or account already activated.");
        exit();
    }

    $tokenFound = false;
    $adminData = null;

    while ($row = $result->fetch_assoc()) {
        if (password_verify($plain_token, $row['Token'])) {
            $tokenFound = true;
            $adminData = $row;
            break;
        }
    }

    if (!$tokenFound) {
        showErrorPage("Invalid activation link. Token not found or account already activated.");
        exit();
    }

    $currentDateTime = new DateTime();
    $tokenExpiry = new DateTime($adminData['TokenExpiry']);

    if ($currentDateTime > $tokenExpiry) {
        showErrorPage("Activation link has expired. Please request a new one.");
        exit();
    }

    $updateStmt = $connect->prepare("UPDATE admintb SET AccountStatus = 'active', Token = NULL, TokenExpiry = NULL WHERE AdminID = ?");
    $updateStmt->bind_param("s", $adminData['AdminID']);
    $updateStmt->execute();

    if ($updateStmt->affected_rows === 1) {
        // Success message
        echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Account Activated</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    </head>
    <body class="bg-white min-h-screen flex flex-col items-center justify-center p-4">
        <div class="w-full max-w-md bg-white overflow-hidden p-8 text-center">
            <div class="flex justify-center mb-6">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center security-badge">
                    <i class="fas fa-check-circle text-green-500 text-4xl"></i>
                </div>
            </div>
            
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Account Activated Successfully</h1>
            <p class="text-gray-600 mb-6">Your admin account has been verified and is now active in the Opulence Haven system.</p>
            
            <a href="../Admin/admin_signin.php" class="inline-flex items-center justify-center w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-md transition duration-200 mb-4 select-none">
                Go to Sign In
            </a>
            
            <p class="text-xs text-gray-500">
                Need help? Contact <a href="mailto:support@opulencehaven.com" class="text-blue-500 hover:underline">support@opulencehaven.com</a>
            </p>
        </div>
        
        <div class="mt-8 text-center text-xs text-gray-400">
            <p>© 2025 Opulence Haven. All rights reserved. | Secure Admin Portal</p>
        </div>
    </body>
    </html>';
    } else {
        showErrorPage("Failed to activate account. Please contact support.");
    }
} catch (Exception $e) {
    showErrorPage("An error occurred: " . $e->getMessage());
}

// Close connection
$connect->close();

function showErrorPage($message)
{
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Activation Error</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    </head>
    <body class="bg-white min-h-screen flex flex-col items-center justify-center p-4">
        <div class="w-full max-w-md bg-white rounded-xl overflow-hidden p-8 text-center">
            <div class="flex justify-center mb-6">
                <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-exclamation-circle text-red-500 text-4xl"></i>
                </div>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Activation Failed</h1>
            <p class="text-gray-600 mb-6">' . $message . '</p>
        </div>

        <p class="text-xs text-gray-500">
            Need help? Contact <a href="mailto:support@opulencehaven.com" class="text-blue-500 hover:underline">support@opulencehaven.com</a>
        </p>

        <div class="mt-8 text-center text-xs text-gray-400">
            <p>© 2025 Opulence Haven. All rights reserved. | Secure Admin Portal</p>
        </div>
    </body>
    </html>';
}
