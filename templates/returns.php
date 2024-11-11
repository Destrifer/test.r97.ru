<?php

use program\core;
require '_new-codebase/front/templates/main/parts/common.php';

//mysqli_query($db, 'UPDATE `configuration` SET `value` = 0 where `id` = 36');
function content_list() {
  global $db;
  $content_list = '';
  $contr = gen_contr();
$rus_months = array('Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь');
if (!\models\User::hasRole('admin', 'slave-admin', 'taker')) {
  return;
}
$usd = str_replace(',', '.', json_decode(@file_get_contents('https://www.cbr-xml-daily.ru/daily_json.js'))->Valute->USD->Value);

$pageNum = (isset(core\App::$URLParams['page'])) ? core\App::$URLParams['page'] : 1;
$dates = getDatesInterval($pageNum);
$d = mysqli_fetch_assoc(mysqli_query($db, 'SELECT `receive_date` FROM `returns` ORDER by `id` ASC LIMIT 1;'))['receive_date'];
$paginator = new core\Paginator(ceil(getMonthsCnt($d, date('Y-m-d')) / 3), 1, $pageNum);
$datesInterval = core\Time::formatVerbose($dates['from']) . ' - ' . core\Time::formatVerbose($dates['to']);

$sql = mysqli_query($db, 'SELECT ret.*, cl.`name` AS client_name, cl.`manager_email`, cl.`manager_notify` FROM 
`returns` ret LEFT JOIN `clients` cl ON cl.`id` = ret.`client_id`    
WHERE ret.`receive_date` BETWEEN "'.$dates['from'].'" AND "'.$dates['to'].'" 
ORDER by ret.`id` DESC;');
if (mysqli_num_rows($sql) != false) {
      
      while ($row = mysqli_fetch_array($sql)) {
      $sql_check_count = (int)mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` WHERE `return_id` = "'.$row['id'].'" and `deleted` = 0;'))['COUNT(*)'];
      $sql_check_count2 = (int)mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` WHERE `return_id` = "'.$row['id'].'" and (`status_admin` = \'Подтвержден\' OR `status_admin` = \'Выдан\') and `deleted` = 0;'))['COUNT(*)'];
      $percent = 0;
      if($sql_check_count > $sql_check_count2) { 
        $percent = round(($sql_check_count2/$sql_check_count)*100);
      }elseif($sql_check_count < $sql_check_count2){
        $percent = round(($sql_check_count/$sql_check_count2)*100);
      }else{
        $percent = 100;
      }
      $link = ' <a href="https://crm.r97.ru/?query=create_super_nak&value=['.gen_list_repairs($row['id']).']">НАК</a><br>';
      $link .= ' <a href="https://crm.r97.ru/?query=create_super_kvit&value=['.gen_list_repairs($row['id']).']">КВИТ</a><br>';
      $link .= ($sql_check_count == $sql_check_count2) ? '<a target="_blank" class="contr1" href="/excel2/'.$row['id'].'/?date='.$row['date'].'&act='.$row['id'].'">АТС-П</a> (<a target="_blank" class="contr1-xls" href="/excel2-xls/'.$row['id'].'/?date='.$row['date'].'&act='.$row['id'].'">XLS</a>)<br>' : '';
      $link .= ' <a href="/act-from/'.$row['id'].'/">АПП-ОП-К</a><br>';
      $link .= ($sql_check_count == $sql_check_count2) ? ' <a href="/act-to/'.$row['id'].'/">АПП-К-ОП</a><br>' : '';
      $link .= ' <a href="/act-a3/'.$row['id'].'/">АВР-АТО-АНРП</a><br>';
      $link .= ' <a target="_blank" href="/excel3/'.$row['id'].'/" class="contr2">АТС-ДНО</a> (<a target="_blank" href="/excel3-xls/'.$row['id'].'/" class="contr2-xls">XLS</a>)<br>';
      $link .= ' <a target="_blank" href="/excel4/'.$row['id'].'/" class="contr3">АТС-ОВГР</a> (<a target="_blank" href="/excel4-xls/'.$row['id'].'/" class="contr3-xls">XLS</a>)';
      $link2 = ($sql_check_count == $sql_check_count2) ? '<a style="background: url(https://image.flaticon.com/icons/png/512/25/25228.png) 0 no-repeat;background-size:cover;margin-right:0;" class="t-4" title="Финансы" data-fancybox autoScale=false data-type="iframe" data-src="/return-finance/'.$row['id'].'/" href="javascript:;" ></a>' : '';
      $color = ($row['date_farewell'] == '' && $percent == 100) ? "style='    background: rgba(128, 189, 3, 0.28);'" : '';
      $content_list .= '<tr '.$color.'>
      <td>'.$row['name'].'</td>
      <td>'.DateTime::createFromFormat('d.m.Y', $row['date'])->format('m').'.'.DateTime::createFromFormat('d.m.Y', $row['date'])->format('Y').' ('.$rus_months[DateTime::createFromFormat('d.m.Y', $row['date'])->format('n')-1].')</td>
      <td>'.$row['id'].'</td>
      <td>'.$row['client_name'].'</td>
      <td>'.$sql_check_count.'</td>
      <td>'.$percent.'%</td>
      <td>'.$row['date'].'</td>';
      if ($sql_check_count == $sql_check_count2 && ($sql_check_count != 0 && $sql_check_count2 != 0)) {
    //  if (true) {
        if ($row['date_out'] == '' && $percent == 100) {
            $date_out = @mysqli_fetch_array(mysqli_query($db, 'SELECT `app_date` FROM `repairs` WHERE `return_id` = '.$row['id'].' ORDER BY STR_TO_DATE(app_date, \'%Y.%m.%d\') DESC LIMIT 1'))['app_date'];
            mysqli_query($db, 'UPDATE `returns` SET `date_out` = \''.$date_out.'\' where `id` = '.$row['id']);
            $row['date_out'] = $date_out;
        // !SM $date_out = @mysqli_fetch_array(mysqli_query($db, 'SELECT `app_date` FROM `repairs` WHERE `return_id` = '.$row['id'].' ORDER BY STR_TO_DATE(app_date, \'%Y.%m.%d\') DESC LIMIT 1'))['app_date'];


        /* mysqli_query($db, 'UPDATE `returns` SET `date_out` = \''.$date_out.'\' where `id` = '.$row['id']);

        $sql2 = mysqli_query($db, 'SELECT * FROM `repairs` where `return_id` = '.$row['id']);
              while ($row2 = mysqli_fetch_array($sql2)) {
        mysqli_query($db, 'UPDATE `repairs` SET
        `app_date` = \''.mysqli_real_escape_string($db, $date_out).'\'
        WHERE `id` = \''.mysqli_real_escape_string($db, $row2['id']).'\' LIMIT 1') or mysqli_error($db);


              admin_log_add('Партия возврата  #'.$row['id'].' получила бы дату выдачи '.$date_out.' и ремонты обновлены');
         $row['date_out'] = $date_out;  */
        if ($row['notify'] == 0 && $row['manager_notify'] == 1) {
         /* mysqli_query($db, 'UPDATE `returns` SET `notify` = 1 where `id` = '.$row['id']);
          client_notify($content_client['manager_email'], $row['id'], 2);    */
        }

        }
         if ($row['usd'] == '') {
        mysqli_query($db, 'UPDATE `returns` SET  `usd` = \''.$usd.'\' where `id` = '.$row['id']);
         } 
       //  $content_list .= '<td></td><td></td>';
       $content_list .= ' <td>
       <input disabled data-id="'.$row['id'].'" 
       class="datepicker2 metro-skin date-out-input" type="text" name="date_out" value="'.\models\Returns::getReadyDate($row['id']).'" readonly="true" />
       </td>
      <td style="    text-align: center;"><input type="checkbox" data-id="'.$row['id'].'" name="out" '.(($row['out'] == 1) ? 'checked ' : '').'>'.(($row['date_farewell'] != '') ? '('.DateTime::createFromFormat('Y.m.d', $row['date_farewell'])->format('d.m.Y').')' : '').'</td>'; 
    // !SM $date_out = '';
      } else {
      $content_list .= '<td></td><td></td>';
      }
      $content_list .= '<td style="font-size:15px;" data-contractor>
      <p style="padding:0px 0px 5px 0px;"><select name="contr"><option value="">Выберите контрагента</option>'.$contr.'</select></p>

      '.$link.'</td>
      <td align="center" class="linkz" >
      <a class="t-2" title="Просмотр карточки" href="/return/'.$row['id'].'/" ></a>
      <a class="t-4" title="Ремонты" data-fancybox autoScale=false data-type="iframe" data-src="/return-dashboard/'.$row['id'].'/" href="javascript:;" ></a>
      '.$link2.' <br><br>
      <a class="t-5" title="Удалить партию" href="/del-return/'.$row['id'].'/" onclick=\'return confirm("Удаление партии удалит все ее ремонты! Подтвердите удаление.")\'></a>
      </td></tr>';

      }
      } 
      return ['dates_interval' => $datesInterval, 'content_html' => $content_list, 'pagination_html' => getPaginationHTML($paginator->getPagination())];
}



