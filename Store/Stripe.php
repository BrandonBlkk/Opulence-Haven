<?php
session_start();
require_once('../config/db_connection.php');
require_once __DIR__ . '/../vendor/autoload.php';
$stripeConfig = require_once __DIR__ . '/../config/stripe.php';

\Stripe\Stripe::setApiKey($stripeConfig['secret_key']);

// Get the user's current reservation
$userID = $_SESSION['UserID'] ?? null;
if (!$userID) {
    die(json_encode(['error' => 'User not logged in']));
}

// Initialize line items array
$line_items = [];

// Process Cart Items (now using ordertb with status 'pending')
$cartQuery = "SELECT 
                od.*, 
                p.Title, 
                p.Price, 
                p.DiscountPrice, 
                p.Description, 
                p.DeliveryInfo,
                p.Brand
              FROM ordertb o
              JOIN orderdetailtb od ON o.OrderID = od.OrderID
              JOIN producttb p ON od.ProductID = p.ProductID
              WHERE o.UserID = ? AND o.Status = 'Pending'";
$stmt = $connect->prepare($cartQuery);
$stmt->bind_param("s", $userID);
$stmt->execute();
$cartItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

foreach ($cartItems as $cartItem) {
    $price = $cartItem['DiscountPrice'] > 0 ? $cartItem['DiscountPrice'] : $cartItem['Price'];

    $line_items[] = [
        'quantity' => $cartItem['OrderUnitQuantity'],
        'price_data' => [
            'currency' => 'usd',
            'unit_amount' => $price * 100, // Convert to cents
            'product_data' => [
                'name' => $cartItem['Title'],
                'description' => $cartItem['Description'],
                'metadata' => [
                    'product_id' => $cartItem['ProductID'],
                    'size_id' => $cartItem['SizeID'],
                    'brand' => $cartItem['Brand'] // Moved brand to metadata
                ]
            ]
        ]
    ];
}

$reservationQuery = "SELECT * FROM reservationtb WHERE UserID = ? AND Status = 'Pending' ORDER BY ReservationDate DESC LIMIT 1";
$stmt = $connect->prepare($reservationQuery);
$stmt->bind_param("s", $userID);
$stmt->execute();
$reservation = $stmt->get_result()->fetch_assoc();

if ($reservation) {
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

    if (!empty($items)) {
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

        // Add reservation items to line items
        foreach ($items as $item) {
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

        // Add taxes as a separate line item (only for reservation)
        $line_items[] = [
            'quantity' => 1,
            'price_data' => [
                'currency' => 'usd',
                'unit_amount' => round($tax * 100),
                'product_data' => [
                    'name' => 'Taxes and Fees (10%)'
                ]
            ]
        ];
    }
}

// Check if there are any items to charge
if (empty($line_items)) {
    die(json_encode(['error' => 'No items found in cart or reservation']));
}

// Get the pending order ID (if exists)
$pendingOrderQuery = "SELECT OrderID FROM ordertb WHERE UserID = ? AND Status = 'Pending' LIMIT 1";
$stmt = $connect->prepare($pendingOrderQuery);
$stmt->bind_param("s", $userID);
$stmt->execute();
$pendingOrder = $stmt->get_result()->fetch_assoc();
$orderID = $pendingOrder['OrderID'] ?? null;

// Create Stripe checkout session
try {
    $checkout_session = \Stripe\Checkout\Session::create([
        "mode" => "payment",
        "success_url" => "http://localhost/OpulenceHaven/Store/payment_success.php?payment=success&order_id=" . $orderID,
        "cancel_url" => "http://localhost/OpulenceHaven/Store/store_checkout.php?payment=cancel",
        "locale" => "auto",
        "customer_email" => $_SESSION['UserEmail'] ?? null,
        "metadata" => [
            "reservation_id" => $reservation['ReservationID'] ?? null,
            "order_id" => $orderID,
            "user_id" => $userID
        ],
        "line_items" => $line_items,
        "payment_intent_data" => [
            "description" => isset($firstItem) ?
                "Hotel reservation from " . $firstItem['CheckInDate'] . " to " . $firstItem['CheckOutDate'] :
                "Product purchase"
        ]
    ]);

    // Redirect to Stripe checkout
    header("HTTP/1.1 303 See Other");
    header("Location: " . $checkout_session->url);
    exit();
} catch (\Stripe\Exception\ApiErrorException $e) {
    // Handle Stripe API errors
    die(json_encode(['error' => 'Stripe error: ' . $e->getMessage()]));
}
