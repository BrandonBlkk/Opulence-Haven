<?php
include(__DIR__ . '/../../config/db_connection.php');
include(__DIR__ . '/../admin_pagination.php');

// Construct the room type query based on search
if ($filterRoomType !== 'random' && !empty($searchRoomQuery)) {
    $roomSelect = "SELECT * FROM roomtb WHERE RoomTypeID = '$filterRoomType' AND (RoomName LIKE '%$searchRoomQuery%') LIMIT $rowsPerPage OFFSET $roomOffset";
} elseif ($filterRoomType !== 'random') {
    $roomSelect = "SELECT * FROM roomtb WHERE RoomTypeID = '$filterRoomType' LIMIT $rowsPerPage OFFSET $roomOffset";
} elseif (!empty($searchRoomQuery)) {
    $roomSelect = "SELECT * FROM roomtb WHERE RoomName LIKE '%$searchRoomQuery%' LIMIT $rowsPerPage OFFSET $roomOffset";
} else {
    $roomSelect = "SELECT * FROM roomtb LIMIT $rowsPerPage OFFSET $roomOffset";
}

$roomSelectQuery = $connect->query($roomSelect);
$rooms = [];

if (mysqli_num_rows($roomSelectQuery) > 0) {
    while ($row = $roomSelectQuery->fetch_assoc()) {
        $rooms[] = $row;
    }
}
?>

