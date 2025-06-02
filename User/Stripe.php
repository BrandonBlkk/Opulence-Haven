<?php
session_start();
include('../config/dbConnection.php');
require_once __DIR__ . '/../vendor/autoload.php';
$stripeConfig = require __DIR__ . '/../config/stripe.php';

\Stripe\Stripe::setApiKey($stripeConfig['secret_key']);

// Get the user's current reservation
$userID = $_SESSION['UserID'] ?? null;
if (!$userID) {
    die(json_encode(['error' => 'User not logged in']));
}

// Get reservation details
$reservationQuery = "SELECT * FROM reservationtb WHERE UserID = ? AND Status = 'Pending' ORDER BY ReservationDate DESC LIMIT 1";
$stmt = $connect->prepare($reservationQuery);
$stmt->bind_param("s", $userID);
$stmt->execute();
$reservation = $stmt->get_result()->fetch_assoc();

if (!$reservation) {
    die(json_encode(['error' => 'No active reservation found']));
}

// Get reservation items with dates and images
$itemsQuery = "SELECT rd.*, rt.RoomType, rt.RoomPrice, rt.RoomCoverImage AS ImagePath, rd.CheckInDate, rd.CheckOutDate
               FROM reservationdetailtb rd
               JOIN roomtb r ON rd.RoomID = r.RoomID
               JOIN roomtypetb rt ON r.RoomTypeID = rt.RoomTypeID
               WHERE rd.ReservationID = ?";
$stmt = $connect->prepare($itemsQuery);
$stmt->bind_param("s", $reservation['ReservationID']);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (empty($items)) {
    die(json_encode(['error' => 'No items found for reservation']));
}

// Get the first item's dates (assuming all items have same dates)
$firstItem = $items[0];
$checkIn = new DateTime($firstItem['CheckInDate']);
$checkOut = new DateTime($firstItem['CheckOutDate']);
$nights = $checkOut->diff($checkIn)->days;

// Ensure nights is at least 1
if ($nights < 1) {
    $nights = 1;
}

// Calculate total price for all rooms
$totalPrice = 0;
foreach ($items as $item) {
    $totalPrice += $item['RoomPrice'] * $nights;
}

// Calculate tax (10% of total price)
$tax = $totalPrice * 0.1;

// Prepare line items for Stripe
$line_items = [];
foreach ($items as $item) {
    $checkInTime = '14:00';
    $checkOutTime = '12:00';

    $description = "Check-in: " . $item['CheckInDate'] . " at " . $checkInTime . "\n";
    $description .= "Check-out: " . $item['CheckOutDate'] . " at " . $checkOutTime . "\n";
    $description .= "Duration: " . $nights . ($nights > 1 ? ' nights' : ' night') . " stay\n";

    $line_items[] = [
        'quantity' => 1,
        'price_data' => [
            'currency' => 'usd',
            'unit_amount' => ($item['RoomPrice'] * $nights) * 100,
            'product_data' => [
                'name' => $item['RoomType'],
                'description' => $description,
                'metadata' => [
                    'room_id' => $item['RoomID'],
                    'checkin' => $item['CheckInDate'] . ' ' . $checkInTime,
                    'checkout' => $item['CheckOutDate'] . ' ' . $checkOutTime,
                    'adults' => $item['Adult'],
                    'children' => $item['Children']
                ]
            ]
        ]
    ];
}

// Add taxes as a separate line item (now calculated on total price)
$line_items[] = [
    'quantity' => 1,
    'price_data' => [
        'currency' => 'usd',
        'unit_amount' => round($tax * 100), // Convert tax amount to cents
        'product_data' => [
            'name' => 'Taxes and Fees (10%)'
        ]
    ]
];

// Create Stripe checkout session
$checkout_session = \Stripe\Checkout\Session::create([
    "mode" => "payment",
    "success_url" => "http://localhost/OpulenceHaven/User/Reservation.php?payment=success",
    "cancel_url" => "http://localhost/OpulenceHaven/User/Reservation.php?payment=cancel",
    "locale" => "auto",
    "customer_email" => $_SESSION['UserEmail'] ?? null,
    "metadata" => [
        "reservation_id" => $reservation['ReservationID'],
        "user_id" => $userID
    ],
    "line_items" => $line_items,
    "payment_intent_data" => [
        "description" => "Hotel reservation from " . $firstItem['CheckInDate'] . " to " . $firstItem['CheckOutDate']
    ]
]);

// Redirect to Stripe checkout
header("HTTP/1.1 303 See Other");
header("Location: " . $checkout_session->url);
exit();
