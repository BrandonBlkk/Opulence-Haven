<aside id="rooomReview" class="fixed top-full flex flex-col bg-white w-full h-full p-4 z-50 transition-all duration-500 ease-in-out">
    <div class="flex justify-end pb-3">
        <i id="reviewCloseBtn" class="ri-close-line text-2xl cursor-pointer rounded transition-colors duration-300"></i>
    </div>

    <div class="flex justify-between items-center mb-2">
        <h2 class="text-xl font-bold text-gray-800 mb-3">Guests who stayed here loved</h2>
        <button id="writeReview" class="mt-4 text-start text-blue-600 hover:text-blue-800 text-sm font-medium">
            Write a review
        </button>
    </div>

    <div class="flex flex-col md:flex-row gap-4">
        <div class="w-full md:w-1/4">
            <div class="bg-white p-4 rounded-md shadow-sm border sticky top-4">
                <h3 class="text-lg font-semibold text-gray-700">Filter by:</h3>
                <h4 class="font-medium text-gray-800 my-4">Popular filters</h4>
                <div class="space-y-3">
                    <label class="flex items-center">
                        <input type="checkbox" class="mr-2 rounded text-orange-500 w-5 h-4">
                        <span class="text-sm">Sea view</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" class="mr-2 rounded text-orange-500 w-5 h-4">
                        <span class="text-sm">Restaurant</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" class="mr-2 rounded text-orange-500 w-5 h-4">
                        <span class="text-sm">Air conditioning</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" class="mr-2 rounded text-orange-500 w-5 h-4">
                        <span class="text-sm">Swimming Pool</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" class="mr-2 rounded text-orange-500 w-5 h-4">
                        <span class="text-sm">Apartments</span>
                    </label>
                </div>

                <h4 class="font-medium text-gray-800 my-4">Facilities</h4>
                <div class="space-y-3">
                    <?php
                    $select = "SELECT * FROM facilitytb";
                    $query = $connect->query($select);
                    $count = $query->num_rows;
                    if ($count) {
                        for ($i = 0; $i < $count; $i++) {
                            $row = $query->fetch_assoc();
                            $faculty_id = $row['FacilityID'];
                            $facility = $row['Facility'];

                    ?>
                            <label class="flex items-center">
                                <input type="checkbox" class="mr-2 rounded text-orange-500 w-5 h-4">
                                <span class="text-sm"><?= $facility ?></span>
                            </label>
                    <?php
                        }
                    } else {
                        echo "<option value='' disabled>No data yet</option>";
                    }
                    ?>
                </div>

                <h4 class="font-medium text-gray-800 my-4">Room rating</h4>
                <div class="space-y-3">
                    <label class="flex items-center">
                        <input type="checkbox" class="mr-2 rounded text-orange-500 w-5 h-4">
                        <span class="text-sm">1 star</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" class="mr-2 rounded text-orange-500 w-5 h-4">
                        <span class="text-sm">2 stars</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" class="mr-2 rounded text-orange-500 w-5 h-4">
                        <span class="text-sm">3 stars</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" class="mr-2 rounded text-orange-500 w-5 h-4">
                        <span class="text-sm">4 stars</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" class="mr-2 rounded text-orange-500 w-5 h-4">
                        <span class="text-sm">5 stars</span>
                    </label>
                </div>
            </div>
        </div>
        <!-- User Testimonials -->
        <div id="review-section" class="space-y-4 mb-8">
            <!-- Grid Layout for Reviews -->
            <div class="grid grid-cols-1 gap-4 divide-y-2 divide-slate-100">
                <?php
                $roomReviewSelect = "SELECT rr.*, u.* FROM roomreviewtb rr 
                            JOIN usertb u ON rr.UserID = u.UserID
                            WHERE RoomTypeID = '$roomtype[RoomTypeID]'";
                $roomReviewResult = $connect->query($roomReviewSelect);
                $totalReviews = $roomReviewResult->num_rows;

                while ($roomReview = $roomReviewResult->fetch_assoc()) {
                    // Extract initials
                    $nameParts = explode(' ', trim($roomReview['UserName']));
                    $initials = substr($nameParts[0], 0, 1);
                    if (count($nameParts) > 1) {
                        $initials .= substr(end($nameParts), 0, 1);
                    }
                    $bgColor = $roomReview['ProfileBgColor'];
                ?>
                    <!-- Review Card -->
                    <div class=" rounded-lg p-4">
                        <div class="flex items-center mb-2">
                            <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center mr-3">
                                <span class="w-10 h-10 rounded-full bg-[<?= $bgColor ?>] text-white uppercase font-semibold flex items-center justify-center select-none"><?= $initials ?></span>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-800"><?= $roomReview['UserName'] ?></h4>
                                <div class="flex items-center">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="select-none space-x-1 cursor-pointer text-sm">
                                            <?php
                                            $fullStars = floor($roomReview['Rating']);
                                            $emptyStars = 5 - $fullStars;
                                            for ($i = 0; $i < $fullStars; $i++) {
                                                echo '<i class="ri-star-fill text-amber-500"></i>';
                                            }
                                            for ($i = 0; $i < $emptyStars; $i++) {
                                                echo '<i class="ri-star-line text-amber-500"></i>';
                                            }
                                            ?>
                                        </div>
                                        <span class="text-gray-500 text-sm"><?= timeAgo($roomReview['AddedDate']) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="text-gray-700 text-sm">
                            "<?php echo $roomReview['Comment'] ?? ''; ?>"
                        </p>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</aside>

<!-- Write Review -->
<?php
include('UserWriteReview.php');
?>

<!-- Loader -->
<?php
include('Loader.php');
?>