<?php
session_start();
require_once('../config/db_connection.php');
include_once('../includes/auto_id_func.php');
include('../includes/mask_email.php');

require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

// Initialize variables
$userID = (!empty($_SESSION["UserID"]) ? $_SESSION["UserID"] : null);
$alertMessage = '';
$reservedRooms = [];
$totalNights = 0;
$totalGuest = 0;

$user = "SELECT * FROM usertb WHERE UserID = '$userID'";
$userData = $connect->query($user)->fetch_assoc();

$nameParts = explode(' ', trim($userData['UserName'] ?? 'Guest'));
$initials = substr($nameParts[0], 0, 1);
if (count($nameParts) > 1) {
    $initials .= substr(end($nameParts), 0, 1);
}

// Check if ReservationID is provided form Modify
if (isset($_GET['modify_reservation_id'])) {
    $modifyReservationID = htmlspecialchars($_GET['modify_reservation_id']);

    // Get reservation details
    $reservationQuery = "SELECT * FROM reservationtb WHERE ReservationID = '$modifyReservationID'";
    $reservationResult = $connect->query($reservationQuery);

    if ($reservationResult->num_rows > 0) {
        $reservation = $reservationResult->fetch_assoc();
        $totalPrice = $reservation['TotalPrice'];

        // Get all rooms in this reservation
        $roomsQuery = "SELECT rd.*, r.*, rt.* 
                       FROM reservationdetailtb rd
                       JOIN roomtb r ON rd.RoomID = r.RoomID
                       JOIN roomtypetb rt ON r.RoomTypeID = rt.RoomTypeID
                       WHERE rd.ReservationID = '$modifyReservationID'";
        $roomsResult = $connect->query($roomsQuery);

        if ($roomsResult->num_rows > 0) {
            while ($room = $roomsResult->fetch_assoc()) {
                try {
                    // Calculate nights
                    $checkin_date = $room['CheckInDate'];
                    $checkout_date = $room['CheckOutDate'];
                    $adults = $room['Adult'];
                    $children = $room['Children'];
                    $nights = (strtotime($checkout_date) - strtotime($checkin_date)) / (60 * 60 * 24);

                    // Add calculated fields
                    $room['nights'] = $nights;
                    $room['subtotal'] = $room['Price'] * $nights;
                    $room['total'] = $room['Price'] * $nights * 1.1; // Tax
                    $reservedRooms[] = $room;

                    // Set total nights (same for all rooms in reservation)
                    $totalNights = $nights;

                    // Sum total guests
                    $totalGuest += $adults + $children;
                } catch (Exception $e) {
                    $alertMessage = "Invalid dates: " . $e->getMessage();
                    header("Location: reservation.php");
                    exit();
                }
            }
        }
    } else {
        header("Location: reservation.php");
        exit();
    }
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
        $roomtype_id = $_POST['roomTypeID'];
        $roomId = $_POST['room_id'];
        $reservationId = $_POST['reservation_id'];
        $checkin_date = $_POST['checkin_date'];
        $checkout_date = $_POST['checkout_date'];
        $adults = $_POST['adults'];
        $children = $_POST['children'];

        // Get modify_reservation_id if exists
        $modify_reservation_id = isset($_POST['modify_reservation_id']) ? $_POST['modify_reservation_id'] : '';

        // Then redirect to the room_details.php page with parameters
        // Replace reservation_id with modify_reservation_id (no reservation_id in URL)
        if (!empty($modify_reservation_id)) {
            $redirectUrl = "room_details.php?modify_reservation_id=$modify_reservation_id&roomTypeID=$roomtype_id&room_id=$roomId&checkin_date=$checkin_date&checkout_date=$checkout_date&adults=$adults&children=$children&edit=1";
        } else {
            $redirectUrl = "room_details.php?roomTypeID=$roomtype_id&room_id=$roomId&checkin_date=$checkin_date&checkout_date=$checkout_date&adults=$adults&children=$children&edit=1";
        }

        header("Location: $redirectUrl");
        exit();
    }
}

