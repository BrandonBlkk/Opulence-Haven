<?php
require_once(__DIR__ . '/../../config/db_connection.php');
include(__DIR__ . '/../admin_pagination.php');

// Construct the purchase select query based on search
if (!empty($searchPurchaseQuery)) {
    $purchaseSelect = "
        SELECT DISTINCT p.* 
        FROM purchasetb p
        INNER JOIN purchasedetailtb pd ON p.PurchaseID = pd.PurchaseID
        INNER JOIN producttb pr ON pd.ProductID = pr.ProductID
        INNER JOIN producttypetb pt ON pr.ProductTypeID = pt.ProductTypeID
        INNER JOIN admintb a ON p.AdminID = a.AdminID
        INNER JOIN suppliertb s ON p.SupplierID = s.SupplierID
        WHERE (
            p.PurchaseID LIKE '%$searchPurchaseQuery%' OR 
            pt.ProductType LIKE '%$searchPurchaseQuery%' OR 
            pt.Description LIKE '%$searchPurchaseQuery%' OR
            pr.Title LIKE '%$searchPurchaseQuery%' OR
            pr.Description LIKE '%$searchPurchaseQuery%' OR
            pr.Brand LIKE '%$searchPurchaseQuery%' OR
            pr.Information LIKE '%$searchPurchaseQuery%' OR
            a.FirstName LIKE '%$searchPurchaseQuery%' OR
            a.LastName LIKE '%$searchPurchaseQuery%' OR
            a.UserName LIKE '%$searchPurchaseQuery%' OR
            a.AdminEmail LIKE '%$searchPurchaseQuery%' OR
            s.SupplierName LIKE '%$searchPurchaseQuery%' OR
            s.SupplierEmail LIKE '%$searchPurchaseQuery%'OR
            s.SupplierCompany LIKE '%$searchPurchaseQuery%'

        )
        AND p.Status = 'Confirmed'
        ORDER BY p.PurchaseDate DESC 
        LIMIT $rowsPerPage OFFSET $purchaseOffset
    ";
} else {
    $purchaseSelect = "
        SELECT p.* 
        FROM purchasetb p
        WHERE p.Status = 'Confirmed'
        ORDER BY p.PurchaseDate DESC 
        LIMIT $rowsPerPage OFFSET $purchaseOffset
    ";
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
            <th class="p-3 text-start hidden md:table-cell">Supplier</th>
            <th class="p-3 text-start hidden md:table-cell">Total Amount</th>
            <th class="p-3 text-start hidden lg:table-cell">Tax</th>
            <th class="p-3 text-start">Status</th>
            <th class="p-3 text-start hidden lg:table-cell">Purchased Date</th>
            <th class="p-3 text-start">Actions</th>
        </tr>
    </thead>
    <tbody class="text-gray-600 text-sm">
        <?php if (!empty($purchases)): ?>
            <?php foreach ($purchases as $purchase):
                $admin = isset($admins[$purchase['AdminID']]) ? $admins[$purchase['AdminID']] : null;
            ?>
                <tr class="border-b border-gray-200 hover:bg-gray-50">
                    <!-- ID -->
                    <td class="p-3 text-start whitespace-nowrap">
                        <div class="font-medium text-gray-500">
                            <span>#<?= htmlspecialchars($purchase['PurchaseID']) ?></span>
                        </div>
                    </td>

                    <!-- Admin -->
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
                                <div class="truncate">
                                    <p class="font-bold truncate"><?= htmlspecialchars($admin['FirstName'] . ' ' . $admin['LastName']) ?></p>
                                    <p class="text-xs text-gray-500 truncate"><?= htmlspecialchars($admin['AdminEmail']) ?></p>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-gray-400">Admin not found</div>
                        <?php endif; ?>
                    </td>

                    <!-- Supplier -->
                    <td class="p-3 text-start hidden md:table-cell">
                        <p class="font-bold truncate"><?= isset($suppliers[$purchase['SupplierID']]) ? htmlspecialchars($suppliers[$purchase['SupplierID']]) : htmlspecialchars($purchase['SupplierID']) ?></p>
                        <p class="text-xs truncate"><?= isset($supplierEmails[$purchase['SupplierID']]) ? htmlspecialchars($supplierEmails[$purchase['SupplierID']]) : htmlspecialchars($purchase['SupplierID']) ?></p>
                    </td>

                    <!-- Total Amount -->
                    <td class="p-3 text-start hidden md:table-cell">
                        $<?= number_format($purchase['TotalAmount'], 2) ?>
                    </td>

                    <!-- Tax -->
                    <td class="p-3 text-start hidden lg:table-cell">
                        $<?= ($purchase['PurchaseTax']) ?>
                    </td>

                    <!-- Status -->
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

                    <td class="p-3 text-start space-x-1 hidden lg:table-cell">
                        <p><?= htmlspecialchars(date('d M Y', strtotime($purchase['PurchaseDate']))) ?></p>
                    </td>

                    <!-- Actions -->
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
        const searchQuery = urlParams.get('purchase_search') || '';

        urlParams.set('purchasepage', page);
        if (searchQuery) urlParams.set('purchase_search', searchQuery);

        const xhr = new XMLHttpRequest();
        xhr.open('GET', `../includes/admin_table_components/purchase_results.php?${urlParams.toString()}`, true);

        xhr.onload = function() {
            if (this.status === 200) {
                document.getElementById('purchaseResults').innerHTML = this.responseText;

                // Also update the pagination controls
                const xhrPagination = new XMLHttpRequest();
                xhrPagination.open('GET', `../includes/admin_table_components/purchase_pagination.php?${urlParams.toString()}`, true);
                xhrPagination.onload = function() {
                    if (this.status === 200) {
                        document.getElementById('paginationContainer').innerHTML = this.responseText;
                    }
                };
                xhrPagination.send();

                window.history.pushState({}, '', `?${urlParams.toString()}`);
                window.scrollTo(0, 0);

                // Re-initialize buttons after table load
                initializeProductTypeActionButtons();
                initializePurchaseDetailsButtons();
            }
        };

        xhr.send();
    }

    // Function to handle search
    function handleSearch() {
        loadPurchasePage(1);
    }

    function showModal() {
        darkOverlay2?.classList.remove('opacity-0', 'invisible');
        darkOverlay2?.classList.add('opacity-100');
        purchaseDetailsModal?.classList.remove('opacity-0', 'invisible', '-translate-y-5');
        purchaseDetailsModal?.classList.add('opacity-100', 'visible', 'translate-y-0');
    }

    function hideModal() {
        darkOverlay2?.classList.remove('opacity-100');
        darkOverlay2?.classList.add('opacity-0', 'invisible');
        purchaseDetailsModal?.classList.add('opacity-0', 'invisible', '-translate-y-5');
        purchaseDetailsModal?.classList.remove('opacity-100', 'visible', 'translate-y-0');
    }

    // Initialize event listeners for search
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.querySelector('input[name="purchase_search"]');

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const urlParams = new URLSearchParams(window.location.search);
                urlParams.set('purchase_search', this.value);
                window.history.pushState({}, '', `?${urlParams.toString()}`);
                handleSearch();
            });
        }

        initializeProductTypeActionButtons();
        initializePurchaseDetailsButtons();
    });

    // Function to initialize action buttons for product types
    function initializeProductTypeActionButtons() {
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach(row => {
            const detailsBtn = row.querySelector('.details-btn');
            if (detailsBtn) {
                detailsBtn.onclick = null; // Remove previous listener
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
        });
    }

    // NEW: Function to initialize purchase details buttons
    function initializePurchaseDetailsButtons() {
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach(row => {
            const detailsBtn = row.querySelector('.details-btn');
            if (detailsBtn) {
                detailsBtn.onclick = null; // Remove previous listener
                detailsBtn.addEventListener('click', function() {
                    const purchaseId = this.getAttribute('data-purchase-id');
                    if (!purchaseId) return;

                    if (darkOverlay2) {
                        darkOverlay2.classList.remove('opacity-0', 'invisible');
                        darkOverlay2.classList.add('opacity-100');
                    }

                    fetch(`../Admin/purchase_history.php?action=getPurchaseDetails&id=${encodeURIComponent(purchaseId)}`)
                        .then(response => response.json())
                        .then(data => {
                            if (!data.success || !data.purchase) {
                                console.error('Failed to load purchase details', data);
                                if (darkOverlay2) {
                                    darkOverlay2.classList.remove('opacity-100');
                                    darkOverlay2.classList.add('opacity-0', 'invisible');
                                }
                                return;
                            }

                            const p = data.purchase;

                            document.getElementById('detailPurchaseID').textContent = p.PurchaseID || '';
                            document.getElementById('detailPurchaseDate').textContent = p.PurchaseDate ? new Date(p.PurchaseDate).toLocaleString() : 'N/A';
                            document.getElementById('detailAdmin').textContent = ((p.FirstName || '') + ' ' + (p.LastName || '')).trim();
                            document.getElementById('detailAdminEmail').textContent = p.AdminEmail || '';
                            document.getElementById('detailSupplier').textContent = p.SupplierName || '';
                            document.getElementById('detailSupplierEmail').textContent = p.SupplierEmail || '';
                            document.getElementById('detailTotalAmount').textContent = p.TotalAmount !== null ? ('$' + parseFloat(p.TotalAmount).toFixed(2)) : 'N/A';
                            document.getElementById('detailTax').textContent = p.PurchaseTax !== null ? ('$' + parseFloat(p.PurchaseTax).toFixed(2)) : 'N/A';
                            document.getElementById('detailStatus').textContent = p.Status || '';

                            fetchPurchaseItems(purchaseId);
                            showModal();
                        })
                        .catch(err => {
                            console.error('Fetch error:', err);
                            if (darkOverlay2) {
                                darkOverlay2.classList.remove('opacity-100');
                                darkOverlay2.classList.add('opacity-0', 'invisible');
                            }
                        });
                });
            }
        });
    }

    // Helper to fetch purchase items (reuse from main.js)
    function fetchPurchaseItems(purchaseId) {
        fetch(`../Admin/purchase_history.php?action=getPurchaseItems&id=${encodeURIComponent(purchaseId)}`)
            .then(res => res.json())
            .then(data => {
                const itemsContainer = document.getElementById('purchaseItems');
                if (!itemsContainer) return;
                itemsContainer.innerHTML = '';

                if (data.success && Array.isArray(data.items) && data.items.length) {
                    data.items.forEach(item => {
                        const tr = document.createElement('tr');
                        const qty = parseFloat(item.Quantity || 0);
                        const unit = parseFloat(item.UnitPrice || 0);
                        const total = qty * unit;

                        tr.innerHTML = `
                            <td class="p-3">${item.ProductName || item.ProductID || 'Product'}</td>
                            <td class="p-3">${qty}</td>
                            <td class="p-3">${unit.toFixed(2)}</td>
                            <td class="p-3">${total.toFixed(2)}</td>
                        `;
                        itemsContainer.appendChild(tr);
                    });
                } else {
                    itemsContainer.innerHTML = '<tr><td colspan="4" class="p-3 text-center text-gray-500">No items found</td></tr>';
                }
            })
            .catch(err => {
                console.error('Error fetching purchase items:', err);
                const itemsContainer = document.getElementById('purchaseItems');
                if (itemsContainer) {
                    itemsContainer.innerHTML = '<tr><td colspan="4" class="p-3 text-center text-red-500">Error loading items</td></tr>';
                }
            });
    }

    // Handle browser back/forward buttons
    window.addEventListener('popstate', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const searchInput = document.querySelector('input[name="purchase_search"]');
        if (searchInput) searchInput.value = urlParams.get('purchase_search') || '';
        loadPurchasePage(urlParams.get('purchasepage') || 1);
    });
</script>