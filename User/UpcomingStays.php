<?php
session_start();
include('../config/dbConnection.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

$userID = (!empty($_SESSION["UserID"]) ? $_SESSION["UserID"] : null);

// Get reservation details
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = mysqli_real_escape_string($connect, $_GET['id']);
    $action = $_GET['action'];

    // Build query based on action
    $query = match ($action) {
        'getReservationDetails' => "SELECT * FROM reservationdetailtb WHERE ReservationID = '$id'",
        default => null
    };
    if ($query) {
        $reservation = $connect->query($query)->fetch_assoc();

        if ($reservation) {
            $response['success'] = true;
            $response['reservation'] = $reservation;
        } else {
            $response['success'] = true;
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Fetch reservations for the user
if ($userID) {
    $stmt = $connect->prepare("SELECT r.*
                          FROM reservationtb r
                          WHERE UserID = ? AND Status = 'Confirmed'");
    $stmt->bind_param("s", $userID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($reservation = $result->fetch_assoc()) {
            // Calculate nights for each reservation 
            try {
                $reservations[] = $reservation;
            } catch (Exception $e) {
                // Handle invalid dates
                $alertMessage = "Invalid dates: " . $e->getMessage();
                header("Location: Reservation.php");
                exit();
            }
        }
    }

    $stmt = $connect->prepare("SELECT rs.*, rd.*, r.*, rt.*, u.*
                          FROM reservationtb rs
                          JOIN reservationdetailtb rd ON rs.ReservationID = rd.ReservationID
                          JOIN roomtb r ON rd.RoomID = r.RoomID 
                          JOIN roomtypetb rt ON rt.RoomTypeID = r.RoomTypeID
                          JOIN usertb u ON rs.UserID = u.UserID
                          WHERE rs.UserID = ? AND rs.Status = 'Confirmed'");
    $stmt->bind_param("s", $userID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($room = $result->fetch_assoc()) {
            // Calculate nights for each room (they should have same dates)
            try {
                $checkin_date = $room['CheckInDate'];
                $checkout_date = $room['CheckOutDate'];
                $adults = $room['Adult'];
                $children = $room['Children'];
                $nights = (strtotime($checkout_date) - strtotime($checkin_date)) / (60 * 60 * 24);

                // Store room data with additional calculated fields
                $room['nights'] = $nights;
                $room['total'] = $room['Price'] * $nights * 1.1;
                $room['subtotal'] = $room['Price'] * $nights;
                $reserved_rooms[] = $room;

                $totalPrice = 0;

                foreach ($reserved_rooms as $room) {
                    // Calculate nights for THIS SPECIFIC ROOM
                    $roomCheckin = new DateTime($room['CheckInDate']);
                    $roomCheckout = new DateTime($room['CheckOutDate']);
                    $roomNights = $roomCheckout->diff($roomCheckin)->days;

                    // Add this room's total price (price * its specific nights)
                    $totalPrice += $room['Price'] * $roomNights;
                }

                // Set total nights (same for all rooms in reservation)
                $totalNights = $nights;
            } catch (Exception $e) {
                // Handle invalid dates
                $alertMessage = "Invalid dates: " . $e->getMessage();
                header("Location: Reservation.php");
                exit();
            }
        }
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
    <!-- Swiper JS -->
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
</head>

<body class="relative">
    <?php
    include('../includes/Navbar.php');
    include('../includes/Cookies.php');
    ?>

    <?php
    if ($userID) {
        $user = "SELECT * FROM usertb WHERE UserID = '$userID'";
        $result = $connect->query($user);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $Membership = $row['Membership'] ?? null;
        } else {
            $Membership = null; // No user found
        }
    } else {
        $Membership = null; // No user signed in
    }
    ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Upcoming Stays</h1>
                <p class="text-gray-500 mt-1">Manage your upcoming reservations and bookings</p>
            </div>
            <div class="relative flex gap-3">
                <button class="flex items-center gap-2 bg-white border border-gray-200 hover:border-gray-300 text-gray-700 font-medium py-2 px-4 rounded-lg transition-all shadow-xs hover:shadow-sm">
                    <i class="ri-filter-line"></i>
                    Filter
                </button>
                <button class="flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white font-medium py-2 px-4 rounded-lg transition-all shadow-xs hover:shadow-sm">
                    <i class="ri-add-line"></i>
                    New Booking
                </button>
            </div>
        </div>

        <!-- Stats Summary -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
            <div class="bg-white p-5 rounded-xl shadow-xs border border-gray-100 hover:border-gray-200 transition-all">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Upcoming Stays</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo count($reservations); ?></p>
                        <p class="text-xs text-gray-400 mt-1">Active reservations</p>
                    </div>
                    <div class="p-3 bg-blue-50/80 rounded-xl text-blue-500">
                        <i class="ri-calendar-check-line text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white p-5 rounded-xl shadow-xs border border-gray-100 hover:border-gray-200 transition-all">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Spend</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">$<?= number_format($totalPrice * 1.1, 2) ?></p>
                        <p class="text-xs text-gray-400 mt-1">Estimated total</p>
                    </div>
                    <div class="p-3 bg-green-50/80 rounded-xl text-green-500">
                        <i class="ri-wallet-line text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white p-5 rounded-xl shadow-xs border border-gray-100 hover:border-gray-200 transition-all">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Reward Points</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo array_sum(array_column($reserved_rooms, 'PointsEarned')); ?></p>
                        <p class="text-xs text-gray-400 mt-1">Available to redeem</p>
                    </div>
                    <div class="p-3 bg-purple-50/80 rounded-xl text-purple-500">
                        <i class="ri-star-line text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sort and Filter Bar -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6 p-4 bg-gray-50 rounded-lg">
            <div class="flex items-center gap-2">
                <p class="text-sm text-gray-600">
                    <span class="font-medium text-gray-700"><?php echo count($reservations); ?></span> upcoming reservations
                </p>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-600">Sort by:</span>
                    <select class="bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 w-64">
                        <option>Check-in date (earliest first)</option>
                        <option>Check-in date (latest first)</option>
                        <option>Price (low to high)</option>
                        <option>Price (high to low)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Upcoming Stay Cards -->
        <div class="space-y-5">
            <?php foreach ($reservations as $data): ?>
                <?php
                // Fetch data
                $reservationID = $data['ReservationID'];

                // Query to get all rooms for this reservation
                $reservation = "SELECT rd.*, r.*, rb.*, rtb.*, u.UserName, u.UserPhone 
               FROM reservationdetailtb rd
               JOIN reservationtb r ON rd.ReservationID = r.ReservationID
               JOIN roomtb rb ON rd.RoomID = rb.RoomID
               JOIN roomtypetb rtb ON rb.RoomTypeID = rtb.RoomTypeID
               JOIN usertb u ON r.UserID = u.UserID 
               WHERE rd.ReservationID = '$reservationID'";

                $result = $connect->query($reservation);
                $rooms = []; // Array to store all rooms for this reservation

                // Store all rooms
                while ($roomData = $result->fetch_assoc()) {
                    $rooms[] = $roomData;
                }

                // Use first room for check-in/check-out and other main details
                $data = $rooms[0];

                // Find the earliest check-in date from all rooms
                $earliestCheckin = null;
                foreach ($rooms as $room) {
                    $currentCheckin = new DateTime($room['CheckInDate']);
                    if ($earliestCheckin === null || $currentCheckin < $earliestCheckin) {
                        $earliestCheckin = $currentCheckin;
                    }
                }

                // Calculate nights
                $checkout = new DateTime($data['CheckOutDate']);
                $nights = $checkout->diff($earliestCheckin)->days;

                // Initialize total price
                $totalNights = 0;
                $totalPrice = 0;

                foreach ($rooms as $room) {
                    // Calculate nights for THIS SPECIFIC ROOM
                    $roomCheckin = new DateTime($room['CheckInDate']);
                    $roomCheckout = new DateTime($room['CheckOutDate']);
                    $roomNights = $roomCheckout->diff($roomCheckin)->days;

                    // Add to total nights
                    $totalNights += $roomNights;

                    // Add this room's total price (price * its specific nights)
                    $totalPrice += $room['Price'] * $roomNights;
                }

                ?>

                <!-- Stay Card -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100 hover:shadow-md transition-all duration-300">
                    <div class="md:flex">
                        <!-- Hotel Image -->
                        <div class="md:w-1/3 relative select-none">
                            <img src="../Admin/<?php echo $data['RoomCoverImage']; ?>"
                                alt="Hotel Room"
                                class="w-full h-52 md:h-full object-cover">
                            <div class="absolute top-4 right-4 flex gap-2">
                                <span class="bg-green-100 text-green-800 text-xs font-semibold px-3 py-1 rounded-full flex items-center gap-1">
                                    <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                                    <?php echo $data['Status']; ?>
                                </span>
                            </div>
                        </div>

                        <!-- Stay Details -->
                        <div class="md:w-2/3 p-6">
                            <div class="flex flex-col sm:flex-row justify-between items-start gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <h2 class="text-xl font-bold text-gray-900">
                                            Reservation #<?php echo $data['ReservationID']; ?>
                                        </h2>
                                    </div>
                                    <div class="flex flex-wrap items-center mt-2 text-gray-600 gap-x-3 gap-y-1">
                                        <span class="flex items-center text-sm">
                                            <i class="ri-user-line mr-1.5 text-gray-400"></i>
                                            <?php echo $data['Title'] . ' ' . $data['FirstName'] . ' ' . $data['LastName']; ?>
                                        </span>
                                        <span class="flex items-center text-sm">
                                            <i class="ri-phone-line mr-1.5 text-gray-400"></i>
                                            <?php echo $data['UserPhone']; ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm text-gray-500">Total stay</div>
                                    <div class="text-sm font-medium text-gray-700">
                                        <?php echo $totalNights; ?> night<?php echo $totalNights > 1 ? 's' : ''; ?>
                                    </div>
                                    <div class="text-xl font-bold text-orange-600 mt-1">
                                        $<?= number_format($totalPrice * 1.1, 2) ?>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        <?php echo $data['PointsRedeemed'] > 0 ?
                                            'Used ' . $data['PointsRedeemed'] . ' points' :
                                            'Includes taxes & fees'; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Stay Dates and Room Info -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mt-6">
                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <h3 class="text-xs font-medium text-gray-500 uppercase tracking-wider">Check-in</h3>
                                    <p class="font-medium text-gray-900 mt-1"><?php echo $earliestCheckin->format('D, j M Y'); ?></p>
                                    <p class="text-sm text-gray-500 mt-0.5">After 2:00 PM</p>
                                </div>
                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <h3 class="text-xs font-medium text-gray-500 uppercase tracking-wider">Check-out</h3>
                                    <p class="font-medium text-gray-900 mt-1"><?php echo $checkout->format('D, j M Y'); ?></p>
                                    <p class="text-sm text-gray-500 mt-0.5">Before 12:00 AM</p>
                                </div>
                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <h3 class="text-xs font-medium text-gray-500 uppercase tracking-wider">Room Details</h3>
                                    <div class="flex flex-wrap items-center gap-2 mt-1">
                                        <?php foreach ($rooms as $room): ?>
                                            <div class="relative group">
                                                <div class="bg-blue-50 hover:bg-blue-100 px-3 py-1 rounded-lg border border-blue-100 transition-colors duration-200 cursor-default">
                                                    <p class="font-medium text-blue-700 text-sm"><?php echo htmlspecialchars($room['RoomName']); ?></p>
                                                </div>
                                                <!-- Enhanced Tooltip -->
                                                <div class="absolute z-10 bottom-full left-1/2 transform -translate-x-1/2 mb-2 hidden group-hover:block animate-fadeIn">
                                                    <div class="bg-gray-800 text-white text-xs px-3 py-1.5 rounded-md shadow-lg whitespace-nowrap">
                                                        <?php echo htmlspecialchars($room['RoomType']); ?>
                                                        <div class="absolute left-1/2 -bottom-1 transform -translate-x-1/2 w-2 h-2 bg-gray-800 rotate-45"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Points and Reservation Info -->
                            <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <h3 class="text-xs font-medium text-gray-500 uppercase tracking-wider">Reservation Date</h3>
                                    <p class="text-sm text-gray-700 mt-1"><?php echo date('M j, Y \a\t H:i', strtotime($data['ReservationDate'])); ?></p>
                                </div>
                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <h3 class="text-xs font-medium text-gray-500 uppercase tracking-wider">Reward Points</h3>
                                    <p class="text-sm text-gray-700 mt-1">
                                        <span class="font-medium text-green-600">+<?php echo $data['PointsEarned']; ?> earned</span>
                                        <?php if ($data['PointsRedeemed'] > 0): ?>
                                            <span class="mx-2 text-gray-300">|</span>
                                            <span class="font-medium text-orange-600">-<?php echo $data['PointsRedeemed']; ?> redeemed</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="mt-6 flex flex-col sm:flex-row gap-3 select-none">
                                <button data-reservation-id="<?= htmlspecialchars($data['ReservationID']) ?>" class="details-btn flex-1 flex items-center justify-center gap-2 bg-orange-500 hover:bg-orange-600 text-white font-medium py-2.5 px-4 rounded-lg transition-all shadow-xs hover:shadow-sm">
                                    <i class="ri-file-list-line"></i> View Details
                                </button>
                                <button class="flex-1 flex items-center justify-center gap-2 bg-white border border-gray-200 hover:border-gray-300 text-gray-700 font-medium py-2.5 px-4 rounded-lg transition-all shadow-xs hover:shadow-sm">
                                    <i class="ri-pencil-line"></i> Modify
                                </button>
                                <button onclick="showCancelModal('<?php echo $data['ReservationID']; ?>', '<?php echo $earliestCheckin->format('M j, Y'); ?> - <?php echo $checkout->format('M j, Y'); ?>', <?php echo $totalPrice; ?>)"
                                    class="flex-1 flex items-center justify-center gap-2 bg-white border border-red-200 hover:border-red-300 text-red-600 font-medium py-2.5 px-4 rounded-lg transition-all shadow-xs hover:shadow-sm">
                                    <i class="ri-close-line"></i> Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <div class="mt-8 flex flex-col sm:flex-row items-center justify-between gap-4 pt-5 border-t border-gray-200">
            <div class="text-sm text-gray-600">
                Showing <span class="font-medium text-gray-900">1</span> to <span class="font-medium text-gray-900"><?php echo count($reserved_rooms); ?></span> of <span class="font-medium text-gray-900"><?php echo count($reserved_rooms); ?></span> results
            </div>
            <div class="flex items-center gap-2">
                <button class="flex items-center justify-center w-9 h-9 rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-all" disabled>
                    <i class="ri-arrow-left-s-line"></i>
                </button>
                <button class="flex items-center justify-center w-9 h-9 rounded-lg border border-orange-500 bg-orange-50 text-orange-600 font-medium transition-all">
                    1
                </button>
                <button class="flex items-center justify-center w-9 h-9 rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-all" disabled>
                    <i class="ri-arrow-right-s-line"></i>
                </button>
            </div>
        </div>
    </div>

    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translate(-50%, 5px);
            }

            to {
                opacity: 1;
                transform: translate(-50%, 0);
            }
        }

        .animate-fadeIn {
            animation: fadeIn 0.15s ease-out forwards;
        }

        .shadow-xs {
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
    </style>

    <div id="reservationDetailModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible -translate-y-5 p-2 transition-all duration-300">
        <div class="bg-white rounded-xl max-w-4xl w-full p-6 animate-fade-in">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-800">Reservation Details</h3>
                <button id="closeReservationDetailModal" class="text-gray-400 hover:text-gray-500">
                    <i class="ri-close-line text-xl"></i>
                </button>
            </div>

            <div class="space-y-3">
                <!-- <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-medium text-gray-800 mb-3">Your Information</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Name:</span>
                            <span class="text-sm font-medium"><?= $data['Title'] ?> <?= $data['FirstName'] ?> <?= $data['LastName'] ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Phone:</span>
                            <span class="text-sm font-medium"><?= $data['UserPhone'] ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Reservation Date:</span>
                            <span class="text-sm font-medium"><?= date('M j, Y H:i', strtotime($data['ReservationDate'])) ?></span>
                        </div>
                    </div>
                </div> -->

                <!-- Room Information -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-medium text-gray-800 mb-3">Room Information</h4>

                    <!-- Swiper Container -->
                    <div class="swiper roomTypeSwiper">
                        <div class="swiper-wrapper">
                            <?php
                            // Group rooms by RoomType
                            $grouped_rooms = [];
                            foreach ($reserved_rooms as $room) {
                                $grouped_rooms[$room['RoomTypeID']][] = $room;
                            }

                            foreach ($grouped_rooms as $room_type_id => $rooms):
                                $first_room = $rooms[0];
                            ?>
                                <!-- Swiper Slide -->
                                <div class="swiper-slide">
                                    <div class="flex flex-col md:flex-row gap-4 py-2">
                                        <div class="md:w-1/3 select-none">
                                            <div class="relative" style="height: 200px;">
                                                <img src="../Admin/<?= $first_room['RoomCoverImage'] ?>"
                                                    alt="Room Image"
                                                    class="w-full h-full object-cover rounded-lg transition-transform duration-300 group-hover:scale-105">
                                            </div>
                                        </div>

                                        <div class="md:w-2/3">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <h5 class="font-bold text-lg text-gray-800">
                                                        <?= $first_room['RoomType'] ?>
                                                    </h5>
                                                    <p class="text-sm text-gray-600 mt-1 line-clamp-2"><?= $first_room['RoomDescription'] ?></p>
                                                    <div class="mt-2 text-xs text-gray-500">
                                                        <?= count($rooms) ?> room<?= count($rooms) > 1 ? 's' : '' ?> of this type
                                                        <div class="flex flex-wrap gap-2 mt-1">
                                                            <?php foreach ($rooms as $index => $room): ?>
                                                                <div class="group relative">
                                                                    <span class="bg-gray-100 px-2 py-1 rounded text-gray-600 font-semibold text-xs cursor-default">
                                                                        Room #<?= $room['RoomName'] ?>
                                                                    </span>
                                                                    <!-- Hidden details that appear on hover -->
                                                                    <div class="absolute z-20 left-0 mt-1 w-64 bg-white p-3 rounded-lg shadow-sm border border-gray-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                                                                        <div class="flex items-center gap-2 text-sm mb-1">
                                                                            <i class="ri-calendar-check-line text-orange-500"></i>
                                                                            <?= date('M j', strtotime($room['CheckInDate'])) ?>
                                                                            <span class="text-gray-400">→</span>
                                                                            <?= date('M j, Y', strtotime($room['CheckOutDate'])) ?>
                                                                        </div>
                                                                        <div class="flex items-center gap-2 text-sm mb-2">
                                                                            <i class="ri-user-line text-orange-500"></i>
                                                                            <?= $room['Adult'] ?> Adult<?= $room['Adult'] > 1 ? 's' : '' ?>
                                                                            <?php if ($room['Children'] > 0): ?>
                                                                                <span class="text-gray-400">+</span>
                                                                                <?= $room['Children'] ?> Child<?= $room['Children'] > 1 ? 'ren' : '' ?>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                        <div class="text-sm text-gray-600">
                                                                            <span class="font-medium">$<?= number_format($room['RoomPrice'], 2) ?></span>
                                                                            <span class="text-gray-500">/night</span>
                                                                        </div>

                                                                        <!-- Cancellation Alert - Inside Tooltip -->
                                                                        <div class="bg-red-50 border-l-4 border-red-400 p-2 my-2 rounded-r">
                                                                            <div class="flex items-start">
                                                                                <i class="ri-alert-line text-red-500 mt-0.5 mr-1 text-sm"></i>
                                                                                <span class="text-xs text-red-700">$50 fee if cancelled within 48 hours</span>
                                                                            </div>
                                                                        </div>

                                                                        <!-- Cancellation Button -->
                                                                        <button class="cancel-btn mt-1 w-full bg-red-50 hover:bg-red-100 text-red-600 text-xs font-medium py-2 px-3 rounded-md transition-colors duration-200 flex items-center justify-center select-none"
                                                                            data-reservation-id="<?= $room['ReservationID'] ?>">
                                                                            <i class="ri-close-circle-line mr-1"></i> Cancel Reservation
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <a href="../User/RoomDetails.php?roomTypeID=<?= $room['RoomTypeID'] ?>&checkin_date=<?= $room['CheckInDate'] ?>&checkout_date=<?= $room['CheckOutDate'] ?>&adults=<?= $room['Adult'] ?>&children=<?= $room['Children'] ?>"
                                                class="mt-2 text-orange-600 hover:text-orange-700 font-medium inline-flex items-center text-xs bg-orange-50 px-3 py-1 rounded-full">
                                                <i class="ri-information-line mr-1"></i> Room Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <!-- Add Pagination -->
                        <div class="swiper-pagination"></div>
                    </div>
                </div>

                <!-- Initialize Swiper -->
                <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" />
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const swiper = new Swiper('.roomTypeSwiper', {
                            // Optional parameters
                            slidesPerView: 1,
                            spaceBetween: 20,
                            centeredSlides: true,

                            // If we need pagination
                            pagination: {
                                el: '.swiper-pagination',
                                clickable: true,
                            },

                            // Responsive breakpoints
                            breakpoints: {
                                // when window width is >= 768px
                                768: {
                                    slidesPerView: 1,
                                    spaceBetween: 30
                                }
                            }
                        });
                    });
                </script>

                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-medium text-gray-800 mb-3">Pricing Breakdown</h4>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Room Rate (<?= $totalNights . ' night' . ($totalNights > 1 ? 's' : '') ?>):</span>
                            <span class="text-sm font-medium">$ <?php echo number_format($totalPrice, 2); ?></span>
                        </div>

                        <?php if ($data['PointsRedeemed'] > 0) { ?>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Points Discount (<?= $data['PointsRedeemed'] ?>points):</span>
                                <span class="text-sm font-medium text-green-600">-$ <?= number_format($data['PointsDiscount'], 2) ?></span>
                            </div>
                        <?php }
                        ?>

                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Taxes & Fees:</span>
                            <span class="text-sm font-medium">$ <?= number_format($totalPrice * 0.1, 2) ?></span>
                        </div>
                        <div class="border-t border-gray-200 pt-2 flex justify-between">
                            <span class="font-medium text-gray-800">Total:</span>
                            <span class="font-bold">$ <?= number_format($totalPrice * 1.1, 2) ?></span>
                        </div>

                        <?php
                        if ($data['PointsEarned'] > 0) {
                        ?>
                            <div class="pt-2 flex justify-between">
                                <span class="text-sm text-gray-600">Points Earned:</span>
                                <span class="text-sm font-medium text-blue-600">+ <?= $data['PointsEarned'] ?> points</span>
                            </div>
                        <?php }
                        ?>
                    </div>
                </div>

                <!-- Cancellation Alert - Main Section -->
                <div class="bg-red-50 border-l-4 border-red-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="ri-alert-line text-red-500 mt-1"></i>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-medium text-red-800">Cancellation Policy</h4>
                            <p class="text-sm text-red-700 mt-1">
                                Cancellations made within 24 hours of check-in will incur a fee of $50.
                                Free cancellation is available until <?= date('F j, Y', strtotime($earliestCheckin->format('Y-m-d') . ' -1 days')) ?>.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancellation Modal -->
    <div id="cancelModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-xl max-w-md w-full p-6 animate-fade-in">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-800">Cancel Reservation</h3>
                <button onclick="document.getElementById('cancelModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-500">
                    <i class="ri-close-line text-xl"></i>
                </button>
            </div>

            <p class="text-gray-600 mb-4">Are you sure you want to cancel your reservation <span id="cancelReservationId" class="font-medium"></span> for dates <span id="cancelReservationDates" class="font-medium"></span>?</p>

            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="ri-alert-line text-yellow-500"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            Cancelling before <?= date('F j, Y', strtotime($earliestCheckin->format('Y-m-d') . ' -1 days')) ?> will result in a full refund. After this date, a cancellation fee may apply.
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                <div class="flex justify-between mb-2">
                    <span class="text-sm text-gray-600">Refund amount:</span>
                    <span class="text-sm font-medium text-gray-800" id="refundAmount"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Cancellation fee:</span>
                    <span class="text-sm font-medium text-gray-800" id="cancellationFee">$0.00</span>
                </div>
                <div class="border-t border-gray-200 mt-2 pt-2 flex justify-between">
                    <span class="text-sm font-medium text-gray-800">Total refund:</span>
                    <span class="text-sm font-bold text-green-600" id="totalRefund"></span>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 mt-6">
                <button onclick="document.getElementById('cancelModal').classList.add('hidden')" class="flex-1 flex items-center justify-center gap-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-2.5 px-4 rounded-md transition-colors">
                    <i class="ri-arrow-left-line"></i> Go back
                </button>
                <button id="confirmCancelBtn" class="flex-1 flex items-center justify-center gap-2 bg-red-600 hover:bg-red-700 text-white font-medium py-2.5 px-4 rounded-md transition-colors">
                    <i class="ri-check-line"></i> Confirm cancellation
                </button>
            </div>
        </div>
    </div>

    <script>
        // Function to show cancellation modal
        function showCancelModal(reservationId, dates, totalPrice) {
            // Calculate deadline (3 days before check-in)
            const checkinDate = new Date(dates.split(' - ')[0]);
            const deadline = new Date(checkinDate);
            deadline.setDate(deadline.getDate() - 3);

            // Set modal content
            document.getElementById('cancelReservationId').textContent = reservationId;
            document.getElementById('cancelReservationDates').textContent = dates;
            document.getElementById('refundAmount').textContent = '$' + totalPrice.toFixed(2);
            document.getElementById('totalRefund').textContent = '$' + totalPrice.toFixed(2);

            // Set up confirm button
            document.getElementById('confirmCancelBtn').onclick = function() {
                cancelReservation(reservationId);
            };

            document.getElementById('cancelModal').classList.remove('hidden');
        }
    </script>

    <!-- MoveUp Btn -->
    <?php
    include('../includes/MoveUpBtn.php');
    include('../includes/Footer.php');
    ?>

    <script src="//unpkg.com/alpinejs" defer></script>
    <script type="module" src="../JS/index.js"></script>
</body>

</html>