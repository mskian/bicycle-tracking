<?php

function sanitizeInput($input) {
    $input = trim($input);
    $input = strip_tags($input);
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    return $input;
}

function sendResponse($status, $message) {
    header('Content-Type: application/json');
    http_response_code($status);
    echo json_encode(['status' => $status, 'message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

function validateInput($data, $fields) {
    foreach ($fields as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            return false;
        }
    }
    return true;
}

function validatePassword($password) {
    return strlen($password) >= 8;
}

function validateUsername($username) {
    $usernameLength = strlen($username);
    return $usernameLength >= 4 && $usernameLength <= 20;
}

?>