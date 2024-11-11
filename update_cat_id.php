<?php
# Подключаем  конфиг:
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/configuration.php');
# Подключаем функции:
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/functions.php');

# New codebase
require_once $_SERVER['DOCUMENT_ROOT'] . '/_new-codebase/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/_new-codebase/back/autoload.php';

function model($id) {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `models` where `id` = '.$id);
      while ($row = mysqli_fetch_array($sql)) {
      $content = $row;

      }
    return $content;
}

function problem_info($id) {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `repairs_work` where `repair_id` = '.$id);
      while ($row = mysqli_fetch_array($sql)) {
      $content = $row;

      }
    return $content;
}

$sql = mysqli_query($db, 'SELECT * FROM `repairs` where `cat_id` = 0;');
      while ($row = mysqli_fetch_array($sql)) {
      //$info = get_request_info($row['id']);
      //$city = get_city($info['city']);

       $model = model($row['model_id']);
       mysqli_query($db, 'UPDATE `repairs` set `cat_id` = '.$model['cat'].' where `id` = '.$row['id']);

      }



$sql = mysqli_query($db, 'SELECT * FROM `repairs` where `problem_id` = 0;');
      while ($row = mysqli_fetch_array($sql)) {
      //$info = get_request_info($row['id']);
      //$city = get_city($info['city']);

       $problem = problem_info($row['id']);
       mysqli_query($db, 'UPDATE `repairs` set `problem_id` = '.$problem['problem_id'].' where `id` = '.$row['id']);

      }



$sql = mysqli_query($db, 'SELECT * FROM `repairs` where `doubled` = 0 and `serial` != \'NULL\' and `serial` != \'-\' and `serial` != \'\';');
      while ($row = mysqli_fetch_array($sql)) {
      //$info = get_request_info($row['id']);
      //$city = get_city($info['city']);
       if (models\Repair::isRepeated($row['id'])) {
        mysqli_query($db, 'UPDATE `repairs` set `doubled` = 1 where `id` = '.$row['id']);
       }


      }


     $sql_model = mysqli_query($db, 'SELECT `id`,`model_id` FROM `repairs` where `model_name` = \'\'');
      while ($row_model = mysqli_fetch_array($sql_model)) {
      $model_info = model_info($row_model['model_id']);
      mysqli_query($db, 'UPDATE `repairs` SET  `model_name` = \''.$model_info['name'].'\' where `id` = '.$row_model['id']);
      echo $model_info['name']."<br>";
      //echo  'UPDATE `repairs` SET  `model_name` = \''.$model_info['name'].'\' where `id` = '.$row_model['id'];
      }

?>