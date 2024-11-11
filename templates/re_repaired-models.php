<?php

use models\Repeats;
use models\repeats\ExportExcel;
use models\staff\Staff;
use program\core;
use program\core\App;

if(!empty(App::$URLParams['action'])){
  switch(App::$URLParams['action']){
    case 'export-excel':
      ExportExcel::run();
      exit;
  }
}


$repeats = Repeats::getRepeats(App::$URLParams);
$groupBy = (empty(App::$URLParams['group-by']) || App::$URLParams['group-by'] == 'cats') ? 'cats' : 'models';



if ($_GET['date1'] || $_GET['date12'] || $_GET['date13'] || $_GET['date14']) {
$display = 'display:block;';
} else {
$display = 'display:none;';
}


if ($_GET['ajaxed'] == 1) {

exit;
} else {

if (isset($_COOKIE['master_id'])) {
    unset($_COOKIE['master_id']);
    setcookie('master_id', '', time() - 3600, '/'); // empty value and old timestamp
}
/*if (isset($_COOKIE['model_id'])) {
    unset($_COOKIE['model_id']);
    setcookie('model_id', '', time() - 3600, '/'); // empty value and old timestamp
}
if (isset($_COOKIE['client_id'])) {
    unset($_COOKIE['client_id']);
    setcookie('client_id', '', time() - 3600, '/'); // empty value and old timestamp
}  */

}



function check_serial($serial, $id) {
  global $config, $db;
$sql = mysqli_query($db, 'SELECT COUNT(*) FROM `serials` WHERE `serial` = \''.mysqli_real_escape_string($db, $serial).'\'  and `model_id` = '.$id);
$count = mysqli_fetch_array($sql)['COUNT(*)'];
if ($count > 0) {
return true;
} else {
return false;
}
}


function models($cat_id) {
  global $db;
$content = array();
$sql = mysqli_query($db, 'SELECT * FROM `models`;');
      while ($row = mysqli_fetch_array($sql)) {
      if ($cat_id == $row['id'] || $_COOKIE['model_id'] == $row['id']) {
      $content .= '<option selected value="'.$row['id'].'">'.$row['name'].'</option>';
      } else {
       $content .= '<option value="'.$row['id'].'">'.$row['name'].'</option>';
      }
      }
    return $content;
}

function clients($cat_id) {
  global $db;
$content = array();
$sql = mysqli_query($db, 'SELECT * FROM `clients` group by `name` order by `name` asc;');
      while ($row = mysqli_fetch_array($sql)) {
      if ($cat_id == $row['id']  || $_COOKIE['client_id'] == $row['id']) {
      $content .= '<option selected value="'.$row['id'].'">'.$row['name'].'</option>';
      } else {
       $content .= '<option value="'.$row['id'].'">'.$row['name'].'</option>';
      }
      }
    return $content;
}


function master_select() {
  $content = '';
$masters = Staff::getMasters();
      foreach ($masters as $row) {
      if ($_GET['master_id'] == $row['id'] || $_COOKIE['master_id'] == $row['id']) {
      $content .= '<option selected value="'.$row['id'].'">'.$row['surname'].' '.$row['name'].' '.$row['thirdname'].'</option>';
      } else {
       $content .= '<option value="'.$row['id'].'">'.$row['surname'].' '.$row['name'].' '.$row['thirdname'].'</option>';
      }
      }
    return $content;
}

function masters_list($cat_id) {
  $content = '';

$masters = Staff::getMasters();
      foreach ($masters as $row) {
      if ($cat_id == $row['id']) {
      $content .= '<option selected value="'.$row['id'].'" title="'.$row['surname'].' '.$row['name'].' '.$row['thirdname'].'">'.$row['surname'].' '.$row['name'].' '.$row['third_name'].'</option>';
      } else {
       $content .= '<option value="'.$row['id'].'">'.$row['surname'].' '.$row['name'].' '.$row['thirdname'].'</option>';
      }
      }
    return $content;
}

function check_doubl_ffs($serial, $id = '') {
  global $config, $db;
  if(empty($serial)){
    return false;
  }
$sql = mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` WHERE `serial` = \''.mysqli_real_escape_string($db, $serial).'\' and `model_id` = '.$id.' AND `deleted` = 0;');
$count = mysqli_fetch_array($sql)['COUNT(*)'];
if ($count > 1) {

return true;

} else {
return false;
}
}

function check_status() {
  global $db;
$sql = mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` where `deleted` != 1 and `status_admin` != \'Подтвержден\' and `status_admin` != \'\'  and `status_admin_read` = 1  order by `id` DESC;');
return mysqli_fetch_array($sql)['COUNT(*)'];
}


