
<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/_new-codebase/config.php';

function getRepairsWithServices($db) {
    $repairs = [];
    $sql = mysqli_query($db, "SELECT r.id, r.title, r.service_id, s.name AS service_name 
                              FROM repairs r 
                              LEFT JOIN requests s ON r.service_id = s.id 
                              ORDER BY r.id DESC LIMIT 100");

    while ($row = mysqli_fetch_assoc($sql)) {
        $repairs[] = $row;
    }
    return $repairs;
}

function getAllServices($db) {
    $services = [];
    $sql = mysqli_query($db, "SELECT id, name FROM requests ORDER BY name");
    while ($row = mysqli_fetch_assoc($sql)) {
        $services[] = $row;
    }
    return $services;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['repair_id'], $_POST['new_service_id'])) {
    $repairId = intval($_POST['repair_id']);
    $newServiceId = intval($_POST['new_service_id']);
    mysqli_query($db, "UPDATE repairs SET service_id = '{$newServiceId}' WHERE id = '{$repairId}'");
    header("Location: /change_service.php?success=1");
    exit;
}

$repairs = getRepairsWithServices($db);
$services = getAllServices($db);
?>

<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Смена сервиса у ремонта</title>
  <link href="/css/style.css" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="/css/datatables.css">
  <script src="/_new-codebase/front/vendor/jquery/jquery-1.7.2.min.js"></script>
  <script src="/_new-codebase/front/vendor/datatables/1.10.12/jquery.dataTables.min.js"></script>
  <script>

</head>
<body>
  <div class="wrapper">
    <h2>Смена сервиса у ремонта</h2>
    
  </div>
</body>
</html>
