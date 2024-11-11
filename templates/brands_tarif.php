<?php

$brand_info = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `brands` WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1;'));


function content_list() {
  global $db;

if (\models\User::hasRole('admin')) {

$sql = mysqli_query($db, 'SELECT * FROM `brands_tarif_cats` where `brand_id` = '.$_GET['id'].';');
if (mysqli_num_rows($sql) != false) {
    while ($row = mysqli_fetch_array($sql)) {


        $sql2 = mysqli_query($db, 'SELECT * FROM `brands_tarif_plans` where `brand_id` = '.$_GET['id'].' ;');
        if (mysqli_num_rows($sql2) != false) {
            while ($row2 = mysqli_fetch_array($sql2)) {

          $current = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `brands_tarif_values` WHERE `cat_id` = '.$row['cat_id'].' and brand_id = '.$_GET['id'].' and plan_id = '.$row2['id'].' LIMIT 1;'));
          if ($current['COUNT(*)'] == 0) {
          mysqli_query($db, 'INSERT INTO `brands_tarif_values` (
            `brand_id`,
            `cat_id`,
            `plan_id`
            ) VALUES (
            \''.mysqli_real_escape_string($db, $_GET['id']).'\',
            \''.$row['cat_id'].'\',
            \''.$row2['id'].'\'
            );') or mysqli_error($db);
          }

            }
        }


}
}


$sql = mysqli_query($db, 'SELECT * FROM `brands_tarif_cats` where brand_id = '.$_GET['id'].' ;');
if (mysqli_num_rows($sql) != false) {
      while ($row = mysqli_fetch_array($sql)) {
          $cat = mysqli_fetch_array(mysqli_query($db, 'SELECT name FROM `cats` WHERE `id` = '.$row['cat_id'].' LIMIT 1;'));

      $content_list .= '<tr>
          <td>'.$row['id'].'</td>
          <td style="font-size:14px">'.$cat['name'].'</td>
          '.gen_inputs($row['cat_id']).'
          </tr>';
      $content_list .= '<tr class="geeneyed">
          <td>'.$row['id'].'</td>
          <td style="font-size:14px">'.$cat['name'].' ПЛАТНЫЙ</td>
          '.gen_inputs($row['cat_id'], true).'
          </tr>';


     }
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

function gen_inputs($cat_id, $green = false) {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `brands_tarif_plans` where brand_id = '.$_GET['id'].' ;');
     $data = ($green == true) ? 'data-paid="1"' : 'data-paid="0"';
      while ($row = mysqli_fetch_array($sql)) {
        $current = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `brands_tarif_values` WHERE `cat_id` = '.$cat_id.' and brand_id = '.$_GET['id'].' and plan_id = '.$row['id'].' LIMIT 1;'));
        $price = ($green == true) ? $current['value_paid'] : $current['value'];
       $content .= '<td><input class="editable" style="width:100px;" type="text"  value="'.$price.'" '.$data.' data-cat-id="'.$cat_id.'" data-brand-id="'.$_GET['id'].'" data-plan-id="'.$row['id'].'"></td>';
      }

    return $content;
}

function gen_titles($cat_id) {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `brands_tarif_plans` where brand_id = '.$_GET['id'].' ;');
        $id = 2;
      while ($row = mysqli_fetch_array($sql)) {
       $content .= ' <th align="left"><a href="#" class="remove_plan" data-column-id="'.$id.'" data-id="'.$row['id'].'" style="color:red;    float: right;">X</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<textarea class="plan_name_edit" style="height: auto; padding: 0;border: 0;width: 100%; font-size:16px;overflow:hidden;    min-height: 60px;"  data-id="'.$row['id'].'"/>'.$row['plan_name'].'</textarea></th> ';
       $id++;
      }

    return $content;
}

function cats_list2($brand_id) {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `cats` ;');
      while ($row = mysqli_fetch_array($sql)) {

       $selected = (mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `brands_tarif_cats` WHERE `cat_id` = \''.mysqli_real_escape_string($db, $row['id']).'\' and `brand_id` = '.$_GET['id'].' LIMIT 1;'))['COUNT(*)'] > 0) ? 'selected' : '';

       $selected = (mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `cats_to_brand` WHERE `cat_id` = \''.mysqli_real_escape_string($db, $row['id']).'\' and `brand_id` = '.$_GET['id'].' LIMIT 1;'))['COUNT(*)'] > 0) ? 'selected' : '';

       $content .= '<option value="'.$row['id'].'" '.$selected.'>'.$row['name'].'</option>';
      }

    return $content;
}

if ($_POST) {

if ($_POST['cats']) {
foreach ($_POST['cats'] as $cat) {

          $current = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `brands_tarif_cats` WHERE `cat_id` = '.$cat.' and brand_id = '.$_GET['id'].' LIMIT 1;'));

          if ($current['COUNT(*)'] == 0) {
          mysqli_query($db, 'INSERT INTO `brands_tarif_cats` (
            `brand_id`,
            `cat_id`
            ) VALUES (
            \''.mysqli_real_escape_string($db, $_GET['id']).'\',
            \''.$cat.'\'
            );') or mysqli_error($db);
          }

}
}

if ($_POST['tarif']) {
          mysqli_query($db, 'INSERT INTO `brands_tarif_plans` (
            `brand_id`,
            `plan_name`
            ) VALUES (
            \''.mysqli_real_escape_string($db, $_GET['id']).'\',
            \''.$_POST['tarif'].'\'
            );') or mysqli_error($db);
}

}

