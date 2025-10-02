<?php
require __DIR__ . '/config.php';
?><!doctype html>
<html><head><meta charset="utf-8"><title>Student Portal</title></head>
<body>
  <h1>Student Portal</h1>
  <form method="get" action="dashboard.php">
    <label>Student ID: <input name="student_id" required></label>
    <button type="submit">View grades</button>
  </form>
</body></html>