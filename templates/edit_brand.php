<?php

use models\Users;

$count = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `brands` WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' ;'));
if ($count['COUNT(*)'] > 0) {
$content = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `brands` WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1;'));
} else {
header('Location: /brands/');
}
# Сохраняем:
if (!empty($_POST['action'])) {

  $brandID = $_GET['id'];
  switch ($_POST['action']) {

    case 'settings':

      $serviceSQL = '';
      if (!empty($_POST['service'])) {
        if ($_POST['service'] == 'y') {
          $s = 'Да';
          $sv = 1;
        } else {
          $s = 'Нет';
          $sv = 0;
        }
        $serviceSQL = '`service` = \'' . $sv . '\', ';
        mysqli_query($db, 'UPDATE `models` SET `service` = "' . $s . '"  WHERE `brand` = "' . mysqli_real_escape_string($db, $content['name']) . '";') or mysqli_error($db);
        mysqli_query($db, 'UPDATE `cats_to_brand` SET `service` = ' . $sv . ' WHERE `brand` = \'' . $content['id'] . '\';') or mysqli_error($db);
      }
      mysqli_query($db, 'UPDATE `brands` SET 
    `name` = \'' . mysqli_real_escape_string($db, trim($_POST['name'])) . '\',
    `warranty` = \'' . mysqli_real_escape_string($db, $_POST['warranty']) . '\',
    `parts` = \'' . mysqli_real_escape_string($db, $_POST['parts']) . '\',
    `user_depot_flag` = \'' . mysqli_real_escape_string($db, $_POST['user_depot_flag']) . '\',
    ' . $serviceSQL . '
    `agent_percent` = \'' . mysqli_real_escape_string($db, $_POST['agent_percent']) . '\',
    `can_repair` = \'' . mysqli_real_escape_string($db, $_POST['can_repair']) . '\' 
    WHERE `id` = \'' . mysqli_real_escape_string($db, $brandID) . '\' LIMIT 1
    ;') or mysqli_error($db);

      if ($_POST['warranty'] && $content['warranty'] != $_POST['warranty']) {
        mysqli_query($db, 'UPDATE `models` SET
    `warranty` = \'' . mysqli_real_escape_string($db, $_POST['warranty']) . '\'
    WHERE `brand` = \'' . mysqli_real_escape_string($db, $content['name']) . '\'
    ;') or mysqli_error($db);
      }

      if ($content['name'] != trim($_POST['name'])) {
        mysqli_query($db, 'UPDATE `models` SET
      `brand` = \'' . mysqli_real_escape_string($db, trim($_POST['name'])) . '\'
      WHERE `brand` = \'' . mysqli_real_escape_string($db, $content['name']) . '\'
      ;') or mysqli_error($db);
      }

      if ($content['custom'] == 1) {
        if ($_POST['warranty'] && $content['warranty'] != $_POST['warranty']) {
          mysqli_query($db, 'UPDATE `models` SET 
        `warranty` = \'' . mysqli_real_escape_string($db, $_POST['warranty']) . '\'
        WHERE `brand` = \'' . mysqli_real_escape_string($db, $content['name']) . '\' 
        ;') or mysqli_error($db);
        }
      }
      break;


    case 'docs':
      mysqli_query($db, 'UPDATE `brands` SET
`billing_info_from` = \'' . mysqli_real_escape_string($db, $_POST['billing_info_from']) . '\',
`billing_info_to` = \'' . mysqli_real_escape_string($db, $_POST['billing_info_to']) . '\',
`billing_info_agent` = \'' . mysqli_real_escape_string($db, $_POST['billing_info_agent']) . '\',
`billing_info_from_footer` = \'' . mysqli_real_escape_string($db, $_POST['billing_info_from_footer']) . '\',
`billing_info_to_footer` = \'' . mysqli_real_escape_string($db, $_POST['billing_info_to_footer']) . '\',
`billing_info_to_fio_footer` = \'' . mysqli_real_escape_string($db, $_POST['billing_info_to_fio_footer']) . '\',
`billing_info_from_fio_footer` = \'' . mysqli_real_escape_string($db, $_POST['billing_info_from_fio_footer']) . '\',
`act_info_order` = \'' . mysqli_real_escape_string($db, $_POST['act_info_order']) . '\',
`act_info_who` = \'' . mysqli_real_escape_string($db, $_POST['act_info_who']) . '\',
`act_info_fio` = \'' . mysqli_real_escape_string($db, $_POST['act_info_fio']) . '\',
`act_info_placer` = \'' . mysqli_real_escape_string($db, $_POST['act_info_placer']) . '\' 
WHERE `id` = ' . $brandID . '
;') or mysqli_error($db);

      if ($content['custom'] == 1) {
        mysqli_query($db, 'UPDATE `brands` SET
  `title` = \'' . mysqli_real_escape_string($db, $_POST['title']) . '\',
  `agent_title` = \'' . mysqli_real_escape_string($db, $_POST['agent_title']) . '\',
  `sc` = \'' . mysqli_real_escape_string($db, $_POST['sc']) . '\',
  `general` = \'' . mysqli_real_escape_string($db, $_POST['general']) . '\',
  `agent_date` = \'' . mysqli_real_escape_string($db, $_POST['agent_date']) . '\',
  `footer_title` = \'' . mysqli_real_escape_string($db, $_POST['footer_title']) . '\',
  `footer_general` = \'' . mysqli_real_escape_string($db, $_POST['footer_general']) . '\',
  `terms` = \'' . mysqli_real_escape_string($db, $_POST['terms']) . '\' 
  WHERE `id` = \'' . mysqli_real_escape_string($db, $_GET['id']) . '\' LIMIT 1
  ;') or mysqli_error($db);
      }
      break;


    case 'child-brands':
      if ($_POST['brands_child']) {
        mysqli_query($db, 'UPDATE `brands` SET 
`child_brand` = "' . mysqli_real_escape_string($db, implode(',', $_POST['brands_child'])) . '"  
WHERE `id` = ' . $brandID . '
;') or mysqli_error($db);
      }
      break;


    case 'service':
      if ($_POST['receiver'] && !empty($_POST['service_flag'])) {
        foreach ($_POST['receiver'] as $service_id) {
          $serviceFlagNum = ($_POST['service_flag'] == 'y') ? 1 : 0;
          $current = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) AS cnt FROM `brands_users` WHERE `service_id` = \'' . mysqli_real_escape_string($db, $service_id) . '\' and `brand_id` = ' . $content['id']));
          if (!$current['cnt']) {
            mysqli_query($db, 'INSERT INTO `brands_users` (
                      `service_id`,
                      `brand_id`,
                      `service`
                      ) VALUES (
                      \'' . mysqli_real_escape_string($db, $service_id) . '\',
                      \'' . mysqli_real_escape_string($db, $content['id']) . '\',
                      \'' . $serviceFlagNum . '\'
                      );') or mysqli_error($db);
          } else {
            mysqli_query($db, 'UPDATE `brands_users` SET `service` = \'' . $serviceFlagNum . '\'  WHERE `service_id` = \'' . mysqli_real_escape_string($db, $service_id) . '\' and `brand_id` = ' . $content['id'] . ' LIMIT 1;') or mysqli_error($db);
          }

          $yes_no = ($_POST['service_flag'] == 'y') ? 'Да' : 'Нет';
          $sql = mysqli_query($db, 'SELECT `cat`, `id`, `name` FROM `models` where `brand` = "' . $content['name'] . '"');
          while ($row = mysqli_fetch_array($sql)) {
            $current = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) AS cnt FROM `models_users` WHERE `service_id` = \'' . mysqli_real_escape_string($db, $service_id) . '\' and `model_id` = ' . $row['id']));
            if (!$current['cnt']) {
              mysqli_query($db, 'INSERT INTO `models_users` (
                  `service_id`,
                  `cat_id`,
                  `model_id`,
                  `name`,
                  `service`
                  ) VALUES (
                  \'' . mysqli_real_escape_string($db, $service_id) . '\',
                  \'' . mysqli_real_escape_string($db, $row['cat']) . '\',
                  \'' . mysqli_real_escape_string($db, $row['id']) . '\',
                  \'' . mysqli_real_escape_string($db, $row['name']) . '\',
                  \'' . mysqli_real_escape_string($db, $yes_no) . '\'
                  );') or mysqli_error($db);
            } else {
              mysqli_query($db, 'UPDATE `models_users` SET `service` = "' . $yes_no . '" WHERE `service_id` = \'' . mysqli_real_escape_string($db, $service_id) . '\' and `model_id` = ' . $row['id'] . ' LIMIT 1;');
            }
          }

          if ($_POST['service_rules_regonal'] == 1) { // Обновлять права моделей\категорий

            $sql = mysqli_query($db, 'SELECT * FROM `cats_to_brand` where `brand_id` = \'' . $content['id'] . '\';');

            while ($row = mysqli_fetch_array($sql)) {
              $current = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `cats_users` WHERE `service_id` = \'' . mysqli_real_escape_string($db, $service_id) . '\' and `cat_id` = ' . $row['cat_id'] . ' LIMIT 1;'));
              $cat = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `cats` WHERE `id` = ' . $row['cat_id'] . ' LIMIT 1;'));
              if ($current['COUNT(*)'] == 0) {
                mysqli_query($db, 'INSERT INTO `cats_users` (
                      `service_id`,
                      `cat_id`,
                      `name`,
                      `service`
                      ) VALUES (
                      \'' . mysqli_real_escape_string($db, $service_id) . '\',
                      \'' . mysqli_real_escape_string($db, $row['cat_id']) . '\',
                      \'' . mysqli_real_escape_string($db, $cat['name']) . '\',
                      \'' . mysqli_real_escape_string($db, $_POST['service_regonal']) . '\'
                      );') or mysqli_error($db);
              } else {
                mysqli_query($db, 'UPDATE `cats_users` SET `service` = \'' . $_POST['service_regonal'] . '\'  WHERE `service_id` = \'' . mysqli_real_escape_string($db, $service_id) . '\' and `cat_id` = ' . $row['cat_id'] . ' LIMIT 1;') or mysqli_error($db);
              }
            }
          }
        }
      }
      break;

    case 'child-cats':
      if ($_POST['cats']) {
        mysqli_query($db, 'DELETE FROM `cats_to_brand` WHERE `brand_id` = ' . $content['id'] . ' and cat_id not in (' . implode(',', $_POST['cats']) . ');') or mysqli_error($db);
        foreach ($_POST['cats'] as $cat_id) {
          mysqli_query($db, 'UPDATE `cats` SET `brand_id` = ' . $content['id'] . ', `is_deleted` = ' . $_POST['is_deleted'] . ' WHERE `id` = ' . $cat_id) or mysqli_error($db);
          $current = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `cats_to_brand` WHERE `brand_id` = ' . $content['id'] . ' and `cat_id` = ' . $cat_id . ' LIMIT 1;'));
          if ($current['COUNT(*)'] == 0) {
            mysqli_query($db, 'INSERT INTO `cats_to_brand` (
                        `brand_id`,
                        `cat_id`
                        ) VALUES (
                        \'' . mysqli_real_escape_string($db, $content['id']) . '\',
                        \'' . mysqli_real_escape_string($db, $cat_id) . '\'
                        );') or mysqli_error($db);
          }
        }
      }
      break;
  }

