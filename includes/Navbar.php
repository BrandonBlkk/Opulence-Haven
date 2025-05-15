<?php
// Get the current file name
$current_page = basename($_SERVER['PHP_SELF']);
?>

<section id="sale-section" class="SVG2 p-2 text-center bg-blue-950 text-white">
    <?php if ($current_page === 'Dining.php'): ?>
        <p>Reserve your table for an exquisite dining experience!</p>
    <?php else: ?>
        <p>Reserve your perfect room today!</p>
    <?php endif; ?>
</section>

<div class="sticky top-0 w-full bg-white border-b z-30">
    <?php
    include('../includes/MoveRightLoader.php');
    ?>
    <nav class="flex items-center justify-between max-w-[1050px] mx-auto p-3">
        <a href="../User/HomePage.php">
            <img src="../UserImages/Screenshot_2024-11-29_201534-removebg-preview.png" class="w-28 select-none" alt="Logo">
        </a>

        <div class="flex items-center gap-5 select-none">
            <?php if ($current_page === 'Dining.php'): ?>
                <div id="diningBtn" class="flex items-center gap-1 hover:bg-gray-100 p-2 rounded-sm transition-colors duration-200">
                    <i class="ri-service-bell-line text-2xl cursor-pointer"></i>
                    <p class="font-semibold">Dine With Us</p>
                </div>
            <?php else: ?>
                <a href="../User/Favorite.php" class="flex items-center gap-1 hover:bg-gray-100 p-2 rounded-sm transition-colors duration-200">
                    <i class="ri-heart-line text-2xl cursor-pointer"></i>
                    <p class="font-semibold">Favorite</p>
                </a>
            <?php endif; ?>
            <i id="menubar" class="ri-menu-4-line text-3xl cursor-pointer transition-transform duration-300"></i>
        </div>
        <?php
        include('Sidebar.php');
        include('MaintenanceAlert.php');
        ?>
    </nav>
</div>

<!-- Dining Reservation Sidebar -->
<aside id="diningAside" class="fixed top-0 -right-full flex flex-col bg-white w-full md:w-[435px] h-full p-4 z-50 transition-all duration-500 ease-in-out">
    <div class="flex justify-between pb-3">
        <div class="max-w-[300px]">
            <span class="text-2xl font-semibold">Dining Request</span>
        </div>
        <i id="diningCloseBtn" class="ri-close-line text-2xl cursor-pointer rounded transition-colors duration-300"></i>
    </div>
    <div class="flex flex-col justify-between gap-3 h-full">
        <div>
            <form action="../User/Dining.php" method="post" id="diningForm" class="flex flex-col gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reservation Details</label>
                    <div class="flex flex-col sm:flex-row sm:gap-2">
                        <!-- Date -->
                        <div class="flex flex-1 flex-col gap-2">
                            <label for="date" class="font-semibold">Date</label>
                            <input type="date" id="date" name="date" min="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d', strtotime('+30 days')); ?>" class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out" required>
                        </div>
                        <!-- Set the minimum date to today -->
                        <script>
                            document.addEventListener('DOMContentLoaded', () => {
                                // Get the current date in YYYY-MM-DD format
                                const today = new Date().toISOString().split('T')[0];

                                // Set the min attribute of the date input to today
                                const dateInput = document.getElementById('date');
                                dateInput.setAttribute('min', today);
                            });
                        </script>
                        <!-- Time -->
                        <div class="flex flex-1 flex-col gap-2">
                            <label for="time" class="font-semibold">Time</label>
                            <input type="time" id="time" name="time" class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out" required>
                        </div>
                    </div>
                    <!-- Number of Guests -->
                    <div class="flex flex-col gap-2">
                        <label for="guests" class="font-semibold">Number of Guests</label>
                        <?php
                        $guests = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10);
                        ?>
                        <select name="guests" class="p-2 w-full border rounded" required>
                            <?php foreach ($guests as $guest): ?>
                                <option value="<?php echo $guest; ?>"><?php echo $guest; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- Special Requests -->
                    <div class="flex flex-col gap-2">
                        <label for="specialrequests" class="font-semibold">Special Requests (Optional)</label>
                        <textarea id="specialRequests" name="specialrequests" class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out" placeholder="Enter any special requests" rows="3"></textarea>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contact Details</label>
                    <!-- Name -->
                    <div class="relative flex flex-col gap-2">
                        <label for="name" class="font-semibold">Name</label>
                        <input type="text" id="diningNameInput" name="name" placeholder="Enter your name" class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out">
                        <small id="diningNameError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>
                    <!-- Email -->
                    <div class="relative flex flex-col gap-2">
                        <label for="email" class="font-semibold">Email</label>
                        <input type="email" id="diningEmailInput" name="email" value="<?php echo $session_userID ? htmlspecialchars($email) : ''; ?>" placeholder="Enter your email" class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out">
                        <small id="diningEmailError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>

                    </div>
                    <!-- Phone -->
                    <div class="relative flex flex-col gap-2">
                        <label for="phone" class="font-semibold">Phone Number</label>
                        <input type="tel" id="diningPhoneInput" name="phone" value="<?php echo $session_userID ? htmlspecialchars($phone) : ''; ?>" placeholder="Enter your phone" class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out">
                        <small id="diningPhoneError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>
                </div>
                <button type="submit" name="reserve" class="bg-amber-500 hover:bg-amber-600 text-white font-semibold text-center p-2 select-none transition-colors duration-300">Reserve Table</button>
            </form>
        </div>
        <div class="space-y-4">
            <p class="text-xs text-slate-600 pb-4">
                Contact our 24/7 support for any assistance with your bookings or inquiries.
                <a href="mailto:support@opulenceHaven.com" class="text-amber-600 underline hover:underline-offset-2">support@opulenceHaven.com</a>
            </p>
        </div>
    </div>
</aside>

<!-- Loader -->
<?php
include('Loader.php');
?>

<!-- Overlay -->
<div id="darkOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden"></div>
<div id="darkOverlay2" class="fixed inset-0 bg-black bg-opacity-50 opacity-0 invisible z-40 transition-opacity duration-300"></div>