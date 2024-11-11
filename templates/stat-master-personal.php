<?php

use models\staff\Staff;
use models\User;
use program\core\App;

$_GET['master_id'] = User::getData('id');
$_GET['date'] = (empty(App::$URLParams['date'])) ? date('Y.m') : App::$URLParams['date'];

function model($id) {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `models` where `id` = '.$id);
      while ($row = mysqli_fetch_array($sql)) {
      $content = $row;

      }
    return $content;
}

function total_month() {
  global $db;
if ($_GET['date']) {
$date = $_GET['date'];
} else {
//$date = ((date("d") < 5) ? date("Y.m", strtotime("-1 months")) : date("Y.m"));
$date = ((date("d") < 5) ? date("Y.m") : date("Y.m"));
}
if ($_GET['master_id']) {
$master_id = ' and `master_user_id` = '.$_GET['master_id'];
}
$sql = mysqli_query($db, 'SELECT  COUNT(*) FROM `repairs` where `master_app_date` REGEXP \''.$date.'\' and `deleted` = 0 and (`status_admin` = \'Подтвержден\' OR `status_admin` = \'Выдан\') and `deleted` = 0 and `service_id` = 33 '.$master_id.'  order by `id` DESC;');
return mysqli_fetch_array($sql)['COUNT(*)'];
}

function total_month_by_user($id) {
  global $db;
if ($_GET['date']) {
$date = $_GET['date'];
} else {
//$date = ((date("d") < 5) ? date("Y.m", strtotime("-1 months")) : date("Y.m"));
$date = date("Y.m");
}
if ($id == 33) { $id = 0;}
if (isset($id)) {
$master_id = ' and `master_user_id` = '.$id;

}
$sql = mysqli_query($db, 'SELECT  COUNT(*) FROM `repairs` where `master_app_date` REGEXP \''.$date.'\' and (`status_admin` = \'Подтвержден\' OR `status_admin` = \'Выдан\') and `deleted` = 0 and `service_id` = 33 '.$master_id.'  order by `id` DESC;');
return mysqli_fetch_array($sql)['COUNT(*)'];
}

function total_pay_month() {
  global $db;
//$dated = ((date("d") < 5) ? date("Y.m", strtotime("-1 months")) : date("Y.m"));
if ($_GET['date']) {
$date = $_GET['date'];
} else {
$date = ((date("d") < 5) ? date("Y.m") : date("Y.m"));
}
if ($date) {
$where_date = ' `master_app_date` REGEXP \''.$date.'\'';

/*$date11 = DateTime::createFromFormat("Y.m", $date);
$date11->modify('-1 month');
 */

}
if ($_GET['master_id']) {
$master_id = ' and `master_user_id` = '.$_GET['master_id'];
}

$sql = mysqli_query($db, 'SELECT * FROM `repairs` where  '.$where_date.' '.$master_id.' and (`status_admin` = \'Подтвержден\' OR `status_admin` = \'Выдан\') and `deleted` = 0 and `service_id` = 33 order by `id` DESC;');
      while ($row = mysqli_fetch_array($sql)) {
      //$info = get_request_info($row['id']);
      //$city = get_city($info['city']);

      if($row['master_app_date']) {


      $exp = explode('.', $row['master_app_date']);
      $app[$exp['0']][$exp[1]] = '';

      }


      }

      foreach ($app as $year => $val) {
      $year_work = $val;
       foreach ($year_work as $month => $value) {

     $sql2 = mysqli_query($db, 'SELECT * FROM `repairs` where `master_app_date` REGEXP \''.$year.'.'.$month.'\' '.$master_id.' and (`status_admin` = \'Подтвержден\' OR `status_admin` = \'Выдан\') and `deleted` = 0 and `service_id` = 33 GROUP by `service_id`  order by `id` DESC ;');

      while ($row2 = mysqli_fetch_array($sql2)) {
      if ($_GET['impo'] != 1) {

      if ($_GET['master_id']) {
      $summ = get_service_summ_master_stat_appdate($row2['service_id'], $month, $year, $_GET['master_id']);
      }  else {
      $summ = get_service_summ_stat_appdate($row2['service_id'], $month, $year);
      }

      $total += $summ;
       }
       }

     }
     }
     if (!$total) {$total = 0;}
     return $total;
}

function total_pay_month_by_user($id) {
  global $db;
//$dated = ((date("d") < 5) ? date("Y.m", strtotime("-1 months")) : date("Y.m"));
if ($_GET['date']) {
$date = $_GET['date'];
} else {
$date = ((date("d") < 5) ? date("Y.m") : date("Y.m"));
}
if ($date) {
$where_date = ' `master_app_date` REGEXP \''.$date.'\'';

/*$date11 = DateTime::createFromFormat("Y.m", $date);
$date11->modify('-1 month');

echo ((date("d") > 5) ? $date11->format('Y.m').'.05' : $date.'.05');
echo ((date("d") > 5) ? $date.'.'.date('d') : $date.'.'.date('d')); */

}
if ($id == 33) { $id = 0;}
if (isset($id)) {
$master_id = ' and `master_user_id` = '.$id;
}

$sql = mysqli_query($db, 'SELECT * FROM `repairs` where  '.$where_date.' '.$where_year.' '.$master_id.' and (`status_admin` = \'Подтвержден\' OR `status_admin` = \'Выдан\') and `deleted` = 0 and `service_id` = 33 order by `id` DESC;');
      while ($row = mysqli_fetch_array($sql)) {
      //$info = get_request_info($row['id']);
      //$city = get_city($info['city']);
      $i2++;

      if($row['master_app_date']) {


      $exp = explode('.', $row['master_app_date']);
      $app[$exp['0']][$exp[1]] = '';

      }


      }

      foreach ($app as $year => $val) {
      $year_work = $val;
       foreach ($year_work as $month => $value) {

     $sql2 = mysqli_query($db, 'SELECT * FROM `repairs` where `master_app_date` REGEXP \''.$year.'.'.$month.'\' '.$master_id.' and (`status_admin` = \'Подтвержден\' OR `status_admin` = \'Выдан\') and `deleted` = 0 and `service_id` = 33 GROUP by `service_id`  order by `id` DESC ;');

      while ($row2 = mysqli_fetch_array($sql2)) {
      if ($_GET['impo'] != 1) {

      if (isset($id)) {
      $summ = get_service_summ_master_stat_appdate($row2['service_id'], $month, $year, $id);
      }  else {
      //$summ = get_service_summ_stat($row2['service_id'], $month, $year);
      }

      $total += $summ;
       }
       }

     }
     }
     if (!$total) {$total = 0;}
     return $total;
}

function total_pay_only_month($month_select) {
  global $db;
//$dated = ((date("d") < 5) ? date("Y.m", strtotime("-1 months")) : date("Y.m"));
if ($month_select) {
$date = $month_select;
}
if ($date) {
$where_date = ' `master_app_date` REGEXP \''.$date.'\'';

/*$date11 = DateTime::createFromFormat("Y.m", $date);
$date11->modify('-1 month');

echo ((date("d") > 5) ? $date11->format('Y.m').'.05' : $date.'.05');
echo ((date("d") > 5) ? $date.'.'.date('d') : $date.'.'.date('d')); */

}
if ($_GET['master_id']) {
$master_id = ' and `master_user_id` = '.$_GET['master_id'];
}

$sql = mysqli_query($db, 'SELECT * FROM `repairs` where  '.$where_date.' '.$where_year.' '.$master_id.' and (`status_admin` = \'Подтвержден\' OR `status_admin` = \'Выдан\') and `deleted` = 0 and `service_id` = 33 order by `id` DESC;');
      while ($row = mysqli_fetch_array($sql)) {
      //$info = get_request_info($row['id']);
      //$city = get_city($info['city']);
      $i2++;

      if($row['master_app_date']) {


      $exp = explode('.', $row['master_app_date']);
      $app[$exp['0']][$exp[1]] = '';

      }


      }


      foreach ($app as $year => $val) {
      $year_work = $val;
       foreach ($year_work as $month => $value) {

     $sql2 = mysqli_query($db, 'SELECT * FROM `repairs` where `master_app_date` REGEXP \''.$year.'.'.$month.'\' '.$master_id.' and (`status_admin` = \'Подтвержден\' OR `status_admin` = \'Выдан\') and `deleted` = 0 and `service_id` = 33 GROUP by `service_id`  order by `id` DESC ;');

      while ($row2 = mysqli_fetch_array($sql2)) {
      if ($_GET['impo'] != 1) {
      $summ = get_service_summ_master_stat_appdate($row2['service_id'], $month, $year, $_GET['master_id']);
      $total += $summ;
       }
       }

     }
     }
     if (!$total) {$total = 0;}
     return $total;
}

