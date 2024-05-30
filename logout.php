<?php

include __DIR__ . '/./api/utils.php';

header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Strict-Transport-Security: max-age=63072000');
header('X-Robots-Tag: noindex, nofollow', true);

session_start();

if(session_status() === PHP_SESSION_ACTIVE) {

    $_SESSION = [];

    session_destroy();

    sendResponse(200, "Logout successful.");

} else {
    sendResponse(200, "No active session to logout from.");
}

?>