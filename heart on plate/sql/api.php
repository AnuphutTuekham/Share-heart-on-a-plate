<?php
// อ่าน heart.sql และคืนค่า JSON ของตาราง / คอลัมน์ / แถว (parsed)
header('Content-Type: application/json; charset=utf-8');

$sqlFile = __DIR__ . '/heart.sql';
if (!file_exists($sqlFile) || !is_readable($sqlFile)) {
    http_response_code(404);
    echo json_encode(['error' => 'heart.sql not found or not readable.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$content = file_get_contents($sqlFile);
if ($content === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Unable to read heart.sql'], JSON_UNESCAPED_UNICODE);
    exit;
}

$tables = [];

// Parse CREATE TABLE -> columns (simple parse)
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
        $tables[$tname] = [
            'columns' => $cols,
            'rows' => [],
        ];
    }
}

// Parse INSERT INTO statements (generic)
if (preg_match_all("/INSERT\s+INTO\s+`([^`]+)`(?:\s*\(([^)]+)\))?\s*VALUES\s*(.+?);/is", $content, $insMatches, PREG_SET_ORDER)) {
    foreach ($insMatches as $im) {
        $t = $im[1];
        $colListRaw = isset($im[2]) ? $im[2] : null;
        $valuesSection = $im[3];

        // parse column list if present
        $colNames = [];
        if ($colListRaw) {
            if (preg_match_all("/`([^`]+)`/", $colListRaw, $cns)) {
                $colNames = $cns[1];
            }
        }

        // extract tuples (handle multiple tuples)
        if (preg_match_all("/\((.*?)\)(?=,\s*\(|\s*$)/s", $valuesSection, $tupleMatches)) {
            foreach ($tupleMatches[1] as $tupleRaw) {
                $tuple = trim($tupleRaw);

                // parse tuple values using CSV parser with single-quote enclosure
                $values = str_getcsv($tuple, ",", "'", "\\");
                // trim whitespace and convert bare NULL -> null
                $values = array_map(function ($v) {
                    $v = trim($v);
                    if (strcasecmp($v, "NULL") === 0) return null;
                    // unescape escaped single quotes (\' -> ')
                    $v = str_replace("\\'", "'", $v);
                    return $v;
                }, $values);

                // map to column names if available and counts match
                if (!empty($colNames) && count($colNames) === count($values)) {
                    $assoc = [];
                    foreach ($colNames as $i => $cn) {
                        $assoc[$cn] = $values[$i];
                    }
                    $rowData = $assoc;
                } else {
                    // fallback: numeric keys
                    $rowData = array_values($values);
                }

                if (!isset($tables[$t])) {
                    $tables[$t] = ['columns' => [], 'rows' => []];
                }
                $tables[$t]['rows'][] = $rowData;
            }
        }
    }
}

echo json_encode(['tables' => $tables], JSON_UNESCAPED_UNICODE);