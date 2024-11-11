<?php


require '_new-codebase/front/templates/main/parts/repair-card/repair-card.php';
require '_new-codebase/front/templates/main/parts/repair-card/acceptance.php';
require '_new-codebase/front/templates/main/parts/dashboard/ui.php';
require '_new-codebase/front/templates/main/parts/approve-form.php';

use program\core;
use models;
use models\User;
use models\dashboard\UI;
use models\Log;
use models\Repair;
use models\services\Settings;

define('VER', 1);

$clientTypes = models\repaircard\Acceptance::getClientTypes();
$warrantyCards = models\repaircard\Acceptance::getWarrantyCards();
$acceptStatuses = models\repaircard\Acceptance::getAcceptStatuses();
$shipStatuses = models\repaircard\Acceptance::getShipStatuses();
$contents = models\repaircard\Acceptance::getContents();
$exterior = models\repaircard\Acceptance::getExterior();
$models = [];
if (!models\User::hasRole('service')) {
  $models = models\repaircard\Acceptance::getModels();
}
if (!empty(core\App::$URLParams['from'])) {
  setcookie('dashboard:tab', core\App::$URLParams['from']);
}
if (models\User::hasRole('service')) {
  models\Counters::delete('approved', $_GET['id'], models\User::getData('id'));
}

