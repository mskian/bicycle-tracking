<?php

include 'utils.php';

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function checkSession() {
    if (!isset($_SESSION['username'])) {
        sendResponse(401, "unauthorized");
    }
}

?>