<?php
require_once(__DIR__ . '/../../config/db_connection.php');
include(__DIR__ . '/../admin_pagination.php');

// Construct the contact query based on search and status filter
if ($filterStatus !== 'random' && !empty($searchContactQuery)) {
    $contactSelect = "SELECT c.*, u.ProfileBgColor, u.UserName
                      FROM contacttb c 
                      LEFT JOIN usertb u ON c.UserID = u.UserID 
                      WHERE c.Status = '$filterStatus' 
                      AND (c.FullName LIKE '%$searchContactQuery%' 
                           OR c.UserEmail LIKE '%$searchContactQuery%' 
                           OR c.Country LIKE '%$searchContactQuery%') 
                      $dateCondition 
                      ORDER BY c.ContactID DESC 
                      LIMIT $rowsPerPage OFFSET $contactOffset";
} elseif ($filterStatus !== 'random') {
    $contactSelect = "SELECT c.*, u.ProfileBgColor, u.UserName
                      FROM contacttb c 
                      LEFT JOIN usertb u ON c.UserID = u.UserID 
                      WHERE c.Status = '$filterStatus' 
                      $dateCondition 
                      ORDER BY c.ContactID DESC 
                      LIMIT $rowsPerPage OFFSET $contactOffset";
} elseif (!empty($searchContactQuery)) {
    $contactSelect = "SELECT c.*, u.ProfileBgColor, u.UserName
                      FROM contacttb c 
                      LEFT JOIN usertb u ON c.UserID = u.UserID 
                      WHERE (c.FullName LIKE '%$searchContactQuery%' 
                             OR c.UserEmail LIKE '%$searchContactQuery%' 
                             OR c.Country LIKE '%$searchContactQuery%') 
                      $dateCondition 
                      ORDER BY c.ContactID DESC 
                      LIMIT $rowsPerPage OFFSET $contactOffset";
} else {
    $contactSelect = "SELECT c.*, u.ProfileBgColor, u.UserName
                      FROM contacttb c 
                      LEFT JOIN usertb u ON c.UserID = u.UserID 
                      WHERE 1 
                      $dateCondition 
                      ORDER BY c.ContactID DESC 
                      LIMIT $rowsPerPage OFFSET $contactOffset";
}

$contactSelectQuery = $connect->query($contactSelect);
$contacts = [];

if (mysqli_num_rows($contactSelectQuery) > 0) {
    while ($row = $contactSelectQuery->fetch_assoc()) {
        $contacts[] = $row;
    }
}
?>

