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
    $role = mysqli_real_escape_string($connect, $_POST['role']);

    // Admin image upload 
    $adminProfile = $_FILES["adminprofile"]["name"];
    $copyFile = "AdminImages/";
    $fileName = $copyFile . uniqid() . "_" . $adminProfile;
    $copy = copy($_FILES["adminprofile"]["tmp_name"], $fileName);

    if (!$copy) {
        echo "<p>Cannot upload Profile Image.</p>";
        exit();
    }

    // Check if the email already exists using prepared statement
    $checkEmailQuery = "SELECT AdminEmail FROM admintb WHERE AdminEmail = '$email'";

    $checkEmailQuery = mysqli_query($connect, $checkEmailQuery);
    $count = mysqli_num_rows($checkEmailQuery);

    if ($count > 0) {
        $alertMessage = 'Email you signed up with is already taken.';
    } else {
        // Insert the new user data using prepared statement
        $insertQuery = "INSERT INTO admintb (AdminID ,AdminProfile, FirstName, LastName, UserName, AdminEmail, AdminPassword, AdminPhone, RoleID) 
                        VALUES ('$adminID', '$fileName', '$firstname', '$lastname', '$username', '$email', '$password', '$phone', '$role')";
        $insert_Query = mysqli_query($connect, $insertQuery);

        if ($insert_Query) {
            $signupSuccess = true;
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/3.5.0/remixicon.css" integrity="sha512-HXXR0l2yMwHDrDyxJbrMD9eLvPe3z3qL3PPeozNTsiHJEENxx8DH2CxmV05iwG0dwoz5n4gQZQyYLUNt1Wdgfg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../CSS/output.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../CSS/input.css?v=<?php echo time(); ?>">
</head>

<body class="flex justify-center items-center min-h-screen">
    <div class="p-3">
        <!-- Logo -->
        <div class="flex items-end gap-1 select-none">
            <a href="../User/AdminDashboard.php">
                <img src="../UserImages/Screenshot_2024-11-29_201534-removebg-preview.png" class="w-28 select-none" alt="Logo">
            </a>
            <p class="text-amber-500 text-sm font-semibold">ADMIN</p>
        </div>
        <h1 class="text-2xl font-bold mt-3 max-w-96">Create your admin account to manage operations</h1>
        <form class="flex flex-col space-y-4 w-full mt-5" action="<?php $_SERVER["PHP_SELF"] ?>" method="post" enctype="multipart/form-data" id="signupForm">

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
                            class="p-2 w-full rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="password"
                            name="password"
                            placeholder="Enter your password">
                        <i id="togglePassword" class="ri-eye-line p-2 cursor-pointer"></i>
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

            <div class="flex items-end gap-1">
                <!-- Profile -->
                <div class="relative">
                    <label for="adminprofile" class="block text-sm font-medium text-gray-700 mb-1">Profile Picture</label>
                    <input
                        type="file"
                        name="adminprofile"
                        id="adminprofile"
                        class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out">
                    <small id="profileError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                </div>
                <!-- Position Input -->
                <div class="flex flex-col space-y-1">
                    <select name="role" class="p-2 border rounded">
                        <option value="" disabled selected>Select your role</option>
                        <?php
                        $select = "SELECT * FROM roletb";
                        $query = mysqli_query($connect, $select);
                        $count = mysqli_num_rows($query);

                        if ($count) {
                            for ($i = 0; $i < $count; $i++) {
                                $row = mysqli_fetch_array($query);
                                $role_id = $row['RoleID'];
                                $role = $row['Role'];

                                echo "<option value= '$role_id'>$role</option>";
                            }
                        } else {
                            echo "<option value='' disabled>No data yet</option>";
                        }
                        ?>
                    </select>
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