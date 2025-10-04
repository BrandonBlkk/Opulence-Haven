<?php
$reservation = "SELECT rt.ReservationID, rt.UserID, rt.ReservationDate, rt.Status, rt.TotalPrice, u.ProfileBgColor, u.UserName FROM reservationtb rt
        JOIN usertb u ON rt.UserID = u.UserID 
        ORDER BY rt.ReservationDate DESC LIMIT 4";
$stmt = $connect->prepare($reservation);
$stmt->execute();
$resResult = $stmt->get_result();
$count = $resResult->num_rows;
?>

<div id="notificationModal" class="fixed top-4 right-4 hidden z-50 w-full max-w-xs md:max-w-sm transition-all duration-300">
    <div id="notificationContent" class="bg-white p-4 relative transform scale-95 opacity-0 transition-all duration-300 rounded-lg shadow-lg overflow-hidden">
        <!-- Close Button -->
        <button id="closeNotificationModal" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">
            <i class="ri-close-line text-xl"></i>
        </button>

        <h3 class="text-lg font-semibold text-gray-700 mb-3">Notifications</h3>
        <div class="space-y-3 divide-y-2 divide-gray-100 max-h-[60vh] overflow-y-auto pr-1">
            <?php
            if ($count > 0) {
                while ($row = $resResult->fetch_assoc()):
                    $bgColor = $row['ProfileBgColor'];
                    $status = $row['Status'];

                    // Set status color
                    $statusColor = match ($status) {
                        'Confirmed' => 'bg-green-100 text-green-800 border-green-200',
                        'Pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                        'Cancelled' => 'bg-red-100 text-red-800 border-red-200',
                        default => 'bg-gray-100 text-gray-800 border-gray-200'
                    };

                    $noti = match ($status) {
                        'Confirmed' => 'has confirmed a reservation',
                        'Pending' => 'has a pending reservation',
                        'Cancelled' => 'cancelled a reservation',
                        default => 'updated reservation'
                    };

                    // Extract initials
                    $nameParts = explode(' ', trim($row['UserName']));
                    $initials = strtoupper(substr($nameParts[0], 0, 1));
                    if (count($nameParts) > 1) {
                        $initials .= strtoupper(substr(end($nameParts), 0, 1));
                    }

                    // Calculate "time ago"
                    $reservationDate = new DateTime($row['ReservationDate']);
                    $currentDate = new DateTime();
                    $interval = $currentDate->diff($reservationDate);

                    if ($interval->y > 0) {
                        $timeAgo = $interval->y . ' year' . ($interval->y > 1 ? 's' : '') . ' ago';
                    } elseif ($interval->m > 0) {
                        $timeAgo = $interval->m . ' month' . ($interval->m > 1 ? 's' : '') . ' ago';
                    } elseif ($interval->d >= 7) {
                        $weeks = floor($interval->d / 7);
                        $timeAgo = $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ago';
                    } elseif ($interval->d > 0) {
                        $timeAgo = $interval->d . ' day' . ($interval->d > 1 ? 's' : '') . ' ago';
                    } elseif ($interval->h > 0) {
                        $timeAgo = $interval->h . ' hour' . ($interval->h > 1 ? 's' : '') . ' ago';
                    } elseif ($interval->i > 0) {
                        $timeAgo = $interval->i . ' minute' . ($interval->i > 1 ? 's' : '') . ' ago';
                    } else {
                        $timeAgo = 'Just now';
                    }
            ?>
                    <div class="p-2 cursor-pointer transition flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-[<?= htmlspecialchars($bgColor) ?>] text-white font-semibold flex items-center justify-center select-none shrink-0">
                            <?= htmlspecialchars($initials) ?>
                        </div>
                        <div class="text-gray-600 text-sm flex-1">
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                                <h1 class="font-semibold mb-1 sm:mb-0"><?= htmlspecialchars($nameParts[0]) ?> <?= $noti ?></h1>
                                <span class="text-xs px-2 py-1 rounded-full select-none border <?= $statusColor ?>">
                                    <?= htmlspecialchars($status) ?>
                                </span>
                            </div>
                            <div class="flex justify-between items-center mt-1 text-xs text-gray-500">
                                <p><?= $timeAgo ?></p>
                                <p class="font-medium">$<?= number_format($row['TotalPrice'], 2) ?></p>
                            </div>
                        </div>
                    </div>
            <?php
                endwhile;
            } else {
                echo '<p class="text-gray-500 text-center text-sm py-4">No reservations found.</p>';
            }
            ?>
        </div>
    </div>
</div>