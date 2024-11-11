<?php

use models\User;
use models\Users;
use program\core;

require '_new-codebase/front/templates/main/parts/common.php';


function content_list()
{
  global $db;
  $content_list = ['body' => '', 'dates_interval' => '', 'pagination_html' => ''];

  $qrt = !empty($_POST['qrt']) ? $_POST['qrt'] : '';
  $year = $_POST['year'] ?? date('Y');
  $dates = getDatesIntervalByQuarter($qrt, $year);
  $content_list['dates_interval'] = core\Time::formatVerbose($dates['from']) . ' - ' . core\Time::formatVerbose($dates['to']);

  $sql = mysqli_query($db, 'SELECT * FROM `combine` WHERE `create_date` BETWEEN "' . $dates['from'] . '" AND "' . $dates['to'] . '" ORDER BY `create_date` DESC;');

  while ($row = mysqli_fetch_array($sql)) {
    //$info = get_request_info($row['id']);
    //$city = get_city($info['city']);

    $sql2 = mysqli_query($db, 'SELECT * FROM `combine_links` where `combine_id` = ' . $row['id'] . ' ;');
    while ($row2 = mysqli_fetch_array($sql2)) {
      if (get_payment_info_combined($row2['pay_billing_id'])['type'] == 1 || get_payment_info_combined($row2['pay_billing_id'])['type'] == 2) {
        $body[$row['id']]['brands'][$row['date']][0] = 'HARPER';
      } else if (get_payment_info_combined($row2['pay_billing_id'])['type'] == 3 || get_payment_info_combined($row2['pay_billing_id'])['type'] == 4) {
        $body[$row['id']]['brands'][$row['date']][1] = 'TESLER';
      } else if (get_payment_info_combined($row2['pay_billing_id'])['type'] == 5 || get_payment_info_combined($row2['pay_billing_id'])['type'] == 6) {
        $body[$row['id']]['brands'][$row['date']][2] = 'OPTIMA';
      } else if (get_payment_info_combined($row2['pay_billing_id'])['type'] == 7 || get_payment_info_combined($row2['pay_billing_id'])['type'] == 8) {
        $body[$row['id']]['brands'][$row['date']][3] = 'OPTIMAPPPO';
      } else if (get_payment_info_combined($row2['pay_billing_id'])['type'] == 9 || get_payment_info_combined($row2['pay_billing_id'])['type'] == 10) {
        $body[$row['id']]['brands'][$row['date']][4] = 'SVEN';
      } else if (get_payment_info_combined($row2['pay_billing_id'])['type'] == 13 || get_payment_info_combined($row2['pay_billing_id'])['type'] == 14) {
        $body[$row['id']]['brands'][$row['date']][5] = 'SELENGA';
      } else if (get_payment_info_combined($row2['pay_billing_id'])['type'] == 15 || get_payment_info_combined($row2['pay_billing_id'])['type'] == 16) {
        $body[$row['id']]['brands'][$row['date']][6] = 'ROCH';
      }
    }
  }



  foreach ($body as $combined => $brands) {

    foreach ($brands['brands'] as $date => $brand) {
      foreach ($brand as $brand_name) {
        $combine_info = get_combined($combined);
        if ($brand_name != 'TESLER' && $brand_name != 'HARPER') {
          $combine_info_new = get_combined_new($combined, $brand_name);
        }
        $brand_id = ($brand_name == 'TESLER') ? '-T' : '';

        if ($brand_name != 'TESLER' && $brand_name != 'HARPER') {
          $brand_id = '-' . $brand_name;
        }

        $brand_tesler = ($brand_name == 'TESLER') ? 1 : 0;
        $brand_roch = ($brand_name == 'ROCH') ? 1 : 0;
        $brand_name2 = strtolower($brand_name);
        $date_exp = explode('.', $date);
        $content_list['body'] .= '<tr>
              <td >' . $combined . $brand_id . '</td>
              <td >' . $brand_name . '</td>
              <td >' . $date . '</td>
              <td >' . (get_sum_combined($combined, $brand_name) - get_service_summ_without_payed(User::getData('id'), $date_exp['1'], $date_exp['0'], $brand_name)) . 'р.</td>
              <td style="text-align:center">
              <a class="t-1" style="    width: 16px;     height: 16px;      display: inline-block;    background: url(/img/s.jpg) 0 0 no-repeat;" title="Скачать счета" href="/get-all-bills/' . $combined . '/' . $brand_name2 . '/" ></a>
              <a class="t-1" style="     width: 16px;    height: 16px;     display: inline-block;   background: url(/img/a.jpg) 0 0 no-repeat;" title="Скачать отчет агента" href="/get-all-agent/' . $combined . '/' . $brand_name2 . '/" ></a>
              </td>

      <td><table style="    margin: 0 auto;" class="nohov min_width"><tr><td>
      <form method="POST">
      <select  name="status_pay" data-pay-id="' . $combine_info['id'] . '" data-more="' . (($brand_name != 'TESLER' && $brand_name != 'HARPER') ? $brand_name : '') . '" data-tesler="' . $brand_tesler . '">
      <option value="0" ' . ((($combine_info['payed'] == 0 && $brand_name != 'TESLER') || ($brand_name == 'TESLER' && $combine_info['payed_tesler'] == 0) || ($brand_name != 'TESLER' && $brand_name != 'HARPER' && $combine_info_new['payed'] == 0)) ? 'selected' : '') . '>Не оплачено</option>
      <option value="1" ' . ((($combine_info['payed'] == 1 && $brand_name != 'TESLER') || ($brand_name == 'TESLER' && $combine_info['payed_tesler'] == 1)  || ($brand_name != 'TESLER' && $brand_name != 'HARPER' && $combine_info_new['payed'] == 1)) ? 'selected' : '') . '>Оплачено</option>
      </select>
      </form>
      </td></tr></table></td><td><table style="    margin: 0 auto;" class="nohov">
      <tr>
      <td>
      <form method="POST">
      <select  name="status_act" data-pay-id="' . $combine_info['id'] . '" data-more="' . (($brand_name != 'TESLER' && $brand_name != 'HARPER') ? $brand_name : '') . '" data-tesler="' . $brand_tesler . '">
      <option value="0" ' . ((($combine_info['act'] == 0  && $brand_name != 'TESLER')  || ($brand_name == 'TESLER' && $combine_info['act_tesler'] == 0)  || ($brand_name != 'TESLER' && $brand_name != 'HARPER' && $combine_info_new['act'] == 0)) ? 'selected' : '') . '>Оригинал акта не получен</option>
      <option value="1" ' . ((($combine_info['act'] == 1  && $brand_name != 'TESLER')  || ($brand_name == 'TESLER' && $combine_info['act_tesler'] == 1)  || ($brand_name != 'TESLER' && $brand_name != 'HARPER' && $combine_info_new['act'] == 1)) ? 'selected' : '') . '>Оригинал акта получен</option>
      </select>
      </form>
      </td>
      </tr>
      <tr>
      <td>
      <form method="POST">
      <select  name="status_bill" data-pay-id="' . $combine_info['id'] . '" data-more="' . (($brand_name != 'TESLER' && $brand_name != 'HARPER') ? $brand_name : '') . '" data-tesler="' . $brand_tesler . '">
      <option value="0" ' . ((($combine_info['bill'] == 0  && $brand_name != 'TESLER') || ($brand_name == 'TESLER' && $combine_info['bill_tesler'] == 0)) ? 'selected' : ''  || ($brand_name != 'TESLER' && $brand_name != 'HARPER' && $combine_info_new['bill'] == 0)) . '>Оригинал счета не получен</option>
      <option value="1" ' . ((($combine_info['bill'] == 1  && $brand_name != 'TESLER') || ($brand_name == 'TESLER' && $combine_info['bill_tesler'] == 1)) ? 'selected' : ''  || ($brand_name != 'TESLER' && $brand_name != 'HARPER' && $combine_info_new['bill'] == 1))  . '>Оригинал счета получен</option>
      </select>
      </form>
      </td>
      </tr></table></td>
              <td style="text-align:center">
              <a class="dwn" href="/get-payment-act-admin-v2/' . $combined . '/' . $brand_name . '/">Скачать акт</a> /
              <a class="dwn" href="/get-payment-bill-admin-v2/' . $combined . '/' . $brand_name . '/">Скачать счет</a>
              </td>
              <td align="center" class="linkz" >
              <a class="t-4" title="Все документы" data-fancybox autoScale=false data-type="iframe" data-src="/payments-from-combined/' . $combined . '/' . $brand_name . '/" href="javascript:;" ></a>
              <a class="t-5" title="Удалить обобщение" href="/del-combined/' . $combined . '/" onclick=\'return confirm("Удаление обобщение?")\'></a>
              </td>
              </tr>';
      }
    }
  }

  return $content_list;
}

