<?php
// filepath: c:\D_drive\heart_on_plate\owner.php
session_start();
require_once __DIR__ . '/sql/config.php';

$roleId = isset($_SESSION['roleID']) ? (int)$_SESSION['roleID'] : ((isset($_SESSION['role_id']) ? (int)$_SESSION['role_id'] : 0));
if ($roleId <= 0) {
    header('Location: profile.html');
    exit;
}

try {
    if ($roleId === 3) {
        // แอดมิน เห็นทั้งหมด
        $stmt = $conn->query("SELECT * FROM projects ORDER BY project_id DESC");
    } else {
        // บทบาทอื่น เห็นเฉพาะ owner_id ของตน (owner_id = roleId)
        $stmt = $conn->prepare("SELECT * FROM projects WHERE owner_id = :owner_id ORDER BY project_id DESC");
        $stmt->execute([':owner_id' => $roleId]);
    }
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
    <title>โครงการของฉัน — แบ่งใจใส่จาน</title>
    <link rel="stylesheet" href="CSS/header.css">
    <link rel="stylesheet" href="CSS/admin.css">
    <link rel="stylesheet" href="CSS/project-list.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <!-- ใช้ Header ส่วนกลาง -->
    <header class="header"></header>

    <main class="admin-main">
        <aside class="admin-sidebar">
            <h2 class="sidebar-title">Owner Dashboard</h2>
            <nav class="sidebar-nav">
                <a class="nav-item active" href="owner.php">โครงการของฉัน</a>
                <a class="nav-item" href="owner_donations.php">ผู้บริจาค</a>  <!-- เปลี่ยนลิงก์ -->
            </nav>
        </aside>

        <section class="admin-content">
            <div class="list-header-row">
                <h2>รายการโครงการ</h2>
                <div class="list-actions">
                    <a href="add_project.php" class="btn btn-primary">เปิดโครงการใหม่</a>
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
                            <th>วันที่เริ่ม</th>
                            <th>เป้าหมายโครงการ</th>
                            <th>สถานะ</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody id="projectTableBody">
                        <?php if ($projects): ?>
                            <?php foreach ($projects as $p): ?>
                                <?php $isClosed = strtolower($p['status'] ?? '') === 'close'; ?>
                                <tr data-project-id="<?php echo (int)$p['project_id']; ?>">
                                    <td><?php echo htmlspecialchars($p['title'] ?? '-'); ?></td>
                                    <td>
                                        <?php
                                            $d = $p['start_date'] ?? '';
                                            echo $d ? htmlspecialchars(date('d/m/Y', strtotime($d))) : '-';
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($p['goal'] ?? '-'); ?></td>
                                    <td data-col="status"><?php echo htmlspecialchars($p['status'] ?? '-'); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="edit_project.php?project_id=<?php echo (int)$p['project_id']; ?>"
                                               class="btn btn-edit">
                                                <i class="fas fa-edit"></i> แก้ไข
                                            </a>
                                            <button type="button"
                                                    class="btn btn-close-project"
                                                    data-id="<?php echo (int)$p['project_id']; ?>"
                                                    <?php echo $isClosed ? 'disabled' : ''; ?>>
                                                ปิดโครงการ
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align:center;">ยังไม่มีโครงการ</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <script src="js/header.js"></script>
    <script>
      const tbody = document.getElementById('projectTableBody');
      tbody?.addEventListener('click', async (e) => {
        const btn = e.target.closest('.btn-close-project');
        if (btn) {
          e.preventDefault(); e.stopPropagation();
          if (!confirm('ยืนยันปิดโครงการนี้?')) return;
          const id = btn.dataset.id;

          try {
            const res = await fetch('close_project.php', {
              method: 'POST',
              headers: {'Content-Type': 'application/x-www-form-urlencoded'},
              body: 'project_id=' + encodeURIComponent(id)
            });
            const data = await res.json();
            if (!res.ok || !data.ok) throw new Error(data.message || 'ไม่สำเร็จ');

            const row = btn.closest('tr');
            row.querySelector('[data-col="status"]').textContent = 'close';
            btn.disabled = true;
          } catch (err) {
            alert('ปิดโครงการไม่สำเร็จ: ' + err.message);
          }
          return;
        }

        // คลิกที่แถว เพื่อดูรายละเอียด
        const row = e.target.closest('tr[data-project-id]');
        if (!row) return;
        const id = row.getAttribute('data-project-id');
        window.location.href = 'post_title1.php?project_id=' + encodeURIComponent(id);
      });
    </script>
</body>
</html>