<?php
session_start();
include('../config/dbConnection.php');

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

// Base query (your existing PHP code remains exactly the same)
$base_query = "SELECT rt.* FROM roomtypetb rt";

// Initialize conditions array for WHERE clauses
$conditions = [];

// Add guest capacity filter if dates are valid
if ($has_dates && $checkin_date >= $today && $checkout_date > $checkin_date) {
    $conditions[] = "rt.RoomCapacity >= $totelGuest";
}

if (isset($_GET['facilities']) && is_array($_GET['facilities']) && !empty($_GET['facilities'])) {
    $facility_ids = array_map('intval', $_GET['facilities']);
    $facility_ids = array_filter($facility_ids);

    if (!empty($facility_ids)) {
        $placeholders = implode(',', array_fill(0, count($facility_ids), '?'));
        $conditions[] = "rt.RoomTypeID IN (
            SELECT DISTINCT RoomTypeID 
            FROM roomtypefacilitytb 
            WHERE FacilityID IN ($placeholders)
        )";

        foreach ($facility_ids as $id) {
            $params[] = $id;
            $types .= 'i';
        }
    }
}

// Process rating filters - Modified to show selected rating AND lower ratings
if (isset($_GET['ratings']) && is_array($_GET['ratings']) && !empty($_GET['ratings'])) {
    $rating_conditions = [];
    $max_rating = max(array_map('intval', $_GET['ratings'])); // Get the highest selected rating

    // Only need one condition since we're checking "less than or equal to" the max selected rating
    $rating_conditions[] = "(SELECT COALESCE(AVG(Rating), 0) FROM roomtypereviewtb WHERE RoomTypeID = rt.RoomTypeID) <= $max_rating";

    // Also ensure the rating is at least 1 (if you want to exclude unrated items)
    $rating_conditions[] = "(SELECT COALESCE(AVG(Rating), 0) FROM roomtypereviewtb WHERE RoomTypeID = rt.RoomTypeID) >= 1";

    if (!empty($rating_conditions)) {
        $conditions[] = "(" . implode(' AND ', $rating_conditions) . ")";
    }
}

// Add all conditions to the query
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
        if (strpos($base_query, 'WHERE') === false) {
            $base_query .= " WHERE rt.RoomTypeID IN (SELECT RoomTypeID FROM roomtypereviewtb)";
        } else {
            $base_query .= " AND rt.RoomTypeID IN (SELECT RoomTypeID FROM roomtypereviewtb)";
        }
        $base_query .= " ORDER BY (SELECT AVG(Rating) FROM roomtypereviewtb WHERE RoomTypeID = rt.RoomTypeID) DESC";
        break;
    default:
        $base_query .= " ORDER BY rt.RoomTypeID DESC";
}

// Execute query
$stmt = $connect->prepare($base_query);
$stmt->execute();
$result = $stmt->get_result();
$available_rooms = $result->fetch_all(MYSQLI_ASSOC);
$foundProperties = count($available_rooms);

