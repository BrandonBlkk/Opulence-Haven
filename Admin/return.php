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

// Get Return Details
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = mysqli_real_escape_string($connect, $_GET['id']);
    $action = $_GET['action'];

    $response = ['success' => false];

    if ($action === 'getReturnDetails') {
        $query = "
            SELECT r.ReturnID, r.RequestDate, r.Status, r.Remarks,
                   u.UserEmail, u.UserName, u.UserPhone,
                   p.Title, pi.ImageAdminPath AS ProductImage
            FROM returntb r
            JOIN usertb u ON r.UserID = u.UserID
            JOIN producttb p ON r.ProductID = p.ProductID
            JOIN productimagetb pi ON p.ProductID = pi.ProductID
            WHERE r.ReturnID = '$id'
        ";

        $result = $connect->query($query);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $response['success'] = true;
            $response['return'] = [
                'ReturnID' => $row['ReturnID'],
                'UserName' => $row['UserName'],
                'UserPhone' => $row['UserPhone'],
                'UserEmail' => $row['UserEmail'],
                'RequestDate' => $row['RequestDate'],
                'Status' => $row['Status'],
                'Title' => $row['Title'],
                'Remarks' => $row['Remarks'],
                'ProductImage' => $row['ProductImage']
            ];
        } else {
            $response['message'] = "No return found with that ID.";
        }
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
                <h2 class="text-xl text-gray-700 font-bold mb-4">User Return Overview</h2>
                <p>Track user return requests, review product conditions, and manage refund or replacement processes to ensure smooth post-purchase service.</p>
            </div>

            <!-- Return Table -->
            <div class="overflow-x-auto">
                <!-- Return Search and Filter -->
                <form method="GET" class="my-4 flex items-start sm:items-center justify-between flex-col sm:flex-row gap-2 sm:gap-0">
                    <h1 class="text-lg text-gray-700 font-semibold text-nowrap">All Returns <span class="text-gray-400 text-sm ml-2"><?php echo $returnCount ?></span></h1>
                    <div class="flex flex-col sm:flex-row items-start sm:items-center w-full gap-2 sm:gap-0">
                        <input type="text" name="return_search" class="p-2 ml-0 sm:ml-5 border border-gray-300 rounded-md w-full outline-none focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out" placeholder="Search for return..." value="<?php echo isset($_GET['return_search']) ? htmlspecialchars($_GET['return_search']) : ''; ?>">
                        <div class="flex items-center">
                            <label for="sort" class="ml-0 sm:ml-4 mr-2 flex items-center cursor-pointer select-none">
                                <i class="ri-filter-2-line text-xl"></i>
                                <p>Filters</p>
                            </label>
                            <!-- Search and filter form -->
                            <form method="GET" class="flex flex-col md:flex-row items-center gap-4 mb-4">
                                <select name="sort" id="sort" class="border p-2 rounded text-sm outline-none">
                                    <option value="random">All Statuses</option>
                                    <option value="Pending" <?= ($filterStatus == 'Pending') ? 'selected' : '' ?>>Pending</option>
                                    <option value="Confirmed" <?= ($filterStatus == 'Confirmed') ? 'selected' : '' ?>>Confirmed</option>
                                    <option value="Rejected" <?= ($filterStatus == 'Rejected') ? 'selected' : '' ?>>Rejected</option>
                                </select>
                            </form>
                        </div>
                    </div>
                </form>

                <!-- Return Table -->
                <div class="tableScrollBar overflow-y-auto max-h-[510px]">
                    <div id="returnResults">
                        <?php include '../includes/admin_table_components/return_results.php'; ?>
                    </div>
                </div>

                <!-- Pagination Controls -->
                <div id="paginationContainer" class="flex justify-between items-center mt-3">
                    <?php include '../includes/admin_table_components/return_pagination.php'; ?>
                </div>
            </div>
        </div>

        <!-- Return Modal -->
        <div id="returnModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible -translate-y-5 p-2 transition-all duration-300">
            <div class="reservationScrollBar bg-white rounded-xl max-w-4xl w-full p-6 animate-fade-in max-h-[92vh] overflow-y-auto overflow-x-hidden">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-800">Return Details</h3>
                    <button id="closeReturnDetailButton" class="text-gray-400 hover:text-gray-500">
                        <i class="ri-close-line text-xl"></i>
                    </button>
                </div>

                <div class="space-y-3">
                    <!-- User Information -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium text-gray-800 mb-3">User Information</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Name:</span>
                                <span class="text-sm font-medium text-gray-600" id="returnUserName"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Phone:</span>
                                <span class="text-sm font-medium text-gray-600" id="returnUserPhone"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Email:</span>
                                <span class="text-sm font-medium text-gray-600">
                                    <a href="#" id="returnUserEmail" class="hover:underline"></a>
                                    <i class="ri-mail-fill"></i>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Request Date:</span>
                                <span class="text-sm font-medium text-gray-600" id="returnRequestDate"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Status:</span>
                                <span class="text-sm font-semibold text-gray-800" id="returnStatus"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Product Information -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium text-gray-800 mb-3">Product Information</h4>
                        <div class="flex flex-col md:flex-row gap-4">
                            <div class="md:w-1/3">
                                <img id="returnProductImage" src="" alt="Product" class="w-full h-40 object-cover rounded-lg">
                            </div>
                            <div class="md:w-2/3 space-y-1">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Product Name:</span>
                                    <span class="text-sm font-medium text-gray-700" id="returnProductName"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-4">
                        <button id="rejectReturnBtn" class="bg-red-500 text-white px-4 py-2 select-none hover:bg-red-600 rounded-sm">
                            Reject
                        </button>
                        <button id="confirmReturnBtn" class="bg-amber-500 text-white px-4 py-2 select-none hover:bg-amber-600 rounded-sm">
                            Confirm
                        </button>
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