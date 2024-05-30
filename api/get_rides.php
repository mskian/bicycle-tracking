<?php

include 'config.php';
include 'session.php';

header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Strict-Transport-Security: max-age=63072000');
header('Content-Type: application/json');
header('X-Robots-Tag: noindex, nofollow', true);

if (!isset($_GET['username'])) {
    echo json_encode(['message' => 'Username parameter is missing']);
    exit;
}

$username = trim($_GET['username']);

if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    echo json_encode(['message' => 'Invalid username']);
    exit;
}

checkSession();

try {

    $stmt = $pdo->prepare('SELECT approved FROM users WHERE username = :username');
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || $user['approved'] != 1) {
        echo json_encode(['message' => 'User is not approved']);
        exit;
    }

    $stmt = $pdo->prepare('SELECT * FROM ride WHERE username = :username ORDER BY id DESC LIMIT 100');
    $stmt->bindValue(':username', $username, PDO::PARAM_STR);
    $stmt->execute();

    $rides = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($rides) > 0) {
        foreach ($rides as &$ride) {
            array_walk($ride, function (&$value) {
                $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            });
        }
        unset($ride);

        echo json_encode($rides);
    } else {
        echo json_encode(['message' => 'No rides found for the provided username']);
    }
} catch (\PDOException $e) {

    echo json_encode(['message' => 'An unexpected error occurred']);
}

?>