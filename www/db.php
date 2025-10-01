<?php
$DB_HOST   = '192.168.56.13';
$DB_NAME   = 'studentdata';
$DB_USER   = 'webuser';
$DB_PASSWD = 'testing_password';

$pdo = new PDO(
  "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
  $DB_USER,
  $DB_PASSWD,
  [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]
);