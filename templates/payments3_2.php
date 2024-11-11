<?php

use models\Services;
use program\core;
require '_new-codebase/front/templates/main/parts/common.php';

$total = 0;

if ($_POST['payed'] == 1) {
setcookie("payed", "1", time()+3600*60*31, "/");
$_COOKIE['payed'] = 1;
setcookie("notpayed", "", time()-3600*60*31, "/");
$_COOKIE['notpayed'] = 0;
}

if ($_POST['notpayed'] == 1) {
setcookie("notpayed", "1", time()+3600*60*31, "/");
$_COOKIE['notpayed'] = 1;
setcookie("payed", "", time()-3600*60*31, "/");
$_COOKIE['payed'] = 0;
}

setcookie("tesler", "1", time()+3600*60*31, "/");
$_COOKIE['tesler'] = 1;


if ($_POST['add_to_black']) {
mysqli_query($db, 'UPDATE `pay_billing` SET `custom_loan` = 1 where `service_id` = '.$_POST['add_to_black'].' LIMIT 1;') or mysqli_error($db);
header('Location: '.$_SERVER["HTTP_REFERER"]);
}

function get_request_info($id) {
  global $db;
return mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `requests` WHERE `user_id` = '.$id));
}

function get_request_info_serice($id) {
  global $db;
$req = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `requests` WHERE `user_id` = '.$id));
return $req;
}

