<?php
require_once(__DIR__ . '/../../config/db_connection.php');
include(__DIR__ . '/../admin_pagination.php');
?>

<!-- Pagination Info -->
<div class="text-gray-500 text-sm" id="paginationInfo">
    <?php
    $currentCount = min($rowsPerPage, $bookingCount - (($reservationCurrentPage - 1) * $rowsPerPage));
    echo 'Showing ' . $currentCount . ' of ' . $bookingCount . ' reservations';
    ?>
</div>

<!-- Pagination Controls -->
<div class="flex justify-center items-center mt-1 gap-1 <?= (!empty($bookingCount)) ? 'flex' : 'hidden' ?>" id="paginationControls">
    <!-- Previous Btn -->
    <?php if ($reservationCurrentPage > 1): ?>
        <a href="#" class="prev-page-btn px-3 py-1 border rounded text-gray-600 bg-white hover:bg-gray-100">
            <i class="ri-arrow-left-s-line"></i>
        </a>
    <?php else: ?>
        <span class="px-3 py-1 border rounded cursor-not-allowed bg-gray-100 border-gray-300 text-gray-600">
            <i class="ri-arrow-left-s-line"></i>
        </span>
    <?php endif; ?>

    <?php for ($p = 1; $p <= $totalReservationPages; $p++): ?>
        <a href="#" class="page-btn px-3 py-1 border rounded text-gray-600 select-none <?= $p == $reservationCurrentPage ? 'bg-gray-100 border-gray-300' : 'bg-white hover:bg-gray-100' ?>" data-page="<?= $p ?>">
            <?= $p ?>
        </a>
    <?php endfor; ?>

    <!-- Next Btn -->
    <?php if ($reservationCurrentPage < $totalReservationPages): ?>
        <a href="#" class="next-page-btn px-3 py-1 border rounded text-gray-600 bg-white hover:bg-gray-100">
            <i class="ri-arrow-right-s-line"></i>
        </a>
    <?php else: ?>
        <span class="px-3 py-1 border rounded cursor-not-allowed bg-gray-100 border-gray-300 text-gray-600">
            <i class="ri-arrow-right-s-line"></i>
        </span>
    <?php endif; ?>
</div>