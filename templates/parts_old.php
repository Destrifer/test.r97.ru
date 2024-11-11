<?php

use models\Parts;
use models\parts\Depots;
use models\Serials;


if (isset($_POST['filter_cat_id'])) {
  setcookie("filter_cat_id", $_POST['filter_cat_id'], time() + 3600 * 24 * 30);
  $_COOKIE['filter_cat_id'] = $_POST['filter_cat_id'];
}
if (isset($_POST['filter_model_id'])) {
  setcookie("filter_model_id", $_POST['filter_model_id'], time() + 3600 * 24 * 30);
  $_COOKIE['filter_model_id'] = $_POST['filter_model_id'];
}
if (isset($_POST['filter_service_id'])) {
  setcookie("filter_service_id", $_POST['filter_service_id'], time() + 3600 * 24 * 30);
  $_COOKIE['filter_service_id'] = $_POST['filter_service_id'];
}
if (isset($_POST['filter_name'])) {
  setcookie("filter_name", $_POST['filter_name'], time() + 3600 * 24 * 30);
  $_COOKIE['filter_name'] = $_POST['filter_name'];
}
if (isset($_POST['filter_code'])) {
  setcookie("filter_code", $_POST['filter_code'], time() + 3600 * 24 * 30);
  $_COOKIE['filter_code'] = $_POST['filter_code'];
}



function content_list()
{
  global $db, $user;
  if ($user['id'] != 1 && $user['type_id'] != 4) {
    return '';
  }
  $filter = [];
  if ($_COOKIE['filter_cat_id']) {
    $filter[] = '`id` IN (SELECT `part_id` FROM `parts2_models` WHERE `model_cat_id` = ' . $_COOKIE['filter_cat_id'] . ')';
  }
  if ($_COOKIE['filter_model_id']) {
    $filter[] = '`id` IN (SELECT `part_id` FROM `parts2_models` WHERE `model_id` = ' . $_COOKIE['filter_model_id'] . ')';
  }
  if ($_COOKIE['filter_name']) {
    $filter[] = '`name` = "' . $_COOKIE['filter_name'] . '"';
  }
  if ($_COOKIE['filter_code']) {
    $filter[] = '`group_id` IN (SELECT `id` FROM `parts2_groups` WHERE `code` = "' . $_COOKIE['filter_code'] . '")';
  }

  $filterSQL = '';
  if ($filter) {
    $filterSQL = ' AND ' . implode(' AND ', $filter);
  }

  $content_list = '';
  $sql = mysqli_query($db, 'SELECT * FROM `parts2` WHERE `del_flag` = 0 ' . $filterSQL);
  if (mysqli_num_rows($sql) != false) {
    while ($row = mysqli_fetch_assoc($sql)) {
      $model = mysqli_fetch_assoc(mysqli_query($db, 'SELECT * FROM `parts2_models` where `part_id` = ' . $row['id'] . ' ORDER BY `id` LIMIT 1'));
      $group = mysqli_fetch_assoc(mysqli_query($db, 'SELECT `code`, `name` FROM `parts2_groups` where `id` = ' . $row['group_id']));
      $depotID = 1;
      $depotName = 'Главный';
      if(!empty($_COOKIE['filter_service_id'])){
        $depot = Depots::getDepot(['user_id' => $_COOKIE['filter_service_id']]);
        if(!$depot){
          $depot = Depots::addDepot(['user_id' => $_COOKIE['filter_service_id'], 'name' => 'Разбор']);
        }
        $depotID = $depot['id'];
        $depotName = $depot['name'];
      }
      $balance = mysqli_fetch_assoc(mysqli_query($db, 'SELECT `place`, `qty` FROM `parts2_balance` where `part_id` = ' . $row['id'] . ' AND `depot_id` = ' . $depotID));
      if(!$balance){
        continue;
      }
      $place = '';
      $qty = 0;
      if ($balance) {
        $place = $balance['place'];
        $qty = $balance['qty'];
      }
      $code = '';
      if ($group) {
        $code = $group['code'] . $row['id'];
      }
      $attr = (isset(Parts::$partAttrs[$row['attr_id']])) ? Parts::$partAttrs[$row['attr_id']] : '';
      if ($model) {
        $serial = Serials::getSerial($model['model_serial'], $model['model_id']);
        $serialData = $serial['provider'];
        if ($serial['order']) {
          $serialData .= ' (' . $serial['order'] . ')';
        }
        $modelName = get_model_by_id($model['model_id']);
        $cat = cat_by_id($model['model_cat_id'])['name'];
      } else {
        $serialData = '';
        $modelName = '';
        $cat = '';
      }

      $content_list .= '<tr>
                          <td align="center" class="linkz">' . getOperationsTD($row) . '</td>
                          <td>' . $code . '</td>
                          <td>' . $row['name'] . '</td>
                          <td>' . $qty . '</td>
                          <td>' . $depotName . '</td>
                          <td><input class="editable" style="width:200px;" type="text" name="place" value="' . $place . '" data-id="' . $row['id'] . '"></td>
                          <td>' . $attr . '</td>
                          <td>' . $cat . '</td>
                          <td >' . $modelName . '</td>
                          <td>' . $serialData . '</td>
                        </tr>';
    }
  }
  return $content_list;
}

