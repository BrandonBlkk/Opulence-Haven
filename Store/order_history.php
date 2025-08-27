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
    <title>My Orders | Opulence Haven</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../CSS/output.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../CSS/input.css?v=<?php echo time(); ?>">
    <!-- Swiper JS -->
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    <!-- AOS CSS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="min-w-[350px]">
    <?php
    include('../includes/navbar.php');
    include('../includes/cookies.php');
    ?>

    <div class="container mx-auto px-4 py-10 max-w-6xl">
        <h1 class="text-2xl sm:text-4xl text-center mb-5 text-blue-900 tracking-wide">Order History</h1>

        <?php
        $stmt = $connect->prepare("SELECT * FROM ordertb WHERE UserID = ? ORDER BY OrderDate DESC");
        $stmt->bind_param("s", $session_userID);
        $stmt->execute();
        $result = $stmt->get_result();
        $orders = $result->fetch_all(MYSQLI_ASSOC);

        if (count($orders) > 0) {
            foreach ($orders as $order) {
                $stmt2 = $connect->prepare("SELECT od.*, p.Title, p.Price, pi.ImageUserPath 
                                             FROM orderdetailtb od 
                                             LEFT JOIN producttb p ON od.ProductID = p.ProductID 
                                             LEFT JOIN productimagetb pi ON od.ProductID = pi.ProductID AND pi.PrimaryImage = 1 
                                             WHERE od.OrderID = ?");
                $stmt2->bind_param("s", $order['OrderID']);
                $stmt2->execute();
                $orderDetails = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

                $orderDate = date("F j, Y g:i A", strtotime($order['OrderDate']));

                $statusColor = '';
                if ($order['Status'] === 'Order Placed') $statusColor = 'bg-blue-100 border border-blue-200 text-blue-800';
                if ($order['Status'] === 'Processing') $statusColor = 'bg-indigo-100 border border-indigo-200 text-indigo-800';
                if ($order['Status'] === 'Shipped') $statusColor = 'bg-purple-100 border border-purple-200 text-purple-800';
                if ($order['Status'] === 'Delivered') $statusColor = 'bg-green-100 border border-green-200 text-green-800';
                if ($order['Status'] === 'Cancelled') $statusColor = 'bg-red-100 border border-red-200 text-red-800';
        ?>

                <div class="bg-white mb-3 overflow-hidden">
                    <!-- Header -->
                    <div class="flex flex-wrap justify-between items-center p-3 sm:p-6 bg-white border-b">
                        <div class="flex items-center gap-3 mb-3 md:mb-0">
                            <span class="text-sm text-gray-500">Order ID:</span>
                            <span class="text-gray-700 font-light text-sm" id="order-<?php echo $order['OrderID']; ?>">
                                <?php echo $order['OrderID']; ?>
                            </span>
                            <button id="copy-btn-<?php echo $order['OrderID']; ?>"
                                onclick="copyOrderId('<?php echo $order['OrderID']; ?>')"
                                class="text-gray-400 hover:text-gray-600 flex text-sm items-center gap-1">
                                <i class="far fa-copy text-sm"></i>
                            </button>
                        </div>
                        <div class="text-sm text-gray-500 mb-3 md:mb-0">
                            <span class="font-light">Placed on:</span> <?php echo $orderDate; ?>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="font-light text-sm">Status:</span>
                            <span class="text-xs px-2 py-1 rounded-full select-none border <?php echo $statusColor; ?>">
                                <?php echo $order['Status']; ?>
                            </span>
                            <button onclick="toggleDetails('<?php echo $order['OrderID']; ?>')" class="ml-4 px-4 py-2 bg-gray-700 text-white text-sm hover:bg-gray-800 transition-colors select-none">
                                View Details
                            </button>
                        </div>
                    </div>

                    <!-- Order Details Section (Initially Hidden) -->
                    <div id="details-<?php echo $order['OrderID']; ?>" class="h-0 overflow-hidden transition-all duration-300 ease-in-out">
                        <!-- Items Section -->
                        <div class="p-3 sm:p-6 space-y-6">
                            <h2 class="text-lg font-light text-gray-700 tracking-wide">Items</h2>
                            <div class="space-y-4">
                                <?php
                                $itemsTotal = 0;
                                foreach ($orderDetails as $detail) {
                                    $itemTotal = $detail['OrderUnitQuantity'] * $detail['OrderUnitPrice'];
                                    $itemsTotal += $itemTotal;
                                    $imagePath = !empty($detail['ImageUserPath']) ? $detail['ImageUserPath'] : '../images/placeholder.png';
                                ?>
                                    <div class="flex items-center gap-5 bg-gray-50 p-5 border border-gray-100">
                                        <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="Product Image" class="w-20 h-20 object-cover select-none">
                                        <div class="flex-1">
                                            <p class="font-light text-gray-700"><?php echo htmlspecialchars($detail['Title'] ?? 'Product ' . $detail['ProductID']); ?></p>
                                            <p class="text-sm text-gray-500 mt-1">Size: <?php echo $detail['SizeID']; ?></p>
                                            <p class="text-sm text-gray-500">Quantity: <?php echo $detail['OrderUnitQuantity']; ?></p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm text-gray-500">Unit Price</p>
                                            <p class="font-light text-gray-700">$<?php echo number_format($detail['OrderUnitPrice'], 2); ?></p>
                                            <p class="text-sm text-gray-500 mt-2">Total: $<?php echo number_format($itemTotal, 2); ?></p>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>

                            <!-- Order Summary -->
                            <div class="mt-8 flex justify-end">
                                <div class="w-full md:w-1/3 bg-gray-50 p-5 border border-gray-100">
                                    <div class="flex justify-between py-2 text-sm">
                                        <span class="font-light text-gray-600">Items Total:</span>
                                        <span class="text-gray-700">$<?php echo number_format($itemsTotal, 2); ?></span>
                                    </div>
                                    <div class="flex justify-between py-2 text-sm">
                                        <span class="font-light text-gray-600">Tax:</span>
                                        <span class="text-gray-700">$<?php echo number_format($order['OrderTax'], 2); ?></span>
                                    </div>
                                    <div class="flex justify-between py-3 border-t border-gray-200 mt-2 text-base">
                                        <span class="font-light text-gray-700">Grand Total:</span>
                                        <span class="font-light text-gray-700">$<?php echo number_format($order['TotalPrice'], 2); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="p-6 bg-gray-50 border-t border-gray-100">
                            <h2 class="text-lg font-light text-gray-700 tracking-wide mb-4">Order Actions</h2>
                            <div class="flex flex-wrap gap-3 select-none">
                                <?php if ($order['Status'] != 'Delivered'): ?>
                                    <a href="../Store/return_item.php"
                                        class="px-4 py-2 bg-blue-900 text-white text-sm hover:bg-blue-950 transition-colors">
                                        <i class="ri-edit-box-line mr-2"></i>Modify Order
                                    </a>
                                    <a href="../Store/return_item.php"
                                        class="px-4 py-2 bg-red-700 text-white text-sm hover:bg-red-800 transition-colors">
                                        <i class="ri-close-circle-line mr-2"></i>Cancel Order
                                    </a>
                                <?php endif; ?>

                                <a href="<?= $order['Status'] != 'Delivered' ? '#' : '../Store/return_item.php' ?>"
                                    class="px-4 py-2 bg-orange-700 text-white text-sm hover:bg-orange-800 transition-colors <?= $order['Status'] != 'Delivered' ? 'opacity-50 pointer-events-none cursor-not-allowed' : '' ?>">
                                    <i class="ri-arrow-left-right-line mr-2"></i>Return Item
                                </a>
                            </div>
                        </div>


                        <!-- Shipping Info -->
                        <div class="p-6 bg-gray-50 border-t border-gray-100">
                            <h2 class="text-lg font-light text-gray-700 tracking-wide mb-4">Shipping Information</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
                                <div>
                                    <p class="mb-2"><span class="font-light">Name:</span> <?php echo $order['FullName']; ?></p>
                                    <p><span class="font-light">Address:</span> <?php echo $order['ShippingAddress']; ?></p>
                                </div>
                                <div>
                                    <p class="mb-2"><span class="font-light">City:</span> <?php echo $order['City']; ?>, <?php echo $order['State']; ?> <?php echo $order['ZipCode']; ?></p>
                                    <p><span class="font-light">Phone:</span> <?php echo $order['PhoneNumber']; ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Remarks -->
                        <?php if (!empty($order['Remarks'])) { ?>
                            <div class="p-6 border-t border-gray-100">
                                <h2 class="text-lg font-light text-gray-700 tracking-wide mb-3">Remarks</h2>
                                <p class="text-gray-600 text-sm font-light"><?php echo $order['Remarks']; ?></p>
                            </div>
                        <?php } ?>
                    </div>
                </div>
        <?php
            }
        } else {
            echo '<div class="bg-white py-32 text-center">';
            echo '<i class="fas fa-box-open text-4xl text-gray-300 mb-4"></i>';
            echo '<h2 class="text-xl font-light text-gray-600">No Orders Found</h2>';
            echo '<p class="text-gray-500 mt-2 font-light">No orders have been placed yet.</p>';
            echo '</div>';
        }
        ?>
    </div>

    <script>
        function copyOrderId(orderId) {
            const text = document.getElementById('order-' + orderId).textContent;
            const button = document.getElementById('copy-btn-' + orderId);

            navigator.clipboard.writeText(text).then(() => {
                // Change icon & text to "Copied" with checkmark
                button.innerHTML = '<i class="fas fa-check text-xs"></i> <span class="text-xs">Copied</span>';

                // Revert back after 2 seconds
                setTimeout(() => {
                    button.innerHTML = '<i class="far fa-copy text-sm"></i>';
                }, 2000);
            });
        }

        function toggleDetails(orderId) {
            const details = document.getElementById('details-' + orderId);
            const button = event.currentTarget;

            const allDetails = document.querySelectorAll('[id^="details-"]');
            const allButtons = document.querySelectorAll('button[onclick^="toggleDetails"]');

            allDetails.forEach(otherDetails => {
                if (otherDetails !== details && !otherDetails.classList.contains('h-0')) {
                    otherDetails.style.height = otherDetails.scrollHeight + 'px';
                    otherDetails.offsetHeight;
                    otherDetails.style.height = '0px';
                    otherDetails.classList.add('h-0');
                }
            });

            allButtons.forEach(otherButton => {
                if (otherButton !== button) {
                    otherButton.textContent = 'View Details';
                }
            });

            if (details.classList.contains('h-0')) {
                details.classList.remove('h-0');
                details.style.height = details.scrollHeight + 'px';
                button.textContent = 'Hide Details';

                setTimeout(() => {
                    if (!details.classList.contains('h-0')) {
                        details.style.height = 'auto';
                    }
                }, 300);
            } else {
                details.style.height = details.scrollHeight + 'px';
                details.offsetHeight;
                details.style.height = '0px';
                button.textContent = 'View Details';

                setTimeout(() => {
                    if (details.style.height === '0px') {
                        details.classList.add('h-0');
                    }
                }, 300);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const detailsSections = document.querySelectorAll('[id^="details-"]');
            detailsSections.forEach(section => {
                section.classList.add('h-0');
                section.style.height = '0px';
            });
        });
    </script>

    <?php
    include('../includes/moveup_btn.php');
    include('../includes/footer.php');
    ?>

    <script type="module" src="../JS/index.js"></script>
</body>

</html>