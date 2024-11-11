<?php
function content_list() {
  global $db;
  $content_list = '';
if (\models\User::hasRole('admin', 'acct')) {

$sql = mysqli_query($db, 'SELECT * FROM `documents`;');
if (mysqli_num_rows($sql) != false) {
    while ($row = mysqli_fetch_array($sql)) {
          $document = create_or_get_document($_GET['id'], $row['id']);
          if ($document['deleted'] != 1) {
          $content_list .= '<tr>
          <td>'.$row['id'].'</td>
          <td>'.$row['name'].'</td>
          <td><form method="POST"><select style="width:300px;" name="status_document" data-document-id="'.$document['id'].'"><option value="Да" '.(($document['status'] == 'Да') ? 'selected' : '').'>Да</option><option value="Нет" '.(($document['status'] == 'Нет') ? 'selected' : '').'>Нет</option></select></form></td>
          <td align="center" class="linkz"><a class="t-3" title="Редактировать документ" style="float:left" href="/document/'.$row['id'].'/edit/'.$_GET['id'].'/" ></a><a title="Удалить для этого СЦ" class="t-5 delete_loan" data-id="'.$document['id'].'" style="" href="#"></a></td>
          </tr>';
          }

    }
  return $content_list;
}

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

if (\models\User::hasRole('admin')) {

//$content = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `requests` WHERE `user_id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1;'));
//$content['cats'] = explode('|', $content['cat']);
$sql = mysqli_query($db, 'SELECT * FROM `cats` where `service` = 1 ;');
if (mysqli_num_rows($sql) != false) {
    while ($row = mysqli_fetch_array($sql)) {
      //if (in_array($row['name'], $content['cats'])) {

          $current = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `prices_service` WHERE `service_id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' and `cat_id` = '.$row['id'].' LIMIT 1;'));

          if ($current['COUNT(*)'] == 0) {
          $prices_global = get_prices_global($row['id']);
          mysqli_query($db, 'INSERT INTO `prices_service` (
            `service_id`,
            `cat_id`,
            `block`,
            `component`,
            `access`,
            `anrp`,
            `ato`
            ) VALUES (
            \''.mysqli_real_escape_string($db, $_GET['id']).'\',
            \''.mysqli_real_escape_string($db, $row['id']).'\',
            \''.mysqli_real_escape_string($db, $prices_global['block']).'\',
            \''.mysqli_real_escape_string($db, $prices_global['element']).'\',
            \''.mysqli_real_escape_string($db, $prices_global['acess']).'\',
            \''.mysqli_real_escape_string($db, $prices_global['anrp']).'\',
            \''.mysqli_real_escape_string($db, $prices_global['ato']).'\'
            );') or mysqli_error($db);
          }/* else {
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

?>
<!doctype html>
<html>
<head>
<meta charset=utf-8>
<title>Документы - Панель управления</title>
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

<script >
// Таблица
$(document).ready(function() {
    $('#table_content').dataTable({
      "pageLength": 30,
      "dom": '<"top"flp<"clear">>rt<"bottom"ip<"clear">>', 
      stateSave: true,
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
    });

    $(document).on('selectmenuchange', 'select[name=status_document]', function() {
        var value = $(this).val();
        var id= $(this).data('document-id');
              if (value) {

                  $.get( "/ajax.php?type=update_document_status&value="+value+"&id="+id, function( data ) {

                  });

              }


        return false;
    });

    $(document).on('click', '.delete_loan', function() {
        var value = $(this).data('id');
        var this_tr = $(this).parent().parent();
              if (value && confirm('Удалить документ?')) {

                  $.get( "/ajax.php?type=remove_document&value="+value, function( data ) {
                  this_tr.hide();

                  });

              }


        return false;
    });

} );

</script>
<style>
.ui-selectmenu-button {
width:250px;
}
</style>
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
           <h2>Документы</h2>

  <div class="adm-catalog">

     <div style="vertical-align:middle;">
     <?php if (\models\User::hasRole('admin', 'acct')) { ?>
     <br>
     <div class="add" style="padding-top:0px;display:inline-block;">
     <a style="width: auto;padding-left: 7px;padding-right: 7px;vertical-align: middle;" href="/document/<?=$_GET['id'];?>/add/" class="button">Добавить документ</a>
    </div>

    <?php } ?>

    </div>

     <br>
  <table id="table_content" class="display" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th align="left">№</th>
                <th align="left">Название документа</th>
                <th align="left">Оригинал получен</th>
                <th align="left">Операции</th>
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