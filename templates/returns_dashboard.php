<?php

use models\staff\Staff;

function content_list() {
  global $db, $config;

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

     $sort_col = 'model_name';
     break;
     case 4:
     $sort_col = 'receive_date';
     break;
     case 5:
     $sort_col = 'ending';
     break;
     case 6:
     $sort_col = 'master_app_date';
     break;
     case 12:
     $sort_col = 'bugs';
     break;
     case 18:
     $sort_col = 'repair_final';
     break;
      case 20:
     $sort_col = 'status_admin';
     break;
     }

     $sLimit = "ORDER by `".$sort_col."` ".$_GET['sSortDir_0']." LIMIT ".mysqli_real_escape_string($db, $_GET['iDisplayStart'] ).", ".mysqli_real_escape_string($db, $_GET['iDisplayLength'] );
    } else {
    $sLimit = "ORDER by `status_admin` asc LIMIT ".mysqli_real_escape_string($db, $_GET['iDisplayStart'] ).", ".mysqli_real_escape_string($db, $_GET['iDisplayLength'] );
    }



  }

$select = '`id`,
`model_id`,
`master_app_date`,
`status_admin_read`,
`serial`,
`status_user_read`,
`client_id`,
`name_shop`,
`create_date`,
`receive_date`,
`ending`,
`return_id`,
`client_type`,
`client`,
`rsc`,
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
'.$sql_return.'
'.$sql_client.'
) ';

//echo mb_detect_encoding($_GET['sSearch']);

// echo $sWhere;

}

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
$where_target = ' and `status_admin` = \'Утилизирован\'';
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
$where_target = ' and `status_admin` != \'Подтвержден\' ';
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
$services_arr[] = \models\User::getData('id');
$sql2 = mysqli_query($db, 'SELECT * FROM `services_link` WHERE `service_parent` = '.\models\User::getData('id'));
if (mysqli_num_rows($sql2) != false) {
while ($row2 = mysqli_fetch_array($sql2)) {
$services_arr[] .= $row2['service_child'];
}
}