function total_pay_only_month_user($month_select, $id) {
  global $db;
//$dated = ((date("d") < 5) ? date("Y.m", strtotime("-1 months")) : date("Y.m"));
if ($month_select) {
$date = $month_select;
}
if ($date) {
$where_date = ' `master_app_date` REGEXP \''.$date.'\'';

/*$date11 = DateTime::createFromFormat("Y.m", $date);
$date11->modify('-1 month');

echo ((date("d") > 5) ? $date11->format('Y.m').'.05' : $date.'.05');
echo ((date("d") > 5) ? $date.'.'.date('d') : $date.'.'.date('d')); */

}
if ($id == 33) { $id = 0;}
if (isset($id)) {
$master_id = ' and `master_user_id` = '.$id;
}

$sql = mysqli_query($db, 'SELECT * FROM `repairs` where  '.$where_date.' '.$where_year.' '.$master_id.' and (`status_admin` = \'Подтвержден\' OR `status_admin` = \'Выдан\') and `deleted` = 0 and `service_id` = 33 order by `id` DESC;');
      while ($row = mysqli_fetch_array($sql)) {
      //$info = get_request_info($row['id']);
      //$city = get_city($info['city']);
      $i2++;

      if($row['master_app_date']) {


      $exp = explode('.', $row['master_app_date']);
      $app[$exp['0']][$exp[1]] = '';

      }


      }

      foreach ($app as $year => $val) {
      $year_work = $val;
       foreach ($year_work as $month => $value) {

     $sql2 = mysqli_query($db, 'SELECT * FROM `repairs` where `master_app_date` REGEXP \''.$year.'.'.$month.'\' '.$master_id.' and (`status_admin` = \'Подтвержден\' OR `status_admin` = \'Выдан\') and `deleted` = 0 and `service_id` = 33 GROUP by `service_id`  order by `id` DESC ;');

      while ($row2 = mysqli_fetch_array($sql2)) {
      if ($_GET['impo'] != 1) {
      $summ = get_service_summ_master_stat_appdate($row2['service_id'], $month, $year, $id);
      $total += $summ;
       }
       }

     }
     }
     if (!$total) {$total = 0;}
     return $total;
}

function count_pay_master($pay, $user_id = '') {
$id = ($_GET['master_id'] != '') ? $_GET['master_id'] : $user_id;
$master = Staff::getStaff(['id' => $id]);
$percent = $master['percent'];

/*if ($old == 1) {

if ($salary < $pay) {
return $salary+(($pay-$salary)*0.3);
} else {
return $salary;
}

} else if ($old == 0 && $old == '') { */

return $pay*($percent/100);

//}



}

function total_user() {
  global $db, $_monthsList;
  $repairs = $body = '';
//$dated = ((date("d") < 5) ? date("Y.m", strtotime("-1 months")) : date("Y.m"));

$date = $_GET['date'];
$where_date = ' `master_app_date` REGEXP \''.$date.'\' and ';


$sql = mysqli_query($db, 'SELECT * FROM `repairs` where  '.$where_date.' (`status_admin` = \'Подтвержден\' OR `status_admin` = \'Выдан\') and `deleted` = 0 and `service_id` = 33 and `master_user_id` = '.$_GET['master_id'].' order by `id` DESC LIMIT 5000');
      while ($row = mysqli_fetch_array($sql)) {
      if($row['master_app_date']) {
      $exp = explode('.', $row['master_app_date']);
      $app[$exp['0']][$exp[1]] = '';
      }

      }

      foreach ($app as $year => $val) {
      $year_work = $val;
       foreach ($year_work as $month => $value) {

     $sql2 = mysqli_query($db, 'SELECT * FROM `repairs` where `master_app_date` REGEXP \''.$year.'.'.$month.'\' and (`status_admin` = \'Подтвержден\' OR `status_admin` = \'Выдан\') and `deleted` = 0 and `service_id` = 33 and `master_user_id` = '.$_GET['master_id'].'  order by `id` DESC ;');
      $count = mysqli_num_rows($sql2);
      $summ = get_service_summ_stat_appdate(33, $month, $year);
      if (!$summ) {$summ = 0;}
      $total_pay = total_pay_only_month($year.'.'.$month);

if (mysqli_num_rows($sql2) != false) {
      $repairs .= '<tr>
      <td>Модель</td>
      <td>Серийник</td>
      <td>РСЦ</td>
      <td>ID</td>
      <td>Дата приёма</td>
      <td>Сумма</td>
      <td>Клиент</td>
      <td>Дефект</td>
      <td>Дата начала</td>
      <td>Дата завершения</td>
      </tr>';
      while ($row2 = mysqli_fetch_array($sql2)) {        
        $zeroFlag = '';
      if(!$row2['total_price']){
        $zeroFlag = 'style="background-color: yellow"';
      }
      $repairs .= '<tr '.$zeroFlag.'>';
 
      $model = model($row2['model_id']);

      if ($row2['create_date'] != '0000-00-00 00:00:00') {

      try {
          $date_created = new DateTime($row2['create_date']);
          $date_ready_created = $date_created->format("d.m.Y");
      } catch (Exception $e) {

      }
      }

      if ($row2['client_id']) {
      $client_info = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `clients` WHERE `id` = \''.mysqli_real_escape_string($db, $row2['client_id']).'\' LIMIT 1;'));
      } else {
      $client_info['name'] = htmlentities($row2['name_shop']);
      }


      $repairs .= '<td>'.$model['name'].'</td>';
      $repairs .= '<td>'.$row2['serial'].'</td>';
      $repairs .= '<td>'.$row2['rsc'].'</td>';
      $repairs .= '<td>'.$row2['id'].'</td>';
      $repairs .= '<td>'.$date_ready_created.'</td>';
      $repairs .= '<td>'.$row2['total_price'].'</td>';
      $repairs .= '<td>'.$client_info['name'].'</td>';
      $repairs .= '<td>'.$row2['bugs'].'</td>';
      $repairs .= '<td>'.\program\core\Time::format($row2['begin_date']).'</td>';
      $repairs .= '<td>'.date_format(date_create_from_format('Y.m.d', $row2['master_app_date']), 'd.m.Y').'</td>';
      $repairs .= '</tr>';
      unset($date_ready_created);

      }
      }
      $old = ($month <= 8) ? 1 : 0;
      $body .= '<tr><td>'.$_monthsList[$month].'</td>
                <td style="text-align:center;">'.$count.'</td>
                <td style="text-align:center;">'.repair_nice2($_GET['master_id']).'%</td>
                <td style="text-align:center;">'.$total_pay.'р.</td>
                <td style="text-align:center;">'.count_pay_master($total_pay, '', $old).'р.</td>
                <td><table>
                '.$repairs.'
                </table></td>
                </tr>';
     unset($total_pay);
     unset($repairs);
     }
     }
     //if (!$total) {$total = 0;}
     return $body;
}

