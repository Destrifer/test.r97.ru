<?php

use program\core;
use models\dashboard\Data;
use models\dashboard\UI;
use models\dashboard\Filter;
use models\dashboard\Settings;
use models\staff\Staff;
use models\User;
use program\core\App;

ini_set('display_errors', 'On');
error_reporting(E_ALL);

UI::goToDefaultTab();

define('VER', 1);

require '_new-codebase/front/templates/main/parts/dashboard/ui.php';
require '_new-codebase/front/templates/main/parts/dashboard/table.php';
require '_new-codebase/front/templates/main/parts/dashboard/filter.php';

if (!empty(App::$URLParams['action'])) {
    switch (App::$URLParams['action']) {
        case 'get-table-editor':
            if (empty(App::$URLParams['tab'])) {
                echo '<p>Параметр "tab" отсутствует.</p>';
                exit;
            }
            require '_new-codebase/front/templates/main/parts/dashboard/table-editor.php';
            $curCols = Settings::getCurrentCols(User::getData('id'), App::$URLParams['tab']);
            $availCols = Settings::getAvailableCols(User::getData('role'), $curCols);
            if (!$curCols) {
                $curCols = $availCols;
                $availCols = [];
            }
            echo getTableEditorHTML($availCols, $curCols);
            exit;
            break;
    }
}

if (!empty($_POST['ajax'])) {
    $response = [];
    switch ($_POST['ajax']) {
        case 'save-settings':
            $response['success_flag'] = Settings::save($_POST['uri'], $_POST['val']);
            break;
        case 'load-settings':
            $response['settings'] = Settings::get($_POST['uri']);
            break;
        case 'save-table-editor':
            $response['success_flag'] = Settings::saveCurrentCols(User::getData('role'), User::getData('id'), $_POST['tab']);
            break;
        case 'save-cols-width':
            $response['success_flag'] = Settings::saveColsWidth(User::getData('role'), User::getData('id'), $_POST['tab']);
            break;
        case 'load-rows':
            $fn = (User::hasRole('admin', 'service')) ? 'getRowsHTML' : 'getGroupedRowsHTML';
            $response['rows_html'] = $fn(Data::getRows(), UI::getCols(), models\ParamsDict::getParamsBySectionID(1), User::getData('role'));
            $response['pagination_html'] = getPaginationHTML(UI::getPagination());
            $response['total_repairs_cnt'] = Data::getCnt();
            break;
        case 'load-filter':
            $filter = Filter::getFilterByURI($_POST['filter-uri']);
            $response['filter_type'] = $filter['type'];
            $response['filter_input_html'] = getFilterInputHTML($filter['uri'], $filter['type'], Filter::getFilterInputValue($_POST['filter-uri']));
            break;
    }
    echo json_encode($response);
    exit;
}

$statuses = models\ParamsDict::getParamsBySectionID(1);
$masters = Staff::getMastersList();
?>
<!doctype html>
<html>

