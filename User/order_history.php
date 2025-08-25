<?php
session_start();
require_once('../config/db_connection.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

$session_userID = (!empty($_SESSION["UserID"]) ? $_SESSION["UserID"] : null);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | Opulence Haven</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../CSS/output.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../CSS/input.css?v=<?php echo time(); ?>">
    <!-- Swiper JS -->
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    <!-- AOS CSS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
</head>

<body class="relative min-w-[350px]">
    <?php
    include('../includes/navbar.php');
    include('../includes/cookies.php');
    ?>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-center text-blue-700 mb-8">My Orders</h1>

        <?php
        // Fetch orders for the user
        $stmt = $connect->prepare("SELECT * FROM ordertb WHERE UserID = ? ORDER BY OrderDate DESC");
        $stmt->bind_param("i", $session_userID);
        $stmt->execute();
        $result = $stmt->get_result();
        $orders = $result->fetch_all(MYSQLI_ASSOC);

        if (count($orders) > 0) {
            foreach ($orders as $order) {
                // Fetch order details for each order
                $stmt2 = $connect->prepare("SELECT * FROM orderdetailtb WHERE OrderID = ?");
                $stmt2->bind_param("i", $order['OrderID']);
                $stmt2->execute();
                $orderDetails = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

                // Format order date
                $orderDate = date("M j, Y g:i A", strtotime($order['OrderDate']));

                // Determine status color
                $statusColor = 'bg-gray-200 text-gray-800';
                if ($order['Status'] === 'Completed') $statusColor = 'bg-green-200 text-green-800';
                if ($order['Status'] === 'Processing') $statusColor = 'bg-blue-200 text-blue-800';
                if ($order['Status'] === 'Cancelled') $statusColor = 'bg-red-200 text-red-800';
                if ($order['Status'] === 'Shipped') $statusColor = 'bg-purple-200 text-purple-800';
        ?>

                <div class="bg-white rounded-lg shadow-md mb-6 overflow-hidden">
                    <!-- Order Header -->
                    <div class="bg-blue-50 p-4 border-b flex flex-wrap justify-between items-center">
                        <div class="mb-2 md:mb-0">
                            <span class="font-semibold">Order ID:</span>
                            <span class="text-blue-600"><?php echo $order['OrderID']; ?></span>
                        </div>
                        <div class="mb-2 md:mb-0">
                            <span class="font-semibold">Placed on:</span>
                            <span><?php echo $orderDate; ?></span>
                        </div>
                        <div>
                            <span class="font-semibold">Status:</span>
                            <span class="px-2 py-1 rounded-full text-sm <?php echo $statusColor; ?>">
                                <?php echo $order['Status']; ?>
                            </span>
                        </div>
                    </div>

                    <!-- Order Details -->
                    <div class="p-4">
                        <h2 class="text-xl font-semibold mb-4 text-gray-700">Order Items</h2>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php
                                    $itemsTotal = 0;
                                    foreach ($orderDetails as $detail) {
                                        $itemTotal = $detail['OrderUnitQuantity'] * $detail['OrderUnitPrice'];
                                        $itemsTotal += $itemTotal;
                                    ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">Product <?php echo $detail['ProductID']; ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap"><?php echo $detail['SizeID']; ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap"><?php echo $detail['OrderUnitQuantity']; ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap">$<?php echo number_format($detail['OrderUnitPrice'], 2); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap">$<?php echo number_format($itemTotal, 2); ?></td>
                                        </tr>
                                    <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Order Summary -->
                        <div class="mt-6 flex justify-end">
                            <div class="w-full md:w-1/3">
                                <div class="flex justify-between py-2">
                                    <span class="font-medium">Items Total:</span>
                                    <span>$<?php echo number_format($itemsTotal, 2); ?></span>
                                </div>
                                <div class="flex justify-between py-2">
                                    <span class="font-medium">Tax:</span>
                                    <span>$<?php echo number_format($order['OrderTax'], 2); ?></span>
                                </div>
                                <div class="flex justify-between py-2 border-t border-gray-300">
                                    <span class="font-semibold">Grand Total:</span>
                                    <span class="font-semibold">$<?php echo number_format($order['TotalPrice'], 2); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Shipping Information -->
                    <div class="bg-gray-50 p-4 border-t">
                        <h2 class="text-lg font-semibold mb-2 text-gray-700">Shipping Information</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p><span class="font-medium">Name:</span> <?php echo $order['FullName']; ?></p>
                                <p><span class="font-medium">Address:</span> <?php echo $order['ShippingAddress']; ?></p>
                            </div>
                            <div>
                                <p><span class="font-medium">City:</span> <?php echo $order['City']; ?>, <?php echo $order['State']; ?> <?php echo $order['ZipCode']; ?></p>
                                <p><span class="font-medium">Phone:</span> <?php echo $order['PhoneNumber']; ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Remarks -->
                    <?php if (!empty($order['Remarks'])) { ?>
                        <div class="p-4 border-t">
                            <h2 class="text-lg font-semibold mb-2 text-gray-700">Remarks</h2>
                            <p class="text-gray-600"><?php echo $order['Remarks']; ?></p>
                        </div>
                    <?php } ?>
                </div>
        <?php
            }
        } else {
            echo '<div class="bg-white rounded-lg shadow-md p-8 text-center">';
            echo '<i class="fas fa-box-open text-5xl text-gray-400 mb-4"></i>';
            echo '<h2 class="text-2xl font-semibold text-gray-600">No orders found</h2>';
            echo '<p class="text-gray-500 mt-2">You haven\'t placed any orders yet.</p>';
            echo '</div>';
        }
        ?>
    </div>

    <?php
    include('../includes/moveup_btn.php');
    include('../includes/footer.php');
    ?>

    <script src="//unpkg.com/alpinejs" defer></script>
    <script type="module" src="../JS/index.js"></script>
</body>

</html>