admin_log_add('Обновлен бренд #'.$brandID);
header('Location: /brands/');
}

function services_list2($brand_id = '') {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `'.Users::TABLE.'` where `status_id` = 1 and `role_id` NOT IN (4,5);');
      while ($row = mysqli_fetch_array($sql)) {
       $info = get_request_info_by_user_id($row['id']);
       if ($info['name']) {
       $servies_array[$row['id']] = trim($info['name']);
       }
      }

//print_r( $servies_array);
asort($servies_array);
//print_r( $servies_array);
foreach ($servies_array as $service_id => $service_name) {
$sel = (mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `brands_users` WHERE `service_id` = \''.mysqli_real_escape_string($db, $service_id).'\' and `brand_id` = '.$brand_id.' and `service` = 1 LIMIT 1;'))['COUNT(*)'] == 1) ? 'selected' : '';

$content .= '<option value="'.$service_id.'" '.$sel.'>'.$service_name.'</option>';

}



    return $content;
}

function cats_list2($brand_id) {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `cats` ;');
      while ($row = mysqli_fetch_array($sql)) {

       $selected = $row['brand_id'] == $brand_id ? 'selected' : '';
       $content .= '<option value="'.$row['id'].'" '.$selected.'>'.$row['name'].'</option>';
      }

    return $content;
}