$content = content_list();

function get_payment_info_combined($pay_id)
{
  global $db;
  $sql = mysqli_query($db, 'SELECT * FROM `pay_billing` where `id` = ' . $pay_id);
  $row = mysqli_fetch_array($sql);
  return $row;
}

function get_combined($pay_id)
{
  global $db;
  $sql = mysqli_query($db, 'SELECT * FROM `combine` where `id` = ' . $pay_id);
  $row = mysqli_fetch_array($sql);
  return $row;
}

function get_combined_new($pay_id, $brand)
{
  global $db;

  $current = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `combine_docs` WHERE `combine_id` = ' . $pay_id . ' and brand = \'' . $brand . '\' LIMIT 1;'));

  if ($current['COUNT(*)'] == 0) {
    mysqli_query($db, 'INSERT INTO `combine_docs` (
            `combine_id`,
            `brand`
            ) VALUES (
            \'' . mysqli_real_escape_string($db, $pay_id) . '\',
            \'' . mysqli_real_escape_string($db, $brand) . '\'
            );') or mysqli_error($db);
  }

  $sql = mysqli_query($db, 'SELECT * FROM `combine_docs` where `combine_id` = ' . $pay_id . ' and brand = \'' . $brand . '\' ');
  $row = mysqli_fetch_array($sql);

  return $row;
}

