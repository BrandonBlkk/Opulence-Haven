<?php
require_once(__DIR__ . '/../../config/db_connection.php');
include(__DIR__ . '/../admin_pagination.php');

// Construct the product type query based on search
if (!empty($searchPurchaseQuery)) {
    $purchaseSelect = "SELECT * FROM purchasetb WHERE ProductType LIKE '%$searchPurchaseQuery%' OR Description LIKE '%$searchPurchaseQuery%' AND Status = 'Confirmed' Order By PurchaseDate DESC LIMIT $rowsPerPage OFFSET $purchaseOffset";
} else {
    $purchaseSelect = "SELECT * FROM purchasetb WHERE Status = 'Confirmed' Order By PurchaseDate DESC LIMIT $rowsPerPage OFFSET $purchaseOffset";
}

$purchaseSelectQuery = $connect->query($purchaseSelect);
$purchases = [];

if (mysqli_num_rows($purchaseSelectQuery) > 0) {
    while ($row = $purchaseSelectQuery->fetch_assoc()) {
        $purchases[] = $row;
    }
}

// Fetch all admins
$admins = [];
$adminSelect = "SELECT * FROM admintb";
$adminSelectQuery = $connect->query($adminSelect);
while ($row = $adminSelectQuery->fetch_assoc()) {
    $admins[$row['AdminID']] = $row;
}

// Get supplier names
$suppliers = [];
$supplierSelect = "SELECT SupplierID, SupplierName, SupplierEmail FROM suppliertb";
$supplierSelectQuery = $connect->query($supplierSelect);
while ($row = $supplierSelectQuery->fetch_assoc()) {
    $suppliers[$row['SupplierID']] = $row['SupplierName'];
    $supplierEmails[$row['SupplierID']] = $row['SupplierEmail'];
}
?>

<table class="min-w-full bg-white rounded-lg">
    <thead>
        <tr class="bg-gray-100 text-gray-600 text-sm">
            <th class="p-3 text-start">ID</th>
            <th class="p-3 text-start">Admin</th>
            <th class="p-3 text-start hidden sm:table-cell">Supplier</th>
            <th class="p-3 text-start">Total Amount</th>
            <th class="p-3 text-start">Tax</th>
            <th class="p-3 text-start">Status</th>
            <th class="p-3 text-start">Actions</th>
        </tr>
    </thead>
    <tbody class="text-gray-600 text-sm">
        <?php if (!empty($purchases)): ?>
            <?php foreach ($purchases as $purchase):
                $admin = isset($admins[$purchase['AdminID']]) ? $admins[$purchase['AdminID']] : null;
            ?>
                <tr class="border-b border-gray-200 hover:bg-gray-50">
                    <td class="p-3 text-start whitespace-nowrap">
                        <div class="font-medium text-gray-500">
                            <span>#<?= htmlspecialchars($purchase['PurchaseID']) ?></span>
                        </div>
                    </td>
                    <td class="p-3 text-start">
                        <?php if ($admin): ?>
                            <div class="flex items-center gap-3">
                                <?php if ($admin['AdminProfile'] === null): ?>
                                    <div class="w-10 h-10 object-cover rounded-full bg-[<?= $admin['ProfileBgColor'] ?>] text-white select-none flex items-center justify-center font-semibold">
                                        <?= strtoupper(substr($admin['UserName'], 0, 1)) ?>
                                    </div>
                                <?php else: ?>
                                    <div class="w-10 h-10 rounded-full select-none overflow-hidden">
                                        <img class="w-full h-full object-cover"
                                            src="<?= htmlspecialchars($admin['AdminProfile']) ?>"
                                            alt="Profile">
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <p class="font-bold"><?= htmlspecialchars($admin['FirstName'] . ' ' . $admin['LastName']) ?></p>
                                    <p class="text-xs text-gray-500"><?= htmlspecialchars($admin['AdminEmail']) ?></p>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-gray-400">Admin not found</div>
                        <?php endif; ?>
                    </td>
                    <td class="p-3 text-start hidden sm:table-cell">
                        <p class="font-bold"><?= isset($suppliers[$purchase['SupplierID']]) ? htmlspecialchars($suppliers[$purchase['SupplierID']]) : htmlspecialchars($purchase['SupplierID']) ?></p>
                        <p><?= isset($supplierEmails[$purchase['SupplierID']]) ? htmlspecialchars($supplierEmails[$purchase['SupplierID']]) : htmlspecialchars($purchase['SupplierID']) ?></p>
                    </td>
                    <td class="p-3 text-start">
                        $<?= number_format($purchase['TotalAmount'], 2) ?>
                    </td>
                    <td class="p-3 text-start">
                        $<?= ($purchase['PurchaseTax']) ?>
                    </td>
                    <td class="p-3 text-start select-none">
                        <?php
                        $statusClass = '';
                        switch ($purchase['Status']) {
                            case 'Pending':
                                $statusClass = 'bg-yellow-100 border-yellow-200 text-yellow-800';
                                break;
                            case 'Completed':
                                $statusClass = 'bg-green-100 border-green-200 text-green-800';
                                break;
                            case 'Cancelled':
                                $statusClass = 'bg-red-100 border-red-200 text-red-800';
                                break;
                            default:
                                $statusClass = 'bg-blue-100 border-blue-200 text-blue-800';
                        }
                        ?>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full border <?= $statusClass ?>">
                            <?= htmlspecialchars($purchase['Status']) ?>
                        </span>
                    </td>
                    <td class="p-3 text-start space-x-1 select-none">
                        <i class="details-btn ri-eye-line text-lg cursor-pointer"
                            data-purchase-id="<?= htmlspecialchars($purchase['PurchaseID']) ?>"></i>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" class="p-3 text-center text-gray-500 py-52">
                    No purchases available.
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<script>
    // Function to load a specific page
    function loadPurchasePage(page) {
        const urlParams = new URLSearchParams(window.location.search);
        const searchQuery = urlParams.get('producttype_search') || '';

        // Update URL parameters
        urlParams.set('producttypepage', page);
        if (searchQuery) urlParams.set('producttype_search', searchQuery);

        const xhr = new XMLHttpRequest();
        xhr.open('GET', `../includes/admin_table_components/producttype_results.php?${urlParams.toString()}`, true);

        xhr.onload = function() {
            if (this.status === 200) {
                document.getElementById('purchaseResults').innerHTML = this.responseText;

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
                    const purchaseId = this.getAttribute('data-producttype-id');
                    darkOverlay2.classList.remove('opacity-0', 'invisible');
                    darkOverlay2.classList.add('opacity-100');

                    fetch(`../Admin/add_producttype.php?action=getProductTypeDetails&id=${purchaseId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById('updateProductTypeID').value = purchaseId;
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
                    const purchaseId = this.getAttribute('data-producttype-id');
                    darkOverlay2.classList.remove('opacity-0', 'invisible');
                    darkOverlay2.classList.add('opacity-100');

                    fetch(`../Admin/add_producttype.php?action=getProductTypeDetails&id=${purchaseId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById('deleteProductTypeID').value = purchaseId;
                                document.getElementById('purchaseDeleteName').textContent = data.producttype.ProductType;
                                purchaseConfirmDeleteModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
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