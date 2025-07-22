<?php
session_start();
include('../config/db_connection.php');
include('../includes/auto_id_func.php');
include('../includes/admin_pagination.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

$alertMessage = '';
$supplierID = AutoID('suppliertb', 'SupplierID', 'SP-', 6);
$response = ['success' => false, 'message' => '', 'generatedId' => $supplierID];

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

    // Check if the supplier already exists using prepared statement
    $checkQuery = "SELECT SupplierEmail, SupplierContact FROM suppliertb WHERE SupplierEmail = '$email' OR SupplierContact = '$contactNumber'";
    $count = $connect->query($checkQuery)->num_rows;

    if ($count > 0) {
        $response['message'] = 'Supplier you added is already existed.';
    } else {
        $addSupplierQuery = "INSERT INTO suppliertb (SupplierID, SupplierName, SupplierEmail, SupplierContact, SupplierCompany, Address, City, State, PostalCode, Country, ProductTypeID)
        VALUES ('$supplierID', '$suppliername', '$email', '$contactNumber', '$companyName', '$address', '$city', '$state', '$postalCode', '$country', '$productType')";

        if ($connect->query($addSupplierQuery)) {
            $response['success'] = true;
            $response['message'] = 'A new supplier has been successfully added.';
            // Keep the generated ID in the response
            $response['generatedId'] = $supplierID;
        } else {
            $response['message'] = "Failed to add supplier. Please try again.";
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Get Supplier Details
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = mysqli_real_escape_string($connect, $_GET['id']);
    $action = $_GET['action'];

    // Build query based on action
    $query = match ($action) {
        'getSupplierDetails' => "SELECT * FROM suppliertb WHERE SupplierID = '$id'",
        default => null
    };
    if ($query) {
        $supplier = $connect->query($query)->fetch_assoc();

        if ($supplier) {
            $response['success'] = true;
            $response['supplier'] = $supplier;
        } else {
            $response['success'] = true;
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Update Spplier
if (isset($_POST['editsupplier'])) {
    $supplierId = mysqli_real_escape_string($connect, $_POST['supplierid']);
    $supplierName = mysqli_real_escape_string($connect, $_POST['updatesuppliername']);
    $supplierEmail = mysqli_real_escape_string($connect, $_POST['updateemail']);
    $supplierContact = mysqli_real_escape_string($connect, $_POST['updatecontactNumber']);
    $supplierCompany = mysqli_real_escape_string($connect, $_POST['updatecompanyName']);
    $supplierAddress = mysqli_real_escape_string($connect, $_POST['updateaddress']);
    $supplierCity = mysqli_real_escape_string($connect, $_POST['updatecity']);
    $supplierState = mysqli_real_escape_string($connect, $_POST['updatestate']);
    $supplierPostalCode = mysqli_real_escape_string($connect, $_POST['updatepostalCode']);
    $supplierCountry = mysqli_real_escape_string($connect, $_POST['updatecountry']);
    $supplierProductType = mysqli_real_escape_string($connect, $_POST['updateproductType']);

    // Update query
    $updateQuery = "UPDATE suppliertb SET SupplierName = '$supplierName', SupplierEmail = '$supplierEmail', SupplierContact = '$supplierContact', 
    SupplierCompany = '$supplierCompany', Address = '$supplierAddress', City = '$supplierCity', State = '$supplierState', PostalCode = '$supplierPostalCode', 
    Country = '$supplierCountry', ProductTypeID = '$supplierProductType' 
    WHERE SupplierID = '$supplierId'";

    if (mysqli_query($connect, $updateQuery)) {
        $response['success'] = true;
        $response['message'] = 'The supplier has been successfully updated.';
        $response['generatedId'] = $supplierId;
        $response['supplierName'] = $supplierName;
        $response['supplierEmail'] = $supplierEmail;
        $response['supplierContact'] = $supplierContact;
        $response['supplierCompany'] = $supplierCompany;
        $response['supplierAddress'] = $supplierAddress;
        $response['supplierCity'] = $supplierCity;
        $response['supplierState'] = $supplierState;
        $response['supplierPostalCode'] = $supplierPostalCode;
        $response['supplierCountry'] = $supplierCountry;
        $response['supplierProductType'] = $supplierProductType;
    } else {
        $response['message'] = "Failed to update supplier. Please try again.";
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Delete Supplier
if (isset($_POST['deletesupplier'])) {
    $supplierId = mysqli_real_escape_string($connect, $_POST['supplierid']);

    // Build query based on action
    $deleteQuery = "DELETE FROM suppliertb WHERE SupplierID = '$supplierId'";

    if (mysqli_query($connect, $deleteQuery)) {
        $response['success'] = true;
        $response['generatedId'] = $supplierId;
    } else {
        $response['success'] = false;
        $response['message'] = 'Failed to delete supplier. Please try again.';
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
    <title>Opulence Haven|Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../CSS/output.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../CSS/input.css?v=<?php echo time(); ?>">
</head>

<body>
    <?php include('../includes/admin_navbar.php'); ?>

    <!-- Main Container -->
    <div class="flex flex-col md:flex-row md:space-x-3 p-3 ml-0 md:ml-[250px] min-w-[380px]">
        <!-- Left Side Content -->
        <div class="w-full bg-white p-2">
            <div class="flex justify-between items-end">
                <div>
                    <h2 class="text-xl text-gray-700 font-bold mb-4">Add Supplier Overview</h2>
                    <p>Add information about suppliers to keep track of inventory, orders, and supplier details for efficient management.</p>
                </div>
                <button id="addSupplierBtn" class="bg-amber-500 text-white font-semibold px-3 py-1 rounded select-none hover:bg-amber-600 transition-colors">
                    <i class="ri-add-line text-xl"></i>
                </button>
            </div>

            <!-- Supplier Table -->
            <div class="overflow-x-auto">
                <!-- Supplier Search and Filter -->
                <form method="GET" class="my-4 flex items-start sm:items-center justify-between flex-col sm:flex-row gap-2 sm:gap-0">
                    <h1 class="text-lg text-gray-700 text-start font-semibold text-nowrap">All Suppliers <span class="text-gray-400 text-sm ml-2"><?php echo $supplierCount ?></span></h1>
                    <div class="flex flex-col sm:flex-row items-start sm:items-center w-full gap-2 sm:gap-0">
                        <input type="text" name="supplier_search" class="p-2 ml-0 sm:ml-5 border border-gray-300 rounded-md w-full outline-none focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out" placeholder="Search for supplier..." value="<?php echo isset($_GET['supplier_search']) ? htmlspecialchars($_GET['supplier_search']) : ''; ?>">
                        <div class="flex items-center">
                            <label for="sort" class="ml-4 mr-2 flex items-center cursor-pointer select-none">
                                <i class="ri-filter-2-line text-xl"></i>
                                <p>Filters</p>
                            </label>
                            <form method="GET" class="flex flex-col md:flex-row items-center gap-4 mb-4">
                                <!-- Keep existing search parameter -->
                                <?php if (!empty($searchSupplierQuery)): ?>
                                    <input type="hidden" name="supplier_search" value="<?= htmlspecialchars($searchSupplierQuery) ?>">
                                <?php endif; ?>
                                <!-- Keep pagination parameter -->
                                <?php if (isset($_GET['supplierpage'])): ?>
                                    <input type="hidden" name="supplierpage" value="<?= (int)$_GET['supplierpage'] ?>">
                                <?php endif; ?>

                                <select name="sort" id="sort" class="border p-2 rounded text-sm outline-none">
                                    <option value="random" <?= $filterSupplierID === 'random' ? 'selected' : '' ?>>All Supplied Products</option>
                                    <?php
                                    $select = "SELECT * FROM producttypetb";
                                    $query = $connect->query($select);
                                    $count = $query->num_rows;

                                    if ($count) {
                                        while ($row = $query->fetch_assoc()) {
                                            $producttype_id = $row['ProductTypeID'];
                                            $producttype = $row['ProductType'];
                                            $selected = ($filterSupplierID == $producttype_id) ? 'selected' : '';
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

                <!-- Supplier Table -->
                <div class="tableScrollBar overflow-y-auto max-h-[495px]">
                    <div id="supplierResults">
                        <?php include '../includes/admin_table_components/supplier_results.php'; ?>
                    </div>
                </div>

                <!-- Pagination Controls -->
                <div id="paginationContainer" class="flex justify-between items-center mt-3">
                    <?php include '../includes/admin_table_components/supplier_pagination.php'; ?>
                </div>
            </div>
        </div>

        <!-- Supplier Details Modal -->
        <div id="updateSupplierModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <div class="bg-white max-w-5xl p-6 rounded-md shadow-md text-center w-full sm:max-w-[500px]">
                <h2 class="text-xl font-bold mb-4">Edit Supplier</h2>
                <form class="flex flex-col space-y-4" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" id="updateSupplierForm">
                    <div>
                        <label class="block text-sm text-start font-medium text-gray-700 mb-1">Supplier Information</label>
                        <div class="flex flex-col sm:flex-row gap-4 sm:gap-2">
                            <input type="hidden" name="supplierid" id="updateSupplierID">
                            <!-- Supplier Name Input -->
                            <div class="relative w-full">
                                <input
                                    id="updateSupplierNameInput"
                                    class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                    type="text"
                                    name="updatesuppliername"
                                    placeholder="Enter supplier's name">
                                <small id="updateSupplierNameError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                            </div>
                            <!-- Company Name Input -->
                            <div class="relative w-full">
                                <input
                                    id="updateCompanyNameInput"
                                    class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                    type="text"
                                    name="updatecompanyName"
                                    placeholder="Enter company name">
                                <small id="updateCompanyNameError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                            </div>
                        </div>
                    </div>
                    <!-- Email Input -->
                    <div class="relative">
                        <input
                            id="updateEmailInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="email"
                            name="updateemail"
                            placeholder="Enter supplier's email">
                        <small id="updateEmailError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>
                    <!-- Contact Number Input -->
                    <div class="relative">
                        <input
                            id="updateContactNumberInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="tel"
                            name="updatecontactNumber"
                            placeholder="Enter contact number">
                        <small id="updateContactNumberError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>
                    <!-- Address Input -->
                    <div class="relative">
                        <label class="block text-sm text-start font-medium text-gray-700 mb-1">Address Details</label>
                        <textarea
                            id="updateAddressInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="text"
                            name="updateaddress"
                            placeholder="Enter address"></textarea>
                        <small id="updateAddressError" class="absolute left-2 -bottom-1 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-4 sm:gap-2">
                        <!-- City Input -->
                        <div class="relative flex-1">
                            <input
                                id="updateCityInput"
                                class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                type="text"
                                name="updatecity"
                                placeholder="Enter city">
                            <small id="updateCityError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                        </div>
                        <!-- State Input -->
                        <div class="relative flex-1">
                            <input
                                id="updateStateInput"
                                class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                type="text"
                                name="updatestate"
                                placeholder="Enter state/region">
                            <small id="updateStateError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                        </div>
                    </div>
                    <!-- Postal Code Input -->
                    <div class="relative">
                        <input
                            id="updatePostalCodeInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="text"
                            name="updatepostalCode"
                            placeholder="Enter postal code">
                        <small id="updatePostalCodeError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>
                    <!-- Country Input -->
                    <div class="relative">
                        <input
                            id="updateCountryInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="text"
                            name="updatecountry"
                            placeholder="Enter country">
                        <small id="updateCountryError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>

                    <!-- Product Type -->
                    <div class="relative">
                        <label class="block text-sm text-start font-medium text-gray-700 mb-1">Product Supplied</label>
                        <select id="updateProductType" name="updateproductType" class="p-2 w-full border rounded">
                            <option value="" disabled selected>Select type of products supplied</option>
                            <?php
                            $select = "SELECT * FROM producttypetb";
                            $query = $connect->query($select);
                            $count = $query->num_rows;

                            if ($count) {
                                for ($i = 0; $i < $count; $i++) {
                                    $row = $query->fetch_assoc();
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
                    <div class="flex justify-end gap-4 select-none">
                        <div id="supplierModalCancelBtn" class="px-4 py-2 bg-gray-200 text-black hover:bg-gray-300 rounded-sm">
                            Cancel
                        </div>
                        <button
                            type="submit"
                            name="editsupplier"
                            class="bg-amber-500 text-white px-4 py-2 select-none hover:bg-amber-600 rounded-sm">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Supplier Delete Modal -->
        <div id="supplierConfirmDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <form class="bg-white max-w-5xl p-6 rounded-md shadow-md text-center" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" id="supplierDeleteForm">
                <h2 class="text-xl font-semibold text-red-600 mb-4">Confirm Supplier Deletion</h2>
                <p class="text-slate-600 mb-2">You are about to delete the following Supplier: <span id="supplierDeleteName" class="font-semibold"></span></p>
                <p class="text-sm text-gray-500 mb-4">
                    Deleting this supplier will permanently remove it from the system, including all associated data.
                </p>
                <input type="hidden" name="supplierid" id="deleteSupplierID">
                <div class="flex justify-end gap-4 select-none">
                    <div id="supplierCancelDeleteBtn" class="px-4 py-2 bg-gray-200 text-black hover:bg-gray-300 rounded-sm">
                        Cancel
                    </div>
                    <button
                        type="submit"
                        name="deletesupplier"
                        class="px-4 py-2 bg-red-600 text-white hover:bg-red-700 rounded-sm">
                        Delete
                    </button>
                </div>
            </form>
        </div>

        <!-- Add Supplier Form -->
        <div id="addSupplierModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <div class="bg-white w-full md:w-1/3 p-6 rounded-md shadow-md ">
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
                        <select name="productType" id="productType" class="p-2 w-full border rounded outline-none" required>
                            <option value="" disabled selected>Select type of products supplied</option>
                            <?php
                            $select = "SELECT * FROM producttypetb";
                            $query = $connect->query($select);
                            $count = $query->num_rows;

                            if ($count) {
                                for ($i = 0; $i < $count; $i++) {
                                    $row = $query->fetch_assoc();
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

                    <div class="flex justify-end gap-4 select-none">
                        <div id="addSupplierCancelBtn" class="px-4 py-2 text-amber-500 font-semibold hover:text-amber-600">
                            Cancel
                        </div>
                        <!-- Submit Button -->
                        <button
                            type="submit"
                            name="addsupplier"
                            class="bg-amber-500 text-white font-semibold px-4 py-2 rounded-sm select-none hover:bg-amber-600 transition-colors">
                            Add Supplier
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Loader -->
    <?php
    include('../includes/alert.php');
    include('../includes/loader.php');
    ?>

    <script src="//unpkg.com/alpinejs" defer></script>
    <script type="module" src="../JS/admin.js"></script>

    <script>

    </script>
</body>

</html>