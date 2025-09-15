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

// Get Rule Details
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = mysqli_real_escape_string($connect, $_GET['id']);
    $action = $_GET['action'];

    $response = ['success' => false];

    if ($action === 'getOrderDetails') {
        // Fetch order details and only one image per product (first image)
        $query = "
            SELECT od.*, o.*, p.*, pt.*, pi.ImageAdminPath, u.UserName, u.ProfileBgColor, u.UserEmail, u.UserPhone
            FROM orderdetailtb od
            JOIN ordertb o ON od.OrderID = o.OrderID
            JOIN producttb p ON od.ProductID = p.ProductID
            JOIN producttypetb pt ON p.ProductTypeID = pt.ProductTypeID
            LEFT JOIN (
                SELECT ProductID, ImageAdminPath 
                FROM productimagetb 
                GROUP BY ProductID
            ) pi ON p.ProductID = pi.ProductID
            JOIN usertb u ON o.UserID = u.UserID
            WHERE od.OrderID = '$id'
        ";
        $result = $connect->query($query);

        if ($result && $result->num_rows > 0) {
            $orderData = null;

            while ($row = $result->fetch_assoc()) {
                if (!$orderData) {
                    $orderData = [
                        'FullName' => $row['FullName'] ?? null,
                        'UserName' => $row['UserName'] ?? null,
                        'ProfileBgColor' => $row['ProfileBgColor'] ?? null,
                        'UserEmail' => $row['UserEmail'] ?? null,
                        'UserPhone' => $row['UserPhone'] ?? null,
                        'ShippingAddress' => $row['ShippingAddress'] ?? null,
                        'City' => $row['City'] ?? null,
                        'State' => $row['State'] ?? null,
                        'ZipCode' => $row['ZipCode'] ?? null,
                        'OrderDate' => $row['OrderDate'] ?? null,
                        'Subtotal' => $row['Subtotal'] ?? 0,
                        'OrderTax' => $row['OrderTax'] ?? 0,
                        'TotalPrice' => $row['TotalPrice'] ?? 0,
                        'Products' => []
                    ];
                }

                // Push each product row into Products array
                $orderData['Products'][] = [
                    'ProductID' => $row['ProductID'],
                    'Title' => $row['Title'],
                    'Description' => $row['Description'],
                    'ImageAdminPath' => $row['ImageAdminPath'] ?? 'default.png',
                    'OrderUnitQuantity' => $row['OrderUnitQuantity'],
                    'Price' => $row['OrderUnitPrice'],
                    'MarkupPercentage' => $row['MarkupPercentage'] ?? 0
                ];
            }

            $response['success'] = true;
            $response['order'] = $orderData;
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
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
            <div>
                <h2 class="text-xl text-gray-700 font-bold mb-4">User Order Overview</h2>
                <p>Monitor active orders, process cancellations, and analyze order trends to optimize resource allocation.</p>
            </div>

            <!-- Product Table -->
            <div class="overflow-x-auto">
                <!-- Product Search and Filter -->
                <form method="GET" class="my-4 flex items-start sm:items-center justify-between flex-col sm:flex-row gap-2 sm:gap-0">
                    <h1 class="text-lg text-gray-700 font-semibold text-nowrap">All Orders <span class="text-gray-400 text-sm ml-2"><?php echo $bookingCount ?></span></h1>
                    <div class="flex flex-col sm:flex-row items-start sm:items-center w-full gap-2 sm:gap-0">
                        <input type="text" name="order_search" class="p-2 ml-0 sm:ml-5 border border-gray-300 rounded-md w-full outline-none focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out" placeholder="Search for order..." value="<?php echo isset($_GET['order_search']) ? htmlspecialchars($_GET['order_search']) : ''; ?>">
                        <div class="flex items-center">
                            <label for="sort" class="ml-0 sm:ml-4 mr-2 flex items-center cursor-pointer select-none">
                                <i class="ri-filter-2-line text-xl"></i>
                                <p>Filters</p>
                            </label>
                            <!-- Search and filter form -->
                            <form method="GET" class="flex flex-col md:flex-row items-center gap-4 mb-4">

                                <select name="sort" id="sort" class="border p-2 rounded text-sm outline-none">
                                    <option value="random">All Statuses</option>
                                    <option value="Order Placed" <?= ($filterStatus == 'Order Placed') ? 'selected' : '' ?>>Order Placed</option>
                                    <option value="Processing" <?= ($filterStatus == 'Processing') ? 'selected' : '' ?>>Processing</option>
                                    <option value="Shipped" <?= ($filterStatus == 'Shipped') ? 'selected' : '' ?>>Shipped</option>
                                    <option value="Delivered" <?= ($filterStatus == 'Delivered') ? 'selected' : '' ?>>Delivered</option>
                                    <option value="Cancelled" <?= ($filterStatus == 'Cancelled') ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                            </form>
                        </div>
                    </div>
                </form>

                <!-- Reservation Table -->
                <div class="tableScrollBar overflow-y-auto max-h-[510px]">
                    <div id="reservationResults">
                        <?php include '../includes/admin_table_components/order_results.php'; ?>
                    </div>
                </div>

                <!-- Pagination Controls -->
                <div id="paginationContainer" class="flex justify-between items-center mt-3">
                    <?php include '../includes/admin_table_components/order_pagination.php'; ?>
                </div>
            </div>
        </div>

        <!-- Order Modal -->
        <div id="orderModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible -translate-y-5 p-2 transition-all duration-300">
            <div class="reservationScrollBar bg-white rounded-xl max-w-4xl w-full p-6 animate-fade-in max-h-[90vh] overflow-y-auto overflow-x-hidden">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-800">Order Details</h3>
                    <button id="closeOrderDetailButton" class="text-gray-400 hover:text-gray-500">
                        <i class="ri-close-line text-xl"></i>
                    </button>
                </div>

                <div class="space-y-3">
                    <!-- User Information -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium text-gray-800 mb-3">User Information</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <div class="py-3 text-start flex items-center gap-2 text-gray-600 text-sm">
                                    <div id="profilePreview" class="w-10 h-10 object-cover rounded-full text-white select-none">
                                        <p class="w-full h-full flex items-center justify-center font-semibold" id="userName"></p>
                                    </div>
                                    <div>
                                        <p class="font-bold" id="userFullName"></p>
                                        <p id="userEmail"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Phone:</span>
                                    <span class="font-medium text-gray-600" id="userPhone"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Address:</span>
                                    <span class="font-medium text-gray-600" id="userAddress"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">City:</span>
                                    <span class="font-medium text-gray-600" id="userCity"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">State:</span>
                                    <span class="font-medium text-gray-600" id="userState"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Zip:</span>
                                    <span class="font-medium text-gray-600" id="userZip"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Order Date:</span>
                                    <span class="font-medium text-gray-600" id="orderDate"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Products Ordered -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium text-gray-800 mb-3">Products Ordered</h4>
                        <div class="space-y-2 orderProductSwiper" id="orderProductContainer">
                            <!-- Dynamically inserted products will appear here -->
                            <div class="swiper-wrapper"></div>
                            <div class="swiper-pagination mt-2"></div>
                        </div>
                    </div>

                    <!-- Pricing Breakdown -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium text-gray-800 mb-3">Pricing Breakdown</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Subtotal:</span>
                                <span class="text-sm font-medium text-gray-600" id="orderSubtotal"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Taxes & Fees:</span>
                                <span class="text-sm font-medium text-gray-600" id="orderTaxesFees"></span>
                            </div>
                            <div class="border-t border-gray-200 pt-2 flex justify-between">
                                <span class="font-medium text-gray-800">Total:</span>
                                <span class="font-bold text-gray-600" id="orderTotal"></span>
                            </div>
                        </div>
                    </div>
                </div>
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