<?php
# Подключаем  конфиг:
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/configuration.php');

# New codebase
require_once $_SERVER['DOCUMENT_ROOT'] . '/_new-codebase/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/_new-codebase/back/autoload.php';

use models\Tariffs;
use program\core;

function model($id) {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `models` where `id` = '.$id);
      while ($row = mysqli_fetch_array($sql)) {
      $content = $row;
      }
    return $content;
}

function problem($id) {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `details_problem` where `id` = '.$id);
      while ($row = mysqli_fetch_array($sql)) {
      $content = $row;
      }
    return $content;
}
$table = Tariffs::getServiceTariffTable(33);
$sql = mysqli_query($db, 'SELECT * FROM `repairs` WHERE `service_id` = 33 AND `repair_type_id` = 0 and `total_price` = 0 ORDER BY `id` DESC;');
            while ($row = mysqli_fetch_array($sql)) {
            $model = model($row['model_id']);

$sql2 = mysqli_query($db, 'SELECT `problem_id` FROM `repairs_work` WHERE `repair_id` = '.$row['id'].' ORDER BY `id` DESC;');
            if (mysqli_num_rows($sql2) > 1) {
            echo 'ska!<br>'.$row['id'];
            }
            while ($row2 = mysqli_fetch_array($sql2)) {

            $problem = problem($row2['problem_id']);

              switch($problem['type']) {
              case 'Всегда блочный ремонт':
                if (mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `prices_service` where `cat_id` = \''.$model['cat'].'\' and `service_id` = 33 ;'))['COUNT(*)'] > 0) {
                $price = @mysqli_fetch_array(mysqli_query($db, 'SELECT `block` FROM `prices_service` where `cat_id` = \''.$model['cat'].'\' and `service_id` = 33 ;'))['block'];
                echo 1;
                } else {
                $price = @mysqli_fetch_array(mysqli_query($db, 'SELECT `block` FROM `'.$table.'` where `cat_id` = \''.$model['cat'].'\';'))['block'];
                }
                mysqli_query($db, 'UPDATE `repairs` SET `repair_type_id` = 1, `total_price` = '.$price.' where `id` = \''.$row['id'].'\';');
                break;
              case 'АНРП':
                if (mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `prices_service` where `cat_id` = \''.$model['cat'].'\' and `service_id` = 33 ;'))['COUNT(*)'] > 0) {

                $price = @mysqli_fetch_array(mysqli_query($db, 'SELECT `anrp` FROM `prices_service` where `cat_id` = \''.$model['cat'].'\' and `service_id` = 33 ;'))['anrp'];
                } else {
                $price = @mysqli_fetch_array(mysqli_query($db, 'SELECT `anrp` FROM `'.$table.'` where `cat_id` = \''.$model['cat'].'\';'))['anrp'];
                }
                mysqli_query($db, 'UPDATE `repairs` SET `repair_type_id` = 4, `total_price` = '.$price.' where `id` = \''.$row['id'].'\';');
                break;
              case 'АТО':
                if (mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `prices_service` where `cat_id` = \''.$model['cat'].'\' and `service_id` = 33 ;'))['COUNT(*)'] > 0) {
                $price = @mysqli_fetch_array(mysqli_query($db, 'SELECT `ato` FROM `prices_service` where `cat_id` = \''.$model['cat'].'\' and `service_id` = 33 ;'))['ato'];
                } else {
                $price = @mysqli_fetch_array(mysqli_query($db, 'SELECT `ato` FROM `'.$table.'` where `cat_id` = \''.$model['cat'].'\';'))['ato'];
                }
                mysqli_query($db, 'UPDATE `repairs` SET `repair_type_id` = 5, `total_price` = '.$price.' where `id` = \''.$row['id'].'\';');
                break;
              case 'Всегда компонентный ремонт':
                if (mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `prices_service` where `cat_id` = \''.$model['cat'].'\' and `service_id` = 33 ;'))['COUNT(*)'] > 0) {
                $price = @mysqli_fetch_array(mysqli_query($db, 'SELECT `component` FROM `prices_service` where `cat_id` = \''.$model['cat'].'\' and `service_id` = 33 ;'))['component'];
                } else {
                $price = @mysqli_fetch_array(mysqli_query($db, 'SELECT `element` FROM `'.$table.'` where `cat_id` = \''.$model['cat'].'\';'))['element'];
                }
                mysqli_query($db, 'UPDATE `repairs` SET `repair_type_id` = 2, `total_price` = '.$price.' where `id` = \''.$row['id'].'\';');
                break;
              case 'Замена аксессуаров':
                if (mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `prices_service` where `cat_id` = \''.$model['cat'].'\' and `service_id` = 33 ;'))['COUNT(*)'] > 0) {
                $price = @mysqli_fetch_array(mysqli_query($db, 'SELECT `access` FROM `prices_service` where `cat_id` = \''.$model['cat'].'\' and `service_id` = 33 ;'))['access'];
                } else {
                $price = @mysqli_fetch_array(mysqli_query($db, 'SELECT `acess` FROM `'.$table.'` where `cat_id` = \''.$model['cat'].'\';'))['acess'];
                }
               mysqli_query($db, 'UPDATE `repairs` SET `repair_type_id` = 3, `total_price` = '.$price.' where `id` = \''.$row['id'].'\';');
                break;
              }

             unset($price);

            }



            }

