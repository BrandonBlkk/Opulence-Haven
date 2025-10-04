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

// Get Reservation Details
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = mysqli_real_escape_string($connect, $_GET['id']);
    $action = $_GET['action'];

    $response = ['success' => false];

    if ($action === 'getReservationDetails') {
        $query = "
            SELECT rd.*, r.*, rb.*, rtb.*, u.UserEmail, u.UserName, u.UserPhone
            FROM reservationdetailtb rd
            JOIN reservationtb r ON rd.ReservationID = r.ReservationID
            JOIN roomtb rb ON rd.RoomID = rb.RoomID
            JOIN roomtypetb rtb ON rb.RoomTypeID = rtb.RoomTypeID
            JOIN usertb u ON r.UserID = u.UserID
            WHERE rd.ReservationID = '$id'
        ";
        $result = $connect->query($query);

        if ($result && $result->num_rows > 0) {
            $reservationData = null;

            while ($row = $result->fetch_assoc()) {
                if (!$reservationData) {
                    $reservationData = [
                        'ReservationID' => $row['ReservationID'],
                        'UserEmail' => $row['UserEmail'] ?? null,
                        'FirstName' => $row['FirstName'] ?? null,
                        'LastName' => $row['LastName'] ?? null,
                        'Title' => $row['Title'] ?? null,
                        'UserPhone' => $row['UserPhone'] ?? null,
                        'ReservationDate' => $row['ReservationDate'] ?? null,
                        'ExpiryDate' => $row['ExpiryDate'] ?? null,
                        'CheckInDate' => $row['CheckInDate'] ?? null,
                        'CheckOutDate' => $row['CheckOutDate'] ?? null,
                        'TotalPrice' => $row['TotalPrice'] ?? 0,
                        'PointsDiscount' => $row['PointsDiscount'] ?? 0,
                        'PointsRedeemed' => $row['PointsRedeemed'] ?? 0,
                        'PointsEarned' => $row['PointsEarned'] ?? 0,
                        'Rooms' => []
                    ];
                }

                // Push each reserved room into Rooms array
                $reservationData['Rooms'][] = [
                    'RoomID' => $row['RoomID'],
                    'RoomName' => $row['RoomName'],
                    'RoomTypeID' => $row['RoomTypeID'],
                    'RoomType' => $row['RoomType'],
                    'RoomDescription' => $row['RoomDescription'],
                    'RoomCoverImage' => $row['RoomCoverImage'] ?? 'default.png',
                    'Adult' => $row['Adult'] ?? 0,
                    'Children' => $row['Children'] ?? 0,
                    'Price' => $row['Price'] ?? 0
                ];
            }

            $response['success'] = true;
            $response['reservation'] = $reservationData;
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
                <h2 class="text-xl text-gray-700 font-bold mb-4">User Reservation Overview</h2>
                <p>Monitor active reservations, process cancellations, and analyze booking trends to optimize resource allocation.</p>
            </div>

            <!-- Product Table -->
            <div class="overflow-x-auto">
                <!-- Product Search and Filter -->
                <form method="GET" class="my-4 flex items-start sm:items-center justify-between flex-col sm:flex-row gap-2 sm:gap-0">
                    <h1 class="text-lg text-gray-700 font-semibold text-nowrap">All Reservations <span class="text-gray-400 text-sm ml-2"><?php echo $bookingCount ?></span></h1>
                    <div class="flex flex-col sm:flex-row items-start sm:items-center w-full gap-2 sm:gap-0">
                        <input type="text" name="reservation_search" class="p-2 ml-0 sm:ml-5 border border-gray-300 rounded-md w-full outline-none focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out" placeholder="Search for reservation..." value="<?php echo isset($_GET['reservation_search']) ? htmlspecialchars($_GET['reservation_search']) : ''; ?>">
                        <div class="flex items-center">
                            <label for="sort" class="ml-0 sm:ml-4 mr-2 flex items-center cursor-pointer select-none">
                                <i class="ri-filter-2-line text-xl"></i>
                                <p>Filters</p>
                            </label>
                            <!-- Search and filter form -->
                            <form method="GET" class="flex flex-col md:flex-row items-center gap-4 mb-4">
                                <select name="sort" id="sort" class="border p-2 rounded text-sm outline-none">
                                    <option value="random">All Statuses</option>
                                    <option value="Confirmed" <?= ($filterStatus == 'Confirmed') ? 'selected' : '' ?>>Confirmed</option>
                                    <option value="Cancelled" <?= ($filterStatus == 'Cancelled') ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                            </form>
                        </div>
                    </div>
                </form>

                <!-- Reservation Table -->
                <div class="tableScrollBar overflow-y-auto max-h-[510px]">
                    <div id="reservationResults">
                        <?php include '../includes/admin_table_components/reservation_results.php'; ?>
                    </div>
                </div>

                <!-- Pagination Controls -->
                <div id="paginationContainer" class="flex justify-between items-center mt-3">
                    <?php include '../includes/admin_table_components/reservation_pagination.php'; ?>
                </div>
            </div>
        </div>

        <!-- Reservation Modal -->
        <div id="reservationModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible -translate-y-5 p-2 transition-all duration-300">
            <div class="reservationScrollBar bg-white rounded-xl max-w-4xl w-full p-6 animate-fade-in max-h-[92vh] overflow-y-auto overflow-x-hidden">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-800">Reservation Details</h3>
                    <button id="closeReservationDetailButton" class="text-gray-400 hover:text-gray-500">
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
                                <span class="text-sm font-medium text-gray-600" id="userName"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Phone:</span>
                                <span class="text-sm font-medium text-gray-600" id="userPhone"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Email:</span>
                                <span class="text-sm font-medium text-gray-600">
                                    <a href="#" id="userEmail" class="hover:underline"></a>
                                    <i class="ri-mail-fill"></i>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Reservation Date:</span>
                                <div class="flex flex-col text-right">
                                    <span class="text-sm font-medium text-gray-600" id="reservationDate"></span>
                                    <span class="text-xs font-semibold text-red-500" id="reservationExpiry"></span>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Room Information -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium text-gray-800 mb-3">Rooms Reserved</h4>
                        <div class="space-y-2 roomTypeSwiper" id="roomContainer">
                            <!-- Dynamically inserted rooms will appear here -->
                            <div class="swiper-wrapper"></div>
                            <div class="swiper-pagination mt-2"></div>
                        </div>
                    </div>

                    <!-- Pricing Breakdown -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium text-gray-800 mb-3">Pricing Breakdown</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600" id="roomRateLabel">Room Rate:</span>
                                <span class="text-sm font-medium text-gray-600" id="roomRate"></span>
                            </div>

                            <div class="flex justify-between hidden" id="pointsDiscountContainer">
                                <span class="text-sm text-gray-600" id="pointsDiscountLabel">Points Discount:</span>
                                <span class="text-sm font-medium text-green-600" id="pointsDiscount"></span>
                            </div>

                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Taxes & Fees:</span>
                                <span class="text-sm font-medium text-gray-600" id="taxesFees"></span>
                            </div>
                            <div class="border-t border-gray-200 pt-2 flex justify-between">
                                <span class="font-medium text-gray-800">Total:</span>
                                <span class="font-bold text-gray-600" id="totalPrice"></span>
                            </div>

                            <div class="pt-2 flex justify-between hidden" id="pointsEarnedContainer">
                                <span class="text-sm text-gray-600">Points Earned:</span>
                                <span class="text-sm font-medium text-blue-600" id="pointsEarned"></span>
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