function parts_price($id) {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `repairs_work` where `repair_id` = '.$id.';');
$row = @mysqli_fetch_array($sql)['sum'];
$sum = ($row) ? $row : 0;
return $sum;
}

function check_status_user() {
  global $db;
$sql = mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` where `status_user_read` = 1 and `deleted` != 1 and `service_id` = '.\models\User::getData('id').' and `status_admin` != \'\' and `status_admin` != \'Подтвержден\' ;');
return mysqli_fetch_array($sql)['COUNT(*)'];
}

function get_request_info_serice($id) {
  global $db;
$req = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `requests` WHERE `user_id` = '.$id));
return $req;
}

function check_complete($id) {
    global $db;
$req = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` WHERE
`model_id` != \'\' and
`status_id` != \'\' and
`bugs` != \'\' and
`begin_date` != "0000-00-00" and
`master_id` != \'\' and
`disease` != \'\' and
`id` = '.$id));
if ($req['COUNT(*)'] == 0) {
return false;
} else {
return true;
}
}

$title = 'Повторные ремонты по моделям';
if($groupBy == 'cats'){
  $title = 'Повторные ремонты по категориям';
}

?>
<!doctype html>
<html>
<head>
<meta charset=utf-8>
<title><?= $title; ?> - Панель управления</title>
<link href="/css/fonts.css" rel="stylesheet" />
<link href="/css/style.css" rel="stylesheet" />
<script src="/_new-codebase/front/vendor/jquery/jquery-1.7.2.min.js"  ></script>
<script src="/js/jquery-ui.min.js"></script>
<script src="/js/jquery.placeholder.min.js"></script>
<script src="/js/jquery.formstyler.min.js"></script>
<script src="/js/main.js"></script>
<script src="/notifier/js/index.js"></script>
<script src="/_new-codebase/front/vendor/tooltipster/tooltipster.bundle.min.js"></script>
<script src='/_new-codebase/front/vendor/mustache/mustache.min.js'></script>
<script src="/_new-codebase/front/vendor/js.cookie-2.2.0.min.js"></script>
<script src="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.concat.min.js"></script>
<link rel="stylesheet" href="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.min.css" />
<link rel="stylesheet" href="/_new-codebase/front/vendor/tooltipster/tooltipster.bundle.min.css" />
<link rel="stylesheet" href="/_new-codebase/front/vendor/tooltipster/tooltipster-sideTip-shadow.min.css" />
<link rel="stylesheet" href="/_new-codebase/front/vendor/animate.min.css" />
<script  src="/_new-codebase/front/vendor/datatables/1.10.19/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="/css/datatables.css">
<link rel="stylesheet" href="/_new-codebase/front/vendor/select2/4.0.4/select2.min.css" />
<script src="/_new-codebase/front/vendor/select2/4.0.4/select2.full.min.js"></script>
<script  src="/_new-codebase/front/vendor/datatables/2.1.1/dataTables.responsive.min.js"></script>
<link rel="stylesheet" type="text/css" href="/_new-codebase/front/vendor/datatables/2.1.1/responsive.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="/notifier/css/style.css">
<link rel="stylesheet" type="text/css" href="/js/daterangepicker.css">
<script src="/js/moment.min.js"></script>
<script src="/js/jquery.daterangepicker.js"></script>
<link rel="stylesheet" href="/_new-codebase/front/vendor/fancybox/3.5.2/jquery.fancybox.min.css" />
<script src="/_new-codebase/front/vendor/fancybox/3.5.2/jquery.fancybox.min.js"></script>
<script src="/_new-codebase/front/vendor/chart-js/chart.bundle.min.js"></script>
<script src="/_new-codebase/front/vendor/chart-js/utils.js"></script>
<style>
.ui-selectmenu-button:after {
    display: none;
}
.ui-selectmenu-button {
    width: 160px;
    font-size: 15px;
}

table.dataTable.dtr-inline.collapsed>tbody>tr>td:first-child:before, table.dataTable.dtr-inline.collapsed>tbody>tr>th:first-child:before {
    top: auto;

    }