function cat_by_id($id)
{
  global $db, $config, $user;
  $sql = mysqli_query($db, 'SELECT * FROM `cats` where `id` = \'' . $id . '\' LIMIT 1;');
  while ($row = mysqli_fetch_array($sql)) {
    $content = $row;
  }
  return $content;
}


function get_names($current = '')
{
  global $db;
  $content = '';
  $whereSQL = ($_COOKIE['filter_model_id']) ? 'WHERE `id` IN (SELECT `part_id` FROM `parts2_models` WHERE `model_id` = '.$_COOKIE['filter_model_id'].')' : '';
  
  $sql = mysqli_query($db, 'SELECT * FROM `parts2` '.$whereSQL.' order by `name`;');
  if (mysqli_num_rows($sql) != false) {
    while ($row = mysqli_fetch_array($sql)) {
      if ($current == $row['name']) {
        $content .= '<option selected value="' . $row['name'] . '">' . $row['name'] . '</option>';
      } else {
        $content .= '<option value="' . $row['name'] . '">' . $row['name'] . '</option>';
      }
    }
  }
  return $content;
}


function getOperationsTD(array $row)
{
  global $user;
  $gallery = $edit = $his = $del = $copy = $look = '';
  if ($user['type_id'] != 4) {
    $his = '<a class="t-1" title="История движения" href="/parts-history/' . $row['id'] . '/" ></a>';
    $edit = '<a class="t-3" title="Редактировать карточку" href="/part/?id=' . $row['id'] . '" ></a>';
  }
  if ($user['type_id'] == 4) {
    $look = '<a class="t-3" title="Просмотреть карточку" href="/part/?id=' . $row['id'] . '" ></a>';
  }

  $imgs = json_decode($row['photos']);
  if (count($imgs) > 0) {
    foreach ($imgs as $img) {
      $gallery .= '<a href="' . $img . '" data-fancybox="gallery' . $row['id'] . '" data-caption="' . $row['name'] . '">
     <img data-src="' . $img . '" alt="" />
   </a>';
    }

    $gallery_html = '<div style="display:none" class="fancyboxblock">' . $gallery . '</div>
   <a style="background: url(http://cdn.onlinewebfonts.com/svg/img_225522.png) no-repeat center center;  background-size: cover;     width: 19px;" class="t-1 showphoto show_img" title="Фото" href="#"><span class="tooltip_content"><img style="max-height:700px;max-width:700px;display:none;" src="https://crm.r97.ru/resizer.php?src=https://crm.r97.ru' . $imgs['0'] . '&h=500&w=500&zc=3&q=70" /></span></a>&nbsp;';
  }

  if ($user['type_id'] != 4) {
    $copy = '<a class="t-4" title="Копировать" href="/copy-parts/' . $row['id'] . '/"></a>';
    $del = '<a class="t-5" title="Удалить карточку" onclick=\'return confirm("Вы уверены, что хотите удалить #' . $row['id'] . '?")\'   href="/del-parts/' . $row['id'] . '/" style="margin-right: 13px;"></a>';
  }

  return $del . $his . $copy . $edit . $look . $gallery_html . '</td>';
}


function get_codes($current = '')
{
  global $db;
  $content = '';
  $sql = mysqli_query($db, 'SELECT DISTINCT `code` FROM `parts2_groups` where `code` != "" ORDER BY `code`;');
  if (mysqli_num_rows($sql) != false) {
    while ($row = mysqli_fetch_array($sql)) {
      if ($current == $row['code']) {
        $content .= '<option selected value="' . $row['code'] . '">' . $row['code'] . '</option>';
      } else {
        $content .= '<option value="' . $row['code'] . '">' . $row['code'] . '</option>';
      }
    }
  }
  return $content;
}

