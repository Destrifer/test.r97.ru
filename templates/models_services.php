<?php
function content_list() {
  global $db;

if (\models\User::hasRole('admin', 'slave-admin', 'taker')) {
$sLimit = "ORDER by `id` ".$_GET['sSortDir_0']." LIMIT ".mysqli_real_escape_string($db, $_GET['iDisplayStart'] ).", ".mysqli_real_escape_string($db, $_GET['iDisplayLength'] );

if (  $_GET['sSearch'] != "" ) {

$sql_total = @mysqli_fetch_array(mysqli_query($db, 'SELECT `id` FROM `models` where `name` REGEXP \''.$_GET['sSearch'].'\' ;'));
//echo 'SELECT `id` FROM `models` where `name` REGEXP \''.$_GET['sSearch'].'\' ;';
if ($sql_total['id']) {
     $sql_tmp = mysqli_query($db, 'SELECT `id` FROM `models` where `name` REGEXP \''.$_GET['sSearch'].'\' ;');
      while ($model_check = mysqli_fetch_array($sql_tmp)) {
      $array_models[] = $model_check['id'];
      }
$sql_model = '
`model_id` IN ('.implode(', ',$array_models).')';
}

$sql_total_client = mysqli_query($db, 'SELECT `id` FROM `cats` where `name` REGEXP \''.$_GET['sSearch'].'\' ;');
if (mysqli_num_rows($sql_total_client) != false) {
while ($row_cliend = mysqli_fetch_array($sql_total_client)) {
  $array_cats[] = $row_cliend['id'];
}
}


if ($array_cats) {
$sql_cat = '
`cat_id` IN ('.implode(', ',$array_cats).')';
}

if ($sql_cat || $sql_model) {

//echo $sql_model;

 if ($sql_cat) {
 $del = ' or ';
 }
$sWhere = '
and (
'.$sql_cat.'
'.$del.'
'.$sql_model.'
) ';
}

}

$sql = mysqli_query($db, 'SELECT * FROM `models_users` where `service_id` = '.$_GET['id'].' '.$sWhere.' '.$sOrder.' '.$sLimit.';');
$sql_total_row = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `models_users` where `service_id` = '.$_GET['id'].' '.$sWhere.' '.$sOrder.' '.$sLimit.' ;'));
//echo 'SELECT * FROM `models_users` where `service_id` = '.$_GET['id'].' '.$sWhere.' '.$sOrder.' '.$sLimit.';';
if (mysqli_num_rows($sql) != false) {
      while ($row = mysqli_fetch_array($sql)) {
      $cats = model_cat_info($row['cat_id']);
      $true = ($row['service'] == 1) ? 'Да' : 'Нет';
      $content_list[] = $row['id'];
      $content_list[] = $cats['name'];
      $content_list[] = $row['name'];
      $content_list[] = '<form method="POST"><select  name="service" data-model-id="'.$row['id'].'"><option value="Нет" '.(($row['service'] == 'Нет') ? 'selected' : '').'>Нет</option><option value="Да" '.(($row['service'] == 'Да') ? 'selected' : '').'>Да</option></select></form>';
      $content_list[] = '<div class="linkz"><a class="t-5" href="/del-service-model/'.$row['id'].'/" title="Удалить" ></a>&nbsp;&nbsp;&nbsp;&nbsp;<a class="t-3" href="/edit-model-service/'.$row['id'].'/" title="Редактировать" ></a></div>';
      $rows[] = $content_list;
      unset($content_list);
      }


      } else {
      $content_list = '<tr><td colspan="4">'.$row['id'].'</td>';
      }

}

if (count($rows) == 0) {
$data = [];
} else {
$data = $rows;
}
//print_r($rows);
$results = ["sEcho" => $_GET['sEcho'],
        	"iTotalRecords" => $sql_total_row['COUNT(*)'],
        	"iTotalDisplayRecords" => $sql_total_row['COUNT(*)'],
        	"aaData" => $data ];

    return json_encode($results);
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

$content = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `requests` WHERE `user_id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1;'));


function check_prices() {
  global $db;

if (\models\User::hasRole('admin')) {

//$content = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `requests` WHERE `user_id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1;'));
//$content['cats'] = explode('|', $content['cat']);
$sql = mysqli_query($db, 'SELECT * FROM `models`;');
if (mysqli_num_rows($sql) != false) {
    while ($row = mysqli_fetch_array($sql)) {
      //if (in_array($row['name'], $content['cats'])) {

          $current = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*),name FROM `models_users` WHERE `service_id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' and `model_id` = '.$row['id'].' LIMIT 1;'));

          if ($current['COUNT(*)'] == 0) {
          mysqli_query($db, 'INSERT INTO `models_users` (
            `service_id`,
            `cat_id`,
            `model_id`,
            `name`,
            `service`
            ) VALUES (
            \''.mysqli_real_escape_string($db, $_GET['id']).'\',
            \''.mysqli_real_escape_string($db, $row['cat']).'\',
            \''.mysqli_real_escape_string($db, $row['id']).'\',
            \''.mysqli_real_escape_string($db, $row['name']).'\',
            \''.mysqli_real_escape_string($db, $row['service']).'\'
            );') or mysqli_error($db);
          } else {
            if ($current['name'] != $row['name']) {
            mysqli_query($db, 'UPDATE `models_users` SET `name` = \''.$row['name'].'\' WHERE `service_id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' and `model_id` = '.$row['id'].' LIMIT 1;');

            }
       }


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



     // }
    }

}


}
}


