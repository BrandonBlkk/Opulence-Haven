<?php
session_start();
include('../config/db_connection.php');
include('../includes/auto_id_func.php');
include('../includes/mask_email.php');

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
    $reservation_id = isset($_GET['reservation_id']) ? $_GET['reservation_id'] : '';
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
                    $room['total'] = $room['Price'] * $nights * 1.1;
                    $room['subtotal'] = $room['Price'] * $nights;
                    $reservedRooms[] = $room;

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

        // Calculate total guests
        $totalGuest = 0;
        foreach ($reservedRooms as $room) {
            $totalGuest += $room['Adult'] + $room['Children'];
        }
    }
}

// Edit room
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['edit_room'])) {
        // Update the room status to "Edit"
        $roomId = $_POST['room_id'];
        $reservationId = $_POST['reservation_id'];
        $checkin_date = $_POST['checkin_date'];
        $checkout_date = $_POST['checkout_date'];
        $adults = $_POST['adults'];
        $children = $_POST['children'];

        // Execute your database update here
        $updateQuery = "UPDATE roomtb SET RoomStatus = 'Edit' WHERE RoomID = ?";
        $stmt = $connect->prepare($updateQuery);
        $stmt->bind_param("s", $roomId);
        $stmt->execute();

        // Then redirect to the room_booking.php page with parameters
        header("Location: room_booking.php?reservation_id=$reservationId&room_id=$roomId&checkin_date=$checkin_date&checkout_date=$checkout_date&adults=$adults&children=$children&edit=1");
        exit();
    }
}

// Remove room from reservation
if (isset($_POST['remove_room'])) {
    $response = ['success' => false];
    $reservation_id = $_POST["reservation_id"];
    $room_id = $_POST["room_id"];

    // Remove room from reservation
    $query = "DELETE FROM reservationdetailtb WHERE RoomID = ? AND ReservationID = ?";
    $stmt = $connect->prepare($query);
    $stmt->bind_param("ss", $room_id, $reservation_id);
    $stmt->execute();

    // Update room status
    $room = "UPDATE roomtb SET RoomStatus = 'Available' WHERE RoomID = ?";
    $stmt = $connect->prepare($room);
    $stmt->bind_param("s", $room_id);
    $stmt->execute();

    // Check if reservation has any rooms left
    $query = "SELECT COUNT(*) as count FROM reservationdetailtb WHERE ReservationID = ?";
    $stmt = $connect->prepare($query);
    $stmt->bind_param("s", $reservation_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];

    if ($count == 0) {
        // Delete empty reservation
        $delete = "DELETE FROM reservationtb WHERE ReservationID = ?";
        $stmt = $connect->prepare($delete);
        $stmt->bind_param("s", $reservation_id);
        $stmt->execute();
    }

    $response['success'] = true;

    // Return JSON response for AJAX
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Add room to favorites
if (isset($_POST['room_favourite'])) {
    if ($userID) {
        $room_id = $_GET["roomID"];
        $roomTypeID = $_POST['roomTypeID'];

        // Get search parameters 
        $checkin_date = isset($_POST['checkin_date']) ? $_POST['checkin_date'] : '';
        $checkout_date = isset($_POST['checkout_date']) ? $_POST['checkout_date'] : '';
        $adults = isset($_POST['adults']) ? intval($_POST['adults']) : 1;
        $children = isset($_POST['children']) ? intval($_POST['children']) : 0;

        $check = "SELECT COUNT(*) as count FROM roomtypefavoritetb WHERE UserID = ? AND RoomTypeID = ?";
        $stmt = $connect->prepare($check);
        $stmt->bind_param("ss", $userID, $roomTypeID);
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->fetch_assoc()['count'];

        if ($count == 0) {
            $insert = "INSERT INTO roomtypefavoritetb (UserID, RoomTypeID, CheckInDate, CheckOutDate, Adult, Children) 
                      VALUES ('$userID', '$roomTypeID', '$checkin_date', '$checkout_date', '$adults', '$children')";
            $connect->query($insert);
        } else {
            $delete = "DELETE FROM roomtypefavoritetb WHERE UserID = ? AND RoomTypeID = ?";
            $stmt = $connect->prepare($delete);
            $stmt->bind_param("ss", $userID, $roomTypeID);
            $stmt->execute();
        }

        // Redirect back with the same search parameters
        $redirect_url = "reservation.php?roomID=$room_id&checkin_date=$checkin_date&checkout_date=$checkout_date&adults=$adults&children=$children";
        header("Location: $redirect_url");
        exit();
    } else {
        $showLoginModal = true;
    }
}

// Submit reservation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_reservation'])) {
    // Get form data
    $reservationID = $_POST['reservation_id'] ?? '';
    $travelling = isset($_POST['work_travel']) ? (int)$_POST['work_travel'] : 0; // Default to 0 
    $title = $_POST['title'] ?? 'Mr.';
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    $phone = $_POST['phone'] ?? '';

    try {
        // Check if reservation exists
        $reservationQuery = "SELECT * FROM reservationtb WHERE ReservationID = ? AND Status = 'Pending'";
        $stmt = $connect->prepare($reservationQuery);
        $stmt->bind_param("s", $reservationID);
        $stmt->execute();
        $reservation = $stmt->get_result()->fetch_assoc();

        if (!$reservation) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'reservation_id' => $reservationID,
                'message' => 'Your reservation cannot be found. It may have expired or already been confirmed.'
            ]);
            exit();
        }

        // Update reservation with personal details
        $updateQuery = "UPDATE reservationtb SET Travelling = ?, Title = ?, FirstName = ?, LastName = ?, UserPhone = ? WHERE ReservationID = ? AND Status = 'Pending'";
        $stmt = $connect->prepare($updateQuery);
        $stmt->bind_param("isssss", $travelling, $title, $firstName, $lastName, $phone, $reservationID);

        if ($stmt->execute()) {
            // Return success response
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'reservation_id' => $reservationID,
                'message' => 'Reservation confirmed successfully'
            ]);
            exit();
        } else {
            throw new Exception("Failed to execute query: " . $stmt->error);
        }
    } catch (Exception $e) {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
        exit();
    }
}