function services_list2()
{
  global $db;
  $sql = mysqli_query($db, 'SELECT * FROM `'.Users::TABLE.'` where `status_id` = 1  and `role_id` NOT IN (4,5);');
  while ($row = mysqli_fetch_array($sql)) {
    $info = get_request_info_by_user_id($row['id']);
    if ($info['name'] != '') {
      if ($_GET['service_id'] == $row['id']) {
        $content .= '<option value="' . $row['id'] . '" selected>' . $info['name'] . '</option>';
      } else {
        $content .= '<option value="' . $row['id'] . '" >' . $info['name'] . '</option>';
      }
    }
  }
  return $content;
}

function brands($id = '')
{
  global $db;
  $sql = mysqli_query($db, 'SELECT * FROM `brands` WHERE `is_deleted` = 0;');
  while ($row = mysqli_fetch_array($sql)) {
    $content .= '<li><label><input type="checkbox" name="brands[]" value="' . $row['name'] . '" checked/>' . $row['name'] . '</label></li>';
  }
  return $content;
}

function brands2($id = '')
{
  global $db;
  $sql = mysqli_query($db, 'SELECT * FROM `brands` WHERE `is_deleted` = 0;');
  while ($row = mysqli_fetch_array($sql)) {
    if ($row['id'] != 4) {
      $content .= '<li><label><input type="checkbox" name="brands2[]" value="' . $row['name'] . '" checked/>' . $row['name'] . '</label></li>';
    } else {
      $content .= '<li><label><input type="checkbox" name="brands2[]" value="' . $row['name'] . '" />' . $row['name'] . '</label></li>';
    }
  }
  return $content;
}


