<?php

use models\Users;

function content_list() {
  global $db;

$sql = mysqli_query($db, 'SELECT * FROM `combine`;');

while ($row = mysqli_fetch_array($sql)) {
      //$info = get_request_info($row['id']);
      //$city = get_city($info['city']);

$sql2 = mysqli_query($db, 'SELECT * FROM `combine_links` where `combine_id` = '.$row['id'].';');
      while ($row2 = mysqli_fetch_array($sql2)) {
        if (get_payment_info_combined($row2['pay_billing_id'])['type'] < 3) {
        $body[$row['id']]['brands'][$row['date']][0] = 'HARPER';
        } else {
        $body[$row['id']]['brands'][$row['date']][1] = 'TESLER';
        }

      }

      }

foreach ($body as $combined => $brands) {
    foreach ($brands['brands'] as $date => $brand) {
        foreach ($brand as $brand_name) {

        $brand_id = ($brand_name == 'TESLER') ? '-T' : '';
        $brand_name2 = strtolower($brand_name);
        $content_list['body'] .= '<tr>
            <td >'.$combined.$brand_id.'</td>
              <td >'.$date.'</td>
              <td style="text-align:center">
              <a class="t-1" style="    width: 16px;     height: 16px;      display: inline-block;    background: url(/img/s.jpg) 0 0 no-repeat;" title="Скачать счета" href="/get-all-bills/'.$combined.'/'.$brand_name2.'/" ></a>
              <a class="t-1" style="     width: 16px;    height: 16px;     display: inline-block;   background: url(/img/a.jpg) 0 0 no-repeat;" title="Скачать отчет агента" href="/get-all-agent/'.$combined.'/'.$brand_name2.'/" ></a>
              </td>
              <td style="text-align:center">
              <a class="dwn" href="/get-payment-act-admin-v2/'.$combined.'/'.$brand_name.'/">Скачать акт</a> /
              <a class="dwn" href="/get-payment-bill-admin-v2/'.$combined.'/'.$brand_name.'/">Скачать счет</a>
              </td>
              <td align="center" class="linkz" >
              <a class="t-5" title="Удалить обобщение" href="/del-combined/'.$combined.'/" onclick=\'return confirm("Удаление обобщение?")\'></a>
              </td>
              </tr>';

        }
    }
}

    return $content_list;
}

$content = content_list();

function get_payment_info_combined($pay_id) {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `pay_billing` where `id` = '.$pay_id);
$row = mysqli_fetch_array($sql);
return $row;
}

function services_list2() {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `'.Users::TABLE.'` where `status_id` = 1 and `role_id` NOT IN (4,5);');
      while ($row = mysqli_fetch_array($sql)) {
       $info = get_request_info_by_user_id($row['id']);
       if ($info['name'] != '') {
       if ($_GET['service_id'] == $row['id']) {
       $content .= '<option value="'.$row['id'].'" selected>'.$info['name'].'</option>';
       } else {
       $content .= '<option value="'.$row['id'].'" >'.$info['name'].'</option>';
       }

       }

      }
    return $content;
}

function brands($id = '') {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `brands` WHERE `is_deleted` = 0;');
      while ($row = mysqli_fetch_array($sql)) {
      $content .= '<li><label><input type="checkbox" name="brands[]" value="'.$row['name'].'" checked/>'.$row['name'].'</label></li>';
      }
    return $content;
}

function brands2($id = '') {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `brands` WHERE `is_deleted` = 0;');
      while ($row = mysqli_fetch_array($sql)) {
      if ($row['id'] != 4) {
      $content .= '<li><label><input type="checkbox" name="brands2[]" value="'.$row['name'].'" checked/>'.$row['name'].'</label></li>';
      } else {
      $content .= '<li><label><input type="checkbox" name="brands2[]" value="'.$row['name'].'" />'.$row['name'].'</label></li>';
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
<link rel="stylesheet" type="text/css" href="<?=$config['url'];?>js/daterangepicker.css">
<script src="/_new-codebase/front/vendor/jquery/jquery-1.7.2.min.js"  ></script>
<script src="<?=$config['url'];?>js/jquery-ui.min.js"></script>
<script src="<?=$config['url'];?>js/jquery.placeholder.min.js"></script>
<script src="<?=$config['url'];?>js/jquery.formstyler.min.js"></script>
<script src="<?=$config['url'];?>js/main.js"></script>

<script src="<?=$config['url'];?>notifier/js/index.js"></script>
<link rel="stylesheet" type="text/css" href="<?=$config['url'];?>notifier/css/style.css">
<link rel="stylesheet" href="/_new-codebase/front/vendor/animate.min.css" />
<script src='/_new-codebase/front/vendor/mustache/mustache.min.js'></script>
<script src="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.concat.min.js"></script>
<link rel="stylesheet" href="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.min.css" />

<script  src="/_new-codebase/front/vendor/datatables/1.10.12/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?=$config['url'];?>css/datatables.css">
 <script src="/_new-codebase/front/vendor/select2/4.0.4/select2.full.min.js"></script>
 <script src="<?=$config['url'];?>js/moment.min.js"></script>
<script src="<?=$config['url'];?>js/jquery.daterangepicker.js"></script>
 <link rel="stylesheet" href="/_new-codebase/front/vendor/select2/4.0.4/select2.min.css" />

<script >
// Таблица
$(document).ready(function() {
    $('#table_content').dataTable({
      stateSave:false,
      "dom": '<"top"flp<"clear">>rt<"bottom"ip<"clear">>', 
      "pageLength": <?=$config['page_limit'];?>,
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

  $('ul.tabs li').click(function(){
    var tab_id = $(this).attr('data-tab');

    $('ul.tabs li').removeClass('current');
    $('.tab-content').removeClass('current');

    $(this).addClass('current');
    $("#"+tab_id).addClass('current');
  })

} );

</script>
<style>
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
</style>
<style>
.select2-container {
    width: 450px !important;
}
</style>
  <script >
  $(document).ready(
    function()		{
    $('#select_model').select2();

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

    $( ".dwn" ).each(function() {
            $( this ).attr('href', $( this ).data('href')+$('input[name="brands2[]"]:checked').map(function() { return this.value;  }).get().join(',')+'/');
    });

    $(document).on('change', 'input[name="brands2[]"]', function() {
          $( ".dwn" ).each(function() {
            $( this ).attr('href', $( this ).data('href')+$('input[name="brands2[]"]:checked').map(function() { return this.value;  }).get().join(',')+'/');
          });
    });

    }  );
  </script>
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
           <h2>История обобщений счетов и актов</h2>



  <table id="table_content" class="display" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th align="left">ID</th>
                <th align="left">Дата</th>
                <th>Счета и отчет агента</th>
                <th>Скачать общий</th>
                <th>Операции</th>
            </tr>
        </thead>

        <tbody>
        <?=$content['body'];?>
        </tbody>
</table>


        </div>
  </div>
</div>
</body>
</html>