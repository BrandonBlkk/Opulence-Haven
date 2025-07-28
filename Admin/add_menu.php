<?php
session_start();
include('../config/db_connection.php');
include('../includes/auto_id_func.php');
include('../includes/admin_pagination.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

$alertMessage = '';
$menuID = AutoID('menutb', 'MenuID', 'M-', 6);

// Add Menu
$response = ['success' => false, 'message' => '', 'generatedId' => $menuID];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['addmenu'])) {
    $menuName = mysqli_real_escape_string($connect, $_POST['menuname']);
    $description = mysqli_real_escape_string($connect, $_POST['description']);
    $startTime = mysqli_real_escape_string($connect, $_POST['starttime']);
    $startTime = date("h:i A", strtotime($startTime));
    $endTime = mysqli_real_escape_string($connect, $_POST['endtime']);
    $endTime = date("h:i A", strtotime($endTime));
    $status = mysqli_real_escape_string($connect, $_POST['status']);

    // Check if the menu already exists using prepared statement
    $checkQuery = "SELECT MenuName FROM menutb WHERE MenuName = ?";
    $checkStmt = $connect->prepare($checkQuery);
    $checkStmt->bind_param("s", $menuName);
    $checkStmt->execute();
    $checkStmt->store_result();
    $count = $checkStmt->num_rows;
    $checkStmt->close();

    if ($count > 0) {
        $response['message'] = 'Menu you added is already existed.';
    } else {
        $addMenuQuery = "INSERT INTO menutb (MenuID, MenuName, Description, StartTime, EndTime, Status) 
                 VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $connect->prepare($addMenuQuery);
        if ($stmt === false) {
            $response['message'] = "Prepare failed: " . htmlspecialchars($connect->error);
        } else {
            // Corrected bind_param - removed extra parameter (was 7?, now 6)
            $stmt->bind_param("ssssss", $menuID, $menuName, $description, $startTime, $endTime, $status);

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'A new menu has been successfully added.';
                $response['generatedId'] = $menuID;
            } else {
                $response['message'] = "Failed to add menu. Please try again. Error: " . htmlspecialchars($stmt->error);
            }
            $stmt->close();
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Get Product Type Details
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = mysqli_real_escape_string($connect, $_GET['id']);
    $action = $_GET['action'];
    $query = match ($action) {
        'getMenuDetails' => "SELECT * FROM menutb WHERE MenuID = ?",
        default => null
    };

    if ($query) {
        $stmt = $connect->prepare($query);

        if ($action === 'getMenuDetails') {
            $stmt->bind_param("s", $id);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $menu = $result->fetch_assoc();

        if ($menu) {
            $response['success'] = true;
            $response['menu'] = $menu;
        } else {
            $response['success'] = true;
        }

        $stmt->close();
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Update Menu
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editmenu'])) {
    $response = ['success' => false, 'message' => '', 'generatedId' => ''];

    // Get and sanitize input data
    $menuId = mysqli_real_escape_string($connect, $_POST['menuid']);
    $menuName = mysqli_real_escape_string($connect, $_POST['updatemenuname']);
    $description = mysqli_real_escape_string($connect, $_POST['updatedescription']);
    $startTime = mysqli_real_escape_string($connect, $_POST['updatestarttime']);
    $startTime = date("h:i A", strtotime($startTime));
    $endTime = mysqli_real_escape_string($connect, $_POST['updateendtime']);
    $endTime = date("h:i A", strtotime($endTime));
    $status = mysqli_real_escape_string($connect, $_POST['status']);

    try {
        // Prepare update statement
        $updateQuery = "UPDATE menutb SET 
                        MenuName = ?,
                        Description = ?,
                        StartTime = ?,
                        EndTime = ?,
                        Status = ?
                        WHERE MenuID = ?";

        $stmt = $connect->prepare($updateQuery);

        if ($stmt === false) {
            throw new Exception('Prepare failed: ' . htmlspecialchars($connect->error));
        }

        // Bind parameters and execute
        $stmt->bind_param(
            "ssssss",
            $menuName,
            $description,
            $startTime,
            $endTime,
            $status,
            $menuId
        );

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'The menu has been successfully updated.';
            $response['generatedId'] = $menuId;
            $response['updatedMenuName'] = $menuName;
            $response['updatedDescription'] = $description;
        } else {
            $response['message'] = "Failed to update menu. Please try again.";
        }

        $stmt->close();
    } catch (Exception $e) {
        $response['message'] = "Error: " . $e->getMessage();
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Delete Menu (keeping original parameter name deleteproducttype)
if (isset($_POST['deletemenu'])) {
    $menuId = mysqli_real_escape_string($connect, $_POST['menuid']);

    // Build query
    $deleteQuery = "DELETE FROM menutb WHERE MenuID = ?";

    // Prepare and execute
    $stmt = $connect->prepare($deleteQuery);

    if ($stmt === false) {
        $response['message'] = 'Prepare failed: ' . htmlspecialchars($connect->error);
    } else {
        $stmt->bind_param("s", $menuId);
        $success = $stmt->execute();
        $stmt->close();

        // Set response
        if ($success) {
            $response['success'] = true;
            $response['message'] = 'Menu deleted successfully';
            $response['generatedId'] = $menuId;
        } else {
            $response['message'] = 'Failed to delete menu. It may not exist.';
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

<body>
    <?php include('../includes/admin_navbar.php'); ?>

    <!-- Main Container -->
    <div class="flex flex-col md:flex-row md:space-x-3 p-3 ml-0 md:ml-[250px] min-w-[380px]">
        <!-- Left Side Content -->
        <div class="w-full bg-white p-2">
            <div class="flex justify-between items-end">
                <div>
                    <h2 class="text-xl text-gray-700 font-bold mb-4">Add Dining Menu Overview</h2>
                    <p>Add information about menu to categorize items, track stock levels, and manage product details for efficient organization.</p>
                </div>
                <button id="addMenuBtn" class="bg-amber-500 text-white font-semibold px-3 py-1 rounded select-none hover:bg-amber-600 transition-colors">
                    <i class="ri-add-line text-xl"></i>
                </button>
            </div>

            <!-- Menu Table -->
            <div class="overflow-x-auto">
                <!-- Menu Search and Filter -->
                <form method="GET" class="my-4 flex items-start sm:items-center justify-between flex-col sm:flex-row gap-2 sm:gap-0">
                    <h1 class="text-lg text-gray-700 font-semibold text-nowrap">All Menus <span class="text-gray-400 text-sm ml-2"><?php echo $menuCount ?></span></h1>
                    <div class="flex items-center w-full">
                        <input type="text" name="menu_search" class="p-2 ml-0 sm:ml-5 border border-gray-300 rounded-md w-full outline-none focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out" placeholder="Search for menu..." value="<?php echo isset($_GET['menu_search']) ? htmlspecialchars($_GET['menu_search']) : ''; ?>">
                        <div class="flex items-center">
                            <label for="sort" class="ml-4 mr-2 flex items-center cursor-pointer select-none">
                                <i class="ri-filter-2-line text-xl"></i>
                                <p>Filters</p>
                            </label>
                            <!-- Search and filter form -->
                            <form method="GET" class="flex flex-col md:flex-row items-center gap-4 mb-4">
                                <select name="sort" id="menuFilter" class="border p-2 rounded text-sm outline-none">
                                    <option value="random">All Status</option>
                                    <option value="available">Available</option>
                                    <option value="unavailable">Unavailable</option>
                                    ?>
                                </select>
                            </form>
                        </div>
                    </div>
                </form>

                <!-- Product Type Table -->
                <div class="tableScrollBar overflow-y-auto max-h-[495px]">
                    <div id="menuResults">
                        <?php include '../includes/admin_table_components/menu_results.php'; ?>
                    </div>
                </div>

                <!-- Pagination Controls -->
                <div id="paginationContainer" class="flex justify-between items-center mt-3">
                    <?php include '../includes/admin_table_components/menu_pagination.php'; ?>
                </div>
            </div>
        </div>

        <!-- Menu Details Modal -->
        <div id="updateMenuModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <div class="bg-white max-w-5xl p-6 rounded-md shadow-md text-center w-full sm:max-w-[500px]">
                <h2 class="text-xl text-start text-gray-700 font-bold">Edit Menu</h2>
                <form class="flex flex-col space-y-4" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" id="updateMenuForm">
                    <input type="hidden" name="menuid" id="updateMenuID">
                    <!-- Menu Input -->
                    <div class="relative w-full">
                        <label class="block text-start text-sm font-medium text-gray-700 mb-1">Menu Information</label>
                        <input
                            id="updateMenuNameInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="text"
                            name="updatemenuname"
                            placeholder="Enter menu">
                        <small id="updateMenuNameError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>

                    <!-- Description Input -->
                    <div class="relative">
                        <textarea
                            id="updateMenuDescriptionInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="text"
                            name="updatedescription"
                            placeholder="Enter menu description"></textarea>
                        <small id="updateMenuDescriptionError" class="absolute left-2 -bottom-1 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4 sm:gap-2">
                        <div class="flex flex-1 flex-col gap-2">
                            <label for="time" class="block text-start text-sm font-medium text-gray-700">Start Time</label>
                            <input type="time" id="updateStartTime" name="updatestarttime" class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out" required>
                        </div>

                        <div class="flex flex-1 flex-col gap-2">
                            <label for="time" class="block text-start text-sm font-medium text-gray-700">End Time</label>
                            <input type="time" id="updateEndTime" name="updateendtime" class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out" required>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="relative">
                        <select name="status" id="updateStatus" class="p-2 w-full border rounded outline-none" required>
                            <option value="" disabled selected>Status</option>
                            <option value="available">Available</option>
                            <option value="unavailable">Unavailable</option>
                        </select>
                    </div>
                    <!-- Submit Button -->
                    <div class="flex justify-end gap-4 select-none">
                        <div id="updateMenuModalCancelBtn" class="px-4 py-2 bg-gray-200 text-black hover:bg-gray-300 rounded-sm">
                            Cancel
                        </div>
                        <button
                            type="submit"
                            name="editmenu"
                            class="bg-amber-500 text-white px-4 py-2 select-none hover:bg-amber-600 rounded-sm">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Product Type Delete Modal -->
        <div id="menuConfirmDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <form class="bg-white max-w-5xl p-6 rounded-md shadow-md text-center" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" id="menuDeleteForm">
                <h2 class="text-xl font-semibold text-red-600 mb-4">Confirm Menu Deletion</h2>
                <p class="text-slate-600 mb-2">You are about to delete the following Menu: <span id="menuDeleteName" class="font-semibold"></span></p>
                <p class="text-sm text-gray-500 mb-4">
                    Deleting this Menu will permanently remove it from the system, including all associated data.
                </p>
                <input type="hidden" name="menuid" id="deleteMenuID">
                <div class="flex justify-end gap-4 select-none">
                    <div id="menuCancelDeleteBtn" class="px-4 py-2 bg-gray-200 text-black hover:bg-gray-300 rounded-sm">
                        Cancel
                    </div>
                    <button
                        type="submit"
                        name="deletemenu"
                        class="px-4 py-2 bg-red-600 text-white hover:bg-red-700 rounded-sm">
                        Delete
                    </button>
                </div>
            </form>
        </div>

        <!-- Add Menu Form -->
        <div id="addMenuModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <div class="bg-white w-full md:w-1/3 p-6 rounded-md shadow-md ">
                <h2 class="text-xl text-gray-700 font-bold mb-4">Add New Menu</h2>
                <form class="flex flex-col space-y-4" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" id="menuForm">
                    <!-- Menu Input -->
                    <div class="relative w-full">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Menu Information</label>
                        <input
                            id="menuNameInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="text"
                            name="menuname"
                            placeholder="Enter menu">
                        <small id="menuNameError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>

                    <!-- Description Input -->
                    <div class="relative">
                        <textarea
                            id="menuDescriptionInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="text"
                            name="description"
                            placeholder="Enter menu description"></textarea>
                        <small id="menuDescriptionError" class="absolute left-2 -bottom-1 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4 sm:gap-2">
                        <div class="flex flex-1 flex-col gap-2">
                            <label for="time" class="block text-sm font-medium text-gray-700">Start Time</label>
                            <input type="time" id="time" name="starttime" class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out" required>
                        </div>

                        <div class="flex flex-1 flex-col gap-2">
                            <label for="time" class="block text-sm font-medium text-gray-700">End Time</label>
                            <input type="time" id="time" name="endtime" class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out" required>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="relative">
                        <select name="status" id="status" class="p-2 w-full border rounded outline-none" required>
                            <option value="" disabled selected>Status</option>
                            <option value="available">Available</option>
                            <option value="unavailable">Unavailable</option>
                        </select>
                    </div>

                    <div class="flex justify-end gap-4 select-none">
                        <div id="addMenuCancelBtn" class="px-4 py-2 text-amber-500 font-semibold hover:text-amber-600">
                            Cancel
                        </div>

                        <!-- Submit Button -->
                        <button
                            type="submit"
                            name="addmenu"
                            class="bg-amber-500 text-white font-semibold px-4 py-2 rounded-sm select-none hover:bg-amber-600 transition-colors">
                            Add Menu
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
</body>

</html>