function get_cats($current = '')
{
  global $db;
  $sql = mysqli_query($db, 'SELECT * FROM `parts` where `parent_id` = \'\' and `cat` != \'\' group by cat;');
  if (mysqli_num_rows($sql) != false) {
    while ($row = mysqli_fetch_array($sql)) {
      $name = cat_by_id($row['cat'])['name'];
      $categ[$row['cat']] = $name;
      unset($name);
    }
  }
  asort($categ);
  foreach ($categ as $id => $cat) {
    if ($cat) {
      if ($current == $id) {
        $content .= '<option selected value="' . $id . '">' . $cat . '</option>';
      } else {
        $content .= '<option value="' . $id . '">' . $cat . '</option>';
      }
    }
  }

  return $content;
}

function get_services($cur = '')
{
  global $db;
  $sql = mysqli_query($db, 'SELECT `user_id`, `name` FROM `requests` WHERE `user_id` IN (SELECT `id` FROM `users` WHERE `type_id` IN (2, 3) AND `active` = 1 AND `block` = 0) ORDER BY `name`');
  $content = '';
    while ($row = mysqli_fetch_array($sql)) {;
      $content .= '<option '.(($row['user_id'] == $cur) ? 'selected' : '').' value="' . $row['user_id'] . '">' . $row['name'] . '</option>';
    }
  return $content;
}

function get_models($current = '')
{
  global $db;

  $content = '';
  $catWhere = ($_COOKIE['filter_cat_id']) ? 'AND `cat` = "' . $_COOKIE['filter_cat_id'] . '"' : '';
  $sql = mysqli_query($db, 'SELECT `id`, `name` FROM `models` WHERE `model_id` != "" '.$catWhere.' GROUP BY `model_id` ORDER BY `name`');
  while ($row = mysqli_fetch_array($sql)) {
    $content .= '<option '.(($current == $row['id']) ? 'selected' : '').' value="'.$row['id'].'">'.$row['name'].'</option>';
  }
  return $content;
}

function get_model_by_id($id)
{
  global $config, $db;
  $sql = mysqli_query($db, 'SELECT `name` FROM `models` WHERE `id` = \'' . mysqli_real_escape_string($db, $id) . '\'');
  return mysqli_fetch_array($sql)['name'];
}

function get_parents($id)
{
  global $config, $db;
  $sql = mysqli_query($db, 'SELECT `model_id`, `serial` FROM `parts` WHERE `parent_id` = \'' . mysqli_real_escape_string($db, $id) . '\' ');
  if (mysqli_num_rows($sql) > 0) {

    //$names .= '<table>';
    while ($row = mysqli_fetch_array($sql)) {
      $content['models_array'][$row['model_id']][] = $row['serial'];
    }

    //print_r($content['models_array']);

    foreach ($content['models_array'] as $model_id => $serial) {
      $names .= '<tr>';
      $names .= '<td>' . get_model_by_id($model_id) . "</td>";
      //$names .= '<td>'.get_serial_name($model_id, $serial).'</td>';
      $names .= '</tr>';
    }

    //$names .= '</table>';

  }
  return $names;
}

function get_provider_name($id)
{
  global $config, $db;
  $sql = mysqli_query($db, 'SELECT `name` FROM `providers` WHERE `id` = \'' . mysqli_real_escape_string($db, $id) . '\'');
  return mysqli_fetch_array($sql)['name'];
}

function get_serial_name($id, $currents = '')
{
  global $db;
  $sql = mysqli_query($db, 'SELECT * FROM `serials` where `model_id` = ' . $id);
  while ($row = mysqli_fetch_array($sql)) {
    $order = ($row['order']) ? '' . $row['order'] : '';
    if (in_array($row['serial'], $currents)) {
      $glue[] = get_provider_name($row['provider_id']) . ' (' . $order . ')';
    } else {
      // $content .= get_provider_name($row['serial_provider']).''.$order.', ';
    }
  }
  $content = @implode(', ', $glue);
  return $content;
}

?>
<!doctype html>
<html>

