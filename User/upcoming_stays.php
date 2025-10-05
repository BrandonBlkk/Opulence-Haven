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
            $reservations[] = $row;
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

// Filter reservations
if (isset($_GET['ajax']) && $_GET['ajax'] === 'filter') {
    $userID = (!empty($_SESSION["UserID"]) ? $_SESSION["UserID"] : null);
    if (!$userID) {
        echo '<p class="text-center text-gray-400 my-36">User not logged in.</p>';
        exit;
    }

    $sort = $_GET['sort'] ?? 'checkin_asc';
    $orderBy = "r.ReservationDate DESC";

    switch ($sort) {
        case 'checkin_asc':
            $orderBy = "MIN(rd.CheckInDate) ASC";
            break;
        case 'checkin_desc':
            $orderBy = "MIN(rd.CheckInDate) DESC";
            break;
        case 'price_asc':
            $orderBy = "SUM(rd.Price) ASC";
            break;
        case 'price_desc':
            $orderBy = "SUM(rd.Price) DESC";
            break;
        default:
            $orderBy = "r.ReservationDate DESC";
    }

    $query = "
        SELECT 
            r.*, 
            MIN(rd.CheckInDate) AS CheckinDate, 
            MAX(rd.CheckOutDate) AS CheckoutDate, 
            SUM(rd.Price) AS TotalPrice
        FROM reservationtb r
        JOIN reservationdetailtb rd ON r.ReservationID = rd.ReservationID
        WHERE r.UserID = ? AND r.Status = 'Confirmed'
        GROUP BY r.ReservationID
        ORDER BY $orderBy
    ";

    $stmt = $connect->prepare($query);
    $stmt->bind_param("s", $userID);
    $stmt->execute();
    $result = $stmt->get_result();

    // Prepare reservations array for reservation_card.php
    $reservations = [];
    while ($row = $result->fetch_assoc()) {
        $reservations[] = $row;
    }

    ob_start();
    if (!empty($reservations)) {
        include 'reservation_card.php';
    } else {
        echo '<p class="text-center text-gray-400 my-36">No reservations found.</p>';
    }
    $html = ob_get_clean();

    echo $html;
    exit;
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
                <h1 class="text-2xl sm:text-4xl text-blue-900 font-semibold">Upcoming Stays</h1>
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
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6 p-4">
            <div class="flex items-center gap-2">
                <p class="text-sm text-gray-600">
                    <span class="font-medium text-gray-700"><?php echo count($reservations ?? []); ?></span> upcoming reservations
                </p>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 text-sm">
                    <span class="text-gray-600">Sort by:</span>
                    <select id="sortFilter" aria-label="Sort reservations by" class="ml-0 sm:ml-2 border-none font-medium focus:ring-0 outline-none bg-gray-100 sm:bg-transparent px-3 py-1 rounded sm:px-0 sm:py-0">
                        <option value="checkin_asc">Check-in date (earliest first)</option>
                        <option value="checkin_desc">Check-in date (latest first)</option>
                        <option value="price_asc">Price (low to high)</option>
                        <option value="price_desc">Price (high to low)</option>
                    </select>
                </div>
            </div>
        </div>


        <?php
        if ($userID !== null) {
        ?>
            <div id="reservationList" class="space-y-3">
                <?php
                include('../User/reservation_card.php');
                ?>
            </div>
        <?php
        } else {
        ?>
            <p class="text-center text-gray-400 my-36">You have no upcoming stays yet.</p>
        <?php } ?>

        <!-- Styles for fade animation -->
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
                    <!-- Reservation Details content dynamically loaded via JS -->
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
                        <span id="cancelButtonText">Confirm cancellation</span>
                        <svg id="cancelButtonSpinner" class="hidden w-5 h-5 ml-2 text-white animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MoveUp Btn -->
    <?php
    include('../includes/alert.php');
    include('../includes/loader.php');
    include('../includes/moveup_btn.php');
    include('../includes/footer.php');
    ?>

    <script src="//unpkg.com/alpinejs" defer></script>
    <script type="module" src="../JS/index.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const sortFilter = document.getElementById("sortFilter");
            const reservationList = document.getElementById("reservationList");
            let firstLoad = true;

            sortFilter.addEventListener("change", async function() {
                const sortValue = this.value;

                // Show skeleton loader
                if (firstLoad) {
                    reservationList.innerHTML = generateSkeleton(3);
                }

                try {
                    const response = await fetch(`?ajax=filter&sort=${sortValue}`);
                    const html = await response.text();

                    if (firstLoad) {
                        setTimeout(() => {
                            reservationList.innerHTML = html;
                            firstLoad = false;
                            window.bindReservationButtons();
                        }, 2000);
                    } else {
                        reservationList.innerHTML = html;
                        window.bindReservationButtons();
                    }
                } catch (error) {
                    reservationList.innerHTML = '<p class="text-center text-red-500 my-10">Error loading reservations.</p>';
                    console.error('Error fetching filtered reservations:', error);
                }

                // Initial binding
                window.bindReservationButtons();
            });

            function generateSkeleton(count) {
                let skeletonHTML = "";
                for (let i = 0; i < count; i++) {
                    skeletonHTML += `
<div class="bg-white rounded-sm border-2 border-gray-100 animate-pulse mb-4">
    <div class="md:flex items-stretch min-h-[260px]">
        <div class="md:w-1/3 bg-gray-200 h-[260px] md:h-auto flex items-center justify-center"></div>
        <div class="md:w-2/3 p-4 flex flex-col justify-between h-full">
            <div class="flex justify-between mb-16">
                <div class="flex flex-col">
                    <div class="h-5 bg-gray-200 rounded w-1/3 mb-3"></div>
                    <div class="flex flex-wrap items-center gap-2 mb-3">
                        <div class="h-3 bg-gray-200 rounded w-24"></div>
                        <div class="h-3 bg-gray-200 rounded w-24"></div>
                    </div>
                </div>
                <div class="space-y-2 text-right">
                    <div class="h-3 bg-gray-200 rounded w-16 ml-auto"></div>
                    <div class="h-5 bg-gray-200 rounded w-20 ml-auto"></div>
                    <div class="h-3 bg-gray-200 rounded w-24 ml-auto"></div>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-2">
                <div class="bg-gray-50 p-2.5 rounded-md space-y-2">
                    <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                    <div class="h-3 bg-gray-200 rounded w-3/4"></div>
                </div>
                <div class="bg-gray-50 p-2.5 rounded-md space-y-2">
                    <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                    <div class="h-3 bg-gray-200 rounded w-3/4"></div>
                </div>
                <div class="bg-gray-50 p-2.5 rounded-md space-y-2">
                    <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                    <div class="flex gap-1 flex-wrap">
                        <div class="h-4 bg-gray-200 rounded w-16"></div>
                        <div class="h-4 bg-gray-200 rounded w-16"></div>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-2">
                <div class="bg-gray-50 p-2.5 rounded-md space-y-2">
                    <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                    <div class="h-3 bg-gray-200 rounded w-3/4"></div>
                </div>
                <div class="bg-gray-50 p-2.5 rounded-md space-y-2">
                    <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                    <div class="flex gap-1 flex-wrap">
                        <div class="h-4 bg-gray-200 rounded w-16"></div>
                    </div>
                </div>
            </div>
            <div class="mt-5 flex flex-col sm:flex-row gap-2.5">
                <div class="h-10 bg-gray-200 rounded w-full"></div>
                <div class="h-10 bg-gray-200 rounded w-full"></div>
                <div class="h-10 bg-gray-200 rounded w-full"></div>
            </div>
        </div>
    </div>
</div>`;
                }
                return skeletonHTML;
            }
        });
    </script>
</body>

</html>