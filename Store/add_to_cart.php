<?php
session_start();
include('../config/db_connection.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
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

    <main class="max-w-[1310px] min-w-[380px] mx-auto p-4">
        <section>
            <div class="flex items-center">
                <!-- Step 1: Store -->
                <div class="flex flex-col items-center">
                    <div class="w-7 h-7 flex items-center justify-center bg-amber-500 text-white rounded-full">
                        1
                    </div>
                    <span class="text-sm font-medium text-gray-700">Store</span>
                </div>

                <!-- Line between steps -->
                <div class="flex-1 h-1 bg-amber-500"></div>

                <!-- Step 2: Cart -->
                <div class="flex flex-col items-center">
                    <div class="w-7 h-7 flex items-center justify-center bg-amber-500 text-white rounded-full">
                        2
                    </div>
                    <span class="text-sm font-medium text-gray-700">Cart</span>
                </div>

                <!-- Line between steps -->
                <div class="flex-1 h-1 bg-gray-200"></div>

                <!-- Step 3: Checkout -->
                <div class="flex flex-col items-center">
                    <div class="w-7 h-7 flex items-center justify-center bg-gray-200 text-gray-700 rounded-full">
                        3
                    </div>
                    <span class="text-sm font-medium text-gray-700">Checkout</span>
                </div>
            </div>
        </section>
    </main>

    <!-- Display products in a styled list -->
    <section class="max-w-[1370px] min-w-[380px] mx-auto px-4 pb-4">
        <div>
            <p class="text-2xl">Cart <span>(<?php echo $cartCount; ?>)</span></p>
        </div>

        <div class="flex flex-col md:flex-row justify-between">
            <div class="md:w-2/3 overflow-y-scroll h-[500px]">
                <?php
                if (isset($_SESSION['UserID'])) {
                    // Get pending order (cart) items from database
                    $cart_query = $connect->prepare("
            SELECT 
                o.OrderID,
                od.OrderUnitQuantity AS Quantity,
                od.ProductID,
                p.Title AS ProductName,
                od.OrderUnitPrice AS FinalPrice,
                p.Price AS BasePrice,
                p.DiscountPrice,
                s.Size,
                s.SizeID,
                s.PriceModifier,
                pi.ImageUserPath AS ProductImage
            FROM ordertb o
            JOIN orderdetailtb od ON o.OrderID = od.OrderID
            JOIN producttb p ON od.ProductID = p.ProductID
            JOIN sizetb s ON od.SizeID = s.SizeID AND od.ProductID = s.ProductID
            LEFT JOIN productimagetb pi ON pi.ProductID = p.ProductID AND pi.PrimaryImage = 1
            WHERE o.UserID = ? AND o.Status = 'pending'
        ");
                    $cart_query->bind_param("s", $_SESSION['UserID']);
                    $cart_query->execute();
                    $cart_result = $cart_query->get_result();

                    if ($cart_result->num_rows > 0) {
                        $order_id = null;
                        while ($item = $cart_result->fetch_assoc()) {
                            $order_id = $item['OrderID'];
                            $title = $item['ProductName'];
                            $image = $item['ProductImage'];
                            $size = $item['Size'];
                            $modifier = isset($item['PriceModifier']) ? (float)$item['PriceModifier'] : 0;
                            $basePrice = isset($item['BasePrice']) ? (float)$item['BasePrice'] : 0;
                            $discount = isset($item['DiscountPrice']) ? (float)$item['DiscountPrice'] : 0;
                            $quantity = $item['Quantity'];
                            $finalPrice = $item['FinalPrice'];

                            $originalPrice = $basePrice + $modifier;
                ?>
                            <div class="flex flex-col md:flex-row justify-between">
                                <form action="#" method="post" class="px-4 py-2 rounded-md flex flex-col md:flex-row justify-between cursor-pointer">
                                    <input type="hidden" name="order_id" value="<?= $order_id ?>">
                                    <input type="hidden" name="product_id" value="<?= $item['ProductID'] ?>">
                                    <input type="hidden" name="size_id" value="<?= $item['SizeID'] ?>">
                                    <div class="flex items-center flex-1">
                                        <div class="w-36">
                                            <img class="w-full h-full object-cover select-none" src="../UserImages/<?= htmlspecialchars($image) ?>" alt="Product Image">
                                        </div>
                                        <div class="ml-4">
                                            <h1 class="text-md sm:text-xl mb-1"><?= htmlspecialchars($title) ?></h1>
                                            <div class="mt-2 flex items-center space-x-2">
                                                <form method="post" action="">
                                                    <input type="hidden" name="product_id" value="<?= $item['ProductID'] ?>">
                                                    <input type="hidden" name="size_id" value="<?= $item['SizeID'] ?>">
                                                    <button type="submit" name="update_quantity" value="decrease" class="px-2 text-sm font-bold text-gray-600 hover:text-red-600">−</button>
                                                </form>
                                                <span class="text-sm"><?= $quantity ?></span>
                                                <form method="post" action="">
                                                    <input type="hidden" name="product_id" value="<?= $item['ProductID'] ?>">
                                                    <input type="hidden" name="size_id" value="<?= $item['SizeID'] ?>">
                                                    <button type="submit" name="update_quantity" value="increase" class="px-2 text-sm font-bold text-gray-600 hover:text-green-600">+</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between md:justify-end md:space-x-8">
                                        <div class="text-right">
                                            <?php if ($discount > 0): ?>
                                                <span class="text-xs text-gray-500 line-through">$<?= number_format($originalPrice, 2) ?></span>
                                                <span class="font-bold text-sm text-red-500 ml-1">$<?= number_format($finalPrice, 2) ?></span>
                                            <?php else: ?>
                                                <span class="font-bold text-sm text-black">$<?= number_format($originalPrice, 2) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="py-2 w-[90px] select-none">
                                            <form action="" method="post" class="ml-2">
                                                <input type="hidden" name="product_id" value="<?= $item['ProductID'] ?>">
                                                <input type="hidden" name="size_id" value="<?= $item['SizeID'] ?>">
                                                <button type="submit" name="remove_from_cart" class="text-red-500 hover:text-red-700 text-sm">
                                                    <i class="ri-delete-bin-6-line text-lg"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </form>
                            </div>
                <?php
                        }
                    } else {
                        echo '<p class="text-center text-gray-500 py-32">Your cart is empty.</p>';
                    }
                } else {
                    echo '<p class="text-center text-gray-500 py-32">Please login to view your cart.</p>';
                }
                ?>
            </div>

            <!-- Order Summary -->
            <div class="md:w-1/3 mt-10 md:mt-0 md:ml-4">
                <div class="p-4">
                    <h2 class="text-xl font-semibold mb-4">Order</h2>
                    <?php
                    $deliveryFee = !empty($subtotal) ? 5.00 : 0.00;
                    $tax = !empty($subtotal) ? $subtotal * 0.10 : 0.00;
                    $total = !empty($subtotal) ? $subtotal + $tax + $deliveryFee : 0.00;
                    ?>

                    <div class="space-y-2">
                        <p class="flex justify-between">
                            <span>Subtotal:</span>
                            <span>$<?= number_format($subtotal ?? 0.00, 2) ?></span>
                        </p>
                        <p class="flex justify-between">
                            <span>Delivery:</span>
                            <span>$<?= number_format($deliveryFee, 2) ?></span>
                        </p>
                        <p class="flex justify-between">
                            <span>Tax (10%):</span>
                            <span>$<?= number_format($tax, 2) ?></span>
                        </p>
                        <p class="flex justify-between font-semibold border-t pt-2">
                            <span>Total:</span>
                            <span>$<?= number_format($total, 2) ?></span>
                        </p>
                    </div>
                    <div class="mt-6 select-none">
                        <a href="../Store/store_checkout.php" class="block w-full text-center font-semibold bg-blue-900 text-white py-2 hover:bg-blue-950 transition duration-300">Proceed to Checkout</a>
                    </div>
                    <div class="mt-4 select-none">
                        <a href="../Store/store.php" class="block w-full border border-amber-500 p-2 text-center font-semibold text-amber-500 select-none hover:text-amber-600 transition-all duration-300">Continue Shopping</a>
                    </div>
                    <div class="flex gap-4 border p-4 mt-4 mb-4">
                        <i class="ri-truck-line text-2xl"></i>
                        <div>
                            <p>Free delivery on qualifying orders.</p>
                            <a href="Delivery.php" class="text-xs underline text-gray-500 hover:text-gray-400 transition-colors duration-200">View our Delivery & Returns Policy</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- MoveUp Btn -->
    <?php
    include('../includes/footer.php');
    ?>

    <script type="module" src="../JS/store.js"></script>
</body>

</html>