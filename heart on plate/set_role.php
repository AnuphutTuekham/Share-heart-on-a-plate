<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// ถ้าต้องการอัปเดตลง DB เมื่อมีการล็อกอินเท่านั้น
require_once __DIR__ . '/sql/config.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'message' => 'Method not allowed']);
        exit;
    }

    $roleId = isset($_POST['role_id']) ? (int)$_POST['role_id'] : 0;
    if (!in_array($roleId, [1, 2, 3], true)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'role_id ไม่ถูกต้อง']);
        exit;
    }

    $_SESSION['role_id'] = $roleId;


    if (isset($_SESSION['user_id'])) {
        $stmt = $conn->prepare('UPDATE users SET role_id = ? WHERE user_id = ?');
        $stmt->execute([$roleId, $_SESSION['user_id']]);
    }

    echo json_encode(['ok' => true, 'role_id' => $roleId]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Server error']);
}