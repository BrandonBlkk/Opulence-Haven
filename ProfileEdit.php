<?php
session_start();
include('./config/dbConnection.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}
$id = (isset($_SESSION['UserID']) && !empty($_SESSION['UserID'])) ? $_SESSION['UserID'] : $id = null;

$alertMessage = '';
$success = false;

// Fetch the current user data from the database
$userQuery = "SELECT * FROM usertb WHERE UserID = $id";
$userResult = mysqli_query($connect, $userQuery);
$userData = mysqli_fetch_assoc($userResult);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['resetPassword'])) {
    $password = mysqli_real_escape_string($connect, trim($_POST['password']));
    $newPassword = mysqli_real_escape_string($connect, trim($_POST['newpassword']));
    $confirmPassword = mysqli_real_escape_string($connect, trim($_POST['confirmpassword']));

    // Check if the password is same from database
    $checkPasswordQuery = "SELECT UserPassword FROM usertb WHERE UserPassword = '$password'";

    $checkPasswordQuery = mysqli_query($connect, $checkPasswordQuery);
    $count = mysqli_num_rows($checkPasswordQuery);

    if ($count > 0) {
        // Ensure the new password and confirmation password match
        if ($newPassword === $confirmPassword) {
            $updatePasswordQuery = "UPDATE usertb SET UserPassword = '$newPassword' WHERE UserID = '$id'";
            $updatePasswordQueryResult = mysqli_query($connect, $updatePasswordQuery);

            if ($updatePasswordQueryResult) {
                $success = true;
            } else {
                $alertMessage = 'Error updating password. Please try again.';
            }
        } else {
            $alertMessage = 'New password and confirmation do not match.';
        }
    } else {
        $alertMessage = "Current password doesn't match our records.";
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/3.5.0/remixicon.css" integrity="sha512-HXXR0l2yMwHDrDyxJbrMD9eLvPe3z3qL3PPeozNTsiHJEENxx8DH2CxmV05iwG0dwoz5n4gQZQyYLUNt1Wdgfg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="CSS/output.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="CSS/input.css?v=<?php echo time(); ?>">
</head>

<body class="relative">
    <?php
    include('./includes/Navbar.php');
    ?>

    <section class="max-w-[1300px] mx-auto">
        <div class="flex-1 px-3 overflow-x-auto">
            <div class="mx-auto py-4 bg-white">
                <div class="pb-3">
                    <h1 class="text-xl sm:text-2xl text-blue-900 font-semibold mb-1">User Information</h1>
                    <div class="text-sm text-gray-500">
                        <p>Here you can eidt public information about yourself.</p>
                        <p>The changes will be displayed for other users within 5 minutes.</p>
                    </div>
                </div>
                <form action="<?php $_SERVER["PHP_SELF"] ?>" method="POST">
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
                                        value="<?php echo $userData['UserName'] ?>"
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
                                        value="<?php echo $userData['UserEmail'] ?>"
                                        placeholder="Enter your email">
                                    <small id="emailError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none">Email is required.</small>
                                </div>
                            </div>
                            <!-- Password Input -->
                            <div>
                                <div class="flex flex-col relative">
                                    <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                                    <input id="passwordInput"
                                        class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                        type="password"
                                        name="password"
                                        value="<?php echo $userData['UserPassword'] ?>"
                                        placeholder="Enter your password"
                                        disabled>
                                    <small id="passwordError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none">Password is required.</small>
                                </div>
                                <p class="text-sm text-amber-500 mt-1 cursor-pointer">Reset your password?</p>
                            </div>

                            <!-- Reset Password -->
                            <div>
                                <form action="<?php $_SERVER["PHP_SELF"] ?>" method="post" id="resetPasswordForm">
                                    <div class="space-y-4">
                                        <h2 class="text-xl font-semibold text-blue-900 mb-4">Reset Password</h2>
                                        <div class="flex flex-col sm:flex-row gap-4 sm:gap-2">
                                            <!-- Current Password Input -->
                                            <div class="flex flex-col flex-1 relative">
                                                <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                                                <div class="flex items-center justify-between border rounded">
                                                    <input id="resetpasswordInput"
                                                        class="p-2 w-full rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                                        type="password"
                                                        name="password"
                                                        placeholder="Enter your password">
                                                    <i id="resettogglePassword" class="ri-eye-line p-2 cursor-pointer"></i>
                                                </div>
                                                <small id="resetpasswordError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none">Password is required.</small>
                                            </div>
                                            <!-- New Password Input -->
                                            <div class="flex flex-col flex-1 relative">
                                                <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                                                <div class="flex items-center justify-between border rounded">
                                                    <input id="newpasswordInput"
                                                        class="p-2 w-full rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                                        type="password"
                                                        name="newpassword"
                                                        placeholder="Set new password">
                                                    <i id="newtogglePassword" class="ri-eye-line p-2 cursor-pointer"></i>
                                                </div>
                                                <small id="newpasswordError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none">New password is required.</small>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Confirm Password Input -->
                                    <div class="flex flex-col flex-1 my-4 relative">
                                        <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                                        <div class="flex items-center justify-between border rounded">
                                            <input id="confirmpasswordInput"
                                                class="p-2 w-full rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                                type="password"
                                                name="confirmpassword"
                                                placeholder="Confirm new password">
                                            <i id="confirmtogglePassword" class="ri-eye-line p-2 cursor-pointer"></i>
                                        </div>
                                        <small id="confirmpasswordError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none">Confirm password is required.</small>
                                        <p class="text-xs text-red-500 mt-1">Ensure you toggle password visibility only in a private and secure environment to protect your information from being seen by others.</p>
                                    </div>
                                    <div class="flex justify-self-end select-none">
                                        <button type="submit" name="resetPassword" class="bg-amber-500 text-white font-semibold px-6 py-2 rounded-sm hover:bg-amber-600 focus:outline-none focus:bg-indigo-700 transition duration-300 ease-in-out">Update Password</button>
                                    </div>
                                </form>
                            </div>
                            <!-- Phone Input -->
                            <div class="relative">
                                <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                <input
                                    id="phone"
                                    class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                    type="tel"
                                    name="phone"
                                    value="<?php echo $userData['UserPhone'] ?>"
                                    placeholder="Enter your phone">
                                <small id="phoneError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none">Phone is required.</small>
                            </div>
                            <div class="flex items-center justify-end gap-3 select-none">
                                <a class="border-2 px-4 py-2 rounded-sm flex items-center justify-center gap-2" href="#">
                                    <i class="ri-delete-bin-line text-xl text-red-500"></i>
                                    <p class="font-semibold">Delete Account</p>
                                </a>
                                <button type="submit" name="modify" class="bg-amber-500 text-white font-semibold px-6 py-2 rounded-sm hover:bg-amber-600 focus:outline-none focus:bg-indigo-700 transition duration-300 ease-in-out">Save Changes</button>
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

    <?php
    include('./includes/Alert.php');
    include('./includes/Footer.php');
    ?>
    <script src="//unpkg.com/alpinejs" defer></script>
    <script src="./JS/index.js"></script>
    <script src="./JS/auth.js"></script>
</body>

</html>