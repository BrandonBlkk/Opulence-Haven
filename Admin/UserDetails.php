<?php
session_start();
include('../config/dbConnection.php');
include('../includes/AdminPagination.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

// Initialize search variables for user
$searchUserQuery = isset($_GET['user_search']) ? mysqli_real_escape_string($connect, $_GET['user_search']) : '';
$filterMembershipID = isset($_GET['sort']) ? $_GET['sort'] : 'random';

// Construct the user query based on search
if ($filterMembershipID !== 'random' && !empty($searchUserQuery)) {
    $userSelect = "SELECT * FROM usertb WHERE Membership = '$filterMembershipID' AND (UserName LIKE '%$searchUserQuery%' OR UserEmail LIKE '%$searchUserQuery%') LIMIT $rowsPerPage OFFSET $userOffset";
} elseif ($filterMembershipID !== 'random') {
    $userSelect = "SELECT * FROM usertb WHERE Membership = '$filterMembershipID' LIMIT $rowsPerPage OFFSET $userOffset";
} elseif (!empty($searchUserQuery)) {
    $userSelect = "SELECT * FROM usertb WHERE UserName LIKE '%$searchUserQuery%' OR UserEmail LIKE '%$searchUserQuery%' LIMIT $rowsPerPage OFFSET $userOffset";
} else {
    $userSelect = "SELECT * FROM usertb LIMIT $rowsPerPage OFFSET $userOffset";
}

$userSelectQuery = $connect->query($userSelect);
$users = [];

if (mysqli_num_rows($userSelectQuery) > 0) {
    while ($row = $userSelectQuery->fetch_assoc()) {
        $users[] = $row;
    }
}

// Construct the user count query based on search
if ($filterMembershipID !== 'random' && !empty($searchUserQuery)) {
    $userQuery = "SELECT COUNT(*) as count FROM usertb WHERE Membership = '$filterMembershipID' AND (UserName LIKE '%$searchUserQuery%' OR UserEmail LIKE '%$searchUserQuery%')";
} elseif ($filterMembershipID !== 'random') {
    $userQuery = "SELECT COUNT(*) as count FROM usertb WHERE Membership = '$filterMembershipID'";
} elseif (!empty($searchUserQuery)) {
    $userQuery = "SELECT COUNT(*) as count FROM usertb WHERE UserName LIKE '%$searchUserQuery%' OR UserEmail LIKE '%$searchUserQuery%'";
} else {
    $userQuery = "SELECT COUNT(*) as count FROM usertb";
}

// Execute the count query
$userResult = $connect->query($userQuery);
$userCount = $userResult->fetch_assoc()['count'];
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
    <div class="flex flex-col md:flex-row md:space-x-3 p-3 ml-0 md:ml-[250px] relative min-w-[380px]">
        <!-- Left Side Content -->
        <div class="w-full bg-white p-2">
            <div>
                <div>
                    <h2 class="text-xl text-gray-700 font-bold mb-4">User's Lists</h2>
                    <p>View the list of users and assign roles for efficient role-based access control.</p>
                </div>
            </div>

            <!-- Admin Table -->
            <div class="overflow-x-auto">
                <!-- Admin Search and Filter -->
                <form method="GET" class="my-4 flex items-start sm:items-center justify-between flex-col sm:flex-row gap-2 sm:gap-0">
                    <h1 class="text-lg text-gray-700 font-semibold text-nowrap">All Users <span class="text-gray-400 text-sm ml-2"><?php echo $userCount ?></span></h1>
                    <div class="flex flex-col sm:flex-row items-start sm:items-center w-full gap-2 sm:gap-0">
                        <input type="text" name="user_search" class="p-2 ml-0 sm:ml-5 border border-gray-300 rounded-md w-full"
                            placeholder="Search for user account..."
                            value="<?php echo isset($_GET['user_search']) ? htmlspecialchars($_GET['user_search']) : ''; ?>">

                        <div class="flex items-center">
                            <label for="sort" class="ml-4 mr-2 flex items-center cursor-pointer select-none">
                                <i class="ri-filter-2-line text-xl"></i>
                                <p>Filters</p>
                            </label>

                            <!-- Removed the nested form and moved the select here -->
                            <select name="sort" id="sort" class="border p-2 rounded text-sm" onchange="this.form.submit()">
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
                <div class="tableScrollBar overflow-y-auto max-h-[510px]">
                    <table class="min-w-full bg-white rounded-lg">
                        <thead>
                            <tr class="bg-gray-100 text-gray-600 text-sm">
                                <th class="p-3 text-start">User</th>
                                <th class="p-3 text-start hidden md:table-cell">Phone</th>
                                <th class="p-3 text-start hidden lg:table-cell">Membership</th>
                                <th class="p-3 text-start text-nowrap hidden xl:table-cell">Last Check Out</th>
                                <th class="p-3 text-start text-nowrap hidden xl:table-cell">Last Sign In</th>
                                <th class="p-3 text-start">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm">
                            <?php if (!empty($users)): ?>
                                <?php foreach ($users as $user):
                                    // Extract initials from the UserName
                                    $nameParts = explode(' ', trim($user['UserName'])); // Split the name by spaces
                                    $initials = substr($nameParts[0], 0, 1); // First letter of the first name
                                    if (count($nameParts) > 1) {
                                        $initials .= substr(end($nameParts), 0, 1); // First letter of the last name
                                    }

                                    $bgColor = $user['ProfileBgColor'];
                                ?>
                                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                                        <td class="p-3 text-start flex items-center gap-2 group">
                                            <p
                                                class="w-10 h-10 rounded-full bg-[<?= $bgColor ?>] text-white uppercase font-semibold flex items-center justify-center select-none">
                                                <?= $initials ?>
                                            </p>
                                            <div>
                                                <p class="font-bold"><?= htmlspecialchars($user['UserName']) ?></p>
                                                <p><?= htmlspecialchars($user['UserEmail']) ?></p>
                                            </div>

                                            <a class="opacity-0 group-hover:opacity-100 transition-all duration-200" href="mailto:<?= htmlspecialchars($user['UserEmail']) ?>"><i class="ri-mail-fill text-lg"></i></a>
                                        </td>
                                        <td class="p-3 text-start hidden md:table-cell">
                                            <?= htmlspecialchars($user['UserPhone']) ? htmlspecialchars($user['UserPhone']) : 'N/A' ?>
                                        </td>
                                        <td class="p-3 text-start space-x-1 select-none hidden lg:table-cell">
                                            <?php if ($user['Membership'] == '1'): ?>
                                                <span><i class="ri-vip-crown-line text-yellow-500"></i> VIP Email</span>
                                            <?php else: ?>
                                                <span><i class="ri-mail-line text-gray-500"></i> Standard Email</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="p-3 text-start hidden md:table-cell">
                                            <?= htmlspecialchars(date('d M Y', strtotime($user['LastSignIn']))) ?>
                                        </td>
                                        <td class="p-3 text-start hidden md:table-cell">
                                            <?= htmlspecialchars(date('d M Y', strtotime($user['LastSignIn']))) ?>
                                        </td>
                                        <td class="p-3 text-start space-x-1 select-none">
                                            <i class="details-btn ri-eye-line text-lg cursor-pointer"
                                                data-user-id="<?= htmlspecialchars($user['UserID']) ?>"></i>
                                            <button class=" text-red-500">
                                                <i class="delete-btn ri-delete-bin-7-line text-xl"
                                                    data-user-id="<?= htmlspecialchars($user['UserID']) ?>"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="p-3 text-center text-gray-500 py-52">
                                        No users available.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Controls -->
                <div class="flex justify-center items-center mt-1 <?= (!empty($users) ? 'flex' : 'hidden') ?>">
                    <!-- Previous Btn -->
                    <?php if ($userCurrentPage > 1) {
                    ?>
                        <a href="?userpage=<?= $userCurrentPage - 1 ?>&user_search=<?= htmlspecialchars($searchUserQuery) ?>&sort=<?= htmlspecialchars($filterMembershipID) ?>"
                            class="px-3 py-1 mx-1 border rounded <?= $userpage == $userCurrentPage ? 'bg-gray-200' : 'bg-white' ?>">
                            <i class="ri-arrow-left-s-line"></i>
                        </a>
                    <?php
                    } else {
                    ?>
                        <p class="px-3 py-1 mx-1 border rounded cursor-not-allowed bg-gray-200">
                            <i class="ri-arrow-left-s-line"></i>
                        </p>
                    <?php
                    }
                    ?>
                    <?php for ($userpage = 1; $userpage <= $totalUserPages; $userpage++): ?>
                        <a href="?userpage=<?= $userpage ?>&user_search=<?= htmlspecialchars($searchUserQuery) ?>&sort=<?= htmlspecialchars($filterMembershipID) ?>"
                            class="px-3 py-1 mx-1 border rounded select-none <?= $userpage == $userCurrentPage ? 'bg-gray-200' : 'bg-white' ?>">
                            <?= $userpage ?>
                        </a>
                    <?php endfor; ?>
                    <!-- Next Btn -->
                    <?php if ($userCurrentPage < $totalUserPages) {
                    ?>
                        <a href="?userpage=<?= $userCurrentPage + 1 ?>&user_search=<?= htmlspecialchars($searchUserQuery) ?>&sort=<?= htmlspecialchars($filterMembershipID) ?>"
                            class="px-3 py-1 mx-1 border rounded <?= $userpage == $userCurrentPage ? 'bg-gray-200' : 'bg-white' ?>">
                            <i class="ri-arrow-right-s-line"></i>
                        </a>
                    <?php
                    } else {
                    ?>
                        <p class="px-3 py-1 mx-1 border rounded cursor-not-allowed bg-gray-200">
                            <i class="ri-arrow-right-s-line"></i>
                        </p>
                    <?php
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- Admin Delete Modal -->
        <div id="adminConfirmDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <form class="bg-white max-w-lg p-6 rounded-md shadow-md text-center" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" id="adminDeleteForm">
                <h2 class="text-xl font-semibold text-red-600 mb-4">Confirm Admin Deletion</h2>
                <p class="text-slate-600 mb-2">You are about to delete the following Admin:</p>
                <div class="flex justify-center items-center gap-2 mb-2">
                    <div class="relative">
                        <div class="w-16 h-16 rounded-full select-none">
                            <img id="adminDeleteProfile" src="" alt="Admin Profile" class="w-full h-full object-cover rounded-full mx-auto">
                        </div>
                        <i class="ri-alert-line bg-slate-200 bg-opacity-55 text-red-500 text-lg absolute -bottom-1 -right-1 rounded-full flex items-center justify-center w-6 h-6 p-1"></i>
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
                <h2 class="text-xl font-bold mb-4">Add New Role</h2>
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
    // include('../includes/Alert.php');
    // include('../includes/Loader.php');
    ?>

    <script src="//unpkg.com/alpinejs" defer></script>
    <script type="module" src="../JS/admin.js"></script>
</body>

</html>