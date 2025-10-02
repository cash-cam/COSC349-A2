<?php
require_once __DIR__ . '/../common/db.php';
header('Content-Type: application/json');

$student_id = $_GET['student_id'] ?? '';
if ($student_id === '') {
  http_response_code(400);
  echo json_encode(['error' => 'MISSING_student_id']);
  exit;
}

try {
  $sql = <<<SQL
SELECT
  p.code           AS paper_code,
  p.name           AS paper_name,
  a.id             AS assessment_id,
  a.name           AS assessment_name,
  a.type           AS assessment_type,
  a.weight         AS weight,
  a.max_points     AS max_points,
  g.points         AS points,
  (g.points / a.max_points)                    AS score_pct,
  (g.points / a.max_points) * a.weight         AS weighted_pct
FROM grades g
JOIN assessments a  ON a.id = g.assessment_id
JOIN papers p       ON p.code = a.paper_code
WHERE g.student_id = :sid
ORDER BY p.code, a.id
SQL;

  $stmt = $pdo->prepare($sql);
  $stmt->execute([':sid' => $student_id]);
  $rows = $stmt->fetchAll();

  echo json_encode(['student_id' => $student_id, 'grades' => $rows], JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
  error_log('grades.php error: ' . $e->getMessage());
  http_response_code(500);
  echo json_encode(['error' => 'INTERNAL', 'message' => $e->getMessage()]);
}