<?php

use models\store\dashboard\ColsBuilder;
use models\store\dashboard\Filters;
use models\store\dashboard\Table;
use models\store\dashboard\Tabs;
use models\User;
use program\core\App;

ini_set('display_errors', 'On');
error_reporting(E_ALL);

define('VER', 1);

require '_new-codebase/front/modules/dashboard-store/tpl/tabs.php';
require '_new-codebase/front/modules/dashboard-store/tpl/cols.php';
require '_new-codebase/front/modules/dashboard-store/tpl/rows.php';

if (!empty($_POST['ajax'])) {
    header('Content-Type: application/json; Charset=utf-8');
    $response = [];
    switch ($_POST['ajax']) {

        case 'get-filters':
            $response = Filters::getFilters();
            break;

        case 'get-filter-options':
            $response = [1 => 'test'];
            break;

        case 'save-cols':
            $response = ColsBuilder::saveCols($_POST['tab_uri'], $_POST['cols_data']);
            break;

        case 'load-state':
            $response = Table::getState();
            break;

        case 'load-table':
            $response = ['rows' => rowsTPL(Table::getRows(), Table::getCols()), 'pagination' => Table::getPagination()];
            break;

        default:
            $response = ['isError' => 1, 'message' => 'Wrong request.'];
    }

    echo json_encode($response);
    exit;
}