function brand_child($current_array = '') {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `brands` ;');
      while ($row = mysqli_fetch_array($sql)) {

       if ($current_array) {
        $current_array_exp = explode(',', $current_array);
        if ($current_array_exp) {
            $selected = (in_array($row['id'], $current_array_exp)) ? 'selected' : '';
        }
       }

       $content .= '<option value="'.$row['id'].'" '.$selected.'>'.$row['name'].'</option>';
       unset($selected);
      }
    return $content;
}

?>
<!doctype html>
<html>
<head>
<meta charset=utf-8>
<title>Редактировать бренд - Панель управления</title>
<link href="/css/fonts.css" rel="stylesheet" />
<link rel="stylesheet" href="/_new-codebase/front/vendor/select2/4.0.4/select2.min.css" />
<link rel="stylesheet" href="/_new-codebase/front/vendor/font-awesome.css" />
<link href="/css/style.css" rel="stylesheet" />
<script src="/_new-codebase/front/vendor/jquery/jquery-1.7.2.min.js"  ></script>
<script src="/js/jquery-ui.min.js"></script>
<script src="/js/jquery.placeholder.min.js"></script>
<script src="/js/jquery.formstyler.min.js"></script>
<script src="/js/main.js"></script>

<script src="/notifier/js/index.js"></script>
<link rel="stylesheet" type="text/css" href="/notifier/css/style.css">
  <link rel="stylesheet" type="text/css" href="/notifier/css/style.css">
