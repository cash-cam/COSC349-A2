<?php
require __DIR__ . '/config.php';
require __DIR__ . '/lib/api_client.php';

$student_id = $_GET['student_id'] ?? '';
if (!$student_id) { header('Location: /'); exit; }

[$status, $json] = api_get('grades.php', ['student_id'=>$student_id]);
$data = @json_decode($json, true);
if ($status !== 200) {
  http_response_code(502);
  echo "<h1>Upstream error</h1><pre>Status: $status\n$json</pre>";
  exit;
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Grades</title></head>
<body>
  <h1>Grades for <?= htmlspecialchars($data['student_id']) ?></h1>
  <table border="1" cellpadding="6"><tr><th>Course</th><th>Grade</th></tr>
  <?php foreach ($data['grades'] as $row): ?>
    <tr><td><?= htmlspecialchars($row['course_code']) ?></td><td><?= htmlspecialchars($row['grade']) ?></td></tr>
  <?php endforeach; ?>
  </table>
  <p><a href="/">Back</a></p>
</body></html>