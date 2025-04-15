<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/_new-codebase/config.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/includes/configuration.php';

use models\User;

// Проверка доступа по роли
if (User::getData('role') !== 'admin') {
    echo 'Доступ запрещён';
    exit;
}

// Список всех сервисов для выпадающего списка
function getAllServices($db) {
    $services = [];
    $sql = mysqli_query($db, "SELECT user_id, name FROM requests ORDER BY name");
    while ($row = mysqli_fetch_assoc($sql)) {
        $services[] = $row;
    }
    return $services;
}

// Обработка замены сервиса
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['repair_id'], $_POST['new_service_id'])) {
    $repairId = intval($_POST['repair_id']);
    $newServiceId = intval($_POST['new_service_id']);
    mysqli_query($db, "UPDATE repairs SET service_id = '{$newServiceId}' WHERE id = '{$repairId}'");
    header("Location: /change-service-server/?success=1");
    exit;
}

$services = getAllServices($db);
?>

<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Смена сервиса (серверная)</title>
  <link href="/css/style.css" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="/css/datatables.css">
  <script src="/_new-codebase/front/vendor/jquery/jquery-1.7.2.min.js"></script>
  <script src="/_new-codebase/front/vendor/datatables/1.10.12/jquery.dataTables.min.js"></script>
  <script>
    const allServices = <?= json_encode($services) ?>;

    $(document).ready(function() {
      const table = $('#repairs_table').DataTable({
        serverSide: true,
        ajax: '/data_repairs.php',
        processing: true,
        pageLength: 25,
        language: {
          emptyTable: "Нет данных для отображения",
          processing: "Загрузка...",
          search: "Поиск:",
          lengthMenu: "Показать _MENU_ записей",
          info: "Показано с _START_ по _END_ из _TOTAL_ записей",
          infoEmpty: "Нет доступных записей",
          paginate: {
            first: "Первая",
            last: "Последняя",
            next: "Следующая",
            previous: "Предыдущая"
          }
        }
      });

      $('#repairs_table').on('draw.dt', function() {
        $('.service-select').each(function() {
          const select = $(this);
          const id = select.data('id');
          select.empty();
          allServices.forEach(service => {
            const selected = (parseInt(select.val()) === service.user_id) ? 'selected' : '';
            select.append('<option value="' + service.user_id + '" ' + selected + '>' + service.name + '</option>');
          });
        });
      });
    });
  </script>
</head>
<body>
<div class="viewport-wrapper">

  <div class="site-header">
    <div class="wrapper">
      <div class="logo">
        <a href="/dashboard/"><img src="/i/logo.png" alt=""/></a>
        <span>Сервис</span>
      </div>
      <div class="logout">
        <a href="/logout/">Выйти, <?=User::getData('login');?></a>
      </div>
    </div>
  </div>

  <div class="wrapper">
    <?=top_menu_admin();?>

    <div class="adm-tab">
      <?=menu_dash();?>
    </div>

    <br>
    <h2>Смена сервиса у ремонта (серверная)</h2>
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
    </table>

  </div> <!-- /.wrapper -->
</div> <!-- /.viewport-wrapper -->
</body>
</html>
