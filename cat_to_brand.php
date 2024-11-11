<?php
# Подключаем  конфиг:
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/configuration.php');
# Подключаем авторизацию
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/auth.php');



$sql = mysqli_query($db, 'SELECT * FROM `brands`');
while ($row = mysqli_fetch_array($sql)) {

    mysqli_query($db, 'DELETE FROM `cats_to_brand` WHERE `brand_id` = '.$row['id'].';') or mysqli_error($db);
    $sql2 = mysqli_query($db, 'SELECT `cat` FROM `models` where brand = \''.$row['name'].'\' group by cat');
    while ($row2 = mysqli_fetch_array($sql2)) {
          mysqli_query($db, 'INSERT INTO `cats_to_brand` (
            `brand_id`,
            `cat_id`
            ) VALUES (
            \''.mysqli_real_escape_string($db, $row['id']).'\',
            \''.mysqli_real_escape_string($db, $row2['cat']).'\'
            );') or mysqli_error($db);
    }

}


       //mysqli_query($db, 'UPDATE `pay_billing` set `sended` = 1 where `id` = '.$row['id']);
      //mysqli_query($db, 'UPDATE `pay_billing` set `payed` = 0 where `id` = '.($row['id']+1));
       /* if (mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `combine_links` where `combine_id` != 50 and `pay_billing_id` = '.$row['id'].';'))['COUNT(*)'] > 0) {
            mysqli_query($db, 'UPDATE `pay_billing` set `old` = 1 where `id` = '.$row['id']);
        } */

?>