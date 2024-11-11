<?php

# Сохраняем:

use models\User;

if ($_POST['send'] == 1) {



mysqli_query($db, 'INSERT INTO `repairs` (
`service_id`,
`rsc`,
`client`,
`client_type`,
`city`,
`address`,
`phone`,
`name_shop`,
`city_shop`,
`address_shop`,
`phone_shop`,
`model_id`,
`talon`,
`serial`,
`status_id`,
`sell_date`,
`complex`,
`visual`,
`bugs`,
`comment`
) VALUES (
\''.mysqli_real_escape_string($db, User::getData('id')).'\',
\''.mysqli_real_escape_string($db, $_POST['rsc']).'\',
\''.mysqli_real_escape_string($db, $_POST['client']).'\',
\''.mysqli_real_escape_string($db, $_POST['client_type']).'\',
\''.mysqli_real_escape_string($db, $_POST['city']).'\',
\''.mysqli_real_escape_string($db, $_POST['address']).'\',
\''.mysqli_real_escape_string($db, $_POST['phone']).'\',
\''.mysqli_real_escape_string($db, $_POST['name_shop']).'\',
\''.mysqli_real_escape_string($db, $_POST['city_shop']).'\',
\''.mysqli_real_escape_string($db, $_POST['address_shop']).'\',
\''.mysqli_real_escape_string($db, $_POST['phone_shop']).'\',
\''.mysqli_real_escape_string($db, $_POST['model_id']).'\',
\''.mysqli_real_escape_string($db, $_POST['talon']).'\',
\''.mysqli_real_escape_string($db, $_POST['serial']).'\',
\''.mysqli_real_escape_string($db, $_POST['status_id']).'\',
\''.mysqli_real_escape_string($db, date('Y-m-d', strtotime($_POST['date']))).'\',
\''.mysqli_real_escape_string($db, implode('|', $_POST['complex'])).'\',
\''.mysqli_real_escape_string($db, implode('|', $_POST['visual'])).'\',
\''.mysqli_real_escape_string($db, $_POST['bugs']).'\',
\''.mysqli_real_escape_string($db, $_POST['comment']).'\'
);') or mysqli_error($db);

admin_log_add('Добавлен новый ремонт '.mysqli_insert_id($db));

header('Location: '.$config['url'].'edit-repair/'.mysqli_insert_id($db).'/step/2/');
}


