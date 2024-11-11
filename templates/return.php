<?php

use program\core;
use program\adapters;
use models;

$count = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `returns` WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' ;'));
if ($count['COUNT(*)'] > 0) {
$content = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `returns` WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\'  LIMIT 1;'));
      $client = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `clients` WHERE `id` = \''.mysqli_real_escape_string($db, $content['client_id']).'\' LIMIT 1;'));
      $sql_check_count = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` WHERE `return_id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\';'));
      $sql_check_count2 = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` WHERE `return_id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' and `status_admin` = \'Подтвержден\';'));

      $percent = ($sql_check_count['COUNT(*)'] > $sql_check_count2['COUNT(*)']) ? round(($sql_check_count2['COUNT(*)']/$sql_check_count['COUNT(*)'])*100) : round(($sql_check_count['COUNT(*)']/$sql_check_count2['COUNT(*)'])*100);

/*$sql = mysqli_query($db, 'SELECT * FROM `parts` WHERE `parent_id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\'');
if (mysqli_num_rows($sql) > 0) {
while ($row = mysqli_fetch_array($sql)) {

$content['models_array'][$row['model_id']][] = $row['serial'];

}
//print_r($content['models_array']);

foreach ($content['models_array'] as $model_id => $serial) {
$content['models_other'] .= '<div class="value2 noheight"><select name="model_add_id[]" class="select3 nomenu"><option>Выберите вариант</option>'.models($model_id).'</select> <select  name="serial_add['.$model_id.'][]" class="select3 nomenu" multiple="multiple"><option value="">Выберите вариант</option>'.get_serial_name($model_id, $serial).'</select><br><a href="#" class="select_all">Выбрать все</a><a href="#" class="remove_field del" data-id="'.$row['id'].'"></a></div><br><br>';
}

//$content['models_other'] .= '<div class="value2"><select name="model_add_id[]" class="select3 nomenu"><option>Выберите вариант</option>'.models($row['model_id']).'</select> <select name="serial_add[]" class="select3 nomenu" multiple="multiple"><option>Выберите вариант</option>'.serials_select($row['model_id'], $row['serial']).'</select><a href="#" class="remove_field del" data-id="'.$row['id'].'"></a></div>';

}  */

} else {
header('Location: '.$config['url'].'dashboard/');
}
# Сохраняем:
if ($_POST['send'] == 1) {

mysqli_query($db, 'UPDATE `parts` SET
`cat` = \''.mysqli_real_escape_string($db, $_POST['cat']).'\',
`model_id` = \''.mysqli_real_escape_string($db, $_POST['model_id']).'\',
`serial` = \''.mysqli_real_escape_string($db, $_POST['serial']).'\',
`group` = \''.mysqli_real_escape_string($db, str_replace("'", '', $_POST['group'])).'\',
`list` = \''.mysqli_real_escape_string($db, $_POST['list']).'\',
`desc` = \''.mysqli_real_escape_string($db, $_POST['desc']).'\',
`type` = \''.mysqli_real_escape_string($db, $_POST['type']).'\',
`weight` = \''.mysqli_real_escape_string($db, $_POST['weight']).'\',
`price` = \''.mysqli_real_escape_string($db, $_POST['price']).'\',
`part` = \''.mysqli_real_escape_string($db, $_POST['part']).'\',
`brand` = \''.mysqli_real_escape_string($db, $_POST['brand']).'\',
`codepre` = \''.mysqli_real_escape_string($db, $_POST['codepre']).'\',
`count` = \''.mysqli_real_escape_string($db, $_POST['count']).'\',
`imgs` = \''.mysqli_real_escape_string($db, uploadPreviews($_POST['files_preview'])).'\'
WHERE `id` = \''.$_GET['id'].'\' LIMIT 1') or mysqli_error($db);


admin_log_add('Обновлена запчасть #'.$_GET['id']);

// Удаление:
if (count($_POST['delete']) > 0) {
foreach ($_POST['delete'] as $ids) {
//mysqli_query($db, 'DELETE FROM `parts` WHERE `id` = \''.$ids.'\';') or mysqli_error($db);
}
}

//print_r($_POST);

// Добавление новых:
$count = count($_POST['model_add_id']);
$count_arrays = 0;
while($count_arrays < $count) {

foreach ($_POST['serial_add'][$_POST['model_add_id'][$count_arrays]] as $serial) {

$count_check = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `parts` WHERE `model_id` = \''.mysqli_real_escape_string($db, $_POST['model_add_id'][$count_arrays]).'\' and `parent_id` = \''.$_GET['id'].'\' and `serial` = \''.$serial.'\' ;'));
if ($count_check['COUNT(*)'] == 0) {

mysqli_query($db, 'INSERT INTO `parts` (
`cat`,
`model_id`,
`serial`,
`group`,
`list`,
`desc`,
`type`,
`weight`,
`price`,
`part`,
`brand`,
`codepre`,
`count`,
`parent_id`,
`imgs`
) VALUES (
\''.mysqli_real_escape_string($db, $_POST['cat']).'\',
\''.mysqli_real_escape_string($db, $_POST['model_add_id'][$count_arrays]).'\',
\''.mysqli_real_escape_string($db, $serial).'\',
\''.mysqli_real_escape_string($db, str_replace("'", '', $_POST['group'])).'\',
\''.mysqli_real_escape_string($db, $_POST['list']).'\',
\''.mysqli_real_escape_string($db, $_POST['desc']).'\',
\''.mysqli_real_escape_string($db, $_POST['type']).'\',
\''.mysqli_real_escape_string($db, $_POST['weight']).'\',
\''.mysqli_real_escape_string($db, $_POST['price']).'\',
\''.mysqli_real_escape_string($db, $_POST['part']).'\',
\''.mysqli_real_escape_string($db, $_POST['brand']).'\',
\''.mysqli_real_escape_string($db, $_POST['codepre']).'\',
\''.mysqli_real_escape_string($db, $_POST['count']).'\',
\''.mysqli_real_escape_string($db, $_GET['id']).'\',
\''.mysqli_real_escape_string($db, uploadPreviews($_POST['files_preview'])).'\'
);') or mysqli_error($db);
}
}

