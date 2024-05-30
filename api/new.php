<?php
include 'config.php';
include 'utils.php';

header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Strict-Transport-Security: max-age=63072000');
header('X-Robots-Tag: noindex, nofollow', true);

function verifyApiKey($apiKey) {
    $validApiKey = getenv('APIKEY');
    return hash_equals($validApiKey, $apiKey);
}

function validateToken() {
    $headers = getallheaders();
    if (isset($headers['Authorization'])) {
        $authHeader = $headers['Authorization'];
        if (strpos($authHeader, 'Bearer ') === 0) {
            $token = substr($authHeader, 7);
            return verifyApiKey($token);
        }
    }
    return false;
}

if (!validateToken()) {
    sendResponse(401, "Unauthorized");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!validateInput($input, ['username', 'password'])) {
        sendResponse(400, "Invalid input.");
    }

    $username = sanitizeInput($input['username']);
    $password = $input['password'];

    if (!validatePassword($password)) {
        sendResponse(400, "Password must be at least 8 characters long.");
    }

    if (!validateusername ($username)) {
        sendResponse(400, "Username must be between 4 and 20 characters long.");
    }

    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    try {

        $stmt = $pdo->prepare('INSERT INTO users (username, password) VALUES (:username, :password)');
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':password', $passwordHash, PDO::PARAM_STR);

        if ($stmt->execute()) {
            sendResponse(201, "Registration successful.");
        } else {
            sendResponse(500, "Registration failed. Please try again later.");
        }
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            sendResponse(409, "Username already exists.");
        } else {
            sendResponse(500, "internal server error");
        }
    }
} else {
    sendResponse(405, "Method not allowed.");
}
?>