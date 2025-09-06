<?php
session_start();
require_once('../config/db_connection.php');

$response = ['success' => false, 'message' => '', 'likeCount' => 0, 'dislikeCount' => 0, 'status' => ''];

if (!isset($_SESSION['UserID'])) {
    $response['status'] = "not_logged_in";
    echo json_encode($response);
    exit();
}

if (isset($_POST['review_id'], $_POST['product_id'], $_POST['reaction_type'])) {
    $reviewID = filter_input(INPUT_POST, 'review_id', FILTER_VALIDATE_INT);
    $product_id = $connect->real_escape_string($_POST['product_id']);
    $userID = $_SESSION['UserID'];
    $newReactionType = $_POST['reaction_type'] === 'like' ? 'like' : 'dislike';

    // Check if user already reacted
    $checkStmt = $connect->prepare("SELECT ReactionType FROM productreviewrttb WHERE ReviewID = ? AND UserID = ?");
    $checkStmt->bind_param("is", $reviewID, $userID);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows > 0) {
        $existingReaction = $result->fetch_assoc()['ReactionType'];
        if ($existingReaction == $newReactionType) {
            // Remove reaction
            $deleteStmt = $connect->prepare("DELETE FROM productreviewrttb WHERE ReviewID = ? AND UserID = ?");
            $deleteStmt->bind_param("is", $reviewID, $userID);
            $deleteStmt->execute();
            $deleteStmt->close();
        } else {
            // Update reaction
            $updateStmt = $connect->prepare("UPDATE productreviewrttb SET ReactionType = ? WHERE ReviewID = ? AND UserID = ?");
            $updateStmt->bind_param("sis", $newReactionType, $reviewID, $userID);
            $updateStmt->execute();
            $updateStmt->close();
        }
    } else {
        // Insert new reaction
        $insertStmt = $connect->prepare("INSERT INTO productreviewrttb (ReviewID, UserID, ReactionType) VALUES (?, ?, ?)");
        $insertStmt->bind_param("iss", $reviewID, $userID, $newReactionType);
        $insertStmt->execute();
        $insertStmt->close();
    }

    $checkStmt->close();

    // Get updated counts
    $likeResult = $connect->query("SELECT COUNT(*) as cnt FROM productreviewrttb WHERE ReviewID = $reviewID AND ReactionType = 'like'");
    $dislikeResult = $connect->query("SELECT COUNT(*) as cnt FROM productreviewrttb WHERE ReviewID = $reviewID AND ReactionType = 'dislike'");
    $response['likeCount'] = $likeResult->fetch_assoc()['cnt'];
    $response['dislikeCount'] = $dislikeResult->fetch_assoc()['cnt'];
    $response['success'] = true;
    $response['message'] = 'Reaction updated';
}

echo json_encode($response);
