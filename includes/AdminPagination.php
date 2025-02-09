<?php
// Set the number of rows per page
$rowsPerPage = 10;

// Get the current page number from the URL or default to 1
$currentPage = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$contactCurrentPage = isset($_GET['contactpage']) && is_numeric($_GET['contactpage']) ? (int)$_GET['contactpage'] : 1;
$productTypeCurrentPage = isset($_GET['producttypepage']) && is_numeric($_GET['producttypepage']) ? (int)$_GET['producttypepage'] : 1;
$supplierCurrentPage = isset($_GET['supplierpage']) && is_numeric($_GET['supplierpage']) ? (int)$_GET['supplierpage'] : 1;
$productCurrentPage = isset($_GET['productpage']) && is_numeric($_GET['productpage']) ? (int)$_GET['productpage'] : 1;
$productSizeCurrentPage = isset($_GET['productsizepage']) && is_numeric($_GET['productsizepage']) ? (int)$_GET['productsizepage'] : 1;
$productImageCurrentPage = isset($_GET['productimagepage']) && is_numeric($_GET['productimagepage']) ? (int)$_GET['productimagepage'] : 1;
$roomTypeCurrentPage = isset($_GET['roomtypepage']) && is_numeric($_GET['roomtypepage']) ? (int)$_GET['roomtypepage'] : 1;
$facilityTypeCurrentPage = isset($_GET['facilitytypepage']) && is_numeric($_GET['facilitytypepage']) ? (int)$_GET['facilitytypepage'] : 1;
$facilityCurrentPage = isset($_GET['facilitypage']) && is_numeric($_GET['facilitypage']) ? (int)$_GET['facilitypage'] : 1;
$ruleCurrentPage = isset($_GET['rulepage']) && is_numeric($_GET['rulepage']) ? (int)$_GET['rulepage'] : 1;
$userCurrentPage = isset($_GET['userpage']) && is_numeric($_GET['userpage']) ? (int)$_GET['userpage'] : 1;


// Calculate the offset for the query
$offset = ($currentPage - 1) * $rowsPerPage;
$contactOffset = ($contactCurrentPage - 1) * $rowsPerPage;
$productTypeOffset = ($productTypeCurrentPage - 1) * $rowsPerPage;
$productOffset = ($productCurrentPage - 1) * $rowsPerPage;
$productSizeOffset = ($productSizeCurrentPage - 1) * $rowsPerPage;
$productImageOffset = ($productImageCurrentPage - 1) * $rowsPerPage;
$supplierOffset = ($supplierCurrentPage - 1) * $rowsPerPage;
$roomTypeOffset = ($roomTypeCurrentPage - 1) * $rowsPerPage;
$facilityTypeOffset = ($facilityTypeCurrentPage - 1) * $rowsPerPage;
$facilityOffset = ($facilityCurrentPage - 1) * $rowsPerPage;
$ruleOffset = ($ruleCurrentPage - 1) * $rowsPerPage;
$userOffset = ($userCurrentPage - 1) * $rowsPerPage;

$filterRoleID = isset($_GET['sort']) ? $_GET['sort'] : 'random';
$filterStatus = isset($_GET['sort']) ? $_GET['sort'] : 'random';
$filterSupplierID = isset($_GET['sort']) ? $_GET['sort'] : 'random';
$filterProductID = isset($_GET['sort']) ? $_GET['sort'] : 'random';
$filterSizes = isset($_GET['sort']) ? $_GET['sort'] : 'random';
$filterImages = isset($_GET['sort']) ? $_GET['sort'] : 'random';

$dateCondition = '';
if (!empty($searchFromDate) && !empty($searchToDate)) {
    $dateCondition = " AND ContactDate BETWEEN '$searchFromDate 00:00:00' AND '$searchToDate 23:59:59'";
} elseif (!empty($searchFromDate)) {
    $dateCondition = " AND ContactDate >= '$searchFromDate 00:00:00'";
} elseif (!empty($searchToDate)) {
    $dateCondition = " AND ContactDate <= '$searchToDate 23:59:59'";
}

