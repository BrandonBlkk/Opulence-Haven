<?php
session_start();
include('../config/dbConnection.php');
include('../includes/AutoIDFunc.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

$alertMessage = '';
$addRuleSuccess = false;
$updateRuleSuccess = false;
$deleteRuleSuccess = false;
$ruleID = AutoID('ruletb', 'RuleID', 'RL-', 6);

// Add Rule
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['addrule'])) {
    $ruletitle = mysqli_real_escape_string($connect, $_POST['ruletitle']);
    $rule = mysqli_real_escape_string($connect, $_POST['rule']);
    $ruleicon = mysqli_real_escape_string($connect, $_POST['ruleicon']);
    $ruleiconsize = mysqli_real_escape_string($connect, $_POST['ruleiconsize']);

    // Check if the product type already exists using prepared statement
    $checkQuery = "SELECT Rule FROM ruletb WHERE Rule = '$rule'";

    $checkQuery = mysqli_query($connect, $checkQuery);
    $count = mysqli_num_rows($checkQuery);

    if ($count > 0) {
        $alertMessage = 'Rule you added is already existed.';
    } else {
        $RuleQuery = "INSERT INTO ruletb (RuleID, RuleTitle, Rule, RuleIcon, IconSize)
        VALUES ('$ruleID', '$ruletitle', '$rule', '$ruleicon', '$ruleiconsize')";

        if (mysqli_query($connect, $RuleQuery)) {
            $addRuleSuccess = true;
        } else {
            $alertMessage = "Failed to add rule. Please try again.";
        }
    }
}

// Get Rule Details
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = mysqli_real_escape_string($connect, $_GET['id']);
    $action = $_GET['action'];

    // Build query based on action
    $query = match ($action) {
        'getRuleDetails' => "SELECT * FROM ruletb WHERE RuleID = '$id'",
        default => null
    };
    if ($query) {
        $result = mysqli_query($connect, $query);
        $rule = mysqli_fetch_assoc($result);

        if ($rule) {
            echo json_encode(['success' => true, 'rule' => $rule]);
        } else {
            echo json_encode(['success' => false]);
        }
    }
    exit;
}

// Update Rule
if (isset($_POST['editrule'])) {
    $ruleId = mysqli_real_escape_string($connect, $_POST['ruleid']);
    $updateRuleTitle = mysqli_real_escape_string($connect, $_POST['updateruletitle']);
    $updateRule = mysqli_real_escape_string($connect, $_POST['updaterule']);
    $updateRuleIcon = mysqli_real_escape_string($connect, $_POST['updateruleicon']);
    $updateRuleIconSize = mysqli_real_escape_string($connect, $_POST['updateruleiconsize']);

    // Update query
    $updateQuery = "UPDATE ruletb SET RuleTitle = '$updateRuleTitle', Rule = '$updateRule', RuleIcon = '$updateRuleIcon', IconSize = '$updateRuleIconSize'
    WHERE RuleID = '$ruleId'";

    if (mysqli_query($connect, $updateQuery)) {
        $updateRuleSuccess = true;
    } else {
        $alertMessage = "Failed to update rule. Please try again.";
    }
}

