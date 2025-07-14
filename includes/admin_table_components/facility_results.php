<?php
include(__DIR__ . '/../../config/dbConnection.php');
include(__DIR__ . '/../admin_pagination.php');

// Construct the facility query based on search
if ($filterFacilityTypeID !== 'random' && !empty($searchFacilityQuery)) {
    $facilitySelect = "SELECT * FROM facilitytb WHERE FacilityTypeID = '$filterFacilityTypeID' AND (Facility LIKE '%$searchFacilityQuery%') LIMIT $rowsPerPage OFFSET $facilityOffset";
} elseif ($filterFacilityTypeID !== 'random') {
    $facilitySelect = "SELECT * FROM facilitytb WHERE FacilityTypeID = '$filterFacilityTypeID' LIMIT $rowsPerPage OFFSET $facilityOffset";
} elseif (!empty($searchFacilityQuery)) {
    $facilitySelect = "SELECT * FROM facilitytb WHERE Facility LIKE '%$searchFacilityQuery%' LIMIT $rowsPerPage OFFSET $facilityOffset";
} else {
    $facilitySelect = "SELECT * FROM facilitytb LIMIT $rowsPerPage OFFSET $facilityOffset";
}

$facilitySelectQuery = $connect->query($facilitySelect);
$facilities = [];

if (mysqli_num_rows($facilitySelectQuery) > 0) {
    while ($row = $facilitySelectQuery->fetch_assoc()) {
        $facilities[] = $row;
    }
}
?>

