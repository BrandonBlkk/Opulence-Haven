<?php
session_start();
include('../config/dbConnection.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

// Timezone 
date_default_timezone_set('Asia/Yangon');

$username = $_SESSION["UserName"];

if (!isset($_SESSION["AdminEmail"])) {
    echo "<script>window.alert('Login first! You cannot direct access the admin info.')</script>";
    echo "<script>window.location = 'AdminSignIn.php'</script>";
}

// Check if there's a welcome message to display
if (isset($_SESSION['welcome_message'])) {
    $welcomeMessage = $_SESSION['welcome_message'];
    unset($_SESSION['welcome_message']); //Show the message only once
}

// Query for total stock
$totalStockQuery = "SELECT SUM(Stock) AS TotalStock FROM producttb";
$totalStockResult = $connect->query($totalStockQuery);
$totalStock = $totalStockResult->fetch_assoc()['TotalStock'];

// Query for low stock (e.g., stock less than 10)
$lowStockQuery = "SELECT COUNT(*) AS LowStock FROM producttb WHERE Stock < 10";
$lowStockResult = $connect->query($lowStockQuery);
$lowStock = $lowStockResult->fetch_assoc()['LowStock'];

// Query for out of stock
$outOfStockQuery = "SELECT COUNT(*) AS OutOfStock FROM producttb WHERE Stock = 0";
$outOfStockResult = $connect->query($outOfStockQuery);
$outOfStock = $outOfStockResult->fetch_assoc()['OutOfStock'];

$roomQuery = "SELECT COUNT(*) as count FROM roomtb WHERE RoomStatus = 'Available'";
$roomResult = $connect->query($roomQuery);
$roomCount = $roomResult->fetch_assoc()['count'];
if ($roomCount > 0) {
    $roomAvailable = $roomCount;
}

$roomQuery = "SELECT COUNT(*) as count FROM reservationtb WHERE Status = 'Confirmed'";
$roomResult = $connect->query($roomQuery);
$roomCount = $roomResult->fetch_assoc()['count'];
if ($roomCount > 0) {
    $roomReserved = $roomCount;
}

// Assuming you have these values from your database:
$totalRooms = $roomAvailable;
$roomReserved = $roomReserved;
$allAvailableRooms = $totalRooms - $roomReserved; // Available rooms

// Calculate percentages
$bookedPercentage = ($roomReserved / $totalRooms) * 100;
$availablePercentage = ($allAvailableRooms / $totalRooms) * 100;
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-100">
    <?php
    include('../includes/admin_navbar.php');
    ?>

    <!-- Welcome message -->
    <?php if (isset($welcomeMessage)): ?>
        <div id="welcomeAlert" class="fixed -top-1 opacity-0 right-3 z-50 transition-all duration-200">
            <div class="flex items-center gap-3 p-3 rounded-lg shadow-lg bg-white backdrop-blur-sm border border-gray-200">
                <a href="../Admin/AdminDashboard.php">
                    <img src="../UserImages/Screenshot_2024-11-29_201534-removebg-preview.png" class="w-16 select-none" alt="Logo">
                </a>
                <div>
                    <p class="font-medium text-gray-800"><?php echo htmlspecialchars($welcomeMessage); ?> to <span class="font-bold text-amber-600">Opulence Haven</span></p>
                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($username); ?></p>
                </div>
                <button onclick="closeWelcomeAlert()" class="ml-2 text-gray-400 hover:text-amber-600 transition-colors">
                    <i class="ri-close-line text-lg"></i>
                </button>
            </div>
        </div>

        <script>
            // Show welcome alert with animation
            const welcomeAlert = document.getElementById('welcomeAlert');

            // Trigger the animation after a small delay to allow DOM to render
            setTimeout(() => {
                welcomeAlert.classList.remove("-top-1", "opacity-0");
                welcomeAlert.classList.add("opacity-100", "top-3");

                // Hide after 5 seconds
                setTimeout(() => {
                    welcomeAlert.classList.add("translate-x-full", "-right-full");
                    setTimeout(() => welcomeAlert.remove(), 200);
                }, 5000);
            }, 100);

            function closeWelcomeAlert() {
                welcomeAlert.classList.add("translate-x-full", "-right-full");
                setTimeout(() => welcomeAlert.remove(), 200);
            }
        </script>
    <?php endif; ?>

    <div class="p-3 ml-0 md:ml-[250px] min-w-[380px]">

        <!-- Left Side Content -->
        <div class="w-full bg-white p-3 rounded-sm">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end">
                <div>
                    <h2 class="text-lg font-bold text-gray-700 mb-4">Website Maintenance Mode</h2>
                    <!-- Status Message -->
                    <div id="statusMessage" class="flex items-center gap-2 mb-4">
                        <span id="statusIcon" class="h-3 w-3 rounded-full"></span>
                        <p id="statusText" class="text-sm text-gray-600"></p>
                    </div>
                </div>
                <!-- Toggle Button -->
                <div class="flex items-center justify-center gap-2 mb-4">
                    <label class="font-medium text-gray-600">Enable Maintenance Mode</label>
                    <div class="relative w-12 flex items-center select-none transition duration-200">
                        <label class="inline-flex items-center cursor-pointer">
                            <input
                                type="checkbox"
                                id="toggleSwitch"
                                class="sr-only peer"
                                onclick="confirmToggle()"
                                <?= ($role !== '1') ? 'disabled' : ''; ?>>
                            <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 rounded-full peer dark:bg-green-500 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-500"></div>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Confirmation Modal -->
        <div id="confirmModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <div class="bg-white p-6 rounded-lg shadow-lg w-96">
                <h3 class="text-lg font-bold text-gray-800">Confirm Action</h3>
                <p class="text-sm text-gray-600 mb-6">Are you sure you want to change the website status?</p>
                <div class="text-xs text-gray-500 mb-5">
                    <p class="flex items-start gap-1">
                        <i class="ri-information-line mt-0.5"></i>
                        <span>This action will immediately affect all visitors. Maintenance mode shows a temporary modal.</span>
                    </p>
                </div>
                <div class="flex justify-end gap-4 select-none">
                    <button
                        onclick="cancelToggle()"
                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded-sm hover:bg-gray-300">
                        Cancel
                    </button>
                    <button
                        onclick="toggleWebsiteStatus()"
                        class="px-4 py-2 bg-red-500 text-white rounded-sm hover:bg-red-600">
                        Confirm
                    </button>
                </div>
            </div>
        </div>

        <script>
            let pendingToggle = false;

            // Initialize the website status from localStorage
            document.addEventListener('DOMContentLoaded', () => {
                const isMaintenanceMode = localStorage.getItem('maintenanceMode') === 'true';
                const toggleSwitch = document.getElementById('toggleSwitch');
                toggleSwitch.checked = isMaintenanceMode;
                updateStatus(isMaintenanceMode);
            });

            // Show confirmation modal
            function confirmToggle() {
                const toggleSwitch = document.getElementById('toggleSwitch');
                pendingToggle = toggleSwitch.checked; // Store the intended toggle state
                toggleSwitch.checked = !toggleSwitch.checked; // Revert temporary change
                darkOverlay2.classList.remove('opacity-0', 'invisible');
                darkOverlay2.classList.add('opacity-100');
                document.getElementById('confirmModal').classList.remove('opacity-0', 'invisible', '-translate-y-5');
            }

            // Cancel the toggle action
            function cancelToggle() {
                darkOverlay2.classList.add('opacity-0', 'invisible');
                darkOverlay2.classList.remove('opacity-100');
                document.getElementById('confirmModal').classList.add('opacity-0', 'invisible', '-translate-y-5');
            }

            // Confirm and toggle the website status
            function toggleWebsiteStatus() {
                const toggleSwitch = document.getElementById('toggleSwitch');
                toggleSwitch.checked = pendingToggle; // Apply the stored toggle state
                localStorage.setItem('maintenanceMode', pendingToggle);
                updateStatus(pendingToggle);
                darkOverlay2.classList.add('opacity-0', 'invisible');
                darkOverlay2.classList.remove('opacity-100');
                document.getElementById('confirmModal').classList.add('opacity-0', 'invisible', '-translate-y-5'); // Hide the modal
            }

            // Update the UI based on the website status
            function updateStatus(isMaintenanceMode) {
                const statusIcon = document.getElementById('statusIcon');
                const statusText = document.getElementById('statusText');

                if (isMaintenanceMode) {
                    // Maintenance Mode
                    statusIcon.className = 'h-3 w-3 rounded-full bg-red-500 animate-pulse';
                    statusText.innerHTML = 'The website is currently <span class="font-semibold text-red-600">in maintenance mode</span>.';
                } else {
                    // Active Mode
                    statusIcon.className = 'h-3 w-3 rounded-full bg-green-500 animate-pulse';
                    statusText.innerHTML = 'The website is currently <span class="font-semibold text-green-600">active</span>.';
                }
            }
        </script>

        <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 mt-3">
            <!-- Total Booking -->
            <?php
            // Get current period counts
            $currentMonthQuery = "SELECT COUNT(*) as count FROM reservationtb 
                     WHERE ReservationDate >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
            $currentWeekQuery = "SELECT COUNT(*) as count FROM reservationtb 
                    WHERE ReservationDate >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
            $currentDayQuery = "SELECT COUNT(*) as count FROM reservationtb 
                   WHERE ReservationDate >= DATE_SUB(NOW(), INTERVAL 1 DAY)";

            $currentMonthResult = $connect->query($currentMonthQuery);
            $currentWeekResult = $connect->query($currentWeekQuery);
            $currentDayResult = $connect->query($currentDayQuery);

            $currentMonthCount = $currentMonthResult->fetch_assoc()['count'];
            $currentWeekCount = $currentWeekResult->fetch_assoc()['count'];
            $currentDayCount = $currentDayResult->fetch_assoc()['count'];

            // Get previous period counts for comparison
            $prevMonthQuery = "SELECT COUNT(*) as count FROM reservationtb 
                  WHERE ReservationDate BETWEEN DATE_SUB(NOW(), INTERVAL 2 MONTH) AND DATE_SUB(NOW(), INTERVAL 1 MONTH)";
            $prevWeekQuery = "SELECT COUNT(*) as count FROM reservationtb 
                 WHERE ReservationDate BETWEEN DATE_SUB(NOW(), INTERVAL 2 WEEK) AND DATE_SUB(NOW(), INTERVAL 1 WEEK)";
            $prevDayQuery = "SELECT COUNT(*) as count FROM reservationtb 
                WHERE ReservationDate BETWEEN DATE_SUB(NOW(), INTERVAL 2 DAY) AND DATE_SUB(NOW(), INTERVAL 1 DAY)";

            $prevMonthResult = $connect->query($prevMonthQuery);
            $prevWeekResult = $connect->query($prevWeekQuery);
            $prevDayResult = $connect->query($prevDayQuery);

            $prevMonthCount = $prevMonthResult->fetch_assoc()['count'];
            $prevWeekCount = $prevWeekResult->fetch_assoc()['count'];
            $prevDayCount = $prevDayResult->fetch_assoc()['count'];

            // Calculate percentage changes
            function calculateChange($current, $previous)
            {
                if ($previous == 0) {
                    return $current > 0 ? 100 : 0; // Handle division by zero
                }
                return (($current - $previous) / $previous) * 100;
            }

            $monthlyChange = calculateChange($currentMonthCount, $prevMonthCount);
            $weeklyChange = calculateChange($currentWeekCount, $prevWeekCount);
            $dailyChange = calculateChange($currentDayCount, $prevDayCount);
            ?>

            <div class="bg-white rounded-sm p-3">
                <h2 class="text-lg font-bold text-gray-700">Total Reservation</h2>
                <p class="text-sm text-gray-500 mb-4">Total number of reservations recorded.</p>
                <div class="grid grid-cols-3 gap-4 mb-4">
                    <div class="text-center">
                        <h3 class="text-xl font-semibold text-blue-600"><?= number_format($currentMonthCount) ?></h3>
                        <p class="text-gray-500 text-xs">Monthly</p>
                        <p class="<?= $monthlyChange >= 0 ? 'text-green-500' : 'text-red-500' ?> text-sm">
                            <?= $monthlyChange >= 0 ? '↑' : '↓' ?> <?= number_format(abs($monthlyChange), 2) ?>%
                        </p>
                    </div>
                    <div class="text-center">
                        <h3 class="text-xl font-semibold text-red-600"><?= number_format($currentWeekCount) ?></h3>
                        <p class="text-gray-500 text-xs">Weekly</p>
                        <p class="<?= $weeklyChange >= 0 ? 'text-green-500' : 'text-red-500' ?> text-sm">
                            <?= $weeklyChange >= 0 ? '↑' : '↓' ?> <?= number_format(abs($weeklyChange), 2) ?>%
                        </p>
                    </div>
                    <div class="text-center">
                        <h3 class="text-xl font-semibold text-green-600"><?= number_format($currentDayCount) ?></h3>
                        <p class="text-gray-500 text-xs">Daily (Avg)</p>
                        <p class="<?= $dailyChange >= 0 ? 'text-green-500' : 'text-red-500' ?> text-sm">
                            <?= $dailyChange >= 0 ? '↑' : '↓' ?> <?= number_format(abs($dailyChange), 2) ?>%
                        </p>
                    </div>
                </div>
            </div>

            <!-- Room Available -->
            <div class="bg-white rounded-sm p-3">
                <h2 class="text-lg font-bold text-gray-700">Room Available</h2>
                <p class="text-sm text-gray-500 mb-4">Current availability of rooms across different time periods.</p>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="text-center">
                        <h3 class="text-xl font-semibold text-red-600"><?= $roomReserved ?></h3>
                        <p class="text-gray-500 text-xs">Booked</p>
                        <p class="<?= $bookedPercentage >= 0 ? 'text-green-500' : 'text-red-500' ?> text-sm">
                            <?= $bookedPercentage >= 0 ? '↑' : '↓' ?> <?= number_format(abs($bookedPercentage), 2) ?>%
                        </p>
                    </div>
                    <div class="text-center">
                        <h3 class="text-xl font-semibold text-green-600"><?= $roomAvailable ?></h3>
                        <p class="text-gray-500 text-xs">Available</p>
                        <p class="<?= $availablePercentage >= 0 ? 'text-green-500' : 'text-red-500' ?> text-sm">
                            <?= $availablePercentage >= 0 ? '↑' : '↓' ?> <?= number_format(abs($availablePercentage), 2) ?>%
                        </p>
                    </div>
                </div>
            </div>

            <!-- Stock Available -->
            <div class="bg-white md:col-span-2 lg:col-span-1 rounded-sm p-3">
                <h2 class="text-lg font-bold text-gray-700">Stock Available</h2>
                <p class="text-sm text-gray-500 mb-4">Current stock status across product types.</p>
                <div class="grid grid-cols-3 gap-4 mb-4">
                    <div class="text-center">
                        <h3 class="text-xl font-semibold text-blue-600">
                            <?php echo number_format($totalStock); ?>
                        </h3>
                        <p class="text-gray-500 text-xs">Total Stock</p>
                    </div>
                    <div class="text-center">
                        <h3 class="text-xl font-semibold text-red-600">
                            <?php echo number_format($lowStock); ?>
                        </h3>
                        <p class="text-gray-500 text-xs">Low Stock</p>
                    </div>
                    <div class="text-center">
                        <h3 class="text-xl font-semibold text-green-600">
                            <?php echo number_format($outOfStock); ?>
                        </h3>
                        <p class="text-gray-500 text-xs">Out of Stock</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <?php
            // Function to get revenue data for the last 30 days
            function getRevenueData($connect, $type, $previous = false)
            {
                $endDate = $previous ? date('Y-m-d', strtotime('-31 days')) : date('Y-m-d');
                $startDate = $previous ? date('Y-m-d', strtotime('-61 days')) : date('Y-m-d', strtotime('-30 days'));

                if ($type === 'rooms') {
                    $query = "
            SELECT 
                DATE(ReservationDate) AS date,
                SUM(TotalPrice) AS revenue
            FROM 
                reservationtb
            WHERE 
                ReservationDate BETWEEN ? AND ?
                AND Status = 'Confirmed'
            GROUP BY 
                DATE(ReservationDate)
            ORDER BY 
                date ASC
        ";
                } else { // orders
                    $query = "
            SELECT 
                DATE(OrderDate) AS date,
                SUM(TotalPrice) AS revenue
            FROM 
                ordertb
            WHERE 
                OrderDate BETWEEN ? AND ?
                AND Status = 'Confirmed'
            GROUP BY 
                DATE(OrderDate)
            ORDER BY 
                date ASC
        ";
                }

                $stmt = $connect->prepare($query);
                $stmt->bind_param('ss', $startDate, $endDate);
                $stmt->execute();
                $result = $stmt->get_result();

                $data = [];
                $totalRevenue = 0;

                while ($row = $result->fetch_assoc()) {
                    $data[$row['date']] = $row['revenue'];
                    $totalRevenue += $row['revenue'];
                }

                // Fill in missing dates with 0 revenue
                $period = new DatePeriod(
                    new DateTime($startDate),
                    new DateInterval('P1D'),
                    new DateTime($endDate)
                );

                $completeData = [];
                foreach ($period as $date) {
                    $dateStr = $date->format('Y-m-d');
                    $completeData[] = [
                        'date' => $dateStr,
                        'revenue' => isset($data[$dateStr]) ? $data[$dateStr] : 0
                    ];
                }

                return [
                    'dailyData' => $completeData,
                    'totalRevenue' => $totalRevenue
                ];
            }

            // Get current and previous data (room reservations and orders)
            $currentRoomData = getRevenueData($connect, 'rooms');
            $previousRoomData = getRevenueData($connect, 'rooms', true);
            $currentOrderData = getRevenueData($connect, 'orders');
            $previousOrderData = getRevenueData($connect, 'orders', true);

            // Calculate amounts based on initial filter (rooms)
            $monthlyRevenue = $currentRoomData['totalRevenue'];
            $weeklyRevenue = array_sum(array_slice(array_column($currentRoomData['dailyData'], 'revenue'), -7));

            // Get the number of days in the current month
            $daysInMonth = date('t');
            $dailyAvg = $monthlyRevenue / $daysInMonth;

            // Calculate percentage changes for rooms
            $roomPrevMonthly = $previousRoomData['totalRevenue'];
            $roomPrevWeekly = array_sum(array_slice(array_column($previousRoomData['dailyData'], 'revenue'), -7));
            $roomPrevDailyAvg = $roomPrevMonthly / $daysInMonth;

            $monthlyChange = $roomPrevMonthly != 0 ?
                (($monthlyRevenue - $roomPrevMonthly) / $roomPrevMonthly) * 100 : 0;
            $weeklyChange = $roomPrevWeekly != 0 ?
                (($weeklyRevenue - $roomPrevWeekly) / $roomPrevWeekly) * 100 : 0;
            $dailyChange = $roomPrevDailyAvg != 0 ?
                (($dailyAvg - $roomPrevDailyAvg) / $roomPrevDailyAvg) * 100 : 0;

            // Calculate percentage changes for orders (will be used when filter changes)
            $orderMonthlyRevenue = $currentOrderData['totalRevenue'];
            $orderWeeklyRevenue = array_sum(array_slice(array_column($currentOrderData['dailyData'], 'revenue'), -7));
            $orderDailyAvg = $orderMonthlyRevenue / $daysInMonth;

            $orderPrevMonthly = $previousOrderData['totalRevenue'];
            $orderPrevWeekly = array_sum(array_slice(array_column($previousOrderData['dailyData'], 'revenue'), -7));
            $orderPrevDailyAvg = $orderPrevMonthly / $daysInMonth;

            $orderMonthlyChange = $orderPrevMonthly != 0 ?
                (($orderMonthlyRevenue - $orderPrevMonthly) / $orderPrevMonthly) * 100 : 0;
            $orderWeeklyChange = $orderPrevWeekly != 0 ?
                (($orderWeeklyRevenue - $orderPrevWeekly) / $orderPrevWeekly) * 100 : 0;
            $orderDailyChange = $orderPrevDailyAvg != 0 ?
                (($orderDailyAvg - $orderPrevDailyAvg) / $orderPrevDailyAvg) * 100 : 0;

            // Prepare labels like the first chart (1, 5, 10, 15, 20, 25, 30 plus any days with data)
            $allRevenueData = array_merge($currentRoomData['dailyData'], $currentOrderData['dailyData']);
            $daysWithData = [];

            foreach ($allRevenueData as $item) {
                if ($item['revenue'] > 0) {
                    $date = new DateTime($item['date']);
                    $daysWithData[] = (int)$date->format('d');
                }
            }

            $selectedDays = [1, 5, 10, 15, 20, 25, 30];
            $allDaysToShow = array_unique(array_merge($selectedDays, $daysWithData));
            sort($allDaysToShow);

            // Create date labels in 'd M' format
            $dateLabels = [];
            foreach ($allDaysToShow as $day) {
                $date = DateTime::createFromFormat('j', $day);
                $dateLabels[] = $date->format('d M');
            }

            // Prepare room and order datasets with all data points
            $roomDataset = [];
            $orderDataset = [];
            foreach ($dateLabels as $label) {
                $dayNum = (int)substr($label, 0, 2);
                $foundRoom = false;
                $foundOrder = false;

                foreach ($currentRoomData['dailyData'] as $item) {
                    $date = new DateTime($item['date']);
                    if ((int)$date->format('d') === $dayNum) {
                        $roomDataset[] = $item['revenue'];
                        $foundRoom = true;
                        break;
                    }
                }
                if (!$foundRoom) {
                    $roomDataset[] = 0;
                }

                foreach ($currentOrderData['dailyData'] as $item) {
                    $date = new DateTime($item['date']);
                    if ((int)$date->format('d') === $dayNum) {
                        $orderDataset[] = $item['revenue'];
                        $foundOrder = true;
                        break;
                    }
                }
                if (!$foundOrder) {
                    $orderDataset[] = 0;
                }
            }
            ?>

            <div class="bg-white rounded-sm p-3 mt-3">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end">
                    <h2 class="text-lg font-bold text-gray-700">Sales Revenue</h2>
                    <select
                        id="revenueFilter"
                        class="text-xs border rounded px-2 py-1 bg-white text-gray-700 outline-none focus:outline-none"
                        onchange="updateRevenueChart()">
                        <option value="rooms">Room Reservations</option>
                        <option value="orders">Orders</option>
                    </select>
                </div>
                <p class="text-sm text-gray-500 mb-4">In last 30 days revenue from rent.</p>
                <div class="grid grid-cols-3 gap-4 mb-4">
                    <div class="text-center">
                        <h3 id="monthlyRevenue" class="text-xl font-semibold text-blue-600">$<?= number_format($monthlyRevenue, 2) ?></h3>
                        <p class="text-gray-500 text-xs">Monthly</p>
                        <p id="monthlyChange" class="<?= $monthlyChange >= 0 ? 'text-green-500' : 'text-red-500' ?> text-sm">
                            <?= $monthlyChange >= 0 ? '↑' : '↓' ?> <?= number_format(abs($monthlyChange), 2) ?>%
                        </p>
                    </div>
                    <div class="text-center">
                        <h3 id="weeklyRevenue" class="text-xl font-semibold text-red-600">$<?= number_format($weeklyRevenue, 2) ?></h3>
                        <p class="text-gray-500 text-xs">Weekly</p>
                        <p id="weeklyChange" class="<?= $weeklyChange >= 0 ? 'text-green-500' : 'text-red-500' ?> text-sm">
                            <?= $weeklyChange >= 0 ? '↑' : '↓' ?> <?= number_format(abs($weeklyChange), 2) ?>%
                        </p>
                    </div>
                    <div class="text-center">
                        <h3 id="dailyRevenue" class="text-xl font-semibold text-green-600">$<?= number_format($dailyAvg, 2) ?></h3>
                        <p class="text-gray-500 text-xs">Daily (Avg)</p>
                        <p id="dailyChange" class="<?= $dailyChange >= 0 ? 'text-green-500' : 'text-red-500' ?> text-sm">
                            <?= $dailyChange >= 0 ? '↑' : '↓' ?> <?= number_format(abs($dailyChange), 2) ?>%
                        </p>
                    </div>
                </div>
                <div class="bg-white rounded-sm p-3 mt-3">
                    <div class="chart-container" style="position: relative; height: auto; width: 100%">
                        <canvas id="salesRevenueChart"></canvas>
                    </div>
                </div>
            </div>

            <script>
                // Store the chart instance and revenue data
                let revenueChart;
                const roomMonthlyRevenue = <?= $currentRoomData['totalRevenue'] ?>;
                const orderMonthlyRevenue = <?= $currentOrderData['totalRevenue'] ?>;
                const roomWeeklyRevenue = <?= array_sum(array_slice(array_column($currentRoomData['dailyData'], 'revenue'), -7)) ?>;
                const orderWeeklyRevenue = <?= array_sum(array_slice(array_column($currentOrderData['dailyData'], 'revenue'), -7)) ?>;
                const roomDailyAvg = <?= $currentRoomData['totalRevenue'] / 30 ?>;
                const orderDailyAvg = <?= $currentOrderData['totalRevenue'] / 30 ?>;

                const roomMonthlyChange = <?= $monthlyChange ?>;
                const orderMonthlyChange = <?= $orderMonthlyChange ?>;
                const roomWeeklyChange = <?= $weeklyChange ?>;
                const orderWeeklyChange = <?= $orderWeeklyChange ?>;
                const roomDailyChange = <?= $dailyChange ?>;
                const orderDailyChange = <?= $orderDailyChange ?>;

                // Initialize the chart
                document.addEventListener('DOMContentLoaded', function() {
                    const ctx = document.getElementById('salesRevenueChart').getContext('2d');

                    revenueChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: <?= json_encode($dateLabels) ?>,
                            datasets: [{
                                label: 'Revenue ($)',
                                data: <?= json_encode($roomDataset) ?>,
                                backgroundColor: 'rgba(59, 130, 246, 0.5)',
                                borderColor: '#3B82F6',
                                borderWidth: 1,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return `$${context.raw.toLocaleString()}`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    grid: {
                                        display: false
                                    },
                                    title: {
                                        display: true,
                                        text: 'Date',
                                        font: {
                                            weight: 'bold'
                                        }
                                    },
                                },
                                y: {
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Revenue ($)',
                                        font: {
                                            weight: 'bold'
                                        }
                                    },
                                }
                            }
                        }
                    });
                });

                // Function to update the chart and summary stats based on filter
                function updateRevenueChart() {
                    const filter = document.getElementById('revenueFilter').value;
                    const roomDataset = <?= json_encode($roomDataset) ?>;
                    const orderDataset = <?= json_encode($orderDataset) ?>;

                    // Update chart data
                    revenueChart.data.datasets[0].data = filter === 'rooms' ? roomDataset : orderDataset;
                    revenueChart.update();

                    // Update summary statistics
                    const monthlyElement = document.getElementById('monthlyRevenue');
                    const weeklyElement = document.getElementById('weeklyRevenue');
                    const dailyElement = document.getElementById('dailyRevenue');

                    const monthlyChangeElement = document.getElementById('monthlyChange');
                    const weeklyChangeElement = document.getElementById('weeklyChange');
                    const dailyChangeElement = document.getElementById('dailyChange');

                    if (filter === 'rooms') {
                        monthlyElement.textContent = '$' + roomMonthlyRevenue.toFixed(2);
                        weeklyElement.textContent = '$' + roomWeeklyRevenue.toFixed(2);
                        dailyElement.textContent = '$' + roomDailyAvg.toFixed(2);

                        monthlyChangeElement.textContent = (roomMonthlyChange >= 0 ? '↑' : '↓') + ' ' + Math.abs(roomMonthlyChange).toFixed(2) + '%';
                        weeklyChangeElement.textContent = (roomWeeklyChange >= 0 ? '↑' : '↓') + ' ' + Math.abs(roomWeeklyChange).toFixed(2) + '%';
                        dailyChangeElement.textContent = (roomDailyChange >= 0 ? '↑' : '↓') + ' ' + Math.abs(roomDailyChange).toFixed(2) + '%';

                        monthlyChangeElement.className = roomMonthlyChange >= 0 ? 'text-green-500 text-sm' : 'text-red-500 text-sm';
                        weeklyChangeElement.className = roomWeeklyChange >= 0 ? 'text-green-500 text-sm' : 'text-red-500 text-sm';
                        dailyChangeElement.className = roomDailyChange >= 0 ? 'text-green-500 text-sm' : 'text-red-500 text-sm';
                    } else {
                        monthlyElement.textContent = '$' + orderMonthlyRevenue.toFixed(2);
                        weeklyElement.textContent = '$' + orderWeeklyRevenue.toFixed(2);
                        dailyElement.textContent = '$' + orderDailyAvg.toFixed(2);

                        monthlyChangeElement.textContent = (orderMonthlyChange >= 0 ? '↑' : '↓') + ' ' + Math.abs(orderMonthlyChange).toFixed(2) + '%';
                        weeklyChangeElement.textContent = (orderWeeklyChange >= 0 ? '↑' : '↓') + ' ' + Math.abs(orderWeeklyChange).toFixed(2) + '%';
                        dailyChangeElement.textContent = (orderDailyChange >= 0 ? '↑' : '↓') + ' ' + Math.abs(orderDailyChange).toFixed(2) + '%';

                        monthlyChangeElement.className = orderMonthlyChange >= 0 ? 'text-green-500 text-sm' : 'text-red-500 text-sm';
                        weeklyChangeElement.className = orderWeeklyChange >= 0 ? 'text-green-500 text-sm' : 'text-red-500 text-sm';
                        dailyChangeElement.className = orderDailyChange >= 0 ? 'text-green-500 text-sm' : 'text-red-500 text-sm';
                    }
                }
            </script>

            <!-- Room Reservation Chart -->
            <div class="bg-white rounded-sm p-3 mt-3">
                <div>
                    <div class="flex justify-between items-center">
                        <h2 class="text-lg font-bold text-gray-700">Room Reservation Analytics</h2>
                        <button class="text-xs bg-gray-200 px-2 py-1 rounded text-gray-700">30 Days</button>
                    </div>
                    <p class="text-sm text-gray-500 mb-4">Trends in room type reservations over time.</p>
                </div>
                <?php
                $currentMonth = date('m');
                $currentYear = date('Y');

                // Query to get reserved room counts by type
                $query = "SELECT 
                        rt.RoomType,
                        COUNT(rd.ReservationID) AS reservation_count
                    FROM 
                        reservationdetailtb rd
                    JOIN 
                        roomtb r ON rd.RoomID = r.RoomID
                    JOIN 
                        roomtypetb rt ON r.RoomTypeID = rt.RoomTypeID
                    WHERE 
                        MONTH(rd.CheckInDate) = $currentMonth AND YEAR(rd.CheckInDate) = $currentYear
                    GROUP BY 
                        rt.RoomType
                    ORDER BY 
                        reservation_count DESC
                ";

                $stmt = $connect->prepare($query);
                $stmt->execute();
                $roomTypes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

                // Prepare data for the chart
                $labels = [];
                $data = [];
                $backgroundColors = [
                    'Single Bed' => 'bg-blue-300',
                    'Double Bed' => 'bg-green-300',
                    'Twin' => 'bg-yellow-300',
                    'Triple' => 'bg-orange-300',
                    'Family' => 'bg-red-300',
                    'Deluxe' => 'bg-purple-300',
                    'Single Deluxe' => 'bg-indigo-300',
                    'Double Deluxe' => 'bg-teal-300',
                    'Triple Deluxe' => 'bg-pink-300',
                    'Family Deluxe' => 'bg-gray-300'
                ];

                $totalReservations = 0;
                foreach ($roomTypes as $roomType) {
                    $labels[] = $roomType['RoomType'];
                    $data[] = $roomType['reservation_count'];
                    $totalReservations += $roomType['reservation_count'];
                }

                // Calculate percentages
                $percentages = [];
                foreach ($roomTypes as $roomType) {
                    $percentage = ($totalReservations > 0) ? round(($roomType['reservation_count'] / $totalReservations) * 100, 2) : 0;
                    $percentages[$roomType['RoomType']] = $percentage;
                }
                ?>

                <div class="flex justify-center max-w-[400px]">
                    <canvas id="roomBookingChart"></canvas>
                </div>
                <div class="flex flex-wrap gap-1 justify-around text-xs">
                    <?php foreach ($roomTypes as $roomType):
                        $roomTypeName = $roomType['RoomType'];
                        $count = $roomType['reservation_count'];
                        $percentage = $percentages[$roomTypeName] ?? 0;
                        $bgColor = $backgroundColors[$roomTypeName] ?? 'bg-gray-300';
                    ?>
                        <div class="flex items-center">
                            <div class="w-2.5 h-2.5 rounded-full <?= $bgColor ?> mr-0.5"></div>
                            <p><?= $roomTypeName ?>: <?= $count ?> (<?= $percentage ?>%)</p>
                        </div>
                    <?php endforeach; ?>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const ctx = document.getElementById('roomBookingChart').getContext('2d');

                        // Get the dynamic data from PHP
                        const roomTypes = <?= json_encode($roomTypes) ?>;
                        const backgroundColors = <?= json_encode(array_values($backgroundColors)) ?>;

                        // Create the chart
                        new Chart(ctx, {
                            type: 'doughnut',
                            data: {
                                labels: roomTypes.map(item => item.RoomType),
                                datasets: [{
                                    data: roomTypes.map(item => item.reservation_count),
                                    backgroundColor: ['#93C5FD', '#34D399', '#FDE047', '#FDBA74', '#F87171', '#D8B4FE', '#818CF8', '#2DD4BF', '#F472B6', '#D1D5DB'],
                                    hoverBackgroundColor: ['#2563EB', '#059669', '#EAB308', '#EA580C', '#DC2626', '#9333EA', '#4F46E5', '#0D9488', '#DB2777', '#6B7280'],
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                plugins: {
                                    legend: {
                                        position: 'right',
                                        labels: {
                                            usePointStyle: true,
                                            padding: 20,
                                        }
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                const label = context.label || '';
                                                const value = context.raw || 0;
                                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                                const percentage = Math.round((value / total) * 100);
                                                return `${label}: ${value} (${percentage}%)`;
                                            }
                                        }
                                    }
                                }
                            }
                        });

                        // Room Booking Chart
                        const roomBookingCtx = document.getElementById('roomBookingChart').getContext('2d');
                        new Chart(roomBookingCtx, {
                            type: 'doughnut',
                            data: {
                                labels: ['Single', 'Double', 'Twin', 'Triple', 'Family', 'Deluxe', 'Single Deluxe', 'Double Deluxe', 'Triple Deluxe', 'Family Deluxe'],
                                datasets: [{
                                    data: [1913, 859, 600, 400, 350, 482, 240, 180, 120, 90],
                                    backgroundColor: ['#93C5FD', '#34D399', '#FDE047', '#FDBA74', '#F87171', '#D8B4FE', '#818CF8', '#2DD4BF', '#F472B6', '#D1D5DB'],
                                    hoverBackgroundColor: ['#2563EB', '#059669', '#EAB308', '#EA580C', '#DC2626', '#9333EA', '#4F46E5', '#0D9488', '#DB2777', '#6B7280'],
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                plugins: {
                                    legend: {
                                        position: 'right',
                                        labels: {
                                            usePointStyle: true,
                                            padding: 20,
                                        }
                                    },
                                }
                            }
                        });
                    });
                </script>
            </div>
        </section>

        <section class="bg-white rounded-sm mt-3">
            <div class="container mx-auto p-3">
                <div>
                    <div class="mb-4">
                        <h2 class="text-lg font-bold text-gray-700">Income vs Expenses</h2>
                        <p class="text-sm text-gray-500">How was your income and expenses this month? <span class="text-xs italic">All amounts in USD</span></p>
                    </div>

                    <!-- Summary Section -->
                    <div class="flex justify-around sm:justify-start mb-6">
                        <?php
                        // Function to get monthly income from orders
                        function getMonthlyOrderIncome($year, $month)
                        {
                            global $connect;
                            $query = "SELECT SUM(TotalPrice) as total FROM ordertb 
              WHERE Status = 'Confirmed' 
              AND YEAR(OrderDate) = $year 
              AND MONTH(OrderDate) = $month";
                            $result = $connect->query($query);
                            $row = $result->fetch_assoc();
                            return $row['total'] ? floatval($row['total']) : 0;
                        }

                        // Function to get monthly income from reservations
                        function getMonthlyReservationIncome($year, $month)
                        {
                            global $connect;
                            $query = "SELECT SUM(TotalPrice) as total FROM reservationtb 
              WHERE Status = 'Confirmed' 
              AND YEAR(ReservationDate) = $year 
              AND MONTH(ReservationDate) = $month";
                            $result = $connect->query($query);
                            $row = $result->fetch_assoc();
                            return $row['total'] ? floatval($row['total']) : 0;
                        }

                        // Get current and previous month dates
                        $currentYear = date('Y');
                        $currentMonth = date('m');
                        $prevMonth = ($currentMonth == 1) ? 12 : $currentMonth - 1;
                        $prevYear = ($currentMonth == 1) ? $currentYear - 1 : $currentYear;

                        // Calculate totals for current month
                        $currentOrderIncome = getMonthlyOrderIncome($currentYear, $currentMonth);
                        $currentReservationIncome = getMonthlyReservationIncome($currentYear, $currentMonth);
                        $currentTotalIncome = $currentOrderIncome + $currentReservationIncome;

                        // Calculate totals for previous month
                        $prevOrderIncome = getMonthlyOrderIncome($prevYear, $prevMonth);
                        $prevReservationIncome = getMonthlyReservationIncome($prevYear, $prevMonth);
                        $prevTotalIncome = $prevOrderIncome + $prevReservationIncome;

                        // Calculate percentage change
                        $percentageChange = 0;
                        if ($prevTotalIncome > 0) {
                            $percentageChange = (($currentTotalIncome - $prevTotalIncome) / $prevTotalIncome) * 100;
                        }

                        // Format the numbers
                        $formattedIncome = number_format($currentTotalIncome, 2);
                        $shortIncome = ($currentTotalIncome >= 1000) ? number_format($currentTotalIncome / 1000, 2) . 'K' : $formattedIncome;
                        $percentageFormatted = number_format(abs($percentageChange), 2);
                        $percentageClass = ($percentageChange >= 0) ? 'text-green-500' : 'text-red-500';
                        $percentageSymbol = ($percentageChange >= 0) ? '↑' : '↓';
                        ?>

                        <!-- Display in your HTML -->
                        <div class="w-1/5">
                            <h3 class="text-2xl font-semibold text-blue-600">$<?php echo $shortIncome; ?></h3>
                            <p class="text-gray-500 text-xs">Income</p>
                            <p class="<?php echo $percentageClass; ?> text-sm">
                                <?php echo $percentageSymbol . ' ' . $percentageFormatted . '%'; ?>
                            </p>
                        </div>
                        <?php
                        // Fetch total expenses for the current month
                        $currentMonth = date('Y-m');
                        $expenseQuery = $connect->query("
    SELECT SUM(TotalAmount) AS totalExpenses 
    FROM purchasetb 
    WHERE DATE_FORMAT(PurchaseDate, '%Y-%m') = '$currentMonth'
");
                        $expenseData = $expenseQuery->fetch_assoc();
                        $totalExpenses = $expenseData['totalExpenses'] ?? 0;

                        // Fetch total expenses for the last month
                        $lastMonth = date('Y-m', strtotime('-1 month'));
                        $lastMonthQuery = $connect->query("
    SELECT SUM(TotalAmount) AS lastMonthExpenses
    FROM purchasetb 
    WHERE DATE_FORMAT(PurchaseDate, '%Y-%m') = '$lastMonth'
");
                        $lastMonthData = $lastMonthQuery->fetch_assoc();
                        $lastMonthExpenses = $lastMonthData['lastMonthExpenses'] ?? 0;

                        // Calculate percentage change
                        $percentageChange = 0;
                        if ($lastMonthExpenses > 0) {
                            $percentageChange = (($totalExpenses - $lastMonthExpenses) / $lastMonthExpenses) * 100;
                        }
                        $isIncrease = $percentageChange >= 0;
                        ?>

                        <!-- Dynamic Expense Display -->
                        <div>
                            <h3 class="text-2xl font-semibold text-red-600">
                                $<?= number_format($totalExpenses, 2) ?>
                            </h3>
                            <p class="text-gray-500 text-xs">Expenses</p>
                            <p class="<?= $isIncrease ? 'text-green-500' : 'text-red-500' ?> text-sm">
                                <?= $isIncrease ? '↑' : '↓' ?>
                                <?= number_format(abs($percentageChange), 2) ?>%
                            </p>
                        </div>
                    </div>

                    <!-- Chart Section -->
                    <div class="flex justify-center">
                        <div class="w-full h-[300px]">
                            <canvas id="incomeExpenseChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            // Get current month and year
            $currentMonth = date('m');
            $currentYear = date('Y');

            // Function to get daily income data from both ordertb and reservationtb
            function getDailyIncomeData($year, $month)
            {
                global $connect;

                // Query for order income
                $orderQuery = "SELECT 
                    DATE_FORMAT(OrderDate, '%d %b') AS day,
                    SUM(TotalPrice) AS amount
                  FROM ordertb
                  WHERE Status = 'Confirmed'
                  AND YEAR(OrderDate) = $year
                  AND MONTH(OrderDate) = $month
                  GROUP BY day";

                // Query for reservation income
                $reservationQuery = "SELECT 
                          DATE_FORMAT(ReservationDate, '%d %b') AS day,
                          SUM(TotalPrice) AS amount
                        FROM reservationtb
                        WHERE Status = 'Confirmed'
                        AND YEAR(ReservationDate) = $year
                        AND MONTH(ReservationDate) = $month
                        GROUP BY day";

                // Execute both queries
                $orderResult = $connect->query($orderQuery);
                $reservationResult = $connect->query($reservationQuery);

                // Combine results
                $combinedData = [];

                // Process order data
                while ($row = $orderResult->fetch_assoc()) {
                    $day = $row['day'];
                    $combinedData[$day] = ($combinedData[$day] ?? 0) + $row['amount'];
                }

                // Process reservation data
                while ($row = $reservationResult->fetch_assoc()) {
                    $day = $row['day'];
                    $combinedData[$day] = ($combinedData[$day] ?? 0) + $row['amount'];
                }

                return $combinedData;
            }

            // Function to get daily expense data from purchasetb
            function getDailyExpenseData($year, $month)
            {
                global $connect;
                $query = "SELECT 
                DATE_FORMAT(PurchaseDate, '%d %b') AS day,
                SUM(TotalAmount) AS amount
              FROM purchasetb
              WHERE YEAR(PurchaseDate) = $year
              AND MONTH(PurchaseDate) = $month
              GROUP BY day";
                $result = $connect->query($query);

                $data = [];
                while ($row = $result->fetch_assoc()) {
                    $data[$row['day']] = $row['amount'];
                }
                return $data;
            }

            // Get data for current month
            $incomeData = getDailyIncomeData($currentYear, $currentMonth);
            $expenseData = getDailyExpenseData($currentYear, $currentMonth);

            // Get all days in month with data
            $allDaysWithData = array_unique(array_merge(array_keys($incomeData), array_keys($expenseData)));

            // Create array of selected days (1, 5, 10, 15, 20, 25, 30) plus any days with data
            $selectedDays = [1, 5, 10, 15, 20, 25, 30];
            $allDates = [];

            // Convert all days with data to day numbers
            $daysWithDataNumbers = [];
            foreach ($allDaysWithData as $dateStr) {
                $dayNum = (int)substr($dateStr, 0, 2);
                $daysWithDataNumbers[] = $dayNum;
            }

            // Combine selected days and days with data
            $allDaysToShow = array_unique(array_merge($selectedDays, $daysWithDataNumbers));
            sort($allDaysToShow);

            // Create date labels
            foreach ($allDaysToShow as $day) {
                $date = DateTime::createFromFormat('j', $day);
                $allDates[] = $date->format('d M');
            }

            // Prepare datasets with all data points
            $incomeDataset = [];
            $expenseDataset = [];
            foreach ($allDates as $date) {
                $incomeDataset[] = $incomeData[$date] ?? 0;
                $expenseDataset[] = $expenseData[$date] ?? 0;
            }
            ?>

            <script>
                const ctx = document.getElementById('incomeExpenseChart').getContext('2d');
                const incomeExpenseChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: <?php echo json_encode($allDates); ?>,
                        datasets: [{
                                label: 'Income',
                                data: <?php echo json_encode($incomeDataset); ?>,
                                borderColor: '#3B82F6',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                fill: true,
                                tension: 0.4,
                            },
                            {
                                label: 'Expenses',
                                data: <?php echo json_encode($expenseDataset); ?>,
                                borderColor: '#EF4444',
                                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                                fill: true,
                                tension: 0.4,
                                borderDash: [5, 5],
                            }
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': $' + context.raw.toLocaleString();
                                    },
                                },
                            },
                        },
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'Date',
                                    font: {
                                        weight: 'bold',
                                    },
                                },
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: 'Amount ($)',
                                    font: {
                                        weight: 'bold',
                                    },
                                },
                                beginAtZero: true,
                            },
                        },
                    },
                });
            </script>
        </section>

        <section class="flex flex-col sm:flex-row gap-3 rounded-sm mt-3">
            <div class="flex-1 divide-y-2 divide-slate-100 bg-white p-3">
                <div class="flex items-center justify-between">
                    <h1 class="text-lg font-bold text-gray-700 mb-2">New Users</h1>
                    <a href="UserDetails.php" class="text-sm font-semibold text-blue-600 hover:text-blue-900 transition-colors duration-200 select-none">View all</a>
                </div>
                <?php foreach ($allUsers as $user):
                    // Extract initials from the UserName
                    $nameParts = explode(' ', trim($user['UserName'])); // Split the name by spaces
                    $initials = substr($nameParts[0], 0, 1); // First letter of the first name
                    if (count($nameParts) > 1) {
                        $initials .= substr(end($nameParts), 0, 1); // First letter of the last name
                    }

                    $bgColor = $user['ProfileBgColor'];
                ?>
                    <div class="p-2">
                        <div class="flex items-center gap-2">
                            <p
                                class="w-10 h-10 rounded-full bg-[<?= $bgColor ?>] text-white uppercase font-semibold flex items-center justify-center select-none">
                                <?= $initials ?>
                            </p>
                            <div class="text-gray-600 text-sm">
                                <h1 class="font-bold"><?= htmlspecialchars($user['UserName']) ?></h1>
                                <p><?= htmlspecialchars($user['UserEmail']) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php
            $reservation = "SELECT rt.ReservationID, rt.UserID, rt.ReservationDate, rt.Status, rt.TotalPrice, u.ProfileBgColor, u.UserName FROM reservationtb rt
                JOIN usertb u ON rt.UserID = u.UserID 
                ORDER BY rt.ReservationDate DESC LIMIT 4";
            $stmt = $connect->prepare($reservation);
            $stmt->execute();
            $resResult = $stmt->get_result();
            $count = $resResult->num_rows;
            ?>

            <div class="flex-1 divide-y-2 divide-slate-100 bg-white p-3">
                <h1 class="text-lg font-bold text-gray-700 mb-2">Recent Reservations</h1>
                <?php
                if ($count > 0) {
                    while ($row = $resResult->fetch_assoc()):
                        $bgColor = $row['ProfileBgColor'];
                        $status = $row['Status'];

                        // Set status color based on reservation status
                        $statusColor = match ($status) {
                            'Confirmed' => 'bg-green-100 text-green-800',
                            'Pending' => 'bg-yellow-100 text-yellow-800',
                            'Cancelled' => 'bg-red-100 text-red-800',
                            default => 'bg-gray-100 text-gray-800'
                        };

                        $noti = match ($status) {
                            'Confirmed' => 'has confirmed a reservation',
                            'Pending' => 'has a pending reservation',
                            'Cancelled' => 'cancelled a reservation',
                            default => 'bg-gray-100 text-gray-800'
                        };

                        // Extract initials from the UserName
                        $nameParts = explode(' ', trim($row['UserName']));
                        $initials = strtoupper(substr($nameParts[0], 0, 1));
                        if (count($nameParts) > 1) {
                            $initials .= strtoupper(substr(end($nameParts), 0, 1));
                        }

                        // Calculate time difference
                        $reservationDate = new DateTime($row['ReservationDate']);
                        $currentDate = new DateTime();
                        $interval = $currentDate->diff($reservationDate);

                        if ($interval->y > 0) {
                            $timeAgo = $interval->y . ' year' . ($interval->y > 1 ? 's' : '') . ' ago';
                        } elseif ($interval->m > 0) {
                            $timeAgo = $interval->m . ' month' . ($interval->m > 1 ? 's' : '') . ' ago';
                        } elseif ($interval->d >= 7) {
                            $weeks = floor($interval->d / 7);
                            $timeAgo = $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ago';
                        } elseif ($interval->d > 0) {
                            $timeAgo = $interval->d . ' day' . ($interval->d > 1 ? 's' : '') . ' ago';
                        } elseif ($interval->h > 0) {
                            $timeAgo = $interval->h . ' hour' . ($interval->h > 1 ? 's' : '') . ' ago';
                        } elseif ($interval->i > 0) {
                            $timeAgo = $interval->i . ' minute' . ($interval->i > 1 ? 's' : '') . ' ago';
                        } else {
                            $timeAgo = 'Just now';
                        }
                ?>
                        <div class="p-2 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-[<?= htmlspecialchars($bgColor) ?>] text-white font-semibold flex items-center justify-center select-none shrink-0">
                                    <?= htmlspecialchars($initials) ?>
                                </div>
                                <div class="text-gray-600 text-sm flex-1">
                                    <div class="flex justify-between items-start">
                                        <h1 class="font-semibold"><?= htmlspecialchars($row['FirstName'] ?? $nameParts[0]) ?> <?= $noti ?></h1>
                                        <span class="text-xs px-2 py-1 rounded-full select-none <?= $statusColor ?>">
                                            <?= htmlspecialchars($status) ?>
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center mt-1">
                                        <p class="text-xs text-gray-500"><?= $timeAgo ?></p>
                                        <p class="text-xs font-medium">$<?= number_format($row['TotalPrice'], 2) ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                <?php
                    endwhile;
                } else {
                    echo '<p class="text-gray-500 text-center text-sm py-24">No reservations found.</p>';
                }
                ?>
            </div>
        </section>
    </div>

    <!-- Loader -->
    <?php
    include('../includes/loader.php');
    ?>

    <script src="//unpkg.com/alpinejs" defer></script>
    <script type="module" src="../JS/admin.js"></script>
</body>

</html>