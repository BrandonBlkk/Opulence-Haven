<?php
include('../includes/admin_pagination.php');

// Get the current file name
$current_page = basename($_SERVER['PHP_SELF']);
$adminID = $_SESSION['AdminID'];

// Fetch admin profile
$adminProfileQuery = "SELECT AdminProfile, RoleID FROM admintb WHERE AdminID = '$adminID'";
$adminProfileResult = $connect->query($adminProfileQuery);
$adminProfileRow = $adminProfileResult->fetch_assoc();
$adminprofile = $adminProfileRow['AdminProfile'];
$role = $adminProfileRow['RoleID'];

// Initialize search and filter variables for admin
$searchAdminQuery = isset($_GET['acc_search']) ? mysqli_real_escape_string($connect, $_GET['acc_search']) : '';
$filterRoleID = isset($_GET['sort']) ? $_GET['sort'] : 'random';

// Fetch all supplier count
$supplierCountQuery = "SELECT COUNT(*) as count FROM suppliertb";
$supplierCountResult = $connect->query($supplierCountQuery);
$allSupplierCount = $supplierCountResult->fetch_assoc()['count'];

// Fetch product type count
$productTypeCountQuery = "SELECT COUNT(*) as count FROM producttypetb";
$productTypeCountResult = $connect->query($productTypeCountQuery);
$allProductTypeCount = $productTypeCountResult->fetch_assoc()['count'];

// Fetch product count
$productCountQuery = "SELECT COUNT(*) as count FROM producttb";
$productCountResult = $connect->query($productCountQuery);
$allProductCount = $productCountResult->fetch_assoc()['count'];

// Fetch all products
$productCountQuery = "SELECT COUNT(*) as count FROM producttb WHERE isActive = 1";
$productCountResult = $connect->query($productCountQuery);
$markupProductCount = $productCountResult->fetch_assoc()['count'];

// Construct the roomtype count query based on search
if (!empty($searchRoomTypeQuery)) {
    $roomTypeQuery = "SELECT COUNT(*) as count FROM roomtypetb WHERE RoomType LIKE '%$searchRoomTypeQuery%' OR RoomDescription LIKE '%$searchRoomTypeQuery%'";
} else {
    $roomTypeQuery = "SELECT COUNT(*) as count FROM roomtypetb";
}

// Execute the count query
$roomTypeResult = $connect->query($roomTypeQuery);
$roomTypeCount = $roomTypeResult->fetch_assoc()['count'];

// Fetch room type count
$roomTypeCountQuery = "SELECT COUNT(*) as count FROM roomtypetb";
$roomTypeCountResult = $connect->query($roomTypeCountQuery);
$allRoomTypeCount = $roomTypeCountResult->fetch_assoc()['count'];

// Fetch room type count
$roomCountQuery = "SELECT COUNT(*) as count FROM roomtb";
$roomCountResult = $connect->query($roomCountQuery);
$allRoomCount = $roomCountResult->fetch_assoc()['count'];

// Fetch rule count
$ruleCountQuery = "SELECT COUNT(*) as count FROM ruletb";
$facilityTypeCountResult = $connect->query($ruleCountQuery);
$allRuleCount = $facilityTypeCountResult->fetch_assoc()['count'];

// Fetch facility type count
$facilityTypeCountQuery = "SELECT COUNT(*) as count FROM facilitytypetb";
$facilityTypeCountResult = $connect->query($facilityTypeCountQuery);
$allFacilityTypeCount = $facilityTypeCountResult->fetch_assoc()['count'];

// Fetch facility count
$facilityCountQuery = "SELECT COUNT(*) as count FROM facilitytb";
$facilityCountResult = $connect->query($facilityCountQuery);
$allFacilityCount = $facilityCountResult->fetch_assoc()['count'];

// Fetch facility count
$menuCountQuery = "SELECT COUNT(*) as count FROM menutb";
$menuCountResult = $connect->query($menuCountQuery);
$allMenuCount = $menuCountResult->fetch_assoc()['count'];

