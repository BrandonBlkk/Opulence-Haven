<?php
include(__DIR__ . '/../../config/db_connection.php');
include(__DIR__ . '/../admin_pagination.php');
?>

<!-- Pagination Info -->
<div class="text-gray-500 text-sm" id="paginationInfo">
    <?php
    $currentCount = min($rowsPerPage, $supplierCount - (($supplierCurrentPage - 1) * $rowsPerPage));
    echo 'Showing ' . $currentCount . ' of ' . $supplierCount . ' suppliers';
    ?>
</div>

<!-- Pagination Controls -->
<div class="flex justify-center items-center mt-1 gap-1 <?= (!empty($supplierCount)) ? 'flex' : 'hidden' ?>" id="paginationControls">
    <!-- Previous Btn -->
    <?php if ($supplierCurrentPage > 1): ?>
        <a href="#" onclick="loadSupplierPage(<?= $supplierCurrentPage - 1 ?>, '<?= htmlspecialchars($searchSupplierQuery) ?>', '<?= $filterSupplierID ?>'); return false;"
            class="px-3 py-1 border rounded text-gray-600 <?= ($supplierCurrentPage - 1) == $supplierCurrentPage ? 'bg-gray-100 border-gray-300' : 'bg-white' ?> hover:bg-gray-100">
            <i class="ri-arrow-left-s-line"></i>
        </a>
    <?php else: ?>
        <span class="px-3 py-1 border rounded cursor-not-allowed bg-gray-100 border-gray-300 text-gray-600">
            <i class="ri-arrow-left-s-line"></i>
        </span>
    <?php endif; ?>

    <?php for ($p = 1; $p <= $totalSupplierPages; $p++): ?>
        <a href="#" onclick="loadSupplierPage(<?= $p ?>, '<?= htmlspecialchars($searchSupplierQuery) ?>', '<?= $filterSupplierID ?>'); return false;"
            class="px-3 py-1 border rounded text-gray-600 select-none <?= $p == $supplierCurrentPage ? 'bg-gray-100 border-gray-300' : 'bg-white hover:bg-gray-100' ?>">
            <?= $p ?>
        </a>
    <?php endfor; ?>

    <!-- Next Btn -->
    <?php if ($supplierCurrentPage < $totalSupplierPages): ?>
        <a href="#" onclick="loadSupplierPage(<?= $supplierCurrentPage + 1 ?>, '<?= htmlspecialchars($searchSupplierQuery) ?>', '<?= $filterSupplierID ?>'); return false;"
            class="px-3 py-1 border rounded text-gray-600 <?= ($supplierCurrentPage + 1) == $supplierCurrentPage ? 'bg-gray-100 border-gray-300' : 'bg-white' ?> hover:bg-gray-100">
            <i class="ri-arrow-right-s-line"></i>
        </a>
    <?php else: ?>
        <span class="px-3 py-1 border rounded cursor-not-allowed bg-gray-100 border-gray-300 text-gray-600">
            <i class="ri-arrow-right-s-line"></i>
        </span>
    <?php endif; ?>
</div>