<?php
require_once(__DIR__ . '/../includes/user_pagination.php');

$sortOrder = 'ASC';
if (isset($_GET['sort']) && $_GET['sort'] === 'newest') {
    $sortOrder = 'DESC';
}

$product_id = $_GET['product_ID'] ?? '';
$productReviewSelect = "SELECT productreviewtb.*, usertb.*
        FROM productreviewtb
        JOIN usertb 
        ON productreviewtb.UserID = usertb.UserID 
        WHERE productreviewtb.ProductID = '$product_id'
        ORDER BY productreviewtb.AddedDate $sortOrder
        LIMIT $rowsPerPage OFFSET $offset";

$productReviewSelectQuery = $connect->query($productReviewSelect);

if ($productReviewSelectQuery->num_rows > 0) {
    while ($row = $productReviewSelectQuery->fetch_assoc()) {
        $reviewID = $row['ReviewID'];
        $product_id = $row['ProductID'];
        $userid = $row['UserID'];
        $fullname = $row['UserName'];
        $reviewdate = $row['AddedDate'];
        $reviewupdate = $row['LastUpdate'];
        $rating = $row['Rating'];
        $comment = $row['Comment'];
        $member = $row['Membership'];

        $comment_words = explode(' ', $comment);
        if (count($comment_words) > 100) {
            $truncated_comment = implode(' ', array_slice($comment_words, 0, 100)) . '...';
            $full_comment = $comment;
        } else {
            $truncated_comment = $comment;
            $full_comment = '';
        }
?>
        <div class="bg-white py-3 flex items-start border-b-2 border-slate-100 space-x-4 w-full">
            <?php
            $nameParts = explode(' ', trim($row['UserName']));
            $initials = substr($nameParts[0], 0, 1);
            if (count($nameParts) > 1) {
                $initials .= substr(end($nameParts), 0, 1);
            }
            $bgColor = $row['ProfileBgColor'];
            ?>
            <div class="w-full">
                <div class="flex items-center gap-2">
                    <p class="w-10 h-10 rounded-full bg-[<?= $bgColor ?>] text-white uppercase font-semibold flex items-center justify-center select-none">
                        <?= $initials ?>
                    </p>
                    <div class="flex items-center flex-wrap space-x-2">
                        <p class="text-sm font-semibold text-gray-800"><?php echo $fullname; ?></p>
                        <span class="text-xs text-gray-500">
                            <?php
                            if ($session_userID == $userid) {
                                echo "<span class='text-sm text-green-500 font-semibold'> (You)</span>";
                            }
                            ?>
                            <?php echo ($member == 1) ? '• Verified Member <i class="ri-checkbox-circle-line text-green-500"></i>' : ''; ?>
                        </span>
                        <div class="review-date-container flex items-center relative">
                            <span class="time-ago text-gray-500 text-xs cursor-pointer hover:text-gray-600">
                                <?= timeAgo($reviewdate) ?>
                                <?php
                                // Add this condition to show "Edited" if the review was modified
                                if (isset($reviewupdate) && $reviewupdate != $reviewdate): ?>
                                    <span class="text-gray-400"> • Edited</span>
                                <?php endif; ?>
                            </span>
                            <span class="full-date text-xs text-gray-500 hidden">
                                Reviewed on <span><?= htmlspecialchars(date('Y-m-d h:i', strtotime($reviewdate))) ?></span>
                                <?php if (isset($reviewupdate) && $reviewupdate != $reviewdate): ?>
                                    <br>Edited on <span><?= htmlspecialchars(date('Y-m-d h:i', strtotime($reviewupdate))) ?></span>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div x-data="{ open: false }" class="relative inline-block <?= ($session_userID === $userid) ? '' : 'hidden' ?>">
                            <button
                                @click="open = !open"
                                type="button"
                                class="text-gray-500 hover:text-gray-600 focus:outline-none transition-colors duration-200">
                                <i class="ri-more-line text-xl"></i>
                            </button>
                            <div
                                x-show="open"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="transform opacity-100 scale-100"
                                x-transition:leave-end="transform opacity-0 scale-95"
                                @click.away="open = false"
                                class="absolute right-0 z-10 mt-2 w-32 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                                role="menu"
                                style="display: none;">
                                <form method="post" class="delete-form py-1 select-none" role="none">
                                    <input type="hidden" name="review_id" value="<?= $reviewID ?>">
                                    <input type="hidden" name="delete" value="1">
                                    <button
                                        @click="open = !open"
                                        type="button"
                                        class="edit-btn block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-colors duration-200"
                                        data-review-id="<?= $reviewID ?>"
                                        data-comment="<?= htmlspecialchars($comment) ?>">
                                        <i class="ri-edit-line mr-2"></i> Edit
                                    </button>
                                    <button
                                        @click="open = !open"
                                        type="submit"
                                        name="delete"
                                        class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-colors duration-200">
                                        <i class="ri-delete-bin-line mr-2"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center mt-1"><?php echo str_repeat('<i class="ri-star-s-line text-amber-500"></i>', $rating); ?></div>
                <div class="flex gap-1 divide-x-2 mt-1">
                    <p class="text-gray-700 text-xs font-semibold px-1">Brand: <span class="font-normal"><?php echo $brand; ?></span></p>
                    <p class="text-gray-700 text-xs font-semibold px-1">Pattern: <span class="font-normal"><?php echo $product_type; ?></span></p>
                </div>

                <div class="review-container">
                    <div class="review">
                        <p class="text-gray-700 mt-2 text-sm leading-relaxed truncated-comment"><?php echo $truncated_comment; ?></p>
                        <?php if ($full_comment): ?>
                            <p class="text-gray-400 inline-block hover:text-gray-500 text-sm cursor-pointer mt-1 select-none read-more"><i class="ri-arrow-down-s-line"></i> Read More</p>
                        <?php endif; ?>

                        <p class="text-gray-700 mt-2 text-sm leading-relaxed full-comment hidden"><?php echo $full_comment; ?></p>
                        <?php if ($full_comment): ?>
                            <p class="text-gray-400 hover:text-gray-500 text-sm cursor-pointer mt-1 normal-case read-less hidden"><i class="ri-arrow-up-s-line"></i> Read Less</p>
                        <?php endif; ?>
                    </div>

                    <!-- Hidden edit form -->
                    <form method="post" class="edit-form hidden mt-2" data-review-id="<?= $reviewID ?>">
                        <input type="hidden" name="review_id" value="<?= $reviewID ?>">
                        <input type="hidden" name="save_edit" value="1">
                        <textarea name="updated_comment" rows="4" class="w-full border rounded p-2 text-sm outline-none"><?php echo htmlspecialchars($full_comment); ?></textarea>
                        <button type="submit" name="save_edit" class="mt-2 text-gray-600 bg-gray-200 hover:bg-gray-300 py-1 px-3 rounded text-sm outline-none select-none">Save</button>
                        <button type="button" class="cancel-edit text-sm text-gray-500 ml-2 select-none">Cancel</button>
                    </form>
                </div>

                <?php
                $likeCount = 0;
                $dislikeCount = 0;
                $userReaction = null;
                if (isset($reviewID)) {
                    $countStmt = $connect->prepare("SELECT ReactionType, COUNT(*) as count FROM productreviewrttb WHERE ReviewID = ? GROUP BY ReactionType");
                    $countStmt->bind_param("i", $reviewID);
                    $countStmt->execute();
                    $result = $countStmt->get_result();
                    while ($row = $result->fetch_assoc()) {
                        if ($row['ReactionType'] == 'like') {
                            $likeCount = $row['count'];
                        } else {
                            $dislikeCount = $row['count'];
                        }
                    }
                    $countStmt->close();
                    if (isset($_SESSION['UserID'])) {
                        $userStmt = $connect->prepare("SELECT ReactionType FROM productreviewrttb WHERE ReviewID = ? AND UserID = ?");
                        $userStmt->bind_param("is", $reviewID, $_SESSION['UserID']);
                        $userStmt->execute();
                        $userResult = $userStmt->get_result();
                        if ($userResult->num_rows > 0) {
                            $userReaction = $userResult->fetch_assoc()['ReactionType'];
                        }
                        $userStmt->close();
                    }
                }
                ?>

                <form method="post" class="mt-3 text-gray-400 reaction-form">
                    <input type="hidden" name="review_id" value="<?= htmlspecialchars($reviewID) ?>">
                    <input type="hidden" name="product_id" value="<?= htmlspecialchars($product_id) ?>">
                    <button type="button" class="like-btn text-xs cursor-pointer <?= ($userReaction == 'like') ? 'text-gray-500' : '' ?>">
                        <i class="ri-thumb-up-<?= ($userReaction == 'like') ? 'fill' : 'line' ?> text-sm"></i>
                        <span class="like-count"><?= $likeCount ?></span> Like
                    </button>
                    <button type="button" class="dislike-btn text-xs cursor-pointer <?= ($userReaction == 'dislike') ? 'text-gray-500' : '' ?>">
                        <i class="ri-thumb-down-<?= ($userReaction == 'dislike') ? 'fill' : 'line' ?> text-sm"></i>
                        <span class="dislike-count"><?= $dislikeCount ?></span> Dislike
                    </button>
                </form>
            </div>
        </div>
<?php
    }
} else {
    echo "<p class='text-center text-gray-500 my-20'>No reviews available for this product.</p>";
}
?>

<!-- <script>
    // Review edit
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle edit form
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                let reviewId = this.getAttribute('data-review-id');
                let comment = this.getAttribute('data-comment');
                let form = document.querySelector(`.edit-form[data-review-id="${reviewId}"]`);
                form.querySelector('textarea').value = comment;
                form.classList.remove('hidden');
                document.querySelector('.review').classList.add('hidden');
            });
        });

        // Cancel edit
        document.querySelectorAll('.cancel-edit').forEach(btn => {
            btn.addEventListener('click', function() {
                this.closest('.edit-form').classList.add('hidden');
                document.querySelector('.review').classList.remove('hidden');
            });
        });
    });
</script> -->