<div class="relative group">
    <a href="../Store/add_to_cart.php" class="bg-blue-900 text-white py-1 px-3 cursor-pointer flex items-center gap-2">
        <i class="ri-shopping-cart-2-line text-xl"></i>
        <span id="cartCount">
            <?php
            $cartCount = 0;
            if (isset($_SESSION['UserID'])) {
                $count_query = $connect->prepare("
                    SELECT SUM(OrderUnitQuantity) as total 
                    FROM orderdetailtb od
                    JOIN ordertb o ON od.OrderID = o.OrderID
                    WHERE o.UserID = ? AND o.Status = 'Pending'
                ");
                $count_query->bind_param("s", $_SESSION['UserID']);
                $count_query->execute();
                $count_result = $count_query->get_result();
                $count_row = $count_result->fetch_assoc();
                $cartCount = $count_row['total'] ?? 0;
            }
            echo $cartCount . ' item' . ($cartCount >= 1 ? 's' : '');
            ?>
        </span>
    </a>

    <!-- Dropdown Cart -->
    <div id="cartDropdown" class="absolute top-full right-0 bg-gray-100 p-3 z-40 w-96 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-opacity duration-300">
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
                    p.SaleQuantity,
                    pi.ImageUserPath,
                    s.Size,
                    s.PriceModifier,
                    od.OrderUnitPrice
                FROM ordertb o
                JOIN orderdetailtb od ON o.OrderID = od.OrderID
                JOIN producttb p ON od.ProductID = p.ProductID
                LEFT JOIN productimagetb pi ON p.ProductID = pi.ProductID AND pi.PrimaryImage = 1
                LEFT JOIN sizetb s ON od.SizeID = s.SizeID AND od.ProductID = s.ProductID
                WHERE o.UserID = ? AND o.Status = 'Pending'
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
                        class="block mt-3 bg-blue-900 text-white text-center py-2 text-sm rounded-sm hover:bg-blue-950 transition-colors">
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