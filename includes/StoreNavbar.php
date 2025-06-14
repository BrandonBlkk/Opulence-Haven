<?php
include('../User/CleanupReservations.php');
?>
<section class="bg-gray-100 px-3 min-w-[380px]">
    <div class="flex items-center justify-end max-w-[1050px] mx-auto gap-5 select-none">
        <!-- Search Icon -->
        <i id="search-icon" class="ri-search-line text-xl cursor-pointer"></i>

        <!-- Account and Favorites -->
        <div class="flex items-center">
            <!-- My Account -->
            <a href="../User/UserSignIn.php" class="font-semibold text-slate-500 hover:bg-gray-200 p-2 rounded-sm transition-colors duration-200">
                <?php echo !empty($_SESSION['UserName']) ? $_SESSION['UserName'] : 'My account'; ?>
            </a>
            <!-- Favorites -->
            <a href="Favorite.php" class="flex items-center gap-2 font-semibold text-slate-500 hover:bg-gray-200 p-2 rounded-sm transition-colors duration-200">
                <i class="ri-heart-line text-xl"></i>
                <span>Favorites</span>
            </a>
        </div>

        <?php
        if (isset($_POST['remove_from_cart']) && isset($_POST['remove_key'])) {
            $key = $_POST['remove_key'];

            if (isset($_SESSION['cart'][$key])) {
                $product_id = $_SESSION['cart'][$key]['product_id'];
                $size_id = $_SESSION['cart'][$key]['size_id'];
                $quantity = $_SESSION['cart'][$key]['quantity'];

                // Restore stock in database
                $stock_query = "SELECT Stock FROM producttb WHERE ProductID = '$product_id'";
                $stock_result = $connect->query($stock_query);
                $stock_row = $stock_result->fetch_assoc();
                $current_stock = isset($stock_row['Stock']) ? (int)$stock_row['Stock'] : 0;

                $new_stock = $current_stock + $quantity;
                $update_stock = "UPDATE producttb SET Stock = '$new_stock' WHERE ProductID = '$product_id'";
                $connect->query($update_stock);

                // Remove item from cart
                unset($_SESSION['cart'][$key]);
                $_SESSION['cart'] = array_values($_SESSION['cart']);
            }

            header("Location: " . $_SERVER['REQUEST_URI']);
            exit();
        }

        if (isset($_POST['update_quantity']) && isset($_POST['update_key'])) {
            $key = $_POST['update_key'];
            $action = $_POST['update_quantity'];

            if (isset($_SESSION['cart'][$key])) {
                $product_id = $_SESSION['cart'][$key]['product_id'];
                $size_id = $_SESSION['cart'][$key]['size_id'];

                // Fetch current stock from database
                $stock_query = "SELECT Stock FROM producttb WHERE ProductID = '$product_id'";
                $stock_result = $connect->query($stock_query);
                $stock_row = $stock_result->fetch_assoc();
                $current_stock = isset($stock_row['Stock']) ? (int)$stock_row['Stock'] : 0;

                if ($action === 'increase') {
                    if ($current_stock > 0) {
                        // Increase quantity in cart and reduce stock in database
                        $_SESSION['cart'][$key]['quantity']++;
                        $new_stock = $current_stock - 1;
                        $update_stock = "UPDATE producttb SET Stock = '$new_stock' WHERE ProductID = '$product_id'";
                        $connect->query($update_stock);
                    }
                } elseif ($action === 'decrease') {
                    // Decrease quantity in cart
                    $_SESSION['cart'][$key]['quantity']--;
                    // Restore stock in database
                    $new_stock = $current_stock + 1;
                    $update_stock = "UPDATE producttb SET Stock = '$new_stock' WHERE ProductID = '$product_id'";
                    $connect->query($update_stock);

                    // Remove item if quantity is zero or less
                    if ($_SESSION['cart'][$key]['quantity'] <= 0) {
                        unset($_SESSION['cart'][$key]);
                    }
                }

                // Reindex cart array
                $_SESSION['cart'] = array_values($_SESSION['cart']);
            }

            header("Location: " . $_SERVER['REQUEST_URI']);
            exit();
        }
        ?>

        <!-- Shopping Cart -->
        <div class="relative group">
            <a href="../Store/AddToCart.php" class="bg-blue-900 text-white py-1 px-3 cursor-pointer flex items-center gap-2">
                <i class="ri-shopping-cart-2-line text-xl"></i>
                <span>
                    <?php
                    $cartCount = 0;
                    if (!empty($_SESSION['cart'])) {
                        foreach ($_SESSION['cart'] as $item) {
                            $cartCount += $item['quantity'];
                            $productID = $item['product_id'];
                        }
                    }
                    echo $cartCount . ' item' . ($cartCount != 1 ? 's' : '');
                    ?>
                </span>
            </a>

            <!-- Dropdown Cart -->
            <div class="absolute top-full right-0 bg-gray-100 p-3 z-40 w-96 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-opacity duration-300">
                <?php if (!empty($_SESSION['cart'])): ?>
                    <div class="space-y-3">
                        <h3 class="font-semibold text-lg text-center mb-2">Your Cart Items</h3>

                        <?php
                        $total = 0;
                        foreach ($_SESSION['cart'] as $key => $item):
                            $product_query = "
                                SELECT p.*, pi.ImageUserPath, s.Size, s.PriceModifier 
                                FROM producttb p
                                LEFT JOIN productimagetb pi ON p.ProductID = pi.ProductID AND pi.PrimaryImage = 1
                                LEFT JOIN sizetb s ON s.SizeID = '" . $item['size_id'] . "' AND s.ProductID = p.ProductID
                                WHERE p.ProductID = '" . $item['product_id'] . "'
                                LIMIT 1
                            ";
                            $product_result = $connect->query($product_query);
                            $product = $product_result->fetch_assoc();

                            $base_price = (!empty($product['DiscountPrice']) && $product['DiscountPrice'] > 0) ? $product['DiscountPrice'] : $product['Price'];
                            $modifier = isset($product['PriceModifier']) ? (float)$product['PriceModifier'] : 0;
                            $price = $base_price + $modifier;

                            $subtotal = $price * $item['quantity'];
                            $total += $subtotal;
                        ?>
                            <div class="flex items-start gap-3 p-2 bg-white rounded">
                                <img src="<?= !empty($product['ImageUserPath']) ? '../UserImages/' . $product['ImageUserPath'] : '../UserImages/default.jpg' ?>"
                                    alt="<?= !empty($product['ImageAlt']) ? $product['ImageAlt'] : $product['Title'] ?>"
                                    class="w-16 h-16 object-cover rounded border">
                                <div class="flex-1">
                                    <h4 class="font-medium text-sm"><?= $product['Title'] ?></h4>
                                    <p class="text-xs text-gray-500">Size: <?= $product['Size'] ?? 'N/A' ?></p>
                                    <div class="flex items-center justify-between mt-1">
                                        <div class="flex items-center gap-1">
                                            <form method="post" action="">
                                                <input type="hidden" name="update_key" value="<?= $key ?>">
                                                <button type="submit" name="update_quantity" value="decrease"
                                                    class="px-2 text-sm font-bold text-gray-600 hover:text-red-600">−</button>
                                            </form>
                                            <span class="text-sm"><?= $item['quantity'] ?></span>
                                            <form method="post" action="">
                                                <input type="hidden" name="update_key" value="<?= $key ?>">
                                                <button type="submit" name="update_quantity" value="increase"
                                                    class="px-2 text-sm font-bold text-gray-600 hover:text-green-600">+</button>
                                            </form>
                                        </div>
                                        <span class="font-medium text-sm">$<?= number_format($subtotal, 2) ?></span>
                                    </div>
                                </div>
                                <form action="" method="post" class="ml-2">
                                    <input type="hidden" name="remove_key" value="<?= $key ?>">
                                    <button type="submit" name="remove_from_cart" class="text-red-500 hover:text-red-700 text-sm">
                                        <i class="ri-close-line"></i>
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>

                        <div class="bg-white p-3 rounded border-t">
                            <div class="flex justify-between font-semibold">
                                <span>Total:</span>
                                <span>$<?= number_format($total, 2) ?></span>
                            </div>
                            <a href="../Store/AddToCart.php"
                                class="block mt-3 bg-blue-900 text-white text-center py-2 text-sm rounded hover:bg-blue-800 transition-colors">
                                Checkout
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="font-semibold text-gray-600 text-center py-4">You have no items in your cart.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Search Bar -->
<form method="get" action="../Store/ProductSearch.php" id="search-bar" class="fixed -top-full w-full bg-white py-5 px-4 shadow-lg transition-all duration-300 z-50">
    <h1 class="text-xl font-semibold pb-4">Find Your Favorites</h1>
    <div class="flex items-center bg-gray-100 rounded-lg p-2">
        <!-- Search Icon -->
        <i class="ri-search-line text-xl text-gray-500 mr-3"></i>

        <!-- Search Input -->
        <input
            type="text"
            name="search"
            placeholder="Search for products..."
            class="w-full bg-transparent border-none focus:outline-none text-gray-800 text-sm placeholder-gray-500" />

        <!-- Clear Button -->
        <button id="searchCloseBtn" class="ml-2 text-gray-500 hover:text-gray-700 transition">
            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</form>


