<?php
require_once(__DIR__ . '/../../config/db_connection.php');
include(__DIR__ . '/../admin_pagination.php');

// Get current page from GET, default to 1
$reservationCurrentPage = isset($_GET['reservationpage']) ? (int)$_GET['reservationpage'] : 1;
$rowsPerPage = $rowsPerPage ?? 10;

// Total reservation count based on search and filter
$searchBookingQuery = isset($_GET['reservation_search']) ? $_GET['reservation_search'] : '';
$filterStatus = $filterStatus ?? 'random';

$countQuery = "SELECT COUNT(*) AS total 
               FROM reservationtb r 
               JOIN usertb u ON r.UserID = u.UserID 
               WHERE r.Status != 'Pending'";

if ($filterStatus !== 'random') {
    $countQuery .= " AND r.Status = '$filterStatus'";
}
if (!empty($searchBookingQuery)) {
    $countQuery .= " AND (r.FirstName LIKE '%$searchBookingQuery%' 
                           OR r.LastName LIKE '%$searchBookingQuery%'
                           OR r.UserPhone LIKE '%$searchBookingQuery%'
                           OR r.ReservationID LIKE '%$searchBookingQuery%'
                           OR u.UserName LIKE '%$searchBookingQuery%')";
}

$resultCount = $connect->query($countQuery);
$rowCount = $resultCount->fetch_assoc();
$bookingCount = $rowCount['total'];
$totalReservationPages = ceil($bookingCount / $rowsPerPage);

$currentCount = min($rowsPerPage, $bookingCount - (($reservationCurrentPage - 1) * $rowsPerPage));
?>

<div class="text-gray-500 text-sm" id="paginationInfo">
    Showing <?= $currentCount ?> of <?= $bookingCount ?> reservations
</div>

<div class="flex justify-center items-center mt-1 gap-1 <?= (!empty($bookingCount)) ? 'flex' : 'hidden' ?>" id="paginationControls">
    <?php if ($reservationCurrentPage > 1): ?>
        <a href="#" data-page="<?= $reservationCurrentPage - 1 ?>" class="prev-page-btn px-3 py-1 border rounded text-gray-600 bg-white hover:bg-gray-100">
            <i class="ri-arrow-left-s-line"></i>
        </a>
    <?php else: ?>
        <span class="px-3 py-1 border rounded cursor-not-allowed bg-gray-100 border-gray-300 text-gray-600">
            <i class="ri-arrow-left-s-line"></i>
        </span>
    <?php endif; ?>

    <?php for ($p = 1; $p <= $totalReservationPages; $p++): ?>
        <a href="#" data-page="<?= $p ?>" class="page-btn px-3 py-1 border rounded text-gray-600 select-none <?= $p == $reservationCurrentPage ? 'bg-gray-100 border-gray-300' : 'bg-white hover:bg-gray-100' ?>">
            <?= $p ?>
        </a>
    <?php endfor; ?>

    <?php if ($reservationCurrentPage < $totalReservationPages): ?>
        <a href="#" data-page="<?= $reservationCurrentPage + 1 ?>" class="next-page-btn px-3 py-1 border rounded text-gray-600 bg-white hover:bg-gray-100">
            <i class="ri-arrow-right-s-line"></i>
        </a>
    <?php else: ?>
        <span class="px-3 py-1 border rounded cursor-not-allowed bg-gray-100 border-gray-300 text-gray-600">
            <i class="ri-arrow-right-s-line"></i>
        </span>
    <?php endif; ?>
</div>