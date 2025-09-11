<aside id="roomReview" class="fixed top-full flex flex-col bg-white w-full h-full p-4 z-40 transition-all duration-500 ease-in-out">
    <div class="flex justify-end pb-3">
        <i id="reviewCloseBtn" class="ri-close-line text-2xl cursor-pointer rounded transition-colors duration-300"></i>
    </div>

    <div class="flex justify-between items-center mb-2">
        <h2 class="text-xl font-bold text-gray-800 mb-3">Guests who stayed here loved</h2>
        <?php if ($userID) : ?>
            <!-- User -->
            <button id="writeReview" class="mt-4 text-start border-2 border-blue-600 rounded-md px-4 py-2 text-blue-600 hover:border-blue-800 hover:text-blue-800 text-sm font-medium select-none">
                Write a review
            </button>
        <?php else : ?>
            <!-- Guest -->
            <a href="../User/user_signin.php"
                class="text-start border-2 inline-block border-blue-900 rounded-md px-4 py-2 text-blue-900 
                                hover:border-blue-950 hover:text-blue-950 text-sm font-medium select-none">
                Sign in to write a review
            </a>
        <?php endif; ?>
    </div>

    <div class="flex flex-col md:flex-row gap-4">
        <div class="w-full md:w-[260px]">
            <div class="bg-white p-4 rounded-md shadow-sm border sticky top-4">
                <h3 class="text-lg font-semibold text-gray-700">Filter by:</h3>
                <h4 class="font-medium text-gray-800 my-4">Room Types</h4>
                <div class="space-y-3">
                    <?php
                    $select = "SELECT * FROM roomtypetb";
                    $query = $connect->query($select);
                    $count = $query->num_rows;
                    if ($count) {
                        for ($i = 0; $i < $count; $i++) {
                            $row = $query->fetch_assoc();
                            $roomtype_id = $row['RoomTypeID'];
                            $roomtype_name = $row['RoomType'];
                            $checked = isset($_GET['roomtypes']) && in_array($roomtype_id, (array)$_GET['roomtypes']) ? 'checked' : '';
                    ?>
                            <label class="flex items-center">
                                <input type="checkbox" name="roomtypes[]" value="<?= $roomtype_id ?>"
                                    class="mr-2 rounded text-orange-500 w-5 h-4 auto-submit"
                                    data-clicked="false" <?= $checked ?>>
                                <span class="text-sm"><?= htmlspecialchars($roomtype_name) ?></span>
                            </label>
                    <?php
                        }
                    } else {
                        echo '<span class="text-sm text-gray-500">No room types available</span>';
                    }
                    ?>
                </div>
                <!-- Traveller Type Filter -->
                <h4 class="font-medium text-gray-800 my-4">Traveller Type</h4>
                <div class="space-y-3">
                    <label class="flex items-center">
                        <input type="checkbox" name="traveller_type[]" value="Family"
                            class="mr-2 rounded text-orange-500 w-5 h-4 auto-submit"
                            data-clicked="false"
                            <?= isset($_GET['traveller_type']) && in_array('Family', (array)$_GET['traveller_type']) ? 'checked' : '' ?>>
                        <span class="text-sm">Family</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="traveller_type[]" value="Couple"
                            class="mr-2 rounded text-orange-500 w-5 h-4 auto-submit"
                            data-clicked="false"
                            <?= isset($_GET['traveller_type']) && in_array('Couple', (array)$_GET['traveller_type']) ? 'checked' : '' ?>>
                        <span class="text-sm">Couple</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="traveller_type[]" value="Group of friends"
                            class="mr-2 rounded text-orange-500 w-5 h-4 auto-submit"
                            data-clicked="false"
                            <?= isset($_GET['traveller_type']) && in_array('Group of friends', (array)$_GET['traveller_type']) ? 'checked' : '' ?>>
                        <span class="text-sm">Group of friends</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="traveller_type[]" value="Solo traveller"
                            class="mr-2 rounded text-orange-500 w-5 h-4 auto-submit"
                            data-clicked="false"
                            <?= isset($_GET['traveller_type']) && in_array('Solo traveller', (array)$_GET['traveller_type']) ? 'checked' : '' ?>>
                        <span class="text-sm">Solo traveller</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="traveller_type[]" value="Business traveller"
                            class="mr-2 rounded text-orange-500 w-5 h-4 auto-submit"
                            data-clicked="false"
                            <?= isset($_GET['traveller_type']) && in_array('Business traveller', (array)$_GET['traveller_type']) ? 'checked' : '' ?>>
                        <span class="text-sm">Business traveller</span>
                    </label>
                </div>

                <!-- Room Rating Filter -->
                <h4 class="font-medium text-gray-800 my-4">Room rating</h4>
                <div class="space-y-3">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <label class="flex items-center">
                            <input type="checkbox" name="ratings[]" value="<?= $i ?>"
                                class="mr-2 rounded text-orange-500 w-5 h-4 auto-submit"
                                data-clicked="false"
                                <?= isset($_GET['ratings']) && in_array($i, (array)$_GET['ratings']) ? 'checked' : '' ?>>
                            <span class="text-sm"><?= $i ?> star<?= $i > 1 ? 's' : '' ?></span>
                        </label>
                    <?php endfor; ?>
                </div>
            </div>
        </div>

        <!-- Review Results -->
        <?php
        include('review_results.php');
        ?>
    </div>
</aside>

<!-- Write Review -->
<?php
include('user_write_review.php');
?>

<!-- Loader -->
<?php
include('loader.php');
?>