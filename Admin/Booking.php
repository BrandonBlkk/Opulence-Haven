<?php
session_start();
include('../config/dbConnection.php');
include('../includes/AutoIDFunc.php');
include('../includes/AdminPagination.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

$alertMessage = '';
$productID = AutoID('producttb', 'ProductID', 'PD-', 6);
$response = ['success' => false, 'message' => '', 'generatedId' => $productID];

// Add Product
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['addproduct'])) {
    $productTitle = mysqli_real_escape_string($connect, $_POST['productTitle']);
    $description = mysqli_real_escape_string($connect, $_POST['description']);
    $specification = mysqli_real_escape_string($connect, $_POST['specification']);
    $information = mysqli_real_escape_string($connect, $_POST['information']);
    $delivery = mysqli_real_escape_string($connect, $_POST['delivery']);
    $brand = mysqli_real_escape_string($connect, $_POST['brand']);
    $price = mysqli_real_escape_string($connect, $_POST['price']);
    $discountPrice = mysqli_real_escape_string($connect, $_POST['discountPrice']);
    $sellingFast = mysqli_real_escape_string($connect, $_POST['sellingfast']);
    $stock = 0;
    $productType = mysqli_real_escape_string($connect, $_POST['productType']);

    // Check if the product already exists using prepared statement
    $checkQuery = "SELECT Title FROM producttb WHERE Title = '$productTitle'";
    $count = $connect->query($checkQuery)->num_rows;

    if ($count > 0) {
        $response['message'] = 'Product you added is already existed.';
    } else {
        $addProductQuery = "INSERT INTO producttb (ProductID, Title, Price, DiscountPrice, Description, Specification, Information, DeliveryInfo, Brand, SellingFast, Stock, ProductTypeID)
        VALUES ('$productID', '$productTitle', '$price', '$discountPrice', '$description', '$specification', '$information', '$delivery', '$brand', '$sellingFast', '$stock', '$productType')";

        if ($connect->query($addProductQuery)) {
            $response['success'] = true;
            $response['message'] = 'A new product has been successfully added.';
            // Keep the generated ID in the response
            $response['generatedId'] = $productID;
        } else {
            $response['message'] = "Failed to add product. Please try again.";
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
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
        $product = $connect->query($query)->fetch_assoc();

        if ($product) {
            $response['success'] = true;
            $response['product'] = $product;
        } else {
            $response['success'] = true;
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Update Product
if (isset($_POST['editproduct'])) {
    $productId = mysqli_real_escape_string($connect, $_POST['productid']);
    $productTitle = mysqli_real_escape_string($connect, $_POST['updateproductTitle']);
    $brand = mysqli_real_escape_string($connect, $_POST['updatebrand']);
    $description = mysqli_real_escape_string($connect, $_POST['updatedescription']);
    $specification = mysqli_real_escape_string($connect, $_POST['updatespecification']);
    $information = mysqli_real_escape_string($connect, $_POST['updateinformation']);
    $delivery = mysqli_real_escape_string($connect, $_POST['updatedelivery']);
    $price = mysqli_real_escape_string($connect, $_POST['updateprice']);
    $discountPrice = mysqli_real_escape_string($connect, $_POST['updatediscountPrice']);
    $sellingFast = mysqli_real_escape_string($connect, $_POST['updatesellingfast']);
    $productType = mysqli_real_escape_string($connect, $_POST['updateproductType']);

    // Update query
    $updateQuery = "UPDATE producttb SET Title = '$productTitle', Brand = '$brand', Description = '$description', Specification = '$specification', Information = '$information', DeliveryInfo = '$delivery', 
    Price = '$price', DiscountPrice = '$discountPrice', SellingFast = '$sellingFast', ProductTypeID = '$productType' WHERE ProductID = '$productId'";

    if ($connect->query($updateQuery)) {
        $response['success'] = true;
        $response['message'] = 'The product has been successfully updated.';
        $response['generatedId'] = $productId;
        $response['productTitle'] = $productTitle;
        $response['brand'] = $brand;
        $response['description'] = $description;
        $response['specification'] = $specification;
        $response['information'] = $information;
        $response['delivery'] = $delivery;
        $response['price'] = $price;
        $response['discountPrice'] = $discountPrice;
        $response['sellingFast'] = $sellingFast;
        $response['productType'] = $productType;
    } else {
        $response['message'] = "Failed to update product. Please try again.";
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Delete Product
if (isset($_POST['deleteproduct'])) {
    $productId = mysqli_real_escape_string($connect, $_POST['productid']);

    // Build query based on action
    $deleteQuery = "DELETE FROM producttb WHERE ProductID = '$productId'";

    if ($connect->query($deleteQuery)) {
        $response['success'] = true;
        $response['generatedId'] = $productId;
    } else {
        $response['success'] = false;
        $response['message'] = 'Failed to delete product. Please try again.';
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Initialize search and filter variables for product
$searchBookingQuery = isset($_GET['product_search']) ? mysqli_real_escape_string($connect, $_GET['product_search']) : '';
$filterProductID = isset($_GET['sort']) ? $_GET['sort'] : 'random';

// Construct the product query based on search and product type filter
if ($filterProductID !== 'random' && !empty($searchBookingQuery)) {
    $bookingSelect = "SELECT * FROM reservationtb WHERE ReservationID = '$filterProductID' AND (Title LIKE '%$searchBookingQuery%' OR Description LIKE '%$searchBookingQuery%' OR Specification LIKE '%$searchBookingQuery%' OR Information LIKE '%$searchBookingQuery%' OR Brand LIKE '%$searchBookingQuery%') LIMIT $rowsPerPage OFFSET $productOffset";
} elseif ($filterProductID !== 'random') {
    $bookingSelect = "SELECT * FROM reservationtb WHERE ReservationID = '$filterProductID' LIMIT $rowsPerPage OFFSET $productOffset";
} elseif (!empty($searchBookingQuery)) {
    $bookingSelect = "SELECT * FROM reservationtb WHERE Title LIKE '%$searchBookingQuery%' OR Description LIKE '%$searchBookingQuery%' OR Specification LIKE '%$searchBookingQuery%' OR Information LIKE '%$searchBookingQuery%' OR Brand LIKE '%$searchBookingQuery%' LIMIT $rowsPerPage OFFSET $productOffset";
} else {
    $bookingSelect = "SELECT r.*, u.UserName, u.UserPhone FROM reservationtb r
    JOIN usertb u ON r.UserID = u.UserID LIMIT $rowsPerPage OFFSET $productOffset";
}

$bookingSelectQuery = $connect->query($bookingSelect);
$bookings = [];

if (mysqli_num_rows($bookingSelectQuery) > 0) {
    while ($row = $bookingSelectQuery->fetch_assoc()) {
        $bookings[] = $row;
    }
}

// Construct the product count query based on search and product type filter
if ($filterProductID !== 'random' && !empty($searchBookingQuery)) {
    $productQuery = "SELECT COUNT(*) as count FROM reservationtb WHERE ReservationID = '$filterProductID' AND (Title LIKE '%$searchBookingQuery%' OR Description LIKE '%$searchBookingQuery%' OR Specification LIKE '%$searchBookingQuery%' OR Information LIKE '%$searchBookingQuery%' OR Brand LIKE '%$searchBookingQuery%')";
} elseif ($filterProductID !== 'random') {
    $productQuery = "SELECT COUNT(*) as count FROM reservationtb WHERE ReservationID = '$filterProductID'";
} elseif (!empty($searchBookingQuery)) {
    $productQuery = "SELECT COUNT(*) as count FROM reservationtb WHERE Title LIKE '%$searchBookingQuery%' OR Description LIKE '%$searchBookingQuery%' OR Specification LIKE '%$searchBookingQuery%' OR Information LIKE '%$searchBookingQuery%' OR Brand LIKE '%$searchBookingQuery%'";
} else {
    $productQuery = "SELECT COUNT(*) as count FROM reservationtb";
}

// Execute the count query
$productResult = $connect->query($productQuery);
$productCount = $productResult->fetch_assoc()['count'];
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
                    <h2 class="text-xl text-gray-700 font-bold mb-4">Add Product Overview</h2>
                    <p>Add product information to monitor inventory, track orders, and manage product details for efficient operations.</p>
                </div>
                <div class="flex items-center gap-3">
                    <!-- Image Button -->
                    <a href="ProductImage.php" class="bg-amber-500 text-white px-3 py-1 rounded select-none hover:bg-amber-600 transition-colors">
                        <i class="ri-folder-image-line text-xl"></i>
                    </a>
                    <!-- Add Product Button -->
                    <button id="addProductBtn" class="bg-amber-500 text-white font-semibold px-3 py-1 rounded select-none hover:bg-amber-600 transition-colors">
                        <i class="ri-add-line text-xl"></i>
                    </button>
                </div>

            </div>

            <!-- Product Table -->
            <div class="overflow-x-auto">
                <!-- Product Search and Filter -->
                <form method="GET" class="my-4 flex items-start sm:items-center justify-between flex-col sm:flex-row gap-2 sm:gap-0">
                    <h1 class="text-lg text-gray-700 font-semibold text-nowrap">All Products <span class="text-gray-400 text-sm ml-2"><?php echo $productCount ?></span></h1>
                    <div class="flex items-center w-full">
                        <input type="text" name="product_search" class="p-2 ml-0 sm:ml-5 border border-gray-300 rounded-md w-full outline-none focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out" placeholder="Search for product..." value="<?php echo isset($_GET['product_search']) ? htmlspecialchars($_GET['product_search']) : ''; ?>">
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
                                    $query = $connect->query($select);
                                    $count = $query->num_rows;

                                    if ($count) {
                                        for ($i = 0; $i < $count; $i++) {
                                            $row = $query->fetch_assoc();
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
                <div class="tableScrollBar overflow-y-auto max-h-[510px]">
                    <table class="min-w-full bg-white rounded-lg">
                        <thead>
                            <tr class="bg-gray-100 text-gray-600 text-sm">
                                <th class="p-3 text-start">Reservation ID</th>
                                <th class="p-3 text-start">Customer</th>
                                <th class="p-3 text-start hidden sm:table-cell">Contact</th>
                                <th class="p-3 text-start">Total Price</th>
                                <th class="p-3 text-start hidden lg:table-cell">Reservation Date</th>
                                <th class="p-3 text-start">Status</th>
                                <th class="p-3 text-start">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm">
                            <?php if (!empty($bookings)): ?>
                                <?php foreach ($bookings as $booking): ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="p-3 text-start whitespace-nowrap">
                                            <div class="flex items-center gap-2 font-medium text-gray-500">
                                                <input type="checkbox" class="form-checkbox h-3 w-3 border-2 text-amber-500">
                                                <span>#<?= htmlspecialchars($booking['ReservationID']) ?></span>
                                            </div>
                                        </td>
                                        <td class="p-3 text-start">
                                            <div class="font-medium">
                                                <?= htmlspecialchars($booking['Title'] . ' ' . $booking['UserName'] . ' ' . $booking['LastName']) ?>
                                            </div>
                                            <div class="text-xs text-gray-400">
                                                <?= htmlspecialchars($booking['Travelling'] === 1 ? 'Travelling' : 'Not Travelling') ?>
                                            </div>
                                        </td>
                                        <td class="p-3 text-start hidden sm:table-cell">
                                            <?= htmlspecialchars($booking['UserPhone']) ?>
                                        </td>
                                        <td class="p-3 text-start">
                                            $<?= htmlspecialchars(number_format($booking['TotalPrice'], 2)) ?>
                                            <?php if ($booking['PointsDiscount'] > 0): ?>
                                                <div class="text-xs text-green-500">
                                                    -$<?= htmlspecialchars(number_format($booking['PointsDiscount'], 2)) ?> (Points)
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="p-3 text-start hidden lg:table-cell">
                                            <?= htmlspecialchars(date('d M Y', strtotime($booking['ReservationDate']))) ?>
                                            <div class="text-xs text-gray-400">
                                                Exp: <?= htmlspecialchars(date('d M Y', strtotime($booking['ExpiryDate']))) ?>
                                            </div>
                                        </td>
                                        <td class="p-3 text-start">
                                            <?php
                                            $statusClass = '';
                                            switch ($booking['Status']) {
                                                case 'Confirmed':
                                                    $statusClass = 'bg-green-100 text-green-800';
                                                    break;
                                                case 'Pending':
                                                    $statusClass = 'bg-yellow-100 text-yellow-800';
                                                    break;
                                                case 'Cancelled':
                                                    $statusClass = 'bg-red-100 text-red-800';
                                                    break;
                                                case 'Completed':
                                                    $statusClass = 'bg-blue-100 text-blue-800';
                                                    break;
                                                default:
                                                    $statusClass = 'bg-gray-100 text-gray-800';
                                            }
                                            ?>
                                            <span class="px-2 py-1 rounded-full text-xs <?= $statusClass ?>">
                                                <?= htmlspecialchars($booking['Status']) ?>
                                            </span>
                                        </td>
                                        <td class="p-3 text-start whitespace-nowrap">
                                            <div class="flex items-center gap-2">
                                                <button class="p-1 text-gray-400 hover:text-amber-500 transition-colors">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                                    </svg>
                                                </button>
                                                <button class="p-1 text-gray-400 hover:text-red-500 transition-colors">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="p-3 text-center text-gray-500 py-52">
                                        No reservations found.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <!-- Pagination Controls -->
                    <div class="flex justify-center items-center mt-1 <?= (!empty($bookings) ? 'flex' : 'hidden') ?>">
                        <!-- Previous Btn -->
                        <?php if ($productCurrentPage > 1) {
                        ?>
                            <a href="?productpage=<?= $productCurrentPage - 1 ?>"
                                class="px-3 py-1 mx-1 border rounded <?= $productpage == $productCurrentPage ? 'bg-gray-200' : 'bg-white' ?>">
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
                        <?php for ($productpage = 1; $productpage <= $totalProductPages; $productpage++): ?>
                            <a href="?productpage=<?= $productpage ?>&product_search=<?= htmlspecialchars($searchBookingQuery) ?>"
                                class="px-3 py-1 mx-1 border rounded select-none <?= $productpage == $productCurrentPage ? 'bg-gray-200' : 'bg-white' ?>">
                                <?= $productpage ?>
                            </a>
                        <?php endfor; ?>
                        <!-- Next Btn -->
                        <?php if ($productCurrentPage < $totalProductPages) {
                        ?>
                            <a href="?productpage=<?= $productCurrentPage + 1 ?>"
                                class="px-3 py-1 mx-1 border rounded <?= $productpage == $productCurrentPage ? 'bg-gray-200' : 'bg-white' ?>">
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
        </div>

        <!-- Product Details Modal -->
        <div id="updateProductModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <div class="bg-white w-full max-w-4xl p-6 rounded-md shadow-md mx-4 sm:mx-8 lg:mx-auto overflow-y-auto max-h-[92vh]">
                <h2 class="text-xl font-bold text-gray-700 mb-4">Edit Product</h2>
                <form class="flex flex-col space-y-4" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" enctype="multipart/form-data" id="updateProductForm">
                    <input type="hidden" name="productid" id="updateProductID">
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
                                placeholder="Enter product description" rows="4"></textarea>
                            <small id="updateDescriptionError" class="absolute left-2 -bottom-1 bg-white text-red-500 text-xs opacity-100 transition-all duration-200 select-none"></small>
                        </div>
                        <!-- Specification -->
                        <div class="relative flex-1">
                            <textarea
                                id="updateSpecificationInput"
                                class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                name="updatespecification"
                                placeholder="Enter product specification" rows="4"></textarea>
                            <small id="updateSpecificationError" class="absolute left-2 -bottom-1 bg-white text-red-500 text-xs opacity-100 transition-all duration-200 select-none"></small>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-4 sm:gap-2">
                        <!-- Information -->
                        <div class="relative flex-1">
                            <textarea
                                id="updateInformationInput"
                                class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                name="updateinformation"
                                placeholder="Enter product information" rows="4"></textarea>
                            <small id="updateInformationError" class="absolute left-2 -bottom-1 bg-white text-red-500 text-xs opacity-100 transition-all duration-200 select-none"></small>
                        </div>
                        <!-- DeliveryInfo -->
                        <div class="relative flex-1">
                            <textarea
                                id="updateDeliveryInput"
                                class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                name="updatedelivery"
                                placeholder="Enter delivery information" rows="4"></textarea>
                            <small id="updateDeliveryError" class="absolute left-2 -bottom-1 bg-white text-red-500 text-xs opacity-100 transition-all duration-200 select-none"></small>
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
                    </div>
                    <div class="flex flex-col sm:flex-row gap-4 sm:gap-2">
                        <!-- Selling Fast -->
                        <div class="relative flex-1">
                            <select name="updatesellingfast" id="updatesellingfast" class="p-2 w-full border rounded outline-none">
                                <option value="" disabled selected>Selling Fast</option>
                                <option value="1" <?php echo $product['SellingFast'] == '1' ? 'selected' : ''; ?>>True</option>
                                <option value="0" <?php echo $product['SellingFast'] == '0' ? 'selected' : ''; ?>>False</option>
                            </select>
                        </div>
                        <!-- Product Type -->
                        <div class="relative flex-1">
                            <select name="updateproductType" id="updateproductType" class="p-2 w-full border rounded outline-none">
                                <option value="" disabled selected>Select type of products</option>
                                <?php
                                $select = "SELECT * FROM producttypetb";
                                $query = $connect->query($select);
                                $count = $query->num_rows;

                                if ($count) {
                                    for ($i = 0; $i < $count; $i++) {
                                        $row = $query->fetch_assoc();
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
                        <div id="updateProductModalCancelBtn" class="px-4 py-2 bg-gray-200 text-black hover:bg-gray-300 rounded-sm">
                            Cancel
                        </div>
                        <button
                            type="submit"
                            name="editproduct"
                            class="bg-amber-500 text-white px-4 py-2 select-none hover:bg-amber-600 rounded-sm">
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
                    <div id="productCancelDeleteBtn" class="px-4 py-2 bg-gray-200 text-black hover:bg-gray-300 rounded-sm">
                        Cancel
                    </div>
                    <button
                        type="submit"
                        name="deleteproduct"
                        class="px-4 py-2 bg-red-600 text-white hover:bg-red-700 rounded-sm">
                        Delete
                    </button>
                </div>
            </form>
        </div>

        <!-- Add Produc Form -->
        <div id="addProductModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <div class="bg-white w-full max-w-4xl p-6 rounded-md shadow-md mx-4 sm:mx-8 lg:mx-auto">
                <h2 class="text-xl font-bold text-gray-700 mb-4">Add New Product</h2>
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
                            <small id="productDescriptionError" class="absolute left-2 -bottom-1 bg-white text-red-500 text-xs opacity-100 transition-all duration-200 select-none"></small>
                        </div>
                        <!-- Specification -->
                        <div class="relative flex-1">
                            <textarea
                                id="specificationInput"
                                class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                name="specification"
                                placeholder="Enter product specification"></textarea>
                            <small id="specificationError" class="absolute left-2 -bottom-1 bg-white text-red-500 text-xs opacity-100 transition-all duration-200 select-none"></small>
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
                            <small id="informationError" class="absolute left-2 -bottom-1 bg-white text-red-500 text-xs opacity-100 transition-all duration-200 select-none"></small>
                        </div>
                        <!-- DeliveryInfo -->
                        <div class="relative flex-1">
                            <textarea
                                id="deliveryInput"
                                class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                name="delivery"
                                placeholder="Enter delivery information"></textarea>
                            <small id="deliveryError" class="absolute left-2 -bottom-1 bg-white text-red-500 text-xs opacity-100 transition-all duration-200 select-none"></small>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-4 sm:gap-2">
                        <!-- Price -->
                        <div class="relative flex-1">
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
                    </div>
                    <div class="flex flex-col sm:flex-row gap-4 sm:gap-2">
                        <!-- Selling Fast -->
                        <div class="relative flex-1">
                            <select name="sellingfast" id="sellingfast" class="p-2 w-full border rounded outline-none" required>
                                <option value="" disabled selected>Selling Fast</option>
                                <option value="1">True</option>
                                <option value="0">False</option>
                            </select>
                        </div>
                        <!-- Product Type -->
                        <div class="relative flex-1">
                            <select name="productType" id="productType" class="p-2 w-full border rounded outline-none" required>
                                <option value="" disabled selected>Select type of products</option>
                                <?php
                                $select = "SELECT * FROM producttypetb";
                                $query = $connect->query($select);
                                $count = $query->num_rows;
                                if ($count) {
                                    for ($i = 0; $i < $count; $i++) {
                                        $row = $query->fetch_assoc();
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
                    <div class="flex justify-between items-center gap-4 select-none">
                        <!-- Product Size -->
                        <div>
                            <a class="mt-1 text-sm text-amber-500 cursor-pointer" href="AddSize.php">Add product size</a>
                        </div>
                        <div class="flex items-center">
                            <div id="addProductCancelBtn" class="px-4 py-2 text-amber-500 font-semibold hover:text-amber-600">
                                Cancel
                            </div>
                            <!-- Submit Button -->
                            <button
                                type="submit"
                                name="addproduct"
                                class="bg-amber-500 text-white font-semibold px-4 py-2 rounded-sm select-none hover:bg-amber-600 transition-colors">
                                Add Product
                            </button>
                        </div>
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