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
        <div class="w-full md:w-2/3 bg-white p-2">
            <h2 class="text-xl font-bold mb-4">Add Product Overview</h2>
            <p>Add product information to monitor inventory, track orders, and manage product details for efficient operations.</p>
        </div>

        <!-- Right Side Form -->
        <div class="w-full md:w-1/3 bg-white rounded-lg shadow p-2">
            <h2 class="text-xl font-bold mb-4">Add New Product</h2>
            <form class="flex flex-col space-y-4" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" enctype="multipart/form-data" id="supplierForm">
                <!-- Product Title Input -->
                <div class="relative w-full">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Product Information</label>
                    <input
                        id="productTitleInput"
                        class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                        type="text"
                        name="productTitle"
                        placeholder="Enter product title">
                    <small id="productTitleError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-100 transition-all duration-200 select-none">Product title is required</small>
                </div>

                <!-- Description -->
                <div class="relative">
                    <textarea
                        id="descriptionInput"
                        class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                        name="description"
                        placeholder="Enter product description"></textarea>
                    <small id="descriptionError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-100 transition-all duration-200 select-none">Description is required</small>
                </div>

                <!-- Specification -->
                <div class="relative">
                    <textarea
                        id="specificationInput"
                        class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                        name="specification"
                        placeholder="Enter product specification"></textarea>
                    <small id="specificationError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-100 transition-all duration-200 select-none"></small>
                </div>
                <!-- Information -->
                <div class="relative">
                    <textarea
                        id="informationInput"
                        class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                        name="information"
                        placeholder="Enter product information"></textarea>
                    <small id="informationError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-100 transition-all duration-200 select-none"></small>
                </div>

                <!-- Image Uploads -->
                <div class="relative">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Product Images</label>
                    <input type="file" name="img1" class="mb-2">
                    <input type="file" name="img2" class="mb-2">
                    <input type="file" name="img3">
                </div>

                <div class="flex flex-col sm:flex-row gap-4 sm:gap-2">
                    <!-- Price -->
                    <div class="relative w-full">
                        <input
                            id="priceInput"
                            type="number"
                            step="0.01"
                            name="price"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            placeholder="Enter product price">
                        <small id="priceError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-100 transition-all duration-200 select-none">Priceis required</small>
                    </div>

                    <!-- Discount Price -->
                    <div class="relative w-full">
                        <input
                            id="discountPriceInput"
                            type="number"
                            step="0.01"
                            name="discountPrice"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            placeholder="Enter discount price">
                    </div>
                </div>

                <!-- Product Size -->
                <div class="relative w-full">
                    <input
                        type="text"
                        name="productSize"
                        class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                        placeholder="Enter product size">
                </div>

                <!-- Stock -->
                <div class="relative w-full">
                    <input
                        type="number"
                        name="stock"
                        class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                        placeholder="Enter stock quantity">
                </div>

                <!-- Product Type -->
                <div class="relative">
                    <select name="productType" id="productType" class="p-2 w-full border rounded">
                        <option value="" disabled selected>Select type of products</option>
                        <?php
                        $select = "SELECT * FROM producttypetb";
                        $query = mysqli_query($connect, $select);
                        $count = mysqli_num_rows($query);

                        if ($count) {
                            for ($i = 0; $i < $count; $i++) {
                                $row = mysqli_fetch_array($query);
                                $product_type_id = $row['ProductTypeID'];
                                $product_type = $row['ProductType'];

                                echo "<option value= '$product_type_id'>$product_type</option>";
                            }
                        } else {
                            echo "<option value='' disabled>No data yet</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    name="submit"
                    class="bg-amber-500 text-white font-semibold px-4 py-2 rounded select-none hover:bg-amber-600 transition-colors">
                    Add Product
                </button>
            </form>
        </div>
    </div>

    <script src="//unpkg.com/alpinejs" defer></script>
    <script type="module" src="../JS/admin.js"></script>
</body>

</html>