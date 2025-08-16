<?php
session_start();
require_once('../config/db_connection.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Timezone 
date_default_timezone_set('Asia/Yangon');

$alertMessage = '';
$addRoleSuccess = false;
$deleteAdminSuccess = false;
$resetAdminPasswordSuccess = false;
$response = ['success' => false, 'message' => ''];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['addrole'])) {
    $role = mysqli_real_escape_string($connect, $_POST['role']);
    $description = mysqli_real_escape_string($connect, $_POST['description']);

    $addRoleQuery = "INSERT INTO roletb (Role, Description)
    VALUES ('$role', '$description')";

    if ($connect->query($addRoleQuery)) {
        $addRoleSuccess = true;
    } else {
        $alertMessage = "Failed to add product type. Please try again.";
    }
}

// Get Admin Details
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = mysqli_real_escape_string($connect, $_GET['id']);
    $action = $_GET['action'];

    // Build query based on action
    $query = match ($action) {
        'getAdminDetails' => "SELECT * FROM admintb WHERE AdminID = '$id'",
        default => null
    };
    if ($query) {
        $result = $connect->query($query)->fetch_assoc();
    }

    if ($result) {
        echo json_encode(['success' => true, 'admin' => $result]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}

// Reset Admin Password by Super Admin
if (isset($_POST['resetpassword'])) {
    $adminId = mysqli_real_escape_string($connect, $_POST['adminid']);

    // First get admin details
    $adminQuery = "SELECT AdminEmail, FirstName, LastName FROM admintb WHERE AdminID = '$adminId'";
    $adminResult = $connect->query($adminQuery);

    if ($adminResult->num_rows > 0) {
        $adminData = $adminResult->fetch_assoc();
        $adminEmail = $adminData['AdminEmail'];
        $adminName = $adminData['FirstName'] . ' ' . $adminData['LastName'];

        // Generate a random token and OTP
        $token = bin2hex(random_bytes(16));
        $otp = rand(100000, 999999);
        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Store the token and expiry in the database
        $updateQuery = "UPDATE admintb SET OTP = '$otp', Token = '$token', TokenExpiry = '$expiry' WHERE AdminEmail = '$adminEmail'";
        $connect->query($updateQuery);

        if ($updateQuery) {
            // Send email notification
            $mail = new PHPMailer(true);

            try {
                // Server settings
                $mailConfig = require __DIR__ . '/../config/mail.php';

                $mail->isSMTP();
                $mail->Host       = $mailConfig['host'];
                $mail->SMTPAuth   = true;
                $mail->Username   = $mailConfig['username'];
                $mail->Password   = $mailConfig['password'];
                $mail->SMTPSecure = $mailConfig['encryption'];
                $mail->Port       = $mailConfig['port'];

                // Recipients
                $mail->setFrom('opulencehaven25@gmail.com', 'Opulence Haven Admin');
                $mail->addAddress($adminEmail, $adminName);

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Your Admin Password Has Been Reset';
                $mail->Body = "
                        <!DOCTYPE html>
                        <html>
                        <head>
                            <meta charset='UTF-8'>
                            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                            <title>Admin Password Reset</title>
                            <style>
                                body {
                                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                                    line-height: 1.6;
                                    color: #333333;
                                    max-width: 600px;
                                    margin: 0 auto;
                                    padding: 20px;
                                }
                                .content {
                                    background-color: #ffffff;
                                    padding: 30px;
                                    border-radius: 8px;
                                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
                                }
                                .button {
                                    display: inline-block;
                                    padding: 12px 24px;
                                    background-color: #F59E0B;
                                    color: #ffffff !important;
                                    text-decoration: none;
                                    border-radius: 6px;
                                    font-weight: 600;
                                    margin: 20px 0;
                                }
                                .footer {
                                    margin-top: 30px;
                                    padding-top: 20px;
                                    border-top: 1px solid #e5e7eb;
                                    font-size: 14px;
                                    color: #6b7280;
                                    text-align: center;
                                }
                                .important-note {
                                    background-color: #f9fafb;
                                    padding: 12px;
                                    border-radius: 6px;
                                    font-size: 14px;
                                    margin: 20px 0;
                                }
                                .support {
                                    margin-top: 15px;
                                    font-size: 14px;
                                }
                            </style>
                        </head>
                        <body>    
                            <div class='content'>
                                <h2 style='margin-top: 0; color: #111827;'>Admin Password Reset</h2>

                                <p>Dear $adminName,</p>

                                <p>Your Opulence Haven admin account password has been reset by a super administrator.</p>

                                <div class='important-note'>
                                    <strong>Important:</strong> This link will expire in 1 hour or after one use for security reasons.
                                </div>

                                <p> Your verification code: <strong>$otp</strong> </p>

                                <div style='text-align: center;'>
                                    <a href='http://localhost/OpulenceHaven/Admin/reset_password.php?token=$token' class='button'>
                                        Reset Your Password
                                    </a>
                                </div>

                                <p>If you didn't request this password reset, please contact our support team immediately.</p>

                                <div class='support'>
                                    <p>Need help? <a href='mailto:support@opulencehaven.com' style='color: #4F46E5;'>Contact our support team</a></p>
                                </div>
                            </div>

                            <div class='footer'>
                                <p>© " . date('Y') . " Opulence Haven. All rights reserved.</p>
                                <p>
                                    <a href='http://localhost/OpulenceHaven/Policies/PrivacyPolicy.php' style='color: #6b7280; text-decoration: none; margin: 0 10px;'>Privacy Policy</a>
                                    <a href='http://localhost/OpulenceHaven/Policies/TermsOfUse.php' style='color: #6b7280; text-decoration: none; margin: 0 10px;'>Terms of Service</a>
                                </p>
                                <p>Opulence Haven, 459 Pyay Road, Kamayut Township, 11041 Yangon, Myanmar</p>
                            </div>
                        </body>
                        </html>
                    ";

                $mail->send();
                $response['success'] = true;
                $response['message'] = "Password reset successfully. The admin has been notified via email.";
            } catch (Exception $e) {
                $response['message'] = "Password reset but email notification failed: " . $e->getMessage();
            }
        } else {
            $response['message'] = "Database error. Please try again.";
        }
    } else {
        $response['message'] = "Admin not found.";
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Delete Admin
if (isset($_POST['deleteadmin'])) {
    $adminId = mysqli_real_escape_string($connect, $_POST['adminid']);

    // Build query based on action
    $deleteQuery = "DELETE FROM admintb WHERE AdminID = '$adminId'";

    if ($connect->query($deleteQuery)) {
        $deleteAdminSuccess = true;
    } else {
        $reponse['message'] = 'Failed to delete admin. Please try again.';
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

<body>
    <?php include('../includes/admin_navbar.php'); ?>

    <!-- Main Container -->
    <div class="flex flex-col md:flex-row md:space-x-3 p-3 ml-0 md:ml-[250px] relative min-w-[380px]">
        <!-- Left Side Content -->
        <div class="w-full bg-white p-2">
            <div class="flex justify-between items-end">
                <div>
                    <h2 class="text-xl text-gray-700 font-bold mb-4">Manage Admin Roles and Accounts</h2>
                    <p>View the list of admins and assign roles for efficient role-based access control.</p>
                </div>
                <button id="addRoleBtn" class="bg-amber-500 text-white font-semibold px-3 py-1 rounded select-none hover:bg-amber-600 transition-colors">
                    <i class="ri-add-line text-xl"></i>
                </button>
            </div>

            <!-- Admin Table -->
            <div class="overflow-x-auto">
                <!-- Admin Search and Filter -->
                <form method="GET" class="my-4 flex items-start sm:items-center justify-between flex-col sm:flex-row gap-2 sm:gap-0">
                    <h1 class="text-lg text-gray-700 font-semibold text-nowrap">All Users <span class="text-gray-400 text-sm ml-2"><?php echo $adminCount ?></span></h1>
                    <div class="flex flex-col sm:flex-row items-start sm:items-center w-full gap-2 sm:gap-0">
                        <input type="text" name="acc_search" class="p-2 ml-0 sm:ml-5 border border-gray-300 rounded-md w-full outline-none focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out" placeholder="Search for admin account..." value="<?php echo isset($_GET['acc_search']) ? htmlspecialchars($_GET['acc_search']) : ''; ?>">
                        <div class="flex items-center">
                            <label for="sort" class="ml-4 mr-2 flex items-center cursor-pointer select-none">
                                <i class="ri-filter-2-line text-xl"></i>
                                <p>Filters</p>
                            </label>
                            <!-- Search and filter form -->
                            <form method="GET" class="flex flex-col md:flex-row items-center gap-4 mb-4">
                                <select name="sort" id="sort" class="border p-2 rounded text-sm outline-none">
                                    <option value="random">All Roles</option>
                                    <?php
                                    $select = "SELECT * FROM roletb";
                                    $query = $connect->query($select);
                                    $count = $query->num_rows;

                                    if ($count) {
                                        for ($i = 0; $i < $count; $i++) {
                                            $row = $query->fetch_assoc();
                                            $role_id = $row['RoleID'];
                                            $role = $row['Role'];
                                            $selected = ($filterRoleID == $role_id) ? 'selected' : '';

                                            echo "<option value='$role_id' $selected>$role</option>";
                                        }
                                    } else {
                                        echo "<option value='' disabled>No data yet</option>";
                                    }
                                    ?>
                                </select>
                            </form>
                        </div>
                    </div>
                </form>


                <!-- Admin Table -->
                <div class="tableScrollBar overflow-y-auto max-h-[495px]">
                    <div id="adminResults">
                        <?php include '../includes/admin_table_components/role_management_results.php'; ?>
                    </div>
                </div>

                <!-- Pagination Controls -->
                <div id="paginationContainer" class="flex justify-between items-center mt-3">
                    <?php include '../includes/admin_table_components/role_management_pagination.php'; ?>
                </div>
            </div>
        </div>

        <!-- Reset Password Modal -->
        <div id="resetAdminPasswordModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <form class="bg-white max-w-md p-6 rounded-md shadow-md text-center" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" id="adminResetPasswordForm">
                <h2 class="text-xl font-semibold mb-4">Reset Admin Password</h2>
                <p class="text-slate-600 mb-2">
                    You are about to reset the password for:
                    <span id="adminResetEmail" class="font-semibold"></span>
                </p>
                <p class="text-sm text-gray-500 mb-4">
                    A new temporary password will be generated, and the admin will be required to change it upon login.
                </p>

                <input type="hidden" name="adminid" id="resetAdminID">
                <input type="hidden" name="resetpassword" value="1">

                <div class="flex justify-end gap-4 select-none">
                    <div id="adminResetPasswordCancelBtn" class="px-4 py-2 bg-gray-200 text-black hover:bg-gray-300 rounded-sm cursor-pointer">
                        Cancel
                    </div>
                    <button type="submit" name="resetpassword" class="px-4 py-2 bg-blue-600 text-white hover:bg-blue-700 rounded-sm">
                        Reset Password
                    </button>
                </div>
            </form>
        </div>

        <!-- Admin Delete Modal -->
        <div id="adminConfirmDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <form class="bg-white max-w-lg p-6 rounded-md shadow-md text-center" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" id="adminDeleteForm">
                <h2 class="text-xl font-semibold text-red-600 mb-4">Confirm Admin Deletion</h2>
                <p class="text-slate-600 mb-2">You are about to delete the following Admin</p>
                <div class="flex justify-center items-center gap-2 mb-2">
                    <div class="relative">
                        <!-- Text version (shown when no profile image) -->
                        <div id="textProfileContainer" class="rounded-full" style="display: none;">
                            <p id="adminDeleteProfileText" class="w-16 h-16 text-white text-xl font-semibold flex items-center justify-center rounded-full select-none"></p>
                            <i class="ri-alert-line bg-slate-200 bg-opacity-55 text-red-500 text-lg absolute -bottom-1 -right-1 rounded-full flex items-center justify-center w-6 h-6 p-1"></i>
                        </div>

                        <!-- Image version (shown when profile image exists) -->
                        <div id="imageProfileContainer" style="display: none;">
                            <div class="w-16 h-16 rounded-full select-none">
                                <img id="adminDeleteProfile" src="" alt="Admin Profile" class="w-full h-full object-cover rounded-full mx-auto">
                            </div>
                            <i class="ri-alert-line bg-slate-200 bg-opacity-55 text-red-500 text-lg absolute -bottom-1 -right-1 rounded-full flex items-center justify-center w-6 h-6 p-1"></i>
                        </div>
                    </div>
                    <div class="text-left text-gray-600 text-sm">
                        <p id="adminDeleteUsername" class="font-bold text-base"></p>
                        <p id="adminDeleteEmail"></p>
                        <p id="adminDeleteRole"></p>
                    </div>
                </div>
                <p class="text-sm text-gray-500 mb-4">
                    Deleting this admin will permanently remove them from the system, including all associated data. This action cannot be undone.
                </p>
                <input type="hidden" name="adminid" id="deleteAdminID">
                <input
                    id="deleteAdminConfirmInput"
                    type="text"
                    placeholder='Type "DELETE" here'
                    class="w-full p-2 mb-4 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-red-300" />
                <div class="flex justify-end gap-4 select-none">
                    <div id="adminCancelDeleteBtn" class="px-4 py-2 bg-gray-200 text-black hover:bg-gray-300 rounded-sm">
                        Cancel
                    </div>
                    <button
                        type="submit"
                        id="confirmAdminDeleteBtn"
                        name="deleteadmin"
                        class="px-4 py-2 bg-red-600 text-white hover:bg-red-700 cursor-not-allowed rounded-sm" disabled>
                        Delete
                    </button>
                </div>
            </form>
        </div>

        <!-- Add Role Form -->
        <div id="addRoleModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <div class="bg-white w-full md:w-1/3 p-6 rounded-md shadow-md ">
                <h2 class="text-xl text-gray-700 font-bold mb-4">Add New Role</h2>
                <form class="flex flex-col space-y-4" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" id="roleForm">
                    <!-- Role Input -->
                    <div class="relative w-full">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Role Information</label>
                        <input
                            id="roleInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="text"
                            name="role"
                            placeholder="Enter role">
                        <small id="roleError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>

                    <!-- Description Input -->
                    <div class="relative">
                        <textarea
                            id="roleDescriptionInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="text"
                            name="description"
                            placeholder="Enter role description"></textarea>
                        <small id="roleDescriptionError" class="absolute left-2 -bottom-1 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>

                    <div class="flex justify-end gap-4 select-none">
                        <div id="addRoleCancelBtn" class="px-4 py-2 text-amber-500 font-semibold hover:text-amber-600">
                            Cancel
                        </div>
                        <!-- Submit Button -->
                        <button
                            type="submit"
                            name="addrole"
                            class="bg-amber-500 text-white font-semibold px-4 py-2 rounded-sm select-none hover:bg-amber-600 transition-colors">
                            Add Role
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Loader -->
    <?php
    include('../includes/alert.php');
    include('../includes/loader.php');
    ?>

    <script src="//unpkg.com/alpinejs" defer></script>
    <script type="module" src="../JS/admin.js"></script>
    <script type="module" src="../JS/adminAuth.js"></script>
</body>

</html>