function get_sum_combined($combined_id, $brand)
{
  global $db;

  $combine = mysqli_fetch_array(mysqli_query($db, 'SELECT `date`,`id` FROM `combine` WHERE `id` = \'' . mysqli_real_escape_string($db, $combined_id) . '\';'));
  $sql2 = mysqli_query($db, 'SELECT `pay_billing_id` FROM `combine_links` where `combine_id` = ' . $combine['id'] . ' and `type` IN (1,3,5,7,9);');
  while ($row2 = mysqli_fetch_array($sql2)) {
    $info = get_payment_info($row2['pay_billing_id']);
    $info['month'] = ($info['month'] < 10) ? '0' . $info['month'] : $info['month'];
    $typer = ($info['type'] > 1) ? 'TESLER' : 'HARPER';
    if ($brand == 'TESLER' && $info['type'] > 1) {
      $summ += get_service_summ($info['service_id'], $info['month'], $info['year'], $typer);
    } else if ($brand == 'HARPER' && $info['type'] < 3) {

      $summ += get_service_summ($info['service_id'], $info['month'], $info['year'], $typer);
    } else if ($brand != 'TESLER' && $brand != 'HARPER' && $info['type'] > 12) {

      $summ += get_service_summ($info['service_id'], $info['month'], $info['year'], $brand);
    }
  }
  $brand_info = brand_info_get($brand);
  $summ_full = $summ + $summ * $brand_info['percent'];
  if ($combine['id'] == 139 && $brand == 'TESLER') {
	$summ_full = $summ_full - 420;
  }
  return $summ_full;
}

?>
<!doctype html>
<html>

