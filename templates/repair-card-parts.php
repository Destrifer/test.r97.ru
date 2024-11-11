<?php

use models\cats\Cats;
use models\User;
use program\core\App;
use models\dashboard\UI;
use models\parts\Depots;
use models\parts\Order;
use models\repaircard\Parts;
use models\transportcompanies\Companies;

ini_set('display_errors', 'On');
error_reporting(E_ALL);

require '_new-codebase/front/templates/main/parts/form.php';
require '_new-codebase/front/templates/main/parts/repair-card/repair-card.php';
require '_new-codebase/front/templates/main/parts/repair-card/parts-list.php';
require '_new-codebase/front/templates/main/parts/repair-card/parts-order.php';
require '_new-codebase/front/templates/main/parts/repair-card/parts-send-window.php';
require '_new-codebase/front/templates/main/parts/repair-card/parts-extra-info-window.php';
require '_new-codebase/front/templates/main/parts/repair-card/store-window.php';
require '_new-codebase/front/templates/main/parts/repair-card/depots-window.php';
require '_new-codebase/front/templates/main/parts/dashboard/ui.php';

define('VER', 40);

photoRedir(App::$URL[1]);

if (isset(App::$URLParams['ajax'])) {
    $resp = [];
    switch (App::$URLParams['ajax']) {
        case 'get-extra-info-window':
            $partIDs = explode(',', $_POST['part_ids']);
            $parts = [];
            foreach ($partIDs as $partID) {
                $parts[] = \models\Parts::getPartByID2($partID);
            }
            echo getExtraInfoWindowHTML($parts, $_POST['repair_id']);
            exit;
            break;

        case 'open-depots-window':
            if (User::hasRole('admin', 'store')) {
                echo getDepotsWindowHTML(Depots::getDepots(), $_POST['part_id'], $_POST['order_id']);
            }
            exit;
            break;

        case 'open-store':
            echo getStoreWindowHTML(\models\Parts::getPartByID2($_POST['part_id']), \models\Parts::getDepotsBalance($_POST['part_id']), $_POST['order_id']);
            exit;
            break;

        case 'replace-depot': // заменить запчасть на такую же, но с другого склада
            $resp = Order::replaceDepot($_POST['order_id'], $_POST['part_id'], $_POST['depot_id'], $_POST['num']);
            break;

        case 'parts-search':
            $parts = Parts::search($_POST, App::$URL[1]);
            $resp = ['html' => getPartsListItemsHTML($parts)];
            break;

        case 'upload-photo':
            $resp = \models\Parts::uploadTmpPhoto();
            break;

        case 'save-extra-window':
            $resp = Order::saveExtraInfo($_POST['parts'], $_POST['repair_id']);
            break;

        case 'receive-order':
            $resp = Order::receiveOrder($_POST['order_id']);
            break;

        case 'reopen-order':
            $resp = Order::reopenOrder($_POST['order_id']);
            break;

        case 'return-order':
            $resp = Order::returnOrder($_POST['order_id'], $_POST['message']);
            break;

        case 'delete-order':
            $resp = Order::deleteOrder($_POST);
            break;

        case 'edit-order':
            $resp = Order::editOrder($_POST);
            break;

        case 'update-order':
            $resp = Order::updateOrder($_POST);
            break;

        case 'send-order':
            $resp = Order::sendOrder($_POST);
            break;

        case 'save-order':
            $resp = Order::saveOrder($_POST);
            break;

        case 'cancel-order':
            $resp = Order::cancelOrder($_POST);
            break;

        case 'get-store-part-html':
            $altFlag = 0;
            if (User::hasRole('admin', 'store')) {
                $altFlag = Order::isAlternativePart($_POST['part_id'], Parts::search(['depot' => 'current'], App::$URL[1]));
            }
            $resp['html'] = getStoreRowHTML(Order::getStorePart($_POST['part_id'], $_POST['depot_id'], 1, 0, 0, 0, $altFlag), true);
            break;

        case 'get-manual-part-html':
            $resp['html'] = getManualRowHTML(Order::getManualPart($_POST['part_id']), true);
            break;

        case 'get-manual-part-window':
            echo getManualPartWindowHTML($_POST['repair_id']);
            exit;
            break;

        case 'save-manual-part-window':
            $resp = Order::saveManualPart($_POST);
            break;

        case 'take-parts': // со своего склада
            $resp = Order::takeParts($_POST);
            break;

        case 'del-part':
            $resp = Order::delPart($_POST['order_id'], $_POST['part_id'], $_POST['origin'], $_POST['message']);
            break;

        case 'return-part':
            $resp = Order::returnPartToStore($_POST['order_id'], $_POST['part_id'], $_POST['message']);
            break;

        case 'receive-part': // получить запчасть от СЦ обратно
            $resp = Order::receivePartFromService($_POST['order_id'], $_POST['part_id'], $_POST['depot_id']);
            break;

        case 'cancel-part':
            $resp = Order::setCancelPart($_POST['cancel_flag'], $_POST['order_id'], $_POST['part_id'], $_POST['origin']);
            break;

        default:
            $resp = ['message' => 'Ошибка запроса.', 'error_flag' => 1];
    }
    echo json_encode($resp);
    exit;
}

