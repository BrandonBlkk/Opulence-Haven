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

// Validate and Fetch Order
if (empty($_GET['order_id'])) {
    die(json_encode(['error' => 'Missing order_id']));
}
$orderID = $_GET['order_id'];

$qOrder = "SELECT OrderID, AdditionalAmount FROM ordertb WHERE OrderID = ? AND UserID = ? LIMIT 1";
$stmt = $connect->prepare($qOrder);
$stmt->bind_param("ss", $orderID, $userID);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    die(json_encode(['error' => 'Order not found']));
}

$additionalAmount = (float)($order['AdditionalAmount'] ?? 0);

if ($additionalAmount <= 0) {
    die(json_encode(['error' => 'No additional payment required for this order']));
}

// Create Stripe line item for additional payment
$line_items = [[
    'quantity' => 1,
    'price_data' => [
        'currency' => 'usd',
        'unit_amount' => round($additionalAmount * 100),
        'product_data' => [
            'name' => 'Additional Payment',
            'description' => 'Payment for modified order #' . htmlspecialchars($orderID)
        ]
    ]
]];

try {
    $checkout_session = \Stripe\Checkout\Session::create([
        "mode" => "payment",
        "success_url" => "http://localhost/OpulenceHaven/Store/order_modify_success.php?payment=success&order_id=" . $orderID,
        "cancel_url" => "http://localhost/OpulenceHaven/Store/modify_order.php?order_id=" . $orderID . "&payment=cancel",
        "locale" => "auto",
        "customer_email" => $_SESSION['UserEmail'] ?? null,
        "metadata" => [
            "order_id" => $orderID,
            "user_id" => $userID,
            "additional_amount" => $additionalAmount
        ],
        "line_items" => $line_items,
        "payment_intent_data" => [
            "description" => "Additional payment for modified order #" . $orderID
        ]
    ]);

    header("HTTP/1.1 303 See Other");
    header("Location: " . $checkout_session->url);
    exit();
} catch (\Stripe\Exception\ApiErrorException $e) {
    die(json_encode(['error' => 'Stripe error: ' . $e->getMessage()]));
}
