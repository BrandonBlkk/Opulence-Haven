<?php
session_start();
require_once('../config/db_connection.php');
include('../includes/mask_email.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}
$id = (isset($_SESSION['UserID']) && !empty($_SESSION['UserID'])) ? $_SESSION['UserID'] : $id = null;

$alertMessage = '';
$profileChanged = false;
$response = ['success' => false, 'profileChanged' => false, 'message' => ''];

// Fetch the current user data from the database
$userQuery = "SELECT * FROM usertb WHERE UserID = '$id'";
$userData = $connect->query($userQuery)->fetch_assoc();

// Update the user profile
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['modify'])) {
    $username = mysqli_real_escape_string($connect, trim($_POST['username']));
    $email = mysqli_real_escape_string($connect, trim($_POST['email']));
    $phone = mysqli_real_escape_string($connect, trim($_POST['phone']));

    // Check if any field actually changed
    $profileChanged = ($username !== $userData['UserName'] || $phone !== $userData['UserPhone']);

    if ($profileChanged) {
        $updateProfileQuery = "UPDATE usertb SET 
                             UserName = '$username', 
                             UserPhone = '$phone' 
                             WHERE UserID = '$id'";
        $updateProfileQueryResult = $connect->query($updateProfileQuery);

        if ($updateProfileQueryResult) {
            $response['profileChanged'] = true;
            $response['success'] = true;
        } else {
            $response['message'] = 'Error updating profile. Please try again.';
        }
    } else {
        $response['success'] = true; // Success but no changes
        $response['profileChanged'] = false;
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Reset the user password
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['resetPassword'])) {
    $response = ['success' => false, 'message' => ''];
    $currentPassword = mysqli_real_escape_string($connect, trim($_POST['password']));
    $newPassword = mysqli_real_escape_string($connect, trim($_POST['newpassword']));
    $confirmPassword = mysqli_real_escape_string($connect, trim($_POST['confirmpassword']));

    $checkPasswordQuery = "SELECT UserPassword FROM usertb WHERE UserID = '$id'";
    $result = $connect->query($checkPasswordQuery);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify the current password
        if (password_verify($currentPassword, $user['UserPassword'])) {
            // First check if new and confirm passwords match before hashing
            if ($_POST['newpassword'] === $_POST['confirmpassword']) {
                // Hash the new password
                $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);

                $updatePasswordQuery = "UPDATE usertb SET UserPassword = '$newPasswordHash' WHERE UserID = '$id'";
                $updatePasswordQueryResult = $connect->query($updatePasswordQuery);

                if ($updatePasswordQueryResult) {
                    $response['success'] = true;
                } else {
                    $response['message'] = 'Error updating password. Please try again.';
                }
            } else {
                $response['message'] = 'New password and confirmation do not match.';
            }
        } else {
            $response['message'] = "Current password is incorrect.";
        }
    } else {
        $response['message'] = "User not found.";
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opulence Haven</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../CSS/output.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../CSS/input.css?v=<?php echo time(); ?>">
</head>

<body class="relative min-w-[380px]">
    <?php
    include('../includes/navbar.php');
    ?>

    <section class="max-w-[1300px] mx-auto">
        <div class="flex-1 px-3 overflow-x-auto">
            <div class="mx-auto py-4 bg-white">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <div class="flex items-center justify-between">
                            <div class="pb-3">
                                <h1 class="text-xl sm:text-2xl text-blue-900 font-semibold mb-1">User Information</h1>
                                <div class="text-sm text-gray-500">
                                    <p>Here you can eidt public information about yourself.</p>
                                    <p>The changes will be displayed for other users within 5 minutes.</p>
                                </div>
                            </div>
                            <button id="profileDeleteBtn" class="border-2 px-4 py-2 rounded-sm flex items-center justify-center gap-2 select-none">
                                <i class="ri-delete-bin-line text-xl text-red-500"></i>
                                <p class="font-semibold">Delete Account</p>
                            </button>
                        </div>
                        <form action="<?php $_SERVER["PHP_SELF"] ?>" method="POST" id="updateProfileForm">
                            <div class="space-y-4">
                                <div>
                                    <div class="flex flex-col sm:flex-row gap-4 sm:gap-2">
                                        <!-- Username Input -->
                                        <div class="relative flex-1">
                                            <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                                            <input
                                                id="usernameInput"
                                                class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                                type="text"
                                                name="username"
                                                value="<?php echo $userData['UserName'] ?>"
                                                placeholder="Enter your username">
                                            <small id="usernameError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                                        </div>
                                        <!-- Email Input -->
                                        <div class="relative flex-1">
                                            <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                            <input
                                                id="emailInput"
                                                class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                                type="email"
                                                name="email"
                                                value="<?php echo maskEmail($userData['UserEmail']) ?>"
                                                placeholder="Enter your email">
                                            <small id="emailError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                                        </div>
                                    </div>
                                    <p class="text-sm text-end text-amber-500 cursor-pointer">Reset your password?</p>
                                </div>

                                <!-- Phone Input -->
                                <div class="relative">
                                    <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                    <input
                                        id="phoneInput"
                                        class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                        type="tel"
                                        name="phone"
                                        value="<?php echo $userData['UserPhone'] ?>"
                                        placeholder="Enter your phone">
                                    <small id="phoneError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                                </div>
                                <div class="flex items-center justify-end gap-3 select-none">
                                    <button type="submit" name="modify" class="bg-amber-500 text-white font-semibold px-6 py-2 rounded-sm hover:bg-amber-600 focus:outline-non transition duration-300 ease-in-out">Save Changes</button>
                                </div>
                            </div>
                        </form>

                        <!-- Reset Password -->
                        <form action="<?php $_SERVER["PHP_SELF"] ?>" method="post" id="resetPasswordForm">
                            <div class="space-y-4">
                                <h2 class="text-xl font-semibold text-blue-900 mb-4">Reset Password</h2>
                                <div class="flex flex-col sm:flex-row gap-4 sm:gap-2">
                                    <!-- Current Password Input -->
                                    <div class="flex flex-col flex-1 relative">
                                        <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                                        <div class="flex items-center justify-between border rounded">
                                            <input id="resetpasswordInput"
                                                class="p-2 w-full pr-10 rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                                type="password"
                                                name="password"
                                                placeholder="Enter your password">
                                            <i id="resettogglePassword" class="absolute right-1 ri-eye-line p-2 cursor-pointer"></i>
                                        </div>
                                        <small id="resetpasswordError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                                    </div>
                                    <!-- New Password Input -->
                                    <div class="flex flex-col flex-1 relative">
                                        <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                                        <div class="flex items-center justify-between border rounded">
                                            <input id="newpasswordInput"
                                                class="p-2 w-full pr-10 rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                                type="password"
                                                name="newpassword"
                                                placeholder="Set new password">
                                            <i id="newtogglePassword" class="absolute right-1 ri-eye-line p-2 cursor-pointer"></i>
                                        </div>
                                        <small id="newpasswordError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                                    </div>
                                </div>
                            </div>
                            <!-- Confirm Password Input -->
                            <div class="flex flex-col flex-1 my-4 relative">
                                <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                                <div class="flex items-center justify-between border rounded">
                                    <input id="confirmpasswordInput"
                                        class="p-2 w-full pr-10 rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                        type="password"
                                        name="confirmpassword"
                                        placeholder="Confirm new password">
                                    <i id="confirmtogglePassword" class="absolute right-1 ri-eye-line p-2 cursor-pointer"></i>
                                </div>
                                <small id="confirmpasswordError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                            </div>
                            <p class="text-xs text-red-500 mt-1">Ensure you toggle password visibility only in a private and secure environment to protect your information from being seen by others.</p>
                            <div class="flex justify-self-end select-none">
                                <button type="submit" name="resetPassword" class="bg-amber-500 text-white font-semibold px-6 py-2 rounded-sm hover:bg-amber-600 focus:outline-none transition duration-300 ease-in-out">Update Password</button>
                            </div>
                        </form>
                    </div>
                    <!-- Right Side Note and Illustration -->
                    <div class="px-0 sm:px-6 rounded-lg flex flex-col items-center justify-center">
                        <div class="bg-sky-100 p-3 rounded">
                            <h3 class="text-xl font-semibold text-gray-700 mb-2">Build Trust!</h3>
                            <p class="text-sm text-gray-600">Your profile is displayed on your account and in communications, making it easy for others to recognize and connect with you.</p>
                        </div>
                        <div class="max-w-[500px] select-none">
                            <img src="../UserImages/account-concept-illustration_114360-409.avif" alt="Illustration" class="w-full h-full object-cover">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Delete Confirmation Modal -->
    <div id="confirmDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
        <div class="bg-white max-w-5xl p-6 rounded-md shadow-md text-center">
            <h2 class="text-xl font-semibold text-red-600 mb-4">Confirm Account Delete</h2>
            <p class="text-slate-600 mb-2">You are currently signed in as:</p>
            <p class="font-semibold text-gray-800 mb-4">
                <?php echo $_SESSION['UserName'] . ' (' . $_SESSION['UserEmail'] . ')'; ?>
            </p>
            <p class="text-sm text-gray-500">
                Deleting your account will permanently remove all your personal data, purchase history, and preferences from our system.
            </p>
            <p class="text-sm text-gray-500 mb-4">
                This action is irreversible. Please type <strong>"DELETE"</strong> in the box below to confirm you want to proceed.
            </p>
            <input
                id="deleteConfirmInput"
                type="text"
                placeholder='Type "DELETE" here'
                class="w-full p-2 mb-4 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-red-300" />
            <div class="flex justify-end gap-4 select-none">
                <button id="cancelDeleteBtn" class="px-4 py-2 bg-gray-200 text-black hover:bg-gray-300">
                    Cancel
                </button>
                <button id="confirmDeleteBtn" class="px-4 py-2 bg-red-600 text-white hover:bg-red-700 cursor-not-allowed" disabled>
                    I understand, delete my account
                </button>
            </div>
        </div>
    </div>

    <?php
    include('../includes/alert.php');
    include('../includes/footer.php');
    ?>
    <script src="//unpkg.com/alpinejs" defer></script>
    <script type="module" src="../JS/index.js"></script>
</body>

</html>