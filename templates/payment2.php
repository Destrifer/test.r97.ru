<?php

use models\Services;
use models\User;
use program\core;
use program\core\Time;

require '_new-codebase/front/templates/main/parts/common.php';

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

function generate_custom_doc($year, $month) {
    global $db;

$sql = mysqli_query($db, 'SELECT * FROM `manual_docs` where `year` = '.$year.' and month = '.$month.';');

while ($row = mysqli_fetch_array($sql)) {


       $summ = $row['sum'];

      $content_list['total'] += $summ;

      if ($summ && $summ != '-') {

      $content_list['body'] .= '<tr>
      <td >'.$year.'.'.$month.'<br><u>'.$row['agent'].'</u></td>
      <td></td><td></td><td></td>';
      $content_list['body'] .= '<td>'.$summ.',00 руб.</td>';

      $content_list['body'] .= '<td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay_custom" data-pay-id="'.$row['id'].'">
      <option value="0" '.(($row['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($row['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>';
      $content_list['body'] .= '<td><table style="    margin: 0 auto;" class="nohov">
      <tr>
      <td>
      <form method="POST">
      <select  name="status_act_custom" data-pay-id="'.$row['id'].'">
      <option value="0" '.(($row['original_bill'] == 0) ? 'selected' : '').'>Оригинал акта не получен</option>
      <option value="1" '.(($row['original_bill'] == 1) ? 'selected' : '').'>Оригинал акта получен</option>
      </select>
      </form>
      </td>
      </tr>
      <tr>
      <td>
      <form method="POST">
      <select  name="status_bill_custom" data-pay-id="'.$row['id'].'">
      <option value="0" '.(($row['original_bill'] == 0) ? 'selected' : '').'>Оригинал счета не получен</option>
      <option value="1" '.(($row['original_bill'] == 1) ? 'selected' : '').'>Оригинал счета получен</option>
      </select>
      </form>
      </td>
      </tr></table></td>';

      $content_list['body'] .= '<td align="center" class="linkz">удл. ред.<!--<a class="t-1" href="/get-payment-archive/'.$year.'/'.$month.'/tesler/" title="Скачать архивом" ></a>--></td>
      </tr>';
      }

}

return $content_list['body'];

}

function get_request_info($id) {
  global $db;
return mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `requests` WHERE `user_id` = '.$id));
}

if (\models\User::getData('id') == 2) {
$add_report = 1;
}

function content_list() {
  global $db;
  $qrt = $_POST['qrt'] ?? 1;
  $year = $_POST['year'] ?? date('Y');
$horizont_array = array('ЗЭБТ-Harper', 'Horizont', 'Hartens', 'ЗЭБТ-Горизонт', 'Белит-Горизонт', 'OK', 'ЗЭБТ-HARTENS', 'ЗЭБТ-Skyworth', 'ЗЭБТ-Prestigio', 'ROSENLEW');

$content_list = ['body' => '', 'dates_interval' => '', 'pagination_html' => ''];

$dates = getDatesIntervalByQuarter($qrt, $year);
$content_list['dates_interval'] = '';
  if($dates){
    $content_list['dates_interval'] = core\Time::formatVerbose($dates['from']) . ' - ' . core\Time::formatVerbose($dates['to']);
  }

//$dated = ((date("d") < 5) ? date("Y.m", strtotime("-1 months")) : date("Y.m"));
$counter = 17;
$userID = User::getData('id');
if ($userID == 2) {
$userID = 33;
$add_report = 1;
}
if(!empty($_POST['service_id'])){
  $userID = $_POST['service_id'];
}

//if ($userID != 33) {
$dated = ((date("d") < 5) ? date("Y.m") : date("Y.m"));
$dated_sql = 'and  `app_date` NOT REGEXP \''.$dated.'\'';
//}

$sql = mysqli_query($db, 'SELECT `app_date` 
FROM `repairs` 
WHERE `service_id` = '.$userID.'  
'.(($dates) ? ' AND (`approve_date` BETWEEN "'.$dates['from'].'" AND "'.$dates['to'].'")' : '').'  
 AND `deleted` = 0 and `status_admin` IN ("Подтвержден", "Выдан") '.$dated_sql.' group by `app_date` order by `id` DESC');

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
      foreach ($year_work as $month => $_v) {

      $type1 = create_or_get_payment_id($userID, $year, $month, 1); //акт
      $type2 = create_or_get_payment_id($userID, $year, $month, 2); //счет
     //print_r($type1);

      /*Проверяем теслер*/
      $sql3 = mysqli_query($db, 'SELECT `model_id` FROM `repairs` where `service_id` = '.$userID.' and `app_date` REGEXP \''.$year.'.'.$month.'\' and (`status_admin` = \'Подтвержден\' or `status_admin` = \'Выдан\') and `deleted` = 0 '.$dated_sql.' group by `model_id` order by `id` DESC ;');
      while ($row3 = mysqli_fetch_array($sql3)) {
      $model = model_info($row3['model_id']);

      if ($model['brand'] == 'TESLER') {
        $tesler = 1;
      }

      if ($model['brand'] == 'ROCH') {
        $roch = 1;
      }

      if ($model['brand'] == 'SVEN') {
        $sven = 1;
      }


      if (in_array($model['brand'], $horizont_array)) {
        $horizont = 1;
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

      if ($roch == 1) {
        $rochAct = create_or_get_payment_id($userID, $year, $month, 15); //акт
        $rochBill = create_or_get_payment_id($userID, $year, $month, 16); //счет
        }


/*       $type15 = create_or_get_payment_id($userID, $year, $month, 15); //акт
      $type16 = create_or_get_payment_id($userID, $year, $month, 16); //счет
 */


      /*/Проверка теслера*/


      if ($_COOKIE['payed'] == 1) {

      if ($type2['status'] == 1) {


      if (!$type2['sum']) {
     $summ = get_service_summ_fast($userID, $month, $year, 'HARPER');
     // here!
      } else {
      if (date("Y-m-j", strtotime($year.'-'.$month.'-1')) >= date("Y-m-j", strtotime('2019-10-1'))) {
      $summ = $type2['sum']-get_service_summ_without_payed($userID, $month, $year, 'HARPER');
      } else {
      $summ = $type2['sum'];
      }
      }

      $content_list['total'] += $summ;
      if ($summ  && $summ != '-') {
      $content_list['body'] .= '<tr>
      <td >'.$year.'.'.$month.'<br><u>HARPER</u></td>
      <td><a href="/get-detail-report/?service-id='.$type2['service_id'].'&month='.$month.'&year='.$year.'&brand=harper">Д. отчет</a></td>
      <td><a href="/get-payment-act/'.$year.'/'.$month.'/">Акт выполненных работ</a></td>
      <td><a href="/get-payment-bill/'.$year.'/'.$month.'/">Счет на оплату</a></td>';
      if ($add_report == 1) {
      $content_list['body'] .= '<td><a class="dwn" data-href="/get-agent/'.$year.'/'.$month.'/'.$counter.'/'.((date("Y-m-j", strtotime($year.'-'.$month.'-1')) >= date("Y-m-j", strtotime('2019-10-1'))) ? 'HARPER,OLTO,SKYLINE' : 'HARPER,OLTO').'/" href="/get-agent/'.$year.'/'.$month.'/'.$counter.'/'.((date("Y-m-j", strtotime($year.'-'.$month.'-1')) >= date("Y-m-j", strtotime('2019-10-1'))) ? 'HARPER,OLTO,SKYLINE' : 'HARPER,OLTO').'/">К. отчет2</a></td>';
      }
      $content_list['body'] .= '<td>'.$summ.',00 руб.</td>';
      $content_list['body'] .= '<td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type2['id'].'">
      <option value="0" '.(($type2['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type2['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>';
      $content_list['body'] .= '<td><table style="    margin: 0 auto;" class="nohov">
      <tr>
      <td>
      <form method="POST">
      <select  name="status_act" data-pay-id="'.$type1['id'].'">
      <option value="0" '.(($type1['original'] == 0) ? 'selected' : '').'>Оригинал акта не получен</option>
      <option value="1" '.(($type1['original'] == 1) ? 'selected' : '').'>Оригинал акта получен</option>
      </select>
      </form>
      </td>
      </tr>
      <tr>
      <td>
      <form method="POST">
      <select  name="status_bill" data-pay-id="'.$type2['id'].'">
      <option value="0" '.(($type2['original'] == 0) ? 'selected' : '').'>Оригинал счета не получен</option>
      <option value="1" '.(($type2['original'] == 1) ? 'selected' : '').'>Оригинал счета получен</option>
      </select>
      </form>
      </td>
      </tr></table></td>';
      $content_list['body'] .= '<td align="center" class="linkz"><a class="t-1" href="/get-payment-archive/'.$year.'/'.$month.'/" title="Скачать архивом" ></a></td>
      </tr>';
      }



       if ($sven == 1  && date("Y-m-j", strtotime($year.'-'.$month.'-1')) > date("Y-m-j", strtotime('2018-4-1'))&& date("Y-n", strtotime($year.'-'.$month.'-01')) < date("Y-n")) {

      if ($type10['status'] == 1) {


      if (!$type10['sum']) {
      $summ = get_service_summ_fast($userID, $month, $year, 'SVEN');
     // here!
      } else {
      $summ = $type10['sum'];
      }

      $content_list['total'] += $summ;
      if ($summ  && $summ != '-') {
      $content_list['body'] .= '<tr>
      <td >'.$year.'.'.$month.'<br><u>SVEN</u></td>
      <td><a href="/get-detail-report/?service-id='.$type10['service_id'].'&month='.$month.'&year='.$year.'&brand=sven">Д. отчет</a></td>
      <td><a href="/get-payment-act/'.$year.'/'.$month.'/sven/">Акт выполненных работ</a></td>
      <td><a href="/get-payment-bill/'.$year.'/'.$month.'/sven/">Счет на оплату</a></td>';
      if ($add_report == 1) {
      $content_list['body'] .= '<td><a class="dwn" data-href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/sven/" href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/sven/">К. отчет</a></td>';
      }
      $content_list['body'] .= '<td>'.$summ.',00 руб.</td>';
      $content_list['body'] .= '<td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type10['id'].'">
      <option value="0" '.(($type10['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type10['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>';
      $content_list['body'] .= '<td><table style="    margin: 0 auto;" class="nohov">
      <tr>
      <td>
      <form method="POST">
      <select  name="status_act" data-pay-id="'.$type9['id'].'">
      <option value="0" '.(($type9['original'] == 0) ? 'selected' : '').'>Оригинал акта не получен</option>
      <option value="1" '.(($type9['original'] == 1) ? 'selected' : '').'>Оригинал акта получен</option>
      </select>
      </form>
      </td>
      </tr>
      <tr>
      <td>
      <form method="POST">
      <select  name="status_bill" data-pay-id="'.$type10['id'].'">
      <option value="0" '.(($type10['original'] == 0) ? 'selected' : '').'>Оригинал счета не получен</option>
      <option value="1" '.(($type10['original'] == 1) ? 'selected' : '').'>Оригинал счета получен</option>
      </select>
      </form>
      </td>
      </tr></table></td>';
      $content_list['body'] .= '<td align="center" class="linkz"><a class="t-1" href="/get-payment-archive/'.$year.'/'.$month.'/sven/" title="Скачать архивом" ></a></td>
      </tr>';
      }
      }
      }

      if ($tesler == 1) {

      if ($type4['status'] == 1) {

 
      if (!$type4['sum']) {
      $summ = get_service_summ_fast($userID, $month, $year, 'TESLER');
     // here!
      } else {
      if (date("Y-m-j", strtotime($year.'-'.$month.'-1')) >= date("Y-m-j", strtotime('2019-10-1'))) {
      $summ = $type4['sum']-get_service_summ_without_payed($userID, $month, $year, 'TESLER');
      } else {
      $summ = $type4['sum'];
      }
      }

      $content_list['total'] += $summ;
      if ($summ  && $summ != '-') {
      $content_list['body'] .= '<tr>
      <td >'.$year.'.'.$month.'<br><u>TESLER</u></td>
      <td><a href="/get-detail-report/?service-id='.$type4['service_id'].'&month='.$month.'&year='.$year.'&brand=tesler">Д. отчет</a></td>
      <td><a href="/get-payment-act/'.$year.'/'.$month.'/tesler/">Акт выполненных работ</a></td>
      <td><a href="/get-payment-bill/'.$year.'/'.$month.'/tesler/">Счет на оплату</a></td>';
      if ($add_report == 1) {
      $content_list['body'] .= '<td><a class="dwn" data-href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/TESLER/" href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/TESLER/">К. отчет</a></td>';
      }
      $content_list['body'] .= '<td>'.$summ.',00 руб.</td>';

       $content_list['body'] .= '<td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type4['id'].'">
      <option value="0" '.(($type4['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type4['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>';
      $content_list['body'] .= '<td><table style="    margin: 0 auto;" class="nohov">
      <tr>
      <td>
      <form method="POST">
      <select  name="status_act" data-pay-id="'.$type3['id'].'">
      <option value="0" '.(($type3['original'] == 0) ? 'selected' : '').'>Оригинал акта не получен</option>
      <option value="1" '.(($type3['original'] == 1) ? 'selected' : '').'>Оригинал акта получен</option>
      </select>
      </form>
      </td>
      </tr>
      <tr>
      <td>
      <form method="POST">
      <select  name="status_bill" data-pay-id="'.$type4['id'].'">
      <option value="0" '.(($type4['original'] == 0) ? 'selected' : '').'>Оригинал счета не получен</option>
      <option value="1" '.(($type4['original'] == 1) ? 'selected' : '').'>Оригинал счета получен</option>
      </select>
      </form>
      </td>
      </tr></table></td>';

      $content_list['body'] .= '<td align="center" class="linkz"><a class="t-1" href="/get-payment-archive/'.$year.'/'.$month.'/tesler/" title="Скачать архивом" ></a></td>
      </tr>';
      }
      }
      }


      if ($roch == 1) {

        if ($rochBill['status'] == 1) {
  
   
        if (!$rochBill['sum']) {
        $summ = get_service_summ_fast($userID, $month, $year, 'ROCH');
       // here!
        } else {
        $summ = $rochBill['sum'];
        }
  
        $content_list['total'] += $summ;
        if ($summ  && $summ != '-') {
        $content_list['body'] .= '<tr>
        <td >'.$year.'.'.$month.'<br><u>ROCH</u></td>
        <td><a href="/get-detail-report/?service-id='.$rochBill['service_id'].'&month='.$month.'&year='.$year.'&brand=roch">Д. отчет</a></td>
        <td><a href="/get-payment-act/'.$year.'/'.$month.'/roch/">Акт выполненных работ</a></td>
        <td><a href="/get-payment-bill/'.$year.'/'.$month.'/roch/">Счет на оплату</a></td>';
        if ($add_report == 1) {
        $content_list['body'] .= '<td><a class="dwn" data-href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/ROCH/" href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/ROCH/">К. отчет</a></td>';
        }
        $content_list['body'] .= '<td>'.$summ.',00 руб.</td>';
  
         $content_list['body'] .= '<td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
        <form method="POST">
        <select  name="status_pay" data-pay-id="'.$rochBill['id'].'">
        <option value="0" '.(($rochBill['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
        <option value="1" '.(($rochBill['status'] == 1) ? 'selected' : '').'>Оплачено</option>
        </select>
        </form>
        </td></tr></table></td>';
        $content_list['body'] .= '<td><table style="    margin: 0 auto;" class="nohov">
        <tr>
        <td>
        <form method="POST">
        <select  name="status_act" data-pay-id="'.$rochAct['id'].'">
        <option value="0" '.(($rochAct['original'] == 0) ? 'selected' : '').'>Оригинал акта не получен</option>
        <option value="1" '.(($rochAct['original'] == 1) ? 'selected' : '').'>Оригинал акта получен</option>
        </select>
        </form>
        </td>
        </tr>
        <tr>
        <td>
        <form method="POST">
        <select  name="status_bill" data-pay-id="'.$rochBill['id'].'">
        <option value="0" '.(($rochBill['original'] == 0) ? 'selected' : '').'>Оригинал счета не получен</option>
        <option value="1" '.(($rochBill['original'] == 1) ? 'selected' : '').'>Оригинал счета получен</option>
        </select>
        </form>
        </td>
        </tr></table></td>';
  
        $content_list['body'] .= '<td align="center" class="linkz"><a class="t-1" href="/get-payment-archive/'.$year.'/'.$month.'/roch/" title="Скачать архивом" ></a></td>
        </tr>';
        }
        }
        }


      if ($horizont == 1) {

      if ($type12['status'] == 1) {


      if (!$type12['sum']) {
    //  $summ = get_service_summ_fast($userID, $month, $year, 'SVEN');
     // here!
     $summ = 0;
      } else {
      $summ = $type12['sum'];
      }

      $content_list['total'] += $summ;
      if ($summ  && $summ != '-') {
      $content_list['body'] .= '<tr>
      <td >'.$year.'.'.$month.'<br><u>ГОРИЗОНТ-СОЮЗ</u></td>
      <td><a href="/get-detail-report/?service-id='.$type12['service_id'].'&month='.$month.'&year='.$year.'&brand=horizont">Д. отчет</a></td>
      <td><a href="/get-payment-act/'.$year.'/'.$month.'/horizont/">Акт выполненных работ</a></td>
      <td><a href="/get-payment-bill/'.$year.'/'.$month.'/horizont/">Счет на оплату</a></td>';
      if ($add_report == 1) {
      $content_list['body'] .= '<td><a class="dwn" data-href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/horizont/" href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/horizont/">К. отчет</a></td>';
      }
      $content_list['body'] .= '<td>'.$summ.',00 руб.</td>';
      $content_list['body'] .= '<td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type12['id'].'">
      <option value="0" '.(($type12['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type12['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>';
      $content_list['body'] .= '<td><table style="    margin: 0 auto;" class="nohov">
      <tr>
      <td>
      <form method="POST">
      <select  name="status_act" data-pay-id="'.$type11['id'].'">
      <option value="0" '.(($type11['original'] == 0) ? 'selected' : '').'>Оригинал акта не получен</option>
      <option value="1" '.(($type11['original'] == 1) ? 'selected' : '').'>Оригинал акта получен</option>
      </select>
      </form>
      </td>
      </tr>
      <tr>
      <td>
      <form method="POST">
      <select  name="status_bill" data-pay-id="'.$type12['id'].'">
      <option value="0" '.(($type12['original'] == 0) ? 'selected' : '').'>Оригинал счета не получен</option>
      <option value="1" '.(($type12['original'] == 1) ? 'selected' : '').'>Оригинал счета получен</option>
      </select>
      </form>
      </td>
      </tr></table></td>';
      $content_list['body'] .= '<td align="center" class="linkz"><a class="t-1" href="/get-payment-archive/'.$year.'/'.$month.'/horizont/" title="Скачать архивом" ></a></td>
      </tr>';
      }
      }
      }

      if ($userID == 33) {

      if (date("Y-m-j", strtotime($year.'-'.$month.'-1')) >= date("Y-m-j", strtotime('2018-2-1')) && date('Y-m', strtotime($year.'-'.$month.'-01')) < strtotime(date('Y-m-d'))) {

  $type5 = create_or_get_payment_id($userID, $year, $month, 5); //счет
    $type6 = create_or_get_payment_id($userID, $year, $month, 6); //счет


      $summ = 120000;
      $type5 = create_or_get_payment_id($userID, $year, $month, 5); //счет
      $type6 = create_or_get_payment_id($userID, $year, $month, 6); //счет
      $content_list['total'] += $summ;
      if ($summ  && $summ != '-') {
      $content_list['body'] .= '<tr>
      <td >'.$year.'.'.$month.'<br><u>Оптима ТП</u></td>
      <td></td>
      <td><a href="/get-payment-act-optima/'.$year.'/'.$month.'/optima-tp/">Акт выполненных работ</a></td>
      <td><a href="/get-payment-bill-optima/'.$year.'/'.$month.'/optima-tp/">Счет на оплату</a></td>';
      if ($add_report == 1) {
      $content_list['body'] .= '<td></td>';
      }
      $content_list['body'] .= '<td>'.$summ.',00 руб.</td>
      <td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type6['id'].'">
      <option value="0" '.(($type6['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type6['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>
            <td><table style="    margin: 0 auto;" class="nohov">
      <tr>
      <td>
      <form method="POST">
      <select  name="status_act" data-pay-id="'.$type5['id'].'">
      <option value="0" '.(($type5['original'] == 0) ? 'selected' : '').'>Оригинал акта не получен</option>
      <option value="1" '.(($type5['original'] == 1) ? 'selected' : '').'>Оригинал акта получен</option>
      </select>
      </form>
      </td>
      </tr>
      <tr>
      <td>
      <form method="POST">
      <select  name="status_bill" data-pay-id="'.$type6['id'].'">
      <option value="0" '.(($type6['original'] == 0) ? 'selected' : '').'>Оригинал счета не получен</option>
      <option value="1" '.(($type6['original'] == 1) ? 'selected' : '').'>Оригинал счета получен</option>
      </select>
      </form>
      </td>
      </tr></table></td>
      <td align="center" class="linkz"><!--<a class="t-1" href="/get-payment-archive-optima/'.$year.'/'.$month.'/optima-tp/" title="Скачать архивом" ></a>--></td>
      </tr>';
      }

      }

      if (date("Y-m-j", strtotime($year.'-'.$month.'-1')) >= date("Y-m-j", strtotime('2019-1-1')) && date("Y-m", strtotime($year.'-'.$month.'-01')) < date("Y-m")) {

       $type7 = create_or_get_payment_id($userID, $year, $month, 7); //счет
      $type8 = create_or_get_payment_id($userID, $year, $month, 8); //счет


      $summ = 157000;

      $content_list['total'] += $summ;
      if ($summ  && $summ != '-') {
      $content_list['body'] .= '<tr>
      <td >'.$year.'.'.$month.'<br><u>Оптима ПППО</u></td>
      <td></td>
      <td><a href="/get-payment-act-optima-pppo/'.$year.'/'.$month.'/optima-tp/">Акт выполненных работ</a></td>
      <td><a href="/get-payment-bill-optima-pppo/'.$year.'/'.$month.'/optima-tp/">Счет на оплату</a></td>';
      if ($add_report == 1) {
      $content_list['body'] .= '<td></td>';
      }
      $content_list['body'] .= '<td>'.$summ.',00 руб.</td>
      <td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type8['id'].'">
      <option value="0" '.(($type8['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type8['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>
            <td><table style="    margin: 0 auto;" class="nohov">
      <tr>
      <td>
      <form method="POST">
      <select  name="status_act" data-pay-id="'.$type7['id'].'">
      <option value="0" '.(($type7['original'] == 0) ? 'selected' : '').'>Оригинал акта не получен</option>
      <option value="1" '.(($type7['original'] == 1) ? 'selected' : '').'>Оригинал акта получен</option>
      </select>
      </form>
      </td>
      </tr>
      <tr>
      <td>
      <form method="POST">
      <select  name="status_bill" data-pay-id="'.$type8['id'].'">
      <option value="0" '.(($type8['original'] == 0) ? 'selected' : '').'>Оригинал счета не получен</option>
      <option value="1" '.(($type8['original'] == 1) ? 'selected' : '').'>Оригинал счета получен</option>
      </select>
      </form>
      </td>
      </tr></table></td>
      <td align="center" class="linkz"><!--<a class="t-1" href="/get-payment-archive-optima/'.$year.'/'.$month.'/optima-tp/" title="Скачать архивом" ></a>--></td>
      </tr>';
      }

      }

      }


      }

      }

      if ($_COOKIE['notpayed'] == 1) {

      if ($type2['status'] == 0) {


      if (!$type2['sum']) {
      $summ = get_service_summ_fast($userID, $month, $year, 'HARPER');
     // here!
      } else {
      if (date("Y-m-j", strtotime($year.'-'.$month.'-1')) >= date("Y-m-j", strtotime('2019-10-1'))) {
      $summ = $type2['sum']-get_service_summ_without_payed($userID, $month, $year, 'HARPER');
      } else {
      $summ = $type2['sum'];
      }
      }


      $content_list['total'] += $summ;
     if ($summ  && $summ != '-') {
      $content_list['body'] .= '<tr>
      <td >'.$year.'.'.$month.'<br><u>HARPER</u></td>
      <td><a href="/get-detail-report/?service-id='.$type2['service_id'].'&month='.$month.'&year='.$year.'&brand=harper">Д. отчет</a></td>
      <td><a href="/get-payment-act/'.$year.'/'.$month.'/">Акт выполненных работ</a></td>
      <td><a href="/get-payment-bill/'.$year.'/'.$month.'/">Счет на оплату</a></td>';
      if ($add_report == 1) {
      $content_list['body'] .= '<td><a class="dwn" data-href="/get-agent/'.$year.'/'.$month.'/'.$counter.'/'.((date("Y-m-j", strtotime($year.'-'.$month.'-1')) >= date("Y-m-j", strtotime('2019-10-1'))) ? 'HARPER,OLTO,SKYLINE' : 'HARPER,OLTO').'/" href="/get-agent/'.$year.'/'.$month.'/'.$counter.'/'.((date("Y-m-j", strtotime($year.'-'.$month.'-1')) >= date("Y-m-j", strtotime('2019-10-1'))) ? 'HARPER,OLTO,SKYLINE' : 'HARPER,OLTO').'/">К. отчет3</a></td>';
      }
      $content_list['body'] .= '<td>'.$summ.',00 руб.</td>';

      $content_list['body'] .= '<td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type2['id'].'">
      <option value="0" '.(($type2['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type2['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>';
      $content_list['body'] .= '<td><table style="    margin: 0 auto;" class="nohov">
      <tr>
      <td>
      <form method="POST">
      <select  name="status_act" data-pay-id="'.$type1['id'].'">
      <option value="0" '.(($type1['original'] == 0) ? 'selected' : '').'>Оригинал акта не получен</option>
      <option value="1" '.(($type1['original'] == 1) ? 'selected' : '').'>Оригинал акта получен</option>
      </select>
      </form>
      </td>
      </tr>
      <tr>
      <td>
      <form method="POST">
      <select  name="status_bill" data-pay-id="'.$type2['id'].'">
      <option value="0" '.(($type2['original'] == 0) ? 'selected' : '').'>Оригинал счета не получен</option>
      <option value="1" '.(($type2['original'] == 1) ? 'selected' : '').'>Оригинал счета получен</option>
      </select>
      </form>
      </td>
      </tr></table></td>';

      $content_list['body'] .= '<td align="center" class="linkz"><a class="t-1" href="/get-payment-archive/'.$year.'/'.$month.'/" title="Скачать архивом" ></a></td>
      </tr>';
      }


      if ($tesler == 1) {

      if ($type4['status'] == 0) {


      if (!$type4['sum']) {
      $summ = get_service_summ_fast($userID, $month, $year, 'TESLER');
      // here!
      } else {
      if (date("Y-m-j", strtotime($year.'-'.$month.'-1')) >= date("Y-m-j", strtotime('2019-10-1'))) {
      $summ = $type4['sum']-get_service_summ_without_payed($userID, $month, $year, 'TESLER');
      } else {
      $summ = $type4['sum'];
      }
      }

      $content_list['total'] += $summ;
      if ($summ  && $summ != '-') {
      $content_list['body'] .= '<tr>
      <td >'.$year.'.'.$month.'<br><u>TESLER</u></td>
      <td><a href="/get-detail-report/?service-id='.$type4['service_id'].'&month='.$month.'&year='.$year.'&brand=tesler">Д. отчет</a></td>
      <td><a href="/get-payment-act/'.$year.'/'.$month.'/tesler/">Акт выполненных работ</a></td>
      <td><a href="/get-payment-bill/'.$year.'/'.$month.'/tesler/">Счет на оплату</a></td>';
      if ($add_report == 1) {
      $content_list['body'] .= '<td><a class="dwn" data-href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/TESLER/" href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/TESLER/">К. отчет</a></td>';
      }
      $content_list['body'] .= '<td>'.$summ.',00 руб.</td>';

      $content_list['body'] .= '<td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type4['id'].'">
      <option value="0" '.(($type4['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type4['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>';
      $content_list['body'] .= '<td><table style="    margin: 0 auto;" class="nohov">
      <tr>
      <td>
      <form method="POST">
      <select  name="status_act" data-pay-id="'.$type3['id'].'">
      <option value="0" '.(($type3['original'] == 0) ? 'selected' : '').'>Оригинал акта не получен</option>
      <option value="1" '.(($type3['original'] == 1) ? 'selected' : '').'>Оригинал акта получен</option>
      </select>
      </form>
      </td>
      </tr>
      <tr>
      <td>
      <form method="POST">
      <select  name="status_bill" data-pay-id="'.$type4['id'].'">
      <option value="0" '.(($type4['original'] == 0) ? 'selected' : '').'>Оригинал счета не получен</option>
      <option value="1" '.(($type4['original'] == 1) ? 'selected' : '').'>Оригинал счета получен</option>
      </select>
      </form>
      </td>
      </tr></table></td>';

      $content_list['body'] .= '<td align="center" class="linkz"><a class="t-1" href="/get-payment-archive/'.$year.'/'.$month.'/tesler/" title="Скачать архивом" ></a></td>
      </tr>';
      }
      }
      }


      if ($roch == 1) {

        if ($rochBill['status'] == 0) {
  
  
        if (!$rochBill['sum']) {
        $summ = get_service_summ_fast($userID, $month, $year, 'ROCH');
        // here!
        } else {
        if (date("Y-m-j", strtotime($year.'-'.$month.'-1')) >= date("Y-m-j", strtotime('2019-10-1'))) {
        $summ = $rochBill['sum']-get_service_summ_without_payed($userID, $month, $year, 'ROCH');
        } else {
        $summ = $rochBill['sum'];
        }
        }
  
        $content_list['total'] += $summ;
        if ($summ  && $summ != '-') {
        $content_list['body'] .= '<tr>
        <td >'.$year.'.'.$month.'<br><u>ROCH</u></td>
        <td><a href="/get-detail-report/?service-id='.$rochBill['service_id'].'&month='.$month.'&year='.$year.'&brand=roch">Д. отчет</a></td>
        <td><a href="/get-payment-act/'.$year.'/'.$month.'/roch/">Акт выполненных работ</a></td>
        <td><a href="/get-payment-bill/'.$year.'/'.$month.'/roch/">Счет на оплату</a></td>';
        if ($add_report == 1) {
        $content_list['body'] .= '<td><a class="dwn" data-href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/ROCH/" href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/ROCH/">К. отчет</a></td>';
        }
        $content_list['body'] .= '<td>'.$summ.',00 руб.</td>';
  
        $content_list['body'] .= '<td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
        <form method="POST">
        <select  name="status_pay" data-pay-id="'.$rochBill['id'].'">
        <option value="0" '.(($rochBill['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
        <option value="1" '.(($rochBill['status'] == 1) ? 'selected' : '').'>Оплачено</option>
        </select>
        </form>
        </td></tr></table></td>';
        $content_list['body'] .= '<td><table style="    margin: 0 auto;" class="nohov">
        <tr>
        <td>
        <form method="POST">
        <select  name="status_act" data-pay-id="'.$rochAct['id'].'">
        <option value="0" '.(($rochAct['original'] == 0) ? 'selected' : '').'>Оригинал акта не получен</option>
        <option value="1" '.(($rochAct['original'] == 1) ? 'selected' : '').'>Оригинал акта получен</option>
        </select>
        </form>
        </td>
        </tr>
        <tr>
        <td>
        <form method="POST">
        <select  name="status_bill" data-pay-id="'.$rochBill['id'].'">
        <option value="0" '.(($rochBill['original'] == 0) ? 'selected' : '').'>Оригинал счета не получен</option>
        <option value="1" '.(($rochBill['original'] == 1) ? 'selected' : '').'>Оригинал счета получен</option>
        </select>
        </form>
        </td>
        </tr></table></td>';
  
        $content_list['body'] .= '<td align="center" class="linkz"><a class="t-1" href="/get-payment-archive/'.$year.'/'.$month.'/roch/" title="Скачать архивом" ></a></td>
        </tr>';
        }
        }
        }


       if ($horizont == 1) {
      ///////////////////////////////////////1
      if ($type12['status'] == 0) {


      if (!$type12['sum']) {
     // $summ = get_service_summ_fast($userID, $month, $year, 'SVEN');
     // here!
     $summ = 0;
      } else {
      $summ = $type12['sum'];
      }

      $content_list['total'] += $summ;
      if ($summ  && $summ != '-') {
      $content_list['body'] .= '<tr>
      <td >'.$year.'.'.$month.'<br><u>ГОРИЗОНТ-СОЮЗ</u></td>
      <td><a href="/get-detail-report/?service-id='.$type12['service_id'].'&month='.$month.'&year='.$year.'&brand=horizont">Д. отчет</a></td>
      <td><a href="/get-payment-act/'.$year.'/'.$month.'/horizont/">Акт выполненных работ</a></td>
      <td><a href="/get-payment-bill/'.$year.'/'.$month.'/horizont/">Счет на оплату</a></td>';
      if ($add_report == 1) {
      $content_list['body'] .= '<td><a class="dwn" data-href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/horizont/" href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/horizont/">К. отчет</a></td>';
      }
      $content_list['body'] .= '<td>'.$summ.',00 руб.</td>';
      $content_list['body'] .= '<td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type12['id'].'">
      <option value="0" '.(($type12['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type12['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>';
      $content_list['body'] .= '<td><table style="    margin: 0 auto;" class="nohov">
      <tr>
      <td>
      <form method="POST">
      <select  name="status_act" data-pay-id="'.$type11['id'].'">
      <option value="0" '.(($type11['original'] == 0) ? 'selected' : '').'>Оригинал акта не получен</option>
      <option value="1" '.(($type11['original'] == 1) ? 'selected' : '').'>Оригинал акта получен</option>
      </select>
      </form>
      </td>
      </tr>
      <tr>
      <td>
      <form method="POST">
      <select  name="status_bill" data-pay-id="'.$type12['id'].'">
      <option value="0" '.(($type12['original'] == 0) ? 'selected' : '').'>Оригинал счета не получен</option>
      <option value="1" '.(($type12['original'] == 1) ? 'selected' : '').'>Оригинал счета получен</option>
      </select>
      </form>
      </td>
      </tr></table></td>';
      $content_list['body'] .= '<td align="center" class="linkz"><a class="t-1" href="/get-payment-archive/'.$year.'/'.$month.'/horizont/" title="Скачать архивом" ></a></td>
      </tr>';
      }
      }
      }


       if ($sven == 1  && date("Y-m-j", strtotime($year.'-'.$month.'-1')) > date("Y-m-j", strtotime('2018-4-1'))&& date("Y-n", strtotime($year.'-'.$month)) < date("Y-n")) {

      if ($type10['status'] == 0) {



      if (!$type10['sum']) {
      $summ = get_service_summ_fast($userID, $month, $year, 'SVEN');
      //here!
      } else {
      $summ = $type10['sum'];
      }

      $content_list['total'] += $summ;
      if ($summ  && $summ != '-') {
      $content_list['body'] .= '<tr>
      <td >'.$year.'.'.$month.'<br><u>SVEN</u></td>
      <td><a href="/get-detail-report/?service-id='.$type10['service_id'].'&month='.$month.'&year='.$year.'&brand=sven">Д. отчет</a></td>
      <td><a href="/get-payment-act/'.$year.'/'.$month.'/sven/">Акт выполненных работ</a></td>
      <td><a href="/get-payment-bill/'.$year.'/'.$month.'/sven/">Счет на оплату</a></td>';
      if ($add_report == 1) {
      $content_list['body'] .= '<td><a class="dwn" data-href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/sven/" href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/sven/">К. отчет</a></td>';
      }
      $content_list['body'] .= '<td>'.$summ.',00 руб.</td>';
      $content_list['body'] .= '<td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type10['id'].'">
      <option value="0" '.(($type10['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type10['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>';
      $content_list['body'] .= '<td><table style="    margin: 0 auto;" class="nohov">
      <tr>
      <td>
      <form method="POST">
      <select  name="status_act" data-pay-id="'.$type9['id'].'">
      <option value="0" '.(($type9['original'] == 0) ? 'selected' : '').'>Оригинал акта не получен</option>
      <option value="1" '.(($type9['original'] == 1) ? 'selected' : '').'>Оригинал акта получен</option>
      </select>
      </form>
      </td>
      </tr>
      <tr>
      <td>
      <form method="POST">
      <select  name="status_bill" data-pay-id="'.$type10['id'].'">
      <option value="0" '.(($type10['original'] == 0) ? 'selected' : '').'>Оригинал счета не получен</option>
      <option value="1" '.(($type10['original'] == 1) ? 'selected' : '').'>Оригинал счета получен</option>
      </select>
      </form>
      </td>
      </tr></table></td>';
      $content_list['body'] .= '<td align="center" class="linkz"><a class="t-1" href="/get-payment-archive/'.$year.'/'.$month.'/sven/" title="Скачать архивом" ></a></td>
      </tr>';
      }
      }
      }


      if ($userID == 33) {

      if (date("Y-m-j", strtotime($year.'-'.$month.'-1')) >= date("Y-m-j", strtotime('2018-11-1')) &&  date('Y-m', strtotime($year.'-'.$month.'-01')) < date('Y-m')) {

        $type5 = create_or_get_payment_id($userID, $year, $month, 5); //счет
      $type6 = create_or_get_payment_id($userID, $year, $month, 6); //счет


      $summ = 120000;

      $content_list['total'] += $summ;
      if ($summ  && $summ != '-') {
      $content_list['body'] .= '<tr>
      <td >'.$year.'.'.$month.'<br><u>Оптима ТП</u></td>
      <td></td>
      <td><a href="/get-payment-act-optima/'.$year.'/'.$month.'/optima-tp/">Акт выполненных работ</a></td>
      <td><a href="/get-payment-bill-optima/'.$year.'/'.$month.'/optima-tp/">Счет на оплату</a></td>';
      if ($add_report == 1) {
      $content_list['body'] .= '<td></td>';
      }
      $content_list['body'] .= '<td>'.$summ.',00 руб.</td>
      <td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type6['id'].'">
      <option value="0" '.(($type6['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type6['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>
            <td><table style="    margin: 0 auto;" class="nohov">
      <tr>
      <td>
      <form method="POST">
      <select  name="status_act" data-pay-id="'.$type5['id'].'">
      <option value="0" '.(($type5['original'] == 0) ? 'selected' : '').'>Оригинал акта не получен</option>
      <option value="1" '.(($type5['original'] == 1) ? 'selected' : '').'>Оригинал акта получен</option>
      </select>
      </form>
      </td>
      </tr>
      <tr>
      <td>
      <form method="POST">
      <select  name="status_bill" data-pay-id="'.$type6['id'].'">
      <option value="0" '.(($type6['original'] == 0) ? 'selected' : '').'>Оригинал счета не получен</option>
      <option value="1" '.(($type6['original'] == 1) ? 'selected' : '').'>Оригинал счета получен</option>
      </select>
      </form>
      </td>
      </tr></table></td>
      <td align="center" class="linkz"><!--<a class="t-1" href="/get-payment-archive-optima/'.$year.'/'.$month.'/optima-tp/" title="Скачать архивом" ></a>--></td>
      </tr>';
      }

      }

      if (date("Y-m-j", strtotime($year.'-'.$month.'-1')) >= date("Y-m-j", strtotime('2019-1-1'))&& date("Y-m", strtotime($year.'-'.$month.'-01')) < date("Y-m")) {

    $type7 = create_or_get_payment_id($userID, $year, $month, 7); //счет
      $type8 = create_or_get_payment_id($userID, $year, $month, 8); //счет


      $summ = 157000;
      /*$type7 = create_or_get_payment_id($userID, $year, $month, 7); //счет
      $type8 = create_or_get_payment_id($userID, $year, $month, 8); //счет */
      $content_list['total'] += $summ;
      if ($summ  && $summ != '-') {
      $content_list['body'] .= '<tr>
      <td >'.$year.'.'.$month.'<br><u>Оптима ПППО</u></td>
      <td></td>
      <td><a href="/get-payment-act-optima-pppo/'.$year.'/'.$month.'/optima-tp/">Акт выполненных работ</a></td>
      <td><a href="/get-payment-bill-optima-pppo/'.$year.'/'.$month.'/optima-tp/">Счет на оплату</a></td>';
      if ($add_report == 1) {
      $content_list['body'] .= '<td></td>';
      }
      $content_list['body'] .= '<td>'.$summ.',00 руб.</td>
      <td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type8['id'].'">
      <option value="0" '.(($type8['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type8['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>
            <td><table style="    margin: 0 auto;" class="nohov">
      <tr>
      <td>
      <form method="POST">
      <select  name="status_act" data-pay-id="'.$type7['id'].'">
      <option value="0" '.(($type7['original'] == 0) ? 'selected' : '').'>Оригинал акта не получен</option>
      <option value="1" '.(($type7['original'] == 1) ? 'selected' : '').'>Оригинал акта получен</option>
      </select>
      </form>
      </td>
      </tr>
      <tr>
      <td>
      <form method="POST">
      <select  name="status_bill" data-pay-id="'.$type8['id'].'">
      <option value="0" '.(($type8['original'] == 0) ? 'selected' : '').'>Оригинал счета не получен</option>
      <option value="1" '.(($type8['original'] == 1) ? 'selected' : '').'>Оригинал счета получен</option>
      </select>
      </form>
      </td>
      </tr></table></td>
      <td align="center" class="linkz"><!--<a class="t-1" href="/get-payment-archive-optima/'.$year.'/'.$month.'/optima-tp/" title="Скачать архивом" ></a>--></td>
      </tr>';
      }

      }

      }


      }

      }
// Code starts here
if ($_COOKIE['notpayed'] != 1 && $_COOKIE['payed'] != 1) {



    
          // HERE!
          $summ = get_service_summ_fast($userID, $month, $year, 'HARPER');
          if (date("Y-m-j", strtotime($year . '-' . $month . '-1')) >= date("Y-m-j", strtotime('2019-10-1'))) {
            $summ = $type2['sum'] - get_service_summ_without_payed($userID, $month, $year, 'HARPER');
          } 

      $content_list['total'] += $summ;

      if ($summ  && $summ != '-') {
      $content_list['body'] .= '<tr>
      <td >'.$year.'.'.$month.'<br><u>HARPER</u></td>
      <td><a href="/get-detail-report/?service-id='.$type2['service_id'].'&month='.$month.'&year='.$year.'&brand=harper">Д. отчет</a></td>
      <td><a href="/get-payment-act/'.$year.'/'.$month.'/">Акт выполненных работ</a></td>
      <td><a href="/get-payment-bill/'.$year.'/'.$month.'/">Счет на оплату</a></td>';
      if ($add_report == 1) {
      $content_list['body'] .= '<td><a class="dwn" data-href="/get-agent/'.$year.'/'.$month.'/'.$counter.'/'.((date("Y-m-j", strtotime($year.'-'.$month.'-1')) >= date("Y-m-j", strtotime('2019-10-1'))) ? 'HARPER,OLTO,SKYLINE' : 'HARPER,OLTO').'/" href="/get-agent/'.$year.'/'.$month.'/'.$counter.'/'.((date("Y-m-j", strtotime($year.'-'.$month.'-1')) >= date("Y-m-j", strtotime('2019-10-1'))) ? 'HARPER,OLTO,SKYLINE' : 'HARPER,OLTO').'/">К. отчет4</a></td>';
      }
      $content_list['body'] .= '<td>'.$summ.',00 руб.</td>';

      $content_list['body'] .= '<td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type2['id'].'">
      <option value="0" '.(($type2['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type2['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>';
      $content_list['body'] .= '<td><table style="    margin: 0 auto;" class="nohov">
      <tr>
      <td>
      <form method="POST">
      <select  name="status_act" data-pay-id="'.$type1['id'].'">
      <option value="0" '.(($type1['original'] == 0) ? 'selected' : '').'>Оригинал акта не получен</option>
      <option value="1" '.(($type1['original'] == 1) ? 'selected' : '').'>Оригинал акта получен</option>
      </select>
      </form>
      </td>
      </tr>
      <tr>
      <td>
      <form method="POST">
      <select  name="status_bill" data-pay-id="'.$type2['id'].'">
      <option value="0" '.(($type2['original'] == 0) ? 'selected' : '').'>Оригинал счета не получен</option>
      <option value="1" '.(($type2['original'] == 1) ? 'selected' : '').'>Оригинал счета получен</option>
      </select>
      </form>
      </td>
      </tr></table></td>';

      $content_list['body'] .= '<td align="center" class="linkz"><a class="t-1" href="/get-payment-archive/'.$year.'/'.$month.'/" title="Скачать архивом" ></a></td>
      </tr>';
      }


      if ($tesler == 1) {


        // HERE!
      $summ = get_service_summ_fast($userID, $month, $year, 'TESLER');
      if (date("Y-m-j", strtotime($year.'-'.$month.'-1')) >= date("Y-m-j", strtotime('2019-10-1'))) {
      $summ = $type4['sum']-get_service_summ_without_payed($userID, $month, $year, 'TESLER');
      } 

      $content_list['total'] += $summ;


      if ($summ && $summ != '-') {

      $content_list['body'] .= '<tr>
      <td >'.$year.'.'.$month.'<br><u>TESLER</u></td>
      <td><a href="/get-detail-report/?service-id='.$type4['service_id'].'&month='.$month.'&year='.$year.'&brand=tesler">Д. отчет</a></td>
      <td><a href="/get-payment-act/'.$year.'/'.$month.'/tesler/">Акт выполненных работ</a></td>
      <td><a href="/get-payment-bill/'.$year.'/'.$month.'/tesler/">Счет на оплату</a></td>';
      if ($add_report == 1) {
      $content_list['body'] .= '<td><a class="dwn" data-href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/TESLER/" href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/TESLER/">К. отчет</a></td>';
      }
      $content_list['body'] .= '<td>'.$summ.',00 руб.</td>';

      $content_list['body'] .= '<td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type4['id'].'">
      <option value="0" '.(($type4['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type4['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>';
      $content_list['body'] .= '<td><table style="    margin: 0 auto;" class="nohov">
      <tr>
      <td>
      <form method="POST">
      <select  name="status_act" data-pay-id="'.$type3['id'].'">
      <option value="0" '.(($type3['original'] == 0) ? 'selected' : '').'>Оригинал акта не получен</option>
      <option value="1" '.(($type3['original'] == 1) ? 'selected' : '').'>Оригинал акта получен</option>
      </select>
      </form>
      </td>
      </tr>
      <tr>
      <td>
      <form method="POST">
      <select  name="status_bill" data-pay-id="'.$type4['id'].'">
      <option value="0" '.(($type4['original'] == 0) ? 'selected' : '').'>Оригинал счета не получен</option>
      <option value="1" '.(($type4['original'] == 1) ? 'selected' : '').'>Оригинал счета получен</option>
      </select>
      </form>
      </td>
      </tr></table></td>';

      $content_list['body'] .= '<td align="center" class="linkz"><a class="t-1" href="/get-payment-archive/'.$year.'/'.$month.'/tesler/" title="Скачать архивом" ></a></td>
      </tr>';
      }

      }

      if ($roch == 1) {


        // HERE!
      $summ = get_service_summ_fast($userID, $month, $year, 'ROCH');
      if (date("Y-m-j", strtotime($year.'-'.$month.'-1')) >= date("Y-m-j", strtotime('2019-10-1'))) {
      $summ = $rochBill['sum']-get_service_summ_without_payed($userID, $month, $year, 'ROCH');
      } 

      $content_list['total'] += $summ;


      if ($summ && $summ != '-') {

      $content_list['body'] .= '<tr>
      <td >'.$year.'.'.$month.'<br><u>ROCH</u></td>
      <td><a href="/get-detail-report/?service-id='.$rochBill['service_id'].'&month='.$month.'&year='.$year.'&brand=roch">Д. отчет</a></td>
      <td><a href="/get-payment-act/'.$year.'/'.$month.'/roch/">Акт выполненных работ</a></td>
      <td><a href="/get-payment-bill/'.$year.'/'.$month.'/roch/">Счет на оплату</a></td>';
      if ($add_report == 1) {
      $content_list['body'] .= '<td><a class="dwn" data-href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/ROCH/" href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/ROCH/">К. отчет</a></td>';
      }
      $content_list['body'] .= '<td>'.$summ.',00 руб.</td>';

      $content_list['body'] .= '<td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$rochBill['id'].'">
      <option value="0" '.(($rochBill['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($rochBill['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>';
      $content_list['body'] .= '<td><table style="    margin: 0 auto;" class="nohov">
      <tr>
      <td>
      <form method="POST">
      <select  name="status_act" data-pay-id="'.$rochAct['id'].'">
      <option value="0" '.(($rochAct['original'] == 0) ? 'selected' : '').'>Оригинал акта не получен</option>
      <option value="1" '.(($rochAct['original'] == 1) ? 'selected' : '').'>Оригинал акта получен</option>
      </select>
      </form>
      </td>
      </tr>
      <tr>
      <td>
      <form method="POST">
      <select  name="status_bill" data-pay-id="'.$rochBill['id'].'">
      <option value="0" '.(($rochBill['original'] == 0) ? 'selected' : '').'>Оригинал счета не получен</option>
      <option value="1" '.(($rochBill['original'] == 1) ? 'selected' : '').'>Оригинал счета получен</option>
      </select>
      </form>
      </td>
      </tr></table></td>';

      $content_list['body'] .= '<td align="center" class="linkz"><a class="t-1" href="/get-payment-archive/'.$year.'/'.$month.'/roch/" title="Скачать архивом" ></a></td>
      </tr>';
      }

      }

      $content_list['body'] .= generate_custom_doc($year, $month);



      if ($horizont == 1) {

      //if ($type12['status'] == 1) {

      if (!$type12['sum']) {
        // here!
    //  $summ = get_service_summ_fast($userID, $month, $year, 'SVEN');
    $summ = 0;
      } else {
      $summ = $type12['sum'];
      }

      $content_list['total'] += $summ;
      if ($summ  && $summ != '-') {
      $content_list['body'] .= '<tr>
      <td >'.$year.'.'.$month.'<br><u>ГОРИЗОНТ-СОЮЗ</u></td>
      <td><a href="/get-detail-report/?service-id='.$type12['service_id'].'&month='.$month.'&year='.$year.'&brand=horizont">Д. отчет</a></td>
      <td><a href="/get-payment-act/'.$year.'/'.$month.'/horizont/">Акт выполненных работ</a></td>
      <td><a href="/get-payment-bill/'.$year.'/'.$month.'/horizont/">Счет на оплату</a></td>';
      if ($add_report == 1) {
      $content_list['body'] .= '<td><a class="dwn" data-href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/horizont/" href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/horizont/">К. отчет</a></td>';
      }
      $content_list['body'] .= '<td>'.$summ.',00 руб.</td>';
      $content_list['body'] .= '<td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type12['id'].'">
      <option value="0" '.(($type12['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type12['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>';
      $content_list['body'] .= '<td><table style="    margin: 0 auto;" class="nohov">
      <tr>
      <td>
      <form method="POST">
      <select  name="status_act" data-pay-id="'.$type11['id'].'">
      <option value="0" '.(($type11['original'] == 0) ? 'selected' : '').'>Оригинал акта не получен</option>
      <option value="1" '.(($type11['original'] == 1) ? 'selected' : '').'>Оригинал акта получен</option>
      </select>
      </form>
      </td>
      </tr>
      <tr>
      <td>
      <form method="POST">
      <select  name="status_bill" data-pay-id="'.$type12['id'].'">
      <option value="0" '.(($type12['original'] == 0) ? 'selected' : '').'>Оригинал счета не получен</option>
      <option value="1" '.(($type12['original'] == 1) ? 'selected' : '').'>Оригинал счета получен</option>
      </select>
      </form>
      </td>
      </tr></table></td>';
      $content_list['body'] .= '<td align="center" class="linkz"><a class="t-1" href="/get-payment-archive/'.$year.'/'.$month.'/horizont/" title="Скачать архивом" ></a></td>
      </tr>';
      }
     // }
      }/// delete horizont

      if ($sven== 1  && date("Y-m-j", strtotime($year.'-'.$month.'-1')) > date("Y-m-j", strtotime('2018-4-1')) && date("Y-n", strtotime($year.'-'.$month.'-01')) < date("Y-n")) {


// here!
      $summ = get_service_summ_fast($userID, $month, $year, 'SVEN');
      
  

      $content_list['total'] += $summ;

      if ($summ && $summ != '-') {

      $content_list['body'] .= '<tr>
      <td >'.$year.'.'.$month.'<br><u>SVEN</u></td>
      <td><a href="/get-detail-report/?service-id='.$type10['service_id'].'&month='.$month.'&year='.$year.'&brand=sven">Д. отчет</a></td>
      <td><a href="/get-payment-act/'.$year.'/'.$month.'/sven/">Акт выполненных работ</a></td>
      <td><a href="/get-payment-bill/'.$year.'/'.$month.'/sven/">Счет на оплату</a></td>';
      if ($add_report == 1) {
      $content_list['body'] .= '<td><a class="dwn" data-href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/sven/" href="/get-agent/'.$year.'/'.$month.'/'.$counter.'-2/sven/">К. отчет</a></td>';
      }
      $content_list['body'] .= '<td>'.$summ.',00 руб.</td>';
      $content_list['body'] .= '<td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type10['id'].'">
      <option value="0" '.(($type10['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type10['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>';
      $content_list['body'] .= '<td><table style="    margin: 0 auto;" class="nohov">
      <tr>
      <td>
      <form method="POST">
      <select  name="status_act" data-pay-id="'.$type9['id'].'">
      <option value="0" '.(($type9['original'] == 0) ? 'selected' : '').'>Оригинал акта не получен</option>
      <option value="1" '.(($type9['original'] == 1) ? 'selected' : '').'>Оригинал акта получен</option>
      </select>
      </form>
      </td>
      </tr>
      <tr>
      <td>
      <form method="POST">
      <select  name="status_bill" data-pay-id="'.$type10['id'].'">
      <option value="0" '.(($type10['original'] == 0) ? 'selected' : '').'>Оригинал счета не получен</option>
      <option value="1" '.(($type10['original'] == 1) ? 'selected' : '').'>Оригинал счета получен</option>
      </select>
      </form>
      </td>
      </tr></table></td>';
      $content_list['body'] .= '<td align="center" class="linkz"><a class="t-1" href="/get-payment-archive/'.$year.'/'.$month.'/sven/" title="Скачать архивом" ></a></td>
      </tr>';
      }

      }


      if ($userID == 33) {

      if (date("Y-m-j", strtotime($year.'-'.$month.'-1')) >= date("Y-m-j", strtotime('2018-11-1'))  && date('Y-m', strtotime($year.'-'.$month.'-01')) < date('Y-m')) {

          $type5 = create_or_get_payment_id($userID, $year, $month, 5); //счет
      $type6 = create_or_get_payment_id($userID, $year, $month, 6); //счет

      $summ = 120000;
      $type5 = create_or_get_payment_id($userID, $year, $month, 5); //счет
      $type6 = create_or_get_payment_id($userID, $year, $month, 6); //счет
      $content_list['total'] += $summ;
      if ($summ  && $summ != '-') {
      $content_list['body'] .= '<tr>
      <td >'.$year.'.'.$month.'<br><u>Оптима ТП</u></td>
      <td></td>
      <td><a href="/get-payment-act-optima/'.$year.'/'.$month.'/optima-tp/">Акт выполненных работ</a></td>
      <td><a href="/get-payment-bill-optima/'.$year.'/'.$month.'/optima-tp/">Счет на оплату</a></td>';
      if ($add_report == 1) {
      $content_list['body'] .= '<td></td>';
      }
      $content_list['body'] .= '<td>'.$summ.',00 руб.</td>
      <td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type6['id'].'">
      <option value="0" '.(($type6['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type6['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>
      <td><table style="    margin: 0 auto;" class="nohov">
      <tr>
      <td>
      <form method="POST">
      <select  name="status_act" data-pay-id="'.$type5['id'].'">
      <option value="0" '.(($type5['original'] == 0) ? 'selected' : '').'>Оригинал акта не получен</option>
      <option value="1" '.(($type5['original'] == 1) ? 'selected' : '').'>Оригинал акта получен</option>
      </select>
      </form>
      </td>
      </tr>
      <tr>
      <td>
      <form method="POST">
      <select  name="status_bill" data-pay-id="'.$type6['id'].'">
      <option value="0" '.(($type6['original'] == 0) ? 'selected' : '').'>Оригинал счета не получен</option>
      <option value="1" '.(($type6['original'] == 1) ? 'selected' : '').'>Оригинал счета получен</option>
      </select>
      </form>
      </td>
      </tr></table></td>
      <td align="center" class="linkz"><!--<a class="t-1" href="/get-payment-archive-optima/'.$year.'/'.$month.'/optima-tp/" title="Скачать архивом" ></a>--></td>
      </tr>';
      }

      }

      if (date("Y-m-j", strtotime($year.'-'.$month.'-1')) >= date("Y-m-j", strtotime('2019-1-1'))&& date("Y-m", strtotime($year.'-'.$month.'-01')) < date("Y-m")) {

      $type7 = create_or_get_payment_id($userID, $year, $month, 7); //счет
      $type8 = create_or_get_payment_id($userID, $year, $month, 8); //счет

      $summ = 157000;

      $content_list['total'] += $summ;
      if ($summ  && $summ != '-') {
      $content_list['body'] .= '<tr>
      <td >'.$year.'.'.$month.'<br><u>Оптима ПППО</u></td>
      <td></td>
      <td><a href="/get-payment-act-optima-pppo/'.$year.'/'.$month.'/optima-tp/">Акт выполненных работ</a></td>
      <td><a href="/get-payment-bill-optima-pppo/'.$year.'/'.$month.'/optima-tp/">Счет на оплату</a></td>';
      if ($add_report == 1) {
      $content_list['body'] .= '<td></td>';
      }
      $content_list['body'] .= '<td>'.$summ.',00 руб.</td>
      <td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type8['id'].'">
      <option value="0" '.(($type8['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type8['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>
      <td><table style="    margin: 0 auto;" class="nohov">
      <tr>
      <td>
      <form method="POST">
      <select  name="status_act" data-pay-id="'.$type7['id'].'">
      <option value="0" '.(($type7['original'] == 0) ? 'selected' : '').'>Оригинал акта не получен</option>
      <option value="1" '.(($type7['original'] == 1) ? 'selected' : '').'>Оригинал акта получен</option>
      </select>
      </form>
      </td>
      </tr>
      <tr>
      <td>
      <form method="POST">
      <select  name="status_bill" data-pay-id="'.$type8['id'].'">
      <option value="0" '.(($type8['original'] == 0) ? 'selected' : '').'>Оригинал счета не получен</option>
      <option value="1" '.(($type8['original'] == 1) ? 'selected' : '').'>Оригинал счета получен</option>
      </select>
      </form>
      </td>
      </tr></table></td>
      <td align="center" class="linkz"><!--<a class="t-1" href="/get-payment-archive-optima/'.$year.'/'.$month.'/optima-tp/" title="Скачать архивом" ></a>--></td>
      </tr>';
      }

      }

      }// оптима


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
if ($add_report == 1) {
$userID = 2;
}
?>
<!doctype html>
<html>
<head>
<meta charset=utf-8>
<title>Счета и Акты прямые</title>
<link href="/css/fonts.css" rel="stylesheet" />
<link href="/css/style.css" rel="stylesheet" />
<script src="/_new-codebase/front/vendor/jquery/jquery.min.js"></script>
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


<script  src="/_new-codebase/front/vendor/datatables/1.10.12/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="/css/datatables.css">
<style>
.nohov tr:hover {
    background-color: transparent !important;
    box-shadow: initial !important;
}

.ui-selectmenu-button:after {
    right: 10px;
}

table.dataTable tbody tr {
    background: none;
}
.ui-selectmenu-button {
width: 275px;
}
.min_width .ui-selectmenu-button {
width: 160px;
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

    $(document).on('selectmenuchange', 'select[name=status_pay]', function() {
        var value = $(this).val();
        var id= $(this).data('pay-id');
              if (value) {

                  $.get( "/ajax.php?type=update_pay_status&value="+value+"&id="+id, function( data ) {

                  });

              }


        return false;
    });

    $(document).on('selectmenuchange', 'select[name=status_pay_custom]', function() {
        var value = $(this).val();
        var id= $(this).data('pay-id');
              if (value) {

                  $.get( "/ajax.php?type=update_pay_status_custom&value="+value+"&id="+id, function( data ) {

                  });

              }


        return false;
    });

    $(document).on('selectmenuchange', 'select[name=status_act]', function() {
        var value = $(this).val();
        var id= $(this).data('pay-id');
              if (value) {

                  $.get( "/ajax.php?type=update_act_status&value="+value+"&id="+id, function( data ) {

                  });

              }


        return false;
    });

    $(document).on('selectmenuchange', 'select[name=status_act_custom]', function() {
        var value = $(this).val();
        var id= $(this).data('pay-id');
              if (value) {

                  $.get( "/ajax.php?type=update_act_status_custom&value="+value+"&id="+id, function( data ) {

                  });

              }


        return false;
    });

    $(document).on('click', '.delete_loan', function() {
        var value = $(this).data('id');
        var this_tr = $(this).parent().parent();
              if (value) {

                  $.get( "/ajax.php?type=remove_loan&value="+value, function( data ) {
                  this_tr.hide();

                  });

              }


        return false;
    });

    $(document).on('selectmenuchange', 'select[name=status_bill]', function() {
        var value = $(this).val();
        var id= $(this).data('pay-id');
              if (value) {

                  $.get( "/ajax.php?type=update_bill_status&value="+value+"&id="+id, function( data ) {

                  });

              }


        return false;
    });

    $(document).on('selectmenuchange', 'select[name=status_bill_custom]', function() {
        var value = $(this).val();
        var id= $(this).data('pay-id');
              if (value) {

                  $.get( "/ajax.php?type=update_bill_status_custom&value="+value+"&id="+id, function( data ) {

                  });

              }


        return false;
    });

} );

</script>

  <!-- New codebase -->
  <link rel="stylesheet" href="/_new-codebase/front/components/the-table/css/the-table.css">
  <link rel="stylesheet" href="/_new-codebase/front/templates/main/css/pagination.css">
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
           <h2 style="margin-bottom: 12px;"><?php if ($userID == 2) { echo 'Счета и Акты прямые'; } else { echo 'Платежные документы'; } ?></h2>
           <?= '<p>' . $content['dates_interval'] . '</p>'; ?>

  <div class="adm-catalog">

           <div style="display: flex;margin: 20px 0;align-items: center;justify-content: space-between;">
        <div>
       
        <?php if ($userID == 33) { ?>
           <div class="add" style="padding-top:0px;display:inline-block;">
     <a style="width: auto;padding-left: 7px;padding-right: 7px;vertical-align: middle;" href="/refresh-sc/" class="button">Обновить данные (t~1m)</a>
    </div>
        <? } ?>
         <?php if ($userID == 2) { ?>
                <div class="add" style="padding-top:0px;display:inline-block;">
     <a style="width: auto;padding-left: 7px;padding-right: 7px;vertical-align: middle;" href="/add-manual-doc/" class="button">Добавить документ</a>
    </div>
        <?php } ?>
    <form method="POST" id="checkb">
    <table>
              <tr>
    <td style="padding: 20px; ">Оплаченные <input type="checkbox" value="1" name="payed" <?=($_COOKIE['payed'] == '1') ? 'checked' : '';?>> &nbsp;&nbsp;&nbsp;&nbsp; Неоплаченные <input type="checkbox" value="1" name="notpayed" <?=($_COOKIE['notpayed'] == 1 ) ? 'checked' : '';?> >&nbsp;&nbsp;&nbsp;&nbsp;<!-- TESLER <input type="checkbox" value="1" name="tesler" <?=($_COOKIE['tesler'] == 1 ) ? 'checked' : '';?> >--><br></td>
    </tr>

            </table>
        </form>

        </div>
        <form method="POST" style="display: flex;column-gap: 32px;">
        <div style="width: 350px">
          <label style="display: block">СЦ:</label>
          <select name="service_id" class="nomenu select2" style="width: 100%;">
            <option value="">Все</option>
          <?php 
          $services = Services::getServicesList();
          $curServiceID = $_POST['service_id'] ?? '';
          foreach($services as $id => $name) {
            $sel = ($curServiceID == $id) ? 'selected' : '';
            echo '<option value="'.$id.'" '.$sel.'>'.$name.'</option>';
          }
          ?>
          </select>
          </div>
        <div style="width: 100px">
          <label>Квартал:</label>
          <select name="qrt" class="nomenu" style="width: 100%">
            <option value="">Все</option>
          <?php 
          $curQrt = $_POST['qrt'] ?? 1;
          foreach(range(1, 4) as $qrt) {
            $sel = ($curQrt == $qrt) ? 'selected' : '';
            echo '<option value="'.$qrt.'" '.$sel.'>'.$qrt.'</option>';
          }
          ?>
          </select>
          </div>
          <div style="width: 100px">
          <label>Год:</label>
          <select name="year" class="nomenu" style="width: 100%">
          <option value="">Все</option>
          <?php 
          $curYear = $_POST['year'] ?? date('Y');
          foreach(range(2017, date('Y')) as $year) {
            $sel = ($curYear == $year) ? 'selected' : '';
            echo '<option value="'.$year.'" '.$sel.'>'.$year.'</option>';
          }
          ?>
          </select>
          </div>
          <div>
            <label style="opacity:0">-</label>
            <button type="submit" style="display: block;height: 54px;padding: 0 16px;">Ok</button>
          </div>
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
</body>
</html>