<?php
session_start();
include('../config/dbConnection.php');
include('../includes/AutoIDFunc.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

$userID = (!empty($_SESSION["UserID"]) ? $_SESSION["UserID"] : null);
$alertMessage = '';

$user = "SELECT * FROM usertb WHERE UserID = '$userID'";
$userData = $connect->query($user)->fetch_assoc();

$nameParts = explode(' ', trim($userData['UserName']));
$initials = substr($nameParts[0], 0, 1);
if (count($nameParts) > 1) {
    $initials .= substr(end($nameParts), 0, 1);
}

// Get search parameters from URL with strict validation
if (isset($_GET["roomID"])) {
    $room_id = $_GET["roomID"];
    $checkin_date = isset($_GET['checkin_date']) ? htmlspecialchars($_GET['checkin_date']) : '';
    $checkout_date = isset($_GET['checkout_date']) ? htmlspecialchars($_GET['checkout_date']) : '';
    $adults = isset($_GET['adults']) ? (int)$_GET['adults'] : 1;
    $children = isset($_GET['children']) ? (int)$_GET['children'] : 0;
    $totalGuest = $adults + $children;

    // Calculate total nights stay
    $totalNights = 0;
    if (!empty($checkin_date) && !empty($checkout_date)) {
        $checkin = new DateTime($checkin_date);
        $checkout = new DateTime($checkout_date);

        // Ensure checkout is after checkin
        if ($checkout > $checkin) {
            $interval = $checkin->diff($checkout);
            $totalNights = $interval->days;
        }
    }

    $query = "SELECT r.*, rt.* FROM roomtb r 
          JOIN roomtypetb rt ON r.RoomTypeID = rt.RoomTypeID 
          WHERE r.RoomID = '$room_id'";

    $room = $connect->query($query)->fetch_assoc();
}

// Add room to favorites
if (isset($_POST['room_favourite'])) {
    if ($userID) {
        $room_id = $_GET["roomID"];
        $roomTypeID = $_POST['roomTypeID'];

        // Get search parameters from POST data (they should be included in the form submission)
        $checkin_date = isset($_POST['checkin_date']) ? $_POST['checkin_date'] : '';
        $checkout_date = isset($_POST['checkout_date']) ? $_POST['checkout_date'] : '';
        $adults = isset($_POST['adults']) ? intval($_POST['adults']) : 1;
        $children = isset($_POST['children']) ? intval($_POST['children']) : 0;

        $check = "SELECT COUNT(*) as count FROM roomtypefavoritetb WHERE UserID = '$userID' AND RoomTypeID = '$roomTypeID'";
        $result = $connect->query($check);
        $count = $result->fetch_assoc()['count'];

        if ($count == 0) {
            $insert = "INSERT INTO roomtypefavoritetb (UserID, RoomTypeID, CheckInDate, CheckOutDate, Adult, Children) 
                      VALUES ('$userID', '$roomTypeID', '$checkin_date', '$checkout_date', '$adults', '$children')";
            $connect->query($insert);
        } else {
            $delete = "DELETE FROM roomtypefavoritetb WHERE UserID = '$userID' AND RoomTypeID = '$roomTypeID'";
            $connect->query($delete);
        }

        // Redirect back with the same search parameters
        $redirect_url = "Reservation.php?roomID=$room_id&checkin_date=$checkin_date&checkout_date=$checkout_date&adults=$adults&children=$children";
        header("Location: $redirect_url");
        exit();
    } else {
        $showLoginModal = true;
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
</head>

<body class="relative min-w-[380px]">
    <?php
    include('../includes/Navbar.php');
    include('../includes/Cookies.php');
    ?>

    <div class="max-w-[1150px] mx-auto px-4 py-8">
        <!-- Progress Steps -->
        <div class="flex justify-between items-center mb-8">
            <div class="flex-1 text-center">
                <div class="w-8 h-8 mx-auto rounded-full bg-blue-600 text-white flex items-center justify-center mb-2">1</div>
                <p class="text-sm font-medium text-blue-600">Your selection</p>
            </div>
            <div class="flex-1 border-t-2 border-blue-600"></div>
            <div class="flex-1 text-center">
                <div class="w-8 h-8 mx-auto rounded-full bg-blue-600 text-white flex items-center justify-center mb-2">2</div>
                <p class="text-sm font-medium text-blue-600">Enter your details</p>
            </div>
            <div class="flex-1 border-t-2 border-gray-300"></div>
            <div class="flex-1 text-center">
                <div class="w-8 h-8 mx-auto rounded-full bg-gray-300 text-gray-600 flex items-center justify-center mb-2">3</div>
                <p class="text-sm font-medium text-gray-600">Confirm your reservation</p>
            </div>
        </div>

        <!-- Main Content -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <!-- Header -->
            <div class="bg-blue-50 p-6 border-b border-gray-200">
                <h1 class="text-2xl font-bold text-gray-800">Great choice! You're almost done.</h1>
            </div>

            <!-- Booking Details -->
            <div class="p-6 border-b border-gray-200">
                <div class="flex flex-col md:flex-row gap-7">
                    <!-- Left Column - Booking Information -->
                    <div class="w-full md:w-1/3">
                        <div class="space-y-6">
                            <!-- Booking Summary -->
                            <div class="bg-white rounded-lg shadow-sm">
                                <h3 class="text-lg font-semibold text-white mb-4 border-b border-gray-100 bg-blue-900 p-2">Your booking detailss</h3>
                                <div class="space-y-2">
                                    <div>
                                        <p class="text-sm font-medium text-gray-500 mb-1">Check-in:</p>
                                        <p class="text-xs font-semibold text-gray-800"><?= date('D, j M Y', strtotime($checkin_date)); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500 mb-1">Check-out:</p>
                                        <p class="text-xs font-semibold text-gray-800"><?= date('D, j M Y', strtotime($checkout_date)); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500 mb-1">Total stay:</p>
                                        <p class="text-xs font-semibold text-gray-800"><?= $totalNights ?> <?= $totalNights > 1 ? 'nights' : 'night'; ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500 mb-1">Guests:</p>
                                        <p class="text-xs font-semibold text-gray-800">
                                            <?= $adults ?> adult<?= $adults > 1 ? 's' : '' ?>
                                            <?= $children > 0 ? ', ' . $children . ' child' . ($children > 1 ? 'ren' : '') : '' ?>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Price Summary -->
                            <div class="bg-white rounded-lg shadow-sm">
                                <h3 class="text-lg font-semibold text-white mb-4 border-b border-gray-100 bg-blue-900 p-2">Price Summary</h3>
                                <div class="space-y-3">
                                    <?php
                                    // Query to get all reservation details with room information
                                    $detailsQuery = "SELECT rd.*, rt.RoomType, r.RoomName 
                    FROM reservationdetailtb rd
                    JOIN roomtb r ON rd.RoomID = r.RoomID
                    JOIN roomtypetb rt ON r.RoomTypeID = rt.RoomTypeID
                    WHERE rd.ReservationID = '$reservationID'";
                                    $detailsResult = mysqli_query($connect, $detailsQuery);

                                    if (!$detailsResult) {
                                        die("Error fetching reservation details: " . mysqli_error($connect));
                                    }

                                    $grandTotal = 0;

                                    if (mysqli_num_rows($detailsResult) > 0) {
                                        while ($roomItem = mysqli_fetch_assoc($detailsResult)) {
                                            $checkIn = new DateTime($roomItem['CheckInDate']);
                                            $checkOut = new DateTime($roomItem['CheckOutDate']);
                                            $totalNights = $checkOut->diff($checkIn)->days;
                                            $roomTotal = $roomItem['Price'] * $totalNights;
                                            $grandTotal += $roomTotal;
                                    ?>
                                            <div class="flex justify-between items-center">
                                                <p class="text-sm font-medium text-gray-600">
                                                    <?= htmlspecialchars($roomItem['RoomName'] ?? 'Room') ?> <?= htmlspecialchars($roomItem['RoomType']) ?>
                                                    × <?= $totalNights ?> night<?= $totalNights > 1 ? 's' : '' ?>
                                                </p>
                                                <p class="text-sm font-semibold text-gray-800">$<?= number_format($roomTotal, 2) ?></p>
                                            </div>
                                    <?php
                                        }
                                    } else {
                                        echo "<p class='text-sm text-gray-600'>No rooms added to reservation yet.</p>";
                                    }
                                    ?>

                                    <div class="pt-3 border-t border-gray-100">
                                        <div class="flex justify-between items-center">
                                            <p class="text-base font-semibold text-gray-800">Total</p>
                                            <p class="text-lg font-bold text-blue-600">$<?= number_format($grandTotal, 2) ?></p>
                                        </div>
                                        <p class="text-xs text-green-600 mt-1">✓ Includes all taxes and fees</p>
                                    </div>

                                    <div class="pt-2">
                                        <p class="text-xs text-gray-500">
                                            <i class="ri-information-line"></i> Price in USD. Your card issuer may apply foreign transaction fees.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Room Information (Keep existing code exactly as is) -->
                    <div class="w-full">
                        <a href="../User/RoomDetails.php?roomTypeID=<?php echo htmlspecialchars($room['RoomTypeID']) ?>&checkin_date=<?= $checkin_date ?>&checkout_date=<?= $checkout_date ?>&adults=<?= $adults ?>&children=<?= $children ?>" class="flex flex-col md:flex-row rounded-md shadow-sm border">
                            <div class="md:w-[48%] h-64 overflow-hidden select-none rounded-l-md relative">
                                <img src="../Admin/<?= htmlspecialchars($room['RoomCoverImage']) ?>" alt="<?= htmlspecialchars($room['RoomType']) ?>" class="w-full h-full object-cover">
                                <form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post">
                                    <input type="hidden" name="checkin_date" value="<?= $checkin_date ?>">
                                    <input type="hidden" name="checkout_date" value="<?= $checkout_date ?>">
                                    <input type="hidden" name="adults" value="<?= $adults ?>">
                                    <input type="hidden" name="children" value="<?= $children ?>">
                                    <input type="hidden" name="roomTypeID" value="<?= $room['RoomTypeID'] ?>">
                                    <button type="submit" name="room_favourite">
                                        <i class="absolute top-3 right-3 ri-heart-fill text-xl cursor-pointer flex items-center justify-center bg-white w-9 h-9 rounded-full hover:bg-slate-100 transition-colors duration-300 <?= $is_favorited ? 'text-red-500 hover:text-red-600' : 'text-slate-400 hover:text-red-300' ?>"></i>
                                    </button>
                                </form>
                            </div>
                            <div class="w-full p-4">
                                <div class="flex justify-between items-start">
                                    <div class="flex items-center gap-4">
                                        <h2 class="text-xl font-bold text-gray-800"><?= htmlspecialchars($room['RoomName']) ?> <?= htmlspecialchars($room['RoomType']) ?></h2>
                                        <?php
                                        // Get average rating
                                        $review_select = "SELECT Rating FROM roomtypereviewtb WHERE RoomTypeID = '$room[RoomTypeID]'";
                                        $select_query = $connect->query($review_select);

                                        // Check if there are any reviews
                                        $totalReviews = $select_query->num_rows;
                                        if ($totalReviews > 0) {
                                            $totalRating = 0;

                                            // Sum all ratings
                                            while ($review = $select_query->fetch_assoc()) {
                                                $totalRating += $review['Rating'];
                                            }

                                            // Calculate the average rating
                                            $averageRating = $totalRating / $totalReviews;
                                        } else {
                                            $averageRating = 0;
                                        }
                                        ?>
                                        <div class="flex items-center gap-3">
                                            <div class="select-none space-x-1 cursor-pointer">
                                                <?php
                                                $fullStars = floor($averageRating);
                                                $halfStar = ($averageRating - $fullStars) >= 0.5 ? 1 : 0;
                                                $emptyStars = 5 - ($fullStars + $halfStar);

                                                // Display full stars
                                                for ($i = 0; $i < $fullStars; $i++) {
                                                    echo '<i class="ri-star-fill text-amber-500"></i>';
                                                }

                                                // Display half star if needed
                                                if ($halfStar) {
                                                    echo '<i class="ri-star-half-line text-amber-500"></i>';
                                                }

                                                // Display empty stars
                                                for ($i = 0; $i < $emptyStars; $i++) {
                                                    echo '<i class="ri-star-line text-amber-500"></i>';
                                                }
                                                ?>
                                            </div>
                                            <p class="text-gray-500 text-sm">
                                                (<?php echo $totalReviews; ?> review<?php echo ($totalReviews > 1) ? 's' : ''; ?>)
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm text-gray-500">Price starts from</div>
                                        <div class="text-lg font-bold text-orange-500">USD<?= number_format($room['RoomPrice'], 2) ?></div>
                                    </div>
                                </div>
                                <div class="text-sm text-gray-600 mt-1"><?= htmlspecialchars($room['RoomType']) ?> <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">Max <?= $room['RoomCapacity'] ?> <?php if ($room['RoomCapacity'] > 1) echo 'guests';
                                                                                                                                                                                                                        else echo 'guest'; ?></span></div>
                                <p class="text-sm text-gray-700 mt-3">
                                    <?php
                                    $description = $room['RoomDescription'] ?? '';
                                    $truncated = mb_strimwidth(htmlspecialchars($description), 0, 200, '...');
                                    echo $truncated;
                                    ?>
                                </p>
                                <div class="flex flex-wrap gap-1 mt-4 select-none">
                                    <?php
                                    $facilitiesQuery = "SELECT f.Facility
                    FROM roomtypefacilitytb rf
                    JOIN facilitytb f ON rf.FacilityID = f.FacilityID
                    WHERE rf.RoomTypeID = '" . $room['RoomTypeID'] . "'";
                                    $facilitiesResult = $connect->query($facilitiesQuery);

                                    if ($facilitiesResult->num_rows > 0) {
                                        while ($facility = $facilitiesResult->fetch_assoc()) {
                                            echo '<span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded">' .
                                                htmlspecialchars($facility['Facility']) .
                                                '</span>';
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        </a>

                        <div class="py-6">
                            <h2 class="text-xl font-semibold text-gray-800 mb-4">Enter your details</h2>

                            <div class="mb-6">
                                <div class="flex items-center mb-2">
                                    <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center mr-3">
                                        <span class="w-10 h-10 rounded-full bg-[<?= $userData['ProfileBgColor'] ?>] text-white uppercase font-semibold flex items-center justify-center select-none"><?= $initials ?></span>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h4 class="text-sm font-medium text-gray-800"><?= $userData['UserName'] ?></h4>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-gray-600 mb-4">Are you travelling for work?</p>
                                <div class="flex space-x-4">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="work_travel" class="h-4 w-4 text-blue-600">
                                        <span class="ml-2">Yes</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="work_travel" class="h-4 w-4 text-blue-600" checked>
                                        <span class="ml-2">No</span>
                                    </label>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                                    <select class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        <option>Mr.</option>
                                        <option>Mrs.</option>
                                        <option>Ms.</option>
                                        <option>Dr.</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">First name <span class="text-red-500">*</span></label>
                                    <input type="text" value="<?= $userData['UserName'] ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Last name (Optional)</label>
                                    <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email address <span class="text-red-500">*</span></label>
                                <input type="email" value="<?= $userData['UserEmail'] ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" disabled>
                                <input type="hidden" name="email" value="<?= $userData['UserEmail'] ?>">
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone <span class="text-red-500">*</span></label>
                                <input type="tel" value="<?= $userData['UserPhone'] ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div class="flex justify-between items-center">
                                <a href="#" class="text-blue-600 hover:text-blue-800 font-medium">Back</a>
                                <button class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-md">
                                    Continue to payment
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MoveUp Btn -->
    <?php
    include('../includes/MoveUpBtn.php');
    include('../includes/Alert.php');
    include('../includes/Footer.php');
    ?>

    <script src="//unpkg.com/alpinejs" defer></script>
    <script type="module" src="../JS/index.js"></script>
</body>

</html>