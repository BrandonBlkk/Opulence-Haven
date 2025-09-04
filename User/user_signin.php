<?php
session_start();
require_once('../config/db_connection.php');
include_once('../includes/auto_id_func.php');
require_once '../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../config');
$dotenv->load();

$alertMessage = '';
$response = ['success' => false, 'message' => '', 'locked' => false, 'attemptsLeft' => null];

$client = new Google\Client();
$client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
$client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
$client->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI']);
$client->addScope('email');
$client->addScope('profile');

$url = $client->createAuthUrl();

// Handle Google OAuth Callback
if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

    if (!isset($token['error'])) {
        $client->setAccessToken($token['access_token']);
        $google_oauth = new Google\Service\Oauth2($client);
        $google_account_info = $google_oauth->userinfo->get();

        $email = $google_account_info['email'];
        $name = $google_account_info['name'];
        $picture = $google_account_info['picture'];

        // Check if the email already exists
        $checkEmailQuery = $connect->prepare("SELECT * FROM usertb WHERE UserEmail = ?");
        $checkEmailQuery->bind_param("s", $email);
        $checkEmailQuery->execute();
        $result = $checkEmailQuery->get_result();

        if ($result->num_rows > 0) {
            $array = $result->fetch_assoc();
            $user_id = $array["UserID"];
            $user_username = $array["UserName"];
            $last_signin = $array["LastSignIn"];

            $_SESSION["UserID"] = $user_id;
            $_SESSION["UserName"] = $user_username;
            $_SESSION["UserEmail"] = $email;
            $_SESSION["welcome_message"] = $last_signin === null ? "Welcome" : "Welcome back";

            $stmt = $connect->prepare("UPDATE usertb SET Status = ?, LastSignIn = NOW() WHERE UserID = ?");
            $status = 'active';
            $stmt->bind_param("ss", $status, $user_id);
            $stmt->execute();
            $stmt->close();
        } else {
            $userID = AutoID('usertb', 'UserID', 'USR-', 6);
            $colors = ['#4285F4', '#EA4335', '#FBBC05', '#34A853', '#673AB7', '#FF6D00', '#00ACC1', '#D81B60', '#8E24AA', '#039BE5', '#7CB342', '#F4511E', '#546E7A', '#E53935', '#00897B', '#5E35B1'];
            $bgColor = $colors[array_rand($colors)];

            $insertQuery = $connect->prepare("INSERT INTO usertb (
                UserID, UserName, UserEmail, UserPassword, Status, LastSignIn, Profile, ProfileBgColor, SignupDate
            ) VALUES (?, ?, ?, ?, ?, NOW(), ?, ?, NOW())");
            $insertQuery->bind_param("sssssss", $userID, $name, $email, $password, $status, $picture, $bgColor);
            $status = 'active';
            $insertQuery->execute();
            $insertQuery->get_result();

            if ($insertQuery) {
                $_SESSION["UserID"] = $userID;
                $_SESSION["UserName"] = $name;
                $_SESSION["UserEmail"] = $email;
                $_SESSION["welcome_message"] = "Welcome";
            } else {
                die("Error creating account.");
            }
        }

        header("Location: home_page.php");
        exit;
    } else {
        echo "Google Sign-In Failed";
    }
}

