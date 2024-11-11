<?php

use models\staff\Staff;
use models\User;

function get_warranty($model_id) {
    global $db;
$req = mysqli_fetch_array(mysqli_query($db, 'SELECT `warranty` FROM `models` WHERE `id` = '.$model_id));
return $req['warranty'];

}



function content_list() {
  global $db;

$time_start = microtime(true);

if ($_COOKIE['master_id']) {
$_GET['master_id'] = $_COOKIE['master_id'];
}
/*if ($_COOKIE['model_id']) {
$_GET['search_model_id'] = $_COOKIE['model_id'];
}
if ($_COOKIE['client_id']) {
$_GET['search_client_id'] = $_COOKIE['client_id'];
}
    */
  if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
  {

    if (in_array($_GET['iSortCol_0'], array(0, 4, 5, 6, 12, 18, 20)) && $_GET['sSortDir_0']) {

     switch($_GET['iSortCol_0']) {
     case 0:

     $sql_model = mysqli_query($db, 'SELECT `id`,`model_id` FROM `repairs` where `model_name` = \'\'');
      while ($row_model = mysqli_fetch_array($sql_model)) {
      $model_info = model_info($row_model['model_id']);
      mysqli_query($db, 'UPDATE `repairs` SET  `model_name` = \''.$model_info['name'].'\' where `id` = '.$row_model['id']);
      //echo  'UPDATE `repairs` SET  `model_name` = \''.$model_info['name'].'\' where `id` = '.$row_model['id'];
      }

     $sort_col = '`model_name`';
     break;
     case 4:
     $sort_col = '`receive_date`';
     break;
     case 5:
     $sort_col = '`ending`';
     break;
     case 6:
     $sort_col = '`master_app_date`';
     break;
     case 12:
     $sort_col = '`bugs`';
     break;
     case 18:
     $sort_col = '`repair_final`';
     break;
      case 20:
     $sort_col = '`status_admin`';
     break;

     }

     $sLimit = " LIMIT ".mysqli_real_escape_string($db, $_GET['iDisplayStart'] ).", ".mysqli_real_escape_string($db, $_GET['iDisplayLength'] );
    } else {
    $sLimit = " LIMIT ".mysqli_real_escape_string($db, $_GET['iDisplayStart'] ).", ".mysqli_real_escape_string($db, $_GET['iDisplayLength'] );
    }



  }

$where_model_user = ($_GET['search_model_id']) ? 'and `model_id` = '.$_GET['search_model_id'] : '';
$where_client_user = ($_GET['search_client_id']) ? 'and `client_id` = '.$_GET['search_client_id'] : '';

$select = '`id`,
`model_id`,
`master_app_date`,
`status_admin_read`,
`serial`,
`status_user_read`,
`client_id`,
`model_name`,
`name_shop`,
`create_date`,
`receive_date`,
`status_by_hand`,
`ending`,
`return_id`,
`client_type`,
`client`,
`rsc`,
`imported`,
`service_id`,
`total_price`,
`status_admin`,
`onway`,
`onway_type`,
`bugs`,
`repair_final`,
`begin_date`,
`finish_date`,
`master_user_id`,
`status_id`,
`app_date`,
`deleted`,
`sell_date`,
`repair_done`,
`anrp_use`,
`anrp_number`';

if ( isset($_GET['sSearch']) && $_GET['sSearch'] != "" ) {

$sql_total = @mysqli_fetch_array(mysqli_query($db, 'SELECT `id` FROM `returns` where `name` LIKE \'%'.$_GET['sSearch'].'%\' ;'));
if ($sql_total['id']) {
$sql_return = 'or
`return_id` = '.mysqli_real_escape_string($db, $sql_total['id']);
}

$sql_total_client = mysqli_query($db, 'SELECT `id` FROM `clients` where `name` LIKE \'%'.$_GET['sSearch'].'%\' ;');
if (mysqli_num_rows($sql_total_client) != false) {
while ($row_cliend = mysqli_fetch_array($sql_total_client)) {
$sql_client .= ' or
`client_id` = '.mysqli_real_escape_string($db, $row_cliend['id']);
}
}


$sWhere = '
and (`rsc` regexp \''.mysqli_real_escape_string($db, $_GET['sSearch']).'\'
or
`id` regexp \''.mysqli_real_escape_string($db, $_GET['sSearch']).'\'
or
`receive_date` regexp \''.mysqli_real_escape_string($db, $_GET['sSearch']).'\'
or
`finish_date` regexp \''.mysqli_real_escape_string($db, $_GET['sSearch']).'\'
or
`bugs` regexp \''.mysqli_real_escape_string($db, $_GET['sSearch']).'\'
or
`serial` regexp \''.mysqli_real_escape_string($db, $_GET['sSearch']).'\'
or
`create_date` regexp \''.mysqli_real_escape_string($db, $_GET['sSearch']).'\'
or
LOWER(`client`) regexp \''.mysqli_real_escape_string($db, $_GET['sSearch']).'\'
or
`client` regexp \''.mysqli_real_escape_string($db, $_GET['sSearch']).'\'
or
`imported_model` regexp \''.mysqli_real_escape_string($db, $_GET['sSearch']).'\'
or
`model_name` regexp \''.mysqli_real_escape_string($db, $_GET['sSearch']).'\'
'.$sql_return.'
'.$sql_client.'
) ';

//echo mb_detect_encoding($_GET['sSearch']);

// echo $sWhere;

}

if ($_GET['date1'] != '' && $_GET['date2'] != '') {
$where_date = ' and DATE(app_date) BETWEEN \''.mysqli_real_escape_string($db, $_GET['date1']).'\' AND \''.mysqli_real_escape_string($db, $_GET['date2']).'\'';
}

if ($_GET['status_check'] != '') {

$problems_array = problems_array_id();

if ($_GET['status_check'] == 1) {
    $status_check = ' and `problem_id` IN ('.$problems_array['test'].', '.$problems_array['repaira'].') and `repair_final` = 2 ';
}
if ($_GET['status_check'] == 2) {
    $status_check = ' and problem_id IN ('.$problems_array['repair'].') and `repair_final` = 2 ';
}
if ($_GET['status_check'] == 3) {
    $status_check = ' and problem_id IN ('.$problems_array['test'].','.$problems_array['repaira'].') and (`repair_final` = 3 or `repair_final` = 1) ';
}
if ($_GET['status_check'] == 4) {
    $status_check = ' and (problem_id IN ('.$problems_array['repair'].','.$problems_array['repaira'].')) ';
}
if ($_GET['status_check'] == 5) {
    $status_check = ' and problem_id IN ('.$problems_array['test'].') ';
}


//
}

if ($_GET['date12'] != '' && $_GET['date22'] != '') {
$where_date2 = ' and `receive_date` BETWEEN \''.mysqli_real_escape_string($db, $_GET['date12']).'\' AND \''.mysqli_real_escape_string($db, $_GET['date22']).'\'';
}
if ($_GET['date13'] != '' && $_GET['date23'] != '') {
$where_date3 = ' and `begin_date` BETWEEN \''.mysqli_real_escape_string($db, $_GET['date13']).'\' AND \''.mysqli_real_escape_string($db, $_GET['date23']).'\'';
}
if ($_GET['date14'] != '' && $_GET['date24'] != '') {
$where_date4 = ' and DATE(app_date) BETWEEN \''.mysqli_real_escape_string($db, $_GET['date14']).'\' AND \''.mysqli_real_escape_string($db, $_GET['date24']).'\'';
}
if ($_GET['date15'] != '' && $_GET['date25'] != '') {
$where_date5 = ' and `out_date` BETWEEN \''.mysqli_real_escape_string($db, $_GET['date15']).'\' AND \''.mysqli_real_escape_string($db, $_GET['date25']).'\'';
}
if ($_GET['target'] || $_GET['type_id']) {
if ($_GET['target'] != 'all') {

if ($_GET['target'] == 'courier' ) {
$where_target = ' and `onway` = 1';
} else if ($_GET['target'] == 'inwork') {
$where_target = ' and `status_admin` != \'Подтвержден\' and `status_admin` != \'Принят\'  and `status_admin` != \'\'  and `status_admin` != \'В работе\'  and `status_admin` != \'Выдан\' ';
} else if ($_GET['target'] == 'approve') {
$where_target = ' and (`status_admin` = \'Подтвержден\' or `status_admin` = \'Утилизирован\' or `status_admin` = \'Выдан\') ';
} else if ($_GET['target'] == 'needparts') {
$where_target = ' and `status_admin` = \'Нужны запчасти\'';
} else if ($_GET['target'] == 'partsintransit') {
$where_target = ' and `status_admin` = \'Запчасти в пути\'';
} else if ($_GET['target'] == 'cancelled') {
$where_target = ' and `status_admin` = \'Отклонен\'';
} else if ($_GET['target'] == 'inprogress') {
$where_target = ' and `status_admin` = \'В работе\'';
} else if ($_GET['target'] == 'accepted') {
$where_target = ' and `status_admin` = \'Принят\'';
} else if ($_GET['target'] == 'ready') {
$where_target = ' and `status_admin` = \'Подтвержден\'';
} else if ($_GET['target'] == 'out') {
$where_target = ' and `status_admin` = \'Выдан\'';
} else if ($_GET['target'] == 'trash') {
$where_target = ' and (`status_admin` = \'Подтвержден\' or `status_admin` = \'Выдан\') and `problem_id` IN (3,6,14,15,16,18,19,24,25,26,27,28,29,30,31,33,35,39,41,43) ';
} else if ($_GET['target'] == 'resell') {
$where_target = ' and (`status_admin` = \'Подтвержден\' or `status_admin` = \'Выдан\') and `problem_id` IN (2,4,7,8,9,10,11,12,17,20,21,36,37,40,42) ';
} else if ($_GET['target']) {
$where_target = ' and `status_admin` = \''.mysqli_real_escape_string($db, $_GET['target']).'\'';
}

}

} else {

if (\models\User::hasRole('admin')) {
$where_target = ' and `status_admin` != \'Подтвержден\' and `status_admin` != \'\'';
} else  {

$where_master_user = ($_GET['master_id']) ? 'and `master_user_id` = '.$_GET['master_id'] : '';

if (!$where_master_user) {
//$where_target = ' and `status_admin` != \'Подтвержден\' ';
}

}


}

if ($_GET['type_id']) {
if ($_GET['type_id'] == 3) {
$type = 2;
$where_type = ' and `client_type` = \''.mysqli_real_escape_string($db, 2).'\'';
}
if ($_GET['type_id'] == 1) {
$type = 1;
$where_type = ' and `client_type` = \''.mysqli_real_escape_string($db, 1).'\'';
}

unset($type);
}

if (!\models\User::hasRole('admin')) {

$where_impo_user = ($_GET['impo'] == 1) ? 'and `status_user_read` = 1 ' : '';


if (\models\User::hasRole('service')) {
$services_arr[] = User::getData('id');
$sql2 = mysqli_query($db, 'SELECT * FROM `services_link` WHERE `service_parent` = '.User::getData('id'));
if (mysqli_num_rows($sql2) != false) {
while ($row2 = mysqli_fetch_array($sql2)) {
$services_arr[] .= $row2['service_child'];
}
}

$sql = mysqli_query($db, 'SELECT '.$select.' FROM `repairs` where `service_id` IN ('.implode(',', $services_arr).') and `deleted` != 1 '.$where_date.' '.$where_date2.' '.$where_date3.' '.$where_date4.' '.$where_date5.' '.$where_target.' '.$where_type.' '.$where_impo_user.' '.$where_master_user.' '.$where_model_user.' '.$where_client_user.' '.$sWhere.' '.$sOrder.' '.$sLimit.';');
$sql_total = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` where `service_id` IN ('.implode(',', $services_arr).') and `deleted` != 1 '.$where_date.' '.$where_date2.' '.$where_date3.' '.$where_date4.' '.$where_date5.' '.$where_target.' '.$where_type.' '.$where_impo_user.' '.$where_master_user.' '.$where_model_user.' '.$where_client_user.' '.$sWhere.'  ;'));

} if (\models\User::hasRole('master', 'taker')) {

$sql = mysqli_query($db, 'SELECT '.$select.' FROM `repairs` WHERE id IN ( SELECT MAX(id) FROM repairs where `deleted` != 1 and doubled = 1 '.$where_date.' '.$status_check.' GROUP BY serial ) ORDER BY `receive_date` desc '.$sLimit.';');
$sql_total = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` WHERE id IN ( SELECT MAX(id) FROM repairs where `deleted` != 1 and doubled = 1 '.$where_date.' '.$status_check.' GROUP BY serial ) ORDER BY `receive_date` desc '));


}  else {
$sql = mysqli_query($db, 'SELECT '.$select.' FROM `repairs` WHERE id IN ( SELECT MAX(id) FROM repairs where `deleted` != 1 and doubled = 1 '.$where_date.' '.$status_check.' GROUP BY serial ) ORDER BY `receive_date` desc '.$sLimit.';');
//echo 'SELECT '.$select.' FROM `repairs` WHERE id IN ( SELECT MAX(id) FROM repairs where doubled = 1 '.$where_date.' '.$status_check.' GROUP BY serial ) ORDER BY STR_TO_DATE(date_get,\'%d.%m.%Y\') desc '.$sLimit.';';
$sql_total = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` WHERE id IN ( SELECT MAX(id) FROM repairs where `deleted` != 1 and doubled = 1 '.$where_date.' '.$status_check.' GROUP BY serial ) ORDER BY `receive_date` desc '));
//echo  'SELECT '.$select.' FROM `repairs` where `service_id` = '.User::getData('id').' and `deleted` != 1 '.$where_date.' '.$where_date2.' '.$where_date3.' '.$where_date4.' '.$where_date5.' '.$where_target.' '.$where_type.' '.$where_impo_user.' '.$where_master_user.' '.$where_model_user.' '.$where_client_user.' '.$sWhere.' '.$sOrder.' '.$sLimit.';';
//echo 'SELECT '.$select.' FROM `repairs` where `service_id` = '.User::getData('id').' and `deleted` != 1 '.$where_date.' '.$where_date2.' '.$where_date3.' '.$where_date4.' '.$where_date5.' '.$where_target.' '.$where_type.' '.$where_impo_user.' '.$where_master_user.' '.$where_model_user.' '.$where_client_user.' '.$sWhere.' '.$sOrder.' '.$sLimit.';';
//echo 'SELECT '.$select.' FROM `repairs` where `service_id` = '.User::getData('id').' and `deleted` != 1 '.$where_date.' '.$where_date2.' '.$where_date3.' '.$where_date4.' '.$where_date5.' '.$where_target.' '.$where_type.' '.$where_impo_user.' '.$where_master_user.' '.$where_model_user.' '.$where_client_user.' '.$sWhere.' '.$sOrder.' '.$sLimit.';';
}


} else {


$where_impo_admin = ($_GET['impo'] == 1) ? ' and `status_admin_read` = 1 ' : '';

if ($_GET['get'] == 'deleted') {
$sql = mysqli_query($db, 'SELECT '.$select.' FROM `repairs` WHERE id IN ( SELECT MAX(id) FROM repairs where `deleted` != 1 and doubled = 1 '.$where_date.' '.$status_check.' GROUP BY serial ) ORDER BY `receive_date` desc '.$sLimit.';');
$sql_total = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` WHERE id IN ( SELECT MAX(id) FROM repairs where `deleted` != 1 and doubled = 1 '.$where_date.' '.$status_check.' GROUP BY serial ) ORDER BY `receive_date` desc '));

} else {
$sql = mysqli_query($db, 'SELECT '.$select.' FROM `repairs` WHERE id IN ( SELECT MAX(id) FROM repairs where `deleted` != 1 and doubled = 1 '.$where_date.'-+ GROUP BY serial ) ORDER BY `receive_date` desc '.$sLimit.';');
$sql_total = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` WHERE id IN ( SELECT MAX(id) FROM repairs where `deleted` != 1 and doubled = 1 '.$where_date.' '.$status_check.' GROUP BY serial ) ORDER BY `receive_date` desc '));

//echo 'SELECT '.$select.' FROM `repairs` where `deleted` != 1  '.$where_date.' '.$where_date2.' '.$where_date3.' '.$where_date4.' '.$where_date5.' '.$where_target.' '.$where_type.' '.$where_impo_admin.' '.$where_master_user.' '.$where_model_user.' '.$where_client_user.' '.$sWhere.' '.$sLimit.'';
}

}
if (mysqli_num_rows($sql) != false) {
      while ($row = mysqli_fetch_array($sql)) {


      $status = array(1 => 'Гарантийный', 2 => 'Платный', 3 => 'Повторный', 4 => 'Предпродажный', 5 => 'Условно-гарантийный');

      $model = model($row['model_id']);

      if (!User::hasRole('master') || (User::hasRole('master') && check_allow($model['cat'], User::getData('id')))){






      if ($row['client_id']) {
      $client_info = mysqli_fetch_array(mysqli_query($db, 'SELECT `name` FROM `clients` WHERE `id` = \''.mysqli_real_escape_string($db, $row['client_id']).'\' LIMIT 1;'));
      } else {
      $client_info['name'] = htmlentities($row['name_shop']);
      }

      if ($row['receive_date'] != '0000-00-00') {

      try {
          $date_new = new DateTime($row['receive_date']);
          $date_ready = $date_new->format("d.m.Y");
      } catch (Exception $e) {

      }
      }

       if ($draw_color == '') {
     $draw_color = (($row['imported'] == 1 && !check_serial_imported($row['serial'], $model['id'])) || $row['serial'] == '' || $row['serial'] == 'NULL' || ($row['client_type'] == 1 && $row['name_shop'] == '') || $row['model_id'] == '' ||  $row['bugs'] == '') ? 'yellow' : '';
     }

      if ($draw_color == '') {
     $draw_color = ($row['anrp_use'] != 0 && $row['anrp_number'] != '') ? 'blue' : '';
     }



     $double_pls = (models\Repair::isRepeated($row['id'])) ? '1' : '0';

     if ($draw_color == '' && $row['status_id'] == 6) {
     $draw_color = 'darkgreen';

     }

      if ($draw_color == '' &&  $date_ready != '' && $row['sell_date'] != '0000-00-00'  && $row['status_id'] == 5) {
     $date2 = DateTime::createFromFormat('Y-m-d', $row['sell_date']);
     if ($date2) {
     $date2_row = $date2->modify('+'.get_warranty($row['model_id']).' days');
     }
      $date3_row = DateTime::createFromFormat('d.m.Y', $date_ready);
       $draw_color = ($date3_row < $date2_row) ? '' : 'grey';


     }

       if ($draw_color  == '') {
       $draw_color = ($row['status_by_hand'] == 1) ? 'grey' : '';
       }

      if ($draw_color == '') {
     $draw_color = (models\Repair::isRepeated($row['id'])) ? 'red' : '';
     }



     /*if ($draw_color == '') {
       if (\models\User::hasRole('admin')) {
      $draw_color = ($row['status_admin_read'] == 0) ? 'green' : '';
      if ($row['serial']) {
      $draw_color3 = (mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` WHERE `serial` = \''.mysqli_real_escape_string($db, $row['serial']).'\' and `deleted` != 1 and `id` != '.$row['id']))['COUNT(*)'] > 0) ? 'redone' : '';
      }
      } else {
      $draw_color = ($row['status_user_read'] == 0) ? 'green' : '';
      }
      }   */




      if ($double_pls == 1) {



      //$draw_color2 = ($config['last_admin'] == $row['id']) ? 'orange' : '';
      $draw_color2 = ($model['name']) ? '' : 'redone';
      if ($draw_color) {
      $content_list['DT_RowClass'] = $draw_color;
      }
      if ($draw_color2) {
      $content_list['DT_RowClass'] = $draw_color2;
      }
      if ($draw_color3) {
      $content_list['DT_RowClass'] = $draw_color3;
      }
      if ($draw_color4) {
      $content_list['DT_RowClass'] = $draw_color4;
      }
      if ($row['create_date'] != '0000-00-00 00:00:00') {

      try {
          $date_created = new DateTime($row['create_date']);
          $date_ready_created = $date_created->format("d.m.Y");
      } catch (Exception $e) {

      }
      }

      if ($row['client_id']) {
      $client = mysqli_fetch_array(mysqli_query($db, 'SELECT `days` FROM `clients` WHERE `id` = \''.mysqli_real_escape_string($db, $row['client_id']).'\' limit 1'));
      $edning = date("d.m.Y", strtotime($row['receive_date'] . " +".$client['days']." days"));
      $edning_sql = date("Y-m-d", strtotime($row['receive_date'] . " +".$client['days']." days"));
      if ($row['ending'] == '0000-00-00') {
        mysqli_query($db, 'UPDATE `repairs` SET `ending` = \''.$edning_sql.'\' WHERE `id` = \''.mysqli_real_escape_string($db, $row['id']).'\' ;') or mysqli_error($db);
      }
      } else {
      $edning = date("d.m.Y", strtotime($row['receive_date'] . " +45 days"));
      $edning_sql = date("Y-m-d", strtotime($row['receive_date'] . " +45 days"));
      if ($row['ending'] == '0000-00-00') {

        mysqli_query($db, 'UPDATE `repairs` SET `ending` = \''.$edning_sql.'\' WHERE `id` = \''.mysqli_real_escape_string($db, $row['id']).'\' ;') or mysqli_error($db);
      }
      }
$return = mysqli_fetch_array(mysqli_query($db, 'SELECT `name`,`date_farewell` FROM `returns` WHERE `id` = \''.mysqli_real_escape_string($db, $row['return_id']).'\' limit 1'));

      if ($row['client_type'] == 1) {
      $clientz = $row['client'];
      $clientz2 = $row['name_shop'].' /';
      } else {
       $clientz = $client_info['name'];
       $clientz2 = $return['name'].' /';
      }



      $content_list[] = ($model['name']) ? $model['name'] : '!!!';
      //$content_list[] = $row['serial'];
      $req = get_request_info_serice($row['service_id']);
      $content_list[] =  strtoupper($row['serial']);

      //(A) Фолиум / FDS9900999 / закончить до 21.05.2018

      $content_list[] = $req['name'];
      $content_list[] = ($row['anrp_use']) ? '<a target="_blank" href="https://crm.r97.ru/edit-repair/'.preg_replace('/\D/', '', $row['anrp_number']).'/step/2/">'.$row['anrp_number'].'</a>' : '';



      $content_list[] = $date_ready;
      $content_list[] = date("d.m.Y", strtotime($row['ending']));
      $content_list[] = ($row['master_app_date'] != '') ? date_format(date_create_from_format('Y.m.d', $row['master_app_date']), 'd.m.Y') : '-';


      if (!User::hasRole('admin')) {
      $content_list[] = $row['rsc'];
      }
      $content_list[] = $row['id'];

      $content_list[] = $date_ready_created;

      if (\models\User::hasRole('admin')) {
      $req = get_request_info_serice($row['service_id']);
      $content_list[] = $req['adress'];
      $content_list[] = $req['name'];
      $content_list[] = $row['total_price'];
      $content_list[] = parts_price($row['id']);
      $content_list[] = (($row['status_admin'] != 'Запрос на выезд' && $row['onway'] == 1) ? get_price_transfer($model['cat'], $row['onway_type'], $row['service_id']) : '0');
      } else {

      }

  $content_list[] = $client_info['name'];
    $content_list[] = $row['client'];
    $content_list[] = $row['bugs'];

    $content_list[] = $return['name'];

    if ($row['repair_final'] == 1) {
    $content_list[] = 'Дефект не обнаружен';
    } else if ($row['repair_final'] == 2) {
    $content_list[] = 'Подтвердилось';
    } else if ($row['repair_final'] == 3) {
    $content_list[] = 'Отказано в гарантии';
    } else {
    $content_list[] = '';
    }


  
    $content_list[] = program\core\Time::format($row['begin_date']);
    $content_list[] = program\core\Time::format($row['finish_date']);
    if (\models\User::hasRole('admin')) {
    $content_list[] = $row['rsc'];
    }

      if (\models\User::hasRole('slave-admin', 'taker')) {

      if ($row['master_user_id']) {
      $master = Staff::getStaff(['id' => $row['master_user_id']]);
      }
      $content_list[] = $master['surname'].' '.$master['name'].' '.$master['thirdname'];
      }

      $content_list[] = $status[$row['status_id']];

      $linkz .= '<a class="t-3" title="Редактировать карточку" href="/edit-repair/'.$row['id'].'/" ></a><a class="t-1" style="    background: url(../img/n.png) 0 0 no-repeat;" title="Скачать наклейку" href="/get-label/'.$row['id'].'/" ></a><a class="t-1" style="    background: url(../img/k.png) 0 0 no-repeat;" title="Скачать квитанцию" href="/get-receipt/'.$row['id'].'/" ></a><input style="    vertical-align: middle;" type="checkbox" class="download_mass" data-id="'.$row['id'].'">&nbsp;';

      if ($row['deleted'] != 1) {
      if (\models\User::hasRole('admin')) {
      $linkz .= '<a class="t-5" title="Удалить карточку" href="/del-repair/'.$row['id'].'/" ></a><input data-id="'.$row['id'].'" style=" display:block;   width: 98px;     height: 32px;    padding: 3px;  margin-top: 5px;    font-size: 19px;" class="datepicker2 metro-skin" type="text" name="date_app" value="'.$row['app_date'].'"  />';
      } else {

      if ($row['status_admin'] != 'Подтвержден') {
      $linkz .= '<a class="t-5" onclick=\'return confirm("Восстановление удаленной заявки возможно только через администратора сервисной базы. Удалить заявку?")\' title="Удалить карточку" href="/del-repair/'.$row['id'].'/" ></a>';
      }

      }
      } else {
      if (\models\User::hasRole('admin')) {
      $linkz .= '<a class="t-2" title="Восстановить карточку" href="/comeback-repair/'.$row['id'].'/" ></a>';
      }
      }


      if (!\models\User::hasRole('admin') && $row['repair_done'] != 1 && !\models\User::hasRole('taker')) {
      if (check_complete($row['id'])) {
      $linkz .= '<a class="mod_fast_true" title="Отправить на проверку" style="background:none;    padding-bottom: 6px;" href="/repair-done/'.$row['id'].'/"><img src="/img/true.png"></a>';
      } else {
      $linkz .= '<a title="Не заполнены все необходимые поля" class="mod_fast_true need_work" title="Отправить на проверку" style="background:none;    padding-bottom: 6px;" href="#" onclick="return false"><img src="/img/true_yellow.png"></a>';
      }
      }

      if (User::getData('id') == 33) {
      $linkz .= '<span style="position:relative;" class="master_change"><a class="add_master" title="Назначить мастера" style="background:none;    padding-bottom: 6px;" href="#"><img style="    height: 17px;   margin-left: 5px;" src="/img/settings.png"></a><select data-id="'.$row['id'].'" name="master_id">
               <option value="0">Выберите мастера</option>
               '.masters_list($row['master_user_id']).'
              </select></span>';
      }

      $confirm = ($row['master_user_id'] != 0) ? 'onclick=\'return confirm("Подтвердите сброс статусов и возврат ремонта в работу.")\'' : ''; //onclick=\'alert("Сначала выберите мастера!");return false\'

      if (User::getData('id') == 33 && $row['status_admin'] != '') {
      $linkz .= '<a class="t-2" style="margin-left: 6px;" '.$confirm.' title="Вернуть на доработку" href="/re-edit-repair/'.$row['id'].'/" ></a>';

      }

      if (\models\User::hasRole('slave-admin', 'taker')) {
      if ($draw_color == 'red'  || $double_pls == 1) {
        $linkz .= '<a class="t-4" style="margin-left: 6px;" title="Показать дубли" data-fancybox data-type="iframe" data-src="/show-double/'.$row['serial'].'/'.$row['id'].'/" href="javascript:;" ></a>';

      }

      }
      $content_list[] = '<div class="linkz">'.$linkz.'</div>';


      if (\models\User::hasRole('admin')) {
      $content_list[] = '<form method="POST"><select  name="status_admin" data-app-date="'.$row['app_date'].'" data-repair-id="'.$row['id'].'"><option value="">Без статуса</option><option value="В обработке" '.(($row['status_admin'] == 'В обработке') ? 'selected' : '').'>В обработке</option><option value="Есть вопросы" '.(($row['status_admin'] == 'Есть вопросы') ? 'selected' : '').'>Есть вопросы</option><option value="Подтвержден" '.(($row['status_admin'] == 'Подтвержден') ? 'selected' : '').'>Подтвержден</option><option value="Отклонен" '.(($row['status_admin'] == 'Отклонен') ? 'selected' : '').'>Отклонен</option><option value="Оплачен" '.(($row['status_admin'] == 'Оплачен') ? 'selected' : '').'>Оплачен</option><option value="На проверке" '.(($row['status_admin'] == 'На проверке') ? 'selected' : '').'>На проверке</option><option value="Нужны запчасти" '.(($row['status_admin'] == 'Нужны запчасти') ? 'selected' : '').'>Нужны запчасти</option><option value="Запчасти в пути" '.(($row['status_admin'] == 'Запчасти в пути') ? 'selected' : '').'>Запчасти в пути</option><option value="Запрос на выезд" '.(($row['status_admin'] == 'Запрос на выезд') ? 'selected' : '').'>Запрос на выезд</option><option value="Выезд подтвержден" '.(($row['status_admin'] == 'Выезд подтвержден') ? 'selected' : '').'>Выезд подтвержден</option><option value="Выезд отклонен" '.(($row['status_admin'] == 'Выезд отклонен') ? 'selected' : '').'>Выезд отклонен</option><option value="Принят" '.(($row['status_admin'] == 'Принят') ? 'selected' : '').'>Принят</option><option value="Выдан" '.(($row['status_admin'] == 'Выдан') ? 'selected' : '').'>Выдан</option><option value="Утилизирован" '.(($row['status_admin'] == 'Утилизирован') ? 'selected' : '').'>Утилизирован</option><option value="В работе" '.(($row['status_admin'] == 'В работе') ? 'selected' : '').'>В работе</option></select></form>';
      } if (\models\User::hasRole('slave-admin', 'taker')) {
      $content_list[] = '<form method="POST"><select  name="status_admin" data-app-date="'.$row['app_date'].'" data-repair-id="'.$row['id'].'"><option value="">Без статуса</option> <option value="В обработке" '.(($row['status_admin'] == 'В обработке') ? 'selected' : '').'>В обработке</option> <option value="В работе" '.(($row['status_admin'] == 'В работе') ? 'selected' : '').'>В работе</option> <option value="Выдан" '.(($row['status_admin'] == 'Выдан') ? 'selected' : '').'>Выдан</option> <option value="Выезд отклонен" '.(($row['status_admin'] == 'Выезд отклонен') ? 'selected' : '').'>Выезд отклонен</option> <option value="Выезд подтвержден" '.(($row['status_admin'] == 'Выезд подтвержден') ? 'selected' : '').'>Выезд подтвержден</option> <option value="Есть вопросы" '.(($row['status_admin'] == 'Есть вопросы') ? 'selected' : '').'>Есть вопросы</option> <option value="Запрос на выезд" '.(($row['status_admin'] == 'Запрос на выезд') ? 'selected' : '').'>Запрос на выезд</option> <option value="Запчасти в пути" '.(($row['status_admin'] == 'Запчасти в пути') ? 'selected' : '').'>Запчасти в пути</option> <option value="На проверке" '.(($row['status_admin'] == 'На проверке') ? 'selected' : '').'>На проверке</option> <option value="Нужны запчасти" '.(($row['status_admin'] == 'Нужны запчасти') ? 'selected' : '').'>Нужны запчасти</option> <option value="Оплачен" '.(($row['status_admin'] == 'Оплачен') ? 'selected' : '').'>Оплачен</option> <option value="Отклонен" '.(($row['status_admin'] == 'Отклонен') ? 'selected' : '').'>Отклонен</option> <option value="Подтвержден" '.(($row['status_admin'] == 'Подтвержден') ? 'selected' : '').'>Подтвержден</option> <option value="Принят" '.(($row['status_admin'] == 'Принят') ? 'selected' : '').'>Принят</option> <option value="Утилизирован" '.(($row['status_admin'] == 'Утилизирован') ? 'selected' : '').'>Утилизирован</option> </select></form>';
      } else {
      $app_date = ($row['app_date'] && $row['status_admin'] == 'Подтвержден') ? '<br>('.$row['app_date'].')' : '';
      $content_list[] = $row['status_admin'].$app_date;
      }

      if (\models\User::hasRole('master', 'slave-admin', 'taker')) {
      $content_list[] = $return['date_farewell'];
      }

      $rows[] = $content_list;
      unset($content_list);
      unset($double_pls);
      unset($linkz);
      unset($date_ready);
      unset($date_new);
      unset($date_ready_created);
      unset($master);
      unset($return);
      unset($clientz);
      unset($clientz2);
      unset($draw_color);

      }

      }

      /* ДУБЛИ */

     $sql2 = mysqli_query($db, 'SELECT * FROM `repairs` where `serial` = \''.mysqli_real_escape_string($db, $row['serial']).'\' and id != '.$row['id'].';');
        while ($row2 = mysqli_fetch_array($sql2)) {

      if (!\models\User::hasRole('master') || (\models\User::hasRole('master') && check_allow($model['cat'], User::getData('id')))){






      if ($row2['client_id']) {
      $client_info = mysqli_fetch_array(mysqli_query($db, 'SELECT `name` FROM `clients` WHERE `id` = \''.mysqli_real_escape_string($db, $row2['client_id']).'\' LIMIT 1;'));
      } else {
      $client_info['name'] = htmlentities($row2['name_shop']);
      }

      if ($row2['receive_date'] != '0000-00-00') {

      try {
          $date_new = new DateTime($row2['receive_date']);
          $date_ready = $date_new->format("d.m.Y");
      } catch (Exception $e) {

      }
      }

       if ($draw_color == '') {
     $draw_color = (($row2['imported'] == 1 && !check_serial_imported($row2['serial'], $model['id'])) || $row2['serial'] == '' || $row2['serial'] == 'NULL' || ($row2['client_type'] == 1 && $row2['name_shop'] == '') || $row2['model_id'] == '' ||  $row2['bugs'] == '') ? 'yellow' : '';
     }

      if ($draw_color == '') {
     $draw_color = ($row2['anrp_use'] != 0 && $row2['anrp_number'] != '') ? 'blue' : '';
     }



     $double_pls = (models\Repair::isRepeated($row2['id'])) ? '1' : '0';

     if ($draw_color == '' && $row2['status_id'] == 6) {
     $draw_color = 'darkgreen';

     }

      if ($draw_color == '' &&  $date_ready != '' && $row2['sell_date'] != '0000-00-00'  && $row2['status_id'] == 5) {
     $date2 = DateTime::createFromFormat('Y.m.d', $row2['sell_date']);
     if ($date2) {
     $date2_row = $date2->modify('+'.get_warranty($row2['model_id']).' days');
     }
      $date3_row = DateTime::createFromFormat('d.m.Y', $date_ready);
       $draw_color = ($date3_row < $date2_row) ? '' : 'grey';


     }

       if ($draw_color  == '') {
       $draw_color = ($row2['status_by_hand'] == 1) ? 'grey' : '';
       }

      if ($draw_color == '') {
     $draw_color = (models\Repair::isRepeated($row2['id'])) ? 'red' : '';
     }



     /*if ($draw_color == '') {
       if (\models\User::hasRole('admin')) {
      $draw_color = ($row2['status_admin_read'] == 0) ? 'green' : '';
      if ($row2['serial']) {
      $draw_color3 = (mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` WHERE `serial` = \''.mysqli_real_escape_string($db, $row2['serial']).'\' and `deleted` != 1 and `id` != '.$row2['id']))['COUNT(*)'] > 0) ? 'redone' : '';
      }
      } else {
      $draw_color = ($row2['status_user_read'] == 0) ? 'green' : '';
      }
      }   */




      if ($double_pls == 1) {



      //$draw_color2 = ($config['last_admin'] == $row2['id']) ? 'orange' : '';
      $draw_color2 = ($model['name']) ? '' : 'redone';
      if ($draw_color) {
      $content_list['DT_RowClass'] = $draw_color;
      }
      if ($draw_color2) {
      $content_list['DT_RowClass'] = $draw_color2;
      }
      if ($draw_color3) {
      $content_list['DT_RowClass'] = $draw_color3;
      }
      if ($draw_color4) {
      $content_list['DT_RowClass'] = $draw_color4;
      }
      if ($row2['create_date'] != '0000-00-00 00:00:00') {

      try {
          $date_created = new DateTime($row2['create_date']);
          $date_ready_created = $date_created->format("d.m.Y");
      } catch (Exception $e) {

      }
      }

      if ($row2['client_id']) {
      $client = mysqli_fetch_array(mysqli_query($db, 'SELECT `days` FROM `clients` WHERE `id` = \''.mysqli_real_escape_string($db, $row2['client_id']).'\' limit 1'));
      $edning = date("d.m.Y", strtotime($row2['receive_date'] . " +".$client['days']." days"));
      $edning_sql = date("Y-m-d", strtotime($row2['receive_date'] . " +".$client['days']." days"));
      if ($row2['ending'] == '0000-00-00') {
        mysqli_query($db, 'UPDATE `repairs` SET `ending` = \''.$edning_sql.'\' WHERE `id` = \''.mysqli_real_escape_string($db, $row2['id']).'\' ;') or mysqli_error($db);
      }
      } else {
      $edning = date("d.m.Y", strtotime($row2['receive_date'] . " +45 days"));
      $edning_sql = date("Y-m-d", strtotime($row2['receive_date'] . " +45 days"));
      if ($row2['ending'] == '0000-00-00') {

        mysqli_query($db, 'UPDATE `repairs` SET `ending` = \''.$edning_sql.'\' WHERE `id` = \''.mysqli_real_escape_string($db, $row2['id']).'\' ;') or mysqli_error($db);
      }
      }
$return = mysqli_fetch_array(mysqli_query($db, 'SELECT `name`,`date_farewell` FROM `returns` WHERE `id` = \''.mysqli_real_escape_string($db, $row2['return_id']).'\' limit 1'));

      if ($row2['client_type'] == 1) {
      $clientz = $row2['client'];
      $clientz2 = $row2['name_shop'].' /';
      } else {
       $clientz = $client_info['name'];
       $clientz2 = $return['name'].' /';
      }



      $content_list[] = ($model['name']) ? $model['name'] : '!!!';
      //$content_list[] = $row2['serial'];
      $req = get_request_info_serice($row2['service_id']);
      $content_list[] =  strtoupper($row2['serial']);

      //(A) Фолиум / FDS9900999 / закончить до 21.05.2018

      $content_list[] = $req['name'];
      $content_list[] = ($row2['anrp_use']) ? '<a target="_blank" href="https://crm.r97.ruu/edit-repair/'.preg_replace('/\D/', '', $row2['anrp_number']).'/step/2/">'.$row2['anrp_number'].'</a>' : '';



      $content_list[] = $date_ready;
      $content_list[] = date("d.m.Y", strtotime($row2['ending']));
      $content_list[] = ($row2['master_app_date'] != '') ? date_format(date_create_from_format('Y.m.d', $row2['master_app_date']), 'd.m.Y') : '-';


      if (!\models\User::hasRole('admin')) {
      $content_list[] = $row2['rsc'];
      }
      $content_list[] = $row2['id'];

      $content_list[] = $date_ready_created;

      if (\models\User::hasRole('admin')) {
      $req = get_request_info_serice($row2['service_id']);
      $content_list[] = $req['adress'];
      $content_list[] = $req['name'];
      $content_list[] = $row2['total_price'];
      $content_list[] = parts_price($row2['id']);
      $content_list[] = (($row2['status_admin'] != 'Запрос на выезд' && $row2['onway'] == 1) ? get_price_transfer($model['cat'], $row2['onway_type'], $row2['service_id']) : '0');
      } else {

      }

  $content_list[] = $client_info['name'];
    $content_list[] = $row2['client'];
    $content_list[] = $row2['bugs'];

    $content_list[] = $return['name'];

    if ($row2['repair_final'] == 1) {
    $content_list[] = 'Дефект не обнаружен';
    } else if ($row2['repair_final'] == 2) {
    $content_list[] = 'Подтвердилось';
    } else if ($row2['repair_final'] == 3) {
    $content_list[] = 'Отказано в гарантии';
    } else {
    $content_list[] = '';
    }


 
    $content_list[] = program\core\Time::format($row2['begin_date']);
    $content_list[] = program\core\Time::format($row2['finish_date']);
    if (\models\User::hasRole('admin')) {
    $content_list[] = $row2['rsc'];
    }

      if (\models\User::hasRole('slave-admin', 'taker')) {

      if ($row2['master_user_id']) {
      $master = Staff::getStaff(['id' => $row2['master_user_id']]);
      }
      $content_list[] = $master['surname'].' '.$master['name'].' '.$master['thirdname'];
      }

      $content_list[] = $status[$row2['status_id']];

      $linkz .= '<a class="t-3" title="Редактировать карточку" href="/edit-repair/'.$row2['id'].'/" ></a><a class="t-1" style="    background: url(../img/n.png) 0 0 no-repeat;" title="Скачать наклейку" href="/get-label/'.$row2['id'].'/" ></a><a class="t-1" style="    background: url(../img/k.png) 0 0 no-repeat;" title="Скачать квитанцию" href="/get-receipt/'.$row2['id'].'/" ></a><input style="    vertical-align: middle;" type="checkbox" class="download_mass" data-id="'.$row2['id'].'">&nbsp;';

      if ($row2['deleted'] != 1) {
      if (\models\User::hasRole('admin')) {
      $linkz .= '<a class="t-5" title="Удалить карточку" href="/del-repair/'.$row2['id'].'/" ></a><input data-id="'.$row2['id'].'" style=" display:block;   width: 98px;     height: 32px;    padding: 3px;  margin-top: 5px;    font-size: 19px;" class="datepicker2 metro-skin" type="text" name="date_app" value="'.$row2['app_date'].'"  />';
      } else {

      if ($row2['status_admin'] != 'Подтвержден') {
      $linkz .= '<a class="t-5" onclick=\'return confirm("Восстановление удаленной заявки возможно только через администратора сервисной базы. Удалить заявку?")\' title="Удалить карточку" href="/del-repair/'.$row2['id'].'/" ></a>';
      }

      }
      } else {
      if (\models\User::hasRole('admin')) {
      $linkz .= '<a class="t-2" title="Восстановить карточку" href="/comeback-repair/'.$row2['id'].'/" ></a>';
      }
      }


      if (!\models\User::hasRole('admin') && $row2['repair_done'] != 1 && !\models\User::hasRole('taker')) {
      if (check_complete($row2['id'])) {
      $linkz .= '<a class="mod_fast_true" title="Отправить на проверку" style="background:none;    padding-bottom: 6px;" href="/repair-done/'.$row2['id'].'/"><img src="/img/true.png"></a>';
      } else {
      $linkz .= '<a title="Не заполнены все необходимые поля" class="mod_fast_true need_work" title="Отправить на проверку" style="background:none;    padding-bottom: 6px;" href="#" onclick="return false"><img src="/img/true_yellow.png"></a>';
      }
      }

      if (User::getData('id') == 33) {
      $linkz .= '<span style="position:relative;" class="master_change"><a class="add_master" title="Назначить мастера" style="background:none;    padding-bottom: 6px;" href="#"><img style="    height: 17px;   margin-left: 5px;" src="/img/settings.png"></a><select data-id="'.$row2['id'].'" name="master_id">
               <option value="0">Выберите мастера</option>
               '.masters_list($row2['master_user_id']).'
              </select></span>';
      }

      $confirm = ($row2['master_user_id'] != 0) ? 'onclick=\'return confirm("Подтвердите сброс статусов и возврат ремонта в работу.")\'' : ''; //onclick=\'alert("Сначала выберите мастера!");return false\'

      if (User::getData('id') == 33 && $row2['status_admin'] != '') {
      $linkz .= '<a class="t-2" style="margin-left: 6px;" '.$confirm.' title="Вернуть на доработку" href="/re-edit-repair/'.$row2['id'].'/" ></a>';

      }

      if (\models\User::hasRole('taker', 'slave-admin')) {
      if ($draw_color == 'red'  || $double_pls == 1) {
        $linkz .= '<a class="t-4" style="margin-left: 6px;" title="Показать дубли" data-fancybox data-type="iframe" data-src="/show-double/'.$row2['serial'].'/'.$row2['id'].'/" href="javascript:;" ></a>';

      }

      }
      $content_list[] = '<div class="linkz">'.$linkz.'</div>';


      if (\models\User::hasRole('admin')) {
      $content_list[] = '<form method="POST"><select  name="status_admin" data-app-date="'.$row2['app_date'].'" data-repair-id="'.$row2['id'].'"><option value="">Без статуса</option><option value="В обработке" '.(($row2['status_admin'] == 'В обработке') ? 'selected' : '').'>В обработке</option><option value="Есть вопросы" '.(($row2['status_admin'] == 'Есть вопросы') ? 'selected' : '').'>Есть вопросы</option><option value="Подтвержден" '.(($row2['status_admin'] == 'Подтвержден') ? 'selected' : '').'>Подтвержден</option><option value="Отклонен" '.(($row2['status_admin'] == 'Отклонен') ? 'selected' : '').'>Отклонен</option><option value="Оплачен" '.(($row2['status_admin'] == 'Оплачен') ? 'selected' : '').'>Оплачен</option><option value="На проверке" '.(($row2['status_admin'] == 'На проверке') ? 'selected' : '').'>На проверке</option><option value="Нужны запчасти" '.(($row2['status_admin'] == 'Нужны запчасти') ? 'selected' : '').'>Нужны запчасти</option><option value="Запчасти в пути" '.(($row2['status_admin'] == 'Запчасти в пути') ? 'selected' : '').'>Запчасти в пути</option><option value="Запрос на выезд" '.(($row2['status_admin'] == 'Запрос на выезд') ? 'selected' : '').'>Запрос на выезд</option><option value="Выезд подтвержден" '.(($row2['status_admin'] == 'Выезд подтвержден') ? 'selected' : '').'>Выезд подтвержден</option><option value="Выезд отклонен" '.(($row2['status_admin'] == 'Выезд отклонен') ? 'selected' : '').'>Выезд отклонен</option><option value="Принят" '.(($row2['status_admin'] == 'Принят') ? 'selected' : '').'>Принят</option><option value="Выдан" '.(($row2['status_admin'] == 'Выдан') ? 'selected' : '').'>Выдан</option><option value="Утилизирован" '.(($row2['status_admin'] == 'Утилизирован') ? 'selected' : '').'>Утилизирован</option><option value="В работе" '.(($row2['status_admin'] == 'В работе') ? 'selected' : '').'>В работе</option></select></form>';
      } if (\models\User::hasRole('slave-admin', 'taker')) {
      $content_list[] = '<form method="POST"><select  name="status_admin" data-app-date="'.$row2['app_date'].'" data-repair-id="'.$row2['id'].'"><option value="">Без статуса</option> <option value="В обработке" '.(($row2['status_admin'] == 'В обработке') ? 'selected' : '').'>В обработке</option> <option value="В работе" '.(($row2['status_admin'] == 'В работе') ? 'selected' : '').'>В работе</option> <option value="Выдан" '.(($row2['status_admin'] == 'Выдан') ? 'selected' : '').'>Выдан</option> <option value="Выезд отклонен" '.(($row2['status_admin'] == 'Выезд отклонен') ? 'selected' : '').'>Выезд отклонен</option> <option value="Выезд подтвержден" '.(($row2['status_admin'] == 'Выезд подтвержден') ? 'selected' : '').'>Выезд подтвержден</option> <option value="Есть вопросы" '.(($row2['status_admin'] == 'Есть вопросы') ? 'selected' : '').'>Есть вопросы</option> <option value="Запрос на выезд" '.(($row2['status_admin'] == 'Запрос на выезд') ? 'selected' : '').'>Запрос на выезд</option> <option value="Запчасти в пути" '.(($row2['status_admin'] == 'Запчасти в пути') ? 'selected' : '').'>Запчасти в пути</option> <option value="На проверке" '.(($row2['status_admin'] == 'На проверке') ? 'selected' : '').'>На проверке</option> <option value="Нужны запчасти" '.(($row2['status_admin'] == 'Нужны запчасти') ? 'selected' : '').'>Нужны запчасти</option> <option value="Оплачен" '.(($row2['status_admin'] == 'Оплачен') ? 'selected' : '').'>Оплачен</option> <option value="Отклонен" '.(($row2['status_admin'] == 'Отклонен') ? 'selected' : '').'>Отклонен</option> <option value="Подтвержден" '.(($row2['status_admin'] == 'Подтвержден') ? 'selected' : '').'>Подтвержден</option> <option value="Принят" '.(($row2['status_admin'] == 'Принят') ? 'selected' : '').'>Принят</option> <option value="Утилизирован" '.(($row2['status_admin'] == 'Утилизирован') ? 'selected' : '').'>Утилизирован</option> </select></form>';
      } else {
      $app_date = ($row2['app_date'] && $row2['status_admin'] == 'Подтвержден') ? '<br>('.$row2['app_date'].')' : '';
      $content_list[] = $row2['status_admin'].$app_date;
      }

      if (\models\User::hasRole('master', 'slave-admin', 'taker')) {
      $content_list[] = $return['date_farewell'];
      }

      $rows[] = $content_list;
      unset($content_list);
      unset($double_pls);
      unset($linkz);
      unset($date_ready);
      unset($date_new);
      unset($date_ready_created);
      unset($master);
      unset($return);
      unset($clientz);
      unset($clientz2);
      unset($draw_color);

      }

      }

        }

      }
      } else {
      $content_list = '<tr><td colspan="9">'.$row['id'].'</td>';
      }
