<?php
declare(strict_types=1);
require_once __DIR__ . '/sql/config.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Method not allowed');
    }

    session_start();
    $ownerId = isset($_SESSION['roleID']) ? (int)$_SESSION['roleID'] : ((isset($_SESSION['role_id']) ? (int)$_SESSION['role_id'] : 0));
    if ($ownerId <= 0) {
        throw new RuntimeException('กรุณาเลือกบทบาทก่อนสร้างโครงการ');
    }

    // รับค่าจากฟอร์ม
    $title       = trim($_POST['project_name'] ?? '');
    $endDate     = trim($_POST['end_date'] ?? '');
    $phone       = trim($_POST['phone'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $goal        = isset($_POST['goal']) ? (int)$_POST['goal'] : 0;
    $address     = trim($_POST['address'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($title === '' || $phone === '' || $email === '' || $goal <= 0 || $address === '' || $description === '') {
        throw new RuntimeException('กรอกข้อมูลให้ครบถ้วน');
    }
    if (!preg_match('/^[0-9]{9,10}$/', $phone)) {
        throw new RuntimeException('เบอร์โทรศัพท์ไม่ถูกต้อง');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('อีเมลไม่ถูกต้อง');
    }

    // อัปโหลดรูป (ถ้ามี)
    $imgPath = null;
    if (!empty($_FILES['img']['name']) && is_uploaded_file($_FILES['img']['tmp_name'])) {
        $allow = ['jpg','jpeg','png','webp'];
        $ext = strtolower(pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allow, true)) {
            throw new RuntimeException('ชนิดไฟล์ภาพไม่รองรับ');
        }
        if ($_FILES['img']['size'] > 5 * 1024 * 1024) {
            throw new RuntimeException('ไฟล์ภาพต้องไม่เกิน 5MB');
        }
        $dir = __DIR__ . '/uploads/projects';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $fname = 'proj_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        if (!move_uploaded_file($_FILES['img']['tmp_name'], $dir . '/' . $fname)) {
            throw new RuntimeException('อัปโหลดรูปไม่สำเร็จ');
        }
        $imgPath = 'uploads/projects/' . $fname;
    }

    // end_date เป็น NULL ได้ ถ้าไม่ได้เลือก
    $endDateTs = $endDate !== '' ? ($endDate . ' 23:59:59') : null;

    // ไม่ใส่ start_date (DEFAULT NULL) และส่งเฉพาะคอลัมน์ที่ต้องบันทึก
    $stmt = $conn->prepare("
        INSERT INTO projects (owner_id, title, img, description, goal, end_date, address, phone, email, status)
        VALUES (:owner_id, :title, :img, :description, :goal, :end_date, :address, :phone, :email, :status)
    ");
    $stmt->execute([
        ':owner_id'    => $ownerId,
        ':title'       => $title,
        ':img'         => $imgPath,
        ':description' => $description,
        ':goal'        => $goal,
        ':end_date'    => $endDate !== '' ? ($endDate . ' 23:59:59') : null,
        ':address'     => $address,
        ':phone'       => $phone,
        ':email'       => $email,
        ':status'      => 'pending',
    ]);

    header('Location: thank_you_project.html');
    exit;
} catch (Throwable $e) {
    error_log('Add project error: ' . $e->getMessage());
    $msg = urlencode($e->getMessage());
    header('Location: add_project.php?error=' . $msg);
    exit;
}