$sql = mysqli_query($db, 'SELECT '.$select.' FROM `repairs` where `return_id` = '.$_GET['return_id'].' and `service_id` IN ('.implode(',', $services_arr).') and `deleted` != 1 '.$where_date.' '.$where_date2.' '.$where_date3.' '.$where_date4.' '.$where_target.' '.$where_type.' '.$where_impo_user.' '.$where_master_user.' '.$sWhere.' '.$sOrder.' '.$sLimit.';');
$sql_total = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` where `return_id` = '.$_GET['return_id'].' and `service_id` IN ('.implode(',', $services_arr).') and `deleted` != 1 '.$where_date.' '.$where_date2.' '.$where_date3.' '.$where_date4.' '.$where_target.' '.$where_type.' '.$where_impo_user.' '.$where_master_user.' '.$sWhere.' ;'));

} if (\models\User::hasRole('taker', 'master')) {

$sql = mysqli_query($db, 'SELECT '.$select.' FROM `repairs` where `return_id` = '.$_GET['return_id'].' and `service_id` = 33 /*and `id` > 9421*/  '.$where_date.' '.$where_date2.' '.$where_date3.' '.$where_date4.' '.$where_target.' '.$where_type.' '.$where_impo_user.' '.$where_master_user.' '.$sWhere.' '.$sOrder.' '.$sLimit.';');
$sql_total = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` where `return_id` = '.$_GET['return_id'].' and `service_id` = 33 /*and `id` > 9421*/ and `deleted` != 1 '.$where_date.' '.$where_date2.' '.$where_date3.' '.$where_date4.' '.$where_target.' '.$where_type.' '.$where_impo_user.' '.$where_master_user.' '.$sWhere.';'));


}  else {
$sql = mysqli_query($db, 'SELECT '.$select.' FROM `repairs` where `return_id` = '.$_GET['return_id'].' and `service_id` = '.\models\User::getData('id').'  '.$where_date.' '.$where_date2.' '.$where_date3.' '.$where_date4.'  '.$where_type.' '.$where_impo_user.' '.$where_master_user.' '.$sWhere.' '.$sOrder.' '.$sLimit.';');
$sql_total = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` where `return_id` = '.$_GET['return_id'].' and `service_id` = '.\models\User::getData('id').' '.$where_date.' '.$where_date2.' '.$where_date3.' '.$where_date4.'  '.$where_type.' '.$where_impo_user.' '.$where_master_user.' '.$sWhere.' ;'));
//echo 'SELECT '.$select.' FROM `repairs` where `service_id` = '.\models\User::getData('id').' and `deleted` != 1 '.$where_date.' '.$where_date2.' '.$where_date3.' '.$where_date4.' '.$where_target.' '.$where_type.' '.$where_impo_user.' '.$where_master_user.' '.$sWhere.' '.$sOrder.' '.$sLimit.';';
//echo 'SELECT * FROM `repairs` where `service_id` = '.\models\User::getData('id').' and `deleted` != 1 '.$where_date.' '.$where_date2.' '.$where_date3.' '.$where_date4.' '.$where_target.' '.$where_type.' '.$where_impo_user.' '.$where_master_user.' '.$sWhere.' '.$sOrder.' '.$sLimit.';';
}


} else {


$where_impo_admin = ($_GET['impo'] == 1) ? ' and `status_admin_read` = 1 ' : '';

if ($_GET['get'] == 'deleted') {
$sql = mysqli_query($db, 'SELECT '.$select.' FROM `repairs`  WHERE `return_id` = '.$_GET['return_id'].' and `deleted` = 1 '.$where_date.' '.$where_date2.' '.$where_date3.' '.$where_date4.' '.$where_type.' '.$where_impo_admin.' '.$where_master_user.' '.$sWhere.' '.$sOrder.' '.$sLimit.';');
$sql_total = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `repairs`  WHERE `return_id` = '.$_GET['return_id'].' and `deleted` = 1 '.$where_date.' '.$where_date2.' '.$where_date3.' '.$where_date4.' '.$where_type.' '.$where_impo_admin.' '.$where_master_user.' '.$sWhere.' ;'));

} else {
$sql = mysqli_query($db, 'SELECT '.$select.' FROM `repairs` where `return_id` = '.$_GET['return_id'].' and   '.$where_date.' '.$where_date2.' '.$where_date3.' '.$where_date4.' '.$where_type.' '.$where_impo_admin.' '.$where_master_user.' '.$sWhere.' '.$sLimit.';');
$sql_total = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` where `return_id` = '.$_GET['return_id'].' and   '.$where_date.' '.$where_date2.' '.$where_date3.' '.$where_date4.'  '.$where_type.' '.$where_impo_admin.' '.$where_master_user.' '.$sWhere.' ;'));

//echo 'SELECT COUNT(*) FROM `repairs` where `deleted` != 1  '.$where_date.' '.$where_date2.' '.$where_date3.' '.$where_date4.' '.$where_target.' '.$where_type.' '.$where_impo_admin.' '.$where_master_user.' '.$sWhere.' ;';
}

}
if (mysqli_num_rows($sql) != false) {
      while ($row = mysqli_fetch_array($sql)) {
      $status = array(1 => 'Гарантийный', 2 => 'Платный', 3 => 'Повторный', 4 => 'Предпродажный', 5 => 'Условно-гарантийный');

      $model = model($row['model_id']);

      if (!\models\User::hasRole('master') || (\models\User::hasRole('master') && check_allow($model['cat'], \models\User::getData('id')))){

      if (\models\User::hasRole('admin')) {
      $draw_color = ($row['status_admin_read'] == 1) ? 'green' : '';
      if ($row['serial']) {
      $draw_color3 = (mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` WHERE `serial` = \''.mysqli_real_escape_string($db, $row['serial']).'\' and `deleted` != 1 and `id` != '.$row['id']))['COUNT(*)'] > 0) ? 'redone' : '';
      }
      } else {
      $draw_color = ($row['status_user_read'] == 1) ? 'green' : '';
      }

      if ($row['client_id']) {
      $client_info = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `clients` WHERE `id` = \''.mysqli_real_escape_string($db, $row['client_id']).'\' LIMIT 1;'));
      } else {
      $client_info['name'] = htmlentities($row['name_shop']);
      }
      $draw_color2 = ($config['last_admin'] == $row['id']) ? 'orange' : '';
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
      $client = mysqli_fetch_array(mysqli_query($db, 'SELECT `days` FROM `clients` WHERE `id` = \''.mysqli_real_escape_string($db, $row['client_id']).'\''));
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
$return = mysqli_fetch_array(mysqli_query($db, 'SELECT `name` FROM `returns` WHERE `id` = \''.mysqli_real_escape_string($db, $row['return_id']).'\''));

      if ($row['client_type'] == 1) {
      $clientz = $row['client'];
      $clientz2 = $row['name_shop'].' /';
      } else {
       $clientz = $client_info['name'];
       $clientz2 = $return['name'].' /';
      }



      $content_list[] = ($model['name']) ? $model['name'] : '!!!';
      $content_list[] = $clientz.' / '.$clientz2.' Закончить до '.$edning;

      //(A) Фолиум / FDS9900999 / закончить до 21.05.2018
      $content_list[] = $row['serial'];

      $content_list[] = ($row['anrp_use']) ? '<a target="_blank" href="https://crm.r97.ru/edit-repair/'.preg_replace('/\D/', '', $row['anrp_number']).'/">'.$row['anrp_number'].'</a>' : '';


      if ($row['receive_date'] != '0000-00-00') {

      try {
          $date_new = new DateTime($row['receive_date']);
          $date_ready = $date_new->format("d.m.Y");
      } catch (Exception $e) {

      }
      }
      $content_list[] = $date_ready;
      $content_list[] = date("d.m.Y", strtotime($row['ending']));
      $content_list[] = $row['master_app_date'];

      if (!\models\User::hasRole('admin')) {
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


      if (!\models\User::hasRole('admin') && $row['repair_done'] != 1 && \models\User::hasRole('taker')) {
      if (check_complete($row['id'])) {
      $linkz .= '<a class="mod_fast_true" title="Отправить на проверку" style="background:none;    padding-bottom: 6px;" href="/repair-done/'.$row['id'].'/"><img src="/img/true.png"></a>';
      } else {
      $linkz .= '<a title="Не заполнены все необходимые поля" class="mod_fast_true need_work" title="Отправить на проверку" style="background:none;    padding-bottom: 6px;" href="#" onclick="return false"><img src="/img/true_yellow.png"></a>';
      }
      }

      if (\models\User::getData('id') == 33) {
      $linkz .= '<span style="position:relative;" class="master_change"><a class="add_master" title="Назначить мастера" style="background:none;    padding-bottom: 6px;" href="#"><img style="    height: 17px;   margin-left: 5px;" src="/img/settings.png"></a><select data-id="'.$row['id'].'" name="master_id">
               <option value="0">Выберите мастера</option>
               '.masters_list($row['master_user_id']).'
              </select></span>';
      }

      $confirm = ($row['master_user_id'] != 0) ? 'onclick=\'return confirm("Подтвердите сброс статусов и возврат ремонта в работу.")\'' : 'onclick=\'alert("Сначала выберите мастера!");return false\'';

      if (\models\User::getData('id') == 33 && $row['status_admin'] != '') {
      $linkz .= '<a class="t-2" style="margin-left: 6px;" '.$confirm.' title="Вернуть на доработку" href="/re-edit-repair/'.$row['id'].'/" ></a>';
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


      $rows[] = $content_list;
      unset($content_list);
      unset($linkz);
      unset($date_ready);
      unset($date_new);
      unset($date_ready_created);
      unset($master);
      unset($return);
      unset($clientz);
      unset($clientz2);
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

    return json_encode($results);
}

if ($_GET['date1'] && $_GET['date2']) {
setcookie("target", "", time()+3600*60*31, "/dashboard/");
setcookie("date1", $_GET['date1'], time()+3600*60*31, "/dashboard/");
setcookie("date12", "", time()+3600*60*31, "/dashboard/");
setcookie("date13", "", time()+3600*60*31, "/dashboard/");
setcookie("date14", "", time()+3600*60*31, "/dashboard/");
setcookie("date2", $_GET['date2'], time()+3600*60*31, "/dashboard/");
setcookie("date22", "", time()+3600*60*31, "/dashboard/");
setcookie("date23", "", time()+3600*60*31, "/dashboard/");
setcookie("date24", "", time()+3600*60*31, "/dashboard/");
setcookie("impo", "", time()+3600*60*31, "/dashboard/");
setcookie("type_id", "", time()+3600*60*31, "/dashboard/");
$_COOKIE['target'] = '';
$_COOKIE['date1'] = $_GET['date1'];
$_COOKIE['date12'] = '';
$_COOKIE['date13'] = '';
$_COOKIE['date14'] = '';
$_COOKIE['date2'] = $_GET['date2'];
$_COOKIE['date22'] = '';
$_COOKIE['date23'] = '';
$_COOKIE['date24'] = '';
$_COOKIE['impo'] = '';
$_COOKIE['type_id'] = '';
} else if ($_GET['date12'] && $_GET['date22']) {
setcookie("target", "", time()+3600*60*31, "/dashboard/");
setcookie("date1", "", time()+3600*60*31, "/dashboard/");
setcookie("date12", $_GET['date12'], time()+3600*60*31, "/dashboard/");
setcookie("date13", "", time()+3600*60*31, "/dashboard/");
setcookie("date14", "", time()+3600*60*31, "/dashboard/");
setcookie("date2", "", time()+3600*60*31, "/dashboard/");
setcookie("date22", $_GET['date22'], time()+3600*60*31, "/dashboard/");
setcookie("date23", "", time()+3600*60*31, "/dashboard/");
setcookie("date24", "", time()+3600*60*31, "/dashboard/");
setcookie("impo", "", time()+3600*60*31, "/dashboard/");
setcookie("type_id", "", time()+3600*60*31, "/dashboard/");
$_COOKIE['target'] = '';
$_COOKIE['date1'] = '';
$_COOKIE['date12'] = $_GET['date12'];
$_COOKIE['date13'] = '';
$_COOKIE['date14'] = '';
$_COOKIE['date2'] = '';
$_COOKIE['date22'] = $_GET['date22'];
$_COOKIE['date23'] = '';
$_COOKIE['date24'] = '';
$_COOKIE['impo'] = '';
$_COOKIE['type_id'] = '';
} else if ($_GET['date13'] && $_GET['date23']) {
setcookie("target", "", time()+3600*60*31, "/dashboard/");
setcookie("date1", "", time()+3600*60*31, "/dashboard/");
setcookie("date12", "", time()+3600*60*31, "/dashboard/");
setcookie("date13", $_GET['date13'], time()+3600*60*31, "/dashboard/");
setcookie("date14", "", time()+3600*60*31, "/dashboard/");
setcookie("date2", "", time()+3600*60*31, "/dashboard/");
setcookie("date22", "", time()+3600*60*31, "/dashboard/");
setcookie("date23", $_GET['date23'], time()+3600*60*31, "/dashboard/");
setcookie("date24", "", time()+3600*60*31, "/dashboard/");
setcookie("impo", "", time()+3600*60*31, "/dashboard/");
setcookie("type_id", "", time()+3600*60*31, "/dashboard/");
$_COOKIE['target'] = '';
$_COOKIE['date1'] = '';
$_COOKIE['date12'] = '';
$_COOKIE['date13'] = $_GET['date13'];
$_COOKIE['date14'] = '';
$_COOKIE['date2'] = '';
$_COOKIE['date22'] = '';
$_COOKIE['date23'] = $_GET['date23'];
$_COOKIE['date24'] = '';
$_COOKIE['impo'] = '';
$_COOKIE['type_id'] = '';
} else if ($_GET['date14'] && $_GET['date24']) {
setcookie("target", "", time()+3600*60*31, "/dashboard/");
setcookie("date1", "", time()+3600*60*31, "/dashboard/");
setcookie("date12", "", time()+3600*60*31, "/dashboard/");
setcookie("date13", "", time()+3600*60*31, "/dashboard/");
setcookie("date14", $_GET['date14'], time()+3600*60*31, "/dashboard/");
setcookie("date2", "", time()+3600*60*31, "/dashboard/");
setcookie("date22", "", time()+3600*60*31, "/dashboard/");
setcookie("date23", "", time()+3600*60*31, "/dashboard/");
setcookie("date24", $_GET['date24'], time()+3600*60*31, "/dashboard/");
setcookie("impo", "", time()+3600*60*31, "/dashboard/");
setcookie("type_id", "", time()+3600*60*31, "/dashboard/");
$_COOKIE['target'] = '';
$_COOKIE['date1'] = '';
$_COOKIE['date12'] = '';
$_COOKIE['date13'] = '';
$_COOKIE['date14'] = $_GET['date14'];
$_COOKIE['date2'] = '';
$_COOKIE['date22'] = '';
$_COOKIE['date23'] = '';
$_COOKIE['date24'] = $_GET['date24'];
$_COOKIE['impo'] = '';
$_COOKIE['type_id'] = '';
} else if ($_GET['impo']) {
setcookie("target", "", time()+3600*60*31, "/dashboard/");
setcookie("date1", "", time()+3600*60*31, "/dashboard/");
setcookie("date12", "", time()+3600*60*31, "/dashboard/");
setcookie("date13", "", time()+3600*60*31, "/dashboard/");
setcookie("date14", "", time()+3600*60*31, "/dashboard/");
setcookie("date2", "", time()+3600*60*31, "/dashboard/");
setcookie("date22", "", time()+3600*60*31, "/dashboard/");
setcookie("date23", "", time()+3600*60*31, "/dashboard/");
setcookie("date24", "", time()+3600*60*31, "/dashboard/");
setcookie("impo", $_GET['impo'], time()+3600*60*31, "/dashboard/");
setcookie("type_id", "", time()+3600*60*31, "/dashboard/");
$_COOKIE['target'] = '';
$_COOKIE['date1'] = '';
$_COOKIE['date12'] = '';
$_COOKIE['date13'] = '';
$_COOKIE['date14'] = '';
$_COOKIE['date2'] = '';
$_COOKIE['date22'] = '';
$_COOKIE['date23'] = '';
$_COOKIE['date24'] = '';
$_COOKIE['impo'] = $_GET['impo'];
$_COOKIE['type_id'] = '';
} else if ($_GET['target']) {
setcookie("target", $_GET['target'], time()+3600*60*31, "/dashboard/");
setcookie("date1", "", time()+3600*60*31, "/dashboard/");
setcookie("date12", "", time()+3600*60*31, "/dashboard/");
setcookie("date13", "", time()+3600*60*31, "/dashboard/");
setcookie("date14", "", time()+3600*60*31, "/dashboard/");
setcookie("date2", "", time()+3600*60*31, "/dashboard/");
setcookie("date22", "", time()+3600*60*31, "/dashboard/");
setcookie("date23", "", time()+3600*60*31, "/dashboard/");
setcookie("date24", "", time()+3600*60*31, "/dashboard/");
setcookie("impo", "", time()+3600*60*31, "/dashboard/");
setcookie("type_id", "", time()+3600*60*31, "/dashboard/");
$_COOKIE['target'] = $_GET['target'];
$_COOKIE['date1'] = '';
$_COOKIE['date12'] = '';
$_COOKIE['date13'] = '';
$_COOKIE['date14'] = '';
$_COOKIE['date2'] = '';
$_COOKIE['date22'] = '';
$_COOKIE['date23'] = '';
$_COOKIE['date24'] = '';
$_COOKIE['impo'] = '';
$_COOKIE['type_id'] = '';
} if ($_GET['type_id']) {
setcookie("target", "", time()+3600*60*31, "/dashboard/");
setcookie("date1", "", time()+3600*60*31, "/dashboard/");
setcookie("date12", "", time()+3600*60*31, "/dashboard/");
setcookie("date13", "", time()+3600*60*31, "/dashboard/");
setcookie("date14", "", time()+3600*60*31, "/dashboard/");
setcookie("date2", "", time()+3600*60*31, "/dashboard/");
setcookie("date22", "", time()+3600*60*31, "/dashboard/");
setcookie("date23", "", time()+3600*60*31, "/dashboard/");
setcookie("date24", "", time()+3600*60*31, "/dashboard/");
setcookie("impo", "", time()+3600*60*31, "/dashboard/");
setcookie("type_id", $_GET['type_id'], time()+3600*60*31, "/dashboard/");
$_COOKIE['target'] = '';
$_COOKIE['date1'] = '';
$_COOKIE['date12'] = '';
$_COOKIE['date13'] = '';
$_COOKIE['date14'] = '';
$_COOKIE['date2'] = '';
$_COOKIE['date22'] = '';
$_COOKIE['date23'] = '';
$_COOKIE['date24'] = '';
$_COOKIE['impo'] = '';
$_COOKIE['type_id'] = $_GET['type_id'];
} else {

if ($_GET['ajaxed'] != 1) {
redir();
}

}

function redir() {
if ($_GET['get'] == 'deleted' || $_GET['master_id']) {
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
}

$return_info = return_info($_GET['return_id']);

function model($id) {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `models` where `id` = '.$id);
      while ($row = mysqli_fetch_array($sql)) {
      $content = $row;

      }
    return $content;
}

function master_select() {
  $content = '';
$masters = Staff::getMasters();
      foreach ($masters as $row) {
      if ($_GET['master_id'] == $row['id']) {
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
$sql = mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` where `status_user_read` = 1 and `deleted` != 1 and `service_id` = '.\models\User::getData('id').' and `status_admin` != \'\' and `status_admin` != \'Подтвержден\' ;');
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
<title>Панель управления</title>
<link href="<?=$config['url'];?>css/fonts.css" rel="stylesheet" />
<link href="<?=$config['url'];?>css/style.css" rel="stylesheet" />
<script src="/_new-codebase/front/vendor/jquery/jquery-1.7.2.min.js"  ></script>
<script src="<?=$config['url'];?>js/jquery-ui.min.js"></script>
<script src="<?=$config['url'];?>js/jquery.placeholder.min.js"></script>
<script src="<?=$config['url'];?>js/jquery.formstyler.min.js"></script>
<script src="<?=$config['url'];?>js/main.js"></script>
<script src="<?=$config['url'];?>notifier/js/index.js"></script>
<script src="/_new-codebase/front/vendor/tooltipster/tooltipster.bundle.min.js"></script>
<script src='/_new-codebase/front/vendor/mustache/mustache.min.js'></script>
<script src="/_new-codebase/front/vendor/js.cookie-2.2.0.min.js"></script>
<script src="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.concat.min.js"></script>
<link rel="stylesheet" href="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.min.css" />
<link rel="stylesheet" href="/_new-codebase/front/vendor/tooltipster/tooltipster.bundle.min.css" />
<link rel="stylesheet" href="/_new-codebase/front/vendor/tooltipster/tooltipster-sideTip-shadow.min.css" />
<link rel="stylesheet" href="/_new-codebase/front/vendor/animate.min.css" />
<script  src="/_new-codebase/front/vendor/datatables/1.10.12/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?=$config['url'];?>css/datatables.css">

<script  src="/_new-codebase/front/vendor/datatables/2.1.1/dataTables.responsive.min.js"></script>
<link rel="stylesheet" type="text/css" href="/_new-codebase/front/vendor/datatables/2.1.1/responsive.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="<?=$config['url'];?>notifier/css/style.css">
<link rel="stylesheet" type="text/css" href="<?=$config['url'];?>js/daterangepicker.css">
<script src="<?=$config['url'];?>js/moment.min.js"></script>
<script src="<?=$config['url'];?>js/jquery.daterangepicker.js"></script>

<style>
.ui-selectmenu-button:after {
    display: none;
}
.ui-selectmenu-button {
    width: 200px;
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
background: rgba(219, 255, 183, 0.6) !important;
}
.orange {
background: rgba(255, 153, 51, 0.4) !important;
}
.redone {
background: rgba(255, 10, 10, 0.4) !important;
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

  $('#two-inputs3').dateRangePicker(
  {
    separator : ' to ',
    getValue: function()
    {
      if ($('#date-range2003').val() && $('#date-range2013').val() )
        return $('#date-range2003').val() + ' to ' + $('#date-range2013').val();
      else
        return '';
    },
    setValue: function(s,s1,s2)
    {
      $('#date-range2003').val(s1);
      $('#date-range2013').val(s2);
    }
  });

  $('#two-inputs4').dateRangePicker(
  {
    separator : ' to ',
    getValue: function()
    {
      if ($('#date-range2004').val() && $('#date-range2014').val() )
        return $('#date-range2004').val() + ' to ' + $('#date-range2014').val();
      else
        return '';
    },
    setValue: function(s,s1,s2)
    {
      $('#date-range2004').val(s1);
      $('#date-range2014').val(s2);
    }
  });

 $('.need_work').tooltipster({
                              trigger: 'hover',
                              position: 'top',
                              animation: 'grow',
                              theme: 'tooltipster-shadow'
                          });

    var groupColumn = 1;

    var table = $('#table_content').dataTable({
      "bStateSave":false,
      "responsive": true,
      "dom": '<"top"flp<"clear">>rt<"bottom"ip<"clear">>',
      "bProcessing": true,
      "bServerSide": true,
      "sAjaxSource": "<?=(strpos($_SERVER['REQUEST_URI'], '?') ? $_SERVER['REQUEST_URI'].'&ajaxed=1' : $_SERVER['REQUEST_URI'].'?ajaxed=1');?>",
      "pageLength": 80,
       "order": [[ 20, 'desc' ]],
             "aoColumns": [
            null,
            null,
            null,
           null,
            { "sType": "date-uk" },
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
        ],
      "columnDefs": [
            { "visible": false, "targets": groupColumn }
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

jQuery.extend( jQuery.fn.dataTableExt.oSort, {
"date-uk-pre": function ( a ) {
    var ukDatea = a.split('.');
    return (ukDatea[2] + ukDatea[1] + ukDatea[0]) * 1;
},

"date-uk-asc": function ( a, b ) {
    return ((a < b) ? -1 : ((a > b) ? 1 : 0));
},

"date-uk-desc": function ( a, b ) {
    return ((a < b) ? 1 : ((a > b) ? -1 : 0));
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
   parent.jQuery.fancybox.getInstance().update();

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
</style>
</head>

<body style="max-width:1250px">

<div class="viewport-wrapper" style="max-width:1250px;padding-top:20px;">



<div class="wrapper">


           <h2>Партия возврата <?=$return_info['name'];?></h2>
                        <br>
  <div class="adm-catalog">
      <div style="vertical-align:middle;    padding-bottom: 15px;   font-size: 16px;position:relative; ">


      <?php if (\models\User::hasRole('slave-admin', 'taker')) { ?> <br>
      Показать: <a <?=($_SERVER['REQUEST_URI'] == '/dashboard/?target=all') ? 'style="font-weight:bold;"' : '';?> href="?target=all">Все</a> /
      <a <?=($_SERVER['REQUEST_URI'] == '/dashboard/?target=accepted') ? 'style="font-weight:bold;"' : '';?> href="?target=accepted">Принят</a> /
      <a <?=($_SERVER['REQUEST_URI'] == '/dashboard/?target=inprogress') ? 'style="font-weight:bold;"' : '';?> href="?target=inprogress">В работе</a> /
      <a <?=($_SERVER['REQUEST_URI'] == '/dashboard/?target=ready') ? 'style="font-weight:bold;"' : '';?> href="?target=ready">Готов</a> /
      <a <?=($_SERVER['REQUEST_URI'] == '/dashboard/?target=out') ? 'style="font-weight:bold;"' : '';?> href="?target=out">Выдан</a> /
      <a <?=($_SERVER['REQUEST_URI'] == '/dashboard/?target=trash') ? 'style="font-weight:bold;"' : '';?> href="?target=trash">Утилизирован</a>
      <br>

                   <div class="add" style="padding-top: 0px;
    display: inline-block;
    position: absolute;
    top: -15px;
    right: 0px;">
    <a style="width: auto;padding-left: 7px;padding-right: 7px;background:#83b41c;color:#fff;vertical-align: middle;" href="#" class="gen_nak button">Объединить наклейки</a> <a style="width: auto;padding-left: 7px;padding-right: 7px;background:#83b41c;color:#fff;vertical-align: middle;" href="#" class="gen_kvit button">Объединить квитанции</a>
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
    bottom: -28px;
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
}
.adm-widget .act {

    width: 438px;
}
      </style>
      <table style="    width: 100%;">
         <tr>  <td>
      <div class="act">
        <div class="level" style="    display: block;    width: 100%;">Тип клиента</div>
        <input id="landing" type="hidden" value="1" name="landing">
        <div class="adm-switch adm-switch-1" style="width: 82px;z-index: 9999999999;">
          <div class="slider">
            <div class="inner"></div>
          </div>
        </div>
        <div class="txt txt-l">Потребитель</div>
        <div class="txt txt-c">Все</div>
        <div class="txt txt-r">Магазин</div>


      </div> </td>

              <td style="    text-align: right;">
             <form method="GET" style="padding-top:3px;">
    <div style="display:inline-block;"><span style="width: 200px;display: inline-block;text-align:right;">Фильтр по мастеру </span>&nbsp;&nbsp;<select class="select2 nomenu" name="master_id"><option value="">Выберите мастера</option><?=master_select();?></select><input class="green_button" type="submit" style="      background: #80bd03;display: inline-block;     margin-left: 15px;     vertical-align: middle;     height: 54px;     margin-top: -4px;" value="Применить" /></div>
    </form>
    </td>
      </tr>
     </table>
</div>




</div>

      <?php } else { ?>

      Показать: <a <?=($_SERVER['REQUEST_URI'] == '/dashboard/?target=all') ? 'style="font-weight:bold;"' : '';?> href="?target=all">Все</a> /
      <a <?=($_SERVER['REQUEST_URI'] == '/dashboard/?target=inwork') ? 'style="font-weight:bold;"' : '';?> href="/dashboard/?target=inwork">Принятые</a> /
      <a <?=($_SERVER['REQUEST_URI'] == '/dashboard/?target=approve') ? 'style="font-weight:bold;"' : '';?> href="?target=approve">Подтвержденные</a> /
      <a <?=($_SERVER['REQUEST_URI'] == '/dashboard/?target=needparts') ? 'style="font-weight:bold;"' : '';?> href="?target=needparts">Нужны запчасти</a> /
      <a <?=($_SERVER['REQUEST_URI'] == '/dashboard/?target=partsintransit') ? 'style="font-weight:bold;"' : '';?> href="?target=partsintransit">Запчасти в пути</a> /
      <a <?=($_SERVER['REQUEST_URI'] == '/dashboard/?target=cancelled') ? 'style="font-weight:bold;"' : '';?> href="?target=cancelled">Отклоненные</a> /
      <a <?=($_SERVER['REQUEST_URI'] == '/dashboard/?target=courier') ? 'style="font-weight:bold;"' : '';?> href="?target=courier">C выездными ремонтами</a>
       <?php }  ?>
       <div style="float:right;text-align:right"><a href="#"  class="dates_filter">Фильтр по датам &#9660;</a><br>
      <a href="#" class="master_filter">Массовое изменение ремонтов &#9660;</a></div>

      </div>

    <div class="dates_block" style="    margin-top: 30px;vertical-align:middle;<?=$display;?>">
    <div style="display:inline-block;padding-left:20px;"><form method="GET"><span style="width: 130px;display: inline-block;text-align:right;">Дата продажи </span><span id="two-inputs"> от <input type="text" id="date-range200" name="date1" style="width: 120px;    text-align: center;    height: 40px;    padding: 0;" value="<?=($_GET['date1'] ? $_GET['date1'] : '')?>"/> до <input name="date2"  type="text" id="date-range201" style="width: 120px;    text-align: center;    height: 40px;    padding: 0;" value="<?=($_GET['date2'] ? $_GET['date2'] : '')?>"/></span><input class="green_button" type="submit" style="display: inline-block;margin-left:15px;  vertical-align: middle;    height: 40px;    margin-top: -4px;" value="Применить" /></form></div>
    <div style="display:inline-block;padding-left:20px;"><form method="GET"><span style="width: 130px;display: inline-block;text-align:right;">Дата приема </span><span id="two-inputs2"> от <input type="text" id="date-range2002" name="date12" style="width: 120px;    text-align: center;    height: 40px;    padding: 0;" value="<?=($_GET['date12'] ? $_GET['date12'] : '')?>"/> до <input name="date22"  type="text" id="date-range2012" style="width: 120px;    text-align: center;    height: 40px;    padding: 0;" value="<?=($_GET['date22'] ? $_GET['date22'] : '')?>"/></span><input class="green_button" type="submit" style="display: inline-block;margin-left:15px;  vertical-align: middle;    height: 40px;    margin-top: -4px;" value="Применить" /></form></div>
    <div style="display:inline-block;padding-left:20px;margin-top:10px;"><form method="GET"><span style="width: 130px;display: inline-block;text-align:right;">Начала ремонта </span><span id="two-inputs3"> от <input type="text" id="date-range2003" name="date13" style="width: 120px;    text-align: center;    height: 40px;    padding: 0;" value="<?=($_GET['date13'] ? $_GET['date13'] : '')?>"/> до <input name="date23"  type="text" id="date-range2013" style="width: 120px;    text-align: center;    height: 40px;    padding: 0;" value="<?=($_GET['date23'] ? $_GET['date23'] : '')?>"/></span><input class="green_button" type="submit" style="display: inline-block;margin-left:15px;  vertical-align: middle;    height: 40px;    margin-top: -4px;" value="Применить" /></form></div>
    <div style="display:inline-block;padding-left:20px;margin-top:10px;"><form method="GET"><span style="width: 130px;display: inline-block;text-align:right;">Конца ремонта </span><span id="two-inputs4"> от <input type="text" id="date-range2004" name="date14" style="width: 120px;    text-align: center;    height: 40px;    padding: 0;" value="<?=($_GET['date14'] ? $_GET['date14'] : '')?>"/> до <input name="date24"  type="text" id="date-range2014" style="width: 120px;    text-align: center;    height: 40px;    padding: 0;" value="<?=($_GET['date24'] ? $_GET['date24'] : '')?>"/></span><input class="green_button" type="submit" style="display: inline-block;margin-left:15px;  vertical-align: middle;    height: 40px;    margin-top: -4px;" value="Применить" /></form></div>
    <br><br></div>

    <div class="master_block" style="    margin-top: 30px;vertical-align:middle;<?=$display;?>">
    <form class="sub_change_form">
    <div style="display:inline-block;padding-left:20px;"><select class="select2 nomenu" name="master_id"><option value="">Выберите мастера</option><?=master_select();?></select></div>
    <div style="display:inline-block;padding-left:20px;"><select  name="status_admin" ><?php echo '<option value="">Без статуса</option> <option value="В обработке" '.(($row['status_admin'] == 'В обработке') ? 'selected' : '').'>В обработке</option> <option value="В работе" '.(($row['status_admin'] == 'В работе') ? 'selected' : '').'>В работе</option> <option value="Выдан" '.(($row['status_admin'] == 'Выдан') ? 'selected' : '').'>Выдан</option> <option value="Выезд отклонен" '.(($row['status_admin'] == 'Выезд отклонен') ? 'selected' : '').'>Выезд отклонен</option> <option value="Выезд подтвержден" '.(($row['status_admin'] == 'Выезд подтвержден') ? 'selected' : '').'>Выезд подтвержден</option> <option value="Есть вопросы" '.(($row['status_admin'] == 'Есть вопросы') ? 'selected' : '').'>Есть вопросы</option> <option value="Запрос на выезд" '.(($row['status_admin'] == 'Запрос на выезд') ? 'selected' : '').'>Запрос на выезд</option> <option value="Запчасти в пути" '.(($row['status_admin'] == 'Запчасти в пути') ? 'selected' : '').'>Запчасти в пути</option> <option value="На проверке" '.(($row['status_admin'] == 'На проверке') ? 'selected' : '').'>На проверке</option> <option value="Нужны запчасти" '.(($row['status_admin'] == 'Нужны запчасти') ? 'selected' : '').'>Нужны запчасти</option> <option value="Оплачен" '.(($row['status_admin'] == 'Оплачен') ? 'selected' : '').'>Оплачен</option> <option value="Отклонен" '.(($row['status_admin'] == 'Отклонен') ? 'selected' : '').'>Отклонен</option> <option value="Подтвержден" '.(($row['status_admin'] == 'Подтвержден') ? 'selected' : '').'>Подтвержден</option> <option value="Принят" '.(($row['status_admin'] == 'Принят') ? 'selected' : '').'>Принят</option> <option value="Утилизирован" '.(($row['status_admin'] == 'Утилизирован') ? 'selected' : '').'>Утилизирован</option>';?></select></div>
    <div style="display:inline-block;padding-left:20px;margin-top:10px;"><input class="green_button sub_change" type="submit" style="display: inline-block;margin-left:15px;  vertical-align: middle;    height: 40px;    margin-top: -4px;" value="Применить" /></div>
        </form>
    <br><br></div>

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



    <?php } else if (!\models\User::hasRole('slave-admin') && \models\User::hasRole('admin')) { ?>

    <font style="color:#FF9900;float: right; display:inline-block; vertical-align: middle;" ><a href="?impo=1">Требуют внимания (<?=check_status_user();?>)</a></font>

    <?php } ?>




    </div>

    <br><br>
  <table id="table_content" class="display" cellspacing="0" width="100%">
        <thead>
            <tr>

                <th align="left" data-priority="1" style="max-width:120px">Модель</th>
                <th align="left" >Партия возврата</th>
                <th align="left" data-priority="2">Серийный номер</th>
                <th align="left" >АНРП №</th>
                <th align="left" data-priority="4" style="width:100px;">Дата приема</th>
                <th align="left"  style="width:100px;"> Закончить до</th>
                <th align="left" data-priority="2" style="width:100px;">Дата готовности</th>
                <th align="left" >Внутренний номер в асц</th>
                <th align="left" >№</th>
                <th align="left">Дата создания</th>
                <th align="left">Поступил от</th>
                <th align="left">Клиент</th>
                <th align="left" data-priority="2" style="max-width:120px;">Неисправность</th>
                <th align="left" >Партия возврата</th>
                <th align="left" >Итоги ремонта</th>
                <th align="left" >Начало ремонта</th>
                <th align="left">Конец ремонта</th>
                <th align="left" data-priority="3" style="width:120px;">Мастер</th>
                <th align="left" data-priority="9">Ремонт</th>
                <th align="center" data-priority="5" style="width:120px;">Операции</th>
                <th align="center" data-priority="4">Статус</th>
            </tr>
        </thead>


</table>


</div>


        </div>
  </div>
</body>
</html>