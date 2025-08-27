<aside id="aside" class="fixed top-0 -right-full flex flex-col bg-white w-full md:w-[330px] h-full p-4 z-40 transition-all duration-500 ease-in-out">
    <div class="flex justify-between pb-3">
        <div class="max-w-[300px]">
            <span class="text-2xl font-semibold">Hello,</span>
            <span class="font-semibold"><?php echo !empty($_SESSION['UserName']) ? $_SESSION['UserName'] : 'Guest'; ?></span>
            <p class="text-slate-400 text-xs <?php echo empty($_SESSION['UserEmail']) ? 'hidden' : 'flex' ?>">
                <?php echo $_SESSION['UserEmail'] ?>
            </p>
            <!-- Points Balance Display -->
            <?php if (!empty($_SESSION['UserID'])): ?>
                <?php
                // Initialize points balance with default value
                $pointsBalance = 0;

                // Prepare and execute the query safely
                $stmt = $connect->prepare("SELECT PointsBalance, Membership FROM usertb WHERE UserID = ?");
                if ($stmt) {
                    $stmt->bind_param("s", $_SESSION['UserID']);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result && $result->num_rows > 0) {
                        $userPoints = $result->fetch_assoc();
                        $pointsBalance = $userPoints['PointsBalance'] ?? 0;
                        $membership = $userPoints['Membership'] ?? 0;
                    }
                    $stmt->close();
                }
                ?>
                <div class="flex items-center gap-2 mt-2">
                    <div class="w-6 h-6 rounded-full bg-amber-500 flex items-center justify-center">
                        <i class="ri-copper-coin-line text-white text-sm"></i>
                    </div>
                    <span class="text-sm font-semibold text-gray-700 points-balance-display">
                        <?php echo number_format($pointsBalance) ?>
                        <?php echo ($pointsBalance > 1) ? 'Points' : 'Point'; ?>
                        Available to Redeem
                    </span>
                </div>
            <?php endif; ?>
        </div>
        <i id="closeBtn" class="ri-close-line text-2xl cursor-pointer rounded transition-colors duration-300"></i>
    </div>
    <div class="flex flex-col justify-between gap-3 h-full">
        <div>
            <div class="flex flex-col gap-3 py-2 pb-4 border-b">
                <h1><?php echo !empty($_SESSION['UserID']) ? 'Create a new account' : 'First time with us?'; ?></h1>
                <a href="../User/user_signup.php" class="bg-amber-500 hover:bg-amber-600 text-white font-semibold text-center p-2 select-none transition-colors duration-300">Sign Up</a>
                <a href="../User/user_signin.php" class="border hover:bg-gray-100 font-semibold text-blue-900 text-center p-2 select-none transition-colors duration-300">Sign In</a>
            </div>
            <div x-data="{ expanded: false, height: 0 }" class="flex flex-col py-2">
                <button @click="expanded = !expanded; height = expanded ? $refs.dropdown.scrollHeight : 0" class="flex items-center justify-between">
                    <h1 class="text-xl font-semibold pb-2">Profile</h1>
                    <i :class="expanded ? 'rotate-180' : 'rotate-0'" class="ri-arrow-up-s-line text-xl transition-transform duration-300"></i>
                </button>
                <div
                    x-ref="dropdown"
                    :style="{ height: expanded ? height + 'px' : '0px' }"
                    class="overflow-hidden transition-all duration-300 select-none">
                    <a href="<?php echo isset($_SESSION['UserID']) ? '../User/profile_edit.php' : '#'; ?>"
                        class="flex justify-between <?php echo isset($_SESSION['UserID']) ? 'text-slate-600 hover:bg-gray-100' : 'text-gray-400 cursor-not-allowed'; ?> p-2 rounded-sm transition-colors duration-300"
                        <?php if (!isset($_SESSION['UserID'])); ?>>
                        <div class="flex items-center gap-1">
                            <i class="ri-user-settings-line text-xl"></i>
                            <p class="font-semibold text-sm">Your Profile</p>
                        </div>
                        <?php if (!isset($_SESSION['UserID'])): ?>
                            <span class="text-xs text-amber-500">(Sign In Required)</span>
                        <?php endif; ?>
                    </a>
                    <div class="pl-3">
                        <a href="../User/reservation.php" class="flex justify-between text-slate-600 hover:bg-gray-100 p-2 rounded-sm transition-colors duration-300">
                            <div class="flex items-center gap-1">
                                <i class="ri-hotel-bed-line text-xl"></i>
                                <p class="font-semibold text-sm">Current Selections</p>
                            </div>
                            <?php
                            // Show reservation count if user is logged in
                            if (!empty($_SESSION['UserID'])) {
                                $reservationCount = 0;
                                $stmt = $connect->prepare("SELECT COUNT(*) as count FROM reservationtb WHERE UserID = ? AND Status = 'Pending'");
                                if ($stmt) {
                                    $stmt->bind_param("s", $_SESSION['UserID']);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    if ($result && $result->num_rows > 0) {
                                        $reservationCount = $result->fetch_assoc()['count'];
                                    }
                                    $stmt->close();
                                }
                                if ($reservationCount > 0) {
                                    echo '<p class="px-2 text-white bg-blue-950 rounded-sm ml-5">' . $reservationCount . '</p>';
                                }
                            }
                            ?>
                        </a>
                        <?php
                        $stay = "SELECT COUNT(*) as count FROM reservationtb WHERE UserID = ? AND Status = 'Confirmed'";
                        $stmt = $connect->prepare($stay);
                        $stmt->bind_param("s", $_SESSION['UserID']);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $stay_count = $result->fetch_assoc()['count'];

                        // order list
                        $order = "SELECT COUNT(*) as count FROM ordertb WHERE UserID = ? AND Status = 'Order Placed'";
                        $stmt = $connect->prepare($order);
                        $stmt->bind_param("s", $_SESSION['UserID']);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $order_count = $result->fetch_assoc()['count'];
                        $stmt->close();
                        ?>
                        <a href="../User/upcoming_stays.php" class="flex justify-between text-slate-600 hover:bg-gray-100 p-2 rounded-sm transition-colors duration-300">
                            <div class="flex items-center gap-1">
                                <i class="ri-calendar-event-line text-xl"></i>
                                <p class="font-semibold text-sm">Upcoming Stays</p>
                            </div>
                            <?php
                            if ($stay_count) {
                            ?>
                                <p class="px-2 text-white bg-blue-950 rounded-sm ml-5"><?= $stay_count ?></p>
                            <?php
                            }
                            ?>
                        </a>
                        <a href="../Store/order_history.php" class="flex justify-between text-slate-600 hover:bg-gray-100 p-2 rounded-sm transition-colors duration-300">
                            <div class="flex items-center gap-1">
                                <i class="ri-file-list-3-line text-xl"></i>
                                <p class="font-semibold text-sm">Purchase List</p>
                            </div>
                            <p class="px-2 text-white bg-blue-950 rounded-sm ml-5"><?= $order_count ?></p>
                        </a>
                    </div>
                </div>
            </div>
            <div class="flex flex-col">
                <h1 class="text-xl font-semibold">Setting</h1>
                <!-- Google Translate -->
                <div id="google_translate_element" class="mt-3 pl-3"></div>
                <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElement"></script>

                <script type="text/javascript">
                    function googleTranslateElement() {
                        new google.translate.TranslateElement({
                            pageLanguage: 'en'
                        }, 'google_translate_element');
                    }
                </script>
            </div>
        </div>
        <div class="space-y-2">
            <p class="text-xs text-slate-600">
                Contact our 24/7 support for any assistance with your bookings or inquiries.
                <a href="mailto:support@opulenceHaven.com" class="text-amber-600 underline hover:underline-offset-2">support@opulenceHaven.com</a>
            </p>
            <div id="logoutBtn" class="flex items-center gap-1 text-slate-600 hover:bg-gray-100 p-3 rounded-sm transition-colors duration-300 cursor-pointer select-none <?php echo empty($_SESSION['UserID']) ? 'hidden' : '' ?>">
                <i class="ri-logout-box-r-line text-xl"></i>
                <p class="font-semibold text-sm">Logout</p>
            </div>
        </div>
    </div>