<head>
  <meta charset=utf-8>
  <title>Запчасти - Панель управления</title>
  <link href="/css/fonts.css" rel="stylesheet" />
  <link href="/css/style.css" rel="stylesheet" />
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
  <script src="/js/jquery-ui.min.js"></script>
  <script src="/js/jquery.placeholder.min.js"></script>
  <script src="/js/jquery.formstyler.min.js"></script>
  <script src="/js/main.js"></script>
  <script src="https://cdn.jsdelivr.net/jquery.tooltipster/4.2.5/js/tooltipster.bundle.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/jquery.tooltipster/4.2.5/css/tooltipster.bundle.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/jquery.tooltipster/4.2.5/css/plugins/tooltipster/sideTip/themes/tooltipster-sideTip-shadow.min.css" />

  <script src="/notifier/js/index.js"></script>
  <link rel="stylesheet" type="text/css" href="/notifier/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css" />
  <script src='https://cdnjs.cloudflare.com/ajax/libs/mustache.js/0.7.2/mustache.min.js'></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/malihu-custom-scrollbar-plugin/3.1.5/jquery.mCustomScrollbar.concat.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/malihu-custom-scrollbar-plugin/3.1.5/jquery.mCustomScrollbar.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.css" />
  <script src="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.js"></script>
  <script src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
  <link rel="stylesheet" type="text/css" href="/css/datatables.css">
  <script src="https://cdn.datatables.net/responsive/2.1.1/js/dataTables.responsive.min.js"></script>
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.1.1/css/responsive.dataTables.min.css">
  <script>
    // Таблица
    $(document).ready(function() {
      $('#table_content').dataTable({
        stateSave: false,
        "dom": '<"top"flp<"clear">>rt<"bottom"ip<"clear">>',
        "responsive": true,
        /* "columnDefs": [
              {
                  "targets": [ 2 ],
                  "visible": false
              }
          ], */
        "pageLength": <?= $config['page_limit']; ?>,
        "fnDrawCallback": function(oSettings) {

          $('.show_img').tooltipster({
            trigger: 'hover',
            position: 'top',
            interactive: true,
            animation: 'grow',
            theme: 'tooltipster-shadow',
            functionInit: function(instance, helper) {
              $(helper.origin).find('.tooltip_content img').show();
              //$(helper.origin).find('.tooltip_content img').attr('src', $(helper.origin).find('.tooltip_content img').data('src'));
              var content = $(helper.origin).find('.tooltip_content').detach();
              instance.content(content);
            },
            functionReady: function(instance, helper) {
              //instance.content().find('img').attr('src', instance.content().find('img').data('src'));

              //$(instance.origin).find('.tooltip_content img').attr('src', $(instance.origin).find('.tooltip_content img').data('src'));
            }
          });

        },
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

      $(document).on('click', '.showphoto', function() {

        $(this).parent().find('.fancyboxblock').find('a:first').click();
        return false;
      });



      $(document).on('change', 'input.editable', function() {
        var id = $(this).data('id');
        var value = $(this).val();

        $.get("/ajax.php?type=update_place&value=" + value + "&id=" + id, function(data) {

          //$('select[name=parts_parts]').html(data.html).selectmenu( "refresh" );
          //$('input[name="serial_parts_hidden"]').val(value);


        });


        return false;
      });

      $(document).on('change', 'select[name="filter_cat_id"]', function() {
        var value = $(this).val();

        $.get("/ajax.php?type=get_namesparts&cat_id=" + value, function(data) {
          $('select[name=filter_name]').html(data.html).selectmenu("refresh");
        });

        $.get("/ajax.php?type=get_modelsparts&cat_id=" + value, function(data) {
          $('select[name=filter_model_id]').html(data.html).selectmenu("refresh");
        });

        $.get("/ajax.php?type=get_codeparts&cat_id=" + value, function(data) {
          $('select[name=filter_code]').html(data.html).selectmenu("refresh");
        });

        return false;
      });

      $(document).on('change', 'select[name="filter_model_id"]', function() {
        var value = $(this).val();

        $.get("/ajax.php?type=get_namesparts_by_model&cat_id=" + value, function(data) {
          $('select[name=filter_name]').html(data.html).selectmenu("refresh");
        });

        $.get("/ajax.php?type=get_codeparts_by_model&cat_id=" + value, function(data) {
          $('select[name=filter_code]').html(data.html).selectmenu("refresh");
        });

        return false;
      });

      $(document).on('change', 'select[name="filter_code"]', function() {
        var value = $(this).val();
        var model_id = $(this).parent().parent().parent().parent().find('select[name="filter_model_id"]').val();

        $.get("/ajax.php?type=get_namesparts_by_codemodel&cat_id=" + value + "&model_id=" + model_id, function(data) {
          $('select[name=filter_name]').html(data.html).selectmenu("refresh");
        });


        return false;
      });

    });
  </script>
  <style>
    .filter_table {
      font-size: 16px;
    }

    .filter_table select {
      font-size: 16px;
    }

    .filter_table td {
      vertical-align: top;

    }
  </style>

  <!-- New codebase -->
  <link href="/_new-codebase/front/vendor/select2/css/select2.min.css" rel="stylesheet">
</head>

<body>

  <div class="viewport-wrapper">

    <div class="site-header">
      <div class="wrapper">

        <div class="logo">
          <a href="/dashboard/"><img src="/i/logo.png" alt="" /></a>
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

          <a href="/logout/">Выйти, <?= $user['login']; ?></a>
        </div>

      </div>
    </div><!-- .site-header -->

    <div class="wrapper">

      <?= top_menu_admin(); ?>

      <div class="adm-tab">

        <?= menu_dash(); ?>

      </div><!-- .adm-tab -->
      <br>
      <h2>Запчасти</h2>

      <div class="adm-catalog" style="margin-top:20px;">
        <form method="POST">
          <table style="width:100%;" class="filter_table">
            <tr>
              <td style="width:66%" colspan="2">Категория<br><select class="select2 nomenu" style="width: 100%;" name="filter_cat_id">
                  <option value="">Выберите категорию</option><?= get_cats($_COOKIE['filter_cat_id']); ?>
                </select></td>  
              <td style="width:33%">Код запчасти (буквы)<br><select class="select2 nomenu" style="width: 100%;" name="filter_code">
                  <option value="">Выберите код</option><?= get_codes($_COOKIE['filter_code']); ?>
                </select></td>
            </tr>
            <tr>
              <td style="padding-top: 20px;">Модель<br><select class="select2 nomenu" style="width: 100%;" name="filter_model_id">
                  <option value="">Выберите модель</option><?= get_models($_COOKIE['filter_model_id']); ?>
                </select></td>
                <td style="padding-top: 20px;">СЦ<br><select class="select2 nomenu" style="width: 100%;" name="filter_service_id">
                  <option value="">Выберите СЦ</option><?= get_services($_COOKIE['filter_service_id']); ?>
                </select></td>  
              <td style="padding-top: 20px;">Наименования запчасти<br><select style="width: 100%;" class="select2 nomenu" name="filter_name">
                  <option value="">Выберите название</option><?= get_names($_COOKIE['filter_name']); ?>
                </select></td>
            </tr>
            <tr>
              <td colspan="2" style="padding-top: 20px;"><input class="green_button" type="submit" style=" height: 40px; " value="Применить" /> <a style="   display: inline-block;     margin-top: 10px;    margin-left: 10px;" href="/clean-filter/">Очистить фильтр</a></td>
            </tr>
          </table>
        </form>
        <br>
        <hr>

        <?php if ($user['type_id'] != 4) { ?><div class="add">
            <a style="width: auto;padding-left: 7px;padding-right: 7px;" href="/part/" class="button">Добавить запчасть</a>
            <?php
            if ($user['type_id'] == 1) {
              echo ' <a style="width: auto;padding-left: 7px;padding-right: 7px;" href="/upload-parts/" class="button">Загрузка/выгрузка в Excel</a>';
              echo ' <a style="width: auto;padding-left: 7px;padding-right: 7px;" href="/depots/" class="button">Склады</a>';
              echo ' <a style="width: auto;padding-left: 7px;padding-right: 7px;" href="/parts-log/" class="button">История</a>';
            }
            ?>
          </div>
        <?php } ?>
        <br>
        <table id="table_content" class="display" cellspacing="0" width="100%">
          <thead>
            <tr>
              <th align="center" data-priority="1" style="width: 180px !important;">Операции</th>
              <th align="left" data-priority="4">Код запчасти</th>
              <th align="left" data-priority="1">Наименование запчасти</th>
              <th align="left" data-priority="2">Количество на складе</th>
              <th align="left" data-priority="3">Склад</th>
              <th align="left" data-priority="4">Место хранения</th>
              <th align="left" data-priority="3">Признак запчасти</th>
              <th align="left" data-priority="5">Категория</th>
              <th align="left" data-priority="1">Модель</th>
              <th align="left" data-priority="1">Завод, заказ</th>
            </tr>
          </thead>

          <tbody>
            <?= content_list(); ?>
          </tbody>
        </table>


      </div>


    </div>
  </div>
  </div>

  <!-- New codebase -->
  <script src="/_new-codebase/front/vendor/select2/js/select2.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {

      $('.select2').select2({
        language: 'ru'
      });

    });
  </script>
</body>

</html>