// Fetch total number of rows for pagination calculation
$totalRowsQuery = "SELECT COUNT(*) as total FROM admintb";
if ($filterRoleID !== 'random' && !empty($searchAdminQuery)) {
    $totalRowsQuery = "SELECT COUNT(*) as total FROM admintb WHERE RoleID = '$filterRoleID' AND (FirstName LIKE '%$searchAdminQuery%' OR LastName LIKE '%$searchAdminQuery%' OR UserName LIKE '%$searchAdminQuery%' OR AdminEmail LIKE '%$searchAdminQuery%')";
} elseif ($filterRoleID !== 'random') {
    $totalRowsQuery = "SELECT COUNT(*) as total FROM admintb WHERE RoleID = '$filterRoleID'";
} elseif (!empty($searchAdminQuery)) {
    $totalRowsQuery = "SELECT COUNT(*) as total FROM admintb WHERE FirstName LIKE '%$searchAdminQuery%' OR LastName LIKE '%$searchAdminQuery%' OR UserName LIKE '%$searchAdminQuery%' OR AdminEmail LIKE '%$searchAdminQuery%'";
}
$totalRowsResult = $connect->query($totalRowsQuery);
$totalRows = $totalRowsResult->fetch_assoc()['total'];

// Calculate the total number of pages
$totalPages = ceil($totalRows / $rowsPerPage);

// Fetch total number of rows for pagination calculation
$totalProductTypeRowsQuery = "SELECT COUNT(*) as total FROM producttypetb";
if (!empty($productTypeSelectQuery)) {
    $totalProductTypeRowsQuery = "SELECT COUNT(*) as total FROM producttypetb WHERE ProductType LIKE '%$searchProductTypeQuery%' OR Description LIKE '%$searchProductTypeQuery%'";
}
$totalProductTypeRowsResult = $connect->query($totalProductTypeRowsQuery);
$totalProductTypeRows = $totalProductTypeRowsResult->fetch_assoc()['total'];

// Calculate the total number of pages
$totalProductTypePages = ceil($totalProductTypeRows / $rowsPerPage);

// Fetch total number of rows for pagination calculation
$totalProductRowsQuery = "SELECT COUNT(*) as total FROM producttb";
if ($filterProductID !== 'random' && !empty($searchProductQuery)) {
    $totalProductRowsQuery = "SELECT COUNT(*) as total FROM producttb WHERE ProductTypeID = '$filterProductID' AND (Title LIKE '%$searchProductQuery%' OR Description LIKE '%$searchProductQuery%' OR Specification LIKE '%$searchProductQuery%' OR Information LIKE '%$searchProductQuery%' OR Brand LIKE '%$searchProductQuery%')";
} elseif ($filterProductID !== 'random') {
    $totalProductRowsQuery = "SELECT COUNT(*) as total FROM producttb WHERE ProductTypeID = '$filterProductID'";
} elseif (!empty($searchProductQuery)) {
    $totalProductRowsQuery = "SELECT COUNT(*) as total FROM producttb WHERE Title LIKE '%$searchProductQuery%' OR Description LIKE '%$searchProductQuery%' OR Specification LIKE '%$searchProductQuery%' OR Information LIKE '%$searchProductQuery%' OR Brand LIKE '%$searchProductQuery%'";
}
$totalProductRowsResult = $connect->query($totalProductRowsQuery);
$totalProductRows = $totalProductRowsResult->fetch_assoc()['total'];

// Calculate the total number of pages
$totalProductPages = ceil($totalProductRows / $rowsPerPage);

