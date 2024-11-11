<?php

use models\Tariffs;
use program\core\Time;

ini_set('post_max_size', '4048M');
ini_set('memory_limit', '4048M');

function get_warranty($model_id) {
    global $db;
$req = mysqli_fetch_array(mysqli_query($db, 'SELECT `warranty` FROM `models` WHERE `id` = '.$model_id));
return $req['warranty'];

}

# Сохраняем:
if ($_POST['send'] == 1) {

$uddd = 1;


}

if ($_FILES['userfile']['tmp_name']) {
/*$act = file_get_contents('act.txt');
file_put_contents('act.txt', $act+1);  */
$act = $_POST['act'];
//error_reporting(-1);
include $_SERVER['DOCUMENT_ROOT'].'/adm/PHPExcel/Classes/PHPExcel.php';
    $dataFfile = $_FILES['userfile']['tmp_name'];
    $objPHPExcel = PHPExcel_IOFactory::load($dataFfile);
    $sheet = $objPHPExcel->getActiveSheet();
    $maxCell = $sheet->getHighestRowAndColumn();
    try{
      $data = $sheet->rangeToArray('A2:' . $maxCell['column'] . $maxCell['row']);
    }catch(Exception $e){
      echo '<p><b>При обработке файла Excel произошла ошибка:</b></p>';
      echo '<p>'.str_replace('internal error', 'невозможно прочитать данные из ячейки', $e->getMessage()).'</p>';
      echo '<p><a href="/mass-upload/">Назад</a></p>';
      exit;
    }
    //echo "Rows available: " . count($data) . "\n";
    $i = 0;

$sql_check_count = mysqli_query($db, 'SELECT COUNT(*) FROM `returns` WHERE `client_id` = \''.mysqli_real_escape_string($db, $_POST['client_id']).'\' and `date` = \''.str_replace('.', '', $_POST['date']).'\';');
$count_today = mysqli_fetch_array($sql_check_count)['COUNT(*)'];

if (count($data) > 0) {

$content_client = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `clients` WHERE `id` = \''.mysqli_real_escape_string($db, $_POST['client_id']).'\' LIMIT 1;'));
$name = ($count_today == 0) ? $content_client['code'].str_replace('.', '', $_POST['date']).'-1' : $content_client['code'].str_replace('.', '', $_POST['date']).'-'.($count_today+1);
    
mysqli_query($db, 'INSERT INTO `returns` (
`client_id`,
`name`,
`date`,
`receive_date`
) VALUES (
\''.mysqli_real_escape_string($db, $_POST['client_id']).'\',
\''.mysqli_real_escape_string($db, $name).'\',
\''.mysqli_real_escape_string($db, $_POST['date']).'\',
\''.mysqli_real_escape_string($db, date('Y-m-d', strtotime($_POST['date']))).'\'
);') or mysqli_error($db); 

$return_id = mysqli_insert_id($db);

}

