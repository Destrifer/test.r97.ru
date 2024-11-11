<?php
require '_new-codebase/front/templates/main/parts/common.php';
function check_cats() {
  global $db;

if (\models\User::hasRole('admin')) {

$sql = mysqli_query($db, 'SELECT * FROM `cats` where `service` = 1;');
      while ($row = mysqli_fetch_array($sql)) {
        $count_prices = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `prices` where `cat_id` = '.$row['id']));
        if ($count_prices['COUNT(*)'] == 0) {
            mysqli_query($db, 'INSERT INTO `prices` (
            `cat_id`
            ) VALUES (
            \''.mysqli_real_escape_string($db, $row['id']).'\'
            );') or mysqli_error($db);
        }

      }

$sql = mysqli_query($db, 'SELECT * FROM `prices`;');
      while ($row = mysqli_fetch_array($sql)) {
        $count_service = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `cats` where `service` = 1 and `id` = '.$row['cat_id']));
        if ($count_service['COUNT(*)'] == 0) {
            mysqli_query($db, 'DELETE FROM `prices` WHERE `id` = '.$row['id'].' LIMIT 1;') or mysqli_error($db);
        }

      }

}
}

check_cats();



function content_list() {
  global $db;

if (\models\User::hasRole('admin')) {
$sql = mysqli_query($db, 'SELECT * FROM `prices` ;');
if (mysqli_num_rows($sql) != false) {
      while ($row = mysqli_fetch_array($sql)) {
      $content_list .= '<tr>
      <td>'.$row['id'].'</td>
      <td>'.cat($row['cat_id'])['name'].'</td>
      <td><input class="editable" data-pass-protect-input readonly style="width:100%;" type="number" min="0" name="element" value="'.$row['element'].'" data-id="'.$row['id'].'" data-service-id="'.$_GET['id'].'"></td>
      <td><input class="editable" data-pass-protect-input readonly style="width:100%;" type="number" min="0" name="block" value="'.$row['block'].'" data-id="'.$row['id'].'" data-service-id="'.$_GET['id'].'"></td> 
          <td><input class="editable" data-pass-protect-input readonly style="width:100%;" type="number" min="0" name="acess" value="'.$row['acess'].'" data-id="'.$row['id'].'" data-service-id="'.$_GET['id'].'"></td>
          <td><input class="editable" data-pass-protect-input readonly style="width:100%;" type="number" min="0" name="anrp" value="'.$row['anrp'].'" data-id="'.$row['id'].'" data-service-id="'.$_GET['id'].'"></td>
          <td><input class="editable" data-pass-protect-input readonly style="width:100%;" type="number" min="0" name="ato" value="'.$row['ato'].'" data-id="'.$row['id'].'" data-service-id="'.$_GET['id'].'"></td>
      </tr>';
      }
      } 
    return $content_list;
}
}

function cat($id) {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `cats` where `id` = \''.$id.'\' LIMIT 1;');
      while ($row = mysqli_fetch_array($sql)) {
      $content = $row;
      }
    return $content;
}


$secNav = [
  ['name' => 'Изменить', 'url' => '#', 'action' => 'pass-protect-open'],
  ['name' => 'Сохранить', 'url' => '#', 'action' => 'save-changes', 'class' => 'disabled']
];
?>
<!doctype html>
<html>
<head>
<meta charset=utf-8>
<title>Тарифы обслуживания 2022 - Панель управления</title>
<link href="/css/fonts.css" rel="stylesheet" />
<link href="/css/style.css" rel="stylesheet" />
<script src="/_new-codebase/front/vendor/jquery/jquery-1.7.2.min.js"  ></script>
<script src="/js/jquery-ui.min.js"></script>
<script src="/js/jquery.placeholder.min.js"></script>
<script src="/js/jquery.formstyler.min.js"></script>
<script src="/js/main.js"></script>

<script src="/notifier/js/index.js"></script>
<link rel="stylesheet"  href="/notifier/css/style.css">
<link rel="stylesheet" href="/_new-codebase/front/vendor/animate.min.css" />
<script src='/_new-codebase/front/vendor/mustache/mustache.min.js'></script>
<script src="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.concat.min.js"></script>
<link rel="stylesheet" href="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.min.css" />

<script  src="/_new-codebase/front/vendor/datatables/1.10.12/jquery.dataTables.min.js"></script>
<link rel="stylesheet"  href="/css/datatables.css">

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



    let data = {};
    let blockFlag = false;

    $('body').on('click', '[data-action]', function(event) {
        event.preventDefault();
        switch (this.dataset.action) {
            case 'save-changes':
                save();
                break;
        }
    });


    function save() {
        if(blockFlag || !Object.keys(data).length){
            return;
        }
        blockFlag = true;
        $.ajax({
            type: 'POST',
            url: '/ajax.php?type=update_price_total',
            data: 'data=' + JSON.stringify(data),
            success: function(resp) {
                if (+resp['error_flag']) {
                    alert(resp['message']);
                    return;
                }
                alert('Тарифы сохранены.');
                data = {};
            },
            complete: function() {
                blockFlag = false;
            },
        });
    }


    $('body').on('change', 'input.editable', function() {
        collectData($(this).attr('name'), $(this).data('id'), $(this).val());
    });


    $(document).on('passprotect:unblock', function() {
        $('[data-action="save-changes"]').removeClass('disabled');
    });


    function collectData(field, id, value) {
        data[id+field] = { field, value, id};
    }

} );

</script>
<!-- New codebase -->
<link href="/_new-codebase/front/vendor/fancybox/jquery.fancybox.min.css" rel="stylesheet" />
<link href="/_new-codebase/front/templates/main/css/form.css" rel="stylesheet" />
<link href="/_new-codebase/front/templates/main/css/layout.css" rel="stylesheet" />
<link href="/_new-codebase/front/templates/main/css/sec-nav.css" rel="stylesheet" />
  <style>
        * {
            box-sizing: border-box;
        }

        [readonly] {
            background-color: #f1f1f1;
            cursor: default;
        }
  </style>
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
           <h2>Тарифы обслуживания 2022</h2>

  <div class="adm-catalog">

     <div class="add layout__mb_md">
      <a style="width: auto;padding-left: 7px;padding-right: 7px;" href="/prices-2/" class="button">Тариф 2018</a>
      <a style="width: auto;padding-left: 7px;padding-right: 7px;" href="/prices-2023/" class="button">Тариф 2023</a>
      <a style="width: auto;padding-left: 7px;padding-right: 7px;" href="/add-price/" class="button">Добавить категорию с расценками</a>
      <a style="width: auto;padding-left: 7px;padding-right: 7px;" href="/tariff-sync/" class="button">Синхронизировать тарифы</a>
      <a style="width: auto;padding-left: 7px;padding-right: 7px;" href="/mass-tariff-change/" class="button">Массово поменять тариф</a>
    </div>
    <nav class="layout__mb_md">
            <?php secNavHTML($secNav); ?>
    </nav>
     <br>
  <table id="table_content" class="display" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th align="left">№</th>
                <th align="left">Тип техники</th>
                <th align="left">Компонентный</th>
                <th align="left">Блочный</th>
                <th align="left">Замена аксессуаров</th>
                <th align="left">АНРП</th>
                <th align="left">АТО</th>
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

    <!-- New codebase -->
    <script src="/_new-codebase/front/vendor/fancybox/jquery.fancybox.min.js"></script>
    <script src="/_new-codebase/front/components/pass-protect/pass-protect.js"></script>
</body>
</html>