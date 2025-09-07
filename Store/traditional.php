<?php
session_start();
require_once('../config/db_connection.php');
include_once('../includes/get_product_func.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch each category
$traditionalProducts = getProductsByType($connect, 'Traditional Products');
$spaProducts = getProductsByType($connect, 'Spa');
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

        <?php if ($traditionalProducts == null): ?>
            <section class="mt-3">
                <h2 class="text-center text-gray-500 py-36 mb-5">No products found</h2>
            </section>
        <?php else: ?>
            <!-- Toiletries -->
            <section class="<?php if (empty($traditionalProducts)): ?>hidden<?php endif; ?>">
                <h1 class="uppercase text-xl sm:text-2xl text-blue-900 font-semibold my-5">Traditional</h1>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 border-t pt-5">
                    <?php foreach ($traditionalProducts as $product): ?>
                        <a href="store_details.php?product_ID=<?php echo htmlspecialchars($product['ProductID']) ?>" class="block w-full <?= $product['SecondaryImagePath'] ? 'group' : '' ?>">
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
        <?php endif; ?>
    </main>

    <!-- MoveUp Btn -->
    <?php
    include('../includes/moveup_btn.php');
    include('../includes/footer.php');
    ?>

    <script type="module" src="../JS/store.js"></script>
</body>

</html>