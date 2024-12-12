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

<body>
    <?php
    include('./includes/StoreNavbar.php');
    ?>

    <main class="max-w-[1310px] mx-auto px-4 py-5">
        <!-- <section class="SVG flex justify-center p-4"> -->
        <form action="<?php $_SERVER["PHP_SELF"] ?>" method="post" enctype="multipart/form-data" class="flex flex-col md:flex-row justify-center">

            <!-- <input type="hidden" name="product_Id" value="<?php echo $product_id; ?>">
            <input type="hidden" name="product_size" value="<?php echo $product_size; ?>"> -->

            <div class="flex flex-col-reverse sm:flex-row gap-3 select-none">
                <div class="select-none cursor-pointer space-x-0 sm:space-y-2 flex gap-2 sm:block">
                    <div class="product-detail-img w-20 h-16">
                        <img class="w-full h-full rounded object-cover hover:border-2 hover:border-indigo-300" src="./UserImages/hilton-bath-mat-HIL-312-NL-WH_xlrg.jpg" alt="Image">
                    </div>
                    <div class="product-detail-img w-20 h-16">
                        <img class="w-full h-full rounded object-cover hover:border-2 hover:border-indigo-300" src="./UserImages/hilton-bath-mat-HIL-312-NL-WH_xlrg.jpg" alt="Image">
                    </div>
                    <div class="product-detail-img w-20 h-16">
                        <img class="w-full h-full rounded object-cover hover:border-2 hover:border-indigo-300" src="./UserImages/hilton-bath-mat-HIL-312-NL-WH_xlrg.jpg" alt="Image">
                    </div>
                </div>
                <div class="relative">
                    <div class="w-full md:max-w-[750px]">
                        <img id="mainImage" class="w-full h-full object-cover" src="./UserImages/hilton-bath-mat-HIL-312-NL-WH_xlrg.jpg" alt="Image">
                    </div>
                </div>
            </div>
            <div class="w-full md:max-w-[290px] py-3 px-0 sm:py-0 sm:px-3">
                <p class="text-lg font-bold mb-2">$ 34.65</p>

                <div class="mb-4 flex justify-between items-center">
                    <p class="text-sm text-gray-500">(20 available)</p>
                </div>

                <div class="mb-4">
                    <div class="mt-4">
                        <select id="size" name="size" class="block w-full p-2 border border-gray-300 rounded-md text-gray-700 bg-white cursor-pointer focus:border-indigo-500 focus:ring-indigo-500 transition-colors duration-200">
                            <option value="" disabled selected>Choose a size</option>
                            <option value="3">UK 3</option>
                            <option value="3.5">UK 3.5</option>
                            <option value="4">UK 4</option>
                            <option value="4.5">UK 4.5</option>
                            <option value="5">UK 5</option>
                            <option value="5.5">UK 5.5</option>
                            <option value="6">UK 6</option>
                            <option value="6.5">UK 6.5</option>
                            <option value="7">UK 7</option>
                            <option value="7.5">UK 7.5</option>
                            <option value="8">UK 8</option>
                            <option value="8.5">UK 8.5</option>
                            <option value="9">UK 9</option>
                            <option value="9.5">UK 9.5</option>
                            <option value="10">UK 10</option>
                            <option value="10.5">UK 10.5</option>
                            <option value="11">UK 11</option>
                            <option value="11.5">UK 11.5</option>
                            <option value="12">UK 12</option>
                            <option value="12.5">UK 12.5</option>
                            <option value="13">UK 13</option>
                            <option value="13.5">UK 13.5</option>
                            <option value="14">UK 14</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-between mb-4">
                    <input type="submit" value="ADD TO BAG" name="addtobag" class="w-full bg-amber-500 hover:bg-amber-600 text-white font-semibold text-center p-2 select-none transition-colors duration-300">
                </div>

                <div class="flex gap-4 border p-4 mb-4">
                    <i class="ri-truck-line text-2xl"></i>
                    <div>
                        <p>Free delivery on qualifying orders.</p>
                        <a href="Delivery.php" class="text-xs underline text-gray-500 hover:text-gray-400 transition-colors duration-200">View our Delivery & Returns Policy</a>
                    </div>
                </div>

                <div class="divide-y cursor-pointer" id="accordion">
                    <div class="p-1" data-target="details">
                        <div class="flex items-center justify-between font-semibold">
                            <h1>Product Details</h1>
                            <i class="ri-add-line text-xl"></i>
                        </div>
                        <div class="h-0 overflow-hidden transition-all duration-300 ease-in-out text-gray-600 text-sm" id="details">
                            <p>fsgdfgsdfgdfg</p>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div class="py-10 px-3 text-center">
            <h1 class="text-xl text-blue-900 font-semibold">Recommended Just For You</h1>
        </div>
        <section class="grid grid-cols-1 md:grid-cols-3 gap-2 px-4 max-w-[1000px] mx-auto">
            <!-- Card 1 -->
            <a href="#" class="block w-full sm:max-w-[300px] mx-auto group">
                <div class="h-auto sm:h-[180px] select-none">
                    <img src="UserImages/hotel-room-5858069_1280.jpg" class="w-full h-full object-cover rounded-sm" alt="Hotel Room">
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
            <a href="#" class="block w-full sm:max-w-[300px] mx-auto group">
                <div class="h-auto sm:h-[180px] select-none">
                    <img src="UserImages/FORMAT-16-9E---1920-X-1080-PX (1)_3by2.webp" class="w-full h-full object-cover rounded-sm" alt="Hotel Room">
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
            <a href="#" class="block w-full sm:max-w-[300px] mx-auto group">
                <div class="h-auto sm:h-[180px] select-none">
                    <img src="UserImages/hotel-room-5858069_1280.jpg" class="w-full h-full object-cover rounded-sm" alt="Hotel Room">
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
    </main>

    <!-- MoveUp Btn -->
    <?php
    include('./includes/Footer.php');
    ?>

    <script src="JS/index.js"></script>
</body>

</html>