// Fetch reservation count
$reservationCountQuery = "SELECT COUNT(*) as count FROM reservationtb WHERE Status = 'Pending'";
$reservationCountResult = $connect->query($reservationCountQuery);
$allReservationCount = $reservationCountResult->fetch_assoc()['count'];

// Initialize search and filter variables for contact
$searchContactQuery = isset($_GET['contact_search']) ? mysqli_real_escape_string($connect, $_GET['contact_search']) : '';
$searchFromDate = isset($_GET['from_date']) ? $_GET['from_date'] : '';
$searchToDate = isset($_GET['to_date']) ? $_GET['to_date'] : '';
$filterStatus = isset($_GET['sort']) ? $_GET['sort'] : 'random';

$dateCondition = '';
if (!empty($searchFromDate) && !empty($searchToDate)) {
    $dateCondition = " AND ContactDate BETWEEN '$searchFromDate 00:00:00' AND '$searchToDate 23:59:59'";
} elseif (!empty($searchFromDate)) {
    $dateCondition = " AND ContactDate >= '$searchFromDate 00:00:00'";
} elseif (!empty($searchToDate)) {
    $dateCondition = " AND ContactDate <= '$searchToDate 23:59:59'";
}

// Fetch contact count
$contactCountQuery = "SELECT COUNT(*) as count FROM contacttb WHERE Status = 'pending'";
$contactCountResult = $connect->query($contactCountQuery);
$allContactCount = $contactCountResult->fetch_assoc()['count'];

$select = "SELECT admintb.*, roletb.Role 
    FROM admintb 
    INNER JOIN roletb ON admintb.RoleID = roletb.RoleID 
    WHERE admintb.RoleID = '$role' AND admintb.AdminID = '$adminID'";
$query = $connect->query($select);

if (mysqli_num_rows($query) > 0) {
    while ($row = $query->fetch_assoc()) {
        $admin_id = $row['AdminID'];
        $admin_profile = $row['AdminProfile'];
        $profile_color = $row['ProfileBgColor'];
        $admin_username = $row['UserName'];
        $role_id = $row['RoleID'];
        $admin_role = $row['Role'];
    }
}

// Initialize search variables for product image
$filterImages = isset($_GET['sort']) ? $_GET['sort'] : 'random';

// Construct the facility type query based on search
if ($filterImages !== 'random') {
    $productImageSelect = "SELECT * FROM productimagetb WHERE ProductID LIKE '$filterImages' LIMIT $rowsPerPage OFFSET $productImageOffset";
} else {
    $productImageSelect = "SELECT * FROM productimagetb LIMIT $rowsPerPage OFFSET $productImageOffset";
}

$productImageSelectQuery = mysqli_query($connect, $productImageSelect);
$productImages = [];

if (mysqli_num_rows($productImageSelectQuery) > 0) {
    while ($row = $productImageSelectQuery->fetch_assoc()) {
        $productImages[] = $row;
    }
}

// Construct the facilitytype count query based on search
if ($filterImages !== 'random') {
    $productImageQuery = "SELECT COUNT(*) as count FROM productimagetb WHERE ProductID LIKE '$filterImages'";
} else {
    $productImageQuery = "SELECT COUNT(*) as count FROM productimagetb";
}

// Execute the count query
$productImageResult = $connect->query($productImageQuery);
$productImageCount = $productImageResult->fetch_assoc()['count'];

// Fetch all users
$select = "SELECT * FROM usertb ORDER BY SignupDate DESC LIMIT 5";
$query = $connect->query($select);
$allUsers = [];

if (mysqli_num_rows($query) > 0) {
    while ($row = $query->fetch_assoc()) {
        $allUsers[] = $row;
    }
}
?>

<!-- Hamburger Menu Button -->
<button id="menu-toggle" class="fixed top-4 right-4 z-50 md:hidden p-2 backdrop-blur-sm text-amber-500 rounded shadow">
    <i class="ri-menu-line text-2xl"></i>
</button>

