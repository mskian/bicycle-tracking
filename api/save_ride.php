<?php

include 'config.php';
include 'session.php';

header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Strict-Transport-Security: max-age=63072000');
//header('Content-Type: application/json');
header('X-Robots-Tag: noindex, nofollow', true);

$input = json_decode(file_get_contents('php://input'), true);

checkSession();

if (!$input || !isset($input['name'], $input['distance'], $input['date'], $input['username'])) {
    //echo json_encode(['message' => 'Invalid input']);
    sendResponse(400, 'Invalid input');
    exit;
}

$name = substr(filter_var(trim($input['name']), FILTER_SANITIZE_SPECIAL_CHARS), 0, 255);
$distance = filter_var($input['distance'], FILTER_VALIDATE_FLOAT);
$date = filter_var($input['date'], FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
$username = substr(filter_var(trim($input['username']), FILTER_SANITIZE_SPECIAL_CHARS), 0, 255);

if ($name === '' || $distance === false || $distance <= 0 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || $username === '') {
    //echo json_encode(['message' => 'Invalid input']);
    sendResponse(400, 'Invalid input');
    exit;
}

try {

    $stmt = $pdo->prepare('SELECT approved FROM users WHERE username = :username');
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || $user['approved'] != 1) {
        //echo json_encode(['message' => 'User is not approved']);
        sendResponse(409, 'User is not approved');
        exit;
    }

    $stmt = $pdo->prepare('INSERT INTO ride (name, distance, date, username) VALUES (:name, :distance, :date, :username)');
    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
    $stmt->bindParam(':distance', $distance, PDO::PARAM_STR);
    $stmt->bindParam(':date', $date, PDO::PARAM_STR);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
    
    //echo json_encode(['message' => 'Ride saved successfully']);
    sendResponse(200, 'Ride saved successfully');

} catch (\PDOException $e) {
    //echo json_encode(['message' => 'An error occurred while processing your request: ' . $e->getMessage()]);
    sendResponse(500, 'An error occurred while processing your request: ' . $e->getMessage());
}

?>