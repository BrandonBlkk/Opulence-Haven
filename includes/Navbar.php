<section id="sale-section" class="SVG2 p-2 text-center bg-blue-950 text-white">
    <p>Reserve your perfect room today!</p>
</section>

<div class="sticky top-0 w-full bg-white border-b z-50">
    <?php
    include('../includes/MoveRightLoader.php');
    ?>
    <nav class="flex items-center justify-between max-w-[1050px] mx-auto p-3">
        <a href="../User/HomePage.php">
            <img src="../UserImages/Screenshot_2024-11-29_201534-removebg-preview.png" class="w-28 select-none" alt="Logo">
        </a>

        <div class="flex items-center gap-5 select-none">
            <a href="../User/Favorite.php" class="flex items-center gap-1 hover:bg-gray-100 p-2 rounded-sm transition-colors duration-200">
                <i class="ri-heart-line text-2xl cursor-pointer"></i>
                <p class="font-semibold">Favorite</p>
            </a>
            <i id="menubar" class="ri-menu-4-line text-3xl cursor-pointer transition-transform duration-300"></i>
        </div>
        <?php
        include('Sidebar.php');
        include('MaintenanceAlert.php');
        ?>
    </nav>
</div>