<?php

use models\staff\Staff;
use models\Users;

function get_request_info($id) {
  global $db;
return mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `requests` WHERE `user_id` = '.$id));
}



function content_list() {
  global $db;
  $content_list = '';
$sql = mysqli_query($db, 'SELECT * FROM `'.Users::TABLE.'` where `role_id` IN (4,5);');
      while ($row = mysqli_fetch_array($sql)) {

      $block = ($row['status_id'] != 2) ? '<a title="Заблокировать" style="background: none;    padding-bottom: 6px;padding-right: 7px;" href="/block-personal/'.$row['id'].'/"><img src="/img/skull.png"></a>' : '<a title="Разблокировать" style="background: none;    padding-bottom: 6px;padding-right: 7px;" href="/unblock-personal/'.$row['id'].'/"><img src="/img/heartbeat.png"></a>';
      $block_style = ($row['status_id'] != 2) ? '' : 'style="background: rgba(255, 71, 71, 0.13);"';
      if ($row['role_id'] == 4) {
      $status = 'Мастер';
      $master = Staff::getStaff(['id' => $row['id']]);
      $name = $master['full_name'];
      }
      if ($row['role_id'] == 5) {
      $status = 'Приемщик';
      $name = $row['nickname'];
      }
      $content_list .= '<tr '.$block_style.'><td '.$block_style.'>'.$row['id'].'</td><td>'.$name.'</td><td style="width:100px">'.$row['email'].'</td><td style="width:100px">'.$status.'</td>
      <td align="center" class="linkz"><a class="t-3" href="/edit-personal/'.$row['id'].'/" title="Редактировать" ></a> <a onclick=\'return confirm("Удалить пользователя?")\' title="Удалить" class="t-5" style="float:right" href="/del-personal/'.$row['id'].'/"></a> '.$block.'</td>
      </tr>';

      unset($status);
      }

    return $content_list;
}
?>
<!doctype html>
<html>
<head>
<meta charset=utf-8>
<title>Сотрудники сервиса - Панель управления</title>
<link href="<?=$config['url'];?>css/fonts.css" rel="stylesheet" />
<link href="<?=$config['url'];?>css/style.css" rel="stylesheet" />
<script src="/_new-codebase/front/vendor/jquery/jquery-1.7.2.min.js"  ></script>
<script src="<?=$config['url'];?>js/jquery-ui.min.js"></script>
<script src="<?=$config['url'];?>js/jquery.placeholder.min.js"></script>
<script src="<?=$config['url'];?>js/jquery.formstyler.min.js"></script>
<script src="<?=$config['url'];?>js/main.js"></script>

<script src="<?=$config['url'];?>notifier/js/index.js"></script>
<link rel="stylesheet" type="text/css" href="<?=$config['url'];?>notifier/css/style.css">
<link rel="stylesheet" href="/_new-codebase/front/vendor/animate.min.css" />
<script src='/_new-codebase/front/vendor/mustache/mustache.min.js'></script>
<script src="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.concat.min.js"></script>
<link rel="stylesheet" href="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.min.css" />

<script  src="/_new-codebase/front/vendor/datatables/1.10.12/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?=$config['url'];?>css/datatables.css">
<script  src="/_new-codebase/front/vendor/datatables/2.1.1/dataTables.responsive.min.js"></script>
<link rel="stylesheet" type="text/css" href="/_new-codebase/front/vendor/datatables/2.1.1/responsive.dataTables.min.css">
<script >
// Таблица
$(document).ready(function() {
    $('#table_content').dataTable({
      stateSave:false,
      "dom": '<"top"flp<"clear">>rt<"bottom"ip<"clear">>', 
      "responsive": true,
      "ordering": false,
      "pageLength": <?=$config['page_limit'];?>,
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
        }});
} );

</script>
</head>

<body>

<div class="viewport-wrapper">

<div class="site-header">
  <div class="wrapper">

    <div class="logo">
      <a href="/dashboard/"><img src="<?=$config['url'];?>i/logo.png" alt=""/></a>
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

      <a href="/logout/">Выйти, <?=\models\User::getData('login');?></a>
    </div>

  </div>
</div><!-- .site-header -->

<div class="wrapper">

<?=top_menu_admin();?>

  <div class="adm-tab">

 <?=menu_dash();?>
  </div><!-- .adm-tab -->
           <br>
           <h2>Сотрудники сервиса</h2>
           <br>
  <div class="adm-catalog">


     <div class="add" style="padding-top:0px;">
      <a style="width: auto;padding-left: 7px;padding-right: 7px;" href="/add-personal/" class="button">Добавить пользователя</a>
    </div>  <br>

  <table id="table_content" class="display" cellspacing="0" width="100%" >
        <thead>
            <tr>
                <th align="left" data-priority="3">ID</th>
                <th align="left" data-priority="3">ФИО</th>
                <th align="left" data-priority="3" style="width:50px!important;max-width:50px;">Email</th>
                <th align="left" data-priority="3">Статус</th>

                <th data-priority="3" >Операции</th>
            </tr>
        </thead>

        <tbody>
        <?=content_list();?>
        </tbody>
</table>


</div>


        </div>
  </div>
</div>
</body>
</html>