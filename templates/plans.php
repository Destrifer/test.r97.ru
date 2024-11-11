<?php

use models\staff\Staff;
use models\Users;

function content_list() {
  global $db;


/*$sql2 = mysqli_query($db, 'SELECT * FROM `cats_users` WHERE `service_id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' and `service` = 1;');
while ($row2 = mysqli_fetch_array($sql2)) {
$cats[] = $row2['cat_id'];
}  */
$content_list = '';
$rus_months = array('0', 'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь');
$sql = mysqli_query($db, 'SELECT * FROM `plans` ;');
if (mysqli_num_rows($sql) != false) {
    while ($row = mysqli_fetch_array($sql)) {

          //$prices = get_prices($row['id']);
          $user_info = Users::getUser(['id' => $row['user_id'], 'is_active' => true]);
          if(!$user_info){
            continue;
          }
  
          $name_month = (int)$row['month'];

          $content_list .= '<tr>
          <td>'.$row['month'].'.'.$row['year'].'  ('.$rus_months[$name_month].')</td>
          <td>01.'.$row['month'].'.'.$row['year'].'</td>
          <td style="font-size:18px">'.$user_info['nickname'].'</td>
          <td><input class="editable" style="width:110px;" type="text" name="block" value="'.$row['plan'].'" data-plan-id="'.$row['id'].'"> р.</td>
          </tr>';
      }
  return $content_list;
}

}

function get_prices_global($id) {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `prices` where `cat_id` = '.$id.' ;');
if (mysqli_num_rows($sql) != false) {
  return $prices = mysqli_fetch_array($sql);
}
}

function get_prices($id) {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `prices_service` where `service_id` = '.$_GET['id'].' and `cat_id` = '.$id.' ;');
if (mysqli_num_rows($sql) != false) {
  return $prices = mysqli_fetch_array($sql);
} else {
  return $prices = array('block' => 0, 'component' => 0, 'access' => 0, 'anrp' => 0, 'ato' => 0);
}
}

check_prices();

function check_prices() {
  global $db;

//if (\models\User::hasRole('admin')) {

//$content = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `requests` WHERE `user_id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1;'));
//$content['cats'] = explode('|', $content['cat']);


$masters = Staff::getStaffList(['service_id' => 6, 'is_active' => true, 'role' => ['master', 'service']]);

foreach ($masters as $row) {

          $current = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `plans` WHERE `user_id` = '.$row['user']['id'].' and `month` = "'.date('m').'" and `year` = "'.date('Y').'" LIMIT 1;'));
  
          if ($current['COUNT(*)'] == 0) {
          mysqli_query($db, 'INSERT INTO `plans` (
            `user_id`,
            `year`,
            `month`
            ) VALUES (
            \''.mysqli_real_escape_string($db, $row['user']['id']).'\',
            \''.mysqli_real_escape_string($db, date('Y')).'\',
            \''.mysqli_real_escape_string($db, date('m')).'\'
            );') or mysqli_error($db);
          }

          /* $current2 = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `plans` WHERE `user_id` = \''.mysqli_real_escape_string($db, $row['user']['id']).'\' and `month` = \''.date("m", strtotime("+1 months")).'\' and `year` = \''.date('Y', strtotime("+1 months")).'\' LIMIT 1;'));
   
          if ($current2['COUNT(*)'] == 0) {
          mysqli_query($db, 'INSERT INTO `plans` (
            `user_id`,
            `year`,
            `month`
            ) VALUES (
            \''.mysqli_real_escape_string($db, $row['user']['id']).'\',
            \''.mysqli_real_escape_string($db, date('Y', strtotime("+1 months"))).'\',
            \''.mysqli_real_escape_string($db, date('m', strtotime("+1 months"))).'\'
            );') or mysqli_error($db);
          } */
          /* else {
          $prices_global = get_prices_global($row['id']);
          mysqli_query($db, 'UPDATE `prices_service` SET
            `service_id`,
            `cat_id`,
            `block`,
            `component`,
            `access`,
            `anrp`,
            `ato`
            WHERE `service_id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' and `cat_id` =
            \''.mysqli_real_escape_string($db, $_GET['id']).'\',
            \''.mysqli_real_escape_string($db, $row['id']).'\',
            \''.mysqli_real_escape_string($db, $prices_global['block']).'\',
            \''.mysqli_real_escape_string($db, $prices_global['component']).'\',
            \''.mysqli_real_escape_string($db, $prices_global['access']).'\',
            \''.mysqli_real_escape_string($db, $prices_global['anrp']).'\',
            \''.mysqli_real_escape_string($db, $prices_global['ato']).'\'
            );') or mysqli_error($db);
          } */




    }


}

