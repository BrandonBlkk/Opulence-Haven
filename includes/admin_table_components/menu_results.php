<?php
include(__DIR__ . '/../../config/db_connection.php');
include(__DIR__ . '/../admin_pagination.php');

// Construct the menu query based on search
if ($filterStatus !== 'random' && !empty($searchMenuQuery)) {
    $menuSelect = "SELECT * FROM menutb WHERE Status = '$filterStatus' AND (MenuName LIKE '%$searchMenuQuery%') LIMIT $rowsPerPage OFFSET $menuOffset";
} elseif ($filterStatus !== 'random') {
    $menuSelect = "SELECT * FROM menutb WHERE Status = '$filterStatus' LIMIT $rowsPerPage OFFSET $menuOffset";
} elseif (!empty($searchMenuQuery)) {
    $menuSelect = "SELECT * FROM menutb WHERE MenuName LIKE '%$searchMenuQuery%' LIMIT $rowsPerPage OFFSET $menuOffset";
} else {
    $menuSelect = "SELECT * FROM menutb LIMIT $rowsPerPage OFFSET $menuOffset";
}

$menuSelectQuery = $connect->query($menuSelect);
$menus = [];

if (mysqli_num_rows($menuSelectQuery) > 0) {
    while ($row = $menuSelectQuery->fetch_assoc()) {
        $menus[] = $row;
    }
}
?>

<table class="min-w-full bg-white rounded-lg">
    <thead>
        <tr class="bg-gray-100 text-gray-600 text-sm">
            <th class="p-3 text-start">ID</th>
            <th class="p-3 text-start">Menu</th>
            <th class="p-3 text-start hidden sm:table-cell">Description</th>
            <th class="p-3 text-start hidden sm:table-cell">Start Time</th>
            <th class="p-3 text-start hidden sm:table-cell">End Time</th>
            <th class="p-3 text-start hidden sm:table-cell">Status</th>
            <th class="p-3 text-start">Actions</th>
        </tr>
    </thead>
    <tbody class="text-gray-600 text-sm">
        <?php if (!empty($menus)): ?>
            <?php foreach ($menus as $menu): ?>
                <tr class="border-b border-gray-200 hover:bg-gray-50">
                    <td class="p-3 text-start whitespace-nowrap">
                        <div class="flex items-center gap-2 font-medium text-gray-500">
                            <input type="checkbox" class="form-checkbox h-3 w-3 border-2 text-amber-500">
                            <span><?= htmlspecialchars($menu['MenuID']) ?></span>
                        </div>
                    </td>
                    <td class="p-3 text-start">
                        <?= htmlspecialchars($menu['MenuName']) ?>
                    </td>
                    <td class="p-3 text-start">
                        <?= htmlspecialchars($menu['Description']) ?>
                    </td>
                    <td class="p-3 text-start">
                        <?= htmlspecialchars($menu['StartTime']) ?>
                    </td>
                    <td class="p-3 text-start">
                        <?= htmlspecialchars($menu['EndTime']) ?>
                    </td>
                    <td class="p-3 text-start">
                        <?= htmlspecialchars($menu['Status']) ?>
                    </td>

                    <td class="p-3 text-start space-x-1 select-none">
                        <i class="details-btn ri-eye-line text-lg cursor-pointer"
                            data-menu-id="<?= htmlspecialchars($menu['MenuID']) ?>"></i>
                        <button class="text-red-500">
                            <i class="delete-btn ri-delete-bin-7-line text-xl"
                                data-menu-id="<?= htmlspecialchars($menu['MenuID']) ?>"></i>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" class="p-3 text-center text-gray-500 py-52">
                    No menus available.
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<script>
    // Function to load a specific page
    function loadMenuPage(page) {
        const urlParams = new URLSearchParams(window.location.search);
        const searchQuery = urlParams.get('menu_search') || '';

        // Update URL parameters
        urlParams.set('menupage', page);
        if (searchQuery) urlParams.set('menu_search', searchQuery);

        const xhr = new XMLHttpRequest();
        xhr.open('GET', `../includes/admin_table_components/menu_results.php?${urlParams.toString()}`, true);

        xhr.onload = function() {
            if (this.status === 200) {
                document.getElementById('menuResults').innerHTML = this.responseText;

                // Also update the pagination controls
                const xhrPagination = new XMLHttpRequest();
                xhrPagination.open('GET', `../includes/admin_table_components/menu_pagination.php?${urlParams.toString()}`, true);
                xhrPagination.onload = function() {
                    if (this.status === 200) {
                        document.getElementById('paginationContainer').innerHTML = this.responseText;
                    }
                };
                xhrPagination.send();

                window.history.pushState({}, '', `?${urlParams.toString()}`);
                window.scrollTo(0, 0);
                initializeMenuActionButtons();
            }
        };

        xhr.send();
    }

    // Function to handle search
    function handleSearch() {
        const searchInput = document.querySelector('input[name="menu_search"]');

        // Reset to page 1 when searching
        loadMenuPage(1);
    }

    // Initialize event listeners for search
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.querySelector('input[name="menu_search"]');

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const urlParams = new URLSearchParams(window.location.search);
                urlParams.set('menu_search', this.value);
                window.history.pushState({}, '', `?${urlParams.toString()}`);
                handleSearch();
            });
        }

        initializeMenuActionButtons();
    });

    // Function to initialize action buttons for facility types
    function initializeMenuActionButtons() {
        // Function to attach event listeners to a row
        const attachEventListenersToRow = (row) => {
            // Details button
            const detailsBtn = row.querySelector('.details-btn');
            if (detailsBtn) {
                detailsBtn.addEventListener('click', function() {
                    const menuId = this.getAttribute('data-menu-id');
                    darkOverlay2.classList.remove('opacity-0', 'invisible');
                    darkOverlay2.classList.add('opacity-100');

                    fetch(`../Admin/add_menu.php?action=getMenuDetails&id=${menuId}`)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                document.getElementById('updateMenuID').value = menuId;
                                document.getElementById('updateMenuNameInput').value = data.menu.MenuName;
                                document.getElementById('updateMenuDescriptionInput').value = data.menu.Description;
                                document.getElementById('updateStartTimeInput').value = data.menu.StartTime;
                                document.getElementById('updateEndTimeInput').value = data.menu.EndTime;
                                document.getElementById('updateStatusSelect').value = data.menu.Status;
                                updateMenuModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                            } else {
                                console.error('Failed to load menu details');
                            }
                        })
                        .catch(error => console.error('Fetch error:', error));
                });
            }

            // Delete button
            const deleteBtn = row.querySelector('.delete-btn');
            if (deleteBtn) {
                deleteBtn.addEventListener('click', function() {
                    const menuId = this.getAttribute('data-menu-id');
                    darkOverlay2.classList.remove('opacity-0', 'invisible');
                    darkOverlay2.classList.add('opacity-100');

                    document.getElementById('deleteMenuID').value = menuId;
                    document.getElementById('menuDeleteName').textContent = this.closest('tr').querySelector('td:nth-child(2)').textContent;
                    menuConfirmDeleteModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
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
        const searchInput = document.querySelector('input[name="menu_search"]');
        if (searchInput) {
            searchInput.value = urlParams.get('menu_search') || '';
        }
        loadMenuPage(urlParams.get('menupage') || 1);
    });
</script>