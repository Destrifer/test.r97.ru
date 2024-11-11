<?php

use models\User;

if ($_GET['r97'] == 1) {
setcookie("r97", "1", time()+3600*60*31, "/stat/");
$_COOKIE['r97'] = 1;
} else {
setcookie("r97", "", time()-3600*60*31, "/stat/");
$_COOKIE['r97'] = 0;
}

if ($_COOKIE['r97'] != 1) {
$not_r97 = ' and `service_id` != 33 ';
}

function content_list() {
  global $db, $config;

if ($_GET['date1'] != '' && $_GET['date2'] != '') {
$where_date = ' and DATE(date) BETWEEN \''.mysqli_real_escape_string($db, $_GET['date1']).'\' AND \''.mysqli_real_escape_string($db, $_GET['date2']).'\'';
}
if ($_GET['date12'] != '' && $_GET['date22'] != '') {
$where_date2 = ' and `receive_date` BETWEEN \''.mysqli_real_escape_string($db, $_GET['date12']).'\' AND \''.mysqli_real_escape_string($db, $_GET['date22']).'\'';
}
if ($_GET['date13'] != '' && $_GET['date23'] != '') {
$where_date3 = ' and `finish_date` BETWEEN \''.mysqli_real_escape_string($db, $_GET['date13']).'\' AND \''.mysqli_real_escape_string($db, $_GET['date23']).'\'';
}
if ($_GET['date14'] != '' && $_GET['date24'] != '') {
$where_date4 = ' and `begin_date` BETWEEN \''.mysqli_real_escape_string($db, $_GET['date14']).'\' AND \''.mysqli_real_escape_string($db, $_GET['date24']).'\'';
}
if ($_GET['target']) {
if ($_GET['target'] != 'all') {

if ($_GET['target'] == 'courier') {
$where_target = ' and `onway` = 1';
} else {
$where_target = ' and `status_admin` = \''.mysqli_real_escape_string($db, $_GET['target']).'\'';
}

}

} else {

if (\models\User::hasRole('admin')) {
$where_target = ' and `status_admin` != \'Подтвержден\' and `status_admin` != \'\'';
} else {
$where_target = ' and `status_admin` != \'Подтвержден\' ';
}


}
if (!User::hasRole('admin')) {

$where_impo_user = ($_GET['impo'] == 1) ? 'and `status_user_read` = 1 ' : '';

if (User::hasRole('service')) {
$services_arr[] = User::getData('id');
$sql2 = mysqli_query($db, 'SELECT * FROM `services_link` WHERE `service_parent` = '.User::getData('id'));
if (mysqli_num_rows($sql2) != false) {
while ($row2 = mysqli_fetch_array($sql2)) {
$services_arr[] .= $row2['service_child'];
}
}

$sql = mysqli_query($db, 'SELECT * FROM `repairs` where `service_id` IN ('.implode(',', $services_arr).') and `deleted` != 1 '.$where_date.' '.$where_date2.' '.$where_date3.' '.$where_date4.' '.$where_target.' '.$where_impo_user.' order by `id` DESC;');
} else {

$sql = mysqli_query($db, 'SELECT * FROM `repairs` where `service_id` = '.User::getData('id').' and `deleted` != 1 '.$where_date.' '.$where_date2.' '.$where_date3.' '.$where_date4.' '.$where_target.' '.$where_impo_user.' order by `id` DESC;');
}


} else {


$where_impo_admin = ($_GET['impo'] == 1) ? ' and `status_admin_read` = 1 ' : '';

if ($_GET['get'] == 'deleted') {
$sql = mysqli_query($db, 'SELECT * FROM `repairs`  WHERE `deleted` = 1 '.$where_date.' '.$where_date2.' '.$where_date3.' '.$where_date4.' '.$where_target.' '.$where_impo_admin.' order by `id` DESC;');
} else {
$sql = mysqli_query($db, 'SELECT * FROM `repairs` where `deleted` != 1  '.$where_date.' '.$where_date2.' '.$where_date3.' '.$where_date4.' '.$where_target.' '.$where_impo_admin.' order by `id` DESC;');
//echo 'SELECT * FROM `repairs` where `deleted` != 1  '.$where_date.' '.$where_date2.' '.$where_date3.' '.$where_date4.' '.$where_target.' '.$where_impo_admin.' order by `id` DESC;';
}

}
if (mysqli_num_rows($sql) != false) {
      while ($row = mysqli_fetch_array($sql)) {
      $status = array(1 => 'Гарантийный', 2 => 'Платный', 3 => 'Повторный', 4 => 'Предпродажный', 5 => 'Условно-гарантийный');

      $model = model($row['model_id']);
      if (\models\User::hasRole('admin')) {
      $draw_color = ($row['status_admin_read'] == 1) ? 'background: rgba(219, 255, 183, 0.6);' : '';
      } else {
      $draw_color = ($row['status_user_read'] == 1) ? 'background: rgba(219, 255, 183, 0.6);' : '';
      }

      $draw_color2 = ($config['last_admin'] == $row['id']) ? 'background: rgba(255, 153, 51, 0.4);' : '';

      if ($row['create_date'] != '0000-00-00 00:00:00') {

      try {
          $date_created = new DateTime($row['create_date']);
          $date_ready_created = $date_created->format("d.m.Y");
      } catch (Exception $e) {

      }
      }

      $content_list .= '<tr style="'.$draw_color.' '.$draw_color2.'">
      <td>'.$row['id'].'</td><td>'.$date_ready_created.'</td>';

      if (\models\User::hasRole('admin')) {
      $req = get_request_info_serice($row['service_id']);
      $content_list .= '<td>'.$req['adress'].'</td>';
      $content_list .= '<td>'.$req['name'].'</td>';
      $content_list .= '<td style="text-align:center">'.$row['total_price'].'</td>';
      $content_list .= '<td style="text-align:center">'.parts_price($row['id']).'</td>';
      $content_list .= '<td style="text-align:center">'.(($row['status_admin'] != 'Запрос на выезд' && $row['onway'] == 1) ? get_price_transfer($model['cat'], $row['onway_type'], $row['service_id']) : '0').'</td>';
      } else {
      $content_list .= '<td>'.$row['service_id'].'</td>';
      }

      if ($row['receive_date'] != '0000-00-00') {

      try {
          $date_new = new DateTime($row['receive_date']);
          $date_ready = $date_new->format("d.m.Y");
      } catch (Exception $e) {

      }
      }
      $content_list .= '<td >'.$date_ready.'</td>
      <td>'.$row['client'].'</td>
      <td>'.$model['name'].'</td>
      <td>'.$row['serial'].'</td>
      <td>'.program\core\Time::format($row['begin_date']).'</td>
      <td>'.program\core\Time::format($row['finish_date']).'</td>
      <td>'.$row['rsc'].'</td>
      <td>'.$status[$row['status_id']].'</td>
      <td align="center" class="linkz" style="width:100%">';

      $content_list .= '<a class="t-3" title="Редактировать карточку" href="/edit-repair/'.$row['id'].'/" ></a><a class="t-1" style="    background: url(../img/n.png) 0 0 no-repeat;" title="Скачать наклейку" href="/get-label/'.$row['id'].'/" ></a><a class="t-1" style="    background: url(../img/k.png) 0 0 no-repeat;" title="Скачать квитанцию" href="/get-receipt/'.$row['id'].'/" ></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

      if ($row['deleted'] != 1) {
      if (\models\User::hasRole('admin')) {
      $content_list .= '<a class="t-5" title="Удалить карточку" href="/del-repair/'.$row['id'].'/" ></a><input data-id="'.$row['id'].'" style=" display:block;   width: 98px;     height: 32px;    padding: 3px;  margin-top: 5px;    font-size: 19px;" class="datepicker2 metro-skin" type="text" name="date_app" value="'.$row['app_date'].'"  />';
      } else {

      if ($row['status_admin'] != 'Подтвержден') {
      $content_list .= '<a class="t-5" onclick=\'return confirm("Восстановление удаленной заявки возможно только через администратора сервисной базы. Удалить заявку?")\' title="Удалить карточку" href="/del-repair/'.$row['id'].'/" ></a>';
      }

      }
      } else {
      if (\models\User::hasRole('admin')) {
      $content_list .= '<a class="t-2" title="Восстановить карточку" href="/comeback-repair/'.$row['id'].'/" ></a>';
      }
      }


      if (!User::hasRole('admin') && $row['repair_done'] != 1) {
      if (check_complete($row['id'])) {
      $content_list .= '<a class="mod_fast_true" title="Отправить на проверку" style="background:none;    padding-bottom: 6px;" href="/repair-done/'.$row['id'].'/"><img src="/img/true.png"></a>';
      } else {
      $content_list .= '<a title="Не заполнены все необходимые поля" class="mod_fast_true need_work" title="Отправить на проверку" style="background:none;    padding-bottom: 6px;" href="#" onclick="return false"><img src="/img/true_yellow.png"></a>';
      }
      }

      $content_list .= '</td>';

      if (\models\User::hasRole('admin')) {
      $content_list .= '<td><form method="POST"><select  name="status_admin" data-repair-id="'.$row['id'].'"><option value="">Без статуса</option><option value="В обработке" '.(($row['status_admin'] == 'В обработке') ? 'selected' : '').'>В обработке</option><option value="Есть вопросы" '.(($row['status_admin'] == 'Есть вопросы') ? 'selected' : '').'>Есть вопросы</option><option value="Подтвержден" '.(($row['status_admin'] == 'Подтвержден') ? 'selected' : '').'>Подтвержден</option><option value="Отклонен" '.(($row['status_admin'] == 'Отклонен') ? 'selected' : '').'>Отклонен</option><option value="Оплачен" '.(($row['status_admin'] == 'Оплачен') ? 'selected' : '').'>Оплачен</option><option value="На проверке" '.(($row['status_admin'] == 'На проверке') ? 'selected' : '').'>На проверке</option><option value="Нужны запчасти" '.(($row['status_admin'] == 'Нужны запчасти') ? 'selected' : '').'>Нужны запчасти</option><option value="Запчасти в пути" '.(($row['status_admin'] == 'Запчасти в пути') ? 'selected' : '').'>Запчасти в пути</option><option value="Запрос на выезд" '.(($row['status_admin'] == 'Запрос на выезд') ? 'selected' : '').'>Запрос на выезд</option><option value="Выезд подтвержден" '.(($row['status_admin'] == 'Выезд подтвержден') ? 'selected' : '').'>Выезд подтвержден</option><option value="Выезд отклонен" '.(($row['status_admin'] == 'Выезд отклонен') ? 'selected' : '').'>Выезд отклонен</option></select></form></td>';
      } else {
      $content_list .= '<td>'.$row['status_admin'].'</td>';
      }

      $content_list .= '</tr>';
      unset($date_ready);
      unset($date_new);
      unset($date_ready_created);
      }
      } else {
      $content_list = '<tr><td colspan=9"">'.$row['id'].'</td>';
      }
    return $content_list;
}