<link rel="stylesheet" href="/_new-codebase/front/vendor/animate.min.css" />
<link rel="stylesheet" href="/js/fSelect.css" />
<script src='/_new-codebase/front/vendor/mustache/mustache.min.js'></script>
<script src="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.concat.min.js"></script>
<link rel="stylesheet" href="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.min.css" />
<script src="/_new-codebase/front/vendor/select2/4.0.4/select2.full.min.js"></script>
<script src="/_new-codebase/front/vendor/select2/select2.multi-checkboxes.js"></script>

<script  src="/_new-codebase/front/vendor/datatables/1.10.12/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="/css/datatables.css">
<script src="/js/fSelect.js"></script>
<script >
// Таблица
$(document).ready(function() {
$('.select2-multiple2').fSelect({
    placeholder: 'Выберите',
    numDisplayed: 1,
    overflowText: '{n} выбрано',
    noResultsText: 'Не найдено',
    searchText: 'Поиск',
    showSearch: true
});

$( ".sel_all" ).on( "click", function() {
  $('.sc_all').find('option').prop("selected", true);
  $('.sc_all').fSelect('reload');
  $('input[name="send_to_all"]').val(1);
  return false;
});
$( ".desel_all" ).on( "click", function() {
  $('.sc_all').find('option').prop("selected", false);
  $('.sc_all').fSelect('reload');
  $('input[name="send_to_all"]').val(0);
  return false;
});


$( ".sel_all2" ).on( "click", function() {
  $('.sc_all2').find('option').prop("selected", true);
  $('.sc_all2').fSelect('reload');
  $('input[name="send_to_all"]').val(1);
  return false;
});
$( ".desel_all2" ).on( "click", function() {
  $('.sc_all2').find('option').prop("selected", false);
  $('.sc_all2').fSelect('reload');
  $('input[name="send_to_all"]').val(0);
  return false;
});

$( ".sel_all3" ).on( "click", function() {
  $('.sc_all3').find('option').prop("selected", true);
  $('.sc_all3').fSelect('reload');
  $('input[name="send_to_all"]').val(1);
  return false;
});
$( ".desel_all3" ).on( "click", function() {
  $('.sc_all3').find('option').prop("selected", false);
  $('.sc_all3').fSelect('reload');
  $('input[name="send_to_all"]').val(0);
  return false;
});

} );

