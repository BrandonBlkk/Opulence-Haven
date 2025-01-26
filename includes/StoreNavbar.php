<section class="bg-gray-100 px-3 min-w-[350px]">
    <div class="flex items-center justify-end max-w-[1050px] mx-auto gap-5 select-none">
        <i id="search-icon" class="ri-search-line text-xl cursor-pointer"></i>
        <a href="../User/UserSignIn.php" class="font-semibold text-slate-500 hover:bg-gray-200 p-2 rounded-sm transition-colors duration-200"><?php echo !empty($_SESSION['UserName']) ? $_SESSION['UserName'] : 'My account'; ?></a>
        <div class="relative group">
            <a href="../Store/AddToCart.php" class="bg-blue-900 text-white py-1 px-3 cursor-pointer flex items-center gap-2">
                <i class="ri-shopping-cart-2-line text-xl"></i>
                <span>0 item</span>
            </a>

            <!-- Dropdown Cart -->
            <div class="absolute top-full right-0 bg-gray-100 p-3 z-40 w-96 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-opacity duration-300">
                <p class="font-semibold text-gray-600 text-center">You have no items in your cart.</p>
            </div>
        </div>
    </div>
</section>

<!-- Search Bar -->
<div id="search-bar" class="fixed -top-full w-full bg-white py-5 px-4 shadow-lg transition-all duration-300 z-50">
    <h1 class="text-xl font-semibold pb-4">Find Your Favorites</h1>
    <div class="flex items-center bg-gray-100 rounded-lg p-2">
        <!-- Search Icon -->
        <i class="ri-search-line text-xl text-gray-500 mr-3"></i>

        <!-- Search Input -->
        <input
            type="text"
            placeholder="Search for products..."
            class="w-full bg-transparent border-none focus:outline-none text-gray-800 text-sm placeholder-gray-500" />

        <!-- Clear Button -->
        <button id="searchCloseBtn" class="ml-2 text-gray-500 hover:text-gray-700 transition">
            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</div>


<!-- Overlay -->
<div id="storeDarkOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden"></div>

<div class="sticky top-0 w-full bg-white border-b z-30 min-w-[350px]">
    <?php
    include('MoveRightLoader.php');
    include('MaintenanceAlert.php');
    ?>
    <nav class="flex items-center justify-between max-w-[1050px] mx-auto p-3">
        <div class="flex items-end gap-1 select-none">
            <a href="Store.php">
                <img src="../UserImages/Screenshot_2024-11-29_201534-removebg-preview.png" class="w-28 select-none" alt="Logo">
            </a>
            <p class="text-amber-500 text-sm font-semibold">STORE</p>
        </div>
        <div class="flex items-center gap-5 select-none relative">
            <div class="items-center hidden sm:flex">
                <a href="RoomEssentials.php" class="flex items-center gap-1 font-semibold hover:bg-gray-100 p-2 rounded-sm transition-colors duration-200">
                    Room Essentials
                </a>
                <a href="Toiletries&Spa.php" class="flex items-center gap-1 font-semibold hover:bg-gray-100 p-2 rounded-sm transition-colors duration-200">
                    Toiletries and Spa
                </a>
            </div>
            <i id="storeMenubar" class="ri-menu-4-line text-3xl cursor-pointer transition-transform duration-300 block sm:hidden"></i>
        </div>

        <!-- Mobile Sidebar -->
        <aside id="aside" class="fixed top-0 -right-full flex flex-col bg-white w-full sm:w-[330px] h-full p-4 z-50 transition-all duration-500 ease-in-out">
            <div class="flex justify-end pb-3">
                <i id="closeBtn" class="ri-close-line text-2xl cursor-pointer rounded transition-colors duration-300"></i>
            </div>
            <div class="flex flex-col justify-between gap-3 h-full">
                <div class="select-none">
                    <a href="../Store/RoomEssentials.php" class="flex justify-between text-slate-600 hover:bg-gray-100 p-2 rounded-sm transition-colors duration-300">
                        <p class="font-semibold text-2xl sm:text-sm">Room Essentials</p>
                    </a>
                    <a href="../Store/Toiletries&Spa.php" class="flex justify-between text-slate-600 hover:bg-gray-100 p-2 rounded-sm transition-colors duration-300">
                        <p class="font-semibold text-2xl sm:text-sm">Toiletries and Spa</p>
                    </a>
                </div>
        </aside>

        <!-- Overlay -->
        <div id="darkOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden"></div>
    </nav>
</div>