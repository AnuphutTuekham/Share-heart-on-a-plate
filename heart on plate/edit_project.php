<?php
require_once __DIR__ . '/sql/config.php';

$projectId = isset($_GET['project_id']) ? (int)$_GET['project_id'] : 0;
$ownerId = 3; // หรือจาก session

if ($projectId <= 0) {
    exit('ไม่พบรหัสโครงการ');
}

try {
    // ดึงข้อมูลโครงการ (ตรวจว่าเป็นของ owner_id = 3) ไม่เช็ค status
    $stmt = $conn->prepare("SELECT * FROM projects WHERE project_id = ? AND owner_id = ?");
    $stmt->execute([$projectId, $ownerId]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$project) {
        exit('ไม่พบโครงการหรือไม่มีสิทธิ์แก้ไข');
    }

    // แยก address เดิม (สมมติรูปแบบ: ต.xxx อ.xxx จ.xxx xxxxx)
    $address = $project['address'] ?? '';
    $district = $amphoe = $province = $zipcode = '';
    if (preg_match('/ต\.([^\s]+)\s+อ\.([^\s]+)\s+จ\.([^\s]+)\s+(\d{5})/', $address, $m)) {
        $district = $m[1];
        $amphoe   = $m[2];
        $province = $m[3];
        $zipcode  = $m[4];
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title       = trim($_POST['project_name'] ?? '');
        $endDate     = trim($_POST['end_date'] ?? '');
        $phone       = trim($_POST['phone'] ?? '');
        $email       = trim($_POST['email'] ?? '');
        $goal        = isset($_POST['goal']) ? (int)$_POST['goal'] : 0;
        $address     = trim($_POST['address'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($title === '' || $endDate === '' || $phone === '' || $goal <= 0 || $address === '') {
            throw new Exception('กรอกข้อมูลให้ครบถ้วน');
        }

        // อัปโหลดรูปใหม่ (ถ้ามี)
        $imgPath = $project['img'];
        if (!empty($_FILES['img']['name']) && is_uploaded_file($_FILES['img']['tmp_name'])) {
            $allow = ['jpg','jpeg','png','webp'];
            $ext = strtolower(pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allow, true)) {
                throw new Exception('ชนิดไฟล์ภาพไม่รองรับ');
            }
            if ($_FILES['img']['size'] > 5 * 1024 * 1024) {
                throw new Exception('ไฟล์ภาพต้องไม่เกิน 5MB');
            }
            $dir = __DIR__ . '/uploads/projects';
            if (!is_dir($dir)) mkdir($dir, 0777, true);
            $fname = 'proj_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            if (move_uploaded_file($_FILES['img']['tmp_name'], $dir . '/' . $fname)) {
                $imgPath = 'uploads/projects/' . $fname;
            }
        }

        // อัปเดตข้อมูล (ไม่เปลี่ยนสถานะ ให้ owner แก้ไขได้ทุกสถานะ)
        $stmtUpd = $conn->prepare("
            UPDATE projects
            SET title=?, end_date=?, goal=?, address=?, phone=?, email=?, description=?, img=?
            WHERE project_id=? AND owner_id=?
        ");
        $stmtUpd->execute([$title, $endDate, $goal, $address, $phone, $email, $description, $imgPath, $projectId, $ownerId]);

        header('Location: owner.php?updated=1');
        exit;
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>แก้ไขโครงการ — แบ่งใจใส่จาน</title>
    <link rel="stylesheet" href="CSS/header.css">
    <link rel="stylesheet" href="CSS/add_project.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
    <script type="text/javascript" src="https://earthchie.github.io/jquery.Thailand.js/jquery.Thailand.js/dependencies/JQL.min.js"></script>
    <script type="text/javascript" src="https://earthchie.github.io/jquery.Thailand.js/jquery.Thailand.js/dependencies/typeahead.bundle.js"></script>
    <link rel="stylesheet" href="https://earthchie.github.io/jquery.Thailand.js/jquery.Thailand.js/dist/jquery.Thailand.min.css">
    <script type="text/javascript" src="https://earthchie.github.io/jquery.Thailand.js/jquery.Thailand.js/dist/jquery.Thailand.min.js"></script>
</head>
<body>
    <header class="header"></header>

    <main class="page-wrap">
        <section class="form-container">
            <h1 class="form-title">แก้ไขโครงการ</h1>

            <?php if (isset($error)): ?>
                <div class="error-message" style="color:#c00;padding:10px;background:#ffe6e6;border-radius:8px;margin-bottom:12px;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form class="project-form" action="edit_project.php?project_id=<?php echo $projectId; ?>" method="post" enctype="multipart/form-data" id="editProjectForm">
                <div class="form-group">
                    <label for="project_name">ชื่อโครงการ</label>
                    <input id="project_name" name="project_name" type="text" value="<?php echo htmlspecialchars($project['title']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="end_date">วันที่สิ้นสุดโครงการ</label>
                    <input id="end_date" name="end_date" type="date" value="<?php echo htmlspecialchars($project['end_date']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="phone">เบอร์โทรศัพท์</label>
                    <input id="phone" name="phone" type="tel" inputmode="numeric" value="<?php echo htmlspecialchars($project['phone']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">อีเมล</label>
                    <input id="email" name="email" type="email" value="<?php echo htmlspecialchars($project['email'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="goal">เป้าหมายโครงการ</label>
                    <input id="goal" name="goal" type="number" min="1" value="<?php echo (int)$project['goal']; ?>" required>
                </div>

                <!-- จุดรับบริจาค (แบบ jquery.Thailand.js) -->
                <div class="form-group form-group--full">
                    <label>จุดรับบริจาค</label>
                    <input id="district" type="text" placeholder="ตำบล" value="<?php echo htmlspecialchars($district); ?>" required />
                    <input id="amphoe" type="text" placeholder="อำเภอ" value="<?php echo htmlspecialchars($amphoe); ?>" required />
                    <input id="province" type="text" placeholder="จังหวัด" value="<?php echo htmlspecialchars($province); ?>" required />
                    <input id="zipcode" type="text" placeholder="รหัสไปรษณีย์" value="<?php echo htmlspecialchars($zipcode); ?>" required />
                    <input type="hidden" name="address" id="address_hidden">
                </div>

                <div class="form-group form-group--full">
                    <label for="description">รายละเอียด</label>
                    <textarea id="description" name="description" rows="5" required><?php echo htmlspecialchars($project['description'] ?? ''); ?></textarea>
                </div>
                <div class="form-group form-group--full">
                    <label for="img">รูปภาพปก (เว้นว่างหากไม่เปลี่ยน)</label>
                    <?php if (!empty($project['img'])): ?>
                        <img src="<?php echo htmlspecialchars($project['img']); ?>" alt="ภาพปัจจุบัน" style="max-width:200px;display:block;margin-bottom:8px;">
                    <?php endif; ?>
                    <input id="img" name="img" type="file" accept="image/*">
                </div>
                <div class="actions">
                    <a class="btn btn-secondary" href="owner.php">ยกเลิก</a>
                    <button class="btn btn-primary" type="submit">บันทึก</button>
                </div>
            </form>
        </section>
    </main>

    <script src="js/header.js"></script>
    <script>
    $.Thailand({
        $district: $('#district'),
        $amphoe: $('#amphoe'),
        $province: $('#province'),
        $zipcode: $('#zipcode'),
    });

    // รวม address ก่อน submit
    $('#editProjectForm').on('submit', function(e){
      const district = $('#district').val().trim();
      const amphoe   = $('#amphoe').val().trim();
      const province = $('#province').val().trim();
      const zipcode  = $('#zipcode').val().trim();

      if (!district || !amphoe || !province || !zipcode) {
        e.preventDefault();
        alert('กรุณากรอกจุดรับบริจาคให้ครบถ้วน');
        return false;
      }

      const fullAddress = `ต.${district} อ.${amphoe} จ.${province} ${zipcode}`;
      $('#address_hidden').val(fullAddress);
    });
    </script>
</body>
</html>