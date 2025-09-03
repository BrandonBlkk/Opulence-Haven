<?php
session_start();
require_once('../config/db_connection.php');
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../config');
$dotenv->load();

\Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

$userID = $_SESSION['UserID'] ?? null;
if (!$userID) {
    die(json_encode(['error' => 'User not logged in']));
}

// Initialize line items array
$line_items = [];
$subtotal = 0;

// Process Cart Items (Pending orders)
$cartQuery = "
    SELECT 
        od.OrderUnitQuantity, 
        od.ProductID, 
        od.SizeID, 
        od.OrderUnitPrice, 
        p.Title, 
        p.Description, 
        p.Brand
    FROM ordertb o
    JOIN orderdetailtb od ON o.OrderID = od.OrderID
    JOIN producttb p ON od.ProductID = p.ProductID
    WHERE o.UserID = ? AND o.Status = 'Pending'
";
$stmt = $connect->prepare($cartQuery);
$stmt->bind_param("s", $userID);
$stmt->execute();
$cartItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Add cart items to Stripe line items and calculate subtotal
foreach ($cartItems as $cartItem) {
    $line_items[] = [
        'quantity' => $cartItem['OrderUnitQuantity'],
        'price_data' => [
            'currency' => 'usd',
            'unit_amount' => round($cartItem['OrderUnitPrice'] * 100), // Already includes markup/discount
            'product_data' => [
                'name' => $cartItem['Title'],
                'description' => $cartItem['Description'],
                'metadata' => [
                    'product_id' => $cartItem['ProductID'],
                    'size_id' => $cartItem['SizeID'],
                    'brand' => $cartItem['Brand']
                ]
            ]
        ]
    ];

    $subtotal += $cartItem['OrderUnitQuantity'] * $cartItem['OrderUnitPrice'];
}

// Stop if no cart items
if (empty($line_items)) {
    die(json_encode(['error' => 'No items found in cart']));
}

// Calculate delivery fee, tax, and total price
$deliveryFee = 5.00;
$tax = $subtotal * 0.10;
$total_price = $subtotal + $tax + $deliveryFee;

// Add delivery fee as a separate line item
$line_items[] = [
    'quantity' => 1,
    'price_data' => [
        'currency' => 'usd',
        'unit_amount' => round($deliveryFee * 100),
        'product_data' => [
            'name' => 'Delivery Fee',
            'description' => 'Standard delivery charge'
        ]
    ]
];

// Add tax as a separate line item
$line_items[] = [
    'quantity' => 1,
    'price_data' => [
        'currency' => 'usd',
        'unit_amount' => round($tax * 100),
        'product_data' => [
            'name' => 'Tax (10%)',
            'description' => 'Applied sales tax'
        ]
    ]
];

// Get pending order ID (for redirect after payment)
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
            "order_id" => $orderID,
            "user_id" => $userID,
            "subtotal" => $subtotal,
            "delivery_fee" => $deliveryFee,
            "tax" => $tax,
            "total_price" => $total_price
        ],
        "line_items" => $line_items,
        "payment_intent_data" => [
            "description" => "Product purchase including delivery and tax"
        ]
    ]);

    header("HTTP/1.1 303 See Other");
    header("Location: " . $checkout_session->url);
    exit();
} catch (\Stripe\Exception\ApiErrorException $e) {
    die(json_encode(['error' => 'Stripe error: ' . $e->getMessage()]));
}
