<?php
session_start();
require_once('../config/db_connection.php');

header('Content-Type: application/json');

if (!isset($_POST['order_id']) || empty($_POST['order_id'])) {
    echo json_encode(['success' => false, 'message' => 'Order ID is missing.']);
    exit;
}

$orderId = $_POST['order_id'];

// Ensure the connection is valid
if (!$connect) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

// Update the order status to Cancelled
$query = "UPDATE ordertb SET Status='Cancelled' WHERE OrderID=?";
$stmt = mysqli_prepare($connect, $query);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => mysqli_error($connect)]);
    exit;
}

mysqli_stmt_bind_param($stmt, 's', $orderId);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => mysqli_error($connect)]);
}

mysqli_stmt_close($stmt);
mysqli_close($connect);