function total_user_by_id($id) {
  global $db;
//$dated = ((date("d") < 5) ? date("Y.m", strtotime("-1 months")) : date("Y.m"));
if ($_GET['date']) {
$date = $_GET['date'];
} else {
$date = ((date("d") < 5) ? date("Y.m") : date("Y.m"));
}
if ($date) {
$where_date = ' `master_app_date` REGEXP \''.$date.'\' and ';


}


$sql = mysqli_query($db, 'SELECT * FROM `repairs` where  '.$where_date.' (`status_admin` = \'Подтвержден\' OR `status_admin` = \'Выдан\') and `deleted` = 0 and `service_id` = 33 and `master_user_id` = '.$id.' order by `id` DESC;');
      while ($row = mysqli_fetch_array($sql)) {

      $i2++;

      if($row['master_app_date']) {


      $exp = explode('.', $row['master_app_date']);
      $app[$exp['0']][$exp[1]] = '';

      }


      }


      foreach ($app as $year => $val) {
      $year_work = $val;
       foreach ($year_work as $month => $value) {

     $sql2 = mysqli_query($db, 'SELECT * FROM `repairs` where `master_app_date` REGEXP \''.$year.'.'.$month.'\' and (`status_admin` = \'Подтвержден\' OR `status_admin` = \'Выдан\') and `deleted` = 0 and `service_id` = 33 and `master_user_id` = '.$id.'  order by `id` DESC ;');
      $summ = get_service_summ_stat_appdate(33, $month, $year);
      if (!$summ) {$summ = 0;}
      $total_pay = total_pay_only_month_user($year.'.'.$month,$id);
      $old = ($month <= 8) ? 1 : 0;
      $body += count_pay_master($total_pay, $id, $old);

     unset($total_pay);
     unset($repairs);
     }
     }
     //if (!$total) {$total = 0;}
     return $body;
}



function total_pay_month_minus() {
  global $db;
$dated = ((date("d") < 5) ? date("Y.m") : date("Y.m"));
if ($_GET['date']) {
$date = $_GET['date'];
} else {
$date = ((date("d") < 5) ? date("Y.m") : date("Y.m"));
}
if ($date) {
$where_date = 'and `master_app_date` REGEXP \''.$date.'\'';

$date11 = DateTime::createFromFormat("Y.m", $date);
//$date11->modify('-1 month');

if (date("d") > 5) {
$minus = date("d")-5;
$month = 5;
$total = 0;
while ($minus >= $total) {
if ($month < 10) {
$month_real = '0'.$month;
} else {
$month_real = $month;
}
$in[] = '\''.$date11->format('Y.m').'.'.$month_real.'\'';
$total++;
$month++;
}

$imploded = implode(',', $in);

} else {

$minus = date("j");
$month = $date11->format('m');
$total = 1;
while ($minus >= $total) {
if ($minus < 10) {
$month_real = '0'.$total;
} else {
$month_real = $total;
}
$in[] = '\''.$date11->format('Y.m').'.'.$month_real.'\'';
$total++;
}

$imploded = implode(',', $in);
}

}
$sql = mysqli_query($db, 'SELECT * FROM `repairs` where `master_app_date` IN ('.$imploded.')  and `status_admin` = \'Подтвержден\' and `deleted` = 0 order by `id` DESC;');
      while ($row = mysqli_fetch_array($sql)) {
      //$info = get_request_info($row['id']);
      //$city = get_city($info['city']);
      if($row['master_app_date']) {


      $exp = explode('.', $row['master_app_date']);
      $app[$exp['0']][$exp[1]] = '';

      }


      }

      foreach ($app as $year => $val) {
      $year_work = $val;
       foreach ($year_work as $month => $value) {

     $sql2 = mysqli_query($db, 'SELECT * FROM `repairs` where DATE(master_app_date) BETWEEN (str_to_date(\''.$first.'\',\'%Y.%m.%d\') AND str_to_date(\''.$second.'\',\'%Y.%m.%d\')) and `status_admin` = \'Подтвержден\' and `deleted` = 0 and `master_app_date` NOT REGEXP \''.$dated.'\' GROUP by `service_id`  order by `id` DESC ;');

     while ($row2 = mysqli_fetch_array($sql2)) {
      if ($_GET['impo'] != 1) {
      $summ = get_service_summ_stat_appdate($row2['service_id'], $month, $year);
      $total += $summ;
       }
       }

     }
     }
     return $total;
}

function total_month_minus() {
  global $db;
if ($_GET['date']) {
$date = $_GET['date'];
} else {
$date = ((date("d") < 5) ? date("Y.m") : date("Y.m"));
}

if ($date) {
$where_date = 'and `master_app_date` REGEXP \''.$date.'\'';

$date11 = DateTime::createFromFormat("Y.m", $date);
//$date11->modify('-1 month');

if (date("d") > 5) {
$minus = date("d")-5;
$month = $date11->format('m');
$total = 0;
while ($minus >= $total) {
if ($month < 10) {
$month_real = '0'.$month;
} else {
$month_real = $month;
}
$in[] = '\''.$date11->format('Y.m').'.'.$month_real.'\'';
$total++;
$month++;
}

$imploded = implode(',', $in);

} else {

$minus = date("j");
$month = $date11->format('m');
$total = 1;
while ($minus >= $total) {
if ($minus < 10) {
$month_real = '0'.$total;
} else {
$month_real = $total;
}
$in[] = '\''.$date11->format('Y.m').'.'.$month_real.'\'';
$total++;
}

$imploded = implode(',', $in);

}


}

$sql = mysqli_query($db, 'SELECT  COUNT(*) FROM `repairs` where `master_app_date` IN ('.$imploded.')  and `deleted` = 0 and `status_admin` = \'Подтвержден\'  order by `id` DESC;');

return mysqli_fetch_array($sql)['COUNT(*)'];
}

function top5_repairs_month() {
  global $db;
if ($_GET['date']) {
$date = $_GET['date'];
$date11 = DateTime::createFromFormat("Y.m", $_GET['date']);
//$date11->modify('-1 month');
$date2 = ((date("d") < 5) ? $date11->format('Y.m') : $date);
} else {
$date = ((date("d") < 5) ? date("Y.m") : date("Y.m"));
$date2 = ((date("d") < 5) ? date("Y.m", strtotime("-1 months")) : date("Y.m", strtotime("-1 months")));
}



$sql2 = mysqli_query($db, 'SELECT * FROM `repairs` where `master_app_date` REGEXP \''.$date.'\' and `status_admin` = \'Подтвержден\' and `deleted` = 0 order by `id` DESC LIMIT 7;');
while ($row2 = mysqli_fetch_array($sql2)) {
      $req = get_request_info_serice($row2['service_id']);
      $model = model($row2['model_id']);

      if ($row2['create_date'] != '0000-00-00 00:00:00') {

      try {
          $date_created = new DateTime($row2['create_date']);
          $date_ready_created = $date_created->format("d.m.Y");
      } catch (Exception $e) {

      }
      }

      $body .= '<tr><td><a target="_blank" href="/edit-repair/'.$row2['id'].'/">'.$row2['id'].'</a></td><td>'.$date_ready_created.'</td><td>'.$req['name'].'</td><td>'.$model['name'].'</td></tr>';

      }
return $body;
}

function top5_services_month() {
  global $db;
if ($_GET['date']) {
$date = $_GET['date'];
$date11 = DateTime::createFromFormat("Y.m", $_GET['date']);
$date11->modify('-1 month');
$date2 = ((date("d") < 5) ? $date11->format('Y.m') : $date);
} else {
$date = ((date("d") < 5) ? date("Y.m") : date("Y.m"));
$date2 = ((date("d") < 5) ? date("Y.m", strtotime("-1 months")) : date("Y.m", strtotime("-1 months")));
}


$sql1 = mysqli_query($db, 'SELECT * FROM `repairs` where `master_app_date` REGEXP \''.$date2.'\' and `status_admin` = \'Подтвержден\' and `deleted` = 0 order by `id` DESC ;');
      while ($row1 = mysqli_fetch_array($sql1)) {
      $services1[$row1['service_id']] += 1;
      $total1 += 1;
      }
arsort($services1);
$id1 = 1;
foreach ($services1 as $service_id1 => $count1) {

$prev[$service_id1] = $id1;

if ($id1 == 10) {
break;
}
$id1++;
}


$sql2 = mysqli_query($db, 'SELECT * FROM `repairs` where `master_app_date` REGEXP \''.$date.'\' and `status_admin` = \'Подтвержден\' and `deleted` = 0 order by `id` DESC ;');
      while ($row2 = mysqli_fetch_array($sql2)) {
      $services[$row2['service_id']] += 1;
      $total += 1;
      }
arsort($services);
$id = 1;
foreach ($services as $service_id => $count) {
$req = get_request_info_serice($service_id);
if ($prev[$service_id] && $prev[$service_id] > $id) {
$change = '<strong>(+<span style="color:green;">'.($prev[$service_id]-$id).'</span>)</strong>';
}
if ($prev[$service_id] && $prev[$service_id] < $id) {
$change = '<strong>(-<span style="color:red;">'.($id-$prev[$service_id]).'</span>)</strong>';
}

$body .= '<tr><td>'.$req['name'].' '.$change.'</a></td><td style="text-align:center;">'.$count.'</td><td>'.round(($count/$total)*100).'%</td></tr>';
unset($change);
if ($id == 10) {
break;
}
$id++;
}


return $body;
}

