<?php
session_start();
include('../config/dbConnection.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

$alertMessage = '';
$addSupplierSuccess = false;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['addsupplier'])) {
    $suppliername = mysqli_real_escape_string($connect, $_POST['suppliername']);
    $companyName = mysqli_real_escape_string($connect, $_POST['companyName']);
    $email = mysqli_real_escape_string($connect, $_POST['email']);
    $contactNumber = mysqli_real_escape_string($connect, $_POST['contactNumber']);
    $address = mysqli_real_escape_string($connect, $_POST['address']);
    $city = mysqli_real_escape_string($connect, $_POST['city']);
    $state = mysqli_real_escape_string($connect, $_POST['state']);
    $postalCode = mysqli_real_escape_string($connect, $_POST['postalCode']);
    $country = mysqli_real_escape_string($connect, $_POST['country']);
    $productType = mysqli_real_escape_string($connect, $_POST['productType']);

    $addSupplierQuery = "INSERT INTO suppliertb (SupplierName, SupplierEmail, SupplierContact, SupplierCompany, Address, City, State, PostalCode, Country, ProductTypeID)
    VALUES ('$suppliername', '$email', '$contactNumber', '$companyName', '$address', '$city', '$state', '$postalCode', '$country', '$productType')";

    if (mysqli_query($connect, $addSupplierQuery)) {
        $addSupplierSuccess = true;
    } else {
        $alertMessage = "Failed to add supplier. Please try again.";
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
            <h2 class="text-xl font-bold mb-4">Add Supplier Overview</h2>
            <p>Add information about suppliers to keep track of inventory, orders, and supplier details for efficient management.</p>
        </div>

        <!-- Right Side Form -->
        <div class="w-full md:w-1/3 bg-white rounded-lg shadow p-4">
            <h2 class="text-xl font-bold mb-4">Add New Supplier</h2>
            <form class="flex flex-col space-y-4" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" id="supplierForm">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Supplier Information</label>
                    <div class="flex flex-col sm:flex-row gap-4 sm:gap-2">
                        <!-- Supplier Name Input -->
                        <div class="relative w-full">
                            <input
                                id="supplierNameInput"
                                class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                type="text"
                                name="suppliername"
                                placeholder="Enter supplier's name">
                            <small id="supplierNameError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                        </div>

                        <!-- Company Name Input -->
                        <div class="relative w-full">
                            <input
                                id="companyNameInput"
                                class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                type="text"
                                name="companyName"
                                placeholder="Enter company name">
                            <small id="companyNameError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                        </div>
                    </div>
                </div>

                <!-- Email Input -->
                <div class="relative">
                    <input
                        id="emailInput"
                        class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                        type="email"
                        name="email"
                        placeholder="Enter supplier's email">
                    <small id="emailError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                </div>

                <!-- Contact Number Input -->
                <div class="relative">
                    <input
                        id="contactNumberInput"
                        class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                        type="tel"
                        name="contactNumber"
                        placeholder="Enter contact number">
                    <small id="contactNumberError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                </div>

                <!-- Address Input -->
                <div class="relative">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address Details</label>
                    <textarea
                        id="addressInput"
                        class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                        type="text"
                        name="address"
                        placeholder="Enter address"></textarea>
                    <small id="addressError" class="absolute left-2 -bottom-1 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                </div>
                <div class="flex flex-col sm:flex-row gap-4 sm:gap-2">
                    <!-- City Input -->
                    <div class="relative">
                        <input
                            id="cityInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="text"
                            name="city"
                            placeholder="Enter city">
                        <small id="cityError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>
                    <!-- State Input -->
                    <div class="relative">
                        <input
                            id="stateInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="text"
                            name="state"
                            placeholder="Enter state/region">
                        <small id="stateError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>
                </div>
                <!-- Postal Code Input -->
                <div class="relative">
                    <input
                        id="postalCodeInput"
                        class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                        type="text"
                        name="postalCode"
                        placeholder="Enter postal code">
                    <small id="postalCodeError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                </div>
                <!-- Country Input -->
                <div class="relative">
                    <input
                        id="countryInput"
                        class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                        type="text"
                        name="country"
                        placeholder="Enter country">
                    <small id="countryError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                </div>

                <!-- Product Type -->
                <div class="relative">
                    <select name="productType" id="productType" class="p-2 w-full border rounded">
                        <option value="" disabled selected>Select type of products supplied</option>
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
                    name="addsupplier"
                    class="bg-amber-500 text-white font-semibold px-4 py-2 rounded select-none hover:bg-amber-600 transition-colors">
                    Add Supplier
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