<?php
// Set the number of rows per page
$rowsPerPage = 1;

// Get the current page number from the URL or default to 1
$currentPage = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$contactCurrentPage = isset($_GET['contactpage']) && is_numeric($_GET['contactpage']) ? (int)$_GET['contactpage'] : 1;
$productTypeCurrentPage = isset($_GET['producttypepage']) && is_numeric($_GET['producttypepage']) ? (int)$_GET['producttypepage'] : 1;
$supplierCurrentPage = isset($_GET['supplierpage']) && is_numeric($_GET['supplierpage']) ? (int)$_GET['supplierpage'] : 1;
$productCurrentPage = isset($_GET['productpage']) && is_numeric($_GET['productpage']) ? (int)$_GET['productpage'] : 1;
$productSizeCurrentPage = isset($_GET['productsizepage']) && is_numeric($_GET['productsizepage']) ? (int)$_GET['productsizepage'] : 1;
$productImageCurrentPage = isset($_GET['productimagepage']) && is_numeric($_GET['productimagepage']) ? (int)$_GET['productimagepage'] : 1;
$roomCurrentPage = isset($_GET['roompage']) && is_numeric($_GET['roompage']) ? (int)$_GET['roompage'] : 1;
$roomTypeCurrentPage = isset($_GET['roomtypepage']) && is_numeric($_GET['roomtypepage']) ? (int)$_GET['roomtypepage'] : 1;
$facilityTypeCurrentPage = isset($_GET['facilitytypepage']) && is_numeric($_GET['facilitytypepage']) ? (int)$_GET['facilitytypepage'] : 1;
// $facilityCurrentPage = isset($_GET['facilitypage']) && is_numeric($_GET['facilitypage']) ? (int)$_GET['facilitypage'] : 1;
$ruleCurrentPage = isset($_GET['rulepage']) && is_numeric($_GET['rulepage']) ? (int)$_GET['rulepage'] : 1;
$menuCurrentPage = isset($_GET['menupage']) && is_numeric($_GET['menupage']) ? (int)$_GET['menupage'] : 1;
$userCurrentPage = isset($_GET['userpage']) && is_numeric($_GET['userpage']) ? (int)$_GET['userpage'] : 1;
$reservationCurrentPage  = isset($_GET['bookingpage']) && is_numeric($_GET['bookingpage']) ? (int)$_GET['bookingpage'] : 1;
$purchaseCurrentPage = isset($_GET['purchasepage']) && is_numeric($_GET['purchasepage']) ? (int)$_GET['purchasepage'] : 1;

// Calculate the offset for the query
$offset = ($currentPage - 1) * $rowsPerPage;
$contactOffset = ($contactCurrentPage - 1) * $rowsPerPage;
$productTypeOffset = ($productTypeCurrentPage - 1) * $rowsPerPage;
$productOffset = ($productCurrentPage - 1) * $rowsPerPage;
$productSizeOffset = ($productSizeCurrentPage - 1) * $rowsPerPage;
$productImageOffset = ($productImageCurrentPage - 1) * $rowsPerPage;
$supplierOffset = ($supplierCurrentPage - 1) * $rowsPerPage;
$roomOffset = ($roomCurrentPage - 1) * $rowsPerPage;
$roomTypeOffset = ($roomTypeCurrentPage - 1) * $rowsPerPage;
$facilityTypeOffset = ($facilityTypeCurrentPage - 1) * $rowsPerPage;
$facilityOffset = ($currentPage - 1) * $rowsPerPage;
$ruleOffset = ($ruleCurrentPage - 1) * $rowsPerPage;
$menuOffset = ($menuCurrentPage - 1) * $rowsPerPage;
$userOffset = ($userCurrentPage - 1) * $rowsPerPage;
$reservationOffset = ($reservationCurrentPage  - 1) * $rowsPerPage;
$purchaseOffset = ($purchaseCurrentPage - 1) * $rowsPerPage;

