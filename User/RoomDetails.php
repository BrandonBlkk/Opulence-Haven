<?php
session_start();
include('../config/dbConnection.php');
include('../includes/AutoIDFunc.php');

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
        header("Location: RoomDetails.php?roomTypeID=$roomtype_id&room_id=$room_id&checkin_date=$checkin_date&checkout_date=$checkout_date&adults=$adults&children=$children");
        exit();
    }

    if ($userID == null) {
        $_SESSION['alert'] = "Please log in to reserve a room.";
        header("Location: RoomDetails.php?roomTypeID=$roomtype_id&room_id=$room_id&checkin_date=$checkInDate&checkout_date=$checkOutDate&adults=$adults&children=$children");
        exit();
    }

    try {
        // Validate dates
        $today = new DateTime();
        $today->setTime(0, 0, 0);
        $checkIn = new DateTime($checkInDate);

        if ($checkIn <= $today) {
            $_SESSION['alert'] = "Check-in date must be at least tomorrow.";
            header("Location: RoomDetails.php?roomTypeID=$roomtype_id&room_id=$room_id&checkin_date=$checkInDate&checkout_date=$checkOutDate&adults=$adults&children=$children");
            exit();
        }

        $checkOut = new DateTime($checkOutDate);
        if ($checkOut <= $checkIn) {
            $_SESSION['alert'] = "Check-out date must be after check-in date.";
            header("Location: RoomDetails.php?roomTypeID=$roomtype_id&room_id=$room_id&checkin_date=$checkInDate&checkout_date=$checkOutDate&adults=$adults&children=$children");
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
                header("Location: RoomDetails.php?roomTypeID=$roomtype_id&room_id=$room_id&checkin_date=$checkInDate&checkout_date=$checkOutDate&adults=$adults&children=$children");
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

            header("Location: Reservation.php?reservation_id=$reservationID&checkin_date=$checkInDate&checkout_date=$checkOutDate&adults=$adults&children=$children");
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

    if ($checkIn <= $today) {
        $_SESSION['alert'] = "Check-in date must be in the future.";
        $redirect_url = "RoomDetails.php?roomTypeID=$roomTypeID&reservation_id=$reservationID&room_id=$roomID&checkin_date=$checkInDate&checkout_date=$checkOutDate&adults=$adults&children=$children&edit=1";
        header("Location: $redirect_url");
        exit();
    }

    if ($checkOut <= $checkIn) {
        $_SESSION['alert'] = "Check-out date must be after check-in date.";
        $redirect_url = "RoomDetails.php?roomTypeID=$roomTypeID&reservation_id=$reservationID&room_id=$roomID&checkin_date=$checkInDate&checkout_date=$checkOutDate&adults=$adults&children=$children&edit=1";
        header("Location: $redirect_url");
        exit();
    }

    if ($userID == null) {
        $_SESSION['alert'] = "Please log in to reserve a room.";
        header("Location: RoomDetails.php?roomTypeID=$roomtype_id&room_id=$room_id&checkin_date=$checkInDate&checkout_date=$checkOutDate&adults=$adults&children=$children");
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
        $redirect_url = "RoomDetails.php?roomTypeID=$roomTypeID&reservation_id=$reservationID&room_id=$roomID&checkin_date=$checkInDate&checkout_date=$checkOutDate&adults=$adults&children=$children&edit=1";
        header("Location: $redirect_url");
        exit();
    }

    // Check for overlapping reservations (excluding current reservation)
    $checkAvailability = "SELECT COUNT(*) as count FROM reservationdetailtb 
                         WHERE RoomID = ? AND ReservationID != ? AND (
                               (? BETWEEN CheckInDate AND CheckOutDate) OR 
                               (? BETWEEN CheckInDate AND CheckOutDate) OR
                               (CheckInDate BETWEEN ? AND ?) OR
                               (CheckOutDate BETWEEN ? AND ?)
                         )";
    $stmtCheck = $connect->prepare($checkAvailability);
    $stmtCheck->bind_param(
        "ssssssss",
        $roomID,
        $reservationID,
        $checkInDate,
        $checkOutDate,
        $checkInDate,
        $checkOutDate,
        $checkInDate,
        $checkOutDate
    );
    $stmtCheck->execute();
    $availabilityResult = $stmtCheck->get_result();
    $count = $availabilityResult->fetch_assoc()['count'];

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
        // First update the reservation details
        $update_room = "UPDATE reservationdetailtb SET 
                        CheckInDate = ?, 
                        CheckOutDate = ?, 
                        Adult = ?, 
                        Children = ? 
                        WHERE RoomID = ? AND ReservationID = ?";
        $stmt = $connect->prepare($update_room);
        $stmt->bind_param("ssiiii", $checkInDate, $checkOutDate, $adults, $children, $roomID, $reservationID);
        $stmt->execute();

        // Then update the reservation total price and expiry
        $newExpiry = date('Y-m-d H:i:s', strtotime('+30 minutes'));
        $updateExpiry = "UPDATE reservationtb SET 
                        ExpiryDate = ?, 
                        TotalPrice = ? 
                        WHERE ReservationID = ?";
        $stmtExpiry = $connect->prepare($updateExpiry);
        $stmtExpiry->bind_param("sds", $newExpiry, $newTotal, $reservationID);
        $stmtExpiry->execute();

        // Commit transaction
        $connect->commit();

        $_SESSION['success'] = "Reservation updated successfully! Total for $nights nights: $" . number_format($newTotal, 2);
        $redirect_url = "Reservation.php?roomID=$roomID&checkin_date=$checkInDate&checkout_date=$checkOutDate&adults=$adults&children=$children";
        header("Location: $redirect_url");
        exit();
    } catch (Exception $e) {
        // Rollback transaction on error
        $connect->rollback();
        $_SESSION['alert'] = "Error updating reservation: " . $e->getMessage();
        $redirect_url = "RoomDetails.php?roomTypeID=$roomTypeID&reservation_id=$reservationID&room_id=$roomID&checkin_date=$checkInDate&checkout_date=$checkOutDate&adults=$adults&children=$children&edit=1";
        header("Location: $redirect_url");
        exit();
    }
}

