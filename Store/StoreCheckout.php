<?php
session_start();
include('../config/dbConnection.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

$userID = (!empty($_SESSION["UserID"]) ? $_SESSION["UserID"] : null);

$alertMessage = '';
$signinSuccess = false;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['order'])) {
    $signinSuccess = true;
    $alertMessage = "No account found with the provided email. Please try again.";
}

$user = "SELECT * FROM usertb WHERE UserID = '$userID'";
$userData = $connect->query($user)->fetch_assoc();
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

                <div class="flex-1 h-1 bg-amber-500"></div>

                <!-- Step 2: Cart -->
                <div class="flex flex-col items-center">
                    <div class="w-7 h-7 flex items-center justify-center bg-amber-500 text-white rounded-full">
                        2
                    </div>
                    <span class="text-sm font-medium text-gray-700">Cart</span>
                </div>

                <div class="flex-1 h-1 bg-gray-200 transition-colors duration-500" id="line"></div>

                <!-- Step 3: Checkout -->
                <div class="flex flex-col items-center">
                    <div id="step" class="w-7 h-7 flex items-center justify-center bg-gray-200 text-gray-700 rounded-full transition-colors duration-500">
                        3
                    </div>
                    <span class="text-sm font-medium text-gray-700">Checkout</span>
                </div>
            </div>
        </section>
    </main>

    <!-- Display products in a styled list -->
    <section class="max-w-[1370px] min-w-[380px] mx-auto px-4 pb-4">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-2xl">Cart <span>(<?php echo $cartCount; ?>)</span></p>
            </div>
            <a href="AddToCart.php" class="text-sm text-gray-500">
                <i class="ri-arrow-left-line"></i>
                Back to cart
            </a>
        </div>

        <div class="flex flex-col md:flex-row justify-between">
            <div class="md:w-2/3 overflow-y-scroll h-[500px]">
                <?php
                if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
                    foreach ($_SESSION['cart'] as $key => $item) {
                        $product_id = $item['product_id'];
                        $size_id = $item['size_id'];
                        $quantity = $item['quantity'];

                        // Get product details with PriceModifier
                        $query = "
                SELECT 
                    p.Title AS ProductName, 
                    s.Size, 
                    s.PriceModifier,
                    p.Price, 
                    p.DiscountPrice, 
                    pi.ImageUserPath AS ProductImage 
                FROM producttb p
                JOIN sizetb s ON p.ProductID = s.ProductID AND s.SizeID = '$size_id'
                LEFT JOIN productimagetb pi ON pi.ProductID = p.ProductID AND pi.PrimaryImage = 1
                WHERE p.ProductID = '$product_id'
                LIMIT 1
            ";
                        $result = $connect->query($query);

                        if ($result && $result->num_rows > 0) {
                            $row = $result->fetch_assoc();
                            $title = $row['ProductName'];
                            $image = $row['ProductImage'];
                            $size = $row['Size'];
                            $modifier = isset($row['PriceModifier']) ? (float)$row['PriceModifier'] : 0;
                            $basePrice = isset($row['Price']) ? (float)$row['Price'] : 0;
                            $discount = isset($row['DiscountPrice']) ? (float)$row['DiscountPrice'] : 0;

                            $originalPrice = $basePrice + $modifier;
                            $finalPrice = ($discount > 0) ? ($discount + $modifier) : $originalPrice;
                ?>
                            <div class="flex flex-col md:flex-row justify-between mb-4">
                                <form action="#" method="post" class="px-4 py-2 rounded-md flex flex-col md:flex-row justify-between cursor-pointer">
                                    <input type="hidden" name="cart_id" value="<?= $key ?>">
                                    <div class="flex items-center flex-1">
                                        <div class="w-36">
                                            <img class="w-full h-full object-cover select-none" src="../UserImages/<?= htmlspecialchars($image) ?>" alt="Product Image">
                                        </div>
                                        <div class="ml-4">
                                            <h1 class="text-md sm:text-xl mb-1"><?= htmlspecialchars($title) ?></h1>
                                            <div class="mt-2 flex items-center space-x-2">
                                                <form method="post" action="">
                                                    <input type="hidden" name="update_key" value="<?= $key ?>">
                                                    <button type="submit" name="update_quantity" value="decrease" class="px-2 text-sm font-bold text-gray-600 hover:text-red-600">−</button>
                                                </form>
                                                <span class="text-sm"><?= $item['quantity'] ?></span>
                                                <form method="post" action="">
                                                    <input type="hidden" name="update_key" value="<?= $key ?>">
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
                                                <input type="hidden" name="remove_key" value="<?= $key ?>">
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
                    }
                } else {
                    echo '<p class="text-center text-gray-500 py-32">Your cart is empty.</p>';
                }
                ?>
            </div>

            <!-- Payment Summary -->
            <form id="paymentForm" action="<?php $_SERVER["PHP_SELF"] ?>" method="POST" class="space-y-2 md:w-[45%] lg:w-1/3 mt-10 md:mt-0 md:ml-4">
                <input type="hidden" name="product_id" value="12345">
                <input type="hidden" name="total_cost" value="100.00">

                <div>
                    <h1 class="text-xl font-semibold mb-1">Secure Checkout</h1>
                    <p class="border border-amber-200 bg-amber-50 bg-opacity-90 text-amber-500 p-2 rounded-sm">Checkout securely - it takes only a few minutes</p>
                </div>

                <div>
                    <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">User Detail</label>
                    <!-- Full Name -->
                    <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 space-x-0 sm:space-x-4 mb-4">
                        <div class="relative w-full">
                            <input
                                id="firstname"
                                class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                type="firstname"
                                name="firstname"
                                placeholder="Enter your firstname">
                            <small id="firstnameError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-100 transition-all duration-200 select-none">Firstname is required.</small>
                        </div>
                        <div class="relative w-full">
                            <input
                                id="lastname"
                                class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                type="lastname"
                                name="lastname"
                                placeholder="Enter your lastname">
                            <small id="lastnameError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-100 transition-all duration-200 select-none">Lastname is required.</small>
                        </div>
                    </div>
                    <!-- Shipping Address -->
                    <div class="relative">
                        <textarea
                            id="address"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            name="address"
                            placeholder="Enter your address">
