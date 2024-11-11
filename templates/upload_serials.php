<?php

use program\core;

if (!empty(core\App::$URLParams['action'])) {
  switch (core\App::$URLParams['action']) {
    case 'import-excel':
      models\models\ImportExcel::run();  
      $_FILES = [];
      break;
    case 'export-excel':
      models\models\ExportExcel::run();
      exit;
  }
}

if (!empty(core\App::$URLParams['ajax'])) {
  switch (core\App::$URLParams['ajax']) {
    case 'get-cats':
      echo json_encode(['html' => getCheckboxOptionsHTML(models\Models::getCats($_POST['brand_id']), $_POST['brand_id'])]);
      exit;
  }
}


if (\models\User::hasRole('admin')) {


# Сохраняем:
if ($_POST['send'] == 1) {



if ($_FILES['filename']['size'] != 0){

require_once 'adm/excel/vendor/autoload.php';

$xls = PHPExcel_IOFactory::load($_FILES["filename"]["tmp_name"]);
$xls->setActiveSheetIndex(0);
$sheet = $xls->getActiveSheet();
$array = $sheet->toArray();
array_shift($array);

foreach($array as $item) {

if ($item['3'] != '') {

$check_serial = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `serials` where `serial` = \''.$item['3'].'\';'))['COUNT(*)'];
if ($check_serial == 0) {
// Производитель
$count_providers = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `providers` where `name` = \''.$item['0'].'\';'))['COUNT(*)'];
if ($count_providers == 0) {
mysqli_query($db, 'INSERT INTO `providers` (
`name`
) VALUES (
\''.mysqli_real_escape_string($db, $item['0']).'\'
);') or mysqli_error($db);
$provider_id = mysqli_insert_id($db);
$result_message['providers_add'] .= 'Добавлен поставщик '.$item['0'].'<br>';
} else {
$providers_id = mysqli_fetch_array(mysqli_query($db, 'SELECT `id` FROM `providers` where `name` = \''.$item['0'].'\';'))['id'];
$provider_id = $providers_id;
}

// Поиск модели
$model = model_by_name($item['2']);
if ($model) {
$model_id = $model['id'];

$providers_ar = explode('|', $model['provider']);
if (!in_array($provider_id, $providers_ar))
$providers_ar[]=$provider_id;
mysqli_query($db, 'UPDATE `models` SET
`provider` = \''.mysqli_real_escape_string($db, implode('|', array_filter($providers_ar))).'\'
WHERE `id` = \''.mysqli_real_escape_string($db, $model_id).'\' LIMIT 1
;') or mysqli_error($db);



/* Серийники */
$lot = preg_replace('/[^0-9.]+/', '', str_replace(array(',000'),array(''), $item['4']));
\models\Serials::addSerial($item['3'], $model_id, $lot, intval($item['1']), $provider_id, 0);
/* /Серийники */

$result_message['add_model'] .= 'Добавлены серийники для модели '.$model['name'].'<br>';

} else {
$result_message['miss_model'] .= 'Отсутствует модель '.$item['2'].'<br>';
}




unset($model_id);
unset($model);
unset($provider_id);
unset($count_providers);
unset($providers_id);
unset($ar);
} else {
$result_message['serial_double'] .= 'Серийник '.$item['3'].' уже привязан к модели '.$item['2'].'<br>';
}

} else {
$result_message['no_serial'] .= 'Модель '.$item['2'].' отсутствует или не указан серийный номер в файле<br>';   
}

}


//print_r($result_message);

}


//header('Location: '.$_SERVER['HTTP_REFERER']);
}

}

function model($id) {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `models` where `id` = '.$id);
      while ($row = mysqli_fetch_array($sql)) {
      $content = $row;
     // print_r($row);
      }
    return $content;
}

function model_by_name($name) {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `models` where `name` = \''.$name.'\' ORDER by id asc LIMIT 1 ;');
      if (mysqli_num_rows($sql) != false) {
      while ($row = mysqli_fetch_array($sql)) {
      $content = $row;
     // print_r($row);
      }
    return $content;
    } else {
    return false;
    }
}

function models($cat_id) {
  global $db;
$content = array();
$sql = mysqli_query($db, 'SELECT * FROM `models`;');
      while ($row = mysqli_fetch_array($sql)) {
      if ($cat_id == $row['id']) {
      $content .= '<option selected value="'.$row['model_id'].'">'.$row['name'].'</option>';
      } else {
       $content .= '<option value="'.$row['model_id'].'">'.$row['name'].'</option>';
      }
      }
    return $content;
}

