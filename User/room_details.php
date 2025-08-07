<?php
session_start();
include('../config/db_connection.php');
include('../includes/auto_id_func.php');
include('../includes/timeago_func.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

$userID = (!empty($_SESSION["UserID"]) ? $_SESSION["UserID"] : null);
$username = (!empty($_SESSION["UserName"]) ? $_SESSION["UserName"] : null);
$alertMessage = '';

// Timezone 
date_default_timezone_set('Asia/Yangon');

// Get search parameters from URL with strict validation
if (isset($_GET["roomTypeID"])) {
    $reservation_id = isset($_GET['reservation_id']) ? $_GET['reservation_id'] : '';
    $roomtype_id = $_GET["roomTypeID"];
    $room_id = isset($_GET['room_id']) ? $_GET['room_id'] : '';
    $checkin_date = isset($_GET['checkin_date']) ? htmlspecialchars($_GET['checkin_date']) : '';
    $checkout_date = isset($_GET['checkin_date']) ? htmlspecialchars($_GET['checkout_date']) : '';
    $adults = isset($_GET['adults']) ? (int)$_GET['adults'] : 1;
    $children = isset($_GET['children']) ? (int)$_GET['children'] : 0;
    $edit = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
    $totalGuest = $adults + $children;

    $query = "SELECT * FROM roomtypetb WHERE RoomTypeID = '$roomtype_id'";

    $roomtype = $connect->query($query)->fetch_assoc();

    // Get TOTAL rooms 
    $totalRoomsQuery = "SELECT COUNT(*) as total FROM roomtb WHERE RoomTypeID = '$roomtype_id'";
    $totalRoomsResult = $connect->query($totalRoomsQuery)->fetch_assoc();
    $totalRooms = $totalRoomsResult['total'];

    // Get AVAILABLE rooms 
    $availableRoomsQuery = "SELECT COUNT(*) as available FROM roomtb WHERE RoomTypeID = '$roomtype_id' AND RoomStatus = 'Available'";
    $availableRoomsResult = $connect->query($availableRoomsQuery)->fetch_assoc();
    $availableRooms = $availableRoomsResult['available'];
}

// Reserve room
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reserve_room_id'])) {
    $roomID = $_POST['reserve_room_id'];
    $reservation_id = $_POST['reservation_id'];
    $checkInDate = $_POST['checkin_date'];
    $checkOutDate = $_POST['checkout_date'];
    $adults = $_POST['adults'];
    $children = $_POST['children'];
    $guests = $adults + $children;

    if ($checkInDate == '' || $checkOutDate == '') {
        $_SESSION['alert'] = "Check-in and check-out dates are required.";
        header("Location: room_details.php?roomTypeID=$roomtype_id&room_id=$room_id&checkin_date=$checkin_date&checkout_date=$checkout_date&adults=$adults&children=$children");
        exit();
    }

    if ($userID == null) {
        $_SESSION['alert'] = "Please log in to reserve a room.";
        header("Location: room_details.php?roomTypeID=$roomtype_id&room_id=$room_id&checkin_date=$checkInDate&checkout_date=$checkOutDate&adults=$adults&children=$children");
        exit();
    }

    try {
        // Validate dates
        $today = new DateTime();
        $today->setTime(0, 0, 0);
        $checkIn = new DateTime($checkInDate);

        if ($checkIn <= $today) {
            $_SESSION['alert'] = "Check-in date must be at least tomorrow.";
            header("Location: room_details.php?roomTypeID=$roomtype_id&room_id=$room_id&checkin_date=$checkInDate&checkout_date=$checkOutDate&adults=$adults&children=$children");
            exit();
        }

        $checkOut = new DateTime($checkOutDate);
        if ($checkOut <= $checkIn) {
            $_SESSION['alert'] = "Check-out date must be after check-in date.";
            header("Location: room_details.php?roomTypeID=$roomtype_id&room_id=$room_id&checkin_date=$checkInDate&checkout_date=$checkOutDate&adults=$adults&children=$children");
            exit();
        }

        // First get the room price from roomtypetb
        $priceQuery = "SELECT rt.RoomPrice, rt.RoomCapacity
                      FROM roomtb r
                      JOIN roomtypetb rt ON r.RoomTypeID = rt.RoomTypeID
                      WHERE r.RoomID = ?";
        $stmtPrice = $connect->prepare($priceQuery);
        $stmtPrice->bind_param("s", $roomID);
        $stmtPrice->execute();
        $priceResult = $stmtPrice->get_result();

        if ($priceResult->num_rows == 0) {
            throw new Exception("Room not found or price not available");
        }

        $priceData = $priceResult->fetch_assoc();
        $tax = $priceData['RoomPrice'] * 0.1;
        $price = $priceData['RoomPrice'] + $tax;

        if ($guests) {
            if ($priceData['RoomCapacity'] < $guests) {
                $_SESSION['alert'] = "Room capacity (" . $priceData['RoomCapacity'] . ") is less than the number of guests ($guests).";
                header("Location: room_details.php?roomTypeID=$roomtype_id&room_id=$room_id&checkin_date=$checkInDate&checkout_date=$checkOutDate&adults=$adults&children=$children");
                exit();
            }
        }

        // Check if this specific room is already reserved
        $check = "SELECT COUNT(*) as count FROM reservationdetailtb 
                 WHERE RoomID = ? AND (
                       (? BETWEEN CheckInDate AND CheckOutDate) OR 
                       (? BETWEEN CheckInDate AND CheckOutDate) OR
                       (CheckInDate BETWEEN ? AND ?) OR
                       (CheckOutDate BETWEEN ? AND ?)
                 )";
        $stmtCheck = $connect->prepare($check);
        if (!$stmtCheck) {
            throw new Exception("Prepare failed: " . $connect->error);
        }

        $bindResult = $stmtCheck->bind_param(
            "sssssss",
            $roomID,
            $checkInDate,
            $checkOutDate,
            $checkInDate,
            $checkOutDate,
            $checkInDate,
            $checkOutDate
        );
        if (!$bindResult) {
            throw new Exception("Bind failed: " . $stmtCheck->error);
        }

        $executeResult = $stmtCheck->execute();
        if (!$executeResult) {
            throw new Exception("Execute failed: " . $stmtCheck->error);
        }

        $result = $stmtCheck->get_result();
        $count = $result->fetch_assoc()['count'];

        if ($count == 0) {
            // Check if user has an existing PENDING reservation
            $checkReservation = "SELECT ReservationID FROM reservationtb WHERE UserID = ? AND Status = 'Pending' ORDER BY ReservationDate DESC LIMIT 1";
            $stmtCheckRes = $connect->prepare($checkReservation);
            $stmtCheckRes->bind_param("s", $userID);
            $stmtCheckRes->execute();
            $resResult = $stmtCheckRes->get_result();

            if ($resResult->num_rows > 0) {
                // Use existing pending reservation
                $reservationData = $resResult->fetch_assoc();
                $reservationID = $reservationData['ReservationID'];

                // Extend the expiry time
                $newExpiry = date('Y-m-d H:i:s', strtotime('+30 minutes'));
                $updateExpiry = "UPDATE reservationtb SET ExpiryDate = ? WHERE ReservationID = ?";
                $stmtExpiry = $connect->prepare($updateExpiry);
                $stmtExpiry->bind_param("ss", $newExpiry, $reservationID);
                $stmtExpiry->execute();
            } else {
                // Create new reservation
                $reservationID = uniqid('RSV');
                $expiryDate = date('Y-m-d H:i:s', strtotime('+30 minutes'));

                // Insert into reservationtb
                $reservationQuery = "INSERT INTO reservationtb (ReservationID, UserID, TotalPrice, ExpiryDate, Status) VALUES (?, ?, ?, ?, 'Pending')";
                $stmt = $connect->prepare($reservationQuery);
                $stmt->bind_param("ssds", $reservationID, $userID, $price, $expiryDate);
                $stmt->execute();
            }

            // Insert into reservationdetailtb
            $detailQuery = "INSERT INTO reservationdetailtb (ReservationID, RoomID, CheckInDate, CheckOutDate, Adult, Children, Price) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt2 = $connect->prepare($detailQuery);
            $stmt2->bind_param("ssssssd", $reservationID, $roomID, $checkInDate, $checkOutDate, $adults, $children, $price);
            $stmt2->execute();

            // Update the total price in reservationtb
            $updateTotal = "UPDATE reservationtb SET TotalPrice = (SELECT SUM(Price) FROM reservationdetailtb WHERE ReservationID = ?) WHERE ReservationID = ?";
            $stmtUpdate = $connect->prepare($updateTotal);
            $stmtUpdate->bind_param("ss", $reservationID, $reservationID);
            $stmtUpdate->execute();

            // Update Room Status
            $updateRoomStatus = "UPDATE roomtb SET RoomStatus = 'Reserved' WHERE RoomID = ?";
            $stmtRoomStatus = $connect->prepare($updateRoomStatus);
            $stmtRoomStatus->bind_param("s", $roomID);
            $stmtRoomStatus->execute();

            header("Location: reservation.php?reservation_id=$reservationID&checkin_date=$checkInDate&checkout_date=$checkOutDate&adults=$adults&children=$children");
            exit();
        } else {
            $alertMessage = "Room is already reserved for the selected dates.";
        }
    } catch (Exception $e) {
        echo "Reservation failed: " . $e->getMessage();
    }
}

