<?php
session_start();
include('../config/dbConnection.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

if (!isset($_SESSION["AdminEmail"])) {
    echo "<script>window.alert('Login first! You cannot direct access the admin info.')</script>";
    echo "<script>window.location = 'AdminSignIn.php'</script>";
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
    include('../includes/AdminNavbar.php');
    ?>

    <div class="p-3 ml-0 md:ml-[250px] min-w-[350px]">
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
                <h3 class="text-lg font-bold text-gray-800 mb-4">Confirm Action</h3>
                <p class="text-sm text-gray-600 mb-6">Are you sure you want to change the website status?</p>
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

        <section class="container mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 mt-3">
            <!-- Total Booking -->
            <div class="bg-white rounded-sm p-3">
                <h2 class="text-lg font-bold text-gray-700">Total Booking</h2>
                <p class="text-sm text-gray-500 mb-4">Total number of bookings recorded.</p>
                <div class="grid grid-cols-3 gap-4 mb-4">
                    <div class="text-center">
                        <h3 class="text-xl font-semibold text-blue-600">1,200</h3>
                        <p class="text-gray-500 text-xs">Monthly</p>
                        <p class="text-green-500 text-sm">↑ 5.12%</p>
                    </div>
                    <div class="text-center">
                        <h3 class="text-xl font-semibold text-red-600">300</h3>
                        <p class="text-gray-500 text-xs">Weekly</p>
                        <p class="text-red-500 text-sm">↓ 2.45%</p>
                    </div>
                    <div class="text-center">
                        <h3 class="text-xl font-semibold text-green-600">45</h3>
                        <p class="text-gray-500 text-xs">Daily (Avg)</p>
                        <p class="text-green-500 text-sm">↑ 3.18%</p>
                    </div>
                </div>
            </div>

            <!-- Room Available -->
            <div class="bg-white rounded-sm p-3">
                <h2 class="text-lg font-bold text-gray-700">Room Available</h2>
                <p class="text-sm text-gray-500 mb-4">Current availability of rooms across different time periods.</p>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="text-center">
                        <h3 class="text-xl font-semibold text-red-600">45</h3>
                        <p class="text-gray-500 text-xs">Booked</p>
                        <p class="text-red-500 text-sm">↓ 2.45%</p>
                    </div>
                    <div class="text-center">
                        <h3 class="text-xl font-semibold text-green-600">15</h3>
                        <p class="text-gray-500 text-xs">Available</p>
                        <p class="text-green-500 text-sm">↑ 3.18%</p>
                    </div>
                </div>
            </div>

            <!-- Stock Available -->
            <div class="bg-white rounded-sm p-3">
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

        <section class="container mx-auto grid grid-cols-1 md:grid-cols-2 gap-3">
            <!-- Sales Revenue -->
            <div class="bg-white rounded-sm p-3 mt-3">
                <h2 class="text-lg font-bold text-gray-700">Sales Revenue</h2>
                <p class="text-sm text-gray-500 mb-4">In last 30 days revenue from rent.</p>
                <div class="grid grid-cols-3 gap-4 mb-4">
                    <div class="text-center">
                        <h3 class="text-xl font-semibold text-blue-600">9.28K</h3>
                        <p class="text-gray-500 text-xs">Monthly</p>
                        <p class="text-green-500 text-sm">↑ 4.63%</p>
                    </div>
                    <div class="text-center">
                        <h3 class="text-xl font-semibold text-red-600">2.69K</h3>
                        <p class="text-gray-500 text-xs">Weekly</p>
                        <p class="text-red-500 text-sm">↓ 1.92%</p>
                    </div>
                    <div class="text-center">
                        <h3 class="text-xl font-semibold text-green-600">0.94K</h3>
                        <p class="text-gray-500 text-xs">Daily (Avg)</p>
                        <p class="text-green-500 text-sm">↑ 3.45%</p>
                    </div>
                </div>
                <canvas id="salesRevenueChart" class="h-[150px]"></canvas>
            </div>

            <!-- Room Booking Chart -->
            <div class="bg-white rounded-sm p-3 mt-2">
                <div class="flex justify-between items-center">
                    <h2 class="text-lg font-bold text-gray-700">Room Booking Chart</h2>
                    <button class="text-xs bg-gray-200 px-2 py-1 rounded text-gray-700">30 Days</button>
                </div>
                <div class="flex justify-center max-w-[400px]">
                    <canvas id="roomBookingChart"></canvas>
                </div>
                <div class="flex flex-wrap gap-1 justify-around text-xs">
                    <div class="flex items-center">
                        <div class="w-2.5 h-2.5 rounded-full bg-blue-300 mr-0.5"></div>
                        <p>Single: 1913 (58.63%)</p>
                    </div>
                    <div class="flex items-center">
                        <div class="w-2.5 h-2.5 rounded-full bg-green-300 mr-0.5"></div>
                        <p>Double: 859 (23.94%)</p>
                    </div>
                    <div class="flex items-center">
                        <div class="w-2.5 h-2.5 rounded-full bg-yellow-300 mr-0.5"></div>
                        <p>Twin: 600 (18.34%)</p>
                    </div>
                    <div class="flex items-center">
                        <div class="w-2.5 h-2.5 rounded-full bg-orange-300 mr-0.5"></div>
                        <p>Triple: 400 (12.21%)</p>
                    </div>
                    <div class="flex items-center">
                        <div class="w-2.5 h-2.5 rounded-full bg-red-300 mr-0.5"></div>
                        <p>Family: 350 (10.69%)</p>
                    </div>
                    <div class="flex items-center">
                        <div class="w-2.5 h-2.5 rounded-full bg-purple-300 mr-0.5"></div>
                        <p>Deluxe: 482 (12.94%)</p>
                    </div>
                    <div class="flex items-center">
                        <div class="w-2.5 h-2.5 rounded-full bg-indigo-300 mr-0.5"></div>
                        <p>Single Deluxe: 240 (8.76%)</p>
                    </div>
                    <div class="flex items-center">
                        <div class="w-2.5 h-2.5 rounded-full bg-teal-300 mr-0.5"></div>
                        <p>Double Deluxe: 180 (6.59%)</p>
                    </div>
                    <div class="flex items-center">
                        <div class="w-2.5 h-2.5 rounded-full bg-pink-300 mr-0.5"></div>
                        <p>Triple Deluxe: 120 (4.39%)</p>
                    </div>
                    <div class="flex items-center">
                        <div class="w-2.5 h-2.5 rounded-full bg-gray-300 mr-0.5"></div>
                        <p>Family Deluxe: 90 (3.45%)</p>
                    </div>
                </div>
            </div>
        </section>

        <script>
            // Sales Revenue Chart
            const salesRevenueCtx = document.getElementById('salesRevenueChart').getContext('2d');
            new Chart(salesRevenueCtx, {
                type: 'bar',
                data: {
                    labels: ['01 Jan', '05 Jan', '10 Jan', '15 Jan', '20 Jan', '25 Jan', '30 Jan'],
                    datasets: [{
                        label: 'Revenue ($)',
                        data: [800, 900, 850, 950, 1000, 870, 920],
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
        </script>

        <section class="bg-white rounded-sm mt-3">
            <div class="container mx-auto p-3">
                <div>
                    <div class="mb-4">
                        <h2 class="text-lg font-bold text-gray-700">Income vs Expenses</h2>
                        <p class="text-sm text-gray-500">How was your income and expenses this month?</p>
                    </div>

                    <!-- Summary Section -->
                    <div class="flex justify-around sm:justify-start mb-6">
                        <div class="w-1/5">
                            <h3 class="text-2xl font-semibold text-blue-600">2.57K</h3>
                            <p class="text-gray-500 text-xs">Income</p>
                            <p class="text-red-500 text-sm">↓ 12.37%</p>
                        </div>
                        <div>
                            <h3 class="text-2xl font-semibold text-red-600">3.5K</h3>
                            <p class="text-gray-500 text-xs">Expenses</p>
                            <p class="text-green-500 text-sm">↑ 8.37%</p>
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

            <script>
                const ctx = document.getElementById('incomeExpenseChart').getContext('2d');
                const incomeExpenseChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: ['01 Jan', '05 Jan', '10 Jan', '15 Jan', '20 Jan', '25 Jan', '30 Jan'], // X-axis labels
                        datasets: [{
                                label: 'Income',
                                data: [2500, 2700, 2900, 3100, 2800, 3000, 2570], // Income data points
                                borderColor: '#3B82F6', // Tailwind blue
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                fill: true,
                                tension: 0.4,
                            },
                            {
                                label: 'Expenses',
                                data: [2200, 2600, 3100, 3500, 3200, 3400, 3500], // Expense data points
                                borderColor: '#EF4444', // Tailwind red
                                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                                fill: true,
                                tension: 0.4,
                                borderDash: [5, 5],
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false, // Ensures the chart respects the fixed dimensions
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
                ?>
                    <div class="p-2">
                        <div class="flex items-center gap-2">
                            <p
                                class="w-10 h-10 rounded-full bg-blue-100 text-blue-500 uppercase font-semibold flex items-center justify-center select-none">
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

            <div class="flex-1 divide-y-2 divide-slate-100 bg-white p-3">
                <h1 class="text-lg font-bold text-gray-700 mb-2">Recent Activities</h1>
                <div class="p-2">
                    <div class="flex items-center gap-2">
                        <p class="w-10 h-10 rounded-full bg-blue-100 text-blue-500 font-semibold flex items-center justify-center select-none">B</p>
                        <div class="text-gray-600 text-sm">
                            <h1 class="font-semibold">Brandon requested for room.</h1>
                            <p>2 hours ago</p>
                        </div>
                    </div>
                </div>
                <div class="p-2">
                    <div class="flex items-center gap-2">
                        <p class="w-10 h-10 rounded-full bg-blue-100 text-blue-500 font-semibold flex items-center justify-center select-none">F</p>
                        <div class="text-gray-600 text-sm">
                            <h1 class="font-semibold">Franco cancelled booking.</h1>
                            <p>1 hour ago</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Loader -->
    <?php
    include('../includes/Loader.php');
    ?>

    <script src="//unpkg.com/alpinejs" defer></script>
    <script type="module" src="../JS/admin.js"></script>
</body>

</html>