table.dataTable thead .sorting {
    font-size: 16px;
}
table.dataTable.row-border tbody tr:first-child th, table.dataTable.row-border tbody tr:first-child td, table.dataTable.display tbody tr:first-child th, table.dataTable.display tbody tr:first-child td {
   /* font-size: 16px;    */
}
.master_change .ui-selectmenu-button {
display:none;
position:absolute;
z-index: 999;
}

</style>
<style>
.date-picker-wrapper .drp_top-bar .apply-btn.disabled {
width: auto !important;
}
.date-picker-wrapper .drp_top-bar .apply-btn {
width: auto !important;
}
.redactor-editor {
text-align: left;
}
.ui-datepicker td a.ui-state-highlight {
    color:#fff;
}
#ui-datepicker-div {
border: 1px solid #ccc;
}
.dataTables_wrapper .dataTables_processing {
position: absolute;
top: 30%;
left: 50%;
width: 30%;
height: 40px;
color:#fff;
margin-left: -15%;
margin-top: -25px;
padding-top: 20px;
text-align: center;
font-size: 1.2em;
background:#77ad07;
z-index:999999999999;
}
.green {
background: rgb(178, 255, 102) !important;
}
.grey {
background: rgba(184, 183, 184, 0.85) !important;
}
.darkgreen {
background: rgb(178, 255, 102)  !important;
}
.yellow {
        background: rgba(242, 242, 68, 0.45) !important;
}
.red {
        background: rgba(245, 87, 81, 0.45) !important;
}
.blue {
        background: rgba(107, 218, 255, 0.45) !important;
}
.orange {
background: rgba(255, 153, 51, 0.4) !important;
}
.redone {
background: rgba(153, 51, 51, 0.4)!important;
}
table.dataTable tbody th, table.dataTable tbody td {
    padding: 8px 18px !important;
}
</style>
<script >
// Таблица
$(document).ready(function() {

  $('#two-inputs').dateRangePicker(
  {
    separator : ' to ',
    getValue: function()
    {
      if ($('#date-range200').val() && $('#date-range201').val() )
        return $('#date-range200').val() + ' to ' + $('#date-range201').val();
      else
        return '';
    },
    setValue: function(s,s1,s2)
    {
      $('#date-range200').val(s1);
      $('#date-range201').val(s2);
    }
  });



var checked = false;
$('#select_all').click(function() {
    if (checked) {
        $(':checkbox').each(function() {
            $(this).prop('checked', false).trigger('refresh');
        });
        checked = false;
    } else {
        $(':checkbox').each(function() {
            $(this).prop('checked', true).trigger('refresh');
        });
        checked = true;
    }
    return false;
});

 $('.need_work').tooltipster({
                              trigger: 'hover',
                              position: 'top',
                              animation: 'grow',
                              theme: 'tooltipster-shadow'
                          });

    var groupColumn = 1;

   $.fn.dataTable.moment( 'dd.mm.YYYY' );
   var groupColumn = 0;
   var table = $('#table_content2').DataTable({
      "bStateSave":false,
      "responsive": true,
      "dom": '<"top"flp<"clear">>rt<"bottom"ip<"clear">>',
      "pageLength": 30,
      <?php
      if($groupBy == 'models'): ?>
      "columnDefs": [
            { "visible": false, "targets": groupColumn },
            { "width": "14%", "targets": 1 }
        ],
        "order": [[ groupColumn, 'asc' ]],
        "drawCallback": function ( settings ) {
            var api = this.api();
            var rows = api.rows( {page:'current'} ).nodes();
            var last=null;

            api.column(groupColumn, {page:'current'} ).data().each( function ( group, i ) {
                if ( last !== group ) {
                    $(rows).eq( i ).before(
                        '<tr class="group" id="removepls"><td colspan="21">'+group+'</td></tr>'
                    );

                    last = group;
                }
            } );
        },
        <?php else: ?> 
          "columnDefs": [
            { "width": "16%", "targets": 0 }
        ],
        <?php endif; ?> 
      "oLanguage": {
            "sLengthMenu": "Показывать _MENU_ записей на страницу",
            "sZeroRecords": "Записей нет.",
            "sInfo": "Показано от _START_ до _END_ из _TOTAL_ записей",
            "sInfoEmpty": "Записей нет.",
            "sProcessing": "Загружаются данные...",
            "oPaginate": {
                 "sFirst": "Первая",
                 "sLast": "Последняя",
                 "sNext": "Следующая",
                 "sPrevious": "Предыдущая",
                },
            "sSearch": "Поиск",
            "sInfoFiltered": "(отфильтровано из _MAX_ записи/(ей)"
        }
   });

   /* Диаграмма */
   <?php $diagData = getDiagramData($repeats); ?>
   var randomScalingFactor = function() {
        return Math.round(Math.random() * 100);
    };
    var config = {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [
                  <?= $diagData['sum']; ?>
                ],
                backgroundColor: [
                    window.chartColors.red,
                    window.chartColors.green,
                    window.chartColors.blue,
                ]
            }],
            labels: [
              <?= $diagData['labels']; ?>
            ]
        },
        options: { 
            interaction: {
              intersect: false
            },
            responsive: true,
            legend: {
                position: 'top',
                display: false
            },
            title: {
                display: true,
                text: 'Стоимость повторных ремонтов'
            },
            animation: {
                animateScale: true,
                animateRotate: true
            },
            aspectRatio: '1.5'
        }
    }; 
    var config2 = {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [
                  <?= $diagData['cnt']; ?>
                ],
                backgroundColor: [
                    window.chartColors.red,
                    window.chartColors.green,
                    window.chartColors.blue,
                ]
            }],
            labels: [
              <?= $diagData['labels']; ?>
            ]
        },
        options: { 
            interaction: {
              intersect: false
            },
            responsive: true,
            legend: {
                position: 'top',
                display: false
            },
            title: {
                display: true,
                text: 'Количество повторных ремонтов'
            },
            animation: {
                animateScale: true,
                animateRotate: true
            },
            aspectRatio: '1.5'
        }
    };


        var ctx = document.getElementById("diagr-sum").getContext("2d");
        window.myDoughnut = new Chart(ctx, config);
        var ctx2 = document.getElementById("diagr-cnt").getContext("2d");
        window.myDoughnut = new Chart(ctx2, config2);

    /* / Диаграмма */


    $(document).on('selectmenuchange', '#table_content select[name=status_admin]', function() {
        var value = $(this).val();
        var id= $(this).data('repair-id');
        var app_date= $(this).data('app-date');
        if (app_date) {
        var date_app_date = $.datepicker.parseDate('yy.mm.dd', app_date);
        }
        var now = new Date();
              if (value) {

                 if (app_date) {

                if (date_app_date.getMonth() == now.getMonth()) {
                $.get( "/ajax.php?type=update_repair_status&value="+encodeURIComponent(value)+"&id="+id, function( data ) {

                  });
                }  else {

                var isGood=confirm("ВЫ УВЕРЕНЫ, ЧТО ХОТИТЕ ИЗМЕНИТЬ СТАТУС ДАННОГО РЕМОНТА, КОТОРЫЙ НАХОДИТСЯ В ПРОШЛОМ ПЛАТЁЖНОМ ПЕРИОДЕ?");
                if (isGood) {
                  $.get( "/ajax.php?type=update_repair_status&value="+encodeURIComponent(value)+"&id="+id, function( data ) {

                  });
                } else {
                  //alert('false');
                }



                }

                 } else {

                    $.get( "/ajax.php?type=update_repair_status&value="+encodeURIComponent(value)+"&id="+id, function( data ) {

                    });

                 }


              }


        return false;
    });