<!-- Sidebar -->
<nav id="sidebar" class="adminNav overflow-y-auto fixed top-0 left-0 h-full w-full sm:w-64 md:w-[250px] p-4 flex flex-col justify-between bg-white shadow-md transform -translate-x-full md:translate-x-0 transition-all duration-300 z-40">
    <div>
        <div class="flex items-start justify-between">
            <!-- Logo -->
            <div class="flex items-end gap-1 select-none">
                <a href="../Admin/admin_dashboard.php">
                    <img src="../UserImages/Screenshot_2024-11-29_201534-removebg-preview.png" class="w-28 select-none" alt="Logo">
                </a>
                <p class="text-amber-500 text-sm font-semibold">ADMIN</p>
            </div>
            <div class="relative select-none cursor-pointer">
                <i class="ri-notification-3-line text-xl"></i>
                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center">3</span>
            </div>

        </div>

        <!-- Profile Menu -->
        <div class="divide-y-2 divide-slate-100">
            <div x-data="{ expanded: false, height: 0 }" class="flex flex-col">
                <button @click="expanded = !expanded; height = expanded ? $refs.dropdown.scrollHeight : 0" class="flex justify-between items-center p-1 rounded flex-1">
                    <div class="flex items-center gap-2">
                        <?php
                        if ($admin_profile == null) {
                        ?>
                            <div class="w-14 h-14 rounded-full my-3 p-1 bg-[<?php echo $profile_color ?>] text-white relative select-none">
                                <p class="w-full h-full flex items-center justify-center text-lg font-semibold"><?php echo substr($admin_username, 0, 1); ?></p>
                                <div class="w-3 h-3 bg-green-500 rounded-full absolute bottom-1 right-1"></div>
                            </div>
                        <?php
                        } else { ?>
                            <div class="w-14 h-14 rounded-full my-3 p-1 bg-slate-200 relative select-none">
                                <img class="w-full h-full object-cover rounded-full" src="<?php echo $adminprofile ?>" alt="Profile">
                                <div class="w-3 h-3 bg-green-500 rounded-full absolute bottom-1 right-1"></div>
                            </div>
                        <?php
                        }
                        ?>
                        <div class="text-start">
                            <p class="font-semibold" id="adminUsername"><?php echo $admin_username ?></p>
                            <p class="text-xs text-gray-400">Welcome</p>
                            <p class="text-xs text-gray-700">(<?php echo $admin_role ?>)</p>
                        </div>
                    </div>
                    <i :class="expanded ? 'rotate-180' : 'rotate-0'" class="ri-arrow-up-s-line text-xl transition-transform duration-300"></i>
                </button>

                <div x-ref="dropdown"
                    :style="{ height: expanded ? height + 'px' : '0px' }"
                    class="overflow-hidden transition-all duration-300 select-none pl-3" class="pl-5 mb-2">
                    <a href="admin_profile_edit.php" class="flex items-center gap-3 p-2 rounded-sm text-slate-600 hover:bg-slate-100">
                        <i class="ri-user-3-line text-xl"></i>
                        <span class="font-semibold text-sm">Your profile</span>
                    </a>
                    <a href="admin_signup.php" class="flex items-center gap-3 p-2 rounded-sm text-slate-600 hover:bg-slate-100">
                        <i class="ri-user-add-line text-xl"></i>
                        <span class="font-semibold text-sm">Add another account</span>
                    </a>
                </div>
            </div>
            <div class="flex flex-col pt-2">
                <div class="mb-2">
                    <h1 class="text-xs font-semibold text-gray-500">MAIN HOME</h1>
                    <a href="admin_dashboard.php" class="flex items-center gap-4 p-2 rounded-sm text-slate-600 hover:bg-slate-100 transition-colors duration-300 select-none <?= $current_page === 'admin_dashboard.php' || $current_page === 'user_details.php' ? 'bg-slate-100' : '' ?>">
                        <i class="ri-dashboard-3-line text-xl"></i>
                        <span class="font-semibold text-sm">Dashboard</span>
                    </a>
                </div>
                <a href="role_management.php" class="flex items-center gap-4 p-2 rounded-sm text-slate-600 hover:bg-slate-100 transition-colors duration-300 select-none <?= ($role === '1') ? 'flex' : 'hidden'; ?> <?= $current_page === 'role_management.php' ? 'bg-slate-100' : '' ?>">
                    <i class="ri-settings-3-line text-xl relative">
                        <p class="bg-red-500 rounded-full text-sm text-white w-5 h-5 text-center absolute -top-1 -right-2 select-none <?php echo ($orderCount != 0) ? 'block' : 'hidden'; ?>"><?php echo $orderCount ?></p>
                    </i>
                    <span class="font-semibold text-sm">Role Management</span>
                </a>

                <!-- Product Menu -->
                <div x-data="{ expanded: false, height: 0 }" class="flex flex-col">
                    <button @click="expanded = !expanded; height = expanded ? $refs.dropdown.scrollHeight : 0"
                        class="flex items-center justify-between gap-4 p-2 rounded-sm text-slate-600 hover:bg-slate-100 transition-colors duration-300 select-none <?= ($role === '1' || $role === '5') ? 'flex' : 'hidden'; ?> <?= $current_page === 'add_supplier.php' || $current_page === 'add_product.php' || $current_page === 'product_image.php' || $current_page === 'add_size.php' || $current_page === 'add_producttype.php' ? 'bg-slate-100' : '' ?>">
                        <div class="flex items-center gap-4">
                            <i class="ri-stock-line text-xl"></i>
                            <span class="font-semibold text-sm">Product</span>
                        </div>
                        <i :class="expanded ? 'rotate-180' : 'rotate-0'" class="ri-arrow-up-s-line text-xl transition-transform duration-300"></i>
                    </button>
                    <div
                        x-ref="dropdown"
                        :style="{ height: expanded ? height + 'px' : '0px' }"
                        class="overflow-hidden transition-all duration-300 select-none">
                        <div class="pl-3">
                            <!-- Existing Links -->
                            <a href="../Admin/add_supplier.php" class="flex justify-between text-slate-600 hover:bg-gray-100 p-2 rounded-sm transition-colors duration-300 select-none <?= ($role === '1' || $role === '5') ? 'flex' : 'hidden'; ?>">
                                <div class="flex items-center gap-1">
                                    <i class="ri-group-line text-xl"></i>
                                    <span class="font-semibold text-sm">Add supplier</span>
                                </div>
                                <p class="px-2 text-white bg-blue-950 rounded-sm ml-5"><?php echo $allSupplierCount ?></p>
                            </a>
                            <!-- Add this new link for Dining Menu Management -->
                            <a href="../Admin/add_menu.php" class="flex justify-between text-slate-600 hover:bg-gray-100 p-2 rounded-sm transition-colors duration-300 select-none <?= ($role === '1' || $role === '2') ? 'flex' : 'hidden'; ?>">
                                <div class="flex items-center gap-1">
                                    <i class="ri-restaurant-line text-xl"></i>
                                    <span class="font-semibold text-sm">Dining Menu</span>
                                </div>
                                <!-- You can add a count badge here if needed -->
                                <p class="px-2 text-white bg-blue-950 rounded-sm ml-5"><?php echo $allMenuCount ?></p>
                            </a>
                            <a href="../Admin/add_product.php" class="flex justify-between text-slate-600 hover:bg-gray-100 p-2 rounded-sm transition-colors duration-300 select-none <?= ($role === '1' || $role === '5') ? 'flex' : 'hidden'; ?>">
                                <div class="flex items-center gap-1">
                                    <i class="ri-suitcase-line text-xl"></i>
                                    <span class="font-semibold text-sm">Add product</span>
                                </div>
                                <p class="px-2 text-white bg-blue-950 rounded-sm ml-5"><?php echo $allProductCount ?></p>
                            </a>
                            <a href="../Admin/pricing_markup.php" class="flex justify-between text-slate-600 hover:bg-gray-100 p-2 rounded-sm transition-colors duration-300 select-none <?= ($role === '1' || $role === '5') ? 'flex' : 'hidden'; ?>">
                                <div class="flex items-center gap-1">
                                    <i class="ri-price-tag-3-line text-xl"></i>
                                    <span class="font-semibold text-sm">Pricing & Markup</span>
                                </div>
                            </a>
                            <a href="../Admin/add_producttype.php" class="flex justify-between text-slate-600 hover:bg-gray-100 p-2 rounded-sm transition-colors duration-300 select-none <?= ($role === '1' || $role === '5') ? 'flex' : 'hidden'; ?>">
                                <div class="flex items-center gap-1">
                                    <i class="ri-list-check-3 text-xl"></i>
                                    <span class="font-semibold text-sm">Add product type</span>
                                </div>
                                <p class="px-2 text-white bg-blue-950 rounded-sm ml-5"><?php echo $allProductTypeCount ?></p>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Room Menu -->
                <div x-data="{ roomExpanded: false, subHeight: 0 }" class="flex flex-col">
                    <button @click="roomExpanded = !roomExpanded; subHeight = roomExpanded ? $refs.subDropdown.scrollHeight : 0"
                        class="flex items-center justify-between gap-4 p-2 rounded-sm text-slate-600 hover:bg-gray-100 transition-colors duration-300 select-none <?= ($role === '1' || $role === '4') ? 'flex' : 'hidden'; ?> <?= $current_page === 'add_roomtype.php' || $current_page === 'add_room.php' || $current_page === 'add_rule.php' || $current_page === 'add_facility.php' || $current_page === 'add_facilitytype.php' ? 'bg-slate-100' : '' ?>">
                        <div class="flex items-center gap-4">
                            <i class="ri-hotel-bed-line text-xl"></i>
                            <span class="font-semibold text-sm">Room</span>
                        </div>
                        <i :class="roomExpanded ? 'rotate-180' : 'rotate-0'" class="ri-arrow-up-s-line text-xl transition-transform duration-300"></i>
                    </button>
                    <div
                        x-ref="subDropdown"
                        :style="{ height: roomExpanded ? subHeight + 'px' : '0px' }"
                        class="overflow-hidden transition-all duration-300 pl-3">
                        <a href="../Admin/add_roomtype.php"
                            class="flex justify-between text-slate-600 hover:bg-gray-100 p-2 rounded-sm transition-colors duration-300 select-none <?= ($role === '1' || $role === '4') ? 'flex' : 'hidden'; ?>">
                            <div class="flex items-center gap-1">
                                <i class="ri-hotel-bed-line text-xl"></i>
                                <span class="font-semibold text-sm">Add room type</span>
                            </div>
                            <p class="px-2 text-white bg-blue-950 rounded-sm ml-5"><?php echo $allRoomTypeCount ?></p>
                        </a>
                        <a href="../Admin/add_room.php"
                            class="flex justify-between text-slate-600 hover:bg-gray-100 p-2 rounded-sm transition-colors duration-300 select-none <?= ($role === '1' || $role === '4') ? 'flex' : 'hidden'; ?>">
                            <div class="flex items-center gap-1">
                                <i class="ri-hotel-bed-line text-xl"></i>
                                <span class="font-semibold text-sm">Add room</span>
                            </div>
                            <p class="px-2 text-white bg-blue-950 rounded-sm ml-5"><?= htmlspecialchars($allRoomCount) ?></p>
                        </a>
                        <a href="../Admin/add_rule.php"
                            class="flex justify-between text-slate-600 hover:bg-gray-100 p-2 rounded-sm transition-colors duration-300 select-none <?= ($role === '1' || $role === '4') ? 'flex' : 'hidden'; ?>">
                            <div class="flex items-center gap-1">
                                <i class="ri-file-list-3-line text-xl"></i>
                                <span class="font-semibold text-sm">Add rule</span>
                            </div>
                            <p class="px-2 text-white bg-blue-950 rounded-sm ml-5"><?php echo $allRuleCount ?></p>
                        </a>
                        <a href="../Admin/add_facility.php"
                            class="flex justify-between text-slate-600 hover:bg-gray-100 p-2 rounded-sm transition-colors duration-300 select-none <?= ($role === '1' || $role === '4') ? 'flex' : 'hidden'; ?>">
                            <div class="flex items-center gap-1">
                                <i class="ri-hotel-line text-xl"></i>
                                <span class="font-semibold text-sm">Add facility</span>
                            </div>
                            <p class="px-2 text-white bg-blue-950 rounded-sm ml-5"><?php echo $allFacilityCount ?></p>
                        </a>
                        <a href="../Admin/add_facilitytype.php"
                            class="flex justify-between text-slate-600 hover:bg-gray-100 p-2 rounded-sm transition-colors duration-300 select-none <?= ($role === '1' || $role === '4') ? 'flex' : 'hidden'; ?>">
                            <div class="flex items-center gap-1">
                                <i class="ri-list-check-3 text-xl"></i>
                                <span class="font-semibold text-sm">Add facility type</span>
                            </div>
                            <p class="px-2 text-white bg-blue-950 rounded-sm ml-5"><?php echo $allFacilityTypeCount ?></p>
                        </a>
                    </div>
                </div>

                <!-- Purchase Menu -->
                <div x-data="{ expanded: false, height: 0 }" class="flex flex-col">
                    <button @click="expanded = !expanded; height = expanded ? $refs.dropdown.scrollHeight : 0" class="flex items-center justify-between gap-4 p-2 rounded-sm text-slate-600 hover:bg-slate-100 transition-colors duration-300 select-none <?= ($role === '1' || $role === '3') ? 'flex' : 'hidden'; ?> <?= $current_page === 'product_purchase.php' ? 'bg-slate-100' : '' ?>">
                        <div class="flex items-center gap-4">
                            <i class="ri-shopping-cart-line text-xl"></i>
                            <span class="font-semibold text-sm">Purchase Menu</span>
                        </div>
                        <i :class="expanded ? 'rotate-180' : 'rotate-0'" class="ri-arrow-up-s-line text-xl transition-transform duration-300"></i>
                    </button>
                    <div
                        x-ref="dropdown"
                        :style="{ height: expanded ? height + 'px' : '0px' }"
                        class="overflow-hidden transition-all duration-300 select-none">
                        <div class="pl-3">
                            <a href="../Admin/product_purchase.php" class="text-slate-600 hover:bg-gray-100 p-2 rounded-sm transition-colors duration-300 select-none <?= ($role === '1' || $role === '3') ? 'flex' : 'hidden'; ?>">
                                <div class="flex items-center gap-1">
                                    <i class="ri-truck-line text-xl"></i>
                                    <span class="font-semibold text-sm">Purchase Product</span>
                                </div>
                            </a>
                            <a href="../Admin/purchase_history.php" class="text-slate-600 hover:bg-gray-100 p-2 rounded-sm transition-colors duration-300 select-none <?= ($role === '1' || $role === '3') ? 'flex' : 'hidden'; ?>">
                                <div class="flex items-center gap-1">
                                    <i class="ri-history-line purchase-history-icon text-xl"></i>
                                    <span class="font-semibold text-sm">Purchase History</span>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Bookings & Orders Menu -->
                <div x-data="{ expanded: false, height: 0 }" class="flex flex-col">
                    <button @click="expanded = !expanded; height = expanded ? $refs.dropdown.scrollHeight : 0" class="flex items-center justify-between gap-4 p-2 rounded-sm text-slate-600 hover:bg-slate-100 transition-colors duration-300 select-none <?= ($role === '1' || $role === '2') ? 'flex' : 'hidden'; ?>">
                        <div class="flex items-center gap-4">
                            <i class="ri-archive-drawer-line text-xl"></i>
                            <span class="font-semibold text-sm">Res & Orders</span>
                        </div>
                        <i :class="expanded ? 'rotate-180' : 'rotate-0'" class="ri-arrow-up-s-line text-xl transition-transform duration-300"></i>
                    </button>
                    <div
                        x-ref="dropdown"
                        :style="{ height: expanded ? height + 'px' : '0px' }"
                        class="overflow-hidden transition-all duration-300 select-none">
                        <div class="pl-3">
                            <a href="../Admin/reservation.php" class="flex justify-between text-slate-600 hover:bg-gray-100 p-2 rounded-sm transition-colors duration-300 select-none <?= ($role === '1' || $role === '2') ? 'flex' : 'hidden'; ?>">
                                <div class="flex items-center gap-1">
                                    <i class="ri-booklet-line text-xl"></i>
                                    <span class="font-semibold text-sm">Reservation</span>
                                </div>
                                <p class="px-2 text-white bg-blue-950 rounded-sm ml-5"><?= htmlspecialchars($allReservationCount) ?></p>
                            </a>
                            <a href="../Admin/order.php" class="flex justify-between text-slate-600 hover:bg-gray-100 p-2 rounded-sm transition-colors duration-300 select-none <?= ($role === '1' || $role === '5') ? 'flex' : 'hidden'; ?>">
                                <div class="flex items-center gap-1">
                                    <i class="ri-shopping-bag-2-line text-xl"></i>
                                    <span class="font-semibold text-sm">Order</span>
                                </div>
                                <p class="px-2 text-white bg-blue-950 rounded-sm ml-5"><?php echo $allProductCount ?></p>
                            </a>
                        </div>
                    </div>
                </div>
                <a href="user_contact.php" class="flex items-center gap-4 p-2 rounded-sm text-slate-600 hover:bg-slate-100 transition-colors duration-300 select-none <?= $current_page === 'user_contact.php' ? 'bg-slate-100' : '' ?>">
                    <i class="ri-message-3-line text-xl relative">
                        <p class="bg-red-500 rounded-full text-sm text-white w-5 h-5 text-center absolute -top-1 -right-2 select-none <?php echo ($allContactCount != 0) ? 'block' : 'hidden'; ?>"><?php echo $allContactCount ?></p>
                    </i>
                    <span class="font-semibold text-sm">User Contacts</span>
                </a>
            </div>
        </div>
        <script src="//unpkg.com/alpinejs" defer></script>
    </div>
    <div>
        <div id="adminLogoutBtn" class="flex items-center gap-4 p-2 rounded-sm text-slate-600 hover:bg-slate-100 select-none cursor-pointer">
            <i class="ri-logout-circle-line text-xl"></i>
            <p class="font-semibold text-sm">Logout</p>
        </div>
        <p class="text-xs text-gray-400">© 2025 <span id="year"></span> OpulenceHaven.com™. All rights reserved.</p>
    </div>
