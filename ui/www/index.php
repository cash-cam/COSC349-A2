<?php
require __DIR__ . '/config.php';
?><!doctype html>
<html><head><meta charset="utf-8"><title>Student Portal</title>
<style>
  body {
    font-family: Arial, sans-serif;
    background-color: #f5f7fa;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
  }
  .container {
    background: #ffffff;
    padding: 2rem 3rem;
	margin-left: 4px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    text-align: center;
    width: 320px;
  }
  h1 {
    margin-bottom: 1.5rem;
    color: #333;
    font-weight: 600;
  }
  form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }
  label {
    font-weight: 500;
    color: #555;
    text-align: left;
    display: block;
  }
  input[name="student_id"] {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 1rem;
    box-sizing: border-box;
  }
    input[name="admin_id"] {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 1rem;
    box-sizing: border-box;
  }
  button {
    background-color: #007bff;
    color: white;
    border: none;
    padding: 0.75rem;
    border-radius: 4px;
    font-size: 1rem;
    cursor: pointer;
    transition: background-color 0.25s ease-in-out;
  }
  button:hover {
    background-color: #0056b3;
  }
</style>
</head>
<body>
  <div class="container">
    <h1>Student Portal</h1>
    <form method="get" action="dashboard.php">
      <label>Student ID: <input name="student_id" required></label>
      <button type="submit">View grades</button>
    </form>
  </div>
  	  <div class="container">
    <h1>Administrator Portal</h1>
    <form method="get" action="dashboard.php">
      <label>Admin ID: <input name="admin_id" required></label>
      <button type="submit">Update Grades</button>
    </form>
</body></html>