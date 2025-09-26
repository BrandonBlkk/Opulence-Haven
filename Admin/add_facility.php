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
$facilityID = AutoID('facilitytb', 'FacilityID', 'F-', 6);
$response = ['success' => false, 'message' => '', 'generatedId' => $facilityID];

// Add Facility
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['addfacility'])) {
    $facility = mysqli_real_escape_string($connect, $_POST['facility']);
    $facilityicon = mysqli_real_escape_string($connect, $_POST['facilityicon']);
    $facilityiconsize = mysqli_real_escape_string($connect, $_POST['facilityiconsize']);
    $additionalcharge = mysqli_real_escape_string($connect, $_POST['additionalcharge']);
    $popular = mysqli_real_escape_string($connect, $_POST['popular']);
    $facilityType = mysqli_real_escape_string($connect, $_POST['facilityType']);

    // Check if the product  already exists using prepared statement
    $checkQuery = "SELECT Facility FROM facilitytb WHERE Facility = '$facility' AND FacilityTypeID = '$facilityType'";
    $count = $connect->query($checkQuery)->num_rows;

    if ($count > 0) {
        $response['message'] = 'Facility you added is already existed.';
    } else {
        $addFacilityQuery = "INSERT INTO facilitytb (FacilityID, Facility, FacilityIcon, IconSize, AdditionalCharge, Popular, FacilityTypeID)
        VALUES ('$facilityID', '$facility', '$facilityicon', '$facilityiconsize', '$additionalcharge', '$popular', '$facilityType')";

        if ($connect->query($addFacilityQuery)) {
            $response['success'] = true;
            $response['message'] = 'A new facility type has been successfully added.';
            // Keep the generated ID in the response
            $response['generatedId'] = $facilityID;
        } else {
            $response['message'] = "Failed to add facility. Please try again.";
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Get Facility  Details
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = mysqli_real_escape_string($connect, $_GET['id']);
    $action = $_GET['action'];

    // Build query based on action
    $query = match ($action) {
        'getFacilityDetails' => "SELECT * FROM facilitytb WHERE FacilityID = '$id'",
        default => null
    };
    if ($query) {
        $facility = $connect->query($query)->fetch_assoc();

        if ($facility) {
            $response['success'] = true;
            $response['facility'] = $facility;
        } else {
            $response['success'] = true;
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Update Facility 
if (isset($_POST['editfacility'])) {
    $facilityId = mysqli_real_escape_string($connect, $_POST['facilityid']);
    $updateFacility = mysqli_real_escape_string($connect, $_POST['updatefacility']);
    $updateFacilityIcon = mysqli_real_escape_string($connect, $_POST['updatefacilityicon']);
    $updateFacilityIconSize = mysqli_real_escape_string($connect, $_POST['updatefacilityiconsize']);
    $updateAdditionalcharge = mysqli_real_escape_string($connect, $_POST['updateadditionalcharge']);
    $updatePopular = mysqli_real_escape_string($connect, $_POST['updatepopular']);
    $updateFacilityType = mysqli_real_escape_string($connect, $_POST['updatefacilitytype']);

    // Update query
    $updateQuery = "UPDATE facilitytb SET Facility = '$updateFacility', FacilityIcon = '$updateFacilityIcon', IconSize = '$updateFacilityIconSize', AdditionalCharge = '$updateAdditionalcharge', Popular = '$updatePopular', FacilityTypeID = '$updateFacilityType' WHERE FacilityID = '$facilityId'";

    if ($connect->query($updateQuery)) {
        $response['success'] = true;
        $response['message'] = 'The facility has been successfully updated.';
        $response['generatedId'] = $facilityId;
        $response['updateFacility'] = $updateFacility;
        $response['updateFacilityIcon'] = $updateFacilityIcon;
        $response['updateFacilityIconSize'] = $updateFacilityIconSize;
        $response['updateAdditionalcharge'] = $updateAdditionalcharge;
        $response['updatePopular'] = $updatePopular;
        $response['updateFacilityType'] = $updateFacilityType;
    } else {
        $response['message'] = "Failed to update facility. Please try again.";
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Delete Facility 
if (isset($_POST['deletefacility'])) {
    $facilityId = mysqli_real_escape_string($connect, $_POST['facilityid']);

    // Build query based on action
    $deleteQuery = "DELETE FROM facilitytb WHERE FacilityID = '$facilityId'";

    if ($connect->query($deleteQuery)) {
        $response['success'] = true;
        $response['generatedId'] = $facilityId;
    } else {
        $response['success'] = false;
        $response['message'] = 'Failed to delete facility. Please try again.';
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Bulk Delete Facilities
if (isset($_POST['bulkdeletefacilities'])) {
    $ids = $_POST['facilityids'] ?? [];
    $response = ['success' => false];

    if (!empty($ids)) {
        $ids = array_map(function ($id) use ($connect) {
            return "'" . mysqli_real_escape_string($connect, $id) . "'";
        }, $ids);

        $idsList = implode(',', $ids);
        $deleteQuery = "DELETE FROM facilitytb WHERE FacilityID IN ($idsList)";

        if ($connect->query($deleteQuery)) {
            $response['success'] = true;
            $response['deletedIds'] = $ids;
        } else {
            $response['message'] = 'Failed to delete selected facilities. Please try again.';
        }
    } else {
        $response['message'] = 'No facilities selected for deletion.';
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
                    <h2 class="text-xl text-gray-700 font-bold mb-4">Add Facility Overview</h2>
                    <p>Add information about room facilities to categorize room types, track usage, and manage facility details for efficient organization.</p>
                </div>
                <div class="flex gap-2">
                    <button id="addFacilityBtn" class="bg-amber-500 text-white font-semibold px-3 py-1 rounded select-none hover:bg-amber-600 transition-colors">
                        <i class="ri-add-line text-xl"></i>
                    </button>
                    <button id="bulkDeleteFacilitiesBtn"
                        class="hidden px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600">
                        Delete Selected
                    </button>
                </div>
            </div>

            <!-- Facility Type Table -->
            <div class="overflow-x-auto">
                <!-- Facility Type Search and Filter -->
                <form method="GET" class="my-4 flex items-start sm:items-center justify-between flex-col sm:flex-row gap-2 sm:gap-0" id="facilityFilterForm">
                    <h1 class="text-lg text-gray-700 font-semibold text-nowrap">All Facilities <span class="text-gray-400 text-sm ml-2" id="facilityCountValue"><?php echo $facilityCount ?></span></h1>
                    <div class="flex flex-col sm:flex-row items-start sm:items-center w-full gap-2 sm:gap-0">
                        <input type="text" name="facility_search" id="facilitySearch" class="p-2 ml-0 sm:ml-5 border border-gray-300 rounded-md w-full outline-none focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out" placeholder="Search for facility..." value="<?php echo isset($_GET['facility_search']) ? htmlspecialchars($_GET['facility_search']) : ''; ?>">
                        <div class="flex items-center">
                            <label for="sort" class="ml-0 sm:ml-4 mr-2 flex items-center cursor-pointer select-none">
                                <i class="ri-filter-2-line text-xl"></i>
                                <p>Filters</p>
                            </label>
                            <!-- Search and filter form -->
                            <form method="GET" class="flex flex-col md:flex-row items-center gap-4 mb-4">
                                <select name="sort" id="facilityTypeFilter" class="border p-2 rounded text-sm outline-none">
                                    <option value="random">All Facility Types</option>
                                    <?php
                                    $select = "SELECT * FROM facilitytypetb";
                                    $query = $connect->query($select);
                                    $count = $query->num_rows;

                                    if ($count) {
                                        for ($i = 0; $i < $count; $i++) {
                                            $row = $query->fetch_assoc();
                                            $facilitytype_id = $row['FacilityTypeID'];
                                            $facilitytype = $row['FacilityType'];
                                            $selected = ($filterFacilityTypeID == $facilitytype_id) ? 'selected' : '';

                                            echo "<option value='$facilitytype_id' $selected>$facilitytype</option>";
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

                <!-- Facility Table -->
                <div class="tableScrollBar overflow-y-auto max-h-[495px]">
                    <div id="facilityResults">
                        <?php include '../includes/admin_table_components/facility_results.php'; ?>
                    </div>
                </div>

                <!-- Pagination Controls -->
                <div id="paginationContainer" class="flex justify-between items-center mt-3">
                    <?php include '../includes/admin_table_components/facility_pagination.php'; ?>
                </div>
            </div>
        </div>

        <!-- Facility Details Modal -->
        <div id="updateFacilityModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <div class="bg-white max-w-5xl p-6 rounded-md shadow-md text-center w-full sm:max-w-[500px]">
                <h2 class="text-xl text-start text-gray-700 font-bold">Edit Facility</h2>
                <form class="flex flex-col space-y-4" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" id="updateFacilityForm">
                    <input type="hidden" name="facilityid" id="updateFacilityID">
                    <!-- Facility Input -->
                    <div class="relative w-full">
                        <label class="block text-sm text-start font-medium text-gray-700 mb-1">Facility Information</label>
                        <input
                            id="updateFacilityInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="text"
                            name="updatefacility"
                            placeholder="Enter facility">
                        <small id="updateFacilityError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>
                    <!-- Icon Input -->
                    <div class="relative w-full">
                        <label class="block text-sm text-start font-medium text-gray-700 mb-1">Optional</label>
                        <textarea
                            id="updateFacilityIconInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            name="updatefacilityicon"
                            placeholder="Enter icon (ri-sofa-line)"></textarea>
                        <small id="updateFacilityIconError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>
                    <!-- Size -->
                    <div class="relative">
                        <label class="block text-sm text-start font-medium text-gray-700 mb-1">Choose Size</label>
                        <select id="updateFacilitySize" name="updatefacilityiconsize" class="p-2 w-full border rounded">
                            <option value="" disabled>Select size of icon</option>
                            <option value="text-base">M</option>
                            <option value="text-lg">L</option>
                            <option value="text-xl">XL</option>
                            <option value="text-2xl">2XL</option>
                        </select>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3 sm:gap-1">
                        <!-- Addictional Charge -->
                        <div class="relative flex-1">
                            <label class="block text-sm text-start font-medium text-gray-700 mb-1">Addictional Charge</label>
                            <select id="updateAddictionalCharge" name="updateadditionalcharge" class="p-2 w-full border rounded">
                                <option value="" disabled>Select one</option>
                                <option value="1">True</option>
                                <option value="0" selected>False</option>
                            </select>
                        </div>
                        <!-- Popular -->
                        <div class="relative flex-1">
                            <label class="block text-sm text-start font-medium text-gray-700 mb-1">Popular</label>
                            <select id="updatePopular" name="updatepopular" class="p-2 w-full border rounded">
                                <option value="" disabled>Select one</option>
                                <option value="1">True</option>
                                <option value="0">False</option>
                            </select>
                        </div>
                    </div>
                    <!-- Facility Type -->
                    <div class="relative">
                        <select id="updateFacilityType" name="updatefacilitytype" class="p-2 w-full border rounded">
                            <option value="" disabled selected>Select type of facility</option>
                            <?php
                            $select = "SELECT * FROM facilitytypetb";
                            $query = $connect->query($select);
                            $count = $query->num_rows;

                            if ($count) {
                                for ($i = 0; $i < $count; $i++) {
                                    $row = $query->fetch_assoc();
                                    $facility_type_id = $row['FacilityTypeID'];
                                    $facility_type = $row['FacilityType'];

                                    echo "<option value= '$facility_type_id'>$facility_type</option>";
                                }
                            } else {
                                echo "<option value='' disabled>No data yet</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <!-- Submit Button -->
                    <div class="flex justify-end gap-4 select-none">
                        <div id="updateFacilityModalCancelBtn" class="px-4 py-2 bg-gray-200 text-black hover:bg-gray-300 rounded-sm">
                            Cancel
                        </div>
                        <button
                            type="submit"
                            name="editfacility"
                            class="bg-amber-500 text-white px-4 py-2 select-none hover:bg-amber-600 rounded-sm">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Facility Delete Modal -->
        <div id="facilityConfirmDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <form class="bg-white max-w-5xl p-6 rounded-md shadow-md text-center" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" id="facilityDeleteForm">
                <h2 class="text-xl font-semibold text-red-600 mb-4">Confirm Facility Deletion</h2>
                <p class="text-slate-600 mb-2">You are about to delete the following Facility: <span id="facilityDeleteName" class="font-semibold"></span></p>
                <p class="text-sm text-gray-500 mb-4">
                    Deleting this Facility will permanently remove it from the system, including all associated data.
                </p>
                <input type="hidden" name="facilityid" id="deleteFacilityID">
                <div class="flex justify-end gap-4 select-none">
                    <div id="facilityCancelDeleteBtn" class="px-4 py-2 bg-gray-200 text-black hover:bg-gray-300 rounded-sm">
                        Cancel
                    </div>
                    <button
                        type="submit"
                        name="deletefacility"
                        class="px-4 py-2 bg-red-600 text-white hover:bg-red-700 rounded-sm">
                        Delete
                    </button>
                </div>
            </form>
        </div>

        <!-- Facilities Bulk Delete Confirm Modal -->
        <div id="facilityBulkDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <div class="bg-white max-w-lg p-6 rounded-md shadow-md text-center">
                <h2 class="text-xl font-semibold text-red-600 mb-4">Confirm Bulk Deletion</h2>
                <p class="text-slate-600 mb-2">
                    You are about to delete <span id="bulkFacilityDeleteCount" class="font-semibold">0</span> Facilities.
                </p>
                <p class="text-sm text-gray-500 mb-4">
                    This action cannot be undone. All selected Facilities will be permanently removed from the system.
                </p>
                <div class="flex justify-end gap-4 select-none">
                    <div id="bulkFacilityDeleteCancelBtn" class="px-4 py-2 bg-gray-200 text-black hover:bg-gray-300 rounded-sm cursor-pointer">
                        Cancel
                    </div>
                    <button
                        type="button"
                        id="bulkFacilityDeleteConfirmBtn"
                        class="px-4 py-2 bg-red-600 text-white hover:bg-red-700 rounded-sm">
                        Delete
                    </button>
                </div>
            </div>
        </div>

        <!-- Add Facility Form -->
        <div id="addFacilityModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <div class="bg-white w-full md:w-1/3 p-6 rounded-md shadow-md ">
                <h2 class="text-xl text-gray-700 font-bold mb-4">Add New Facility</h2>
                <form class="flex flex-col space-y-4" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" id="facilityForm">
                    <!-- Facility Input -->
                    <div class="relative w-full">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Facility Information</label>
                        <input
                            id="facilityInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="text"
                            name="facility"
                            placeholder="Enter facility">
                        <small id="facilityError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>
                    <!-- Icon Input -->
                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Optional</label>
                        <textarea
                            id="facilityIconInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="text"
                            name="facilityicon"
                            placeholder="Enter icon (ri-sofa-line)"></textarea>
                        <small id="facilityIconError" class="absolute left-2 -bottom-1 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>
                    <!-- Size -->
                    <div class="relative">
                        <label class="block text-sm text-start font-medium text-gray-700 mb-1">Choose Size</label>
                        <select id="facilitySize" name="facilityiconsize" class="p-2 w-full border rounded">
                            <option value="" disabled>Select size of icon</option>
                            <option value="text-base">M</option>
                            <option value="text-lg">L</option>
                            <option value="text-xl" selected>XL (default)</option>
                            <option value="text-2xl">2XL</option>
                        </select>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 sm:gap-1">
                        <!-- Addictional Charge -->
                        <div class="relative flex-1">
                            <label class="block text-sm text-start font-medium text-gray-700 mb-1">Addictional Charge</label>
                            <select id="addictionalCharge" name="additionalcharge" class="p-2 w-full border rounded">
                                <option value="" disabled>Select one</option>
                                <option value="1">True</option>
                                <option value="0" selected>False (default)</option>
                            </select>
                        </div>
                        <!-- Popular -->
                        <div class="relative flex-1">
                            <label class="block text-sm text-start font-medium text-gray-700 mb-1">Popular</label>
                            <select id="popular" name="popular" class="p-2 w-full border rounded">
                                <option value="" disabled>Select one</option>
                                <option value="1">True</option>
                                <option value="0" selected>False (default)</option>
                            </select>
                        </div>
                    </div>
                    <!-- Facility Type -->
                    <div class="relative">
                        <select id="FacilityType" name="facilityType" class="p-2 w-full border rounded outline-none" required>
                            <option value="" disabled selected>Select type of facility</option>
                            <?php
                            $select = "SELECT * FROM facilitytypetb";
                            $query = $connect->query($select);
                            $count = $query->num_rows;

                            if ($count) {
                                for ($i = 0; $i < $count; $i++) {
                                    $row = $query->fetch_assoc();
                                    $facility_type_id = $row['FacilityTypeID'];
                                    $facility_type = $row['FacilityType'];

                                    echo "<option value= '$facility_type_id'>$facility_type</option>";
                                }
                            } else {
                                echo "<option value='' disabled>No data yet</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="flex justify-end gap-4 select-none">
                        <div id="addFacilityCancelBtn" class="px-4 py-2 text-amber-500 font-semibold hover:text-amber-600">
                            Cancel
                        </div>
                        <!-- Submit Button -->
                        <button
                            type="submit"
                            name="addfacility"
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