<head>
  <meta charset=utf-8>
  <title>Счета и Акты Агентские - Панель управления</title>
  <link href="/css/fonts.css" rel="stylesheet" />
  <link href="/css/style.css" rel="stylesheet" />
  <link rel="stylesheet" type="text/css" href="/js/daterangepicker.css">
  <script src="/_new-codebase/front/vendor/jquery/jquery-1.7.2.min.js" ></script>
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
  <script src="/_new-codebase/front/vendor/select2/4.0.4/select2.full.min.js"></script>
  <script src="/js/moment.min.js"></script>
  <script src="/js/jquery.daterangepicker.js"></script>
  <link rel="stylesheet" href="/_new-codebase/front/vendor/fancybox/3.5.2/jquery.fancybox.min.css" />
  <script src="/_new-codebase/front/vendor/fancybox/3.5.2/jquery.fancybox.min.js"></script>
  <link rel="stylesheet" href="/_new-codebase/front/vendor/select2/4.0.4/select2.min.css" />

  <script >
    // Таблица
    $(document).ready(function() {
      $('#table_content').dataTable({
        stateSave: false,
        "order": [
          [2, 'desc']
        ],
        "dom": '<"top"flp<"clear">>rt<"bottom"ip<"clear">>',
        "pageLength": <?= $config['page_limit']; ?>,
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
        }
      });

      $('ul.tabs li').click(function() {
        var tab_id = $(this).attr('data-tab');

        $('ul.tabs li').removeClass('current');
        $('.tab-content').removeClass('current');

        $(this).addClass('current');
        $("#" + tab_id).addClass('current');
      })

      $(document).on('selectmenuchange', 'select[name=status_bill]', function() {
        var value = $(this).val();
        var id = $(this).data('pay-id');
        var tesler = $(this).data('tesler');
        var extra = $(this).data('more');
        if (value) {

          $.get("/ajax.php?type=update_bill_status_combine&value=" + value + "&id=" + id + "&tesler=" + tesler + "&extra=" + extra, function(data) {

          });

        }


        return false;
      });

      $(document).on('selectmenuchange', 'select[name=status_pay]', function() {
        var value = $(this).val();
        var id = $(this).data('pay-id');
        var tesler = $(this).data('tesler');
        var extra = $(this).data('more');
        if (value) {

          $.get("/ajax.php?type=update_pay_status_combine&value=" + value + "&id=" + id + "&tesler=" + tesler + "&extra=" + extra, function(data) {

          });

        }


        return false;
      });

      $(document).on('selectmenuchange', 'select[name=status_act]', function() {
        var value = $(this).val();
        var id = $(this).data('pay-id');
        var tesler = $(this).data('tesler');
        var extra = $(this).data('more');
        if (value) {

          $.get("/ajax.php?type=update_act_status_combine&value=" + value + "&id=" + id + "&tesler=" + tesler + "&extra=" + extra, function(data) {

          });

        }


        return false;
      });

    });
  </script>
  <style>
    .ui-selectmenu-button:after {
      right: 10px;
    }

    .ui-selectmenu-button {
      font-size: 15px;
    }

    table.dataTable tbody tr {
      background: none;
    }

    .ui-selectmenu-button {
      width: 250px;
    }

    .min_width .ui-selectmenu-button {
      width: 145px;
    }

    ul.tabs {
      margin: 0px;
      padding: 0px;
      list-style: none;
    }

    ul.tabs li {
      background: none;
      color: #222;
      display: inline-block;
      padding: 10px 15px;
      cursor: pointer;
    }

    ul.tabs li.current {
      background: #ededed;
      color: #222;
    }

    .tab-content {
      display: none;
      background: #ededed;
      padding: 15px;
    }

    .tab-content.current {
      display: inherit;
    }

    .nohov table.dataTable.hover tbody tr:hover,
    table.dataTable.display tbody tr:hover {
      background: none;
      z-index: 0;
      box-shadow: none;
    }
  </style>
  <style>
    .select2-container {
      width: 450px !important;
    }
  </style>
  <script >
    $(document).ready(
      function() {
        $('#select_model').select2();



        /*$( ".dwn" ).each(function() {
                $( this ).attr('href', $( this ).data('href')+$('input[name="brands2[]"]:checked').map(function() { return this.value;  }).get().join(',')+'/');
        }); */

        $(document).on('change', 'input[name="brands2[]"]', function() {
          $(".dwn").each(function() {
            $(this).attr('href', $(this).data('href') + $('input[name="brands2[]"]:checked').map(function() {
              return this.value;
            }).get().join(',') + '/');
          });
        });

        $(document).on('change', 'input[name="payed"]', function() {

          if ($(this).is(':checked')) {

            $('select[name="status_pay"]').each(function(index) {
              if ($(this).val() == 1) {
                $(this).parent().parent().parent().parent().parent().parent().parent().show();
              } else {
                $(this).parent().parent().parent().parent().parent().parent().parent().hide();
              }
            });

          } else {
            $('.odd').show();
            $('.even').show();
          }


        });

        $(document).on('change', 'input.editable', function() {
          var cat_id = $(this).data('combine-id');
          var value = $(this).val();
          if (value) {

            $.get("/ajax.php?type=update_combine_number&id=" + cat_id + "&value=" + value, function(data) {


            });

          }


          return false;
        });

        $(".datepicker2").datepicker({
          dateFormat: 'yy.mm.dd',
          onSelect: function(dateText, inst) {
            var date = $(this).val();
            var id = $(this).data('combine-id');


            $.get("/ajax.php?type=update_combine_date&value=" + date + "&id=" + id, function(data) {

            });



          },
          beforeShow: function(input, inst) {
            $('#ui-datepicker-div').addClass("ll-skin-cangas");
          }
        });

      });
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
          <a href="/dashboard/"><img src="<?= $config['url']; ?>i/logo.png" alt="" /></a>
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

          <a href="/logout/">Выйти, <?= \models\User::getData('login'); ?></a>
        </div>

      </div>
    </div><!-- .site-header -->

    <div class="wrapper">

      <?= top_menu_admin(); ?>

      <div class="adm-tab">

        <?= menu_dash(); ?>

      </div><!-- .adm-tab -->
      <br>
      <h2 style="margin-bottom: 12px;">Счета и Акты Агентские</h2>
      <?= '<p>' . $content['dates_interval'] . '</p>'; ?>

      <div style="display: flex;margin: 20px 0;align-items: center;justify-content: space-between;">
        <div>Оплаченные <input type="checkbox" value="1" name="payed" <?= ($_COOKIE['payed'] == '1') ? 'checked' : ''; ?>>
        </div>
        <div>
        <form method="POST" style="display: flex;column-gap: 32px;">
        <div style="width: 100px">
          <label>Квартал:</label>
          <select name="qrt" class="nomenu" style="width: 100%">
            <option value="">Все</option>
          <?php 
          $curQrt = $_POST['qrt'] ?? '';
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
            <th align="left">ID</th>
            <th align="left">Бренд</th>
            <th align="left">Дата</th>
            <th align="left">Сумма</th>
            <th>Счета и отчет агента</th>
            <th align="center">Оплата</th>
            <th align="center">Получение оригиналов</th>
            <th>Скачать общий</th>
            <th>Операции</th>
          </tr>
        </thead>

        <tbody>
          <?= $content['body']; ?>
        </tbody>
      </table>


    </div>
  </div>
  </div>
</body>

</html>