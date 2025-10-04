<?php
require_once(__DIR__ . '/../../config/db_connection.php');
include(__DIR__ . '/../admin_pagination.php');
?>

<!-- Pagination Info -->
<div class="text-gray-500 text-sm" id="paginationInfo">
    <?php
    $currentCount = min($rowsPerPage, $orderCount - (($orderCurrentPage - 1) * $rowsPerPage));
    echo 'Showing ' . $currentCount . ' of ' . $orderCount . ' orders';
    ?>
</div>

<!-- Pagination Controls -->
<div class="flex justify-center items-center mt-1 gap-1 <?= (!empty($orderCount)) ? 'flex' : 'hidden' ?>" id="paginationControls">
    <!-- Previous Btn -->
    <?php if ($orderCurrentPage > 1): ?>
        <a href="#" onclick="loadOrderPage(<?= $orderCurrentPage - 1 ?>); return false;"
            class="px-3 py-1 border rounded text-gray-600 <?= ($orderCurrentPage - 1) == $orderCurrentPage ? 'bg-gray-100 border-gray-300' : 'bg-white' ?> hover:bg-gray-100">
            <i class="ri-arrow-left-s-line"></i>
        </a>
    <?php else: ?>
        <span class="px-3 py-1 border rounded cursor-not-allowed bg-gray-100 border-gray-300 text-gray-600">
            <i class="ri-arrow-left-s-line"></i>
        </span>
    <?php endif; ?>

    <?php for ($p = 1; $p <= $totalOrderPages; $p++): ?>
        <a href="#" onclick="loadOrderPage(<?= $p ?>); return false;"
            class="px-3 py-1 border rounded text-gray-600 select-none <?= $p == $orderCurrentPage ? 'bg-gray-100 border-gray-300' : 'bg-white hover:bg-gray-100' ?>">
            <?= $p ?>
        </a>
    <?php endfor; ?>

    <!-- Next Btn -->
    <?php if ($orderCurrentPage < $totalOrderPages): ?>
        <a href="#" onclick="loadOrderPage(<?= $orderCurrentPage + 1 ?>); return false;"
            class="px-3 py-1 border rounded text-gray-600 <?= ($orderCurrentPage + 1) == $orderCurrentPage ? 'bg-gray-100 border-gray-300' : 'bg-white' ?> hover:bg-gray-100">
            <i class="ri-arrow-right-s-line"></i>
        </a>
    <?php else: ?>
        <span class="px-3 py-1 border rounded cursor-not-allowed bg-gray-100 border-gray-300 text-gray-600">
            <i class="ri-arrow-right-s-line"></i>
        </span>
    <?php endif; ?>
</div>