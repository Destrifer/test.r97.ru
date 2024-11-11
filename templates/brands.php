<?php

use models\brands\Brands;
use program\core\App;

if (!empty(App::$URLParams['ajax'])) {
  switch (App::$URLParams['ajax']) {
    case 'hide':
      Brands::disable(App::$URLParams['id']);
      break;
    case 'show':
      Brands::enable(App::$URLParams['id']);
      break;
  }
  exit;
}


function content_list() {
  global $db;
  $content_list = '';
if (\models\User::hasRole('admin')) {
$sql = mysqli_query($db, 'SELECT * FROM `brands` ORDER BY `is_deleted` ASC, `id` ASC;');
if (mysqli_num_rows($sql) != false) {
      while ($row = mysqli_fetch_array($sql)) {
        if(!$row['is_deleted']){
          $showFlag = '<a title="Скрыть" style="background: none;    padding-bottom: 6px;padding-right: 7px;" href="#" data-id="'.$row['id'].'" data-on-click="hide"><img src="/img/hide-on-site.png"></a>';
        }else{
          $showFlag = '<a title="Показать" style="background: none;    padding-bottom: 6px;padding-right: 7px;" href="#" data-id="'.$row['id'].'" data-on-click="show"><img src="/img/show-on-site.png"></a>';
        }
      $content_list .= '<tr>
      <td>'.$row['id'].'</td>
      <td>'.$row['name'].'</td>
      <td align="center" class="linkz">
      <a class="t-3" title="Редактировать карточку" style="margin-right: 7px" href="/edit-brands/'.$row['id'].'/" ></a>
      '.$showFlag.'
    </td></tr>';
      }
      } else {
      $content_list = '<tr><td colspan=""9>'.$row['id'].'</td>';
      }
    return $content_list;
}
}
?>
<!doctype html>
<html>
<head>
<meta charset=utf-8>
<title>Бренды - Панель управления</title>
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

<script >
// Таблица
$(document).ready(function() {
    $('#table_content').dataTable({
      "pageLength": 30,
      "dom": '<"top"flp<"clear">>rt<"bottom"ip<"clear">>',
      stateSave: true,
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
           <h2>Бренды</h2>

  <div class="adm-catalog">

     <div class="add">
      <a style="width: auto;padding-left: 7px;padding-right: 7px;" href="/add-brands/" class="button">Добавить бренд</a>
    </div>
     <br>
  <table id="table_content" class="display" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th align="left">№</th>
                <th align="left">Название</th>
                <th align="center">Операции</th>
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
  $('body').on('click', '[data-on-click]', function(){
    let $this = $(this);
    let url = ($this.data('on-click') == 'show') ? '?ajax=show' : '?ajax=hide';
    $.ajax({
            type: 'GET',
            url: url, 
            data: 'id=' + $this.data('id'),
            cache: false,
            dataType: 'json',
            success: function(){
              if($this.data('on-click') == 'hide'){
                $this.data('on-click', 'show');
                $this.attr('title', 'Показать');
                $this.html('<img src="/img/show-on-site.png">');
              }else{
                $this.data('on-click', 'hide');
                $this.attr('title', 'Скрыть');
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