function models($cat_id = '') {
  global $db;
$content = array();
$sql = mysqli_query($db, 'SELECT * FROM `models`;');
      while ($row = mysqli_fetch_array($sql)) {
      if ($cat_id == $row['id']) {
      $content .= '<option selected value="'.$row['id'].'">'.$row['name'].'</option>';
      } else {
       $content .= '<option value="'.$row['id'].'">'.$row['name'].'</option>';
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
<title>Панель управления</title>
<link href="<?=$config['url'];?>css/fonts.css" rel="stylesheet" />
<link href="<?=$config['url'];?>css/style.css" rel="stylesheet" />

<script src="/_new-codebase/front/vendor/jquery/jquery-1.7.2.min.js"  ></script>
<script src="<?=$config['url'];?>js/jquery-ui.min.js"></script>
<script src="<?=$config['url'];?>js/jquery.placeholder.min.js"></script>
<script src="<?=$config['url'];?>js/jquery.formstyler.min.js"></script>
<link rel="stylesheet" href="/_new-codebase/front/vendor/select2/4.0.4/select2.min.css" />
<script src="/_new-codebase/front/vendor/select2/4.0.4/select2.full.min.js"></script>
<script src="/_new-codebase/front/vendor/jquery-validation/jquery.validate.min.js"></script>
<script src="/_new-codebase/front/vendor/jquery-validation/additional-methods.min.js"></script>
<script src="/_new-codebase/front/vendor/tooltipster/tooltipster.bundle.min.js"></script>
<link rel="stylesheet" href="/_new-codebase/front/vendor/tooltipster/tooltipster.bundle.min.css" />
<link rel="stylesheet" href="/_new-codebase/front/vendor/tooltipster/tooltipster-sideTip-shadow.min.css" />

<script src="<?=$config['url'];?>js/main.js"></script>

<script src="<?=$config['url'];?>notifier/js/index.js"></script>
<link rel="stylesheet" type="text/css" href="<?=$config['url'];?>notifier/css/style.css">
<link rel="stylesheet" href="/_new-codebase/front/vendor/animate.min.css" />
<script src='/_new-codebase/front/vendor/mustache/mustache.min.js'></script>
<script src="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.concat.min.js"></script>
<link rel="stylesheet" href="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.min.css" />

<script  src="/_new-codebase/front/vendor/datatables/1.10.12/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?=$config['url'];?>css/datatables.css">
<script src="/_new-codebase/front/vendor/jquery.inputmask.bundle.min.js"></script>
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

 $('.phone_mask').inputmask({"mask": "+7 (999) 999-9999"}); //specifying options

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

    $('input[name="visual[]"]').on('change', function() {
        //alert($(this).data('group'));
        if ($(this).data('group') == 'new') {
            $('input[data-group="old"]').prop('checked', false).trigger('refresh');
        } else if ($(this).data('group') == 'old') {
           $('input[data-group="new"]').prop('checked', false).trigger('refresh');
        }



    });

    $('input[name="complex[]"]').on('change', function() {
        //alert($(this).data('group'));
        if ($(this).data('group') == 'full') {
            $('input[data-group="part"]').prop('checked', false).trigger('refresh');
        } else if ($(this).data('group') == 'part') {
           $('input[data-group="full"]').prop('checked', false).trigger('refresh');
        }



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
           <h2>Добавление карточки ремонта</h2>


      <div class="progress" style="margin-bottom: 20px;margin-top:15px;">
           <span>Проверка</span>
           <span class="current">Приемка</span>
            <?php if (!User::hasRole('taker')) { ?>
 <span >Ремонт</span>
  <?php } ?>
           <span>Запчасти</span>
           <span>Фото и видео</span>
           <span>Акты</span>
      </div>

  <form id="send" method="POST" class="repair_form validate_form">
   <div class="adm-form" style="padding-top:0;">

               <!--   <div class="item">
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
   <br><br>   -->


  <div id="tab-1" class="tab-content current">

                 <br> <h3>Информация о клиенте:</h3>

                 <div class="item">
              <div class="level">Клиент:</div>
              <div class="value">
                <input placeholder="Фамилия, Имя, Отчество" type="text" name="client" value="<?=$content['client'];?>"  />
              </div>
            </div>

                    <div class="item">
              <div class="level">Тип клиента:</div>
              <div class="value">
              <select name="client_type">
               <option value="">Выберите тип клиента</option>
               <option value="1" <?php if ($content['client_type'] == 1) { echo 'selected';}?>>Потребитель</option>
               <option value="2" <?php if ($content['client_type'] == 2) { echo 'selected';}?>>Магазин</option>
              </select>
              </div>
            </div>

                    <div class="item">
              <div class="level">Город:</div>
              <div class="value">
                <input type="text" name="city" value="<?=$content['city'];?>"  />
              </div>
            </div>

                    <div class="item">
              <div class="level">Адрес:</div>
              <div class="value">
                <input type="text" name="address" value="<?=$content['address'];?>"  />
              </div>
            </div>

                    <div class="item">
              <div class="level">Телефон</div>
              <div class="value">
                <input type="text" name="phone" class="phone_mask" value="<?=$content['phone'];?>"  />
              </div>
            </div>

           <br><br> <h3>Информация о продавце техники:</h3>

                    <div class="item">
              <div class="level">Наименование торговой организации</div>
              <div class="value">
                <input type="text" name="name_shop" value="<?=$content['name_shop'];?>"  />
              </div>
            </div>

                    <div class="item">
              <div class="level">Город:</div>
              <div class="value">
                <input type="text" name="city_shop" value="<?=$content['city_shop'];?>"  />
              </div>
            </div>

                    <div class="item">
              <div class="level">Адрес:</div>
              <div class="value">
                <input type="text" name="address_shop" value="<?=$content['address_shop'];?>"  />
              </div>
            </div>

                    <div class="item">
              <div class="level">Телефон</div>
              <div class="value">
                <input type="text" name="phone_shop" value="<?=$content['phone_shop'];?>"  />
              </div>
            </div>

<br><br> <h3>Информация о модели:</h3>

                    <div class="item">
              <div class="level">Модель:</div>
              <div class="value">
              <select name="model_id" class="select2 nomenu">
               <option value="">Выберите вариант</option>
               <?=models();?>
              </select>
              </div>
            </div>

                    <div class="item">
              <div class="level">Гарантийный талон:</div>
              <div class="value">
              <select name="talon" >
               <option value="">Выберите</option>
               <option value="" >Гарантийный талон</option>
               <option value="" >Чек</option>
               <option value="" >Гарантийный талон+Чек</option>
              </select>
              </div>
            </div>

                    <div class="item">
              <div class="level">Серийный номер:</div>
              <div class="value">
                <input type="text" name="serial" class="serial_check" value="<?=$content['serial'];?>"  />
              </div>
            </div>

                    <div class="item">
              <div class="level">Статус ремонта:</div>
              <div class="value">
              <select name="status_id">
               <option value="">Выберите статус</option>
               <option value="1" <?php if ($content['status_id'] == 1) { echo 'selected';}?>>Гарантийный</option>
               <option value="3" <?php if ($content['status_id'] == 3) { echo 'selected';}?>>Повторный</option>
               <option value="5" <?php if ($content['status_id'] == 5) { echo 'selected';}?>>Условно-гарантийный</option>
              </select>
              </div>
            </div>

                    <div class="item">
              <div class="level">Дата продажи:</div>
              <div class="value">
                              <input class="datepicker metro-skin" type="text" name="date" value="<?=$content['sell_date'];?>"  />
              </div>
            </div>

            <br>
            <br>


            <h3>Комплектация:</h3>
                           <div class="adm-finish" style="padding-top:0px;">
            <ul>
              <li><label><input type="checkbox" name="complex[]" class="termsCheck" value="ПОЛНАЯ" data-group="full"/>ПОЛНАЯ</label></li>
              <li><label><input type="checkbox" name="complex[]" class="termsCheck" value="КОР" data-group="part"/>КОР</label></li>
              <li><label><input type="checkbox" name="complex[]" class="termsCheck" value="АПП" data-group="part"/>АПП</label></li>
              <li><label><input type="checkbox" name="complex[]" class="termsCheck" value="ГАР ТАЛ" data-group="part"/>ГАР ТАЛ</label></li>
              <li><label><input type="checkbox" name="complex[]" class="termsCheck" value="ЧЕК" data-group="part"/>ЧЕК</label></li>
              <li><label><input type="checkbox" name="complex[]" class="termsCheck" value="ПДУ" data-group="part"/>ПДУ</label></li>
              <li><label><input type="checkbox" name="complex[]" class="termsCheck" value="НОЖКИ" data-group="part"/>НОЖКИ</label></li>
              <li><label><input type="checkbox" name="complex[]" class="termsCheck" value="ПОДСТАВКА" data-group="part"/>ПОДСТАВКА</label></li>
            </ul>
            </div>

                  <div class="item">
              <div class="level">Дополнение к комплектации:</div>
              <div class="value">
                <input type="text" name="complex[]" value=""  />
              </div>
            </div>

                <br>
            <br>
                <h3>Внешний вид:</h3>
                            <div class="adm-finish" style="padding-top:0px;">
            <ul>
              <li><label><input type="checkbox" name="visual[]" class="termsCheck" value="НОВЫЙ" data-group="new"/>НОВЫЙ</label></li>
              <li><label><input type="checkbox" name="visual[]" class="termsCheck" value="Б/У" data-group="old"/>Б/У</label></li>
              <li><label><input type="checkbox" name="visual[]" class="termsCheck" value="ЦАРАПИНЫ" data-group="old"/>ЦАРАПИНЫ</label></li>
            </ul>
            </div>

                  <br><br>
              <hr>

                     <div class="item">
              <div class="level">Неисправность, со слов клиента:</div>
              <div class="value">
                              <input type="text" name="bugs" value="<?=$content['bugs'];?>"  />
              </div>
            </div>

                    <div class="item">
              <div class="level">Примечание:</div>
              <div class="value">
                              <input type="text" name="comment" value="<?=$content['comment'];?>"  />
              </div>
            </div>

              </div>





                <div class="adm-finish">
            <div class="save">
              <input type="hidden" name="send" value="1" />
              <input type="hidden" name="model_id_check" value="">
              <button type="submit" >Сохранить</button>
            </div>
            </div>
        </div>

      </form>




        </div>
  </div>
</div>
</body>
</html>