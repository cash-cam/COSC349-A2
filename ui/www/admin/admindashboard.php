<?php
require __DIR__ . '/../config.php';
require __DIR__ . '/../lib/api_client.php';

/** --- load current assessments (for the grade dropdown) --- */
$assessments = [];
list($stA, $bodyA) = api_get('assessments.php');
if ($stA === 200) {
  $parsed = json_decode($bodyA, true);
  if (isset($parsed['assessments']) && is_array($parsed['assessments'])) {
    $assessments = $parsed['assessments'];
  }
}
$assByPaper = [];
foreach ($assessments as $a) { $assByPaper[$a['paper_code']][] = $a; }

$admin_id = trim($_GET['admin_id'] ?? '');
$msg = ''; $err = '';

/** --- verify admin id (nice UX if not an admin) --- */
if ($admin_id !== '') {
  list($st, $body) = api_post('admin_login.php', ['admin_id' => $admin_id]);
  if ($st !== 200) {
    $err = "Unknown admin_id (HTTP $st): $body";
    $admin_id = '';
  }
}

/** --- handle POST actions --- */
if ($admin_id !== '' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';

  if ($action === 'create_paper') {
    // Create / update a paper
    list($st, $body) = api_post('admin_create_paper.php', [
      'admin_id' => $admin_id,
      'code'     => $_POST['paper_code_new'] ?? '',
      'name'     => $_POST['paper_name_new'] ?? '',
    ]);
    if ($st === 200) {
      $msg = 'Paper created/updated.';
      // refresh assessments so the new paper section shows immediately
      list($stA, $bodyA) = api_get('assessments.php');
      if ($stA === 200) {
        $parsed = json_decode($bodyA, true);
        $assessments = (isset($parsed['assessments']) && is_array($parsed['assessments'])) ? $parsed['assessments'] : [];
        $assByPaper = [];
        foreach ($assessments as $a) { $assByPaper[$a['paper_code']][] = $a; }
      }
    } else {
      $err = "Paper save failed (HTTP $st): $body";
    }
  }

  if ($action === 'create_assessment') {
    list($st, $body) = api_post('admin_create_assessment.php', [
      'admin_id'   => $admin_id,
      'paper_code' => $_POST['paper_code'] ?? '',
      'name'       => $_POST['name'] ?? '',
      'type'       => $_POST['type'] ?? '',
      'weight'     => $_POST['weight'] ?? '',
      'max_points' => $_POST['max_points'] ?? '',
      'due_date'   => $_POST['due_date'] ?? '',
    ]);
    if ($st === 200) {
      $msg = 'Assessment created/updated.';
      // refresh assessments so dropdown includes the new one
      list($stA, $bodyA) = api_get('assessments.php');
      if ($stA === 200) {
        $parsed = json_decode($bodyA, true);
        $assessments = (isset($parsed['assessments']) && is_array($parsed['assessments'])) ? $parsed['assessments'] : [];
        $assByPaper = [];
        foreach ($assessments as $a) { $assByPaper[$a['paper_code']][] = $a; }
      }
    } else {
      $err = "Create failed (HTTP $st): $body";
    }
  }

  if ($action === 'set_grade') {
    list($st, $body) = api_post('admin_set_grade.php', [
      'admin_id'      => $admin_id,
      'student_id'    => $_POST['student_id'] ?? '',
      'assessment_id' => $_POST['assessment_id'] ?? '',
      'points'        => $_POST['points'] ?? '',
    ]);
    if ($st === 200) {
      $msg = 'Grade set/updated.';
    } else {
      $err = "Update failed (HTTP $st): $body";
    }
  }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Admin Dashboard</title>
  <style>
    :root { --bg:#f6f8fb; --card:#fff; --text:#1f2937; --muted:#6b7280; --brand:#2563eb; --brand-ink:#1e40af; --line:#e5e7eb; }
    *{box-sizing:border-box} body{margin:0;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica,Arial,sans-serif;background:var(--bg);color:var(--text)}
    .container{max-width:980px;margin:6vh auto;padding:0 20px}
    .top{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px}
    a{color:var(--brand);text-decoration:none} a:hover{color:var(--brand-ink)}
    .card{background:var(--card);border:1px solid var(--line);border-radius:10px;box-shadow:0 8px 26px rgba(0,0,0,.06); margin-bottom:18px}
    .card h2{margin:0;padding:16px 18px;border-bottom:1px solid var(--line);font-size:18px}
    .card .body{padding:16px 18px}
    label{display:block;font-size:14px;margin:10px 0 6px;color:#374151}
    input,select{width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:8px}
    button{margin-top:12px;padding:10px 14px;border:none;border-radius:8px;background:var(--brand);color:#fff;font-weight:600;cursor:pointer}
    button:hover{background:var(--brand-ink)}
    .banner{margin-bottom:12px;padding:10px 14px;border-radius:8px}
    .ok{background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0}
    .err{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}
    .muted{color:var(--muted);font-size:13px}
    .grid{display:grid;grid-template-columns:1fr 1fr;gap:20px}
    @media (max-width:900px){ .grid{grid-template-columns:1fr} }
  </style>
</head>
<body>
  <div class="container">
    <div class="top">
      <h1>Admin Dashboard</h1>
      <div class="muted"><a href="/">Home</a></div>
    </div>

    <?php if ($msg): ?><div class="banner ok"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <?php if ($err): ?><div class="banner err"><?= htmlspecialchars($err) ?></div><?php endif; ?>

    <?php if ($admin_id === ''): ?>
      <div class="card">
        <h2>Enter Admin ID</h2>
        <div class="body">
          <form method="get">
            <label>Admin ID</label>
            <input name="admin_id" placeholder="e.g. A0000001" required>
            <button type="submit">Continue</button>
          </form>
          <p class="muted">We’ll validate this against the administrators table via the API.</p>
        </div>
      </div>
    <?php else: ?>
      <div class="grid">
        <!-- NEW: Create / Update Paper -->
        <div class="card">
          <h2>Create / Update Paper</h2>
          <div class="body">
            <form method="post">
              <input type="hidden" name="action" value="create_paper">
              <input type="hidden" name="admin_id" value="<?= htmlspecialchars($admin_id) ?>">
              <label>Paper code</label>
              <input name="paper_code_new" placeholder="e.g. BSNS114" required>
              <label>Paper name</label>
              <input name="paper_name_new" placeholder="e.g. Business Systems 1" required>
              <button type="submit">Save paper</button>
            </form>
            <p class="muted">API: <code><?= htmlspecialchars(getenv('API_BASE_URL') ?: 'NOT SET') ?>/admin_create_paper.php</code></p>
          </div>
        </div>

        <!-- Create / Update Assessment -->
        <div class="card">
          <h2>Create / Update Assessment</h2>
          <div class="body">
            <form method="post">
              <input type="hidden" name="action" value="create_assessment">
              <input type="hidden" name="admin_id" value="<?= htmlspecialchars($admin_id) ?>">
              <label>Paper code</label>
              <input name="paper_code" placeholder="e.g. COSC349" required>
              <label>Name</label>
              <input name="name" placeholder="e.g. A2 Project" required>
              <label>Type</label>
              <select name="type" required>
                <option value="lab">lab</option>
                <option value="assignment">assignment</option>
                <option value="test">test</option>
                <option value="exam">exam</option>
              </select>
              <label>Weight (0..1)</label>
              <input name="weight" type="number" step="0.001" min="0" max="1" required>
              <label>Max points</label>
              <input name="max_points" type="number" step="0.01" min="0.01" required>
              <label>Due date</label>
              <input name="due_date" type="date">
              <button type="submit">Save assessment</button>
            </form>
            <p class="muted">API: <code><?= htmlspecialchars(getenv('API_BASE_URL') ?: 'NOT SET') ?>/admin_create_assessment.php</code></p>
          </div>
        </div>

        <!-- Set / Update Grade -->
        <div class="card">
          <h2>Set / Update Grade</h2>
          <div class="body">
            <?php if (empty($assessments)): ?>
              <p class="muted" style="margin-top:0">No assessments found yet. Create one first.</p>
            <?php endif; ?>
            <form method="post">
              <input type="hidden" name="action" value="set_grade">
              <input type="hidden" name="admin_id" value="<?= htmlspecialchars($admin_id) ?>">

              <label>Student ID</label>
              <input name="student_id" placeholder="e.g. S0000001" required>

              <label>Assessment (paper — name)</label>
              <select name="assessment_id" required>
                <option value="" disabled selected>Select an assessment…</option>
                <?php foreach ($assByPaper as $paper => $items): ?>
                  <optgroup label="<?= htmlspecialchars($paper) ?>">
                    <?php foreach ($items as $a): ?>
                      <?php
                        $label = sprintf('%s — %s (%s) [id #%d]',
                          $a['paper_code'], $a['name'], $a['type'], (int)$a['id']);
                      ?>
                      <option value="<?= (int)$a['id'] ?>"><?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                  </optgroup>
                <?php endforeach; ?>
              </select>

              <label>Points</label>
              <input name="points" type="number" step="0.01" min="0" required>

              <button type="submit">Save grade</button>
            </form>
            <p class="muted">API: <code><?= htmlspecialchars(getenv('API_BASE_URL') ?: 'NOT SET') ?>/admin_set_grade.php</code></p>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>