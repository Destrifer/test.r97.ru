<?php
# Подключаем  конфиг:
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/configuration.php');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$sql = mysqli_query($db, 'SELECT * FROM `repairs_photo` where uploaded = 1 LIMIT 25000;');
      while ($row = mysqli_fetch_array($sql)) {
        unlink(str_replace('http://service.harper.ru/', '', $row['url']));

      }

?>