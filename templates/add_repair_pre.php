<?php

use models\Log;
use models\User;

require '_new-codebase/front/templates/main/parts/repair-card/repair-card.php';

if (User::hasRole('slave-admin', 'taker')) {
mysqli_query($db, 'INSERT INTO `repairs` (
`service_id`,
`status_admin`
) VALUES (
33,
"Принят"
);') or mysqli_error($db);

$id = mysqli_insert_id($db);
Log::repair(10, 'На вкладке "Проверка".', $id);
header('Location: /edit-repair/'.$id.'/');
exit;
}

# Сохраняем:
if ($_POST['send'] == 1) {



$service_id = (User::getData('role_id') < 4) ? User::getData('id') : 33;
$personal = (User::hasRole('admin')) ? '' : ',`status_admin`';
$personal2 = (User::hasRole('admin')) ? '' : ',"Принят"';
mysqli_query($db, 'INSERT INTO `repairs` (
`service_id`,
`model_id`
'.$personal.'
) VALUES (
\''.mysqli_real_escape_string($db, $service_id).'\',
\''.mysqli_real_escape_string($db, $_POST['model_id']).'\'
'.$personal2.'
);') or mysqli_error($db);
$id = mysqli_insert_id($db);
Log::repair(10, 'На вкладке "Проверка".', $id);

header('Location: /edit-repair/'.$id.'/');
}


function models($cat_id = '') {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `models` ;');
$user_perms = 0;
$allow = 0;
      while ($row = mysqli_fetch_array($sql)) {

      if (!User::hasRole('slave-admin', 'admin', 'master', 'taker')) {

      $brand = mysqli_fetch_array(mysqli_query($db, 'SELECT `can_repair` FROM `brands` WHERE `name` = \''.mysqli_real_escape_string($db, $row['brand']).'\' LIMIT 1;'));


      if ($brand['can_repair'] == 1) {

      $check_can = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `models_users` WHERE `model_id` = \''.mysqli_real_escape_string($db, $row['id']).'\' and service_id = \''.mysqli_real_escape_string($db, User::getData('id')).'\' LIMIT 1;'));
      if ($check_can['COUNT(*)'] > 0) {
        $user_perms = 1;
        $check_can2 = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `models_users` WHERE `model_id` = \''.mysqli_real_escape_string($db, $row['id']).'\' and service_id = \''.mysqli_real_escape_string($db, User::getData('id')).'\' and service = \'Да\' LIMIT 1;'));
        if ($check_can2['COUNT(*)'] > 0) {
            $allow = 1;
        }
      }

      if ($user_perms != 1) {
        if (trim($row['service']) == 'Да') {
            $allow = 1;
        }
      }

     // echo $row['id'].' - '.$user_perms.' - '.$allow."\n";

      if ($allow == 1) {
       if ($cat_id == $row['id']) {
      $content .= '<option selected value="'.$row['id'].'">'.$row['name'].'</option>';
      } else {
       $content .= '<option value="'.$row['id'].'">'.$row['name'].'</option>';
      }
      }

      }

      } else {

      if ($cat_id == $row['id']) {
      $content .= '<option selected value="'.$row['id'].'">'.$row['name'].'</option>';
      } else {
       $content .= '<option value="'.$row['id'].'">'.$row['name'].'</option>';
      }

      }


      }
    return $content;
}