# Сохраняем:
if (isset($_POST['save_mode'])) {

  $goToDashboardFlag = false;
  if (!empty($_POST['submit_type'])) {
    $goToDashboardFlag = true;
  }
  $repair = Repair::getRepairByID($_GET['id']);
  $statusAdmin = $repair['status_admin'];
  $oldStatus = $statusAdmin;
  $newStatus = '';
  if (!empty($_POST['dismant_flag']) && empty($repair['install_status']) && User::hasRole('service', 'taker')) {
    $newStatus = 'Запрос на демонтаж';
    mysqli_query($db, 'UPDATE `repairs` SET `status_admin` = "' . $newStatus . '" WHERE `id` = ' . $_GET['id']) or mysqli_error($db);
  } else if (!empty($_POST['onway']) ) {
    if(empty($repair['onway']) && User::hasRole('service', 'taker')){
      $newStatus = 'Запрос на выезд';
    }
    $sql_inj = ', `onway` = 1, `onway_type` = "' . mysqli_real_escape_string($db, $_POST['options']) . '"';
  } else if (empty($_POST['onway'])) {
    $sql_inj = ', `onway` = 0, `onway_type` = ""';
  }


  $_POST['anrp_use'] = (!empty($_POST['anrp_number'])) ? 1 : 0;


  if (!empty($_POST['no_serial'])) {
    $_POST['serial'] = '';
  }
  if (!isset($_POST['anrp_number'])) {
    $_POST['anrp_number'] = '';
  }
  $repair_final = (!empty($_POST['anrp_number'])) ? ', `repair_final` = 2' : '';

  if ($_POST['status_id'] == 5) {
    $status_by_hand = ', `status_by_hand` = 1';
  } else {
    $status_by_hand = ', `status_by_hand` = 0';
  }

  $statusAdmin = '';

  if (isset($_POST['submit_type']) && $_POST['submit_type'] == 'send-aside-control') {
    $newStatus = 'На проверке';
    $statusAdmin = '`status_admin` = "' . $newStatus . '",';
  }
  $cat = models\Cats::getCatByModelID($_POST['model_id']);
  $catID = ($cat) ? $cat['id'] : 0;
  $modelSQL = '`model_id` = "' . mysqli_real_escape_string($db, $_POST['model_id']) . '", `cat_id` = ' . $catID . ',';
  $visual = '';
  if (!empty($_POST['visual'])) {
    $visual = implode('|', $_POST['visual']);
  }
  $complex = '';
  if (!empty($_POST['complex'])) {
    $complex = implode('|', $_POST['complex']);
  }
  $_POST['no_serial'] = (empty($_POST['no_serial'])) ? '' : '1';
  $phoneShop = preg_replace('/[^0-9+]/', '', $_POST['phone_shop']);
  if (strlen($phoneShop) < 5) {
    $phoneShop = '';
  }
  $phone = preg_replace('/[^0-9+]/', '', $_POST['phone']);
  if (strlen($phone) < 5) {
    $phone = '';
  }
  $delField = '';
  if (models\User::hasRole('slave-admin', 'taker')) {
    $delField = '`deleted` = 0,';
  }
  $attentionSQL = (models\repair\Attention::has($_POST['model_id'], $_POST['serial'])) ? ',`attention_flag` = 1' : '';
  $shipStatusID = (models\Repair::isRepeated($_GET['id'], $_POST['model_id'], $_POST['serial'])) ? 3 : $_POST['status_ship_id'];
  $serialInvalidFlag = (empty($_POST['no_serial']) && !\models\Serials::isValid($_POST['serial'], $_POST['model_id'])) ? 1 : 0;
  mysqli_query($db, 'UPDATE `repairs` SET
`rsc` = "' . mysqli_real_escape_string($db, $_POST['rsc']) . '",
`refuse_doc_flag` = "' . ((isset($_POST['refuse_doc_flag'])) ? $_POST['refuse_doc_flag'] : '') . '",
`client` = \'' . mysqli_real_escape_string($db, $_POST['client']) . '\',
`client_type` = \'' . mysqli_real_escape_string($db, $_POST['client_type']) . '\',
`address` = \'' . mysqli_real_escape_string($db, $_POST['address']) . '\',
`phone` = "' . $phone . '",
`name_shop` = "' . mysqli_real_escape_string($db, str_replace(['"', "'"], '', $_POST['name_shop'])) . '",
`anrp_use` = \'' . mysqli_real_escape_string($db, $_POST['anrp_use']) . '\',
`anrp_number` = \'' . mysqli_real_escape_string($db, $_POST['anrp_number']) . '\',
`address_shop` = \'' . mysqli_real_escape_string($db, $_POST['address_shop']) . '\',
`phone_shop` = "' . $phoneShop . '",
' . $modelSQL . '
`status_ship_id` = \'' . $shipStatusID . '\',
`talon` = \'' . mysqli_real_escape_string($db, $_POST['talon']) . '\',
`serial` = \'' . mysqli_real_escape_string($db, $_POST['serial']) . '\',
`no_serial` = \'' . $_POST['no_serial'] . '\',
`status_id` = \'' . mysqli_real_escape_string($db, $_POST['status_id']) . '\',
`sell_date` = "' . core\Time::format($_POST['sell_date'], 'Y-m-d') . '",
`receive_date` = "' . core\Time::format($_POST['receive_date'], 'Y-m-d') . '",
`complex` = \'' . $complex . '\',
`serial_invalid_flag` = ' . $serialInvalidFlag . ',
`visual` = \'' . $visual . '\',
`visual_comment` = \'' . mysqli_real_escape_string($db, $_POST['visual_comment']) . '\',
`bugs` = \'' . mysqli_real_escape_string($db, $_POST['bugs']) . '\',
`install_status` = ' . ((!empty($_POST['dismant_flag'])) ? 1 : 0) . ',
' . $statusAdmin . '
`comment` = \'' . mysqli_real_escape_string($db, $_POST['comment']) . '\',
' . $delField . ' 
`out_date` = "' . core\Time::format($_POST['out_date'], 'Y-m-d') . '" 
' . $attentionSQL . ' 
' . $repair_final . '
' . $sql_inj . '
' . $status_by_hand . ' 
WHERE `id` = \'' . mysqli_real_escape_string($db, $_GET['id']) . '\' LIMIT 1
;') or mysqli_error($db);
  Repair::clearCache();
  Log::repair(2, 'Приемка.', $_GET['id']);

  $settings = Settings::getSettingsByServiceID($repair['service_id']);

  if (in_array($newStatus, ['Запрос на выезд', 'Запрос на демонтаж']) && !empty($settings['auto_approve_out_dismant'])) {
    if ($newStatus == 'Запрос на выезд') {
      $newStatus = 'Выезд подтвержден';
    } else {
      $newStatus = 'Демонтаж подтвержден';
    }
  }
  if ($newStatus && ($newStatus != $oldStatus)) {
    Repair::changeStatus($_GET['id'], $newStatus);
    Log::repair(1, '"' . $oldStatus . '" на "' . $newStatus . '", при сохранении вкладки "Приемка".', $_GET['id']);
  }

  $backURL = '/dashboard/';
  if (!empty($_COOKIE['dashboard:tab'])) {
    $backURL = '/dashboard/?tab=' . $_COOKIE['dashboard:tab'];
  }
  switch ($_POST['save_mode']) {
    case 'save-and-close':
      header('Location: ' . $backURL);
      exit;
      break;
    case 'save':
      header('Location: /edit-repair/' . $_GET['id'] . '/');
      exit;
      break;
    case 'save-and-next':
      header('Location: /edit-repair/' . $_GET['id'] . '/step/4/');
      exit;
      break;
  }
  if ($goToDashboardFlag) {
    header('Location: ' . $backURL);
    exit;
  }
  unset($_POST['save_mode']);
}



$error = '';
if (strpos($_SERVER['REQUEST_URI'], 'error') !== false) {
  $error = 'Пожалуйста, выберите статус ремонта.';
}

if (models\User::hasRole('service')) {
  models\Counters::delete('approved', $_GET['id'], models\User::getData('id'));
}
disable_notice('/edit-repair/' . $_GET['id'] . '/step/6/', User::getData('id'));

if (\models\User::hasRole('admin', 'slave-admin', 'taker', 'master')) {
  $count = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` WHERE `id` = \'' . mysqli_real_escape_string($db, $_GET['id']) . '\';'));
} else {
  $count = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` WHERE `id` = \'' . mysqli_real_escape_string($db, $_GET['id']) . '\' and `service_id` = ' . User::getData('id') . ';'));
}


