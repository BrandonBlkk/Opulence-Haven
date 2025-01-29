<?php
session_start();
include('../config/dbConnection.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

$alertMessage = '';
$confirmContactSuccess = false;

// Get Contact Details
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = mysqli_real_escape_string($connect, $_GET['id']);
    $action = $_GET['action'];

    // Build query based on action
    $query = match ($action) {
        'getContactDetails' => "SELECT * FROM contacttb WHERE ContactID = '$id'",
        default => null
    };
    if ($query) {
        $result = $connect->query($query)->fetch_assoc();
    }

    if ($result) {
        echo json_encode(['success' => true, 'contact' => $result]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}

// Update Contact
if (isset($_POST['respondcontact'])) {
    $contactId = mysqli_real_escape_string($connect, $_POST['contactid']);

    // Update query
    $updateQuery = "UPDATE contacttb SET Status = 'responded' WHERE ContactID = '$contactId'";

    if ($connect->query($updateQuery)) {
        $confirmContactSuccess = true;
    } else {
        $alertMessage = "Failed to update product type. Please try again.";
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
    <div class="p-3 ml-0 md:ml-[250px] min-w-[350px] relative">
        <!-- Left Side Content -->
        <div class="w-full bg-white p-2">
            <h2 class="text-xl font-bold mb-4">Manage User Contacts</h2>
            <p>View the list of user contacts and manage them.</p>

            <!-- User Contact Table -->
            <div class="overflow-x-auto">
                <!-- User Contact Search and Filter -->
                <form method="GET" class="my-4 flex items-start sm:items-center justify-between flex-col sm:flex-row gap-2 sm:gap-0">
                    <h1 class="text-lg font-semibold text-nowrap">All Contacts <span class="text-gray-400 text-sm ml-2"><?php echo $contactCount ?></span></h1>
                    <div class="flex flex-col sm:flex-row items-start sm:items-center w-full gap-2 sm:gap-0">
                        <input type="text" name="contact_search" class="p-2 ml-0 sm:ml-5 border border-gray-300 rounded-md w-full" placeholder="Search for user contact..." value="<?php echo isset($_GET['contact_search']) ? htmlspecialchars($_GET['contact_search']) : ''; ?>">
                        <div class="flex items-center">
                            <label for="sort" class="ml-4 mr-2 flex items-center cursor-pointer select-none">
                                <i class="ri-filter-2-line text-xl"></i>
                                <p>Filters</p>
                            </label>
                            <!-- Search and filter form -->
                            <form method="GET" class="flex flex-col md:flex-row items-center gap-4 mb-4">
                                <select name="sort" id="sort" class="border p-2 rounded text-sm" onchange="this.form.submit()">
                                    <option value="random" <?= ($filterStatus === 'random') ? 'selected' : ''; ?>>All Status</option>
                                    <option value="pending" <?= ($filterStatus === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                    <option value="responded" <?= ($filterStatus === 'responded') ? 'selected' : ''; ?>>Responded</option>
                                </select>
                            </form>
                            <i id="contactDateFilterBtn" class="ri-calendar-2-line text-xl px-3 cursor-pointer"></i>
                        </div>
                    </div>
                </form>
                <div class="tableScrollBar overflow-y-auto max-h-[510px]">
                    <table class="min-w-full bg-white rounded-lg">
                        <thead>
                            <tr class="bg-gray-100 text-gray-600 text-sm">
                                <th class="p-3 text-start">ID</th>
                                <th class="p-3 text-start hidden sm:table-cell">UserID</th>
                                <th class="p-3 text-start">User</th>
                                <th class="p-3 text-start hidden xl:table-cell">Phone</th>
                                <th class="p-3 text-start hidden xl:table-cell">Country</th>
                                <th class="p-3 text-start hidden lg:table-cell">Message</th>
                                <th class=" p-3 text-start hidden md:table-cell">Status</th>
                                <th class="p-3 text-start hidden xl:table-cell">Date</th>
                                <th class="p-3 text-start">Action</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm">
                            <?php foreach ($contacts as $contact): ?>
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="p-3 text-start whitespace-nowrap">
                                        <div class="flex items-center gap-2 font-medium text-gray-500">
                                            <span><?= htmlspecialchars($contact['ContactID']) ?></span>
                                        </div>
                                    </td>
                                    <td class="p-3 text-start whitespace-nowrap hidden sm:table-cell">
                                        <span><?= htmlspecialchars($contact['UserID']) ?></span>
                                    </td>
                                    <td class="p-3 text-start flex items-center gap-2">
                                        <div>
                                            <p class="font-bold"><?= htmlspecialchars($contact['FullName']) ?></p>
                                            <p><?= htmlspecialchars($contact['UserEmail']) ?></p>
                                        </div>
                                    </td>
                                    <td class="p-3 text-start hidden xl:table-cell">
                                        <p><?= htmlspecialchars($contact['UserPhone']) ?></p>
                                    </td>
                                    <td class="p-3 text-start space-x-1 select-none hidden xl:table-cell">
                                        <p><?= htmlspecialchars($contact['Country']) ?></p>
                                    </td>
                                    <td class="p-3 text-start space-x-1 hidden lg:table-cell"">
                                        <p>
                                            <?= htmlspecialchars(mb_strimwidth($contact['ContactMessage'], 0, 50, '...')) ?>
                                        </p>
                                    </td>
                                    <td class=" p-3 text-start space-x-1 select-none hidden md:table-cell">
                                        <span class="p-1 rounded-md <?= $contact['Status'] === 'responded' ? 'bg-green-100' : 'bg-red-100' ?>">
                                            <?= htmlspecialchars($contact['Status']) ?>
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
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Controls -->
                <div class="flex justify-center items-center mt-1">
                    <!-- Previous Btn -->
                    <?php if ($contactCurrentPage > 1) {
                    ?>
                        <a href="?contactpage=<?= $contactCurrentPage - 1 ?>"
                            class="px-3 py-1 mx-1 border rounded <?= $contactpage == $contactCurrentPage ? 'bg-gray-200' : 'bg-white' ?>">
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
                    <?php for ($contactpage = 1; $contactpage <= $totalContactPages; $contactpage++): ?>
                        <a href="?contactpage=<?= $contactpage ?>&sort=<?= htmlspecialchars($filterStatus) ?>&contact_search=<?= htmlspecialchars($searchContactQuery) ?>"
                            class="px-3 py-1 mx-1 border rounded select-none <?= $contactpage == $contactCurrentPage ? 'bg-gray-200' : 'bg-white' ?>">
                            <?= $contactpage ?>
                        </a>
                    <?php endfor; ?>
                    <!-- Next Btn -->
                    <?php if ($contactCurrentPage < $totalContactPages) {
                    ?>
                        <a href="?contactpage=<?= $contactCurrentPage + 1 ?>"
                            class="px-3 py-1 mx-1 border rounded <?= $contactpage == $contactCurrentPage ? 'bg-gray-200' : 'bg-white' ?>">
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

        <!-- Contact Details Modal -->
        <div id="confirmContactModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <div class="bg-white max-w-5xl p-6 rounded-md shadow-md text-center w-full sm:max-w-[500px]">
                <h2 class="text-xl font-bold">User Contact</h2>
                <form class="flex flex-col space-y-4" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" id="confirmContactForm">
                    <input type="hidden" name="contactid" id="confirmContactID">
                    <div class="text-gray-600 text-sm text-left">
                        <h1 class="font-medium text-gray-700 text-lg">Form</h1>
                        <p class="font-bold" id="username"></p>
                        <p id="useremail"></p>
                    </div>
                    <!-- Message Input -->
                    <div class="w-full">
                        <label class="block text-sm text-start font-medium text-gray-700 mb-1">Message</label>
                        <p id="contactMessage" class="text-start"></p>
                    </div>
                    <!-- Submit Button -->
                    <div class="flex justify-end gap-4 select-none">
                        <div id="confirmContactModalCancelBtn" class="px-4 py-2 bg-gray-200 text-black hover:bg-gray-300 rounded-sm">
                            Cancel
                        </div>
                        <button
                            type="submit"
                            name="respondcontact"
                            class="bg-amber-500 text-white px-4 py-2 select-none hover:bg-amber-600 rounded-sm">
                            Respond
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Date Filter -->
        <div id="contactDateFilterModal" class="fixed top-36 right-0 sm:right-5 z-50 w-full sm:max-w-[500px] flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <div class="bg-white max-w-5xl p-5 rounded-md shadow-md text-center w-full sm:max-w-[500px]">
                <h2 class="text-md text-start font-bold mb-4">Date Filter</h2>
                <form class="flex flex-col space-y-4" method="get">
                    <!-- Date Filter: From -->
                    <div class="text-start">
                        <label class="block text-sm text-start font-medium text-gray-700 mb-1">From:</label>
                        <input type="date" name="from_date" id="from_date" value="<?php echo isset($_GET['from_date']) ? $_GET['from_date'] : ''; ?>" class="border p-2 rounded w-full">
                    </div>
                    <!-- Date Filter: To -->
                    <div class="text-start">
                        <label class="block text-sm text-start font-medium text-gray-700 mb-1">To:</label>
                        <input type="date" name="to_date" id="to_date" value="<?php echo isset($_GET['to_date']) ? $_GET['to_date'] : ''; ?>" class="border p-2 rounded w-full">
                    </div>
                    <!-- Search Button -->
                    <div class="flex justify-end gap-4 select-none">
                        <button type="submit" class="w-full sm:w-auto bg-amber-500 text-white px-4 py-2 rounded-sm hover:bg-amber-600">
                            Search
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