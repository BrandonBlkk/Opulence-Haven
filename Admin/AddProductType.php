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
$productTypeID = AutoID('producttypetb', 'ProductTypeID', 'PT-', 6);

// Add Product Type
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['addproducttype'])) {
    $producttype = mysqli_real_escape_string($connect, $_POST['producttype']);
    $description = mysqli_real_escape_string($connect, $_POST['description']);

    $addProductTypeQuery = "INSERT INTO producttypetb (ProductTypeID, ProductType, Description)
    VALUES ('$productTypeID', '$producttype', '$description')";

    if (mysqli_query($connect, $addProductTypeQuery)) {
        $addProductTypeSuccess = true;
    } else {
        $alertMessage = "Failed to add product type. Please try again.";
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
    <title>Opulence Haven | Add Supplier</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/3.5.0/remixicon.css" integrity="sha512-HXXR0l2yMwHDrDyxJbrMD9eLvPe3z3qL3PPeozNTsiHJEENxx8DH2CxmV05iwG0dwoz5n4gQZQyYLUNt1Wdgfg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../CSS/output.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../CSS/input.css?v=<?php echo time(); ?>">
</head>

<body>
    <?php include('../includes/AdminNavbar.php'); ?>

    <!-- Main Container -->
    <div class="flex flex-col md:flex-row md:space-x-3 p-3 ml-0 md:ml-[250px]">
        <!-- Left Side Content -->
        <div class="w-full md:w-2/3 bg-white p-2">
            <h2 class="text-xl font-bold mb-4">Add Product Type Overview</h2>
            <p>Add information about product types to categorize items, track stock levels, and manage product details for efficient organization.</p>

            <!-- Supplier Table -->
            <div class="overflow-x-auto">
                <!-- Supplier Search and Filter -->
                <form method="GET" class="my-4 flex items-center justify-between flex-col sm:flex-row gap-2 sm:gap-0">
                    <h1 class="text-lg font-semibold text-nowrap">All Product Type <span class="text-gray-400 text-sm ml-2"><?php echo $productTypeCount ?></span></h1>
                    <div class="flex items-center w-full">
                        <input type="text" name="producttype_search" class="p-2 ml-0 sm:ml-5 border border-gray-300 rounded-md w-full" placeholder="Search for product type..." value="<?php echo isset($_GET['producttype_search']) ? htmlspecialchars($_GET['producttype_search']) : ''; ?>">
                    </div>
                </form>
                <div class="overflow-y-auto max-h-[510px]">
                    <table class="min-w-full bg-white rounded-lg">
                        <thead>
                            <tr class="bg-gray-100 text-gray-600 text-sm">
                                <th class="p-3 text-left">ID</th>
                                <th class="p-3 text-left">Type</th>
                                <th class="p-3 text-center">Description</th>
                                <th class="p-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm">
                            <?php foreach ($productTypes as $productType): ?>
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="p-3 text-left whitespace-nowrap">
                                        <div class="flex items-center gap-2 font-medium text-gray-500">
                                            <input type="checkbox" class="form-checkbox h-3 w-3 border-2 text-amber-500">
                                            <span><?= htmlspecialchars($productType['ProductTypeID']) ?></span>
                                        </div>
                                    </td>
                                    <td class="p-3 text-center">
                                        <?= htmlspecialchars($productType['ProductType']) ?>
                                    </td>
                                    <td class="p-3 text-center">
                                        <?= htmlspecialchars($productType['Description']) ?>
                                    </td>
                                    <td class="p-3 text-center space-x-1 select-none">
                                        <i class="details-btn ri-eye-line text-lg cursor-pointer"
                                            data-producttype-id="<?= htmlspecialchars($productType['ProductTypeID']) ?>"></i>
                                        <button class="text-red-500">
                                            <i class="delete-btn ri-delete-bin-7-line text-xl"
                                                data-producttype-id="<?= htmlspecialchars($productType['ProductTypeID']) ?>"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Product Type Details Modal -->
        <div id="updateProductTypeModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <div class="bg-white max-w-5xl p-6 rounded-md shadow-md text-center w-full sm:max-w-[500px]">
                <h2 class="text-xl font-bold mb-4">Edit Product Type</h2>
                <form class="flex flex-col space-y-4" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" id="updateProductTypeForm">
                    <input type="hidden" name="producttypeid" id="updateProductTypeID">
                    <!-- Product Type Input -->
                    <div class="relative w-full">
                        <label class="block text-sm text-start font-medium text-gray-700 mb-1">Product Type Information</label>
                        <input
                            id="updateProductTypeInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="text"
                            name="updateproducttype"
                            placeholder="Enter product type">
                        <small id="updateProductTypeError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>
                    <!-- Description Input -->
                    <div class="relative w-full">
                        <label class="block text-sm text-start font-medium text-gray-700 mb-1">Description</label>
                        <textarea
                            id="updateProductTypeDescription"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            name="updatedescription"
                            placeholder="Enter product type description"></textarea>
                        <small id="updateProductTypeDescriptionError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>
                    <!-- Submit Button -->
                    <div class="flex justify-end gap-4 select-none">
                        <div id="updateProductTypeModalCancelBtn" class="px-4 py-2 bg-gray-200 text-black hover:bg-gray-300">
                            Cancel
                        </div>
                        <button
                            type="submit"
                            name="editproducttype"
                            class="bg-amber-500 text-white px-4 py-2 select-none hover:bg-amber-600">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Product Type Delete Modal -->
        <div id="productTypeConfirmDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <form class="bg-white max-w-5xl p-6 rounded-md shadow-md text-center" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" id="productTypeDeleteForm">
                <h2 class="text-xl font-semibold text-red-600 mb-4">Confirm Product Type Deletion</h2>
                <p class="text-slate-600 mb-2">You are about to delete the following Product Type: <span id="productTypeDeleteName" class="font-semibold"></span></p>
                <p class="text-sm text-gray-500 mb-4">
                    Deleting this Product Type will permanently remove it from the system, including all associated data.
                </p>
                <input type="hidden" name="producttypeid" id="deleteProductTypeID">
                <div class="flex justify-end gap-4 select-none">
                    <div id="productTypeCancelDeleteBtn" class="px-4 py-2 bg-gray-200 text-black hover:bg-gray-300">
                        Cancel
                    </div>
                    <button
                        type="submit"
                        name="deleteproducttype"
                        class="px-4 py-2 bg-red-600 text-white hover:bg-red-700">
                        Delete
                    </button>
                </div>
            </form>
        </div>

        <!-- Right Side Form -->
        <div class="w-full md:w-1/3 h-full bg-white rounded-lg shadow p-2">
            <h2 class="text-xl font-bold mb-4">Add New Product Type</h2>
            <form class="flex flex-col space-y-4" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" id="productTypeForm">
                <!-- Product Type Input -->
                <div class="relative w-full">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Product Type Information</label>
                    <input
                        id="productTypeInput"
                        class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                        type="text"
                        name="producttype"
                        placeholder="Enter product type">
                    <small id="productTypeError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                </div>

                <!-- Description Input -->
                <div class="relative">
                    <textarea
                        id="descriptionInput"
                        class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                        type="text"
                        name="description"
                        placeholder="Enter product type description"></textarea>
                    <small id="descriptionError" class="absolute left-2 -bottom-1 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    name="addproducttype"
                    class="bg-amber-500 text-white font-semibold px-4 py-2 rounded select-none hover:bg-amber-600 transition-colors">
                    Add Product Type
                </button>
            </form>
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