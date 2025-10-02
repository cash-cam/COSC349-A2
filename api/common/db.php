<?php
$DB_HOST = getenv('DB_HOST');
$DB_NAME = getenv('DB_NAME');
$DB_USER = getenv('DB_USER');
$DB_PASS = getenv('DB_PASS');

try {
  $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
                 $DB_USER, $DB_PASS, [
                   PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                   PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                 ]);
} catch (Throwable $e) {
  http_response_code(500);
  header('Content-Type: application/json');
  echo json_encode(['error' => 'DB_CONNECT_FAIL', 'message' => $e->getMessage()]);
  exit;
}