?>
<!doctype html>
<html>
<head>
<meta charset=utf-8>
<title>Панель управления</title>
<link href="<?=$config['url'];?>css/fonts.css" rel="stylesheet" />
<link rel="stylesheet" href="/_new-codebase/front/vendor/select2/4.0.4/select2.min.css" />
<link rel="stylesheet" href="/_new-codebase/front/vendor/font-awesome.css" />
<link href="<?=$config['url'];?>css/style.css" rel="stylesheet" />
<script src="/_new-codebase/front/vendor/jquery/jquery-1.7.2.min.js"  ></script>
<script src="<?=$config['url'];?>js/jquery-ui.min.js"></script>
<script src="<?=$config['url'];?>js/jquery.placeholder.min.js"></script>
<script src="<?=$config['url'];?>js/jquery.formstyler.min.js"></script>
<script src="<?=$config['url'];?>js/main.js"></script>

<script src="<?=$config['url'];?>notifier/js/index.js"></script>
<link rel="stylesheet" type="text/css" href="<?=$config['url'];?>notifier/css/style.css">
  <link rel="stylesheet" type="text/css" href="<?=$config['url'];?>notifier/css/style.css">
<link rel="stylesheet" href="/_new-codebase/front/vendor/animate.min.css" />
<link rel="stylesheet" href="/js/fSelect.css" />
<script src='/_new-codebase/front/vendor/mustache/mustache.min.js'></script>
<script src="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.concat.min.js"></script>
<link rel="stylesheet" href="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.min.css" />
<script src="/_new-codebase/front/vendor/select2/4.0.4/select2.full.min.js"></script>
<script src="/_new-codebase/front/vendor/select2/select2.multi-checkboxes.js"></script>

<script  src="/_new-codebase/front/vendor/datatables/1.10.12/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?=$config['url'];?>css/datatables.css">
<script src="<?=$config['url'];?>js/fSelect.js"></script>
<style>
.geeneyed {
background-color: rgb(178, 255, 102) !important;
}

table.dataTable.display tbody tr.even>.sorting_1, table.dataTable.order-column.stripe tbody tr.even>.sorting_1 {
  background-color: rgb(178, 255, 102) !important;
}

table td {
    vertical-align: middle;
}


</style>
<script >
// Таблица
$(document).ready(function() {
  var table = $('#table_content').dataTable({
      "pageLength": 30,
      "dom": '<"top"flp<"clear">>rt<"bottom"ip<"clear">>',
      stateSave: false,
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

$('.select2-multiple2').fSelect({
    placeholder: 'Выберите',
    numDisplayed: 1,
    overflowText: '{n} выбрано',
    noResultsText: 'Не найдено',
    searchText: 'Поиск',
    showSearch: true
});


    $(document).on('change', 'input.editable', function() {
        var cat_id = $(this).data('cat-id');
        var brand_id = $(this).data('brand-id');
        var plan_id = $(this).data('plan-id');
         var paid = $(this).data('paid');
        var value = $(this).val();
              if (value) {

                  $.get( "/ajax.php?type=update_brand_price&cat_id="+cat_id+"&brand_id="+brand_id+"&plan_id="+plan_id+"&value="+value+"&paid="+paid, function( data ) {


                  });

              }


        return false;
    });

    $(document).on('change', 'textarea.plan_name_edit', function() {
        var id = $(this).data('id');
        var value = $(this).val();
              if (value) {

                  $.get( "/ajax.php?type=update_brand_plan_name&id="+id+"&value="+value, function( data ) {


                  });

              }


        return false;
    });

    $(document).on('click', 'a.remove_plan', function() {
         var id = $(this).data('id');
         table.fnSetColumnVis( $(this).attr('data-column-id'), false );

     if (confirm('Подтверждаете удаление?')) {

              if (id) {

                  $.get( "/ajax.php?type=remove_brand_plan&id="+id, function( data ) {


                  });

              }
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
           <h2>Тарифы бренда <?=$brand_info['name'];?></h2>

  <div class="adm-catalog">


       <br><br>

  <form id="send" method="POST">
   <div class="adm-form" style="padding-top:0;">

             <h2>Создание сетки тарифов:</h2>

             <div class="item">
              <div class="level" style="display: block;text-align: center;width: 50%;    margin: 0 auto;">Категории:</div>
              <div class="value" style="display:block;">
                <input type="hidden" name="send_to_all" value="0">
              <select name="cats[]" class="nomenu select2-multiple2 sc_all2" multiple>

               <!--<option value="all">Всем</option>-->
               <?=cats_list2($brand_info['id']);?>
              </select>

              </div>
            </div>

             <div class="item">
              <div class="level" style="display: block;text-align: center;width: 50%;    margin: 0 auto;">Добавить тариф:</div>
              <div class="value" style="display:block;">
                <input type="text" name="tarif" value=""  />
                <button type="submit" style="    height: 53px;     vertical-align: top;      padding: 0px 10px;" >Сохранить</button>
              </div>
            </div>

          </div>
  </form>
  <br>
  <hr>
   <br>
  <table id="table_content" class="display" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th align="left">№</th>
                <th align="left">Категория</th>
                <?=gen_titles();?>
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