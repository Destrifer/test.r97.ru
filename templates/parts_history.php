<?php
$part = part_by_id($_GET['id']);

function content_list() {
  global $db;

if (\models\User::hasRole('admin')) {

$sql = mysqli_query($db, 'SELECT * FROM `parts_sc_log` where `part_id` = '.$_GET['id']);
if (mysqli_num_rows($sql) != false) {
      while ($row = mysqli_fetch_array($sql)) {

$sql2 = mysqli_query($db, 'SELECT * FROM `parts` where `id` = '.$row['part_id'].';');
      while ($row2 = mysqli_fetch_array($sql2)) {
      $model = get_model_by_id($row2['model_id']);
      $service = service_request_info($row['service_id']);
      $content_list .= '<tr>
      <td >'.$model.'</td>
      <td>'.$row2['serial'].'</td>
      <td>'.$service['name'].'</td>
      <td><a href="https://crm.r97.ru/edit-repair/'.$row['repair_id'].'/">'.$row['repair_id'].'</td>
      <td >'.$row['date_get'].'</td>
      <td >'.$row['date_use'].'</td>
      </tr>';
      }

      }
}


    return $content_list;
}

}

function cat_by_id($id) {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `cats` where `id` = \''.$id.'\' LIMIT 1;');
      while ($row = mysqli_fetch_array($sql)) {
      $content = $row;
      }
    return $content;
}

function get_model_by_id($id) {
  global $config, $db;
$sql = mysqli_query($db, 'SELECT `name` FROM `models` WHERE `id` = \''.mysqli_real_escape_string($db, $id).'\'');
return mysqli_fetch_array($sql)['name'];
}

function get_parents($id) {
  global $config, $db;
$sql = mysqli_query($db, 'SELECT `model_id`, `serial` FROM `parts` WHERE `parent_id` = \''.mysqli_real_escape_string($db, $id).'\' ');
if (mysqli_num_rows($sql) > 0) {

//$names .= '<table>';
while ($row = mysqli_fetch_array($sql)) {
$content['models_array'][$row['model_id']][] = $row['serial'];
}

//print_r($content['models_array']);

foreach ($content['models_array'] as $model_id => $serial) {
  $names .= '<tr>';
  $names .= '<td>'.get_model_by_id($model_id)."</td>";
  $names .= '<td>'.get_serial_name($model_id, $serial).'</td>';
  $names .= '</tr>';
}




//$names .= '</table>';

}
return $names;
}

function get_provider_name($id)  {
  global $config, $db;
$sql = mysqli_query($db, 'SELECT `name` FROM `providers` WHERE `id` = \''.mysqli_real_escape_string($db, $id).'\'');
return mysqli_fetch_array($sql)['name'];
}

function get_serial_name($id, $currents = '') {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `serials` where `model_id` = '.$id);
      while ($row = mysqli_fetch_array($sql)) {
      $order = ($row['order']) ? ''.$row['order'] : '';
      if (in_array($row['serial'], $currents)) {
      $glue[] = get_provider_name($row['provider_id']).' ('.$order.')';
      } else {
      // $content .= get_provider_name($row['serial_provider']).''.$order.', ';
      }
      }
    $content = @implode(', ', $glue);
    return $content;
}

?>
<!doctype html>
<html>
<head>
<meta charset=utf-8>
<title>Панель управления</title>
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
      "responsive": true,
      "dom": '<"top"flp<"clear">>rt<"bottom"ip<"clear">>', 
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

    $(document).on('change', 'input.editable', function() {
        var id = $(this).data('id');
        var value = $(this).val();

                  $.get( "/ajax.php?type=update_place_service&value="+value+"&id="+id, function( data ) {

                 //$('select[name=parts_parts]').html(data.html).selectmenu( "refresh" );
                  //$('input[name="serial_parts_hidden"]').val(value);


                  });


        return false;
    });

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
           <h2><?=$part['list'];?></h2>  <br>

  <div class="adm-catalog">

   <table id="table_content" class="display" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th align="left" data-priority="2" >Модель</th>
                <th align="left" data-priority="8">Серийный номер</th>
                <th align="left" data-priority="8">Сервис</th>
                <th align="left" data-priority="7">Ремонт</th>
                <th align="left" data-priority="1">Дата получения</th>
                <th align="left" data-priority="1">Дата использования</th>
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