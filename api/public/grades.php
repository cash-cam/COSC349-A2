<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../common/db.php';

$student_id = trim($_GET['student_id'] ?? '');
if ($student_id === '') {
  http_response_code(400);
  echo json_encode(['error' => 'MISSING_student_id']);
  exit;
}
try {
  $sql = "
    SELECT
      g.assessment_id,
      a.paper_code,
      p.name  AS paper_name,
      a.name  AS assessment_name,
      a.type  AS assessment_type,
      a.weight,
      a.max_points,
      g.points,
      CASE
        WHEN a.max_points > 0 THEN g.points / a.max_points
        ELSE NULL
      END AS score_pct,
      CASE
        WHEN a.max_points > 0 THEN (g.points / a.max_points) * a.weight
        ELSE NULL
      END AS weighted_pct
    FROM grades g
    JOIN assessments a ON a.id   = g.assessment_id
    JOIN papers      p ON p.code = a.paper_code
    WHERE g.student_id = ?
    ORDER BY a.paper_code, a.id
  ";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([$student_id]);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode(['grades' => $rows], JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
  error_log('grades.php error: ' . $e->getMessage());
  http_response_code(500);
  echo json_encode(['error' => 'INTERNAL']);
}