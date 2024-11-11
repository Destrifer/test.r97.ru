<?php

use models\User;
use program\core;

if(!empty(core\App::$URLParams['ajax'])){
  switch(core\App::$URLParams['ajax']){
    case 'update-payment-sum':
      $newSum = models\Payments::updatePaymentSum(core\App::$URLParams['payment-id'], core\App::$URLParams['payment-brand']);
      echo json_encode(['sum' => $newSum]);
    break;

  }
  exit;
}

if(\models\User::hasRole('service')){
  disable_notice('/payment/', \models\User::getData('id'));
}


if ($_POST['payed'] == 1) {
setcookie("payed", "1", time()+3600*60*31, "/payments/");
$_COOKIE['payed'] = 1;
} else {
setcookie("payed", "", time()-3600*60*31, "/payments/");
$_COOKIE['payed'] = 0;
}
if ($_POST['notpayed'] == 1) {
setcookie("notpayed", "1", time()+3600*60*31, "/payments/");
$_COOKIE['notpayed'] = 1;
} else {
setcookie("notpayed", "", time()-3600*60*31, "/payments/");
$_COOKIE['notpayed'] = 0;
}
/*if ($_POST['tesler'] == 1) { */
setcookie("tesler", "1", time()+3600*60*31, "/payment/");
$_COOKIE['tesler'] = 1;
/*} else {
setcookie("tesler", "", time()-3600*60*31, "/payments/");
$_COOKIE['tesler'] = 0;
}  */

function get_request_info($id) {
  global $db;
return mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `requests` WHERE `user_id` = '.$id));
}

if (\models\User::hasRole('acct')) {
$add_report = 1;
}

