<?php
require_once(__DIR__ . '/../../config/db_connection.php');
include(__DIR__ . '/../admin_pagination.php');

// Get current page from GET, default to 1
$returnCurrentPage = isset($_GET['returnpage']) ? (int)$_GET['returnpage'] : 1;
$rowsPerPage = $rowsPerPage ?? 10; // fallback if not set
$returnOffset = ($returnCurrentPage - 1) * $rowsPerPage;

// Construct the reservation query based on search and status filter
$searchReturnQuery = isset($_GET['return_search']) ? $_GET['return_search'] : '';
$filterStatus = $filterStatus ?? 'random';

if ($filterStatus !== 'random' && !empty($searchReturnQuery)) {
    $returnSelect = "
        SELECT 
            r.*, 
            u.UserName, 
            u.UserPhone, 
            u.UserEmail, 
            u.ProfileBgColor, 
            p.Title
        FROM returntb r
        JOIN usertb u ON r.UserID = u.UserID
        JOIN producttb p ON r.ProductID = p.ProductID
        WHERE r.Status = '$filterStatus'
          AND (
              u.UserName LIKE '%$searchReturnQuery%' OR
              u.UserEmail LIKE '%$searchReturnQuery%' OR
              u.UserPhone LIKE '%$searchReturnQuery%' OR
              r.ReturnID LIKE '%$searchReturnQuery%' OR
              p.Title LIKE '%$searchReturnQuery%'
          )
        ORDER BY r.RequestDate DESC
        LIMIT $rowsPerPage OFFSET $returnOffset
    ";
} elseif ($filterStatus !== 'random') {
    $returnSelect = "
        SELECT 
            r.*, 
            u.UserName, 
            u.UserPhone, 
            u.UserEmail, 
            u.ProfileBgColor, 
            p.Title
        FROM returntb r
        JOIN usertb u ON r.UserID = u.UserID
        JOIN producttb p ON r.ProductID = p.ProductID
        WHERE r.Status = '$filterStatus'
        ORDER BY r.RequestDate DESC
        LIMIT $rowsPerPage OFFSET $returnOffset
    ";
} elseif (!empty($searchReturnQuery)) {
    $returnSelect = "
        SELECT 
            r.*, 
            u.UserName, 
            u.UserPhone, 
            u.UserEmail, 
            u.ProfileBgColor, 
            p.Title
        FROM returntb r
        JOIN usertb u ON r.UserID = u.UserID
        JOIN producttb p ON r.ProductID = p.ProductID
        WHERE (
            u.UserName LIKE '%$searchReturnQuery%' OR
            u.UserEmail LIKE '%$searchReturnQuery%' OR
            u.UserPhone LIKE '%$searchReturnQuery%' OR
            r.ReturnID LIKE '%$searchReturnQuery%' OR
            p.Title LIKE '%$searchReturnQuery%'
        )
        ORDER BY r.RequestDate DESC
        LIMIT $rowsPerPage OFFSET $returnOffset
    ";
} else {
    $returnSelect = "
        SELECT 
            r.*, 
            u.UserName, 
            u.UserPhone, 
            u.UserEmail, 
            u.ProfileBgColor, 
            p.Title
        FROM returntb r
        JOIN usertb u ON r.UserID = u.UserID
        JOIN producttb p ON r.ProductID = p.ProductID
        ORDER BY r.RequestDate DESC
        LIMIT $rowsPerPage OFFSET $returnOffset
    ";
}

$returnSelectQuery = $connect->query($returnSelect);
$returns = [];
if ($returnSelectQuery && mysqli_num_rows($returnSelectQuery) > 0) {
    while ($row = $returnSelectQuery->fetch_assoc()) {
        $returns[] = $row;
    }
}
?>

