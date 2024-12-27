<?php
// $adminEmail = $_SESSION['AdminEmail'];

// $adminSelect = "SELECT * FROM admintb
//                 WHERE AdminEmail = '$adminEmail'";

// $adminSelectQuery = mysqli_query($connect, $adminSelect);
// $count = mysqli_num_rows($adminSelectQuery);

// for ($i = 0; $i < $count; $i++) {
//     $array = mysqli_fetch_array($adminSelectQuery);
//     $adminprofile = $array['AdminProfile'];
//     $adminusername = $array['AdminUserName'];
//     $adminPosition = $array['AdminPosition'];
// }

// // Count the number of product types
// $productTypeCountQuery = "SELECT COUNT(*) as count FROM producttypetb";
// $productTypeCountResult = mysqli_query($connect, $productTypeCountQuery);
// $productTypeCountRow = mysqli_fetch_assoc($productTypeCountResult);
// $productTypeCount = $productTypeCountRow['count'];

// $productCountQuery = "SELECT COUNT(*) as count FROM producttb";
// $productCountResult = mysqli_query($connect, $productCountQuery);
// $productCountRow = mysqli_fetch_assoc($productCountResult);
// $productCount = $productCountRow['count'];

// // Fetch customer count
// $customerCountQuery = "SELECT COUNT(*) as count FROM customertb";
// $customerCountResult = mysqli_query($connect, $customerCountQuery);
// $customerCountRow = mysqli_fetch_assoc($customerCountResult);
// $customerCount = $customerCountRow['count'];

// // Fetch suplier count
// $supplierCountQuery = "SELECT COUNT(*) as count FROM suppliertb";
// $supplierCountResult = mysqli_query($connect, $supplierCountQuery);
// $supplierCountRow = mysqli_fetch_assoc($supplierCountResult);
// $supplierCount = $supplierCountRow['count'];

// // Fetch cuscontact count
// $cuscontactCountQuery = "SELECT COUNT(*) as count FROM cuscontacttb WHERE Status = 'Contacted'";
// $cuscontactCountResult = mysqli_query($connect, $cuscontactCountQuery);
// $cuscontactCountRow = mysqli_fetch_assoc($cuscontactCountResult);
// $cuscontactCount = $cuscontactCountRow['count'];

// // Fetch cuscontact count
// $orderCountQuery = "SELECT COUNT(*) as count FROM ordertb WHERE Status = 'Pending'";
// $orderCountResult = mysqli_query($connect, $orderCountQuery);
// $orderCountRow = mysqli_fetch_assoc($orderCountResult);
// $orderCount = $orderCountRow['count'];
?>

<!-- Hamburger Menu Button -->
<button id="menu-toggle" class="fixed top-4 right-4 z-50 md:hidden p-2 backdrop-blur-sm text-indigo-500 rounded shadow">
    <i class="ri-menu-line text-2xl"></i>
</button>