// Handle payment callback
if (isset($_GET['payment'])) {
    if ($_GET['payment'] === 'success') {
        $_SESSION['success'] = "Payment successful! Your reservation is confirmed.";

        // Update reservation status to confirmed
        if (isset($reservationID)) {
            $update = "UPDATE reservationtb SET Status = 'Confirmed' WHERE ReservationID = ?";
            $stmt = $connect->prepare($update);
            $stmt->bind_param("s", $reservationID);
            $stmt->execute();
        }
    } elseif ($_GET['payment'] === 'cancel') {
        $_SESSION['alert'] = "Payment was cancelled. Your reservation is still pending.";
    }

    // Remove payment parameter from URL
    header("Location: reservation.php");
    exit();
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
    include('../includes/navbar.php');
    include('../includes/cookies.php');
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
                                        echo '</div>';
                                    } else {
                                        // Get user's current points balance
                                        $pointsQuery = "SELECT PointsBalance, Membership FROM usertb WHERE UserID = '$userID'";
                                        $pointsResult = mysqli_query($connect, $pointsQuery);
                                        $userPoints = mysqli_fetch_assoc($pointsResult);
                                        $pointsBalance = $userPoints['PointsBalance'] ?? 0;
                                        $membership = $userPoints['Membership'] ?? 0;

                                        // Query to get all reservation details with room information
                                        $detailsQuery = "SELECT rd.*, rt.RoomType, r.RoomName, rt.RoomCoverImage, rt.RoomPrice 
                    FROM reservationdetailtb rd
                    JOIN roomtb r ON rd.RoomID = r.RoomID
                    JOIN roomtypetb rt ON r.RoomTypeID = rt.RoomTypeID
                    WHERE rd.ReservationID = '$reservationID'";
                                        $detailsResult = mysqli_query($connect, $detailsQuery);

                                        if (!$detailsResult) {
                                            die("Error fetching reservation details: " . mysqli_error($connect));
                                        }

                                        $grandTotal = 0;
                                        $subtotal = 0;
                                        $pointsDiscount = 0;
                                        $pointsRedeemed = 0;
                                        $showPointsOption = false;

                                        if (mysqli_num_rows($detailsResult) > 0) {
                                            // Calculate room totals
                                            while ($roomItem = mysqli_fetch_assoc($detailsResult)) {
                                                $checkIn = new DateTime($roomItem['CheckInDate']);
                                                $checkOut = new DateTime($roomItem['CheckOutDate']);
                                                $totalNights = $checkOut->diff($checkIn)->days;
                                                $roomTotal = $roomItem['RoomPrice'] * $totalNights;
                                                $grandTotal += $roomTotal;
                                                $subtotal += $roomTotal;
                                            }
                                            mysqli_data_seek($detailsResult, 0); // Reset pointer for display

                                            // Calculate maximum possible discount (100 points = $1, max 20%)
                                            $maxDiscountAmount = $subtotal * 0.2;
                                            $maxPointsDiscount = floor($pointsBalance / 100);
                                            $actualDiscount = min($maxDiscountAmount, $maxPointsDiscount);

                                            // Check if user can redeem points
                                            $showPointsOption = ($pointsBalance >= 100 && $actualDiscount > 0);

                                            // Process points redemption if requested
                                            if (isset($_POST['apply_points']) && $showPointsOption) {
                                                $pointsDiscount = $actualDiscount;
                                                $pointsRedeemed = $actualDiscount * 100;
                                                $grandTotal -= $pointsDiscount;

                                                // Update reservation with points info
                                                $updateQuery = "UPDATE reservationtb 
                          SET PointsDiscount = '$pointsDiscount', 
                              PointsRedeemed = '$pointsRedeemed'
                          WHERE ReservationID = '$reservationID'";
                                                mysqli_query($connect, $updateQuery);

                                                // Update user's points balance
                                                $newBalance = $pointsBalance - $pointsRedeemed;
                                                $updatePoints = "UPDATE usertb SET PointsBalance = '$newBalance' WHERE UserID = '$userID'";
                                                mysqli_query($connect, $updatePoints);
                                                $pointsBalance = $newBalance;
                                            }

                                            // Handle remove discount request 
                                            if (isset($_POST['remove_points'])) {
                                                // Get the current points balance first
                                                $currentPointsQuery = "SELECT PointsBalance, Membership FROM usertb WHERE UserID = '$userID'";
                                                $currentResult = $connect->query($currentPointsQuery);
                                                $currentRow = $currentResult->fetch_assoc();
                                                $currentPoints = $currentRow['PointsBalance'];
                                                $membership = $currentRow['Membership'];

                                                // Get the redeemed points from the reservation
                                                $getPointsQuery = "SELECT PointsRedeemed FROM reservationtb WHERE ReservationID = '$reservationID'";
                                                $result = $connect->query($getPointsQuery);
                                                $row = $result->fetch_assoc();
                                                $pointsToReturn = $row['PointsRedeemed'];

                                                // Calculate the original points balance before redemption
                                                $originalPoints = $currentPoints + $pointsToReturn;

                                                // Reset points in reservationtb
                                                $removeQuery = "UPDATE reservationtb 
                          SET PointsDiscount = 0, PointsRedeemed = 0 
                          WHERE ReservationID = '$reservationID'";
                                                $connect->query($removeQuery);

                                                // Return FULL original points to usertb (not just the redeemed amount)
                                                $returnPointsQuery = "UPDATE usertb 
                               SET PointsBalance = $originalPoints 
                               WHERE UserID = '$userID'";
                                                $connect->query($returnPointsQuery);

                                                // Update local variable to reflect changes
                                                $pointsBalance = $originalPoints;
                                                $pointsDiscount = 0;
                                                $pointsRedeemed = 0;
                                                $grandTotal = $subtotal; // Reset grand total to original amount
                                            }

                                            // Display room details
                                            while ($roomItem = mysqli_fetch_assoc($detailsResult)) {
                                                $checkIn = new DateTime($roomItem['CheckInDate']);
                                                $checkOut = new DateTime($roomItem['CheckOutDate']);
                                                $totalNights = $checkOut->diff($checkIn)->days;
                                                $roomTotal = $roomItem['RoomPrice'] * $totalNights;
                                    ?>
                                                <div>
                                                    <div class="flex justify-between">
                                                        <h4 class="font-medium text-gray-800">
                                                            <?= htmlspecialchars($roomItem['RoomName'] ?? 'Room') ?> <?= htmlspecialchars($roomItem['RoomType']) ?>
                                                        </h4>
                                                        <p class="font-semibold text-gray-800">$<?= number_format($roomItem['RoomPrice'], 2) ?></p>
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
                                                    <p class="text-gray-800">$<?= number_format($subtotal, 2) ?></p>
                                                </div>

                                                <!-- Points Discount Section -->
                                                <?php if ($showPointsOption): ?>
                                                    <div class="mt-4">
                                                        <?php if ($pointsDiscount > 0): ?>
                                                            <!-- Confetti container (hidden by default) -->
                                                            <div id="confetti-container" class="fixed inset-0 pointer-events-none z-50 hidden"></div>

                                                            <!-- Applied Voucher Style -->
                                                            <div class="border-2 border-green-400 rounded-lg bg-green-50 p-3">
                                                                <div class="flex justify-between items-center">
                                                                    <div class="flex items-center">
                                                                        <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                                        </svg>
                                                                        <span class="font-semibold text-green-700">Points Applied</span>
                                                                    </div>
                                                                    <span class="font-bold text-green-700">-$<?= number_format($pointsDiscount, 2) ?></span>
                                                                </div>
                                                                <div class="flex justify-between items-center mt-2 pt-2 border-t border-green-200">
                                                                    <span class="text-xs text-gray-600">
                                                                        Used <?= $pointsRedeemed ?> points (<?= number_format($pointsDiscount, 2) ?> discount)
                                                                    </span>
                                                                    <form method="post">
                                                                        <button type="submit" name="remove_points" class="text-xs font-medium text-red-600 hover:text-red-800 underline">
                                                                            Remove
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            </div>

                                                            <script>
                                                                // Show confetti only if points were just applied
                                                                <?php if (isset($_POST['apply_points'])): ?>
                                                                    document.addEventListener('DOMContentLoaded', function() {
                                                                        const container = document.getElementById('confetti-container');
                                                                        container.classList.remove('hidden');

                                                                        // Confetti colors
                                                                        const colors = ['#f44336', '#e91e63', '#9c27b0', '#673ab7', '#3f51b5',
                                                                            '#2196f3', '#03a9f4', '#00bcd4', '#009688', '#4CAF50',
                                                                            '#8BC34A', '#CDDC39', '#FFEB3B', '#FFC107', '#FF9800', '#FF5722'
                                                                        ];

                                                                        // Create confetti pieces
                                                                        for (let i = 0; i < 100; i++) {
                                                                            setTimeout(() => {
                                                                                const confetti = document.createElement('div');
                                                                                confetti.className = 'confetti';

                                                                                // Random properties
                                                                                const color = colors[Math.floor(Math.random() * colors.length)];
                                                                                const size = Math.random() * 10 + 5;
                                                                                const left = Math.random() * 100;
                                                                                const animationDuration = Math.random() * 3 + 2;
                                                                                const delay = Math.random();

                                                                                confetti.style.backgroundColor = color;
                                                                                confetti.style.width = `${size}px`;
                                                                                confetti.style.height = `${size}px`;
                                                                                confetti.style.left = `${left}%`;
                                                                                confetti.style.animationDuration = `${animationDuration}s`;
                                                                                confetti.style.animationDelay = `${delay}s`;
                                                                                confetti.style.borderRadius = Math.random() > 0.5 ? '50%' : '0';

                                                                                container.appendChild(confetti);

                                                                                // Remove after animation
                                                                                confetti.addEventListener('animationend', function() {
                                                                                    confetti.remove();
                                                                                });
                                                                            }, i * 30);
                                                                        }

                                                                        // Hide container after animation
                                                                        setTimeout(() => {
                                                                            container.classList.add('hidden');
                                                                        }, 10000);
                                                                    });

                                                                    // Confetti animation
                                                                    const style = document.createElement('style');
                                                                    style.textContent = `
                                                                    .confetti {
                                                                        position: absolute;
                                                                        width: 10px;
                                                                        height: 10px;
                                                                        opacity: 0;
                                                                        animation: confetti-fall 3s ease-in-out forwards;
                                                                        top: -10px;
                                                                        z-index: 1000;
                                                                    }
                                                                    @keyframes confetti-fall {
                                                                        0% {
                                                                            transform: translateY(0) rotate(0deg);
                                                                            opacity: 1;
                                                                        }
                                                                        100% {
                                                                            transform: translateY(100vh) rotate(360deg);
                                                                            opacity: 0;
                                                                        }
                                                                    }
                                                                `;
                                                                    document.head.appendChild(style);
                                                                <?php endif; ?>
                                                            </script>
                                                        <?php else: ?>
                                                            <!-- Redeem Voucher Style -->
                                                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-3 hover:border-blue-400 transition-colors">
                                                                <form method="post" class="flex items-center justify-between">
                                                                    <div class="flex items-center">
                                                                        <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                                                                        </svg>
                                                                        <div>
                                                                            <p class="text-sm font-medium text-gray-700">Redeem Points</p>
                                                                            <p class="text-xs text-gray-500">
                                                                                <?= $pointsBalance ?> points available (Save up to $<?= number_format($actualDiscount, 2) ?>)
                                                                            </p>
                                                                        </div>
                                                                    </div>
                                                                    <button type="submit" name="apply_points" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-full text-sm font-medium transition-colors">
                                                                        Apply
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>

                                                <div class="flex justify-between items-center mt-1">
                                                    <p class="text-sm text-gray-600">Taxes and fees</p>
                                                    <p class="text-gray-800">$<?= number_format($grandTotal * 0.1, 2) ?></p>
                                                </div>

                                                <div class="flex justify-between items-center mt-3 pt-3 border-t border-gray-200">
                                                    <p class="text-lg font-bold text-gray-900">Total</p>
                                                    <p class="text-lg font-bold text-blue-600">$<?= number_format(($grandTotal * 1.1), 2) ?></p>
                                                </div>

                                                <!-- Points Earned Notification -->
                                                <?php
                                                $pointsEarned = floor($subtotal);
                                                if ($membership == 1) $pointsEarned = floor($subtotal * 3);  // 3 pts/$
                                                else $pointsEarned = floor($subtotal * 1);  // 1 pt/$
                                                ?>
                                                <p class="text-xs text-green-600 mt-1">
                                                    ✓ Earn <?= $pointsEarned ?> points after payment
                                                    <?= $membership == 1 ? '(Membership bonus applied)' : '' ?>
                                                </p>
                                            </div>
                                    <?php
                                        } else {
                                            // Show message when reservation exists but has no rooms
                                            echo '<div class="text-center py-6">';
                                            echo '<i class="ri-shopping-cart-line text-4xl text-gray-300 mb-3"></i>';
                                            echo '<p class="text-gray-500">Your reservation is empty</p>';
                                            echo '<a href="room_booking.php" class="mt-2 inline-block text-blue-600 hover:text-blue-800 text-sm font-medium">';
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

                    <!-- Right Column - Room Information and Expiry -->
                    <div class="w-full">
                        <?php
                        // When loading the reservation page
                        $reservationQuery = "SELECT ExpiryDate FROM reservationtb WHERE ReservationID = ?";
                        $stmt = $connect->prepare($reservationQuery);
                        $stmt->bind_param("s", $reservationID);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $reservation = $result->fetch_assoc();

                        // Check if reservation exists and get expiry time
                        if ($reservation) {
                            $expiryTimestamp = strtotime($reservation['ExpiryDate']) * 1000;
                            $expiryDisplay = date('h:i A', strtotime($reservation['ExpiryDate']));
                        } else {
                            // Reservation expired or doesn't exist
                            $expiryTimestamp = 0;
                            $expiryDisplay = "EXPIRED";
                        }
                        ?>
                        <div class="expiry-notice mb-3 <?= $reservation ? '' : 'hidden' ?>">
                            <div class="flex items-center" id="countdown-container">
                                <svg id="countdown-icon" class="h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                </svg>
                                <span class="font-medium">Reservation expires in:</span>
                                <span id="countdown-timer" class="font-bold ml-1" data-expiry="<?= $expiryTimestamp ?>">
                                    <?= $expiryDisplay ?>
                                </span>
                            </div>
                        </div>

                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const timerElement = document.getElementById('countdown-timer');
                                const containerElement = document.getElementById('countdown-container');
                                const iconElement = document.getElementById('countdown-icon');
                                const expiryTimestamp = parseInt(timerElement.dataset.expiry);

                                function updateTimerColors(minutes) {
                                    // Reset all classes first
                                    containerElement.className = 'flex items-center p-4 border-l-4';
                                    iconElement.className = 'h-5 w-5 mr-2';

                                    if (minutes <= 0) {
                                        // Expired
                                        containerElement.classList.add('bg-gray-100', 'border-gray-400', 'text-gray-800');
                                        iconElement.classList.add('text-gray-500');
                                    } else if (minutes < 5) {
                                        // Critical
                                        containerElement.classList.add('bg-red-50', 'border-red-400', 'text-red-800');
                                        iconElement.classList.add('text-red-500');
                                    } else if (minutes < 10) {
                                        // Warning
                                        containerElement.classList.add('bg-amber-50', 'border-amber-400', 'text-amber-800');
                                        iconElement.classList.add('text-amber-500');
                                    } else {
                                        // Normal
                                        containerElement.classList.add('bg-green-50', 'border-green-400', 'text-green-800');
                                        iconElement.classList.add('text-green-500');
                                    }
                                }

                                function updateCountdown() {
                                    // If already expired (timestamp is 0)
                                    if (expiryTimestamp <= 0) {
                                        timerElement.textContent = "EXPIRED";
                                        updateTimerColors(0);
                                        return;
                                    }

                                    const now = new Date().getTime();
                                    const remaining = expiryTimestamp - now;

                                    // If expired during countdown
                                    if (remaining <= 0) {
                                        timerElement.textContent = "EXPIRED";
                                        updateTimerColors(0);
                                        return;
                                    }

                                    // Calculate remaining time
                                    const minutes = Math.floor((remaining % (1000 * 60 * 60)) / (1000 * 60));
                                    const seconds = Math.floor((remaining % (1000 * 60)) / 1000);

                                    // Display
                                    timerElement.textContent = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;

                                    // Update colors based on remaining time
                                    updateTimerColors(minutes);
                                }

                                // Only start countdown if not already expired
                                if (expiryTimestamp > 0) {
                                    // Initial update
                                    updateCountdown();

                                    // Update every second
                                    const countdownInterval = setInterval(updateCountdown, 1000);

                                    // Cleanup
                                    window.addEventListener('beforeunload', () => {
                                        clearInterval(countdownInterval);
                                    });
                                } else {
                                    // Show expired state immediately
                                    updateTimerColors(0);
                                }
                            });
                        </script>

                        <!-- Display reserved rooms -->
                        <?php if (!empty($reservedRooms)): ?>
                            <div id="reserved-rooms-container" class="space-y-2">
                                <?php foreach ($reservedRooms as $room): ?>
                                    <div class="reserved-room flex flex-col md:flex-row rounded-md shadow-sm border">
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
                                                        USD<?= number_format($room['RoomPrice'], 2) ?>
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

                                            <form class="edit-remove-room-form" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" class="mt-4 flex gap-3">
                                                <input type="hidden" name="reservation_id" value="<?= $reservationID ?>">
                                                <input type="hidden" name="room_id" value="<?= $room['RoomID'] ?>">
                                                <input type="hidden" name="checkin_date" value="<?= $room['CheckInDate'] ?>">
                                                <input type="hidden" name="checkout_date" value="<?= $room['CheckOutDate'] ?>">
                                                <input type="hidden" name="adults" value="<?= $room['Adult'] ?>">
                                                <input type="hidden" name="children" value="<?= $room['Children'] ?>">

                                                <button type="submit" name="edit_room"
                                                    class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                                    Edit Room
                                                </button>

                                                <button type="button" name="remove_room" class="remove-room-btn text-red-600 hover:text-red-800 text-sm font-medium">
                                                    Remove Room
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div id="no-rooms-message" class="text-center py-32" style="display: none;">
                                <p class="text-gray-500">You don't have any rooms reserved yet.</p>
                                <a href="room_booking.php" class="text-blue-600 hover:underline mt-2 inline-block">
                                    Browse available rooms
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-32">
                                <p class="text-gray-500">You don't have any rooms reserved yet.</p>
                                <a href="room_booking.php" class="text-blue-600 hover:underline mt-2 inline-block">
                                    Browse available rooms
                                </a>
                            </div>
                        <?php endif; ?>

                        <form id="reservationForm" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" class="py-6">
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
                                        <input type="radio" name="work_travel" value="1" class="h-4 w-4 text-blue-600" <?= isset($_POST['work_travel']) && $_POST['work_travel'] == 1 ? 'checked' : '' ?>>
                                        <span class="ml-2">Yes</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="work_travel" value="0" class="h-4 w-4 text-blue-600" <?= !isset($_POST['work_travel']) || (isset($_POST['work_travel']) && $_POST['work_travel'] == 0) ? 'checked' : '' ?>>
                                        <span class="ml-2">No</span>
                                    </label>
                                </div>
                            </div>

                            <input type="hidden" name="reservation_id" value="<?= $reservationID ?>">

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                                    <select name="title" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        <option>Mr.</option>
                                        <option>Mrs.</option>
                                        <option>Ms.</option>
                                        <option>Dr.</option>
                                    </select>
                                </div>
                                <div class="relative">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">First name <span class="text-red-500">*</span></label>
                                    <input type="text" name="first_name" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    <small id="firstNameError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none">First name is required</small>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Last name (Optional)</label>
                                    <input type="text" name="last_name" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email address <span class="text-red-500">*</span></label>
                                <input type="email" value="<?= maskEmail($userData['UserEmail']) ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" disabled>
                            </div>

                            <div class="mb-6 relative">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone <span class="text-red-500">*</span></label>
                                <input type="tel" name="phone" value="<?= $userData['UserPhone'] ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <small id="phoneError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none">First name is required</small>
                            </div>

                            <div class="flex justify-between items-center">
                                <a href="../User/room_booking.php?checkin_date=<?= $checkin_date ?>&checkout_date=<?= $checkout_date ?>&adults=<?= $adults ?>&children=<?= $children ?>" class="text-blue-600 hover:text-blue-800 font-medium">Back</a>
                                <button type="submit" id="submitButton" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-md flex items-center justify-center select-none">
                                    <span id="buttonText">Continue to payment</span>
                                    <svg id="buttonSpinner" class="hidden w-5 h-5 ml-2 text-white animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MoveUp Btn -->
    <?php
    include('../includes/moveup_btn.php');
    include('../includes/loader.php');
    include('../includes/alert.php');
    include('../includes/footer.php');
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle payment success/cancel messages
            <?php if (isset($_SESSION['success'])): ?>
                alert('<?= $_SESSION['success'] ?>');
                <?php unset($_SESSION['success']); ?>
            <?php elseif (isset($_SESSION['alert'])): ?>
                alert('<?= $_SESSION['alert'] ?>');
                <?php unset($_SESSION['alert']); ?>
            <?php endif; ?>
        });
    </script>
    <script src="//unpkg.com/alpinejs" defer></script>
    <script type="module" src="../JS/index.js"></script>
</body>

</html>