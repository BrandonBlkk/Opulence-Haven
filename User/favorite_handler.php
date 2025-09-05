<?php
session_start();
require_once('../config/db_connection.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

$userID = (!empty($_SESSION["UserID"]) ? $_SESSION["UserID"] : null);

if (isset($_POST['room_favourite'])) {
    // Initialize response
    $res = ['status' => '', 'error' => ''];

    if (isset($_SESSION['UserID']) && $_SESSION['UserID']) {
        $userID = $_SESSION['UserID'];
        $roomTypeID = $connect->real_escape_string($_POST['roomTypeID']);
        $checkin_date = isset($_POST['checkin_date']) ? $connect->real_escape_string($_POST['checkin_date']) : '';
        $checkout_date = isset($_POST['checkout_date']) ? $connect->real_escape_string($_POST['checkout_date']) : '';
        $adults = isset($_POST['adults']) ? intval($_POST['adults']) : 1;
        $children = isset($_POST['children']) ? intval($_POST['children']) : 0;

        // Check if already favorited
        $check = $connect->query("SELECT COUNT(*) as count FROM roomtypefavoritetb WHERE UserID = '$userID' AND RoomTypeID = '$roomTypeID'");

        if ($check && $row = $check->fetch_assoc()) {
            if ($row['count'] == 0) {
                // Add to favorites
                $insert = $connect->query("INSERT INTO roomtypefavoritetb (UserID, RoomTypeID, CheckInDate, CheckOutDate, Adult, Children) 
                                          VALUES ('$userID', '$roomTypeID', '$checkin_date', '$checkout_date', '$adults', '$children')");
                if ($insert) {
                    $res['status'] = 'added';
                } else {
                    $res['error'] = 'Insert failed: ' . $connect->error;
                }
            } else {
                // Remove from favorites
                $delete = $connect->query("DELETE FROM roomtypefavoritetb WHERE UserID = '$userID' AND RoomTypeID = '$roomTypeID'");
                if ($delete) {
                    $res['status'] = 'removed';
                } else {
                    $res['error'] = 'Delete failed: ' . $connect->error;
                }
            }
        } else {
            $res['error'] = 'Failed to check favorite status';
        }
    } else {
        $res['status'] = 'not_logged_in';
    }

    header('Content-Type: application/json');
    echo json_encode($res);
    exit();
}
