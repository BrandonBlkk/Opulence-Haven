<?php
session_start();
require_once('../config/db_connection.php');
include_once('../includes/update_image_func.php');
include_once('../includes/mask_email.php');
require_once('../includes/auth_check.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

$adminID = $_SESSION['AdminID'];

// Fetch admin data
$adminQuery = "SELECT * FROM admintb WHERE AdminID = '$adminID'";
$adminRow = $connect->query($adminQuery)->fetch_assoc();
$adminprofile = $adminRow['AdminProfile'] ?? null;
$admin_username = $adminRow['UserName'];
$profile_color = $adminRow['ProfileBgColor'];

$alertMessage = '';
$response = ['success' => false, 'message' => '', 'adminProfile' => null, 'removeProfile' => false, 'changesMade' => false];

// Fetch admin profile
$adminQuery = "SELECT * FROM admintb WHERE AdminID = '$adminID'";
$adminRow = $connect->query($adminQuery)->fetch_assoc();


// Handle form submission for profile update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['modify'])) {

    try {
        // Get current admin data
        $currentQuery = "SELECT FirstName, LastName, UserName, AdminPhone, RoleID, AdminProfile FROM admintb WHERE AdminID = '$adminID'";
        $currentResult = $connect->query($currentQuery);
        $currentData = $currentResult->fetch_assoc();

        // Get form data
        $firstname = mysqli_real_escape_string($connect, $_POST['firstname'] ?? '');
        $lastname = mysqli_real_escape_string($connect, $_POST['lastname'] ?? '');
        $username = mysqli_real_escape_string($connect, $_POST['username'] ?? '');
        $phone = mysqli_real_escape_string($connect, $_POST['phone'] ?? '');
        $role = mysqli_real_escape_string($connect, $_POST['role'] ?? '');

        // Check if any fields were actually changed
        $changesDetected = false;
        if (
            $firstname != $currentData['FirstName'] ||
            $lastname != $currentData['LastName'] ||
            $username != $currentData['UserName'] ||
            $phone != $currentData['AdminPhone'] ||
            $role != $currentData['RoleID']
        ) {
            $changesDetected = true;
        }

        // Initialize with current profile
        $adminProfile = $adminprofile;
        $profileChanged = false;

        // Process image upload if file was provided
        if (isset($_FILES['AdminProfile']) && $_FILES['AdminProfile']['error'] == UPLOAD_ERR_OK) {
            $imageFile = $_FILES['AdminProfile'];
            $result = uploadProductImage($imageFile, $adminprofile);

            if (isset($result['adminPath'])) {
                $adminProfile = $result['adminPath'];
                $response['adminProfile'] = $adminProfile;
                $profileChanged = true;
            } elseif (isset($result['image'])) {
                throw new Exception($result['image']);
            } else {
                throw new Exception('Error processing profile image upload');
            }
        }

        // Handle profile removal
        $removeProfile = isset($_POST['removeProfile']) && $_POST['removeProfile'] == '1';
        if ($removeProfile) {
            // Only mark as changed if there was a profile to remove
            if (!empty($adminprofile) && $adminprofile != 'NULL') {
                $profileChanged = true;
            }
            // Delete the old profile image if it exists
            if (!empty($adminprofile) && file_exists($adminprofile)) {
                @unlink($adminprofile);
            }
            $adminProfile = null;
            $response['removeProfile'] = true;
        }

        // Only update if changes were detected
        if ($changesDetected || $profileChanged) {
            $response['changesMade'] = true;

            // Build the query with proper NULL handling
            $updateProfileQuery = "UPDATE admintb SET 
            AdminProfile = ?, 
            FirstName = ?, 
            LastName = ?, 
            UserName = ?, 
            AdminPhone = ?,  
            RoleID = ? 
            WHERE AdminID = ?";

            // Prepare the statement
            $stmt = $connect->prepare($updateProfileQuery);

            if ($stmt === false) {
                die('Prepare failed: ' . htmlspecialchars($connect->error));
            }

            // Bind parameters - handle NULL for AdminProfile
            $adminProfileParam = ($adminProfile === null) ? null : $adminProfile;
            $stmt->bind_param(
                "sssssss",
                $adminProfileParam,
                $firstname,
                $lastname,
                $username,
                $phone,
                $role,
                $adminID
            );

            // Execute the statement
            $updateProfileQueryResult = $stmt->execute();

            if ($updateProfileQueryResult === false) {
                die('Execute failed: ' . htmlspecialchars($stmt->error));
            }

            $stmt->close();

            if (!$updateProfileQueryResult) {
                throw new Exception('Error updating profile: ' . $connect->error);
            }

            $response['success'] = true;
            $response['newUsername'] = $username;

            // Update session with new profile if needed
            if ($adminID == $_SESSION['AdminID']) {
                $_SESSION['admin_profile'] = ($adminProfile === null ? null : $adminProfile);
            }
        } else {
            $response['success'] = true;
            $response['message'] = 'No changes were made to your profile.';
        }
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Handle password change 
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['changePassword'])) {

    try {
        $oldPassword = isset($_POST['oldPassword']) ? mysqli_real_escape_string($connect, $_POST['oldPassword']) : '';
        $newPassword = isset($_POST['newPassword']) ? mysqli_real_escape_string($connect, $_POST['newPassword']) : '';
        $confirmPassword = isset($_POST['confirmPassword']) ? mysqli_real_escape_string($connect, $_POST['confirmPassword']) : '';

        // Verify old password first
        $checkQuery = "SELECT AdminID FROM admintb WHERE AdminID = '$adminID' AND AdminPassword = '$oldPassword'";
        $checkResult = $connect->query($checkQuery);

        if ($checkResult->num_rows === 0) {
            throw new Exception('Current password is incorrect');
        }

        if ($newPassword !== $confirmPassword) {
            throw new Exception('New password and confirmation password do not match');
        }

        $updatePasswordQuery = $connect->prepare("UPDATE admintb SET AdminPassword = ? WHERE AdminID = ?");
        $updatePasswordQuery->bind_param('ss', $newPassword, $adminID);
        $updatePasswordQueryResult = $updatePasswordQuery->execute();
        $updatePasswordQuery->close();

        if (!$updatePasswordQueryResult) {
            throw new Exception('Error updating password: ' . $connect->error);
        }

        $response['success'] = true;
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
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

<body class="min-w-[380px]">
    <?php include('../includes/admin_navbar.php'); ?>

    <!-- Main Container -->
    <div class="p-3 ml-0 md:ml-[250px]">
        <div class="w-full bg-white">
            <div class="flex-1 px-3 overflow-x-auto">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <div class="flex items-center justify-between">
                            <div class="pb-3">
                                <h1 class="text-xl sm:text-2xl text-blue-900 font-semibold mb-1">Edit Profile</h1>
                                <div class="text-sm text-gray-500">
                                    <p>Here you can eidt public information about yourself.</p>
                                    <p>The changes will be displayed for other users within a second.</p>
                                </div>
                            </div>
                        </div>
                        <form action="<?php $_SERVER["PHP_SELF"] ?>" method="POST" enctype="multipart/form-data" id="updateAdminProfileForm">
                            <!-- Profile Picture Upload -->
                            <div class="flex flex-col sm:flex-row items-center justify-center sm:justify-start mb-3 gap-5">
                                <div class="relative w-40 h-40 rounded-full my-3 select-none group">
                                    <!-- Profile Image -->
                                    <?php if (empty($adminprofile)): ?>
                                        <div id="profilePreview" class="w-full h-full object-cover rounded-full bg-[<?php echo $profile_color ?>] text-white select-none">
                                            <p class="w-full h-full flex items-center justify-center text-5xl font-semibold"><?php echo strtoupper(substr($admin_username, 0, 1)); ?></p>
                                        </div>
                                    <?php else: ?>
                                        <img id="profilePreview" class="w-full h-full object-cover rounded-full" src="<?php echo htmlspecialchars($adminprofile); ?>" alt="Profile">
                                    <?php endif; ?>

                                    <!-- Camera Icon Overlay -->
                                    <div class="absolute inset-0 bg-black bg-opacity-50 rounded-full flex items-center justify-center cursor-pointer opacity-0 group-hover:opacity-100 transition-opacity duration-300" onclick="document.getElementById('AdminProfile').click()">
                                        <i class="ri-camera-line text-white text-3xl"></i>
                                    </div>

                                    <!-- Remove Profile Button (only shown when profile exists) -->
                                    <?php if (!empty($adminprofile)): ?>
                                        <div class="absolute top-2 left-3 bg-red-500 text-white text-xs font-semibold w-7 h-7 flex items-center justify-center p-2 rounded-full cursor-pointer opacity-0 group-hover:opacity-100 hover:bg-red-600 transition-all duration-300" onclick="removeProfile()">
                                            <i class="ri-close-line"></i>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Alert Box -->
                                    <div id="unsavedAlert" class="absolute -top-2 right-0 bg-yellow-500 text-black text-xs font-semibold py-1 px-3 rounded shadow-md opacity-0 pointer-events-none transition-opacity duration-300">
                                        Profile unsaved
                                    </div>

                                    <!-- Hidden File Input -->
                                    <input type="file" id="AdminProfile" name="AdminProfile" class="hidden" onchange="previewProfileImage(event)" accept="image/*">
                                    <!-- Hidden input for profile removal -->
                                    <input type="hidden" id="removeProfileFlag" name="removeProfile" value="0">
                                </div>
                                <div>
                                    <div class="flex justify-start">
                                        <div id="adminProfileDeleteBtn" class="flex items-center justify-center gap-2 p-2 rounded-md hover:bg-slate-100 transition-colors duration-300 cursor-pointer select-none">
                                            <i class="ri-delete-bin-line text-xl text-red-500"></i>
                                            <p class="font-semibold">Delete Account</p>
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-500 mt-3">Recommended size is 160x160px.</p>
                                </div>
                            </div>

                            <script>
                                const previewProfileImage = (event) => {
                                    const file = event.target.files[0];
                                    if (file) {
                                        const reader = new FileReader();
                                        reader.onload = (e) => {
                                            const previewContainer = document.getElementById('profilePreview');

                                            // Always create/replace with img element when new image is selected
                                            const img = document.createElement('img');
                                            img.id = 'profilePreview';
                                            img.className = 'w-full h-full object-cover rounded-full';
                                            img.src = e.target.result;
                                            img.alt = 'Profile';

                                            // Replace the existing element (whether div or img)
                                            previewContainer.parentNode.replaceChild(img, previewContainer);

                                            // Show alert with transition
                                            const alertBox = document.getElementById('unsavedAlert');
                                            if (alertBox) {
                                                alertBox.classList.remove('opacity-0', 'pointer-events-none');
                                            }

                                            // Reset remove profile flag if new image is selected
                                            document.getElementById('removeProfileFlag').value = '0';
                                        };
                                        reader.readAsDataURL(file);
                                    }
                                };

                                function removeProfile() {
                                    if (confirm('Are you sure you want to remove your profile picture?')) {
                                        // Create the initial div with first letter
                                        const previewContainer = document.getElementById('profilePreview');
                                        const div = document.createElement('div');
                                        div.id = 'profilePreview';
                                        div.className = 'w-full h-full object-cover rounded-full bg-[<?php echo $profile_color ?>] text-white select-none';
                                        div.innerHTML = `<p class="w-full h-full flex items-center justify-center text-5xl font-semibold"><?php echo strtoupper(substr($admin_username, 0, 1)); ?></p>`;

                                        // Replace the image with the div
                                        previewContainer.parentNode.replaceChild(div, previewContainer);

                                        // Set the remove profile flag
                                        document.getElementById('removeProfileFlag').value = '1';

                                        // Show alert
                                        const alertBox = document.getElementById('unsavedAlert');
                                        if (alertBox) {
                                            alertBox.classList.remove('opacity-0', 'pointer-events-none');
                                        }
                                    }
                                }
                            </script>

                            <div class="space-y-4">
                                <div class="flex flex-col sm:flex-row gap-4 sm:gap-2">
                                    <!-- Firstname Input -->
                                    <div class="relative flex-1">
                                        <label for="firstnameInput" class="block text-sm font-medium text-gray-700 mb-1">Firstname</label>
                                        <input
                                            id="firstnameInput"
                                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                            type="text"
                                            name="firstname"
                                            value="<?php echo $adminRow['FirstName'] ?>"
                                            placeholder="Enter your firstname">
                                        <small id="firstnameError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                                    </div>
                                    <!-- Lastname Input -->
                                    <div class="relative flex-1">
                                        <label for="lastnameInput" class="block text-sm font-medium text-gray-700 mb-1">Lastname</label>
                                        <input
                                            id="lastnameInput"
                                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                            type="text"
                                            name="lastname"
                                            value="<?php echo $adminRow['LastName'] ?>"
                                            placeholder="Enter your lastname">
                                        <small id="lastnameError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                                    </div>
                                </div>
                                <!-- Username Input -->
                                <div class="relative">
                                    <label for="usernameInput" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                                    <input
                                        id="usernameInput"
                                        class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                        type="text"
                                        name="username"
                                        value="<?php echo $adminRow['UserName'] ?>"
                                        placeholder="Enter your username">
                                    <small id="usernameError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                                </div>
                                <!-- Email Input -->
                                <div class="relative flex-1">
                                    <label for="emailInput" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                    <input
                                        id="emailInput"
                                        class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                        type="email"
                                        name="email"
                                        value="<?php echo maskEmail($adminRow['AdminEmail']) ?>"
                                        placeholder="Enter your email" disabled>
                                    <small id="emailError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                                </div>
                                <div class="flex justify-start sm:justify-end">
                                    <a id="changePasswordBtn" class="text-sm text-gray-400 hover:text-gray-500" href="#">Change Password</a>
                                </div>
                                <div class="flex flex-col sm:flex-row gap-4 sm:gap-2">
                                    <!-- Phone Input -->
                                    <div class="relative flex-1">
                                        <label for="phoneInput" class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                        <input
                                            id="phoneInput"
                                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                            type="tel"
                                            name="phone"
                                            value="<?php echo $adminRow['AdminPhone'] ?>"
                                            placeholder="Enter your phone">
                                        <small id="phoneError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex flex-col relative">
                                            <label for="roleSelect" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                                            <select name="role" class="border rounded p-2 bg-gray-50 outline-none focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out" <?= ($role !== '1') ? 'disabled' : ''; ?>>
                                                <?php
                                                // Fetch roles for the dropdown
                                                $rolesQuery = "SELECT * FROM roletb";
                                                $rolesResult = $connect->query($rolesQuery);

                                                if ($rolesResult->num_rows > 0) {
                                                    // Get the admin's role
                                                    $adminRoleID = $adminRow['RoleID'];

                                                    while ($roleRow = $rolesResult->fetch_assoc()) {
                                                        $selected = $roleRow['RoleID'] == $adminRoleID ? 'selected' : '';
                                                        echo "<option value='{$roleRow['RoleID']}' $selected>{$roleRow['Role']}</option>";
                                                    }
                                                } else {
                                                    echo "<option value='' disabled>No roles available</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center justify-end gap-3 select-none">
                                    <button type="submit" name="modify" class="bg-amber-500 text-white font-semibold px-6 py-2 rounded-sm hover:bg-amber-600 focus:outline-non transition duration-300 ease-in-out">Update Profile</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <!-- Right Side Note and Illustration -->
                    <div class="px-0 sm:px-6 rounded-lg flex flex-col items-center justify-center">
                        <div class="bg-sky-100 p-3 rounded">
                            <h3 class="text-xl font-semibold text-gray-700 mb-2">Build Trust!</h3>
                            <p class="text-gray-600">Your profile is displayed on your account and in communications, making it easy for others to recognize and connect with you.</p>
                        </div>
                        <div class="max-w-[500px] select-none">
                            <img src="../UserImages/account-concept-illustration_114360-409.avif" alt="Illustration" class="w-full h-full object-cover">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div id="changePasswordModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible -translate-y-5 transition-all duration-300">
        <div class="bg-white p-6 rounded-lg shadow-lg w-96">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Change Password</h3>

            <form class="flex flex-col space-y-4" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" id="changePasswordForm">
                <!-- Old Password -->
                <div class="flex flex-col relative">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Old Password</label>
                    <div class="flex items-center justify-between border rounded">
                        <input
                            type="password"
                            name="oldPassword"
                            id="oldPasswordInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            placeholder="Enter your old password">
                        <i id="togglePassword" class="absolute right-2 ri-eye-line p-2 cursor-pointer"></i>
                    </div>
                    <small id="oldPasswordError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                </div>

                <!-- New Password -->
                <div class="flex flex-col relative mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                    <div class="flex items-center justify-between border rounded">
                        <input
                            type="password"
                            name="newPassword"
                            id="newPasswordInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            placeholder="Enter your new password">
                        <i id="togglePassword2" class="absolute right-2 ri-eye-line p-2 cursor-pointer"></i>
                    </div>
                    <small id="newPasswordError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                </div>

                <!-- Confirm New Password -->
                <div class="flex flex-col relative mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                    <div class="flex items-center justify-between border rounded">
                        <input
                            type="password"
                            name="confirmPassword"
                            id="confirmPasswordInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            placeholder="Confirm your new password">
                        <i id="togglePassword3" class="absolute right-2 ri-eye-line p-2 cursor-pointer"></i>
                    </div>
                    <small id="confirmPasswordError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                </div>

                <div class="flex items-center justify-end gap-3 select-none">
                    <button id="cancelChangeBtn" type="button" class="px-4 py-2 bg-gray-200 text-black hover:bg-gray-300 rounded-sm transition duration-300 ease-in-out">Cancel</button>
                    <button id="confirmChangeBtn" type="submit" name="changePassword" class="px-4 py-2 rounded-sm bg-amber-500 text-white hover:bg-amber-600 transition duration-300 ease-in-out">Change Password</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="confirmDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
        <div class="bg-white max-w-5xl p-6 rounded-md shadow-md text-center">
            <h2 class="text-xl font-semibold text-red-600 mb-4">Confirm Account Delete</h2>
            <p class="text-slate-600 mb-2">You are currently signed in as:</p>
            <div class="flex justify-center items-center gap-2 mb-2">
                <?php
                if ($admin_profile === null) {
                ?>
                    <div class="relative">
                        <p class="w-16 h-16 bg-[<?php echo $profile_color ?>] text-white text-xl font-semibold flex items-center justify-center rounded-full select-none"><?php echo substr($admin_username, 0, 1); ?></p>
                        <i class="ri-alert-line bg-slate-200 bg-opacity-55 text-red-500 text-lg absolute -bottom-1 -right-1 rounded-full flex items-center justify-center w-6 h-6 p-1"></i>
                    </div>
                <?php
                } else {
                ?>
                    <div class="relative">
                        <div class="w-16 h-16 rounded-full select-none">
                            <img src="<?php echo htmlspecialchars($adminRow['AdminProfile']); ?>" alt="Admin Profile" class="w-full h-full object-cover rounded-full mx-auto">
                        </div>
                        <i class="ri-alert-line bg-slate-200 bg-opacity-55 text-red-500 text-lg absolute -bottom-1 -right-1 rounded-full flex items-center justify-center w-6 h-6 p-1"></i>
                    </div>
                <?php
                }
                ?>
                <div class="text-left text-gray-600 text-sm">
                    <p class="text-gray-800 font-bold text-base">
                        <?php echo $adminRow['UserName']; ?>
                    </p>
                    <p><?php echo $adminRow['AdminEmail']; ?></p>
                    <p class="text-xs"><?php echo $admin_role; ?></p>
                </div>
            </div>
            <p class="text-sm text-gray-500">
                Deleting your account will permanently remove all your personal data, and preferences from our system.
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
                <button id="cancelDeleteBtn" class="px-4 py-2 bg-gray-200 text-black hover:bg-gray-300 rounded-sm">
                    Cancel
                </button>
                <button id="confirmDeleteBtn" class="px-4 py-2 bg-red-600 text-white hover:bg-red-700 cursor-not-allowed rounded-sm" disabled>
                    I understand, delete my account
                </button>
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
</body>

</html>