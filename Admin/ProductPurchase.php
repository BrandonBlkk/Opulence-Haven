<?php
session_start();
include('../config/dbConnection.php');
include('../includes/AutoIDFunc.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

$alertMessage = '';
$addFacilitySuccess = false;
// $facilityID = AutoID('facilitytb', 'PurchaseID', 'PUR-', 6);

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

// Add product to session cart
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["addToList"])) {
    $productID = $_POST["productID"];
    $productTitle = $_POST["producttitle"];
    $quantity = $_POST["quantity"];
    $productPrice = $_POST["productprice"];

    $productExists = false;

    // Check if product already exists in the cart
    foreach ($_SESSION['cart'] as &$existingProduct) {
        if ($existingProduct["productID"] === $productID) {
            $existingProduct["quantity"] += $quantity;
            $existingProduct["productPrice"] = $existingProduct["quantity"] * $productPrice;
            $productExists = true;
            break;
        }
    }

    // If product does not exist, add it to the session cart
    if (!$productExists) {
        $_SESSION['cart'][] = [
            "productID" => $productID,
            "productTitle" => $productTitle,
            "quantity" => $quantity,
            "productPrice" => $productPrice * $quantity,
        ];
    }
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
    <?php include('../includes/AdminNavbar.php'); ?>

    <div class="flex flex-col md:flex-row md:space-x-3 p-3 ml-0 md:ml-[250px] min-w-[350px]">
        <div class="w-full bg-white p-2">
            <h2 class="text-xl text-gray-700 font-bold mb-4">Purchase Product</h2>
            <form class="flex flex-col space-y-4" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" id="ruleForm">
                <div>
                    <label for="productID" class="block text-sm font-medium text-gray-700">Select Product</label>
                    <select name="productID" id="productID" required class="w-full p-2 border rounded mt-1 outline-none">
                        <option value="" selected>Select a product</option>
                        <?php foreach ($allProducts as $id => $details): ?>
                            <option value="<?= $id ?>"
                                data-title="<?= htmlspecialchars($details['Title']) ?>"
                                data-brand="<?= htmlspecialchars($details['Brand']) ?>"
                                data-price="<?= htmlspecialchars($details['Price']) ?>"
                                data-stock="<?= htmlspecialchars($details['Stock']) ?>"
                                data-type="<?= htmlspecialchars($details['ProductType']) ?>">
                                <?= htmlspecialchars($details['Title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="flex flex-col sm:flex-row gap-4 sm:gap-2">
                    <!-- Title -->
                    <div class="relative">
                        <label for="producttitle" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                        <input class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out" type="text" name="producttitle" id="producttitle" required placeholder="Choose product to get title">
                    </div>
                    <!-- Brand -->
                    <div class="relative flex-1">
                        <label for="productbrand" class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
                        <input class="p-2 w-full border rounded" type="text" name="productbrand" id="productbrand" disabled placeholder="Choose product to get brand">
                    </div>
                </div>

                <!-- Price -->
                <div class="relative">
                    <label for="productprice" class="block text-sm font-medium text-gray-700 mb-1">Price</label>
                    <input class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out" type="number" name="productprice" id="productprice" min="1" required placeholder="Choose product to get price">
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
                    <input class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out" type="number" name="quantity" id="quantity" min="1" required placeholder="Enter quantity">
                </div>

                <div class="flex flex-col sm:flex-row justify-end gap-4 sm:gap-2">
                    <button type="submit" name="addToList" class="bg-blue-500 text-white px-4 py-2 hover:bg-blue-600 rounded-sm">
                        Add to List
                    </button>
                </div>
            </form>

            <!-- Display Product List Before Purchase Button -->
            <table class="w-full mt-4 border-collapse border border-gray-200">
                <tr class="bg-gray-100 text-gray-600 text-sm">
                    <th class="border p-2 text-start">No</th>
                    <th class="border p-2 text-start">Product</th>
                    <th class="border p-2 text-start">Quantity</th>
                    <th class="border p-2 text-start">Price</th>
                    <th class="border p-2 text-start">Remove</th>
                </tr>
                <?php if (!empty($_SESSION['cart'])): ?>
                    <?php $count = 1; ?>
                    <?php foreach ($_SESSION['cart'] as $index => $item): ?>
                        <tr>
                            <td class="border p-2"><?= $count ?></td>
                            <td class="border p-2"><?= htmlspecialchars($item['productTitle']) ?></td>
                            <td class="border p-2"><?= htmlspecialchars($item['quantity']) ?></td>
                            <td class="border p-2">$<?= number_format($item['productPrice'], 2) ?></td>
                            <td class="border p-2 text-center">
                                <form method="post" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
                                    <input type="hidden" name="removeIndex" value="<?= $index ?>">
                                    <button type="submit" name="removeItem" class="text-red-500">Remove</button>
                                </form>
                            </td>
                        </tr>
                        <?php $count++; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center p-2">No products added.</td>
                    </tr>
                <?php endif; ?>
            </table>

            <form method="post" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
                <!-- Supplier -->
                <div class="mt-4">
                    <label for="supplierID" class="block text-sm font-medium text-gray-700">Select Supplier</label>
                    <select name="supplierID" id="supplierID" required class="w-full p-2 border rounded mt-1 outline-none">
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

                <button type="submit" class="bg-green-500 text-white px-4 py-2 mt-4 rounded w-full">
                    Complete Purchase
                </button>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('productID').addEventListener('change', function() {
            var selectedOption = this.options[this.selectedIndex];
            document.getElementById('producttitle').value = selectedOption.getAttribute('data-title') || '';
            document.getElementById('productbrand').value = selectedOption.getAttribute('data-brand') || '';
            document.getElementById('productprice').value = selectedOption.getAttribute('data-price') || '';
            document.getElementById('productstock').value = selectedOption.getAttribute('data-stock') || '';
            document.getElementById('producttype').value = selectedOption.getAttribute('data-type') || '';
        });
    </script>

    <?php include('../includes/Alert.php'); ?>
    <?php include('../includes/Loader.php'); ?>
    <script src="//unpkg.com/alpinejs" defer></script>
    <script type="module" src="../JS/admin.js"></script>
</body>

</html>