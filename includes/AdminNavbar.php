<?php
$adminID = $_SESSION['AdminID'];
$username = $_SESSION['UserName'];

// Fetch admin profile
$adminProfileQuery = "SELECT AdminProfile, RoleID FROM admintb WHERE AdminID = '$adminID'";
$adminProfileResult = mysqli_query($connect, $adminProfileQuery);
$adminProfileRow = mysqli_fetch_assoc($adminProfileResult);
$adminprofile = $adminProfileRow['AdminProfile'];
$role = $adminProfileRow['RoleID'];

// Initialize search and filter variables
$searchAdminQuery = isset($_GET['acc_search']) ? mysqli_real_escape_string($connect, $_GET['acc_search']) : '';
$filterRoleID = isset($_GET['sort']) ? $_GET['sort'] : 'random';

// Construct the admin query based on search and role filter
if ($filterRoleID !== 'random' && !empty($searchAdminQuery)) {
    $adminSelect = "SELECT * FROM admintb WHERE RoleID = '$filterRoleID' AND (FirstName LIKE '%$searchAdminQuery%' OR LastName LIKE '%$searchAdminQuery%' OR UserName LIKE '%$searchAdminQuery%' OR AdminEmail LIKE '%$searchAdminQuery%')";
} elseif ($filterRoleID !== 'random') {
    $adminSelect = "SELECT * FROM admintb WHERE RoleID = '$filterRoleID'";
} elseif (!empty($searchAdminQuery)) {
    $adminSelect = "SELECT * FROM admintb WHERE FirstName LIKE '%$searchAdminQuery%' OR LastName LIKE '%$searchAdminQuery%' OR UserName LIKE '%$searchAdminQuery%' OR AdminEmail LIKE '%$searchAdminQuery%'";
} else {
    $adminSelect = "SELECT * FROM admintb";
}

$adminSelectQuery = mysqli_query($connect, $adminSelect);
$admins = [];

if (mysqli_num_rows($adminSelectQuery) > 0) {
    while ($row = mysqli_fetch_assoc($adminSelectQuery)) {
        $admins[] = $row;
    }
}

$searchSupplierQuery = isset($_GET['supplier_search']) ? mysqli_real_escape_string($connect, $_GET['supplier_search']) : '';
$filterSupplierID = isset($_GET['sort']) ? $_GET['sort'] : 'random';

// Construct the supplier query based on search and role filter
if ($filterSupplierID !== 'random' && !empty($searchSupplierQuery)) {
    $supplierSelect = "SELECT * FROM suppliertb WHERE ProductTypeID = '$filterSupplierID' AND (SupplierName LIKE '%$searchSupplierQuery%' OR SupplierEmail LIKE '%$searchSupplierQuery%' OR SupplierContact LIKE '%$searchSupplierQuery%' OR SupplierCompany LIKE '%$searchSupplierQuery%' OR Country LIKE '%$searchSupplierQuery%')";
} elseif ($filterSupplierID !== 'random') {
    $supplierSelect = "SELECT * FROM suppliertb WHERE ProductTypeID = '$filterSupplierID'";
} elseif (!empty($searchSupplierQuery)) {
    $supplierSelect = "SELECT * FROM suppliertb WHERE SupplierName LIKE '%$searchSupplierQuery%' OR SupplierEmail LIKE '%$searchSupplierQuery%' OR SupplierContact LIKE '%$searchSupplierQuery%' OR SupplierCompany LIKE '%$searchSupplierQuery%' OR Country LIKE '%$searchSupplierQuery%'";
} else {
    $supplierSelect = "SELECT * FROM suppliertb";
}

$supplierSelectQuery = mysqli_query($connect, $supplierSelect);
$suppliers = [];

if (mysqli_num_rows($supplierSelectQuery) > 0) {
    while ($row = mysqli_fetch_assoc($supplierSelectQuery)) {
        $suppliers[] = $row;
    }
}

$searchProductTypeQuery = isset($_GET['producttype_search']) ? mysqli_real_escape_string($connect, $_GET['producttype_search']) : '';
$filterProductTypeID = isset($_GET['sort']) ? $_GET['sort'] : 'random';