// Initialize search and filter variables
$searchSupplierQuery = isset($_GET['supplier_search']) ? mysqli_real_escape_string($connect, $_GET['supplier_search']) : '';
$searchProductTypeQuery = isset($_GET['producttype_search']) ? mysqli_real_escape_string($connect, $_GET['producttype_search']) : '';
$searchProductQuery = isset($_GET['product_search']) ? mysqli_real_escape_string($connect, $_GET['product_search']) : '';
$searchSizeQuery = isset($_GET['size_search']) ? mysqli_real_escape_string($connect, $_GET['size_search']) : '';
$searchRoomTypeQuery = isset($_GET['roomtype_search']) ? mysqli_real_escape_string($connect, $_GET['roomtype_search']) : '';
$searchRoomQuery = isset($_GET['room_search']) ? mysqli_real_escape_string($connect, $_GET['room_search']) : '';
$searchRuleQuery = isset($_GET['rule_search']) ? mysqli_real_escape_string($connect, $_GET['rule_search']) : '';
$searchFacilityTypeQuery = isset($_GET['facilitytype_search']) ? mysqli_real_escape_string($connect, $_GET['facilitytype_search']) : '';
$searchFacilityQuery = isset($_GET['facility_search']) ? mysqli_real_escape_string($connect, $_GET['facility_search']) : '';
$searchMenuQuery = isset($_GET['menu_search']) ? mysqli_real_escape_string($connect, $_GET['menu_search']) : '';
$searchBookingQuery = isset($_GET['booking_search']) ? mysqli_real_escape_string($connect, $_GET['booking_search']) : '';
$searchUserQuery = isset($_GET['user_search']) ? mysqli_real_escape_string($connect, $_GET['user_search']) : '';
$searchPurchaseQuery = isset($_GET['purchase_search']) ? mysqli_real_escape_string($connect, $_GET['purchase_search']) : '';

$filterRoleID = isset($_GET['sort']) ? $_GET['sort'] : 'random';
$filterStatus = isset($_GET['sort']) ? $_GET['sort'] : 'random';
$filterSupplierID = isset($_GET['sort']) ? $_GET['sort'] : 'random';
$filterProductTypeID = isset($_GET['sort']) ? $_GET['sort'] : 'random';
$filterProductID = isset($_GET['sort']) ? $_GET['sort'] : 'random';
$filterSizes = isset($_GET['sort']) ? $_GET['sort'] : 'random';
$filterImages = isset($_GET['sort']) ? $_GET['sort'] : 'random';
$filterFacilityTypeID = isset($_GET['sort']) ? $_GET['sort'] : 'random';
$filterMembershipID = isset($_GET['sort']) ? $_GET['sort'] : 'random';
$filterRoomType = isset($_GET['sort']) ? $_GET['sort'] : 'random';

$dateCondition = '';
if (!empty($searchFromDate) && !empty($searchToDate)) {
    $dateCondition = " AND ContactDate BETWEEN '$searchFromDate 00:00:00' AND '$searchToDate 23:59:59'";
} elseif (!empty($searchFromDate)) {
    $dateCondition = " AND ContactDate >= '$searchFromDate 00:00:00'";
} elseif (!empty($searchToDate)) {
    $dateCondition = " AND ContactDate <= '$searchToDate 23:59:59'";
}

// Construct the admin count query based on search and role filter
if ($filterRoleID !== 'random' && !empty($searchAdminQuery)) {
    $adminCountQuery = "SELECT COUNT(*) as count FROM admintb WHERE RoleID = '$filterRoleID' AND (FirstName LIKE '%$searchAdminQuery%' OR LastName LIKE '%$searchAdminQuery%' OR UserName LIKE '%$searchAdminQuery%' OR AdminEmail LIKE '%$searchAdminQuery%')";
} elseif ($filterRoleID !== 'random') {
    $adminCountQuery = "SELECT COUNT(*) as count FROM admintb WHERE RoleID = '$filterRoleID'";
} elseif (!empty($searchAdminQuery)) {
    $adminCountQuery = "SELECT COUNT(*) as count FROM admintb WHERE FirstName LIKE '%$searchAdminQuery%' OR LastName LIKE '%$searchAdminQuery%' OR UserName LIKE '%$searchAdminQuery%' OR AdminEmail LIKE '%$searchAdminQuery%'";
} else {
    $adminCountQuery = "SELECT COUNT(*) as count FROM admintb";
}

