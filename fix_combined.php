<?php
# Подключаем  конфиг:
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/configuration.php');
# Подключаем авторизацию
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/auth.php');


/*$sql = mysqli_query($db, 'SELECT * FROM `pay_billing` WHERE `sended` = 1');
      while ($row = mysqli_fetch_array($sql)) {

        if (mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `combine_links` where `pay_billing_id` = '.$row['id'].';'))['COUNT(*)'] > 0) {
            mysqli_query($db, 'UPDATE `pay_billing` set `old` = 1 where `id` = '.$row['id']);
        }

      }  */

$sql = mysqli_query($db, 'SELECT * FROM `pay_billing` WHERE `sended` = 1 and `old` = 0');
      while ($row = mysqli_fetch_array($sql)) {

       //mysqli_query($db, 'UPDATE `pay_billing` set `sended` = 1 where `id` = '.$row['id']);
      mysqli_query($db, 'UPDATE `pay_billing` set `payed` = 0 where `id` = '.($row['id']+1));
       /* if (mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `combine_links` where `combine_id` != 50 and `pay_billing_id` = '.$row['id'].';'))['COUNT(*)'] > 0) {
            mysqli_query($db, 'UPDATE `pay_billing` set `old` = 1 where `id` = '.$row['id']);
        } */

     }


?>