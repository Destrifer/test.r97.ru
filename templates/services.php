<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

use program\core;

if (!empty(core\App::$URLParams['ajax'])) {
  switch (core\App::$URLParams['ajax']) {
      case 'hide-on-site':
        mysqli_query($db, 'UPDATE `requests` SET `is_hidden_on_site` = 1 where `user_id` = "'.core\App::$URLParams['id'].'";');
          exit;
          case 'show-on-site':
            mysqli_query($db, 'UPDATE `requests` SET `is_hidden_on_site` = 0 where `user_id` = "'.core\App::$URLParams['id'].'";');
            exit;    
  }
}


function get_request_info($id) {
  global $db;
return mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `requests` WHERE `user_id` = '.$id));
}



function content_list() {
  global $db;
  $content_list = '';
$sql = mysqli_query($db, 'SELECT * FROM `users` where `role_id` = 3;');
      while ($row = mysqli_fetch_array($sql)) {
      $info = get_request_info($row['id']);
      if (!$info['id']) {
      $info['id'] = 0;
      }
      $city = get_city($info['city']);
      $billing = service_billing_info($row['id']);
      create_documents($row['id']);
             

      if ($billing['check'] == 1) {
      $icons .= '<img src="/i/plus.png" title="Платежные данные загружены.">';
      } else {
      $icons .= '<img src="/i/minus.png" title="Платежные данные не загружены.">';
      }
      if ($billing['aproved'] == 1) {
      $icons .= '<img src="/i/plus.png" title="Платежные данные подтверждены.">';
      } else {
      $icons .= '<img src="/i/minus.png" title="Платежные данные не подтверждены.">';
      }
       if (check_document($row['id'])) {
      $icons1 .= '<img src="/i/plus.png" title="Документы получены">';
      } else {
      $icons1 .= '<img src="/i/minus.png" title="Документы не получены">';
      }

      if ($_GET['get'] == 'other') {
      if ($info['mod'] == 0) {

      $block = ($row['status_id'] != 2) ? '<a title="Заблокировать" style="background: none;    padding-bottom: 6px;padding-right: 7px;" href="/block-service/'.$row['id'].'/"><img src="/img/skull.png"></a>' : '<a title="Разблокировать" style="background: none;    padding-bottom: 6px;padding-right: 7px;" href="/unblock-service/'.$row['id'].'/"><img src="/img/heartbeat.png"></a>';
      if(!$info['is_hidden_on_site']){
        $showFlag = '<a title="Скрыть на сайте" style="background: none;    padding-bottom: 6px;padding-right: 7px;" href="#" data-id="'.$row['id'].'" data-on-site-trigger="hide"><img src="/img/hide-on-site.png"></a>';
      }else{
        $showFlag = '<a title="Показать на сайте" style="background: none;    padding-bottom: 6px;padding-right: 7px;" href="#" data-id="'.$row['id'].'" data-on-site-trigger="show"><img src="/img/show-on-site.png"></a>';
      }
      $block_style = ($row['status_id'] == 1) ? '' : 'style="background: rgba(255, 71, 71, 0.13);"';
      $content_list .= '<tr '.$block_style.'><td '.$block_style.'>'.$row['id'].'</td><td style="width:100px">'.$row['login'].'</td><td>'.$info['phones'].'</td><td>'.$city['fcity_name'].'</td><td>'.$info['phisical_adress'].'</td><td>'.$info['name'].'</td><td style="text-align:center;">'.$icons1.'</td><td style="text-align:center;">'.$icons.'</td>
      <td align="center" class="linkz"><a class="t-3" href="/service/'.$row['id'].'/edit/" title="Редактировать" ></a> <a title="Удалить" class="t-5" style="float:right" href="/del-service/'.$info['id'].'/'.$row['id'].'/"></a> '.$block. ' '.$showFlag.' <a title="Написать сервису" style="background:none; padding-bottom: 8px;padding-right: 7px;" href="/notify/?service_id='.$row['id'].'"><img src="https://cdn3.iconfinder.com/data/icons/streamline-icon-set-free-pack/48/Streamline-58-20.png"></a><a title="Зайти как сервис" style="background:none;  padding-bottom: 8px;padding-right: 7px;" href="/login-like/'.$row['id'].'/"><img src="/img/if_login_account_enter_door_3005767.png"></a></td>
      </tr>';

      }
      } else if ($_GET['get'] == 'refused') {
      if ($info['mod'] == 2) {

        if(!$info['is_hidden_on_site']){
          $showFlag = '<a title="Скрыть на сайте" style="background: none;    padding-bottom: 6px;padding-right: 7px;" href="#" data-id="'.$row['id'].'" data-on-site-trigger="hide"><img src="/img/hide-on-site.png"></a>';
        }else{
          $showFlag = '<a title="Показать на сайте" style="background: none;    padding-bottom: 6px;padding-right: 7px;" href="#" data-id="'.$row['id'].'" data-on-site-trigger="show"><img src="/img/show-on-site.png"></a>';
        }

      $block = ($row['status_id'] != 2) ? '<a title="Заблокировать" style="background: none;    padding-bottom: 6px;padding-right: 7px;" href="/block-service/'.$row['id'].'/"><img src="/img/skull.png"></a>' : '<a title="Разблокировать" style="background: none;    padding-bottom: 6px;padding-right: 7px;" href="/unblock-service/'.$row['id'].'/"><img src="/img/heartbeat.png"></a>';
      $block_style = ($row['status_id'] == 1) ? '' : 'style="background: rgba(255, 71, 71, 0.13);"';
      $content_list .= '<tr '.$block_style.'><td '.$block_style.'>'.$row['id'].'</td><td style="width:100px">'.$row['login'].'</td><td>'.$info['phones'].'</td><td>'.$city['fcity_name'].'</td><td>'.$info['phisical_adress'].'</td><td>'.$info['name'].'</td><td style="text-align:center;">'.$icons1.'</td><td style="text-align:center;">'.$icons.'</td>
      <td align="center" class="linkz"><a class="t-3" href="/service/'.$row['id'].'/edit/" title="Редактировать" ></a> <a title="Удалить" class="t-5" style="float:right" href="/del-service/'.$info['id'].'/'.$row['id'].'/"></a> '.$block.' '.$showFlag.' <a title="Написать сервису" style="background:none;  padding-bottom: 8px;padding-right: 7px;" href="/notify/?service_id='.$row['id'].'"><img src="https://cdn3.iconfinder.com/data/icons/streamline-icon-set-free-pack/48/Streamline-58-20.png"></a><a title="Зайти как сервис" style="background:none;  padding-bottom: 8px;padding-right: 7px;" href="/login-like/'.$row['id'].'/"><img src="/img/if_login_account_enter_door_3005767.png"></a></td>
      </tr>';

      }
      } else  {
      if ($info['mod'] == 1) {

        if(!$info['is_hidden_on_site']){
          $showFlag = '<a title="Скрыть на сайте" style="background: none;    padding-bottom: 6px;padding-right: 7px;" href="#" data-id="'.$row['id'].'" data-on-site-trigger="hide"><img src="/img/hide-on-site.png"></a>';
        }else{
          $showFlag = '<a title="Показать на сайте" style="background: none;    padding-bottom: 6px;padding-right: 7px;" href="#" data-id="'.$row['id'].'" data-on-site-trigger="show"><img src="/img/show-on-site.png"></a>';
        }

      $block = ($row['status_id'] != 2) ? '<a title="Заблокировать" style="background: none;    padding-bottom: 6px;padding-right: 7px;" href="/block-service/'.$row['id'].'/"><img src="/img/skull.png"></a>' : '<a title="Разблокировать" style="background: none;    padding-bottom: 6px;padding-right: 7px;" href="/unblock-service/'.$row['id'].'/"><img src="/img/heartbeat.png"></a>';
      $block_style = ($row['status_id'] == 1) ? '' : 'style="background: rgba(255, 71, 71, 0.13);"';
      $content_list .= '<tr '.$block_style.'><td '.$block_style.'>'.$row['id'].'</td><td style="width:100px">'.$row['login'].'</td><td>'.$info['phones'].'</td><td>'.$city['fcity_name'].'</td><td>'.$info['phisical_adress'].'</td><td>'.$info['name'].'</td><td style="text-align:center;">'.$icons1.'</td><td style="text-align:center;">'.$icons.'</td>
      <td align="center" class="linkz"><a class="t-3" href="/service/'.$row['id'].'/edit/" title="Редактировать" ></a> <a title="Удалить" class="t-5" style="float:right" href="/del-service/'.$info['id'].'/'.$row['id'].'/"></a> '.$block.' '.$showFlag.' <a title="Написать сервису" style="background:none;  padding-bottom: 8px;padding-right: 7px;" href="/notify/?service_id='.$row['id'].'"><img src="https://cdn3.iconfinder.com/data/icons/streamline-icon-set-free-pack/48/Streamline-58-20.png"></a><a title="Зайти как сервис" style="background:none;  padding-bottom: 8px;padding-right: 7px;" href="/login-like/'.$row['id'].'/"><img src="/img/if_login_account_enter_door_3005767.png"></a></td>
      </tr>';

      }
      }
      unset($icons);
      unset($icons1);
      }

    return $content_list;
}
?>
<!doctype html>
<html>
<head>
<meta charset=utf-8>
<title>Управление СЦ</title>
<link href="/css/fonts.css" rel="stylesheet" />
<link href="/css/style.css" rel="stylesheet" />
<script src="/_new-codebase/front/vendor/jquery/jquery-1.7.2.min.js"  ></script>
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
<script  src="/_new-codebase/front/vendor/datatables/2.1.1/dataTables.responsive.min.js"></script>
<link rel="stylesheet" type="text/css" href="/_new-codebase/front/vendor/datatables/2.1.1/responsive.dataTables.min.css">
<script >
// Таблица
$(document).ready(function() {
    $('#table_content').dataTable({
      stateSave:false,
      "dom": '<"top"flp<"clear">>rt<"bottom"ip<"clear">>', 
      "responsive": true,
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
} );

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
								<h2>Управление СЦ</h2>
								<?php	echo \models\User::getUserRole();?>
           <br>
  <div class="adm-catalog">

     <div class="add" style="padding-top:0px;">
      <a style="width: auto;padding-left: 7px;padding-right: 7px;" href="/services/" class="button">Активные СЦ</a> 
      <a style="width: auto;padding-left: 7px;padding-right: 7px;" href="/services/other/" class="button">Неактивные СЦ</a> 
      <a style="width: auto;padding-left: 7px;padding-right: 7px;background:#EB0000;color:#fff;" href="/services/refused/" class="button">Отклоненные СЦ</a> 
      <a style="width: auto;padding-left: 7px;padding-right: 7px;" href="/services-settings/" class="button">Общие настройки</a> 
      <a style="width: auto;padding-left: 7px;padding-right: 7px;float: right;background:#006600;color:#fff;" href="/excel-services/<?php if ($_GET['get'] == 'refused') { echo 'refused/'; } else if ($_GET['get'] == 'other') { echo 'other/'; } ?>" class="button">Экспорт в xls</a>
    </div>  <br>

  <table id="table_content" class="display" cellspacing="0" width="100%" >
        <thead>
            <tr>
                <th align="left" data-priority="3" width="3%">ID</th>
                <th align="left" data-priority="3" width="10%">Логин</th>
                <th align="left" data-priority="3" width="23%">Телефон</th>
                <th align="left" data-priority="4" width="10%">Город</th>
                <th align="left" width="19%">Адрес</th>
                <th align="left" data-priority="3" width="10%">Название сервиса</th>
                <th  data-priority="3" style="text-align:center;" width="5%">Документы</th>
                <th  data-priority="3" style="text-align:center;" width="8%">Платежные данные</th>
                <th data-priority="3" width="11%">Операции</th>
            </tr>
        </thead>

        <tbody>
        <?=content_list();?>
        </tbody>
</table>


</div>


        </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  $('body').on('click', '[data-on-site-trigger]', function(){
    let $this = $(this);
    let url = ($this.data('on-site-trigger') == 'show') ? '?ajax=show-on-site' : '?ajax=hide-on-site';
    $.ajax({
            type: 'GET',
            url: url, 
            data: 'id=' + $this.data('id'),
            cache: false,
            dataType: 'json',
            success: function(){
              if($this.data('on-site-trigger') == 'hide'){
                $this.data('on-site-trigger', 'show');
                $this.attr('title', 'Показать на сайте');
                $this.html('<img src="/img/show-on-site.png">');
              }else{
                $this.data('on-site-trigger', 'hide');
                $this.attr('title', 'Скрыть на сайте');
                $this.html('<img src="/img/hide-on-site.png">');
              }
            },
            error: function(jqXHR) {
                console.log('Ошибка сервера');
                console.log(jqXHR.responseText);
            }
            });
    return false;
  });
});

</script>

</body>
</html>