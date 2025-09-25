<?php
require_once(__DIR__ . '/../../config/db_connection.php');
include(__DIR__ . '/../admin_pagination.php');

// Construct the product type query based on search
if (!empty($searchProductTypeQuery)) {
    $productTypeSelect = "SELECT * FROM producttypetb WHERE ProductType LIKE '%$searchProductTypeQuery%' OR Description LIKE '%$searchProductTypeQuery%' LIMIT $rowsPerPage OFFSET $productTypeOffset";
} else {
    $productTypeSelect = "SELECT * FROM producttypetb LIMIT $rowsPerPage OFFSET $productTypeOffset";
}

$productTypeSelectQuery = $connect->query($productTypeSelect);
$productTypes = [];

if (mysqli_num_rows($productTypeSelectQuery) > 0) {
    while ($row = $productTypeSelectQuery->fetch_assoc()) {
        $productTypes[] = $row;
    }
}
?>

<table class="min-w-full bg-white rounded-lg">
    <thead>
        <tr class="bg-gray-100 text-gray-600 text-sm">
            <th class="p-3 text-start">
                <input type="checkbox" id="selectAllProductTypes" class="form-checkbox h-3 w-3 border-2 text-amber-500">
            </th>
            <th class="p-3 text-start">ID</th>
            <th class="p-3 text-start">Type</th>
            <th class="p-3 text-start hidden sm:table-cell">Description</th>
            <th class="p-3 text-start">Actions</th>
        </tr>
    </thead>
    <tbody class="text-gray-600 text-sm">
        <?php if (!empty($productTypes)): ?>
            <?php foreach ($productTypes as $productType): ?>
                <tr class="border-b border-gray-200 hover:bg-gray-50">
                    <td class="p-3 text-start">
                        <input type="checkbox" class="rowProductTypeCheckbox form-checkbox h-3 w-3 border-2 text-amber-500" value="<?= htmlspecialchars($productType['ProductTypeID']) ?>">
                    </td>
                    <td class="p-3 text-start whitespace-nowrap">
                        <?= htmlspecialchars($productType['ProductTypeID']) ?>
                    </td>
                    <td class="p-3 text-start">
                        <?= htmlspecialchars($productType['ProductType']) ?>
                    </td>
                    <td class="p-3 text-start hidden sm:table-cell">
                        <?= htmlspecialchars($productType['Description']) ?>
                    </td>
                    <td class="p-3 text-start space-x-1 select-none">
                        <i class="details-btn ri-eye-line text-lg cursor-pointer"
                            data-producttype-id="<?= htmlspecialchars($productType['ProductTypeID']) ?>"></i>
                        <button class="text-red-500">
                            <i class="delete-btn ri-delete-bin-7-line text-xl"
                                data-producttype-id="<?= htmlspecialchars($productType['ProductTypeID']) ?>"></i>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" class="p-3 text-center text-gray-500 py-52">
                    No product types available.
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<script>
    // Function to load a specific page
    function loadProductTypePage(page) {
        const urlParams = new URLSearchParams(window.location.search);
        const searchQuery = urlParams.get('producttype_search') || '';

        // Update URL parameters
        urlParams.set('producttypepage', page);
        if (searchQuery) urlParams.set('producttype_search', searchQuery);

        const xhr = new XMLHttpRequest();
        xhr.open('GET', `../includes/admin_table_components/producttype_results.php?${urlParams.toString()}`, true);

        xhr.onload = function() {
            if (this.status === 200) {
                document.getElementById('productTypeResults').innerHTML = this.responseText;

                // Also update the pagination controls
                const xhrPagination = new XMLHttpRequest();
                xhrPagination.open('GET', `../includes/admin_table_components/producttype_pagination.php?${urlParams.toString()}`, true);
                xhrPagination.onload = function() {
                    if (this.status === 200) {
                        document.getElementById('paginationContainer').innerHTML = this.responseText;
                    }
                };
                xhrPagination.send();

                window.history.pushState({}, '', `?${urlParams.toString()}`);
                window.scrollTo(0, 0);
                initializeProductTypeActionButtons();
            }
        };

        xhr.send();
    }

    // Function to handle search
    function handleSearch() {
        const searchInput = document.querySelector('input[name="producttype_search"]');

        // Reset to page 1 when searching
        loadProductTypePage(1);
    }

    // Initialize event listeners for search
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.querySelector('input[name="producttype_search"]');

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const urlParams = new URLSearchParams(window.location.search);
                urlParams.set('producttype_search', this.value);
                window.history.pushState({}, '', `?${urlParams.toString()}`);
                handleSearch();
            });
        }

        initializeProductTypeActionButtons();
    });

    // Function to initialize action buttons for product types
    function initializeProductTypeActionButtons() {
        // Function to attach event listeners to a row
        const attachEventListenersToRow = (row) => {
            // Details button
            const detailsBtn = row.querySelector('.details-btn');
            if (detailsBtn) {
                detailsBtn.addEventListener('click', function() {
                    const productTypeId = this.getAttribute('data-producttype-id');
                    darkOverlay2.classList.remove('opacity-0', 'invisible');
                    darkOverlay2.classList.add('opacity-100');

                    fetch(`../Admin/add_producttype.php?action=getProductTypeDetails&id=${productTypeId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById('updateProductTypeID').value = productTypeId;
                                document.querySelector('[name="updateproducttype"]').value = data.producttype.ProductType;
                                document.querySelector('[name="updatedescription"]').value = data.producttype.Description;
                                updateProductTypeModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                            } else {
                                console.error('Failed to load product type details');
                            }
                        })
                        .catch(error => console.error('Fetch error:', error));
                });
            }

            // Delete button
            const deleteBtn = row.querySelector('.delete-btn');
            if (deleteBtn) {
                deleteBtn.addEventListener('click', function() {
                    const productTypeId = this.getAttribute('data-producttype-id');
                    darkOverlay2.classList.remove('opacity-0', 'invisible');
                    darkOverlay2.classList.add('opacity-100');

                    fetch(`../Admin/add_producttype.php?action=getProductTypeDetails&id=${productTypeId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById('deleteProductTypeID').value = productTypeId;
                                document.getElementById('productTypeDeleteName').textContent = data.producttype.ProductType;
                                productTypeConfirmDeleteModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                            } else {
                                console.error('Failed to load product type details');
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
        const searchInput = document.querySelector('input[name="producttype_search"]');
        if (searchInput) {
            searchInput.value = urlParams.get('producttype_search') || '';
        }
        loadProductTypePage(urlParams.get('producttypepage') || 1);
    });
</script>