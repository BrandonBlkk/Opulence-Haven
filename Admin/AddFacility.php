<?php
session_start();
include('../config/dbConnection.php');
include('../includes/AutoIDFunc.php');
include('../includes/AdminPagination.php');

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

// Initialize search and filter variables for facility
$searchFacilityQuery = isset($_GET['facility_search']) ? mysqli_real_escape_string($connect, $_GET['facility_search']) : '';
$filterFacilityTypeID = isset($_GET['sort']) ? $_GET['sort'] : 'random';

// Construct the facility query based on search
if ($filterFacilityTypeID !== 'random' && !empty($searchFacilityQuery)) {
    $facilitySelect = "SELECT * FROM facilitytb WHERE FacilityTypeID = '$filterFacilityTypeID' AND (Facility LIKE '%$searchFacilityQuery%') LIMIT $rowsPerPage OFFSET $facilityOffset";
} elseif ($filterFacilityTypeID !== 'random') {
    $facilitySelect = "SELECT * FROM facilitytb WHERE FacilityTypeID = '$filterFacilityTypeID' LIMIT $rowsPerPage OFFSET $facilityOffset";
} elseif (!empty($searchFacilityQuery)) {
    $facilitySelect = "SELECT * FROM facilitytb WHERE Facility LIKE '%$searchFacilityQuery%' LIMIT $rowsPerPage OFFSET $facilityOffset";
} else {
    $facilitySelect = "SELECT * FROM facilitytb LIMIT $rowsPerPage OFFSET $facilityOffset";
}

$facilitySelectQuery = $connect->query($facilitySelect);
$facilities = [];

if (mysqli_num_rows($facilitySelectQuery) > 0) {
    while ($row = $facilitySelectQuery->fetch_assoc()) {
        $facilities[] = $row;
    }
}

// Construct the facility count query based on search
if ($filterFacilityTypeID !== 'random' && !empty($searchFacilityQuery)) {
    $facilityQuery = "SELECT COUNT(*) as count FROM facilitytb WHERE FacilityTypeID = '$filterFacilityTypeID' AND (Facility LIKE '%$searchFacilityQuery%')";
} elseif ($filterFacilityTypeID !== 'random') {
    $facilityQuery = "SELECT COUNT(*) as count FROM facilitytb WHERE FacilityTypeID = '$filterFacilityTypeID'";
} elseif (!empty($searchFacilityQuery)) {
    $facilityQuery = "SELECT COUNT(*) as count FROM facilitytb WHERE Facility LIKE '%$searchFacilityQuery%'";
} else {
    $facilityQuery = "SELECT COUNT(*) as count FROM facilitytb";
}

// Execute the count query
$facilityResult = $connect->query($facilityQuery);
$facilityCount = $facilityResult->fetch_assoc()['count'];

