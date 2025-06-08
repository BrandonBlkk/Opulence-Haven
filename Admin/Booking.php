<?php
session_start();
include('../config/dbConnection.php');
include('../includes/AutoIDFunc.php');
include('../includes/AdminPagination.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

$alertMessage = '';

// Initialize search and filter variables for reservations
$searchBookingQuery = isset($_GET['booking_search']) ? mysqli_real_escape_string($connect, $_GET['booking_search']) : '';
$filterStatus = isset($_GET['sort']) ? $_GET['sort'] : 'random';
$bookingCurrentPage = isset($_GET['bookingpage']) ? (int)$_GET['bookingpage'] : 1;
$rowsPerPage = 1; // Number of rows per page
$bookingOffset = ($bookingCurrentPage - 1) * $rowsPerPage;

// Construct the reservation query based on search and status filter
if ($filterStatus !== 'random' && !empty($searchBookingQuery)) {
    $bookingSelect = "SELECT r.*, u.UserName, u.UserPhone 
                     FROM reservationtb r 
                     JOIN usertb u ON r.UserID = u.UserID  
                     WHERE r.Status = '$filterStatus' 
                     AND (r.FirstName LIKE '%$searchBookingQuery%' 
                          OR r.LastName LIKE '%$searchBookingQuery%'
                          OR r.UserPhone LIKE '%$searchBookingQuery%'
                          OR r.ReservationID LIKE '%$searchBookingQuery%'
                          OR u.UserName LIKE '%$searchBookingQuery%') 
                     LIMIT $rowsPerPage OFFSET $bookingOffset";
} elseif ($filterStatus !== 'random') {
    $bookingSelect = "SELECT r.*, u.UserName, u.UserPhone 
                     FROM reservationtb r 
                     JOIN usertb u ON r.UserID = u.UserID 
                     WHERE r.Status = '$filterStatus' 
                     LIMIT $rowsPerPage OFFSET $bookingOffset";
} elseif (!empty($searchBookingQuery)) {
    $bookingSelect = "SELECT r.*, u.UserName, u.UserPhone 
                     FROM reservationtb r 
                     JOIN usertb u ON r.UserID = u.UserID 
                     WHERE (r.FirstName LIKE '%$searchBookingQuery%'
                           OR r.LastName LIKE '%$searchBookingQuery%'
                           OR r.UserPhone LIKE '%$searchBookingQuery%'
                           OR r.ReservationID LIKE '%$searchBookingQuery%'
                           OR u.UserName LIKE '%$searchBookingQuery%') 
                     LIMIT $rowsPerPage OFFSET $bookingOffset";
} else {
    $bookingSelect = "SELECT r.*, u.UserName, u.UserPhone 
                     FROM reservationtb r
                     JOIN usertb u ON r.UserID = u.UserID 
                     LIMIT $rowsPerPage OFFSET $bookingOffset";
}

$bookingSelectQuery = $connect->query($bookingSelect);
$bookings = [];

if (mysqli_num_rows($bookingSelectQuery) > 0) {
    while ($row = $bookingSelectQuery->fetch_assoc()) {
        $bookings[] = $row;
    }
}

// Count query - simplified using the same conditions
if ($filterStatus !== 'random' && !empty($searchBookingQuery)) {
    $BookingQuery = "SELECT COUNT(*) as count 
                    FROM reservationtb r
                    JOIN usertb u ON r.UserID = u.UserID  
                    WHERE r.Status = '$filterStatus' 
                    AND (r.FirstName LIKE '%$searchBookingQuery%' 
                         OR r.LastName LIKE '%$searchBookingQuery%'
                         OR r.UserPhone LIKE '%$searchBookingQuery%'
                         OR r.ReservationID LIKE '%$searchBookingQuery%'
                         OR u.UserName LIKE '%$searchBookingQuery%')";
} elseif ($filterStatus !== 'random') {
    $BookingQuery = "SELECT COUNT(*) as count 
                    FROM reservationtb r
                    JOIN usertb u ON r.UserID = u.UserID 
                    WHERE r.Status = '$filterStatus'";
} elseif (!empty($searchBookingQuery)) {
    $BookingQuery = "SELECT COUNT(*) as count 
                    FROM reservationtb r
                    JOIN usertb u ON r.UserID = u.UserID 
                    WHERE (r.FirstName LIKE '%$searchBookingQuery%'
                          OR r.LastName LIKE '%$searchBookingQuery%'
                          OR r.UserPhone LIKE '%$searchBookingQuery%'
                          OR r.ReservationID LIKE '%$searchBookingQuery%'
                          OR u.UserName LIKE '%$searchBookingQuery%')";
} else {
    $BookingQuery = "SELECT COUNT(*) as count 
                    FROM reservationtb r
                    JOIN usertb u ON r.UserID = u.UserID";
}

// Execute the count query
$bookingResult = $connect->query($BookingQuery);
$bookingCount = $bookingResult->fetch_assoc()['count'];
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
            <div>
                <h2 class="text-xl text-gray-700 font-bold mb-4">User Reservation Overview</h2>
                <p>Monitor active reservations, process cancellations, and analyze booking trends to optimize resource allocation.</p>
            </div>

            <!-- Product Table -->
            <div class="overflow-x-auto">
                <!-- Product Search and Filter -->
                <form method="GET" class="my-4 flex items-start sm:items-center justify-between flex-col sm:flex-row gap-2 sm:gap-0">
                    <h1 class="text-lg text-gray-700 font-semibold text-nowrap">All Reservations <span class="text-gray-400 text-sm ml-2"><?php echo $bookingCount ?></span></h1>
                    <div class="flex items-center w-full">
                        <input type="text" name="booking_search" class="p-2 ml-0 sm:ml-5 border border-gray-300 rounded-md w-full outline-none focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out" placeholder="Search for reservation..." value="<?php echo isset($_GET['booking_search']) ? htmlspecialchars($_GET['booking_search']) : ''; ?>">
                        <div class="flex items-center">
                            <label for="sort" class="ml-4 mr-2 flex items-center cursor-pointer select-none">
                                <i class="ri-filter-2-line text-xl"></i>
                                <p>Filters</p>
                            </label>
                            <!-- Search and filter form -->
                            <form method="GET" class="flex flex-col md:flex-row items-center gap-4 mb-4">
                                <select name="sort" id="sort" class="border p-2 rounded text-sm" onchange="this.form.submit()">
                                    <option value="random">All Statuses</option>
                                    <option value="Pending" <?= ($filterStatus == 'Pending') ? 'selected' : '' ?>>Pending</option>
                                    <option value="Confirmed" <?= ($filterStatus == 'Confirmed') ? 'selected' : '' ?>>Confirmed</option>
                                    <option value="Cancelled" <?= ($filterStatus == 'Cancelled') ? 'selected' : '' ?>>Cancelled</option>
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
                                            <div class="font-medium text-gray-500">
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
                                            <i class="details-btn ri-eye-line text-lg cursor-pointer"
                                                data-booking-id="<?= htmlspecialchars($booking['ReservationID']) ?>"></i>
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
                        <?php if ($bookingCurrentPage > 1) {
                        ?>
                            <a href="?bookingpage=<?= $bookingCurrentPage - 1 ?>&sort=<?= htmlspecialchars($filterStatus) ?>&booking_search=<?= htmlspecialchars($searchBookingQuery) ?>"
                                class="px-3 py-1 mx-1 border rounded <?= $bookingpage == $bookingCurrentPage ? 'bg-gray-200' : 'bg-white' ?>">
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
                        <?php for ($bookingpage = 1; $bookingpage <= $totalBookingPages; $bookingpage++): ?>
                            <a href="?bookingpage=<?= $bookingpage ?>&sort=<?= htmlspecialchars($filterStatus) ?>&booking_search=<?= htmlspecialchars($searchBookingQuery) ?>"
                                class="px-3 py-1 mx-1 border rounded select-none <?= $bookingpage == $bookingCurrentPage ? 'bg-gray-200' : 'bg-white' ?>">
                                <?= $bookingpage ?>
                            </a>
                        <?php endfor; ?>
                        <!-- Next Btn -->
                        <?php if ($bookingCurrentPage < $totalBookingPages) {
                        ?>
                            <a href="?bookingpage=<?= $bookingCurrentPage + 1 ?>&sort=<?= htmlspecialchars($filterStatus) ?>&booking_search=<?= htmlspecialchars($searchBookingQuery) ?>"
                                class="px-3 py-1 mx-1 border rounded <?= $bookingpage == $bookingCurrentPage ? 'bg-gray-200' : 'bg-white' ?>">
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