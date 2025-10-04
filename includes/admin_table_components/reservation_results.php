<?php
require_once(__DIR__ . '/../../config/db_connection.php');
include(__DIR__ . '/../admin_pagination.php');

// Get current page from GET, default to 1
$reservationCurrentPage = isset($_GET['reservationpage']) ? (int)$_GET['reservationpage'] : 1;
$rowsPerPage = $rowsPerPage ?? 10; // fallback if not set
$reservationOffset = ($reservationCurrentPage - 1) * $rowsPerPage;

// Construct the reservation query based on search and status filter
$searchBookingQuery = isset($_GET['reservation_search']) ? $_GET['reservation_search'] : '';
$filterStatus = $filterStatus ?? 'random';

if ($filterStatus !== 'random' && !empty($searchBookingQuery)) {
    $bookingSelect = "SELECT r.*, u.UserName, u.UserPhone, u.UserEmail, u.ProfileBgColor 
                     FROM reservationtb r 
                     JOIN usertb u ON r.UserID = u.UserID  
                     WHERE r.Status = '$filterStatus' 
                     AND r.Status != 'Pending'
                     AND (r.FirstName LIKE '%$searchBookingQuery%' 
                          OR r.LastName LIKE '%$searchBookingQuery%'
                          OR r.UserPhone LIKE '%$searchBookingQuery%'
                          OR r.ReservationID LIKE '%$searchBookingQuery%'
                          OR u.UserName LIKE '%$searchBookingQuery%') 
                     ORDER BY r.ReservationDate DESC
                     LIMIT $rowsPerPage OFFSET $reservationOffset";
} elseif ($filterStatus !== 'random') {
    $bookingSelect = "SELECT r.*, u.UserName, u.UserPhone, u.UserEmail, u.ProfileBgColor
                     FROM reservationtb r 
                     JOIN usertb u ON r.UserID = u.UserID 
                     WHERE r.Status = '$filterStatus' 
                     AND r.Status != 'Pending'
                     ORDER BY r.ReservationDate DESC
                     LIMIT $rowsPerPage OFFSET $reservationOffset";
} elseif (!empty($searchBookingQuery)) {
    $bookingSelect = "SELECT r.*, u.UserName, u.UserPhone, u.UserEmail, u.ProfileBgColor 
                     FROM reservationtb r 
                     JOIN usertb u ON r.UserID = u.UserID 
                     WHERE (r.FirstName LIKE '%$searchBookingQuery%'
                           OR r.LastName LIKE '%$searchBookingQuery%'
                           OR r.UserPhone LIKE '%$searchBookingQuery%'
                           OR r.ReservationID LIKE '%$searchBookingQuery%'
                           OR u.UserName LIKE '%$searchBookingQuery%')
                     AND r.Status != 'Pending'
                     ORDER BY r.ReservationDate DESC
                     LIMIT $rowsPerPage OFFSET $reservationOffset";
} else {
    $bookingSelect = "SELECT r.*, u.UserName, u.UserPhone, u.UserEmail, u.ProfileBgColor
                     FROM reservationtb r
                     JOIN usertb u ON r.UserID = u.UserID 
                     WHERE r.Status != 'Pending'
                     ORDER BY r.ReservationDate DESC
                     LIMIT $rowsPerPage OFFSET $reservationOffset";
}

$bookingSelectQuery = $connect->query($bookingSelect);
$bookings = [];
if (mysqli_num_rows($bookingSelectQuery) > 0) {
    while ($row = $bookingSelectQuery->fetch_assoc()) {
        $bookings[] = $row;
    }
}
?>