function total_models_month() {
  global $db;
if ($_GET['date']) {
$date = $_GET['date'];
$date11 = DateTime::createFromFormat("Y.m", $_GET['date']);
$date11->modify('-1 month');
$date2 = ((date("d") < 5) ? $date11->format('Y.m') : $date);
} /*else {
$date = ((date("d") < 5) ? date("Y.m") : date("Y.m"));
$date2 = ((date("d") < 5) ? date("Y.m", strtotime("-1 months")) : date("Y.m", strtotime("-1 months")));
} */

$masters = Staff::getMasters();
      foreach($masters as $row) {
      $stat = get_repair_stats_by_user($row['id']);

        $plan = plan_value_by_user($row['id']);
        $summ = total_pay_month_by_user($row['id']);

        if ($plan > $summ && $plan != 0) {
        $planz = round($summ/$plan*100);
        } else if ($plan < $summ  && $plan != 0) {
        $planz = round($summ/$plan*100);
        } else {
        $planz = 0;
        }

      $body .= '<tr>
      <td>'.$row['surname'].' '.$row['name'].' '.$row['thirdname'].'</td>
                  <td style="text-align:center">'.total_month_by_user($row['id']).'</td>
                  <td style="text-align:center">'.$stat['repair'].'</td>
                  <td style="text-align:center">'.$stat['ato'].'</td>
                  <td style="text-align:center">'.$stat['anrp'].'</td>
                  <td style="text-align:center">'.total_pay_month_by_user($row['id']).'</td>
                  <td style="text-align:center">'.total_user_by_id($row['id']).'</td>
                  <td style="text-align:center">'.repair_nice2($row['id']).'%</td>
      <td style="    background: none;    text-align: center;     color: #fff;     height: 20px;      position: relative;    margin: 60px 0 20px 0;  "><span style="width:'.str_replace(',', '.', $planz).'%;  display: block;background-color:#80bd03;height:100%;"><span style="position:absolute;left:45%;color:#000">'.$planz.'%</span></span></td>
      </tr>';
      }




return $body;
}

function total_cities_month() {
  global $db;
if ($_GET['date']) {
$date = $_GET['date'];
$date11 = DateTime::createFromFormat("Y.m", $_GET['date']);
$date11->modify('-1 month');
$date2 = ((date("d") < 5) ? $date11->format('Y.m') : $date);
} else {
$date = ((date("d") < 5) ? date("Y.m") : date("Y.m"));
$date2 = ((date("d") < 5) ? date("Y.m", strtotime("-1 months")) : date("Y.m", strtotime("-1 months")));
}


$sql1 = mysqli_query($db, 'SELECT * FROM `repairs` where `master_app_date` REGEXP \''.$date2.'\' and `status_admin` = \'Подтвержден\' and `deleted` = 0 order by `id` DESC ;');
      while ($row1 = mysqli_fetch_array($sql1)) {
      $services1[$row1['service_id']] += 1;
      $total1 += 1;
      }


foreach ($services1 as $service_id1 => $count1) {
$req1 = get_request_info_serice($service_id1);
$cities1[$req1['city']] += $count1;
}

arsort($cities1);
$id1 = 1;
foreach ($cities1 as $city_id1 => $count1) {

$prev[$city_id1] = $id1;

if ($id1 == 10) {
break;
}
$id1++;
}

//print_r($prev);

$sql2 = mysqli_query($db, 'SELECT * FROM `repairs` where `master_app_date` REGEXP \''.$date.'\' and `status_admin` = \'Подтвержден\' and `deleted` = 0 order by `id` DESC ;');
      while ($row2 = mysqli_fetch_array($sql2)) {
      $services[$row2['service_id']] += 1;
      $total += 1;
      }


foreach ($services as $service_id => $count) {
$req = get_request_info_serice($service_id);
$cities[$req['city']] += $count;
}


arsort($cities);
$id = 1;
foreach ($cities as $city_id => $count) {
$city = get_city($city_id);

if ($prev[$city_id] && $prev[$city_id] > $id) {
$change = '<strong>(+<span style="color:green;">'.($prev[$city_id]-$id).'</span>)</strong>';
}
if ($prev[$city_id] && $prev[$city_id] < $id) {
$change = '<strong>(-<span style="color:red;">'.($id-$prev[$city_id]).'</span>)</strong>';
}

$body .= '<tr><td style="width:250px;">'.$city['fcity_name'].' '.$change.'</a></td><td style="text-align:center;">'.$count.'</td><td style="width:50px;">'.round(($count/$total)*100).'%</td></tr>';
unset($change);
if ($id == 10) {
break;
}
$id++;
}

return $body;
}

function get_repair_sum_stats()
{
  global $db;
  $labels = [1 => 'Блочный ремонт', 2 => 'Компонентный ремонт', 3 => 'Замена аксессуаров', 5 => 'АТО', 4 => 'АНРП'];
  $sum = [];
  $blocks = ['sum' => '', 'labels' => ''];
  if ($_GET['date']) {
    $date = $_GET['date'];
  } else {
    $date = date('Y.m');
  }
  if ($_GET['master_id']) {
    $master_id = ' and `master_user_id` = ' . $_GET['master_id'];
  }
  $formatFn = function($val) {
    return round($val);
  };
  foreach ($labels as $type => $name) {
    $sql = mysqli_query($db, 'SELECT SUM(total_price) AS sum, COUNT(*) AS cnt FROM `repairs` where `service_id` = 33 and `repair_type_id` = ' . $type . ' and `master_app_date` REGEXP "' . $date . '" ' . $master_id . ' and `deleted` = 0;');
    $r = mysqli_fetch_array($sql);
    if(!$r['sum']){
      unset($labels[$type]);
      continue;
    }
    $sum[] = Staff::getWorkCost($_GET['master_id'], $r['sum'], $formatFn);
    $labels[$type] .= ' (' . $r['cnt'] . ' шт.)';
  }
  $blocks['labels'] = implode(',', array_map(function ($item) { return '"' . $item . '"';}, $labels));
  $blocks['sum'] = implode(',', $sum);
  $blocks['labels_list'] = $labels;
  return $blocks;
}

function get_repair_stats() {
  global $db;
  $labels = ['Ремонт', 'АТО', 'АНРП'];
  $cnt = [];
if ($_GET['date']) {
$date = $_GET['date'];
} else {
$date = date("Y.m");
}
if ($_GET['master_id']) {
$master_id = ' and `master_user_id` = '.$_GET['master_id'];
}
$keys = ['IN (1,2,3)', '= 5', '= 4'];
foreach($keys as $i => $k){
  $sql = mysqli_query($db, 'SELECT COUNT(*) AS cnt FROM `repairs` where `service_id` = 33 and `repair_type_id` '.$k.' and `master_app_date` REGEXP \''.$date.'\' '.$master_id.' and `deleted` != 1;');
  $r = mysqli_fetch_array($sql);
  if(!$r['cnt']){
    unset($labels[$i]);
    continue;
  }
  $cnt[] = (int)$r['cnt'];
  $labels[$i] .= ' (' . $r['cnt'] . ' шт.)';
}
$blocks = ['data' => '', 'labels' => ''];
$blocks['labels'] = implode(',', array_map(function ($item) { return '"' . $item . '"';}, $labels));
$blocks['data'] = implode(',', $cnt);
$blocks['labels_list'] = $labels;
return $blocks;
}

