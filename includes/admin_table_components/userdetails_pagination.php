<?php
include(__DIR__ . '/../../config/dbConnection.php');
include(__DIR__ . '/../AdminPagination.php');
?>

<!-- Pagination Info -->
<div class="text-gray-500 text-sm" id="paginationInfo">
    <?php
    $currentCount = min($rowsPerPage, $userCount - (($userCurrentPage - 1) * $rowsPerPage));
    echo 'Showing ' . $currentCount . ' of ' . $userCount . ' users';
    ?>
</div>

<!-- Pagination Controls -->
<div class="flex justify-center items-center mt-1 gap-1 <?= (!empty($userCount)) ? 'flex' : 'hidden' ?>" id="paginationControls">
    <!-- Previous Btn -->
    <?php if ($userCurrentPage > 1): ?>
        <a href="#" onclick="loadUserPage(<?= $userCurrentPage - 1 ?>, '<?= htmlspecialchars($searchUserQuery) ?>', '<?= $filterMembershipID ?>'); return false;"
            class="px-3 py-1 border rounded text-gray-600 <?= ($userCurrentPage - 1) == $userCurrentPage ? 'bg-gray-100 border-gray-300' : 'bg-white' ?> hover:bg-gray-100">
            <i class="ri-arrow-left-s-line"></i>
        </a>
    <?php else: ?>
        <span class="px-3 py-1 border rounded cursor-not-allowed bg-gray-100 border-gray-300 text-gray-600">
            <i class="ri-arrow-left-s-line"></i>
        </span>
    <?php endif; ?>

    <?php for ($p = 1; $p <= $totalUserPages; $p++): ?>
        <a href="#" onclick="loadUserPage(<?= $p ?>, '<?= htmlspecialchars($searchUserQuery) ?>', '<?= $filterMembershipID ?>'); return false;"
            class="px-3 py-1 border rounded text-gray-600 select-none <?= $p == $userCurrentPage ? 'bg-gray-100 border-gray-300' : 'bg-white hover:bg-gray-100' ?>">
            <?= $p ?>
        </a>
    <?php endfor; ?>

    <!-- Next Btn -->
    <?php if ($userCurrentPage < $totalUserPages): ?>
        <a href="#" onclick="loadUserPage(<?= $userCurrentPage + 1 ?>, '<?= htmlspecialchars($searchUserQuery) ?>', '<?= $filterMembershipID ?>'); return false;"
            class="px-3 py-1 border rounded text-gray-600 <?= ($userCurrentPage + 1) == $userCurrentPage ? 'bg-gray-100 border-gray-300' : 'bg-white' ?> hover:bg-gray-100">
            <i class="ri-arrow-right-s-line"></i>
        </a>
    <?php else: ?>
        <span class="px-3 py-1 border rounded cursor-not-allowed bg-gray-100 border-gray-300 text-gray-600">
            <i class="ri-arrow-right-s-line"></i>
        </span>
    <?php endif; ?>
</div>