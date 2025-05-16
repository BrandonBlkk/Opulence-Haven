<?php
session_start();
include('../config/dbConnection.php');
include('../includes/AutoIDFunc.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

$alertMessage = '';
$signupSuccess = false;
$adminID = AutoID('admintb', 'AdminID', 'AD-', 6);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signup'])) {
    $firstname = mysqli_real_escape_string($connect, trim($_POST['firstname']));
    $lastname = mysqli_real_escape_string($connect, trim($_POST['lastname']));
    $username = mysqli_real_escape_string($connect, trim($_POST['username']));
    $email = mysqli_real_escape_string($connect, trim($_POST['email']));
    $password = mysqli_real_escape_string($connect, trim($_POST['password']));
    $phone = mysqli_real_escape_string($connect, trim($_POST['phone']));
    $role = isset($_POST['roleSelect']) ? mysqli_real_escape_string($connect, $_POST['roleSelect']) : '';

    // Predefined set colors
    $googleColors = [
        '#4285F4', // Blue
        '#EA4335', // Red
        '#FBBC05', // Yellow
        '#34A853', // Green
        '#673AB7', // Purple
        '#FF6D00', // Orange
        '#00ACC1', // Teal
        '#D81B60', // Pink
        '#8E24AA', // Deep Purple
        '#039BE5', // Light Blue
        '#7CB342', // Light Green
        '#F4511E', // Deep Orange
        '#546E7A', // Blue Grey
        '#E53935', // Dark Red
        '#00897B', // Dark Teal
        '#5E35B1'  // Dark Purple
    ];

    // Randomly pick one color from the array
    $profileBgColor = $googleColors[array_rand($googleColors)];

    // Check if the email already exists
    $checkEmailQuery = "SELECT AdminEmail FROM admintb WHERE AdminEmail = '$email'";
    $count = $connect->query($checkEmailQuery)->num_rows;

    if ($count > 0) {
        $alertMessage = 'Email you signed up with is already taken.';
    } else if (empty($role)) {
        $alertMessage = 'Please select a role to create an account.';
    } else {
        // Insert the new user data
        $insertQuery = "INSERT INTO admintb (AdminID, ProfileBgColor, FirstName, LastName, UserName, AdminEmail, AdminPassword, AdminPhone, RoleID) 
                        VALUES ('$adminID', '$profileBgColor', '$firstname', '$lastname', '$username', '$email', '$password', '$phone', '$role')";
        $insert_Query = $connect->query($insertQuery);

        if ($insert_Query) {
            $_SESSION["welcome_message"] = "Welcome";
            $_SESSION["AdminID"] = $adminID;
            $_SESSION["UserName"] = $username;
            $_SESSION["AdminEmail"] = $email;
            $signupSuccess = true;
        } else {
            $alertMessage = 'Error creating account. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opulence Haven|Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../CSS/output.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../CSS/input.css?v=<?php echo time(); ?>">
</head>

<body class="flex justify-center items-center min-h-screen min-w-[380px]">
    <div class="p-3">
        <!-- Logo -->
        <div class="flex items-end gap-1 select-none">
            <a href="../User/AdminDashboard.php">
                <img src="../UserImages/Screenshot_2024-11-29_201534-removebg-preview.png" class="w-28 select-none" alt="Logo">
            </a>
            <p class="text-amber-500 text-sm font-semibold">ADMIN</p>
        </div>
        <h1 class="text-2xl font-bold mt-3 max-w-96">Create your admin account to manage operations</h1>
        <form class="flex flex-col space-y-4 w-full mt-5" action="<?php $_SERVER["PHP_SELF"] ?>" method="post" id="signupForm">

            <div class="flex flex-col">
                <p class="font-semibold text-xs mb-1">Sign up with your email</p>
                <!-- Firstname Input -->
                <div class="flex flex-col sm:flex-row gap-4 sm:gap-2">
                    <div class="relative w-full">
                        <input
                            id="firstnameInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="text"
                            name="firstname"
                            placeholder="Enter your firstname">
                        <small id="firstnameError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>
                    <!-- Lastname Input -->
                    <div class="relative w-full">
                        <input
                            id="lastnameInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="text"
                            name="lastname"
                            placeholder="Enter your lastname">
                        <small id="lastnameError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>
                </div>
            </div>

            <!-- Username Input -->
            <div class="relative">
                <input
                    id="usernameInput"
                    class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                    type="text"
                    name="username"
                    placeholder="Enter your username">
                <small id="usernameError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 sm:gap-2">
                <!-- Email Input -->
                <div class="relative w-full">
                    <input
                        id="emailInput"
                        class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                        type="email"
                        name="email"
                        placeholder="Enter your email">
                    <small id="emailError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                </div>

                <!-- Password Input -->
                <div class="flex flex-col relative w-full">
                    <div class="flex items-center justify-between border rounded">
                        <input id="signupPasswordInput"
                            class="p-2 w-full pr-10 rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="password"
                            name="password"
                            placeholder="Enter your password">
                        <i id="togglePassword" class="absolute right-1 ri-eye-line p-2 cursor-pointer"></i>
                    </div>
                    <small id="signupPasswordError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                </div>
            </div>

            <!-- Phone Input -->
            <div class="relative">
                <input
                    id="phone"
                    class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                    type="tel"
                    name="phone"
                    placeholder="Enter your phone">
                <small id="phoneError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
            </div>

            <div class="flex flex-col sm:flex-row items-end gap-3 sm:gap-1">
                <!-- Position Input -->
                <div class="flex flex-col space-y-1 w-full relative">
                    <select name="roleSelect" class="p-2 border rounded">
                        <option value="" disabled selected>Select your role</option>
                        <?php
                        $select = "SELECT * FROM roletb";
                        $query = $connect->query($select);
                        $count = $query->num_rows;

                        if ($count) {
                            for ($i = 0; $i < $count; $i++) {
                                $row = $query->fetch_assoc();
                                $role_id = $row['RoleID'];
                                $role = $row['Role'];

                                echo "<option value= '$role_id'>$role</option>";
                            }
                        } else {
                            echo "<option value='' disabled>No data yet</option>";
                        }
                        ?>
                    </select>
                    <small id="roleError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                </div>
            </div>

            <!-- reCAPTCHA -->
            <div class="flex justify-center">
                <div class="g-recaptcha transform scale-75 md:scale-100" data-sitekey="6LcE3G0pAAAAAE1GU9UXBq0POWnQ_1AMwyldy8lX"></div>
            </div>

            <!-- Signup Button -->
            <input
                class="bg-amber-500 font-semibold text-white px-4 py-2 rounded-md hover:bg-amber-600 cursor-pointer transition-colors duration-200"
                type="submit"
                name="signup"
                value="Sign Up">

            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-200"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-3 bg-white text-xs text-gray-500">Already a member of OPULENCE?</span>
                </div>
            </div>

            <!-- Signin Button -->
            <a href="AdminSignIn.php" class="relative text-center px-4 py-2 rounded-md cursor-pointer select-none group">
                <p class="relative z-10 text-blue-900 font-semibold">Sign In Now</p>
                <div class="absolute inset-0 rounded-md group-hover:bg-gray-100 transition-colors duration-300"></div>
            </a>
        </form>

        <!-- Include reCAPTCHA Script -->
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    </div>

    <!-- Loader -->
    <?php
    include('../includes/Alert.php');
    include('../includes/Loader.php');
    ?>

    <script type="module" src="../JS/adminAuth.js"></script>
</body>

</html>