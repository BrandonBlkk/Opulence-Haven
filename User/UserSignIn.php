<?php
session_start();
include('../config/dbConnection.php');

$alertMessage = '';
$signinSuccess = false;
$isAccountLocked = false;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signin'])) {
    $email = mysqli_real_escape_string($connect, trim($_POST['email']));
    $password = mysqli_real_escape_string($connect, trim($_POST['password']));
    $lastSignIn = mysqli_real_escape_string($connect, $_POST['signindate']);

    // Check if the email exists
    $checkEmailQuery = "SELECT * FROM usertb 
    WHERE UserEmail = '$email'";
    $check_email_query = mysqli_query($connect, $checkEmailQuery);
    $emailExist = mysqli_num_rows($check_email_query);

    if (!$emailExist) {
        $alertMessage = "No account found with the provided email. Please try again.";
    } else {
        // Check if the email exists and fetch data
        $checkAccQuery = "SELECT * FROM usertb 
        WHERE UserEmail = '$email' AND UserPassword = '$password';";
        $check_account_query = mysqli_query($connect, $checkAccQuery);
        $rowCount = mysqli_num_rows($check_account_query);

        // Initialize or reset sign-in attempt counter based on email consistency
        if (!isset($_SESSION['last_email']) || $_SESSION['last_email'] !== $email) {
            $_SESSION['signin_attempts'] = 0;
            $_SESSION['last_email'] = $email;
        }

        // Check customer account match with signup account
        if ($rowCount > 0) {
            $array = mysqli_fetch_array($check_account_query);
            $user_id = $array["UserID"];
            $user_username = $array["UserName"];
            $user_email = $array["UserEmail"];

            $_SESSION["UserID"] = $user_id;
            $_SESSION["UserName"] = $user_username;
            $_SESSION["UserEmail"] = $user_email;

            $updateSignInQuery = "UPDATE usertb SET LastSignIn = '$lastSignIn',
            Status = 'active' WHERE UserID = '$user_id'";
            mysqli_query($connect, $updateSignInQuery);

            // Reset sign-in attempts on successful sign-in
            $_SESSION['signin_attempts'] = 0;
            $_SESSION['last_email'] = null;
            $signinSuccess = true;
        } else {
            // Increment sign-in attempt counter for the same email
            $_SESSION['signin_attempts']++;

            // Check if sign-in attempts exceed limit
            if ($_SESSION['signin_attempts'] === 3) {
                // Reset sign-in attempts
                $_SESSION['signin_attempts'] = 0;
                $isAccountLocked = true;
            } else if ($_SESSION['signin_attempts'] === 2) {
                $alertMessage = "Multiple failed attempts. One more may lock your account temporarily.";
            } else {
                $alertMessage = "The password you entered is incorrect. Please try again.";
            }
        }
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
    <link rel="stylesheet" href="../CSS/output.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../CSS/input.css?v=<?php echo time(); ?>">
</head>

<body class="flex justify-center items-center min-h-screen">
    <div class="flex flex-col-reverse md:flex-row justify-center gap-5 sm:gap-10 p-3">
        <div class="signinCon pb-0 sm:pb-5">
            <div class="max-w-[450px] hidden sm:block">
                <img src="../UserImages/Screenshot 2024-12-01 002052.png" class="w-full h-[400px] object-cover rounded-t-lg" alt="Image">
            </div>
            <div class="px-0 sm:px-3">
                <h1 class="text-xl font-bold mt-0 sm:mt-10">Get ready to:</h1>
                <div class="flex items-center gap-2 mt-2">
                    <i class="ri-check-line font-semibold text-amber-500"></i>
                    <p>Book your perfect room with access to exclusive offers</p>
                </div>
                <div class="flex items-center gap-2 mt-2">
                    <i class="ri-check-line font-semibold text-amber-500"></i>
                    <p>Access your booking from any device, anytime, anywhere</p>
                </div>
            </div>
        </div>
        <div>
            <a href="../Homepage.php">
                <img src="../UserImages/Screenshot_2024-11-29_201534-removebg-preview.png" class="w-28 select-none" alt="Logo">
            </a>
            <h1 class="text-2xl font-bold mt-10 sm:mt-20 max-w-96">Welcome back! access your account</h1>
            <form class="flex flex-col space-y-4 w-full mt-5" action="<?php $_SERVER["PHP_SELF"] ?>" method="post" id="signinForm">

                <!-- Email Input -->
                <div class="relative">
                    <p class="font-semibold text-xs mb-1">Sign in with your email</p>
                    <input
                        id="email"
                        class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                        type="email"
                        name="email"
                        placeholder="Enter your email">
                    <small id="emailError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none">Email is required.</small>
                </div>

                <!-- Password Input -->
                <div class="flex flex-col relative">
                    <div class="flex items-center justify-between border rounded">
                        <input
                            id="passwordInput2"
                            class="p-2 w-full rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="password"
                            name="password"
                            placeholder="Enter your password">
                        <i id="togglePassword2" class="ri-eye-line p-2 cursor-pointer"></i>
                    </div>
                    <small id="passwordError2" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none">Password is required.</small>
                </div>

                <a href="ForgetPassword.php" class="text-xs text-gray-400 hover:text-gray-500">Forget your password?</a>

                <!-- Date Input -->
                <input type="hidden" id="signindate" name="signindate" value="<?php echo date("Y-m-d h:i:s"); ?>">


                <!-- Signin Button -->
                <input
                    class=" bg-amber-500 font-semibold text-white px-4 py-2 rounded-md hover:bg-amber-600 cursor-pointer transition-colors duration-200"
                    type="submit"
                    name="signin"
                    value="Sign In">

                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-3 bg-white text-xs text-gray-500">new to OPULENCE?</span>
                    </div>
                </div>

                <!-- Signup Button -->
                <a href="UserSignUp.php" class="relative text-center px-4 py-2 rounded-md cursor-pointer select-none group">
                    <p class="relative z-10 text-blue-900 font-semibold">Sign Up Now</p>
                    <div class="absolute inset-0 rounded-md group-hover:bg-gray-100 transition-colors duration-300"></div>
                </a>
                <p class="text-xs text-slate-700">By signing in, you agree to our
                    <a href="PrivacyPolicy.php" class="hover:underline underline-offset-2">Privacy Policy</a> and
                    <a href="TermOfUse.php" class="hover:underline underline-offset-2">Terms of Use</a>.
                </p>
            </form>
        </div>
    </div>

    <!-- Loader -->
    <?php
    include('../includes/Alert.php');
    include('../includes/Loader.php');
    ?>

    <script type="module" src="../JS/auth.js"></script>
</body>

</html>