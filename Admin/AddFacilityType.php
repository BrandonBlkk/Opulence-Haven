<?php
session_start();
include('../config/dbConnection.php');
include('../includes/AutoIDFunc.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

$alertMessage = '';
$addProductTypeSuccess = false;
$updateProductTypeSuccess = false;
$deleteProductTypeSuccess = false;
$facilityTypeID = AutoID('facilitytypetb', 'FacilityTypeID', 'FT-', 6);

// Add Product Type
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['addfacilitytype'])) {
    $facilitytype = mysqli_real_escape_string($connect, $_POST['facilitytype']);
    $facilitytypeicon = mysqli_real_escape_string($connect, $_POST['facilitytypeicon']);
    $facilitytypeiconsize = mysqli_real_escape_string($connect, $_POST['facilitytypeiconsize']);

    // Check if the product type already exists using prepared statement
    $checkQuery = "SELECT FacilityType FROM facilitytypetb WHERE FacilityType = '$facilitytype'";

    $checkQuery = mysqli_query($connect, $checkQuery);
    $count = mysqli_num_rows($checkQuery);

    if ($count > 0) {
        $alertMessage = 'Facility type you added is already existed.';
    } else {
        $addFacilityTypeQuery = "INSERT INTO facilitytypetb (FacilityTypeID, FacilityType, FacilityTypeIcon, IconSize)
        VALUES ('$facilityTypeID', '$facilitytype', '$facilitytypeicon', '$facilitytypeiconsize')";

        if (mysqli_query($connect, $addFacilityTypeQuery)) {
            $addProductTypeSuccess = true;
        } else {
            $alertMessage = "Failed to add facility type. Please try again.";
        }
    }
}

// Get Product Type Details
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = mysqli_real_escape_string($connect, $_GET['id']);
    $action = $_GET['action'];

    // Build query based on action
    $query = match ($action) {
        'getProductTypeDetails' => "SELECT * FROM producttypetb WHERE ProductTypeID = '$id'",
        default => null
    };
    if ($query) {
        $result = mysqli_query($connect, $query);
        $producttype = mysqli_fetch_assoc($result);

        if ($producttype) {
            echo json_encode(['success' => true, 'producttype' => $producttype]);
        } else {
            echo json_encode(['success' => false]);
        }
    }
    exit;
}

// Update Product Type
if (isset($_POST['editproducttype'])) {
    $productTypeId = mysqli_real_escape_string($connect, $_POST['producttypeid']);
    $updatedProductType = mysqli_real_escape_string($connect, $_POST['updateproducttype']);
    $updatedDescription = mysqli_real_escape_string($connect, $_POST['updatedescription']);

    // Update query
    $updateQuery = "UPDATE producttypetb SET ProductType = '$updatedProductType', Description = '$updatedDescription' WHERE ProductTypeID = '$productTypeId'";

    if (mysqli_query($connect, $updateQuery)) {
        $updateProductTypeSuccess = true;
    } else {
        $alertMessage = "Failed to update product type. Please try again.";
    }
}