function model($id) {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `models` where `id` = '.$id);
      while ($row = mysqli_fetch_array($sql)) {
      $content = $row;

      }
    return $content;
}

function total_month() {
  global $db, $not_r97;

if ($_GET['date']) {
$date = $_GET['date'];
} else {
//$date = ((date("d") < 5) ? date("Y.m", strtotime("-1 months")) : date("Y.m"));
$date = ((date("d") < 5) ? date("Y.m") : date("Y.m"));
}

if ($_GET['date']) {

    $sql_dated = ' `app_date` REGEXP \''.$date.'\'' ;

} else if ($_GET['date1'] && $_GET['date2']){

    $sql_dated = ' DATE(app_date) BETWEEN \''.$_GET['date1'].'\' AND \''.$_GET['date2'].'\'';

} else {

    $sql_dated = ' `app_date` REGEXP \''.$date.'\'' ;

}

if ($_GET['repair_type_id']) {
$sql_type = ' and `repair_type_id` = '.$_GET['repair_type_id'].' ';
}

if ($_GET['cat_id']) {
$sql_cat_id = ' and `cat_id` = '.$_GET['cat_id'].' ';
}



$sql = mysqli_query($db, 'SELECT  COUNT(*) FROM `repairs` where '.$sql_dated.' and `deleted` = 0 and `status_admin` = \'Подтвержден\' '.$not_r97.' '.$sql_type.' '.$sql_cat_id.'  order by `id` DESC;');
return mysqli_fetch_array($sql)['COUNT(*)'];
}

