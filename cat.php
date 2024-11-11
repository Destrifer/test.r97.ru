<?php
# Подключаем  конфиг:
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/configuration.php');

function cat_by_name($id) {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `cats` where `name` = \''.$id.'\' LIMIT 1;');
      while ($row = mysqli_fetch_array($sql)) {
      $content = $row;
      }
    return $content;
}

/*$sql = mysqli_query($db, 'SELECT * FROM `prices`;');
      while ($row = mysqli_fetch_array($sql)) {

      $cat = cat_by_name($row['type']);
      if ($cat['id'])
      mysqli_query($db, 'UPDATE `prices` set `type` = \''.$cat['id'].'\' WHERE `id` = '.$row['id'].' LIMIT 1;') or mysqli_error($db);

     mysqli_query($db, 'INSERT INTO `prices` (
      `type`
      ) VALUES (
      \''.mysqli_real_escape_string($db, $row['name']).'\'
      );') or mysqli_error($db);


      }    */


$sql = mysqli_query($db, 'SELECT * FROM `repairs` WHERE `client_type` = 2 and `deleted` = 0;');
      while ($row = mysqli_fetch_array($sql)) {

      $count = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `clients` where `name` = \''.$row['client'].'\' and `address` = \''.$row['address'].'\' and `phone` = \''.$row['phone'].'\' LIMIT 1;'))['COUNT(*)'];
      if ($count == 0) {
      mysqli_query($db, 'INSERT INTO `clients` (
      `name`,
      `address`,
      `phone`
      ) VALUES (
      \''.mysqli_real_escape_string($db, $row['client']).'\',
      \''.mysqli_real_escape_string($db, $row['address']).'\',
      \''.mysqli_real_escape_string($db, $row['phone']).'\'
      );') or mysqli_error($db);
      }

      $id = mysqli_insert_id($db);
      mysqli_query($db, 'UPDATE `repairs` set `client_id` = \''.$id.'\' WHERE `id` = '.$row['id'].' LIMIT 1;') or mysqli_error($db);


      }
?>