function content_list() {
  global $db;

$horizont_array = array('ЗЭБТ-Harper', 'Horizont', 'Hartens', 'ЗЭБТ-Горизонт', 'Белит-Горизонт', 'OK', 'ЗЭБТ-HARTENS', 'ЗЭБТ-Skyworth', 'ЗЭБТ-Prestigio', 'ROSENLEW');


//$dated = ((date("d") < 5) ? date("Y.m", strtotime("-1 months")) : date("Y.m"));
$counter = 17;
$userID = User::getData('id');
if ($userID == 2) {
$userID = 33;
$add_report = 1;
}

if (!\models\User::hasRole('slave-admin')) {
$dated_sql = 'and `app_date` NOT REGEXP "'.date("Y.m").'"';
}

$sql = mysqli_query($db, 'SELECT * FROM `repairs` where `service_id` = '.$userID.' and `status_id` != 6 and `deleted` = 0 and (`status_admin` = \'Подтвержден\' or `status_admin` = \'Выдан\') '.$dated_sql.' group by `app_date` order by `id` DESC;');
while ($row = mysqli_fetch_array($sql)) {
      //$info = get_request_info($row['id']);
      //$city = get_city($info['city']);

      if($row['app_date']) {
      $exp = explode('.', $row['app_date']);
      $app[$exp['0']][$exp[1]] = '';
      }


      }
      foreach ($app as $year => $val) {
      $year_work = $val;
      foreach ($year_work as $month => $value) {

      $type1 = create_or_get_payment_id($userID, $year, $month, 1); //акт
      $type2 = create_or_get_payment_id($userID, $year, $month, 2); //счет

      /*Проверяем теслер*/
      $sql3 = mysqli_query($db, 'SELECT `model_id` FROM `repairs` where `service_id` = '.$userID.' and `app_date` REGEXP "'.$year.'.'.$month.'" and (`status_admin` = \'Подтвержден\' or `status_admin` = \'Выдан\') and `deleted` = 0 '.$dated_sql.' group by `model_id` order by `id` DESC ;');
      
      while ($row3 = mysqli_fetch_array($sql3)) {

      $model = model_info($row3['model_id']);

      if ($model['brand'] == 'TESLER') {
        $tesler = 1;
      }

      if ($model['brand'] == 'SVEN') {
        $sven = 1;
      }

      if ($model['brand'] == 'ROCH') {
        $roch = 1;
      }

      if (in_array($model['brand'], $horizont_array)) {
        $horizont = 1;
      }

      if ($model['brand'] == 'SELENGA') {
        $selenga = 1;
      }

      }

      if ($tesler == 1) {
      $type3 = create_or_get_payment_id($userID, $year, $month, 3); //акт
      $type4 = create_or_get_payment_id($userID, $year, $month, 4); //счет
      }

      if ($sven == 1) {
      $type9 = create_or_get_payment_id($userID, $year, $month, 9); //акт
      $type10 = create_or_get_payment_id($userID, $year, $month, 10); //счет
      }

      if ($horizont == 1) {
      $type11 = create_or_get_payment_id($userID, $year, $month, 11); //акт
      $type12 = create_or_get_payment_id($userID, $year, $month, 12); //счет
      }

      if ($selenga == 1) {

      $type13 = create_or_get_payment_id($userID, $year, $month, 13); //акт
      $type14 = create_or_get_payment_id($userID, $year, $month, 14); //счет
      }

      if ($roch == 1) {
        $rochAct = create_or_get_payment_id($userID, $year, $month, 15); //акт
        $rochBill = create_or_get_payment_id($userID, $year, $month, 16); //счет
      }

      //if (check_returns_pls($content['return_id'])) {
     // echo $content['return_id'];
     // unset($content);
      /*/Проверка теслера*/


$date_current_sven = new DateTime("01/".$month."/".$year);
$date_from_sven    = new DateTime("01/04/2018");

      if ($_COOKIE['payed'] == 0) {

      if ($type2['status'] == 1) {

      if ($type2['status'] == 0) {
      $status2 = '<td style="background: rgba(255, 71, 71, 0.13);">Не оплачено</td>';
      } else {
      $status2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оплачено</td>';
      }
      if ($type1['original'] == 0) {
      $original = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал акта не получен</td>';
      } else {
      $original = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал акта получен</td>';
      }
      if ($type2['original'] == 0) {
      $original2 = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал счета не получен</td>';
      } else {
      $original2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал счета получен</td>';
      }

      if (!$type2['sum']) {
      $summ = get_service_summ_fast($userID, $month, $year, 'HARPER', '', true);
      } else {
      $summ = $type2['sum'];
      }

      $content_list['total'] += $summ;
      if ($summ  && $summ != '-') {
      $content_list['body'] .= '<tr data-payment-id="'.$type2['id'].'" data-payment-brand="harper">
      <td >'.$year.'.'.$month.'<br><u>HARPER</u></td>
      <td><a href="/get-detail-report/?service-id='.$type2['service_id'].'&month='.$month.'&year='.$year.'&brand=harper">Отчет</a></td>
      <td><a href="/get-payment-act/'.$year.'/'.$month.'/">Акт выполненных работ</a></td>
      <td><a href="/get-payment-bill/'.$year.'/'.$month.'/">Счет на оплату</a></td>';
      if ($add_report == 1) {
      $content_list['body'] .= '<td><a class="dwn" data-href="/get-agent/'.$year.'/'.$month.'/'.$counter.'/HARPER,OLTO/" href="/get-agent/'.$year.'/'.$month.'/'.$counter.'/HARPER,OLTO/">Отчет</a></td>';
      }
      $content_list['body'] .= '<td><span data-payment-sum="'.$type2['id'].'">'.$summ.'</span>,00 руб.</td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$status.'</tr><tr>'.$status2.'</tr></table></td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$original.'</tr><tr>'.$original2.'</tr></table></td>
      <td align="center" class="linkz"><a class="t-1" href="/get-payment-archive/'.$year.'/'.$month.'/" title="Скачать архивом" ></a></td>
      </tr>';
      }

      if ($sven == 1 && $date_current_sven >= $date_from_sven) {

      if ($type10['status'] == 1) {
      if ($type10['status'] == 0) {
      $status2 = '<td style="background: rgba(255, 71, 71, 0.13);">Не оплачено</td>';
      } else {
      $status2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оплачено</td>';
      }
      if ($type9['original'] == 0) {
      $original = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал акта не получен</td>';
      } else {
      $original = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал акта получен</td>';
      }
      if ($type10['original'] == 0) {
      $original2 = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал счета не получен</td>';
      } else {
      $original2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал счета получен</td>';
      }
      //$summ = get_service_summ_fast($userID, $month, $year, 'SVEN');

      if (!$type10['sum']) {
      $summ = get_service_summ_fast($userID, $month, $year, 'SVEN', '', true);
      } else {
      $summ = $type10['sum'];
      }

      $content_list['total'] += $summ;
      if ($summ  && $summ != '-') {
      $content_list['body'] .= '<tr data-payment-id="'.$type10['id'].'" data-payment-brand="sven">
      <td >'.$year.'.'.$month.'<br><u>SVEN</u></td>
      <td><a href="/get-detail-report/?service-id='.$type2['service_id'].'&month='.$month.'&year='.$year.'&brand=sven">Отчет</a></td>
      <td><a href="/get-payment-act/'.$year.'/'.$month.'/sven/">Акт выполненных работ</a></td>
      <td><a href="/get-payment-bill/'.$year.'/'.$month.'/sven/">Счет на оплату</a></td>';
      if ($add_report == 1) {
      $content_list['body'] .= '<td><a class="dwn" data-href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/sven/" href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/sven/">Отчет</a></td>';
      }
      $content_list['body'] .= '<td><span data-payment-sum="'.$type10['id'].'">'.$summ.'</span>,00 руб.</td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$status.'</tr><tr>'.$status2.'</tr></table></td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$original.'</tr><tr>'.$original2.'</tr></table></td>
      <td align="center" class="linkz"><a class="t-1" href="/get-payment-archive/'.$year.'/'.$month.'/sven/" title="Скачать архивом" ></a></td>
      </tr>';
      }
      }
      }

      if ($tesler == 1) {

      if ($type4['status'] == 1) {
      if ($type4['status'] == 0) {
      $status2 = '<td style="background: rgba(255, 71, 71, 0.13);">Не оплачено</td>';
      } else {
      $status2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оплачено</td>';
      }
      if ($type3['original'] == 0) {
      $original = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал акта не получен</td>';
      } else {
      $original = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал акта получен</td>';
      }
      if ($type4['original'] == 0) {
      $original2 = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал счета не получен</td>';
      } else {
      $original2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал счета получен</td>';
      }
      //$summ = get_service_summ_fast($userID, $month, $year, 'TESLER');

      if (!$type4['sum']) {
      $summ = get_service_summ_fast($userID, $month, $year, 'TESLER', '', true);
      } else {
      $summ = $type4['sum'];
      }

      $content_list['total'] += $summ;
      if ($summ  && $summ != '-') {
      $content_list['body'] .= '<tr data-payment-id="'.$type4['id'].'" data-payment-brand="tesler">
      <td >'.$year.'.'.$month.'<br><u>TESLER</u></td>
      <td><a href="/get-detail-report/?service-id='.$type2['service_id'].'&month='.$month.'&year='.$year.'&brand=tesler">Отчет</a></td>
      <td><a href="/get-payment-act/'.$year.'/'.$month.'/tesler/">Акт выполненных работ</a></td>
      <td><a href="/get-payment-bill/'.$year.'/'.$month.'/tesler/">Счет на оплату</a></td>';
      if ($add_report == 1) {
      $content_list['body'] .= '<td><a class="dwn" data-href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/TESLER/" href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/TESLER/">Отчет</a></td>';
      }
      $content_list['body'] .= '<td><span data-payment-sum="'.$type4['id'].'">'.$summ.'</span>,00 руб.</td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$status.'</tr><tr>'.$status2.'</tr></table></td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$original.'</tr><tr>'.$original2.'</tr></table></td>
      <td align="center" class="linkz"><a class="t-1" href="/get-payment-archive/'.$year.'/'.$month.'/tesler/" title="Скачать архивом" ></a></td>
      </tr>';
      }
      }
      }

      if ($roch == 1) {

        if ($rochBill['status'] == 1) {
        if ($rochBill['status'] == 0) {
        $status2 = '<td style="background: rgba(255, 71, 71, 0.13);">Не оплачено</td>';
        } else {
        $status2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оплачено</td>';
        }
        if ($rochAct['original'] == 0) {
        $original = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал акта не получен</td>';
        } else {
        $original = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал акта получен</td>';
        }
        if ($rochBill['original'] == 0) {
        $original2 = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал счета не получен</td>';
        } else {
        $original2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал счета получен</td>';
        }
        if (!$rochBill['sum']) {
        $summ = get_service_summ_fast($userID, $month, $year, 'ROCH', '', true);
        } else {
        $summ = $rochBill['sum'];
        }
  
        $content_list['total'] += $summ;
        if ($summ  && $summ != '-') {
        $content_list['body'] .= '<tr data-payment-id="'.$rochBill['id'].'" data-payment-brand="roch">
        <td >'.$year.'.'.$month.'<br><u>ROCH</u></td>
        <td><a href="/get-detail-report/?service-id='.$type2['service_id'].'&month='.$month.'&year='.$year.'&brand=roch">Отчет</a></td>
        <td><a href="/get-payment-act/'.$year.'/'.$month.'/roch/">Акт выполненных работ</a></td>
        <td><a href="/get-payment-bill/'.$year.'/'.$month.'/roch/">Счет на оплату</a></td>';
        if ($add_report == 1) {
        $content_list['body'] .= '<td><a class="dwn" data-href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/ROCH/" href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/ROCH/">Отчет</a></td>';
        }
        $content_list['body'] .= '<td><span data-payment-sum="'.$rochBill['id'].'">'.$summ.'</span>,00 руб.</td>
        <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$status.'</tr><tr>'.$status2.'</tr></table></td>
        <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$original.'</tr><tr>'.$original2.'</tr></table></td>
        <td align="center" class="linkz"><a class="t-1" href="/get-payment-archive/'.$year.'/'.$month.'/roch/" title="Скачать архивом" ></a></td>
        </tr>';
        }
        }
        }

      if ($horizont == 1) {

      if ($type12['status'] == 1) {
      if ($type12['status'] == 0) {
      $status2 = '<td style="background: rgba(255, 71, 71, 0.13);">Не оплачено</td>';
      } else {
      $status2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оплачено</td>';
      }
      if ($type11['original'] == 0) {
      $original = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал акта не получен</td>';
      } else {
      $original = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал акта получен</td>';
      }
      if ($type12['original'] == 0) {
      $original2 = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал счета не получен</td>';
      } else {
      $original2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал счета получен</td>';
      }
      //$summ = get_service_summ_fast($userID, $month, $year, 'TESLER');

      if (!$type12['sum']) {
      $summ = get_service_summ_fast($userID, $month, $year, 'TESLER', '', true);
      } else {
      $summ = $type12['sum'];
      }

      $content_list['total'] += $summ;
      if ($summ  && $summ != '-') {
      $content_list['body'] .= '<tr>
      <td >'.$year.'.'.$month.'<br><u>ГОРИЗОНТ-СОЮЗ</u></td>
      <td><a href="/get-detail-report/?service-id='.$type12['service_id'].'&month='.$month.'&year='.$year.'&brand=horizont">Отчет</a></td>
      <td><a href="/get-payment-act/'.$year.'/'.$month.'/horizont/">Акт выполненных работ</a></td>
      <td><a href="/get-payment-bill/'.$year.'/'.$month.'/horizont/">Счет на оплату</a></td>';
      if ($add_report == 1) {
      $content_list['body'] .= '<td><a class="dwn" data-href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/HORIZONT/" href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/HORIZONT/">Отчет</a></td>';
      }
      $content_list['body'] .= '<td>'.$summ.',00 руб.</td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$status.'</tr><tr>'.$status2.'</tr></table></td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$original.'</tr><tr>'.$original2.'</tr></table></td>
      <td align="center" class="linkz"><a class="t-1" href="/get-payment-archive/'.$year.'/'.$month.'/horizont/" title="Скачать архивом" ></a></td>
      </tr>';
      }
      }
      }

      if ($selenga == 1) {

      if ($type14['status'] == 1) {
      if ($type14['status'] == 0) {
      $status2 = '<td style="background: rgba(255, 71, 71, 0.13);">Не оплачено</td>';
      } else {
      $status2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оплачено</td>';
      }
      if ($type13['original'] == 0) {
      $original = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал акта не получен</td>';
      } else {
      $original = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал акта получен</td>';
      }
      if ($type14['original'] == 0) {
      $original2 = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал счета не получен</td>';
      } else {
      $original2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал счета получен</td>';
      }
      //$summ = get_service_summ_fast($userID, $month, $year, 'TESLER');

      if (!$type14['sum']) {
      $summ = get_service_summ_fast($userID, $month, $year, 'SELENGA', '', true);
      } else {
      $summ = $type14['sum'];
      }

      $content_list['total'] += $summ;
      if ($summ  && $summ != '-') {
      $content_list['body'] .= '<tr data-payment-id="'.$type14['id'].'" data-payment-brand="selenga-brand">
      <td >'.$year.'.'.$month.'<br><u>SELENGA</u></td>
      <td><a href="/get-detail-report/?service-id='.$type14['service_id'].'&month='.$month.'&year='.$year.'&brand=selenga-brand">Отчет</a></td>
      <td><a href="/get-payment-act/'.$year.'/'.$month.'/selenga-brand/">Акт выполненных работ</a></td>
      <td><a href="/get-payment-bill/'.$year.'/'.$month.'/selenga-brand/">Счет на оплату</a></td>';
      if ($add_report == 1) {
      $content_list['body'] .= '<td><a class="dwn" data-href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/SELENGA/" href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/SELENGA/">Отчет</a></td>';
      }
      $content_list['body'] .= '<td><span data-payment-sum="'.$type14['id'].'">'.$summ.'</span>,00 руб.</td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$status.'</tr><tr>'.$status2.'</tr></table></td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$original.'</tr><tr>'.$original2.'</tr></table></td>
      <td align="center" class="linkz"><a class="t-1" href="/get-payment-archive/'.$year.'/'.$month.'/selenga/" title="Скачать архивом" ></a></td>
      </tr>';
      }
      }
      }

      if ($userID == 33) {

      if (date("Y-n-j", strtotime($year.'-'.$month.'-1')) >= date("Y-n-j", strtotime('2018-2-1')) && date("Y-n", strtotime($year.'-'.$month)) < date("Y-n")) {

      $type5 = create_or_get_payment_id($userID, $year, $month, 5); //счет
      $type6 = create_or_get_payment_id($userID, $year, $month, 6); //счет
      if ($type6['status'] == 0) {
      $status2 = '<td style="background: rgba(255, 71, 71, 0.13);">Не оплачено</td>';
      } else {
      $status2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оплачено</td>';
      }
      if ($type5['original'] == 0) {
      $original = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал акта не получен</td>';
      } else {
      $original = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал акта получен</td>';
      }
      if ($type6['original'] == 0) {
      $original2 = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал счета не получен</td>';
      } else {
      $original2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал счета получен</td>';
      }

      $summ = 120000;
      $content_list['total'] += $summ;
      if ($summ  && $summ != '-') {
      $content_list['body'] .= '<tr>
      <td >'.$year.'.'.$month.'<br><u>Оптима ТП</u></td>
      <td><a href="/get-detail-report/?service-id='.$type5['service_id'].'&month='.$month.'&year='.$year.'&brand=optima-tp">Отчет</a></td>
      <td><a href="/get-payment-act-optima/'.$year.'/'.$month.'/optima-tp/">Акт выполненных работ</a></td>
      <td><a href="/get-payment-bill-optima/'.$year.'/'.$month.'/optima-tp/">Счет на оплату</a></td>';
      if ($add_report == 1) {
      $content_list['body'] .= '<td></td>';
      }
      $content_list['body'] .= '<td>'.$summ.',00 руб.</td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$status.'</tr><tr>'.$status2.'</tr></table></td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$original.'</tr><tr>'.$original2.'</tr></table></td>
      <td align="center" class="linkz"><!--<a class="t-1" href="/get-payment-archive-optima/'.$year.'/'.$month.'/optima-tp/" title="Скачать архивом" ></a>--></td>
      </tr>';
      }

      }

      if (date("Y-n-j", strtotime($year.'-'.$month.'-1')) > date("Y-n-j", strtotime('2018-4-1')) && date("Y-n", strtotime($year.'-'.$month)) < date("Y-n")) {

      $type7 = create_or_get_payment_id($userID, $year, $month, 7); //счет
      $type8 = create_or_get_payment_id($userID, $year, $month, 8); //счет
      if ($type8['status'] == 0) {
      $status2 = '<td style="background: rgba(255, 71, 71, 0.13);">Не оплачено</td>';
      } else {
      $status2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оплачено</td>';
      }
      if ($type7['original'] == 0) {
      $original = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал акта не получен</td>';
      } else {
      $original = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал акта получен</td>';
      }
      if ($type8['original'] == 0) {
      $original2 = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал счета не получен</td>';
      } else {
      $original2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал счета получен</td>';
      }

      $summ = 157000;
      $content_list['total'] += $summ;
      if ($summ  && $summ != '-') {
      $content_list['body'] .= '<tr>
      <td >'.$year.'.'.$month.'<br><u>Оптима ПППО</u></td>
      <td><a href="/get-detail-report/?service-id='.$type2['service_id'].'&month='.$month.'&year='.$year.'&brand=optima-tp">Отчет</a></td>
      <td><a href="/get-payment-act-optima-pppo/'.$year.'/'.$month.'/optima-tp/">Акт выполненных работ</a></td>
      <td><a href="/get-payment-bill-optima-pppo/'.$year.'/'.$month.'/optima-tp/">Счет на оплату</a></td>';
      if ($add_report == 1) {
      $content_list['body'] .= '<td></td>';
      }
      $content_list['body'] .= '<td>'.$summ.',00 руб.</td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$status.'</tr><tr>'.$status2.'</tr></table></td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$original.'</tr><tr>'.$original2.'</tr></table></td>
      <td align="center" class="linkz"><!--<a class="t-1" href="/get-payment-archive-optima/'.$year.'/'.$month.'/optima-tp/" title="Скачать архивом" ></a>--></td>
      </tr>';
      }

      }

      }


      }

      }

      if ($_COOKIE['notpayed'] == 0) {

      if ($type2['status'] == 0) {

      if ($type2['status'] == 0) {
      $status2 = '<td style="background: rgba(255, 71, 71, 0.13);">Не оплачено</td>';
      } else {
      $status2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оплачено</td>';
      }
      if ($type1['original'] == 0) {
      $original = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал акта не получен</td>';
      } else {
      $original = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал акта получен</td>';
      }
      if ($type2['original'] == 0) {
      $original2 = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал счета не получен</td>';
      } else {
      $original2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал счета получен</td>';
      }
      //$summ = get_service_summ_fast($userID, $month, $year, 'HARPER');

      if (!$type2['sum']) {
      $summ = get_service_summ_fast($userID, $month, $year, 'HARPER', '', true);
      } else {
      $summ = $type2['sum'];
      }


      $content_list['total'] += $summ;
     if ($summ  && $summ != '-') {
      $content_list['body'] .= '<tr data-payment-id="'.$type2['id'].'" data-payment-brand="harper">
      <td >'.$year.'.'.$month.'<br><u>HARPER</u></td>
      <td><a href="/get-detail-report/?service-id='.$type2['service_id'].'&month='.$month.'&year='.$year.'&brand=harper">Отчет</a></td>
      <td><a href="/get-payment-act/'.$year.'/'.$month.'/">Акт выполненных работ</a></td>
      <td><a href="/get-payment-bill/'.$year.'/'.$month.'/">Счет на оплату</a></td>';
      if ($add_report == 1) {
      $content_list['body'] .= '<td><a class="dwn" data-href="/get-agent/'.$year.'/'.$month.'/'.$counter.'/HARPER,OLTO/" href="/get-agent/'.$year.'/'.$month.'/'.$counter.'/HARPER,OLTO/">Отчет</a></td>';
      }
      $content_list['body'] .= '<td><span data-payment-sum="'.$type2['id'].'">'.$summ.'</span>,00 руб.</td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$status.'</tr><tr>'.$status2.'</tr></table></td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$original.'</tr><tr>'.$original2.'</tr></table></td>
      <td align="center" class="linkz"><a class="t-1" href="/get-payment-archive/'.$year.'/'.$month.'/" title="Скачать архивом" ></a></td>
      </tr>';
      }


      if ($tesler == 1) {

      if ($type4['status'] == 0) {
      if ($type4['status'] == 0) {
      $status2 = '<td style="background: rgba(255, 71, 71, 0.13);">Не оплачено</td>';
      } else {
      $status2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оплачено</td>';
      }
      if ($type3['original'] == 0) {
      $original = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал акта не получен</td>';
      } else {
      $original = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал акта получен</td>';
      }
      if ($type4['original'] == 0) {
      $original2 = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал счета не получен</td>';
      } else {
      $original2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал счета получен</td>';
      }


      if (!$type4['sum']) {
      $summ = get_service_summ_fast($userID, $month, $year, 'TESLER', '', true);
      } else {
      $summ = $type4['sum'];
      }

      $content_list['total'] += $summ;
      if ($summ  && $summ != '-') {
      $content_list['body'] .= '<tr data-payment-id="'.$type4['id'].'" data-payment-brand="tesler">
      <td >'.$year.'.'.$month.'<br><u>TESLER</u></td>
      <td><a href="/get-detail-report/?service-id='.$type4['service_id'].'&month='.$month.'&year='.$year.'&brand=tesler">Отчет</a></td>
      <td><a href="/get-payment-act/'.$year.'/'.$month.'/tesler/">Акт выполненных работ</a></td>
      <td><a href="/get-payment-bill/'.$year.'/'.$month.'/tesler/">Счет на оплату</a></td>';
      if ($add_report == 1) {
      $content_list['body'] .= '<td><a class="dwn" data-href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/TESLER/" href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/TESLER/">Отчет</a></td>';
      }
      $content_list['body'] .= '<td><span data-payment-sum="'.$type4['id'].'">'.$summ.'</span>,00 руб.</td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$status.'</tr><tr>'.$status2.'</tr></table></td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$original.'</tr><tr>'.$original2.'</tr></table></td>
      <td align="center" class="linkz"><a class="t-1" href="/get-payment-archive/'.$year.'/'.$month.'/tesler/" title="Скачать архивом" ></a></td>
      </tr>';
      }
      }
      }

      if ($roch == 1) {

        if ($rochBill['status'] == 0) {
        if ($rochBill['status'] == 0) {
        $status2 = '<td style="background: rgba(255, 71, 71, 0.13);">Не оплачено</td>';
        } else {
        $status2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оплачено</td>';
        }
        if ($rochAct['original'] == 0) {
        $original = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал акта не получен</td>';
        } else {
        $original = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал акта получен</td>';
        }
        if ($rochBill['original'] == 0) {
        $original2 = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал счета не получен</td>';
        } else {
        $original2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал счета получен</td>';
        }
  
  
        if (!$rochBill['sum']) {
        $summ = get_service_summ_fast($userID, $month, $year, 'ROCH', '', true);
        } else {
        $summ = $rochBill['sum'];
        }
  
        $content_list['total'] += $summ;
        if ($summ  && $summ != '-') {
        $content_list['body'] .= '<tr data-payment-id="'.$rochBill['id'].'" data-payment-brand="roch">
        <td >'.$year.'.'.$month.'<br><u>ROCH</u></td>
        <td><a href="/get-detail-report/?service-id='.$rochBill['service_id'].'&month='.$month.'&year='.$year.'&brand=roch">Отчет</a></td>
        <td><a href="/get-payment-act/'.$year.'/'.$month.'/roch/">Акт выполненных работ</a></td>
        <td><a href="/get-payment-bill/'.$year.'/'.$month.'/roch/">Счет на оплату</a></td>';
        if ($add_report == 1) {
        $content_list['body'] .= '<td><a class="dwn" data-href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/ROCH/" href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/ROCH/">Отчет</a></td>';
        }
        $content_list['body'] .= '<td><span data-payment-sum="'.$rochBill['id'].'">'.$summ.'</span>,00 руб.</td>
        <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$status.'</tr><tr>'.$status2.'</tr></table></td>
        <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$original.'</tr><tr>'.$original2.'</tr></table></td>
        <td align="center" class="linkz"><a class="t-1" href="/get-payment-archive/'.$year.'/'.$month.'/roch/" title="Скачать архивом" ></a></td>
        </tr>';
        }
        }
        }

      if ($horizont == 1) {

      if ($type12['status'] == 0) {
      if ($type12['status'] == 0) {
      $status2 = '<td style="background: rgba(255, 71, 71, 0.13);">Не оплачено</td>';
      } else {
      $status2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оплачено</td>';
      }
      if ($type11['original'] == 0) {
      $original = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал акта не получен</td>';
      } else {
      $original = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал акта получен</td>';
      }
      if ($type12['original'] == 0) {
      $original2 = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал счета не получен</td>';
      } else {
      $original2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал счета получен</td>';
      }
      //$summ = get_service_summ_fast($userID, $month, $year, 'TESLER');

      if (!$type12['sum']) {
      $summ = get_service_summ_fast($userID, $month, $year, 'TESLER', '', true);
      } else {
      $summ = $type12['sum'];
      }

      $content_list['total'] += $summ;
      if ($summ  && $summ != '-') {
      $content_list['body'] .= '<tr>
      <td >'.$year.'.'.$month.'<br><u>ГОРИЗОНТ-СОЮЗ</u></td>
      <td><a href="/get-detail-report/?service-id='.$type12['service_id'].'&month='.$month.'&year='.$year.'&brand=horizont">Отчет</a></td>
      <td><a href="/get-payment-act/'.$year.'/'.$month.'/horizont/">Акт выполненных работ</a></td>
      <td><a href="/get-payment-bill/'.$year.'/'.$month.'/horizont/">Счет на оплату</a></td>';
      if ($add_report == 1) {
      $content_list['body'] .= '<td><a class="dwn" data-href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/HORIZONT/" href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/HORIZONT/">Отчет</a></td>';
      }
      $content_list['body'] .= '<td>'.$summ.',00 руб.</td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$status.'</tr><tr>'.$status2.'</tr></table></td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$original.'</tr><tr>'.$original2.'</tr></table></td>
      <td align="center" class="linkz"><a class="t-1" href="/get-payment-archive/'.$year.'/'.$month.'/horizont/" title="Скачать архивом" ></a></td>
      </tr>';
      }
      }
      }

      if ($selenga == 1) {

      if ($type14['status'] == 1) {
      if ($type14['status'] == 0) {
      $status2 = '<td style="background: rgba(255, 71, 71, 0.13);">Не оплачено</td>';
      } else {
      $status2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оплачено</td>';
      }
      if ($type13['original'] == 0) {
      $original = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал акта не получен</td>';
      } else {
      $original = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал акта получен</td>';
      }
      if ($type14['original'] == 0) {
      $original2 = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал счета не получен</td>';
      } else {
      $original2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал счета получен</td>';
      }
      //$summ = get_service_summ_fast($userID, $month, $year, 'TESLER');

      if (!$type14['sum']) {
      $summ = get_service_summ_fast($userID, $month, $year, 'SELENGA', '', true);
      } else {
      $summ = $type14['sum'];
      }

      $content_list['total'] += $summ;
      if ($summ  && $summ != '-') {
      $content_list['body'] .= '<tr data-payment-id="'.$type14['id'].'" data-payment-brand="selenga-brand">
      <td >'.$year.'.'.$month.'<br><u>SELENGA</u></td>
      <td><a href="/get-detail-report/?service-id='.$type14['service_id'].'&month='.$month.'&year='.$year.'&brand=selenga-brand">Отчет</a></td>
      <td><a href="/get-payment-act/'.$year.'/'.$month.'/selenga-brand/">Акт выполненных работ</a></td>
      <td><a href="/get-payment-bill/'.$year.'/'.$month.'/selenga-brand/">Счет на оплату</a></td>';
      if ($add_report == 1) {
      $content_list['body'] .= '<td><a class="dwn" data-href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/SELENGA/" href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/SELENGA/">Отчет</a></td>';
      }
      $content_list['body'] .= '<td><span data-payment-sum="'.$type14['id'].'">'.$summ.'</span>,00 руб.</td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$status.'</tr><tr>'.$status2.'</tr></table></td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$original.'</tr><tr>'.$original2.'</tr></table></td>
      <td align="center" class="linkz"><a class="t-1" href="/get-payment-archive/'.$year.'/'.$month.'/selenga/" title="Скачать архивом" ></a></td>
      </tr>';
      }
      }
      }

      if ($sven == 1  &&  $date_current_sven >= $date_from_sven) {

      if ($type10['status'] == 0) {
      if ($type10['status'] == 0) {
      $status2 = '<td style="background: rgba(255, 71, 71, 0.13);">Не оплачено</td>';
      } else {
      $status2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оплачено</td>';
      }
      if ($type9['original'] == 0) {
      $original = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал акта не получен</td>';
      } else {
      $original = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал акта получен</td>';
      }
      if ($type10['original'] == 0) {
      $original2 = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал счета не получен</td>';
      } else {
      $original2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал счета получен</td>';
      }


      if (!$type10['sum']) {
      $summ = get_service_summ_fast($userID, $month, $year, 'SVEN', '', true);
      } else {
      $summ = $type10['sum'];
      }

      $content_list['total'] += $summ;
      if ($summ  && $summ != '-') {
      $content_list['body'] .= '<tr data-payment-id="'.$type10['id'].'" data-payment-brand="sven">
      <td >'.$year.'.'.$month.'<br><u>SVEN</u></td>
      <td><a href="/get-detail-report/?service-id='.$type10['service_id'].'&month='.$month.'&year='.$year.'&brand=sven">Отчет</a></td>
      <td><a href="/get-payment-act/'.$year.'/'.$month.'/sven/">Акт выполненных работ</a></td>
      <td><a href="/get-payment-bill/'.$year.'/'.$month.'/sven/">Счет на оплату</a></td>';
      if ($add_report == 1) {
      $content_list['body'] .= '<td><a class="dwn" data-href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/sven/" href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/sven/">Отчет</a></td>';
      }
      $content_list['body'] .= '<td><span data-payment-sum="'.$type10['id'].'">'.$summ.'</span>,00 руб.</td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$status.'</tr><tr>'.$status2.'</tr></table></td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$original.'</tr><tr>'.$original2.'</tr></table></td>
      <td align="center" class="linkz"><a class="t-1" href="/get-payment-archive/'.$year.'/'.$month.'/sven/" title="Скачать архивом" ></a></td>
      </tr>';
      }
      }
      }

      if ($userID == 33) {

      if (date("Y-n-j", strtotime($year.'-'.$month.'-1')) >= date("Y-n-j", strtotime('2018-2-1'))  && date("Y-n", strtotime($year.'-'.$month)) < date("Y-n")) {

      $type5 = create_or_get_payment_id($userID, $year, $month, 5); //счет
      $type6 = create_or_get_payment_id($userID, $year, $month, 6); //счет
      if ($type6['status'] == 0) {
      $status2 = '<td style="background: rgba(255, 71, 71, 0.13);">Не оплачено</td>';
      } else {
      $status2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оплачено</td>';
      }
      if ($type5['original'] == 0) {
      $original = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал акта не получен</td>';
      } else {
      $original = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал акта получен</td>';
      }
      if ($type6['original'] == 0) {
      $original2 = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал счета не получен</td>';
      } else {
      $original2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал счета получен</td>';
      }

      $summ = 120000;
      $content_list['total'] += $summ;
      if ($summ  && $summ != '-') {
      $content_list['body'] .= '<tr>
      <td >'.$year.'.'.$month.'<br><u>Оптима ТП</u></td>
      <td><a href="/get-detail-report/?service-id='.$type5['service_id'].'&month='.$month.'&year='.$year.'&brand=optima-tp">Отчет</a></td>
      <td><a href="/get-payment-act-optima/'.$year.'/'.$month.'/optima-tp/">Акт выполненных работ</a></td>
      <td><a href="/get-payment-bill-optima/'.$year.'/'.$month.'/optima-tp/">Счет на оплату</a></td>';
      if ($add_report == 1) {
      $content_list['body'] .= '<td></td>';
      }
      $content_list['body'] .= '<td>'.$summ.',00 руб.</td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$status.'</tr><tr>'.$status2.'</tr></table></td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$original.'</tr><tr>'.$original2.'</tr></table></td>
      <td align="center" class="linkz"><!--<a class="t-1" href="/get-payment-archive-optima/'.$year.'/'.$month.'/optima-tp/" title="Скачать архивом" ></a>--></td>
      </tr>';
      }

      }

      if (date("Y-n-j", strtotime($year.'-'.$month.'-1')) > date("Y-n-j", strtotime('2018-4-1')) && date("Y-n", strtotime($year.'-'.$month)) < date("Y-n")) {

      $type7 = create_or_get_payment_id($userID, $year, $month, 7); //счет
      $type8 = create_or_get_payment_id($userID, $year, $month, 8); //счет
      if ($type8['status'] == 0) {
      $status2 = '<td style="background: rgba(255, 71, 71, 0.13);">Не оплачено</td>';
      } else {
      $status2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оплачено</td>';
      }
      if ($type7['original'] == 0) {
      $original = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал акта не получен</td>';
      } else {
      $original = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал акта получен</td>';
      }
      if ($type8['original'] == 0) {
      $original2 = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал счета не получен</td>';
      } else {
      $original2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал счета получен</td>';
      }

      $summ = 157000;
      $content_list['total'] += $summ;
      if ($summ  && $summ != '-') {
      $content_list['body'] .= '<tr>
      <td >'.$year.'.'.$month.'<br><u>Оптима ПППО</u></td>
      <td><a href="/get-detail-report/?service-id='.$type7['service_id'].'&month='.$month.'&year='.$year.'&brand=optima-tp">Отчет</a></td>
      <td><a href="/get-payment-act-optima-pppo/'.$year.'/'.$month.'/optima-tp/">Акт выполненных работ</a></td>
      <td><a href="/get-payment-bill-optima-pppo/'.$year.'/'.$month.'/optima-tp/">Счет на оплату</a></td>';
      if ($add_report == 1) {
      $content_list['body'] .= '<td></td>';
      }
      $content_list['body'] .= '<td>'.$summ.',00 руб.</td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$status.'</tr><tr>'.$status2.'</tr></table></td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$original.'</tr><tr>'.$original2.'</tr></table></td>
      <td align="center" class="linkz"><!--<a class="t-1" href="/get-payment-archive-optima/'.$year.'/'.$month.'/optima-tp/" title="Скачать архивом" ></a>--></td>
      </tr>';
      }

      }

      }


      }

      }

if ($_COOKIE['notpayed'] != 0 && $_COOKIE['payed'] != 0) {


      if ($type2['status'] == 0) {
      $status2 = '<td style="background: rgba(255, 71, 71, 0.13);">Не оплачено</td>';
      } else {
      $status2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оплачено</td>';
      }
      if ($type1['original'] == 0) {
      $original = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал акта не получен</td>';
      } else {
      $original = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал акта получен</td>';
      }
      if ($type2['original'] == 0) {
      $original2 = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал счета не получен</td>';
      } else {
      $original2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал счета получен</td>';
      }


      if (!$type2['sum']) {
      $summ = get_service_summ_fast($userID, $month, $year, 'HARPER', '', true);
      } else {
      $summ = $type2['sum'];
      }
      $content_list['total'] += $summ;



      if ($summ  && $summ != '-') {
      $content_list['body'] .= '<tr data-payment-id="'.$type2['id'].'" data-payment-brand="harper">
      <td >'.$year.'.'.$month.'<br><u>HARPER</u></td>
      <td><a href="/get-detail-report/?service-id='.$type2['service_id'].'&month='.$month.'&year='.$year.'&brand=harper">Отчет</a></td>
      <td><a href="/get-payment-act/'.$year.'/'.$month.'/">Акт выполненных работ</a></td>
      <td><a href="/get-payment-bill/'.$year.'/'.$month.'/">Счет на оплату</a></td>';
      if ($add_report == 1) {
      $content_list['body'] .= '<td><a class="dwn" data-href="/get-agent/'.$year.'/'.$month.'/'.$counter.'/HARPER,OLTO/" href="/get-agent/'.$year.'/'.$month.'/'.$counter.'/HARPER,OLTO/">Отчет</a></td>';
      }
      $content_list['body'] .= '<td><span data-payment-sum="'.$type2['id'].'">'.$summ.'</span>,00 руб.</td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$status.'</tr><tr>'.$status2.'</tr></table></td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$original.'</tr><tr>'.$original2.'</tr></table></td>
      <td align="center" class="linkz"><a class="t-1" href="/get-payment-archive/'.$year.'/'.$month.'/" title="Скачать архивом" ></a></td>
      </tr>';
      }


      if ($tesler == 1) {

		if ($type4['id'] != 34564) {
      if ($type4['status'] == 0) {
      $status2 = '<td style="background: rgba(255, 71, 71, 0.13);">Не оплачено</td>';
      } else {
      $status2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оплачено</td>';
      }
      if ($type3['original'] == 0) {
      $original = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал акта не получен</td>';
      } else {
      $original = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал акта получен</td>';
      }
      if ($type4['original'] == 0) {
      $original2 = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал счета не получен</td>';
      } else {
      $original2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал счета получен</td>';
      }


      if (!$type4['sum']) {
      $summ = get_service_summ_fast($userID, $month, $year, 'TESLER', '', true);
      } else {
      $summ = $type4['sum'];
            }

      $content_list['total'] += $summ;



      if ($summ && $summ != '-') {
	
      $content_list['body'] .= '<tr data-payment-id="'.$type4['id'].'" data-payment-brand="tesler">
      <td >'.$year.'.'.$month.'<br><u>TESLER</u></td>
      <td><a href="/get-detail-report/?service-id='.$type4['service_id'].'&month='.$month.'&year='.$year.'&brand=tesler">Отчет</a></td>
      <td><a href="/get-payment-act/'.$year.'/'.$month.'/tesler/">Акт выполненных работ</a></td>
      <td><a href="/get-payment-bill/'.$year.'/'.$month.'/tesler/">Счет на оплату</a></td>';
      if ($add_report == 1) {
      $content_list['body'] .= '<td><a class="dwn" data-href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/TESLER/" href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/TESLER/">Отчет</a></td>';
      }
      $content_list['body'] .= '<td><span data-payment-sum="'.$type4['id'].'">'.$summ.'</span>,00 руб.</td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$status.'</tr><tr>'.$status2.'</tr></table></td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$original.'</tr><tr>'.$original2.'</tr></table></td>
      <td align="center" class="linkz"><a class="t-1" href="/get-payment-archive/'.$year.'/'.$month.'/tesler/" title="Скачать архивом" ></a></td>
      </tr>';
      }

      }

      if ($roch == 1) {


        if ($rochBill['status'] == 0) {
        $status2 = '<td style="background: rgba(255, 71, 71, 0.13);">Не оплачено</td>';
        } else {
        $status2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оплачено</td>';
        }
        if ($rochAct['original'] == 0) {
        $original = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал акта не получен</td>';
        } else {
        $original = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал акта получен</td>';
        }
        if ($rochBill['original'] == 0) {
        $original2 = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал счета не получен</td>';
        } else {
        $original2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал счета получен</td>';
        }
  
  
        if (!$rochBill['sum']) {
        $summ = get_service_summ_fast($userID, $month, $year, 'ROCH', '', true);
        } else {
        $summ = $rochBill['sum'];
              }
  
        $content_list['total'] += $summ;
  
  
  
        if ($summ && $summ != '-') {
  
        $content_list['body'] .= '<tr data-payment-id="'.$rochBill['id'].'" data-payment-brand="roch">
        <td >'.$year.'.'.$month.'<br><u>ROCH</u></td>
        <td><a href="/get-detail-report/?service-id='.$rochBill['service_id'].'&month='.$month.'&year='.$year.'&brand=roch">Отчет</a></td>
        <td><a href="/get-payment-act/'.$year.'/'.$month.'/roch/">Акт выполненных работ</a></td>
        <td><a href="/get-payment-bill/'.$year.'/'.$month.'/roch/">Счет на оплату</a></td>';
        if ($add_report == 1) {
        $content_list['body'] .= '<td><a class="dwn" data-href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/ROCH/" href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/ROCH/">Отчет</a></td>';
        }
        $content_list['body'] .= '<td><span data-payment-sum="'.$rochBill['id'].'">'.$summ.'</span>,00 руб.</td>
        <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$status.'</tr><tr>'.$status2.'</tr></table></td>
        <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$original.'</tr><tr>'.$original2.'</tr></table></td>
        <td align="center" class="linkz"><a class="t-1" href="/get-payment-archive/'.$year.'/'.$month.'/roch/" title="Скачать архивом" ></a></td>
        </tr>';
        }
  
        }

      if ($horizont == 1) {

      if ($type12['status'] == 0) {
      $status2 = '<td style="background: rgba(255, 71, 71, 0.13);">Не оплачено</td>';
      } else {
      $status2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оплачено</td>';
      }
      if ($type11['original'] == 0) {
      $original = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал акта не получен</td>';
      } else {
      $original = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал акта получен</td>';
      }
      if ($type12['original'] == 0) {
      $original2 = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал счета не получен</td>';
      } else {
      $original2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал счета получен</td>';
      }


      if (!$type12['sum']) {
      $summ = get_service_summ_fast($userID, $month, $year, 'TESLER', '', true);
      } else {
      $summ = $type12['sum'];
            }

      $content_list['total'] += $summ;



      if ($summ && $summ != '-') {

      $content_list['body'] .= '<tr>
      <td >'.$year.'.'.$month.'<br><u>ГОРИЗОНТ-СОЮЗ</u></td>
      <td><a href="/get-detail-report/?service-id='.$type12['service_id'].'&month='.$month.'&year='.$year.'&brand=horizont">Отчет</a></td>
      <td><a href="/get-payment-act/'.$year.'/'.$month.'/horizont/">Акт выполненных работ</a></td>
      <td><a href="/get-payment-bill/'.$year.'/'.$month.'/horizont/">Счет на оплату</a></td>';
      if ($add_report == 1) {
      $content_list['body'] .= '<td><a class="dwn" data-href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/HORIZONT/" href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/HORIZONT/">Отчет</a></td>';
      }
      $content_list['body'] .= '<td>'.$summ.',00 руб.</td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$status.'</tr><tr>'.$status2.'</tr></table></td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$original.'</tr><tr>'.$original2.'</tr></table></td>
      <td align="center" class="linkz"><a class="t-1" href="/get-payment-archive/'.$year.'/'.$month.'/horizont/" title="Скачать архивом" ></a></td>
      </tr>';
      }
      }

      if ($selenga == 1) {

      if ($type14['status'] == 0) {
      $status2 = '<td style="background: rgba(255, 71, 71, 0.13);">Не оплачено</td>';
      } else {
      $status2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оплачено</td>';
      }
      if ($type13['original'] == 0) {
      $original = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал акта не получен</td>';
      } else {
      $original = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал акта получен</td>';
      }
      if ($type14['original'] == 0) {
      $original2 = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал счета не получен</td>';
      } else {
      $original2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал счета получен</td>';
      }
      //$summ = get_service_summ_fast($userID, $month, $year, 'TESLER');

      if (!$type14['sum']) {
      $summ = get_service_summ_fast($userID, $month, $year, 'SELENGA', '', true);
      } else {
      $summ = $type14['sum'];
      }

      $content_list['total'] += $summ;
      if ($summ  && $summ != '-') {
      $content_list['body'] .= '<tr data-payment-id="'.$type14['id'].'" data-payment-brand="SELENGA-brand">
      <td >'.$year.'.'.$month.'<br><u>SELENGA</u></td>
      <td><a href="/get-detail-report/?service-id='.$type14['service_id'].'&month='.$month.'&year='.$year.'&brand=SELENGA-brand">Отчет</a></td>
      <td><a href="/get-payment-act/'.$year.'/'.$month.'/SELENGA-brand/">Акт выполненных работ</a></td>
      <td><a href="/get-payment-bill/'.$year.'/'.$month.'/SELENGA-brand/">Счет на оплату</a></td>';
      if ($add_report == 1) {
      $content_list['body'] .= '<td><a class="dwn" data-href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/SELENGA/" href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/SELENGA/">Отчет</a></td>';
      }
      $content_list['body'] .= '<td><span data-payment-sum="'.$type14['id'].'">'.$summ.'</span>,00 руб.</td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$status.'</tr><tr>'.$status2.'</tr></table></td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$original.'</tr><tr>'.$original2.'</tr></table></td>
      <td align="center" class="linkz"><a class="t-1" href="/get-payment-archive/'.$year.'/'.$month.'/selenga/" title="Скачать архивом" ></a></td>
      </tr>';
      }

      }

      if ($sven== 1  && $date_current_sven >= $date_from_sven) {


      if ($type10['status'] == 0) {
      $status2 = '<td style="background: rgba(255, 71, 71, 0.13);">Не оплачено</td>';
      } else {
      $status2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оплачено</td>';
      }
      if ($type9['original'] == 0) {
      $original = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал акта не получен</td>';
      } else {
      $original = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал акта получен</td>';
      }
      if ($type10['original'] == 0) {
      $original2 = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал счета не получен</td>';
      } else {
      $original2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал счета получен</td>';
      }


      if (!$type10['sum']) {
      $summ = get_service_summ_fast($userID, $month, $year, 'SVEN', '', true);
      } else {
      $summ = $type10['sum'];
            }

      $content_list['total'] += $summ;



      if ($summ && $summ != '-') {

      $content_list['body'] .= '<tr data-payment-id="'.$type10['id'].'" data-payment-brand="sven">
      <td >'.$year.'.'.$month.'<br><u>SVEN</u></td>
      <td><a href="/get-detail-report/?service-id='.$type10['service_id'].'&month='.$month.'&year='.$year.'&brand=sven">Отчет</a></td>
      <td><a href="/get-payment-act/'.$year.'/'.$month.'/sven/">Акт выполненных работ</a></td>
      <td><a href="/get-payment-bill/'.$year.'/'.$month.'/sven/">Счет на оплату</a></td>';
      if ($add_report == 1) {
      $content_list['body'] .= '<td><a class="dwn" data-href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/sven/" href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/sven/">Отчет</a></td>';
      }
      $content_list['body'] .= '<td><span data-payment-sum="'.$type10['id'].'">'.$summ.'</span>,00 руб.</td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$status.'</tr><tr>'.$status2.'</tr></table></td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$original.'</tr><tr>'.$original2.'</tr></table></td>
      <td align="center" class="linkz"><a class="t-1" href="/get-payment-archive/'.$year.'/'.$month.'/sven/" title="Скачать архивом" ></a></td>
      </tr>';
      }

      }

      if ($userID == 33) {

      if (date("Y-n-j", strtotime($year.'-'.$month.'-1')) >= date("Y-n-j", strtotime('2018-2-1'))  && date("Y-n", strtotime($year.'-'.$month)) < date("Y-n")) {

      $type5 = create_or_get_payment_id($userID, $year, $month, 5); //счет
      $type6 = create_or_get_payment_id($userID, $year, $month, 6); //счет

      if ($type6['status'] == 0) {
      $status2 = '<td style="background: rgba(255, 71, 71, 0.13);">Не оплачено</td>';
      } else {
      $status2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оплачено</td>';
      }
      if ($type5['original'] == 0) {
      $original = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал акта не получен</td>';
      } else {
      $original = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал акта получен</td>';
      }
      if ($type6['original'] == 0) {
      $original2 = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал счета не получен</td>';
      } else {
      $original2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал счета получен</td>';
      }

      $summ = 120000;
      $content_list['total'] += $summ;
      if ($summ  && $summ != '-') {
      $content_list['body'] .= '<tr>
      <td >'.$year.'.'.$month.'<br><u>Оптима ТП</u></td>
      <td><a href="/get-detail-report/?service-id='.$type5['service_id'].'&month='.$month.'&year='.$year.'&optima-tp">Отчет</a></td>
      <td><a href="/get-payment-act-optima/'.$year.'/'.$month.'/optima-tp/">Акт выполненных работ</a></td>
      <td><a href="/get-payment-bill-optima/'.$year.'/'.$month.'/optima-tp/">Счет на оплату</a></td>';
      if ($add_report == 1) {
      $content_list['body'] .= '<td></td>';
      }
      $content_list['body'] .= '<td>'.$summ.',00 руб.</td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$status.'</tr><tr>'.$status2.'</tr></table></td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$original.'</tr><tr>'.$original2.'</tr></table></td>
      <td align="center" class="linkz"><!--<a class="t-1" href="/get-payment-archive-optima/'.$year.'/'.$month.'/optima-tp/" title="Скачать архивом" ></a>--></td>
      </tr>';
      }

      }

      if (date("Y-n-j", strtotime($year.'-'.$month.'-1')) > date("Y-n-j", strtotime('2018-4-1')) && date("Y-n", strtotime($year.'-'.$month)) < date("Y-n")) {

      $type7 = create_or_get_payment_id($userID, $year, $month, 7); //счет
      $type8 = create_or_get_payment_id($userID, $year, $month, 8); //счет

      if ($type8['status'] == 0) {
      $status2 = '<td style="background: rgba(255, 71, 71, 0.13);">Не оплачено</td>';
      } else {
      $status2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оплачено</td>';
      }
      if ($type7['original'] == 0) {
      $original = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал акта не получен</td>';
      } else {
      $original = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал акта получен</td>';
      }
      if ($type8['original'] == 0) {
      $original2 = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал счета не получен</td>';
      } else {
      $original2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал счета получен</td>';
      }

      $summ = 157000;
      $content_list['total'] += $summ;
      if ($summ  && $summ != '-') {
      $content_list['body'] .= '<tr>
      <td >'.$year.'.'.$month.'<br><u>Оптима ПППО</u></td>
      <td><a href="/get-detail-report/?service-id='.$type7['service_id'].'&month='.$month.'&year='.$year.'&optima-tp">Отчет</a></td>
      <td><a href="/get-payment-act-optima-pppo/'.$year.'/'.$month.'/optima-tp/">Акт выполненных работ</a></td>
      <td><a href="/get-payment-bill-optima-pppo/'.$year.'/'.$month.'/optima-tp/">Счет на оплату</a></td>';
      if ($add_report == 1) {
      $content_list['body'] .= '<td></td>';
      }
      $content_list['body'] .= '<td>'.$summ.',00 руб.</td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$status.'</tr><tr>'.$status2.'</tr></table></td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$original.'</tr><tr>'.$original2.'</tr></table></td>
      <td align="center" class="linkz"><!--<a class="t-1" href="/get-payment-archive-optima/'.$year.'/'.$month.'/optima-tp/" title="Скачать архивом" ></a>--></td>
      </tr>';
      }

      }

      }

	  }
      }
      unset($summ);
      unset($type1);
      unset($type2);
      unset($type3);
      unset($type4);
      unset($type5);
      unset($type6);
      unset($type7);
      unset($type8);
      unset($type9);
      unset($type10);
      unset($type11);
      unset($type12);
      $counter--;

      }

     }
     //}

    return $content_list;
}