</nav>

<!-- Confirmation Modal -->
<div id="adminConfirmModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
    <div class="bg-white max-w-5xl p-6 rounded-md shadow-md text-center">
        <h2 class="text-xl font-semibold text-blue-900 mb-4">Confirm Logout</h2>
        <p class="text-slate-600 mb-2">You are currently signed in as:</p>
        <p class="font-semibold text-gray-800 mb-4">
            <?php echo $_SESSION['UserName'] . ' (' . $_SESSION['AdminEmail'] . ')'; ?>
        </p>
        <p class="text-sm text-gray-500 mb-6">
            Logging out will end your session and remove access to secure areas of your account. Ensure all changes are saved.
        </p>
        <div class="flex justify-end gap-4 select-none">
            <button id="cancelBtn" class="px-4 py-2 bg-gray-200 text-black hover:bg-gray-300 rounded-sm">
                Cancel
            </button>
            <button id="adminConfirmLogoutBtn" class="px-4 py-2 bg-red-600 text-white hover:bg-red-700 rounded-sm">
                Logout
            </button>
        </div>
    </div>
</div>

<!-- Dark Overlay -->
<div id="overlay" class="fixed inset-0 bg-black opacity-50 hidden z-30"></div>
<div id="darkOverlay2" class="fixed inset-0 bg-black bg-opacity-50 opacity-0 invisible  z-40 transition-opacity duration-300"></div>