<table class="min-w-full bg-white rounded-lg">
    <thead>
        <tr class="bg-gray-100 text-gray-600 text-sm">
            <th class="p-3 text-start">Reservation ID</th>
            <th class="p-3 text-start">Customer</th>
            <th class="p-3 text-start hidden sm:table-cell">Contact</th>
            <th class="p-3 text-start">Total Price</th>
            <th class="p-3 text-start hidden lg:table-cell">Reservation Date</th>
            <th class="p-3 text-start">Status</th>
            <th class="p-3 text-start">Actions</th>
        </tr>
    </thead>
    <tbody class="text-gray-600 text-sm">
        <?php if (!empty($bookings)): ?>
            <?php foreach ($bookings as $booking): ?>
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="p-3 text-start whitespace-nowrap">
                        <div class="font-medium text-gray-500">
                            <span>#<?= htmlspecialchars($booking['ReservationID']) ?></span>
                        </div>
                    </td>
                    <td class="p-3 text-start flex items-center gap-2">
                        <div id="profilePreview" class="w-10 h-10 object-cover rounded-full bg-[<?= $booking['ProfileBgColor'] ?>] text-white select-none">
                            <p class="w-full h-full flex items-center justify-center font-semibold"><?= strtoupper(substr($booking['UserName'], 0, 1)) ?></p>
                        </div>
                        <div>
                            <p class="font-bold"><?= htmlspecialchars($booking['Title'] . ' ' . $booking['FirstName'] . ' ' . $booking['LastName']) ?> <span class="text-gray-400 text-xs font-normal">(<?= htmlspecialchars($booking['UserName']) ?>)</span></p>
                            <p class="text-xs text-gray-500 truncate"><?= htmlspecialchars($booking['UserEmail']) ?></p>
                            <div class="text-xs text-gray-400 mt-1 textred">
                                <?= htmlspecialchars($booking['Travelling'] === 1 ? 'Travelling' : 'Not Travelling') ?>
                            </div>
                        </div>
                    </td>
                    <td class="p-3 text-start hidden sm:table-cell">
                        <?= htmlspecialchars($booking['UserPhone']) ?>
                    </td>
                    <td class="p-3 text-start">
                        $<?= htmlspecialchars(number_format($booking['TotalPrice'], 2)) ?>
                        <?php if ($booking['PointsDiscount'] > 0): ?>
                            <div class="text-xs text-green-500">
                                -$<?= htmlspecialchars(number_format($booking['PointsDiscount'], 2)) ?> (Points)
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="p-3 text-start hidden lg:table-cell">
                        <?= htmlspecialchars(date('d M Y', strtotime($booking['ReservationDate']))) ?>
                        <div class="text-xs text-gray-400">
                            Exp: <?= htmlspecialchars(date('d M Y', strtotime($booking['ExpiryDate']))) ?>
                        </div>
                    </td>
                    <td class="p-3 text-start">
                        <?php
                        $statusClass = '';
                        switch ($booking['Status']) {
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
                            <?= htmlspecialchars($booking['Status']) ?>
                        </span>
                    </td>
                    <td class="p-3 text-start whitespace-nowrap">
                        <i class="details-btn ri-eye-line text-lg cursor-pointer" data-reservation-id="<?= htmlspecialchars($booking['ReservationID']) ?>"></i>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" class="p-3 text-center text-gray-500 py-52">
                    No reservations found.
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<script>
    // Function to load a specific page for reservations
    function loadReservationPage(page) {
        const urlParams = new URLSearchParams(window.location.search);
        const searchQuery = urlParams.get('reservation_search') || '';
        const filterStatus = urlParams.get('sort') || 'random';

        // Update URL parameters
        urlParams.set('reservationpage', page);
        urlParams.set('reservation_search', searchQuery);
        urlParams.set('sort', filterStatus);

        const xhr = new XMLHttpRequest();
        xhr.open('GET', `../includes/admin_table_components/reservation_results.php?${urlParams.toString()}`, true);

        xhr.onload = function() {
            if (this.status === 200) {
                document.getElementById('reservationResults').innerHTML = this.responseText;

                // Re-attach reservation detail button listeners after table update
                document.querySelectorAll('tbody tr').forEach(row => {
                    attachReservationListenersToRow(row);
                });

                // Update the pagination controls
                const xhrPagination = new XMLHttpRequest();
                xhrPagination.open('GET', `../includes/admin_table_components/reservation_pagination.php?${urlParams.toString()}`, true);
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

    // Function to handle search for reservations
    function handleReservationSearch() {
        loadReservationPage(1);
    }

    const attachReservationListenersToRow = (row) => {
        const detailsBtn = row.querySelector('.details-btn');
        if (detailsBtn) {
            detailsBtn.addEventListener('click', function() {
                const reservationId = this.getAttribute('data-reservation-id');

                // Show modal
                if (darkOverlay2) {
                    darkOverlay2.classList.remove('opacity-0', 'invisible');
                    darkOverlay2.classList.add('opacity-100');
                }
                reservationModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');

                // Fetch reservation details
                fetch(`../Admin/reservation.php?action=getReservationDetails&id=${reservationId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success || !data.reservation) {
                            console.error('Failed to load reservation details');
                            return;
                        }

                        const reservation = data.reservation;

                        // User Info
                        document.getElementById('userName').textContent = `${reservation.Title ?? ''} ${reservation.FirstName ?? ''} ${reservation.LastName ?? ''}`.trim();
                        document.getElementById('userPhone').textContent = reservation.UserPhone ?? "N/A";

                        // Email with mailto link
                        const userEmailEl = document.getElementById('userEmail');
                        if (reservation.UserEmail) {
                            userEmailEl.textContent = reservation.UserEmail;
                            userEmailEl.href = `mailto:${reservation.UserEmail}`;
                        } else {
                            userEmailEl.textContent = "N/A";
                            userEmailEl.removeAttribute('href');
                        }

                        // Reservation date & expiry
                        document.getElementById('reservationDate').textContent = formatDate(reservation.ReservationDate);
                        if (reservation.ExpiryDate) {
                            const expiryText = `Expires on ${formatDate(reservation.CheckInDate, { year: 'numeric', month: 'short', day: 'numeric' })} ${relativeTimeFromNow(reservation.CheckInDate)}`;
                            const expiryEl = document.getElementById('reservationExpiry');
                            expiryEl.textContent = expiryText;

                            // highlight if < 2 days
                            const expiryDate = new Date(reservation.CheckInDate);
                            const diffDays = Math.ceil((expiryDate - new Date()) / (1000 * 60 * 60 * 24));
                            expiryEl.classList.remove('text-red-500', 'text-gray-500', 'text-yellow-600');
                            if (diffDays <= 2) expiryEl.classList.add('text-red-500');
                            else if (diffDays <= 7) expiryEl.classList.add('text-yellow-600');
                            else expiryEl.classList.add('text-gray-500');
                        } else {
                            document.getElementById('reservationExpiry').textContent = '';
                        }

                        // Rooms array
                        const rooms = Array.isArray(reservation.Rooms) ? reservation.Rooms : (reservation.Rooms ? [reservation.Rooms] : []);

                        // Determine nights — if different per room is needed, server must return per-room dates.
                        const checkIn = reservation.CheckInDate ? new Date(reservation.CheckInDate) : null;
                        const checkOut = reservation.CheckOutDate ? new Date(reservation.CheckOutDate) : null;
                        const nights = (checkIn && checkOut) ? Math.max(1, Math.ceil((checkOut - checkIn) / (1000 * 60 * 60 * 24))) : 1;

                        // Itemize each room and compute subtotal
                        const subtotal = rooms.reduce((sum, r) => sum + (parseFloat(r.Price) || 0), 0);
                        const taxesFees = subtotal * 0.10;
                        const pointsDiscount = parseFloat(reservation.PointsDiscount || 0) || 0;
                        const total = subtotal + taxesFees - pointsDiscount;

                        // Update UI: room breakdown (per room), subtotal, taxes, total
                        const roomRateLabel = document.getElementById('roomRateLabel');
                        const roomRateEl = document.getElementById('roomRate');
                        const taxesFeesEl = document.getElementById('taxesFees');
                        const totalPriceEl = document.getElementById('totalPrice');

                        roomRateLabel.textContent = `Rooms Subtotal (${rooms.length} room${rooms.length>1?'s':''}, ${nights} night${nights>1?'s':''}):`;
                        roomRateEl.textContent = `$ ${subtotal.toFixed(2)}`;
                        taxesFeesEl.textContent = `$ ${taxesFees.toFixed(2)}`;
                        totalPriceEl.textContent = `$ ${total.toFixed(2)}`;

                        // Points discount (show/hide)
                        if ((parseFloat(reservation.PointsRedeemed) || 0) > 0 || pointsDiscount > 0) {
                            document.getElementById('pointsDiscountContainer').classList.remove('hidden');
                            document.getElementById('pointsDiscount').textContent = `- $ ${pointsDiscount.toFixed(2)}`;
                        } else {
                            document.getElementById('pointsDiscountContainer').classList.add('hidden');
                        }

                        // Points earned (show/hide)
                        if ((parseFloat(reservation.PointsEarned) || 0) > 0) {
                            document.getElementById('pointsEarnedContainer').classList.remove('hidden');
                            document.getElementById('pointsEarned').textContent = `+ ${parseFloat(reservation.PointsEarned).toFixed(0)} points`;
                        } else {
                            document.getElementById('pointsEarnedContainer').classList.add('hidden');
                        }

                        // Create or update itemized room list under pricing
                        (function renderRoomBreakdown() {
                            // find the pricing container
                            const roomRateEl = document.getElementById('roomRate');
                            const pricingBlock = roomRateEl ? roomRateEl.closest('.space-y-3') : null;
                            if (!pricingBlock) return;

                            let breakdown = document.getElementById('roomBreakdown');
                            if (!breakdown) {
                                breakdown = document.createElement('div');
                                breakdown.id = 'roomBreakdown';
                                breakdown.className = 'space-y-1 text-sm text-gray-700';
                                // insert before taxes row (taxesFees's parent)
                                const taxesRow = document.getElementById('taxesFees')?.parentElement;
                                if (taxesRow) pricingBlock.insertBefore(breakdown, taxesRow);
                                else pricingBlock.appendChild(breakdown);
                            }
                            // fill breakdown
                            breakdown.innerHTML = rooms.map(r => {
                                const rPrice = parseFloat(r.Price || 0);
                                const perNight = nights > 0 ? (rPrice / nights) : rPrice;
                                return `<div class="flex justify-between"><span>${r.RoomName || 'Room'} <span class="text-xs text-gray-500">(${nights} night${nights>1?'s':''})</span></span><span class="font-medium">$${rPrice.toFixed(2)} <span class="text-xs text-gray-400">($${perNight.toFixed(2)}/night)</span></span></div>`;
                            }).join('');
                        })();

                        // Initialize swiper with rooms and nights so slide shows correct per-room totals
                        initializeRoomSwiper(rooms, nights);
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
                loadReservationPage(page);
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
        const searchInput = document.querySelector('input[name="reservation_search"]');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const urlParams = new URLSearchParams(window.location.search);
                urlParams.set('reservation_search', this.value);
                window.history.pushState({}, '', `?${urlParams.toString()}`);
                handleReservationSearch();
            });
        }

        // Filter select listener
        const filterSelect = document.querySelector('select[name="sort"]');
        if (filterSelect) {
            filterSelect.addEventListener('change', function() {
                const urlParams = new URLSearchParams(window.location.search);
                urlParams.set('sort', this.value);
                window.history.pushState({}, '', `?${urlParams.toString()}`);
                loadReservationPage(1); // reset to page 1 on filter change
            });
        }

        // Initial attachment of pagination listeners
        attachPaginationEventListeners();

        // Initial attachment of reservation detail buttons
        document.querySelectorAll('tbody tr').forEach(row => {
            attachReservationListenersToRow(row);
        });
    });

    // Handle browser back/forward buttons
    window.addEventListener('popstate', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const searchInput = document.querySelector('input[name="reservation_search"]');
        const filterSelect = document.querySelector('select[name="sort"]');

        if (searchInput) searchInput.value = urlParams.get('reservation_search') || '';
        if (filterSelect) filterSelect.value = urlParams.get('sort') || 'random';

        loadReservationPage(urlParams.get('reservationpage') || 1);
    });
</script>