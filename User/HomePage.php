<?php
session_start();
include('../config/dbConnection.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}
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
    include('../includes/Cookies.php');
    ?>

    <main class="pb-4">
        <div class="select-none">
            <img src="../UserImages/hotel-room-5858069_1280.jpg"
                class="w-full h-full lg:max-h-[620px] object-cover object-bottom clip-custom"
                alt="Image">
        </div>

        <div class="flex flex-col items-center justify-center py-16 px-3 text-center">
            <h1 class="text-2xl sm:text-4xl mb-5 text-blue-900 font-semibold">Get away at the best price</h1>
            <p class="text-slate-600 mb-3">Where, when, with anyone you want</p>
            <a href="#" class="flex items-center gap-1 group">
                <p class="group-hover:underline group-hover:underline-offset-2">Discover all offers</p>
                <i class="ri-arrow-right-line text-xl group-hover:translate-x-2 transition-all duration-200"></i>
            </a>
        </div>
        <section class="grid grid-cols-1 md:grid-cols-3 gap-4 px-4 max-w-[1310px] mx-auto">
            <!-- Card 1 -->
            <a href="#" class="block w-full md:max-w-[450px] mx-auto group">
                <div class="h-auto sm:h-[280px] select-none">
                    <img src="../UserImages/hotel-room-5858069_1280.jpg" class="w-full h-full object-cover rounded-sm" alt="Image">
                </div>
                <div>
                    <h1 class="text-slate-700 font-semibold mt-3">Black Friday Limited Offer</h1>
                    <p class="text-slate-600 mt-2">
                        Book on ALL.com to get 3x Reward points for your stay, across Europe and North Africa.
                        Choose from a variety of brands, and find your dream destination for your perfect trip.
                    </p>
                    <div class="flex items-center text-amber-500 group mt-1">
                        <span class="group-hover:text-amber-600 transition-all duration-200">Book now</span>
                        <i class="ri-arrow-right-line text-xl group-hover:text-amber-600 group-hover:translate-x-2 transition-all duration-200"></i>
                    </div>
                </div>
            </a>

            <!-- Card 2 -->
            <a href="#" class="block w-full md:max-w-[450px] mx-auto group">
                <div class="h-auto sm:h-[280px] select-none">
                    <img src="../UserImages/FORMAT-16-9E---1920-X-1080-PX (1)_3by2.webp" class="w-full h-full object-cover rounded-sm" alt="Image">
                </div>
                <div>
                    <h1 class="text-slate-700 font-semibold mt-3">Life in balance: Breakfast at Opulence</h1>
                    <p class="text-slate-600 mt-2">
                        When there's an opportunity to indulge while enjoying a variety of choices,
                        ensuring the energy needed for the day ahead. Perfect for business or family trips.
                    </p>
                    <div class="flex items-center text-amber-500 group mt-1">
                        <span class="group-hover:text-amber-600 transition-all duration-200">Book now</span>
                        <i class="ri-arrow-right-line text-xl group-hover:text-amber-600 group-hover:translate-x-2 transition-all duration-200"></i>
                    </div>
                </div>
            </a>

            <!-- Card 3 -->
            <a href="../Store/Store.php" class="block w-full md:max-w-[450px] mx-auto group">
                <div class="h-auto sm:h-[280px] select-none">
                    <img src="../UserImages/Standard-Room-model.jpg" class="w-full h-full object-cover rounded-sm" alt="Image">
                </div>
                <div>
                    <h1 class="text-slate-700 font-semibold mt-3">Opulence Store - Black Friday</h1>
                    <p class="text-slate-600 mt-2">
                        25% off on Opulence bedding collection. End the year softly with Opulence bedding for cozy,
                        hotel-like nights. Pillows, duvets, mattresses, and much more!
                    </p>
                    <div class="flex items-center text-amber-500 group mt-1">
                        <span class="group-hover:text-amber-600 transition-all duration-200">Shop now</span>
                        <i class="ri-arrow-right-line text-xl group-hover:text-amber-600 group-hover:translate-x-2 transition-all duration-200"></i>
                    </div>
                </div>
            </a>
        </section>
        <div class="flex flex-col items-center justify-center py-16 px-3 text-center">
            <p class="text-slate-600 mb-3">YOUR OPULENCE</p>
            <h1 class="text-2xl sm:text-4xl mb-5 text-blue-900 font-semibold">Inspirational Hotels</h1>
            <p class="text-slate-600 mb-3">Intuitive stays in destination hotels</p>
        </div>
        <section class="grid grid-cols-1 md:grid-cols-3 gap-4 px-4 pb-10 border-b max-w-[1310px] mx-auto">
            <!-- Card 1 -->
            <div class="block w-full md:max-w-[450px] mx-auto group">
                <div class="h-auto sm:h-[280px] select-none">
                    <img src="../UserImages/family-6475821_1280.jpg" class="w-full h-full object-cover rounded-sm" alt="Image">
                </div>
                <div>
                    <h1 class="text-slate-700 font-semibold mt-3">Family</h1>
                    <p class="text-slate-600 mt-2">
                        Time spent with loved ones is time well spent, so make every
                        moment matter and create memories with your family.
                    </p>
                </div>
            </div>

            <!-- Card 2 -->
            <div class="block w-full md:max-w-[450px] mx-auto group">
                <div class="h-auto sm:h-[280px] select-none">
                    <img src="../UserImages/hand-massage-7440712_1280.jpg" class="w-full h-full object-cover rounded-sm" alt="Image">
                </div>
                <div>
                    <h1 class="text-slate-700 font-semibold mt-3">Wellness</h1>
                    <p class="text-slate-600 mt-2">
                        Take time to rest and relax. When you’re on top of your game,
                        your business and personal lives thrive too.
                    </p>
                </div>
            </div>

            <!-- Card 3 -->
            <div class="block w-full md:max-w-[450px] mx-auto group">
                <div class="h-auto sm:h-[280px] select-none">
                    <img src="../UserImages/business.webp" class="w-full h-full object-cover rounded-sm" alt="Image">
                </div>
                <div>
                    <h1 class="text-slate-700 font-semibold mt-3">Business</h1>
                    <p class="text-slate-600 mt-2">
                        Blend business and leisure for a flawless stay – and when work’s done,
                        it’s time to bond with family or friends.
                    </p>
                </div>
            </div>
        </section>

        <section class="p-4 py-10 max-w-[1310px] mx-auto flex flex-col md:flex-row gap-5">
            <div class="flex-1 select-none">
                <img src="../UserImages/modern-highrise-building.jpg" class="w-full h-full sm:h-[600px] object-cover rounded-sm" alt="Hotel Image">
            </div>
            <div class="flex-1 flex flex-col justify-between gap-3">
                <div class="flex flex-col">
                    <h1 class="text-2xl sm:text-4xl mb-5 text-blue-900 font-semibold">The world of Opulence</h1>
                    <p class="text-slate-600 mb-5">
                        Take your pick of distinctive Opulence experiences and enjoy cherished moments in unforgettable locations.
                        In your own time, make space for the essentials of life at our hotels, resorts, suites, and residences.
                    </p>
                    <a href="#" class="bg-amber-500 rounded-sm hover:bg-amber-600 text-white font-semibold text-center py-2 px-4 select-none transition-colors duration-300 self-start sm:self-end">
                        Read more
                    </a>
                </div>
                <div>
                    <iframe
                        class="gmap_iframe w-full h-64 sm:h-96 select-none"
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3819.126600855232!2d96.12904707492125!3d16.82007438397361!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x30c1eb4aa7a892d9%3A0xd6483ad95ecee1ef!2s459%20Pyay%20Rd%2C%20Yangon%2011041!5e0!3m2!1sen!2smm!4v1733504156110!5m2!1sen!2smm"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>

        </section>
    </main>

    <!-- MoveUp Btn -->
    <?php
    include('../includes/MoveUpBtn.php');
    include('../includes/Footer.php');
    ?>
    <script src="//unpkg.com/alpinejs" defer></script>
    <script src="../JS/index.js"></script>
</body>

</html>