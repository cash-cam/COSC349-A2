

<?php
/* Dashboard.php
- Once a student login they are redirected to this page
- Page shows there current results from each paper they are enrolled in
- 
*/
session_start();
if (empty($_SESSION['student_id'])) {
  header('Location: index.php');
  exit;
}
require __DIR__.'/db.php';

$sid = $_SESSION['student_id'];

// Detailed per-assessment view for this student:
// This pdo statement is AI generated
$details = $pdo->prepare("
SELECT a.paper_code,
       a.name AS assessment,
       a.type,
       a.weight,
       a.max_points,
       g.points,
       ROUND((g.points / a.max_points) * 100, 2) AS pct_raw,
       ROUND(((g.points / a.max_points) * a.weight) * 100, 2) AS pct_weighted
FROM enrolments e
JOIN assessments a
  ON a.paper_code = e.paper_code
LEFT JOIN grades g
  ON g.student_id = e.student_id AND g.assessment_id = a.id
WHERE e.student_id = ?
ORDER BY a.paper_code, a.id
");
$details->execute([$sid]);
$rows = $details->fetchAll();

$student_name = $pdo->prepare("SELECT name FROM students WHERE student_id = ?");
$student_name->execute([$sid]);
$name = $student_name->fetchColumn() ?: 'Student';


// Per-paper totals for this student (weighted %):
$totals = $pdo->prepare("
SELECT a.paper_code,
       ROUND(SUM(COALESCE(g.points,0)/a.max_points * a.weight) * 100, 2) AS total_pct
FROM enrolments e
JOIN assessments a ON a.paper_code = e.paper_code
LEFT JOIN grades g  ON g.student_id = e.student_id AND g.assessment_id = a.id
WHERE e.student_id = ?
GROUP BY a.paper_code
ORDER BY a.paper_code
");
$totals->execute([$sid]);
$paperTotals = $totals->fetchAll();

// Class averages per paper (weighted % across all enrolled students):
$classAvg = $pdo->query("
WITH student_totals AS (
  SELECT e.student_id, a.paper_code,
         SUM(COALESCE(g.points,0)/a.max_points * a.weight) AS weighted
  FROM enrolments e
  JOIN assessments a ON a.paper_code = e.paper_code
  LEFT JOIN grades g ON g.student_id = e.student_id AND g.assessment_id = a.id
  GROUP BY e.student_id, a.paper_code
)
SELECT paper_code,
       ROUND(AVG(weighted) * 100, 2) AS class_avg_pct
FROM student_totals
GROUP BY paper_code
ORDER BY paper_code
");
$classAverages = $classAvg->fetchAll();
?>
<!DOCTYPE html>
<html>
<head><title>My Results</title></head>
<body>
    <h1>Welcome back <?= htmlspecialchars($name) ?></h1>

  <h2 style="padding: 5px">My assessments (all papers) </h2>
  <table border="1" style="background-color:grey; left-margin: 5px;">
    <tr>
      <th>Paper</th><th>Assessment</th><th>Type</th>
      <th>Weight</th><th>Max</th><th>My Points</th>
      <th>% (raw)</th><th>% (weighted)</th>
    </tr>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= htmlspecialchars($r['paper_code']) ?></td>
        <td><?= htmlspecialchars($r['assessment']) ?></td>
        <td><?= htmlspecialchars($r['type']) ?></td>
        <td><?= htmlspecialchars($r['weight']) ?></td>
        <td><?= htmlspecialchars($r['max_points']) ?></td>
        <td><?= htmlspecialchars($r['points'] ?? 0) ?></td>
        <td><?= htmlspecialchars($r['pct_raw'] ?? 0) ?></td>
        <td><?= htmlspecialchars($r['pct_weighted'] ?? 0) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>

  <h2>My totals per paper (weighted %)</h2>
  <table border="1" style="background-color:grey">
    <tr><th>Paper</th><th>My total %</th></tr>
    <?php foreach ($paperTotals as $t): ?>
      <tr>
        <td><?= htmlspecialchars($t['paper_code']) ?></td>
        <td><?= htmlspecialchars($t['total_pct']) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>

  <h2>Class averages per paper (weighted %)</h2>
  <table border="1" style="background-color:grey">
    <tr><th>Paper</th><th>Class average %</th></tr>
    <?php foreach ($classAverages as $c): ?>
      <tr>
        <td><?= htmlspecialchars($c['paper_code']) ?></td>
        <td><?= htmlspecialchars($c['class_avg_pct']) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
  <p><a href= "logout.php">Logout</p
</body>
</html>
