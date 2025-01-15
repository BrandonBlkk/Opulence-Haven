<?php
session_start();
include('../config/dbConnection.php');
include('../includes/AutoIDFunc.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

$alertMessage = '';
$addProductSuccess = false;
$updateProductSuccess = false;
$deleteProductSuccess = false;
$productID = AutoID('producttb', 'ProductID', 'PD-', 6);

// Add Product Type
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['addproduct'])) {
    $productTitle = mysqli_real_escape_string($connect, $_POST['productTitle']);
    $description = mysqli_real_escape_string($connect, $_POST['description']);
    $specification = mysqli_real_escape_string($connect, $_POST['specification']);
    $information = mysqli_real_escape_string($connect, $_POST['information']);
    $delivery = mysqli_real_escape_string($connect, $_POST['delivery']);
    $brand = mysqli_real_escape_string($connect, $_POST['brand']);
    $price = mysqli_real_escape_string($connect, $_POST['price']);
    $discountPrice = mysqli_real_escape_string($connect, $_POST['discountPrice']);
    $productSize = mysqli_real_escape_string($connect, $_POST['productSize']);
    $sellingFast = mysqli_real_escape_string($connect, $_POST['sellingfast']);
    $stock = mysqli_real_escape_string($connect, $_POST['stock']);
    $productType = mysqli_real_escape_string($connect, $_POST['productType']);

    // Product image 1 upload 
    $productImage1 = $_FILES["img1"]["name"];
    $copyFile = "AdminImages/";
    $fileName1 = $copyFile . uniqid() . "_" . $productImage1;
    $copy = copy($_FILES["img1"]["tmp_name"], $fileName1);

    if (!$copy) {
        echo "<p>Cannot upload Product Image 1.</p>";
        exit();
    }
    $userProductImage1 = $_FILES["img1"]["name"];
    $copyFile = "../UserImages/";
    $userFileName1 = $copyFile . uniqid() . "_" . $userProductImage1;
    $copy = copy($_FILES["img1"]["tmp_name"], $userFileName1);

    if (!$copy) {
        echo "<p>Cannot upload Product Image 1.</p>";
        exit();
    }
    // Product image 2 upload 
    $productImage2 = $_FILES["img2"]["name"];
    $copyFile = "AdminImages/";
    $fileName2 = $copyFile . uniqid() . "_" . $productImage2;
    $copy = copy($_FILES["img2"]["tmp_name"], $fileName2);

    if (!$copy) {
        echo "<p>Cannot upload Product Image 2.</p>";
        exit();
    }
    $userProductImage2 = $_FILES["img2"]["name"];
    $copyFile = "../UserImages/";
    $userFileName2 = $copyFile . uniqid() . "_" . $userProductImage2;
    $copy = copy($_FILES["img2"]["tmp_name"], $userFileName2);

    if (!$copy) {
        echo "<p>Cannot upload Product Image 2.</p>";
        exit();
    }
    // Product image 3 upload 
    $productImage3 = $_FILES["img3"]["name"];
    $copyFile = "AdminImages/";
    $fileName3 = $copyFile . uniqid() . "_" . $productImage3;
    $copy = copy($_FILES["img3"]["tmp_name"], $fileName3);

    if (!$copy) {
        echo "<p>Cannot upload Product Image 3.</p>";
        exit();
    }
    $userProductImage3 = $_FILES["img3"]["name"];
    $copyFile = "../UserImages/";
    $userFileName3 = $copyFile . uniqid() . "_" . $userProductImage3;
    $copy = copy($_FILES["img3"]["tmp_name"], $userFileName3);

    if (!$copy) {
        echo "<p>Cannot upload Product Image 3.</p>";
        exit();
    }

    // Check if the product already exists using prepared statement
    $checkQuery = "SELECT Title FROM producttb WHERE Title = '$productTitle'";

    $checkQuery = mysqli_query($connect, $checkQuery);
    $count = mysqli_num_rows($checkQuery);

    if ($count > 0) {
        $alertMessage = 'Product you added is already existed.';
    } else {
        $addProductQuery = "INSERT INTO producttb (ProductID, Title, AdminImg1, AdminImg2, AdminImg3, UserImg1, UserImg2, UserImg3, Price, DiscountPrice, Description, Specification, Information, DeliveryInfo, Brand, ProductSize, SellingFast, Stock, ProductTypeID)
        VALUES ('$productID', '$productTitle', '$fileName1', '$fileName2', '$fileName3', '$userFileName1', '$userFileName2', '$userFileName3', '$price', '$discountPrice', '$description', '$specification', '$information', '$delivery', '$brand', '$productSize', '$sellingFast', '$stock', '$productType')";

        if (mysqli_query($connect, $addProductQuery)) {
            $addProductSuccess = true;
        } else {
            $alertMessage = "Failed to add product. Please try again.";
        }
    }
}