</aside>

<!-- Confirmation Modal -->
<div id="confirmModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
    <div class="bg-white max-w-5xl p-6 rounded-md shadow-md text-center">
        <h2 class="text-xl font-semibold text-blue-900 mb-4">Confirm Logout</h2>
        <p class="text-slate-600 mb-2">You are currently signed in as:</p>
        <p class="font-semibold text-gray-800 mb-4">
            <?php echo $_SESSION['UserName'] . ' (' . $_SESSION['UserEmail'] . ')'; ?>
        </p>
        <p class="text-sm text-gray-500 mb-6">
            Logging out will end your session and remove access to secure areas of your account. Ensure all changes are saved.
        </p>
        <div class="flex justify-end gap-4 select-none">
            <button id="cancelBtn" class="px-4 py-2 bg-gray-200 text-black hover:bg-gray-300">
                Cancel
            </button>
            <button id="confirmLogoutBtn" class="px-4 py-2 bg-red-600 text-white hover:bg-red-700">
                Logout
            </button>
        </div>
    </div>
</div>

<!-- Loader -->
<?php
include('loader.php');
?>

<!-- Overlay -->
<div id="darkOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden"></div>
<div id="darkOverlay2" class="fixed inset-0 bg-black bg-opacity-50 opacity-0 invisible z-40 transition-opacity duration-300"></div>