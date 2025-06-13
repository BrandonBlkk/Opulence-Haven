<?php
session_start();
include('../config/dbConnection.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

$userID = (!empty($_SESSION["UserID"]) ? $_SESSION["UserID"] : null);

// Check if there's a welcome message to display
if (isset($_SESSION['welcome_message']) && isset($_SESSION['UserName'])) {
    $welcomeMessage = $_SESSION['welcome_message'];
    $username = $_SESSION['UserName'];
    unset($_SESSION['welcome_message']); //Show the message only once
}

// Update membership
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['UserID'])) {

    $stmt = $connect->prepare("UPDATE usertb SET Membership = 1, PointsBalance = 500 WHERE UserID = ?");
    $stmt->bind_param("s", $userID);

    if ($stmt->execute()) {
        echo "Membership updated successfully.";
    } else {
        echo "Failed to update membership.";
    }

    $stmt->close();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
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

    <?php
    if ($userID) {
        $user = "SELECT * FROM usertb WHERE UserID = '$userID'";
        $result = $connect->query($user);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $Membership = $row['Membership'] ?? null;
        } else {
            $Membership = null; // No user found
        }
    } else {
        $Membership = null; // No user signed in
    }
    ?>

    <?php if ($Membership == 0 && $userID): ?>
        <!-- Side Popup Container -->
        <div id="membershipPopup" class="fixed right-[-320px] top-1/2 -translate-y-1/2 w-80 bg-white shadow-lg rounded-l-md z-50 transition-all duration-300 ease-out">
            <!-- Header -->
            <div class="flex justify-between items-center bg-orange-500 text-white p-3 rounded-tl-md">
                <h3 class="font-bold text-lg">Unlock Rewards!</h3>
                <button id="closePopup" type="button" class="text-xl hover:text-gray-200">×</button>
            </div>

            <!-- Body with Form -->
            <form id="membershipForm" class="p-4">
                <input type="hidden" id="userID" value="<?php echo $userID; ?>">
                <p class="text-gray-600 mb-4">Join our free membership and earn 500 bonus points today.</p>

                <div class="mb-4">
                    <label for="newsletterOptIn" class="flex items-center">
                        <input type="checkbox" id="newsletterOptIn" name="newsletterOptIn" class="mr-2">
                        <span class="text-sm text-gray-600">Receive exclusive offers via email</span>
                    </label>
                </div>

                <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-white py-2 px-4 rounded-md transition-colors">
                    Join Now
                </button>
            </form>

            <!-- Add this inside #membershipPopup after the form -->
            <div id="confettiSuccess" class="hidden text-center relative overflow-hidden p-4">
                <div class="mx-auto relative flex items-center justify-center h-16 w-16 z-10">
                    <!-- Background Circle -->
                    <div class="absolute h-16 w-16 rounded-full"></div>

                    <!-- Animated Border -->
                    <svg class="absolute h-16 w-16 -rotate-90" viewBox="0 0 100 100">
                        <circle
                            cx="50"
                            cy="50"
                            r="45"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="4"
                            stroke-linecap="round"
                            stroke-dasharray="283"
                            stroke-dashoffset="283"
                            class="text-green-500 animate-[borderFill_0.6s_ease-out_0.3s_1_forwards]" />
                    </svg>

                    <!-- Checkmark -->
                    <svg class="h-8 w-8 text-green-600 opacity-0 animate-[appearAndSpin_0.5s_ease-out_0.9s_1_forwards]"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        id="success-checkmark">
                        <path stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>

                <h3 class="mt-4 text-lg font-semibold text-gray-800 relative z-20">Membership Activated!</h3>
                <p class="mt-2 text-sm text-amber-500 relative z-20">You earned 500 bonus points!</p>
            </div>

            <style>
                @keyframes borderFill {
                    0% {
                        stroke-dashoffset: 283;
                    }

                    100% {
                        stroke-dashoffset: 0;
                    }
                }

                @keyframes appearAndSpin {
                    0% {
                        opacity: 0;
                        transform: scale(0.5) rotate(-90deg);
                    }

                    70% {
                        opacity: 1;
                        transform: scale(1.1) rotate(10deg);
                    }

                    100% {
                        opacity: 1;
                        transform: scale(1) rotate(0deg);
                    }
                }

                .confetti {
                    position: fixed;
                    width: 10px;
                    height: 10px;
                    opacity: 0;
                    animation: confetti-fall 3s ease-in-out forwards;
                    top: -10px;
                    z-index: 50;
                }

                @keyframes confetti-fall {
                    0% {
                        transform: translateY(0) rotate(0deg);
                        opacity: 1;
                    }

                    100% {
                        transform: translateY(100vh) rotate(360deg);
                        opacity: 0;
                    }
                }
            </style>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const userID = document.getElementById('userID');
                const membershipPopup = document.getElementById('membershipPopup');
                const membershipForm = document.getElementById('membershipForm');
                const closeBtn = document.getElementById('closePopup');

                // Auto-show popup after 3 seconds
                setTimeout(() => {
                    membershipPopup.classList.replace('right-[-320px]', 'right-0');
                }, 3000);

                // Close popup
                closeBtn.addEventListener('click', () => {
                    membershipPopup.classList.replace('right-0', 'right-[-320px]');
                });

                // Submit form and update membership
                membershipForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const formData = new FormData();
                    formData.append('UserID', userID.value);

                    fetch('../User/HomePage.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(res => res.text())
                        .then(data => {
                            // Hide form and show animation
                            form.classList.add('hidden');
                            document.getElementById('confettiSuccess').classList.remove('hidden');

                            // Start confetti
                            createConfetti();

                            // Auto-close popup after 4 seconds
                            setTimeout(() => {
                                membershipPopup.classList.replace('right-0', 'right-[-320px]');
                            }, 4000);
                        })
                        .catch(err => {
                            alert('Error: ' + err);
                        });
                });
            });

            // Confetti Generator
            function createConfetti() {
                const colors = ['#f44336', '#e91e63', '#9c27b0', '#673ab7', '#3f51b5', '#2196f3', '#4CAF50', '#FF9800', '#FFC107'];
                for (let i = 0; i < 50; i++) {
                    const confetti = document.createElement('div');
                    confetti.className = 'confetti';
                    confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                    confetti.style.width = `${Math.random() * 8 + 4}px`;
                    confetti.style.height = `${Math.random() * 8 + 4}px`;
                    confetti.style.left = `${Math.random() * 100}%`;
                    confetti.style.animationDuration = `${Math.random() * 2 + 2}s`;
                    confetti.style.animationDelay = `${Math.random() * 0.5}s`;
                    confetti.style.borderRadius = Math.random() > 0.5 ? '50%' : '0';
                    document.body.appendChild(confetti);
                    confetti.addEventListener('animationend', () => confetti.remove());
                }
            }
        </script>
    <?php endif; ?>

    <!-- Welcome message -->
    <?php if (isset($welcomeMessage)): ?>
        <div id="welcomeAlert" class="fixed -top-1 opacity-0 right-3 z-50 transition-all duration-200">
            <div class="flex items-center gap-3 p-3 rounded-lg shadow-lg bg-white backdrop-blur-sm border border-gray-200">
                <a href="../User/HomePage.php">
                    <img src="../UserImages/Screenshot_2024-11-29_201534-removebg-preview.png" class="w-16 select-none" alt="Logo">
                </a>
                <div>
                    <p class="font-medium text-gray-800"><?php echo htmlspecialchars($welcomeMessage); ?> to <span class="font-bold text-amber-600">Opulence Haven</span></p>
                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($username); ?></p>
                </div>
                <button onclick="closeWelcomeAlert()" class="ml-2 text-gray-400 hover:text-amber-600 transition-colors">
                    <i class="ri-close-line text-lg"></i>
                </button>
            </div>
        </div>

        <script>
            // Show welcome alert with animation
            const welcomeAlert = document.getElementById('welcomeAlert');

            // Trigger the animation after a small delay to allow DOM to render
            setTimeout(() => {
                welcomeAlert.classList.remove("-top-1", "opacity-0");
                welcomeAlert.classList.add("opacity-100", "top-3");

                // Hide after 5 seconds
                setTimeout(() => {
                    welcomeAlert.classList.add("translate-x-full", "-right-full");
                    setTimeout(() => welcomeAlert.remove(), 200);
                }, 5000);
            }, 100);

            function closeWelcomeAlert() {
                welcomeAlert.classList.add("translate-x-full", "-right-full");
                setTimeout(() => welcomeAlert.remove(), 200);
            }
        </script>
    <?php endif; ?>

    <main class="pb-4">
        <div class="relative swiper-container">
            <!-- Swiper Wrapper -->
            <div class="swiper-wrapper">
                <div class="swiper-slide">
                    <img src="../UserImages/hotel-room-5858069_1280.jpg"
                        class="w-full h-full lg:max-h-[620px] object-cover object-bottom clip-custom select-none"
                        alt="Hotel Room">
                </div>
                <div class="swiper-slide">
                    <img src="../UserImages/slide_image_2.jpg"
                        class="w-full h-full lg:max-h-[620px] object-cover object-bottom clip-custom select-none"
                        alt="Another Room">
                </div>
                <div class="swiper-slide">
                    <img src="../UserImages/slide_image_3.jpg"
                        class="w-full h-full lg:max-h-[620px] object-cover object-bottom clip-custom select-none"
                        alt="Yet Another Room">
                </div>
                <div class="swiper-slide">
                    <img src="../UserImages/slide_image_4.jpg"
                        class="w-full h-full lg:max-h-[620px] object-cover object-top clip-custom select-none"
                        alt="Yet Another Room">
                </div>

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
            </div>

            <!-- Search Form at Bottom Center -->
            <form id="checkin-form" action="../User/RoomBooking.php" method="GET" class="fixed bottom-8 left-1/2 transform -translate-x-1/2 w-full sm:max-w-[1030px] z-10 p-4 bg-white rounded-sm shadow-sm border flex justify-between items-center space-x-4 transition-all duration-1000">
                <div class="flex items-center space-x-4">
                    <div class="flex gap-3">
                        <!-- Check-in Date -->
                        <div>
                            <label class="font-semibold text-blue-900">Check-In Date</label>
                            <input type="date" id="checkin-date" name="checkin_date" class="p-3 border border-gray-300 rounded-sm outline-none" placeholder="Check-in Date" required>
                        </div>
                        <!-- Check-out Date -->
                        <div>
                            <label class="font-semibold text-blue-900">Check-Out Date</label>
                            <input type="date" id="checkout-date" name="checkout_date" class="p-3 border border-gray-300 rounded-sm outline-none" placeholder="Check-out Date" required>
                        </div>
                    </div>
                    <div class="flex">
                        <!-- Adults -->
                        <select id="adults" name="adults" class="p-3 border border-gray-300 rounded-sm outline-none">
                            <option value="1">1 Adult</option>
                            <option value="2">2 Adults</option>
                            <option value="3">3 Adults</option>
                            <option value="4">4 Adults</option>
                            <option value="5">5 Adults</option>
                            <option value="6">6 Adults</option>
                        </select>
                        <!-- Children -->
                        <select id="children" name="children" class="p-3 border border-gray-300 rounded-sm outline-none">
                            <option value="0">0 Children</option>
                            <option value="1">1 Child</option>
                            <option value="2">2 Children</option>
                            <option value="3">3 Children</option>
                            <option value="4">4 Children</option>
                            <option value="5">5 Children</option>
                        </select>
                    </div>
                </div>

                <!-- Search Button -->
                <button type="submit" name="check_availability" class="p-3 bg-blue-900 text-white rounded-sm hover:bg-blue-950 uppercase font-semibold transition-colors duration-300 select-none">
                    Check Availability
                </button>
            </form>
        </div>

        <div class="flex flex-col items-center justify-center py-16 px-3 text-center">
            <h1 class="text-2xl sm:text-4xl mb-5 text-blue-900 font-semibold">Get away at the best price</h1>
            <p class="text-slate-600 mb-3">Where, when, with anyone you want</p>
            <a href="#" class="flex items-center gap-1 group">
                <p class="group-hover:underline group-hover:underline-offset-2">Discover all offers</p>
                <i class="ri-arrow-right-line text-xl group-hover:translate-x-2 transition-all duration-200"></i>
            </a>
        </div>

        <section class="grid grid-cols-1 md:grid-cols-3 gap-4 px-4 max-w-[1310px] mx-auto" data-aos="fade">
            <!-- Card 1 -->
            <a href="#" class="block w-full md:max-w-[450px] mx-auto group">
                <div class="h-auto sm:h-[280px] select-none overflow-hidden">
                    <img src="../UserImages/hotel-room-5858069_1280.jpg" class="w-full h-full object-cover rounded-sm transform group-hover:scale-105 transition-transform duration-200" alt="Image">
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
            <a href="Dining.php" class="block w-full md:max-w-[450px] mx-auto group">
                <div class="h-auto sm:h-[280px] select-none overflow-hidden">
                    <img src="../UserImages/FORMAT-16-9E---1920-X-1080-PX (1)_3by2.webp" class="w-full h-full object-cover rounded-sm transform group-hover:scale-105 transition-transform duration-200" alt="Image">
                </div>
                <div>
                    <h1 class="text-slate-700 font-semibold mt-3">Life in balance: Breakfast at Opulence</h1>
                    <p class="text-slate-600 mt-2">
                        When there's an opportunity to indulge while enjoying a variety of choices,
                        ensuring the energy needed for the day ahead. Perfect for business or family trips.
                    </p>
                    <div class="flex items-center text-amber-500 group mt-1">
                        <span class="group-hover:text-amber-600 transition-all duration-200">Reserve your breakfast</span>
                        <i class="ri-arrow-right-line text-xl group-hover:text-amber-600 group-hover:translate-x-2 transition-all duration-200"></i>
                    </div>
                </div>
            </a>

            <!-- Card 3 -->
            <a href="../Store/Store.php" class="block w-full md:max-w-[450px] mx-auto group">
                <div class="h-auto sm:h-[280px] select-none overflow-hidden">
                    <img src="../UserImages/Standard-Room-model.jpg" class="w-full h-full object-cover rounded-sm transform group-hover:scale-105 transition-transform duration-200" alt="Image">
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

        <section class="grid grid-cols-1 md:grid-cols-3 gap-4 px-4 pb-10 border-b max-w-[1310px] mx-auto" data-aos="fade">
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

    <!-- AOS JS -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 600,
            once: false,
        });
    </script>
    <script src="//unpkg.com/alpinejs" defer></script>
    <script type="module" src="../JS/index.js"></script>
</body>

</html>