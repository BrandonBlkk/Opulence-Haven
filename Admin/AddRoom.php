<?php
session_start();
include('../config/dbConnection.php');
include('../includes/AutoIDFunc.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

$alertMessage = '';
$addRoomSuccess = false;
$updateRoomSuccess = false;
$deleteRoomSuccess = false;
$roomID = AutoID('roomtb', 'RoomID', 'R-', 6);

// Add Room
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['addroom'])) {
    $roomname = mysqli_real_escape_string($connect, $_POST['roomname']);
    $description = mysqli_real_escape_string($connect, $_POST['roomdescription']);
    $roomprice = mysqli_real_escape_string($connect, $_POST['roomprice']);
    $roomstatus = mysqli_real_escape_string($connect, $_POST['roomstatus']);
    $roomtype = mysqli_real_escape_string($connect, $_POST['roomtype']);

    // Product image upload 
    $productImage = $_FILES["roomcoverimage"]["name"];
    $copyFile = "AdminImages/";
    $fileName = $copyFile . uniqid() . "_" . $productImage;
    $copy = copy($_FILES["roomcoverimage"]["tmp_name"], $fileName);

    if (!$copy) {
        echo "<p>Cannot upload Product Image.</p>";
        exit();
    }
    // $userProductImage = $_FILES["roomcoverimage"]["name"];
    // $copyFile = "../UserImages/";
    // $userFileName = $copyFile . uniqid() . "_" . $userProductImage;
    // $copy = copy($_FILES["roomcoverimage"]["tmp_name"], $userFileName);

    // if (!$copy) {
    //     echo "<p>Cannot upload Product Image.</p>";
    //     exit();
    // }

    // Check if the room already exists
    $checkQuery = "SELECT RoomName FROM roomtb WHERE RoomName = '$roomname'";
    $count = $connect->query($checkQuery)->num_rows;

    if ($count > 0) {
        $alertMessage = 'Room you added is already existed.';
    } else {
        $RoomQuery = "INSERT INTO roomtb (RoomID, RoomCoverImage, RoomName, RoomDescription, RoomPrice, RoomStatus, RoomTypeID)
        VALUES ('$roomID', '$fileName', '$roomname', '$description', '$roomprice', '$roomstatus', '$roomtype')";

        if ($connect->query($RoomQuery)) {
            // Insert selected facilities into roomfacilitytb
            if (isset($_POST['facilities']) && is_array($_POST['facilities'])) {
                foreach ($_POST['facilities'] as $facilityID) {
                    $facilityID = mysqli_real_escape_string($connect, $facilityID);
                    $insertFacility = "INSERT INTO roomfacilitytb (RoomID, FacilityID) VALUES ('$roomID', '$facilityID')";
                    $connect->query($insertFacility);
                }
            }
            $addRoomSuccess = true;
        } else {
            $alertMessage = "Failed to add room. Please try again.";
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
    <div class="flex flex-col md:flex-row md:space-x-3 p-3 ml-0 md:ml-[250px] min-w-[350px]">
        <!-- Left Side Content -->
        <div class="w-full bg-white p-2">
            <div class="flex justify-between items-end">
                <div>
                    <h2 class="text-xl text-gray-700 font-bold mb-4">Add Room</h2>
                    <p>Add room details to create new entries, manage availability, and organize rooms for better tracking and efficient management.</p>
                </div>
                <button id="addRoomBtn" class="bg-amber-500 text-white font-semibold px-3 py-1 rounded select-none hover:bg-amber-600 transition-colors">
                    <i class="ri-add-line text-xl"></i>
                </button>
            </div>

            <!-- Room Table -->
            <div class="overflow-x-auto">
                <!-- Room Search and Filter -->
                <form method="GET" class="my-4 flex items-start sm:items-center justify-between flex-col sm:flex-row gap-2 sm:gap-0">
                    <h1 class="text-lg text-gray-700 font-semibold text-nowrap">All Rooms <span class="text-gray-400 text-sm ml-2"><?php echo $allRoomCount ?></span></h1>
                    <div class="flex items-center w-full">
                        <input type="text" name="room_search" class="p-2 ml-0 sm:ml-5 border border-gray-300 rounded-md w-full outline-none focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out" placeholder="Search for room..." value="<?php echo isset($_GET['room_search']) ? htmlspecialchars($_GET['room_search']) : ''; ?>">
                    </div>
                </form>
                <div class="tableScrollBar overflow-y-auto max-h-[510px]">
                    <table class="min-w-full bg-white rounded-lg">
                        <thead>
                            <tr class="bg-gray-100 text-gray-600 text-sm">
                                <th class="p-3 text-start">ID</th>
                                <th class="p-3 text-start">Cover Image</th>
                                <th class="p-3 text-start hidden sm:table-cell">Room</th>
                                <th class="p-3 text-start hidden sm:table-cell">Description</th>
                                <th class="p-3 text-start hidden sm:table-cell">Price</th>
                                <th class="p-3 text-start hidden sm:table-cell">Status</th>
                                <th class="p-3 text-start hidden sm:table-cell">Room Type</th>
                                <th class="p-3 text-start">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm">
                            <?php if (!empty($rooms)): ?>
                                <?php foreach ($rooms as $room): ?>
                                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                                        <td class="p-3 text-start whitespace-nowrap">
                                            <div class="flex items-center gap-2 font-medium text-gray-500">
                                                <input type="checkbox" class="form-checkbox h-3 w-3 border-2 text-amber-500">
                                                <span><?= htmlspecialchars($room['RoomID']) ?></span>
                                            </div>
                                        </td>
                                        <td class="p-3 text-start select-none">
                                            <img src="<?= htmlspecialchars($room['RoomCoverImage']) ?>" alt="Product Image" class="w-12 h-12 object-cover rounded-sm">
                                        </td>
                                        <td class="p-3 text-start">
                                            <?= htmlspecialchars($room['RoomName']) ?>
                                        </td>
                                        <td class="p-3 text-start hidden sm:table-cell">
                                            <?= htmlspecialchars($room['RoomDescription']) ?>
                                        </td>
                                        <td class="p-3 text-start hidden sm:table-cell">
                                            <?= htmlspecialchars($room['RoomPrice']) ?>
                                        </td>
                                        <td class="p-3 text-start hidden sm:table-cell">
                                            <?= htmlspecialchars($room['RoomStatus']) ?>
                                        </td>
                                        <td class="p-3 text-start hidden md:table-cell">
                                            <?php
                                            // Fetch the specific product type for the supplier
                                            $roomTypeID = $room['RoomTypeID'];
                                            $roomTypeQuery = "SELECT RoomType FROM roomtypetb WHERE RoomTypeID = '$roomTypeID'";
                                            $roomTypeResult = mysqli_query($connect, $roomTypeQuery);

                                            if ($roomTypeResult && $roomTypeResult->num_rows > 0) {
                                                $roomTypeRow = $roomTypeResult->fetch_assoc();
                                                echo htmlspecialchars($roomTypeRow['RoomType']);
                                            }
                                            ?>
                                        </td>
                                        <td class="p-3 text-start space-x-1 select-none">
                                            <i class="details-btn ri-eye-line text-lg cursor-pointer"
                                                data-roomtype-id="<?= htmlspecialchars($room['RoomTypeID']) ?>"></i>
                                            <button class="text-red-500">
                                                <i class="delete-btn ri-delete-bin-7-line text-xl"
                                                    data-roomtype-id="<?= htmlspecialchars($room['RoomTypeID']) ?>"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="p-3 text-center text-gray-500 py-52">
                                        No rooms available.
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

        <!-- Room Details Modal -->
        <div id="updateRoomModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <div class="bg-white max-w-5xl p-6 rounded-md shadow-md text-center w-full sm:max-w-[500px]">
                <h2 class="text-xl text-start text-gray-700 font-bold">Edit Room</h2>
                <form class="flex flex-col space-y-4" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" id="updateRoomForm">
                    <input type="hidden" name="roomtypeid" id="updateRoomID">
                    <!-- Room Input -->
                    <div class="relative w-full">
                        <label class="block text-sm text-start font-medium text-gray-700 mb-1">Room Information</label>
                        <input
                            id="updateRoomInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="text"
                            name="updateroomtype"
                            placeholder="Enter room type">
                        <small id="updateRoomError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>
                    <!-- Description Input -->
                    <div class="relative w-full">
                        <label class="block text-sm text-start font-medium text-gray-700 mb-1">Description</label>
                        <textarea
                            id="updateRoomDescriptionInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            name="updateroomtypedescription"
                            placeholder="Enter room type description"></textarea>
                        <small id="updateRoomDescriptionError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
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
                        <div id="updateRoomModalCancelBtn" class="px-4 py-2 bg-gray-200 text-black hover:bg-gray-300 rounded-sm">
                            Cancel
                        </div>
                        <button
                            type="submit"
                            name="editroom"
                            class="bg-amber-500 text-white px-4 py-2 select-none hover:bg-amber-600 rounded-sm">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Room Delete Modal -->
        <div id="roomConfirmDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <form class="bg-white max-w-5xl p-6 rounded-md shadow-md text-center" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" id="productDeleteForm">
                <h2 class="text-xl font-semibold text-red-600 mb-4">Confirm Room Deletion</h2>
                <p class="text-slate-600 mb-2">You are about to delete the following Room : <span id="roomDeleteName" class="font-semibold"></span></p>
                <p class="text-sm text-gray-500 mb-4">
                    Deleting this Room will permanently remove it from the system, including all associated data.
                </p>
                <input type="hidden" name="roomtypeid" id="deleteRoomID">
                <div class="flex justify-end gap-4 select-none">
                    <div id="roomCancelDeleteBtn" class="px-4 py-2 bg-gray-200 text-black hover:bg-gray-300 rounded-sm">
                        Cancel
                    </div>
                    <button
                        type="submit"
                        name="deleteroom"
                        class="px-4 py-2 bg-red-600 text-white hover:bg-red-700 rounded-sm">
                        Delete
                    </button>
                </div>
            </form>
        </div>

        <!-- Add Room Form -->
        <div id="addRoomModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <div class="bg-white w-full md:w-1/3 p-6 rounded-md shadow-md">
                <h2 class="text-xl text-gray-700 font-bold mb-4">Add New Room</h2>
                <form class="flex flex-col space-y-4" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" id="roomForm" enctype="multipart/form-data">

                    <!-- Cover Image Upload - Updated Section -->
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

                    <!-- Room Input -->
                    <div class="relative w-full">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Room Information</label>
                        <input
                            id="roomNameInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="text"
                            name="roomname"
                            placeholder="Enter room name">
                        <small id="roomNameError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>

                    <!-- Description Input -->
                    <div class="relative">
                        <textarea
                            id="roomDescriptionInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="text"
                            name="roomdescription"
                            placeholder="Enter room description"></textarea>
                        <small id="roomDescriptionError" class="absolute left-2 -bottom-1 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>

                    <!-- Price Input -->
                    <div class="relative">
                        <input
                            id="roomPriceInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="number"
                            name="roomprice"
                            placeholder="Enter room price">
                        <small id="roomPriceError" class="absolute left-2 -bottom-1 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
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

                    <div class="flex flex-col sm:flex-row gap-4 sm:gap-2">
                        <!-- Status -->
                        <div class="relative flex-1">
                            <select name="roomstatus" id="roomstatus" class="p-2 w-full border rounded outline-none" required>
                                <option value="" disabled selected>Status</option>
                                <option value="available">Available</option>
                                <option value="unavailable">Unavailable</option>
                            </select>
                        </div>
                        <!-- Room Type -->
                        <div class="relative flex-1">
                            <select name="roomtype" id="roomtype" class="p-2 w-full border rounded outline-none" required>
                                <option value="" disabled selected>Select type of rooms</option>
                                <?php
                                $select = "SELECT * FROM roomtypetb";
                                $query = $connect->query($select);
                                $count = $query->num_rows;
                                if ($count) {
                                    for ($i = 0; $i < $count; $i++) {
                                        $row = $query->fetch_assoc();
                                        $room_type_id = $row['RoomTypeID'];
                                        $room_type = $row['RoomType'];
                                        echo "<option value='$room_type_id'>$room_type</option>";
                                    }
                                } else {
                                    echo "<option value='' disabled>No data yet</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end gap-4 select-none">
                        <div id="addRoomCancelBtn" class="px-4 py-2 text-amber-500 font-semibold hover:text-amber-600">
                            Cancel
                        </div>
                        <!-- Submit Button -->
                        <button
                            type="submit"
                            name="addroom"
                            class="bg-amber-500 text-white font-semibold px-4 py-2 rounded-sm select-none hover:bg-amber-600 transition-colors">
                            Add Room
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

            // Handle file selection
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

            // Handle drag and drop
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                uploadArea.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            ['dragenter', 'dragover'].forEach(eventName => {
                uploadArea.addEventListener(eventName, highlight, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                uploadArea.addEventListener(eventName, unhighlight, false);
            });

            function highlight() {
                uploadArea.classList.add('border-blue-400', 'bg-blue-50');
            }

            function unhighlight() {
                uploadArea.classList.remove('border-blue-400', 'bg-blue-50');
            }

            uploadArea.addEventListener('drop', handleDrop, false);

            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                coverInput.files = files;
                const event = new Event('change');
                coverInput.dispatchEvent(event);
            }

            function removeCoverImage() {
                coverInput.value = '';
                coverPreviewContainer.classList.add('hidden');
                uploadArea.classList.remove('hidden');
            }
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