$i_row = 2;
    foreach ($data as $row) {
if (!$row['2']) {
  continue;
}
$model = model_by_name_search($row['2']);
//$client_id = ($row['14']) ? 1 : 2;

$serviceFlag = 1;

if ($model['id']) {
$sql_check_count2 = mysqli_query($db, 'SELECT `service` FROM `models_users` WHERE `service_id` = 33 AND `model_id` = "'.$model['id'].'";');
if(mysqli_num_rows($sql_check_count2)){
  $ar = mysqli_fetch_array($sql_check_count2);
  if($ar['service'] == 'Нет'){
    $serviceFlag = 0;
  }
}
}


$status_id = 1;
if (strtolower($row['8']) == 'возврат от клиента') {
$client_id = 1;
$status_id = 1;
$date_sell = $row['6'];
}
if (strtolower($row['8']) == 'организация') {
$client_id = 2;
$status_id = 7;
}


$anrp_use = '';
$update_approve = '';
$update_approve_field = '';
$readyDate = '';
$readyDate_field = '';
$startDate = date('d.m.Y', strtotime($_POST['date']));
$beginDate = date('Y-m-d', strtotime($_POST['date']));
$endDate = '';
$finishDate = '0000-00-00';
if($row['13'] != ''){
  $anrp_use = 1;
  $endDate = date('d.m.Y', strtotime($_POST['date']));
  $finishDate = date('Y-m-d', strtotime($_POST['date']));
  $update_approve = ', 2';
  $update_approve_field = ', `repair_final`';
  $readyDate = ', "'.date('Y-m-d').'"';
  $readyDate_field = ', `ready_date`';
}
if ($row['6']) {

if (preg_match('/\//', $row['6'])) {
$row['6'] = $date = date('d.m.Y',PHPExcel_Shared_Date::ExcelToPHP($sheet->getCellByColumnAndRow(6, $i_row)->getValue()));
}
}

$i_row++;

///////////////////////
/* Проверка гарантии  */
if ($row['6']) {

$date2_war = DateTime::createFromFormat('d.m.Y', str_replace('/', '.', $row['6']));
if ($date2_war) {
$date2_row_war = $date2_war->modify('+'.get_warranty($model['id']).' days');
}
$date3 = new DateTime();
$date3_row_war = DateTime::createFromFormat('d.m.Y', date("d.m.Y", strtotime($_POST['date'])));

if ($date3_row_war && $date2_row_war) {
$status_id = ($date3_row_war <= $date2_row_war) ? 1 : 5;
} else {
$status_id = 1;
}

}


                    
/* /// */
/////////
$noSerial = (empty($row['3'])) ? 1 : 0;
$shopName = '';
$shopAddress = '';
$shopPhone = '';
if(empty($row['14']) && empty($row['15']) && empty($row['16'])){
  $content_client['type_id'] = 2;
}
if($content_client['type_id'] == 2) {
  $shopName = trim(str_replace('(A)', '', $content_client['name']));
  $shopAddress = (empty($content_client['address'])) ? 'отсутствует' : $content_client['address'];
  $shopPhone = $content_client['phone'];
}

mysqli_query($db, 'INSERT INTO `repairs` (
`status_ship_id`, 
`begin_date`,
`finish_date`,
`service_id`,
`rsc`,
`client`,
`client_type`,
`client_id`,
`address`,
`phone`,
`name_shop`,
`address_shop`,
`phone_shop`,
`model_id`,
`talon`,
`serial`,
`status_id`,
`sell_date`,
`receive_date`,
`complex`,
`visual`,
`bugs`,
`comment`,
`visual_comment`,
`imported`,
`imported_model`,
`return_id`,
`status_admin`,
`anrp_use`,
`anrp_number`,
`no_serial`,
`serial_invalid_flag`,
`refuse_doc_flag` 
'.$update_approve_field.'
'.$readyDate_field.'
) VALUES (
'.getShipStatus($row, $content_client['type_id'], $model['id']).', 
"'.$beginDate.'",
"'.$finishDate.'",
33,
\''.mysqli_real_escape_string($db, $row['1']).'\',
\''.mysqli_real_escape_string($db, $row['14']).'\',
\''.mysqli_real_escape_string($db, $content_client['type_id']).'\',
\''.mysqli_real_escape_string($db, $_POST['client_id']).'\',
\''.mysqli_real_escape_string($db, ((empty($row['15'])) ? 'отсутствует' : $row['15'])).'\',
\''.mysqli_real_escape_string($db, $row['16']).'\',
\''.mysqli_real_escape_string($db, $shopName).'\',
\''.mysqli_real_escape_string($db, $shopAddress).'\',
\''.mysqli_real_escape_string($db, $shopPhone).'\',
\''.mysqli_real_escape_string($db, $model['id']).'\',
\''.mysqli_real_escape_string($db, getComplex($row['11'])).'\',
\''.mysqli_real_escape_string($db, $row['3']).'\',
\''.mysqli_real_escape_string($db, $status_id).'\',
\''.mysqli_real_escape_string($db, Time::format(str_replace('/', '.', $row['6']), 'Y-m-d')).'\',
\''.mysqli_real_escape_string($db, date('Y-m-d', strtotime($_POST['date']))).'\', 
\''.mysqli_real_escape_string($db, getProductContent($row['11'])).'\', 
\''.mysqli_real_escape_string($db, '').'\',
\''.mysqli_real_escape_string($db, $row['9']).'\',
\''.mysqli_real_escape_string($db, '').'\',
\''.mysqli_real_escape_string($db, getExterior($row['12'])).'\',
1,
\''.mysqli_real_escape_string($db, $row['2']).'\',
'.$return_id.',
"Принят",
\''.mysqli_real_escape_string($db, $anrp_use).'\',
\''.mysqli_real_escape_string($db, trim($row['13'])).'\',
"'.$noSerial.'",
'.getSerialInvalidFlag($row['3'], $model['id'], $noSerial).',
"n" 
' . $update_approve . ' 
' . $readyDate . '
);') or mysqli_error($db); 
$repair_id = mysqli_insert_id($db);


if ($serviceFlag == 0 && $repair_id) {

mysqli_query($db, 'INSERT INTO `repairs_work` (
`repair_id`,
`name`,
`position`,
`problem_id`,
`repair_type_id`,
`qty`,
`ordered_flag`,
`price`,
`sum`
) VALUES (
\''.$repair_id.'\',
\''.mysqli_real_escape_string($db, '').'\',
\''.mysqli_real_escape_string($db, '').'\',
\''.mysqli_real_escape_string($db, 27).'\',
\''.mysqli_real_escape_string($db, 3).'\',
\''.mysqli_real_escape_string($db, 0).'\',
\''.mysqli_real_escape_string($db, 0).'\',
\''.mysqli_real_escape_string($db, 0).'\',
\''.mysqli_real_escape_string($db, 0).'\'
);');

$table = Tariffs::getServiceTariffTable(33);
          if (mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `prices_service` where `cat_id` = \'' . $model['cat'] . '\' and `service_id` = 33 ;'))['COUNT(*)'] > 0) {
            $price = @mysqli_fetch_array(mysqli_query($db, 'SELECT `anrp` FROM `prices_service` where `cat_id` = \'' . $model['cat'] . '\' and `service_id` = 33 ;'))['anrp'];
          } else {
            $price = @mysqli_fetch_array(mysqli_query($db, 'SELECT `anrp` FROM `'.$table.'` where `cat_id` = \'' . $model['cat'] . '\';'))['anrp'];
          }
          mysqli_query($db, 'UPDATE `repairs` SET `repair_type_id` = 4, `app_date` = "' . date("Y.m.d") . '", `approve_date` = "' . date("Y-m-d") . '", `finish_date` = "' . date("Y-m-d") . '", `ready_date` = "' . date('Y.m.d') . '", `master_app_date` = "' . date("Y.m.d") . '", `total_price` = ' . $price . ', `status_admin` = "Подтвержден", `repair_done` = 1, `disease` = 654, `repair_final` = 2 WHERE `id` = "' . $repair_id . '";');
}
unset($model);
unset($price);
unset($anrp_sql);
unset($anrp_value);

    }