function get_trash_stats() {
  global $db;
if ($_GET['date']) {
$date = $_GET['date'];
} else {
$date = ((date("d") < 5) ? date("Y.m") : date("Y.m"));
}
if ($_GET['master_id']) {
$master_id = ' and `master_user_id` = '.$_GET['master_id'];
}

$sql = mysqli_query($db, 'SELECT `model_id` FROM `repairs` WHERE `deleted` = 0 and `service_id` = 33 and (`status_admin` = \'Подтвержден\' or `status_admin` = \'Выдан\') and `problem_id` IN (3,6,14,15,16,18,19,24,25,26,27,28,29,30,31,33,35,39,41,43) and `master_app_date` REGEXP \''.$date.'\' '.$master_id.' ;');
      while ($row = mysqli_fetch_array($sql)) {
      $model = model_info($row['model_id']);
      if ($model['brand'] == 'HARPER' || $model['brand'] == 'OLTO' || $model['brand'] == 'SKYLINE') {
      $models[$model['name']] += 1;
      }
      }

//print_r($models);

foreach ($models as $model => $value) {

if ($value > 3) {

$blocks['stat'] .= $value.', ';
$blocks['model'] .= '\''.$model.'\', ';
$blocks['color'] .= '\'rgb(' . rand(0,255) . ',' . rand(0,255) . ',' . rand(0, 255) . ')\', ';

}


}

return $blocks;
}

function get_resell_stats() {
  global $db;
if ($_GET['date']) {
$date = $_GET['date'];
} else {
$date = ((date("d") < 5) ? date("Y.m") : date("Y.m"));
}
if ($_GET['master_id']) {
$master_id = ' and `master_user_id` = '.$_GET['master_id'];
}

$sql = mysqli_query($db, 'SELECT `model_id` FROM `repairs` WHERE `deleted` = 0 and `service_id` = 33 and (`status_admin` = \'Подтвержден\' or `status_admin` = \'Выдан\') and `problem_id` IN (2,4,7,8,9,10,11,12,17,20,21,36,37,40,42) and `master_app_date` REGEXP \''.$date.'\' '.$master_id.' ;');
      while ($row = mysqli_fetch_array($sql)) {
      $model = model_info($row['model_id']);
      if ($model['brand'] == 'HARPER' || $model['brand'] == 'OLTO' || $model['brand'] == 'NESONS' || $model['brand'] == 'SKYLINE') {
      $models[$model['name']] += 1;
      }

      }

foreach ($models as $model => $value) {

if ($value > 1) {

$blocks['stat'] .= $value.', ';
$blocks['model'] .= '\''.$model.'\', ';
$blocks['color'] .= '\'rgb(' . rand(0,255) . ',' . rand(0,255) . ',' . rand(0, 255) . ')\', ';

}


}

return $blocks;
}

function get_repair_stats_by_user($id) {
  global $db;
if ($_GET['date']) {
$date = $_GET['date'];
} else {
$date = ((date("d") < 5) ? date("Y.m") : date("Y.m"));
}
if ($id == 33) { $id = 0;}
if (isset($id)) {
$master_id = ' and `master_user_id` = '.$id;
}
$sql = mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` where `repair_type_id` = 5 and `service_id` = 33 and (`status_admin` = \'Подтвержден\' OR `status_admin` = \'Выдан\') and `master_app_date` REGEXP \''.$date.'\' '.$master_id.' and `deleted` != 1;');
$blocks['ato'] = mysqli_fetch_array($sql)['COUNT(*)'];
$sql = mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` where `repair_type_id` = 4 and `service_id` = 33 and (`status_admin` = \'Подтвержден\' OR `status_admin` = \'Выдан\') and `master_app_date` REGEXP \''.$date.'\' '.$master_id.' and `deleted` != 1;');
$blocks['anrp'] = mysqli_fetch_array($sql)['COUNT(*)'];
$sql = mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` where `repair_type_id` IN (1,2,3) and `service_id` = 33 and (`status_admin` = \'Подтвержден\' OR `status_admin` = \'Выдан\') and `master_app_date` REGEXP \''.$date.'\' '.$master_id.' and `deleted` != 1;');
$blocks['repair'] = mysqli_fetch_array($sql)['COUNT(*)'];
/*$sql = mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` where `repair_type_id` = 2 and `master_app_date` REGEXP \''.$date.'\' and `deleted` != 1;');
$blocks .= mysqli_fetch_array($sql)['COUNT(*)'].', ';
$sql = mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` where `repair_type_id` = 3 and `master_app_date` REGEXP \''.$date.'\' and `deleted` != 1;');
$blocks .= mysqli_fetch_array($sql)['COUNT(*)']; */
return $blocks;
}

function repair_nice() {
  global $db;
if ($_GET['date']) {
$date = $_GET['date'];
} else {
$date = ((date("d") < 5) ? date("Y.m") : date("Y.m"));
}
if ($_GET['master_id']) {
$master_id = ' and `master_user_id` = '.$_GET['master_id'];
}
$sql = mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` where `repair_type_id` IN (4) and `service_id` = 33 and (`status_admin` = \'Подтвержден\' OR `status_admin` = \'Выдан\') and `master_app_date` REGEXP \''.$date.'\' and `deleted` != 1 '.$master_id.';');
$ato = mysqli_fetch_array($sql)['COUNT(*)'];
$sql = mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` where `repair_type_id` IN (1,2,3,5) and `service_id` = 33 and (`status_admin` = \'Подтвержден\' OR `status_admin` = \'Выдан\') and `master_app_date` REGEXP \''.$date.'\' and `deleted` != 1 '.$master_id.';');
$repair = mysqli_fetch_array($sql)['COUNT(*)'];

/*$sql = mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` where `repair_type_id` = 2 and `master_app_date` REGEXP \''.$date.'\' and `deleted` != 1;');
$blocks .= mysqli_fetch_array($sql)['COUNT(*)'].', ';
$sql = mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` where `repair_type_id` = 3 and `master_app_date` REGEXP \''.$date.'\' and `deleted` != 1;');
$blocks .= mysqli_fetch_array($sql)['COUNT(*)']; */
return round($repair/($repair+$ato)*100).'%';
}

function repair_nice2($user_id = '') {
  global $db;
if ($_GET['date']) {
$date = $_GET['date'];
} else {
$date = ((date("d") < 5) ? date("Y.m") : date("Y.m"));
}
if ($user_id) {
$master_id = ' and `master_user_id` = '.$user_id;
}
$sql = mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` where `repair_type_id` IN (4) and `service_id` = 33 and (`status_admin` = \'Подтвержден\' OR `status_admin` = \'Выдан\') and `master_app_date` REGEXP \''.$date.'\' and `deleted` != 1 '.$master_id.';');
$ato = mysqli_fetch_array($sql)['COUNT(*)'];
$sql = mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` where `repair_type_id` IN (1,2,3,5) and `service_id` = 33 and (`status_admin` = \'Подтвержден\' OR `status_admin` = \'Выдан\') and `master_app_date` REGEXP \''.$date.'\' and `deleted` != 1 '.$master_id.';');
$repair = mysqli_fetch_array($sql)['COUNT(*)'];
/*$sql = mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` where `repair_type_id` = 2 and `master_app_date` REGEXP \''.$date.'\' and `deleted` != 1;');
$blocks .= mysqli_fetch_array($sql)['COUNT(*)'].', ';
$sql = mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` where `repair_type_id` = 3 and `master_app_date` REGEXP \''.$date.'\' and `deleted` != 1;');
$blocks .= mysqli_fetch_array($sql)['COUNT(*)']; */

if ($repair > 0 && $ato > 0) {
return round($repair/($repair+$ato)*100, 1);
} else {
return 0;
}


}

function check_status() {
  global $db;
$sql = mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` where (`status_admin` IN (\'В обработке\', \'Есть вопросы\', \'Подтвержден\', \'Отклонен\', \'Оплачен\', \'На проверке\', \'Запчасти в пути\', \'Нужны запчасти\', \'Запрос на выезд\', \'Выезд подтвержден\', \'Выезд отклонен\') or `repair_done` = 1) and `status_admin_read` = 1 and `deleted` != 1;');
return mysqli_fetch_array($sql)['COUNT(*)'];
}

