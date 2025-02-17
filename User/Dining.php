<?php
session_start();
include('../config/dbConnection.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

$session_userID = (!empty($_SESSION["UserID"]) ? $_SESSION["UserID"] : null);

if ($session_userID) {
    $getUserDetails = "SELECT * FROM usertb WHERE UserID = '$session_userID'";
    $getUserDetailsResult = mysqli_query($connect, $getUserDetails);

    if ($getUserDetailsResult) {
        $userDetails = mysqli_fetch_assoc($getUserDetailsResult);
        $username = $userDetails['UserName'];
        $email = $userDetails['UserEmail'];
        $phone = $userDetails['UserPhone'];
    } else {
        echo "Error: " . mysqli_error($connect);
        exit();
    }
}

$alertMessage = '';
$reservationSuccess = false;

// Make Dining Reservation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reserve'])) {
    $date = mysqli_real_escape_string($connect, $_POST['date']);
    $time = mysqli_real_escape_string($connect, $_POST['time']);
    $guests = mysqli_real_escape_string($connect, $_POST['guests']);
    $specialrequests = mysqli_real_escape_string($connect, $_POST['specialrequests']);
    $name = mysqli_real_escape_string($connect, $_POST['name']);
    $email = mysqli_real_escape_string($connect, $_POST['email']);
    $phone = mysqli_real_escape_string($connect, $_POST['phone']);

    $reservationQuery = "INSERT INTO diningreservationtb (Date, Time, NumberOfGuests, SpecialRequest, Name, Email, PhoneNumber, UserID)
    VALUES ('$date', '$time', '$guests', '$specialrequests', '$name', '$email', '$phone', " . ($session_userID ? "'$session_userID'" : "NULL") . ")";

    if ($connect->query($reservationQuery)) {
        $reservationSuccess = true;
    } else {
        $alertMessage = "Failed to reserve the table. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opulence Haven</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../CSS/output.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../CSS/input.css?v=<?php echo time(); ?>">
    <!-- Swiper JS -->
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>

</head>

<body class="relative">
    <?php
    include('../includes/Navbar.php');
    include('../includes/Cookies.php');
    ?>

    <main class="pb-4">
        <div class="relative swiper-container">
            <!-- Swiper Wrapper -->
            <div class="swiper-wrapper">
                <div class="swiper-slide relative">
                    <img src="../UserImages/photo-1414235077428-338989a2e8c0.avif"
                        class="w-full h-full lg:max-h-[620px] object-cover object-center clip-custom select-none"
                        alt="Yet Another Room">
                    <div class="absolute inset-0 flex items-center justify-center sm:justify-start pl-0 sm:pl-12 bg-black bg-opacity-20 clip-custom">
                        <div class="text-center">
                            <h2 class="text-white text-4xl lg:text-6xl">Discover Gourmet Delights</h2>
                            <p class="text-white text-lg mt-4">Our menu features a blend of traditional and modern cuisine.</p>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide relative">
                    <img src="../UserImages/premium_photo-1724526853404-d3fdddb261a2.avif"
                        class="w-full h-full lg:max-h-[620px] object-cover object-center clip-custom select-none"
                        alt="Hotel Room">
                    <div class="absolute inset-0 flex items-center justify-center sm:justify-start pl-0 sm:pl-12 bg-black bg-opacity-20 clip-custom">
                        <div class="text-center">
                            <h2 class="text-white text-4xl lg:text-6xl">Indulge in Exquisite Dining</h2>
                            <p class="text-white text-lg mt-4">Experience a culinary journey with our world-class chefs.</p>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide relative">
                    <img src="../UserImages/photo-1558346489-19413928158b.avif"
                        class="w-full h-full lg:max-h-[620px] object-cover object-center clip-custom select-none"
                        alt="Yet Another Room">
                    <div class="absolute inset-0 flex items-center justify-center sm:justify-start pl-0 sm:pl-12 bg-black bg-opacity-20 clip-custom">
                        <div class="text-center">
                            <h2 class="text-white text-4xl lg:text-6xl">Savor the Flavors</h2>
                            <p class="text-white text-lg mt-4">Discover unique flavors crafted from locally sourced ingredients.</p>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide relative">
                    <img src="../UserImages/premium_photo-1723491285855-f1035c4c703c.avif"
                        class="w-full h-full lg:max-h-[620px] object-cover object-bottom clip-custom select-none"
                        alt="Another Room">
                    <div class="absolute inset-0 flex items-center justify-center sm:justify-start pl-0 sm:pl-12 bg-black bg-opacity-20 clip-custom">
                        <div class="text-center">
                            <h2 class="text-white text-4xl lg:text-6xl">Experience Culinary Excellence</h2>
                            <p class="text-white text-lg mt-4">Join us for an unforgettable dining experience in a luxurious setting.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const swiper = new Swiper('.swiper-container', {
                effect: 'fade',
                loop: true,
                autoplay: {
                    delay: 6000,
                    disableOnInteraction: false,
                },
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
                allowTouchMove: true,
            });
        });
    </script>

    <section id="fade-in-section" class="py-10 pb-16 px-5 sm:px-10 md:px-20 lg:px-40 xl:px-60 2xl:px-80 text-center">
        <h1 class="text-2xl mb-5 text-blue-900 font-semibold">Discover the Taste of Tradition</h1>
        <p class="text-2xl sm:text-3xl font-light">Experience the authentic flavors of our region with dishes inspired by local traditions and ingredients. Our chefs bring the essence of the destination to your plate, creating a dining experience that connects you to the culture and heritage of the area. Every bite tells a story, and every meal is a celebration of local cuisine.</p>
    </section>

    <section id="fade-in-section" class="flex flex-col py-0 sm:py-16 px-4 max-w-[1310px] mx-auto">
        <h1 class="text-slate-600 mb-10">DISCOVER THE ART OF DINING</h1>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pb-16 border-b">
            <!-- Card 1 -->
            <div class="block w-full group">
                <div class="h-auto sm:h-[380px] select-none">
                    <img src="../UserImages/photo-1565895405227-31cffbe0cf86.avif" class="w-full h-full object-cover rounded-sm" alt="Image">
                </div>
                <div>
                    <h1 class="text-slate-700 font-semibold mt-3">Gourmet Family Dining</h1>
                    <p class="text-slate-600 mt-2">
                        Savor exquisite flavors and heartwarming moments with your loved ones. Every dish is a masterpiece, crafted with the finest ingredients for an unforgettable dining experience in a cozy and elegant ambiance.
                    </p>
                </div>
            </div>

            <!-- Card 2 -->
            <div class="block w-full group">
                <div class="h-auto sm:h-[380px] select-none">
                    <img src="../UserImages/photo-1662982692115-743f9e716b98.avif" class="w-full h-full object-cover rounded-sm" alt="Image">
                </div>
                <div>
                    <h1 class="text-slate-700 font-semibold mt-3">Culinary Excellence</h1>
                    <p class="text-slate-600 mt-2">
                        Experience a world-class dining affair where artistry meets flavor. From live cooking performances to gourmet delicacies, every bite is a celebration of passion, sophistication, and indulgence.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="p-4 py-10 pb-16 sm:pb-24 max-w-[1310px] mx-auto flex flex-col-reverse md:flex-row gap-5">
        <div class="flex-1 flex flex-col justify-between gap-3" id="fade-in-section-top">
            <div class="flex flex-col">
                <h1 class="text-2xl sm:text-4xl mb-5 text-blue-900 font-semibold">Echoes of Culinary Excellence</h1>
                <p class="text-slate-600 mb-5">
                    Indulge in the legacy of fine dining, where every dish is a masterpiece crafted with passion and precision.
                    Guided by our celebrated chefs, guests embark on a journey through flavors that tell stories of tradition,
                    innovation, and artistry. From the first bite to the last, savor the extraordinary in an ambiance that
                    reflects the elegance and heritage of Opulence. The experience concludes with a curated dessert selection,
                    offering the perfect moment to reflect on the beauty and legacy of culinary excellence.
                </p>
                <p>459 Pyay Road, Kamayut Township , 11041, Yangon, Myanmar</p>
            </div>
        </div>
        <div class="flex-1 select-none" id="image-section">
            <img src="../UserImages/photo-1532250327408-9bd6e0ce2c49.avif" class="w-full h-full object-cover rounded-sm" alt="Hotel Image">
        </div>
    </section>

    <!-- Swiper -->
    <div class="swiper mySwiper w-full max-w-[1310px] mx-auto pb-16">
        <h1 class="text-slate-600 mb-10 uppercase px-4 sm:px-0">Explore Culinary Masterpieces</h1>

        <div class="swiper-wrapper select-none">
            <div class="swiper-slide">
                <img src="../UserImages/premium_photo-1661443680197-6509c9ca0e22.avif" class="w-full h-[380px] object-cover" alt="Hotel Room">
            </div>
            <div class="swiper-slide">
                <img src="../UserImages/photo-1662982696492-057328dce48b.avif" class="w-full h-[380px] object-cover" alt="Breakfast">
            </div>
            <div class="swiper-slide">
                <img src="../UserImages/premium_photo-1661470225464-735636d873f1.avif" class="w-full h-[380px] object-cover" alt="Opulence Store">
            </div>
            <div class="swiper-slide">
                <img src="../UserImages/photo-1625862220431-f8d70c6addda.avif" class="w-full h-[380px] object-cover" alt="Hotel Room">
            </div>
            <div class="swiper-slide">
                <img src="../UserImages/photo-1625860927329-df5602da7729.avif" class="w-full h-[380px] object-cover" alt="Breakfast">
            </div>
            <div class="swiper-slide">
                <img src="../UserImages/photo-1625862577363-1c5e5a0f0e43.avif" class="w-full h-[380px] object-cover" alt="Opulence Store">
            </div>
        </div>
        <!-- Navigation Buttons -->
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
    </div>

    <!-- Initialize Swiper -->
    <script>
        var swiper = new Swiper(".mySwiper", {
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
            slidesPerView: 1,
            spaceBetween: 10,
            breakpoints: {
                640: {
                    slidesPerView: 1
                },
                768: {
                    slidesPerView: 2
                },
                1024: {
                    slidesPerView: 3
                },
            },
        });
    </script>
</body>

</html>


<!-- MoveUp Btn -->
<?php
include('../includes/MoveUpBtn.php');
include('../includes/Footer.php');
?>
<script src="//unpkg.com/alpinejs" defer></script>
<script src="https://unpkg.com/scrollreveal"></script>
<script src="../JS/index.js"></script>
</body>

</html>