if (empty(App::$URL[1])) {
    header('Location: /dashboard/needparts/');
    exit;
}

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
    <!-- Vendor -->
    <link href="/_new-codebase/front/vendor/fancybox/jquery.fancybox.min.css" rel="stylesheet" />
    <link href="/_new-codebase/front/vendor/air-datepicker/css/datepicker.min.css" rel="stylesheet" />
    <link href="/_new-codebase/front/vendor/select2/css/select2.min.css" rel="stylesheet">
    <!-- Common -->
    <link href="/_new-codebase/front/templates/main/css/icons.css?v=<?= VER; ?>" rel="stylesheet" />
    <link href="/_new-codebase/front/templates/main/css/form.css?v=<?= VER; ?>" rel="stylesheet" />
    <link href="/_new-codebase/front/templates/main/css/grid.css?v=<?= VER; ?>" rel="stylesheet" />
    <link href="/_new-codebase/front/templates/main/css/layout.css?v=<?= VER; ?>" rel="stylesheet" />
    <!-- Dashboard-store -->
    <link href="/_new-codebase/front/modules/dashboard-store/css/main.css?v=<?= VER; ?>" rel="stylesheet" />
    <link href="/_new-codebase/front/modules/dashboard-store/css/table.css?v=<?= VER; ?>" rel="stylesheet" />
    <link href="/_new-codebase/front/modules/dashboard-store/css/tabs.css?v=<?= VER; ?>" rel="stylesheet" />
    <link href="/_new-codebase/front/modules/dashboard-store/css/controls.css?v=<?= VER; ?>" rel="stylesheet" />
    <link href="/_new-codebase/front/modules/dashboard-store/css/preloader.css?v=<?= VER; ?>" rel="stylesheet" />
    <link href="/_new-codebase/front/modules/dashboard-store/css/filter.css?v=<?= VER; ?>" rel="stylesheet" />
    <link href="/_new-codebase/front/modules/dashboard-store/css/resizer.css?v=<?= VER; ?>" rel="stylesheet" />
    <link href="/_new-codebase/front/modules/dashboard-store/css/paginator.css?v=<?= VER; ?>" rel="stylesheet" />
    <link href="/_new-codebase/front/modules/dashboard-store/css/sorting.css?v=<?= VER; ?>" rel="stylesheet" />
    <link href="/_new-codebase/front/modules/dashboard-store/css/top-btn.css?v=<?= VER; ?>" rel="stylesheet" />
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

        <h2 class="layout__mb_lg">Заказы запчастей</h2>

        <!-- Меню вкладок -->
        <section class="layout__mb_md">
            <?= tabsTPL(Tabs::getTabs()); ?>
        </section>

        <!-- Фильтры -->
        <section class="layout__mb_md">
            <div class="filter-container" data-filter="container"></div>
        </section>

        <!-- Кнопки -->
        <section class="layout__mb_md">
            <div class="controls">
                <div class="control control_mg control_add-filter icon-filter" data-filter="add-filter">Добавить фильтр</div>
                <div class="control control_mg control_clear-filter icon-trash" data-filter="clear-filter" style="display: none">Очистить фильтр</div>
            </div>
        </section>


        <!-- Кол-во, поиск, нумерация страниц -->
        <section class="layout__mb_md">
            <div class="container gutters">
                <div class="row">
                    <div class="col-1">
                        <label class="capt">Кол-во:</label>
                        <input type="number" min="1" max="1000" value="1" data-pagination="page-len-input" class="form__text">
                    </div>
                    <div class="col-2"></div>
                    <div class="col-6">
                        <div class="pagination-wrap">
                            <div data-pagination="info-box" class="pagination-info-box"></div>
                            <nav data-pagination="nav" class="pagination-nav"></nav>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="capt">Поиск</div>
                        <input type="search" value="" placeholder="🔎" class="form__text">
                    </div>
                </div>
            </div>
        </section>

        <!-- Таблица (без строк) -->
        <section class="layout__mb_md">
            <section id="table-viewport">
                <table>
                    <thead>
                        <?= colsTPL(Table::getCols()); ?>
                    </thead>
                    <tbody id="table-body">
                        <!-- Rows -->
                    </tbody>
                </table>
            </section>
        </section>

        <section class="layout__mb_md" style="margin-bottom: 100px">
            <!-- Нумерация страниц -->
            <div class="container gutters">
                <div class="row">
                    <div class="col-1"></div>
                    <div class="col-2"></div>
                    <div class="col-6">
                        <div class="pagination-wrap">
                            <div data-pagination="info-box" class="pagination-info-box"></div>
                            <nav data-pagination="nav" class="pagination-nav"></nav>
                        </div>
                    </div>
                    <div class="col-3"></div>
                </div>
            </div>
        </section>

    </main>

    <div id="user-data" style="display: none"><?= json_encode(['id' => User::getData('id'), 'role' => User::getData('role')]); ?></div>

    <script src="/_new-codebase/front/vendor/jquery/jquery.min.js"></script>
    <script src='/_new-codebase/front/vendor/mustache/mustache.min.js'></script>
    <script src="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.concat.min.js"></script>
    <script src="/notifier/js/index.js"></script>
    <!-- Vendor -->
    <script src="/_new-codebase/front/vendor/fancybox/jquery.fancybox.min.js"></script>
    <script src="/_new-codebase/front/vendor/air-datepicker/js/datepicker.min.js"></script>
    <script src="/_new-codebase/front/vendor/select2/js/select2.min.js"></script>
    <!-- Dashboard-store -->
    <script src="/_new-codebase/front/modules/dashboard-store/js/classes/event-emitter.js?v=<?= VER; ?>"></script>
    <script src="/_new-codebase/front/modules/dashboard-store/js/classes/cols-builder.js?v=<?= VER; ?>"></script>
    <script src="/_new-codebase/front/modules/dashboard-store/js/classes/preloader.js?v=<?= VER; ?>"></script>
    <script src="/_new-codebase/front/modules/dashboard-store/js/classes/resizer.js?v=<?= VER; ?>"></script>
    <script src="/_new-codebase/front/modules/dashboard-store/js/classes/top-btn.js?v=<?= VER; ?>"></script>
    <script src="/_new-codebase/front/modules/dashboard-store/js/classes/filter.js?v=<?= VER; ?>"></script>
    <script src="/_new-codebase/front/modules/dashboard-store/js/classes/sorting.js?v=<?= VER; ?>"></script>
    <script src="/_new-codebase/front/modules/dashboard-store/js/classes/paginator.js?v=<?= VER; ?>"></script>
    <script src="/_new-codebase/front/modules/dashboard-store/js/classes/state.js?v=<?= VER; ?>"></script>
    <script src="/_new-codebase/front/modules/dashboard-store/js/classes/table.js?v=<?= VER; ?>"></script>
    <script src="/_new-codebase/front/modules/dashboard-store/js/main.js?v=<?= VER; ?>"></script>
</body>

</html>