$count_arrays++;
}

$sql = mysqli_query($db, 'SELECT * FROM `parts` WHERE `parent_id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\'');
if (mysqli_num_rows($sql) > 0) {
while ($row = mysqli_fetch_array($sql)) {
if (in_array($row['serial'], $_POST['serial_add'][$row['model_id']])) {

} else {
mysqli_query($db, 'DELETE FROM `parts` WHERE `model_id` = '.$row['model_id'].' and `parent_id` = '.$_GET['id'].' and  `serial` = \''.$row['serial'].'\';') or mysqli_error($db);
}
}
}

//header('Location: '.$config['url'].'parts/');
}

function models($cat_id = '') {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `models` where `service` = \'Да\' order by `name` asc;');
      while ($row = mysqli_fetch_array($sql)) {
      if ($cat_id == $row['id']) {
      $content .= '<option selected value="'.$row['id'].'">'.$row['name'].'</option>';
      } else {
       $content .= '<option value="'.$row['id'].'">'.$row['name'].'</option>';
      }
      }
    return $content;
}

function get_provider_name($id)  {
  global $config, $db;
$sql = mysqli_query($db, 'SELECT * FROM `providers` WHERE `id` = \''.mysqli_real_escape_string($db, $id).'\'');
return mysqli_fetch_array($sql)['name'];
}

function cat($cat_id = '') {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `cats` where `name` != \'\';');

      while ($row = mysqli_fetch_array($sql)) {
      if ($cat_id == $row['id']) {
      $content .= '<option selected value="'.$row['id'].'">'.$row['name'].'</option>';
      } else {
       $content .= '<option value="'.$row['id'].'">'.$row['name'].'</option>';
      }
      }
    return $content;
}

function get_serial_name($id, $currents = '') {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `serials` where `model_id` = '.$id);
      while ($row = mysqli_fetch_array($sql)) {
      $order = ($row['order']) ? ', '.$row['order'] : '';
      if (in_array($row['serial'], $currents)) {
      $content .= '<option selected value="'.$row['serial'].'">'.$row['serial'].'('.get_provider_name($row['provider_id']).''.$order.')</option>';
      } else {
       $content .= '<option value="'.$row['serial'].'">'.$row['serial'].' ('.get_provider_name($row['provider_id']).''.$order.')</option>';
      }
      }
    return $content;
}

function serials_select($id, $current = '') {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `serials` where `model_id` = '.$id);
      while ($row = mysqli_fetch_array($sql)) {
      $order = ($row['order']) ? ', '.$row['order'] : '';
      if ($current == $row['serial']) {
      $content .= '<option selected value="'.$row['serial'].'">'.$row['serial'].'('.get_provider_name($row['provider_id']).''.$order.')</option>';
      } else {
       $content .= '<option value="'.$row['serial'].'">'.$row['serial'].' ('.get_provider_name($row['provider_id']).''.$order.')</option>';
      }
      }
    return $content;
}

