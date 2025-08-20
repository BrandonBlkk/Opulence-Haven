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
$productTypeID = AutoID('producttypetb', 'ProductTypeID', 'PT-', 6);

// Add Product Type
$response = ['success' => false, 'message' => '', 'generatedId' => $productTypeID];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['addproducttype'])) {
    $producttype = mysqli_real_escape_string($connect, $_POST['producttype']);
    $description = mysqli_real_escape_string($connect, $_POST['description']);

    // Check if the product type already exists using prepared statement
    $checkQuery = "SELECT ProductType FROM producttypetb WHERE ProductType = '$producttype'";
    $count = $connect->query($checkQuery)->num_rows;

    if ($count > 0) {
        $response['message'] = 'Product type you added is already existed.';
    } else {
        $addProductTypeQuery = "INSERT INTO producttypetb (ProductTypeID, ProductType, Description)
        VALUES ('$productTypeID', '$producttype', '$description')";

        if ($connect->query($addProductTypeQuery)) {
            $response['success'] = true;
            $response['message'] = 'A new product type has been successfully added.';
            // Keep the generated ID in the response
            $response['generatedId'] = $productTypeID;
        } else {
            $response['message'] = "Failed to add product type. Please try again.";
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Get Product Type Details
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = mysqli_real_escape_string($connect, $_GET['id']);
    $action = $_GET['action'];

    $query = match ($action) {
        'getProductTypeDetails' => "SELECT * FROM producttypetb WHERE ProductTypeID = '$id'",
        default => null
    };

    if ($query) {
        $producttype = $connect->query($query)->fetch_assoc();

        if ($producttype) {
            $response['success'] = true;
            $response['producttype'] = $producttype;
        } else {
            $response['success'] = true;
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Update Product Type
if (isset($_POST['editproducttype'])) {
    $productTypeId = mysqli_real_escape_string($connect, $_POST['producttypeid']);
    $updatedProductType = mysqli_real_escape_string($connect, $_POST['updateproducttype']);
    $updatedDescription = mysqli_real_escape_string($connect, $_POST['updatedescription']);

    $response = ['success' => false];

    $updateQuery = "UPDATE producttypetb SET ProductType = '$updatedProductType', Description = '$updatedDescription' WHERE ProductTypeID = '$productTypeId'";

    if ($connect->query($updateQuery)) {
        $response['success'] = true;
        $response['message'] = 'The product type has been successfully updated.';
        $response['generatedId'] = $productTypeId;
        $response['updatedProductType'] = $updatedProductType;
        $response['updatedDescription'] = $updatedDescription;
    } else {
        $response['message'] = "Failed to update product type. Please try again.";
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Delete Product Type
if (isset($_POST['deleteproducttype'])) {
    $productTypeId = mysqli_real_escape_string($connect, $_POST['producttypeid']);

    // Build query based on action
    $deleteQuery = "DELETE FROM producttypetb WHERE ProductTypeID = '$productTypeId'";

    if ($connect->query($deleteQuery)) {
        $response['success'] = true;
        $response['generatedId'] = $productTypeId;
    } else {
        $response['success'] = false;
        $response['message'] = 'Failed to delete product type. Please try again.';
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
                    <h2 class="text-xl text-gray-700 font-bold mb-4">Add Product Type Overview</h2>
                    <p>Add information about product types to categorize items, track stock levels, and manage product details for efficient organization.</p>
                </div>
                <button id="addProductTypeBtn" class="bg-amber-500 text-white font-semibold px-3 py-1 rounded select-none hover:bg-amber-600 transition-colors">
                    <i class="ri-add-line text-xl"></i>
                </button>
            </div>

            <!-- Prooduct Type Table -->
            <div class="overflow-x-auto">
                <!-- Product Type Search and Filter -->
                <form method="GET" class="my-4 flex items-start sm:items-center justify-between flex-col sm:flex-row gap-2 sm:gap-0">
                    <h1 class="text-lg text-gray-700 font-semibold text-nowrap">All Product Types <span class="text-gray-400 text-sm ml-2"><?php echo $productTypeCount ?></span></h1>
                    <div class="flex items-center w-full">
                        <input type="text" name="producttype_search" class="p-2 ml-0 sm:ml-5 border border-gray-300 rounded-md w-full outline-none focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out" placeholder="Search for product type..." value="<?php echo isset($_GET['producttype_search']) ? htmlspecialchars($_GET['producttype_search']) : ''; ?>">
                    </div>
                </form>

                <!-- Product Type Table -->
                <div class="tableScrollBar overflow-y-auto max-h-[495px]">
                    <div id="productTypeResults">
                        <?php include '../includes/admin_table_components/producttype_results.php'; ?>
                    </div>
                </div>

                <!-- Pagination Controls -->
                <div id="paginationContainer" class="flex justify-between items-center mt-3">
                    <?php include '../includes/admin_table_components/producttype_pagination.php'; ?>
                </div>
            </div>
        </div>

        <!-- Product Type Details Modal -->
        <div id="updateProductTypeModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <div class="bg-white max-w-5xl p-6 rounded-md shadow-md text-center w-full sm:max-w-[500px]">
                <h2 class="text-xl text-start text-gray-700 font-bold">Edit Product Type</h2>
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
                        <small id="updateProductTypeDescriptionError" class="absolute left-2 -bottom-1 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>
                    <!-- Submit Button -->
                    <div class="flex justify-end gap-4 select-none">
                        <div id="updateProductTypeModalCancelBtn" class="px-4 py-2 bg-gray-200 text-black hover:bg-gray-300 rounded-sm">
                            Cancel
                        </div>
                        <button
                            type="submit"
                            name="editproducttype"
                            class="bg-amber-500 text-white px-4 py-2 select-none hover:bg-amber-600 rounded-sm">
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
                    <div id="productTypeCancelDeleteBtn" class="px-4 py-2 bg-gray-200 text-black hover:bg-gray-300 rounded-sm">
                        Cancel
                    </div>
                    <button
                        type="submit"
                        name="deleteproducttype"
                        class="px-4 py-2 bg-red-600 text-white hover:bg-red-700 rounded-sm">
                        Delete
                    </button>
                </div>
            </form>
        </div>

        <!-- Add Product Type Form -->
        <div id="addProductTypeModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <div class="bg-white w-full md:w-1/3 p-6 rounded-md shadow-md ">
                <h2 class="text-xl text-gray-700 font-bold mb-4">Add New Product Type</h2>
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

                    <div class="flex justify-end gap-4 select-none">
                        <div id="addProductTypeCancelBtn" class="px-4 py-2 text-amber-500 font-semibold hover:text-amber-600">
                            Cancel
                        </div>
                        <!-- Submit Button -->
                        <button
                            type="submit"
                            name="addproducttype"
                            class="bg-amber-500 text-white font-semibold px-4 py-2 rounded-sm select-none hover:bg-amber-600 transition-colors">
                            Add Product Type
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