<div id="writeReviewModal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 invisible p-2 -translate-y-5 transition-all duration-300">
    <div class="bg-white w-full md:w-1/3 mx-4 p-6 rounded-md shadow-md max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl text-gray-700 font-bold mb-4">Write a Review</h2>
        <form class="flex flex-col space-y-4" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" id="roomForm">

            <!-- Room Name -->
            <div class="relative">
                <label class="block text-sm font-medium text-gray-700 mb-1">Room Name</label>
                <input
                    id="roomNameInput"
                    class="p-2 w-full border rounded focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50 transition duration-300 ease-in-out"
                    type="text"
                    name="roomname"
                    placeholder="Enter room name">
                <small id="roomNameError" class="absolute left-2 -bottom-2 bg-white text-red-500 text-xs opacity-0 transition-all duration-200 select-none"></small>
            </div>

            <!-- Status and Room Type -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Status -->
                <div class="relative">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="roomstatus" id="roomstatus" class="p-2 w-full border rounded outline-none" required>
                        <option value="" disabled selected>Select status</option>
                        <option value="Available">Available</option>
                        <option value="Unavailable">Unavailable</option>
                        <option value="Maintenance">Maintenance</option>
                    </select>
                </div>

                <!-- Room Type -->
                <div class="relative">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Room Type</label>
                    <select name="roomtype" id="roomtype" class="p-2 w-full border rounded outline-none" required>
                        <option value="" disabled selected>Select type of rooms</option>
                        <?php
                        $select = "SELECT * FROM roomtypetb";
                        $query = $connect->query($select);
                        $count = $query->num_rows;
                        if ($count) {
                            for ($i = 0; $i < $count; $i++) {
                                $row = $query->fetch_assoc();
                                $room_type_id = $row['RoomTypeID'];
                                $room_type = $row['RoomType'];
                                echo "<option value='$room_type_id'>$room_type</option>";
                            }
                        } else {
                            echo "<option value='' disabled>No data yet</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end gap-4 select-none pt-4">
                <div id="addReviewCancelBtn" class="px-4 py-2 text-amber-500 font-semibold hover:text-amber-600 cursor-pointer">
                    Cancel
                </div>
                <button
                    type="submit"
                    name="addroom"
                    class="bg-amber-500 text-white font-semibold px-4 py-2 rounded-sm select-none hover:bg-amber-600 transition-colors">
                    Add Room
                </button>
            </div>
        </form>
    </div>
</div>

<div id="darkOverlay2" class="fixed inset-0 bg-black bg-opacity-50 opacity-0 invisible  z-40 transition-opacity duration-300"></div>