</textarea>
                        <small id="addressError" class="absolute left-2 -bottom-0 bg-white text-red-500 text-xs opacity-100 transition-all duration-200 select-none">Address is required.</small>
                    </div>
                </div>

                <div>
                    <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">Contact Detail</label>
                    <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 space-x-0 sm:space-x-4 mb-4">
                        <!-- Email Address -->
                        <div class="relative w-full">
                            <input
                                id="email"
                                class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                type="email"
                                name="email"
                                value="<?php echo $userData['UserEmail']; ?>"
                                placeholder="Enter your email" disabled>
                            <small id="emailError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none">Email is required.</small>
                        </div>

                        <!-- Mobile Phone -->
                        <div class="relative w-full">
                            <input
                                id="phone"
                                class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                type="phone"
                                name="phone"
                                value="<?php echo $userData['UserPhone']; ?>"
                                placeholder="Enter your phone">
                            <small id="phoneError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none">Phone is required.</small>
                        </div>
                    </div>

                    <!-- City and State -->
                    <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 space-x-0 sm:space-x-4 mb-4">
                        <div class="relative w-full">
                            <input
                                id="city"
                                class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                type="city"
                                name="city"
                                placeholder="Enter your city">
                            <small id="cityError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none">City is required.</small>
                        </div>

                        <div class="relative w-full">
                            <input
                                id="state"
                                class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                type="state"
                                name="state"
                                placeholder="Enter your state">
                            <small id="stateError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none">State is required.</small>
                        </div>
                    </div>

                    <!-- ZIP Code -->
                    <div class="relative">
                        <input
                            id="zip"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="zip"
                            name="zip"
                            placeholder="Enter your zip">
                        <small id="zipError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none">Zip is required.</small>
                    </div>
                </div>

                <!-- Payment Method -->
                <div>
                    <label for="payment_method" class="block text-sm font-medium text-gray-700">Payment Method</label>
                    <select
                        name="payment_method"
                        id="payment_method"
                        class="mt-1 block w-full p-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out sm:text-sm"
                        onchange="togglePaymentInfo(this.value)">
                        <option value="" disabled selected>Select a payment method</option>
                        <option value="cash">Cash</option>
                        <option value="visa">Visa</option>
                        <option value="mastercard">MasterCard</option>
                        <option value="discover">Discover</option>
                        <option value="applepay">Apple Pay</option>
                    </select>
                </div>

                <!-- Payment Information Fields -->
                <div id="payment-info" class="mt-4 hidden">
                    <div id="credit-card-info" class="hidden">
                        <label for="card_number" class="block text-sm font-medium text-gray-700">Card Number</label>
                        <input type="text" id="card_number" name="card_number" class="mt-1 block w-full p-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out sm:text-sm" placeholder="1234 5678 9012 3456">
                    </div>
                </div>

                <script>
                    function togglePaymentInfo(paymentMethod) {
                        document.getElementById('payment-info').classList.add('hidden');
                        document.getElementById('credit-card-info').classList.add('hidden');

                        if (paymentMethod === 'visa' || paymentMethod === 'mastercard' || paymentMethod === 'discover') {
                            document.getElementById('payment-info').classList.remove('hidden');
                            document.getElementById('credit-card-info').classList.remove('hidden');
                        }
                    }
                </script>

                <button type="submit" name="order" class="block w-full text-center font-semibold bg-blue-900 text-white py-2 hover:bg-blue-950 transition duration-300">Place Order</button>
            </form>
        </div>
    </section>


    <!-- MoveUp Btn -->
    <?php
    include('../includes/Alert.php');
    include('../includes/Footer.php');
    ?>

    <script type="module" src="../JS/store.js"></script>
</body>

</html>