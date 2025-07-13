<?php

// Timezone 
date_default_timezone_set('Asia/Yangon');
// Function to cleanup expired reservations
function cleanupExpiredReservations($connect)
{
    try {
        // Get current datetime 
        $currentDateTime = date('Y-m-d H:i:s');

        // Get all expired reservation IDs
        $getExpiredQuery = "SELECT ReservationID FROM reservationtb 
                          WHERE Status = 'Pending' AND ExpiryDate <= ?";
        $stmtGet = $connect->prepare($getExpiredQuery);
        $stmtGet->bind_param("s", $currentDateTime);
        $stmtGet->execute();
        $expiredResult = $stmtGet->get_result();

        // Process each expired reservation
        while ($row = $expiredResult->fetch_assoc()) {
            $reservationID = $row['ReservationID'];

            // Update room status to Available
            $updateRoomsQuery = "UPDATE roomtb SET RoomStatus = 'Available' 
                                WHERE RoomID IN (SELECT RoomID FROM reservationdetailtb WHERE ReservationID = ?)";
            $stmtRooms = $connect->prepare($updateRoomsQuery);
            $stmtRooms->bind_param("s", $reservationID);
            $stmtRooms->execute();

            // Delete from reservationdetailtb first (due to foreign key constraints)
            $deleteDetails = "DELETE FROM reservationdetailtb WHERE ReservationID = ?";
            $stmtDetails = $connect->prepare($deleteDetails);
            $stmtDetails->bind_param("s", $reservationID);
            $stmtDetails->execute();

            // Then delete from reservationtb
            $deleteReservation = "DELETE FROM reservationtb WHERE ReservationID = ?";
            $stmtReservation = $connect->prepare($deleteReservation);
            $stmtReservation->bind_param("s", $reservationID);
            $stmtReservation->execute();
        }

        return true;
    } catch (Exception $e) {
        error_log("Cleanup failed: " . $e->getMessage());
        return false;
    }
}

// Call this function at the start of your reservation processing
cleanupExpiredReservations($connect);