// Delete Rule
if (isset($_POST['deleterule'])) {
    $ruleId = mysqli_real_escape_string($connect, $_POST['ruleid']);

    // Build query based on action
    $deleteQuery = "DELETE FROM ruletb WHERE RuleID = '$ruleId'";

    if (mysqli_query($connect, $deleteQuery)) {
        $deleteRuleSuccess = true;
    } else {
        $alertMessage = "Failed to delete rule. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opulence Haven|Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../CSS/output.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../CSS/input.css?v=<?php echo time(); ?>">
</head>

<body>
    <?php include('../includes/AdminNavbar.php'); ?>

    <!-- Main Container -->
    <div class="flex flex-col md:flex-row md:space-x-3 p-3 ml-0 md:ml-[250px] min-w-[350px]">
        <!-- Left Side Content -->
        <div class="w-full bg-white p-2">
            <div class="flex justify-between items-end">
                <div>
                    <h2 class="text-xl text-gray-700 font-bold mb-4">Add Rule Overview</h2>
                    <p>Add information about rule to categorize items, track stock levels, and manage room details for efficient organization.</p>
                </div>
                <button id="addRuleBtn" class="bg-amber-500 text-white font-semibold px-3 py-1 rounded select-none hover:bg-amber-600 transition-colors">
                    <i class="ri-add-line text-xl"></i>
                </button>
            </div>

            <!-- Prooduct Type Table -->
            <div class="overflow-x-auto">
                <!-- Product Type Search and Filter -->
                <form method="GET" class="my-4 flex items-start sm:items-center justify-between flex-col sm:flex-row gap-2 sm:gap-0">
                    <h1 class="text-lg text-gray-700 font-semibold text-nowrap">All Rules <span class="text-gray-400 text-sm ml-2"><?php echo $ruleCount ?></span></h1>
                    <div class="flex items-center w-full">
                        <input type="text" name="rule_search" class="p-2 ml-0 sm:ml-5 border border-gray-300 rounded-md w-full" placeholder="Search for rule..." value="<?php echo isset($_GET['rule_search']) ? htmlspecialchars($_GET['rule_search']) : ''; ?>">
                    </div>
                </form>
                <div class="tableScrollBar overflow-y-auto max-h-[510px]">
                    <table class="min-w-full bg-white rounded-lg">
                        <thead>
                            <tr class="bg-gray-100 text-gray-600 text-sm">
                                <th class="p-3 text-start">ID</th>
                                <th class="p-3 text-start">Title</th>
                                <th class="p-3 text-start">Rule</th>
                                <th class="p-3 text-start hidden sm:table-cell">Icon</th>
                                <th class="p-3 text-start">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm">
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
                                        <?= htmlspecialchars($rule['Rule']) ?>
                                    </td>
                                    <td class="p-3 text-start">
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
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Controls -->
                <div class="flex justify-center items-center mt-1">
                    <!-- Previous Btn -->
                    <?php if ($roomTypeCurrentPage > 1) {
                    ?>
                        <a href="?roomtypepage=<?= $roomTypeCurrentPage - 1 ?>"
                            class="px-3 py-1 mx-1 border rounded <?= $roomtypepage == $roomTypeCurrentPage ? 'bg-gray-200' : 'bg-white' ?>">
                            <i class="ri-arrow-left-s-line"></i>
                        </a>
                    <?php
                    } else {
                    ?>
                        <p class="px-3 py-1 mx-1 border rounded cursor-not-allowed bg-gray-200">
                            <i class="ri-arrow-left-s-line"></i>
                        </p>
                    <?php
                    }
                    ?>
                    <?php for ($roomtypepage = 1; $roomtypepage <= $totalRoomTypePages; $roomtypepage++): ?>
                        <a href="?roomtypepage=<?= $roomtypepage ?>&roomtype_search=<?= htmlspecialchars($searchRoomTypeQuery) ?>"
                            class="px-3 py-1 mx-1 border rounded select-none <?= $roomtypepage == $roomTypeCurrentPage ? 'bg-gray-200' : 'bg-white' ?>">
                            <?= $roomtypepage ?>
                        </a>
                    <?php endfor; ?>
                    <!-- Next Btn -->
                    <?php if ($roomTypeCurrentPage < $totalRoomTypePages) {
                    ?>
                        <a href="?roomtypepage=<?= $roomTypeCurrentPage + 1 ?>"
                            class="px-3 py-1 mx-1 border rounded <?= $roomtypepage == $roomTypeCurrentPage ? 'bg-gray-200' : 'bg-white' ?>">
                            <i class="ri-arrow-right-s-line"></i>
                        </a>
                    <?php
                    } else {
                    ?>
                        <p class="px-3 py-1 mx-1 border rounded cursor-not-allowed bg-gray-200">
                            <i class="ri-arrow-right-s-line"></i>
                        </p>
                    <?php
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- Rule Details Modal -->
        <div id="updateRuleModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <div class="bg-white max-w-5xl p-6 rounded-md shadow-md text-center w-full sm:max-w-[500px]">
                <h2 class="text-xl text-start text-gray-700 font-bold">Edit Rule</h2>
                <form class="flex flex-col space-y-4" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" id="updateRuleForm">
                    <input type="hidden" name="ruleid" id="updateRuleID">
                    <!-- Rule Input -->
                    <div class="relative w-full">
                        <label class="block text-sm text-start font-medium text-gray-700 mb-1">Rule Information</label>
                        <input
                            id="updateRuleTitleInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="text"
                            name="updateruletitle"
                            placeholder="Enter title">
                        <small id="updateRuleTitleError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>
                    <div class="relative w-full">
                        <input
                            id="updateRuleInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="text"
                            name="updaterule"
                            placeholder="Enter rule">
                        <small id="updateRuleError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>
                    <!-- Icon Input -->
                    <div class="relative">
                        <input
                            id="updateRuleIconInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="text"
                            name="updateruleicon"
                            placeholder="Enter rule icon">
                        <small id="updateRuleIconError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>
                    <!-- Size -->
                    <div class="relative">
                        <label class="block text-sm text-start font-medium text-gray-700 mb-1">Choose Size</label>
                        <select name="updateruleiconsize" class="p-2 w-full border rounded">
                            <option value="" disabled>Select size of icon</option>
                            <option value="text-base">M</option>
                            <option value="text-lg">L</option>
                            <option value="text-xl" selected>XL (default)</option>
                            <option value="text-2xl">2XL</option>
                        </select>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end gap-4 select-none">
                        <div id="updateRuleModalCancelBtn" class="px-4 py-2 bg-gray-200 text-black hover:bg-gray-300 rounded-sm">
                            Cancel
                        </div>
                        <button
                            type="submit"
                            name="editrule"
                            class="bg-amber-500 text-white px-4 py-2 select-none hover:bg-amber-600 rounded-sm">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Room Type Delete Modal -->
        <div id="ruleConfirmDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <form class="bg-white max-w-5xl p-6 rounded-md shadow-md text-center" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" id="ruleDeleteForm">
                <h2 class="text-xl font-semibold text-red-600 mb-4">Confirm Rule Deletion</h2>
                <p class="text-slate-600 mb-2">You are about to delete the following Rule: <span id="ruleDeleteName" class="font-semibold"></span></p>
                <p class="text-sm text-gray-500 mb-4">
                    Deleting this Rule will permanently remove it from the system, including all associated data.
                </p>
                <input type="hidden" name="ruleid" id="deleteRuleID">
                <div class="flex justify-end gap-4 select-none">
                    <div id="ruleCancelDeleteBtn" class="px-4 py-2 bg-gray-200 text-black hover:bg-gray-300 rounded-sm">
                        Cancel
                    </div>
                    <button
                        type="submit"
                        name="deleterule"
                        class="px-4 py-2 bg-red-600 text-white hover:bg-red-700 rounded-sm">
                        Delete
                    </button>
                </div>
            </form>
        </div>

        <!-- Add Rule Form -->
        <div id="addRuleModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <div class="bg-white w-full md:w-1/3 p-6 rounded-md shadow-md ">
                <h2 class="text-xl text-gray-700 font-bold mb-4">Add New Rule</h2>
                <form class="flex flex-col space-y-4" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" id="ruleForm">
                    <!-- Rule Input -->
                    <div class="relative w-full">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Rule Information</label>
                        <input
                            id="ruleTitleInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="text"
                            name="ruletitle"
                            placeholder="Enter title">
                        <small id="ruleTitleError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>
                    <div class="relative w-full">
                        <input
                            id="ruleInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="text"
                            name="rule"
                            placeholder="Enter rule">
                        <small id="ruleError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>
                    <!-- Icon Input -->
                    <div class="relative">
                        <input
                            id="ruleIconInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="text"
                            name="ruleicon"
                            placeholder="Enter rule icon">
                        <small id="ruleIconError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>
                    <!-- Size -->
                    <div class="relative">
                        <label class="block text-sm text-start font-medium text-gray-700 mb-1">Choose Size</label>
                        <select name="ruleiconsize" class="p-2 w-full border rounded">
                            <option value="" disabled>Select size of icon</option>
                            <option value="text-base">M</option>
                            <option value="text-lg">L</option>
                            <option value="text-xl" selected>XL (default)</option>
                            <option value="text-2xl">2XL</option>
                        </select>
                    </div>

                    <div class="flex justify-end gap-4 select-none">
                        <div id="addRuleCancelBtn" class="px-4 py-2 text-amber-500 font-semibold hover:text-amber-600">
                            Cancel
                        </div>
                        <!-- Submit Button -->
                        <button
                            type="submit"
                            name="addrule"
                            class="bg-amber-500 text-white font-semibold px-4 py-2 rounded-sm select-none hover:bg-amber-600 transition-colors">
                            Add Rule
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Loader -->
    <?php
    include('../includes/Alert.php');
    include('../includes/Loader.php');
    ?>

    <script src="//unpkg.com/alpinejs" defer></script>
    <script type="module" src="../JS/admin.js"></script>
</body>

</html>