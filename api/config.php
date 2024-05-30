<?php

include 'store.php';

try {
    (new DevCoder\DotEnv(__DIR__ . '/../.env'))->load();
} catch (InvalidArgumentException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['message' => 'Environment configuration file not found']);
    exit;
}

$host = getenv('DBHOST') ?: 'localhost';
$dbname = getenv('DBNAME') ?: 'default_dbname';
$dbuser = getenv('DBUSER') ?: 'default_user';
$dbpassword = getenv('DBPASSWORD') ?: 'default_password';

$dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $host, $dbname);

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $dbuser, $dbpassword, $options);
} catch (\PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['message' => 'Internal Server Error']);
    exit;
}

?>