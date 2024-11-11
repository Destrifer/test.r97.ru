<?php

require '_new-codebase/front/templates/main/parts/repair-card/repair-card.php';
require '_new-codebase/front/templates/main/parts/dashboard/ui.php';
use program\core;
use models;
use models\User;
use models\dashboard\UI;
use models\Repair;

if (\models\User::hasRole('admin', 'slave-admin', 'taker', 'master')) {
$count = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\';'));
} else {
$count = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' and `service_id` = '.User::getData('id').';'));
}


if ($count['COUNT(*)'] > 0) {
if (\models\User::hasRole('admin', 'slave-admin', 'taker', 'master')) {
$content = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `repairs` WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1;'));
$repairStatus = $content['status_admin'];
if ($content['status_admin'] != 'Подтвержден') {
$content['status_admin'] = '' ;
}

$content['repair_done'] = 0;
} else {
$content = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `repairs` WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' and `service_id` = '.User::getData('id').' LIMIT 1;'));
$repairStatus = $content['status_admin'];

}
$content['complexs'] = explode('|', $content['complex']);
$content['visuals'] = explode('|', $content['visual']);
$content['model'] = model($content['model_id']);

$sql = mysqli_query($db, 'SELECT * FROM `repairs_parts` where `repair_id` = '.$content['id']);
      while ($row = mysqli_fetch_array($sql)) {
       $part_info = part_info($row['part_id']);

       $content['parts'] .= '<div class="part"><div class="item"><div class="level">Группы запчастей</div><div class="value"><select name="groups_parts" ><option value="" disabled selected>Выберите вариант</option>'.groups($content['model']['cat'], $part_info['group']).'</select></div></div><div class="item"><div class="level">Запчасть</div><div class="value"><select name="parts_parts[]" ><option value="" disabled selected>'.parts($content['model']['cat'], $content['model']['id'], $content['serial'], $part_info['group'], $row['part_id']).'</option></select></div></div></div>';
      }

//print_r($content['model']);
} else {
header('Location: '.$config['url'].'dashboard/');
}
# Сохраняем:
if ($_POST['send'] == 1) {



$rand = rand(999999,(int) 99999999999999999999);

if ($_FILES['filename']['size'] != 0){
copy($_FILES["filename"]["tmp_name"], $_SERVER['DOCUMENT_ROOT'].'/images/catalog/'.$rand.$_FILES['filename']['name']);
mysqli_query($db, 'INSERT INTO `repairs_photo` (
`repair_id`,
`photo_id`,
`url`
) VALUES (
\''.mysqli_real_escape_string($db, $content['id']).'\',
\'1\',
\''.mysqli_real_escape_string($db, $config['url'].'images/catalog/'.$rand.$_FILES['filename']['name']).'\'
);') or mysqli_error($db);
}

if ($_FILES['filename2']['size'] != 0){
copy($_FILES["filename2"]["tmp_name"], $_SERVER['DOCUMENT_ROOT'].'/images/catalog/'.$rand.$_FILES['filename2']['name']);
mysqli_query($db, 'INSERT INTO `repairs_photo` (
`repair_id`,
`photo_id`,
`url`
) VALUES (
\''.mysqli_real_escape_string($db, $content['id']).'\',
\'2\',
\''.mysqli_real_escape_string($db, $config['url'].'images/catalog/'.$rand.$_FILES['filename2']['name']).'\'
);') or mysqli_error($db);
}

if ($_FILES['filename3']['size'] != 0){
copy($_FILES["filename3"]["tmp_name"], $_SERVER['DOCUMENT_ROOT'].'/images/catalog/'.$rand.$_FILES['filename3']['name']);
mysqli_query($db, 'INSERT INTO `repairs_photo` (
`repair_id`,
`photo_id`,
`url`
) VALUES (
\''.mysqli_real_escape_string($db, $content['id']).'\',
\'3\',
\''.mysqli_real_escape_string($db, $config['url'].'images/catalog/'.$rand.$_FILES['filename3']['name']).'\'
);') or mysqli_error($db);
}

if ($_FILES['filename4']['size'] != 0){
copy($_FILES["filename4"]["tmp_name"], $_SERVER['DOCUMENT_ROOT'].'/images/catalog/'.$rand.$_FILES['filename4']['name']);
mysqli_query($db, 'INSERT INTO `repairs_photo` (
`repair_id`,
`photo_id`,
`url`
) VALUES (
\''.mysqli_real_escape_string($db, $content['id']).'\',
\'5\',
\''.mysqli_real_escape_string($db, $config['url'].'images/catalog/'.$rand.$_FILES['filename4']['name']).'\'
);') or mysqli_error($db);
}

if ($_FILES['filename5']['size'] != 0){
copy($_FILES["filename5"]["tmp_name"], $_SERVER['DOCUMENT_ROOT'].'/images/catalog/'.$rand.$_FILES['filename5']['name']);
mysqli_query($db, 'INSERT INTO `repairs_photo` (
`repair_id`,
`photo_id`,
`url`
) VALUES (
\''.mysqli_real_escape_string($db, $content['id']).'\',
\'5\',
\''.mysqli_real_escape_string($db, $config['url'].'images/catalog/'.$rand.$_FILES['filename5']['name']).'\'
);') or mysqli_error($db);
}

if ($_FILES['filename6']['size'] != 0){
copy($_FILES["filename6"]["tmp_name"], $_SERVER['DOCUMENT_ROOT'].'/images/catalog/'.$rand.$_FILES['filename6']['name']);
mysqli_query($db, 'INSERT INTO `repairs_photo` (
`repair_id`,
`photo_id`,
`url`
) VALUES (
\''.mysqli_real_escape_string($db, $content['id']).'\',
\'6\',
\''.mysqli_real_escape_string($db, $config['url'].'images/catalog/'.$rand.$_FILES['filename6']['name']).'\'
);') or mysqli_error($db);
}

if ($_FILES['filename7']['size'] != 0){
copy($_FILES["filename7"]["tmp_name"], $_SERVER['DOCUMENT_ROOT'].'/images/catalog/'.$rand.$_FILES['filename7']['name']);
mysqli_query($db, 'INSERT INTO `repairs_photo` (
`repair_id`,
`photo_id`,
`url`
) VALUES (
\''.mysqli_real_escape_string($db, $content['id']).'\',
\'7\',
\''.mysqli_real_escape_string($db, $config['url'].'images/catalog/'.$rand.$_FILES['filename7']['name']).'\'
);') or mysqli_error($db);
}

header('Location: '.$config['url'].'dashboard/');
}

