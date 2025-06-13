<?php
include('../includes/AdminPagination.php');

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

// Construct the admin query based on search and role filter with LIMIT and OFFSET
if ($filterRoleID !== 'random' && !empty($searchAdminQuery)) {
    $adminSelect = "SELECT * FROM admintb WHERE RoleID = '$filterRoleID' AND (FirstName LIKE '%$searchAdminQuery%' OR LastName LIKE '%$searchAdminQuery%' OR UserName LIKE '%$searchAdminQuery%' OR AdminEmail LIKE '%$searchAdminQuery%') LIMIT $rowsPerPage OFFSET $offset";
} elseif ($filterRoleID !== 'random') {
    $adminSelect = "SELECT * FROM admintb WHERE RoleID = '$filterRoleID' LIMIT $rowsPerPage OFFSET $offset";
} elseif (!empty($searchAdminQuery)) {
    $adminSelect = "SELECT * FROM admintb WHERE FirstName LIKE '%$searchAdminQuery%' OR LastName LIKE '%$searchAdminQuery%' OR UserName LIKE '%$searchAdminQuery%' OR AdminEmail LIKE '%$searchAdminQuery%' LIMIT $rowsPerPage OFFSET $offset";
} else {
    $adminSelect = "SELECT * FROM admintb LIMIT $rowsPerPage OFFSET $offset";
}

// Execute the query to fetch admins
$adminSelectQuery = $connect->query($adminSelect);
$admins = [];

