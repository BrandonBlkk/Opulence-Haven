<?php
session_start();
include('../config/db_connection.php');
include('../includes/auto_id_func.php');
include('../includes/admin_pagination.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

$alertMessage = '';
$facilityTypeID = AutoID('facilitytypetb', 'FacilityTypeID', 'FT-', 6);
$response = ['success' => false, 'message' => '', 'generatedId' => $facilityTypeID];

// Add Facility Type
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['addfacilitytype'])) {
    $facilitytype = mysqli_real_escape_string($connect, $_POST['facilitytype']);
    $facilitytypeicon = mysqli_real_escape_string($connect, $_POST['facilitytypeicon']);
    $facilitytypeiconsize = mysqli_real_escape_string($connect, $_POST['facilitytypeiconsize']);

    $checkQuery = "SELECT FacilityType FROM facilitytypetb WHERE FacilityType = '$facilitytype'";
    $count = $connect->query($checkQuery)->num_rows;

    if ($count > 0) {
        $response['message'] = 'Facility type you added is already existed.';
    } else {
        $addFacilityTypeQuery = "INSERT INTO facilitytypetb (FacilityTypeID, FacilityType, FacilityTypeIcon, IconSize)
        VALUES ('$facilityTypeID', '$facilitytype', '$facilitytypeicon', '$facilitytypeiconsize')";

        if ($connect->query($addFacilityTypeQuery)) {
            $response['success'] = true;
            $response['message'] = 'A new facility type has been successfully added.';
            // Keep the generated ID in the response
            $response['generatedId'] = $facilityTypeID;
        } else {
            $response['message'] = "Failed to add facility type. Please try again.";
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Get Facility Type Details
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = mysqli_real_escape_string($connect, $_GET['id']);
    $action = $_GET['action'];

    // Build query based on action
    $query = match ($action) {
        'getFacilityTypeDetails' => "SELECT * FROM facilitytypetb WHERE FacilityTypeID = '$id'",
        default => null
    };
    if ($query) {
        $facilitytype = $connect->query($query)->fetch_assoc();

        if ($facilitytype) {
            $response['success'] = true;
            $response['facilitytype'] = $facilitytype;
        } else {
            $response['success'] = false;
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Update Facility Type
if (isset($_POST['editfacilitytype'])) {
    $facilityTypeId = mysqli_real_escape_string($connect, $_POST['facilitytypeid']);
    $updatedFacilityType = mysqli_real_escape_string($connect, $_POST['updatefacilitytype']);
    $updatedFacilityTypeIcon = mysqli_real_escape_string($connect, $_POST['updatefacilitytypeicon']);
    $updatedFacilityTypeIconSize = mysqli_real_escape_string($connect, $_POST['updatefacilitytypeiconsize']);

    $response = ['success' => false];

    // Update query
    $updateQuery = "UPDATE facilitytypetb SET 
                    FacilityType = '$updatedFacilityType', 
                    FacilityTypeIcon = '$updatedFacilityTypeIcon', 
                    IconSize = '$updatedFacilityTypeIconSize' 
                    WHERE FacilityTypeID = '$facilityTypeId'";

    if ($connect->query($updateQuery)) {
        $response['success'] = true;
        $response['message'] = 'The facility type has been successfully updated.';
        $response['generatedId'] = $facilityTypeId;
        $response['updatedFacilityType'] = $updatedFacilityType;
        $response['updatedFacilityTypeIcon'] = $updatedFacilityTypeIcon;
        $response['updatedFacilityTypeIconSize'] = $updatedFacilityTypeIconSize;
    } else {
        $response['message'] = "Failed to update facility type. Please try again.";
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Delete Facility Type
if (isset($_POST['deletefacilitytype'])) {
    $facilityTypeId = mysqli_real_escape_string($connect, $_POST['facilitytypeid']);
    $deleteQuery = "DELETE FROM facilitytypetb WHERE FacilityTypeID = '$facilityTypeId'";

    if ($connect->query($deleteQuery)) {
        $response['success'] = true;
        $response['generatedId'] = $facilityTypeId;
    } else {
        $response['success'] = false;
        $response['message'] = 'Failed to delete facility type. Please try again.';
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
                    <h2 class="text-xl text-gray-700 font-bold mb-4">Add Facility Type Overview</h2>
                    <p>Add information about facility types to categorize facilities, track usage, and manage facility details for efficient organization.</p>
                </div>
                <button id="addFacilityTypeBtn" class="bg-amber-500 text-white font-semibold px-3 py-1 rounded select-none hover:bg-amber-600 transition-colors">
                    <i class="ri-add-line text-xl"></i>
                </button>
            </div>

            <!-- Facility Type Table -->
            <div class="overflow-x-auto">
                <!-- Facility Type Search and Filter -->
                <form method="GET" class="my-4 flex items-start sm:items-center justify-between flex-col sm:flex-row gap-2 sm:gap-0">
                    <h1 class="text-lg text-gray-700 font-semibold text-nowrap">All Facility Types <span class="text-gray-400 text-sm ml-2"><?php echo $allFacilityTypeCount ?></span></h1>
                    <div class="flex items-center w-full">
                        <input type="text" name="facilitytype_search" class="p-2 ml-0 sm:ml-5 border border-gray-300 rounded-md w-full outline-none focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out" placeholder="Search for facility type..." value="<?php echo isset($_GET['facilitytype_search']) ? htmlspecialchars($_GET['facilitytype_search']) : ''; ?>">
                    </div>
                </form>

                <!-- Facility Type Table -->
                <div class="tableScrollBar overflow-y-auto max-h-[495px]">
                    <div id="facilityTypeResults">
                        <?php include '../includes/admin_table_components/facilitytype_results.php'; ?>
                    </div>
                </div>

                <!-- Pagination Controls -->
                <div id="paginationContainer" class="flex justify-between items-center mt-3">
                    <?php include '../includes/admin_table_components/facilitytype_pagination.php'; ?>
                </div>
            </div>
        </div>

        <!-- Facility Type Details Modal -->
        <div id="updateFacilityTypeModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <div class="bg-white max-w-5xl p-6 rounded-md shadow-md text-center w-full sm:max-w-[500px]">
                <h2 class="text-xl text-start text-gray-700 font-bold">Edit Facility Type</h2>
                <form class="flex flex-col space-y-4" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" id="updateFacilityTypeForm">
                    <input type="hidden" name="facilitytypeid" id="updateFacilityTypeID">
                    <!-- Facility Type Input -->
                    <div class="relative w-full">
                        <label class="block text-sm text-start font-medium text-gray-700 mb-1">Facility Type Information</label>
                        <input
                            id="updateFacilityTypeInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="text"
                            name="updatefacilitytype"
                            placeholder="Enter product type">
                        <small id="updateFacilityTypeError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>
                    <!-- Icon Input -->
                    <div class="relative w-full">
                        <label class="block text-sm text-start font-medium text-gray-700 mb-1">Icon</label>
                        <textarea
                            id="updateFacilityTypeIconInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            name="updatefacilitytypeicon"
                            placeholder="Enter icon"></textarea>
                        <small id="updateFacilityTypeIconError" class="absolute left-2 -bottom-1 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>
                    <!-- Size -->
                    <div class="relative">
                        <label class="block text-sm text-start font-medium text-gray-700 mb-1">Choose Size</label>
                        <select id="updateFacilityTypeSize" name="updatefacilitytypeiconsize" class="p-2 w-full border rounded">
                            <option value="" disabled>Select size of icon</option>
                            <option value="text-base">M</option>
                            <option value="text-lg">L</option>
                            <option value="text-xl">XL</option>
                            <option value="text-2xl">2XL</option>
                        </select>
                    </div>
                    <!-- Submit Button -->
                    <div class="flex justify-end gap-4 select-none">
                        <div id="updateFacilityTypeModalCancelBtn" class="px-4 py-2 bg-gray-200 text-black hover:bg-gray-300 rounded-sm">
                            Cancel
                        </div>
                        <button
                            type="submit"
                            name="editfacilitytype"
                            class="bg-amber-500 text-white px-4 py-2 select-none hover:bg-amber-600 rounded-sm">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Facility Type Delete Modal -->
        <div id="facilityTypeConfirmDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <form class="bg-white max-w-5xl p-6 rounded-md shadow-md text-center" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" id="facilityTypeDeleteForm">
                <h2 class="text-xl font-semibold text-red-600 mb-4">Confirm Facility Type Deletion</h2>
                <p class="text-slate-600 mb-2">You are about to delete the following Facility Type: <span id="facilityTypeDeleteName" class="font-semibold"></span></p>
                <p class="text-sm text-gray-500 mb-4">
                    Deleting this Facility Type will permanently remove it from the system, including all associated data.
                </p>
                <input type="hidden" name="facilitytypeid" id="deleteFacilityTypeID">
                <div class="flex justify-end gap-4 select-none">
                    <div id="facilityTypeCancelDeleteBtn" class="px-4 py-2 bg-gray-200 text-black hover:bg-gray-300 rounded-sm">
                        Cancel
                    </div>
                    <button
                        type="submit"
                        name="deletefacilitytype"
                        class="px-4 py-2 bg-red-600 text-white hover:bg-red-700 rounded-sm">
                        Delete
                    </button>
                </div>
            </form>
        </div>

        <!-- Add Facility Type Form -->
        <div id="addFacilityTypeModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <div class="bg-white w-full md:w-1/3 p-6 rounded-md shadow-md ">
                <h2 class="text-xl text-gray-700 font-bold mb-4">Add New Facility Type</h2>
                <form class="flex flex-col space-y-4" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" id="facilityTypeForm">
                    <!-- Facility Type Input -->
                    <div class="relative w-full">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Facility Type Information</label>
                        <input
                            id="facilityTypeInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="text"
                            name="facilitytype"
                            placeholder="Enter facility type">
                        <small id="facilityTypeError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>
                    <!-- Icon Input -->
                    <div class="relative">
                        <textarea
                            id="facilityTypeIconInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="text"
                            name="facilitytypeicon"
                            placeholder="Enter icon (ri-sofa-line)"></textarea>
                        <small id="facilityTypeIconError" class="absolute left-2 -bottom-1 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>
                    <!-- Size -->
                    <div class="relative">
                        <label class="block text-sm text-start font-medium text-gray-700 mb-1">Choose Size</label>
                        <select id="facilityTypeSize" name="facilitytypeiconsize" class="p-2 w-full border rounded">
                            <option value="" disabled>Select size of icon</option>
                            <option value="text-base">M</option>
                            <option value="text-lg">L</option>
                            <option value="text-xl" selected>XL (default)</option>
                            <option value="text-2xl">2XL</option>
                        </select>
                    </div>

                    <div class="flex justify-end gap-4 select-none">
                        <div id="addFacilityTypeCancelBtn" class="px-4 py-2 text-amber-500 font-semibold hover:text-amber-600">
                            Cancel
                        </div>
                        <!-- Submit Button -->
                        <button
                            type="submit"
                            name="addfacilitytype"
                            class="bg-amber-500 text-white font-semibold px-4 py-2 rounded-sm select-none hover:bg-amber-600 transition-colors">
                            Add Facility
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