photoRedir($_GET['id']);

function model($id) {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `models` where `id` = '.$id);
      while ($row = mysqli_fetch_array($sql)) {
      $content = $row;
     // print_r($row);
      }
    return $content;
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


$repair = Repair::getRepairByID($_GET['id']);

?>
<!doctype html>
<html>
<head>
<meta charset=utf-8>
<title>Акты - Панель управления</title>
<link href="/css/fonts.css" rel="stylesheet" />
<link href="/css/style.css?v=1.00" rel="stylesheet" />
<script src="/_new-codebase/front/vendor/jquery/jquery-1.7.2.min.js"  ></script>
<script src="/js/jquery-ui.min.js"></script>
<script src="/js/jquery.placeholder.min.js"></script>
<script src="/js/jquery.formstyler.min.js"></script>

<script src="/notifier/js/index.js"></script>
<link rel="stylesheet"  href="/notifier/css/style.css">
<link rel="stylesheet" href="/_new-codebase/front/vendor/animate.min.css" />
<script src='/_new-codebase/front/vendor/mustache/mustache.min.js'></script>
<script src="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.concat.min.js"></script>
<link rel="stylesheet" href="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.min.css" />
<script src="/js/jquery.dialogx.js"></script>
<link rel="stylesheet" href="/js/jquery.dialogx.css" />
<link rel="stylesheet" href="/_new-codebase/front/vendor/select2/4.0.4/select2.min.css" />
<script src="/_new-codebase/front/vendor/select2/4.0.4/select2.full.min.js"></script>
<script src="/_new-codebase/front/vendor/jquery-validation/jquery.validate.min.js"></script>
<script src="/_new-codebase/front/vendor/jquery-validation/additional-methods.min.js"></script>
<script src="/_new-codebase/front/vendor/tooltipster/tooltipster.bundle.min.js"></script>
<link rel="stylesheet" href="/_new-codebase/front/vendor/tooltipster/tooltipster.bundle.min.css" />
<link rel="stylesheet" href="/_new-codebase/front/vendor/tooltipster/tooltipster-sideTip-shadow.min.css" />
<link href="/_new-codebase/front/templates/main/css/repair-card/save-parts-window.css" rel="stylesheet">

<script src="/js/main.js"></script>


<script  src="/_new-codebase/front/vendor/datatables/1.10.12/jquery.dataTables.min.js"></script>
<link rel="stylesheet"  href="/css/datatables.css">

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
                              timer: 6000,
                              theme: 'tooltipster-shadow'
                          });
                          $('select').tooltipster({
                              trigger: 'custom',
                              position: 'bottom',
                              animation: 'grow',
                              timer: 6000,
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
  }<?php if (!User::hasRole('slave-admin', 'taker', 'master')) { ?>,
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
  } <?php } ?>
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
        label.error {
          display: block;
    color: red;
    }
