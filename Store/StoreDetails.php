<?php
session_start();
include('../config/dbConnection.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

$alertMessage = "";
$addToBagSuccess = false;
$session_userID = (!empty($_SESSION["UserID"]) ? $_SESSION["UserID"] : null);

if (isset($_GET["product_ID"])) {
    $product_id = $_GET["product_ID"];

    // Update the query to fetch all product images
    $query = "SELECT p.*, pt.ProductTypeID, pt.ProductType, pi.ImageUserPath, ps.Size, ps.PriceModifier
              FROM producttb p
              INNER JOIN producttypetb pt ON p.ProductTypeID = pt.ProductTypeID
              LEFT JOIN productimagetb pi ON p.ProductID = pi.ProductID
              LEFT JOIN sizetb ps ON p.ProductID = ps.ProductID
              WHERE p.ProductID = '$product_id'";

    $product = $connect->query($query)->fetch_assoc();

    // Fetch product details
    $product_id = $product['ProductID'];
    $product_type_id = $product['ProductTypeID'];
    $product_type = $product['ProductType'];
    $title = $product['Title'];
    $price = $product['Price'];
    $price_modifier = $product['PriceModifier'];
    $discount_price = $product['DiscountPrice'];
    $description = $product['Description'];
    $product_specification = $product['Specification'];
    $product_information = $product['Information'];
    $product_delivery = $product['DeliveryInfo'];
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

// Check if CustomerID is set in session
if (isset($session_userID) && !empty($_SESSION['UserID'])) {

    // Process form submission if favoriteBtn is set
    if (isset($_POST['addtofavorites'])) {

        $check_query = "SELECT COUNT(*) as count FROM productfavoritetb WHERE UserID = '$session_userID' AND ProductID = '$product_id'";
        $result = $connect->query($check_query);
        $count = $result->fetch_assoc()['count'];

        // If the product is not already in favorites, insert it
        if ($count == 0) {
            $insert_query = "INSERT INTO productfavoritetb (UserID, ProductID) VALUES ('$session_userID', '$product_id')";
            $connect->query($insert_query);

            // Refresh the page
            header("Location: ../Store/StoreDetails.php?product_ID=$product_id");
        }
    }
} else {
    if (isset($_POST['addtofavorites'])) {

        // If user not logged in
        $alertMessage = "Login first to add to favorites";
    }
}

// Remove specified product from favorite
if (isset($_POST['removefromfavorites'])) {

    $delete_query = "DELETE FROM productfavoritetb WHERE ProductID = '$product_id'";
    $connect->query($delete_query);

    // Refresh the page
    header("Location: ../Store/StoreDetails.php?product_ID=$product_id");
}

// // Add product to cart
if (isset($_POST['addtobag'])) {
    $product_size = $_POST['size'];

    // Get stock from database for this product and size
    $stock_query = "SELECT Stock FROM producttb WHERE ProductID = '$product_id'";
    $stock_result = $connect->query($stock_query);
    $stock_data = $stock_result->fetch_assoc();
    $stock = isset($stock_data['Stock']) ? (int)$stock_data['Stock'] : 0;

    if ($stock > 0) {
        // Reduce stock in the database
        $new_stock = $stock - 1;
        $update_stock = "UPDATE producttb SET Stock = '$new_stock' WHERE ProductID = '$product_id'";
        $connect->query($update_stock);

        // Initialize cart in session if it doesn't exist
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = array();
        }

        // Create a unique key for this product+size combination
        $cart_item_key = $product_id . '_' . $product_size;

        // Check if item already exists in cart
        if (isset($_SESSION['cart'][$cart_item_key])) {
            // Increment quantity if item exists
            $_SESSION['cart'][$cart_item_key]['quantity'] += 1;
        } else {
            // Add new item to cart
            $_SESSION['cart'][$cart_item_key] = array(
                'product_id' => $product_id,
                'size_id' => $product_size,
                'quantity' => 1
            );
        }

        $addToBagSuccess = true;
    } else {
        $addToBagError = "Out of stock.";
    }
}

// Product Review
$productReviewQuery = "SELECT COUNT(*) as count FROM productreviewtb WHERE ProductID = '$product_id'";

// Execute the count query
$productReviewResult = $connect->query($productReviewQuery);
$productReviewCount = $productReviewResult->fetch_assoc()['count'];

$productReviewSelect = "SELECT * FROM productreviewtb 
WHERE ProductID = '$product_id'";
$productReviewSelectQuery = $connect->query($productReviewSelect);
$productReviews = [];

if ($productReviewSelectQuery->num_rows > 0) {
    while ($row = $productReviewSelectQuery->fetch_assoc()) {
        $productReviews[] = $row;
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

    <main class="max-w-[1310px] mx-auto px-4 py-5 min-w-[380px]">
        <div class="flex text-sm text-slate-600">
            <a href="../User/HomePage.php" class="underline">Home</a>
            <span><i class="ri-arrow-right-s-fill"></i></span>
            <a href="Store.php" class="underline">Store</a>
            <span><i class="ri-arrow-right-s-fill"></i></span>
            <a href="StoreDetails.php?product_ID=<?= urlencode($product_id) ?>" class=" underline">Store Details</a>
        </div>

        <form id="addToBagForm" action="<?php $_SERVER["PHP_SELF"] ?>" method="post" enctype="multipart/form-data" class="flex flex-col md:flex-row justify-center gap-3 mt-3">

            <input type="hidden" name="product_Id" id="product_ID" value="<?php echo $product_id; ?>">

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
                <div class="relative overflow-hidden group">
                    <div class="w-full md:max-w-[740px] max-h-[430px] cursor-zoom-in relative">
                        <img
                            id="mainImage"
                            class="w-full h-full object-cover transition-transform duration-200 group-hover:scale-125"
                            src="<?= htmlspecialchars($main_product_image) ?>"
                            alt="Main Image">
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

                // Zoom based on mouse
                const mainImage = document.getElementById('mainImage');
                mainImage.addEventListener('mousemove', (e) => {
                    const {
                        left,
                        top,
                        width,
                        height
                    } = mainImage.getBoundingClientRect();
                    const x = ((e.clientX - left) / width) * 100;
                    const y = ((e.clientY - top) / height) * 100;
                    mainImage.style.transformOrigin = `${x}% ${y}%`;
                });
                mainImage.addEventListener('mouseleave', () => {
                    mainImage.style.transformOrigin = 'center center';
                });
            </script>

            <div class="w-full md:max-w-[290px] py-3 sm:py-0">
                <div class="mb-4">
                    <div class="flex justify-between items-center">
                        <p class="text-base sm:text-lg font-bold mb-2" id="priceDisplay">$ <?= $price; ?></p>

                        <?php
                        $check = "SELECT * FROM productfavoritetb 
                        WHERE ProductID = '$product_id'
                        And UserID = '$session_userID'";

                        $check_query = $connect->query($check);
                        $rowCount = $check_query->num_rows;

                        $buttonHtml = $rowCount > 0
                            ? '
                             <button name="removefromfavorites" class="bg-slate-100 p-2 rounded-md hover:bg-slate-200 transition-colors duration-200 relative group">
                                <i class="ri-heart-fill text-xl text-amber-500"></i>
                                <!-- Tooltip -->
                                <span class="absolute top-12 left-1/2 transform -translate-x-1/2 bg-gray-600 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                    Add to Favorites
                                </span>
                            </button>
                            '
                            : '
                             <button name="addtofavorites" class="bg-slate-100 p-2 rounded-md hover:bg-slate-200 transition-colors duration-200 relative group">
                                <i class="ri-heart-line text-xl text-gray-400"></i>
                                <!-- Tooltip -->
                                <span class="absolute top-12 left-1/2 transform -translate-x-1/2 bg-gray-600 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                    Add to Favorites
                                </span>
                            </button>
                            ';
                        echo $buttonHtml;
                        ?>
                    </div>
                    <div class="flex gap-1 items-center">
                        <p class="text-sm text-gray-500">(<?= $stock; ?> <?= ($stock > 1) ? 'available' : 'available' ?>)</p>
                        <?php if ($selling_fast == 1): ?>
                            <div class="inline-block bg-green-500 text-white text-xs font-bold px-2 py-1 rounded-sm select-none">
                                Selling Fast
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php
                    $review_select = "SELECT Rating FROM productreviewtb WHERE ProductID = '$product_id'";
                    $select_query = $connect->query($review_select);

                    // Check if there are any reviews
                    $totalReviews = $select_query->num_rows;
                    if ($totalReviews > 0) {
                        $totalRating = 0;

                        // Sum all ratings
                        while ($review = $select_query->fetch_assoc()) {
                            $totalRating += $review['Rating'];
                        }

                        // Calculate the average rating
                        $averageRating = $totalRating / $totalReviews;
                    } else {
                        $averageRating = 0;
                    }
                    ?>

                    <div class="flex items-center gap-3 mb-4">
                        <div class="select-none space-x-1 cursor-pointer">
                            <?php
                            $fullStars = floor($averageRating);
                            $halfStar = ($averageRating - $fullStars) >= 0.5 ? 1 : 0;
                            $emptyStars = 5 - ($fullStars + $halfStar);

                            // Display full stars
                            for ($i = 0; $i < $fullStars; $i++) {
                                echo '<i class="ri-star-fill text-amber-500"></i>';
                            }

                            // Display half star if needed
                            if ($halfStar) {
                                echo '<i class="ri-star-half-line text-amber-500"></i>';
                            }

                            // Display empty stars
                            for ($i = 0; $i < $emptyStars; $i++) {
                                echo '<i class="ri-star-line text-amber-500"></i>';
                            }
                            ?>
                        </div>
                        <p class="text-gray-500 text-sm">
                            <?php echo number_format($averageRating, 1); ?> out of 5
                            (<?php echo $totalReviews; ?> review<?php echo ($totalReviews > 1) ? 's' : ''; ?>)
                        </p>
                    </div>
                </div>

                <!-- Size Dropdown -->
                <div class="mb-4">
                    <div class="mt-4">
                        <select id="size" name="size" class="block w-full p-2 border border-gray-300 rounded-md text-gray-700 bg-white cursor-pointer focus:border-amber-500 focus:ring-amber-500 outline-none transition-colors duration-75">
                            <option value="" disabled selected>Choose a size</option>

                            <?php
                            $query_sizes = "SELECT SizeID, Size, PriceModifier FROM sizetb WHERE ProductID = '$product_id'";
                            $result_sizes = $connect->query($query_sizes);
                            if ($result_sizes->num_rows > 0) :
                                while ($size = $result_sizes->fetch_assoc()) :
                            ?>
                                    <option value="<?php echo $size['SizeID']; ?>" data-modifier="<?php echo $size['PriceModifier']; ?>">
                                        <?php echo $size['Size']; ?>
                                    </option>
                                <?php endwhile;
                            else : ?>
                                <option value="" disabled>No size available right now</option>
                            <?php endif; ?>
                        </select>

                        <!-- Validation message -->
                        <p id="sizeError" class="text-red-500 text-sm mt-1 hidden">Please select a size.</p>
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
                <h1 id="tab-description" class="tab text-base sm:text-lg text-blue-900 font-semibold cursor-pointer select-none <?= ($description === 'Not provided') ? 'hidden' : ''; ?>" onclick="showTab('description')">Description</h1>
                <h1 id="tab-specification" class="tab text-base sm:text-lg text-blue-900 font-semibold cursor-pointer select-none <?= ($product_specification === 'Not provided') ? 'hidden' : ''; ?>" onclick="showTab('specification')">Specification</h1>
                <h1 id="tab-information" class="tab text-base sm:text-lg text-blue-900 font-semibold cursor-pointer select-none <?= ($product_information === 'Not provided') ? 'hidden' : ''; ?>" onclick="showTab('information')">Information</h1>
                <h1 id="tab-review" class="tab text-base sm:text-lg text-blue-900 font-semibold cursor-pointer select-none" onclick="showTab('review')">Reviews (<?= ($productReviewCount) ?>)</h1>

                <!-- Moving Bar -->
                <div id="active-bar" class="absolute bottom-0 left-0 h-[2px] bg-blue-900 transition-all duration-300"></div>
            </div>

            <!-- Tab Contents -->
            <div class="mt-5">
                <!-- Description -->
                <div id="description" class="tab-content hidden">
                    <p class="text-gray-600"><?php echo nl2br($description); ?></p>
                </div>
                <!-- Specification -->
                <div id="specification" class="tab-content hidden">
                    <p class="text-gray-600"><?php echo nl2br($product_specification); ?></p>
                </div>
                <!-- Information -->
                <div id="information" class="tab-content grid grid-cols-1 sm:grid-cols-2">
                    <div>
                        <h1 id="tab-specification" class="tab text-base sm:text-lg text-blue-900 font-semibold cursor-pointer select-none">Information</h1>
                        <p class="text-gray-600"><?php echo nl2br($product_information); ?></p>
                    </div>
                    <!-- Delivery-->
                    <div class="<?= ($product_delivery === 'Not provided') ? 'hidden' : ''; ?>">
                        <h1 id="tab-specification" class="tab text-base sm:text-lg text-blue-900 font-semibold cursor-pointer select-none">Delivery</h1>
                        <p class=" text-gray-600"><?php echo nl2br($product_delivery); ?></p>
                    </div>
                </div>

                <!-- Review -->
                <div id="review" class="tab-content hidden">
                    <form class="w-24" method="get">
                        <!-- Hidden input to preserve the product_ID -->
                        <input type="hidden" name="product_ID" value="<?= htmlspecialchars($_GET['product_ID'] ?? '') ?>">

                        <!-- Sorting dropdown -->
                        <select id="options" name="sort" class="block w-full py-1 text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none" onchange="this.form.submit()">
                            <option value="oldest" <?= (isset($_GET['sort']) && $_GET['sort'] === 'oldest') ? 'selected' : '' ?>>Oldest</option>
                            <option value="newest" <?= (isset($_GET['sort']) && $_GET['sort'] === 'newest') ? 'selected' : '' ?>>Newest</option>
                        </select>
                    </form>

                    <?php
                    // Determine the sorting order based on the selected option
                    $sortOrder = 'ASC';
                    if (isset($_GET['sort']) && $_GET['sort'] === 'newest') {
                        $sortOrder = 'DESC';
                    }

                    // Fetch the product_ID from the URL
                    $product_id = $_GET['product_ID'] ?? '';

                    // SQL query to fetch reviews for the product with sorting
                    $productReviewSelect = "SELECT productreviewtb.*, usertb.*
                             FROM productreviewtb
                             JOIN usertb 
                             ON productreviewtb.UserID = usertb.UserID 
                             WHERE productreviewtb.ProductID = '$product_id'
                             ORDER BY productreviewtb.AddedDate $sortOrder";

                    // Execute the query
                    $productReviewSelectQuery = $connect->query($productReviewSelect);

                    // Check if reviews exist
                    if ($productReviewSelectQuery->num_rows > 0) {
                        while ($row = $productReviewSelectQuery->fetch_assoc()) {
                            // Fetch each review's data
                            $userid = $row['UserID'];
                            $fullname = $row['UserName'];
                            $reviewdate = $row['AddedDate'];
                            $rating = $row['Rating'];
                            $comment = $row['Comment'];

                            // Logic to handle truncated comments
                            $comment_words = explode(' ', $comment);
                            if (count($comment_words) > 100) {
                                $truncated_comment = implode(' ', array_slice($comment_words, 0, 100)) . '...';
                                $full_comment = $comment;
                            } else {
                                $truncated_comment = $comment;
                                $full_comment = '';
                            }
                    ?>
                            <!-- Output the review and customer details -->
                            <div class="bg-white py-3 flex items-start border-b-2 border-slate-100 space-x-4">
                                <?php
                                // Extract initials from the UserName
                                $nameParts = explode(' ', trim($row['UserName'])); // Split the name by spaces
                                $initials = substr($nameParts[0], 0, 1); // First letter of the first name
                                if (count($nameParts) > 1) {
                                    $initials .= substr(end($nameParts), 0, 1); // First letter of the last name
                                }

                                $bgColor = $row['ProfileBgColor'];
                                ?>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <p class="w-10 h-10 rounded-full bg-[<?= $bgColor ?>] text-white uppercase font-semibold flex items-center justify-center select-none">
                                            <?= $initials ?>
                                        </p>
                                        <div class="flex items-center flex-wrap space-x-2">
                                            <p class="text-sm font-semibold text-gray-800"><?php echo $fullname; ?></p>
                                            <span class="text-xs text-gray-500">
                                                <?php
                                                // Check if the admin ID matches the logged-in admin's ID
                                                if ($session_userID == $userid) {
                                                    echo "<span class='text-sm text-green-500 font-semibold'> (You)</span>";
                                                }
                                                ?>
                                                • Verified Buyer <i class="ri-checkbox-circle-line text-green-500"></i>
                                            </span>
                                            <span class="text-xs text-gray-500">Reviewed on <span><?= htmlspecialchars(date('Y-m-d', strtotime($reviewdate))) ?></span></span>
                                        </div>
                                    </div>
                                    <div class="flex items-center mt-1"><?php echo str_repeat('<i class="ri-star-s-line text-amber-500"></i>', $rating); ?></div>
                                    <div class="flex gap-1 divide-x-2 mt-1">
                                        <p class="text-gray-700 text-xs font-semibold px-1">Brand: <span class="font-normal"><?php echo $brand; ?></span></p>
                                        <p class="text-gray-700 text-xs font-semibold px-1">Pattern: <span class="font-normal"><?php echo $product_type; ?></span></p>
                                    </div>

                                    <!-- Truncated Comment -->
                                    <p class="text-gray-700 mt-2 text-sm leading-relaxed truncated-comment">
                                        <?php echo $truncated_comment; ?>
                                    </p>
                                    <?php if ($full_comment): ?>
                                        <p class="text-indigo-600 text-sm cursor-pointer mt-1 read-more">
                                            <i class="ri-arrow-down-s-line"></i> Read More
                                        </p>
                                    <?php endif; ?>

                                    <!-- Full Comment -->
                                    <p class="text-gray-700 mt-2 text-sm leading-relaxed full-comment hidden">
                                        <?php echo $full_comment; ?>
                                    </p>
                                    <?php if ($full_comment): ?>
                                        <p class="text-indigo-600 text-sm cursor-pointer mt-1 read-less hidden">
                                            <i class="ri-arrow-up-s-line"></i> Read Less
                                        </p>
                                    <?php endif; ?>

                                    <!-- Reaction -->
                                    <div class="mt-1 flex gap-2 text-slate-600 select-none">
                                        <span class="text-xs cursor-pointer">
                                            <i class="ri-thumb-up-line text-base"></i>
                                            Like
                                        </span>
                                        <span class="text-xs cursor-pointer">
                                            <i class="ri-thumb-down-line text-base"></i>
                                            Dislike
                                        </span>
                                    </div>
                                </div>
                            </div>
                    <?php
                        }
                    } else {
                        echo "<p class='text-center text-gray-500 my-20'>No reviews available for this product.</p>";
                    }
                    ?>
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

                // Store the active tab in localStorage
                localStorage.setItem('activeTab', tabId);
            };

            // Set the active tab on page load
            document.addEventListener('DOMContentLoaded', () => {
                // Get the stored tab or use default if none exists
                const savedTab = localStorage.getItem('activeTab');
                const initialTab = savedTab || 'description';

                // Show the tab
                showTab(initialTab);
            });
        </script>

        <!-- Recommended section -->
        <div class="py-10 px-3 text-center">
            <h1 class="text-lg sm:text-xl text-blue-900 font-semibold">Recommended Just For You</h1>
        </div>
        <section class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 px-4 max-w-[1000px] mx-auto">
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
    include('../includes/Alert.php');
    include('../includes/Loader.php');
    include('../includes/Footer.php');
    ?>

    <script type="module" src="../JS/store.js"></script>
</body>

</html>