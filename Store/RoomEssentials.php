<?php
session_start();
include('../config/dbConnection.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

// Pillows
$productSelect = "SELECT p.*, pt.ProductType FROM producttb p
INNER JOIN producttypetb pt 
ON p.ProductTypeID = pt.ProductTypeID
WHERE pt.ProductType = 'Pillow'";
$productSelectQuery = $connect->query($productSelect);
$pillowProducts = [];

if ($productSelectQuery->num_rows > 0) {
    while ($row = $productSelectQuery->fetch_assoc()) {
        $pillowProducts[] = $row;
    }
}

// Linens
$productSelect = "SELECT p.*, pt.ProductType FROM producttb p
INNER JOIN producttypetb pt 
ON p.ProductTypeID = pt.ProductTypeID
WHERE pt.ProductType = 'Linen'";

$productSelectQuery = $connect->query($productSelect);
$linenProducts = [];

if ($productSelectQuery->num_rows > 0) {
    while ($row = $productSelectQuery->fetch_assoc()) {
        $linenProducts[] = $row;
    }
}

// Duvets
$productSelect = "SELECT p.*, pt.ProductType FROM producttb p
INNER JOIN producttypetb pt 
ON p.ProductTypeID = pt.ProductTypeID
WHERE pt.ProductType = 'Duvet'";
$productSelectQuery = $connect->query($productSelect);
$duvetProducts = [];

if ($productSelectQuery->num_rows > 0) {
    while ($row = $productSelectQuery->fetch_assoc()) {
        $duvetProducts[] = $row;
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/3.5.0/remixicon.css" integrity="sha512-HXXR0l2yMwHDrDyxJbrMD9eLvPe3z3qL3PPeozNTsiHJEENxx8DH2CxmV05iwG0dwoz5n4gQZQyYLUNt1Wdgfg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../CSS/output.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../CSS/input.css?v=<?php echo time(); ?>">
</head>

<body>
    <?php
    include('../includes/StoreNavbar.php');
    ?>

    <main class="max-w-[1310px] mx-auto px-4 py-5">
        <div class="flex text-sm text-slate-600">
            <a href="../User/HomePage.php" class="underline">Home</a>
            <span><i class="ri-arrow-right-s-fill"></i></span>
            <a href="Store.php" class="underline">Store</a>
            <span><i class="ri-arrow-right-s-fill"></i></span>
            <a href="RoomEssentials.php" class="underline">Room Essentials</a>
        </div>

        <section class="mt-3">
            <h1 class="text-center uppercase text-xl sm:text-2xl text-blue-900 font-semibold bg-gray-100 py-5 mb-5">Black Friday | save 25% on everything</h1>
        </section>

        <section>
            <h1 class="uppercase text-xl sm:text-2xl text-blue-900 font-semibold my-5">Opulence bed</h1>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 border-t pt-5">
                <!-- Card 1 -->
                <a href="StoreDetails.php" class="block w-full group">
                    <div class="relative">
                        <div class="h-auto md:h-[350px] lg:h-[300px] select-none">
                            <img src="../UserImages/pillow-bed-decoration-interior-bedroom.jpg" class="w-full h-full object-cover rounded-sm" alt="Store Image">
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
                            <img src="../UserImages/mattress-2029193_1280.jpg" class="w-full h-full object-cover rounded-sm" alt="Store Image">
                        </div>
                        <div class="absolute bottom-0 bg-opacity-45 text-white p-3 w-full z-20 group-hover:translate-x-1 group-hover:-translate-y-1 transition-all duration-300">
                            <h1 class="font-semibold mt-3">Opulence Bed: Mattress & Base</h1>
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
                            <img src="../UserImages/novotel-mattress-protector-114-04-16-01-lrg.jpg" class="w-full h-full object-cover rounded-sm" alt="Store Image">
                        </div>
                        <div class="absolute bottom-0 bg-opacity-45 text-white p-3 w-full z-20 group-hover:translate-x-1 group-hover:-translate-y-1 transition-all duration-300">
                            <h1 class="font-semibold mt-3">Mattress Protector</h1>
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
                            <img src="../UserImages/mattress-2489612_1280.jpg" class="w-full h-full object-cover rounded-sm" alt="Store Image">
                        </div>
                        <div class="absolute bottom-0 bg-opacity-45 text-white p-3 w-full z-20 group-hover:translate-x-1 group-hover:-translate-y-1 transition-all duration-300">
                            <h1 class="font-semibold mt-3">Mattress Protector</h1>
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

        <!-- Pillows -->
        <section class="<?php if (empty($pillowProducts)): ?>hidden<?php endif; ?>">
            <h1 class="uppercase text-xl sm:text-2xl text-blue-900 font-semibold my-5">Pillows</h1>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 border-t pt-5">
                <?php foreach ($pillowProducts as $product): ?>
                    <a href="StoreDetails.php" class="block w-full group">
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

        <!-- Linens -->
        <section class="<?php if (empty($linenProducts)): ?>hidden<?php endif; ?>">
            <h1 class="uppercase text-xl sm:text-2xl text-blue-900 font-semibold my-5">Linens</h1>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 border-t pt-5">
                <?php foreach ($linenProducts as $product): ?>
                    <a href="StoreDetails.php" class="block w-full group">
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

        <!-- Duvets -->
        <section class="<?php if (empty($duvetsProducts)): ?>hidden<?php endif; ?>">
            <h1 class="uppercase text-xl sm:text-2xl text-blue-900 font-semibold my-5">Duvets</h1>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 border-t pt-5">
                <?php foreach ($duvetsProducts as $product): ?>
                    <a href="StoreDetails.php" class="block w-full group">
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
    </main>

    <!-- MoveUp Btn -->
    <?php
    include('../includes/MoveUpBtn.php');
    include('../includes/Footer.php');
    ?>

    <script src="../JS/store.js"></script>
</body>

</html>