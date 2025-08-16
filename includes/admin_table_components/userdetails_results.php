<?php
require_once(__DIR__ . '/../../config/db_connection.php');
include(__DIR__ . '/../admin_pagination.php');

// Construct the user query based on search
if ($filterMembershipID !== 'random' && !empty($searchUserQuery)) {
    $userSelect = "SELECT * FROM usertb WHERE Membership = '$filterMembershipID' AND (UserName LIKE '%$searchUserQuery%' OR UserEmail LIKE '%$searchUserQuery%') LIMIT $rowsPerPage OFFSET $userOffset";
} elseif ($filterMembershipID !== 'random') {
    $userSelect = "SELECT * FROM usertb WHERE Membership = '$filterMembershipID' LIMIT $rowsPerPage OFFSET $userOffset";
} elseif (!empty($searchUserQuery)) {
    $userSelect = "SELECT * FROM usertb WHERE UserName LIKE '%$searchUserQuery%' OR UserEmail LIKE '%$searchUserQuery%' LIMIT $rowsPerPage OFFSET $userOffset";
} else {
    $userSelect = "SELECT * FROM usertb LIMIT $rowsPerPage OFFSET $userOffset";
}

$userSelectQuery = $connect->query($userSelect);
$users = [];

if (mysqli_num_rows($userSelectQuery) > 0) {
    while ($row = $userSelectQuery->fetch_assoc()) {
        $users[] = $row;
    }
}
?>

