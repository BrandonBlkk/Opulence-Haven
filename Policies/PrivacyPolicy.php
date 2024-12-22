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
    <link rel="stylesheet" href="../CSS/output.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../CSS/input.css?v=<?php echo time(); ?>">
</head>

<body class="relative">
    <?php
    include('../includes/Navbar.php');
    ?>

    <main class=" px-3 max-w-[1310px] mx-auto py-4">
        <!-- Introduction -->
        <section>
            <h2 class="text-2xl font-bold text-blue-900 mb-4">Welcome to OPULENCE's Privacy Policy</h2>
            <p class="mb-4 text-gray-700">
                This Privacy Policy explains how OPULENCE collects, uses, and protects your personal information when you use our services.
                Your privacy is of utmost importance to us, and we are committed to safeguarding your data.
            </p>
        </section>

        <!-- Data Collection -->
        <section>
            <h3 class="text-lg font-semibold text-gray-800 mb-2">What Data We Collect</h3>
            <ul class="list-disc list-inside text-gray-700 space-y-2">
                <li>Personal Information: Name, email address, phone number, and billing details.</li>
                <li>Account Data: Login credentials, profile information, and preferences.</li>
                <li>Usage Data: Pages visited, time spent, and interaction with features.</li>
                <li>Device Information: IP address, browser type, and operating system.</li>
            </ul>
        </section>

        <!-- Data Usage -->
        <section class="mt-2">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">How We Use Your Data</h3>
            <p class="text-gray-700">
                OPULENCE uses the collected data to provide a personalized shopping experience, improve website functionality, process orders,
                and communicate promotions or updates.
            </p>
            <ul class="list-disc list-inside text-gray-700 space-y-2 mt-4">
                <li>Processing transactions and delivering purchases.</li>
                <li>Improving website performance and customer support.</li>
                <li>Sending promotional emails or service updates (with your consent).</li>
                <li>Ensuring security and preventing fraud.</li>
            </ul>
        </section>

        <!-- Data Sharing -->
        <section class="mt-2">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Data Sharing & Disclosure</h3>
            <p class="text-gray-700">
                OPULENCE does not sell your personal data. However, we may share your information with trusted third parties under the following circumstances:
            </p>
            <ul class="list-disc list-inside text-gray-700 space-y-2 mt-4">
                <li>Payment processors to complete transactions.</li>
                <li>Service providers supporting our operations (e.g., hosting and analytics).</li>
                <li>Compliance with legal obligations or protection against fraud.</li>
            </ul>
        </section>

        <!-- Data Storage -->
        <section class="mt-2">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">How We Store & Protect Your Data</h3>
            <p class="text-gray-700">
                OPULENCE implements robust security measures to protect your data. All sensitive data is encrypted and stored on secure servers.
                Retention periods are determined based on legal requirements and operational needs.
            </p>
        </section>

        <!-- User Rights -->
        <section class="mt-2">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Your Rights</h3>
            <p class="text-gray-700">
                As a user, you have rights regarding your personal data:
            </p>
            <ul class="list-disc list-inside text-gray-700 space-y-2 mt-4">
                <li>Access: Request a copy of the data we hold about you.</li>
                <li>Correction: Update inaccurate or incomplete information.</li>
                <li>Deletion: Request data removal in certain circumstances.</li>
                <li>Opt-Out: Unsubscribe from promotional communications.</li>
            </ul>
        </section>

        <!-- Cookies -->
        <section class="mt-2">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Cookies</h3>
            <p class="text-gray-700">
                OPULENCE uses cookies to enhance your browsing experience. Cookies are small files stored on your device that help us
                understand user preferences and improve website performance. You can manage cookie settings in your browser.
            </p>
        </section>

        <!-- Contact Information -->
        <section class="mt-2">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Contact Us</h3>
            <p class="text-gray-700">
                If you have questions about this Privacy Policy or wish to exercise your rights, please contact us at:
            </p>
            <p class="mt-4">
                <span class="block">Email: <a href="mailto:mail@opulence.com" class="text-amber-500 hover:underline">mail@opulence.com</a></span>
                <span class="block">Phone: +1 234 567 890</span>
                <span class="block">Address: 459 Pyay Road, Kamayut Township , 11041, Yangon, Myanmar</span>
            </p>
        </section>
    </main>

    <?php
    include('../includes/Footer.php');
    ?>
    <script src="//unpkg.com/alpinejs" defer></script>
    <script src="../JS/index.js"></script>
    <script src="../JS/auth.js"></script>
</body>

</html>