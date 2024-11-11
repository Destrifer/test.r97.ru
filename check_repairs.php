<?php

# Подключаем  конфиг:
require_once('includes/configuration.php');
# Подключаем функции:
require_once('includes/functions.php');

function get_request_info($id) {
  global $db;
return mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `requests` WHERE `user_id` = '.$id));
}

function get_request_info_serice($id) {
  global $db;
$req = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `requests` WHERE `user_id` = '.$id));
return $req;
}

function get_service_summ_fast_array($user_id, $month, $year, $brand = '') {
  global $db;

$horizont_array = array('ЗЭБТ-Harper', 'Horizont', 'Hartens', 'ЗЭБТ-Горизонт', 'Белит-Горизонт', 'OK', 'ЗЭБТ-HARTENS', 'ЗЭБТ-Skyworth', 'ЗЭБТ-Prestigio', 'ROSENLEW');

$sql = mysqli_query($db, 'SELECT `return_id`, `id`, `model_id`, `total_price`, `status_admin`, `onway`, `onway_type` FROM `repairs` WHERE YEAR(app_date) = '.$year.' and MONTH(app_date) = '.$month.' and deleted = 0 and (status_admin = \'Подтвержден\' or status_admin = \'Выдан\') and  service_id = '.$user_id.'');
//echo 'SELECT r.return_id,r.id,r.model_id,r.total_price,r.status_admin,r.onway,r.onway_type,m.cat as cat,m.brand as brand FROM repairs r INNER JOIN models m ON r.model_id = m.id WHERE YEAR(r.app_date) = '.$year.' and MONTH(r.app_date) = '.$month.' and r.deleted` = 0 and (r.status_admin = \'Подтвержден\' or r.status_admin` = \'Выдан\') and  r.service_id = '.$user_id;
      while ($row = mysqli_fetch_array($sql)) {
      //$info = get_request_info($row['id']);
      //$city = get_city($info['city']);

      if (check_returns_pls($row['return_id']) || $row['return_id'] == 0) {

      $model = model_info($row['model_id']);
       //$parts_info = repairs_parts_info($row['id']);
       if ($brand == 'TESLER') {
       //echo $row['id'].'<br>';
       if ($model['brand'] == 'TESLER') {
        // echo $row['id'].'-tesler';
        $summ += $row['total_price'];
        $summ += $parts_info['summ'];
        $summ += parts_price_billing($row['id']);
        $summ += (($row['status_admin'] != 'Запрос на выезд' && $row['onway'] == 1) ? get_price_transfer($model['cat'], $row['onway_type'], $user_id) : '0');
        //echo $row['id'].' - '.$row['total_price'].'+'.parts_price_billing($row['id']).'+'.(($row['status_admin'] != 'Запрос на выезд' && $row['onway'] == 1) ? get_price_transfer($model['cat'], $row['onway_type']) : '0').'<br>';
        //mysqli_query($db, 'UPDATE `pay_billing` SET `sum` = '.$summ.' WHERE `service_id` = '.$user_id.' and `year` = '.$year.' and `month` = '.$month.' and `type` = 4;') or mysqli_error($db);



       }

       } else if ($brand == 'HORIZONT') {
       //echo $row['id'].'<br>';
       if (in_array($model['brand'], $horizont_array)) {
        // echo $row['id'].'-tesler';
        $summ += $row['total_price'];
        $summ += $parts_info['summ'];
        $summ += parts_price_billing($row['id']);
        $summ += (($row['status_admin'] != 'Запрос на выезд' && $row['onway'] == 1) ? get_price_transfer($model['cat'], $row['onway_type'], $user_id) : '0');
        //echo $row['id'].' - '.$row['total_price'].'+'.parts_price_billing($row['id']).'+'.(($row['status_admin'] != 'Запрос на выезд' && $row['onway'] == 1) ? get_price_transfer($model['cat'], $row['onway_type']) : '0').'<br>';
        //mysqli_query($db, 'UPDATE `pay_billing` SET `sum` = '.$summ.' WHERE `service_id` = '.$user_id.' and `year` = '.$year.' and `month` = '.$month.' and `type` = 12;') or mysqli_error($db);



       }

       } else if ($brand == 'SVEN') {
       //echo $row['id'].'<br>';
       if ($model['brand'] == 'SVEN') {
        // echo $row['id'].'-tesler';
        $summ += $row['total_price'];
        $summ += $parts_info['summ'];
        $summ += parts_price_billing($row['id']);
        $summ += (($row['status_admin'] != 'Запрос на выезд' && $row['onway'] == 1) ? get_price_transfer($model['cat'], $row['onway_type'], $user_id) : '0');
        //echo $row['id'].' - '.$row['total_price'].'+'.parts_price_billing($row['id']).'+'.(($row['status_admin'] != 'Запрос на выезд' && $row['onway'] == 1) ? get_price_transfer($model['cat'], $row['onway_type']) : '0').'<br>';
       // mysqli_query($db, 'UPDATE `pay_billing` SET `sum` = '.$summ.' WHERE `service_id` = '.$user_id.' and `year` = '.$year.' and `month` = '.$month.' and `type` = 10;') or mysqli_error($db);



       }

       } else if ($brand == 'HARPER') {

       if ($model['brand'] == 'HARPER' || $model['brand'] == 'OLTO' || $model['brand'] == 'NESONS') {
          //file_put_contents('ids.txt', $row['id'].PHP_EOL, FILE_APPEND);
         //echo $row['id'].'-tesler';


        $summko += $row['total_price'];
        $summko += $parts_info['summ'];
        $summko += parts_price_billing($row['id']);
        $summko += (($row['status_admin'] != 'Запрос на выезд' && $row['onway'] == 1) ? get_price_transfer($model['cat'], $row['onway_type'], $user_id) : '0');

        $summ += $row['total_price'];
        $summ += $parts_info['summ'];
        $summ += parts_price_billing($row['id']);
        $summ += (($row['status_admin'] != 'Запрос на выезд' && $row['onway'] == 1) ? get_price_transfer($model['cat'], $row['onway_type'], $user_id) : '0');
        echo $row['id'].' - harper - '.$summko.'<br>';      
       unset($summko);
       //mysqli_query($db, 'UPDATE `pay_billing` SET `sum` = '.$summ.' WHERE `service_id` = '.$user_id.' and `year` = '.$year.' and `month` = '.$month.' and `type` = 2;') or mysqli_error($db);

       }

       } else {
       if ($model['brand'] != 'TESLER' && !in_array($model['brand'], $horizont_array) && $model['brand'] != 'SVEN' ) {

        $summ += $row['total_price'];
        $summ += $parts_info['summ'];
        $summ += parts_price_billing($row['id']);
        $summ += (($row['status_admin'] != 'Запрос на выезд' && $row['onway'] == 1) ? get_price_transfer($model['cat'], $row['onway_type'], $user_id) : '0');

        //mysqli_query($db, 'UPDATE `pay_billing` SET `sum` = '.$summ.' WHERE `service_id` = '.$user_id.' and `year` = '.$year.' and `month` = '.$month.' and `type` = 2;') or mysqli_error($db);


       } else {

        $summ += $row['total_price'];
        $summ += $parts_info['summ'];
        $summ += parts_price_billing($row['id']);
        $summ += (($row['status_admin'] != 'Запрос на выезд' && $row['onway'] == 1) ? get_price_transfer($model['cat'], $row['onway_type'], $user_id) : '0');
        //mysqli_query($db, 'UPDATE `pay_billing` SET `sum` = '.$summ.' WHERE `service_id` = '.$user_id.' and `year` = '.$year.' and `month` = '.$month.' and `type` = 4;') or mysqli_error($db);


       }




       }

      }

      }



    //file_put_contents('ids.txt', );
    echo $summ;
}

echo get_service_summ_fast_array(33, 11, 2018, 'HARPER')

?>