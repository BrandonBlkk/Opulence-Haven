<?php
include(__DIR__ . '/../../config/db_connection.php');
include(__DIR__ . '/../admin_pagination.php');

// Construct the room type query based on search
if (!empty($searchRoomTypeQuery)) {
    $roomTypeSelect = "SELECT * FROM roomtypetb WHERE RoomType LIKE '%$searchRoomTypeQuery%' OR RoomDescription LIKE '%$searchRoomTypeQuery%' LIMIT $rowsPerPage OFFSET $roomTypeOffset";
} else {
    $roomTypeSelect = "SELECT * FROM roomtypetb LIMIT $rowsPerPage OFFSET $roomTypeOffset";
}

$roomTypeSelectQuery = $connect->query($roomTypeSelect);
$roomTypes = [];

if (mysqli_num_rows($roomTypeSelectQuery) > 0) {
    while ($row = $roomTypeSelectQuery->fetch_assoc()) {
        $roomTypes[] = $row;
    }
}
?>

<table class="min-w-full bg-white rounded-lg">
    <thead>
        <tr class="bg-gray-100 text-gray-600 text-sm">
            <th class="p-3 text-start">ID</th>
            <th class="p-3 text-start">Cover Image</th>
            <th class="p-3 text-start">Type</th>
            <th class="p-3 text-start hidden sm:table-cell">Description</th>
            <th class="p-3 text-start hidden sm:table-cell">Capacity</th>
            <th class="p-3 text-start">Actions</th>
        </tr>
    </thead>
    <tbody class="text-gray-600 text-sm">
        <?php if (!empty($roomTypes)): ?>
            <?php foreach ($roomTypes as $roomType): ?>
                <tr class="border-b border-gray-200 hover:bg-gray-50">
                    <td class="p-3 text-start whitespace-nowrap">
                        <div class="flex items-center gap-2 font-medium text-gray-500">
                            <input type="checkbox" class="form-checkbox h-3 w-3 border-2 text-amber-500">
                            <span><?= htmlspecialchars($roomType['RoomTypeID']) ?></span>
                        </div>
                    </td>
                    <td class="p-3 text-start select-none">
                        <img src="<?= htmlspecialchars($roomType['RoomCoverImage']) ?>" alt="Product Image" class="w-12 h-12 object-cover rounded-sm">
                    </td>
                    <td class="p-3 text-start">
                        <?= htmlspecialchars($roomType['RoomType']) ?>
                    </td>
                    <td class="p-3 text-start hidden sm:table-cell">
                        <?= htmlspecialchars($roomType['RoomDescription']) ?>
                    </td>
                    <td class="p-3 text-start hidden sm:table-cell">
                        <?= htmlspecialchars($roomType['RoomCapacity']) ?>
                    </td>
                    <td class="p-3 text-start space-x-1 select-none">
                        <i class="details-btn ri-eye-line text-lg cursor-pointer"
                            data-roomtype-id="<?= htmlspecialchars($roomType['RoomTypeID']) ?>"></i>
                        <button class="text-red-500">
                            <i class="delete-btn ri-delete-bin-7-line text-xl"
                                data-roomtype-id="<?= htmlspecialchars($roomType['RoomTypeID']) ?>"></i>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" class="p-3 text-center text-gray-500 py-52">
                    No room types available.
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<!-- Add these to your head section -->
<link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css">

<!-- After your table, add this modal -->
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 z-50 hidden items-center justify-center p-4">
    <div class="relative w-full max-w-4xl">
        <!-- Close button -->
        <button id="closeModal" class="absolute top-0 right-0 m-4 text-white text-3xl z-50"></button>

        <!-- Swiper container -->
        <div class="swiper-container">
            <div class="swiper-wrapper">
                <!-- Slides will be added dynamically via JavaScript -->
            </div>
            <!-- Add navigation buttons -->
            <div class="swiper-button-next text-white"></div>
            <div class="swiper-button-prev text-white"></div>
            <!-- Add pagination -->
            <div class="swiper-pagination"></div>
        </div>
    </div>
</div>

<!-- Add this script at the end of your body -->
<script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize modal elements
        const modal = document.getElementById('imageModal');
        const closeBtn = document.getElementById('closeModal');
        const swiperWrapper = document.querySelector('.swiper-wrapper');

        // Get all table images
        const tableImages = document.querySelectorAll('tbody img[alt="Product Image"]');

        // Create a Swiper instance (will be initialized properly when modal opens)
        let swiper;

        // Add click event to each image
        tableImages.forEach(img => {
            img.style.cursor = 'pointer'; // Make it obvious it's clickable
            img.addEventListener('click', function() {
                // Get all images for this room type (assuming you might have multiple)
                const roomTypeId = this.closest('tr').querySelector('.details-btn').getAttribute('data-roomtype-id');

                // In a real app, you might fetch additional images from the server
                // For this example, we'll just use the cover image
                const images = [this.src];

                // Clear previous slides
                swiperWrapper.innerHTML = '';

                // Add new slides
                images.forEach(imageUrl => {
                    const slide = document.createElement('div');
                    slide.className = 'swiper-slide';
                    slide.innerHTML = `<img src="${imageUrl}" class="w-full h-full object-contain" alt="Room Image">`;
                    swiperWrapper.appendChild(slide);
                });

                // Initialize or update Swiper
                if (swiper) {
                    swiper.update();
                } else {
                    swiper = new Swiper('.swiper-container', {
                        loop: true,
                        pagination: {
                            el: '.swiper-pagination',
                            clickable: true,
                        },
                        navigation: {
                            nextEl: '.swiper-button-next',
                            prevEl: '.swiper-button-prev',
                        },
                    });
                }

                // Show modal
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            });
        });

        // Close modal
        closeBtn.addEventListener('click', function() {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        });

        // Close when clicking outside content
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }
        });
    });
