
<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/_new-codebase/config.php';

// Получаем параметры DataTables
$start = intval($_GET['start'] ?? 0);
$length = intval($_GET['length'] ?? 10);
$search = mysqli_real_escape_string($db, $_GET['search']['value'] ?? '');

// Подсчёт общего количества записей
$totalResult = mysqli_query($db, "SELECT COUNT(*) FROM repairs");
$totalRow = mysqli_fetch_row($totalResult)[0];

// Построим основной запрос
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
    "draw" => intval($_GET['draw']),
    "recordsTotal" => $totalRow,
    "recordsFiltered" => count($data),
    "data" => $data
]);
?>