<!-- Overlay -->
<div id="storeDarkOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden"></div>
<div id="darkOverlay2" class="fixed inset-0 bg-black bg-opacity-50 opacity-0 invisible z-40 transition-opacity duration-300"></div>

<?php
include('MoveRightLoader.php');
include('MaintenanceAlert.php');
?>

<div class="sticky top-0 w-full bg-white border-b z-30 min-w-[380px]">
    <nav class="flex items-center justify-between max-w-[1050px] mx-auto p-3">
        <div class="flex items-end gap-1 select-none">
            <a href="Store.php">
                <img src="../UserImages/Screenshot_2024-11-29_201534-removebg-preview.png" class="w-28 select-none" alt="Logo">
            </a>
            <p class="text-amber-500 text-sm font-semibold">STORE</p>
        </div>
        <div class="flex items-center gap-5 select-none relative">
            <div class="items-center hidden sm:flex">
                <a href="RoomEssentials.php" class="flex items-center gap-1 font-semibold hover:bg-gray-100 p-2 rounded-sm transition-colors duration-200">
                    Room Essentials
                </a>
                <a href="Toiletries&Spa.php" class="flex items-center gap-1 font-semibold hover:bg-gray-100 p-2 rounded-sm transition-colors duration-200">
                    Toiletries and Spa
                </a>
                <a href="Traditional.php" class="flex items-center gap-1 font-semibold hover:bg-gray-100 p-2 rounded-sm transition-colors duration-200">
                    Traditional Products
                </a>
            </div>
            <i id="storeMenubar" class="ri-menu-4-line text-3xl cursor-pointer transition-transform duration-300 block sm:hidden"></i>
        </div>

        <!-- Mobile Sidebar -->
        <aside id="aside" class="fixed top-0 -right-full flex flex-col bg-white w-full sm:w-[330px] h-full p-4 z-50 transition-all duration-500 ease-in-out">
            <div class="flex justify-end pb-3">
                <i id="closeBtn" class="ri-close-line text-2xl cursor-pointer rounded transition-colors duration-300"></i>
            </div>
            <div class="flex flex-col justify-between gap-3 h-full">
                <div class="select-none">
                    <a href="../Store/RoomEssentials.php" class="flex justify-between text-slate-600 hover:bg-gray-100 p-2 rounded-sm transition-colors duration-300">
                        <p class="font-semibold text-2xl sm:text-sm">Room Essentials</p>
                    </a>
                    <a href="../Store/Toiletries&Spa.php" class="flex justify-between text-slate-600 hover:bg-gray-100 p-2 rounded-sm transition-colors duration-300">
                        <p class="font-semibold text-2xl sm:text-sm">Toiletries and Spa</p>
                    </a>
                    <a href="../Store/Traditional.php" class="flex justify-between text-slate-600 hover:bg-gray-100 p-2 rounded-sm transition-colors duration-300">
                        <p class="font-semibold text-2xl sm:text-sm">Traditional Products</p>
                    </a>
                </div>
        </aside>

        <!-- Overlay -->
        <div id="darkOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden"></div>
    </nav>
</div>