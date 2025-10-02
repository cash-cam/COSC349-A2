<?php
# Simple return for application
require_once __DIR__ . '/../common/db.php';
header('Content-Type: application/json'); 

$student_id = $_GET['student_id'] ?? null;
if (!$student_id) { http_response_code(400); echo json_encode(['error'=>'MISSING_student_id']); exit; }

$stmt = $pdo->prepare('SELECT course_code, grade FROM grades WHERE student_id = ? ORDER BY course_code');
$stmt->execute([$student_id]);
echo json_encode(['student_id'=>$student_id, 'grades'=>$stmt->fetchAll()]);
