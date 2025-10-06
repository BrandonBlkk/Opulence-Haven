<?php foreach ($reservations as $data): ?>
    <?php
    $reservationID = $data['ReservationID'];

    // Query to get all rooms for this reservation
    $reservation = "SELECT rd.*, r.*, rb.*, rtb.*, u.UserName, u.UserPhone 
                FROM reservationdetailtb rd
                JOIN reservationtb r ON rd.ReservationID = r.ReservationID
                JOIN roomtb rb ON rd.RoomID = rb.RoomID
                JOIN roomtypetb rtb ON rb.RoomTypeID = rtb.RoomTypeID
                JOIN usertb u ON r.UserID = u.UserID 
                WHERE rd.ReservationID = '$reservationID'";

    $result = $connect->query($reservation);
    $rooms = [];

    while ($roomData = $result->fetch_assoc()) {
        $rooms[] = $roomData;
    }

    // Use first room for main details
    $data = $rooms[0];

    // Find earliest check-in and latest check-out
    $earliestCheckin = null;
    $latestCheckout = null;
    foreach ($rooms as $room) {
        $currentCheckin = new DateTime($room['CheckInDate']);
        $currentCheckout = new DateTime($room['CheckOutDate']);
        if ($earliestCheckin === null || $currentCheckin < $earliestCheckin) {
            $earliestCheckin = $currentCheckin;
        }
        if ($latestCheckout === null || $currentCheckout > $latestCheckout) {
            $latestCheckout = $currentCheckout;
        }
    }

    $totalNights = $latestCheckout->diff($earliestCheckin)->days;

    // Total price
    $totalPrice = 0;
    foreach ($rooms as $room) {
        $roomCheckin = new DateTime($room['CheckInDate']);
        $roomCheckout = new DateTime($room['CheckOutDate']);
        $roomNights = $roomCheckout->diff($roomCheckin)->days;
        $totalPrice += $room['Price'] * $roomNights;
    }

    // Reservation status
    $today = new DateTime();
    $today->setTime(0, 0, 0);
    $earliestCheckin->setTime(0, 0, 0);
    $latestCheckout->setTime(0, 0, 0);

    if ($today < $earliestCheckin) {
        $reservationStatus = "Upcoming";
        $statusColor = "bg-blue-50 border-blue-400 text-blue-800";
    } elseif ($today >= $earliestCheckin && $today <= $latestCheckout) {
        $reservationStatus = "Ongoing";
        $statusColor = "bg-green-50 border-green-400 text-green-800";
    } else {
        $reservationStatus = "Completed";
        $statusColor = "bg-gray-50 border-gray-400 text-gray-800";
    }
    ?>

    <!-- Stay Card -->
    <div class="bg-white rounded-sm border-2 border-gray-100">
        <div class="md:flex">
            <!-- Hotel Image -->
            <div class="md:w-1/3 relative select-none">
                <img src="../Admin/<?php echo $data['RoomCoverImage']; ?>"
                    alt="Hotel Room"
                    class="w-full h-40 md:h-full object-cover">
                <div class="absolute top-3 right-3">
                    <span class="<?php echo $statusColor; ?> text-[11px] font-medium px-2.5 py-0.5 rounded-full flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full 
                            <?php echo $reservationStatus === 'Upcoming' ? 'bg-blue-500' : ($reservationStatus === 'Ongoing' ? 'bg-green-500' : 'bg-gray-500'); ?>">
                        </span>
                        <?php echo $reservationStatus; ?>
                    </span>
                </div>
            </div>

            <!-- Stay Details -->
            <div class="md:w-2/3 p-4">
                <div class="flex flex-col sm:flex-row justify-between items-start gap-3">
                    <div class="flex-1">
                        <h2 class="text-lg font-semibold text-gray-900">
                            Reservation #<?php echo $data['ReservationID']; ?>
                        </h2>
                        <div class="flex flex-wrap items-center mt-1.5 text-gray-600 gap-x-3 gap-y-1 text-sm">
                            <span class="flex items-center">
                                <i class="ri-user-line mr-1 text-gray-400"></i>
                                <?php echo $data['Title'] . ' ' . $data['FirstName'] . ' ' . $data['LastName']; ?>
                            </span>
                            <span class="flex items-center">
                                <i class="ri-phone-line mr-1 text-gray-400"></i>
                                <?php echo $data['UserPhone']; ?>
                            </span>
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <div class="text-xs text-gray-500">Total stay</div>
                        <div class="text-sm font-medium text-gray-700">
                            <?php echo $totalNights; ?> night<?php echo $totalNights > 1 ? 's' : ''; ?>
                        </div>
                        <div class="text-lg font-bold text-orange-600 mt-0.5">
                            $<?= number_format($totalPrice * 1.1, 2) ?>
                        </div>
                        <div class="text-xs text-gray-500 mt-0.5">
                            <?php echo $data['PointsRedeemed'] > 0 ? 'Used ' . $data['PointsRedeemed'] . ' points' : 'Includes taxes & fees'; ?>
                        </div>
                    </div>
                </div>

                <!-- Stay Dates and Room Info -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-4 text-sm">
                    <div class="bg-gray-50 p-2.5 rounded-md">
                        <h3 class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">Check-in</h3>
                        <p class="font-medium text-gray-700 mt-0.5"><?php echo $earliestCheckin->format('D, j M Y'); ?></p>
                        <p class="text-xs text-gray-500">After 2:00 PM</p>
                    </div>
                    <div class="bg-gray-50 p-2.5 rounded-md">
                        <h3 class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">Check-out</h3>
                        <p class="font-medium text-gray-700 mt-0.5"><?php echo $latestCheckout->format('D, j M Y'); ?></p>
                        <p class="text-xs text-gray-500">Before 12:00 PM</p>
                    </div>
                    <div class="bg-gray-50 p-2.5 rounded-md">
                        <h3 class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">Room Details</h3>
                        <div class="flex flex-wrap items-center gap-1.5 mt-0.5">
                            <?php foreach ($rooms as $room): ?>
                                <div class="relative group">
                                    <div class="bg-blue-50 hover:bg-blue-100 px-2.5 py-0.5 rounded-md border border-blue-100 text-xs font-medium text-blue-700 cursor-default">
                                        <?php echo htmlspecialchars($room['RoomName']); ?>
                                    </div>
                                    <!-- Tooltip -->
                                    <div class="absolute z-10 bottom-full left-1/2 transform -translate-x-1/2 mb-1 hidden group-hover:block animate-fadeIn">
                                        <div class="bg-gray-800 text-white text-xs px-2.5 py-1 rounded-md shadow-md whitespace-nowrap">
                                            <?php echo htmlspecialchars($room['RoomType']); ?>
                                            <div class="absolute left-1/2 -bottom-1 transform -translate-x-1/2 w-2 h-2 bg-gray-800 rotate-45"></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Points and Reservation Info -->
                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                    <div class="bg-gray-50 p-2.5 rounded-md">
                        <h3 class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">Reservation Date</h3>
                        <p class="text-gray-700 mt-0.5"><?php echo date('M j, Y \a\t H:i', strtotime($data['ReservationDate'])); ?></p>
                    </div>
                    <div class="bg-gray-50 p-2.5 rounded-md">
                        <h3 class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">Reward Points</h3>
                        <p class="text-gray-700 mt-0.5">
                            <span class="font-medium text-green-600">+<?php echo $data['PointsEarned']; ?> earned</span>
                            <?php if ($data['PointsRedeemed'] > 0): ?>
                                <span class="mx-1 text-gray-300">|</span>
                                <span class="font-medium text-orange-600">-<?php echo $data['PointsRedeemed']; ?> redeemed</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-5 flex flex-col sm:flex-row gap-2.5 select-none">
                    <button data-reservation-id="<?= htmlspecialchars($data['ReservationID']) ?>"
                        class="details-btn flex-1 flex items-center justify-center gap-1.5 bg-amber-500 hover:bg-amber-600 text-white font-medium px-4 py-2 rounded-sm transition-all shadow-sm">
                        <i class="ri-file-list-line"></i> View Details
                    </button>
                    <?php
                    // Calculate total adults, children, infants for the reservation
                    $totalAdults = 0;
                    $totalChildren = 0;
                    $totalInfants = 0; // if your DB has infants field
                    foreach ($rooms as $room) {
                        $totalAdults += $room['Adult'];
                        $totalChildren += $room['Children'];
                        $totalInfants += $room['Infants'] ?? 0;
                    }
                    ?>

                    <a
                        <?php if ($reservationStatus !== 'Completed'): ?>
                        href="../User/reservation.php?modify_reservation_id=<?= htmlspecialchars($data['ReservationID']) ?>&checkin_date=<?= urlencode($earliestCheckin->format('Y-m-d')) ?>&checkout_date=<?= urlencode($latestCheckout->format('Y-m-d')) ?>&adults=<?= urlencode($totalAdults) ?>&children=<?= urlencode($totalChildren) ?>&infants=<?= urlencode($totalInfants) ?>"
                        <?php endif; ?>
                        class="flex-1 flex items-center justify-center gap-1.5 border border-gray-200 
                                        <?= $reservationStatus === 'Completed'
                                            ? 'text-gray-400 cursor-not-allowed'
                                            : 'bg-white hover:border-gray-300 text-gray-700' ?> 
                                        font-medium px-4 py-2 rounded-sm transition-all shadow-sm">
                        <i class="ri-pencil-line"></i> Modify
                    </a>
                    <button
                        <?php if ($reservationStatus === 'Completed') echo 'disabled'; ?>
                        class="openCancelModalBtn flex-1 flex items-center justify-center gap-1.5 px-4 py-2 rounded-sm transition-all shadow-sm font-medium
                                        <?= $reservationStatus === 'Completed' ? 'border border-gray-200 text-gray-400 cursor-not-allowed' : 'bg-white border border-red-200 hover:border-red-300 text-red-600' ?>"
                        data-reservation-id="<?= htmlspecialchars($data['ReservationID']) ?>"
                        data-dates="<?= $earliestCheckin->format('Y-m-d') ?> - <?= $latestCheckout->format('Y-m-d') ?>"
                        data-total-price="<?= $totalPrice * 1.1 ?>">
                        <i class="ri-close-line"></i> Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>