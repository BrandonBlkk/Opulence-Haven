<?php
session_start();
require_once('../config/db_connection.php');
include_once('../includes/auto_id_func.php');

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../config');
$dotenv->load();

$alertMessage = '';
$response = ['success' => false, 'message' => ''];
$userID = AutoID('usertb', 'UserID', 'USR-', 6);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signup'])) {
    $username = mysqli_real_escape_string($connect, trim($_POST['username']));
    $email = mysqli_real_escape_string($connect, trim($_POST['email']));
    $password = mysqli_real_escape_string($connect, trim($_POST['password']));
    $phone = mysqli_real_escape_string($connect, trim($_POST['phone']));

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

    $profileBgColor = $googleColors[array_rand($googleColors)];

    // Check if the email already exists
    $checkEmailQuery = $connect->prepare("SELECT UserEmail FROM usertb WHERE UserEmail = ?");
    $checkEmailQuery->bind_param("s", $email);
    $checkEmailQuery->execute();
    $checkEmailQuery->store_result();
    $count = $checkEmailQuery->num_rows;

    if ($count > 0) {
        $response['message'] = 'Email you signed up with is already taken.';
    } else {
        // Hash the password
        $password = password_hash($password, PASSWORD_DEFAULT);

        // Insert the new user data
        $insert_Query = $connect->prepare("INSERT INTO usertb (UserID, UserName, UserEmail, UserPassword, UserPhone, ProfileBgColor) 
                        VALUES (? , ?, ?, ?, ?, ?)");
        $insert_Query->bind_param("ssssss", $userID, $username, $email, $password, $phone, $profileBgColor);
        $insert_Query->execute();
        $insert_Query->get_result();

        if ($insert_Query) {
            $_SESSION["welcome_message"] = "Welcome";
            $_SESSION["UserID"] = $userID;
            $_SESSION["UserName"] = $username;
            $_SESSION["UserEmail"] = $email;
            $response['success'] = true;

            // Store email data in session to send after page reload
            $_SESSION['email_data'] = [
                'email' => $email,
                'username' => $username
            ];
        }
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
    <!-- Add the following line to include Remix Icon -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>

<body class="flex justify-center items-center min-h-screen min-w-[380px]">
    <div class="flex flex-col-reverse md:flex-row justify-center gap-5 sm:gap-10 p-3">
        <div class="signinCon pb-0 sm:pb-5">
            <div class="max-w-[450px] hidden sm:block select-none">
                <img src="../UserImages/Screenshot 2024-12-01 002052.png" class="w-full h-[400px] object-cover rounded-t-lg" alt="Image">
            </div>
            <div class="px-0 sm:px-3">
                <h1 class="text-xl font-bold mt-0 sm:mt-10">Get ready to:</h1>
                <div class="flex items-center gap-2 mt-2">
                    <i class="ri-check-line font-semibold text-amber-500"></i>
                    <p>Reserve your perfect room with access to exclusive offers</p>
                </div>
                <div class="flex items-center gap-2 mt-2">
                    <i class="ri-check-line font-semibold text-amber-500"></i>
                    <p>Access your reservation from any device, anytime, anywhere</p>
                </div>
            </div>
        </div>
        <div>
            <a href="home_page.php">
                <img src="../UserImages/Screenshot_2024-11-29_201534-removebg-preview.png" class="w-28 select-none" alt="Logo">
            </a>
            <h1 class="text-2xl font-bold mt-10 sm:mt-20 max-w-96">Unlock more savings as a member</h1>
            <form class="flex flex-col space-y-4 w-full mt-5" action="<?php $_SERVER["PHP_SELF"] ?>" method="post" id="signupForm">
                <!-- Username Input -->
                <div class="relative">
                    <p class="font-semibold text-xs mb-1">Sign up with your email</p>
                    <input
                        id="username"
                        class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                        type="text"
                        name="username"
                        placeholder="Enter your username">
                    <small id="usernameError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                </div>

                <div class="flex flex-col sm:flex-row gap-4 sm:gap-2">
                    <!-- Email Input -->
                    <div class="relative">
                        <input
                            id="emailInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="email"
                            name="email"
                            placeholder="Enter your email">
                        <small id="emailError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>

                    <!-- Password Input -->
                    <div class="flex flex-col relative">
                        <div class="flex items-center justify-between border rounded">
                            <input id="passwordInput"
                                class="p-2 w-full pr-10 rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                type="password"
                                name="password"
                                placeholder="Enter your password">
                            <i id="togglePassword" class="absolute right-1 ri-eye-line p-2 cursor-pointer"></i>
                        </div>
                        <small id="passwordError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
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

                <!-- reCAPTCHA -->
                <div class="flex justify-center">
                    <div class="g-recaptcha transform scale-75 md:scale-100"
                        data-sitekey="<?php echo $_ENV["RECAPTCHA_SITE_KEY"]; ?>">
                    </div>
                </div>

                <input type="hidden" name="signup" value="1">

                <!-- Signup Button -->
                <input
                    class="bg-amber-500  font-semibold text-white px-4 py-2 rounded-md hover:bg-amber-600 cursor-pointer transition-colors duration-200"
                    type="submit"
                    id="signup"
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
                <a href="user_signin.php" class="relative text-center px-4 py-2 rounded-md cursor-pointer select-none group">
                    <p class="relative z-10 text-blue-900 font-semibold">Sign In Now</p>
                    <div class="absolute inset-0 rounded-md group-hover:bg-gray-100 transition-colors duration-300"></div>
                </a>
                <p class="text-xs text-slate-700">By creating an account, you agree to our
                    <a href="../Policies/privacy_policy.php" class="hover:underline underline-offset-2">Privacy policy</a> and
                    <a href="../Policies/terms_of_use.php" class="hover:underline underline-offset-2">Terms of use</a>.
                </p>
            </form>
        </div>
    </div>

    <!-- Hidden fields for JavaScript -->
    <input type="hidden" id="alertMessage" value="<?php echo htmlspecialchars($alertMessage); ?>">
    <input type="hidden" id="signupSuccess" value="<?php echo $signupSuccess ? 'true' : 'false'; ?>">

    <!-- Loader -->
    <?php
    include('../includes/alert.php');
    include('../includes/loader.php');
    ?>

    <script type="module" src="../JS/auth.js"></script>
</body>

</html>