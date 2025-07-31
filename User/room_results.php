<?php
// Base query (your existing PHP code remains exactly the same)
$base_query = "SELECT rt.* FROM roomtypetb rt";

// Initialize conditions array for WHERE clauses
$conditions = [];

// Add guest capacity filter if dates are valid
if ($has_dates && $checkin_date >= $today && $checkout_date > $checkin_date) {
    $conditions[] = "rt.RoomCapacity >= $totelGuest";
}

if (isset($_GET['facilities']) && is_array($_GET['facilities']) && !empty($_GET['facilities'])) {
    $facility_ids = array_map('intval', $_GET['facilities']);
    $facility_ids = array_filter($facility_ids);

    if (!empty($facility_ids)) {
        $placeholders = implode(',', array_fill(0, count($facility_ids), '?'));
        $conditions[] = "rt.RoomTypeID IN (
            SELECT DISTINCT RoomTypeID 
            FROM roomtypefacilitytb 
            WHERE FacilityID IN ($placeholders)
        )";

        foreach ($facility_ids as $id) {
            $params[] = $id;
            $types .= 'i';
        }
    }
}

// Process rating filters - Modified to show selected rating AND lower ratings
if (isset($_GET['ratings']) && is_array($_GET['ratings']) && !empty($_GET['ratings'])) {
    $rating_conditions = [];
    $max_rating = max(array_map('intval', $_GET['ratings'])); // Get the highest selected rating

    // Only need one condition since we're checking "less than or equal to" the max selected rating
    $rating_conditions[] = "(SELECT COALESCE(AVG(Rating), 0) FROM roomtypereviewtb WHERE RoomTypeID = rt.RoomTypeID) <= $max_rating";

    // Also ensure the rating is at least 1 (if you want to exclude unrated items)
    $rating_conditions[] = "(SELECT COALESCE(AVG(Rating), 0) FROM roomtypereviewtb WHERE RoomTypeID = rt.RoomTypeID) >= 1";

    if (!empty($rating_conditions)) {
        $conditions[] = "(" . implode(' AND ', $rating_conditions) . ")";
    }
}

// Add all conditions to the query
if (!empty($conditions)) {
    $base_query .= " WHERE " . implode(' AND ', $conditions);
}

// Sorting
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'top_picks';
switch ($sort) {
    case 'price_low_high':
        $base_query .= " ORDER BY rt.RoomPrice ASC";
        break;
    case 'price_high_low':
        $base_query .= " ORDER BY rt.RoomPrice DESC";
        break;
    case 'rating':
        if (strpos($base_query, 'WHERE') === false) {
            $base_query .= " WHERE rt.RoomTypeID IN (SELECT RoomTypeID FROM roomtypereviewtb)";
        } else {
            $base_query .= " AND rt.RoomTypeID IN (SELECT RoomTypeID FROM roomtypereviewtb)";
        }
        $base_query .= " ORDER BY (SELECT AVG(Rating) FROM roomtypereviewtb WHERE RoomTypeID = rt.RoomTypeID) DESC";
        break;
    default:
        $base_query .= " ORDER BY rt.RoomTypeID DESC";
}

// Execute query
$stmt = $connect->prepare($base_query);
$stmt->execute();
$result = $stmt->get_result();
$available_rooms = $result->fetch_all(MYSQLI_ASSOC);
$foundProperties = count($available_rooms);
?>

