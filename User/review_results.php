<?php
if ($totalReviews > 0) {
    // Get selected filters from query parameters
    $selectedRatings = isset($_GET['ratings']) ? $_GET['ratings'] : [];
    $selectedTravellerTypes = isset($_GET['traveller_type']) ? $_GET['traveller_type'] : [];
    $selectedRoomTypes = isset($_GET['roomtypes']) ? $_GET['roomtypes'] : [];

    $ratingFilter = '';
    if (!empty($selectedRatings)) {
        $ratingFilter = " AND rr.Rating IN (" . implode(',', array_map('intval', $selectedRatings)) . ")";
    }

    $travellerTypeFilter = '';
    if (!empty($selectedTravellerTypes)) {
        $escapedTypes = array_map(function ($type) use ($connect) {
            return "'" . $connect->real_escape_string($type) . "'";
        }, $selectedTravellerTypes);
        $travellerTypeFilter = " AND rr.TravellerType IN (" . implode(',', $escapedTypes) . ")";
    }

    $roomTypeFilter = '';
    if (!empty($selectedRoomTypes)) {
        $escapedRoomTypes = array_map(function ($type) use ($connect) {
            return "'" . $connect->real_escape_string($type) . "'";
        }, $selectedRoomTypes);
        $roomTypeFilter = " AND rr.RoomTypeID IN (" . implode(',', $escapedRoomTypes) . ")";
    }
?>
    <div id="review-section" class="reservationScrollBar space-y-4 mb-8 w-full h-[600px] overflow-y-auto">
        <!-- Grid Layout for Reviews -->
        <div id="reviews-container" class="flex flex-col gap-4 divide-y-2 divide-slate-100">
            <?php
            $roomReviewSelect = "SELECT rr.*, u.*, rt.RoomType
                    FROM roomtypereviewtb rr 
                    JOIN usertb u ON rr.UserID = u.UserID 
                    JOIN roomtypetb rt ON rr.RoomTypeID = rt.RoomTypeID
                    WHERE 1=1 $ratingFilter $travellerTypeFilter $roomTypeFilter
                    ORDER BY rr.AddedDate DESC";
            $roomReviewResult = $connect->query($roomReviewSelect);
            $filteredReviews = $roomReviewResult->num_rows;

            if ($filteredReviews > 0) {
                while ($roomReview = $roomReviewResult->fetch_assoc()) {
                    // Extract initials
                    $userid = $roomReview['UserID'];
                    $nameParts = explode(' ', trim($roomReview['UserName']));
                    $initials = substr($nameParts[0], 0, 1);
                    if (count($nameParts) > 1) {
                        $initials .= substr(end($nameParts), 0, 1);
                    }
                    $bgColor = $roomReview['ProfileBgColor'];
            ?>
                    <!-- Review Card -->
                    <div class="review-card rounded-lg p-4" data-rating="<?= $roomReview['Rating'] ?>">
                        <div class="flex items-center mb-2">
                            <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center mr-3">
                                <span class="w-10 h-10 rounded-full bg-[<?= $bgColor ?>] text-white uppercase font-semibold flex items-center justify-center select-none"><?= $initials ?></span>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h4 class="text-sm font-medium text-gray-800"><?= $roomReview['UserName'] ?></h4>
                                    <div class="flex items-center gap-2">
                                        <!-- Country Flag -->
                                        <span class="text-xs flag-icon flag-icon-<?= strtolower($roomReview['Country']) ?> rounded-sm shadow-sm"></span>

                                        <!-- Country Name (Fetched via API) -->
                                        <span class="text-xs text-gray-600 country-name" data-country-code="<?= $roomReview['Country'] ?>">
                                            Loading...
                                        </span>
                                    </div>
                                </div>

                                <script>
                                    // Fetch country names from RestCountries API
                                    document.querySelectorAll('.country-name').forEach(el => {
                                        const countryCode = el.getAttribute('data-country-code');
                                        fetch(`https://restcountries.com/v3.1/alpha/${countryCode}`)
                                            .then(response => response.json())
                                            .then(data => {
                                                el.textContent = data[0]?.name?.common || countryCode;
                                            })
                                            .catch(() => {
                                                el.textContent = countryCode; // Fallback if API fails
                                            });
                                    });
                                </script>
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
                                        <div class="review-date-container relative">
                                            <!-- timeAgo span - shown by default -->
                                            <span class="time-ago text-gray-500 text-xs cursor-pointer hover:text-gray-600">
                                                <?= timeAgo($roomReview['AddedDate']) ?>
                                            </span>

                                            <!-- Reviewed on span - hidden by default -->
                                            <span class="full-date text-xs text-gray-500 hidden">
                                                Reviewed on <span><?= htmlspecialchars(date('Y-m-d h:i', strtotime($roomReview['AddedDate']))) ?></span>
                                            </span>
                                        </div>
                                        <div x-data="{ open: false }" class="relative inline-block <?= ($userID === $userid) ? '' : 'hidden' ?>">
                                            <button
                                                @click="open = !open"
                                                type="button"
                                                class="text-gray-500 hover:text-gray-600 focus:outline-none transition-colors duration-200">
                                                <i class="ri-more-line text-xl"></i>
                                            </button>
                                            <div
                                                x-show="open"
                                                x-transition:enter="transition ease-out duration-100"
                                                x-transition:enter-start="transform opacity-0 scale-95"
                                                x-transition:enter-end="transform opacity-100 scale-100"
                                                x-transition:leave="transition ease-in duration-75"
                                                x-transition:leave-start="transform opacity-100 scale-100"
                                                x-transition:leave-end="transform opacity-0 scale-95"
                                                @click.away="open = false"
                                                class="absolute right-0 z-10 mt-2 w-32 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                                                role="menu"
                                                style="display: none;">
                                                <form method="post" class="delete-form py-1 select-none" role="none">
                                                    <?php $reviewID = $roomReview['ReviewID'] ?? 0; ?>
                                                    <input type="hidden" name="review_id" value="<?= $reviewID ?>">
                                                    <input type="hidden" name="delete" value="1">
                                                    <button
                                                        @click="open = false"
                                                        type="button"
                                                        class="edit-btn block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-colors duration-200"
                                                        data-review-id="<?= $reviewID ?>"
                                                        data-comment="<?= htmlspecialchars($roomReview['Comment'] ?? '') ?>">
                                                        <i class="ri-edit-line mr-2"></i> Edit
                                                    </button>
                                                    <button
                                                        @click="open = false"
                                                        type="submit"
                                                        name="delete"
                                                        class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-colors duration-200">
                                                        <i class="ri-delete-bin-line mr-2"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </div>

                                        <script>
                                            document.addEventListener('DOMContentLoaded', function() {
                                                // Get all review date containers
                                                const dateContainers = document.querySelectorAll('.review-date-container');

                                                dateContainers.forEach(container => {
                                                    const timeAgo = container.querySelector('.time-ago');
                                                    const fullDate = container.querySelector('.full-date');

                                                    // Toggle between timeAgo and full date on click
                                                    container.addEventListener('click', function() {
                                                        timeAgo.classList.add('hidden');
                                                        fullDate.classList.remove('hidden');

                                                        setTimeout(() => {
                                                            timeAgo.classList.remove('hidden');
                                                            fullDate.classList.add('hidden');
                                                        }, 2000);
                                                    });
                                                });
                                            });
                                        </script>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="flex items-center gap-2 mb-2">
                                <img class="w-5 select-none" src="<?= $roomReview['RoomType'] == 'Single Bed' ? '../UserImages/single-bed.png' : '../UserImages/bed.png' ?>" alt="">
                                <p class="text-gray-900 text-xs"><?= $roomReview['RoomType'] ?></p>
                            </div>
                            <div class="flex items-center gap-1 mb-2">
                                <?php
                                $iconPath = '../UserImages/group.png'; // default
                                if ($roomReview['TravellerType'] == 'Solo traveller') {
                                    $iconPath = '../UserImages/man.png';
                                } elseif ($roomReview['TravellerType'] == 'Couple') {
                                    $iconPath = '../UserImages/man (1).png';
                                }
                                ?>
                                <img class="w-5 select-none" src="<?= $iconPath ?>" alt="Traveller type icon">
                                <p class="text-gray-900 text-xs"><?php echo $roomReview['TravellerType'] ?? ''; ?></p>
                            </div>
                        </div>

                        <!-- Review text -->
                        <div class="review" data-review-id="<?= $reviewID ?>">
                            <p class="review-text text-gray-700 text-sm">
                                "<?= htmlspecialchars($roomReview['Comment'] ?? '') ?>"
                            </p>
                        </div>

                        <!-- Hidden edit form -->
                        <form method="post" class="edit-form hidden mt-2" data-review-id="<?= $reviewID ?>">
                            <input type="hidden" name="review_id" value="<?= $reviewID ?>">
                            <input type="hidden" name="save_edit" value="1">
                            <textarea name="updated_comment" rows="4" class="w-full border rounded p-2 text-sm outline-none"><?= htmlspecialchars($roomReview['Comment'] ?? '') ?></textarea>
                            <button type="submit" name="save_edit" class="mt-2 text-gray-600 bg-gray-200 hover:bg-gray-300 py-1 px-3 rounded text-sm outline-none select-none">Save</button>
                            <button type="button" class="cancel-edit text-sm text-gray-500 ml-2 select-none">Cancel</button>
                        </form>

                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const editBtns = document.querySelectorAll('.edit-btn');
                                editBtns.forEach(btn => {
                                    btn.addEventListener('click', function() {
                                        const reviewId = this.getAttribute('data-review-id');
                                        const comment = this.getAttribute('data-comment');
                                        const form = document.querySelector(`.edit-form[data-review-id="${reviewId}"]`);
                                        const reviewText = document.querySelector(`.review[data-review-id="${reviewId}"]`);
                                        if (!form || !reviewText) return;
                                        const textarea = form.querySelector('textarea');
                                        if (textarea) textarea.value = comment;
                                        reviewText.classList.add('hidden');
                                        form.classList.remove('hidden');
                                    });
                                });

                                const cancelBtns = document.querySelectorAll('.cancel-edit');
                                cancelBtns.forEach(btn => {
                                    btn.addEventListener('click', function() {
                                        const editForm = this.closest('.edit-form');
                                        const reviewId = editForm.getAttribute('data-review-id');
                                        const reviewText = document.querySelector(`.review[data-review-id="${reviewId}"]`);
                                        if (editForm && reviewText) {
                                            editForm.classList.add('hidden');
                                            reviewText.classList.remove('hidden');
                                        }
                                    });
                                });
                            });
                        </script>

                        <?php
                        // Initialize reaction counts and user reaction
                        $likeCount = 0;
                        $dislikeCount = 0;
                        $userReaction = null;

                        if (isset($roomReview['ReviewID'])) {
                            $reviewID = $roomReview['ReviewID'];

                            // Fetch total likes and dislikes
                            $countStmt = $connect->prepare("SELECT ReactionType, COUNT(*) as count 
                          FROM roomtypereviewrttb 
                          WHERE ReviewID = ? 
                          GROUP BY ReactionType");
                            $countStmt->bind_param("i", $reviewID);
                            $countStmt->execute();
                            $result = $countStmt->get_result();

                            while ($row = $result->fetch_assoc()) {
                                if ($row['ReactionType'] == 'like') {
                                    $likeCount = $row['count'];
                                } else {
                                    $dislikeCount = $row['count'];
                                }
                            }
                            $countStmt->close();

                            // Check if current user has reacted
                            if (isset($_SESSION['UserID'])) {
                                $userStmt = $connect->prepare("SELECT ReactionType FROM roomtypereviewrttb 
                             WHERE ReviewID = ? AND UserID = ?");
                                $userStmt->bind_param("is", $reviewID, $_SESSION['UserID']);
                                $userStmt->execute();
                                $userResult = $userStmt->get_result();

                                if ($userResult->num_rows > 0) {
                                    $userReaction = $userResult->fetch_assoc()['ReactionType'];
                                }
                                $userStmt->close();
                            }
                        }
                        ?>

                        <!-- Reactions -->
                        <form method="post" class="mt-3 text-gray-400 roomtype-reaction-form">
                            <input type="hidden" name="review_id" value="<?= htmlspecialchars($roomReview['ReviewID']) ?>">
                            <input type="hidden" name="roomTypeID" value="<?= htmlspecialchars($roomtype['RoomTypeID']) ?>">
                            <input type="hidden" name="checkin_date" value="<?= htmlspecialchars($checkin_date) ?>">
                            <input type="hidden" name="checkout_date" value="<?= htmlspecialchars($checkout_date) ?>">
                            <input type="hidden" name="adults" value="<?= htmlspecialchars($adults) ?>">
                            <input type="hidden" name="children" value="<?= htmlspecialchars($children) ?>">

                            <button type="button" class="like-btn text-xs cursor-pointer <?= ($userReaction == 'like') ? 'text-gray-500' : '' ?>">
                                <i class="ri-thumb-up-<?= ($userReaction == 'like') ? 'fill' : 'line' ?> text-sm"></i>
                                <span class="like-count"><?= $likeCount ?></span> Like
                            </button>

                            <button type="button" class="dislike-btn text-xs cursor-pointer <?= ($userReaction == 'dislike') ? 'text-gray-500' : '' ?>">
                                <i class="ri-thumb-down-<?= ($userReaction == 'dislike') ? 'fill' : 'line' ?> text-sm"></i>
                                <span class="dislike-count"><?= $dislikeCount ?></span> Dislike
                            </button>
                        </form>
                    </div>
                <?php } ?>
                <script>
                    // Fetch country names for newly loaded reviews
                    document.querySelectorAll('.country-name').forEach(el => {
                        const countryCode = el.getAttribute('data-country-code');
                        if (!el._fetched) {
                            el._fetched = true;
                            fetch(`https://restcountries.com/v3.1/alpha/${countryCode}`)
                                .then(response => response.json())
                                .then(data => {
                                    el.textContent = data[0]?.name?.common || countryCode;
                                })
                                .catch(() => {
                                    el.textContent = countryCode;
                                });
                        }
                    });
                </script>
            <?php } else { ?>
                <div class="flex items-center justify-center h-full w-full py-8">
                    <p class="mt-10 py-36 flex justify-center text-center text-base text-gray-400">We couldn't find any reviews matching your search criteria.</p>
                </div>
            <?php } ?>
        </div>
    </div>
<?php
} else {
?>
    <p class="mt-10 py-36 flex justify-center text-gray-600 text-center text-base">No reviews found with selected filters.</p>
<?php
}
?>