<?php
// Security headers
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');

// Check if user is logged in
if (!isset($_SESSION['AdminID']) || !isset($_SESSION['RoleID'])) {
    header("Location: ../Admin/admin_signin.php");
    exit();
}

// Define role permissions
$rolePermissions = [
    1 => [
        'admin_dashboard.php',
        'admin_profile_edit.php',
        'add_facilitytype.php',
        'add_facility.php',
        'add_menu.php',
        'add_producttype.php',
        'add_product.php',
        'add_roomtype.php',
        'add_room.php',
        'add_rule.php',
        'add_supplier.php',
        'add_size.php',
        'product_image.php',
        'product_purchase.php',
        'purchase_history.php',
        'reservation.php',
        'role_management.php',
        'user_contact.php',
        'user_details.php'
    ], // Super Admin
    2 => [
        'admin_dashboard.php',
        'admin_profile_edit.php',
        'reservation.php',
        'user_contact.php',
        'user_details.php',
        'add_room.php'
    ], // Reservation Manager
    3 => [
        'admin_dashboard.php',
        'admin_profile_edit.php',
        'add_producttype.php',
        'add_product.php',
        'add_size.php',
        'add_supplier.php',
        'product_purchase.php',
        'purchase_history.php',
        'user_contact.php',
        'user_details.php'
    ],  // Purchase Manager
    4 => [
        'admin_dashboard.php',
        'admin_profile_edit.php',
        'add_roomtype.php',
        'add_room.php',
        'add_rule.php',
        'add_facility.php',
        'add_facilitytype.php',
        'user_contact.php',
        'user_details.php',
        'reservation.php'
    ], // Room Manager
    5 => [
        'admin_dashboard.php',
        'admin_profile_edit.php',
        'add_producttype.php',
        'add_product.php',
        'add_size.php',
        'product_image.php',
        'user_contact.php',
        'user_details.php',
        'product_purchase.php',
        'purchase_history.php'
    ], // Product Manager
];

// Get current page
$current_page = basename($_SERVER['PHP_SELF']);

// Check if user's role has permission to access this page
if (
    !isset($rolePermissions[$_SESSION['RoleID']]) ||
    !in_array($current_page, $rolePermissions[$_SESSION['RoleID']])
) {
    header("Location: ../Admin/unauthorized.php");
    exit();
}
