<?php
session_start();
include('../config/db_connection.php');

ob_start();
include('../Store/cart.php'); // Render dropdown HTML as in your main code
$dropdownHTML = ob_get_clean();

$cartCount = 0;
if (isset($_SESSION['UserID'])) {
    $stmt = $connect->prepare("
        SELECT SUM(OrderUnitQuantity) as total 
        FROM orderdetailtb od
        JOIN ordertb o ON od.OrderID = o.OrderID
        WHERE o.UserID = ? AND o.Status = 'pending'
    ");
    $stmt->bind_param("s", $_SESSION['UserID']);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $cartCount = $row['total'] ?? 0;
}

echo json_encode([
    'countText' => $cartCount . ' item' . ($cartCount != 1 ? 's' : ''),
    'dropdownHTML' => $dropdownHTML
]);
