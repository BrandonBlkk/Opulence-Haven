<?php
session_start();
include('../config/dbConnection.php');

if (isset($_SESSION['AdminID'])) {
    $admin_id = $_SESSION['AdminID'];

    // Update the user's status to inactive
    $updateSignInQuery = "UPDATE admintb SET Status = 'inactive' WHERE AdminID = '$admin_id'";
    if (!mysqli_query($connect, $updateSignInQuery)) {
        http_response_code(500);
        exit("Failed to update admin status.");
    }
}

// Destroy the session
session_unset();
session_destroy();

http_response_code(200);
exit();
