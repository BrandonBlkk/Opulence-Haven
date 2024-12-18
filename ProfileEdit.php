<?php
session_start();
include('./config/dbConnection.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}


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

    <section class="max-w-[1300px] mx-auto">
        <div class="flex-1 px-0 sm:px-6 overflow-x-auto">
            <div class="mx-auto py-4 bg-white">
                <div class="pb-3">
                    <h1 class="text-xl sm:text-2xl text-blue-900 font-semibold mb-1">User Information</h1>
                    <div class="text-gray-500">
                        <p>Here you can eidt public information about yourself.</p>
                        <p>The changes will be displayed for other users within 5 minutes.</p>
                    </div>
                </div>
                <form method="POST">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div class="flex flex-col sm:flex-row gap-4 sm:gap-2">
                                <!-- Username Input -->
                                <div class="relative flex-1">
                                    <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                                    <input
                                        id="username"
                                        class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                        type="text"
                                        name="username"
                                        placeholder="Enter your username">
                                    <small id="usernameError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none">Username is required.</small>
                                </div>
                                <!-- Email Input -->
                                <div class="relative flex-1">
                                    <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                    <input
                                        id="email"
                                        class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                        type="email"
                                        name="email"
                                        placeholder="Enter your email">
                                    <small id="emailError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none">Email is required.</small>
                                </div>
                            </div>
                            <!-- Password Input -->
                            <div class="flex flex-col relative">
                                <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                                <div class="flex items-center justify-between border rounded">
                                    <input id="passwordInput"
                                        class="p-2 w-full rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                        type="password"
                                        name="password"
                                        placeholder="Enter your password">
                                    <i id="togglePassword" class="ri-eye-line p-2 cursor-pointer"></i>
                                </div>
                                <small id="passwordError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none">Password is required.</small>
                            </div>

                            <p>Reset your password?</p>

                            <!-- Reset Password Modal -->
                            <div class=" space-y-4">
                                <h2 class="text-xl font-semibold text-blue-900 mb-4">Reset Password</h2>
                                <!-- Current Password Input -->
                                <div class="flex flex-col relative">
                                    <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                                    <div class="flex items-center justify-between border rounded">
                                        <input id="passwordInput"
                                            class="p-2 w-full rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                            type="password"
                                            name="password"
                                            placeholder="Enter your password">
                                        <i id="togglePassword" class="ri-eye-line p-2 cursor-pointer"></i>
                                    </div>
                                    <small id="passwordError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none">Password is required.</small>
                                </div>
                                <!-- New Password Input -->
                                <div class="flex flex-col relative">
                                    <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                                    <div class="flex items-center justify-between border rounded">
                                        <input id="passwordInput"
                                            class="p-2 w-full rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                            type="password"
                                            name="password"
                                            placeholder="Enter your password">
                                        <i id="togglePassword" class="ri-eye-line p-2 cursor-pointer"></i>
                                    </div>
                                    <small id="passwordError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none">Password is required.</small>
                                </div>
                            </div>

                            <!-- Phone Input -->
                            <div class="relative">
                                <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                <input
                                    id="phone"
                                    class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                    type="tel"
                                    name="phone"
                                    placeholder="Enter your phone">
                                <small id="phoneError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none">Phone is required.</small>
                            </div>
                            <div class="flex items-center justify-end gap-3 select-none">
                                <a class="border-2 px-4 py-2 rounded-lg flex items-center justify-center gap-2" href="#">
                                    <i class="ri-delete-bin-line text-xl text-red-500"></i>
                                    <p class="text-sm">Delete Account</p>
                                </a>

                                <div class="flex">
                                    <button type="submit" name="modify" class="bg-indigo-500 text-white px-6 py-2 rounded hover:bg-indigo-600 focus:outline-none focus:bg-indigo-700 transition duration-300 ease-in-out">Save Changes</button>
                                </div>
                            </div>
                        </div>
                        <!-- Right Side Note and Illustration -->
                        <div class="px-0 sm:px-6 rounded-lg flex flex-col items-center justify-center">
                            <div class="bg-sky-100 p-3 rounded">
                                <h3 class="text-xl font-semibold text-gray-700 mb-2">Build Trust!</h3>
                                <p class="text-gray-600">Your profile is displayed on your account and in communications, making it easy for others to recognize and connect with you.</p>
                            </div>
                            <div class="max-w-[500px] select-none">
                                <img src="./UserImages/account-concept-illustration_114360-409.avif" alt="Illustration" class="w-full h-full object-cover">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- <div id="confirmModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-100 p-2 -translate-y-5 transition-all duration-300">
        <div class="bg-white max-w-5xl p-6 rounded-md shadow-md text-center">
            <h2 class="text-xl font-semibold text-blue-900 mb-4">Reset Password</h2>
            <p class="text-slate-600 mb-2">You are currently signed in as:</p>
            <p class="font-semibold text-gray-800 mb-4">
            </p>
            <p class="text-sm text-gray-500 mb-6">
                Logging out will end your session and remove access to secure areas of your account. Ensure all changes are saved.
            </p>
            <div class="flex justify-end gap-4 select-none">
                <button id="cancelBtn" class="px-4 py-2 bg-gray-200 text-black hover:bg-gray-300">
                    Cancel
                </button>
                <button id="confirmLogoutBtn" class="px-4 py-2 bg-red-600 text-white hover:bg-red-700">
                    Logout
                </button>
            </div>
        </div>
    </div> -->

    <!-- Overlay -->
    <div id="darkOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden"></div>

    <?php
    include('./includes/Footer.php');
    ?>
    <script src="//unpkg.com/alpinejs" defer></script>
    <script src="./JS/index.js"></script>
</body>

</html>