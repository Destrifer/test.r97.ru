<?php
# Подключаем  конфиг:
require_once('includes/configuration.php');
# Подключаем функции:
require_once('includes/functions.php');


error_reporting(E_ALL ^ E_NOTICE);

$sql = mysqli_query($db, 'SELECT * FROM `returns` ORDER by `id` DESC;');
if (mysqli_num_rows($sql) != false) {
      while ($row = mysqli_fetch_array($sql)) {
      $sql_check_count = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(id) FROM `repairs` WHERE `return_id` = \''.mysqli_real_escape_string($db, $row['id']).'\' and `deleted` = 0;'));
      $sql_check_count2 = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(id) FROM `repairs` WHERE `return_id` = \''.mysqli_real_escape_string($db, $row['id']).'\' and (`status_admin` = \'Подтвержден\' OR `status_admin` = \'Выдан\') and `deleted` = 0;'));


        if ($row['date_out'] > '2019.10.01') {
        echo $row['id'].' - '.$row['date_out'].' affected<hr>';

$sql_check_repair = mysqli_query($db, 'SELECT * FROM `repairs` WHERE `return_id` = '.$row['id'].' ;');

while ($row_repair = mysqli_fetch_array($sql_check_repair)) {
       $model = model_info($row_repair['model_id']);
       if ($model['brand'] == 'HARPER' || $model['brand'] == 'OLTO' || $model['brand'] == 'NESONS') {

           //$summ += $row_repair['total_price'];
           $model_check = @mysqli_fetch_array(mysqli_query($db, 'SELECT `service` FROM `models_users` WHERE `service_id` = 33 and `model_id` = '.$model['id'].' ;'))['service'];
           if ($model_check == 'Нет') {

            echo $row_repair['id'].' не обслуживется - '.$row_repair['total_price'].' -  '.$row_repair['master_user_id'].'<br>';

            //mysqli_query($db, 'UPDATE `repairs` SET `tmp_total_price` = \''.$row_repair['total_price'].'\' where `id` = '.$row_repair['id']);
            //mysqli_query($db, 'UPDATE `repairs` SET `total_price` = tmp_total_price where master_user_id != 0 and `id` = '.$row_repair['id']);
           }


       }
}




}





}

}
?>