$('.select2').select2({ width: 'resolve' });

$( ".datepicker2" ).datepicker({
  dateFormat: 'yy.mm.dd',
    onSelect: function(dateText, inst) {
        var date = $(this).val();
        var id = $(this).data('id');


                  $.get( "/ajax.php?type=update_repair_appdate&value="+date+"&id="+id, function( data ) {

                  });



    } ,
     beforeShow: function(input, inst) {
       $('#ui-datepicker-div').addClass("ll-skin-cangas");
   }
});



    $(document).on('click', '.dates_filter', function() {
        $('.dates_block').slideToggle( "slow" );
        return false;
    });

     $(document).on('click', '.master_filter', function() {
        $('.master_block').slideToggle( "slow" );
        return false;
    });

    $(document).on('click', '.gen_nak', function() {
    var ids = [];
    $('.download_mass:checked').each(function(){
          ids.push($(this).data('id'));
          //$('[data-remodal-id=modal]').remodal().close();
          //$('[data-remodal-id=modal2]').remodal().open();
    });

          //console.log(ids);
         // $.get('/?query=create_super_nak&value='+JSON.stringify(ids));
          $(location).attr('href','/?query=create_super_nak&value='+JSON.stringify(ids));
          return false;
    });

    $(document).on('click', '.gen_kvit', function() {
    var ids = [];
    $('.download_mass:checked').each(function(){
          ids.push($(this).data('id'));
          //$('[data-remodal-id=modal]').remodal().close();
          //$('[data-remodal-id=modal2]').remodal().open();
    });

          //console.log(ids);
         // $.get('/?query=create_super_nak&value='+JSON.stringify(ids));
          $(location).attr('href','/?query=create_super_kvit&value='+JSON.stringify(ids));
          return false;
    });

    $(document).on('click', '.sub_change', function(e) {
     e.preventDefault();
    var ids = [];
    var form = $('.sub_change_form');
    var master_id = form.find('select[name="master_id"]').val();
    var status_admin = form.find('select[name="status_admin"]').val();
    $('.download_mass:checked').each(function(){
          ids.push($(this).data('id'));
          //ids.push($(this).data('id'));

          //$('[data-remodal-id=modal]').remodal().close();
          //$('[data-remodal-id=modal2]').remodal().open();
    });

          console.log(ids);
         // $.get('/?query=create_super_nak&value='+JSON.stringify(ids));
          //$(location).attr('href','/?query=create_super_nak&value='+JSON.stringify(ids));

          $.get( "/ajax.php?type=mass_update&status_admin="+encodeURIComponent(status_admin)+"&master_id="+master_id+"&value="+JSON.stringify(ids), function( data ) {
           $(location).attr('href','/dashboard/');
          });

          return false;
    });


} );

