<?php
session_start();
include('../config/dbConnection.php');

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
    include('../includes/StoreNavbar.php');
    ?>

    <main class="max-w-[1310px] mx-auto p-4">
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
    <section class="max-w-[1370px] mx-auto px-4 pb-4">
        <div>
            <p class="text-2xl">Cart <span>(2)</span></p>
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
                            <input type="number" name="new_quantity" value="2" min="1" class="mt-2 w-16 px-2 py-1 border border-gray-300 rounded-md text-gray-700 focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>
                    <div class="text-right flex flex-row-reverse md:flex-col items-center">
                        <div class="px-4 py-2 w-[90px] select-none">
                            <button type="submit" name="updateBtn" class="text-blue-500 hover:text-blue-700 ml-2">
                                <i class="ri-edit-circle-line text-lg"></i>
                            </button>
                            <button type="submit" name="deleteBtn" class="text-red-500 hover:text-red-700 ml-2">
                                <i class="ri-delete-bin-6-line text-lg"></i>
                            </button>
                        </div>
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
                            <input type="number" name="new_quantity" value="1" min="1" class="mt-2 w-16 px-2 py-1 border border-gray-300 rounded-md text-gray-700 focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>
                    <div class="text-right flex flex-row-reverse md:flex-col items-center">
                        <div class="px-4 py-2 w-[90px] select-none">
                            <button type="submit" name="updateBtn" class="text-blue-500 hover:text-blue-700 ml-2">
                                <i class="ri-edit-circle-line text-lg"></i>
                            </button>
                            <button type="submit" name="deleteBtn" class="text-red-500 hover:text-red-700 ml-2">
                                <i class="ri-delete-bin-6-line text-lg"></i>
                            </button>
                        </div>
                        <div class="mb-2">
                            <span class="text-xs text-gray-500 line-through">$30.00</span>
                            <span class="font-bold text-sm text-red-500">$25.00</span>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Order Summary -->
            <div class="md:w-1/3 mt-10 md:mt-0 md:ml-4">
                <div class="p-4">
                    <h2 class="text-xl font-semibold mb-4">Order</h2>
                    <div class="space-y-2">
                        <p class="flex justify-between"><span>Subtotal:</span> <span>$45.00</span></p>
                        <p class="flex justify-between"><span>Delivery:</span> <span>$5.00</span></p>
                        <p class="flex justify-between font-semibold border-t"><span>Total:</span> <span>$50.00</span></p>
                    </div>
                    <div class="mt-6 select-none">
                        <a href="../Store/StoreCheckout.php" class="block w-full text-center font-semibold bg-blue-900 text-white py-2 hover:bg-blue-950 transition duration-300">Proceed to Checkout</a>
                    </div>
                    <div class="mt-4 select-none">
                        <a href="../Store/Store.php" class="block w-full border border-amber-500 p-2 text-center font-semibold text-amber-500 select-none hover:text-amber-600 transition-all duration-300">Continue Shopping</a>
                    </div>
                    <div class="flex gap-4 border p-4 mt-4 mb-4">
                        <i class="ri-truck-line text-2xl"></i>
                        <div>
                            <p>Free delivery on qualifying orders.</p>
                            <a href="Delivery.php" class="text-xs underline text-gray-500 hover:text-gray-400 transition-colors duration-200">View our Delivery & Returns Policy</a>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col py-5">
                    <h1 class="text-xl font-semibold mb-1">Payment Methods:</h1>
                    <ul class="flex gap-2 select-none">
                        <li>
                            <img class="w-9" src="../UserImages/fashion-designer-cc-visa-icon.svg" alt="Icon">
                        </li>
                        <li>
                            <img class="w-9" src="../UserImages/fashion-designer-cc-mastercard-icon.svg" alt="Icon">
                        </li>
                        <li>
                            <img class="w-9" src="../UserImages/fashion-designer-cc-discover-icon.svg" alt="Icon">
                        </li>
                        <li>
                            <img class="w-9" src="../UserImages/fashion-designer-cc-apple-pay-icon.svg" alt="Icon">
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>


    <!-- MoveUp Btn -->
    <?php
    include('../includes/Footer.php');
    ?>

    <script src="../JS/store.js"></script>
</body>

</html>