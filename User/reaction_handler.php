<?php
session_start();
require_once('../config/db_connection.php');

$response = ['success' => false, 'message' => '', 'likeCount' => 0, 'dislikeCount' => 0, 'status' => ''];

// Check if user is logged in
if (!isset($_SESSION['UserID'])) {
    $response['status'] = "not_logged_in";
    echo json_encode($response);
    exit();
}

if (isset($_POST['review_id'], $_POST['roomTypeID'], $_POST['reaction_type'])) {
    // Validate and sanitize input
    $reviewID = filter_input(INPUT_POST, 'review_id', FILTER_VALIDATE_INT);
    $roomTypeID = $connect->real_escape_string($_POST['roomTypeID']);
    $checkin_date = isset($_POST['checkin_date']) ? $connect->real_escape_string($_POST['checkin_date']) : '';
    $checkout_date = isset($_POST['checkout_date']) ? $connect->real_escape_string($_POST['checkout_date']) : '';
    $adults = isset($_POST['adults']) ? intval($_POST['adults']) : 1;
    $children = isset($_POST['children']) ? intval($_POST['children']) : 0;
    $userID = $_SESSION['UserID'];
    $newReactionType = $_POST['reaction_type'] === 'like' ? 'like' : 'dislike';

    // Check if user already reacted
    $checkStmt = $connect->prepare("SELECT ReactionType FROM roomtypereviewrttb WHERE ReviewID = ? AND UserID = ?");
    $checkStmt->bind_param("is", $reviewID, $userID);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows > 0) {
        $existingReaction = $result->fetch_assoc()['ReactionType'];

        if ($existingReaction == $newReactionType) {
            // Remove reaction
            $deleteStmt = $connect->prepare("DELETE FROM roomtypereviewrttb WHERE ReviewID = ? AND UserID = ?");
            $deleteStmt->bind_param("is", $reviewID, $userID);
            $deleteStmt->execute();
            $deleteStmt->close();
        } else {
            // Update reaction
            $updateStmt = $connect->prepare("UPDATE roomtypereviewrttb SET ReactionType = ? WHERE ReviewID = ? AND UserID = ?");
            $updateStmt->bind_param("sis", $newReactionType, $reviewID, $userID);
            $updateStmt->execute();
            $updateStmt->close();
        }
    } else {
        // Insert new reaction
        $insertStmt = $connect->prepare("INSERT INTO roomtypereviewrttb (ReviewID, UserID, ReactionType) VALUES (?, ?, ?)");
        $insertStmt->bind_param("iss", $reviewID, $userID, $newReactionType);
        $insertStmt->execute();
        $insertStmt->close();
    }

    $checkStmt->close();

    // Get updated counts
    $likeResult = $connect->query("SELECT COUNT(*) as cnt FROM roomtypereviewrttb WHERE ReviewID = $reviewID AND ReactionType = 'like'");
    $dislikeResult = $connect->query("SELECT COUNT(*) as cnt FROM roomtypereviewrttb WHERE ReviewID = $reviewID AND ReactionType = 'dislike'");
    $response['likeCount'] = $likeResult->fetch_assoc()['cnt'];
    $response['dislikeCount'] = $dislikeResult->fetch_assoc()['cnt'];
    $response['success'] = true;
    $response['message'] = 'Reaction updated';
}

echo json_encode($response);