$.datepicker.setDefaults( $.datepicker.regional[ "ru" ] );

     $(document).on('keypress keyup search input paste cut change', '#table_content_filter input[type=search]', function() {
        var value = $(this).val();
        var id= $(this).data('id');

              Cookies.set('search', value, { expires: 7 });

    });



$(window).on('load', function() {
       if (Cookies.get('search') && Cookies.get('search') != '') {
    //table.column( 18 ).search( Cookies.get('search') );
  //fnFilter
   $('#table_content_filter input[type=search]').val(Cookies.get('search')).trigger('input');
    }
});
</script>
<script src="/_new-codebase/front/vendor/moment.js/moment.min.js"></script>
<script src="/_new-codebase/front/vendor/moment.js/datetime-moment.js"></script>
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
tr.group,
tr.group:hover {
    background: #77ad07;
    background-color: #77ad07 !important;
    color: #fff;
}
#removepls td:before{
display:none;

}
#removepls td{
padding-left:10px;
}
.yellow {
        background: rgba(242, 242, 68, 0.45) !important;
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

      <?php if ($_SESSION['adminer'] == 1) { ?>
      <a href="/login-like/1/">service2</a> <span style="color:#fff;">-></span> <span style="color:#fff;"><?=\models\User::getData('login');?></span>
      <?php } else {  ?>
      <a href="/logout/">Выйти, <?=\models\User::getData('login');?></a>
      <?php } ?>

    </div>

  </div>
</div><!-- .site-header -->

<div class="wrapper">

<?=top_menu_admin();?>

  <div class="adm-tab">

  <?=menu_dash();?>

  </div><!-- .adm-tab -->
           <br>
           <h2><?= $title; ?></h2>
                        <br>
  <div class="adm-catalog">
      <div style="vertical-align:middle;    padding-bottom: 15px;   font-size: 16px;position:relative; ">


      <?php if (\models\User::hasRole('slave-admin', 'taker')) { ?> 

                   <div class="add" style="padding-top: 0px;
    display: inline-block;
    position: absolute;
    top: -15px;
    right: 0px;display:none;">
    <a href="#" id="select_all" style="margin-right:10px;">Выбрать все</a> <a style="width: auto;padding-left: 7px;padding-right: 7px;background:#83b41c;color:#fff;vertical-align: middle;" href="#" class="gen_nak button">Объединить наклейки</a> <a style="width: auto;padding-left: 7px;padding-right: 7px;background:#83b41c;color:#fff;vertical-align: middle;" href="#" class="gen_kvit button">Объединить квитанции</a>
    </div>

<div class="adm-widget" style="margin-top: 0px;">


    <div class="block">

      <script>
        $(document).ready(function(){
          $('.adm-switch-1 .slider').slider({
            animate: false,
            range: false,
            min: 1,
            max: 3,
            value: <?=($_GET['type_id'] == 1 || $_GET['type_id'] == 3) ? $_GET['type_id'] : 2?>,
            step: 1,
            slide: function( event, ui ) {
             // console.log(ui.value);
            window.location.href = "https://crm.r97.ru/dashboard/?type_id="+ui.value;

            }
          });


        });
      </script>
      <style>
      .adm-widget .act .adm-switch {
    display: inline-block;
    vertical-align: middle;
    margin: 0 25px;
    width: 190px !Important;
    }
    .adm-widget .act .txt {
    position: absolute;
    /* left: 422px; */
    display: inline-block;
       bottom: 18px;
    /* margin-bottom: -30px; */
}
.adm-widget .act .txt-l {
    left:1px;
}
.adm-widget .act .txt-c {
    left:108px;
}
.adm-widget .act .txt-r {
    left:158px;
    right:auto !important;
}
.adm-widget .act {

    width: 438px;
}
.select2-container {
  width: 200px;
}


#models-stat-table tr{
  border-bottom: solid 1px #eee;
}

#models-stat-table td, #models-stat-table th{
  padding: .5em;
}

