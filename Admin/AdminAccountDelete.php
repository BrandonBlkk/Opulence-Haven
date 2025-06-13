<?php
session_start();
include('../config/dbConnection.php');

if (isset($_SESSION['AdminID'])) {
    $admin_id = $_SESSION['AdminID'];

    // Delete the admin account
    $accountDeleteQuery = "DELETE FROM admintb WHERE AdminID = ?";
    $stmt = $connect->prepare($accountDeleteQuery);
    $stmt->bind_param('s', $admin_id);
    $success = $stmt->execute();
    $stmt->close();

    if (!$success) {
        http_response_code(500);
        exit("Failed to delete an account.");
    }
}

// Destroy the session
session_unset();
session_destroy();

http_response_code(200);
exit();
