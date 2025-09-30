<?php
session_start();
require_once('../config/db_connection.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

$userID = (!empty($_SESSION["UserID"]) ? $_SESSION["UserID"] : null);
$reservations = [];
$reserved_rooms = [];
$totalPrice = 0;
$totalNights = 0;

// Get reservation details
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = mysqli_real_escape_string($connect, $_GET['id']);
    $action = $_GET['action'];

    // Build query
    $query = match ($action) {
        'getReservationDetails' => "
            SELECT rd.*, r.*, rb.RoomName, rtb.RoomTypeID, rtb.RoomType, rtb.RoomCoverImage, rtb.RoomPrice, rtb.RoomDescription, u.UserName, u.UserPhone
            FROM reservationdetailtb rd
            JOIN reservationtb r ON rd.ReservationID = r.ReservationID
            JOIN roomtb rb ON rd.RoomID = rb.RoomID
            JOIN roomtypetb rtb ON rb.RoomTypeID = rtb.RoomTypeID
            JOIN usertb u ON r.UserID = u.UserID
            WHERE rd.ReservationID = '$id'
        ",
        default => null
    };

    if ($query) {
        $result = $connect->query($query);
        $reservations = [];

        while ($row = $result->fetch_assoc()) {
            $reservations[] = $row; // ✅ push all rooms into array
        }

        if (!empty($reservations)) {
            $response = [
                'success' => true,
                'reservations' => $reservations
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'Reservation not found'
            ];
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
                          WHERE UserID = ? AND Status = 'Confirmed'
                          Order By r.ReservationDate DESC");
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
                header("Location: reservation.php");
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
            // Calculate nights for each room
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
                $tax = 0;

                foreach ($reserved_rooms as $room) {
                    // Calculate nights for THIS SPECIFIC ROOM
                    $roomCheckin = new DateTime($room['CheckInDate']);
                    $roomCheckout = new DateTime($room['CheckOutDate']);
                    $roomNights = $roomCheckout->diff($roomCheckin)->days;

                    // Add this room's total price (price * its specific nights)
                    $tax = $room['Price'] * $roomNights * 0.1;
                    $totalPrice += $room['Price'] * $roomNights + $tax;
                }

                // Set total nights (same for all rooms in reservation)
                $totalNights = $nights;
            } catch (Exception $e) {
                // Handle invalid dates
                $alertMessage = "Invalid dates: " . $e->getMessage();
                header("Location: reservation.php");
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

<body class="relative min-w-[380px]">
    <?php
    include('../includes/navbar.php');
    include('../includes/cookies.php');
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

    $tommorrow = date('Y-m-d', strtotime('+1 day'));
    $after_tommorrow = date('Y-m-d', strtotime('+2 day'));
    ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Upcoming Stays</h1>
                <p class="text-gray-500 mt-1">Manage your upcoming reservations and bookings</p>
            </div>
            <div class="relative flex gap-3">
                <a href="../User/room_booking.php?checkin_date=<?= $tommorrow ?>&checkout_date=<?= $after_tommorrow ?>" class="flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white font-medium py-2 px-4 rounded-sm transition-all select-none">
                    <i class="ri-add-line"></i>
                    New Booking
                </a>
            </div>
        </div>

        <!-- Stats Summary -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
            <div class="bg-white p-5 rounded-xl shadow-xs border border-gray-100 hover:border-gray-200 transition-all">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Upcoming Stays</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo count($reservations ?? []); ?></p>
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
                        <p class="text-2xl font-bold text-gray-900 mt-1">$<?= number_format($totalPrice ?? 0 * 1.1, 2) ?></p>
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
                        <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo array_sum(array_column($reserved_rooms ?? [], 'PointsEarned')); ?></p>
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
                    <span class="font-medium text-gray-700"><?php echo count($reservations ?? []); ?></span> upcoming reservations
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

        <?php
        if ($userID !== null) {
        ?>
            <div class="space-y-3">
                <?php foreach ($reservations as $data): ?>
                    <?php
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
                    $rooms = [];

                    // Store all rooms
                    while ($roomData = $result->fetch_assoc()) {
                        $rooms[] = $roomData;
                    }

                    // Use first room for main details
                    $data = $rooms[0];

                    // Find earliest check-in and latest check-out across all rooms
                    $earliestCheckin = null;
                    $latestCheckout = null;
                    foreach ($rooms as $room) {
                        $currentCheckin = new DateTime($room['CheckInDate']);
                        $currentCheckout = new DateTime($room['CheckOutDate']);
                        if ($earliestCheckin === null || $currentCheckin < $earliestCheckin) {
                            $earliestCheckin = $currentCheckin;
                        }
                        if ($latestCheckout === null || $currentCheckout > $latestCheckout) {
                            $latestCheckout = $currentCheckout;
                        }
                    }

                    // Total nights based on overall reservation period
                    $totalNights = $latestCheckout->diff($earliestCheckin)->days;

                    // Total price based on total nights for each room individually
                    $totalPrice = 0;
                    foreach ($rooms as $room) {
                        $roomCheckin = new DateTime($room['CheckInDate']);
                        $roomCheckout = new DateTime($room['CheckOutDate']);
                        $roomNights = $roomCheckout->diff($roomCheckin)->days;
                        $totalPrice += $room['Price'] * $roomNights;
                    }

                    // Determine reservation status
                    $today = new DateTime();
                    $today->setTime(0, 0, 0);
                    $earliestCheckin->setTime(0, 0, 0);
                    $latestCheckout->setTime(0, 0, 0);

                    if ($today < $earliestCheckin) {
                        $reservationStatus = "Upcoming";
                        $statusColor = "bg-blue-50 border-blue-400 text-blue-800";
                    } elseif ($today >= $earliestCheckin && $today <= $latestCheckout) {
                        $reservationStatus = "Ongoing";
                        $statusColor = "bg-green-50 border-green-400 text-green-800";
                    } else {
                        $reservationStatus = "Completed";
                        $statusColor = "bg-gray-50 border-gray-400 text-gray-800";
                    }
                    ?>

                    <!-- Stay Card -->
                    <div class="bg-white rounded-sm border-2 border-gray-100">
                        <div class="md:flex">
                            <!-- Hotel Image -->
                            <div class="md:w-1/3 relative select-none">
                                <img src="../Admin/<?php echo $data['RoomCoverImage']; ?>"
                                    alt="Hotel Room"
                                    class="w-full h-40 md:h-full object-cover">
                                <div class="absolute top-3 right-3">
                                    <span class="<?php echo $statusColor; ?> text-[11px] font-medium px-2.5 py-0.5 rounded-full flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full 
                                    <?php echo $reservationStatus === 'Upcoming' ? 'bg-blue-500' : ($reservationStatus === 'Ongoing' ? 'bg-green-500' : 'bg-gray-500'); ?>">
                                        </span>
                                        <?php echo $reservationStatus; ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Stay Details -->
                            <div class="md:w-2/3 p-4">
                                <div class="flex flex-col sm:flex-row justify-between items-start gap-3">
                                    <div class="flex-1">
                                        <h2 class="text-lg font-semibold text-gray-900">
                                            Reservation #<?php echo $data['ReservationID']; ?>
                                        </h2>
                                        <div class="flex flex-wrap items-center mt-1.5 text-gray-600 gap-x-3 gap-y-1 text-sm">
                                            <span class="flex items-center">
                                                <i class="ri-user-line mr-1 text-gray-400"></i>
                                                <?php echo $data['Title'] . ' ' . $data['FirstName'] . ' ' . $data['LastName']; ?>
                                            </span>
                                            <span class="flex items-center">
                                                <i class="ri-phone-line mr-1 text-gray-400"></i>
                                                <?php echo $data['UserPhone']; ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <div class="text-xs text-gray-500">Total stay</div>
                                        <div class="text-sm font-medium text-gray-700">
                                            <?php echo $totalNights; ?> night<?php echo $totalNights > 1 ? 's' : ''; ?>
                                        </div>
                                        <div class="text-lg font-bold text-orange-600 mt-0.5">
                                            $<?= number_format($totalPrice * 1.1, 2) ?>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-0.5">
                                            <?php echo $data['PointsRedeemed'] > 0 ?
                                                'Used ' . $data['PointsRedeemed'] . ' points' :
                                                'Includes taxes & fees'; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Stay Dates and Room Info -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-4 text-sm">
                                    <div class="bg-gray-50 p-2.5 rounded-md">
                                        <h3 class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">Check-in</h3>
                                        <p class="font-medium text-gray-900 mt-0.5"><?php echo $earliestCheckin->format('D, j M Y'); ?></p>
                                        <p class="text-xs text-gray-500">After 2:00 PM</p>
                                    </div>
                                    <div class="bg-gray-50 p-2.5 rounded-md">
                                        <h3 class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">Check-out</h3>
                                        <p class="font-medium text-gray-900 mt-0.5"><?php echo $latestCheckout->format('D, j M Y'); ?></p>
                                        <p class="text-xs text-gray-500">Before 12:00 PM</p>
                                    </div>
                                    <div class="bg-gray-50 p-2.5 rounded-md">
                                        <h3 class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">Room Details</h3>
                                        <div class="flex flex-wrap items-center gap-1.5 mt-0.5">
                                            <?php foreach ($rooms as $room): ?>
                                                <div class="relative group">
                                                    <div class="bg-blue-50 hover:bg-blue-100 px-2.5 py-0.5 rounded-md border border-blue-100 text-xs font-medium text-blue-700 cursor-default">
                                                        <?php echo htmlspecialchars($room['RoomName']); ?>
                                                    </div>
                                                    <!-- Tooltip -->
                                                    <div class="absolute z-10 bottom-full left-1/2 transform -translate-x-1/2 mb-1 hidden group-hover:block animate-fadeIn">
                                                        <div class="bg-gray-800 text-white text-xs px-2.5 py-1 rounded-md shadow-md whitespace-nowrap">
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
                                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                                    <div class="bg-gray-50 p-2.5 rounded-md">
                                        <h3 class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">Reservation Date</h3>
                                        <p class="text-gray-700 mt-0.5"><?php echo date('M j, Y \a\t H:i', strtotime($data['ReservationDate'])); ?></p>
                                    </div>
                                    <div class="bg-gray-50 p-2.5 rounded-md">
                                        <h3 class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">Reward Points</h3>
                                        <p class="text-gray-700 mt-0.5">
                                            <span class="font-medium text-green-600">+<?php echo $data['PointsEarned']; ?> earned</span>
                                            <?php if ($data['PointsRedeemed'] > 0): ?>
                                                <span class="mx-1 text-gray-300">|</span>
                                                <span class="font-medium text-orange-600">-<?php echo $data['PointsRedeemed']; ?> redeemed</span>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="mt-5 flex flex-col sm:flex-row gap-2.5 select-none">
                                    <button data-reservation-id="<?= htmlspecialchars($data['ReservationID']) ?>"
                                        class="details-btn flex-1 flex items-center justify-center gap-1.5 bg-amber-500 hover:bg-amber-600 text-white font-medium px-4 py-2 rounded-sm transition-all shadow-sm">
                                        <i class="ri-file-list-line"></i> View Details
                                    </button>
                                    <button class="flex-1 flex items-center justify-center gap-1.5 bg-white border border-gray-200 hover:border-gray-300 text-gray-700 font-medium px-4 py-2 rounded-sm transition-all shadow-sm">
                                        <i class="ri-pencil-line"></i> Modify
                                    </button>
                                    <button class="openCancelModalBtn flex-1 flex items-center justify-center gap-1.5 bg-white border border-red-200 hover:border-red-300 text-red-600 font-medium px-4 py-2 rounded-sm transition-all shadow-sm"
                                        data-reservation-id="<?= htmlspecialchars($data['ReservationID']) ?>"
                                        data-dates="<?= $earliestCheckin->format('Y-m-d') ?> - <?= $latestCheckout->format('Y-m-d') ?>"
                                        data-total-price="<?= $totalPrice ?>">
                                        <i class="ri-close-line"></i> Cancel
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php
        } else {
        ?>
            <p class="text-center text-gray-400 my-36">You have no upcoming stays yet.</p>
        <?php } ?>
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

    <!-- Reservation Detail Modal -->
    <div id="reservationDetailModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible -translate-y-5 p-2 transition-all duration-300">
        <div class="bg-white rounded-xl max-w-4xl w-full p-6 animate-fade-in max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-800">Reservation Details</h3>
                <button id="closeReservationDetailModal" class="text-gray-400 hover:text-gray-500">
                    <i class="ri-close-line text-xl"></i>
                </button>
            </div>

            <div class="space-y-3">
                <!-- Reservation Details -->
            </div>
        </div>
    </div>

    <!-- Cancellation Modal -->
    <div id="cancelModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible -translate-y-5 p-4 transition-all duration-300">
        <div class="bg-white rounded-xl max-w-lg w-full p-6 animate-fade-in">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-800">Cancel Reservation</h3>
                <button id="closeCancelModal" class="text-gray-400 hover:text-gray-500">
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
                        <p class="text-sm text-yellow-700" id="cancelDeadlineText">
                            <!-- Deadline text will be set here -->
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

            <div class="flex justify-end mt-6">
                <button id="confirmCancelBtn" class="inline-flex items-center justify-center gap-2 bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-sm transition-colors select-none">
                    <i class="ri-check-line"></i> Confirm cancellation
                </button>
            </div>
        </div>
    </div>

    <!-- MoveUp Btn -->
    <?php
    include('../includes/moveup_btn.php');
    include('../includes/footer.php');
    ?>

    <script src="//unpkg.com/alpinejs" defer></script>
    <script type="module" src="../JS/index.js"></script>
</body>

</html>