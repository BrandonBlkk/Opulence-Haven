<?php
session_start();
require_once('../config/db_connection.php');

if (isset($_SESSION['UserID'])) {
    $user_id = $_SESSION['UserID'];

    // Update the user's status to inactive
    $updateSignInQuery = "UPDATE usertb SET Status = 'inactive', LastSignIn = NOW() WHERE UserID = '$user_id'";
    if (!$connect->query($updateSignInQuery)) {
        http_response_code(500);
        exit("Failed to update user status.");
    }
}

// Destroy the session
session_unset();
session_destroy();

http_response_code(200);
exit();