</style>
<!-- New codebase -->
<link href="/_new-codebase/front/modules/dashboard/css/ui.css" rel="stylesheet" />
<link rel="stylesheet" href="/_new-codebase/front/vendor/fancybox/jquery.fancybox.min.css" />
<link href="/_new-codebase/front/templates/main/css/repair-card/repair-card.css" rel="stylesheet">
<link href="/_new-codebase/front/templates/main/css/form.css" rel="stylesheet">
<!-- Aside controls -->
<link href="/_new-codebase/front/components/aside-controls/css/aside-controls.css" rel="stylesheet">
</head>

<body>

  <?php
  if ($content['status_admin'] == 'Есть вопросы' && models\User::hasRole('service')) {
    echo '<div class="top-message top-message_alert" style="text-align:center">Пожалуйста, внесите исправления в карточку и отправьте на проверку.</div>';
  }
  ?>

<div class="viewport-wrapper">

<div class="site-header">
  <div class="wrapper" >

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

<div class="wrapper" style="max-width: 1170px">

<?=top_menu_admin();?>

  <div class="adm-tab">

  <?= getSummaryHTML(models\RepairCard::getSummary($content['id'])); ?>

  <?php if (User::getData('id') == 33 && $content['master_id'] != 0  && $content['status_admin'] != 'Подтвержден'  && $_GET['readonly'] != 1) { ?>

    <script>
      $(document).ready(function() {


   if ($('select[name="master_id"]').val() != '') {
   $('.show_faster').show();
   }

    } );
    </script>


     <?php } ?>
<?=menu_dash();?>

  </div><!-- .adm-tab -->


           <br>
             <!-- Меню вкладок -->
             <section class="layout__mb_md">
            <?= getTabsHTML(UI::getTabs(User::getData('role'))); ?>
        </section>
           <h2>Процессинг</h2>
<?php
$stepsNavHTML = getStepsNavHTML(\models\RepairCard::getStepsNav($content['id'], 'docs'));
echo $stepsNavHTML;
?>

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

    <h2>Акты</h2>
       <?php 

       if (in_array($repair['status'], ['Подтвержден', 'Выдан'])) { ?>

       <?php/* if (in_array($content['repair_type_id'], array(1, 2, 3))) { */?>
      <div class="add">
      <a style="width: auto;padding-left: 7px;padding-right: 7px;" href="/get-act/<?=$_GET['id'];?>/" class="button">Скачать Акт выполненных работ</a>
      </div>
      <?php/* }*/ ?>

      <?php if (in_array($content['repair_type_id'], array(4)) && !isSmartRepair()) { ?>
      <div class="add">
      <a style="width: auto;padding-left: 7px;padding-right: 7px;" href="/get-reject/<?=$_GET['id'];?>/" class="button">Скачать Акт неремонтопригодности</a>
    </div>
       <?php } ?>

      <?php if (in_array($content['repair_type_id'], array(5, 23))) { ?>
          <div class="add">
      <a style="width: auto;padding-left: 7px;padding-right: 7px;" href="/get-tech/<?=$_GET['id'];?>/" class="button">Скачать Акт тех. заключения</a>
    </div>
    <?php } ?>
    <?php } else { ?>
    Акты будут доступны после подтверждения этой заявки со стороны Harper.
    <?php } ?>
  </div><!-- .adm-video -->


  </div>



        </div>

      </form>

      <?php
echo $stepsNavHTML;
?>


        </div>
  </div>
</div>
<!-- New codebase -->
<script src="/_new-codebase/front/vendor/fancybox/jquery.fancybox.min.js"></script>
<script src="/_new-codebase/front/components/status/status.js"></script>
<script src='/_new-codebase/front/components/repair-card/repair-card.js?v=1.02'></script>
<!-- Aside controls -->
<script src="/_new-codebase/front/components/request.js"></script>
<script src="/_new-codebase/front/components/aside-controls/js/confirm-approve-window.js"></script>
<script src="/_new-codebase/front/components/aside-controls/js/save-parts-window.js"></script>
<script src="/_new-codebase/front/components/aside-controls/js/aside-controls.js"></script>
<!-- / Aside controls -->
<div id="aside-controls-json" style="display: none"><?= json_encode(models\RepairCard::getAsideControls($content['id'])); ?></div>
<div id="repair-data-json" style="display: none"><?= json_encode(['id' => $content['id'], 'model_id' => $content['model_id']]); ?></div>
<div id="user-data-json" style="display: none"><?= json_encode(['id' => models\User::getData('id'), 'role' => models\User::getData('role')]); ?></div>
</body>
</html>


<?php
function isSmartRepair()
{
  global $db, $content;
  $sql = mysqli_query($db, 'SELECT `problem_id` FROM `repairs_work` WHERE `repair_id` = ' . $content['id']);
  while ($row = mysqli_fetch_assoc($sql)) {
    if ($row['problem_id'] == 60) {
      return true;
    }
  }
  return false;
}