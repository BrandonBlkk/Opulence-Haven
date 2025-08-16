<?php
require_once(__DIR__ . '/../../config/db_connection.php');
include(__DIR__ . '/../admin_pagination.php');

// Construct the supplier query based on search and role filter
if ($filterSupplierID !== 'random' && !empty($searchSupplierQuery)) {
    $supplierSelect = "SELECT * FROM suppliertb 
                      WHERE ProductTypeID = '$filterSupplierID' 
                      AND (SupplierName LIKE '%$searchSupplierQuery%' 
                           OR SupplierEmail LIKE '%$searchSupplierQuery%' 
                           OR SupplierContact LIKE '%$searchSupplierQuery%' 
                           OR SupplierCompany LIKE '%$searchSupplierQuery%' 
                           OR Country LIKE '%$searchSupplierQuery%') 
                      LIMIT $rowsPerPage OFFSET $supplierOffset";
} elseif ($filterSupplierID !== 'random') {
    $supplierSelect = "SELECT * FROM suppliertb 
                      WHERE ProductTypeID = '$filterSupplierID' 
                      LIMIT $rowsPerPage OFFSET $supplierOffset";
} elseif (!empty($searchSupplierQuery)) {
    $supplierSelect = "SELECT * FROM suppliertb 
                      WHERE SupplierName LIKE '%$searchSupplierQuery%' 
                      OR SupplierEmail LIKE '%$searchSupplierQuery%' 
                      OR SupplierContact LIKE '%$searchSupplierQuery%' 
                      OR SupplierCompany LIKE '%$searchSupplierQuery%' 
                      OR Country LIKE '%$searchSupplierQuery%' 
                      LIMIT $rowsPerPage OFFSET $supplierOffset";
} else {
    $supplierSelect = "SELECT * FROM suppliertb 
                      LIMIT $rowsPerPage OFFSET $supplierOffset";
}

$supplierSelectQuery = $connect->query($supplierSelect);
$suppliers = [];

if (mysqli_num_rows($supplierSelectQuery) > 0) {
    while ($row = $supplierSelectQuery->fetch_assoc()) {
        $suppliers[] = $row;
    }
}
?>

