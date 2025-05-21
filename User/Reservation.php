<?php
session_start();
include('../config/dbConnection.php');
include('../includes/AutoIDFunc.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

$userID = (!empty($_SESSION["UserID"]) ? $_SESSION["UserID"] : null);
$alertMessage = '';

$user = "SELECT * FROM usertb WHERE UserID = '$userID'";
$userData = $connect->query($user)->fetch_assoc();

$nameParts = explode(' ', trim($userData['UserName']));
$initials = substr($nameParts[0], 0, 1);
if (count($nameParts) > 1) {
    $initials .= substr(end($nameParts), 0, 1);
}

// Get search parameters from URL with strict validation
if (isset($_GET["roomID"])) {
    $room_id = $_GET["roomID"];
    $checkin_date = isset($_GET['checkin_date']) ? htmlspecialchars($_GET['checkin_date']) : '';
    $checkout_date = isset($_GET['checkout_date']) ? htmlspecialchars($_GET['checkout_date']) : '';
    $adults = isset($_GET['adults']) ? (int)$_GET['adults'] : 1;
    $children = isset($_GET['children']) ? (int)$_GET['children'] : 0;
    $totalGuest = $adults + $children;

    // Calculate total nights stay
    $totalNights = 0;
    if (!empty($checkin_date) && !empty($checkout_date)) {
        $checkin = new DateTime($checkin_date);
        $checkout = new DateTime($checkout_date);

        // Ensure checkout is after checkin
        if ($checkout > $checkin) {
            $interval = $checkin->diff($checkout);
            $totalNights = $interval->days;
        }
    }

    $query = "SELECT r.*, rt.* FROM roomtb r 
          JOIN roomtypetb rt ON r.RoomTypeID = rt.RoomTypeID 
          WHERE r.RoomID = '$room_id'";

    $room = $connect->query($query)->fetch_assoc();
}

// Get reservation details
// if ($userID) {
//     $query = "SELECT * FROM reservationtb WHERE UserID = '$userID'";
//     $reservation = $connect->query($query)->fetch_assoc();

//     if ($reservation) {
//         $reservationID = $reservation['ReservationID'];
//         $totalPrice = $reservation['TotalPrice'];
//     }

//     $query = "SELECT * FROM reservationdetailtb rd
//             JOIN roomtb r ON rd.RoomID = r.RoomID
//             JOIN roomtypetb rt ON r.RoomTypeID = rt.RoomTypeID
//             WHERE ReservationID = '$reservationID'";
//     $room = $connect->query($query)->fetch_assoc();

//     if ($room) {
//         $roomID = $room['RoomID'];
//         $adults = $room['Adult'];
//         $children = $room['Children'];
//         $totalGuest = $adults + $children;

//         try {
//             $checkin_date = $room['CheckInDate'];
//             $checkout_date = $room['CheckOutDate'];

//             $totalNights = (strtotime($checkout_date) - strtotime($checkin_date)) / (60 * 60 * 24);
//         } catch (Exception $e) {
//             // Handle invalid dates
//             $alertMessage = "Invalid dates: " . $e->getMessage();
//             header("Location: Reservation.php");
//             exit();
//         }
//     }
// }

if ($userID) {
    // Get the user's reservation
    $query = "SELECT * FROM reservationtb WHERE UserID = '$userID' AND Status = 'Pending'";
    $reservationResult = $connect->query($query);

    if ($reservationResult->num_rows > 0) {
        $reservation = $reservationResult->fetch_assoc();
        $reservationID = $reservation['ReservationID'];
        $totalPrice = $reservation['TotalPrice'];

        // Get all reserved rooms for this reservation
        $roomsQuery = "SELECT rd.*, r.*, rt.* 
                      FROM reservationdetailtb rd
                      JOIN roomtb r ON rd.RoomID = r.RoomID
                      JOIN roomtypetb rt ON r.RoomTypeID = rt.RoomTypeID
                      WHERE rd.ReservationID = '$reservationID'";
        $roomsResult = $connect->query($roomsQuery);

        $reservedRooms = [];
        $totalNights = 0;

        if ($roomsResult->num_rows > 0) {
            while ($room = $roomsResult->fetch_assoc()) {
                // Calculate nights for each room (they should have same dates)
                try {
                    $checkin_date = $room['CheckInDate'];
                    $checkout_date = $room['CheckOutDate'];
                    $adults = $room['Adult'];
                    $children = $room['Children'];
                    $nights = (strtotime($checkout_date) - strtotime($checkin_date)) / (60 * 60 * 24);

                    // Store room data with additional calculated fields
                    $room['nights'] = $nights;
                    $room['subtotal'] = $room['Price'] * $nights;
                    $reservedRooms[] = $room;

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

        // Calculate total guests
        $totalGuest = 0;
        foreach ($reservedRooms as $room) {
            $totalGuest += $room['Adult'] + $room['Children'];
        }
    }
}

// Remove room from reservation
if (isset($_POST['remove_room'])) {
    $reservation_id = $_POST["reservation_id"];
    $room_id = $_POST["room_id"];
    $query = "DELETE FROM reservationdetailtb WHERE RoomID = '$room_id' AND ReservationID = '$reservation_id'";
    $connect->query($query);

    // Redirect back to the reservation page
    header("Location: Reservation.php");
    exit();
}

// Add room to favorites
if (isset($_POST['room_favourite'])) {
    if ($userID) {
        $room_id = $_GET["roomID"];
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
        $redirect_url = "Reservation.php?roomID=$room_id&checkin_date=$checkin_date&checkout_date=$checkout_date&adults=$adults&children=$children";
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

    <div class="max-w-[1150px] mx-auto px-4 py-8">
        <!-- Progress Steps -->
        <div class="flex justify-between items-center mb-8">
            <div class="flex-1 text-center">
                <div class="w-8 h-8 mx-auto rounded-full bg-blue-600 text-white flex items-center justify-center mb-2">1</div>
                <p class="text-sm font-medium text-blue-600">Your selection</p>
            </div>
            <div class="flex-1 border-t-2 border-blue-600"></div>
            <div class="flex-1 text-center">
                <div class="w-8 h-8 mx-auto rounded-full bg-blue-600 text-white flex items-center justify-center mb-2">2</div>
                <p class="text-sm font-medium text-blue-600">Enter your details</p>
            </div>
            <div class="flex-1 border-t-2 border-gray-300"></div>
            <div class="flex-1 text-center">
                <div class="w-8 h-8 mx-auto rounded-full bg-gray-300 text-gray-600 flex items-center justify-center mb-2">3</div>
                <p class="text-sm font-medium text-gray-600">Confirm your reservation</p>
            </div>
        </div>

        <!-- Main Content -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <!-- Header -->
            <div class="bg-blue-50 p-6 border-b border-gray-200">
                <h1 class="text-2xl font-bold text-gray-800">Great choice! You're almost done.</h1>
            </div>

            <!-- Booking Details -->
            <div class="p-6 border-b border-gray-200">
                <div class="flex flex-col md:flex-row gap-7">
                    <!-- Left Column - Booking Information -->
                    <div class="w-full md:w-1/3">
                        <div class="space-y-6">
                            <!-- Booking Summary -->
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                                <h3 class="text-lg font-semibold text-white mb-0 border-b border-blue-800 bg-blue-900 p-3">
                                    Your booking details
                                </h3>

                                <div class="p-4 space-y-4">
                                    <?php if (!empty($checkin_date) && !empty($checkout_date)): ?>
                                        <!-- Booking details when dates are available -->
                                        <div class="grid grid-cols-2 gap-0">
                                            <div class="bg-gray-50 p-3 rounded-lg">
                                                <p class="text-xs font-medium text-gray-500 mb-1 flex items-center">
                                                    <i class="ri-calendar-event-line mr-1"></i> Check-in
                                                </p>
                                                <p class="text-sm font-semibold text-gray-800">
                                                    <?= date('D, j M Y', strtotime($checkin_date)); ?>
                                                </p>
                                                <p class="text-xs text-gray-500 mt-1">
                                                    After 2:00 PM
                                                </p>
                                            </div>

                                            <div class="bg-gray-50 p-3 rounded-lg">
                                                <p class="text-xs font-medium text-gray-500 mb-1 flex items-center">
                                                    <i class="ri-calendar-event-line mr-1"></i> Check-out
                                                </p>
                                                <p class="text-sm font-semibold text-gray-800">
                                                    <?= date('D, j M Y', strtotime($checkout_date)); ?>
                                                </p>
                                                <p class="text-xs text-gray-500 mt-1">
                                                    Before 12:00 PM
                                                </p>
                                            </div>

                                            <div class="bg-gray-50 p-3 rounded-lg">
                                                <p class="text-xs font-medium text-gray-500 mb-1 flex items-center">
                                                    <i class="ri-hotel-bed-line mr-1"></i> Total stay
                                                </p>
                                                <p class="text-sm font-semibold text-gray-800">
                                                    <?= $totalNights ?> <?= $totalNights > 1 ? 'nights' : 'night' ?>
                                                </p>
                                                <p class="text-xs text-gray-500 mt-1">
                                                    <?= $totalNights > 1 ? 'From arrival to departure' : 'One night stay' ?>
                                                </p>
                                            </div>

                                            <div class="bg-gray-50 p-3 rounded-lg">
                                                <p class="text-xs font-medium text-gray-500 mb-1 flex items-center">
                                                    <i class="ri-user-line mr-1"></i> Guests
                                                </p>
                                                <p class="text-sm font-semibold text-gray-800">
                                                    <?= $adults ?> adult<?= $adults > 1 ? 's' : '' ?>
                                                    <?= $children > 0 ? ', ' . $children . ' child' . ($children > 1 ? 'ren' : '') : '' ?>
                                                </p>
                                                <p class="text-xs text-gray-500 mt-1">
                                                    Max capacity: <?= $room['RoomCapacity'] ?? 'N/A' ?>
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Additional booking info -->
                                        <div class="bg-blue-50 border border-blue-100 rounded-lg p-3 mt-2">
                                            <p class="text-xs text-blue-800 flex items-start">
                                                <i class="ri-information-line mr-2 mt-0.5"></i>
                                                Need to modify your booking? Contact our customer service for assistance.
                                            </p>
                                        </div>
                                    <?php else: ?>
                                        <!-- Empty state when no booking details -->
                                        <div class="text-center py-6">
                                            <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                                                <i class="ri-calendar-line text-2xl text-gray-400"></i>
                                            </div>
                                            <h4 class="text-sm font-medium text-gray-500 mb-1">No booking details found</h4>
                                            <p class="text-xs text-gray-400 mb-4">Please select dates to see booking information</p>
                                            <a href="RoomBooking.php" class="inline-flex items-center text-xs font-medium text-blue-600 hover:text-blue-800">
                                                Browse available rooms <i class="ri-arrow-right-line ml-1"></i>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Price Summary -->
                            <div class="bg-white rounded-lg shadow-sm">
                                <h3 class="text-lg font-semibold text-white mb-4 border-b border-gray-100 bg-blue-900 p-2">Price Summary</h3>
                                <div class="space-y-3">
                                    <?php
                                    if (empty($reservationID)) {
                                        // Show message when no reservation exists
                                        echo '<div class="text-center py-6">';
                                        echo '<i class="ri-hotel-bed-line text-4xl text-gray-300 mb-3"></i>';
                                        echo '<p class="text-gray-500">You don\'t have any active reservations</p>';
                                        echo '<a href="RoomBooking.php" class="mt-2 inline-block text-blue-600 hover:text-blue-800 text-sm font-medium">';
                                        echo 'Browse available rooms →';
                                        echo '</a>';
                                        echo '</div>';
                                    } else {
                                        // Query to get all reservation details with room information
                                        $detailsQuery = "SELECT rd.*, rt.RoomType, r.RoomName, rt.RoomCoverImage 
                        FROM reservationdetailtb rd
                        JOIN roomtb r ON rd.RoomID = r.RoomID
                        JOIN roomtypetb rt ON r.RoomTypeID = rt.RoomTypeID
                        WHERE rd.ReservationID = '$reservationID'";
                                        $detailsResult = mysqli_query($connect, $detailsQuery);

                                        if (!$detailsResult) {
                                            die("Error fetching reservation details: " . mysqli_error($connect));
                                        }

                                        $grandTotal = 0;

                                        if (mysqli_num_rows($detailsResult) > 0) {
                                            while ($roomItem = mysqli_fetch_assoc($detailsResult)) {
                                                $checkIn = new DateTime($roomItem['CheckInDate']);
                                                $checkOut = new DateTime($roomItem['CheckOutDate']);
                                                $totalNights = $checkOut->diff($checkIn)->days;
                                                $roomTotal = $roomItem['Price'] * $totalNights;
                                                $grandTotal += $roomTotal;
                                    ?>
                                                <div>
                                                    <div class="flex justify-between">
                                                        <h4 class="font-medium text-gray-800">
                                                            <?= htmlspecialchars($roomItem['RoomName'] ?? 'Room') ?> <?= htmlspecialchars($roomItem['RoomType']) ?>
                                                        </h4>
                                                        <p class="font-semibold text-gray-800">$<?= number_format($roomTotal, 2) ?></p>
                                                    </div>
                                                    <p class="text-sm text-gray-500 mt-1">
                                                        <?= $checkIn->format('M j, Y') ?> - <?= $checkOut->format('M j, Y') ?>
                                                        (<?= $totalNights ?> night<?= $totalNights > 1 ? 's' : '' ?>)
                                                    </p>
                                                    <p class="text-sm text-gray-500 mt-1">
                                                        <?= $roomItem['Adult'] ?> adult<?= $roomItem['Adult'] > 1 ? 's' : '' ?>
                                                        <?= $roomItem['Children'] > 0 ? ' + ' . $roomItem['Children'] . ' child' . ($roomItem['Children'] > 1 ? 'ren' : '') : '' ?>
                                                    </p>
                                                </div>
                                            <?php
                                            }
                                            ?>
                                            <div class="pt-3 border-t border-gray-100">
                                                <div class="flex justify-between items-center">
                                                    <p class="text-base font-semibold text-gray-800">Subtotal</p>
                                                    <p class="text-gray-800">$<?= number_format($grandTotal, 2) ?></p>
                                                </div>
                                                <div class="flex justify-between items-center mt-1">
                                                    <p class="text-sm text-gray-600">Taxes and fees</p>
                                                    <p class="text-gray-800">$<?= number_format($grandTotal * 0.1, 2) ?></p> <!-- Assuming 10% tax -->
                                                </div>
                                                <div class="flex justify-between items-center mt-3 pt-3 border-t border-gray-200">
                                                    <p class="text-lg font-bold text-gray-900">Total</p>
                                                    <p class="text-lg font-bold text-blue-600">$<?= number_format($grandTotal * 1.1, 2) ?></p> <!-- Total with tax -->
                                                </div>
                                                <p class="text-xs text-green-600 mt-1">✓ Includes all taxes and fees</p>
                                            </div>
                                    <?php
                                        } else {
                                            // Show message when reservation exists but has no rooms
                                            echo '<div class="text-center py-6">';
                                            echo '<i class="ri-shopping-cart-line text-4xl text-gray-300 mb-3"></i>';
                                            echo '<p class="text-gray-500">Your reservation is empty</p>';
                                            echo '<a href="RoomBooking.php" class="mt-2 inline-block text-blue-600 hover:text-blue-800 text-sm font-medium">';
                                            echo 'Add rooms to your reservation →';
                                            echo '</a>';
                                            echo '</div>';
                                        }
                                    }
                                    ?>

                                    <div class="pt-2">
                                        <p class="text-xs text-gray-500">
                                            <i class="ri-information-line"></i> Price in USD. Your card issuer may apply foreign transaction fees.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Room Information (Keep existing code exactly as is) -->
                    <div class="w-full">
                        <!-- Display reserved rooms -->
                        <?php if (!empty($reservedRooms)): ?>
                            <div class="space-y-2">
                                <?php foreach ($reservedRooms as $room): ?>
                                    <div class="flex flex-col md:flex-row rounded-md shadow-sm border">
                                        <div class="md:w-[48%] h-56 overflow-hidden select-none rounded-l-md relative">
                                            <img src="../Admin/<?= htmlspecialchars($room['RoomCoverImage']) ?>"
                                                alt="<?= htmlspecialchars($room['RoomType']) ?>"
                                                class="w-full h-full object-cover">
                                        </div>
                                        <div class="w-full p-4">
                                            <div class="flex justify-between items-start">
                                                <div class="flex items-center gap-4">
                                                    <h2 class="text-xl font-bold text-gray-800">
                                                        <?= htmlspecialchars($room['RoomName']) ?>
                                                        <?= htmlspecialchars($room['RoomType']) ?>
                                                    </h2>
                                                </div>
                                                <div class="text-right">
                                                    <div class="text-sm text-gray-500">
                                                        <?= $room['nights'] ?> night<?= $room['nights'] > 1 ? 's' : '' ?>
                                                    </div>
                                                    <div class="text-lg font-bold text-orange-500">
                                                        USD<?= number_format($room['subtotal'], 2) ?>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="text-sm text-gray-600 mt-1">
                                                Check-in: <?= date('M j, Y', strtotime($room['CheckInDate'])) ?><br>
                                                Check-out: <?= date('M j, Y', strtotime($room['CheckOutDate'])) ?>
                                            </div>

                                            <div class="text-sm text-gray-600 mt-1">
                                                Guests: <?= $room['Adult'] ?> adult<?= $room['Adult'] > 1 ? 's' : '' ?>
                                                <?= $room['Children'] > 0 ? ' + ' . $room['Children'] . ' child' . ($room['Children'] > 1 ? 'ren' : '') : '' ?>
                                            </div>

                                            <!-- Remove room button -->
                                            <form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" class="mt-4">
                                                <input type="hidden" name="reservation_id" value="<?= $reservationID ?>">
                                                <input type="hidden" name="room_id" value="<?= $room['RoomID'] ?>">
                                                <button type="submit" name="remove_room"
                                                    class="text-red-600 hover:text-red-800 text-sm font-medium">
                                                    Remove Room
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-8">
                                <p class="text-gray-500">You don't have any rooms reserved yet.</p>
                                <a href="RoomBooking.php" class="text-blue-600 hover:underline mt-2 inline-block">
                                    Browse available rooms
                                </a>
                            </div>
                        <?php endif; ?>

                        <div class="py-6">
                            <h2 class="text-xl font-semibold text-gray-800 mb-4">Enter your details</h2>

                            <div class="mb-6">
                                <div class="flex items-center mb-2">
                                    <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center mr-3">
                                        <span class="w-10 h-10 rounded-full bg-[<?= $userData['ProfileBgColor'] ?>] text-white uppercase font-semibold flex items-center justify-center select-none"><?= $initials ?></span>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h4 class="text-sm font-medium text-gray-800"><?= $userData['UserName'] ?></h4>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-gray-600 mb-4">Are you travelling for work?</p>
                                <div class="flex space-x-4">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="work_travel" class="h-4 w-4 text-blue-600">
                                        <span class="ml-2">Yes</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="work_travel" class="h-4 w-4 text-blue-600" checked>
                                        <span class="ml-2">No</span>
                                    </label>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                                    <select class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        <option>Mr.</option>
                                        <option>Mrs.</option>
                                        <option>Ms.</option>
                                        <option>Dr.</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">First name <span class="text-red-500">*</span></label>
                                    <input type="text" value="<?= $userData['UserName'] ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Last name (Optional)</label>
                                    <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email address <span class="text-red-500">*</span></label>
                                <input type="email" value="<?= $userData['UserEmail'] ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" disabled>
                                <input type="hidden" name="email" value="<?= $userData['UserEmail'] ?>">
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone <span class="text-red-500">*</span></label>
                                <input type="tel" value="<?= $userData['UserPhone'] ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div class="flex justify-between items-center">
                                <a href="#" class="text-blue-600 hover:text-blue-800 font-medium">Back</a>
                                <button class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-md">
                                    Continue to payment
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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