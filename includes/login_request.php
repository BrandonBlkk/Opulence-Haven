<div id="loginModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
    <!-- Modal Container -->
    <div class="relative bg-white rounded-md shadow-2xl max-w-md w-full mx-4 z-10 overflow-hidden">
        <!-- Modal Header -->
        <div class="bg-blue-950 p-6 flex justify-between items-center">
            <div class="flex items-center space-x-3">

                <h3 class="text-xl font-semibold text-white">Sign In for Full Access</h3>
            </div>

            <button
                id="closeLoginModal"
                class="text-white">
                <i class="ri-close-line"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
            <div class="flex items-start mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-500 mt-0.5 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div>
                    <p class="text-gray-700 font-medium">To save favorite rooms and access all features:</p>
                    <ul class="list-disc list-inside text-gray-600 mt-2 space-y-1 pl-4">
                        <li>Save unlimited favorite rooms</li>
                        <li>Get personalized recommendations</li>
                        <li>Access booking history</li>
                        <li>Receive exclusive member deals</li>
                    </ul>
                </div>
            </div>

            <div class="bg-blue-50 p-4 rounded-lg">
                <div class="flex">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-800 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9z" clip-rule="evenodd" />
                    </svg>
                    <p class="text-sm text-blue-800">New to our site? Creating an account only takes 30 seconds!</p>
                </div>
            </div>

            <div class="flex flex-col space-y-2 my-3">
                <a
                    href="../User/user_signin.php"
                    class="px-6 py-2.5 bg-amber-500 text-white rounded-md hover:from-indigo-700 hover:to-purple-700 transition-colors font-medium text-center shadow-sm select-none">
                    Sign In
                </a>
                <a
                    href="../User/user_signup.php"
                    class="text-xs text-center text-blue-900 hover:text-blue-800 hover:underline transition-colors">
                    Don't have an account? Register
                </a>
            </div>

            <!-- Footer matching the site's advisory note style -->
            <div class="bg-gray-50 px-6 py-3 border-t border-gray-200">
                <p class="text-xs text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Remember to review any travel advisories before booking.
                </p>
            </div>
        </div>
    </div>
</div>