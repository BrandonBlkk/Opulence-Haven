<?php
session_start();
include('../config/dbConnection.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

// Initialize array to store favorite rooms
$favorite_rooms = [];
$userID = (!empty($_SESSION["UserID"]) ? $_SESSION["UserID"] : null);

// Check if user is logged in
if (isset($_SESSION['UserID'])) {
    $favorite_query = "SELECT f.*, r.*, rt.RoomType , rt.RoomCapacity
                      FROM roomfavoritetb f
                      JOIN roomtb r ON f.RoomID = r.RoomID
                      JOIN roomtypetb rt ON r.RoomTypeID = rt.RoomTypeID
                      WHERE f.UserID = '$userID'";

    $favorite_result = $connect->query($favorite_query);

    if ($favorite_result->num_rows > 0) {
        while ($room = $favorite_result->fetch_assoc()) {
            $favorite_rooms[] = $room;
        }
    }
}

// Add room to favorites
if (isset($_POST['room_favourite'])) {
    $roomID = $_POST['roomID'];

    $check = "SELECT COUNT(*) as count FROM roomfavoritetb WHERE UserID = '$userID' AND RoomID = '$roomID'";
    $result = $connect->query($check);
    $count = $result->fetch_assoc()['count'];

    if ($count == 0) {
        $insert = "INSERT INTO roomfavoritetb (UserID, RoomID) VALUES ('$userID', '$roomID')";
        $connect->query($insert);
    } else {
        $delete = "DELETE FROM roomfavoritetb WHERE UserID = '$userID' AND RoomID = '$roomID'";
        $connect->query($delete);
    }

    // Refresh page
    header("Location: Favorite.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opulence Haven</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../CSS/output.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../CSS/input.css?v=<?php echo time(); ?>">
</head>

<body class="relative">
    <?php
    include('../includes/Navbar.php');
    ?>

    <main class="pb-4 px-4 max-w-[1310px] mx-auto">
        <!-- Info -->
        <section class="flex items-center justify-center <?php echo !empty($_SESSION['UserID']) ? 'hidden' : ''; ?>">
            <div class="flex gap-2 border border-t-0 p-3">
                <i class="ri-heart-line text-2xl cursor-pointer bg-slate-100 w-10 h-10 rounded-full flex items-center justify-center"></i>
                <div>
                    <p class="text-lg">Keep track of stays you like</p>
                    <p class="text-slate-600 text-sm"><a class="underline underline-offset-2" href="UserSignIn.php">Sign in</a> or <a class="underline underline-offset-2" href="UserSIgnUp.php">create an account</a> to save your favorite stays to your account and create your own lists.</p>
                </div>
            </div>
        </section>

        <div class="my-5">
            <h1 class="uppercase text-xl sm:text-2xl text-blue-900 font-semibold">Your Favorites</h1>
            <span class="text-amber-500">(<?php echo count($favorite_rooms); ?>) saved <?php echo count($favorite_rooms) > 1 ? 'rooms' : 'room'; ?></span>
        </div>
        <?php
        if (isset($_SESSION['UserID']) && $_SESSION['UserID'] && !empty($favorite_rooms)) {
        ?>
            <section class="grid grid-cols-1 gap-6">
                <?php foreach ($favorite_rooms as $room):
                    // Check if room is favorited
                    $check_favorite = "SELECT COUNT(*) as count FROM roomfavoritetb WHERE UserID = '$userID' AND RoomID = '" . $room['RoomID'] . "'";
                    $favorite_result = $connect->query($check_favorite);
                    $is_favorited = $favorite_result->fetch_assoc()['count'] > 0;
                ?>
                    <!-- Card -->
                    <div class="border rounded-lg overflow-hidden">
                        <div class="flex flex-col md:flex-row">
                            <!-- Image -->
                            <div class="md:w-[28%] h-64 overflow-hidden select-none rounded-l-md relative">
                                <img src="<?= htmlspecialchars($room['RoomCoverImage']) ?>" alt="<?= htmlspecialchars($room['RoomName']) ?>" class="w-full h-full object-cover">
                                <form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post">
                                    <input type="hidden" name="roomID" value="<?= $room['RoomID'] ?>">
                                    <button type="submit" name="room_favourite">
                                        <!-- Changed this line to use $is_favorited -->
                                        <i class="absolute top-3 right-3 ri-heart-fill text-xl cursor-pointer flex items-center justify-center bg-white w-9 h-9 rounded-full hover:bg-slate-100 transition-colors duration-300 <?= $is_favorited ? 'text-red-500 hover:text-red-600' : 'text-slate-400 hover:text-red-300' ?>"></i>
                                    </button>
                                </form>
                            </div>

                            <!-- Details -->
                            <div class="md:w-2/3 p-5">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h2 class="text-xl font-bold text-gray-800"><?= htmlspecialchars($room['RoomType']) ?> <?= htmlspecialchars($room['RoomName']) ?></h2>
                                        <?php
                                        $review_select = "SELECT Rating FROM roomviewtb WHERE RoomID = '" . $room['RoomID'] . "'";
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
                                        <p class="text-sm text-gray-600 mt-2 flex items-center">
                                            <i class="ri-map-pin-line mr-1"></i> Opulence Haven, Yangon
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm text-gray-500">1 night: <?php echo $room['RoomCapacity'] ?> adults</p>
                                        <p class="text-lg font-bold text-orange-500">USD <?php echo number_format($room['RoomPrice'], 0) ?></p>
                                        <p class="text-xs text-gray-500">Includes taxes and charges</p>
                                        <p class="text-xs text-green-600 mt-1">✔ Free cancellation</p>
                                    </div>
                                </div>

                                <div class="mt-4 pt-4 border-t">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <p class="text-sm text-gray-600"><?php
                                                                                echo date('D j M', strtotime($room['CheckInDate'])) . ' - ' . date('D j M', strtotime($room['CheckOutDate']));
                                                                                ?></p>
                                            <p class="text-sm text-gray-600"><?php echo $room['Adults'] ?> <?php echo $room['Adults'] > 1 ? 'adults' : 'adult'; ?> - <?php echo $room['Children'] ?> <?php echo $room['Children'] > 1 ? 'children' : 'child'; ?> - 1 room</p>
                                        </div>
                                        <a href="../User/RoomDetails.php?RoomID=<?php echo $room['RoomID'] ?>" class="px-4 py-2 bg-amber-500 text-white font-semibold rounded-md hover:bg-amber-600 transition-colors select-none">
                                            View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </section>
        <?php
        } else {
        ?>
            <p class="mt-10 py-36 flex justify-center text-center text-base text-gray-400">
                You have no favorite items yet.
            </p>
        <?php
        }
        ?>
    </main>

    <?php
    include('../includes/Footer.php');
    ?>

    <script src="//unpkg.com/alpinejs" defer></script>
    <script type="module" src="../JS/index.js"></script>
</body>

</html>