if (count($rows) == 0) {
$data = [];
} else {
$data = $rows;
}
$results = ["sEcho" => $_GET['sEcho'],
        	"iTotalRecords" => $sql_total['COUNT(*)'],
        	"iTotalDisplayRecords" => $sql_total['COUNT(*)'],
        	"aaData" => $data ];

$time_end = microtime(true);

//dividing with 60 will give the execution time in minutes otherwise seconds
$execution_time = ($time_end - $time_start)/60;


    return json_encode($results);


}

if (isset($_GET['master_id'])) {
setcookie("master_id", $_GET['master_id'], time()+3600*60*31, "/dashboard/");
}
if (isset($_GET['search_model_id'])) {
//setcookie("model_id", $_GET['search_model_id'], time()+3600*60*31, "/dashboard/");
}
if (isset($_GET['search_client_id'])) {
//setcookie("client_id", $_GET['search_client_id'], time()+3600*60*31, "/dashboard/");
}

if ($_GET['date1'] && $_GET['date2']) {
setcookie("target", "", time()+3600*60*31, "/re-repaired/");
setcookie("date1", $_GET['date1'], time()+3600*60*31, "/re-repaired/");
setcookie("date12", "", time()+3600*60*31, "/re-repaired/");
setcookie("date13", "", time()+3600*60*31, "/re-repaired/");
setcookie("date14", "", time()+3600*60*31, "/re-repaired/");
setcookie("date2", $_GET['date2'], time()+3600*60*31, "/re-repaired/");
setcookie("date22", "", time()+3600*60*31, "/re-repaired/");
setcookie("date23", "", time()+3600*60*31, "/re-repaired/");
setcookie("date24", "", time()+3600*60*31, "/re-repaired/");
setcookie("date25", "", time()+3600*60*31, "/re-repaired/");
setcookie("impo", "", time()+3600*60*31, "/re-repaired/");
setcookie("type_id", "", time()+3600*60*31, "/re-repaired/");
$_COOKIE['target'] = '';
$_COOKIE['date1'] = $_GET['date1'];
$_COOKIE['date12'] = '';
$_COOKIE['date13'] = '';
$_COOKIE['date14'] = '';
$_COOKIE['date2'] = $_GET['date2'];
$_COOKIE['date22'] = '';
$_COOKIE['date23'] = '';
$_COOKIE['date24'] = '';
$_COOKIE['date25'] = '';
$_COOKIE['impo'] = '';
$_COOKIE['type_id'] = '';
} else if ($_GET['date12'] && $_GET['date22']) {
setcookie("target", "", time()+3600*60*31, "/re-repaired/");
setcookie("date1", "", time()+3600*60*31, "/re-repaired/");
setcookie("date12", $_GET['date12'], time()+3600*60*31, "/re-repaired/");
setcookie("date13", "", time()+3600*60*31, "/re-repaired/");
setcookie("date14", "", time()+3600*60*31, "/re-repaired/");
setcookie("date2", "", time()+3600*60*31, "/re-repaired/");
setcookie("date22", $_GET['date22'], time()+3600*60*31, "/re-repaired/");
setcookie("date23", "", time()+3600*60*31, "/re-repaired/");
setcookie("date24", "", time()+3600*60*31, "/re-repaired/");
setcookie("date25", "", time()+3600*60*31, "/re-repaired/");
setcookie("impo", "", time()+3600*60*31, "/re-repaired/");
setcookie("type_id", "", time()+3600*60*31, "/re-repaired/");
$_COOKIE['target'] = '';
$_COOKIE['date1'] = '';
$_COOKIE['date12'] = $_GET['date12'];
$_COOKIE['date13'] = '';
$_COOKIE['date14'] = '';
$_COOKIE['date2'] = '';
$_COOKIE['date22'] = $_GET['date22'];
$_COOKIE['date23'] = '';
$_COOKIE['date24'] = '';
$_COOKIE['date25'] = '';
$_COOKIE['impo'] = '';
$_COOKIE['type_id'] = '';
} else if ($_GET['date13'] && $_GET['date23']) {
setcookie("target", "", time()+3600*60*31, "/re-repaired/");
setcookie("date1", "", time()+3600*60*31, "/re-repaired/");
setcookie("date12", "", time()+3600*60*31, "/re-repaired/");
setcookie("date13", $_GET['date13'], time()+3600*60*31, "/re-repaired/");
setcookie("date14", "", time()+3600*60*31, "/re-repaired/");
setcookie("date2", "", time()+3600*60*31, "/re-repaired/");
setcookie("date22", "", time()+3600*60*31, "/re-repaired/");
setcookie("date23", $_GET['date23'], time()+3600*60*31, "/re-repaired/");
setcookie("date24", "", time()+3600*60*31, "/re-repaired/");
setcookie("date25", "", time()+3600*60*31, "/re-repaired/");
setcookie("impo", "", time()+3600*60*31, "/re-repaired/");
setcookie("type_id", "", time()+3600*60*31, "/re-repaired/");
$_COOKIE['target'] = '';
$_COOKIE['date1'] = '';
$_COOKIE['date12'] = '';
$_COOKIE['date13'] = $_GET['date13'];
$_COOKIE['date14'] = '';
$_COOKIE['date2'] = '';
$_COOKIE['date22'] = '';
$_COOKIE['date23'] = $_GET['date23'];
$_COOKIE['date24'] = '';
$_COOKIE['date25'] = '';
$_COOKIE['impo'] = '';
$_COOKIE['type_id'] = '';
} else if ($_GET['date14'] && $_GET['date24']) {
setcookie("target", "", time()+3600*60*31, "/re-repaired/");
setcookie("date1", "", time()+3600*60*31, "/re-repaired/");
setcookie("date12", "", time()+3600*60*31, "/re-repaired/");
setcookie("date13", "", time()+3600*60*31, "/re-repaired/");
setcookie("date14", $_GET['date14'], time()+3600*60*31, "/re-repaired/");
setcookie("date2", "", time()+3600*60*31, "/re-repaired/");
setcookie("date22", "", time()+3600*60*31, "/re-repaired/");
setcookie("date23", "", time()+3600*60*31, "/re-repaired/");
setcookie("date24", $_GET['date24'], time()+3600*60*31, "/re-repaired/");
setcookie("date25", "", time()+3600*60*31, "/re-repaired/");
setcookie("impo", "", time()+3600*60*31, "/re-repaired/");
setcookie("type_id", "", time()+3600*60*31, "/re-repaired/");
$_COOKIE['target'] = '';
$_COOKIE['date1'] = '';
$_COOKIE['date12'] = '';
$_COOKIE['date13'] = '';
$_COOKIE['date14'] = $_GET['date14'];
$_COOKIE['date2'] = '';
$_COOKIE['date22'] = '';
$_COOKIE['date23'] = '';
$_COOKIE['date24'] = $_GET['date24'];
$_COOKIE['date25'] = '';
$_COOKIE['impo'] = '';
$_COOKIE['type_id'] = '';
} else if ($_GET['date15'] && $_GET['date25']) {
setcookie("target", "", time()+3600*60*31, "/re-repaired/");
setcookie("date1", "", time()+3600*60*31, "/re-repaired/");
setcookie("date12", "", time()+3600*60*31, "/re-repaired/");
setcookie("date13", "", time()+3600*60*31, "/re-repaired/");
setcookie("date14", $_GET['date14'], time()+3600*60*31, "/re-repaired/");
setcookie("date2", "", time()+3600*60*31, "/re-repaired/");
setcookie("date22", "", time()+3600*60*31, "/re-repaired/");
setcookie("date23", "", time()+3600*60*31, "/re-repaired/");
setcookie("date24", "", time()+3600*60*31, "/re-repaired/");
setcookie("date25", $_GET['date25'], time()+3600*60*31, "/re-repaired/");
setcookie("impo", "", time()+3600*60*31, "/re-repaired/");
setcookie("type_id", "", time()+3600*60*31, "/re-repaired/");
$_COOKIE['target'] = '';
$_COOKIE['date1'] = '';
$_COOKIE['date12'] = '';
$_COOKIE['date13'] = '';
$_COOKIE['date14'] = $_GET['date14'];
$_COOKIE['date2'] = '';
$_COOKIE['date22'] = '';
$_COOKIE['date23'] = '';
$_COOKIE['date24'] = '';
$_COOKIE['date25'] = $_GET['date25'];
$_COOKIE['impo'] = '';
$_COOKIE['type_id'] = '';
} else if ($_GET['impo']) {
setcookie("target", "", time()+3600*60*31, "/re-repaired/");
setcookie("date1", "", time()+3600*60*31, "/re-repaired/");
setcookie("date12", "", time()+3600*60*31, "/re-repaired/");
setcookie("date13", "", time()+3600*60*31, "/re-repaired/");
setcookie("date14", "", time()+3600*60*31, "/re-repaired/");
setcookie("date2", "", time()+3600*60*31, "/re-repaired/");
setcookie("date22", "", time()+3600*60*31, "/re-repaired/");
setcookie("date23", "", time()+3600*60*31, "/re-repaired/");
setcookie("date24", "", time()+3600*60*31, "/re-repaired/");
setcookie("date25", "", time()+3600*60*31, "/re-repaired/");
setcookie("impo", $_GET['impo'], time()+3600*60*31, "/re-repaired/");
setcookie("type_id", "", time()+3600*60*31, "/re-repaired/");
$_COOKIE['target'] = '';
$_COOKIE['date1'] = '';
$_COOKIE['date12'] = '';
$_COOKIE['date13'] = '';
$_COOKIE['date14'] = '';
$_COOKIE['date2'] = '';
$_COOKIE['date22'] = '';
$_COOKIE['date23'] = '';
$_COOKIE['date24'] = '';
$_COOKIE['date24'] = '';
$_COOKIE['impo'] = $_GET['impo'];
$_COOKIE['type_id'] = '';
} else if ($_GET['target']) {
setcookie("target", $_GET['target'], time()+3600*60*31, "/re-repaired/");
setcookie("date1", "", time()+3600*60*31, "/re-repaired/");
setcookie("date12", "", time()+3600*60*31, "/re-repaired/");
setcookie("date13", "", time()+3600*60*31, "/re-repaired/");
setcookie("date14", "", time()+3600*60*31, "/re-repaired/");
setcookie("date2", "", time()+3600*60*31, "/re-repaired/");
setcookie("date22", "", time()+3600*60*31, "/re-repaired/");
setcookie("date23", "", time()+3600*60*31, "/re-repaired/");
setcookie("date24", "", time()+3600*60*31, "/re-repaired/");
setcookie("date25", "", time()+3600*60*31, "/re-repaired/");
setcookie("impo", "", time()+3600*60*31, "/re-repaired/");
setcookie("type_id", "", time()+3600*60*31, "/re-repaired/");
$_COOKIE['target'] = $_GET['target'];
$_COOKIE['date1'] = '';
$_COOKIE['date12'] = '';
$_COOKIE['date13'] = '';
$_COOKIE['date14'] = '';
$_COOKIE['date2'] = '';
$_COOKIE['date22'] = '';
$_COOKIE['date23'] = '';
$_COOKIE['date24'] = '';
$_COOKIE['date25'] = '';
$_COOKIE['impo'] = '';
$_COOKIE['type_id'] = '';
} else if ($_GET['type_id']) {
setcookie("target", "", time()+3600*60*31, "/re-repaired/");
setcookie("date1", "", time()+3600*60*31, "/re-repaired/");
setcookie("date12", "", time()+3600*60*31, "/re-repaired/");
setcookie("date13", "", time()+3600*60*31, "/re-repaired/");
setcookie("date14", "", time()+3600*60*31, "/re-repaired/");
setcookie("date2", "", time()+3600*60*31, "/re-repaired/");
setcookie("date22", "", time()+3600*60*31, "/re-repaired/");
setcookie("date23", "", time()+3600*60*31, "/re-repaired/");
setcookie("date24", "", time()+3600*60*31, "/re-repaired/");
setcookie("date25", "", time()+3600*60*31, "/re-repaired/");
setcookie("impo", "", time()+3600*60*31, "/re-repaired/");
setcookie("type_id", $_GET['type_id'], time()+3600*60*31, "/re-repaired/");
$_COOKIE['target'] = '';
$_COOKIE['date1'] = '';
$_COOKIE['date12'] = '';
$_COOKIE['date13'] = '';
$_COOKIE['date14'] = '';
$_COOKIE['date2'] = '';
$_COOKIE['date22'] = '';
$_COOKIE['date23'] = '';
$_COOKIE['date24'] = '';
$_COOKIE['date24'] = '';
$_COOKIE['impo'] = '';
$_COOKIE['type_id'] = $_GET['type_id'];
} else if ($_GET['status_check']) {
setcookie("target", "", time()+3600*60*31, "/re-repaired/");
setcookie("date1", "", time()+3600*60*31, "/re-repaired/");
setcookie("date12", "", time()+3600*60*31, "/re-repaired/");
setcookie("date13", "", time()+3600*60*31, "/re-repaired/");
setcookie("date14", "", time()+3600*60*31, "/re-repaired/");
setcookie("date2", "", time()+3600*60*31, "/re-repaired/");
setcookie("date22", "", time()+3600*60*31, "/re-repaired/");
setcookie("date23", "", time()+3600*60*31, "/re-repaired/");
setcookie("date24", "", time()+3600*60*31, "/re-repaired/");
setcookie("date25", "", time()+3600*60*31, "/re-repaired/");
setcookie("impo", "", time()+3600*60*31, "/re-repaired/");
setcookie("type_id", $_GET['type_id'], time()+3600*60*31, "/re-repaired/");
setcookie("status_check", $_GET['status_check'], time()+3600*60*31, "/re-repaired/");
$_COOKIE['target'] = '';
$_COOKIE['date1'] = '';
$_COOKIE['date12'] = '';
$_COOKIE['date13'] = '';
$_COOKIE['date14'] = '';
$_COOKIE['date2'] = '';
$_COOKIE['date22'] = '';
$_COOKIE['date23'] = '';
$_COOKIE['date24'] = '';
$_COOKIE['date24'] = '';
$_COOKIE['impo'] = '';
$_COOKIE['type_id'] = '';
} else {

if ($_GET['ajaxed'] != 1) {
redir();
}

}