// Execute the count query
$adminCountResult = $connect->query($adminCountQuery);
$adminCount = $adminCountResult->fetch_assoc()['count'];

// Calculate the total number of pages
$totalAdminPages = ceil($adminCount / $rowsPerPage);

// Construct the prooducttype count query based on search
if (!empty($searchProductTypeQuery)) {
    $productTypeQuery = "SELECT COUNT(*) as count FROM producttypetb WHERE ProductType LIKE '%$searchProductTypeQuery%' OR Description LIKE '%$searchProductTypeQuery%'";
} else {
    $productTypeQuery = "SELECT COUNT(*) as count FROM producttypetb";
}

// Execute the count query
$productTypeResult = $connect->query($productTypeQuery);
$productTypeCount = $productTypeResult->fetch_assoc()['count'];

// Calculate the total number of pages
$totalProductTypePages = ceil($productTypeCount / $rowsPerPage);

// Construct the product count query based on search and product type filter
if ($filterProductID !== 'random' && !empty($searchProductQuery)) {
    $productQuery = "SELECT COUNT(*) as count FROM producttb WHERE ProductTypeID = '$filterProductID' AND (Title LIKE '%$searchProductQuery%' OR Description LIKE '%$searchProductQuery%' OR Specification LIKE '%$searchProductQuery%' OR Information LIKE '%$searchProductQuery%' OR Brand LIKE '%$searchProductQuery%')";
} elseif ($filterProductID !== 'random') {
    $productQuery = "SELECT COUNT(*) as count FROM producttb WHERE ProductTypeID = '$filterProductID'";
} elseif (!empty($searchProductQuery)) {
    $productQuery = "SELECT COUNT(*) as count FROM producttb WHERE Title LIKE '%$searchProductQuery%' OR Description LIKE '%$searchProductQuery%' OR Specification LIKE '%$searchProductQuery%' OR Information LIKE '%$searchProductQuery%' OR Brand LIKE '%$searchProductQuery%'";
} else {
    $productQuery = "SELECT COUNT(*) as count FROM producttb";
}

// Execute the count query
$productResult = $connect->query($productQuery);
$productCount = $productResult->fetch_assoc()['count'];

// Calculate the total number of pages
$totalProductPages = ceil($productCount / $rowsPerPage);

// Construct the facilitytype count query based on search
if ($filterSizes !== 'random' && !empty($searchSizeQuery)) {
    $productSizeQuery = "SELECT COUNT(*) as count FROM sizetb WHERE ProductID = '$filterSizes' AND Size LIKE '%$searchSizeQuery%'";
} elseif ($filterSizes !== 'random') {
    $productSizeQuery = "SELECT COUNT(*) as count FROM sizetb WHERE ProductID = '$filterSizes'";
} elseif (!empty($searchSizeQuery)) {
    $productSizeQuery = "SELECT COUNT(*) as count FROM sizetb WHERE Size LIKE '%$searchSizeQuery%'";
} else {
    $productSizeQuery = "SELECT COUNT(*) as count FROM sizetb";
}

// Execute the count query
$productSizeResult = $connect->query($productSizeQuery);
$productSizeCount = $productSizeResult->fetch_assoc()['count'];

// Calculate the total number of pages
$totalProductSizePages = ceil($productSizeCount / $rowsPerPage);


// Fetch total number of rows for pagination calculation
$totalProductImageRowsQuery = "SELECT COUNT(*) as total FROM productimagetb";
if ($filterImages !== 'random') {
    $totalProductImageRowsQuery = "SELECT COUNT(*) as total FROM productimagetb WHERE ProductID LIKE '$filterImages'";
}
$totalProductImageRowsResult = $connect->query($totalProductImageRowsQuery);
$totalProductImageRows = $totalProductImageRowsResult->fetch_assoc()['total'];

