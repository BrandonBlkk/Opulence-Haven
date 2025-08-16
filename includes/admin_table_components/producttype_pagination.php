<?php
require_once(__DIR__ . '/../../config/db_connection.php');
include(__DIR__ . '/../admin_pagination.php');
?>

<!-- Pagination Info -->
<div class="text-gray-500 text-sm" id="paginationInfo">
    <?php
    $currentCount = min($rowsPerPage, $productTypeCount - (($productTypeCurrentPage - 1) * $rowsPerPage));
    echo 'Showing ' . $currentCount . ' of ' . $productTypeCount . ' product types';
    ?>
</div>

<!-- Pagination Controls -->
<div class="flex justify-center items-center mt-1 gap-1 <?= (!empty($productTypeCount)) ? 'flex' : 'hidden' ?>" id="paginationControls">
    <!-- Previous Btn -->
    <?php if ($productTypeCurrentPage > 1): ?>
        <a href="#" onclick="loadProductTypePage(<?= $productTypeCurrentPage - 1 ?>); return false;"
            class="px-3 py-1 border rounded text-gray-600 <?= ($productTypeCurrentPage - 1) == $productTypeCurrentPage ? 'bg-gray-100 border-gray-300' : 'bg-white' ?> hover:bg-gray-100">
            <i class="ri-arrow-left-s-line"></i>
        </a>
    <?php else: ?>
        <span class="px-3 py-1 border rounded cursor-not-allowed bg-gray-100 border-gray-300 text-gray-600">
            <i class="ri-arrow-left-s-line"></i>
        </span>
    <?php endif; ?>

    <?php for ($p = 1; $p <= $totalProductTypePages; $p++): ?>
        <a href="#" onclick="loadProductTypePage(<?= $p ?>); return false;"
            class="px-3 py-1 border rounded text-gray-600 select-none <?= $p == $productTypeCurrentPage ? 'bg-gray-100 border-gray-300' : 'bg-white hover:bg-gray-100' ?>">
            <?= $p ?>
        </a>
    <?php endfor; ?>

    <!-- Next Btn -->
    <?php if ($productTypeCurrentPage < $totalProductTypePages): ?>
        <a href="#" onclick="loadProductTypePage(<?= $productTypeCurrentPage + 1 ?>); return false;"
            class="px-3 py-1 border rounded text-gray-600 <?= ($productTypeCurrentPage + 1) == $productTypeCurrentPage ? 'bg-gray-100 border-gray-300' : 'bg-white' ?> hover:bg-gray-100">
            <i class="ri-arrow-right-s-line"></i>
        </a>
    <?php else: ?>
        <span class="px-3 py-1 border rounded cursor-not-allowed bg-gray-100 border-gray-300 text-gray-600">
            <i class="ri-arrow-right-s-line"></i>
        </span>
    <?php endif; ?>
</div>