<?php
session_start();
include('../config/dbConnection.php');
include('../includes/UpdateImageFunc.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

$adminID = $_SESSION['AdminID'];

// Fetch admin profile
$adminProfileQuery = "SELECT AdminProfile FROM admintb WHERE AdminID = '$adminID'";
$adminProfileRow = $connect->query($adminProfileQuery)->fetch_assoc();
$adminprofile = $adminProfileRow['AdminProfile'];

$alertMessage = '';
$profileUpdate = false;

// Fetch admin profile
$adminQuery = "SELECT * FROM admintb WHERE AdminID = '$adminID'";
$adminRow = $connect->query($adminQuery)->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['modify'])) {
    $firstname = mysqli_real_escape_string($connect, $_POST['firstname']);
    $lastname = mysqli_real_escape_string($connect, $_POST['lastname']);
    $username = mysqli_real_escape_string($connect, $_POST['username']);
    $email = mysqli_real_escape_string($connect, $_POST['email']);
    $phone = mysqli_real_escape_string($connect, $_POST['phone']);
    $role = mysqli_real_escape_string($connect, $_POST['role']);

    // Current image from the database
    $currentProfile = $adminprofile;

    // Simulate $_FILES array for images
    $imageFile = $_FILES['AdminProfile'];

    // Upload Profile Image 
    $result = uploadProductImage($imageFile, $currentProfile);
    if (is_array($result)) {
        echo $result['image'] . "<br>";
    } else {
        $adminProfile = $result;
    }

    $updateProfileQuery = "UPDATE admintb SET AdminProfile = '$adminProfile', FirstName = '$firstname', 
    LastName = '$lastname', UserName = '$username', AdminEmail = '$email', AdminPhone ='$phone',  RoleID = '$role' 
    WHERE AdminID = '$adminID'";
    $updateProfileQueryResult = $connect->query($updateProfileQuery);

    if ($updateProfileQueryResult) {
        $profileUpdate = true;
    } else {
        $alertMessage = 'Error updating password. Please try again.';
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
    <?php include('../includes/AdminNavbar.php'); ?>

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
                                    <p>The changes will be displayed for other users within 5 minutes.</p>
                                </div>
                            </div>
                        </div>
                        <form action="<?php $_SERVER["PHP_SELF"] ?>" method="POST" enctype="multipart/form-data" id="updateAdminProfileForm">
                            <!-- Profile Picture Upload -->
                            <div class="flex flex-col sm:flex-row items-center justify-center sm:justify-start mb-3 gap-5">
                                <div class="relative w-40 h-40 rounded-full my-3 select-none group">
                                    <!-- Profile Image -->
                                    <img
                                        id="profilePreview"
                                        class="w-full h-full object-cover rounded-full"
                                        src="<?php echo htmlspecialchars($adminRow['AdminProfile']); ?>"
                                        alt="Profile">
                                    <!-- Camera Icon Overlay -->
                                    <div
                                        class="absolute inset-0 bg-black bg-opacity-50 rounded-full flex items-center justify-center cursor-pointer opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                                        onclick="document.getElementById('AdminProfile').click()">
                                        <i class="ri-camera-line text-white text-3xl"></i>
                                    </div>
                                    <!-- Alert Box -->
                                    <div
                                        id="unsavedAlert"
                                        class="absolute -top-2 right-0 bg-yellow-500 text-black text-xs font-semibold py-1 px-3 rounded shadow-md opacity-0 pointer-events-none transition-opacity duration-300">
                                        Profile unsaved
                                    </div>
                                    <!-- Hidden File Input -->
                                    <input
                                        type="file"
                                        id="AdminProfile"
                                        name="AdminProfile"
                                        class="hidden"
                                        onchange="previewProfileImage(event)">
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
                                            const previewImg = document.getElementById('profilePreview');
                                            if (previewImg) {
                                                previewImg.src = e.target.result;
                                            }
                                            // Show alert with transition
                                            const alertBox = document.getElementById('unsavedAlert');
                                            if (alertBox) {
                                                alertBox.classList.remove('opacity-0', 'pointer-events-none');
                                            }
                                        };
                                        reader.readAsDataURL(file);
                                    }
                                };
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
                                <div class="flex flex-col sm:flex-row gap-4 sm:gap-2">
                                    <!-- Email Input -->
                                    <div class="relative flex-1">
                                        <label for="emailInput" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                        <input
                                            id="emailInput"
                                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                            type="email"
                                            name="email"
                                            value="<?php echo $adminRow['AdminEmail'] ?>"
                                            placeholder="Enter your email">
                                        <small id="emailError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                                    </div>
                                    <!-- Password Input -->
                                    <div class="flex-1">
                                        <div class="flex flex-col relative">
                                            <label for="passwordInput" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                                            <input id="passwordInput"
                                                class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                                type="password"
                                                name="password"
                                                value="<?php echo $adminRow['AdminPassword'] ?>"
                                                placeholder="Enter your password"
                                                disabled>
                                            <small id="passwordError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                                        </div>
                                    </div>
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
                                            <select name="role" class="border rounded p-2 bg-gray-50" <?= ($role !== '1') ? 'disabled' : ''; ?>>
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

    <!-- Delete Confirmation Modal -->
    <div id="confirmDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
        <div class="bg-white max-w-5xl p-6 rounded-md shadow-md text-center">
            <h2 class="text-xl font-semibold text-red-600 mb-4">Confirm Account Delete</h2>
            <p class="text-slate-600 mb-2">You are currently signed in as:</p>
            <div class="flex justify-center items-center gap-2 mb-2">
                <div class="relative">
                    <div class="w-16 h-16 rounded-full select-none">
                        <img src="<?php echo htmlspecialchars($adminRow['AdminProfile']); ?>" alt="Admin Profile" class="w-full h-full object-cover rounded-full mx-auto">
                    </div>
                    <i class="ri-alert-line bg-slate-200 bg-opacity-55 text-red-500 text-lg absolute -bottom-1 -right-1 rounded-full flex items-center justify-center w-6 h-6 p-1"></i>
                </div>
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
    include('../includes/Alert.php');
    include('../includes/Loader.php');
    ?>

    <script src="//unpkg.com/alpinejs" defer></script>
    <script type="module" src="../JS/admin.js"></script>
</body>

</html>