// Edit reservation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_room_id'])) {
    $roomTypeID = $_POST['roomTypeID'];
    $roomID = $_POST['edit_room_id'];
    $checkInDate = $_POST['checkin_date'];
    $checkOutDate = $_POST['checkout_date'];
    $adults = $_POST['adults'];
    $children = $_POST['children'];
    $guests = $adults + $children;
    $reservationID = $_POST['reservation_id'];

    // Validate dates first
    $today = new DateTime();
    $today->setTime(0, 0, 0);
    $checkIn = new DateTime($checkInDate);
    $checkOut = new DateTime($checkOutDate);

    $encodedParams = "roomTypeID=" . urlencode($roomTypeID) .
        "&reservation_id=" . urlencode($reservationID) .
        "&room_id=" . urlencode($roomID) .
        "&checkin_date=" . urlencode($checkInDate) .
        "&checkout_date=" . urlencode($checkOutDate) .
        "&adults=" . urlencode($adults) .
        "&children=" . urlencode($children) .
        "&edit=1";

    if ($checkIn <= $today) {
        $_SESSION['alert'] = "Check-in date must be in the future.";
        header("Location: room_details.php?$encodedParams");
        exit();
    }

    if ($checkOut <= $checkIn) {
        $_SESSION['alert'] = "Check-out date must be after check-in date.";
        header("Location: room_details.php?$encodedParams");
        exit();
    }

    if ($userID == null) {
        $_SESSION['alert'] = "Please log in to reserve a room.";
        $loginParams = "roomTypeID=" . urlencode($roomTypeID) .
            "&room_id=" . urlencode($roomID) .
            "&checkin_date=" . urlencode($checkInDate) .
            "&checkout_date=" . urlencode($checkOutDate) .
            "&adults=" . urlencode($adults) .
            "&children=" . urlencode($children);
        header("Location: room_details.php?$loginParams");
        exit();
    }

    // Check room capacity
    $checkRoomCapacity = "SELECT r.RoomID, rt.RoomCapacity FROM roomtb r 
                         JOIN roomtypetb rt ON r.RoomTypeID = rt.RoomTypeID
                         WHERE r.RoomID = ?";
    $stmt = $connect->prepare($checkRoomCapacity);
    $stmt->bind_param("s", $roomID);
    $stmt->execute();
    $result = $stmt->get_result();
    $roomData = $result->fetch_assoc();

    if ($guests > $roomData['RoomCapacity']) {
        $_SESSION['alert'] = "Room capacity (" . $roomData['RoomCapacity'] . ") is less than the number of guests ($guests).";
        header("Location: room_details.php?$encodedParams");
        exit();
    }

    // Check for overlapping reservations (excluding current reservation AND allowing same user to edit)
    $checkAvailability = "SELECT COUNT(*) as count FROM reservationdetailtb rd
                     JOIN reservationtb r ON rd.ReservationID = r.ReservationID
                     WHERE rd.RoomID = ? 
                     AND rd.ReservationID != ? 
                     AND r.UserID != ? 
                     AND rd.CheckOutDate > NOW() 
                     AND (
                         (? < rd.CheckOutDate AND ? > rd.CheckInDate) OR
                         (rd.CheckInDate < ? AND rd.CheckOutDate > ?)
                     )";

    $stmtCheck = $connect->prepare($checkAvailability);
    $stmtCheck->bind_param(
        "sssssss",
        $roomID,
        $reservationID,
        $userID,       // Current user's ID to exclude their own bookings
        $checkInDate,
        $checkOutDate,
        $checkOutDate,
        $checkInDate
    );
    $stmtCheck->execute();
    $availabilityResult = $stmtCheck->get_result();
    $count = $availabilityResult->fetch_assoc()['count'];

    if ($count > 0) {
        $_SESSION['alert'] = "The room is not available for the selected dates.";
        header("Location: room_details.php?$encodedParams");
        exit();
    }

    // Calculate number of nights
    $nights = $checkOut->diff($checkIn)->days;

    // Get room price
    $getRoomPrice = "SELECT RoomPrice FROM roomtypetb rt JOIN roomtb r ON rt.RoomTypeID = r.RoomTypeID WHERE r.RoomID = ?";
    $stmtPrice = $connect->prepare($getRoomPrice);
    $stmtPrice->bind_param("s", $roomID);
    $stmtPrice->execute();
    $priceResult = $stmtPrice->get_result();
    $roomPrice = $priceResult->fetch_assoc()['RoomPrice'];

    // Calculate new total price (price per night * number of nights)
    $subtotal = $roomPrice * $nights;
    $tax = $subtotal * 0.10; // 10% tax
    $newTotal = $subtotal + $tax;

    // Begin transaction
    $connect->begin_transaction();

    try {
        // First update the reservation details for the specific room
        $update_room = "UPDATE reservationdetailtb SET 
                CheckInDate = ?, 
                CheckOutDate = ?, 
                Adult = ?, 
                Children = ? 
                WHERE RoomID = ? AND ReservationID = ?";
        $stmt = $connect->prepare($update_room);
        $stmt->bind_param("ssiiss", $checkInDate, $checkOutDate, $adults, $children, $roomID, $reservationID);

        if (!$stmt->execute()) {
            throw new Exception("Failed to update room reservation details: " . $stmt->error);
        }

        $affectedRows = $stmt->affected_rows;
        if ($affectedRows === 0) {
            throw new Exception("No rows were updated. Check if RoomID and ReservationID combination exists.");
        }

        // Recalculate the total price for the entire reservation
        $getAllRooms = "SELECT rd.RoomID, rt.RoomPrice 
                       FROM reservationdetailtb rd
                       JOIN roomtb r ON rd.RoomID = r.RoomID
                       JOIN roomtypetb rt ON r.RoomTypeID = rt.RoomTypeID
                       WHERE rd.ReservationID = ?";
        $stmtAll = $connect->prepare($getAllRooms);
        $stmtAll->bind_param("s", $reservationID);
        $stmtAll->execute();
        $allRoomsResult = $stmtAll->get_result();

        $totalSubtotal = 0;
        while ($room = $allRoomsResult->fetch_assoc()) {
            // For the current room, use the new dates to calculate nights
            if ($room['RoomID'] == $roomID) {
                $roomNights = $nights;
            } else {
                // For other rooms, get their existing dates
                $getRoomDates = "SELECT CheckInDate, CheckOutDate FROM reservationdetailtb WHERE RoomID = ? AND ReservationID = ?";
                $stmtDates = $connect->prepare($getRoomDates);
                $stmtDates->bind_param("ss", $room['RoomID'], $reservationID);
                $stmtDates->execute();
                $datesResult = $stmtDates->get_result();
                $dates = $datesResult->fetch_assoc();
                $roomCheckIn = new DateTime($dates['CheckInDate']);
                $roomCheckOut = new DateTime($dates['CheckOutDate']);
                $roomNights = $roomCheckOut->diff($roomCheckIn)->days;
            }
            $totalSubtotal += $room['RoomPrice'] * $roomNights;
        }

        $totalTax = $totalSubtotal * 0.10;
        $newTotalPrice = $totalSubtotal + $totalTax;

        // Then update the reservation total price and expiry
        $newExpiry = date('Y-m-d H:i:s', strtotime('+30 minutes'));
        $updateExpiry = "UPDATE reservationtb SET 
                        ExpiryDate = ?, 
                        TotalPrice = ? 
                        WHERE ReservationID = ?";
        $stmtExpiry = $connect->prepare($updateExpiry);
        $stmtExpiry->bind_param("sds", $newExpiry, $newTotalPrice, $reservationID);
        $stmtExpiry->execute();

        // Update room status
        $updateRoomStatus = "UPDATE roomtb SET RoomStatus = 'Reserved' WHERE RoomID = ?";
        $stmtRoomStatus = $connect->prepare($updateRoomStatus);
        $stmtRoomStatus->bind_param("s", $roomID);
        $stmtRoomStatus->execute();

        // Commit transaction
        $connect->commit();

        $_SESSION['success'] = "Reservation updated successfully! Total for $nights nights: $" . number_format($newTotalPrice, 2);

        $successParams = "roomID=" . urlencode($roomID) .
            "&checkin_date=" . urlencode($checkInDate) .
            "&checkout_date=" . urlencode($checkOutDate) .
            "&adults=" . urlencode($adults) .
            "&children=" . urlencode($children);

        header("Location: reservation.php?$successParams");
        exit();
    } catch (Exception $e) {
        // Rollback transaction on error
        $connect->rollback();
        $_SESSION['alert'] = "Error updating reservation: " . $e->getMessage();
        header("Location: room_details.php?$encodedParams");
        exit();
    }
}

