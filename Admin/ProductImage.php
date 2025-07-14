<?php
session_start();
include('../config/dbConnection.php');
include('../includes/auto_id_func.php');
include('../includes/update_image_func.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

$alertMessage = '';
$addProductImageSuccess = false;
$updateProductImageSuccess = false;
$deleteProductImageSuccess = false;

// Add Product Image
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['addproductimage'])) {
    $imagealt = mysqli_real_escape_string($connect, $_POST['imagealt']);
    $product = mysqli_real_escape_string($connect, $_POST['product']);
    $primary = mysqli_real_escape_string($connect, $_POST['primary']);
    $secondary = mysqli_real_escape_string($connect, $_POST['secondary']);

    // Product image upload 
    $productImage = $_FILES["image"]["name"];
    $copyFile = "AdminImages/";
    $fileName = $copyFile . uniqid() . "_" . $productImage;
    $copy = copy($_FILES["image"]["tmp_name"], $fileName);

    if (!$copy) {
        echo "<p>Cannot upload Product Image.</p>";
        exit();
    }
    $userProductImage = $_FILES["image"]["name"];
    $copyFile = "../UserImages/";
    $userFileName = $copyFile . uniqid() . "_" . $userProductImage;
    $copy = copy($_FILES["image"]["tmp_name"], $userFileName);

    if (!$copy) {
        echo "<p>Cannot upload Product Image.</p>";
        exit();
    }

    $addProductImageQuery = "INSERT INTO productimagetb (ImageAdminPath, ImageUserPath, ImageAlt, PrimaryImage, SecondaryImage, ProductID)
    VALUES ('$fileName', '$userFileName', '$imagealt', '$primary', '$secondary', '$product')";

    if ($connect->query($addProductImageQuery)) {
        $addProductImageSuccess = true;
    } else {
        $alertMessage = "Failed to add product image. Please try again.";
    }
}

// Get Product Image Details
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = mysqli_real_escape_string($connect, $_GET['id']);
    $action = $_GET['action'];

    // Build query based on action
    $query = match ($action) {
        'getProductImageDetails' => "SELECT * FROM productimagetb WHERE ImageID = '$id'",
        default => null
    };
    if ($query) {
        $result = $connect->query($query);
        $productimage = $result->fetch_assoc();

        if ($productimage) {
            echo json_encode(['success' => true, 'productimage' => $productimage]);
        } else {
            echo json_encode(['success' => false]);
        }
    }
    exit;
}

// Update Product Image
if (isset($_POST['editproductimage'])) {
    $productImageID = mysqli_real_escape_string($connect, $_POST['productimageid']);
    $updateImageAlt = mysqli_real_escape_string($connect, $_POST['updateimagealt']);
    $updatePrimary = mysqli_real_escape_string($connect, $_POST['updateprimary']);
    $updateSecondary = mysqli_real_escape_string($connect, $_POST['updatesecondary']);
    $updateProduct = mysqli_real_escape_string($connect, $_POST['updateproduct']);

    // Fetch current product image
    $productImageQuery = "SELECT ImageAdminPath FROM productimagetb WHERE ImageID = '$productImageID'";
    $productImageRow = $connect->query($productImageQuery)->fetch_assoc();
    $currentProductImage = $productImageRow['ImageAdminPath'];

    // Simulate $_FILES array for images
    $imageFile = $_FILES['updateimage'];

    // Change Product Image
    $result = uploadProductImage($imageFile, $currentProductImage);

    if (isset($result['image'])) {
        echo $result['image'] . "<br>";
    } else {
        $adminImagePath = $result['adminPath'];
        $userImagePath = $result['userPath'];

        // Update database with both image paths
        $updateQuery = "UPDATE productimagetb 
                        SET ImageAdminPath = '$adminImagePath', ImageUserPath = '$userImagePath', ImageAlt = '$updateImageAlt',
                            PrimaryImage = '$updatePrimary', SecondaryImage = '$updateSecondary', ProductID = '$updateProduct'
                        WHERE ImageID = '$productImageID'";

        if ($connect->query($updateQuery)) {
            $updateProductImageSuccess = true;
        } else {
            $alertMessage = "Failed to update product image. Please try again.";
        }
    }
}

