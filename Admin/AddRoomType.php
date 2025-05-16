<?php
session_start();
include('../config/dbConnection.php');
include('../includes/AutoIDFunc.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

$alertMessage = '';
$addRoomTypeSuccess = false;
$updateRoomTypeSuccess = false;
$deleteRoomTypeSuccess = false;
$roomTypeID = AutoID('roomtypetb', 'RoomTypeID', 'RT-', 6);

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
        $alertMessage = 'Room type you added is already existed.';
    } else {
        $RoomTypeQuery = "INSERT INTO roomtypetb (RoomTypeID, RoomCoverImage, RoomType, RoomDescription, RoomCapacity, RoomPrice, RoomQuantity)
        VALUES ('$roomTypeID', '$fileName', '$roomtype', '$description', '$roomcapacity', '$roomprice', '$roomquantity')";

        if ($connect->query($RoomTypeQuery)) {
            // Insert selected facilities into roomfacilitytb
            if (isset($_POST['facilities']) && is_array($_POST['facilities'])) {
                foreach ($_POST['facilities'] as $facilityID) {
                    $facilityID = mysqli_real_escape_string($connect, $facilityID);
                    $insertFacility = "INSERT INTO roomfacilitytb (RoomTypeID, FacilityID) VALUES ('$roomTypeID', '$facilityID')";
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
                            $insertImage = "INSERT INTO roomimagetb (ImagePath, RoomTypeID) VALUES ('$imagePath', '$roomTypeID')";
                            $connect->query($insertImage);
                        }
                    }
                }
            }

            $addRoomTypeSuccess = true;
        } else {
            $alertMessage = "Failed to add room type. Please try again.";
        }
    }
}

// Get Room Type Details
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = mysqli_real_escape_string($connect, $_GET['id']);
    $action = $_GET['action'];

    // Build query based on action
    $query = match ($action) {
        'getRoomTypeDetails' => "SELECT * FROM roomtypetb WHERE RoomTypeID = '$id'",
        default => null
    };
    if ($query) {
        $result = $connect->query($query)?->fetch_assoc();

        if ($result) {
            echo json_encode(['success' => true, 'roomtype' => $result]);
        } else {
            echo json_encode(['success' => false]);
        }
    }
    exit;
}

// Update Product Type
if (isset($_POST['editroomtype'])) {
    $roomTypeId = mysqli_real_escape_string($connect, $_POST['roomtypeid']);
    $RoomType = mysqli_real_escape_string($connect, $_POST['updateroomtype']);
    $updatedRoomTypeDescription = mysqli_real_escape_string($connect, $_POST['updateroomtypedescription']);
    $updatedRoomCapacity = mysqli_real_escape_string($connect, $_POST['updateroomcapacity']);

    // Update query
    $updateQuery = "UPDATE roomtypetb SET RoomType = '$RoomType', RoomDescription = '$updatedRoomTypeDescription', RoomCapacity = '$updatedRoomCapacity' 
    WHERE RoomTypeID = '$roomTypeId'";

    if ($connect->query($updateQuery)) {
        $updateRoomTypeSuccess = true;
    } else {
        $alertMessage = "Failed to update room type. Please try again.";
    }
}

