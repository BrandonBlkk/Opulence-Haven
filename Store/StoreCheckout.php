<?php
session_start();
include('../config/dbConnection.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

$alertMessage = '';
$signinSuccess = false;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['order'])) {
    $signinSuccess = true;
    $alertMessage = "No account found with the provided email. Please try again.";
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
            <p class="text-2xl">Cart <span>(2)</span></p>
            <a href="AddToCart.php" class="text-sm text-gray-500">
                <i class="ri-arrow-left-line"></i>
                Back to cart
            </a>
        </div>

        <div class="flex flex-col md:flex-row justify-between">
            <div class="md:w-2/3 overflow-y-scroll h-[500px]">
                <form action="#" method="post" class="px-4 py-2 rounded-md flex flex-col md:flex-row justify-between cursor-pointer">
                    <input type="hidden" name="cart_id" value="1">
                    <div class="flex items-center">
                        <div class="w-36">
                            <img class="w-full h-full object-cover select-none" src="../UserImages/white-pillow.jpg" alt="Product Image">
                        </div>
                        <div class="ml-4">
                            <h1 class="text-md sm:text-xl mb-1">Sample Product Title</h1>
                            <p class="text-xs sm:text-sm text-gray-400">Size: <span class="text-black">M</span></p>
                            <input type="number" name="new_quantity" value="2" min="1" class="mt-2 w-16 px-2 py-1 border border-gray-300 rounded-md text-gray-700 focus:outline-none focus:border-indigo-500" disabled>
                        </div>
                    </div>
                    <div class="text-right flex flex-row-reverse md:flex-col items-center">
                        <div class="mb-2">
                            <span class="text-xs text-gray-500 line-through">$25.00</span>
                            <span class="font-bold text-sm text-red-500">$20.00</span>
                        </div>
                    </div>
                </form>

                <form action="#" method="post" class="px-4 py-2 rounded-md flex flex-col md:flex-row justify-between cursor-pointer">
                    <input type="hidden" name="cart_id" value="2">
                    <div class="flex items-center">
                        <div class="w-36">
                            <img class="w-full h-full object-cover select-none" src="../UserImages/bed-945881_1280.jpg" alt="Product Image">
                        </div>
                        <div class="ml-4">
                            <h1 class="text-md sm:text-xl mb-1">Another Sample Product</h1>
                            <p class="text-xs sm:text-sm text-gray-400">Size: <span class="text-black">L</span></p>
                            <input type="number" name="new_quantity" value="1" min="1" class="mt-2 w-16 px-2 py-1 border border-gray-300 rounded-md text-gray-700 focus:outline-none focus:border-indigo-500" disabled>
                        </div>
                    </div>
                    <div class="text-right flex flex-row-reverse md:flex-col items-center">
                        <div class="mb-2">
                            <span class="text-xs text-gray-500 line-through">$30.00</span>
                            <span class="font-bold text-sm text-red-500">$25.00</span>
                        </div>
                    </div>
                </form>
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
                                placeholder="Enter your email">
                            <small id="emailError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none">Email is required.</small>
                        </div>

                        <!-- Mobile Phone -->
                        <div class="relative w-full">
                            <input
                                id="phone"
                                class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                type="phone"
                                name="phone"
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

    <script src="../JS/store.js"></script>
</body>

</html>