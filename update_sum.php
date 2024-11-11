<?php
# Подключаем  конфиг:
require_once('includes/configuration.php');
# Подключаем функции:
require_once('includes/functions.php');

function get_request_info($id) {
  global $db;
return mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `requests` WHERE `user_id` = '.$id));
}

function get_request_info_serice($id) {
  global $db;
$req = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `requests` WHERE `user_id` = '.$id));
return $req;
}



//mysqli_query($db, 'UPDATE `pay_billing` set sum = \'\' where `year` = 2018');
mysqli_query($db, 'UPDATE `pay_billing` set sum = \'\' where `year` = 2019 and service_id != 33');

$dated = ((date("d") < 5) ? date("Y.m") : date("Y.m"));

$sql = mysqli_query($db, 'SELECT * FROM `repairs` where `app_date` != \'\' and (`status_admin` = \'Подтвержден\' OR `status_admin` = \'Выдан\') and `deleted` = 0 order by `id` DESC;');
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


  $sql2 = mysqli_query($db, 'SELECT * FROM `repairs` where `app_date` REGEXP \''.$year.'.'.$month.'\' and (`status_admin` = \'Подтвержден\' OR `status_admin` = \'Выдан\') and `deleted` = 0 and `app_date` NOT REGEXP \''.$dated.'\' GROUP by `service_id`  order by `id` DESC ;');
      while ($row2 = mysqli_fetch_array($sql2)) {


      $type1 = create_or_get_payment_id($row2['service_id'], $year, $month, 1); //акт
      $type2 = create_or_get_payment_id($row2['service_id'], $year, $month, 2); //счет

      /*Проверяем теслер*/
      $sql3 = mysqli_query($db, 'SELECT * FROM `repairs` where `service_id` = '.$row2['service_id'].' and `app_date` REGEXP \''.$year.'.'.$month.'\' and (`status_admin` = \'Подтвержден\' OR `status_admin` = \'Выдан\') and `deleted` = 0 and `app_date` NOT REGEXP \''.$dated.'\' order by `id` DESC ;');
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


      if ($sven == 1) {
      $type9 = create_or_get_payment_id($row2['service_id'], $year, $month, 9); //акт
      $type10 = create_or_get_payment_id($row2['service_id'], $year, $month, 10); //счет
      }

      //if (check_returns_pls($content['return_id'])) {
     // echo $content['return_id'];
     // unset($content);
      /*/Проверка теслера*/


      if ($_COOKIE['payed'] == 1) {

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
      $summ = get_service_summ_fast($row2['service_id'], $month, $year, 'HARPER');
      //echo 'harper: '.$month.':'.$year.'<br>';
      } else {
      $summ = $type2['sum'];
      }

      $content_list['total'] += $summ;
      if ($summ) {
      $content_list['body'] .= '<tr>
      <td >'.$year.'.'.$month.'<br><u>HARPER</u></td>
      <td><a href="/get-payment-act/'.$year.'/'.$month.'/">Акт выполненных работ</a></td>
      <td><a href="/get-payment-bill/'.$year.'/'.$month.'/">Счет на оплату</a></td>
      <td>'.$summ.',00 руб.</td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$status.'</tr><tr>'.$status2.'</tr></table></td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$original.'</tr><tr>'.$original2.'</tr></table></td>
      <td align="center" class="linkz"><a class="t-1" href="/get-payment-archive/'.$year.'/'.$month.'/" title="Скачать архивом" ></a></td>
      </tr>';
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
      //$summ = get_service_summ_fast($row2['service_id'], $month, $year, 'TESLER');

      if (!$type4['sum']) {
      $summ = get_service_summ_fast($row2['service_id'], $month, $year, 'TESLER');
     // echo 'tesler: '.$month.':'.$year.'<br>';
      } else {
      $summ = $type4['sum'];
      }

      $content_list['total'] += $summ;
      if ($summ) {
      $content_list['body'] .= '<tr>
      <td >'.$year.'.'.$month.'<br><u>TESLER</u></td>
      <td><a href="/get-payment-act/'.$year.'/'.$month.'/tesler/">Акт выполненных работ</a></td>
      <td><a href="/get-payment-bill/'.$year.'/'.$month.'/tesler/">Счет на оплату</a></td>
      <td>'.$summ.',00 руб.</td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$status.'</tr><tr>'.$status2.'</tr></table></td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$original.'</tr><tr>'.$original2.'</tr></table></td>
      <td align="center" class="linkz"><a class="t-1" href="/get-payment-archive/'.$year.'/'.$month.'/tesler/" title="Скачать архивом" ></a></td>
      </tr>';
      }
      }
      }

      }

      }

      if ($_COOKIE['notpayed'] == 1) {

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
      //$summ = get_service_summ_fast($row2['service_id'], $month, $year, 'HARPER');

      if (!$type2['sum']) {
      $summ = get_service_summ_fast($row2['service_id'], $month, $year, 'HARPER');
      //echo 'harper: '.$month.':'.$year.'<br>';
      } else {
      $summ = $type2['sum'];
      }


      $content_list['total'] += $summ;
      if ($summ) {
      $content_list['body'] .= '<tr>
      <td >'.$year.'.'.$month.'<br><u>HARPER</u></td>
      <td><a href="/get-payment-act/'.$year.'/'.$month.'/">Акт выполненных работ</a></td>
      <td><a href="/get-payment-bill/'.$year.'/'.$month.'/">Счет на оплату</a></td>
      <td>'.$summ.',00 руб.</td>
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
      $summ = get_service_summ_fast($row2['service_id'], $month, $year, 'TESLER');
      //echo 'tesler: '.$month.':'.$year.'<br>';
      } else {
      $summ = $type4['sum'];
      }

      $content_list['total'] += $summ;
      if ($summ) {
      $content_list['body'] .= '<tr>
      <td >'.$year.'.'.$month.'<br><u>TESLER</u></td>
      <td><a href="/get-payment-act/'.$year.'/'.$month.'/tesler/">Акт выполненных работ</a></td>
      <td><a href="/get-payment-bill/'.$year.'/'.$month.'/tesler/">Счет на оплату</a></td>
      <td>'.$summ.',00 руб.</td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$status.'</tr><tr>'.$status2.'</tr></table></td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$original.'</tr><tr>'.$original2.'</tr></table></td>
      <td align="center" class="linkz"><a class="t-1" href="/get-payment-archive/'.$year.'/'.$month.'/tesler/" title="Скачать архивом" ></a></td>
      </tr>';
      }
      }
      }

      }

      }