// Get Product Details
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = mysqli_real_escape_string($connect, $_GET['id']);
    $action = $_GET['action'];

    // Build query based on action
    $query = match ($action) {
        'getProductDetails' => "SELECT * FROM producttb WHERE ProductID = '$id'",
        default => null
    };
    if ($query) {
        $result = mysqli_query($connect, $query);
        $product = mysqli_fetch_assoc($result);

        if ($product) {
            echo json_encode(['success' => true, 'product' => $product]);
        } else {
            echo json_encode(['success' => false]);
        }
    }
    exit;
}

// Update Product Type
if (isset($_POST['editproduct'])) {
    $productTypeId = mysqli_real_escape_string($connect, $_POST['producttypeid']);
    $productTitle = mysqli_real_escape_string($connect, $_POST['updateproductTitle']);
    $brand = mysqli_real_escape_string($connect, $_POST['updatebrand']);
    $description = mysqli_real_escape_string($connect, $_POST['updatedescription']);
    $specification = mysqli_real_escape_string($connect, $_POST['updatespecification']);
    $information = mysqli_real_escape_string($connect, $_POST['updateinformation']);
    $delivery = mysqli_real_escape_string($connect, $_POST['updatedelivery']);
    $price = mysqli_real_escape_string($connect, $_POST['updateprice']);
    $discountPrice = mysqli_real_escape_string($connect, $_POST['updatediscountPrice']);
    $productSize = mysqli_real_escape_string($connect, $_POST['updateproductsize']);
    $stock = mysqli_real_escape_string($connect, $_POST['updatestock']);
    $sellingFast = mysqli_real_escape_string($connect, $_POST['updatesellingfast']);
    $productType = mysqli_real_escape_string($connect, $_POST['updateproductType']);

    // Update query
    $updateQuery = "UPDATE producttb SET Title = '$productTitle', Brand = '$brand', Description = '$description', Specification = '$specification', Information = '$information', DeliveryInfo = '$delivery', 
    Price = '$price', DiscountPrice = '$discountPrice', ProductSize = '$productSize', Stock = '$stock', SellingFast = '$sellingFast', ProductTypeID = '$productType' WHERE ProductID = '$productId'";

    if (mysqli_query($connect, $updateQuery)) {
        $updateProductSuccess = true;
    } else {
        $alertMessage = "Failed to update product type. Please try again.";
    }
}

