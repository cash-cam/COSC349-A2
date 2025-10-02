<?php
session_start();
require __DIR__. '/db.php';

$email = $_POST['email'];
$student_id = $_POST['student_id'];

if (!$email || !$student_id) {
    http_response_code(400);
    exit('Missing email or student ID');
  }

$valid_student_statement = $pdo->prepare("SELECT student_id, email FROM students WHERE student_id = ? AND email = ?");
$valid_student_statement ->execute([$student_id, $email]);
$user = $valid_student_statement->fetch();

if(!$user){
    http_response_code(401);
    exit('Invalid Credintials');
}

$_SESSION['student_id'] = $user['student_id'];
$_SESSION['name'] = $user['name'];
header('Location: dashboard.php');
