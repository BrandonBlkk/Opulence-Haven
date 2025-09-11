<div id="writeReviewModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
    <div class="bg-white w-full md:w-1/2 lg:w-1/3 mx-4 p-6 rounded-md shadow-md max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl text-gray-700 font-bold mb-4">Write a Review</h2>
        <form class="flex flex-col space-y-4" action="<?php echo $_SERVER["PHP_SELF"]; ?>?roomTypeID=<?= $roomtype_id ?>&checkin_date=<?= $checkin_date ?>&checkout_date=<?= $checkout_date ?>&adults=<?= $adults ?>&children=<?= $children ?>" method="post" id="reviewForm">
            <!-- Inside your review form -->
            <input type="hidden" name="roomTypeID" value="<?= htmlspecialchars($roomtype['RoomTypeID']) ?>">

            <!-- Traveller Type -->
            <div class="relative">
                <label class="block text-sm font-medium text-gray-700 mb-1">Traveller Type</label>
                <select
                    id="travellerTypeSelect"
                    class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                    name="travellertype">
                    <option value="">Select Traveller Type</option>
                    <option value="Family">Family</option>
                    <option value="Couple">Couple</option>
                    <option value="Group of friends">Group of friends</option>
                    <option value="Solo traveller">Solo traveller</option>
                    <option value="Business traveller">Business traveller</option>
                </select>
                <small id="travellerTypeError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
            </div>

            <!-- Star Rating -->
            <div class="relative">
                <label class="block text-sm font-medium text-gray-700 mb-1">Rating</label>
                <div class="flex space-x-1 select-none">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <button type="button" class="star-rating text-2xl" data-rating="<?= $i ?>">
                            <span class="text-gray-300 hover:text-amber-400">★</span>
                        </button>
                    <?php endfor; ?>
                </div>
                <input type="hidden" id="ratingValue" name="rating" value="0">
                <small id="ratingError" class="absolute left-2 -bottom-3 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
            </div>

            <!-- Country Select with Flags -->
            <div class="relative">
                <div class="flex items-center border rounded overflow-hidden">
                    <div id="countryFlag" class="pl-2">
                        <img src="https://flagcdn.com/w20/mm.png" class="w-5 h-3.5" alt="Flag">
                    </div>
                    <select id="countryDropdown" name="country" class="border-none p-2 rounded text-sm w-full focus:outline-none">
                        <option value="">Loading...</option>
                    </select>
                </div>
                <small id="countryError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
            </div>

            <!-- Review Text -->
            <div class="relative">
                <label class="block text-sm font-medium text-gray-700 mb-1">Your Review</label>
                <textarea
                    id="reviewInput"
                    class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out min-h-[150px]"
                    name="reviewtext"
                    placeholder="Share your experience..."></textarea>
                <small id="reviewError" class="absolute left-2 -bottom-1 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
            </div>

            <input type="hidden" name="submitreview" value="1">

            <!-- Form Actions -->
            <div class="flex justify-end gap-4 select-none pt-4">
                <div id="ReviewCancelBtn" class="px-4 py-2 text-amber-500 font-semibold hover:text-amber-600 cursor-pointer">
                    Cancel
                </div>
                <button
                    type="submit"
                    name="submitreview"
                    class="bg-amber-500 text-white font-semibold px-4 py-2 rounded-sm select-none hover:bg-amber-600 transition-colors">
                    Submit Review
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Star rating functionality
    document.addEventListener('DOMContentLoaded', function() {
        const stars = document.querySelectorAll('.star-rating');
        const ratingInput = document.getElementById('ratingValue');

        stars.forEach(star => {
            star.addEventListener('click', function() {
                const rating = parseInt(this.getAttribute('data-rating'));
                ratingInput.value = rating;

                // Update star display
                stars.forEach((s, index) => {
                    const starIcon = s.querySelector('span');
                    if (index < rating) {
                        starIcon.classList.add('text-amber-400');
                        starIcon.classList.remove('text-gray-300');
                    } else {
                        starIcon.classList.add('text-gray-300');
                        starIcon.classList.remove('text-amber-400');
                    }
                });
            });
        });
    });
</script>

<div id="darkOverlay2" class="fixed inset-0 bg-black bg-opacity-50 opacity-0 invisible z-40 transition-opacity duration-300"></div>

<script>
    // Write Room Review
    document.addEventListener("DOMContentLoaded", () => {
        const writeReviewModal = document.getElementById('writeReviewModal');
        const writeReview = document.getElementById('writeReview');
        const roomReview = document.getElementById('roomReview');
        const ReviewCancelBtn = document.getElementById('ReviewCancelBtn');
        const darkOverlay2 = document.getElementById('darkOverlay2');

        if (writeReviewModal && writeReview && ReviewCancelBtn && darkOverlay2) {
            // Show modal
            writeReview.addEventListener('click', () => {
                roomReview.style.top = '100%';
                darkOverlay2.classList.remove('opacity-0', 'invisible');
                darkOverlay2.classList.add('opacity-100');
                writeReviewModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
            });

            // Cancel button functionality
            ReviewCancelBtn.addEventListener('click', () => {
                roomReview.style.top = '0%';
                writeReviewModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
                darkOverlay2.classList.add('opacity-0', 'invisible');
                darkOverlay2.classList.remove('opacity-100');
            });
        }
    });

    // Enhanced Country Dropdown with Flags
    const dropdown = document.getElementById('countryDropdown');
    const selectedFlag = document.getElementById('selectedFlag');
    const countryFlag = document.getElementById('countryFlag');
    const phoneInput = document.getElementById('contactPhoneInput');

    const fetchCountries = async () => {
        try {
            const response = await fetch('https://countriesnow.space/api/v0.1/countries/info?returns=flag,unicodeFlag,dialCode,name,iso2');
            if (!response.ok) {
                throw new Error('Failed to fetch countries');
            }
            const data = await response.json();

            // Convert API response to match the expected format
            const countries = data.data.map(country => ({
                cca2: country.iso2,
                name: {
                    common: country.name
                },
                idd: {
                    root: country.dialCode || ""
                },
                flags: {
                    png: country.flag || `https://flagcdn.com/w20/${country.iso2.toLowerCase()}.png`
                }
            }));

            populateDropdown(countries);
            populateCountryCodeDropdown(countries);
        } catch (error) {
            console.error(error);
            dropdown.innerHTML = '<option value="">Error loading countries</option>';
        }
    }

    const populateDropdown = (countries) => {
        dropdown.innerHTML = '<option value="">Select a country</option>';
        countries.sort((a, b) => a.name.common.localeCompare(b.name.common));

        countries.forEach(country => {
            const option = document.createElement('option');
            option.value = country.cca2;
            option.dataset.flag = country.flags.png;
            option.textContent = `${country.name.common}`;
            if (country.cca2 === "MM") {
                option.selected = true;
            }
            dropdown.appendChild(option);
        });
    }

    const populateCountryCodeDropdown = (countries) => {
        // Filter countries that have calling codes
        const countriesWithCallingCodes = countries.filter(c => c.idd && c.idd.root);
    }

    // Update flag when country dropdown changes
    dropdown.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            const flagUrl = selectedOption.dataset.flag;
            countryFlag.innerHTML = `<img src="${flagUrl}" class="w-5 h-3.5" alt="Flag">`;
        }
    });

    // Initialize with Myanmar as default
    fetchCountries();
</script>