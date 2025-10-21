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

    $projectId = isset($_POST['project_id']) ? (int)$_POST['project_id'] : 0;
    $status    = isset($_POST['status']) ? strtolower(trim($_POST['status'])) : '';

    if ($projectId <= 0 || !in_array($status, ['open', 'close'], true)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'Invalid input']);
        exit;
    }

    $stmt = $conn->prepare('UPDATE projects SET status = :status WHERE project_id = :id');
    $stmt->execute([':status' => $status, ':id' => $projectId]);

    echo json_encode(['ok' => true]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Server error', 'detail' => $e->getMessage()]);
}