<?php
session_start();
include('../config/dbConnection.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

if (isset($_GET["product_ID"])) {
    $product_id = $_GET["product_ID"];

    // Update the query to fetch all product images
    $query = "SELECT p.*, pt.ProductTypeID, pi.ImageUserPath, ps.Size, ps.PriceModifier
              FROM producttb p
              INNER JOIN producttypetb pt ON p.ProductTypeID = pt.ProductTypeID
              LEFT JOIN productimagetb pi ON p.ProductID = pi.ProductID
              LEFT JOIN sizetb ps ON p.ProductID = ps.ProductID
              WHERE p.ProductID = '$product_id'";

    $product = $connect->query($query)->fetch_assoc();

    // Fetch product details
    $product_id = $product['ProductID'];
    $product_type_id = $product['ProductTypeID'];
    $title = $product['Title'];
    $price = $product['Price'];
    $price_modifier = $product['PriceModifier'];
    $discount_price = $product['DiscountPrice'];
    $description = $product['Description'];
    $product_specification = $product['Specification'];
    $product_information = $product['Information'];
    $brand = $product['Brand'];
    $selling_fast = $product['SellingFast'];
    $stock = $product['Stock'];

    // Fetch product image paths
    $main_product_image = $product['ImageUserPath'];
    $side_images = [];

    $query_images = "SELECT ImageUserPath FROM productimagetb WHERE ProductID = '$product_id'";
    $result_images = $connect->query($query_images);

    // Store side images
    while ($image = $result_images->fetch_assoc()) {
        $side_images[] = $image['ImageUserPath'];
    }

    // Fetch product sizes
    $product_size = $product['Size'];
    $sizes = [];

    $query_sizes = "SELECT Size, PriceModifier  FROM sizetb WHERE ProductID = '$product_id'";
    $result_sizes = $connect->query($query_sizes);

    // Store sizes
    while ($size = $result_sizes->fetch_assoc()) {
        $sizes[] = $size['Size'];
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

    <main class="max-w-[1310px] mx-auto px-4 py-5 min-w-[350px]">
        <div class="flex text-sm text-slate-600">
            <a href="HomePage.php" class="underline">Home</a>
            <span><i class="ri-arrow-right-s-fill"></i></span>
            <a href="Store.php" class="underline">Store</a>
            <span><i class="ri-arrow-right-s-fill"></i></span>
            <a href="StoreDetails.php" class="underline">Store Details</a>
        </div>

        <form action="<?php $_SERVER["PHP_SELF"] ?>" method="post" enctype="multipart/form-data" class="flex flex-col md:flex-row justify-center gap-3 mt-3">

            <input type="hidden" name="product_Id" value="<?php echo $product_id; ?>">
            <!-- <input type="hidden" name="product_size" value="<?php echo $product_size; ?>"> -->

            <!-- Product Showcase -->
            <div class="flex flex-col-reverse sm:flex-row gap-3 select-none">
                <!-- Side Images -->
                <div class="select-none cursor-pointer space-x-0 sm:space-y-2 flex gap-2 sm:block">
                    <?php foreach ($side_images as $side_image): ?>
                        <div class="product-detail-img w-20 h-16" onclick="changeImage('<?= htmlspecialchars($side_image) ?>')">
                            <img
                                class="w-full h-full rounded object-cover hover:border-2 hover:border-amber-300"
                                src="<?= htmlspecialchars($side_image) ?>"
                                alt="Image"
                                onmouseover="changeMainImage(this)"
                                onmouseout="resetMainImage()">
                        </div>
                    <?php endforeach; ?>
                </div>
                <!-- Main Image -->
                <div class="relative overflow-hidden">
                    <div class="w-full md:max-w-[740px] max-h-[430px]">
                        <img id="mainImage" class="w-full h-full object-cover" src="<?= htmlspecialchars($main_product_image) ?>" alt="Main Image">
                    </div>
                </div>
            </div>

            <script>
                let originalImage = document.getElementById('mainImage').src;

                function changeMainImage(element) {
                    const mainImage = document.getElementById('mainImage');
                    mainImage.src = element.src;
                }

                function resetMainImage() {
                    const mainImage = document.getElementById('mainImage');
                    mainImage.src = originalImage;
                }

                // Update the original image when clicked
                function changeImage(newImage) {
                    const mainImage = document.getElementById('mainImage');
                    originalImage = newImage;
                    mainImage.src = newImage;
                }
            </script>

            <div class="w-full md:max-w-[290px] py-3 px-0 sm:py-0 sm:px-3">
                <p class="text-lg font-bold mb-2" id="priceDisplay">$ <?= $price; ?></p>
                <div class="mb-4 flex justify-between items-center">
                    <p class="text-sm text-gray-500">(<?= $stock; ?> available)</p>
                </div>

                <!-- Size Dropdown -->
                <div class="mb-4">
                    <div class="mt-4">
                        <select id="size" name="size" class="block w-full p-2 border border-gray-300 rounded-md text-gray-700 bg-white cursor-pointer focus:border-amber-500 focus:ring-amber-500 outline-none transition-colors duration-200">
                            <option value="" disabled selected>Choose a size</option>
                            <?php if (!empty($sizes)) : ?>
                                <?php
                                $query_sizes = "SELECT SizeID, Size, PriceModifier FROM sizetb WHERE ProductID = '$product_id'";
                                $result_sizes = $connect->query($query_sizes);
                                while ($size = $result_sizes->fetch_assoc()) :
                                ?>
                                    <option value="<?php echo $size['SizeID']; ?>" data-modifier="<?php echo $size['PriceModifier']; ?>">
                                        <?php echo $size['Size']; ?>
                                    </option>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <option value="" disabled>No size available right now</option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                <script>
                    // Dynamically update price
                    document.getElementById("size").addEventListener("change", function() {
                        // Get the base price from PHP
                        const basePrice = <?= $price; ?>;
                        const selectedOption = this.options[this.selectedIndex];
                        const priceModifier = parseFloat(selectedOption.getAttribute("data-modifier"));

                        // Calculate the new price
                        const newPrice = basePrice + (priceModifier || 0);

                        // Update the displayed price
                        document.getElementById("priceDisplay").textContent = `$ ${newPrice.toFixed(2)}`;
                    });
                </script>

                <div class="flex items-center justify-between mb-4 <?= !empty($sizes) ? '' : 'cursor-not-allowed' ?>">
                    <input
                        type="submit"
                        value="ADD TO CART"
                        name="addtobag"
                        class="w-full bg-amber-500 hover:bg-amber-600 text-white font-semibold text-center p-2 select-none transition-colors duration-300 <?= empty($sizes) ? 'pointer-events-none' : '' ?>"
                        <?= empty($sizes) ? 'disabled' : '' ?>>
                </div>

                <div class="flex gap-4 border p-4 mb-4">
                    <i class="ri-truck-line text-2xl"></i>
                    <div>
                        <p>Free delivery on qualifying orders.</p>
                        <a href="../Delivery.php" class="text-xs underline text-gray-500 hover:text-gray-400 transition-colors duration-200">View our Delivery & Returns Policy</a>
                    </div>
                </div>
            </div>
        </form>

        <section class="max-w-[950px] mx-auto">
            <div class="relative flex gap-10 border-b border-slate-200 pt-8 pb-2">
                <!-- Tabs -->
                <h1 id="tab-description" class="tab text-lg text-blue-900 font-semibold cursor-pointer select-none <?= ($description === 'Not provided') ? 'hidden' : ''; ?>" onclick="showTab('description')">Description</h1>
                <h1 id="tab-specification" class="tab text-lg text-blue-900 font-semibold cursor-pointer select-none <?= ($product_specification === 'Not provided') ? 'hidden' : ''; ?>" onclick="showTab('specification')">Specification</h1>
                <h1 id="tab-information" class="tab text-lg text-blue-900 font-semibold cursor-pointer select-none <?= ($product_information === 'Not provided') ? 'hidden' : ''; ?>" onclick="showTab('information')">Information</h1>
                <h1 id="tab-review" class="tab text-lg text-blue-900 font-semibold cursor-pointer select-none" onclick="showTab('review')">Reviews (0)</h1>

                <!-- Moving Bar -->
                <div id="active-bar" class="absolute bottom-0 left-0 h-[2px] bg-blue-900 transition-all duration-300"></div>
            </div>

            <!-- Tab Contents -->
            <div class="mt-5">
                <div id="description" class="tab-content hidden">
                    <p class="text-gray-600"><?php echo nl2br($description); ?></p>
                </div>
                <div id="specification" class="tab-content hidden">
                    <p class="text-gray-600"><?php echo nl2br($product_specification); ?></p>
                </div>
                <div id="information" class="tab-content hidden">
                    <p class="text-gray-600"><?php echo nl2br($product_information); ?></p>
                </div>
                <div id="review" class="tab-content hidden">
                    <p class="text-gray-500 text-center my-20">No user review yet</p>
                </div>
            </div>
        </section>

        <script>
            // Function to show the selected tab and move the active bar
            const showTab = (tabId) => {
                // Hide all tab contents
                const tabs = document.querySelectorAll('.tab-content');
                tabs.forEach(tab => tab.classList.add('hidden'));

                // Show the selected tab content
                const activeTab = document.getElementById(tabId);
                if (activeTab) {
                    activeTab.classList.remove('hidden');
                }

                // Update the active bar position
                const clickedTab = document.getElementById(`tab-${tabId}`);
                const activeBar = document.getElementById('active-bar');
                if (clickedTab && activeBar) {
                    const tabRect = clickedTab.getBoundingClientRect();
                    const parentRect = clickedTab.parentElement.getBoundingClientRect();
                    activeBar.style.width = `${tabRect.width}px`;
                    activeBar.style.left = `${tabRect.left - parentRect.left}px`;
                }
            };

            // Set a default tab to show and position the bar on page load
            document.addEventListener('DOMContentLoaded', () => {
                showTab('description');
            });
        </script>

        <div class="py-10 px-3 text-center">
            <h1 class="text-xl text-blue-900 font-semibold">Recommended Just For You</h1>
        </div>
        <section class="grid grid-cols-1 md:grid-cols-3 gap-2 px-4 max-w-[1000px] mx-auto">
            <!-- Card 1 -->
            <a href="#" class="block w-full sm:max-w-[300px] mx-auto group">
                <div class="h-auto sm:h-[180px] select-none">
                    <img src="../UserImages/hotel-room-5858069_1280.jpg" class="w-full h-full object-cover rounded-sm" alt="Hotel Room">
                </div>
                <div>
                    <h1 class="text-slate-700 font-semibold mt-3">Black Friday Limited Offer</h1>
                    <p class="text-slate-600 mt-2">
                        Book on ALL.com to get 3x Reward points for your stay, across Europe and North Africa.
                        Choose from a variety of brands, and find your dream destination for your perfect trip.
                    </p>
                    <div class="flex items-center text-amber-500 group mt-1">
                        <span class="group-hover:text-amber-600 transition-all duration-200">Book now</span>
                        <i class="ri-arrow-right-line text-xl group-hover:text-amber-600 group-hover:translate-x-2 transition-all duration-200"></i>
                    </div>
                </div>
            </a>

            <!-- Card 2 -->
            <a href="#" class="block w-full sm:max-w-[300px] mx-auto group">
                <div class="h-auto sm:h-[180px] select-none">
                    <img src="../UserImages/FORMAT-16-9E---1920-X-1080-PX (1)_3by2.webp" class="w-full h-full object-cover rounded-sm" alt="Hotel Room">
                </div>
                <div>
                    <h1 class="text-slate-700 font-semibold mt-3">Life in balance: Breakfast at Opulence</h1>
                    <p class="text-slate-600 mt-2">
                        When there's an opportunity to indulge while enjoying a variety of choices,
                        ensuring the energy needed for the day ahead. Perfect for business or family trips.
                    </p>
                    <div class="flex items-center text-amber-500 group mt-1">
                        <span class="group-hover:text-amber-600 transition-all duration-200">Book now</span>
                        <i class="ri-arrow-right-line text-xl group-hover:text-amber-600 group-hover:translate-x-2 transition-all duration-200"></i>
                    </div>
                </div>
            </a>

            <!-- Card 3 -->
            <a href="#" class="block w-full sm:max-w-[300px] mx-auto group">
                <div class="h-auto sm:h-[180px] select-none">
                    <img src="../UserImages/hotel-room-5858069_1280.jpg" class="w-full h-full object-cover rounded-sm" alt="Hotel Room">
                </div>
                <div>
                    <h1 class="text-slate-700 font-semibold mt-3">Opulence Store - Black Friday</h1>
                    <p class="text-slate-600 mt-2">
                        25% off on Opulence bedding collection. End the year softly with Opulence bedding for cozy,
                        hotel-like nights. Pillows, duvets, mattresses, and much more!
                    </p>
                    <div class="flex items-center text-amber-500 group mt-1">
                        <span class="group-hover:text-amber-600 transition-all duration-200">Shop now</span>
                        <i class="ri-arrow-right-line text-xl group-hover:text-amber-600 group-hover:translate-x-2 transition-all duration-200"></i>
                    </div>
                </div>
            </a>
        </section>
    </main>

    <!-- MoveUp Btn -->
    <?php
    include('../includes/Footer.php');
    ?>

    <script src="../JS/store.js"></script>
</body>

</html>