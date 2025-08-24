<?php
require_once(__DIR__ . '/../../config/db_connection.php');
include(__DIR__ . '/../admin_pagination.php');

// Construct the product query based on search and product type filter
if ($filterProductID !== 'random' && !empty($searchProductQuery)) {
    $productSelect = "SELECT * FROM producttb WHERE ProductTypeID = '$filterProductID' AND (Title LIKE '%$searchProductQuery%' OR Description LIKE '%$searchProductQuery%' OR Specification LIKE '%$searchProductQuery%' OR Information LIKE '%$searchProductQuery%' OR Brand LIKE '%$searchProductQuery%') LIMIT $rowsPerPage OFFSET $productOffset";
} elseif ($filterProductID !== 'random') {
    $productSelect = "SELECT * FROM producttb WHERE ProductTypeID = '$filterProductID' LIMIT $rowsPerPage OFFSET $productOffset";
} elseif (!empty($searchProductQuery)) {
    $productSelect = "SELECT * FROM producttb WHERE Title LIKE '%$searchProductQuery%' OR Description LIKE '%$searchProductQuery%' OR Specification LIKE '%$searchProductQuery%' OR Information LIKE '%$searchProductQuery%' OR Brand LIKE '%$searchProductQuery%' LIMIT $rowsPerPage OFFSET $productOffset";
} else {
    $productSelect = "SELECT * FROM producttb LIMIT $rowsPerPage OFFSET $productOffset";
}

$productSelectQuery = $connect->query($productSelect);
$products = [];

if (mysqli_num_rows($productSelectQuery) > 0) {
    while ($row = $productSelectQuery->fetch_assoc()) {
        $products[] = $row;
    }
}
?>