function redir() {
  return;
if ($_GET['get'] == 'deleted' || $_GET['master_id'] || $_GET['search_model_id']  || $_GET['search_client_id']) {
//header('Location: http://service.harper.ru/dashboard/?target='.$_COOKIE['target']);
//exit;
} else if ($_COOKIE['date1'] && $_COOKIE['date2'] && $_COOKIE['date1'] != $_GET['date1'] && $_COOKIE['date2'] != $_GET['date2']) {
header('Location: https://crm.r97.ru/dashboard/?date1='.$_COOKIE['date1'].'&date2='.$_COOKIE['date2']);
exit;
} else if ($_COOKIE['date12'] && $_COOKIE['date22'] && $_COOKIE['date12'] != $_GET['date12'] && $_COOKIE['date22'] != $_GET['date22']) {
header('Location: https://crm.r97.ru/dashboard/?date12='.$_COOKIE['date12'].'&date22='.$_COOKIE['date22']);
exit;
} else if ($_COOKIE['date13'] && $_COOKIE['date23'] && $_COOKIE['date13'] != $_GET['date13'] && $_COOKIE['date23'] != $_GET['date23']) {
header('Location: https://crm.r97.ru/dashboard/?date13='.$_COOKIE['date13'].'&date23='.$_COOKIE['date23']);
exit;
} else if ($_COOKIE['date14'] && $_COOKIE['date24'] && $_COOKIE['date14'] != $_GET['date14'] && $_COOKIE['date24'] != $_GET['date24']) {
header('Location: https://crm.r97.ru/dashboard/?date14='.$_COOKIE['date14'].'&date24='.$_COOKIE['date24']);
exit;
} else if ($_COOKIE['impo'] && $_COOKIE['impo'] != $_GET['impo']) {
header('Location: https://crm.r97.ru/dashboard/?impo='.$_COOKIE['impo']);
exit;
} else if ($_COOKIE['target'] && $_COOKIE['target'] != $_GET['target']) {
header('Location: https://crm.r97.ru/dashboard/?target='.$_COOKIE['target']);
exit;
} else if ($_COOKIE['type_id'] && $_COOKIE['type_id'] != $_GET['type_id']) {
header('Location: https://crm.r97.ru/dashboard/?type_id='.$_COOKIE['type_id']);
exit;
}
}

