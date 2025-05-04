<?php
session_start();
include('../config/dbConnection.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

$userID = $_SESSION['UserID'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
    <title>Opulence Haven</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../CSS/output.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../CSS/input.css?v=<?php echo time(); ?>">

</head>

<body class="relative">
    <?php
    include('../includes/Navbar.php');
    include('../includes/Cookies.php');
    ?>

    <main class="pb-4">
        <div class="relative swiper-container flex justify-center">
            <!-- Search Form at Bottom Center -->
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" class="w-full sm:max-w-[1030px] z-10 p-4 bg-white rounded-sm shadow-lg flex justify-between items-center space-x-4">
                <div class="flex items-center space-x-4">
                    <div class="flex gap-3">
                        <!-- Check-in Date -->
                        <div>
                            <label class="font-semibold text-blue-900">Check-In Date</label>
                            <input type="date" id="checkin-date" name="checkin_date" class="p-3 border border-gray-300 rounded-sm" placeholder="Check-in Date">
                        </div>
                        <!-- Check-out Date -->
                        <div>
                            <label class="font-semibold text-blue-900">Check-Out Date</label>
                            <input type="date" id="checkout-date" name="checkout_date" class="p-3 border border-gray-300 rounded-sm" placeholder="Check-out Date">
                        </div>

                        <script>
                            document.addEventListener('DOMContentLoaded', () => {
                                // Get the current date in YYYY-MM-DD format
                                const today = new Date().toISOString().split('T')[0];

                                // Set the min attribute of the date input to today
                                const checkInDateInput = document.getElementById('checkin-date');
                                const checkOutDateInput = document.getElementById('checkout-date');

                                checkInDateInput.setAttribute('min', today);
                                checkOutDateInput.setAttribute('min', today);
                            });
                        </script>
                    </div>
                    <div class="flex">
                        <!-- Adults -->
                        <select id="adults" name="adults" class="p-3 border border-gray-300 rounded-sm">
                            <option value="1">1 Adult</option>
                            <option value="2">2 Adults</option>
                            <option value="3">3 Adults</option>
                            <option value="4">4 Adults</option>
                            <option value="5">5 Adults</option>
                            <option value="6">6 Adults</option>
                        </select>
                        <!-- Children -->
                        <select id="children" name="children" class="p-3 border border-gray-300 rounded-sm">
                            <option value="0">0 Children</option>
                            <option value="1">1 Child</option>
                            <option value="2">2 Children</option>
                            <option value="3">3 Children</option>
                            <option value="4">4 Children</option>
                            <option value="5">5 Children</option>
                        </select>
                    </div>
                </div>

                <!-- Search Button -->
                <div class="flex items-center space-x-2 select-none">
                    <button type="submit" name="check_availability" class="p-3 bg-blue-900 text-white rounded-sm hover:bg-blue-950 uppercase font-semibold transition-colors duration-300">Check Availability</button>
                </div>
            </form>
        </div>

        <section class="max-w-[1200px] mx-auto px-4 py-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Yangon: 62 properties found</h1>
                <div class="text-sm">
                    <span class="text-gray-600">Sort by:</span>
                    <select class="ml-2 border-none font-medium focus:ring-0 outline-none">
                        <option>Our top picks</option>
                        <option>Price (low to high)</option>
                        <option>Price (high to low)</option>
                        <option>Star rating</option>
                    </select>
                </div>
            </div>

            <div class="mb-4 text-sm text-gray-600 border-b pb-4">
                Please review any travel advisories provided by your government to make an informed decision about your stay in this area, which may be considered conflict-affected.
            </div>

            <div class="flex flex-col md:flex-row gap-6">
                <!-- Left: Filter Panel -->
                <aside class="w-full md:w-1/4">
                    <div class="bg-white p-4 rounded-md shadow-sm border sticky top-4">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">Filter by:</h3>
                        <h4 class="font-medium text-gray-800 mb-2">Popular filters</h4>
                        <div class="space-y-3">
                            <label class="flex items-center">
                                <input type="checkbox" class="mr-2 rounded text-orange-500">
                                <span>Hotels</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" class="mr-2 rounded text-orange-500">
                                <span>Free WiFi</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" class="mr-2 rounded text-orange-500">
                                <span>5 stars</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" class="mr-2 rounded text-orange-500">
                                <span>Sea view</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" class="mr-2 rounded text-orange-500">
                                <span>Restaurant</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" class="mr-2 rounded text-orange-500">
                                <span>Air conditioning</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" class="mr-2 rounded text-orange-500">
                                <span>Swimming Pool</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" class="mr-2 rounded text-orange-500">
                                <span>Apartments</span>
                            </label>
                        </div>
                    </div>
                </aside>

                <!-- Right: Hotel Listings -->
                <div class="w-full space-y-6">
                    <!-- Entire Homes CTA -->
                    <div class="bg-white p-4 rounded-md shadow-sm border">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="font-medium text-gray-800">Looking for a space of your own?</h3>
                                <p class="text-sm text-gray-600">Find privacy and peace of mind with an entire home or apartment to yourself</p>
                            </div>
                            <button class="text-orange-500 font-medium hover:text-orange-600">
                                Show entire homes & apartments →
                            </button>
                        </div>
                    </div>

                    <!-- Hotel Listing -->
                    <div class="bg-white overflow-hidde space-y-2">
                        <a href="../User/RoomDetails.php" class="flex flex-col md:flex-row items-center rounded-md shadow-sm border">
                            <div class="md:w-1/3 h-64 overflow-hidden select-none">
                                <img src="../UserImages/beautiful-hotel-insights-details.jpg" alt="85 SOHO Premium Residences" class="w-full h-full object-cover">
                            </div>
                            <div class="md:w-2/3 p-4">
                                <div class="flex justify-between">
                                    <h2 class="text-xl font-bold text-gray-800">85 SOHO Premium Residences <span class="text-yellow-400">★★★★</span></h2>
                                    <div class="text-right">
                                        <div class="text-sm text-gray-500">Price starts from</div>
                                        <div class="text-lg font-bold text-orange-500">$120</div>
                                    </div>
                                </div>
                                <div class="text-sm text-gray-600 mt-1">Kandawayi Lake Area, Yangon <span class="text-gray-400">•</span> Show on map <span class="text-gray-400">•</span> 1.9 km from centre</div>
                                <p class="text-gray-700 mt-3">Set within the Kandawayi Lake Area district in Yangon, 85 SOHO Premium Residences has air conditioning, a balcony, and city views. This 4-star apartment offers a 24-hour front desk and a lift.</p>
                                <div class="flex flex-wrap gap-2 mt-4 select-none">
                                    <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">Air conditioning</span>
                                    <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">Balcony</span>
                                    <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">City view</span>
                                    <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">24-hour front desk</span>
                                </div>
                            </div>
                        </a>

                        <!-- Skeleton -->
                        <a href="../User/RoomDetails.php" class="flex flex-col md:flex-row items-center w-full bg-white rounded-md overflow-hidden shadow-sm border animate-pulse">
                            <!-- Image Skeleton -->
                            <div class="md:w-1/3 h-64 bg-slate-200"></div>

                            <!-- Content Skeleton -->
                            <div class="md:w-2/3 p-4 w-full space-y-3">
                                <!-- Title and Price Row -->
                                <div class="flex justify-between items-start space-x-4">
                                    <div class="w-2/3 h-5 bg-slate-200 rounded-md"></div>
                                    <div class="space-y-2 w-1/3">
                                        <div class="h-4 bg-slate-200 rounded-md"></div>
                                        <div class="h-4 bg-slate-200 rounded-md"></div>
                                    </div>
                                </div>

                                <!-- Location Info -->
                                <div class="w-3/4 h-4 bg-slate-200 rounded-md"></div>

                                <!-- Description -->
                                <div class="space-y-2">
                                    <div class="h-3 bg-slate-200 rounded-md w-full"></div>
                                    <div class="h-3 bg-slate-200 rounded-md w-5/6"></div>
                                    <div class="h-3 bg-slate-200 rounded-md w-3/4"></div>
                                </div>

                                <!-- Feature Tags -->
                                <div class="flex flex-wrap gap-2 mt-2">
                                    <div class="h-6 w-24 bg-slate-200 rounded-md"></div>
                                    <div class="h-6 w-20 bg-slate-200 rounded-md"></div>
                                    <div class="h-6 w-28 bg-slate-200 rounded-md"></div>
                                    <div class="h-6 w-32 bg-slate-200 rounded-md"></div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Additional hotel listings can be added here following the same pattern -->
                </div>
            </div>
        </section>
    </main>

    <!-- MoveUp Btn -->
    <?php
    include('../includes/MoveUpBtn.php');
    include('../includes/Footer.php');
    ?>

    <!-- AOS JS -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 600,
            once: false,
        });
    </script>
    <script src="//unpkg.com/alpinejs" defer></script>
    <script type="module" src="../JS/index.js"></script>
</body>

</html>