// Calculate the total number of pages
$totalProductImagePages = ceil($totalProductImageRows / $rowsPerPage);

// Construct the contact query based on search and status filter
if ($filterStatus !== 'random' && !empty($searchContactQuery)) {
    $contactQuery = "SELECT COUNT(*) as count FROM contacttb WHERE Status = '$filterStatus' AND (FullName LIKE '%$searchContactQuery%' OR UserEmail LIKE '%$searchContactQuery%' OR Country LIKE '%$searchContactQuery%') $dateCondition";
} elseif ($filterStatus !== 'random') {
    $contactQuery = "SELECT COUNT(*) as count FROM contacttb WHERE Status = '$filterStatus' $dateCondition";
} elseif (!empty($searchContactQuery)) {
    $contactQuery = "SELECT COUNT(*) as count FROM contacttb WHERE FullName LIKE '%$searchContactQuery%' OR UserEmail LIKE '%$searchContactQuery%' OR Country LIKE '%$searchContactQuery%' $dateCondition";
} else {
    $contactQuery = "SELECT COUNT(*) as count FROM contacttb WHERE 1 $dateCondition";
}

// Execute the count query
$contactResult = $connect->query($contactQuery);
$contactCount = $contactResult->fetch_assoc()['count'];

// Calculate the total number of pages
$totalContactPages = ceil($contactCount / $rowsPerPage);

// Construct the supplier count query based on search
if ($filterSupplierID !== 'random' && !empty($searchSupplierQuery)) {
    $supplierQuery = "SELECT COUNT(*) as count FROM suppliertb 
                     WHERE ProductTypeID = '$filterSupplierID' 
                     AND (SupplierName LIKE '%$searchSupplierQuery%' 
                          OR SupplierEmail LIKE '%$searchSupplierQuery%' 
                          OR SupplierContact LIKE '%$searchSupplierQuery%' 
                          OR SupplierCompany LIKE '%$searchSupplierQuery%' 
                          OR Country LIKE '%$searchSupplierQuery%')";
} elseif ($filterSupplierID !== 'random') {
    $supplierQuery = "SELECT COUNT(*) as count FROM suppliertb 
                     WHERE ProductTypeID = '$filterSupplierID'";
} elseif (!empty($searchSupplierQuery)) {
    $supplierQuery = "SELECT COUNT(*) as count FROM suppliertb 
                     WHERE SupplierName LIKE '%$searchSupplierQuery%' 
                     OR SupplierEmail LIKE '%$searchSupplierQuery%' 
                     OR SupplierContact LIKE '%$searchSupplierQuery%' 
                     OR SupplierCompany LIKE '%$searchSupplierQuery%' 
                     OR Country LIKE '%$searchSupplierQuery%'";
} else {
    $supplierQuery = "SELECT COUNT(*) as count FROM suppliertb";
}

$supplierResult = $connect->query($supplierQuery);
$supplierCount = $supplierResult->fetch_assoc()['count'];

// Calculate the total number of pages
$totalSupplierPages = ceil($supplierCount / $rowsPerPage);

// Fetch total number of rows for pagination calculation
$totalRoomTypeRowsQuery = "SELECT COUNT(*) as total FROM roomtypetb";
if (!empty($roomTypeSelectQuery)) {
    $totalRoomTypeRowsQuery = "SELECT COUNT(*) as total FROM roomtypetb WHERE RoomType LIKE '%$searchRoomTypeQuery%' OR RoomDescription LIKE '%$searchRoomTypeQuery%'";
}
$totalRoomTypeRowsResult = $connect->query($totalRoomTypeRowsQuery);
$totalRoomTypeRows = $totalRoomTypeRowsResult->fetch_assoc()['total'];

// Calculate the total number of pages
$totalRoomTypePages = ceil($totalRoomTypeRows / $rowsPerPage);