function total_pay_month() {
  global $db, $not_r97;
//$dated = ((date("d") < 5) ? date("Y.m", strtotime("-1 months")) : date("Y.m"));
if ($_GET['date']) {
$date = $_GET['date'];
} else {
$date = ((date("d") < 5) ? date("Y.m") : date("Y.m"));
}

if ($_GET['repair_type_id']) {
$sql_type = ' and `repair_type_id` = '.$_GET['repair_type_id'].' ';
}

if ($_GET['cat_id']) {
$sql_cat_id = ' and `cat_id` = '.$_GET['cat_id'].' ';
}



if ($_GET['date']) {

    $sql_dated = ' `app_date` REGEXP \''.$date.'\'' ;

} else if ($_GET['date1'] && $_GET['date2']){

    $sql_dated = ' DATE(app_date) BETWEEN \''.$_GET['date1'].'\' AND \''.$_GET['date2'].'\'';

} else {

    $sql_dated = ' `app_date` REGEXP \''.$date.'\'' ;

}

if ($date) {
$where_date = ' `app_date` REGEXP \''.$date.'\'';

/*$date11 = DateTime::createFromFormat("Y.m", $date);
$date11->modify('-1 month');

echo ((date("d") > 5) ? $date11->format('Y.m').'.05' : $date.'.05');
echo ((date("d") > 5) ? $date.'.'.date('d') : $date.'.'.date('d')); */

}



$sql = mysqli_query($db, 'SELECT * FROM `repairs` where  '.$sql_dated.' '.$where_year.' and `status_admin` = \'Подтвержден\' and `deleted` = 0 '.$not_r97.' '.$sql_type.' '.$sql_cat_id.'  order by `id` DESC;');
      while ($row = mysqli_fetch_array($sql)) {
      //$info = get_request_info($row['id']);
      //$city = get_city($info['city']);
      $i2++;

      if($row['app_date']) {
      $exp = explode('.', $row['app_date']);
      $app[$exp['0']] = $exp[1];
      }


      }

     foreach ($app as $year => $month) {

     $sql2 = mysqli_query($db, 'SELECT * FROM `repairs` where `app_date` REGEXP \''.$year.'.'.$month.'\' and `status_admin` = \'Подтвержден\' and `deleted` = 0 '.$not_r97.' '.$sql_type.' '.$sql_cat_id.'  GROUP by `service_id`  order by `id` DESC ;');
    // echo 'SELECT * FROM `repairs` where `app_date` REGEXP \''.$year.'.'.$month.'\' and `status_admin` = \'Подтвержден\' and `deleted` = 0  GROUP by `service_id`  order by `id` DESC ;';

      while ($row2 = mysqli_fetch_array($sql2)) {
      if ($_GET['impo'] != 1) {
      $summ = get_service_summ($row2['service_id'], $month, $year);
     //echo $summ.'<br>';
      $total += $summ;
       }
       }

     }
     if (!$total) {$total = 0;}
     return $total;
}

