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
$response = ['success' => false, 'message' => ''];

// Get all orders in the month (exclude Pending and Cancelled)
$orderQuery = "SELECT o.OrderID, od.ProductID, od.OrderUnitQuantity, od.OrderUnitPrice, p.MarkupPercentage
               FROM ordertb o
               JOIN orderdetailtb od ON o.OrderID = od.OrderID
               JOIN producttb p ON od.ProductID = p.ProductID
               WHERE o.Status NOT IN ('Pending', 'Cancelled')";

$orderResult = $connect->query($orderQuery);

$totalProfit = 0;
$uniqueProductsSold = [];

if ($orderResult && $orderResult->num_rows > 0) {
    while ($row = $orderResult->fetch_assoc()) {
        // Calculate profit for each product in confirmed orders
        $profitPerUnit = $row['OrderUnitPrice'] * ($row['MarkupPercentage'] / 100);
        $totalProfit += $profitPerUnit * $row['OrderUnitQuantity'];

        // Track unique sold products
        $uniqueProductsSold[$row['ProductID']] = true;
    }
}

// Count of unique products sold
$markupProductCount = count($uniqueProductsSold);

// Total available products
$allProductQuery = "SELECT COUNT(*) AS totalProducts FROM producttb";
$allProductResult = $connect->query($allProductQuery);
$allProductRow = $allProductResult ? $allProductResult->fetch_assoc() : ['totalProducts' => 0];
$allProductCount = $allProductRow['totalProducts'];

// Calculate real Average Markup
$markupQuery = "SELECT MarkupPercentage FROM producttb WHERE isActive = 1";
$markupResult = $connect->query($markupQuery);

$markupSum = 0;
$markupCount = 0;

if ($markupResult && $markupResult->num_rows > 0) {
    while ($row = $markupResult->fetch_assoc()) {
        $markupSum += $row['MarkupPercentage'];
        $markupCount++;
    }
}

$averageMarkup = ($markupCount > 0) ? round($markupSum / $markupCount, 2) : 0;
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
                    <h2 class="text-xl text-gray-700 font-bold mb-2">Pricing & Markup Management</h2>
                    <p class="text-gray-600">Set profit margins by adding markup percentages to supplier prices. Monitor pricing strategies and profitability.</p>
                </div>
            </div>

            <!-- Pricing Table -->
            <div class="overflow-x-auto">
                <!-- Pricing Search and Filter -->
                <form method="GET" class="my-4 flex items-start sm:items-center justify-between flex-col sm:flex-row gap-2 sm:gap-0">
                    <h1 class="text-lg text-gray-700 font-semibold text-nowrap">All Products
                        <span class="text-gray-400 text-sm ml-2"><?php echo isset($productCount) ? $productCount : 0; ?></span>
                    </h1>
                    <div class="flex items-center w-full">
                        <input type="text" name="product_search" class="p-2 ml-0 sm:ml-5 border border-gray-300 rounded-md w-full outline-none focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out" placeholder="Search for product..." value="<?php echo isset($_GET['product_search']) ? htmlspecialchars($_GET['product_search']) : ''; ?>">
                        <div class="flex items-center">
                            <label for="sort" class="ml-4 mr-2 flex items-center cursor-pointer select-none">
                                <i class="ri-filter-2-line text-xl"></i>
                                <p>Filters</p>
                            </label>
                            <!-- Search and filter form -->
                            <form method="GET" class="flex flex-col md:flex-row items-center gap-4 mb-4">
                                <select name="sort" id="sort" class="border p-2 rounded text-sm outline-none">
                                    <option value="random">All Product Types</option>
                                    <?php
                                    $select = "SELECT * FROM producttypetb";
                                    $query = $connect->query($select);
                                    $count = $query->num_rows;

                                    if ($count) {
                                        while ($row = $query->fetch_assoc()) {
                                            $producttype_id = $row['ProductTypeID'];
                                            $producttype = $row['ProductType'];
                                            $selected = (isset($_GET['sort']) && $_GET['sort'] == $producttype_id) ? 'selected' : '';
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

                <!-- Pricing Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                        <div class="flex justify-between items-center">
                            <h3 class="text-blue-800 font-semibold">Average Markup</h3>
                            <i class="ri-line-chart-line text-blue-500 text-xl"></i>
                        </div>
                        <p class="text-2xl font-bold text-blue-900">
                            <?php echo $averageMarkup; ?>%
                        </p>
                        <p class="text-sm text-blue-600">Across all products</p>
                    </div>

                    <div class="bg-green-50 p-4 rounded-lg border border-green-100">
                        <div class="flex justify-between items-center">
                            <h3 class="text-green-800 font-semibold">Profit Potential</h3>
                            <i class="ri-money-dollar-circle-line text-green-500 text-xl"></i>
                        </div>
                        <p class="text-2xl font-bold text-green-900">
                            $<?php echo number_format((float)$totalProfit, 2); ?>
                        </p>
                        <p class="text-sm text-green-600">Monthly estimate</p>
                    </div>

                    <div class="bg-purple-50 p-4 rounded-lg border border-purple-100">
                        <div class="flex justify-between items-center">
                            <h3 class="text-purple-800 font-semibold">Products Priced</h3>
                            <i class="ri-checkbox-circle-line text-purple-500 text-xl"></i>
                        </div>
                        <p class="text-2xl font-bold text-purple-900">
                            <?php echo $markupProductCount ?>/<?php echo $allProductCount ?>
                        </p>
                        <p class="text-sm text-purple-600">
                            <?php
                            $completionPercentage = ($allProductCount > 0)
                                ? round(($markupProductCount / $allProductCount) * 100, 2)
                                : 0;
                            echo $completionPercentage . "% completed";
                            ?>
                        </p>
                    </div>
                </div>

                <!-- Pricing Table -->
                <div class="tableScrollBar overflow-y-auto max-h-[495px]">
                    <div id="pricingResults">
                        <?php include '../includes/admin_table_components/pricing_results.php'; ?>
                    </div>
                </div>

                <!-- Pagination Controls -->
                <div id="paginationContainer" class="flex justify-between items-center mt-3">
                    <?php include '../includes/admin_table_components/pricing_pagination.php'; ?>
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