// Construct the supplier query based on search and role filter
if (!empty($searchProductTypeQuery)) {
    $productTypeSelect = "SELECT * FROM producttypetb WHERE ProductType LIKE '%$searchProductTypeQuery%' OR Description LIKE '%$searchProductTypeQuery%'";
} else {
    $productTypeSelect = "SELECT * FROM producttypetb";
}

$productTypeSelectQuery = mysqli_query($connect, $productTypeSelect);
$productTypes = [];

if (mysqli_num_rows($productTypeSelectQuery) > 0) {
    while ($row = mysqli_fetch_assoc($productTypeSelectQuery)) {
        $productTypes[] = $row;
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
$adminCountResult = mysqli_query($connect, $adminCountQuery);
$adminCountRow = mysqli_fetch_assoc($adminCountResult);
$adminCount = $adminCountRow['count'];

// Construct the supplier count query based on search and product type filter
if ($filterSupplierID !== 'random' && !empty($searchSupplierQuery)) {
    $supplierQuery = "SELECT COUNT(*) as count FROM suppliertb WHERE ProductTypeID = '$filterSupplierID' AND (SupplierName LIKE '%$searchSupplierQuery%' OR SupplierEmail LIKE '%$searchSupplierQuery%' OR SupplierContact LIKE '%$searchSupplierQuery%' OR SupplierCompany LIKE '%$searchSupplierQuery%' OR Country LIKE '%$searchSupplierQuery%')";
} elseif ($filterSupplierID !== 'random') {
    $supplierQuery = "SELECT COUNT(*) as count FROM suppliertb WHERE ProductTypeID = '$filterSupplierID'";
} elseif (!empty($searchSupplierQuery)) {
    $supplierQuery = "SELECT COUNT(*) as count FROM suppliertb WHERE SupplierName LIKE '%$searchSupplierQuery%' OR SupplierEmail LIKE '%$searchSupplierQuery%' OR SupplierContact LIKE '%$searchSupplierQuery%' OR SupplierCompany LIKE '%$searchSupplierQuery%' OR Country LIKE '%$searchSupplierQuery%'";
} else {
    $supplierQuery = "SELECT COUNT(*) as count FROM suppliertb";
}

// Execute the count query
$supplierResult = mysqli_query($connect, $supplierQuery);
$supplierRow = mysqli_fetch_assoc($supplierResult);
$supplierCount = $supplierRow['count'];
// Fetch all supplier count
$supplierCountQuery = "SELECT COUNT(*) as count FROM suppliertb";
$supplierCountResult = mysqli_query($connect, $supplierCountQuery);
$supplierCountRow = mysqli_fetch_assoc($supplierCountResult);
$allSupplierCount = $supplierCountRow['count'];

// Construct the prooducttype count query based on search and product type filter
if (!empty($searchProductTypeQuery)) {
    $prooducTypeQuery = "SELECT COUNT(*) as count FROM producttypetb WHERE ProductType LIKE '%$searchProductTypeQuery%' OR Description LIKE '%$searchProductTypeQuery%'";
} else {
    $prooducTypeQuery = "SELECT COUNT(*) as count FROM producttypetb";
}

// Execute the count query
$productTypeResult = mysqli_query($connect, $prooducTypeQuery);
$productTypeRow = mysqli_fetch_assoc($productTypeResult);
$productTypeCount = $productTypeRow['count'];
// Fetch product type count
$productTypeCountQuery = "SELECT COUNT(*) as count FROM producttypetb";
$productTypeCountResult = mysqli_query($connect, $productTypeCountQuery);
$productTypeCountRow = mysqli_fetch_assoc($productTypeCountResult);
$allProductTypeCount = $productTypeCountRow['count'];

// Fetch product count
$productCountQuery = "SELECT COUNT(*) as count FROM producttb";
$productCountResult = mysqli_query($connect, $productCountQuery);
$productCountRow = mysqli_fetch_assoc($productCountResult);
$productCount = $productCountRow['count'];

$select = "SELECT admintb.*, roletb.Role 
    FROM admintb 
    INNER JOIN roletb ON admintb.RoleID = roletb.RoleID 
    WHERE admintb.RoleID = '$role'
";
$query = mysqli_query($connect, $select);

if (mysqli_num_rows($query) > 0) {
    while ($row = mysqli_fetch_assoc($query)) {
        $admin_id = $row['AdminID'];
        $role_id = $row['RoleID'];
        $admin_role = $row['Role'];
    }
}
?>

<!-- Hamburger Menu Button -->
<button id="menu-toggle" class="fixed top-4 right-4 z-50 md:hidden p-2 backdrop-blur-sm text-amber-500 rounded shadow">
    <i class="ri-menu-line text-2xl"></i>
</button>

<!-- Sidebar -->
<nav id="sidebar" class="fixed top-0 left-0 h-full w-full sm:w-64 md:w-[250px] p-4 flex flex-col justify-between bg-white shadow-lg transform -translate-x-full md:translate-x-0 transition-all duration-300 z-40">
    <div>
        <!-- Logo -->
        <div class="flex items-end gap-1 select-none">
            <a href="../Admin/AdminDashboard.php">
                <img src="../UserImages/Screenshot_2024-11-29_201534-removebg-preview.png" class="w-28 select-none" alt="Logo">
            </a>
            <p class="text-amber-500 text-sm font-semibold">ADMIN</p>
        </div>
        <div class="divide-y-2 divide-slate-100">
            <div x-data="{ expanded: false, height: 0 }" class="flex flex-col">
                <button @click="expanded = !expanded; height = expanded ? $refs.dropdown.scrollHeight : 0" class="flex justify-between items-center p-1 rounded">
                    <div class="flex items-center gap-2">
                        <div class="w-14 h-14 rounded-full my-3 p-1 bg-slate-200 relative select-none">
                            <img class="w-full h-full object-cover rounded-full" src="<?php echo $adminprofile ?>" alt="Profile">
                            <div class="w-3 h-3 bg-green-500 rounded-full absolute bottom-1 right-1"></div>
                        </div>
                        <div class="text-start">
                            <p class="font-semibold"><?php echo $username ?></p>
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
                <a href="AdminDashboard.php" class="flex items-center gap-4 p-2 rounded-sm text-slate-600 hover:bg-slate-100 transition-colors duration-300 select-none">
                    <i class="ri-dashboard-3-line text-xl"></i>
                    <span class="font-semibold text-sm">Dashboard</span>
                </a>
                <a href="RoleManagement.php" class="flex items-center gap-4 p-2 rounded-sm text-slate-600 hover:bg-slate-100 transition-colors duration-300 select-none <?= ($role === '1') ? 'flex' : 'hidden'; ?>">
                    <i class="ri-settings-3-line text-xl relative">
                        <p class="bg-red-500 rounded-full text-sm text-white w-5 h-5 text-center absolute -top-1 -right-2 select-none <?php echo ($orderCount != 0) ? 'block' : 'hidden'; ?>"><?php echo $orderCount ?></p>
                    </i>
                    <span class="font-semibold text-sm">Role Management</span>
                </a>
                <div x-data="{ expanded: false, height: 0 }" class="flex flex-col">
                    <button @click="expanded = !expanded; height = expanded ? $refs.dropdown.scrollHeight : 0" class="flex items-center justify-between gap-4 p-2 rounded-sm text-slate-600 hover:bg-slate-100 transition-colors duration-300 select-none <?= ($role === '3') ? 'hidden' : 'flex'; ?>">
                        <div class="flex items-center gap-4">
                            <i class="ri-stock-line text-xl"></i>
                            <span class="font-semibold text-sm">Inventory</span>
                        </div>
                        <i :class="expanded ? 'rotate-180' : 'rotate-0'" class="ri-arrow-up-s-line text-xl transition-transform duration-300"></i>
                    </button>
                    <div
                        x-ref="dropdown"
                        :style="{ height: expanded ? height + 'px' : '0px' }"
                        class="overflow-hidden transition-all duration-300 select-none">
                        <div class="pl-3">
                            <a href="../Admin/AddSupplier.php" class="flex justify-between text-slate-600 hover:bg-gray-100 p-2 rounded-sm transition-colors duration-300 select-none <?= ($role === '1') ? 'flex' : 'hidden'; ?>">
                                <div class="flex items-center gap-1">
                                    <i class="ri-group-line text-xl"></i>
                                    <span class="font-semibold text-sm">Add supplier</span>
                                </div>
                                <p class="px-2 text-white bg-blue-950 rounded-sm ml-5"><?php echo $allSupplierCount ?></p>
                            </a>
                            <a href="../Admin/AddProduct.php" class="flex justify-between text-slate-600 hover:bg-gray-100 p-2 rounded-sm transition-colors duration-300 select-none <?= ($role === '1' || $role === '5') ? 'flex' : 'hidden'; ?>">
                                <div class="flex items-center gap-1">
                                    <i class="ri-shirt-line text-xl"></i>
                                    <span class="font-semibold text-sm">Add product</span>
                                </div>
                                <p class="px-2 text-white bg-blue-950 rounded-sm ml-5"><?php echo $productCount ?></p>
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
                <div x-data="{ expanded: false, height: 0 }" class="flex flex-col">
                    <button @click="expanded = !expanded; height = expanded ? $refs.dropdown.scrollHeight : 0" class="flex items-center justify-between gap-4 p-2 rounded-sm text-slate-600 hover:bg-slate-100 transition-colors duration-300 select-none <?= ($role === '1' || $role === '3') ? 'flex' : 'hidden'; ?>">
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
                            <a href="../Admin/AddSupplier.php" class="flex justify-between text-slate-600 hover:bg-gray-100 p-2 rounded-sm transition-colors duration-300 select-none <?= ($role === '1' || $role === '3') ? 'flex' : 'hidden'; ?>">
                                <div class="flex items-center gap-1">
                                    <i class="ri-truck-line text-xl"></i>
                                    <span class="font-semibold text-sm">Purchase Product</span>
                                </div>
                                <p class="px-2 text-white bg-blue-950 rounded-sm ml-5"><?= htmlspecialchars($supplierCount) ?></p>
                            </a>
                            <a href="../Admin/AddProduct.php" class="flex justify-between text-slate-600 hover:bg-gray-100 p-2 rounded-sm transition-colors duration-300 select-none <?= ($role === '1' || $role === '3') ? 'flex' : 'hidden'; ?>">
                                <div class="flex items-center gap-1">
                                    <i class="ri-history-line purchase-history-icon text-xl"></i>
                                    <span class="font-semibold text-sm">Purchase History</span>
                                </div>
                                <p class="px-2 text-white bg-blue-950 rounded-sm ml-5"><?php echo $productCount ?></p>
                            </a>
                        </div>
                    </div>
                </div>

                <a href="CusOrder.php" class="flex items-center gap-4 p-2 rounded-sm text-slate-600 hover:bg-slate-100 transition-colors duration-300 select-none <?= ($role === '1' || $role === '5') ? 'flex' : 'hidden'; ?>">
                    <i class="ri-shopping-bag-2-line text-xl relative">
                        <p class="bg-red-500 rounded-full text-sm text-white w-5 h-5 text-center absolute -top-1 -right-2 select-none <?php echo ($orderCount != 0) ? 'block' : 'hidden'; ?>"><?php echo $orderCount ?></p>
                    </i>
                    <span class="font-semibold text-sm">Orders</span>
                </a>
                <a href="UserContact.php" class="flex items-center gap-4 p-2 rounded-sm text-slate-600 hover:bg-slate-100 transition-colors duration-300 select-none">
                    <i class="ri-message-3-line text-xl relative">
                        <p class="bg-red-500 rounded-full text-sm text-white w-5 h-5 text-center absolute -top-1 -right-2 select-none <?php echo ($cuscontactCount != 0) ? 'block' : 'hidden'; ?>"><?php echo $cuscontactCount ?></p>
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
            <button id="cancelBtn" class="px-4 py-2 bg-gray-200 text-black hover:bg-gray-300">
                Cancel
            </button>
            <button id="adminConfirmLogoutBtn" class="px-4 py-2 bg-red-600 text-white hover:bg-red-700">
                Logout
            </button>
        </div>
    </div>
</div>

<!-- Dark Overlay -->
<div id="overlay" class="fixed inset-0 bg-black opacity-50 hidden z-30"></div>
<div id="darkOverlay2" class="fixed inset-0 bg-black bg-opacity-50 opacity-0 invisible  z-40 transition-opacity duration-300"></div>