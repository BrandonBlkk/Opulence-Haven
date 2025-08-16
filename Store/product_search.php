<?php
session_start();
require_once('../config/db_connection.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

// Initialize search query
$searchQuery = '';
$showAllProducts = false;

if (isset($_GET['search'])) {
    $searchQuery = trim($_GET['search']);
    // Only fetch products if the search query is not empty
    if (!empty($searchQuery)) {
        // Fetch all products with optional search filter
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
            WHERE p.Title LIKE '%$searchQuery%' OR pt.ProductType LIKE '%$searchQuery%' OR p.Brand LIKE '%$searchQuery%'
            GROUP BY p.ProductID";

        $productSelectQuery = $connect->query($productSelect);
        $products = [];

        if ($productSelectQuery->num_rows > 0) {
            while ($row = $productSelectQuery->fetch_assoc()) {
                $products[] = $row;
            }
        }
    } else {
        // If search query is empty, set a flag to show a message
        $showAllProducts = false;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - Opulence Haven</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../CSS/output.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../CSS/input.css?v=<?php echo time(); ?>">
</head>

<body>
    <?php
    include('../includes/store_navbar.php');
    ?>

    <main class="max-w-[1310px] min-w-[380px] mx-auto px-4 py-5">
        <!-- Breadcrumb -->
        <div class="flex text-sm text-slate-600">
            <a href="../User/home_page.php" class="underline">Home</a>
            <span><i class="ri-arrow-right-s-fill"></i></span>
            <a href="store.php" class="underline">Store</a>
            <span><i class="ri-arrow-right-s-fill"></i></span>
            <a href="product_search.php?search=<?= urlencode($searchQuery) ?>" class="underline">Product Search</a>
        </div>

        <!-- Search Results -->
        <section class="mt-5">
            <h1 class="text-xl sm:text-2xl text-blue-900 font-semibold mb-5">
                <?php if (!empty($searchQuery)): ?>
                    Search Results for "<?= htmlspecialchars($searchQuery) ?>"
                <?php else: ?>
                    Please enter a search term
                <?php endif; ?>
            </h1>
            <?php if (empty($searchQuery)): ?>
                <p class="text-center text-gray-500 my-36">Enter a search term to find products.</p>
            <?php elseif (empty($products)): ?>
                <p class="text-center text-gray-500 my-36">No products found.</p>
            <?php else: ?>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 border-t pt-5">
                    <?php foreach ($products as $product): ?>
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
            <?php endif; ?>
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