if ($_COOKIE['notpayed'] != 1 && $_COOKIE['payed'] != 1) {


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
      $summ = get_service_summ_fast($row2['service_id'], $month, $year, 'HARPER');
      //echo 'harper: '.$month.':'.$year.'<br>';
      } else {
      $summ = $type2['sum'];
      }


      $content_list['total'] += $summ;



      if ($summ) {
      $content_list['body'] .= '<tr>
      <td >'.$year.'.'.$month.'<br><u>HARPER</u></td>
      <td><a href="/get-payment-act/'.$year.'/'.$month.'/">Акт выполненных работ</a></td>
      <td><a href="/get-payment-bill/'.$year.'/'.$month.'/">Счет на оплату</a></td>
      <td>'.$summ.',00 руб.</td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$status.'</tr><tr>'.$status2.'</tr></table></td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$original.'</tr><tr>'.$original2.'</tr></table></td>
      <td align="center" class="linkz"><a class="t-1" href="/get-payment-archive/'.$year.'/'.$month.'/" title="Скачать архивом" ></a></td>
      </tr>';
      }


      if ($tesler == 1) {


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
      $summ = get_service_summ_fast($row2['service_id'], $month, $year, 'TESLER');
      //echo 'tesler: '.$month.':'.$year.'<br>';
      } else {
      $summ = $type4['sum'];
      }

      $content_list['total'] += $summ;



      if ($summ) {
      $content_list['body'] .= '<tr>
      <td >'.$year.'.'.$month.'<br><u>TESLER</u></td>
      <td><a href="/get-payment-act/'.$year.'/'.$month.'/tesler/">Акт выполненных работ</a></td>
      <td><a href="/get-payment-bill/'.$year.'/'.$month.'/tesler/">Счет на оплату</a></td>
      <td>'.$summ.',00 руб.</td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$status.'</tr><tr>'.$status2.'</tr></table></td>
      <td><table style="    margin: 0 auto;" class="nohov"><tr>'.$original.'</tr><tr>'.$original2.'</tr></table></td>
      <td align="center" class="linkz"><a class="t-1" href="/get-payment-archive/'.$year.'/'.$month.'/tesler/" title="Скачать архивом" ></a></td>
      </tr>';
      }

      }


      }

      }

     }


}



//mysqli_query($db, 'UPDATE `pay_billing` set sum = \'-\' where `sum` = \'\'');

?>