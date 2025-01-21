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

    // Check if the product type already exists using prepared statement
    $checkQuery = "SELECT RoomType FROM roomtypetb WHERE RoomType = '$roomtype'";

    $checkQuery = mysqli_query($connect, $checkQuery);
    $count = mysqli_num_rows($checkQuery);

    if ($count > 0) {
        $alertMessage = 'Room type you added is already existed.';
    } else {
        $RoomTypeQuery = "INSERT INTO roomtypetb (RoomTypeID, RoomType, RoomDescription, RoomCapacity)
        VALUES ('$roomTypeID', '$roomtype', '$description', '$roomcapacity')";

        if (mysqli_query($connect, $RoomTypeQuery)) {
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
        $result = mysqli_query($connect, $query);
        $roomtype = mysqli_fetch_assoc($result);

        if ($roomtype) {
            echo json_encode(['success' => true, 'roomtype' => $roomtype]);
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

    if (mysqli_query($connect, $updateQuery)) {
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

    if (mysqli_query($connect, $deleteQuery)) {
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/3.5.0/remixicon.css" integrity="sha512-HXXR0l2yMwHDrDyxJbrMD9eLvPe3z3qL3PPeozNTsiHJEENxx8DH2CxmV05iwG0dwoz5n4gQZQyYLUNt1Wdgfg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
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
                    <h2 class="text-xl font-bold mb-4">Add Room Type Overview</h2>
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
                    <h1 class="text-lg font-semibold text-nowrap">All Room Types <span class="text-gray-400 text-sm ml-2"><?php echo $roomTypeCount ?></span></h1>
                    <div class="flex items-center w-full">
                        <input type="text" name="roomtype_search" class="p-2 ml-0 sm:ml-5 border border-gray-300 rounded-md w-full" placeholder="Search for room type..." value="<?php echo isset($_GET['roomtype_search']) ? htmlspecialchars($_GET['roomtype_search']) : ''; ?>">
                    </div>
                </form>
                <div class="tableScrollBar overflow-y-auto max-h-[510px]">
                    <table class="min-w-full bg-white rounded-lg">
                        <thead>
                            <tr class="bg-gray-100 text-gray-600 text-sm">
                                <th class="p-3 text-start">ID</th>
                                <th class="p-3 text-start">Type</th>
                                <th class="p-3 text-start hidden sm:table-cell">Description</th>
                                <th class="p-3 text-start hidden sm:table-cell">Capacity</th>
                                <th class="p-3 text-start">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm">
                            <?php foreach ($roomTypes as $roomType): ?>
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="p-3 text-start whitespace-nowrap">
                                        <div class="flex items-center gap-2 font-medium text-gray-500">
                                            <input type="checkbox" class="form-checkbox h-3 w-3 border-2 text-amber-500">
                                            <span><?= htmlspecialchars($roomType['RoomTypeID']) ?></span>
                                        </div>
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
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Controls -->
                <div class="flex justify-center items-center mt-1">
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
                <h2 class="text-xl font-bold mb-4">Edit Room Type</h2>
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
            <div class="bg-white w-full md:w-1/3 p-6 rounded-md shadow-md ">
                <h2 class="text-xl font-bold mb-4">Add New Room Type</h2>
                <form class="flex flex-col space-y-4" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" id="roomTypeForm">
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