// Delete Product Type
if (isset($_POST['deleteproducttype'])) {
    $productTypeId = mysqli_real_escape_string($connect, $_POST['producttypeid']);

    // Build query based on action
    $deleteQuery = "DELETE FROM producttypetb WHERE ProductTypeID = '$productTypeId'";

    if (mysqli_query($connect, $deleteQuery)) {
        $deleteProductTypeSuccess = true;
    } else {
        $alertMessage = "Failed to delete product type. Please try again.";
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
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
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
                    <h2 class="text-xl font-bold mb-4">Add Facility Type Overview</h2>
                    <p>Add information about product types to categorize items, track stock levels, and manage product details for efficient organization.</p>
                </div>
                <button id="addProductTypeBtn" class="bg-amber-500 text-white font-semibold px-3 py-1 rounded select-none hover:bg-amber-600 transition-colors">
                    <i class="ri-add-line text-xl"></i>
                </button>
            </div>

            <!-- Facility Type Table -->
            <div class="overflow-x-auto">
                <!-- Facility Type Search and Filter -->
                <form method="GET" class="my-4 flex items-start sm:items-center justify-between flex-col sm:flex-row gap-2 sm:gap-0">
                    <h1 class="text-lg font-semibold text-nowrap">All Facility Types <span class="text-gray-400 text-sm ml-2"><?php echo $facilityTypeCount ?></span></h1>
                    <div class="flex items-center w-full">
                        <input type="text" name="facilitytype_search" class="p-2 ml-0 sm:ml-5 border border-gray-300 rounded-md w-full" placeholder="Search for facility type..." value="<?php echo isset($_GET['facilitytype_search']) ? htmlspecialchars($_GET['facilitytype_search']) : ''; ?>">
                    </div>
                </form>
                <div class="tableScrollBar overflow-y-auto max-h-[510px]">
                    <table class="min-w-full bg-white rounded-lg">
                        <thead>
                            <tr class="bg-gray-100 text-gray-600 text-sm">
                                <th class="p-3 text-start">ID</th>
                                <th class="p-3 text-start">Type</th>
                                <th class="p-3 text-start hidden sm:table-cell">Icon</th>
                                <th class="p-3 text-start">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm">
                            <?php foreach ($facilityTypes as $facilityType): ?>
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="p-3 text-start whitespace-nowrap">
                                        <div class="flex items-center gap-2 font-medium text-gray-500">
                                            <input type="checkbox" class="form-checkbox h-3 w-3 border-2 text-amber-500">
                                            <span><?= htmlspecialchars($facilityType['FacilityTypeID']) ?></span>
                                        </div>
                                    </td>
                                    <td class="p-3 text-start">
                                        <?= htmlspecialchars($facilityType['FacilityType']) ?>
                                    </td>
                                    <td class="p-3 text-start hidden sm:table-cell">
                                        <i class="<?= htmlspecialchars($facilityType['FacilityTypeIcon'], ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($facilityType['IconSize'], ENT_QUOTES, 'UTF-8') ?>"></i>
                                    </td>

                                    <td class="p-3 text-start space-x-1 select-none">
                                        <i class="details-btn ri-eye-line text-lg cursor-pointer"
                                            data-producttype-id="<?= htmlspecialchars($facilityType['FacilityTypeID']) ?>"></i>
                                        <button class="text-red-500">
                                            <i class="delete-btn ri-delete-bin-7-line text-xl"
                                                data-producttype-id="<?= htmlspecialchars($facilityType['FacilityTypeID']) ?>"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Controls -->
                <div class="flex justify-center items-center mt-1">
                    <?php if ($facilityTypeCurrentPage > 1) {
                    ?>
                        <a href="?facilitytypepage=<?= $facilityTypeCurrentPage - 1 ?>"
                            class="px-3 py-1 mx-1 border rounded <?= $facilitytypepage == $facilityTypeCurrentPage ? 'bg-gray-200' : 'bg-white' ?>">
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
                    <?php for ($facilitytypepage = 1; $facilitytypepage <= $totalFacilityTypePages; $facilitytypepage++): ?>
                        <a href="?facilitytypepage=<?= $facilitytypepage ?>&facilitytype_search=<?= htmlspecialchars($searchFacilityTypeQuery) ?>"
                            class="px-3 py-1 mx-1 border rounded select-none <?= $facilitytypepage == $facilityTypeCurrentPage ? 'bg-gray-200' : 'bg-white' ?>">
                            <?= $facilitytypepage ?>
                        </a>
                    <?php endfor; ?>
                    <!-- Next Btn -->
                    <?php if ($facilityTypeCurrentPage < $totalFacilityTypePages) {
                    ?>
                        <a href="?facilitytypepage=<?= $facilityTypeCurrentPage + 1 ?>"
                            class="px-3 py-1 mx-1 border rounded <?= $facilitytypepage == $facilityTypeCurrentPage ? 'bg-gray-200' : 'bg-white' ?>">
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

        <!-- Facility Type Details Modal -->
        <div id="updateFacilityTypeModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <div class="bg-white max-w-5xl p-6 rounded-md shadow-md text-center w-full sm:max-w-[500px]">
                <h2 class="text-xl font-bold mb-4">Edit Facility Type</h2>
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
                            id="updateFacilityTypeIcon"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            name="updatefacilityicon"
                            placeholder="Enter icon"></textarea>
                        <small id="updateFacilityTypeError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
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
                        <div id="updateFacilityTypeModalCancelBtn" class="px-4 py-2 bg-gray-200 text-black hover:bg-gray-300">
                            Cancel
                        </div>
                        <button
                            type="submit"
                            name="editfacilitytype"
                            class="bg-amber-500 text-white px-4 py-2 select-none hover:bg-amber-600">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Facility Type Delete Modal -->
        <div id="productTypeConfirmDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <form class="bg-white max-w-5xl p-6 rounded-md shadow-md text-center" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" id="productTypeDeleteForm">
                <h2 class="text-xl font-semibold text-red-600 mb-4">Confirm Facility Type Deletion</h2>
                <p class="text-slate-600 mb-2">You are about to delete the following Facility Type: <span id="productTypeDeleteName" class="font-semibold"></span></p>
                <p class="text-sm text-gray-500 mb-4">
                    Deleting this Facility Type will permanently remove it from the system, including all associated data.
                </p>
                <input type="hidden" name="producttypeid" id="deleteProductTypeID">
                <div class="flex justify-end gap-4 select-none">
                    <div id="productTypeCancelDeleteBtn" class="px-4 py-2 bg-gray-200 text-black hover:bg-gray-300">
                        Cancel
                    </div>
                    <button
                        type="submit"
                        name="deletefacilitytype"
                        class="px-4 py-2 bg-red-600 text-white hover:bg-red-700">
                        Delete
                    </button>
                </div>
            </form>
        </div>

        <!-- Add Facility Type Form -->
        <div id="addProductTypeModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <div class="bg-white w-full md:w-1/3 p-6 rounded-md shadow-md ">
                <h2 class="text-xl font-bold mb-4">Add New Facility Type</h2>
                <form class="flex flex-col space-y-4" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" id="productTypeForm">
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
                            <option value="text-xl" selected>XL</option>
                            <option value="text-2xl">2XL</option>
                        </select>
                    </div>

                    <div class="flex justify-end gap-4 select-none">
                        <div id="addProductTypeCancelBtn" class="px-4 py-2 text-amber-500 font-semibold hover:text-amber-600">
                            Cancel
                        </div>
                        <!-- Submit Button -->
                        <button
                            type="submit"
                            name="addfacilitytype"
                            class="bg-amber-500 text-white font-semibold px-4 py-2 rounded select-none hover:bg-amber-600 transition-colors">
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