
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once $_SERVER['DOCUMENT_ROOT'].'/_new-codebase/config.php';

function getRepairsWithServices($db) {
    $repairs = [];
    $sql = mysqli_query($db, "SELECT r.id, r.service_id, s.name AS service_name, s.user_id AS request_user_id
                              FROM repairs r 
                              LEFT JOIN requests s ON r.service_id = s.user_id 
                              ORDER BY r.id DESC");

    if (!$sql) {
        echo '<p style="color:red">Ошибка запроса: ' . mysqli_error($db) . '</p>';
        return [];
    }

    while ($row = mysqli_fetch_assoc($sql)) {
        $repairs[] = $row;
    }
    return $repairs;
}

function getAllServices($db) {
    $services = [];
    $sql = mysqli_query($db, "SELECT user_id, name FROM requests ORDER BY name");
    while ($row = mysqli_fetch_assoc($sql)) {
        $services[] = $row;
    }
    return $services;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['repair_id'], $_POST['new_service_id'])) {
    $repairId = intval($_POST['repair_id']);
    $newServiceId = intval($_POST['new_service_id']);
    mysqli_query($db, "UPDATE repairs SET service_id = '{$newServiceId}' WHERE id = '{$repairId}'");
    header("Location: /change-service/?success=1");
    exit;
}

$repairs = getRepairsWithServices($db);
$services = getAllServices($db);
?>

<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Смена сервиса</title>
  <link href="/css/style.css" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="/css/datatables.css">
  <script src="/_new-codebase/front/vendor/jquery/jquery-1.7.2.min.js"></script>
  <script src="/_new-codebase/front/vendor/datatables/1.10.12/jquery.dataTables.min.js"></script>
  <script>
  $(document).ready(function() {
    $('#repairs_table').DataTable({
        "language": {
            "emptyTable": "Нет данных для отображения"
        }
    });
  });
  </script>
</head>
<body>
  <div class="wrapper">
    <h2>Смена сервиса у ремонта</h2>
    <?php if (isset($_GET['success'])): ?>
      <div style="color: green; font-weight: bold;">Сервис успешно изменен</div>
    <?php endif; ?>

    <table id="repairs_table" class="display" width="100%">
      <thead>
        <tr>
          <th>ID ремонта</th>
          <th>Текущий сервис</th>
          <th>Новый сервис</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($repairs as $repair): ?>
        <tr>
          <td><?= $repair['id'] ?></td>
          <td><?= $repair['service_name'] ? htmlspecialchars($repair['service_name']) : '<span style="color:red;">Сервис не найден</span>' ?></td>
          <td>
            <form method="POST" onsubmit="return confirm('Сменить сервис?');" style="display: flex; gap: 4px;">
              <input type="hidden" name="repair_id" value="<?= $repair['id'] ?>">
              <select name="new_service_id">
                <?php foreach ($services as $service): ?>
                  <option value="<?= $service['user_id'] ?>" <?= $service['user_id'] == $repair['service_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($service['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <button type="submit">OK</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
