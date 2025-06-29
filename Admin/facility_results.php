<?php
include('../config/dbConnection.php');

// Initialize search and filter variables for facility
$searchFacilityQuery = isset($_GET['facility_search']) ? mysqli_real_escape_string($connect, $_GET['facility_search']) : '';
$filterFacilityTypeID = isset($_GET['sort']) ? $_GET['sort'] : 'random';

// Pagination variables
$rowsPerPage = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$facilityOffset = ($page - 1) * $rowsPerPage;

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

// Construct the facility count query based on search
if ($filterFacilityTypeID !== 'random' && !empty($searchFacilityQuery)) {
    $facilityQuery = "SELECT COUNT(*) as count FROM facilitytb WHERE FacilityTypeID = '$filterFacilityTypeID' AND (Facility LIKE '%$searchFacilityQuery%')";
} elseif ($filterFacilityTypeID !== 'random') {
    $facilityQuery = "SELECT COUNT(*) as count FROM facilitytb WHERE FacilityTypeID = '$filterFacilityTypeID'";
} elseif (!empty($searchFacilityQuery)) {
    $facilityQuery = "SELECT COUNT(*) as count FROM facilitytb WHERE Facility LIKE '%$searchFacilityQuery%'";
} else {
    $facilityQuery = "SELECT COUNT(*) as count FROM facilitytb";
}

// Execute the count query
$facilityResult = $connect->query($facilityQuery);
$facilityCount = $facilityResult->fetch_assoc()['count'];

// Fetch facility count
$facilityCountQuery = "SELECT COUNT(*) as count FROM facilitytb";
$facilityCountResult = $connect->query($facilityCountQuery);
$allFacilityCount = $facilityCountResult->fetch_assoc()['count'];
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