function parts_price($id) {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `repairs_work` where `repair_id` = '.$id.';');
$row = @mysqli_fetch_array($sql)['sum'];
$sum = ($row) ? $row : 0;
return $sum;
}

function check_status_user() {
  global $db;
$sql = mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` where `status_user_read` = 1 and `deleted` != 1 and `service_id` = '.User::getData('id').' and `status_admin` != \'\' and `status_admin` != \'Подтвержден\' ;');
return mysqli_fetch_array($sql)['COUNT(*)'];
}

function plan_value() {
  global $db;
$master_id = ($_GET['master_id']) ? $_GET['master_id'] : 33;

if ($_GET['date']) {
$date_exp = explode('.', $_GET['date']);
$month = $date_exp['1'];
$year = $date_exp['0'];
} else {
$month = ((date("d") < 5) ? date("m") : date("m"));
$year = ((date("d") < 5) ? date("Y") : date("Y"));
}

$sql = mysqli_query($db, 'SELECT `plan` FROM `plans` where `user_id` = '.$master_id.' and `year` = \''.$year.'\' and `month` = \''.$month.'\' ;');
return mysqli_fetch_array($sql)['plan'];
}

function plan_value_by_user($id) {
  global $db;
$master_id = ($id) ? $id : 33;

if ($_GET['date']) {
$date = $_GET['date'];
$date_exp = explode('.', $_GET['date']);
$month = $date_exp['1'];
$year = $date_exp['0'];
} else {
$month = ((date("d") < 5) ? date("m") : date("m"));
$year = ((date("d") < 5) ? date("Y") : date("Y"));
}

$sql = mysqli_query($db, 'SELECT `plan` FROM `plans` where `user_id` = '.$master_id.' and `year` = \''.$year.'\' and `month` = \''.$month.'\' ;');
return mysqli_fetch_array($sql)['plan'];
}

function get_request_info_serice($id) {
  global $db;
$req = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `requests` WHERE `user_id` = '.$id));
return $req;
}

function check_complete($id) {
    global $db;
$req = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` WHERE
`client` != \'\' and
`phone` != \'\' and
`name_shop` != \'\' and
`model_id` != \'\' and
`status_id` != \'\' and
`bugs` != \'\' and
`begin_date` != "0000-00-00" and
`master_id` != \'\' and
`disease` != \'\' and
`id` = '.$id));
if ($req['COUNT(*)'] == 0) {
return false;
} else {
return true;
}
}

function plan_master_percent() {
  global $db;
if ($_GET['date']) {
$date = $_GET['date'];
$date11 = DateTime::createFromFormat("Y.m", $_GET['date']);
$date11->modify('-1 month');
$date2 = ((date("d") < 5) ? $date11->format('Y.m') : $date);
} /*else {
$date = ((date("d") < 5) ? date("Y.m") : date("Y.m"));
$date2 = ((date("d") < 5) ? date("Y.m", strtotime("-1 months")) : date("Y.m", strtotime("-1 months")));
} */

$masters = Staff::getMasters();
$total_plan = $planz = $summ_total = 0;
foreach ($masters as $row) {
        $plan = plan_value_by_user($row['id']);
        $summ = total_pay_month_by_user($row['id']);
        $percent = $row['percent'];
        if ($percent >= 40) {
        $total_plan += $plan;
        if ($plan > $summ && $plan != 0) {
        $planz += round($summ/$plan*100);
        } else if ($plan < $summ  && $plan != 0) {
        $planz += round($summ/$plan*100);
        } else {
        $planz += 0;
        }
        $summ_total += $summ;
        }
      }
return round($summ_total/$total_plan*100);
}

function master_select() {
  $content = '';
$masters = Staff::getMasters();
    foreach ($masters as $row) {
        if ($_GET['master_id'] == $row['id']) {
        $content .= '<option selected value="'.$row['id'].'">'.$row['full_name'].'</option>';
        } else {
        $content .= '<option value="'.$row['id'].'">'.$row['full_name'].'</option>';
        }
    }
    return $content;
}

?>
<!doctype html>
<html>
<head>
<meta charset=utf-8>
<title>Статистика - Панель управления</title>
<link href="/css/fonts.css" rel="stylesheet" />
<link href="/css/style.css" rel="stylesheet" />
<script src="/_new-codebase/front/vendor/jquery/jquery-1.7.2.min.js"  ></script>
<script src="/js/jquery-ui.min.js"></script>
<script src="/js/jquery.placeholder.min.js"></script>
<script src="/js/jquery.formstyler.min.js"></script>
<script src="/js/main.js"></script>
<script src="/notifier/js/index.js"></script>
<script src="/_new-codebase/front/vendor/tooltipster/tooltipster.bundle.min.js"></script>
<script src='/_new-codebase/front/vendor/mustache/mustache.min.js'></script>
<script src="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.concat.min.js"></script>
<link rel="stylesheet" href="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.min.css" />
<link rel="stylesheet" href="/_new-codebase/front/vendor/tooltipster/tooltipster.bundle.min.css" />
<link rel="stylesheet" href="/_new-codebase/front/vendor/tooltipster/tooltipster-sideTip-shadow.min.css" />
<link rel="stylesheet" href="/_new-codebase/front/vendor/animate.min.css" />
<script  src="/_new-codebase/front/vendor/datatables/1.10.12/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="/css/datatables.css">

<script  src="/_new-codebase/front/vendor/datatables/2.1.1/dataTables.responsive.min.js"></script>
<link rel="stylesheet" type="text/css" href="/_new-codebase/front/vendor/datatables/2.1.1/responsive.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="/notifier/css/style.css">
<link rel="stylesheet" type="text/css" href="/js/daterangepicker.css">
<script src="/js/moment.min.js"></script>
<script src="/js/jquery.daterangepicker.js"></script>
<script src="/_new-codebase/front/vendor/chart-js/chart.bundle.min.js"></script>
<script src="/_new-codebase/front/vendor/chart-js/utils.js"></script>

<style>
.ui-selectmenu-button:after {
    display: none;
}
.ui-selectmenu-button {
    width: 285px;
    font-size: 15px;
}

table.dataTable.dtr-inline.collapsed>tbody>tr>td:first-child:before, table.dataTable.dtr-inline.collapsed>tbody>tr>th:first-child:before {
    top: auto;

    }
table.dataTable thead .sorting {
    font-size: 16px;
}

</style>
<style>
.date-picker-wrapper .drp_top-bar .apply-btn.disabled {
width: auto !important;
}
.date-picker-wrapper .drp_top-bar .apply-btn {
width: auto !important;
}
.redactor-editor {
text-align: left;
}
.ui-datepicker td a.ui-state-highlight {
    color:#fff;
}
#ui-datepicker-div {
border: 1px solid #ccc;
z-index: 999999999999999999 !important;
}
.canvas-holder{
  width: 305px;   
  display: inline-block;
  margin: 32px;
}
@media (min-width: 1920px) {
  .canvas-holder{
  width: 375px;   
}
}
.canvas-legend{
margin-top: 16px;
}

.canvas-legend-item{
margin-top: 4px;
display: flex;
align-items: center;
}

.canvas-legend-item::before{
content: '';
display: inline-block;
width: 24px;
height: 24px;
background-color: #eee;
margin-right: 8px;
}

.canvas-legend-item_1::before{
  background-color: rgb(255, 99, 132);
}

.canvas-legend-item_2::before{
  background-color: rgb(255, 159, 64);
}

