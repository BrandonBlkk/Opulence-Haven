<?php
require_once(__DIR__ . '/../../config/db_connection.php');
include(__DIR__ . '/../admin_pagination.php');

// Construct the order query based on search and status filter
if ($filterStatus !== 'random' && !empty($searchOrderQuery)) {
    $orderSelect = "SELECT o.*, u.UserName, u.UserPhone, u.ProfileBgColor, u.UserEmail 
                    FROM ordertb o
                    JOIN usertb u ON o.UserID = u.UserID  
                    WHERE o.Status = '$filterStatus' 
                    AND o.Status != 'Pending' 
                    AND (o.FullName LIKE '%$searchOrderQuery%' 
                         OR o.PhoneNumber LIKE '%$searchOrderQuery%'
                         OR o.OrderID LIKE '%$searchOrderQuery%'
                         OR u.UserName LIKE '%$searchOrderQuery%')
                    ORDER BY o.OrderDate DESC
                    LIMIT $rowsPerPage OFFSET $orderOffset";
} elseif ($filterStatus !== 'random') {
    $orderSelect = "SELECT o.*, u.UserName, u.UserPhone, u.ProfileBgColor, u.UserEmail  
                    FROM ordertb o
                    JOIN usertb u ON o.UserID = u.UserID 
                    WHERE o.Status = '$filterStatus'
                    AND o.Status != 'Pending' 
                    ORDER BY o.OrderDate DESC
                    LIMIT $rowsPerPage OFFSET $orderOffset";
} elseif (!empty($searchOrderQuery)) {
    $orderSelect = "SELECT o.*, u.UserName, u.UserPhone, u.ProfileBgColor, u.UserEmail 
                    FROM ordertb o
                    JOIN usertb u ON o.UserID = u.UserID 
                    WHERE (o.FullName LIKE '%$searchOrderQuery%'
                           OR o.PhoneNumber LIKE '%$searchOrderQuery%'
                           OR o.OrderID LIKE '%$searchOrderQuery%'
                           OR u.UserName LIKE '%$searchOrderQuery%') 
                    AND o.Status != 'Pending' 
                    ORDER BY o.OrderDate DESC
                    LIMIT $rowsPerPage OFFSET $orderOffset";
} else {
    $orderSelect = "SELECT o.*, u.UserName, u.UserPhone, u.ProfileBgColor, u.UserEmail 
                    FROM ordertb o
                    JOIN usertb u ON o.UserID = u.UserID 
                    WHERE o.Status != 'Pending'
                    ORDER BY o.OrderDate DESC
                    LIMIT $rowsPerPage OFFSET $orderOffset";
}

$orderSelectQuery = $connect->query($orderSelect);
$orders = [];

if ($orderSelectQuery && mysqli_num_rows($orderSelectQuery) > 0) {
    while ($row = $orderSelectQuery->fetch_assoc()) {
        $orders[] = $row;
    }
}
?>

