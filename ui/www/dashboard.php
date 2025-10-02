<?php
require __DIR__ . '/config.php';
require __DIR__ . '/lib/api_client.php';

$student_id = $_GET['student_id'] ?? '';
if (!$student_id) { header('Location: /'); exit; }

list($status, $json) = api_get('grades.php', ['student_id' => $student_id]);
$data = json_decode($json, true);
if ($status !== 200 || !is_array($data)) {
  http_response_code(502);
  echo "<h1>Upstream error</h1><pre>Status: $status\n" . htmlspecialchars($json) . "</pre>";
  exit;
}

$rows = $data['grades'] ?? [];
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Grades for <?= htmlspecialchars($student_id) ?></title></head>
<body>
<h1>Grades for <?= htmlspecialchars($student_id) ?></h1>
<table border="1" cellpadding="6">
  <tr>
    <th>Paper</th><th>Assessment</th><th>Type</th>
    <th>Weight</th><th>Points</th><th>Max</th><th>%</th><th>Weighted %</th>
  </tr>
  <?php foreach ($rows as $r): ?>
  <tr>
    <td><?= htmlspecialchars($r['paper_code']) ?> — <?= htmlspecialchars($r['paper_name']) ?></td>
    <td><?= htmlspecialchars($r['assessment_name']) ?> (#<?= (int)$r['assessment_id'] ?>)</td>
    <td><?= htmlspecialchars($r['assessment_type']) ?></td>
    <td><?= number_format((float)$r['weight'] * 100, 1) ?>%</td>
    <td><?= htmlspecialchars($r['points']) ?></td>
    <td><?= htmlspecialchars($r['max_points']) ?></td>
    <td><?= number_format((float)$r['score_pct'] * 100, 1) ?>%</td>
    <td><?= number_format((float)$r['weighted_pct'] * 100, 1) ?>%</td>
  </tr>
  <?php endforeach; ?>
</table>
<p><a href="/">Back</a></p>
</body>
</html>