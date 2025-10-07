<?php
session_start();
require_once('../config/db_connection.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

$userID = (!empty($_SESSION["UserID"]) ? $_SESSION["UserID"] : null);
$alertMessage = '';
$showLoginModal = false;

// Get search parameters from URL
$reservation_id = isset($_GET['reservation_id']) ? $_GET['reservation_id'] : '';
$room_id = isset($_GET['room_id']) ? $_GET['room_id'] : '';
$checkin_date = isset($_GET['checkin_date']) ? $_GET['checkin_date'] : '';
$checkout_date = isset($_GET['checkout_date']) ? $_GET['checkout_date'] : '';
$adults = isset($_GET['adults']) ? intval($_GET['adults']) : 1;
$children = isset($_GET['children']) ? intval($_GET['children']) : 0;
$edit = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
$totelGuest = $adults + $children;

if ($checkin_date == '' || $checkout_date == '') {
    $_SESSION['alert'] = "Check-in and check-out dates are required.";
}

// Validate dates
$has_dates = !empty($checkin_date) && !empty($checkout_date);
$today = date('Y-m-d');

// Initialize variables at the start
$conditions = [];
$params = [];
$types = '';
$query_parts = [];

// Base query
$base_query = "SELECT rt.* FROM roomtypetb rt";

// Add guest capacity filter if dates are valid
if ($has_dates && $checkin_date >= $today && $checkout_date > $checkin_date) {
    $conditions[] = "rt.RoomCapacity >= ?";
    $params[] = $totelGuest;
    $types .= 'i';
}

// Facilities filter
if (isset($_GET['facilities']) && is_array($_GET['facilities']) && !empty($_GET['facilities'])) {
    // Sanitize the facility IDs
    $facility_ids = array_filter($_GET['facilities'], function ($id) {
        return is_string($id) && preg_match('/^[a-zA-Z0-9_-]+$/', $id);
    });

    if (!empty($facility_ids)) {
        $placeholders = implode(',', array_fill(0, count($facility_ids), '?'));
        $conditions[] = "rt.RoomTypeID IN (
            SELECT DISTINCT RoomTypeID 
            FROM roomtypefacilitytb 
            WHERE FacilityID IN ($placeholders)
        )";

        foreach ($facility_ids as $id) {
            $params[] = $id;
            $types .= 's';
        }
    }
}

// Rating filters
if (isset($_GET['ratings']) && is_array($_GET['ratings']) && !empty($_GET['ratings'])) {
    $max_rating = max(array_map('intval', $_GET['ratings']));
    $conditions[] = "(SELECT COALESCE(AVG(Rating), 0) FROM roomtypereviewtb WHERE RoomTypeID = rt.RoomTypeID) BETWEEN 1 AND ?";
    $params[] = $max_rating;
    $types .= 'i';
}

// Build WHERE clause
if (!empty($conditions)) {
    $base_query .= " WHERE " . implode(' AND ', $conditions);
}

// Sorting
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'top_picks';
switch ($sort) {
    case 'price_low_high':
        $base_query .= " ORDER BY rt.RoomPrice ASC";
        break;
    case 'price_high_low':
        $base_query .= " ORDER BY rt.RoomPrice DESC";
        break;
    case 'rating':
        $base_query .= " ORDER BY (SELECT COALESCE(AVG(Rating), 0) FROM roomtypereviewtb WHERE RoomTypeID = rt.RoomTypeID) DESC";
        break;
    default:
        $base_query .= " ORDER BY rt.RoomTypeID DESC";
}

