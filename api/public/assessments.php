<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../common/db.php';

$paper = trim($_GET['paper_code'] ?? '');

try {
  if ($paper !== '') {
    // Return assessments for a single paper
    $sql = "SELECT id, paper_code, name, type, weight, max_points,
                   COALESCE(due_date,'0000-00-00') AS due_date
            FROM assessments
            WHERE paper_code = ?
            ORDER BY id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$paper]);
  } else {
    // Return ALL assessments (used by admin dropdown)
    $sql = "SELECT id, paper_code, name, type, weight, max_points,
                   COALESCE(due_date,'0000-00-00') AS due_date
            FROM assessments
            ORDER BY paper_code, id";
    $stmt = $pdo->query($sql);
  }

  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
  echo json_encode(['assessments' => $rows], JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
  error_log('assessments.php error: '.$e->getMessage());
  http_response_code(500);
  echo json_encode(['error' => 'INTERNAL']);
}