if ($count['COUNT(*)'] > 0) {
  if (\models\User::hasRole('admin', 'slave-admin', 'taker', 'master')) {
    $content = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `repairs` WHERE `id` = \'' . mysqli_real_escape_string($db, $_GET['id']) . '\' LIMIT 1;'));
    $repairStatus = $content['status_admin'];
    $content['status_admin'] = 'Есть вопросы';
    $content['repair_done'] = 0;


    mysqli_query($db, 'UPDATE `configuration` SET `value` = \'' . mysqli_real_escape_string($db, $_GET['id']) . '\' WHERE `id` = 13 LIMIT 1;') or mysqli_error($db);
  } else {
    $content = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `repairs` WHERE `id` = \'' . mysqli_real_escape_string($db, $_GET['id']) . '\' and `service_id` = ' . User::getData('id') . ' LIMIT 1;'));
    $repairStatus = $content['status_admin'];
  }
  $content['complexs'] = explode('|', $content['complex']);
  $content['contents_extra'] = implode(', ', array_diff($content['complexs'], $contents));
  $content['visuals'] = explode('|', $content['visual']);
  $content['model'] = model($content['model_id']);
  $content['serial_incorrect_flag'] = (($content['imported'] == 1 && !models\Serials::isValid($content['serial'], $content['model_id'])) || ($content['imported'] == 1 && $content['serial'] == ''));
  $content = stripslashes_array($content);
  $content['client_info'] = ($content['client_id'] != 0) ? mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `clients` WHERE `id` = \'' . mysqli_real_escape_string($db, $content['client_id']) . '\' LIMIT 1;')) : '';
  $serial = models\Serials::getSerial($content['serial'], $content['model_id']);
  $content['provider'] = $serial['provider'];
  $content['provider_order'] = $serial['order'];
  $content['warranty'] = get_warranty($content['model_id']);
  $content['phone_shop'] = preg_replace('/[^0-9+]/', '', $content['phone_shop']);
  $content['phone'] = preg_replace('/[^0-9+]/', '', $content['phone']);

  $cat = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `cats` WHERE `id` = \'' . mysqli_real_escape_string($db, $content['model']['cat']) . '\' LIMIT 1;'));
} else {
  header('Location: ' . $config['url'] . 'dashboard/');
}


$service = models\Services::getServiceByID($content['service_id']);

$minReceiveDate = '2000-01-01';
if (!User::hasRole('admin', 'slave-admin')) {
  $maxInterval = ($service['country'] == 1) ? '9' : '5';
  $minReceiveDate = date('Y-m-d', strtotime($thisDate . ' - ' . $maxInterval . ' days'));
}




function model($id)
{
  global $db;
  $sql = mysqli_query($db, 'SELECT * FROM `models` where `id` = ' . $id);
  if (!$sql) {
    return ['cat' => 0, 'name' => '', 'id' => ''];
  }
  while ($row = mysqli_fetch_array($sql)) {
    $content = $row;
    // print_r($row);
  }
  return $content;
}


function models($cat_id)
{
  global $db;
  $content = '';
  $sql = mysqli_query($db, 'SELECT * FROM `models` order by `name` ASC;');
  while ($row = mysqli_fetch_array($sql)) {
    if ($cat_id == $row['id']) {
      $content .= '<option selected value="' . $row['id'] . '">' . $row['name'] . '</option>';
    } else {
      $content .= '<option value="' . $row['id'] . '">' . str_replace('\'', '', $row['name']) . '</option>';
    }
  }
  return $content;
}