// Add/Remove room from favorites
if (isset($_POST['room_favourite'])) {
    // Initialize response
    $res = ['status' => '', 'error' => ''];

    if (isset($_SESSION['UserID']) && $_SESSION['UserID']) {
        $userID = $_SESSION['UserID'];
        $roomTypeID = $connect->real_escape_string($_POST['roomTypeID']);
        $checkin_date = isset($_POST['checkin_date']) ? $connect->real_escape_string($_POST['checkin_date']) : '';
        $checkout_date = isset($_POST['checkout_date']) ? $connect->real_escape_string($_POST['checkout_date']) : '';
        $adults = isset($_POST['adults']) ? intval($_POST['adults']) : 1;
        $children = isset($_POST['children']) ? intval($_POST['children']) : 0;

        // Check if already favorited
        $check = $connect->query("SELECT COUNT(*) as count FROM roomtypefavoritetb WHERE UserID = '$userID' AND RoomTypeID = '$roomTypeID'");

        if ($check && $row = $check->fetch_assoc()) {
            if ($row['count'] == 0) {
                // Add to favorites
                $insert = $connect->query("INSERT INTO roomtypefavoritetb (UserID, RoomTypeID, CheckInDate, CheckOutDate, Adult, Children) 
                                          VALUES ('$userID', '$roomTypeID', '$checkin_date', '$checkout_date', '$adults', '$children')");
                if ($insert) {
                    $res['status'] = 'added';
                } else {
                    $res['error'] = 'Insert failed: ' . $connect->error;
                }
            } else {
                // Remove from favorites
                $delete = $connect->query("DELETE FROM roomtypefavoritetb WHERE UserID = '$userID' AND RoomTypeID = '$roomTypeID'");
                if ($delete) {
                    $res['status'] = 'removed';
                } else {
                    $res['error'] = 'Delete failed: ' . $connect->error;
                }
            }
        } else {
            $res['error'] = 'Failed to check favorite status';
        }
    } else {
        $res['status'] = 'not_logged_in';
    }

    header('Content-Type: application/json');
    echo json_encode($res);
    exit();
}

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
    $averageRating = round($totalRating / $totalReviews, 1); // Round to 1 decimal place

    // Function to get rating description
    function getRatingDescription($rating)
    {
        if ($rating >= 4.5) return 'Exceptional';
        if ($rating >= 4.0) return 'Superb';
        if ($rating >= 3.5) return 'Very Good';
        if ($rating >= 3.0) return 'Good';
        if ($rating >= 2.5) return 'Average';
        if ($rating >= 2.0) return 'Below Average';
        return 'Poor';
    }

    // Get the rating description
    $ratingDescription = getRatingDescription($averageRating);
} else {
    $averageRating = 0;
    $ratingDescription = 'No Reviews';
    $totalReviews = 0;
}

// Count Review
$review_count_select = "SELECT COUNT(*) as count FROM roomtypereviewtb WHERE RoomTypeID = '$roomtype[RoomTypeID]'";
$review_count_query = $connect->query($review_count_select);
$review_count_result = $review_count_query->fetch_assoc();
$review_count = $review_count_result['count'];

// Submit Review
if (isset($_POST['submitreview'])) {
    if ($userID) {
        // Get values from POST
        $roomTypeID = $_POST['roomTypeID'];
        $travellerType = mysqli_real_escape_string($connect, $_POST['travellertype']);
        $rating = intval($_POST['rating']);
        $country = mysqli_real_escape_string($connect, $_POST['country']);
        $review = mysqli_real_escape_string($connect, $_POST['reviewtext']);

        // Validate inputs
        if (empty($travellerType) || empty($rating) || empty($country) || empty($review)) {
            $alertMessage = "All fields are required!";
        } else {
            // Insert into database
            $insert = "INSERT INTO roomtypereviewtb (Rating, Country, Comment, TravellerType, UserID, RoomTypeID) 
                      VALUES ('$rating', '$country', '$review', '$travellerType', '$userID', '$roomTypeID')";

            if ($connect->query($insert)) {
                // Redirect back with the same search parameters
                $redirect_url = "room_details.php?roomTypeID=$roomTypeID&checkin_date=$checkin_date&checkout_date=$checkout_date&adults=$adults&children=$children";
                header("Location: $redirect_url");
                exit();
            }
        }
    }
}