// Add room to favorites
if (isset($_POST['room_favourite'])) {
    // Initialize response
    $res = ['status' => '', 'error' => ''];

    if (isset($userID) && $userID) {
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

function timeAgo($date)
{
    // Set timezone to Myanmar (Yangon)
    $timezone = new DateTimeZone('Asia/Yangon');

    // Create DateTime objects with Myanmar timezone
    $now = new DateTime('now', $timezone);
    $then = new DateTime($date, $timezone);
    $diff = $now->diff($then);

    if ($diff->y > 0) {
        return $diff->y == 1 ? '1 year ago' : $diff->y . ' years ago';
    } elseif ($diff->m > 0) {
        return $diff->m == 1 ? '1 month ago' : $diff->m . ' months ago';
    } elseif ($diff->d > 7) {
        $weeks = floor($diff->d / 7);
        return $weeks == 1 ? '1 week ago' : $weeks . ' weeks ago';
    } elseif ($diff->d > 0) {
        return $diff->d == 1 ? '1 day ago' : $diff->d . ' days ago';
    } elseif ($diff->h > 0) {
        return $diff->h == 1 ? '1 hour ago' : $diff->h . ' hours ago';
    } elseif ($diff->i > 0) {
        return $diff->i == 1 ? '1 minute ago' : $diff->i . ' minutes ago';
    } else {
        return 'Just now';
    }
}

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
                $redirect_url = "RoomDetails.php?roomTypeID=$roomTypeID&checkin_date=$checkin_date&checkout_date=$checkout_date&adults=$adults&children=$children";
                header("Location: $redirect_url");
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.5.0/css/flag-icon.min.css" />
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

    <main class="pb-4">
        <div class="relative swiper-container flex justify-center">
            <div class="max-w-[1150px] mx-auto px-4 pb-8">
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get" class="w-full p-4 bg-white border-b border-gray-100 flex justify-between items-center space-x-4 relative">
                    <input type="hidden" name="roomTypeID" value="<?= $roomtype['RoomTypeID'] ?>">
                    <input type="hidden" name="reservation_id" value="<?= $reservation_id ?>">
                    <div class="flex items-center space-x-4">
                        <div class="flex gap-3">
                            <!-- Check-in Date -->
                            <div>
                                <label class="font-semibold text-blue-900">Check-In Date</label>
                                <input type="date" id="checkin-date" name="checkin_date"
                                    class="p-3 border border-gray-300 rounded-sm outline-none"
                                    value="<?php echo isset($_GET['checkin_date']) ? $_GET['checkin_date'] : ''; ?>"
                                    placeholder="Check-in Date">
                            </div>
                            <!-- Check-out Date -->
                            <div>
                                <label class="font-semibold text-blue-900">Check-Out Date</label>
                                <input type="date" id="checkout-date" name="checkout_date"
                                    class="p-3 border border-gray-300 rounded-sm outline-none"
                                    value="<?php echo isset($_GET['checkout_date']) ? $_GET['checkout_date'] : ''; ?>"
                                    placeholder="Check-out Date">
                            </div>
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

                    <?php if (isset($_SESSION['alert'])): ?>
                        <div class="absolute -bottom-2 text-sm text-red-500">
                            <?= $_SESSION['alert'] ?>
                        </div>
                        <?php unset($_SESSION['alert']); ?>
                    <?php endif; ?>

                    <script>
                        document.addEventListener('DOMContentLoaded', () => {
                            const checkInDateInput = document.getElementById('checkin-date');
                            const checkOutDateInput = document.getElementById('checkout-date');

                            if (checkInDateInput && checkOutDateInput) {
                                // Get tomorrow's date in YYYY-MM-DD format
                                const tomorrow = new Date();
                                tomorrow.setDate(tomorrow.getDate() + 1);
                                const tomorrowStr = tomorrow.toISOString().split('T')[0];

                                // Set min attributes
                                checkInDateInput.setAttribute('min', tomorrowStr);
                                checkOutDateInput.setAttribute('min', tomorrowStr);

                                // Update checkout min date when checkin changes
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
                        });
                    </script>
                </form>
                <!-- Breadcrumbs -->
                <div class="flex text-sm text-slate-600 my-4">
                    <a href="../User/HomePage.php" class="underline">Home</a>
                    <span><i class="ri-arrow-right-s-fill"></i></span>
                    <a href="../User/RoomBooking.php?checkin_date=<?= $checkin_date ?>&checkout_date=<?= $checkout_date ?>&adults=<?= $adults ?>&children=<?= $children ?>" class="underline">Rooms</a>
                    <span><i class="ri-arrow-right-s-fill"></i></span>
                    <a href="../User/RoomDetails.php?roomTypeID=<?php echo htmlspecialchars($roomtype['RoomTypeID']) ?>&checkin_date=<?= $checkin_date ?>&checkout_date=<?= $checkout_date ?>&adults=<?= $adults ?>&children=<?= $children ?>" class=" underline">Store Details</a>
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
                            <h1 class="text-2xl font-bold"><?= htmlspecialchars($roomtype['RoomType']) ?></h1>
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
                    </div>

                    <div class="flex items-center gap-3">
                        <?php
                        // Check if room is favorited
                        $check_favorite = "SELECT COUNT(*) as count FROM roomtypefavoritetb WHERE UserID = '$userID' AND RoomTypeID = '" . $roomtype['RoomTypeID'] . "'";
                        $favorite_result = $connect->query($check_favorite);
                        $is_favorited = $favorite_result->fetch_assoc()['count'] > 0;
                        ?>

                        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" id="favoriteForms">
                            <input type="hidden" name="checkin_date" value="<?= htmlspecialchars($checkin_date) ?>">
                            <input type="hidden" name="checkout_date" value="<?= htmlspecialchars($checkout_date) ?>">
                            <input type="hidden" name="adults" value="<?= htmlspecialchars($adults) ?>">
                            <input type="hidden" name="children" value="<?= htmlspecialchars($children) ?>">
                            <input type="hidden" name="roomTypeID" value="<?= htmlspecialchars($roomtype['RoomTypeID']) ?>">
                            <button type="submit" name="room_favourite">
                                <i class="ri-heart-fill text-2xl cursor-pointer flex items-center justify-center bg-white w-11 h-11 rounded-full hover:bg-slate-100 transition-colors duration-300 <?= $is_favorited ? 'text-red-500 hover:text-red-600' : 'text-slate-400 hover:text-red-300' ?>"></i>
                            </button>
                        </form>

                        <button
                            onclick="scrollToAvailability()"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-1 px-2 rounded transition-colors select-none">
                            Reserve
                        </button>
                    </div>
                </div>

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
                    <div id="swiperModal" class="fixed inset-0 bg-black bg-opacity-75 z-50 hidden items-center justify-center">
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
                                        <tr class="<?= ($room['RoomStatus'] == 'Available' || $room['RoomStatus'] == 'Edit') ? '' : 'opacity-50'; ?>">
                                            <td class="px-3 md:px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($room['RoomName']) ?> (<?= htmlspecialchars($room['RoomType']) ?>)</td>
                                            <td class="px-3 md:px-6 py-4 whitespace-nowrap text-gray-600"><?= htmlspecialchars($room['RoomStatus']) ?></td>
                                            <td class="px-3 md:px-6 py-4 whitespace-nowrap">
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
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Facilities of Opulence Haven</h2>

                    <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
                        <!-- Great for your stay -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-1">
                                <?php
                                // Get the icon for 'Stay' category
                                $stayIconQuery = "SELECT FacilityTypeIcon FROM facilitytypetb WHERE FacilityType = 'Stay'";
                                $stayIconResult = $connect->query($stayIconQuery);
                                $stayIcon = $stayIconResult->fetch_assoc();
                                ?>
                                <i class="<?= htmlspecialchars($stayIcon['FacilityTypeIcon']) ?> text-xl leading-none"></i>
                                <h3 class="text-md font-semibold text-gray-700">Great for your stay</h3>
                            </div>
                            <div class="grid grid-cols-1 gap-3">
                                <?php
                                $safetyQuery = "SELECT * FROM facilitytb 
                       WHERE FacilityTypeID = (SELECT FacilityTypeID FROM facilitytypetb WHERE FacilityType = 'Stay')
                       ORDER BY Facility";
                                $safetyResult = $connect->query($safetyQuery);

                                while ($facility = $safetyResult->fetch_assoc()) {
                                    echo '
                                <div class="flex items-center gap-2">
                                    <i class="ri-checkbox-circle-line text-base text-green-500 leading-none"></i>
                                    <span class="text-gray-700 text-sm leading-none">' . htmlspecialchars($facility['Facility']) . '</span>
                                </div>
                                ';
                                }
                                ?>
                            </div>
                        </div>

                        <!-- Bathroom -->
                        <div class="space-y-4">
                            <h3 class="text-md font-semibold text-gray-700">Bathroom</h3>
                            <div class="grid grid-cols-1 gap-3">
                                <?php
                                $safetyQuery = "SELECT * FROM facilitytb 
                           WHERE FacilityTypeID = (SELECT FacilityTypeID FROM facilitytypetb WHERE FacilityType = 'Bathroom')
                           ORDER BY Facility";
                                $safetyResult = $connect->query($safetyQuery);

                                while ($facility = $safetyResult->fetch_assoc()) {
                                    echo '
                                <div class="flex items-center gap-2">
                                    <i class="ri-checkbox-circle-line text-base text-green-500 leading-none"></i>
                                    <span class="text-gray-700 text-sm leading-none">' . htmlspecialchars($facility['Facility']) . '</span>
                                </div>
                                ';
                                }
                                ?>
                            </div>
                        </div>

                        <!-- Activities -->
                        <div class="space-y-4">
                            <h3 class="text-md font-semibold text-gray-700">Activities</h3>
                            <div class="grid grid-cols-1 gap-3">
                                <?php
                                $safetyQuery = "SELECT * FROM facilitytb 
                           WHERE FacilityTypeID = (SELECT FacilityTypeID FROM facilitytypetb WHERE FacilityType = 'Activities')
                           ORDER BY Facility";
                                $safetyResult = $connect->query($safetyQuery);

                                while ($facility = $safetyResult->fetch_assoc()) {
                                    echo '
                                <div class="flex items-center gap-2">
                                    <i class="ri-checkbox-circle-line text-base text-green-500 leading-none"></i>
                                    <span class="text-gray-700 text-sm leading-none">' . htmlspecialchars($facility['Facility']) . '</span>
                                </div>
                                ';
                                }
                                ?>
                            </div>
                        </div>

                        <!-- Living Area -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-1">
                                <?php
                                $stayIconQuery = "SELECT FacilityTypeIcon FROM facilitytypetb WHERE FacilityType = 'Living Area'";
                                $stayIconResult = $connect->query($stayIconQuery);
                                $stayIcon = $stayIconResult->fetch_assoc();
                                ?>
                                <i class="<?= htmlspecialchars($stayIcon['FacilityTypeIcon']) ?> text-xl leading-none"></i>
                                <h3 class="text-md font-semibold text-gray-700">Living Area</h3>
                            </div>
                            <div class="grid grid-cols-1 gap-3">
                                <?php
                                $safetyQuery = "SELECT * FROM facilitytb 
                           WHERE FacilityTypeID = (SELECT FacilityTypeID FROM facilitytypetb WHERE FacilityType = 'Living Area')
                           ORDER BY Facility";
                                $safetyResult = $connect->query($safetyQuery);

                                while ($facility = $safetyResult->fetch_assoc()) {
                                    echo '
                                <div class="flex items-center gap-2">
                                    <i class="ri-checkbox-circle-line text-base text-green-500 leading-none"></i>
                                    <span class="text-gray-700 text-sm leading-none">' . htmlspecialchars($facility['Facility']) . '</span>
                                </div>
                                ';
                                }
                                ?>
                            </div>
                        </div>

                        <!-- Media & Technology -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-1">
                                <?php
                                $stayIconQuery = "SELECT FacilityTypeIcon FROM facilitytypetb WHERE FacilityType = 'Media & Technology'";
                                $stayIconResult = $connect->query($stayIconQuery);
                                $stayIcon = $stayIconResult->fetch_assoc();
                                ?>
                                <i class="<?= htmlspecialchars($stayIcon['FacilityTypeIcon']) ?> text-xl leading-none"></i>
                                <h3 class="text-md font-semibold text-gray-700">Media & Technology</h3>
                            </div>
                            <div class="grid grid-cols-1 gap-3">
                                <?php
                                $safetyQuery = "SELECT * FROM facilitytb 
                           WHERE FacilityTypeID = (SELECT FacilityTypeID FROM facilitytypetb WHERE FacilityType = 'Media & Technology')
                           ORDER BY Facility";
                                $safetyResult = $connect->query($safetyQuery);

                                while ($facility = $safetyResult->fetch_assoc()) {
                                    echo '
                                <div class="flex items-center gap-2">
                                    <i class="ri-checkbox-circle-line text-base text-green-500 leading-none"></i>
                                    <span class="text-gray-700 text-sm leading-none">' . htmlspecialchars($facility['Facility']) . '</span>
                                </div>
                                ';
                                }
                                ?>
                            </div>
                        </div>
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
    include('../includes/MoveUpBtn.php');
    include('../includes/Alert.php');
    include('../includes/UserRoomReview.php');
    include('../includes/Footer.php');
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