<table class="min-w-full bg-white rounded-lg">
    <thead>
        <tr class="bg-gray-100 text-gray-600 text-sm">
            <th class="p-3 text-start">Return ID</th>
            <th class="p-3 text-start">Order ID</th>
            <th class="p-3 text-start">User</th>
            <th class="p-3 text-start">Product</th>
            <th class="p-3 text-start">Action Type</th>
            <th class="p-3 text-start">Remarks</th>
            <th class="p-3 text-start">Requested Date</th>
            <th class="p-3 text-start">Status</th>
            <th class="p-3 text-start">Actions</th>
        </tr>
    </thead>
    <tbody class="text-gray-600 text-sm">
        <?php if (!empty($returns)): ?>
            <?php foreach ($returns as $return): ?>
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="p-3 text-start whitespace-nowrap font-medium text-gray-500">#<?= htmlspecialchars($return['ReturnID']) ?></td>
                    <td class="p-3 text-start whitespace-nowrap"><?= htmlspecialchars($return['OrderID']) ?></td>
                    <td class="p-3 text-start flex items-center gap-2">
                        <div class="w-10 h-10 object-cover rounded-full bg-[<?= $return['ProfileBgColor'] ?>] text-white select-none flex items-center justify-center font-semibold">
                            <?= strtoupper(substr($return['UserName'], 0, 1)) ?>
                        </div>
                        <div>
                            <p class="font-bold"><?= htmlspecialchars($return['UserName']) ?></p>
                            <p class="text-xs text-gray-500 truncate"><?= htmlspecialchars($return['UserEmail']) ?></p>
                        </div>
                    </td>
                    <td class="p-3 text-start whitespace-nowrap"><?= htmlspecialchars($return['Title']) ?></td>
                    <td class="p-3 text-start"><?= htmlspecialchars($return['ActionType']) ?></td>
                    <td class="p-3 text-start"><?= htmlspecialchars($return['Remarks']) ?></td>
                    <td class="p-3 text-start"><?= htmlspecialchars(date('d M Y', strtotime($return['RequestDate']))) ?></td>
                    <td class="p-3 text-start">
                        <?php
                        $statusClass = '';
                        switch ($return['Status']) {
                            case 'Confirmed':
                                $statusClass = 'bg-green-100 border-green-200 text-green-800';
                                break;
                            case 'Pending':
                                $statusClass = 'bg-yellow-100 border-yellow-200 text-yellow-800';
                                break;
                            case 'Cancelled':
                                $statusClass = 'bg-red-100 border-red-200 text-red-800';
                                break;
                            case 'Completed':
                                $statusClass = 'bg-blue-100 border-blue-200 text-blue-800';
                                break;
                            default:
                                $statusClass = 'bg-gray-100 border-gray-200 text-gray-800';
                        }
                        ?>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full border select-none <?= $statusClass ?>">
                            <?= htmlspecialchars($return['Status']) ?>
                        </span>
                    </td>
                    <td class="p-3 text-start whitespace-nowrap">
                        <i class="details-btn ri-eye-line text-lg cursor-pointer" data-return-id="<?= htmlspecialchars($return['ReturnID']) ?>"></i>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="11" class="p-3 text-center text-gray-500 py-52">
                    No returns found.
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<script>
    // Function to load a specific page for return
    function loadReturnPage(page) {
        const urlParams = new URLSearchParams(window.location.search);
        const searchQuery = urlParams.get('return_search') || '';
        const filterStatus = urlParams.get('sort') || 'random';

        // Update URL parameters
        urlParams.set('returnpage', page);
        urlParams.set('return_search', searchQuery);
        urlParams.set('sort', filterStatus);

        const xhr = new XMLHttpRequest();
        xhr.open('GET', `../includes/admin_table_components/return_results.php?${urlParams.toString()}`, true);

        xhr.onload = function() {
            if (this.status === 200) {
                document.getElementById('returnResults').innerHTML = this.responseText;

                // Re-attach reservation detail button listeners after table update
                document.querySelectorAll('tbody tr').forEach(row => {
                    attachReturnListenersToRow(row);
                });

                // Update the pagination controls
                const xhrPagination = new XMLHttpRequest();
                xhrPagination.open('GET', `../includes/admin_table_components/return_pagination.php?${urlParams.toString()}`, true);
                xhrPagination.onload = function() {
                    if (this.status === 200) {
                        document.getElementById('paginationContainer').innerHTML = this.responseText;
                        // Re-attach pagination listeners
                        attachPaginationEventListeners();
                    }
                };
                xhrPagination.send();

                window.history.pushState({}, '', `?${urlParams.toString()}`);
                window.scrollTo(0, 0);
            }
        };

        xhr.send();
    }

    // Function to handle search for return
    function handleReturnSearch() {
        loadReturnPage(1);
    }

    const attachReturnListenersToRow = (row) => {
        const detailsBtn = row.querySelector('.details-btn');
        if (detailsBtn) {
            detailsBtn.addEventListener('click', function() {
                const returnId = this.getAttribute('data-return-id');

                // Show modal
                darkOverlay2.classList.remove('opacity-0', 'invisible');
                darkOverlay2.classList.add('opacity-100');
                returnModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');

                // Fetch return details
                fetch(`return.php?action=getReturnDetails&id=${returnId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success || !data.return) {
                            console.error('Failed to load return details');
                            return;
                        }

                        const returnData = data.return;

                        document.getElementById('returnUserName').textContent = returnData.UserName || "N/A";
                        document.getElementById('returnUserPhone').textContent = returnData.UserPhone || "N/A";
                        document.getElementById('returnUserEmail').textContent = returnData.UserEmail || "N/A";
                        document.getElementById('returnUserEmail').href = returnData.UserEmail ? `mailto:${returnData.UserEmail}` : '#';
                        document.getElementById('returnRequestDate').textContent = formatDate(returnData.RequestDate);
                        document.getElementById('returnStatus').textContent = returnData.Status || "N/A";
                        document.getElementById('returnProductName').textContent = returnData.Title || "N/A";
                        document.getElementById('returnProductImage').src = returnData.ProductImage && returnData.ProductImage.trim() !== "" ?
                            `../Admin/${returnData.ProductImage}` :
                            '../Admin/default.png';
                    })
                    .catch(error => {
                        console.error('Fetch error:', error);
                    });
            });
        }
    };

    // Function to attach pagination event listeners dynamically
    function attachPaginationEventListeners() {
        function clickHandler(e) {
            e.preventDefault();
            const page = parseInt(this.dataset.page);
            if (!isNaN(page)) {
                loadReturnPage(page);
            }
        }

        document.querySelectorAll('.page-btn').forEach(btn => {
            btn.removeEventListener('click', clickHandler);
            btn.addEventListener('click', clickHandler);
        });

        const prevBtn = document.querySelector('.prev-page-btn');
        if (prevBtn) {
            prevBtn.removeEventListener('click', clickHandler);
            prevBtn.addEventListener('click', clickHandler);
        }

        const nextBtn = document.querySelector('.next-page-btn');
        if (nextBtn) {
            nextBtn.removeEventListener('click', clickHandler);
            nextBtn.addEventListener('click', clickHandler);
        }
    }

    // Initialize order action buttons
    function initializeOrderActionButtons() {
        document.querySelectorAll('tbody tr').forEach(row => {
            const detailsBtn = row.querySelector('.details-btn');
            if (detailsBtn) {
                detailsBtn.addEventListener('click', function() {
                    const orderId = this.getAttribute('data-order-id');
                    currentOrderId = orderId;

                    if (darkOverlay2) {
                        darkOverlay2.classList.remove('opacity-0', 'invisible');
                        darkOverlay2.classList.add('opacity-100');
                    }
                    orderModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');

                    fetch(`../Admin/order.php?action=getOrderDetails&id=${orderId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success && data.order) {
                                const order = data.order;

                                document.getElementById('userFullName').textContent = order.FullName ?? "N/A";
                                document.getElementById('userName').textContent = order.UserName ? order.UserName.charAt(0).toUpperCase() : "N/A";
                                document.getElementById('profilePreview').style.backgroundColor = order.ProfileBgColor ?? "#999";
                                document.getElementById('userEmail').textContent = order.UserEmail ?? "N/A";
                                document.getElementById('userPhone').textContent = order.UserPhone ?? "N/A";
                                document.getElementById('userAddress').textContent = order.ShippingAddress ?? "N/A";
                                document.getElementById('userCity').textContent = order.City ?? "N/A";
                                document.getElementById('userState').textContent = order.State ?? "N/A";
                                document.getElementById('userZip').textContent = order.ZipCode ?? "N/A";
                                document.getElementById('orderDate').textContent = formatDate(order.OrderDate);

                                document.getElementById('orderSubtotal').textContent = `$ ${parseFloat(order.Subtotal ?? 0).toFixed(2)}`;
                                document.getElementById('orderTaxesFees').textContent = `$ ${((parseFloat(order.OrderTax) || 0) + 5).toFixed(2)}`;
                                document.getElementById('orderTotal').textContent = `$ ${parseFloat(order.TotalPrice ?? 0).toFixed(2)}`;

                                const products = Array.isArray(order.Products) ? order.Products : [];
                                initializeOrderProductSwiper(products);
                            } else {
                                console.error('Failed to load order details');
                            }
                        })
                        .catch(error => console.error('Fetch error:', error));
                });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Search input listener
        const searchInput = document.querySelector('input[name="return_search"]');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const urlParams = new URLSearchParams(window.location.search);
                urlParams.set('return_search', this.value);
                window.history.pushState({}, '', `?${urlParams.toString()}`);
                handleReturnSearch();
            });
        }

        // Filter select listener
        const filterSelect = document.querySelector('select[name="sort"]');
        if (filterSelect) {
            filterSelect.addEventListener('change', function() {
                const urlParams = new URLSearchParams(window.location.search);
                urlParams.set('sort', this.value);
                window.history.pushState({}, '', `?${urlParams.toString()}`);
                loadReturnPage(1); // reset to page 1 on filter change
            });
        }

        // Initial attachment of pagination listeners
        attachPaginationEventListeners();

        // Initial attachment of reservation detail buttons
        document.querySelectorAll('tbody tr').forEach(row => {
            attachReturnListenersToRow(row);
        });
    });

    // Handle browser back/forward buttons
    window.addEventListener('popstate', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const searchInput = document.querySelector('input[name="return_search"]');
        const filterSelect = document.querySelector('select[name="sort"]');

        if (searchInput) searchInput.value = urlParams.get('return_search') || '';
        if (filterSelect) filterSelect.value = urlParams.get('sort') || 'random';

        loadReturnPage(urlParams.get('reservationpage') || 1);
    });
</script>