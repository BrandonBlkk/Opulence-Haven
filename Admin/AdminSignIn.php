<?php
session_start();
include('../config/dbConnection.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

$alertMessage = '';
$response = ['success' => false, 'message' => '', 'locked' => false, 'attemptsLeft' => null];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signin'])) {
    $email = mysqli_real_escape_string($connect, trim($_POST['email']));
    $password = mysqli_real_escape_string($connect, trim($_POST['password']));

    // Check if the email exists
    $checkEmailQuery = "SELECT * FROM admintb 
    WHERE AdminEmail = '$email'";
    $emailExist = $connect->query($checkEmailQuery)->num_rows;

    if (!$emailExist) {
        $response['message'] = "No account found with the provided email. Please try again.";
    } else {
        // Check if the email exists and fetch data
        $checkAccQuery = "SELECT * FROM admintb 
        WHERE AdminEmail = '$email' AND AdminPassword = '$password';";
        $rowCount = $connect->query($checkAccQuery)->num_rows;

        // Initialize or reset sign-in attempt counter based on email consistency
        if (!isset($_SESSION['last_email']) || $_SESSION['last_email'] !== $email) {
            $_SESSION['signin_attempts'] = 0;
            $_SESSION['last_email'] = $email;
        }

        // Check customer account match with signup account
        if ($rowCount > 0) {
            $array = $connect->query($checkAccQuery)->fetch_assoc();
            $admin_id = $array["AdminID"];
            $admin_username = $array["UserName"];
            $admin_email = $array["AdminEmail"];
            $role = $array["RoleID"];
            $last_signin = $array["LastSignIn"];

            $_SESSION["UserName"] = $admin_username;
            $_SESSION["AdminID"] = $admin_id;
            $_SESSION["UserName"] = $admin_username;
            $_SESSION["AdminEmail"] = $admin_email;
            $_SESSION["RoleID"] = $role;
            // Determine welcome message
            if ($last_signin === null) {
                $_SESSION["welcome_message"] = "Welcome, " . $admin_username . "!";
            } else {
                $_SESSION["welcome_message"] = "Welcome back, " . $admin_username . "!";
            }

            $updateSignInQuery = "UPDATE admintb SET Status = 'active' 
            WHERE AdminID = '$admin_id'";
            $connect->query($updateSignInQuery);

            // Reset sign-in attempts on successful sign-in
            $_SESSION['signin_attempts'] = 0;
            $_SESSION['last_email'] = null;
            $response['success'] = true;
        } else {
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
        <h1 class="text-2xl font-bold mt-3 max-w-96">Welcome back! access your admin account</h1>
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
                        id="signinPasswordInput"
                        class="p-2 w-full pr-10 rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                        type="password"
                        name="password"
                        placeholder="Enter your password">
                    <i id="togglePassword2" class="absolute right-1 ri-eye-line p-2 cursor-pointer"></i>
                </div>
                <small id="signinPasswordError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
            </div>

            <a href="ForgetPassword.php" class="text-xs text-gray-400 hover:text-gray-500">Forget your password?</a>

            <input type="hidden" name="signin" value="1">

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
            <a href="AdminSignUp.php" class="relative text-center px-4 py-2 rounded-md cursor-pointer select-none group">
                <p class="relative z-10 text-blue-900 font-semibold">Sign Up Now</p>
                <div class="absolute inset-0 rounded-md group-hover:bg-gray-100 transition-colors duration-300"></div>
            </a>
        </form>
    </div>

    <!-- Loader -->
    <?php
    include('../includes/Alert.php');
    include('../includes/Loader.php');
    ?>

    <script type="module" src="../JS/adminAuth.js"></script>
</body>

</html>