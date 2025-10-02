<?php
require_once __DIR__ . '/../common/db.php';
header('Content-Type: application/json');
try {
  $pdo->query('SELECT 1');
  echo json_encode(['php'=>'ok', 'db'=>'ok']);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['php'=>'ok', 'db'=>'fail', 'message'=>$e->getMessage()]);
}