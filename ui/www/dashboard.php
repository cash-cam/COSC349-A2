<?php
require __DIR__ . '/config.php';
require __DIR__ . '/lib/api_client.php';

$student_id = $_REQUEST['student_id'] ?? '';
if (!$student_id) { header('Location: /'); exit; }

list($status, $json) = api_get('grades.php', ['student_id' => $student_id]);
$data = json_decode($json, true);
$rows = ($status === 200 && is_array($data) && isset($data['grades']) && is_array($data['grades'])) ? $data['grades'] : [];
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Grades for <?= htmlspecialchars($student_id) ?></title>
  <style>
    :root { --bg:#f6f8fb; --card:#fff; --text:#1f2937; --muted:#6b7280; --brand:#2563eb; --brand-ink:#1e40af; --line:#e5e7eb; --row:#fafbff; }
    * { box-sizing: border-box; }
    body { margin:0; font-family: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica, Arial, sans-serif; background: var(--bg); color: var(--text); }
    .container { max-width: 920px; margin: 48px auto; padding: 0 20px; }
    .card { background: var(--card); border: 1px solid var(--line); border-radius: 10px; box-shadow: 0 6px 20px rgba(0,0,0,0.06); overflow: hidden; }
    .card-header { padding: 22px 24px; border-bottom: 1px solid var(--line); display:flex; align-items:center; justify-content:space-between; }
    h1 { font-size: 22px; margin: 0; font-weight: 600; }
    .muted { color: var(--muted); font-size: 14px; }
    .actions a { text-decoration: none; color: var(--brand); font-weight: 600; }
    .actions a:hover { color: var(--brand-ink); }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 12px 14px; border-bottom: 1px solid var(--line); text-align: left; }
    thead th { font-size: 13px; letter-spacing: .02em; text-transform: uppercase; color: var(--muted); background: #f9fafb; }
    tbody tr:nth-child(even) { background: var(--row); }
    tbody tr:hover { background: #eef2ff; }
    .right { text-align: right; white-space: nowrap; }
    .empty { padding: 24px; text-align:center; color: var(--muted); }
    .pill { display:inline-block; padding: 2px 8px; border-radius: 999px; font-size: 12px; background:#eef2ff; color:#3730a3; }
  </style>
</head>
<body>
  <div class="container">
    <div class="card">
      <div class="card-header">
        <h1>Grades for Student ID: <?= htmlspecialchars($student_id) ?></h1>
        <div class="actions"><a href="/">&larr; Back</a></div>
      </div>

      <?php if ($status !== 200): ?>
        <div class="empty">
          <div style="margin-bottom:8px; font-weight:600;">Upstream error</div>
          <div class="muted">Status: <?= (int)$status ?></div>
          <details style="margin-top:8px;">
            <summary class="muted">Show response</summary>
            <pre style="text-align:left; overflow:auto; white-space:pre-wrap; background:#f9fafb; padding:12px; border:1px solid var(--line); border-radius:8px;"><?= htmlspecialchars($json) ?></pre>
          </details>
        </div>
      <?php elseif (!$rows): ?>
        <div class="empty">No grades found for this student.</div>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>Paper</th>
              <th>Assessment</th>
              <th>Type</th>
              <th class="right">Weight</th>
              <th class="right">Points</th>
              <th class="right">Max</th>
              <th class="right">% Score</th>
              <th class="right">Weighted %</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $r): ?>
              <tr>
                <td><?= htmlspecialchars($r['paper_code']) ?> — <?= htmlspecialchars($r['paper_name']) ?></td>
                <td><?= htmlspecialchars($r['assessment_name']) ?> <span class="pill">#<?= (int)$r['assessment_id'] ?></span></td>
                <td><?= htmlspecialchars($r['assessment_type']) ?></td>
                <td class="right"><?= number_format((float)$r['weight'] * 100, 1) ?>%</td>
                <td class="right"><?= htmlspecialchars($r['points']) ?></td>
                <td class="right"><?= htmlspecialchars($r['max_points']) ?></td>
                <td class="right"><?= number_format((float)$r['score_pct'] * 100, 1) ?>%</td>
                <td class="right"><?= number_format((float)$r['weighted_pct'] * 100, 1) ?>%</td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>