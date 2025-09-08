<?php
session_start();
ob_start();
require_once('../config/db_connection.php');
include_once('../includes/timeago_func.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

$alertMessage = "";
$response = ['success' => false, 'message' => '', 'outofstock' => false, 'stock' => 0, 'login_required' => false];
$session_userID = (!empty($_SESSION["UserID"]) ? $_SESSION["UserID"] : null);

if (isset($_GET["product_ID"])) {
    $product_id = $_GET["product_ID"];

    // Update the query to fetch all product images
    $query = "SELECT p.*, pt.ProductTypeID, pt.ProductType, pi.ImageUserPath, ps.Size, ps.PriceModifier
              FROM producttb p
              INNER JOIN producttypetb pt ON p.ProductTypeID = pt.ProductTypeID
              LEFT JOIN productimagetb pi ON p.ProductID = pi.ProductID
              LEFT JOIN sizetb ps ON p.ProductID = ps.ProductID
              WHERE p.ProductID = ?";
    $query = $connect->prepare($query);
    $query->bind_param("s", $product_id);
    $query->execute();
    $result = $query->get_result();
    $product = $result->fetch_assoc();

    // Fetch product details
    $product_id = $product['ProductID'];
    $product_type_id = $product['ProductTypeID'];
    $product_type = $product['ProductType'];
    $title = $product['Title'];
    $price = $product['Price'];
    $markup = $product['MarkupPercentage'];
    $price_modifier = $product['PriceModifier'];
    $discount_price = $product['DiscountPrice'];
    $description = $product['Description'];
    $product_specification = $product['Specification'];
    $product_information = $product['Information'];
    $product_delivery = $product['DeliveryInfo'];
    $brand = $product['Brand'];
    $selling_fast = $product['SellingFast'];
    $stock = $product['SaleQuantity'];

    // Final price
    $final_price = $price * (1 + $markup / 100);

    // Fetch product image paths
    $main_product_image = $product['ImageUserPath'];
    $side_images = [];

    $query_images = "SELECT ImageUserPath FROM productimagetb WHERE ProductID = ?";
    $query_images = $connect->prepare($query_images);
    $query_images->bind_param("s", $product_id);
    $query_images->execute();
    $result_images = $query_images->get_result();

    // Store side images
    while ($image = $result_images->fetch_assoc()) {
        $side_images[] = $image['ImageUserPath'];
    }

    // Fetch product sizes
    $product_size = $product['Size'];
    $sizes = [];

    $query_sizes = "SELECT Size, PriceModifier  FROM sizetb WHERE ProductID = ?";
    $query_sizes = $connect->prepare($query_sizes);
    $query_sizes->bind_param("s", $product_id);
    $query_sizes->execute();
    $result_sizes = $query_sizes->get_result();

    // Store sizes
    while ($size = $result_sizes->fetch_assoc()) {
        $sizes[] = $size['Size'];
    }

    $query_sizes->close();
    $query_images->close();
    $query->close();
}

// Check if CustomerID is set in session
if (isset($session_userID) && !empty($_SESSION['UserID'])) {

    // Process form submission if favoriteBtn is set
    if (isset($_POST['addtofavorites'])) {

        $check_query = "SELECT COUNT(*) as count FROM productfavoritetb WHERE UserID = ? AND ProductID = ?";
        $check_query = $connect->prepare($check_query);
        $check_query->bind_param("ss", $session_userID, $product_id);
        $check_query->execute();
        $result = $check_query->get_result();
        $count = $result->fetch_assoc()['count'];
        $check_query->close();

        // If the product is not already in favorites, insert it
        if ($count == 0) {
            $insert_query = "INSERT INTO productfavoritetb (UserID, ProductID) VALUES (?, ?)";
            $insert_query = $connect->prepare($insert_query);
            $insert_query->bind_param("ss", $session_userID, $product_id);
            $insert_query->execute();
            $insert_query->close();

            // Refresh the page
            header("Location: ../Store/store_details.php?product_ID=$product_id");
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

    $delete_query = "DELETE FROM productfavoritetb WHERE ProductID = ?";
    $delete_query = $connect->prepare($delete_query);
    $delete_query->bind_param("s", $product_id);
    $delete_query->execute();
    $delete_query->close();

    // Refresh the page
    header("Location: ../Store/store_details.php?product_ID=$product_id");
}

// Add product to cart
if (isset($_POST['addtobag'])) {
    $product_id = isset($_POST['product_Id']) ? $_POST['product_Id'] : '';
    $product_size = isset($_POST['size']) ? $_POST['size'] : '';
    $response = [];

    // Get stock and product price data
    $stock_query = $connect->prepare("SELECT SaleQuantity, Price, DiscountPrice, MarkupPercentage FROM producttb WHERE ProductID = ?");
    $stock_query->bind_param("s", $product_id);
    $stock_query->execute();
    $stock_result = $stock_query->get_result();
    $stock_data = $stock_result->fetch_assoc();
    $stock = isset($stock_data['SaleQuantity']) ? (int)$stock_data['SaleQuantity'] : 0;

    // Check if user is logged in
    if ($session_userID) {
        if ($stock > 0) {
            // Base price considering discount
            $base_price = (!empty($stock_data['DiscountPrice']) && $stock_data['DiscountPrice'] > 0)
                ? $stock_data['DiscountPrice']
                : $stock_data['Price'];

            // Add product markup
            $markup_percentage = isset($stock_data['MarkupPercentage']) ? (float)$stock_data['MarkupPercentage'] : 0;
            $price_with_markup = $base_price * (1 + $markup_percentage / 100);

            // Add size modifier if applicable
            $size_modifier = 0;
            if (!empty($product_size)) {
                $size_query = $connect->prepare("SELECT PriceModifier FROM sizetb WHERE SizeID = ? AND ProductID = ?");
                $size_query->bind_param("is", $product_size, $product_id);
                $size_query->execute();
                $size_result = $size_query->get_result();
                if ($size_result->num_rows > 0) {
                    $size_modifier = $size_result->fetch_assoc()['PriceModifier'] ?? 0;
                }
            }

            $final_price = $price_with_markup + $size_modifier;

            // Reduce stock in the database
            $new_stock = $stock - 1;
            $update_stock = $connect->prepare("UPDATE producttb SET SaleQuantity = ? WHERE ProductID = ?");
            $update_stock->bind_param("is", $new_stock, $product_id);
            $update_stock->execute();

            // Get or create pending order
            $order_query = $connect->prepare("SELECT OrderID FROM ordertb WHERE UserID = ? AND Status = 'Pending' LIMIT 1");
            $order_query->bind_param("s", $session_userID);
            $order_query->execute();
            $order_result = $order_query->get_result();

            if ($order_result->num_rows > 0) {
                $order_id = $order_result->fetch_assoc()['OrderID'];
            } else {
                $order_id = uniqid('ORD_');
                $insert_order = $connect->prepare("
                    INSERT INTO ordertb (OrderID, UserID, Status, OrderDate) 
                    VALUES (?, ?, 'Pending', NOW())
                ");
                $insert_order->bind_param("ss", $order_id, $session_userID);
                $insert_order->execute();
            }

            // Check if item already exists in order details
            $check_item = $connect->prepare("
                SELECT OrderUnitQuantity 
                FROM orderdetailtb 
                WHERE OrderID = ? AND ProductID = ? AND SizeID = ?
            ");
            $check_item->bind_param("sss", $order_id, $product_id, $product_size);
            $check_item->execute();
            $item_result = $check_item->get_result();

            if ($item_result->num_rows > 0) {
                // Update existing item quantity
                $update_item = $connect->prepare("
                    UPDATE orderdetailtb 
                    SET OrderUnitQuantity = OrderUnitQuantity + 1, 
                        OrderUnitPrice = ?
                    WHERE OrderID = ? AND ProductID = ? AND SizeID = ?
                ");
                $update_item->bind_param("dsss", $final_price, $order_id, $product_id, $product_size);
                $update_item->execute();
            } else {
                // Add new item to order details
                $insert_item = $connect->prepare("
                    INSERT INTO orderdetailtb 
                    (OrderID, ProductID, SizeID, OrderUnitQuantity, OrderUnitPrice) 
                    VALUES (?, ?, ?, 1, ?)
                ");
                $insert_item->bind_param("sssd", $order_id, $product_id, $product_size, $final_price);
                $insert_item->execute();
            }

            $response['success'] = true;
            $response['stock'] = $new_stock;
        } else {
            $response['outofstock'] = true;
        }
    } else {
        $response['login_required'] = true;
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Product Review
$productReviewQuery = "SELECT COUNT(*) as count FROM productreviewtb WHERE ProductID = ?";
$productReviewQuery = $connect->prepare($productReviewQuery);
$productReviewQuery->bind_param("s", $product_id);

// Execute the count query
$productReviewResult = $productReviewQuery->execute();
$productReviewCount = $productReviewQuery->get_result()->fetch_assoc()['count'];
$productReviewQuery->close();

$productReviewSelect = "SELECT * FROM productreviewtb 
WHERE ProductID = ?";
$productReviewSelect = $connect->prepare($productReviewSelect);
$productReviewSelect->bind_param("s", $product_id);
$productReviewSelectQuery = $productReviewSelect->execute();
$productReviewSelectQuery = $productReviewSelect->get_result();
$productReviews = [];

if ($productReviewSelectQuery->num_rows > 0) {
    while ($row = $productReviewSelectQuery->fetch_assoc()) {
        $productReviews[] = $row;
    }
}

//Review Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_edit'])) {

    $response = ['success' => false, 'message' => 'Failed to update review'];

    // Validate inputs
    $review_id = isset($_POST['review_id']) ? (int) $_POST['review_id'] : 0;
    $updated_comment = isset($_POST['updated_comment']) ? trim($_POST['updated_comment']) : '';

    if ($review_id > 0 && $updated_comment !== '') {
        // Use prepared statement (no need for real_escape_string)
        $stmt = $connect->prepare("UPDATE productreviewtb SET Comment = ? WHERE ReviewID = ?");
        if ($stmt) {
            $stmt->bind_param("si", $updated_comment, $review_id);
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Review updated successfully';
            } else {
                $response['message'] = 'Database execute failed';
            }
            $stmt->close();
        } else {
            $response['message'] = 'Database prepare failed';
        }
    } else {
        $response['message'] = 'Invalid data';
    }

    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response);
    exit;
}

// Review delete
if (isset($_POST['delete'])) {
    $review_id = isset($_POST['review_id']) ? (int) $_POST['review_id'] : 0;

    header('Content-Type: application/json; charset=utf-8');

    if ($review_id > 0) {
        $delete_query = $connect->prepare("DELETE FROM productreviewtb WHERE ReviewID = ?");
        if ($delete_query) {
            $delete_query->bind_param("i", $review_id);
            if ($delete_query->execute()) {
                $response = [
                    'success' => true,
                    'message' => 'Review deleted successfully'
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Database delete failed'
                ];
            }
            $delete_query->close();
        } else {
            $response = [
                'success' => false,
                'message' => 'Database prepare failed'
            ];
        }
    } else {
        $response = [
            'success' => false,
            'message' => 'Missing required fields'
        ];
    }

    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    echo json_encode($response);
    exit();
}

// Handle reaction submission
if (isset($_POST['like']) || isset($_POST['dislike'])) {
    // Check if user is logged in
    if (!isset($_SESSION['UserID'])) {
        header("Location: ../User/user_signin.php");
        exit();
    }

    // Validate and sanitize input
    $reviewID = filter_input(INPUT_POST, 'review_id', FILTER_VALIDATE_INT);
    $product_id = $connect->real_escape_string($_POST['product_id']);
    $userID = $_SESSION['UserID'];
    $newReactionType = isset($_POST['like']) ? 'like' : 'dislike';

    // Check if user already reacted to this review
    $checkStmt = $connect->prepare("SELECT ReactionType FROM productreviewrttb WHERE ReviewID = ? AND UserID = ?");
    $checkStmt->bind_param("is", $reviewID, $userID);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows > 0) {
        $existingReaction = $result->fetch_assoc()['ReactionType'];

        if ($existingReaction == $newReactionType) {
            // User clicked same reaction - remove it
            $deleteStmt = $connect->prepare("DELETE FROM productreviewrttb WHERE ReviewID = ? AND UserID = ?");
            $deleteStmt->bind_param("is", $reviewID, $userID);
            $deleteStmt->execute();
            $deleteStmt->close();
        } else {
            // User changed reaction - update it
            $updateStmt = $connect->prepare("UPDATE productreviewrttb SET ReactionType = ? WHERE ReviewID = ? AND UserID = ?");
            $updateStmt->bind_param("sis", $newReactionType, $reviewID, $userID);
            $updateStmt->execute();
            $updateStmt->close();
        }
    } else {
        // User hasn't reacted - insert new reaction
        $insertStmt = $connect->prepare("INSERT INTO productreviewrttb (ReviewID, UserID, ReactionType) VALUES (?, ?, ?)");
        $insertStmt->bind_param("iss", $reviewID, $userID, $newReactionType);
        $insertStmt->execute();
        $insertStmt->close();
    }

    $checkStmt->close();

    // Refresh the page to show updated reactions
    $redirect_url = "store_details.php?product_ID=$product_id";
    header("Location: $redirect_url");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opulence Haven</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="../CSS/output.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../CSS/input.css?v=<?php echo time(); ?>">
</head>

<body>
    <?php
    include('../includes/store_navbar.php');
    ?>

    <!-- Login Modal -->
    <?php
    include('../includes/login_request.php');
    ?>

    <main class="max-w-[1310px] mx-auto px-4 py-5 min-w-[380px]">
        <div class="flex text-sm text-slate-600">
            <a href="../User/home_page.php" class="underline">Home</a>
            <span><i class="ri-arrow-right-s-fill"></i></span>
            <a href="store.php" class="underline">Store</a>
            <span><i class="ri-arrow-right-s-fill"></i></span>
            <a href="store_details.php?product_ID=<?= urlencode($product_id) ?>" class=" underline">Store Details</a>
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
                                alt="Image">
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
                        <p class="text-base sm:text-lg font-bold mb-2" id="priceDisplay">$ <?= $final_price; ?></p>

                        <?php
                        $check = "SELECT * FROM productfavoritetb 
                        WHERE ProductID = '$product_id'
                        AND UserID = '$session_userID'";

                        $check_query = $connect->query($check);
                        $rowCount = $check_query->num_rows;

                        $buttonHtml = $rowCount > 0
                            ? '
                    <button type="button" class="favorite-btn bg-slate-100 p-2 rounded-md hover:bg-slate-200 transition-colors duration-200 relative group" 
                        data-product-id="' . $product_id . '" data-action="remove">
                        <i class="ri-heart-fill text-xl text-amber-500"></i>
                        <span class="absolute top-12 left-1/2 transform -translate-x-1/2 bg-gray-600 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                            Remove from Favorites
                        </span>
                    </button>
                    '
                            : '
                    <button type="button" class="favorite-btn bg-slate-100 p-2 rounded-md hover:bg-slate-200 transition-colors duration-200 relative group" 
                        data-product-id="' . $product_id . '" data-action="add">
                        <i class="ri-heart-line text-xl text-gray-400"></i>
                        <span class="absolute top-12 left-1/2 transform -translate-x-1/2 bg-gray-600 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                            Add to Favorites
                        </span>
                    </button>
                    ';

                        echo $buttonHtml;
                        ?>
                    </div>
                    <div class="flex gap-1 items-center">
                        <p class="text-sm text-gray-500">(<span id="stockDisplay"><?= $stock; ?></span> <?= ($stock > 1) ? 'availables' : 'available' ?>)</p>
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
                        // $price * (1 + $markup / 100);

                        const basePrice = <?= $price * (1 + $markup / 100); ?>;
                        const selectedOption = this.options[this.selectedIndex];
                        const priceModifier = parseFloat(selectedOption.getAttribute("data-modifier"));

                        // Calculate the new price
                        const newPrice = basePrice + (priceModifier || 0);

                        // Update the displayed price
                        document.getElementById("priceDisplay").textContent = `$ ${newPrice.toFixed(2)}`;
                    });
                </script>

                <input type="hidden" name="addtobag" value="1">

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
                <div id="description" class="tab-content">
                    <p class="text-gray-600"><?php echo nl2br($description); ?></p>
                </div>
                <!-- Specification -->
                <div id="specification" class="tab-content hidden">
                    <p class="text-gray-600"><?php echo nl2br($product_specification); ?></p>
                </div>
                <!-- Information -->
                <div id="information" class="tab-content grid grid-cols-1 sm:grid-cols-2 hidden">
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

                <div id="review" class="tab-content hidden">
                    <form id="reviewFilterForm" class="w-24" method="get">
                        <input type="hidden" name="product_ID" value="<?= htmlspecialchars($_GET['product_ID'] ?? '') ?>">
                        <select id="sortReviews" name="sort" class="block w-full py-1 text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none">
                            <option value="oldest" <?= (isset($_GET['sort']) && $_GET['sort'] === 'oldest') ? 'selected' : '' ?>>Oldest</option>
                            <option value="newest" <?= (isset($_GET['sort']) && $_GET['sort'] === 'newest') ? 'selected' : '' ?>>Newest</option>
                        </select>
                    </form>

                    <div id="review-results-container">
                        <?php include('../Store/review_results.php'); ?>
                    </div>

                    <div id="review-pagination-container" class="flex justify-between items-center mt-3">
                        <?php include('../Store/review_pagination.php'); ?>
                    </div>
                </div>

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

                    document.addEventListener('DOMContentLoaded', function() {
                        // Show description first by default
                        showTab('description');

                        // Get all review date containers
                        const dateContainers = document.querySelectorAll('.review-date-container');

                        dateContainers.forEach(container => {
                            const timeAgo = container.querySelector('.time-ago');
                            const fullDate = container.querySelector('.full-date');

                            // Toggle between timeAgo and full date on click
                            container.addEventListener('click', function() {
                                timeAgo.classList.add('hidden');
                                fullDate.classList.remove('hidden');

                                setTimeout(() => {
                                    timeAgo.classList.remove('hidden');
                                    fullDate.classList.add('hidden');
                                }, 2000);
                            });
                        });
                    });
                </script>
            </div>
        </section>

        <!-- Recommended section -->
        <div class="py-10 px-3 text-center">
            <h1 class="text-lg sm:text-xl text-blue-900 font-semibold">Recommended Just For You</h1>
        </div>
        <section class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 px-4 max-w-[1000px] mx-auto">
            <?php
            // Get recommended products
            $query = "SELECT * FROM producttb WHERE ProductID != ? AND isActive = 1 AND SaleQuantity > 0 ORDER BY RAND() LIMIT 3";
            $query = $connect->prepare($query);
            $query->bind_param("s", $product_id);
            $query->execute();
            $result = $query->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
            ?>
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
            <?php
                }
            } else {
                echo '<p class="col-span-3 text-gray-500 text-center my-36">No recommended products found.</p>';
            }
            ?>
        </section>
    </main>

    <!-- MoveUp Btn -->
    <?php
    include('../includes/alert.php');
    include('../includes/loader.php');
    include('../includes/footer.php');
    ?>

    <script type="module" src="../JS/store.js"></script>
</body>

</html>