// Prepare and execute the statement
$stmt = $connect->prepare($base_query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$available_rooms = $result->fetch_all(MYSQLI_ASSOC);
$foundProperties = count($available_rooms);
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

<body class="relative min-w-[380px]">
    <?php
    include('../includes/navbar.php');
    include('../includes/cookies.php');
    ?>

    <!-- Login Modal -->
    <?php
    include('../includes/login_request.php');
    ?>

    <main class="pb-4">
        <div class="relative swiper-container flex justify-center">
            <!-- Desktop Form (shown on lg screens and up) -->
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get"
                class="availability-form hidden lg:flex w-full sm:max-w-[1030px] z-10 p-4 bg-white border-b justify-between items-end space-x-4">
                <input type="hidden" name="reservation_id" value="<?= $reservation_id ?>">

                <div class="flex items-center space-x-4 w-full">
                    <div class="flex gap-3 w-full">
                        <!-- Check-in Date -->
                        <div class="w-full">
                            <label class="font-semibold text-blue-900 block mb-1">Check-In Date</label>
                            <input type="date" id="checkin-date" name="checkin_date"
                                class="w-full p-3 border border-gray-300 rounded-sm outline-none"
                                max="<?php echo date('Y-m-d', strtotime('+1 year')); ?>"
                                value="<?php echo isset($_GET['checkin_date']) ? $_GET['checkin_date'] : ''; ?>"
                                placeholder="Check-in Date">
                            <?php if (isset($_SESSION['alert'])): ?>
                                <div class="absolute -bottom-5 text-sm text-red-500">
                                    <?= $_SESSION['alert'] ?>
                                </div>
                                <?php unset($_SESSION['alert']); ?>
                            <?php endif; ?>
                        </div>
                        <!-- Check-out Date -->
                        <div class="w-full">
                            <label class="font-semibold text-blue-900 block mb-1">Check-Out Date</label>
                            <input type="date" id="checkout-date" name="checkout_date"
                                class="w-full p-3 border border-gray-300 rounded-sm outline-none"
                                value="<?php echo isset($_GET['checkout_date']) ? $_GET['checkout_date'] : ''; ?>"
                                placeholder="Check-out Date">
                        </div>
                    </div>
                    <div class="flex gap-3 w-full">
                        <!-- Adults -->
                        <div class="w-full">
                            <label class="font-semibold text-blue-900 block mb-1">Adults</label>
                            <select id="adults" name="adults" class="w-full p-3 border border-gray-300 rounded-sm outline-none">
                                <?php for ($i = 1; $i <= 6; $i++): ?>
                                    <option value="<?= $i ?>" <?= $adults == $i ? 'selected' : '' ?>><?= $i ?> Adult<?= $i > 1 ? 's' : '' ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <!-- Children -->
                        <div class="w-full">
                            <label class="font-semibold text-blue-900 block mb-1">Children</label>
                            <select id="children" name="children" class="w-full p-3 border border-gray-300 rounded-sm outline-none">
                                <?php for ($i = 0; $i <= 5; $i++): ?>
                                    <option value="<?= $i ?>" <?= $children == $i ? 'selected' : '' ?>><?= $i ?> <?= $i == 1 ? 'Child' : 'Children' ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Search Button -->
                <button type="submit" name="check_availability"
                    class="p-3 mb-0.5 bg-blue-900 text-white rounded-sm hover:bg-blue-950 uppercase font-semibold transition-colors duration-300 select-none whitespace-nowrap">
                    Check Availability
                </button>
            </form>

            <!-- Mobile Check-In Button (shown on small screens) -->
            <div id="mobileButtonsWrapper" class="lg:hidden fixed bottom-3 right-3 gap-3 z-20 transform transition-all duration-300">
                <div id="mobileFilterButton">
                    <button class="bg-amber-500 text-white p-3 rounded-full shadow-lg hover:bg-orange-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                    </button>
                </div>

                <button id="mobile-checkin-button" class="bg-blue-900 text-white mt-1 p-3 rounded-full shadow-md hover:bg-blue-950 transform transition-all duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
            </div>

            <!-- Mobile Check-In Slide-Up Form -->
            <div id="mobile-checkin-form" class="lg:hidden fixed bottom-0 left-0 right-0 bg-white p-4 border-t shadow-md z-40 transform translate-y-full transition-transform duration-500">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-blue-900 font-semibold text-lg">Book a Room</h2>
                    <button id="close-mobile-search" class="text-red-500 font-bold text-lg">&times;</button>
                </div>
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get" class="availability-form flex flex-col space-y-3">
                    <input type="hidden" name="reservation_id" value="<?= $reservation_id ?>">

                    <!-- Check-in Date -->
                    <div>
                        <label class="font-semibold text-blue-900">Check-In Date</label>
                        <input type="date" id="mobile-checkin-date" name="checkin_date"
                            class="p-2 border border-gray-300 rounded-sm w-full"
                            max="<?php echo date('Y-m-d', strtotime('+1 year')); ?>"
                            value="<?php echo isset($_GET['checkin_date']) ? $_GET['checkin_date'] : ''; ?>" required>
                    </div>
                    <!-- Check-out Date -->
                    <div>
                        <label class="font-semibold text-blue-900">Check-Out Date</label>
                        <input type="date" id="mobile-checkout-date" name="checkout_date"
                            class="p-2 border border-gray-300 rounded-sm w-full"
                            value="<?php echo isset($_GET['checkout_date']) ? $_GET['checkout_date'] : ''; ?>" required>
                    </div>
                    <!-- Adults -->
                    <div>
                        <label class="font-semibold text-blue-900">Adults</label>
                        <select name="adults" class="p-2 border border-gray-300 rounded-sm w-full">
                            <?php for ($i = 1; $i <= 6; $i++): ?>
                                <option value="<?= $i ?>" <?= $adults == $i ? 'selected' : '' ?>><?= $i ?> Adult<?= $i > 1 ? 's' : '' ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <!-- Children -->
                    <div>
                        <label class="font-semibold text-blue-900">Children</label>
                        <select name="children" class="p-2 border border-gray-300 rounded-sm w-full">
                            <?php for ($i = 0; $i <= 5; $i++): ?>
                                <option value="<?= $i ?>" <?= $children == $i ? 'selected' : '' ?>><?= $i ?> <?= $i == 1 ? 'Child' : 'Children' ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <!-- Submit Button -->
                    <button type="submit" name="check_availability" class="p-3 bg-blue-900 text-white rounded-sm hover:bg-blue-950 uppercase font-semibold transition">
                        Check Availability
                    </button>
                </form>
            </div>

            <script>
                // Set min dates for date inputs
                document.addEventListener('DOMContentLoaded', () => {
                    const today = new Date().toISOString().split('T')[0];
                    const checkInDateInput = document.getElementById('checkin-date');
                    const checkOutDateInput = document.getElementById('checkout-date');
                    const mobileCheckInInput = document.getElementById('mobile-checkin-date');
                    const mobileCheckOutInput = document.getElementById('mobile-checkout-date');

                    if (checkInDateInput) checkInDateInput.setAttribute('min', today);
                    if (checkOutDateInput) checkOutDateInput.setAttribute('min', today);
                    if (mobileCheckInInput) mobileCheckInInput.setAttribute('min', today);
                    if (mobileCheckOutInput) mobileCheckOutInput.setAttribute('min', today);
                });
            </script>
        </div>

        <section class="max-w-[1200px] mx-auto px-4 py-8">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
                <h1 id="propertiesCount" class="text-xl sm:text-2xl font-bold text-gray-800">Rooms: <?= $foundProperties ?> properties found</h1>

                <div class="flex items-center justify-between sm:justify-start gap-4">
                    <div class="text-sm">
                        <span class="text-gray-600 hidden sm:inline">Sort by:</span>
                        <select id="sortRooms" class="ml-0 sm:ml-2 border-none font-medium focus:ring-0 outline-none bg-gray-100 sm:bg-transparent px-3 py-1 rounded sm:px-0 sm:py-0">
                            <option value="top_picks" data-clicked="false">Our top picks</option>
                            <option value="price_low_high" data-clicked="false">Price (low to high)</option>
                            <option value="price_high_low" data-clicked="false">Price (high to low)</option>
                            <option value="rating" data-clicked="false">Star rating</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="mb-4 text-sm text-gray-600 border-b pb-4">
                Please review any travel advisories provided by your government to make an informed decision about your stay in this area, which may be considered conflict-affected.
            </div>

            <div class="flex flex-col md:flex-row gap-3">
                <!-- Desktop Filter Panel (visible on md and larger screens) -->
                <aside class="hidden lg:block w-full md:w-1/4">
                    <form method="GET" action="" class="bg-white p-4 rounded-md shadow-sm border sticky top-4" id="filterForm">
                        <!-- Preserve existing GET parameters -->
                        <input type="hidden" name="checkin_date" value="<?= $checkin_date ?>">
                        <input type="hidden" name="checkout_date" value="<?= $checkout_date ?>">
                        <input type="hidden" name="adults" value="<?= $adults ?>">
                        <input type="hidden" name="children" value="<?= $children ?>">
                        <input type="hidden" name="roomTypeID" value="<?= $roomtype['RoomTypeID'] ?>">
                        <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
                        <?php if ($has_dates): ?>
                            <input type="hidden" name="checkin" value="<?= htmlspecialchars($checkin_date) ?>">
                            <input type="hidden" name="checkout" value="<?= htmlspecialchars($checkout_date) ?>">
                            <input type="hidden" name="guests" value="<?= htmlspecialchars($totelGuest) ?>">
                        <?php endif; ?>

                        <h3 class="text-lg font-semibold text-gray-700">Filter by:</h3>

                        <h4 class="font-medium text-gray-800 my-4">Facilities</h4>
                        <div class="space-y-3">
                            <?php
                            $select = "SELECT * FROM facilitytb";
                            $query = $connect->query($select);
                            $count = $query->num_rows;
                            if ($count) {
                                for ($i = 0; $i < $count; $i++) {
                                    $row = $query->fetch_assoc();
                                    $faculty_id = $row['FacilityID'];
                                    $facility = $row['Facility'];
                                    $checked = isset($_GET['facilities']) && in_array($faculty_id, $_GET['facilities']) ? 'checked' : '';
                            ?>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="facilities[]" value="<?= $faculty_id ?>" class="mr-2 rounded text-orange-500 w-5 h-4 auto-submit" data-clicked="false" <?= $checked ?>>
                                        <span class="text-sm"><?= htmlspecialchars($facility) ?></span>
                                    </label>
                            <?php
                                }
                            } else {
                                echo "<option value='' disabled>No data yet</option>";
                            }
                            ?>
                        </div>

                        <h4 class="font-medium text-gray-800 my-4">Room rating</h4>
                        <div class="space-y-3">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <label class="flex items-center">
                                    <input type="checkbox" name="ratings[]" value="<?= $i ?>" class="mr-2 rounded text-orange-500 w-5 h-4 auto-submit" data-clicked="false"
                                        <?= isset($_GET['ratings']) && in_array($i, $_GET['ratings']) ? 'checked' : '' ?>>
                                    <span class="text-sm"><?= $i ?> star<?= $i > 1 ? 's' : '' ?></span>
                                </label>
                            <?php endfor; ?>
                        </div>

                        <!-- Keep the submit button for accessibility (hidden but still functional) -->
                        <button type="submit" class="hidden">Apply Filters</button>
                    </form>
                </aside>

                <!-- Mobile Filter Sidebar (hidden by default) -->
                <div id="mobileFilterSidebar" class="fixed inset-0 z-40 -translate-x-full transition-transform duration-500">
                    <!-- Sidebar Content -->
                    <div class="absolute inset-y-0 left-0 w-4/5 max-w-sm bg-white overflow-y-auto transform transition-transform duration-300 ease-in-out -translate-x-full" id="sidebarContent">
                        <div class="p-4">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-gray-700">Filter by</h3>
                                <button id="closeMobileFilter" class="text-gray-500 hover:text-gray-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <form method="GET" action="" id="mobileFilterForm">
                                <!-- Preserve existing GET parameters -->
                                <input type="hidden" name="checkin_date" value="<?= $checkin_date ?>">
                                <input type="hidden" name="checkout_date" value="<?= $checkout_date ?>">
                                <input type="hidden" name="adults" value="<?= $adults ?>">
                                <input type="hidden" name="children" value="<?= $children ?>">
                                <input type="hidden" name="roomTypeID" value="<?= $roomtype['RoomTypeID'] ?>">
                                <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
                                <?php if ($has_dates): ?>
                                    <input type="hidden" name="checkin" value="<?= htmlspecialchars($checkin_date) ?>">
                                    <input type="hidden" name="checkout" value="<?= htmlspecialchars($checkout_date) ?>">
                                    <input type="hidden" name="guests" value="<?= htmlspecialchars($totelGuest) ?>">
                                <?php endif; ?>

                                <h4 class="font-medium text-gray-800 my-4">Facilities</h4>
                                <div class="space-y-3">
                                    <?php
                                    $select = "SELECT * FROM facilitytb";
                                    $query = $connect->query($select);
                                    $count = $query->num_rows;
                                    if ($count) {
                                        for ($i = 0; $i < $count; $i++) {
                                            $row = $query->fetch_assoc();
                                            $faculty_id = $row['FacilityID'];
                                            $facility = $row['Facility'];
                                            $checked = isset($_GET['facilities']) && in_array($faculty_id, $_GET['facilities']) ? 'checked' : '';
                                    ?>
                                            <label class="flex items-center">
                                                <input type="checkbox" name="facilities[]" value="<?= $faculty_id ?>" class="mr-2 rounded text-orange-500 w-5 h-4 mobile-auto-submit" data-clicked="false" <?= $checked ?>>
                                                <span class="text-sm"><?= htmlspecialchars($facility) ?></span>
                                            </label>
                                    <?php
                                        }
                                    } else {
                                        echo "<option value='' disabled>No data yet</option>";
                                    }
                                    ?>
                                </div>

                                <h4 class="font-medium text-gray-800 my-4">Room rating</h4>
                                <div class="space-y-3">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="ratings[]" value="<?= $i ?>" class="mr-2 rounded text-orange-500 w-5 h-4 mobile-auto-submit" data-clicked="false"
                                                <?= isset($_GET['ratings']) && in_array($i, $_GET['ratings']) ? 'checked' : '' ?>>
                                            <span class="text-sm"><?= $i ?> star<?= $i > 1 ? 's' : '' ?></span>
                                        </label>
                                    <?php endfor; ?>
                                </div>

                                <div class="mt-6">
                                    <button type="submit" class="w-full bg-orange-500 text-white py-2 px-4 rounded-md hover:bg-orange-600 transition-colors">
                                        Apply Filters
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="w-full space-y-2">
                    <!-- Entire Homes CTA - Responsive Version -->
                    <div class="bg-white p-4 rounded-md shadow-sm border">
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 sm:gap-4">
                            <div class="flex-1">
                                <h3 class="font-medium text-gray-800 text-base sm:text-lg">Looking for a space of your own?</h3>
                                <p class="text-sm text-gray-600 mt-1">Find privacy and peace of mind with an entire home or apartment to yourself</p>
                            </div>
                            <button class="text-orange-500 font-medium hover:text-orange-600 whitespace-nowrap text-right sm:text-left mt-2 sm:mt-0">
                                Show entire homes & apartments →
                            </button>
                        </div>
                    </div>
                    <div id="room-results-container">
                        <?php include 'room_results.php'; ?>
                    </div>
                </div>
            </div>
        </section>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Initialize favorite buttons
                initFavoriteButtons();

                // Set selected option based on current sort
                const currentSort = new URLSearchParams(window.location.search).get('sort') || 'top_picks';
                const sortSelect = document.getElementById('sortRooms');
                sortSelect.value = currentSort;

                // Track clicked state for sort options
                const sortOptions = Array.from(sortSelect.options);
                sortOptions.forEach(option => {
                    if (option.value === currentSort) {
                        option.dataset.clicked = "true";
                    }
                });

                // Add event listener for sorting
                sortSelect.addEventListener('change', function() {
                    const selectedOption = sortSelect.options[sortSelect.selectedIndex];
                    const showLoading = selectedOption.dataset.clicked === 'false';

                    if (showLoading) {
                        selectedOption.dataset.clicked = 'true';
                    }

                    const url = new URL(window.location.href);
                    url.searchParams.set('sort', this.value);

                    if (showLoading) {
                        showLoadingState();
                    }

                    const availabilityForm = document.querySelector('.availability-form');
                    const formData = new URLSearchParams(new FormData(availabilityForm));
                    formData.append('sort', this.value);
                    formData.append('ajax_request', '1');

                    const fetchUrl = availabilityForm.action + '?' + formData.toString();

                    fetchResults(fetchUrl, showLoading);
                });

                // Auto-submit for desktop filters
                document.querySelectorAll('.auto-submit').forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        const form = document.getElementById('filterForm');
                        const showLoading = this.dataset.clicked === 'false';
                        if (showLoading) {
                            this.dataset.clicked = 'true';
                        }
                        submitFilterForm(form, showLoading);
                    });
                });

                // Auto-submit for mobile filters
                document.querySelectorAll('.mobile-auto-submit').forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        const showLoading = this.dataset.clicked === 'false';
                        if (showLoading) {
                            this.dataset.clicked = 'true';
                        }
                    });
                });

                document.getElementById('filterForm').addEventListener('submit', function(e) {
                    e.preventDefault();
                    submitFilterForm(this, true);
                });

                document.getElementById('mobileFilterForm').addEventListener('submit', function(e) {
                    e.preventDefault();
                    submitFilterForm(this, true);
                    document.getElementById('mobileFilterSidebar').classList.add('-translate-x-full');
                });

                function submitFilterForm(form, showLoading) {
                    const formData = new URLSearchParams(new FormData(form));
                    formData.append('ajax_request', '1');

                    const availabilityForm = document.querySelector('.availability-form');
                    const baseUrl = availabilityForm.action;
                    const fetchUrl = baseUrl + '?' + formData.toString();

                    if (showLoading) {
                        showLoadingState();
                    }

                    fetchResults(fetchUrl, showLoading);
                }

                function showLoadingState() {
                    document.getElementById('room-results-container').innerHTML = `
            <div class="w-full space-y-2">
                ${Array(3).fill().map(() => `
                <div class="bg-white overflow-hidden rounded-md shadow-sm border animate-pulse">
                    <div class="flex flex-col md:flex-row">
                        <div class="w-full md:w-[28%] h-48 sm:h-56 md:h-[261px] bg-gray-200"></div>
                        <div class="w-full md:w-2/3 p-4 space-y-4">
                            <div class="flex justify-between">
                                <div class="space-y-3 w-2/3">
                                    <div class="h-6 bg-gray-200 rounded w-3/4"></div>
                                    <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                                    <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                                </div>
                                <div class="space-y-1 w-1/3 text-right">
                                    <div class="h-3 bg-gray-200 rounded ml-auto w-2/3"></div>
                                    <div class="h-5 bg-gray-200 rounded ml-auto w-1/2"></div>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <div class="h-4 bg-gray-200 rounded w-full"></div>
                                <div class="h-4 bg-gray-200 rounded w-5/6"></div>
                                <div class="h-4 bg-gray-200 rounded w-4/6"></div>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                ${Array(5).fill().map(() => `<div class="h-6 bg-gray-200 rounded w-16"></div>`).join('')}
                            </div>
                        </div>
                    </div>
                </div>
                `).join('')}
            </div>
        `;
                }

                function fetchResults(url, shouldDelay) {
                    fetch(url, {
                            method: 'GET',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'text/html'
                            }
                        })
                        .then(response => {
                            if (!response.ok) throw new Error('Network response was not ok');
                            return response.text();
                        })
                        .then(data => {
                            const processData = () => {
                                const tempDiv = document.createElement('div');
                                tempDiv.innerHTML = data;

                                const newContent = tempDiv.querySelector('#room-results-container');
                                const countElement = tempDiv.querySelector('#propertiesCount');
                                const newCount = countElement ? countElement.textContent : '0 properties found';

                                if (newContent) {
                                    document.getElementById('room-results-container').innerHTML = newContent.innerHTML;
                                    document.getElementById('propertiesCount').textContent = newCount;
                                    window.history.pushState({
                                        path: url.toString()
                                    }, '', url.toString());

                                    // FIX: Reinitialize favorite buttons after AJAX content update
                                    initFavoriteButtons();
                                } else {
                                    throw new Error('Invalid response format');
                                }
                            };

                            if (shouldDelay) {
                                setTimeout(processData, 1000);
                            } else {
                                processData();
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            document.getElementById('room-results-container').innerHTML = `
                <div class="max-w-[1200px] mx-auto px-4 py-16 text-center">
                    <div class="bg-white p-8 rounded-lg shadow-sm border max-w-2xl mx-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h2 class="text-xl font-bold text-gray-800 mt-4">Error Loading Results</h2>
                        <p class="text-gray-600 mt-2">We couldn't load the search results. Please try again.</p>
                        <div class="mt-6">
                            <button onclick="window.location.reload()" class="inline-block bg-orange-500 text-white px-6 py-2 rounded-md hover:bg-orange-600 transition-colors select-none">
                                Try Again
                            </button>
                        </div>
                    </div>
                </div>
            `;
                            document.getElementById('propertiesCount').textContent = 'Rooms: 0 properties found';
                        });
                }

                const availabilityForms = document.querySelectorAll('.availability-form');
                availabilityForms.forEach(function(availabilityForm) {
                    availabilityForm.addEventListener('submit', function(e) {
                        e.preventDefault();
                        const formData = new URLSearchParams(new FormData(availabilityForm));
                        formData.append('ajax_request', '1');
                        showLoadingState();
                        const url = availabilityForm.action + '?' + formData.toString();
                        fetchResults(url, true);
                    });
                });

                // CLEAR ALERTS ON INPUT
                const forms = document.querySelectorAll('.availability-form');
                forms.forEach(form => {
                    form.querySelectorAll('input, select').forEach(input => {
                        input.addEventListener('input', function() {
                            const alert = document.querySelector('.text-sm.text-red-500');
                            if (alert) alert.remove();
                        });
                    });

                    form.addEventListener('submit', function() {
                        const alert = document.querySelector('.text-sm.text-red-500');
                        if (alert) alert.remove();
                    });
                });

                // INITIALIZE FAVORITE BUTTONS ON PAGE LOAD
                initFavoriteButtons();
            });

            // FUNCTION TO REBIND FAVORITE BUTTON EVENTS
            function initFavoriteButtons() {
                const loginModal = document.getElementById('loginModal');
                const darkOverlay2 = document.getElementById('darkOverlay2');
                const sparkleColors = [
                    'bg-amber-500', 'bg-red-500', 'bg-pink-500', 'bg-yellow-400', 'bg-white', 'bg-blue-300'
                ];

                document.querySelectorAll('.favoriteForm').forEach(favoriteForm => {
                    const favoriteBtn = favoriteForm.querySelector('.favoriteBtn');
                    const heartIcon = favoriteForm.querySelector('.heartIcon');
                    const heartParticles = favoriteForm.querySelector('.heartParticles');

                    favoriteForm.addEventListener('submit', function(e) {
                        e.preventDefault();
                        const formData = new FormData(this);
                        heartIcon.classList.add('animate-bounce');
                        favoriteBtn.disabled = true;

                        fetch('../User/favorite_handler.php', {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'Accept': 'application/json'
                                }
                            })
                            .then(response => {
                                if (!response.ok) throw new Error('Network response was not ok');
                                return response.json();
                            })
                            .then(data => {
                                if (data.status === 'added') {
                                    heartIcon.classList.remove('text-slate-400', 'hover:text-red-300');
                                    heartIcon.classList.add('text-red-500', 'hover:text-red-600');
                                    createSparkleEffect(heartParticles);
                                } else if (data.status === 'removed') {
                                    heartIcon.classList.remove('text-red-500', 'hover:text-red-600');
                                    heartIcon.classList.add('text-slate-400', 'hover:text-red-300');
                                } else if (data.status === 'not_logged_in') {
                                    if (loginModal && darkOverlay2) {
                                        loginModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                                        darkOverlay2.classList.remove('opacity-0', 'invisible');
                                        darkOverlay2.classList.add('opacity-100');
                                        const closeLoginModal = document.getElementById('closeLoginModal');
                                        closeLoginModal.addEventListener('click', function() {
                                            loginModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
                                            darkOverlay2.classList.add('opacity-0', 'invisible');
                                            darkOverlay2.classList.remove('opacity-100');
                                        });
                                    }
                                }
                            })
                            .catch(error => console.error('Error:', error))
                            .finally(() => {
                                setTimeout(() => {
                                    heartIcon.classList.remove('animate-bounce');
                                    favoriteBtn.disabled = false;
                                }, 500);
                            });
                    });

                    function createSparkleEffect(heartParticles) {
                        heartParticles.innerHTML = '';
                        for (let i = 0; i < 5; i++) {
                            const sparkle = document.createElement('div');
                            const randomColor = sparkleColors[Math.floor(Math.random() * sparkleColors.length)];
                            sparkle.className = `absolute w-1.5 h-1.5 ${randomColor} rounded-full opacity-0`;
                            sparkle.style.left = `${30 + Math.random() * 40}%`;
                            sparkle.style.top = `${30 + Math.random() * 40}%`;

                            sparkle.animate([{
                                    transform: 'translate(0, 0) scale(0.5)',
                                    opacity: 0
                                },
                                {
                                    transform: `translate(${(Math.random() - 0.5) * 10}px, ${(Math.random() - 0.5) * 10}px) scale(1.8)`,
                                    opacity: 0.9,
                                    offset: 0.5
                                },
                                {
                                    transform: `translate(${(Math.random() - 0.5) * 20}px, ${(Math.random() - 0.5) * 20}px) scale(0.2)`,
                                    opacity: 0
                                }
                            ], {
                                duration: 1000,
                                delay: i * 150,
                                easing: 'cubic-bezier(0.4, 0, 0.2, 1)'
                            });

                            heartParticles.appendChild(sparkle);
                            setTimeout(() => sparkle.remove(), 1150 + i * 150);
                        }
                    }
                });
            }
        </script>

        <style>
            .animate-pulse {
                animation: pulse 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
            }

            @keyframes pulse {

                0%,
                100% {
                    opacity: 1;
                }

                50% {
                    opacity: 0.5;
                }
            }
        </style>
    </main>

    <!-- MoveUp Btn -->
    <?php
    include('../includes/moveup_btn.php');
    include('../includes/alert.php');
    include('../includes/footer.php');
    ?>

    <script src="//unpkg.com/alpinejs" defer></script>
    <script type="module" src="../JS/index.js"></script>
</body>

</html>