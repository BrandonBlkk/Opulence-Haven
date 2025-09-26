<?php
require_once(__DIR__ . '/../../config/db_connection.php');
include(__DIR__ . '/../admin_pagination.php');

// Construct the facility type query based on search
if (!empty($searchFacilityTypeQuery)) {
    $facilityTypeSelect = "SELECT * FROM facilitytypetb WHERE FacilityType LIKE '%$searchFacilityTypeQuery%' LIMIT $rowsPerPage OFFSET $facilityTypeOffset";
} else {
    $facilityTypeSelect = "SELECT * FROM facilitytypetb LIMIT $rowsPerPage OFFSET $facilityTypeOffset";
}

$facilityTypeSelectQuery = $connect->query($facilityTypeSelect);
$facilityTypes = [];

if (mysqli_num_rows($facilityTypeSelectQuery) > 0) {
    while ($row = $facilityTypeSelectQuery->fetch_assoc()) {
        $facilityTypes[] = $row;
    }
}
?>

<table class="min-w-full bg-white rounded-lg">
    <thead>
        <tr class="bg-gray-100 text-gray-600 text-sm">
            <th class="p-3 text-start">
                <input type="checkbox" id="selectAllCheckbox"
                    class="form-checkbox h-3 w-3 border-2 text-amber-500">
                ID
            </th>
            <th class="p-3 text-start">Type</th>
            <th class="p-3 text-start hidden sm:table-cell">Icon</th>
            <th class="p-3 text-start">Actions</th>
        </tr>
    </thead>
    <tbody class="text-gray-600 text-sm">
        <?php if (!empty($facilityTypes)): ?>
            <?php foreach ($facilityTypes as $facilityType): ?>
                <tr class="border-b border-gray-200 hover:bg-gray-50">
                    <td class="p-3 text-start whitespace-nowrap">
                        <div class="flex items-center gap-2 font-medium text-gray-500">
                            <input type="checkbox"
                                class="rowCheckbox form-checkbox h-3 w-3 border-2 text-amber-500"
                                value="<?= htmlspecialchars($facilityType['FacilityTypeID']) ?>">
                            <?= htmlspecialchars($facilityType['FacilityTypeID']) ?>
                        </div>
                    </td>
                    <td class="p-3 text-start"><?= htmlspecialchars($facilityType['FacilityType']) ?></td>
                    <td class="p-3 text-start hidden sm:table-cell">
                        <i class="<?= htmlspecialchars($facilityType['FacilityTypeIcon'], ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($facilityType['IconSize'], ENT_QUOTES, 'UTF-8') ?>"></i>
                    </td>
                    <td class="p-3 text-start space-x-1 select-none">
                        <i class="details-btn ri-eye-line text-lg cursor-pointer"
                            data-facilitytype-id="<?= htmlspecialchars($facilityType['FacilityTypeID']) ?>"></i>
                        <button class="text-red-500">
                            <i class="delete-btn ri-delete-bin-7-line text-xl"
                                data-facilitytype-id="<?= htmlspecialchars($facilityType['FacilityTypeID']) ?>"></i>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" class="p-3 text-center text-gray-500 py-52">
                    No facility types available.
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<script>
    // Function to load a specific page
    function loadFacilityTypePage(page) {
        const urlParams = new URLSearchParams(window.location.search);
        const searchQuery = urlParams.get('facilitytype_search') || '';

        // Update URL parameters
        urlParams.set('facilitytypepage', page);
        if (searchQuery) urlParams.set('facilitytype_search', searchQuery);

        const xhr = new XMLHttpRequest();
        xhr.open('GET', `../includes/admin_table_components/facilitytype_results.php?${urlParams.toString()}`, true);

        xhr.onload = function() {
            if (this.status === 200) {
                document.getElementById('facilityTypeResults').innerHTML = this.responseText;

                // Also update the pagination controls
                const xhrPagination = new XMLHttpRequest();
                xhrPagination.open('GET', `../includes/admin_table_components/facilitytype_pagination.php?${urlParams.toString()}`, true);
                xhrPagination.onload = function() {
                    if (this.status === 200) {
                        document.getElementById('paginationContainer').innerHTML = this.responseText;
                    }
                };
                xhrPagination.send();

                window.history.pushState({}, '', `?${urlParams.toString()}`);
                window.scrollTo(0, 0);
                initializeFacilityTypeActionButtons();
            }
        };

        xhr.send();
    }

    // Function to handle search
    function handleSearch() {
        const searchInput = document.querySelector('input[name="facilitytype_search"]');

        // Reset to page 1 when searching
        loadFacilityTypePage(1);
    }

    // Initialize event listeners for search
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.querySelector('input[name="facilitytype_search"]');

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const urlParams = new URLSearchParams(window.location.search);
                urlParams.set('facilitytype_search', this.value);
                window.history.pushState({}, '', `?${urlParams.toString()}`);
                handleSearch();
            });
        }

        initializeFacilityTypeActionButtons();
    });

    // Function to initialize action buttons for facility types
    function initializeFacilityTypeActionButtons() {
        // Function to attach event listeners to a row
        const attachEventListenersToRow = (row) => {
            // Details button
            const detailsBtn = row.querySelector('.details-btn');
            if (detailsBtn) {
                detailsBtn.addEventListener('click', function() {
                    const facilityTypeId = this.getAttribute('data-facilitytype-id');
                    darkOverlay2.classList.remove('opacity-0', 'invisible');
                    darkOverlay2.classList.add('opacity-100');

                    fetch(`../Admin/add_facilitytype.php?action=getFacilityTypeDetails&id=${facilityTypeId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById('updateFacilityTypeID').value = facilityTypeId;
                                document.querySelector('[name="updatefacilitytype"]').value = data.facilitytype.FacilityType;
                                document.querySelector('[name="updatefacilitytypeicon"]').value = data.facilitytype.FacilityTypeIcon;
                                document.querySelector('[name="updatefacilitytypeiconsize"]').value = data.facilitytype.IconSize;
                                updateFacilityTypeModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                            } else {
                                console.error('Failed to load facility type details');
                            }
                        })
                        .catch(error => console.error('Fetch error:', error));
                });
            }

            // Delete button
            const deleteBtn = row.querySelector('.delete-btn');
            if (deleteBtn) {
                deleteBtn.addEventListener('click', function() {
                    const facilityTypeId = this.getAttribute('data-facilitytype-id');
                    darkOverlay2.classList.remove('opacity-0', 'invisible');
                    darkOverlay2.classList.add('opacity-100');

                    fetch(`../Admin/add_facilitytype.php?action=getFacilityTypeDetails&id=${facilityTypeId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById('deleteFacilityTypeID').value = facilityTypeId;
                                document.getElementById('facilityTypeDeleteName').textContent = data.facilitytype.FacilityType;
                                facilityTypeConfirmDeleteModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                            } else {
                                console.error('Failed to load facility type details');
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
        const searchInput = document.querySelector('input[name="facilitytype_search"]');
        if (searchInput) {
            searchInput.value = urlParams.get('facilitytype_search') || '';
        }
        loadFacilityTypePage(urlParams.get('facilitytypepage') || 1);
    });
</script>