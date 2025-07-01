<?php
include(__DIR__ . '/../../config/dbConnection.php');
include(__DIR__ . '/../AdminPagination.php');

// Construct the facility type query based on search
if ($filterSizes !== 'random' && !empty($searchSizeQuery)) {
    $productSizeSelect = "SELECT * FROM sizetb WHERE ProductID = '$filterSizes' AND Size LIKE '%$searchSizeQuery%' LIMIT $rowsPerPage OFFSET $productSizeOffset";
} elseif ($filterSizes !== 'random') {
    $productSizeSelect = "SELECT * FROM sizetb WHERE ProductID = '$filterSizes' LIMIT $rowsPerPage OFFSET $productSizeOffset";
} elseif (!empty($searchSizeQuery)) {
    $productSizeSelect = "SELECT * FROM sizetb WHERE Size LIKE '%$searchSizeQuery%' LIMIT $rowsPerPage OFFSET $productSizeOffset";
} else {
    $productSizeSelect = "SELECT * FROM sizetb LIMIT $rowsPerPage OFFSET $productSizeOffset";
}

$productSizeSelectQuery = mysqli_query($connect, $productSizeSelect);
$productSizes = [];

if (mysqli_num_rows($productSizeSelectQuery) > 0) {
    while ($row = $productSizeSelectQuery->fetch_assoc()) {
        $productSizes[] = $row;
    }
}
?>