// Delete Product Image
if (isset($_POST['deleteproductimage'])) {
    $productImageId = mysqli_real_escape_string($connect, $_POST['productimageid']);

    // Build query based on action
    $deleteQuery = "DELETE FROM productimagetb WHERE ImageID = '$productImageId'";

    if ($connect->query($deleteQuery)) {
        $deleteProductImageSuccess = true;
    } else {
        $alertMessage = "Failed to delete product image. Please try again.";
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
    <?php include('../includes/admin_navbar.php'); ?>

    <!-- Main Container -->
    <div class="flex flex-col md:flex-row md:space-x-3 p-3 ml-0 md:ml-[250px] min-w-[380px]">
        <!-- Left Side Content -->
        <div class="w-full bg-white p-2">
            <div class="flex justify-between items-end">
                <div>
                    <h2 class="text-xl text-gray-700 font-bold mb-4">Add Product Image Overview</h2>
                    <p>Add information about product images to showcase items, enhance visual appeal, and provide a better shopping experience for customers.</p>
                </div>
                <button id="addProductImageBtn" class="bg-amber-500 text-white font-semibold px-3 py-1 rounded select-none hover:bg-amber-600 transition-colors">
                    <i class="ri-add-line text-xl"></i>
                </button>
            </div>

            <!-- Product Image Table -->
            <div class="overflow-x-auto">
                <!-- Product Image Filter -->
                <form method="GET" class="my-4 flex items-start sm:items-center justify-between flex-col sm:flex-row gap-2 sm:gap-0">
                    <h1 class="text-lg text-gray-700 font-semibold text-nowrap">All Product Images <span class="text-gray-400 text-sm ml-2"><?php echo $productImageCount ?></span></h1>
                    <div class="flex items-center">
                        <label for="sort" class="ml-4 mr-2 flex items-center cursor-pointer select-none">
                            <i class="ri-filter-2-line text-xl"></i>
                            <p>Filters</p>
                        </label>
                        <!-- Filter form -->
                        <form method="GET" class="flex flex-col md:flex-row items-center gap-4 mb-4">
                            <select name="sort" id="sort" class="border p-2 rounded text-sm" onchange="this.form.submit()">
                                <option value="random">All Images</option>
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
                                <th class="p-3 text-start">No</th>
                                <th class="p-3 text-start hidden sm:table-cell">Product</th>
                                <th class="p-3 text-start">Image Path</th>
                                <th class="p-3 text-start hidden sm:table-cell">Image Alt</th>
                                <th class="p-3 text-start">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm">
                            <?php
                            $count = 1;
                            ?>
                            <?php foreach ($productImages as $productImage): ?>
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="p-3 text-start whitespace-nowrap">
                                        <div class="flex items-center gap-2 font-medium text-gray-500">
                                            <input type="checkbox" class="form-checkbox h-3 w-3 border-2 text-amber-500">
                                            <span><?= $count ?></span>
                                        </div>
                                    </td>
                                    <td class="p-3 text-start hidden sm:table-cell">
                                        <?php
                                        // Fetch the specific product for the image
                                        $productID = $productImage['ProductID'];
                                        $productQuery = "SELECT ProductID, Title FROM producttb WHERE ProductID = '$productID'";
                                        $productResult = mysqli_query($connect, $productQuery);

                                        if ($productResult && $productResult->num_rows > 0) {
                                            $productRow = $productResult->fetch_assoc();
                                            echo htmlspecialchars($productRow['ProductID'] . " (" . $productRow['Title'] . ")");
                                        } else {
                                            echo "Product not found";
                                        }
                                        ?>
                                    </td>

                                    <td class="p-3 text-start select-none">
                                        <img src="<?= htmlspecialchars($productImage['ImageAdminPath']) ?>" alt="Product Image" class="w-12 h-12 object-cover rounded-sm">
                                    </td>
                                    <td class="p-3 text-start hidden sm:table-cell">
                                        <?= htmlspecialchars($productImage['ImageAlt']) ?>
                                    </td>
                                    <td class="p-3 text-start space-x-1 select-none">
                                        <i class="details-btn ri-eye-line text-lg cursor-pointer"
                                            data-productimage-id="<?= htmlspecialchars($productImage['ImageID']) ?>"></i>
                                        <button class="text-red-500">
                                            <i class="delete-btn ri-delete-bin-7-line text-xl"
                                                data-productimage-id="<?= htmlspecialchars($productImage['ImageID']) ?>"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php $count++; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Controls -->
                <div class="flex justify-center items-center mt-1 <?= (!empty($productImages) ? 'flex' : 'hidden') ?>">
                    <!-- Previous Btn -->
                    <?php if ($productImageCurrentPage > 1) {
                    ?>
                        <a href="?productimagepage=<?= $productImageCurrentPage - 1 ?>&sort=<?= htmlspecialchars($filterImages) ?>"
                            class="px-3 py-1 mx-1 border rounded <?= $productimagepage == $productImageCurrentPage ? 'bg-gray-200' : 'bg-white' ?>">
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
                    <?php for ($productimagepage = 1; $productimagepage <= $totalProductImagePages; $productimagepage++): ?>
                        <a href="?productimagepage=<?= $productimagepage ?>&sort=<?= htmlspecialchars($filterImages) ?>"
                            class="px-3 py-1 mx-1 border rounded select-none <?= $productimagepage == $productImageCurrentPage ? 'bg-gray-200' : 'bg-white' ?>">
                            <?= $productimagepage ?>
                        </a>
                    <?php endfor; ?>
                    <!-- Next Btn -->
                    <?php if ($productImageCurrentPage < $totalProductImagePages) {
                    ?>
                        <a href="?productimagepage=<?= $productImageCurrentPage + 1 ?>&sort=<?= htmlspecialchars($filterImages) ?>"
                            class="px-3 py-1 mx-1 border rounded <?= $productimagepage == $productImageCurrentPage ? 'bg-gray-200' : 'bg-white' ?>">
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

        <!-- Product Image Details Modal -->
        <div id="updateProductImageModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <div class="bg-white max-w-5xl p-6 rounded-md shadow-md text-center w-full sm:max-w-[500px]">
                <h2 class="text-xl text-start text-gray-700 font-bold">Edit Product Image</h2>
                <form class="flex flex-col space-y-4" action="<?php echo $_SERVER["PHP_SELF"]; ?>" enctype="multipart/form-data" method="post" id="updateProductImageForm">
                    <input type="hidden" name="productimageid" id="updateProductImageID">
                    <!-- Image Input -->
                    <div class="relative w-full">
                        <label class="block text-sm text-start font-medium text-gray-700 mb-1">Product Image Information</label>
                        <div class="w-full h-56 select-none">
                            <img id="updateimagepath" class="w-full h-full object-cover" src="">
                        </div>
                        <input
                            id="updateProductImageInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="file"
                            name="updateimage">
                        <small id="updateProductImageError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>
                    <!-- Image Path Input -->
                    <div class="relative w-full">
                        <input
                            id="updateProductImageAltInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="text"
                            name="updateimagealt"
                            placeholder="Enter image alt">
                        <small id="updateProductImageAltError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>
                    <!-- Product -->
                    <div class="relative">
                        <select name="updateproduct" id="updateProduct" class="p-2 w-full border rounded">
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
                    <div class="flex flex-col sm:flex-row gap-4 sm:gap-2">
                        <!-- Primary Image -->
                        <div class="relative flex-1">
                            <label class="block text-sm text-start font-medium text-gray-700 mb-1">Primary (Optional)</label>
                            <select name="updateprimary" id="updatePrimary" class="p-2 w-full border rounded" required>
                                <option value="1">True</option>
                                <option value="0" selected>False (default)</option>
                            </select>
                        </div>
                        <!-- Secondary Image -->
                        <div class="relative flex-1">
                            <label class="block text-sm text-start font-medium text-gray-700 mb-1">Secondary (Optional)</label>
                            <select name="updatesecondary" id="updateSecondary" class="p-2 w-full border rounded" required>
                                <option value="1">True</option>
                                <option value="0" selected>False (default)</option>
                            </select>
                        </div>
                    </div>
                    <!-- Submit Button -->
                    <div class="flex justify-end gap-4 select-none">
                        <div id="updateProductImageModalCancelBtn" class="px-4 py-2 bg-gray-200 text-black hover:bg-gray-300 rounded-sm">
                            Cancel
                        </div>
                        <button
                            type="submit"
                            name="editproductimage"
                            class="bg-amber-500 text-white px-4 py-2 select-none hover:bg-amber-600 rounded-sm">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Product Type Delete Modal -->
        <div id="productImageConfirmDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <form class="bg-white max-w-5xl p-6 rounded-md shadow-md text-center" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" id="productImageDeleteForm">
                <h2 class="text-xl font-semibold text-red-600 mb-4">Confirm Product Image Deletion</h2>
                <p class="text-slate-600 mb-2">You are about to delete the following Product Image
                <div class="flex justify-center mb-3">
                    <div class="w-56 select-none">
                        <img id="deleteImagePath" class="w-full h-full object-cover" src="">
                    </div>
                </div>
                </p>
                <p class="text-sm text-gray-500 mb-4">
                    Deleting this Product Image will permanently remove it from the system, including all associated data.
                </p>
                <input type="hidden" name="productimageid" id="deleteProductImageID">
                <div class="flex justify-end gap-4 select-none">
                    <div id="productImageCancelDeleteBtn" class="px-4 py-2 bg-gray-200 text-black hover:bg-gray-300 rounded-sm">
                        Cancel
                    </div>
                    <button
                        type="submit"
                        name="deleteproductimage"
                        class="px-4 py-2 bg-red-600 text-white hover:bg-red-700 rounded-sm">
                        Delete
                    </button>
                </div>
            </form>
        </div>

        <!-- Add Product Image Form -->
        <div id="addProductImageModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <div class="bg-white w-full md:w-1/3 p-6 rounded-md shadow-md ">
                <h2 class="text-xl text-gray-700 font-bold mb-4">Add New Product Image</h2>
                <form class="flex flex-col space-y-4" action="<?php echo $_SERVER["PHP_SELF"]; ?>" enctype="multipart/form-data" method="post" id="productImageForm">
                    <!-- Image Input -->
                    <div class="relative w-full">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Product Image Information</label>
                        <input
                            id="productImageInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="file"
                            name="image" required>
                        <small id="productImageError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>
                    <!-- Image Alt Input -->
                    <div class="relative w-full">
                        <input
                            id="productImageAltInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="text"
                            name="imagealt"
                            placeholder="Enter image alt">
                        <small id="productImageAltError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>
                    <!-- Product -->
                    <div class="relative">
                        <select name="product" id="product" class="p-2 w-full border rounded" required>
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
                    <div class="flex flex-col sm:flex-row gap-4 sm:gap-2">
                        <!-- Primary Image -->
                        <div class="relative flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Primary (Optional)</label>
                            <select name="primary" id="primary" class="p-2 w-full border rounded">
                                <option value="1">True</option>
                                <option value="0" selected>False (default)</option>
                            </select>
                        </div>
                        <!-- Secondary Image -->
                        <div class="relative flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Secondary (Optional)</label>
                            <select name="secondary" id="secondary" class="p-2 w-full border rounded">
                                <option value="1">True</option>
                                <option value="0" selected>False (default)</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end gap-4 select-none">
                        <div id="addProductImageCancelBtn" class="px-4 py-2 text-amber-500 font-semibold hover:text-amber-600">
                            Cancel
                        </div>
                        <!-- Submit Button -->
                        <button
                            type="submit"
                            name="addproductimage"
                            class="bg-amber-500 text-white font-semibold px-4 py-2 rounded-sm select-none hover:bg-amber-600 transition-colors">
                            Add Image
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