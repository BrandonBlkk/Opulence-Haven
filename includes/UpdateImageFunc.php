<?php
function uploadProductImage($imageFile, $currentImagePath)
{
    // Initialize an array to store errors
    $errors = [];

    // Check if a new image file is provided
    if (!empty($imageFile['name'])) {
        $targetDirAdmin = "AdminImages/";
        $targetDirUser = "../UserImages/"; // Save in UserImages folder

        $uniqueFileName = uniqid() . "_" . basename($imageFile['name']);
        $targetFilePathAdmin = $targetDirAdmin . $uniqueFileName;
        $targetFilePathUser = $targetDirUser . $uniqueFileName;

        // Upload the file to both directories
        if (copy($imageFile['tmp_name'], $targetFilePathAdmin) && copy($imageFile['tmp_name'], $targetFilePathUser)) {
            return [
                'adminPath' => $targetFilePathAdmin,
                'userPath' => $targetFilePathUser
            ]; // Return both paths
        } else {
            $errors['image'] = "Cannot upload " . htmlspecialchars($imageFile['name']) . ".";
        }
    } else {
        // If no new image is uploaded, return the current image paths
        return [
            'adminPath' => $currentImagePath,
            'userPath' => str_replace("AdminImages/", "../UserImages/", $currentImagePath)
        ];
    }
    return $errors;
}
