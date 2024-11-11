<?php

use models\disposals\Requests;
use models\staff\Staff;
use models\Tariffs;
use models\User;
use models\Users;

$_monthsList = array(
"01"=>"Январь","02"=>"Февраль","03"=>"Март",
"04"=>"Апрель","05"=>"Май", "06"=>"Июнь",
"07"=>"Июль","08"=>"Август","09"=>"Сентябрь",
"10"=>"Октябрь","11"=>"Ноябрь","12"=>"Декабрь");

$_monthsList2 = array(
"01"=>"Января","02"=>"Февраля","03"=>"Марта",
"04"=>"Апреля","05"=>"Мая", "06"=>"Июня",
"07"=>"Июля","08"=>"Августа","09"=>"Сентября",
"10"=>"Октября","11"=>"Ноября","12"=>"Декабря");






function getCompletedColor($repair)
{
  if(empty($repair) || $repair['status_id'] == 6){ // Платный
    return '';
  }
  if (empty($repair['serial'])) {
    return 'yellow';
  }
  if (!empty($repair['serial']) && !models\Serials::isValid($repair['serial'], $repair['model_id'])) {
    return 'serial-error-bg';
  }  
  if (empty($repair['model_id']) || empty($repair['bugs']) || (empty($repair['visual']) && empty($repair['visual_comment'])) || empty($repair['complex']) || empty($repair['refuse_doc_flag'])) {
    return 'yellow';
  }
  if ($repair['client_type'] == 1 && empty($repair['name_shop'])) {
    return 'yellow';
  }
}

function photoRedir($repairID)
{
  global $db;
  if (!\models\User::hasRole('service')) {
    return;
  }
  $r = mysqli_fetch_assoc(mysqli_query($db, 'SELECT `bugs`, `serial`, `no_serial`, `client_type`, `status_admin` FROM `repairs` WHERE `id` = ' . $repairID));
  if (in_array($r['status_admin'], ['Подтвержден', 'Отклонен'])) {
    return;
  }
  if ((empty($r['serial']) && empty($r['no_serial'])) && empty($r['bugs'])) {
    header('Location: /edit-repair/' . $repairID . '/');
    exit;
  }
  $photoErr = \models\repair\Check::hasPhotoErrors($repairID); 
  if (!empty($r['client_type']) && $photoErr['error_flag']) {
    header('Location: /edit-repair/' . $repairID . '/step/4/');
    exit;
  }
}


function stripslashes_array($array) {
   return is_array($array) ?
     array_map('stripslashes_array', $array) : htmlspecialchars($array, ENT_QUOTES);
}

function cutString($string, $maxlen) {
     $len = (mb_strlen($string) > $maxlen)
         ? mb_strripos(mb_substr($string, 0, $maxlen), ' ')
         : $maxlen
     ;
     $cutStr = mb_substr($string, 0, $len);
     return (mb_strlen($string) > $maxlen)
         ? '' . $cutStr . '...'
         : '' . $cutStr . ''
     ;
 }

function parts_price_billing($id) {
  global $db;
$sql = mysqli_query($db, 'SELECT sum(`sum`) FROM `repairs_work` where `repair_id` = '.$id.';');
$row = @mysqli_fetch_array($sql)['sum(`sum`)'];
$sum = ($row) ? $row : 0;
return $sum;
}

function parts_price_billing_info($id) {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `repairs_work` where `repair_id` = '.$id.';');
$row = @mysqli_fetch_array($sql);
$sum = ($row) ? $row : 0;
return $sum;
}


function check_sended($id, $part_id) {
  global $db;

if (mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `parts_sc` where `repair_id` = '.$id.' and `part_id` = '.$part_id.';'))['COUNT(*)'] > 0) {
return false;
} else {
return true;
}
}

function check_user_cats($id, $cat_id, $type) {
  global $db;

if (mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `users_cats` where `user_id` = '.$id.' and `cat_id` = '.$cat_id.' and `type_id` = '.$type.';'))['COUNT(*)'] > 0) {
return true;
} else {
return false;
}
}

