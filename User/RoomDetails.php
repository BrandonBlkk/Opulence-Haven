<?php
session_start();
include('../config/dbConnection.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

$userID = (!empty($_SESSION["UserID"]) ? $_SESSION["UserID"] : null);
$alertMessage = '';

// Get search parameters from URL with strict validation
if (isset($_GET["roomID"])) {
    $room_id = $_GET["roomID"];
    $checkin_date = isset($_GET['checkin_date']) ? htmlspecialchars($_GET['checkin_date']) : '';
    $checkout_date = isset($_GET['checkin_date']) ? htmlspecialchars($_GET['checkout_date']) : '';
    $adults = isset($_GET['adults']) ? (int)$_GET['adults'] : 1;
    $children = isset($_GET['children']) ? (int)$_GET['children'] : 0;
    $totalGuest = $adults + $children;

    $query = "SELECT r.*, rt.RoomType, rt.RoomCapacity
          FROM roomtb r
          JOIN roomtypetb rt ON r.RoomTypeID = rt.RoomTypeID
          WHERE r.RoomID = '$room_id'";

    $room = $connect->query($query)->fetch_assoc();
}

// Add room to favorites
if (isset($_POST['room_favourite'])) {
    if ($userID) {
        $roomID = $_POST['roomID'];

        // Get search parameters from POST data (they should be included in the form submission)
        $checkin_date = isset($_POST['checkin_date']) ? $_POST['checkin_date'] : '';
        $checkout_date = isset($_POST['checkout_date']) ? $_POST['checkout_date'] : '';
        $adults = isset($_POST['adults']) ? intval($_POST['adults']) : 1;
        $children = isset($_POST['children']) ? intval($_POST['children']) : 0;

        $check = "SELECT COUNT(*) as count FROM roomfavoritetb WHERE UserID = '$userID' AND RoomID = '$roomID'";
        $result = $connect->query($check);
        $count = $result->fetch_assoc()['count'];

        if ($count == 0) {
            $insert = "INSERT INTO roomfavoritetb (UserID, RoomID, CheckInDate, CheckOutDate, Adults, Children) 
                      VALUES ('$userID', '$roomID', '$checkin_date', '$checkout_date', '$adults', '$children')";
            $connect->query($insert);
        } else {
            $delete = "DELETE FROM roomfavoritetb WHERE UserID = '$userID' AND RoomID = '$roomID'";
            $connect->query($delete);
        }

        // Redirect back with the same search parameters
        $redirect_url = "RoomDetails.php?roomID=$roomID&checkin_date=$checkin_date&checkout_date=$checkout_date&adults=$adults&children=$children";
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
                <div class="text-sm text-gray-600 mb-4">
                    Home > Hotels > Myanmar > Yangon Region > Yangon > Yangon downtown > The Rangoon Hotel (Hotel), Yangon (Myanmar) deals
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
                            $review_select = "SELECT Rating FROM roomviewtb WHERE RoomID = '$room_id'";
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
                            <h1 class="text-2xl font-bold"><?= htmlspecialchars($room['RoomType']) ?> <?= htmlspecialchars($room['RoomName']) ?></h1>
                        </div>

                        <!-- Address -->
                        <p class="text-gray-700 mb-2">
                            331-333 Corner of 1st Street and Anawrahta Road, Lammadaw Township, Yangon downtown, 11131 Yangon, Myanmar – <span class="text-blue-600">Excellent location - show map</span>
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <?php
                        // Check if room is favorited
                        $check_favorite = "SELECT COUNT(*) as count FROM roomfavoritetb WHERE UserID = '$userID' AND RoomID = '" . $room['RoomID'] . "'";
                        $favorite_result = $connect->query($check_favorite);
                        $is_favorited = $favorite_result->fetch_assoc()['count'] > 0;
                        ?>
                        <form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post">
                            <input type="hidden" name="checkin_date" value="<?= $checkin_date ?>">
                            <input type="hidden" name="checkout_date" value="<?= $checkout_date ?>">
                            <input type="hidden" name="adults" value="<?= $adults ?>">
                            <input type="hidden" name="children" value="<?= $children ?>">
                            <input type="hidden" name="roomID" value="<?= $room['RoomID'] ?>">
                            <button type="submit" name="room_favourite">
                                <!-- Changed this line to use $is_favorited -->
                                <i class="ri-heart-fill text-2xl cursor-pointer flex items-center justify-center bg-white w-11 h-11 rounded-full hover:bg-slate-100 transition-colors duration-300 <?= $is_favorited ? 'text-red-500 hover:text-red-600' : 'text-slate-400 hover:text-red-300' ?>"></i>
                            </button>
                        </form>
                        <button class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-3 rounded">
                            Reserve
                        </button>
                    </div>
                </div>

                <div class="flex justify-between">
                    <div class="w-[50%] select-none">
                        <img src="../Admin/<?= htmlspecialchars($room['RoomCoverImage']) ?>" class="w-full h-full object-cover">
                    </div>
                    <!-- Score breakdown -->
                    <div class="mr-4 flex items-start">
                        <!-- Rating -->
                        <div class="flex flex-col items-center">
                            <span class="bg-blue-600 text-white px-2 py-1 rounded mr-2 text-sm">Superb</span>
                            <p class="text-gray-700 mr-2 text-sm">
                                <?php echo $totalReviews; ?> review<?php echo ($totalReviews > 1) ? 's' : ''; ?>
                            </p>
                        </div>
                        <span class="text-2xl font-bold">9.3</span>
                    </div>
                </div>

                <!-- Photos link -->
                <div class="mb-6">
                    <a href="#" class="text-blue-600">+37 photos</a>
                </div>

                <!-- About section -->
                <div class="mb-8">
                    <h2 class="text-xl font-bold mb-2">About this property</h2>
                    <p class="mb-3">
                        <?= htmlspecialchars($room['RoomDescription']) ?>
                    </p>
                    <p class="text-sm text-gray-600 mt-2">
                        Distance in property description is calculated using © OpenStreetMap
                    </p>
                </div>

                <!-- Facilities section -->
                <div class="mb-8">
                    <h2 class="text-xl font-bold mb-2">Most popular facilities</h2>
                    <div class="flex flex-wrap gap-2 select-none">
                        <?php
                        $facilitiesQuery = "SELECT f.Facility
                                            FROM roomfacilitytb rf
                                            JOIN facilitytb f ON rf.FacilityID = f.FacilityID
                                            WHERE rf.RoomID = '" . $room['RoomID'] . "'";
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

                <!-- Property highlights -->
                <div class="mb-6">
                    <h2 class="text-xl font-bold mb-2">Property highlights</h2>
                    <p class="mb-2">
                        <strong>Top location:</strong> Highly rated by recent guests (9.3)
                    </p>
                    <p class="mb-2">
                        <strong>Breakfast info</strong><br>
                        Vegetarian, Gluten-free, Asian
                    </p>
                    <p>
                        <strong>Free parking available at the hotel</strong>
                    </p>
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

    <script src="//unpkg.com/alpinejs" defer></script>
    <script type="module" src="../JS/index.js"></script>
</body>

</html>