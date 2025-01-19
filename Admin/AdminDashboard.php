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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opulence Haven|Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/3.5.0/remixicon.css" integrity="sha512-HXXR0l2yMwHDrDyxJbrMD9eLvPe3z3qL3PPeozNTsiHJEENxx8DH2CxmV05iwG0dwoz5n4gQZQyYLUNt1Wdgfg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../CSS/output.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../CSS/input.css?v=<?php echo time(); ?>">
</head>

<body>
    <?php
    include('../includes/AdminNavbar.php');
    ?>

    <div class="flex flex-col md:flex-row md:space-x-3 p-3 ml-0 md:ml-[250px] min-w-[350px]">
        <!-- Left Side Content -->
        <div class="w-full bg-white p-2">
            <div class="bg-whiteshadow rounded-lg">
                <h2 class="text-lg font-bold text-gray-700 mb-4">Website Maintenance Mode</h2>
                <!-- Status Message -->
                <div id="statusMessage" class="flex items-center gap-2 mb-4">
                    <span id="statusIcon" class="h-3 w-3 rounded-full"></span>
                    <p id="statusText" class="text-sm text-gray-600"></p>
                </div>
                <!-- Toggle Button -->
                <div class="flex items-center justify-between">
                    <label class="font-medium text-gray-600">Enable Maintenance Mode</label>
                    <div class="relative inline-block w-12 align-middle select-none transition duration-200">
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
                    statusIcon.className = 'h-3 w-3 rounded-full bg-red-500';
                    statusText.innerHTML = 'The website is currently <span class="font-semibold text-red-600">in maintenance mode</span>.';
                } else {
                    // Active Mode
                    statusIcon.className = 'h-3 w-3 rounded-full bg-green-500';
                    statusText.innerHTML = 'The website is currently <span class="font-semibold text-green-600">active</span>.';
                }
            }
        </script>
    </div>

    <!-- Loader -->
    <?php
    include('../includes/Loader.php');
    ?>

    <script src="//unpkg.com/alpinejs" defer></script>
    <script type="module" src="../JS/admin.js"></script>
</body>

</html>