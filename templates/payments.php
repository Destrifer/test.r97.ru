<?php

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
if ($_POST['tesler'] == 1) {
setcookie("tesler", "1", time()+3600*60*31, "/payments/");
$_COOKIE['tesler'] = 1;
} else {
setcookie("tesler", "", time()-3600*60*31, "/payments/");
$_COOKIE['tesler'] = 0;
}

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
  global $db;

$dated = ((date("d") < 5) ? date("Y.m", strtotime("-1 months")) : date("Y.m"));

if ($_GET['service_id'] && $_GET['service_id'] != 'all') {

if ($_GET['year']) {
$where_year = 'and `app_date` REGEXP \''.$_GET['year'].'\'';
}
if ($_GET['date']) {
$where_date = 'and `app_date` REGEXP \''.$_GET['date'].'\'';
}


$sql = mysqli_query($db, 'SELECT * FROM `repairs` where `service_id` = '.$_GET['service_id'].' '.$where_date.' and `status_admin` = \'Подтвержден\' and `deleted` = 0 and  `app_date` NOT REGEXP \''.$dated.'\' '.$where_year.'  order by `id` DESC;');
      while ($row = mysqli_fetch_array($sql)) {
      //$info = get_request_info($row['id']);
      //$city = get_city($info['city']);

      if($row['app_date']) {

      $exp = explode('.', $row['app_date']);

      $app[$exp['0']][$exp[1]] = '';
      }



      }
    //print_r($app);
     //array_reverse($app,true);;


      foreach ($app as $year => $val) {
      $year_work = $val;
      foreach ($year_work as $month => $value) {

      $type1 = create_or_get_payment_id($_GET['service_id'], $year, $month, 1); //акт
      $type2 = create_or_get_payment_id($_GET['service_id'], $year, $month, 2); //счет

      /*Проверяем теслер*/
      $sql3 = mysqli_query($db, 'SELECT * FROM `repairs` where `service_id` = '.$_GET['service_id'].' and `app_date` REGEXP \''.$year.'.'.$month.'\' and `status_admin` = \'Подтвержден\' and `deleted` = 0 and `app_date` NOT REGEXP \''.$dated.'\' order by `id` DESC ;');
      while ($row3 = mysqli_fetch_array($sql3)) {
      $model = model_info($row3['model_id']);

      if ($model['brand'] == 'TESLER') {
        $tesler = 1;
      }
      }

      if ($tesler == 1) {
      $type3 = create_or_get_payment_id($_GET['service_id'], $year, $month, 3); //акт
      $type4 = create_or_get_payment_id($_GET['service_id'], $year, $month, 4); //счет
      }

      /*/Проверка теслера*/

      // Оплаченные харпер
      if ($_COOKIE['payed'] == 1) {

       $tr = (get_service_loan($_GET['service_id'], $month, $year, 1, 2)) ? ' background: rgba(255, 51, 0, 0);background-color: rgba(255, 51, 0, 0.14) !important;' : '';

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
      $summ = get_service_summ($_GET['service_id'], $month, $year, 'HARPER');
      if ($summ) {
      $content_list['total'] += $summ;
      $content_list['body'] .= '<tr style="'.$tr.'">
      <td style="text-align:center;'.$tr.'">'.$year.'.'.$month.'<br><u>HARPER</u></td>
      <td><a href="/get-payment-act-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/">Акт оплаченных работ</a></td>
      <td><a href="/get-payment-bill-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/">Счет на оплату</a></td>
      <td>'.$summ.',00 руб.</td>
      <td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type2['id'].'">
      <option value="0" '.(($type2['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type2['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>
      <td><table style="    margin: 0 auto;" class="nohov">
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
      </tr></table></td>
      <td align="center" class="linkz"><a class="t-1" href="/get-payment-archive-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/" title="Скачать архивом" ></a></td>
      </tr>';
      }
      }

      // Оплаченные Теслер
      if ($tesler == 1 && $_COOKIE['tesler'] == 1) {

      if ($type4['status'] == 1) {

      $tr = (get_service_loan($_GET['service_id'], $month, $year, 3, 4)) ? 'background: rgba(255, 51, 0, 0);background-color: rgba(255, 51, 0, 0.14) !important;' : '';

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
      $summ = get_service_summ($_GET['service_id'], $month, $year, 'TESLER');

      if ($summ) {
      $content_list['total'] += $summ;
      $content_list['body'] .= '<tr style="'.$tr.'">
      <td style="text-align:center;'.$tr.'">'.$year.'.'.$month.'<br><u>TESLER</u><br><br>'.(($tr) ? '<img src="/img/skull.png">' : '').'</td>
      <td><a href="/get-payment-act-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/tesler/">Акт оплаченных работ</a></td>
      <td><a href="/get-payment-bill-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/tesler/">Счет на оплату</a></td>
      <td>'.$summ.',00 руб.</td>
      <td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type4['id'].'">
      <option value="0" '.(($type4['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type4['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>
      <td><table style="    margin: 0 auto;" class="nohov">
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
      </tr></table></td>
      <td align="center" class="linkz"><a class="t-1" href="/get-payment-archive-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/tesler/" title="Скачать архивом" ></a></td>
      </tr>';
      }
      }
      }
      }

      // Не оплаченные харпер
      if ($_COOKIE['notpayed'] == 1) {

      if ($type2['status'] == 0) {

      $tr = (get_service_loan($_GET['service_id'], $month, $year, 1, 2)) ? 'background: rgba(255, 51, 0, 0);background-color: rgba(255, 51, 0, 0.14) !important;' : '';

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
      $summ = get_service_summ($_GET['service_id'], $month, $year, 'HARPER');

      if ($summ) {
      $content_list['total'] += $summ;
      $content_list['body'] .= '<tr style="'.$tr.'">
      <td style="text-align:center;'.$tr.'">'.$year.'.'.$month.'<br><u>HARPER</u><br><br>'.(($tr) ? '<img src="/img/skull.png">' : '').'</td>
      <td><a href="/get-payment-act-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/">Акт оплаченных работ</a></td>
      <td><a href="/get-payment-bill-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/">Счет на оплату</a></td>
      <td>'.$summ.',00 руб.</td>
      <td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type2['id'].'">
      <option value="0" '.(($type2['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type2['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>
      <td><table style="    margin: 0 auto;" class="nohov">
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
      </tr></table></td>
      <td align="center" class="linkz"><a class="t-1" href="/get-payment-archive-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/" title="Скачать архивом" ></a></td>
      </tr>';
      }


      //Не оплаченные теслер
      if ($tesler == 1 && $_COOKIE['tesler'] == 1) {
      $tr = (get_service_loan($_GET['service_id'], $month, $year, 3, 4)) ? 'background: rgba(255, 51, 0, 0);background-color: rgba(255, 51, 0, 0.14) !important;' : '';

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
      $summ = get_service_summ($_GET['service_id'], $month, $year, 'TESLER');

      $content_list['total'] += $summ;
      if ($summ) {
      $content_list['body'] .= '<tr style="'.$tr.'">
      <td style="text-align:center;'.$tr.'">'.$year.'.'.$month.'<br><u>TESLER</u><br><br>'.(($tr) ? '<img src="/img/skull.png">' : '').'</td>
      <td><a href="/get-payment-act-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/tesler/">Акт оплаченных работ</a></td>
      <td><a href="/get-payment-bill-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/tesler/">Счет на оплату</a></td>
      <td>'.$summ.',00 руб.</td>
      <td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type4['id'].'">
      <option value="0" '.(($type4['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type4['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>
      <td><table style="    margin: 0 auto;" class="nohov">
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
      </tr></table></td>
      <td align="center" class="linkz"><a class="t-1" href="/get-payment-archive-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/tesler/" title="Скачать архивом" ></a></td>
      </tr>';
      }
      }
      }
      }

      // Все остальные харпер
      if ($_COOKIE['notpayed'] != 1 && $_COOKIE['payed'] != 1) {

      $tr = (get_service_loan($_GET['service_id'], $month, $year, 1, 2)) ? '    background: rgba(255, 51, 0, 0);background-color: rgba(255, 51, 0, 0.14) !important;' : '';

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
      $summ = get_service_summ($_GET['service_id'], $month, $year, 'HARPER');

      if ($summ) {
      $content_list['total'] += $summ;
      $content_list['body'] .= '<tr style="'.$tr.'">
      <td style="text-align:center;'.$tr.'">'.$year.'.'.$month.'<br><u>HARPER</u><br><br>'.(($tr) ? '<img src="/img/skull.png">' : '').'</td>
      <td><a href="/get-payment-act-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/">Акт оплаченных работ</a></td>
      <td><a href="/get-payment-bill-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/">Счет на оплату</a></td>
      <td>'.$summ.',00 руб.</td>
      <td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type2['id'].'">
      <option value="0" '.(($type2['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type2['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>
      <td><table style="    margin: 0 auto;" class="nohov">
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
      </tr></table></td>
      <td align="center" class="linkz"><a class="t-1" href="/get-payment-archive-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/" title="Скачать архивом" ></a></td>
      </tr>';
      }

      // Все остальные теслер
      if ($tesler == 1 && $_COOKIE['tesler'] == 1) {

      $tr = (get_service_loan($_GET['service_id'], $month, $year, 3, 4)) ? 'background: rgba(255, 51, 0, 0);background-color: rgba(255, 51, 0, 0.14) !important;' : '';


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
      $summ = get_service_summ($_GET['service_id'], $month, $year, 'TESLER');

      if ($summ) {
      $content_list['total'] += $summ;
      $content_list['body'] .= '<tr style="'.$tr.'">
      <td style="text-align:center;'.$tr.'">'.$year.'.'.$month.'<br><u>TESLER</u><br><br>'.(($tr) ? '<img src="/img/skull.png">' : '').'</td>
      <td><a href="/get-payment-act-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/tesler/">Акт оплаченных работ</a></td>
      <td><a href="/get-payment-bill-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/tesler/">Счет на оплату</a></td>
      <td>'.$summ.',00 руб.</td>
      <td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type4['id'].'">
      <option value="0" '.(($type4['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type4['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>
      <td><table style="    margin: 0 auto;" class="nohov">
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
      </tr></table></td>
      <td align="center" class="linkz"><a class="t-1" href="/get-payment-archive-admin/'.$_GET['service_id'].'/'.$year.'/'.$month.'/tesler/" title="Скачать архивом" ></a></td>
      </tr>';
      }
      }

      }
      ////
     }
     }

} else {

if ($_GET['date']) {
$where_date = 'and `app_date` REGEXP \''.$_GET['date'].'\'';
}
if ($_GET['year']) {
$where_year = 'and `app_date` REGEXP \''.$_GET['year'].'\'';
}
$sql = mysqli_query($db, 'SELECT * FROM `repairs` where `app_date` != \'\' '.$where_date.' '.$where_year.' and `status_admin` = \'Подтвержден\' and `deleted` = 0 order by `id` DESC;');
      while ($row = mysqli_fetch_array($sql)) {
      //$info = get_request_info($row['id']);
      //$city = get_city($info['city']);

    // echo $tesler;
      if($row['app_date']) {

      $exp = explode('.', $row['app_date']);

      $app[$exp['0']][$exp[1]] = '';
      }


      }
   // print_r($app);
    //array_reverse($app,true);;
    // print_r($app);
     foreach ($app as $year => $val) {
      $year_work = $val;
      foreach ($year_work as $month => $value) {


  $sql2 = mysqli_query($db, 'SELECT * FROM `repairs` where `app_date` REGEXP \''.$year.'.'.$month.'\' and `status_admin` = \'Подтвержден\' and `deleted` = 0 and `app_date` NOT REGEXP \''.$dated.'\' GROUP by `service_id`  order by `id` DESC ;');
      while ($row2 = mysqli_fetch_array($sql2)) {


      $type1 = create_or_get_payment_id($row2['service_id'], $year, $month, 1); //акт
      $type2 = create_or_get_payment_id($row2['service_id'], $year, $month, 2); //счет

      /*Проверяем теслер*/
      $sql3 = mysqli_query($db, 'SELECT * FROM `repairs` where `service_id` = '.$row2['service_id'].' and `app_date` REGEXP \''.$year.'.'.$month.'\' and `status_admin` = \'Подтвержден\' and `deleted` = 0 and `app_date` NOT REGEXP \''.$dated.'\' order by `id` DESC ;');
      while ($row3 = mysqli_fetch_array($sql3)) {
      $model = model_info($row3['model_id']);


      if ($model['brand'] == 'TESLER') {
        $tesler = 1;
       // print_r($row3);
      }
      }

      if ($tesler == 1) {
      $type3 = create_or_get_payment_id($row2['service_id'], $year, $month, 3); //акт
      $type4 = create_or_get_payment_id($row2['service_id'], $year, $month, 4); //счет
      }

      /*/Проверка теслера*/
      $req = get_request_info_serice($row2['service_id']);

      if ($_COOKIE['notpayed'] == 1) {


      if ($type2['status'] == 0) {

  //print_r($type1);
      //$block_style = ($row['block'] == 0) ? '' : 'style="background: rgba(255, 71, 71, 0.13);"';
      /*if ($type1['status'] == 0) {
      $status = '<td style="background: rgba(255, 71, 71, 0.13);">Не оплачено</td>';
      } else {
      $status = '<td style="background: rgba(116, 220, 116, 0.13);">Оплачено</td>';
      }  */

      $tr = (get_service_loan($row2['service_id'], $month, $year, 1, 2)) ? 'background: rgba(255, 51, 0, 0);background-color: rgba(255, 51, 0, 0.14) !important;' : '';

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

      if ($_GET['impo'] != 1) {

      $summ = get_service_summ($row2['service_id'], $month, $year, 'HARPER');

      if ($summ) {
      $content_list['total'] += $summ;
      $content_list['body'] .= '<tr style="'.$tr.'">
      <td style="text-align:center;'.$tr.'">'.$year.'.'.$month.'<br><u>HARPER</u><br><br>'.(($tr) ? '<img src="/img/skull.png">' : '').'</td>
      <td >'.$req['name'].'</td>
      <td><a href="/get-payment-act-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/">Акт оплаченных работ</a></td>
      <td><a href="/get-payment-bill-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/">Счет на оплату</a></td>
      <td>'.$summ.',00 руб.</td>
      <td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type2['id'].'">
      <option value="0" '.(($type2['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type2['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>
      <td><table style="    margin: 0 auto;" class="nohov">
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
      </tr></table></td>
      <td align="center" class="linkz"><a class="t-1" href="/get-payment-archive-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/" title="Скачать архивом" ></a></td>
      </tr>';
      }
      }       else if (($type2['status'] == 1) and ($type1['original'] == 0 || $type2['original'] == 0 || check_custom_loan($row2['service_id']))) {
      $tr = (get_service_loan($row2['service_id'], $month, $year, 1, 2) || check_custom_loan($row2['service_id'])) ? 'background: rgba(255, 51, 0, 0);background-color: rgba(255, 51, 0, 0.14) !important;' : '';

      $summ = get_service_summ($row2['service_id'], $month, $year, 'HARPER');

      if ($summ) {
      $content_list['total'] += $summ;
      $content_list['body'] .= '<tr style="'.$tr.'">
      <td style="text-align:center;'.$tr.'">'.$year.'.'.$month.'<br><u>HARPER</u><br><br>'.(($tr) ? '<img src="/img/skull.png">' : '').'</td>
      <td >'.$req['name'].'</td>
      <td><a href="/get-payment-act-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/">Акт оплаченных работ</a></td>
      <td><a href="/get-payment-bill-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/">Счет на оплату</a></td>
      <td>'.$summ.',00 руб.</td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type2['id'].'">
      <option value="0" '.(($type2['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type2['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>
      <td><table style="    margin: 0 auto;" class="nohov">
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
      </tr></table></td>
      <td align="center" class="linkz"><a class="t-1" href="/get-payment-archive-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/" title="Скачать архивом" ></a><br>'.((check_custom_loan($row2['service_id'])) ? '<a title="Удалить из списка" class="t-5 delete_loan" data-id="'.$row2['service_id'].'" style="" href="#"></a>' : '').'</td>
      </tr>';
      }
      }
       }

      //TESLER
      if ($tesler == 1 && $_COOKIE['tesler'] == 1) {
      $tr = (get_service_loan($row2['service_id'], $month, $year, 3, 4)) ? ' background: rgba(255, 51, 0, 0);background-color: rgba(255, 51, 0, 0.14) !important;' : '';
      if ($type4['status'] == 0) {
      $status2 = '<td style="background: rgba(255, 71, 71, 0.13);">Не оплачено</td>';
      } else {
      $status2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оплачено</td>';
      }
      if ($type1['original'] == 0) {
      $original = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал акта не получен</td>';
      } else {
      $original = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал акта получен</td>';
      }
      if ($type4['original'] == 0) {
      $original2 = '<td style="background: rgba(255, 71, 71, 0.13);">Оригинал счета не получен</td>';
      } else {
      $original2 = '<td style="background: rgba(116, 220, 116, 0.13);">Оригинал счета получен</td>';
      }

      if ($_GET['impo'] != 1) {

      $summ = get_service_summ($row2['service_id'], $month, $year, 'TESLER');

      if ($summ) {
      $content_list['total'] += $summ;
      $content_list['body'] .= '<tr style="'.$tr.'">
      <td style="text-align:center;'.$tr.'">'.$year.'.'.$month.'<br><u>TESLER</u>'.(($tr) ? '<img src="/img/skull.png">' : '').'</td>
      <td >'.$req['name'].'</td>
      <td><a href="/get-payment-act-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/">Акт оплаченных работ</a></td>
      <td><a href="/get-payment-bill-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/">Счет на оплату</a></td>
      <td>'.$summ.',00 руб.</td>
      <td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type4['id'].'">
      <option value="0" '.(($type4['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type4['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>
      <td><table style="    margin: 0 auto;" class="nohov">
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
      </tr></table></td>
      <td align="center" class="linkz"><a class="t-1" href="/get-payment-archive-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/" title="Скачать архивом" ></a></td>
      </tr>';
      }
      }
      else if (($type4['status'] == 1) and ($type3['original'] == 0 || $type4['original'] == 0 || check_custom_loan($row2['service_id']))) {

        $tr = (get_service_loan($row2['service_id'], $month, $year, 3, 4)) ? 'background: rgba(255, 51, 0, 0);background-color: rgba(255, 51, 0, 0.14) !important;' : '';
      $summ = get_service_summ($row2['service_id'], $month, $year, 'TESLER');

      if ($summ) {
      $content_list['total'] += $summ;
      $content_list['body'] .= '<tr style="text-align:center;'.$tr.'">
      <td style="text-align:center;'.$tr.'">'.$year.'.'.$month.'<br><u>TESLER</u><br><br>'.(($tr) ? '<img src="/img/skull.png">' : '').'</td>
      <td >'.$req['name'].'</td>
      <td><a href="/get-payment-act-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/">Акт оплаченных работ</a></td>
      <td><a href="/get-payment-bill-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/">Счет на оплату</a></td>
      <td>'.$summ.',00 руб.</td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type4['id'].'">
      <option value="0" '.(($type4['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type4['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>
      <td><table style="    margin: 0 auto;" class="nohov">
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
      </tr></table></td>
      <td align="center" class="linkz"><a class="t-1" href="/get-payment-archive-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/" title="Скачать архивом" ></a><br>'.((check_custom_loan($row2['service_id'])) ? '<a title="Удалить из списка" class="t-5 delete_loan" data-id="'.$row2['service_id'].'" style="" href="#"></a>' : '').'</td>
      </tr>';
      }
      }

      }
      }

      if ($_COOKIE['payed'] == 1) {


      if ($type2['status'] == 1) {

  //print_r($type1);
      //$block_style = ($row['block'] == 0) ? '' : 'style="background: rgba(255, 71, 71, 0.13);"';
      /*if ($type1['status'] == 0) {
      $status = '<td style="background: rgba(255, 71, 71, 0.13);">Не оплачено</td>';
      } else {
      $status = '<td style="background: rgba(116, 220, 116, 0.13);">Оплачено</td>';
      }  */


      $tr = (get_service_loan($row2['service_id'], $month, $year, 1, 2)) ? 'background: rgba(255, 51, 0, 0);background-color: rgba(255, 51, 0, 0.14) !important;' : '';
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

      if ($_GET['impo'] != 1) {
      $summ = get_service_summ($row2['service_id'], $month, $year, 'HARPER');

      if ($summ) {
      $content_list['total'] += $summ;
      $content_list['body'] .= '<tr style="'.$tr.'">
      <td style="text-align:center;'.$tr.'">'.$year.'.'.$month.'<br><u>HARPER</u><br><br>'.(($tr) ? '<img src="/img/skull.png">' : '').'</td>
      <td >'.$req['name'].'</td>
      <td><a href="/get-payment-act-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/">Акт оплаченных работ</a></td>
      <td><a href="/get-payment-bill-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/">Счет на оплату</a></td>
      <td>'.$summ.',00 руб.</td>
      <td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type2['id'].'">
      <option value="0" '.(($type2['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type2['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>
      <td><table style="    margin: 0 auto;" class="nohov">
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
      </tr></table></td>
      <td align="center" class="linkz"><a class="t-1" href="/get-payment-archive-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/" title="Скачать архивом" ></a></td>
      </tr>';
      }
      } else if (($type2['status'] == 1) and ( $type1['original'] == 0 || $type2['original'] == 0  || check_custom_loan($row2['service_id']))) {

      $tr = (get_service_loan($row2['service_id'], $month, $year, 1, 2) || check_custom_loan($row2['service_id'])) ? ' background: rgba(255, 51, 0, 0);background-color: rgba(255, 51, 0, 0.14) !important;' : '';
      $summ = get_service_summ($row2['service_id'], $month, $year, 'HARPER');

      if ($summ) {

      $content_list['total'] += $summ;
      $content_list['body'] .= '<tr style="'.$tr.'">
      <td style="text-align:center;'.$tr.'">'.$year.'.'.$month.'<br><u>HARPER</u><br><br>'.(($tr) ? '<img src="/img/skull.png">' : '').'</td>
      <td >'.$req['name'].'</td>
      <td><a href="/get-payment-act-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/">Акт оплаченных работ</a></td>
      <td><a href="/get-payment-bill-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/">Счет на оплату</a></td>
      <td>'.$summ.',00 руб.</td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type2['id'].'">
      <option value="0" '.(($type2['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type2['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>
      <td><table style="    margin: 0 auto;" class="nohov">
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
      </tr></table></td>
      <td align="center" class="linkz"><a class="t-1" href="/get-payment-archive-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/" title="Скачать архивом" ></a><br>'.((check_custom_loan($row2['service_id'])) ? '<a title="Удалить из списка" class="t-5 delete_loan" data-id="'.$row2['service_id'].'" style="" href="#"></a>' : '').'</td>
      </tr>';
      }
      }
       }

      //TESLER
            if ($type4['status'] == 1 && $_COOKIE['tesler'] == 1) {

  //print_r($type3);
      //$block_style = ($row['block'] == 0) ? '' : 'style="background: rgba(255, 71, 71, 0.13);"';
      /*if ($type3['status'] == 0) {
      $status = '<td style="background: rgba(255, 71, 71, 0.13);">Не оплачено</td>';
      } else {
      $status = '<td style="background: rgba(116, 220, 116, 0.13);">Оплачено</td>';
      }  */

      $tr = (get_service_loan($row2['service_id'], $month, $year, 3, 4)) ? ' background: rgba(255, 51, 0, 0);background-color: rgba(255, 51, 0, 0.14) !important;' : '';

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

      if ($_GET['impo'] != 1) {
      $summ = get_service_summ($row2['service_id'], $month, $year, 'TESLER');

      if ($summ) {
      $content_list['total'] += $summ;
      $content_list['body'] .= '<tr style="'.$tr.'">
      <td style="text-align:center;'.$tr.'">'.$year.'.'.$month.'<br><u>TESLER</u><br><br>'.(($tr) ? '<img src="/img/skull.png">' : '').'</td>
      <td >'.$req['name'].'</td>
      <td><a href="/get-payment-act-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/">Акт оплаченных работ</a></td>
      <td><a href="/get-payment-bill-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/">Счет на оплату</a></td>
      <td>'.$summ.',00 руб.</td>
      <td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type4['id'].'">
      <option value="0" '.(($type4['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type4['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>
      <td><table style="    margin: 0 auto;" class="nohov">
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
      </tr></table></td>
      <td align="center" class="linkz"><a class="t-1" href="/get-payment-archive-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/" title="Скачать архивом" ></a></td>
      </tr>';
      }
      } else if (($type4['status'] == 1) and ($type3['original'] == 0 || $type4['original'] == 0  || check_custom_loan($row2['service_id']))) {

   $tr = (get_service_loan($row2['service_id'], $month, $year, 3, 4)  || check_custom_loan($row2['service_id'])) ? ' background: rgba(255, 51, 0, 0);background-color: rgba(255, 51, 0, 0.14) !important;' : '';
      $summ = get_service_summ($row2['service_id'], $month, $year, 'TESLER');

      if ($summ) {
      $content_list['total'] += $summ;
      $content_list['body'] .= '<tr style="'.$tr.'">
      <td style="text-align:center;'.$tr.'">'.$year.'.'.$month.'<br><u>TESLER</u><br><br>'.(($tr) ? '<img src="/img/skull.png">' : '').'</td>
      <td >'.$req['name'].'</td>
      <td><a href="/get-payment-act-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/">Акт оплаченных работ</a></td>
      <td><a href="/get-payment-bill-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/">Счет на оплату</a></td>
      <td>'.$summ.',00 руб.</td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type4['id'].'">
      <option value="0" '.(($type4['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type4['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>
      <td><table style="    margin: 0 auto;" class="nohov">
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
      </tr></table></td>
      <td align="center" class="linkz"><a class="t-1" href="/get-payment-archive-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/" title="Скачать архивом" ></a><br>'.((check_custom_loan($row2['service_id'])) ? '<a title="Удалить из списка" class="t-5 delete_loan" data-id="'.$row2['service_id'].'" style="" href="#"></a>' : '').'</td>
      </tr>';
      }
      }
      }
      }

      if ($_COOKIE['notpayed'] != 1 && $_COOKIE['payed'] != 1) {

      $tr = (get_service_loan($row2['service_id'], $month, $year, 1, 2)) ? 'background: rgba(255, 51, 0, 0);background-color: rgba(255, 51, 0, 0.14) !important;' : '';

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

      if ($_GET['impo'] != 1) {  
      $summ = get_service_summ($row2['service_id'], $month, $year, 'HARPER');

      if ($summ) {

      $content_list['total'] += $summ;
      $content_list['body'] .= '<tr style="'.$tr.'">
      <td style="text-align:center;'.$tr.'">'.$year.'.'.$month.'<br><u>HARPER</u><br><br>'.(($tr) ? '<img src="/img/skull.png">' : '').'</td>
      <td >'.$req['name'].'</td>
      <td><a href="/get-payment-act-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/">Акт оплаченных работ</a></td>
      <td><a href="/get-payment-bill-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/">Счет на оплату</a></td>
      <td>'.$summ.',00 руб.</td>
      <td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type2['id'].'">
      <option value="0" '.(($type2['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type2['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>
      <td><table style="    margin: 0 auto;" class="nohov">
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
      </tr></table></td>
      <td align="center" class="linkz"><a class="t-1" href="/get-payment-archive-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/" title="Скачать архивом" ></a></td>
      </tr>';
      }
      } else if (($type2['status'] == 1) and ($type1['original'] == 0 || $type2['original'] == 0  || check_custom_loan($row2['service_id']))) {

      $summ = get_service_summ($row2['service_id'], $month, $year, 'HARPER');

       $tr = (get_service_loan($row2['service_id'], $month, $year, 1, 2)  || check_custom_loan($row2['service_id'])) ? ' background: rgba(255, 51, 0, 0);background-color: rgba(255, 51, 0, 0.14) !important;' : '';

      if ($summ) {

      $content_list['total'] += $summ;
      $content_list['body'] .= '<tr style="'.$tr.'">
      <td style="text-align:center;'.$tr.'">'.$year.'.'.$month.'<br><u>HARPER</u><br><br>'.(($tr) ? '<img src="/img/skull.png">' : '').'</td>
      <td >'.$req['name'].'</td>
      <td><a href="/get-payment-act-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/">Акт оплаченных работ</a></td>
      <td><a href="/get-payment-bill-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/">Счет на оплату</a></td>
      <td>'.$summ.',00 руб.</td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type2['id'].'">
      <option value="0" '.(($type2['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type2['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>
      <td><table style="    margin: 0 auto;" class="nohov">
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
      </tr></table></td>
      <td align="center" class="linkz"><a class="t-1" href="/get-payment-archive-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/" title="Скачать архивом" ></a><br>'.((check_custom_loan($row2['service_id'])) ? '<a title="Удалить из списка" class="t-5 delete_loan" data-id="'.$row2['service_id'].'" style="" href="#"></a>' : '').'</td>
      </tr>';
      }
      }

      }

      //TESLER
            if ($_COOKIE['notpayed'] != 1 && $_COOKIE['payed'] != 1) {

          if ($tesler == 1 && $_COOKIE['tesler'] == 1) {
      $tr = (get_service_loan($row2['service_id'], $month, $year, 3, 4)) ? 'background: rgba(255, 51, 0, 0);background-color: rgba(255, 51, 0, 0.14) !important;' : '';
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

      if ($_GET['impo'] != 1) {
      $summ = get_service_summ($row2['service_id'], $month, $year, 'TESLER');
      if ($summ) {

      $content_list['total'] += $summ;
      $content_list['body'] .= '<tr style="'.$tr.'">
      <td style="text-align:center;'.$tr.'">'.$year.'.'.$month.'<br><u>TESLER</u><br><br>'.(($tr) ? '<img src="/img/skull.png">' : '').'</td>
      <td >'.$req['name'].'</td>
      <td><a href="/get-payment-act-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/">Акт оплаченных работ</a></td>
      <td><a href="/get-payment-bill-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/">Счет на оплату</a></td>
      <td>'.$summ.',00 руб.</td>
      <td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type4['id'].'">
      <option value="0" '.(($type4['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type4['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>
      <td><table style="    margin: 0 auto;" class="nohov">
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
      </tr></table></td>
      <td align="center" class="linkz"><a class="t-1" href="/get-payment-archive-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/" title="Скачать архивом" ></a></td>
      </tr>';
      }
      } else if (($type4['status'] == 1) and ($type3['original'] == 0 || $type4['original'] == 0  || check_custom_loan($row2['service_id']))) {

      $summ = get_service_summ($row2['service_id'], $month, $year, 'TESLER');
      
      $tr = (get_service_loan($row2['service_id'], $month, $year, 3, 4)  || check_custom_loan($row2['service_id'])) ? ' background: rgba(255, 51, 0, 0);background-color: rgba(255, 51, 0, 0.14) !important;' : '';
      if ($summ) {
      $content_list['total'] += $summ;
      $content_list['body'] .= '<tr style="'.$tr.'">
      <td style="text-align:center;'.$tr.'">'.$year.'.'.$month.'<br><u>TESLER</u><br><br>'.(($tr) ? '<img src="/img/skull.png">' : '').'</td>
      <td >'.$req['name'].'</td>
      <td><a href="/get-payment-act-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/">Акт оплаченных работ</a></td>
      <td><a href="/get-payment-bill-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/">Счет на оплату</a></td>
      <td>'.$summ.',00 руб.</td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="'.$type4['id'].'">
      <option value="0" '.(($type4['status'] == 0) ? 'selected' : '').'>Не оплачено</option>
      <option value="1" '.(($type4['status'] == 1) ? 'selected' : '').'>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td>
      <td><table style="    margin: 0 auto;" class="nohov">
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
      </tr></table></td>
      <td align="center" class="linkz"><a class="t-1" href="/get-payment-archive-admin/'.$row2['service_id'].'/'.$year.'/'.$month.'/tesler/" title="Скачать архивом" ></a><br>'.((check_custom_loan($row2['service_id'])) ? '<a title="Удалить из списка" class="t-5 delete_loan" data-id="'.$row2['service_id'].'" style="" href="#"></a>' : '').'</td>
      </tr>';
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
    $content = content_list();
//}



function services_select($cat_id = '') {
  global $db;
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
<title>Панель управления</title>
<link href="<?=$config['url'];?>css/fonts.css" rel="stylesheet" />
<link href="<?=$config['url'];?>css/style.css" rel="stylesheet" />
<script src="/_new-codebase/front/vendor/jquery/jquery-1.7.2.min.js"  ></script>
<script src="<?=$config['url'];?>js/jquery-ui.min.js"></script>
<script src="<?=$config['url'];?>js/jquery.placeholder.min.js"></script>
<script src="<?=$config['url'];?>js/jquery.formstyler.min.js"></script>
<script src="<?=$config['url'];?>js/main.js"></script>
<link rel="stylesheet" href="/_new-codebase/front/vendor/select2/4.0.4/select2.min.css" />
<script src="/_new-codebase/front/vendor/select2/4.0.4/select2.full.min.js"></script>
<script src="<?=$config['url'];?>notifier/js/index.js"></script>
<link rel="stylesheet" type="text/css" href="<?=$config['url'];?>notifier/css/style.css">
<link rel="stylesheet" href="/_new-codebase/front/vendor/animate.min.css" />
<script src='/_new-codebase/front/vendor/mustache/mustache.min.js'></script>
<script src="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.concat.min.js"></script>
<link rel="stylesheet" href="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.min.css" />

<script  src="/_new-codebase/front/vendor/datatables/1.10.12/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?=$config['url'];?>css/datatables.css">

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
     /* stateSave:true, */
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

    $(document).on('selectmenuchange', 'select[name=status_act]', function() {
        var value = $(this).val();
        var id= $(this).data('pay-id');
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
           <h2>Платежные документы2</h2>
           <br>
  <div class="adm-catalog">
    <div class="dates_block" style="vertical-align:middle;display:inline-block;height: 92px;padding-right:10px;">
      <h3>Скачивание по месяцам</h3><br>
     <form method="POST" action="/get-payment-archive-full/">
    <div style="display:inline-block;padding-left:20px;"><span style="width: 95px;display: inline-block;text-align:right;position:relative;">Год и месяц </span> <input type="text" class="monthPicker" name="date" style="width: 120px;    text-align: center;    height: 40px;    padding: 0;" value="<?=($_GET['date1'] ? $_GET['date1'] : '')?>"/></div>
    <div style="display:inline-block;"><input class="green_button" type="submit" style="display: inline-block;margin-left:15px;  vertical-align: middle;    height: 40px;    margin-top: -4px;" value="Скачать" /></div>
    </form>
    </div><div class="dates_block" style="vertical-align:middle;display:inline-block;height: 92px;border-left: 1px solid #ccc;    padding-left: 10px;">
        <h3>Фильтрация</h3>
     <form method="GET" style="padding-top:3px;">
    <div style="display:inline-block;padding-left:20px;"><span style="width: 95px;display: inline-block;text-align:left;position:relative;">Год и месяц </span><br> <input type="text" class="monthPicker" name="date" style="width: 120px;    text-align: center;    height: 40px;    padding: 0;" value="<?=($_GET['date'] ? $_GET['date'] : '')?>"/></div>
    <div style="display:inline-block;padding-left:20px;"><span style="width: 5px;display: inline-block;text-align:left;position:relative;font-size: 25px;">/</span></div>
    <div style="display:inline-block;padding-left:20px;"> <span style="width: 95px;display: inline-block;text-align:left;position:relative;">Год </span><br> <input type="text" class="monthPicker2" name="year" style="width: 120px;    text-align: center;    height: 40px;    padding: 0;" value="<?=($_GET['year'] ? $_GET['year'] : '')?>"/></div>
    <div style="display:inline-block;"><span style="width: 70px;display: inline-block;text-align:right;">Сервис </span>&nbsp;&nbsp;<select class="select2 nomenu" name="service_id"><option value="all">Все сервисы</option><?=services_select();?></select><input class="green_button" type="submit" style="display: inline-block;margin-left:15px;  vertical-align: middle;    height: 40px;    margin-top: -4px;" value="Применить" /></div>
    </form>
    </div>
    <hr> <br>
     <div style="vertical-align:middle;">


    <form method="POST" id="checkb">
    <table style="">
              <tr>

              <td> <div class="add" style="padding-top:0px;display:inline-block;">
     <a style="width: auto;padding-left: 7px;padding-right: 7px;background:#EB0000;color:#fff;vertical-align: middle;" href="/payments/?impo=1" class="button">Должники</a>
    </div></td>
    <td style="padding: 20px; ">Оплаченные <input type="checkbox" value="1" name="payed" <?=($_COOKIE['payed'] == '1') ? 'checked' : '';?>> &nbsp;&nbsp;&nbsp;&nbsp; Неоплаченные <input type="checkbox" value="1" name="notpayed" <?=($_COOKIE['notpayed'] == 1 ) ? 'checked' : '';?> >&nbsp;&nbsp;&nbsp;&nbsp; TESLER <input type="checkbox" value="1" name="tesler" <?=($_COOKIE['tesler'] == 1 ) ? 'checked' : '';?> ><br></td>
    </tr>
            </table>
        </form>
    </div><?php if ($_GET['impo'] == 1) {?>
    <table style="    position: absolute;    right: 0px;     top: 432px;">

               <tr>

    <td colspan="2" style="padding: 20px; "> <form method="POST" style="padding-top:3px;">
    <div style="display:inline-block;"><span style="width: 200px;display: inline-block;text-align:right;">Добавить в должники </span>&nbsp;&nbsp;<select class="select2 nomenu" name="add_to_black"><?=services_select();?></select><input class="green_button" type="submit" style="    background: #EB0000;display: inline-block;margin-left:15px;  vertical-align: middle;    height: 40px;    margin-top: -4px;" value="Применить" /></div>
    </form></td>
    </tr>


    </table>
      <?php } ?>
          <br><br>
  <table id="table_content" class="display" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th align="center" style="text-align:center;">Период</th>
                <?php if ($_GET['service_id'] == 'all' || !$_GET['service_id']) {
                 echo '<th align="left">СЦ</th>';
                };?>
                <th align="left">Акт выполненных работ</th>
                <th align="left">Счет на оплату</th>
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