</script>

<style>
    /* Custom styles for the swiper in modal */
    .swiper-container {
        width: 100%;
        height: 80vh;
    }

    .swiper-slide img {
        max-height: 100%;
        max-width: 100%;
        margin: 0 auto;
        display: block;
    }
</style>

<script>
    // Function to load a specific page
    function loadRoomTypePage(page) {
        const urlParams = new URLSearchParams(window.location.search);
        const searchQuery = urlParams.get('roomtype_search') || '';

        // Update URL parameters
        urlParams.set('roomtypepage', page);
        if (searchQuery) urlParams.set('roomtype_search', searchQuery);

        const xhr = new XMLHttpRequest();
        xhr.open('GET', `../includes/admin_table_components/roomtype_results.php?${urlParams.toString()}`, true);

        xhr.onload = function() {
            if (this.status === 200) {
                document.getElementById('roomTypeResults').innerHTML = this.responseText;

                // Also update the pagination controls
                const xhrPagination = new XMLHttpRequest();
                xhrPagination.open('GET', `../includes/admin_table_components/roomtype_pagination.php?${urlParams.toString()}`, true);
                xhrPagination.onload = function() {
                    if (this.status === 200) {
                        document.getElementById('paginationContainer').innerHTML = this.responseText;
                    }
                };
                xhrPagination.send();

                window.history.pushState({}, '', `?${urlParams.toString()}`);
                window.scrollTo(0, 0);
                initializeRoomTypeActionButtons();
            }
        };

        xhr.send();
    }

    // Function to handle search
    function handleSearch() {
        const searchInput = document.querySelector('input[name="roomtype_search"]');

        // Reset to page 1 when searching
        loadRoomTypePage(1);
    }

    // Initialize event listeners for search
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.querySelector('input[name="roomtype_search"]');

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const urlParams = new URLSearchParams(window.location.search);
                urlParams.set('roomtype_search', this.value);
                window.history.pushState({}, '', `?${urlParams.toString()}`);
                handleSearch();
            });
        }

        initializeRoomTypeActionButtons();
    });

    // Function to initialize action buttons for room types
    function initializeRoomTypeActionButtons() {
        // Function to attach event listeners to a row
        const attachEventListenersToRow = (row) => {
            // Details button
            const detailsBtn = row.querySelector('.details-btn');
            if (detailsBtn) {
                detailsBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const roomTypeId = this.getAttribute('data-roomtype-id');
                    darkOverlay2.classList.remove('opacity-0', 'invisible');
                    darkOverlay2.classList.add('opacity-100');

                    fetch(`../Admin/AddRoomType.php?action=getRoomTypeDetails&id=${roomTypeId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById('updateRoomTypeID').value = roomTypeId;
                                document.getElementById('updateRoomTypeInput').value = data.roomtype.RoomType;
                                document.getElementById('updateRoomTypeDescriptionInput').value = data.roomtype.RoomDescription;
                                document.getElementById('updateRoomCapacityInput').value = data.roomtype.RoomCapacity;
                                updateRoomTypeModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                            } else {
                                console.error('Failed to load room type details');
                            }
                        })
                        .catch(error => console.error('Fetch error:', error));
                });
            }

            // Delete button
            const deleteBtn = row.querySelector('.delete-btn');
            if (deleteBtn) {
                deleteBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const roomTypeId = this.getAttribute('data-roomtype-id');
                    darkOverlay2.classList.remove('opacity-0', 'invisible');
                    darkOverlay2.classList.add('opacity-100');

                    fetch(`../Admin/AddRoomType.php?action=getRoomTypeDetails&id=${roomTypeId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById('deleteRoomTypeID').value = roomTypeId;
                                document.getElementById('roomTypeDeleteName').textContent = data.roomtype.RoomType;
                                roomTypeConfirmDeleteModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                            } else {
                                console.error('Failed to load room type details');
                            }
                        })
                        .catch(error => console.error('Fetch error:', error));
                });
            }
        };

        // Initialize all existing rows
        document.querySelectorAll('tbody tr').forEach(row => {
            attachEventListenersToRow(row);
        });
    }

    // Handle browser back/forward buttons
    window.addEventListener('popstate', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const searchInput = document.querySelector('input[name="roomtype_search"]');
        if (searchInput) {
            searchInput.value = urlParams.get('roomtype_search') || '';
        }
        loadRoomTypePage(urlParams.get('roomtypepage') || 1);
    });
</script>