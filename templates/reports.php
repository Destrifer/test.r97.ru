<?php

use models\Parts;
use models\parts\Balance;
use models\staff\Staff;
use program\core;
use program\core\Time;
ini_set("memory_limit", "3000M");
  if(!empty($_POST['date'])){
$d = explode(' - ', $_POST['date']);
$_POST['date1'] = Time::format($d[0], 'Y-m-d');
$_POST['date2'] = (!empty($d[1])) ? Time::format($d[1], 'Y-m-d') : '';
}
# Сохраняем:
if (($_POST['send'] == 1 && $_POST['receiver'] != '') || !\models\User::hasRole('admin') && $_POST['send'] == 1) {
  $repairFinal = models\repaircard\Repair::getRepairFinal();
  $repairFinal[0] = '';


$glob_id = 3;
require_once './adm/excel/vendor/autoload.php';

        if (file_exists('adm/excel/files')) {
            foreach (glob('adm/excel/files/*') as $file) {
                unlink($file);
            }
        }

    $lfcr = chr(10);
        $new_file = 'adm/excel/files/1.xlsx';
        copy('_new-codebase/content/templates/excel/report.xlsx', $new_file);

        $xls = PHPExcel_IOFactory::load($new_file);
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();


if ((isset($_POST['status_33_1']) && $_POST['status_33_1'] == 1) || (isset($_POST['status_33_2']) && $_POST['status_33_2'] == 1)) {

$sheet->setCellValue("A1", 'Номер ремонта');
$sheet->setCellValue("B1", 'Клиент');
$sheet->setCellValue("C1", 'Модель');
$sheet->setCellValue("D1", 'Серийный номер');
$sheet->setCellValue("E1", 'Завод производитель');
$sheet->setCellValue("F1", 'Номер заказа у завода производителя');
$sheet->setCellValue("G1", 'Стоимость модели');

if (\models\User::hasRole('admin', 'slave-admin')) {
$sheet->setCellValue("H1", 'Без мастера');
$sheet->setCellValue("I1", 'Мастер');
$sheet->setCellValue("J1", 'Завод');
$sheet->setCellValue("K1", 'Заказ');
$sheet->setCellValue("L1", 'Статус ремонта');
}

} else {

$dateStrFrom = (!empty($_POST['date1'])) ? ' с ' . core\Time::format($_POST['date1']) : '';
$dateStrTo = (!empty($_POST['date2'])) ? ' по ' . core\Time::format($_POST['date2']) : '';
$serviceStr = 'все СЦ';
if(!\models\User::hasRole('admin', 'slave-admin')){
  $row = mysqli_fetch_assoc(mysqli_query($db, 'SELECT `name`, `name_public` FROM `requests` WHERE `user_id` = '.\models\User::getData('id')));
  $serviceStr = $row['name'] . ', ' . $row['name_public'];
}elseif(!empty($_POST['receiver']) && $_POST['receiver'] != 'all' && $_POST['receiver'] != 'except'){
  $row = mysqli_fetch_assoc(mysqli_query($db, 'SELECT `name`, `name_public` FROM `requests` WHERE `user_id` = '.$_POST['receiver']));
  $serviceStr = $row['name'] . ', ' . $row['name_public'];
}

  $sheet->setCellValue("A1", 'Сервисный отчет' . $dateStrFrom . $dateStrTo . ', '.$serviceStr);


}



if (in_array('\'Оплачен\'', $_POST['status'])) {
$where = '\'Подтвержден\', \'Выдан\'';
$payed = 1;
} else {
  $payed = 0;
$where = implode(',', $_POST['status']);
}


$where_user = '';
if ($_POST['receiver'] != 'all' && $_POST['receiver'] != 'except' && $_POST['receiver'] != '') {

$where_user = 'and `service_id` = \''.mysqli_real_escape_string($db, $_POST['receiver']).'\'';

}else if($_POST['receiver'] == 'except'){
  $where_user = 'and `service_id` != 33';
}

if (!\models\User::hasRole('admin')) {
$where_user = 'and `service_id` = \''.mysqli_real_escape_string($db, \models\User::getData('id')).'\'';
}

if (!empty($_POST['date1']) && !empty($_POST['date2'])) {
  if(in_array('\'Выдан\'', $_POST['status']) || in_array('\'Подтвержден\'', $_POST['status'])){
    $where_date = 'and `approve_date` BETWEEN \''.$_POST['date1'].'\' AND \''.date('Y.m.d', strtotime($_POST['date2'])).'\'';
  }else{
    $where_date = 'and `create_date` BETWEEN \''.$_POST['date1'].'\' AND \''.date('Y.m.d', strtotime($_POST['date2'] . '+1 day')).'\'';
  }

}

$where_model = (!empty($_POST['model_id'])) ? 'and `model_id` = \''.mysqli_real_escape_string($db, $_POST['model_id']).'\'' : '';
$where_client_id = (!empty($_POST['clientid'])) ? 'and `client_id` = \''.mysqli_real_escape_string($db, $_POST['client_id']).'\'' : '';
$where_new = '';
$where_new .= (!empty($_POST['status_33_1'])) ? ' and `problem_id` IN (3,6,14,15,16,18,19,24,25,26,27,28,29,30,31,33,35,39,41,43) ' : '';
$where_new .= (!empty($_POST['status_33_2'])) ? ' and `problem_id` IN (2,4,7,8,9,10,11,12,17,20,21,36,37,40,42) ' : '';

if ($_POST['status_id']) {
$psql = implode(',', $_POST['status_id']);
$status_id_sql = ' and `status_id` IN ('.$psql.') ';
}

$no_master_sql = (!empty($_POST['no_master'])) ? 'and `master_user_id` != 0' : '';


$sql = mysqli_query($db, 'SELECT * FROM `repairs` WHERE `deleted` = 0 '.$where_user.' '.$where_model.' '.$where_client_id.' '.$where_date.' '.$status_id_sql.' and `status_admin` in ('.$where.') '.$where_new.' '.$no_master_sql.' ;');

      while ($row = mysqli_fetch_array($sql)) {

     if ($payed == 1) {

     if ($row['app_date']) {
     $app = DateTime::createFromFormat('Y.m.d', $row['app_date']);
     $year = $app->format('Y');
     $month = $app->format('n');
     }

      if (check_payed_repair($year, $month, $row['service_id'])) {

        $content = $row;
        $content['model'] = model_info($content['model_id']);
        $content['service_info'] = service_request_info($content['service_id']);
        $content['cat_info'] = model_cat_info($content['model']['cat']);
        $content['parts_info'] = repairs_parts_info_array($content['id']);
        $content['master_info'] = master_info($content['master_id']);
        if ($row['master_user_id']) {
        $master = Staff::getStaff(['id' => $row['master_user_id']]);
        }
        $serialInfo = models\Serials::getSerial($content['serial'], $content['model_id']);
        switch ($row['status_id']) {
          case 1:
              $status_name = "Гарантийный";
              break;
          case 5:
              $status_name =  "Условно-гарантийный";
              break;
          case 6:
              $status_name =  "Платный";
              break;
       }

      if ($content['begin_date'] != '0000-00-00') {
      $date1 = new DateTime($content['begin_date']);
      $date1_ready = $date1->format('d.m.Y');
      }
      if ($content['finish_date']) {

      if (preg_match('/2020/',$content['finish_date']) || preg_match('/2019/',$content['finish_date']) || preg_match('/2018/',$content['finish_date']) || preg_match('/2017/',$content['finish_date'])) {
      $date2 = DateTime::createFromFormat('Y-m-d', $content['finish_date']);
      $date2_ready = $date2->format('d.m.Y');
      } else if (preg_match('/2020/',$content['finish_date']) || preg_match('/2019/',$content['finish_date']) || preg_match('/2018/',$content['finish_date']) || preg_match('/2017/',$content['finish_date'])) {
      $date2 = DateTime::createFromFormat('Y-m-d', $content['finish_date']);
      $date2_ready = $date2->format('d.m.Y');
      } else {
      $date2 = DateTime::createFromFormat('Y-m-d', $content['finish_date']);
      $date2_ready = $date2->format('d.m.Y');
      }

      }

      if (!core\Time::isEmpty($content['begin_date']) && !core\Time::isEmpty($content['finish_date'])) {
      $date1 = new DateTime($content['begin_date']);

      $date2 = new DateTime($date2_ready);

      $diff = $date2->diff($date1)->format("%a");
      }

      if (check_returns_pls($row['return_id'])  || $row['return_id'] == 0) {


      if (in_array($content['model']['brand'], $_POST['brands'])) {
        $sheet->setCellValue("A$glob_id", core\Time::format($content['receive_date']));
        $sheet->setCellValue("B$glob_id", $status_name);
        $sheet->setCellValue("D$glob_id", $content['status_admin']);
        $sheet->setCellValue("E$glob_id", $content['service_info']['id']);
        $sheet->setCellValue("F$glob_id", $content['service_info']['name']);
        $sheet->setCellValue("G$glob_id", $content['rsc']);
        $sheet->setCellValue("H$glob_id", $content['id']);
        $sheet->setCellValue("I$glob_id", $content['model']['brand']);
        $sheet->setCellValue("J$glob_id", $content['cat_info']['name']);
        $sheet->setCellValue("L$glob_id", $content['model']['name']);
        $sheet->setCellValue("M$glob_id", $content['serial']);
        $sheet->setCellValue("P$glob_id", $serialInfo['plant']);
        $sheet->setCellValue("Q$glob_id", str_replace('|', ', ', trim($content['complex'], '|')));
        if(!empty($content['visual'])){
          $content['visual_comment'] = $content['visual'] . ', ' . $content['visual_comment'];
        }
        $sheet->setCellValue("R$glob_id", str_replace('|', ', ', trim($content['visual_comment'], '| ,')));
        if ($content['client_type'] == 2) { // Принят от
          $client = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `clients` WHERE `id` = \''.mysqli_real_escape_string($db, $content['client_id']).'\' LIMIT 1;'));
          if($client){
            $sheet->setCellValue("S$glob_id", $client['name']);
          }else{
            $sheet->setCellValue("S$glob_id", $content['name_shop']);
          }
        } else if ($content['client_type'] == 1) {
          $sheet->setCellValue("S$glob_id", $content['client']);
        }//count($content['parts_info'])
        $s = '';
        if($content['status_ship_id'] == 1 || ($content['status_ship_id'] == 0 && $content['sell_date'] == '0000-00-00' && $content['client_type'] == 2)){
          $s = 'Предторговый';
        }elseif($content['status_ship_id'] == 2 || ($content['status_ship_id'] == 0 && $content['sell_date'] != '0000-00-00') ){
          $s = 'Клиентский';
        }elseif($content['status_ship_id'] == 3){
          $s = 'Повторный';
        }
        $sheet->setCellValue("T$glob_id", $s); // Статус поступления 
        $clientType = ($content['client_type'] == 2)? 'Магазин': 'Потребитель';
        $sheet->setCellValue("U$glob_id", $clientType);
        $sheet->setCellValue("V$glob_id", $content['client']);
        $sheet->setCellValue("W$glob_id", trim($content['address']));
        $sheet->setCellValue("X$glob_id", $content['phone']);
        $sheet->setCellValue("Y$glob_id", trim($content['name_shop']));
        $sheet->setCellValue("Z$glob_id", trim($content['address_shop']));
        $sheet->setCellValue("AA$glob_id", trim($content['phone_shop']));
        $sheet->setCellValue("AB$glob_id", core\Time::format($content['sell_date']));
        $sheet->setCellValue("AC$glob_id", core\Time::getBetween($content['sell_date'], date('Y-m-d'))); // Время с момента продажи
        $sheet->setCellValue("AD$glob_id", $content['bugs']); // Заявленная неисправность
        $sheet->setCellValue("AE$glob_id", getDisease($content['disease'])); // Симптомы
        $sheet->setCellValue("AF$glob_id", $date1_ready); // Дата начала ремонта
        if(!empty($master['name'])){ // Мастер
          $sheet->setCellValue("AG$glob_id", $master['surname'].' '.$master['name'].' '.$master['thirdname']);
        }else{
          $sheet->setCellValue("AG$glob_id", $content['master_info']['surname'].' '.$content['master_info']['name'].' '.$content['master_info']['thirdname']);
        }
        $sheet->setCellValue("AJ$glob_id", $content['comment']); // Комментарии к ремонту
        $sheet->setCellValue("AK$glob_id", $content['parts_info']['0']['qty']); // Кол-во деталей
        $sheet->setCellValue("AL$glob_id", $content['parts_info']['0']['position']); // Позиционное обозначение
        $problem = get_content_by_id('details_problem', $content['parts_info']['0']['problem_id']);
        $sheet->setCellValue("AM$glob_id", $problem['name']); // ID причин отказа детали (да-да, name тут - это id!)
        if(in_array($content['parts_info']['0']['problem_id'], [5, 44])){
          $content['parts_info']['0']['name'] = 'Не использовалась';
        }elseif(is_numeric($content['parts_info']['0']['name'])){
          $sqlP = mysqli_query($db, 'SELECT `list` AS name FROM `parts` WHERE `id` = '.$content['parts_info']['0']['name'].' ;');
          $rowP = mysqli_fetch_array($sqlP);
          if($rowP){
            $content['parts_info']['0']['name'] = $rowP['name'];
          }
        }
        $sheet->setCellValue("AJ$glob_id", $content['parts_info']['0']['name']); // Наименование детали
        $sheet->setCellValue("AM$glob_id", get_content_by_id('repair_type', $content['parts_info']['0']['repair_type_id'])['name']); // ID причин отказа детали
        $sheet->setCellValue("AQ$glob_id", core\Time::format($content['finish_date']));
        $sheet->setCellValue("AR$glob_id", core\Time::format($content['approve_date'])); // Дата подтверждения
        $sheet->setCellValue("AS$glob_id", core\Time::format($content['out_date'])); // Дата выдачи
        $sheet->setCellValue("AT$glob_id", $diff);
        $sheet->setCellValue("AU$glob_id", (($content['parts_info']['0']['ordered_flag'] == 1) ? 'Да' : 'Нет')); // Деталь производителя
        $sheet->setCellValue("AV$glob_id", $repairFinal[$content['repair_final']]); // итоги ремонта
        $sheet->setCellValue("AW$glob_id", $content['parts_info']['0']['price']); // цена
        $sheet->setCellValue("AX$glob_id", $content['parts_info']['sum']);
        $sheet->setCellValue("AY$glob_id", $row['transport_cost']);
        $sheet->setCellValue("AZ$glob_id", $content['dismant_cost']); // Стоимость демонтажа
        $sheet->setCellValue("BA$glob_id", $content['install_cost']); // Стоимость монтажа
        $sheet->setCellValue("BB$glob_id", $content['total_price']);
        $sheet->setCellValue("BC$glob_id", $content['total_price'] + $row['transport_cost'] + $row['parts_cost'] + $row['install_cost'] + $row['dismant_cost']);
          
        if ((\models\User::getData('id') == 1 || \models\User::getData('id') == 33)) {
          $sheet->setCellValue("AP$glob_id", $problem['type']); // Тип ремонта
          $sheet->setCellValue("C$glob_id", $content['anrp_number']);
          $sheet->setCellValue("K$glob_id", $content['model']['model_id']); // торговый код
          $sheet->setCellValue("N$glob_id", $serialInfo['provider']);
          $sheet->setCellValue("O$glob_id", $serialInfo['order']);
          if ($content['master_user_id'] == 0) {
            $sheet->setCellValue("AH$glob_id", 'Без мастера'); // Без мастера
            }
          $sheet->setCellValue("AM$glob_id", $content['parts_info']['0']['problem_id']); // ID причин отказа детали
        }
      $glob_id++;

      }

      }

      }

   

     } else {


        $content = $row;
        $content['model'] = model_info($content['model_id']);
        $content['service_info'] = service_request_info($content['service_id']);
        $content['cat_info'] = model_cat_info($content['model']['cat']);
        $content['parts_info'] = repairs_parts_info_array($content['id']);
        $content['master_info'] = master_info($content['master_id']);
        $master = ['name' => '', 'surname' => '', 'thirdname' => ''];
        if ($row['master_user_id']) {
        $master = Staff::getStaff(['id' => $row['master_user_id']]);
        }
        $serialInfo = models\Serials::getSerial($content['serial'], $content['model_id']);
           switch ($row['status_id']) {
          case 1:
              $status_name = "Гарантийный";
              break;
          case 5:
              $status_name =  "Условно-гарантийный";
              break;
          case 6:
              $status_name =  "Платный";
              break;
       }

      if (isset($content['begin_date'])) {
      $date1 = new DateTime($content['begin_date']);
      $date1_ready = $date1->format('d.m.Y');
      }

      if (strtotime($content['ending']) > 0) {

      $date2 = DateTime::createFromFormat('Y-m-d', $content['ending']);
      $date2_ready = $date2->format('d.m.Y');

      }

      if (!core\Time::isEmpty($content['begin_date']) && !core\Time::isEmpty($content['finish_date'])) {
      $date1 = new DateTime($content['begin_date']);
      $date2 = new DateTime($date2_ready);
      $diff_pre = $date2->diff($date1);
      if ($diff_pre) {
      $diff = $diff_pre->format("%a");
      }
      }

      if (1 == 1) {
      //check_returns_pls($row['return_id'])  || $row['return_id'] == 0
      $provider = get_provider_info($content['model_id']);
      if (in_array($content['model']['brand'], $_POST['brands'])) {


      if ((isset($_POST['status_33_1']) && $_POST['status_33_1'] == 1) || (isset($_POST['status_33_2']) && $_POST['status_33_2'] == 1)) {
   
      $ids_count++;
      $sum_count += $content['model']['price_usd'];

$sheet->setCellValue("A$glob_id", $content['id']);
$sheet->setCellValue("B$glob_id", $content['client']);
$sheet->setCellValue("C$glob_id", $content['model']['name']);
$sheet->setCellValue("D$glob_id", $content['serial']);
$sheet->setCellValue("E$glob_id", $provider['name']);
$sheet->setCellValue("F$glob_id", $provider['order_id']);
$sheet->setCellValue("G$glob_id", $content['model']['price_usd']);

if ((\models\User::getData('id') == 1 || \models\User::getData('id') == 33) && $content['master_user_id'] == 0) {
$sheet->setCellValue("H$glob_id", 'Без мастера');
}
if (\models\User::getData('id') == 33) {
$sheet->setCellValue("I$glob_id", $master['surname'].' '.$master['name'].' '.$master['thirdname']);
$sheet->setCellValue("J$glob_id", $serialInfo['provider']);
$sheet->setCellValue("K$glob_id", $serialInfo['order']);
}
if (\models\User::hasRole('admin')) {
  $sheet->setCellValue("L$glob_id", $status_name);
  $sheet->setCellValue("AK$glob_id", str_replace('|', ', ', trim($content['complex'], '|')));
  $sheet->setCellValue("AL$glob_id", str_replace('|', ', ', trim($content['visual_comment'], '|')));
  $sheet->setCellValue("AM$glob_id", trim($content['city_shop']));
  $sheet->setCellValue("AN$glob_id", trim($content['address_shop'])); 
  $sheet->setCellValue("AO$glob_id", trim($content['city']));
  $sheet->setCellValue("AP$glob_id", trim($content['address']));  
  $sheet->setCellValue("AQ$glob_id", trim($content['phone_shop']));  
  $clientType = ($content['client_type'] == 2)? 'Магазин': 'Потребитель';
  $sheet->setCellValue("AR$glob_id", $clientType);  
  $sheet->setCellValue("AS$glob_id", core\Time::format($content['receive_date']));  
  }
 $glob_id++;

      } else {

        $sheet->setCellValue("A$glob_id", core\Time::format($content['receive_date']));
        $sheet->setCellValue("B$glob_id", $status_name);
        $sheet->setCellValue("D$glob_id", $content['status_admin']);
        $sheet->setCellValue("E$glob_id", $content['service_info']['id']);
        $sheet->setCellValue("F$glob_id", $content['service_info']['name']);
        $sheet->setCellValue("G$glob_id", $content['rsc']);
        $sheet->setCellValue("H$glob_id", $content['id']);
        $sheet->setCellValue("I$glob_id", $content['model']['brand']);
        $sheet->setCellValue("J$glob_id", $content['cat_info']['name']);
        $sheet->setCellValue("L$glob_id", $content['model']['name']);
        $sheet->setCellValue("M$glob_id", $content['serial']);
        $sheet->setCellValue("P$glob_id", $serialInfo['plant']);
        $sheet->setCellValue("Q$glob_id", str_replace('|', ', ', trim($content['complex'], '|')));
        if(!empty($content['visual'])){
          $content['visual_comment'] = $content['visual'] . ', ' . $content['visual_comment'];
        }
        $sheet->setCellValue("R$glob_id", str_replace('|', ', ', trim($content['visual_comment'], '| ,')));
        if ($content['client_type'] == 2) { // Принят от
          $client = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `clients` WHERE `id` = \''.mysqli_real_escape_string($db, $content['client_id']).'\' LIMIT 1;'));
          if($client){
            $sheet->setCellValue("S$glob_id", $client['name']);
          }else{
            $sheet->setCellValue("S$glob_id", $content['name_shop']);
          }
        } else if ($content['client_type'] == 1) {
          $sheet->setCellValue("S$glob_id", $content['client']);
        }//count($content['parts_info'])
        $s = '';
        if($content['status_ship_id'] == 1 || ($content['status_ship_id'] == 0 && core\Time::isEmpty($content['sell_date']) && $content['client_type'] == 2)){
          $s = 'Предторговый';
        }elseif($content['status_ship_id'] == 2 || ($content['status_ship_id'] == 0 && !core\Time::isEmpty($content['sell_date'])) ){
          $s = 'Клиентский';
        }elseif($content['status_ship_id'] == 3){
          $s = 'Повторный';
        }
        $sheet->setCellValue("T$glob_id", $s); // Статус поступления 
        $clientType = ($content['client_type'] == 2)? 'Магазин': 'Потребитель';
        $sheet->setCellValue("U$glob_id", $clientType);
        $sheet->setCellValue("V$glob_id", $content['client']);
        $sheet->setCellValue("W$glob_id", trim($content['address']));
        $sheet->setCellValue("X$glob_id", $content['phone']);
        $sheet->setCellValue("Y$glob_id", trim($content['name_shop']));
        $sheet->setCellValue("Z$glob_id", trim($content['address_shop']));
        $sheet->setCellValue("AA$glob_id", trim($content['phone_shop']));
        $sheet->setCellValue("AB$glob_id", core\Time::format($content['sell_date']));
        $sheet->setCellValue("AC$glob_id", core\Time::getBetween($content['sell_date'], date('Y-m-d'))); // Время с момента продажи
        $sheet->setCellValue("AD$glob_id", $content['bugs']);
        $sheet->setCellValue("AE$glob_id", getDisease($content['disease'])); // Симптомы
        $sheet->setCellValue("AF$glob_id", $date1_ready);
        if(!empty($master['name'])){  // Мастер
          $sheet->setCellValue("AG$glob_id", $master['surname'].' '.$master['name'].' '.$master['thirdname']);
        }else{
          $sheet->setCellValue("AG$glob_id", $content['master_info']['surname'].' '.$content['master_info']['name'].' '.$content['master_info']['thirdname']);
        }
        $sheet->setCellValue("AJ$glob_id", $content['comment']);
        $sheet->setCellValue("AK$glob_id", $content['parts_info']['0']['qty']);
        $sheet->setCellValue("AL$glob_id", $content['parts_info']['0']['position']);
        $problem = get_content_by_id('details_problem', $content['parts_info']['0']['problem_id']);
        $sheet->setCellValue("AN$glob_id", $problem['name']);
        if(in_array($content['parts_info']['0']['problem_id'], [5, 44])){
          $content['parts_info']['0']['name'] = 'Не использовалась';
        }elseif(is_numeric($content['parts_info']['0']['name'])){
          $sqlP = mysqli_query($db, 'SELECT `list` AS name FROM `parts` WHERE `id` = '.$content['parts_info']['0']['name'].' ;');
          $rowP = mysqli_fetch_array($sqlP);
          if($rowP){
            $content['parts_info']['0']['name'] = $rowP['name'];
          }
        }
        $sheet->setCellValue("AJ$glob_id", $content['parts_info']['0']['name']);
        $sheet->setCellValue("AO$glob_id", get_content_by_id('repair_type', $content['parts_info']['0']['repair_type_id'])['name']);
        $sheet->setCellValue("AQ$glob_id", core\Time::format($content['finish_date']));
        $sheet->setCellValue("AR$glob_id", core\Time::format($content['approve_date'])); // Дата подтверждения
        $sheet->setCellValue("AS$glob_id", core\Time::format($content['out_date'])); // Дата выдачи
        $sheet->setCellValue("AT$glob_id", $diff);
        $sheet->setCellValue("AU$glob_id", (($content['parts_info']['0']['ordered_flag'] == 1) ? 'Да' : 'Нет'));
        $sheet->setCellValue("AV$glob_id", $repairFinal[$content['repair_final']]); // итоги ремонта
        $sheet->setCellValue("AW$glob_id", $content['parts_info']['0']['price']);
        $sheet->setCellValue("AX$glob_id", $content['parts_info']['sum']);
        $sheet->setCellValue("AY$glob_id", $row['transport_cost']);
        $sheet->setCellValue("AZ$glob_id", $content['dismant_cost']); // Стоимость демонтажа
        $sheet->setCellValue("BA$glob_id", $content['install_cost']); // Стоимость монтажа
        $sheet->setCellValue("BB$glob_id", $content['total_price']);
        $sheet->setCellValue("BC$glob_id", $content['total_price'] + $row['transport_cost'] + $row['parts_cost'] + $row['install_cost'] + $row['dismant_cost']);
          
        if ((\models\User::getData('id') == 1 || \models\User::getData('id') == 33)) {
          $sheet->setCellValue("AP$glob_id", $problem['type']); // Тип ремонта
          $sheet->setCellValue("C$glob_id", $content['anrp_number']);
          $sheet->setCellValue("K$glob_id", $content['model']['model_id']); // торговый код
          $sheet->setCellValue("N$glob_id", $serialInfo['provider']);
          $sheet->setCellValue("O$glob_id", $serialInfo['order']);
          $sheet->setCellValue("P$glob_id", $serialInfo['plant']); // Завод-сборщик
          if ($content['master_user_id'] == 0) {
            $sheet->setCellValue("AH$glob_id", 'Без мастера');
            }
          $sheet->setCellValue("AM$glob_id", $content['parts_info']['0']['problem_id']); // ID причин отказа детали
        }
/* 
if (\models\User::getData('id') == 1 || \models\User::getData('id') == 33) {
$sheet->setCellValue("AG$glob_id", $master['surname'].' '.$master['name'].' '.$master['thirdname']);
$sheet->setCellValue("AH$glob_id", $provider['name']);
$sheet->setCellValue("AI$glob_id", $provider_order);
}
if (\models\User::hasRole('admin')) {
  $sheet->setCellValue("AJ$glob_id", $status_name);
  $sheet->setCellValue("AK$glob_id", str_replace('|', ', ', trim($content['complex'], '|')));
  $sheet->setCellValue("AL$glob_id", str_replace('|', ', ', trim($content['visual_comment'], '|')));
  $sheet->setCellValue("AM$glob_id", trim($content['city_shop']));
  $sheet->setCellValue("AN$glob_id", trim($content['address_shop'])); 
  $sheet->setCellValue("AO$glob_id", trim($content['city']));
  $sheet->setCellValue("AP$glob_id", trim($content['address']));  
  $sheet->setCellValue("AQ$glob_id", trim($content['phone_shop'])); 
 
  $sheet->setCellValue("AR$glob_id", $clientType);   
  $sheet->setCellValue("AS$glob_id", trim($content['date_get']));  
  }
        if ($content['client_type'] == 2) {
            $client = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `clients` WHERE `id` = \''.mysqli_real_escape_string($db, $content['client_id']).'\' LIMIT 1;'));
            $sheet->setCellValue("AE$glob_id", $client['name']);

        } else if ($content['client_type'] == 1) {
            $sheet->setCellValue("AE$glob_id", $content['client']);
        }
 */

 $glob_id++;
      }

      }

      }

     }

      }

      $border = array(
        'borders'=>array(
          'allborders' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN,
            'color' => array('rgb' => '000000')
          )
        )
      );
       
      $sheet->getStyle("A3:BC".($glob_id - 1))->applyFromArray($border);

      if(\models\User::getData('id') != 1 && \models\User::getData('id') != 33){
        $sheet->removeColumn('AM'); // ID причин отказа детали
        $sheet->removeColumn('AH'); // Без мастера
        $sheet->removeColumn('P');
        $sheet->removeColumn('O');
        $sheet->removeColumn('N');
        $sheet->removeColumn('K');
        $sheet->removeColumn('F');
        $sheet->removeColumn('E');
        $sheet->removeColumn('C');
      }

      