<?php if ($foundProperties > 0): ?>
    <!-- Right: Hotel Listings -->
    <div class="w-full space-y-2">

        <!-- Room Listings -->
        <?php foreach ($available_rooms as $roomtype):
            // Check if room is favorited
            $check_favorite = "SELECT COUNT(*) as count FROM roomtypefavoritetb WHERE UserID = '$userID' AND RoomTypeID = '" . $roomtype['RoomTypeID'] . "'";
            $favorite_result = $connect->query($check_favorite);
            $is_favorited = $favorite_result->fetch_assoc()['count'] > 0;
        ?>
            <div class="bg-white overflow-hidden hotel-listings-container">
                <a href="../User/room_details.php?roomTypeID=<?php echo htmlspecialchars($roomtype['RoomTypeID']) ?>&reservation_id=<?= $reservation_id ?>&room_id=<?= $room_id ?>&checkin_date=<?= $checkin_date ?>&checkout_date=<?= $checkout_date ?>&adults=<?= $adults ?>&children=<?= $children ?>&edit=<?= $edit ?>" class="flex flex-col md:flex-row rounded-md shadow-sm border">
                    <!-- Image Section - Full width on mobile, 28% on desktop -->
                    <div class="w-full md:w-[28%] h-48 sm:h-56 md:h-[261px] overflow-hidden select-none rounded-t-md md:rounded-l-md md:rounded-tr-none relative">
                        <img src="../Admin/<?= htmlspecialchars($roomtype['RoomCoverImage']) ?>" alt="<?= htmlspecialchars($roomtype['RoomType']) ?>" class="w-full h-full object-cover">
                        <form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post">
                            <input type="hidden" name="checkin_date" value="<?= $checkin_date ?>">
                            <input type="hidden" name="checkout_date" value="<?= $checkout_date ?>">
                            <input type="hidden" name="adults" value="<?= $adults ?>">
                            <input type="hidden" name="children" value="<?= $children ?>">
                            <input type="hidden" name="roomTypeID" value="<?= $roomtype['RoomTypeID'] ?>">
                            <button type="submit" name="room_favourite" class="focus:outline-none">
                                <i class="absolute top-3 right-3 ri-heart-fill text-xl cursor-pointer flex items-center justify-center bg-white w-9 h-9 rounded-full hover:bg-slate-100 transition-colors duration-300 <?= $is_favorited ? 'text-red-500 hover:text-red-600' : 'text-slate-400 hover:text-red-300' ?>"></i>
                            </button>
                        </form>
                    </div>

                    <!-- Content Section -->
                    <div class="w-full md:w-2/3 p-3 sm:p-4">
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-2">
                            <!-- Room Title and Rating -->
                            <div class="flex flex-col gap-1 sm:gap-2">
                                <h2 class="text-lg sm:text-xl font-bold text-gray-800"><?= htmlspecialchars($roomtype['RoomType']) ?></h2>
                                <?php
                                // Get average rating
                                $review_select = "SELECT Rating FROM roomtypereviewtb WHERE RoomTypeID = '$roomtype[RoomTypeID]'";
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
                                <div class="flex items-center gap-2 sm:gap-3">
                                    <div class="select-none space-x-0.5 sm:space-x-1 cursor-pointer">
                                        <?php
                                        $fullStars = floor($averageRating);
                                        $halfStar = ($averageRating - $fullStars) >= 0.5 ? 1 : 0;
                                        $emptyStars = 5 - ($fullStars + $halfStar);

                                        // Display full stars
                                        for ($i = 0; $i < $fullStars; $i++) {
                                            echo '<i class="ri-star-fill text-amber-500 text-sm sm:text-base"></i>';
                                        }

                                        // Display half star if needed
                                        if ($halfStar) {
                                            echo '<i class="ri-star-half-line text-amber-500 text-sm sm:text-base"></i>';
                                        }

                                        // Display empty stars
                                        for ($i = 0; $i < $emptyStars; $i++) {
                                            echo '<i class="ri-star-line text-amber-500 text-sm sm:text-base"></i>';
                                        }
                                        ?>
                                    </div>
                                    <p class="text-gray-500 text-xs sm:text-sm">
                                        (<?php echo $totalReviews; ?> review<?php echo ($totalReviews > 1) ? 's' : ''; ?>)
                                    </p>
                                </div>
                            </div>

                            <!-- Price -->
                            <div class="text-right">
                                <div class="text-xs sm:text-sm text-gray-500">Price starts from</div>
                                <div class="text-base sm:text-lg font-bold text-orange-500">USD<?= number_format($roomtype['RoomPrice'], 2) ?></div>
                            </div>
                        </div>

                        <!-- Room Details -->
                        <div class="text-xs sm:text-sm text-gray-600 mt-2">
                            <?= htmlspecialchars($roomtype['RoomType']) ?>
                            <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">Max <?= $roomtype['RoomCapacity'] ?> <?php if ($roomtype['RoomCapacity'] > 1) echo 'guests';
                                                                                                                                    else echo 'guest'; ?></span>
                            <span class="text-gray-400">•</span> Show on map
                        </div>

                        <!-- Description -->
                        <p class="text-xs sm:text-sm text-gray-700 mt-2 sm:mt-3">
                            <?php
                            $description = $roomtype['RoomDescription'] ?? '';
                            $truncated = mb_strimwidth(htmlspecialchars($description), 0, 250, '...');
                            echo $truncated;
                            ?>
                        </p>

                        <!-- Facilities -->
                        <div class="flex flex-wrap gap-1 mt-3 sm:mt-4 select-none">
                            <?php
                            $facilitiesQuery = "SELECT f.Facility
                FROM roomtypefacilitytb rf
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
                </a>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="max-w-[1200px] mx-auto px-4 py-16 text-center">
        <div class="bg-white p-8 rounded-lg shadow-sm border max-w-2xl mx-auto">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h2 class="text-xl font-bold text-gray-800 mt-4">No properties found</h2>
            <p class="text-gray-600 mt-2">We couldn't find any properties matching your search criteria.</p>
            <div class="mt-6">
                <a href="../index.php" class="inline-block bg-orange-500 text-white px-6 py-2 rounded-md hover:bg-orange-600 transition-colors select-none">
                    Modify search
                </a>
            </div>
        </div>
    </div>
<?php endif; ?>