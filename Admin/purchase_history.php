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
$response = ['success' => false];

// Only handle requests that include action and id
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = mysqli_real_escape_string($connect, $_GET['id']);
    $action = $_GET['action'];

    if ($action === 'getPurchaseDetails') {
        // Get purchase header with admin and supplier info
        $query = "
            SELECT p.*, a.FirstName, a.LastName, a.AdminEmail, s.SupplierName, s.SupplierEmail
            FROM purchasetb p
            LEFT JOIN admintb a ON p.AdminID = a.AdminID
            LEFT JOIN suppliertb s ON p.SupplierID = s.SupplierID
            WHERE p.PurchaseID = '$id'
            LIMIT 1
        ";
        $res = $connect->query($query);
        if ($res && $res->num_rows > 0) {
            $purchase = $res->fetch_assoc();
            $response['success'] = true;
            $response['purchase'] = $purchase;
        } else {
            $response['success'] = false;
            $response['message'] = 'Purchase not found';
        }
    } elseif ($action === 'getPurchaseItems') {
        // Get purchase items and product names
        $query = "
            SELECT pd.PurchaseID, pd.ProductID, pd.PurchaseUnitQuantity AS Quantity, pd.PurchaseUnitPrice AS UnitPrice, p.Title AS ProductName
            FROM purchasedetailtb pd
            LEFT JOIN producttb p ON pd.ProductID = p.ProductID
            WHERE pd.PurchaseID = '$id'
        ";
        $res = $connect->query($query);
        $items = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                // Normalize numeric values
                $row['Quantity'] = (float)$row['Quantity'];
                $row['UnitPrice'] = (float)$row['UnitPrice'];
                $items[] = $row;
            }
            $response['success'] = true;
            $response['items'] = $items;
        } else {
            $response['success'] = false;
            $response['message'] = 'Error fetching items';
        }
    } else {
        $response['success'] = false;
        $response['message'] = 'Invalid action';
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

        <!-- Purchase Details Modal -->
        <div id="purchaseDetailsModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible -translate-y-5 p-2 transition-all duration-300">
            <div class="reservationScrollBar bg-white rounded-xl max-w-4xl w-full p-6 animate-fade-in max-h-[92vh] overflow-y-auto overflow-x-hidden">
                <!-- Header -->
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-800">Purchase Details</h3>
                    <button id="closePurchaseDetailsBtn" class="text-gray-400 hover:text-gray-500">
                        <i class="ri-close-line text-xl"></i>
                    </button>
                </div>

                <div class="space-y-3">
                    <!-- Purchase Summary -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium text-gray-800 mb-3">Summary</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <p class="text-sm text-gray-500">Purchase ID</p>
                                <p class="text-sm font-medium text-gray-600" id="detailPurchaseID"></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Date</p>
                                <p class="text-sm font-medium text-gray-600" id="detailPurchaseDate"></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Purchased By</p>
                                <p class="text-sm font-medium text-gray-600" id="detailAdmin"></p>
                                <p class="text-xs text-gray-500" id="detailAdminEmail"></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Supplier</p>
                                <p class="text-sm font-medium text-gray-600" id="detailSupplier"></p>
                                <p class="text-xs text-gray-500" id="detailSupplierEmail"></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Total Amount</p>
                                <p class="text-sm font-medium text-gray-600" id="detailTotalAmount"></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Tax</p>
                                <p class="text-sm font-medium text-gray-600" id="detailTax"></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Status</p>
                                <p class="text-sm font-medium text-gray-600" id="detailStatus"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium text-gray-800 mb-3">Items Purchased</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white border rounded-lg overflow-hidden">
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
                    </div>
                </div>

                <!-- Footer Buttons -->
                <div class="flex justify-end gap-3 mt-6">
                    <button id="printVoucherBtn" class="px-4 py-2 bg-amber-500 text-white hover:bg-amber-600 rounded-sm transition-colors duration-200 select-none">
                        <i class="ri-printer-line mr-1"></i> Print Voucher
                    </button>
                    <button id="downloadVoucherBtn" class="px-4 py-2 bg-green-600 text-white hover:bg-green-700 rounded-sm transition-colors duration-200 select-none">
                        <i class="ri-download-line mr-1"></i> Download Voucher
                    </button>
                </div>
            </div>
        </div>

        <script>
            // Print Voucher
            document.getElementById("printVoucherBtn").addEventListener("click", function() {
                const contentToPrint = document.querySelector("#purchaseDetailsModal .space-y-3").innerHTML; // only details
                const newWindow = window.open("", "_blank");
                newWindow.document.write(`
        <html>
            <head>
                <title>Purchase Voucher</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 20px; }
                    h3 { margin-bottom: 10px; }
                    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                    table, th, td { border: 1px solid #ddd; }
                    th, td { padding: 8px; text-align: left; }
                    th { background: #f5f5f5; }
                </style>
            </head>
            <body>
                <h3>Purchase Voucher</h3>
                ${contentToPrint}
            </body>
        </html>
    `);
                newWindow.document.close();
                newWindow.print();
            });

            // Download Voucher
            document.getElementById("downloadVoucherBtn").addEventListener("click", function() {
                const contentToDownload = document.querySelector("#purchaseDetailsModal .space-y-3").innerHTML;
                const blob = new Blob([`
        <html>
            <head>
                <title>Purchase Voucher</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 20px; }
                    h3 { margin-bottom: 10px; }
                    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                    table, th, td { border: 1px solid #ddd; }
                    th, td { padding: 8px; text-align: left; }
                    th { background: #f5f5f5; }
                </style>
            </head>
            <body>
                <h3>Purchase Voucher</h3>
                ${contentToDownload}
            </body>
        </html>
        `], {
                    type: "text/html"
                });

                const link = document.createElement("a");
                link.href = URL.createObjectURL(blob);
                link.download = "purchase-voucher.html";
                link.click();
                URL.revokeObjectURL(link.href);
            });
        </script>
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