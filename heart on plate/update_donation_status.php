<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/sql/config.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'message' => 'Method not allowed']);
        exit;
    }

    $donationId = isset($_POST['donations_id']) ? (int)$_POST['donations_id'] : 0;
    $status     = isset($_POST['status']) ? trim($_POST['status']) : '';
    if ($donationId <= 0 || $status === '') {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'ข้อมูลไม่ถูกต้อง']);
        exit;
    }

    $ownerId = 3;

    $stmt = $conn->prepare("
        UPDATE donations d
        JOIN projects p ON d.project_id = p.project_id
        SET d.status = :status
        WHERE d.donations_id = :id AND p.owner_id = :ownerId
    ");
    $stmt->execute([
        ':status'  => $status,
        ':id'      => $donationId,
        ':ownerId' => $ownerId
    ]);

    if ($stmt->rowCount() === 0) {
        echo json_encode(['ok' => false, 'message' => 'ไม่พบข้อมูลหรือไม่มีสิทธิ์แก้ไข']);
        exit;
    }

    echo json_encode(['ok' => true]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Server error', 'detail' => $e->getMessage()]);
}