if ($_GET['ajaxed'] == 1) {
echo content_list();
exit;
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
<style>
.ui-selectmenu-button {
width:150px;
}
</style>
<script >
// Таблица
$(document).ready(function() {
    $('#table_content').dataTable({
      "pageLength": <?=$config['page_limit'];?>,
      stateSave: true,
"bProcessing": true,
      "bServerSide": true,
      "sAjaxSource": "<?=(strpos($_SERVER['REQUEST_URI'], '?') ? $_SERVER['REQUEST_URI'].'&ajaxed=1' : $_SERVER['REQUEST_URI'].'?ajaxed=1');?>",
"drawCallback": function ( settings ) {



  $('#table_content select:not(.nomenu)').selectmenu({
    open: function(){
      $(this).selectmenu('menuWidget').css('width', $(this).selectmenu('widget').outerWidth());
    },
        change: function( event, data ) {
        var selValue = $(this).val();
       if ($(".validate_form").length) {
        $(".validate_form").validate().element(this);
        if (selValue.length > 0) {
            $(this).next('div').removeClass("input-validation-error");
        } else {
            $(this).next('div').addClass("input-validation-error");
        }
        }

      }
  }).addClass("selected_menu");

        },
      "dom": '<"top"flp<"clear">>rt<"bottom"ip<"clear">>', 
      "oLanguage": {
            "sLengthMenu": "Показывать _MENU_ записей на страницу",
            "sZeroRecords": "Записей нет.",
            "sInfo": "Показано от _START_ до _END_ из _TOTAL_ записей",
            "sInfoEmpty": "Записей нет.",
            "sProcessing": "Загружаются данные...",
            "oPaginate": {
                 "sFirst": "Первая",
                 "sLast": "Последняя",
                 "sNext": "Следующая",
                 "sPrevious": "Предыдущая",
                },
            "sSearch": "Поиск",
            "sInfoFiltered": "(отфильтровано из _MAX_ записи/(ей)"
        }});

   /* $(document).on('change', 'input.editable', function() {
        var cat_id = $(this).data('cat-id');
        var service_id = $(this).data('service-id');
        var type_field = $(this).attr('name');
        var value = $(this).val();
              if (value) {

                  $.get( "/ajax.php?type=update_service_price&cat_id="+cat_id+"&service_id="+service_id+"&value="+value+"&type_field="+type_field, function( data ) {

                 //$('select[name=parts_parts]').html(data.html).selectmenu( "refresh" );
                  //$('input[name="serial_parts_hidden"]').val(value);


                  });

              }


        return false;
    }); */

    $(document).on('selectmenuchange', 'select[name=service]', function() {
        var value = $(this).val();
        var id= $(this).data('model-id');
        //var id= $(this).data('item-id');
              if (value) {

                  $.get( "/ajax.php?type=update_user_models&user=<?=$_GET['id'];?>&value="+value+"&id="+id, function( data ) {

                  });

              }


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
           <h2>Обслуживаемые модели <?=$content['name'];?></h2>

  <div class="adm-catalog">

     <br>
  <table id="table_content" class="display" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th align="left">№</th>
                <th align="left">Категория</th>
                <th align="left">Модель</th>
                <th align="left">Обслуживается</th>
                <th align="left">Операции</th>
            </tr>
        </thead>


</table>


</div>


        </div>
  </div>
</div>
</body>
</html>