try {
    $repair = models\Repair::getRepairByID(App::$URL[1]);
    $orders = Order::getOrdersByRepairID($repair['id']);
    $stepsNavHTML = getStepsNavHTML(\models\RepairCard::getStepsNav($repair['id'], 'parts'));
} catch (Exception $e) {
    exit($e->getMessage() . ' <a href="/">Перейти на главную</a></p>');
}
?>
<!doctype html>
<html>

<head>
    <meta charset=utf-8>
    <title>Запчасти - Карточка ремонта</title>
    <link href="/css/fonts.css" rel="stylesheet" />
    <link href="/css/style-without-forms.css" rel="stylesheet" />
    <link href="/css/ic.css" rel="stylesheet" />
    <link rel="stylesheet" href="/notifier/css/style.css">
    <link rel="stylesheet" href="/_new-codebase/front/vendor/animate.min.css" />
    <link rel="stylesheet" href="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.min.css" />
    <link rel="stylesheet" href="/_new-codebase/front/vendor/fancybox/jquery.fancybox.min.css" />
    <link rel="stylesheet" href="/_new-codebase/front/vendor/air-datepicker/css/datepicker.css">
    <link rel="stylesheet" href="/_new-codebase/front/vendor/select2/css/select2.min.css">
    <link rel="stylesheet" href="/js/fSelect.css" />
    <!-- New codebase -->
    <style>
        * {
            box-sizing: border-box;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 28px !important;
        }
    </style>
    <link href="/_new-codebase/front/modules/dashboard/css/ui.css" rel="stylesheet" />
    <link href="/_new-codebase/front/templates/main/css/grid.css" rel="stylesheet">
    <link href="/_new-codebase/front/templates/main/css/table.css" rel="stylesheet">
    <link href="/_new-codebase/front/templates/main/css/form.css?v=<?= VER; ?>" rel="stylesheet">
    <link href="/_new-codebase/front/templates/main/css/repair-card/repair-card.css?v=<?= VER; ?>" rel="stylesheet">
    <link href="/_new-codebase/front/templates/main/css/repair-card/parts/extra-info-window.css?v=<?= VER; ?>" rel="stylesheet">
    <link href="/_new-codebase/front/templates/main/css/repair-card/parts/parts.css?v=<?= VER; ?>" rel="stylesheet">
    <link href="/_new-codebase/front/templates/main/css/repair-card/parts/parts-list.css?v=<?= VER; ?>" rel="stylesheet">
    <link href="/_new-codebase/front/templates/main/css/repair-card/parts/parts-order.css?v=<?= VER; ?>" rel="stylesheet">
    <link href="/_new-codebase/front/templates/main/css/repair-card/save-parts-window.css?v=<?= VER; ?>" rel="stylesheet">
    <link href="/_new-codebase/front/templates/main/css/notice.css?v=<?= VER; ?>" rel="stylesheet" />
    <script src='/_new-codebase/front/vendor/jquery/jquery.min.js'></script>
    <script src='/js/main.js'></script>
    <!-- Aside controls -->
    <link href="/_new-codebase/front/components/aside-controls/css/aside-controls.css" rel="stylesheet">
</head>