if ($_POST['status_33_1'] == 1 || $_POST['status_33_2'] == 1) {
      $provider = get_provider_info($content['model_id']);

$sheet->setCellValue("A$glob_id", 'Итого: '.$ids_count.'');
$sheet->setCellValue("B$glob_id", '');
$sheet->setCellValue("C$glob_id", '');
$sheet->setCellValue("D$glob_id", '');
$sheet->setCellValue("E$glob_id", '');
$sheet->setCellValue("F$glob_id", '');
$sheet->setCellValue("G$glob_id", 'Сумма итого: '.$sum_count.'');


      }



$xls->getDefaultStyle()->getAlignment()->setWrapText(true);
foreach ($xls->getWorksheetIterator() as $worksheet) {

    $xls->setActiveSheetIndex($xls->getIndex($worksheet));

    $sheet = $xls->getActiveSheet();

    $cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(true);
    /** @var PHPExcel_Cell $cell */
    foreach ($cellIterator as $cell) {
        $sheet->getColumnDimension($cell->getColumn())->setWidth(22);
            }

}


        $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
        $objWriter->save($new_file);

       header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="report_'.date('d.m.Y').'.xlsx"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($new_file));
        ob_clean();
        flush();
        readfile($new_file);

exit;

}

if (($_POST['send'] == 2) || (!\models\User::hasRole('admin')) && $_POST['send'] == 2) {



$glob_id = 2;
require_once './adm/excel/vendor/autoload.php';

        if (file_exists('adm/excel/files')) {
            foreach (glob('adm/excel/files/*') as $file) {
                unlink($file);
            }
        }

    $lfcr = chr(10);
        $new_file = 'adm/excel/files/1.xlsx';
        copy('adm/excel/blank.xlsx', $new_file);

        $xls = PHPExcel_IOFactory::load($new_file);
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();

$sheet->setCellValue("A1", 'Модель');
$sheet->setCellValue("B1", 'Количество');
$sheet->setCellValue("C1", 'Фактическая неисправность');
$sheet->setCellValue("D1", 'Завод');
$sheet->setCellValue("E1", '№ Заказа (Лот)');
$sheet->setCellValue("F1", 'Серийный №');
$sheet->setCellValue("G1", 'Наименование детали (элемента)');
$sheet->setCellValue("H1", 'Заявленная неисправность');
$sheet->setCellValue("I1", 'Проделанная работа');
$sheet->setCellValue("J1", 'Вид ремонта');
$sheet->setCellValue("K1", '№ Ремонта');

//$where = implode(',', $_POST['status']);

if ($_POST['receiver'] != 'all' && $_POST['receiver'] != 'except' && $_POST['receiver'] != '') {
$where_user = 'and `service_id` = \''.mysqli_real_escape_string($db, $_POST['receiver']).'\'';
}else if($_POST['receiver'] == 'except'){
  $where_user = 'and `service_id` != 33';
}

if (!\models\User::hasRole('admin')) {
$where_user = 'and `service_id` = \''.mysqli_real_escape_string($db, \models\User::getData('id')).'\'';
}

if ($_POST['date1'] != '' && $_POST['date2'] != '') {
$where_date = 'and DATE(app_date) BETWEEN \''.$_POST['date1'].'\' AND \''.$_POST['date2'].'\'';
}

$sql = mysqli_query($db, 'SELECT * FROM `repairs` WHERE `anrp_use` != 1 and `anrp_number` = \'\' and `app_date` != \'\' and `deleted` = 0 '.$where_user.' '.$where_date.' and `status_admin` = \'Подтвержден\';');

      while ($row = mysqli_fetch_array($sql)) {
        $content = $row;
        $content['model'] = model_info($content['model_id']);
        $content['service_info'] = service_request_info($content['service_id']);
        $content['cat_info'] = model_cat_info($content['model']['cat']);
        $content['parts_info'] = repairs_parts_info($content['id']);

     /* if ($content['start_date']) {
      $date1 = new DateTime($content['start_date']);
      $date1_ready = $date1->format('d.m.Y');
      }
      if ($content['end_date']) {
        echo $content['end_date'].'-'.$content['id'].'|';
      $date2 = DateTime::createFromFormat('d.m.Y', $content['end_date']);
      $date2_ready = $date2->format('d.m.Y');
      }
          */
      /*if ($content['start_date'] && $content['end_date']) {
      $date1 = new DateTime($content['start_date']);
      $date2 = new DateTime($content['end_date']);
      $diff = $date2->diff($date1)->format("%a");
      }   */
      //print_r($_POST['brands']);
     // echo $content['model']['brand'].'<br>';
      $problem = get_content_by_id('details_problem', $content['parts_info']['problem_id']);

        if ($problem['work_type'] == 'repair') {

      if (in_array($content['model']['brand'], $_POST['brands_1c'])) {
      $provider = get_provider_info($content['model_id']);

$sheet->setCellValue("A$glob_id", $content['model']['name']);
$sheet->setCellValue("B$glob_id", 1);
$sheet->setCellValue("C$glob_id", getDisease($content['disease']));
$sheet->setCellValue("D$glob_id", $provider['name']);
$sheet->setCellValue("E$glob_id", $provider['order_id']);
$sheet->setCellValue("F$glob_id", $content['serial']);
$sheet->setCellValue("G$glob_id", $content['parts_info']['name']);
$sheet->setCellValue("H$glob_id", $content['bugs']);
$sheet->setCellValue("I$glob_id", $problem['name']);
$sheet->setCellValue("J$glob_id", get_content_by_id('repair_type', $content['parts_info']['repair_type_id'])['name']);
$sheet->setCellValue("K$glob_id", $content['id']);
$sheet->getStyle('A'.$glob_id.':K'.$glob_id.'')->getAlignment()->setWrapText(true);


 $glob_id++;

      }

      }

      }

      $cols = range('A', 'K');
      foreach ($cols as $columnID) {
          $sheet->getColumnDimension($columnID)->setWidth('25');
      }

      $sheet->getStyle("A1:K".($glob_id-1))->applyFromArray(array(
        'borders'=>array(
          'allborders' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN,
            'color' => array('rgb' => '000000')
          )
        )
      ));
      $sheet->getStyle("B1:B".($glob_id-1))->applyFromArray([
        'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER],
      ]);
      $sheet->getStyle("E1:E".($glob_id-1))->applyFromArray([
        'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER],
      ]);
      $sheet->getStyle("K1:K".($glob_id-1))->applyFromArray([
        'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER],
      ]);
      $sheet->getStyle("A1:K1")->applyFromArray([
        'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, 'wrap' => true],
        'font' => ['bold' => true]
      ]);

        $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
        $objWriter->save($new_file);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="report_'.date('d.m.Y').'.xlsx"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($new_file));
        ob_clean();
        flush();
        readfile($new_file);

