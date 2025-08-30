<?php
ob_start();
session_start();
require_once('../config/db_connection.php');

header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL);

$res = ['success' => false, 'message' => 'Unknown error'];

try {
    if (!isset($_SESSION['UserID'])) {
        throw new Exception('Unauthorized');
    }
    $userId = $_SESSION['UserID'];

    if (empty($_POST['order_id'])) throw new Exception('Missing order_id');
    if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        throw new Exception('Invalid CSRF token');
    }
    $orderId = $_POST['order_id'];

    // Load order & check ownership
    $qOrder = "SELECT * FROM ordertb WHERE OrderID = ? AND UserID = ?";
    $stmt = mysqli_prepare($connect, $qOrder);
    mysqli_stmt_bind_param($stmt, 'ss', $orderId, $userId);
    mysqli_stmt_execute($stmt);
    $order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$order) throw new Exception('Order not found');
    if (!in_array($order['Status'], ['Order Placed', 'Processing'])) {
        throw new Exception('Order cannot be modified at its current status');
    }

    // Store original order in session for rollback
    $_SESSION['original_order_' . $orderId] = $order;

    // Start transaction
    mysqli_begin_transaction($connect);

    // 1) Update shipping fields
    $FullName = $_POST['FullName'] ?? $order['FullName'];
    $PhoneNumber = $_POST['PhoneNumber'] ?? $order['PhoneNumber'];
    $ShippingAddress = $_POST['ShippingAddress'] ?? $order['ShippingAddress'];
    $City = $_POST['City'] ?? $order['City'];
    $State = $_POST['State'] ?? $order['State'];
    $ZipCode = $_POST['ZipCode'] ?? $order['ZipCode'];

    $qUpdateOrder = "
    UPDATE ordertb
    SET FullName=?, PhoneNumber=?, ShippingAddress=?, City=?, State=?, ZipCode=?
    WHERE OrderID=? AND UserID=?
    ";
    $stmt1 = mysqli_prepare($connect, $qUpdateOrder);
    mysqli_stmt_bind_param($stmt1, 'ssssssss', $FullName, $PhoneNumber, $ShippingAddress, $City, $State, $ZipCode, $orderId, $userId);
    if (!mysqli_stmt_execute($stmt1)) throw new Exception('Failed to update shipping info');
    mysqli_stmt_close($stmt1);

    // 2) Update quantities (qty[productId][sizeId] => value)
    $qty = $_POST['qty'] ?? [];

    // Get all current lines for the order
    $qLines = "SELECT ProductID, SizeID, OrderUnitPrice, OrderUnitQuantity FROM orderdetailtb WHERE OrderID=?";
    $stmt2 = mysqli_prepare($connect, $qLines);
    mysqli_stmt_bind_param($stmt2, 's', $orderId);
    mysqli_stmt_execute($stmt2);
    $resLines = mysqli_stmt_get_result($stmt2);
    $lines = [];
    while ($row = mysqli_fetch_assoc($resLines)) {
        $key = $row['ProductID'] . '|' . $row['SizeID'];
        $lines[$key] = $row;
    }
    mysqli_stmt_close($stmt2);

    // Store original line items in session
    $_SESSION['original_lines_' . $orderId] = $lines;

    // Update each line that exists
    foreach ($lines as $key => $line) {
        list($pid, $sizeId) = explode('|', $key);
        $newQty = (int)($qty[$pid][$sizeId] ?? 0);
        if ($newQty < 1) $newQty = 1;

        $qUpd = "UPDATE orderdetailtb SET OrderUnitQuantity=? WHERE OrderID=? AND ProductID=? AND SizeID=?";
        $stmtU = mysqli_prepare($connect, $qUpd);
        mysqli_stmt_bind_param($stmtU, 'isss', $newQty, $orderId, $pid, $sizeId);
        if (!mysqli_stmt_execute($stmtU)) throw new Exception('Failed to update item quantity');
        mysqli_stmt_close($stmtU);
    }

    // 3) Recalculate totals
    $qSum = "SELECT SUM(OrderUnitQuantity * OrderUnitPrice) AS items_total FROM orderdetailtb WHERE OrderID=?";
    $stmt3 = mysqli_prepare($connect, $qSum);
    mysqli_stmt_bind_param($stmt3, 's', $orderId);
    mysqli_stmt_execute($stmt3);
    $sumRow = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt3));
    mysqli_stmt_close($stmt3);

    $itemsTotal = (float)($sumRow['items_total'] ?? 0);
    $orderTax = (float)$order['OrderTax']; // keep existing tax
    $deliveryFee = 5.00; // Fixed delivery fee

    $newGrandTotal = $itemsTotal + $orderTax + $deliveryFee;

    $previousTotal = (float)($order['TotalPrice'] ?? 0);
    $additionalAmount = max(0, $newGrandTotal - $previousTotal);

    $qTotals = "UPDATE ordertb SET TotalPrice=?, AdditionalAmount=? WHERE OrderID=?";
    $stmt4 = mysqli_prepare($connect, $qTotals);
    mysqli_stmt_bind_param($stmt4, 'dds', $newGrandTotal, $additionalAmount, $orderId);
    if (!mysqli_stmt_execute($stmt4)) throw new Exception('Failed to update totals');
    mysqli_stmt_close($stmt4);

    mysqli_commit($connect);

    $res = ['success' => true, 'additional_due' => $additionalAmount];
} catch (Exception $e) {
    if (isset($connect) && $connect) {
        mysqli_rollback($connect);
    }
    $res = ['success' => false, 'message' => $e->getMessage()];
}

ob_clean();
echo json_encode($res);
exit;
