<footer class="bg-gray-100 min-w-[380px] text-gray-400 flex flex-col justify-center gap-10">
    <section class="max-w-[1400px] mx-auto">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-5 pt-20 px-5 sm:px-10">
            <div>
                <h1 class="text-4xl text-indigo-400 font-semibold mb-5 select-none"><img src="../UserImages/Screenshot_2024-11-29_201534-removebg-preview.png" class="w-28" alt="Logo"></h1>
                <p class="text-sm text-slate-700">Discover our commitment to providing exceptional stays, comfort, and unforgettable experiences.</p>
                <ul class="flex items-center gap-7 text-xl mt-3">
                    <li class="hover:text-rose-700 transition-colors duration-200">
                        <a href="#"><i class="ri-instagram-line"></i></a>
                    </li>
                    <li class="hover:text-blue-700 transition-colors duration-200">
                        <a href="#"><i class="ri-facebook-circle-fill"></i></a>
                    </li>
                    <li class="hover:text-gray-700 transition-colors duration-200">
                        <a href="#"><i class="ri-twitter-x-line"></i></a>
                    </li>
                    <li class="hover:text-red-700 transition-colors duration-200">
                        <a href="#"><i class="ri-youtube-fill"></i></a>
                    </li>
                </ul>

                <!-- Google Translate -->
                <div id="google_translate_element" class="mt-3"></div>
                <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElement"></script>

                <script type="text/javascript">
                    function googleTranslateElement() {
                        new google.translate.TranslateElement({
                            pageLanguage: 'en'
                        }, 'google_translate_element');
                    }
                </script>
            </div>
            <div>
                <div>
                    <h1 class="text-lg font-semibold text-black mb-3">Information</h1>
                    <ul class="text-sm flex flex-col gap-1 text-slate-700">
                        <li>
                            <a class="hover:underline" href="AboutUs.php">About Us</a>
                        </li>
                        <li>
                            <a class="hover:underline" href="../User/Contact.php">Contact</a>
                        </li>
                        <li>
                            <a class="hover:underline" href="../Policies/TermsAndConditions.php">Terms & Conditions</a>
                        </li>
                        <li>
                            <a class="hover:underline" href="../Policies/CookiePolicy.php">Cookies Policy</a>
                        </li>
                        <li>
                            <a class="hover:underline" href="../Policies/TermOfUse.php">Terms of Use</a>
                        </li>
                        <li>
                            <a class="hover:underline" href="../Policies/PrivacyPolicy.php">Privacy Policy</a>
                        </li>
                        <li>
                            <a class="hover:underline" href="Support.php">Customer Support</a>
                        </li>
                    </ul>
                </div>

            </div>
            <div>
                <h1 class="text-lg font-semibold text-black mb-3">Discover</h1>
                <ul class="text-sm flex flex-col gap-1 text-slate-700">
                    <li>
                        <a class="hover:underline" href="Offers.php">Special Offers</a>
                    </li>
                    <li>
                        <a class="hover:underline" href="Rooms.php">Luxury Rooms</a>
                    </li>
                    <li>
                        <a class="hover:underline" href="NearbyAttractions.php">Nearby Attractions</a>
                    </li>
                    <li>
                        <a class="hover:underline" href="../Store/RoomEssentials.php">Room Essentials</a>
                    </li>
                    <li>
                        <a class="hover:underline" href="../Store/Toiletries&Spa.php">Toiletries & Spa</a>
                    </li>
                    <li>
                        <a class="hover:underline" href="FAQ.php">Frequently Asked Questions</a>
                    </li>
                </ul>
            </div>
            <div>
                <h1 class="text-lg font-semibold text-black mb-3">Locate Us</h1>
                <ul class="text-sm flex flex-col gap-1 text-slate-700">
                    <li>459 Pyay Road, Kamayut Township , 11041</li>
                    <li>Yangon, Myanmar</li>
                    <li>+1 123-456-7890</li>
                    <li>mail@opulence.com</li>
                </ul>
            </div>
        </div>

        <!-- Newsletter -->
        <div class="flex justify-center px-5 md:px-8 py-14">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-20">
                <div class="flex gap-10">
                    <div>
                        <i class="ri-archive-line text-2xl text-amber-500 select-none"></i>
                        <h1 class="font-semibold">60 DAYS RETURN</h1>
                        <p class="text-sm">Return within 60 days, hassle-free.</p>
                    </div>
                    <div>
                        <i class="ri-truck-line text-2xl text-amber-500 select-none"></i>
                        <h1 class="font-semibold">FREE SHIPPING</h1>
                        <p class="text-sm">Free shipping for loyal customers.</p>
                    </div>
                </div>
                <div class="border-l-0 lg:border-l pl-0 lg:pl-16">
                    <h1 class="text-2xl font-semibold mb-3">Newsletter Sign Up</h1>
                    <!-- Newsletter Form -->
                    <form id="newsletterForm" class="flex flex-col gap-6 sm:gap-0 sm:flex-row" method="POST" action="../User/Subscribe.php">
                        <input class="border w-full p-2 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out" type="email" id="email" name="email" placeholder="Your email please" required>
                        <input class="bg-black py-2 px-5 text-white select-none cursor-pointer" type="submit" value="SUBSCRIBE">
                    </form>

                    <!-- Loader -->
                    <?php
                    include('../includes/Loader.php');
                    ?>

                    <script>
                        document.getElementById('newsletterForm').addEventListener('submit', function(e) {
                            e.preventDefault(); // Prevent the default form submission

                            // Show the loader
                            document.getElementById('loader').classList.remove('hidden');
                            document.getElementById('loader').classList.add('flex');

                            // Get form data
                            const formData = new FormData(this);

                            // Send the form data using AJAX
                            fetch('../Mail/Subscribe.php', {
                                    method: 'POST',
                                    body: formData
                                })
                                .then(response => response.text()) // Parse the response as text
                                .then(data => {
                                    // Hide the loader
                                    document.getElementById('loader').classList.remove('flex');
                                    document.getElementById('loader').classList.add('hidden');

                                    // Redirect back to the previous page
                                    document.getElementById('email').value = '';
                                })
                                .catch(error => {
                                    // Hide the loader and show an error message
                                    document.getElementById('loader').classList.remove('flex');
                                    document.getElementById('loader').classList.add('hidden');
                                    alert('There was an error sending the email. Please try again later.');
                                    console.error('Error:', error);
                                });
                        });
                    </script>
                </div>
            </div>
        </div>

        <!-- <section class="p-4 max-w-3xl mx-auto">
            <div class="flex justify-between cursor-pointer select-none">
                <div class="w-20">
                    <img class="w-full h-full" src="../UserImages/north-face.avif" alt="Image">
                </div>
                <div class="w-20">
                    <img class="w-full h-full" src="../UserImages/north-face.avif" alt="Image">
                </div>
                <div class="w-20">
                    <img class="w-full h-full" src="../UserImages/north-face.avif" alt="Image">
                </div>
                <div class="w-20">
                    <img class="w-full h-full" src="../UserImages/north-face.avif" alt="Image">
                </div>
                <div class="w-20">
                    <img class="w-full h-full" src="../UserImages/north-face.avif" alt="Image">
                </div>
                <div class="w-20">
                    <img class="w-full h-full" src="../UserImages/north-face.avif" alt="Image">
                </div>
            </div>
        </section> -->

        <div class="flex flex-col items-center border-t border-gray-200 gap-3 sm:gap-0 py-10 px-10">
            <p class="text-xs text-slate-700 mb-1">OpulenceHaven.com is part of Booking Holdings Inc., the world leader in online travel and related services.</p>
            <p class="text-xs text-slate-700">Copyright © <span id="year"></span> OpulenceHaven.com™. All rights reserved.</p>
        </div>
    </section>
</footer>