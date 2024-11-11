<?php
# Подключаем  конфиг:
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/configuration.php');
# Подключаем авторизацию
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/auth.php');

function get_service_summ_fast($user_id, $month, $year, $brand = '') {
  global $db;

$sql = mysqli_query($db, 'SELECT `return_id`,`id`,`model_id`, `transport_cost`, `parts_cost`, `install_cost`, `dismant_cost`, `total_price`,`status_admin`,`onway`,`onway_type` FROM `repairs` WHERE `app_date` REGEXP \''.mysqli_real_escape_string($db, $year.'.'.$month.'.').'\' and `deleted` = 0 and (`status_admin` = \'Подтвержден\' or `status_admin` = \'Выдан\') and  `service_id` = '.$user_id.';');
      while ($row = mysqli_fetch_array($sql)) {
      //$info = get_request_info($row['id']);
      //$city = get_city($info['city']);

      if (check_returns_pls($row['return_id']) || $row['return_id'] == 0) {

       $model = model_info($row['model_id']);
       //$parts_info = repairs_parts_info($row['id']);
       if ($brand == 'TESLER') {
       if ($model['brand'] == 'TESLER') {
        $summ += $row['total_price'];
        $summ += $parts_info['summ'];
        $summ += $row['parts_cost'];
        $summ += $row['transport_cost'];

        mysqli_query($db, 'UPDATE `pay_billing` SET `sum` = '.$summ.' WHERE `service_id` = '.$user_id.' and `year` = '.$year.' and `month` = '.$month.' and `type` = 4;') or mysqli_error($db);

       }

       } else if ($brand == 'HARPER') {

       if ($model['brand'] == 'HARPER' || $model['brand'] == 'OLTO' || $model['brand'] == 'NESONS') {
          //file_put_contents('ids.txt', $row['id'].PHP_EOL, FILE_APPEND);
         //echo $row['id'].'-tesler';
        $summ += $row['total_price'];
        $summ += $parts_info['summ'];
        $summ += parts_price_billing($row['id']);
        $summ += (($row['status_admin'] != 'Запрос на выезд' && $row['onway'] == 1) ? get_price_transfer($model['cat'], $row['onway_type'], $user_id) : '0');

       mysqli_query($db, 'UPDATE `pay_billing` SET `sum` = '.$summ.' WHERE `service_id` = '.$user_id.' and `year` = '.$year.' and `month` = '.$month.' and `type` = 2;') or mysqli_error($db);

       }

       } else {
       if ($model['brand'] != 'TESLER') {

        $summ += $row['total_price'];
        $summ += $parts_info['summ'];
        $summ += parts_price_billing($row['id']);
        $summ += (($row['status_admin'] != 'Запрос на выезд' && $row['onway'] == 1) ? get_price_transfer($model['cat'], $row['onway_type'], $user_id) : '0');

        mysqli_query($db, 'UPDATE `pay_billing` SET `sum` = '.$summ.' WHERE `service_id` = '.$user_id.' and `year` = '.$year.' and `month` = '.$month.' and `type` = 2;') or mysqli_error($db);


       } else {

        $summ += $row['total_price'];
        $summ += $parts_info['summ'];
        $summ += parts_price_billing($row['id']);
        $summ += (($row['status_admin'] != 'Запрос на выезд' && $row['onway'] == 1) ? get_price_transfer($model['cat'], $row['onway_type'], $user_id) : '0');
        mysqli_query($db, 'UPDATE `pay_billing` SET `sum` = '.$summ.' WHERE `service_id` = '.$user_id.' and `year` = '.$year.' and `month` = '.$month.' and `type` = 4;') or mysqli_error($db);


       }




       }

      }

      }



    //file_put_contents('ids.txt', );
    return $summ;
}