if ($_GET['date1'] || $_GET['date12'] || $_GET['date13'] || $_GET['date14']) {
$display = 'display:block;';
} else {
$display = 'display:none;';
}


if ($_GET['ajaxed'] == 1) {
echo content_list();
exit;
} else {

if (isset($_COOKIE['master_id'])) {
    unset($_COOKIE['master_id']);
    setcookie('master_id', '', time() - 3600, '/'); // empty value and old timestamp
}
/*if (isset($_COOKIE['model_id'])) {
    unset($_COOKIE['model_id']);
    setcookie('model_id', '', time() - 3600, '/'); // empty value and old timestamp
}
if (isset($_COOKIE['client_id'])) {
    unset($_COOKIE['client_id']);
    setcookie('client_id', '', time() - 3600, '/'); // empty value and old timestamp
}  */

}


function check_serial($serial, $id) {
  return models\Serials::isValid($serial, $id);
}

function model($id) {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `models` where `id` = '.$id);
      while ($row = mysqli_fetch_array($sql)) {
      $content = $row;

      }
    return $content;
}

function problems_array() {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `details_problem`;');
      while ($row = mysqli_fetch_array($sql)) {

       $content[$row['id']] = $row['name'];

      }
    return $content;
}

function problems_array_id() {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `details_problem`;');
      while ($row = mysqli_fetch_array($sql)) {

       if (preg_match('/ТЕСТИРОВАНИЕ/u', $row['name'])) {
        $test[] = $row['id'];
       } else if (preg_match('/РЕМОНТА/u', $row['name'])) {
        $repaira[] = $row['id'];
       } else if (preg_match('/РЕМОНТ/u', $row['name'])) {
        $repair[] = $row['id'];
       }

      }

$content['test'] = implode(',', $test);
$content['repaira'] = implode(',', $repaira);
$content['repair'] = implode(',', $repair);

    return $content;
}

