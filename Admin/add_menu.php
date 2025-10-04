<?php
session_start();
require_once('../config/db_connection.php');
include_once('../includes/auto_id_func.php');
include_once('../includes/admin_pagination.php');
require_once('../includes/auth_check.php');

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
    $location = mysqli_real_escape_string($connect, $_POST['location']);

    // Menu PDF upload
    $menuPDF = $_FILES["menupdf"]["name"];
    $copyFile = "AdminPDFs/";
    $fileName = $copyFile . uniqid() . "_" . basename($menuPDF);

    if ($_FILES["menupdf"]["type"] !== "application/pdf") {
        $response['message'] = "Only PDF files are allowed.";
    } elseif ($_FILES["menupdf"]["size"] > 5 * 1024 * 1024) {
        $response['message'] = "PDF file exceeds 5MB limit.";
    } else {
        $copy = copy($_FILES["menupdf"]["tmp_name"], $fileName);

        if (!$copy) {
            $response['message'] = "Cannot upload PDF.";
        } else {
            // Check duplicate menu
            $checkQuery = "SELECT MenuName FROM menutb WHERE MenuName = ?";
            $checkStmt = $connect->prepare($checkQuery);
            $checkStmt->bind_param("s", $menuName);
            $checkStmt->execute();
            $checkStmt->store_result();
            $count = $checkStmt->num_rows;
            $checkStmt->close();

            if ($count > 0) {
                $response['message'] = 'Menu you added already exists.';
            } else {
                $addMenuQuery = "INSERT INTO menutb (MenuID, MenuPDF, MenuName, Description, StartTime, EndTime, Location, Status) 
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $connect->prepare($addMenuQuery);
                if ($stmt === false) {
                    $response['message'] = "Prepare failed: " . htmlspecialchars($connect->error);
                } else {
                    $stmt->bind_param("ssssssss", $menuID, $fileName, $menuName, $description, $startTime, $endTime, $location, $status);
                    if ($stmt->execute()) {
                        $response['success'] = true;
                        $response['message'] = 'A new menu has been successfully added.';
                        $response['generatedId'] = $menuID;
                    } else {
                        $response['message'] = "Failed to add menu. Error: " . htmlspecialchars($stmt->error);
                    }
                    $stmt->close();
                }
            }
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Get Menu Details
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

    $menuId = mysqli_real_escape_string($connect, $_POST['menuid']);
    $menuName = mysqli_real_escape_string($connect, $_POST['updatemenuname']);
    $description = mysqli_real_escape_string($connect, $_POST['updatedescription']);
    $startTime = date("h:i A", strtotime(mysqli_real_escape_string($connect, $_POST['updatestarttime'])));
    $endTime = date("h:i A", strtotime(mysqli_real_escape_string($connect, $_POST['updateendtime'])));
    $status = mysqli_real_escape_string($connect, $_POST['status']);
    $location = mysqli_real_escape_string($connect, $_POST['updatelocation']);
    $removePdf = isset($_POST['updateRemovePdf']) ? $_POST['updateRemovePdf'] : "0";

    try {
        // Get existing PDF
        $existingPdf = '';
        $result = $connect->query("SELECT MenuPDF FROM menutb WHERE MenuID = '$menuId'");
        if ($result && $row = $result->fetch_assoc()) {
            $existingPdf = $row['MenuPDF'];
        }

        // Handle new PDF upload
        if (!empty($_FILES["updatemenupdf"]["name"])) {
            $menuPdf = $_FILES["updatemenupdf"]["name"];
            $copyFile = "AdminPDFs/";
            $fileName = $copyFile . uniqid() . "_" . $menuPdf;
            if (!copy($_FILES["updatemenupdf"]["tmp_name"], $fileName)) {
                throw new Exception("Failed to upload Menu PDF.");
            }

            // Remove old PDF if exists
            if ($existingPdf && file_exists($existingPdf)) {
                unlink($existingPdf);
            }

            $updateQuery = "UPDATE menutb SET MenuName = ?, Description = ?, StartTime = ?, EndTime = ?, Location = ?, Status = ?, MenuPDF = ? WHERE MenuID = ?";
            $stmt = $connect->prepare($updateQuery);
            $stmt->bind_param("ssssssss", $menuName, $description, $startTime, $endTime, $location, $status, $fileName, $menuId);
        } else if ($removePdf === "1") {
            // Remove PDF
            if ($existingPdf && file_exists($existingPdf)) {
                unlink($existingPdf);
            }
            $updateQuery = "UPDATE menutb SET MenuName = ?, Description = ?, StartTime = ?, EndTime = ?, Location = ?, Status = ?, MenuPDF = NULL WHERE MenuID = ?";
            $stmt = $connect->prepare($updateQuery);
            $stmt->bind_param("sssssss", $menuName, $description, $startTime, $endTime, $location, $status, $menuId);
        } else {
            // No PDF change
            $updateQuery = "UPDATE menutb SET MenuName = ?, Description = ?, StartTime = ?, EndTime = ?, Location = ?, Status = ? WHERE MenuID = ?";
            $stmt = $connect->prepare($updateQuery);
            $stmt->bind_param("sssssss", $menuName, $description, $startTime, $endTime, $location, $status, $menuId);
        }

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'The menu has been successfully updated.';
            $response['generatedId'] = $menuId;
            $response['updatedMenuName'] = $menuName;
            $response['updatedDescription'] = $description;
        } else {
            $response['message'] = "Failed to update menu.";
        }

        $stmt->close();
    } catch (Exception $e) {
        $response['message'] = "Error: " . $e->getMessage();
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Delete Menu
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

// Bulk Delete Menus
if (isset($_POST['bulkdeletemenus'])) {
    $ids = $_POST['menuids'] ?? [];

    $response = ['success' => false];

    if (!empty($ids)) {
        $ids = array_map(function ($id) use ($connect) {
            return "'" . mysqli_real_escape_string($connect, $id) . "'";
        }, $ids);

        $idsList = implode(',', $ids);
        $deleteQuery = "DELETE FROM menutb WHERE MenuID IN ($idsList)";

        if ($connect->query($deleteQuery)) {
            $response['success'] = true;
            $response['deletedIds'] = $ids;
        } else {
            $response['message'] = 'Failed to delete selected menus. Please try again.';
        }
    } else {
        $response['message'] = 'No menus selected for deletion.';
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
                <div class="flex gap-2">
                    <button id="addMenuBtn" class="bg-amber-500 text-white font-semibold px-3 py-1 rounded select-none hover:bg-amber-600 transition-colors">
                        <i class="ri-add-line text-xl"></i>
                    </button>
                    <button id="bulkDeleteMenuBtn"
                        class="hidden px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 transition-colors">
                        Delete Selected
                    </button>
                </div>
            </div>

            <!-- Menu Table -->
            <div class="overflow-x-auto">
                <!-- Menu Search and Filter -->
                <form method="GET" class="my-4 flex items-start sm:items-center justify-between flex-col sm:flex-row gap-2 sm:gap-0">
                    <h1 class="text-lg text-gray-700 font-semibold text-nowrap">All Menus <span class="text-gray-400 text-sm ml-2"><?php echo $menuCount ?></span></h1>
                    <div class="flex flex-col sm:flex-row items-start sm:items-center w-full gap-2 sm:gap-0">
                        <input type="text" name="menu_search" class="p-2 ml-0 sm:ml-5 border border-gray-300 rounded-md w-full outline-none focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out" placeholder="Search for menu..." value="<?php echo isset($_GET['menu_search']) ? htmlspecialchars($_GET['menu_search']) : ''; ?>">
                        <div class="flex items-center">
                            <label for="sort" class="ml-0 sm:ml-4 mr-2 flex items-center cursor-pointer select-none">
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
            <div class="reservationScrollBar bg-white w-full md:w-2/5 p-6 rounded-md shadow-md overflow-y-auto max-h-[98vh]">
                <h2 class="text-xl text-start text-gray-700 font-bold">Edit Menu</h2>
                <form class="flex flex-col space-y-4" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" id="updateMenuForm" enctype="multipart/form-data">
                    <input type="hidden" name="menuid" id="updateMenuID">

                    <!-- Menu PDF Upload -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Menu PDF (Max 5MB)</label>
                        <div id="updateMenuPreviewContainer" class="hidden mb-4">
                            <div class="relative group border border-gray-200 rounded-lg p-3 bg-gray-50">
                                <p id="updateMenuPreview" class="text-sm text-gray-700 truncate"></p>
                                <!-- View link -->
                                <a id="updateMenuViewLink" href="#" target="_blank" class="block text-blue-600 text-xs underline mt-1"></a>
                                <button type="button" onclick="removeUpdateFile()"
                                    class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm hover:bg-red-600 shadow-md">
                                    ×
                                </button>
                            </div>
                        </div>
                        <div class="relative">
                            <div id="updateUploadArea" class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center bg-gray-50 hover:bg-gray-100 transition-colors cursor-pointer">
                                <div class="flex flex-col items-center justify-center space-y-2 py-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    <p class="text-sm text-gray-600">Click to upload or drag and drop</p>
                                    <p class="text-xs text-gray-500">PDF only (Max. 5MB)</p>
                                </div>
                                <input type="file" name="updatemenupdf" id="updateCoverInput"
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept="application/pdf">
                            </div>
                        </div>
                        <input type="hidden" id="updateRemovePdf" name="updateRemovePdf" value="0">
                    </div>

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

                    <!-- Location Input -->
                    <div class="relative">
                        <input
                            id="updateMenuLocationInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="text"
                            name="updatelocation"
                            placeholder="Enter menu location">
                        <small id="updateMenuLocationError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
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

        <!-- Menu Delete Modal -->
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

        <!-- Menu Bulk Delete Modal -->
        <div id="menuBulkDeleteModal"
            class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <div class="bg-white max-w-lg p-6 rounded-md shadow-md text-center">
                <h2 class="text-xl font-semibold text-red-600 mb-4">Confirm Bulk Deletion</h2>
                <p class="text-slate-600 mb-2">
                    You are about to delete <span id="menuBulkDeleteCount" class="font-semibold">0</span> Menus.
                </p>
                <p class="text-sm text-gray-500 mb-4">
                    This action cannot be undone. All selected menus will be permanently removed from the system.
                </p>
                <div class="flex justify-end gap-4 select-none">
                    <div id="bulkDeleteMenuCancelBtn"
                        class="px-4 py-2 bg-gray-200 text-black hover:bg-gray-300 rounded-sm cursor-pointer">
                        Cancel
                    </div>
                    <button type="button" id="bulkDeleteMenuConfirmBtn"
                        class="px-4 py-2 bg-red-600 text-white hover:bg-red-700 rounded-sm">
                        Delete
                    </button>
                </div>
            </div>
        </div>

        <!-- Add Menu Form -->
        <div id="addMenuModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <div class="reservationScrollBar bg-white w-full md:w-2/5 p-6 rounded-md shadow-md overflow-y-auto max-h-[98vh]">
                <h2 class="text-xl text-gray-700 font-bold mb-4">Add New Menu</h2>
                <form class="flex flex-col space-y-4" action="<?php echo $_SERVER["PHP_SELF"]; ?>" enctype="multipart/form-data" method="post" id="menuForm">
                    <!-- Menu PDF Upload -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Menu PDF (Max 5MB)</label>
                        <div id="menuPreviewContainer" class="hidden mb-4">
                            <div class="relative group border border-gray-200 rounded-lg p-3 bg-gray-50">
                                <p id="menuPreview" class="text-sm text-gray-700 truncate"></p>
                                <button type="button" onclick="removeCoverFile()"
                                    class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm hover:bg-red-600 shadow-md">
                                    ×
                                </button>
                            </div>
                        </div>
                        <div class="relative">
                            <div id="uploadArea" class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center bg-gray-50 hover:bg-gray-100 transition-colors cursor-pointer">
                                <div class="flex flex-col items-center justify-center space-y-2 py-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    <p class="text-sm text-gray-600">Click to upload or drag and drop</p>
                                    <p class="text-xs text-gray-500">PDF only (Max. 5MB)</p>
                                </div>
                                <input type="file" name="menupdf" id="cover-input" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept="application/pdf">
                            </div>
                        </div>
                    </div>
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

                    <!-- Location Input -->
                    <div class="relative">
                        <input
                            id="menuLocationInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="text"
                            name="location"
                            placeholder="Enter menu location">
                        <small id="menuLocationError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
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

    <script>
        // Elements for menu PDF
        const coverInput = document.getElementById('cover-input');
        const menuPreview = document.getElementById('menuPreview');
        const menuPreviewContainer = document.getElementById('menuPreviewContainer');
        const uploadArea = document.getElementById('uploadArea');

        coverInput.addEventListener('change', function(event) {
            handleFilePreview(event.target, menuPreview, menuPreviewContainer, uploadArea);
        });

        function removeCoverFile() {
            coverInput.value = '';
            menuPreviewContainer.classList.add('hidden');
            uploadArea.classList.remove('hidden');
        }

        // Drag & Drop for Add
        setupDragDrop(uploadArea, coverInput, menuPreview, menuPreviewContainer);

        // Elements for Update
        const updateCoverInput = document.getElementById('updateCoverInput');
        const updateMenuPreview = document.getElementById('updateMenuPreview');
        const updateMenuPreviewContainer = document.getElementById('updateMenuPreviewContainer');
        const updateUploadArea = document.getElementById('updateUploadArea');
        const updateRemovePdf = document.getElementById('updateRemovePdf');

        updateCoverInput.addEventListener('change', function(event) {
            handleFilePreview(event.target, updateMenuPreview, updateMenuPreviewContainer, updateUploadArea);
            updateRemovePdf.value = "0"; // If user selects a new file, don't remove
        });

        function removeUpdateFile() {
            updateCoverInput.value = '';
            updateMenuPreviewContainer.classList.add('hidden');
            updateUploadArea.classList.remove('hidden');
            updateRemovePdf.value = "1"; // Mark PDF for removal
        }

        // Drag & Drop for Update
        setupDragDrop(updateUploadArea, updateCoverInput, updateMenuPreview, updateMenuPreviewContainer);

        // Handle File Preview
        function handleFilePreview(input, previewElement, container, area) {
            const file = input.files[0];
            if (file) {
                if (file.type !== "application/pdf") {
                    alert("Only PDF files are allowed.");
                    input.value = '';
                    return;
                }
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size exceeds 5MB limit');
                    input.value = '';
                    return;
                }
                previewElement.textContent = file.name;
                container.classList.remove('hidden');
                area.classList.add('hidden');
            }
        }

        function setupDragDrop(area, input, preview, container) {
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                area.addEventListener(eventName, preventDefaults, false);
            });
            ['dragenter', 'dragover'].forEach(eventName => {
                area.addEventListener(eventName, () => highlight(area), false);
            });
            ['dragleave', 'drop'].forEach(eventName => {
                area.addEventListener(eventName, () => unhighlight(area), false);
            });

            area.addEventListener('drop', function(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                if (files.length > 0) {
                    if (files[0].type !== "application/pdf") {
                        alert("Only PDF files are allowed.");
                        return;
                    }
                    input.files = files;
                    input.dispatchEvent(new Event('change'));
                }
            });
        }

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        function highlight(element) {
            element.classList.add('border-blue-400', 'bg-blue-50');
        }

        function unhighlight(element) {
            element.classList.remove('border-blue-400', 'bg-blue-50');
        }
    </script>

    <!-- Loader -->
    <?php
    include('../includes/alert.php');
    include('../includes/loader.php');
    ?>

    <script src="//unpkg.com/alpinejs" defer></script>
    <script type="module" src="../JS/admin.js"></script>
</body>

</html>