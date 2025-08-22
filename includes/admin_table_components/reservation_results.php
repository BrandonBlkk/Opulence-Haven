<?php
require_once(__DIR__ . '/../../config/db_connection.php');
include(__DIR__ . '/../admin_pagination.php');

// Construct the reservation query based on search and status filter
if ($filterStatus !== 'random' && !empty($searchBookingQuery)) {
    $bookingSelect = "SELECT r.*, u.UserName, u.UserPhone 
                     FROM reservationtb r 
                     JOIN usertb u ON r.UserID = u.UserID  
                     WHERE r.Status = '$filterStatus' 
                     AND (r.FirstName LIKE '%$searchBookingQuery%' 
                          OR r.LastName LIKE '%$searchBookingQuery%'
                          OR r.UserPhone LIKE '%$searchBookingQuery%'
                          OR r.ReservationID LIKE '%$searchBookingQuery%'
                          OR u.UserName LIKE '%$searchBookingQuery%') 
                     LIMIT $rowsPerPage OFFSET $reservationOffset";
} elseif ($filterStatus !== 'random') {
    $bookingSelect = "SELECT r.*, u.UserName, u.UserPhone 
                     FROM reservationtb r 
                     JOIN usertb u ON r.UserID = u.UserID 
                     WHERE r.Status = '$filterStatus' 
                     LIMIT $rowsPerPage OFFSET $reservationOffset";
} elseif (!empty($searchBookingQuery)) {
    $bookingSelect = "SELECT r.*, u.UserName, u.UserPhone 
                     FROM reservationtb r 
                     JOIN usertb u ON r.UserID = u.UserID 
                     WHERE (r.FirstName LIKE '%$searchBookingQuery%'
                           OR r.LastName LIKE '%$searchBookingQuery%'
                           OR r.UserPhone LIKE '%$searchBookingQuery%'
                           OR r.ReservationID LIKE '%$searchBookingQuery%'
                           OR u.UserName LIKE '%$searchBookingQuery%') 
                     LIMIT $rowsPerPage OFFSET $reservationOffset";
} else {
    $bookingSelect = "SELECT r.*, u.UserName, u.UserPhone 
                     FROM reservationtb r
                     JOIN usertb u ON r.UserID = u.UserID 
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
                    <td class="p-3 text-start">
                        <div class="font-medium">
                            <?= htmlspecialchars($booking['Title'] . ' ' . $booking['UserName'] . ' ' . $booking['LastName']) ?>
                        </div>
                        <div class="text-xs text-gray-400">
                            <?= htmlspecialchars($booking['Travelling'] === 1 ? 'Travelling' : 'Not Travelling') ?>
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
                        <span class="px-2 py-1 text-xs font-semibold rounded-full border <?= $statusClass ?>">
                            <?= htmlspecialchars($booking['Status']) ?>
                        </span>
                    </td>
                    <td class="p-3 text-start whitespace-nowrap">
                        <i class="details-btn ri-eye-line text-lg cursor-pointer"
                            data-reservation-id="<?= htmlspecialchars($booking['ReservationID']) ?>"></i>
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

        // Update URL parameters
        urlParams.set('reservationpage', page);
        if (searchQuery) urlParams.set('reservation_search', searchQuery);

        const xhr = new XMLHttpRequest();
        xhr.open('GET', `../includes/admin_table_components/reservation_results.php?${urlParams.toString()}`, true);

        xhr.onload = function() {
            if (this.status === 200) {
                document.getElementById('reservationResults').innerHTML = this.responseText;

                // Also update the pagination controls
                const xhrPagination = new XMLHttpRequest();
                xhrPagination.open('GET', `../includes/admin_table_components/reservation_pagination.php?${urlParams.toString()}`, true);
                xhrPagination.onload = function() {
                    if (this.status === 200) {
                        document.getElementById('paginationContainer').innerHTML = this.responseText;
                        // Re-attach event listeners after pagination update
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

    // Function to attach pagination event listeners
    function attachPaginationEventListeners() {
        // Page number buttons
        document.querySelectorAll('.page-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const page = this.getAttribute('data-page');
                loadReservationPage(parseInt(page));
            });
        });

        // Previous button
        const prevBtn = document.querySelector('.prev-page-btn');
        if (prevBtn) {
            prevBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const currentPage = <?= $reservationCurrentPage ?>;
                if (currentPage > 1) {
                    loadReservationPage(currentPage - 1);
                }
            });
        }

        // Next button
        const nextBtn = document.querySelector('.next-page-btn');
        if (nextBtn) {
            nextBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const currentPage = <?= $reservationCurrentPage ?>;
                const totalPages = <?= $totalReservationPages ?>;
                if (currentPage < totalPages) {
                    loadReservationPage(currentPage + 1);
                }
            });
        }
    }

    // Initialize event listeners
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

        // Initial attachment of pagination listeners
        attachPaginationEventListeners();
    });

    // Handle browser back/forward buttons
    window.addEventListener('popstate', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const searchInput = document.querySelector('input[name="reservation_search"]');
        if (searchInput) {
            searchInput.value = urlParams.get('reservation_search') || '';
        }
        loadReservationPage(urlParams.get('reservationpage') || 1);
    });
</script>