.dates_block{
  display: flex;
    align-items: center;
}

.canvas-holder{
  width: 605px;   
  display: inline-block;
  margin: 32px;
}
@media (min-width: 1920px) {
  .canvas-holder{
  width: 680px;   
}
}

.canvas-legend{
margin-top: 16px;
}

.canvas-legend-item{
margin-top: 4px;
display: flex;
align-items: center;
}

.canvas-legend-item::before{
content: '';
display: inline-block;
width: 24px;
height: 24px;
background-color: #eee;
margin-right: 8px;
}

.canvas-legend-item_1::before{
  background-color: rgb(255, 99, 132);
}

.canvas-legend-item_2::before{
  background-color: rgb(75, 192, 192);
}

.canvas-legend-item_3::before{
  background-color: rgb(54, 162, 235);
}

      </style>
      <table style="display:none;    width: 100%;margin-top:20px;">
         <tr>  <td style="vertical-align: top;max-width:240px;">
      <div class="act">
        <div class="level" style="    display: block;    width: 100%;">Тип клиента</div>
        <input id="landing" type="hidden" value="1" name="landing">
        <div class="adm-switch adm-switch-1" style="width: 82px;z-index: 9;">
          <div class="slider">
            <div class="inner"></div>
          </div>
        </div>
        <div class="txt txt-l">Потребитель</div>
        <div class="txt txt-c">Все</div>
        <div class="txt txt-r">Магазин</div>


      </div> </td>
               <td style="z-index:999999;">
                 <table>
                   <tr>
                     <td style="vertical-align: top;"> <form method="GET" style="padding-top:3px;">
    <div style="display:inline-block;"><span style="display: inline-block;">Модель </span>&nbsp;&nbsp;<select style="width: 200px;display: inline-block;" class="select2 nomenu" name="search_model_id"><option value="">Выберите модель</option><?=models();?></select></div>
    </form></td>
                   </tr>
                   <tr>
                     <td style="vertical-align: top;"> <form method="GET" style="padding-top:3px;">
    <div style="display:inline-block;"><span style="display: inline-block;margin-right: 1px;">&nbsp;Клиент </span>&nbsp;&nbsp;<select style="width: 200px;display: inline-block;" class="select2 nomenu" name="search_client_id"><option value="">Выберите клиента</option><?=clients();?></select></div>
    </form></td>
                   </tr>
                 </table>
               </td>
              <td style="vertical-align: top;     max-width: 545px;  ">
             <form method="GET" style="padding-top:3px;">
    <div style="display:inline-block;"><span style="display: inline-block;">Фильтр по мастеру </span>&nbsp;&nbsp;<select class="select2 nomenu" name="master_id"><option value="">Выберите мастера</option><?=master_select();?></select><input class="green_button" type="submit" style="      background: #80bd03;display: inline-block;     margin-left: 15px;     vertical-align: middle;     height: 54px;     margin-top: -4px;" value="Применить" /></div>
    </form>
    </td>
      </tr>
     </table>
