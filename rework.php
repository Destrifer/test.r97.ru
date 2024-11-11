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
      //echo 'SELECT COUNT(id) FROM `repairs` WHERE `return_id` = \''.mysqli_real_escape_string($db, $row['id']).'\' and (`status_admin` = \'Подтвержден\' OR `status_admin` = \'Выдан\') and `deleted` = 0;';

      //echo $row['id'].' - '.$sql_check_count['COUNT(id)'].':'.$sql_check_count2['COUNT(id)'].'<br>';

      if ($sql_check_count['COUNT(id)'] == $sql_check_count2['COUNT(id)'] && ($sql_check_count['COUNT(id)'] != 0 && $sql_check_count2['COUNT(id)'] =! 0)) {

      if ($row['date_out'] == '' || preg_match('/2019\./', $row['date_out'])) {
        $date_out = @mysqli_fetch_array(mysqli_query($db, 'SELECT `app_date` FROM `repairs` WHERE `return_id` = '.$row['id'].' ORDER BY STR_TO_DATE(app_date, \'%Y.%m.%d\') DESC LIMIT 1'))['app_date'];
       if ($row['date_out'] != $date_out) {

        if ($row['date_out'] > '2019.09.01') {
        echo $row['id'].' - '.$row['date_out'].' - must be '.$date_out.'<br>';
        }

       }
        /* mysqli_query($db, 'UPDATE `returns` SET `date_out` = \''.$date_out.'\' where `id` = '.$row['id']);

        $sql2 = mysqli_query($db, 'SELECT * FROM `repairs` where `return_id` = '.$row['id']);
              while ($row2 = mysqli_fetch_array($sql2)) {
        mysqli_query($db, 'UPDATE `repairs` SET
        `app_date` = \''.mysqli_real_escape_string($db, $date_out).'\'
        WHERE `id` = \''.mysqli_real_escape_string($db, $row2['id']).'\' LIMIT 1') or mysqli_error($db);


              admin_log_add('Партия возврата  #'.$row['id'].' получила бы дату выдачи '.$date_out.' и ремонты обновлены');
         $row['date_out'] = $date_out;  */
        if ($row['notify'] == 0 && $content_client['manager_notify'] == 1) {
         /* mysqli_query($db, 'UPDATE `returns` SET `notify` = 1 where `id` = '.$row['id']);
          client_notify($content_client['manager_email'], $row['id'], 2);    */
        }

        }

      }
      }
      }

?>