.canvas-legend-item_3::before{
  background-color: rgb(255, 205, 86);
}

.canvas-legend-item_4::before{
  background-color: rgb(75, 192, 192);
}

.canvas-legend-item_5::before{
  background-color: rgb(54, 162, 235);
}

.total_table {
 margin-top: 24px;
}

.total_table tr:hover {
 background-color: lightgoldenrodyellow;
}

.total_table td {
 padding: 6px;
 border-bottom: solid 1px #eee;
}

</style>
<script >
// Таблица
$(document).ready(function() {

var randomScalingFactor = function() {
        return Math.round(Math.random() * 100);
    };
    <?php 
      $repSum = get_repair_sum_stats();
    ?>
    var config4 = {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [
                    <?=$repSum['sum'];?>
                ],
                backgroundColor: [
                    window.chartColors.red,
                    window.chartColors.orange,
                    window.chartColors.yellow,
                    window.chartColors.green,
                    window.chartColors.blue,
                ]
            }],
            labels: [
                <?=$repSum['labels'];?>
            ]
        },
        options: { 
            interaction: {
              intersect: false
            },
            responsive: true,
            legend: {
                position: 'top',
                display: false
            },
            title: {
                display: true,
                text: 'Структура всех ремонтов'
            },
            animation: {
                animateScale: true,
                animateRotate: true
            },
            aspectRatio: '1.5'
        }
    };
    
    <?php $rep = get_repair_stats(); ?>
 var config = {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [
                    <?= $rep['data'];?>
                ],
                backgroundColor: [
                    window.chartColors.red,
                    window.chartColors.orange,
                    window.chartColors.yellow,
                    window.chartColors.green,
                    window.chartColors.blue,
                ]
            }],
            labels: [
              <?= $rep['labels'];?>
            ]
        },
        options: {
            interaction: {
              intersect: false
            },
            responsive: true,
            legend: {
                position: 'top',
                display: false
            },
            title: {
                display: true,
                text: 'Структура ремонтов товара Оптима-М'
            },
            animation: {
                animateScale: true,
                animateRotate: true
            },
            aspectRatio: '1.5'
        }
    };

 <?php
     $trash = get_trash_stats();
     ?>

 var config2 = {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [
                <?=$trash['stat'];?>
                ],
                backgroundColor: [
                 <?=$trash['color'];?>
                ]
            }],
            labels: [
                <?=$trash['model'];?>
            ],
            aspectRatio: '1.5'
        },
        options: {
            responsive: true,
            legend: {
                position: 'top',
                display:false
            },
            title: {
                display: true,
                text: 'Утилизация'
            },
            animation: {
                animateScale: true,
                animateRotate: true
            },
            aspectRatio: '1.5'
        }
    };

     <?php
     $resell = get_resell_stats();
     ?>

     var config3 = {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [
                <?=$resell['stat'];?>
                ],
                backgroundColor: [
                 <?=$resell['color'];?>
                ]
            }],
            labels: [
                <?=$resell['model'];?>
            ]
        },
        options: {
            responsive: true,
            legend: {
                position: 'top',
                display: false
            },
            title: {
                display: true,
                text: 'Уценка'
            },
            animation: {
                animateScale: true,
                animateRotate: true
            },
            aspectRatio: '1.5'
        }
    };

   

    window.onload = function() {
      
        var ctx4 = document.getElementById("chart-area4").getContext("2d");
        window.myDoughnut = new Chart(ctx4, config4);

        var ctx = document.getElementById("chart-area").getContext("2d");
        window.myDoughnut = new Chart(ctx, config);

        var ctx2 = document.getElementById("chart-area2").getContext("2d");
        window.myDoughnut = new Chart(ctx2, config2);

        var ctx3 = document.getElementById("chart-area3").getContext("2d");
        window.myDoughnut = new Chart(ctx3, config3);

    };



 $('.need_work').tooltipster({
                              trigger: 'hover',
                              position: 'top',
                              animation: 'grow',
                              theme: 'tooltipster-shadow'
                          });

    $('.table_content').dataTable({
      stateSave:false,
      responsive: true,
      ordering: false,
      "pageLength": <?=$config['page_limit'];?>,
       "order": [[ 0, 'desc' ]],
      "oLanguage": {
            "sLengthMenu": "Показывать _MENU_ записей на страницу",
            "sZeroRecords": "Записей нет.",
            "sInfo": "Показано от _START_ до _END_ из _TOTAL_ записей",
            "sInfoEmpty": "Записей нет.",

            "oPaginate": {
                 "sFirst": "Первая",
                 "sLast": "Последняя",
                 "sNext": "Следующая",
                 "sPrevious": "Предыдущая",
                },
            "sSearch": "Поиск",
            "sInfoFiltered": "(отфильтровано из _MAX_ записи/(ей)"
        }});


$(".monthPicker").datepicker({
    dateFormat: 'yy.mm',
    changeMonth: true,
      changeYear: true,
      showButtonPanel: true,
      yearRange: '2017:<?= date('Y'); ?>',
      maxDate: new Date(<?=date("Y, m, 0");?>),
      beforeShow : function(){
           if($('.datepicker_wrapper2').length){
                $(this).datepicker("widget").unwrap('<span class="datepicker_wrapper2"></span>');
           }
      },
      onClose: function(dateText, inst) {
            var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
            var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
            $(this).datepicker('setDate', new Date(year, month, 1));
        }
  });

  $(".monthPicker").focus(function () {
    $(".ui-datepicker-calendar").hide();
    $("#ui-datepicker-div").position({
        my: "center top",
        at: "center bottom",
        of: $(this)
      });

  });


$(".monthPicker2").datepicker({
    dateFormat: 'yy',
    changeMonth: true,
      changeYear: true,
      showButtonPanel: true,
      yearRange: '2017:2020',
      maxDate: new Date(<?=date("Y, m, 0");?>),
      beforeShow : function(){
           if(!$('.datepicker_wrapper2').length){
                $(this).datepicker("widget").wrap('<span class="datepicker_wrapper2"></span>');
           }
      },
      onClose: function(dateText, inst) {
            var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
            var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
            $(this).datepicker('setDate', new Date(year, month, 1));
        }
  });

  $(".monthPicker2").focus(function () {
    $(".ui-datepicker-calendar").hide();
    $("#ui-datepicker-div").position({
        my: "center top",
        at: "center bottom",
        of: $(this)
      });

  });

 $("#ui-datepicker-div").css("border", "1px solid #ccc");
$.datepicker.setDefaults( $.datepicker.regional[ "ru" ] );

    $(document).on('selectmenuchange', 'select[name=status_admin]', function() {
        var value = $(this).val();
        var id= $(this).data('repair-id');
              if (value) {

                  $.get( "/ajax.php?type=update_repair_status&value="+value+"&id="+id, function( data ) {

                  });

              }


        return false;
    });



/*setTimeout(function(){

$.ajax({
          'type': 'GET',
          'url': '<?=$_SERVER['REQUEST_URI'];?>',
          'complete': function(response)
           {

              $("html").html(response.responseText);
           }
        });


}, 5000); */

} );

$.datepicker.setDefaults( $.datepicker.regional[ "ru" ] );

