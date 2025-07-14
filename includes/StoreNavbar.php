<?php
include('../User/cleanup_reservations.php');
?>
<section class="bg-gray-100 px-3 min-w-[380px]">
    <div class="flex items-center justify-end max-w-[1050px] mx-auto gap-5 select-none">
        <!-- Search Icon -->
        <i id="search-icon" class="ri-search-line text-xl cursor-pointer"></i>

        <!-- Account and Favorites -->
        <div class="flex items-center">
            <!-- My Account -->
            <a href="../User/user_signin.php" class="font-semibold text-slate-500 hover:bg-gray-200 p-2 rounded-sm transition-colors duration-200">
                <?php echo !empty($_SESSION['UserName']) ? $_SESSION['UserName'] : 'My account'; ?>
            </a>
            <!-- Favorites -->
            <a href="favorite.php" class="flex items-center gap-2 font-semibold text-slate-500 hover:bg-gray-200 p-2 rounded-sm transition-colors duration-200">
                <i class="ri-heart-line text-xl"></i>
                <span>Favorites</span>
            </a>
        </div>

        <?php
        // Remove from cart
        if (isset($_POST['remove_from_cart']) && isset($_POST['product_id']) && isset($_POST['size_id'])) {
            $product_id = $_POST['product_id'];
            $size_id = $_POST['size_id'];
            $user_id = $_SESSION['UserID'] ?? null;

            if ($user_id) {
                // Get pending order ID for this user
                $order_query = $connect->prepare("SELECT OrderID FROM ordertb WHERE UserID = ? AND Status = 'pending' LIMIT 1");
                $order_query->bind_param("s", $user_id);
                $order_query->execute();
                $order_result = $order_query->get_result();

                if ($order_result->num_rows > 0) {
                    $order_id = $order_result->fetch_assoc()['OrderID'];

                    // Get quantity being removed to restore stock
                    $detail_query = $connect->prepare("
                SELECT OrderUnitQuantity 
                FROM orderdetailtb 
                WHERE OrderID = ? AND ProductID = ? AND SizeID = ?
            ");
                    $detail_query->bind_param("ssi", $order_id, $product_id, $size_id);
                    $detail_query->execute();
                    $detail_result = $detail_query->get_result();

                    if ($detail_result->num_rows > 0) {
                        $quantity = $detail_result->fetch_assoc()['OrderUnitQuantity'];

                        // Restore stock
                        $update_stock = $connect->prepare("UPDATE producttb SET Stock = Stock + ? WHERE ProductID = ?");
                        $update_stock->bind_param("is", $quantity, $product_id);
                        $update_stock->execute();

                        // Remove item from order details
                        $delete_item = $connect->prepare("
                    DELETE FROM orderdetailtb 
                    WHERE OrderID = ? AND ProductID = ? AND SizeID = ?
                ");
                        $delete_item->bind_param("ssi", $order_id, $product_id, $size_id);
                        $delete_item->execute();

                        // If no more items, delete the pending order
                        $check_empty = $connect->prepare("SELECT COUNT(*) AS count FROM orderdetailtb WHERE OrderID = ?");
                        $check_empty->bind_param("s", $order_id);
                        $check_empty->execute();
                        $empty_result = $check_empty->get_result()->fetch_assoc();

                        if ($empty_result['count'] == 0) {
                            $delete_order = $connect->prepare("DELETE FROM ordertb WHERE OrderID = ?");
                            $delete_order->bind_param("s", $order_id);
                            $delete_order->execute();
                        }
                    }
                }
            }

            header("Location: " . $_SERVER['REQUEST_URI']);
            exit();
        }

        // Update quantity in cart
        if (isset($_POST['update_quantity'])) {
            $product_id = $_POST['product_id'] ?? null;
            $size_id = $_POST['size_id'] ?? null;
            $action = $_POST['update_quantity'];
            $user_id = $_SESSION['UserID'] ?? null;

            if ($user_id && $product_id && $size_id) {
                // Get pending order ID
                $order_query = $connect->prepare("SELECT OrderID FROM ordertb WHERE UserID = ? AND Status = 'pending' LIMIT 1");
                $order_query->bind_param("s", $user_id);
                $order_query->execute();
                $order_result = $order_query->get_result();

                if ($order_result->num_rows > 0) {
                    $order_id = $order_result->fetch_assoc()['OrderID'];

                    // Get current quantity and product stock
                    $detail_query = $connect->prepare("
                SELECT od.OrderUnitQuantity, p.Stock 
                FROM orderdetailtb od
                JOIN producttb p ON od.ProductID = p.ProductID
                WHERE od.OrderID = ? AND od.ProductID = ? AND od.SizeID = ?
            ");
                    $detail_query->bind_param("ssi", $order_id, $product_id, $size_id);
                    $detail_query->execute();
                    $detail_result = $detail_query->get_result();

                    if ($detail_result->num_rows > 0) {
                        $row = $detail_result->fetch_assoc();
                        $current_quantity = $row['OrderUnitQuantity'];
                        $current_stock = $row['Stock'];

                        if ($action === 'increase' && $current_stock > 0) {
                            // Increase quantity in order details
                            $update_detail = $connect->prepare("
                        UPDATE orderdetailtb 
                        SET OrderUnitQuantity = OrderUnitQuantity + 1 
                        WHERE OrderID = ? AND ProductID = ? AND SizeID = ?
                    ");
                            $update_detail->bind_param("ssi", $order_id, $product_id, $size_id);
                            $update_detail->execute();

                            // Reduce stock
                            $update_stock = $connect->prepare("UPDATE producttb SET Stock = Stock - 1 WHERE ProductID = ?");
                            $update_stock->bind_param("s", $product_id);
                            $update_stock->execute();
                        } elseif ($action === 'decrease') {
                            if ($current_quantity > 1) {
                                // Decrease quantity
                                $update_detail = $connect->prepare("
                            UPDATE orderdetailtb 
                            SET OrderUnitQuantity = OrderUnitQuantity - 1 
                            WHERE OrderID = ? AND ProductID = ? AND SizeID = ?
                        ");
                                $update_detail->bind_param("ssi", $order_id, $product_id, $size_id);
                                $update_detail->execute();

                                // Restore stock
                                $update_stock = $connect->prepare("UPDATE producttb SET Stock = Stock + 1 WHERE ProductID = ?");
                                $update_stock->bind_param("s", $product_id);
                                $update_stock->execute();
                            } else {
                                // Remove item if quantity would be 0
                                $delete_item = $connect->prepare("
                            DELETE FROM orderdetailtb 
                            WHERE OrderID = ? AND ProductID = ? AND SizeID = ?
                        ");
                                $delete_item->bind_param("ssi", $order_id, $product_id, $size_id);
                                $delete_item->execute();

                                // Restore stock
                                $update_stock = $connect->prepare("UPDATE producttb SET Stock = Stock + 1 WHERE ProductID = ?");
                                $update_stock->bind_param("s", $product_id);
                                $update_stock->execute();

                                // Check if order is now empty
                                $check_empty = $connect->prepare("SELECT COUNT(*) AS count FROM orderdetailtb WHERE OrderID = ?");
                                $check_empty->bind_param("s", $order_id);
                                $check_empty->execute();
                                $empty_result = $check_empty->get_result()->fetch_assoc();

                                if ($empty_result['count'] == 0) {
                                    $delete_order = $connect->prepare("DELETE FROM ordertb WHERE OrderID = ?");
                                    $delete_order->bind_param("s", $order_id);
                                    $delete_order->execute();
                                }
                            }
                        }
                    }
                }
            }

            header("Location: " . $_SERVER['REQUEST_URI']);
            exit();
        }
        ?>

        <!-- Shopping Cart -->
        <div class="relative group">
            <a href="../Store/add_to_cart.php" class="bg-blue-900 text-white py-1 px-3 cursor-pointer flex items-center gap-2">
                <i class="ri-shopping-cart-2-line text-xl"></i>
                <span>
                    <?php
                    $cartCount = 0;
                    if (isset($_SESSION['UserID'])) {
                        $count_query = $connect->prepare("
                    SELECT SUM(OrderUnitQuantity) as total 
                    FROM orderdetailtb od
                    JOIN ordertb o ON od.OrderID = o.OrderID
                    WHERE o.UserID = ? AND o.Status = 'pending'
                ");
                        $count_query->bind_param("s", $_SESSION['UserID']);
                        $count_query->execute();
                        $count_result = $count_query->get_result();
                        $count_row = $count_result->fetch_assoc();
                        $cartCount = $count_row['total'] ?? 0;
                    }
                    echo $cartCount . ' item' . ($cartCount != 1 ? 's' : '');
                    ?>
                </span>
            </a>

            <!-- Dropdown Cart -->
            <div class="absolute top-full right-0 bg-gray-100 p-3 z-40 w-96 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-opacity duration-300">
                <?php if (isset($_SESSION['UserID'])):
                    // Get pending order items
                    $cart_query = $connect->prepare("
                SELECT 
                    o.OrderID,
                    od.ProductID,
                    od.SizeID,
                    od.OrderUnitQuantity AS Quantity,
                    p.Title,
                    p.Price,
                    p.DiscountPrice,
                    p.Stock,
                    pi.ImageUserPath,
                    s.Size,
                    s.PriceModifier,
                    od.OrderUnitPrice
                FROM ordertb o
                JOIN orderdetailtb od ON o.OrderID = od.OrderID
                JOIN producttb p ON od.ProductID = p.ProductID
                LEFT JOIN productimagetb pi ON p.ProductID = pi.ProductID AND pi.PrimaryImage = 1
                LEFT JOIN sizetb s ON od.SizeID = s.SizeID AND od.ProductID = s.ProductID
                WHERE o.UserID = ? AND o.Status = 'pending'
            ");
                    $cart_query->bind_param("s", $_SESSION['UserID']);
                    $cart_query->execute();
                    $cart_result = $cart_query->get_result();

                    if ($cart_result->num_rows > 0):
                        $total = 0;
                        while ($item = $cart_result->fetch_assoc()):
                            $price = $item['OrderUnitPrice'];
                            $subtotal = $price * $item['Quantity'];
                            $total += $subtotal;
                ?>
                            <div class="flex items-start gap-3 p-2 bg-white rounded">
                                <img src="<?= !empty($item['ImageUserPath']) ? '../UserImages/' . $item['ImageUserPath'] : '../UserImages/default.jpg' ?>"
                                    alt="<?= $item['Title'] ?>"
                                    class="w-16 h-16 object-cover rounded border">
                                <div class="flex-1">
                                    <h4 class="font-medium text-sm"><?= $item['Title'] ?></h4>
                                    <p class="text-xs text-gray-500">Size: <?= $item['Size'] ?? 'N/A' ?></p>
                                    <div class="flex items-center justify-between mt-1">
                                        <div class="flex items-center gap-1">
                                            <form method="post" action="">
                                                <input type="hidden" name="product_id" value="<?= $item['ProductID'] ?>">
                                                <input type="hidden" name="size_id" value="<?= $item['SizeID'] ?>">
                                                <button type="submit" name="update_quantity" value="decrease"
                                                    class="px-2 text-sm font-bold text-gray-600 hover:text-red-600">−</button>
                                            </form>
                                            <span class="text-sm"><?= $item['Quantity'] ?></span>
                                            <form method="post" action="">
                                                <input type="hidden" name="product_id" value="<?= $item['ProductID'] ?>">
                                                <input type="hidden" name="size_id" value="<?= $item['SizeID'] ?>">
                                                <button type="submit" name="update_quantity" value="increase"
                                                    class="px-2 text-sm font-bold text-gray-600 hover:text-green-600">+</button>
                                            </form>
                                        </div>
                                        <span class="font-medium text-sm">$<?= number_format($subtotal, 2) ?></span>
                                    </div>
                                </div>
                                <form action="" method="post" class="ml-2">
                                    <input type="hidden" name="product_id" value="<?= $item['ProductID'] ?>">
                                    <input type="hidden" name="size_id" value="<?= $item['SizeID'] ?>">
                                    <button type="submit" name="remove_from_cart" class="text-red-500 hover:text-red-700 text-sm">
                                        <i class="ri-close-line"></i>
                                    </button>
                                </form>
                            </div>
                        <?php endwhile; ?>

                        <div class="bg-white p-3 rounded border-t">
                            <div class="flex justify-between font-semibold">
                                <span>Total:</span>
                                <span>$<?= number_format($total, 2) ?></span>
                            </div>
                            <a href="../Store/add_to_cart.php"
                                class="block mt-3 bg-blue-900 text-white text-center py-2 text-sm rounded hover:bg-blue-800 transition-colors">
                                Checkout
                            </a>
                        </div>
                    <?php else: ?>
                        <p class="font-semibold text-gray-600 text-center py-4">You have no items in your cart.</p>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="font-semibold text-gray-600 text-center py-4">Please login to view your cart.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Search Bar -->
<form method="get" action="../Store/product_search.php" id="search-bar" class="fixed -top-full w-full bg-white py-5 px-4 shadow-lg transition-all duration-300 z-50">
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
            <a href="store.php">
                <img src="../UserImages/Screenshot_2024-11-29_201534-removebg-preview.png" class="w-28 select-none" alt="Logo">
            </a>
            <p class="text-amber-500 text-sm font-semibold">STORE</p>
        </div>
        <div class="flex items-center gap-5 select-none relative">
            <div class="items-center hidden sm:flex">
                <a href="room_essentials.php" class="flex items-center gap-1 font-semibold hover:bg-gray-100 p-2 rounded-sm transition-colors duration-200">
                    Room Essentials
                </a>
                <a href="toiletrie_spa.php" class="flex items-center gap-1 font-semibold hover:bg-gray-100 p-2 rounded-sm transition-colors duration-200">
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
                    <a href="../Store/room_essentials.php" class="flex justify-between text-slate-600 hover:bg-gray-100 p-2 rounded-sm transition-colors duration-300">
                        <p class="font-semibold text-2xl sm:text-sm">Room Essentials</p>
                    </a>
                    <a href="../Store/toiletrie_spa.php" class="flex justify-between text-slate-600 hover:bg-gray-100 p-2 rounded-sm transition-colors duration-300">
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