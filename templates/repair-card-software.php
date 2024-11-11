<?php

use models\User;
use program\core\App;
use models\dashboard\UI;
use models\repaircard\Software;

require '_new-codebase/front/templates/main/parts/repair-card/repair-card.php';
require '_new-codebase/front/templates/main/parts/repair-card/software.php';
require '_new-codebase/front/templates/main/parts/dashboard/ui.php';

ini_set('display_errors', 'On');
error_reporting(E_ALL);

define('VER', 1);

if (isset(App::$URLParams['ajax'])) {
    $resp = [];
    switch (App::$URLParams['ajax']) {

        case 'request-software':
            $resp = Software::requestSoftware($_POST, App::$URL[1]);
            break;

        default:
            $resp = ['message' => 'Ошибка запроса.', 'error_flag' => 1];
    }
    echo json_encode($resp);
    exit;
}

photoRedir(App::$URL[1]);

try {
    $repair = models\Repair::getRepairByID(App::$URL[1]);
} catch (Exception $e) {
    exit('<p>Ремонт не найден. <a href="/">Перейти на главную</a></p>');
}

$infobase = [];
$serial = models\Serials::getSerial($repair['serial'], $repair['model_id']);
if ($serial['id']) {
    $infobase = models\Infobase::getFilesBySerialID($serial['id']);
}
?>
<!doctype html>
<html>

<head>
    <meta charset=utf-8>
    <title>Схемы и ПО - Карточка ремонта</title>
    <link href="/css/fonts.css" rel="stylesheet" />
    <link href="/css/style-without-forms.css" rel="stylesheet" />
    <link rel="stylesheet" href="/notifier/css/style.css">
    <link rel="stylesheet" href="/_new-codebase/front/vendor/animate.min.css" />
    <link rel="stylesheet" href="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.min.css" />
    <link rel="stylesheet" href="/_new-codebase/front/vendor/fancybox/jquery.fancybox.min.css" />

    <!-- New codebase -->
    <style>
        * {
            box-sizing: border-box;
        }
    </style>
    <link href="/_new-codebase/front/modules/dashboard/css/ui.css" rel="stylesheet" />
    <link href="/_new-codebase/front/templates/main/css/grid.css" rel="stylesheet">
    <link href="/_new-codebase/front/templates/main/css/form.css?v=<?= VER; ?>" rel="stylesheet">
    <link href="/_new-codebase/front/templates/main/css/repair-card/repair-card.css?v=<?= VER; ?>" rel="stylesheet">
    <link href="/_new-codebase/front/templates/main/css/repair-card/software/software.css?v=<?= VER; ?>" rel="stylesheet">
    <link href="/_new-codebase/front/templates/main/css/repair-card/save-parts-window.css" rel="stylesheet">
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

    <div class="viewport-wrapper">

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

            <?php
            $stepsNavHTML = getStepsNavHTML(\models\RepairCard::getStepsNav($repair['id'], 'software'));
            echo $stepsNavHTML;
            ?>

            <section class="repair-card">
                <?php
                echo getSoftwareHTML($infobase, (User::hasRole('admin') || !in_array($repair['status'], ['Подтвержден', 'Отклонен', 'Выдан'])));
                if (!$infobase && !User::hasRole('admin') && !in_array($repair['status'], ['Подтвержден', 'Отклонен'])) {
                    echo getRequestSoftwareForm((($repair['status'] == 'Запрос ПО') ? 'readonly' : ''), Software::$types);
                }
                ?>
            </section>

            <?= $stepsNavHTML; ?>
        </div>
    </div>
    </div>

    <script src='/_new-codebase/front/vendor/mustache/mustache.min.js'></script>
    <script src="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.concat.min.js"></script>
    <script src="/notifier/js/index.js"></script>

    <!-- New codebase -->
    <script src="/_new-codebase/front/vendor/fancybox/jquery.fancybox.min.js"></script>
    <script src='/_new-codebase/front/components/repair-card/repair-card-new.js?v=<?= VER; ?>'></script>
    <script src="/_new-codebase/front/components/status/status.js"></script>
    <script src='/_new-codebase/front/modules/repair-card/software/software.js?v=<?= VER; ?>'></script>
    <!-- Aside controls -->
    <script src="/_new-codebase/front/components/request.js"></script>
    <script src="/_new-codebase/front/components/aside-controls/js/confirm-approve-window.js"></script>
    <script src="/_new-codebase/front/components/aside-controls/js/save-parts-window.js"></script>
    <script src="/_new-codebase/front/components/aside-controls/js/aside-controls.js"></script>
    <!-- / Aside controls -->
    <div id="aside-controls-json" style="display: none"><?= json_encode(models\RepairCard::getAsideControls($repair['id'])); ?></div>
    <div id="repair-data-json" style="display: none"><?= json_encode(['id' => $repair['id'], 'model_id' => $repair['model_id']]); ?></div>
    <div id="user-data-json" style="display: none"><?= json_encode(['id' => models\User::getData('id'), 'role' => models\User::getData('role')]); ?></div>
</body>

</html>