<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unauthorized Access</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-white min-h-screen flex flex-col items-center justify-center p-4">
    <div class="w-full max-w-md bg-white overflow-hidden p-8 text-center">
        <div class="flex justify-center mb-6">
            <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center">
                <i class="fas fa-exclamation-circle text-red-500 text-4xl"></i>
            </div>
        </div>

        <h1 class="text-2xl font-bold text-gray-800 mb-2">Unauthorized Access</h1>
        <p class="text-gray-600 mb-6">You don't have permission to access this page.</p>

        <a href="../Admin/admin_dashboard.php" class="inline-flex items-center justify-center w-full px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-medium rounded-md transition duration-200 mb-4 select-none">
            Return to Dashboard
        </a>

        <p class="text-xs text-gray-500">
            Need help? Contact <a href="mailto:support@opulencehaven.com" class="text-blue-500 hover:underline">support@opulencehaven.com</a>
        </p>
    </div>

    <div class="mt-8 text-center text-xs text-gray-400">
        <p>© 2025 Opulence Haven. All rights reserved. | Secure Admin Portal</p>
    </div>
</body>

</html>