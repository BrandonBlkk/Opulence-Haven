<aside id="aside" class="fixed top-0 -right-full flex flex-col bg-white w-full md:w-[330px] h-full p-4 z-40 transition-all duration-500 ease-in-out">
    <div class="flex justify-between pb-3">
        <div class="max-w-[300px]">
            <span class="text-2xl font-semibold">Hello,</span>
            <span class="font-semibold"><?php echo !empty($_SESSION['UserName']) ? $_SESSION['UserName'] : 'Guest'; ?></span>
            <p class="text-slate-400 text-xs <?php echo empty($_SESSION['UserEmail']) ? 'hidden' : 'flex' ?>">
                <?php echo $_SESSION['UserEmail'] ?>
            </p>
        </div>
        <i id="closeBtn" class="ri-close-line text-2xl cursor-pointer rounded transition-colors duration-300"></i>
    </div>
    <div class="flex flex-col justify-between gap-3 h-full">
        <div>
            <div class="flex flex-col gap-3 py-2 pb-4 border-b">
                <h1>Are you new here?</h1>
                <a href="UserSignUp.php" class="bg-amber-500 hover:bg-amber-600 text-white font-semibold text-center p-2 select-none transition-colors duration-300">Sign Up</a>
                <a href="UserSignIn.php" class="border hover:bg-gray-100 font-semibold text-blue-900 text-center p-2 select-none transition-colors duration-300">Sign In</a>
            </div>
            <div x-data="{ expanded: false, height: 0 }" class="flex flex-col py-2">
                <button @click="expanded = !expanded; height = expanded ? $refs.dropdown.scrollHeight : 0" class="flex items-center justify-between">
                    <h1 class="text-xl font-semibold pb-2">Profile</h1>
                    <i :class="expanded ? 'rotate-180' : 'rotate-0'" class="ri-arrow-up-s-line text-xl transition-transform duration-300"></i>
                </button>
                <div
                    x-ref="dropdown"
                    :style="{ height: expanded ? height + 'px' : '0px' }"
                    class="overflow-hidden transition-all duration-300 pl-3 select-none">
                    <a href="#" class="flex justify-between text-slate-600 hover:bg-gray-100 p-2 rounded-sm transition-colors duration-300">
                        <div class="flex items-center gap-1">
                            <i class="ri-book-2-line text-xl"></i>
                            <p class="font-semibold text-sm">My Booking</p>
                        </div>
                        <p class="px-2 text-white bg-blue-950 rounded-sm ml-5">1</p>
                    </a>
                    <a href="#" class="flex justify-between text-slate-600 hover:bg-gray-100 p-2 rounded-sm transition-colors duration-300">
                        <div class="flex items-center gap-1">
                            <i class="ri-file-list-3-line text-xl"></i>
                            <p class="font-semibold text-sm">Purchase List</p>
                        </div>
                        <p class="px-2 text-white bg-blue-950 rounded-sm ml-5">3</p>
                    </a>
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
        <div id="logoutBtn" class="flex items-center gap-1 text-slate-600 hover:bg-gray-100 p-3 rounded-sm transition-colors duration-300 cursor-pointer select-none <?php echo empty($_SESSION['UserID']) ? 'hidden' : '' ?>">
            <i class="ri-logout-box-r-line text-xl"></i>
            <p class="font-semibold text-sm">Logout</p>
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
include('./includes/Loader.php');
?>

<!-- Overlay -->
<div id="darkOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden"></div>
<div id="darkOverlay2" class="fixed inset-0 bg-black bg-opacity-50 opacity-0 invisible  z-40 transition-opacity duration-300"></div>