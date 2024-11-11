<?php

use models\Users;
use program\core;

if (isset(core\App::$URLParams['action'])) {

  switch (core\App::$URLParams['action']) {
    case 'save-service-form':
      if(!empty($_POST['model_id']) && !empty($_POST['service_id'])){
        if($_POST['model_id'] == 'all'){
          models\Models::setServiceFlagBrand($_POST['service_id'], $_POST['brand_id']);
        }else{
          foreach($_POST['service_id'] as $serviceID){
            models\Models::setServiceFlag($_POST['model_id'], $serviceID, $_POST['service_flag']);
          }
        }
      }
      header('Location: /models-brand/'.$_POST['brand_id'].'/');
      exit;
      break;
    }

}


$brand = getBrand(core\App::$URL[1]);

if(!$brand){
    exit('Бренд #'.core\App::$URL[1].' не найден.');
}

function getBrand($id){
    global $db;
    $sql = mysqli_query($db, 'SELECT * FROM `brands` WHERE `id` = '.$id.';');
    return mysqli_fetch_assoc($sql);
}

function getServicesHTML(){
  global $db;
  $html = '<div class="service-col">';
  $sql = mysqli_query($db, 'SELECT r.`user_id`, r.`name` FROM `requests` r LEFT JOIN `'.Users::TABLE.'` u ON u.`id` = r.`user_id` WHERE r.`mod` = 1 AND u.`role_id` IN (3,4) AND u.`status_id` = 1 ORDER BY r.`name`;');
  $n = 0;
  while($row = mysqli_fetch_assoc($sql)){
    if($n == 12){
      $html .= '</div><div class="service-col">';
      $n = 0;
    }
    $html .= '<label class="service-row"><input data-check-flag="" type="checkbox" name="service_id[]" value="'.$row['user_id'].'"> '.$row['name'].'</label>';
    $n++;
  }
  $html .= '</div>';
  return '<div class="service">'.$html.'</div>';
}

function content_list() {
  global $db, $brand;
  $content_list = '';
if (\models\User::hasRole('admin')) {
$sql = mysqli_query($db, 'SELECT * FROM `models` WHERE `brand` = "'.$brand['name'].'";');
if (mysqli_num_rows($sql) != false) {
      while ($row = mysqli_fetch_array($sql)) {
        $content_list .= '<tr>
      <td >'.$row['id'].'</td>
      <td style="width:150px;">'.$row['model_id'].'</td>
      <td data-model-name>'.$row['name'].'</td>
      <td>'.cat_by_id($row['cat'])['name'].'</td>
      <td>'.$row['service'].'</td>
      <td align="center" class="linkz" >
      <a class="t-3" title="Обслуживаемость" data-src="#services" data-fancybox href="javascript:;" data-service-flag-trig="'.$row['id'].'" ></a>
      </td></tr>';
      }
      } 
    return $content_list;
}
}

function cat_by_id($id) {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `cats` where `id` = \''.$id.'\' LIMIT 1;');
      while ($row = mysqli_fetch_array($sql)) {
      $content = $row;
      }
    return $content;
}

?>
<!doctype html>
<html>
<head>
<meta charset=utf-8>
<title>Модели бренда <?= $brand['name'] ?> - Панель управления</title>
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

<script src="/_new-codebase/front/vendor/datatables/1.10.12/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="/css/datatables.css">

<link rel="stylesheet" href="/_new-codebase/front/vendor/fancybox/3.5.2/jquery.fancybox.min.css" />
<script src="/_new-codebase/front/vendor/fancybox/3.5.2/jquery.fancybox.min.js"></script>

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
        },
        initComplete: function () {
            this.api().columns().every( function () {
                var column = this;
                if (column.selector.cols ==5) {
                var select = $('<select><option value=""></option></select>')
                    .appendTo( $(column.footer()).empty() )
                    .on( 'change', function () {
                        var val = $.fn.dataTable.util.escapeRegex(
                            $(this).val()
                        );

                        column
                            .search( val ? '^'+val+'$' : '', true, false )
                            .draw();
                    } );

                column.data().unique().sort().each( function ( d, j ) {
                    select.append( '<option value="'+d+'">'+d+'</option>' )
                } );
            } });
        }});
} );
</script>

<style>

.service{
    display: flex;
    flex-wrap: wrap;
}

.service-row{
  display: block;
    margin: 16px 0;
}

.service-col{
  display: inline-block;
  width: 33%;
    margin: 16px 0;
    padding-right: 24px;
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
           <h2>Модели бренда <?= $brand['name'] ?></h2>

  <div class="adm-catalog">
  <div class="add">
      <a style="width: auto;padding-left: 7px;padding-right: 7px;" title="Выставить обслуживаемость для всех моделей бренда" data-src="#services" data-fancybox href="javascript:;" data-service-flag-trig="all"  class="button">Синхронизировать все модели</a>
    </div>
     <br>
  <table id="table_content" class="display" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th align="left">№</th>
                <th align="left" >Код</th>
                <th align="left" >Модель</th>
                <th align="left">Категория</th>
                <th align="left">Обслуживание</th>
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

  <div style="display: none;" id="services">
  <form action="?action=save-service-form" method="POST">
	<h3 style="font-size: 21px;font-weight: 300;">Выберите СЦ для установки параметра "Обслуживание"</h3>
  <div style="margin-top: 32px;" id="model-sync-block">
    <label for="service-flag">Модель <b><span id="model-name-text"></span></b> обслуживается</label>
      <select name="service_flag" id="service-flag" class="nomenu" style="height: 30px;">
        <option value="Да">Да</option>
        <option value="Нет">Нет</option>
      </select>
  </div>
  <div style="margin-top: 32px;font-weight: 600"><label><input type="checkbox" data-check-all-flags> Выделить все</label></div>
  <div>
    <?php
      echo getServicesHTML();
    ?>
  </div>
  <div style="margin-top: 32px; margin-bottom: 32px">
    <input type="hidden" name="brand_id" value="<?= $brand['id']; ?>">
    <input type="hidden" name="model_id" value="0" id="model-id">
    <button type="submit" style="padding: 0 72px;">Применить</button>
  </div>
  </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  $('[data-check-all-flags]').on('change', function(){
      $('[data-check-flag]').attr('checked', this.checked).trigger('refresh');
  });

    $('body').on('click', '[data-service-flag-trig]', function(){
      let $this = $(this);
      if($this.data('service-flag-trig') == 'all'){
        $('#model-sync-block').hide();
      }else{
        $('#model-sync-block').show();
        let modelName = $this.closest('tr').find('[data-model-name]').text();
        $('#model-name-text').text(modelName);
      }
      $('#model-id').val($this.data('service-flag-trig'));
  });
});
</script>

</body>
</html>