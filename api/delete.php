<?php

include 'config.php';
include 'session.php';

header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Strict-Transport-Security: max-age=63072000');
header('X-Robots-Tag: noindex, nofollow', true);

$input = json_decode(file_get_contents('php://input'), true);

checkSession();

if (!$input || !isset($input['ride_id'], $input['username'])) {
    sendResponse(400, 'Invalid input');
    exit;
}

$ride_id = filter_var($input['ride_id'], FILTER_VALIDATE_INT);
$username = substr(filter_var(trim($input['username']), FILTER_SANITIZE_SPECIAL_CHARS), 0, 255);

if ($ride_id === false || $ride_id <= 0 || $username === '') {
    sendResponse(400, 'Invalid input');
    exit;
}

try {

    $stmt = $pdo->prepare('SELECT approved FROM users WHERE username = :username');
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || $user['approved'] != 1) {
        sendResponse(409, 'User is not approved');
        exit;
    }

    $stmt = $pdo->prepare('SELECT * FROM ride WHERE id = :ride_id AND username = :username');
    $stmt->bindParam(':ride_id', $ride_id, PDO::PARAM_INT);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
    $ride = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ride) {
        sendResponse(404, 'Ride not found');
        exit;
    }

    $stmt = $pdo->prepare('DELETE FROM ride WHERE id = :ride_id AND username = :username');
    $stmt->bindParam(':ride_id', $ride_id, PDO::PARAM_INT);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        sendResponse(200, 'Ride deleted successfully');
    } else {
        sendResponse(500, 'Failed to delete the ride');
    }

} catch (\PDOException $e) {
    sendResponse(500, 'An error occurred while processing your request: ' . $e->getMessage());
}

function sendResponse($status_code, $message) {
    http_response_code($status_code);
    echo json_encode(['message' => $message]);
}

?>