<table class="min-w-full bg-white rounded-lg">
    <thead>
        <tr class="bg-gray-100 text-gray-600 text-sm">
            <th class="p-3 text-start">ID</th>
            <th class="p-3 text-start">Title</th>
            <th class="p-3 text-start hidden sm:table-cell">Price</th>
            <th class="p-3 text-start">Stock</th>
            <th class="p-3 text-start hidden lg:table-cell">Added Date</th>
            <th class="p-3 text-start">Actions</th>
        </tr>
    </thead>
    <tbody class="text-gray-600 text-sm">
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $product): ?>
                <?php
                $isLowStock = $product['Stock'] < 10;
                $isCriticalStock = $product['Stock'] < 3;
                $isOutOfStock = $product['Stock'] == 0;
                ?>
                <tr class="hover:bg-gray-50 transition-colors 
                                   <?= $isCriticalStock ? 'bg-red-50' : ($isLowStock ? 'bg-yellow-50' : '') ?>">
                    <td class="p-3 text-start whitespace-nowrap">
                        <div class="flex items-center gap-2 font-medium text-gray-500">
                            <input type="checkbox" class="form-checkbox h-3 w-3 border-2 text-amber-500">
                            <span><?= htmlspecialchars($product['ProductID']) ?></span>
                        </div>
                    </td>
                    <td class="p-3 text-start">
                        <?= htmlspecialchars($product['Title']) ?>
                    </td>
                    <td class="p-3 text-start hidden sm:table-cell">
                        $<?= htmlspecialchars(number_format($product['Price'], 2)) ?>
                    </td>
                    <td class="p-4 text-start select-none">
                        <div class="flex items-center gap-2">
                            <?php if ($isOutOfStock): ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 border-red-200 text-red-800">
                                    Out of Stock
                                </span>
                            <?php elseif ($isCriticalStock): ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 border-red-200 text-red-800">
                                    Critical (<?= $product['Stock'] ?> left)
                                </span>
                            <?php elseif ($isLowStock): ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 border-yellow-200 text-yellow-800">
                                    Low (<?= $product['Stock'] ?> left)
                                </span>
                            <?php else: ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 border-green-200 text-green-800">
                                    In Stock (<?= $product['Stock'] ?>)
                                </span>
                            <?php endif; ?>

                            <?php if ($isLowStock): ?>
                                <a href="product_purchase.php?ProductID=<?= $product['ProductID'] ?>" class="text-xs text-blue-600 hover:text-blue-800 hover:underline">
                                    Reorder
                                </a>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="p-3 text-start hidden lg:table-cell">
                        <?= htmlspecialchars(date('d M Y', strtotime($product['AddedDate']))) ?>
                    </td>
                    <td class="p-3 text-start space-x-1 select-none">
                        <a href="product_purchase.php?ProductID=<?= $product['ProductID'] ?>" class="text-xs text-amber-600">
                            <i class="ri-store-line text-lg cursor-pointer"></i>
                        </a>
                        <i class="details-btn ri-eye-line text-lg cursor-pointer"
                            data-product-id="<?= htmlspecialchars($product['ProductID']) ?>"></i>
                        <button class="text-red-500">
                            <i class="delete-btn ri-delete-bin-7-line text-xl"
                                data-product-id="<?= htmlspecialchars($product['ProductID']) ?>"></i>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" class="p-3 text-center text-gray-500 py-52">
                    No products available.
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<script>
    // Function to load a specific page
    function loadProductPage(page) {
        const urlParams = new URLSearchParams(window.location.search);
        const searchQuery = urlParams.get('product_search') || '';
        const sortType = urlParams.get('sort') || 'random';

        // Update URL parameters
        urlParams.set('productpage', page);
        if (searchQuery) urlParams.set('product_search', searchQuery);
        if (sortType !== 'random') urlParams.set('sort', sortType);

        const xhr = new XMLHttpRequest();
        xhr.open('GET', `../includes/admin_table_components/product_results.php?${urlParams.toString()}`, true);

        xhr.onload = function() {
            if (this.status === 200) {
                document.getElementById('productResults').innerHTML = this.responseText;

                // Also update the pagination controls
                const xhrPagination = new XMLHttpRequest();
                xhrPagination.open('GET', `../includes/admin_table_components/product_pagination.php?${urlParams.toString()}`, true);
                xhrPagination.onload = function() {
                    if (this.status === 200) {
                        document.getElementById('paginationContainer').innerHTML = this.responseText;
                    }
                };
                xhrPagination.send();

                window.history.pushState({}, '', `?${urlParams.toString()}`);
                window.scrollTo(0, 0);
                initializeProductActionButtons();
            }
        };

        xhr.send();
    }

    // Function to handle search and filter
    function handleSearchFilter() {
        const searchInput = document.querySelector('input[name="product_search"]');
        const filterSelect = document.querySelector('select[name="sort"]');

        // Reset to page 1 when searching or filtering
        loadProductPage(1);
    }

    // Initialize event listeners for search and filter
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.querySelector('input[name="product_search"]');
        const filterSelect = document.querySelector('select[name="sort"]');

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const urlParams = new URLSearchParams(window.location.search);
                urlParams.set('product_search', this.value);
                window.history.pushState({}, '', `?${urlParams.toString()}`);
                handleSearchFilter();
            });
        }

        if (filterSelect) {
            filterSelect.addEventListener('change', function() {
                const urlParams = new URLSearchParams(window.location.search);
                urlParams.set('sort', this.value);
                window.history.pushState({}, '', `?${urlParams.toString()}`);
                handleSearchFilter();
            });
        }

        initializeProductActionButtons();
    });

    // Function to initialize action buttons for products
    function initializeProductActionButtons() {
        // Function to attach event listeners to a row
        const attachEventListenersToRow = (row) => {
            // Details button
            const detailsBtn = row.querySelector('.details-btn');
            if (detailsBtn) {
                detailsBtn.addEventListener('click', function() {
                    const productId = this.getAttribute('data-product-id');
                    darkOverlay2.classList.remove('opacity-0', 'invisible');
                    darkOverlay2.classList.add('opacity-100');

                    fetch(`../Admin/AddProduct.php?action=getProductDetails&id=${productId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById('updateProductID').value = productId;
                                document.querySelector('[name="updateproductTitle"]').value = data.product.Title;
                                document.querySelector('[name="updatebrand"]').value = data.product.Brand;
                                document.querySelector('[name="updatedescription"]').value = data.product.Description;
                                document.querySelector('[name="updatespecification"]').value = data.product.Specification;
                                document.querySelector('[name="updateinformation"]').value = data.product.Information;
                                document.querySelector('[name="updatedelivery"]').value = data.product.DeliveryInfo;
                                document.querySelector('[name="updateprice"]').value = data.product.Price;
                                document.querySelector('[name="updatediscountPrice"]').value = data.product.DiscountPrice;
                                document.querySelector('[name="updatesellingfast"]').value = data.product.SellingFast;
                                document.querySelector('[name="updateproductType"]').value = data.product.ProductTypeID;
                                updateProductModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                            } else {
                                console.error('Failed to load product details');
                            }
                        })
                        .catch(error => console.error('Fetch error:', error));
                });
            }

            // Delete button
            const deleteBtn = row.querySelector('.delete-btn');
            if (deleteBtn) {
                deleteBtn.addEventListener('click', function() {
                    const productId = this.getAttribute('data-product-id');
                    darkOverlay2.classList.remove('opacity-0', 'invisible');
                    darkOverlay2.classList.add('opacity-100');

                    fetch(`../Admin/AddProduct.php?action=getProductDetails&id=${productId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById('deleteProductID').value = productId;
                                document.getElementById('productDeleteName').textContent = data.product.Title;
                                productConfirmDeleteModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                            } else {
                                console.error('Failed to load product details');
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
        const searchInput = document.querySelector('input[name="product_search"]');
        const filterSelect = document.querySelector('select[name="sort"]');

        if (searchInput) searchInput.value = urlParams.get('product_search') || '';
        if (filterSelect) filterSelect.value = urlParams.get('sort') || 'random';
        loadProductPage(urlParams.get('productpage') || 1);
    });
</script>