// Remove room from reservation
if (isset($_POST['remove_room'])) {
    $response = ['success' => false];

    // Determine reservation ID
    if (isset($_POST['modify_reservation_id']) && !empty($_POST['modify_reservation_id'])) {
        $reservation_id = $_POST['modify_reservation_id'];
    } else {
        $reservation_id = $_POST["reservation_id"];
    }

    $room_id = $_POST["room_id"];

    // Remove room from reservationdetailtb
    $query = "DELETE FROM reservationdetailtb WHERE RoomID = ? AND ReservationID = ?";
    $stmt = $connect->prepare($query);
    $stmt->bind_param("ss", $room_id, $reservation_id);
    $stmt->execute();

    // Update room status to Available
    $room = "UPDATE roomtb SET RoomStatus = 'Available' WHERE RoomID = ?";
    $stmt = $connect->prepare($room);
    $stmt->bind_param("s", $room_id);
    $stmt->execute();

    // Check if any rooms are left for this reservation
    $query = "SELECT COUNT(*) as count FROM reservationdetailtb WHERE ReservationID = ?";
    $stmt = $connect->prepare($query);
    $stmt->bind_param("s", $reservation_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];

    if ($count == 0) {
        // Delete reservation and all details if no rooms left
        $deleteDetails = "DELETE FROM reservationdetailtb WHERE ReservationID = ?";
        $stmt = $connect->prepare($deleteDetails);
        $stmt->bind_param("s", $reservation_id);
        $stmt->execute();

        $deleteReservation = "DELETE FROM reservationtb WHERE ReservationID = ?";
        $stmt = $connect->prepare($deleteReservation);
        $stmt->bind_param("s", $reservation_id);
        $stmt->execute();
    } else {
        // If rooms remain, update total price and points
        $updateTotal = "UPDATE reservationtb 
                        SET TotalPrice = (SELECT SUM(Price) FROM reservationdetailtb WHERE ReservationID = ?) * 1.1 
                        WHERE ReservationID = ?";
        $stmtUpdate = $connect->prepare($updateTotal);
        $stmtUpdate->bind_param("ss", $reservation_id, $reservation_id);
        $stmtUpdate->execute();

        // Recalculate points
        $getUserID = "SELECT UserID FROM reservationtb WHERE ReservationID = ?";
        $stmtUser = $connect->prepare($getUserID);
        $stmtUser->bind_param("s", $reservation_id);
        $stmtUser->execute();
        $userID = $stmtUser->get_result()->fetch_assoc()['UserID'];

        $getMembership = "SELECT Membership FROM usertb WHERE UserID = ?";
        $stmtMember = $connect->prepare($getMembership);
        $stmtMember->bind_param("s", $userID);
        $stmtMember->execute();
        $membership = $stmtMember->get_result()->fetch_assoc()['Membership'];

        $getTotalPrice = "SELECT TotalPrice FROM reservationtb WHERE ReservationID = ?";
        $stmtTotal = $connect->prepare($getTotalPrice);
        $stmtTotal->bind_param("s", $reservation_id);
        $stmtTotal->execute();
        $totalPrice = $stmtTotal->get_result()->fetch_assoc()['TotalPrice'];

        $pointsEarned = ($membership == 1) ? $totalPrice * 3 : $totalPrice;

        $updatePoints = "UPDATE reservationtb SET PointsEarned = ? WHERE ReservationID = ?";
        $stmtPoints = $connect->prepare($updatePoints);
        $stmtPoints->bind_param("ds", $pointsEarned, $reservation_id);
        $stmtPoints->execute();
    }

    // Reset timer if all rooms removed
    if ($count == 0) {
        $defaultExpiry = time() + (15 * 60); // Reset to 15 minutes from now
        $response['reset_timer'] = true;
        $response['new_expiry'] = $defaultExpiry;
    } else {
        $response['reset_timer'] = false;
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
        if ($userID !== null) {
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
        } else {
            // User is not logged in
            header('Content-Type: application/json');
            echo json_encode([
                'login_required' => true,
            ]);
            exit();
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

        // Fetch the latest reservation for the logged-in user
        $userID = $_SESSION['UserID'] ?? null;
        if ($userID) {
            $reservationQuery = $connect->prepare("
                SELECT * FROM reservationtb 
                WHERE UserID = ? AND Status = 'Pending' 
                ORDER BY ReservationDate DESC LIMIT 1
            ");
            $reservationQuery->bind_param("s", $userID);
            $reservationQuery->execute();
            $reservationResult = $reservationQuery->get_result();
            $reservation = $reservationResult->fetch_assoc();

            if ($reservation) {
                $reservationID = $reservation['ReservationID'];

                // Update reservation status to confirmed
                $update = "UPDATE reservationtb SET Status = 'Confirmed' WHERE ReservationID = ?";
                $stmt = $connect->prepare($update);
                $stmt->bind_param("s", $reservationID);
                $stmt->execute();

                // Send payment confirmation email (existing code unchanged)
                if (isset($_SESSION['UserEmail'])) {
                    $email = $_SESSION['UserEmail'];
                    $username = $_SESSION['UserName'] ?? 'Guest';

                    // Fetch reservation details for email
                    $itemsQuery = $connect->prepare("
                        SELECT rd.*, rt.RoomType, rt.RoomPrice, rd.CheckInDate, rd.CheckOutDate
                        FROM reservationdetailtb rd
                        JOIN roomtb r ON rd.RoomID = r.RoomID
                        JOIN roomtypetb rt ON r.RoomTypeID = rt.RoomTypeID
                        WHERE rd.ReservationID = ?
                    ");
                    $itemsQuery->bind_param("s", $reservationID);
                    $itemsQuery->execute();
                    $items = $itemsQuery->get_result()->fetch_all(MYSQLI_ASSOC);

                    $subtotal = 0;
                    $items_html = '';
                    foreach ($items as $item) {
                        $checkIn = new DateTime($item['CheckInDate']);
                        $checkOut = new DateTime($item['CheckOutDate']);
                        $nights = $checkOut->diff($checkIn)->days;
                        $nights = max($nights, 1);
                        $line_total = $item['RoomPrice'] * $nights;
                        $subtotal += $line_total;
                        $items_html .= "
                            <tr>
                                <td style='padding: 12px; border: 1px solid #ddd;'>{$item['RoomType']}</td>
                                <td style='padding: 12px; border: 1px solid #ddd;'>{$item['CheckInDate']}</td>
                                <td style='padding: 12px; border: 1px solid #ddd;'>{$item['CheckOutDate']}</td>
                                <td style='padding: 12px; border: 1px solid #ddd;'>{$nights} night(s)</td>
                                <td style='padding: 12px; border: 1px solid #ddd;'>$" . number_format($line_total, 2) . "</td>
                            </tr>
                        ";
                    }

                    $tax = $subtotal * 0.10;
                    $total_price = $subtotal + $tax;

                    // Define check-in instructions and cancellation policy
                    $checkin_time = '2:00 PM';
                    $checkout_time = '12:00 PM';
                    $cancellation_policy = "Cancellations made within 24 hours of check-in will incur a fee of \$50. Free cancellation is available until August 28, 2025.";

                    try {
                        $mail = new PHPMailer(true);
                        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../config');
                        $dotenv->load();

                        $mail->isSMTP();
                        $mail->Host       = $_ENV['MAIL_HOST'];
                        $mail->SMTPAuth   = true;
                        $mail->Username   = $_ENV['MAIL_USERNAME'];
                        $mail->Password   = $_ENV['MAIL_PASSWORD'];
                        $mail->SMTPSecure = $_ENV['MAIL_ENCRYPTION'];
                        $mail->Port       = $_ENV['MAIL_PORT'];

                        $mail->setFrom('opulencehaven25@gmail.com', 'Opulence Haven');
                        $mail->addAddress($email);

                        $mail->isHTML(true);
                        $mail->Subject = "Reservation Confirmation - #{$reservationID}";
                        $mail->Body = "
                        <html>
                        <head>
                            <style>
                                body { font-family: Arial, sans-serif; line-height: 1.6; background-color: #f9f9f9; color: #333; }
                                .content { background: #ffffff; padding: 20px; border-radius: 8px; max-width: 600px; margin: auto; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
                                .footer { padding-top: 20px; border-top: 1px solid #eee; font-size: 0.9em; color: #777; text-align: center; }
                                h2 { color: #444; }
                                table { border-collapse: collapse; width: 100%; margin-top: 20px; }
                                th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
                                th { background-color: #f4f4f4; font-weight: bold; color: #444; }
                            </style>
                        </head>
                        <body>
                            <div class='content'>
                                <h2>Reservation Confirmation</h2>
                                <p>Hello {$username},</p>
                                <p>Your payment was successful! Your reservation <strong>#{$reservationID}</strong> has been confirmed.</p>

                                <h3>Reservation Details</h3>
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Room Type</th>
                                            <th>Check-In</th>
                                            <th>Check-Out</th>
                                            <th>Duration</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {$items_html}
                                    </tbody>
                                </table>

                                <p style='margin-top: 20px;'>
                                    <strong>Subtotal:</strong> $" . number_format($subtotal, 2) . "<br>
                                    <strong>Tax (10%):</strong> $" . number_format($tax, 2) . "<br>
                                    <strong>Total:</strong> $" . number_format($total_price, 2) . "
                                </p>

                                <h3>Check-in Instructions</h3>
                                <p>Please check in on <strong>" . $items[0]['CheckInDate'] . " at {$checkin_time}</strong> and check out by <strong>" . $items[0]['CheckOutDate'] . " at {$checkout_time}</strong>.</p>

                                <h3>Cancellation Policy</h3>
                                <p>{$cancellation_policy}</p>

                                <p>We look forward to hosting you! Please keep this email as your confirmation.</p>
                            </div>
                            <div class='footer'>
                                <p>&copy; " . date('Y') . " Opulence Haven. All rights reserved.</p>
                            </div>
                        </body>
                        </html>
                        ";

                        $mail->AltBody = "Hello {$username},\n\nYour payment was successful! Your reservation #{$reservationID} has been confirmed.\n\nSubtotal: $" . number_format($subtotal, 2) . "\nTax: $" . number_format($tax, 2) . "\nTotal: $" . number_format($total_price, 2) . "\n\nCheck-in Instructions:\nPlease check in on " . $items[0]['CheckInDate'] . " at {$checkin_time} and check out by " . $items[0]['CheckOutDate'] . " at {$checkout_time}.\n\nCancellation Policy:\n{$cancellation_policy}\n\nThank you for choosing Opulence Haven!";

                        $mail->send();
                    } catch (Exception $e) {
                        error_log("Reservation confirmation email failed: {$mail->ErrorInfo}");
                    }
                }
            }
        }
    }

    // Handle payment cancellation
    if ($_GET['payment'] === 'cancel') {
        $userID = $_SESSION['UserID'] ?? null;
        if ($userID) {
            $reservationQuery = $connect->prepare("
                SELECT * FROM reservationtb 
                WHERE UserID = ? AND Status = 'Pending' 
                ORDER BY ReservationDate DESC LIMIT 1
            ");
            $reservationQuery->bind_param("s", $userID);
            $reservationQuery->execute();
            $reservation = $reservationQuery->get_result()->fetch_assoc();

            if ($reservation && $reservation['PointsRedeemed'] > 0) {
                $pointsToReturn = $reservation['PointsRedeemed'];

                // Return points to user balance
                $updatePoints = $connect->prepare("UPDATE usertb SET PointsBalance = PointsBalance + ? WHERE UserID = ?");
                $updatePoints->bind_param("is", $pointsToReturn, $userID);
                $updatePoints->execute();

                // Reset points in reservation
                $resetPoints = $connect->prepare("UPDATE reservationtb SET PointsDiscount = 0, PointsRedeemed = 0 WHERE ReservationID = ?");
                $resetPoints->bind_param("s", $reservation['ReservationID']);
                $resetPoints->execute();
            }
        }
        $_SESSION['error'] = "Payment was cancelled. Your redeemed points have been restored.";
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

<body class="relative bg-gray-50 min-w-[380px]">
    <?php
    include('../includes/navbar.php');
    include('../includes/cookies.php');
    ?>

    <!-- Login Modal -->
    <?php
    include('../includes/login_request.php');
    ?>

    <div class="max-w-[1150px] mx-auto px-0 sm:px-4 py-3 sm:py-8">
        <!-- Progress Steps -->
        <div class="flex justify-between items-center px-3 mb-8">
            <div class="flex-1 text-center">
                <div class="w-8 h-8 mx-auto rounded-full bg-amber-500 text-white flex items-center justify-center mb-2">1</div>
                <p class="text-sm font-medium text-amber-500">Your selection</p>
            </div>
            <div class="flex-1 border-t-2 border-amber-500"></div>
            <div class="flex-1 text-center">
                <div class="w-8 h-8 mx-auto rounded-full bg-amber-500 text-white flex items-center justify-center mb-2">2</div>
                <p class="text-sm font-medium text-amber-500">Enter your details</p>
            </div>
            <div class="flex-1 border-t-2 border-gray-300"></div>
            <div class="flex-1 text-center">
                <div class="w-8 h-8 mx-auto rounded-full bg-gray-200 text-gray-500 flex items-center justify-center mb-2">3</div>
                <p class="text-sm font-medium text-gray-500">Confirm your reservation</p>
            </div>
        </div>

        <!-- Main Content -->
        <div class="bg-white overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r bg-blue-50 p-3 sm:p-6">
                <h1 class="text-2xl font-semibold text-gray-800 leading-snug">
                    Great choice! You're almost there.
                </h1>
                <p class="mt-1 text-base text-gray-600">
                    Complete the remaining steps to finish your reservation smoothly.
                </p>
            </div>

            <!-- Booking Details -->
            <div class="p-3 sm:p-6 border-b border-gray-200">
                <div class="flex flex-col lg:flex-row gap-7">
                    <!-- Left Column - Booking Information -->
                    <div class="w-full lg:w-1/3">
                        <div class="space-y-6">
                            <!-- Booking Summary -->
                            <div class="bg-white rounded-lg shadow-sm">
                                <h3 class="text-lg font-semibold rounded-t-lg text-white mb-0 border-b border-blue-800 bg-blue-900 p-3">
                                    Your booking details
                                </h3>
                                <!-- Additional booking info -->
                                <div class="bg-blue-50 border border-blue-100 rounded-lg p-3 my-3">
                                    <p class="text-xs text-blue-800 flex items-start">
                                        <i class="ri-information-line mr-2 mt-0.5"></i>
                                        Need to modify your booking? Contact our customer service for assistance.
                                    </p>
                                </div>
                                <div class="space-y-3">
                                    <?php
                                    if (isset($reservationID)) {
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

                                            // Start scrollable container for reservation details
                                            echo '<div class="reservationScrollBar border rounded-lg bg-white shadow-sm" style="max-height: 320px; overflow-y: auto;">';

                                            // Display room details
                                            while ($roomItem = mysqli_fetch_assoc($detailsResult)) {
                                                $checkIn = new DateTime($roomItem['CheckInDate']);
                                                $checkOut = new DateTime($roomItem['CheckOutDate']);
                                                $totalNights = $checkOut->diff($checkIn)->days;
                                                $roomTotal = $roomItem['RoomPrice'] * $totalNights;
                                    ?>
                                                <div class="space-y-2 p-3 border-b" id="booking-details-container">
                                                    <!-- Room Title and Price -->
                                                    <div class="flex justify-between items-center">
                                                        <h4 class="text-base font-semibold text-gray-800">
                                                            <?= htmlspecialchars($roomItem['RoomName'] ?? 'Room') ?>
                                                            <?= htmlspecialchars($roomItem['RoomType']) ?>
                                                        </h4>
                                                        <p class="text-base font-bold text-gray-900">
                                                            USD<?= number_format($roomItem['RoomPrice'] * $totalNights, 2) ?>
                                                        </p>
                                                    </div>

                                                    <!-- Dates -->
                                                    <div class="text-xs text-gray-600">
                                                        <div class="flex items-center space-x-2">
                                                            <div>
                                                                <p class="flex items-center text-[11px] font-medium text-gray-500">
                                                                    <i class="ri-calendar-event-line mr-1"></i> Check-in
                                                                </p>
                                                                <p class="font-medium text-sm"><?= $checkIn->format('M j, Y') ?></p>
                                                            </div>
                                                            <span class="text-gray-400">—</span>
                                                            <div>
                                                                <p class="flex items-center text-[11px] font-medium text-gray-500">
                                                                    <i class="ri-calendar-event-line mr-1"></i> Check-out
                                                                </p>
                                                                <p class="font-medium text-sm"><?= $checkOut->format('M j, Y') ?></p>
                                                            </div>
                                                        </div>
                                                        <p class="flex items-center text-[11px] font-medium text-gray-500 mt-1">
                                                            <i class="ri-hotel-bed-line mr-1"></i> Total stay: <?= $totalNights ?> night<?= $totalNights > 1 ? 's' : '' ?>
                                                        </p>
                                                    </div>

                                                    <!-- Guests -->
                                                    <p class="text-xs font-medium text-gray-500 mb-1 flex items-center">
                                                        <i class="ri-user-line mr-1"></i> Guests
                                                    </p>
                                                    <div class="text-xs text-gray-600">
                                                        <?= $roomItem['Adult'] ?> adult<?= $roomItem['Adult'] > 1 ? 's' : '' ?>
                                                        <?= $roomItem['Children'] > 0 ? ' + ' . $roomItem['Children'] . ' child' . ($roomItem['Children'] > 1 ? 'ren' : '') : '' ?>
                                                    </div>
                                                </div>
                                            <?php
                                            }

                                            // End scrollable container
                                            echo '</div>';
                                            ?>
                                            <div class="pt-3 border-t border-gray-100">
                                                <div class="flex justify-between items-center">
                                                    <p class="text-base font-semibold text-gray-800">Subtotal</p>
                                                    <p class="text-gray-800">USD<?= number_format($subtotal, 2) ?></p>
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
                                                    <p class="text-gray-800">USD<?= number_format($grandTotal * 0.1, 2) ?></p>
                                                </div>

                                                <div class="flex justify-between items-center mt-3 pt-3 border-t border-gray-200">
                                                    <p class="text-lg font-bold text-gray-900">Total</p>
                                                    <p class="text-lg font-bold text-blue-600">USD<?= number_format(($grandTotal * 1.1), 2) ?></p>
                                                </div>

                                                <!-- Points Earned Notification -->
                                                <?php
                                                // Calculate points earned based on actual amount paid (after points discount)
                                                $paidAmount = $grandTotal * 1.1; // includes taxes
                                                if ($membership == 1) $pointsEarned = floor($paidAmount * 3);  // 3 pts/$
                                                else $pointsEarned = floor($paidAmount * 1);  // 1 pt/$
                                                ?>
                                                <p class="text-xs text-green-600 mt-1">
                                                    ✓ Earn <?= $pointsEarned ?> points after payment
                                                    <?= $membership == 1 ? '(Membership bonus applied)' : '' ?>
                                                </p>
                                            </div>
                                    <?php
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
                        <div class="expiry-notice mb-3 
    <?= (isset($_GET['modify_reservation_id']) || !$reservation) ? 'hidden' : '' ?>">
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
                        <div class="">
                            <?php if (!empty($reservedRooms)): ?>
                                <div id="reserved-rooms-container" class="space-y-2">
                                    <?php
                                    $initialLoad = 3; // Number of rooms to show initially
                                    $totalRooms = count($reservedRooms);
                                    foreach ($reservedRooms as $index => $room):
                                        $hiddenClass = $index >= $initialLoad ? 'hidden' : '';
                                    ?>
                                        <div class="reserved-room flex flex-col md:flex-row rounded-md shadow-sm border <?= $hiddenClass ?>">
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

                                                <form class="edit-remove-room-form mt-4 flex gap-3" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post">
                                                    <input type="hidden" name="reservation_id" value="<?= $reservationID ?>">
                                                    <input type="hidden" name="roomTypeID" value="<?= $room['RoomTypeID'] ?>">
                                                    <input type="hidden" name="room_id" value="<?= $room['RoomID'] ?>">
                                                    <input type="hidden" name="checkin_date" value="<?= $room['CheckInDate'] ?>">
                                                    <input type="hidden" name="checkout_date" value="<?= $room['CheckOutDate'] ?>">
                                                    <input type="hidden" name="adults" value="<?= $room['Adult'] ?>">
                                                    <input type="hidden" name="children" value="<?= $room['Children'] ?>">

                                                    <?php if (isset($_GET['modify_reservation_id'])): ?>
                                                        <input type="hidden" name="modify_reservation_id" value="<?= htmlspecialchars($_GET['modify_reservation_id']) ?>">
                                                    <?php endif; ?>

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

                                <?php if ($totalRooms > $initialLoad): ?>
                                    <div class="text-center mt-4">
                                        <button id="load-more-rooms" class="px-5 py-2.5 bg-white text-blue-600 text-sm font-medium rounded-sm border border-blue-200 hover:bg-blue-50 transition-all duration-200 flex items-center justify-center mx-auto">
                                            <span>Load More</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                            <svg class="hidden h-4 w-4 ml-1.5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </button>
                                    </div>
                                <?php endif; ?>
                                <div id="no-rooms-message" class="text-center py-32" style="display: none;">
                                    <p class="text-gray-500 text-sm sm:text-base">You don't have any rooms reserved yet.</p>
                                    <a href="room_booking.php?checkin_date" class="text-blue-600 hover:underline mt-2 inline-block">
                                        Browse available rooms
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-32">
                                    <p class="text-gray-500 text-sm sm:text-base">You don't have any rooms reserved yet.</p>
                                    <a href="room_booking.php" class="text-blue-600 hover:underline mt-2 inline-block">
                                        Browse available rooms
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Load More Script -->
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const loadMoreBtn = document.getElementById('load-more-rooms');
                                if (loadMoreBtn) {
                                    loadMoreBtn.addEventListener('click', function() {
                                        const hiddenRooms = document.querySelectorAll('.reserved-room.hidden');
                                        let count = 0;
                                        hiddenRooms.forEach(room => {
                                            if (count < 3) { // Number of rooms to load each time
                                                room.classList.remove('hidden');
                                                count++;
                                            }
                                        });
                                        if (document.querySelectorAll('.reserved-room.hidden').length === 0) {
                                            loadMoreBtn.style.display = 'none';
                                        }
                                    });
                                }
                            });
                        </script>

                        <form id="reservationForm" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" class="py-6">
                            <h2 class="text-xl font-semibold text-gray-800 mb-4">Enter your details</h2>

                            <div class="mb-6">
                                <div class="flex items-center mb-2">
                                    <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center mr-3">
                                        <span class="w-10 h-10 rounded-full bg-[<?= $userData['ProfileBgColor'] ?? 'bg-slate-500' ?>] text-white uppercase font-semibold flex items-center justify-center select-none"><?= $initials ?></span>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h4 class="text-sm font-medium text-gray-800"><?= $userData['UserName'] ?? "Guest" ?></h4>
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
                                    <input type="text" name="first_name" placeholder="Enter your first name" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    <small id="firstNameError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none">First name is required</small>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Last name (Optional)</label>
                                    <input type="text" name="last_name" placeholder="Enter your last name" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email address <span class="text-red-500">*</span></label>
                                <input type="email" value="<?= maskEmail($userData['UserEmail'] ?? '') ?>" placeholder="guest@example" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" disabled>
                            </div>

                            <div class="mb-6 relative">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone <span class="text-red-500">*</span></label>
                                <input type="tel" name="phone" value="<?= $userData['UserPhone'] ?? '' ?>" placeholder="xxx-xxx-xxx" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <small id="phoneError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none">Phone is required</small>
                            </div>

                            <div class="flex justify-between items-center">
                                <!-- Back Button -->
                                <?php if (isset($_GET['modify_reservation_id'])): ?>
                                    <!-- User came from modification page -->
                                    <a href="../User/reservation.php?modify_reservation_id=<?= htmlspecialchars($_GET['modify_reservation_id']) ?>&checkin_date=<?= urlencode($checkin_date) ?>&checkout_date=<?= urlencode($checkout_date) ?>&adults=<?= urlencode($adults) ?>&children=<?= urlencode($children) ?>"
                                        class="text-blue-900 hover:text-blue-950 font-medium">
                                        Back
                                    </a>
                                <?php else: ?>
                                    <?php if (!empty($reservedRooms)): ?>
                                        <!-- Normal back link -->
                                        <a href="../User/room_booking.php?checkin_date=<?= urlencode($checkin_date) ?>&checkout_date=<?= urlencode($checkout_date) ?>&adults=<?= urlencode($adults) ?>&children=<?= urlencode($children) ?>"
                                            class="text-blue-900 hover:text-blue-950 font-medium">
                                            Back
                                        </a>
                                    <?php else: ?>
                                        <!-- Back to previous page -->
                                        <a href="../User/room_booking.php" class="text-blue-900 hover:text-blue-950 font-medium">
                                            Back
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <button type="submit" id="submitButton" name="submit_reservation" class="bg-blue-900 hover:bg-blue-950 text-white font-medium py-2 px-6 rounded-sm flex items-center justify-center select-none">
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