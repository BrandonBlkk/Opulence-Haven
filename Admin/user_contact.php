<?php
session_start();
include('../config/db_connection.php');

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
    $response = mysqli_real_escape_string($connect, $_POST['adminResponse']);
    $username = 'Bran';
    $useremail = 'kyawzayartun0527@gmail.com';

    // Prepare and execute update query
    $updateQuery = "UPDATE contacttb SET Status = ? WHERE ContactID = '$contactId'";
    $stmt = $connect->prepare($updateQuery);

    if ($stmt) {
        $status = 'responded';
        $stmt->bind_param("s", $status);

        if ($stmt->execute()) {
            $confirmContactSuccess = true;
            $_SESSION['contact_data'] = [
                'useremail' => $useremail,
                'username' => $username,
                'response' => $response
            ];
        } else {
            $alertMessage = "Failed to update contact. Please try again.";
        }

        $stmt->close();
    } else {
        $alertMessage = "Failed to prepare the update statement.";
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
    <?php include('../includes/admin_navbar.php'); ?>

    <!-- Main Container -->
    <div class="p-3 ml-0 md:ml-[250px] min-w-[380px] relative">
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
                        <input type="text" name="contact_search" class="p-2 ml-0 sm:ml-5 border border-gray-300 rounded-md w-full outline-none focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out" placeholder="Search for user contact..." value="<?php echo isset($_GET['contact_search']) ? htmlspecialchars($_GET['contact_search']) : ''; ?>">
                        <div class="flex items-center">
                            <label for="sort" class="ml-4 mr-2 flex items-center cursor-pointer select-none">
                                <i class="ri-filter-2-line text-xl"></i>
                                <p>Filters</p>
                            </label>
                            <!-- Search and filter form -->
                            <form method="GET" class="flex flex-col md:flex-row items-center gap-4 mb-4">
                                <select name="sort" id="sort" class="border p-2 rounded text-sm outline-none">
                                    <option value="random" <?= ($filterStatus === 'random') ? 'selected' : ''; ?>>All Status</option>
                                    <option value="pending" <?= ($filterStatus === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                    <option value="responded" <?= ($filterStatus === 'responded') ? 'selected' : ''; ?>>Responded</option>
                                </select>
                            </form>
                            <i id="contactDateFilterBtn" class="ri-calendar-2-line text-xl px-3 cursor-pointer"></i>
                        </div>
                    </div>
                </form>

                <!-- Contact Table -->
                <div class="tableScrollBar overflow-y-auto max-h-[510px]">
                    <div id="contactResults">
                        <?php include '../includes/admin_table_components/contact_results.php'; ?>
                    </div>
                </div>

                <!-- Pagination Controls -->
                <div id="paginationContainer" class="flex justify-between items-center mt-3">
                    <?php include '../includes/admin_table_components/contact_pagination.php'; ?>
                </div>
            </div>
        </div>

        <!-- Contact Details Modal -->
        <div id="confirmContactModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-4 -translate-y-5 transition-all duration-300">
            <div class="bg-white max-w-4xl w-full rounded-lg shadow-xl overflow-hidden">
                <!-- Modal Header -->
                <div class="bg-gray-50 px-6 py-4 border-b">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-gray-800">Contact Inquiry Details</h2>
                    </div>
                    <div class="flex items-center mt-2 text-sm text-gray-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span id="contactDate"></span>
                    </div>
                </div>

                <!-- Form -->
                <form class="flex flex-col" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" id="confirmContactForm">
                    <input type="hidden" name="contactid" id="confirmContactID">

                    <div class="p-6 space-y-6">
                        <!-- User Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="block text-sm font-medium text-gray-500">Full Name</label>
                                <div id="username" name="username" class="text-gray-800 font-medium p-2 bg-gray-50 rounded"></div>
                            </div>
                            <div class="space-y-1">
                                <label class="block text-sm font-medium text-gray-500">Email Address</label>
                                <div id="useremail" name="useremail" class="text-gray-800 p-2 bg-gray-50 rounded"></div>
                            </div>
                            <div class="space-y-1">
                                <label class="block text-sm font-medium text-gray-500">Phone Number</label>
                                <div id="userphone" class="text-gray-800 p-2 bg-gray-50 rounded"></div>
                            </div>
                            <div class="space-y-1">
                                <label class="block text-sm font-medium text-gray-500">Country</label>
                                <div id="usercountry" class="text-gray-800 p-2 bg-gray-50 rounded"></div>
                            </div>
                        </div>

                        <!-- Original Message -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">Original Message</label>
                            <div id="contactMessage" class="p-3 border border-gray-200 rounded-md bg-gray-50 text-gray-700 min-h-[100px] max-h-[200px] overflow-y-auto"></div>
                        </div>

                        <!-- Admin Response -->
                        <div class="space-y-2">
                            <label for="adminResponse" class="block text-sm font-medium text-gray-700">Your Response</label>
                            <textarea name="adminResponse" id="adminResponse" rows="4" class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out" placeholder="Type your response here..." required></textarea>
                            <div class="flex items-center text-sm text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                This response will be emailed to the user
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="bg-gray-50 px-6 py-3 gap-4 flex justify-end border-t select-none">
                        <div id="confirmContactModalCancelBtn" class="px-4 py-2 bg-gray-200 text-black hover:bg-gray-300 rounded-sm">
                            Cancel
                        </div>
                        <button
                            type="submit"
                            name="respondcontact"
                            class="bg-amber-500 text-white px-4 py-2 select-none hover:bg-amber-600 rounded-sm">
                            Send Response
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Date Filter -->
        <div id="contactDateFilterModal" class="fixed top-36 right-0 sm:right-5 z-50 w-full sm:max-w-[500px] flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
            <div class="bg-white max-w-5xl p-5 rounded-md shadow-md text-center w-full sm:max-w-[500px]">
                <h2 class="text-md text-start text-gray-700 font-bold mb-4">Date Filter</h2>
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
    include('../includes/alert.php');
    include('../includes/loader.php');
    ?>

    <script src="//unpkg.com/alpinejs" defer></script>
    <script type="module" src="../JS/admin.js"></script>
</body>

</html>