<?php
# ����������  ������:
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/configuration.php');
# ���������� �������:
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/functions.php');
# ���������� �����������
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/auth.php');

/*$file = file('repairs.txt');
foreach ($file as $id) {
$sql = mysqli_query($db, 'SELECT `id`,`total_price`, `status_admin`, `onway`, `onway_type`, `model_id`, `app_date`  FROM `repairs` WHERE `id` = '.$id.';');
      while ($row = mysqli_fetch_array($sql)) {

      $model = model_info($row['model_id']);

      if ($model['brand'] == 'HARPER' || $model['brand'] == 'OLTO' && $row['app_date'] < '2019.10.01') {
      $summ += $row['total_price'];
      $summ += parts_price_billing($row['id']);
      $summ += (($row['status_admin'] != '������ �� �������� ������' && $row['onway'] == 1) ? get_price_transfer($model['cat'], $row['onway_type']) : '0');

      echo $row['id'].' - '.$summ.' - '.$row['app_date'].'<br>';
      $total_sum += $summ;
      unset($summ);

      }

      }

}
echo '<hr>'.$total_sum;  */

$file = file('returns.txt');
foreach ($file as $id) {
$sql = mysqli_query($db, 'SELECT `id`,`total_price`,`service_id`, `status_admin`, `onway`, `onway_type`, `model_id`, `app_date`  FROM `repairs` WHERE `return_id` = '.$id.';');
      while ($row = mysqli_fetch_array($sql)) {

      $model = model_info($row['model_id']);

      if ($model['brand'] == 'HARPER' || $model['brand'] == 'NESONS' || $model['brand'] == 'OLTO' && $row['app_date'] < '2019.10.01') {
      $summ += $row['total_price'];
      $summ += parts_price_billing($row['id']);
      $summ += (($row['status_admin'] != '������ �� �������� ������' && $row['onway'] == 1) ? get_price_transfer($model['cat'], $row['onway_type'], $row['service_id']) : '0');
      mysqli_query($db, 'UPDATE `repairs` SET `app_date` = "2019.10.01", `approve_date` = "2019-10-01" where `id` = '.$row['id']);
      echo $row['id'].' - '.$summ.' - '.$row['app_date'].'<br>';
      $total_sum += $summ;
      unset($summ);

      }

      }

}

/*$sql = mysqli_query($db, 'SELECT `id`,`total_price`, `status_admin`, `return_id`, `master_app_date`, `onway`, `onway_type`, `model_id`, `app_date`  FROM `repairs` WHERE `service_id` = 33 and `app_date` REGEXP \'2019.09.\' and return_id != \'\';');
      while ($row = mysqli_fetch_array($sql)) {

      $model = model_info($row['model_id']);

      if (($model['brand'] == 'HARPER' || $model['brand'] == 'OLTO') && $row['app_date'] <= '2019.10.01') {
      $summ += $row['total_price'];
      $summ += parts_price_billing($row['id']);
      $summ += (($row['status_admin'] != '������ �� �������� ������' && $row['onway'] == 1) ? get_price_transfer($model['cat'], $row['onway_type']) : '0');

      if ($row['master_app_date'] >= '2019.10.01') {
      //echo $row['id'].' - '.$summ.' - '.$row['app_date'].' - '.$row['master_app_date'].'<br>';
      echo $row['return_id'].'<br>';
      $total_sum += $summ;
      }

      unset($summ);

      }

      }

    */
//

echo $total_sum;
?>