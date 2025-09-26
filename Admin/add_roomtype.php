<?php
session_start();
require_once('../config/db_connection.php');
include_once('../includes/auto_id_func.php');
require_once('../includes/auth_check.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

$alertMessage = '';
$roomTypeID = AutoID('roomtypetb', 'RoomTypeID', 'RT-', 6);
$response = ['success' => false, 'message' => '', 'generatedId' => $roomTypeID];

// Add Room Type
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['addroomtype'])) {
    $roomtype = mysqli_real_escape_string($connect, $_POST['roomtype']);
    $description = mysqli_real_escape_string($connect, $_POST['description']);
    $roomcapacity = mysqli_real_escape_string($connect, $_POST['roomcapacity']);
    $roomprice = mysqli_real_escape_string($connect, $_POST['roomprice']);
    $roomquantity = mysqli_real_escape_string($connect, $_POST['roomquantity']);

    // Product image upload 
    $roomImage = $_FILES["roomcoverimage"]["name"];
    $copyFile = "AdminImages/";
    $fileName = $copyFile . uniqid() . "_" . $roomImage;
    $copy = copy($_FILES["roomcoverimage"]["tmp_name"], $fileName);

    if (!$copy) {
        echo "<p>Cannot upload Product Image.</p>";
        exit();
    }

    // Check if the product type already exists using prepared statement
    $checkQuery = "SELECT RoomType FROM roomtypetb WHERE RoomType = '$roomtype'";
    $count = $connect->query($checkQuery)->num_rows;

    if ($count > 0) {
        $response['message'] = 'Room type you added is already existed.';
    } else {
        $RoomTypeQuery = "INSERT INTO roomtypetb (RoomTypeID, RoomCoverImage, RoomType, RoomDescription, RoomCapacity, RoomPrice, RoomQuantity)
        VALUES ('$roomTypeID', '$fileName', '$roomtype', '$description', '$roomcapacity', '$roomprice', '$roomquantity')";

        if ($connect->query($RoomTypeQuery)) {
            // Insert selected facilities into roomtypefacilitytb
            if (isset($_POST['facilities']) && is_array($_POST['facilities'])) {
                foreach ($_POST['facilities'] as $facilityID) {
                    $facilityID = mysqli_real_escape_string($connect, $facilityID);
                    $insertFacility = "INSERT INTO roomtypefacilitytb (RoomTypeID, FacilityID) VALUES ('$roomTypeID', '$facilityID')";
                    $connect->query($insertFacility);
                }
            }

            // Handle additional images
            if (!empty($_FILES['additional_images']['name'][0])) {
                $additionalImages = $_FILES['additional_images'];

                for ($i = 0; $i < count($additionalImages['name']); $i++) {
                    if ($additionalImages['error'][$i] === UPLOAD_ERR_OK) {
                        $tempName = $additionalImages['tmp_name'][$i];
                        $imageName = $additionalImages['name'][$i];
                        $imagePath = $copyFile . uniqid() . "_" . $imageName;

                        if (move_uploaded_file($tempName, $imagePath)) {
                            $insertImage = "INSERT INTO roomtypeimagetb (ImagePath, RoomTypeID) VALUES ('$imagePath', '$roomTypeID')";
                            $connect->query($insertImage);
                        }
                    }
                }
            }

            $response['success'] = true;
            $response['message'] = "A new room type has been successfully added.";
        } else {
            $response['message'] = "Failed to add room type. Please try again.";
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Get Room Type Details
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = mysqli_real_escape_string($connect, $_GET['id']);
    $action = $_GET['action'];

    // Build query based on action
    $query = match ($action) {
        'getRoomTypeDetails' => "SELECT rt.*, rti.ImagePath 
                         FROM roomtypetb rt 
                         LEFT JOIN roomtypeimagetb rti ON rt.RoomTypeID = rti.RoomTypeID 
                         WHERE rt.RoomTypeID = '$id'",
        default => null
    };

    if ($query) {
        $result = $connect->query($query);
        $roomtype = $result->fetch_assoc();

        if ($roomtype) {
            // Get associated facilities
            $facilityQuery = "SELECT FacilityID FROM roomtypefacilitytb WHERE RoomTypeID = '$id'";
            $facilityResult = $connect->query($facilityQuery);
            $associatedFacilities = array();

            while ($row = $facilityResult->fetch_assoc()) {
                $associatedFacilities[] = $row['FacilityID'];
            }

            // Get additional images
            $imageQuery = "SELECT ImagePath FROM roomtypeimagetb WHERE RoomTypeID = '$id'";
            $imageResult = $connect->query($imageQuery);
            $additionalImages = array();

            while ($row = $imageResult->fetch_assoc()) {
                $additionalImages[] = $row['ImagePath'];
            }

            $response['success'] = true;
            $response['roomtype'] = $roomtype;
            $response['facilities'] = $associatedFacilities;
            $response['additional_images'] = $additionalImages;
        } else {
            $response['success'] = false;
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Update Product Type
if (isset($_POST['editroomtype'])) {
    $roomTypeId = mysqli_real_escape_string($connect, $_POST['roomtypeid']);
    $RoomType = mysqli_real_escape_string($connect, $_POST['updateroomtype']);
    $updatedRoomTypeDescription = mysqli_real_escape_string($connect, $_POST['updateroomtypedescription']);
    $updatedRoomCapacity = mysqli_real_escape_string($connect, $_POST['updateroomcapacity']);
    $updateRoomTypePrice = mysqli_real_escape_string($connect, $_POST['updateroomprice']);
    $updateRoomTypeQuantity = mysqli_real_escape_string($connect, $_POST['updateroomquantity']);

    // Initialize response array
    $response = ['success' => false, 'message' => ''];

    try {
        // Start transaction
        $connect->begin_transaction();

        // Handle cover image upload if provided
        $coverImagePath = null;
        if (!empty($_FILES['update_roomcoverimage']['name'])) {
            $coverImage = $_FILES['update_roomcoverimage'];
            $uploadDir = 'AdminImages/';
            $fileExtension = pathinfo($coverImage['name'], PATHINFO_EXTENSION);
            $fileName = uniqid() . '_' . time() . '.' . $fileExtension;
            $targetPath = $uploadDir . $fileName;

            // Validate and move uploaded file
            if (move_uploaded_file($coverImage['tmp_name'], $targetPath)) {
                // First delete old cover image if exists
                $getOldImage = "SELECT RoomCoverImage FROM roomtypetb WHERE RoomTypeID = '$roomTypeId'";
                $oldImageResult = $connect->query($getOldImage);
                if ($oldImageResult->num_rows > 0) {
                    $oldImage = $oldImageResult->fetch_assoc();
                    if (file_exists($oldImage['RoomCoverImage'])) {
                        unlink($oldImage['RoomCoverImage']);
                    }
                }
                $coverImagePath = $targetPath;
            }
        }

        // Update query for room type
        $updateQuery = "UPDATE roomtypetb SET 
                        RoomType = ?, 
                        RoomDescription = ?, 
                        RoomCapacity = ?, 
                        RoomPrice = ?, 
                        RoomQuantity = ?" .
            ($coverImagePath ? ", RoomCoverImage = '$coverImagePath'" : "") .
            " WHERE RoomTypeID = '$roomTypeId'";

        $stmt = $connect->prepare($updateQuery);
        $stmt->bind_param("ssidi", $RoomType, $updatedRoomTypeDescription, $updatedRoomCapacity, $updateRoomTypePrice, $updateRoomTypeQuantity);

        if (!$stmt->execute()) {
            throw new Exception("Failed to update room type.");
        }

        // Handle additional images
        if (!empty($_FILES['additional_images']['name'][0])) {
            $uploadDir = 'AdminImages/';
            $allowedExtensions = ['jpg', 'jpeg', 'png'];

            // First delete existing additional images for this room type
            $deleteExistingQuery = "DELETE FROM roomtypeimagetb WHERE RoomTypeID = '$roomTypeId'";
            if (!$connect->query($deleteExistingQuery)) {
                throw new Exception("Failed to clear existing room images.");
            }

            // Process new additional images
            foreach ($_FILES['additional_images']['tmp_name'] as $key => $tmpName) {
                $fileName = $_FILES['additional_images']['name'][$key];
                $fileSize = $_FILES['additional_images']['size'][$key];
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                // Validate file
                if (!in_array($fileExtension, $allowedExtensions)) {
                    continue; // Skip invalid files
                }
                if ($fileSize > 5000000) { // 5MB limit
                    continue;
                }

                $newFileName = uniqid() . '_' . time() . '.' . $fileExtension;
                $targetPath = $uploadDir . $newFileName;

                if (move_uploaded_file($tmpName, $targetPath)) {
                    // Insert into roomtypeimagetb
                    $insertImageQuery = "INSERT INTO roomtypeimagetb (ImagePath, RoomTypeID) VALUES ('$targetPath', '$roomTypeId')";
                    if (!$connect->query($insertImageQuery)) {
                        throw new Exception("Failed to save additional images.");
                    }
                }
            }
        }

        // Handle facilities update
        if (isset($_POST['update_facilities'])) {
            // First delete existing facilities for this room type
            $deleteFacilitiesQuery = "DELETE FROM roomtypefacilitytb WHERE RoomTypeID = '$roomTypeId'";
            if (!$connect->query($deleteFacilitiesQuery)) {
                throw new Exception("Failed to clear existing facilities.");
            }

            // Insert new facilities
            foreach ($_POST['update_facilities'] as $facilityId) {
                $facilityId = mysqli_real_escape_string($connect, $facilityId);
                $insertFacilityQuery = "INSERT INTO roomtypefacilitytb (RoomTypeID, FacilityID) VALUES ('$roomTypeId', '$facilityId')";
                if (!$connect->query($insertFacilityQuery)) {
                    throw new Exception("Failed to save facilities.");
                }
            }
        } else {
            // If no facilities selected, remove all facilities for this room
            $deleteFacilitiesQuery = "DELETE FROM roomtypefacilitytb WHERE RoomTypeID = '$roomTypeId'";
            $connect->query($deleteFacilitiesQuery);
        }

        // Update LastUpdate timestamp
        $updateTimestampQuery = "UPDATE roomtypetb SET LastUpdate = NOW() WHERE RoomTypeID = '$roomTypeId'";
        $connect->query($updateTimestampQuery);

        // Commit transaction
        $connect->commit();

        $response['success'] = true;
        $response['message'] = 'The room type has been successfully updated.';
    } catch (Exception $e) {
        // Rollback transaction on error
        $connect->rollback();
        $response['message'] = $e->getMessage();
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Delete Room Type
if (isset($_POST['deleteroomtype'])) {
    $roomTypeId = mysqli_real_escape_string($connect, $_POST['roomtypeid']);

    // Build query based on action
    $deleteQuery = "DELETE FROM roomtypetb WHERE RoomTypeID = '$roomTypeId'";

    if ($connect->query($deleteQuery)) {
        $response['success'] = true;
        $response['generatedId'] = $roomTypeId;
    } else {
        $response['message'] = "Failed to delete room type. Please try again.";
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Bulk Delete Room Types
if (isset($_POST['bulkdeleteroomtypes'])) {
    $ids = $_POST['roomtypeids'] ?? [];
    $response = ['success' => false];

    if (!empty($ids)) {
        $ids = array_map(function ($id) use ($connect) {
            return "'" . mysqli_real_escape_string($connect, $id) . "'";
        }, $ids);

        $idsList = implode(',', $ids);
        $deleteQuery = "DELETE FROM roomtypetb WHERE RoomTypeID IN ($idsList)";

        if ($connect->query($deleteQuery)) {
            $response['success'] = true;
            $response['deletedIds'] = $ids;
        } else {
            $response['message'] = 'Failed to delete selected room types. Please try again.';
        }
    } else {
        $response['message'] = 'No room types selected for deletion.';
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
                    <h2 class="text-xl text-gray-700 font-bold mb-4">Add Room Type Overview</h2>
                    <p>Add information about room types to categorize items, track stock levels, and manage room details for efficient organization.</p>
                </div>
                <div class="flex gap-2">
                    <button id="addRoomTypeBtn" class="bg-amber-500 text-white font-semibold px-3 py-1 rounded select-none hover:bg-amber-600 transition-colors">
                        <i class="ri-add-line text-xl"></i>
                    </button>
                    <button id="bulkDeleteBtn"
                        class="hidden px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600">
                        Delete Selected
                    </button>
                </div>
            </div>

            <!-- Prooduct Type Table -->
            <div class="overflow-x-auto">
                <!-- Product Type Search and Filter -->
                <form method="GET" class="my-4 flex items-start sm:items-center justify-between flex-col sm:flex-row gap-2 sm:gap-0">
                    <h1 class="text-lg text-gray-700 font-semibold text-nowrap">All Room Types <span class="text-gray-400 text-sm ml-2"><?php echo $roomTypeCount ?></span></h1>
                    <div class="flex items-center w-full">
                        <input type="text" name="roomtype_search" class="p-2 ml-0 sm:ml-5 border border-gray-300 rounded-md w-full outline-none focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out" placeholder="Search for room type..." value="<?php echo isset($_GET['roomtype_search']) ? htmlspecialchars($_GET['roomtype_search']) : ''; ?>">
                    </div>
                </form>

                <!-- Room Type Table -->
                <div class="tableScrollBar overflow-y-auto max-h-[495px]">
                    <div id="roomTypeResults">
                        <?php include '../includes/admin_table_components/roomtype_results.php'; ?>
                    </div>
                </div>

                <!-- Pagination Controls -->
                <div id="paginationContainer" class="flex justify-between items-center mt-3">
                    <?php include '../includes/admin_table_components/roomtype_pagination.php'; ?>
                </div>
            </div>
        </div>

        <!-- Update Room Type Modal -->
        <div id="updateRoomTypeModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <div class="bg-white w-full max-w-4xl p-6 rounded-md shadow-md max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl text-gray-700 font-bold">Update Room Type</h2>
                    <button id="updateRoomTypeModalCancelBtn" class="text-gray-400 hover:text-gray-500">
                        <i class="ri-close-line text-xl"></i>
                    </button>
                </div>
                <form class="flex flex-col space-y-4" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" enctype="multipart/form-data" id="updateRoomTypeForm">
                    <input type="hidden" name="roomtypeid" id="updateRoomTypeID">

                    <!-- Cover Image Upload -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Cover Image (High-Quality JPG/PNG/JPEG)</label>
                        <div id="update-cover-preview-container" class="mb-4">
                            <div class="relative group">
                                <img id="updateRoomTypeImage" class="w-full h-40 object-cover rounded-lg border border-gray-200 select-none" src="">
                                <button type="button" onclick="removeUpdateCoverImage()"
                                    class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm hover:bg-red-600 shadow-md transition-opacity opacity-0 group-hover:opacity-100">
                                    ×
                                </button>
                            </div>
                        </div>
                        <div class="relative">
                            <div id="update-upload-area" class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center bg-gray-50 hover:bg-gray-100 transition-colors cursor-pointer">
                                <div class="flex flex-col items-center justify-center space-y-2 py-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <p class="text-sm text-gray-600">Click to upload or drag and drop</p>
                                    <p class="text-xs text-gray-500">PNG, JPG, JPEG (Max. 5MB)</p>
                                </div>
                                <input type="file" name="update_roomcoverimage" id="update-cover-input" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept="image/jpeg,image/png,image/jpg">
                            </div>
                        </div>
                    </div>

                    <!-- Additional Images Upload -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Additional Room Images (Max 5 images)</label>
                        <div id="update-additional-preview-container" class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-4"></div>
                        <div class="relative">
                            <div id="update-additional-upload-area" class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center bg-gray-50 hover:bg-gray-100 transition-colors cursor-pointer">
                                <div class="flex flex-col items-center justify-center space-y-2 py-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <p class="text-sm text-gray-600">Click to upload or drag and drop</p>
                                    <p class="text-xs text-gray-500">PNG, JPG, JPEG (Max. 5MB each, Max 5 files)</p>
                                </div>
                                <input type="file" name="additional_images[]" id="update-additional-input" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept="image/jpeg,image/png,image/jpg" multiple>
                            </div>
                        </div>
                    </div>

                    <script>
                        // Handle removal of existing additional images
                        document.addEventListener('click', function(e) {
                            if (e.target.closest('[data-image-index]')) {
                                e.preventDefault();
                                const button = e.target.closest('[data-image-index]');
                                const imageDiv = button.parentElement;
                                imageDiv.remove();
                            }
                        });
                    </script>

                    <!-- Room Type Input -->
                    <div class="relative w-full">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Room Type Information</label>
                        <input
                            id="updateRoomTypeInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="text"
                            name="updateroomtype"
                            placeholder="Enter room type">
                        <small id="updateRoomTypeError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>
                    <!-- Description Input -->
                    <div class="relative">
                        <textarea
                            id="updateRoomTypeDescriptionInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="text"
                            name="updateroomtypedescription"
                            rows="3"
                            placeholder="Enter room type description"></textarea>
                        <small id="updateRoomTypeDescriptionError" class="absolute left-2 -bottom-1 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Capacity Input -->
                        <div class="relative w-full">
                            <input
                                id="updateRoomCapacityInput"
                                class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                type="number"
                                name="updateroomcapacity"
                                placeholder="Enter room capacity">
                            <small id="updateRoomCapacityError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                        </div>
                        <!-- Room Price -->
                        <div class="relative">
                            <input
                                id="updateRoomPriceInput"
                                class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                type="number"
                                name="updateroomprice"
                                placeholder="Enter room price">
                            <small id="updateRoomPriceError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                        </div>
                        <!-- Quantity Input -->
                        <div class="relative w-full">
                            <input
                                id="updateRoomQuantityInput"
                                class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                type="number"
                                name="updateroomquantity"
                                placeholder="Enter room quantity">
                            <small id="updateRoomQuantityError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                        </div>
                    </div>

                    <!-- Facilities Selection -->
                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Facilities</label>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2 max-h-40 overflow-y-auto p-2 border rounded" id="updateFacilitiesContainer">
                            <?php
                            $facilityQuery = "SELECT * FROM facilitytb";
                            $facilityResult = $connect->query($facilityQuery);

                            if ($facilityResult->num_rows > 0) {
                                while ($facility = $facilityResult->fetch_assoc()) {
                                    $facilityID = $facility['FacilityID'];
                                    $facilityName = $facility['Facility'];
                                    echo '
        <div class="flex items-center">
            <input type="checkbox" id="update_facility_' . $facilityID . '" name="update_facilities[]" value="' . $facilityID . '" class="h-4 w-4 text-amber-500 focus:ring-amber-500 border-gray-300 rounded">
            <label for="update_facility_' . $facilityID . '" class="ml-2 text-sm text-gray-700">' . $facilityName . '</label>
        </div>';
                                }
                            } else {
                                echo '<p class="text-sm text-gray-500 col-span-3">No facilities available</p>';
                            }
                            ?>
                        </div>
                        <small id="updateFacilitiesError" class="absolute left-2 -bottom-1 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>

                    <div class="flex justify-end gap-4 select-none">
                        <div id="updateRoomTypeModalCancelBtn2" class="px-4 py-2 text-amber-500 font-semibold hover:text-amber-600">
                            Cancel
                        </div>
                        <!-- Submit Button -->
                        <button
                            type="submit"
                            name="updateroomtype"
                            class="bg-amber-500 text-white font-semibold px-4 py-2 rounded-sm select-none hover:bg-amber-600 transition-colors">
                            Update Room Type
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Room Type Delete Modal -->
        <div id="roomTypeConfirmDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <form class="bg-white max-w-5xl p-6 rounded-md shadow-md text-center" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" id="roomTypeDeleteForm">
                <h2 class="text-xl font-semibold text-red-600 mb-4">Confirm Room Type Deletion</h2>
                <p class="text-slate-600 mb-2">You are about to delete the following Room Type: <span id="roomTypeDeleteName" class="font-semibold"></span></p>
                <p class="text-sm text-gray-500 mb-4">
                    Deleting this Room Type will permanently remove it from the system, including all associated data.
                </p>
                <input type="hidden" name="roomtypeid" id="deleteRoomTypeID">
                <div class="flex justify-end gap-4 select-none">
                    <div id="roomTypeCancelDeleteBtn" class="px-4 py-2 bg-gray-200 text-black hover:bg-gray-300 rounded-sm">
                        Cancel
                    </div>
                    <button
                        type="submit"
                        name="deleteroomtype"
                        class="px-4 py-2 bg-red-600 text-white hover:bg-red-700 rounded-sm">
                        Delete
                    </button>
                </div>
            </form>
        </div>

        <!-- Room Type Bulk Delete Modal -->
        <div id="roomTypeBulkDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <div class="bg-white max-w-lg p-6 rounded-md shadow-md text-center">
                <h2 class="text-xl font-semibold text-red-600 mb-4">Confirm Bulk Deletion</h2>
                <p class="text-slate-600 mb-2">
                    You are about to delete <span id="bulkDeleteCount" class="font-semibold">0</span> Room Types.
                </p>
                <p class="text-sm text-gray-500 mb-4">
                    This action cannot be undone. All selected Room Types will be permanently removed from the system.
                </p>
                <div class="flex justify-end gap-4 select-none">
                    <div id="bulkDeleteCancelBtn" class="px-4 py-2 bg-gray-200 text-black hover:bg-gray-300 rounded-sm cursor-pointer">
                        Cancel
                    </div>
                    <button type="button" id="bulkDeleteConfirmBtn" class="px-4 py-2 bg-red-600 text-white hover:bg-red-700 rounded-sm">
                        Delete
                    </button>
                </div>
            </div>
        </div>

        <!-- Add Room Type Form -->
        <div id="addRoomTypeModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <div class="bg-white w-full max-w-4xl p-6 rounded-md shadow-md max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl text-gray-700 font-bold">Add New Room Type</h2>
                </div>
                <form class="flex flex-col space-y-4" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" enctype="multipart/form-data" id="roomTypeForm">

                    <!-- Cover Image Upload -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Cover Image (High-Quality JPG/PNG/JPEG)</label>
                        <div id="cover-preview-container" class="hidden mb-4">
                            <div class="relative group">
                                <img id="cover-preview" class="w-full h-40 object-cover rounded-lg border border-gray-200">
                                <button type="button" onclick="removeCoverImage()"
                                    class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm hover:bg-red-600 shadow-md transition-opacity opacity-0 group-hover:opacity-100">
                                    ×
                                </button>
                            </div>
                        </div>
                        <div class="relative">
                            <div id="upload-area" class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center bg-gray-50 hover:bg-gray-100 transition-colors cursor-pointer">
                                <div class="flex flex-col items-center justify-center space-y-2 py-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <p class="text-sm text-gray-600">Click to upload or drag and drop</p>
                                    <p class="text-xs text-gray-500">PNG, JPG, JPEG (Max. 5MB)</p>
                                </div>
                                <input type="file" name="roomcoverimage" id="cover-input" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept="image/jpeg,image/png,image/jpg">
                            </div>
                        </div>
                    </div>

                    <!-- Additional Images Upload -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Additional Room Images (Max 5 images)</label>
                        <div id="add-additional-preview-container" class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-4"></div>
                        <div class="relative">
                            <div id="add-additional-upload-area" class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center bg-gray-50 hover:bg-gray-100 transition-colors cursor-pointer">
                                <div class="flex flex-col items-center justify-center space-y-2 py-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <p class="text-sm text-gray-600">Click to upload or drag and drop</p>
                                    <p class="text-xs text-gray-500">PNG, JPG, JPEG (Max. 5MB each, Max 5 files)</p>
                                </div>
                                <input type="file" name="additional_images[]" id="add-additional-input" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept="image/jpeg,image/png,image/jpg" multiple>
                            </div>
                        </div>
                    </div>

                    <!-- Room Type Input -->
                    <div class="relative w-full">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Room Type Information</label>
                        <input
                            id="roomTypeInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="text"
                            name="roomtype"
                            placeholder="Enter room type">
                        <small id="roomTypeError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>
                    <!-- Description Input -->
                    <div class="relative">
                        <textarea
                            id="roomTypeDescriptionInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="text"
                            name="description"
                            rows="3"
                            placeholder="Enter room type description"></textarea>
                        <small id="roomTypeDescriptionError" class="absolute left-2 -bottom-1 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Capacity Input -->
                        <div class="relative w-full">
                            <input
                                id="roomCapacityInput"
                                class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                type="number"
                                name="roomcapacity"
                                placeholder="Enter room capacity">
                            <small id="roomCapacityError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                        </div>
                        <!-- Room Price -->
                        <div class="relative">
                            <input
                                id="roomPriceInput"
                                class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                type="number"
                                name="roomprice"
                                placeholder="Enter room price">
                            <small id="roomPriceError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                        </div>
                        <!-- Quantity Input -->
                        <div class="relative w-full">
                            <input
                                id="roomQuantityInput"
                                class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                type="number"
                                name="roomquantity"
                                placeholder="Enter room quantity">
                            <small id="roomQuantityError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                        </div>
                    </div>

                    <!-- Facilities Selection -->
                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Facilities</label>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2 max-h-40 overflow-y-auto p-2 border rounded">
                            <?php
                            $facilityQuery = "SELECT * FROM facilitytb";
                            $facilityResult = $connect->query($facilityQuery);

                            if ($facilityResult->num_rows > 0) {
                                while ($facility = $facilityResult->fetch_assoc()) {
                                    $facilityID = $facility['FacilityID'];
                                    $facilityName = $facility['Facility'];
                                    echo '
                            <div class="flex items-center">
                                <input type="checkbox" id="facility_' . $facilityID . '" name="facilities[]" value="' . $facilityID . '" class="h-4 w-4 text-amber-500 focus:ring-amber-500 border-gray-300 rounded">
                                <label for="facility_' . $facilityID . '" class="ml-2 text-sm text-gray-700">' . $facilityName . '</label>
                            </div>';
                                }
                            } else {
                                echo '<p class="text-sm text-gray-500 col-span-3">No facilities available</p>';
                            }
                            ?>
                        </div>
                        <small id="facilitiesError" class="absolute left-2 -bottom-1 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>

                    <div class="flex justify-end gap-4 select-none">
                        <div id="addRoomTypeCancelBtn" class="px-4 py-2 text-amber-500 font-semibold hover:text-amber-600">
                            Cancel
                        </div>
                        <!-- Submit Button -->
                        <button
                            type="submit"
                            name="addroomtype"
                            class="bg-amber-500 text-white font-semibold px-4 py-2 rounded-sm select-none hover:bg-amber-600 transition-colors">
                            Add Room Type
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            // JavaScript to handle image preview
            const coverInput = document.getElementById('cover-input');
            const coverPreview = document.getElementById('cover-preview');
            const coverPreviewContainer = document.getElementById('cover-preview-container');
            const uploadArea = document.getElementById('upload-area');

            // Update modal cover image handling
            const updateCoverInput = document.getElementById('update-cover-input');
            const updateCoverPreview = document.getElementById('updateRoomTypeImage');
            const updateCoverPreviewContainer = document.getElementById('update-cover-preview-container');
            const updateUploadArea = document.getElementById('update-upload-area');

            // Add modal elements
            const addAdditionalInput = document.getElementById('add-additional-input');
            const addAdditionalPreviewContainer = document.getElementById('add-additional-preview-container');
            const addAdditionalUploadArea = document.getElementById('add-additional-upload-area');
            let addAdditionalImages = [];

            // Update modal elements
            const updateAdditionalInput = document.getElementById('update-additional-input');
            const updateAdditionalPreviewContainer = document.getElementById('update-additional-preview-container');
            const updateAdditionalUploadArea = document.getElementById('update-additional-upload-area');
            let updateAdditionalImages = [];

            // Cover Image Handling (remains the same)
            coverInput.addEventListener('change', function(event) {
                const file = event.target.files[0];
                if (file) {
                    if (file.size > 5 * 1024 * 1024) {
                        alert('File size exceeds 5MB limit');
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        coverPreview.src = e.target.result;
                        coverPreviewContainer.classList.remove('hidden');
                        uploadArea.classList.add('hidden');
                    }
                    reader.readAsDataURL(file);
                }
            });

            function removeCoverImage() {
                coverInput.value = '';
                coverPreviewContainer.classList.add('hidden');
                uploadArea.classList.remove('hidden');
            }

            // Update Modal Cover Image Handling
            updateCoverInput.addEventListener('change', function(event) {
                const file = event.target.files[0];
                if (file) {
                    if (file.size > 5 * 1024 * 1024) {
                        alert('File size exceeds 5MB limit');
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        updateCoverPreview.src = e.target.result;
                        updateCoverPreviewContainer.classList.remove('hidden');
                        updateUploadArea.classList.add('hidden');
                    }
                    reader.readAsDataURL(file);
                }
            });

            function removeUpdateCoverImage() {
                updateCoverInput.value = '';
                updateCoverPreviewContainer.classList.add('hidden');
                updateUploadArea.classList.remove('hidden');
            }

            // Add Modal Additional Images Handling
            addAdditionalInput.addEventListener('change', function(event) {
                handleAddAdditionalFiles(event.target.files);
            });

            function handleAddAdditionalFiles(files) {
                if (addAdditionalImages.length + files.length > 5) {
                    alert('You can upload a maximum of 5 additional images');
                    return;
                }

                for (let i = 0; i < files.length; i++) {
                    const file = files[i];

                    if (file.size > 5 * 1024 * 1024) {
                        alert(`File ${file.name} exceeds 5MB limit`);
                        continue;
                    }

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const imageId = 'add-additional-img-' + Date.now() + '-' + Math.floor(Math.random() * 1000);
                        addAdditionalImages.push({
                            id: imageId,
                            file: file,
                            preview: e.target.result
                        });

                        updateAddAdditionalPreviews();
                    }
                    reader.readAsDataURL(file);
                }
            }

            function updateAddAdditionalPreviews() {
                addAdditionalPreviewContainer.innerHTML = '';

                addAdditionalImages.forEach((image, index) => {
                    const previewDiv = document.createElement('div');
                    previewDiv.className = 'relative group';
                    previewDiv.innerHTML = `
                <img src="${image.preview}" class="w-full h-32 object-cover rounded-lg border border-gray-200">
                <button type="button" onclick="removeAddAdditionalImage('${image.id}')" 
                    class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm hover:bg-red-600 shadow-md transition-opacity opacity-0 group-hover:opacity-100">
                    ×
                </button>
            `;
                    addAdditionalPreviewContainer.appendChild(previewDiv);
                });

                if (addAdditionalImages.length >= 5) {
                    addAdditionalUploadArea.classList.add('hidden');
                } else {
                    addAdditionalUploadArea.classList.remove('hidden');
                }
            }

            function removeAddAdditionalImage(id) {
                addAdditionalImages = addAdditionalImages.filter(img => img.id !== id);
                updateAddAdditionalPreviews();
            }

            // Update Modal Additional Images Handling
            updateAdditionalInput.addEventListener('change', function(event) {
                handleUpdateAdditionalFiles(event.target.files);
            });

            function handleUpdateAdditionalFiles(files) {
                if (updateAdditionalImages.length + files.length > 5) {
                    alert('You can upload a maximum of 5 additional images');
                    return;
                }

                for (let i = 0; i < files.length; i++) {
                    const file = files[i];

                    if (file.size > 5 * 1024 * 1024) {
                        alert(`File ${file.name} exceeds 5MB limit`);
                        continue;
                    }

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const imageId = 'update-additional-img-' + Date.now() + '-' + Math.floor(Math.random() * 1000);
                        updateAdditionalImages.push({
                            id: imageId,
                            file: file,
                            preview: e.target.result
                        });

                        updateUpdateAdditionalPreviews();
                    }
                    reader.readAsDataURL(file);
                }
            }

            function updateUpdateAdditionalPreviews() {
                updateAdditionalPreviewContainer.innerHTML = '';

                updateAdditionalImages.forEach((image, index) => {
                    const previewDiv = document.createElement('div');
                    previewDiv.className = 'relative group';
                    previewDiv.innerHTML = `
                <img src="${image.preview}" class="w-full h-32 object-cover rounded-lg border border-gray-200">
                <button type="button" onclick="removeUpdateAdditionalImage('${image.id}')" 
                    class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm hover:bg-red-600 shadow-md transition-opacity opacity-0 group-hover:opacity-100">
                    ×
                </button>
            `;
                    updateAdditionalPreviewContainer.appendChild(previewDiv);
                });

                if (updateAdditionalImages.length >= 5) {
                    updateAdditionalUploadArea.classList.add('hidden');
                } else {
                    updateAdditionalUploadArea.classList.remove('hidden');
                }
            }

            function removeUpdateAdditionalImage(id) {
                updateAdditionalImages = updateAdditionalImages.filter(img => img.id !== id);
                updateUpdateAdditionalPreviews();
            }

            // Drag and Drop Handling (updated for both modals)
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                [uploadArea, addAdditionalUploadArea, updateAdditionalUploadArea].forEach(area => {
                    area.addEventListener(eventName, preventDefaults, false);
                });
            });

            ['dragenter', 'dragover'].forEach(eventName => {
                uploadArea.addEventListener(eventName, () => highlight(uploadArea), false);
                addAdditionalUploadArea.addEventListener(eventName, () => highlight(addAdditionalUploadArea), false);
                updateAdditionalUploadArea.addEventListener(eventName, () => highlight(updateAdditionalUploadArea), false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                uploadArea.addEventListener(eventName, () => unhighlight(uploadArea), false);
                addAdditionalUploadArea.addEventListener(eventName, () => unhighlight(addAdditionalUploadArea), false);
                updateAdditionalUploadArea.addEventListener(eventName, () => unhighlight(updateAdditionalUploadArea), false);
            });

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

            addAdditionalUploadArea.addEventListener('drop', function(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                handleAddAdditionalFiles(files);
            });

            updateAdditionalUploadArea.addEventListener('drop', function(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                handleUpdateAdditionalFiles(files);
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