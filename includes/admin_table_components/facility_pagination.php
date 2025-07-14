<?php
include(__DIR__ . '/../../config/db_connection.php');
include(__DIR__ . '/../admin_pagination.php');
?>

<div class="text-gray-500 text-sm" id="paginationInfo">
    <?php
    $currentCount = min($rowsPerPage, $facilityCount - (($currentPage - 1) * $rowsPerPage));
    echo 'Showing ' . $currentCount . ' of ' . $facilityCount . ' facilities';
    ?>
</div>
<div class="flex justify-center items-center mt-1 gap-1 <?= (!empty($facilityCount)) ? 'flex' : 'hidden' ?>" id="paginationControls">
    <!-- Previous Btn -->
    <?php if ($currentPage > 1): ?>
        <a href="#" onclick="loadPage(<?= $currentPage - 1 ?>); return false;"
            class="px-3 py-1 border rounded text-gray-600 <?= ($currentPage - 1) == $currentPage ? 'bg-gray-100 border-gray-300' : 'bg-white' ?> hover:bg-gray-100">
            <i class="ri-arrow-left-s-line"></i>
        </a>
    <?php else: ?>
        <span class="px-3 py-1 border rounded cursor-not-allowed bg-gray-100 border-gray-300 text-gray-600">
            <i class="ri-arrow-left-s-line"></i>
        </span>
    <?php endif; ?>

    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
        <a href="#" onclick="loadPage(<?= $p ?>); return false;"
            class="px-3 py-1 border rounded text-gray-600 select-none <?= $p == $currentPage ? 'bg-gray-100 border-gray-300' : 'bg-white hover:bg-gray-100' ?>">
            <?= $p ?>
        </a>
    <?php endfor; ?>

    <!-- Next Btn -->
    <?php if ($currentPage < $totalPages): ?>
        <a href="#" onclick="loadPage(<?= $currentPage + 1 ?>); return false;"
            class="px-3 py-1 border rounded text-gray-600 <?= ($currentPage + 1) == $currentPage ? 'bg-gray-100 border-gray-300' : 'bg-white' ?> hover:bg-gray-100">
            <i class="ri-arrow-right-s-line"></i>
        </a>
    <?php else: ?>
        <span class="px-3 py-1 border rounded cursor-not-allowed bg-gray-100 border-gray-300 text-gray-600">
            <i class="ri-arrow-right-s-line"></i>
        </span>
    <?php endif; ?>
</div>