<?php
session_start();
include('../config/dbConnection.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

$userID = (!empty($_SESSION["UserID"]) ? $_SESSION["UserID"] : null);
$alertMessage = '';

// Get search parameters from URL with strict validation
if (isset($_GET["roomTypeID"])) {
    $roomtype_id = $_GET["roomTypeID"];
    $checkin_date = isset($_GET['checkin_date']) ? htmlspecialchars($_GET['checkin_date']) : '';
    $checkout_date = isset($_GET['checkin_date']) ? htmlspecialchars($_GET['checkout_date']) : '';
    $adults = isset($_GET['adults']) ? (int)$_GET['adults'] : 1;
    $children = isset($_GET['children']) ? (int)$_GET['children'] : 0;
    $totalGuest = $adults + $children;

    $query = "SELECT * FROM roomtypetb WHERE RoomTypeID = '$roomtype_id'";

    $roomtype = $connect->query($query)->fetch_assoc();

    // Get TOTAL rooms 
    $totalRoomsQuery = "SELECT COUNT(*) as total FROM roomtb WHERE RoomTypeID = '$roomtype_id'";
    $totalRoomsResult = $connect->query($totalRoomsQuery)->fetch_assoc();
    $totalRooms = $totalRoomsResult['total'];

    // Get AVAILABLE rooms 
    $availableRoomsQuery = "SELECT COUNT(*) as available FROM roomtb WHERE RoomTypeID = '$roomtype_id' AND RoomStatus = 'Available'";
    $availableRoomsResult = $connect->query($availableRoomsQuery)->fetch_assoc();
    $availableRooms = $availableRoomsResult['available'];
}

// Add room to favorites
if (isset($_POST['room_favourite'])) {
    if ($userID) {
        $roomTypeID = $_POST['roomTypeID'];

        // Get search parameters from POST data (they should be included in the form submission)
        $checkin_date = isset($_POST['checkin_date']) ? $_POST['checkin_date'] : '';
        $checkout_date = isset($_POST['checkout_date']) ? $_POST['checkout_date'] : '';
        $adults = isset($_POST['adults']) ? intval($_POST['adults']) : 1;
        $children = isset($_POST['children']) ? intval($_POST['children']) : 0;

        $check = "SELECT COUNT(*) as count FROM roomfavoritetb WHERE UserID = '$userID' AND RoomTypeID = '$roomTypeID'";
        $result = $connect->query($check);
        $count = $result->fetch_assoc()['count'];

        if ($count == 0) {
            $insert = "INSERT INTO roomfavoritetb (UserID, RoomTypeID, CheckInDate, CheckOutDate, Adult, Children) 
                      VALUES ('$userID', '$roomTypeID', '$checkin_date', '$checkout_date', '$adults', '$children')";
            $connect->query($insert);
        } else {
            $delete = "DELETE FROM roomfavoritetb WHERE UserID = '$userID' AND RoomTypeID = '$roomTypeID'";
            $connect->query($delete);
        }

        // Redirect back with the same search parameters
        $redirect_url = "RoomDetails.php?roomTypeID=$roomTypeID&checkin_date=$checkin_date&checkout_date=$checkout_date&adults=$adults&children=$children";
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

<body class="relative">
    <?php
    include('../includes/Navbar.php');
    include('../includes/Cookies.php');
    ?>

    <main class="pb-4">
        <div class="relative swiper-container flex justify-center">
            <div class="max-w-[1150px] mx-auto px-4 py-8">
                <!-- Breadcrumbs -->
                <div class="flex text-sm text-slate-600 mb-4">
                    <a href="../User/HomePage.php" class="underline">Home</a>
                    <span><i class="ri-arrow-right-s-fill"></i></span>
                    <a href="../User/RoomBooking.php?checkin_date=<?= $checkin_date ?>&checkout_date=<?= $checkout_date ?>&adults=<?= $adults ?>&children=<?= $children ?>" class="underline">Rooms</a>
                    <span><i class="ri-arrow-right-s-fill"></i></span>
                    <a href="../User/RoomDetails.php?roomTypeID=<?php echo htmlspecialchars($roomtype['RoomTypeID']) ?>&checkin_date=<?= $checkin_date ?>&checkout_date=<?= $checkout_date ?>&adults=<?= $adults ?>&children=<?= $children ?>" class=" underline">Store Details</a>
                </div>

                <!-- Navigation tabs -->
                <div class="flex border-b mb-6">
                    <div class="mr-4 pb-2 border-b-2 border-blue-600 font-medium">Overview</div>
                    <div class="mr-4 pb-2 text-gray-600">Info & prices</div>
                    <div class="mr-4 pb-2 text-gray-600">Facilities</div>
                    <div class="mr-4 pb-2 text-gray-600">House rules</div>
                    <div class="pb-2 text-gray-600">Guest reviews (116)</div>
                </div>

                <div class="flex justify-between items-start">
                    <div>
                        <!-- Hotel name and rating -->
                        <div class="mb-2">
                            <?php
                            $review_select = "SELECT Rating FROM roomviewtb WHERE RoomTypeID = '$roomtype_id'";
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
                            </div>
                            <h1 class="text-2xl font-bold"><?= htmlspecialchars($roomtype['RoomType']) ?></h1>
                        </div>

                        <!-- Address -->
                        <p class="text-gray-700 text-sm mb-2">
                            459 Pyay Road, Kamayut Township , 11041
                            Yangon, Myanmar – <span class="text-blue-600">Excellent location - show map</span>
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <?php
                        // Check if room is favorited
                        $check_favorite = "SELECT COUNT(*) as count FROM roomfavoritetb WHERE UserID = '$userID' AND RoomTypeID = '" . $roomtype['RoomTypeID'] . "'";
                        $favorite_result = $connect->query($check_favorite);
                        $is_favorited = $favorite_result->fetch_assoc()['count'] > 0;
                        ?>
                        <form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post">
                            <input type="hidden" name="checkin_date" value="<?= $checkin_date ?>">
                            <input type="hidden" name="checkout_date" value="<?= $checkout_date ?>">
                            <input type="hidden" name="adults" value="<?= $adults ?>">
                            <input type="hidden" name="children" value="<?= $children ?>">
                            <input type="hidden" name="roomTypeID" value="<?= $roomtype['RoomTypeID'] ?>">
                            <button type="submit" name="room_favourite">
                                <!-- Changed this line to use $is_favorited -->
                                <i class="ri-heart-fill text-2xl cursor-pointer flex items-center justify-center bg-white w-11 h-11 rounded-full hover:bg-slate-100 transition-colors duration-300 <?= $is_favorited ? 'text-red-500 hover:text-red-600' : 'text-slate-400 hover:text-red-300' ?>"></i>
                            </button>
                        </form>
                        <button
                            onclick="scrollToAvailability()"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-3 rounded transition-colors">
                            Reserve
                        </button>
                    </div>
                </div>

                <div class="flex justify-between gap-24">
                    <!-- Swiper.js Styles -->
                    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

                    <!-- Room Images Grid -->
                    <div class="flex gap-2">
                        <!-- Cover Image - Made larger -->
                        <div class="w-[70%] h-[450px] select-none cursor-pointer" onclick="openSwiper(0)">
                            <img src="../Admin/<?= htmlspecialchars($roomtype['RoomCoverImage']) ?>"
                                class="w-full h-full object-cover rounded-lg border border-gray-200">
                        </div>

                        <!-- Additional Images (Up to 3 with +X overlay) - Made larger -->
                        <?php
                        $additionalImagesQuery = "SELECT * FROM roomimagetb WHERE RoomTypeID = '$roomtype_id'";
                        $additionalImagesResult = $connect->query($additionalImagesQuery);

                        if ($additionalImagesResult->num_rows > 0) {
                            $allImages = [];

                            // Add cover image as first in array
                            $allImages[] = ['ImagePath' => $roomtype['RoomCoverImage']];

                            while ($row = $additionalImagesResult->fetch_assoc()) {
                                $allImages[] = $row;
                            }

                            // Output 3 thumbnails
                            $displayImages = array_slice($allImages, 1, 3); // skip first (cover)
                            $extraCount = count($allImages) - 4;

                            echo '<div class="w-[30%] grid grid-cols-1 gap-2 select-none">';
                            foreach ($displayImages as $index => $image) {
                                $imgIndex = $index + 1; // cover is 0
                                echo '<div class="relative cursor-pointer" onclick="openSwiper(' . $imgIndex . ')">';
                                echo '<img src="../Admin/' . htmlspecialchars($image['ImagePath']) . '" class="w-full h-[145px] object-cover rounded-lg border border-gray-200">';
                                if ($index === 2 && $extraCount > 0) {
                                    echo '<div class="absolute inset-0 bg-black bg-opacity-40 rounded-lg flex items-center justify-center">';
                                    echo '<span class="text-white text-lg font-semibold">+' . $extraCount . '</span>';
                                    echo '</div>';
                                }
                                echo '</div>';
                            }
                            echo '</div>';
                        }
                        ?>
                    </div>

                    <!-- Swiper Modal -->
                    <div id="swiperModal" class="fixed inset-0 bg-black bg-opacity-75 z-50 hidden items-center justify-center">
                        <div class="relative w-full mx-auto">
                            <!-- Close Button -->
                            <button onclick="closeSwiper()" class="absolute top-2 right-2 text-white text-2xl z-50">&times;</button>

                            <!-- Swiper Container -->
                            <div class="swiper mySwiper">
                                <div class="swiper-wrapper select-none">
                                    <?php
                                    foreach ($allImages as $img) {
                                        echo '<div class="swiper-slide">';
                                        echo '<img src="../Admin/' . htmlspecialchars($img['ImagePath']) . '" class="w-full max-h-[700px] object-contain mx-auto rounded-lg">';
                                        echo '</div>';
                                    }
                                    ?>
                                </div>
                                <!-- Navigation buttons -->
                                <div class="swiper-button-next"></div>
                                <div class="swiper-button-prev"></div>
                                <!-- Pagination -->
                                <div class="swiper-pagination"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Swiper.js Script -->
                    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

                    <script>
                        let swiperInstance;

                        function openSwiper(startIndex = 0) {
                            document.getElementById('swiperModal').classList.remove('hidden');
                            document.getElementById('swiperModal').classList.add('flex');
                            swiperInstance = new Swiper(".mySwiper", {
                                initialSlide: startIndex,
                                navigation: {
                                    nextEl: ".swiper-button-next",
                                    prevEl: ".swiper-button-prev",
                                },
                                pagination: {
                                    el: ".swiper-pagination",
                                    clickable: true,
                                },
                                loop: false,
                            });
                        }

                        function closeSwiper() {
                            document.getElementById('swiperModal').classList.add('hidden');
                            document.getElementById('swiperModal').classList.remove('flex');
                            if (swiperInstance) swiperInstance.destroy(true, true);
                        }
                    </script>

                    <!-- Rating and Review -->
                    <div class="flex justify-end items-start w-72">
                        <div class="flex flex-col items-center mr-2">
                            <span class="bg-blue-600 text-white px-2 py-1 rounded text-sm">Superb</span>
                            <p class="text-gray-700 text-sm">
                                <?php echo $totalReviews; ?> review<?php echo ($totalReviews > 1) ? 's' : ''; ?>
                            </p>
                        </div>
                        <span class="text-2xl font-bold">9.3</span>
                    </div>
                </div>

                <div class="flex items-center">
                    <!-- About section -->
                    <div class="my-8 w-[65%]">
                        <h2 class="text-xl font-bold mb-2">About this property</h2>
                        <p class="text-gray-700 text-sm mb-3">
                            <?= nl2br(htmlspecialchars($roomtype['RoomDescription'])) ?>
                        </p>
                        <p class="text-sm text-gray-500 mt-2">
                            Distance in property description is calculated using © OpenStreetMap
                        </p>
                    </div>

                    <!-- Property highlights -->
                    <div class="mb-6 bg-blue-50 flex-1 p-3">
                        <h2 class="text-lg font-bold text-slate-700 mb-2">Property highlights</h2>
                        <p class="mb-2 text-sm text-gray-500 flex items-center">
                            <i class="ri-map-pin-line text-xl"></i>
                            Top location: Highly rated by recent guests (9.3)
                        </p>
                        <p class="mb-2 text-sm text-gray-500">
                            <strong class="text-slate-700">Breakfast info</strong><br>
                            Vegetarian, Gluten-free, Asian
                        </p>
                        <p class="text-sm text-gray-500 flex items-center">
                            <i class="ri-parking-box-line text-xl"></i>
                            Free parking available at the hotel
                        </p>
                    </div>
                </div>

                <!-- Facilities section -->
                <div class="mb-8">
                    <h2 class="text-xl font-bold mb-2">Most popular facilities</h2>
                    <div class="flex flex-wrap gap-2 select-none mt-6">
                        <?php
                        $facilitiesQuery = "SELECT f.Facility
                                FROM roomfacilitytb rf
                                JOIN facilitytb f ON rf.FacilityID = f.FacilityID
                                WHERE rf.RoomTypeID = '" . $roomtype['RoomTypeID'] . "'";
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

                <!-- Availability section -->
                <div id="availability-section" class="border-t pt-6">
                    <h2 class="text-xl font-bold mb-4">
                        Availability (<?= $availableRooms ?> of <?= $totalRooms ?> left)
                    </h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Availability</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php
                                $roomSelect = "SELECT r.*, rt.RoomType
                          FROM roomtb r 
                          JOIN roomtypetb rt ON r.RoomTypeID = rt.RoomTypeID 
                          WHERE r.RoomTypeID = '" . $roomtype['RoomTypeID'] . "'";
                                $roomSelectResult = $connect->query($roomSelect);

                                if ($roomSelectResult->num_rows > 0) {
                                    while ($room = $roomSelectResult->fetch_assoc()) {

                                ?>
                                        <tr class="<?= ($room['RoomStatus'] == 'Available') ? '' : 'opacity-50'; ?>">
                                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($room['RoomName']) ?> (<?= htmlspecialchars($room['RoomType']) ?>)</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-gray-600"><?= htmlspecialchars($room['RoomStatus']) ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <button class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-1 px-3 rounded text-sm select-none <?= ($room['RoomStatus'] == 'Available') ? '' : 'disabled'; ?>">View</button>
                                            </td>
                                        </tr>
                                <?php
                                    }
                                } else {
                                    echo '<tr><td colspan="3" class="px-6 py-4 text-center text-gray-500">No rooms found</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- House rules -->
                <div class="mb-8 bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                    <div class="flex items-start">
                        <!-- Icon for visual appeal -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-amber-500 mt-0.5 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>

                        <div class="mb-8">
                            <h2 class="text-xl font-bold text-gray-800 mb-3">House Rules</h2>
                            <p class="text-gray-600 mb-4">The Opulence Haven Hotel welcomes special requests to make your stay perfect</p>

                            <ul class="space-y-4">
                                <?php
                                $ruleSelect = "SELECT * FROM ruletb";
                                $ruleSelectResult = $connect->query($ruleSelect);

                                if ($ruleSelectResult->num_rows > 0) {
                                    while ($rule = $ruleSelectResult->fetch_assoc()) {
                                ?>
                                        <li class="flex gap-4">
                                            <!-- Icon Column - Perfectly centered vertically -->
                                            <div class="flex items-center justify-center h-full flex-shrink-0">
                                                <i class="<?= htmlspecialchars($rule['RuleIcon']) ?> <?= htmlspecialchars($rule['IconSize']) ?> text-gray-500"></i>
                                            </div>

                                            <!-- Text Column -->
                                            <div>
                                                <h3 class="font-medium text-gray-800 mb-1"><?= htmlspecialchars($rule['RuleTitle']) ?></h3>
                                                <p class="text-gray-600 text-sm leading-relaxed">
                                                    <?= nl2br(htmlspecialchars($rule['Rule'])) ?>
                                                </p>
                                            </div>
                                        </li>
                                <?php
                                    }
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- MoveUp Btn -->
    <?php
    include('../includes/MoveUpBtn.php');
    include('../includes/Alert.php');
    include('../includes/Footer.php');
    ?>

    <script>
        function scrollToAvailability() {
            const section = document.getElementById('availability-section');
            const sectionRect = section.getBoundingClientRect();
            const sectionMiddle = sectionRect.top + window.scrollY + (sectionRect.height / 2) - (window.innerHeight / 2);

            window.scrollTo({
                top: sectionMiddle,
                behavior: 'smooth'
            });

            // Optional: Focus on first available room's "View" button
            const firstAvailableBtn = document.querySelector('.bg-blue-600:not(.disabled)');
            if (firstAvailableBtn) {
                firstAvailableBtn.focus();
            }
        }
    </script>

    <script src="//unpkg.com/alpinejs" defer></script>
    <script type="module" src="../JS/index.js"></script>
</body>

</html>