// Delete Product Type
if (isset($_POST['deleteproduct'])) {
    $productId = mysqli_real_escape_string($connect, $_POST['productid']);

    // Build query based on action
    $deleteQuery = "DELETE FROM producttb WHERE ProductID = '$productId'";

    if (mysqli_query($connect, $deleteQuery)) {
        $deleteProductSuccess = true;
    } else {
        $alertMessage = "Failed to delete product. Please try again.";
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
                    <h2 class="text-xl font-bold mb-4">Add Product Overview</h2>
                    <p>Add product information to monitor inventory, track orders, and manage product details for efficient operations.</p>
                </div>
                <button id="addProductBtn" class="bg-amber-500 text-white font-semibold px-3 py-1 rounded select-none hover:bg-amber-600 transition-colors">
                    <i class="ri-add-line text-xl"></i>
                </button>
            </div>

            <!-- Product Table -->
            <div class="overflow-x-auto">
                <!-- Product Search and Filter -->
                <form method="GET" class="my-4 flex items-center justify-between flex-col sm:flex-row gap-2 sm:gap-0">
                    <h1 class="text-lg font-semibold text-nowrap">All Product <span class="text-gray-400 text-sm ml-2"><?php echo $productCount ?></span></h1>
                    <div class="flex items-center w-full">
                        <input type="text" name="product_search" class="p-2 ml-0 sm:ml-5 border border-gray-300 rounded-md w-full" placeholder="Search for product..." value="<?php echo isset($_GET['product_search']) ? htmlspecialchars($_GET['product_search']) : ''; ?>">
                        <div class="flex items-center">
                            <label for="sort" class="ml-4 mr-2 flex items-center cursor-pointer select-none">
                                <i class="ri-filter-2-line text-xl"></i>
                                <p>Filters</p>
                            </label>
                            <!-- Search and filter form -->
                            <form method="GET" class="flex flex-col md:flex-row items-center gap-4 mb-4">
                                <select name="sort" id="sort" class="border p-2 rounded text-sm" onchange="this.form.submit()">
                                    <option value="random">All Product Types</option>
                                    <?php
                                    $select = "SELECT * FROM producttypetb";
                                    $query = mysqli_query($connect, $select);
                                    $count = mysqli_num_rows($query);

                                    if ($count) {
                                        for ($i = 0; $i < $count; $i++) {
                                            $row = mysqli_fetch_array($query);
                                            $producttype_id = $row['ProductTypeID'];
                                            $producttype = $row['ProductType'];
                                            $selected = ($filterProductTypeID == $producttype_id) ? 'selected' : '';

                                            echo "<option value='$producttype_id' $selected>$producttype</option>";
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
                <div class="overflow-y-auto max-h-[510px]">
                    <table class="min-w-full bg-white rounded-lg">
                        <thead>
                            <tr class="bg-gray-100 text-gray-600 text-sm">
                                <th class="p-3 text-start">ID</th>
                                <th class="p-3 text-start">Title</th>
                                <th class="p-3 text-start">Image</th>
                                <th class="p-3 text-start">Price</th>
                                <th class="p-3 text-start">Stock</th>
                                <th class="p-3 text-start">Added Date</th>
                                <th class="p-3 text-start">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm">
                            <?php foreach ($products as $product): ?>
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="p-3 text-start whitespace-nowrap">
                                        <div class="flex items-center gap-2 font-medium text-gray-500">
                                            <input type="checkbox" class="form-checkbox h-3 w-3 border-2 text-amber-500">
                                            <span><?= htmlspecialchars($product['ProductID']) ?></span>
                                        </div>
                                    </td>
                                    <td class="p-3 text-start">
                                        <?= htmlspecialchars($product['Title']) ?>
                                    </td>
                                    <td class="p-3 text-start select-none">
                                        <img src="<?= htmlspecialchars($product['AdminImg1']) ?>" alt="Product Image" class="w-12 h-12 object-cover rounded-sm">
                                    </td>
                                    <td class="p-3 text-start">
                                        $<?= htmlspecialchars(number_format($product['Price'], 2)) ?>
                                    </td>
                                    <td class="p-3 text-start">
                                        <?= htmlspecialchars($product['Stock']) ?>
                                    </td>
                                    <td class="p-3 text-start">
                                        <?= htmlspecialchars(date('Y-m-d', strtotime($product['AddedDate']))) ?>
                                    </td>
                                    <td class="p-3 text-start space-x-1 select-none">
                                        <i class="details-btn ri-eye-line text-lg cursor-pointer"
                                            data-product-id="<?= htmlspecialchars($product['ProductID']) ?>"></i>
                                        <button class="text-red-500">
                                            <i class="delete-btn ri-delete-bin-7-line text-xl"
                                                data-product-id="<?= htmlspecialchars($product['ProductID']) ?>"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Pagination Controls -->
                    <!-- <div class="flex justify-center items-center mt-1">
                        <?php for ($page = 1; $page <= $totalPages; $page++): ?>
                            <a href="?page=<?= $page ?>&sort=<?= htmlspecialchars($filterRoleID) ?>&acc_search=<?= htmlspecialchars($searchAdminQuery) ?>"
                                class="px-3 py-1 mx-1 border rounded <?= $page == $currentPage ? 'bg-gray-200' : 'bg-white' ?>">
                                <?= $page ?>
                            </a>
                        <?php endfor; ?>
                    </div> -->
                </div>
            </div>
        </div>

        <!-- Product Details Modal -->
        <div id="updateProductModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <div class="bg-white w-full max-w-4xl p-6 rounded-md shadow-md mx-4 sm:mx-8 lg:mx-auto overflow-y-auto max-h-[92vh]">
                <h2 class="text-xl text-center font-bold mb-4">Edit Product</h2>
                <form class="flex flex-col space-y-4" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" enctype="multipart/form-data" id="updateProductForm">
                    <input type="hidden" name="producttypeid" id="updateProductID">
                    <!-- Product Title Input -->
                    <div class="relative w-full">
                        <label class="block text-sm text-start font-medium text-gray-700 mb-1">Product Information</label>
                        <input
                            id="updateProductTitleInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="text"
                            name="updateproductTitle"
                            placeholder="Enter product title">
                        <small id="updateProductTitleError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-100 transition-all duration-200 select-none"></small>
                    </div>
                    <!-- Brand -->
                    <div class="relative">
                        <input
                            id="updateBrandInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            name="updatebrand"
                            placeholder="Enter product brand">
                        <small id="updateBrandError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-100 transition-all duration-200 select-none"></small>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-4 sm:gap-2">
                        <!-- Description -->
                        <div class="relative flex-1">
                            <textarea
                                id="updateDescriptionInput"
                                class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                name="updatedescription"
                                placeholder="Enter product description"></textarea>
                            <small id="updateDescriptionError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-100 transition-all duration-200 select-none"></small>
                        </div>
                        <!-- Specification -->
                        <div class="relative flex-1">
                            <textarea
                                id="updateSpecificationInput"
                                class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                name="updatespecification"
                                placeholder="Enter product specification"></textarea>
                            <small id="updateSpecificationError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-100 transition-all duration-200 select-none"></small>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-4 sm:gap-2">
                        <!-- Information -->
                        <div class="relative flex-1">
                            <textarea
                                id="updateInformationInput"
                                class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                name="updateinformation"
                                placeholder="Enter product information"></textarea>
                            <small id="updateInformationError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-100 transition-all duration-200 select-none"></small>
                        </div>
                        <!-- DeliveryInfo -->
                        <div class="relative flex-1">
                            <textarea
                                id="updateDeliveryInput"
                                class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                name="updatedelivery"
                                placeholder="Enter delivery information"></textarea>
                            <small id="updateDeliveryError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-100 transition-all duration-200 select-none"></small>
                        </div>
                    </div>
                    <!-- Image Uploads -->
                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Product Images</label>
                        <div class="flex flex-col flex-wrap sm:flex-row gap-4 sm:gap-2">
                            <div>
                                <div class="w-20 h-20">
                                    <img id="updateimg1" src="" alt="Current Image 1" class="w-full h-full object-cover">
                                </div>
                                <input type="file" name="updateimg1" class="mb-2">
                            </div>
                            <div>
                                <div class="w-20 h-20">
                                    <img id="updateimg2" src="" alt="Current Image 2" class="w-full h-full object-cover">
                                </div>
                                <input type="file" name="updateimg2" class="mb-2">
                            </div>
                            <div>
                                <div class="w-20 h-20">
                                    <img id="updateimg3" src="" alt="Current Image 3" class="w-full h-full object-cover">
                                </div>
                                <input type="file" name="updateimg3">
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-4 sm:gap-2">
                        <!-- Price -->
                        <div class="relative w-full">
                            <input
                                id="updatePriceInput"
                                type="number"
                                step="0.01"
                                name="updateprice"
                                class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                placeholder="Enter product price">
                            <small id="updatePriceError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-100 transition-all duration-200 select-none"></small>
                        </div>
                        <!-- Discount Price -->
                        <div class="relative w-full">
                            <input
                                id="updateDiscountPriceInput"
                                type="number"
                                step="0.01"
                                name="updatediscountPrice"
                                class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                placeholder="Enter discount price">
                            <small id="updateDiscountPriceError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-100 transition-all duration-200 select-none"></small>
                        </div>
                        <!-- Product Size -->
                        <div class="relative w-full">
                            <input
                                id="updateProductSizeInput"
                                type="text"
                                name="updateproductsize"
                                class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                placeholder="Enter product size">
                            <small id="updateProductSizeError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-100 transition-all duration-200 select-none"></small>
                        </div>
                        <!-- Stock -->
                        <div class="relative w-full">
                            <input
                                id="updateStockInput"
                                type="number"
                                name="updatestock"
                                class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                placeholder="Enter stock quantity">
                            <small id="updateStockError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-100 transition-all duration-200 select-none"></small>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-4 sm:gap-2">
                        <!-- Selling Fast -->
                        <div class="relative flex-1">
                            <select name="updatesellingfast" id="updatesellingfast" class="p-2 w-full border rounded">
                                <option value="">Selling Fast</option>
                                <option value="true" <?php echo $product['SellingFast'] == 'true' ? 'selected' : ''; ?>>True</option>
                                <option value="false" <?php echo $product['SellingFast'] == 'false' ? 'selected' : ''; ?>>False</option>
                            </select>
                        </div>
                        <!-- Product Type -->
                        <div class="relative flex-1">
                            <select name="updateproductType" id="updateproductType" class="p-2 w-full border rounded">
                                <option value="" disabled selected>Select type of products</option>
                                <?php
                                $select = "SELECT * FROM producttypetb";
                                $query = mysqli_query($connect, $select);
                                $count = mysqli_num_rows($query);

                                if ($count) {
                                    for ($i = 0; $i < $count; $i++) {
                                        $row = mysqli_fetch_array($query);
                                        $product_type_id = $row['ProductTypeID'];
                                        $product_type = $row['ProductType'];

                                        echo "<option value= '$product_type_id'>$product_type</option>";
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
                        <div id="updateProductModalCancelBtn" class="px-4 py-2 bg-gray-200 text-black hover:bg-gray-300">
                            Cancel
                        </div>
                        <button
                            type="submit"
                            name="editproduct"
                            class="bg-amber-500 text-white px-4 py-2 select-none hover:bg-amber-600">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Product Delete Modal -->
        <div id="productConfirmDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <form class="bg-white max-w-5xl p-6 rounded-md shadow-md text-center" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" id="productDeleteForm">
                <h2 class="text-xl font-semibold text-red-600 mb-4">Confirm Product Deletion</h2>
                <p class="text-slate-600 mb-2">You are about to delete the following Product: <span id="productDeleteName" class="font-semibold"></span></p>
                <p class="text-sm text-gray-500 mb-4">
                    Deleting this Product will permanently remove it from the system, including all associated data.
                </p>
                <input type="hidden" name="productid" id="deleteProductID">
                <div class="flex justify-end gap-4 select-none">
                    <div id="productCancelDeleteBtn" class="px-4 py-2 bg-gray-200 text-black hover:bg-gray-300">
                        Cancel
                    </div>
                    <button
                        type="submit"
                        name="deleteproduct"
                        class="px-4 py-2 bg-red-600 text-white hover:bg-red-700">
                        Delete
                    </button>
                </div>
            </form>
        </div>

        <!-- Add Produc Form -->
        <div id="addProductModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <div class="bg-white w-full max-w-4xl p-6 rounded-md shadow-md mx-4 sm:mx-8 lg:mx-auto overflow-y-auto max-h-[92vh]">
                <h2 class="text-xl font-bold mb-4 text-center">Add New Product</h2>
                <form class="flex flex-col space-y-4" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data" id="productForm">
                    <!-- Product Title Input -->
                    <div class="relative w-full">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Product Information</label>
                        <input
                            id="productTitleInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="text"
                            name="productTitle"
                            placeholder="Enter product title">
                        <small id="productTitleError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-100 transition-all duration-200 select-none"></small>
                    </div>
                    <!-- Brand -->
                    <div class="relative">
                        <input
                            id="brandInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            name="brand"
                            placeholder="Enter product brand">
                        <small id="brandError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-100 transition-all duration-200 select-none"></small>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-4 sm:gap-2">
                        <!-- Description -->
                        <div class="relative flex-1">
                            <textarea
                                id="productDescriptionInput"
                                class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                name="description"
                                placeholder="Enter product description"></textarea>
                            <small id="productDescriptionError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-100 transition-all duration-200 select-none"></small>
                        </div>
                        <!-- Specification -->
                        <div class="relative flex-1">
                            <textarea
                                id="specificationInput"
                                class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                name="specification"
                                placeholder="Enter product specification"></textarea>
                            <small id="specificationError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-100 transition-all duration-200 select-none"></small>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-4 sm:gap-2">
                        <!-- Information -->
                        <div class="relative flex-1">
                            <textarea
                                id="informationInput"
                                class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                name="information"
                                placeholder="Enter product information"></textarea>
                            <small id="informationError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-100 transition-all duration-200 select-none"></small>
                        </div>
                        <!-- DeliveryInfo -->
                        <div class="relative flex-1">
                            <textarea
                                id="deliveryInput"
                                class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                name="delivery"
                                placeholder="Enter delivery information"></textarea>
                            <small id="deliveryError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-100 transition-all duration-200 select-none"></small>
                        </div>
                    </div>
                    <!-- Image Uploads -->
                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Product Images</label>
                        <input type="file" name="img1" class="mb-2" required>
                        <input type="file" name="img2" class="mb-2" required>
                        <input type="file" name="img3" required>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-4 sm:gap-2">
                        <!-- Price -->
                        <div class="relative fl1">
                            <input
                                id="priceInput"
                                type="number"
                                step="0.01"
                                name="price"
                                class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                placeholder="Enter product price">
                            <small id="priceError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-100 transition-all duration-200 select-none"></small>
                        </div>
                        <!-- Discount Price -->
                        <div class="relative flex-1">
                            <input
                                id="discountPriceInput"
                                type="number"
                                step="0.01"
                                name="discountPrice"
                                class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                placeholder="Enter discount price">
                            <small id="discountPriceError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-100 transition-all duration-200 select-none"></small>
                        </div>
                        <!-- Product Size -->
                        <div class="relative flex-1">
                            <input
                                id="productSizeInput"
                                type="text"
                                name="productSize"
                                class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                placeholder="Enter product size">
                            <small id="productSizeError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-100 transition-all duration-200 select-none"></small>
                        </div>
                        <!-- Stock -->
                        <div class="relative flex-1">
                            <input
                                id="stockInput"
                                type="number"
                                name="stock"
                                class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                placeholder="Enter stock quantity">
                            <small id="stockError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-100 transition-all duration-200 select-none"></small>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-4 sm:gap-2">
                        <!-- Selling Fast -->
                        <div class="relative flex-1">
                            <select name="sellingfast" id="sellingfast" class="p-2 w-full border rounded" required>
                                <option value="" disabled selected>Selling Fast</option>
                                <option value="true">True</option>
                                <option value="false">False</option>
                            </select>
                        </div>
                        <!-- Product Type -->
                        <div class="relative flex-1">
                            <select name="productType" id="productType" class="p-2 w-full border rounded" required>
                                <option value="" disabled selected>Select type of products</option>
                                <?php
                                $select = "SELECT * FROM producttypetb";
                                $query = mysqli_query($connect, $select);
                                $count = mysqli_num_rows($query);
                                if ($count) {
                                    for ($i = 0; $i < $count; $i++) {
                                        $row = mysqli_fetch_array($query);
                                        $product_type_id = $row['ProductTypeID'];
                                        $product_type = $row['ProductType'];
                                        echo "<option value='$product_type_id'>$product_type</option>";
                                    }
                                } else {
                                    echo "<option value='' disabled>No data yet</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end gap-4 select-none">
                        <div id="addProductCancelBtn" class="px-4 py-2 text-amber-500 font-semibold hover:text-amber-600">
                            Cancel
                        </div>
                        <!-- Submit Button -->
                        <button
                            type="submit"
                            name="addproduct"
                            class="bg-amber-500 text-white font-semibold px-4 py-2 rounded select-none hover:bg-amber-600 transition-colors">
                            Add Product
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