function content_list() {
  global $db, $total;

  $content_list = ['table_html' => '', 'dates_interval' => '', 'pagination_html' => ''];
  $qrt = $_POST['qrt'] ?? 1;
  $year = $_POST['year'] ?? date('Y');
  $dates = getDatesIntervalByQuarter($qrt, $year);
  $content_list['dates_interval'] = '';
  if($dates){
    $content_list['dates_interval'] = core\Time::formatVerbose($dates['from']) . ' - ' . core\Time::formatVerbose($dates['to']);
  }


$dated = ((date("d") < 5) ? date("Y.m") : date("Y.m"));

if ($_GET['service_id'] && $_GET['service_id'] != 'all') {

if ($_GET['year']) {
$where_year = 'and `app_date` REGEXP \''.$_GET['year'].'\'';
}
if ($_GET['date']) {
$where_date = 'and `app_date` REGEXP \''.$_GET['date'].'\'';
}


$sql = mysqli_query($db, 'SELECT `app_date` FROM `repairs` where `service_id` = '.$_GET['service_id'].' and `service_id` != 33 and `status_admin` IN ("Подтвержден", "Выдан") and `deleted` = 0 and  `app_date` NOT REGEXP \''.$dated.'\' '.$where_year.'  order by `id` DESC;');
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

      $type1 = create_or_get_payment_id($_GET['service_id'], $year, $month, 1); //акт
      $type2 = create_or_get_payment_id($_GET['service_id'], $year, $month, 2); //счет

      /*Проверяем теслер*/
      $sql3 = mysqli_query($db, 'SELECT `model_id` FROM `repairs` where `service_id` = '.$_GET['service_id'].' and `service_id` != 33 and `app_date` REGEXP \''.$year.'.'.$month.'\' and (`status_admin` = \'Подтвержден\' OR `status_admin` = \'Выдан\') and `deleted` = 0 and `app_date` NOT REGEXP \''.$dated.'\' group by `model_id` order by `id` DESC ;');
      while ($row3 = mysqli_fetch_array($sql3)) {
      $model = model_info($row3['model_id']);

      if ($model['brand'] == 'TESLER') {
        $tesler = 1;
      }

      if ($model['brand'] == 'ROCH') {
        $roch = 1;
      }

      if ($model['brand'] == 'SELENGA') {
        $selenga = 1;
      }

     }

      if ($tesler == 1) {
      $type3 = create_or_get_payment_id($_GET['service_id'], $year, $month, 3); //акт
      $type4 = create_or_get_payment_id($_GET['service_id'], $year, $month, 4); //счет
      }

       if ($selenga == 1) {

      $type13 = create_or_get_payment_id($_GET['service_id'], $year, $month, 13); //акт
      $type14 = create_or_get_payment_id($_GET['service_id'], $year, $month, 14); //счет
      }

      if ($roch == 1) {
        $rochAct = create_or_get_payment_id($_GET['service_id'], $year, $month, 15); //акт
        $rochBill = create_or_get_payment_id($_GET['service_id'], $year, $month, 16); //счет
        }

      /*/Проверка теслера*/

      // Оплаченные харпер
      if ($_COOKIE['payed'] == 1) {


      if ($type2['status'] == 1) {

      if (!$type2['sum']) {
      $summ = get_service_summ_fast($_GET['service_id'], $month, $year, 'HARPER', 0, true);
      } else {
      $summ = $type2['sum'];
      }

      if ($summ  && $summ != '-' && $type1['sended'] != 1 && ($type2['status'] == 0 || ($type1['original'] == 0 || $type2['original'] == 0))) {
      $total += $summ;
  
      $content_list['table_html'] .= '<tr><td>'.$year.'.'.$month.'<br><u>HARPER</u></td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-act-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/">Акт оплаченных работ</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-bill-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/">Счет на оплату</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-detail-report/?service-id='.$type1['service_id'].'&month='.$month.'&year='.$year.'&brand=harper">Отчет</a></td>';
      $content_list['table_html'] .= '<td>'.$summ.',00 руб.</td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type2['id'].'">
      <option value="0" '.(($type2['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type2['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov">
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
      $content_list['table_html'] .= '<td><div class="linkz">'.(($type1['original'] == 1 && $type2['original'] == 1) ? '<a class="t-2" style="display:block" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/" title="Отправить на оплату" ></a>' : '<a class="t-2" style="display:none" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/" title="Отправить на оплату" ></a>').' <a class="t-1" href="/get-payment-archive-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/" title="Скачать архивом" ></a></div></td>';
      $content_list['table_html'] .= '<td>'.$year.'.'.$month.'</td>';
      $content_list['table_html'] .= '</tr>';
      }
      }

      // Оплаченные Теслер
      if ($tesler == 1 && $_COOKIE['tesler'] == 1) {

      if ($type4['status'] == 1) {


      if (!$type4['sum']) {
      $summ = get_service_summ_fast($_GET['service_id'], $month, $year, 'TESLER', 0, true);
      } else {
      $summ = $type4['sum'];
      }

      if ($summ  && $summ != '-'  && $type3['sended'] != 1 && ($type4['status'] == 0 || ($type3['original'] == 0 || $type4['original'] == 0))) {
            $total += $summ;
  
      $content_list['table_html'] .= '<tr><td>'.$year.'.'.$month.'<br><u>TESLER</u></td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-act-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/tesler/">Акт оплаченных работ</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-bill-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/tesler/">Счет на оплату</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-detail-report/?service-id='.$type3['service_id'].'&month='.$month.'&year='.$year.'&brand=tesler">Отчет</a></td>';
      $content_list['table_html'] .= '<td>'.$summ.',00 руб.</td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type4['id'].'">
      <option value="0" '.(($type4['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type4['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov">
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
      $content_list['table_html'] .= '<td><div class="linkz">'.(($type4['original'] == 1 && $type3['original'] == 1) ? '<a class="t-2" style="display:block" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/" title="Отправить на оплату" ></a>' : '<a class="t-2" style="display:none" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/" title="Отправить на оплату" ></a>').' <a class="t-1" href="/get-payment-archive-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/tesler/" title="Скачать архивом" ></a>'.(check_combined($type3['id'], $type4['id']) ? '<br><br><input type="checkbox" value="1" name="combine" data-act-id="'.$type3['id'].'" data-bill-id="'.$type4['id'].'">' : '').'</div></td>';
      $content_list['table_html'] .= '<td>'.$year.'.'.$month.'</td>';
      $content_list['table_html'] .= '</tr>';
      }
      }
      }

      if ($roch == 1) {

        if ($rochBill['status'] == 1) {
  
  
        if (!$rochBill['sum']) {
        $summ = get_service_summ_fast($_GET['service_id'], $month, $year, 'ROCH', 0, true);
        } else {
        $summ = $rochBill['sum'];
        }
  
        if ($summ  && $summ != '-'  && $rochAct['sended'] != 1 && ($rochBill['status'] == 0 || ($rochAct['original'] == 0 || $rochBill['original'] == 0))) {
              $total += $summ;
    
        $content_list['table_html'] .= '<tr><td>'.$year.'.'.$month.'<br><u>ROCH</u></td>';
        $content_list['table_html'] .= '<td><a href="/get-payment-act-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/roch/">Акт оплаченных работ</a></td>';
        $content_list['table_html'] .= '<td><a href="/get-payment-bill-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/roch/">Счет на оплату</a></td>';
        $content_list['table_html'] .= '<td><a href="/get-detail-report/?service-id='.$rochAct['service_id'].'&month='.$month.'&year='.$year.'&brand=roch">Отчет</a></td>';
        $content_list['table_html'] .= '<td>'.$summ.',00 руб.</td>';
        $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
        <form method="POST">
        <select  name="status_pay" data-pay-id="'.$rochBill['id'].'">
        <option value="0" '.(($rochBill['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
        <option value="1" '.(($rochBill['status'] == 1) ? 'selected' : '').'>Оплачено</option>
        </select>
        </form>
        </td></tr></table></td>';
        $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov">
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
        $content_list['table_html'] .= '<td><div class="linkz">'.(($rochBill['original'] == 1 && $rochAct['original'] == 1) ? '<a class="t-2" style="display:block" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/roch/" title="Отправить на оплату" ></a>' : '<a class="t-2" style="display:none" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/roch/" title="Отправить на оплату" ></a>').' <a class="t-1" href="/get-payment-archive-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/roch/" title="Скачать архивом" ></a>'.(check_combined($rochAct['id'], $rochBill['id']) ? '<br><br><input type="checkbox" value="1" name="combine" data-act-id="'.$rochAct['id'].'" data-bill-id="'.$rochBill['id'].'">' : '').'</div></td>';
        $content_list['table_html'] .= '<td>'.$year.'.'.$month.'</td>';
        $content_list['table_html'] .= '</tr>';
        }
        }
        }

      // Оплаченные Теслер
      if ($selenga == 1) {

      if ($type14['status'] == 1) {

      if (!$type14['sum']) {
      $summ = get_service_summ_fast($_GET['service_id'], $month, $year, 'SELENGA', 0, true);
      } else {
      $summ = $type14['sum'];
      }

      if ($summ  && $summ != '-'  && $type13['sended'] != 1 && ($type14['status'] == 0 || ($type13['original'] == 0 || $type14['original'] == 0))) {
            $total += $summ;
      $content_list['table_html'] .= '<tr><td>'.$year.'.'.$month.'<br><u>SELENGA</u></td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-act-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/selenga-brand/">Акт оплаченных работ</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-bill-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/selenga-bran/">Счет на оплату</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-detail-report/?service-id='.$type14['service_id'].'&month='.$month.'&year='.$year.'&brand=selenga-brand">Отчет</a></td>';
      $content_list['table_html'] .= '<td>'.$summ.',00 руб.</td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type14['id'].'">
      <option value="0" '.(($type14['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type14['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov">
      <tr>
      <td>
      <form method="POST">
      <select  name="status_act" data-pay-id="'.$type13['id'].'">
      <option value="0" '.(($type13['original'] == 0) ? 'selected' : '').'>Оригинал акта не получен</option>
      <option value="1" '.(($type13['original'] == 1) ? 'selected' : '').'>Оригинал акта получен</option>
      </select>
      </form>
      </td>
      </tr>
      <tr>
      <td>
      <form method="POST">
      <select  name="status_bill" data-pay-id="'.$type14['id'].'">
      <option value="0" '.(($type14['original'] == 0) ? 'selected' : '').'>Оригинал счета не получен</option>
      <option value="1" '.(($type14['original'] == 1) ? 'selected' : '').'>Оригинал счета получен</option>
      </select>
      </form>
      </td>
      </tr></table></td>';
      $content_list['table_html'] .= '<td><div class="linkz">'.(($type14['original'] == 1 && $type13['original'] == 1) ? '<a class="t-2" style="display:block" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/selenga/" title="Отправить на оплату" ></a>' : '<a class="t-2" style="display:none" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/selenga/" title="Отправить на оплату" ></a>').' <a class="t-1" href="/get-payment-archive-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/selenga/" title="Скачать архивом" ></a>'.(check_combined($type13['id'], $type14['id']) ? '<br><br><input type="checkbox" value="1" name="combine" data-act-id="'.$type13['id'].'" data-bill-id="'.$type14['id'].'">' : '').'</div></td>';
      $content_list['table_html'] .= '<td>'.$year.'.'.$month.'</td>';
      $content_list['table_html'] .= '</tr>';
      }
      }
      }

      }

      // Не оплаченные харпер
      if ($_COOKIE['notpayed'] == 1) {

      if ($type2['status'] == 0) {


      if (!$type2['sum']) {
      $summ = get_service_summ_fast($_GET['service_id'], $month, $year, 'HARPER', 0, true);
      } else {
      $summ = $type2['sum'];
      }

      if ($summ  && $summ != '-'  && $type1['sended'] != 1 && ($type2['status'] == 0 || ($type1['original'] == 0 || $type2['original'] == 0))) {
            $total += $summ;

      $content_list['table_html'] .= '<tr><td>'.$year.'.'.$month.'<br><u>HARPER</u></td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-act-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/">Акт оплаченных работ</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-bill-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/">Счет на оплату</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-detail-report/?service-id='.$type1['service_id'].'&month='.$month.'&year='.$year.'&brand=harper">Отчет</a></td>';
      $content_list['table_html'] .= '<td>'.$summ.',00 руб.</td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type2['id'].'">
      <option value="0" '.(($type2['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type2['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov">
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
      $content_list['table_html'] .= '<td><div class="linkz">'.(($type2['original'] == 1 && $type1['original'] == 1) ? '<a class="t-2" style="display:block" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/" title="Отправить на оплату" ></a>' : '<a class="t-2" style="display:none" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/" title="Отправить на оплату" ></a>').' <a class="t-1" href="/get-payment-archive-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/" title="Скачать архивом" ></a>'.(check_combined($type1['id'], $type2['id']) ? '<br><br><input type="checkbox" value="1" name="combine" data-act-id="'.$type1['id'].'" data-bill-id="'.$type2['id'].'">' : '').'</div></td>';
      $content_list['table_html'] .= '<td>'.$year.'.'.$month.'</td>';
      $content_list['table_html'] .= '</tr>';
      }


      //Не оплаченные теслер
      if ($tesler == 1 && $_COOKIE['tesler'] == 1) {


      if (!$type4['sum']) {
      $summ = get_service_summ_fast($_GET['service_id'], $month, $year, 'TESLER', 0, true);
      } else {
      $summ = $type4['sum'];
      }

      if ($summ  && $summ != '-'  && $type3['sended'] != 1 && ($type4['status'] == 0 || ($type3['original'] == 0 || $type4['original'] == 0))) {
            $total += $summ;

      $content_list['table_html'] .= '<tr><td>'.$year.'.'.$month.'<br><u>TESLER</u></td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-act-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/tesler/">Акт оплаченных работ</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-bill-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/tesler/">Счет на оплату</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-detail-report/?service-id='.$type4['service_id'].'&month='.$month.'&year='.$year.'&brand=tesler">Отчет</a></td>';
      $content_list['table_html'] .= '<td>'.$summ.',00 руб.</td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type4['id'].'">
      <option value="0" '.(($type4['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type4['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov">
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
      $content_list['table_html'] .= '<td><div class="linkz">'.(($type4['original'] == 1 && $type3['original'] == 1) ? '<a class="t-2" style="display:block" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/" title="Отправить на оплату" ></a>' : '<a class="t-2" style="display:none" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/" title="Отправить на оплату" ></a>').' <a class="t-1" href="/get-payment-archive-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/tesler/" title="Скачать архивом" ></a>'.(check_combined($type3['id'], $type4['id']) ? '<br><br><input type="checkbox" value="1" name="combine" data-act-id="'.$type3['id'].'" data-bill-id="'.$type4['id'].'">' : '').'</div></td>';
      $content_list['table_html'] .= '<td>'.$year.'.'.$month.'</td>';
      $content_list['table_html'] .= '</tr>';
      }
      }


      if ($roch == 1) {

        if (!$rochBill['sum']) {
        $summ = get_service_summ_fast($_GET['service_id'], $month, $year, 'ROCH', 0, true);
        } else {
        $summ = $rochBill['sum'];
        }
  
        if ($summ  && $summ != '-'  && $rochAct['sended'] != 1 && ($rochBill['status'] == 0 || ($rochAct['original'] == 0 || $rochBill['original'] == 0))) {
              $total += $summ;
  
        $content_list['table_html'] .= '<tr><td>'.$year.'.'.$month.'<br><u>TESLER</u></td>';
        $content_list['table_html'] .= '<td><a href="/get-payment-act-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/tesler/">Акт оплаченных работ</a></td>';
        $content_list['table_html'] .= '<td><a href="/get-payment-bill-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/tesler/">Счет на оплату</a></td>';
        $content_list['table_html'] .= '<td><a href="/get-detail-report/?service-id='.$rochBill['service_id'].'&month='.$month.'&year='.$year.'&brand=tesler">Отчет</a></td>';
        $content_list['table_html'] .= '<td>'.$summ.',00 руб.</td>';
        $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
        <form method="POST">
        <select  name="status_pay" data-pay-id="'.$rochBill['id'].'">
        <option value="0" '.(($rochBill['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
        <option value="1" '.(($rochBill['status'] == 1) ? 'selected' : '').'>Оплачено</option>
        </select>
        </form>
        </td></tr></table></td>';
        $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov">
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
        $content_list['table_html'] .= '<td><div class="linkz">'.(($rochBill['original'] == 1 && $rochAct['original'] == 1) ? '<a class="t-2" style="display:block" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/" title="Отправить на оплату" ></a>' : '<a class="t-2" style="display:none" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/" title="Отправить на оплату" ></a>').' <a class="t-1" href="/get-payment-archive-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/tesler/" title="Скачать архивом" ></a>'.(check_combined($rochAct['id'], $rochBill['id']) ? '<br><br><input type="checkbox" value="1" name="combine" data-act-id="'.$rochAct['id'].'" data-bill-id="'.$rochBill['id'].'">' : '').'</div></td>';
        $content_list['table_html'] .= '<td>'.$year.'.'.$month.'</td>';
        $content_list['table_html'] .= '</tr>';
        }
        }


      if ($selenga == 1) {


      if (!$type14['sum']) {
      $summ = get_service_summ_fast($_GET['service_id'], $month, $year, 'SELENGA', 0, true);
      } else {
      $summ = $type14['sum'];
      }

      if ($summ  && $summ != '-'  && $type13['sended'] != 1 && ($type14['status'] == 0 || ($type13['original'] == 0 || $type14['original'] == 0))) {
            $total += $summ;

      $content_list['table_html'] .= '<tr><td>'.$year.'.'.$month.'<br><u>SELENGA</u></td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-act-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/selenga-brand/">Акт оплаченных работ</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-bill-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/selenga-brand/">Счет на оплату</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-detail-report/?service-id='.$type14['service_id'].'&month='.$month.'&year='.$year.'&brand=selenga-brand">Отчет</a></td>';
      $content_list['table_html'] .= '<td>'.$summ.',00 руб.</td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type14['id'].'">
      <option value="0" '.(($type14['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type14['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov">
      <tr>
      <td>
      <form method="POST">
      <select  name="status_act" data-pay-id="'.$type13['id'].'">
      <option value="0" '.(($type13['original'] == 0) ? 'selected' : '').'>Оригинал акта не получен</option>
      <option value="1" '.(($type13['original'] == 1) ? 'selected' : '').'>Оригинал акта получен</option>
      </select>
      </form>
      </td>
      </tr>
      <tr>
      <td>
      <form method="POST">
      <select  name="status_bill" data-pay-id="'.$type14['id'].'">
      <option value="0" '.(($type14['original'] == 0) ? 'selected' : '').'>Оригинал счета не получен</option>
      <option value="1" '.(($type14['original'] == 1) ? 'selected' : '').'>Оригинал счета получен</option>
      </select>
      </form>
      </td>
      </tr></table></td>';
      $content_list['table_html'] .= '<td><div class="linkz">'.(($type14['original'] == 1 && $type13['original'] == 1) ? '<a class="t-2" style="display:block" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/selenga/" title="Отправить на оплату" ></a>' : '<a class="t-2" style="display:none" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/selenga/" title="Отправить на оплату" ></a>').' <a class="t-1" href="/get-payment-archive-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/selenga/" title="Скачать архивом" ></a>'.(check_combined($type13['id'], $type14['id']) ? '<br><br><input type="checkbox" value="1" name="combine" data-act-id="'.$type13['id'].'" data-bill-id="'.$type14['id'].'">' : '').'</div></td>';
      $content_list['table_html'] .= '<td>'.$year.'.'.$month.'</td>';
      $content_list['table_html'] .= '</tr>';
      }
      }

      }
      }

      // Все остальные харпер
      if ($_COOKIE['notpayed'] != 1 && $_COOKIE['payed'] != 1) {


      if (!$type2['sum']) {
      $summ = get_service_summ_fast($_GET['service_id'], $month, $year, 'HARPER', 0, true);
      } else {
      $summ = $type2['sum'];
      }

      if ($summ  && $summ != '-'  && $type1['sended'] != 1 && ($type2['status'] == 0 || ($type1['original'] == 0 || $type2['original'] == 0))) {
            $total += $summ;

      $content_list['table_html'] .= '<tr><td>'.$year.'.'.$month.'<br><u>HARPER</u></td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-act-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/">Акт оплаченных работ</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-bill-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/">Счет на оплату</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-detail-report/?service-id='.$type1['service_id'].'&month='.$month.'&year='.$year.'&brand=harper">Отчет</a></td>';
      $content_list['table_html'] .= '<td>'.$summ.',00 руб.</td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type2['id'].'">
      <option value="0" '.(($type2['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type2['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov">
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
      $content_list['table_html'] .= '<td><div class="linkz">'.(($type1['original'] == 1 && $type2['original'] == 1) ? '<a class="t-2" style="display:block" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/" title="Отправить на оплату" ></a>' : '<a class="t-2" style="display:none" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/" title="Отправить на оплату" ></a>').' <a class="t-1" href="/get-payment-archive-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/" title="Скачать архивом" ></a>'.(check_combined($type1['id'], $type2['id']) ? '<br><br><input type="checkbox" value="1" name="combine" data-act-id="'.$type1['id'].'" data-bill-id="'.$type2['id'].'">' : '').'</div></td>';
      $content_list['table_html'] .= '<td>'.$year.'.'.$month.'</td>';
      $content_list['table_html'] .= '</tr>';
      }

      // Все остальные теслер
      if ($tesler == 1 && $_COOKIE['tesler'] == 1) {


      if (!$type4['sum']) {
      $summ = get_service_summ_fast($_GET['service_id'], $month, $year, 'TESLER', 0, true);
      } else {
      $summ = $type4['sum'];
      }

      if ($summ  && $summ != '-'  && $type3['sended'] != 1 && ($type4['status'] == 0 || ($type3['original'] == 0 || $type4['original'] == 0))) {
            $total += $summ;

      $content_list['table_html'] .= '<tr><td>'.$year.'.'.$month.'<br><u>TESLER</u></td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-act-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/tesler/">Акт оплаченных работ</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-bill-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/tesler/">Счет на оплату</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-detail-report/?service-id='.$type3['service_id'].'&month='.$month.'&year='.$year.'&brand=tesler">Отчет</a></td>';
      $content_list['table_html'] .= '<td>'.$summ.',00 руб.</td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type4['id'].'">
      <option value="0" '.(($type4['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type4['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov">
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
      $content_list['table_html'] .= '<td><div class="linkz">'.(($type4['original'] == 1 && $type3['original'] == 1) ? '<a class="t-2" style="display:block" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/" title="Отправить на оплату" ></a>' : '<a class="t-2" style="display:none" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/" title="Отправить на оплату" ></a>').' <a class="t-1" href="/get-payment-archive-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/tesler/" title="Скачать архивом" ></a>'.(check_combined($type3['id'], $type4['id']) ? '<br><br><input type="checkbox" value="1" name="combine" data-act-id="'.$type3['id'].'" data-bill-id="'.$type4['id'].'">' : '').'</div></td>';
      $content_list['table_html'] .= '<td>'.$year.'.'.$month.'</td>';
      $content_list['table_html'] .= '</tr>';
      }
      }


      if ($roch == 1) {
        if (!$rochBill['sum']) {
        $summ = get_service_summ_fast($_GET['service_id'], $month, $year, 'ROCH', 0, true);
        } else {
        $summ = $rochBill['sum'];
        }
  
        if ($summ  && $summ != '-'  && $rochAct['sended'] != 1 && ($rochBill['status'] == 0 || ($rochAct['original'] == 0 || $rochBill['original'] == 0))) {
              $total += $summ;
  
        $content_list['table_html'] .= '<tr><td>'.$year.'.'.$month.'<br><u>ROCH</u></td>';
        $content_list['table_html'] .= '<td><a href="/get-payment-act-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/roch/">Акт оплаченных работ</a></td>';
        $content_list['table_html'] .= '<td><a href="/get-payment-bill-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/roch/">Счет на оплату</a></td>';
        $content_list['table_html'] .= '<td><a href="/get-detail-report/?service-id='.$rochAct['service_id'].'&month='.$month.'&year='.$year.'&brand=roch">Отчет</a></td>';
        $content_list['table_html'] .= '<td>'.$summ.',00 руб.</td>';
        $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
        <form method="POST">
        <select  name="status_pay" data-pay-id="'.$rochBill['id'].'">
        <option value="0" '.(($rochBill['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
        <option value="1" '.(($rochBill['status'] == 1) ? 'selected' : '').'>Оплачено</option>
        </select>
        </form>
        </td></tr></table></td>';
        $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov">
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
        $content_list['table_html'] .= '<td><div class="linkz">'.(($rochBill['original'] == 1 && $rochAct['original'] == 1) ? '<a class="t-2" style="display:block" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/roch/" title="Отправить на оплату" ></a>' : '<a class="t-2" style="display:none" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/roch/" title="Отправить на оплату" ></a>').' <a class="t-1" href="/get-payment-archive-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/roch/" title="Скачать архивом" ></a>'.(check_combined($rochAct['id'], $rochBill['id']) ? '<br><br><input type="checkbox" value="1" name="combine" data-act-id="'.$rochAct['id'].'" data-bill-id="'.$rochBill['id'].'">' : '').'</div></td>';
        $content_list['table_html'] .= '<td>'.$year.'.'.$month.'</td>';
        $content_list['table_html'] .= '</tr>';
        }
        }

      if ($selenga == 1) {

      if (!$type14['sum']) {
      $summ = get_service_summ_fast($_GET['service_id'], $month, $year, 'SELENGA', 0, true);
      } else {
      $summ = $type14['sum'];
      }

      if ($summ  && $summ != '-'  && $type13['sended'] != 1 && ($type14['status'] == 0 || ($type13['original'] == 0 || $type14['original'] == 0))) {
            $total += $summ;

      $content_list['table_html'] .= '<tr><td>'.$year.'.'.$month.'<br><u>SELENGA</u></td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-act-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/selenga-brand/">Акт оплаченных работ</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-bill-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/selenga-brand/">Счет на оплату</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-detail-report/?service-id='.$type14['service_id'].'&month='.$month.'&year='.$year.'&brand=selenga-brand">Отчет</a></td>';
      $content_list['table_html'] .= '<td>'.$summ.',00 руб.</td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type14['id'].'">
      <option value="0" '.(($type14['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type14['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov">
      <tr>
      <td>
      <form method="POST">
      <select  name="status_act" data-pay-id="'.$type13['id'].'">
      <option value="0" '.(($type13['original'] == 0) ? 'selected' : '').'>Оригинал акта не получен</option>
      <option value="1" '.(($type13['original'] == 1) ? 'selected' : '').'>Оригинал акта получен</option>
      </select>
      </form>
      </td>
      </tr>
      <tr>
      <td>
      <form method="POST">
      <select  name="status_bill" data-pay-id="'.$type14['id'].'">
      <option value="0" '.(($type14['original'] == 0) ? 'selected' : '').'>Оригинал счета не получен</option>
      <option value="1" '.(($type14['original'] == 1) ? 'selected' : '').'>Оригинал счета получен</option>
      </select>
      </form>
      </td>
      </tr></table></td>';
      $content_list['table_html'] .= '<td><div class="linkz">'.(($type14['original'] == 1 && $type13['original'] == 1) ? '<a class="t-2" style="display:block" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/selenga/" title="Отправить на оплату" ></a>' : '<a class="t-2" style="display:none" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/selenga/" title="Отправить на оплату" ></a>').' <a class="t-1" href="/get-payment-archive-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/selenga/" title="Скачать архивом" ></a>'.(check_combined($type13['id'], $type14['id']) ? '<br><br><input type="checkbox" value="1" name="combine" data-act-id="'.$type13['id'].'" data-bill-id="'.$type14['id'].'">' : '').'</div></td>';
      $content_list['table_html'] .= '<td>'.$year.'.'.$month.'</td>';
      $content_list['table_html'] .= '</tr>';
      }
      }

      }

            unset($model);
      unset($tesler);



     }
     }

} else {

if ($_GET['date']) {
$where_date = 'and `app_date` REGEXP \''.$_GET['date'].'\'';
}
if ($_GET['year']) {                                                                                          /* and DATE(`app_date`) > \'2018.09.01\' */
$where_year = 'and `app_date` REGEXP \''.$_GET['year'].'\'';
}
$sql = mysqli_query($db, 'SELECT `app_date` FROM `repairs` 
where `app_date` != "" 
'.((!empty($_POST['service_id'])) ? ' AND `service_id` = ' . $_POST['service_id'] : ' AND `service_id` != 33 ').' 
'.(($dates) ? 'AND (`approve_date` BETWEEN "'.$dates['from'].'" AND "'.$dates['to'].'")' : '').' 
'.$where_year.' 
and `status_admin` IN ("Подтвержден", "Выдан") 
and `deleted` = 0 group by `app_date` order by `id` DESC;');

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


  $sql2 = mysqli_query($db, 'SELECT `service_id` FROM `repairs` where `app_date` REGEXP \''.$year.'.'.$month.'\' '.((!empty($_POST['service_id'])) ? ' AND `service_id` = ' . $_POST['service_id'] : ' AND `service_id` != 33 ').'  and `status_admin` IN ("Подтвержден", "Выдан") and `deleted` = 0 and `app_date` NOT REGEXP \''.$dated.'\' GROUP by `service_id`  order by `id` DESC ;');
      while ($row2 = mysqli_fetch_array($sql2)) {


      $type1 = create_or_get_payment_id($row2['service_id'], $year, $month, 1); //акт
      $type2 = create_or_get_payment_id($row2['service_id'], $year, $month, 2); //счет

      /*Проверяем теслер*/
      $sql3 = mysqli_query($db, 'SELECT `model_id` FROM `repairs` WHERE ' 
                          . ((!empty($_POST['service_id'])) ? ' `service_id` = ' . $_POST['service_id'] : ' `service_id` != 33 ')
                          . ' AND `app_date` REGEXP \'' . $year . '.' . $month . '\''
                          . ' AND `status_admin` IN ("Подтвержден", "Выдан")'
                          . ' AND `deleted` = 0'
                          . ' AND `status_id` != 6'
                          . ' AND `app_date` NOT REGEXP \'' . $dated . '\''
                          . ' GROUP BY `model_id`'
                          . ' ORDER BY `id` DESC;');

/*
if ($sql3) {
    
    echo '<table border="1">';
    echo '<tr><th>ID</th><th>Model ID</th></tr>';
    while ($row = mysqli_fetch_assoc($sql3)) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($row['id']) . '</td>';
        echo '<td>--' . htmlspecialchars($row['model_id']) . '</td>';
        echo '</tr>';
    }
    echo '</table>';
} else {
    
    echo 'Ошибка выполнения запроса: ' . mysqli_error($db);
}
*/


      while ($row3 = mysqli_fetch_array($sql3)) {
      $model = model_info($row3['model_id']);


      if ($model['brand'] == 'TESLER') {
        $tesler = 1;
      }

      if ($model['brand'] == 'ROCH') {
        $roch = 1;
      }

      if ($model['brand'] == 'SELENGA') {
        $selenga = 1;
      }
      }

      if ($tesler == 1) {
      $type3 = create_or_get_payment_id($row2['service_id'], $year, $month, 3); //акт
      $type4 = create_or_get_payment_id($row2['service_id'], $year, $month, 4); //счет
      }

      if ($roch == 1) {
        $rochBill = create_or_get_payment_id($row2['service_id'], $year, $month, 16); //акт
        $rochAct = create_or_get_payment_id($row2['service_id'], $year, $month, 15); //счет
      }

      if ($selenga == 1) {
      $type13 = create_or_get_payment_id($row2['service_id'], $year, $month, 13); //акт
      $type14 = create_or_get_payment_id($row2['service_id'], $year, $month, 14); //счет
      }

      /*/Проверка теслера*/
      $req = get_request_info_serice($row2['service_id']);

      if ($_COOKIE['notpayed'] == 1) {


      if ($type2['status'] == 0) {

      if ($_GET['impo'] != 1) {



      if (!$type2['sum']) {
      $summ = get_service_summ_fast($row2['service_id'], $month, $year, 'HARPER', 0, true);
      } else {
      $summ = $type2['sum'];
      }

      if ($summ  && $summ != '-'  && $type1['sended'] != 1  && ($type2['status'] == 0 || ($type1['original'] == 0 || $type2['original'] == 0))) {
      $total += $summ;

      $content_list['table_html'] .= '<tr><td>'.$year.'.'.$month.'<br><u>HARPER</u></td>';
      $content_list['table_html'] .= '<td>'.$req['name'].'</td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-act-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/">Акт оплаченных работ</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-bill-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/">Счет на оплату</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-detail-report/?service-id='.$type1['service_id'].'&month='.$month.'&year='.$year.'&brand=harper">Отчет</a></td>';
      $content_list['table_html'] .= '<td>'.$summ.',00 руб.</td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type2['id'].'">
      <option value="0" '.(($type2['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type2['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov">
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
      $content_list['table_html'] .= '<td><div class="linkz">'.(($type2['original'] == 1 && $type1['original'] == 1) ? '<a class="t-2" style="display:block" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/" title="Отправить на оплату" ></a>' : '<a class="t-2" style="display:none" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/" title="Отправить на оплату" ></a>').' <a class="t-1" href="/get-payment-archive-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/" title="Скачать архивом" ></a>'.(check_combined($type1['id'], $type2['id']) ? '<br><br><input type="checkbox" value="1" name="combine" data-act-id="'.$type1['id'].'" data-bill-id="'.$type2['id'].'">' : '').'</div></td>';
      $content_list['table_html'] .= '<td>'.$year.'.'.$month.'</td>';
      $content_list['table_html'] .= '</tr>';
      }
      }       else if (($type2['status'] == 1) and ($type1['original'] == 0 || $type2['original'] == 0 )) {



      if (!$type2['sum']) {
      $summ = get_service_summ_fast($row2['service_id'], $month, $year, 'HARPER', 0, true);
      } else {
      $summ = $type2['sum'];
      }

      if ($summ  && $summ != '-'  && $type1['sended'] != 1  && ($type2['status'] == 0 || ($type1['original'] == 0 || $type2['original'] == 0))) {
      $total += $summ;

      $content_list['table_html'] .= '<tr><td>'.$year.'.'.$month.'<br><u>HARPER</u></td>';
      $content_list['table_html'] .= '<td>'.$req['name'].'</td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-act-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/">Акт оплаченных работ</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-bill-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/">Счет на оплату</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-detail-report/?service-id='.$type1['service_id'].'&month='.$month.'&year='.$year.'&brand=harper">Отчет</a></td>';
      $content_list['table_html'] .= '<td>'.$summ.',00 руб.</td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type2['id'].'">
      <option value="0" '.(($type2['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type2['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov">
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
      $content_list['table_html'] .= '<td><div class="linkz">'.(($type1['original'] == 1 && $type2['original'] == 1) ? '<a class="t-2" style="display:block" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/" title="Отправить на оплату" ></a>' : '<a class="t-2" style="display:none" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/" title="Отправить на оплату" ></a>').' <a class="t-1" href="/get-payment-archive-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/" title="Скачать архивом" ></a>'.(check_combined($type1['id'], $type2['id']) ? '<br><br><input type="checkbox" value="1" name="combine" data-act-id="'.$type1['id'].'" data-bill-id="'.$type2['id'].'">' : '').'<br>'.((check_custom_loan($row2['service_id'])) ? '<a title="Удалить из списка" class="t-5 delete_loan" data-id="'.$row2['service_id'].'" style="" href="#"></a>' : '').'</div></td>';
      $content_list['table_html'] .= '<td>'.$year.'.'.$month.'</td>';
      $content_list['table_html'] .= '</tr>';
      }
      }
       }

      //TESLER
      if ($tesler == 1 && $_COOKIE['tesler'] == 1) {

      if ($_GET['impo'] != 1) {


      if (!$type4['sum']) {
      $summ = get_service_summ_fast($row2['service_id'], $month, $year, 'TESLER', 0, true);
      } else {
      $summ = $type4['sum'];
      }

      if ($summ  && $summ != '-'  && $type3['sended'] != 1  && ($type4['status'] == 0 || ($type3['original'] == 0 || $type4['original'] == 0))) {
      $total += $summ;

      $content_list['table_html'] .= '<tr><td>'.$year.'.'.$month.'<br><u>TESLER</u></td>';
      $content_list['table_html'] .= '<td>'.$req['name'].'</td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-act-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/">Акт оплаченных работ</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-bill-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/">Счет на оплату</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-detail-report/?service-id='.$type3['service_id'].'&month='.$month.'&year='.$year.'&brand=tesler">Отчет</a></td>';
      $content_list['table_html'] .= '<td>'.$summ.',00 руб.</td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type4['id'].'">
      <option value="0" '.(($type4['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type4['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov">
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
      $content_list['table_html'] .= '<td><div class="linkz">'.(($type4['original'] == 1 && $type3['original'] == 1) ? '<a class="t-2" style="display:block" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/" title="Отправить на оплату" ></a>' : '<a class="t-2" style="display:none" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/" title="Отправить на оплату" ></a>').' <a class="t-1" href="/get-payment-archive-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/" title="Скачать архивом" ></a>'.(check_combined($type3['id'], $type4['id']) ? '<br><br><input type="checkbox" value="1" name="combine" data-act-id="'.$type3['id'].'" data-bill-id="'.$type4['id'].'">' : '').'</div></td>';
      $content_list['table_html'] .= '<td>'.$year.'.'.$month.'</td>';
      $content_list['table_html'] .= '</tr>';
      }
      }
      else if (($type4['status'] == 1) and ($type3['original'] == 0 || $type4['original'] == 0 )) {


      if (!$type4['sum']) {
      $summ = get_service_summ_fast($row2['service_id'], $month, $year, 'TESLER', 0, true);
      } else {
      $summ = $type4['sum'];
      }

      if ($summ  && $summ != '-'  && $type3['sended'] != 1 && ($type4['status'] == 0 || ($type3['original'] == 0 || $type4['original'] == 0))) {
      $total += $summ;

      $content_list['table_html'] .= '<tr><td>'.$year.'.'.$month.'<br><u>TESLER</u></td>';  
      $content_list['table_html'] .= '<td>'.$req['name'].'</td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-act-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/">Акт оплаченных работ</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-bill-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/">Счет на оплату</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-detail-report/?service-id='.$type3['service_id'].'&month='.$month.'&year='.$year.'&brand=tesler">Отчет</a></td>';
      $content_list['table_html'] .= '<td>'.$summ.',00 руб.</td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type4['id'].'">
      <option value="0" '.(($type4['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type4['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov">
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
      $content_list['table_html'] .= '<td><div class="linkz">'.(($type4['original'] == 1 && $type3['original'] == 1) ? '<a class="t-2" style="display:block" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/" title="Отправить на оплату" ></a>' : '<a class="t-2" style="display:none" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/" title="Отправить на оплату" ></a>').' <a class="t-1" href="/get-payment-archive-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/" title="Скачать архивом" ></a>'.(check_combined($type3['id'], $type4['id']) ? '<br><br><input type="checkbox" value="1" name="combine" data-act-id="'.$type3['id'].'" data-bill-id="'.$type4['id'].'">' : '').'<br>'.((check_custom_loan($row2['service_id'])) ? '<a title="Удалить из списка" class="t-5 delete_loan" data-id="'.$row2['service_id'].'" style="" href="#"></a>' : '').'</div></td>';
      $content_list['table_html'] .= '<td>'.$year.'.'.$month.'</td>';
      $content_list['table_html'] .= '</tr>';
   }
      }

      }



      if ($roch == 1) {

        if ($_GET['impo'] != 1) {
  
  
        if (!$rochBill['sum']) {
        $summ = get_service_summ_fast($row2['service_id'], $month, $year, 'ROCH', 0, true);
        } else {
        $summ = $rochBill['sum'];
        }
  
        if ($summ  && $summ != '-'  && $rochAct['sended'] != 1  && ($rochBill['status'] == 0 || ($rochAct['original'] == 0 || $rochBill['original'] == 0))) {
        $total += $summ;
  
        $content_list['table_html'] .= '<tr><td>'.$year.'.'.$month.'<br><u>ROCH</u></td>';
        $content_list['table_html'] .= '<td>'.$req['name'].'</td>';
        $content_list['table_html'] .= '<td><a href="/get-payment-act-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/roch/">Акт оплаченных работ</a></td>';
        $content_list['table_html'] .= '<td><a href="/get-payment-bill-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/roch/">Счет на оплату</a></td>';
        $content_list['table_html'] .= '<td><a href="/get-detail-report/?service-id='.$rochAct['service_id'].'&month='.$month.'&year='.$year.'&brand=roch">Отчет</a></td>';
        $content_list['table_html'] .= '<td>'.$summ.',00 руб.</td>';
        $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
        <form method="POST">
        <select  name="status_pay" data-pay-id="'.$rochBill['id'].'">
        <option value="0" '.(($rochBill['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
        <option value="1" '.(($rochBill['status'] == 1) ? 'selected' : '').'>Оплачено</option>
        </select>
        </form>
        </td></tr></table></td>';
        $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov">
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
        $content_list['table_html'] .= '<td><div class="linkz">'.(($rochBill['original'] == 1 && $rochAct['original'] == 1) ? '<a class="t-2" style="display:block" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/roch/" title="Отправить на оплату" ></a>' : '<a class="t-2" style="display:none" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/roch/" title="Отправить на оплату" ></a>').' <a class="t-1" href="/get-payment-archive-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/roch/" title="Скачать архивом" ></a>'.(check_combined($rochAct['id'], $rochBill['id']) ? '<br><br><input type="checkbox" value="1" name="combine" data-act-id="'.$rochAct['id'].'" data-bill-id="'.$rochBill['id'].'">' : '').'</div></td>';
        $content_list['table_html'] .= '<td>'.$year.'.'.$month.'</td>';
        $content_list['table_html'] .= '</tr>';
        }
        }
        else if (($rochBill['status'] == 1) and ($rochAct['original'] == 0 || $rochBill['original'] == 0 )) {
  
  
        if (!$rochBill['sum']) {
        $summ = get_service_summ_fast($row2['service_id'], $month, $year, 'ROCH', 0, true);
        } else {
        $summ = $rochBill['sum'];
        }
  
        if ($summ  && $summ != '-'  && $rochAct['sended'] != 1 && ($rochBill['status'] == 0 || ($rochAct['original'] == 0 || $rochBill['original'] == 0))) {
        $total += $summ;
  
        $content_list['table_html'] .= '<tr><td>'.$year.'.'.$month.'<br><u>ROCH</u></td>';  
        $content_list['table_html'] .= '<td>'.$req['name'].'</td>';
        $content_list['table_html'] .= '<td><a href="/get-payment-act-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/roch/">Акт оплаченных работ</a></td>';
        $content_list['table_html'] .= '<td><a href="/get-payment-bill-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/roch/">Счет на оплату</a></td>';
        $content_list['table_html'] .= '<td><a href="/get-detail-report/?service-id='.$rochAct['service_id'].'&month='.$month.'&year='.$year.'&brand=roch">Отчет</a></td>';
        $content_list['table_html'] .= '<td>'.$summ.',00 руб.</td>';
        $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov"><tr><td>
        <form method="POST">
        <select  name="status_pay" data-pay-id="'.$rochBill['id'].'">
        <option value="0" '.(($rochBill['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
        <option value="1" '.(($rochBill['status'] == 1) ? 'selected' : '').'>Оплачено</option>
        </select>
        </form>
        </td></tr></table></td>';
        $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov">
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
        $content_list['table_html'] .= '<td><div class="linkz">'.(($rochBill['original'] == 1 && $rochAct['original'] == 1) ? '<a class="t-2" style="display:block" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/roch/" title="Отправить на оплату" ></a>' : '<a class="t-2" style="display:none" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/roch/" title="Отправить на оплату" ></a>').' <a class="t-1" href="/get-payment-archive-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/roch/" title="Скачать архивом" ></a>'.(check_combined($rochAct['id'], $rochBill['id']) ? '<br><br><input type="checkbox" value="1" name="combine" data-act-id="'.$rochAct['id'].'" data-bill-id="'.$rochBill['id'].'">' : '').'<br>'.((check_custom_loan($row2['service_id'])) ? '<a title="Удалить из списка" class="t-5 delete_loan" data-id="'.$row2['service_id'].'" style="" href="#"></a>' : '').'</div></td>';
        $content_list['table_html'] .= '<td>'.$year.'.'.$month.'</td>';
        $content_list['table_html'] .= '</tr>';
     }
        }
  
        }




      if ($selenga == 1) {

      if ($_GET['impo'] != 1) {


      if (!$type14['sum']) {
      $summ = get_service_summ_fast($row2['service_id'], $month, $year, 'SELENGA', 0, true);
      } else {
      $summ = $type14['sum'];
      }

      if ($summ  && $summ != '-'  && $type13['sended'] != 1  && ($type14['status'] == 0 || ($type13['original'] == 0 || $type14['original'] == 0))) {
      $total += $summ;

      $content_list['table_html'] .= '<tr><td>'.$year.'.'.$month.'<br><u>SELENGA</u></td>';
      $content_list['table_html'] .= '<td>'.$req['name'].'</td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-act-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/selenga-brand/">Акт оплаченных работ</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-bill-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/selenga-brand/">Счет на оплату</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-detail-report/?service-id='.$type14['service_id'].'&month='.$month.'&year='.$year.'&brand=selenga-brand">Отчет</a></td>';
      $content_list['table_html'] .= '<td>'.$summ.',00 руб.</td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type14['id'].'">
      <option value="0" '.(($type14['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type14['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov">
      <tr>
      <td>
      <form method="POST">
      <select  name="status_act" data-pay-id="'.$type13['id'].'">
      <option value="0" '.(($type13['original'] == 0) ? 'selected' : '').'>Оригинал акта не получен</option>
      <option value="1" '.(($type13['original'] == 1) ? 'selected' : '').'>Оригинал акта получен</option>
      </select>
      </form>
      </td>
      </tr>
      <tr>
      <td>
      <form method="POST">
      <select  name="status_bill" data-pay-id="'.$type14['id'].'">
      <option value="0" '.(($type14['original'] == 0) ? 'selected' : '').'>Оригинал счета не получен</option>
      <option value="1" '.(($type14['original'] == 1) ? 'selected' : '').'>Оригинал счета получен</option>
      </select>
      </form>
      </td>
      </tr></table></td>';
      $content_list['table_html'] .= '<td><div class="linkz">'.(($type14['original'] == 1 && $type13['original'] == 1) ? '<a class="t-2" style="display:block" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/selenga/" title="Отправить на оплату" ></a>' : '<a class="t-2" style="display:none" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/selenga/" title="Отправить на оплату" ></a>').' <a class="t-1" href="/get-payment-archive-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/selenga/" title="Скачать архивом" ></a>'.(check_combined($type13['id'], $type14['id']) ? '<br><br><input type="checkbox" value="1" name="combine" data-act-id="'.$type13['id'].'" data-bill-id="'.$type14['id'].'">' : '').'</div></td>';
      $content_list['table_html'] .= '<td>'.$year.'.'.$month.'</td>';
      $content_list['table_html'] .= '</tr>';
      }
      }
      else if (($type14['status'] == 1) and ($type13['original'] == 0 || $type14['original'] == 0 )) {


      if (!$type14['sum']) {
      $summ = get_service_summ_fast($row2['service_id'], $month, $year, 'SELENGA', 0, true);
      } else {
      $summ = $type14['sum'];
      }

      if ($summ  && $summ != '-'  && $type13['sended'] != 1 && ($type14['status'] == 0 || ($type13['original'] == 0 || $type14['original'] == 0))) {
      $total += $summ;

      $content_list['table_html'] .= '<tr><td>'.$year.'.'.$month.'<br><u>SELENGA</u></td>'; 
      $content_list['table_html'] .= '<td>'.$req['name'].'</td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-act-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/selenga-brand/">Акт оплаченных работ</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-bill-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/selenga-brand/">Счет на оплату</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-detail-report/?service-id='.$type14['service_id'].'&month='.$month.'&year='.$year.'&brand=selenga-brand">Отчет</a></td>';
      $content_list['table_html'] .= '<td>'.$summ.',00 руб.</td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type14['id'].'">
      <option value="0" '.(($type14['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type14['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov">
      <tr>
      <td>
      <form method="POST">
      <select  name="status_act" data-pay-id="'.$type13['id'].'">
      <option value="0" '.(($type13['original'] == 0) ? 'selected' : '').'>Оригинал акта не получен</option>
      <option value="1" '.(($type13['original'] == 1) ? 'selected' : '').'>Оригинал акта получен</option>
      </select>
      </form>
      </td>
      </tr>
      <tr>
      <td>
      <form method="POST">
      <select  name="status_bill" data-pay-id="'.$type14['id'].'">
      <option value="0" '.(($type14['original'] == 0) ? 'selected' : '').'>Оригинал счета не получен</option>
      <option value="1" '.(($type14['original'] == 1) ? 'selected' : '').'>Оригинал счета получен</option>
      </select>
      </form>
      </td>
      </tr></table></td>';
      $content_list['table_html'] .= '<td><div class="linkz">'.(($type14['original'] == 1 && $type13['original'] == 1) ? '<a class="t-2" style="display:block" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/selenga/" title="Отправить на оплату" ></a>' : '<a class="t-2" style="display:none" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/selenga/" title="Отправить на оплату" ></a>').' <a class="t-1" href="/get-payment-archive-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/selenga/" title="Скачать архивом" ></a>'.(check_combined($type13['id'], $type14['id']) ? '<br><br><input type="checkbox" value="1" name="combine" data-act-id="'.$type13['id'].'" data-bill-id="'.$type14['id'].'">' : '').'<br>'.((check_custom_loan($row2['service_id'])) ? '<a title="Удалить из списка" class="t-5 delete_loan" data-id="'.$row2['service_id'].'" style="" href="#"></a>' : '').'</div></td>';
      $content_list['table_html'] .= '<td>'.$year.'.'.$month.'</td>';
      $content_list['table_html'] .= '</tr>';
   }
      }

      }

      }

      if ($_COOKIE['payed'] == 1) {

      if ($type2['status'] == 1) {


      if ($_GET['impo'] != 1) {


      if (!$type2['sum']) {
      $summ = get_service_summ_fast($row2['service_id'], $month, $year, 'HARPER', 0, true);
      } else {
      $summ = $type2['sum'];
      }

      if ($summ  && $summ != '-'  && $type1['sended'] != 1 && ($type2['status'] == 0 || ($type1['original'] == 0 || $type2['original'] == 0))) {
      $total += $summ;

      $content_list['table_html'] .= '<tr><td>'.$year.'.'.$month.'<br><u>HARPER</u></td>';
      $content_list['table_html'] .= '<td>'.$req['name'].'</td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-act-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/">Акт оплаченных работ</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-bill-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/">Счет на оплату</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-detail-report/?service-id='.$type1['service_id'].'&month='.$month.'&year='.$year.'&brand=harper">Отчет</a></td>';
      $content_list['table_html'] .= '<td>'.$summ.',00 руб.</td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type2['id'].'">
      <option value="0" '.(($type2['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type2['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov">
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
      $content_list['table_html'] .= '<td><div class="linkz">'.(($type1['original'] == 1 && $type2['original'] == 1) ? '<a class="t-2" style="display:block" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/" title="Отправить на оплату" ></a>' : '<a class="t-2" style="display:none" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/" title="Отправить на оплату" ></a>').' <a class="t-1" href="/get-payment-archive-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/" title="Скачать архивом" ></a>'.(check_combined($type1['id'], $type2['id']) ? '<br><br><input type="checkbox" value="1" name="combine" data-act-id="'.$type1['id'].'" data-bill-id="'.$type2['id'].'">' : '').'</div></td>';
      $content_list['table_html'] .= '<td>'.$year.'.'.$month.'</td>';
      $content_list['table_html'] .= '</tr>';

      }
      } else if (($type2['status'] == 1) and ( $type1['original'] == 0 || $type2['original'] == 0 )) {



      if (!$type2['sum']) {
      $summ = get_service_summ_fast($row2['service_id'], $month, $year, 'HARPER', 0, true);
      } else {
      $summ = $type2['sum'];
      }

      if ($summ  && $summ != '-'  && $type1['sended'] != 1 && ($type2['status'] == 0 || ($type1['original'] == 0 || $type2['original'] == 0))) {

      $total += $summ;

      $content_list['table_html'] .= '<tr><td>'.$year.'.'.$month.'<br><u>HARPER</u></td>';
      $content_list['table_html'] .= '<td>'.$req['name'].'</td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-act-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/">Акт оплаченных работ</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-bill-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/">Счет на оплату</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-detail-report/?service-id='.$type1['service_id'].'&month='.$month.'&year='.$year.'&brand=harper">Отчет</a></td>';
      $content_list['table_html'] .= '<td>'.$summ.',00 руб.</td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type2['id'].'">
      <option value="0" '.(($type2['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type2['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov">
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
      $content_list['table_html'] .= '<td><div class="linkz">'.(($type1['original'] == 1 && $type2['original'] == 1) ? '<a class="t-2" style="display:block" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/" title="Отправить на оплату" ></a>' : '<a class="t-2" style="display:none" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/" title="Отправить на оплату" ></a>').' <a class="t-1" href="/get-payment-archive-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/" title="Скачать архивом" ></a>'.(check_combined($type1['id'], $type2['id']) ? '<br><br><input type="checkbox" value="1" name="combine" data-act-id="'.$type1['id'].'" data-bill-id="'.$type2['id'].'">' : '').'<br>'.((check_custom_loan($row2['service_id'])) ? '<a title="Удалить из списка" class="t-5 delete_loan" data-id="'.$row2['service_id'].'" style="" href="#"></a>' : '').'</div></td>';
      $content_list['table_html'] .= '<td>'.$year.'.'.$month.'</td>';
      $content_list['table_html'] .= '</tr>';

      }
      }
       }

      //TESLER
            if ($type4['status'] == 1 && $_COOKIE['tesler'] == 1) {


      if ($_GET['impo'] != 1) {


      if (!$type4['sum']) {
      $summ = get_service_summ_fast($row2['service_id'], $month, $year, 'TESLER', 0, true);
      } else {
      $summ = $type4['sum'];
      }


      if ($summ  && $summ != '-'  && $type3['sended'] != 1  && ($type4['status'] == 0 || ($type3['original'] == 0 || $type4['original'] == 0))) {
      $total += $summ;

       $content_list['table_html'] .= '<tr><td>'.$year.'.'.$month.'<br><u>TESLER</u></td>';
      $content_list['table_html'] .= '<td>'.$req['name'].'</td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-act-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/">Акт оплаченных работ</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-bill-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/">Счет на оплату</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-detail-report/?service-id='.$type4['service_id'].'&month='.$month.'&year='.$year.'&brand=tesler">Отчет</a></td>';
      $content_list['table_html'] .= '<td>'.$summ.',00 руб.</td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type4['id'].'">
      <option value="0" '.(($type4['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type4['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov">
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
      $content_list['table_html'] .= '<td><div class="linkz">'.(($type4['original'] == 1 && $type3['original'] == 1) ? '<a class="t-2" style="display:block" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/" title="Отправить на оплату" ></a>' : '<a class="t-2" style="display:none" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/" title="Отправить на оплату" ></a>').' <a class="t-1" href="/get-payment-archive-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/" title="Скачать архивом" ></a>'.(check_combined($type3['id'], $type4['id']) ? '<br><br><input type="checkbox" value="1" name="combine" data-act-id="'.$type3['id'].'" data-bill-id="'.$type4['id'].'">' : '').'</div></td>';
      $content_list['table_html'] .= '<td>'.$year.'.'.$month.'</td>';
      $content_list['table_html'] .= '</tr>';
      }
      } else if (($type4['status'] == 1) and ($type3['original'] == 0 || $type4['original'] == 0  )) {


      if (!$type4['sum']) {
      $summ = get_service_summ_fast($row2['service_id'], $month, $year, 'TESLER', 0, true);
      } else {
      $summ = $type4['sum'];
      }

      if ($summ  && $summ != '-'  && $type3['sended'] != 1 && ($type4['status'] == 0 || ($type3['original'] == 0 || $type4['original'] == 0))) {

      $total += $summ;

      $content_list['table_html'] .= '<tr><td>'.$year.'.'.$month.'<br><u>TESLER</u></td>';
      $content_list['table_html'] .= '<td>'.$req['name'].'</td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-act-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/">Акт оплаченных работ</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-bill-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/">Счет на оплату</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-detail-report/?service-id='.$type3['service_id'].'&month='.$month.'&year='.$year.'&brand=tesler">Отчет</a></td>';
      $content_list['table_html'] .= '<td>'.$summ.',00 руб.</td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type4['id'].'">
      <option value="0" '.(($type4['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type4['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov">
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
      $content_list['table_html'] .= '<td><div class="linkz">'.(($type3['original'] == 1 && $type4['original'] == 1) ? '<a class="t-2" style="display:block" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/" title="Отправить на оплату" ></a>' : '<a class="t-2" style="display:none" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/" title="Отправить на оплату" ></a>').' <a class="t-1" href="/get-payment-archive-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/" title="Скачать архивом" ></a>'.(check_combined($type3['id'], $type4['id']) ? '<br><br><input type="checkbox" value="1" name="combine" data-act-id="'.$type3['id'].'" data-bill-id="'.$type4['id'].'">' : '').'<br>'.((check_custom_loan($row2['service_id'])) ? '<a title="Удалить из списка" class="t-5 delete_loan" data-id="'.$row2['service_id'].'" style="" href="#"></a>' : '').'</div></td>';
      $content_list['table_html'] .= '<td>'.$year.'.'.$month.'</td>';
      $content_list['table_html'] .= '</tr>';
      }
      }
      }



      if ($rochBill['status'] == 1 && $roch == 1) {


        if ($_GET['impo'] != 1) {
  
  
        if (!$rochBill['sum']) {
        $summ = get_service_summ_fast($row2['service_id'], $month, $year, 'ROCH', 0, true);
        } else {
        $summ = $rochBill['sum'];
        }
  
  
        if ($summ  && $summ != '-'  && $rochAct['sended'] != 1  && ($rochBill['status'] == 0 || ($rochAct['original'] == 0 || $rochBill['original'] == 0))) {
        $total += $summ;
  
         $content_list['table_html'] .= '<tr><td>'.$year.'.'.$month.'<br><u>ROCH</u></td>';
        $content_list['table_html'] .= '<td>'.$req['name'].'</td>';
        $content_list['table_html'] .= '<td><a href="/get-payment-act-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/roch/">Акт оплаченных работ</a></td>';
        $content_list['table_html'] .= '<td><a href="/get-payment-bill-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/roch/">Счет на оплату</a></td>';
        $content_list['table_html'] .= '<td><a href="/get-detail-report/?service-id='.$type4['service_id'].'&month='.$month.'&year='.$year.'&brand=roch">Отчет</a></td>';
        $content_list['table_html'] .= '<td>'.$summ.',00 руб.</td>';
        $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
        <form method="POST">
        <select  name="status_pay" data-pay-id="'.$rochBill['id'].'">
        <option value="0" '.(($rochBill['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
        <option value="1" '.(($rochBill['status'] == 1) ? 'selected' : '').'>Оплачено</option>
        </select>
        </form>
        </td></tr></table></td>';
        $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov">
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
        $content_list['table_html'] .= '<td><div class="linkz">'.(($rochBill['original'] == 1 && $rochAct['original'] == 1) ? '<a class="t-2" style="display:block" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/roch/" title="Отправить на оплату" ></a>' : '<a class="t-2" style="display:none" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/roch/" title="Отправить на оплату" ></a>').' <a class="t-1" href="/get-payment-archive-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/roch/" title="Скачать архивом" ></a>'.(check_combined($rochAct['id'], $rochBill['id']) ? '<br><br><input type="checkbox" value="1" name="combine" data-act-id="'.$rochAct['id'].'" data-bill-id="'.$rochBill['id'].'">' : '').'</div></td>';
        $content_list['table_html'] .= '<td>'.$year.'.'.$month.'</td>';
        $content_list['table_html'] .= '</tr>';
        }
        } else if (($rochBill['status'] == 1) and ($rochAct['original'] == 0 || $rochBill['original'] == 0  )) {
  
  
        if (!$rochBill['sum']) {
        $summ = get_service_summ_fast($row2['service_id'], $month, $year, 'ROCH', 0, true);
        } else {
        $summ = $rochBill['sum'];
        }
  
        if ($summ  && $summ != '-'  && $rochAct['sended'] != 1 && ($rochBill['status'] == 0 || ($rochAct['original'] == 0 || $rochBill['original'] == 0))) {
  
        $total += $summ;
  
        $content_list['table_html'] .= '<tr><td>'.$year.'.'.$month.'<br><u>ROCH</u></td>';
        $content_list['table_html'] .= '<td>'.$req['name'].'</td>';
        $content_list['table_html'] .= '<td><a href="/get-payment-act-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/roch/">Акт оплаченных работ</a></td>';
        $content_list['table_html'] .= '<td><a href="/get-payment-bill-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/roch/">Счет на оплату</a></td>';
        $content_list['table_html'] .= '<td><a href="/get-detail-report/?service-id='.$rochAct['service_id'].'&month='.$month.'&year='.$year.'&brand=roch">Отчет</a></td>';
        $content_list['table_html'] .= '<td>'.$summ.',00 руб.</td>';
        $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov"><tr><td>
        <form method="POST">
        <select  name="status_pay" data-pay-id="'.$rochBill['id'].'">
        <option value="0" '.(($rochBill['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
        <option value="1" '.(($rochBill['status'] == 1) ? 'selected' : '').'>Оплачено</option>
        </select>
        </form>
        </td></tr></table></td>';
        $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov">
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
        $content_list['table_html'] .= '<td><div class="linkz">'.(($rochAct['original'] == 1 && $rochBill['original'] == 1) ? '<a class="t-2" style="display:block" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/roch/" title="Отправить на оплату" ></a>' : '<a class="t-2" style="display:none" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/roch/" title="Отправить на оплату" ></a>').' <a class="t-1" href="/get-payment-archive-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/roch/" title="Скачать архивом" ></a>'.(check_combined($rochAct['id'], $rochBill['id']) ? '<br><br><input type="checkbox" value="1" name="combine" data-act-id="'.$rochAct['id'].'" data-bill-id="'.$rochBill['id'].'">' : '').'<br>'.((check_custom_loan($row2['service_id'])) ? '<a title="Удалить из списка" class="t-5 delete_loan" data-id="'.$row2['service_id'].'" style="" href="#"></a>' : '').'</div></td>';
        $content_list['table_html'] .= '<td>'.$year.'.'.$month.'</td>';
        $content_list['table_html'] .= '</tr>';
        }
        }
        }



             if ($type14['status'] == 1 ) {


      if ($_GET['impo'] != 1) {


      if (!$type14['sum']) {
      $summ = get_service_summ_fast($row2['service_id'], $month, $year, 'SELENGA', 0, true);
      } else {
      $summ = $type14['sum'];
      }


      if ($summ  && $summ != '-'  && $type13['sended'] != 1  && ($type14['status'] == 0 || ($type13['original'] == 0 || $type14['original'] == 0))) {
      $total += $summ;

       $content_list['table_html'] .= '<tr><td>'.$year.'.'.$month.'<br><u>SELENGA</u></td>';
      $content_list['table_html'] .= '<td>'.$req['name'].'</td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-act-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/selenga-brand/">Акт оплаченных работ</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-bill-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/selenga-brand/">Счет на оплату</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-detail-report/?service-id='.$type14['service_id'].'&month='.$month.'&year='.$year.'&brand=selenga-brand">Отчет</a></td>';
      $content_list['table_html'] .= '<td>'.$summ.',00 руб.</td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type14['id'].'">
      <option value="0" '.(($type14['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type14['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov">
      <tr>
      <td>
      <form method="POST">
      <select  name="status_act" data-pay-id="'.$type13['id'].'">
      <option value="0" '.(($type13['original'] == 0) ? 'selected' : '').'>Оригинал акта не получен</option>
      <option value="1" '.(($type13['original'] == 1) ? 'selected' : '').'>Оригинал акта получен</option>
      </select>
      </form>
      </td>
      </tr>
      <tr>
      <td>
      <form method="POST">
      <select  name="status_bill" data-pay-id="'.$type14['id'].'">
      <option value="0" '.(($type14['original'] == 0) ? 'selected' : '').'>Оригинал счета не получен</option>
      <option value="1" '.(($type14['original'] == 1) ? 'selected' : '').'>Оригинал счета получен</option>
      </select>
      </form>
      </td>
      </tr></table></td>';
      $content_list['table_html'] .= '<td><div class="linkz">'.(($type14['original'] == 1 && $type13['original'] == 1) ? '<a class="t-2" style="display:block" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/selenga/" title="Отправить на оплату" ></a>' : '<a class="t-2" style="display:none" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/selenga/" title="Отправить на оплату" ></a>').' <a class="t-1" href="/get-payment-archive-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/selenga/" title="Скачать архивом" ></a>'.(check_combined($type13['id'], $type14['id']) ? '<br><br><input type="checkbox" value="1" name="combine" data-act-id="'.$type13['id'].'" data-bill-id="'.$type14['id'].'">' : '').'</div></td>';
      $content_list['table_html'] .= '<td>'.$year.'.'.$month.'</td>';
      $content_list['table_html'] .= '</tr>';
      }
      } else if (($type14['status'] == 1) and ($type13['original'] == 0 || $type14['original'] == 0  )) {


      if (!$type14['sum']) {
      $summ = get_service_summ_fast($row2['service_id'], $month, $year, 'SELENGA', 0, true);
      } else {
      $summ = $type14['sum'];
      }

      if ($summ  && $summ != '-'  && $type13['sended'] != 1 && ($type14['status'] == 0 || ($type13['original'] == 0 || $type14['original'] == 0))) {

      $total += $summ;

      $content_list['table_html'] .= '<tr><td>'.$year.'.'.$month.'<br><u>SELENGA</u></td>';
      $content_list['table_html'] .= '<td>'.$req['name'].'</td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-act-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/selenga-brand/">Акт оплаченных работ</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-bill-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/selenga-brand/">Счет на оплату</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-detail-report/?service-id='.$type13['service_id'].'&month='.$month.'&year='.$year.'&brand=selenga-brand">Отчет</a></td>';
      $content_list['table_html'] .= '<td>'.$summ.',00 руб.</td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type14['id'].'">
      <option value="0" '.(($type14['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type14['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov">
      <tr>
      <td>
      <form method="POST">
      <select  name="status_act" data-pay-id="'.$type13['id'].'">
      <option value="0" '.(($type13['original'] == 0) ? 'selected' : '').'>Оригинал акта не получен</option>
      <option value="1" '.(($type13['original'] == 1) ? 'selected' : '').'>Оригинал акта получен</option>
      </select>
      </form>
      </td>
      </tr>
      <tr>
      <td>
      <form method="POST">
      <select  name="status_bill" data-pay-id="'.$type14['id'].'">
      <option value="0" '.(($type14['original'] == 0) ? 'selected' : '').'>Оригинал счета не получен</option>
      <option value="1" '.(($type14['original'] == 1) ? 'selected' : '').'>Оригинал счета получен</option>
      </select>
      </form>
      </td>
      </tr></table></td>';
      $content_list['table_html'] .= '<td><div class="linkz">'.(($type13['original'] == 1 && $type14['original'] == 1) ? '<a class="t-2" style="display:block" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/selenga/" title="Отправить на оплату" ></a>' : '<a class="t-2" style="display:none" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/selenga/" title="Отправить на оплату" ></a>').' <a class="t-1" href="/get-payment-archive-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/selenga/" title="Скачать архивом" ></a>'.(check_combined($type13['id'], $type14['id']) ? '<br><br><input type="checkbox" value="1" name="combine" data-act-id="'.$type13['id'].'" data-bill-id="'.$type14['id'].'">' : '').'<br>'.((check_custom_loan($row2['service_id'])) ? '<a title="Удалить из списка" class="t-5 delete_loan" data-id="'.$row2['service_id'].'" style="" href="#"></a>' : '').'</div></td>';
      $content_list['table_html'] .= '<td>'.$year.'.'.$month.'</td>';
      $content_list['table_html'] .= '</tr>';
      }
      }
      }

      }

      if ($_COOKIE['notpayed'] != 1 && $_COOKIE['payed'] != 1) {


      if ($_GET['impo'] != 1) {


      if (!$type2['sum']) {
      $summ = get_service_summ_fast($row2['service_id'], $month, $year, 'HARPER', 0, true);
      } else {
      $summ = $type2['sum'];
      }
      if ($summ  && $summ != '-'  && $type1['sended'] != 1 && ($type2['status'] == 0 || ($type1['original'] == 0 || $type2['original'] == 0))) {

      $total += $summ;

      $content_list['table_html'] .= '<tr><td>'.$year.'.'.$month.'<br><u>HARPER</u></td>';
      $content_list['table_html'] .= '<td>'.$req['name'].'</td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-act-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/">Акт оплаченных работ</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-bill-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/">Счет на оплату</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-detail-report/?service-id='.$type2['service_id'].'&month='.$month.'&year='.$year.'&brand=harper">Отчет</a></td>';
      $content_list['table_html'] .= '<td>'.$summ.',00 руб.</td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type2['id'].'">
      <option value="0" '.(($type2['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type2['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov">
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
      $content_list['table_html'] .= '<td><div class="linkz">'.(($type1['original'] == 1 && $type2['original'] == 1) ? '<a class="t-2" style="display:block" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/" title="Отправить на оплату" ></a>' : '<a class="t-2" style="display:none" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/" title="Отправить на оплату" ></a>').' <a class="t-1" href="/get-payment-archive-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/" title="Скачать архивом" ></a>'.(check_combined($type1['id'], $type2['id']) ? '<br><br><input type="checkbox" value="1" name="combine" data-act-id="'.$type1['id'].'" data-bill-id="'.$type2['id'].'">' : '').'</div></td>';
      $content_list['table_html'] .= '<td>'.$year.'.'.$month.'</td>';
      $content_list['table_html'] .= '</tr>';
      }
      } else if (($type2['status'] == 1) and ($type1['original'] == 0 || $type2['original'] == 0 )) {



      if (!$type2['sum']) {
      $summ = get_service_summ_fast($row2['service_id'], $month, $year, 'HARPER', 0, true);
      } else {
      $summ = $type2['sum'];
      }


      if ($summ  && $summ != '-' && $type1['sended'] != 1 && ($type2['status'] == 0 || ($type1['original'] == 0 || $type2['original'] == 0))) {

      $total += $summ;

      $content_list['table_html'] .= '<tr><td>'.$year.'.'.$month.'<br><u>HARPER</u></td>';
      $content_list['table_html'] .= '<td>'.$req['name'].'</td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-act-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/">Акт оплаченных работ</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-bill-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/">Счет на оплату</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-detail-report/?service-id='.$type1['service_id'].'&month='.$month.'&year='.$year.'&brand=harper">Отчет</a></td>';
      $content_list['table_html'] .= '<td>'.$summ.',00 руб.</td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type2['id'].'">
      <option value="0" '.(($type2['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type2['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov">
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
      $content_list['table_html'] .= '<td><div class="linkz">'.(($type1['original'] == 1 && $type2['original'] == 1) ? '<a class="t-2" style="display:block" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/" title="Отправить на оплату" ></a>' : '<a class="t-2" style="display:none" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/" title="Отправить на оплату" ></a>').' <a class="t-1" href="/get-payment-archive-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/" title="Скачать архивом" ></a>'.(check_combined($type1['id'], $type2['id']) ? '<br><br><input type="checkbox" value="1" name="combine" data-act-id="'.$type1['id'].'" data-bill-id="'.$type2['id'].'">' : '').'<br>'.((check_custom_loan($row2['service_id'])) ? '<a title="Удалить из списка" class="t-5 delete_loan" data-id="'.$row2['service_id'].'" style="" href="#"></a>' : '').'</div></td>';
      $content_list['table_html'] .= '<td>'.$year.'.'.$month.'</td>';
      $content_list['table_html'] .= '</tr>';
      }

      }

      }

      //TESLER
            if ($_COOKIE['notpayed'] != 1 && $_COOKIE['payed'] != 1) {

          if ($tesler == 1 && $_COOKIE['tesler'] == 1) {


      if ($_GET['impo'] != 1) {


      if (!$type4['sum']) {
      $summ = get_service_summ_fast($row2['service_id'], $month, $year, 'TESLER', 0, true);
      } else {
      $summ = $type4['sum'];
      }

      if ($summ  && $summ != '-' && $type3['sended'] != 1 && ($type4['status'] == 0 || ($type3['original'] == 0 || $type4['original'] == 0))) {
      $total += $summ;

      $content_list['table_html'] .= '<tr><td>'.$year.'.'.$month.'<br><u>TESLER</u></td>';
      $content_list['table_html'] .= '<td>'.$req['name'].'</td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-act-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/">Акт оплаченных работ</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-bill-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/">Счет на оплату</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-detail-report/?service-id='.$type4['service_id'].'&month='.$month.'&year='.$year.'&brand=tesler">Отчет</a></td>';
      $content_list['table_html'] .= '<td>'.$summ.',00 руб.</td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type4['id'].'">
      <option value="0" '.(($type4['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type4['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov">
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
      $content_list['table_html'] .= '<td><div class="linkz">'.(($type4['original'] == 1 && $type3['original'] == 1) ? '<a class="t-2" style="display:block" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/" title="Отправить на оплату" ></a>' : '<a class="t-2" style="display:none" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/" title="Отправить на оплату" ></a>').' <a class="t-1" href="/get-payment-archive-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/" title="Скачать архивом" ></a>'.(check_combined($type3['id'], $type4['id']) ? '<br><br><input type="checkbox" value="1" name="combine" data-act-id="'.$type3['id'].'" data-bill-id="'.$type4['id'].'">' : '').'</div></td>';
      $content_list['table_html'] .= '<td>'.$year.'.'.$month.'</td>';
      $content_list['table_html'] .= '</tr>';
      }


      } else if (($type4['status'] == 1) and ($type3['original'] == 0 || $type4['original'] == 0  )) {



      if (!$type4['sum']) {
      $summ = get_service_summ_fast($row2['service_id'], $month, $year, 'TESLER', 0, true);
      } else {
      $summ = $type4['sum'];
      }

      if ($summ  && $summ != '-'  && $type3['sended'] != 1  && ($type4['status'] == 0 || ($type3['original'] == 0 || $type4['original'] == 0))) {

      $total += $summ;

      $content_list['table_html'] .= '<tr><td>'.$year.'.'.$month.'<br><u>TESLER</u></td>';
      $content_list['table_html'] .= '<td>'.$req['name'].'</td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-act-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/">Акт оплаченных работ</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-bill-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/">Счет на оплату</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-detail-report/?service-id='.$type3['service_id'].'&month='.$month.'&year='.$year.'&brand=tesler">Отчет</a></td>';
      $content_list['table_html'] .= '<td>'.$summ.',00 руб</td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type4['id'].'">
      <option value="0" '.(($type4['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type4['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov">
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
      $content_list['table_html'] .= '<td><div class="linkz">'.(($type4['original'] == 1 && $type3['original'] == 1) ? '<a class="t-2" style="display:block" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/" title="Отправить на оплату" ></a>' : '<a class="t-2" style="display:none" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/" title="Отправить на оплату" ></a>').' <a class="t-1" href="/get-payment-archive-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/" title="Скачать архивом" ></a>'.(check_combined($type3['id'], $type4['id']) ? '<br><br><input type="checkbox" value="1" name="combine" data-act-id="'.$type3['id'].'" data-bill-id="'.$type4['id'].'">' : '').'<br>'.((check_custom_loan($row2['service_id'])) ? '<a title="Удалить из списка" class="t-5 delete_loan" data-id="'.$row2['service_id'].'" style="" href="#"></a>' : '').'</div></td>';
      $content_list['table_html'] .= '<td>'.$year.'.'.$month.'</td>';
      $content_list['table_html'] .= '</tr>';

    }
      }
     }



     if ($roch == 1) {

      if ($_GET['impo'] != 1) {

      if (!$rochBill['sum']) {
      $summ = get_service_summ_fast($row2['service_id'], $month, $year, 'ROCH', 0, true);
      } else {
      $summ = $rochBill['sum'];
      }

      if ($summ  && $summ != '-' && $rochAct['sended'] != 1 && ($rochBill['status'] == 0 || ($rochAct['original'] == 0 || $rochBill['original'] == 0))) {
      $total += $summ;

      $content_list['table_html'] .= '<tr><td>'.$year.'.'.$month.'<br><u>ROCH</u></td>';
      $content_list['table_html'] .= '<td>'.$req['name'].'</td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-act-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/roch/">Акт оплаченных работ</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-bill-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/roch/">Счет на оплату</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-detail-report/?service-id='.$row2['service_id'].'&month='.$month.'&year='.$year.'&brand=roch">Отчет</a></td>';
      $content_list['table_html'] .= '<td>'.$summ.',00 руб.</td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$rochBill['id'].'">
      <option value="0" '.(($rochBill['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($rochBill['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov">
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
      $content_list['table_html'] .= '<td><div class="linkz">'.(($rochBill['original'] == 1 && $rochAct['original'] == 1) ? '<a class="t-2" style="display:block" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/roch/" title="Отправить на оплату" ></a>' : '<a class="t-2" style="display:none" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/roch/" title="Отправить на оплату" ></a>').' <a class="t-1" href="/get-payment-archive-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/roch/" title="Скачать архивом" ></a>'.(check_combined($rochAct['id'], $rochBill['id']) ? '<br><br><input type="checkbox" value="1" name="combine" data-act-id="'.$rochAct['id'].'" data-bill-id="'.$rochBill['id'].'">' : '').'</div></td>';
      $content_list['table_html'] .= '<td>'.$year.'.'.$month.'</td>';
      $content_list['table_html'] .= '</tr>';
      }


      } else if (($rochBill['status'] == 1) and ($rochAct['original'] == 0 || $rochBill['original'] == 0  )) {



      if (!$rochBill['sum']) {
      $summ = get_service_summ_fast($row2['service_id'], $month, $year, 'ROCH', 0, true);
      } else {
      $summ = $rochBill['sum'];
      }

      if ($summ  && $summ != '-'  && $rochAct['sended'] != 1  && ($rochBill['status'] == 0 || ($rochAct['original'] == 0 || $rochBill['original'] == 0))) {

      $total += $summ;

      $content_list['table_html'] .= '<tr><td>'.$year.'.'.$month.'<br><u>ROCH</u></td>';
      $content_list['table_html'] .= '<td>'.$req['name'].'</td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-act-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/roch/">Акт оплаченных работ</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-bill-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/roch/">Счет на оплату</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-detail-report/?service-id='.$rochAct['service_id'].'&month='.$month.'&year='.$year.'&brand=roch">Отчет</a></td>';
      $content_list['table_html'] .= '<td>'.$summ.',00 руб</td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$rochBill['id'].'">
      <option value="0" '.(($rochBill['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($rochBill['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov">
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
      $content_list['table_html'] .= '<td><div class="linkz">'.(($rochBill['original'] == 1 && $rochAct['original'] == 1) ? '<a class="t-2" style="display:block" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/roch/" title="Отправить на оплату" ></a>' : '<a class="t-2" style="display:none" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/roch/" title="Отправить на оплату" ></a>').' <a class="t-1" href="/get-payment-archive-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/roch/" title="Скачать архивом" ></a>'.(check_combined($rochAct['id'], $rochBill['id']) ? '<br><br><input type="checkbox" value="1" name="combine" data-act-id="'.$rochAct['id'].'" data-bill-id="'.$rochBill['id'].'">' : '').'<br>'.((check_custom_loan($row2['service_id'])) ? '<a title="Удалить из списка" class="t-5 delete_loan" data-id="'.$row2['service_id'].'" style="" href="#"></a>' : '').'</div></td>';
      $content_list['table_html'] .= '<td>'.$year.'.'.$month.'</td>';
      $content_list['table_html'] .= '</tr>';

    }
      }
     }


          if ($selenga == 1) {


      if ($_GET['impo'] != 1) {


      if (!$type14['sum']) {
      $summ = get_service_summ_fast($row2['service_id'], $month, $year, 'SELENGA', 0, true);
      } else {
      $summ = $type14['sum'];
      }

      if ($summ  && $summ != '-' && $type13['sended'] != 1 && ($type14['status'] == 0 || ($type13['original'] == 0 || $type14['original'] == 0))) {
      $total += $summ;

      $content_list['table_html'] .= '<tr><td>'.$year.'.'.$month.'<br><u>SELENGA</u></td>';
      $content_list['table_html'] .= '<td>'.$req['name'].'</td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-act-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/selenga-brand/">Акт оплаченных работ</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-bill-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/selenga-brand/">Счет на оплату</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-detail-report/?service-id='.$type14['service_id'].'&month='.$month.'&year='.$year.'&brand=selenga-brand">Отчет</a></td>';
      $content_list['table_html'] .= '<td>'.$summ.',00 руб.</td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type14['id'].'">
      <option value="0" '.(($type14['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type14['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov">
      <tr>
      <td>
      <form method="POST">
      <select  name="status_act" data-pay-id="'.$type13['id'].'">
      <option value="0" '.(($type13['original'] == 0) ? 'selected' : '').'>Оригинал акта не получен</option>
      <option value="1" '.(($type13['original'] == 1) ? 'selected' : '').'>Оригинал акта получен</option>
      </select>
      </form>
      </td>
      </tr>
      <tr>
      <td>
      <form method="POST">
      <select  name="status_bill" data-pay-id="'.$type14['id'].'">
      <option value="0" '.(($type14['original'] == 0) ? 'selected' : '').'>Оригинал счета не получен</option>
      <option value="1" '.(($type14['original'] == 1) ? 'selected' : '').'>Оригинал счета получен</option>
      </select>
      </form>
      </td>
      </tr></table></td>';
      $content_list['table_html'] .= '<td><div class="linkz">'.(($type14['original'] == 1 && $type13['original'] == 1) ? '<a class="t-2" style="display:block" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/selenga/" title="Отправить на оплату" ></a>' : '<a class="t-2" style="display:none" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/selenga/" title="Отправить на оплату" ></a>').' <a class="t-1" href="/get-payment-archive-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/selenga/" title="Скачать архивом" ></a>'.(check_combined($type13['id'], $type14['id']) ? '<br><br><input type="checkbox" value="1" name="combine" data-act-id="'.$type13['id'].'" data-bill-id="'.$type14['id'].'">' : '').'</div></td>';
      $content_list['table_html'] .= '<td>'.$year.'.'.$month.'</td>';
      $content_list['table_html'] .= '</tr>';
      }


      } else if (($type14['status'] == 1) and ($type13['original'] == 0 || $type14['original'] == 0  )) {



      if (!$type14['sum']) {
      $summ = get_service_summ_fast($row2['service_id'], $month, $year, 'SELENGA', 0, true);
      } else {
      $summ = $type14['sum'];
      }

      if ($summ  && $summ != '-'  && $type13['sended'] != 1  && ($type14['status'] == 0 || ($type13['original'] == 0 || $type14['original'] == 0))) {

      $total += $summ;

      $content_list['table_html'] .= '<tr><td>'.$year.'.'.$month.'<br><u>SELENGA</u></td>';
      $content_list['table_html'] .= '<td>'.$req['name'].'</td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-act-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/selenga-brand/">Акт оплаченных работ</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-payment-bill-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/selenga-brand">Счет на оплату</a></td>';
      $content_list['table_html'] .= '<td><a href="/get-detail-report/?service-id='.$type14['service_id'].'&month='.$month.'&year='.$year.'&brand=selenga-brand">Отчет</a></td>';
      $content_list['table_html'] .= '<td>'.$summ.',00 руб</td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type14['id'].'">
      <option value="0" '.(($type14['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type14['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>';
      $content_list['table_html'] .= '<td><table style="    margin: 0 auto;" class="nohov">
      <tr>
      <td>
      <form method="POST">
      <select  name="status_act" data-pay-id="'.$type13['id'].'">
      <option value="0" '.(($type13['original'] == 0) ? 'selected' : '').'>Оригинал акта не получен</option>
      <option value="1" '.(($type13['original'] == 1) ? 'selected' : '').'>Оригинал акта получен</option>
      </select>
      </form>
      </td>
      </tr>
      <tr>
      <td>
      <form method="POST">
      <select  name="status_bill" data-pay-id="'.$type14['id'].'">
      <option value="0" '.(($type14['original'] == 0) ? 'selected' : '').'>Оригинал счета не получен</option>
      <option value="1" '.(($type14['original'] == 1) ? 'selected' : '').'>Оригинал счета получен</option>
      </select>
      </form>
      </td>
      </tr></table></td>';
      $content_list['table_html'] .= '<td><div class="linkz">'.(($type14['original'] == 1 && $type13['original'] == 1) ? '<a class="t-2" style="display:block" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/selenga/" title="Отправить на оплату" ></a>' : '<a class="t-2" style="display:none" href="/send-to-pay/'.$row2['service_id'].'/'.$year.'/'.$month.'/selenga/" title="Отправить на оплату" ></a>').' <a class="t-1" href="/get-payment-archive-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/selenga/" title="Скачать архивом" ></a>'.(check_combined($type13['id'], $type14['id']) ? '<br><br><input type="checkbox" value="1" name="combine" data-act-id="'.$type13['id'].'" data-bill-id="'.$type14['id'].'">' : '').'<br>'.((check_custom_loan($row2['service_id'])) ? '<a title="Удалить из списка" class="t-5 delete_loan" data-id="'.$row2['service_id'].'" style="" href="#"></a>' : '').'</div></td>';
      $content_list['table_html'] .= '<td>'.$year.'.'.$month.'</td>';
      $content_list['table_html'] .= '</tr>';

    }

      }

     }
      }

        unset($model);
    unset($tesler);

      }

      }

     }

}


    return $content_list;
}

//if ($_GET['date'] && $_GET['service_id']) {
   // $content = content_list();
//}



function services_select($cat_id = '') {
  global $db;
  $content = '';
$sql = mysqli_query($db, 'SELECT * FROM `requests` where `name` != \'\';');
      while ($row = mysqli_fetch_array($sql)) {
      if ($cat_id == $row['user_id']) {
      $content .= '<option selected value="'.$row['user_id'].'">'.$row['name'].'</option>';
      } else {
       $content .= '<option value="'.$row['user_id'].'">'.$row['name'].'</option>';
      }
      }
    return $content;
}


?>
<!doctype html>
<html>
<head>
<meta charset=utf-8>
<title>Счета в работе - агентские</title>
<link href="/css/fonts.css" rel="stylesheet" />
<link href="/css/style.css" rel="stylesheet" />
<script src="/_new-codebase/front/vendor/jquery/jquery.min.js"></script>
<script src="/js/jquery-ui.min.js"></script>
<script src="/js/jquery.placeholder.min.js"></script>
<script src="/js/jquery.formstyler.min.js"></script>
<script src="/js/main.js"></script>
<link rel="stylesheet" href="/_new-codebase/front/vendor/select2/4.0.4/select2.min.css" />
<script src="/_new-codebase/front/vendor/select2/4.0.4/select2.full.min.js"></script>
<script src="/notifier/js/index.js"></script>
<script src="/_new-codebase/front/vendor/remodal/remodal.min.js"></script>
<link rel="stylesheet" href="/_new-codebase/front/vendor/remodal/remodal.css" />
<link rel="stylesheet" href="/_new-codebase/front/vendor/remodal/remodal-default-theme.css" />
<link rel="stylesheet" type="text/css" href="/notifier/css/style.css">
<link rel="stylesheet" href="/_new-codebase/front/vendor/animate.min.css" />

<script src='/_new-codebase/front/vendor/mustache/mustache.min.js'></script>
<script src="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.concat.min.js"></script>
<link rel="stylesheet" href="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.min.css" />

<script  src="/_new-codebase/front/vendor/datatables/js/data-tables.min.js"></script>
<link rel="stylesheet" type="text/css" href="/css/datatables.css">

<style>

.t-2 {
    background: url(/img/right-arrow.png) !important;
    height: 32px !important;
    width: 32px  !important;
    margin-bottom: 10px;
}
.linkz {
    position:absolute;
    top:20px;
}

input[name="combine"] {
display:none;
}

.t-1 {
display:none !important;
}
.jq-checkbox {

    display: none !Important;
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

table.dataTable.row-border tbody th, table.dataTable.row-border tbody td, table.dataTable.display tbody th, table.dataTable.display tbody td {
    position:relative;
}
.nohov tr:hover {
    background-color: transparent !important;
    box-shadow: initial !important;
}
.impor {
 background: rgba(255, 51, 0, 0);background-color: rgba(255, 51, 0, 0.14) !important;
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

.dataTables_wrapper .dataTables_paginate .paginate_button:active {
    background: #80bd03;
    padding: 0 9px;
    border-radius: 4px;
    color: #fff;

}

</style>
<script >
// Таблица
$(document).ready(function() {
    $('#table_content').dataTable({
      "responsive": true,
      stateSave:true,
      "dom": '<"top"flp<"clear">>rt<"bottom"ip<"clear">>',
      "pageLength": <?=$config['page_limit'];?>,
       "lengthMenu": [[10, 25, 50, 100, 200, 300, 500, -1], [10, 25, 50, 100, 200, 300, 500, 'Все (ресурсоёмко)']],
      <?php if ($_GET['service_id'] == 'all' || !$_GET['service_id']) { ?>
      "order": [[ 8, 'desc' ]],

       "columnDefs": [
            {
                "targets": [ 9 ],
                "visible": false,
                "searchable": false
            }
        ],

      <?php } else { ?>

      "order": [[ 7, 'desc' ]],

       "columnDefs": [
            {
                "targets": [ 8 ],
                "visible": false,
                "searchable": false
            }
        ],

      <?php } ?>



       "fnDrawCallback": function( oSettings ) {

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

    $('input[type="checkbox"], input[type="radio"]:not(.nomenu)').styler();


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


$(".monthPicker").datepicker({
    dateFormat: 'yy.mm',
    changeMonth: true,
      changeYear: true,
      showButtonPanel: true,
      yearRange: '2017:2019',
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


$(".monthPicker2").datepicker({
    dateFormat: 'yy',
    changeMonth: true,
      changeYear: true,
      showButtonPanel: true,
      yearRange: '2017:2019',
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

$('.select2').select2();

    $(document).on('selectmenuchange', 'select[name=status_pay]', function() {
        var value = $(this).val();
        var id= $(this).data('pay-id');
              if (value) {

                  $.get( "/ajax.php?type=update_pay_status&value="+value+"&id="+id, function( data ) {

                  });

              }


        return false;
    });

   $(document).on('click', '.t-2', function() {

    $.get($(this).attr('href'), function( data ) {

                  });
    $(this).parent().parent().parent().hide();
    return false;
   });

    $(document).on('selectmenuchange', 'select[name=status_act]', function() {
        var value = $(this).val();
        var id= $(this).data('pay-id');
        var parent = $(this).parent().parent().parent().parent().parent();
        var parent_row = $(this).parent().parent().parent().parent().parent().parent().parent();
        if (parent.find('select[name="status_act"]').val() == 1 && parent.find('select[name="status_bill"]').val() == 1) {
            parent_row.find('.t-2').show();
        }  else {
            parent_row.find('.t-2').hide();
        }



              if (value) {

                  $.get( "/ajax.php?type=update_act_status&value="+value+"&id="+id, function( data ) {

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

        var parent = $(this).parent().parent().parent().parent().parent();
        var parent_row = $(this).parent().parent().parent().parent().parent().parent().parent();
        if (parent.find('select[name="status_act"]').val() == 1 && parent.find('select[name="status_bill"]').val() == 1) {
            parent_row.find('.t-2').show();
        } else {
            parent_row.find('.t-2').hide();
        }

              if (value) {

                  $.get( "/ajax.php?type=update_bill_status&value="+value+"&id="+id, function( data ) {

                  });

              }


        return false;
    });

    $(document).on('change', 'input[name="payed"],input[name="notpayed"],input[name="tesler"]', function() {
        var form = $(this).parent().parent().parent().parent();
        $('#checkb').submit();
    });

    $(document).on('click', '.combine_docs', function() {

        $('[data-remodal-id=modal]').remodal().open();

    /*$('.jq-checkbox.checked').each(function(){
          alert($(this).data('monthid'));
    });*/

    });


    $(document).on('click', '.gen_acts', function() {
    var acts = [];
    var bills = [];
    var result = [];
    $('.jq-checkbox.checked').each(function(){
          bills.push($(this).data('billid'));
          acts.push($(this).data('actid'));
          $(this).remove();
          $('[data-remodal-id=modal]').remodal().close();
          $('[data-remodal-id=modal2]').remodal().open();
    });

          result.push(bills);
          result.push(acts);
          result.push($('input[name="date_combine"]').val());
          console.log(result);
          $.get('/ajax.php?type=create_combine&value='+JSON.stringify(result));
    });

} );

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

  <!-- New codebase -->
  <link rel="stylesheet" href="/_new-codebase/front/components/the-table/css/the-table.css">
  <link rel="stylesheet" href="/_new-codebase/front/templates/main/css/pagination.css">
</head>

<body>
<?php

$content = content_list();

?>
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
           <h2 style="margin-bottom: 12px;">Счета в работе - агентские</h2>
           <?= '<p>' . $content['dates_interval'] . '</p>'; ?>
  <div class="adm-catalog">

    <div style="display: flex;margin: 20px 0;align-items: center;justify-content: space-between;">
        <div>
        <a style="width: auto;padding-left: 7px;padding-right: 7px;background:#80bd03;color:#fff;vertical-align: middle;" href="/payments-v3-archive/" class="button">Архив</a>
        </div>
        <div>
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
      </div>   


  <table id="table_content" class="display" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th align="center" style="text-align:center;">Период</th>
                <?php if ($_GET['service_id'] == 'all' || !$_GET['service_id']) {
                 echo '<th align="left">СЦ</th>';
                };?>
                <th align="left">Акт выполненных работ</th>
                <th align="left">Счет на оплату</th>
                <th align="left">Детальный отчет</th>
                <th align="center">Сумма</th>
                <th align="center">Оплата</th>
                <th align="center">Получение оригиналов</th>
                <th>Скачать</th>
            </tr>
        </thead>
        <tbody>
          <?= $content['table_html']; ?>
        </tbody>
</table>

<?=($total) ? '<div>Итого: <strong>'.$total.',00 руб</strong></div> ' : '';?>

</div>


        </div>
  </div>
<div class="remodal" data-remodal-id="modal">
  <button data-remodal-action="close" class="remodal-close"></button>
  <h1 style="float:none !important">Выберите дату</h1>
   <br />
  <table style="    margin: 0 auto;">
  <tr>
  <Td>
  <input type="text" class="monthPicker" name="date_combine" style="width: 120px;    text-align: center;    height: 40px;    padding: 0;" value=""/>
  <br><br><br></Td>
  </tr>
  </table>

  <button style="    width: 30%;"  class="gen_acts">Генерировать</button>

</div>

<div class="remodal" data-remodal-id="modal2">
  <button data-remodal-action="close" class="remodal-close"></button>
  <h1 style="float:none !important">Готово</h1>
   <br />
  <a href="/combined/">К списку документов</a>

</div>

</body>
</html>