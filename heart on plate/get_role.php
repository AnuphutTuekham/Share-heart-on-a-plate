<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
  'role_id' => isset($_SESSION['role_id']) ? (int)$_SESSION['role_id'] : 0
]);