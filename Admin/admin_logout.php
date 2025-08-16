<?php
session_start();
require_once('../config/db_connection.php');

if (isset($_SESSION['AdminID'])) {
    $admin_id = $_SESSION['AdminID'];

    // Update the user's status to inactive
    $updateSignInQuery = "UPDATE admintb SET 
                     Status = 'inactive', 
                     LastSignIn = NOW() 
                     WHERE AdminID = '$admin_id'";
    if (!$connect->query($updateSignInQuery)) {
        http_response_code(500);
        exit("Failed to update admin status.");
    }
}

// Destroy the session
session_unset();
session_destroy();

http_response_code(200);
exit();
