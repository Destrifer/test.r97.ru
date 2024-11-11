<?php

use models\staff\Staff;
use models\User;
use models\Users;
use program\core;
use program\core\Time;
ini_set("memory_limit", "3000M");
  if(!empty($_POST['date'])){
$d = explode(' - ', $_POST['date']);
$_POST['date1'] = Time::format($d[0], 'Y-m-d');
$_POST['date2'] = (!empty($d[1])) ? Time::format($d[1], 'Y-m-d') : '';
}
$masterID = User::getData('id');
$master = Staff::getStaff(['id' => $masterID]);
$userID = 33;
# Сохраняем:
if ($_POST['send'] == 1) {

  $glob_id = 3;
  require_once './adm/excel/vendor/autoload.php';
  
          if (file_exists('adm/excel/files')) {
              foreach (glob('adm/excel/files/*') as $file) {
                  unlink($file);
              }
          }
  
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
  
  if ($userID == 1 || $userID == 33) {
  $sheet->setCellValue("I1", 'Мастер');
  $sheet->setCellValue("J1", 'Завод');
  $sheet->setCellValue("K1", 'Заказ');
  $sheet->setCellValue("L1", 'Статус ремонта');
  }
  
  } else {
  
  $dateStrFrom = (!empty($_POST['date1'])) ? ' с ' . core\Time::format($_POST['date1']) : '';
  $dateStrTo = (!empty($_POST['date2'])) ? ' по ' . core\Time::format($_POST['date2']) : '';

    $row = mysqli_fetch_assoc(mysqli_query($db, 'SELECT `name`, `name_public` FROM `requests` WHERE `user_id` = 33'));
    $serviceStr = $row['name'] . ', ' . $row['name_public'];

  
    $sheet->setCellValue("A1", 'Сервисный отчет' . $dateStrFrom . $dateStrTo . ', '.$serviceStr);
  
  
  }
  
  
  
  if (in_array('\'Оплачен\'', $_POST['status'])) {
  $where = '"Подтвержден", "Выдан"';
  $payed = 1;
  } else {
    $payed = 0;
  $where = implode(',', $_POST['status']);
  }
  
  

  $where_user = 'AND `service_id` = 33 AND `master_user_id` = ' .$masterID. ' ';

  
  if (!empty($_POST['date1']) && !empty($_POST['date2'])) {
  $where_date = 'and `create_date` BETWEEN \''.$_POST['date1'].'\' AND \''.$_POST['date2'].'\'';
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

        if (!Time::isEmpty($content['begin_date'])) {
          $date1 = new DateTime($content['begin_date']);
          $date1_ready = $date1->format('d.m.Y');
        }
        if (!Time::isEmpty($content['finish_date'])) {
          $date2 = new DateTime($content['finish_date']);
          $date2_ready = $date2->format('d.m.Y');
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
          $sheet->setCellValue("AG$glob_id", $master['surname'].' '.$master['name'].' '.$master['thirdname']);
          $sheet->setCellValue("AJ$glob_id", $content['comment']); // Комментарии к ремонту
          $sheet->setCellValue("AK$glob_id", $content['parts_info']['0']['qty']); // Кол-во деталей
          $sheet->setCellValue("AL$glob_id", $content['parts_info']['0']['position']); // Позиционное обозначение
          $problem = get_content_by_id('details_problem', $content['parts_info']['0']['problem_id']);
          $sheet->setCellValue("AM$glob_id", $problem['name']); // ID причин отказа детали (да-да, name тут - это id!)
          if($content['parts_info']['0']['problem_id'] == 5){
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
          $sheet->setCellValue("AV$glob_id", $content['parts_info']['0']['price']);
          $sheet->setCellValue("AW$glob_id", $content['parts_info']['sum']);
          $sheet->setCellValue("AX$glob_id", (($row['status_admin'] != 'Запрос на выезд' && $row['onway'] == 1) ? get_price_transfer($content['model']['cat'], $row['onway_type'], $content['service_id']) : '0'));
          $sheet->setCellValue("AY$glob_id", $content['total_price']);
          $sheet->setCellValue("AZ$glob_id", ($content['total_price'] + (($row['status_admin'] != 'Запрос на выезд' && $row['onway'] == 1) ? get_price_transfer($content['model']['cat'], $row['onway_type'], $content['service_id']) : '0') + $content['parts_info']['sum']));
          $sheet->setCellValueExplicit("BA$glob_id", Staff::getWorkCost($masterID, $content['total_price']), PHPExcel_Cell_DataType::TYPE_NUMERIC);
          if (($userID == 1 || $userID == 33)) {
            $sheet->setCellValue("AP$glob_id", $problem['type']); // Тип ремонта
            $sheet->setCellValue("C$glob_id", $content['anrp_number']);
            $sheet->setCellValue("K$glob_id", $content['model']['model_id']); // торговый код
            $sheet->setCellValue("N$glob_id", $serialInfo['provider']);
            $sheet->setCellValue("O$glob_id", $serialInfo['order']);
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
  
  if ($userID == 33) {
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
          $sheet->setCellValue("AG$glob_id", $master['surname'].' '.$master['name'].' '.$master['thirdname']);
          $sheet->setCellValue("AJ$glob_id", $content['comment']);
          $sheet->setCellValue("AK$glob_id", $content['parts_info']['0']['qty']);
          $sheet->setCellValue("AL$glob_id", $content['parts_info']['0']['position']);
          $problem = get_content_by_id('details_problem', $content['parts_info']['0']['problem_id']);
          $sheet->setCellValue("AN$glob_id", $problem['name']);
          if($content['parts_info']['0']['problem_id'] == 5){
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
          $sheet->setCellValue("AV$glob_id", $content['parts_info']['0']['price']);
          $sheet->setCellValue("AW$glob_id", $content['parts_info']['sum']);
          $sheet->setCellValue("AX$glob_id", (($row['status_admin'] != 'Запрос на выезд' && $row['onway'] == 1) ? get_price_transfer($content['model']['cat'], $row['onway_type'], $content['service_id']) : '0'));
          $sheet->setCellValue("AY$glob_id", $content['total_price']);
          $sheet->setCellValue("AZ$glob_id", ($content['total_price'] + (($row['status_admin'] != 'Запрос на выезд' && $row['onway'] == 1) ? get_price_transfer($content['model']['cat'], $row['onway_type'], $content['service_id']) : '0') + $content['parts_info']['sum']));
          $sheet->setCellValueExplicit("BA$glob_id", Staff::getWorkCost($masterID, $content['total_price']), PHPExcel_Cell_DataType::TYPE_NUMERIC);
          if (($userID == 1 || $userID == 33)) {
            $sheet->setCellValue("AP$glob_id", $problem['type']); // Тип ремонта
            $sheet->setCellValue("C$glob_id", $content['anrp_number']);
            $sheet->setCellValue("K$glob_id", $content['model']['model_id']); // торговый код
            $sheet->setCellValue("N$glob_id", $serialInfo['provider']);
            $sheet->setCellValue("O$glob_id", $serialInfo['order']);
            $sheet->setCellValue("P$glob_id", $serialInfo['plant']); // Завод-сборщик
            $sheet->setCellValue("AM$glob_id", $content['parts_info']['0']['problem_id']); // ID причин отказа детали
          }

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
         
        $sheet->getStyle("A3:BA".($glob_id - 1))->applyFromArray($border);
        
        $sheet->removeColumn('AM'); // ID причин отказа детали
        $sheet->removeColumn('AH'); // Без мастера
        $sheet->removeColumn('AG'); // Мастер
        $sheet->removeColumn('F'); // Название СЦ
        $sheet->removeColumn('E'); // № СЦ
  
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
$sql = mysqli_query($db, 'SELECT * FROM `'.Users::TABLE.'` WHERE `status_id` = 1 and `role_id` = 3;');
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

function brands() {
  global $db;
  $content = '';
$sql = mysqli_query($db, 'SELECT * FROM `brands` WHERE `is_deleted` = 0;');
      while ($row = mysqli_fetch_array($sql)) {
      $check_service = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `brands_users` WHERE `brand_id` = \''.mysqli_real_escape_string($db, $row['id']).'\' and `service_id` = 33 and service = 1 LIMIT 1;'));
      if ($check_service['COUNT(*)'] > 0) {
      $content .= '<li><label><input type="checkbox" name="brands[]" value="'.$row['name'].'" />'.$row['name'].'</label></li>';
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
$sql = mysqli_query($db, 'SELECT * FROM `models` order by `name` ASC;');
      while ($row = mysqli_fetch_array($sql)) {

      if (User::hasRole('admin', 'slave-admin')) {
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
$sql = mysqli_query($db, 'SELECT * FROM `brands`;');
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
$sql = mysqli_query($db, 'SELECT * FROM `brands` order by name ASC;');
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
<title>Отчетность - Панель управления</title>
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
              <div class="level" style="display: block;text-align: center;width: 100%;">Дата создания ремонта:</div>
              <div class="value" style="display:block;">
              <span id="two-inputs"><input type="text" data-air-datepicker autocomplete="off" data-range="true" data-multiple-dates-separator=" - "  id="date-range200" name="date" style="    width: 250px; text-align: center;  height: 30px;padding:5px;" value=""/></span>

              </div>
            </div>
               <br> <br>
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
            <?php if ($userID == 33) { ?>
             <div class="adm-finish" style="padding-top:10px;">
            <ul>
              <li><label><input type="checkbox" name="status_33_1" value="1" />Утилизирован</label></li>
              <li><label><input type="checkbox" name="status_33_2" value="1" />Уценка</label></li>
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