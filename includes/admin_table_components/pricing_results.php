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

// Update product - This will now be handled via AJAX
?>

<table class="min-w-full bg-white rounded-lg">
    <thead>
        <tr class="bg-gray-100 text-gray-600 text-sm">
            <th class="p-3 text-start">ID</th>
            <th class="p-3 text-start">Product</th>
            <th class="p-3 text-start hidden sm:table-cell">Supplier Price</th>
            <th class="p-3 text-start">Selling Price</th>
            <th class="p-3 text-start">Profit/Unit</th>
            <th class="p-3 text-start">Stock</th>
            <th class="p-3 text-start">Status</th>
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
                <tr class="hover:bg-gray-50 transition-colors" id="product-row-<?= htmlspecialchars($product['ProductID']) ?>">
                    <td class="p-3 text-start whitespace-nowrap">
                        <div class="flex items-center gap-2 font-medium text-gray-500">
                            #
                            <span><?= htmlspecialchars($product['ProductID']) ?></span>
                        </div>
                    </td>
                    <td class="p-3 text-start">
                        <?= htmlspecialchars($product['Title']) ?>
                    </td>
                    <td class="p-3 text-start hidden sm:table-cell">
                        $<?= htmlspecialchars(number_format($product['Price'], 2)) ?>
                    </td>
                    <td class="p-3 text-start hidden sm:table-cell" id="selling-price-<?= htmlspecialchars($product['ProductID']) ?>">
                        <?php
                        $basePrice = $product['Price'];
                        $markup = $product['MarkupPercentage'];
                        $finalPrice = $basePrice + ($basePrice * ($markup / 100));
                        echo '$' . htmlspecialchars(number_format($finalPrice, 2));
                        ?>
                    </td>
                    <td class="p-3 text-start hidden sm:table-cell" id="profit-unit-<?= htmlspecialchars($product['ProductID']) ?>">
                        <?php
                        $profit = $basePrice * ($markup / 100);
                        echo '$' . htmlspecialchars(number_format($profit, 2));
                        ?>
                        (<span id="markup-percentage-<?= htmlspecialchars($product['ProductID']) ?>"><?php
                                                                                                        $markup = $product['MarkupPercentage'];
                                                                                                        echo htmlspecialchars((floor($markup) == $markup) ? (int)$markup : number_format($markup, 2)) . '%';
                                                                                                        ?></span>)
                    </td>
                    <!-- Stock Column -->
                    <td class="p-3 text-start select-none">
                        <div class="flex flex-col gap-1">
                            <!-- Main Stock -->
                            <?php
                            // Determine stock badge classes
                            if ($isOutOfStock) {
                                $stockClass = 'bg-red-100 border border-red-200 text-red-800';
                            } elseif ($isCriticalStock) {
                                $stockClass = 'bg-yellow-100 border border-yellow-200 text-yellow-800';
                            } else {
                                $stockClass = 'bg-green-100 border border-green-200 text-green-800';
                            }
                            ?>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $stockClass ?>">
                                Stock (<span id="available-stock-<?= htmlspecialchars($product['ProductID']) ?>"><?= htmlspecialchars($product['Stock'] - $product['SaleQuantity']) ?></span>)
                            </span>

                            <!-- Sale Stock (only show if > 0) -->
                            <?php if (!empty($product['SaleQuantity']) && $product['SaleQuantity'] > 0): ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 border border-blue-200 text-blue-800">
                                    Sale (<span id="sale-quantity-<?= htmlspecialchars($product['ProductID']) ?>"><?= htmlspecialchars($product['SaleQuantity']) ?></span>)
                                </span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="p-3 text-start">
                        <span class="text-xs px-2 py-1 rounded-full select-none border
        <?= $product['IsActive']
                    ? 'bg-green-100 text-green-800 border-green-200'
                    : 'bg-red-100 text-red-800 border-red-200' ?>" id="status-badge-<?= htmlspecialchars($product['ProductID']) ?>">
                            <?= htmlspecialchars($product['IsActive'] ? 'On Sale' : 'Not On Sale') ?>
                        </span>
                    </td>
                    <td class="p-3 text-start hidden lg:table-cell">
                        <?= htmlspecialchars(date('d M Y', strtotime($product['AddedDate']))) ?>
                    </td>
                    <td class="p-3 text-start select-none">
                        <form method="POST" class="inline-flex items-center gap-4 product-update-form" data-product-id="<?= htmlspecialchars($product['ProductID']) ?>">
                            <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['ProductID']) ?>">

                            <!-- Sale Quantity Input -->
                            <div class="flex flex-col items-start">
                                <label for="sale-quantity-<?= $product['ProductID'] ?>" class="text-xs text-gray-600">Qty</label>
                                <input id="sale-quantity-<?= $product['ProductID'] ?>" type="number" step="1" min="0" max="<?= $product['Stock'] ?>" name="sale_quantity"
                                    value="<?= htmlspecialchars($product['SaleQuantity'] ?? 0) ?>"
                                    class="w-20 border rounded p-1 text-xs text-gray-700 sale-quantity-input"
                                    placeholder="Sale Qty">
                            </div>

                            <!-- Markup Percentage Input -->
                            <div class="flex flex-col items-start">
                                <label for="markup-<?= $product['ProductID'] ?>" class="text-xs text-gray-600">Markup %</label>
                                <input id="markup-<?= $product['ProductID'] ?>" type="number" step="0.01" min="0" name="markup_percentage"
                                    value="<?= htmlspecialchars($product['MarkupPercentage']) ?>"
                                    class="w-16 border rounded p-1 text-xs text-gray-700 markup-input"
                                    placeholder="%">
                            </div>

                            <!-- Toggle Switch for Product Active Status -->
                            <label class="relative inline-block w-9 h-5">
                                <input type="checkbox" name="is_active" value="1"
                                    <?= $product['IsActive'] ? 'checked' : '' ?>
                                    class="sr-only peer status-toggle">
                                <span class="absolute inset-0 rounded-full bg-gray-300 peer-checked:bg-green-500 transition-colors duration-300"></span>
                                <span class="absolute left-0.5 top-0.5 w-4 h-4 bg-white rounded-full transition-transform duration-300 peer-checked:translate-x-4"></span>
                            </label>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="10" class="p-3 text-center text-gray-500 py-52">
                    No products available.
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<script>
    // Function to handle search and filter
    function handleSearchFilter() {
        const searchInput = document.querySelector('input[name="product_search"]');
        const filterSelect = document.querySelector('select[name="sort"]');

        // Reset to page 1 when searching or filtering
        loadProductPage(1);
    }

    // Initialize product update forms
    function initializeProductUpdateForms() {
        // Handle sale quantity input changes
        document.querySelectorAll('.sale-quantity-input').forEach(input => {
            input.addEventListener('change', function() {
                updateProduct(this);
            });
        });

        // Handle markup input changes
        document.querySelectorAll('.markup-input').forEach(input => {
            input.addEventListener('change', function() {
                updateProduct(this);
            });
        });

        // Handle status toggle changes
        document.querySelectorAll('.status-toggle').forEach(toggle => {
            toggle.addEventListener('change', function() {
                updateProduct(this);
            });
        });
    }

    // Update product via AJAX
    function updateProduct(element) {
        const form = element.closest('.product-update-form');
        const productId = form.dataset.productId;
        const formData = new FormData(form);

        // Show loading state
        const row = document.getElementById(`product-row-${productId}`);

        fetch('../Admin/admin_update_product.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the UI with the new values
                    document.getElementById(`selling-price-${productId}`).textContent = `$${data.finalPrice}`;
                    document.getElementById(`profit-unit-${productId}`).innerHTML = `$${data.profit} (<span id="markup-percentage-${productId}">${data.markupPercentage}%</span>)`;
                    document.getElementById(`available-stock-${productId}`).textContent = data.availableStock;

                    if (document.getElementById(`sale-quantity-${productId}`)) {
                        document.getElementById(`sale-quantity-${productId}`).textContent = data.saleQuantity;
                    }

                    // Update status badge
                    const statusBadge = document.getElementById(`status-badge-${productId}`);
                    if (data.isActive) {
                        statusBadge.className = 'text-xs px-2 py-1 rounded-full select-none border bg-green-100 text-green-800 border-green-200';
                        statusBadge.textContent = 'On Sale';
                    } else {
                        statusBadge.className = 'text-xs px-2 py-1 rounded-full select-none border bg-red-100 text-red-800 border-red-200';
                        statusBadge.textContent = 'Not On Sale';
                    }

                    // Show success message
                    showNotification('Product updated successfully', 'success');
                } else {
                    showNotification('Error updating product: ' + data.message, 'error');
                    // Revert the form values if there was an error
                    form.reset();
                }
            })
            .catch(error => {
                showNotification('Network error: ' + error, 'error');
                // Revert the form values if there was an error
                form.reset();
            })
    }

    // Initialize event listeners for search and filter
    document.addEventListener('DOMContentLoaded', function() {
        initializeProductActionButtons();
        initializeProductUpdateForms();
    });

    // Function to initialize action buttons for products
    function initializeProductActionButtons() {
        // Function to attach event listeners to a row
        const attachEventListenersToRow = (row) => {};

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