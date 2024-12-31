<?php
function uploadProductImage($imageFile, $currentImagePath)
{
    // Initialize an array to store errors
    $errors = [];

    // Check if a new image file is provided
    if (!empty($imageFile['name'])) {
        $targetDir = "AdminImages/";
        $uniqueFileName = uniqid() . "_" . basename($imageFile['name']);
        $targetFilePath = $targetDir . $uniqueFileName;

        // Upload the file to the server
        if (copy($imageFile['tmp_name'], $targetFilePath)) {
            return $targetFilePath; // Return the path of the uploaded file
        } else {
            $errors['image'] = "Cannot upload " . htmlspecialchars($imageFile['name']) . ".";
        }
    } else {
        // If no new image is uploaded, return the current image path
        return $currentImagePath;
    }
    return $errors;
}
