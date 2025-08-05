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
        $roomTypeFilter = " AND rr.RoomTypeID IN (" . implode(',', array_map('intval', $selectedRoomTypes)) . ")";
    }
?>
    <div id="review-section" class="space-y-4 mb-8 w-full h-[80vh] overflow-y-auto">
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
                                        <span class="text-gray-500 text-sm"><?= timeAgo($roomReview['AddedDate']) ?></span>
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
                        <p class="text-gray-700 text-sm">
                            "<?php echo $roomReview['Comment'] ?? ''; ?>"
                        </p>
                    </div>
                <?php } ?>
                <script>
                    // Fetch country names for newly loaded reviews
                    document.querySelectorAll('.country-name').forEach(el => {
                        const countryCode = el.getAttribute('data-country-code');
                        if (!el._fetched) { // Only fetch if not already fetched
                            el._fetched = true;
                            fetch(`https://restcountries.com/v3.1/alpha/${countryCode}`)
                                .then(response => response.json())
                                .then(data => {
                                    el.textContent = data[0]?.name?.common || countryCode;
                                })
                                .catch(() => {
                                    el.textContent = countryCode; // Fallback if API fails
                                });
                        }
                    });
                </script>
            <?php } else { ?>
                <div class="flex items-center justify-center h-full w-full py-8">
                    <p class="mt-10 py-36 flex justify-center text-center text-base text-gray-400">We couldn't find any properties matching your search criteria.</p>
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-submit for all filters
        document.querySelectorAll('.auto-submit').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const showLoading = this.dataset.clicked === 'false';
                if (showLoading) {
                    this.dataset.clicked = 'true';
                }
                submitReviewFilterForm(showLoading);
            });
        });

        // Function to submit filter form via AJAX
        function submitReviewFilterForm(showLoading) {
            const formData = new URLSearchParams();

            // Get selected ratings
            const ratingCheckboxes = document.querySelectorAll('input[name="ratings[]"]:checked');
            ratingCheckboxes.forEach(checkbox => {
                formData.append('ratings[]', checkbox.value);
            });

            // Get selected traveller types
            const travellerTypeCheckboxes = document.querySelectorAll('input[name="traveller_type[]"]:checked');
            travellerTypeCheckboxes.forEach(checkbox => {
                formData.append('traveller_type[]', checkbox.value);
            });

            // Get selected room types
            const roomTypeCheckboxes = document.querySelectorAll('input[name="roomtypes[]"]:checked');
            roomTypeCheckboxes.forEach(checkbox => {
                formData.append('roomtypes[]', checkbox.value);
            });

            // Get current URL parameters
            const currentParams = new URLSearchParams(window.location.search);
            currentParams.forEach((value, key) => {
                if (key !== 'ratings[]' && key !== 'traveller_type[]' && key !== 'roomtypes[]') {
                    formData.append(key, value);
                }
            });

            formData.append('ajax_request', '1');

            // Show loading state if requested
            if (showLoading) {
                showReviewLoadingState();
            }

            // Fetch results
            fetchReviewResults(formData, showLoading);
        }

        // Function to show loading state
        function showReviewLoadingState() {
            document.getElementById('reviews-container').innerHTML = `
            <div class="w-[80%] space-y-4">
                ${Array(3).fill().map(() => `
                <div class="bg-white p-4 animate-pulse">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 rounded-full bg-gray-200 mr-3"></div>
                        <div class="space-y-2 flex-1">
                            <div class="h-4 bg-gray-200 rounded w-1/3"></div>
                            <div class="h-3 bg-gray-200 rounded w-1/4"></div>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div class="h-4 bg-gray-200 rounded w-full"></div>
                        <div class="h-4 bg-gray-200 rounded w-5/6"></div>
                        <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                    </div>
                </div>
                `).join('')}
            </div>
        `;
        }

        // Function to fetch and display results
        function fetchReviewResults(formData, shouldDelay) {
            const url = window.location.pathname + '?' + formData.toString();

            fetch(url, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.text();
                })
                .then(data => {
                    const processData = () => {
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = data;

                        // Update the reviews container
                        const newContent = tempDiv.querySelector('#reviews-container');
                        if (newContent) {
                            document.getElementById('reviews-container').innerHTML = newContent.innerHTML;
                            window.history.pushState({
                                path: url.toString()
                            }, '', url.toString());

                            // Trigger country name fetch for new reviews
                            fetchCountryNames();
                        } else {
                            throw new Error('Invalid response format');
                        }
                    };

                    if (shouldDelay) {
                        setTimeout(processData, 1000); // Delay for first click
                    } else {
                        processData(); // No delay for subsequent clicks
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('reviews-container').innerHTML = `
                <div class="bg-white p-8 rounded-lg shadow-sm border text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h2 class="text-xl font-bold text-gray-800 mt-4">Error Loading Reviews</h2>
                    <p class="text-gray-600 mt-2">We couldn't load the reviews. Please try again.</p>
                    <div class="mt-6">
                        <button onclick="window.location.reload()" class="inline-block bg-orange-500 text-white px-6 py-2 rounded-md hover:bg-orange-600 transition-colors select-none">
                            Try Again
                        </button>
                    </div>
                </div>
            `;
                });
        }

        // Function to fetch country names
        function fetchCountryNames() {
            document.querySelectorAll('.country-name').forEach(el => {
                const countryCode = el.getAttribute('data-country-code');
                if (!el._fetched) { // Only fetch if not already fetched
                    el._fetched = true;
                    fetch(`https://restcountries.com/v3.1/alpha/${countryCode}`)
                        .then(response => response.json())
                        .then(data => {
                            el.textContent = data[0]?.name?.common || countryCode;
                        })
                        .catch(() => {
                            el.textContent = countryCode; // Fallback if API fails
                        });
                }
            });
        }

        // Initial fetch of country names
        fetchCountryNames();
    });
</script>