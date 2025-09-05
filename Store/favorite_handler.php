<?php
session_start();
require_once('../config/db_connection.php');

$response = ['success' => false, 'message' => '', 'status' => '', 'action' => '', 'icon' => '', 'tooltip' => ''];

$product_id = isset($_POST['product_id']) ? $connect->real_escape_string($_POST['product_id']) : '';
$action = isset($_POST['action']) ? $_POST['action'] : '';
$userID = isset($_SESSION['UserID']) ? $_SESSION['UserID'] : null;

if (!$userID) {
    $response['status'] = 'not_logged_in';
    $response['message'] = 'Login first to add to favorites.';
    echo json_encode($response);
    exit();
}

if ($action === 'add') {
    $check_query = "SELECT COUNT(*) as count FROM productfavoritetb WHERE UserID = '$userID' AND ProductID = '$product_id'";
    $result = $connect->query($check_query);
    $count = $result->fetch_assoc()['count'];

    if ($count == 0) {
        $insert_query = "INSERT INTO productfavoritetb (UserID, ProductID) VALUES ('$userID', '$product_id')";
        if ($connect->query($insert_query)) {
            $response['success'] = true;
            $response['action'] = 'added';
            $response['icon'] = 'ri-heart-fill text-amber-500';
            $response['tooltip'] = 'Remove from Favorites';
        }
    } else {
        $response['success'] = false;
        $response['message'] = 'Already in favorites.';
    }
} elseif ($action === 'remove') {
    $delete_query = "DELETE FROM productfavoritetb WHERE UserID = '$userID' AND ProductID = '$product_id'";
    if ($connect->query($delete_query)) {
        $response['success'] = true;
        $response['action'] = 'removed';
        $response['icon'] = 'ri-heart-line text-gray-400';
        $response['tooltip'] = 'Add to Favorites';
    }
}

echo json_encode($response);