// Delete Product Type
if (isset($_POST['deleteroomtype'])) {
    $roomTypeId = mysqli_real_escape_string($connect, $_POST['roomtypeid']);

    // Build query based on action
    $deleteQuery = "DELETE FROM roomtypetb WHERE RoomTypeID = '$roomTypeId'";

    if ($connect->query($deleteQuery)) {
        $deleteRoomTypeSuccess = true;
    } else {
        $alertMessage = "Failed to delete room type. Please try again.";
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
    <div class="flex flex-col md:flex-row md:space-x-3 p-3 ml-0 md:ml-[250px] min-w-[380px]">
        <!-- Left Side Content -->
        <div class="w-full bg-white p-2">
            <div class="flex justify-between items-end">
                <div>
                    <h2 class="text-xl text-gray-700 font-bold mb-4">Add Room Type Overview</h2>
                    <p>Add information about room types to categorize items, track stock levels, and manage room details for efficient organization.</p>
                </div>
                <button id="addRoomTypeBtn" class="bg-amber-500 text-white font-semibold px-3 py-1 rounded select-none hover:bg-amber-600 transition-colors">
                    <i class="ri-add-line text-xl"></i>
                </button>
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
                <div class="tableScrollBar overflow-y-auto max-h-[510px]">
                    <table class="min-w-full bg-white rounded-lg">
                        <thead>
                            <tr class="bg-gray-100 text-gray-600 text-sm">
                                <th class="p-3 text-start">ID</th>
                                <th class="p-3 text-start">Cover Image</th>
                                <th class="p-3 text-start">Type</th>
                                <th class="p-3 text-start hidden sm:table-cell">Description</th>
                                <th class="p-3 text-start hidden sm:table-cell">Capacity</th>
                                <th class="p-3 text-start">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm">
                            <?php if (!empty($roomTypes)): ?>
                                <?php foreach ($roomTypes as $roomType): ?>
                                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                                        <td class="p-3 text-start whitespace-nowrap">
                                            <div class="flex items-center gap-2 font-medium text-gray-500">
                                                <input type="checkbox" class="form-checkbox h-3 w-3 border-2 text-amber-500">
                                                <span><?= htmlspecialchars($roomType['RoomTypeID']) ?></span>
                                            </div>
                                        </td>
                                        <td class="p-3 text-start select-none">
                                            <img src="<?= htmlspecialchars($roomType['RoomCoverImage']) ?>" alt="Product Image" class="w-12 h-12 object-cover rounded-sm">
                                        </td>
                                        <td class="p-3 text-start">
                                            <?= htmlspecialchars($roomType['RoomType']) ?>
                                        </td>
                                        <td class="p-3 text-start hidden sm:table-cell">
                                            <?= htmlspecialchars($roomType['RoomDescription']) ?>
                                        </td>
                                        <td class="p-3 text-start hidden sm:table-cell">
                                            <?= htmlspecialchars($roomType['RoomCapacity']) ?>
                                        </td>
                                        <td class="p-3 text-start space-x-1 select-none">
                                            <i class="details-btn ri-eye-line text-lg cursor-pointer"
                                                data-roomtype-id="<?= htmlspecialchars($roomType['RoomTypeID']) ?>"></i>
                                            <button class="text-red-500">
                                                <i class="delete-btn ri-delete-bin-7-line text-xl"
                                                    data-roomtype-id="<?= htmlspecialchars($roomType['RoomTypeID']) ?>"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="p-3 text-center text-gray-500 py-52">
                                        No room types available.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Controls -->
                <div class="flex justify-center items-center mt-1 <?= (!empty($roomTypes) ? 'flex' : 'hidden') ?>">
                    <!-- Previous Btn -->
                    <?php if ($roomTypeCurrentPage > 1) {
                    ?>
                        <a href="?roomtypepage=<?= $roomTypeCurrentPage - 1 ?>"
                            class="px-3 py-1 mx-1 border rounded <?= $roomtypepage == $roomTypeCurrentPage ? 'bg-gray-200' : 'bg-white' ?>">
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
                    <?php for ($roomtypepage = 1; $roomtypepage <= $totalRoomTypePages; $roomtypepage++): ?>
                        <a href="?roomtypepage=<?= $roomtypepage ?>&roomtype_search=<?= htmlspecialchars($searchRoomTypeQuery) ?>"
                            class="px-3 py-1 mx-1 border rounded select-none <?= $roomtypepage == $roomTypeCurrentPage ? 'bg-gray-200' : 'bg-white' ?>">
                            <?= $roomtypepage ?>
                        </a>
                    <?php endfor; ?>
                    <!-- Next Btn -->
                    <?php if ($roomTypeCurrentPage < $totalRoomTypePages) {
                    ?>
                        <a href="?roomtypepage=<?= $roomTypeCurrentPage + 1 ?>"
                            class="px-3 py-1 mx-1 border rounded <?= $roomtypepage == $roomTypeCurrentPage ? 'bg-gray-200' : 'bg-white' ?>">
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

        <!-- Room Type Details Modal -->
        <div id="updateRoomTypeModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <div class="bg-white max-w-5xl p-6 rounded-md shadow-md text-center w-full sm:max-w-[500px]">
                <h2 class="text-xl text-start text-gray-700 font-bold">Edit Room Type</h2>
                <form class="flex flex-col space-y-4" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" id="updateRoomTypeForm">
                    <input type="hidden" name="roomtypeid" id="updateRoomTypeID">
                    <!-- Room Type Input -->
                    <div class="relative w-full">
                        <label class="block text-sm text-start font-medium text-gray-700 mb-1">Room Type Information</label>
                        <input
                            id="updateRoomTypeInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="text"
                            name="updateroomtype"
                            placeholder="Enter room type">
                        <small id="updateRoomTypeError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>
                    <!-- Description Input -->
                    <div class="relative w-full">
                        <label class="block text-sm text-start font-medium text-gray-700 mb-1">Description</label>
                        <textarea
                            id="updateRoomTypeDescriptionInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            name="updateroomtypedescription"
                            placeholder="Enter room type description"></textarea>
                        <small id="updateRoomTypeDescriptionError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>
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
                    <!-- Submit Button -->
                    <div class="flex justify-end gap-4 select-none">
                        <div id="updateRoomTypeModalCancelBtn" class="px-4 py-2 bg-gray-200 text-black hover:bg-gray-300 rounded-sm">
                            Cancel
                        </div>
                        <button
                            type="submit"
                            name="editroomtype"
                            class="bg-amber-500 text-white px-4 py-2 select-none hover:bg-amber-600 rounded-sm">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Room Type Delete Modal -->
        <div id="roomTypeConfirmDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <form class="bg-white max-w-5xl p-6 rounded-md shadow-md text-center" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" id="productTypeDeleteForm">
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

        <!-- Add Room Type Form -->
        <div id="addRoomTypeModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <div class="bg-white w-full max-w-4xl p-6 rounded-md shadow-md max-h-[90vh] overflow-y-auto">
                <h2 class="text-xl text-gray-700 font-bold mb-4">Add New Room Type</h2>
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
                        <div id="additional-preview-container" class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-4"></div>
                        <div class="relative">
                            <div id="additional-upload-area" class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center bg-gray-50 hover:bg-gray-100 transition-colors cursor-pointer">
                                <div class="flex flex-col items-center justify-center space-y-2 py-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <p class="text-sm text-gray-600">Click to upload or drag and drop</p>
                                    <p class="text-xs text-gray-500">PNG, JPG, JPEG (Max. 5MB each, Max 5 files)</p>
                                </div>
                                <input type="file" name="additional_images[]" id="additional-input" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept="image/jpeg,image/png,image/jpg" multiple>
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
            const additionalInput = document.getElementById('additional-input');
            const additionalPreviewContainer = document.getElementById('additional-preview-container');
            const additionalUploadArea = document.getElementById('additional-upload-area');
            let additionalImages = [];

            // Cover Image Handling
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

            // Additional Images Handling
            additionalInput.addEventListener('change', function(event) {
                handleAdditionalFiles(event.target.files);
            });

            function handleAdditionalFiles(files) {
                if (additionalImages.length + files.length > 5) {
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
                        const imageId = 'additional-img-' + Date.now() + '-' + Math.floor(Math.random() * 1000);
                        additionalImages.push({
                            id: imageId,
                            file: file,
                            preview: e.target.result
                        });

                        updateAdditionalPreviews();
                    }
                    reader.readAsDataURL(file);
                }
            }

            function updateAdditionalPreviews() {
                additionalPreviewContainer.innerHTML = '';

                additionalImages.forEach((image, index) => {
                    const previewDiv = document.createElement('div');
                    previewDiv.className = 'relative group';
                    previewDiv.innerHTML = `
                <img src="${image.preview}" class="w-full h-32 object-cover rounded-lg border border-gray-200">
                <button type="button" onclick="removeAdditionalImage('${image.id}')" 
                    class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm hover:bg-red-600 shadow-md transition-opacity opacity-0 group-hover:opacity-100">
                    ×
                </button>
            `;
                    additionalPreviewContainer.appendChild(previewDiv);
                });

                if (additionalImages.length >= 5) {
                    additionalUploadArea.classList.add('hidden');
                } else {
                    additionalUploadArea.classList.remove('hidden');
                }
            }

            function removeAdditionalImage(id) {
                additionalImages = additionalImages.filter(img => img.id !== id);
                updateAdditionalPreviews();
            }

            // Drag and Drop Handling
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                [uploadArea, additionalUploadArea].forEach(area => {
                    area.addEventListener(eventName, preventDefaults, false);
                });
            });

            ['dragenter', 'dragover'].forEach(eventName => {
                uploadArea.addEventListener(eventName, () => highlight(uploadArea), false);
                additionalUploadArea.addEventListener(eventName, () => highlight(additionalUploadArea), false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                uploadArea.addEventListener(eventName, () => unhighlight(uploadArea), false);
                additionalUploadArea.addEventListener(eventName, () => unhighlight(additionalUploadArea), false);
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

            additionalUploadArea.addEventListener('drop', function(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                handleAdditionalFiles(files);
            });
        </script>
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