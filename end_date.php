<?php
# Подключаем  конфиг:
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/configuration.php');
# Подключаем авторизацию
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/auth.php');

$sql = mysqli_query($db, 'SELECT * FROM `repairs` where `ending` = \'0000-00-00\' ;');
if (mysqli_num_rows($sql) != false) {
      while ($row = mysqli_fetch_array($sql)) {

      if ($row['client_id']) {
      $client = mysqli_fetch_array(mysqli_query($db, 'SELECT `days` FROM `clients` WHERE `id` = \''.mysqli_real_escape_string($db, $row['client_id']).'\''));
      $edning = date("d.m.Y", strtotime($row['receive_date'] . " +".$client['days']." days"));
      $edning_sql = date("Y-m-d", strtotime($row['receive_date'] . " +".$client['days']." days"));

        mysqli_query($db, 'UPDATE `repairs` SET `ending` = \''.$edning_sql.'\' WHERE `id` = \''.mysqli_real_escape_string($db, $row['id']).'\' ;') or mysqli_error($db);

      } else {
      $edning = date("d.m.Y", strtotime($row['receive_date'] . " +45 days"));
      $edning_sql = date("Y-m-d", strtotime($row['receive_date'] . " +45 days"));

        mysqli_query($db, 'UPDATE `repairs` SET `ending` = \''.$edning_sql.'\' WHERE `id` = \''.mysqli_real_escape_string($db, $row['id']).'\' ;') or mysqli_error($db);

      }


}

}


$sql = mysqli_query($db, 'SELECT * FROM `returns` ORDER by `id` DESC;');
if (mysqli_num_rows($sql) != false) {
      while ($row = mysqli_fetch_array($sql)) {
         if ($row['date_out'] == '') {
         $date_out = @mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `repairs` WHERE `return_id` = '.$row['id'].' ORDER BY `repairs`.`app_date` DESC LIMIT 1'))['app_date'];
         mysqli_query($db, 'UPDATE `returns` SET `date_out` = \''.$date_out.'\' where `id` = '.$row['id']);

       $sql2 = mysqli_query($db, 'SELECT `id` FROM `repairs` where `return_id` = '.$row['id'] .' AND `status_admin` = "Подтвержден"');
              while ($row2 = mysqli_fetch_array($sql2)) {
        mysqli_query($db, 'UPDATE `repairs` SET
        `app_date` = \''.mysqli_real_escape_string($db, $date_out).'\', 
        `approve_date` = \''.mysqli_real_escape_string($db, str_replace('.', '-', $date_out)).'\' 
        WHERE `id` = \''.mysqli_real_escape_string($db, $row2['id']).'\'') or mysqli_error($db);  


              }

         $row['date_out'] = $date_out;
        }

}

}

?>