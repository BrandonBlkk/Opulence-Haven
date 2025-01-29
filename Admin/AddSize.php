<?php
session_start();
include('../config/dbConnection.php');
include('../includes/AutoIDFunc.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

$alertMessage = '';
$addProductSizeSuccess = false;
$updateProductSizeSuccess = false;
$deleteProductSizeSuccess = false;

// Add Product Size
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['addproductsize'])) {
    $size = mysqli_real_escape_string($connect, $_POST['size']);
    $price = mysqli_real_escape_string($connect, $_POST['price']);
    $product = mysqli_real_escape_string($connect, $_POST['product']);

    $addProductSizeQuery = "INSERT INTO sizetb (Size, PriceModifier, ProductID)
    VALUES ('$size', '$price', '$product')";

    if ($connect->query($addProductSizeQuery)) {
        $addProductSizeSuccess = true;
    } else {
        $alertMessage = "Failed to add product size. Please try again.";
    }
}

// Get Product Size Details
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = mysqli_real_escape_string($connect, $_GET['id']);
    $action = $_GET['action'];

    // Build query based on action
    $query = match ($action) {
        'getProductSizeDetails' => "SELECT * FROM sizetb WHERE SizeID = '$id'",
        default => null
    };
    if ($query) {
        $result = $connect->query($query);
        $productsize = $result->fetch_assoc();

        if ($productsize) {
            echo json_encode(['success' => true, 'productsize' => $productsize]);
        } else {
            echo json_encode(['success' => false]);
        }
    }
    exit;
}

// Update Product Size
if (isset($_POST['editproductsize'])) {
    $productsizeid = mysqli_real_escape_string($connect, $_POST['productsizeid']);
    $updateSize = mysqli_real_escape_string($connect, $_POST['updatesize']);
    $updatePrice = mysqli_real_escape_string($connect, $_POST['updateprice']);
    $updateProduct = mysqli_real_escape_string($connect, $_POST['updateproduct']);

    // Update query
    $updateQuery = "UPDATE sizetb SET Size = '$updateSize', PriceModifier = '$updatePrice', ProductID = '$updateProduct' WHERE SizeID = '$productsizeid'";

    if ($connect->query($updateQuery)) {
        $updateProductSizeSuccess = true;
    } else {
        $alertMessage = "Failed to update product size. Please try again.";
    }
}

