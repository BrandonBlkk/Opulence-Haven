<?php
session_start();
include('../config/db_connection.php');
include('../includes/auto_id_func.php');
include('../includes/admin_pagination.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

$alertMessage = '';

// Get Product Type Details
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = mysqli_real_escape_string($connect, $_GET['id']);
    $action = $_GET['action'];

    if ($action == 'getPurchaseDetails') {
        $query = "SELECT p.*, a.FirstName, a.LastName, a.AdminEmail, s.SupplierName, s.SupplierEmail FROM purchasetb p
        JOIN admintb a ON p.AdminID = a.AdminID
        JOIN suppliertb s ON p.SupplierID = s.SupplierID WHERE PurchaseID = '$id'";
        $purchase = $connect->query($query)->fetch_assoc();

        if ($purchase) {
            $response['success'] = true;
            $response['purchase'] = $purchase;
        } else {
            $response['success'] = false;
            $response['message'] = 'Purchase not found';
        }
    } elseif ($action == 'getPurchaseItems') {
        $query = "SELECT pd.*, p.ProductName 
                  FROM purchasedetailtb pd 
                  JOIN producttb p ON pd.ProductID = p.ProductID 
                  WHERE pd.PurchaseID = '$id'";
        $result = $connect->query($query);

        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }

        $response['success'] = true;
        $response['items'] = $items;
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
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
                <h2 class="text-xl text-gray-700 font-bold mb-4">Purchase History</h2>
                <p>Manage the purchase history and view details for each purchase.</p>
            </div>

            <!-- Prooduct Type Table -->
            <div class="overflow-x-auto">
                <!-- Product Type Search and Filter -->
                <form method="GET" class="my-4 flex items-start sm:items-center justify-between flex-col sm:flex-row gap-2 sm:gap-0">
                    <h1 class="text-lg text-gray-700 font-semibold text-nowrap">All Purchases <span class="text-gray-400 text-sm ml-2"><?php echo $purchaseCount ?></span></h1>
                    <div class="flex items-center w-full">
                        <input type="text" name="purchase_search" class="p-2 ml-0 sm:ml-5 border border-gray-300 rounded-md w-full outline-none focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out" placeholder="Search for purchase..." value="<?php echo isset($_GET['purchase_search']) ? htmlspecialchars($_GET['purchase_search']) : ''; ?>">
                    </div>
                </form>

                <!-- Product Type Table -->
                <div class="tableScrollBar overflow-y-auto max-h-[495px]">
                    <div id="purchaseResults">
                        <?php include '../includes/admin_table_components/purchase_results.php'; ?>
                    </div>
                </div>

                <!-- Pagination Controls -->
                <div id="paginationContainer" class="flex justify-between items-center mt-3">
                    <?php include '../includes/admin_table_components/purchase_pagination.php'; ?>
                </div>
            </div>
        </div>

        <!-- Add this modal at the bottom of your file, before the scripts -->
        <div id="purchaseDetailsModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <div class="bg-white max-w-4xl p-6 rounded-md shadow-md text-center w-full">
                <h2 class="text-xl text-start text-gray-700 font-bold mb-4">Purchase Details</h2>

                <!-- Purchase Summary -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 text-start">
                    <div>
                        <p class="text-sm text-gray-500">Purchase ID</p>
                        <p class="font-medium" id="detailPurchaseID"></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Date</p>
                        <p class="font-medium" id="detailPurchaseDate"></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Admin</p>
                        <p class="font-medium" id="detailAdmin"></p>
                        <p class="text-sm text-gray-500" id="detailAdminEmail"></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Supplier</p>
                        <p class="font-medium" id="detailSupplier"></p>
                        <p class="text-sm text-gray-500" id="detailSupplierEmail"></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total Amount</p>
                        <p class="font-medium" id="detailTotalAmount"></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Tax</p>
                        <p class="font-medium" id="detailTax"></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Status</p>
                        <p class="font-medium" id="detailStatus"></p>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead>
                            <tr class="bg-gray-100 text-gray-600 text-sm">
                                <th class="p-3 text-start">Product</th>
                                <th class="p-3 text-start">Quantity</th>
                                <th class="p-3 text-start">Unit Price</th>
                                <th class="p-3 text-start">Total</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm" id="purchaseItems">
                            <!-- Items will be inserted here -->
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end mt-6">
                    <button id="closePurchaseDetailsBtn" class="px-4 py-2 bg-gray-200 text-black hover:bg-gray-300 rounded-sm">
                        Close
                    </button>
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