function total_pay_month_minus() {
  global $db, $not_r97;
$dated = ((date("d") < 5) ? date("Y.m") : date("Y.m"));
if ($_GET['date']) {
$date = $_GET['date'];
} else {
$date = ((date("d") < 5) ? date("Y.m") : date("Y.m"));
}

if ($_GET['repair_type_id']) {
$sql_type = ' and `repair_type_id` = '.$_GET['repair_type_id'].' ';
}

if ($_GET['cat_id']) {
$sql_cat_id = ' and `cat_id` = '.$_GET['cat_id'].' ';
}



if ($_GET['date']) {

    $sql_dated = ' and `app_date` REGEXP \''.$date.'\'' ;

} else if ($_GET['date1'] && $_GET['date2']){

    $sql_dated = ' and DATE(app_date) BETWEEN \''.$_GET['date1'].'\' AND \''.$_GET['date2'].'\'';

} else {

    $sql_dated = ' and `app_date` REGEXP \''.$date.'\'' ;

}

if ($date) {
$where_date = 'and `app_date` REGEXP \''.$date.'\'';

$date11 = DateTime::createFromFormat("Y.m", $date);
//$date11->modify('-1 month');

if (date("d") > 5) {
$minus = date("d")-5;
$month = 5;
$total = 0;
while ($minus >= $total) {
//echo '|'.$month.'|';
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
//echo '|'.$month.'|';
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
$sql = mysqli_query($db, 'SELECT * FROM `repairs` where `app_date` IN ('.$imploded.')  and `status_admin` = \'Подтвержден\' and `deleted` = 0 '.$not_r97.' '.$sql_type.' '.$sql_cat_id.' order by `id` DESC;');
      while ($row = mysqli_fetch_array($sql)) {
      //$info = get_request_info($row['id']);
      //$city = get_city($info['city']);
      if($row['app_date']) {
      $exp = explode('.', $row['app_date']);
      $app[$exp['0']] = $exp[1];
      }


      }

     foreach ($app as $year => $month) {

     $sql2 = mysqli_query($db, 'SELECT * FROM `repairs` where DATE(app_date) BETWEEN (str_to_date(\''.$first.'\',\'%Y.%m.%d\') AND str_to_date(\''.$second.'\',\'%Y.%m.%d\')) and `status_admin` = \'Подтвержден\' and `deleted` = 0 and `app_date` NOT REGEXP \''.$dated.'\' '.$not_r97.' '.$sql_type.' '.$sql_cat_id.' GROUP by `service_id`  order by `id` DESC ;');

     while ($row2 = mysqli_fetch_array($sql2)) {
      if ($_GET['impo'] != 1) {
      $summ = get_service_summ($row2['service_id'], $month, $year);
      $total += $summ;
       }
       }

     }
     return $total;
}

function total_month_minus() {
  global $db, $not_r97;
if ($_GET['date']) {
$date = $_GET['date'];
} else {
$date = ((date("d") < 5) ? date("Y.m") : date("Y.m"));
}

if ($_GET['repair_type_id']) {
$sql_type = ' and `repair_type_id` = '.$_GET['repair_type_id'].' ';
}

if ($_GET['cat_id']) {
$sql_cat_id = ' and `cat_id` = '.$_GET['cat_id'].' ';
}



if ($date) {
$where_date = 'and `app_date` REGEXP \''.$date.'\'';

$date11 = DateTime::createFromFormat("Y.m", $date);
//$date11->modify('-1 month');

if (date("d") > 5) {
$minus = date("d")-5;
$month = $date11->format('m');
$total = 0;
while ($minus >= $total) {
//echo '|'.$month.'|';
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
//echo '|'.$month.'|';
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
//echo 'SELECT  COUNT(*) FROM `repairs` where `app_date` IN ('.$imploded.')  and `deleted` = 0 and `status_admin` = \'Подтвержден\'  order by `id` DESC;';
$sql = mysqli_query($db, 'SELECT  COUNT(*) FROM `repairs` where `app_date` IN ('.$imploded.')  and `deleted` = 0 and `status_admin` = \'Подтвержден\' '.$not_r97.' '.$sql_type.' '.$sql_cat_id.' order by `id` DESC;');

return mysqli_fetch_array($sql)['COUNT(*)'];
}

function top5_repairs_month() {
  global $db, $not_r97;
if ($_GET['date']) {
$date = $_GET['date'];
$date11 = DateTime::createFromFormat("Y.m", $_GET['date']);
//$date11->modify('-1 month');
$date2 = ((date("d") < 5) ? $date11->format('Y.m') : $date);
} else {
$date = ((date("d") < 5) ? date("Y.m") : date("Y.m"));
$date2 = ((date("d") < 5) ? date("Y.m", strtotime("-1 months")) : date("Y.m", strtotime("-1 months")));
}

if ($_GET['repair_type_id']) {
$sql_type = ' and `repair_type_id` = '.$_GET['repair_type_id'].' ';
}

if ($_GET['cat_id']) {
$sql_cat_id = ' and `cat_id` = '.$_GET['cat_id'].' ';
}



if ($_GET['date']) {

    $sql_dated = ' `app_date` REGEXP \''.$date.'\'' ;

} else if ($_GET['date1'] && $_GET['date2']){

    $sql_dated = ' DATE(app_date) BETWEEN \''.$_GET['date1'].'\' AND \''.$_GET['date2'].'\'';

} else {

    $sql_dated = ' `app_date` REGEXP \''.$date.'\'' ;

}


$sql2 = mysqli_query($db, 'SELECT * FROM `repairs` where '.$sql_dated.' and `status_admin` = \'Подтвержден\' and `deleted` = 0 '.$not_r97.' '.$sql_type.' '.$sql_cat_id.' order by `id` DESC LIMIT 7;');
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
  global $db, $not_r97;
if ($_GET['date']) {
$date = $_GET['date'];
$date11 = DateTime::createFromFormat("Y.m", $_GET['date']);
$date11->modify('-1 month');
$date2 = ((date("d") < 5) ? $date11->format('Y.m') : $date);
} else {
$date = ((date("d") < 5) ? date("Y.m") : date("Y.m"));
$date2 = ((date("d") < 5) ? date("Y.m", strtotime("-1 months")) : date("Y.m", strtotime("-1 months")));
}

if ($_GET['repair_type_id']) {
$sql_type = ' and `repair_type_id` = '.$_GET['repair_type_id'].' ';
}

if ($_GET['cat_id']) {
$sql_cat_id = ' and `cat_id` = '.$_GET['cat_id'].' ';
}



if ($_GET['date']) {

    $sql_dated = ' `app_date` REGEXP \''.$date.'\'' ;
    $sql_dated2 =  '`app_date` REGEXP \''.$date2.'\'';

} else if ($_GET['date1'] && $_GET['date2']){

    $sql_dated = ' DATE(app_date) BETWEEN \''.$_GET['date1'].'\' AND \''.$_GET['date2'].'\'';
    $sql_dated2 = ' DATE(app_date) BETWEEN \''.$_GET['date1'].'\' AND \''.$_GET['date2'].'\'';

} else {

    $sql_dated = ' `app_date` REGEXP \''.$date.'\'' ;
    $sql_dated2 =  '`app_date` REGEXP \''.$date2.'\'';

}


$sql1 = mysqli_query($db, 'SELECT * FROM `repairs` where '.$sql_dated2.' and `status_admin` = \'Подтвержден\' and `deleted` = 0 '.$not_r97.' '.$sql_type.' '.$sql_cat_id.' order by `id` DESC ;');
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


$sql2 = mysqli_query($db, 'SELECT * FROM `repairs` where '.$sql_dated.' and `status_admin` = \'Подтвержден\' and `deleted` = 0 '.$not_r97.' '.$sql_type.' '.$sql_cat_id.' order by `id` DESC ;');
      while ($row2 = mysqli_fetch_array($sql2)) {
      $services[$row2['service_id']] += 1;
      $total += 1;
      }
arsort($services);
$id = 1;
foreach ($services as $service_id => $count) {
$req = get_request_info_serice($service_id);
// echo $prev[$service_id];
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
  global $db, $not_r97;
if ($_GET['date']) {
$date = $_GET['date'];
$date11 = DateTime::createFromFormat("Y.m", $_GET['date']);
$date11->modify('-1 month');
$date2 = ((date("d") < 5) ? $date11->format('Y.m') : $date);
} else {
$date = ((date("d") < 5) ? date("Y.m") : date("Y.m"));
$date2 = ((date("d") < 5) ? date("Y.m", strtotime("-1 months")) : date("Y.m", strtotime("-1 months")));
}

if ($_GET['repair_type_id']) {
$sql_type = ' and `repair_type_id` = '.$_GET['repair_type_id'].' ';
}

if ($_GET['cat_id']) {
$sql_cat_id = ' and `cat_id` = '.$_GET['cat_id'].' ';
}



if ($_GET['date']) {

    $sql_dated = ' `app_date` REGEXP \''.$date.'\'' ;
    $sql_dated2 =  '`app_date` REGEXP \''.$date2.'\'';

} else if ($_GET['date1'] && $_GET['date2']){

    $sql_dated = ' DATE(app_date) BETWEEN \''.$_GET['date1'].'\' AND \''.$_GET['date2'].'\'';
    $sql_dated2 = ' DATE(app_date) BETWEEN \''.$_GET['date1'].'\' AND \''.$_GET['date2'].'\'';

} else {

    $sql_dated = ' `app_date` REGEXP \''.$date.'\'' ;
    $sql_dated2 =  '`app_date` REGEXP \''.$date2.'\'';

}


$sql1 = mysqli_query($db, 'SELECT * FROM `repairs` where '.$sql_dated.' and `status_admin` = \'Подтвержден\' and `deleted` = 0 '.$not_r97.' '.$sql_type.' '.$sql_cat_id.'  order by `id` DESC ;');
      while ($row1 = mysqli_fetch_array($sql1)) {
      $services1[$row1['model_id']] += 1;
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

$sql2 = mysqli_query($db, 'SELECT * FROM `repairs` where '.$sql_dated2.' and `status_admin` = \'Подтвержден\' and `deleted` = 0 '.$not_r97.' '.$sql_type.' '.$sql_cat_id.'  order by `id` DESC ;');
//echo 'SELECT * FROM `repairs` where `app_date` REGEXP \''.$date.'\' and `status_admin` = \'Подтвержден\' and `deleted` = 0 order by `id` DESC ;';
      while ($row2 = mysqli_fetch_array($sql2)) {
      $models[$row2['model_id']] += 1;
      $total += 1;
      }
arsort($models);
$id = 1;
foreach ($models as $model_id => $count) {
$model = model($model_id);
if ($prev[$model_id] && $prev[$model_id] > $id) {
$change = '<strong>(+<span style="color:green;">'.($prev[$model_id]-$id).'</span>)</strong>';
}
if ($prev[$model_id] && $prev[$model_id] < $id) {
$change = '<strong>(-<span style="color:red;">'.($id-$prev[$model_id]).'</span>)</strong>';
}

$body .= '<tr><td>'.$model['name'].' '.$change.'</a></td><td style="text-align:center;">'.$count.'</td><td>'.round(($count/$total)*100).'%</td></tr>';
unset($change);
if ($id == 10) {
break;
}
$id++;
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

if ($_GET['repair_type_id']) {
$sql_type = ' and `repair_type_id` = '.$_GET['repair_type_id'].' ';
}

if ($_GET['cat_id']) {
$sql_cat_id = ' and `cat_id` = '.$_GET['cat_id'].' ';
}



if ($_GET['date']) {

    $sql_dated = ' `app_date` REGEXP \''.$date.'\'' ;
    $sql_dated2 =  '`app_date` REGEXP \''.$date2.'\'';

} else if ($_GET['date1'] && $_GET['date2']){

    $sql_dated = ' DATE(app_date) BETWEEN \''.$_GET['date1'].'\' AND \''.$_GET['date2'].'\'';
    $sql_dated2 = ' DATE(app_date) BETWEEN \''.$_GET['date1'].'\' AND \''.$_GET['date2'].'\'';

} else {

    $sql_dated = ' `app_date` REGEXP \''.$date.'\'' ;
    $sql_dated2 =  '`app_date` REGEXP \''.$date2.'\'';

}


$sql1 = mysqli_query($db, 'SELECT * FROM `repairs` where '.$sql_dated2.' and `status_admin` = \'Подтвержден\' and `deleted` = 0 '.$sql_type.' '.$sql_cat_id.'  order by `id` DESC ;');
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

$sql2 = mysqli_query($db, 'SELECT * FROM `repairs` where '.$sql_dated.' and `status_admin` = \'Подтвержден\' and `deleted` = 0 '.$sql_type.' '.$sql_cat_id.' order by `id` DESC ;');
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

function get_repair_stats() {
  global $db, $not_r97;
if ($_GET['date']) {
$date = $_GET['date'];
} else {
$date = ((date("d") < 5) ? date("Y.m") : date("Y.m"));
}

if ($_GET['repair_type_id']) {
$sql_type = ' and `repair_type_id` = '.$_GET['repair_type_id'].' ';
}

if ($_GET['cat_id']) {
$sql_cat_id = ' and `cat_id` = '.$_GET['cat_id'].' ';
}



if ($_GET['date']) {

    $sql_dated = ' `app_date` REGEXP \''.$date.'\'' ;

} else if ($_GET['date1'] && $_GET['date2']){

    $sql_dated = ' DATE(app_date) BETWEEN \''.$_GET['date1'].'\' AND \''.$_GET['date2'].'\'';

} else {

    $sql_dated = ' `app_date` REGEXP \''.$date.'\'' ;

}

$sql = mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` where `repair_type_id` = 5 and '.$sql_dated.' and `deleted` != 1 '.$not_r97.' '.$sql_type.' '.$sql_cat_id.';');
$blocks .= mysqli_fetch_array($sql)['COUNT(*)'].', ';
$sql = mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` where `repair_type_id` = 4 and '.$sql_dated.' and `deleted` != 1 '.$not_r97.' '.$sql_type.' '.$sql_cat_id.';');
$blocks .= mysqli_fetch_array($sql)['COUNT(*)'].', ';
$sql = mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` where `repair_type_id` = 1 and '.$sql_dated.' and `deleted` != 1 '.$not_r97.' '.$sql_type.' '.$sql_cat_id.';');
$blocks .= mysqli_fetch_array($sql)['COUNT(*)'].', ';
$sql = mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` where `repair_type_id` = 2 and '.$sql_dated.' and `deleted` != 1 '.$not_r97.' '.$sql_type.' '.$sql_cat_id.';');
$blocks .= mysqli_fetch_array($sql)['COUNT(*)'].', ';
$sql = mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` where `repair_type_id` = 3 and '.$sql_dated.' and `deleted` != 1 '.$not_r97.' '.$sql_type.' '.$sql_cat_id.';');
$blocks .= mysqli_fetch_array($sql)['COUNT(*)'];
return $blocks;
}

function check_status() {
  global $db, $not_r97;
$sql = mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` where (`status_admin` IN (\'В обработке\', \'Есть вопросы\', \'Подтвержден\', \'Отклонен\', \'Оплачен\', \'На проверке\', \'Запчасти в пути\', \'Нужны запчасти\', \'Запрос на выезд\', \'Выезд подтвержден\', \'Выезд отклонен\') or `repair_done` = 1) and `status_admin_read` = 1 and `deleted` != 1 '.$not_r97.';');
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

function get_request_info_serice($id) {
  global $db;
$req = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `requests` WHERE `user_id` = '.$id));
return $req;
}

function cat($cat_id = '') {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `cats` where `name` != \'\';');

      while ($row = mysqli_fetch_array($sql)) {
      if ($cat_id == $row['id']) {
      $content .= '<option selected value="'.$row['id'].'">'.$row['name'].'</option>';
      } else {
       $content .= '<option value="'.$row['id'].'">'.$row['name'].'</option>';
      }
      }
    return $content;
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

?>
<!doctype html>
<html>
<head>
<meta charset=utf-8>
<title>Панель управления</title>
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
<script src="/_new-codebase/front/vendor/chart-js/2.7.1/chart.bundle.js"></script>

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
table.dataTable.row-border tbody tr:first-child th, table.dataTable.row-border tbody tr:first-child td, table.dataTable.display tbody tr:first-child th, table.dataTable.display tbody tr:first-child td {
   /* font-size: 16px;    */
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
}

</style>
<script >
// Таблица
$(document).ready(function() {



var randomScalingFactor = function() {
        return Math.round(Math.random() * 100);
    };

 var config = {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [
                    <?=get_repair_stats();?>
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
                "АТО",
                "АНРП",
                "Блочный ремонт",
                "Компонтентый ремонт",
                "Замена аксессуаров"
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
                text: 'Структура обращений'
            },
            animation: {
                animateScale: true,
                animateRotate: true
            },
             tooltips: {
    callbacks: {
      label: function(tooltipItem, data) {
        var dataset = data.datasets[tooltipItem.datasetIndex];
        var meta = dataset._meta[Object.keys(dataset._meta)[0]];
        var total = meta.total;
        var currentValue = dataset.data[tooltipItem.index];
        var percentage = parseFloat((currentValue/total*100).toFixed(1));
        return currentValue + ' (' + percentage + '%)';
      },
      title: function(tooltipItem, data) {
        return data.labels[tooltipItem[0].index];
      }
    }
  },
        },

    };


    window.onload = function() {
        var ctx = document.getElementById("chart-area").getContext("2d");
        window.myDoughnut = new Chart(ctx, config);
    };



 $('.need_work').tooltipster({
                              trigger: 'hover',
                              position: 'top',
                              animation: 'grow',
                              theme: 'tooltipster-shadow'
                          });

    $('.table_content').dataTable({
      stateSave:false,

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
      yearRange: '2017:2020',
      maxDate: new Date(<?=((date("d") < 5) ? date("Y, m, 0", strtotime("-2 months")) : date("Y, m, 0", strtotime("-1 months")));?>),
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

  $('#two-inputs').dateRangePicker(
  {
    separator : ' to ',
    getValue: function()
    {
      if ($('#date-range200').val() && $('#date-range201').val() )
        return $('#date-range200').val() + ' to ' + $('#date-range201').val();
      else
        return '';
    },
    setValue: function(s,s1,s2)
    {
      $('#date-range200').val(s1);
      $('#date-range201').val(s2);
    }
  });

  $('#two-inputs2').dateRangePicker(
  {
    separator : ' to ',
    getValue: function()
    {
      if ($('#date-range2002').val() && $('#date-range2012').val() )
        return $('#date-range2002').val() + ' to ' + $('#date-range2012').val();
      else
        return '';
    },
    setValue: function(s,s1,s2)
    {
      $('#date-range2002').val(s1);
      $('#date-range2012').val(s2);
    }
  });

$(".monthPicker2").datepicker({
    dateFormat: 'yy',
    changeMonth: true,
      changeYear: true,
      showButtonPanel: true,
      yearRange: '2017:2020',
      maxDate: new Date(<?=((date("d") < 5) ? date("Y, m, 0", strtotime("-2 months")) : date("Y, m, 0", strtotime("-1 months")));?>),
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

   /*$(document).on('change', 'input[name="r97"]', function() {
        var form = $(this).parent().parent().submit();
        //$('#checkb');
    }); */


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

</style>
<META HTTP-EQUIV="REFRESH" CONTENT="30">
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
    <form method="GET" style="padding-top:3px;">
    <div style="display:inline-block;padding-left:10px;"><a href="https://crm.r97.ru/stat/">Текущий</a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/ </div>
    <div style="display:inline-block;padding-left:10px;"><span style="width: 95px;display: inline-block;text-align:left;position:relative;">Год и месяц </span><br> <input type="text" class="monthPicker" name="date" style="width: 120px;    text-align: center;    height: 40px;    padding: 0;" value="<?=($_GET['date'] ? $_GET['date'] : '')?>"/></div>
    <div style="display:inline-block;padding-left:10px;"><span id="two-inputs">От <input type="text" id="date-range200" name="date1" style="    width: 120px;   height: 30px;padding:5px;" value="<?=($_GET['date1'] ? $_GET['date1'] : '')?>"/> До <input name="date2"  type="text" id="date-range201" style="    width: 120px;   height: 30px;padding:5px;" value="<?=($_GET['date2'] ? $_GET['date2'] : '')?>"/></span></div>
    <div style="display:inline-block;padding-left:10px;">R97 <input type="checkbox" value="1" name="r97" <?=($_COOKIE['r97'] == '1') ? 'checked' : '';?>> &nbsp;</div>
<div style="display:inline-block;padding-left:10px;">Тип ремонта &nbsp;&nbsp;<select style="max-width:150px;" class="select2 nomenu" name="repair_type_id">
<option value="">Все</option>
<option value="5">АТО</option>
<option value="4">АНРП</option>
<option value="1">Блочный ремонт</option>
<option value="2">Компонтентый ремонт</option>
<option value="3">Замена аксессуаров</option>
</select>&nbsp;&nbsp;&nbsp;<select style="max-width:150px;" class="select2 nomenu" name="cat_id">
<option value="">Все</option>
<?=cat($_GET['cat_id']);?>
</select></div>
    <div style="display:inline-block;padding-left:10px;"><input class="green_button" type="submit" style="display: inline-block;margin-left:15px;  vertical-align: middle;    height: 40px;    margin-top: -4px;" value="Применить" /></div>
    </form>
    </div>


    <br><br>

    <table>
  <tr>
    <td style="width:33%;padding-right:5px;">
      <h3>Последние 7 ремонтов</h3>
  <table class="display table_content" cellspacing="0" width="100%" data-paging="false" data-info="false" data-searching="false" data-ordering="false">
        <thead>
            <tr>
                <th align="left" data-priority="1">ID</th>
                <th align="left" data-priority="1">Создан</th>
                <th align="left" data-priority="1">СЦ</th>
                <th align="left" data-priority="1">Модель</th>
            </tr>
        </thead>

        <tbody>
        <?=top5_repairs_month();?>
        </tbody>
</table>
</td>
    <td style="width:33%;padding-right:5px;">
       <h3>Топ 10 СЦ</h3>
  <table class="display table_content" cellspacing="0" width="100%" data-paging="false" data-info="false" data-searching="false" data-ordering="false">
        <thead>
            <tr>
                <th align="left" data-priority="1">СЦ</th>
                <th align="left">Количество</th>
                <th align="left">%</th>

            </tr>
        </thead>

        <tbody>
         <?=top5_services_month();?>
        </tbody>
</table>
</td>
    <td style="width:33%">
      <h3>Сводка</h3>
  <table class="display table_content" cellspacing="0" width="100%" data-paging="false" data-info="false" data-searching="false" data-ordering="false">
        <thead>
            <tr>
                <th align="center" data-priority="1">Всего обращений:</th>

            </tr>
        </thead>

        <tbody>
        <tr>
        <td align="center"><?=total_month();?> (<?=total_month_minus();?>)</td>

        </tr>
        </tbody>
</table>

  <table class="display table_content" cellspacing="0" width="100%" data-paging="false" data-info="false" data-searching="false" data-ordering="false">
        <thead>
            <tr>
                <th align="center" data-priority="1">Сумма за этот период:</th>

            </tr>
        </thead>

        <tbody>
        <tr>
        <td align="center"><?=total_pay_month();?>,00 руб. (<?=total_pay_month_minus();?>,00 руб.)</td>

        </tr>
        </tbody>
</table>

 <div id="canvas-holder" height="300" width="500">
        <canvas id="chart-area" />
    </div>

</td>
</tr>
  <tr>
    <td style="width:66%;padding-right:5px" colspan="2">
       <h3>Топ 10 моделей</h3>
  <table class="display table_content" cellspacing="0" width="100%" data-paging="false" data-info="false" data-searching="false" data-ordering="false">
        <thead>
            <tr>
                <th align="left" data-priority="1">Модель</th>
                <th align="left" style="text-align:center;">Количество</th>
                <th align="left" data-priority="4" style="width:120px;">% от всех</th>
            </tr>
        </thead>

        <tbody>
          <?=total_models_month();?>
        </tbody>
</table>
</td>
    <td style="width:33%">
       <h3>Топ 10 городов</h3>
  <table class="display table_content" cellspacing="0" width="100%" data-paging="false" data-info="false" data-searching="false" data-ordering="false">
        <thead>
            <tr>
                <th align="left" data-priority="1">Город</th>
                <th align="left" style="text-align:center;">Количество</th>
                <th align="left" data-priority="4" style="width:120px;">%</th>

            </tr>
        </thead>

        <tbody>
         <?=total_cities_month();?>
        </tbody>
</table>
</td>
</tr>
</table>

</div>


        </div>
  </div>
</div>
</body>
</html>