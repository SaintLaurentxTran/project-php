<?php
$dbHost = '127.0.0.1';
$dbName = 'shopeefake';
$dbUser = 'root';
$dbPass = '';
$dbCharset = 'utf8mb4';

$dsn = "mysql:host={$dbHost};dbname={$dbName};charset={$dbCharset}";
$options = [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
  $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
} catch (Throwable $e) {
  http_response_code(500);
  exit("DB connection failed: " . $e->getMessage());
}

define('JWT_SECRET', 'a7f9b2d4e8c156f3a9e2d7b4c6a8f1e0');
define('JWT_TIME_TO_LIVE', 3600);