<?php
include(__DIR__ . '/../../config/dbConnection.php');
include(__DIR__ . '/../AdminPagination.php');
?>

<div class="text-gray-500 text-sm" id="paginationInfo">
    <?php
    $currentCount = min($rowsPerPage, $totalFacilityTypeRows - (($facilityTypeCurrentPage - 1) * $rowsPerPage));
    echo 'Showing ' . $currentCount . ' of ' . $totalFacilityTypeRows . ' facility types';
    ?>
</div>
<div class="flex justify-center items-center mt-1 gap-1 <?= (!empty($totalFacilityTypeRows)) ? 'flex' : 'hidden' ?>" id="paginationControls">
    <!-- Previous Btn -->
    <?php if ($facilityTypeCurrentPage > 1): ?>
        <a href="#" onclick="loadFacilityTypePage(<?= $facilityTypeCurrentPage - 1 ?>); return false;"
            class="px-3 py-1 border rounded text-gray-600 <?= ($facilityTypeCurrentPage - 1) == $facilityTypeCurrentPage ? 'bg-gray-100 border-gray-300' : 'bg-white' ?> hover:bg-gray-100">
            <i class="ri-arrow-left-s-line"></i>
        </a>
    <?php else: ?>
        <span class="px-3 py-1 border rounded cursor-not-allowed bg-gray-100 border-gray-300 text-gray-600">
            <i class="ri-arrow-left-s-line"></i>
        </span>
    <?php endif; ?>

    <?php for ($p = 1; $p <= $totalFacilityTypePages; $p++): ?>
        <a href="#" onclick="loadFacilityTypePage(<?= $p ?>); return false;"
            class="px-3 py-1 border rounded text-gray-600 select-none <?= $p == $facilityTypeCurrentPage ? 'bg-gray-100 border-gray-300' : 'bg-white hover:bg-gray-100' ?>">
            <?= $p ?>
        </a>
    <?php endfor; ?>

    <!-- Next Btn -->
    <?php if ($facilityTypeCurrentPage < $totalFacilityTypePages): ?>
        <a href="#" onclick="loadFacilityTypePage(<?= $facilityTypeCurrentPage + 1 ?>); return false;"
            class="px-3 py-1 border rounded text-gray-600 <?= ($facilityTypeCurrentPage + 1) == $facilityTypeCurrentPage ? 'bg-gray-100 border-gray-300' : 'bg-white' ?> hover:bg-gray-100">
            <i class="ri-arrow-right-s-line"></i>
        </a>
    <?php else: ?>
        <span class="px-3 py-1 border rounded cursor-not-allowed bg-gray-100 border-gray-300 text-gray-600">
            <i class="ri-arrow-right-s-line"></i>
        </span>
    <?php endif; ?>
</div>