<!-- Sidebar -->
<nav id="sidebar" class="fixed top-0 left-0 h-full w-64 md:w-[250px] p-4 flex flex-col justify-between bg-white shadow-lg transform -translate-x-full md:translate-x-0 transition-transform duration-300 z-40">
    <div>
        <!-- Logo -->
        <div class="flex items-end gap-1 select-none">
            <a href="../User/AdminDashboard.php">
                <img src="../UserImages/Screenshot_2024-11-29_201534-removebg-preview.png" class="w-28 select-none" alt="Logo">
            </a>
            <p class="text-amber-500 text-sm font-semibold">ADMIN</p>
        </div>
        <div class="divide-y-2 divide-slate-100">
            <div x-data="{ expanded: false, height: 0 }" class="flex flex-col">
                <button @click="expanded = !expanded; height = expanded ? $refs.dropdown.scrollHeight : 0" class="flex justify-between items-center p-1 rounded">
                    <div class="flex items-center gap-2">
                        <div class="w-12 h-12 rounded-full my-3 relative select-none">
                            <img class="w-full h-full object-cover rounded-full" src="<?php echo $adminprofile ?>" alt="Profile">
                            <div class="w-3 h-3 bg-green-500 rounded-full absolute bottom-0 right-0"></div>
                        </div>
                        <div class="text-start">
                            <p class="font-semibold">Brandon</p>
                            <p class="text-xs text-gray-400">Welcome</p>
                        </div>
                    </div>
                    <i :class="expanded ? 'rotate-180' : 'rotate-0'" class="ri-arrow-up-s-line text-xl transition-transform duration-300"></i>
                </button>

                <div x-ref="dropdown"
                    :style="{ height: expanded ? height + 'px' : '0px' }"
                    class="overflow-hidden transition-all duration-300 select-none pl-3" class="pl-5 mb-2">
                    <a href="AdminProfile.php" class="flex items-center gap-3 p-2 rounded-sm text-slate-600 hover:bg-slate-100">
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
                <a href="AdminDashboard.php" class="flex items-center gap-4 p-2 rounded-sm text-slate-600 hover:bg-slate-100 transition-colors duration-300">
                    <i class="ri-dashboard-3-line text-xl"></i>
                    <span class="font-semibold text-sm">Dashboard</span>
                </a>
                <div x-data="{ expanded: false, height: 0 }" class="flex flex-col">
                    <button @click="expanded = !expanded; height = expanded ? $refs.dropdown.scrollHeight : 0" class="flex items-center justify-between gap-4 p-2 rounded-sm text-slate-600 hover:bg-slate-100 transition-colors duration-300">
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
                            <a href="#" class="flex justify-between text-slate-600 hover:bg-gray-100 p-2 rounded-sm transition-colors duration-300">
                                <div class="flex items-center gap-1">
                                    <i class="ri-group-line text-xl"></i>
                                    <span class="font-semibold text-sm">Add supplier</span>
                                </div>
                                <p class="px-2 text-white bg-blue-950 rounded-sm ml-5">1</p>
                            </a>
                            <a href="#" class="flex justify-between text-slate-600 hover:bg-gray-100 p-2 rounded-sm transition-colors duration-300">
                                <div class="flex items-center gap-1">
                                    <i class="ri-shirt-line text-xl"></i>
                                    <span class="font-semibold text-sm">Add product</span>
                                </div>
                                <p class="px-2 text-white bg-blue-950 rounded-sm ml-5">3</p>
                            </a>
                            <a href="#" class="flex justify-between text-slate-600 hover:bg-gray-100 p-2 rounded-sm transition-colors duration-300">
                                <div class="flex items-center gap-1">
                                    <i class="ri-list-check-3 text-xl"></i>
                                    <span class="font-semibold text-sm">Add product type</span>
                                </div>
                                <p class="px-2 text-white bg-blue-950 rounded-sm ml-5">3</p>
                            </a>
                        </div>
                    </div>
                </div>

                <a href="CusOrder.php" class="flex items-center gap-4 p-2 rounded-sm text-slate-600 hover:bg-slate-100 transition-colors duration-300">
                    <i class="ri-shopping-bag-2-line text-xl relative">
                        <p class="bg-red-500 rounded-full text-sm text-white w-5 h-5 text-center absolute -top-1 -right-2 select-none <?php echo ($orderCount != 0) ? 'block' : 'hidden'; ?>"><?php echo $orderCount ?></p>
                    </i>
                    <span class="font-semibold text-sm">Orders</span>
                </a>
                <a href="UserContact.php" class="flex items-center gap-4 p-2 rounded-sm text-slate-600 hover:bg-slate-100 transition-colors duration-300">
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
        <div class="flex items-center gap-4 p-1 rounded hover:bg-slate-100">
            <i class="ri-logout-circle-line text-xl"></i>
            <a href="AdminSignOut.php">Sign Out</a>
        </div>
        <p class="text-xs text-gray-400">© <span id="year"></span> pulenceHaven.com™. All rights reserved.</p>
    </div>
</nav>

<div id="overlay" class="fixed inset-0 bg-black opacity-50 hidden z-30"></div>

<script>
    document.getElementById('menu-toggle').addEventListener('click', function() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        if (sidebar.classList.contains('-translate-x-full')) {
            sidebar.classList.remove('-translate-x-full');
            sidebar.classList.add('translate-x-0');
            overlay.classList.remove('hidden');
        } else {
            sidebar.classList.add('-translate-x-full');
            sidebar.classList.remove('translate-x-0');
            overlay.classList.add('hidden');
        }
    });

    document.getElementById('overlay').addEventListener('click', function() {
        document.getElementById('sidebar').classList.add('-translate-x-full');
        document.getElementById('sidebar').classList.remove('translate-x-0');
        this.classList.add('hidden');
    });
</script>