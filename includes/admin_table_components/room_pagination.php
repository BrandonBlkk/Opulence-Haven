<?php
include(__DIR__ . '/../../config/dbConnection.php');
include(__DIR__ . '/../AdminPagination.php');
?>

<!-- Pagination Info -->
<div class="text-gray-500 text-sm" id="paginationInfo">
    <?php
    $currentCount = min($rowsPerPage, $roomCount - (($roomCurrentPage - 1) * $rowsPerPage));
    echo 'Showing ' . $currentCount . ' of ' . $roomCount . ' rooms';
    ?>
</div>

<!-- Pagination Controls -->
<div class="flex justify-center items-center mt-1 gap-1 <?= (!empty($roomCount)) ? 'flex' : 'hidden' ?>" id="paginationControls">
    <!-- Previous Btn -->
    <?php if ($roomCurrentPage > 1): ?>
        <a href="#" onclick="loadRoomPage(<?= $roomCurrentPage - 1 ?>); return false;"
            class="px-3 py-1 border rounded text-gray-600 <?= ($roomCurrentPage - 1) == $roomCurrentPage ? 'bg-gray-100 border-gray-300' : 'bg-white' ?> hover:bg-gray-100">
            <i class="ri-arrow-left-s-line"></i>
        </a>
    <?php else: ?>
        <span class="px-3 py-1 border rounded cursor-not-allowed bg-gray-100 border-gray-300 text-gray-600">
            <i class="ri-arrow-left-s-line"></i>
        </span>
    <?php endif; ?>

    <?php for ($p = 1; $p <= $totalRoomPages; $p++): ?>
        <a href="#" onclick="loadRoomPage(<?= $p ?>); return false;"
            class="px-3 py-1 border rounded text-gray-600 select-none <?= $p == $roomCurrentPage ? 'bg-gray-100 border-gray-300' : 'bg-white hover:bg-gray-100' ?>">
            <?= $p ?>
        </a>
    <?php endfor; ?>

    <!-- Next Btn -->
    <?php if ($roomCurrentPage < $totalRoomPages): ?>
        <a href="#" onclick="loadRoomPage(<?= $roomCurrentPage + 1 ?>); return false;"
            class="px-3 py-1 border rounded text-gray-600 <?= ($roomCurrentPage + 1) == $roomCurrentPage ? 'bg-gray-100 border-gray-300' : 'bg-white' ?> hover:bg-gray-100">
            <i class="ri-arrow-right-s-line"></i>
        </a>
    <?php else: ?>
        <span class="px-3 py-1 border rounded cursor-not-allowed bg-gray-100 border-gray-300 text-gray-600">
            <i class="ri-arrow-right-s-line"></i>
        </span>
    <?php endif; ?>
</div>