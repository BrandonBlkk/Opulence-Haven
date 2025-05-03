<?php
session_start();
include('../config/dbConnection.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

// Pillows
$productSelect = "SELECT p.*, 
        pt.ProductType, 
        pi_primary.ImageUserPath AS PrimaryImagePath, 
        pi_secondary.ImageUserPath AS SecondaryImagePath
    FROM producttb p
    INNER JOIN producttypetb pt 
        ON p.ProductTypeID = pt.ProductTypeID
    LEFT JOIN productimagetb pi_primary
        ON p.ProductID = pi_primary.ProductID AND pi_primary.PrimaryImage = 1
    LEFT JOIN productimagetb pi_secondary
        ON p.ProductID = pi_secondary.ProductID AND pi_secondary.SecondaryImage = 1
    WHERE pt.ProductType = 'Pillow'
    GROUP BY p.ProductID";

$productSelectQuery = $connect->query($productSelect);
$pillowProducts = [];

if ($productSelectQuery->num_rows > 0) {
    while ($row = $productSelectQuery->fetch_assoc()) {
        $pillowProducts[] = $row;
    }
}

// Linens
$productSelect = "SELECT p.*, 
pt.ProductType, 
pi_primary.ImageUserPath AS PrimaryImagePath, 
pi_secondary.ImageUserPath AS SecondaryImagePath
FROM producttb p
INNER JOIN producttypetb pt 
ON p.ProductTypeID = pt.ProductTypeID
INNER JOIN productimagetb pi_primary
ON p.ProductID = pi_primary.ProductID AND pi_primary.PrimaryImage = 1
LEFT JOIN productimagetb pi_secondary
ON p.ProductID = pi_secondary.ProductID AND pi_secondary.SecondaryImage = 1
WHERE pt.ProductType = 'Linen'";

$productSelectQuery = $connect->query($productSelect);
$linenProducts = [];

if ($productSelectQuery->num_rows > 0) {
    while ($row = $productSelectQuery->fetch_assoc()) {
        $linenProducts[] = $row;
    }
}

// Duvets
$productSelect = "SELECT p.*, 
pt.ProductType, 
pi_primary.ImageUserPath AS PrimaryImagePath, 
pi_secondary.ImageUserPath AS SecondaryImagePath
FROM producttb p
INNER JOIN producttypetb pt 
ON p.ProductTypeID = pt.ProductTypeID
INNER JOIN productimagetb pi_primary
ON p.ProductID = pi_primary.ProductID AND pi_primary.PrimaryImage = 1
LEFT JOIN productimagetb pi_secondary
ON p.ProductID = pi_secondary.ProductID AND pi_secondary.SecondaryImage = 1
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
    <link rel="stylesheet" href="../CSS/output.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../CSS/input.css?v=<?php echo time(); ?>">
</head>

<body>
    <?php
    include('../includes/StoreNavbar.php');
    ?>

    <main class="max-w-[1310px] min-w-[380px] mx-auto px-4 py-5">
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
                    <a href="StoreDetails.php?product_ID=<?php echo htmlspecialchars($product['ProductID']) ?>" class="block w-full <?= $product['SecondaryImagePath'] ? 'group' : '' ?>">
                        <div class="relative">
                            <div class="relative w-full h-auto md:h-[350px] lg:h-[300px] select-none mb-4">
                                <!-- Primary Image -->
                                <img
                                    class="w-full h-full object-cover transition-opacity duration-300 opacity-100 group-hover:opacity-0"
                                    src="<?= htmlspecialchars($product['PrimaryImagePath']) ?>"
                                    alt="Primary Image">
                                <!-- Secondary Image -->
                                <img
                                    class="w-full h-full object-cover absolute top-0 left-0 transition-opacity duration-300 opacity-0 group-hover:opacity-100 <?= $product['SecondaryImagePath'] ? '' : 'hidden' ?>"
                                    src="<?= htmlspecialchars($product['SecondaryImagePath']) ?>"
                                    alt="Secondary Image">
                            </div>
                            <!-- Selling Fast Badge -->
                            <?php if (isset($product['SellingFast']) && $product['SellingFast'] == 1): ?>
                                <div class="absolute top-1 left-1 bg-green-500 text-white text-xs font-bold px-2 py-1 rounded-sm">
                                    Selling Fast
                                </div>
                            <?php endif; ?>
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
                    <a href="StoreDetails.php" class="block w-full group <?= $product['SecondaryImagePath'] ? '' : 'pointer-events-none' ?>">
                        <div class="relative">
                            <div class="relative w-full h-auto md:h-[350px] lg:h-[300px] select-none mb-4">
                                <!-- Primary Image -->
                                <img
                                    class="w-full h-full object-cover transition-opacity duration-300 opacity-100 group-hover:opacity-0"
                                    src="<?= htmlspecialchars($product['PrimaryImagePath']) ?>"
                                    alt="Primary Image">
                                <!-- Secondary Image -->
                                <img
                                    class="w-full h-full object-cover absolute top-0 left-0 transition-opacity duration-300 opacity-0 group-hover:opacity-100 <?= $product['SecondaryImagePath'] ? '' : 'hidden' ?>"
                                    src="<?= htmlspecialchars($product['SecondaryImagePath']) ?>"
                                    alt="Secondary Image">
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
                    <a href="StoreDetails.php" class="block w-full group <?= $product['SecondaryImagePath'] ? '' : 'pointer-events-none' ?>">
                        <div class="relative">
                            <div class="relative w-full h-auto md:h-[350px] lg:h-[300px] select-none mb-4">
                                <!-- Primary Image -->
                                <img
                                    class="w-full h-full object-cover transition-opacity duration-300 opacity-100 group-hover:opacity-0"
                                    src="<?= htmlspecialchars($product['PrimaryImagePath']) ?>"
                                    alt="Primary Image">
                                <!-- Secondary Image -->
                                <img
                                    class="w-full h-full object-cover absolute top-0 left-0 transition-opacity duration-300 opacity-0 group-hover:opacity-100 <?= $product['SecondaryImagePath'] ? '' : 'hidden' ?>"
                                    src="<?= htmlspecialchars($product['SecondaryImagePath']) ?>"
                                    alt="Secondary Image">
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

    <script type="module" src="../JS/store.js"></script>
</body>

</html>