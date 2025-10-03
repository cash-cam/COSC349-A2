<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../common/db.php';

$admin_id = trim($_POST['admin_id'] ?? '');
if ($admin_id === '') {
  http_response_code(400);
  echo json_encode(['error' => 'MISSING_admin_id']);
  exit;
}

$stmt = $pdo->prepare('SELECT 1 FROM administrators WHERE admin_id = ?');
$stmt->execute([$admin_id]);
$exists = (bool)$stmt->fetchColumn();

if (!$exists) {
  http_response_code(401);
  echo json_encode(['error' => 'UNKNOWN_ADMIN']);
  exit;
}

echo json_encode(['ok' => true, 'admin_id' => $admin_id]);