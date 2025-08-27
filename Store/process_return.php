<?php
session_start();
require_once('../config/db_connection.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['product_id']) || empty($data['order_id']) || empty($data['quantity']) || empty($data['action_type'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data.']);
    exit();
}

$userID = !empty($_SESSION["UserID"]) ? $_SESSION["UserID"] : null;

$productID = mysqli_real_escape_string($connect, $data['product_id']);
$orderID = mysqli_real_escape_string($connect, $data['order_id']);
$quantity = (int)$data['quantity'];
$remarks = mysqli_real_escape_string($connect, $data['remarks']);
$actionType = mysqli_real_escape_string($connect, ucfirst(strtolower($data['action_type'])));

// Check if return request already exists for this order and product
$checkQuery = "SELECT * FROM returntb WHERE OrderID = '$orderID' AND ProductID = '$productID'";
$checkResult = mysqli_query($connect, $checkQuery);

if (mysqli_num_rows($checkResult) > 0) {
    $existing = mysqli_fetch_assoc($checkResult);

    // Prevent opposite action type
    if ($existing['ActionType'] !== $actionType) {
        echo json_encode([
            'success' => false,
            'message' => $actionType . ' request denied. Existing ' . $existing['ActionType'] . ' request found.'
        ]);
        exit();
    }

    echo json_encode([
        'success' => false,
        'message' => $actionType . ' request for this product in this order already exists.'
    ]);
    exit();
}

$returnID = uniqid('REX_'); // Generate unique ReturnID

// Insert the return/exchange request
$sql = "INSERT INTO returntb (ReturnID, OrderID, ProductID, UserID, ActionType, ReturnQuantity, Status, RequestDate, Remarks)
        VALUES ('$returnID', '$orderID', '$productID', '$userID', '$actionType', $quantity, 'Pending', NOW(), '$remarks')";

if (mysqli_query($connect, $sql)) {
    echo json_encode(['success' => true, 'message' => "$actionType request added successfully."]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($connect)]);
}
