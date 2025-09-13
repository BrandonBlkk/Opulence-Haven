<?php
require_once(__DIR__ . '/../../config/db_connection.php');
include(__DIR__ . '/../admin_pagination.php');

// Construct the order query based on search and status filter
if ($filterStatus !== 'random' && !empty($searchBookingQuery)) {
    $orderSelect = "SELECT o.*, u.UserName, u.UserPhone, u.ProfileBgColor, u.UserEmail 
                     FROM ordertb o
                     JOIN usertb u ON o.UserID = u.UserID  
                     WHERE o.Status = '$filterStatus' 
                     AND (o.FullName LIKE '%$searchBookingQuery%' 
                          OR o.PhoneNumber LIKE '%$searchBookingQuery%'
                          OR o.OrderID LIKE '%$searchBookingQuery%'
                          OR u.UserName LIKE '%$searchBookingQuery%') 
                     LIMIT $rowsPerPage OFFSET $reservationOffset";
} elseif ($filterStatus !== 'random') {
    $orderSelect = "SELECT o.*, u.UserName, u.UserPhone, u.ProfileBgColor, u.UserEmail  
                     FROM ordertb o
                     JOIN usertb u ON o.UserID = u.UserID 
                     WHERE o.Status = '$filterStatus' 
                     LIMIT $rowsPerPage OFFSET $reservationOffset";
} elseif (!empty($searchBookingQuery)) {
    $orderSelect = "SELECT o.*, u.UserName, u.UserPhone, u.ProfileBgColor, u.UserEmail 
                     FROM ordertb o
                     JOIN usertb u ON o.UserID = u.UserID 
                     WHERE (o.FullName LIKE '%$searchBookingQuery%'
                           OR o.PhoneNumber LIKE '%$searchBookingQuery%'
                           OR o.OrderID LIKE '%$searchBookingQuery%'
                           OR u.UserName LIKE '%$searchBookingQuery%') 
                     LIMIT $rowsPerPage OFFSET $reservationOffset";
} else {
    $orderSelect = "SELECT o.*, u.UserName, u.UserPhone, u.ProfileBgColor, u.UserEmail 
                     FROM ordertb o
                     JOIN usertb u ON o.UserID = u.UserID 
                     LIMIT $rowsPerPage OFFSET $reservationOffset";
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
                    <td class="p-3 text-start flex items-center gap-2">
                        <div id="profilePreview" class="w-10 h-10 object-cover rounded-full bg-[<?php echo $order['ProfileBgColor'] ?>] text-white select-none">
                            <p class="w-full h-full flex items-center justify-center font-semibold"><?php echo strtoupper(substr($order['UserName'], 0, 1)); ?></p>
                        </div>
                        <div>
                            <p class="font-bold"><?= htmlspecialchars($order['FullName']) ?> <span class="text-gray-400 text-xs font-normal">(<?= htmlspecialchars($order['UserName']) ?>)</span></p>
                            <p><?= htmlspecialchars($order['UserEmail']) ?></p>
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
                        <span class="px-2 py-1 text-xs font-semibold rounded-full border <?= $statusClass ?>">
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
    // Function to load a specific page for orders
    function loadOrderPage(page) {
        const urlParams = new URLSearchParams(window.location.search);
        const searchQuery = urlParams.get('order_search') || '';

        // Update URL parameters
        urlParams.set('orderpage', page);
        if (searchQuery) urlParams.set('order_search', searchQuery);

        const xhr = new XMLHttpRequest();
        xhr.open('GET', `../includes/admin_table_components/order_results.php?${urlParams.toString()}`, true);

        xhr.onload = function() {
            if (this.status === 200) {
                document.getElementById('orderResults').innerHTML = this.responseText;

                // Also update the pagination controls
                const xhrPagination = new XMLHttpRequest();
                xhrPagination.open('GET', `../includes/admin_table_components/order_pagination.php?${urlParams.toString()}`, true);
                xhrPagination.onload = function() {
                    if (this.status === 200) {
                        document.getElementById('paginationContainer').innerHTML = this.responseText;
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

    function handleOrderSearch() {
        loadOrderPage(1);
    }

    function attachPaginationEventListeners() {
        document.querySelectorAll('.page-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const page = this.getAttribute('data-page');
                loadOrderPage(parseInt(page));
            });
        });

        const prevBtn = document.querySelector('.prev-page-btn');
        if (prevBtn) {
            prevBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const currentPage = <?= $reservationCurrentPage ?>;
                if (currentPage > 1) {
                    loadOrderPage(currentPage - 1);
                }
            });
        }

        const nextBtn = document.querySelector('.next-page-btn');
        if (nextBtn) {
            nextBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const currentPage = <?= $reservationCurrentPage ?>;
                const totalPages = <?= $totalReservationPages ?>;
                if (currentPage < totalPages) {
                    loadOrderPage(currentPage + 1);
                }
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.querySelector('input[name="order_search"]');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const urlParams = new URLSearchParams(window.location.search);
                urlParams.set('order_search', this.value);
                window.history.pushState({}, '', `?${urlParams.toString()}`);
                handleOrderSearch();
            });
        }
        attachPaginationEventListeners();
    });

    window.addEventListener('popstate', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const searchInput = document.querySelector('input[name="order_search"]');
        if (searchInput) {
            searchInput.value = urlParams.get('order_search') || '';
        }
        loadOrderPage(urlParams.get('orderpage') || 1);
    });
</script>