<?php
session_start();
include('../config/dbConnection.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

$userID = (!empty($_SESSION["UserID"]) ? $_SESSION["UserID"] : null);

// Fetch reservations for the user
if ($userID) {
    $stmt = $connect->prepare("SELECT rs.*, rd.*, r.*, rt.*
                          FROM reservationtb rs
                          JOIN reservationdetailtb rd ON rs.ReservationID = rd.ReservationID
                          JOIN roomtb r ON rd.RoomID = r.RoomID 
                          JOIN roomtypetb rt ON rt.RoomTypeID = r.RoomTypeID
                          WHERE rs.UserID = ? AND rs.Status = 'Confirmed'");
    $stmt->bind_param("s", $userID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($room = $result->fetch_assoc()) {
            // Calculate nights for each room (they should have same dates)
            try {
                $checkin_date = $room['CheckInDate'];
                $checkout_date = $room['CheckOutDate'];
                $adults = $room['Adult'];
                $children = $room['Children'];
                $nights = (strtotime($checkout_date) - strtotime($checkin_date)) / (60 * 60 * 24);

                // Store room data with additional calculated fields
                $room['nights'] = $nights;
                $room['total'] = $room['Price'] * $nights * 1.1;
                $room['subtotal'] = $room['Price'] * $nights;
                $reservedRooms[] = $room;

                // Set total nights (same for all rooms in reservation)
                $totalNights = $nights;
            } catch (Exception $e) {
                // Handle invalid dates
                $alertMessage = "Invalid dates: " . $e->getMessage();
                header("Location: Reservation.php");
                exit();
            }

            $reserved_rooms[] = $room;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
    <title>Opulence Haven</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../CSS/output.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../CSS/input.css?v=<?php echo time(); ?>">
    <!-- Swiper JS -->
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
</head>

<body class="relative">
    <?php
    include('../includes/Navbar.php');
    include('../includes/Cookies.php');
    ?>

    <?php
    if ($userID) {
        $user = "SELECT * FROM usertb WHERE UserID = '$userID'";
        $result = $connect->query($user);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $Membership = $row['Membership'] ?? null;
        } else {
            $Membership = null; // No user found
        }
    } else {
        $Membership = null; // No user signed in
    }
    ?>

    <div class="max-w-6xl mx-auto p-4 md:p-6">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Upcoming Stays</h1>
            <div class="relative">
                <button class="flex items-center gap-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-2 px-4 rounded-md transition-colors">
                    <i class="ri-filter-line"></i>
                    Filter
                </button>
            </div>
        </div>

        <!-- Stats Summary -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Upcoming Stays</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo count($reserved_rooms); ?></p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full">
                        <i class="ri-calendar-check-line text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Spend</p>
                        <p class="text-2xl font-bold text-gray-800">$<?php echo number_format(array_sum(array_column($reserved_rooms, 'Price')), 2); ?></p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-full">
                        <i class="ri-wallet-line text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Reward Points</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo array_sum(array_column($reserved_rooms, 'PointsEarned')); ?></p>
                    </div>
                    <div class="p-3 bg-purple-100 rounded-full">
                        <i class="ri-star-line text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sort Options -->
        <div class="flex items-center justify-between mb-6">
            <p class="text-sm text-gray-600">Showing <?php echo count($reserved_rooms); ?> upcoming reservations</p>
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-600">Sort by:</span>
                <select class="bg-white border border-gray-300 text-gray-700 text-sm rounded-md px-3 py-1.5 focus:outline-none focus:ring-1 focus:ring-orange-500">
                    <option>Check-in date (earliest first)</option>
                    <option>Check-in date (latest first)</option>
                    <option>Price (low to high)</option>
                    <option>Price (high to low)</option>
                </select>
            </div>
        </div>

        <!-- Upcoming Stay Cards -->
        <div class="space-y-6">
            <?php foreach ($reserved_rooms as $reservation): ?>
                <?php
                $checkin = new DateTime($reservation['CheckInDate']);
                $checkout = new DateTime($reservation['CheckOutDate']);
                $nights = $checkout->diff($checkin)->days;
                $totalPrice = $reservation['Price'] * $nights;
                ?>
                <!-- Stay Card -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-200 hover:shadow-md transition-shadow">
                    <div class="md:flex">
                        <!-- Hotel Image -->
                        <div class="md:w-1/3 relative">
                            <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80"
                                alt="Hotel Room"
                                class="w-full h-48 md:h-full object-cover">
                            <div class="absolute top-4 right-4 flex gap-2">
                                <span class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">
                                    <?php echo $reservation['Status']; ?>
                                </span>
                                <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">
                                    <?php echo $reservation['PointsRedeemed'] > 0 ? 'Points Used' : 'Paid'; ?>
                                </span>
                            </div>
                        </div>

                        <!-- Stay Details -->
                        <div class="md:w-2/3 p-6">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h2 class="text-xl font-bold text-gray-800">
                                        Reservation #<?php echo $reservation['ReservationID']; ?>
                                    </h2>
                                    <div class="flex items-center mt-1 text-gray-600">
                                        <i class="ri-user-line mr-1"></i>
                                        <span><?php echo $reservation['Title'] . ' ' . $reservation['FirstName'] . ' ' . $reservation['LastName']; ?></span>
                                        <span class="mx-2 text-gray-400">•</span>
                                        <i class="ri-phone-line mr-1"></i>
                                        <span><?php echo $reservation['UserPhone']; ?></span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm text-gray-500">Total stay</div>
                                    <div class="text-sm text-gray-500">
                                        <?php echo $nights; ?> night<?php echo $nights > 1 ? 's' : ''; ?>
                                    </div>
                                    <div class="text-lg font-bold text-orange-500">
                                        $<?php echo number_format($totalPrice, 2); ?>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        <?php echo $reservation['PointsRedeemed'] > 0 ?
                                            'Used ' . $reservation['PointsRedeemed'] . ' points' :
                                            'Includes taxes & fees'; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Stay Dates and Room Info -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">Check-in</h3>
                                    <p class="font-medium"><?php echo $checkin->format('D j M Y'); ?></p>
                                    <p class="text-sm text-gray-600">3:00 PM</p>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">Check-out</h3>
                                    <p class="font-medium"><?php echo $checkout->format('D j M Y'); ?></p>
                                    <p class="text-sm text-gray-600">11:00 AM</p>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">Room Details</h3>
                                    <p class="font-medium"><?php echo $reservation['RoomName']; ?></p>
                                    <p class="text-sm text-gray-600">
                                        <?php echo $reservation['Adult']; ?> adult<?php echo $reservation['Adult'] > 1 ? 's' : ''; ?>
                                        <?php echo $reservation['Children'] > 0 ? ' • ' . $reservation['Children'] . ' child' . ($reservation['Children'] > 1 ? 'ren' : '') : ''; ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Points and Reservation Info -->
                            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="p-3 bg-gray-50 rounded-lg">
                                    <h3 class="text-sm font-medium text-gray-500">Reservation Date</h3>
                                    <p class="text-sm"><?php echo date('M j, Y H:i', strtotime($reservation['ReservationDate'])); ?></p>
                                </div>
                                <div class="p-3 bg-gray-50 rounded-lg">
                                    <h3 class="text-sm font-medium text-gray-500">Reward Points</h3>
                                    <p class="text-sm">
                                        Earned: <span class="font-medium text-green-600"><?php echo $reservation['PointsEarned']; ?></span>
                                        <?php if ($reservation['PointsRedeemed'] > 0): ?>
                                            • Redeemed: <span class="font-medium text-orange-600"><?php echo $reservation['PointsRedeemed']; ?></span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="mt-6 flex flex-col sm:flex-row gap-3">
                                <button
                                    class="flex-1 flex items-center justify-center gap-2 bg-orange-500 hover:bg-orange-600 text-white font-medium py-2.5 px-4 rounded-md transition-colors">
                                    <i class="ri-file-list-line"></i> View details
                                </button>
                                <button class="flex-1 flex items-center justify-center gap-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-2.5 px-4 rounded-md transition-colors">
                                    <i class="ri-pencil-line"></i> Modify
                                </button>
                                <button onclick="showCancelModal('<?php echo $reservation['ReservationID']; ?>', '<?php echo $checkin->format('M j, Y'); ?> - <?php echo $checkout->format('M j, Y'); ?>', <?php echo $totalPrice; ?>)"
                                    class="flex-1 flex items-center justify-center gap-2 bg-white border border-red-300 hover:bg-red-50 text-red-600 font-medium py-2.5 px-4 rounded-md transition-colors">
                                    <i class="ri-close-line"></i> Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <div class="mt-8 flex items-center justify-between">
            <div class="text-sm text-gray-600">
                Showing <span class="font-medium">1</span> to <span class="font-medium"><?php echo count($reserved_rooms); ?></span> of <span class="font-medium"><?php echo count($reserved_rooms); ?></span> results
            </div>
            <div class="flex gap-1">
                <button class="px-3 py-1.5 rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                    Previous
                </button>
                <button class="px-3 py-1.5 rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 font-medium">
                    1
                </button>
                <button class="px-3 py-1.5 rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                    Next
                </button>
            </div>
        </div>
    </div>

    <div id="reservationDetailModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-100 p-2 transition-all duration-300">
        <div class="bg-white rounded-xl max-w-4xl w-full p-6 animate-fade-in overflow-y-auto max-h-[700px]">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-800">Reservation Details</h3>
                <button onclick="document.getElementById('reservationDetailModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-500">
                    <i class="ri-close-line text-xl"></i>
                </button>
            </div>

            <div id="reservationDetailContent">
                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-medium text-gray-800 mb-3">Guest Information</h4>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Name:</span>
                                    <span class="text-sm font-medium"><?= $reservation['Title'] ?> <?= $reservation['FirstName'] ?> <?= $reservation['LastName'] ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Phone:</span>
                                    <span class="text-sm font-medium"><?= $reservation['UserPhone'] ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Reservation Date:</span>
                                    <span class="text-sm font-medium"><?= date('M j, Y H:i', strtotime($reservation['ReservationDate'])) ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-medium text-gray-800 mb-3">Stay Details</h4>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Check-in:</span>
                                    <span class="text-sm font-medium"><?= $checkin->format('D, M j, Y') ?> at 3:00 PM</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Check-out:</span>
                                    <span class="text-sm font-medium"><?= $checkout->format('D, M j, Y') ?> at 11:00 AM</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Duration:</span>
                                    <span class="text-sm font-medium"><?= $nights . ' night' . ($nights > 1 ? 's' : '') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium text-gray-800 mb-3">Room Information</h4>
                        <div class="flex flex-col md:flex-row gap-4">
                            <div class="md:w-1/3">
                                <img src="../Admin/<?= $reservation['RoomCoverImage'] ?>"
                                    alt="Room Image"
                                    class="w-full h-auto rounded-lg">
                            </div>
                            <div class="md:w-2/3">
                                <h5 class="font-bold text-lg"><?= $reservation['RoomName'] ?></h5>
                                <p class="text-sm text-gray-600 mt-1"><?= $reservation['RoomDescription'] ?></p>

                                <div class="mt-4 grid grid-cols-2 gap-4">
                                    <div>
                                        <span class="text-sm text-gray-600">Adults:</span>
                                        <span class="text-sm font-medium ml-2"><?= $reservation['Adult'] ?></span>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-600">Children:</span>
                                        <span class="text-sm font-medium ml-2"><?= $reservation['Children'] ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium text-gray-800 mb-3">Pricing Breakdown</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Room Rate (<?= $nights . ' night' . ($nights > 1 ? 's' : '') ?>):</span>
                                <span class="text-sm font-medium">$ <?= number_format($reservation['Price'] * $nights, 2) ?></span>
                            </div>

                            <?php if ($reservation['PointsRedeemed'] > 0) { ?>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Points Discount (<?= $reservation['PointsRedeemed'] ?>points):</span>
                                    <span class="text-sm font-medium text-green-600">-$ <?= number_format($reservation['PointsDiscount'], 2) ?></span>
                                </div>
                            <?php }
                            ?>

                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Taxes & Fees:</span>
                                <span class="text-sm font-medium">$ <?= number_format($totalPrice * 0.1, 2) ?></span>
                            </div>
                            <div class="border-t border-gray-200 pt-2 flex justify-between">
                                <span class="font-medium text-gray-800">Total:</span>
                                <span class="font-bold">$ <?= number_format($totalPrice * 1.1, 2) ?></span>
                            </div>

                            <?php

                            if ($reservation['PointsEarned'] > 0) {
                            ?>
                                <div class="pt-2 flex justify-between">
                                    <span class="text-sm text-gray-600">Points Earned:</span>
                                    <span class="text-sm font-medium text-blue-600">+ <?= $reservation['PointsEarned'] ?> points</span>
                                </div>
                            <?php }
                            ?>
                        </div>
                    </div>

                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="ri-information-line text-blue-500 mt-1"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-medium text-blue-800">Cancellation Policy</h4>
                                <p class="text-sm text-blue-700 mt-1">
                                    Free cancellation until ' . $cancellationDeadline->format('M j, Y') . '.
                                    After this date, a cancellation fee of $50 will apply.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancellation Modal -->
    <div id="cancelModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-xl max-w-md w-full p-6 animate-fade-in">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-800">Cancel Reservation</h3>
                <button onclick="document.getElementById('cancelModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-500">
                    <i class="ri-close-line text-xl"></i>
                </button>
            </div>

            <p class="text-gray-600 mb-4">Are you sure you want to cancel your reservation <span id="cancelReservationId" class="font-medium"></span> for dates <span id="cancelReservationDates" class="font-medium"></span>?</p>

            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="ri-alert-line text-yellow-500"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            Cancelling before <span id="cancelDeadline"></span> will result in a full refund. After this date, a cancellation fee may apply.
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                <div class="flex justify-between mb-2">
                    <span class="text-sm text-gray-600">Refund amount:</span>
                    <span class="text-sm font-medium text-gray-800" id="refundAmount"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Cancellation fee:</span>
                    <span class="text-sm font-medium text-gray-800" id="cancellationFee">$0.00</span>
                </div>
                <div class="border-t border-gray-200 mt-2 pt-2 flex justify-between">
                    <span class="text-sm font-medium text-gray-800">Total refund:</span>
                    <span class="text-sm font-bold text-green-600" id="totalRefund"></span>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 mt-6">
                <button onclick="document.getElementById('cancelModal').classList.add('hidden')" class="flex-1 flex items-center justify-center gap-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-2.5 px-4 rounded-md transition-colors">
                    <i class="ri-arrow-left-line"></i> Go back
                </button>
                <button id="confirmCancelBtn" class="flex-1 flex items-center justify-center gap-2 bg-red-600 hover:bg-red-700 text-white font-medium py-2.5 px-4 rounded-md transition-colors">
                    <i class="ri-check-line"></i> Confirm cancellation
                </button>
            </div>
        </div>
    </div>

    <script>
        // Function to show cancellation modal
        function showCancelModal(reservationId, dates, totalPrice) {
            // Calculate deadline (3 days before check-in)
            const checkinDate = new Date(dates.split(' - ')[0]);
            const deadline = new Date(checkinDate);
            deadline.setDate(deadline.getDate() - 3);

            // Set modal content
            document.getElementById('cancelReservationId').textContent = reservationId;
            document.getElementById('cancelReservationDates').textContent = dates;
            document.getElementById('cancelDeadline').textContent = deadline.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });
            document.getElementById('refundAmount').textContent = '$' + totalPrice.toFixed(2);
            document.getElementById('totalRefund').textContent = '$' + totalPrice.toFixed(2);

            // Set up confirm button
            document.getElementById('confirmCancelBtn').onclick = function() {
                cancelReservation(reservationId);
            };

            document.getElementById('cancelModal').classList.remove('hidden');
        }

        // Function to cancel reservation
        function cancelReservation(reservationId) {
            fetch(`cancel_reservation.php?reservation_id=${reservationId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Reservation cancelled successfully');
                        location.reload();
                    } else {
                        alert('Failed to cancel reservation: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to cancel reservation');
                });
        }
    </script>

    <!-- MoveUp Btn -->
    <?php
    include('../includes/MoveUpBtn.php');
    include('../includes/Footer.php');
    ?>

    <script src="//unpkg.com/alpinejs" defer></script>
    <script type="module" src="../JS/index.js"></script>
</body>

</html>