</div>




</div>

      <?php } else { ?>

      <!--Показать: <a <?=($_SERVER['REQUEST_URI'] == '/dashboard/?target=all') ? 'style="font-weight:bold;"' : '';?> href="?target=all">Все</a> /
      <a <?=($_SERVER['REQUEST_URI'] == '/dashboard/?target=inwork') ? 'style="font-weight:bold;"' : '';?> href="/dashboard/?target=inwork">Принятые</a> /
      <a <?=($_SERVER['REQUEST_URI'] == '/dashboard/?target=approve') ? 'style="font-weight:bold;"' : '';?> href="?target=approve">Подтвержденные</a> /
      <a <?=($_SERVER['REQUEST_URI'] == '/dashboard/?target=needparts') ? 'style="font-weight:bold;"' : '';?> href="?target=needparts">Нужны запчасти</a> /
      <a <?=($_SERVER['REQUEST_URI'] == '/dashboard/?target=partsintransit') ? 'style="font-weight:bold;"' : '';?> href="?target=partsintransit">Запчасти в пути</a> /
      <a <?=($_SERVER['REQUEST_URI'] == '/dashboard/?target=cancelled') ? 'style="font-weight:bold;"' : '';?> href="?target=cancelled">Отклоненные</a> /
      <a <?=($_SERVER['REQUEST_URI'] == '/dashboard/?target=courier') ? 'style="font-weight:bold;"' : '';?> href="?target=courier">C выездными ремонтами</a>
     -->  <?php }  ?>
       <!--<div style="float:right;text-align:right"><a href="#"  class="dates_filter">Фильтр по датам &#9660;</a><br>
      <a href="#" class="master_filter">Массовое изменение ремонтов &#9660;</a></div>

      </div>  -->

    <form method="GET" class="dates_block">
        <div>
          Дата подтверждения
          <span id="two-inputs"> 
          от <input type="text" id="date-range200" name="date1" value="<?= (!empty(core\App::$URLParams['date1'])) ? core\App::$URLParams['date1'] : ''; ?>" style="width:150px"> 
          до <input name="date2"  type="text" id="date-range201" value="<?= (!empty(core\App::$URLParams['date2'])) ? core\App::$URLParams['date2'] : ''; ?>" style="width:150px"></span>
        </div>

        <div style="margin-left: 32px">
          <select name="group-by">
            <option value="models" <?= ($groupBy == 'models') ? 'selected' : '' ; ?>>По моделям</option>
            <option value="cats" <?= ($groupBy == 'cats') ? 'selected' : '' ; ?>>По категориям</option>
          </select>
        </div>

        <div>
          <input class="green_button" type="submit" style="display: inline-block;margin-left:15px;  vertical-align: middle;    height: 52px;    margin-top: -4px;" value="Применить" />
        </div>
   </form>


     <div style="vertical-align:middle;position:relative">

      <div style="float: right;margin-top:-50px">
     <a style="width: auto;padding: 0 7px;" href="?action=export-excel&<?= parse_url($_SERVER['REQUEST_URI'])['query']; ?>" class="button">Выгрузить в Excel</a>
    </div>


    </div>

    <br><br>
    <div class="canvas-holder">
    <div id="canvas-holder">
            <canvas id="diagr-sum" />
            </div>
            <div class="canvas-legend">
        <?php
          $i = 1;
          $list = explode(',', $diagData['labels']);
          foreach($list as $label){
            echo '<div class="canvas-legend-item canvas-legend-item_'.$i.'">'.trim($label, '"').'</div>';
            $i++;
          }
        ?>
        </div> 
    </div>
    <div class="canvas-holder">
    <div id="canvas-holder">
            <canvas id="diagr-cnt" />
            </div>
            <div class="canvas-legend">
        <?php
          $i = 1;
          foreach($list as $label){
            echo '<div class="canvas-legend-item canvas-legend-item_'.$i.'">'.trim($label, '"').'</div>';
            $i++;
          }
        ?>
        </div> 
    </div>


    <table id="table_content2" class="display" cellspacing="0" width="100%" style="font-size: 16px;" id="models-stat-table">
    <thead>
            <tr>
                <?php