// Construct the roomtype count query based on search
if ($filterRoomType !== 'random' && !empty($searchRoomQuery)) {
    $roomQuery = "SELECT COUNT(*) as count FROM roomtb WHERE RoomTypeID = '$filterRoomType' AND (RoomName LIKE '%$searchRoomQuery%')";
} elseif ($filterRoomType !== 'random') {
    $roomQuery = "SELECT COUNT(*) as count FROM roomtb WHERE RoomTypeID = '$filterRoomType'";
} elseif (!empty($searchRoomQuery)) {
    $roomQuery = "SELECT COUNT(*) as count FROM roomtb WHERE RoomName LIKE '%$searchRoomQuery%'";
} else {
    $roomQuery = "SELECT COUNT(*) as count FROM roomtb";
}

// Execute the count query
$roomResult = $connect->query($roomQuery);
$roomCount = $roomResult->fetch_assoc()['count'];

// Calculate the total number of pages
$totalRoomPages = ceil($roomCount / $rowsPerPage);

// Fetch total number of rows for pagination calculation
$totalFacilityTypeRowsQuery = "SELECT COUNT(*) as total FROM facilitytypetb";
if (!empty($searchFacilityTypeQuery)) {
    $totalFacilityTypeRowsQuery = "SELECT COUNT(*) as total FROM facilitytypetb WHERE FacilityType LIKE '%$searchFacilityTypeQuery%'";
}
$totalFacilityTypeRowsResult = $connect->query($totalFacilityTypeRowsQuery);
$totalFacilityTypeRows = $totalFacilityTypeRowsResult->fetch_assoc()['total'];

// Calculate the total number of pages
$totalFacilityTypePages = ceil($totalFacilityTypeRows / $rowsPerPage);

// Construct the facility count query based on search
if ($filterFacilityTypeID !== 'random' && !empty($searchFacilityQuery)) {
    $facilityQuery = "SELECT COUNT(*) as count FROM facilitytb WHERE FacilityTypeID = '$filterFacilityTypeID' AND (Facility LIKE '%$searchFacilityQuery%')";
} elseif ($filterFacilityTypeID !== 'random') {
    $facilityQuery = "SELECT COUNT(*) as count FROM facilitytb WHERE FacilityTypeID = '$filterFacilityTypeID'";
} elseif (!empty($searchFacilityQuery)) {
    $facilityQuery = "SELECT COUNT(*) as count FROM facilitytb WHERE Facility LIKE '%$searchFacilityQuery%'";
} else {
    $facilityQuery = "SELECT COUNT(*) as count FROM facilitytb";
}

// Execute the count query
$facilityResult = $connect->query($facilityQuery);
$facilityCount = $facilityResult->fetch_assoc()['count'];

// Calculate total pages
$totalPages = ceil($facilityCount / $rowsPerPage);

// Construct the rule count query based on search
if (!empty($searchRuleQuery)) {
    $ruleQuery = "SELECT COUNT(*) as count FROM ruletb WHERE RuleTitle LIKE '%$searchRuleQuery%' OR Rule LIKE '%$searchRuleQuery%'";
} else {
    $ruleQuery = "SELECT COUNT(*) as count FROM ruletb";
}

// Execute the count query
$ruleResult = $connect->query($ruleQuery);
$ruleCount = $ruleResult->fetch_assoc()['count'];

// Calculate the total number of pages
$totalRulePages = ceil($ruleCount / $rowsPerPage);

// Construct the user count query based on search
if ($filterMembershipID !== 'random' && !empty($searchUserQuery)) {
    $userQuery = "SELECT COUNT(*) as count FROM usertb WHERE Membership = '$filterMembershipID' AND (UserName LIKE '%$searchUserQuery%' OR UserEmail LIKE '%$searchUserQuery%')";
} elseif ($filterMembershipID !== 'random') {
    $userQuery = "SELECT COUNT(*) as count FROM usertb WHERE Membership = '$filterMembershipID'";
} elseif (!empty($searchUserQuery)) {
    $userQuery = "SELECT COUNT(*) as count FROM usertb WHERE UserName LIKE '%$searchUserQuery%' OR UserEmail LIKE '%$searchUserQuery%'";
} else {
    $userQuery = "SELECT COUNT(*) as count FROM usertb";
}

