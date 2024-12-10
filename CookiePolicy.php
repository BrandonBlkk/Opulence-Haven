<?php

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opulence Haven</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/3.5.0/remixicon.css" integrity="sha512-HXXR0l2yMwHDrDyxJbrMD9eLvPe3z3qL3PPeozNTsiHJEENxx8DH2CxmV05iwG0dwoz5n4gQZQyYLUNt1Wdgfg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="CSS/output.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="CSS/input.css?v=<?php echo time(); ?>">
</head>

<body class="relative">
    <?php
    include('./includes/Navbar.php');
    ?>

    <main class="py-4">
        <div class="flex flex-col md:flex-row max-w-[1310px] mx-auto gap-3 px-3">
            <!-- Left: Cookie Policy Information -->
            <div class="w-full md:w-2/3">
                <h2 class="text-2xl font-bold text-blue-900 mb-4">Cookie Policy</h2>
                <p class="text-gray-600 leading-relaxed mb-4">
                    The Opulence Haven Hotel Booking website uses cookies to enhance your booking experience, streamline navigation, and provide tailored recommendations. Cookies are small text files stored on your device that allow us to recognize your preferences, improve functionality, and deliver personalized services. By using this website, you consent to our cookie usage policy.
                </p>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Why We Use Cookies:</h3>
                <ul class="list-disc pl-6 text-gray-600 mb-4">
                    <li><strong>Essential Cookies:</strong> Enable secure access to booking details, manage reservations, and support core website functionalities like login and session management.</li>
                    <li><strong>Performance Cookies:</strong> Monitor website performance, track loading times, and analyze user interactions to ensure a seamless booking experience.</li>
                    <li><strong>Functional Cookies:</strong> Save preferences such as room choices, check-in and check-out dates, and special requests for returning visitors.</li>
                    <li><strong>Marketing Cookies:</strong> Offer personalized promotions, targeted discounts, and information about nearby events, dining, and entertainment options.</li>
                    <li><strong>Analytics Cookies:</strong> Provide insights into visitor behavior, including frequently viewed destinations, to help improve website design and user experience.</li>
                </ul>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Data Protection at Opulence Haven:</h3>
                <p class="text-gray-600 mb-4">
                    Our cookies comply with strict security protocols to safeguard your data. We never store sensitive information, such as credit card details or personal identification, in cookies. All data is handled in line with GDPR and other relevant privacy laws.
                </p>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Benefits of Cookies:</h3>
                <p class="text-gray-600 mb-4">
                    Cookies help you enjoy a faster and more efficient booking process by automatically filling out forms, displaying relevant deals, and reducing search times. They also allow us to remember your preferences, ensuring a consistent and enjoyable user experience during every visit.
                </p>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">How to Manage Your Preferences:</h3>
                <p class="text-gray-600 mb-4">
                    Manage or disable cookies at any time through your browser settings. Note that disabling cookies may limit website features, such as access to your booking history, personalized offers, and tailored search results.
                </p>
            </div>

            <!-- Right: Hotel Location -->
            <div class="w-full md:w-1/3 sticky top-6 h-fit">
                <h2 class="text-2xl font-bold text-blue-900 mb-4">Hotel Location</h2>
                <p class="text-gray-600 mb-2">
                    <span class="font-semibold">Name:</span> Opulence Haven Hotel
                </p>
                <p class="text-gray-600 mb-2">
                    <span class="font-semibold">Address:</span> 459 Pyay Road, Kamayut Township , 11041, Yangon, Myanmar
                </p>
                <p class="text-gray-600 mb-4">
                    <span class="font-semibold">Email:</span> mail@opulence.com
                </p>
                <p class="text-gray-600 mb-4">
                    <span class="font-semibold">Contact:</span> +1 123-456-7890
                </p>
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3819.126600855232!2d96.12904707492125!3d16.82007438397361!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x30c1eb4aa7a892d9%3A0xd6483ad95ecee1ef!2s459%20Pyay%20Rd%2C%20Yangon%2011041!5e0!3m2!1sen!2smm!4v1733504156110!5m2!1sen!2smm"
                    width="100%"
                    height="200"
                    style="border:0;"
                    allowfullscreen=""
                    loading="lazy"
                    class="rounded-md">
                </iframe>
            </div>
        </div>
    </main>

    <!-- MoveUp Btn -->
    <?php
    include('./includes/MoveUpBtn.php');
    include('./includes/Footer.php');
    ?>
    <script src="//unpkg.com/alpinejs" defer></script>
    <script src="./JS/index.js"></script>
</body>

</html>