function problems_array_id_var() {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `details_problem`;');
      while ($row = mysqli_fetch_array($sql)) {

       if (preg_match('/ТЕСТИРОВАНИЕ/u', $row['name'])) {
        $test[] = $row['id'];
       } else if (preg_match('/РЕМОНТА/u', $row['name'])) {
        $repaira[] = $row['id'];
       } else if (preg_match('/РЕМОНТ/u', $row['name'])) {
        $repair[] = $row['id'];
       }

      }

$content['test'] = $test;
$content['repaira'] = $repaira;
$content['repair'] = $repair;

    return $content;
}

function check_serial_imported($serial, $model) {
  return models\Serials::isValid($serial, $model);
}

function models($cat_id) {
  global $db;
$content = array();
$sql = mysqli_query($db, 'SELECT * FROM `models`;');
      while ($row = mysqli_fetch_array($sql)) {
      if ($cat_id == $row['id'] || $_COOKIE['model_id'] == $row['id']) {
      $content .= '<option selected value="'.$row['id'].'">'.$row['name'].'</option>';
      } else {
       $content .= '<option value="'.$row['id'].'">'.$row['name'].'</option>';
      }
      }
    return $content;
}

function clients($cat_id) {
  global $db;
$content = array();
$sql = mysqli_query($db, 'SELECT * FROM `clients` group by `name` order by `name` asc;');
      while ($row = mysqli_fetch_array($sql)) {
      if ($cat_id == $row['id']  || $_COOKIE['client_id'] == $row['id']) {
      $content .= '<option selected value="'.$row['id'].'">'.$row['name'].'</option>';
      } else {
       $content .= '<option value="'.$row['id'].'">'.$row['name'].'</option>';
      }
      }
    return $content;
}


