<?php
session_start();
include('../config/dbConnection.php');
include('../includes/AutoIDFunc.php');

$userID = $_SESSION['UserID'];

$select = "SELECT * FROM usertb WHERE UserID = '$userID'";
$query = mysqli_query($connect, $select);

if (mysqli_num_rows($query) > 0) {
    while ($row = mysqli_fetch_assoc($query)) {
        $user_email = $row['UserEmail'];
        $user_phone = $row['UserPhone'];
    }
}

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

$alertMessage = '';
$contactSuccess = false;
$contactID = AutoID('contacttb', 'ContactID', 'CT-', 6);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $fullname = mysqli_real_escape_string($connect, $_POST['fullname']);
    $email = mysqli_real_escape_string($connect, $_POST['email']);
    $contactNumber = mysqli_real_escape_string($connect, $_POST['phone']);
    $country = mysqli_real_escape_string($connect, $_POST['country']);
    $message = mysqli_real_escape_string($connect, $_POST['contactMessage']);

    $addContactQuery = "INSERT INTO contacttb (ContactID, UserID, FullName, UserEmail, UserPhone, Country, ContactMessage)
    VALUES ('$contactID', '$userID', '$fullname', '$email', '$contactNumber', '$country', '$message')";

    if (mysqli_query($connect, $addContactQuery)) {
        $contactSuccess = true;
    } else {
        $alertMessage = "Failed to submit contact. Please try again.";
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

        <!-- Contact Form -->
        <section class="flex flex-col sm:flex-row justify-center gap-10 p-3">
            <div>
                <h1 class="text-2xl font-semibold text-black mb-3">Locate Us</h1>
                <ul class="text-lg flex flex-col gap-1 text-slate-700">
                    <li>459 Pyay Road, Kamayut Township , 11041</li>
                    <li>Yangon, Myanmar</li>
                    <li>+1 123-456-7890</li>
                    <li>mail@opulence.com</li>
                </ul>
            </div>
            <div>
                <form class="flex flex-col space-y-4 w-full" action="<?php $_SERVER["PHP_SELF"] ?>" method="post" id="signinForm">
                    <!-- User id -->
                    <input type="hidden" name="userid" value="<?php echo $userID; ?>">
                    <label class="block text-sm text-start font-medium text-gray-700 mb-1">User Information</label>
                    <div class="flex flex-col sm:flex-row gap-4 sm:gap-2">
                        <!-- Username Input -->
                        <div class="relative">
                            <input
                                id="contactFullNameInput"
                                class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                type="text"
                                name="fullname"
                                placeholder="Enter your fullname">
                            <small id="contactFullNameError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                        </div>
                        <!-- Email Input -->
                        <div class="relative">
                            <input
                                id="contactEmailInput"
                                class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                                type="email"
                                name="email"
                                value="<?php echo $user_email; ?>"
                                placeholder="Enter your email">
                            <small id="contactEmailError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                        </div>
                    </div>

                    <!-- Phone Input -->
                    <div class="relative">
                        <input
                            id="contactPhoneInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            type="tel"
                            name="phone"
                            value="<?php echo $user_phone; ?>"
                            placeholder="Enter your phone">
                        <small id="contactPhoneError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>

                    <!-- Country Select -->
                    <div class="relative">
                        <select id="countryDropdown" name="country" class="border p-2 rounded text-sm w-full">
                            <option value="">Loading...</option>
                        </select>

                        <script>
                            const dropdown = document.getElementById('countryDropdown');

                            async function fetchCountries() {
                                try {
                                    const response = await fetch('https://restcountries.com/v3.1/all');
                                    if (!response.ok) {
                                        throw new Error('Failed to fetch countries');
                                    }
                                    const countries = await response.json();
                                    populateDropdown(countries);
                                } catch (error) {
                                    console.error(error);
                                    dropdown.innerHTML = '<option value="">Error loading countries</option>';
                                }
                            }

                            function populateDropdown(countries) {
                                dropdown.innerHTML = '<option value="">Select a country</option>';
                                countries.sort((a, b) => a.name.common.localeCompare(b.name.common)); // Sort by country name
                                countries.forEach(country => {
                                    const option = document.createElement('option');
                                    option.value = country.cca2; // Use country code as the value
                                    option.textContent = country.name.common; // Use country name as the display text
                                    dropdown.appendChild(option);
                                });
                            }

                            fetchCountries();
                        </script>
                        <small id="phoneError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>

                    <!-- Message Input -->
                    <div class="relative w-full">
                        <label class="block text-sm text-start font-medium text-gray-700 mb-1">Message <sup>*</sup></label>
                        <textarea
                            id="contactMessageInput"
                            class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                            name="contactMessage"
                            placeholder="Enter your message"></textarea>
                        <small id="contactMessageError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
                    </div>

                    <!-- Signin Button -->
                    <input
                        class=" bg-amber-500 font-semibold text-white px-4 py-2 rounded-md hover:bg-amber-600 cursor-pointer transition-colors duration-200"
                        type="submit"
                        name="submit"
                        value="Submit">

                    <!-- reCAPTCHA -->
                    <div class="flex justify-center">
                        <div class="g-recaptcha" data-sitekey="6LcE3G0pAAAAAE1GU9UXBq0POWnQ_1AMwyldy8lX"></div>
                    </div>

                    <!-- Include reCAPTCHA Script -->
                    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
                </form>
            </div>
        </section>
        <div class="flex flex-col items-center justify-center py-16 px-3 text-center">
            <p class="text-slate-600 mb-3">YOUR OPULENCE</p>
            <h1 class="text-2xl sm:text-4xl mb-5 text-blue-900 font-semibold">Follow Us</h1>
            <p class="text-slate-600 mb-3">Stay in touch and connected to all the news and happenings.</p>
            <ul class="flex items-center gap-7 text-xl mt-3">
                <li class="bg-rose-600 p-2 rounded-full w-12 h-12 text-white hover:bg-rose-500 transition-colors duration-200">
                    <a href="#"><i class="ri-instagram-line text-3xl"></i></a>
                </li>
                <li class="bg-blue-600 p-2 rounded-full w-12 h-12 text-white hover:bg-blue-500 transition-colors duration-200">
                    <a href="#"><i class="ri-facebook-circle-fill text-3xl"></i></a>
                </li>
                <li class="bg-black p-2 rounded-full w-12 h-12 text-white hover:bg-gray-500 transition-colors duration-200">
                    <a href="#"><i class="ri-twitter-x-line text-3xl"></i></a>
                </li>
                <li class="bg-red-600 p-2 rounded-full w-12 h-12 text-white hover:bg-red-500 transition-colors duration-200">
                    <a href="#"><i class="ri-youtube-fill text-3xl"></i></a>
                </li>
            </ul>
        </div>

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