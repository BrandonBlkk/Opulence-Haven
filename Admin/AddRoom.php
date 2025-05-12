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
    $roomstatus = mysqli_real_escape_string($connect, $_POST['roomstatus']);
    $roomtype = mysqli_real_escape_string($connect, $_POST['roomtype']);

    // Check if the room already exists
    $checkQuery = "SELECT RoomName FROM roomtb WHERE RoomName = '$roomname'";
    $count = $connect->query($checkQuery)->num_rows;

    if ($count > 0) {
        $alertMessage = 'Room you added is already existed.';
    } else {
        $RoomQuery = "INSERT INTO roomtb (RoomID, RoomName, RoomStatus, RoomTypeID)
        VALUES ('$roomID', '$roomname', '$roomstatus', '$roomtype')";

        if ($connect->query($RoomQuery)) {
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
                                <th class="p-3 text-start hidden sm:table-cell">Room</th>
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
                                        <td class="p-3 text-start">
                                            <?= htmlspecialchars($room['RoomName']) ?>
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
                    <!-- Room Name -->
                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Room Name</label>
                        <input
                            id="updateRoomNameInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="text"
                            name="updateroomname"
                            placeholder="Enter room name">
                        <small id="updateRoomNameError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>

                    <!-- Status and Room Type -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Status -->
                        <div class="relative">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="updateroomstatus" id="updateroomstatus" class="p-2 w-full border rounded outline-none" required>
                                <option value="" disabled selected>Select status</option>
                                <option value="available">Available</option>
                                <option value="unavailable">Unavailable</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                        </div>

                        <!-- Room Type -->
                        <div class="relative">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Room Type</label>
                            <select name="updateroomtype" id="updateroomtype" class="p-2 w-full border rounded outline-none" required>
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
            <div class="bg-white w-full md:w-1/3 mx-4 p-6 rounded-md shadow-md max-h-[90vh] overflow-y-auto">
                <h2 class="text-xl text-gray-700 font-bold mb-4">Add New Room</h2>
                <form class="flex flex-col space-y-4" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" id="roomForm">

                    <!-- Room Name -->
                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Room Name</label>
                        <input
                            id="roomNameInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="text"
                            name="roomname"
                            placeholder="Enter room name">
                        <small id="roomNameError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>

                    <!-- Status and Room Type -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Status -->
                        <div class="relative">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="roomstatus" id="roomstatus" class="p-2 w-full border rounded outline-none" required>
                                <option value="" disabled selected>Select status</option>
                                <option value="Available">Available</option>
                                <option value="Unavailable">Unavailable</option>
                                <option value="Maintenance">Maintenance</option>
                            </select>
                        </div>

                        <!-- Room Type -->
                        <div class="relative">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Room Type</label>
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

                    <!-- Form Actions -->
                    <div class="flex justify-end gap-4 select-none pt-4">
                        <div id="addRoomCancelBtn" class="px-4 py-2 text-amber-500 font-semibold hover:text-amber-600 cursor-pointer">
                            Cancel
                        </div>
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