/*$sql = mysqli_query($db, 'SELECT * FROM `repairs` WHERE `service_id` = 33 AND `repair_type_id` != 0 and `total_price` = 0 ORDER BY `id` DESC;');
            while ($row = mysqli_fetch_array($sql)) {

              $model = model($row['model_id']);
              switch($row['repair_type_id']) {
              case 1:
                if (mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `prices_service` where `cat_id` = \''.$model['cat'].'\' and `service_id` = 33 ;'))['COUNT(*)'] > 0) {
                $price = @mysqli_fetch_array(mysqli_query($db, 'SELECT `block` FROM `prices_service` where `cat_id` = \''.$model['cat'].'\' and `service_id` = 33 ;'))['block'];

                } else {
                $price = @mysqli_fetch_array(mysqli_query($db, 'SELECT `block` FROM `prices` where `cat_id` = \''.$model['cat'].'\';'))['block'];
                }
                mysqli_query($db, 'UPDATE `repairs` SET `repair_type_id` = 1, `total_price` = '.$price.' where `id` = \''.$row['id'].'\';');
                break;
              case 4:
                if (mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `prices_service` where `cat_id` = \''.$model['cat'].'\' and `service_id` = 33 ;'))['COUNT(*)'] > 0) {

                $price = @mysqli_fetch_array(mysqli_query($db, 'SELECT `anrp` FROM `prices_service` where `cat_id` = \''.$model['cat'].'\' and `service_id` = 33 ;'))['anrp'];
                } else {
                $price = @mysqli_fetch_array(mysqli_query($db, 'SELECT `anrp` FROM `prices` where `cat_id` = \''.$model['cat'].'\';'))['anrp'];
                }
               mysqli_query($db, 'UPDATE `repairs` SET `repair_type_id` = 4, `total_price` = '.$price.' where `id` = \''.$row['id'].'\';');
                break;
              case 5:
                if (mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `prices_service` where `cat_id` = \''.$model['cat'].'\' and `service_id` = 33 ;'))['COUNT(*)'] > 0) {
                $price = @mysqli_fetch_array(mysqli_query($db, 'SELECT `ato` FROM `prices_service` where `cat_id` = \''.$model['cat'].'\' and `service_id` = 33 ;'))['ato'];
                } else {
                $price = @mysqli_fetch_array(mysqli_query($db, 'SELECT `ato` FROM `prices` where `cat_id` = \''.$model['cat'].'\';'))['ato'];
                }
                mysqli_query($db, 'UPDATE `repairs` SET `repair_type_id` = 5, `total_price` = '.$price.' where `id` = \''.$row['id'].'\';');
                break;
              case 2:
                if (mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `prices_service` where `cat_id` = \''.$model['cat'].'\' and `service_id` = 33 ;'))['COUNT(*)'] > 0) {
                $price = @mysqli_fetch_array(mysqli_query($db, 'SELECT `component` FROM `prices_service` where `cat_id` = \''.$model['cat'].'\' and `service_id` = 33 ;'))['component'];
                } else {
                $price = @mysqli_fetch_array(mysqli_query($db, 'SELECT `component` FROM `prices` where `cat_id` = \''.$model['cat'].'\';'))['component'];
                }
                mysqli_query($db, 'UPDATE `repairs` SET `repair_type_id` = 2, `total_price` = '.$price.' where `id` = \''.$row['id'].'\';');
                break;
              case 3:
                if (mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `prices_service` where `cat_id` = \''.$model['cat'].'\' and `service_id` = 33 ;'))['COUNT(*)'] > 0) {
                $price = @mysqli_fetch_array(mysqli_query($db, 'SELECT `acess` FROM `prices_service` where `cat_id` = \''.$model['cat'].'\' and `service_id` = 33 ;'))['acess'];
                } else {
                $price = @mysqli_fetch_array(mysqli_query($db, 'SELECT `acess` FROM `prices` where `cat_id` = \''.$model['cat'].'\';'))['acess'];
                }
                mysqli_query($db, 'UPDATE `repairs` SET `repair_type_id` = 3, `total_price` = '.$price.' where `id` = \''.$row['id'].'\';');
                break;
              }

              //echo $price.'<br>';

            }
       */

?>