<table class="min-w-full bg-white rounded-lg">
    <thead>
        <tr class="bg-gray-100 text-gray-600 text-sm">
            <th class="p-3 text-start">ID</th>
            <th class="p-3 text-start">Name</th>
            <th class="p-3 text-start hidden md:table-cell">Product Supplied</th>
            <th class="p-3 text-start hidden sm:table-cell">Contact</th>
            <th class="p-3 text-start hidden lg:table-cell">Company</th>
            <th class="p-3 text-start hidden lg:table-cell">Address</th>
            <th class="p-3 text-start">Actions</th>
        </tr>
    </thead>
    <tbody class="text-gray-600 text-sm">
        <?php if (!empty($suppliers)): ?>
            <?php foreach ($suppliers as $supplier): ?>
                <tr class="border-b border-gray-200 hover:bg-gray-50">
                    <td class="p-3 text-start whitespace-nowrap">
                        <div class="flex items-center gap-2 font-medium text-gray-500">
                            <input type="checkbox" class="form-checkbox h-3 w-3 border-2 text-amber-500">
                            <span><?= htmlspecialchars($supplier['SupplierID']) ?></span>
                        </div>
                    </td>
                    <td class="p-3">
                        <p class="font-bold"><?= htmlspecialchars($supplier['SupplierName']) ?></p>
                        <p><?= htmlspecialchars($supplier['SupplierEmail']) ?></p>
                    </td>
                    <td class="p-3 text-start hidden md:table-cell">
                        <?php
                        // Fetch the specific product type for the supplier
                        $productTypeID = $supplier['ProductTypeID'];
                        $productTypeQuery = "SELECT ProductType FROM producttypetb WHERE ProductTypeID = '$productTypeID'";
                        $productTypeResult = mysqli_query($connect, $productTypeQuery);

                        if ($productTypeResult && $productTypeResult->num_rows > 0) {
                            $productTypeRow = $productTypeResult->fetch_assoc();
                            echo htmlspecialchars($productTypeRow['ProductType']);
                        }
                        ?>
                    </td>
                    <td class="p-3 text-start hidden sm:table-cell">
                        <?= htmlspecialchars($supplier['SupplierContact']) ?>
                    </td>
                    <td class="p-3 text-start hidden lg:table-cell">
                        <?= htmlspecialchars($supplier['SupplierCompany']) ?>
                    </td>
                    <td class="p-3 text-start hidden lg:table-cell">
                        <p>
                            <?= htmlspecialchars($supplier['Address']) ?>,
                            <?= htmlspecialchars($supplier['City']) ?>,
                            <?= htmlspecialchars($supplier['Country']) ?>
                        </p>
                    </td>
                    <td class="p-3 text-start space-x-1 select-none">
                        <i class="details-btn ri-eye-line text-lg cursor-pointer"
                            data-supplier-id="<?= htmlspecialchars($supplier['SupplierID']) ?>"></i>
                        <button class="text-red-500">
                            <i class="delete-btn ri-delete-bin-7-line text-xl"
                                data-supplier-id="<?= htmlspecialchars($supplier['SupplierID']) ?>"></i>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" class="p-3 text-center text-gray-500 py-52">
                    No suppliers available.
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<script>
    // Function to load a specific page
    function loadSupplierPage(page) {
        const urlParams = new URLSearchParams(window.location.search);
        const searchQuery = urlParams.get('supplier_search') || '';
        const sortQuery = urlParams.get('sort') || '';

        // Update URL parameters
        urlParams.set('supplierpage', page);
        if (searchQuery) urlParams.set('supplier_search', searchQuery);
        if (sortQuery) urlParams.set('sort', sortQuery);

        const xhr = new XMLHttpRequest();
        xhr.open('GET', `../includes/admin_table_components/supplier_results.php?${urlParams.toString()}`, true);

        xhr.onload = function() {
            if (this.status === 200) {
                document.getElementById('supplierResults').innerHTML = this.responseText;

                // Also update the pagination controls
                const xhrPagination = new XMLHttpRequest();
                xhrPagination.open('GET', `../includes/admin_table_components/supplier_pagination.php?${urlParams.toString()}`, true);
                xhrPagination.onload = function() {
                    if (this.status === 200) {
                        document.getElementById('paginationContainer').innerHTML = this.responseText;
                    }
                };
                xhrPagination.send();

                window.history.pushState({}, '', `?${urlParams.toString()}`);
                window.scrollTo(0, 0);
                initializeSupplierActionButtons();
            }
        };

        xhr.send();
    }

    // Function to handle search
    function handleSupplierSearch() {
        const searchInput = document.querySelector('input[name="supplier_search"]');
        const filterSelect = document.querySelector('select[name="sort"]');

        // Reset to page 1 when searching
        loadSupplierPage(1);
    }

    // Initialize event listeners for search
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.querySelector('input[name="supplier_search"]');
        const sortSelect = document.querySelector('select[name="sort"]');

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const urlParams = new URLSearchParams(window.location.search);
                urlParams.set('supplier_search', this.value);
                window.history.pushState({}, '', `?${urlParams.toString()}`);
                handleSupplierSearch();
            });
        }

        if (sortSelect) {
            sortSelect.addEventListener('change', function() {
                const urlParams = new URLSearchParams(window.location.search);
                urlParams.set('sort', this.value);
                window.history.pushState({}, '', `?${urlParams.toString()}`);
                handleSupplierSearch();
            });
        }

        initializeSupplierActionButtons();
    });

    // Function to initialize action buttons for suppliers
    function initializeSupplierActionButtons() {
        // Function to attach event listeners to a row
        const attachEventListenersToRow = (row) => {
            // Details button
            const detailsBtn = row.querySelector('.details-btn');
            if (detailsBtn) {
                detailsBtn.addEventListener('click', function() {
                    const supplierId = this.getAttribute('data-supplier-id');
                    darkOverlay2.classList.remove('opacity-0', 'invisible');
                    darkOverlay2.classList.add('opacity-100');

                    fetch(`../Admin/add_supplier.php?action=getSupplierDetails&id=${supplierId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById('updateSupplierID').value = supplierId;
                                document.querySelector('[name="updatesuppliername"]').value = data.supplier.SupplierName;
                                document.querySelector('[name="updatecompanyName"]').value = data.supplier.SupplierCompany;
                                document.querySelector('[name="updateemail"]').value = data.supplier.SupplierEmail;
                                document.querySelector('[name="updatecontactNumber"]').value = data.supplier.SupplierContact;
                                document.querySelector('[name="updateaddress"]').value = data.supplier.Address;
                                document.querySelector('[name="updatecity"]').value = data.supplier.City;
                                document.querySelector('[name="updatestate"]').value = data.supplier.State;
                                document.querySelector('[name="updatepostalCode"]').value = data.supplier.PostalCode;
                                document.querySelector('[name="updatecountry"]').value = data.supplier.Country;
                                document.querySelector('[name="updateproductType"]').value = data.supplier.ProductTypeID;
                                updateSupplierModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                            } else {
                                console.error('Failed to load supplier details');
                            }
                        })
                        .catch(error => console.error('Fetch error:', error));
                });
            }

            // Delete button
            const deleteBtn = row.querySelector('.delete-btn');
            if (deleteBtn) {
                deleteBtn.addEventListener('click', function() {
                    const supplierId = this.getAttribute('data-supplier-id');
                    darkOverlay2.classList.remove('opacity-0', 'invisible');
                    darkOverlay2.classList.add('opacity-100');

                    fetch(`../Admin/add_supplier.php?action=getSupplierDetails&id=${supplierId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById('deleteSupplierID').value = supplierId;
                                document.getElementById('supplierDeleteName').textContent = data.supplier.SupplierName;
                                supplierConfirmDeleteModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                            } else {
                                console.error('Failed to load supplier details');
                            }
                        })
                        .catch(error => console.error('Fetch error:', error));
                });
            }
        };

        // Initialize all existing rows
        document.querySelectorAll('tbody tr').forEach(row => {
            attachEventListenersToRow(row);
        });
    }

    // Handle browser back/forward buttons
    window.addEventListener('popstate', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const searchInput = document.querySelector('input[name="supplier_search"]');
        const sortSelect = document.querySelector('select[name="sort"]');

        if (searchInput) {
            searchInput.value = urlParams.get('supplier_search') || '';
        }
        if (sortSelect) {
            sortSelect.value = urlParams.get('sort') || '';
        }
        loadSupplierPage(urlParams.get('supplierpage') || 1);
    });
</script>