<table class="min-w-full bg-white rounded-lg">
    <thead>
        <tr class="bg-gray-100 text-gray-600 text-sm">
            <th class="p-3 text-start">User</th>
            <th class="p-3 text-start hidden md:table-cell">Phone</th>
            <th class="p-3 text-start hidden lg:table-cell">Membership</th>
            <th class="p-3 text-start text-nowrap hidden xl:table-cell">Last Check Out</th>
            <th class="p-3 text-start text-nowrap hidden xl:table-cell">Last Sign In</th>
            <th class="p-3 text-start">Actions</th>
        </tr>
    </thead>
    <tbody class="text-gray-600 text-sm">
        <?php if (!empty($users)): ?>
            <?php foreach ($users as $user):
                // Extract initials from the UserName
                $nameParts = explode(' ', trim($user['UserName'])); // Split the name by spaces
                $initials = substr($nameParts[0], 0, 1); // First letter of the first name
                if (count($nameParts) > 1) {
                    $initials .= substr(end($nameParts), 0, 1); // First letter of the last name
                }

                $bgColor = $user['ProfileBgColor'];
            ?>
                <tr class="border-b border-gray-200 hover:bg-gray-50">
                    <td class="p-3 text-start flex items-center gap-2 group">
                        <p
                            class="w-10 h-10 rounded-full bg-[<?= $bgColor ?>] text-white uppercase font-semibold flex items-center justify-center select-none">
                            <?= $initials ?>
                        </p>
                        <div>
                            <p class="font-bold"><?= htmlspecialchars($user['UserName']) ?></p>
                            <p><?= htmlspecialchars($user['UserEmail']) ?></p>
                        </div>

                        <a class="opacity-0 group-hover:opacity-100 transition-all duration-200" href="mailto:<?= htmlspecialchars($user['UserEmail']) ?>"><i class="ri-mail-fill text-lg"></i></a>
                    </td>
                    <td class="p-3 text-start hidden md:table-cell">
                        <?= htmlspecialchars($user['UserPhone']) ? htmlspecialchars($user['UserPhone']) : 'N/A' ?>
                    </td>
                    <td class="p-3 text-start space-x-1 select-none hidden lg:table-cell">
                        <?php if ($user['Membership'] == '1'): ?>
                            <span><i class="ri-vip-crown-line text-yellow-500"></i> VIP Email</span>
                        <?php else: ?>
                            <span><i class="ri-mail-line text-gray-500"></i> Standard Email</span>
                        <?php endif; ?>
                    </td>
                    <td class="p-3 text-start hidden md:table-cell">
                        <?= htmlspecialchars(date('d M Y', strtotime($user['LastSignIn']))) ?>
                    </td>
                    <td class="p-3 text-start hidden md:table-cell">
                        <?= htmlspecialchars(date('d M Y', strtotime($user['LastSignIn']))) ?>
                    </td>
                    <td class="p-3 text-start space-x-1 select-none">
                        <i class="details-btn ri-eye-line text-lg cursor-pointer"
                            data-user-id="<?= htmlspecialchars($user['UserID']) ?>"></i>
                        <button class=" text-red-500">
                            <i class="delete-btn ri-delete-bin-7-line text-xl"
                                data-user-id="<?= htmlspecialchars($user['UserID']) ?>"></i>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" class="p-3 text-center text-gray-500 py-52">
                    No users available.
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<script>
    // Function to load a specific page of users with pagination update
    function loadUserPage(page, searchQuery = '', filterMembershipID = '') {
        const urlParams = new URLSearchParams(window.location.search);
        const currentSearch = urlParams.get('user_search') || '';
        const currentSort = urlParams.get('sort') || '';
        const currentFilter = urlParams.get('filter') || '';

        // Update URL parameters
        urlParams.set('userpage', page);
        if (searchQuery) urlParams.set('user_search', searchQuery);
        if (filterMembershipID) urlParams.set('filter', filterMembershipID);
        if (currentSort) urlParams.set('sort', currentSort);

        // Fetch user results
        fetch(`../includes/admin_table_components/userdetails_results.php?${urlParams.toString()}`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('userResults').innerHTML = html;

                // Always fetch and update pagination controls
                return fetch(`../includes/admin_table_components/userdetails_pagination.php?${urlParams.toString()}`);
            })
            .then(response => response.text())
            .then(html => {
                document.getElementById('paginationContainer').innerHTML = html;
                window.history.pushState({}, '', `?${urlParams.toString()}`);
                window.scrollTo(0, 0);
                initializeUserActionButtons();
            })
            .catch(error => console.error('Error:', error));
    }

    // Function to handle user search and update pagination
    function handleUserSearch() {
        const urlParams = new URLSearchParams(window.location.search);
        const searchInput = document.querySelector('input[name="user_search"]');
        const sortSelect = document.querySelector('select[name="sort"]');
        const filterSelect = document.querySelector('select[name="filter"]');

        if (searchInput) urlParams.set('user_search', searchInput.value);
        if (sortSelect) urlParams.set('sort', sortSelect.value);
        if (filterSelect) urlParams.set('filter', filterSelect.value);

        urlParams.set('userpage', 1);

        // Fetch updated user results
        fetch(`../includes/admin_table_components/userdetails_results.php?${urlParams.toString()}`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('userResults').innerHTML = html;
                return fetch(`../includes/admin_table_components/userdetails_pagination.php?${urlParams.toString()}`);
            })
            .then(response => response.text())
            .then(html => {
                document.getElementById('paginationContainer').innerHTML = html;
                window.history.pushState({}, '', `?${urlParams.toString()}`);
                window.scrollTo(0, 0);
                initializeUserActionButtons();
            })
            .catch(error => console.error('Error:', error));
    }

    // Initialize event listeners for search and sort
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.querySelector('input[name="user_search"]');
        const sortSelect = document.querySelector('select[name="sort"]');
        const filterSelect = document.querySelector('select[name="filter"]');

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                handleUserSearch();
            });
        }

        if (sortSelect) {
            sortSelect.addEventListener('change', function() {
                handleUserSearch();
            });
        }

        if (filterSelect) {
            filterSelect.addEventListener('change', function() {
                handleUserSearch();
            });
        }

        initializeUserActionButtons();
    });

    // Reusable action button setup
    function initializeUserActionButtons() {
        const userConfirmDeleteModal = document.getElementById('userConfirmDeleteModal');
        const userCancelDeleteBtn = document.getElementById('userCancelDeleteBtn');
        const deleteUserBtns = document.querySelectorAll('.user-delete-btn');
        const deleteUserConfirmInput = document.getElementById('deleteUserConfirmInput');
        const confirmUserDeleteBtn = document.getElementById('confirmUserDeleteBtn');

        if (deleteUserBtns) {
            deleteUserBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const userId = this.getAttribute('data-user-id');

                    fetch(`../Admin/UserManagement.php?action=getUserDetails&id=${userId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById('deleteUserID').value = userId;
                                document.getElementById('userDeleteName').textContent = data.user.FullName;
                                document.getElementById('userDeleteEmail').textContent = data.user.Email;

                                if (data.user.ProfileImage) {
                                    document.getElementById('userDeleteProfileImg').src = data.user.ProfileImage;
                                    document.getElementById('userDeleteProfileImg').style.display = 'block';
                                    document.getElementById('userDeleteProfileText').style.display = 'none';
                                } else {
                                    document.getElementById('userDeleteProfileText').textContent = data.user.FullName.charAt(0).toUpperCase();
                                    document.getElementById('userDeleteProfileText').style.backgroundColor = data.user.ProfileBgColor;
                                    document.getElementById('userDeleteProfileText').style.display = 'flex';
                                    document.getElementById('userDeleteProfileImg').style.display = 'none';
                                }
                            }
                        })
                        .catch(error => console.error('Fetch error:', error));

                    darkOverlay2.classList.remove('opacity-0', 'invisible');
                    darkOverlay2.classList.add('opacity-100');
                    userConfirmDeleteModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                });
            });
        }

        if (userCancelDeleteBtn) {
            userCancelDeleteBtn.addEventListener('click', () => {
                userConfirmDeleteModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
                darkOverlay2.classList.add('opacity-0', 'invisible');
                darkOverlay2.classList.remove('opacity-100');
                deleteUserConfirmInput.value = '';
                confirmUserDeleteBtn.disabled = true;
                confirmUserDeleteBtn.classList.add("cursor-not-allowed");
            });
        }

        if (deleteUserConfirmInput && confirmUserDeleteBtn) {
            deleteUserConfirmInput.addEventListener("input", () => {
                const isMatch = deleteUserConfirmInput.value.trim() === "DELETE";
                confirmUserDeleteBtn.disabled = !isMatch;
                confirmUserDeleteBtn.classList.toggle("cursor-not-allowed", !isMatch);
            });
        }
    }

    // Handle browser back/forward button for consistent state
    window.addEventListener('popstate', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const searchInput = document.querySelector('input[name="user_search"]');
        const sortSelect = document.querySelector('select[name="sort"]');

        if (searchInput) {
            searchInput.value = urlParams.get('user_search') || '';
        }
        if (sortSelect) {
            sortSelect.value = urlParams.get('sort') || '';
        }
        loadUserPage(urlParams.get('userpage') || 1);
    });
</script>