// Fetch facility count
$facilityCountQuery = "SELECT COUNT(*) as count FROM facilitytb";
$facilityCountResult = $connect->query($facilityCountQuery);
$allFacilityCount = $facilityCountResult->fetch_assoc()['count'];
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
    <div class="flex flex-col md:flex-row md:space-x-3 p-3 ml-0 md:ml-[250px] min-w-[380px]">
        <!-- Left Side Content -->
        <div class="w-full bg-white p-2">
            <div class="flex justify-between items-end">
                <div>
                    <h2 class="text-xl text-gray-700 font-bold mb-4">Add Facility Overview</h2>
                    <p>Add information about room facilities to categorize room types, track usage, and manage facility details for efficient organization.</p>
                </div>
                <button id="addFacilityBtn" class="bg-amber-500 text-white font-semibold px-3 py-1 rounded select-none hover:bg-amber-600 transition-colors">
                    <i class="ri-add-line text-xl"></i>
                </button>
            </div>

            <!-- Facility Type Table -->
            <div class="overflow-x-auto">
                <!-- Facility Type Search and Filter -->
                <form method="GET" class="my-4 flex items-start sm:items-center justify-between flex-col sm:flex-row gap-2 sm:gap-0">
                    <h1 class="text-lg text-gray-700 font-semibold text-nowrap">All Facilities <span class="text-gray-400 text-sm ml-2"><?php echo $facilityCount ?></span></h1>
                    <div class="flex items-center w-full">
                        <input type="text" name="facility_search" class="p-2 ml-0 sm:ml-5 border border-gray-300 rounded-md w-full outline-none focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out" placeholder="Search for facility..." value="<?php echo isset($_GET['facility_search']) ? htmlspecialchars($_GET['facility_search']) : ''; ?>">
                        <div class="flex items-center">
                            <label for="sort" class="ml-4 mr-2 flex items-center cursor-pointer select-none">
                                <i class="ri-filter-2-line text-xl"></i>
                                <p>Filters</p>
                            </label>
                            <!-- Search and filter form -->
                            <form method="GET" class="flex flex-col md:flex-row items-center gap-4 mb-4">
                                <select name="sort" id="sort" class="border p-2 rounded text-sm" onchange="this.form.submit()">
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
                <div class="tableScrollBar overflow-y-auto max-h-[510px]">
                    <table class="min-w-full bg-white rounded-lg">
                        <thead>
                            <tr class="bg-gray-100 text-gray-600 text-sm">
                                <th class="p-3 text-start">ID</th>
                                <th class="p-3 text-start">Type</th>
                                <th class="p-3 text-start">Icon</th>
                                <th class="p-3 text-start hidden md:table-cell">Additional Charge</th>
                                <th class="p-3 text-start hidden md:table-cell">Popular</th>
                                <th class="p-3 text-start hidden lg:table-cell">Facility Type</th>
                                <th class="p-3 text-start">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm">
                            <?php if (!empty($facilities)): ?>
                                <?php foreach ($facilities as $facility): ?>
                                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                                        <td class="p-3 text-start whitespace-nowrap">
                                            <div class="flex items-center gap-2 font-medium text-gray-500">
                                                <input type="checkbox" class="form-checkbox h-3 w-3 border-2 text-amber-500">
                                                <span><?= htmlspecialchars($facility['FacilityID']) ?></span>
                                            </div>
                                        </td>
                                        <td class="p-3 text-start">
                                            <?= htmlspecialchars($facility['Facility']) ?>
                                        </td>
                                        <td class="p-3 text-start">
                                            <?= !empty($facility['FacilityIcon']) && !empty($facility['IconSize'])
                                                ? '<i class="' . htmlspecialchars($facility['FacilityIcon'], ENT_QUOTES, 'UTF-8') . ' ' . htmlspecialchars($facility['IconSize'], ENT_QUOTES, 'UTF-8') . '"></i>'
                                                : 'None' ?>
                                        </td>
                                        <td class="p-3 text-start hidden md:table-cell">
                                            <?= htmlspecialchars($facility['AdditionalCharge'] == 1 ? 'True' : 'False') ?>
                                        </td>
                                        <td class="p-3 text-start hidden md:table-cell">
                                            <?= htmlspecialchars($facility['Popular'] == 1 ? 'True' : 'False') ?>
                                        </td>
                                        <td class="p-3 text-start hidden lg:table-cell">
                                            <?php
                                            // Fetch the specific facility type for the facility
                                            $facilityTypeID = $facility['FacilityTypeID'];
                                            $facilityTypeQuery = "SELECT FacilityType FROM facilitytypetb WHERE FacilityTypeID = '$facilityTypeID'";
                                            $facilityTypeResult = mysqli_query($connect, $facilityTypeQuery);

                                            if ($facilityTypeResult && $facilityTypeResult->num_rows > 0) {
                                                $facilityTypeRow = $facilityTypeResult->fetch_assoc();
                                                echo htmlspecialchars($facilityTypeRow['FacilityType']);
                                            }
                                            ?>
                                        </td>
                                        <td class="p-3 text-start space-x-1 select-none">
                                            <i class="details-btn ri-eye-line text-lg cursor-pointer"
                                                data-facility-id="<?= htmlspecialchars($facility['FacilityID']) ?>"></i>
                                            <button class="text-red-500">
                                                <i class="delete-btn ri-delete-bin-7-line text-xl"
                                                    data-facility-id="<?= htmlspecialchars($facility['FacilityID']) ?>"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="p-3 text-center text-gray-500 py-52">
                                        No facilities available.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Controls -->
                <div class="flex justify-center items-center mt-1 <?= (!empty($facilities) ? 'flex' : 'hidden') ?>">
                    <?php if ($facilityCurrentPage > 1) {
                    ?>
                        <a href="?facilitypage=<?= $facilityCurrentPage - 1 ?>&facility_search=<?= htmlspecialchars($searchFacilityQuery) ?>&sort=<?= $filterFacilityTypeID ?>"
                            class="px-3 py-1 mx-1 border rounded <?= $facilitypage == $facilityCurrentPage ? 'bg-gray-200' : 'bg-white' ?>">
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
                    <?php for ($facilitypage = 1; $facilitypage <= $totalFacilityPages; $facilitypage++): ?>
                        <a href="?facilitypage=<?= $facilitypage ?>&facility_search=<?= htmlspecialchars($searchFacilityQuery) ?>&sort=<?= $filterFacilityTypeID ?>"
                            class="px-3 py-1 mx-1 border rounded select-none <?= $facilitypage == $facilityCurrentPage ? 'bg-gray-200' : 'bg-white' ?>">
                            <?= $facilitypage ?>
                        </a>
                    <?php endfor; ?>
                    <!-- Next Btn -->
                    <?php if ($facilityCurrentPage < $totalFacilityPages) {
                    ?>
                        <a href="?facilitypage=<?= $facilityCurrentPage + 1 ?>&facility_search=<?= htmlspecialchars($searchFacilityQuery) ?>&sort=<?= $filterFacilityTypeID ?>"
                            class="px-3 py-1 mx-1 border rounded <?= $facilitypage == $facilityCurrentPage ? 'bg-gray-200' : 'bg-white' ?>">
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
    include('../includes/Alert.php');
    include('../includes/Loader.php');
    ?>

    <script src="//unpkg.com/alpinejs" defer></script>
    <script type="module" src="../JS/admin.js"></script>
</body>

</html>