// Fetch total number of rows for pagination calculation
$totalProductSizeRowsQuery = "SELECT COUNT(*) as total FROM sizetb";
if ($filterSizes !== 'random') {
    $totalProductSizeRowsQuery = "SELECT COUNT(*) as total FROM sizetb WHERE ProductID = '$filterSizes'";
} elseif (!empty($searchSizeQuery)) {
    $totalProductSizeRowsQuery = "SELECT COUNT(*) as total FROM sizetb WHERE Size LIKE '%$searchSizeQuery%'";
}
$totalProductSizeRowsResult = $connect->query($totalProductSizeRowsQuery);
$totalProductSizeRows = $totalProductSizeRowsResult->fetch_assoc()['total'];

// Calculate the total number of pages
$totalProductSizePages = ceil($totalProductSizeRows / $rowsPerPage);



// Fetch total number of rows for pagination calculation
$totalProductImageRowsQuery = "SELECT COUNT(*) as total FROM productimagetb";
if ($filterImages !== 'random') {
    $totalProductImageRowsQuery = "SELECT COUNT(*) as total FROM productimagetb WHERE ProductID LIKE '$filterImages'";
}
$totalProductImageRowsResult = $connect->query($totalProductImageRowsQuery);
$totalProductImageRows = $totalProductImageRowsResult->fetch_assoc()['total'];

// Calculate the total number of pages
$totalProductImagePages = ceil($totalProductImageRows / $rowsPerPage);



// Fetch total number of rows for pagination calculation
$totalContactRowsQuery = "SELECT COUNT(*) as total FROM contacttb";
if ($filterStatus !== 'random' && !empty($searchContactQuery)) {
    $totalContactRowsQuery = "SELECT COUNT(*) as total FROM contacttb WHERE Status = '$filterStatus' AND (FullName LIKE '%$searchContactQuery%' OR UserEmail LIKE '%$searchContactQuery%' OR Country LIKE '%$searchContactQuery%') $dateCondition";
} elseif ($filterStatus !== 'random') {
    $totalContactRowsQuery = "SELECT COUNT(*) as total FROM contacttb WHERE Status = '$filterStatus' LIMIT $rowsPerPage OFFSET $contactOffset $dateCondition";
} elseif (!empty($searchContactQuery)) {
    $totalContactRowsQuery = "SELECT COUNT(*) as total FROM contacttb WHERE FullName LIKE '%$searchContactQuery%' OR UserEmail LIKE '%$searchContactQuery%' OR Country LIKE '%$searchContactQuery%' LIMIT $rowsPerPage OFFSET $contactOffset $dateCondition";
}
$totalContactRowsResult = $connect->query($totalContactRowsQuery);
$totalContactRows = $totalContactRowsResult->fetch_assoc()['total'];

// Calculate the total number of pages
$totalContactPages = ceil($totalContactRows / $rowsPerPage);

// Fetch total number of rows for pagination calculation
$totalSupplierRowsQuery = "SELECT COUNT(*) as total FROM suppliertb";
if ($filterRoleID !== 'random' && !empty($searchAdminQuery)) {
    $totalSupplierRowsQuery = "SELECT COUNT(*) as total FROM suppliertb WHERE ProductTypeID = '$filterSupplierID' AND (SupplierName LIKE '%$searchSupplierQuery%' OR SupplierEmail LIKE '%$searchSupplierQuery%' OR SupplierContact LIKE '%$searchSupplierQuery%' OR SupplierCompany LIKE '%$searchSupplierQuery%' OR Country LIKE '%$searchSupplierQuery%')";
} elseif ($filterRoleID !== 'random') {
    $totalSupplierRowsQuery = "SELECT COUNT(*) as total FROM suppliertb WHERE ProductTypeID = '$filterSupplierID'";
} elseif (!empty($searchAdminQuery)) {
    $totalSupplierRowsQuery = "SELECT COUNT(*) as total FROM suppliertb WHERE SupplierName LIKE '%$searchSupplierQuery%' OR SupplierEmail LIKE '%$searchSupplierQuery%' OR SupplierContact LIKE '%$searchSupplierQuery%' OR SupplierCompany LIKE '%$searchSupplierQuery%' OR Country LIKE '%$searchSupplierQuery%'";
}
$totalSupplierRowsResult = $connect->query($totalSupplierRowsQuery);
$totalSupplierRows = $totalSupplierRowsResult->fetch_assoc()['total'];