function get_last_photo($repair_id, $type) {
  global $db;

$sql = mysqli_query($db, 'SELECT * FROM `repairs_photo` where `photo_id` = '.$type.' and `repair_id` = '.$repair_id);
    if (mysqli_num_rows($sql) != false) {
      while ($row = mysqli_fetch_array($sql)) {
       $content = ($row['url_do'] != '') ? $row['url_do'] : $row['url'];
      }
      }
    return $content;
}

function part_info($id) {
  global $db;
$content = array();
$sql = mysqli_query($db, 'SELECT * FROM `parts` where `id` = '.$id);
      while ($row = mysqli_fetch_array($sql)) {
       $content = $row;
      }
    return $content;
}

function groups($cat, $group = '') {
  global $db;

$sql = mysqli_query($db, 'SELECT * FROM `groups` where `cat` = \''.$cat.'\';');
      while ($row = mysqli_fetch_array($sql)) {

      if ($group == $row['name']) {
      $content .= '<option value="'.$row['name'].'" selected>'.$row['name'].'</option>';
      } else {
      $content .= '<option value="'.$row['name'].'">'.$row['name'].'</option>';
      }

      }
    return $content;
}

function parts($cat_id, $model_id, $serial, $group, $id = '') {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `parts` where `cat` = \''.$cat_id.'\' and `group` = \''.$group.'\' and `model_id` = \''.$model_id.'\' and `serial` = \''.$serial.'\';');
//echo 'SELECT * FROM `parts` where `cat` = \''.$cat_id.'\' and `group` = \''.$group.'\' and `model_id` = \''.$model_id.'\' and `serial` = \''.$serial.'\'';
      while ($row = mysqli_fetch_array($sql)) {
      if ($id == $row['id']) {
      $content .= '<option selected value="'.$row['id'].'">'.$row['list'].'</option>';
      } else {
       $content .= '<option value="'.$row['id'].'">'.$row['list'].'</option>';
      }
      }
    return $content;
}

?>
<!doctype html>
<html>
<head>
<meta charset=utf-8>
<title>Загрузка серийников - Панель управления</title>
<link href="/css/fonts.css" rel="stylesheet" />
<link href="/css/style.css" rel="stylesheet" />
<script src="/_new-codebase/front/vendor/jquery/jquery-1.7.2.min.js"  ></script>
<script src="/js/jquery-ui.min.js"></script>
<script src="/js/jquery.placeholder.min.js"></script>
<script src="/js/jquery.formstyler.min.js"></script>

<link rel="stylesheet" href="/_new-codebase/front/vendor/select2/4.0.4/select2.min.css" />
<script src="/_new-codebase/front/vendor/select2/4.0.4/select2.full.min.js"></script>
<script src="/_new-codebase/front/vendor/jquery-validation/jquery.validate.min.js"></script>
<script src="/_new-codebase/front/vendor/jquery-validation/additional-methods.min.js"></script>
<script src="/_new-codebase/front/vendor/tooltipster/tooltipster.bundle.min.js"></script>
<link rel="stylesheet" href="/_new-codebase/front/vendor/tooltipster/tooltipster.bundle.min.css" />
<link rel="stylesheet" href="/_new-codebase/front/vendor/tooltipster/tooltipster-sideTip-shadow.min.css" />


<script src="/js/main.js"></script>

<script src="/notifier/js/index.js"></script>
<link rel="stylesheet"  href="/notifier/css/style.css">
<link rel="stylesheet" href="/_new-codebase/front/vendor/animate.min.css" />
<script src='/_new-codebase/front/vendor/mustache/mustache.min.js'></script>
<script src="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.concat.min.js"></script>
<link rel="stylesheet" href="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.min.css" />

<script  src="/_new-codebase/front/vendor/datatables/1.10.12/jquery.dataTables.min.js"></script>
<link rel="stylesheet"  href="/css/datatables.css">