<table class="min-w-full bg-white rounded-lg">
    <thead>
        <tr class="bg-gray-100 text-gray-600 text-sm">
            <th class="p-3 text-start">No</th>
            <th class="p-3 text-start hidden sm:table-cell">UserID</th>
            <th class="p-3 text-start">User</th>
            <th class="p-3 text-start hidden xl:table-cell">Phone</th>
            <th class="p-3 text-start hidden xl:table-cell">Country</th>
            <th class="p-3 text-start hidden lg:table-cell">Message</th>
            <th class="p-3 text-start hidden lg:table-cell">Status</th>
            <th class="p-3 text-start hidden xl:table-cell">Date</th>
            <th class="p-3 text-start">Action</th>
        </tr>
    </thead>
    <tbody class="text-gray-600 text-sm">
        <?php if (!empty($contacts)): ?>
            <?php $count = 1; ?>
            <?php foreach ($contacts as $contact): ?>
                <tr class="border-b border-gray-200 hover:bg-gray-50">
                    <td class="p-3 text-start whitespace-nowrap">
                        <div class="flex items-center gap-2 font-medium text-gray-500">
                            <span><?= htmlspecialchars($count) ?></span>
                        </div>
                    </td>
                    <td class="p-3 text-start whitespace-nowrap hidden sm:table-cell">
                        <span><?= htmlspecialchars($contact['UserID']) ?></span>
                    </td>
                    <td class="p-3 text-start flex items-center gap-2">
                        <div id="profilePreview" class="w-10 h-10 object-cover rounded-full bg-[<?php echo $contact['ProfileBgColor'] ?>] text-white select-none">
                            <p class="w-full h-full flex items-center justify-center font-semibold"><?php echo strtoupper(substr($contact['UserName'], 0, 1)); ?></p>
                        </div>
                        <div>
                            <p class="font-bold"><?= htmlspecialchars($contact['FullName']) ?></p>
                            <p class="text-xs text-gray-500 truncate"><?= htmlspecialchars($contact['UserEmail']) ?></p>
                        </div>
                    </td>
                    <td class="p-3 text-start hidden xl:table-cell">
                        <p><?= htmlspecialchars($contact['UserPhone']) ?></p>
                    </td>
                    <td class="p-3 text-start space-x-1 select-none hidden xl:table-cell">
                        <p><?= htmlspecialchars($contact['Country']) ?></p>
                    </td>
                    <td class="p-3 text-start space-x-1 hidden lg:table-cell">
                        <p><?= htmlspecialchars(mb_strimwidth($contact['ContactMessage'], 0, 50, '...')) ?></p>
                    </td>
                    <td class="p-3 text-start space-x-1 hidden lg:table-cell select-none">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full border <?= $contact['Status'] === 'responded' ? 'bg-green-100 border-green-200 text-green-800' : 'bg-red-100 border-red-200 text-red-800' ?>">
                            <?= htmlspecialchars($contact['Status']) === 'responded' ? 'Responded' : 'Pending' ?>
                        </span>
                    </td>
                    <td class="p-3 text-start space-x-1 hidden xl:table-cell">
                        <p><?= htmlspecialchars(date('d M Y', strtotime($contact['ContactDate']))) ?></p>
                    </td>
                    <td class="p-3 text-start space-x-1 select-none">
                        <i class="details-btn ri-eye-line text-lg cursor-pointer"
                            data-contact-id="<?= htmlspecialchars($contact['ContactID']) ?>">
                        </i>
                    </td>
                </tr>
                <?php $count++; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="9" class="p-3 text-center text-gray-500 py-52">
                    No contacts available.
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<script>
    // Function to load a specific contact page
    function loadContactPage(page) {
        const urlParams = new URLSearchParams(window.location.search);
        const searchQuery = urlParams.get('contact_search') || '';
        const sortType = urlParams.get('sort') || 'random';

        // Update URL parameters
        urlParams.set('contactpage', page);
        if (searchQuery) urlParams.set('contact_search', searchQuery);
        if (sortType !== 'random') urlParams.set('sort', sortType);

        const xhr = new XMLHttpRequest();
        xhr.open('GET', `../includes/admin_table_components/contact_results.php?${urlParams.toString()}`, true);

        xhr.onload = function() {
            if (this.status === 200) {
                document.getElementById('contactResults').innerHTML = this.responseText;

                // Also update the pagination controls
                const xhrPagination = new XMLHttpRequest();
                xhrPagination.open('GET', `../includes/admin_table_components/contact_pagination.php?${urlParams.toString()}`, true);
                xhrPagination.onload = function() {
                    if (this.status === 200) {
                        document.getElementById('paginationContainer').innerHTML = this.responseText;
                    }
                };
                xhrPagination.send();

                window.history.pushState({}, '', `?${urlParams.toString()}`);
                window.scrollTo(0, 0);
                initializeContactActionButtons();
            }
        };

        xhr.send();
    }

    // Function to handle contact search and filter
    function handleContactSearchFilter() {
        const searchInput = document.querySelector('input[name="contact_search"]');
        const filterSelect = document.querySelector('select[name="sort"]');

        // Reset to page 1 when searching or filtering
        loadContactPage(1);
    }

    // Initialize event listeners for contact search and filter
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.querySelector('input[name="contact_search"]');
        const filterSelect = document.querySelector('select[name="sort"]');

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const urlParams = new URLSearchParams(window.location.search);
                urlParams.set('contact_search', this.value);
                window.history.pushState({}, '', `?${urlParams.toString()}`);
                handleContactSearchFilter();
            });
        }

        if (filterSelect) {
            filterSelect.addEventListener('change', function() {
                const urlParams = new URLSearchParams(window.location.search);
                urlParams.set('sort', this.value);
                window.history.pushState({}, '', `?${urlParams.toString()}`);
                handleContactSearchFilter();
            });
        }

        initializeContactActionButtons();
    });

    // Function to initialize contact action buttons
    function initializeContactActionButtons() {
        const confirmContactModal = document.getElementById('confirmContactModal');
        const confirmContactModalCancelBtn = document.getElementById('confirmContactModalCancelBtn');
        const detailsBtns = document.querySelectorAll('.details-btn');

        if (confirmContactModal && confirmContactModalCancelBtn && detailsBtns) {
            // Add click event to each button
            detailsBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const contactId = this.getAttribute('data-contact-id');
                    const darkOverlay2 = document.getElementById('darkOverlay2');

                    darkOverlay2.classList.remove('opacity-0', 'invisible');
                    darkOverlay2.classList.add('opacity-100');

                    // Fetch contact details
                    fetch(`../Admin/user_contact.php?action=getContactDetails&id=${contactId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Fill the modal form with contact data
                                document.getElementById('confirmContactID').value = contactId;
                                document.getElementById('contactDate').textContent = data.contact.ContactDate;
                                document.getElementById('contactMessage').textContent = data.contact.ContactMessage;
                                document.getElementById('username').value = data.contact.FullName;
                                document.getElementById('useremail').value = data.contact.UserEmail;
                                document.getElementById('contactMessageInput').value = data.contact.ContactMessage;

                                // Display the values in the div elements
                                document.getElementById('displayUsername').textContent = data.contact.FullName;
                                document.getElementById('displayUseremail').textContent = data.contact.UserEmail;
                                document.getElementById('userphone').textContent = data.contact.UserPhone;
                                document.getElementById('usercountry').textContent = data.contact.Country;

                                // Add hidden input for contact message if not exists
                                if (!document.getElementById('contactMessageInput')) {
                                    const input = document.createElement('input');
                                    input.type = 'hidden';
                                    input.id = 'contactMessageInput';
                                    input.name = 'contactMessage';
                                    input.value = data.contact.ContactMessage;
                                    document.getElementById('confirmContactForm').appendChild(input);
                                } else {
                                    document.getElementById('contactMessageInput').value = data.contact.ContactMessage;
                                }

                                // Show the modal
                                confirmContactModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                            } else {
                                console.error('Failed to load contact details');
                            }
                        })
                        .catch(error => console.error('Fetch error:', error));
                });
            });

            confirmContactModalCancelBtn.addEventListener('click', () => {
                confirmContactModal.classList.add('opacity-0', 'invisible', '-translate-y-5');
                document.getElementById('darkOverlay2').classList.add('opacity-0', 'invisible');
                document.getElementById('darkOverlay2').classList.remove('opacity-100');
            });
        }
    }

    // Handle browser back/forward buttons for contacts
    window.addEventListener('popstate', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const filterSelect = document.querySelector('select[name="sort"]');
        const searchInput = document.querySelector('input[name="contact_search"]');

        if (filterSelect) filterSelect.value = urlParams.get('sort') || 'random';
        if (searchInput) searchInput.value = urlParams.get('contact_search') || '';
        loadContactPage(urlParams.get('contactpage') || 1);
    });
</script>