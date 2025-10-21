<?php
// filepath: c:\D_drive\heart_on_plate\project-list.php
require_once __DIR__ . '/sql/config.php';

try {
    $sql = "
        SELECT
            p.project_id,
            p.title,
            p.goal,
            p.status,
            p.start_date,
            u.name AS owner_name
        FROM projects p
        LEFT JOIN user u ON p.owner_id = u.user_id
        ORDER BY p.project_id DESC
    ";
    $stmt = $conn->query($sql);
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $projects = [];
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>รายการโครงการ — แบ่งใจใส่จาน</title>
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
            <h2 class="sidebar-title">Admin Dashboard</h2>
            <nav class="sidebar-nav">
                <a class="nav-item" href="admin.html">Dashboard</a>
                <a class="nav-item active" href="project-list.php">ตรวจสอบโครงการ</a>
                <a class="nav-item" href="donor-list.php">ผู้บริจาค</a>

            </nav>
        </aside>

        <section class="admin-content">
            <div class="list-header-row">
                <h2>รายการโครงการ</h2>
                <div class="search-box">
                    <input type="search" placeholder="ค้นหาโครงการ..." />
                </div>
            </div>

            <?php if (!empty($error)): ?>
                <div class="error" style="color:#c00;"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="project-list">
                <table class="project-table">
                    <thead>
                        <tr>
                            <th>ชื่อโครงการ</th>
                            <th>เจ้าของโครงการ</th>
                            <th>วันที่เริ่ม</th>
                            <th>เป้าหมาย</th>
                            <th>สถานะ</th>
                            <th>จัดการ</th>
                            <th>รายละเอียด</th>
                        </tr>
                    </thead>
                    <tbody id="projectTableBody">
                        <?php if ($projects): ?>
                            <?php foreach ($projects as $p): ?>
                                <?php $status = strtolower($p['status'] ?? ''); ?>
                                <tr data-project-id="<?php echo (int)$p['project_id']; ?>">
                                    <td><?php echo htmlspecialchars($p['title'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($p['owner_name'] ?? '-'); ?></td>
                                    <td>
                                        <?php
                                          $d = $p['start_date'] ?? null;
                                          echo $d ? htmlspecialchars(date('d/m/Y', strtotime($d))) : '-';
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($p['goal'] ?? '-'); ?></td>
                                    <td data-col="status"><?php echo htmlspecialchars($p['status'] ?? '-'); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button type="button"
                                                    class="btn btn-approve"
                                                    data-id="<?php echo (int)$p['project_id']; ?>"
                                                    data-status="open"
                                                    <?php echo $status === 'open' ? 'disabled' : ''; ?>>
                                                อนุมัติ
                                            </button>
                                            <button type="button"
                                                    class="btn btn-reject"
                                                    data-id="<?php echo (int)$p['project_id']; ?>"
                                                    data-status="close"
                                                    <?php echo $status === 'close' ? 'disabled' : ''; ?>>
                                                ไม่อนุมัติ
                                            </button>
                                        </div>
                                    </td>
                                    <td>
                                        <a class="btn-link" href="post_title1.php?project_id=<?php echo (int)$p['project_id']; ?>">รายละเอียด</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" style="text-align:center;">ยังไม่มีโครงการ</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <script src="js/header.js"></script>
    <script>
      document.getElementById('projectTableBody')?.addEventListener('click', async (e) => {
        const btn = e.target.closest('.btn-approve, .btn-reject');
        if (!btn) return;

        const id = btn.dataset.id;
        const status = btn.dataset.status;

        try {
          const res = await fetch('update_project_status.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'project_id=' + encodeURIComponent(id) + '&status=' + encodeURIComponent(status)
          });

          const text = await res.text();
          let data;
          try { data = JSON.parse(text); }   
          catch { throw new Error('Invalid JSON: ' + text.slice(0,120)); }

          if (!res.ok || !data.ok) throw new Error(data.message || 'อัปเดตไม่สำเร็จ');

          const row = btn.closest('tr');
          row.querySelector('[data-col="status"]').textContent = status;
          row.querySelectorAll('.btn-approve, .btn-reject').forEach(b => {
            b.disabled = (b.dataset.status === status);
          });
        } catch (err) {
          alert('เกิดข้อผิดพลาด: ' + err.message);
        }
      });
    </script>
</body>
</html>