<body>
    <?php
    if ($repair['status'] == 'Есть вопросы' && User::hasRole('service')) {
        echo '<div class="top-message top-message_alert" style="text-align:center">Пожалуйста, внесите исправления в карточку и отправьте на проверку.</div>';
    }
    ?>

    <main class="viewport-wrapper">

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

        <div class="wrapper" style="max-width: 1280px">

            <?= top_menu_admin(); ?>

            <div class="adm-tab">

                <?= getSummaryHTML(models\RepairCard::getSummary($repair['id'])); ?>

                <?= menu_dash(); ?>

            </div><!-- .adm-tab -->
            <br>

            <!-- Меню вкладок -->
            <section class="layout__mb_md">
                <?= getTabsHTML(UI::getTabs(User::getData('role'))); ?>
            </section>

            <h2>Процессинг</h2>

            <?= $stepsNavHTML; ?>

            <section class="repair-card">

                <div class="container gutters">
                    <div class="row">
                        <?php if (Order::isNewOrderAllowed($repair['id'])) : ?>
                            <div class="col-12">
                                <?= getOrderFormHTML((!User::hasRole('admin', 'store')) ? [] : ['status_id' => Order::SERVICE_SENT], $repair['id'], $repair['service_id']); ?>
                                <div class="form__sep" style="height: 35px"></div>
                            </div>
                        <?php elseif (User::hasRole('service') && in_array($repair['status'], ['Запрос на монтаж', 'Запрос на демонтаж', 'Запрос на выезд'])) : ?>
                            <div class="col-12">
                                <div class="notice notice__alert" style="font-weight: 600">
                                    Ремонт находится в статусе "<?= $repair['status']; ?>", заказ запчастей невозможен, пока администратор не обработает запрос.
                                </div>
                                <div class="form__sep" style="height: 35px"></div>
                            </div>
                        <?php endif; ?>
                        <?php foreach ($orders as $order) : ?>
                            <div class="col-12">
                                <?= getOrderFormHTML($order, $repair['id'], $repair['service_id']); ?>
                            </div>
                            <div class="col-12">
                                <div class="form__sep" style="height: 35px"></div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <form class="row" id="filter-form" style="padding-bottom: 2em;">
                        <?php
                        filterFormHTML(
                            User::getData('role'),
                            Parts::getCountries(),
                            Parts::getDepots(),
                            \models\Parts::$partAttrs,
                            \models\Parts::$partTypes,
                            \models\Providers::getProviders(),
                            Cats::getCatsList(),
                            Parts::getGroups()
                        );
                        ?>
                    </form>

                    <div class="row">
                        <div class="col-12">
                            <div class="form__cell">
                                <section class="parts-list container" id="parts-list">
                                    <div style="text-align: center">Загрузка...</div>
                                </section>
                            </div>
                        </div>

                        <?php if (User::hasRole('service')) : ?>
                            <div class="col-12">
                                <button class="no-parts-btn" data-action="open-manual-part-window">Требуемый к заказу модуль отсутствует в списке</button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <input type="hidden" data-input="repair-id" value="<?= $repair['id']; ?>">

            <?= $stepsNavHTML; ?>
        </div>
    </main>

    <?php
    if (User::hasRole('admin', 'store')) {
        sendWindowHTML(Companies::getCompanies());
    }
    ?>

    <!-- Confirm modal window -->
    <div style="display: none" id="confirm-window">
        <div class="form" style="min-width: 400px">
            <h3 class="form__title" style="margin: 0 0 34px 0;" data-elem="title"></h3>
            <div><textarea class="form__text" data-input="confirm-value" rows="10"></textarea></div>
            <div class="form__cell-panel" style="justify-content: space-between;">
                <button class="form__btn" data-on-click="confirm">Подтвердить</button>
                <button class="form__btn form__btn_secondary" data-on-click="close">Отмена</button>
            </div>
        </div>
    </div>
    <!-- / Confirm modal window -->

    <div id="user-data-json" style="display: none"><?= json_encode(['id' => User::getData('id'), 'role' => User::getData('role')]); ?></div>

    <script src='/_new-codebase/front/vendor/mustache/mustache.min.js'></script>
    <script src="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.concat.min.js"></script>
    <script src="/notifier/js/index.js"></script>
    <script src="/js/fSelect.js"></script>

    <!-- New codebase -->
    <script src="/_new-codebase/front/vendor/fancybox/jquery.fancybox.min.js"></script>
    <script src="/_new-codebase/front/vendor/air-datepicker/js/datepicker.min.js"></script>
    <script src="/_new-codebase/front/vendor/select2/js/select2.min.js?v=<?= VER; ?>"></script>
    <script src='/_new-codebase/front/components/repair-card/repair-card.js?v=<?= VER; ?>'></script>
    <script src="/_new-codebase/front/components/status/status.js"></script>
    <script src='/_new-codebase/front/components/input-filter.js?v=<?= VER; ?>'></script>
    <script src='/_new-codebase/front/modules/repair-card/parts/part.js?v=<?= VER; ?>'></script>
    <script src='/_new-codebase/front/modules/repair-card/parts/filter.js?v=<?= VER; ?>'></script>
    <script src='/_new-codebase/front/modules/repair-card/parts/store-window.js?v=<?= VER; ?>'></script>
    <script src='/_new-codebase/front/modules/repair-card/parts/depots-window.js?v=<?= VER; ?>'></script>
    <script src='/_new-codebase/front/modules/repair-card/parts/send-window.js?v=<?= VER; ?>'></script>
    <script src='/_new-codebase/front/modules/repair-card/parts/confirm-window.js?v=<?= VER; ?>'></script>
    <script src='/_new-codebase/front/modules/repair-card/parts/extra-info-window.js?v=<?= VER; ?>'></script>
    <script src='/_new-codebase/front/modules/repair-card/parts/parts-list.js?v=<?= VER; ?>'></script>
    <script src='/_new-codebase/front/modules/repair-card/parts/parts-order.js?v=<?= VER; ?>'></script>
    <script src='/_new-codebase/front/modules/repair-card/parts/parts.js?v=<?= VER; ?>'></script>
    <!-- Aside controls -->
    <script src="/_new-codebase/front/components/request.js"></script>
    <script src="/_new-codebase/front/components/aside-controls/js/confirm-approve-window.js"></script>
    <script src="/_new-codebase/front/components/aside-controls/js/save-parts-window.js"></script>
    <script src="/_new-codebase/front/components/aside-controls/js/aside-controls.js"></script>
    <!-- / Aside controls -->
    <div id="aside-controls-json" style="display: none"><?= json_encode(models\RepairCard::getAsideControls($repair['id'])); ?></div>
    <div id="repair-data-json" style="display: none"><?= json_encode(['id' => $repair['id'], 'model_id' => $repair['model_id']]); ?></div>
</body>

</html>