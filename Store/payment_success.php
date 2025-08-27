<?php
session_start();
require_once('../config/db_connection.php');

// Handle payment success callback
if (isset($_GET['payment']) && $_GET['payment'] == 'success' && isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];
    $userID = $_SESSION['UserID'];

    // Verify the order belongs to the current user
    $verify_order = $connect->prepare("SELECT OrderID FROM ordertb WHERE OrderID = ? AND UserID = ?");
    $verify_order->bind_param("ss", $order_id, $userID);
    $verify_order->execute();
    $order_result = $verify_order->get_result();

    if ($order_result->num_rows > 0) {

        // Calculate subtotal from order details
        $subtotal = 0;
        $order_items_query = $connect->prepare("SELECT OrderUnitQuantity, OrderUnitPrice FROM orderdetailtb WHERE OrderID = ?");
        $order_items_query->bind_param("s", $order_id);
        $order_items_query->execute();
        $items_result = $order_items_query->get_result();

        while ($item = $items_result->fetch_assoc()) {
            $subtotal += $item['OrderUnitQuantity'] * $item['OrderUnitPrice'];
        }

        // Calculate tax (10%) and total
        $deliveryFee = 5.00;
        $tax = $subtotal * 0.10;
        $total_price = $subtotal + $tax + $deliveryFee;

        // Update order status, total price, and tax
        $update_status = $connect->prepare("UPDATE ordertb SET Status = 'Processing', TotalPrice = ?, OrderTax = ? WHERE OrderID = ?");
        $update_status->bind_param("dds", $total_price, $tax, $order_id);

        if ($update_status->execute()) {
            // Order updated successfully
            header("Location: store_checkout.php");
            exit();
        } else {
            die("Error updating order status and total: " . $connect->error);
        }
    } else {
        // Order doesn't belong to user or doesn't exist
        die("Invalid order");
    }
} else {
    // Invalid access to this page
    header("Location: store_checkout.php");
    exit();
}
