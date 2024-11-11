<?php

use models\repair\Util;

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
  $content = '';
  $providers_select = '';
$sql = mysqli_query($db, 'SELECT * FROM `serials` WHERE `model_id` = '.$id . ' ORDER BY `provider_id`, `order`');
      if (mysqli_num_rows($sql) != false) {
        $i = 0;
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
       <div class="i" data-serial-container>
       <input type="hidden" name="serial_id['.$i.']" value="'.$row['id'].'">
       <input type="hidden" data-del-serial-flag name="del_flag['.$i.']" value="0">
       <input style="width: 200px;" type="text" name="serials_first['.$i.']" value="'.$row['serial'].'" placeholder="Начальный номер"/>
       <input style="width: 200px;" type="text" name="serials_lot['.$i.']" value="'.$row['lot'].'" placeholder="Размер лота" />
       <select name="serial_provider['.$i.']">
          <option value="">Выберите поставщика</option>'.$providers_select.'</select>
        <input style="width: 100px;" type="text" name="order['.$i.']" value="'.$row['order'].'" placeholder="Заказ"/>
        <select name="plant_id['.$i.']">
          <option value="0">Выберите сборщика</option>';
         foreach($plants as $id => $plant){
           $sel = ($id != $row['plant_id']) ? '' : 'selected';
          $content .= '<option value="'.$id.'" '.$sel.'>'.$plant.'</option>';
         }
         $content .= '</select> 
          <div class="del" data-action="del-serial"></div>
          </div>';
      $i++;
        }
      }
    return $content;
}

