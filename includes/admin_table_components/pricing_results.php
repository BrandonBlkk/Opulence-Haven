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

// Update product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $productID = mysqli_real_escape_string($connect, $_POST['product_id']);
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $markupPercentage = isset($_POST['markup_percentage']) && $_POST['markup_percentage'] !== ''
        ? (float) $_POST['markup_percentage']
        : 0.00;
    $saleQuantity = isset($_POST['sale_quantity']) && $_POST['sale_quantity'] !== ''
        ? (int) $_POST['sale_quantity']
        : 0;

    // Make sure sale quantity does not exceed stock
    $queryStock = "SELECT Stock FROM producttb WHERE ProductID = '$productID'";
    $resultStock = $connect->query($queryStock);
    $rowStock = $resultStock->fetch_assoc();
    $stock = (int) $rowStock['Stock'];
    if ($saleQuantity > $stock) {
        $saleQuantity = $stock;
    }

    // Update product table
    $query = "UPDATE producttb 
              SET IsActive = '$isActive', 
                  MarkupPercentage = '$markupPercentage', 
                  SaleQuantity = '$saleQuantity'
              WHERE ProductID = '$productID'";
    mysqli_query($connect, $query);
}

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
                <tr class="hover:bg-gray-50 transition-colors">
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
                    <td class="p-3 text-start hidden sm:table-cell">
                        <?php
                        $basePrice = $product['Price'];
                        $markup = $product['MarkupPercentage'];
                        $finalPrice = $basePrice + ($basePrice * ($markup / 100));
                        echo '$' . htmlspecialchars(number_format($finalPrice, 2));
                        ?>
                    </td>
                    <td class="p-3 text-start hidden sm:table-cell">
                        <?php
                        $profit = $basePrice * ($markup / 100);
                        echo '$' . htmlspecialchars(number_format($profit, 2));
                        ?>
                        (<?php
                            $markup = $product['MarkupPercentage'];
                            echo htmlspecialchars((floor($markup) == $markup) ? (int)$markup : number_format($markup, 2)) . '%';
                            ?>)
                    </td>
                    <!-- Stock Column -->
                    <td class="p-3 text-start">
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
                                Stock (<?= htmlspecialchars($product['Stock']) ?>)
                            </span>

                            <!-- Sale Stock (only show if > 0) -->
                            <?php if (!empty($product['SaleQuantity']) && $product['SaleQuantity'] > 0): ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 border border-blue-200 text-blue-800">
                                    Sale (<?= htmlspecialchars($product['SaleQuantity']) ?>)
                                </span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="p-3 text-start">
                        <span class="text-xs px-2 py-1 rounded-full select-none border
        <?= $product['IsActive']
                    ? 'bg-green-100 text-green-800 border-green-200'
                    : 'bg-red-100 text-red-800 border-red-200' ?>">
                            <?= htmlspecialchars($product['IsActive'] ? 'On Sale' : 'Not On Sale') ?>
                        </span>
                    </td>
                    <td class="p-3 text-start hidden lg:table-cell">
                        <?= htmlspecialchars(date('d M Y', strtotime($product['AddedDate']))) ?>
                    </td>
                    <td class="p-3 text-start space-x-1 select-none">
                        <form method="POST" action="<?= $_SERVER['PHP_SELF']; ?>" class="inline-flex items-center gap-2">
                            <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['ProductID']) ?>">

                            <!-- Sale Quantity Input -->
                            <input type="number" step="1" min="0" max="<?= $product['Stock'] ?>" name="sale_quantity"
                                value="<?= htmlspecialchars($product['SaleQuantity'] ?? 0) ?>"
                                class="w-20 border rounded p-1 text-xs text-gray-700"
                                placeholder="Sale Qty">

                            <!-- Markup Percentage Input -->
                            <input type="number" step="0.01" min="0" name="markup_percentage"
                                value="<?= htmlspecialchars($product['MarkupPercentage']) ?>"
                                class="w-16 border rounded p-1 text-xs text-gray-700"
                                placeholder="%">

                            <!-- IsActive Checkbox -->
                            <label class="switch">
                                <input type="checkbox" name="is_active" value="1"
                                    <?= $product['IsActive'] ? 'checked' : '' ?>
                                    onchange="this.form.submit()">
                                <span class="slider"></span>
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