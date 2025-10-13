<?php
require_once(__DIR__ . '/../../config/db_connection.php');
include(__DIR__ . '/../admin_pagination.php');

// Get current page from GET, default to 1
$returnCurrentPage = isset($_GET['returnpage']) ? (int)$_GET['returnpage'] : 1;
$rowsPerPage = $rowsPerPage ?? 10;

// Total reservation count based on search and filter
$searchReturnQuery = isset($_GET['return_search']) ? $_GET['return_search'] : '';
$filterStatus = $filterStatus ?? 'random';

$countQuery = "
    SELECT COUNT(*) AS total 
    FROM returntb r
    JOIN ordertb o ON r.OrderID = o.OrderID
    JOIN usertb u ON r.UserID = u.UserID
    JOIN producttb p ON r.ProductID = p.ProductID";

if ($filterStatus !== 'random') {
    $countQuery .= " AND r.Status = '$filterStatus'";
}

if (!empty($searchReturnQuery)) {
    $countQuery .= " AND (
        u.UserName LIKE '%$searchReturnQuery%' 
        OR u.UserEmail LIKE '%$searchReturnQuery%' 
        OR u.UserPhone LIKE '%$searchReturnQuery%' 
        OR r.ReturnID LIKE '%$searchReturnQuery%' 
        OR o.OrderID LIKE '%$searchReturnQuery%'
        OR p.Title LIKE '%$searchReturnQuery%'
    )";
}

$resultCount = $connect->query($countQuery);
$rowCount = $resultCount->fetch_assoc();
$returnCount = $rowCount['total'];
$totalReturnPages = ceil($returnCount / $rowsPerPage);

$currentCount = min($rowsPerPage, $returnCount - (($returnCurrentPage - 1) * $rowsPerPage));
?>

<div class="text-gray-500 text-sm" id="paginationInfo">
    Showing <?= $currentCount ?> of <?= $returnCount ?> returns
</div>

<div class="flex justify-center items-center mt-1 gap-1 <?= (!empty($returnCount)) ? 'flex' : 'hidden' ?>" id="paginationControls">
    <?php if ($returnCurrentPage > 1): ?>
        <a href="#" data-page="<?= $returnCurrentPage - 1 ?>" class="prev-page-btn px-3 py-1 border rounded text-gray-600 bg-white hover:bg-gray-100">
            <i class="ri-arrow-left-s-line"></i>
        </a>
    <?php else: ?>
        <span class="px-3 py-1 border rounded cursor-not-allowed bg-gray-100 border-gray-300 text-gray-600">
            <i class="ri-arrow-left-s-line"></i>
        </span>
    <?php endif; ?>

    <?php for ($p = 1; $p <= $totalReturnPages; $p++): ?>
        <a href="#" data-page="<?= $p ?>" class="page-btn px-3 py-1 border rounded text-gray-600 select-none <?= $p == $returnCurrentPage ? 'bg-gray-100 border-gray-300' : 'bg-white hover:bg-gray-100' ?>">
            <?= $p ?>
        </a>
    <?php endfor; ?>

    <?php if ($returnCurrentPage < $totalReturnPages): ?>
        <a href="#" data-page="<?= $returnCurrentPage + 1 ?>" class="next-page-btn px-3 py-1 border rounded text-gray-600 bg-white hover:bg-gray-100">
            <i class="ri-arrow-right-s-line"></i>
        </a>
    <?php else: ?>
        <span class="px-3 py-1 border rounded cursor-not-allowed bg-gray-100 border-gray-300 text-gray-600">
            <i class="ri-arrow-right-s-line"></i>
        </span>
    <?php endif; ?>
</div>