<table class="min-w-full bg-white rounded-lg">
    <thead>
        <tr class="bg-gray-100 text-gray-600 text-sm">
            <th class="p-3 text-start">ID</th>
            <th class="p-3 text-start">Type</th>
            <th class="p-3 text-start">Icon</th>
            <th class="p-3 text-start hidden md:table-cell">Additional Charge</th>
            <th class="p-3 text-start hidden md:table-cell">Popular</th>
            <th class="p-3 text-start hidden lg:table-cell">Facility Type</th>
            <th class="p-3 text-start">Actions</th>
        </tr>
    </thead>
    <tbody class="text-gray-600 text-sm">
        <?php if (!empty($facilities)): ?>
            <?php foreach ($facilities as $facility): ?>
                <tr class="border-b border-gray-200 hover:bg-gray-50">
                    <td class="p-3 text-start whitespace-nowrap">
                        <div class="flex items-center gap-2 font-medium text-gray-500">
                            <input type="checkbox" class="form-checkbox h-3 w-3 border-2 text-amber-500">
                            <span><?= htmlspecialchars($facility['FacilityID']) ?></span>
                        </div>
                    </td>
                    <td class="p-3 text-start">
                        <?= htmlspecialchars($facility['Facility']) ?>
                    </td>
                    <td class="p-3 text-start">
                        <?= !empty($facility['FacilityIcon']) && !empty($facility['IconSize'])
                            ? '<i class="' . htmlspecialchars($facility['FacilityIcon'], ENT_QUOTES, 'UTF-8') . ' ' . htmlspecialchars($facility['IconSize'], ENT_QUOTES, 'UTF-8') . '"></i>'
                            : 'None' ?>
                    </td>
                    <td class="p-3 text-start hidden md:table-cell">
                        <?= htmlspecialchars($facility['AdditionalCharge'] == 1 ? 'True' : 'False') ?>
                    </td>
                    <td class="p-3 text-start hidden md:table-cell">
                        <?= htmlspecialchars($facility['Popular'] == 1 ? 'True' : 'False') ?>
                    </td>
                    <td class="p-3 text-start hidden lg:table-cell">
                        <?php
                        $facilityTypeID = $facility['FacilityTypeID'];
                        $facilityTypeQuery = "SELECT FacilityType FROM facilitytypetb WHERE FacilityTypeID = '$facilityTypeID'";
                        $facilityTypeResult = mysqli_query($connect, $facilityTypeQuery);

                        if ($facilityTypeResult && $facilityTypeResult->num_rows > 0) {
                            $facilityTypeRow = $facilityTypeResult->fetch_assoc();
                            echo htmlspecialchars($facilityTypeRow['FacilityType']);
                        }
                        ?>
                    </td>
                    <td class="p-3 text-start space-x-1 select-none">
                        <i class="details-btn ri-eye-line text-lg cursor-pointer"
                            data-facility-id="<?= htmlspecialchars($facility['FacilityID']) ?>"></i>
                        <button class="text-red-500">
                            <i class="delete-btn ri-delete-bin-7-line text-xl"
                                data-facility-id="<?= htmlspecialchars($facility['FacilityID']) ?>"></i>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" class="p-3 text-center text-gray-500 py-52">
                    No facilities available.
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<script>
    // Function to load a specific page
    function loadPage(page) {
        const urlParams = new URLSearchParams(window.location.search);
        const searchQuery = urlParams.get('facility_search') || '';
        const sortType = urlParams.get('sort') || 'random';

        // Update URL parameters
        urlParams.set('page', page);
        if (searchQuery) urlParams.set('facility_search', searchQuery);
        if (sortType !== 'random') urlParams.set('sort', sortType);

        const xhr = new XMLHttpRequest();
        xhr.open('GET', `../includes/admin_table_components/facility_results.php?${urlParams.toString()}`, true);

        xhr.onload = function() {
            if (this.status === 200) {
                document.getElementById('facilityResults').innerHTML = this.responseText;

                // Also update the pagination controls
                const xhrPagination = new XMLHttpRequest();
                xhrPagination.open('GET', `../includes/admin_table_components/facility_pagination.php?${urlParams.toString()}`, true);
                xhrPagination.onload = function() {
                    if (this.status === 200) {
                        document.getElementById('paginationContainer').innerHTML = this.responseText;
                    }
                };
                xhrPagination.send();

                window.history.pushState({}, '', `?${urlParams.toString()}`);
                window.scrollTo(0, 0);
                initializeActionButtons();
            }
        };

        xhr.send();
    }

    // Function to handle search and filter
    function handleSearchFilter() {
        const searchInput = document.querySelector('input[name="facility_search"]');
        const filterSelect = document.querySelector('select[name="sort"]');

        // Reset to page 1 when searching or filtering
        loadPage(1);
    }

    // Initialize event listeners for search and filter
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.querySelector('input[name="facility_search"]');
        const filterSelect = document.querySelector('select[name="sort"]');

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const urlParams = new URLSearchParams(window.location.search);
                urlParams.set('facility_search', this.value);
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

        initializeActionButtons();
    });

    // Function to initialize action buttons
    function initializeActionButtons() {
        // Details buttons
        document.querySelectorAll('.details-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const facilityId = this.getAttribute('data-facility-id');
                darkOverlay2.classList.remove('opacity-0', 'invisible');
                darkOverlay2.classList.add('opacity-100');

                fetch(`../Admin/AddFacility.php?action=getFacilityDetails&id=${facilityId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('updateFacilityID').value = facilityId;
                            document.querySelector('[name="updatefacility"]').value = data.facility.Facility;
                            document.querySelector('[name="updatefacilityicon"]').value = data.facility.FacilityIcon;
                            document.querySelector('[name="updatefacilityiconsize"]').value = data.facility.IconSize;
                            document.querySelector('[name="updateadditionalcharge"]').value = data.facility.AdditionalCharge;
                            document.querySelector('[name="updatepopular"]').value = data.facility.Popular;
                            document.querySelector('[name="updatefacilitytype"]').value = data.facility.FacilityTypeID;
                            updateFacilityModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                        } else {
                            console.error('Failed to load facility details');
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));
            });
        });

        // Delete buttons
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const facilityId = this.getAttribute('data-facility-id');
                darkOverlay2.classList.remove('opacity-0', 'invisible');
                darkOverlay2.classList.add('opacity-100');

                fetch(`../Admin/AddFacility.php?action=getFacilityDetails&id=${facilityId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('deleteFacilityID').value = facilityId;
                            document.getElementById('facilityDeleteName').textContent = data.facility.Facility;
                            facilityConfirmDeleteModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                        } else {
                            console.error('Failed to load facility details');
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));
            });
        });
    }

    // Handle browser back/forward buttons
    window.addEventListener('popstate', function() {
        const urlParams = new URLSearchParams(window.location.search);
        facilityTypeFilter.value = urlParams.get('sort') || 'random';
        facilitySearch.value = urlParams.get('facility_search') || '';
        loadPage();
    });
</script>