<table class="min-w-full bg-white rounded-lg">
    <thead>
        <tr class="bg-gray-100 text-gray-600 text-sm">
            <th class="p-3 text-start">ID</th>
            <th class="p-3 text-start">Size</th>
            <th class="p-3 text-start">Price</th>
            <th class="p-3 text-start hidden sm:table-cell">Product</th>
            <th class="p-3 text-start">Actions</th>
        </tr>
    </thead>
    <tbody class="text-gray-600 text-sm">
        <?php
        $count = 1;
        ?>
        <?php if (!empty($productSizes)): ?>
            <?php foreach ($productSizes as $productSize): ?>
                <tr class="border-b border-gray-200 hover:bg-gray-50">
                    <td class="p-3 text-start whitespace-nowrap">
                        <div class="flex items-center gap-2 font-medium text-gray-500">
                            <input type="checkbox" class="form-checkbox h-3 w-3 border-2 text-amber-500">
                            <span><?= $count ?></span>
                        </div>
                    </td>
                    <td class="p-3 text-start">
                        <?= htmlspecialchars($productSize['Size']) ?>
                    </td>
                    <td class="p-3 text-start">
                        <?= htmlspecialchars($productSize['PriceModifier']) ?>
                    </td>
                    <td class="p-3 text-start hidden sm:table-cell">
                        <?php
                        // Fetch the specific product type for the supplier
                        $productID = $productSize['ProductID'];
                        $productQuery = "SELECT ProductID, Title FROM producttb WHERE ProductID = '$productID'";
                        $productResult = mysqli_query($connect, $productQuery);

                        if ($productResult && $productResult->num_rows > 0) {
                            $productRow = $productResult->fetch_assoc();
                            echo htmlspecialchars($productRow['ProductID'] . " (" . $productRow['Title'] . ")");
                        } else {
                            echo "Product not found"; // Fallback message
                        }
                        ?>
                    </td>
                    <td class="p-3 text-start space-x-1 select-none">
                        <i class="details-btn ri-eye-line text-lg cursor-pointer"
                            data-productsize-id="<?= htmlspecialchars($productSize['SizeID']) ?>"></i>
                        <button class="text-red-500">
                            <i class="delete-btn ri-delete-bin-7-line text-xl"
                                data-productsize-id="<?= htmlspecialchars($productSize['SizeID']) ?>"></i>
                        </button>
                    </td>
                </tr>
                <?php $count++; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" class="p-3 text-center text-gray-500 py-52">
                    No product sizes available.
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<script>
    // Function to load a specific page
    function loadProductSizePage(page) {
        const urlParams = new URLSearchParams(window.location.search);
        const searchQuery = urlParams.get('size_search') || '';
        const sortQuery = urlParams.get('sort') || '';

        // Update URL parameters
        urlParams.set('productsizepage', page);
        if (searchQuery) urlParams.set('size_search', searchQuery);
        if (sortQuery) urlParams.set('sort', sortQuery);

        const xhr = new XMLHttpRequest();
        xhr.open('GET', `../includes/admin_table_components/productsize_results.php?${urlParams.toString()}`, true);

        xhr.onload = function() {
            if (this.status === 200) {
                document.getElementById('productSizeResults').innerHTML = this.responseText;

                // Also update the pagination controls
                const xhrPagination = new XMLHttpRequest();
                xhrPagination.open('GET', `../includes/admin_table_components/productsize_pagination.php?${urlParams.toString()}`, true);
                xhrPagination.onload = function() {
                    if (this.status === 200) {
                        document.getElementById('paginationContainer').innerHTML = this.responseText;
                    }
                };
                xhrPagination.send();

                window.history.pushState({}, '', `?${urlParams.toString()}`);
                window.scrollTo(0, 0);
                initializeProductSizeActionButtons();
            }
        };

        xhr.send();
    }

    // Function to handle search
    function handleSizeSearch() {
        const searchInput = document.querySelector('input[name="size_search"]');
        const filterSelect = document.querySelector('select[name="sort"]');

        // Reset to page 1 when searching
        loadProductSizePage(1);
    }

    // Initialize event listeners for search
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.querySelector('input[name="size_search"]');
        const sortSelect = document.querySelector('select[name="sort"]');

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const urlParams = new URLSearchParams(window.location.search);
                urlParams.set('size_search', this.value);
                window.history.pushState({}, '', `?${urlParams.toString()}`);
                handleSizeSearch();
            });
        }

        if (sortSelect) {
            sortSelect.addEventListener('change', function() {
                const urlParams = new URLSearchParams(window.location.search);
                urlParams.set('sort', this.value);
                window.history.pushState({}, '', `?${urlParams.toString()}`);
                handleSizeSearch();
            });
        }

        initializeProductSizeActionButtons();
    });

    // Function to initialize action buttons for product sizes
    function initializeProductSizeActionButtons() {
        // Function to attach event listeners to a row
        const attachEventListenersToRow = (row) => {
            // Details button
            const detailsBtn = row.querySelector('.details-btn');
            if (detailsBtn) {
                detailsBtn.addEventListener('click', function() {
                    const productSizeId = this.getAttribute('data-productsize-id');
                    darkOverlay2.classList.remove('opacity-0', 'invisible');
                    darkOverlay2.classList.add('opacity-100');

                    fetch(`../Admin/AddSize.php?action=getProductSizeDetails&id=${productSizeId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById('updateProductSizeID').value = productSizeId;
                                document.querySelector('[name="updatesize"]').value = data.productsize.Size;
                                document.querySelector('[name="updateprice"]').value = data.productsize.PriceModifier;
                                document.querySelector('[name="updateproduct"]').value = data.productsize.ProductID;
                                updateProductSizeModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                            } else {
                                console.error('Failed to load product size details');
                            }
                        })
                        .catch(error => console.error('Fetch error:', error));
                });
            }

            // Delete button
            const deleteBtn = row.querySelector('.delete-btn');
            if (deleteBtn) {
                deleteBtn.addEventListener('click', function() {
                    const productSizeId = this.getAttribute('data-productsize-id');
                    darkOverlay2.classList.remove('opacity-0', 'invisible');
                    darkOverlay2.classList.add('opacity-100');

                    fetch(`../Admin/AddSize.php?action=getProductSizeDetails&id=${productSizeId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById('deleteProductSizeID').value = productSizeId;
                                document.getElementById('productSizeDeleteName').textContent = data.productsize.Size;
                                productSizeConfirmDeleteModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                            } else {
                                console.error('Failed to load product size details');
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
        const searchInput = document.querySelector('input[name="size_search"]');
        const sortSelect = document.querySelector('select[name="sort"]');

        if (searchInput) {
            searchInput.value = urlParams.get('size_search') || '';
        }
        if (sortSelect) {
            sortSelect.value = urlParams.get('sort') || '';
        }
        loadProductSizePage(urlParams.get('productsizepage') || 1);
    });
</script>