$count = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `models` WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' ;'));
if ($count['COUNT(*)'] > 0) {
$content = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `models` WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1;'));
$content['serials'] = gen_serials($content['id']);
$exp_provider = explode('|', $content['provider']);
$content['provider_first'] = $exp_provider['0'];

if (count($exp_provider) > 1) {
unset($exp_provider['0']);

foreach ($exp_provider as $provider) {
$content['provider_other'] .= '<div class="value2"><select name="provider[]" ><option value="">Выберите вариант</option>'.trim(providers($provider)).'</select><a href="" class="remove_field del"></a></div>';
}

}

} else {
header('Location: /models/');
}
# Сохраняем:
if ($_POST['send'] == 1) {



mysqli_query($db, 'UPDATE `models` SET
`model_id` = \''.mysqli_real_escape_string($db, $_POST['model_id']).'\',
`brand` = \''.mysqli_real_escape_string($db, $_POST['brand']).'\',
`name` = \''.mysqli_real_escape_string($db, $_POST['name']).'\',
`price_usd` = \''.mysqli_real_escape_string($db, $_POST['price_usd']).'\',   
`cat` = \''.mysqli_real_escape_string($db, $_POST['cat']).'\',
`service` = \''.mysqli_real_escape_string($db, $_POST['service']).'\',
`status` = \''.mysqli_real_escape_string($db, $_POST['status']).'\',
`warranty` = \''.mysqli_real_escape_string($db, $_POST['warranty']).'\',
`provider` = \''.mysqli_real_escape_string($db, implode('|', $_POST['provider'])).'\'
WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1
;') or mysqli_error($db);

admin_log_add('Обновлена модель #'.$_GET['id']);

  // mysqli_query($db, 'DELETE FROM `serials` WHERE `model_id` = \''.$_GET['id'].'\' /*and `first_serial` = \''.mysqli_real_escape_string($db, $_POST['serials_first'][$count_arrays]).'\'*/;') or mysqli_error($db);

  /* Обработка серийных номеров */
  for ($i = 0; $i < 1000; $i++) {
    if (empty($_POST['serials_first'][$i])) {
      break;
    }
    if (!empty($_POST['del_flag'][$i])) {
      \models\Serials::delSerial($_POST['serial_id'][$i]);
      continue;
    }
    if (empty($_POST['serial_id'][$i])) {
      \models\Serials::addSerial(
        $_POST['serials_first'][$i],
        $_GET['id'],
        $_POST['serials_lot'][$i],
        $_POST['order'][$i],
        $_POST['serial_provider'][$i],
        $_POST['plant_id'][$i]
      );
      Util::updateSerialInvalidFlag($_POST['serials_first'][$i], $_GET['id']);
      continue;
    }
    if (!empty($_POST['serial_id'][$i])) {
      \models\Serials::updateSerial(
        $_POST['serial_id'][$i],
        $_POST['serials_first'][$i],
        $_POST['serials_lot'][$i],
        $_POST['order'][$i],
        $_POST['serial_provider'][$i],
        $_POST['plant_id'][$i]
      );
      Util::updateSerialInvalidFlag($_POST['serials_first'][$i], $_GET['id']);
      continue;
    }
  }


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

header('Location: /models/');
}


function cat($cat_id = '', $brand = '') {
  global $db;

$brand_id = mysqli_fetch_array(mysqli_query($db, 'SELECT id FROM `brands` WHERE `name` = \''.mysqli_real_escape_string($db, $brand).'\'  LIMIT 1;'))['id'];


$sql2 = mysqli_query($db, 'SELECT * FROM `cats_to_brand` where brand_id = '.$brand_id.' ;');

while ($row2 = mysqli_fetch_array($sql2)) {
$cats[] = $row2['cat_id'];

}

//print_r($cats);

$sql = mysqli_query($db, 'SELECT * FROM `cats` where `id` IN ('.implode(',', $cats).') order by `name` asc;');
$content = '';
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
  $content = '';
$sql = mysqli_query($db, 'SELECT * FROM `providers` order by `name` asc;');
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
  $content = '';
$sql = mysqli_query($db, 'SELECT * FROM `brands` WHERE `is_deleted` = 0 order by `name` asc;');
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
<title>Редактировать модель - Панель управления</title>
<link href="/css/fonts.css" rel="stylesheet" />
<link href="/css/style.css" rel="stylesheet" />
<script src="/_new-codebase/front/vendor/jquery/jquery-1.7.2.min.js"  ></script>
<script src="/js/jquery-ui.min.js"></script>
<script src="/js/jquery.placeholder.min.js"></script>
<script src="/js/jquery.formstyler.min.js"></script>
<script src="/js/main.js"></script>

<script src="/notifier/js/index.js"></script>
<link rel="stylesheet"  href="/notifier/css/style.css">
<link rel="stylesheet" href="/_new-codebase/front/vendor/animate.min.css" />
<script src='/_new-codebase/front/vendor/mustache/mustache.min.js'></script>
<script src="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.concat.min.js"></script>
<link rel="stylesheet" href="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.min.css" />

<link rel="stylesheet" href="/_new-codebase/front/vendor/select2/4.0.4/select2.min.css" />
<script src="/_new-codebase/front/vendor/select2/4.0.4/select2.full.min.js"></script>
<script src="/_new-codebase/front/vendor/select2/4.0.4/ru.js"></script>

<script  src="/_new-codebase/front/vendor/datatables/1.10.12/jquery.dataTables.min.js"></script>
<link rel="stylesheet"  href="/css/datatables.css">

<script >
// Таблица
$(document).ready(function() {
  $('.select2').select2();
    $(document).on('click', '[data-action]', function(){
      let $this = $(this);
      let $container = $this.closest('[data-serial-container]');
      switch($this.data('action')){
        case 'del-serial':
        if($container.hasClass('serial_deleted')){
          $container.removeClass('serial_deleted').find('[data-del-serial-flag]').val(0);
        }else{
          $container.addClass('serial_deleted').find('[data-del-serial-flag]').val(1);
        }
      }
      return false;
    });

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

            $(wrapper).append('<div class="i"><input style="width: 200px;" type="text" data-input-filter="serial" name="serials_first[]" placeholder="Начальный номер"/><input style="width: 200px;" type="text" name="serials_lot[]" placeholder="Размер лота"/><select name="serial_provider[]"><option value="">Выберите поставщика</option>'+select_new+'</select><input style="width: 100px;" type="text" name="order[]" placeholder="Заказ"/><select name="plant_id[]"><?= $plantsSelect; ?></select> <a href="#" class="remove_field del"></a></div>'); //add input box
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

    .serial_deleted{
      opacity: .3;
    }

    .del{
      cursor: pointer;
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
              <div class="level">Код техники:</div>
              <div class="value">
                <input type="text" name="model_id" value="<?=$content['model_id'];?>"  />
              </div>
            </div>

                    <div class="item">
              <div class="level">Бренд:</div>
              <div class="value">
                              <select name="brand" class="nomenu select2">
               <option>Выберите вариант</option>
               <?=brands($content['brand']);?>
              </select>
              </div>
            </div>

                  <div class="item">
              <div class="level">Товар:</div>
              <div class="value">
                <input type="text" name="name" value="<?=$content['name'];?>"  />
              </div>
            </div>

                    <div class="item">
              <div class="level">Категория:</div>
              <div class="value">
                     <select name="cat" class="nomenu select2">
               <option>Выберите вариант</option>
               <?=cat($content['cat'], $content['brand']);?>
              </select>
              </div>
            </div>

                   <div class="item">
              <div class="level">Цена (usd):</div>
              <div class="value">
                <input type="text" name="price_usd" value="<?=$content['price_usd'];?>"  />
              </div>
            </div>

                   <div class="item">
              <div class="level">Срок гарантии:</div>
              <div class="value">
                <input type="text" name="warranty" value="<?=$content['warranty'];?>"  />
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


         <div class="item">
              <div class="level">Поставщик:</div>
              <div class="value">

              <select name="provider[]" class="nomenu select2"><option value="" >Выберите вариант</option><?=providers($content['provider_first']);?></select>

              <div class="field input_fields_wrap2" style="margin-top: 20px;">
              <?=$content['provider_other'];?>
              </div>

              <div class="add adm-add">
                <a href="#" class="add_field_button2"><u>Добавить еще</u></a>
              </div>



              </div>
            </div>

            <div class="item item-feature" style="width:100%;">
            <div class="level" style="margin: 0 auto;display: block;">Серийные номера</div>
            <div class="value input_fields_wrap" style="display: block;">
              <?=$content['serials'];?>
            </div>


              <div class="adm-add" style="padding-left: 0px">
                <a href="#" class="add_field_button"><u>Добавить еще</u></a>
              </div>
            </div>
                 <?php if (!\models\User::hasRole('master')) { ?>
                <div class="adm-finish">
            <div class="save">
              <input type="hidden" name="send" value="1" />
              <button type="submit" >Сохранить</button>
            </div>
            </div>
            <?php } ?>
        </div>

      </form>




        </div>
  </div>

  <!-- New codebase -->
  <script src='/_new-codebase/front/components/input-filter.js'></script>
</body>
</html>