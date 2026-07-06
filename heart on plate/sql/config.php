<?php
$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'heart';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

require_once __DIR__ . '/session_handler.php';
initDbSession($conn);
session_start();