?>
<!doctype html>
<html>
<head>
<meta charset=utf-8>
<title>План пользователей - Панель управления</title>
<link href="/css/fonts.css" rel="stylesheet" />
<link href="/css/style.css" rel="stylesheet" />
<script src="/_new-codebase/front/vendor/jquery/jquery-1.7.2.min.js"  ></script>
<script src="/js/jquery-ui.min.js"></script>
<script src="/js/jquery.placeholder.min.js"></script>
<script src="/js/jquery.formstyler.min.js"></script>
<script src="/js/main.js"></script>

<script src="/notifier/js/index.js"></script>
<link rel="stylesheet" type="text/css" href="/notifier/css/style.css">
<link rel="stylesheet" href="/_new-codebase/front/vendor/animate.min.css" />
<script src='/_new-codebase/front/vendor/mustache/mustache.min.js'></script>
<script src="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.concat.min.js"></script>
<link rel="stylesheet" href="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.min.css" />

<script  src="/_new-codebase/front/vendor/datatables/1.10.12/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="/css/datatables.css">
<script src="/_new-codebase/front/vendor/datatables/1.10.19/date-de.js"></script>

<script >
// Таблица
$(document).ready(function() {

var groupColumn = 0;

    var table = $('#table_content').dataTable({
      "pageLength": 30,
      "dom": '<"top"flp<"clear">>rt<"bottom"ip<"clear">>',
      "order": [[ groupColumn, 'desc' ]],       
      "columnDefs": [
            { "visible": false, "targets": groupColumn },
             {   "targets": [ 1 ],
                "visible": false
            },
            { type: 'de_date', targets: 1 }

        ],
        "order": [[ 1, "desc" ]],
        "drawCallback": function ( settings ) {
            var api = this.api();
            var rows = api.rows( {page:'current'} ).nodes();
            var last=null;

            api.column(groupColumn, {page:'current'} ).data().each( function ( group, i ) {
                if ( last !== group ) {
                    $(rows).eq( i ).before(
                        '<tr class="group"><td colspan="2">'+group+'</td></tr>'
                    );

                    last = group;
                }
            } );

        },
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

    $(document).on('change', 'input.editable', function() {
        var plan_id = $(this).data('plan-id');
        var value = $(this).val();
              if (value) {

                  $.get( "/ajax.php?type=update_plan&plan_id="+plan_id+"&value="+value, function( data ) {

                 //$('select[name=parts_parts]').html(data.html).selectmenu( "refresh" );
                  //$('input[name="serial_parts_hidden"]').val(value);


                  });

              }


        return false;
    });

$('#table_content tbody').on( 'click', 'tr.group', function () {
        var currentOrder = table.order()[0];
        if ( currentOrder[0] === groupColumn && currentOrder[1] === 'asc' ) {
            table.order( [ groupColumn, 'desc' ] ).draw();
        }
        else {
            table.order( [ groupColumn, 'asc' ] ).draw();
        }
    } );

} );


</script>
<style>
tr.group,
tr.group:hover {
    background: #77ad07;
    background-color: #77ad07 !important;
    color: #fff;
}
</style>
</head>

<body>

<div class="viewport-wrapper">

<div class="site-header">
  <div class="wrapper">

    <div class="logo">
      <a href="/dashboard/"><img src="/i/logo.png" alt=""/></a>
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
           <h2>План пользователей</h2>

  <div class="adm-catalog">

     <br>
  <table id="table_content" class="display" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th align="left">Месяц</th>
                <th align="left">Дата</th>
                <th align="left">Пользователь</th>
                <th align="left">План</th>
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