if (mysqli_num_rows($adminSelectQuery) > 0) {
    while ($row = $adminSelectQuery->fetch_assoc()) {
        $admins[] = $row;
    }
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

// Initialize search variables for room type
$searchRoomTypeQuery = isset($_GET['roomtype_search']) ? mysqli_real_escape_string($connect, $_GET['roomtype_search']) : '';

// Construct the room type query based on search
if (!empty($searchRoomTypeQuery)) {
    $roomTypeSelect = "SELECT * FROM roomtypetb WHERE RoomType LIKE '%$searchRoomTypeQuery%' OR RoomDescription LIKE '%$searchRoomTypeQuery%' LIMIT $rowsPerPage OFFSET $roomTypeOffset";
} else {
    $roomTypeSelect = "SELECT * FROM roomtypetb LIMIT $rowsPerPage OFFSET $roomTypeOffset";
}

$roomTypeSelectQuery = $connect->query($roomTypeSelect);
$roomTypes = [];

if (mysqli_num_rows($roomTypeSelectQuery) > 0) {
    while ($row = $roomTypeSelectQuery->fetch_assoc()) {
        $roomTypes[] = $row;
    }
}

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

// Construct the contact query based on search and status filter
if ($filterStatus !== 'random' && !empty($searchContactQuery)) {
    $contactSelect = "SELECT c.*, u.ProfileBgColor, u.UserName
                      FROM contacttb c 
                      LEFT JOIN usertb u ON c.UserID = u.UserID 
                      WHERE c.Status = '$filterStatus' 
                      AND (c.FullName LIKE '%$searchContactQuery%' 
                           OR c.UserEmail LIKE '%$searchContactQuery%' 
                           OR c.Country LIKE '%$searchContactQuery%') 
                      $dateCondition 
                      ORDER BY c.ContactID DESC 
                      LIMIT $rowsPerPage OFFSET $contactOffset";
} elseif ($filterStatus !== 'random') {
    $contactSelect = "SELECT c.*, u.ProfileBgColor, u.UserName
                      FROM contacttb c 
                      LEFT JOIN usertb u ON c.UserID = u.UserID 
                      WHERE c.Status = '$filterStatus' 
                      $dateCondition 
                      ORDER BY c.ContactID DESC 
                      LIMIT $rowsPerPage OFFSET $contactOffset";
} elseif (!empty($searchContactQuery)) {
    $contactSelect = "SELECT c.*, u.ProfileBgColor, u.UserName
                      FROM contacttb c 
                      LEFT JOIN usertb u ON c.UserID = u.UserID 
                      WHERE (c.FullName LIKE '%$searchContactQuery%' 
                             OR c.UserEmail LIKE '%$searchContactQuery%' 
                             OR c.Country LIKE '%$searchContactQuery%') 
                      $dateCondition 
                      ORDER BY c.ContactID DESC 
                      LIMIT $rowsPerPage OFFSET $contactOffset";
} else {
    $contactSelect = "SELECT c.*, u.ProfileBgColor, u.UserName
                      FROM contacttb c 
                      LEFT JOIN usertb u ON c.UserID = u.UserID 
                      WHERE 1 
                      $dateCondition 
                      ORDER BY c.ContactID DESC 
                      LIMIT $rowsPerPage OFFSET $contactOffset";
}

$contactSelectQuery = $connect->query($contactSelect);
$contacts = [];

if (mysqli_num_rows($contactSelectQuery) > 0) {
    while ($row = $contactSelectQuery->fetch_assoc()) {
        $contacts[] = $row;
    }
}

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
        <!-- Logo -->
        <div class="flex items-end gap-1 select-none">
            <a href="../Admin/AdminDashboard.php">
                <img src="../UserImages/Screenshot_2024-11-29_201534-removebg-preview.png" class="w-28 select-none" alt="Logo">
            </a>
            <p class="text-amber-500 text-sm font-semibold">ADMIN</p>
        </div>

        <!-- Profile Menu -->
        <div class="divide-y-2 divide-slate-100">
            <div x-data="{ expanded: false, height: 0 }" class="flex flex-col">
                <button @click="expanded = !expanded; height = expanded ? $refs.dropdown.scrollHeight : 0" class="flex justify-between items-center p-1 rounded">
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
                    <a href="AdminProfileEdit.php" class="flex items-center gap-3 p-2 rounded-sm text-slate-600 hover:bg-slate-100">
                        <i class="ri-user-3-line text-xl"></i>
                        <span class="font-semibold text-sm">Your profile</span>
                    </a>
                    <a href="AdminSignUp.php" class="flex items-center gap-3 p-2 rounded-sm text-slate-600 hover:bg-slate-100">
                        <i class="ri-user-add-line text-xl"></i>
                        <span class="font-semibold text-sm">Add another account</span>
                    </a>
                </div>
            </div>
            <div class="flex flex-col pt-2">
                <div class="mb-2">
                    <h1 class="text-xs font-semibold text-gray-500">MAIN HOME</h1>
                    <a href="AdminDashboard.php" class="flex items-center gap-4 p-2 rounded-sm text-slate-600 hover:bg-slate-100 transition-colors duration-300 select-none <?= $current_page === 'AdminDashboard.php' || $current_page === 'UserDetails.php' ? 'bg-slate-100' : '' ?>">
                        <i class="ri-dashboard-3-line text-xl"></i>
                        <span class="font-semibold text-sm">Dashboard</span>
                    </a>
                </div>
                <a href="RoleManagement.php" class="flex items-center gap-4 p-2 rounded-sm text-slate-600 hover:bg-slate-100 transition-colors duration-300 select-none <?= ($role === '1') ? 'flex' : 'hidden'; ?> <?= $current_page === 'RoleManagement.php' ? 'bg-slate-100' : '' ?>">
                    <i class="ri-settings-3-line text-xl relative">
                        <p class="bg-red-500 rounded-full text-sm text-white w-5 h-5 text-center absolute -top-1 -right-2 select-none <?php echo ($orderCount != 0) ? 'block' : 'hidden'; ?>"><?php echo $orderCount ?></p>
                    </i>
                    <span class="font-semibold text-sm">Role Management</span>
                </a>

                <!-- Product Menu -->
                <div x-data="{ expanded: false, height: 0 }" class="flex flex-col">
                    <button @click="expanded = !expanded; height = expanded ? $refs.dropdown.scrollHeight : 0"
                        class="flex items-center justify-between gap-4 p-2 rounded-sm text-slate-600 hover:bg-slate-100 transition-colors duration-300 select-none <?= ($role === '1' || $role === '5') ? 'flex' : 'hidden'; ?> <?= $current_page === 'AddSupplier.php' || $current_page === 'AddProduct.php' || $current_page === 'ProductImage.php' || $current_page === 'AddSize.php' || $current_page === 'AddProductType.php' ? 'bg-slate-100' : '' ?>">
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
                            <a href="../Admin/AddSupplier.php" class="flex justify-between text-slate-600 hover:bg-gray-100 p-2 rounded-sm transition-colors duration-300 select-none <?= ($role === '1' || $role === '5') ? 'flex' : 'hidden'; ?>">
                                <div class="flex items-center gap-1">
                                    <i class="ri-group-line text-xl"></i>
                                    <span class="font-semibold text-sm">Add supplier</span>
                                </div>
                                <p class="px-2 text-white bg-blue-950 rounded-sm ml-5"><?php echo $allSupplierCount ?></p>
                            </a>
                            <a href="../Admin/AddProduct.php" class="flex justify-between text-slate-600 hover:bg-gray-100 p-2 rounded-sm transition-colors duration-300 select-none <?= ($role === '1' || $role === '5') ? 'flex' : 'hidden'; ?>">
                                <div class="flex items-center gap-1">
                                    <i class="ri-suitcase-line text-xl"></i>
                                    <span class="font-semibold text-sm">Add product</span>
                                </div>
                                <p class="px-2 text-white bg-blue-950 rounded-sm ml-5"><?php echo $allProductCount ?></p>
                            </a>
                            <a href="../Admin/AddProductType.php" class="flex justify-between text-slate-600 hover:bg-gray-100 p-2 rounded-sm transition-colors duration-300 select-none <?= ($role === '1' || $role === '5') ? 'flex' : 'hidden'; ?>">
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
                        class="flex items-center justify-between gap-4 p-2 rounded-sm text-slate-600 hover:bg-gray-100 transition-colors duration-300 select-none <?= ($role === '1' || $role === '4') ? 'flex' : 'hidden'; ?> <?= $current_page === 'AddRoomType.php' || $current_page === 'AddRoom.php' || $current_page === 'AddRule.php' || $current_page === 'AddFacility.php' || $current_page === 'AddFacilityType.php' ? 'bg-slate-100' : '' ?>">
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
                        <a href="../Admin/AddRoomType.php"
                            class="flex justify-between text-slate-600 hover:bg-gray-100 p-2 rounded-sm transition-colors duration-300 select-none <?= ($role === '1' || $role === '4') ? 'flex' : 'hidden'; ?>">
                            <div class="flex items-center gap-1">
                                <i class="ri-hotel-bed-line text-xl"></i>
                                <span class="font-semibold text-sm">Add room type</span>
                            </div>
                            <p class="px-2 text-white bg-blue-950 rounded-sm ml-5"><?php echo $allRoomTypeCount ?></p>
                        </a>
                        <a href="../Admin/AddRoom.php"
                            class="flex justify-between text-slate-600 hover:bg-gray-100 p-2 rounded-sm transition-colors duration-300 select-none <?= ($role === '1' || $role === '4') ? 'flex' : 'hidden'; ?>">
                            <div class="flex items-center gap-1">
                                <i class="ri-hotel-bed-line text-xl"></i>
                                <span class="font-semibold text-sm">Add room</span>
                            </div>
                            <p class="px-2 text-white bg-blue-950 rounded-sm ml-5"><?= htmlspecialchars($allRoomCount) ?></p>
                        </a>
                        <a href="../Admin/AddRule.php"
                            class="flex justify-between text-slate-600 hover:bg-gray-100 p-2 rounded-sm transition-colors duration-300 select-none <?= ($role === '1' || $role === '4') ? 'flex' : 'hidden'; ?>">
                            <div class="flex items-center gap-1">
                                <i class="ri-file-list-3-line text-xl"></i>
                                <span class="font-semibold text-sm">Add rule</span>
                            </div>
                            <p class="px-2 text-white bg-blue-950 rounded-sm ml-5"><?php echo $allRuleCount ?></p>
                        </a>
                        <a href="../Admin/AddFacility.php"
                            class="flex justify-between text-slate-600 hover:bg-gray-100 p-2 rounded-sm transition-colors duration-300 select-none <?= ($role === '1' || $role === '4') ? 'flex' : 'hidden'; ?>">
                            <div class="flex items-center gap-1">
                                <i class="ri-hotel-line text-xl"></i>
                                <span class="font-semibold text-sm">Add facility</span>
                            </div>
                            <p class="px-2 text-white bg-blue-950 rounded-sm ml-5"><?php echo $allFacilityCount ?></p>
                        </a>
                        <a href="../Admin/AddFacilityType.php"
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
                    <button @click="expanded = !expanded; height = expanded ? $refs.dropdown.scrollHeight : 0" class="flex items-center justify-between gap-4 p-2 rounded-sm text-slate-600 hover:bg-slate-100 transition-colors duration-300 select-none <?= ($role === '1' || $role === '3') ? 'flex' : 'hidden'; ?> <?= $current_page === 'ProductPurchase.php' ? 'bg-slate-100' : '' ?>">
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
                            <a href="../Admin/ProductPurchase.php" class="text-slate-600 hover:bg-gray-100 p-2 rounded-sm transition-colors duration-300 select-none <?= ($role === '1' || $role === '3') ? 'flex' : 'hidden'; ?>">
                                <div class="flex items-center gap-1">
                                    <i class="ri-truck-line text-xl"></i>
                                    <span class="font-semibold text-sm">Purchase Product</span>
                                </div>
                            </a>
                            <a href="../Admin/AddProduct.php" class="text-slate-600 hover:bg-gray-100 p-2 rounded-sm transition-colors duration-300 select-none <?= ($role === '1' || $role === '3') ? 'flex' : 'hidden'; ?>">
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
                            <span class="font-semibold text-sm">Bookings & Orders</span>
                        </div>
                        <i :class="expanded ? 'rotate-180' : 'rotate-0'" class="ri-arrow-up-s-line text-xl transition-transform duration-300"></i>
                    </button>
                    <div
                        x-ref="dropdown"
                        :style="{ height: expanded ? height + 'px' : '0px' }"
                        class="overflow-hidden transition-all duration-300 select-none">
                        <div class="pl-3">
                            <a href="../Admin/Booking.php" class="flex justify-between text-slate-600 hover:bg-gray-100 p-2 rounded-sm transition-colors duration-300 select-none <?= ($role === '1' || $role === '2') ? 'flex' : 'hidden'; ?>">
                                <div class="flex items-center gap-1">
                                    <i class="ri-booklet-line text-xl"></i>
                                    <span class="font-semibold text-sm">Booking</span>
                                </div>
                                <p class="px-2 text-white bg-blue-950 rounded-sm ml-5"><?= htmlspecialchars($allReservationCount) ?></p>
                            </a>
                            <a href="../Admin/AddProduct.php" class="flex justify-between text-slate-600 hover:bg-gray-100 p-2 rounded-sm transition-colors duration-300 select-none <?= ($role === '1' || $role === '5') ? 'flex' : 'hidden'; ?>">
                                <div class="flex items-center gap-1">
                                    <i class="ri-shopping-bag-2-line text-xl"></i>
                                    <span class="font-semibold text-sm">Order</span>
                                </div>
                                <p class="px-2 text-white bg-blue-950 rounded-sm ml-5"><?php echo $allProductCount ?></p>
                            </a>
                        </div>
                    </div>
                </div>
                <a href="UserContact.php" class="flex items-center gap-4 p-2 rounded-sm text-slate-600 hover:bg-slate-100 transition-colors duration-300 select-none <?= $current_page === 'UserContact.php' ? 'bg-slate-100' : '' ?>">
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
        <p class="text-xs text-gray-400">© <span id="year"></span> OpulenceHaven.com™. All rights reserved.</p>
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