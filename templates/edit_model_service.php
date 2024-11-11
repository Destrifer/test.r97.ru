<?php

$sql = mysqli_query($db, 'SELECT * FROM `plants` ORDER BY `name`');
$plants = [];
while($row = mysqli_fetch_array($sql)){
  $plants[$row['id']] = $row['name'];
}
$plantsSelect = '<option value="0">Выберите сборщика</option>';
foreach($plants as $id => $plant){
  $plantsSelect .= '<option value="'.$id.'">'.$plant.'</option>';
}

function privider_name($id) {
  global $db;

$sql = mysqli_query($db, 'SELECT * FROM `providers` where `id` = '.$id);
      while ($row = mysqli_fetch_array($sql)) {
      $content = $row['name'];
      }
    return $content;
}

function gen_serials($id) {
  global $db, $plants;
$sql = mysqli_query($db, 'SELECT * FROM `serials` WHERE `model_id` = '.$id);
      if (mysqli_num_rows($sql) != false) {
      while ($row = mysqli_fetch_array($sql)) {

       $model = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `models` WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1;'));
       $exp_provider = explode('|', $model['provider']);
       foreach ($exp_provider as $provider) {
         if ($row['provider_id'] == $provider) {
            $providers_select .= '<option selected value="'.$provider.'">'.privider_name($provider).'</option>';
         } else {
            $providers_select .= '<option value="'.$provider.'">'.privider_name($provider).'</option>';
         }


       }

       $content .= '
       <div class="i"><input style="width: 200px;" type="text" name="serials_first[]" value="'.$row['first_serial'].'" placeholder="Начальный номер"/><input style="width: 200px;" type="text" name="serials_lot[]" value="'.$row['lot'].'" placeholder="Размер лота" /><select name="serial_provider[]"><option value="">Выберите поставщика</option>'.$providers_select.'</select><input style="width: 100px;" type="text" name="order[]" value="'.$row['order'].'" placeholder="Заказ"/><select name="plant_id[]">
       <option value="0">Выберите сборщика</option>';
      foreach($plants as $id => $plant){
        $sel = ($id != $row['plant_id']) ? '' : 'selected';
       $content .= '<option value="'.$id.'" '.$sel.'>'.$plant.'</option>';
      }
      $content .= '</select> <a href="#" class="remove_field del"></a></div>
       ';
      }
      }
    return $content;
}

$count = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `models_users` WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' ;'));
if ($count['COUNT(*)'] > 0) {
$content = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `models_users` WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1;'));
$content['serials'] = gen_serials($content['id']);
$exp_provider = explode('|', $content['provider']);
$content['provider_first'] = $exp_provider['0'];

if (count($exp_provider) > 1) {
unset($exp_provider['0']);

foreach ($exp_provider as $provider) {
$content['provider_other'] .= '<div class="value2"><select name="provider[]" ><option value="">Выберите вариант</option>'.trim(providers($provider)).'</select><a href="#" class="remove_field del"></a></div>';
}

}

} else {
header('Location: '.$_SERVER['HTTP_REFERER']);
}
# Сохраняем:
if ($_POST['send'] == 1) {



mysqli_query($db, 'UPDATE `models_users` SET
`name` = \''.mysqli_real_escape_string($db, $_POST['name']).'\',
`cat_id` = \''.mysqli_real_escape_string($db, $_POST['cat_id']).'\',
`service` = \''.mysqli_real_escape_string($db, $_POST['service']).'\'
WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1
;') or mysqli_error($db);

admin_log_add('Обновлена модель #'.$_GET['id']);

/*foreach ($_POST['serials'] as $serial) {
$serial_array = explode('-', $serial);
mysqli_query($db, 'INSERT INTO `serials` (
`model_id`,
`serial_start`,
`serial_end`
) VALUES (
\''.mysqli_real_escape_string($db, $_GET['id']).'\',
\''.mysqli_real_escape_string($db, $serial_array['0']).'\',
\''.mysqli_real_escape_string($db, $serial_array['1']).'\'
);') or mysqli_error($db);

}*/

header('Location: https://crm.r97.ru/models-service/32/');
}


function cat($cat_id = '') {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `cats` where `name` != \'\' order by `name` asc;');

      while ($row = mysqli_fetch_array($sql)) {
      if ($cat_id == $row['id']) {
      $content .= '<option selected value="'.$row['id'].'">'.$row['name'].'</option>';
      } else {
       $content .= '<option value="'.$row['id'].'">'.$row['name'].'</option>';
      }
      }
    return $content;
}

function providers($id = '') {
  global $db;

$sql = mysqli_query($db, 'SELECT * FROM `providers`;');
      while ($row = mysqli_fetch_array($sql)) {
      if ($id == $row['id']) {
      $content .= '<option selected value="'.$row['id'].'">'.$row['name'].'</option>';
      } else {
       $content .= '<option value="'.$row['id'].'">'.$row['name'].'</option>';
      }
      }
    return $content;
}

