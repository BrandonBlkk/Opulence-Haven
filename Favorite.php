<?php
session_start();
include('./config/dbConnection.php');

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/3.5.0/remixicon.css" integrity="sha512-HXXR0l2yMwHDrDyxJbrMD9eLvPe3z3qL3PPeozNTsiHJEENxx8DH2CxmV05iwG0dwoz5n4gQZQyYLUNt1Wdgfg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="CSS/output.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="CSS/input.css?v=<?php echo time(); ?>">
</head>

<body class="relative">
    <?php
    include('./includes/Navbar.php');
    ?>

    <main class="pb-4 px-4 max-w-[1310px] mx-auto">
        <!-- Info -->
        <section class="flex items-center justify-center <?php echo !empty($_SESSION['UserID']) ? 'hidden' : ''; ?>">
            <div class="flex gap-2 border border-t-0 p-3">
                <i class="ri-heart-line text-2xl cursor-pointer bg-slate-100 w-10 h-10 rounded-full flex items-center justify-center"></i>
                <div>
                    <p class="text-lg">Keep track of stays you like</p>
                    <p class="text-slate-600 text-sm">Log in or create an account to save your favorite stays to your account and create your own lists.</p>
                </div>
            </div>
        </section>

        <h1 class="uppercase text-xl sm:text-2xl text-blue-900 font-semibold my-5">Your Favorites <span class="text-amber-500">(3)</span></h1>
        <!-- No Booking -->
        <!-- <div class="max-w-sm">
            <img src="./UserImages/Screenshot 2024-12-01 002052.png" class="w-full h-full object-cover" alt="Image">
        </div> -->

        <section class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Card 1 -->
            <a href="StoreDetails.php" class="block w-full group">
                <div class="relative">
                    <div class="h-auto md:h-[350px] lg:h-[300px] select-none">
                        <img src="UserImages/white-comfortable-pillow-blanket-decoration-bed-interior-bedroom.jpg" class="w-full h-full object-cover rounded-sm" alt="Hotel Room">
                    </div>
                    <div class="absolute bottom-0 bg-opacity-45 text-white p-3 w-full z-20 group-hover:translate-x-1 group-hover:-translate-y-1 transition-all duration-300">
                        <h1 class="font-semibold mt-3">Black Friday Limited Offer</h1>
                        <p class="mt-2 text-sm">
                            Book on ALL.com to get 3x Reward points for your stay, across Europe and North Africa.
                            Choose from a variety of brands, and find your dream destination for your perfect trip.
                        </p>
                        <div class="flex items-center text-amber-500 group mt-1">
                            <span class="select-none">Book now</span>
                            <i class="ri-arrow-right-line text-xl group-hover:translate-x-2 transition-all duration-200"></i>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 right-0 h-44 bg-gradient-to-t from-blue-950/75 lg:from-amber-900/65 via-blue-950/65 lg:via-amber-900/45 to-transparent z-10 group-hover:h-48 transition-all duration-300"></div>
                </div>
            </a>

            <a href="StoreDetails.php" class="block w-full group">
                <div class="relative">
                    <div class="h-auto md:h-[350px] lg:h-[300px] select-none">
                        <img src="UserImages/white-pillow.jpg" class="w-full h-full object-cover rounded-sm" alt="Hotel Room">
                    </div>
                    <div class="absolute bottom-0 bg-opacity-45 text-white p-3 w-full z-20 group-hover:translate-x-1 group-hover:-translate-y-1 transition-all duration-300">
                        <h1 class="font-semibold mt-3">Black Friday Limited Offer</h1>
                        <p class="mt-2 text-sm">
                            Book on ALL.com to get 3x Reward points for your stay, across Europe and North Africa.
                            Choose from a variety of brands, and find your dream destination for your perfect trip.
                        </p>
                        <div class="flex items-center text-amber-500 group mt-1">
                            <span class="select-none">Book now</span>
                            <i class="ri-arrow-right-line text-xl group-hover:translate-x-2 transition-all duration-200"></i>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 right-0 h-44 bg-gradient-to-t from-blue-950/75 lg:from-amber-900/65 via-blue-950/65 lg:via-amber-900/45 to-transparent z-10 group-hover:h-48 transition-all duration-300"></div>
                </div>
            </a>

            <a href="StoreDetails.php" class="block w-full group">
                <div class="relative">
                    <div class="h-auto md:h-[350px] lg:h-[300px] select-none">
                        <img src="UserImages/novotel-pillow-protector-pair-nov_lrg.jpg" class="w-full h-full object-cover rounded-sm" alt="Hotel Room">
                    </div>
                    <div class="absolute bottom-0 bg-opacity-45 text-white p-3 w-full z-20 group-hover:translate-x-1 group-hover:-translate-y-1 transition-all duration-300">
                        <h1 class="font-semibold mt-3">Black Friday Limited Offer</h1>
                        <p class="mt-2 text-sm">
                            Book on ALL.com to get 3x Reward points for your stay, across Europe and North Africa.
                            Choose from a variety of brands, and find your dream destination for your perfect trip.
                        </p>
                        <div class="flex items-center text-amber-500 group mt-1">
                            <span class="select-none">Book now</span>
                            <i class="ri-arrow-right-line text-xl group-hover:translate-x-2 transition-all duration-200"></i>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 right-0 h-44 bg-gradient-to-t from-blue-950/75 lg:from-amber-900/65 via-blue-950/65 lg:via-amber-900/45 to-transparent z-10 group-hover:h-48 transition-all duration-300"></div>
                </div>
            </a>
        </section>
    </main>

    <?php
    include('./includes/Footer.php');
    ?>

    <script src="//unpkg.com/alpinejs" defer></script>
    <script src="./JS/index.js"></script>
</body>

</html>