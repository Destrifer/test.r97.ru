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


# Сохраняем:
if ($_POST['send'] == 1) {



mysqli_query($db, 'INSERT INTO `models` (
`model_id`,
`brand`,
`name`,
`cat`,
`price_usd`,
`service`,
`status`,
`provider`
) VALUES (
\''.mysqli_real_escape_string($db, $_POST['model_id']).'\',
\''.mysqli_real_escape_string($db, $_POST['brand']).'\',
\''.mysqli_real_escape_string($db, $_POST['name']).'\',
\''.mysqli_real_escape_string($db, $_POST['cat']).'\',
\''.mysqli_real_escape_string($db, $_POST['price_usd']).'\',
\''.mysqli_real_escape_string($db, $_POST['service']).'\',
\''.mysqli_real_escape_string($db, $_POST['status']).'\',
\''.mysqli_real_escape_string($db, implode('|', $_POST['provider'])).'\'
);') or mysqli_error($db);
//print_r($_POST['provider']);
$id = mysqli_insert_id($db);

admin_log_add('Добавлена новая модель '.$_POST['name']);

/*echo 'INSERT INTO `models` (
`model_id`,
`brand`,
`name`,
`cat`,
`service`,
`status`,
`provider`
) VALUES (
\''.mysqli_real_escape_string($db, $_POST['model_id']).'\',
\''.mysqli_real_escape_string($db, $_POST['brand']).'\',
\''.mysqli_real_escape_string($db, $_POST['name']).'\',
\''.mysqli_real_escape_string($db, $_POST['cat']).'\',
\''.mysqli_real_escape_string($db, $_POST['service']).'\',
\''.mysqli_real_escape_string($db, $_POST['status']).'\',
\''.mysqli_real_escape_string($db, implode('|', $_POST['provider'])).'\'
);'; */

$count = count($_POST['serials_first']);
$count_arrays = 0;

while ($count_arrays < $count) {

\models\Serials::addSerial($_POST['serials_first'][$count_arrays], $id, 
$_POST['serials_lot'][$count_arrays], $_POST['order'][$count_arrays], 
$_POST['serial_provider'][$count_arrays], $_POST['plant_id'][$count_arrays]);  
Util::updateSerialInvalidFlag($_POST['serials_first'][$count_arrays], $id);
$count_arrays++;
}

/*foreach ($_POST['serials'] as $serial) {

$serial_array = explode('-', $serial);
mysqli_query($db, 'INSERT INTO `serials` (
`model_id`,
`serial_start`,
`serial_end`
) VALUES (
\''.mysqli_real_escape_string($db, $id).'\',
\''.mysqli_real_escape_string($db, $serial_array['0']).'\',
\''.mysqli_real_escape_string($db, $serial_array['1']).'\'
);') or mysqli_error($db);


}   */

header('Location: /models/');
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
$sql = mysqli_query($db, 'SELECT * FROM `providers` ORDER BY `name`;');
      while ($row = mysqli_fetch_array($sql)) {
      /*if ($cat_id == $row['cat']) {
      $content .= '<option selected value="'.$row['id'].'">'.$row['name'].'</option>';
      } else {  */
       $content .= '<option value="'.$row['id'].'">'.$row['name'].'</option>';
      /*}*/
      }
    return $content;
}

function brands($id = '') {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `brands` WHERE `is_deleted` = 0 order by name;');
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
<link rel="stylesheet" href="/_new-codebase/front/vendor/select2/4.0.4/select2.min.css" />
<script src="/_new-codebase/front/vendor/select2/4.0.4/select2.full.min.js"></script>
<script src="/_new-codebase/front/vendor/select2/4.0.4/ru.js"></script>
<script >
// Таблица
$(document).ready(function() {

$('.select2').select2();
    $('#table_content').dataTable({
      stateSave:false,
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

    $(document).on('change', 'select[name="brand"]', function() {
        var value = $(this).val();
        var this_block = $(this).parent();
              if (value) {
                  $.get( "/ajax.php?type=get_cat_brand&id="+value, function( data ) {
                  $('select[name=cat]').html(data.html).trigger('change.select2');
                  $('select[name=service]').val(data.service).selectmenu( "refresh" ); 
                   $('input[name=name]').val(value);
                  //this_block.find($('select[name="serial_add[]"]')).html('<option>Выберите вариант</option>'+data.html2).trigger('change.select2');
                  });
              }
              return false;
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
    .select2-results__option {
    font-size: 16px;
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
           <h2>Добавление модели</h2>

  <form id="send" method="POST">
   <div class="adm-form" style="padding-top:0;">

                    <div class="item">
              <div class="level">Бренд:</div>
              <div class="value">
                              <select name="brand" class="nomenu select2">
               <option>Выберите вариант</option>
               <?=brands($content['service']);?>
              </select>
              </div>
            </div>

                    <div class="item">
              <div class="level">Категория:</div>
              <div class="value">
                     <select name="cat" class="nomenu select2"> <option>Выберите вариант</option><?=cat();?></select>
              </div>
            </div>

                  <div class="item">
              <div class="level">Код техники:</div>
              <div class="value">
                <input type="text" name="model_id" value="<?=$content['model_id'];?>"  />
              </div>
            </div>




                  <div class="item">
              <div class="level">Товар:</div>
              <div class="value">
                <input type="text" name="name" value="<?=$content['name'];?>"  />
              </div>
            </div>


                   <div class="item">
              <div class="level">Цена (usd):</div>
              <div class="value">
                <input type="text" name="price_usd" value="<?=$content['price_usd'];?>"  />
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

              <select name="provider[]" class="nomenu select2" ><option value="" selected>Выберите вариант</option><?=providers();?></select>

              <div class="field input_fields_wrap2" style="margin-top: 20px;">

              </div>

              <div class="add adm-add">
                <a href="#" class="add_field_button2"><u>Добавить еще</u></a>
              </div>



              </div>
            </div>



            <div class="item item-feature" style="width:100%;">
            <div class="level" style="margin: 0 auto;display: block;">Серийные номера</div>
            <div class="value input_fields_wrap" style="display: block;">
              <?=$content['specials'];?>
            </div>


              <div class="adm-add" style="padding-left: 0px">
                <a href="#" class="add_field_button"><u>Добавить еще</u></a>
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

  <!-- New codebase -->
  <script src='/_new-codebase/front/components/input-filter.js'></script>
</body>
</html>