$content = content_list();

?>
<!doctype html>
<html>
<head>
<meta charset=utf-8>
<title><?php if ($userID == 2) { echo 'Счета и Акты прямые'; } else { echo 'Платежные документы'; } ?></title>
<link href="/css/fonts.css" rel="stylesheet" />
<link href="/css/style.css" rel="stylesheet" />
<script src="/_new-codebase/front/vendor/jquery/jquery-1.7.2.min.js"></script>
<script src="/js/jquery-ui.min.js"></script>
<script src="/js/jquery.placeholder.min.js"></script>
<script src="/js/jquery.formstyler.min.js"></script>
<script src="/js/main.js"></script>

<script src="/notifier/js/index.js"></script>
<link rel="stylesheet" type="text/css" href="/notifier/css/style.css">
<link rel="stylesheet" href="/_new-codebase/front/vendor/animate.min.css" />
<script src='/_new-codebase/front/vendor/mustache/mustache.min.js'></script>
<script src="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.concat.min.js"></script>
<link rel="stylesheet" href="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.min.css" />

<script src="/_new-codebase/front/vendor/datatables/js/data-tables.min.js"></script>
<link rel="stylesheet" type="text/css" href="/css/datatables.css">
<style>
.nohov tr:hover {
    background-color: transparent !important;
    box-shadow: initial !important;
}