exit;

}

if ($_POST['send'] == 4) {


$glob_id = 7;
require_once './adm/excel/vendor/autoload.php';

        if (file_exists('adm/excel/files')) {
            foreach (glob('adm/excel/files/*') as $file) {
                unlink($file);
            }
        }

    $lfcr = chr(10);
        $new_file = 'adm/excel/files/1.xlsx';
        copy('adm/excel/Aftersales.xlsx', $new_file);

   //     $xls = PHPExcel_IOFactory::load($new_file);
  //      $xls->setActiveSheetIndex(0);
  //      $sheet = $xls->getActiveSheet();

//$sheet->setCellValue("D4", $_POST['date1']);
//$sheet->setCellValue("G4", $_POST['date2']);

//$where = implode(',', $_POST['status']);

$body = '
<html>
<script src="/_new-codebase/front/vendor/jquery/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>

<script  src="/_new-codebase/front/vendor/print.min.js"></script>
<script src="/_new-codebase/front/vendor/zapjs.com/download.js"></script>
<script src="/_new-codebase/front/vendor/xlsx.core.min.js" integrity="sha256-NEmjetUCzF/mJX6Ztvx6h+WG2j+A8LX85axUiZWWMK4=" crossorigin="anonymous"></script>
<script src="/_new-codebase/front/vendor/filesaver.js"></script>
<script src="/_new-codebase/front/vendor/tableexport.min.js" integrity="sha256-2mlJMabqiyPb1w0ZdzOuuyOWeHkngxrYTowNETowwtI=" crossorigin="anonymous"></script>
<script>
$( document ).ready(function() {



$.expr[":"].contains = $.expr.createPseudo(function(arg) {
    return function( elem ) {
        return $(elem).text().toUpperCase().indexOf(arg.toUpperCase()) >= 0;
    };
});

      $(\'.download\').on(\'click\', function() {
        //printJS(\'printable\', \'html\');
        download(document.getElementById("printable").outerHTML, "report.html", "text/html");
        });


      $(\'.download2\').on(\'click\', function() {

     $(\'.loader\').show();
     $.post( "/get-xls-report/", { date1: "'.$_POST["date1"].'", date2: "'.$_POST["date2"].'", table: document.getElementById("printable").outerHTML }, function(retData) {
     //window.open(\'https://crm.r97.ru/xml_tmp/report.xlsx\');
     $(\'.loader\').hide();
     window.location.href = \'https://crm.r97.ru/xml_tmp/report.xlsx\';

}  );
        });

        $(\'[data-action="download-photos"]\').on(\'click\', function() {

          $(\'.loader\').show();
          $.post( "/get-tv-report-photos/", { date1: "'.$_POST["date1"].'", date2: "'.$_POST["date2"].'", provider: "'.$_POST['plant'].'", table: document.getElementById("printable").outerHTML }, function(resp) {
          $(\'.loader\').hide();
          let data = JSON.parse(resp);
          window.location.href = data.file_url;
        }  );
             });


$(\'.zui-table\').find(\'td:not(.image)\').each(function() {
  $(this).click(function() {
    $(\'.zui-table td\').not($(this)).prop(\'contenteditable\', false);
    $(this).prop(\'contenteditable\', true);
  });
  $(this).blur(function() {
    $(this).prop(\'contenteditable\', false);
  });

});

      $(\'.del_sel\').on(\'click\', function() {

      $(\'#printable\ input[type=checkbox]:checked:visible\').each(function(index){
  //part where the magic happens
  $(this).parent().parent().parent().remove();
});

         });

      $(\'.sel_all\').on(\'click\', function() {
          var checkboxes = $(\'#printable\').find(\':checkbox:visible\');
          checkboxes.prop(\'checked\', true);
         });
      $(\'.desel_all\').on(\'click\', function() {
          var checkboxes = $(\'#printable\').find(\':checkbox:visible\');
          checkboxes.prop(\'checked\', false);
         });

$(".upload").on("click", function() {
    var file_data = $(this).parent().find(\'input[type="file"]\').prop("files")[0];
    var content_id = $(this).parent().find(\'input[type="file"]\').data(\'id\');
    var content_type = $(this).parent().find(\'input[type="file"]\').data(\'type\');
    console.log(content_id);
    console.log(content_type);
    var form_data = new FormData();
    var td = $(this).parent();
    form_data.append("file", file_data);
    form_data.append("content_id", content_id);
    form_data.append("content_type", content_type);
    $.ajax({
        url: "/editor_img.php",
        dataType: \'script\',
        cache: false,
        contentType: false,
        processData: false,
        data: form_data,
        type: \'post\',
        success: function(result){
            td.html(\'<a target="_blank" href="\'+result+\'"><img class="lazy" src="https://crm.r97.ru/resizer.php?src=\'+result+\'&h=200&w=200&zc=4&q=70" style="max-width:200px;"></a>\');
        }
    });
    return false;
});

function update_pp() {
var id = 0;
$(\'#printable\ td.pp:visible\').each(function(index){
  id++;
  $(this).text(id);
});
}

update_pp();

function change_update() {
$(\'#printable tr\').show();
if ($(\'#filter1\').val()) {
$("#printable td.col1:contains(\'" + $(\'#filter1\').val() + "\'):visible").parent().show();
$("#printable td.col1:not(:contains(\'" + $(\'#filter1\').val() + "\'))").parent().hide();
}
if ($(\'#filter2\').val()) {
$("#printable td.col2:contains(\'" + $(\'#filter2\').val() + "\'):visible").parent().show();
$("#printable td.col2:not(:contains(\'" + $(\'#filter2\').val() + "\'))").parent().hide();
}
if ($(\'#filter3\').val()) {
$("#printable td.col3:contains(\'" + $(\'#filter3\').val() + "\'):visible").parent().show();
$("#printable td.col3:not(:contains(\'" + $(\'#filter3\').val() + "\'))").parent().hide();
}
if ($(\'#filter4\').val() ) {
$("#printable td.col4:contains(\'" + $(\'#filter4\').val() + "\'):visible").parent().show();
$("#printable td.col4:not(:contains(\'" + $(\'#filter4\').val() + "\'))").parent().hide();
}
if ($(\'#filter5\').val()) {
$("#printable td.col5:contains(\'" + $(\'#filter5\').val() + "\'):visible").parent().show();
$("#printable td.col5:not(:contains(\'" + $(\'#filter5\').val() + "\'))").parent().hide();
}
if ($(\'#filter6\').val() ) {
$("#printable td.col6:contains(\'" + $(\'#filter6\').val() + "\'):visible").parent().show();
$("#printable td.col6:not(:contains(\'" + $(\'#filter6\').val() + "\'))").parent().hide();
}
if ($(\'#filter7\').val() ) {
$("#printable td.col7:contains(\'" + $(\'#filter7\').val() + "\'):visible").parent().show();
$("#printable td.col7:not(:contains(\'" + $(\'#filter7\').val() + "\'))").parent().hide();
}
if ($(\'#filter8\').val()) {
$("#printable td.col8:contains(\'" + $(\'#filter8\').val() + "\'):visible").parent().show();
$("#printable td.col8:not(:contains(\'" + $(\'#filter8\').val() + "\'))").parent().hide();
}
update_pp();
}

    $(\'#filter1\').change(function() {
    if ($(this).val() == \'\') {
        change_update();
        } else {
        $("#printable td.col1:contains(\'" + $(this).val() + "\'):visible").parent().show();
        $("#printable td.col1:not(:contains(\'" + $(this).val() + "\'))").parent().hide();
        }
        update_pp();
    });
    $(\'#filter2\').change(function() {
    if ($(this).val() == \'\') {
        change_update();
        } else {
        $("#printable td.col2:contains(\'" + $(this).val() + "\'):visible").parent().show();
        $("#printable td.col2:not(:contains(\'" + $(this).val() + "\'))").parent().hide();
        }
      update_pp();
    });
    $(\'#filter3\').change(function() {
    if ($(this).val() == \'\') {
        change_update();
        } else {
        $("#printable td.col3:contains(\'" + $(this).val() + "\'):visible").parent().show();
        $("#printable td.col3:not(:contains(\'" + $(this).val() + "\'))").parent().hide();
        }
        update_pp();
    });
    $(\'#filter4\').change(function() {
    if ($(this).val() == \'\') {
        change_update();
        } else {
        $("#printable td.col4:contains(\'" + $(this).val() + "\'):visible").parent().show();
        $("#printable td.col4:not(:contains(\'" + $(this).val() + "\'))").parent().hide();
        }
        update_pp();
    });
    $(\'#filter5\').change(function() {
        if ($(this).val() == \'\') {
        change_update();
        } else {
        $("#printable td.col5:contains(\'" + $(this).val() + "\'):visible").parent().show();
        $("#printable td.col5:not(:contains(\'" + $(this).val() + "\'))").parent().hide();
        }
        update_pp();
    });
    $(\'#filter6\').change(function() {
        if ($(this).val() == \'\') {
        change_update();
        } else {
        $("#printable td.col6:contains(\'" + $(this).val() + "\'):visible").parent().show();
        $("#printable td.col6:not(:contains(\'" + $(this).val() + "\'))").parent().hide();
        }
        update_pp();
    });
    $(\'#filter7\').change(function() {
        if ($(this).val() == \'\') {
        change_update();
        } else {
        $("#printable td.col7:contains(\'" + $(this).val() + "\'):visible").parent().show();
        $("#printable td.col7:not(:contains(\'" + $(this).val() + "\'))").parent().hide();
        }
        update_pp();
    });
    $(\'#filter8\').change(function() {
        if ($(this).val() == \'\') {
        change_update();
        } else {
        $("#printable td.col8:contains(\'" + $(this).val() + "\'):visible").parent().show();
        $("#printable td.col8:not(:contains(\'" + $(this).val() + "\'))").parent().hide();
        }
        update_pp();
    });




      });



</script>
<body  >

<style>
button.download{
height: 47px;
    background: #80bd03;
    font-size: 17px;
    color: #fff;
    border:0;
    margin-left:30px;
    cursor:pointer;
}
button.download2, button[data-action="download-photos"]{
height: 47px;
    background: #80bd03;
    font-size: 17px;
    color: #fff;
    border:0;
    margin-left:10px;
    cursor:pointer;
}
.delete_row {
 display:block !important;
}
</style>
<button class="download">Печать</button>&nbsp;&nbsp;&nbsp;<button class="download2">Сформировать XLS</button> <img src="/img/ajax-loader.gif" style="display:none;" class="loader"> <button data-action="download-photos">Скачать фото</button><br>


<table style="background: #0080001c;">
<tr>
<td colspan="8">
<a href="#" style="color:black" class="sel_all">Выделить все</a> /
<a class="desel_all" style="color:black" href="#">Снять выделение</a>
<a style="float:right;color:red" href="#" class="del_sel">Удалить выделенные</a></td>
</tr>
<tr>
<td class="header">Factory<br><input type="text" id="filter1"></td>
<td class="header">Нoменклатура<br><input type="text" id="filter2"></td>
<td class="header">SN<br><input type="text" id="filter3"></td>
<td class="header">Order number<br><input type="text" id="filter4"></td>
<td class="header">Завод сборщик<br><input type="text" id="filter9"></td>
<td class="header">Причина обращения<br><input type="text" id="filter5"></td>
<td class="header">Дефект<br><input type="text" id="filter6"></td>
<td class="header">Бракованная деталь №<br><input type="text" id="filter7"></td>
<td class="header">Позиционное обозначение<br><input type="text" id="filter8"></td>
</tr>
</table>

<table id="printable" class="zui-table">

<style>
    table { page-break-inside:auto }
    tr    { page-break-inside:avoid; page-break-after:auto }
    thead { display:table-header-group }
    tfoot { display:table-footer-group }
table {
  border-collapse: collapse;
  margin:30px;
  font-size:1vw;
}
tr {
  border-bottom: 1px solid #ccc;
}
th, td {
  text-align: left;
  padding: 4px;
  border: 1px solid #ccc;
}
.header {
text-align:center;
}
 @media print
{
    .no-print, .no-print *
    {
        display: none !important;
    }
}


</style>

<tr><td colspan="13">ИП Кулиджанов Андрей Александрович ИНН 773771305797; ОГРНИП 313774621100330; Адрес местонахождения: 115569, г. Москва, ул. Маршала Захарова, дом 20, кв. 92;  mail: ak@r97.ru; tel.: +7 (495) 136-90-75</td></tr>
<tr><td colspan="13" style="height:30px;border:0;"></td></tr>
<tr>
<td class="header" colspan="3">Период от: Period started: </td>
<td style="text-align:center;">'.date("d.m.Y", strtotime($_POST['date1'])).'</td>
<td class="header" colspan="2">До: Period over: </td>
<td style="text-align:center;">'.date("d.m.Y", strtotime($_POST['date2'])).'</td>
<td colspan="6"></td>
</tr>
<tr><td colspan="11" style="height:30px;border:0;"></td></tr>
<tr>
<td class="header">№ПП</td>
<td class="header">Factory</td>
<td class="header">Нoменклатура / Model Name</td>
<td class="header">SN</td>
<td class="header">Order number</td>
<td class="header">Завод сборщик</td>
<td class="header">Причина обращения/Reason</td>
<td class="header">Дефект</td>
<td class="header">Defect</td>
<td class="header">Бракованная деталь №/defect spare parts</td>
<td class="header">Позиционное обозначение (на плате/схеме) Name of the board</td>
<td class="header">Фото шильдика с серийным номером — Photo of the nameplate with a serial number</td>
<td class="header">Фото дефекта — Photo defect</td>
<td class="header">Фото общего вида основной платы с разъёмами — Photo Mainboard with connectors</td>
<td class="header">Фото наименования основной платы — Photo of the name of the main board</td>
<td class="header">Фото шильдика на корпусе LCD — Photo of the nameplate on the LCD</td>
<td class="header">Фото шильдика на плате LCD №1 — Photo of the nameplate on the LCD board #1</td>
<td class="header">Фото шильдика на плате LCD №2 — Photo of the nameplate on the LCD board #2</td>
<td class="header">Доп. фото/Add photo </td>
<td class="header">Решение поставщика / Supplier decision (Please choose one variant in the right column)</td>
<td class="header">Закрыто / Completed</td>
</tr>';

if ($_POST['date1'] != '' && $_POST['date2'] != '') {
$where_date = 'and DATE(app_date) BETWEEN \''.$_POST['date1'].'\' AND \''.$_POST['date2'].'\'';
}

$sql = mysqli_query($db, 'SELECT * FROM `repairs` WHERE `repair_type_id` = 4 and `cat_id` IN(48,52,53,54,55,56,57,163,164,165,166,167,168,169) and `deleted` = 0 '.$where_user.' '.$where_date.' and (`status_admin` = \'Подтвержден\' OR `status_admin` = \'Выдан\');');
//echo  'SELECT * FROM `repairs` WHERE `repair_type_id` = 4 and `cat_id` IN(48,52,53,54,55,56,57,163,164,165,166,167,168,169) and `deleted` = 0 '.$where_user.' '.$where_date.' and (`status_admin` = \'Подтвержден\' OR `status_admin` = \'Выдан\');';

 //$xls->getActiveSheet()->insertNewRowBefore(8, mysqli_num_rows($sql)-1);



      while ($row = mysqli_fetch_array($sql)) {

      $count = mysqli_fetch_array(mysqli_query($db, 'SELECT `id` FROM `repairs` WHERE `serial` = \''.mysqli_real_escape_string($db, $row['serial']).'\' order by id asc limit 1;'))['id'];

        if ($row['anrp_number'] == '' && $row['anrp_use'] == 0) {

        $content = $row;
        $content['model'] = model_info($content['model_id']);
        $content['service_info'] = service_request_info($content['service_id']);
        $content['cat_info'] = model_cat_info($content['model']['cat']);
        $content['parts_info'] = repairs_parts_info($content['id']);
        $content['master_info'] = master_info($content['master_id']);
        $content['serial_info'] = models\Serials::getSerial($content['serial'], $content['model_id']);
        $content['issue'] = @mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `issues` WHERE `id` = \''.mysqli_real_escape_string($db, $content['disease']).'\';'));

      $provider = $content['serial_info']['provider'];

      if ($_POST['plant'] == '' || $provider == $_POST['plant']) {

$repair_list = @mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `repairs_work` WHERE `repair_id` = \''.mysqli_real_escape_string($db, $content['id']).'\';'));


  if ($repair_list['problem_id'] == 60) {
    continue;
  }

/*$sheet->setCellValue("B$glob_id", $provider['name']);
$sheet->setCellValue("C$glob_id", $content['model']['name']);
$sheet->setCellValue("D$glob_id", $content['serial']);
$sheet->setCellValue("E$glob_id", $provider['order_id']);
$sheet->setCellValue("F$glob_id", $content['bugs']);
$sheet->setCellValue("G$glob_id", $content['issue']['name']);
$sheet->setCellValue("H$glob_id", $repair_list['name']);
$sheet->setCellValue("I$glob_id", $repair_list['position']);     */


$bug = get_last_photo($content['id'], 3);
$shildik = get_last_photo($content['id'], 2);
$shildik_serial = get_last_photo($content['id'], 1);
$shildik_main = get_last_photo($content['id'], 4);
$shildik_lookup = get_last_photo($content['id'], 5);
$LCDBoardName1 = get_last_photo($content['id'], 9);
$LCDBoardName2 = get_last_photo($content['id'], 10);
if ($bug) {
/*$objDrawing = new PHPExcel_Worksheet_Drawing();
$objDrawing->setPath('/var/www/service.harper.ru/data/www/service.harper.ru'.str_replace('http://service.harper.ru', '', $bug));
$objDrawing->setCoordinates("J$glob_id");
$objDrawing->setWorksheet($xls->getActiveSheet());
$objDrawing->setResizeProportional(false);
$objDrawing->setHeight(20);
$objDrawing->setWidth(130);*/
//$sheet->setCellValue("J$glob_id", '/var/www/service.harper.ru/data/www/service.harper.ru'.str_replace('http://service.harper.ru', '', $bug));
}

if ($shildik) {
/*$objDrawing2 = new PHPExcel_Worksheet_Drawing();
$objDrawing2->setPath('/var/www/service.harper.ru/data/www/service.harper.ru'.str_replace('http://service.harper.ru', '', $shildik));
$objDrawing2->setCoordinates("K$glob_id");
$objDrawing2->setWorksheet($xls->getActiveSheet());
$objDrawing2->setResizeProportional(false);
$objDrawing2->setHeight(20);
$objDrawing2->setWidth(110);   */
//$sheet->setCellValue("K$glob_id", '/var/www/service.harper.ru/data/www/service.harper.ru'.str_replace('http://service.harper.ru', '', $bug));
}




$sql2 = mysqli_query($db, 'SELECT * FROM `photos` where `repair_id` = '.$content['id']);
      if (mysqli_num_rows($sql2) != false) {
      while ($row2 = mysqli_fetch_array($sql2)) {

      $add_photo = $row2['url'];

/*$objDrawing3 = new PHPExcel_Worksheet_Drawing();
$objDrawing3->setPath('/var/www/service.harper.ru/data/www/service.harper.ru'.str_replace('http://service.harper.ru', '', $row2['url']));
$objDrawing3->setCoordinates("L$glob_id");
$objDrawing3->setWorksheet($xls->getActiveSheet());
$objDrawing3->setResizeProportional(false);
$objDrawing3->setHeight(20);
$objDrawing3->setWidth(110); */

      }
      }

$body .= '<tr class="pars_pls" data-repair-id="'.$content['id'].'">
<td class="pp" style="text-align:center"></td>
<td class="col1" style="position:relative;"><span style="position:absolute;left:3px;top:3px;color:red;cursor:pointer" class="delete_row no-print"><input type="checkbox"></span>'.$provider.'</td>
<td class="col2">'.$content['model']['name'].'</td>
<td class="col3">'.$content['serial'].'</td>
<td class="col4" style="text-align:center">'.$content['serial_info']['order'].'</td>
<td class="col9">'.$content['serial_info']['plant'].'</td>
<td class="col5">'.$content['bugs'].'</td>
<td class="col6">'.$content['issue']['name'].'</td>
<td class="col6">'.$content['issue']['eng'].'</td>
<td class="col7">'.$repair_list['name'].'</td>
<td class="col8">'.$repair_list['position'].'</td>';

if ($shildik_serial) {
$body .= '<td class="image image3" image_url="'.$shildik_serial.'"><a target="_blank" href="'.$shildik_serial.'"><img class="lazy" src="https://crm.r97.ru/resizer.php?src='.$shildik_serial.'&h=200&w=200&zc=4&q=70" style="max-width:200px;"></a></td>';
} else {
$body .= '<td class="image" style="text-align:center"><input style="max-width:120px;" data-id="'.$content['id'].'" data-type="1" type="file" name="sortpic" /><br><a style="color:#000;font-weight:bold;" href="#" class="upload">Загрузить</a></td>';
}

if ($bug) {
$body .= '<td class="image image1" image_url="'.$bug.'"><a target="_blank" href="'.$bug.'"><img class="lazy" src="https://crm.r97.ru/resizer.php?src='.$bug.'&h=200&w=200&zc=4&q=70" style="max-width:200px;"></a></td>';
} else {
$body .= '<td class="image" style="text-align:center"><input data-id="'.$content['id'].'" data-type="3" style="max-width:120px;" type="file" name="sortpic" /><br><a style="color:#000;font-weight:bold;" href="#" class="upload">Загрузить</a></td>';
}


if ($shildik_lookup) {
$body .= '<td class="image image5" image_url="'.$shildik_lookup.'"><a target="_blank" href="'.$shildik_lookup.'"><img class="lazy" src="https://crm.r97.ru/resizer.php?src='.$shildik_lookup.'&h=200&w=200&zc=4&q=70" style="max-width:200px;"></a></td>';
} else {
$body .= '<td class="image" style="text-align:center"><input style="max-width:120px;" data-id="'.$content['id'].'" data-type="5" type="file" name="sortpic" /><br><a style="color:#000;font-weight:bold;" href="#" class="upload">Загрузить</a></td>';
}

if ($shildik_main) {
$body .= '<td class="image image4" image_url="'.$shildik_main.'"><a target="_blank" href="'.$shildik_main.'"><img class="lazy" src="https://crm.r97.ru/resizer.php?src='.$shildik_main.'&h=200&w=200&zc=4&q=70" style="max-width:200px;"></a></td>';
} else {
$body .= '<td class="image" style="text-align:center"><input style="max-width:120px;" data-id="'.$content['id'].'" data-type="4" type="file" name="sortpic" /><br><a style="color:#000;font-weight:bold;" href="#" class="upload">Загрузить</a></td>';
}

if ($shildik) {
$body .= '<td class="image image2" image_url="'.$shildik.'"><a target="_blank" href="'.$shildik.'"><img class="lazy" src="https://crm.r97.ru/resizer.php?src='.$shildik.'&h=200&w=200&zc=4&q=70" style="max-width:200px;"></a></td>';
} else {
$body .= '<td class="image" style="text-align:center"><input style="max-width:120px;" data-id="'.$content['id'].'" data-type="2" type="file" name="sortpic" /><br><a style="color:#000;font-weight:bold;" href="#" class="upload">Загрузить</a></td>';
}

if ($LCDBoardName1) {
  $body .= '<td class="image image5" image_url="'.$LCDBoardName1.'"><a target="_blank" href="'.$LCDBoardName1.'"><img class="lazy" src="https://crm.r97.ru/resizer.php?src='.$LCDBoardName1.'&h=200&w=200&zc=4&q=70" style="max-width:200px;"></a></td>';
  } else {
  $body .= '<td class="image" style="text-align:center"><input style="max-width:120px;" data-id="'.$content['id'].'" data-type="9" type="file" name="sortpic" /><br><a style="color:#000;font-weight:bold;" href="#" class="upload">Загрузить</a></td>';
  }

  if ($LCDBoardName2) {
    $body .= '<td class="image image5" image_url="'.$LCDBoardName2.'"><a target="_blank" href="'.$LCDBoardName2.'"><img class="lazy" src="https://crm.r97.ru/resizer.php?src='.$LCDBoardName2.'&h=200&w=200&zc=4&q=70" style="max-width:200px;"></a></td>';
    } else {
    $body .= '<td class="image" style="text-align:center"><input style="max-width:120px;" data-id="'.$content['id'].'" data-type="10" type="file" name="sortpic" /><br><a style="color:#000;font-weight:bold;" href="#" class="upload">Загрузить</a></td>';
    }
  
if ($add_photo) {
$body .= '<td class="image image6" image_url="'.$add_photo.'"><a target="_blank" href="'.$add_photo.'"><img class="lazy" src="https://crm.r97.ru/resizer.php?src='.$add_photo.'&h=200&w=200&zc=4&q=70" style="max-width:200px;"></a></td>';
} else {
$body .= '<td class="image" style="text-align:center"><input style="max-width:120px;" type="file" data-id="'.$content['id'].'" data-type="0" name="sortpic" /><br><a style="color:#000;font-weight:bold;" href="#" class="upload">Загрузить</a></td>';
}

$body .= '<td class="col10"></td><td class="col11"></td></tr>';

unset($content['parts_pics']);
unset($shildik);
unset($bug);
unset($add_photo);
/**/


/*$sheet->setCellValue("B$glob_id", 1);
$sheet->setCellValue("G$glob_id", $content['parts_info']['name']);
$sheet->setCellValue("H$glob_id", $problem);
$sheet->setCellValue("I$glob_id", get_content_by_id('repair_type', $content['parts_info']['repair_type'])['name']);
$sheet->setCellValue("J$glob_id", $content['id']); */

 $glob_id++;
    }
     }
      }

//   header("Content-type: application/octet-stream");
 //     header("Content-Disposition: attachment; filename=\"report.html\"");
      echo $body.'<tr><td colspan="12" style="height:30px;border:0;"></td></tr><tr><td colspan="12">Акт составлен в 3-х экземплярах/ Report is made in 3 copies</td></tr><tr><td colspan="12" style="height:30px;border:0;"></td></tr><tr><td colspan="12">Signature ______________________________</td></tr>
<style>
.delete_row {
 display:none;
}
</style>
</table>


</body></html>';

        //$objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
        //$objWriter->save($new_file);

       /* header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="report_'.date('d.m.Y').'.xlsx"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($new_file));
        ob_clean();
        flush();
        readfile($new_file);  */

exit;

}

if ($_POST['send'] == 3) {

$glob_id = 13;
$count_id = 1;

require_once './adm/excel/vendor/autoload.php';

        if (file_exists('adm/excel/files')) {
            foreach (glob('adm/excel/files/*') as $file) {
                unlink($file);
            }
        }

    $lfcr = chr(10);
        $new_file = 'adm/excel/files/1.xlsx';
        copy('adm/excel/parts.xlsx', $new_file);

        $xls = PHPExcel_IOFactory::load($new_file);
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();


$where = '"Подтвержден", "Выдан"';
$payed = 1;

if ($_POST['receiver'] != 'all' && $_POST['receiver'] != '') {

$where_user = 'and `service_id` = "'.mysqli_real_escape_string($db, $_POST['receiver']).'"';

}else if($_POST['receiver'] == 'except'){
  $where_user = 'and `service_id` != 33';
}

if (!\models\User::hasRole('admin')) {
$where_user = 'and `service_id` = \''.mysqli_real_escape_string($db, \models\User::getData('id')).'\'';
}

if ($_POST['date1'] != '' && $_POST['date2'] != '') {
$where_date = 'and `approve_date` BETWEEN "'.$_POST['date1'].'" AND "'.$_POST['date2'].'"';
}

$sql = mysqli_query($db, 'SELECT * FROM `repairs` WHERE `deleted` = 0 '.$where_user.' '.$where_model.' '.$where_client_id.' '.$where_date.' and `status_admin` in ('.$where.') '.$where_new.';');
$sql2 = mysqli_query($db, 'SELECT * FROM `repairs` WHERE `deleted` = 0 '.$where_user.' '.$where_model.' '.$where_client_id.' '.$where_date.' and `status_admin` in ('.$where.') '.$where_new.';');

$check_repairs_count = mysqli_num_rows($sql);

$from_date = DateTime::createFromFormat('Y-m-d', $_POST['date1']);
$to_date = DateTime::createFromFormat('Y-m-d', $_POST['date2']);


$sheet->setCellValue("B8", 'с '.$from_date->format('d').' '.$_monthsList2[$from_date->format('m')].' '.$from_date->format('Y').' г. по '.$to_date->format('d').' '.$_monthsList2[$to_date->format('m')].' '.$to_date->format('Y').' г. следующие запасные части и материалы:');
$sheet->setCellValue("H5", date('d').' '.$_monthsList2[date('m')].' '.date('Y').' г.');

  /* ГОТОВИМ */
  while ($row2 = mysqli_fetch_array($sql2)) {
    $content2 = $row2;
    $content2['model'] = model_info($content2['model_id']);
    $content2['parts_info'] = repairs_parts_info_array($content2['id']);
    if ($row2['return_id'] == 0 || check_returns_pls($row2['return_id'])) {
      if ($content2['parts_info']['0']['ordered_flag'] == 1 && $content2['parts_info']['0']['qty'] > 0 && in_array($content2['model']['brand'], $_POST['brands']) && in_array($content2['parts_info']['0']['repair_type_id'], [6, 7])) {
        $part_info2 = Parts::getPartByID2($content2['parts_info'][0]['part_id']);
        if ($part_info2) {
            $partsCnt = Balance::count($content2['parts_info'][0]['part_id'], 1);
          $counter_parts[$part_info2['id']]['parts'] += $content2['parts_info'][0]['qty'];
          $counter_parts[$part_info2['id']]['count'] = $partsCnt;
          $counter_parts2[$part_info2['id']]['parts'] += $content2['parts_info'][0]['qty'];
          $counter_parts2[$part_info2['id']]['count'] = $partsCnt;
        }
      }
    }
  }

 
/* ГОТОВИМ */

      while ($row = mysqli_fetch_array($sql)) {

     if ($payed == 1) {

     if ($row['app_date']) {
     $app = DateTime::createFromFormat('Y.m.d', $row['app_date']);
     $year = $app->format('Y');
     $month = $app->format('n');
     }

 //       $sheet->setCellValue("B4", 'SELECT * FROM `repairs` WHERE `deleted` = 0 '.$where_user.' '.$where_model.' '.$where_client_id.' '.$where_date.' and `status_admin` in ('.$where.') '.$where_new.';');

        $content = $row;
        $content['model'] = model_info($content['model_id']);
        $content['parts_info'] = repairs_parts_info_array($content['id']);


      //print_r($_POST['brands']);
      //echo $content['model']['brand'].'<br>';

      if ($row['return_id'] == 0 || check_returns_pls($row['return_id'])) {


      if (in_array($content['model']['brand'], $_POST['brands'])) {
        
 $part_info = Parts::getPartByID2($content['parts_info'][0]['part_id']);

      if ($part_info && $content['parts_info']['0']['qty'] > 0 && $content['parts_info']['0']['ordered_flag'] == 1 && ($content['parts_info']['0']['repair_type_id'] == 7 || $content['parts_info']['0']['repair_type_id'] == 6)) {



if ($glob_id != 13) {
$xls->getActiveSheet()->insertNewRowBefore($glob_id, 1);
}

$counter_plus += $content['parts_info']['0']['qty'];

$part_info3['count'] = $counter_parts[$part_info['id']]['count']+$counter_parts[$part_info['id']]['parts'];
$counter_parts2[$part_info['id']]['parts'] = $counter_parts2[$part_info['id']]['parts']-$content['parts_info']['0']['qty'];
$part_info3['left'] = $counter_parts2[$part_info['id']]['count']+$counter_parts2[$part_info['id']]['parts'];

$app_date = DateTime::createFromFormat('Y.m.d', $content['app_date']);
$app_date_ready = $app_date->format('d.m.Y');

$sheet->setCellValue("A$glob_id", $count_id);
$sheet->setCellValue("B$glob_id", 'Гарантийный ремонт');
$sheet->setCellValue("C$glob_id", $content['id'].' от '.$app_date_ready);
$sheet->setCellValue("D$glob_id", $part_info['name_1s']);
$sheet->setCellValue("E$glob_id", 'шт.');
$sheet->setCellValue("F$glob_id", $part_info3['count']);
$sheet->setCellValue("G$glob_id", $content['parts_info']['0']['qty']);
$sheet->setCellValue("H$glob_id", $part_info3['left']);

$counter_parts[$part_info['id']]['parts'] = $counter_parts[$part_info['id']]['parts']-$content['parts_info']['0']['qty'];

                                 //$content['parts_info']['0']['position']

/*$sheet->setCellValue("T$glob_id", $content['parts_info']['0']['price']);
$sheet->setCellValue("U$glob_id", $content['parts_info']['summ']);
$sheet->setCellValue("V$glob_id", (($row['status_admin'] != 'Запрос на выезд' && $row['onway'] == 1) ? get_price_transfer($content['model']['cat'], $row['onway_type'], $content['service_id']) : '0'));
$sheet->setCellValue("W$glob_id", $content['total_price']);
$sheet->setCellValue("X$glob_id", ($content['total_price'] + (($row['status_admin'] != 'Запрос на выезд' && $row['onway'] == 1) ? get_price_transfer($content['model']['cat'], $row['onway_type'], $content['service_id']) : '0') + $content['parts_info']['summ']));
$sheet->setCellValue("Y$glob_id", $content['client']);
$sheet->setCellValue("Z$glob_id", $content['phone']);
$sheet->setCellValue("AA$glob_id", $content['date']);
$sheet->setCellValue("AB$glob_id", $date1_ready);
$sheet->setCellValue("AC$glob_id", $date2_ready);
$sheet->setCellValue("AD$glob_id", $diff);
                                              */
       /* if ($content['client_type'] == 2) {
            $client = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `clients` WHERE `id` = \''.mysqli_real_escape_string($db, $content['client_id']).'\' LIMIT 1;'));
            $sheet->setCellValue("AE$glob_id", $client['name']);

        } else if ($content['client_type'] == 1) {
            $sheet->setCellValue("AE$glob_id", $content['client']);
        }   */

     // echo 1;
      $glob_id++;
      $count_id++;



      }

      }

      }



     }



      }

$total = $glob_id;
$named = $glob_id+2;

$sheet->setCellValue("G$total", $counter_plus);
$sheet->setCellValue("H$total", '');
$sheet->setCellValue("A$named", '1. Общее количество запасных частей и материалов, использованных для выполнения гарантийного ремонта, составила '.num2str22($counter_plus).' ('.$counter_plus.') шт.');

if ($_POST['status_33_1'] == 1 || $_POST['status_33_2'] == 1) {
      $provider = get_provider_info($content['model_id']);

$sheet->setCellValue("A$glob_id", 'Итого: '.$ids_count.'');
$sheet->setCellValue("B$glob_id", '');
$sheet->setCellValue("C$glob_id", '');
$sheet->setCellValue("D$glob_id", '');
$sheet->setCellValue("E$glob_id", '');
$sheet->setCellValue("F$glob_id", '');
$sheet->setCellValue("G$glob_id", 'Сумма итого: '.$sum_count.'');


      }


/*$xls->getDefaultStyle()->getAlignment()->setWrapText(true);
foreach ($xls->getWorksheetIterator() as $worksheet) {

    $xls->setActiveSheetIndex($xls->getIndex($worksheet));

    $sheet = $xls->getActiveSheet();
    $cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(true);

    foreach ($cellIterator as $cell) {
        $sheet->getColumnDimension($cell->getColumn())->setWidth(22);
            }
}   */


        $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
        $objWriter->save($new_file);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="parts-report_'.$from_date->format('d.m.Y').'-'.$to_date->format('d.m.Y').'.xlsx');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($new_file));
        ob_clean();
        flush();
        readfile($new_file);

exit;

}

function content_list() {
  global $db;

$dated = ((date("d") < 5) ? date("Y.m") : date("Y.m"));

$sql = mysqli_query($db, 'SELECT * FROM `repairs` WHERE `deleted` = 0 and `status_admin` = \'Подтвержден\' and  `app_date` NOT REGEXP \''.$dated.'\' order by `id` DESC;');
//echo 'SELECT * FROM `repairs` WHERE `deleted` = 0 and `status_admin` = \'Подтвержден\' and  `app_date` NOT REGEXP \''.$dated.'\' order by `id` DESC;';

while ($row = mysqli_fetch_array($sql)) {
      //$info = get_request_info($row['id']);
      //$city = get_city($info['city']);

      if($row['app_date']) {
      $exp = explode('.', $row['app_date']);
      $app[$exp['0']][$exp[1]] = '';
      }


      }
    // print_r($app);
     //array_reverse($app,true);;
    // print_r($app);
    $ids = 1;


      foreach ($app as $app_count) {
      $counter += count($app_count, COUNT_RECURSIVE);
      }


      foreach ($app as $year => $val) {
      $year_work = $val;

      foreach ($year_work as $month => $value) {

     /* $type1 = create_or_get_payment_id(\models\User::getData('id'), $year, $month, 1); //акт
      $type2 = create_or_get_payment_id(\models\User::getData('id'), $year, $month, 2); //счет   */
     //print_r($type1);
      //$block_style = ($row['block'] == 0) ? '' : 'style="background: rgba(255, 71, 71, 0.13);"';
      /*if ($type1['status'] == 0) {
      $status = '<td style="background: rgba(255, 71, 71, 0.13);">Не оплачено</td>';
      } else {
      $status = '<td style="background: rgba(116, 220, 116, 0.13);">Оплачено</td>';
      }  */
      /*if ($type2['status'] == 0) {
      $status2 = '<td style="background: rgba(255, 71, 71, 0.13);">Не оплачено</td>';
      } else {
      $status2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оплачено</td>';
      }
      if ($type1['original'] == 0) {
      $original = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал акта не получен</td>';
      } else {
      $original = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал акта получен</td>';
      }
      if ($type2['original'] == 0) {
      $original2 = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал счета не получен</td>';
      } else {
      $original2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал счета получен</td>';
      }
      $summ = get_service_summ(\models\User::getData('id'), $month, $year);
      $content_list['total'] += $summ;  */
      $content_list['body'] .= '<tr>
      <td >'.$year.'.'.$month.'</td>
      <td style="text-align:center"><a class="dwn" data-href="/get-agent/'.$year.'/'.$month.'/'.$counter.'/HARPER,OLTO/" href="/get-agent/'.$year.'/'.$month.'/'.$counter.'/HARPER,OLTO/">Отчет Агента</a></td>
      <td style="text-align:center"><a class="dwn" data-href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/TESLER/" href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/TESLER/">Отчет Агента</a></td>
      </tr>';
      $counter--;
      }
     }


    return $content_list;
}

$content = content_list();

function services_list2() {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `users` where `status_id` = 1 and `role_id` = 3;');
      while ($row = mysqli_fetch_array($sql)) {
       $info = get_request_info_by_user_id($row['id']);
       if ($info['name'] != '') {
       if ($_GET['service_id'] == $row['id']) {
       $content .= '<option value="'.$row['id'].'" selected>'.$info['name'].'</option>';
       } else {
       $content .= '<option value="'.$row['id'].'" >'.$info['name'].'</option>';
       }

       }

      }
    return $content;
}

function services_list2_plant() {
  global $db;
  $content = '';
$sql = mysqli_query($db, 'SELECT * FROM `providers` ORDER BY `name`;');
      while ($row = mysqli_fetch_array($sql)) {
       if ($_GET['service_id'] == $row['id']) {
       $content .= '<option value="'.$row['name'].'" selected>'.$row['name'].'</option>';
       } else {
       $content .= '<option value="'.$row['name'].'" >'.$row['name'].'</option>';
       }

      }
    return $content;
}

function clients($cat_id = 0) {
  global $db;

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

function brands($id = '') {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `brands` WHERE `is_deleted` = 0;');
      while ($row = mysqli_fetch_array($sql)) {
      if (\models\User::getData('id') == 33 || \models\User::getData('id') == 1) {
      $content .= '<li><label><input type="checkbox" name="brands[]" value="'.$row['name'].'" />'.$row['name'].'</label></li>';
      } else {
      $check_service = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `brands_users` WHERE `brand_id` = \''.mysqli_real_escape_string($db, $row['id']).'\' and `service_id` = \''.mysqli_real_escape_string($db, \models\User::getData('id')).'\' and service = 1 LIMIT 1;'));
      if ($check_service['COUNT(*)'] > 0) {
      $content .= '<li><label><input type="checkbox" name="brands[]" value="'.$row['name'].'" />'.$row['name'].'</label></li>';
      }
      }

      }
    return $content;
}

function get_last_photo($repair_id, $type) {
  global $db;

$sql = mysqli_query($db, 'SELECT * FROM `repairs_photo` where `photo_id` = '.$type.' and `repair_id` = '.$repair_id.' order by id desc LIMIT 1');
    if (mysqli_num_rows($sql) != false) {
      while ($row = mysqli_fetch_array($sql)) {
       $content = ($row['url_do'] != '') ? $row['url_do'] : $row['url'];
      }
      }
    return $content;
}

function check_payed_repair($year, $month, $service_id) {
  global $db;

//  echo $year.'|'.$month.'|'.$service_id.'<br>';

$sql = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `pay_billing` where `service_id` = \''.$service_id.'\' and `month` = \''.$month.'\' and `year` = \''.$year.'\' and (`type` = 2 OR `type` = 4) and `status` = 1 and `original` = 1 ;'));
//ho  'SELECT COUNT(*) FROM `pay_billing` where `service_id` = \''.$service_id.'\' and `month` = \''.$month.'\' and `year` = \''.$year.'\' and (`type` = 2 OR `type` = 4) and `status` = 1 and `original` = 1 ;';
if ($sql['COUNT(*)'] > 0) {
return true;
} else {
return false;
}


}

function getDisease($id){
  global $db;
  if(empty($id)){
    return '';
  }
  $row = mysqli_fetch_assoc(mysqli_query($db, 'SELECT `name` FROM `issues` WHERE `id` = '.$id));
  return (!empty($row['name'])) ? $row['name'] : '';
}


function models($cat_id) {
  global $db;
$content = array();
$sql = mysqli_query($db, 'SELECT * FROM `models` WHERE `is_deleted` = 0 order by `name` ASC;');
      while ($row = mysqli_fetch_array($sql)) {

      if (\models\User::getData('id') == 33 || \models\User::getData('id') == 1) {
        if ($cat_id == $row['id']) {
        $content .= '<option selected value="'.$row['id'].'">'.$row['name'].'</option>';
        } else {
         $content .= '<option value="'.$row['id'].'">'.$row['name'].'</option>';
        }
      } else {
        if ($cat_id == $row['id']) {
        $content .= '<option selected value="'.$row['id'].'">'.$row['name'].'</option>';
        } else if ($row['brand'] == 'HARPER' || $row['brand'] == 'TESLER' || $row['brand'] == 'OLTO' || $row['brand'] == 'SKYLINE' || $row['brand'] == 'NESONS') {
         $content .= '<option value="'.$row['id'].'">'.$row['name'].'</option>';
        }
      }

      }
    return $content;
}

function brands2($id = '') {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `brands` WHERE `is_deleted` = 0;');
      while ($row = mysqli_fetch_array($sql)) {
      if ($row['id'] != 4) {
      $content .= '<li><label><input type="checkbox" name="brands2[]" value="'.$row['name'].'" checked/>'.$row['name'].'</label></li>';
      } else {
      $content .= '<li><label><input type="checkbox" name="brands2[]" value="'.$row['name'].'" />'.$row['name'].'</label></li>';
      }
      }
    return $content;
}

function brands3($id = '') {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `brands` WHERE `is_deleted` = 0 order by name ASC;');
      while ($row = mysqli_fetch_array($sql)) {
      $content .= '<li><label><input type="checkbox" name="brands_1c[]" value="'.$row['name'].'" />'.$row['name'].'</label></li>';
      }
    return $content;
}

function get_provider_info($model_id) {
  global $db;
  $content = ['name' => '', 'order_id' => ''];
$sql = mysqli_query($db, 'SELECT * FROM `serials` WHERE `model_id` = '.$model_id);
      if (mysqli_num_rows($sql) != false) {
      while ($row = mysqli_fetch_array($sql)) {


       $model = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `models` WHERE `id` = \''.mysqli_real_escape_string($db, $model_id).'\' LIMIT 1;'));
       $exp_provider = explode('|', $model['provider']);
       foreach ($exp_provider as $provider) {
         if ($row['provider_id'] == $provider) {

            $content['name'] = privider_name($provider);
            $content['order_id'] = $row['order'];
            break;
         }


       }

      }

      return $content;

      }



}

function privider_name($id) {
  global $db;

$sql = mysqli_query($db, 'SELECT * FROM `providers` where `id` = '.$id);
      while ($row = mysqli_fetch_array($sql)) {
      $content = $row['name'];
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
 <link rel="stylesheet" href="/_new-codebase/front/vendor/select2/4.0.4/select2.min.css" />

<script >
// Таблица
$(document).ready(function() {
    $('#table_content').dataTable({
      stateSave:false,
      "dom": '<"top"flp<"clear">>rt<"bottom"ip<"clear">>', 
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
.open-brands{
  cursor: pointer;
}

.open-brands_closed::before{
  content: '\1F53B';
  margin-right: 6px;
}

.open-brands_open::before{
  content: '\1F53A';
  margin-right: 6px;
}
</style>
  <script >
  $(document).ready(
    function()		{
    $('#select_model').select2();
    $('#select_model2').select2();
     $('#select_model3').select2();
 

    $(document).on('change', 'input[name="brands2[]"]', function() {
          $( ".dwn" ).each(function() {
            $( this ).attr('href', $( this ).data('href')+$('input[name="brands2[]"]:checked').map(function() { return this.value;  }).get().join(',')+'/');
          });
    });

var checked = false;
$('.select_all').click(function() {
    if (checked) {

    } else {
        $('input[name="brands[]"]:checkbox').each(function() {
            $(this).prop('checked', true).trigger('refresh');
        });
        checked = true;
    }
    return false;
});


$('.deselect_all').click(function() {
    if (checked) {
        $('input[name="brands[]"]:checkbox').each(function() {
            $(this).prop('checked', false).trigger('refresh');
        });
        checked = false;
    }
    return false;
});


$('.open-brands').on('click', function(){

  if(this.classList.contains('open-brands_closed')){
    this.classList.remove('open-brands_closed');
    this.classList.add('open-brands_open');
    $('#brands-table').slideDown();
  }else{
    this.classList.add('open-brands_closed');
    this.classList.remove('open-brands_open');
    $('#brands-table').slideUp();
  }


});

    }  );
  </script>

    <!-- New codebase -->
    <link href="/_new-codebase/front/vendor/air-datepicker/css/datepicker.min.css" rel="stylesheet" />
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
           <h2>Генерация отчета</h2>

  <form id="send" method="POST">
   <div class="adm-form" style="padding-top:0;">

             <div class="item">
              <div class="level" style="display: block;text-align: center;width: 100%;">Дата подтверждения ремонта:</div>
              <div class="value" style="display:block;">
              <span id="two-inputs"><input type="text" data-air-datepicker autocomplete="off" data-range="true" data-multiple-dates-separator=" - "  id="date-range200" name="date" style="    width: 250px; text-align: center;  height: 30px;padding:5px;" value=""/></span>

              </div>
            </div>
               <br> <br>
               <?php if (\models\User::hasRole('admin')) { ?>
                <div class="item">
              <div class="level" style="display: block;text-align: center;width: 100%;">Фильтрация по СЦ:</div>
              <div class="value" style="display:block;">
              <select name="receiver" class="nomenu" id="select_model">
               <option selected value="">Выберите СЦ</option>
               <option value="all" style="background:#DDF4DD">Все</option>
               <option value="except" style="background:#DDF4DD">Все, кроме ИП Кулиджанов</option>
               <?=services_list2();?>
              </select>
              </div>
            </div>
             <?php } ?> <br><br>
              <h3 style="margin-top:20px;">Статусы ремонта:</h3>
             <div class="adm-finish" style="padding-top:10px;">
            <ul style="padding-top:0;">
              <li><label><input type="checkbox" name="status_id[]" value="1" checked/>Гарантийный</label></li>
              <li><label><input type="checkbox" name="status_id[]" value="5" checked/>Условно-гарантийный</label></li>
              <li><label><input type="checkbox" name="status_id[]" value="6" checked/>Платный</label></li>
</ul>
            </div><br><br>
            <h3>Статусы:</h3>
             <div class="adm-finish" style="padding-top:10px;">
            <ul>
            <li><label><input type="checkbox" name="status[]" value="''" />Без статуса</label></li>
              <?php
$sqlSt = mysqli_query($db, 'SELECT DISTINCT `status_admin` FROM `repairs` WHERE `status_admin` != "" ORDER BY `status_admin`;');
while ($row = mysqli_fetch_assoc($sqlSt)) {
  $ch = (in_array($row['status_admin'], ['Подтвержден', 'Выдан'])) ? 'checked' : '';
echo '<li style="padding: 6px"><label><input type="checkbox" name="status[]" '.$ch.' value="\''.$row['status_admin'].'\'" />'.$row['status_admin'].'</label></li>';
}
?>
            </ul>
            </div>
            <?php if (\models\User::getData('id') == 33) { ?>
             <div class="adm-finish" style="padding-top:10px;">
            <ul>
              <li><label><input type="checkbox" name="status_33_1" value="1" />Утилизирован</label></li>
              <li><label><input type="checkbox" name="status_33_2" value="1" />Уценка</label></li>
               <li><label><input type="checkbox" name="no_master" value="1" />Без мастера</label></li>
</ul>
            </div>

             <?php } ?>   <br>

            <br>
            <h3>Бренды:</h3>
             <div class="adm-finish" style="padding-top:10px;position:realtive;">   <span><a href="#" class="select_all">Выбрать все</a> \ <a class="deselect_all" href="#">Снять все</a></span>

            <ul>
              <?=brands();?>  </ul>
            </div>
                     <br>
                    <div class="item">
              <div class="level">Модель:</div>
              <div class="value">

              <select name="model_id" class="select2 nomenu">
               <option value="">Выберите модель</option>
                <?=models($content['model_id']);?>
              </select>
              </div>
            </div>

                    <div class="item">
              <div class="level">Принят от:</div>
              <div class="value">

              <select name="client_id" class="select2 nomenu">
                <option value="">Выберите клиента</option><?=clients();?>
              </select>
              </div>
            </div>



                <div class="adm-finish">
            <div class="save">
              <input type="hidden" name="send" value="1" />
              <button type="submit" >Генерировать</button>
            </div>
            </div>
        </div>

      </form>
<?php if (\models\User::hasRole('admin', 'slave-admin')) { ?>
<br><br><br>
<hr>
   <h2>Отчет по списанию запчастей</h2>
  <form id="send2" method="POST">
   <div class="adm-form" style="padding-top:0;">

             <div class="item">
              <div class="level" style="display: block;text-align: center;width: 100%;">Дата подтверждения ремонта:</div>
              <div class="value" style="display:block;">
              <span id="two-inputs3"><input type="text" data-air-datepicker autocomplete="off" data-range="true" data-multiple-dates-separator=" - " id="date-range300" name="date" style="    width: 250px; text-align: center;   height: 30px;padding:5px;" value=""/></span>

              </div>
            </div>
               <br> <br>
                <div class="item">
              <div class="level" style="display: block;text-align: center;width: 100%;">Фильтрация по СЦ:</div>
              <div class="value" style="display:block;">
              <select name="receiver" class="nomenu" id="select_model2">
               <option selected value="">Выберите СЦ</option>
               <option value="all" style="background:#DDF4DD">Все</option>
               <?=services_list2();?>
              </select>
              </div>
            </div>
           <br><br>

  <br>

            <h3>Бренды:</h3>
             <div class="adm-finish" style="padding-top:10px;position:realtive;">   <span><a href="#" class="select_all">Выбрать все</a> \ <a class="deselect_all" href="#">Снять все</a></span>

         <ul>
              <li><label><input type="checkbox" name="brands[]" value="HARPER" checked/>HARPER</label></li><li><label><input type="checkbox" name="brands[]" value="OLTO" checked/>OLTO</label></li><li><label><input type="checkbox" name="brands[]" value="SKYLINE" checked/>SKYLINE</label></li> </ul>
            </div>




                <div class="adm-finish">
            <div class="save">
              <input type="hidden" name="send" value="3" />
              <button type="submit" >Генерировать</button>
            </div>
            </div>
           <br>
          <hr>
           <br>
        </div>

      </form>

            <h3 class="open-brands open-brands_closed">Бренды</h3>

            <section style="display: none" id="brands-table">
            <!-- <div class="adm-finish" style="padding-top:10px;">
            <ul>
              <?=brands2();?>
            </ul>
            </div><br> -->
  <table id="table_content" class="display" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th align="left">Период</th>
                <th>HARPER\OLTO</th>
                <th>TESLER</th>
            </tr>
        </thead>

        <tbody>
        <?=$content['body'];?>
        </tbody>
</table>
</section>


<br><br><br> <hr>

            <h3>1C отчет:</h3>
              <form id="send" method="POST">
   <div class="adm-form" style="padding-top:0;">
              <div class="item">
              <div class="level" style="display: block;text-align: center;width: 100%;">Дата подтверждения ремонта:</div>
              <div class="value" style="display:block;">
              <span id="two-inputs2"><input type="text" data-air-datepicker autocomplete="off" data-range="true" data-multiple-dates-separator=" - " id="date-range2002" name="date" style="    width: 250px; text-align: center; height: 30px;padding:5px;" value=""/></span>

              </div>
            </div>

             <div class="adm-finish">


             <ul>
              <?=brands3();?>
            </ul><br><br>

            <div class="save">
              <input type="hidden" name="send" value="2" />
              <button type="submit" >Генерировать</button>
            </div>
            </div>
            </div>
   </form>
<br><br><br> <hr>

            <h3>Отчет по браку ТВ:</h3>
              <form id="send" method="POST">
   <div class="adm-form" style="padding-top:0;">
                     <div class="item">
              <div class="level" style="display: block;text-align: center;width: 100%;">Фильтрация по заводу:</div>
              <div class="value" style="display:block;">
              <select name="plant" class="nomenu" id="select_model3">
               <option selected value="">Выберите завод</option>
               <option value="all" style="background:#DDF4DD">Все</option>
               <?=services_list2_plant();?>
              </select>
              </div>
            </div>
              <div class="item">
              <div class="level" style="display: block;text-align: center;width: 100%;">Дата подтверждения ремонта:</div>
              <div class="value" style="display:block;">
              <span id="two-inputs4"><input type="text" data-air-datepicker autocomplete="off" data-range="true" data-multiple-dates-separator=" - " name="date" style="    width: 250px; text-align: center;  height: 30px;padding:5px;" value=""/> </span>

              </div>
            </div>

             <div class="adm-finish">


            <div class="save">
              <input type="hidden" name="send" value="4" />
              <button type="submit" >Генерировать</button>
            </div>
            </div>
            </div>
   </form>
 <?php } ?>
        </div>
  </div>
</div>

 <!-- New codebase -->
 <script src="/_new-codebase/front/vendor/air-datepicker/js/datepicker.min.js"></script>
 <script>
        document.addEventListener('DOMContentLoaded', function() {
            $('[data-air-datepicker]').datepicker({
                language: 'ru',
                autoClose: true,
                maxDate: new Date()
            });
        });
    </script>
</body>
</html>