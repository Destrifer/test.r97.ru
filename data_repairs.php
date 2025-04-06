
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

require_once $_SERVER['DOCUMENT_ROOT'].'/_new-codebase/config.php';

$start = intval($_GET['start'] ?? 0);
$length = intval($_GET['length'] ?? 10);
$search = mysqli_real_escape_string($db, $_GET['search']['value'] ?? '');
$draw = intval($_GET['draw'] ?? 0);

// Получаем общее количество строк
$totalRow = 0;
$totalResult = mysqli_query($db, "SELECT COUNT(*) FROM repairs");
if ($totalResult) {
    $row = mysqli_fetch_row($totalResult);
    $totalRow = intval($row[0]);
} else {
    echo json_encode([
        "draw" => $draw,
        "recordsTotal" => null,
        "recordsFiltered" => 0,
        "data" => [],
        "error" => "Ошибка COUNT-запроса: " . mysqli_error($db)
    ]);
    exit;
}

// WHERE фильтр
$where = "";
if ($search !== '') {
    $where = "WHERE r.id LIKE '%{$search}%' OR s.name LIKE '%{$search}%'";
}

$sql = "SELECT r.id, r.service_id, s.name AS service_name 
        FROM repairs r 
        LEFT JOIN requests s ON r.service_id = s.user_id 
        {$where}
        ORDER BY r.id DESC
        LIMIT {$start}, {$length}";

$data = [];
$result = mysqli_query($db, $sql);
if (!$result) {
    echo json_encode([
        "draw" => $draw,
        "recordsTotal" => $totalRow,
        "recordsFiltered" => 0,
        "data" => [],
        "error" => "Ошибка SELECT-запроса: " . mysqli_error($db)
    ]);
    exit;
}

while ($row = mysqli_fetch_assoc($result)) {
    $data[] = [
        $row['id'],
        $row['service_name'] ?? '<span style="color:red">Сервис не найден</span>',
        '<form method="POST" onsubmit="return confirm(\'Сменить сервис?\');" style="display:flex;gap:4px;">
            <input type="hidden" name="repair_id" value="' . $row['id'] . '">
            <select name="new_service_id" class="service-select" data-id="' . $row['id'] . '"></select>
            <button type="submit">OK</button>
        </form>'
    ];
}

echo json_encode([
    "draw" => $draw,
    "recordsTotal" => $totalRow,
    "recordsFiltered" => $totalRow,
    "data" => $data
]);
?>