// Add room to favorites
if (isset($_POST['room_favourite'])) {
    if ($userID) {
        $roomTypeID = $_POST['roomTypeID'];

        // Get search parameters from POST data (they should be included in the form submission)
        $checkin_date = isset($_POST['checkin_date']) ? $_POST['checkin_date'] : '';
        $checkout_date = isset($_POST['checkout_date']) ? $_POST['checkout_date'] : '';
        $adults = isset($_POST['adults']) ? intval($_POST['adults']) : 1;
        $children = isset($_POST['children']) ? intval($_POST['children']) : 0;

        $check = "SELECT COUNT(*) as count FROM roomtypefavoritetb WHERE UserID = '$userID' AND RoomTypeID = '$roomTypeID'";
        $result = $connect->query($check);
        $count = $result->fetch_assoc()['count'];

        if ($count == 0) {
            $insert = "INSERT INTO roomtypefavoritetb (UserID, RoomTypeID, CheckInDate, CheckOutDate, Adult, Children) 
                      VALUES ('$userID', '$roomTypeID', '$checkin_date', '$checkout_date', '$adults', '$children')";
            $connect->query($insert);
        } else {
            $delete = "DELETE FROM roomtypefavoritetb WHERE UserID = '$userID' AND RoomTypeID = '$roomTypeID'";
            $connect->query($delete);
        }

        // Redirect back with the same search parameters
        $redirect_url = "RoomBooking.php?checkin_date=$checkin_date&checkout_date=$checkout_date&adults=$adults&children=$children";
        header("Location: $redirect_url");
        exit();
    } else {
        $showLoginModal = true;
    }
}
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
    include('../includes/Navbar.php');
    include('../includes/Cookies.php');
    ?>

    <!-- Login Modal -->
    <div id="loginModal" class="fixed inset-0 z-50 flex items-center justify-center <?php echo !empty($showLoginModal) ? '' : 'hidden'; ?>">
        <!-- Overlay -->
        <div class="absolute inset-0 bg-black opacity-50"></div>

        <!-- Modal Container -->
        <div class="relative bg-white rounded-md shadow-2xl max-w-md w-full mx-4 z-10 overflow-hidden">
            <!-- Modal Header -->
            <div class="bg-blue-950 p-6 flex justify-between items-center">
                <div class="flex items-center space-x-3">

                    <h3 class="text-xl font-semibold text-white">Sign In for Full Access</h3>
                </div>

                <button
                    type="button"
                    onclick="closeModal()"
                    class="text-white">
                    <i class="ri-close-line"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6">
                <div class="flex items-start mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-500 mt-0.5 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div>
                        <p class="text-gray-700 font-medium">To save favorite rooms and access all features:</p>
                        <ul class="list-disc list-inside text-gray-600 mt-2 space-y-1 pl-4">
                            <li>Save unlimited favorite rooms</li>
                            <li>Get personalized recommendations</li>
                            <li>Access booking history</li>
                            <li>Receive exclusive member deals</li>
                        </ul>
                    </div>
                </div>

                <div class="bg-blue-50 p-4 rounded-lg">
                    <div class="flex">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-800 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9z" clip-rule="evenodd" />
                        </svg>
                        <p class="text-sm text-blue-800">New to our site? Creating an account only takes 30 seconds!</p>
                    </div>
                </div>

                <div class="flex flex-col space-y-2 my-3">
                    <a
                        href="../User/UserSignIn.php"
                        class="px-6 py-2.5 bg-amber-500 text-white rounded-md hover:from-indigo-700 hover:to-purple-700 transition-colors font-medium text-center shadow-sm select-none">
                        Sign In
                    </a>
                    <a
                        href="../User/UserSignUp.php"
                        class="text-xs text-center text-blue-900 hover:text-blue-800 hover:underline transition-colors">
                        Don't have an account? Register
                    </a>
                </div>

                <!-- Footer matching the site's advisory note style -->
                <div class="bg-gray-50 px-6 py-3 border-t border-gray-200">
                    <p class="text-xs text-gray-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Remember to review any travel advisories before booking.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function closeModal() {
            document.getElementById('loginModal').classList.add('hidden');
            // Remove the favorite POST parameter from URL if page was refreshed
            if (window.history.replaceState) {
                window.history.replaceState(null, null, window.location.pathname);
            }
        }

        // Close modal when clicking outside
        document.getElementById('loginModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>

    <main class="pb-4">
        <div class="relative swiper-container flex justify-center">
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get" class="w-full sm:max-w-[1030px] z-10 p-4 bg-white border-b flex justify-between items-center space-x-4">
                <div class="flex items-center space-x-4">
                    <div class="flex gap-3">
                        <!-- Check-in Date -->
                        <div>
                            <label class="font-semibold text-blue-900">Check-In Date</label>
                            <input type="date" id="checkin-date" name="checkin_date"
                                class="p-3 border border-gray-300 rounded-sm outline-none"
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
                        <div>
                            <label class="font-semibold text-blue-900">Check-Out Date</label>
                            <input type="date" id="checkout-date" name="checkout_date"
                                class="p-3 border border-gray-300 rounded-sm outline-none"
                                value="<?php echo isset($_GET['checkout_date']) ? $_GET['checkout_date'] : ''; ?>"
                                placeholder="Check-out Date">
                        </div>

                        <script>
                            document.addEventListener('DOMContentLoaded', () => {
                                const today = new Date().toISOString().split('T')[0];
                                const checkInDateInput = document.getElementById('checkin-date');
                                const checkOutDateInput = document.getElementById('checkout-date');
                                checkInDateInput.setAttribute('min', today);
                                checkOutDateInput.setAttribute('min', today);
                            });
                        </script>
                    </div>
                    <div class="flex">
                        <!-- Adults -->
                        <select id="adults" name="adults" class="p-3 border border-gray-300 rounded-sm outline-none">
                            <?php for ($i = 1; $i <= 6; $i++): ?>
                                <option value="<?= $i ?>" <?= $adults == $i ? 'selected' : '' ?>><?= $i ?> Adult<?= $i > 1 ? 's' : '' ?></option>
                            <?php endfor; ?>
                        </select>
                        <!-- Children -->
                        <select id="children" name="children" class="p-3 border border-gray-300 rounded-sm outline-none">
                            <?php for ($i = 0; $i <= 5; $i++): ?>
                                <option value="<?= $i ?>" <?= $children == $i ? 'selected' : '' ?>><?= $i ?> <?= $i == 1 ? 'Child' : 'Children' ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>

                <!-- Search Button -->
                <div class="flex items-center space-x-2 select-none">
                    <button type="submit" name="check_availability"
                        class="p-3 bg-blue-900 text-white rounded-sm hover:bg-blue-950 uppercase font-semibold transition-colors duration-300">
                        Check Availability
                    </button>
                </div>
            </form>
        </div>

        <section class="max-w-[1200px] mx-auto px-4 py-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Rooms: <?= $foundProperties ?> properties found</h1>
                <div class="text-sm">
                    <span class="text-gray-600">Sort by:</span>
                    <select id="sortRooms" class="ml-2 border-none font-medium focus:ring-0 outline-none">
                        <option value="top_picks">Our top picks</option>
                        <option value="price_low_high">Price (low to high)</option>
                        <option value="price_high_low">Price (high to low)</option>
                        <option value="rating">Star rating</option>
                    </select>
                </div>
                <script>
                    // Add event listener for sorting
                    document.getElementById('sortRooms').addEventListener('change', function() {
                        const url = new URL(window.location.href);
                        url.searchParams.set('sort', this.value);
                        window.location.href = url.toString();
                    });

                    // Set selected option based on current sort
                    const currentSort = new URLSearchParams(window.location.search).get('sort') || 'top_picks';
                    document.getElementById('sortRooms').value = currentSort;
                </script>
            </div>

            <div class="mb-4 text-sm text-gray-600 border-b pb-4">
                Please review any travel advisories provided by your government to make an informed decision about your stay in this area, which may be considered conflict-affected.
            </div>

            <div class="flex flex-col md:flex-row gap-3">
                <!-- Left: Filter Panel -->
                <aside class="w-full md:w-1/4">
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

                        <h4 class="font-medium text-gray-800 my-4">Popular filters</h4>
                        <div class="space-y-3">
                            <label class="flex items-center">
                                <input type="checkbox" name="sea_view" value="1" class="mr-2 rounded text-orange-500 w-5 h-4 auto-submit" <?= isset($_GET['sea_view']) ? 'checked' : '' ?>>
                                <span class="text-sm">Sea view</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="restaurant" value="1" class="mr-2 rounded text-orange-500 w-5 h-4 auto-submit" <?= isset($_GET['restaurant']) ? 'checked' : '' ?>>
                                <span class="text-sm">Restaurant</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="air_conditioning" value="1" class="mr-2 rounded text-orange-500 w-5 h-4 auto-submit" <?= isset($_GET['air_conditioning']) ? 'checked' : '' ?>>
                                <span class="text-sm">Air conditioning</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="swimming_pool" value="1" class="mr-2 rounded text-orange-500 w-5 h-4 auto-submit" <?= isset($_GET['swimming_pool']) ? 'checked' : '' ?>>
                                <span class="text-sm">Swimming Pool</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="apartments" value="1" class="mr-2 rounded text-orange-500 w-5 h-4 auto-submit" <?= isset($_GET['apartments']) ? 'checked' : '' ?>>
                                <span class="text-sm">Apartments</span>
                            </label>
                        </div>

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
                                        <input type="checkbox" name="facilities[]" value="<?= $faculty_id ?>" class="mr-2 rounded text-orange-500 w-5 h-4 auto-submit" <?= $checked ?>>
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
                                    <input type="checkbox" name="ratings[]" value="<?= $i ?>" class="mr-2 rounded text-orange-500 w-5 h-4 auto-submit"
                                        <?= isset($_GET['ratings']) && in_array($i, $_GET['ratings']) ? 'checked' : '' ?>>
                                    <span class="text-sm"><?= $i ?> star<?= $i > 1 ? 's' : '' ?></span>
                                </label>
                            <?php endfor; ?>
                        </div>

                        <!-- Keep the submit button for accessibility (hidden but still functional) -->
                        <button type="submit" class="hidden">Apply Filters</button>
                    </form>

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            // Show loading skeleton when filters are changed
                            const filterForm = document.getElementById('filterForm');
                            const mainContent = document.querySelector('.hotel-listings-container');

                            // Create skeleton loading element
                            const skeletonLoading = document.createElement('div');
                            skeletonLoading.className = 'skeleton-loading';
                            skeletonLoading.innerHTML = `
                        
                            <!-- Hotel Listings Skeleton (3 items) -->
                            ${Array(3).fill().map(() => `
                                <div class="bg-white overflow-hidden animate-pulse mb-2">
                                    <div class="flex flex-col md:flex-row rounded-md shadow-sm border">
                                        <div class="md:w-[28%] h-64 bg-gray-200 rounded-l-md"></div>
                                        <div class="md:w-2/3 p-4 space-y-3">
                                            <div class="flex justify-between">
                                                <div class="h-6 bg-gray-200 rounded w-1/2"></div>
                                                <div class="h-6 bg-gray-200 rounded w-1/4"></div>
                                            </div>
                                            <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                                            <div class="h-3 bg-gray-200 rounded w-full"></div>
                                            <div class="h-3 bg-gray-200 rounded w-4/5"></div>
                                            <div class="flex flex-wrap gap-2 mt-4">
                                                <div class="h-4 bg-gray-200 rounded w-16"></div>
                                                <div class="h-4 bg-gray-200 rounded w-20"></div>
                                                <div class="h-4 bg-gray-200 rounded w-12"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    `;

                            // Auto-submit when any checkbox is clicked
                            document.querySelectorAll('.auto-submit').forEach(function(checkbox) {
                                checkbox.addEventListener('change', function() {
                                    // Show loading skeleton
                                    if (mainContent) {
                                        mainContent.innerHTML = '';
                                        mainContent.appendChild(skeletonLoading);
                                    }

                                    // For checkboxes with the same name (like facilities[]), we need to ensure all are included
                                    if (this.name.endsWith('[]')) {
                                        document.querySelectorAll('input[name="' + this.name + '"]').forEach(function(cb) {
                                            if (!cb.checked) {
                                                // Add hidden input for unchecked boxes to maintain state
                                                const hiddenInput = document.createElement('input');
                                                hiddenInput.type = 'hidden';
                                                hiddenInput.name = cb.name;
                                                hiddenInput.value = cb.value;
                                                hiddenInput.classList.add('temp-hidden');
                                                document.getElementById('filterForm').appendChild(hiddenInput);
                                            }
                                        });

                                        // Remove any existing temp hidden inputs for this checkbox
                                        document.querySelectorAll('input.temp-hidden[name="' + this.name + '"]').forEach(function(el) {
                                            el.remove();
                                        });
                                    }

                                    // Submit the form
                                    document.getElementById('filterForm').submit();
                                });
                            });
                        });
                    </script>
                </aside>

                <?php if ($foundProperties > 0): ?>
                    <!-- Right: Hotel Listings -->
                    <div class="w-full space-y-2">
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

                        <!-- Room Listings -->
                        <?php foreach ($available_rooms as $roomtype):
                            // Check if room is favorited
                            $check_favorite = "SELECT COUNT(*) as count FROM roomtypefavoritetb WHERE UserID = '$userID' AND RoomTypeID = '" . $roomtype['RoomTypeID'] . "'";
                            $favorite_result = $connect->query($check_favorite);
                            $is_favorited = $favorite_result->fetch_assoc()['count'] > 0;
                        ?>
                            <div class="bg-white overflow-hidden hotel-listings-container">
                                <a href="../User/RoomDetails.php?roomTypeID=<?php echo htmlspecialchars($roomtype['RoomTypeID']) ?>&reservation_id=<?= $reservation_id ?>&room_id=<?= $room_id ?>&checkin_date=<?= $checkin_date ?>&checkout_date=<?= $checkout_date ?>&adults=<?= $adults ?>&children=<?= $children ?>&edit=<?= $edit ?>" class="flex flex-col md:flex-row rounded-md shadow-sm border">
                                    <div class="md:w-[28%] h-64 overflow-hidden select-none rounded-l-md relative">
                                        <img src="../Admin/<?= htmlspecialchars($roomtype['RoomCoverImage']) ?>" alt="<?= htmlspecialchars($roomtype['RoomType']) ?>" class="w-full h-full object-cover">
                                        <form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post">
                                            <input type="hidden" name="checkin_date" value="<?= $checkin_date ?>">
                                            <input type="hidden" name="checkout_date" value="<?= $checkout_date ?>">
                                            <input type="hidden" name="adults" value="<?= $adults ?>">
                                            <input type="hidden" name="children" value="<?= $children ?>">
                                            <input type="hidden" name="roomTypeID" value="<?= $roomtype['RoomTypeID'] ?>">
                                            <button type="submit" name="room_favourite">
                                                <i class="absolute top-3 right-3 ri-heart-fill text-xl cursor-pointer flex items-center justify-center bg-white w-9 h-9 rounded-full hover:bg-slate-100 transition-colors duration-300 <?= $is_favorited ? 'text-red-500 hover:text-red-600' : 'text-slate-400 hover:text-red-300' ?>"></i>
                                            </button>
                                        </form>
                                    </div>
                                    <div class="md:w-2/3 p-4">
                                        <div class="flex justify-between items-start">
                                            <div class="flex items-center gap-4">
                                                <h2 class="text-xl font-bold text-gray-800"><?= htmlspecialchars($roomtype['RoomType']) ?></h2>
                                                <?php
                                                // Get average rating
                                                $review_select = "SELECT Rating FROM roomtypereviewtb WHERE RoomTypeID = '$roomtype[RoomTypeID]'";
                                                $select_query = $connect->query($review_select);

                                                // Check if there are any reviews
                                                $totalReviews = $select_query->num_rows;
                                                if ($totalReviews > 0) {
                                                    $totalRating = 0;

                                                    // Sum all ratings
                                                    while ($review = $select_query->fetch_assoc()) {
                                                        $totalRating += $review['Rating'];
                                                    }

                                                    // Calculate the average rating
                                                    $averageRating = $totalRating / $totalReviews;
                                                } else {
                                                    $averageRating = 0;
                                                }
                                                ?>
                                                <div class="flex items-center gap-3">
                                                    <div class="select-none space-x-1 cursor-pointer">
                                                        <?php
                                                        $fullStars = floor($averageRating);
                                                        $halfStar = ($averageRating - $fullStars) >= 0.5 ? 1 : 0;
                                                        $emptyStars = 5 - ($fullStars + $halfStar);

                                                        // Display full stars
                                                        for ($i = 0; $i < $fullStars; $i++) {
                                                            echo '<i class="ri-star-fill text-amber-500"></i>';
                                                        }

                                                        // Display half star if needed
                                                        if ($halfStar) {
                                                            echo '<i class="ri-star-half-line text-amber-500"></i>';
                                                        }

                                                        // Display empty stars
                                                        for ($i = 0; $i < $emptyStars; $i++) {
                                                            echo '<i class="ri-star-line text-amber-500"></i>';
                                                        }
                                                        ?>
                                                    </div>
                                                    <p class="text-gray-500 text-sm">
                                                        (<?php echo $totalReviews; ?> review<?php echo ($totalReviews > 1) ? 's' : ''; ?>)
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-sm text-gray-500">Price starts from</div>
                                                <div class="text-lg font-bold text-orange-500">USD<?= number_format($roomtype['RoomPrice'], 2) ?></div>
                                            </div>
                                        </div>
                                        <div class="text-sm text-gray-600 mt-1"><?= htmlspecialchars($roomtype['RoomType']) ?> <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">Max <?= $roomtype['RoomCapacity'] ?> <?php if ($roomtype['RoomCapacity'] > 1) echo 'guests';
                                                                                                                                                                                                                                        else echo 'guest'; ?></span> <span class="text-gray-400">•</span> Show on map</div>
                                        <p class="text-sm text-gray-700 mt-3">
                                            <?php
                                            $description = $roomtype['RoomDescription'] ?? '';
                                            $truncated = mb_strimwidth(htmlspecialchars($description), 0, 250, '...');
                                            echo $truncated;
                                            ?>
                                        </p>
                                        <div class="flex flex-wrap gap-1 mt-4 select-none">
                                            <?php
                                            $facilitiesQuery = "SELECT f.Facility
                                    FROM roomtypefacilitytb rf
                                    JOIN facilitytb f ON rf.FacilityID = f.FacilityID
                                    WHERE rf.RoomTypeID = '" . $roomtype['RoomTypeID'] . "'";
                                            $facilitiesResult = $connect->query($facilitiesQuery);

                                            if ($facilitiesResult->num_rows > 0) {
                                                while ($facility = $facilitiesResult->fetch_assoc()) {
                                                    echo '<span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded">' .
                                                        htmlspecialchars($facility['Facility']) .
                                                        '</span>';
                                                }
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="max-w-[1200px] mx-auto px-4 py-16 text-center">
                        <div class="bg-white p-8 rounded-lg shadow-sm border max-w-2xl mx-auto">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h2 class="text-xl font-bold text-gray-800 mt-4">No properties found</h2>
                            <p class="text-gray-600 mt-2">We couldn't find any properties matching your search criteria.</p>
                            <div class="mt-6">
                                <a href="../index.php" class="inline-block bg-orange-500 text-white px-6 py-2 rounded-md hover:bg-orange-600 transition-colors select-none">
                                    Modify search
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>

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
    include('../includes/MoveUpBtn.php');
    include('../includes/Alert.php');
    include('../includes/Footer.php');
    ?>

    <script src="//unpkg.com/alpinejs" defer></script>
    <script type="module" src="../JS/index.js"></script>
</body>

</html>