// Calculate the total number of pages
$totalSupplierPages = ceil($totalSupplierRows / $rowsPerPage);

// Fetch total number of rows for pagination calculation
$totalRoomTypeRowsQuery = "SELECT COUNT(*) as total FROM roomtypetb";
if (!empty($roomTypeSelectQuery)) {
    $totalRoomTypeRowsQuery = "SELECT COUNT(*) as total FROM roomtypetb WHERE RoomType LIKE '%$searchRoomTypeQuery%' OR RoomDescription LIKE '%$searchRoomTypeQuery%'";
}
$totalRoomTypeRowsResult = $connect->query($totalRoomTypeRowsQuery);
$totalRoomTypeRows = $totalRoomTypeRowsResult->fetch_assoc()['total'];

// Calculate the total number of pages
$totalRoomTypePages = ceil($totalRoomTypeRows / $rowsPerPage);

// Fetch total number of rows for pagination calculation
$totalFacilityTypeRowsQuery = "SELECT COUNT(*) as total FROM facilitytypetb";
if (!empty($searchFacilityTypeQuery)) {
    $totalFacilityTypeRowsQuery = "SELECT COUNT(*) as total FROM facilitytypetb WHERE FacilityType LIKE '%$searchFacilityTypeQuery%'";
}
$totalFacilityTypeRowsResult = $connect->query($totalFacilityTypeRowsQuery);
$totalFacilityTypeRows = $totalFacilityTypeRowsResult->fetch_assoc()['total'];

// Calculate the total number of pages
$totalFacilityTypePages = ceil($totalFacilityTypeRows / $rowsPerPage);

// Fetch total number of rows for pagination calculation
$totalFacilityRowsQuery = "SELECT COUNT(*) as total FROM facilitytb";
if (!empty($searchFacilityQuery)) {
    $totalFacilityRowsQuery = "SELECT COUNT(*) as total FROM facilitytb WHERE Facility LIKE '%$searchFacilityQuery%'";
}
$totalFacilityRowsResult = $connect->query($totalFacilityRowsQuery);
$totalFacilityRows = $totalFacilityRowsResult->fetch_assoc()['total'];

// Calculate the total number of pages
$totalFacilityPages = ceil($totalFacilityRows / $rowsPerPage);

// Fetch total number of rows for pagination calculation
$totalRuleRowsQuery = "SELECT COUNT(*) as total FROM ruletb";
if (!empty($searchRuleQuery)) {
    $totalRuleRowsQuery = "SELECT COUNT(*) as total FROM ruletb WHERE RuleTitle LIKE '%$searchRuleQuery%' OR Rule LIKE '%$searchRuleQuery%'";
}
$totalRuleRowsResult = $connect->query($totalRuleRowsQuery);
$totalRuleRows = $totalRuleRowsResult->fetch_assoc()['total'];

// Calculate the total number of pages
$totalRulePages = ceil($totalRuleRows / $rowsPerPage);

// Fetch total number of rows for pagination calculation
$totalUserRowsQuery = "SELECT COUNT(*) as total FROM usertb";
if (!empty($searchRuleQuery)) {
    $totalUserRowsQuery = "SELECT COUNT(*) as total FROM usertb WHERE UserName LIKE '%$searchUserQuery%' OR UserEmail LIKE '%$searchUserQuery%'";
}
$totalUserRowsResult = $connect->query($totalUserRowsQuery);
$totalUserRows = $totalUserRowsResult->fetch_assoc()['total'];

// Calculate the total number of pages
$totalUserPages = ceil($totalUserRows / $rowsPerPage);
