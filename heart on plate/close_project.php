<?php
// filepath: c:\D_drive\heart_on_plate\close_project.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/sql/config.php';
session_start();

$roleId = isset($_SESSION['roleID']) ? (int)$_SESSION['roleID'] : ((isset($_SESSION['role_id']) ? (int)$_SESSION['role_id'] : 0));

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'message' => 'Method not allowed']);
        exit;
    }

    $projectId = isset($_POST['project_id']) ? (int)$_POST['project_id'] : 0;
    if ($projectId <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'project_id ไม่ถูกต้อง']);
        exit;
    }

    if ($roleId === 3) {
        // แอดมิน ปิดได้ทุกโครงการ
        $stmt = $conn->prepare("
            UPDATE projects
            SET status = 'close'
            WHERE project_id = ? AND status <> 'close'
        ");
        $stmt->execute([$projectId]);
    } else {
        // อื่นๆ ปิดได้เฉพาะของตน (owner_id = roleId)
        $stmt = $conn->prepare("
            UPDATE projects
            SET status = 'close'
            WHERE project_id = ? AND owner_id = ? AND status <> 'close'
        ");
        $stmt->execute([$projectId, $roleId]);
    }

    echo json_encode(['ok' => $stmt->rowCount() > 0]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Server error']);
}
