<?php

use models\User;
use program\core;

if (!User::hasRole('admin') || empty($_GET['query'])) {
    exit;
}

$serviceID = $_GET['query'];

if (!empty(core\App::$URLParams['ajax'])) {
    switch (core\App::$URLParams['ajax']) {
        case 'upd-price':
            mysqli_query($db, 'UPDATE `transfer_service` SET  `' . core\App::$URLParams['type_field'] . '` = "' . core\App::$URLParams['value'] . '" 
            WHERE `id` = ' . core\App::$URLParams['id']) or mysqli_error($db);
            exit;
    }
}

/* Добавление отсутствующих категорий с транспортировкой */
$sql = mysqli_query($db, 'SELECT * FROM `cats` where `travel` = 1 AND `id` IN (SELECT `cat_id` FROM `cats_users` WHERE `service_id` = ' . $serviceID . ' AND `service` = 1)');
while ($row = mysqli_fetch_array($sql)) {
    $count_prices = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `transfer_service` WHERE `cat_id` = ' . $row['id'] . ' AND `service_id` = ' . $serviceID));
    if (!$count_prices['COUNT(*)']) {
        mysqli_query($db, 'INSERT INTO `transfer_service` (
            `cat_id`,
            `service_id`
            ) VALUES (
            ' . $row['id'] . ',
            ' . $serviceID . '
            );') or mysqli_error($db);
    }
}

/* Удаление несуществующих категорий с транспортировкой */
$sql = mysqli_query($db, 'SELECT `id`, `cat_id` FROM `transfer_service` WHERE `service_id` = ' . $serviceID);
while ($row = mysqli_fetch_array($sql)) {
    $count_service = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `cats` where `travel` = 1 and `id` = ' . $row['cat_id']));
    if ($count_service['COUNT(*)'] == 0) {
        mysqli_query($db, 'DELETE FROM `transfer_service` WHERE `id` = ' . $row['id']) or mysqli_error($db);
    }
}


function content_list()
{
    global $db, $serviceID;
    $content_list = '';
    $sql = mysqli_query($db, 'SELECT * FROM `transfer_service` WHERE `service_id` = ' . $serviceID);
    if (!mysqli_num_rows($sql)) {
        return '';
    }
    while ($row = mysqli_fetch_array($sql)) {
        $content_list .= '<tr>
          <td>' . $row['id'] . '</td>
          <td>' . cat($row['cat_id'])['name'] . '</td>
          <td><input class="editable" style="width:120px;" type="text" name="shop" value="' . $row['shop'] . '" data-id="' . $row['id'] . '"></td>
          <td><input class="editable" style="width:120px;" type="text" name="buyer" value="' . $row['buyer'] . '" data-id="' . $row['id'] . '"></td>
          <td><input class="editable" style="width:120px;" type="text" name="zone_1" value="' . $row['zone_1'] . '" data-id="' . $row['id'] . '"></td>
          <td><input class="editable" style="width:120px;" type="text" name="zone_2" value="' . $row['zone_2'] . '" data-id="' . $row['id'] . '"></td>
          <td><input class="editable" style="width:120px;" type="text" name="zone_3" value="' . $row['zone_3'] . '" data-id="' . $row['id'] . '"></td>
          <td><input class="editable" style="width:120px;" type="text" name="zone_4" value="' . $row['zone_4'] . '" data-id="' . $row['id'] . '"></td>
      </tr>';
    }
    return $content_list;
}

function cat($id)
{
    global $db;
    return mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `cats` where `id` = ' . $id . ';'));
}

function getServiceLink($serviceID)
{
    global $db;
    $row = mysqli_fetch_assoc(mysqli_query($db, 'SELECT `name` FROM `requests` where `user_id` = ' . $serviceID . ';'));
    return '<a href="/service/' . $serviceID . '/edit/">' . $row['name'] . '</a>';
}
?>
<!doctype html>
<html>

<head>
    <meta charset=utf-8>
    <title>Тарифы транспорт - Панель управления</title>
    <link href="/css/fonts.css" rel="stylesheet" />
    <link href="/css/style.css" rel="stylesheet" />
    <script src="/_new-codebase/front/vendor/jquery/jquery.min.js"></script>
    <script src="/js/jquery-ui.min.js"></script>
    <script src="/js/jquery.placeholder.min.js"></script>
    <script src="/js/jquery.formstyler.min.js"></script>
    <script src="/js/main.js"></script>

    <script src="/notifier/js/index.js"></script>
    <link rel="stylesheet" href="/notifier/css/style.css">
    <link rel="stylesheet" href="/_new-codebase/front/vendor/animate.min.css" />
    <script src='/_new-codebase/front/vendor/mustache/mustache.min.js'></script>
    <script src="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.concat.min.js"></script>
    <link rel="stylesheet" href="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.min.css" />

    <script src="/_new-codebase/front/vendor/datatables/1.10.12/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="/css/datatables.css">

    <script >
        // Таблица
        $(document).ready(function() {
            $('#table_content').dataTable({
                stateSave: false,
                "dom": '<"top"flp<"clear">>rt<"bottom"ip<"clear">>',
                "pageLength": <?= $config['page_limit']; ?>,
                "oLanguage": {
                    "sLengthMenu": "Показывать _MENU_ записей на страницу",
                    "sZeroRecords": "Записей нет.",
                    "sInfo": "Показано от _START_ до _END_ из _TOTAL_ записей",
                    "sInfoEmpty": "Записей нет.",

                    "oPaginate": {
                        "sFirst": "Первая",
                        "sLast": "Последняя",
                        "sNext": "Следующая",
                        "sPrevious": "Предыдущая",
                    },
                    "sSearch": "Поиск",
                    "sInfoFiltered": "(отфильтровано из _MAX_ записи/(ей)"
                }
            });

            $(document).on('change', 'input.editable', function() {
                $.get("?ajax=upd-price&value=" + $(this).val() + "&id=" + $(this).data('id') + "&type_field=" + $(this).attr('name'));
            });


        });
    </script>
</head>

<body>

    <div class="viewport-wrapper">

        <header class="site-header">
            <div class="wrapper">

                <div class="logo">
                    <a href="/dashboard/"><img src="<?= $config['url']; ?>i/logo.png" alt="" /></a>
                    <span>Сервис</span>
                </div>

                <div class="not-container">
                    <button style="position:relative;    margin-left: 120px;   margin-top: 15px;" type="button" class="button-default show-notifications js-show-notifications animated swing">
                        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="30" height="32" viewBox="0 0 30 32">
                            <defs>
                                <g id="icon-bell">
                                    <path class="path1" d="M15.143 30.286q0-0.286-0.286-0.286-1.054 0-1.813-0.759t-0.759-1.813q0-0.286-0.286-0.286t-0.286 0.286q0 1.304 0.92 2.223t2.223 0.92q0.286 0 0.286-0.286zM3.268 25.143h23.179q-2.929-3.232-4.402-7.348t-1.473-8.652q0-4.571-5.714-4.571t-5.714 4.571q0 4.536-1.473 8.652t-4.402 7.348zM29.714 25.143q0 0.929-0.679 1.607t-1.607 0.679h-8q0 1.893-1.339 3.232t-3.232 1.339-3.232-1.339-1.339-3.232h-8q-0.929 0-1.607-0.679t-0.679-1.607q3.393-2.875 5.125-7.098t1.732-8.902q0-2.946 1.714-4.679t4.714-2.089q-0.143-0.321-0.143-0.661 0-0.714 0.5-1.214t1.214-0.5 1.214 0.5 0.5 1.214q0 0.339-0.143 0.661 3 0.357 4.714 2.089t1.714 4.679q0 4.679 1.732 8.902t5.125 7.098z" />
                                </g>
                            </defs>
                            <g fill="#000000">
                                <use xlink:href="#icon-bell" transform="translate(0 0)"></use>
                            </g>
                        </svg>

                        <div class="notifications-count js-count"></div>

                    </button>
                </div>

                <div class="logout">

                    <a href="/logout/">Выйти, <?= \models\User::getData('login'); ?></a>
                </div>

            </div>
        </header>

        <main class="wrapper">

            <?= top_menu_admin(); ?>

            <nav class="adm-tab"><?= menu_dash(); ?></nav>

            <h2 style="margin: 32px 0 16px;">Тарифы транспорт</h2>
            <h3 style="margin-bottom: 48px; font-weight: 400;"><?= getServiceLink($serviceID); ?></h3>

            <table id="table_content" class="display" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th align="left">№</th>
                        <th align="left">Тип техники</th>
                        <th align="left">Магазин</th>
                        <th align="left">Потребитель</th>
                        <th align="left">Зона 1 (<50км)< /th>
                        <th align="left">Зона 2 (50-100км)</th>
                        <th align="left">Зона 3 (100-150км)</th>
                        <th align="left">Зона 4 (>150км)</th>
                    </tr>
                </thead>

                <tbody>
                    <?= content_list(); ?>
                </tbody>
            </table>

        </main>
    </div>
</body>

</html>