// Delete Product Size
if (isset($_POST['deleteproductsize'])) {
    $productsizeid = mysqli_real_escape_string($connect, $_POST['productsizeid']);

    // Build query based on action
    $deleteQuery = "DELETE FROM sizetb WHERE SizeID = '$productsizeid'";

    if ($connect->query($deleteQuery)) {
        $deleteProductSizeSuccess = true;
    } else {
        $alertMessage = "Failed to delete product size. Please try again.";
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
                    <h2 class="text-xl text-gray-700 font-bold mb-4">Add Product Size Overview</h2>
                    <p>Add information about product images to showcase items, enhance visual appeal, and provide a better shopping experience for customers.</p>
                </div>
                <button id="addProductSizeBtn" class="bg-amber-500 text-white font-semibold px-3 py-1 rounded select-none hover:bg-amber-600 transition-colors">
                    <i class="ri-add-line text-xl"></i>
                </button>
            </div>

            <!-- Prooduct Image Table -->
            <div class="overflow-x-auto">
                <!-- Product Image Filter -->
                <form method="GET" class="my-4 flex items-start sm:items-center justify-between flex-col sm:flex-row gap-2 sm:gap-0">
                    <h1 class="text-lg text-gray-700 font-semibold text-nowrap">All Product Sizes <span class="text-gray-400 text-sm ml-2"><?php echo $productSizeCount ?></span></h1>
                    <div class="flex items-center w-full">
                        <input type="text" name="size_search" class="p-2 ml-0 sm:ml-5 border border-gray-300 rounded-md w-full" placeholder="Search for product size..." value="<?php echo isset($_GET['size_search']) ? htmlspecialchars($_GET['size_search']) : ''; ?>">
                    </div>
                    <div class="flex items-center">
                        <label for="sort" class="ml-4 mr-2 flex items-center cursor-pointer select-none">
                            <i class="ri-filter-2-line text-xl"></i>
                            <p>Filters</p>
                        </label>
                        <!-- Filter form -->
                        <form method="GET" class="flex flex-col md:flex-row items-center gap-4 mb-4">
                            <select name="sort" id="sort" class="border p-2 rounded text-sm" onchange="this.form.submit()">
                                <option value="random">All Products</option>
                                <?php
                                $select = "SELECT * FROM producttb";
                                $query = $connect->query($select);
                                $count = $query->num_rows;

                                if ($count) {
                                    for ($i = 0; $i < $count; $i++) {
                                        $row = $query->fetch_assoc();
                                        $product_id = $row['ProductID'];
                                        $selected = ($filterImages == $product_id) ? 'selected' : '';

                                        echo "<option value='$product_id' $selected>$product_id</option>";
                                    }
                                } else {
                                    echo "<option value='' disabled>No data yet</option>";
                                }
                                ?>
                            </select>
                        </form>
                    </div>
                </form>
                <div class="tableScrollBar overflow-y-auto max-h-[510px]">
                    <table class="min-w-full bg-white rounded-lg">
                        <thead>
                            <tr class="bg-gray-100 text-gray-600 text-sm">
                                <th class="p-3 text-start">ID</th>
                                <th class="p-3 text-start">Size</th>
                                <th class="p-3 text-start">Price</th>
                                <th class="p-3 text-start">Product</th>
                                <th class="p-3 text-start">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm">
                            <?php
                            $count = 1;
                            ?>
                            <?php foreach ($productSizes as $productSize): ?>
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="p-3 text-start whitespace-nowrap">
                                        <div class="flex items-center gap-2 font-medium text-gray-500">
                                            <input type="checkbox" class="form-checkbox h-3 w-3 border-2 text-amber-500">
                                            <span><?= $count ?></span>
                                        </div>
                                    </td>
                                    <td class="p-3 text-start hidden sm:table-cell">
                                        <?= htmlspecialchars($productSize['Size']) ?>
                                    </td>
                                    <td class="p-3 text-start hidden sm:table-cell">
                                        <?= htmlspecialchars($productSize['PriceModifier']) ?>
                                    </td>
                                    <td class="p-3 text-start hidden sm:table-cell">
                                        <?php
                                        // Fetch the specific product type for the supplier
                                        $productID = $productSize['ProductID'];
                                        $productQuery = "SELECT ProductID, Title FROM producttb WHERE ProductID = '$productID'";
                                        $productResult = mysqli_query($connect, $productQuery);

                                        if ($productResult && $productResult->num_rows > 0) {
                                            $productRow = $productResult->fetch_assoc();
                                            echo htmlspecialchars($productRow['ProductID'] . " (" . $productRow['Title'] . ")");
                                        } else {
                                            echo "Product not found"; // Fallback message
                                        }
                                        ?>
                                    </td>
                                    <td class="p-3 text-start space-x-1 select-none">
                                        <i class="details-btn ri-eye-line text-lg cursor-pointer"
                                            data-productsize-id="<?= htmlspecialchars($productSize['SizeID']) ?>"></i>
                                        <button class="text-red-500">
                                            <i class="delete-btn ri-delete-bin-7-line text-xl"
                                                data-productsize-id="<?= htmlspecialchars($productSize['SizeID']) ?>"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php $count++; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Controls -->
                <div class="flex justify-center items-center mt-1">
                    <?php if ($productTypeCurrentPage > 1) {
                    ?>
                        <a href="?producttypepage=<?= $productTypeCurrentPage - 1 ?>"
                            class="px-3 py-1 mx-1 border rounded <?= $producttypepage == $productTypeCurrentPage ? 'bg-gray-200' : 'bg-white' ?>">
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
                    <?php for ($producttypepage = 1; $producttypepage <= $totalProductTypePages; $producttypepage++): ?>
                        <a href="?producttypepage=<?= $producttypepage ?>&producttype_search=<?= htmlspecialchars($searchProductTypeQuery) ?>"
                            class="px-3 py-1 mx-1 border rounded select-none <?= $producttypepage == $productTypeCurrentPage ? 'bg-gray-200' : 'bg-white' ?>">
                            <?= $producttypepage ?>
                        </a>
                    <?php endfor; ?>
                    <!-- Next Btn -->
                    <?php if ($productTypeCurrentPage < $totalProductTypePages) {
                    ?>
                        <a href="?producttypepage=<?= $productTypeCurrentPage + 1 ?>"
                            class="px-3 py-1 mx-1 border rounded <?= $producttypepage == $productTypeCurrentPage ? 'bg-gray-200' : 'bg-white' ?>">
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

        <!-- Product Type Details Modal -->
        <div id="updateProductSizeModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <div class="bg-white max-w-5xl p-6 rounded-md shadow-md text-center w-full sm:max-w-[500px]">
                <h2 class="text-xl font-bold mb-4">Edit Product Size</h2>
                <form class="flex flex-col space-y-4" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" id="updateProductSizeForm">
                    <input type="hidden" name="productsizeid" id="updateProductSizeID">
                    <!-- Size Input -->
                    <div class="relative w-full">
                        <label class="block text-sm text-start font-medium text-gray-700 mb-1">Product Size Information</label>
                        <input
                            id="updateSizeInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="text"
                            name="updatesize"
                            placeholder="Enter product size">
                        <small id="updateSizeError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>
                    <!-- Price Input -->
                    <div class="relative w-full">
                        <input
                            id="updatePriceModifierInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="text"
                            name="updateprice"
                            placeholder="Enter product price">
                        <small id="updatePriceModifierError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>
                    <!-- Product -->
                    <div class="relative">
                        <select name="updateproduct" class="p-2 w-full border rounded outline-none" required>
                            <option value="" disabled selected>Select product</option>
                            <?php
                            $select = "SELECT * FROM producttb";
                            $query = $connect->query($select);
                            $count = $query->num_rows;

                            if ($count) {
                                for ($i = 0; $i < $count; $i++) {
                                    $row = $query->fetch_assoc();
                                    $product__id = $row['ProductID'];
                                    $title = $row['Title'];

                                    echo "<option value= '$product__id'>$title</option>";
                                }
                            } else {
                                echo "<option value='' disabled>No data yet</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end gap-4 select-none">
                        <div id="updateProductSizeModalCancelBtn" class="px-4 py-2 bg-gray-200 text-black hover:bg-gray-300 rounded-sm">
                            Cancel
                        </div>
                        <button
                            type="submit"
                            name="editproductsize"
                            class="bg-amber-500 text-white px-4 py-2 select-none hover:bg-amber-600 rounded-sm">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Product Type Delete Modal -->
        <div id="productSizeConfirmDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <form class="bg-white max-w-5xl p-6 rounded-md shadow-md text-center" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" id="productSizeDeleteForm">
                <h2 class="text-xl font-semibold text-red-600 mb-4">Confirm Product Size Deletion</h2>
                <p class="text-slate-600 mb-2">You are about to delete the following Product Size: <span id="productSizeDeleteName" class="font-semibold"></span></p>
                <p class="text-sm text-gray-500 mb-4">
                    Deleting this Product Size will permanently remove it from the system, including all associated data.
                </p>
                <input type="hidden" name="productsizeid" id="deleteProductSizeID">
                <div class="flex justify-end gap-4 select-none">
                    <div id="productSizeCancelDeleteBtn" class="px-4 py-2 bg-gray-200 text-black hover:bg-gray-300 rounded-sm">
                        Cancel
                    </div>
                    <button
                        type="submit"
                        name="deleteproductsize"
                        class="px-4 py-2 bg-red-600 text-white hover:bg-red-700 rounded-sm">
                        Delete
                    </button>
                </div>
            </form>
        </div>

        <!-- Add Product Size Form -->
        <div id="addProductSizeModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <div class="bg-white w-full md:w-1/3 p-6 rounded-md shadow-md ">
                <h2 class="text-xl font-bold mb-4">Add New Product Size</h2>
                <form class="flex flex-col space-y-4" action="<?php echo $_SERVER["PHP_SELF"]; ?>" enctype="multipart/form-data" method="post" id="productSizeForm">
                    <!-- Size Input -->
                    <div class="relative w-full">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Product Size Information</label>
                        <input
                            id="sizeInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="text"
                            name="size"
                            placeholder="Enter product size">
                        <small id="sizeError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>
                    <!-- Price Input -->
                    <div class="relative w-full">
                        <input
                            id="priceModifierInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="text"
                            name="price"
                            placeholder="Enter product price modifier">
                        <small id="priceModifierError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>
                    <!-- Product -->
                    <div class="relative">
                        <select name="product" id="product" class="p-2 w-full border rounded outline-none" required>
                            <option value="" disabled selected>Select product</option>
                            <?php
                            $select = "SELECT * FROM producttb";
                            $query = $connect->query($select);
                            $count = $query->num_rows;

                            if ($count) {
                                for ($i = 0; $i < $count; $i++) {
                                    $row = $query->fetch_assoc();
                                    $product__id = $row['ProductID'];
                                    $title = $row['Title'];

                                    echo "<option value= '$product__id'>$title</option>";
                                }
                            } else {
                                echo "<option value='' disabled>No data yet</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="flex justify-end gap-4 select-none">
                        <div id="addProductSizeCancelBtn" class="px-4 py-2 text-amber-500 font-semibold hover:text-amber-600">
                            Cancel
                        </div>
                        <!-- Submit Button -->
                        <button
                            type="submit"
                            name="addproductsize"
                            class="bg-amber-500 text-white font-semibold px-4 py-2 rounded-sm select-none hover:bg-amber-600 transition-colors">
                            Add Size
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