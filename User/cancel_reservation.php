<?php
session_start();
require_once('../config/db_connection.php');

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$reservationId = $data['reservationId'] ?? null;

if (!$reservationId) {
    echo json_encode(['success' => false, 'message' => 'Reservation ID missing']);
    exit;
}

// Update reservation status
$sql = "UPDATE reservationtb SET Status='Cancelled' WHERE ReservationID=?";
$stmt = $connect->prepare($sql);
$stmt->bind_param("s", $reservationId);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}

$stmt->close();
$connect->close();
