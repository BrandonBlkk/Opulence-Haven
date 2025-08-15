<?php
session_start();
require_once('../config/db_connection.php');

if (isset($_SESSION['UserID'])) {
    $user_id = $_SESSION['UserID'];

    // Delete the user account
    $accountDeleteQuery = "DELETE FROM usertb WHERE UserID = '$user_id'";
    if (!$connect->query($accountDeleteQuery)) {
        http_response_code(500);
        exit("Failed to delete an account.");
    }
}

// Destroy the session
session_unset();
session_destroy();

http_response_code(200);
exit();
