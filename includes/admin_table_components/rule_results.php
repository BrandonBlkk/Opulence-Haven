<?php
include(__DIR__ . '/../../config/dbConnection.php');
include(__DIR__ . '/../admin_pagination.php');

// Construct the rule query based on search
if (!empty($searchRuleQuery)) {
    $ruleSelect = "SELECT * FROM ruletb WHERE RuleTitle LIKE '%$searchRuleQuery%' OR Rule LIKE '%$searchRuleQuery%' LIMIT $rowsPerPage OFFSET $ruleOffset";
} else {
    $ruleSelect = "SELECT * FROM ruletb LIMIT $rowsPerPage OFFSET $ruleOffset";
}

$ruleSelectQuery = $connect->query($ruleSelect);
$rules = [];

if (mysqli_num_rows($ruleSelectQuery) > 0) {
    while ($row = $ruleSelectQuery->fetch_assoc()) {
        $rules[] = $row;
    }
}
?>

<table class="min-w-full bg-white rounded-lg">
    <thead>
        <tr class="bg-gray-100 text-gray-600 text-sm">
            <th class="p-3 text-start">ID</th>
            <th class="p-3 text-start">Title</th>
            <th class="p-3 text-start hidden sm:table-cell">Rule</th>
            <th class="p-3 text-start hidden sm:table-cell">Icon</th>
            <th class="p-3 text-start">Actions</th>
        </tr>
    </thead>
    <tbody class="text-gray-600 text-sm">
        <?php if (!empty($rules)): ?>
            <?php foreach ($rules as $rule): ?>
                <tr class="border-b border-gray-200 hover:bg-gray-50">
                    <td class="p-3 text-start whitespace-nowrap">
                        <div class="flex items-center gap-2 font-medium text-gray-500">
                            <input type="checkbox" class="form-checkbox h-3 w-3 border-2 text-amber-500">
                            <span><?= htmlspecialchars($rule['RuleID']) ?></span>
                        </div>
                    </td>
                    <td class="p-3 text-start">
                        <?= htmlspecialchars($rule['RuleTitle']) ?>
                    </td>
                    <td class="p-3 text-start hidden sm:table-cell">
                        <?= htmlspecialchars(mb_strimwidth($rule['Rule'], 0, 50, '...')) ?>
                    </td>
                    <td class="p-3 text-start hidden sm:table-cell">
                        <?= !empty($rule['RuleIcon']) && !empty($rule['IconSize'])
                            ? '<i class="' . htmlspecialchars($rule['RuleIcon'], ENT_QUOTES, 'UTF-8') . ' ' . htmlspecialchars($rule['IconSize'], ENT_QUOTES, 'UTF-8') . '"></i>'
                            : 'None' ?>
                    </td>
                    <td class="p-3 text-start space-x-1 select-none">
                        <i class="details-btn ri-eye-line text-lg cursor-pointer"
                            data-rule-id="<?= htmlspecialchars($rule['RuleID']) ?>"></i>
                        <button class="text-red-500">
                            <i class="delete-btn ri-delete-bin-7-line text-xl"
                                data-rule-id="<?= htmlspecialchars($rule['RuleID']) ?>"></i>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" class="p-3 text-center text-gray-500 py-52">
                    No rules available.
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<script>
    // Function to load a specific page
    function loadRulePage(page) {
        const urlParams = new URLSearchParams(window.location.search);
        const searchQuery = urlParams.get('rule_search') || '';

        // Update URL parameters
        urlParams.set('rulepage', page);
        if (searchQuery) urlParams.set('rule_search', searchQuery);

        const xhr = new XMLHttpRequest();
        xhr.open('GET', `../includes/admin_table_components/rule_results.php?${urlParams.toString()}`, true);

        xhr.onload = function() {
            if (this.status === 200) {
                document.getElementById('ruleResults').innerHTML = this.responseText;

                // Also update the pagination controls
                const xhrPagination = new XMLHttpRequest();
                xhrPagination.open('GET', `../includes/admin_table_components/rule_pagination.php?${urlParams.toString()}`, true);
                xhrPagination.onload = function() {
                    if (this.status === 200) {
                        document.getElementById('paginationContainer').innerHTML = this.responseText;
                    }
                };
                xhrPagination.send();

                window.history.pushState({}, '', `?${urlParams.toString()}`);
                window.scrollTo(0, 0);
                initializeRuleActionButtons();
            }
        };

        xhr.send();
    }

    // Function to handle search
    function handleRuleSearch() {
        const searchInput = document.querySelector('input[name="rule_search"]');

        // Reset to page 1 when searching
        loadRulePage(1);
    }

    // Initialize event listeners for search
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.querySelector('input[name="rule_search"]');

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const urlParams = new URLSearchParams(window.location.search);
                urlParams.set('rule_search', this.value);
                window.history.pushState({}, '', `?${urlParams.toString()}`);
                handleRuleSearch();
            });
        }

        initializeRuleActionButtons();
    });

    // Function to initialize action buttons for rules
    function initializeRuleActionButtons() {
        // Function to attach event listeners to a row
        const attachEventListenersToRow = (row) => {
            // Details button
            const detailsBtn = row.querySelector('.details-btn');
            if (detailsBtn) {
                detailsBtn.addEventListener('click', function() {
                    const ruleId = this.getAttribute('data-rule-id');
                    darkOverlay2.classList.remove('opacity-0', 'invisible');
                    darkOverlay2.classList.add('opacity-100');

                    fetch(`../Admin/AddRule.php?action=getRuleDetails&id=${ruleId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById('updateRuleID').value = ruleId;
                                document.querySelector('[name="updateruletitle"]').value = data.rule.RuleTitle;
                                document.querySelector('[name="updaterule"]').value = data.rule.Rule;
                                document.querySelector('[name="updateruleicon"]').value = data.rule.RuleIcon;
                                document.querySelector('[name="updateruleiconsize"]').value = data.rule.IconSize;
                                updateRuleModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                            } else {
                                console.error('Failed to load rule details');
                            }
                        })
                        .catch(error => console.error('Fetch error:', error));
                });
            }

            // Delete button
            const deleteBtn = row.querySelector('.delete-btn');
            if (deleteBtn) {
                deleteBtn.addEventListener('click', function() {
                    const ruleId = this.getAttribute('data-rule-id');
                    darkOverlay2.classList.remove('opacity-0', 'invisible');
                    darkOverlay2.classList.add('opacity-100');

                    fetch(`../Admin/AddRule.php?action=getRuleDetails&id=${ruleId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById('deleteRuleID').value = ruleId;
                                document.getElementById('ruleDeleteName').textContent = data.rule.RuleTitle;
                                ruleConfirmDeleteModal.classList.remove('opacity-0', 'invisible', '-translate-y-5');
                            } else {
                                console.error('Failed to load rule details');
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
        const searchInput = document.querySelector('input[name="rule_search"]');
        if (searchInput) {
            searchInput.value = urlParams.get('rule_search') || '';
        }
        loadRulePage(urlParams.get('rulepage') || 1);
    });
</script>