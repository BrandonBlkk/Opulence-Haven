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
        // Update order status to Confirmed
        $update_status = $connect->prepare("UPDATE ordertb SET Status = 'Confirmed' WHERE OrderID = ?");
        $update_status->bind_param("s", $order_id);

        if ($update_status->execute()) {
            // Order status updated successfully
            // Redirect to a success page
            header("Location: store_checkout.php");
            exit();
        } else {
            // Handle error updating order status
            die("Error updating order status: " . $connect->error);
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
