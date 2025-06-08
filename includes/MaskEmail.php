<?php
function maskEmail($email, $visibleChars = 2)
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ''; // Return empty if invalid email
    }

    $parts = explode("@", $email);
    $username = $parts[0];
    $domain = $parts[1] ?? '';

    // Mask username
    $maskedUsername = substr($username, 0, $visibleChars)
        . str_repeat('*', max(0, strlen($username) - $visibleChars));

    return $maskedUsername . "@" . $domain;
}