if ($content_client['manager_notify'] == 1) {
client_notify($content_client['manager_email'], $return_id, 1, $content_client['name']);
}
if ($content_client['manager_contact_notify'] == 1) {
client_notify($content_client['contacts_phone'], $return_id, 1, $content_client['name']);
}

header('Location: /returns/');
exit;

}




function clients($cat_id) {
  global $db;
$content = array();
$sql = mysqli_query($db, 'SELECT * FROM `clients`;');
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
<title>Массовый импорт ремонтов - Панель управления</title>
<link href="/css/fonts.css" rel="stylesheet" />
<link href="/css/style.css" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="/js/daterangepicker.css">
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
 <script src="/_new-codebase/front/vendor/select2/4.0.4/select2.full.min.js"></script>
 <script src="/js/moment.min.js"></script>
<script src="/js/jquery.daterangepicker.js"></script>
 <link rel="stylesheet" href="/_new-codebase/front/vendor/select2/4.0.4/select2.min.css" />

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
$( ".datepicker" ).datepicker({ dateFormat: 'dd.mm.yy',maxDate: '0' });
$( ".datepicker2" ).datepicker({ dateFormat: 'dd.mm.yy' });
$("#ui-datepicker-div").addClass("ll-skin-cangas");
$.datepicker.setDefaults( $.datepicker.regional[ "ru" ] );

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
</style>
<style>
.select2-container {
    width: 450px !important;
}
</style>
  <script >
  $(document).ready(
    function()		{
    $('#select_model').select2();

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

  $('#two-inputs2').dateRangePicker(
  {
    separator : ' to ',
    getValue: function()
    {
      if ($('#date-range2002').val() && $('#date-range2012').val() )
        return $('#date-range2002').val() + ' to ' + $('#date-range2012').val();
      else
        return '';
    },
    setValue: function(s,s1,s2)
    {
      $('#date-range2002').val(s1);
      $('#date-range2012').val(s2);
    }
  });

    $( ".dwn" ).each(function() {
            $( this ).attr('href', $( this ).data('href')+$('input[name="brands2[]"]:checked').map(function() { return this.value;  }).get().join(',')+'/');
    });

    $(document).on('change', 'input[name="brands2[]"]', function() {
          $( ".dwn" ).each(function() {
            $( this ).attr('href', $( this ).data('href')+$('input[name="brands2[]"]:checked').map(function() { return this.value;  }).get().join(',')+'/');
          });
    });

    }  );
  </script>
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
           <h2>Массовый импорт ремонтов</h2>

  <form  enctype="multipart/form-data" id="send" method="POST">
   <div class="adm-form" style="padding-top:0;">

          <div class="item">
              <div class="level">Клиент:</div>
              <div class="value">
              <select name="client_id" class="select2 nomenu">
               <option value="">Выберите клиента</option>
                <?=clients();?>
              </select>
              </div>
            </div>

   <div class="item">
              <div class="level">Дата приема:</div>
              <div class="value">
                <input type="text" required class="datepicker metro-skin" name="date" value="<?=\program\core\Time::format($content['begin_date']);?>"  />
              </div>
            </div>

   <div class="item">
              <div class="level">Файл импорта:</div>
              <div class="value">
                <input type="file" name="userfile" />
              </div>
            </div>


                <div class="adm-finish">
            <div class="save">
              <input type="hidden" name="send" value="1" />
              <button type="submit" >Импортировать</button>
            </div>
            </div>
        </div>

      </form>

        </div>
  </div>
</div>
</body>
</html>


<?php

function getSerialInvalidFlag($serial, $modelID, $noSerialFlag)
{
  if($noSerialFlag || empty(trim($serial))){
    return 0;
  }
  return (int)!\models\Serials::isValid($serial, $modelID);
}

function getShipStatus(array $repair, $clientType, $modelID)
{
  if (models\Repair::isRepeated(0, $modelID, $repair['3'])) { // $repair['3'] - serial
    return 3; // Повторный
  }
  if ($clientType == 1 || ($clientType == 2 && !Time::isEmpty($repair['6']))) { // $repair['6'] - sell_date
    return 2; // Клиентский
  }
  if ($clientType == 2 && Time::isEmpty($repair['6'])) {
    return 1;  // Предторговый   
  }
  return 0;
}
  

function getExterior($val){
  return (!empty($val)) ? trim($val) : 'б/у';
}


function getProductContent($val)
{
  return (!empty($val)) ? mb_strtoupper(trim($val)) : 'ПОЛНАЯ';
}

  function getComplex($complex)
  {
    $c = [];
    if (preg_match('/полная/iu', $complex)) {
      return 'Гарантийный талон';
    }
    if (preg_match('/ФГТ|гарантийник/iu', $complex)) {
      $c[] = 'Гарантийный талон';
    }
    if (preg_match('/ККЧ|КЧ|Чек/iu', $complex)) {
      $c[] = 'Чек';
    }
    if ($c) {
      return implode('+', $c);
    }
    return 'Гарантийный талон';
  }