</style>
<script >
// Таблица
$(document).ready(function() {
    $('#table_content').dataTable({
      stateSave:true,
      "dom": '<"top"flp<"clear">>rt<"bottom"ip<"clear">>',
      "pageLength": <?=$config['page_limit'];?>,
      "order": [[ 0, "desc" ]],
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

    $(document).on('change', 'input[name="payed"],input[name="notpayed"],input[name="tesler"]', function() {
        var form = $(this).parent().parent().parent().parent();
        $('#checkb').submit();
    });

} );

</script>
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
           <h2><?php if ($userID == 2) { echo 'Счета и Акты прямые'; } else { echo 'Платежные документы'; } ?></h2>
           <br>
  <div class="adm-catalog">
         <div style="vertical-align:middle;">

         <?php if ($userID == 33) { ?>
           <div class="add" style="padding-top:0px;display:inline-block;">
     <a style="width: auto;padding-left: 7px;padding-right: 7px;vertical-align: middle;" href="/refresh-sc/" class="button">Обновить данные (t~1m)</a>
    </div>
        <? } ?>

    <form method="POST" id="checkb">
    <table style="">
              <tr>
    <td style="padding: 20px; ">Неоплаченные <input type="checkbox" value="1" name="payed" <?=($_COOKIE['payed'] == '1') ? 'checked' : '';?>> &nbsp;&nbsp;&nbsp;&nbsp; Оплаченные <input type="checkbox" value="1" name="notpayed" <?=($_COOKIE['notpayed'] == 1 ) ? 'checked' : '';?> >&nbsp;&nbsp;&nbsp;&nbsp;<!-- TESLER <input type="checkbox" value="1" name="tesler" <?=($_COOKIE['tesler'] == 1 ) ? 'checked' : '';?> >--><br></td>
    </tr>

            </table>
        </form>
    </div>

  <table id="table_content" class="display" cellspacing="0" width="100%" style="font-size:16px;">
        <thead>
            <tr>
                <th align="left">Период</th>
                <th align="left">Отчет</th>
                <th align="left">Акт выполненных работ</th>
                <th align="left">Счет на оплату</th>
                <?php if ($add_report == 1) { echo '<th align="left">Отчет</th>'; }?>
                <th align="center">Сумма</th>
                <th align="center">Оплата</th>
                <th align="center">Получение оригиналов</th>
                <th>Скачать</th>
            </tr>
        </thead>

        <tbody>
        <?=$content['body'];?>
        </tbody>
</table>

<?=($content['total']) ? '<div>Итого: <strong>'.$content['total'].',00 руб</strong></div> ' : '';?>

</div>


        </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
  let processed = {};
$('body').on('mouseenter', '[data-payment-id]', function(){
  let $this = $(this),
      curID = $this.data('payment-id');
  if(processed[curID] != undefined || !curID){
    return;
  }
  processed[curID] = true;
  $.ajax({
            type: 'GET',
            url: document.location.href,
            data:  'ajax=update-payment-sum&payment-id=' + curID + '&payment-brand=' + $this.data('payment-brand'),
            cache: false,
            dataType: 'json',
            success: function(resp){
              if(!resp.sum){
                return;
              }
              document.querySelector('[data-payment-sum="'+curID+'"]').innerHTML = resp.sum;
            },
            error: function(jqXHR) {
                console.log('Ошибка сервера');
                console.log(jqXHR.responseText);
            }
            });
});
});
</script>
</body>
</html>