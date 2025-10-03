<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../common/db.php';

$admin_id      = trim($_POST['admin_id'] ?? '');
$student_id    = trim($_POST['student_id'] ?? '');
$assessment_id = (int)($_POST['assessment_id'] ?? 0);
$points        = $_POST['points'] ?? null;

if ($admin_id === '' || $student_id === '' || $assessment_id <= 0 || $points === null) {
  http_response_code(400);
  echo json_encode(['error' => 'MISSING_FIELDS']);
  exit;
}

try {
  // 1) Admin check
  $chk = $pdo->prepare('SELECT 1 FROM administrators WHERE admin_id = ?');
  $chk->execute([$admin_id]);
  if (!$chk->fetchColumn()) {
    http_response_code(401);
    echo json_encode(['error' => 'UNAUTHORIZED']);
    exit;
  }

  // 2) Assessment exists + paper_code
  $stmt = $pdo->prepare('SELECT paper_code FROM assessments WHERE id = ?');
  $stmt->execute([$assessment_id]);
  $paper_code = $stmt->fetchColumn();
  if (!$paper_code) {
    http_response_code(404);
    echo json_encode(['error' => 'ASSESSMENT_NOT_FOUND']);
    exit;
  }

  // 3) Auto-enrol if not already enrolled
  $en = $pdo->prepare('SELECT 1 FROM enrolments WHERE student_id=? AND paper_code=?');
  $en->execute([$student_id, $paper_code]);
  if (!$en->fetchColumn()) {
    $insEn = $pdo->prepare('INSERT INTO enrolments (student_id, paper_code) VALUES (?, ?)');
    $insEn->execute([$student_id, $paper_code]);
  }

  // 4) Upsert grade
  $up = $pdo->prepare('INSERT INTO grades (student_id, assessment_id, points)
                       VALUES (:sid,:aid,:pts)
                       ON DUPLICATE KEY UPDATE points = VALUES(points)');
  $up->execute([
    ':sid' => $student_id,
    ':aid' => $assessment_id,
    ':pts' => $points,
  ]);

  echo json_encode(['ok' => true, 'student_id'=>$student_id, 'assessment_id'=>$assessment_id, 'paper_code'=>$paper_code]);
} catch (Throwable $e) {
  error_log('admin_set_grade: '.$e->getMessage());
  http_response_code(500);
  echo json_encode(['error' => 'INTERNAL']);
}