function check_complete($id)
{
  global $db;
  $req = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` WHERE
`client` != \'\' and
`phone` != \'\' and
`name_shop` != \'\' and
`model_id` != \'\' and
`status_id` != \'\' and
`bugs` != \'\' and
`finish_date` != "0000-00-00" and
`begin_date` != "0000-00-00" and
`master_id` != \'\' and
`disease` != \'\' and
`id` = ' . $id));
  if ($req['COUNT(*)'] == 0) {
    return false;
  } else {
    return true;
  }
}

function get_warranty($model_id)
{
  global $db;
  $sql = mysqli_query($db, 'SELECT `warranty` FROM `models` WHERE `id` = ' . $model_id);
  if (!$sql) {
    return 365;
  }
  $req = mysqli_fetch_array($sql);
  return $req['warranty'];
}

?>
<!doctype html>
<html>

<head>
  <meta charset=utf-8>
  <title>Приемка - Карточка ремонта</title>
  <link href="/css/fonts.css" rel="stylesheet" />
  <link href="/css/style-without-forms.css?v=1.00" rel="stylesheet" />
  <link rel="stylesheet" href="/_new-codebase/front/vendor/tooltipster/tooltipster.bundle.min.css" />
  <link rel="stylesheet" href="/_new-codebase/front/vendor/tooltipster/tooltipster-sideTip-shadow.min.css" />
  <link rel="stylesheet" type="text/css" href="/notifier/css/style.css">
  <link rel="stylesheet" href="/_new-codebase/front/vendor/animate.min.css" />
  <link rel="stylesheet" href="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.min.css" />
  <link rel="stylesheet" href="/_new-codebase/front/vendor/fancybox/jquery.fancybox.min.css" />
  <style>
    .payed {
      background-color: rgb(178, 255, 102) !important;
      color: #000 !important;
    }

    .cond-guarant {
      background-color: rgba(184, 183, 184, 0.85) !important;
      color: #000 !important;
    }

    .repeat-repair {
      background-color: rgba(245, 87, 81, 0.45) !important;
      color: #000 !important;
    }
  </style>
  <!-- New codebase -->
  <style>
    * {
      box-sizing: border-box;
    }

    .iti {
      width: 100%;
    }
  </style>
  <link href="/_new-codebase/front/modules/dashboard/css/ui.css" rel="stylesheet" />
  <link href="/_new-codebase/front/vendor/air-datepicker/css/datepicker.min.css" rel="stylesheet">
  <link href="/_new-codebase/front/vendor/select2/css/select2.min.css" rel="stylesheet">
  <link href="/_new-codebase/front/templates/main/css/grid.css" rel="stylesheet">
  <link href="/_new-codebase/front/templates/main/css/form.css" rel="stylesheet">
  <link href="/_new-codebase/front/templates/main/css/repair-card/save-parts-window.css" rel="stylesheet">
  <link href="/_new-codebase/front/templates/main/css/repair-card/repair-card.css" rel="stylesheet">
  <link href="/js/radios-to-slider.min.css" rel="stylesheet" />
  <link href="/_new-codebase/front/vendor/intl-tel-input/css/intlTelInput.css" rel="stylesheet">
  <script src='/_new-codebase/front/vendor/jquery/jquery.min.js'></script>
  <link href="/_new-codebase/front/templates/main/css/notice.css?v=<?= VER; ?>" rel="stylesheet" />
  <link href="/_new-codebase/front/templates/main/css/repair-card/approve-form.css?v=<?= VER; ?>" rel="stylesheet">
  <!-- Aside controls -->
  <link href="/_new-codebase/front/components/aside-controls/css/aside-controls.css?v=<?= VER; ?>" rel="stylesheet">
</head>

<body>
  <?php
  if ($content['status_admin'] == 'Есть вопросы' && models\User::hasRole('service')) {
    echo '<div class="top-message top-message_alert" style="text-align:center">Пожалуйста, внесите исправления в карточку и отправьте на проверку.</div>';
  }
  ?>

  <div class="viewport-wrapper">

    <div class="site-header">
      <div class="wrapper" style="max-width: 1280px">

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

    <div class="wrapper" style="max-width: 1280px">

      <?= top_menu_admin(); ?>

      <div class="adm-tab">

        <?php
        echo getSummaryHTML(models\RepairCard::getSummary($content['id']));
        ?>

        <?= menu_dash(); ?>

      </div><!-- .adm-tab -->
      <br>
      <!-- Меню вкладок -->
      <section class="layout__mb_md">
        <?= getTabsHTML(UI::getTabs(User::getData('role'))); ?>
      </section>
      <h2>Процессинг</h2>

      <?php
      $stepsNavHTML = getStepsNavHTML(\models\RepairCard::getStepsNav($content['id'], 'acceptance'));
      echo $stepsNavHTML;
      ?>

      <form id="repair-form" method="POST" class="repair_form repair-card">
        <div class="container gutters">
          <div class="row">

            <div class="col-12 col-sm-6">
              <div class="form__cell">
                <label class="form__label" for="client-type">От кого поступил товар:</label>
                <select name="client_type" id="client-type" class="form__select">
                  <?= getOptionsHTML($clientTypes, $content['client_type']); ?>
                </select>
              </div>
            </div>

            <div class="col-12 col-sm-6">
              <div class="form__cell">
                <label class="form__label" for="rsc">Внутренний номер в АСЦ:</label>
                <input type="text" name="rsc" id="rsc" value="<?= $content['rsc']; ?>" class="form__text">
              </div>
            </div>

            <div class="col-12 col-sm-6">
              <div class="form__cell">
                <label class="form__label" for="receive-date">Дата приема:</label>
                <input type="text" name="receive_date" id="receive-date" data-mindate="<?= $minReceiveDate; ?>" placeholder="дд.мм.гггг" data-input-filter="date" data-input-filter-min-date="<?= $minReceiveDate; ?>" data-datepicker-receive value="<?= core\Time::format($content['receive_date']); ?>" class="form__text">
              </div>
            </div>

            <div class="col-12 col-sm-6">
              <div class="form__cell">
                <label class="form__label" for="warranty-card">Документы для гарантии:</label>
                <select name="talon" id="warranty-card" class="form__select">
                  <?= getOptionsHTML($warrantyCards, $content['talon']); ?>
                </select>
              </div>
            </div>

          </div>

          <div class="row" id="legal-entity-section">
            <div class="col-12">
              <h3 class="form__title">Информация о продавце техники</h3>
            </div>

            <div class="col-12 col-sm-6">
              <div class="form__cell">
                <label class="form__label" for="shop-name">Наименование торговой организации:</label>
                <input type="text" name="name_shop" id="shop-name" value="<?= $content['name_shop']; ?>" class="form__text">
              </div>
            </div>


            <div class="col-12 col-sm-6">
              <div class="form__cell">
                <label class="form__label" for="shop-phone">Телефон:</label>
                <input type="text" name="phone_shop" id="shop-phone" data-input-filter="phone" data-intl-tel-input value="<?= $content['phone_shop']; ?>" class="form__text">
                <ul class="form__cell-panel">
                  <li class="form__cell-panel-item">
                    <div class="form__notice form__notice_alert" id="shop-phone-error" style="display:none"></div>
                  </li>
                </ul>
              </div>
            </div>

            <div class="col-12">
              <div class="form__cell">
                <label class="form__label" for="shop-address">Адрес:</label>
                <input type="text" name="address_shop" id="shop-address" value="<?= $content['address_shop']; ?>" class="form__text">
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-12">
              <h3 class="form__title">Информация по гарантии</h3>
            </div>

            <div class="col-12 col-sm-6">
              <div class="form__cell">
                <label class="form__label" for="recept-status">Статус приёма:</label>
                <?php
                $color = '';
                if ($content['status_id'] == 6) {
                  $color = 'payed';
                }
                if ($content['status_id'] == 5) {
                  $color = 'cond-guarant';
                }
                ?>
                <select name="status_id" id="recept-status" class="form__select <?= $color; ?>">
                  <?= getOptionsReceptHTML($acceptStatuses, $content['status_id']); ?>
                </select>
              </div>
            </div>

            <div class="col-12 col-sm-6">
              <div class="form__cell">
                <label class="form__label" for="sale-date">Дата продажи:</label>
                <input type="text" name="sell_date" id="sale-date" data-input-filter="date" data-input-filter-max-date="today" placeholder="дд.мм.гггг" data-datepicker value="<?= core\Time::format($content['sell_date']); ?>" class="form__text">
                <ul class="form__cell-panel">
                  <li class="form__cell-panel-item">
                    <div class="form__notice form__notice_alert" id="sale-date-error" style="display:none">Срок гарантии истек</div>
                  </li>
                </ul>
              </div>
            </div>

          </div>

          <div class="row" id="private-person-section">
            <div class="col-12">
              <h3 class="form__title">Информация о клиенте (частнике)</h3>
            </div>

            <div class="col-12 col-sm-6">
              <div class="form__cell">
                <label class="form__label" for="client-name">ФИО клиента:</label>
                <input type="text" name="client" id="client-name" value="<?= $content['client']; ?>" class="form__text">
              </div>
            </div>

            <div class="col-12 col-sm-6">
              <div class="form__cell">
                <label class="form__label" for="client-phone">Телефон:</label>
                <input type="text" name="phone" id="client-phone" data-input-filter="phone" data-intl-tel-input value="<?= $content['phone']; ?>" class="form__text">
                <ul class="form__cell-panel">
                  <li class="form__cell-panel-item">
                    <div class="form__notice form__notice_alert" id="client-phone-error" style="display:none"></div>
                  </li>
                </ul>
              </div>
            </div>

            <div class="col-12">
              <div class="form__cell">
                <label class="form__label" for="client-address">Адрес:</label>
                <input type="text" name="address" id="client-address" value="<?= $content['address']; ?>" class="form__text">
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-12">
              <h3 class="form__title">Информация о модели</h3>
            </div>

            <?php
            $modelNotFound = '';
            if ($content['imported'] == 1 && !$content['model_id']) {
              $modelNotFound = '<div class="form__notice form__notice_alert">Модель не определена: ' . $content['imported_model'] . '</div>';
            }
            ?>

            <div class="col-12 col-sm-6">
              <div class="form__cell">
                <label class="form__label" for="model-id">Модель:</label>
                <?php
                if ($models) {
                  echo '<select name="model_id" id="model-id" class="form__select select2">
                        <option value="0">- Выберите модель -</option>
                          ' . getOptionsHTML($models, $content['model_id']) . '
                       </select>';
                } else {
                  echo '
                  <input type="hidden" name="model_id" value="' . $content['model_id'] . '">
                  <input type="text" readonly value="' . $content['model']['name'] . '" class="form__text">';
                }
                ?>
                <ul class="form__cell-panel">
                  <li class="form__cell-panel-item">
                    <?= $modelNotFound; ?>
                  </li>
                </ul>
              </div>
            </div>

            <div class="col-12 col-sm-6">
              <div class="form__cell">
                <label class="form__label" for="serial">Серийный номер:</label>
                <input type="text" name="serial" id="serial" value="<?= $content['serial']; ?>" data-input-filter="serial" class="form__text">
                <ul class="form__cell-panel">
                  <li class="form__cell-panel-item" style="position: absolute; top: 10px; left: -16px;"><label for="no-serial-flag"><input type="checkbox" id="no-serial-flag" name="no_serial" value="1" <?= ($content['no_serial'] == 1) ? 'checked' : ''; ?>> Номера нет</label></li>
                  <li class="form__cell-panel-item">
                    <div class="form__notice form__notice_alert" id="serial-error" style="display:none">Некорректный номер</div>
                  </li>
                </ul>
              </div>
            </div>


            <?php
            $cols = (!models\User::hasRole('service')) ? ' col-sm-3' : ' col-sm-6';
            ?>

            <?php if (!models\User::hasRole('service')) : ?>
              <div class="col-12 col-sm-3">
                <div class="form__cell">
                  <label class="form__label" for="anrp-number">АНРП №:</label>
                  <input type="text" name="anrp_number" id="anrp-number" data-input-filter="int" value="<?= $content['anrp_number']; ?>" class="form__text">
                  <ul class="form__cell-panel">
                    <li class="form__cell-panel-item">
                      <div class="form__notice" id="anrp-info" style="display:none"></div>
                    </li>
                  </ul>
                </div>
              </div>
            <?php endif; ?>

            <div class="col-12 <?= $cols; ?>">
              <div class="form__cell">
                <?php
                $color = '';
                if (models\Repair::isRepeated($content['id'])) {
                  $color = 'repeat-repair';
                }
                ?>
                <label class="form__label" for="status-ship-id">Статус поступления:</label>
                <select name="status_ship_id" id="status-ship-id" class="form__select <?= $color; ?>" readonly>
                  <?= getOptionsHTML($shipStatuses, $content['status_ship_id']); ?>
                </select>
              </div>
            </div>


            <?php
            if (!models\User::hasRole('service')) :
            ?>
              <div class="col-12 col-sm-3">
                <div class="form__cell">
                  <label class="form__label" for="">Завод:</label>
                  <input type="text" readonly value="<?= $content['provider']; ?>" class="form__text">
                </div>
              </div>

              <div class="col-12 col-sm-3">
                <div class="form__cell">
                  <label class="form__label" for="">Заказ:</label>
                  <input type="text" readonly value="<?= $content['provider_order']; ?>" class="form__text">
                </div>
              </div>
            <?php
            endif;
            ?>

            <div class="col-12">
              <div class="form__cell" id="serial-info"></div>
            </div>

            <?php if ($cat['install_flag']) : ?>
              <div class="col-6">
                <div class="form__cell">
                  <div class="form__flags-section" id="install-flag">
                    <label class="form__flag"><input type="checkbox" name="dismant_flag" value="1" <?= ($content['install_status']) ? 'checked' : ''; ?> class="form__checkbox"> Нужен демонтаж</label>
                  </div>
                </div>
              </div>

              <?php if (User::hasRole('admin') && $content['install_status'] == 1) : ?>
                <div class="col-12">
                  <div class="form__cell">
                    <?php approveFormHTML('Подтверждение демонтажа', $content['install_status'] > 0, $content['id'], 'set-dismant-approved-status'); ?>
                  </div>
                </div>
              <?php endif; ?>

            <?php endif; ?>

            <div class="col-12">
              <h3 class="form__title">Комплектация</h3>
            </div>

            <div class="col-12">
              <div class="form__cell">
                <div class="form__flags-section" id="complex">
                  <?= getCheckboxesContentsHTML('complex[]', $contents, $content['complexs']); ?>
                </div>
              </div>
            </div>

            <div class="col-12">
              <div class="form__cell">
                <label class="form__label" for="contents-extra">Дополнение к комплектации:</label>
                <input type="text" name="complex[]" id="contents-extra" value="<?= $content['contents_extra']; ?>" class="form__text">
              </div>
            </div>


            <div class="col-12">
              <h3 class="form__title">Внешний вид</h3>
            </div>

            <div class="col-12">
              <div class="form__cell">
                <div class="form__flags-section" id="visual">
                  <?= getCheckboxesExteriorHTML('visual[]', $exterior, $content['visuals']); ?>
                </div>
              </div>
            </div>

            <div class="col-12">
              <div class="form__cell">
                <label class="form__label" for="visual-comment">Дополнение к внешнему виду:</label>
                <input type="text" name="visual_comment" id="visual-comment" value="<?= $content['visual_comment']; ?>" class="form__text">
              </div>
            </div>

            <div class="col-12">
              <div class="form__cell">
                <label class="form__label" for="defect-client">Неисправность со слов клиента:</label>
                <input type="text" name="bugs" id="defect-client" value="<?= $content['bugs']; ?>" class="form__text">
              </div>
            </div>

            <div class="col-12">
              <div class="form__cell">
                <label class="form__label" for="defect-fact">Фактическая неисправность при приёме (своими словами):</label>
                <input type="text" name="comment" id="defect-fact" value="<?= $content['comment']; ?>" class="form__text">
              </div>
            </div>

            <div class="col-12 col-sm-6">
              <div class="form__cell">
                <label class="form__label" for="out-date">Дата выдачи:</label>
                <input type="text" name="out_date" id="out-date" data-input-filter="date" <?= (($content['status_admin'] == 'Подтвержден') ? '' : 'readonly'); ?> placeholder="дд.мм.гггг" data-datepicker value="<?= core\Time::format($content['out_date']); ?>" class="form__text">
              </div>
            </div>

            <div class="col-12 col-sm-6">
              <div class="form__cell">
                <label class="form__label" for="out-date">Заявление об отказе в ремонте:</label>
                <div class="form__flags">
                  <label class="form__flag-item"><input type="radio" required name="refuse_doc_flag" value="y" <?= ($content['refuse_doc_flag'] == 'y') ? 'checked' : ''; ?>> Есть</label>
                  <label class="form__flag-item"><input type="radio" required name="refuse_doc_flag" value="n" <?= ($content['refuse_doc_flag'] == 'n') ? 'checked' : ''; ?>> Нет</label>
                </div>
              </div>
            </div>

          </div>

          <?php if ($cat['travel']) : ?>
            <div class="row">

              <div class="col-12">
                <h3 class="form__title">Выездной ремонт</h3>
              </div>

              <div class="col-12">
                <div class="form__cell">
                  <label for="onway-flag"><input type="checkbox" id="onway-flag" name="onway" value="1" <?= ($content['onway'] == 1 || $content['onway'] == 2) ? 'checked' : ''; ?>> Ремонт является выездным</label>
                </div>
              </div>
            </div>

            <div class="row" id="onway-section">

              <div class="col-12">
                <div class="form__cell">
                  <div id="zones" style="position: relative; width: 100%;">
                    <input id="option1" class="nomenu" name="options" type="radio" value="shop" <?= ($content['onway_type'] == 'shop') ? 'checked' : ''; ?>>
                    <label for="option1">Магазин <br>в городе</label>

                    <input id="option2" class="nomenu" name="options" type="radio" value="buyer" <?= ($content['onway_type'] == 'buyer') ? 'checked' : ''; ?>>
                    <label for="option2">Потребитель <br>в городе</label>

                    <input id="option3" class="nomenu" name="options" value="zone1" type="radio" <?= ($content['onway_type'] == 'zone1') ? 'checked' : ''; ?>>
                    <label for="option3">50 км<br> от СЦ</label>

                    <input id="option4" class="nomenu" name="options" value="zone2" type="radio" <?= ($content['onway_type'] == 'zone2') ? 'checked' : ''; ?>>
                    <label for="option4">50-100 км<br> от СЦ</label>

                    <input id="option5" class="nomenu" name="options" value="zone3" type="radio" <?= ($content['onway_type'] == 'zone3') ? 'checked' : ''; ?>>
                    <label for="option5">100-150 км<br> от СЦ</label>

                    <input id="option6" class="nomenu" name="options" value="zone4" type="radio" <?= ($content['onway_type'] == 'zone4') ? 'checked' : ''; ?>>
                    <label for="option6">больше 150 км<br> от СЦ</label>
                  </div>
                </div>
              </div>

              <?php if (models\User::hasRole('admin')) : ?>

                <div class="col-12">
                  <div class="form__cell">
                    <?php approveFormHTML('Подтверждение выезда', $content['onway'], $content['id'], 'set-outside-approved-status'); ?>
                  </div>
                </div>

              <?php
              endif;
              if (!empty($content['onway_comment'])) :
              ?>

                <div class="col-12">
                  <div class="form__cell">
                    <textarea class="form__text" <?= ((models\User::hasRole('service')) ? 'readonly' : ''); ?>><?= $content['onway_comment']; ?></textarea>
                  </div>
                </div>

              <?php endif; ?>

            </div>
          <?php endif; ?>

          <div class="row">
            <?php if (models\User::hasRole('admin', 'slave-admin', 'taker') || in_array($repairStatus, ['Выезд отклонен', 'Выезд подтвержден', 'Есть вопросы', 'Отклонен', 'Оплачен', 'Принят', 'В работе', 'Одобрен акт'])) {
              if (models\User::hasRole('admin', 'slave-admin', 'taker') || (models\User::getData('id') == $content['service_id']) || (models\User::hasRole('master') && $content['service_id'] == 33)) {
            ?>
                <div class="col-12">
                  <div class="form__cell repair-card__controls repair-card__controls_submit">
                    <input type="hidden" name="submit_type" id="submit-type" value="" />
                    <input type="hidden" name="warranty" id="warranty" value="<?= $content['warranty']; ?>">
                    <input type="hidden" name="save_mode" id="save-mode" value="">
                    <button class="form__btn" data-submit-trig="save-and-close">Сохранить и закрыть</button>
                    <button class="form__btn" data-submit-trig="save">Сохранить</button>
                    <button class="form__btn" data-submit-trig="save-and-next">Сохранить и перейти в «Фото и видео»</button>
                  </div>
                  <div class="form__cell">
                    <div class="form__notif form__notif_error" id="form-notif" style="display:none">Пожалуйста, исправьте ошибки в форме.</div>
                  </div>
                </div>

              <?php
              }
            } else if (in_array($repairStatus, ['Запрос на монтаж', 'Запрос на демонтаж', 'Запрос на выезд'])) { ?>
              <div class="col-12">
                <div class="form__cell repair-card__controls repair-card__controls_submit" style="font-weight: 600; justify-content:center">
                  <div class="notice notice__alert">
                    Ремонт находится в статусе "<?= $repairStatus; ?>", редактирование невозможно, <br> пока администратор не обработает запрос.
                  </div>
                </div>
              <?php
            }
              ?>

              </div>
          </div>

      </form>


      <?php
      echo $stepsNavHTML;
      ?>
    </div>
  </div>
  </div>
  <script src="/_new-codebase/front/vendor/jquery-validation/jquery.validate.min.js"></script>
  <script src="/_new-codebase/front/vendor/jquery-validation/additional-methods.min.js"></script>
  <script src="/_new-codebase/front/vendor/tooltipster/tooltipster.bundle.min.js"></script>
  <script src='/_new-codebase/front/vendor/mustache/mustache.min.js'></script>
  <script src="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.concat.min.js"></script>
  <script src="/notifier/js/index.js"></script>

  <script>
    let $repairForm,
      $formNotif;

    $(document).ready(function() {

      $repairForm = $('#repair-form');
      $formNotif = $('#form-notif');

      $('input[title!=""], input[type="text"], input[type="checkbox"], select, #visual, #complex').tooltipster({
        position: 'top',
        animation: 'grow',
        theme: 'tooltipster-shadow',
        timer: 6000,
        multiple: true
      });

      $('.tip').tooltipster({
        maxWidth: 400,
        contentCloning: true,
        position: 'right',
        animation: 'grow',
        timer: 6000,
        theme: 'tooltipster-shadow'
      });

      $.validator.setDefaults({
        ignore: ""
      });

      jQuery.extend(jQuery.validator.messages, {
        required: 'Заполните, пожалуйста, поле.'
      });


      $repairForm.validate({
        ignore: '',
        highlight: function(element, errorClass) {
          if (element.type == 'checkbox' || element.type == 'radio') {
            $(element).closest('.form__flags').addClass('form__input-error');
          } else {
            $(element).addClass('form__input-error');
          }
        },
        errorClass: 'form__input-error',
        errorPlacement: function(error, element) {
          var ele = $(element),
            err = $(error),
            msg = err.text();
          if (msg) {
            ele.tooltipster('content', msg);
            ele.tooltipster('open');
            $formNotif.show();
          }
        },
        unhighlight: function(element, errorClass, validClass) {
          $(element).removeClass(errorClass).addClass(validClass).tooltipster('close');
        }
      });



      if (window.location.href.indexOf('errors') != -1) {
        $repairForm.valid();
      }
    });
  </script>

  <!-- New codebase -->
  <script src="/_new-codebase/front/vendor/fancybox/jquery.fancybox.min.js"></script>
  <script src="/_new-codebase/front/vendor/air-datepicker/js/datepicker.min.js"></script>
  <script src="/_new-codebase/front/vendor/select2/js/select2.min.js"></script>
  <script src='/_new-codebase/front/components/repair-card/repair-card.js?v=2'></script>
  <script src='/_new-codebase/front/modules/repair-card/acceptance/form-manager.js?v=2'></script>
  <script src='/_new-codebase/front/components/input-filter.js'></script>
  <script src="/_new-codebase/front/vendor/intl-tel-input/js/intlTelInput.js"></script>
  <script src="/_new-codebase/front/modules/intl-tel-input-settings.js"></script>
  <script src="/_new-codebase/front/components/status/status.js?v=<?= VER; ?>"></script>
  <script src="/_new-codebase/front/templates/main/js/approve-form.js?v=<?= VER; ?>"></script>
  <script src="/_new-codebase/front/components/request.js"></script>
  <script src="/js/main.js?v=<?= VER; ?>"></script>
  <!-- Aside controls -->
  <script src="/_new-codebase/front/components/aside-controls/js/confirm-approve-window.js?v=<?= VER; ?>"></script>
  <script src="/_new-codebase/front/components/aside-controls/js/save-parts-window.js?v=<?= VER; ?>"></script>
  <script src="/_new-codebase/front/components/aside-controls/js/aside-controls.js?v=<?= VER; ?>"></script>
  <!-- / Aside controls -->
  <script src="/js/jquery.radios-to-slider.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {


      $('[data-datepicker]').datepicker({
        language: 'ru',
        autoClose: true,
        maxDate: new Date(),
        onShow: function(inst, animationCompleted) {
          if (inst.el.getAttribute('readonly') !== null) {
            inst.hide();
          }
        }
      });

      $('[data-datepicker-receive]').datepicker({
        language: 'ru',
        autoClose: true,
        minDate: new Date($('#receive-date').data('mindate')),
        maxDate: new Date()
      });

      $('.select2').select2();

    });
  </script>

  <!-- <div id="aside-controls-json" style="display: none"><?= json_encode(models\RepairCard::getAsideControls($content['id'])); ?></div> -->
  <div id="repair-data-json" style="display: none"><?= json_encode(['id' => $content['id'], 'model_id' => $content['model_id']]); ?></div>
  <div id="user-data-json" style="display: none"><?= json_encode(['id' => models\User::getData('id'), 'role' => models\User::getData('role')]); ?></div>
</body>

</html>