if($groupBy == 'cats'){
  echo '<th align="left" style="width: 60%">Категория</th>';
}else{
  echo '<th align="left" style="width: 30%">Категория</th>
  <th align="left" style="width: 30%">Модель</th>';
}
?>       
                <th align="left" style="width: 5%">Повторные ремонты</th>
                <th align="left" style="width: 5%">Сумма</th>
                <th align="left" style="width: 5%">Утиль</th>
                <th align="left" style="width: 5%">Сумма</th>
                <th align="left" style="width: 5%">Возврат клиенту</th>
                <th align="left" style="width: 5%">Сумма</th>
                <th align="left" style="width: 5%">Ремонт и передача на уценку</th>
                <th align="left" style="width: 5%;">Сумма</th>
            </tr>
        </thead>
        <tbody>
      <?= getModelsStat($repeats); ?>
        </tbody>
</table>

</div>


        </div>
  </div>
</body>
</html>

<?php

function getDiagramData(array $data)
{
  $capts = ['Утиль', 'Возврат клиенту', 'Ремонт и передача на уценку'];
  $sum = ['discard' => 0, 'returns' => 0, 'markdown' => 0];
  $cnt = ['discard' => 0, 'returns' => 0, 'markdown' => 0];
  $keys = ['discard', 'returns', 'markdown'];
  foreach ($data as $cat) {
    foreach($keys as $k){
      $sum[$k] += $cat['stat'][$k]['sum'];
      $cnt[$k] += $cat['stat'][$k]['cnt'];
    }
  }
  return [
    'labels' => '"'.implode('","', $capts).'"',
    'sum' => implode(',', $sum),
    'cnt' => implode(',', $cnt),
  ];
}


function getModelsStat(array $data)
{
  $html = '';
  if (empty(App::$URLParams['group-by']) || App::$URLParams['group-by'] == 'cats') {
    foreach ($data as $cat) {
      $html .= '<tr>
      <td>' . $cat['cat'] . '</td>
      <td>' . $cat['stat']['total']['cnt'] . '</td>
      <td style="white-space:nowrap" data-order="'.$cat['stat']['total']['sum'].'">' . number_format($cat['stat']['total']['sum'], 0, ',', ' ') . ' ₽</td>
      <td>' . $cat['stat']['discard']['cnt'] . '</td>
      <td style="white-space:nowrap" data-order="'.$cat['stat']['discard']['sum'].'">' . number_format($cat['stat']['discard']['sum'], 0, ',', ' ') . ' ₽</td>
      <td>' . $cat['stat']['returns']['cnt'] . '</td>
      <td style="white-space:nowrap" data-order="'.$cat['stat']['returns']['sum'].'">' . number_format($cat['stat']['returns']['sum'], 0, ',', ' ') . ' ₽</td>
      <td>' . $cat['stat']['markdown']['cnt'] . '</td>
      <td style="white-space:nowrap" data-order="'.$cat['stat']['markdown']['sum'].'">' . number_format($cat['stat']['markdown']['sum'], 0, ',', ' ') . ' ₽</td>
      </tr>';
    }
  } else {
    foreach ($data as $cat) {
      foreach ($cat['models'] as $model) {
        $html .= '<tr>
      <td>' . $cat['cat'] . '</td>
      <td>' . $model['model'] . '</td>
      <td>' . $model['total']['cnt'] . '</td>
      <td style="white-space:nowrap" data-order="'.$model['total']['sum'].'">' . number_format($model['total']['sum'], 0, ',', ' ') . ' ₽</td>
      <td>' . $model['discard']['cnt'] . '</td>
      <td style="white-space:nowrap" data-order="'.$model['discard']['sum'].'">' . number_format($model['discard']['sum'], 0, ',', ' ') . ' ₽</td>
      <td>' . $model['returns']['cnt'] . '</td>
      <td style="white-space:nowrap" data-order="'.$model['returns']['sum'].'">' . number_format($model['returns']['sum'], 0, ',', ' ') . ' ₽</td>
      <td>' . $model['markdown']['cnt'] . '</td>
      <td style="white-space:nowrap" data-order="'.$model['markdown']['sum'].'">' . number_format($model['markdown']['sum'], 0, ',', ' ') . ' ₽</td>
      </tr>';
      }
    }
  }
  return $html;
}