function brands($id = '') {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `brands` WHERE `is_deleted` = 0;');
      while ($row = mysqli_fetch_array($sql)) {
      if ($id == $row['name']) {
      $content .= '<option selected value="'.$row['name'].'">'.$row['name'].'</option>';
      } else {
       $content .= '<option value="'.$row['name'].'">'.$row['name'].'</option>';
      }
      }
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

<script >
// Таблица
$(document).ready(function() {
    $('#table_content').dataTable({
      "pageLength": 30,
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

  $('ul.tabs li').click(function(){
    var tab_id = $(this).attr('data-tab');

    $('ul.tabs li').removeClass('current');
    $('.tab-content').removeClass('current');

    $(this).addClass('current');
    $("#"+tab_id).addClass('current');
  })

    var max_fields      = 50; //maximum input boxes allowed
    var wrapper         = $(".input_fields_wrap"); //Fields wrapper
    var add_button      = $(".add_field_button"); //Add button ID

    var x = 1; //initlal text box count
    var select_new = '';
    $(add_button).click(function(e){ //on add input button click
        e.preventDefault();
        if(x < max_fields){ //max input box allowed
            x++; //text box increment

            $( "select[name='provider[]']" ).each(function() {

              select_new += '<option value="'+$( this ).val()+'">'+$( this ).find('option:selected').text()+'</option>';
            });

            $(wrapper).append('<div class="i"><input style="width: 200px;" type="text" name="serials_first[]" placeholder="Начальный номер"/><input style="width: 200px;" type="text" name="serials_lot[]" placeholder="Размер лота"/><select name="serial_provider[]"><option value="">Выберите поставщика</option>'+select_new+'</select><input style="width: 100px;" type="text" name="order[]" placeholder="Заказ"/><select name="plant_id[]"><?= $plantsSelect; ?></select> <a href="#" class="remove_field del"></a></div>'); //add input box
            $('select:not(.nomenu)').selectmenu({
            open: function(){
              $(this).selectmenu('menuWidget').css('width', $(this).selectmenu('widget').outerWidth());
            }}).addClass("selected_menu");

            select_new = '';

        }
    });

    $(document).on("change","input[name='serials_first[]']", function(){ //user click on remove text
        var first = $(this).val();
        //$(this).val();
    });

    $(document).on("change","input[name='serials_lot[]']", function(){
        var lot = $(this).val();
        var first = $(this).parent().find("input[name='serials_first[]']").val();

    });

    $(wrapper).on("click",".remove_field", function(e){ //user click on remove text
        e.preventDefault(); $(this).parent('div').remove(); x--;
    })

    var max_fields2      = 50; //maximum input boxes allowed
    var wrapper2         = $(".input_fields_wrap2"); //Fields wrapper
    var add_button2      = $(".add_field_button2"); //Add button ID

    var x2 = 1; //initlal text box count
    $(add_button2).click(function(e){ //on add input button click
        e.preventDefault();
        if(x2 < max_fields2){ //max input box allowed
            x2++; //text box increment
            $(wrapper2).append('<div class="value2"><select name="provider[]" ><option value="">Выберите вариант</option><?=trim(providers());?></select><a href="#" class="remove_field del"></a></div>'); //add input box
            $('select:not(.nomenu)').selectmenu({
            open: function(){
              $(this).selectmenu('menuWidget').css('width', $(this).selectmenu('widget').outerWidth());
            }}).addClass("selected_menu");
        }
    });

    $(wrapper2).on("click",".remove_field", function(e){ //user click on remove text
        e.preventDefault(); $(this).parent('div').remove(); x--;
    })

} );

</script>
<style>
ul.tabs{
      margin: 0px;
      padding: 0px;
      list-style: none;
    }
    ul.tabs li{
      background: none;
      color: #222;
      display: inline-block;
      padding: 10px 15px;
      cursor: pointer;
    }

    ul.tabs li.current{
      background: #ededed;
      color: #222;
    }

    .tab-content{
      display: none;
      background: #ededed;
      padding: 15px;
    }

    .tab-content.current{
      display: inherit;
    }
    .adm-form .item-feature .i:after {
    display:none;
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
           <h2>Редактирование модели</h2>

  <form id="send" method="POST">
   <div class="adm-form" style="padding-top:0;">

                  <div class="item">
              <div class="level">Товар:</div>
              <div class="value">
                <input type="text" name="name" value="<?=$content['name'];?>"  />
              </div>
            </div>

                    <div class="item">
              <div class="level">Категория:</div>
              <div class="value">
                     <select name="cat_id">
               <option>Выберите вариант</option>
               <?=cat($content['cat_id']);?>
              </select>
              </div>
            </div>

                    <div class="item" style="vertical-align: top;">
              <div class="level">Обслуживается:</div>
              <div class="value">
                              <select name="service">
               <option>Выберите вариант</option>
               <option value="Да" <?php if ($content['service'] == 'Да') { echo 'selected';}?>>Да</option>
               <option value="Нет" <?php if ($content['service'] == 'Нет') { echo 'selected';}?>>Нет</option>
              </select>
              </div>
            </div>


                <div class="adm-finish">
            <div class="save">
              <input type="hidden" name="send" value="1" />
              <button type="submit" >Сохранить</button>
            </div>
            </div>
        </div>

      </form>




        </div>
  </div>
</body>
</html>