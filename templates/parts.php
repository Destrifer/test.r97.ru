<?php

use models\dicts\Dict;
use models\geo\Countries;
use models\Parts;
use models\parts\Depots;
use models\parts\Disposals;
use models\parts\PartsTableExcel;
use models\PartsTable;
use models\User;
use program\core\App;

ini_set('display_errors', 'On');
error_reporting(E_ALL);

define('VER', 7);

require '_new-codebase/front/templates/main/parts/form.php';
require '_new-codebase/front/templates/main/parts/common.php';
require '_new-codebase/front/templates/main/parts/table.php';
require '_new-codebase/front/templates/main/parts/parts/parts.php';
require '_new-codebase/front/templates/main/parts/parts/filter-form.php';
require '_new-codebase/front/templates/main/parts/parts/disposal-parts.php';
require '_new-codebase/front/templates/main/parts/parts/move-parts.php';

$filter = PartsTable::prepareFilter(App::$URLParams);
if (!empty(App::$URLParams['ajax'])) {
  $response = [];
  switch (App::$URLParams['ajax']) {

    case 'move-parts':
      if (User::hasRole('admin', 'store', 'taker', 'slave-admin')) {
        $response = Parts::move($_POST);
      }
      break;

    case 'clone-part':
      if (User::hasRole('admin')) {
        $response = Parts::clone($_POST['part_id'], $_POST['depot_id']);
      }
      break;

    case 'restore-part':
      $response = Parts::restore($_POST['part_id']);
      break;

    case 'send-dispose-request':
      $response = Disposals::sendDisposeRequest($_POST);
      break;

    case 'dispose-parts':
      $response = Disposals::disposeParts($_POST);
      break;

    case 'get-move-parts-window':
      echo getMovePartsTableHTML(Depots::getDepots());
      exit;

    case 'get-dispose-parts-table':
      $response = ['parts_table_html' => getDisposalPartsTableHTML(PartsTable::getDisposalParts($_POST), User::hasRole('service'))];
      break;

    case 'get-models-list':
      $response = PartsTable::getModelsList($_POST);
      break;

    case 'get-parts':
      $response = datatableResponse(PartsTable::getParts($filter), $filter, PartsTable::getCols($filter), PartsTable::getFilterCnt($filter), PartsTable::getTotalCnt(), User::getData('role'));
      break;

    default:
      $response = ['message' => 'Неверный тип запроса.', 'error_flag' => 1];
  }
  echo json_encode($response);
  exit;
}

if (!empty(App::$URLParams['action'])) {
  switch (App::$URLParams['action']) {

    case 'generate-excel':
      PartsTableExcel::generate(PartsTable::getParts($filter), $filter);
  }
  exit;
}

if (!App::$URLParams && User::hasRole('admin', 'store')) {
  header('Location: /parts/?depot_id=1');
  exit;
}

if (User::hasRole('service')) {
  $secNav = [
    ['name' => 'История', 'url' => '/parts-log/'],
  ];
  if (empty(App::$URLParams['show-disposals'])) {
    $secNav[] = ['name' => 'Готовые к утилизации', 'url' => '/parts/?show-disposals=1', 'cnt' => PartsTable::getDisposalPartsCount()];
  } else {
    $secNav[] = ['name' => 'Все запчасти', 'url' => '/parts/'];
  }
} else {
  $reqCnt = '';
  if ($_SESSION['cache_requests_cnt']) {
    $reqCnt = '<span class="sec-nav__cnt" title="Запросов на утилизацию">' . $_SESSION['cache_requests_cnt'] . '</span>';
  }
  $secNav = [
    ['name' => 'Добавить запчасть', 'url' => '/part/'],
    ['name' => 'Загрузка/выгрузка в Excel', 'url' => '/upload-parts/'],
    ['name' => 'Склады', 'url' => '/depots/'],
    ['name' => 'История', 'url' => '/parts-log/'],
    ['name' => 'Отправки', 'url' => '/parts-ships/'],
    ['name' => 'Приходы', 'url' => '/parts-arrivals/'],
    ['name' => 'Запросы на утилизацию' . $reqCnt, 'url' => '/disposal-requests/'],
    ['name' => 'Транспортные компании', 'url' => '/transport-companies/'],
    ['name' => 'Справочник шаблонов', 'url' => '/part-names-ref/']
  ];
}

$title = 'Запчасти';
if (User::hasRole('service')) {
  if (!empty(App::$URLParams['show-disposals'])) {
    $title = 'Готовые к утилизации запчасти (' . PartsTable::getDisposalPartsCount() . ')';
  } else {
    $title = 'Запчасти, склад Разбор';
  }
}
?>
<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <title><?= $title; ?> - Панель управления</title>
  <link href="/css/fonts.css" rel="stylesheet" />
  <link href="/css/style-without-forms.css" rel="stylesheet" />
  <link href="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.min.css" rel="stylesheet" />
  <link href="/_new-codebase/front/vendor/animate.min.css" rel="stylesheet" />
  <link href="/notifier/css/style.css" rel="stylesheet">
  <link href="/css/ic.css" rel="stylesheet">
  <link href="/js/fSelect.css" rel="stylesheet">

  <!-- New codebase -->
  <link href="/_new-codebase/front/vendor/fancybox/jquery.fancybox.min.css" rel="stylesheet" />
  <link href="/_new-codebase/front/vendor/air-datepicker/css/datepicker.min.css" rel="stylesheet" />
  <link href="/_new-codebase/front/vendor/select2/css/select2.min.css" rel="stylesheet">
  <link href="/_new-codebase/front/vendor/datatables/datatables.min.css" rel="stylesheet">
  <link href="/_new-codebase/front/vendor/datatables/datatables-custom.css?v=<?= VER; ?>" rel="stylesheet">
  <link href="/_new-codebase/front/templates/main/css/form.css?v=<?= VER; ?>" rel="stylesheet" />
  <link href="/_new-codebase/front/templates/main/css/grid.css?v=<?= VER; ?>" rel="stylesheet" />
  <link href="/_new-codebase/front/templates/main/css/layout.css?v=<?= VER; ?>" rel="stylesheet" />
  <link href="/_new-codebase/front/templates/main/css/sec-nav.css?v=<?= VER; ?>" rel="stylesheet" />
  <link href="/_new-codebase/front/templates/main/css/table.css?v=<?= VER; ?>" rel="stylesheet" />
  <link href="/_new-codebase/front/modules/parts/parts.css?v=<?= VER; ?>" rel="stylesheet" />
  <link href="/_new-codebase/front/modules/parts/filter-form.css?v=<?= VER; ?>" rel="stylesheet" />
  <style>
    * {
      box-sizing: border-box;
    }

    .fancybox-active .select2-container {
      z-index: 99992;
    }
  </style>