function gen_contr() {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `contrahens` ;');
      while ($row = mysqli_fetch_array($sql)) {
      if ($country_id == $row['id']) {
      $content .= '<option selected value="'.$row['id'].'">'.$row['name'].'</option>';
      } else {
       $content .= '<option value="'.$row['id'].'">'.$row['name'].'</option>';
      }
      }
    return $content;
}

function gen_list_repairs($return_id) {
  global $db;
$sql = mysqli_query($db, 'SELECT `id` FROM `repairs` where `return_id` = '.$return_id);
      while ($row = mysqli_fetch_array($sql)) {
       $content[] = $row['id'];
      }
    return implode(',', $content);

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
?>
<!doctype html>
<html>
<head>
<meta charset=utf-8>
<title>Партии возвратов</title>
<link href="/css/fonts.css" rel="stylesheet" />
<link href="/css/style.css" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="/js/daterangepicker.css">
<script src="/_new-codebase/front/vendor/jquery/jquery.min.js"  ></script>
<script src="/js/jquery-ui.min.js"></script>
<script src="/js/jquery.placeholder.min.js"></script>
<script src="/js/jquery.formstyler.min.js"></script>
<script src="/js/main.js"></script>
 <script src="/js/moment.min.js"></script>
<script src="/js/jquery.daterangepicker.js"></script>
 <script src="/notifier/js/index.js"></script>
 <script src="/_new-codebase/front/vendor/remodal/remodal.min.js"></script>
<link rel="stylesheet" href="/_new-codebase/front/vendor/remodal/remodal.css" />
<link rel="stylesheet" href="/_new-codebase/front/vendor/remodal/remodal-default-theme.css" />
<link rel="stylesheet" type="text/css" href="/notifier/css/style.css">
<link rel="stylesheet" href="/_new-codebase/front/vendor/animate.min.css" />
<script src='/_new-codebase/front/vendor/mustache/mustache.min.js'></script>
<link rel="stylesheet" href="/_new-codebase/front/vendor/select2/4.0.4/select2.min.css" />
<script src="/_new-codebase/front/vendor/select2/4.0.4/select2.full.min.js"></script>
<script src="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.concat.min.js"></script>
<link rel="stylesheet" href="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.min.css" />
<script  src="/_new-codebase/front/vendor/datatables/1.10.12/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="/css/datatables.css">
<link rel="stylesheet" href="/_new-codebase/front/vendor/fancybox/3.5.2/jquery.fancybox.min.css" />
<script src="/_new-codebase/front/vendor/fancybox/3.5.2/jquery.fancybox.min.js"></script>
<script src="/_new-codebase/front/vendor/datatables/1.10.19/date-de.js"></script>

<script >
// Таблица
$(document).ready(function() {

    var groupColumn = 1;

    var table = $('#table_content').dataTable({
      stateSave:false,
      "dom": '<"top"flp<"clear">>rt<"bottom"ip<"clear">>',
      "columnDefs": [
            { "visible": false, "targets": groupColumn },
            {   "targets": [ 2 ],
                "visible": false
            },
            { type: 'de_date', targets: 6 }
        ],
        "order": [[ 6, "desc" ]],
        //"order": [[ groupColumn, 'desc' ]],
        "drawCallback": function ( settings ) {
            var api = this.api();
            var rows = api.rows( {page:'current'} ).nodes();
            var last=null;

            api.column(groupColumn, {page:'current'} ).data().each( function ( group, i ) {
                if ( last != group ) {
                    $(rows).eq( i ).before(
                        '<tr class="group"><td colspan="9">'+group+'</td></tr>'
                    ); 

                    last = group;
                }
            } );

            $( ".datepicker2" ).datepicker({
  dateFormat: 'yy.mm.dd',
  maxDate: new Date,
    onSelect: function(dateText, inst) {
        var date = $(this).val();
        var id = $(this).data('id');


                  $.get( "/ajax.php?type=update_repair_outdate&value="+date+"&id="+id, function( data ) {

                  });



    } ,
     beforeShow: function(input, inst) {
       $('#ui-datepicker-div').addClass("ll-skin-cangas");
   }
});


        },
      //  "paging": false,  
      "pageLength": <?=$config['page_limit'];?>,
      "oSearch": {"sSearch": "<?= (!empty(core\App::$URLParams['search'])) ? core\App::$URLParams['search'] : ''; ?>"},
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

jQuery.extend( jQuery.fn.dataTableExt.oSort, {
"date-uk-pre": function ( a ) {
    var ukDatea = a.split('.');
    return (ukDatea[2] + ukDatea[1] + ukDatea[0]) * 1;
},

"date-uk-asc": function ( a, b ) {
    return ((a < b) ? -1 : ((a > b) ? 1 : 0));
},

"date-uk-desc": function ( a, b ) {
    return ((a < b) ? 1 : ((a > b) ? -1 : 0));
}
} );

$('#table_content tbody').on( 'click', 'tr.group', function () {
        var currentOrder = table.order()[0];
        if ( currentOrder[0] === groupColumn && currentOrder[1] === 'asc' ) {
            table.order( [ groupColumn, 'desc' ] ).draw();
        }
        else {
            table.order( [ groupColumn, 'asc' ] ).draw();
        }
    } );


$( ".datepicker2" ).datepicker({
  dateFormat: 'yy.mm.dd',
  maxDate: new Date,
    onSelect: function(dateText, inst) {
        var date = $(this).val();
        var id = $(this).data('id');


                  $.get( "/ajax.php?type=update_repair_outdate&value="+date+"&id="+id, function( data ) {

                  });



    } ,
     beforeShow: function(input, inst) {
       $('#ui-datepicker-div').addClass("ll-skin-cangas");
   }
});
$.datepicker.setDefaults( $.datepicker.regional[ "ru" ] );

    $(document).on('change', 'input[name="out"]', function() {
        var id = $(this).data('id');

        if (this.checked) {

          $.get( "/ajax.php?type=returns_out&id="+id, function( data ) {
          });

        }  else {

          $.get( "/ajax.php?type=returns_out_minus&id="+id, function( data ) {
          });

        }





    });

    $(document).on('selectmenuchange', 'select[name="contr"]', function() {
let $container = $(this).closest('[data-contractor]');
$container.find('.contr1').attr('href', $container.find('.contr1').attr('href')+'&contr='+$(this).val());
$container.find('.contr2').attr('href', $container.find('.contr2').attr('href')+'?contr='+$(this).val());
$container.find('.contr3').attr('href', $container.find('.contr3').attr('href')+'?contr='+$(this).val());
$container.find('.contr1-xls').attr('href', $container.find('.contr1-xls').attr('href')+'&contr='+$(this).val());
$container.find('.contr2-xls').attr('href', $container.find('.contr2-xls').attr('href')+'?contr='+$(this).val());
$container.find('.contr3-xls').attr('href', $container.find('.contr3-xls').attr('href')+'?contr='+$(this).val()); 

});

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


$( ".show_calend" ).on( "click", function() {
$('[data-remodal-id=modal]').remodal().open();
return false;
});

$( ".gen_acts" ).on( "click", function() {

var date1 = $('#date-range200').val();
var date2 = $('#date-range201').val();

/*var checked = $('input[name="only_tv"]').is(':checked');
if (checked) {
    var tv_only = 1;
} else {
    var tv_only = 0;
}  */
var mySelections = [];
        $('.select_cats option').each(function(i) {
                if (this.selected == true) {
                        mySelections.push(this.value);
                }
        });

         $.get( "/ajax.php?type=get_returns_report&date1="+date1+"&date2="+date2+"&cats="+mySelections, function( data ) {

         $('.stats_modal').html(data.body);

         });


});

$( ".gen_acts_cli" ).on( "click", function() {

var date1 = $('#date-range200').val();
var date2 = $('#date-range201').val();

/*var checked = $('input[name="only_tv"]').is(':checked');
if (checked) {
    var tv_only = 1;
} else {
    var tv_only = 0;
}  */
var mySelections = [];
        $('.select_cats option').each(function(i) {
                if (this.selected == true) {
                        mySelections.push(this.value);
                }
        });

         $.get( "/ajax.php?type=get_returns_report_cli&date1="+date1+"&date2="+date2+"&cats="+mySelections, function( data ) {

         $('.stats_modal').html(data.body);

         });


});

$('.select2').select2();

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
tr.group,
tr.group:hover {
    background: #77ad07;
    background-color: #77ad07 !important;
    color: #fff;
}
#ui-datepicker-div {
z-index: 999999999999999999 !important;
}
.ui-selectmenu-button:after {

    right: 8px;
}
.date-picker-wrapper {
    z-index: 9999999999999999 !important;
}
.select2-container {

    z-index: 9999999999;

}
.select2-selection__choice {
font-size:15px;
}
.date-out-input
display:block;   width: 98px;     height: 32px;    padding: 3px;  margin-top: 5px;    font-size: 19px;
}
</style>

<!-- New codebase -->
<link rel="stylesheet" href="/_new-codebase/front/components/the-table/css/the-table.css">
<link rel="stylesheet" href="/_new-codebase/front/templates/main/css/pagination.css">
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
           <?php
$content = content_list(); 
?>

           <h2 style="margin-bottom: 12px;">Партии возвратов</h2>
<?= '<p>'.$content['dates_interval'].'</p>'; ?>
  <div class="adm-catalog">

<div style="display: flex;margin: 20px 0;align-items: center;justify-content: space-between;">
     <a style="width: auto;padding-left: 7px;padding-right: 7px;vertical-align: middle;" href="#" class="button show_calend">Финансовый отчет</a>
   
   <div>
     <?= $content['pagination_html']; ?>
     </div>
    </div>


  <table id="table_content" class="display" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th align="left">Партия</th>

                <th align="left">Дата</th>
                <th align="left">ID</th>
                <th align="left">Клиент</th>
                <th align="left">Кол-во</th>
                <th align="left">Уже проверено</th>
                <th align="left">Дата приема</th>
                <th align="left">Дата готовности партии</th>
                <th align="left">Выдан</th>
                <th align="left">Документы</th>
                <th align="center">Операции</th>
            </tr>
        </thead>

        <tbody>
        <?= $content['content_html']; ?>
        </tbody>
</table>

<div class="remodal" data-remodal-id="modal">
  <button data-remodal-action="close" class="remodal-close"></button>
  <h1 style="float:none !important">Выберите дату</h1>
   <br />
  <table style="    margin: 0 auto;">
  <tr>
  <Td>

<span id="two-inputs">От <input type="text" id="date-range200" name="date1" style="    width: 120px;   height: 30px;padding:5px;" value="<?=($_GET['date1'] ? $_GET['date1'] : '')?>"/> До <input name="date2"  type="text" id="date-range201" style="    width: 120px;   height: 30px;padding:5px;" value="<?=($_GET['date2'] ? $_GET['date2'] : '')?>"/></span>

<br><br>


  <br></Td>
  </tr>
  <tr>
  <td>
  Категория
<select style="width:600px;" class="select2 nomenu select_cats" name="cats[]" multiple="multiple"><?=cat();?></select>
 <br><br>
</td>
  </tr>
  </table>

  <button style="    width: 30%;"  class="gen_acts">Генерировать</button>
  <button style="    width: 40%;"  class="gen_acts_cli">Генерировать по клиентам</button>

<div class="stats_modal"></div>

</div>


</div>


        </div>
  </div>
</div>
</body>
</html>