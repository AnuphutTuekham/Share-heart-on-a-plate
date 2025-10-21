<?php
require_once __DIR__ . '/config.php';

// เริ่มเก็บ output ถ้ายังไม่มี (เพื่อจับข้อความ debug เช่น "Connected successfully")
if (!ob_get_level()) ob_start();

$rows = [];
$error = '';
$tablesDetails = [];

// ดึง summary จากฐานข้อมูล (ถ้า config.php สร้าง $conn)
try {
    if (isset($conn)) {
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $conn->prepare("SELECT project_sum, food_sum, donate_sum, not_shipped_sum, shipped_sum FROM sum_results");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    // production: บันทึก $e->getMessage() ลง log แทนแสดง
    $error = 'Database error.';
}

// อ่านและ parse heart.sql เพื่อดึงชื่อตาราง, คอลัมน์ และ INSERT rows (แบบง่าย)
$sqlFile = __DIR__ . '/heart.sql';
if (file_exists($sqlFile) && is_readable($sqlFile)) {
    $content = file_get_contents($sqlFile);
    if ($content === false) {
        $error = 'Unable to read heart.sql';
    } else {
        // Parse CREATE TABLE blocks
        if (preg_match_all("/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`([^`]+)`\s*\((.*?)\)\s*(?:ENGINE|DEFAULT|CHARSET|COLLATE|;)/is", $content, $createMatches, PREG_SET_ORDER)) {
            foreach ($createMatches as $m) {
                $tname = $m[1];
                $body = $m[2];
                $cols = [];
                if (preg_match_all("/^\s*`([^`]+)`\s+([^,]+)/m", $body, $colMatches, PREG_SET_ORDER)) {
                    foreach ($colMatches as $cm) {
                        $cols[] = ['name' => $cm[1], 'definition' => trim($cm[2])];
                    }
                }
                $tablesDetails[$tname] = ['columns' => $cols, 'rows' => []];
            }
        }

        // Parse INSERT INTO ... VALUES (...) , (...) ;
        if (preg_match_all("/INSERT\s+INTO\s+`([^`]+)`(?:\s*\(([^)]+)\))?\s*VALUES\s*(.+?);/is", $content, $insMatches, PREG_SET_ORDER)) {
            foreach ($insMatches as $im) {
                $t = $im[1];
                $colListRaw = isset($im[2]) ? $im[2] : null;
                $valuesSection = $im[3];

                // extract tuples
                if (preg_match_all("/\((.*?)\)(?=,\s*\(|\s*$)/s", $valuesSection, $tupleMatches)) {
                    foreach ($tupleMatches[1] as $tupleRaw) {
                        $tuple = trim($tupleRaw);
                        // พื้นฐาน: เก็บ raw tuple string (ไม่พยายาม parse ครบกรณีซับซ้อน)
                        if (!isset($tablesDetails[$t])) $tablesDetails[$t] = ['columns'=>[], 'rows'=>[]];
                        $tablesDetails[$t]['rows'][] = $tuple;
                    }
                }
            }
        }
    }
} else {
    $error = 'heart.sql not found or not readable.';
}

// ถ้าถามเป็น JSON ให้คืนค่า (ก่อนคืนให้ล้าง output buffer ที่อาจมีข้อความ debug)
if (isset($_GET['format']) && $_GET['format'] === 'json') {
    if (ob_get_length()) {
        ob_clean(); // ล้างข้อความนำหน้า เช่น "Connected successfully"
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'summary' => $rows,
        'tables'  => $tablesDetails,
        'error'   => $error ?: null,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Tables in heart.sql</title>
</head>
<body>
<h1>Tables in heart.sql</h1>
<?php if (!empty($error)): ?>
    <p><?php echo htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
<?php else: ?>
    <p>Found <?php echo count($tablesDetails); ?> table(s).</p>
    <?php foreach ($tablesDetails as $t => $info): ?>
        <section>
            <h2><?php echo htmlspecialchars($t, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></h2>
            <h3>Columns</h3>
            <?php if (empty($info['columns'])): ?>
                <p>No columns parsed.</p>
            <?php else: ?>
                <ul>
                <?php foreach ($info['columns'] as $c): ?>
                    <li><?php echo htmlspecialchars($c['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?> — <?php echo htmlspecialchars($c['definition'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></li>
                <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <h3>Sample rows (raw)</h3>
            <?php if (empty($info['rows'])): ?>
                <p>No INSERT rows found.</p>
            <?php else: ?>
                <pre><?php echo htmlspecialchars(implode("\n", array_slice($info['rows'], 0, 10)), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></pre>
            <?php endif; ?>
        </section>
    <?php endforeach; ?>
<?php endif; ?>
</body>
</html>