</head>

<body>

  <header class="site-header">
    <div class="wrapper" style="max-width: 1920px">

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
        <?php if (isset($_SESSION['adminer']) && $_SESSION['adminer'] == 1) { ?>
          <a href="/login-like/1/">service2</a> <span style="color:#fff;">-></span> <span style="color:#fff;"><?= \models\User::getData('login'); ?></span>
        <?php } else {  ?>
          <a href="/logout/">Выйти, <?= \models\User::getData('login'); ?></a>
        <?php } ?>
      </div>
    </div>
  </header>

  <main class="wrapper" style="max-width: 1920px">

    <?= top_menu_admin(); ?>

    <!-- Главное меню -->
    <nav class="adm-tab"><?= menu_dash(); ?></nav>

    <h2 class="layout__mb_md"><?= $title; ?></h2>

    <nav class="layout__mb_md">
      <?php secNavHTML($secNav); ?>
    </nav>

    <section class="layout__mb_lg" style="visibility: hidden" id="filter-section">
      <?php filterFormPartsHTML(
        App::$URLParams,
        PartsTable::getCatsList(),
        PartsTable::getCodesList(),
        PartsTable::getModelsList(App::$URLParams),
        (User::hasRole('service')) ? [] : PartsTable::getDepotsList(),
        PartsTable::getPartsList(),
        (User::hasRole('service')) ? [] : Parts::$partAttrs,
        (User::hasRole('service')) ? [] : Parts::$partTypes,
        (User::hasRole('service')) ? [] : Parts::getVendors(),
        (User::hasRole('service', 'slave-admin')) ? [] : Countries::getCountries()
      ); ?>
    </section>

    <section class="layout__mb_md">
      <?php
      if (User::hasRole('service')) {
        $n = [
          ['name' => 'Выбрать все', 'url' => '', 'action' => 'select-all'],
          ['name' => 'Снять все', 'url' => '', 'action' => 'deselect-all']
        ];
        if (!empty(App::$URLParams['show-disposals'])) {
          $n[] = ['name' => 'Запрос на утилизацию', 'url' => '', 'action' => 'open-disposal-window'];
        }
        secNavHTML($n);
      } else {
        secNavHTML([
          ['name' => 'Переместить запчасти', 'url' => '', 'action' => 'open-move-window'],
          ['name' => 'Утилизировать запчасти', 'url' => '', 'action' => 'open-disposal-window'],
          ['name' => 'Выбрать все', 'url' => '', 'action' => 'select-all'],
          ['name' => 'Снять все', 'url' => '', 'action' => 'deselect-all'],
          ['name' => 'Выгрузить в Excel', 'url' => '', 'action' => 'generate-excel']
        ]);
      }
      ?>
    </section>

    <section class="layout__mb_lg">
      <table id="datatable" class="display">
        <?php tableHeadHTML(PartsTable::getCols($filter)); ?>
      </table>
    </section>

  </main>

  <?php
  if (User::hasRole('service')) {
    disposalWindowServiceHTML();
  } else {
    disposalWindowAdminHTML(Dict::getValues(1, [1, 2, 3, 4]), (!empty(App::$URLParams['show-disposals']) ? 6 : 0));
  }
  ?>

  <script src="/_new-codebase/front/vendor/jquery/jquery.min.js"></script>
  <script src="/_new-codebase/front/vendor/fancybox/jquery.fancybox.min.js"></script>
  <script src='/_new-codebase/front/vendor/mustache/mustache.min.js'></script>
  <script src="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.concat.min.js"></script>
  <script src="/notifier/js/index.js"></script>
  <script src="/js/fSelect.js"></script>

  <!-- New codebase -->
  <script src="/_new-codebase/front/vendor/air-datepicker/js/datepicker.min.js"></script>
  <script src="/_new-codebase/front/vendor/select2/js/select2.min.js?v=<?= VER; ?>"></script>
  <script src="/_new-codebase/front/vendor/datatables/datatables-custom.js?v=<?= VER; ?>"></script>
  <script src="/_new-codebase/front/vendor/datatables/datatables.min.js"></script>
  <script src="/_new-codebase/front/modules/parts/parts.js?v=<?= VER; ?>"></script>
  <script src="/_new-codebase/front/modules/parts/filter-form.js?v=<?= VER; ?>"></script>
  <script src="/_new-codebase/front/modules/parts/disposal-parts.js?v=<?= VER; ?>"></script>
  <script src="/_new-codebase/front/modules/parts/move-parts.js?v=<?= VER; ?>"></script>
</body>

</html>