function master_select() {
  $content = '';
$masters = Staff::getMasters();
      foreach ($masters as $row) {
      if ($_GET['master_id'] == $row['id'] || $_COOKIE['master_id'] == $row['id']) {
      $content .= '<option selected value="'.$row['id'].'">'.$row['surname'].' '.$row['name'].' '.$row['thirdname'].'</option>';
      } else {
       $content .= '<option value="'.$row['id'].'">'.$row['surname'].' '.$row['name'].' '.$row['thirdname'].'</option>';
      }
      }
    return $content;
}

function masters_list($cat_id) {
  $content = '';

$masters = Staff::getMasters();
      foreach ($masters as $row) {
      if ($cat_id == $row['id']) {
      $content .= '<option selected value="'.$row['id'].'" title="'.$row['surname'].' '.$row['name'].' '.$row['third_name'].'">'.$row['surname'].' '.$row['name'].' '.$row['third_name'].'</option>';
      } else {
       $content .= '<option value="'.$row['id'].'">'.$row['surname'].' '.$row['name'].' '.$row['third_dname'].'</option>';
      }
      }
    return $content;
}

function check_doubl_ffs($serial, $id = '') {
  global $config, $db;
  if(empty($serial)){
    return false;
  }
$sql = mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` WHERE `serial` = \''.mysqli_real_escape_string($db, $serial).'\' and `model_id` = '.$id.' AND `deleted` = 0;');
$count = mysqli_fetch_array($sql)['COUNT(*)'];
if ($count > 1) {

return true;

} else {
return false;
}
}

function check_status() {
  global $db;
$sql = mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` where `deleted` != 1 and `status_admin` != \'Подтвержден\' and `status_admin` != \'\'  and `status_admin_read` = 1  order by `id` DESC;');
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

