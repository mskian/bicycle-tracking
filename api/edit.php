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

if (!$input || !isset($input['ride_id'], $input['name'], $input['distance'], $input['date'], $input['username'])) {
    sendResponse(400, 'Invalid input');
    exit;
}

$ride_id = filter_var($input['ride_id'], FILTER_VALIDATE_INT);
$name = substr(filter_var(trim($input['name']), FILTER_SANITIZE_SPECIAL_CHARS), 0, 255);
$distance = filter_var($input['distance'], FILTER_VALIDATE_FLOAT);
$date = filter_var($input['date'], FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
$username = substr(filter_var(trim($input['username']), FILTER_SANITIZE_SPECIAL_CHARS), 0, 255);

if ($ride_id === false || $ride_id <= 0 || $name === '' || $distance === false || $distance <= 0 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || $username === '') {
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

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM ride WHERE name = :name AND distance = :distance AND date = :date AND username = :username AND id != :ride_id');
    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
    $stmt->bindParam(':distance', $distance, PDO::PARAM_STR);
    $stmt->bindParam(':date', $date, PDO::PARAM_STR);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->bindParam(':ride_id', $ride_id, PDO::PARAM_INT);
    $stmt->execute();
    $duplicateCount = $stmt->fetchColumn();

    if ($duplicateCount > 0) {
        sendResponse(409, 'Duplicate ride data');
        exit;
    }

    $stmt = $pdo->prepare('UPDATE ride SET name = :name, distance = :distance, date = :date WHERE id = :ride_id AND username = :username');
    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
    $stmt->bindParam(':distance', $distance, PDO::PARAM_STR);
    $stmt->bindParam(':date', $date, PDO::PARAM_STR);
    $stmt->bindParam(':ride_id', $ride_id, PDO::PARAM_INT);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        sendResponse(200, 'Ride updated successfully');
    } else {
        sendResponse(304, 'No changes made to the ride data');
    }

} catch (\PDOException $e) {
    sendResponse(500, 'An error occurred while processing your request: ' . $e->getMessage());
}

?>