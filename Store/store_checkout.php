<?php
session_start();
require_once('../config/db_connection.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

$userID = (!empty($_SESSION["UserID"]) ? $_SESSION["UserID"] : null);

$alertMessage = '';
$response  = ['success' => false, 'message' => ''];

// Turn off error reporting to prevent HTML output before JSON
error_reporting(0);
ini_set('display_errors', 0);

// Ensure no output has been sent before headers
if (ob_get_length()) ob_clean();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['order'])) {
    // Initialize response array first
    $response = [];

    // Check if database connection is available
    if (!isset($connect) || !$connect) {
        $response = ['success' => false, 'message' => 'Database connection failed'];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    // Check if userID is set
    if (!isset($userID)) {
        $response = ['success' => false, 'message' => 'User not authenticated'];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    $firstname = mysqli_real_escape_string($connect, $_POST['firstname']);
    $lastname = mysqli_real_escape_string($connect, $_POST['lastname']);
    $address = mysqli_real_escape_string($connect, $_POST['address']);
    $contact = mysqli_real_escape_string($connect, $_POST['phone']);
    $email = mysqli_real_escape_string($connect, $_POST['email']);
    $city = mysqli_real_escape_string($connect, $_POST['city']);
    $state = mysqli_real_escape_string($connect, $_POST['state']);
    $zip = mysqli_real_escape_string($connect, $_POST['zip']);
    $remarks = mysqli_real_escape_string($connect, $_POST['remarks']);

    $fullname = $firstname . ' ' . $lastname;

    // Check if user has a pending order
    $cart_query = "SELECT OrderID FROM ordertb WHERE UserID = '$userID' AND Status = 'Pending'";
    $cart_result = $connect->query($cart_query);

    if ($cart_result && $cart_result->num_rows > 0) {
        // Get the order ID
        $order_data = $cart_result->fetch_assoc();
        $order_id = $order_data['OrderID'];

        // Update the existing pending order with customer information
        $update_order = $connect->prepare("
            UPDATE ordertb 
            SET FullName = ?, ShippingAddress = ?, PhoneNumber = ?, City = ?, State = ?, ZipCode = ?, Remarks = ? 
            WHERE OrderID = ?
        ");

        if ($update_order) {
            $update_order->bind_param("ssssssss", $fullname, $address, $contact, $city, $state, $zip, $remarks, $order_id);

            if ($update_order->execute()) {
                $response = ['success' => true, 'message' => 'Order information updated successfully.'];
            } else {
                // Use a generic error message to avoid exposing database errors
                $response = ['success' => false, 'message' => 'Failed to update order information.'];
            }
            $update_order->close();
        } else {
            $response = ['success' => false, 'message' => 'Failed to prepare update statement.'];
        }
    } else {
        $response = ['success' => false, 'message' => 'No pending order found. Please add products to your cart before payment.'];
    }

    // Ensure proper JSON encoding
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
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
            <a href="add_to_cart.php" class="text-sm text-gray-500">
                <i class="ri-arrow-left-line"></i>
                Back to cart
            </a>
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
                p.Price * (1 + p.MarkupPercentage / 100) AS BasePrice,
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
            WHERE o.UserID = ? AND o.Status = 'Pending'
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

            <!-- Payment Summary -->
            <form id="paymentForm" action="<?php $_SERVER["PHP_SELF"] ?>" method="POST" class="md:w-[45%] lg:w-1/3 mt-10 md:mt-0 md:ml-4">
                <div>
                    <div class="space-y-2">
                        <h1 class="text-xl font-semibold mb-1">Secure Checkout</h1>
                        <p class="border border-amber-200 bg-amber-50 bg-opacity-90 text-amber-500 p-2 rounded-sm">Checkout securely - it takes only a few minutes</p>
                    </div>

                    <div>
                        <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">User Details</label>
                        <!-- Full Name -->
                        <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 space-x-0 sm:space-x-4 mb-4">
                            <div class="relative w-full">
                                <input
                                    id="firstnameInput"
                                    class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                    type="text"
                                    name="firstname"
                                    placeholder="Enter your firstname">
                                <small id="firstnameError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                            </div>
                            <div class="relative w-full">
                                <input
                                    id="lastnameInput"
                                    class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                    type="text"
                                    name="lastname"
                                    placeholder="Enter your lastname">
                                <small id="lastnameError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                            </div>
                        </div>
                        <!-- Shipping Address -->
                        <div class="relative">
                            <textarea
                                id="addressInput"
                                class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                name="address"
                                placeholder="Enter your full shipping address"
                                rows="3"></textarea>
                            <small id="addressError" class="absolute left-2 -bottom-0 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                        </div>
                    </div>

                    <div>
                        <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">Contact Details</label>
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
                                    id="phoneInput"
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
                                    id="cityInput"
                                    class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                    type="city"
                                    name="city"
                                    placeholder="Enter your city">
                                <small id="cityError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none">City is required.</small>
                            </div>

                            <div class="relative w-full">
                                <input
                                    id="stateInput"
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
                                id="zipInput"
                                class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                type="zip"
                                name="zip"
                                placeholder="Enter your zip">
                            <small id="zipError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none">Zip is required.</small>
                        </div>

                        <!-- Remarks -->
                        <div class="relative">
                            <label for="payment_method" class="block text-sm font-medium text-gray-700 my-1">Remarks (Optional)</label>
                            <textarea
                                id="remarksInput"
                                class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                name="remarks"
                                placeholder="Enter your remarks"
                                rows="2"></textarea>
                            <small id="remarksError" class="absolute left-2 -bottom-0 bg-white text-red-500 text-xs opacity-100 transition-all duration-200 select-none"></small>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="order" value="1">

                <button type="submit" id="submitButton" name="order" class="flex items-center justify-center mt-4 w-full text-center font-semibold bg-blue-900 text-white py-2 hover:bg-blue-950 transition duration-300 select-none">
                    <span id="buttonText">Continue to Payment</span>
                    <svg id="buttonSpinner" class="hidden w-5 h-5 ml-2 text-white animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </form>
        </div>
    </section>


    <!-- MoveUp Btn -->
    <?php
    include('../includes/alert.php');
    include('../includes/footer.php');
    ?>

    <script type="module" src="../JS/store.js"></script>
</body>

</html>