</script>
<script>
/* Russian (UTF-8) initialisation for the jQuery UI date picker plugin. */
/* Written by Andrew Stromnov (stromnov@gmail.com). */
( function( factory ) {
  if ( typeof define === "function" && define.amd ) {

    // AMD. Register as an anonymous module.
    define( [ "../widgets/datepicker" ], factory );
  } else {

    // Browser globals
    factory( jQuery.datepicker );
  }
}( function( datepicker ) {

datepicker.regional.ru = {
  closeText: "Выбрать",
  prevText: "",
  nextText: "",
  currentText: "Текущий",
  monthNames: [ "Январь","Февраль","Март","Апрель","Май","Июнь",
  "Июль","Август","Сентябрь","Октябрь","Ноябрь","Декабрь" ],
  monthNamesShort: [ "Янв","Фев","Мар","Апр","Май","Июн",
  "Июл","Авг","Сен","Окт","Ноя","Дек" ],
  dayNames: [ "воскресенье","понедельник","вторник","среда","четверг","пятница","суббота" ],
  dayNamesShort: [ "вск","пнд","втр","срд","чтв","птн","сбт" ],
  dayNamesMin: [ "Вс","Пн","Вт","Ср","Чт","Пт","Сб" ],
  weekHeader: "Нед",
  dateFormat: "dd.mm.yy",
  firstDay: 1,
  isRTL: false,
  showMonthAfterYear: false,
  yearSuffix: "" };
datepicker.setDefaults( datepicker.regional.ru );

return datepicker.regional.ru;

} ) );
</script>
<style>
.datepicker_wrapper2 .ui-datepicker-month {
display:none;
}
.ui-datepicker .ui-datepicker-buttonpane{
text-align: center;
}

.ui-datepicker .ui-datepicker-buttonpane button {
 float:none;
}
.min_width .ui-selectmenu-button{
width: 160px !important;
}

ul.tabs{
      margin: 0px;
      padding: 0px;
      list-style: none;
    }
    ul.tabs li{
      background: none;
      color: #222;
      display: inline-block;
      padding: 10px 15px;
      cursor: pointer;
    }

    ul.tabs li.current{
      background: #ededed;
      color: #222;
    }

    .tab-content{
      display: none;
      background: #ededed;
      padding: 15px;
    }

    .tab-content.current{
      display: inherit;
    }
    .ui-selectmenu-button {
    width: 250px;
    }

.select2-container {
    width: 300px !important;
    }
   .ui-datepicker .ui-datepicker-buttonpane button.ui-datepicker-current {
    float:none;

}
.ui-datepicker {
    height: 180px;
}
[role="row"] td{
  vertical-align: middle;
}
</style>

</head>

<body>

<div class="viewport-wrapper">

<div class="site-header">
  <div class="wrapper">

    <div class="logo">
      <a href="/dashboard/"><img src="/i/logo.png" alt=""/></a>
      <span>Сервис</span>

   </div>

  <div class="not-container">
  <button style="position:relative;    margin-left: 120px;   margin-top: 15px;" type="button" class="button-default show-notifications js-show-notifications animated swing">
  <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="30" height="32" viewBox="0 0 30 32">
    <defs>
      <g id="icon-bell">
        <path class="path1" d="M15.143 30.286q0-0.286-0.286-0.286-1.054 0-1.813-0.759t-0.759-1.813q0-0.286-0.286-0.286t-0.286 0.286q0 1.304 0.92 2.223t2.223 0.92q0.286 0 0.286-0.286zM3.268 25.143h23.179q-2.929-3.232-4.402-7.348t-1.473-8.652q0-4.571-5.714-4.571t-5.714 4.571q0 4.536-1.473 8.652t-4.402 7.348zM29.714 25.143q0 0.929-0.679 1.607t-1.607 0.679h-8q0 1.893-1.339 3.232t-3.232 1.339-3.232-1.339-1.339-3.232h-8q-0.929 0-1.607-0.679t-0.679-1.607q3.393-2.875 5.125-7.098t1.732-8.902q0-2.946 1.714-4.679t4.714-2.089q-0.143-0.321-0.143-0.661 0-0.714 0.5-1.214t1.214-0.5 1.214 0.5 0.5 1.214q0 0.339-0.143 0.661 3 0.357 4.714 2.089t1.714 4.679q0 4.679 1.732 8.902t5.125 7.098z" />
      </g>
    </defs>
    <g fill="#000000">
      <use xlink:href="#icon-bell" transform="translate(0 0)"></use>
    </g>
  </svg>

  <div class="notifications-count js-count"></div>

</button>
</div>

    <div class="logout">

      <a href="/logout/">Выйти, <?=\models\User::getData('login');?></a>
    </div>

  </div>
</div><!-- .site-header -->

<div class="wrapper">

<?=top_menu_admin();?>

  <div class="adm-tab">

  <?=menu_dash();?>

  </div><!-- .adm-tab -->



           <br>
           <h2>Статистика</h2>

  <div class="adm-catalog" style="font-size:16px !important;">
  <div class="dates_block" style="vertical-align:middle;">
    <form method="GET" style="padding-top:16px;display: flex;align-items: center;">
    <div style="margin-right: 30px"><a href="https://crm.r97.ru/stat-master-personal/">Текущий</a></div>
    <div style="margin-right: 30px">Год и месяц <br> <input type="text" class="monthPicker" name="date" style="width: 120px;    text-align: center;    height: 40px;    padding: 0;" value="<?=($_GET['date'] ? $_GET['date'] : '')?>"/></div>
    <div><input class="green_button" type="submit" style="display: inline-block;  vertical-align: middle;        height: 54px; " value="Применить" /></div>
    </form>
    </div>


    <br><br>

    <section style="display: flex; justify-content: center; align-items: flex-start;">
    
    <div class="canvas-holder">
        <div id="canvas-holder">
            <canvas id="chart-area4" />
        </div>
        <div class="canvas-legend">
        <?php
          $i = 1;
          foreach($repSum['labels_list'] as $label){
            echo '<div class="canvas-legend-item canvas-legend-item_'.$i.'">'.$label.'</div>';
            $i++;
          }
        ?>
        </div> 
    </div>
    
    <div class="canvas-holder">
        <div id="canvas-holder">
            <canvas id="chart-area" />
        </div>
        <div class="canvas-legend">
        <?php
          $i = 1;
          foreach($rep['labels_list'] as $label){
            echo '<div class="canvas-legend-item canvas-legend-item_'.$i.'">'.$label.'</div>';
            $i++;
          }
        ?>
        </div>
    </div>

    <div id="canvas-holder" class="canvas-holder">
        <canvas id="chart-area2" />
    </div>

     <div id="canvas-holder" class="canvas-holder">
        <canvas id="chart-area3" />
    </div>

</section>


    <div>

 
  <table class="display table_content"  cellspacing="0" data-paging="false" data-info="false" data-searching="false" data-ordering="false" date-responsive="true">
        <thead>
            <tr>
                <th align="left" style="max-width:50px;">Месяц</th>
                <th align="center" style="min-width:120px;">Ремонтов</th>
                <th align="center" style="min-width:220px;">Эффективность</th>
                <th align="center" style="min-width:220px;" >Выручка</th>
                <th align="center" style="min-width:320px;" >К выплате</th>
                <th align="left" >Ремонты</th>
            </tr>
        </thead>

        <tbody>
          <?=total_user();?>
        </tbody>
</table>

<br><br>
  
      <h3>Общее за этот месяц</h3>
  <table class="total_table" cellspacing="0" width="100%" data-paging="false" data-info="false" data-searching="false" data-ordering="false">

           <tbody>
            <tr>
                <td style="width: 350px">Всего ремонтов:</td>
                <td><?=total_month();?></td>
            </tr>

            <tr>
                <td>Эффективность ремонтов:</td>
                <td><?=repair_nice();?></td>
            </tr>

            <tr>
                <td>Выручка:</td>
                <td><?=number_format(total_pay_month(), 2, ',', ' ');?> р.</td>
            </tr>

            <tr>
                <td>План:</td>
                <td><?= number_format(plan_value(), 0, ',', ' ');?> р.</td>
            </tr>

            <tr>
                <td>Общий план по сервису выполнен на:</td>
                <td>
                <?php
                                    $plan = plan_value();
                                    $summ = total_pay_month();

                                    if ($plan > $summ && $plan != 0) {
                                      echo round($summ / $plan * 100);
                                    } else if ($plan < $summ  && $plan != 0) {
                                      echo round($summ / $plan * 100);
                                    } else {
                                      echo 0;
                                    }
?> %</td>
            </tr>

            <tr>
                <td>План по мастерам выполнен на:</td>
                <td><?=plan_master_percent()?> %</td>
            </tr>

        </tbody>
        
</table>
    


</div>
</div>


        </div>
  </div>
</div>
</body>
</html>