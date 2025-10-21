<?php
session_start();
require_once 'sql/config.php';

// ดึง role_id จาก session ถ้ายังไม่มีให้ลองอ่านจากตาราง users ด้วย user_id ใน session
$roleId = isset($_SESSION['role_id']) ? (int)$_SESSION['role_id'] : 0;
if (!$roleId && isset($_SESSION['user_id'])) {
    try {
        $stmtRole = $conn->prepare("SELECT role_id FROM users WHERE user_id = ?");
        $stmtRole->execute([$_SESSION['user_id']]);
        $roleId = (int)($stmtRole->fetchColumn() ?: 0);
        $_SESSION['role_id'] = $roleId;
    } catch (Throwable $e) { /* ignore, ใช้ 0 ต่อไป */ }
}

try {
    // แสดงเฉพาะโครงการที่เปิดอยู่
    $stmt = $conn->prepare("SELECT * FROM projects WHERE status = :status ORDER BY project_id DESC");
    $stmt->execute([':status' => 'open']);
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = $e->getMessage();
}

// สรุปจำนวนผู้บริจาควันนี้ และจำนวนอาหารที่ได้รับ (รวมทุกโครงการ)
$donorTotal = 0;
$foodTotal = 0;
try {
    $stmtC = $conn->query("
        SELECT 
          COALESCE(SUM(donor_count), 0) AS donor_total,
          COALESCE(SUM(food_amout_sum), 0) AS food_total
        FROM daily_donor_count
    ");
    $row = $stmtC->fetch(PDO::FETCH_ASSOC) ?: [];
    $donorTotal = (int)($row['donor_total'] ?? 0);
    $foodTotal  = (int)($row['food_total'] ?? 0);
} catch (Throwable $e) {
    // ถ้าตาราง/คอลัมน์ยังไม่พร้อม ให้แสดง 0
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แบ่งใจใส่จาน</title>

    <?php
      $verHeader = file_exists('CSS/header.css') ? filemtime('CSS/header.css') : time();
      $verStyle  = file_exists('CSS/style.css')  ? filemtime('CSS/style.css')  : time();
    ?>
    <link rel="stylesheet" href="CSS/header.css?v=<?php echo $verHeader; ?>">
    <link rel="stylesheet" href="CSS/style.css?v=<?php echo $verStyle; ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
  <header class="header">
    <div class="header-inner">
      <div class="logo">
        <a href="index.php" class="logo-link">   <!-- เปลี่ยนจาก index.html เป็น index.php -->
          <img src="pic/logoNOBG.png" alt="โลโก้ แบ่งใจใส่จาน" class="logo-icon">
          <h1>แบ่งใจใส่จาน</h1>
        </a>
      </div>

      <div class="search-bar">
        <input type="text" placeholder="ค้นหาโครงการ">
        <button><i class="fas fa-search"></i></button>
      </div>
      <div class="user-profile" id="userProfile" aria-haspopup="true" aria-expanded="false">
        <img src="pic/logoNOBG.png" alt="User Profile">
        <div class="dropdown-menu" id="dropdownMenu">
          <?php if ($roleId === 1): ?>
              <a href="profile.html" class="menu-item">
                  <i class="fas fa-user-edit"></i><span>เปลี่ยนโปรไฟล์</span>
              </a>
          <?php elseif ($roleId === 2): ?>
              <a href="profile.html" class="menu-item">
                  <i class="fas fa-user-edit"></i><span>เปลี่ยนโปรไฟล์</span>
              </a>
              <a href="owner.php" class="menu-item">
                  <i class="fas fa-folder-open"></i><span>โครงการของฉัน</span>
              </a>
          <?php elseif ($roleId === 3): ?>
              <a href="profile.html" class="menu-item">
                  <i class="fas fa-user-edit"></i><span>เปลี่ยนโปรไฟล์</span>
              </a>
              <a href="owner.php" class="menu-item">
                  <i class="fas fa-folder-open"></i><span>โครงการของฉัน</span>
              </a>
              <a href="project-list.php" class="menu-item">
                  <i class="fas fa-clipboard-check"></i><span>ตรวจสอบโครงการ</span>
              </a>
          <?php else: ?>
              <!-- ค่าเริ่มต้น ถ้ายังไม่มี role -->
              <a href="profile.html" class="menu-item">
                  <i class="fas fa-user-edit"></i><span>เปลี่ยนโปรไฟล์</span>
              </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </header>

  <!-- แถบสรุปวันนี้ (แสดงครั้งเดียวใต้ header) -->
  <section class="global-today-stats" aria-label="สรุปวันนี้">
    <div class="item">
      <span class="label">จำนวนผู้บริจาควันนี้</span>
      <span class="value"><?php echo number_format($donorTotal); ?></span>
    </div>
    <div class="item">
      <span class="label">จำนวนอาหารที่ได้รับ</span>
      <span class="value"><?php echo number_format($foodTotal); ?></span>
    </div>
  </section>

  <main class="main-content">
    <?php if (isset($error)): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php else: ?>
        <?php foreach ($projects as $project): ?>
            <a href="post_title1.php?project_id=<?php echo htmlspecialchars($project['project_id']); ?>" 
               class="view-details-button">
                <div class="project-card">
                    <div class="project-header">
                        <img src="<?php echo htmlspecialchars($project['img']); ?>" 
                             alt="<?php echo htmlspecialchars($project['title']); ?>" 
                             class="banner-image">
                        <div class="project-info">
                            <h2><?php echo htmlspecialchars($project['title']); ?></h2>
                            <p><strong>เป้าหมาย:</strong> <?php echo htmlspecialchars($project['goal'] ?? '-'); ?></p>
                            <p><strong>จุดรับบริจาค:</strong> <?php echo htmlspecialchars($project['address'] ?? '-'); ?></p>
                            <p><strong>ติดต่อ:</strong> <?php echo htmlspecialchars($project['phone'] ?? '-'); ?></p>
                        </div>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    <?php endif; ?>
  </main>

  <script>
    (function initHeaderDropdown(){
      const userProfile = document.getElementById('userProfile');
      const dropdown = document.getElementById('dropdownMenu');
      if (!userProfile || !dropdown) return;

      const close = () => {
        dropdown.classList.remove('show');
        userProfile.setAttribute('aria-expanded', 'false');
      };

      userProfile.addEventListener('click', (e) => {
        e.stopPropagation();
        const shown = dropdown.classList.toggle('show');
        userProfile.setAttribute('aria-expanded', shown ? 'true' : 'false');
      });

      document.addEventListener('click', (e) => {
        if (!e.target.closest('#userProfile')) close();
      });

      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') close();
      });
    })();
  </script>
</body>
</html>