// Handle reaction submission
if (isset($_POST['like']) || isset($_POST['dislike'])) {
    // Check if user is logged in
    if (!isset($_SESSION['UserID'])) {
        header("Location: user_signin.php");
        exit();
    }

    // Validate and sanitize input
    $reviewID = filter_input(INPUT_POST, 'review_id', FILTER_VALIDATE_INT);
    $roomTypeID = $connect->real_escape_string($_POST['roomTypeID']);
    $checkin_date = isset($_POST['checkin_date']) ? $connect->real_escape_string($_POST['checkin_date']) : '';
    $checkout_date = isset($_POST['checkout_date']) ? $connect->real_escape_string($_POST['checkout_date']) : '';
    $adults = isset($_POST['adults']) ? intval($_POST['adults']) : 1;
    $children = isset($_POST['children']) ? intval($_POST['children']) : 0;
    $userID = $_SESSION['UserID'];
    $newReactionType = isset($_POST['like']) ? 'like' : 'dislike';

    // Check if user already reacted to this review
    $checkStmt = $connect->prepare("SELECT ReactionType FROM roomtypereviewrttb WHERE ReviewID = ? AND UserID = ?");
    $checkStmt->bind_param("is", $reviewID, $userID);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows > 0) {
        $existingReaction = $result->fetch_assoc()['ReactionType'];

        if ($existingReaction == $newReactionType) {
            // User clicked same reaction - remove it
            $deleteStmt = $connect->prepare("DELETE FROM roomtypereviewrttb WHERE ReviewID = ? AND UserID = ?");
            $deleteStmt->bind_param("is", $reviewID, $userID);
            $deleteStmt->execute();
            $deleteStmt->close();
        } else {
            // User changed reaction - update it
            $updateStmt = $connect->prepare("UPDATE roomtypereviewrttb SET ReactionType = ? WHERE ReviewID = ? AND UserID = ?");
            $updateStmt->bind_param("sis", $newReactionType, $reviewID, $userID);
            $updateStmt->execute();
            $updateStmt->close();
        }
    } else {
        // User hasn't reacted - insert new reaction
        $insertStmt = $connect->prepare("INSERT INTO roomtypereviewrttb (ReviewID, UserID, ReactionType) VALUES (?, ?, ?)");
        $insertStmt->bind_param("iss", $reviewID, $userID, $newReactionType);
        $insertStmt->execute();
        $insertStmt->close();
    }

    $checkStmt->close();

    // Refresh the page to show updated reactions
    $redirect_url = "room_details.php?roomTypeID=$roomTypeID&checkin_date=$checkin_date&checkout_date=$checkout_date&adults=$adults&children=$children";
    header("Location: $redirect_url");
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.5.0/css/flag-icon.min.css" />
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
            <div class="max-w-[1150px] mx-auto px-4 pb-8">
                <!-- Desktop Form (shown on lg screens and up) -->
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get"
                    class="availability-form hidden lg:flex w-full z-10 p-4 bg-white border-b border-gray-100 justify-between items-end space-x-4 relative">
                    <input type="hidden" name="roomTypeID" value="<?= $roomtype['RoomTypeID'] ?>">
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

                    <?php if (isset($_SESSION['alert'])): ?>
                        <div class="absolute -bottom-2 text-sm text-red-500 alert-message">
                            <?= $_SESSION['alert'] ?>
                        </div>
                        <?php unset($_SESSION['alert']); ?>
                    <?php endif; ?>
                </form>

                <!-- Mobile Check-In Button (shown on small screens) -->
                <div id="mobileButtonsWrapper" class="lg:hidden fixed bottom-3 right-3 transform transition-all duration-300 z-20">
                    <button id="mobile-checkin-button" class="bg-blue-900 text-white p-3 rounded-full z-20 shadow-md hover:bg-blue-950 transform transition-all duration-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                </div>

                <!-- Mobile Check-In Slide-Up Form -->
                <div id="mobile-checkin-form" class="lg:hidden fixed bottom-0 left-0 right-0 bg-white p-4 border-t shadow-md z-30 transform translate-y-full transition-transform duration-500">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-blue-900 font-semibold text-lg">Book a Room</h2>
                        <button id="close-mobile-search" class="text-red-500 font-bold text-lg">&times;</button>

                    </div>
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get" class="availability-form flex flex-col space-y-3">
                        <input type="hidden" name="roomTypeID" value="<?= $roomtype['RoomTypeID'] ?>">
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
                    document.addEventListener('DOMContentLoaded', function() {
                        // Handle all availability forms (desktop and mobile)
                        const availabilityForms = document.querySelectorAll('.availability-form');

                        availabilityForms.forEach(function(form) {
                            form.addEventListener('submit', function(e) {
                                e.preventDefault();
                                checkAvailability(form);
                            });
                        });

                        // AJAX function to handle form submission
                        function checkAvailability(form) {
                            const formData = new URLSearchParams(new FormData(form));
                            const alertElements = document.querySelectorAll('.alert-message');
                            const submitButton = form.querySelector('button[type="submit"]');

                            // Clear all alert messages
                            alertElements.forEach(alert => {
                                alert.style.display = 'none';
                                alert.textContent = '';
                            });

                            // For GET requests, append parameters to URL
                            fetch('<?php echo $_SERVER['PHP_SELF']; ?>?' + formData.toString(), {
                                    method: 'GET',
                                    headers: {
                                        'X-Requested-With': 'XMLHttpRequest',
                                        'Accept': 'text/html'
                                    }
                                })
                                .then(response => {
                                    if (!response.ok) {
                                        throw new Error('Network response was not ok');
                                    }
                                    return response.text();
                                })
                                .then(data => {
                                    // Update the URL without reloading
                                    window.history.pushState({}, '', '<?php echo $_SERVER['PHP_SELF']; ?>?' + formData.toString());

                                    // Create a temporary DOM element to parse the response
                                    const parser = new DOMParser();
                                    const doc = parser.parseFromString(data, 'text/html');

                                    // Update the reserve and edit forms with new data from the response
                                    updateFormsFromResponse(doc);

                                    // Update any alert messages
                                    const responseAlert = doc.querySelector('.alert-message');
                                    if (responseAlert && responseAlert.textContent.trim()) {
                                        alertElements.forEach(alert => {
                                            alert.textContent = responseAlert.textContent;
                                            alert.style.display = 'block';
                                        });
                                    }
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    alertElements.forEach(alert => {
                                        alert.textContent = 'An error occurred. Please try again.';
                                        alert.style.display = 'block';
                                    });
                                })
                                .finally(() => {
                                    submitButton.textContent = originalButtonText;
                                    submitButton.disabled = false;
                                });
                        }

                        // Update reserve and edit forms with new data from the response
                        function updateFormsFromResponse(doc) {
                            // Update reserve forms
                            const reserveForms = document.querySelectorAll('form[method="POST"]');
                            const responseReserveForms = doc.querySelectorAll('form[method="POST"]');

                            if (reserveForms.length === responseReserveForms.length) {
                                reserveForms.forEach((form, index) => {
                                    const responseForm = responseReserveForms[index];
                                    const inputs = form.querySelectorAll('input[type="hidden"]');

                                    inputs.forEach(input => {
                                        const responseInput = responseForm.querySelector(`input[name="${input.name}"]`);
                                        if (responseInput) {
                                            input.value = responseInput.value;
                                        }
                                    });
                                });
                            }

                            // Update room status buttons
                            const roomStatusContainers = document.querySelectorAll('.room-status-container');
                            const responseRoomStatusContainers = doc.querySelectorAll('.room-status-container');

                            if (roomStatusContainers.length === responseRoomStatusContainers.length) {
                                roomStatusContainers.forEach((container, index) => {
                                    container.innerHTML = responseRoomStatusContainers[index].innerHTML;
                                });
                            }
                        }

                        // Date validation (optional)
                        const validateDates = function() {
                            const checkinDate = document.getElementById('checkin-date')?.value;
                            const checkoutDate = document.getElementById('checkout-date')?.value;
                            const mobileCheckinDate = document.getElementById('mobile-checkin-date')?.value;
                            const mobileCheckoutDate = document.getElementById('mobile-checkout-date')?.value;

                            if (checkinDate && checkoutDate && new Date(checkoutDate) <= new Date(checkinDate)) {
                                const alertElements = document.querySelectorAll('.alert-message');
                                alertElements.forEach(alert => {
                                    alert.textContent = 'Check-out date must be after check-in date';
                                    alert.style.display = 'block';
                                });
                                return false;
                            }

                            if (mobileCheckinDate && mobileCheckoutDate && new Date(mobileCheckoutDate) <= new Date(mobileCheckinDate)) {
                                const alertElements = document.querySelectorAll('.alert-message');
                                alertElements.forEach(alert => {
                                    alert.textContent = 'Check-out date must be after check-in date';
                                    alert.style.display = 'block';
                                });
                                return false;
                            }

                            return true;
                        };

                        // Add event listeners for date validation
                        document.getElementById('checkin-date')?.addEventListener('change', validateDates);
                        document.getElementById('checkout-date')?.addEventListener('change', validateDates);
                        document.getElementById('mobile-checkin-date')?.addEventListener('change', validateDates);
                        document.getElementById('mobile-checkout-date')?.addEventListener('change', validateDates);
                    });
                </script>

                <script>
                    // Date validation for both desktop and mobile forms
                    document.addEventListener('DOMContentLoaded', () => {
                        // Get tomorrow's date in YYYY-MM-DD format
                        const tomorrow = new Date();
                        tomorrow.setDate(tomorrow.getDate() + 1);
                        const tomorrowStr = tomorrow.toISOString().split('T')[0];

                        // Desktop form elements
                        const checkInDateInput = document.getElementById('checkin-date');
                        const checkOutDateInput = document.getElementById('checkout-date');

                        // Mobile form elements
                        const mobileCheckInInput = document.getElementById('mobile-checkin-date');
                        const mobileCheckOutInput = document.getElementById('mobile-checkout-date');

                        // Set min dates for all date inputs
                        [checkInDateInput, checkOutDateInput, mobileCheckInInput, mobileCheckOutInput].forEach(input => {
                            if (input) input.setAttribute('min', tomorrowStr);
                        });

                        // Update checkout min date when checkin changes (desktop)
                        if (checkInDateInput && checkOutDateInput) {
                            checkInDateInput.addEventListener('change', function() {
                                if (this.value) {
                                    const nextDay = new Date(this.value);
                                    nextDay.setDate(nextDay.getDate() + 1);
                                    const nextDayStr = nextDay.toISOString().split('T')[0];
                                    checkOutDateInput.min = nextDayStr;

                                    if (checkOutDateInput.value && checkOutDateInput.value < nextDayStr) {
                                        checkOutDateInput.value = '';
                                    }
                                }
                            });
                        }

                        // Update checkout min date when checkin changes (mobile)
                        if (mobileCheckInInput && mobileCheckOutInput) {
                            mobileCheckInInput.addEventListener('change', function() {
                                if (this.value) {
                                    const nextDay = new Date(this.value);
                                    nextDay.setDate(nextDay.getDate() + 1);
                                    const nextDayStr = nextDay.toISOString().split('T')[0];
                                    mobileCheckOutInput.min = nextDayStr;

                                    if (mobileCheckOutInput.value && mobileCheckOutInput.value < nextDayStr) {
                                        mobileCheckOutInput.value = '';
                                    }
                                }
                            });
                        }
                    });
                </script>
                <!-- Breadcrumbs -->
                <div class="flex text-sm text-slate-600 my-4">
                    <a href="../User/home_page.php" class="underline">Home</a>
                    <span><i class="ri-arrow-right-s-fill"></i></span>
                    <a href="../User/room_booking.php?checkin_date=<?= $checkin_date ?>&checkout_date=<?= $checkout_date ?>&adults=<?= $adults ?>&children=<?= $children ?>" class="underline">Rooms</a>
                    <span><i class="ri-arrow-right-s-fill"></i></span>
                    <a href="../User/room_details.php?roomTypeID=<?php echo htmlspecialchars($roomtype['RoomTypeID']) ?>&checkin_date=<?= $checkin_date ?>&checkout_date=<?= $checkout_date ?>&adults=<?= $adults ?>&children=<?= $children ?>" class=" underline">Store Details</a>
                </div>

                <!-- Navigation tabs -->
                <div class="flex border-b mb-6">
                    <div class="mr-4 pb-2 border-b-2 border-blue-600 font-medium cursor-pointer">Overview</div>
                    <div class="mr-4 pb-2 text-gray-600 cursor-pointer">Info & prices</div>
                    <div onclick="scrollToFacilities()" class="mr-4 pb-2 text-gray-600 cursor-pointer">Facilities</div>
                    <div onclick="scrollToRule()" class="mr-4 pb-2 text-gray-600 cursor-pointer">House rules</div>
                    <div onclick="scrollToReview()" class="pb-2 text-gray-600 cursor-pointer">Guest reviews (<?= $review_count ?>)</div>
                </div>

                <div class="flex justify-between items-start">
                    <div>
                        <!-- Hotel name and rating -->
                        <div class="mb-2">
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
                            </div>
                            <h1 class="text-xl sm:text-2xl font-bold"><?= htmlspecialchars($roomtype['RoomType']) ?></h1>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <?php
                        // Check if room is favorited
                        $check_favorite = "SELECT COUNT(*) as count FROM roomtypefavoritetb WHERE UserID = '$userID' AND RoomTypeID = '" . $roomtype['RoomTypeID'] . "'";
                        $favorite_result = $connect->query($check_favorite);
                        $is_favorited = $favorite_result->fetch_assoc()['count'] > 0;
                        ?>

                        <form id="favoriteForm" method="post">
                            <input type="hidden" name="checkin_date" value="<?= htmlspecialchars($checkin_date) ?>">
                            <input type="hidden" name="checkout_date" value="<?= htmlspecialchars($checkout_date) ?>">
                            <input type="hidden" name="adults" value="<?= htmlspecialchars($adults) ?>">
                            <input type="hidden" name="children" value="<?= htmlspecialchars($children) ?>">
                            <input type="hidden" name="roomTypeID" value="<?= htmlspecialchars($roomtype['RoomTypeID']) ?>">
                            <input type="hidden" name="room_favourite" value="1">
                            <button type="submit" name="room_favourite" id="favoriteBtn" class="relative group">
                                <i id="heartIcon" class="ri-heart-fill text-2xl cursor-pointer flex items-center justify-center bg-white w-11 h-11 rounded-full hover:bg-slate-100 transition-all duration-500 ease-[cubic-bezier(0.68,-0.6,0.32,1.6)] <?= $is_favorited ? 'text-red-500 hover:text-red-600' : 'text-slate-400 hover:text-red-300' ?>"></i>
                                <span id="heartParticles" class="absolute inset-0 overflow-hidden pointer-events-none"></span>
                            </button>
                        </form>

                        <button
                            onclick="scrollToAvailability()"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-1 px-2 rounded transition-colors select-none">
                            Reserve
                        </button>

                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const favoriteForm = document.getElementById('favoriteForm');
                                const favoriteBtn = document.getElementById('favoriteBtn');
                                const heartIcon = document.getElementById('heartIcon');
                                const heartParticles = document.getElementById('heartParticles');
                                const loginModal = document.getElementById('loginModal');

                                // Array of possible sparkle colors
                                const sparkleColors = [
                                    'bg-amber-500',
                                    'bg-red-500',
                                    'bg-pink-500',
                                    'bg-yellow-400',
                                    'bg-white',
                                    'bg-blue-300'
                                ];

                                if (favoriteForm) {
                                    favoriteForm.addEventListener('submit', function(e) {
                                        e.preventDefault();

                                        const formData = new FormData(this);
                                        const wasFavorited = heartIcon.classList.contains('text-red-500');

                                        // Add loading state with bounce effect
                                        heartIcon.classList.add('animate-bounce');
                                        favoriteBtn.disabled = true;

                                        fetch('../User/room_details.php', {
                                                method: 'POST',
                                                body: formData,
                                                headers: {
                                                    'Accept': 'application/json'
                                                }
                                            })
                                            .then(response => {
                                                if (!response.ok) {
                                                    throw new Error('Network response was not ok');
                                                }
                                                return response.json();
                                            })
                                            .then(data => {
                                                if (data.status === 'added') {
                                                    // Success animation for adding
                                                    heartIcon.classList.remove('text-slate-400', 'hover:text-red-300');
                                                    heartIcon.classList.add('text-red-500', 'hover:text-red-600');
                                                    animateHeartChange(true, wasFavorited);
                                                    createSparkleEffect(); // Add sparkle effect only when adding to favorites
                                                } else if (data.status === 'removed') {
                                                    // Success animation for removing
                                                    heartIcon.classList.remove('text-red-500', 'hover:text-red-600');
                                                    heartIcon.classList.add('text-slate-400', 'hover:text-red-300');
                                                    animateHeartChange(false, wasFavorited);
                                                } else if (data.status === 'not_logged_in') {
                                                    loginModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');

                                                    const darkOverlay2 = document.getElementById('darkOverlay2');
                                                    darkOverlay2.classList.remove('opacity-0', 'invisible');
                                                    darkOverlay2.classList.add('opacity-100');

                                                    const closeLoginModal = document.getElementById('closeLoginModal');
                                                    closeLoginModal.addEventListener('click', function() {
                                                        loginModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
                                                        darkOverlay2.classList.add('opacity-0', 'invisible');
                                                        darkOverlay2.classList.remove('opacity-100');
                                                    })
                                                } else if (data.error) {
                                                    console.error('Error:', data.error);
                                                    // Revert visual state if error occurred
                                                    revertHeartState(wasFavorited);
                                                    alert('An error occurred: ' + data.error);
                                                }
                                            })
                                            .catch(error => {
                                                console.error('Error:', error);
                                                // Revert visual state if error occurred
                                                revertHeartState(wasFavorited);
                                                alert('An error occurred. Please try again.');
                                            })
                                            .finally(() => {
                                                // Remove loading state after animation completes
                                                setTimeout(() => {
                                                    heartIcon.classList.remove('animate-bounce');
                                                    favoriteBtn.disabled = false;
                                                }, 500);
                                            });
                                    });
                                }

                                function animateHeartChange(isNowFavorited, wasFavorited) {
                                    // Skip animation if state didn't actually change (shouldn't happen)
                                    if (isNowFavorited === wasFavorited) return;
                                }

                                function revertHeartState(wasFavorited) {
                                    if (wasFavorited) {
                                        heartIcon.classList.add('text-red-500', 'hover:text-red-600');
                                        heartIcon.classList.remove('text-slate-400', 'hover:text-red-300');
                                    } else {
                                        heartIcon.classList.add('text-slate-400', 'hover:text-red-300');
                                        heartIcon.classList.remove('text-red-500', 'hover:text-red-600');
                                    }
                                }

                                function createSparkleEffect() {
                                    // Clear previous particles
                                    heartParticles.innerHTML = '';

                                    for (let i = 0; i < 5; i++) {
                                        const sparkle = document.createElement('div');
                                        // Get random color from sparkleColors array
                                        const randomColor = sparkleColors[Math.floor(Math.random() * sparkleColors.length)];
                                        sparkle.className = `absolute w-1.5 h-1.5 ${randomColor} rounded-full opacity-0`;
                                        sparkle.style.left = `${30 + Math.random() * 40}%`;
                                        sparkle.style.top = `${30 + Math.random() * 40}%`;

                                        // Animate sparkle with more dynamic movement
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

                                        // Remove sparkle after animation
                                        setTimeout(() => {
                                            sparkle.remove();
                                        }, 1150 + i * 150);
                                    }
                                }
                            });
                        </script>

                        <style>
                            @keyframes bounce {

                                0%,
                                100% {
                                    transform: scale(1);
                                }

                                50% {
                                    transform: scale(1.1);
                                }
                            }

                            .animate-bounce {
                                animation: bounce 0.2s ease-in-out;
                            }
                        </style>
                    </div>
                </div>

                <!-- Address -->
                <p class="text-gray-700 text-sm mb-2">
                    459 Pyay Road, Kamayut Township, 11041 Yangon, Myanmar –
                    <a
                        href="https://www.google.com/maps/place/459+Pyay+Rd,+Yangon"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="text-blue-600 hover:underline cursor-pointer">
                        show map
                    </a>
                </p>

                <div class="flex flex-col lg:flex-row justify-between gap-3">
                    <!-- Swiper.js Styles -->
                    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

                    <!-- Room Images Grid -->
                    <div class="flex flex-col md:flex-row gap-2 w-full">
                        <!-- Cover Image - Made larger -->
                        <div class="w-full md:w-[70%] h-[250px] md:h-[450px] select-none cursor-pointer" onclick="openSwiper(0)">
                            <img src="../Admin/<?= htmlspecialchars($roomtype['RoomCoverImage']) ?>"
                                class="w-full h-full object-cover rounded-lg border border-gray-200">
                        </div>

                        <!-- Additional Images (Up to 3 with +X overlay) - Made larger -->
                        <?php
                        $additionalImagesQuery = "SELECT * FROM roomtypeimagetb WHERE RoomTypeID = '$roomtype_id'";
                        $additionalImagesResult = $connect->query($additionalImagesQuery);

                        if ($additionalImagesResult->num_rows > 0) {
                            $allImages = [];

                            // Add cover image as first in array
                            $allImages[] = ['ImagePath' => $roomtype['RoomCoverImage']];

                            while ($row = $additionalImagesResult->fetch_assoc()) {
                                $allImages[] = $row;
                            }

                            // Output 3 thumbnails
                            $displayImages = array_slice($allImages, 1, 3); // skip first (cover)
                            $extraCount = count($allImages) - 4;

                            echo '<div class="w-full md:w-[30%] grid grid-cols-3 md:grid-cols-1 gap-2 select-none">';
                            foreach ($displayImages as $index => $image) {
                                $imgIndex = $index + 1; // cover is 0
                                echo '<div class="relative cursor-pointer" onclick="openSwiper(' . $imgIndex . ')">';
                                echo '<img src="../Admin/' . htmlspecialchars($image['ImagePath']) . '" class="w-full h-[100px] md:h-[145px] object-cover rounded-lg border border-gray-200">';
                                if ($index === 2 && $extraCount > 0) {
                                    echo '<div class="absolute inset-0 bg-black bg-opacity-40 rounded-lg flex items-center justify-center">';
                                    echo '<span class="text-white text-lg font-semibold">+' . $extraCount . '</span>';
                                    echo '</div>';
                                }
                                echo '</div>';
                            }
                            echo '</div>';
                        }
                        ?>
                    </div>

                    <!-- Swiper Modal -->
                    <div id="swiperModal" class="fixed inset-0 bg-black bg-opacity-75 z-50 hidden">
                        <div class="relative w-full mx-auto">
                            <!-- Close Button -->
                            <button onclick="closeSwiper()" class="absolute top-2 right-2 text-white text-2xl z-50">&times;</button>

                            <!-- Swiper Container -->
                            <div class="swiper mySwiper">
                                <div class="swiper-wrapper select-none">
                                    <?php
                                    foreach ($allImages as $img) {
                                        echo '<div class="swiper-slide">';
                                        echo '<img src="../Admin/' . htmlspecialchars($img['ImagePath']) . '" class="w-full max-h-[700px] object-contain mx-auto rounded-lg">';
                                        echo '</div>';
                                    }
                                    ?>
                                </div>
                                <!-- Navigation buttons -->
                                <div class="swiper-button-next"></div>
                                <div class="swiper-button-prev"></div>
                                <!-- Pagination -->
                                <div class="swiper-pagination"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Swiper.js Script -->
                    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

                    <script>
                        let swiperInstance;

                        function openSwiper(startIndex = 0) {
                            document.getElementById('swiperModal').classList.remove('hidden');
                            document.getElementById('swiperModal').classList.add('flex');
                            swiperInstance = new Swiper(".mySwiper", {
                                initialSlide: startIndex,
                                navigation: {
                                    nextEl: ".swiper-button-next",
                                    prevEl: ".swiper-button-prev",
                                },
                                pagination: {
                                    el: ".swiper-pagination",
                                    clickable: true,
                                },
                                loop: false,
                            });
                        }

                        function closeSwiper() {
                            document.getElementById('swiperModal').classList.add('hidden');
                            document.getElementById('swiperModal').classList.remove('flex');
                            if (swiperInstance) swiperInstance.destroy(true, true);
                        }
                    </script>

                    <!-- Rating and Review Section -->
                    <div class="w-full md:w-[300px] p-0 md:p-5 rounded-lg shadow-sm border border-gray-100">
                        <!-- Rating Summary -->
                        <div class="flex justify-between items-start mb-6">
                            <div class="flex items-start">
                                <div class="flex flex-col items-center mr-3">
                                    <span class="bg-blue-600 text-white px-3 py-1 rounded-md text-sm font-medium select-none"><?= $ratingDescription ?></span>
                                    <p class="text-gray-700 text-sm mt-1">
                                        <?= $totalReviews ?> <?= ($totalReviews > 1) ? 'reviews' : 'review' ?>
                                    </p>
                                </div>
                                <span class="text-2xl font-bold text-gray-900"><?= number_format($averageRating, 1); ?></span>
                            </div>
                        </div>

                        <!-- Guest Highlights -->
                        <div class="mb-6">
                            <p class="text-gray-600 font-bold text-sm mt-1 mb-3">Guests who stayed here loved</p>
                            <div class="flex flex-wrap gap-2">
                                <span class="bg-gray-100 text-gray-500 px-3 py-1 rounded-full text-xs">Comfortable beds</span>
                                <span class="bg-gray-100 text-gray-500 px-3 py-1 rounded-full text-xs">Great location</span>
                                <span class="bg-gray-100 text-gray-500 px-3 py-1 rounded-full text-xs">Friendly staff</span>
                            </div>
                        </div>

                        <!-- User Testimonials -->
                        <div class="space-y-4">
                            <!-- Swiper Container -->
                            <?php
                            if ($totalReviews > 0) {
                            ?>
                                <div class="swiper reviewSwiper">
                                    <div class="swiper-wrapper pb-3">
                                        <?php
                                        $roomReviewSelect = "SELECT rr.*, u.* FROM roomtypereviewtb rr 
                                    JOIN usertb u ON rr.UserID = u.UserID
                                    WHERE RoomTypeID = '$roomtype[RoomTypeID]'
                                    ORDER BY rr.Rating DESC";
                                        $roomReviewResult = $connect->query($roomReviewSelect);
                                        $totalReviews = $roomReviewResult->num_rows;

                                        while ($roomReview = $roomReviewResult->fetch_assoc()) {
                                            // Extract initials
                                            $nameParts = explode(' ', trim($roomReview['UserName']));
                                            $initials = substr($nameParts[0], 0, 1);
                                            if (count($nameParts) > 1) {
                                                $initials .= substr(end($nameParts), 0, 1);
                                            }
                                            $bgColor = $roomReview['ProfileBgColor'];
                                        ?>
                                            <!-- Slide for each review -->
                                            <div class="swiper-slide">
                                                <div class="border-b border-gray-100 pb-4 last:border-0 last:pb-0">
                                                    <div class="flex items-center mb-2">
                                                        <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center mr-3">
                                                            <span class="w-10 h-10 rounded-full bg-[<?= $bgColor ?>] text-white uppercase font-semibold flex items-center justify-center select-none"><?= $initials ?></span>
                                                        </div>
                                                        <div>
                                                            <div class="flex items-center gap-2">
                                                                <h4 class="text-sm font-medium text-gray-800"><?= $roomReview['UserName'] ?></h4>
                                                                <div class="flex items-center gap-2">
                                                                    <!-- Country Flag -->
                                                                    <span class="text-xs flag-icon flag-icon-<?= strtolower($roomReview['Country']) ?> rounded-sm shadow-sm"></span>

                                                                    <!-- Country Name (Fetched via API) -->
                                                                    <span
                                                                        class="text-xs text-gray-600 country-name"
                                                                        data-country-code="<?= $roomReview['Country'] ?>">
                                                                        Loading...
                                                                    </span>
                                                                </div>
                                                            </div>

                                                            <script>
                                                                // Fetch country names from CountriesNow API
                                                                document.querySelectorAll('.country-name').forEach(el => {
                                                                    const countryCode = el.getAttribute('data-country-code');

                                                                    // First try the CountriesNow API
                                                                    fetch('https://countriesnow.space/api/v0.1/countries/info?returns=name,iso2')
                                                                        .then(response => {
                                                                            if (!response.ok) throw new Error('API request failed');
                                                                            return response.json();
                                                                        })
                                                                        .then(data => {
                                                                            if (data.error) throw new Error(data.msg);

                                                                            // Find the country in the response
                                                                            const country = data.data.find(c => c.iso2 === countryCode);
                                                                            el.textContent = country?.name || countryCode;
                                                                        })
                                                                        .catch(() => {
                                                                            // Fallback to local country names if API fails
                                                                            const localCountryNames = {
                                                                                'MM': 'Myanmar',
                                                                                'US': 'United States',
                                                                                'GB': 'United Kingdom',
                                                                                // Add more country codes and names as needed
                                                                            };
                                                                            el.textContent = localCountryNames[countryCode] || countryCode;
                                                                        });
                                                                });
                                                            </script>
                                                            <div class="flex items-center">
                                                                <div class="flex items-center gap-3 mb-4">
                                                                    <div class="select-none space-x-1 cursor-pointer text-sm">
                                                                        <?php
                                                                        $fullStars = floor($roomReview['Rating']);
                                                                        $emptyStars = 5 - $fullStars;
                                                                        for ($i = 0; $i < $fullStars; $i++) {
                                                                            echo '<i class="ri-star-fill text-amber-500"></i>';
                                                                        }
                                                                        for ($i = 0; $i < $emptyStars; $i++) {
                                                                            echo '<i class="ri-star-line text-amber-500"></i>';
                                                                        }
                                                                        ?>
                                                                    </div>
                                                                    <span class="text-gray-500 text-xs"><?= timeAgo($roomReview['AddedDate']) ?></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <p class="text-gray-700 text-xs">
                                                        "<?php
                                                            $review = $roomReview['Comment'] ?? '';
                                                            $truncated = mb_strimwidth(htmlspecialchars($review), 0, 250, '...');
                                                            echo $truncated;
                                                            ?>"
                                                    </p>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    <!-- Add pagination if needed -->
                                    <div class="swiper-pagination"></div>
                                </div>
                            <?php
                            } else {
                            ?>
                                <p class="text-gray-400 text-xs text-center py-20">No reviews yet.</p>
                            <?php
                            }
                            ?>

                            <!-- View All Button -->
                            <button onclick="scrollToReview()" class="w-full mt-4 text-center text-blue-600 hover:text-blue-800 text-sm font-medium">
                                View all <?= $totalReviews ?> <?= ($totalReviews > 1) ? 'reviews' : 'review' ?> →
                            </button>
                        </div>

                        <!-- Add Swiper JS and CSS -->
                        <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
                        <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>

                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                new Swiper('.reviewSwiper', {
                                    slidesPerView: 1,
                                    spaceBetween: 20,
                                    grabCursor: true,
                                    pagination: {
                                        el: '.swiper-pagination',
                                        clickable: true,
                                    },
                                });
                            });
                        </script>

                        <style>
                            .swiper {
                                width: 100%;
                                height: 100%;
                            }

                            .swiper-slide {
                                padding-bottom: 20px;
                                /* Space for pagination */
                            }

                            .swiper-pagination-bullet {
                                background: #3b82f6;
                                /* Blue color matching your theme */
                            }
                        </style>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row items-start gap-0 md:gap-5 my-8">
                    <!-- About section -->
                    <div class="w-full md:w-[65%]">
                        <h2 class="text-xl font-bold mb-2">About this room</h2>
                        <p class="text-gray-700 text-sm mb-3">
                            <?= nl2br(htmlspecialchars($roomtype['RoomDescription'])) ?>
                        </p>
                        <p class="text-sm text-gray-500 mt-2">
                            Distance in property description is calculated using © OpenStreetMap
                        </p>
                    </div>

                    <!-- Property highlights -->
                    <div class="bg-blue-50 flex-1 p-3 w-full">
                        <h2 class="text-lg font-bold text-slate-600 mb-2">Property highlights</h2>
                        <p class="mb-2 text-sm text-gray-500 flex items-center">
                            <i class="ri-map-pin-line text-xl"></i>
                            Top location: Highly rated by recent guests (<?= number_format($averageRating, 1); ?>)
                        </p>
                        <p class="mb-2 text-sm text-gray-500">
                            <strong class="text-slate-600">Breakfast info</strong><br>
                            Vegetarian, Gluten-free, Asian
                        </p>
                        <p class="text-sm text-gray-500 flex items-center">
                            <i class="ri-parking-box-line text-xl"></i>
                            Free parking available at the hotel
                        </p>
                    </div>
                </div>

                <!-- Facilities section -->
                <div id="facilities-section" class="mb-8">
                    <h2 class="text-xl font-bold mb-2">Most popular facilities</h2>
                    <div class="flex flex-wrap gap-2 select-none mt-6">
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

                <!-- Availability section -->
                <div id="availability-section" class="border-t pt-6 mb-6">
                    <h2 class="text-xl font-bold mb-4">
                        Availability (<?= $availableRooms ?> of <?= $totalRooms ?> left)
                    </h2>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room Name</th>
                                    <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Availability</th>
                                    <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php
                                $roomSelect = "SELECT r.*, rt.RoomType
                                FROM roomtb r 
                                JOIN roomtypetb rt ON r.RoomTypeID = rt.RoomTypeID 
                                WHERE r.RoomTypeID = '" . $roomtype['RoomTypeID'] . "'
                                ORDER BY r.RoomStatus = 'Available' DESC";
                                $roomSelectResult = $connect->query($roomSelect);

                                if ($roomSelectResult->num_rows > 0) {
                                    while ($room = $roomSelectResult->fetch_assoc()) {
                                ?>
                                        <tr class="<?= ($room['RoomStatus'] == 'Available' || $room['RoomStatus'] == 'Edit') ? '' : 'opacity-50'; ?> text-base sm:text-sm">
                                            <td class="px-3 md:px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($room['RoomName']) ?> (<?= htmlspecialchars($room['RoomType']) ?>)</td>
                                            <td class="px-3 md:px-6 py-4 whitespace-nowrap text-gray-600"><?= htmlspecialchars($room['RoomStatus']) ?></td>
                                            <td class="px-3 md:px-6 py-4 whitespace-nowrap">
                                                <div class="room-status-container">
                                                    <?php if ($room['RoomStatus'] == 'Available') : ?>
                                                        <form method="POST" style="display:inline;">
                                                            <input type="hidden" name="reservation_id" value="<?= $reservation_id ?>">
                                                            <input type="hidden" name="reserve_room_id" value="<?= htmlspecialchars($room['RoomID']) ?>">
                                                            <input type="hidden" name="checkin_date" value="<?= $checkin_date ?>">
                                                            <input type="hidden" name="checkout_date" value="<?= $checkout_date ?>">
                                                            <input type="hidden" name="adults" value="<?= $adults ?>">
                                                            <input type="hidden" name="children" value="<?= $children ?>">
                                                            <button
                                                                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-1 px-3 rounded text-sm select-none">
                                                                Reserve
                                                            </button>
                                                        </form>
                                                    <?php elseif ($room['RoomStatus'] == 'Edit') : ?>
                                                        <form method="POST" style="display:inline;">
                                                            <input type="hidden" name="edit_room_id" value="<?= htmlspecialchars($room['RoomID']) ?>">
                                                            <input type="hidden" name="checkin_date" value="<?= $checkin_date ?>">
                                                            <input type="hidden" name="checkout_date" value="<?= $checkout_date ?>">
                                                            <input type="hidden" name="adults" value="<?= $adults ?>">
                                                            <input type="hidden" name="children" value="<?= $children ?>">
                                                            <!-- You should also include the ReservationID if you have it -->
                                                            <input type="hidden" name="reservation_id" value="<?= $reservation_id ?>">
                                                            <input type="hidden" name="roomTypeID" value="<?= $roomtype['RoomTypeID'] ?>">
                                                            <button class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-1 px-3 rounded text-sm select-none">
                                                                Edit
                                                            </button>
                                                        </form>
                                                    <?php else : ?>
                                                        <button
                                                            class="bg-gray-400 text-white font-semibold py-1 px-3 rounded text-sm select-none cursor-not-allowed"
                                                            disabled>
                                                            Reserved
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                <?php
                                    }
                                } else {
                                    echo '<tr><td colspan="3" class="px-6 py-4 text-center text-gray-500">No rooms found</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- User Testimonials -->
                <div id="review-section" class="space-y-4 mb-8">
                    <h2 class="text-xl font-bold text-gray-800 mb-3">Guests who stayed here loved</h2>

                    <!-- Swiper Container (replaces grid layout) -->
                    <?php
                    if ($totalReviews > 0) {
                    ?>
                        <div class="swiper review-swiper">
                            <div class="swiper-wrapper">
                                <?php
                                $roomReviewSelect = "SELECT rr.*, u.* FROM roomtypereviewtb rr 
                            JOIN usertb u ON rr.UserID = u.UserID
                            WHERE RoomTypeID = '$roomtype[RoomTypeID]'
                            ORDER BY AddedDate DESC";
                                $roomReviewResult = $connect->query($roomReviewSelect);
                                $totalReviews = $roomReviewResult->num_rows;

                                while ($roomReview = $roomReviewResult->fetch_assoc()) {
                                    // Extract initials
                                    $nameParts = explode(' ', trim($roomReview['UserName']));
                                    $initials = substr($nameParts[0], 0, 1);
                                    if (count($nameParts) > 1) {
                                        $initials .= substr(end($nameParts), 0, 1);
                                    }
                                    $bgColor = $roomReview['ProfileBgColor'];
                                ?>
                                    <!-- Review Card as Swiper Slide -->
                                    <div class="swiper-slide">
                                        <div class="border border-gray-200 rounded-lg p-4 h-full">
                                            <div class="flex items-center mb-2">
                                                <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center mr-3">
                                                    <span class="w-10 h-10 rounded-full bg-[<?= $bgColor ?>] text-white uppercase font-semibold flex items-center justify-center select-none"><?= $initials ?></span>
                                                </div>
                                                <div>
                                                    <div class="flex items-center gap-2">
                                                        <h4 class="text-sm font-medium text-gray-800"><?= $roomReview['UserName'] ?></h4>
                                                        <div class="flex items-center gap-2">
                                                            <!-- Country Flag -->
                                                            <span class="text-xs flag-icon flag-icon-<?= strtolower($roomReview['Country']) ?> rounded-sm shadow-sm"></span>
                                                            <!-- Country Name -->
                                                            <span class="text-xs text-gray-600 country-name" data-country-code="<?= $roomReview['Country'] ?>">
                                                                Loading...
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <div class="flex items-center gap-3 mb-4">
                                                            <div class="select-none space-x-1 cursor-pointer text-sm">
                                                                <?php
                                                                $fullStars = floor($roomReview['Rating']);
                                                                $emptyStars = 5 - $fullStars;
                                                                for ($i = 0; $i < $fullStars; $i++) {
                                                                    echo '<i class="ri-star-fill text-amber-500"></i>';
                                                                }
                                                                for ($i = 0; $i < $emptyStars; $i++) {
                                                                    echo '<i class="ri-star-line text-amber-500"></i>';
                                                                }
                                                                ?>
                                                            </div>
                                                            <span class="text-gray-500 text-xs"><?= timeAgo($roomReview['AddedDate']) ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <p class="text-gray-700 text-xs">
                                                "<?php
                                                    $review = $roomReview['Comment'] ?? '';
                                                    $truncated = mb_strimwidth(htmlspecialchars($review), 0, 250, '...');
                                                    echo $truncated;
                                                    ?>"
                                            </p>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>

                            <!-- Add Navigation Arrows -->
                            <div class="swiper-button-next right-0 top-[40%]"></div>
                            <div class="swiper-button-prev left-0 top-[40%]"></div>
                        </div>
                    <?php
                    } else {
                    ?>
                        <p class="text-base text-gray-400 text-center py-20">No reviews yet.</p>
                    <?php
                    }
                    ?>

                    <!-- View All Button -->
                    <button id="viewAllReviews" class="w-full text-start text-blue-600 hover:text-blue-800 text-sm font-medium">
                        Read all reviews
                    </button>
                </div>

                <!-- Add Swiper JS and CSS -->
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
                <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

                <style>
                    .review-swiper {
                        /* Space for navigation arrows */
                        position: relative;
                    }

                    .review-swiper {
                        width: 100%;
                    }

                    .swiper-slide {

                        height: auto;
                    }

                    .swiper-button-next,
                    .swiper-button-prev {
                        color: #d97706;
                        /* Amber-500 color */
                        top: 40%;
                        /* Center vertically */
                    }
                </style>

                <script>
                    // Initialize Swiper
                    document.addEventListener('DOMContentLoaded', function() {
                        const swiper = new Swiper('.review-swiper', {
                            slidesPerView: 3,
                            spaceBetween: 16,
                            navigation: {
                                nextEl: '.swiper-button-next',
                                prevEl: '.swiper-button-prev',
                            },
                        });

                        // Local country name mapping as fallback
                        const countryNameMap = {
                            'MM': 'Myanmar',
                            'US': 'United States',
                            'GB': 'United Kingdom',
                            'CA': 'Canada',
                            'AU': 'Australia',
                            'JP': 'Japan',
                            'KR': 'South Korea',
                            'CN': 'China',
                            'IN': 'India',
                            'DE': 'Germany',
                            'FR': 'France',
                            'IT': 'Italy',
                            'ES': 'Spain',
                            'BR': 'Brazil',
                            'MX': 'Mexico',
                            'RU': 'Russia'
                        };

                        document.querySelectorAll('.country-name').forEach(el => {
                            const countryCode = el.getAttribute('data-country-code');

                            // First try to get from local map
                            if (countryNameMap[countryCode]) {
                                el.textContent = countryNameMap[countryCode];
                                return;
                            }

                            // Fallback to API if not in local map
                            fetch(`https://country.io/names.json`)
                                .then(response => {
                                    if (!response.ok) throw new Error('API request failed');
                                    return response.json();
                                })
                                .then(data => {
                                    el.textContent = data[countryCode] || countryCode;
                                })
                                .catch(() => {
                                    el.textContent = countryCode; // Final fallback
                                });
                        });
                    });
                </script>

                <div id="facilities-section" class="space-y-6 mb-8">
                    <h2 class="text-xl font-bold text-gray-800 mb-6">Facilities of Opulence Haven</h2>

                    <div class="flex flex-wrap gap-6">
                        <?php
                        // Define all facility categories with their display titles and icons
                        $categories = [
                            'Stay' => ['title' => 'Great for your stay', 'icon' => true],
                            'Bathroom' => ['title' => 'Bathroom', 'icon' => false],
                            'Bedroom' => ['title' => 'Bedroom', 'icon' => false],
                            'Outdoors' => ['title' => 'Outdoors', 'icon' => false],
                            'Activities' => ['title' => 'Activities', 'icon' => false],
                            'Living Area' => ['title' => 'Living Area', 'icon' => true],
                            'Media & Technology' => ['title' => 'Media & Technology', 'icon' => true],
                            'Food & Drink' => ['title' => 'Food & Drink', 'icon' => false],
                            'Reception services' => ['title' => 'Reception services', 'icon' => false],
                            'Cleaning services' => ['title' => 'Cleaning services', 'icon' => false],
                            'Safety & security' => ['title' => 'Safety & security', 'icon' => false],
                            'General' => ['title' => 'General', 'icon' => false]
                        ];

                        foreach ($categories as $type => $info) {
                            // Check if there are facilities for this category
                            $checkQuery = "SELECT COUNT(*) as count FROM facilitytb 
                          WHERE FacilityTypeID = (SELECT FacilityTypeID FROM facilitytypetb WHERE FacilityType = ?)";
                            $stmt = $connect->prepare($checkQuery);
                            $stmt->bind_param("s", $type);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $row = $result->fetch_assoc();

                            if ($row['count'] > 0) {
                                // Get icon for this category if available
                                $icon = '';
                                if ($info['icon']) {
                                    $iconQuery = "SELECT FacilityTypeIcon FROM facilitytypetb WHERE FacilityType = ?";
                                    $iconStmt = $connect->prepare($iconQuery);
                                    $iconStmt->bind_param("s", $type);
                                    $iconStmt->execute();
                                    $iconResult = $iconStmt->get_result();
                                    $iconData = $iconResult->fetch_assoc();
                                    $icon = $iconData['FacilityTypeIcon'] ?? '';
                                }

                                echo '<div class="space-y-4 min-w-[200px] max-w-[300px] flex-1">';

                                // Display title with icon if available
                                if ($icon) {
                                    echo '<div class="flex items-center gap-1">';
                                    echo '<i class="' . htmlspecialchars($icon) . ' text-xl leading-none"></i>';
                                    echo '<h3 class="text-md font-semibold text-gray-700">' . htmlspecialchars($info['title']) . '</h3>';
                                    echo '</div>';
                                } else {
                                    echo '<h3 class="text-md font-semibold text-gray-700">' . htmlspecialchars($info['title']) . '</h3>';
                                }

                                echo '<div class="grid grid-cols-1 gap-3">';

                                // Get and display all facilities for this category
                                $facilityQuery = "SELECT * FROM facilitytb 
                                 WHERE FacilityTypeID = (SELECT FacilityTypeID FROM facilitytypetb WHERE FacilityType = ?)
                                 ORDER BY Facility";
                                $facilityStmt = $connect->prepare($facilityQuery);
                                $facilityStmt->bind_param("s", $type);
                                $facilityStmt->execute();
                                $facilityResult = $facilityStmt->get_result();

                                while ($facility = $facilityResult->fetch_assoc()) {
                                    echo '<div class="flex items-center gap-2">';
                                    echo '<i class="ri-checkbox-circle-line text-base text-green-500 leading-none"></i>';
                                    echo '<span class="text-gray-700 text-sm leading-none">' . htmlspecialchars($facility['Facility']) . '</span>';

                                    // Check if this facility has additional charge
                                    if (strpos($facility['Facility'], 'Additional charge') !== false) {
                                        echo '<span class="text-xs text-gray-500 ml-1">(Additional charge)</span>';
                                    }

                                    echo '</div>';
                                }

                                echo '</div></div>';
                            }
                        }

                        // Special cases for Internet and Parking which might be text descriptions
                        $textFacilities = ['Internet', 'Parking'];
                        foreach ($textFacilities as $facility) {
                            $checkQuery = "SELECT COUNT(*) as count FROM facilitytb 
                          WHERE FacilityTypeID = (SELECT FacilityTypeID FROM facilitytypetb WHERE FacilityType = ?)";
                            $stmt = $connect->prepare($checkQuery);
                            $stmt->bind_param("s", $facility);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $row = $result->fetch_assoc();

                            if ($row['count'] > 0) {
                                echo '<div class="space-y-4 min-w-[200px] max-w-[300px] flex-1">';
                                echo '<h3 class="text-md font-semibold text-gray-700">' . htmlspecialchars($facility) . '</h3>';

                                $descQuery = "SELECT Facility FROM facilitytb 
                             WHERE FacilityTypeID = (SELECT FacilityTypeID FROM facilitytypetb WHERE FacilityType = ?)
                             LIMIT 1";
                                $descStmt = $connect->prepare($descQuery);
                                $descStmt->bind_param("s", $facility);
                                $descStmt->execute();
                                $descResult = $descStmt->get_result();
                                $desc = $descResult->fetch_assoc();

                                echo '<p class="text-gray-700 text-sm">' . htmlspecialchars($desc['Facility']) . '</p>';
                                echo '</div>';
                            }
                        }
                        ?>
                    </div>
                </div>

                <!-- House rules -->
                <div id="rule-section" class="border-t pt-6">
                    <div class="flex items-start">
                        <div>
                            <h2 class="text-xl font-bold text-gray-800 mb-3">House Rules</h2>
                            <p class="text-gray-600 text-sm mb-4">The Opulence Haven Hotel welcomes special requests to make your stay perfect</p>

                            <ul class="space-y-4">
                                <?php
                                $ruleSelect = "SELECT * FROM ruletb ORDER BY RuleID DESC";
                                $ruleSelectResult = $connect->query($ruleSelect);

                                if ($ruleSelectResult->num_rows > 0) {
                                    while ($rule = $ruleSelectResult->fetch_assoc()) {
                                ?>
                                        <li class="flex gap-6">
                                            <!-- Icon Column - Perfectly centered vertically -->
                                            <div class="flex items-center justify-center h-full w-8">
                                                <i class="<?= htmlspecialchars($rule['RuleIcon']) ?> <?= htmlspecialchars($rule['IconSize']) ?> text-gray-500"></i>
                                            </div>

                                            <!-- Text Column -->
                                            <div>
                                                <h3 class="font-medium text-gray-800 mb-1"><?= htmlspecialchars($rule['RuleTitle']) ?></h3>
                                                <p class="text-gray-600 text-sm leading-relaxed">
                                                    <?= nl2br(htmlspecialchars($rule['Rule'])) ?>
                                                </p>
                                            </div>
                                        </li>
                                <?php
                                    }
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- MoveUp Btn -->
    <?php
    include('../includes/moveup_btn.php');
    include('../includes/alert.php');
    include('../includes/user_room_review.php');
    include('../includes/footer.php');
    ?>

    <script>
        // Initialize tab functionality
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.tab-item');
            const activeIndicator = document.getElementById('active-indicator');

            // Set initial position (adjust based on your default active tab)
            const activeTab = document.querySelector('.tab-item.text-blue-600');
            if (activeTab) {
                updateIndicatorPosition(activeTab);
            }

            // Add click handlers for all tabs
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // Update active styles
                    tabs.forEach(t => t.classList.remove('text-blue-600', 'border-b-2', 'border-blue-600'));
                    tabs.forEach(t => t.classList.add('text-gray-600'));
                    this.classList.remove('text-gray-600');
                    this.classList.add('text-blue-600');

                    // Move the indicator
                    updateIndicatorPosition(this);
                });
            });

            // Update indicator position function
            function updateIndicatorPosition(activeTab) {
                const tabRect = activeTab.getBoundingClientRect();
                const containerRect = activeTab.parentElement.getBoundingClientRect();

                activeIndicator.style.width = `${tabRect.width}px`;
                activeIndicator.style.left = `${tabRect.left - containerRect.left}px`;
            }

            // Handle window resize
            window.addEventListener('resize', function() {
                const activeTab = document.querySelector('.tab-item.text-blue-600');
                if (activeTab) {
                    updateIndicatorPosition(activeTab);
                }
            });
        });

        // Keep your existing scroll functions exactly as they are
        function scrollToFacilities() {
            const section = document.getElementById('facilities-section');
            const sectionRect = section.getBoundingClientRect();
            const sectionMiddle = sectionRect.top + window.scrollY + (sectionRect.height / 2) - (window.innerHeight / 2);

            window.scrollTo({
                top: sectionMiddle,
                behavior: 'smooth'
            });
        }

        function scrollToAvailability() {
            const section = document.getElementById('availability-section');
            const sectionRect = section.getBoundingClientRect();
            const sectionMiddle = sectionRect.top + window.scrollY + (sectionRect.height / 2) - (window.innerHeight / 2);

            window.scrollTo({
                top: sectionMiddle,
                behavior: 'smooth'
            });
        }

        function scrollToRule() {
            const section = document.getElementById('rule-section');
            const sectionRect = section.getBoundingClientRect();
            const sectionMiddle = sectionRect.top + window.scrollY + (sectionRect.height / 2) - (window.innerHeight / 2);

            window.scrollTo({
                top: sectionMiddle,
                behavior: 'smooth'
            });
        }

        function scrollToReview() {
            const section = document.getElementById('review-section');
            const sectionRect = section.getBoundingClientRect();
            const sectionMiddle = sectionRect.top + window.scrollY + (sectionRect.height / 2) - (window.innerHeight / 2);

            window.scrollTo({
                top: sectionMiddle,
                behavior: 'smooth'
            });
        }

        function closeModal() {
            document.getElementById('loginModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('loginModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>

    <script src="//unpkg.com/alpinejs" defer></script>
    <script type="module" src="../JS/index.js"></script>
</body>

</html>