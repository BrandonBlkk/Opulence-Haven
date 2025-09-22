<?php
session_start();
require_once('../config/db_connection.php');
include_once('../includes/admin_pagination.php');
require_once('../includes/auth_check.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

$admin = $_SESSION["AdminID"] ?? null;
$role = $_SESSION["RoleID"] ?? null;

$alertMessage = '';

// Get User Details
if (isset($_GET['action']) && $_GET['action'] === 'getUserDetails' && isset($_GET['id'])) {
    $id = mysqli_real_escape_string($connect, $_GET['id']);
    $response = ['success' => false];

    $query = "
        SELECT UserID, UserName, UserEmail, UserPhone, SignupDate, LastSignIn, 
               Status, Profile, ProfileBgColor, Membership, PointsBalance
        FROM usertb
        WHERE UserID = '$id'
        LIMIT 1
    ";
    $result = $connect->query($query);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();

        $response['success'] = true;
        $response['user'] = [
            'UserID'        => $user['UserID'],
            'UserName'      => $user['UserName'],
            'UserEmail'     => $user['UserEmail'],
            'UserPhone'     => $user['UserPhone'],
            'SignupDate'    => $user['SignupDate'],
            'LastSignIn'    => $user['LastSignIn'],
            'Status'        => $user['Status'],
            'Profile'       => $user['Profile'],
            'ProfileBgColor' => $user['ProfileBgColor'],
            'Membership'    => $user['Membership'],
            'PointsBalance' => $user['PointsBalance']
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Delete User
if (isset($_POST['deleteuser']) && !empty($_POST['userid'])) {
    $userId = mysqli_real_escape_string($connect, $_POST['userid']);

    // Fetch user details
    $fetchQuery = $connect->prepare("SELECT UserEmail, UserName FROM usertb WHERE UserID = ?");
    $fetchQuery->bind_param("s", $userId);
    $fetchQuery->execute();
    $userResult = $fetchQuery->get_result();

    $userData = null;
    if ($userResult && $userResult->num_rows > 0) {
        $userData = $userResult->fetch_assoc();
    }

    // Delete user
    $deleteQuery = $connect->prepare("DELETE FROM usertb WHERE UserID = ?");
    $deleteQuery->bind_param("s", $userId);

    if ($deleteQuery->execute()) {
        echo json_encode([
            'success' => true,
            'userEmail' => $userData['UserEmail'] ?? null,
            'userName'  => $userData['UserName'] ?? null
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete user. Please try again.']);
    }
    exit;
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
            <div>
                <div>
                    <h2 class="text-xl text-gray-700 font-bold mb-4">User's Lists</h2>
                    <p>View the list of signed up users for efficient</p>
                </div>
            </div>

            <!-- Admin Table -->
            <div class="overflow-x-auto">
                <!-- Admin Search and Filter -->
                <form method="GET" class="my-4 flex items-start sm:items-center justify-between flex-col sm:flex-row gap-2 sm:gap-0">
                    <h1 class="text-lg text-gray-700 font-semibold text-nowrap">All Users <span class="text-gray-400 text-sm ml-2"><?php echo $userCount ?></span></h1>
                    <div class="flex flex-col sm:flex-row items-start sm:items-center w-full gap-2 sm:gap-0">
                        <input type="text" name="user_search" class="p-2 ml-0 sm:ml-5 border border-gray-300 rounded-md w-full outline-none focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            placeholder="Search for user account..."
                            value="<?php echo isset($_GET['user_search']) ? htmlspecialchars($_GET['user_search']) : ''; ?>">

                        <div class="flex items-center">
                            <label for="sort" class="ml-0 sm:ml-4 mr-2 flex items-center cursor-pointer select-none">
                                <i class="ri-filter-2-line text-xl"></i>
                                <p>Filters</p>
                            </label>

                            <!-- Removed the nested form and moved the select here -->
                            <select name="sort" id="sort" class="border p-2 rounded text-sm outline-none">
                                <option value="random" <?= ($filterMembershipID === 'random') ? 'selected' : '' ?>>All Users</option>
                                <option value='1' <?= ($filterMembershipID === '1') ? 'selected' : '' ?>>Member</option>
                                <option value="0" <?= ($filterMembershipID === '0') ? 'selected' : '' ?>>Standard</option>
                            </select>
                        </div>

                        <!-- Add hidden inputs for pagination if needed -->
                        <?php if (isset($_GET['userpage'])): ?>
                            <input type="hidden" name="userpage" value="<?= $_GET['userpage'] ?>">
                        <?php endif; ?>
                    </div>
                </form>

                <!-- User Table -->
                <div class="tableScrollBar overflow-y-auto max-h-[495px]">
                    <div id="userResults">
                        <?php include '../includes/admin_table_components/userdetails_results.php'; ?>
                    </div>
                </div>

                <!-- Pagination Controls -->
                <div id="paginationContainer" class="flex justify-between items-center mt-3">
                    <?php include '../includes/admin_table_components/userdetails_pagination.php'; ?>
                </div>
            </div>
        </div>

        <!-- User Details Modal -->
        <div id="userDetailsModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-x-5 transition-all duration-300">
            <div class="bg-white rounded-xl max-w-lg w-full p-6 animate-fade-in max-h-[90vh] overflow-y-auto overflow-x-hidden shadow-md">
                <!-- Header -->
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-800">User Details</h3>
                    <button id="closeUserDetailButton" class="text-gray-400 hover:text-gray-500">
                        <i class="ri-close-line text-xl"></i>
                    </button>
                </div>

                <div class="space-y-3">
                    <!-- User Information -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium text-gray-800 mb-3">User Information</h4>
                        <div class="space-y-2">
                            <div class="flex items-center gap-3">
                                <div class="py-3 text-start flex items-center gap-2 text-gray-600 text-sm">
                                    <div id="userProfileBg" class="w-10 h-10 object-cover rounded-full text-white select-none">
                                        <p class="w-full h-full flex items-center justify-center font-semibold" id="userName"></p>
                                    </div>
                                    <div class="text-left text-gray-600 text-sm">
                                        <p id="userDetailsName" class="font-bold text-base"></p>
                                        <p id="userDetailsEmail"></p>
                                        <p id="userDetailsPhone"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm mt-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">User ID:</span>
                                    <span class="font-medium text-gray-600" id="userDetailsID"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Status:</span>
                                    <span class="font-medium text-gray-600" id="userDetailsStatus"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Signup Date:</span>
                                    <span class="font-medium text-gray-600" id="userDetailsSignup"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Last Sign In:</span>
                                    <span class="font-medium text-gray-600" id="userDetailsLastSignIn"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Membership:</span>
                                    <span class="font-medium text-gray-600" id="userDetailsMembership"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Points Balance:</span>
                                    <span class="font-medium text-gray-600" id="userDetailsPoints"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-6 flex justify-end gap-3">
                    <?php if ($role == 1): ?>
                        <button id="profileDeleteBtn"
                            class="border-2 px-4 py-2 rounded-sm flex items-center justify-center gap-2 select-none">
                            <i class="ri-delete-bin-line text-xl text-red-500"></i>
                            <p class="font-semibold text-gray-600">Delete Account</p>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- User Delete Modal -->
        <div id="userConfirmDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible transition-all duration-300">
            <form
                class="relative bg-white w-full max-w-md p-6 rounded-lg shadow-xl transform transition-all duration-300 text-center"
                action="<?php echo $_SERVER["PHP_SELF"]; ?>"
                method="post"
                id="userDeleteForm">

                <!-- Header Icon -->
                <div class="flex items-center justify-center mb-4 relative">
                    <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                        <i class="ri-alert-line text-red-600 text-2xl"></i>
                    </div>
                </div>

                <!-- Title -->
                <h2 class="text-xl font-semibold text-gray-800 mb-2">Delete User</h2>
                <p class="text-gray-600 text-sm mb-4">You are about to delete the following user:</p>

                <!-- User Profile Info -->
                <div class="flex justify-center items-center gap-4 mb-4">
                    <!-- Text version -->
                    <div id="textUserProfileContainer" class="rounded-full w-16 h-16 bg-gray-300 flex items-center justify-center text-white text-xl font-semibold select-none" style="display: none;">
                        <p id="userDeleteProfileText"></p>
                        <i class="ri-alert-line bg-slate-200 bg-opacity-55 text-red-500 text-lg absolute -bottom-1 -right-1 rounded-full flex items-center justify-center w-6 h-6 p-1"></i>
                    </div>
                    <!-- Image version -->
                    <div id="imageUserProfileContainer" class="relative w-16 h-16 rounded-full select-none" style="display: none;">
                        <img id="userDeleteProfile" src="" alt="User Profile" class="w-full h-full object-cover rounded-full mx-auto">
                        <i class="ri-alert-line bg-slate-200 bg-opacity-55 text-red-500 text-lg absolute -bottom-1 -right-1 rounded-full flex items-center justify-center w-6 h-6 p-1"></i>
                    </div>
                    <!-- User info -->
                    <div class="text-left text-gray-600 text-sm">
                        <p id="userDeleteUsername" class="font-bold text-base"></p>
                        <p id="userDeleteEmail"></p>
                        <p id="userDeleteRole"></p>
                    </div>
                </div>

                <!-- Warning -->
                <p class="text-sm text-gray-500 mb-4">
                    This action is permanent and cannot be undone. All related data will be removed.
                </p>

                <!-- Hidden Input -->
                <input type="hidden" name="userid" id="deleteUserID">

                <!-- Reason for Deletion -->
                <div class="mb-4 text-left">
                    <label for="deleteReason" class="block text-sm font-medium text-gray-700 mb-1">Reason for Deletion</label>
                    <select name="deleteReason" id="deleteReason" class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-red-300">
                        <option value="">Select a reason</option>
                        <option value="Violation of Terms">Violation of Terms</option>
                        <option value="Fake Account">Fake Account</option>
                        <option value="Inactive Account">Inactive Account</option>
                        <option value="Requested by User">Requested by User</option>
                        <option value="Other">Other (Specify Below)</option>
                    </select>
                    <!-- Custom reason -->
                    <textarea name="customDeleteReason" id="customDeleteReason" rows="2" placeholder="Enter custom reason" class="w-full mt-2 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-red-300 hidden"></textarea>
                </div>

                <!-- Confirm Input -->
                <input
                    id="deleteUserConfirmInput"
                    type="text"
                    placeholder='Type "DELETE" here'
                    class="w-full p-2 mb-4 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-red-300" />

                <!-- Buttons -->
                <div class="flex justify-end gap-3 select-none">
                    <button type="button" id="userCancelDeleteBtn"
                        class="px-4 py-2 bg-gray-200 text-black hover:bg-gray-300 rounded-sm">
                        Cancel
                    </button>
                    <button type="submit" id="confirmUserDeleteBtn" name="deleteuser"
                        class="px-4 py-2 bg-red-600 text-white hover:bg-red-700 cursor-not-allowed rounded-sm" disabled>
                        Delete
                    </button>
                </div>
            </form>
        </div>

        <script>
            // Show custom reason textarea when "Other" is selected
            document.addEventListener("DOMContentLoaded", () => {
                const reasonSelect = document.getElementById("deleteReason");
                const customReason = document.getElementById("customDeleteReason");

                if (reasonSelect && customReason) {
                    reasonSelect.addEventListener("change", () => {
                        if (reasonSelect.value === "Other") {
                            customReason.classList.remove("hidden");
                        } else {
                            customReason.classList.add("hidden");
                            customReason.value = "";
                        }
                    });
                }
            });
        </script>
    </div>

    <!-- Loader -->
    <?php
    include('../includes/alert.php');
    include('../includes/loader.php');
    ?>

    <script src="//unpkg.com/alpinejs" defer></script>
    <script type="module" src="../JS/admin.js"></script>
</body>

</html>