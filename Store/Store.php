<?php
session_start();
include('../config/dbConnection.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opulence Haven</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../CSS/output.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../CSS/input.css?v=<?php echo time(); ?>">
</head>

<body>
    <?php
    include('../includes/StoreNavbar.php');
    ?>

    <main class="max-w-[1310px] min-w-[380px] mx-auto px-4 py-5">
        <div class="flex text-sm text-slate-600">
            <a href="../User/home_page.php" class="underline">Home</a>
            <span><i class="ri-arrow-right-s-fill"></i></span>
            <a href="Store.php" class="underline">Store</a>
        </div>

        <section class="mt-3">
            <h1 class="text-center uppercase text-xl sm:text-2xl text-blue-900 font-semibold bg-gray-100 py-5 mb-5">Black Friday | save 25% on everything</h1>
            <div class="relative">
                <div class="select-none h-[400px] sm:h-[540px]">
                    <img src="../UserImages/bed-945881_1280.jpg" class="w-full h-full object-cover" alt="Image">
                </div>
                <div class="absolute bottom-0 lg:bottom-7 left-1/2 transform -translate-x-1/2 w-full lg:w-[900px] px-0 sm:px-14 py-8 rounded-none lg:rounded-full flex flex-col sm:flex-row gap-3 items-center justify-between bg-opacity-75 bg-white">
                    <div class="relative text-center z-10">
                        <h1 class="text-lg sm:text-xl text-blue-900 font-bold">Explore Opulence Bedding Comfort</h1>
                        <p class="text-blue-900 font-semibold mb-3">A hotel-quality bed at home</p>
                        <p class="text-slate-600 text-sm">Treat yourself with Opulence sleeping experience in the comfort of your own bedroom.</p>
                    </div>
                    <a href="RoomEssentials.php" class="relative z-10 bg-amber-500 hover:bg-amber-600 text-white font-semibold text-center py-2 px-8 rounded-full transition-colors duration-300">Discover</a>
                </div>
            </div>
        </section>

        <section class="space-y-3">
            <div class="flex flex-col gap-2 sm:flex-row items-center justify-between">
                <div class="w-full sm:max-w-[770px] h-auto sm:h-[370px] select-none">
                    <img src="../UserImages/NOV-HP-02B.jpg" class="w-full h-full object-cover clip-custom2" alt="Image">
                </div>
                <div class="text-center flex flex-col justify-center items-center">
                    <h1 class="uppercase text-lg sm:text-xl text-blue-900 font-bold">Opulence hotel bed package</h1>
                    <p class="text-slate-600 mb-5">Experience hotel comfort at home with Novotel bed and bedding essentials.Discover</p>
                    <a href="#" class="relative z-10 bg-amber-500 hover:bg-amber-600 text-white font-semibold text-center py-2 px-8 rounded-full select-none transition-colors duration-300">Discover</a>
                </div>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row-reverse items-center justify-between">
                <div class="w-full sm:max-w-[770px] h-auto sm:h-[370px] select-none">
                    <img src="../UserImages/Hotel-Pillows-Richard-Haworth.png" class="w-full h-full object-cover clip-custom3" alt="Image">
                </div>
                <div class="text-center flex flex-col justify-center items-center">
                    <h1 class="uppercase text-lg sm:text-xl text-blue-900 font-bold">Opulence hotel pillow</h1>
                    <p class="text-slate-600 mb-5">Experience hotel comfort at home with Novotel bed and bedding essentials.Discover</p>
                    <a href="#" class="relative z-10 bg-amber-500 hover:bg-amber-600 text-white font-semibold text-center py-2 px-8 rounded-full select-none transition-colors duration-300">Discover</a>
                </div>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row items-center justify-between">
                <div class="w-full sm:max-w-[770px] h-auto sm:h-[370px] select-none">
                    <img src="../UserImages/NOV-HP-02.jpg" class="w-full h-full object-cover clip-custom2" alt="Image">
                </div>
                <div class="text-center flex flex-col justify-center items-center">
                    <h1 class="uppercase text-lg sm:text-xl text-blue-900 font-bold">Opulence hotel mattress</h1>
                    <p class="text-slate-600 mb-5">Experience hotel comfort at home with Novotel bed and bedding essentials.Discover</p>
                    <a href="#" class="relative z-10 bg-amber-500 hover:bg-amber-600 text-white font-semibold text-center py-2 px-8 rounded-full select-none transition-colors duration-300">Discover</a>
                </div>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row-reverse items-center justify-between">
                <div class="w-full sm:max-w-[770px] h-auto sm:h-[370px] select-none">
                    <img src="../UserImages/NOV-HP-03.jpg" class="w-full h-full object-cover clip-custom3" alt="Image">
                </div>
                <div class="text-center flex flex-col justify-center items-center">
                    <h1 class="uppercase text-lg sm:text-xl text-blue-900 font-bold">Opulence hotel linen</h1>
                    <p class="text-slate-600 mb-5">Experience hotel comfort at home with Novotel bed and bedding essentials.Discover</p>
                    <a href="#" class="relative z-10 bg-amber-500 hover:bg-amber-600 text-white font-semibold text-center py-2 px-8 rounded-full select-none transition-colors duration-300">Discover</a>
                </div>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row items-center justify-between">
                <div class="w-full sm:max-w-[770px] h-auto sm:h-[370px] select-none">
                    <img src="../UserImages/NOV-HP-07.jpg" class="w-full h-full object-cover" alt="Image">
                </div>
                <div class="text-center flex flex-col justify-center items-center">
                    <h1 class="uppercase text-lg sm:text-xl text-blue-900 font-bold">Opulence hotel duvet</h1>
                    <p class="text-slate-600 mb-5">Experience hotel comfort at home with Novotel bed and bedding essentials.Discover</p>
                    <a href="#" class="relative z-10 bg-amber-500 hover:bg-amber-600 text-white font-semibold text-center py-2 px-8 rounded-full select-none transition-colors duration-300">Discover</a>
                </div>
            </div>
        </section>
    </main>

    <!-- MoveUp Btn -->
    <?php
    include('../includes/MoveUpBtn.php');
    include('../includes/Footer.php');
    ?>

    <script type="module" src="../JS/store.js"></script>
</body>

</html>