<table class="min-w-full bg-white rounded-lg">
    <thead>
        <tr class="bg-gray-100 text-gray-600 text-sm">
            <th class="p-3 text-start">ID</th>
            <th class="p-3 text-start hidden sm:table-cell">Room</th>
            <th class="p-3 text-start hidden sm:table-cell">Status</th>
            <th class="p-3 text-start hidden sm:table-cell">Room Type</th>
            <th class="p-3 text-start">Actions</th>
        </tr>
    </thead>
    <tbody class="text-gray-600 text-sm">
        <?php if (!empty($rooms)): ?>
            <?php foreach ($rooms as $room): ?>
                <tr class="border-b border-gray-200 hover:bg-gray-50">
                    <td class="p-3 text-start whitespace-nowrap">
                        <div class="flex items-center gap-2 font-medium text-gray-500">
                            <input type="checkbox" class="form-checkbox h-3 w-3 border-2 text-amber-500">
                            <span><?= htmlspecialchars($room['RoomID']) ?></span>
                        </div>
                    </td>
                    <td class="p-3 text-start">
                        <?= htmlspecialchars($room['RoomName']) ?>
                    </td>
                    <td class="p-3 text-start hidden sm:table-cell">
                        <?= htmlspecialchars($room['RoomStatus']) ?>
                    </td>
                    <td class="p-3 text-start hidden md:table-cell">
                        <?php
                        // Fetch the specific product type for the supplier
                        $roomTypeID = $room['RoomTypeID'];
                        $roomTypeQuery = "SELECT RoomType FROM roomtypetb WHERE RoomTypeID = '$roomTypeID'";
                        $roomTypeResult = mysqli_query($connect, $roomTypeQuery);

                        if ($roomTypeResult && $roomTypeResult->num_rows > 0) {
                            $roomTypeRow = $roomTypeResult->fetch_assoc();
                            echo htmlspecialchars($roomTypeRow['RoomType']);
                        }
                        ?>
                    </td>
                    <td class="p-3 text-start space-x-1 select-none">
                        <i class="details-btn ri-eye-line text-lg cursor-pointer"
                            data-room-id="<?= htmlspecialchars($room['RoomID']) ?>"></i>
                        <button class="text-red-500">
                            <i class="delete-btn ri-delete-bin-7-line text-xl"
                                data-room-id="<?= htmlspecialchars($room['RoomID']) ?>"></i>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" class="p-3 text-center text-gray-500 py-52">
                    No rooms available.
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<script>
    // Function to load a specific page
    function loadRoomPage(page) {
        const urlParams = new URLSearchParams(window.location.search);
        const searchQuery = urlParams.get('room_search') || '';
        const filter = urlParams.get('sort') || '';

        // Update URL parameters
        urlParams.set('roompage', page);
        if (searchQuery) urlParams.set('room_search', searchQuery);
        if (filter) urlParams.set('sort', filter);

        const xhr = new XMLHttpRequest();
        xhr.open('GET', `../includes/admin_table_components/room_results.php?${urlParams.toString()}`, true);

        xhr.onload = function() {
            if (this.status === 200) {
                document.getElementById('roomResults').innerHTML = this.responseText;

                // Also update the pagination controls
                const xhrPagination = new XMLHttpRequest();
                xhrPagination.open('GET', `../includes/admin_table_components/room_pagination.php?${urlParams.toString()}`, true);
                xhrPagination.onload = function() {
                    if (this.status === 200) {
                        document.getElementById('paginationContainer').innerHTML = this.responseText;
                    }
                };
                xhrPagination.send();

                window.history.pushState({}, '', `?${urlParams.toString()}`);
                window.scrollTo(0, 0);
                initializeRoomActionButtons();
            }
        };

        xhr.send();
    }

    // Function to handle search and filter
    function handleSearchFilter() {
        // Reset to page 1 when searching or filtering
        loadRoomPage(1);
    }

    // Initialize event listeners for search and filter
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.querySelector('input[name="room_search"]');
        const filterSelect = document.querySelector('select[name="sort"]');

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const urlParams = new URLSearchParams(window.location.search);
                urlParams.set('room_search', this.value);
                window.history.pushState({}, '', `?${urlParams.toString()}`);
                handleSearchFilter();
            });
        }

        if (filterSelect) {
            filterSelect.addEventListener('change', function() {
                const urlParams = new URLSearchParams(window.location.search);
                urlParams.set('sort', this.value);
                window.history.pushState({}, '', `?${urlParams.toString()}`);
                handleSearchFilter();
            });
        }

        initializeRoomActionButtons();
    });

    // Function to initialize action buttons for rooms
    function initializeRoomActionButtons() {
        // Function to attach event listeners to a row
        const attachEventListenersToRow = (row) => {
            // Details button
            const detailsBtn = row.querySelector('.details-btn');
            if (detailsBtn) {
                detailsBtn.addEventListener('click', function() {
                    const roomId = this.getAttribute('data-room-id');
                    darkOverlay2.classList.remove('opacity-0', 'invisible');
                    darkOverlay2.classList.add('opacity-100');

                    fetch(`../Admin/AddRoom.php?action=getRoomDetails&id=${roomId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById('updateRoomID').value = roomId;
                                document.querySelector('[name="updateroomname"]').value = data.room.RoomName;
                                document.querySelector('[name="updateroomstatus"]').value = data.room.RoomStatus;
                                document.querySelector('[name="updateroomtype"]').value = data.room.RoomTypeID;
                                updateRoomModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                            } else {
                                console.error('Failed to load room details');
                            }
                        })
                        .catch(error => console.error('Fetch error:', error));
                });
            }

            // Delete button
            const deleteBtn = row.querySelector('.delete-btn');
            if (deleteBtn) {
                deleteBtn.addEventListener('click', function() {
                    const roomId = this.getAttribute('data-room-id');
                    darkOverlay2.classList.remove('opacity-0', 'invisible');
                    darkOverlay2.classList.add('opacity-100');

                    fetch(`../Admin/AddRoom.php?action=getRoomDetails&id=${roomId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById('deleteRoomID').value = roomId;
                                document.getElementById('roomDeleteName').textContent = data.room.RoomName;
                                roomConfirmDeleteModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                            } else {
                                console.error('Failed to load room details');
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
        const searchInput = document.querySelector('input[name="room_search"]');
        const filterSelect = document.querySelector('select[name="sort"]');

        if (searchInput) {
            searchInput.value = urlParams.get('room_search') || '';
        }
        if (filterSelect) {
            filterSelect.value = urlParams.get('sort') || '';
        }
        loadRoomPage(urlParams.get('roompage') || 1);
    });
</script>