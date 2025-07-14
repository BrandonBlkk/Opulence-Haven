<?php
session_start();
include('../config/db_connection.php');

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

$session_userID = (!empty($_SESSION["UserID"]) ? $_SESSION["UserID"] : null);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | Opulence Haven</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../CSS/output.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../CSS/input.css?v=<?php echo time(); ?>">
    <!-- Swiper JS -->
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    <!-- AOS CSS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
</head>

<body class="relative min-w-[350px]">
    <?php
    include('../includes/navbar.php');
    include('../includes/cookies.php');
    ?>

    <!-- Hero Section with Swiper -->
    <div class="relative swiper-container h-96 md:h-screen max-h-[800px] overflow-hidden">
        <div class="swiper-wrapper">
            <div class="swiper-slide relative">
                <img src="https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80"
                    alt="Opulence Haven Hotel Lobby"
                    class="w-full h-full object-cover object-center select-none">
                <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center">
                    <div class="text-center px-4" data-aos="fade-up">
                        <h1 class="text-white text-4xl lg:text-6xl font-bold mb-6">Our Story</h1>
                        <div class="w-32 h-1 bg-amber-500 mx-auto mb-8"></div>
                        <p class="text-xl text-white max-w-2xl mx-auto">Discover the legacy of elegance and hospitality that defines Opulence Haven</p>
                    </div>
                </div>
            </div>
            <div class="swiper-slide relative">
                <img src="https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80"
                    alt="Opulence Haven Exterior"
                    class="w-full h-full object-cover object-center select-none">
                <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center">
                    <div class="text-center px-4" data-aos="fade-up">
                        <h1 class="text-white text-4xl lg:text-6xl font-bold mb-6">Our Heritage</h1>
                        <div class="w-32 h-1 bg-amber-500 mx-auto mb-8"></div>
                        <p class="text-xl text-white max-w-2xl mx-auto">Blending Myanmar tradition with contemporary luxury</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Our Story Section -->
    <section class="py-16 px-4 sm:px-6 lg:px-8 bg-white">
        <div class="max-w-7xl mx-auto">
            <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">
                <!-- Text Content -->
                <div class="order-2 lg:order-1" data-aos="fade-right" data-aos-mobile="false">
                    <div class="mb-8">
                        <span class="text-sm uppercase tracking-widest text-amber-600 font-medium">Our Heritage</span>
                        <h2 class="mt-2 text-3xl md:text-4xl font-serif font-light text-gray-900 leading-tight">
                            The <span class="font-semibold">Opulence Haven</span> Journey
                        </h2>
                        <div class="w-16 h-0.5 bg-amber-500 mt-6"></div>
                    </div>

                    <div class="space-y-6 text-gray-700">
                        <p class="text-lg leading-relaxed">
                            Founded in 2018 by U Myint Maung in the vibrant city of Yangon, Opulence Haven Hotel was conceived as a premium destination that would blend Myanmar's rich cultural heritage with contemporary luxury.
                        </p>
                        <p class="text-lg leading-relaxed">
                            Our vision was simple yet ambitious: to create a sanctuary that would welcome international and local travelers alike with unparalleled hospitality, elegant accommodations, and personalized service that would leave lasting impressions.
                        </p>
                        <p class="text-lg leading-relaxed">
                            From our early days, we've distinguished ourselves by harmonizing traditional Myanmar warmth with modern comforts, earning recognition and praise from guests across the globe.
                        </p>
                    </div>
                </div>

                <!-- Image Content -->
                <div class="order-1 lg:order-2 relative" data-aos="fade-left" data-aos-mobile="false">
                    <div class="relative aspect-w-16 aspect-h-12 lg:aspect-none select-none">
                        <img src="https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80"
                            alt="Opulence Haven Hotel Exterior"
                            class="w-full h-full object-cover object-center rounded-sm shadow-lg">
                    </div>
                    <div class="absolute -bottom-6 -right-6 bg-amber-600 px-6 py-4 text-white font-serif text-xl hidden lg:block transform translate-y-0 hover:-translate-y-1 transition-transform duration-300">
                        <span class="block text-sm font-sans font-normal tracking-wider">ESTABLISHED</span>
                        <span class="block text-2xl font-medium">2018</span>
                    </div>

                    <!-- Decorative element -->
                    <div class="absolute -top-6 -left-6 w-24 h-24 border-4 border-amber-500 opacity-20 hidden lg:block"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Mission Section -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="text-center mb-16" data-aos="fade-up" data-aos-mobile="false">
                <span class="text-sm uppercase tracking-wider text-amber-600 font-medium">Our Promise</span>
                <h2 class="mt-4 text-3xl md:text-4xl font-serif font-light text-gray-900">
                    Commitment to <span class="font-semibold">Excellence</span>
                </h2>
                <div class="w-16 h-0.5 bg-amber-500 mt-6 mx-auto"></div>
                <p class="mt-6 max-w-3xl mx-auto text-lg text-gray-600 leading-relaxed">
                    At Opulence Haven, we don't just provide accommodation—we craft experiences that resonate with the soul of Myanmar while meeting global standards of luxury.
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Card 1 -->
                <div class="group relative overflow-hidden bg-white rounded-lg shadow-sm transition-all duration-500" data-aos="fade-up" data-aos-delay="100" data-aos-mobile="false">
                    <!-- Icon container with animated border -->
                    <div class="relative z-10 p-8 text-center">
                        <div class="mx-auto w-20 h-20 flex items-center justify-center mb-6 relative">
                            <!-- Animated circle border -->
                            <div class="absolute inset-0 rounded-full border-2 border-amber-300 transform scale-95 group-hover:scale-105 group-hover:border-amber-500 transition-all duration-500"></div>
                            <!-- Icon -->
                            <div class="text-amber-500 text-4xl group-hover:text-amber-600 transition-colors duration-300">✧</div>
                        </div>

                        <!-- Content -->
                        <h3 class="text-xl font-serif font-medium mb-4 text-gray-900 group-hover:text-gray-800 transition-colors duration-300">
                            Heritage & Innovation
                        </h3>
                        <p class="text-gray-600 leading-relaxed group-hover:text-gray-700 transition-colors duration-300">
                            We honor Myanmar's traditions while embracing modern hospitality innovations to serve our guests better.
                        </p>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="group relative overflow-hidden bg-white rounded-lg shadow-sm transition-all duration-500" data-aos="fade-up" data-aos-delay="200" data-aos-mobile="false">
                    <!-- Icon container with animated border -->
                    <div class="relative z-10 p-8 text-center">
                        <div class="mx-auto w-20 h-20 flex items-center justify-center mb-6 relative">
                            <!-- Animated circle border -->
                            <div class="absolute inset-0 rounded-full border-2 border-amber-300 transform scale-95 group-hover:scale-105 group-hover:border-amber-500 transition-all duration-500"></div>
                            <!-- Icon -->
                            <div class="text-amber-500 text-4xl group-hover:text-amber-600 transition-colors duration-300">♥</div>
                        </div>

                        <!-- Content -->
                        <h3 class="text-xl font-serif font-medium mb-4 text-gray-900 group-hover:text-gray-800 transition-colors duration-300">
                            Personalized Service
                        </h3>
                        <p class="text-gray-600 leading-relaxed group-hover:text-gray-700 transition-colors duration-300">
                            Every guest receives attentive, customized service that anticipates needs and exceeds expectations.
                        </p>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="group relative overflow-hidden bg-white rounded-lg shadow-sm transition-all duration-500" data-aos="fade-up" data-aos-delay="300" data-aos-mobile="false">
                    <!-- Icon container with animated border -->
                    <div class="relative z-10 p-8 text-center">
                        <div class="mx-auto w-20 h-20 flex items-center justify-center mb-6 relative">
                            <!-- Animated circle border -->
                            <div class="absolute inset-0 rounded-full border-2 border-amber-300 transform scale-95 group-hover:scale-105 group-hover:border-amber-500 transition-all duration-500"></div>
                            <!-- Icon -->
                            <div class="text-amber-500 text-4xl group-hover:text-amber-600 transition-colors duration-300">✿</div>
                        </div>

                        <!-- Content -->
                        <h3 class="text-xl font-serif font-medium mb-4 text-gray-900 group-hover:text-gray-800 transition-colors duration-300">
                            Sustainable Luxury
                        </h3>
                        <p class="text-gray-600 leading-relaxed group-hover:text-gray-700 transition-colors duration-300">
                            We believe true opulence must be responsible, implementing eco-friendly practices throughout our operations.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Digital Transformation Section -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                <!-- Image -->
                <div class="relative" data-aos="fade-right" data-aos-mobile="false">
                    <div class="aspect-w-16 aspect-h-9 overflow-hidden select-none">
                        <img src="../UserImages/modern-highrise-building.jpg"
                            class="w-full h-full object-cover transform hover:scale-105 transition-transform duration-700"
                            alt="Hotel Image">
                    </div>
                    <div class="absolute -bottom-6 -right-6 bg-gray-50 p-6 hidden lg:block">
                        <span class="block text-xs uppercase tracking-wider text-amber-600">Innovation</span>
                        <span class="block text-2xl font-serif font-medium mt-1">Digital Future</span>
                    </div>
                </div>

                <!-- Content -->
                <div class="lg:pl-12" data-aos="fade-left" data-aos-mobile="false">
                    <span class="text-sm uppercase tracking-wider text-amber-600 font-medium">Transformation</span>
                    <h2 class="mt-2 text-3xl md:text-4xl font-serif font-light text-gray-900">
                        Embracing the <span class="font-semibold">Digital Era</span>
                    </h2>
                    <div class="w-16 h-0.5 bg-amber-500 mt-6"></div>

                    <div class="mt-8 space-y-6 text-gray-600 leading-relaxed">
                        <p>
                            While our core values remain unchanged, we recognize the need to evolve with the digital age. Our transition from manual operations to a comprehensive online presence marks an exciting new chapter in our story.
                        </p>
                        <p>
                            This website represents our commitment to making Opulence Haven more accessible to travelers worldwide while maintaining the personal touch that defines our hospitality.
                        </p>
                    </div>

                    <div class="mt-10">
                        <a href="room_booking.php" class="inline-flex items-center px-8 py-3 border border-transparent text-base font-medium rounded-sm shadow-sm text-white bg-amber-500 hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 select-none transition-all duration-300 group">
                            Explore Our Rooms
                            <svg class="ml-3 h-5 w-5 transform group-hover:translate-x-1 transition-transform" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonial Section -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="text-center mb-16" data-aos="fade-up" data-aos-mobile="false">
                <span class="text-sm uppercase tracking-wider text-amber-600 font-medium">Guest Voices</span>
                <h2 class="mt-4 text-3xl md:text-4xl font-serif font-light text-gray-900">
                    Exceptional <span class="font-semibold">Experiences</span>
                </h2>
                <div class="w-16 h-0.5 bg-amber-500 mt-6 mx-auto"></div>
                <p class="mt-6 max-w-3xl mx-auto text-lg text-gray-600 leading-relaxed">
                    Don't just take our word for it—here's what our guests say about their Opulence Haven experience
                </p>
            </div>

            <div class="swiper testimonialSwiper pb-16" data-aos="fade-up" data-aos-mobile="false">
                <div class="swiper-wrapper">
                    <!-- Testimonial 1 -->
                    <div class="swiper-slide">
                        <div class="bg-white p-10 rounded-lg shadow-md border border-gray-100 h-full flex flex-col">
                            <div class="text-amber-500 text-3xl mb-6">"</div>
                            <p class="mb-8 text-gray-600 italic leading-relaxed flex-grow">
                                The perfect blend of Myanmar's warm hospitality and modern luxury. Every detail was thoughtfully considered.
                            </p>
                            <div class="flex items-center">
                                <img src="https://randomuser.me/api/portraits/women/65.jpg"
                                    alt="Sarah K."
                                    class="w-12 h-12 rounded-full object-cover">
                                <div class="ml-4">
                                    <h4 class="font-bold text-gray-900">Sarah K.</h4>
                                    <p class="text-sm text-gray-500">London, UK</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Testimonial 2 -->
                    <div class="swiper-slide">
                        <div class="bg-white p-10 rounded-lg shadow-md border border-gray-100 h-full flex flex-col">
                            <div class="text-amber-500 text-3xl mb-6">"</div>
                            <p class="mb-8 text-gray-600 italic leading-relaxed flex-grow">
                                From the moment we arrived, we felt like honored guests rather than customers. The staff went above and beyond.
                            </p>
                            <div class="flex items-center">
                                <img src="https://randomuser.me/api/portraits/men/32.jpg"
                                    alt="Michael T."
                                    class="w-12 h-12 rounded-full object-cover">
                                <div class="ml-4">
                                    <h4 class="font-bold text-gray-900">Michael T.</h4>
                                    <p class="text-sm text-gray-500">Sydney, Australia</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Testimonial 3 -->
                    <div class="swiper-slide">
                        <div class="bg-white p-10 rounded-lg shadow-md border border-gray-100 h-full flex flex-col">
                            <div class="text-amber-500 text-3xl mb-6">"</div>
                            <p class="mb-8 text-gray-600 italic leading-relaxed flex-grow">
                                A true haven in the heart of Yangon. The perfect base to explore the city while enjoying five-star comforts.
                            </p>
                            <div class="flex items-center">
                                <img src="https://randomuser.me/api/portraits/women/44.jpg"
                                    alt="Priya R."
                                    class="w-12 h-12 rounded-full object-cover">
                                <div class="ml-4">
                                    <h4 class="font-bold text-gray-900">Priya R.</h4>
                                    <p class="text-sm text-gray-500">Mumbai, India</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="swiper-pagination !relative !mt-10"></div>
            </div>
        </div>
    </section>

    <!-- Premium CTA Section - Melia Luxury Style -->
    <section class="py-24 bg-[#1a1a1a] relative">
        <!-- Decorative background elements -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute top-0 right-0 w-1/3 h-full bg-[#222222]"></div>
        </div>

        <div class="max-w-7xl mx-auto px-6 lg:px-8 relative z-10">
            <div class="flex flex-col lg:flex-row items-center">
                <!-- Text content -->
                <div class="lg:w-1/2 lg:pr-12 mb-12 lg:mb-0">
                    <span class="inline-block text-sm uppercase tracking-[0.2em] text-amber-500 mb-6 font-light">
                        Luxury Experience
                    </span>
                    <h2 class="text-4xl md:text-5xl font-serif font-normal text-white leading-snug mb-8">
                        Your <span class="font-medium text-amber-500">Opulent Escape</span> Awaits
                    </h2>
                    <div class="w-24 h-0.5 bg-gradient-to-r from-amber-500 to-transparent mb-10"></div>
                    <p class="text-lg text-gray-300 leading-relaxed mb-10 max-w-lg">
                        Discover unparalleled hospitality in the heart of Yangon, where timeless tradition meets contemporary luxury.
                    </p>

                    <!-- Trust indicators -->
                    <div class="flex items-center space-x-10">
                        <div class="flex items-center">
                            <div class="bg-amber-500/10 p-1.5 rounded-full mr-3">
                                <svg class="h-5 w-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <span class="text-sm text-gray-300 tracking-wider">5-Star Rated</span>
                        </div>
                        <div class="flex items-center">
                            <div class="bg-amber-500/10 p-1.5 rounded-full mr-3">
                                <svg class="h-5 w-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd" />
                                    <path d="M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z" />
                                </svg>
                            </div>
                            <span class="text-sm text-gray-300 tracking-wider">Award Winning</span>
                        </div>
                    </div>
                </div>

                <!-- Right side - premium booking panel -->
                <div class="lg:w-1/2 relative">
                    <!-- Decorative frame -->
                    <div class="absolute inset-0 border-2 border-amber-500/20 transform translate-x-5 translate-y-5"></div>
                    <div class="absolute inset-0 border-2 border-b-0 border-r-0  border-amber-500/20 transform -translate-x-2 -translate-y-2"></div>

                    <!-- Main panel -->
                    <div class="relative bg-[#222222] p-12">
                        <h3 class="text-2xl font-serif font-normal text-white mb-10 text-center">
                            Reserve Your <span class="text-amber-500">Exclusive</span> Retreat
                        </h3>

                        <!-- Benefit highlights -->
                        <div class="grid grid-cols-2 gap-6 mb-12">
                            <div class="flex items-start">
                                <div class="bg-amber-500/10 p-1 rounded-full mr-3 mt-0.5">
                                    <svg class="h-5 w-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <span class="text-sm text-gray-300 tracking-wide">Complimentary upgrade</span>
                            </div>
                            <div class="flex items-start">
                                <div class="bg-amber-500/10 p-1 rounded-full mr-3 mt-0.5">
                                    <svg class="h-5 w-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <span class="text-sm text-gray-300 tracking-wide">Early check-in</span>
                            </div>
                            <div class="flex items-start">
                                <div class="bg-amber-500/10 p-1 rounded-full mr-3 mt-0.5">
                                    <svg class="h-5 w-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <span class="text-sm text-gray-300 tracking-wide">Welcome amenities</span>
                            </div>
                            <div class="flex items-start">
                                <div class="bg-amber-500/10 p-1 rounded-full mr-3 mt-0.5">
                                    <svg class="h-5 w-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <span class="text-sm text-gray-300 tracking-wide">Priority reservations</span>
                            </div>
                        </div>

                        <!-- Action buttons -->
                        <div class="space-y-5">
                            <!-- Secondary action -->
                            <a href="room_booking.php" class="group relative flex items-center justify-center px-8 py-4 border border-amber-500/30 rounded-sm overflow-hidden transition-all duration-300 hover:border-amber-400/50">
                                <span class="relative flex items-center text-lg font-medium text-amber-500">
                                    Explore Rooms
                                    <svg class="ml-3 h-5 w-5 text-amber-500 transition-all duration-300 group-hover:translate-x-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </a>
                        </div>

                        <!-- Guarantee text -->
                        <div class="mt-10 text-center">
                            <p class="text-xs text-gray-400 tracking-wider">
                                <svg class="w-4 h-4 inline-block mr-2 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                                Best rate guaranteed when booking direct
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Initialize Swiper -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Hero Swiper
            const heroSwiper = new Swiper('.swiper-container', {
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
            });

            const testimonialSwiper = new Swiper('.testimonialSwiper', {
                slidesPerView: 1,
                spaceBetween: 30,
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
                breakpoints: {
                    640: {
                        slidesPerView: 1,
                    },
                    768: {
                        slidesPerView: 2,
                    },
                    1024: {
                        slidesPerView: 3,
                    },
                },
            });
        });
    </script>

    <?php
    include('../includes/moveup_btn.php');
    include('../includes/footer.php');
    ?>

    <!-- AOS JS with mobile detection -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Function to check if mobile device
        function isMobile() {
            return window.innerWidth < 768;
        }

        // Initialize AOS with mobile check
        document.addEventListener('DOMContentLoaded', function() {
            AOS.init({
                duration: 600,
                once: false,
                disable: isMobile() // Disable on mobile
            });

            // Re-init AOS when window is resized
            window.addEventListener('resize', function() {
                AOS.refresh();
            });

            // Disable animations for elements with data-aos-mobile="false" on mobile
            if (isMobile()) {
                document.querySelectorAll('[data-aos-mobile="false"]').forEach(el => {
                    el.removeAttribute('data-aos');
                    el.removeAttribute('data-aos-delay');
                });
            }
        });
    </script>
    <script src="//unpkg.com/alpinejs" defer></script>
    <script type="module" src="../JS/index.js"></script>
</body>

</html>