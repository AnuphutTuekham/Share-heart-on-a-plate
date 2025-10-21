<?php
require_once __DIR__ . '/sql/config.php';
session_start();

// ดึง roleID จาก session
$roleId = isset($_SESSION['roleID']) ? (int)$_SESSION['roleID'] : (isset($_SESSION['role_id']) ? (int)$_SESSION['role_id'] : 0);
if ($roleId <= 0) {
    header('Location: profile.html');
    exit;
}

try {
    // ตรวจสอบชื่อคอลัมน์จำนวนในตาราง donations (amount หรือ amout)
    $colStmt = $conn->prepare("
        SELECT COLUMN_NAME
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'donations'
          AND COLUMN_NAME IN ('amount', 'amout')
        ORDER BY FIELD(COLUMN_NAME,'amount','amout')
        LIMIT 1
    ");
    $colStmt->execute();
    $amountColumn = $colStmt->fetchColumn() ?: 'amount';

    if ($roleId === 3) {
        // แอดมิน: แสดงทั้งหมด
        $sql = "
            SELECT 
                d.donations_id,
                d.food_name,
                d.MFG,
                d.EXP,
                d.food_img,
                d.status,
                d.food_type,
                p.title AS project_title,
                ft.Description AS food_type_desc,
                d.`$amountColumn` AS amount_value
            FROM donations d
            LEFT JOIN projects p ON d.project_id = p.project_id
            LEFT JOIN food_type ft ON d.food_type = ft.food_type_id
            ORDER BY d.donations_id DESC
        ";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
    } else {
        // บทบาทอื่น: เห็นเฉพาะโครงการที่ owner_id = roleID
        $sql = "
            SELECT 
                d.donations_id,
                d.food_name,
                d.MFG,
                d.EXP,
                d.food_img,
                d.status,
                d.food_type,
                p.title AS project_title,
                ft.Description AS food_type_desc,
                d.`$amountColumn` AS amount_value
            FROM donations d
            LEFT JOIN projects p ON d.project_id = p.project_id
            LEFT JOIN food_type ft ON d.food_type = ft.food_type_id
            WHERE p.owner_id = :ownerId
            ORDER BY d.donations_id DESC
        ";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':ownerId' => $roleId]);
    }

    $donations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $donations = [];
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>ผู้บริจาค — แบ่งใจใส่จาน</title>
    <link rel="stylesheet" href="CSS/header.css">
    <link rel="stylesheet" href="CSS/admin.css">
    <link rel="stylesheet" href="CSS/project-list.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <!-- Header ส่วนกลาง -->
    <header class="header"></header>

    <main class="admin-main">
        <aside class="admin-sidebar">
            <h2 class="sidebar-title">Owner Dashboard</h2>
            <nav class="sidebar-nav">
                <a class="nav-item" href="admin.html">Dashboard</a>
                <a class="nav-item" href="project-list.php">ตรวจสอบโครงการ</a>
                <a class="nav-item active" href="donor-list.php">ผู้บริจาค</a>
            </nav>
        </aside>

        <section class="admin-content">
            <div class="list-header-row">
                <h2>รายการผู้บริจาค</h2>
                <div class="search-box">
                    <input type="search" placeholder="ค้นหาชื่ออาหารหรือสถานะ...">
                </div>
            </div>

            <?php if (!empty($error)): ?>
                <div class="error" style="color:#c00;"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="project-list">
                <table class="project-table donation-table">
                    <thead>
                        <tr>
                            <th>ภาพ</th>
                            <th>ประเภทอาหาร</th>
                            <th>ชื่ออาหาร</th>
                            <th>จำนวน</th>
                            <th>MFG</th>
                            <th>EXP</th>
                            <th>สถานะ</th>
                            <th>โครงการ</th>
                        </tr>
                    </thead>
                    <tbody id="donationTableBody">
                        <?php if ($donations): ?>
                            <?php foreach ($donations as $d): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($d['food_img'])): ?>
                                            <img src="<?php echo htmlspecialchars($d['food_img']); ?>" alt="food" style="width:56px;height:56px;object-fit:cover;border-radius:8px;">
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($d['food_type_desc'] ?: $d['food_type']); ?></td>
                                    <td><?php echo htmlspecialchars($d['food_name']); ?></td>
                                    <td><?php echo htmlspecialchars($d['amount_value']); ?></td>
                                    <td><?php echo htmlspecialchars($d['MFG'] ?: '-'); ?></td>
                                    <td><?php echo htmlspecialchars($d['EXP'] ?: '-'); ?></td>
                                    <td><?php echo htmlspecialchars($d['status'] ?: '-'); ?></td>
                                    <td><?php echo htmlspecialchars($d['project_title'] ?: '-'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="8" style="text-align:center;">ยังไม่มีรายการบริจาค</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <script src="js/header.js"></script>
</body>
</html>