function check_complete($id) {
    global $db;
$req = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` WHERE
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
<title>Повторные ремонты - Панель управления</title>
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
<script src="/_new-codebase/front/vendor/js.cookie-2.2.0.min.js"></script>
<script src="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.concat.min.js"></script>
<link rel="stylesheet" href="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.min.css" />
<link rel="stylesheet" href="/_new-codebase/front/vendor/tooltipster/tooltipster.bundle.min.css" />
<link rel="stylesheet" href="/_new-codebase/front/vendor/tooltipster/tooltipster-sideTip-shadow.min.css" />
<link rel="stylesheet" href="/_new-codebase/front/vendor/animate.min.css" />
<script  src="/_new-codebase/front/vendor/datatables/1.10.19/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="/css/datatables.css">
<link rel="stylesheet" href="/_new-codebase/front/vendor/select2/4.0.4/select2.min.css" />
<script src="/_new-codebase/front/vendor/select2/4.0.4/select2.full.min.js"></script>
<script  src="/_new-codebase/front/vendor/datatables/2.1.1/dataTables.responsive.min.js"></script>
<link rel="stylesheet" type="text/css" href="/_new-codebase/front/vendor/datatables/2.1.1/responsive.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="/notifier/css/style.css">
<link rel="stylesheet" type="text/css" href="/js/daterangepicker.css">
<script src="/js/moment.min.js"></script>
<script src="/js/jquery.daterangepicker.js"></script>
<link rel="stylesheet" href="/_new-codebase/front/vendor/fancybox/3.5.2/jquery.fancybox.min.css" />
<script src="/_new-codebase/front/vendor/fancybox/3.5.2/jquery.fancybox.min.js"></script>
<script src="/_new-codebase/front/vendor/chart-js/2.7.1/chart.bundle.js"></script>
<style>
.ui-selectmenu-button:after {
    display: none;
}
.ui-selectmenu-button {
    width: 160px;
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
.master_change .ui-selectmenu-button {
display:none;
position:absolute;
z-index: 999;
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
.dataTables_wrapper .dataTables_processing {
position: absolute;
top: 30%;
left: 50%;
width: 30%;
height: 40px;
color:#fff;
margin-left: -15%;
margin-top: -25px;
padding-top: 20px;
text-align: center;
font-size: 1.2em;
background:#77ad07;
z-index:999999999999;
}
.green {
background: rgb(178, 255, 102) !important;
}
.grey {
background: rgba(184, 183, 184, 0.85) !important;
}
.darkgreen {
background: rgb(178, 255, 102)  !important;
}
.yellow {
        background: rgba(242, 242, 68, 0.45) !important;
}
.red {
        background: rgba(245, 87, 81, 0.45) !important;
}
.blue {
        background: rgba(107, 218, 255, 0.45) !important;
}
.orange {
background: rgba(255, 153, 51, 0.4) !important;
}
.redone {
background: rgba(153, 51, 51, 0.4)!important;
}
</style>
<script >
// Таблица
$(document).ready(function() {

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



var checked = false;
$('#select_all').click(function() {
    if (checked) {
        $(':checkbox').each(function() {
            $(this).prop('checked', false).trigger('refresh');
        });
        checked = false;
    } else {
        $(':checkbox').each(function() {
            $(this).prop('checked', true).trigger('refresh');
        });
        checked = true;
    }
    return false;
});

 $('.need_work').tooltipster({
                              trigger: 'hover',
                              position: 'top',
                              animation: 'grow',
                              theme: 'tooltipster-shadow'
                          });

    var groupColumn = 1;

   $.fn.dataTable.moment( 'dd.mm.YYYY' );
    var table = $('#table_content').DataTable({
      "bStateSave":false,
      "responsive": true,
      "dom": '<"top"flp<"clear">>rt<"bottom"ip<"clear">>',
      "bProcessing": true,
      "bServerSide": true,
      "deferRender": true,
      lengthMenu: [10, 50, 100, 200, 500, 1000, 2000 ],
      "sAjaxSource": "<?=(strpos($_SERVER['REQUEST_URI'], '?') ? $_SERVER['REQUEST_URI'].'&ajaxed=1' : $_SERVER['REQUEST_URI'].'?ajaxed=1');?>",
      "fnServerParams": function ( aoData ) {
            aoData.push( { "name": "search_client_id", "value": $('select[name="search_client_id"]').val() } );
            aoData.push( { "name": "search_model_id", "value": $('select[name="search_model_id"]').val() } );
        },
      "pageLength": 30,
       <?php if ($_GET['target'] == 'ready') { ?>
           "order": [[ 6, 'desc' ]],
       <?php } else if ($_GET['target'] == 'accepted') {  ?>
          "order": [[ 4, 'desc' ]],
       <?php } else if ($_GET['target'] == 'inprogress') {  ?>
          "order": [[ 4, 'desc' ]],
       <?php } else  if ($_GET['target'] == 'out') { ?>

           <?php if (\models\User::hasRole('master', 'slave-admin', 'taker')) {  ?>
             "order": [[ 21, 'desc' ]],
             <?php } else {?>
             "order": [[ 5, 'desc' ]],
            <?php } ?>

        <?php } else if ($_GET['target'] == 'trash') { ?>
                "order": [[ 6, 'desc' ]],
         <?php } else if ($_GET['target'] == 'resell') { ?>
            "order": [[ 6, 'desc' ]],
       <?php } else {?>
        "order": [[ 4, 'asc' ]],
       <?php } ?>

             "aoColumns": [
            null,
            null,
            null,
           null,
       null,
           null,
           null,
          null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
             null,
             null,
             null,
             null,
             null
             <?php if (\models\User::hasRole('master', 'slave-admin', 'taker')) {  ?>
             , null
             <?php } ?>
        ],
      "columnDefs": [
            { "visible": false, "targets": groupColumn },
            {   "targets": [ 21 ],
                "visible": false
            }
        ],
        /*"order": [[ groupColumn, 'asc' ]],  */
        "drawCallback": function ( settings ) {
            var api = this.api();
            var rows = api.rows( {page:'current'} ).nodes();
            var last=null;

            api.column(groupColumn, {page:'current'} ).data().each( function ( group, i ) {
                if ( last !== group ) {
                    $(rows).eq( i ).before(
                        '<tr class="group" id="removepls"><td colspan="21">'+group+'</td></tr>'
                    );

                    last = group;
                }
            } );

$( ".datepicker2" ).datepicker({
  dateFormat: 'yy.mm.dd',
    onSelect: function(dateText, inst) {
        var date = $(this).val();
        var id = $(this).data('id');


                  $.get( "/ajax.php?type=update_repair_appdate&value="+date+"&id="+id, function( data ) {

                  });



    } ,
     beforeShow: function(input, inst) {
       $('#ui-datepicker-div').addClass("ll-skin-cangas");
   }
});


     $(document).on('click', '.add_master', function() {

         $(this).parent().find('.ui-selectmenu-button').show();


        return false;
    });


     $(document).on('selectmenuchange selectmenuselect', 'select[name=master_id]', function() {
        var value = $(this).val();
        var this_select = $(this).parent().find('.ui-selectmenu-button');
        var id= $(this).data('id');
              if (value) {



                 $.get( "/ajax.php?type=add_master&value="+value+"&id="+id, function( data ) {

                  this_select.hide();

                  });

              }


        return false;
    });


$('select[name="search_model_id"]').change(function (e) {
        table.draw();
});
$('select[name="search_client_id"]').change(function (e) {
        table.draw();
});

     $(document).on('selectmenuclose', 'select[name=master_id]', function() {
        var value = $(this).val();
        var this_select = $(this).parent().find('.ui-selectmenu-button');


                  this_select.hide();


        return false;
    });

  $('#table_content select:not(.nomenu)').selectmenu({
    open: function(){
      $(this).selectmenu('menuWidget').css('width', $(this).selectmenu('widget').outerWidth());
    },
        change: function( event, data ) {
        var selValue = $(this).val();
       if ($(".validate_form").length) {
        $(".validate_form").validate().element(this);
        if (selValue.length > 0) {
            $(this).next('div').removeClass("input-validation-error");
        } else {
            $(this).next('div').addClass("input-validation-error");
        }
        }

      }
  }).addClass("selected_menu");

        },

      "oLanguage": {
            "sLengthMenu": "Показывать _MENU_ записей на страницу",
            "sZeroRecords": "Записей нет.",
            "sInfo": "Показано от _START_ до _END_ из _TOTAL_ записей",
            "sInfoEmpty": "Записей нет.",
            "sProcessing": "Загружаются данные...",
            "oPaginate": {
                 "sFirst": "Первая",
                 "sLast": "Последняя",
                 "sNext": "Следующая",
                 "sPrevious": "Предыдущая",
                },
            "sSearch": "Поиск",
            "sInfoFiltered": "(отфильтровано из _MAX_ записи/(ей)"
        }});

    $(document).on('selectmenuchange', '#table_content select[name=status_admin]', function() {
        var value = $(this).val();
        var id= $(this).data('repair-id');
        var app_date= $(this).data('app-date');
        if (app_date) {
        var date_app_date = $.datepicker.parseDate('yy.mm.dd', app_date);
        }
        var now = new Date();
              if (value) {

                 if (app_date) {

                if (date_app_date.getMonth() == now.getMonth()) {
                $.get( "/ajax.php?type=update_repair_status&value="+encodeURIComponent(value)+"&id="+id, function( data ) {

                  });
                }  else {

                var isGood=confirm("ВЫ УВЕРЕНЫ, ЧТО ХОТИТЕ ИЗМЕНИТЬ СТАТУС ДАННОГО РЕМОНТА, КОТОРЫЙ НАХОДИТСЯ В ПРОШЛОМ ПЛАТЁЖНОМ ПЕРИОДЕ?");
                if (isGood) {
                  $.get( "/ajax.php?type=update_repair_status&value="+encodeURIComponent(value)+"&id="+id, function( data ) {

                  });
                } else {
                  //alert('false');
                }



                }

                 } else {

                    $.get( "/ajax.php?type=update_repair_status&value="+encodeURIComponent(value)+"&id="+id, function( data ) {

                    });

                 }


              }


        return false;
    });

$('.select2').select2({ width: 'resolve' });

$( ".datepicker2" ).datepicker({
  dateFormat: 'yy.mm.dd',
    onSelect: function(dateText, inst) {
        var date = $(this).val();
        var id = $(this).data('id');


                  $.get( "/ajax.php?type=update_repair_appdate&value="+date+"&id="+id, function( data ) {

                  });



    } ,
     beforeShow: function(input, inst) {
       $('#ui-datepicker-div').addClass("ll-skin-cangas");
   }
});



    $(document).on('click', '.dates_filter', function() {
        $('.dates_block').slideToggle( "slow" );
        return false;
    });

     $(document).on('click', '.master_filter', function() {
        $('.master_block').slideToggle( "slow" );
        return false;
    });

    $(document).on('click', '.gen_nak', function() {
    var ids = [];
    $('.download_mass:checked').each(function(){
          ids.push($(this).data('id'));
          //$('[data-remodal-id=modal]').remodal().close();
          //$('[data-remodal-id=modal2]').remodal().open();
    });

          //console.log(ids);
         // $.get('/?query=create_super_nak&value='+JSON.stringify(ids));
          $(location).attr('href','/?query=create_super_nak&value='+JSON.stringify(ids));
          return false;
    });

    $(document).on('click', '.gen_kvit', function() {
    var ids = [];
    $('.download_mass:checked').each(function(){
          ids.push($(this).data('id'));
          //$('[data-remodal-id=modal]').remodal().close();
          //$('[data-remodal-id=modal2]').remodal().open();
    });

          //console.log(ids);
         // $.get('/?query=create_super_nak&value='+JSON.stringify(ids));
          $(location).attr('href','/?query=create_super_kvit&value='+JSON.stringify(ids));
          return false;
    });

    $(document).on('click', '.sub_change', function(e) {
     e.preventDefault();
    var ids = [];
    var form = $('.sub_change_form');
    var master_id = form.find('select[name="master_id"]').val();
    var status_admin = form.find('select[name="status_admin"]').val();
    $('.download_mass:checked').each(function(){
          ids.push($(this).data('id'));
          //ids.push($(this).data('id'));

          //$('[data-remodal-id=modal]').remodal().close();
          //$('[data-remodal-id=modal2]').remodal().open();
    });

          console.log(ids);
         // $.get('/?query=create_super_nak&value='+JSON.stringify(ids));
          //$(location).attr('href','/?query=create_super_nak&value='+JSON.stringify(ids));

          $.get( "/ajax.php?type=mass_update&status_admin="+encodeURIComponent(status_admin)+"&master_id="+master_id+"&value="+JSON.stringify(ids), function( data ) {
           $(location).attr('href','/dashboard/');
          });

          return false;
    });


} );

$.datepicker.setDefaults( $.datepicker.regional[ "ru" ] );

     $(document).on('keypress keyup search input paste cut change', '#table_content_filter input[type=search]', function() {
        var value = $(this).val();
        var id= $(this).data('id');

              Cookies.set('search', value, { expires: 7 });

    });



$(window).on('load', function() {
       if (Cookies.get('search') && Cookies.get('search') != '') {
    //table.column( 18 ).search( Cookies.get('search') );
  //fnFilter
   $('#table_content_filter input[type=search]').val(Cookies.get('search')).trigger('input');
    }
});
</script>
<script src="/_new-codebase/front/vendor/moment.js/moment.min.js"></script>
<script src="/_new-codebase/front/vendor/moment.js/datetime-moment.js"></script>
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
  closeText: "Закрыть",
  prevText: "&#x3C;Пред",
  nextText: "След&#x3E;",
  currentText: "Сегодня",
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
tr.group,
tr.group:hover {
    background: #77ad07;
    background-color: #77ad07 !important;
    color: #fff;
}
#removepls td:before{
display:none;

}
#removepls td{
padding-left:10px;
}
.yellow {
        background: rgba(242, 242, 68, 0.45) !important;
}
</style>
</head>

<body>

<div class="viewport-wrapper">

<div class="site-header">
  <div class="wrapper">

    <div class="logo">
      <a href="/dashboard/"><img src="<?=$config['url'];?>i/logo.png" alt=""/></a>
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

      <?php if ($_SESSION['adminer'] == 1) { ?>
      <a href="/login-like/1/">service2</a> <span style="color:#fff;">-></span> <span style="color:#fff;"><?=\models\User::getData('login');?></span>
      <?php } else {  ?>
      <a href="/logout/">Выйти, <?=\models\User::getData('login');?></a>
      <?php } ?>

    </div>

  </div>
</div><!-- .site-header -->

<div class="wrapper">

<?=top_menu_admin();?>

  <div class="adm-tab">

  <?=menu_dash();?>

  </div><!-- .adm-tab -->
           <br>
           <h2>Повторные ремонты</h2>
                        <br>
  <div class="adm-catalog">
      <div style="vertical-align:middle;    padding-bottom: 15px;   font-size: 16px;position:relative; ">
      <a style="width: auto;padding-left: 7px;padding-right: 7px;" href="/repeat-models/" class="button">Статистика по моделям</a>

      <?php if (\models\User::hasRole('slave-admin', 'taker')) { ?> 

                   <div class="add" style="padding-top: 0px;
    display: inline-block;
    position: absolute;
    top: -15px;
    right: 0px;display:none;">
    <a href="#" id="select_all" style="margin-right:10px;">Выбрать все</a> <a style="width: auto;padding-left: 7px;padding-right: 7px;background:#83b41c;color:#fff;vertical-align: middle;" href="#" class="gen_nak button">Объединить наклейки</a> <a style="width: auto;padding-left: 7px;padding-right: 7px;background:#83b41c;color:#fff;vertical-align: middle;" href="#" class="gen_kvit button">Объединить квитанции</a>
    </div>

<div class="adm-widget" style="margin-top: 0px;">


    <div class="block">

      <script>
        $(document).ready(function(){
          $('.adm-switch-1 .slider').slider({
            animate: false,
            range: false,
            min: 1,
            max: 3,
            value: <?=($_GET['type_id'] == 1 || $_GET['type_id'] == 3) ? $_GET['type_id'] : 2?>,
            step: 1,
            slide: function( event, ui ) {
             // console.log(ui.value);
            window.location.href = "https://crm.r97.ru/dashboard/?type_id="+ui.value;

            }
          });


        });
      </script>
      <style>
      .adm-widget .act .adm-switch {
    display: inline-block;
    vertical-align: middle;
    margin: 0 25px;
    width: 190px !Important;
    }
    .adm-widget .act .txt {
    position: absolute;
    /* left: 422px; */
    display: inline-block;
       bottom: 18px;
    /* margin-bottom: -30px; */
}
.adm-widget .act .txt-l {
    left:1px;
}
.adm-widget .act .txt-c {
    left:108px;
}
.adm-widget .act .txt-r {
    left:158px;
    right:auto !important;
}
.adm-widget .act {

    width: 438px;
}
.select2-container {
  width: 200px;
}
      </style>
      <table style="display:none;    width: 100%;margin-top:20px;">
         <tr>  <td style="vertical-align: top;max-width:240px;">
      <div class="act">
        <div class="level" style="    display: block;    width: 100%;">Тип клиента</div>
        <input id="landing" type="hidden" value="1" name="landing">
        <div class="adm-switch adm-switch-1" style="width: 82px;z-index: 9;">
          <div class="slider">
            <div class="inner"></div>
          </div>
        </div>
        <div class="txt txt-l">Потребитель</div>
        <div class="txt txt-c">Все</div>
        <div class="txt txt-r">Магазин</div>


      </div> </td>
               <td style="z-index:999999;">
                 <table>
                   <tr>
                     <td style="vertical-align: top;"> <form method="GET" style="padding-top:3px;">
    <div style="display:inline-block;"><span style="display: inline-block;">Модель </span>&nbsp;&nbsp;<select style="width: 200px;display: inline-block;" class="select2 nomenu" name="search_model_id"><option value="">Выберите модель</option><?=models();?></select></div>
    </form></td>
                   </tr>
                   <tr>
                     <td style="vertical-align: top;"> <form method="GET" style="padding-top:3px;">
    <div style="display:inline-block;"><span style="display: inline-block;margin-right: 1px;">&nbsp;Клиент </span>&nbsp;&nbsp;<select style="width: 200px;display: inline-block;" class="select2 nomenu" name="search_client_id"><option value="">Выберите клиента</option><?=clients();?></select></div>
    </form></td>
                   </tr>
                 </table>
               </td>
              <td style="vertical-align: top;     max-width: 545px;  ">
             <form method="GET" style="padding-top:3px;">
    <div style="display:inline-block;"><span style="display: inline-block;">Фильтр по мастеру </span>&nbsp;&nbsp;<select class="select2 nomenu" name="master_id"><option value="">Выберите мастера</option><?=master_select();?></select><input class="green_button" type="submit" style="      background: #80bd03;display: inline-block;     margin-left: 15px;     vertical-align: middle;     height: 54px;     margin-top: -4px;" value="Применить" /></div>
    </form>
    </td>
      </tr>
     </table>
</div>




</div>

      <?php } else { ?>

      <!--Показать: <a <?=($_SERVER['REQUEST_URI'] == '/dashboard/?target=all') ? 'style="font-weight:bold;"' : '';?> href="?target=all">Все</a> /
      <a <?=($_SERVER['REQUEST_URI'] == '/dashboard/?target=inwork') ? 'style="font-weight:bold;"' : '';?> href="/dashboard/?target=inwork">Принятые</a> /
      <a <?=($_SERVER['REQUEST_URI'] == '/dashboard/?target=approve') ? 'style="font-weight:bold;"' : '';?> href="?target=approve">Подтвержденные</a> /
      <a <?=($_SERVER['REQUEST_URI'] == '/dashboard/?target=needparts') ? 'style="font-weight:bold;"' : '';?> href="?target=needparts">Нужны запчасти</a> /
      <a <?=($_SERVER['REQUEST_URI'] == '/dashboard/?target=partsintransit') ? 'style="font-weight:bold;"' : '';?> href="?target=partsintransit">Запчасти в пути</a> /
      <a <?=($_SERVER['REQUEST_URI'] == '/dashboard/?target=cancelled') ? 'style="font-weight:bold;"' : '';?> href="?target=cancelled">Отклоненные</a> /
      <a <?=($_SERVER['REQUEST_URI'] == '/dashboard/?target=courier') ? 'style="font-weight:bold;"' : '';?> href="?target=courier">C выездными ремонтами</a>
     -->  <?php }  ?>
       <!--<div style="float:right;text-align:right"><a href="#"  class="dates_filter">Фильтр по датам &#9660;</a><br>
      <a href="#" class="master_filter">Массовое изменение ремонтов &#9660;</a></div>

      </div>  -->

    <div class="dates_block" style="    margin-top: 30px;vertical-align:middle;margin-bottom:10px;">
    <div style="display:inline-block;"><form method="GET"><span style="width: 150px;display: inline-block;text-align:right;">Дата подтверждения </span><span id="two-inputs"> от <input type="text" id="date-range200" name="date1" style="width: 120px;    text-align: center;    height: 40px;    padding: 0;" value="<?=($_GET['date1'] ? $_GET['date1'] : '')?>"/> до <input name="date2"  type="text" id="date-range201" style="width: 120px;    text-align: center;    height: 40px;    padding: 0;" value="<?=($_GET['date2'] ? $_GET['date2'] : '')?>"/></span><input class="green_button" type="submit" style="display: inline-block;margin-left:15px;  vertical-align: middle;    height: 40px;    margin-top: -4px;" value="Применить" /></form></div>
   <div style="display:inline-block;    margin-left: 50px;"><form method="GET"><select  name="status_check" ><option value="">Выберите статус</option><option value="1">Уценка</option><option value="2">Утиль</option><option value="3">Вернули обратно клиенту</option><option value="4">Подтвердилось</option><option value="5">Не подтвердилось</option>   </select><input class="green_button" type="submit" style="display: inline-block;margin-left:15px;  vertical-align: middle;    height: 40px;    margin-top: -4px;" value="Применить" /></form></div>

 </div>

    <!--<div class="master_block" style="    margin-top: 30px;vertical-align:middle;<?=$display;?>">
    <form class="sub_change_form">
    <div style="display:inline-block;padding-left:20px;"><select class="select2 nomenu" name="master_id" style="width:260px;width: 100%;min-width:200px;"><option value="">Выберите мастера</option><?=master_select();?></select></div>
    <div style="display:inline-block;padding-left:20px;margin-top:10px;"><input class="green_button sub_change" type="submit" style="display: inline-block;margin-left:15px;  vertical-align: middle;    height: 40px;    margin-top: -4px;" value="Применить" /></div>
        </form>
    <br><br></div> -->

     <div style="vertical-align:middle;position:relative">
     <?php if (\models\User::hasRole('admin')) { ?>
     <?php if ($_GET['get'] != 'deleted') { ?>

     <div class="add" style="padding-top:0px;display:inline-block;">
     <a style="width: auto;padding-left: 7px;padding-right: 7px;background:#EB0000;color:#fff;vertical-align: middle;" href="/dashboard/deleted/" class="button">Удаленные ремонты</a>
    </div>

   <font style="color:#FF9900;float: right; display:inline-block; vertical-align: middle;" ><a href="?impo=1">Требуют внимания (<?=check_status();?>)</a></font>

    <?php } else { ?>
      <div class="add" style="padding-top:0px;display:inline-block;">
     <a style="width: auto;padding-left: 7px;padding-right: 7px;vertical-align: middle;" href="/dashboard/" class="button">Активные ремонты</a> <a style="width: auto;padding-left: 7px;padding-right: 7px;background:#83b41c;color:#fff;vertical-align: middle;" href="#" class="gen_nak button">Объединить наклейки</a> <a style="width: auto;padding-left: 7px;padding-right: 7px;background:#83b41c;color:#fff;vertical-align: middle;" href="#" class="gen_kvit button">Объединить квитанции</a>
    </div>

    <?php } ?>



    <?php } else if (!User::hasRole('slave-admin') && User::hasRole('admin')) { ?>

    <font style="color:#FF9900;float: right; display:inline-block; vertical-align: middle;" ><a href="?impo=1">Требуют внимания (<?=check_status_user();?>)</a></font>

    <?php } ?>




    </div>

   <?php

  if ($_GET['date1'] != '' && $_GET['date2'] != '') {
$where_date = ' and DATE(app_date) BETWEEN \''.mysqli_real_escape_string($db, $_GET['date1']).'\' AND \''.mysqli_real_escape_string($db, $_GET['date2']).'\'';
}

     /*$sql = mysqli_query($db, 'SELECT * FROM `repairs`  WHERE id IN ( SELECT MAX(id) FROM repairs where doubled = 1 and deleted != 1 '.$where_date.' GROUP BY serial ) ORDER BY STR_TO_DATE(date_get,\'%d.%m.%Y\') desc');
     $yes = 0;
     $no = 0;
     $yes_2 = 0;
     $no_2 = 0;
     $back_2 = 0;


     while ($row = mysqli_fetch_array($sql)) {  */

      //$row = @mysqli_fetch_array(mysqli_query($db, 'SELECT id,repair_final FROM `repairs` WHERE `serial` = \''.$row['serial'].'\' order by STR_TO_DATE(date_get,\'%d.%m.%Y\') DESC LIMIT 1'));
      //$row = @mysqli_fetch_array(mysqli_query($db, 'SELECT id,problem_id,repair_final FROM `repairs` WHERE `serial` = \''.$row['serial'].'\' order by STR_TO_DATE(date_get,\'%d.%m.%Y\') DESC LIMIT 1'));


     /*if (in_array($row['problem_id'], $problems_array['test'])) {
      $no++;
      } else if (in_array($row['problem_id'], $problems_array['repaira'])) {
      $yes++;
      } else if (in_array($row['problem_id'], $problems_array['repair'])) {
      $yes++;
      }

      if (in_array($row['problem_id'], $problems_array['test'])) {
        if ($row['repair_final'] == 2) {
          $yes_2++;
         // echo $row['id']."\n";
        }
        if ($row['repair_final'] == 3 || $row['repair_final'] == 1) {
          $back_2++;
        }
      } else if (in_array($row['problem_id'], $problems_array['repaira'])) {
        if ($row['repair_final'] == 2) {
          $yes_2++;
          //echo $row['id']."\n";
        }
        if ($row['repair_final'] == 3 || $row['repair_final'] == 1) {
          $back_2++;
        }
      } else if (in_array($row['problem_id'], $problems_array['repair'])) {
        if ($row['repair_final'] == 2) {
          $no_2++;
        }
      }  */


     // }
$problems_array = problems_array_id();
     $check_sql = ' and `problem_id` IN ('.$problems_array['test'].', '.$problems_array['repaira'].') and `repair_final` = 2 ';
     $yes_2 = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `repairs`  WHERE id IN ( SELECT MAX(id) FROM repairs where doubled = 1 and deleted != 1 '.$where_date.' '.$check_sql.' GROUP BY serial )'))['COUNT(*)'];
     //echo 'SELECT COUNT(*) FROM `repairs`  WHERE id IN ( SELECT MAX(id) FROM repairs where doubled = 1 and deleted != 1 '.$where_date.' '.$check_sql.' GROUP BY serial )';

     $check_sql = ' and problem_id IN ('.$problems_array['repair'].') and `repair_final` = 2 ';
     $no_2 = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `repairs`  WHERE id IN ( SELECT MAX(id) FROM repairs where doubled = 1 and deleted != 1 '.$where_date.' '.$check_sql.' GROUP BY serial ) '))['COUNT(*)'];

     $check_sql = ' and problem_id IN ('.$problems_array['test'].','.$problems_array['repaira'].') and (`repair_final` = 3 or `repair_final` = 1) ';
     $back_2 = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `repairs`  WHERE id IN ( SELECT MAX(id) FROM repairs where doubled = 1 and deleted != 1 '.$where_date.' '.$check_sql.' GROUP BY serial ) '))['COUNT(*)'];

     $check_sql = ' and (problem_id IN ('.$problems_array['repair'].','.$problems_array['repaira'].')) ';
     $yes = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `repairs`  WHERE id IN ( SELECT MAX(id) FROM repairs where doubled = 1 and deleted != 1 '.$where_date.' '.$check_sql.' GROUP BY serial ) '))['COUNT(*)'];

     $check_sql = ' and problem_id IN ('.$problems_array['test'].') ';
     $no = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `repairs`  WHERE id IN ( SELECT MAX(id) FROM repairs where doubled = 1 and deleted != 1 '.$where_date.' '.$check_sql.' GROUP BY serial ) '))['COUNT(*)'];


  $yes_percent = round($yes/($no+$yes)*100);
  $no_percent = round($no/($no+$yes)*100);
  $yes_2_percent = round($yes_2/($no_2+$yes_2+$back_2)*100);
  $no_2_percent = round($no_2/($no_2+$yes_2+$back_2)*100);
  $back_2_percent = round($back_2/($no_2+$yes_2+$back_2)*100);
   ?>

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
                <?=$yes;?>, <?=$no;?>
                ],
                backgroundColor: [
                    window.chartColors.green,
                    window.chartColors.red
                ]
            }],
            labels: [
                "Подтвердилось - <?=$yes_percent;?>%",
                "Не подтвердилось - <?=$no_percent;?>%"
            ]
        },
        options: {
            responsive: true,
            legend: {
                position: 'top',
                display: true
            },
            title: {
                display: false,
                text: 'Сводка'
            },
            animation: {
                animateScale: true,
                animateRotate: true
            }
        }
    };
 var config2 = {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [
                <?=$yes_2;?>, <?=$no_2;?>, <?=$back_2;?>
                ],
                backgroundColor: [
                    window.chartColors.blue,
                    window.chartColors.orange
                ]
            }],
            labels: [
                "Уценка - <?=$yes_2_percent;?>%",
                "Утиль - <?=$no_2_percent;?>%",
                "Вернули обратно клиенту - <?=$back_2_percent;?>%",
            ]
        },
        options: {
            responsive: true,
            legend: {
                position: 'top',
                display: true
            },
            title: {
                display: false,
                text: 'Сводка'
            },
            animation: {
                animateScale: true,
                animateRotate: true
            }
        }
    };
    window.onload = function() {
        var ctx = document.getElementById("chart-area").getContext("2d");
        window.myDoughnut = new Chart(ctx, config);

        var ctx2 = document.getElementById("chart-area2").getContext("2d");
        window.myDoughnut = new Chart(ctx2, config2);
    };

    });



 </script>


 <div id="canvas-holder" height="300" width="500" style="width: 512px;   display: inline-block;">
        <canvas id="chart-area" />
    </div>
 <div id="canvas-holder" height="300" width="500" style="width: 512px;   display: inline-block;">
        <canvas id="chart-area2" />
    </div>
    <br><br>
  <table id="table_content" class="display" cellspacing="0" width="100%" style="    font-size: 16px;">
        <thead>
            <tr>

                <th align="left" data-priority="1" style="min-width:150px;width:150px;max-width:150px;">Модель</th>
                <th align="left" >Партия возврата</th>
                <th align="left" data-priority="2">Серийный номер</th>
                <th align="left" >АНРП №</th>
                <th align="left" data-priority="4" style="width:70px;max-width:70px;">Дата приема</th>
                <th align="left"  style="width:120px;"> Закончить до</th>
                <th align="left" data-priority="2" style="width:70px;max-width:70px;">Дата готовности</th>
                <th align="left" >Внутренний номер в асц</th>
                <th align="left" >№</th>
                <th align="left">Дата создания</th>
                <th align="left">Поступил от</th>
                <th align="left">Клиент</th>
                <th align="left" data-priority="2" style="width:150px;max-width:150px;">Неисправность</th>
                <th align="left" >Партия возврата</th>
                <th align="left" >Итоги ремонта</th>
                <th align="left" >Начало ремонта</th>
                <th align="left">Конец ремонта</th>
                <th align="left" data-priority="3" style="width:120px;max-width:120px;">Мастер</th>
                <th align="left" >Ремонт</th>
                <th align="center" data-priority="5" style="width:250px;max-width:250px;">Операции</th>
                <th align="center" data-priority="4" style="min-width:150px;width:250px;max-width:200px;">Статус</th>
                <?php if (\models\User::hasRole('master', 'slave-admin', 'taker')) {    ?>
                <th align="center" data-priority="4">21</th>
                <?php } ?>
            </tr>
        </thead>


</table>


</div>


        </div>
  </div>
</body>
</html>