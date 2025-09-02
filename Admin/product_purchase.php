<?php
session_start();
require_once('../config/db_connection.php');
include_once('../includes/auto_id_func.php');
require_once('../includes/auth_check.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

$alertMessage = '';
$purchaseSuccess = false;
$response = ['success' => false, 'message' => ''];
$purchaseID = uniqid("PUR_");

// Initialize session variable if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Fetch all products for JavaScript use
$allProducts = [];
$productQuery = "SELECT p.ProductID, p.Title, p.Brand, p.Price, p.Stock, p.ProductTypeID, pt.ProductType
FROM producttb p
INNER JOIN producttypetb pt ON p.ProductTypeID = pt.ProductTypeID";
$result = $connect->query($productQuery);
while ($row = $result->fetch_assoc()) {
    $allProducts[$row['ProductID']] = $row;
}

if (isset($_GET["ProductID"])) {
    $product_id = $_GET["ProductID"];
}

// Handle AJAX requests
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajax'])) {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_to_cart') {
        // Add product to session cart via AJAX
        $productID = $_POST["productID"];
        $productTitle = $_POST["producttitle"];
        $quantity = (int)$_POST["quantity"];
        $productPrice = (float)$_POST["productprice"];

        if (empty($productID)) {
            $response['message'] = "Please select a product to purchase.";
        } elseif ($quantity <= 0) {
            $response['message'] = "Choose a quantity greater than 0.";
        } else {
            $productExists = false;

            // Check if product already exists in the cart
            foreach ($_SESSION['cart'] as &$existingProduct) {
                if ($existingProduct["productID"] === $productID) {
                    $existingProduct["quantity"] += $quantity;
                    // Keep productPrice as unit price (not multiplied)
                    $existingProduct["totalPrice"] = $existingProduct["quantity"] * $existingProduct["unitPrice"];
                    $productExists = true;
                    break;
                }
            }

            // If product does not exist, add to cart
            if (!$productExists) {
                $_SESSION['cart'][] = [
                    "productID" => $productID,
                    "productTitle" => $productTitle,
                    "quantity" => $quantity,
                    "unitPrice" => $productPrice, // store unit price separately
                    "totalPrice" => $productPrice * $quantity
                ];
            }

            $response['success'] = true;
            $response['message'] = "Product added to list successfully!";
            $response['cart_count'] = count($_SESSION['cart']);
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    } elseif ($action === 'update_quantity') {
        $index = $_POST['index'];
        $quantity = (int)$_POST['quantity'];

        if (isset($_SESSION['cart'][$index])) {
            $_SESSION['cart'][$index]['quantity'] = $quantity;
            $_SESSION['cart'][$index]['totalPrice'] = $_SESSION['cart'][$index]['unitPrice'] * $quantity;

            $response['success'] = true;
            $response['message'] = "Quantity updated successfully.";
            $response['cart_count'] = count($_SESSION['cart']);
        } else {
            $response['success'] = false;
            $response['message'] = "Item not found in cart.";
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    } elseif ($action === 'remove_item') {
        // Remove product from session cart via AJAX
        $removeIndex = $_POST["removeIndex"];

        if (isset($_SESSION['cart'][$removeIndex])) {
            array_splice($_SESSION['cart'], $removeIndex, 1);
            $response['success'] = true;
            $response['message'] = "Product removed from list.";
            $response['cart_count'] = count($_SESSION['cart']);
        } else {
            $response['message'] = "Item not found in cart.";
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    } elseif ($action === 'complete_purchase') {
        // Purchase product via AJAX
        if (!isset($_POST["supplierID"]) || empty($_POST["supplierID"])) {
            $response['message'] = "Please select a supplier.";
        } else {
            $supplierID = $_POST["supplierID"];

            // Check if cart exists and has items
            if (!empty($_SESSION['cart'])) {
                $success = true;

                // Process each item in cart
                foreach ($_SESSION['cart'] as $item) {
                    $productID = $item['productID'];
                    $quantity = $item['quantity'];

                    // update stock 
                    $updateQuantity = "UPDATE producttb SET Stock = Stock + $quantity WHERE ProductID = '$productID'";
                    if (!$connect->query($updateQuantity)) {
                        $response['message'] = "Error updating stock for product: " . $item['productTitle'];
                        $success = false;
                        break;
                    }
                }

                if ($success) {
                    $adminID = $_SESSION["AdminID"];
                    $supplierID = $_POST["supplierID"];
                    $purchaseTax = 0.10; // 10% tax
                    $status = 'Pending';

                    // Calculate subtotal (sum of all unit prices)
                    $subtotal = 0;
                    foreach ($_SESSION['cart'] as $item) {
                        $subtotal += $item['unitPrice'] * $item['quantity']; // correct
                    }

                    // Calculate tax amount separately
                    $taxAmount = $subtotal * $purchaseTax;

                    // Total amount = subtotal + tax
                    $totalAmount = $subtotal + $taxAmount;

                    $purchaseQuery = "INSERT INTO purchasetb (PurchaseID, AdminID, SupplierID, TotalAmount, PurchaseTax, Status)
                VALUES ('$purchaseID', '$adminID', '$supplierID', '$totalAmount', '$purchaseTax', '$status')";

                    if ($connect->query($purchaseQuery)) {
                        // Save cart items before clearing the cart
                        $cartItems = $_SESSION['cart'];

                        // Clear cart after saving items to variable
                        unset($_SESSION['cart']);

                        foreach ($cartItems as $item) {
                            $productID = $item['productID'];
                            $quantity = $item['quantity'];
                            $unitPrice = $item['unitPrice']; // store actual unit price

                            $purchaseItemQuery = "INSERT INTO purchasedetailtb (PurchaseID, ProductID, PurchaseUnitQuantity, PurchaseUnitPrice)
VALUES ('$purchaseID', '$productID', '$quantity', '$unitPrice')";

                            if (!$connect->query($purchaseItemQuery)) {
                                $response['message'] = "Error saving purchase item: " . $item['productTitle'];
                                $success = false;
                                break;
                            }
                        }
                    }

                    if ($success) {
                        $response['success'] = true;
                        $response['message'] = "Purchase completed successfully!";
                    }
                }
            } else {
                $response['message'] = "Your cart is empty.";
            }
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }
}

// Handle cart table request
if (isset($_GET['get_cart_table'])) {
    ob_start();
    include("../includes/admin_table_components/cart_table.php");
    $cartTable = ob_get_clean();
    echo $cartTable;
    exit();
}

// Regular form submission handling (for non-JS fallback)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["addToList"])) {
    $productID = $_POST["productID"];
    $productTitle = $_POST["producttitle"];
    $quantity = (int)$_POST["quantity"];
    $productPrice = (float)$_POST["productprice"];

    if (empty($productID)) {
        $alertMessage = "Please select a product to purchase.";
    } elseif ($quantity <= 0) {
        $alertMessage = "Choose a quantity greater than 0.";
    } else {
        $productExists = false;

        // Check if product already exists in the cart
        foreach ($_SESSION['cart'] as &$existingProduct) {
            if ($existingProduct["productID"] === $productID) {
                $existingProduct["quantity"] += $quantity;
                // Keep productPrice as unit price (not multiplied)
                $existingProduct["totalPrice"] = $existingProduct["quantity"] * $existingProduct["unitPrice"];
                $productExists = true;
                break;
            }
        }

        // If product does not exist, add to cart
        if (!$productExists) {
            $_SESSION['cart'][] = [
                "productID" => $productID,
                "productTitle" => $productTitle,
                "quantity" => $quantity,
                "unitPrice" => $productPrice, // store unit price separately
                "totalPrice" => $productPrice * $quantity
            ];
        }

        // Set success message
        $alertMessage = "Product added to list successfully!";
    }
}

// Remove product from session cart (non-JS fallback)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["removeItem"])) {
    $removeIndex = $_POST["removeIndex"];

    if (isset($_SESSION['cart'][$removeIndex])) {
        array_splice($_SESSION['cart'], $removeIndex, 1);
        $alertMessage = "Product removed from list.";
    }

    // Redirect to prevent form resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opulence Haven|Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../CSS/output.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../CSS/input.css?v=<?php echo time(); ?>">
</head>

<body>
    <?php include('../includes/admin_navbar.php'); ?>

    <div class="flex flex-col md:flex-row md:space-x-3 p-3 ml-0 md:ml-[250px] min-w-[380px]">
        <div class="w-full bg-white p-2">
            <h2 class="text-xl text-gray-700 font-bold mb-4">Purchase Product</h2>
            <form class="flex flex-col space-y-4" id="addToListForm">
                <div>
                    <label for="productID" class="block text-sm font-medium text-gray-700">Select Product</label>
                    <?php
                    // Get the ProductID from the URL if it exists
                    $selectedProductID = $_GET['ProductID'] ?? null;
                    $selectedProductData = null;

                    // If a ProductID is provided, find its details in $allProducts
                    if ($selectedProductID && isset($allProducts[$selectedProductID])) {
                        $selectedProductData = $allProducts[$selectedProductID];
                    }
                    ?>

                    <select name="productID" id="productID" class="w-full p-2 border rounded mt-1 outline-none">
                        <option value="" <?= !$selectedProductID ? 'selected' : '' ?>>Select a product</option>
                        <?php foreach ($allProducts as $id => $details): ?>
                            <option value="<?= $id ?>"
                                data-title="<?= htmlspecialchars($details['Title']) ?>"
                                data-brand="<?= htmlspecialchars($details['Brand']) ?>"
                                data-price="<?= htmlspecialchars($details['Price']) ?>"
                                data-stock="<?= htmlspecialchars($details['Stock']) ?>"
                                data-type="<?= htmlspecialchars($details['ProductType']) ?>"
                                <?= ($selectedProductID == $id) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($details['Title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="flex flex-col sm:flex-row gap-4 sm:gap-2">
                    <!-- Title -->
                    <div class="relative flex-1">
                        <label for="producttitle" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                        <input class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="text" name="producttitle_display" id="producttitle" disabled placeholder="Choose product to get title">
                        <input type="hidden" name="producttitle" id="hidden_producttitle">
                    </div>

                    <!-- Brand -->
                    <div class="relative flex-1">
                        <label for="productbrand" class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
                        <input class="p-2 w-full border rounded" type="text" name="productbrand_display" id="productbrand" disabled placeholder="Choose product to get brand">
                    </div>

                    <!-- Price -->
                    <div class="relative flex-1">
                        <label for="productprice" class="block text-sm font-medium text-gray-700 mb-1">Price</label>
                        <input class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="number" name="productprice_display" id="productprice" min="1" disabled placeholder="Choose product to get price">
                        <input type="hidden" name="productprice" id="hidden_productprice">
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-4 sm:gap-2">
                    <!-- Stock -->
                    <div class="relative flex-1">
                        <label for="productstock" class="block text-sm font-medium text-gray-700 mb-1">Stock</label>
                        <input class="p-2 w-full border rounded" type="number" id="productstock" disabled placeholder="Choose product to get stock">
                    </div>
                    <!-- Type -->
                    <div class="relative flex-1">
                        <label for="producttype" class="block text-sm font-medium text-gray-700 mb-1">Product Type</label>
                        <input class="p-2 w-full border rounded" type="text" id="producttype" disabled placeholder="Choose product to get type">
                    </div>
                </div>

                <!-- Quantity -->
                <div class="relative">
                    <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                    <input class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out" type="number" name="quantity" id="quantity" min="1" placeholder="Enter quantity">
                </div>

                <div class="flex flex-col sm:flex-row justify-end gap-4 sm:gap-2">
                    <button type="button" id="addToListBtn" class="bg-blue-800 text-white px-4 py-2 hover:bg-blue-900 rounded-sm">
                        Add to List
                    </button>
                </div>
            </form>

            <!-- Cart Table Container -->
            <div id="cartTableContainer">
                <?php include("../includes/admin_table_components/cart_table.php"); ?>
            </div>

            <form id="purchaseForm">
                <!-- Supplier -->
                <div class="mt-4">
                    <label for="supplierID" class="block text-sm font-medium text-gray-700">Select Supplier</label>
                    <select name="supplierID" id="supplierID" class="w-full p-2 border rounded mt-1 outline-none" required>
                        <option value="" disabled selected>Select a supplier</option>
                        <?php
                        $supplierQuery = "SELECT s.SupplierID, s.SupplierName, p.ProductType 
                            FROM suppliertb s
                            INNER JOIN producttypetb p ON s.ProductTypeID = p.ProductTypeID";

                        $result = $connect->query($supplierQuery);

                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='{$row['SupplierID']}'>{$row['SupplierName']} ({$row['ProductType']})</option>";
                        }
                        ?>
                    </select>
                </div>

                <button type="button" id="completePurchaseBtn" class="bg-amber-500 text-white font-semibold w-full px-4 py-2 mt-4 rounded-sm select-none hover:bg-amber-600 transition-colors">
                    Complete Purchase
                </button>
            </form>
        </div>
    </div>

    <?php include('../includes/alert.php'); ?>
    <?php include('../includes/loader.php'); ?>
    <script src="//unpkg.com/alpinejs" defer></script>
    <script type="module" src="../JS/admin.js"></script>
</body>

</html>