<head>
    <meta charset=utf-8>
    <title>Панель управления</title>
    <link href="/css/fonts.css" rel="stylesheet" />
    <link href="/css/style-without-forms.css" rel="stylesheet" />
    <link href="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.min.css" rel="stylesheet" />
    <link href="/_new-codebase/front/vendor/animate.min.css" rel="stylesheet" />
    <link href="/notifier/css/style.css" rel="stylesheet">

    <!-- New codebase -->
    <link href="/_new-codebase/front/vendor/fancybox/jquery.fancybox.min.css" rel="stylesheet" />
    <link href="/_new-codebase/front/vendor/air-datepicker/css/datepicker.min.css" rel="stylesheet" />
    <link href="/_new-codebase/front/vendor/select2/css/select2.min.css" rel="stylesheet">
    <link href="/_new-codebase/front/templates/main/css/form.css?v=<?= VER; ?>" rel="stylesheet" />
    <link href="/_new-codebase/front/templates/main/css/grid.css?v=<?= VER; ?>" rel="stylesheet" />
    <link href="/_new-codebase/front/modules/dashboard/css/fonts.css?v=<?= VER; ?>" rel="stylesheet" />
    <link href="/_new-codebase/front/modules/dashboard/css/layout.css?v=<?= VER; ?>" rel="stylesheet" />
    <link href="/_new-codebase/front/modules/dashboard/css/table.css?v=<?= VER; ?>" rel="stylesheet" />
    <link href="/_new-codebase/front/modules/dashboard/css/ui.css?v=<?= VER; ?>" rel="stylesheet" />
    <link href="/_new-codebase/front/modules/dashboard/css/filter.css?v=<?= VER; ?>" rel="stylesheet" />
    <link href="/_new-codebase/front/modules/dashboard/css/table-editor.css?v=<?= VER; ?>" rel="stylesheet" />

    <style>
        * {
            box-sizing: border-box;
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
                    <a href="/login-like/1/">service2</a> <span style="color:#fff;">-></span> <span style="color:#fff;"><?= User::getData('login'); ?></span>
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

        <h2 class="layout__mb_lg">Текущая активность СЦ</h2>

        <!-- Меню вкладок -->
        <section class="layout__mb_md">
            <?= getTabsHTML(UI::getTabs(User::getData('role'))); ?>
        </section>

        <!-- Цвета ремонтов -->
        <section class="layout__mb_md">
            <?php
            $colorsHTML = getColorsLegendHTML(UI::getColorsCaptions(User::getData('role')));
            echo $colorsHTML;
            ?>
        </section>

        <!-- Фильтры -->
        <section class="layout__mb_md">
            <div class="container">
                <div class="row filter__row" id="filter-items-ctr">
                    <?= getFilterItemsHTML(Filter::getActiveFilter(), Filter::getFilter()); ?>
                </div>
            </div>
        </section>

        <!-- Операции над выбранными ремонтами -->
        <section class="layout__mb_md">
            <?php
            $userRole = User::getData('role');
            switch ($userRole) {
                case 'taker':
                    echo getOperationsSlaveAdminHTML($masters);
                    break;
                case 'admin':
                    echo getOperationsSlaveAdminHTML([], $statuses);
                    break;
                case 'slave-admin':
                    echo getOperationsSlaveAdminHTML($masters, $statuses);
                    break;
                case 'master':
                    echo getOperationsMasterHTML();
                    break;
                case 'service':
                    echo getOperationsMasterHTML();
                    break;
            }
            ?>
        </section>

        <!-- Кол-во, поиск, нумерация страниц -->
        <section class="layout__mb_md">
            <div class="container gutters">
                <div class="row">
                    <div class="col-1">
                        <label class="capt">Кол-во:</label>
                        <?= getNumPerPageHTML(Data::getNumPerPage()); ?>
                    </div>
                    <div class="col-2"></div>
                    <div class="col-6">
                        <div class="capt" style="text-align: center">Ремонтов: <span data-cnt="repairs-on-page">-</span> из <span data-cnt="total-repairs">-</span></div>
                        <nav data-pagination class="table-pagination"></nav>
                    </div>
                    <div class="col-3">
                        <div class="capt">Поиск</div>
                        <input type="search" data-input="search" value="<?= ((isset(core\App::$URLParams['search'])) ? core\App::$URLParams['search'] : ''); ?>" placeholder="🔎" class="form__text">
                    </div>
                </div>
            </div>
        </section>

        <!-- Таблица (без строк) -->
        <section class="layout__mb_md">
            <?= getTableHTML(UI::getCols()); ?>
        </section>

        <section class="layout__mb_md" style="margin-bottom: 100px">
            <!-- Нумерация страниц -->
            <div class="container gutters">
                <div class="row">
                    <div class="col-1"></div>
                    <div class="col-2"></div>
                    <div class="col-6">
                        <div class="capt" style="text-align: center">Ремонтов: <span data-cnt="repairs-on-page">-</span> из <span data-cnt="total-repairs">-</span></div>
                        <nav data-pagination class="table-pagination"></nav>
                    </div>
                    <div class="col-3"></div>
                </div>
            </div>
            <!-- Цвета ремонтов -->
            <?= $colorsHTML; ?>
        </section>

    </main>

    <div class="to-top-btn" id="to-top-btn" data-action="to-top" style="display: none" title="Наверх/обратно"></div>

    <!-- Невидимые элементы -->
    <?= getSortHTML(UI::getTabSort()); ?>

    <form method="POST" id="attention-comment-form" action="" style="display: none">
        <h3 style="margin-bottom: 16px;">Введите причину проверки:</h3>
        <div style="margin-bottom: 16px;">
            <textarea name="message" required class="form__text" cols="70" rows="5"></textarea>
        </div>
        <div>
            <input type="hidden" name="repair_id" value="">
            <input type="hidden" name="attention_flag" value="">
            <button type="submit" class="form__btn form__btn_main">
                Отправить
            </button>
        </div>
    </form>

    <div id="loader" class="table__loader-overlay">
        <div class="table__loader">Загрузка данных...</div>
    </div>

    <div id="masters-select" class="popup-select" data-popup>
        <select class="form__select">
            <option value="0">Без мастера</option>
            <?php
            foreach ($masters as $val => $name) {
                echo '<option value="' . $val . '">' . $name . '</option>';
            }
            ?>
        </select>
    </div>

    <div id="user-data" style="display: none"><?= json_encode(['id' => models\User::getData('id')]); ?></div>

    <script src="/_new-codebase/front/vendor/jquery/jquery.min.js"></script>
    <script src='/_new-codebase/front/vendor/mustache/mustache.min.js'></script>
    <script src="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.concat.min.js"></script>
    <script src="/notifier/js/index.js"></script>

    <!-- New codebase -->
    <script src="/_new-codebase/front/vendor/fancybox/jquery.fancybox.min.js"></script>
    <script src="/_new-codebase/front/vendor/air-datepicker/js/datepicker.min.js"></script>
    <script src="/_new-codebase/front/vendor/select2/js/select2.min.js"></script>
    <script src="/_new-codebase/front/modules/dashboard/js/settings.js?v=<?= VER; ?>"></script>
    <script src="/_new-codebase/front/modules/dashboard/js/repair.js?v=<?= VER; ?>"></script>
    <script src="/_new-codebase/front/modules/dashboard/js/table.js?v=<?= VER; ?>"></script>
    <script src="/_new-codebase/front/modules/dashboard/js/filter.js?v=<?= VER; ?>"></script>
    <script src="/_new-codebase/front/modules/dashboard/js/table-editor.js?v=<?= VER; ?>"></script>
    <script src="/_new-codebase/front/modules/dashboard/js/main.js?v=<?= VER; ?>"></script>
</body>

</html>