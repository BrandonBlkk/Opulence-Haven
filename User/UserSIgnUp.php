<?php
include('../config/dbConnection.php');

$alertMessage = '';
$signupSuccess = false;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signup'])) {
    $username = mysqli_real_escape_string($connect, trim($_POST['username']));
    $email = mysqli_real_escape_string($connect, trim($_POST['email']));
    $password = mysqli_real_escape_string($connect, trim($_POST['password']));
    $phone = mysqli_real_escape_string($connect, trim($_POST['phone']));
    $signupDate = mysqli_real_escape_string($connect, $_POST['signupdate']);

    // Check if the email already exists using prepared statement
    $checkEmailQuery = "SELECT UserEmail FROM usertb WHERE UserEmail = '$email'";

    $checkEmailQuery = mysqli_query($connect, $checkEmailQuery);
    $count = mysqli_num_rows($checkEmailQuery);

    if ($count > 0) {
        $alertMessage = 'Email you signed up with is already taken.';
    } else {
        // Insert the new user data using prepared statement
        $insertQuery = "INSERT INTO usertb (UserName, UserEmail, UserPassword, UserPhone, SignupDate, Status) 
                        VALUES ('$username', '$email', '$password', '$phone', '$signupDate', 'inactive')";
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
                    <small id="usernameError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none">Username is required.</small>
                </div>

                <div class="flex flex-col sm:flex-row gap-4 sm:gap-2">
                    <!-- Email Input -->
                    <div class="relative">
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
                    <input
                        id="phone"
                        class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                        type="tel"
                        name="phone"
                        placeholder="Enter your phone">
                    <small id="phoneError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none">Phone is required.</small>
                </div>

                <!-- Date Input -->
                <input type="date" class="hidden" id="signupdate" name="signupdate" value="<?php echo date("Y-m-d") ?>">

                <!-- reCAPTCHA -->
                <div class="flex justify-center">
                    <div class="g-recaptcha" data-sitekey="6LcE3G0pAAAAAE1GU9UXBq0POWnQ_1AMwyldy8lX"></div>
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
                <a href="UserSignIn.php" class="relative text-center px-4 py-2 rounded-md cursor-pointer select-none group">
                    <p class="relative z-10 text-blue-900 font-semibold">Sign In Now</p>
                    <div class="absolute inset-0 rounded-md group-hover:bg-gray-100 transition-colors duration-300"></div>
                </a>
                <p class="text-xs text-slate-700">By creating an account, you agree to our
                    <a href="../Policies/PrivacyPolicy.php" class="hover:underline underline-offset-2">Privacy policy</a> and
                    <a href="../Policies/TermOfUse.php" class="hover:underline underline-offset-2">Terms of use</a>.
                </p>
            </form>

            <!-- Include reCAPTCHA Script -->
            <script src="https://www.google.com/recaptcha/api.js" async defer></script>
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