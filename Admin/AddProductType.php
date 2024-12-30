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
        <div class="w-full md:w-2/3 bg-white rounded-lg shadow p-4">
            <h2 class="text-xl font-bold mb-4">Add Product Type Overview</h2>
            <p>Add information about suppliers to keep track of inventory, orders, and supplier details for efficient management.</p>
        </div>

        <!-- Right Side Form -->
        <div class="w-full md:w-1/3 bg-white rounded-lg shadow p-4">
            <h2 class="text-xl font-bold mb-4">Add Product Type Form</h2>
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