function masters($cat_id) {
  global $db;
$content = array();
$sql = mysqli_query($db, 'SELECT * FROM `repairmans` WHERE `service_id` = '.User::getData('id'));
      while ($row = mysqli_fetch_array($sql)) {
      if ($cat_id == $row['id']) {
      $content .= '<option selected value="'.$row['id'].'">'.$row['name'].' '.$row['surname'].'</option>';
      } else {
       $content .= '<option value="'.$row['id'].'">'.$row['name'].' '.$row['surname'].'</option>';
      }
      }
    return $content;
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

?>
<!doctype html>
<html>
<head>
<meta charset=utf-8>
<title>Добавление карточки ремонта - Панель управления</title>
<link href="/css/fonts.css" rel="stylesheet" />
<link href="/css/style.css" rel="stylesheet" />

<script src="/_new-codebase/front/vendor/jquery/jquery-1.7.2.min.js"></script>
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

<script src="/notifier/js/index.js"></script>
<link rel="stylesheet" href="/notifier/css/style.css">
<link rel="stylesheet" href="/_new-codebase/front/vendor/animate.min.css" />
<script src='/_new-codebase/front/vendor/mustache/mustache.min.js'></script>
<script src="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.concat.min.js"></script>
<link rel="stylesheet" href="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.min.css" />

<script src="/js/main.js"></script>
<script src="/_new-codebase/front/vendor/datatables/js/data-tables.min.js"></script>
<link rel="stylesheet" type="text/css" href="/css/datatables.css">

<script >
// Таблица
$(document).ready(function() {
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
        ignore: [],
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

    var max_fields2      = 50; //maximum input boxes allowed
    var wrapper2         = $(".input_fields_wrap2"); //Fields wrapper
    var add_button2      = $(".add_field_button2"); //Add button ID

    var x2 = 1; //initlal text box count
    $(add_button2).click(function(e){ //on add input button click
        e.preventDefault();
        if(x2 < max_fields2){ //max input box allowed
            x2++; //text box increment
            $(wrapper2).append('<div class="value part"><input type="text" value="" name="parts[]"/> <a href="#" class="remove_field del"></a></div>'); //add input box
        }
    });

    $(wrapper2).on("click",".remove_field", function(e){ //user click on remove text
        e.preventDefault(); $(this).parent('div').remove(); x--;
    });

$( ".datepicker" ).datepicker();
$("#ui-datepicker-div").addClass("ll-skin-cangas");
$.datepicker.setDefaults( $.datepicker.regional[ "ru" ] );


    $('select[name=cat_parts]').on('selectmenuchange', function() {
        var value = $(this).val();
              if (value) {

                  $.get( "/ajax.php?type=get_models&id="+value, function( data ) {
                  /*var obj = jQuery.parseJSON(data);
                  $('input[name=title]').val(obj.title);  */
                  $('select[name=model_id_parts]').html(data.html).selectmenu( "refresh" );
                  $('input[name="cat_parts_hidden"]').val(value);


                  });

              }
              return false;
    });

    $('.serial_check').on('change', function() {
        var value = $(this).val();
        var input = $(this);
        var cat = $('input[name="cat_parts_hidden"]').val();
              if (value) {

                  $.get( "/ajax.php?type=check_serials&model_name="+$('input[name="model_id_check"]').val()+"&serial="+value, function( data ) {
                  /*var obj = jQuery.parseJSON(data);
                  $('input[name=title]').val(obj.title);  */
                  //$('select[name=serial_parts]').html(data.html).selectmenu( "refresh" );
                  if (data.answer == 1) {
                  input.css('border-color', '#8BC34A');
                  $('input[name="serial_parts_hidden"]').val(value);

                  $.get( "/ajax.php?type=get_group&serial="+value+"&model_id="+data.model, function( datatwo ) {
                  $('select[name=groups_parts]').html(datatwo.html).selectmenu( "refresh" );
                  });

                  } else {
                  input.css('border-color', '#F44336');
                  }


                  });

              }


        return false;
    });

    $('select[name="model_id"]').on('change', function() {
        var value = $(this).val();
        var input = $(this);
              if (value) {

                  $.get( "/ajax.php?type=check_model1&id="+value, function( data ) {
                  if (data.answer == 1) {
                  $('.submit_true').show();
                  $('.error_check').hide();
                  $('.ok_check').show();
                  } else {
                  $('.submit_true').hide();
                  $('.error_check').show();
                  $('.ok_check').hide();

                  <?php if (User::hasRole('slave-admin')) { ?>
                  $('.submit_true').show();
                  $('.error_check').hide();
                  $('.ok_check').show();
                  <?php } ?>

                  }


                  });

              }


        return false;
    });

    $('.select2').on('select2:selecting', function(e) {
        $('input[name="model_id_check"]').val(e.params.args.data.id);
    });

    /*$('select[name=model_id_parts]').on('selectmenuchange', function() {
        var value = $(this).val();
        var cat = $('input[name="cat_parts_hidden"]').val();
              if (value) {

                  $.get( "/ajax.php?type=get_serials&model_id="+value+"&cat="+cat, function( data ) {
                  $('select[name=serial_parts]').html(data.html).selectmenu( "refresh" );
                  $('input[name="model_id_parts_hidden"]').val(value);



                  });

              }


        return false;
    }); */


    /*$('select[name=serial_parts]').on('selectmenuchange', function() {
        var value = $(this).val();
        var cat = $('input[name="cat_parts_hidden"]').val();
        var model = $('input[name="model_id_parts_hidden"]').val();
              if (value) {

                  $.get( "/ajax.php?type=get_group&serial="+value+"&model_id="+model+"&cat="+cat, function( data ) {
                  $('select[name=groups_parts]').html(data.html).selectmenu( "refresh" );
                  $('input[name="serial_parts_hidden"]').val(value);


                  });

              }


        return false;
    }); */

    $('select[name=groups_parts]').on('selectmenuchange', function() {
        var value = $(this).val();
        var cat = $('input[name="cat_parts_hidden"]').val();
        var model = $('input[name="model_id_parts_hidden"]').val();
        var serial = $('input[name="serial_parts_hidden"]').val();
              if (value) {

                  $.get( "/ajax.php?type=get_parts&group="+value+"&serial="+serial+"&model_id="+model+"&cat="+cat, function( data ) {
                  /*var obj = jQuery.parseJSON(data);
                  $('input[name=title]').val(obj.title);  */
                  $('select[name=parts_parts]').html(data.html).selectmenu( "refresh" );
                  $('input[name="serial_parts_hidden"]').val(value);


                  });

              }


        return false;
    });

    $('select[name=parts_parts]').on('selectmenuchange', function() {
              var value = $(this).val();
              if (value) {

                   $('.add_to_list').show();

              }


        return false;
    });

$('.select2').select2();

$("select").on("select2:close", function (e) {
        $(this).valid();
    });


/*$(document.body).on("change",".select2",function(){
 if ($(".validate_form").length) {

        return true;
});    */


} );

</script>
<script>
/* Russian (UTF-8) initialisation for the jQuery UI date picker plugin. */
/* Written by Andrew Stromnov (stromnov@gmail.com). */
( function( factory ) {
  if ( typeof define === "function" && define.amd ) {

    // AMD. Register as an anonymous module.
    define( [ "../widgets/datepicker" ], factory );
  } else {

    // Browser globals
    factory( jQuery.datepicker );
  }
}( function( datepicker ) {

datepicker.regional.ru = {
  closeText: "Закрыть",
  prevText: "&#x3C;Пред",
  nextText: "След&#x3E;",
  currentText: "Сегодня",
  monthNames: [ "Январь","Февраль","Март","Апрель","Май","Июнь",
  "Июль","Август","Сентябрь","Октябрь","Ноябрь","Декабрь" ],
  monthNamesShort: [ "Янв","Фев","Мар","Апр","Май","Июн",
  "Июл","Авг","Сен","Окт","Ноя","Дек" ],
  dayNames: [ "воскресенье","понедельник","вторник","среда","четверг","пятница","суббота" ],
  dayNamesShort: [ "вск","пнд","втр","срд","чтв","птн","сбт" ],
  dayNamesMin: [ "Вс","Пн","Вт","Ср","Чт","Пт","Сб" ],
  weekHeader: "Нед",
  dateFormat: "dd.mm.yy",
  firstDay: 1,
  isRTL: false,
  showMonthAfterYear: false,
  yearSuffix: "" };
datepicker.setDefaults( datepicker.regional.ru );

return datepicker.regional.ru;

} ) );
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
           <h2>Добавление карточки ремонта</h2>


           <?php
        $stepsNavHTML = getStepsNavHTML(\models\RepairCard::getStepsNav(0, 'check'));
        echo $stepsNavHTML;
      ?>

  <form id="send" method="POST" class="repair_form validate_form">
   <div class="adm-form" style="padding-top:0;">



  <div id="tab-1" class="tab-content current">

                 <br> <h3>Добавление карточки ремонта:</h3><br>

                <p style="text-align:left;padding: 0px 30px;">Убедитесь, что модель, с которой обратился клиент, подлежит обслуживанию в сервисных центрах. Для этого выберите модель в списке ниже и посмотрите результат.
                <br><br>
                Если в результате вы видите "не обслуживается", сообщите клиенту, чтобы он обратился напрямую в торгующую организацию для замены или возврата данного товара.
                <br><br>
                Будьте внимательны, мы не компенсируем ремонт или актирование необслуживаемого товара.
                </p>


                    <div class="item" style="width:100%;">
              <div class="level">Модель:</div>
              <div class="value">
              <select name="model_id" class="select2 nomenu" style="margin-left: 140px !important;  margin-top: 40px !important;">
               <option value="">Выберите вариант</option>
               <?=models();?>
              </select>
              </div>
            </div>


            <div class="item error_check" style="width:100%;display:none;" >
              <div class="level" style="color:#FF0000">Не обслуживается.</div>
            </div>
               <div class="item ok_check" style="width:100%;display:none;" >
              <div class="level" style="color:#7ACC00">Все хорошо, модель обслуживается.</div>
            </div>

                <div class="adm-finish">
            <div class="save">
              <input type="hidden" name="send" value="1" />
              <button type="submit" class="submit_true" style="display:none;" >Создать карточку ремонта</button>
            </div>
            </div>
        </div>
        </div>
      </form>




  </div>
</div>
</body>
</html>