<?php
require_once 'sql/config.php';

$project_id = isset($_GET['project_id']) ? $_GET['project_id'] : null;

if (!$project_id) {
    throw new Exception('ไม่พบรหัสโครงการ');
}

try {
    // ดึงข้อมูลประเภทอาหาร
    $stmt = $conn->query("SELECT * FROM food_type");
    $foodTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Debug: ตรวจสอบข้อมูลที่ส่งมา
        error_log('POST data: ' . print_r($_POST, true));
        error_log('FILES data: ' . print_r($_FILES, true));

        // ตรวจสอบข้อมูลที่จำเป็น
        if (empty($_POST['food_name']) || empty($_POST['MFG']) || empty($_POST['food_type']) || empty($_POST['amount'])) {
            throw new Exception('กรุณากรอกข้อมูลให้ครบถ้วน');
        }

        // เตรียมข้อมูล
        $foodName = $_POST['food_name'];
        $mfgDate = $_POST['MFG'];
        $expDate = $_POST['EXP'] ?: null;
        $foodType = $_POST['food_type'];
        $amount = $_POST['amount'];
        
        // จัดการรูปภาพ
        $foodImg = null;
        if (isset($_FILES['food_img']) && $_FILES['food_img']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/uploads/';
            $imageFileName = uniqid() . '_' . basename($_FILES['food_img']['name']);
            $foodImg = 'uploads/' . $imageFileName;
            
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            if (!move_uploaded_file($_FILES['food_img']['tmp_name'], $uploadDir . $imageFileName)) {
                throw new Exception('ไม่สามารถอัพโหลดรูปภาพได้');
            }
        }

        // เตรียม SQL ตามโครงสร้างตารางใน heart.sql
        $sql = "INSERT INTO donations (
                    food_name, 
                    MFG, 
                    EXP, 
                    food_type, 
                    amount,
                    food_img,
                    status,
                    project_id
                ) VALUES (
                    :food_name, 
                    :MFG, 
                    :EXP, 
                    :food_type, 
                    :amount,
                    :food_img,
                    'ยังไม่จัดส่ง',
                    :project_id
                )";
        
        // เตรียม statement
        $stmt = $conn->prepare($sql);
        
        // Execute with named parameters
        $success = $stmt->execute([
            ':food_name' => $foodName,
            ':MFG' => $mfgDate,
            ':EXP' => $expDate,
            ':food_type' => $foodType,
            ':amount' => $amount,
            ':food_img' => $foodImg,
            ':project_id' => $project_id
        ]);

        if ($success) {
            // เปลี่ยนปลายทางหลังส่งฟอร์ม
            header('Location: thank_you.html');
            exit;
        } else {
            throw new Exception('ไม่สามารถบันทึกข้อมูลได้');
        }
    }
} catch (Exception $e) {
    $error = $e->getMessage();
    error_log('Error: ' . $error);
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>กรอกข้อมูลอาหาร — แบ่งใจใส่จาน</title>
    <link rel="stylesheet" href="CSS/header.css">
    <link rel="stylesheet" href="CSS/add_food.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <!-- ใช้ Header ส่วนกลาง -->
    <header class="header"></header>

    <main class="add-main">
        <h2 class="page-title">กรอกข้อมูลการบริจาค</h2>

        <form class="food-form" action="add_food.php?project_id=<?php echo htmlspecialchars($project_id); ?>" method="post" enctype="multipart/form-data" aria-label="ฟอร์มกรอกข้อมูลอาหาร">
            <input type="hidden" name="project_id" value="<?php echo htmlspecialchars($project_id); ?>">

            <label>
                <span class="label-text">ชื่ออาหาร</span>
                <input name="food_name" type="text" placeholder="ชื่ออาหาร" required>
            </label>

            <label>
                <span class="label-text">วัน/เดือน/ปี ที่ผลิต</span>
                <input name="MFG" type="date" required>
            </label>

            <label>
                <span class="label-text">วัน/เดือน/ปี ที่หมดอายุ</span>
                <input name="EXP" type="date">
            </label>

            <label>
                <span class="label-text">ประเภทอาหาร</span>
                <select name="food_type" required>
                    <option value="" disabled selected>เลือกประเภทอาหาร</option>
                    <?php if (isset($foodTypes)): ?>
                        <?php foreach ($foodTypes as $type): ?>
                            <option value="<?php echo htmlspecialchars($type['food_type_id']); ?>">
                                <?php echo htmlspecialchars($type['Description']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </label>

            <label>
                <span class="label-text">จำนวน</span>
                <input name="amount" type="number" min="1" placeholder="จำนวน" required>
            </label>

            <label class="file-label">
                <span class="label-text">แนบภาพ</span>
                <input name="food_img" type="file" accept="image/*">
            </label>

            <div class="form-actions">
                <button type="button" class="back-button" onclick="history.back();">
                    <i class="fas fa-arrow-left" aria-hidden="true"></i> กลับ
                </button>
                <button type="submit" class="submit-button">เสร็จสิ้น</button>
            </div>
        </form>

        <?php if (isset($error)): ?>
            <div class="error-message" style="color: red; margin: 10px 0;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
    </main>

    <!-- สคริปต์โหลด Header ส่วนกลาง -->
    <script src="js/header.js"></script>
</body>
</html>