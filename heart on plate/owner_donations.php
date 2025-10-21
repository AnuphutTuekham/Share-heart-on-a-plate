<?php
require_once __DIR__ . '/sql/config.php';
$ownerId = 2; // กรองเฉพาะโครงการของ owner_id = 3

try {
    // ตรวจสอบชื่อคอลัมน์จำนวนในตาราง donations (amount หรือ amout)
    $colStmt = $conn->prepare("
        SELECT COLUMN_NAME
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'donations'
          AND COLUMN_NAME IN ('amount', 'amout')
        ORDER BY FIELD(COLUMN_NAME,'amount','amout')  -- ให้ amount มาก่อนถ้ามี
        LIMIT 1
    ");
    $colStmt->execute();
    $amountColumn = $colStmt->fetchColumn();
    if (!$amountColumn) {
        // fallback ป้องกัน error แม้ไม่มีคอลัมน์ (กรณี schema ไม่ครบ)
        $amountColumn = 'amount';
    }

    // ดึงรายการบริจาค โดย join กับ projects เพื่อกรอง owner
    // และ join กับ food_type เพื่อดึงคำอธิบายประเภทอาหาร
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
    $stmt->execute([':ownerId' => $ownerId]);
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
                <a class="nav-item" href="owner.php">โครงการของฉัน</a>
                <a class="nav-item active" href="owner_donations.php">ผู้บริจาค</a>
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
                            <th>จัดการ</th> <!-- เพิ่ม -->
                        </tr>
                    </thead>
                    <tbody id="donationTableBody">
                        <?php if ($donations): ?>
                            <?php foreach ($donations as $d): ?>
                                <?php $isReceived = trim((string)($d['status'] ?? '')) === 'จัดส่งแล้ว'; ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($d['food_img'])): ?>
                                            <img src="<?php echo htmlspecialchars($d['food_img']); ?>" alt="food" style="width:56px;height:56px;object-fit:cover;border-radius:8px;">
                                        <?php else: ?>-<?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($d['food_type_desc'] ?: $d['food_type']); ?></td>
                                    <td><?php echo htmlspecialchars($d['food_name']); ?></td>
                                    <td><?php echo htmlspecialchars($d['amount_value']); ?></td>
                                    <td><?php echo htmlspecialchars($d['MFG'] ?: '-'); ?></td>
                                    <td><?php echo htmlspecialchars($d['EXP'] ?: '-'); ?></td>
                                    <td data-col="status"><?php echo htmlspecialchars($d['status'] ?: '-'); ?></td>
                                    <td><?php echo htmlspecialchars($d['project_title'] ?: '-'); ?></td>
                                    <td>
                                        <button
                                          type="button"
                                          class="btn btn-received btn-mark-received"
                                          data-id="<?php echo (int)$d['donations_id']; ?>"
                                          <?php echo $isReceived ? 'disabled' : ''; ?>>
                                          ได้รับอาหารแล้ว
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="9" style="text-align:center;">ยังไม่มีรายการบริจาค</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <script src="js/header.js"></script>
    <script>
      // อัปเดตสถานะเป็น "จัดส่งแล้ว" เมื่อกดปุ่ม
      document.getElementById('donationTableBody')?.addEventListener('click', async (e) => {
        const btn = e.target.closest('.btn-mark-received');
        if (!btn) return;

        const id = btn.dataset.id;
        try {
          const res = await fetch('update_donation_status.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'donations_id=' + encodeURIComponent(id) + '&status=' + encodeURIComponent('จัดส่งแล้ว')
          });
          const text = await res.text();
          let data; try { data = JSON.parse(text); } catch { throw new Error('Invalid JSON: ' + text); }
          if (!res.ok || !data.ok) throw new Error(data.message || 'อัปเดตไม่สำเร็จ');

          const row = btn.closest('tr');
          row.querySelector('[data-col="status"]').textContent = 'จัดส่งแล้ว';
          btn.disabled = true;
        } catch (err) {
          alert('เกิดข้อผิดพลาด: ' + err.message);
        }
      });
    </script>
</body>
</html>