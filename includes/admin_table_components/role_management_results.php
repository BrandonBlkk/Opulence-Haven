<?php
require_once(__DIR__ . '/../../config/db_connection.php');
include(__DIR__ . '/../admin_pagination.php');

// Construct the admin query based on search and role filter with LIMIT and OFFSET
if ($filterRoleID !== 'random' && !empty($searchAdminQuery)) {
    $adminSelect = "SELECT * FROM admintb WHERE RoleID = '$filterRoleID' AND (FirstName LIKE '%$searchAdminQuery%' OR LastName LIKE '%$searchAdminQuery%' OR UserName LIKE '%$searchAdminQuery%' OR AdminEmail LIKE '%$searchAdminQuery%') LIMIT $rowsPerPage OFFSET $offset";
} elseif ($filterRoleID !== 'random') {
    $adminSelect = "SELECT * FROM admintb WHERE RoleID = '$filterRoleID' LIMIT $rowsPerPage OFFSET $offset";
} elseif (!empty($searchAdminQuery)) {
    $adminSelect = "SELECT * FROM admintb WHERE FirstName LIKE '%$searchAdminQuery%' OR LastName LIKE '%$searchAdminQuery%' OR UserName LIKE '%$searchAdminQuery%' OR AdminEmail LIKE '%$searchAdminQuery%' LIMIT $rowsPerPage OFFSET $offset";
} else {
    $adminSelect = "SELECT * FROM admintb LIMIT $rowsPerPage OFFSET $offset";
}

// Execute the query to fetch admins
$adminSelectQuery = $connect->query($adminSelect);
$admins = [];

if (mysqli_num_rows($adminSelectQuery) > 0) {
    while ($row = $adminSelectQuery->fetch_assoc()) {
        $admins[] = $row;
    }
}
?>

