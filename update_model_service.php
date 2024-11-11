<?php
# Подключаем  конфиг:
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/configuration.php');
# Подключаем функции:
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/functions.php');

$file = file('models.txt');
foreach ($file as $line) {

$exp = explode(',', $line);
//print_r($exp);
mysqli_query($db, 'UPDATE `models` SET `service` = \''.trim($exp['1']).'\' where `id` = '.$exp['0']);

}




/*$sql = mysqli_query($db, 'SELECT distinct name, id FROM `issues`;');
      while ($row = mysqli_fetch_array($sql)) {
      $check = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `issues` WHERE `name` = \''.mysqli_real_escape_string($db, $row['name']).'\';'))['COUNT(*)'];

      if ($check > 1) {

        $first_row = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `issues` WHERE `name` = \''.mysqli_real_escape_string($db, $row['name']).'\' LIMIT 1;'));
        echo '<u>'.$row['id'].' - '.$row['name'].'</u><br><hr>';
        $sql2 = mysqli_query($db, 'SELECT * FROM `issues` where `name` = \''.mysqli_real_escape_string($db, $row['name']).'\' and id != '.$first_row['id'].';');
        while ($row2 = mysqli_fetch_array($sql2)) {

            //mysqli_query($db, 'UPDATE `repairs` SET `disease` = \''.$first_row['id'].'\' where `disease` = '.$row2['id']);
            //mysqli_query($db, 'DELETE from `issues` where `id` = '.$row2['id']);
            echo '<b>'.$row2['id'].' - '.$row2['name'].'</b><br>';

        }
        echo '<hr>';

      } else {
        echo $row['id'].' - '.$row['name'].'<br>';
      }


      }
*/


?>