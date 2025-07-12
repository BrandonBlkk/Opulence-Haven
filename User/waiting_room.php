<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opulence Haven</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../CSS/output.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../CSS/input.css?v=<?php echo time(); ?>">
</head>

<body class="min-w-[380px]">
    <section class="waiting-section flex flex-col items-center justify-center min-h-screen p-2 bg-gray-50">
        <div class="waiting-title text-center space-y-2 mb-8">
            <h1 class="text-3xl font-bold">Hang Tight</h1>
            <h2 class="text-lg sm:text-xl text-gray-600">You're now in a virtual queue</h2>
            <p class="text-sm sm:text-base text-gray-700">You are now placed in a virtual queue and will be redirected to our site shortly.</p>
        </div>
        <div class="waiting-img max-w-[650px] mb-8 select-none">
            <img class="max-w-full h-auto" src="../UserImages/travelers-with-suitcases-semi-flat-color-character-editable-full-body-people-sitting-on-wooden-bench-and-waiting-on-white-simple-cartoon-spot-illustration-for-web-graphic-d.jpg" alt="Waiting Image">
        </div>
        <p id="time" class="text-base sm:text-lg text-center">
            <span class="font-semibold">Your estimated waiting time is: </span>
            <span id="countdown" class="text-amber-600">10:00</span> minutes
        </p>
        <p id="alert" class="text-red-500 font-bold mt-2">DO NOT EXIT PAGE</p>

        <!-- Additional Information Section -->
        <div class="additional-info text-center mt-6 space-y-4">
            <p class="text-gray-600 text-sm">
                <span class="font-medium">Booking Tip:</span> Rooms and offers are held during the queue but may become limited. Please stay on the page.
            </p>
            <p class="text-gray-600 text-sm">
                <span class="font-medium">Questions?</span> Reach out to our support team at <a href="mailto:support@opulenceHaven.com" class="text-amber-600 underline">support@opulenceHaven.com</a>.
            </p>
            <p class="text-gray-600 text-sm">
                <span class="font-medium">Securing Your Spot:</span> The virtual queue ensures fair access to the best deals and availability. Thank you for your understanding.
            </p>
        </div>
    </section>

    <script src="./JS/auth.js"></script>

    <script type="text/javascript">
        let totalSeconds = 600;

        const updateCountdown = () => {
            let minutes = Math.floor(totalSeconds / 60);
            let seconds = totalSeconds % 60;

            // Format minutes and seconds with leading zeros if needed
            let formattedMinutes = minutes < 10 ? "0" + minutes : minutes;
            let formattedSeconds = seconds < 10 ? "0" + seconds : seconds;

            document.getElementById("countdown").textContent = formattedMinutes + ":" + formattedSeconds;
            totalSeconds--;

            if (totalSeconds < 0) {
                window.location.href = "user_signin.php";
            }
        }

        setInterval(updateCountdown, 1000);
    </script>
</body>

</html>