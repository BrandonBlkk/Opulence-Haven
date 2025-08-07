<?php
function timeAgo($date)
{
    // Set timezone to Myanmar (Yangon)
    $timezone = new DateTimeZone('Asia/Yangon');

    // Create DateTime objects with Myanmar timezone
    $now = new DateTime('now', $timezone);
    $then = new DateTime($date, $timezone);
    $diff = $now->diff($then);

    if ($diff->y > 0) {
        return $diff->y == 1 ? '1 year ago' : $diff->y . ' years ago';
    } elseif ($diff->m > 0) {
        return $diff->m == 1 ? '1 month ago' : $diff->m . ' months ago';
    } elseif ($diff->d > 7) {
        $weeks = floor($diff->d / 7);
        return $weeks == 1 ? '1 week ago' : $weeks . ' weeks ago';
    } elseif ($diff->d > 0) {
        return $diff->d == 1 ? '1 day ago' : $diff->d . ' days ago';
    } elseif ($diff->h > 0) {
        return $diff->h == 1 ? '1 hour ago' : $diff->h . ' hours ago';
    } elseif ($diff->i > 0) {
        return $diff->i == 1 ? '1 minute ago' : $diff->i . ' minutes ago';
    } else {
        return 'Just now';
    }
}
