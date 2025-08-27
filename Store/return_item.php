<?php
session_start();
require_once('../config/db_connection.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

$response = ['success' => false, 'message' => ''];

if (isset($_POST['find_item'])) {
    $order_id = $_POST['order_id'];
    $email = $_POST['email'];

    // Check if order exists
    $checkQuery = "SELECT * FROM ordertb WHERE OrderID = ? AND Status = 'Delivered'";
    $stmt = $connect->prepare($checkQuery);
    $stmt->bind_param("s", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();
        $orderID = $order['OrderID'];
        $orderDate = $order['OrderDate'];
        $orderStatus = $order['Status'];

        // Check if email exists
        $checkEmailQuery = "SELECT * FROM usertb WHERE UserEmail = ?";
        $stmt = $connect->prepare($checkEmailQuery);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $emailResult = $stmt->get_result();
        $stmt->close();

        if ($emailResult->num_rows > 0) {
            $user = $emailResult->fetch_assoc();
            $userID = $user['UserID'];

            // Check if order belongs to user
            $checkOrderQuery = "SELECT * FROM ordertb WHERE OrderID = ? AND UserID = ?";
            $stmt = $connect->prepare($checkOrderQuery);
            $stmt->bind_param("ss", $orderID, $userID);
            $stmt->execute();
            $orderResult = $stmt->get_result();
            $stmt->close();

            if ($orderResult->num_rows > 0) {
                // Fetch ordered products
                $productQuery = "
               SELECT 
                    od.OrderID,
                    od.OrderUnitPrice,
                    od.OrderUnitQuantity,
                    od.SizeID,
                    s.Size,
                    p.ProductID, 
                    p.Title, 
                    ROUND(p.Price + (p.Price * (p.MarkupPercentage / 100)), 2) AS FinalPrice, 
                    pi.ImageUserPath
                FROM orderdetailtb od
                JOIN producttb p 
                    ON od.ProductID = p.ProductID
                LEFT JOIN productimagetb pi 
                    ON p.ProductID = pi.ProductID 
                    AND pi.PrimaryImage = 1
                LEFT JOIN sizetb s 
                    ON od.SizeID = s.SizeID
                LEFT JOIN returntb r 
                    ON od.OrderID = r.OrderID 
                    AND p.ProductID = r.ProductID
                WHERE od.OrderID = ? 
                    AND r.ProductID IS NULL;";

                $stmt = $connect->prepare($productQuery);
                $stmt->bind_param("s", $orderID);
                $stmt->execute();
                $productsResult = $stmt->get_result();
                $stmt->close();

                $products = [];
                while ($row = $productsResult->fetch_assoc()) {
                    $products[] = $row;
                }

                $response = [
                    'success' => true,
                    'products' => $products
                ];
            } else {
                $response = ['success' => false, 'message' => 'This order does not belong to the provided email address.'];
            }
        } else {
            $response = ['success' => false, 'message' => 'No account found with the provided email address.'];
        }
    } else {
        $response = ['success' => false, 'message' => 'No order was found with the provided order ID.'];
    }

    header('Content-Type: application/json');
    echo json_encode($response);
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
    <script src="//unpkg.com/alpinejs" defer></script>
    <link rel="stylesheet" href="../CSS/output.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../CSS/input.css?v=<?php echo time(); ?>">
</head>

<body class="relative min-w-[380px]">
    <!DOCTYPE html>
    <html lang="en">

    <main id="returnForm" class="flex justify-center items-center min-h-screen">
        <div class="p-8 w-full max-w-md">
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Return & Refund Item</h1>
                <p class="text-gray-600">Let's track your order first</p>
            </div>

            <div class="mb-5 bg-blue-50 p-4 rounded-lg border border-blue-100">
                <div class="flex items-start space-x-3">
                    <i class="fas fa-info-circle text-blue-500 text-lg mt-0.5"></i>
                    <div>
                        <h3 class="text-sm font-semibold text-blue-800">Where to find your Order ID</h3>
                        <ul class="list-disc pl-5 mt-2 text-sm text-blue-700 space-y-1">
                            <li>Order confirmation email</li>
                            <li>Account order history</li>
                            <li>Packing slip included with your order</li>
                        </ul>
                    </div>
                </div>
            </div>

            <form id="returnItemForm" action="<?php $_SERVER["PHP_SELF"]; ?>" method="POST" class="space-y-4">
                <div class="relative">
                    <label for="order_id" class="block text-sm font-medium text-gray-700 mb-1">Order ID</label>
                    <input
                        id="orderIDInput"
                        class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                        type="text"
                        name="order_id"
                        placeholder="Enter your order id">
                    <small id="orderIDError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-100 transition-all duration-200 select-none"></small>
                </div>

                <div class="relative">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input
                        type="email"
                        name="email"
                        id="emailInput"
                        class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300"
                        placeholder="Enter your email">
                    <small id="emailError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                </div>

                <input type="hidden" name="find_item" value="1">

                <button
                    type="submit" name="find_item"
                    class="w-full bg-amber-500 text-white font-semibold py-2 rounded-sm hover:bg-amber-600 transition duration-300 select-none">
                    Find My Order
                </button>
            </form>
            <div class="mt-6 text-center text-sm text-gray-500">
                <p>Enter your order ID and email to track your return status and initiate a return if eligible.</p>
            </div>
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">Need help with your return?
                    <a href="customer_support.php" class="text-amber-500 hover:underline">Contact Support</a>
                </p>
            </div>
        </div>
    </main>

    <div id="orderedProducts" class="justify-center mt-32 hidden p-3">
        <div class="w-full max-w-md">
            <h2 class="text-2xl font-bold text-gray-800 mb-3">Which product would you like to return?</h2>
            <p id="refundableMessage" class="text-sm text-gray-600 mb-2">
                Only products without an existing refund request are shown below.
            </p>
            <div id="productsContainer" class="space-y-2"></div>
            <div class="flex space-x-4 mt-4">
                <button id="backToFormButton"
                    class="w-1/2 text-gray-700 font-semibold py-2 transition duration-300 select-none">
                    Back
                </button>
                <button id="nextButton"
                    class="w-1/2 bg-amber-500 text-white font-semibold py-2 rounded-sm hover:bg-amber-600 transition duration-300 select-none">
                    Next
                </button>
            </div>
        </div>
    </div>

    <div id="actionSection" class="justify-center mt-32 hidden">
        <div class="w-full max-w-md p-3 bg-white">
            <h2 class="text-2xl font-bold text-gray-800 mb-3">Your Selected Product</h2>
            <div id="selectedProductContainer"></div>

            <div id="actionOptions">
                <h2 class="text-lg font-semibold text-gray-800 mb-3">Choose Your Action</h2>
                <form id="actionForm" class="space-y-4">
                    <div>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="radio" name="return_action" value="exchange" class="outline-none">
                            <span class="text-gray-700">Exchange for another product</span>
                        </label>
                    </div>
                    <div>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="radio" name="return_action" value="refund" class="outline-none">
                            <span class="text-gray-700">Request a refund</span>
                        </label>
                    </div>

                    <input type="hidden" name="form_type" value="action">

                    <div class="flex space-x-4 mt-4">
                        <button type="button" id="backButton"
                            class="w-1/2 text-gray-700 font-semibold py-2 transition duration-300 select-none">
                            Back
                        </button>
                        <button type="submit"
                            class="w-1/2 bg-amber-500 text-white font-semibold py-2 rounded-sm hover:bg-amber-600 transition duration-300 select-none">
                            Continue
                        </button>
                    </div>
                </form>
            </div>

            <!-- Refund reason section (hidden by default) -->
            <div id="refundReasonSection" class="hidden">
                <p class="text-xs text-red-600 mb-4">
                    Please note: Once you confirm, you cannot change your action (Exchange or Refund).
                </p>
                <h2 class="text-lg font-semibold text-gray-800">Select a reason for your refund</h2>
                <form id="refundReasonForm" class="space-y-3">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="radio" name="refund_reason" value="Damaged product">
                        <span class="text-gray-700">Received damaged product</span>
                    </label>
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="radio" name="refund_reason" value="Wrong item delivered">
                        <span class="text-gray-700">Wrong item delivered</span>
                    </label>
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="radio" name="refund_reason" value="Not as described">
                        <span class="text-gray-700">Item not as described</span>
                    </label>
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="radio" name="refund_reason" value="Other">
                        <span class="text-gray-700">Other</span>
                    </label>

                    <div class="flex space-x-4 mt-4">
                        <button type="button" id="backToAction"
                            class="w-1/2 text-gray-700 font-semibold py-2 transition duration-300 select-none">
                            Back
                        </button>
                        <button type="submit"
                            class="w-1/2 bg-amber-500 text-white font-semibold py-2 rounded-sm hover:bg-amber-600 transition duration-300 select-none">
                            Confirm Refund
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Loader -->
    <?php
    include('../includes/loader.php');
    include('../includes/alert.php');
    ?>

    <script type="module" src="../JS/store.js"></script>
</body>

</html>