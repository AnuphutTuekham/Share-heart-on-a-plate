<?php
require_once 'sql/config.php';

try {
    // ดึงข้อมูลจากตาราง projects โดยใช้ project_id
    $stmt = $conn->prepare("SELECT * FROM projects WHERE project_id = ?");
    $projectId = isset($_GET['project_id']) ? $_GET['project_id'] : 1; // รับ project_id จาก URL
    $stmt->execute([$projectId]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$project) {
        throw new Exception('ไม่พบข้อมูลโครงการ');
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ข้อมูลมูลนิธิแบ่งมื้ออิ่ม</title>
    <link rel="stylesheet" href="CSS/header.css">
    <link rel="stylesheet" href="CSS/post_title.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <header class="header"></header>

    <main class="main-content">
        <div class="detail-card">
            <div class="image-section">
                <img src="<?php echo htmlspecialchars($project['img']); ?>" 
                     alt="<?php echo htmlspecialchars($project['title']); ?>" 
                     class="detail-image">
            </div>
            <div class="info-section">
                <?php if (isset($error)): ?>
                    <p class="error"><?php echo htmlspecialchars($error); ?></p>
                <?php else: ?>
                    <h2><?php echo htmlspecialchars($project['title']); ?></h2>
                    <p><?php echo nl2br(htmlspecialchars($project['description'])); ?></p>
                    
                    <div class="foundation-details">
                        <p><strong>จุดรับบริจาค:</strong> <?php echo htmlspecialchars($project['address']); ?></p>
                        <p><strong>เบอร์ติดต่อ:</strong> <?php echo htmlspecialchars($project['phone']); ?></p>
                        <p><strong>อีเมล:</strong> <?php echo htmlspecialchars($project['email']); ?></p>
                        <p><strong>วันที่เริ่มโครงการ:</strong> <?php echo htmlspecialchars($project['start_date']); ?></p>
                        <p><strong>เป้าหมาย:</strong> <?php echo htmlspecialchars($project['goal']); ?></p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="button-section">
                <a href="add_food.php?project_id=<?php echo htmlspecialchars($project['project_id']); ?>" class="action-button donate-button">บริจาค</a>
            </div>
        </div>
    </main>
    <script src="js/header.js"></script>
</body>
</html>