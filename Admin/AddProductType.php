<?php
session_start();
include('../config/dbConnection.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

$alertMessage = '';
$addProductTypeSuccess = false;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['addproducttype'])) {
    $producttype = mysqli_real_escape_string($connect, $_POST['producttype']);
    $description = mysqli_real_escape_string($connect, $_POST['description']);

    $addProductTypeQuery = "INSERT INTO producttypetb (ProductType, Description)
    VALUES ('$producttype', '$description')";

    if (mysqli_query($connect, $addProductTypeQuery)) {
        $addProductTypeSuccess = true;
    } else {
        $alertMessage = "Failed to add product type. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opulence Haven | Add Supplier</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/3.5.0/remixicon.css" integrity="sha512-HXXR0l2yMwHDrDyxJbrMD9eLvPe3z3qL3PPeozNTsiHJEENxx8DH2CxmV05iwG0dwoz5n4gQZQyYLUNt1Wdgfg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../CSS/output.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../CSS/input.css?v=<?php echo time(); ?>">
</head>

<body>
    <?php include('../includes/AdminNavbar.php'); ?>

    <!-- Main Container -->
    <div class="flex flex-col md:flex-row md:space-x-3 p-3 ml-0 md:ml-[250px]">
        <!-- Left Side Content -->
        <div class="w-full md:w-2/3 bg-white p-4">
            <h2 class="text-xl font-bold mb-4">Add Product Type Overview</h2>
            <p>Add information about suppliers to keep track of inventory, orders, and supplier details for efficient management.</p>
            <!-- Supplier Table -->
            <div class="overflow-x-auto mt-4">
                <!-- Supplier Search and Filter -->
                <form method="GET" class="my-4 flex items-center justify-between flex-col sm:flex-row gap-2 sm:gap-0">
                    <h1 class="text-lg font-semibold text-nowrap">All Product Type <span class="text-gray-400 text-sm ml-2"><?php echo $productTypeCount ?></span></h1>
                    <div class="flex items-center w-full">
                        <input type="text" name="producttype_search" class="p-2 ml-0 sm:ml-5 border border-gray-300 rounded-md w-full" placeholder="Search for product type..." value="<?php echo isset($_GET['producttype_search']) ? htmlspecialchars($_GET['producttype_search']) : ''; ?>">
                        <div class="flex items-center">
                            <label for="sort" class="ml-4 mr-2 flex items-center cursor-pointer select-none">
                                <i class="ri-filter-2-line text-xl"></i>
                                <p>Filters</p>
                            </label>
                            <!-- Search and filter form -->
                            <form method="GET" class="flex flex-col md:flex-row items-center gap-4 mb-4">
                                <select name="sort" id="sort" class="border p-2 rounded text-sm" onchange="this.form.submit()">
                                    <option value="random">All Types</option>
                                    <?php
                                    $select = "SELECT * FROM producttypetb";
                                    $query = mysqli_query($connect, $select);
                                    $count = mysqli_num_rows($query);

                                    if ($count) {
                                        for ($i = 0; $i < $count; $i++) {
                                            $row = mysqli_fetch_array($query);
                                            $producttype_id = $row['ProductTypeID'];
                                            $producttype = $row['ProductType'];
                                            $selected = ($filterProductTypeID == $producttype_id) ? 'selected' : '';

                                            echo "<option value='$producttype_id' $selected>$producttype</option>";
                                        }
                                    } else {
                                        echo "<option value='' disabled>No data yet</option>";
                                    }
                                    ?>
                                </select>
                            </form>
                        </div>
                    </div>
                </form>
                <div class="adminTable overflow-y-auto max-h-[510px]">
                    <table class="min-w-full bg-white rounded-lg">
                        <thead>
                            <tr class="bg-gray-100 text-gray-600 text-sm">
                                <th class="p-3 text-left">ID</th>
                                <th class="p-3 text-left">Type</th>
                                <th class="p-3 text-center">Description</th>
                                <th class="p-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm">
                            <?php foreach ($productTypes as $productType): ?>
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="p-3 text-left whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <input type="checkbox" class="form-checkbox h-3 w-3 border-2 text-amber-500">
                                            <span><?= htmlspecialchars($productType['ProductTypeID']) ?></span>
                                        </div>
                                    </td>
                                    <td class="p-3 text-center">
                                        <?= htmlspecialchars($productType['ProductType']) ?>
                                    </td>
                                    <td class="p-3 text-center">
                                        <?= htmlspecialchars($productType['Description']) ?>
                                    </td>
                                    <td class="p-3 text-center space-x-1 select-none">
                                        <i id="detailsBtn" class="ri-eye-line text-lg cursor-pointer"></i>
                                        <button class="text-red-500">
                                            <i class="ri-delete-bin-7-line text-xl"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Side Form -->
        <div class="w-full md:w-1/3 bg-white rounded-lg shadow p-4">
            <h2 class="text-xl font-bold mb-4">Add New Product Type</h2>
            <form class="flex flex-col space-y-4" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" id="productTypeForm">
                <!-- Product Type Input -->
                <div class="relative w-full">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Product Type Information</label>
                    <input
                        id="productTypeInput"
                        class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                        type="text"
                        name="producttype"
                        placeholder="Enter product type">
                    <small id="productTypeError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                </div>

                <!-- Description Input -->
                <div class="relative">
                    <textarea
                        id="descriptionInput"
                        class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                        type="text"
                        name="description"
                        placeholder="Enter product type description"></textarea>
                    <small id="descriptionError" class="absolute left-2 -bottom-1 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    name="addproducttype"
                    class="bg-amber-500 text-white font-semibold px-4 py-2 rounded select-none hover:bg-amber-600 transition-colors">
                    Add Product Type
                </button>
            </form>
        </div>
    </div>

    <!-- Loader -->
    <?php
    include('../includes/Alert.php');
    include('../includes/Loader.php');
    ?>

    <script src="//unpkg.com/alpinejs" defer></script>
    <script type="module" src="../JS/admin.js"></script>
</body>

</html>