<?php
session_start();
require_once('../config/db_connection.php');

// --- auth / context ---
if (!isset($_SESSION['UserID'])) {
    http_response_code(403);
    exit('Not authorized');
}
$userId = $_SESSION['UserID'];

if (empty($_GET['order_id'])) {
    http_response_code(400);
    exit('Missing order_id');
}
$orderId = $_GET['order_id'];
$cancelAlert = null;

// Handle Stripe payment cancel first
if (isset($_GET['payment'], $_GET['order_id']) && $_GET['payment'] === 'cancel') {
    $cancelOrderId = $_GET['order_id'];

    // Retrieve original order
    $originalOrder = $_SESSION['original_order_' . $cancelOrderId] ?? null;
    $originalLines = $_SESSION['original_lines_' . $cancelOrderId] ?? [];

    if ($originalOrder) {
        mysqli_begin_transaction($connect);

        // Restore order totals and shipping info
        $qRestoreOrder = "
            UPDATE ordertb
            SET FullName=?, PhoneNumber=?, ShippingAddress=?, City=?, State=?, ZipCode=?, TotalPrice=?, AdditionalAmount=0
            WHERE OrderID=? AND UserID=?
        ";
        $stmtR = mysqli_prepare($connect, $qRestoreOrder);
        mysqli_stmt_bind_param(
            $stmtR,
            'ssssssdss',
            $originalOrder['FullName'],
            $originalOrder['PhoneNumber'],
            $originalOrder['ShippingAddress'],
            $originalOrder['City'],
            $originalOrder['State'],
            $originalOrder['ZipCode'],
            $originalOrder['TotalPrice'],
            $cancelOrderId,
            $userId
        );
        mysqli_stmt_execute($stmtR);
        mysqli_stmt_close($stmtR);

        // Restore line quantities
        foreach ($originalLines as $key => $line) {
            list($pid, $sizeId) = explode('|', $key);
            $origQty = $line['OrderUnitQuantity'];
            $qRestoreLine = "UPDATE orderdetailtb SET OrderUnitQuantity=? WHERE OrderID=? AND ProductID=? AND SizeID=?";
            $stmtL = mysqli_prepare($connect, $qRestoreLine);
            mysqli_stmt_bind_param($stmtL, 'isss', $origQty, $cancelOrderId, $pid, $sizeId);
            mysqli_stmt_execute($stmtL);
            mysqli_stmt_close($stmtL);
        }

        mysqli_commit($connect);

        // Clear session stored originals
        unset($_SESSION['original_order_' . $cancelOrderId]);
        unset($_SESSION['original_lines_' . $cancelOrderId]);

        // set success alert
        $cancelAlert = "Your payment was canceled. The order has been restored to its original state.";
    } else {
        // set fallback alert
        $cancelAlert = "Your payment was canceled. No changes were made to the order.";
    }
}

// Fetch order (ensure ownership)
$qOrder = "SELECT * FROM ordertb WHERE OrderID = ? AND UserID = ?";
$stmt = mysqli_prepare($connect, $qOrder);
mysqli_stmt_bind_param($stmt, 'ss', $orderId, $userId);
mysqli_stmt_execute($stmt);
$order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$order) {
    http_response_code(404);
    exit('Order not found');
}

// Store shipping info in variables
$fullName = htmlspecialchars($order['FullName'] ?? '');
$phoneNumber = htmlspecialchars($order['PhoneNumber'] ?? '');
$shippingAddress = htmlspecialchars($order['ShippingAddress'] ?? '');
$city = htmlspecialchars($order['City'] ?? '');
$state = htmlspecialchars($order['State'] ?? '');
$zipCode = htmlspecialchars($order['ZipCode'] ?? '');
$orderStatus = htmlspecialchars($order['Status'] ?? '');
$orderTax = number_format((float)($order['OrderTax'] ?? 0), 2);
$totalPrice = number_format((float)($order['TotalPrice'] ?? 0), 2);

// Only allow modify if Order Placed or Processing
$canModify = in_array($orderStatus, ['Order Placed', 'Processing']);

// Fetch order items (fixed duplicate image join)
$qItems = "
  SELECT od.ProductID, od.SizeID, od.OrderUnitQuantity, od.OrderUnitPrice,
         COALESCE(p.Title, CONCAT('Product ', od.ProductID)) AS Title,
         COALESCE(i.ImageUserPath, '../images/placeholder.png') AS ImageUserPath,
         p.SaleQuantity
  FROM orderdetailtb od
  LEFT JOIN producttb p ON p.ProductID = od.ProductID
  LEFT JOIN productimagetb i ON i.ProductID = od.ProductID AND i.PrimaryImage = 1
  WHERE od.OrderID = ?
";
$stmt2 = mysqli_prepare($connect, $qItems);
mysqli_stmt_bind_param($stmt2, 's', $orderId);
mysqli_stmt_execute($stmt2);
$itemsRes = mysqli_stmt_get_result($stmt2);
$orderItems = mysqli_fetch_all($itemsRes, MYSQLI_ASSOC);
mysqli_stmt_close($stmt2);

// CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_token'];
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

    <div class="max-w-6xl mx-auto p-6">
        <h1 class="text-2xl sm:text-4xl text-center text-blue-900 tracking-wide">Modify Order</h1>

        <?php if ($cancelAlert): ?>
            <div class="p-4 mt-10 rounded border-l-4 border-red-400 bg-red-50 text-red-500">
                <i class="fas fa-exclamation-circle mr-2"></i> <?= htmlspecialchars($cancelAlert) ?>
            </div>
        <?php endif; ?>

        <?php if (!$canModify): ?>
            <div class="p-4 bg-yellow-50 border-l-4 border-yellow-400 text-yellow-800 rounded mb-6">
                <strong>Notice:</strong> This order cannot be modified because its status is
                <span class="font-medium"><?= $orderStatus ?></span>.
            </div>
        <?php endif; ?>

        <form id="modifyForm" method="POST" class="space-y-6 bg-white">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
            <input type="hidden" name="order_id" value="<?= htmlspecialchars($orderId) ?>">

            <!-- Shipping Information -->
            <div class="border-b border-gray-200 pb-6">
                <h2 class="text-lg font-light text-gray-700 tracking-wide mb-4">Shipping Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Full Name</label>
                        <input name="FullName"
                            value="<?= $fullName ?>"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            <?= $canModify ? '' : 'disabled' ?> />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Phone Number</label>
                        <input name="PhoneNumber"
                            value="<?= $phoneNumber ?>"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            <?= $canModify ? '' : 'disabled' ?> />
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-600 mb-1">Address</label>
                        <input name="ShippingAddress"
                            value="<?= $shippingAddress ?>"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            <?= $canModify ? '' : 'disabled' ?> />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">City</label>
                        <input name="City"
                            value="<?= $city ?>"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            <?= $canModify ? '' : 'disabled' ?> />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">State</label>
                        <input name="State"
                            value="<?= $state ?>"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            <?= $canModify ? '' : 'disabled' ?> />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Zip Code</label>
                        <input name="ZipCode"
                            value="<?= $zipCode ?>"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            <?= $canModify ? '' : 'disabled' ?> />
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="border-b border-gray-200 pb-6">
                <h2 class="text-lg font-light text-gray-700 tracking-wide mb-4">Order Items</h2>
                <div class="flex items-center gap-3 mb-3">
                    <span class="text-sm text-gray-500">Order ID:</span>
                    <span class="text-gray-700 font-light text-sm" id="order-<?php echo $orderId; ?>">
                        <?php echo $orderId; ?>
                    </span>
                    <a id="copy-btn-<?php echo $orderId; ?>"
                        onclick="copyOrderId('<?php echo $orderId; ?>')"
                        class="text-gray-400 hover:text-gray-600 flex text-sm items-center gap-1">
                        <i class="far fa-copy text-sm"></i>
                    </a>
                </div>
                <div class="space-y-2" id="orderItemsContainer">
                    <?php foreach ($orderItems as $item): ?>
                        <div class="flex items-center gap-5 bg-gray-50 p-5 border border-gray-100 rounded">
                            <img src="<?= htmlspecialchars($item['ImageUserPath']) ?>" alt="Product Image" class="w-20 h-20 object-cover select-none">
                            <div class="flex-1">
                                <p class="font-light text-gray-800"><?= htmlspecialchars($item['Title']) ?></p>
                                <p class="text-xs text-gray-500 mt-1">Product: <?= htmlspecialchars($item['ProductID']) ?> • Size: <?= htmlspecialchars($item['SizeID']) ?></p>
                                <p class="text-xs text-gray-500">Unit Price: $<?= number_format($item['OrderUnitPrice'], 2) ?></p>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Quantity</label>
                                <input type="number" min="1" max="<?= $item['SaleQuantity'] ?>"
                                    name="qty[<?= htmlspecialchars($item['ProductID']) ?>][<?= htmlspecialchars($item['SizeID']) ?>]"
                                    value="<?= (int)$item['OrderUnitQuantity'] ?>"
                                    class="w-20 border border-gray-300 rounded px-2 py-1 text-sm outline-none"
                                    <?= $canModify ? '' : 'disabled' ?>>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-6 text-right text-sm text-gray-600" id="orderTotals">
                    <p>Tax: <span class="font-semibold">$<?= $orderTax ?></span></p>
                    <p>Grand Total: <span class="font-semibold">$<?= $totalPrice ?></span></p>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end items-center gap-4 pt-4">
                <a href="./order_history.php"
                    class="px-4 py-2 bg-gray-200 text-gray-800 text-sm hover:bg-gray-300 transition-colors select-none">Back</a>
                <?php if ($canModify): ?>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-900 text-white text-sm hover:bg-blue-950 transition-colors select-none">
                        Save Changes
                    </button>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <?php
    include('../includes/moveup_btn.php');
    include('../includes/alert.php');
    include('../includes/footer.php');
    ?>
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

        document.addEventListener('DOMContentLoaded', () => {
            <?php if (isset($_GET['payment'], $_GET['order_id']) && $_GET['payment'] === 'cancel'): ?>
                // Update totals
                const taxElem = document.querySelector('#orderTotals p:nth-child(1) span');
                const totalElem = document.querySelector('#orderTotals p:nth-child(2) span');
                if (taxElem) taxElem.textContent = '$<?= $orderTax ?>';
                if (totalElem) totalElem.textContent = '$<?= $totalPrice ?>';

                // Update all quantity inputs dynamically
                <?php foreach ($orderItems as $item): ?>
                    const inputElem = document.querySelector('input[name="qty[<?= $item['ProductID'] ?>][<?= $item['SizeID'] ?>]"]');
                    if (inputElem) inputElem.value = <?= (int)$item['OrderUnitQuantity'] ?>;
                <?php endforeach; ?>
            <?php endif; ?>
        });
    </script>

    <script type="module" src="../JS/store.js"></script>
    <script type="module" src="../JS/index.js"></script>
</body>

</html>