<script >
// Таблица
$(document).ready(function() {

  let catsSelected = {};
      let catsCntElem = document.getElementById('cats-cnt');

      $('[data-flags-select-trig]').on('click', function(){
        let $list = $('[data-flags-select-items-list]');
        $list.width(this.closest('[data-flags-select]').clientWidth);
        $list.slideToggle();
      });

      $('[data-flags-select-reset]').on('click', function(){
        $('[data-flags-select-flag]').removeAttr('checked');
        $('#cats-select input[type="checkbox"]').trigger('refresh');
        catsCntElem.innerText = 0;
        catsSelected = {};
      });

      $(document).on('change', '[data-flags-select-flag]', function(){
        if ($(this).prop('checked') === true ) {
          catsSelected[this.value] = 1;  
        }else{
          delete catsSelected[this.value]; 
        }
        catsCntElem.innerText = Object.keys(catsSelected).length;
      });

      $('#brands-select').on('change', function(){
        let $cats = $('[data-flags-list="'+this.value+'"]'); 
        $('[data-flags-list]').hide();
        if($cats.length){
          $cats.show();
          return;
        }
        $.ajax({
                type: 'POST',
                url: '?ajax=get-cats',
                dataType: 'json',
                data: 'brand_id=' + this.value,
                success: function(resp) {
                    $('#cats-select').append(resp.html);
                    $('#cats-select input[type="checkbox"]').styler();
                },
                error: function(jqXHR) {
                console.log('Ошибка сервера');
                console.log(jqXHR.responseText);
            }
            });
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

    $(document).on('selectmenuchange', 'select[name=groups_parts]', function() {
        var value = $(this).val();
        var this_parent = $(this).parent().parent().parent();
        var cat = $('input[name="cat_parts_hidden"]').val();
        var model = $('input[name="model_id_parts_hidden"]').val();
        var serial = $('input[name="serial_parts_hidden"]').val();
              if (value) {

                  $.get( "/ajax.php?type=get_parts&group="+value+"&serial=<?=$content['serial'];?>&model_id=<?=$content['model']['id'];?>&cat=<?=$content['model']['cat'];?>", function( data ) {
                  /*var obj = jQuery.parseJSON(data);
                  $('input[name=title]').val(obj.title);  */
                  this_parent.find($('select[name="parts_parts[]"]')).html(data.html).selectmenu( "refresh" );
                  $('input[name="serial_parts_hidden"]').val(value);
                  $('.add_to_list').show();

                  });

              }


        return false;
    });

    var max_fields2      = 50; //maximum input boxes allowed
    var wrapper2         = $(".input_fields_wrap2"); //Fields wrapper
    var add_button2      = $(".add_field_button2"); //Add button ID

    var x2 = 1; //initlal text box count
    $(add_button2).click(function(e){ //on add input button click
        e.preventDefault();
        if(x2 < max_fields2){ //max input box allowed
            x2++; //text box increment
            $(wrapper2).append('<div class="part"><div class="item"><div class="level">Группы запчастей</div><div class="value"><select name="groups_parts" ><option value="" disabled selected>Выберите вариант</option><?=groups($content['model']['cat']);?></select></div></div><div class="item"><div class="level">Запчасть</div><div class="value"><select name="parts_parts[]" ><option value="" disabled selected>Выберите группу запчастей</option></select></div></div></div>');
        }
            $('select[name="parts_parts[]"]').selectmenu();
            $('select[name="groups_parts"]').selectmenu();
    });

    $(wrapper2).on("click",".remove_field", function(e){ //user click on remove text
        e.preventDefault(); $(this).parent('div').remove(); x--;
    });

 $('input[type="text"]').tooltipster({
                              trigger: 'custom',
                              position: 'bottom',
                              animation: 'grow',
                              theme: 'tooltipster-shadow'
                          });
                          $('select').tooltipster({
                              trigger: 'custom',
                              position: 'bottom',
                              animation: 'grow',
                              theme: 'tooltipster-shadow'
                          });
$.validator.setDefaults({
    ignore: ""
});

jQuery.extend(jQuery.validator.messages, {
    required: "Обязательно к заполнению!"
});

$(".repair_form").validate({
        ignore: "",
  rules: {
      client: {
      required: true
      },
      phone: {
      required: true
      },
      name_shop: {
      required: true
      },
      model_id: {
      required: true
      },
      serial: {
      required: true
      },
      status_id: {
      required: true
      },
      bugs: {
      required: true
      },
      end_date: {
      required: true
      },
      start_date: {
      required: true
      },
      master_id: {
      required: true
      }
  },
  highlight: function (element, errorClass) {
            $(element).addClass("input-validation-error");
  },
  errorClass: "field-validation-error",
  errorPlacement: function(error, element) {
      var ele = $(element),
      err = $(error),
      msg = err.text();
      if (msg != null && msg !== "") {
      ele.tooltipster('content', msg);
      ele.tooltipster('open'); //open only if the error message is not blank. By default jquery-validate will return a label with no actual text in it so we have to check the innerHTML.
      }
  },
  unhighlight: function(element, errorClass, validClass) {
      $(element).removeClass(errorClass).addClass(validClass).tooltipster('close');
      $(element).removeClass("input-validation-error");
  }
});

$( ".datepicker" ).datepicker();
$("#ui-datepicker-div").addClass("ll-skin-cangas");
$.datepicker.setDefaults( $.datepicker.regional[ "ru" ] );

$('.select2').select2();

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

  .flag-select{
   position: relative;
  }

  .flags-select__trig{
   background-color: #fff;
   padding: 5px;
   border: solid 1px #eee;
   height: 47px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .flags-select__items-list {
    display: none;
    position: absolute;
    background-color: #fff;
    z-index: 9999;
    max-height: 50vh;
    padding: 16px;
    box-shadow: 0 5px 5px 0 #00000061;
    overflow-x: hidden;
    overflow-y: auto;
}

  .flag-select__item{
    display: block;
    margin: 3px 0;
    padding: 5px;
  }

  .flags-select__reset{
    margin-top: 6px;
    font-size: 15px;
    color: #cf0000;
    border-bottom: dashed 1px #cf0000;
    display: inline-block;
    cursor: pointer;
  }

  .import-log{
    margin: 32px 0;
  }

  .import-log__row{
    padding: 8px;
    border-bottom: solid 1px #eee;
}

  .import-log__title{
    margin: 24px 0 8px 0;
    font-size: 1.2em;
}

.import-log__list li{
  list-style-type: decimal;
  display: block;
  padding: 8px;
    border-bottom: solid 1px #eee;
    list-style-position: inside;
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
           <h2>Загрузка серийников</h2>  <br>

  <form id="send" method="POST" enctype="multipart/form-data">
   <div class="adm-form" style="padding-top:0;">

                <!--  <div class="item">
              <div class="level">Номер квитанции РСЦ:</div>
              <div class="value">
                <input type="text" name="rsc" value="<?=$content['rsc'];?>"  />
              </div>
            </div>

                  <div class="item">
              <div class="level">Заказ-наряд клиента:</div>
              <div class="value">
                <input type="text" name="zakaz_client" value="<?=$content['zakaz_client'];?>"  />
              </div>
            </div>
   <br><br>  -->


  <div  class="tab-content current" style="    padding-bottom: 60px;">

       <div class="adm-video">


            <div class="item" style="width: auto;">
              <div class="level">Загрузите файл:</div>
              <div class="value">
                <input type="file" name="filename" />
              </div>
            </div>

            <?php if ($result_message) { ?>
            <br><br>
            <div>
            <h2>Результаты импорта:</h2>

            <?php
            foreach ($result_message as $message) {
            echo $message;
            }
            ?>

            </div>
            <?php } ?>

  </div><!-- .adm-video -->




  </div>



                <div class="adm-finish">
            <div class="save">
              <input type="hidden" name="send" value="1" />
              <button type="submit" >Загрузить</button>
            </div>
            </div>
        </div>

      </form>



      <div style="margin-bottom: 32px;">
      <br>
           <h2>Выгрузка моделей</h2>  <br>
          <form action="?action=export-excel" method="POST" style="display: flex; margin-top: 20px;">
            <div style="margin-right: 32px;">
              <label class="dashboard__filter-label">Бренд:</label>
              <select class="select2 nomenu" name="brand_id" id="brands-select" style="width: 200px">
                <?= getCheckOptionsHTML(models\Models::getBrands()); ?>
              </select>
            </div>
            <div style="margin-right: 32px;">
              <div data-flags-select class="flags-select" style="width: 600px">
                <div data-flags-select-trig class="flags-select__trig"><span>Выбрано категорий: <span id="cats-cnt">0</span></span></div>
                <div data-flags-select-items-list id="cats-select" class="flags-select__items-list">
                  <?= getCheckboxOptionsHTML(models\Models::getCats(), 0); ?>
                </div>
                <div class="flags-select__reset" data-flags-select-reset>Сбросить</div>
              </div>
            </div>
            <div>
              <button type="submit" style="width: auto;padding-left: 7px;padding-right: 7px;height: 59px;" class="button">Выгрузка в Excel</button>
            </div>
          </form>
        </div>


        <div>
      <br>
           <h2>Загрузка моделей<a name="log"></a></h2>  <br>
          <form action="?action=import-excel#log" method="POST" enctype="multipart/form-data" style="display: flex; margin-top: 20px;">
            <div style="margin-right: 32px;display: flex;align-items: center;">
              <input type="file" name="excel_file">
            </div>
            <div>
              <button type="submit" style="width: auto;padding-left: 7px;padding-right: 7px;height: 59px;" class="button">Загрузить Excel</button>
            </div>
          </form>
          <?php if(models\models\ImportExcel::$log) { 
            $log = models\models\ImportExcel::$log;
          echo '<div class="import-log">';
          if(isset($log['excel']['err'])){
            echo '<div class="import-log__err">'.$log['excel']['err'].'</div>';
          } else {
            echo '<div class="import-log__row">'.$log['excel']['total_rows'][0].'</div>';
            echo '<div class="import-log__row">'.$log['excel']['total_models'][0].'</div>';

            if(!empty($log['models']['add'])){
              printAdd($log['models']['add'], 'моделей');
            }

            if(!empty($log['models']['upd'])){
              printUpd($log['models']['upd'], 'моделей');
            }

            if(!empty($log['models']['err'])){
              printErr($log['models']['upd'], 'моделей');
            }

            if(!empty($log['cats']['add'])){
              printAdd($log['cats']['add'], 'категорий');
            }

            if(!empty($log['cats']['err'])){
              printErr($log['cats']['upd'], 'категорий');
            }

            if(!empty($log['providers']['add'])){
               printAdd($log['providers']['add'], 'поставщиков');
            }

            if(!empty($log['providers']['err'])){
              printErr($log['providers']['err'], 'поставщиков');
            }

            if(!empty($log['plants']['add'])){
              printAdd($log['plants']['add'], 'сборщиков');
            }

           if(!empty($log['plants']['err'])){
            printErr($log['plants']['err'], 'сборщиков');
            }

            if(!empty($log['serials']['add'])){
              printAdd($log['serials']['add'], 'серийных номеров');
            }

            if(!empty($log['serials']['upd'])){
              printUpd($log['serials']['upd'], 'серийных номеров');
            }

            if(!empty($log['serials']['err'])){
              printErr($log['serials']['err'], 'серийных номеров');
            }
          } 
          echo '</div>';
          } ?>
        </div>

        </div>
  </div>
</div>
</body>
</html>

<?php

function getCheckboxOptionsHTML(array $data, $brandID)
{
  $html = '<div data-flags-list="'.$brandID.'">';
  if(!$data){
    return $html . '<label class="flag-select__item">Категорий нет</label></div>';
  }
  foreach ($data as $row) {
    $html .= '<label class="flag-select__item"><input type="checkbox" name="cat_id[]" data-flags-select-flag data-cat-id value="' . $row['id'] . '"> ' . $row['name'] . '</label>';
  }
  return $html . '</div>';
}

function getCheckOptionsHTML(array $data)
{
  $html = '<option value="0">Все</option>';
  foreach ($data as $row) {
    $html .= '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
  }
  return $html;
}


function printAdd(array $data, $title){
  echo '<div class="import-log__row">
  <h3 class="import-log__title">Добавлено '.$title.': '.count($data).'</h3></div>';
  echo '<ol class="import-log__list">';
  foreach($data as $mes){
    echo '<li>'.$mes.'</li>';
  }
  echo '</ol>';
}


function printUpd(array $data, $title){
  echo '<div class="import-log__row">
  <h3 class="import-log__title">Обновлено '.$title.': '.count($data).'</h3></div>';
  echo '<ol class="import-log__list">';
  foreach($data as $mes){
    echo '<li>'.$mes.'</li>';
  }
  echo '</ol>';
}


function printErr(array $data, $title){
  echo '<div class="import-log__row">
  <h3 class="import-log__title">Ошибки при обработке '.$title.': '.count($data).'</h3></div>';
  echo '<ol class="import-log__list">';
  foreach($data as $mes){
    echo '<li>'.$mes.'</li>';
  }
  echo '</ol>';
}