// If user clicked "Continue with Google"
elseif (isset($_GET['google'])) {
    header("Location: $url");
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signin'])) {
    $email = mysqli_real_escape_string($connect, trim($_POST['email']));
    $password = mysqli_real_escape_string($connect, trim($_POST['password']));

    // Check if the email exists
    $checkEmailQuery = $connect->prepare("SELECT * FROM usertb WHERE UserEmail = ?");
    $checkEmailQuery->bind_param("s", $email);
    $checkEmailQuery->execute();
    $result = $checkEmailQuery->get_result();

    // Check if any rows were returned
    $emailExist = $result->num_rows;

    if (!$emailExist) {
        $response['message'] = 'No account found with the provided email. Please try again.';
    } else {
        // Fetch the user data including the hashed password
        $userData = $result->fetch_assoc();

        // Verify the password against the hashed password in database
        if (password_verify($password, $userData['UserPassword'])) {
            // Password is correct
            $user_id = $userData["UserID"];
            $user_username = $userData["UserName"];
            $user_email = $userData["UserEmail"];
            $last_signin = $userData["LastSignIn"];

            $_SESSION["UserID"] = $user_id;
            $_SESSION["UserName"] = $user_username;
            $_SESSION["UserEmail"] = $user_email;

            // Determine welcome message
            if ($last_signin === null) {
                $_SESSION["welcome_message"] = "Welcome";
            } else {
                $_SESSION["welcome_message"] = "Welcome back";
            }

            // Update sign-in status
            $updateSignInQuery = "UPDATE usertb SET Status = ?, LastSignIn = NOW() WHERE UserID = ?";
            $stmt = $connect->prepare($updateSignInQuery);
            $status = 'active';
            $stmt->bind_param("si", $status, $user_id);
            $stmt->execute();
            $stmt->close();

            // Reset sign-in attempts on successful sign-in
            $_SESSION['signin_attempts'] = 0;
            $_SESSION['last_email'] = null;

            $response['success'] = true;
        } else {
            // Password is incorrect
            // Initialize or reset sign-in attempt counter based on email consistency
            if (!isset($_SESSION['last_email']) || $_SESSION['last_email'] !== $email) {
                $_SESSION['signin_attempts'] = 0;
                $_SESSION['last_email'] = $email;
            }

            // Increment sign-in attempt counter for the same email
            $_SESSION['signin_attempts']++;

            // Check if sign-in attempts exceed limit
            if ($_SESSION['signin_attempts'] === 3) {
                // Reset sign-in attempts
                $_SESSION['signin_attempts'] = 0;
                $response['locked'] = true;
            } else if ($_SESSION['signin_attempts'] === 2) {
                $response['message'] = 'Multiple failed attempts. One more may lock your account temporarily.';
            } else {
                $response['message'] = 'The password you entered is incorrect. Please try again.';
            }
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
            <h1 class="text-2xl font-bold mt-10 sm:mt-20 max-w-96">Welcome back! access your account</h1>
            <form class="flex flex-col space-y-4 w-full mt-5" action="<?php $_SERVER["PHP_SELF"] ?>" method="post" id="signinForm">

                <!-- Email Input -->
                <div class="relative">
                    <p class="font-semibold text-xs mb-1">Sign in with your email</p>
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
                        <input
                            id="passwordInput2"
                            class="p-2 w-full rounded pr-10 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="password"
                            name="password"
                            placeholder="Enter your password">
                        <i id="togglePassword2" class="absolute right-1 ri-eye-line p-2 cursor-pointer"></i>
                    </div>
                    <small id="passwordError2" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                </div>

                <a href="forget_password.php" class="text-xs text-gray-400 hover:text-gray-500">Forget your password?</a>

                <input type="hidden" name="signin" value="1">

                <!-- Signin Button -->
                <input
                    class=" bg-amber-500 font-semibold text-white px-4 py-2 rounded-md hover:bg-amber-600 cursor-pointer transition-colors duration-200"
                    type="submit"
                    name="signin"
                    value="Sign In">

                <a href="<?= $client->createAuthUrl() ?>" id="googleSignInBtn" class="flex items-center justify-center gap-2 border border-gray-200 rounded-md px-4 py-2 font-semibold text-slate-700 hover:bg-gray-50 transition-colors duration-200 select-none">
                    <!-- Google Icon (visible by default) -->
                    <svg id="googleIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" class="block">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                    </svg>

                    <!-- Loading Spinner (hidden by default) -->
                    <span id="googleSpinner" class="hidden animate-spin w-5 h-5 border-[3px] border-current border-t-transparent text-amber-500 rounded-full"></span>

                    <!-- Button Text -->
                    <span>Sign in with Google</span>
                </a>

                <script>
                    document.addEventListener('DOMContentLoaded', () => {
                        const googleSignInBtn = document.getElementById('googleSignInBtn');
                        const googleIcon = document.getElementById('googleIcon');
                        const googleSpinner = document.getElementById('googleSpinner');

                        if (googleSignInBtn && googleIcon && googleSpinner) {
                            googleSignInBtn.addEventListener('click', function(e) {
                                // Only prevent default if we're not in the callback phase
                                if (!window.location.href.includes('?code=')) {
                                    // Show loading state
                                    googleIcon.classList.add('hidden');
                                    googleSpinner.classList.remove('hidden');
                                    window.location.href = googleSignInBtn.href;
                                }
                            });
                        }

                        // Reset button state if page loads again 
                        window.addEventListener('load', function() {
                            googleIcon.classList.remove('hidden');
                            googleSpinner.classList.add('hidden');
                        });
                    });
                </script>

                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-3 bg-white text-xs text-gray-500">new to OPULENCE?</span>
                    </div>
                </div>

                <!-- Signup Button -->
                <a href="user_signup.php" class="relative text-center px-4 py-2 rounded-md cursor-pointer select-none group">
                    <p class="relative z-10 text-blue-900 font-semibold">Sign Up Now</p>
                    <div class="absolute inset-0 rounded-md group-hover:bg-gray-100 transition-colors duration-300"></div>
                </a>

                <p class="text-xs text-slate-700">By signing in, you agree to our
                    <a href="../Policies/privacy_policy.php" class="hover:underline underline-offset-2">Privacy Policy</a> and
                    <a href="../Policies/terms_of_use.php" class="hover:underline underline-offset-2">Terms of Use</a>.
                </p>
            </form>
        </div>
    </div>

    <!-- Loader -->
    <?php
    include('../includes/alert.php');
    include('../includes/loader.php');
    ?>

    <script type="module" src="../JS/auth.js"></script>
</body>

</html>