<table class="min-w-full bg-white rounded-lg">
    <thead>
        <tr class="bg-gray-100 text-gray-600 text-sm">
            <th class="p-3 text-start">ID</th>
            <th class="p-3 text-start">Admin</th>
            <th class="p-3 text-start hidden md:table-cell">Role</th>
            <th class="p-3 text-start hidden lg:table-cell">Status</th>
            <th class="p-3 text-start text-nowrap hidden xl:table-cell">Reset Password</th>
            <th class="p-3 text-start">Actions</th>
        </tr>
    </thead>
    <tbody class="text-gray-600 text-sm">
        <?php if (!empty($admins)): ?>
            <?php foreach ($admins as $admin): ?>
                <tr class="border-b border-gray-200 hover:bg-gray-50">
                    <td class="p-3 text-start whitespace-nowrap">
                        <div class="flex items-center gap-2 font-medium text-gray-500">
                            <input type="checkbox" class="form-checkbox h-3 w-3 border-2 text-amber-500">
                            <span><?= htmlspecialchars($admin['AdminID']) ?></span>
                        </div>
                    </td>
                    <td class="p-3 text-start flex items-center gap-2">
                        <?php
                        if ($admin['AdminProfile'] === null) {
                        ?>
                            <div id="profilePreview" class="w-10 h-10 object-cover rounded-full bg-[<?php echo $admin['ProfileBgColor'] ?>] text-white select-none">
                                <p class="w-full h-full flex items-center justify-center font-semibold"><?php echo strtoupper(substr($admin['UserName'], 0, 1)); ?></p>
                            </div>
                        <?php
                        } else {
                        ?>
                            <div class="w-10 h-10 rounded-full select-none">
                                <img class="w-full h-full object-cover rounded-full"
                                    src="<?= htmlspecialchars($admin['AdminProfile']) ?>"
                                    alt="Profile">
                            </div>
                        <?php
                        }
                        ?>
                        <div>
                            <p class="font-bold"><?= htmlspecialchars($admin['FirstName'] . ' ' . $admin['LastName']) ?>
                                <!-- <?php
                                        // Check if the admin ID matches the logged-in admin's ID
                                        if ($adminID == $admin['AdminID']) {
                                            echo "<span class='text-sm text-green-500 font-semibold'> (You)</span>";
                                        }
                                        ?> -->
                            </p>
                            <p><?= htmlspecialchars($admin['AdminEmail']) ?></p>
                        </div>
                    </td>
                    <td class="p-3 text-start hidden md:table-cell">
                        <select name="updateRole" class="border rounded p-2 bg-gray-50">
                            <?php
                            // Fetch roles for the dropdown
                            $rolesQuery = "SELECT * FROM roletb";
                            $rolesResult = $connect->query($rolesQuery);

                            if ($rolesResult->num_rows > 0) {
                                while ($roleRow = $rolesResult->fetch_assoc()) {
                                    $selected = $roleRow['RoleID'] == $admin['RoleID'] ? 'selected' : '';
                                    echo "<option value='{$roleRow['RoleID']}' $selected>{$roleRow['Role']}</option>";
                                }
                            } else {
                                echo "<option value='' disabled>No roles available</option>";
                            }
                            ?>
                        </select>
                    </td>
                    <td class="p-3 text-start space-x-1 select-none hidden lg:table-cell">
                        <span class="text-xs px-2 py-1 rounded-full select-none border <?= $admin['Status'] === 'active' ? 'bg-green-100 border-green-200' : 'bg-red-100 border-red-200' ?>">
                            <?= htmlspecialchars($admin['Status']) ? 'Active' : 'Inactive' ?>
                        </span>
                    </td>
                    <td class="reset-btn p-3 text-start space-x-1 hidden xl:table-cell">
                        <button class="reset-btn" data-admin-id="<?= htmlspecialchars($admin['AdminID']) ?>">
                            <span class="rounded-md border-2"><i class="ri-arrow-left-line text-md"></i></span>
                            <span>Reset</span>
                        </button>
                    </td>
                    <td class="p-3 text-start space-x-1 select-none">
                        <button class=" text-amber-500">
                            <i class="ri-edit-line text-xl"></i>
                        </button>
                        <button class=" text-red-500">
                            <i class="delete-btn ri-delete-bin-7-line text-xl"
                                data-admin-id="<?= htmlspecialchars($admin['AdminID']) ?>"></i>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" class="p-3 text-center text-gray-500 py-52">
                    No admins available.
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<script>
    // Function to load a specific page
    function loadPage(page) {
        const urlParams = new URLSearchParams(window.location.search);
        const searchQuery = urlParams.get('acc_search') || '';
        const sortType = urlParams.get('sort') || 'random';

        // Update URL parameters
        urlParams.set('page', page);
        if (searchQuery) urlParams.set('acc_search', searchQuery);
        if (sortType !== 'random') urlParams.set('sort', sortType);

        const xhr = new XMLHttpRequest();
        xhr.open('GET', `../includes/admin_table_components/role_management_results.php?${urlParams.toString()}`, true);

        xhr.onload = function() {
            if (this.status === 200) {
                document.getElementById('adminResults').innerHTML = this.responseText;

                // Also update the pagination controls
                const xhrPagination = new XMLHttpRequest();
                xhrPagination.open('GET', `../includes/admin_table_components/role_management_pagination.php?${urlParams.toString()}`, true);
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
        // Reset to page 1 when searching or filtering
        loadPage(1);
    }

    // Initialize event listeners for search and filter
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.querySelector('input[name="acc_search"]');
        const filterSelect = document.querySelector('select[name="sort"]');

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const urlParams = new URLSearchParams(window.location.search);
                urlParams.set('acc_search', this.value);
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
        // Delete buttons functionality
        const adminConfirmDeleteModal = document.getElementById('adminConfirmDeleteModal');
        const adminCancelDeleteBtn = document.getElementById('adminCancelDeleteBtn');
        const deleteBtns = document.querySelectorAll('.delete-btn');
        const deleteAdminConfirmInput = document.getElementById('deleteAdminConfirmInput');
        const confirmAdminDeleteBtn = document.getElementById('confirmAdminDeleteBtn');
        const darkOverlay2 = document.getElementById('darkOverlay2');

        // Reset password buttons functionality
        const resetAdminPasswordModal = document.getElementById('resetAdminPasswordModal');
        const adminResetPasswordCancelBtn = document.getElementById('adminResetPasswordCancelBtn');
        const resetBtns = document.querySelectorAll('.reset-btn button');

        if (adminConfirmDeleteModal && adminCancelDeleteBtn && deleteBtns) {
            // Add click event to each delete button
            deleteBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const adminId = this.getAttribute('data-admin-id');

                    // Fetch admin details
                    fetch(`../Admin/RoleManagement.php?action=getAdminDetails&id=${adminId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById('deleteAdminID').value = adminId;
                                document.getElementById('adminDeleteEmail').textContent = data.admin.AdminEmail;
                                document.getElementById('adminDeleteUsername').textContent = data.admin.UserName;

                                // Handle both cases (with profile image and without)
                                if (data.admin.AdminProfile) {
                                    // If admin has profile image
                                    document.getElementById('adminDeleteProfile').src = data.admin.AdminProfile;
                                    // Show image container and hide text container
                                    document.getElementById('adminDeleteProfile').parentElement.parentElement.style.display = 'block';
                                    document.getElementById('adminDeleteProfileText').parentElement.style.display = 'none';
                                } else {
                                    // If admin doesn't have profile image
                                    document.getElementById('adminDeleteProfileText').textContent = data.admin.UserName.charAt(0).toUpperCase();
                                    // Show text container and hide image container
                                    document.getElementById('adminDeleteProfileText').parentElement.style.backgroundColor = data.admin.ProfileBgColor;
                                    document.getElementById('adminDeleteProfileText').parentElement.style.display = 'block';
                                    document.getElementById('adminDeleteProfile').parentElement.parentElement.style.display = 'none';
                                }
                            } else {
                                console.error('Failed to load admin details');
                            }
                        })
                        .catch(error => console.error('Fetch error:', error));

                    // Show modal
                    darkOverlay2.classList.remove('opacity-0', 'invisible');
                    darkOverlay2.classList.add('opacity-100');
                    adminConfirmDeleteModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                });
            });

            // Cancel button functionality
            adminCancelDeleteBtn.addEventListener('click', () => {
                adminConfirmDeleteModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
                darkOverlay2.classList.add('opacity-0', 'invisible');
                darkOverlay2.classList.remove('opacity-100');
                deleteAdminConfirmInput.value = ''; // Clear the confirmation input
                confirmAdminDeleteBtn.disabled = true; // Disable delete button
                confirmAdminDeleteBtn.classList.add("cursor-not-allowed");
            });

            // Enable Delete Button only if input matches "DELETE"
            deleteAdminConfirmInput.addEventListener("input", () => {
                const isMatch = deleteAdminConfirmInput.value.trim() === "DELETE";
                confirmAdminDeleteBtn.disabled = !isMatch;

                // Toggle the 'cursor-not-allowed' class
                if (isMatch) {
                    confirmAdminDeleteBtn.classList.remove("cursor-not-allowed");
                } else {
                    confirmAdminDeleteBtn.classList.add("cursor-not-allowed");
                }
            });
        }

        // Reset password button functionality
        if (resetAdminPasswordModal && adminResetPasswordCancelBtn && resetBtns) {
            // Add click event to each reset button
            resetBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const adminId = this.getAttribute('data-admin-id');

                    // Fetch admin details
                    fetch(`../Admin/RoleManagement.php?action=getAdminDetails&id=${adminId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById('resetAdminID').value = adminId;
                                document.getElementById('adminResetEmail').textContent = data.admin.AdminEmail;
                            } else {
                                console.error('Failed to load admin details');
                            }
                        })
                        .catch(error => console.error('Fetch error:', error));

                    // Show modal
                    darkOverlay2.classList.remove('opacity-0', 'invisible');
                    darkOverlay2.classList.add('opacity-100');
                    resetAdminPasswordModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                });
            });

            // Cancel button functionality
            adminResetPasswordCancelBtn.addEventListener('click', () => {
                resetAdminPasswordModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
                darkOverlay2.classList.add('opacity-0', 'invisible');
                darkOverlay2.classList.remove('opacity-100');
            });
        }

        // Check for success message on page load
        const urlParams = new URLSearchParams(window.location.search);
        const deleteAdminSuccess = urlParams.get('delete_success');
        const alertMessage = urlParams.get('message');

        if (deleteAdminSuccess) {
            // Show Alert
            showAlert('The admin account has been successfully deleted.');
            setTimeout(() => {
                window.location.href = 'RoleManagement.php';
            }, 5000);
        } else if (alertMessage) {
            // Show Alert
            showAlert(alertMessage);
        }
    }

    // Handle browser back/forward buttons
    window.addEventListener('popstate', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const filterSelect = document.querySelector('select[name="sort"]');
        const searchInput = document.querySelector('input[name="acc_search"]');

        if (filterSelect) filterSelect.value = urlParams.get('sort') || 'random';
        if (searchInput) searchInput.value = urlParams.get('acc_search') || '';
        loadPage(urlParams.get('page') || 1);
    });
</script>