function count_in_progress() {
  global $db;

$count = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` where `service_id` = 33 and `status_admin` != \'\' and `deleted` != 1 and `model_id` != \'\' and `master_user_id` = '.User::getData('id').' and `status_admin` = \'В работе\' order by `id` DESC;'))['COUNT(*)'];
return $count;
}

function check_document($id) {
  global $db;
if(in_array($id, [288, 188])){
$q = ''; // проверять Дополнительное соглашение №8
}else{
  $q = ' AND `document_id` != 11 '; // не проверять Дополнительное соглашение №8
}

if (mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `services_documents` where `service_id` = '.$id.' ' . $q .' and `status` = "Нет" and `deleted` != 1;'))['COUNT(*)'] > 0) {
return false;
} else {
return true;
}


}


function num2str($num) {
  $nul='ноль';
  $ten=array(
    array('','один','два','три','четыре','пять','шесть','семь', 'восемь','девять'),
    array('','одна','две','три','четыре','пять','шесть','семь', 'восемь','девять'),
  );
  $a20=array('десять','одиннадцать','двенадцать','тринадцать','четырнадцать' ,'пятнадцать','шестнадцать','семнадцать','восемнадцать','девятнадцать');
  $tens=array(2=>'двадцать','тридцать','сорок','пятьдесят','шестьдесят','семьдесят' ,'восемьдесят','девяносто');
  $hundred=array('','сто','двести','триста','четыреста','пятьсот','шестьсот', 'семьсот','восемьсот','девятьсот');
  $unit=array( // Units
    array('копейка' ,'копейки' ,'копеек',	 1),
    array('рубль'   ,'рубля'   ,'рублей'    ,0),
    array('тысяча'  ,'тысячи'  ,'тысяч'     ,1),
    array('миллион' ,'миллиона','миллионов' ,0),
    array('миллиард','милиарда','миллиардов',0),
  );
  //
  list($rub,$kop) = explode(',',sprintf("%015.2f", floatval($num)));
  $out = array();
  if (intval($rub)>0) {
    foreach(str_split($rub,3) as $uk=>$v) { // by 3 symbols
      if (!intval($v)) continue;
      $uk = sizeof($unit)-$uk-1; // unit key
      $gender = $unit[$uk][3];
      list($i1,$i2,$i3) = array_map('intval',str_split($v,1));
      // mega-logic
      $out[] = $hundred[$i1]; # 1xx-9xx
      if ($i2>1) $out[]= $tens[$i2].' '.$ten[$gender][$i3]; # 20-99
      else $out[]= $i2>0 ? $a20[$i3] : $ten[$gender][$i3]; # 10-19 | 1-9
      // units without rub & kop
      if ($uk>1) $out[]= morph($v,$unit[$uk][0],$unit[$uk][1],$unit[$uk][2]);
    } //foreach
  }
  else $out[] = $nul;
  $out[] = morph(intval($rub), $unit[1][0],$unit[1][1],$unit[1][2]); // rub
  $out[] = $kop.' '.morph($kop,$unit[0][0],$unit[0][1],$unit[0][2]); // kop
  return trim(preg_replace('/ {2,}/', ' ', join(' ',$out)));
}
function num2str2($num) {
  $nul='ноль';
  $ten=array(
    array('','один','два','три','четыре','пять','шесть','семь', 'восемь','девять'),
    array('','одна','две','три','четыре','пять','шесть','семь', 'восемь','девять'),
  );
  $a20=array('десять','одиннадцать','двенадцать','тринадцать','четырнадцать' ,'пятнадцать','шестнадцать','семнадцать','восемнадцать','девятнадцать');
  $tens=array(2=>'двадцать','тридцать','сорок','пятьдесят','шестьдесят','семьдесят' ,'восемьдесят','девяносто');
  $hundred=array('','сто','двести','триста','четыреста','пятьсот','шестьсот', 'семьсот','восемьсот','девятьсот');
  $unit=array( // Units
    array('копейка' ,'копейки' ,'копеек',	 1),
    array('рубль'   ,'рубля'   ,'рублей'    ,0),
    array('тысяча'  ,'тысячи'  ,'тысяч'     ,1),
    array('миллион' ,'миллиона','миллионов' ,0),
    array('миллиард','милиарда','миллиардов',0),
  );
  //
  list($rub,$kop) = explode(',',sprintf("%015.2f", floatval($num)));
  $out = array();
  if (intval($rub)>0) {
    foreach(str_split($rub,3) as $uk=>$v) { // by 3 symbols
      if (!intval($v)) continue;
      $uk = sizeof($unit)-$uk-1; // unit key
      $gender = $unit[$uk][3];
      list($i1,$i2,$i3) = array_map('intval',str_split($v,1));
      // mega-logic
      $out[] = $hundred[$i1]; # 1xx-9xx
      if ($i2>1) $out[]= $tens[$i2].' '.$ten[$gender][$i3]; # 20-99
      else $out[]= $i2>0 ? $a20[$i3] : $ten[$gender][$i3]; # 10-19 | 1-9
      // units without rub & kop
      if ($uk>1) $out[]= morph($v,$unit[$uk][0],$unit[$uk][1],$unit[$uk][2]);
    } //foreach
  }
  else $out[] = $nul;
    $out[] = morph(intval($rub), $unit[1][0],$unit[1][1],$unit[1][2]); // rub
  $out[] = $kop.' '.morph($kop,$unit[0][0],$unit[0][1],$unit[0][2]); // kop
  return trim(preg_replace('/ {2,}/', ' ', join(' ',$out)));
}
function num2str22($num) {
  $nul='ноль';
  $ten=array(
    array('','один','два','три','четыре','пять','шесть','семь', 'восемь','девять'),
    array('','одна','две','три','четыре','пять','шесть','семь', 'восемь','девять'),
  );
  $a20=array('десять','одиннадцать','двенадцать','тринадцать','четырнадцать' ,'пятнадцать','шестнадцать','семнадцать','восемнадцать','девятнадцать');
  $tens=array(2=>'двадцать','тридцать','сорок','пятьдесят','шестьдесят','семьдесят' ,'восемьдесят','девяносто');
  $hundred=array('','сто','двести','триста','четыреста','пятьсот','шестьсот', 'семьсот','восемьсот','девятьсот');
  $unit=array( // Units
    array('копейка' ,'копейки' ,'копеек',	 1),
    array('рубль'   ,'рубля'   ,'рублей'    ,0),
    array('тысяча'  ,'тысячи'  ,'тысяч'     ,1),
    array('миллион' ,'миллиона','миллионов' ,0),
    array('миллиард','милиарда','миллиардов',0),
  );
  //
  list($rub,$kop) = explode(',',sprintf("%015.2f", floatval($num)));
  $out = array();
  if (intval($rub)>0) {
    foreach(str_split($rub,3) as $uk=>$v) { // by 3 symbols
      if (!intval($v)) continue;
      $uk = sizeof($unit)-$uk-1; // unit key
      $gender = $unit[$uk][3];
      list($i1,$i2,$i3) = array_map('intval',str_split($v,1));
      // mega-logic
      $out[] = $hundred[$i1]; # 1xx-9xx
      if ($i2>1) $out[]= $tens[$i2].' '.$ten[$gender][$i3]; # 20-99
      else $out[]= $i2>0 ? $a20[$i3] : $ten[$gender][$i3]; # 10-19 | 1-9
      // units without rub & kop
      if ($uk>1) $out[]= morph($v,$unit[$uk][0],$unit[$uk][1],$unit[$uk][2]);
    } //foreach
  }
  else $out[] = $nul;
    $out[] = morph(intval($rub)); // rub
  //$out[] = $kop.' '.morph($kop,$unit[0][0],$unit[0][1],$unit[0][2]); // kop
  return trim(preg_replace('/ {2,}/', ' ', join(' ',$out)));
}
function gen_act($service_id, $month, $year, $tesler = '') {
  global $db, $config, $_monthsList, $_monthsList2;
 require_once './adm/excel/vendor/autoload.php';

        $content = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `repairs` WHERE `app_date` REGEXP \''.mysqli_real_escape_string($db, $year.'.'.$month.'.').'\' and `service_id` = '.$service_id.' and `deleted` = 0;'));

        /*$sql3 = mysqli_query($db, 'SELECT * FROM `repairs` where `service_id` = '.$service_id.' and `app_date` REGEXP \''.mysqli_real_escape_string($db, $year.'.'.$month.'.').'\' and `status_admin` = \'Подтвержден\' and `deleted` = 0 order by `id` DESC ;');
        while ($row3 = mysqli_fetch_array($sql3)) {
        $model = model_info($row3['model_id']);

        if ($model['brand'] == 'TESLER') {
          $tesler = 1;
        }
        }  */

        $content['billing_log'] = get_payment_info_by_date($service_id, $year, $month, 1);
        $content['service_info'] = service_request_info($service_id);
        $content['billing_info'] = service_billing_info($service_id);
        if ($tesler != 1) {
        $new_file = './adm/excel/archive/'.translit2(preg_replace('/[^a-zA-Zа-яА-Я0-9]/ui', '',$content['service_info']['name']).'_'.$month.'_'.$year.'_акт.xlsx');
        copy('./adm/excel/payed.xlsx', $new_file);

        $xls = PHPExcel_IOFactory::load($new_file);
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();
        $sheet->setCellValue('B3', $content['billing_log']['id']);
        $sheet->setCellValue('D3', date("t", strtotime($_GET['year'].'-'.$_GET['month'].'-05')).' '.$_monthsList2[$_GET['month']].' '.$_GET['year'].' года.');
        $sheet->setCellValue('B5', $content['service_info']['name'].', ИНН '.$content['service_info']['inn'].', КПП '.$content['service_info']['kpp'].', '.$content['service_info']['adress'].', Р\С '.$content['billing_info']['sc2'].', '.$content['billing_info']['bank_name'].', БИК '.$content['billing_info']['bik'].', К\С '.$content['billing_info']['sc1'].', тел. '.$content['service_info']['phones']);
        $sheet->setCellValue('B9', $config['billing_info']);
        $sheet->setCellValue('B14', 'Договор организации сервисного обслуживания '.$content['billing_info']['agree']);
        $sheet->setCellValue('B17', 'Организация сервисного обслуживания за '.$_monthsList[$_GET['month']].' '.$_GET['year'].' года.');
        $sheet->setCellValue('G17', get_service_summ($service_id, $month, $year, 'HARPER').',00');
        $sheet->setCellValue('H17', get_service_summ($service_id, $month, $year, 'HARPER').',00');
        $sheet->setCellValue('H18', get_service_summ($service_id, $month, $year, 'HARPER').',00');
        $sheet->setCellValue('A20', 'Всего оказано услуг 1, на сумму '.get_service_summ($service_id, $month, $year, 'HARPER').',00 руб.');
        $sheet->setCellValue('A21', num2str(get_service_summ($service_id, $month, $year, 'HARPER')));
        if ($content['billing_info']['chp'] != 1) {
        $sheet->setCellValue('A28', 'Генеральный директор '.$content['service_info']['name']);
        } else {
        $sheet->setCellValue('A32', 'Индивидуальный предприниматель');
        $sheet->setCellValue('A28', $content['service_info']['name']);
        }
        $sheet->setCellValue('A30', $content['service_info']['req_gen_fio']);


       $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
       $objWriter->save($new_file);

       } else {
        $new_file = './adm/excel/archive/'.translit2(preg_replace('/[^a-zA-Zа-яА-Я0-9]/ui', '',$content['service_info']['name']).'_'.$month.'_'.$year.'_акт_2.xlsx');
        copy('./adm/excel/payed.xlsx', $new_file);

        $xls = PHPExcel_IOFactory::load($new_file);
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();
        $status_array = array(1 => 'Гарантийный', 3 => 'Повторный', 5 => 'Условно-гарантийный', 6 => 'Платный');
        $status_type = array(1 => 'Блочный', 2 => 'Компонентный', 3 => 'Платный', 4 => 'Справка', 5 => 'Замена аксессуара', 6 => 'Повторный', 7 => 'Диагностика');
        $sheet->setCellValue('B3', $content['billing_log']['id'].'-2');
        $sheet->setCellValue('D3', date("t", strtotime($_GET['year'].'-'.$_GET['month'].'-05')).' '.$_monthsList2[$_GET['month']].' '.$_GET['year'].' года.');
        $sheet->setCellValue('B5', $content['service_info']['name'].', ИНН '.$content['service_info']['inn'].', КПП '.$content['service_info']['kpp'].', '.$content['service_info']['adress'].', Р\С '.$content['billing_info']['sc2'].', '.$content['billing_info']['bank_name'].', БИК '.$content['billing_info']['bik'].', К\С '.$content['billing_info']['sc1'].', тел. '.$content['service_info']['phones']);
        $sheet->setCellValue('B9', $config['billing_info']);
        $sheet->setCellValue('B14', 'Договор организации сервисного  обслуживания '.$content['billing_info']['agree']);
        $sheet->setCellValue('B17', 'Организация сервисного обслуживания по бренду Tesler за '.$_monthsList[$_GET['month']].' '.$_GET['year'].' года.');
        $sheet->setCellValue('G17', get_service_summ($service_id, $month, $year, 'TESLER').',00');
        $sheet->setCellValue('H17', get_service_summ($service_id, $month, $year, 'TESLER').',00');
        $sheet->setCellValue('H18', get_service_summ($service_id, $month, $year, 'TESLER').',00');
        $sheet->setCellValue('A20', 'Всего оказано услуг 1, на сумму '.get_service_summ($service_id, $month, $year, 'TESLER').',00 руб.');
        $sheet->setCellValue('A21', num2str(get_service_summ($service_id, $month, $year, 'TESLER')));
        if ($content['billing_info']['chp'] != 1) {
        $sheet->setCellValue('A28', 'Генеральный директор '.$content['service_info']['name']);
        } else {
        $sheet->setCellValue('A32', 'Индивидуальный предприниматель');
        $sheet->setCellValue('A28', $content['service_info']['name']);
        }
        $sheet->setCellValue('A30', $content['service_info']['req_gen_fio']);


       $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
       $objWriter->save($new_file);
       }


}

function gen_bill($service_id, $month, $year, $brand = '') {
  global $db, $config, $_monthsList, $_monthsList2;
 require_once './adm/excel/vendor/autoload.php';

        $content = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `repairs` WHERE `app_date` REGEXP \''.mysqli_real_escape_string($db, $year.'.'.$month.'.').'\' and `service_id` = '.$service_id.' and `deleted` = 0;'));

        /*$sql3 = mysqli_query($db, 'SELECT * FROM `repairs` where `service_id` = '.$service_id.' and `app_date` REGEXP \''.mysqli_real_escape_string($db, $year.'.'.$month.'.').'\' and `status_admin` = \'Подтвержден\' and `deleted` = 0 order by `id` DESC ;');
        while ($row3 = mysqli_fetch_array($sql3)) {
        $model = model_info($row3['model_id']);

        if ($model['brand'] == 'TESLER') {
          $tesler = 1;
        }
        } */

        $content['billing_log'] = get_payment_info_by_date($service_id, $year, $month, 1);
        $content['service_info'] = service_request_info($service_id);
        $content['billing_info'] = service_billing_info($service_id);

        $lfcr = chr(10);

        if ($brand == 'harper') {
        $new_file = './adm/excel/archive/'.translit2(preg_replace('/[^a-zA-Zа-яА-Я0-9]/ui', '',$content['service_info']['name']).'_'.$month.'_'.$year.'_счет.xlsx');
        copy('./adm/excel/bill.xlsx', $new_file);

        $xls = PHPExcel_IOFactory::load($new_file);
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();
        $status_array = array(1 => 'Гарантийный', 3 => 'Повторный', 5 => 'Условно-гарантийный', 6 => 'Платный');
        $status_type = array(1 => 'Блочный', 2 => 'Компонентный', 3 => 'Платный', 4 => 'Справка', 5 => 'Замена аксессуара', 6 => 'Повторный', 7 => 'Диагностика');
        $sheet->setCellValue('B2', $content['billing_info']['bank_name']);
        $sheet->setCellValue('F2', $content['billing_info']['bik']);
        $sheet->setCellValue('F3', $content['billing_info']['sc1']);
        $sheet->setCellValue('F4', $content['billing_info']['sc2']);
        $sheet->setCellValue('B4', $content['service_info']['kpp']);
        $sheet->setCellValue('B3', $content['service_info']['inn']);
        $sheet->getStyle('B3') ->getNumberFormat() ->setFormatCode('0');
        $sheet->getStyle('B4') ->getNumberFormat() ->setFormatCode('0');
        $sheet->setCellValue('A8', 'Счёт на оплату № '.$content['billing_log']['id'].' от '.date("t", strtotime($year.'-'.$month.'-05')).' '.$_monthsList2[$month].' '.$year.' года. ');
        $sheet->setCellValue('B5', $content['service_info']['name']);
        $sheet->setCellValue('B11', $content['service_info']['name'].', ИНН '.$content['service_info']['inn'].', КПП '.$content['service_info']['kpp'].', '.$content['service_info']['adress'].', тел.'.$content['service_info']['phones']);
        $sheet->setCellValue('B14', $config['billing_info']);
        $sheet->setCellValue('B17', 'Вознаграждение за гарантийные, сервисные услуги по договору '.$content['billing_info']['agree']);
        $sheet->setCellValue('B20', 'Организация сервисного обслуживания за '.$_monthsList[$month].' '.$year.' года.');
        //if ($tesler == 1) {
        $sheet->setCellValue('E20', get_service_summ($service_id, $month, $year, 'HARPER'));
        $sheet->setCellValue('F20', get_service_summ($service_id, $month, $year, 'HARPER'));
        $sheet->setCellValue('F22', get_service_summ($service_id, $month, $year, 'HARPER'));
        $sheet->setCellValue('F24', get_service_summ($service_id, $month, $year, 'HARPER'));
        $sheet->setCellValue('E26', get_service_summ($service_id, $month, $year, 'HARPER').' руб.');
        $sheet->setCellValue('B27', num2str(get_service_summ($service_id, $month, $year, 'HARPER')));
        //}
        $sheet->setCellValue('B32', $content['service_info']['req_gen_fio']);
        if ($content['billing_info']['chp'] != 1) {
        $sheet->setCellValue('F32', $content['billing_info']['accountant']);
        } else {
        $sheet->setCellValue('A32', 'Индивидуальный предприниматель');
        $sheet->setCellValue('F32', '');
        $sheet->setCellValue('E32', '');
        $sheet->setCellValue('D32', '');
        $sheet->getStyle("E32")->applyFromArray(
            array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('rgb' => 'CCCCCC')
                    )
                )
            )
        );
        $sheet->getStyle("F32")->applyFromArray(
            array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('rgb' => 'CCCCCC')
                    )
                )
            )
        );
        }


       $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
       $objWriter->save($new_file);
       } else if ($brand == 'tesler') {
       $new_file = './adm/excel/archive/'.translit2(preg_replace('/[^a-zA-Zа-яА-Я0-9]/ui', '',$content['service_info']['name']).'_'.$month.'_'.$year.'_счет_2.xlsx');
        copy('./adm/excel/bill.xlsx', $new_file);

        $xls = PHPExcel_IOFactory::load($new_file);
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();
        $status_array = array(1 => 'Гарантийный', 3 => 'Повторный', 5 => 'Условно-гарантийный', 6 => 'Платный');
        $status_type = array(1 => 'Блочный', 2 => 'Компонентный', 3 => 'Платный', 4 => 'Справка', 5 => 'Замена аксессуара', 6 => 'Повторный', 7 => 'Диагностика');
        $sheet->setCellValue('B2', $content['billing_info']['bank_name']);
        $sheet->setCellValue('F2', $content['billing_info']['bik']);
        $sheet->setCellValue('F3', $content['billing_info']['sc1']);
        $sheet->setCellValue('F4', $content['billing_info']['sc2']);
        $sheet->setCellValue('B4', $content['service_info']['kpp']);
        $sheet->setCellValue('B3', $content['service_info']['inn']);
        $sheet->getStyle('B3') ->getNumberFormat() ->setFormatCode('0');
        $sheet->getStyle('B4') ->getNumberFormat() ->setFormatCode('0');
        $sheet->setCellValue('A8', 'Счёт на оплату № '.$content['billing_log']['id'].'-2 от '.date("t", strtotime($year.'-'.$month.'-05')).' '.$_monthsList2[$month].' '.$year.' года. ');
        $sheet->setCellValue('B5', $content['service_info']['name']);
        $sheet->setCellValue('B11', $content['service_info']['name'].', ИНН '.$content['service_info']['inn'].', КПП '.$content['service_info']['kpp'].', '.$content['service_info']['adress'].', тел.'.$content['service_info']['phones']);
        $sheet->setCellValue('B14', $config['billing_info']);
        $sheet->setCellValue('B17', 'Вознаграждение за гарантийные, сервисные услуги по договору '.$content['billing_info']['agree']);
        $sheet->setCellValue('B20', 'Организация сервисного обслуживания по бренду '.$brand.' за '.$_monthsList[$month].' '.$year.' года.');
        if ($brand != 'harper') {
        $sheet->setCellValue('E20', get_service_summ($service_id, $month, $year, strtoupper($brand)));
        $sheet->setCellValue('F20', get_service_summ($service_id, $month, $year, strtoupper($brand)));
        $sheet->setCellValue('F22', get_service_summ($service_id, $month, $year, strtoupper($brand)));
        $sheet->setCellValue('F24', get_service_summ($service_id, $month, $year, strtoupper($brand)));
        $sheet->setCellValue('E26', get_service_summ($service_id, $month, $year, strtoupper($brand)).' руб.');
        $sheet->setCellValue('B27', num2str(get_service_summ($service_id, $month, $year, strtoupper($brand))));
        }
        $sheet->setCellValue('B32', $content['service_info']['req_gen_fio']);
        if ($content['billing_info']['chp'] != 1) {
        $sheet->setCellValue('F32', $content['billing_info']['accountant']);
        } else {
        $sheet->setCellValue('A32', 'Индивидуальный предприниматель');
        $sheet->setCellValue('F32', '');
        $sheet->setCellValue('E32', '');
        $sheet->setCellValue('D32', '');
        $sheet->getStyle("E32")->applyFromArray(
            array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('rgb' => 'CCCCCC')
                    )
                )
            )
        );
        $sheet->getStyle("F32")->applyFromArray(
            array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('rgb' => 'CCCCCC')
                    )
                )
            )
        );
        }


       $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
       $objWriter->save($new_file);
       }  else  {
       $new_file = './adm/excel/archive/'.translit2(preg_replace('/[^a-zA-Zа-яА-Я0-9]/ui', '',$content['service_info']['name']).'_'.$month.'_'.$year.'_счет_3.xlsx');
        copy('./adm/excel/bill.xlsx', $new_file);

        $xls = PHPExcel_IOFactory::load($new_file);
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();
        $status_array = array(1 => 'Гарантийный', 3 => 'Повторный', 5 => 'Условно-гарантийный', 6 => 'Платный');
        $status_type = array(1 => 'Блочный', 2 => 'Компонентный', 3 => 'Платный', 4 => 'Справка', 5 => 'Замена аксессуара', 6 => 'Повторный', 7 => 'Диагностика');
        $sheet->setCellValue('B2', $content['billing_info']['bank_name']);
        $sheet->setCellValue('F2', $content['billing_info']['bik']);
        $sheet->setCellValue('F3', $content['billing_info']['sc1']);
        $sheet->setCellValue('F4', $content['billing_info']['sc2']);
        $sheet->setCellValue('B4', $content['service_info']['kpp']);
        $sheet->setCellValue('B3', $content['service_info']['inn']);
        $sheet->getStyle('B3') ->getNumberFormat() ->setFormatCode('0');
        $sheet->getStyle('B4') ->getNumberFormat() ->setFormatCode('0');
        $sheet->setCellValue('A8', 'Счёт на оплату № '.$content['billing_log']['id'].'-3 от '.date("t", strtotime($year.'-'.$month.'-05')).' '.$_monthsList2[$month].' '.$year.' года. ');
        $sheet->setCellValue('B5', $content['service_info']['name']);
        $sheet->setCellValue('B11', $content['service_info']['name'].', ИНН '.$content['service_info']['inn'].', КПП '.$content['service_info']['kpp'].', '.$content['service_info']['adress'].', тел.'.$content['service_info']['phones']);
        $sheet->setCellValue('B14', $config['billing_info']);
        $sheet->setCellValue('B17', 'Вознаграждение за гарантийные, сервисные услуги по договору '.$content['billing_info']['agree']);
        $sheet->setCellValue('B20', 'Организация сервисного обслуживания по бренду '.$brand.' за '.$_monthsList[$month].' '.$year.' года.');
        if ($brand != 'harper') {
        $sheet->setCellValue('E20', get_service_summ($service_id, $month, $year, strtoupper($brand)));
        $sheet->setCellValue('F20', get_service_summ($service_id, $month, $year, strtoupper($brand)));
        $sheet->setCellValue('F22', get_service_summ($service_id, $month, $year, strtoupper($brand)));
        $sheet->setCellValue('F24', get_service_summ($service_id, $month, $year, strtoupper($brand)));
        $sheet->setCellValue('E26', get_service_summ($service_id, $month, $year, strtoupper($brand)).' руб.');
        $sheet->setCellValue('B27', num2str(get_service_summ($service_id, $month, $year, strtoupper($brand))));
        }
        $sheet->setCellValue('B32', $content['service_info']['req_gen_fio']);
        if ($content['billing_info']['chp'] != 1) {
        $sheet->setCellValue('F32', $content['billing_info']['accountant']);
        } else {
        $sheet->setCellValue('A32', 'Индивидуальный предприниматель');
        $sheet->setCellValue('F32', '');
        $sheet->setCellValue('E32', '');
        $sheet->setCellValue('D32', '');
        $sheet->getStyle("E32")->applyFromArray(
            array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('rgb' => 'CCCCCC')
                    )
                )
            )
        );
        $sheet->getStyle("F32")->applyFromArray(
            array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('rgb' => 'CCCCCC')
                    )
                )
            )
        );
        }


       $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
       $objWriter->save($new_file);
       }

}


function gen_act2($service_id, $month, $year) {
  global $db, $config, $_monthsList, $_monthsList2;
 require_once './adm/excel/vendor/autoload.php';

        $content = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `repairs` WHERE `app_date` REGEXP \''.mysqli_real_escape_string($db, $year.'.'.$month.'.').'\' and `service_id` = '.$service_id.' and `deleted` = 0;'));

        $sql3 = mysqli_query($db, 'SELECT * FROM `repairs` where `service_id` = '.$service_id.' and `app_date` REGEXP \''.mysqli_real_escape_string($db, $year.'.'.$month.'.').'\' and `status_admin` = \'Подтвержден\' and `deleted` = 0 order by `id` DESC ;');
        while ($row3 = mysqli_fetch_array($sql3)) {
        $model = model_info($row3['model_id']);

        if ($model['brand'] == 'TESLER') {
          $tesler = 1;
        }
        }

        $content['billing_log'] = get_payment_info_by_date($service_id, $year, $month, 1);
        $content['service_info'] = service_request_info($service_id);
        $content['billing_info'] = service_billing_info($service_id);
        $lfcr = chr(10);

        $new_file = './adm/excel/archive/'.translit2(preg_replace('/[^a-zA-Zа-яА-Я0-9]/ui', '',$content['service_info']['name']).'_'.$month.'_'.$year.'_акт.xlsx');
        copy('./adm/excel/payed.xlsx', $new_file);

        $xls = PHPExcel_IOFactory::load($new_file);
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();
        $status_array = array(1 => 'Гарантийный', 3 => 'Повторный', 5 => 'Условно-гарантийный', 6 => 'Платный');
        $status_type = array(1 => 'Блочный', 2 => 'Компонентный', 3 => 'Платный', 4 => 'Справка', 5 => 'Замена аксессуара', 6 => 'Повторный', 7 => 'Диагностика');
        $sheet->setCellValue('B3', $content['billing_log']['id']);
        $sheet->setCellValue('D3', date("t", strtotime($_GET['year'].'-'.$_GET['month'].'-05')).' '.$_monthsList2[$_GET['month']].' '.$_GET['year'].' года.');
        $sheet->setCellValue('B5', $content['service_info']['name'].', ИНН '.$content['service_info']['inn'].', КПП '.$content['service_info']['kpp'].', '.$content['service_info']['adress'].', Р\С '.$content['billing_info']['sc2'].', '.$content['billing_info']['bank_name'].', БИК '.$content['billing_info']['bik'].', К\С '.$content['billing_info']['sc1'].', тел. '.$content['service_info']['phones']);
        $sheet->setCellValue('B9', $config['billing_info']);
        $sheet->setCellValue('B14', 'Договор организации сервисного обслуживания '.$content['billing_info']['agree']);
        $sheet->setCellValue('B17', 'Организация сервисного обслуживания за '.$_monthsList[$_GET['month']].' '.$_GET['year'].' года.');
        $sheet->setCellValue('G17', get_service_summ($service_id, $month, $year, 'HARPER').',00');
        $sheet->setCellValue('H17', get_service_summ($service_id, $month, $year, 'HARPER').',00');
        $sheet->setCellValue('H18', get_service_summ($service_id, $month, $year, 'HARPER').',00');
        $sheet->setCellValue('A20', 'Всего оказано услуг 1, на сумму '.get_service_summ($service_id, $month, $year, 'HARPER').',00 руб.');
        $sheet->setCellValue('A21', num2str(get_service_summ($service_id, $month, $year, 'HARPER')));
        if ($content['billing_info']['chp'] != 1) {
        $sheet->setCellValue('A28', 'Генеральный директор '.$content['service_info']['name']);
        } else {
        $sheet->setCellValue('A32', 'Индивидуальный предприниматель');
        $sheet->setCellValue('A28', $content['service_info']['name']);
        }
        $sheet->setCellValue('A30', $content['service_info']['req_gen_fio']);


       $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
       $objWriter->save($new_file);

       if ($tesler == 1) {
        $new_file = './adm/excel/archive/'.translit2(preg_replace('/[^a-zA-Zа-яА-Я0-9]/ui', '',$content['service_info']['name']).'_'.$month.'_'.$year.'_акт_2.xlsx');
        copy('./adm/excel/payed.xlsx', $new_file);

        $xls = PHPExcel_IOFactory::load($new_file);
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();
        $status_array = array(1 => 'Гарантийный', 3 => 'Повторный', 5 => 'Условно-гарантийный', 6 => 'Платный');
        $status_type = array(1 => 'Блочный', 2 => 'Компонентный', 3 => 'Платный', 4 => 'Справка', 5 => 'Замена аксессуара', 6 => 'Повторный', 7 => 'Диагностика');
        $sheet->setCellValue('B3', $content['billing_log']['id'].'-2');
        $sheet->setCellValue('D3', date("t", strtotime($_GET['year'].'-'.$_GET['month'].'-05')).' '.$_monthsList2[$_GET['month']].' '.$_GET['year'].' года.');
        $sheet->setCellValue('B5', $content['service_info']['name'].', ИНН '.$content['service_info']['inn'].', КПП '.$content['service_info']['kpp'].', '.$content['service_info']['adress'].', Р\С '.$content['billing_info']['sc2'].', '.$content['billing_info']['bank_name'].', БИК '.$content['billing_info']['bik'].', К\С '.$content['billing_info']['sc1'].', тел. '.$content['service_info']['phones']);
        $sheet->setCellValue('B9', $config['billing_info']);
        $sheet->setCellValue('B14', 'Договор организации сервисного обслуживания по бренду Tesler '.$content['billing_info']['agree']);
        $sheet->setCellValue('B17', 'Организация сервисного обслуживания за '.$_monthsList[$_GET['month']].' '.$_GET['year'].' года.');
        $sheet->setCellValue('G17', get_service_summ($service_id, $month, $year, 'TESLER').',00');
        $sheet->setCellValue('H17', get_service_summ($service_id, $month, $year, 'TESLER').',00');
        $sheet->setCellValue('H18', get_service_summ($service_id, $month, $year, 'TESLER').',00');
        $sheet->setCellValue('A20', 'Всего оказано услуг 1, на сумму '.get_service_summ($service_id, $month, $year, 'TESLER').',00 руб.');
        $sheet->setCellValue('A21', num2str(get_service_summ($service_id, $month, $year, 'TESLER')));
        if ($content['billing_info']['chp'] != 1) {
        $sheet->setCellValue('A28', 'Генеральный директор '.$content['service_info']['name']);
        } else {
        $sheet->setCellValue('A32', 'Индивидуальный предприниматель');
        $sheet->setCellValue('A28', $content['service_info']['name']);
        }
        $sheet->setCellValue('A30', $content['service_info']['req_gen_fio']);


       $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
       $objWriter->save($new_file);
       }


}

function gen_bill2($service_id, $month, $year) {
  global $db, $config, $_monthsList, $_monthsList2;
 require_once './adm/excel/vendor/autoload.php';

        $content = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `repairs` WHERE `app_date` REGEXP \''.mysqli_real_escape_string($db, $year.'.'.$month.'.').'\' and `service_id` = '.$service_id.' and `deleted` = 0;'));

        $sql3 = mysqli_query($db, 'SELECT * FROM `repairs` where `service_id` = '.$service_id.' and `app_date` REGEXP \''.mysqli_real_escape_string($db, $year.'.'.$month.'.').'\' and `status_admin` = \'Подтвержден\' and `deleted` = 0 order by `id` DESC ;');
        while ($row3 = mysqli_fetch_array($sql3)) {
        $model = model_info($row3['model_id']);

        if ($model['brand'] == 'TESLER') {
          $tesler = 1;
        }
        }

        $content['billing_log'] = get_payment_info_by_date($service_id, $year, $month, 1);
        $content['service_info'] = service_request_info($service_id);
        $content['billing_info'] = service_billing_info($service_id);

        $lfcr = chr(10);


        $new_file = './adm/excel/archive/'.translit2(preg_replace('/[^a-zA-Zа-яА-Я0-9]/ui', '',$content['service_info']['name']).'_'.$month.'_'.$year.'_счет.xlsx');
        copy('./adm/excel/bill.xlsx', $new_file);

        $xls = PHPExcel_IOFactory::load($new_file);
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();
        $status_array = array(1 => 'Гарантийный', 3 => 'Повторный', 5 => 'Условно-гарантийный', 6 => 'Платный');
        $status_type = array(1 => 'Блочный', 2 => 'Компонентный', 3 => 'Платный', 4 => 'Справка', 5 => 'Замена аксессуара', 6 => 'Повторный', 7 => 'Диагностика');
        $sheet->setCellValue('B2', $content['billing_info']['bank_name']);
        $sheet->setCellValue('F2', $content['billing_info']['bik']);
        $sheet->setCellValue('F3', $content['billing_info']['sc1']);
        $sheet->setCellValue('F4', $content['billing_info']['sc2']);
        $sheet->setCellValue('B4', $content['service_info']['kpp']);
        $sheet->setCellValue('B3', $content['service_info']['inn']);
        $sheet->getStyle('B3') ->getNumberFormat() ->setFormatCode('0');
        $sheet->getStyle('B4') ->getNumberFormat() ->setFormatCode('0');
        $sheet->setCellValue('A8', 'Счёт на оплату № '.$content['billing_log']['id'].' от '.date("t", strtotime($_GET['year'].'-'.$_GET['month'].'-05')).' '.$_monthsList2[$_GET['month']].' '.$_GET['year'].' года. ');
        $sheet->setCellValue('B5', $content['service_info']['name']);
        $sheet->setCellValue('B11', $content['service_info']['name'].', ИНН '.$content['service_info']['inn'].', КПП '.$content['service_info']['kpp'].', '.$content['service_info']['adress'].', тел.'.$content['service_info']['phones']);
        $sheet->setCellValue('B14', $config['billing_info']);
        $sheet->setCellValue('B17', 'Вознаграждение за гарантийные, сервисные услуги по договору '.$content['billing_info']['agree']);
        $sheet->setCellValue('B20', 'Организация сервисного обслуживания за '.$_monthsList[$_GET['month']].' '.$_GET['year'].' года.');
       // if ($tesler == 1) {
        $sheet->setCellValue('E20', get_service_summ($service_id, $month, $year, 'HARPER'));
        $sheet->setCellValue('F20', get_service_summ(User::getData('id'), $month, $year, 'HARPER'));
        $sheet->setCellValue('F22', get_service_summ($service_id, $month, $year, 'HARPER'));
        $sheet->setCellValue('F24', get_service_summ($service_id, $month, $year, 'HARPER'));
        $sheet->setCellValue('E26', get_service_summ($service_id, $month, $year, 'HARPER').' руб.');
        $sheet->setCellValue('B27', num2str(get_service_summ($service_id, $month, $year, 'HARPER')));
        //}
        $sheet->setCellValue('B32', $content['service_info']['req_gen_fio']);
        if ($content['billing_info']['chp'] != 1) {
        $sheet->setCellValue('F32', $content['billing_info']['accountant']);
        } else {
        $sheet->setCellValue('A32', 'Индивидуальный предприниматель');
        $sheet->setCellValue('F32', '');
        $sheet->setCellValue('E32', '');
        $sheet->setCellValue('D32', '');
        $sheet->getStyle("E32")->applyFromArray(
            array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('rgb' => 'CCCCCC')
                    )
                )
            )
        );
        $sheet->getStyle("F32")->applyFromArray(
            array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('rgb' => 'CCCCCC')
                    )
                )
            )
        );
        }


       $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
       $objWriter->save($new_file);
       if ($tesler == 1) {
       $new_file = './adm/excel/archive/'.translit2(preg_replace('/[^a-zA-Zа-яА-Я0-9]/ui', '',$content['service_info']['name']).'_'.$month.'_'.$year.'_счет_2.xlsx');
        copy('./adm/excel/bill.xlsx', $new_file);

        $xls = PHPExcel_IOFactory::load($new_file);
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();
        $status_array = array(1 => 'Гарантийный', 3 => 'Повторный', 5 => 'Условно-гарантийный', 6 => 'Платный');
        $status_type = array(1 => 'Блочный', 2 => 'Компонентный', 3 => 'Платный', 4 => 'Справка', 5 => 'Замена аксессуара', 6 => 'Повторный', 7 => 'Диагностика');
        $sheet->setCellValue('B2', $content['billing_info']['bank_name']);
        $sheet->setCellValue('F2', $content['billing_info']['bik']);
        $sheet->setCellValue('F3', $content['billing_info']['sc1']);
        $sheet->setCellValue('F4', $content['billing_info']['sc2']);
        $sheet->setCellValue('B4', $content['service_info']['kpp']);
        $sheet->setCellValue('B3', $content['service_info']['inn']);
        $sheet->getStyle('B3') ->getNumberFormat() ->setFormatCode('0');
        $sheet->getStyle('B4') ->getNumberFormat() ->setFormatCode('0');
        $sheet->setCellValue('A8', 'Счёт на оплату № '.$content['billing_log']['id'].'-2 от '.date("t", strtotime($_GET['year'].'-'.$_GET['month'].'-05')).' '.$_monthsList2[$_GET['month']].' '.$_GET['year'].' года. ');
        $sheet->setCellValue('B5', $content['service_info']['name']);
        $sheet->setCellValue('B11', $content['service_info']['name'].', ИНН '.$content['service_info']['inn'].', КПП '.$content['service_info']['kpp'].', '.$content['service_info']['adress'].', тел.'.$content['service_info']['phones']);
        $sheet->setCellValue('B14', $config['billing_info']);
        $sheet->setCellValue('B17', 'Вознаграждение за гарантийные, сервисные услуги по договору '.$content['billing_info']['agree']);
        $sheet->setCellValue('B20', 'Организация сервисного обслуживания по бренду Tesler за '.$_monthsList[$_GET['month']].' '.$_GET['year'].' года.');
        if ($tesler == 1) {
        $sheet->setCellValue('E20', get_service_summ($service_id, $month, $year, 'TESLER'));
        $sheet->setCellValue('F20', get_service_summ(User::getData('id'), $month, $year, 'TESLER'));
        $sheet->setCellValue('F22', get_service_summ($service_id, $month, $year, 'TESLER'));
        $sheet->setCellValue('F24', get_service_summ($service_id, $month, $year, 'TESLER'));
        $sheet->setCellValue('E26', get_service_summ($service_id, $month, $year, 'TESLER').' руб.');
        $sheet->setCellValue('B27', num2str(get_service_summ($service_id, $month, $year, 'TESLER')));
        }
        $sheet->setCellValue('B32', $content['service_info']['req_gen_fio']);
        if ($content['billing_info']['chp'] != 1) {
        $sheet->setCellValue('F32', $content['billing_info']['accountant']);
        } else {
        $sheet->setCellValue('A32', 'Индивидуальный предприниматель');
        $sheet->setCellValue('F32', '');
        $sheet->setCellValue('E32', '');
        $sheet->setCellValue('D32', '');
        $sheet->getStyle("E32")->applyFromArray(
            array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('rgb' => 'CCCCCC')
                    )
                )
            )
        );
        $sheet->getStyle("F32")->applyFromArray(
            array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('rgb' => 'CCCCCC')
                    )
                )
            )
        );
        }


       $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
       $objWriter->save($new_file);
       }

}
function morph($n, $f1, $f2, $f5) {
  $n = abs(intval($n)) % 100;
  if ($n>10 && $n<20) return $f5;
  $n = $n % 10;
  if ($n>1 && $n<5) return $f2;
  if ($n==1) return $f1;
  return $f5;
}

function get_payment_info($id) {
  global $config, $db;
$sql = mysqli_query($db, 'SELECT * FROM `pay_billing` WHERE `id` = '.$id.';');
return mysqli_fetch_array($sql);
}

function get_document_info($id) {
  global $config, $db;
$sql = mysqli_query($db, 'SELECT * FROM `services_documents` WHERE `id` = '.$id.';');
return mysqli_fetch_array($sql);
}


function brand_by_id($id) {
  global $config, $db;
$sql = mysqli_query($db, 'SELECT * FROM `brands` WHERE `id` = '.$id.';');
return mysqli_fetch_array($sql);
}

function get_payment_info_by_date($service_id, $year, $month, $type) {
  global $config, $db;
$sql = mysqli_query($db, 'SELECT * FROM `pay_billing` WHERE `service_id` = '.$service_id.' and `year` = '.$year.' and `month` = '.$month.' and `type` = '.$type.';');
return mysqli_fetch_array($sql);
}

function create_or_get_payment_id($service_id, $year, $month, $type)
{
  global $db;
  $sql = mysqli_query($db, 'SELECT * FROM `pay_billing` WHERE `service_id` = ' . $service_id . ' and `year` = ' . $year . ' and `month` = ' . $month . ' and `type` = ' . $type . ' limit 1;');
  if (mysqli_num_rows($sql) > 0) {
    return mysqli_fetch_array($sql);
  }
  mysqli_query($db, 'INSERT INTO `pay_billing` (
    `service_id`,
    `year`,
    `month`,
    `type`
    ) VALUES (
    \'' . mysqli_real_escape_string($db, $service_id) . '\',
    \'' . mysqli_real_escape_string($db, $year) . '\',
    \'' . mysqli_real_escape_string($db, $month) . '\',
    \'' . mysqli_real_escape_string($db, $type) . '\'
    );') or mysqli_error($db);
  $id = mysqli_insert_id($db);
  return get_payment_info($id);
}

function create_or_get_document($service_id, $document_id) {
  global $db;

$sql = mysqli_query($db, 'SELECT id FROM `services_documents` WHERE `service_id` = '.$service_id.' and `document_id` = '.$document_id.';');
if (mysqli_num_rows($sql) > 0) {
    return get_document_info(mysqli_fetch_array($sql)['id']);

} else {
    mysqli_query($db, 'INSERT INTO `services_documents` (
    `service_id`,
    `document_id`,
    `status`
    ) VALUES (
    \''.mysqli_real_escape_string($db, $service_id).'\',
    \''.mysqli_real_escape_string($db, $document_id).'\',
    \'Нет\'
    );') or mysqli_error($db);
    $id = mysqli_insert_id($db);
    return get_document_info($id);
}

}

function create_documents($service_id) {
  global $db;

$sql = mysqli_query($db, 'SELECT * FROM `documents`;');
      while ($row = mysqli_fetch_array($sql)) {
        $document = create_or_get_document($service_id, $row['id']);
      }
}



function get_service_summ($user_id, $month, $year, $brand = '', $server_user_id = '', $billing_manager = '')
{
  global $db;
  $ids = [];
  $summ = 0;
  $date_current = new DateTime("01/" . $month . "/" . $year);
  $date_from    = new DateTime("01/10/2019");
  $date_returns    = new DateTime("01/05/2020");
  $checkReturnsFlag  = ($date_current < $date_returns) ? true : false;
  $horizont_array = array('ЗЭБТ-Harper', 'Horizont', 'Hartens', 'ЗЭБТ-Горизонт', 'Белит-Горизонт', 'OK', 'ЗЭБТ-HARTENS', 'ЗЭБТ-Skyworth', 'ЗЭБТ-Prestigio', 'ROSENLEW');

  if ($date_current >= $date_from) {
    $harper_brand = array('HARPER', 'OLTO', 'SKYLINE', 'NESONS');
    if (true || $billing_manager == 1) {
      $no_for_money = ' and `status_id` != 6 ';
    }
  } else {
    $harper_brand = array('HARPER', 'OLTO');
  }
  $sql = mysqli_query($db, 'SELECT `return_id`,`id`,`model_id`,`total_price`, `parts_cost`, `transport_cost`,`status_admin`,`onway`,`onway_type`, `master_user_id` FROM `repairs` WHERE `app_date` REGEXP \'' . mysqli_real_escape_string($db, $year . '.' . $month . '.') . '\' and `deleted` = 0 and (`status_admin` = \'Подтвержден\' or `status_admin` = \'Выдан\') and  `service_id` = ' . $user_id . ' ' . $no_for_money . ';');
  while ($row = mysqli_fetch_array($sql)) {
    //$info = get_request_info($row['id']);
    //$city = get_city($info['city']);
    // HERE!
    if (!$checkReturnsFlag || (check_returns_pls($row['return_id']) || $row['return_id'] == 0)) {
      
      $model = model_info($row['model_id']);
      //$parts_info = repairs_parts_info($row['id']);
      if ($brand == 'TESLER') {
        if ($model['brand'] == 'TESLER') {
          $summ += $row['total_price'];
          $summ += $row['parts_cost'];
          $summ += $row['transport_cost'];
          $ids[] = $row['id'];
        }
      } else if ($brand == 'ROCH') {
        if ($model['brand'] == 'ROCH') {
          $summ += $row['total_price'];
          $summ += $row['parts_cost'];
          $summ += $row['transport_cost'];
          $ids[] = $row['id'];
        }
      } else if ($brand == 'SELENGA') {
        if ($model['brand'] == 'SELENGA') {
          $summ += $row['total_price'];
          $summ += $row['parts_cost'];
          $summ += $row['transport_cost'];
          $ids[] = $row['id'];
        }
      } else if ($brand == 'HORIZONT') {
        if (in_array($model['brand'], $horizont_array)) {
          $summ += $row['total_price'];
          $summ += $row['parts_cost'];
          $summ += $row['transport_cost'];
          $ids[] = $row['id'];
        }
      } else if ($brand == 'SVEN') {
        if ($model['brand'] == 'SVEN') {
          $summ += $row['total_price'];
          $summ += $row['parts_cost'];
          $summ += $row['transport_cost'];
          $ids[] = $row['id'];
        }
      } else if ($brand == 'HARPER') {
        if (in_array($model['brand'], $harper_brand)) {
          if ($date_current >= $date_from) {
            if ($row['master_user_id'] <= 0 && ($user_id == 33 || $server_user_id == 33)) {
            } else {
              $summ += $row['total_price'];
              $summ += $row['parts_cost'];
              $summ += $row['transport_cost'];
              $ids[] = $row['id'];
            }
          } else {
            $summ += $row['total_price'];
            $summ += $row['parts_cost'];
            $summ += $row['transport_cost'];
            $ids[] = $row['id'];
          }
        }
      } else {
        if ($model['brand'] != 'TESLER' && $model['brand'] != 'SELENGA' && !in_array($model['brand'], $horizont_array)) {
          $summ += $row['total_price'];
          $summ += $row['parts_cost'];
          $summ += $row['transport_cost'];
          $ids[] = $row['id'];
        } else {
          $summ += $row['total_price'];
          $summ += $row['parts_cost'];
          $summ += $row['transport_cost'];
          $ids[] = $row['id'];
        }
      }
    }
  }
/*    echo '<pre>';
  print_r(implode(',',$ids));
  echo '</pre>';
  exit;  */
  return $summ;
}


function get_service_summ_without_payed($user_id, $month, $year, $brand = '', $server_user_id = '', $billing_manager = '')
{
  global $db;
  $summ = 0;
  $horizont_array = array('ЗЭБТ-Harper', 'Horizont', 'Hartens', 'ЗЭБТ-Горизонт', 'Белит-Горизонт', 'OK', 'ЗЭБТ-HARTENS', 'ЗЭБТ-Skyworth', 'ЗЭБТ-Prestigio', 'ROSENLEW');

  $date_current = new DateTime("01/" . $month . "/" . $year);
  $date_from    = new DateTime("01/10/2019");

  if ($date_current >= $date_from) {
    $harper_brand = array('HARPER', 'OLTO', 'SKYLINE', 'NESONS');
    $no_for_money = ' and `status_id` = 6 ';
  } else {
    $harper_brand = array('HARPER', 'OLTO');
  }
  $sql = mysqli_query($db, 'SELECT `return_id`,`id`,`model_id`,`transport_cost`, `parts_cost`, `install_cost`, `dismant_cost`,`total_price`,`status_admin`,`onway`,`onway_type`, `master_user_id` FROM `repairs` WHERE `app_date` REGEXP \'' . mysqli_real_escape_string($db, $year . '.' . $month . '.') . '\' and `deleted` = 0 and (`status_admin` = \'Подтвержден\' or `status_admin` = \'Выдан\') and  `service_id` = ' . $user_id . ' ' . $no_for_money . ';');
  while ($row = mysqli_fetch_array($sql)) {
    $cost = $row['total_price'] + $row['parts_cost'] + $row['transport_cost'] + $row['dismant_cost'] + $row['install_cost'];
    if ($row['return_id'] == 0 || check_returns_pls($row['return_id'])) {
      $model = model_info($row['model_id']);
      if ($brand == 'TESLER') {
        if ($model['brand'] == 'TESLER') {
          $summ += $cost;
        }
      } else if ($brand == 'SELENGA') {
        if ($model['brand'] == 'SELENGA') {
          $summ += $cost;
        }
      } else if ($brand == 'HORIZONT') {
        if (in_array($model['brand'], $horizont_array)) {
          $summ += $cost;
        }
      } else if ($brand == 'SVEN') {
        if ($model['brand'] == 'SVEN') {
          $summ += $cost;
        }
      } else if ($brand == 'HARPER') {
        if (in_array($model['brand'], $harper_brand)) {
          if ($date_current >= $date_from) {
            if ($row['master_user_id'] <= 0 && ($user_id == 33 || $server_user_id == 33)) {
            } else {
              $summ += $cost;
            }
          } else {
            $summ += $cost;
          }
        }
      } else {
        if ($model['brand'] != 'TESLER' && !in_array($model['brand'], $horizont_array)) {
          $summ += $cost;
        } else {
          $summ += $cost;
        }
      }
    }
  }
  return $summ;
}


function get_service_summ_fast($user_id, $month, $year, $brand = '', $server_user_id = '', $notPaidFlag = false)
{
  global $db;
  $summ = 0;
  $horizont_array = ['ЗЭБТ-Harper', 'Horizont', 'Hartens', 'ЗЭБТ-Горизонт', 'Белит-Горизонт', 'OK', 'ЗЭБТ-HARTENS', 'ЗЭБТ-Skyworth', 'ЗЭБТ-Prestigio', 'ROSENLEW'];
  $date_current = new DateTime("01/" . $month . "/" . $year);
  $date_from    = new DateTime("01/10/2019");
  $date_returns    = new DateTime("01/05/2020");
  $checkReturnsFlag  = ($date_current < $date_returns) ? true : false;
  if ($date_current >= $date_from) {
    $harper_brand = ['HARPER', 'OLTO', 'SKYLINE', 'NESONS'];
  } else {
    $harper_brand = ['HARPER', 'OLTO'];
  }
  $sql = mysqli_query($db, 'SELECT `return_id`, `id`, `model_id`, `transport_cost`, `parts_cost`, `install_cost`, `dismant_cost`, `total_price`, `status_admin`, `onway`, `onway_type`, `master_user_id` FROM `repairs` WHERE `app_date` REGEXP \'' . mysqli_real_escape_string($db, $year . '.' . $month . '.') . '\' AND `deleted` = 0 AND (`status_admin` = "Подтвержден" or `status_admin` = "Выдан") '.(($notPaidFlag) ? 'AND `status_id` != 6' : '').' and `service_id` = ' . $user_id);
  while ($row = mysqli_fetch_array($sql)) {
    $model = model_info($row['model_id']);
    $cost = $row['total_price'] + $row['parts_cost'] + $row['transport_cost'] + $row['dismant_cost'] + $row['install_cost'];
    if (!$checkReturnsFlag  || $row['return_id'] == 0 || (check_returns_pls($row['return_id']))) {
      if ($brand == 'TESLER') {
        if ($model['brand'] == 'TESLER') {
          $summ += $cost;
          mysqli_query($db, 'UPDATE `pay_billing` SET `sum` = ' . $summ . ' WHERE `service_id` = ' . $user_id . ' and `year` = ' . $year . ' and `month` = ' . $month . ' and `type` = 4;') or mysqli_error($db);
        }
      } else if ($brand == 'SELENGA') {
        if ($model['brand'] == 'SELENGA') {
          $summ += $cost;
          mysqli_query($db, 'UPDATE `pay_billing` SET `sum` = ' . $summ . ' WHERE `service_id` = ' . $user_id . ' and `year` = ' . $year . ' and `month` = ' . $month . ' and `type` = 14;') or mysqli_error($db);
        }
      } else if ($brand == 'ROCH') {
        if ($model['brand'] == 'ROCH') {
          $summ += $cost;
          mysqli_query($db, 'UPDATE `pay_billing` SET `sum` = ' . $summ . ' WHERE `service_id` = ' . $user_id . ' and `year` = ' . $year . ' and `month` = ' . $month . ' and `type` = 16;') or mysqli_error($db);
        }
      } else if ($brand == 'HORIZONT') {
        if (in_array($model['brand'], $horizont_array)) {
          $summ += $cost;
          mysqli_query($db, 'UPDATE `pay_billing` SET `sum` = ' . $summ . ' WHERE `service_id` = ' . $user_id . ' and `year` = ' . $year . ' and `month` = ' . $month . ' and `type` = 12;') or mysqli_error($db);
        }
      } else if ($brand == 'SVEN') {
        if ($model['brand'] == 'SVEN') {
          $summ += $cost;
          mysqli_query($db, 'UPDATE `pay_billing` SET `sum` = ' . $summ . ' WHERE `service_id` = ' . $user_id . ' and `year` = ' . $year . ' and `month` = ' . $month . ' and `type` = 10;') or mysqli_error($db);
        }
      } else if ($brand == 'HARPER') {
        if (in_array($model['brand'], $harper_brand)) {
          if ($date_current >= $date_from) {
            if ($row['master_user_id'] <= 0 && ($user_id == 33 || $server_user_id == 33)) {
           } else { 
              $summ += $cost;
              mysqli_query($db, 'UPDATE `pay_billing` SET `sum` = ' . $summ . ' WHERE `service_id` = ' . $user_id . ' and `year` = ' . $year . ' and `month` = ' . $month . ' and `type` = 2;') or mysqli_error($db);
            }
          } else {
            $summ += $cost;
            mysqli_query($db, 'UPDATE `pay_billing` SET `sum` = ' . $summ . ' WHERE `service_id` = ' . $user_id . ' and `year` = ' . $year . ' and `month` = ' . $month . ' and `type` = 2;') or mysqli_error($db);
          }
        }
      } else {
        if ($model['brand'] != 'TESLER' && $model['brand'] != 'SELENGA' && !in_array($model['brand'], $horizont_array) && $model['brand'] != 'SVEN') {
          $summ += $cost;
          mysqli_query($db, 'UPDATE `pay_billing` SET `sum` = ' . $summ . ' WHERE `service_id` = ' . $user_id . ' and `year` = ' . $year . ' and `month` = ' . $month . ' and `type` = 2;') or mysqli_error($db);
        } else {
          $summ += $cost;
          mysqli_query($db, 'UPDATE `pay_billing` SET `sum` = ' . $summ . ' WHERE `service_id` = ' . $user_id . ' and `year` = ' . $year . ' and `month` = ' . $month . ' and `type` = 4;') or mysqli_error($db);
        }
      }
    }
  }
  return $summ;
}


function get_service_summ_stat($user_id, $month, $year, $brand = '') {
  global $db;

$sql = mysqli_query($db, 'SELECT `return_id`,`id`,`model_id`,`total_price`,`status_admin`,`onway`,`onway_type` FROM `repairs` WHERE `app_date` REGEXP \''.mysqli_real_escape_string($db, $year.'.'.$month.'.').'\' and `deleted` = 0 and (`status_admin` = \'Подтвержден\' or `status_admin` = \'Выдан\') and  `service_id` = '.$user_id.';');
      while ($row = mysqli_fetch_array($sql)) {
      //$info = get_request_info($row['id']);
      //$city = get_city($info['city']);

       $model = model_info($row['model_id']);
       //$parts_info = repairs_parts_info($row['id']);
       if ($brand == 'TESLER') {
       //echo $row['id'].'<br>';
       if ($model['brand'] == 'TESLER') {
         //echo $row['id'].'-tesler';
        $summ += $row['total_price'];
        $summ += $parts_info['sum'];
        $summ += parts_price_billing($row['id']);
        $summ += (($row['status_admin'] != 'Запрос на выезд' && $row['onway'] == 1) ? get_price_transfer($model['cat'], $row['onway_type'], $user_id) : '0');


       }

       } else if ($brand == 'HARPER') {

       if ($model['brand'] == 'HARPER' || $model['brand'] == 'OLTO' || $model['brand'] == 'NESONS') {
          //file_put_contents('ids.txt', $row['id'].PHP_EOL, FILE_APPEND);
         //echo $row['id'].'-tesler';
        $summ += $row['total_price'];
        $summ += $parts_info['sum'];
        $summ += parts_price_billing($row['id']);
        $summ += (($row['status_admin'] != 'Запрос на выезд' && $row['onway'] == 1) ? get_price_transfer($model['cat'], $row['onway_type'], $user_id) : '0');


       }

       } else {
       if ($model['brand'] != 'TESLER') {

        $summ += $row['total_price'];
        $summ += $parts_info['sum'];
        $summ += parts_price_billing($row['id']);
        $summ += (($row['status_admin'] != 'Запрос на выезд' && $row['onway'] == 1) ? get_price_transfer($model['cat'], $row['onway_type'], $user_id) : '0');
       } else {
          //echo $row['id'].'-!tesler';
        $summ += $row['total_price'];
        $summ += $parts_info['sum'];
        $summ += parts_price_billing($row['id']);
        $summ += (($row['status_admin'] != 'Запрос на выезд' && $row['onway'] == 1) ? get_price_transfer($model['cat'], $row['onway_type'], $user_id) : '0');


       }




       }


      }


    //file_put_contents('ids.txt', );
    return $summ;
}

function get_service_summ_stat_appdate($user_id, $month, $year, $brand = '') {
  global $db;

$sql = mysqli_query($db, 'SELECT `return_id`,`id`,`model_id`,`total_price`,`status_admin`,`onway`,`onway_type` FROM `repairs` WHERE `master_app_date` REGEXP \''.mysqli_real_escape_string($db, $year.'.'.$month.'.').'\' and `deleted` = 0 and (`status_admin` = \'Подтвержден\' or `status_admin` = \'Выдан\') and  `service_id` = '.$user_id.';');
      while ($row = mysqli_fetch_array($sql)) {
      //$info = get_request_info($row['id']);
      //$city = get_city($info['city']);

       $model = model_info($row['model_id']);
       //$parts_info = repairs_parts_info($row['id']);
       if ($brand == 'TESLER') {
       //echo $row['id'].'<br>';
       if ($model['brand'] == 'TESLER') {
         //echo $row['id'].'-tesler';
        $summ += $row['total_price'];
        //$summ += $parts_info['summ'];
        //$summ += parts_price_billing($row['id']);
        //$summ += (($row['status_admin'] != 'Запрос на выезд' && $row['onway'] == 1) ? get_price_transfer($model['cat'], $row['onway_type']) : '0');


       }

       } else if ($brand == 'HARPER') {

       if ($model['brand'] == 'HARPER' || $model['brand'] == 'OLTO' || $model['brand'] == 'NESONS') {
          //file_put_contents('ids.txt', $row['id'].PHP_EOL, FILE_APPEND);
         //echo $row['id'].'-tesler';
        $summ += $row['total_price'];
        //$summ += $parts_info['summ'];
        //$summ += parts_price_billing($row['id']);
        //$summ += (($row['status_admin'] != 'Запрос на выезд' && $row['onway'] == 1) ? get_price_transfer($model['cat'], $row['onway_type']) : '0');


       }

       } else {
       if ($model['brand'] != 'TESLER') {

        $summ += $row['total_price'];
        //$summ += $parts_info['summ'];
        //$summ += parts_price_billing($row['id']);
        //$summ += (($row['status_admin'] != 'Запрос на выезд' && $row['onway'] == 1) ? get_price_transfer($model['cat'], $row['onway_type']) : '0');
       } else {
          //echo $row['id'].'-!tesler';
        $summ += $row['total_price'];
       // $summ += $parts_info['summ'];
       // $summ += parts_price_billing($row['id']);
       // $summ += (($row['status_admin'] != 'Запрос на выезд' && $row['onway'] == 1) ? get_price_transfer($model['cat'], $row['onway_type']) : '0');


       }




       }


      }


    //file_put_contents('ids.txt', );
    return $summ;
}

function get_service_summ_master($user_id, $month, $year, $master_id) {
  global $db;

$sql = mysqli_query($db, 'SELECT `return_id`, `id`,`model_id`,`total_price`,`status_admin`,`onway`,`onway_type` FROM `repairs` WHERE `app_date` REGEXP \''.mysqli_real_escape_string($db, $year.'.'.$month.'.').'\' and `deleted` = 0 and (`status_admin` = \'Подтвержден\' or `status_admin` = \'Выдан\') and  `service_id` = '.$user_id.' and `master_user_id` = '.$master_id.';');
      while ($row = mysqli_fetch_array($sql)) {
      //$info = get_request_info($row['id']);
      //$city = get_city($info['city']);

       if (check_returns_pls($row['return_id']) || $row['return_id'] == 0) {

       $model = model_info($row['model_id']);
       //$parts_info = repairs_parts_info($row['id']);
       if ($brand == 'TESLER') {

       if ($model['brand'] == 'TESLER') {

         //echo $row['id'].'-tesler';
        $summ += $row['total_price'];
       // $summ += $parts_info['summ'];
       // $summ += parts_price_billing($row['id']);
       // $summ += (($row['status_admin'] != 'Запрос на выезд' && $row['onway'] == 1) ? get_price_transfer($model['cat'], $row['onway_type']) : '0');


       }

       } else if ($brand == 'HARPER') {

       if ($model['brand'] == 'HARPER' || $model['brand'] == 'OLTO' || $model['brand'] == 'NESONS') {
          //file_put_contents('ids.txt', $row['id'].PHP_EOL, FILE_APPEND);
         //echo $row['id'].'-tesler';
        $summ += $row['total_price'];
       // $summ += $parts_info['summ'];
       // $summ += parts_price_billing($row['id']);
        //$summ += (($row['status_admin'] != 'Запрос на выезд' && $row['onway'] == 1) ? get_price_transfer($model['cat'], $row['onway_type']) : '0');


       }

       } else {
       if ($model['brand'] != 'TESLER') {

        $summ += $row['total_price'];
       // $summ += $parts_info['summ'];
       // $summ += parts_price_billing($row['id']);
       // $summ += (($row['status_admin'] != 'Запрос на выезд' && $row['onway'] == 1) ? get_price_transfer($model['cat'], $row['onway_type']) : '0');
       } else {
          //echo $row['id'].'-!tesler';
        $summ += $row['total_price'];
       // $summ += $parts_info['summ'];
       // $summ += parts_price_billing($row['id']);
        //$summ += (($row['status_admin'] != 'Запрос на выезд' && $row['onway'] == 1) ? get_price_transfer($model['cat'], $row['onway_type']) : '0');


       }




       }

      }

      }


    //file_put_contents('ids.txt', );
    return $summ;
}

function get_service_summ_master_stat($user_id, $month, $year, $master_id) {
  global $db;

$sql = mysqli_query($db, 'SELECT `return_id`, `id`,`model_id`,`total_price`,`status_admin`,`onway`,`onway_type` FROM `repairs` WHERE `app_date` REGEXP \''.mysqli_real_escape_string($db, $year.'.'.$month.'.').'\' and `deleted` = 0 and (`status_admin` = \'Подтвержден\' or `status_admin` = \'Выдан\') and  `service_id` = '.$user_id.' and `master_user_id` = '.$master_id.';');
      while ($row = mysqli_fetch_array($sql)) {
      //$info = get_request_info($row['id']);
      //$city = get_city($info['city']);

       $model = model_info($row['model_id']);
       //$parts_info = repairs_parts_info($row['id']);
       if ($brand == 'TESLER') {

       if ($model['brand'] == 'TESLER') {

         //echo $row['id'].'-tesler';
        $summ += $row['total_price'];
        //$summ += $parts_info['summ'];
       // $summ += parts_price_billing($row['id']);
        //$summ += (($row['status_admin'] != 'Запрос на выезд' && $row['onway'] == 1) ? get_price_transfer($model['cat'], $row['onway_type']) : '0');


       }

       } else if ($brand == 'HARPER') {

       if ($model['brand'] == 'HARPER' || $model['brand'] == 'OLTO' || $model['brand'] == 'NESONS') {
          //file_put_contents('ids.txt', $row['id'].PHP_EOL, FILE_APPEND);
         //echo $row['id'].'-tesler';
        $summ += $row['total_price'];
       // $summ += $parts_info['summ'];
       // $summ += parts_price_billing($row['id']);
       // $summ += (($row['status_admin'] != 'Запрос на выезд' && $row['onway'] == 1) ? get_price_transfer($model['cat'], $row['onway_type']) : '0');


       }

       } else {
       if ($model['brand'] != 'TESLER') {

        $summ += $row['total_price'];
       // $summ += $parts_info['summ'];
       // $summ += parts_price_billing($row['id']);
       // $summ += (($row['status_admin'] != 'Запрос на выезд' && $row['onway'] == 1) ? get_price_transfer($model['cat'], $row['onway_type']) : '0');
       } else {
          //echo $row['id'].'-!tesler';
        $summ += $row['total_price'];
       // $summ += $parts_info['summ'];
       // $summ += parts_price_billing($row['id']);
       // $summ += (($row['status_admin'] != 'Запрос на выезд' && $row['onway'] == 1) ? get_price_transfer($model['cat'], $row['onway_type']) : '0');


       }




       }


      }


    //file_put_contents('ids.txt', );
    return $summ;
}

function get_service_summ_master_stat_appdate($user_id, $month, $year, $master_id) {
  global $db;
  $summ = 0;
  $brand = '';
$sql = mysqli_query($db, 'SELECT `return_id`, `id`,`model_id`,`total_price`,`status_admin`,`onway`,`onway_type` FROM `repairs` WHERE `master_app_date` REGEXP \''.mysqli_real_escape_string($db, $year.'.'.$month.'.').'\' and `deleted` = 0 and (`status_admin` = \'Подтвержден\' or `status_admin` = \'Выдан\') and  `service_id` = '.$user_id.' and `master_user_id` = '.$master_id.';');
 //      echo mysqli_num_rows($sql);
while ($row = mysqli_fetch_array($sql)) {
      //$info = get_request_info($row['id']);
      //$city = get_city($info['city']);

       $model = model_info($row['model_id']);
       //$parts_info = repairs_parts_info($row['id']);
       if ($brand == 'TESLER') {

       if ($model['brand'] == 'TESLER') {

         //echo $row['id'].'-tesler';
        $summ += $row['total_price'];
        //$summ += $parts_info['summ'];
        //$summ += parts_price_billing($row['id']);
       // $summ += (($row['status_admin'] != 'Запрос на выезд' && $row['onway'] == 1) ? get_price_transfer($model['cat'], $row['onway_type']) : '0');


       }

       } else if ($brand == 'HARPER') {

       if ($model['brand'] == 'HARPER' || $model['brand'] == 'OLTO' || $model['brand'] == 'NESONS') {
          //file_put_contents('ids.txt', $row['id'].PHP_EOL, FILE_APPEND);
         //echo $row['id'].'-tesler';
        $summ += $row['total_price'];
       // $summ += $parts_info['summ'];
       // $summ += parts_price_billing($row['id']);
       // $summ += (($row['status_admin'] != 'Запрос на выезд' && $row['onway'] == 1) ? get_price_transfer($model['cat'], $row['onway_type']) : '0');


       }

       } else {
       if ($model['brand'] != 'TESLER') {

        $summ += $row['total_price'];
       // $summ += $parts_info['summ'];
       // $summ += parts_price_billing($row['id']);
       // $summ += (($row['status_admin'] != 'Запрос на выезд' && $row['onway'] == 1) ? get_price_transfer($model['cat'], $row['onway_type']) : '0');
       } else {
          //echo $row['id'].'-!tesler';
        $summ += $row['total_price'];
       // $summ += $parts_info['summ'];
       // $summ += parts_price_billing($row['id']);
       // $summ += (($row['status_admin'] != 'Запрос на выезд' && $row['onway'] == 1) ? get_price_transfer($model['cat'], $row['onway_type']) : '0');


       }




       }


      }


    //file_put_contents('ids.txt', );
    return $summ;
}

function get_service_loan($user_id, $month, $year, $type1, $type2) {
  global $db;

$date11 = DateTime::createFromFormat("Y.m", $year.'.'.$month);
//$date11->modify('-1 month');
$date2 = $date11->format('Y.m');
$date_ready = explode('.', $date2);

$check_repairs = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `pay_billing` where `service_id` = '.$user_id.' and `month` = \''.$month.'.\' and `year` = \''.$year.'\' and `original` = 0 order by `id` DESC ;'));
//echo 'SELECT COUNT(*) FROM `repairs` where `service_id` = '.$user_id.' and `app_date` REGEXP \''.$date2.'.\' and `status_admin` = \'Подтвержден\' and `deleted` = 0 order by `id` DESC ;';
if ($check_repairs['COUNT(*)'] > 0) {

//$type1 = create_or_get_payment_id($user_id, $date_ready['0'], $date_ready['1'], $type1);
//$type2 = create_or_get_payment_id($user_id, $date_ready['0'], $date_ready['1'], $type2);
/*if ($type1 && $type2) {
if ($type1['original'] == 0 || $type2['original'] == 0) {   */
return true;
/*} else {
//echo 1;
return false;
}
} else {
  //echo 1;
return false;
}   */

} else {
//  echo 1;
return false;
}

}

function check_custom_loan($user_id) {
  global $db;
$check_repairs = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `pay_billing` where `service_id` = '.$user_id.' and `custom_loan` = 1;'));
if ($check_repairs['COUNT(*)'] > 0) {
return true;
} else {
return false;
}
}

function check_returns_pls($return_id) {
  global $db;

$sql_check_count = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` WHERE `return_id` = \''.mysqli_real_escape_string($db, $return_id).'\' and `deleted` = 0;'));
$sql_check_count2 = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` WHERE `return_id` = \''.mysqli_real_escape_string($db, $return_id).'\' and (`status_admin` = \'Подтвержден\' OR `status_admin` = \'Выдан\') and `deleted` = 0;'));
$percent = ($sql_check_count['COUNT(*)'] > $sql_check_count2['COUNT(*)']) ? round(($sql_check_count2['COUNT(*)']/$sql_check_count['COUNT(*)'])*100) : round(($sql_check_count['COUNT(*)']/$sql_check_count2['COUNT(*)'])*100);

if ($percent == 100) {
return true;
} else {
return false;
}
}


function check_combined($act_id, $bill_id) {
  global $db;
$check_act = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `combine_links` where `pay_billing_id` = '.$act_id.';'));
$check_bill = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `combine_links` where `pay_billing_id` = '.$bill_id.';'));

if ($check_act['COUNT(*)'] > 0 && $check_bill['COUNT(*)'] > 0)  {
return true;
} else {
return true;
}

}

function check_combined_by_id($combine_id, $act_id) {
  global $db;
$check_act = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `combine_links` where `pay_billing_id` = '.$act_id.' and `combine_id` = '.$combine_id.' ;'));
//echo $check_act['COUNT(*)'].'<br>';
if ($check_act['COUNT(*)'] == 0 )  {
return false;
} else {
return true;
}

//return false;

}

function disable_notice($string, $user_id) {
    global $db;
mysqli_query($db, 'UPDATE `notification` SET `read` = 1 where `user_id` = '.$user_id.' and `link` REGEXP \''.$string.'\';') or mysqli_error($db);
return true;
}

function get_client_ip() {
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if(getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if(getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if(getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if(getenv('HTTP_FORWARDED'))
       $ipaddress = getenv('HTTP_FORWARDED');
    else if(getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

function admin_log_add($name) {
    global $db;
mysqli_query($db, 'INSERT INTO `admin_logs` (
`name`
) VALUES (
\''.mysqli_real_escape_string($db, $name).'\'
);');
}



function notice_add($subject, $text, $user_id = '', $link = '', $fullText = '') {
    global $config, $db;




mysqli_query($db, 'INSERT INTO `notification` (
`subject`,
`text`,
`read`,
`user_id`,
`link`
) VALUES (
\''.mysqli_real_escape_string($db, $subject).'\',
\''.mysqli_real_escape_string($db, $text).'\',
0,
\''.mysqli_real_escape_string($db, $user_id).'\',
\''.mysqli_real_escape_string($db, $link).'\'
);') or mysqli_error($db);



$userinfo = get_user_info2($user_id);
$message = ($fullText) ?  $text .'<br>* * *<br>'.$fullText : $text;
if ($user_id == 1) {
  $toEmail = 'service3@harper.ru';
}else{
  $toEmail = $userinfo['email'];
}
if ($userinfo['email'] != '') {
$mes = '<html>
                      <body bgcolor="#DCEEFC">
                      <h3>У вас новое уведомление</h3><br>
                      '.$subject.'
                      <br>
                      <br>
                      '.$message.'
                      <br>
                      '.(($link) ? '<a href="'.$link.'">Подробнее</a>' : '').'
                      <br>
                      <br>
                      '.$config['email_footer'].'
- -  <br>
С уважением, <br>
Служба поддержки HARPER, OLTO, SKYLINE и TESLER <br>
e-mail: <a href="mailto:kan@r97.ru">kan@r97.ru</a>; <a href="mailto:service2@harper.ru">service2@harper.ru</a>
                      </body>

                    </html>';
                    // TODO: нормальная отправка почты
                    $headers  = "MIME-Version: 1.0\r\n";
                    $headers .= "Content-type: text/html; charset=utf-8\r\n";
                    $headers .= "From: " . $config['mail_from'] . "\r\n";
                    mail($toEmail, trim($subject, '. '), $mes, $headers);
/* 
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/PHPMailer-master/PHPMailerAutoload.php');
$mail = new PHPMailer;
$mail->isSMTP(); */
//$mail->SMTPDebug = 1;
/* $mail->Host = $config['mail_host'];
$mail->SMTPAuth = true;
$mail->SMTPSecure = "ssl";
$mail->Username = $config['mail_username'];
$mail->Password = $config['mail_password'];
$mail->Timeout       =  10;
$mail->Port = 465;
$mail->setFrom($config['mail_username'], $config['mail_from']);
$mail->addAddress($toEmail);
$mail->isHTML(true);
$mail->Subject = trim($subject, '. ');
$mail->CharSet = 'UTF-8';
$mail->Body    = $mes; */
//$mail->MailerDebug = true;
//$mail->send();





/*$mail = new PHPMailer;
$mail->isSMTP();
$mail->Host = 'smtp.mail.ru';
$mail->SMTPAuth = true;
$mail->SMTPSecure = "ssl";
$mail->Username = 'robot@harper.ru';
$mail->Password = '!@qwASzx';
$mail->Timeout       =  10;
$mail->setFrom('robot@harper.ru', 'Harper.ru');
$mail->Subject = "Новое уведомление от HARPER.RU";
$mail->CharSet = 'UTF-8';
$mail->msgHTML($body);


foreach ($result as $row) { //This iterator syntax only works in PHP 5.4+
    $mail->addAddress($row['email'], $row['full_name']);
    if (!empty($row['photo'])) {
        $mail->addStringAttachment($row['photo'], 'YourPhoto.jpg'); //Assumes the image data is stored in the DB
    }

    if (!$mail->send()) {
        echo "Mailer Error (" . str_replace("@", "&#64;", $row["email"]) . ') ' . $mail->ErrorInfo . '<br />';
        break; //Abandon sending
    } else {
        echo "Message sent to :" . $row['full_name'] . ' (' . str_replace("@", "&#64;", $row['email']) . ')<br />';
        //Mark it as sent in the DB
        mysqli_query(
            $mysql,
            "UPDATE mailinglist SET sent = true WHERE email = '" .
            mysqli_real_escape_string($mysql, $row['email']) . "'"
        );
    }

    $mail->clearAddresses();
    $mail->clearAttachments();
}
     */

}

}

function myUrlEncode($string) {
    $entities = array('%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%2B', '%24', '%2C', '%2F', '%3F', '%25', '%23', '%5B', '%5D');
    $replacements = array('!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "+", "$", ",", "/", "?", "%", "#", "[", "]");
    return str_replace($entities, $replacements, urlencode($string));
}

function client_notify($email, $return_id, $status, $client_name = '') {
    global $config, $db;
$content = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `returns` WHERE `id` = \''.mysqli_real_escape_string($db, $return_id).'\' LIMIT 1;'));
$sql_tasks = mysqli_query($db, 'SELECT * FROM `repairs` WHERE `return_id` = \''.mysqli_real_escape_string($db, $return_id).'\';');

    if (mysqli_num_rows($sql_tasks) != false)
      while ($row = mysqli_fetch_array($sql_tasks)) {

        $model = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `models` WHERE `id` = \''.mysqli_real_escape_string($db, $row['model_id']).'\' LIMIT 1;'));

        $message_models .= '<tr>
        <td style="padding:7px;border:1px solid #ccc;">'.$model['name'].'</td>
        <td style="padding:7px;border:1px solid #ccc;">'.$row['serial'].'</td>
        <td style="padding:7px;border:1px solid #ccc;">'.program\core\Time::format($row['receive_date']).'</td>
        <td style="padding:7px;border:1px solid #ccc;">'.program\core\Time::format($row['finish_date']).'</td>
        <td style="padding:7px;border:1px solid #ccc;">'.$row['bugs'].'</td>
        </tr>';

      }


if ($status == 1) {

// принят
$mes = '<html>
                      <body bgcolor="#DCEEFC">
                      <h3>Партия '.$content['name'].' / Клиент '.$client_name.' получила статус Принята</h3><br>
                      <br>
                      <h4>Информация о партии</h4>
                      <br>
                      <table style="border-collapse:collapse">
                      <tr>
                      <td style="padding:7px;border:1px solid #ccc;"><strong>Модель</strong></td>
                      <td style="padding:7px;border:1px solid #ccc;"><strong>Серийный номер</strong></td>
                      <td style="padding:7px;border:1px solid #ccc;"><strong>Дата приема</strong></td>
                      <td style="padding:7px;border:1px solid #ccc;"><strong>Дата готовности</strong></td>
                      <td style="padding:7px;border:1px solid #ccc;"><strong>Неисправность</strong></td>
                      </tr>
                      '.$message_models.'
                      </table><br>
                      <br>
                      <br>
- -  <br>
С уважением, <br>
Служба поддержки HARPER, OLTO, SKYLINE и TESLER <br>
e-mail: <a href="mailto:kan@r97.ru">kan@r97.ru</a>; <a href="mailto:service2@harper.ru">service2@harper.ru</a>
                      </body>

                    </html>';

}
if ($status == 2) {

// подтвержден
$mes = '<html>
                      <body bgcolor="#DCEEFC">
                      <h3>Партия '.$content['name'].' получила статус Подтверждена</h3><br>
                      <br>
                      <h4>Информация о партии</h4>
                      <br>
                      <table style="border-collapse:collapse">
                      <tr>
                      <td style="padding:7px;border:1px solid #ccc;"><strong>Модель</strong></td>
                      <td style="padding:7px;border:1px solid #ccc;"><strong>Серийный номер</strong></td>
                      <td style="padding:7px;border:1px solid #ccc;"><strong>Дата приема</strong></td>
                      <td style="padding:7px;border:1px solid #ccc;"><strong>Дата готовности</strong></td>
                      <td style="padding:7px;border:1px solid #ccc;"><strong>Неисправность</strong></td>
                      </tr>
                      '.$message_models.'
                      </table><br>
                      <br>
                      <br>
- -  <br>
С уважением, <br>
Служба поддержки HARPER, OLTO, SKYLINE и TESLER <br>
e-mail: <a href="mailto:kan@r97.ru">kan@r97.ru</a>; <a href="mailto:service2@harper.ru">service2@harper.ru</a>
                      </body>

                    </html>';

}
if ($status == 3) {

// выдан
$mes = '<html>
                      <body bgcolor="#DCEEFC">
                      <h3>Партия '.$content['name'].' получила статус Выдана</h3><br>
                      <br>
                      <h4>Информация о партии</h4>
                      <br>
                      <table style="border-collapse:collapse">
                      <tr>
                      <td style="padding:7px;border:1px solid #ccc;"><strong>Модель</strong></td>
                      <td style="padding:7px;border:1px solid #ccc;"><strong>Серийный номер</strong></td>
                      <td style="padding:7px;border:1px solid #ccc;"><strong>Дата приема</strong></td>
                      <td style="padding:7px;border:1px solid #ccc;"><strong>Дата готовности</strong></td>
                      <td style="padding:7px;border:1px solid #ccc;"><strong>Неисправность</strong></td>
                      </tr>
                      '.$message_models.'
                      </table><br>
                      <br>
                      <br>
                      '.$config['email_footer'].'
- -  <br>
С уважением, <br>
Служба поддержки HARPER, OLTO, SKYLINE и TESLER <br>
e-mail: <a href="mailto:kan@r97.ru">kan@r97.ru</a>; <a href="mailto:service2@harper.ru">service2@harper.ru</a>
                      </body>

                    </html>';

}

require_once($_SERVER['DOCUMENT_ROOT'].'/includes/PHPMailer-master/PHPMailerAutoload.php');

$mail = new PHPMailer;
$mail->isSMTP();
//$mail->SMTPDebug = 1;
$mail->Host = $config['mail_host'];
$mail->SMTPAuth = true;
$mail->SMTPSecure = "ssl";
$mail->Username = $config['mail_username'];
$mail->Password = $config['mail_password'];
$mail->Timeout       =  10;
$mail->Port = 465;
$mail->setFrom($config['mail_username'], $config['mail_from']);
$mail->addAddress($email);
$mail->isHTML(true);
$mail->Subject = 'Партия '.$content['name'].' получила статус Выдана';
$mail->CharSet = 'UTF-8';
$mail->Body    = $mes;
//$mail->MailerDebug = true;

if(!$mail->send()) {
    //echo 'Message could not be sent.';
   //echo 'Mailer Error: ' . $mail->ErrorInfo;
} else {
   // echo 'Message has been sent';
}


}


function count_req() {
  global $config, $db;
$sql = mysqli_query($db, 'SELECT COUNT(*) FROM `requests` WHERE `mod` = 0;');
return mysqli_fetch_array($sql)['COUNT(*)'];
}

function check_read($id) {
  global $db;

if (User::hasRole('admin')) {
$sql = mysqli_query($db, 'SELECT COUNT(*) FROM `feedback_admin` WHERE `repair_id` = '.$id.' and `read_admin` = 0;');
return mysqli_fetch_array($sql)['COUNT(*)'];
} else {
$sql = mysqli_query($db, 'SELECT COUNT(*) FROM `feedback_admin` WHERE `repair_id` = '.$id.' and `read` = 0;');
return mysqli_fetch_array($sql)['COUNT(*)'];
}


}

function get_request_info2($id) {
  global $db;
return mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `requests` WHERE `id` = '.$id));
}

function get_request_info_by_user_id($id) {
  global $db;
return mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `requests` WHERE `user_id` = '.$id));
}

function get_user_info2($id) {
  global $db;
return mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `users` WHERE `id` = '.$id));
}

function del($table, $id, $service_id = '') {
  global $config, $db;

if ($service_id && $table == 'repairmans') {
$sql = mysqli_query($db, 'DELETE FROM `'.$table.'` WHERE `id` = \''.mysqli_real_escape_string($db, $id).'\' and `service_id` = '.$service_id.' LIMIT 1');
} else {
$sql = mysqli_query($db, 'DELETE FROM `'.$table.'` WHERE `id` = \''.mysqli_real_escape_string($db, $id).'\' LIMIT 1');
}
return true;

}

function del_city($table, $id, $service_id = '') {
  global $config, $db;

$sql = mysqli_query($db, 'DELETE FROM `'.$table.'` WHERE `fcity_id` = \''.mysqli_real_escape_string($db, $id).'\' LIMIT 1');

return true;

}


function del_return($id) {
  global $config, $db;

mysqli_query($db, 'DELETE FROM `returns` WHERE `id` = '.$id);
mysqli_query($db, 'UPDATE `repairs` SET `deleted` = 1 WHERE `return_id` = '.$id);


return true;

}

function del_combined($id) {
  global $config, $db;

$sql2 = mysqli_query($db, 'SELECT * FROM `combine_links` where `combine_id` = '.$id.' and `type` = 0;');
      while ($row2 = mysqli_fetch_array($sql2)) {

      mysqli_query($db, 'UPDATE `pay_billing` SET
      `status` = 0
      WHERE `id` = \''.mysqli_real_escape_string($db, $row2['pay_billing_id']).'\' LIMIT 1') or mysqli_error($db);

      }

mysqli_query($db, 'DELETE FROM `combine` WHERE `id` = '.$id);
mysqli_query($db, 'DELETE FROM `combine_links` WHERE `combine_id` = '.$id);

return true;

}

function del_ticket($id) {
  global $config, $db;

mysqli_query($db, 'DELETE FROM `feedback_admin` WHERE `id` = '.$id);
mysqli_query($db, 'DELETE FROM `feedback_messages` WHERE `feedback_id` = '.$id);

return true;

}

function get_city($id) {
  global $db;

$sql = mysqli_query($db, 'SELECT * FROM `cityfull` where `fcity_id` = '.$id);
      while ($row = mysqli_fetch_array($sql)) {
      $content = $row;
      }
    return $content;
}

function true($id) {
  global $config, $db;
require($_SERVER['DOCUMENT_ROOT'].'/includes/PHPMailer-master/PHPMailerAutoload.php');
$content = get_request_info2($id);
$content2 = get_user_info2($content['user_id']);
$toEmail = $content2['email'];
mysqli_query($db, 'UPDATE `users` SET
`status_id` = 1
WHERE `id` = \''.mysqli_real_escape_string($db, $content['user_id']).'\' LIMIT 1
;') or mysqli_error($db);

mysqli_query($db, 'UPDATE `requests` SET
`mod` = 1
WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1
;') or mysqli_error($db);

admin_log_add('Одобрена анкета #'.$_GET['id']);

$mes = '<html>
                      <body bgcolor="#DCEEFC">
                      <h3>Уважаемый '.$content2['email'].'.</h3><br>
                      Ваша заявка на подключение одорбена.<br>
                      <strong>Ваш логин</strong>: '.$content2['email'].'<br>
                      <strong>Ваш пароль</strong>: '.$content2['password'].'

                      <br>
                      <br>
                      '.$config['email_footer'].'
                      <br>
- -  <br>
<b>Пожалуйста, при ответе сохраняйте переписку.<br>
С уважением,  <br>
Служба поддержки SERVICE.R97   <br>
<img src="http://harper.ru//img/Picture1.jpg" height="50px"><br>
e-mail: service2@harper.ru</b>
                      </body>

                    </html>';

$mail = new PHPMailer;
$mail->isSMTP();
$mail->SMTPDebug = 1;
$mail->Host = $config['mail_host'];
$mail->SMTPAuth = true;
$mail->SMTPSecure = "ssl";
$mail->Username = $config['mail_username'];
$mail->Password = $config['mail_password'];
$mail->Timeout       =  10;
$mail->Port = 465;
$mail->setFrom($config['mail_username'], $config['mail_from']);
$mail->addAddress($toEmail);
$mail->isHTML(true);
$mail->Subject = "Ваша заявка на SERVICE.R97.RU";
$mail->CharSet = 'UTF-8';
$mail->Body    = $mes;
$mail->MailerDebug = true;

$mail->send();

}

function repair_done($id) {
  global $db;

mysqli_query($db, 'UPDATE `repairs` SET
`repair_done` = 1,
`status_admin` = \'На проверке\',
`finish_date` = \''.date("Y-m-d").'\'
WHERE `id` = \''.mysqli_real_escape_string($db, $id).'\' and `service_id` = '.User::getData('id').' LIMIT 1
;') or mysqli_error($db);

}

function repair_personal_done($id) {
  global $db;

mysqli_query($db, 'UPDATE `repairs` SET
`status_admin` = \'Подтвержден\',
`repair_done` = 1,
`has_questions` = 0,
`master_app_date` = \''.date("Y.m.d").'\',
`finish_date` = \''.date("Y-m-d").'\'
WHERE `id` = \''.mysqli_real_escape_string($db, $id).'\' and `service_id` = 33 LIMIT 1
;') or mysqli_error($db);

$content = @mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `repairs` WHERE `id` = \''.mysqli_real_escape_string($db, $id).'\' LIMIT 1;'));
$sql_check_count = @mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` WHERE `return_id` = \''.mysqli_real_escape_string($db, $content['return_id']).'\' and `deleted` = 0;'));
$sql_check_count2 = @mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` WHERE `return_id` = \''.mysqli_real_escape_string($db, $content['return_id']).'\' and `status_admin` = \'Подтвержден\' and `deleted` = 0;'));

if (($sql_check_count['COUNT(*)'] == $sql_check_count2['COUNT(*)']) && ($sql_check_count2['COUNT(*)'] != 0 && $sql_check_count['COUNT(*)'] != 0)) {
mysqli_query($db, 'UPDATE `returns` SET `light` = 1 where `id` = '.$content['return_id']);
}


}

function require_parts($id) {
  global $db;
  $photoErr = \models\repair\Check::hasPhotoErrors($id); 
  if(models\User::hasRole('service', 'master') && $photoErr['error_flag']){
    header('Location: /edit-repair/'.$id.'/step/4/');
    exit;
  }

if (!User::hasRole('slave-admin')) {
mysqli_query($db, 'UPDATE `repairs` SET
`status_admin` = "Нужны запчасти" 
WHERE `id` = \''.mysqli_real_escape_string($db, $id).'\' LIMIT 1
;') or mysqli_error($db);
}

if (User::hasRole('slave-admin')) {

$sql = mysqli_query($db, 'SELECT * FROM `repairs_parts` where `repair_id` = '.$id);
      while ($row = mysqli_fetch_array($sql)) {

/////
$repair = @mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `repairs` WHERE `id` = '.$id));
mysqli_query($db, 'INSERT INTO `parts_sc` (
`part_id`,
`repair_id`,
`service_id`,
`count`,
`date_get`
) VALUES (
\''.mysqli_real_escape_string($db, $row['part_id']).'\',
\''.mysqli_real_escape_string($db, $id).'\',
\''.mysqli_real_escape_string($db, $repair['service_id']).'\',
\''.mysqli_real_escape_string($db, '1').'\',
NOW()
);') or mysqli_error($db);

mysqli_query($db, 'INSERT INTO `parts_sc_log` (
`part_id`,
`repair_id`,
`service_id`,
`count`,
`date_get`
) VALUES (
\''.mysqli_real_escape_string($db, $row['part_id']).'\',
\''.mysqli_real_escape_string($db, $id).'\',
\''.mysqli_real_escape_string($db, $repair['service_id']).'\',
\''.mysqli_real_escape_string($db, '1').'\',
NOW()
);') or mysqli_error($db);

$part = @mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `parts` WHERE `id` = '.$row['part_id']));
$id = ($part['parent_id'] > 0) ? $part['parent_id'] : $row['part_id'];
mysqli_query($db, 'UPDATE `parts` SET
`count` = count - 1
WHERE `id` = \''.mysqli_real_escape_string($db, $id).'\' LIMIT 1') or mysqli_error($db);
admin_log_add('Запчасть #'.$id.' списана.');
admin_log_add('Запчасть #'.$id.' отправлена.');

//////////
 }

}

}

function repair_del($id) {
  global $db;

if (User::hasRole('admin')) {
mysqli_query($db, 'UPDATE `repairs` SET
`deleted` = 1
WHERE `id` = \''.mysqli_real_escape_string($db, $id).'\' LIMIT 1
;') or mysqli_error($db);

admin_log_add('Удален ремонт #'.$id);

} else {
mysqli_query($db, 'UPDATE `repairs` SET
`deleted` = 1
WHERE `id` = \''.mysqli_real_escape_string($db, $id).'\' and `service_id` = '.User::getData('id').' LIMIT 1
;') or mysqli_error($db);
}



}

function repair_comeback($id) {
  global $db;

if (User::hasRole('admin')) {
mysqli_query($db, 'UPDATE `repairs` SET
`deleted` = 0
WHERE `id` = \''.mysqli_real_escape_string($db, $id).'\' LIMIT 1
;') or mysqli_error($db);
}

}

function false($id) {
  global $config, $db;
require($_SERVER['DOCUMENT_ROOT'].'/includes/PHPMailer-master/PHPMailerAutoload.php');
$content = get_request_info2($id);
$content2 = get_user_info2($content['user_id']);
$toEmail = $content2['email'];
mysqli_query($db, 'UPDATE `users` SET
`status_id` = 0
WHERE `id` = \''.mysqli_real_escape_string($db, $content['user_id']).'\' LIMIT 1
;') or mysqli_error($db);

mysqli_query($db, 'UPDATE `requests` SET
`mod` = 2,
`comment` = \''.mysqli_real_escape_string($db, $_POST['comment']).'\'
WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1
;') or mysqli_error($db);

$mes = '<html>
                      <body bgcolor="#DCEEFC">
                      <h3>Уважаемый '.$content2['email'].'.</h3><br>
                      Ваша заявка на подключение отклонена. <br>
                      Причина: '.$_POST['comment'].'

                      <br>
                      <br>
                      '.$config['email_footer'].'
                      <br>
- -  <br>
<b>Пожалуйста, при ответе сохраняйте переписку.<br>
С уважением,  <br>
Служба поддержки SERVICE.R97   <br>
<img src="http://harper.ru//img/Picture1.jpg" height="50px"><br>
e-mail: service2@harper.ru</b>
                      </body>

                    </html>';

$mail = new PHPMailer;
$mail->isSMTP();
$mail->SMTPDebug = 1;
$mail->Host = $config['mail_host'];
$mail->SMTPAuth = true;
$mail->SMTPSecure = "ssl";
$mail->Username = $config['mail_username'];
$mail->Password = $config['mail_password'];
$mail->Timeout       =  10;
$mail->Port = 465;
$mail->setFrom($config['mail_username'], $config['mail_from']);
$mail->addAddress($toEmail);
$mail->isHTML(true);
$mail->Subject = "Ваша заявка на SERVICE.R97.RU";
$mail->CharSet = 'UTF-8';
$mail->Body    = $mes;
$mail->MailerDebug = true;

$mail->send();

}

function block($table, $id, $service_id = '') {
  global $config, $db;

$sql = mysqli_query($db, 'UPDATE `'.$table.'` SET `block` = 1 WHERE `id` = \''.mysqli_real_escape_string($db, $id).'\' LIMIT 1');
return true;

}

function unblock($table, $id, $service_id = '') {
  global $config, $db;

$sql = mysqli_query($db, 'UPDATE `'.$table.'` SET `block` = 0 WHERE `id` = \''.mysqli_real_escape_string($db, $id).'\' LIMIT 1');
return true;

}


function top_menu_admin() {
if (User::hasRole('admin')) {
echo '<div class="adm-top">

    <div class="adm-wait">

      <h4>Ожидают обработки</h4>

      <div class="block">

        <div class="item">
          <div class="level">Анкеты СЦ</div>
          <div class="value">
            <a href="/requests/">'.count_req().'</a>
          </div>
        </div>
        <div class="item">
          <div class="level">Сообщения</div>
          <div class="value">
            <a href="/tickets/">'.count_tickets().'</a>
          </div>
        </div>
        <div class="item">
          <div class="level">Статистика</div>
          <div class="value" style="text-align: center;margin-top: -5px;">
            <a href="/stat/"><img src="/img/lifeline-of-heartbeat-in-a-circle.png"></a>
          </div>
        </div>
        <div class="item" id="check-model-service-placeholder"></div>
        <div class="item" id="check-serial-placeholder"></div>
      </div>

    </div><!-- .adm-wait -->


  </div><!-- .adm-top -->';
 } else if (User::hasRole('slave-admin')) {
echo '<div class="adm-top">

    <div class="adm-wait">

      <h4>Ожидают обработки</h4>

      <div class="block">
        <div class="item">
          <div class="level">Сообщения</div>
          <div class="value">
            <a href="/tickets/">'.count_tickets().'</a>
          </div>
        </div>
        <div class="item">
          <div class="level">Статистика</div>
          <div class="value" style="text-align: center;margin-top: -5px;">
            <a href="/stat-master/"><img src="/img/lifeline-of-heartbeat-in-a-circle.png"></a>
          </div>
        </div>
        <div class="item" id="check-model-service-placeholder"></div>
        <div class="item" id="check-serial-placeholder"></div>
      </div>

    </div><!-- .adm-wait -->


  </div><!-- .adm-top -->';
 }  else if (User::hasRole('master')) {
echo '<div class="adm-top">

    <div class="adm-wait">

      <h4>Ожидают обработки</h4>

      <div class="block">

        <div class="item">
          <div class="level">Сообщения</div>
          <div class="value">
            <a href="/tickets/">'.count_tickets().'</a>
          </div>
        </div>
        <div class="item">
          <div class="level">Заработано</div>
          <div class="value" style="text-align: center;margin-top: -5px;">
            '.check_money().'
          </div>
        </div>
        <div class="item">
        <div class="level">Статистика</div>
        <div class="value" style="text-align: center;margin-top: -5px;">
          <a href="/stat-master-personal/"><img src="/img/lifeline-of-heartbeat-in-a-circle.png"></a>
        </div>

      </div>
      <div class="item" id="check-model-service-placeholder"></div>
      <div class="item" id="check-serial-placeholder"></div>
      </div>

    </div><!-- .adm-wait -->


  </div><!-- .adm-top -->';
 } else if (!User::hasRole('acct'))  {
echo '<div class="adm-top">

    <div class="adm-wait">

      <div class="block">

        <div class="item">
          <div class="level">Сообщения</div>
          <div class="value">
            <a href="/tickets/">'.count_tickets().'</a>
          </div>
        </div>';

        echo '<div class="item" id="check-model-service-placeholder"></div>
              <div class="item" id="check-serial-placeholder"></div>';

      echo '</div>

    </div><!-- .adm-wait -->


  </div><!-- .adm-top -->';
 }
}


function count_tickets() {
  global $db;

if (User::hasRole('admin')) {
$where = 'where `status` != \'Вопрос закрыт\' and `status` != \'Уведомление\' and `repair_id` = 0';
} else {
$where = 'where `user_id` = '.User::getData('id').' and `status` != \'Вопрос закрыт\' and `status` != \'Уведомление\' and `repair_id` = 0';
}
$count = 0;
$sql = mysqli_query($db, 'SELECT COUNT(*) FROM `feedback_admin` '.$where.' ;');
      while ($row = mysqli_fetch_array($sql)) {
      $count = $row['COUNT(*)'];
     /* $check = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `feedback_messages` where `feedback_id` = '.$row['id'].' and `read` = 0 GROUP by `feedback_id`;'))['COUNT(*)'];
      if ($check > 0) {
      $count++;
      } */
      }
    return $count;
}

function check_money() {
$total_pay = total_pay_only_month_funk(date('Y.m'), User::getData('id'));

return number_format(count_pay_master_funk($total_pay, User::getData('id')), 0, ',', ' ').' &#8381;';

}

function count_pay_master_funk($pay, $user_id = '', $old = '') {
$id = (!empty($_GET['master_id'])) ? $_GET['master_id'] : $user_id;
$master = Staff::getStaff(['user_id' => $id]);
$salary = $master['salary'];
$percent = $master['percent'];

if ($old == 1) {

if ($salary < $pay) {
return $salary+(($pay-$salary)*0.3);
} else {
return $salary;
}

} else if ($old == 0 && $old == '') {
return $pay*($percent/100);

}



}

function total_pay_only_month_funk($month_select, $master_id_var) {
  global $db;
  $total = 0;
//$dated = ((date("d") < 5) ? date("Y.m", strtotime("-1 months")) : date("Y.m"));
if ($month_select) {
$date = $month_select;
}
if ($date) {
$where_date = ' `master_app_date` REGEXP \''.$date.'\'';

/*$date11 = DateTime::createFromFormat("Y.m", $date);
$date11->modify('-1 month');

echo ((date("d") > 5) ? $date11->format('Y.m').'.05' : $date.'.05');
echo ((date("d") > 5) ? $date.'.'.date('d') : $date.'.'.date('d')); */

}

if ($master_id_var) {
$master_id = ' and `master_user_id` = '.$master_id_var;
$master_idd = $master_id_var;
}

$sql = mysqli_query($db, 'SELECT * FROM `repairs` where  '.$where_date.' '.$master_id.' and (`status_admin` = \'Подтвержден\' OR `status_admin` = \'Выдан\') and `deleted` = 0 and `service_id` = 33 order by `id` DESC;');
      while ($row = mysqli_fetch_array($sql)) {
      //$info = get_request_info($row['id']);
      //$city = get_city($info['city']);

      if($row['master_app_date']) {


      $exp = explode('.', $row['master_app_date']);
      $app[$exp['0']][$exp[1]] = '';

      }


      }

      if(empty($app)){
        return 0;
      }
      foreach ($app as $year => $val) {
      $year_work = $val;
       foreach ($year_work as $month => $value) {

     $sql2 = mysqli_query($db, 'SELECT * FROM `repairs` where `master_app_date` REGEXP \''.$year.'.'.$month.'\' '.$master_id.' and (`status_admin` = \'Подтвержден\' OR `status_admin` = \'Выдан\') and `deleted` = 0 and `service_id` = 33 GROUP by `service_id`  order by `id` DESC ;');

      while ($row2 = mysqli_fetch_array($sql2)) {
      if (empty($_GET['impo']) || $_GET['impo'] != 1) {
      $summ = get_service_summ_master_stat_appdate($row2['service_id'], $month, $year, $master_idd);
      $total += $summ;
       }
       }

     }
     }
     if (!$total) {$total = 0;}
     return $total;
}

function model_info($id)
{
  global $db;
  static $cache = [];
  if (!isset($cache[$id])) {
    $sql = mysqli_query($db, 'SELECT * FROM `models` where `id` = ' . $id);
    $cache[$id] = mysqli_fetch_assoc($sql);
  }
  return $cache[$id];
}

function model_by_name_search($id) {
  global $db;
  $id = preg_replace('/[\s]{2,}/', ' ', $id);
$sql = mysqli_query($db, 'SELECT * FROM `models` where `name` REGEXP \''.str_replace(array("\r\n", "\r", "\n"), '', trim(rtrim(preg_replace("/[^a-zA-Z0-9 ()-_]/u", "", $id)))).'\';');
//echo $id;
      while ($row = mysqli_fetch_array($sql)) {
      $content = $row;
     // print_r($row);
      }
if (!$content) {
$sql = mysqli_query($db, 'SELECT * FROM `models` where `name` LIKE \'%'.str_replace(array("\r\n", "\r", "\n"), '', trim(rtrim(preg_replace("/[^a-zA-Z0-9 ()-_]/u", "", $id)))).'%\';');
//echo $id;
      while ($row = mysqli_fetch_array($sql)) {
      $content = $row;
     // print_r($row);
      }
}
    return $content;
}

function service_request_info($id) {
  global $db;
  static $cache = [];
  if(!isset($cache[$id])){
    $sql = mysqli_query($db, 'SELECT * FROM `requests` where `user_id` = '.$id);
    $cache[$id] = mysqli_fetch_array($sql);
  }
  return $cache[$id];
}

function service_billing_info($id) {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `billing` where `service_id` = '.$id);
      while ($row = mysqli_fetch_array($sql)) {
      $content = $row;
     // print_r($row);
      }
    return $content;
}

function repair_info($id) {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `repairs` where `id` = '.$id);
      while ($row = mysqli_fetch_array($sql)) {
      $content = $row;
     // print_r($row);
      }
    return $content;
}

function model_cat_info($id) {
  global $db;
  static $cache = [];
  if(!isset($cache[$id])){
    $sql = mysqli_query($db, 'SELECT * FROM `cats` where `id` = ' . $id);
    $cache[$id] = mysqli_fetch_array($sql);
  }
  return $cache[$id];
}

function check_allow($cat_id, $user_id, $type = 1) {
  global $db;

$check_cat = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `users_cats` WHERE `type_id` = '.$type.' and `cat_id` = '.$cat_id.' and `user_id` = '.$user_id.';'));
//echo 'SELECT COUNT(*) FROM `users_cats` WHERE `type_id` = '.$type.' and `cat_id` = '.$cat_id.' and `user_id` = '.$user_id.';';
if ($check_cat['COUNT(*)'] > 0) {
return true;
}

}

function master_info($id) {
  global $db;
  static $cache = [];
  if(!isset($cache[$id])){
    $sql = mysqli_query($db, 'SELECT * FROM `repairmans` where `id` = '.$id);
    $cache[$id] =  mysqli_fetch_assoc($sql);
  }
  return $cache[$id];
}

function return_info($id) {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `returns` where `id` = '.$id);
      while ($row = mysqli_fetch_array($sql)) {
      $content = $row;
     // print_r($row);
      }
    return $content;
}

function get_content_by_id($table, $id) {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `'.$table.'` where `id` = '.$id);
      while ($row = mysqli_fetch_array($sql)) {
      $content = $row;
      }
    return $content;
}

function get_price_transfer($catID, $zone, $serviceID = 0)
{
  global $db;
  if (empty($zone)) {
    return 0;
  }
  $k = str_replace('zone', 'zone_', $zone);
  if ($serviceID) {
    $ar = mysqli_fetch_assoc(mysqli_query($db, 'SELECT `' . $k . '` FROM `transfer_service` where `cat_id` = ' . $catID . ' AND `service_id` = ' . $serviceID . ';'));
    if (empty($ar[$k])) {
      return get_price_transfer($catID, $zone);
    }
    return $ar[$k];
  }
  $table = Tariffs::getTransportTariffTable($serviceID);
  $ar = mysqli_fetch_assoc(mysqli_query($db, 'SELECT `' . $k . '` FROM `'.$table.'` where `cat_id` = ' . $catID . ';'));
  if (!$ar) {
    return 0;
  }
  return $ar[$k];
}

function repairs_parts_info($id) {
  global $db;
  static $cache = [];
  if(!isset($cache[$id])){
    $sql = mysqli_query($db, 'SELECT * FROM `repairs_work` where `repair_id` = '.$id.' LIMIT 1');
    $cache[$id] = mysqli_fetch_array($sql);
  }
  return $cache[$id];
}

function repairs_parts_info_array($id)
{
  global $db;
  $sql = mysqli_query($db, 'SELECT * FROM `repairs_work` where `repair_id` = ' . $id . ';');
  while ($row = mysqli_fetch_array($sql)) {
    $content[] = $row;
    if (!isset($content['sum'])) {
      $content['sum'] = 0;
    }
    $content['sum'] += $row['sum'];
  }
  return $content;
}

function part_by_id($id, $modelID = 0) {
  global $db;
  if(is_numeric($id)){
    $sql = mysqli_query($db, 'SELECT * FROM `parts` where `id` = '.$id.';');
  }else{
    $sql = mysqli_query($db, 'SELECT * FROM `parts` where `list` = "'.$id.'" AND `model_id` = "'.$modelID.'" LIMIT 1;');
  }
  $part = mysqli_fetch_array($sql);
  if(empty($part['list'])){
    return $part;
  }
  $part2 = mysqli_fetch_assoc(mysqli_query($db, 'SELECT `name` FROM `parts2` where `id` = '.$part['id']));
  if(empty($part2['name'])){
    return $part;
  }
  $part['list'] = $part2['name'];
  return $part;
}

function gen_notify($id) {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `notification` WHERE `user_id` = '.$id.' AND `read` = 0 ORDER BY `id` ASC LIMIT 50 ;');
$arrays = [];
     while ($row = mysqli_fetch_array($sql)) {

     $arrays[] = array(
      'id' => $row['id'],
      'title' => $row['subject'],
      'text' => $row['text'],
      'date' => date("d.m.Y", strtotime($row['date'])),
      'link' => $row['link'],
      'read' => false
      );

    }
    $cnt = count($arrays);
if($cnt < 50){
  $sql = mysqli_query($db, 'SELECT * FROM `notification` WHERE `user_id` = '.$id.' AND `read` = 1 ORDER BY `id` DESC LIMIT '.(50 - $cnt).' ;');
  if (mysqli_num_rows($sql) > 0) {
  while ($row = mysqli_fetch_array($sql)) {

  $arrays[] = array(
   'id' => $row['id'],
   'title' => $row['subject'],
   'text' => $row['text'],
   'date' => date("d.m.Y", strtotime($row['date'])),
   'link' => $row['link'],
   'read' => true
   );

 }
}
}
    return $arrays;
}

function check_returns() {
/*      global $db;

$sql = mysqli_query($db, 'SELECT `id`,`date_out` FROM `returns` ORDER by `id` DESC;');
if (mysqli_num_rows($sql) != false) {
      while ($row = mysqli_fetch_array($sql)) {
      $sql_check_count = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` WHERE `return_id` = \''.mysqli_real_escape_string($db, $row['id']).'\' and `deleted` = 0;'));
      $sql_check_count2 = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` WHERE `return_id` = \''.mysqli_real_escape_string($db, $row['id']).'\' and (`status_admin` = \'Подтвержден\' OR `status_admin` = \'Выдан\') and `deleted` = 0;'));

      $percent = ($sql_check_count['COUNT(*)'] > $sql_check_count2['COUNT(*)']) ? round(($sql_check_count2['COUNT(*)']/$sql_check_count['COUNT(*)'])*100) : round(($sql_check_count['COUNT(*)']/$sql_check_count2['COUNT(*)'])*100);
      if ($percent == 100 && $row['date_out'] == '') {
      $i++;
      }
      }
      }

if ($i > 0) {
    return '('.$i.')';
} else {
    return '';
}   */

}

function menu_dash() {
$user_33 = (User::hasRole('slave-admin')) ? '<span>/</span><a href="/clients/">Клиенты</a><span>/</span><a href="/mass-upload/">Массовый импорт ремонтов</a><span>/</span><a href="/returns/">Партии возвратов '.check_returns().'</a><span>/</span><a href="/users/">Сотрудники сервиса</a>        <span>/</span><a href="/issues/">Фактическая неисправность</a>        <span>/</span><a href="/models/">Модели</a>        <span>/</span><a href="/models-service/33/">Обслуживаемые модели сервиса</a><span>/</span><a href="/contrahens/">Контрагенты для актов</a><span>/</span><a href="/re-repaired/">Повторные ремонты</a><span>/</span><a href="/parts/">Запчасти</a>' : '';
$user_55 = (User::hasRole('taker')) ? '<span>/</span><a href="/clients/">Клиенты</a><span>/</span><a href="/mass-upload/">Массовый импорт ремонтов</a><span>/</span><a href="/returns/">Партии возвратов '.check_returns().'</a>' : '';
$user_66 = (User::hasRole('master')) ? '<span>/</span><a href="/models/">Модели</a><span>/</span><a href="/settings-user/">Настройки</a>' : '';
$userTaker = (User::hasRole('taker')) ? '<span>/</span><a href="/models/">Модели</a><span>/</span><a href="/models-service/33/">Обслуживаемые модели</a><span>/</span><a href="/settings-user/">Настройки</a>' : '';
$reqCnt = '';
if(!isset($_SESSION['cache_requests_cnt'])){
  $_SESSION['cache_requests_cnt'] = Requests::count(['status_id' => 0]);
}
if($_SESSION['cache_requests_cnt']){
  $reqCnt = '<span class="menu-cnt" title="Запросов на утилизацию">'.$_SESSION['cache_requests_cnt'].'</span>';
}
if (User::hasRole('admin')) {
echo '<div class="item">
      <div class="level">Базы данных</div>
      <div class="value">
        <a href="/brands/">Бренды</a>
        <span>/</span>
        <a href="/models/">Модели</a>
        <span>/</span>
        <a href="/categories/">Категории</a>
        <span>/</span>
        <a href="/prices/">Тарифы обслуживания</a>
        <span>/</span>
        <a href="/transfer/">Тарифы транспорт</a>
        <span>/</span>
        <a href="/tariffs-install/">Тарифы на демонтаж/монтаж</a>
        <span>/</span>
        <a href="/parts/">Запчасти '.$reqCnt.'</a>
        <span>/</span>
        <a href="/groups/">Группы запчастей</a>
        <span>/</span>
        <a href="/providers/">Поставщики</a>
        <span>/</span>
        <a href="/problems/">Проделанная работа</a>
        <span>/</span>
        <a href="/repair-types/">Вид ремонта</a>
        <span>/</span>
        <a href="/issues/">Фактическая неисправность</a>
        <span>/</span>
        <a href="/countries/">Страны</a>
        <span>/</span>
        <a href="/cities/">Города</a>
        <span>/</span>
        <a href="/clients/">Клиенты</a>
        <span>/</span>
        <a href="/plants/">Завод-сборщик</a>
        <span>/</span>
        <a href="/infobase/">Техническая документация</a>
      </div>
      <div class="item">
      <div class="level">Управление</div>
      <div class="value">
        <a href="/dashboard/">Главная</a>
        <span>/</span>
        <a href="/services/">Управление СЦ</a>
        <span>/</span>        
        <a href="/payments-v3/">Платежные документы</a>
        <span>/</span>
        <a href="/reports/">Отчетность</a>
        <span>/</span>
        <a href="/config/">Настройки</a>
        <span>/</span>
        <a href="/upload-serials/">Загрузка серийников</a>
        <span>/</span>
        <a href="/dicts/">Справочники</a>
         <span>/</span>
        <a href="/users/">Пользователи</a>
        <span>/</span>
        <a href="/log/">Лог системы</a>
       </div>
      ';
}
if (User::hasRole('slave-admin')) {
echo '<div class="item">
      <div class="level">Базы данных</div>
      <div class="value">
        <a href="/manual/">Инструкция по работе с базой</a>
'.$user_33.'
      </div>
</div><div class="item">
      <div class="level">Управление</div>
      <div class="value">
        <a href="/dashboard/">Главная</a>
        <span>/</span>
        <a href="/my-services/">Управление СЦ</a>
        <span>/</span>
        <a href="/reports/">Отчетность</a>
        <span>/</span>
        <a href="/payment/">Платежные документы</a>
        <span>/</span>
        <a href="/tickets/">Задать вопрос</a>
        <span>/</span>
        <a href="/settings/">Настройки</a>
      </div>
</div>';
}
if (User::hasRole('service')) {
echo '<div class="item">
      <div class="level">Базы данных</div>
      <div class="value">
        <a href="/manual/">Инструкция по работе с базой</a>
        '.$user_33.'
        <span>/</span>
        <a href="/parts/">Запчасти, склад Разбор</a>
      </div>
</div><div class="item">
      <div class="level">Управление</div>
      <div class="value">
        <a href="/dashboard/">Главная</a>
        <span>/</span>
        <a href="/reports/">Отчетность</a>
        <span>/</span>
        <a href="/payment/">Платежные документы</a>
        <span>/</span>
        <a href="/repairmans/">Мастера</a>
        <span>/</span>
        <a href="/tickets/">Задать вопрос</a>
        <span>/</span>
        <a href="/settings/">Настройки</a>
      </div>
</div>';
 }
if (User::hasRole('master')) {
echo '<div class="item">
      <div class="level">Управление</div>
      <div class="value">
        <a href="/dashboard/">Главная</a>
        <span>/</span> 
        <a href="/reports-personal/">Отчетность</a>
        <span>/</span>
        <a href="/tickets/">Задать вопрос</a>
        '.$user_66.'
      </div>
</div>';
 }
 if (User::hasRole('taker')) {
echo '<div class="item">
      <div class="level">Управление</div>
      <div class="value">
        <a href="/dashboard/">Главная</a>
        <span>/</span>
        <a href="/tickets/">Задать вопрос</a>  
        '.$user_55.'
        '.$userTaker.'
      </div>
</div>';
 }
if (User::hasRole('acct')) {
echo '<div class="item">
      <div class="level">Управление</div>
      <div class="value" style="font-size: 16px;">
        <a href="/dashboard/">Главная</a>
        <span>/</span>
        <a href="/services/">Управление СЦ</a>
        <!--<a href="/reports/">Отчетность</a>
        <span>/</span> -->
      </div>
</div>
<div class="item">
      <div class="level">Счета и акты</div>
      <div class="value" style="font-size: 16px;">
        <a href="/payment/">Счета и Акты прямые</a>
        <span>/</span>
        <a href="/combined/">Счета и Акты Агентские</a>
        <span>/</span>
        <a href="/payments-v3/">Счета в работе - агентские</a>
        <span>/</span>
        <!--<a href="/payments-sended/">Счета на оплату - агентские</a>
        <span>/</span>-->
        <a href="/payments-payed/">Счета на оплату - агентские</a>
      </div>
</div>';
}
if(User::hasRole('service', 'taker')){
  echo '<div class="item" style="margin-right: 24px">
          <div class="value"><a href="#" onclick="createRepairBtn(event)" class="create-repair-btn">Создать карточку ремонта</a></div>
          <script>
          function createRepairBtn(event){
              event.preventDefault();
              if(event.target.classList.contains("blocked")){
                  return;
              }
              event.target.classList.add("blocked");
              location.href = "/add-repair/";
          }
          </script>
        </div>';
}
}



function menu($page = '') {
  global $config, $db;
$sql = mysqli_query($db, 'SELECT * FROM `menu`;');
    if (mysqli_num_rows($sql) != false)
      while ($row = mysqli_fetch_array($sql)) {
        $menu .= '<li><a href="'.$row['url'].'">'.$row['anchor'].'</a></li><li>';
      }
    return $menu;
}

function menu_type($type_id) {
  global $config, $db;
$sql = mysqli_query($db, 'SELECT * FROM `categories` WHERE `type` = \''.mysqli_real_escape_string($db, $type_id).'\' and `active` = 1;');
$cat_info = mysqli_query($db, 'SELECT * FROM `categories` WHERE `type` = \''.mysqli_real_escape_string($db, $type_id).'\' and `active` = 1;');
$id = 1;
while ($row = mysqli_fetch_array($cat_info)) {
if (mysqli_num_rows($cat_info) != $id) {
$cat_in .= $row['id'].',';
} else {
$cat_in .= $row['id'];
}
$id++;
}
$leader = @mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `items` WHERE `cat_id` IN ('.$cat_in.') and `leader` = 1 ORDER BY RAND() LIMIT 1;'));
$cat_info_item = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `categories` WHERE `id` = '.$leader['cat_id'].' LIMIT 1;'));
$menu .= '<div class="box">
            <div class="best">
              <div class="inner">
                <div class="top">
                  Лидер продаж
                </div>
                <div class="img">
                  <img src="'.resizer($leader['img_main'], '', '180', 2).'" alt=""/>
                </div>
                <div class="title">
                  '.$leader['name'].'
                </div>
                <div class="type">
                  '.$cat_info_item['single_name'].'
                </div>
                <!--<div class="text">
                  '.cutString(strip_tags($leader['short_desc']), 250).'
                </div>-->
                <div class="detail">
                  <a href="'.$config['url'].$leader['sef'].'/">Подробнее</a>
                </div>
              </div>
            </div><div class="list">';
        $counter = 0;
        $main_counter = 0;
        $num = mysqli_num_rows($sql);
    if (mysqli_num_rows($sql) != false) {
      while ($row = mysqli_fetch_array($sql)) {
        //$img_query = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `items` WHERE `cat_id` = \''.mysqli_real_escape_string($db, $row['id']).'\' LIMIT 1;'));
        //$img_array = json_decode($img_query['imgs']);
        $counter++;
        $main_counter++;
        if ($counter == 1) { $menu .= '<div class="inline">'; }
        $menu .= '<div class="item">
                  <a href="'.$config['url'].$row['sef'].'/">
                    <span class="img">
                      <img src="'.resizer($row['img'], 135, 180, 2).'" alt=""/>
                    </span>
                    <span class="title">
                      '.$row['name'].'
                    </span>
                  </a>
                </div>
              ';

      if ($counter == 4) { $menu .= '</div>'; $counter = 0; } else if ($main_counter == $num) { $menu .= '</div>'; }

      }

      $menu .= '</div></div>';

      } else { $menu = 'Раздел пуст.<br><br>'; }
    return $menu;
}

function footer_categories($type_id) {
  global $config, $db;
$sql = mysqli_query($db, 'SELECT * FROM `categories` WHERE `type` = \''.mysqli_real_escape_string($db, $type_id).'\' and `active` = 1;');
    if (mysqli_num_rows($sql) != false) {
      while ($row = mysqli_fetch_array($sql)) {
        $menu .= '<li><a href="'.$config['url'].$row['sef'].'/" data-img="'.resizer($row['img2'], '238', '324', 2).'">'.$row['name'].'</a></li> ';
      }
    }
    return $menu;
}

function menu_type_mobile($type_id) {
  global $config, $db;
$sql = mysqli_query($db, 'SELECT * FROM `categories` WHERE `type` = \''.mysqli_real_escape_string($db, $type_id).'\' and `active` = 1;');

    if (mysqli_num_rows($sql) != false) {
      while ($row = mysqli_fetch_array($sql)) {
        $menu .= '<li><a href="'.$config['url'].$row['sef'].'/">'.$row['name'].'</a></li>';
      }

      } else { $menu = 'Раздел пуст.<br><br>'; }
    return $menu;
}

function menu_type_options($type_id) {
  global $config, $db;
$sql = mysqli_query($db, 'SELECT * FROM `categories` WHERE `type` = \''.mysqli_real_escape_string($db, $type_id).'\' and `active` = 1;');

    if (mysqli_num_rows($sql) != false) {
      while ($row = mysqli_fetch_array($sql)) {
        $menu .= '<option value="'.$row['id'].'">'.$row['name'].'</option>';
      }
    }
    return $menu;
}



function menu_header() {
return '
<li><a href="http://harper.ru/about/">О компании</a></li>
<li><a href="http://harper.ru/news/">Новости</a></li>
<li><a href="http://harper.ru/gde-kupit/">Где купить</a></li>
<li><a href="http://harper.ru/support/">Поддержка</a></li>
<li><a href="http://harper.ru/partneram/">Партнерам</a></li>
<li><a href="http://harper.ru/contacts/">Контакты</a></li>
';
}

function menu_header_mobile() {
return '
<li><a href="http://harper.ru/about/">О компании</a></li>
<li><a href="http://harper.ru/news/">Новости</a></li>
<li><a href="http://harper.ru/gde-kupit/">Где купить</a></li>
<li><a href="http://harper.ru/support/">Поддержка</a></li>
<li><a href="http://harper.ru/partneram/">Партнерам</a></li>
<li><a href="http://harper.ru/contacts/">Контакты</a></li>
<li><a href="" class="open-callback">Написать нам</a></li>
';
}

function index_news_slider() {
  global $config, $db;
# 1 новость:
$news_1 = @mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `news_new` WHERE `type` = 1 ORDER by `id` desc LIMIT 1;'));
preg_match_all('/<img [^>]*src=["|\']([^"|\']+)/i', $news_1['text'], $news_1_img);
$news_1_text = cutString(strip_tags($news_1['text']), 150);

# 2 видео-презентация:
$news_2 = @mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `news_new` WHERE `type` = 2 ORDER by `id` desc LIMIT 1;'));
if (!preg_match('/src="([^"]+)/', $news_2['text'], $match_2))
$match_2['1'] = str_replace('watch?v=', 'embed/', $news_2['video']);
if ($news_2['video_pic'] == '') {
$news_2['video_pic'] = 'http://i.ytimg.com/vi/'.str_replace('https://www.youtube.com/embed/', '', $match_2['1']).'/hqdefault.jpg';
}

# 3 новость:
$news_3 = @mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `news_new` WHERE `type` = 1 ORDER by `id` desc LIMIT 1, 1;'));
preg_match_all('/<img [^>]*src=["|\']([^"|\']+)/i', $news_3['text'], $news_3_img);
$news_3_text = cutString(strip_tags($news_3['text']), 150);

# 4 новость:
$news_4 = @mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `news_new` WHERE `type` = 1 ORDER by `id` desc LIMIT 2, 1;'));
preg_match_all('/<img [^>]*src=["|\']([^"|\']+)/i', $news_4['text'], $news_4_img);
$news_4_text = cutString(strip_tags($news_4['text']), 150);

# 5 новость:
$news_5 = @mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `news_new` WHERE `type` = 1 ORDER by `id` desc LIMIT 3, 1;'));
preg_match_all('/<img [^>]*src=["|\']([^"|\']+)/i', $news_5['text'], $news_5_img);
$news_5_text = cutString(strip_tags($news_5['text']),150);

# 6 видео-презентация:
$news_6 = @mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `news_new` WHERE `type` = 2 ORDER by `id` desc LIMIT 1, 1;'));

if (!preg_match('/src="([^"]+)/', $news_6['text'], $match_6))
$match_6['1'] = str_replace('watch?v=', 'embed/', $news_2['video']);
if ($news_6['video_pic'] == '') {
$news_6['video_pic'] = 'http://i.ytimg.com/vi/'.str_replace('https://www.youtube.com/embed/', '', $match_6['1']).'/hqdefault.jpg';
}

# 7 новость:
$news_7 = @mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `news_new` WHERE `type` = 1 ORDER by `id` desc LIMIT 4, 1;'));
preg_match_all('/<img [^>]*src=["|\']([^"|\']+)/i', $news_7['text'], $news_7_img);
$news_7_text = cutString(strip_tags($news_7['text']), 150);

# 8 новость:
$news_8 = @mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `news_new` WHERE `type` = 1 ORDER by `id` desc LIMIT 5, 1;'));
preg_match_all('/<img [^>]*src=["|\']([^"|\']+)/i', $news_8['text'], $news_8_img);
$news_8_text = cutString(strip_tags($news_8['text']), 150);

# 9 новость:
$news_9 = @mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `news_new` WHERE `type` = 1 ORDER by `id` desc LIMIT 6, 1;'));
preg_match_all('/<img [^>]*src=["|\']([^"|\']+)/i', $news_9['text'], $news_9_img);
$news_9_text = cutString(strip_tags($news_9['text']), 150);

# 10 видео-презентация:
$news_10 = @mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `news_new` WHERE `type` = 2 ORDER by `id` desc LIMIT 2, 1;'));
preg_match('/src="([^"]+)/', $news_10['text'], $match_10);

# 11 новость:
$news_11 = @mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `news_new` WHERE `type` = 1 ORDER by `id` desc LIMIT 7, 1;'));
preg_match_all('/<img [^>]*src=["|\']([^"|\']+)/i', $news_11['text'], $news_11_img);
$news_11_text = cutString(strip_tags($news_11['text']), 150);

# 12 новость:
$news_12 = @mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `news_new` WHERE `type` = 1 ORDER by `id` desc LIMIT 8, 1;'));
preg_match_all('/<img [^>]*src=["|\']([^"|\']+)/i', $news_12['text'], $news_12_img);
$news_12_text = cutString(strip_tags($news_12['text']), 150);

$content = ' <li class="item">
            <a href="/'.$news_1['sef'].'/"/>
              <span class="img" style="background-image: url('.$news_1_img['1']['0'].');">
                <span class="tb"></span>
              </span>
              <span class="entry">
                <span class="date">
                  '.$news_1['date'].'
                </span>
                <span class="title">
                  '.$news_1['name'].'
                </span>
                <span class="text">
                  '.$news_1_text.'...
                </span>
              </span>
            </a>
          </li>

          <li class="item row">
            <span class="social">
              <span class="tl">
                Мы в социальных сетях
              </span>
              <span class="tx">
                Присоеденяйтесь к нам
              </span>
              <span class="bx">
    <a href="https://twitter.com/HarperRussia" target="_blank" class="tw"></a>
          <a href="http://vk.com/harperrussia" target="_blank" class="vk"></a>
          <a href="https://www.facebook.com/harper.russia" target="_blank" class="fb"></a>
          <a href="http://instagram.com/harper_russia" target="_blank" class="in"></a>
          <a href="http://www.youtube.com/channel/UC71NkNcIUz0G2xf3wb2dD5w/" target="_blank" class="yt"></a>
              </span>
            </span>
            <a class="fancybox fancybox.iframe video" href="'.$match_2['1'].'?autoplay=1" style="background-image: url('.$news_2['video_pic'].');">
              <span class="in">
                <span class="ic"></span>
                <span class="tx">
                 '.wordwrap($news_2['name'], 40, "<br />").'
                </span>
              </span>
            </a>
          </li>

          <li class="item">
            <a href="/'.$news_3['sef'].'/">
              <span class="img" style="background-image: url('.$news_3_img['1']['0'].');">
                <span class="tb"></span>
              </span>
              <span class="entry">
                <span class="date">
                  '.$news_3['date'].'
                </span>
                <span class="title">
                  '.$news_3['name'].'
                </span>
                <span class="text">
                 '.$news_3_text.'...
                </span>
              </span>
            </a>
          </li>

          <li class="item bot">
            <a href="/'.$news_4['sef'].'/">
              <span class="entry">
                <span class="date">
                  '.$news_4['date'].'
                </span>
                <span class="title">
                  '.$news_4['name'].'
                </span>
                <span class="text">
                  '.$news_4_text.'...
                </span>
              </span>
              <span class="img" style="background-image: url('.$news_4_img['1']['0'].');">
                <span class="tb"></span>
              </span>
            </a>
          </li>

          <li class="item">
            <a href="/'.$news_5['sef'].'/">
              <span class="img" style="background-image: url('.$news_5_img['1']['0'].');">
                <span class="tb"></span>
              </span>
              <span class="entry">
                <span class="date">
                  '.$news_5['date'].'
                </span>
                <span class="title">
                  '.$news_5['name'].'
                </span>
                <span class="text">
                  '.$news_5_text.'...
                </span>
              </span>
            </a>
          </li>

          <li class="item row">
            <span class="social">
              <span class="tl">
                Мы в социальных сетях
              </span>
              <span class="tx">
                Присоеденяйтесь к нам
              </span>
              <span class="bx">
     <a href="https://twitter.com/HarperRussia" target="_blank" class="tw"></a>
          <a href="http://vk.com/harperrussia" target="_blank" class="vk"></a>
          <a href="https://www.facebook.com/harper.russia" target="_blank" class="fb"></a>
          <a href="http://instagram.com/harper_russia" target="_blank" class="in"></a>
          <a href="http://www.youtube.com/channel/UC71NkNcIUz0G2xf3wb2dD5w/" target="_blank" class="yt"></a>
              </span>
            </span>
            <a class="fancybox fancybox.iframe video" href="'.$match_6['1'].'?autoplay=1" style="background-image: url('.$news_6['video_pic'].');">
              <span class="in">
                <span class="ic"></span>
                <span class="tx">
                   '.$news_6['name'].'
                </span>
              </span>
            </a>
          </li>

          <li class="item">
            <a href="/'.$news_7['sef'].'/">
              <span class="img" style="background-image: url('.$news_7_img['1']['0'].');">
                <span class="tb"></span>
              </span>
              <span class="entry">
                <span class="date">
                  '.$news_7['date'].'
                </span>
                <span class="title">
                  '.$news_7['name'].'
                </span>
                <span class="text">
                 '.$news_7_text.'...
                </span>
              </span>
            </a>
          </li>

          <li class="item bot">
            <a href="/'.$news_8['sef'].'/">
              <span class="entry">
                <span class="date">
                  '.$news_8['date'].'
                </span>
                <span class="title">
                  '.$news_8['name'].'
                </span>
                <span class="text">
                  '.$news_8_text.'...
                </span>
              </span>
              <span class="img" style="background-image: url('.$news_8_img['1']['0'].');">
                <span class="tb"></span>
              </span>
            </a>
          </li>

           <li class="item">
            <a href="/'.$news_9['sef'].'/"/>
              <span class="img" style="background-image: url('.$news_9_img['1']['0'].');">
                <span class="tb"></span>
              </span>
              <span class="entry">
                <span class="date">
                  '.$news_9['date'].'
                </span>
                <span class="title">
                  '.$news_9['name'].'
                </span>
                <span class="text">
                  '.$news_9_text.'...
                </span>
              </span>
            </a>
          </li>

          <li class="item row">
            <span class="social">
              <span class="tl">
                Мы в социальных сетях
              </span>
              <span class="tx">
                Присоеденяйтесь к нам
              </span>
              <span class="bx">
    <a href="https://twitter.com/HarperRussia" target="_blank" class="tw"></a>
          <a href="http://vk.com/harperrussia" target="_blank" class="vk"></a>
          <a href="https://www.facebook.com/harper.russia" target="_blank" class="fb"></a>
          <a href="http://instagram.com/harper_russia" target="_blank" class="in"></a>
          <a href="http://www.youtube.com/channel/UC71NkNcIUz0G2xf3wb2dD5w/" target="_blank" class="yt"></a>
              </span>
            </span>
            <a class="fancybox fancybox.iframe video" href="'.$match_10['1'].'?autoplay=1" style="background-image: url('.$news_10['video_pic'].');">
              <span class="in">
                <span class="ic"></span>
                <span class="tx">
                   '.$news_10['name'].'
                </span>
              </span>
            </a>
          </li>

           <li class="item">
            <a href="/'.$news_12['sef'].'/"/>
              <span class="img" style="background-image: url('.$news_12_img['1']['0'].');">
                <span class="tb"></span>
              </span>
              <span class="entry">
                <span class="date">
                  '.$news_12['date'].'
                </span>
                <span class="title">
                  '.$news_12['name'].'
                </span>
                <span class="text">
                  '.$news_12_text.'...
                </span>
              </span>
            </a>
          </li>

          <li class="item bot">
            <a href="/'.$news_11['sef'].'/">
              <span class="entry">
                <span class="date">
                  '.$news_11['date'].'
                </span>
                <span class="title">
                  '.$news_11['name'].'
                </span>
                <span class="text">
                  '.$news_11_text.'...
                </span>
              </span>
              <span class="img" style="background-image: url('.$news_11_img['1']['0'].');">
                <span class="tb"></span>
              </span>
            </a>
          </li>


          ';

return $content;
}


function index_news_reviews_slider() {
  global $config, $db;
# 1 новость:
$news_1 = @mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `news_new` WHERE `type` = 3 ORDER by `id` desc LIMIT 1;'));
preg_match_all('/<img [^>]*src=["|\']([^"|\']+)/i', $news_1['text'], $news_1_img);
$news_1_text = cutString(strip_tags($news_1['text']), 150);

/*if ($news_1_img['1']['0'] == '') {
$news_1_img['1']['0'] = 'http://i.ytimg.com/vi/'.str_replace('https://www.youtube.com/watch?v=', '', $news_1['video']).'/hqdefault.jpg';
} */

# 2 новость:
$news_2 = @mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `news_new` WHERE `type` = 3 ORDER by `id` desc LIMIT 1, 1;'));
preg_match_all('/<img [^>]*src=["|\']([^"|\']+)/i', $news_2['text'], $news_2_img);
$news_2_text = cutString(strip_tags($news_2['text']), 150);

# 3 видео-презентация:
$news_3 = @mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `news_new` WHERE `type` = 4 ORDER by `id` desc LIMIT 1;'));

# 4 новость:
$news_4 = @mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `news_new` WHERE `type` = 3 ORDER by `id` desc LIMIT 2, 1;'));
preg_match_all('/<img [^>]*src=["|\']([^"|\']+)/i', $news_4['text'], $news_4_img);
$news_4_text = cutString(strip_tags($news_4['text']), 150);

# 5 новость:
$news_5 = @mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `news_new` WHERE `type` = 3 ORDER by `id` desc LIMIT 3, 1;'));
preg_match_all('/<img [^>]*src=["|\']([^"|\']+)/i', $news_5['text'], $news_5_img);
$news_5_text = cutString(strip_tags($news_5['text']),150);

# 6 видео-презентация:
$news_6 = @mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `news_new` WHERE `type` = 4 ORDER by `id` desc LIMIT 1, 1;'));

# 7 новость:
$news_7 = @mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `news_new` WHERE `type` = 3 ORDER by `id` desc LIMIT 4, 1;'));
preg_match_all('/<img [^>]*src=["|\']([^"|\']+)/i', $news_7['text'], $news_7_img);
$news_7_text = cutString(strip_tags($news_7['text']), 150);

# 8 новость:
$news_8 = @mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `news_new` WHERE `type` = 3 ORDER by `id` desc LIMIT 5, 1;'));
preg_match_all('/<img [^>]*src=["|\']([^"|\']+)/i', $news_8['text'], $news_8_img);
$news_8_text = cutString(strip_tags($news_8['text']), 150);

# 9 видео-презентация:
$news_9 = @mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `news_new` WHERE `type` = 4 ORDER by `id` desc LIMIT 2, 1;'));

# 7 новость:
$news_10 = @mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `news_new` WHERE `type` = 3 ORDER by `id` desc LIMIT 6, 1;'));
preg_match_all('/<img [^>]*src=["|\']([^"|\']+)/i', $news_10['text'], $news_10_img);
$news_10_text = cutString(strip_tags($news_10['text']), 150);

# 8 новость:
$news_11 = @mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `news_new` WHERE `type` = 3 ORDER by `id` desc LIMIT 7, 1;'));
preg_match_all('/<img [^>]*src=["|\']([^"|\']+)/i', $news_11['text'], $news_11_img);
$news_11_text = cutString(strip_tags($news_11['text']), 150);

# 9 видео-презентация:
$news_12 = @mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `news_new` WHERE `type` = 4 ORDER by `id` desc LIMIT 3, 1;'));

$content = ' <li class="item">
            <a href="'.$config['url'].$news_1['sef'].'/">
              <span class="img">
                <span class="tb"><img src="'.resizer($news_1_img['1']['0'], 210, 295, 4).'" alt=""/></span>
              </span>
              <span class="entry">
                <span class="date">
                 '.$news_1['date'].'
                </span>
                <span class="title">
                 '.$news_1['name'].'
                </span>
                <span class="text">
                  '.$news_1_text.'
                </span>
              </span>
            </a>
          </li>

          <li class="item bot">
            <a href="'.$config['url'].$news_2['sef'].'/">
              <span class="entry">
                <span class="date">
                 '.$news_2['date'].'
                </span>
                <span class="title">
                  '.$news_2['name'].'
                </span>
                <span class="text">
                  '.$news_2_text.'
                </span>
              </span>
              <span class="img">
                <span class="tb"><img src="'.$news_2_img['1']['0'].'" alt=""/></span>
              </span>
            </a>
          </li>

          <li class="item bg">
            <a href="'.$news_3['link'].'" style="background-image: url('.$news_3['banner_img'].');">
              <span class="info">
                <span class="title">
                 '.$news_3['name'].'
                </span>
              </span>
            </a>
          </li>

          <li class="item">
            <a href="'.$config['url'].$news_4['sef'].'/">
              <span class="img">
                <span class="tb"><img src="'.$news_4_img['1']['0'].'" alt=""/></span>
              </span>
              <span class="entry">
                <span class="date">
                 '.$news_4['date'].'
                </span>
                <span class="title">
                  '.$news_4['name'].'
                </span>
                <span class="text">
                  '.$news_4_text.'
                </span>
              </span>
            </a>
          </li>

          <li class="item bot">
            <a href="'.$config['url'].$news_5['sef'].'/">
              <span class="entry">
                <span class="date">
                 '.$news_5['date'].'
                </span>
                <span class="title">
                 '.$news_5['name'].'
                </span>
                <span class="text">
                  '.$news_5_text.'
                </span>
              </span>
              <span class="img">
                <span class="tb"><img src="'.$news_5_img['1']['0'].'" alt=""/></span>
              </span>
            </a>
          </li>

          <li class="item bg">
            <a href="'.$news_6['link'].'" style="background-image: url('.$news_6['banner_img'].');">
              <span class="info">
                <span class="title">
               '.$news_6['name'].'
                </span>
              </span>
            </a>
          </li>

          <li class="item bot">
            <a href="'.$config['url'].$news_7['sef'].'/">
              <span class="entry">
                <span class="date">
                 '.$news_7['date'].'
                </span>
                <span class="title">
                 '.$news_7['name'].'
                </span>
                <span class="text">
                  '.$news_7_text.'
                </span>
              </span>
              <span class="img">
                <span class="tb"><img src="'.$news_7_img['1']['0'].'" alt=""/></span>
              </span>
            </a>
          </li>

          <li class="item">
            <a href="'.$config['url'].$news_8['sef'].'/">
              <span class="img">
                <span class="tb"><img src="'.$news_8_img['1']['0'].'" alt=""/></span>
              </span>
              <span class="entry">
                <span class="date">
                 '.$news_8['date'].'
                </span>
                <span class="title">
                  '.$news_8['name'].'
                </span>
                <span class="text">
                  '.$news_8_text.'
                </span>
              </span>
            </a>
          </li>

          <li class="item bg">
            <a href="'.$news_9['link'].'" style="background-image: url('.$news_9['banner_img'].');">
              <span class="info">
                <span class="title">
               '.$news_9['name'].'
                </span>
              </span>
            </a>
          </li>

          <li class="item bot">
            <a href="'.$config['url'].$news_10['sef'].'/">
              <span class="entry">
                <span class="date">
                 '.$news_10['date'].'
                </span>
                <span class="title">
                 '.$news_10['name'].'
                </span>
                <span class="text">
                  '.$news_10_text.'
                </span>
              </span>
              <span class="img">
                <span class="tb"><img src="'.$news_10_img['1']['0'].'" alt=""/></span>
              </span>
            </a>
          </li>

          <li class="item">
            <a href="'.$config['url'].$news_11['sef'].'/">
              <span class="img">
                <span class="tb"><img src="'.$news_11_img['1']['0'].'" alt=""/></span>
              </span>
              <span class="entry">
                <span class="date">
                 '.$news_11['date'].'
                </span>
                <span class="title">
                  '.$news_11['name'].'
                </span>
                <span class="text">
                  '.$news_11_text.'
                </span>
              </span>
            </a>
          </li>

          <li class="item bg">
            <a href="'.$news_12['link'].'" style="background-image: url('.$news_12['banner_img'].');">
              <span class="info">
                <span class="title">
               '.$news_12['name'].'
                </span>
              </span>
            </a>
          </li>

          ';

return $content;
}

function download_hd($id) {
  global $config, $db;
if (intval($id)) {
    $content = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `items` WHERE `id` = \''.mysqli_real_escape_string($db, $id).'\' LIMIT 1;'));
      // print_r($content);
         if ($content) {

$images = json_decode($content['hd'], true);

require_once($_SERVER['DOCUMENT_ROOT'].'/includes/Zip.php');

$fileTime = date("D, d M Y H:i:s T");
$fileDir = './';
$zip = new Zip();
$zip->setComment("HD images file.\nCreated on " . date('l jS \of F Y h:i:s A'));

foreach ($images as $image) {

if (!preg_match('/harper.ru/', $image)) {
$image = str_replace(array('/img/'), array('http://harper.ru/img/'), $image);
}

$filename = basename($image, '.jpg').'.jpg';
$zip->addFile(file_get_contents($image), $filename);
}

$zip->sendZip(translit($content['name']).'.zip');


        }

    }
}

function term($sef) {
  global $config, $db;
$id = 1;
$sql = mysqli_query($db, 'SELECT * FROM `types` WHERE `sef` = \''.mysqli_real_escape_string($db, $sef).'\' order by `id` ASC;');
    if (mysqli_num_rows($sql) != false)
      while ($row = mysqli_fetch_array($sql)) {

        $sql2 = mysqli_query($db, 'SELECT * FROM `categories` WHERE `type` = '.$row['id'].' and `active` = 1 order by `id` ASC;');
        if (mysqli_num_rows($sql2) != false)
          while ($row2 = mysqli_fetch_array($sql2)) {
            $class = ($id == 2) ? 'standart-r' : '';
            $content['body'] .= '<div class="standart '.$class.'">

            <div class="promo">
              <div class="img">
                <img src="'.resizer($row2['img3'], 273, 303, 2).'" alt="'.$row2['name'].'"/>
              </div>
              <div class="entry">
                <div class="inner">

                  <div class="title">
                    '.$row2['land_title'].'
                  </div>

                  <div class="text">
                    '.$row2['land_text'].'
                  </div>

                  <div class="more">
                    <a href="'.$config['url'].$row2['sef'].'/">Все товары</a>
                  </div>

                </div>
              </div>
            </div>';
                 $content['body'] .= '<div class="list"><ul>';

                $sql3 = mysqli_query($db, 'SELECT * FROM `items` WHERE `cat_id` = '.$row2['id'].' and `old` != 1 order by `id` DESC LIMIT 4;');
                if (mysqli_num_rows($sql3) != false)
                while ($row3 = mysqli_fetch_array($sql3)) {

                    if ($row3['icons'] != '') {
                    $icons = json_decode($row3['icons'], true);
                    $id_icon = 1;
                    foreach ($icons as $icon => $img) {

                    $cat_icons = json_decode($row2['icons'], true);
                    foreach ($cat_icons as $icon_id['0'] => $icon) {
                        if (!is_array($icon['0'])) {
                            if ($icon['0'] == $img) {


                            }
                        } else {
                        foreach ($icon['0'] as $img2 => $name2) {
                            if ($img2 == $img) {

                            $name = $name2;

                            }
                        }
                        }
                    }
                    //print_r($cat_icons);
                    $row3['icons_draw'] .= '<div class="item item_shar"><i><img width="34px" src="'.$img.'" alt=""/><span>'.$name.'</span></i></div>';
                    if ($id_icon == 6) {break;}
                    $id_icon++;
                    }
                    }

                    $row3['colors'] = ($row3['colors']) ? '<div class="color"><span>'.item_colors($row3['id']).'</span> </div>' : '';
                    $imgs = json_decode($row3['imgs']);
                    $content['body'] .= '<li class="product-item">
                  <div class="img">
                    <a href="'.$config['url'].$row3['sef'].'/"><img src="'.resizer($imgs['0'], 173, 260, 2).'" alt="'.$row3['name'].'" alt="'.$row3['name'].'"/></a>
                  </div>
                  <div class="entry">
                    <div class="cat">
                     '.$row2['single_name'].'
                    </div>
                    <div class="title">
                      <a href="'.$config['url'].$row3['sef'].'/">'.$row3['name'].' </a>
                    </div>
                    <div class="func">
                     '.$row3['icons_draw'].'
                    </div>
                     '.$row3['colors'].'
                    <div class="hidden">
                      <div class="detail">
                        <a href="'.$config['url'].$row3['sef'].'/">Подробнее</a>
                      </div>
                    </div>
                  </div>';

                }

               $content['body'] .= '</ul></div>';

            $content['body'] .= '</div><!-- .standart -->';

           /*slider*/

$content['body'] .= '  <div class="slider">

    <div class="inner">
      <ul>';

                $sql4 = mysqli_query($db, 'SELECT * FROM `items` WHERE `cat_id` = '.$row2['id'].' order by `id` DESC LIMIT 4, 20;');
                if (mysqli_num_rows($sql4) != false)
                while ($row4 = mysqli_fetch_array($sql4)) {

                    if ($row4['icons'] != '') {
                    $icons = json_decode($row4['icons'], true);
                    $id_icon = 1;  
                    foreach ($icons as $icon => $img) {

                    $cat_icons = json_decode($row2['icons'], true);
                    foreach ($cat_icons as $icon_id['0'] => $icon) {
                        if (!is_array($icon['0'])) {
                            if ($icon['0'] == $img) {


                            }
                        } else {
                        foreach ($icon['0'] as $img2 => $name2) {
                            if ($img2 == $img) {

                            $name = $name2;

                            }
                        }
                        }
                    }

                    //print_r($cat_icons);
                    $row4['icons_draw'] .= '<div class="item item_shar"><i><img width="34px" src="'.$img.'" alt=""/><span>'.$name.'</span></i></div>';
                    if ($id_icon == 6) {break;}
                    $id_icon++;

                    }
                    }

                    $row4['colors'] = ($row4['colors']) ? '<div class="color"><span>'.item_colors($row4['id']).'</span> </div>' : '';
                    $imgs = json_decode($row4['imgs']);
                    $content['body'] .= '

                    <li class="product-item">
                  <div class="img">
                    <a href="'.$config['url'].$row4['sef'].'/"><img src="'.resizer($imgs['0'], 173, 260, 2).'" alt="'.$row4['name'].'" alt="'.$row4['name'].'"/></a>
                  </div>
                  <div class="entry">
                    <div class="cat">
                     '.$row2['single_name'].'
                    </div>
                    <div class="title">
                      <a href="'.$config['url'].$row4['sef'].'/">'.$row4['name'].' </a>
                    </div>
                    <div class="func">
                     '.$row4['icons_draw'].'
                    </div>
                     '.$row4['colors'].'
                    <div class="hidden">
                      <div class="detail">
                        <a href="'.$config['url'].$row4['sef'].'/">Подробнее</a>
                      </div>
                    </div>
                  </div>
                  </li>';

                }

               $content['body'] .= '</ul>
    </div>

    <div class="arr-l"></div>
    <div class="arr-r"></div>

  </div><!-- .slider -->';
           /*slider*/

          if ($id == 2) {$id = 0;}
          $id++;
          }


        $content['title'] = $row['name'];
      }
    return $content;
}

function services($page = '') {
  global $config, $db;
$sql = mysqli_query($db, 'SELECT * FROM `services` order by `city` ASC;');
    if (mysqli_num_rows($sql) != false)
      while ($row = mysqli_fetch_array($sql)) {
        $phone_code = ($row['phone_code'] != 0) ? '('.$row['phone_code'].')' : '';
        $menu .= '<tr>
	<td>'.$row['city'].'</td>
	<td><span class="titled_name">'.$row['name'].'</span></td>
	<td>'.$row['street'].'</td>
	<td>'.$phone_code.' '.$row['phone'].'</td>
</tr>';
      }
    return $menu;
}

/*function landing($id) {
  global $config, $db;

$sql = mysqli_query($db, 'SELECT * FROM `landings` WHERE `item_id` = \''.mysqli_real_escape_string($db, $id).'\' ORDER by `position` ASC;');
if (mysqli_num_rows($sql) != false)

      while ($row = mysqli_fetch_array($sql)) {

      if ($row['text_position'] == 0) {
      $content .= '<div class="item-1" style="background: url('.$row['background'].') center top no-repeat;">
      <div class="wrapper resizable">

        <div class="title draggable resizable">
           '.$row['name'].'
        </div>
        <div class="text draggable resizable">
          '.$row['text'].'
        </div>
        <div class="img draggable2">
          <img src="'.$row['img'].'" class="resizable_img" alt=""/>
        </div>
      </div>
    </div>';
      }
      if ($row['text_position'] == 1) {
      $content .= '
    <div class="item-2">
      <div class="wrapper resizable">
        <div class="title draggable resizable">
          '.$row['name'].'
        </div>
        <div class="text draggable">
          '.$row['text'].'
        </div>
      </div>
    </div>';
      }
      if ($row['text_position'] == 2) {
      $content .= '<div class="item-4">
      <div class="wrapper resizable">
        <div class="img draggable2 ">
          <img src="'.$row['img'].'" class="resizable_img" alt=""/>
        </div>
        <div class="title draggable resizable">
          '.$row['name'].'
        </div>
        <div class="text draggable">
          '.$row['text'].'
        </div>
      </div>
    </div>';
      }

      }

return $content;
} */

function is_editor($id, $item_id) {
  global $config;
  if ($_COOKIE['editor_proof'] == 1)
return '<a style="position:absolute;z-index:1;" href="'.$config['url'].'adm/edit_landing.php?id='.$item_id.'&screen_id='.$id.'">Редактировать экран</a>';
}

function get_icons($screen_id) {
  global $config, $db;

$sql = mysqli_query($db, 'SELECT * FROM `item_icons` WHERE `item_id` = \''.mysqli_real_escape_string($db, $screen_id).'\';');
if (mysqli_num_rows($sql) != false)  {
      $icons = '<ul>';
      while ($row = mysqli_fetch_array($sql)) {
      $icons .= '<li>
        <div class="icon">
          <img src="'.$row['icon'].'" alt="'.$row['name'].'"/>
        </div>
        <div class="entry">
          <div class="title">'.$row['name'].'</div>
        </div></li>';
      }
      $icons .= '</ul>';
}
return $icons;
}

function landing($id) {
  global $config, $db;

$sql = mysqli_query($db, 'SELECT * FROM `landings` WHERE `item_id` = \''.mysqli_real_escape_string($db, $id).'\' ORDER by `position` ASC;');
if (mysqli_num_rows($sql) != false)

      while ($row = mysqli_fetch_array($sql)) {

      /* ИЗОБРАЖЕНИЕ */
      $img_height = ($row['img_height']) ? 'height: '.$row['img_height'].'px;' : '';
      $img_width = ($row['img_width']) ? 'width: '.$row['img_width'].'px;' : '';
      $img_top = ($row['img_top']) ? 'top: '.$row['img_top'].'px;' : '';
      $img_left = ($row['img_left']) ? 'left: '.$row['img_left'].'px;' : '';

      /* ЗАГОЛОВОК */
      $title_height = ($row['title_height']) ? 'height: '.$row['title_height'].'px;' : '';
      $title_width = ($row['title_width']) ? 'width: '.$row['title_width'].'px;' : '';
      $title_top = ($row['title_top']) ? 'top: '.$row['title_top'].'px;' : '';
      $title_left = ($row['title_left']) ? 'left: '.$row['title_left'].'px;' : '';
      $title_color = ($row['title_color']) ? 'color: '.$row['title_color'].';' : '';

      /* ТЕКСТ */
      $text_height = ($row['text_height']) ? 'height: '.$row['text_height'].'px;' : '';
      $text_width = ($row['text_width']) ? 'width: '.$row['text_width'].'px;' : '';
      $text_top = ($row['text_top']) ? 'top: '.$row['text_top'].'px;' : '';
      $text_left = ($row['text_left']) ? 'left: '.$row['text_left'].'px;' : '';
      $text_color = ($row['text_color']) ? 'color: '.$row['text_color'].';' : '';

      /* ОБШИВКА */
      $wrapper_height = ($row['wrapper_height']) ? 'height: '.$row['wrapper_height'].'px;' : '';

      if ($row['text_position'] == 0 && $row['icons_only'] == 0) {
      $content .= '<div class="item-5" style="background: url('.$row['background'].') center top no-repeat;">
      <div class="wrapper resizable" data-type="wrapper" data-id="'.$row['id'].'" style="'.$wrapper_height.'">
       '.is_editor($row['id'], $row['item_id']).'
        <div class="table">
        <div class="cell">
          <div class="title draggable resizable" data-type="title" data-id="'.$row['id'].'" style="position: relative;'.$title_height.$title_width.$title_top.$title_bottom.$title_left.$title_right.$title_color.'"> ';
                    if ($_COOKIE['editor_proof'] == 1) {
          $content .= '<span class="drag" style="position: absolute;top: -10px;left: -20px;background: url(/img/drag.png);height: 20px;width: 20px;cursor:move"></span>';
          }
          $content .= $row['name'].'
          </div>
          <div class="txt draggable resizable" data-type="text" data-id="'.$row['id'].'" style="position: relative;'.$text_height.$text_width.$text_top.$text_bottom.$text_left.$text_right.$text_color.'">';

          if ($_COOKIE['editor_proof'] == 1) {
          $content .= '
          <span class="drag" style="position: absolute;top: -10px;left: -20px;background: url(/img/drag.png);height: 20px;width: 20px;cursor:move"></span>
          <textarea class="text redacted" data-id="'.$row['id'].'">'.$row['text'].'</textarea>';
          } else {
          $content .= $row['text'];
          }

          $content .= '</div>
          <div class="land_icons" style="position: relative;'.$text_top.$text_bottom.$text_left.$text_right.$text_width.'">'.get_icons($row['id']).'</div>
        </div>
        <div class="cell draggable2" data-id="'.$row['id'].'" data-type="img" style="position: relative;'.$img_top.$img_bottom.$img_left.$img_right.'">
        <img data-type="img" id="img_'.$row['id'].'" data-id="'.$row['id'].'" style="position: relative;'.$img_height.$img_width.'" src="'.$row['img'].'" class="resizable_img" alt=""/>
        </div>
      </div>
      </div>
    </div>';
      }
      if ($row['text_position'] == 1 && $row['icons_only'] == 0) {
      $content .= '
 <div class="item-5" style="background: url('.$row['background'].') center top no-repeat;">
      <div class="wrapper resizable" data-type="wrapper" data-id="'.$row['id'].'" style="'.$wrapper_height.'">
       '.is_editor($row['id'], $row['item_id']).'
        <div class="table">
        <div class="cell draggable2" data-id="'.$row['id'].'" data-type="img" style="position: relative;'.$img_top.$img_bottom.$img_left.$img_right.'">
          <img data-type="img" id="img_'.$row['id'].'" data-id="'.$row['id'].'" style="position: relative;'.$img_height.$img_width.'" src="'.$row['img'].'" class="resizable_img" alt=""/>
        </div>
        <div class="cell">
          <div class="title draggable resizable" data-type="title" data-id="'.$row['id'].'" style="position: relative;'.$title_height.$title_width.$title_top.$title_bottom.$title_left.$title_right.$title_color.'">';
          if ($_COOKIE['editor_proof'] == 1) {
          $content .= '<span class="drag" style="position: absolute;top: -10px;left: -20px;background: url(/img/drag.png);height: 20px;width: 20px;cursor:move"></span>';
          }
          $content .= $row['name'].'
          </div>
          <div class="txt draggable resizable" data-type="text" data-id="'.$row['id'].'" style="position: relative;'.$text_height.$text_width.$text_top.$text_bottom.$text_left.$text_right.$text_color.'">';

          if ($_COOKIE['editor_proof'] == 1) {
          $content .= '
          <span class="drag" style="position: absolute;top: -10px;left: -20px;background: url(/img/drag.png);height: 20px;width: 20px;cursor:move"></span>
          <textarea class="text redacted" data-id="'.$row['id'].'">'.$row['text'].'</textarea>';
          } else {
          $content .= $row['text'];
          }

          $content .= '
        </div>
          <div class="land_icons" style="position: relative;'.$text_top.$text_bottom.$text_left.$text_right.$text_width.'">'.get_icons($row['id']).'</div>
        </div>
      </div>
      </div>
    </div>';
      }
      if ($row['text_position'] == 2 && $row['icons_only'] == 0) {
      $content .= '
 <div class="item-5" style="background: url('.$row['background'].') center top no-repeat;">
      <div class="wrapper resizable" data-type="wrapper" data-id="'.$row['id'].'" style="'.$wrapper_height.'">
       '.is_editor($row['id'], $row['item_id']).'
        <div class="table">
        <div class="cell draggable2" data-id="'.$row['id'].'" data-type="img" style="position: relative;'.$img_top.$img_bottom.$img_left.$img_right.'">
         <img data-type="img" id="img_'.$row['id'].'" data-id="'.$row['id'].'" style="position: relative;'.$img_height.$img_width.'" src="'.$row['img'].'" class="resizable_img" alt=""/>
         </div>
        <div class="cell">
          <div class="title draggable resizable" data-type="title" data-id="'.$row['id'].'" style="position: relative;'.$title_height.$title_width.$title_top.$title_bottom.$title_left.$title_right.$title_color.'">';
          if ($_COOKIE['editor_proof'] == 1) {
          $content .= '<span class="drag" style="position: absolute;top: -10px;left: -20px;background: url(/img/drag.png);height: 20px;width: 20px;cursor:move"></span>';
          }
          $content .= $row['name'].'
          </div>
          <div class="txt draggable resizable" data-type="text" data-id="'.$row['id'].'" style="position: relative;'.$text_height.$text_width.$text_top.$text_bottom.$text_left.$text_right.$text_color.'">';

          if ($_COOKIE['editor_proof'] == 1) {
          $content .= '
          <span class="drag" style="position: absolute;top: -10px;left: -20px;background: url(/img/drag.png);height: 20px;width: 20px;cursor:move"></span>
          <textarea class="text redacted" data-id="'.$row['id'].'">'.$row['text'].'</textarea>';
          } else {
          $content .= $row['text'];
          }

          $content .= '</div>
          <div class="land_icons" style="position: relative;'.$text_top.$text_bottom.$text_left.$text_right.$text_width.'">'.get_icons($row['id']).'</div>
        </div>
      </div>
      </div>
    </div>';
      }

      if ($row['icons_only'] == 1) {
      $content .= '<div class="step-4 wrapper" style="min-height: 180px;margin-bottom: 60px;">
      <div class="inner">
        <ul>';

      $sql_icons = mysqli_query($db, 'SELECT * FROM `item_icons` WHERE `item_id` = '.$row['id']);
      if (mysqli_num_rows($sql_icons) != false) {
        while ($row_icons = mysqli_fetch_array($sql_icons)) {

       $content .= '<li>
        <div class="icon">
          <img src="'.$row_icons['icon'].'" alt=""/>
        </div>
        <div class="entry">
          <div class="title" style="font-weight: 400;">
           '.$row_icons['name'].'
          </div>
        </div>
        <a href=""></a>';
        }
      }

   $content .=  '</ul>
      </div>
      <div class="arr-l"></div>
      <div class="arr-r"></div>
    </div>';
      }

      }

return $content;
}

function landing_test($id) {
  global $config, $db;
if ($_POST['name']) {
$row['text_position'] = $_POST['position_text'];
$row['background'] = $_POST['background_file'];
$row['name'] = $_POST['name'];
$row['text'] = $_POST['text'];
$row['img'] = $_POST['files_preview']['0'];

      if ($row['text_position'] == 0) {
      $content = '<div class="item-1" style="background: url('.$row['background'].') center top no-repeat;">
      <div class="wrapper">
        <div class="title">
           '.$row['name'].'
        </div>
        <div class="text" id="draggable">
          '.$row['text'].'
        </div>
        <div class="img">
          <img src="'.$row['img'].'" alt=""/>
        </div>
      </div>
    </div>';
      }
      if ($row['text_position'] == 1) {
      $content = '
    <div class="item-2">
      <div class="wrapper">
        <div class="title">
          '.$row['name'].'
        </div>
        <div class="text" id="draggable">
          '.$row['text'].'
        </div>
      </div>
    </div>';
      }
      if ($row['text_position'] == 2) {
      $content = '<div class="item-4">
      <div class="wrapper">
        <div class="img">
          <img src="'.$row['img'].'" alt=""/>
        </div>
        <div class="title">
          '.$row['name'].'
        </div>
        <div class="text" id="draggable">
          '.$row['text'].'
        </div>
      </div>
    </div>';
      }

return $content;
}
}

function get_specials($id) {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `item_specials` WHERE `item_id` = '.$id);
if (mysqli_num_rows($sql) != false) {
      $content .= '<div class="char"><div class="title">Особенности</div><ul>';
      while ($row = mysqli_fetch_array($sql)) {
       $content .= '<li>'.$row['special'].';</li>';
      }
      $content .= '</ul></div>';
}
return $content;
}


function get_color_code($code) {
  global $db;
return mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `colors` WHERE `name` = \''.$code.'\' LIMIT 1'));
}

function get_colors($id) {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `item_colors` WHERE `item_id` = '.$id);
if (mysqli_num_rows($sql) != false) {
      while ($row = mysqli_fetch_array($sql)) {
       $color = get_color_code($row['color']);
       $border = ($color['code'] == "#FFF") ? 'border: 1px solid #ccc;' : '';
       $content .= '<div class="item">
          <div class="icon" style="background: '.$color['code'].';'.$border.'"></div>
          <div class="text">'.$color['name_ru'].'</div>
        </div>';
      }
}
return $content;
}

function content($sef, $cat_id) {
  global $config, $db;
$sef = strip_tags($sef);
$sql = mysqli_query($db, 'SELECT * FROM `items` WHERE `sef` = \''.mysqli_real_escape_string($db, $sef).'\' LIMIT 1;');

    if (mysqli_num_rows($sql) != false)
      while ($row = mysqli_fetch_array($sql)) {
        $content = $row;

        # Иконки:
        if ($row['icons'] != '') {
        $icons = json_decode($row['icons'], true);
        foreach ($icons as $icon) {
        $content['icons_draw'] .= '<img src="'.$icon.'" />';
        }
        }

        $content['cat_info'] = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `categories` WHERE `id` = '.$row['cat_id'].';'));
        $content['type_info'] = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `types` WHERE `id` = '.$content['cat_info']['type'].';'));
        $content['gallery'] = ($row['imgs']) ? drow_slider($row['imgs']) : '';
        $content['videos'] = ($row['videos'] != '') ? $row['videos'] : 'По этому товару еще нет видео.';
        $content['hd'] = ($row['hd'] != '') ? $row['videos'] : 'По этому товару еще нет видео.';

        $presents_row = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `presents` WHERE `item_id` = '.$row['id'].' LIMIT 1'));
        if ($presents_row['file']) { $content['present'] = '<br><br><a href="'.$presents_row['file'].'">Посмотреть презентацию товара</a>'; }

        $content['gallery'] = str_replace('HD', '<a href="http://harper.ru/get-hd/'.$row['id'].'/">Скачать фотографии в высоком разрешении</a>'.$content['present'], $content['gallery']);


      }

    else die('Error');
    return $content;
}

function content_new($sef, $cat_id) {
  global $config, $db;
$sef = strip_tags($sef);
$sql = mysqli_query($db, 'SELECT * FROM `items` WHERE `sef` = \''.mysqli_real_escape_string($db, $sef).'\' LIMIT 1;');

    if (mysqli_num_rows($sql) != false)
      while ($row = mysqli_fetch_array($sql)) {
        $content = $row;

        $content['cat_info'] = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `categories` WHERE `id` = '.$row['cat_id'].';'));

        if ($row['imgs']) {
        $images = json_decode($row['imgs']);
        if ($images) {
        foreach ($images as $img) {
        $content['main_img'] .= $config['url'].$img;
        break;
        }
        }
        }
       // echo $content['main_img'];
        if (!$row['img_main']) {
        $content['img_main'] = $content['main_img'];
        }

        # Иконки:
        if ($row['icons'] != '') {
        $icons = json_decode($row['icons'], true);
        foreach ($icons as $icon => $img) {

        $cat_icons = json_decode($content['cat_info']['icons'], true);
        foreach ($cat_icons as $icon_id['0'] => $icon) {
            if (!is_array($icon['0'])) {
                if ($icon['0'] == $img) {


                }
            } else {
            foreach ($icon['0'] as $img2 => $name2) {
                if ($img2 == $img) {

                $name = $name2;

                }
            }
            }
        }

        //print_r($cat_icons);
        $name = ($name) ? '<p>'.$name.'</p>' : '';;
        $content['icons_draw'] .= '<div class="item item-1"><i><img src="'.$img.'" alt=""/></i><div>'.$name.'</div></div>';

        }
        }

        $content['type_info'] = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `types` WHERE `id` = '.$content['cat_info']['type'].';'));
        $content['gallery'] = ($row['imgs']) ? drow_slider_new($row['imgs']) : '';
        $content['videos'] = ($row['videos'] != '') ? $row['videos'] : 'По этому товару еще нет видео.';
        $content['hd'] = ($row['hd'] != '') ? $row['videos'] : 'По этому товару еще нет видео.';
        $video_big = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `item_videos` WHERE `item_id` = '.$row['id'].' ORDER by `id` DESC LIMIT 1;'));
        parse_str( parse_url( $video_big['video'], PHP_URL_QUERY ), $my_array_of_vars );
        parse_str(file_get_contents("http://youtube.com/get_video_info?video_id=".$my_array_of_vars['v']), $ytarr);
        $content['video_big']['code'] = $my_array_of_vars['v'];
        $content['video_big']['title'] = $ytarr['title'];
       // print_r($ytarr);
        $content['video_big']['img'] = 'http://i.ytimg.com/vi/'.$my_array_of_vars['v'].'/hqdefault.jpg';
        $content['other_vids'] = other_vids($row['id']);
        $presents_row = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `presents` WHERE `item_id` = '.$row['id'].' LIMIT 1'));
        if ($presents_row['file']) {
        $content['present'] = '<li class="t-1"><a href="'.$presents_row['file'].'"><span>Скачать <span>презентацию</span></span></a></li>';
        }

        $content['specials'] = get_specials($row['id']);
        $content['colors'] = get_colors($row['id']);
        $content['gallery'] = str_replace('HD', '<a href="http://harper.ru/get-hd/'.$row['id'].'/">Скачать фотографии в высоком разрешении</a>'.$content['present'], $content['gallery']);
        $content['HD'] = '<li class="t-4"><a href="http://harper.ru/get-hd/'.$row['id'].'/"><span>Скачать фото</span></a></li>';

      }

    else die('Error');
    return $content;
}

function content_new_id($id) {
  global $config, $db;
$sql = mysqli_query($db, 'SELECT * FROM `items` WHERE `id` = \''.mysqli_real_escape_string($db, $id).'\' LIMIT 1;');

    if (mysqli_num_rows($sql) != false)
      while ($row = mysqli_fetch_array($sql)) {
        $content = $row;

        $content['cat_info'] = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `categories` WHERE `id` = '.$row['cat_id'].';'));

        if ($row['imgs']) {
        $images = json_decode($row['imgs']);
        if ($images) {
        foreach ($images as $img) {
        $content['main_img'] .= $config['url'].$img;
        break;
        }
        }
        }
       // echo $content['main_img'];
        if (!$row['img_main']) {
        $content['img_main'] = $content['main_img'];
        }

        # Иконки:
        if ($row['icons'] != '') {
        $icons = json_decode($row['icons'], true);
        foreach ($icons as $icon => $img) {

        $cat_icons = json_decode($content['cat_info']['icons'], true);
        foreach ($cat_icons as $icon_id['0'] => $icon) {
            if (!is_array($icon['0'])) {
                if ($icon['0'] == $img) {


                }
            } else {
            foreach ($icon['0'] as $img2 => $name2) {
                if ($img2 == $img) {

                $name = $name2;

                }
            }
            }
        }

        //print_r($cat_icons);
        $name = ($name) ? '<p>'.$name.'</p>' : '';;
        $content['icons_draw'] .= '<div class="item item-1"><i><img src="'.$img.'" alt=""/></i><div>'.$name.'</div></div>';

        }
        }

        $content['type_info'] = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `types` WHERE `id` = '.$content['cat_info']['type'].';'));
        $content['gallery'] = ($row['imgs']) ? drow_slider_new($row['imgs']) : '';
        $content['videos'] = ($row['videos'] != '') ? $row['videos'] : 'По этому товару еще нет видео.';
        $content['hd'] = ($row['hd'] != '') ? $row['videos'] : 'По этому товару еще нет видео.';
        $video_big = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `item_videos` WHERE `item_id` = '.$row['id'].' ORDER by `id` DESC LIMIT 1;'));
        parse_str( parse_url( $video_big['video'], PHP_URL_QUERY ), $my_array_of_vars );
        parse_str(file_get_contents("http://youtube.com/get_video_info?video_id=".$my_array_of_vars['v']), $ytarr);
        $content['video_big']['code'] = $my_array_of_vars['v'];
        $content['video_big']['title'] = $ytarr['title'];
        $content['video_big']['img'] = $ytarr['iurlhq'];
        $content['other_vids'] = other_vids($row['id']);
        $presents_row = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `presents` WHERE `item_id` = '.$row['id'].' LIMIT 1'));
        if ($presents_row['file']) {
        $content['present'] = '<li class="t-1"><a href="'.$presents_row['file'].'"><span>Скачать <span>презентацию</span></span></a></li>';
        }

        $content['specials'] = get_specials($row['id']);
        $content['colors'] = get_colors($row['id']);
        $content['gallery'] = str_replace('HD', '<a href="http://harper.ru/get-hd/'.$row['id'].'/">Скачать фотографии в высоком разрешении</a>'.$content['present'], $content['gallery']);
        $content['HD'] = '<li class="t-4"><a href="http://harper.ru/get-hd/'.$row['id'].'/"><span>Скачать фото</span></a></li>';

      }

    else die('Error');
    return $content;
}

function other_vids($id) {
  global $config, $db;
$sef = strip_tags($sef);
$sql = mysqli_query($db, 'SELECT * FROM `item_videos` WHERE `item_id` = '.$id.' ORDER by `id` DESC LIMIT 1, 5;');

    if (mysqli_num_rows($sql) != false)
      while ($row = mysqli_fetch_array($sql)) {

      parse_str( parse_url( $row['video'], PHP_URL_QUERY ), $my_array_of_vars );
      parse_str(file_get_contents("http://youtube.com/get_video_info?video_id=".$my_array_of_vars['v']), $ytarr);
      //print_r($ytarr);
      $content = '<li>
            <a class="fancybox fancybox.iframe" href="http://www.youtube.com/embed/'.$my_array_of_vars['v'].'?autoplay=1">
              <span class="img">
                <img src="'.$ytarr['iurl'].'" width="252px" alt=""/>
              </span>
              <span class="entry">
                <span class="title">
                  '.$ytarr['title'].'
                </span>
                <span class="sign">
                  Видео с youtube
                </span>
              </span>
            </a>
          </li>';

      }

return $content;
}

function news($sef, $cat_id) {
  global $config, $db;
$sef = strip_tags($sef);
$sql = mysqli_query($db, 'SELECT * FROM `news_new` WHERE `sef` = \''.mysqli_real_escape_string($db, $sef).'\' LIMIT 1;');

    if (mysqli_num_rows($sql) != false)
      while ($row = mysqli_fetch_array($sql)) {
        $content = $row;
      }

    else die('Error');
    return $content;
}

function brand_info_get($brand) {
  global $config, $db;
$sef = strip_tags($sef);
$sql = mysqli_query($db, 'SELECT * FROM `brands` WHERE `name` = \''.mysqli_real_escape_string($db, $brand).'\' LIMIT 1;');

    if (mysqli_num_rows($sql) != false)
      while ($row = mysqli_fetch_array($sql)) {
        $content = $row;
        $content['percent'] = $row['agent_percent']/100;
      }

    else die('Error');
    return $content;
}


function news_index() {
  global $config, $db;
$sef = strip_tags($sef);
$sql = mysqli_query($db, 'SELECT * FROM `news_new` ORDER by `id` DESC LIMIT 3;');

    if (mysqli_num_rows($sql) != false)
      while ($row = mysqli_fetch_array($sql)) {
        preg_match_all('/<img [^>]*src=["|\']([^"|\']+)/i', $row['text'], $img_matches);
                foreach ($img_matches['1'] as $imgs) {
                  $img_html .= '<img src="'.$imgs.'" style="width: 231px;">';
                  }
        $content .= '<li>
                	<p class="date">'.$row['date'].'</p>
                    <p class="name"><a href="'.$config['url'].$row['sef'].'/">'.$row['name'].'</a></p>
                    <div class="img">'.$img_html.'</div>
					<p class="more"><a href="'.$config['url'].$row['sef'].'/">Узнать подробнее</a></p>
                </li>';
                unset($img_html);
      }

    return $content;

}

function shops_list($shops = '') {
  global $config, $db;

if ($shops) {
$sql_add = 'where `name` IN (\''.$shops.'\')';
}
$sql = mysqli_query($db, 'SELECT * FROM `shops` '.$sql_add.' ORDER by `id` ASC;');
    if (mysqli_num_rows($sql) != false)
      while ($row = mysqli_fetch_array($sql)) {
        $content['shops'] .= '<li>
      <a href="'.$row['website'].'">
        <span class="logo">
          <span><img src="'.resizer($row['logo'], 113, 198, 2).'" alt=""/></span>
        </span>
        <span class="entry">
          <span class="title">
            '.$row['name'].'
          </span>
          <span class="url">
            '.$row['website'].'
          </span>
          <span class="phone">
           '.$row['phone'].'
          </span>
        </span>
      </a>';
      }

    return $content;

}

function numberFormat($digit, $width) {
    while(strlen($digit) < $width)
      $digit = '0' . $digit;
      return $digit;
}

function qa_add($question, $name, $email, $item_id) {

require($_SERVER['DOCUMENT_ROOT'].'/includes/PHPMailer-master/PHPMailerAutoload.php');

  global $config, $db;
/*mysqli_query($db, 'INSERT INTO `qa` (
`item_id`,
`date`,
`email`,
`name`,
`question`
) VALUES (
\''.mysqli_real_escape_string($db, $item_id).'\',
\''.mysqli_real_escape_string($db, date("d.m.Y")).'\',
\''.mysqli_real_escape_string($db, strip_tags($email)).'\',
\''.mysqli_real_escape_string($db, strip_tags($name)).'\',
\''.mysqli_real_escape_string($db, strip_tags($question)).'\'
);');                         */
//setcookie('qa', 'ok', time()+3);

mysqli_query($db, 'INSERT INTO `feedback_admin` (
`date`,
`name`,
`email`,
`phone`,
`message`,
`model_id`,
`status`,
`md5`,
`geoip`
) VALUES (
\''.mysqli_real_escape_string($db, date("Y-m-d H:i:s")).'\',
\''.mysqli_real_escape_string($db, $_POST['name']).'\',
\''.mysqli_real_escape_string($db, $_POST['email']).'\',
\''.mysqli_real_escape_string($db, $_POST['phone']).'\',
\''.mysqli_real_escape_string($db, $_POST['question']).'\',
\''.mysqli_real_escape_string($db, $item_id).'\',
\''.mysqli_real_escape_string($db, 'Вопрос открыт').'\',
\''.mysqli_real_escape_string($db, md5(date("Y-m-d H:i:s"))).'\',
\''.mysqli_real_escape_string($db, $config['geo']['city']['name_ru']).'\'
);') or mysqli_error($db);
$id = mysqli_insert_id($db);

mysqli_query($db, 'INSERT INTO `feedback_messages` (
`feedback_id`,
`message`,
`user_type`,
`date`,
`read`
) VALUES (
\''.mysqli_real_escape_string($db, $id).'\',
\''.mysqli_real_escape_string($db, $_POST['question']).'\',
\'1\',
\''.mysqli_real_escape_string($db, date("Y-m-d H:i:s")).'\',
\'1\'
);') or mysqli_error($db);

/* ОТПРАВИМ АДМИНУ */
$to = $config['email_form'];
$mes = '<html>
                      <body bgcolor="#DCEEFC">
                      <h3>Новый вопрос с harper.ru</h3><br>
                      <strong>Имя:</strong> '.$_POST['name'].'<br>
                      <strong>Телефон:</strong> '.$_POST['phone'].'<br>
                      <strong>E-Mail:</strong> '.$_POST['email'].'<br>
                      <strong>Вопрос:</strong> '.$_POST['question'].'<br>
                      <strong>Обработать: </strong><a href="http://harper.ru/adm/feedback.php?id='.$id.'">http://harper.ru/adm/feedback.php?id='.$id.'</a>
                      </body>
                    </html>';

$mail = new PHPMailer;
$mail->isSMTP();
$mail->Host = $config['mail_host'];
$mail->SMTPAuth = true;
$mail->SMTPSecure = "ssl";
$mail->Username = $config['mail_username'];
$mail->Password = $config['mail_password'];
$mail->Timeout       =  10;
$mail->Port = 465;
$mail->setFrom($config['mail_username'], $config['mail_from']);
$mail->addAddress($to);
$mail->isHTML(true);
$mail->Subject = "Новый вопрос с r97.ru [Q".numberFormat($id, 4)."]";
$mail->CharSet = 'UTF-8';
$mail->Body    = $mes;
$mail->MailerDebug = true;
$mail->send();


/* ОТПРАВИМ ЮЗЕРУ */
$mes = '<html>
                      <body bgcolor="#DCEEFC">
                      <h3>Уважаемый '.$_POST['name'].'.</h3><br>
                      '.date("Y-m-d H:i:s").' к нам поступило Ваше обращение № [Q'.numberFormat($id, 4).']:<br><br>
                      Тема обращения: '.$_POST['questions'].'<br>
                      Ваше имя: '.$_POST['name'].'<br>
                      Ваш телефон: '.$_POST['phone'].'<br>
                      Ваш Email: '.$_POST['email'].'<br>
                      Ваш вопрос: '.$_POST['question'].'<br><br>
<u>В течении ближайшего времени оно будет рассмотрено и Вам будет дан ответ.</u><br><br>
- -  <br>
<b>Пожалуйста, при ответе сохраняйте переписку.<br>
С уважением,  <br>
Служба поддержки HARPER   <br>
<img src="http://harper.ru/img/Picture1.jpg" height="50px"><br>
e-mail: info@harper.com.ru</b>
                      </body>

                    </html>';

$mail = new PHPMailer;
$mail->isSMTP();
$mail->Host = $config['mail_host']; 
$mail->SMTPAuth = true;
$mail->SMTPSecure = "ssl";
$mail->Username = $config['mail_username'];
$mail->Password = $config['mail_password'];
$mail->Timeout       =  10;
$mail->Port = 465;
$mail->setFrom($config['mail_username'], $config['mail_from']);
$mail->addAddress($_POST['email']);
$mail->isHTML(true);
$mail->Subject = "Служба поддержки R97.RU [Q".numberFormat($id, 4)."]";
$mail->CharSet = 'UTF-8';
$mail->Body    = $mes;
$mail->MailerDebug = true;
$mail->send();

//header('Location: '.$_SERVER['HTTP_REFERER']);
}

function review_add($review, $name, $email, $mark, $item_id) {
  global $config, $db;
mysqli_query($db, 'INSERT INTO `reviews` (
`item_id`,
`name`,
`email`,
`review`,
`mark`,
`date`
) VALUES (
\''.mysqli_real_escape_string($db, $item_id).'\',
\''.mysqli_real_escape_string($db, strip_tags($name)).'\',
\''.mysqli_real_escape_string($db, strip_tags($email)).'\',
\''.mysqli_real_escape_string($db, strip_tags($review)).'\',
\''.mysqli_real_escape_string($db, strip_tags($mark)).'\',
\''.mysqli_real_escape_string($db, date("d.m.Y")).'\'
);');
//setcookie('review', 'ok', time()+3);
//header('Location: '.$_SERVER['HTTP_REFERER']);
}


function slider_index() {
  global $config, $db;
$sef = strip_tags($sef);
$sql = mysqli_query($db, 'SELECT * FROM `slider2` ORDER by `id` DESC LIMIT 15;');

    if (mysqli_num_rows($sql) != false)
      while ($row = mysqli_fetch_array($sql)) {
        $active = ($row['active'] == 1) ? 'style="background-image: url('.$row['background'].');"' : '';
        $content .= '<li>
        <div class="bg" '.$active.'></div>
        <div class="wrapper">
          <div class="img">
            <img src="'.$row['img'].'" alt=""/>
          </div>
          <div class="entry">
            <div class="model" style="color:'.$row['color'].'">
              '.$row['name'].'
            </div>
            <div class="title" style="color:'.$row['color'].'">
              '.$row['title'].'
            </div>
            <div class="text" style="color:'.$row['color'].'">
              '.$row['desc'].'
            </div>
            <div class="detail">
              <a href="'.$row['link'].'">ПОДРОБНЕЕ</a>
            </div>
          </div>
        </div>
      </li>';
      }

    return $content;

}

function search($query = '') {
  global $config, $db;
if ($query != '') {
$query = strip_tags($query);
# Товар
$sql = mysqli_query($db, 'SELECT * FROM `items` WHERE `name` LIKE \'%'.mysqli_real_escape_string($db, $query).'%\' or `title` LIKE \'%'.mysqli_real_escape_string($db, $query).'%\' or `short_desc` LIKE \'%'.mysqli_real_escape_string($db, $query).'%\' ORDER by `id` DESC LIMIT 50;');
    $content['items_count']= 0;
    if (mysqli_num_rows($sql) != false)
      while ($row = mysqli_fetch_array($sql)) {
        $cat_info = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `categories` WHERE `id` = \''.mysqli_real_escape_string($db, $row['cat_id']).'\';'));
        $imgs = json_decode($row['imgs']);

        if ($row['icons'] != '') {
        $icons = json_decode($row['icons'], true);
        foreach ($icons as $icon => $img) {

        $cat_icons = json_decode($cat_info['icons'], true);
        foreach ($cat_icons as $icon_id['0'] => $icon) {
            if (!is_array($icon['0'])) {
                if ($icon['0'] == $img) {


                }
            } else {
            foreach ($icon['0'] as $img2 => $name2) {
                if ($img2 == $img) {

                $name = $name2;

                }
            }
            }
        }

        //print_r($cat_icons);
        $content['icons_draw'] .= '<div class="item item_shar"><i><img width="34px" src="'.$img.'" alt=""/><span>'.$name.'</span></i></div>';

        }
        }

        $content['body_items'] .= '<li class="product-item">
          <div class="img">
            <a href="'.$config['url'].$row['sef'].'/"><img width="253" height="183" src="'.resizer($imgs['0'], 183, 253, 2).'" alt=""/></a>
          </div>
          <div class="entry">
            <div class="cat">
              '.$cat_info['single_name'].'
            </div>
            <div class="title">
              <a href="'.$config['url'].$row['sef'].'/">'.$row['name'].'</a>
            </div>
            <div class="func">
            '.$content['icons_draw'].'
            </div>
            <div class="color">
              <span>'.item_colors($row['id']).'</span>
            </div>
            <div class="hidden">
              <div class="detail">
                <a href="'.$config['url'].$row['sef'].'/">Подробнее</a>
              </div>
            </div>
          </div>
          <!--<div class="leader"></div>-->';
          $content['items_count']++;
          unset($content['icons_draw']);
      }

$content['reviews_count'] = 0;

# Новости
$sql = mysqli_query($db, 'SELECT * FROM `news_new` WHERE `name` LIKE \'%'.mysqli_real_escape_string($db, $query).'%\' or `title` LIKE \'%'.mysqli_real_escape_string($db, $query).'%\' or `text` LIKE \'%'.mysqli_real_escape_string($db, $query).'%\' ORDER by `id` DESC LIMIT 50;');
      $content['news_count']= 0;
    if (mysqli_num_rows($sql) != false)
       //$content['body'] .= '<br><h3 style="margin-left: -0px;">Поиск по новостям</h3>';
      while ($row = mysqli_fetch_array($sql)) {
        preg_match_all('/<img [^>]*src=["|\']([^"|\']+)/i', $row['text'], $img_matches);
        $content['body_news'] .= '<li>
          <a href="'.$config['url'].$row['sef'].'/">
            <span class="img">
              <span><img src="'.resizer(str_replace('http://harper.ru/resize.php?src=', '', $img_matches['1']['0']), 175, 263, 2).'" alt="'.$row['name'].'"/></span>
            </span>
            <span class="entry">
              <span class="date">
                '.$row['date'].'
              </span>
              <span class="title">
               '.$row['name'].'
              </span>
            </span>
            <!--<span class="video">Видео</span>-->
          </a>
          </li>';
          $content['news_count']++;
      }

# Инструкции
$sql = mysqli_query($db, 'SELECT * FROM `manuals` WHERE `name` LIKE \'%'.mysqli_real_escape_string($db, $query).'%\' or `desc` LIKE \'%'.mysqli_real_escape_string($db, $query).'%\' ORDER by `id` DESC LIMIT 50;');
    if (mysqli_num_rows($sql) != false)
       //$content['body'] .= '<br><h3 style="margin-left: -0px;">Поиск по новостям</h3>';
      $content['manuals_count']= 0;
      while ($row = mysqli_fetch_array($sql)) {
        $item = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `items` WHERE `id` = '.$row['item_id'].' LIMIT 1;'));
        $imgs = json_decode($item['imgs']);

        $content['body_manuals'] .= '<li>
          <a href="'.$row['file'].'">
            <span class="img">
              <span><img src="'.resizer($imgs['0'], 175, 263, 2).'" alt="'.$item['name'].'"/></span>
            </span>
            <span class="entry">
              <span class="date">
                '.$row['date'].'
              </span>
              <span class="title">
               '.str_replace('http://harper.ru/files/manuals/', '', $row['file']).'

              </span>
            </span>
            <!--<span class="video">Видео</span>-->
          </a>
          </li>';
          $content['manuals_count']++;
      }

# Прошивки
$sql = mysqli_query($db, 'SELECT * FROM `firmware` WHERE `name` LIKE \'%'.mysqli_real_escape_string($db, $query).'%\' or `desc` LIKE \'%'.mysqli_real_escape_string($db, $query).'%\' ORDER by `id` DESC LIMIT 50;');
    if (mysqli_num_rows($sql) != false)
       //$content['body'] .= '<br><h3 style="margin-left: -0px;">Поиск по новостям</h3>';
       $content['firmware_count']= 0;
      while ($row = mysqli_fetch_array($sql)) {

        $item = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `items` WHERE `id` = '.$row['item_id'].' LIMIT 1;'));
        $imgs = json_decode($item['imgs']);
        $content['body_firmware'] .= '<li>
          <a href="'.$row['file'].'">
            <span class="img">
              <span><img src="'.resizer($imgs['0'], 175, 263, 2).'" alt="'.$item['name'].'"/></span>
            </span>
            <span class="entry">
              <span class="date">
                '.$row['date'].'
              </span>
              <span class="title">
               '.str_replace('http://harper.ru/files/firmware/', '', $row['file']).'

              </span>
            </span>
            <!--<span class="video">Видео</span>-->
          </a>
          </li>';
          $content['firmware_count']++;
      }

} else {
$content['body'] = '<br>Поиск не дал результатов.';
}
$content['query'] = $query;
return $content;
}

function gen_models($query) {
  global $config, $db;
$sql = mysqli_query($db, 'SELECT `name` FROM `items` WHERE `name` REGEXP \''.mysqli_real_escape_string($db, $query).'\';');
    if (mysqli_num_rows($sql) != false)
      while ($row = mysqli_fetch_array($sql)) {
       $content[] = $row['name'];
      }
return $content;
}

function get_cities($query = '', $list = 1) {
  global $config, $db;
$sql = mysqli_query($db, 'SELECT MIN(id), city, country FROM `services` WHERE `country` = '.$query.' and `list` = '.$list.' GROUP BY city ORDER BY `city` ASC;');
    if (mysqli_num_rows($sql) != false)
      while ($row = mysqli_fetch_array($sql)) {
       $content .= '<option value="'.$row['city'].'" data-country="'.$row['country'].'">'.$row['city'].'</option>';
      }
return $content;
}

function gen_parts($repair_id, $id = '') {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `parts_sc` where `repair_id` = \''.$repair_id.'\';');
      while ($row = mysqli_fetch_array($sql)) {
      $part = @mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `parts` WHERE `id` = '.$row['part_id']));
      if ($id == $row['id']) {
      $content .= '<option selected value="'.$row['id'].'">'.str_replace('\'', '', htmlspecialchars(stripslashes($part['list']))).'</option>';
      } else {
       $content .= '<option value="'.$row['id'].'">'.str_replace('\'', '', htmlspecialchars(stripslashes($part['list']))).'</option>';
      }
      }
    return $content;
}

function gen_parts33($repair_id, $id = '') {
  global $db;
$repair =  mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `repairs` WHERE `id` = \''.mysqli_real_escape_string($db, $repair_id).'\''));
//$first_serial = mysqli_fetch_array(mysqli_query($db, 'SELECT `first_serial` FROM `serials` WHERE `serial` = \''.mysqli_real_escape_string($db, $repair['serial']).'\' and `model_id` = '.$repair['model_id']));
//$count1 = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `parts` /*where `model_id` = \''.$repair['model_id'].'\' and `serial` = \''.$first_serial['first_serial'].'\' and `parent_id` = 0*/;'));

//$serial_sql = ($repair['serial']) ? 'and `serial` = \''.$first_serial['first_serial'].'\'' : '';

/*if ($count1['COUNT(*)'] > 0) {
$sql = mysqli_query($db, 'SELECT * FROM `parts` where `model_id` = \''.$repair['model_id'].'\' '.$serial_sql.' and `parent_id` = 0;');
} else {

$sql = mysqli_query($db, 'SELECT * FROM `parts` where `model_id` = \''.$repair['model_id'].'\' '.$serial_sql.';');

} */

$sql = mysqli_query($db, 'SELECT * FROM `parts` where `parent_id` = 0 and `model_id` = \''.$repair['model_id'].'\' and `count` > 0 order by `list` desc');
//echo 'SELECT * FROM `parts` where `parent_id` = 0 '.$where.' order by `list` ASC';
if (mysqli_num_rows($sql) > 0) {
      while ($row = mysqli_fetch_array($sql)) {
    $sel = ($row['id'] == $id) ? 'selected' : '';
    $content2 .= '<option value="'.$row['id'].'" '. $sel.'>'.str_replace('\'', '', htmlspecialchars(stripslashes($row['list']))).'</option>';


}
}

/*дочки*/
$sql = mysqli_query($db, 'SELECT * FROM `parts` WHERE id = '.$id.' or id IN (SELECT MAX(id) FROM parts where `count` > 0 and `model_id` = \''.$repair['model_id'].'\' AND `parent_id` != 0 AND `parent_id` != \'\' GROUP BY parent_id ) ORDER BY id desc');
//echo 'SELECT * FROM `parts` WHERE id = '.$id.' or id IN ('.$id.', SELECT MAX(id) FROM parts where `count` > 0 and `model_id` = \''.$repair['model_id'].'\' AND `parent_id` != 0 AND `parent_id` != \'\' GROUP BY parent_id ) ORDER BY id desc';
//echo 'SELECT * FROM `parts` WHERE id = '.$id.' or id IN (SELECT MAX(id) FROM parts where `count` > 0 and `model_id` = \''.$repair['model_id'].'\' AND `parent_id` != 0 AND `parent_id` != \'\' GROUP BY parent_id ) ORDER BY id desc';
//$sql = mysqli_query($db, 'SELECT * FROM `parts` where `parent_id` != \'\' and `parent_id` != 0 '.$where.' and `count` > 0 order by id desc');

//echo 'SELECT * FROM `parts` where `parent_id` != \'\' and `parent_id` != 0 '.$where.' and `count` > 0 order by id desc';
//echo 'SELECT * FROM `parts` where `parent_id` = 0 '.$where.' order by `list` ASC';
if (mysqli_num_rows($sql) > 0) {
      while ($row = mysqli_fetch_array($sql)) {
    $sel = ($row['id'] == $id) ? 'selected' : '';
    $content2 .= '<option value="'.$row['id'].'" '. $sel.'>'.str_replace('\'', '', htmlspecialchars(stripslashes($row['list']))).'</option>';

}
}

      /* if (mysqli_num_rows($sql) > 0 && $id != '') {
      while ($row = mysqli_fetch_array($sql)) {
      if ($id == $row['id']) {
      $content .= '<option selected value="'.$row['id'].'">'.str_replace('\'', '', htmlspecialchars(stripslashes($row['list']))).'</option>';
      } else {
       $content .= '<option value="'.$row['id'].'">'.str_replace('\'', '', htmlspecialchars(stripslashes($row['list']))).'</option>';
      }
      } */

if ($content2) {
return $content2;
} else {
return false;
}


}

function get_cities_shops($query = '', $list = 1) {
  global $config, $db;
$sql = mysqli_query($db, 'SELECT MIN(id), city, country FROM `shops_list` WHERE `country` = '.$query.' and `list` = '.$list.' GROUP BY city ORDER BY `city` ASC;');
    if (mysqli_num_rows($sql) != false)
      while ($row = mysqli_fetch_array($sql)) {
       $content .= '<option value="'.$row['city'].'" data-country="'.$row['country'].'">'.$row['city'].'</option>';
      }
return $content;
}

function names_array($names) {
$names_exp = explode(',', $names);
foreach ($names_exp as $name) {
$names_new[] = '\''.$name.'\'';
}
return implode(',', $names_new);
}

function get_cities_shops_item($query = '', $list = 1, $names) {
  global $config, $db;
$names = names_array($names);
$sql = mysqli_query($db, 'SELECT MIN(id), city, country FROM `shops_list` WHERE `country` = '.$query.' and `list` = '.$list.' and `name` IN ('.$names.') GROUP BY city ORDER BY `city` ASC;');
    if (mysqli_num_rows($sql) != false)
      while ($row = mysqli_fetch_array($sql)) {
       $content .= '<option value="'.$row['city'].'" data-country="'.$row['country'].'">'.$row['city'].'</option>';
      }
return $content;
}

function get_cities_country_by_model($query) {
  global $config, $db;

if ($query) {

$sql_check = mysqli_query($db, 'SELECT * FROM `items` WHERE `name` LIKE \'%'.mysqli_real_escape_string($db, $query).'%\' LIMIT 1;');

if (mysqli_num_rows($sql_check) != false) {

$item = mysqli_fetch_array($sql_check);
if ($item['service'] != 0) {

$list = $item['service_list'];
$sql = mysqli_query($db, 'SELECT MIN(id), city, country FROM `services` WHERE `country` = 1 and `list` = '.$list.' GROUP BY city ORDER by city ASC;');
    if (mysqli_num_rows($sql) != false) {
      $id = 1;
      while ($row = mysqli_fetch_array($sql)) {
       if ($id == 1) { $content['options'] .= '<option value="all" data-country="'.$row['country'].'">Все города</option>'; }
       $content['options'] .= '<option value="'.$row['city'].'" data-country="'.$row['country'].'">'.$row['city'].'</option>';
       $id++;
      }
    } else {
    $content['options'] .= '<option value="" data-country="'.$row['country'].'">СЦ в этой стране еще нет</option>';
    }

# Все по стране:
$query = strip_tags($query);
$sql = mysqli_query($db, 'SELECT * FROM `services` WHERE `country` = 1 and `list` = '.$list.';');
    if (mysqli_num_rows($sql) != false) {
      $i = 0;
      $content['body'] = '      <div class="head">
        <div class="city">Город</div>
        <div class="name">Название центра</div>
        <div class="addr">Адрес</div>
        <div class="phone">Номер телефона</div>
      </div>';
      while ($row = mysqli_fetch_array($sql)) {
       $content['body'] .= '<li>
          <a href="">
            <span class="city">
              '.$row['city'].'
            </span>
            <span class="name">
              '.$row['name'].'
            </span>
            <span class="addr">
              '.$row['street'].'
            </span>
            <span class="phone">
              ('.$row['phone_code'].') '.$row['phone'].'
            </span>
          </a>
        </li>';
       $service = explode(',', $row['service_coord']);
       $content['list'] = $list;
       $content['map_services'][] = array('coords' => array('mapx' => $service['1'], 'mapy' => $service['0']), 'street' => $row['street'], 'phone' => '('.$row['phone_code'].') '.$row['phone']);
       $i++;
      }
      }

} else {
$content['body'] = '<br>Данная модель не обслуживается в сервисных центрах. Если в течение гарантийного срока (указан в гарантийном талоне) вы столкнулись с какими-то проблемами по этой модели, обратитесь в торгующую организацию, где вы приобрели данную модель для замены на аналогичную.<br><br> При возникновении вопросов или проблем, вы всегда можете обратиться к нам на странице контакты. Пишите нам, мы поможем.';
}

} else {
$content['body'] = '<br>Поиск не дал результатов.';
}
} else {
$content['body'] = '<br>Поиск не дал результатов.';
}

return $content;
}

function get_cities_country($query = '', $list = 1) {
  global $config, $db;
$sql = mysqli_query($db, 'SELECT MIN(id), city, country FROM `services` WHERE `country` = '.$query.' and `list` = '.$list.' GROUP BY city ORDER BY city ASC;');
    if (mysqli_num_rows($sql) != false) {
      $id = 1;
      while ($row = mysqli_fetch_array($sql)) {
       if ($id == 1) { $content['options'] .= '<option value="all" data-country="'.$row['country'].'">Все города</option>'; }
       $content['options'] .= '<option value="'.$row['city'].'" data-country="'.$row['country'].'">'.$row['city'].'</option>';
       $id++;
      }
    } else {
    $content['options'] .= '<option value="" data-country="'.$row['country'].'">СЦ в этой стране еще нет</option>';
    }

# Все по стране:
$query = strip_tags($query);
$sql = mysqli_query($db, 'SELECT * FROM `services` WHERE `country` = '.$query.' and `list` = '.$list.';');
    if (mysqli_num_rows($sql) != false) {
      $i = 0;
      $content['body'] = '      <div class="head">
        <div class="city">Город</div>
        <div class="name">Название центра</div>
        <div class="addr">Адрес</div>
        <div class="phone">Номер телефона</div>
      </div>';
      while ($row = mysqli_fetch_array($sql)) {
       $content['body'] .= '<li>
          <a href="">
            <span class="city">
              '.$row['city'].'
            </span>
            <span class="name">
              '.$row['name'].'
            </span>
            <span class="addr">
              '.$row['street'].'
            </span>
            <span class="phone">
              ('.$row['phone_code'].') '.$row['phone'].'
            </span>
          </a>
        </li>';
       $service = explode(',', $row['service_coord']);
       $content['map_services'][] = array('coords' => array('mapx' => $service['1'], 'mapy' => $service['0']), 'street' => $row['street'], 'phone' => '('.$row['phone_code'].') '.$row['phone']);
       $i++;
      }
      }

return $content;
}

function get_cities_country_shops($query = '', $list = 1) {
  global $config, $db;
$sql = mysqli_query($db, 'SELECT MIN(id), city, country FROM `shops_list` WHERE `country` = '.$query.' and `list` = '.$list.' GROUP BY city ORDER BY city ASC;');
    if (mysqli_num_rows($sql) != false) {
      $id = 1;
      while ($row = mysqli_fetch_array($sql)) {
       if ($id == 1) { $content['options'] .= '<option value="all" data-country="'.$row['country'].'">Все города</option>'; }
       $content['options'] .= '<option value="'.$row['city'].'" data-country="'.$row['country'].'">'.$row['city'].'</option>';
       $id++;
      }
    } else {
    $content['options'] .= '<option value="" data-country="'.$row['country'].'">Магазинов в этой стране еще нет</option>';
    }

# Все по стране:
$query = strip_tags($query);
$sql = mysqli_query($db, 'SELECT * FROM `shops_list` WHERE `country` = '.$query.' and `list` = '.$list.';');
    if (mysqli_num_rows($sql) != false) {
      $i = 0;
      $content['body'] = '      <div class="head">
        <div class="city">Город</div>
        <div class="name">Название центра</div>
        <div class="addr">Адрес</div>
        <div class="phone">Номер телефона</div>
      </div>';
      while ($row = mysqli_fetch_array($sql)) {
       $content['body'] .= '<li>
          <a href="">
            <span class="city">
              '.$row['city'].'
            </span>
            <span class="name">
              '.$row['name'].'
            </span>
            <span class="addr">
              '.$row['street'].'
            </span>
            <span class="phone">
              ('.$row['phone_code'].') '.$row['phone'].'
            </span>
          </a>
        </li>';
       $service = explode(',', $row['service_coord']);
       $content['map_services'][] = array('coords' => array('mapx' => $service['1'], 'mapy' => $service['0']), 'street' => '<b>'.$row['name'].'</b><br>'.$row['street'], 'phone' => $row['phone']);
       $i++;
      }
      }

return $content;
}

function get_cities_country_shops_item($query = '', $list = 1, $item_id) {
  global $config, $db;
$item = content_new_id($item_id);
$names = names_array($item['shops']);
$sql = mysqli_query($db, 'SELECT MIN(id), city, country FROM `shops_list` WHERE `country` = '.$query.' and `list` = '.$list.' and `name` IN ('.$names.') GROUP BY city ORDER BY city ASC;');
    if (mysqli_num_rows($sql) != false) {
      $id = 1;
      while ($row = mysqli_fetch_array($sql)) {
       if ($id == 1) { $content['options'] .= '<option value="all" data-country="'.$row['country'].'">Все города</option>'; }
       $content['options'] .= '<option value="'.$row['city'].'" data-country="'.$row['country'].'">'.$row['city'].'</option>';
       $id++;
      }
    } else {
    $content['options'] .= '<option value="" data-country="'.$row['country'].'">Магазинов в этой стране еще нет</option>';
    }

# Все по стране:
$query = strip_tags($query);
$sql = mysqli_query($db, 'SELECT * FROM `shops_list` WHERE `country` = '.$query.' and `list` = '.$list.' and `name` IN ('.$names.');');
    if (mysqli_num_rows($sql) != false) {
      $i = 0;
      $content['body'] = '      <div class="head">
        <div class="city">Город</div>
        <div class="name">Название центра</div>
        <div class="addr">Адрес</div>
        <div class="phone">Номер телефона</div>
      </div>';
      while ($row = mysqli_fetch_array($sql)) {
       $content['body'] .= '<li>
          <a href="">
            <span class="city">
              '.$row['city'].'
            </span>
            <span class="name">
              '.$row['name'].'
            </span>
            <span class="addr">
              '.$row['street'].'
            </span>
            <span class="phone">
              ('.$row['phone_code'].') '.$row['phone'].'
            </span>
          </a>
        </li>';
       $service = explode(',', $row['service_coord']);
       $content['map_services'][] = array('coords' => array('mapx' => $service['1'], 'mapy' => $service['0']), 'street' => '<b>'.$row['name'].'</b><br>'.$row['street'], 'phone' => $row['phone']);
       $i++;
      }
      }

return $content;
}

function gen_placemarks($country = 1, $list = 1) {
  global $config, $db;
$sql = mysqli_query($db, 'SELECT * FROM `services` WHERE `country` = '.$country.' and `list` = '.$list.';');
$i = 0;
    if (mysqli_num_rows($sql) != false)
      while ($row = mysqli_fetch_array($sql)) {
       $coords = explode(',', $row['service_coord']);
       $content .= 'myGeoObjects['.$i.'] = new ymaps.Placemark(['.$coords['1'].','.$coords['0'].'],{
                                clusterCaption: \''.$row['street'].'\',
                                balloonContentBody: \''.$row['street'].'<br>('.$row['phone_code'].') '.$row['phone'].'\'
                                });';
                                 $i++;
      }
return $content;
}

function gen_placemarks_shops($country = 1, $list = 1) {
  global $config, $db;
$sql = mysqli_query($db, 'SELECT * FROM `shops_list` WHERE `country` = '.$country.' and `list` = '.$list.';');
$i = 0;
    if (mysqli_num_rows($sql) != false)
      while ($row = mysqli_fetch_array($sql)) {
       $coords = explode(',', $row['service_coord']);
       $content .= 'myGeoObjects['.$i.'] = new ymaps.Placemark(['.$coords['1'].','.$coords['0'].'],{
                                clusterCaption: \''.$row['street'].'\',
                                balloonContentBody: \'<b>'.$row['name'].'</b><br>'.$row['street'].'<br>'.$row['phone'].'\'
                                });';
                                 $i++;
      }
return $content;
}

function gen_placemarks_shops_item($country = 1, $list = 1, $names) {
  global $config, $db;
$names = names_array($names);
$sql = mysqli_query($db, 'SELECT * FROM `shops_list` WHERE `country` = '.$country.' and `list` = '.$list.'  and `name` IN ('.$names.');');
$i = 0;
    if (mysqli_num_rows($sql) != false)
      while ($row = mysqli_fetch_array($sql)) {
       $coords = explode(',', $row['service_coord']);
       $content .= 'myGeoObjects['.$i.'] = new ymaps.Placemark(['.$coords['1'].','.$coords['0'].'],{
                                clusterCaption: \''.$row['street'].'\',
                                balloonContentBody: \'<b>'.$row['name'].'</b><br>'.$row['street'].'<br>'.$row['phone'].'\'
                                });';
                                 $i++;
      }
return $content;
}

function search_cities($query, $country, $list = 1) {
  global $config, $db;
$query = strip_tags($query);
$sql = mysqli_query($db, 'SELECT * FROM `services` WHERE `city` = \''.$query.'\' and `country` = '.$country.' and `list` = '.$list.';');
    if (mysqli_num_rows($sql) != false) {
      $i = 0;
      $content['body'] = '      <div class="head">
        <div class="city">Город</div>
        <div class="name">Название центра</div>
        <div class="addr">Адрес</div>
        <div class="phone">Номер телефона</div>
      </div>';
      while ($row = mysqli_fetch_array($sql)) {
       $content['body'] .= '<li>
          <a href="">
            <span class="city">
              '.$row['city'].'
            </span>
            <span class="name">
              '.$row['name'].'
            </span>
            <span class="addr">
              '.$row['street'].'
            </span>
            <span class="phone">
              ('.$row['phone_code'].') '.$row['phone'].'
            </span>
          </a>
        </li>';
       $city = explode(',', $row['city_coord']);
       $service = explode(',', $row['service_coord']);
       $content['map_services'][] = array('coords' => array('mapx' => $service['1'], 'mapy' => $service['0']), 'street' => $row['street'], 'phone' => '('.$row['phone_code'].') '.$row['phone']);
       $content['map_city']['mapx'] = $city['1'];
       $content['map_city']['mapy'] = $city['0'];
       $i++;
      }
      }
return $content;
}

function search_cities_shops($query, $country, $list = 1) {
  global $config, $db;
$query = strip_tags($query);
$sql = mysqli_query($db, 'SELECT * FROM `shops_list` WHERE `city` = \''.$query.'\' and `country` = '.$country.' and `list` = '.$list.';');
    if (mysqli_num_rows($sql) != false) {
      $i = 0;
      $content['body'] = '      <div class="head">
        <div class="city">Город</div>
        <div class="name">Название центра</div>
        <div class="addr">Адрес</div>
        <div class="phone">Номер телефона</div>
      </div>';
      while ($row = mysqli_fetch_array($sql)) {
       $content['body'] .= '<li>
          <a href="">
            <span class="city">
              '.$row['city'].'
            </span>
            <span class="name">
              '.$row['name'].'
            </span>
            <span class="addr">
              '.$row['street'].'
            </span>
            <span class="phone">
              ('.$row['phone_code'].') '.$row['phone'].'
            </span>
          </a>
        </li>';
       $city = explode(',', $row['city_coord']);
       $service = explode(',', $row['service_coord']);
       $content['map_services'][] = array('coords' => array('mapx' => $service['1'], 'mapy' => $service['0']), 'street' => '<b>'.$row['name'].'</b><br>'.$row['street'], 'phone' => $row['phone']);
       $content['map_city']['mapx'] = $city['1'];
       $content['map_city']['mapy'] = $city['0'];
       $i++;
      }
      }
return $content;
}

function search_cities_shops_item($query, $country, $list = 1, $item_id) {
  global $config, $db;
$item = content_new_id($item_id);
$names = names_array($item['shops']);
$query = strip_tags($query);
$sql = mysqli_query($db, 'SELECT * FROM `shops_list` WHERE `city` = \''.$query.'\' and `country` = '.$country.' and `list` = '.$list.' and `name` IN ('.$names.');');
    if (mysqli_num_rows($sql) != false) {
      $i = 0;
      $content['body'] = '      <div class="head">
        <div class="city">Город</div>
        <div class="name">Название центра</div>
        <div class="addr">Адрес</div>
        <div class="phone">Номер телефона</div>
      </div>';
      while ($row = mysqli_fetch_array($sql)) {
       $content['body'] .= '<li>
          <a href="">
            <span class="city">
              '.$row['city'].'
            </span>
            <span class="name">
              '.$row['name'].'
            </span>
            <span class="addr">
              '.$row['street'].'
            </span>
            <span class="phone">
              ('.$row['phone_code'].') '.$row['phone'].'
            </span>
          </a>
        </li>';
       $city = explode(',', $row['city_coord']);
       $service = explode(',', $row['service_coord']);
       $content['map_services'][] = array('coords' => array('mapx' => $service['1'], 'mapy' => $service['0']), 'street' => '<b>'.$row['name'].'</b><br>'.$row['street'], 'phone' => $row['phone']);
       $content['map_city']['mapx'] = $city['1'];
       $content['map_city']['mapy'] = $city['0'];
       $i++;
      }
      }
return $content;
}

function search_cities_all($list = 1) {
  global $config, $db;
$sql = mysqli_query($db, 'SELECT * FROM `services` WHERE `list` = '.$list.';');
    if (mysqli_num_rows($sql) != false) {
      $content['body'] = '      <div class="head">
        <div class="city">Город</div>
        <div class="name">Название центра</div>
        <div class="addr">Адрес</div>
        <div class="phone">Номер телефона</div>
      </div>';
      while ($row = mysqli_fetch_array($sql)) {
       $content .= '<li>
          <a href="">
            <span class="city">
              '.$row['city'].'
            </span>
            <span class="name">
              '.$row['name'].'
            </span>
            <span class="addr">
              '.$row['street'].'
            </span>
            <span class="phone">
              ('.$row['phone_code'].') '.$row['phone'].'
            </span>
          </a>
        </li>';
      }
      }
return $content;
}

function search_techies($query = '') {
  global $config, $db;
if ($query != '') {
$query = trim(strip_tags($query));
# Инструкции
$sql = mysqli_query($db, 'SELECT * FROM `manuals` WHERE `name` LIKE \'%'.mysqli_real_escape_string($db, $query).'%\' or `desc` LIKE \'%'.mysqli_real_escape_string($db, $query).'%\' ORDER by `id` DESC LIMIT 50;');
    if (mysqli_num_rows($sql) != false)
      while ($row = mysqli_fetch_array($sql)) {
            $item = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `items` WHERE `id` = '.$row['item_id'].' LIMIT 1;'));
       $content .= '<tr>
                        	<td>'.$row['date'].'</td>
                            <td>
                            	<p><a href="'.$config['url'].$item['sef'].'/">'.$item['name'].'</a></p>
                                <p>'.$row['desc'].'</p>
                          	</td>
                            <td><a href="'.$row['file'].'"><img src="'.$config['url'].'temp/img_098.jpg" alt="img"/></a><br><a href="'.$row['file'].'">'.str_replace('http://harper.ru/files/manuals/', '', $row['file']).'</a></td>
                        </tr>';
      }
# Прошивки
$sql = mysqli_query($db, 'SELECT * FROM `firmware` WHERE `name` LIKE \'%'.mysqli_real_escape_string($db, $query).'%\' or `desc` LIKE \'%'.mysqli_real_escape_string($db, $query).'%\' ORDER by `id` DESC LIMIT 50;');
    if (mysqli_num_rows($sql) != false)
      while ($row = mysqli_fetch_array($sql)) {
            $item = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `items` WHERE `id` = '.$row['item_id'].' LIMIT 1;'));
       $content .= '<tr>
                        	<td>'.$row['date'].'</td>
                            <td>
                            	<p><a href="'.$config['url'].$item['sef'].'/">'.$item['name'].'</a></p>
                                <p>'.$row['desc'].'</p>
                          	</td>
                            <td><a href="'.$row['file'].'"><img src="'.$config['url'].'temp/img_099.jpg" alt="img"/></a><br><a href="'.$row['file'].'">'.str_replace('http://harper.ru/files/firmware/', '', $row['file']).'</a></td>
                        </tr>';
      }
# Прошивки
$sql = mysqli_query($db, 'SELECT * FROM `presents` WHERE `name` LIKE \'%'.mysqli_real_escape_string($db, $query).'%\' or `desc` LIKE \'%'.mysqli_real_escape_string($db, $query).'%\' ORDER by `id` DESC LIMIT 50;');
    if (mysqli_num_rows($sql) != false)
      while ($row = mysqli_fetch_array($sql)) {
            $item = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `items` WHERE `id` = '.$row['item_id'].' LIMIT 1;'));
       $content .= '<tr>
                        	<td>'.$row['date'].'</td>
                            <td>
                            	<p><a href="'.$config['url'].$item['sef'].'/">'.$item['name'].'</a></p>
                                <p>'.$row['desc'].'</p>
                          	</td>
                            <td><a href="'.$row['file'].'"><img src="'.$config['url'].'temp/img_099.jpg" alt="img"/></a><br><a href="'.$row['file'].'">'.str_replace('http://harper.ru/files/presents/', '', $row['file']).'</a></td>
                        </tr>';
      }
} else {
$content = '<br>Поиск не дал результатов.';
}
if (!$content) {
 $content = '<br>Поиск не дал результатов.';
}
    return $content;
}

function search_supported() {
  global $config, $db;
$cat_id = intval($_GET['cat_id']);
$query = strip_tags($_GET['query']);
$supported = (intval($_GET['supported']) == 1) ? 'and `service` = 1' : '';

if ($cat_id && !$query) {
$sql_query = 'WHERE `cat_id` = \''.mysqli_real_escape_string($db, $cat_id).'\' '.$supported;
} else if ($cat_id && $query) {
$sql_query = 'WHERE `cat_id` = \''.mysqli_real_escape_string($db, $cat_id).'\' and `name` LIKE \'%'.mysqli_real_escape_string($db, $query).'%\' '.$supported;
} else if (!$cat_id && $query) {
$sql_query = 'WHERE `name` LIKE \'%'.mysqli_real_escape_string($db, $query).'%\' '.$supported;
}
//echo $sql_query;
# Инструкции
$sql = mysqli_query($db, 'SELECT * FROM `items` '.$sql_query.' ORDER by `id` DESC LIMIT 200;');
    if (mysqli_num_rows($sql) != false) {
      $content = '<div class="top">
      <div class="type">Тип товара</div>
      <div class="name">Название модели</div>
      <div class="serv">Обслуживание</div>
    </div>
    <ul>';
      while ($row = mysqli_fetch_array($sql)) {
       $cat_info = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `categories` WHERE `id` = \''.$row['cat_id'].'\' LIMIT 1;'));
       $support = ($row['service'] == 1) ? '<span class="serv"><span>Да</span></span>' : '<span class="serv no"><span>Нет</span></span>';
       $content .= '<li>
        <div>
          <a style="cursor:default;" onclick="return false">
            <span class="type">'.$cat_info['single_name'].'</span>
            <span class="name">'.$row['name'].'</span>
            '.$support.'
          </a>
        </div>
      </li>';
      }
      $content .= '</div> ';
} else {
$content = '<br>Поиск не дал результатов';
}

return $content;
}

function supported_list_first () {
  global $config, $db;

# Инструкции
$sql = mysqli_query($db, 'SELECT * FROM `items` '.$sql_query.' ORDER by `id` DESC LIMIT 500;');
    if (mysqli_num_rows($sql) != false) {
      $content = '<div class="top">
      <div class="type">Тип товара</div>
      <div class="name">Название модели</div>
      <div class="serv">Обслуживание</div>
    </div>
    <ul>';
      while ($row = mysqli_fetch_array($sql)) {
       $cat_info = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `categories` WHERE `id` = \''.$row['cat_id'].'\' LIMIT 1;'));
       $support = ($row['service'] == 1) ? '<span class="serv"><span>Да</span></span>' : '<span class="serv no"><span>Нет</span></span>';
       $content .= '<li>
        <div>
        <a style="cursor:default;" onclick="return false">
            <span class="type">'.$cat_info['single_name'].'</span>
            <span class="name">'.$row['name'].'</span>
            '.$support.'
            </a>
        </div>
      </li>';
      }
      $content .= '</div> ';
}

return $content;
}

function search_service($query = '') {
  global $config, $db;
if ($query != '') {
$query = trim(strip_tags($query));
# Инструкции
$sql = mysqli_query($db, 'SELECT * FROM `service` WHERE `code` = \''.mysqli_real_escape_string($db, $query).'\' ORDER by `id` DESC LIMIT 1;');
    if (mysqli_num_rows($sql) != false)
      while ($row = mysqli_fetch_array($sql)) {
       $content .= '<div class="product">

      <div class="img">
        <img src="/files_harper/ss-product-1.jpg" alt=""/>
      </div>

      <div class="entry">

        <div class="cat">
          Портативная колонка
        </div>

        <div class="title">
          '.$row['code'].'
        </div>

        <div class="text">
          Bluetooch, Защита от брызг и дождя IPX6
        </div>

      </div>

    </div>

    <div class="status">
      <div class="inner">

        <div class="title">
          '.$row['status'].'
        </div>

        <div class="text">
          Ориентеровочная готовность '.$row['date'].'
        </div>

      </div>
    </div>';
      }
} else {
$content = '<br>Обращение с таким кодом не зарегистрировано.';
}
if (!$content) {
 $content = '<br>Обращение с таким кодом не зарегистрировано.';
}
    return $content;
}

function form_answer() {
  global $config;

if ($_COOKIE['send'] == 'ok') {
unset($_COOKIE['send']);
echo '<div class="window">
      	Ваше сообщение упешно отправлено. <br> Пожалуйста, ожидайте ответа в течении 24 часов.
    </div>';
}

if ($_COOKIE['qa'] == 'ok') {
unset($_COOKIE['qa']);
echo '<div class="window">
      	Ваш вопрос успешно отправлен. <br> Пожалуйста, ожидайте ответа в течении 24 часов.
    </div>';
}

if ($_COOKIE['review'] == 'ok') {
unset($_COOKIE['review']);
echo '<div class="window">
      	Ваш отзыв успешно отправлен. <br> Пожалуйста, ожидайте проверки в течении 24 часов.
    </div>';
}

}

function drow_slider($imgs) {

$images = json_decode($imgs);

$slider .= '<div class="photo pull-left">
            	<div id="slider" class="flexslider">
               		<ul class="slides">';

foreach ($images as $img) {
$slider .= '<li><a class="fancybox" rel="gallery" href="'.$img.'"><img src="'.$img.'" /></a></li>';
}

$slider .=                 	'</ul>
                </div>
                <div id="carousel" class="flexslider">
                  	<ul class="slides">';


foreach ($images as $img) {
$slider .= '<li><a><img src="'.resizer($img, 120, 90, 1).'" /></a></li>';
}

$slider .=                  	'</ul>
                </div>
                <p>HD</p>
            </div>';

return $slider;
}

function drow_slider_new($imgs) {

$images = json_decode($imgs);
foreach ($images as $img) {
$slider .= '<li data-big="'.resizer($img, 500, 500, 2).'" data-source="'.$img.'"><span><img src="'.resizer($img, '', 127, 58).'" alt=""/></span> ';
}

return $slider;
}

function ads($id) {
  global $config, $db;
    $ads = mysqli_fetch_array(mysqli_query($db, 'SELECT `code` FROM `ads` WHERE `id` = '.$id));
    return $ads['code'];
}

function get_techie_by_id($item_id, $techie_id) {
 // echo $item_id.'|'.$techie_id.'<br>';
  global $config, $db;
$techie_info = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `techies` WHERE `item_id` = \''.mysqli_real_escape_string($db, $item_id).'\' and `techie_id` = \''.mysqli_real_escape_string($db, $techie_id).'\';'));
return $techie_info['value'];
}

function compare() {
  global $config, $db;

$cat_sef = strip_tags(str_replace('sravnit-', '', $_GET['query']));
$cat_info = @mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `categories` WHERE `sef` = \''.mysqli_real_escape_string($db, $cat_sef ).'\';'));

if ($cat_info) {

  # Получаем характеристики:
  $cat_json = json_decode($cat_info['techies']);

  if ($_SESSION['compare']) {
    if ($_SESSION['compare'][$cat_info['id']]) {
      foreach ($_SESSION['compare'][$cat_info['id']] as $item) {
        $i++;
        $item = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `items` WHERE `id` = '.$item.' LIMIT 1;'));
        $imgs = json_decode($item['imgs']);
        $items['body'] .= '

        <li data-id="'.$i.'">
          <div class="img">
            <a href="'.$config['url'].$item['sef'].'/"><img src="'.resizer($imgs['0'], 125, 161, 2).'" alt="'.$item['name'].'"/></a>
          </div>
          <div class="del">
            <a href="" data-itemid="'.$item['id'].'" data-catid="'.$cat_info['id'].'">Убрать<span> из сравнения</span></a>
          </div>
          <div class="char">
            <div class="item title"><a href="'.$config['url'].$item['sef'].'/">'.$item['name'].'</a></div>';

        foreach ($cat_json as $techie_id => $techie_value)  {
            $items['body'] .= '<div class="item">'.get_techie_by_id($item['id'], $techie_id).'</div>';
        }

        $items['body'] .= '</div>
        </li>';




      }

    $items['techies'] .= '<li><div class="item" data-id="0">Название модели</div></li>';
    foreach ($cat_json as $techie_id => $techie_value)  {
    $items['techies'] .= '<li><div class="item" data-id="0">'.$techie_value.'</div></li>';
    }

    } else {

    $items['error'] .= 'Товар для сравнения нет!';

    }

    $type_info = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `types` WHERE `id` = '.$cat_info['type'].';'));
    $items['cat_info'] = $cat_info;
    $items['type_info'] = $type_info;

  }

}



return $items;
}


function techies($id) {
  global $config, $db;

$item_info = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `items` WHERE `id` = \''.mysqli_real_escape_string($db, $id).'\' LIMIT 1;'));
$cat_info = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `categories` WHERE `id` = \''.mysqli_real_escape_string($db, $item_info['cat_id']).'\';'));
$techie_info = mysqli_query($db, 'SELECT * FROM `techies` WHERE `item_id` = \''.mysqli_real_escape_string($db, $id).'\';');
$techie_inner = mysqli_query($db, 'SELECT * FROM `categories_inner` WHERE `cat_id` = \''.mysqli_real_escape_string($db, $item_info['cat_id']).'\';');
$techie_count = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `categories_inner` WHERE `cat_id` = \''.mysqli_real_escape_string($db, $item_info['cat_id']).'\';'));
$json_var = json_decode($cat_info['techies'], true);

$id_ceil = 1;
$id_global = 1;
$ceil = round($techie_count['COUNT(*)']/3);

while ($row_terms = mysqli_fetch_array($techie_inner)) {

if ($id_ceil == 1) { $techies_ready .= '<div class="box">'; }

$count = mysqli_query($db, 'SELECT * FROM `categories_techies` WHERE `term_id` = \''.mysqli_real_escape_string($db, $row_terms['id']).'\';');
  while ($row_count = mysqli_fetch_array($count)) {
    $get_count = get_techie_by_id($id, $row_count['techie_id']);
    if ($get_count != '-' && $get_count != '') {
      $ok = 1;
    }
  }

if ($ok > 0) {

    $techies_ready .= '<div class="item">
              <div class="title">
                '.$row_terms['name'].'
              </div>
              <ul>';
    //echo $row_terms['id'].'|';
    $techie_terms = mysqli_query($db, 'SELECT * FROM `categories_techies` WHERE `term_id` = \''.mysqli_real_escape_string($db, $row_terms['id']).'\';');
      while ($row_inner = mysqli_fetch_array($techie_terms)) {

        $get_techie = get_techie_by_id($item_info['id'], $row_inner['techie_id']);
        if ($get_techie != '-' && $get_techie != '' && $get_techie) {

        $techies_ready .= '
                <li>
                  <div class="level"><span>'.$json_var[$row_inner['techie_id']].'</span></div>
                  <div class="value">'.$get_techie.'</div>
                </li>';
                unset($get_techie);
        }

      }

    $techies_ready .= '</ul>
            </div>';
}

if ($id_ceil == $ceil) {
$techies_ready .= '</div>'; $id_ceil = 0;
} else if ($id_global == $techie_count['COUNT(*)']) {
$techies_ready .= '</div>';
}


$id_ceil++;
$id_global++;
unset($ok);

}

//$techies = json_decode($cat_info['techies'], true);

/*if (mysqli_num_rows($techie_info) != false) {
while ($row = mysqli_fetch_array($techie_info)) {

        $techies_name = $techies[$row['techie_id']];
        if ($row['value'] != '-' && $row['value'] != '' && $techies_name) {
        $techies_ready .= '<tr>
                        	<td><span>'.$techies_name.'</span></td>
                            <td>'.$row['value'].'</td>
                      	</tr>';
         }
}
} */

return $techies_ready;
}

function qa($id) {
  global $config, $db;
$techie_info = mysqli_query($db, 'SELECT * FROM `qa` WHERE `item_id` = \''.mysqli_real_escape_string($db, $id).'\' and `active` = 1;');
if (mysqli_num_rows($techie_info) != false) {
while ($row = mysqli_fetch_array($techie_info)) {
        $techies_ready .= '<li>
            <div class="author">
              <div class="name">
                '.$row['name'].'
              </div>
              <div class="date">
                '.$row['date'].'
              </div>
              <!--<div class="city">
                Москва
              </div>-->
            </div>
            <div class="text">
              '.$row['question'].'
            </div>
            <div class="answer">
              <div class="head">
                HARPER
              </div>
              <div class="text">
                '.$row['answer'].'
              </div>
            </div>
          </li>';
}
}

return $techies_ready;
}

function text_reviews($id) {
  global $config, $db;
$techie_info = mysqli_query($db, 'SELECT * FROM `item_reviews` WHERE `item_id` = \''.mysqli_real_escape_string($db, $id).'\' ;');
if (mysqli_num_rows($techie_info) != false) {
$id = 1;
while ($row = mysqli_fetch_array($techie_info)) {
        $techies_ready .= '
            <li>
            <a target="_blank" href="'.$row['link'].'">
              <span class="num">'.$id.'</span>
              <span class="title">
                '.$row['name'].'
              </span>
              <span class="link">
               '.$row['link'].'
              </span>
            </a>
          </li>
        ';
          $id++;
}
}

return $techies_ready;
}

function feedback_info($id) {
  global $config, $db;
$id = strip_tags($id);
$feedback_info = @mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `feedback_admin` WHERE `md5` = \''.mysqli_real_escape_string($db, $id).'\' ;'));
return $feedback_info;
}

function feedback_add($id) {
  global $config, $db;
mysqli_query($db, 'INSERT INTO `feedback_messages` (
`date`,
`user_type`,
`message`,
`feedback_id`,
`read`
) VALUES (
\''.mysqli_real_escape_string($db, date("Y-m-d H:i:s")).'\',
\''.mysqli_real_escape_string($db, '1').'\',
\''.mysqli_real_escape_string($db, strip_tags($_POST['message'])).'\',
\''.mysqli_real_escape_string($db, $id).'\',
\'0\'
);') or mysqli_error($db);
}

function feedbackvote() {
  global $config, $db;

$id = intval($_GET['id']);

if ($_GET['type'] == 'minus') {
    $sql = 'vote-1';
} else if ($_GET['type'] == 'plus') {
    $sql = 'vote+1';
}

mysqli_query($db, 'UPDATE `feedback_admin` SET `vote` = '.$sql.' WHERE `id` = \''.mysqli_real_escape_string($db, $id).'\' ;') or mysqli_error($db);

}

function feedback_close($id) {
  global $config, $db;
mysqli_query($db, 'UPDATE `feedback_admin` SET `status` = \'Вопрос закрыт\' WHERE `id` = \''.mysqli_real_escape_string($db, $id).'\' ;') or mysqli_error($db);
header("Location: ".$_SERVER['HTTP_REFERER']);
exit;
}

function update_notify($userID, $id, $markRead = false) {
  global $db;
  if($id < 0){
    mysqli_query($db, 'UPDATE `notification` SET `read` = 1 WHERE `user_id` = \''.mysqli_real_escape_string($db, $userID).'\';') or mysqli_error($db);
  }else{
    $r = (!$markRead) ? 0 : 1;
    mysqli_query($db, 'UPDATE `notification` SET `read` = '.$r.' WHERE `id` = \''.mysqli_real_escape_string($db, $id).'\';') or mysqli_error($db);
  }
}

function update_notify_app($user, $id) {
  global $db;
mysqli_query($db, 'UPDATE `notification` SET `read` = 0 WHERE `id` = \''.mysqli_real_escape_string($db, $id).'\' and `user_id` = \''.mysqli_real_escape_string($db, $user).'\' ;') or mysqli_error($db);
}

function feedback_reopen($id) {
  global $db;
mysqli_query($db, 'UPDATE `feedback_admin` SET `status` = \'Вопрос открыт\' WHERE `id` = \''.mysqli_real_escape_string($db, $id).'\' ;') or mysqli_error($db);
header("Location: ".$_SERVER['HTTP_REFERER']);
exit;
}

function feedback_messages($id) {
  global $config, $db;
$id = strip_tags($id);
$feedback_info = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `feedback_admin` WHERE `md5` = \''.mysqli_real_escape_string($db, $id).'\' ;'));

if ($feedback_info['id']) {
  $feedback = mysqli_query($db, 'SELECT * FROM `feedback_messages` WHERE `feedback_id` = \''.mysqli_real_escape_string($db, $feedback_info['id']).'\';');
  if (mysqli_num_rows($feedback) != false) {
            $messages .= '<ul><li>';
    while ($row = mysqli_fetch_array($feedback)) {

            #юзер:
            if ($row['user_type'] == 1) {
            $messages .= '<div class="author">
                <div class="date">
                  '.$row['date'].'
                </div>
              </div>
              <div class="text">
                '.nl2br($row['message']).'
              </div>';
            }
            #суппорт:
            if ($row['user_type'] == 2) {
             $messages .= '<div class="answer">
            <div class="head">
              HARPER
            </div>
            <div class="text">
              '.nl2br($row['message']).'
            </div>
          </div>
          <div class="vote">
            <span>Оцените ответ службы поддержки</span>
            <a href="" data-feedbackid="'.$row['feedback_id'].'" data-type="plus" class="up"></a>
            <a href="" data-feedbackid="'.$row['feedback_id'].'" data-type="minus" class="down"></a>
          </div>';
            }

    }
            $messages .= '</li></ul>';
  }

}

return $messages;
}

function check_last_messages($id) {
  global $config, $db;
$id = strip_tags($id);
$feedback_info = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `feedback_admin` WHERE `md5` = \''.mysqli_real_escape_string($db, $id).'\' ;'));

if ($feedback_info['id']) {
$feedback_date = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `feedback_messages` WHERE `feedback_id` = \''.mysqli_real_escape_string($db, $feedback_info['id']).'\' ORDER by `id` DESC LIMIT 1;'));
$date = strtotime($feedback_date['date']);
$date = strtotime("+7 day", $date);
if ($date > strtotime(date("Y-m-d H:i:s"))) {
return true;
} else {
return false;
}

}

return $messages;
}

function faq($id) {
  global $config, $db;
$techie_info = mysqli_query($db, 'SELECT * FROM `faq`;');
if (mysqli_num_rows($techie_info) != false) {
while ($row = mysqli_fetch_array($techie_info)) {
        $techies_ready .= '<li>
        <div class="author">
          <div class="name">
            Гость
          </div>
        </div>
        <div class="text">
          '.$row['question'].'
        </div>
        <div class="answer">
          <div class="head">
            HARPER
          </div>
          <div class="text">
            '.$row['answer'].'
          </div>
        </div>
      </li>';
}
}

return $techies_ready;
}

function articles($sef, $page = 0) {
  global $config, $db;
$sef = trim(strip_tags($sef));
    require_once($_SERVER['DOCUMENT_ROOT'].'/includes/paginator.php');
    # Считаем количество записей:
    $count = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `articles`;'));
    $countElements = $count['COUNT(*)'];
    if ($countElements == 0) {
        $categories['body'] = '<br><br>Категория пуста!';
        $categories['name'] = $cat_info['name'];
      $categories['desc'] = $cat_info['desc'];
            $categories['name'] = $cat_info['name'];
      $categories['title'] = ($cat_info['title']) ? $cat_info['title'] : $cat_info['name'];
      //die('Wrong url, retard.');
    } else {
      # Вызываем класс:

      $params = array('pageSize' => 200, 'maxPage' => '20000', 'urlPrefix'   => $config['url'].$cat_info['sef'].'/', 'urlPostfix'  => '/', 'css' => '', 'title' => '', 'litag' => 'span', 'tag' => '', 'arr' => 'false', 'line' => '5', 'solid' => 'true');
      $pager = new goPaginator($countElements, $params);
          $sql = mysqli_query($db, 'SELECT * FROM `articles` order by `id` DESC LIMIT '.$pager->getSqlLimits());
    if (mysqli_num_rows($sql) != false) {
      $counter = 1;
      while ($row = mysqli_fetch_array($sql)) {
        preg_match_all('/<img [^>]*src=["|\']([^"|\']+)/i', $row['text'], $img_matches);
        //$src = ($img_matches['1']['0']) ? $config['url'].'trumb/'.str_replace(array('http://beremennost-po-nedelyam.com/wp-content/', 'http://beremennost-po-nedelyam.com/images/'), array('wp-content/', 'images/'), $img_matches['1']['0']) : $config['url'].'images/default.png';
        $date = explode(' ', $row['date']);
        $categories['body'] .= '<li>';
        $categories['body'] .= '<img src="'.resizer($img_matches['1']['0']).'" width="200" height="200" alt="img" class="pull-left"/>
                    <p class="title"><strong><a href="'.$config['url'].'stati/'.$row['sef'].'">'.$row['name'].'</a></strong></p>
                    <p>'.cutString(strip_tags($row['text']), 250).'</p>
                    <p class="date">Опубликовано: '.$date['0'].'</p>
                    <div class="clear"></div>';
        $categories['body'] .= '</li>';

        $counter++;
      }

      if (($pager != ''))
      $categories['pager'] = '<br><h3 style="border-bottom: 0px; font-size: 19px;">Навигация:</h3>'.$pager.'';
    }

    }

    return stripslashes_array($categories);
}


function reviews($id) {
  global $config, $db;
$techie_info = mysqli_query($db, 'SELECT * FROM `reviews` WHERE `item_id` = \''.mysqli_real_escape_string($db, $id).'\' and `active` = 1;');
if (mysqli_num_rows($techie_info) != false) {
while ($row = mysqli_fetch_array($techie_info)) {

        $techies_ready .= '<li>
            <div class="author">
              <div class="name">
                '.$row['name'].'
              </div>
              <div class="date">
                '.$row['date'].'
              </div>
              <!--<div class="city">
                Москва
              </div>-->
            </div>
            <div class="rating">
              <span class="tx">Оценка</span>
              <span class="in">
                <span style="width: '.($row['mark']*16).'px;"></span>
              </span>
            </div>
            <div class="box">
              <div class="text">
                '.$row['review'].'
              </div>
              <div class="vote">
                <span>Согласны?</span> <a href="" class="yes">Да <span>1</span></a> <a href="" class="no">Нет <span>0</span></a>
              </div>
            </div>
          </li>';
}
} else {
$techies_ready = '<li>Отзывов еще нет</li>';
}

return $techies_ready;
}

function firmware($id) {
  global $config, $db;
$item_info = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `items` WHERE `id` = \''.mysqli_real_escape_string($db, $id).'\' LIMIT 1;'));
$firmware_info = mysqli_query($db, 'SELECT * FROM `firmware` WHERE `item_id` = \''.mysqli_real_escape_string($db, $id).'\';');

if (mysqli_num_rows($firmware_info) != false) {
while ($row = mysqli_fetch_array($firmware_info)) {

        $firmware_ready .= '<li>
          <div class="date">
            '.$row['name'].'
          </div>
          <div class="text">
            '.$row['desc'].'
          </div>
          <div class="load">
            <a href="'.$row['file'].'"><span>'.str_replace('http://harper.ru/files/firmware/', '', $row['file']).'</span></a>
          </div>
        </li>';
}
} else {
$firmware_ready = '';
}

return $firmware_ready;
}

function manual($id) {
  global $config, $db;
$item_info = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `items` WHERE `id` = \''.mysqli_real_escape_string($db, $id).'\' LIMIT 1;'));
$firmware_info = mysqli_query($db, 'SELECT * FROM `manuals` WHERE `item_id` = \''.mysqli_real_escape_string($db, $id).'\';');

if (mysqli_num_rows($firmware_info) != false) {
while ($row = mysqli_fetch_array($firmware_info)) {

        $firmware_ready .= '<tr>
                            <td>
                            	<!--<p>'.$row['name'].'</p>-->
                                <p>'.$row['desc'].'</p>
                          	</td>
                            <td><a href="'.$row['file'].'"><img src="'.$config['url'].'temp/img_098.jpg" alt="img"/></a><br><a href="'.$row['file'].'">'.str_replace('http://harper.ru/files/manuals/', '', $row['file']).'</a></td>
                        </tr>';
}
}

return $firmware_ready;
}

function manual_link($id) {
  global $config, $db;
$item_info = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `items` WHERE `id` = \''.mysqli_real_escape_string($db, $id).'\' LIMIT 1;'));
$firmware_info = mysqli_query($db, 'SELECT * FROM `manuals` WHERE `item_id` = \''.mysqli_real_escape_string($db, $id).'\';');

if (mysqli_num_rows($firmware_info) != false) {
while ($row = mysqli_fetch_array($firmware_info)) {
        $firmware_ready = $row['file'];
}
}

return $firmware_ready;
}

function options($id) {
  global $config, $db;
$item_info = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `items` WHERE `id` = \''.mysqli_real_escape_string($db, $id).'\' LIMIT 1;'));
if ($item_info) {
$options = array_filter(explode("\n", $item_info['options']));
foreach ($options as $option) {
  if ($option != '') {
$ready .= '<li>'.$option.'</li>';
}
}


}
return $ready;
}

function index_last($limit = 3) {
  global $config, $db;
   $sql = mysqli_query($db, 'SELECT * FROM `items` WHERE `active` = 1 ORDER by `id` DESC LIMIT '.$limit);
    $number = mysqli_num_rows($sql);
    if ($number != false) {
             $counter = 1;
         while ($row = mysqli_fetch_array($sql)) {
                $imgs = json_decode($row['imgs']);

                //if ($counter == 1) { $categories['body'] .= '<tr>'; }
                //$new = ($row['new'] == 1) ? 'class="new"' : '';
                //$old = ($row['old'] == 1) ? 'class="old"' : '';
                $specs = ($row['old'] == 1) ? 'Данная модель снята с производства' : $row['specs'];
                $color = ($row['colors']) ? $row['colors'] : '';
                $categories['body'] .= '
                <li>
                        <div class="img">
                            <a href="'.$config['url'].$row['sef'].'/"><img src="'.$imgs['0'].'" alt="img"/></a>
                        </div>
                        <p>'.$cat_info['single_name'].'</p>
                        <p class="name"><a href="'.$config['url'].$row['sef'].'/">'.$row['name'].'</a></p>
                        <p class="charact">'.$specs.'</p>
						<p class="color">'.$color.'</p>
                    </li>
                ';
                //if ($counter == 3) { $categories['body'] .= '</tr>'; $counter = 0;  }
                //if ($number == $ic) { $categories['body'] .= '</tr>';}
                $counter++;
                $ic++;
            }
         }
return $categories['body'];
}

function items_index_slider($limit = 10) {
  global $config, $db;
   $sql = mysqli_query($db, 'SELECT * FROM `items` WHERE `active` = 1 ORDER by `id` DESC LIMIT '.$limit);
    $number = mysqli_num_rows($sql);
         while ($row = mysqli_fetch_array($sql)) {
                $cat_info = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `categories` WHERE `id` = \''.$row['cat_id'].'\' LIMIT 1;'));
                $imgs = json_decode($row['imgs']);
                $color = ($row['colors']) ? $row['colors'] : '';
                $content .= '<li>
          <div class="img">
            <span><img src="'.resizer($imgs['0'], '', 240, 2).'" alt=""/></span>
          </div>
          <div class="entry">
            <div class="cat">
              '.$cat_info['single_name'].'
            </div>
            <div class="title">
              '.$row['name'].'
            </div>
            <div class="text">
             '.$color.'
            </div>
          </div>
          <a href="'.$config['url'].$row['sef'].'/"></a>
        </li>';
                $counter++;
            }
return $content;
}

function items_404($limit = 5) {
  global $config, $db;
   $sql = mysqli_query($db, 'SELECT * FROM `items` WHERE `active` = 1 ORDER by `id` DESC LIMIT '.$limit);
    $number = mysqli_num_rows($sql);
         while ($row = mysqli_fetch_array($sql)) {
                $cat_info = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `categories` WHERE `id` = \''.$row['cat_id'].'\' LIMIT 1;'));
                $imgs = json_decode($row['imgs']);
                $color = ($row['colors']) ? $row['colors'] : '';
                $content .= '<li>
          <div class="img">
            <span><img src="'.resizer($imgs['0'], '', 240, 2).'" alt=""/></span>
          </div>
          <div class="entry">
            <div class="cat">
              '.$cat_info['single_name'].'
            </div>
            <div class="title">
              '.$row['name'].'
            </div>
            <div class="text">
             '.$color.'
            </div>
          </div>
          <a href="'.$config['url'].$row['sef'].'/"></a>
        </li>';
                $counter++;
            }
return $content;
}

function category($sef, $page = 0) {
  global $config, $db;
unset($_SESSION['techies']['categories']);
$sef = trim(strip_tags($sef));
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/pagination.php');
# Считаем количество записей:
$cat_info = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `categories` WHERE `sef` = \''.$sef.'\';'));
$type_info = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `types` WHERE `id` = '.$cat_info['type'].';'));
$count = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `items` WHERE `cat_id` = '.$cat_info['id'].' and `active` = 1;'));
$countElements = $count['COUNT(*)'];
if ($countElements == 0) {
    $categories['body'] = '<br><br>Категория пуста!';
    $categories['name'] = $cat_info['name'];
    $categories['type_name'] = $type_info['name'];
    $categories['desc'] =  $cat_info['desc'];
    $categories['title'] =  ($_GET['page'] > 0) ? $categories['title'].' - '.$pager->getCurrentPage().' страница' : $categories['title'];
} else {
    # Вызываем класс:
    $params = array('pageSize' => 50, 'maxPage' => '20000', 'urlPrefix'   => $config['url'].$cat_info['sef'].'/', 'urlPostfix'  => '/', 'css' => '', 'title' => '', 'litag' => 'span', 'tag' => '', 'arr' => 'false', 'line' => '5', 'solid' => 'true');
    $pager = new goPaginator($countElements, $params);

    $sql = mysqli_query($db, 'SELECT * FROM `items` WHERE `cat_id` = '.$cat_info['id'].' and `active` = 1 and `old` != 1 ORDER by `id` DESC LIMIT '.$pager->getSqlLimits());
    $number = mysqli_num_rows($sql);
    if ($number != false) {
         while ($row = mysqli_fetch_array($sql)) {
           $now = date('d.m.Y');
       $cat_info = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `categories` WHERE `id` = \''.mysqli_real_escape_string($db, $row['cat_id']).'\';'));
              $imgs = json_decode($row['imgs']);

              if ($row['icons'] != '') {
              $icons = json_decode($row['icons'], true);
              $id_icon = 1;
              foreach ($icons as $icon => $img) {

              $cat_icons = json_decode($cat_info['icons'], true);
              foreach ($cat_icons as $icon_id['0'] => $icon) {
                  if (!is_array($icon['0'])) {
                      if ($icon['0'] == $img) {


                      }
                  } else {
                  foreach ($icon['0'] as $img2 => $name2) {
                      if ($img2 == $img) {

                      $name = $name2;

                      }
                  }
                  }
              }

              //print_r($cat_icons);
              $content['icons_draw'] .= '<div class="item item_shar"><i><img width="34px" src="'.$img.'" alt=""/><span>'.$name.'</span></i></div>';
              if ($id_icon == 6) {break;}
                    $id_icon++;
              }
              }

                $new = ($row['new'] == 1 && strtotime($row['active_date']) > strtotime($now)) ? '<div class="new"></div>' : '';
                $old = ($row['old'] == 1) ? 'class="old"' : '';
                $specs = ($row['old'] == 1) ? 'Данная модель снята с производства' : $row['specs'];
                $color = ($row['colors']) ? $row['colors'] : '';

                $compare = (in_array($row['id'], $_SESSION['compare'][$row['cat_id']])) ? '<a href="">В сравнении</a>' : '<a href="">Сравнить</a>';

                $categories['body'] .= '<li class="product-item">
          <div class="img">
            <a href="'.$config['url'].$row['sef'].'/"><img width="253" height="183" src="'.resizer($imgs['0'], 183, 253, 2).'" alt=""/></a>
          </div>
          <div class="entry">
            <div class="cat">
              '.$cat_info['single_name'].'
            </div>
            <div class="title">
              <a href="'.$config['url'].$row['sef'].'/">'.$row['name'].'</a>
            </div>
            <div class="func">
            '.$content['icons_draw'].'
            </div>
            <div class="color">
              <span>'.item_colors($row['id']).'</span>
            </div>
            <div class="hidden">
              <div class="cmp" data-id="'.$row['id'].'" data-catid="'.$row['cat_id'].'">

                '.$compare.'

              </div>
              <div class="detail">
                <a href="'.$config['url'].$row['sef'].'/">Подробнее</a>
              </div>
            </div>
          </div>
          '.$new;
                unset($content['icons_draw']);
            }
           }

        $categories['title'] =  ($_GET['page'] > 0) ? $cat_info['title'].' - '.$pager->getCurrentPage().' страница' : $cat_info['title'];
        $categories['name'] = $cat_info['name'];
        $categories['type_name'] = $type_info['name'];
        $categories['cat_page'] = $cat_info['cat_page'];
        $categories['desc'] = ($_GET['page'] == 0) ? $cat_info['desc']: $cat_info['desc']='';
        $categories['id'] = $cat_info['id'];
         $categories['sef'] = $cat_info['sef'];
         $categories['type_info'] = $type_info;

        # Сортировка:
        //print_r($cookie);
        $sorter_encode = json_decode($cat_info['techies']);
        $sorter_filter = json_decode($cat_info['techies_filter']);
        foreach ($sorter_encode as $sorter_var => $var_id) {
        if (in_array($sorter_var, $sorter_filter)) {
        $categories['sorter'] .= '<div class="item select">
          <div class="value">
            <select name="" data-role="none">
              <option selected="selected" value="0">'.$var_id.'</option>
              '.gen_unique_techies($cat_info['id'], $sorter_var).'
            </select>
          </div>
        </div>';
        }
        }
        if (!$categories['sorter']) { $categories['sorter'] .= '<br>';}

      if ($pager != '')
            $categories['pager'] = '<i>Страница:</i>'.$pager.'';
        # Старые:
        $sql = mysqli_query($db, 'SELECT * FROM `items` WHERE `cat_id` = '.$cat_info['id'].' and `active` = 1 and `old` = 1 ORDER by `id` DESC LIMIT '.$pager->getSqlLimits());
    $number = mysqli_num_rows($sql);
    if ($number != false) {
         while ($row = mysqli_fetch_array($sql)) {
           $now = date('d.m.Y');
       $cat_info = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `categories` WHERE `id` = \''.mysqli_real_escape_string($db, $row['cat_id']).'\';'));
              $imgs = json_decode($row['imgs']);

              if ($row['icons'] != '') {
              $icons = json_decode($row['icons'], true);
              $id_icon = 1;
              foreach ($icons as $icon => $img) {

              $cat_icons = json_decode($cat_info['icons'], true);
              foreach ($cat_icons as $icon_id['0'] => $icon) {
                  if (!is_array($icon['0'])) {
                      if ($icon['0'] == $img) {


                      }
                  } else {
                  foreach ($icon['0'] as $img2 => $name2) {
                      if ($img2 == $img) {

                      $name = $name2;

                      }
                  }
                  }
              }

              //print_r($cat_icons);
              $content['icons_draw'] .= '<div class="item item_shar"><i><img width="34px" src="'.$img.'" alt=""/><span>'.$name.'</span></i></div>';
              if ($id_icon == 6) {break;}
                    $id_icon++;
              }
              }

                $categories['body_archive'] .= '<li class="product-item">
          <div class="img">
            <a href="'.$config['url'].$row['sef'].'/"><img width="253" height="183" src="'.resizer($imgs['0'], 183, 253, 2).'" alt=""/></a>
          </div>
          <div class="entry">
            <div class="cat">
              '.$cat_info['single_name'].'
            </div>
            <div class="title">
              <a href="'.$config['url'].$row['sef'].'/">'.$row['name'].'</a>
            </div>
            <div class="func">
            '.$content['icons_draw'].'
            </div>
            <div class="color">
              <span>'.item_colors($row['id']).'</span>
            </div>
            <div class="hidden">
              <div class="cmp" data-id="'.$row['id'].'" data-catid="'.$row['cat_id'].'">
                <a href="">Сравнить</a>
              </div>
              <div class="detail">
                <a href="'.$config['url'].$row['sef'].'/">Подробнее</a>
              </div>
            </div>
          </div>
          ';
                unset($content['icons_draw']);
            }




    }

}


return  $categories;
}

function category_by_id($sef, $page = 0, $ids = '') {
  global $config, $db;
$sef = trim(strip_tags($sef));
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/pagination.php');
# Считаем количество записей:
$cat_info = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `categories` WHERE `id` = \''.$sef.'\';'));
$type_info = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `types` WHERE `id` = '.$cat_info['type'].';'));

if ($ids) {
    $iid = 1;
    foreach ($ids as $item_id) {
        if (count($ids) != $iid) {
        $cat_in .= $item_id.',';
        } else {
        $cat_in .= $item_id;
        }
        $iid++;
    }
    $ids_in = ' and `id` IN ('.$cat_in.') ';
}
//echo $ids_in;
$count = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `items` WHERE `cat_id` = '.$cat_info['id'].' '.$ids_in.' and `active` = 1;'));
$countElements = $count['COUNT(*)'];
if ($countElements == 0) {
    $categories['body'] = '<br><br>Категория пуста!';
    $categories['name'] = $cat_info['name'];
    $categories['type_name'] = $type_info['name'];
    $categories['desc'] =  $cat_info['desc'];
    $categories['title'] =  ($_GET['page'] > 0) ? $categories['title'].' - '.$pager->getCurrentPage().' страница' : $categories['title'];
} else {
    # Вызываем класс:
    $params = array('pageSize' => 50, 'maxPage' => '20000', 'urlPrefix'   => $config['url'].$cat_info['sef'].'/', 'urlPostfix'  => '/', 'css' => '', 'title' => '', 'litag' => 'span', 'tag' => '', 'arr' => 'false', 'line' => '5', 'solid' => 'true');
    $pager = new goPaginator($countElements, $params);

    $sql = mysqli_query($db, 'SELECT * FROM `items` WHERE `cat_id` = '.$cat_info['id'].' and `active` = 1 '.$ids_in.' and `old` != 1 ORDER by `id` DESC LIMIT '.$pager->getSqlLimits());
    $number = mysqli_num_rows($sql);
    if ($number != false) {
         while ($row = mysqli_fetch_array($sql)) {
           $now = date('d.m.Y');
       $cat_info = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `categories` WHERE `id` = \''.mysqli_real_escape_string($db, $row['cat_id']).'\';'));
              $imgs = json_decode($row['imgs']);

              if ($row['icons'] != '') {
              $icons = json_decode($row['icons'], true);
              $id_icon = 1;
              foreach ($icons as $icon => $img) {

              $cat_icons = json_decode($cat_info['icons'], true);
              foreach ($cat_icons as $icon_id['0'] => $icon) {
                  if (!is_array($icon['0'])) {
                      if ($icon['0'] == $img) {


                      }
                  } else {
                  foreach ($icon['0'] as $img2 => $name2) {
                      if ($img2 == $img) {

                      $name = $name2;

                      }
                  }
                  }
              }

              //print_r($cat_icons);
              $content['icons_draw'] .= '<div class="item item_shar"><i><img width="34px" src="'.$img.'" alt=""/><span>'.$name.'</span></i></div>';
              if ($id_icon == 6) {break;}
                    $id_icon++;
              }
              }

                $new = ($row['new'] == 1 && strtotime($row['active_date']) > strtotime($now)) ? '<div class="new"></div>' : '';
                $old = ($row['old'] == 1) ? 'class="old"' : '';
                $specs = ($row['old'] == 1) ? 'Данная модель снята с производства' : $row['specs'];
                $color = ($row['colors']) ? $row['colors'] : '';

                $categories['body'] .= '<li class="product-item">
          <div class="img">
            <a href="'.$config['url'].$row['sef'].'/"><img width="253" height="183" src="'.resizer($imgs['0'], 183, 253, 2).'" alt=""/></a>
          </div>
          <div class="entry">
            <div class="cat">
              '.$cat_info['single_name'].'
            </div>
            <div class="title">
              <a href="'.$config['url'].$row['sef'].'/">'.$row['name'].'</a>
            </div>
            <div class="func">
            '.$content['icons_draw'].'
            </div>
            <div class="color">
              <span>'.item_colors($row['id']).'</span>
            </div>
            <div class="hidden">
              <div class="cmp" data-id="'.$row['id'].'" data-catid="'.$row['cat_id'].'">
                <a href="">Сравнить</a>
              </div>
              <div class="detail">
                <a href="'.$config['url'].$row['sef'].'/">Подробнее</a>
              </div>
            </div>
          </div>
          '.$new;
                unset($content['icons_draw']);
            }
           }
        # Старые:
        $sql = mysqli_query($db, 'SELECT * FROM `items` WHERE `cat_id` = '.$cat_info['id'].' '.$ids_in.' and `active` = 1 and `old` = 1 ORDER by `id` DESC LIMIT '.$pager->getSqlLimits());
    $number = mysqli_num_rows($sql);
    if ($number != false) {
         while ($row = mysqli_fetch_array($sql)) {
           $now = date('d.m.Y');
       $cat_info = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `categories` WHERE `id` = \''.mysqli_real_escape_string($db, $row['cat_id']).'\';'));
              $imgs = json_decode($row['imgs']);

              if ($row['icons'] != '') {
              $icons = json_decode($row['icons'], true);
              $id_icon = 1;
              foreach ($icons as $icon => $img) {

              $cat_icons = json_decode($cat_info['icons'], true);
              foreach ($cat_icons as $icon_id['0'] => $icon) {
                  if (!is_array($icon['0'])) {
                      if ($icon['0'] == $img) {


                      }
                  } else {
                  foreach ($icon['0'] as $img2 => $name2) {
                      if ($img2 == $img) {

                      $name = $name2;

                      }
                  }
                  }
              }

              //print_r($cat_icons);
              $content['icons_draw'] .= '<div class="item item_shar"><i><img width="34px" src="'.$img.'" alt=""/><span>'.$name.'</span></i></div>';
              if ($id_icon == 6) {break;}
                    $id_icon++;
              }
              }

                $categories['body_archive'] .= '<li class="product-item">
          <div class="img">
            <a href="'.$config['url'].$row['sef'].'/"><img width="253" height="183" src="'.resizer($imgs['0'], 183, 253, 2).'" alt=""/></a>
          </div>
          <div class="entry">
            <div class="cat">
              '.$cat_info['single_name'].'
            </div>
            <div class="title">
              <a href="'.$config['url'].$row['sef'].'/">'.$row['name'].'</a>
            </div>
            <div class="func">
            '.$content['icons_draw'].'
            </div>
            <div class="color">
              <span>'.item_colors($row['id']).'</span>
            </div>
            <div class="hidden">
              <div class="cmp" data-id="'.$row['id'].'" data-catid="'.$row['cat_id'].'">
                <a href="">Сравнить</a>
              </div>
              <div class="detail">
                <a href="'.$config['url'].$row['sef'].'/">Подробнее</a>
              </div>
            </div>
          </div>
          ';
                unset($content['icons_draw']);
            }

        $categories['title'] =  ($_GET['page'] > 0) ? $cat_info['title'].' - '.$pager->getCurrentPage().' страница' : $cat_info['title'];
        $categories['name'] = $cat_info['name'];
        $categories['type_name'] = $type_info['name'];
        $categories['cat_page'] = $cat_info['cat_page'];
        $categories['desc'] = ($_GET['page'] == 0) ? $cat_info['desc']: $cat_info['desc']='';
        $categories['id'] = $cat_info['id'];

        # Сортировка:
        //print_r($cookie);
        $sorter_encode = json_decode($cat_info['techies']);
        $sorter_filter = json_decode($cat_info['techies_filter']);
        foreach ($sorter_encode as $sorter_var => $var_id) {
        if (in_array($sorter_var, $sorter_filter)) {
        $categories['sorter'] .= '<div class="item select">
          <div class="level">'.$var_id.'</div>
          <div class="value">
            <select name="" data-role="none">
              <option selected="selected" value="0">'.$var_id.'</option>
              '.gen_unique_techies($cat_info['id'], $sorter_var).'
            </select>
          </div>
        </div>';
        }
        }
        if (!$categories['sorter']) { $categories['sorter'] .= '<br>';}

      if ($pager != '')
            $categories['pager'] = '<i>Страница:</i>'.$pager.'';
    }

}


return  $categories;
}



function news_list($page = 0) {
  global $config, $db;
$sef = trim(strip_tags($sef));
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/pagination.php');
# Считаем количество записей:
$count = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `news_new` WHERE `type` != \'4\';'));
$countElements = $count['COUNT(*)'];
if ($countElements == 0) {
    $categories['body'] = '<br><br>Новостей еще нет!!';
    $categories['title'] =  ($_GET['page'] > 0) ? 'Новости - '.$pager->getCurrentPage().' страница' : 'Новости';
} else {
    # Вызываем класс:
    $params = array('pageSize' => 24, 'maxPage' => '20000', 'urlPrefix'   => $config['url'].'news/', 'urlPostfix'  => '/', 'css' => '', 'title' => '', 'litag' => '', 'tag' => '', 'arr' => 'true', 'line' => '5', 'solid' => 'true');
    $pager = new goPaginator($countElements, $params);
    $sql = mysqli_query($db, 'SELECT * FROM `news_new` WHERE `type` != \'4\' ORDER by `id` DESC LIMIT '.$pager->getSqlLimits());
    $number = mysqli_num_rows($sql);
    if ($number != false) {
         while ($row = mysqli_fetch_array($sql)) {
                preg_match_all('/<img [^>]*src=["|\']([^"|\']+)/i', $row['text'], $img_matches);
                foreach ($img_matches['1'] as $imgs) {
                  $img_html .= '<img src="" style="width: 231px;">';
                  }
                 // print_r($img_matches['1']);
                 parse_str( parse_url( $imgs, PHP_URL_QUERY ), $my_array_of_vars );
                 if ($my_array_of_vars['src']) {
                 $img = $my_array_of_vars['src'];
                 } else {
                 $img = $img_matches['1']['0'];
                 }
                 $type = ($row['type'] == 2) ? '<span class="video">Видео</span>' : '';
                 if ($row['type'] == 2) {
                  parse_str( parse_url( $row['video'], PHP_URL_QUERY ), $my_array_of_vars2 );

                  //parse_str(file_get_contents("http://youtube.com/get_video_info?video_id=".$my_array_of_vars2['v']), $ytarr);
                  //      print_r($ytarr);
                  //$img = $ytarr['thumbnail_url'];
                  $img = 'http://i.ytimg.com/vi/'.$my_array_of_vars2['v'].'/hqdefault.jpg';
                  $link = '<a class="fancybox fancybox.iframe" href="http://www.youtube.com/embed/'.$my_array_of_vars2['v'].'?autoplay=1">';
                 }

                  if (!isset($link)) {  $link = '<a href="'.$config['url'].$row['sef'].'/">'; }
                  $categories['body'] .= '
                    <li>
      '.$link.'
        <span class="img">
          <span><img src="'.resizer($img, 206, 191, 2).'" alt="'.$row['name'].'"/></span>
        </span>
        <span class="entry">
          <span class="date">
           '.$row['date'].'
          </span>
          <span class="title">
           '.$row['name'].'
          </span>
        </span>
        '.$type.'
      </a>    ';
                $img_html = '';
                unset($link);
            }
        $categories['title'] =  ($_GET['page'] > 0) ? 'Новости - '.$pager->getCurrentPage().' страница' : 'Новости';
        $categories['name'] = 'Новости';


      if ($pager != '')
            $categories['pager'] = $pager;
    }

}
return  $categories;
}

function gen_unique_techies($cat_id, $techie_id) {
  global $config, $db;
$sql = mysqli_query($db, 'SELECT DISTINCT `value` FROM `techies` WHERE `techie_id` = '.$techie_id.' and `cat_id` = '.$cat_id.' ORDER by `value` ASC ');
    if (mysqli_num_rows($sql) != false) {
      while ($row = mysqli_fetch_array($sql)) {

        if ($_COOKIE['sorter']) {
          $cookie = explode('|', urldecode($_COOKIE['sorter']));
          if (($cookie['0'] == $cat_id) and ($cookie['1'] == $techie_id) and ($cookie['2'] == $row['value'])) {
          $selected = 'selected';
          } else {
          $selected = '';
          }
        }

        $colors_techies_array = array('6' => '15', '4' => '8', '17' => '7');
        if ($colors_techies_array[$cat_id] == $techie_id) {
            $colors_options .= $row['value'].', ';
        } else {
        if ($row['value'])
        $techies .= '<option name="version" value="cat_id='.$cat_id.'&techie_id='.$techie_id.'&techie_value='.$row['value'].'" '.$selected.'>'.$row['value'].'</option>';

       }

      }
    }

        if ($colors_options) {
            $colors_values = explode(',', $colors_options);
            $maped = array_map('trim', $colors_values);
            $uniq = array_filter(array_unique($maped));
            foreach ($uniq as $color) {

          if ($_COOKIE['sorter']) {
          $cookie = explode('|', urldecode($_COOKIE['sorter']));
          if (($cookie['0'] == $cat_id) and ($cookie['1'] == $techie_id) and ($cookie['2'] == $color)) {
          $selected = 'selected';
          } else {
          $selected = '';
          }
          }
                        $techies .= '<option name="version" value="http://harper.ru/sorter.php?cat_id='.$cat_id.'&techie_id='.$techie_id.'&techie_value='.$color.'" '.$selected.'>'.$color.'</option>';
            }
        }

return $techies;
}

function item_colors($id) {
  global $config, $db;
$sql = mysqli_query($db, 'SELECT `color` FROM `item_colors` WHERE `item_id` = '.$id);
    if (mysqli_num_rows($sql) != false) {
      while ($row = mysqli_fetch_array($sql)) {
        $i++;
        $color_info = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `colors` WHERE `name` = \''.$row['color'].'\' '));
        if (mysqli_num_rows($sql) != $i) {
        $content .= $color_info['name_ru'].', ';
        } else {
        $content .= $color_info['name_ru'];
        }
      }
        return strtolower($content);
    } else {
    return false;
    }
}

function categories_questions_add() {
  global $config, $db;
$sef = strip_tags($sef);
$id = intval(strip_tags($id));
$sql = mysqli_query($db, 'SELECT * FROM `categories_questions`;');
    if (mysqli_num_rows($sql) != false) {
      while ($row = mysqli_fetch_array($sql)) {
        $content .= '<option value="'.$row['id'].'/">'.$row['name'].'</option>';
      }
        return $content;
    } else {
    return false;
    }
}


function translit($str) {
header('Content-Type: text/html; charset=windows-1251');
setlocale(LC_ALL, 'ru_RU.windows-1251', 'rus');
mb_http_input('windows-1251');
mb_http_output('windows-1251');
mb_internal_encoding("windows-1251");
    $vars = array(
        "а"=>"a","б"=>"b",
        "в"=>"v","г"=>"g","д"=>"d", "ие"=> "iye", "е"=>"e","ж"=>"j","з"=>"z","и"=>"i","й"=>"y","к"=>"k",
        "л"=>"l","м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r","с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",
        "ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"y","ы"=>"yi","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya",
        " & " => "-and-", " – " => "-",  "  " => " ", " - "=> "-" , " "=> "_", "."=> "", ","=> "", "!"=> "",":"=> "","?"=> "", "  "=> "", "-"=> "-",
        "'"=> "", "*"=> "", "\""=> "", " / "=> "-", ")" => "", "(" => "", " — " => "-", "—" => "-", "^" => "", "/" => "-", "+" => "plus"
        );
    return strip_tags(strtr(preg_replace("/[^a-zа-я_ 0-9]/", "", mb_strtolower($str)),$vars));
}

function translit2($string)
  {
    $table = array(
                'А' => 'A',
                'Б' => 'B',
                'В' => 'V',
                'Г' => 'G',
                'Д' => 'D',
                'Е' => 'E',
                'Ё' => 'YO',
                'Ж' => 'ZH',
                'З' => 'Z',
                'И' => 'I',
                'Й' => 'J',
                'К' => 'K',
                'Л' => 'L',
                'М' => 'M',
                'Н' => 'N',
                'О' => 'O',
                'П' => 'P',
                'Р' => 'R',
                'С' => 'S',
                'Т' => 'T',
                'У' => 'U',
                'Ф' => 'F',
                'Х' => 'H',
                'Ц' => 'C',
                'Ч' => 'CH',
                'Ш' => 'SH',
                'Щ' => 'CSH',
                'Ь' => '',
                'Ы' => 'Y',
                'Ъ' => '',
                'Э' => 'E',
                'Ю' => 'YU',
                'Я' => 'YA',

                'а' => 'a',
                'б' => 'b',
                'в' => 'v',
                'г' => 'g',
                'д' => 'd',
                'е' => 'e',
                'ё' => 'yo',
                'ж' => 'zh',
                'з' => 'z',
                'и' => 'i',
                'й' => 'j',
                'к' => 'k',
                'л' => 'l',
                'м' => 'm',
                'н' => 'n',
                'о' => 'o',
                'п' => 'p',
                'р' => 'r',
                'с' => 's',
                'т' => 't',
                'у' => 'u',
                'ф' => 'f',
                'х' => 'h',
                'ц' => 'c',
                'ч' => 'ch',
                'ш' => 'sh',
                'щ' => 'csh',
                'ь' => '',
                'ы' => 'y',
                'ъ' => '',
                'э' => 'e',
                'ю' => 'yu',
                'я' => 'ya',
                ' ' => '_'
    );

    $output = str_replace(
        array_keys($table),
        array_values($table),$string
    );

    return $output;
}

function resizer($url, $h = '236', $w = '325', $zc = 4) {
  global $config;
return $config['url'].'resize.php?src='.$url.'&h='.$h.'&w='.$w.'&zc='.$zc.'&q=100';
}


function getDatesIntervalByQuarter($qrt, $year)
{
  if(!$year){
    return [];
  }
  $dates = ['from' => '', 'to' => ''];
  $from = [
    1 => $year . '-01-01',
    2 => $year . '-04-04',
    3 => $year . '-07-01',
    4 => $year . '-10-01'
  ];
  $to = [
      1 => $year . '-03-31',
      2 => $year . '-06-30',
      3 => $year . '-09-30',
      4 => $year . '-12-31'
    ];
  if(!$qrt){
    $dates['to'] = $to[4];
    $dates['from'] = $from[1];
  }else{
    $dates['to'] = $to[$qrt];
    $dates['from'] = $from[$qrt];
  }
  return $dates;
}

function getDatesInterval($curPage){
  $dates = ['from' => '', 'to' => ''];
  $curPage--;
  $t = mktime(0, 0, 0, date('m') - (($curPage * 2) + $curPage), date('d'), date('Y'));
  $dates['to'] = date('Y-m-t', $t);
  $t = mktime(0, 0, 0, date('m') - (($curPage + 1) * 2 + $curPage), date('d'), date('Y'));
  $dates['from'] = date('Y-m', $t) . '-01';
  return $dates;
}



function getMonthsCnt($dateFrom, $dateTo){
  $dateFrom = date_create(date('Y-m-d', strtotime($dateFrom)));
  $dateTo = date_create(date('Y-m-d', strtotime($dateTo)));
  $y = date_format($dateTo, 'Y') - date_format($dateFrom, 'Y');
  if(!$y){
      return date_format($dateTo, 'n') - date_format($dateFrom, 'n');
  }
   $m = (12 - date_format($dateFrom, 'n')) + date_format($dateTo, 'n');
   $m += ($y - 1) * 12;
   return $m;
}


function getBillingDocFillStatus($billing){
  $keys = ['bank_name', 'bik', 'sc2', 'agree'];
  $r = 0;
  foreach($keys as $k){
    if(empty($billing[$k])){
      $r++;
    }
  }
  if(!$r){
    return 'full';
  }
  if($r == count($keys)){
    return 'empty';
  }
  if(empty($billing['agree'])){
    return 'agree';
  }
  return 'partly';
  }

  
function hasLackOfDocuments($serviceID){
  global $db;
return (bool)mysqli_fetch_assoc(mysqli_query($db, 'SELECT COUNT(*) AS cnt FROM `services_documents` WHERE `service_id` = ' . $serviceID . ' AND `status` = "Нет" AND `deleted` = 0'))['cnt'];
  }
?>