<table class="min-w-full bg-white rounded-lg">
    <thead>
        <tr class="bg-gray-100 text-gray-600 text-sm">
            <th class="p-3 text-start">Order ID</th>
            <th class="p-3 text-start">User</th>
            <th class="p-3 text-start hidden sm:table-cell">Contact</th>
            <th class="p-3 text-start">Total Price</th>
            <th class="p-3 text-start hidden lg:table-cell">Order Date</th>
            <th class="p-3 text-start">Status</th>
            <th class="p-3 text-start">Actions</th>
        </tr>
    </thead>
    <tbody class="text-gray-600 text-sm">
        <?php if (!empty($orders)): ?>
            <?php foreach ($orders as $order): ?>
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="p-3 text-start whitespace-nowrap">
                        <div class="font-medium text-gray-500">
                            <span>#<?= htmlspecialchars($order['OrderID']) ?></span>
                        </div>
                    </td>
                    <td class="p-3 text-start whitespace-nowrap flex items-center gap-2">
                        <div id="profilePreview" class="w-10 h-10 object-cover rounded-full bg-[<?php echo $order['ProfileBgColor'] ?>] text-white select-none">
                            <p class="w-full h-full flex items-center justify-center font-semibold"><?php echo strtoupper(substr($order['UserName'], 0, 1)); ?></p>
                        </div>
                        <div>
                            <p class="font-bold">
                                <?= htmlspecialchars($order['FullName']) ?>
                                <span class="text-gray-400 text-xs font-normal">(<?= htmlspecialchars($order['UserName']) ?>)</span>
                            </p>
                            <p class="text-xs text-gray-500 truncate hidden sm:block"><?= htmlspecialchars($order['UserEmail']) ?></p>
                        </div>
                    </td>
                    <td class="p-3 text-start hidden sm:table-cell">
                        <?= htmlspecialchars($order['PhoneNumber']) ?>
                    </td>
                    <td class="p-3 text-start">
                        $<?= htmlspecialchars(number_format($order['TotalPrice'], 2)) ?>
                        <?php if ($order['AdditionalAmount'] > 0): ?>
                            <div class="text-xs text-blue-500">
                                +$<?= htmlspecialchars(number_format($order['AdditionalAmount'], 2)) ?> (Additional)
                            </div>
                        <?php endif; ?>
                        <?php if ($order['OrderTax'] > 0): ?>
                            <div class="text-xs text-green-500">
                                +$<?= htmlspecialchars(number_format($order['OrderTax'], 2)) ?> (Tax)
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="p-3 text-start hidden lg:table-cell">
                        <?= htmlspecialchars(date('d M Y', strtotime($order['OrderDate']))) ?>
                        <div class="text-xs text-gray-400 <?php echo $order['DeliveredDate'] ? '' : 'hidden'; ?>">
                            Delivered: <?= htmlspecialchars(date('d M Y', strtotime($order['DeliveredDate']))) ?>
                        </div>
                    </td>
                    <td class="p-3 text-start">
                        <?php
                        $statusClass = '';
                        switch ($order['Status']) {
                            case 'Order Placed':
                                $statusClass = 'bg-blue-100 border border-blue-200 text-blue-800';
                                break;
                            case 'Processing':
                                $statusClass = 'bg-indigo-100 border border-indigo-200 text-indigo-800';
                                break;
                            case 'Shipped':
                                $statusClass = 'bg-purple-100 border border-purple-200 text-purple-800';
                                break;
                            case 'Delivered':
                                $statusClass = 'bg-green-100 border border-green-200 text-green-800';
                                break;
                            case 'Cancelled':
                                $statusClass = 'bg-red-100 border border-red-200 text-red-800';
                                break;
                            default:
                                $statusClass = 'bg-gray-100 border-gray-200 text-gray-800';
                        }
                        ?>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full border select-none <?= $statusClass ?>">
                            <?= htmlspecialchars($order['Status']) ?>
                        </span>
                    </td>
                    <td class="p-3 text-start whitespace-nowrap">
                        <i class="details-btn ri-eye-line text-lg cursor-pointer"
                            data-order-id="<?= htmlspecialchars($order['OrderID']) ?>"></i>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" class="p-3 text-center text-gray-500 py-52">
                    No orders found.
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<script>
    // Function to load a specific page of orders
    function loadOrderPage(page = 1) {
        const urlParams = new URLSearchParams(window.location.search);
        const searchQuery = document.querySelector('input[name="order_search"]')?.value || '';
        const sortType = document.querySelector('select[name="sort"]')?.value || 'random';

        // Update URL parameters
        urlParams.set('orderpage', page);
        urlParams.set('order_search', searchQuery);
        urlParams.set('sort', sortType);

        // Fetch order results
        fetch(`../includes/admin_table_components/order_results.php?${urlParams.toString()}`)
            .then(response => response.text())
            .then(data => {
                document.getElementById('orderResults').innerHTML = data;

                // Fetch pagination controls
                return fetch(`../includes/admin_table_components/order_pagination.php?${urlParams.toString()}`);
            })
            .then(response => response.text())
            .then(pagination => {
                document.getElementById('paginationContainer').innerHTML = pagination;
                attachPaginationEventListeners(); // Reattach events after update
                initializeOrderActionButtons(); // Reattach action buttons
            })
            .catch(error => console.error('Fetch error:', error));

        window.history.pushState({}, '', `?${urlParams.toString()}`);
        window.scrollTo(0, 0);
    }

    // Function to handle realtime search and filter
    function handleSearchFilter() {
        loadOrderPage(1);
    }

    // Attach pagination events dynamically
    function attachPaginationEventListeners() {
        document.querySelectorAll('.page-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const page = parseInt(this.dataset.page);
                if (!isNaN(page)) loadOrderPage(page);
            });
        });

        const prevBtn = document.querySelector('.prev-page-btn');
        if (prevBtn) {
            prevBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const currentPage = parseInt(new URLSearchParams(window.location.search).get('orderpage') || 1);
                if (currentPage > 1) loadOrderPage(currentPage - 1);
            });
        }

        const nextBtn = document.querySelector('.next-page-btn');
        if (nextBtn) {
            nextBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const currentPage = parseInt(new URLSearchParams(window.location.search).get('orderpage') || 1);
                const totalPages = parseInt(document.querySelector('.page-btn:last-child')?.dataset.page || 1);
                if (currentPage < totalPages) loadOrderPage(currentPage + 1);
            });
        }
    }

    // Initialize order action buttons
    function initializeOrderActionButtons() {
        document.querySelectorAll('tbody tr').forEach(row => {
            // Edit button
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

    // Initialize everything on page load
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.querySelector('input[name="order_search"]');
        const filterSelect = document.querySelector('select[name="sort"]');

        if (searchInput) searchInput.addEventListener('input', handleSearchFilter);
        if (filterSelect) filterSelect.addEventListener('change', handleSearchFilter);

        attachPaginationEventListeners();
        initializeOrderActionButtons();
    });

    // Handle back/forward browser buttons
    window.addEventListener('popstate', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const searchInput = document.querySelector('input[name="order_search"]');
        const filterSelect = document.querySelector('select[name="sort"]');

        if (searchInput) searchInput.value = urlParams.get('order_search') || '';
        if (filterSelect) filterSelect.value = urlParams.get('sort') || 'random';

        loadOrderPage(parseInt(urlParams.get('orderpage') || 1));
    });
</script>