function groups($cat_id = '') {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `groups` where `name` != \'\';');
      while ($row = mysqli_fetch_array($sql)) {
      if ($cat_id == $row['name']) {
      $content .= '<option selected value="'.$row['name'].'">'.$row['name'].'</option>';
      } else {
       $content .= '<option value="'.$row['name'].'">'.$row['name'].'</option>';
      }
      }
    return $content;
}

function group_by_name($name, $current = '') {
  global $config, $db;
$sql = mysqli_query($db, 'SELECT * FROM `groups` WHERE `cat` = \''.mysqli_real_escape_string($db, $name).'\'');
      while ($row = mysqli_fetch_array($sql)) {

      if ($current == $row['name']) {
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
<link rel="stylesheet" href="/_new-codebase/front/vendor/fancybox/3.2.5/jquery.fancybox.min.css" />
<script src="/_new-codebase/front/vendor/fancybox/3.2.5/jquery.fancybox.min.js"></script>
<script  src="/_new-codebase/front/vendor/datatables/1.10.12/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?=$config['url'];?>css/datatables.css">
<link rel="stylesheet" href="/_new-codebase/front/vendor/select2/4.0.4/select2.min.css" />
<script src="/_new-codebase/front/vendor/select2/4.0.4/select2.full.min.js"></script>
<script src="/_new-codebase/front/vendor/select2/4.0.4/ru.js"></script>
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

    $('.select2').select2();
    $('.select3').select2();

    $('select[name=model_id]').on('change', function() {
        var value = $(this).val();
              if (value) {

                  $.get( "/ajax.php?type=get_cat&id="+value, function( data ) {
                  /*var obj = jQuery.parseJSON(data);
                  $('input[name=title]').val(obj.title);  */
                  $('select[name=cat]').html(data.html).selectmenu( "refresh" );
                  //$('select[name="cat"]').val(data.value);
                  $('select[name=serial]').html(data.html2).trigger('change.select2');

                  $('input[name=codepre]').val(data.pre);

                  });

              }
              return false;
    });

    $('select[name=group]').on('selectmenuchange', function() {
        var value = $(this).val();

              if (value) {

                  $.get( "/ajax.php?type=get_pre&id="+value, function( data ) {
                  /*var obj = jQuery.parseJSON(data);
                  $('input[name=title]').val(obj.title);  */
                  $('input[name=codepre]').val(data.pre);

                  });

              }
              return false;
    });

    $(document).on('change', 'select[name="model_add_id[]"]', function() {
        var value = $(this).val();
        var this_block = $(this).parent();
              if (value) {
                  $.get( "/ajax.php?type=get_cat&id="+value, function( data ) {
                  this_block.find($('select[name="serial_add[]"]').attr('name', 'serial_add['+value+'][]'));
                  this_block.find($('select[name="serial_add['+value+'][]"]')).html('<option value="">Выберите вариант</option>'+data.html2).trigger('change.select2');
                  });
              }
              return false;
    });

    $('select[name=group]').on('selectmenuchange', function() {
        var value = $(this).val();

              if (value) {

                  $.get( "/ajax.php?type=get_pre&id="+value, function( data ) {
                  /*var obj = jQuery.parseJSON(data);
                  $('input[name=title]').val(obj.title);  */
                  $('input[name=codepre]').val(data.pre);

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
            $(wrapper2).append('<div class="value2 noheight"><select name="model_add_id[]" class="select3 nomenu"><option>Выберите вариант</option><?=models();?></select> <select name="serial_add[]" class="select3 nomenu " multiple="multiple"><option value="">Выберите вариант</option></select><br><a href="#" class="select_all">Выбрать все</a><a href="#" class="remove_field del"></a></div><br><br>');
            $('.select3').select2();
        }
    });

    $(wrapper2).on("click",".remove_field", function(e){ //user click on remove text
        if ($(this).data('id')) {
        $('.save').append('<input type="hidden" name="delete[]" value="'+$(this).data('id')+'">');
        }
        e.preventDefault(); $(this).parent('div').remove(); x2--;

    })

$(document).on('click', '.select_all', function() {
    var select = $(this).parent().find('select[name*="serial_add"]');
    select.find('option[value!=""]').prop("selected","selected");
    select.trigger('change');
    return false;
});

} );

</script>
<script  src="/js/ajaxupload.3.5.dev.js" ></script>

<script >
$(document).ready(function(){
      var maxPhotos = 50;

      $(".remove_preview").live('click', function(e){
        $(this).parent().remove();
        var total = $("#files li").length;
        if(total>=maxPhotos){
          $("#upload").fadeOut();
        }else{
          $("#upload").fadeIn();
        }
      });
      var btnUpload = $('#upload');
      var status = $('#status');
      new AjaxUpload(btnUpload, {
        action: '/js/upload-file-dev.php',
        name: 'uploadfile[]',
        onSubmit: function(file, ext){
          /*if (! (ext && /^(jpg|png|jpeg|gif)$/.test(ext))){
            // extension is not allowed
            status.text('Можно загружать только JPG, PNG или GIF файлы');
            return false;
          }*/
          status.html('<div><span style="vertical-align: middlebackground: #fff;">Загрузка...Подождите пока файл/ы будет загружен.</span></div>');
        },
        onComplete: function(file, response){
          //On completion clear the status
          status.text('');
          //Add uploaded file to list
          if(response!=="error"){

            var resp=$.parseJSON(response);
            var fl=0;
            for(a=0;a<resp.length;a++)
            {
              if(resp[a]=='false')
              {
                fl++;
              }
            }
            if(fl==resp.length)
            {
              status.text('Можно загружать только JPG, PNG или GIF файлы');
              return false;
            }
            if(fl>0)
            {
              status.text('Можно загружать только JPG, PNG или GIF файлы. Один или несколько файлов не соответсвуют формату. Эти файлы не были загружены.');
            }

            var total = $("#files li").length+resp.length;
            if(total>=maxPhotos){
              $("#upload").fadeOut();
            }else{
              $("#upload").fadeIn();
            }

            for(a=0;a<resp.length-fl;a++)
            {
              if(resp[a]!='false')
              {
                $('<li class="adm-media-item"></li>').appendTo('#files').html('<div class="img "><span style="background: #fff;"><img style="max-height:100px;max-width: 150px;" src="'+resp[a]+'" alt=""/></span></div><a href="'+resp[a]+'" class="del"></a><input type="hidden" name="files_preview[]" value="'+resp[a]+'" />').addClass('success');
              }
            }


          } else{
            $('<li></li>').appendTo('#files').text("Ошибка").addClass('error').fadeOut(5000);
          }
        }
      });


$('#files img').each(function (){
 var currentImage = $(this);
 currentImage.wrap("<a class='fancybox' href='" + currentImage.attr("src") + "'</a>"); });

$(".fancybox").fancybox();

    });
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
    .select2-container {
    width:90% !important;
    height:auto !important;

    }
    .select2-selection, .select2-container {
height:unset !Important;
}
.noheight {
  position: relative;
}
.input_fields_wrap2 .del {
    position: absolute;
    top: 50%;
    right: 0;
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
           <h2>Информация о партии <?=$content['name'];?></h2>

  <form id="send" method="POST">
   <div class="adm-form" style="padding-top:0;">


                  <div class="item">
              <div class="level">Клиент:</div>
              <div class="value">
                <input type="text" value="<?=$client['name'];?>" readonly />
              </div>
            </div>

                  <div class="item">
              <div class="level">Дата приема:</div>
              <div class="value">
                <input type="text" value="<?=$content['date'];?>" readonly  />
              </div>
            </div>

                  <div class="item">
              <div class="level">Выдать не позднее:</div>
              <div class="value">
                <input type="text" value="<?=date("d.m.Y", strtotime($content['date'] . " +".$client['days']." days"));?>" readonly  />
              </div>
            </div>

                  <div class="item">
              <div class="level">Кол-во в партии:</div>
              <div class="value">
                <input type="text" value="<?=$sql_check_count['COUNT(*)'];?>" readonly  />
              </div>
            </div>

                  <div class="item">
              <div class="level">Уже проверено:</div>
              <div class="value">
                <input type="text" value="<?=$sql_check_count2['COUNT(*)'];?>" readonly  />
              </div>
            </div>

                   <div class="item">
              <div class="level">Номер партии:</div>
              <div class="value">
                <input type="text" value="<?=$content['name'];?>" readonly  />
              </div>
            </div>



        </div>

      </form>




        </div>
  </div>
</body>
</html>

<?php

function uploadPreviews(array $previews)
{
  $res = [];
  foreach ($previews as $preview) {
    if (strpos($preview, 'digitalocean') !== false) {
      $res[] = $preview;
      continue;
    }
    try {
      $file = new core\File($preview);
      if(!$file->exists()){
        continue;
      }
      $url = adapters\DigitalOcean::uploadFile($preview, 'uploads/photos/parts/' . date('mY') . '/' . adapters\DigitalOcean::makeFilename() . '.' . strtolower($file->ext));
      if (empty($url)) {
        continue;
      }
      $res[] = $url;
    } catch (Exception $e) {
      continue;
    }
  }
  if(!$res){
    return '';
  }
  return json_encode($res);
}