</script>
<style>
    .tab-content{
      display: none;
      background: #ededed;
      padding: 15px;
    }

    .tab-content.current{
      display: inherit;
    }
    .adm-form input {
    width: 80%;
    width: 800px;
}
.adm-tab .level {
    display: table-cell;
    vertical-align: middle;
    width: 265px;
    font-size: 20px;
}
.adm-form .item {
    padding-top: 20px;
    width: 100%;
    display: inline-block;
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

  <div class="adm-tab" style="    min-height: 1450px;">

<?=menu_dash();?>

  </div><!-- .adm-tab -->
     <br>
     <div class="add">
      <a style="width: auto;padding-left: 7px;padding-right: 7px;" href="/brands-tarif/<?=$content['id'];?>/" class="button">Тарифы бренда</a>
      <a style="width: auto;padding-left: 7px;padding-right: 7px;" href="/repair-types-brand/<?=$content['id'];?>/" class="button">Вид ремонта</a>
     <a style="width: auto;padding-left: 7px;padding-right: 7px;" href="/problems-brand/<?=$content['id'];?>/" class="button">Проделанная работа</a>
     <a style="width: auto;padding-left: 7px;padding-right: 7px;" href="/models-brand/<?=$content['id'];?>/" class="button">Модели</a>
  </div>


           <br>
           <h2>Редактирование бренда</h2>

  <form id="send" method="POST">
   <div class="adm-form" style="padding-top:0;">


                        <div class="item" style="text-align:left;">
              <div class="level">Название:</div>
              <div class="value">
                <input type="text" name="name" value="<?=$content['name'];?>"  />
              </div>
            </div>

                        <div class="item" style="width:40%;text-align: left;float:left;">
              <div class="level">Срок гарантии:</div>
              <div class="value">
                <input type="text" name="warranty" style="width:50%;" value="<?=$content['warranty'];?>"  />
              </div>
            </div>


                 <div class="item" style="width:55%;">
              <div class="level">Обслуживание всех моделей и категорий в базе:</div>
              <div class="value">
                     <select name="service">
                        <option>Выберите вариант</option>
                        <option value="y" <?=($content['service'] == 1) ? 'selected' : '';?>>Да</option>
                        <option value="n" <?=($content['service'] == 0) ? 'selected' : '';?>>Нет</option>
                     </select>
              </div>
            </div>

                 <div class="item" style="width:55%;margin-left: 40%;">
              <div class="level">Возможность взять в ремонт:</div>
              <div class="value">
                     <select name="can_repair">
                        <option>Выберите вариант</option>
                        <option value="1" <?=($content['can_repair'] == 1) ? 'selected' : '';?>>Да</option>
                        <option value="0" <?=($content['can_repair'] == 0) ? 'selected' : '';?>>Нет</option>
                     </select>
              </div>
            </div>

                 <div class="item" style="width:55%;text-align: left;float:left;">
              <div class="level">Использовать склад запчастей HARPER:</div>
              <div class="value">
                     <select name="parts">
                        <option>Выберите вариант</option>
                        <option value="1" <?=($content['parts'] == 1) ? 'selected' : '';?>>Да</option>
                        <option value="0" <?=($content['parts'] == 0) ? 'selected' : '';?>>Нет</option>
                     </select>
              </div>
            </div>

            <div class="item" style="width:55%;">
              <div class="level">Приходовать запчасти на склад «Разбор»:</div>
              <div class="value">
                     <select name="user_depot_flag">
                        <option value="0" <?=($content['user_depot_flag'] == 0) ? 'selected' : '';?>>Нет</option>
                        <option value="1" <?=($content['user_depot_flag'] == 1) ? 'selected' : '';?>>Да</option>
                     </select>
              </div>
            </div>


                                    <div class="item" style="width:40%;text-align: left;float:left;">
              <div class="level">Агентский процент:</div>
              <div class="value">
                <input type="text" name="agent_percent" style="width:50%;" value="<?=$content['agent_percent'];?>"  /> %
              </div>

          
            </div>

            <div class="adm-finish">
            <div class="save">
              <input type="hidden" name="action" value="settings" />
              <button type="submit" >Сохранить</button>
            </div>
            </div>

            </div>
  </form>

  <form id="send" method="POST">
   <div class="adm-form" style="padding-top:0;">

            <br><br>
             <hr><bR>
             <h2>Дочерние бренды</h2>
             <div class="item">
              <div class="level" style="display: block;text-align: center;width: 100%;">Бренды:</div>
              <div class="value" style="display:block;">
              <select name="brands_child[]" class="nomenu select2-multiple2  sc_all3" multiple>

               <!--<option value="all">Всем</option>-->
               <?=brand_child($content['child_brand']);?>
              </select>
                            <div>
              <a href="#" class="sel_all3" style="">Выбрать всех</a> / <a href="#" class="desel_all3">Снять всех</a>
               </div>
              </div>
              <div class="adm-finish">
            <div class="save">
              <input type="hidden" name="action" value="child-brands" />
              <button type="submit" >Сохранить</button>
            </div>
            </div>
            </div>

            </div>
</form>
<form id="send" method="POST">
   <div class="adm-form" style="padding-top:0;">

             <br><br><hr><bR>
             <h2>Обслуживание в СЦ:</h2>


             <div class="item">
              <div class="value" style="display:block;">

                <input type="hidden" name="send_to_all" value="0">
              <select name="receiver[]" class="nomenu select2-multiple2 sc_all" multiple>
                <?=services_list2();?>
              </select>
              <div>
              <a href="#" class="sel_all">Выбрать всех</a> / <a href="#" class="desel_all">Снять всех</a>
               </div>
              </div>
            </div>

                 <div class="item" style="    display: block;    margin: 0 auto; ">
              <div class="level">Обслуживание:</div>
              <div class="value">
                     <select name="service_flag"> <option value="">Выберите вариант</option><option value="y">Да</option><option value="n">Нет</option></select>
              </div>
            </div>

             <div class="item" style="    display: block;    margin: 0 auto;">
              <div class="level">Обновлять права моделей\категорий:</div>
              <div class="value">
                     <select name="service_rules_regonal"> <option>Выберите вариант</option><option value="0" selected>Нет</option><option value="1">Да</option></select>
              </div>

              <div class="adm-finish">
            <div class="save">
              <input type="hidden" name="action" value="service" />
              <button type="submit" >Сохранить</button>
            </div>
            </div>
            </div>
            </div>
</form>
<form id="send" method="POST">
   <div class="adm-form" style="padding-top:0;">

             <br><br><hr><bR>
             <h2>Категории бренда:</h2>

             <div class="item">
              <div class="level" style="display: block;text-align: center;width: 100%;">Категории:</div>
              <div class="value" style="display:block;">
                <input type="hidden" name="send_to_all" value="0">
              <select name="cats[]" class="nomenu select2-multiple2 sc_all2" multiple>

               <!--<option value="all">Всем</option>-->
               <?=cats_list2($content['id']);?>
              </select>
              <div>
              <a href="#" class="sel_all2" style="">Выбрать всех</a> / <a href="#" class="desel_all2">Снять всех</a>
               </div>
              </div>

              <div class="adm-finish">
            <div class="save">
              <input type="hidden" name="is_deleted" value="<?= $content['is_deleted']; ?>" />
              <input type="hidden" name="action" value="child-cats" />
              <button type="submit" >Сохранить</button>
            </div>
            </div>

            </div>
            </div>
</form>
<form id="send" method="POST">
   <div class="adm-form" style="padding-top:0;">

              <br><br><hr><bR>
              <h2>Акты:</h2>

                        <div class="item">
              <div class="level">Договор сервисного обслуживания:</div>
              <div class="value">
                <input type="text" name="act_info_order" value="<?=$content['act_info_order'];?>"  />
              </div>
            </div>

                        <div class="item">
              <div class="level">Исполнитель:</div>
              <div class="value">
                <input type="text" name="act_info_who" value="<?=$content['act_info_who'];?>"  />
              </div>
            </div>

                        <div class="item">
              <div class="level">Поле фио возле подписи:</div>
              <div class="value">
                <input type="text" name="act_info_fio" value="<?=$content['act_info_fio'];?>"  />
              </div>
            </div>

                        <div class="item">
              <div class="level">Поле в футере заказчик:</div>
              <div class="value">
                <input type="text" name="act_info_placer" value="<?=$content['act_info_placer'];?>"  />
              </div>
            </div>

            <?php if ($content['custom']) { ?>

              <br><br><hr><bR>
              <h2>Документация:</h2>

                        <div class="item">
              <div class="level">Заголовок документа:</div>
              <div class="value">
                <input type="text" name="title" value="<?=$content['title'];?>"  />
              </div>
            </div>
			
						<div class="item">
              <div class="level">Заголовок документа агентский:</div>
              <div class="value">
                <input type="text" name="agent_title" value="<?=$content['agent_title'];?>"  />
              </div>
            </div>

                        <div class="item">
              <div class="level">Название СЦ в тексте:</div>
              <div class="value">
                <input type="text" name="sc" value="<?=$content['sc'];?>"  />
              </div>
            </div>

                        <div class="item">
              <div class="level">Генеральный директор в тексте:</div>
              <div class="value">
                <input type="text" name="general" value="<?=$content['general'];?>"  />
              </div>
            </div>

                        <div class="item">
              <div class="level">Дата и номер агентского соглашение:</div>
              <div class="value">
                <input type="text" name="agent_date" value="<?=$content['agent_date'];?>"  />
              </div>
            </div>

                         <div class="item">
              <div class="level">Название СЦ в футере (подпись):</div>
              <div class="value">
                <input type="text" name="footer_title" value="<?=$content['footer_title'];?>"  />
              </div>
            </div>

                        <div class="item">
              <div class="level">Генеральный директор в футере (подпись):</div>
              <div class="value">
                <input type="text" name="footer_general" value="<?=$content['footer_general'];?>"  />
              </div>
            </div>

                        <div class="item" style="width:100%">
              <div class="level">Текст:</div>
              <div class="value">
                               <div class="adm-w-text" style="border:0px;">
                  <textarea id="redactor_text" style="width: 800px;" name="terms" rows="5"><?=$content['terms'];?></textarea>
                </div>
              </div>
            </div>


            <?php } ?>

              <br><br><hr><bR>
              <h2>Данные для объединенных документов:</h2>

                        <div class="item">
              <div class="level">Поле исполнитель:</div>
              <div class="value">
                <input type="text" name="billing_info_from" value="<?=$content['billing_info_from'];?>"  />
              </div>
            </div>

                        <div class="item">
              <div class="level">Поле заказчик:</div>
              <div class="value">
                <input type="text" name="billing_info_to" value="<?=$content['billing_info_to'];?>"  />
              </div>
            </div>

                        <div class="item">
              <div class="level">Поле основание:</div>
              <div class="value">
                <input type="text" name="billing_info_agent" value="<?=$content['billing_info_agent'];?>"  />
              </div>
            </div>

                        <div class="item">
              <div class="level">Поле в футере исполнитель:</div>
              <div class="value">
                <input type="text" name="billing_info_from_footer" value="<?=$content['billing_info_from_footer'];?>"  />
              </div>
            </div>

                         <div class="item">
              <div class="level">Поле в футере заказчик:</div>
              <div class="value">
                <input type="text" name="billing_info_to_footer" value="<?=$content['billing_info_to_footer'];?>"  />
              </div>
            </div>

                        <div class="item">
              <div class="level">Поле фио возле подписи в футере заказчик:</div>
              <div class="value">
                <input type="text" name="billing_info_to_fio_footer" value="<?=$content['billing_info_to_fio_footer'];?>"  />
              </div>
            </div>

                        <div class="item">
              <div class="level">Поле фио возле подписи в футере исполнитель:</div>
              <div class="value">
                <input type="text" name="billing_info_from_fio_footer" value="<?=$content['billing_info_from_fio_footer'];?>"  />
              </div>
            </div>



                <div class="adm-finish">
            <div class="save">
              <input type="hidden" name="action" value="docs" />
              <button type="submit" >Сохранить</button>
            </div>
            </div>
        </div>

      </form>




        </div>
  </div>

</body>
</html>