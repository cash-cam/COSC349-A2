<?php
// /var/www/html/public/admin_create_paper.php
header('Content-Type: application/json');
require_once __DIR__ . '/../common/db.php';

$admin_id = trim($_POST['admin_id'] ?? '');
if ($admin_id === '') { http_response_code(401); echo json_encode(['error'=>'UNAUTHORIZED']); exit; }

$chk = $pdo->prepare('SELECT 1 FROM administrators WHERE admin_id = ?');
$chk->execute([$admin_id]);
if (!$chk->fetchColumn()) { http_response_code(401); echo json_encode(['error'=>'UNAUTHORIZED']); exit; }

$code = strtoupper(trim($_POST['code'] ?? ''));
$name = trim($_POST['name'] ?? '');

if ($code === '' || $name === '') {
  http_response_code(400);
  echo json_encode(['error' => 'MISSING_FIELDS', 'need' => ['code','name']]);
  exit;
}

try {
  $sql = "INSERT INTO papers(code, name)
          VALUES (:c, :n)
          ON DUPLICATE KEY UPDATE name = VALUES(name)";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([':c'=>$code, ':n'=>$name]);

  echo json_encode(['ok'=>true, 'paper'=>['code'=>$code, 'name'=>$name]]);
} catch (Throwable $e) {
  error_log('admin_create_paper: '.$e->getMessage());
  http_response_code(500);
  echo json_encode(['error'=>'INTERNAL']);
}