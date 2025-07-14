<?php
session_start();
include('../config/db_connection.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

// Toiletries
$productSelect = "SELECT p.*, pt.ProductType FROM producttb p
INNER JOIN producttypetb pt 
ON p.ProductTypeID = pt.ProductTypeID
WHERE pt.ProductType = 'Toiletries'";
$productSelectQuery = $connect->query($productSelect);
$toiletriesProducts = [];

if ($productSelectQuery->num_rows > 0) {
    while ($row = $productSelectQuery->fetch_assoc()) {
        $toiletriesProducts[] = $row;
    }
}

// Spa
$productSelect = "SELECT p.*, pt.ProductType FROM producttb p
INNER JOIN producttypetb pt 
ON p.ProductTypeID = pt.ProductTypeID
WHERE pt.ProductType = 'Spa'";
$productSelectQuery = $connect->query($productSelect);
$spaProducts = [];

if ($productSelectQuery->num_rows > 0) {
    while ($row = $productSelectQuery->fetch_assoc()) {
        $spaProducts[] = $row;
    }
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
    include('../includes/store_navbar.php');
    ?>

    <main class="max-w-[1310px] min-w-[380px] mx-auto px-4 py-5">
        <div class="flex text-sm text-slate-600">
            <a href="../User/home_page.php" class="underline">Home</a>
            <span><i class="ri-arrow-right-s-fill"></i></span>
            <a href="store.php" class="underline">Store</a>
            <span><i class="ri-arrow-right-s-fill"></i></span>
            <a href="toiletrie_spa.php" class="underline">Toiletries and Spa</a>
        </div>

        <section class="mt-3">
            <h1 class="text-center uppercase text-xl sm:text-2xl text-blue-900 font-semibold bg-gray-100 py-5 mb-5">Black Friday | save 25% on everything</h1>
        </section>

        <!-- Toiletries -->
        <section class="<?php if (empty($toiletriesProducts)): ?>hidden<?php endif; ?>">
            <h1 class="uppercase text-xl sm:text-2xl text-blue-900 font-semibold my-5">Toiletries</h1>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 border-t pt-5">
                <?php foreach ($toiletriesProducts as $product): ?>
                    <a href="store_details.php" class="block w-full group">
                        <div class="relative">
                            <div class="h-auto md:h-[350px] lg:h-[300px] select-none">
                                <img src="<?= htmlspecialchars($product['UserImg1']) ?>" class="w-full h-full object-cover rounded-sm" alt="Store Image">
                            </div>
                            <div class="absolute bottom-0 bg-opacity-45 text-white p-3 w-full z-20 group-hover:translate-x-1 group-hover:-translate-y-1 transition-all duration-300">
                                <h1 class="font-semibold mt-3"><?= htmlspecialchars($product['Title']) ?></h1>
                                <div class="flex items-center text-amber-500 group mt-1">
                                    <span class="select-none">Order now</span>
                                    <i class="ri-arrow-right-line text-xl group-hover:translate-x-2 transition-all duration-200"></i>
                                </div>
                            </div>
                            <div class="absolute bottom-0 left-0 right-0 h-44 bg-gradient-to-t from-blue-950/75 lg:from-amber-900/65 via-blue-950/65 lg:via-amber-900/45 to-transparent z-10 group-hover:h-48 transition-all duration-300"></div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Spa -->
        <section class="<?php if (empty($spaProducts)): ?>hidden<?php endif; ?>">
            <h1 class="uppercase text-xl sm:text-2xl text-blue-900 font-semibold my-5">Toiletries</h1>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 border-t pt-5">
                <?php foreach ($spaProducts as $product): ?>
                    <a href="store_details.php" class="block w-full group">
                        <div class="relative">
                            <div class="h-auto md:h-[350px] lg:h-[300px] select-none">
                                <img src="<?= htmlspecialchars($product['UserImg1']) ?>" class="w-full h-full object-cover rounded-sm" alt="Store Image">
                            </div>
                            <div class="absolute bottom-0 bg-opacity-45 text-white p-3 w-full z-20 group-hover:translate-x-1 group-hover:-translate-y-1 transition-all duration-300">
                                <h1 class="font-semibold mt-3"><?= htmlspecialchars($product['Title']) ?></h1>
                                <div class="flex items-center text-amber-500 group mt-1">
                                    <span class="select-none">Order now</span>
                                    <i class="ri-arrow-right-line text-xl group-hover:translate-x-2 transition-all duration-200"></i>
                                </div>
                            </div>
                            <div class="absolute bottom-0 left-0 right-0 h-44 bg-gradient-to-t from-blue-950/75 lg:from-amber-900/65 via-blue-950/65 lg:via-amber-900/45 to-transparent z-10 group-hover:h-48 transition-all duration-300"></div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <section>
            <h1 class="uppercase text-xl sm:text-2xl text-blue-900 font-semibold my-5">Toiletries</h1>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 border-t pt-5">
                <!-- Card 1 -->
                <a href="store_details.php" class="block w-full group">
                    <div class="relative">
                        <div class="h-auto md:h-[350px] lg:h-[300px] select-none">
                            <img src="../UserImages/beautiful-hotel-insights-details.jpg" class="w-full h-full object-cover rounded-sm" alt="Store Image">
                        </div>
                        <div class="absolute bottom-0 bg-opacity-45 text-white p-3 w-full z-20 group-hover:translate-x-1 group-hover:-translate-y-1 transition-all duration-300">
                            <h1 class="font-semibold mt-3">Bath Sheet</h1>
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

                <a href="store_details.php" class="block w-full group">
                    <div class="relative">
                        <div class="h-auto md:h-[350px] lg:h-[300px] select-none">
                            <img src="../UserImages/hilton-bath-mat-HIL-312-NL-WH_xlrg.jpg" class="w-full h-full object-cover rounded-sm" alt="Store Image">
                        </div>
                        <div class="absolute bottom-0 bg-opacity-45 text-white p-3 w-full z-20 group-hover:translate-x-1 group-hover:-translate-y-1 transition-all duration-300">
                            <h1 class="font-semibold mt-3">Bath Mat</h1>
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

                <a href="store_details.php" class="block w-full group">
                    <div class="relative">
                        <div class="h-auto md:h-[350px] lg:h-[300px] select-none">
                            <img src="../UserImages/nov_.jpeg" class="w-full h-full object-cover rounded-sm" alt="Store Image">
                        </div>
                        <div class="absolute bottom-0 bg-opacity-45 text-white p-3 w-full z-20 group-hover:translate-x-1 group-hover:-translate-y-1 transition-all duration-300">
                            <h1 class="font-semibold mt-3">Hand Towel</h1>
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

                <a href="store_details.php" class="block w-full group">
                    <div class="relative">
                        <div class="h-auto md:h-[350px] lg:h-[300px] select-none">
                            <img src="../UserImages/w-hotels-hooded-robe-WHO-400-MIC-SH-WL-GY_lrg.webp" class="w-full h-full object-cover rounded-sm" alt="Store Image">
                        </div>
                        <div class="absolute bottom-0 bg-opacity-45 text-white p-3 w-full z-20 group-hover:translate-x-1 group-hover:-translate-y-1 transition-all duration-300">
                            <h1 class="font-semibold mt-3">Hooded Robe</h1>
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

                <a href="store_details.php" class="block w-full group">
                    <div class="relative">
                        <div class="h-auto md:h-[350px] lg:h-[300px] select-none">
                            <img src="../UserImages/westin-hotel-body-wash-HB-301-WT_lrg.webp" class="w-full h-full object-cover rounded-sm" alt="Store Image">
                        </div>
                        <div class="absolute bottom-0 bg-opacity-45 text-white p-3 w-full z-20 group-hover:translate-x-1 group-hover:-translate-y-1 transition-all duration-300">
                            <h1 class="font-semibold mt-3">Body Wash</h1>
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

                <a href="store_details.php" class="block w-full group">
                    <div class="relative">
                        <div class="h-auto md:h-[350px] lg:h-[300px] select-none">
                            <img src="../UserImages/the-ritz-carlton-spa-fresh-hand-wash-RTZ-300-LS-SF_lrg.jpg" class="w-full h-full object-cover rounded-sm" alt="Store Image">
                        </div>
                        <div class="absolute bottom-0 bg-opacity-45 text-white p-3 w-full z-20 group-hover:translate-x-1 group-hover:-translate-y-1 transition-all duration-300">
                            <h1 class="font-semibold mt-3">Fresh Hand Wash</h1>
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
            </div>
        </section>

        <section>
            <h1 class="uppercase text-xl sm:text-2xl text-blue-900 font-semibold my-5">Spa</h1>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 border-t pt-5">
                <!-- Card 1 -->
                <a href="store_details.php" class="block w-full group">
                    <div class="relative">
                        <div class="h-auto md:h-[350px] lg:h-[300px] select-none">
                            <img src="../UserImages/the-ritz-carlton-spa-fresh-body-scrub-RTZ-306-SF-6_lrg.jpg" class="w-full h-full object-cover rounded-sm" alt="Store Image">
                        </div>
                        <div class="absolute bottom-0 bg-opacity-45 text-white p-3 w-full z-20 group-hover:translate-x-1 group-hover:-translate-y-1 transition-all duration-300">
                            <h1 class="font-semibold mt-3">Body Scrub</h1>
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

                <a href="store_details.php" class="block w-full group">
                    <div class="relative">
                        <div class="h-auto md:h-[350px] lg:h-[300px] select-none">
                            <img src="../UserImages/westin-hotel-shampoo-conditioner-set-HB-307-WT_lrg.webp" class="w-full h-full object-cover rounded-sm" alt="Store Image">
                        </div>
                        <div class="absolute bottom-0 bg-opacity-45 text-white p-3 w-full z-20 group-hover:translate-x-1 group-hover:-translate-y-1 transition-all duration-300">
                            <h1 class="font-semibold mt-3">Shampoo - Conditioner Set</h1>
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

                <a href="store_details.php" class="block w-full group">
                    <div class="relative">
                        <div class="h-auto md:h-[350px] lg:h-[300px] select-none">
                            <img src="../UserImages/61pAmDdgVxL.jpg" class="w-full h-full object-cover rounded-sm" alt="Store Image">
                        </div>
                        <div class="absolute bottom-0 bg-opacity-45 text-white p-3 w-full z-20 group-hover:translate-x-1 group-hover:-translate-y-1 transition-all duration-300">
                            <h1 class="font-semibold mt-3">Essential Oils</h1>
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

                <a href="store_details.php" class="block w-full group">
                    <div class="relative">
                        <div class="h-auto md:h-[350px] lg:h-[300px] select-none">
                            <img src="../UserImages/Quick-Recovery_1_0dbd5318-0b51-484b-a116-ae029b87a40e.webp" class="w-full h-full object-cover rounded-sm" alt="Store Image">
                        </div>
                        <div class="absolute bottom-0 bg-opacity-45 text-white p-3 w-full z-20 group-hover:translate-x-1 group-hover:-translate-y-1 transition-all duration-300">
                            <h1 class="font-semibold mt-3">Mask</h1>
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
            </div>
        </section>
    </main>

    <!-- MoveUp Btn -->
    <?php
    include('../includes/moveup_btn.php');
    include('../includes/footer.php');
    ?>

    <script type="module" src="../JS/store.js"></script>
</body>

</html>