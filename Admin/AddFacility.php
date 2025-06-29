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
                <form method="GET" class="my-4 flex items-start sm:items-center justify-between flex-col sm:flex-row gap-2 sm:gap-0" id="facilityFilterForm">
                    <h1 class="text-lg text-gray-700 font-semibold text-nowrap">All Facilities <span class="text-gray-400 text-sm ml-2" id="facilityCountValue"><?php echo $facilityCount ?></span></h1>
                    <div class="flex items-center w-full">
                        <input type="text" name="facility_search" id="facilitySearch" class="p-2 ml-0 sm:ml-5 border border-gray-300 rounded-md w-full outline-none focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out" placeholder="Search for facility..." value="<?php echo isset($_GET['facility_search']) ? htmlspecialchars($_GET['facility_search']) : ''; ?>">
                        <div class="flex items-center">
                            <label for="sort" class="ml-4 mr-2 flex items-center cursor-pointer select-none">
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
                <div class="tableScrollBar overflow-y-auto max-h-[510px]">
                    <div id="facilityResults">
                        <!-- Facility results will be loaded here via Ajax -->
                        <?php include 'facility_results.php'; ?>
                    </div>
                </div>

                <!-- Pagination Controls -->
                <div class="flex justify-between items-center mt-3">
                    <div class="text-gray-500 text-sm" id="paginationInfo">
                        <?php
                        echo 'Showing ' . min($facilityOffset + 1, $facilityCount) . ' to ' .
                            min($facilityOffset + $rowsPerPage, $facilityCount) . ' of ' .
                            $facilityCount . ' facilities';
                        ?>
                    </div>
                    <div class="flex justify-center items-center mt-1 gap-1 <?= (!empty($facilities)) ? 'flex' : 'hidden' ?>" id="paginationControls">
                        <!-- Previous Btn -->
                        <?php if ($page > 1): ?>
                            <a href="#" onclick="loadPage(<?= $page - 1 ?>); return false;"
                                class="px-3 py-1 border rounded text-gray-600 <?= ($page - 1) == $page ? 'bg-gray-100 border-gray-300' : 'bg-white' ?> hover:bg-gray-100">
                                <i class="ri-arrow-left-s-line"></i>
                            </a>
                        <?php else: ?>
                            <span class="px-3 py-1 border rounded cursor-not-allowed bg-gray-100 border-gray-300 text-gray-600">
                                <i class="ri-arrow-left-s-line"></i>
                            </span>
                        <?php endif; ?>

                        <?php
                        $totalPages = ceil($facilityCount / $rowsPerPage);
                        for ($p = 1; $p <= $totalPages; $p++): ?>
                            <a href="#" onclick="loadPage(<?= $p ?>); return false;"
                                class="px-3 py-1 border rounded text-gray-600 select-none <?= $p == $page ? 'bg-gray-100 border-gray-300' : 'bg-white hover:bg-gray-100' ?>">
                                <?= $p ?>
                            </a>
                        <?php endfor; ?>

                        <!-- Next Btn -->
                        <?php if ($page < $totalPages): ?>
                            <a href="#" onclick="loadPage(<?= $page + 1 ?>); return false;"
                                class="px-3 py-1 border rounded text-gray-600 <?= ($page + 1) == $page ? 'bg-gray-100 border-gray-300' : 'bg-white' ?> hover:bg-gray-100">
                                <i class="ri-arrow-right-s-line"></i>
                            </a>
                        <?php else: ?>
                            <span class="px-3 py-1 border rounded cursor-not-allowed bg-gray-100 border-gray-300 text-gray-600">
                                <i class="ri-arrow-right-s-line"></i>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <script>
                    function updatePaginationControls(currentPage, rowsPerPage, totalCount) {
                        const totalPages = Math.ceil(totalCount / rowsPerPage);
                        const startItem = Math.min((currentPage - 1) * rowsPerPage + 1, totalCount);
                        const endItem = Math.min(currentPage * rowsPerPage, totalCount);

                        // Update pagination info
                        document.getElementById('paginationInfo').textContent =
                            `Showing ${startItem} to ${endItem} of ${totalCount} facilities`;

                        // Update pagination controls
                        let paginationHTML = '';

                        // Previous button
                        if (currentPage > 1) {
                            paginationHTML += `
                <a href="#" onclick="loadPage(${currentPage - 1}); return false;"
                    class="px-3 py-1 border rounded text-gray-600 bg-white hover:bg-gray-100">
                    <i class="ri-arrow-left-s-line"></i>
                </a>`;
                        } else {
                            paginationHTML += `
                <span class="px-3 py-1 border rounded text-gray-600 cursor-not-allowed bg-gray-100 border-gray-300">
                    <i class="ri-arrow-left-s-line"></i>
                </span>`;
                        }

                        // Page numbers
                        for (let p = 1; p <= totalPages; p++) {
                            paginationHTML += `
                <a href="#" onclick="loadPage(${p}); return false;"
                    class="px-3 py-1 border rounded text-gray-600 select-none ${p == currentPage ? 'bg-gray-100 border-gray-300' : 'bg-white hover:bg-gray-100'}">
                    ${p}
                </a>`;
                        }

                        // Next button
                        if (currentPage < totalPages) {
                            paginationHTML += `
                <a href="#" onclick="loadPage(${currentPage + 1}); return false;"
                    class="px-3 py-1 border rounded text-gray-600 bg-white hover:bg-gray-100">
                    <i class="ri-arrow-right-s-line"></i>
                </a>`;
                        } else {
                            paginationHTML += `
                <span class="px-3 py-1 border rounded text-gray-600 cursor-not-allowed bg-gray-100 border-gray-300">
                    <i class="ri-arrow-right-s-line"></i>
                </span>`;
                        }

                        document.getElementById('paginationControls').innerHTML = paginationHTML;
                    }

                    // Your existing initializeActionButtons function remains exactly the same
                    function initializeActionButtons() {
                        // Details buttons
                        document.querySelectorAll('.details-btn').forEach(btn => {
                            btn.addEventListener('click', function() {
                                const facilityId = this.getAttribute('data-facility-id');
                                darkOverlay2.classList.remove('opacity-0', 'invisible');
                                darkOverlay2.classList.add('opacity-100');

                                fetch(`../Admin/AddFacility.php?action=getFacilityDetails&id=${facilityId}`)
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            document.getElementById('updateFacilityID').value = facilityId;
                                            document.querySelector('[name="updatefacility"]').value = data.facility.Facility;
                                            document.querySelector('[name="updatefacilityicon"]').value = data.facility.FacilityIcon;
                                            document.querySelector('[name="updatefacilityiconsize"]').value = data.facility.IconSize;
                                            document.querySelector('[name="updateadditionalcharge"]').value = data.facility.AdditionalCharge;
                                            document.querySelector('[name="updatepopular"]').value = data.facility.Popular;
                                            document.querySelector('[name="updatefacilitytype"]').value = data.facility.FacilityTypeID;
                                            updateFacilityModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                                        } else {
                                            console.error('Failed to load facility details');
                                        }
                                    })
                                    .catch(error => console.error('Fetch error:', error));
                            });
                        });

                        // Delete buttons
                        document.querySelectorAll('.delete-btn').forEach(btn => {
                            btn.addEventListener('click', function() {
                                const facilityId = this.getAttribute('data-facility-id');
                                darkOverlay2.classList.remove('opacity-0', 'invisible');
                                darkOverlay2.classList.add('opacity-100');

                                fetch(`../Admin/AddFacility.php?action=getFacilityDetails&id=${facilityId}`)
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            document.getElementById('deleteFacilityID').value = facilityId;
                                            document.getElementById('facilityDeleteName').textContent = data.facility.Facility;
                                            facilityConfirmDeleteModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                                        } else {
                                            console.error('Failed to load facility details');
                                        }
                                    })
                                    .catch(error => console.error('Fetch error:', error));
                            });
                        });
                    }

                    function loadPage(page) {
                        const urlParams = new URLSearchParams(window.location.search);
                        const searchQuery = urlParams.get('facility_search') || '';
                        const sortType = urlParams.get('sort') || 'random';

                        // Update URL parameters
                        urlParams.set('page', page);
                        if (searchQuery) urlParams.set('facility_search', searchQuery);
                        if (sortType !== 'random') urlParams.set('sort', sortType);

                        const xhr = new XMLHttpRequest();
                        xhr.open('GET', `facility_results.php?${urlParams.toString()}`, true);

                        xhr.onload = function() {
                            if (this.status === 200) {
                                // Update the table content
                                document.getElementById('facilityResults').innerHTML = this.responseText;

                                // Update browser URL without reloading
                                window.history.pushState({}, '', `?${urlParams.toString()}`);
                                window.scrollTo(0, 0);

                                // Reinitialize action buttons
                                initializeActionButtons();

                                // Update pagination info and controls
                                updatePaginationControls(page, <?= $rowsPerPage ?>, <?= $facilityCount ?>);
                            }
                        };

                        xhr.send();
                    }

                    // Handle browser back/forward buttons
                    window.addEventListener('popstate', function() {
                        const urlParams = new URLSearchParams(window.location.search);
                        const page = urlParams.get('page') || 1;
                        loadPage(page);
                    });
                </script>
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