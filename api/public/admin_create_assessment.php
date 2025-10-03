<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../common/db.php';

$admin_id = trim($_POST['admin_id'] ?? '');
if ($admin_id === '') { http_response_code(401); echo json_encode(['error'=>'UNAUTHORIZED']); exit; }
$chk = $pdo->prepare('SELECT 1 FROM administrators WHERE admin_id = ?');
$chk->execute([$admin_id]);
if (!$chk->fetchColumn()) { http_response_code(401); echo json_encode(['error'=>'UNAUTHORIZED']); exit; }

$paper_code = trim($_POST['paper_code'] ?? '');
$name       = trim($_POST['name'] ?? '');
$type       = trim($_POST['type'] ?? '');
$weight     = $_POST['weight'] ?? '';
$max_points = $_POST['max_points'] ?? '';
$due_date   = $_POST['due_date'] ?? null;

if ($paper_code === '' || $name === '' || $type === '' || $weight === '' || $max_points === '') {
  http_response_code(400);
  echo json_encode(['error' => 'MISSING_FIELDS']);
  exit;
}

try {
  $sql = 'INSERT INTO assessments (paper_code, name, type, weight, max_points, due_date)
          VALUES (:pc,:nm,:tp,:wt,:mx,:dd)
          ON DUPLICATE KEY UPDATE type=VALUES(type), weight=VALUES(weight),
                                  max_points=VALUES(max_points), due_date=VALUES(due_date)';
  $stmt = $pdo->prepare($sql);
  $stmt->execute([':pc'=>$paper_code, ':nm'=>$name, ':tp'=>$type, ':wt'=>$weight, ':mx'=>$max_points, ':dd'=>$due_date]);
  echo json_encode(['ok'=>true]);
} catch (Throwable $e) {
  error_log('admin_create_assessment: '.$e->getMessage());
  http_response_code(500);
  echo json_encode(['error'=>'INTERNAL','message'=>$e->getMessage()]);
}