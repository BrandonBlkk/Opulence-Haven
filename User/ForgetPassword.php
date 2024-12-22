<?php

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opulence Haven</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/3.5.0/remixicon.css" integrity="sha512-HXXR0l2yMwHDrDyxJbrMD9eLvPe3z3qL3PPeozNTsiHJEENxx8DH2CxmV05iwG0dwoz5n4gQZQyYLUNt1Wdgfg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="CSS/output.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="CSS/input.css?v=<?php echo time(); ?>">
</head>

<body class="relative">
    <?php
    include('./includes/Navbar.php');
    ?>

    <!DOCTYPE html>
    <html lang="en">

    <main class="flex justify-center">
        <div class="p-8 w-full max-w-md">
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Forgot Password</h1>
                <p class="text-gray-600">Enter your email to reset your password and regain access.</p>
            </div>

            <form action="reset_password.php" method="POST" class="space-y-6">
                <!-- Email Input -->
                <div class="relative">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        required
                        class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300"
                        placeholder="Enter your email">
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
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

    <?php
    include('./includes/Footer.php');
    ?>
    <script src="//unpkg.com/alpinejs" defer></script>
    <script src="./JS/index.js"></script>
</body>

</html>