// Execute the count query
$userResult = $connect->query($userQuery);
$userCount = $userResult->fetch_assoc()['count'];

// Calculate the total number of pages
$totalUserPages = ceil($userCount / $rowsPerPage);

// Count query - simplified using the same conditions
if ($filterStatus !== 'random' && !empty($searchBookingQuery)) {
    $BookingQuery = "SELECT COUNT(*) as count 
                    FROM reservationtb r
                    JOIN usertb u ON r.UserID = u.UserID  
                    WHERE r.Status = '$filterStatus' 
                    AND (r.FirstName LIKE '%$searchBookingQuery%' 
                         OR r.LastName LIKE '%$searchBookingQuery%'
                         OR r.UserPhone LIKE '%$searchBookingQuery%'
                         OR r.ReservationID LIKE '%$searchBookingQuery%'
                         OR u.UserName LIKE '%$searchBookingQuery%')";
} elseif ($filterStatus !== 'random') {
    $BookingQuery = "SELECT COUNT(*) as count 
                    FROM reservationtb r
                    JOIN usertb u ON r.UserID = u.UserID 
                    WHERE r.Status = '$filterStatus'";
} elseif (!empty($searchBookingQuery)) {
    $BookingQuery = "SELECT COUNT(*) as count 
                    FROM reservationtb r
                    JOIN usertb u ON r.UserID = u.UserID 
                    WHERE (r.FirstName LIKE '%$searchBookingQuery%'
                          OR r.LastName LIKE '%$searchBookingQuery%'
                          OR r.UserPhone LIKE '%$searchBookingQuery%'
                          OR r.ReservationID LIKE '%$searchBookingQuery%'
                          OR u.UserName LIKE '%$searchBookingQuery%')";
} else {
    $BookingQuery = "SELECT COUNT(*) as count 
                    FROM reservationtb r
                    JOIN usertb u ON r.UserID = u.UserID";
}

// Execute the count query
$bookingResult = $connect->query($BookingQuery);
$bookingCount = $bookingResult->fetch_assoc()['count'];

// Calculate the total number of pages
$totalReservationPages = ceil($bookingCount / $rowsPerPage);

// Construct the menu count query based on search
if ($filterStatus !== 'random' && !empty($searchMenuQuery)) {
    $menuQuery = "SELECT COUNT(*) as count FROM menutb WHERE Status = '$filterStatus' AND (MenuName LIKE '%$searchMenuQuery%')";
} elseif ($filterStatus !== 'random') {
    $menuQuery = "SELECT COUNT(*) as count FROM menutb WHERE Status = '$filterStatus'";
} elseif (!empty($searchMenuQuery)) {
    $menuQuery = "SELECT COUNT(*) as count FROM menutb WHERE MenuName LIKE '%$searchMenuQuery%'";
} else {
    $menuQuery = "SELECT COUNT(*) as count FROM menutb";
}

// Execute the count query
$menuResult = $connect->query($menuQuery);
$menuCount = $menuResult->fetch_assoc()['count'];

// Calculate the total number of pages
$totalMenuPages = ceil($menuCount / $rowsPerPage);

// Construct the purchase count query based on search
if (!empty($searchPurchaseQuery)) {
    $purchaseQuery = "SELECT COUNT(*) as count FROM purchasetb WHERE ProductType LIKE '%$searchPurchaseQuery%' OR Description LIKE '%$searchProductTypeQuery%'";
} else {
    $purchaseQuery = "SELECT COUNT(*) as count FROM purchasetb";
}

// Execute the count query
$purchaseResult = $connect->query($purchaseQuery);
$purchaseCount = $purchaseResult->fetch_assoc()['count'];

// Calculate the total number of pages
$totalPurchasePages = ceil($purchaseCount / $rowsPerPage);
