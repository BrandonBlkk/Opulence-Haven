<?php
// Set the number of rows per page
$rowsPerPage = 4;

// Get the current page number from the URL or default to 1
$currentPage = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

// Calculate the offset for the query
$offset = ($currentPage - 1) * $rowsPerPage;

// Construct the facility count query based on search
$reivewQuery = "SELECT COUNT(*) as count FROM productreviewtb";

// Execute the count query
$reviewResult = $connect->query($reivewQuery);
$reviewCount = $reviewResult->fetch_assoc()['count'];

// Calculate total pages
$totalPages = ceil($reviewCount / $rowsPerPage);
