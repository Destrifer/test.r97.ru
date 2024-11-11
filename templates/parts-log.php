<?php

use models\dicts\Dict;
use models\Models;
use models\Parts;
use models\parts\Depots;
use models\parts\Log;
use models\parts\log\LogTable;
use models\User;
use models\Users;
use program\core\App;

define('VER', '2');

require '_new-codebase/front/templates/main/parts/form.php';
require '_new-codebase/front/templates/main/parts/table.php';
require '_new-codebase/front/templates/main/parts/log/table.php';

if (!empty(App::$URLParams['ajax'])) {
    $resp = [];
    switch (App::$URLParams['ajax']) {
        case 'get-log':
            $filter = datatableFilter(App::$URLParams, LogTable::getCols());
            $resp = datatableResponse(LogTable::getLog($filter), $filter, LogTable::getFilterCnt($filter), LogTable::getTotalCnt(), User::getData('role'));
            break;

        case 'revert':
            $resp = Log::revert($_POST['id']);
            break;
    }
    echo json_encode($resp);
    exit;
}
?>

<!doctype html>
<html>

<head>
    <meta charset=utf-8>
    <title>История запчастей - Панель управления</title>
    <link href="/css/fonts.css" rel="stylesheet" />
    <link href="/css/ic.css" rel="stylesheet" />
    <link href="/css/style-without-forms.css" rel="stylesheet" />
    <script src="/_new-codebase/front/vendor/jquery/jquery-1.7.2.min.js"></script>
    <script src="/js/main.js"></script>
    <script src="/notifier/js/index.js"></script>
    <link rel="stylesheet" type="text/css" href="/notifier/css/style.css">
    <link rel="stylesheet" href="/_new-codebase/front/vendor/animate.min.css" />
    <script src='/_new-codebase/front/vendor/mustache/mustache.min.js'></script>
    <script src="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.concat.min.js"></script>
    <link rel="stylesheet" href="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.min.css" />

    <!-- New codebase -->
    <link href="/_new-codebase/front/vendor/datatables/datatables.min.css" rel="stylesheet">
    <link href="/_new-codebase/front/vendor/datatables/datatables-custom.css?v=<?= VER; ?>" rel="stylesheet">
    <link href="/_new-codebase/front/vendor/air-datepicker/css/datepicker.min.css" rel="stylesheet" />
    <link href="/_new-codebase/front/vendor/select2/css/select2.min.css" rel="stylesheet">
    <link href="/_new-codebase/front/templates/main/css/grid.css" rel="stylesheet">
    <link href="/_new-codebase/front/templates/main/css/layout.css?v=<?= VER; ?>" rel="stylesheet" />
    <link href="/_new-codebase/front/templates/main/css/form.css?v=<?= VER; ?>" rel="stylesheet">
    <link href="/_new-codebase/front/modules/log/log.css" rel="stylesheet">
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

                    <a href="/logout/">Выйти, <?= \models\User::getData('login'); ?></a>
                </div>

            </div>
        </div><!-- .site-header -->

        <div class="wrapper">

            <?= top_menu_admin(); ?>

            <div class="adm-tab">

                <?= menu_dash(); ?>

            </div><!-- .adm-tab -->
            <br>
            <h2>История запчастей</h2>

            <main class="log" style="margin-top:20px;">

                <form class="form log__filter-form layout__mb_md">
                    <div class="container gutters">
                        <div class="row">
                            <div class="col-3">
                                <label>Действие</label>
                                <select class="form__select" name="operation" data-filter>
                                    <?php optionsHTML(LogTable::getOperations(), (isset(App::$URLParams['operation']) ? App::$URLParams['operation'] : '')); ?>
                                </select>
                            </div>
                            <div class="col-3">
                                <label>Запчасть</label>
                                <select class="form__select select2" name="part" data-filter>
                                    <?php optionsHTML(Parts::getParts(['attr_id' => 1]), (isset(App::$URLParams['part']) ? App::$URLParams['part'] : '')); ?>
                                </select>
                            </div>
                            <?php if (User::hasRole('admin', 'store', 'slave-admin')) : ?>
                                <div class="col-3">
                                    <label>Склад</label>
                                    <select class="form__select select2" name="depot" data-filter>
                                        <?php optionsHTML(Depots::getDepots(), (isset(App::$URLParams['depot']) ? App::$URLParams['depot'] : '')); ?>
                                    </select>
                                </div>
                                <div class="col-3">
                                    <label>Пользователь</label>
                                    <select class="form__select select2" name="user" data-filter>
                                        <?php optionsHTML(Users::getUsers(['active' => true]), (isset(App::$URLParams['user']) ? App::$URLParams['user'] : ''), '-- выбрать --', 'login'); ?>
                                    </select>
                                </div>
                                <div class="col-3">
                                    <div class="form__field">
                                        <label>Дата</label>
                                        <input type="text" placeholder="Дата" class="form__text" name="date" data-filter data-datepicker-interval data-range="true" data-multiple-dates-separator=" - " value="<?= App::$URLParams['date'] ?? ''; ?>">
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form__field">
                                        <label>Причина утилизации</label>
                                        <select class="form__select select2" name="object_id" data-filter>
                                            <?php optionsHTML(Dict::getValues(1), (isset(App::$URLParams['object_id']) ? App::$URLParams['object_id'] : '')); ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-5">
                                    <div class="form__field">
                                        <label>Модель</label>
                                        <select class="form__select select2" name="model" data-filter>
                                            <?php optionsHTML(Models::getModels(), (isset(App::$URLParams['model']) ? App::$URLParams['model'] : '')); ?>
                                        </select>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="col-12 log__filter-btns">
                                <button class="form__btn form__btn_secondary log__filter-btn" type="button" data-action="reset">Сброс</button>
                                <button class="form__btn log__filter-btn" type="button" data-action="apply">Применить</button>
                            </div>
                        </div>
                    </div>
                </form>

                <table id="datatable" class="display">
                    <?php tableHeadHTML(LogTable::getCols()); ?>
                </table>
            </main>

        </div>
    </div>

    <!-- New codebase -->
    <script src="/_new-codebase/front/vendor/air-datepicker/js/datepicker.min.js"></script>
    <script src="/_new-codebase/front/vendor/datatables/datatables-custom.js?v=<?= VER; ?>"></script>
    <script src="/_new-codebase/front/vendor/datatables/datatables.min.js"></script>
    <script src="/_new-codebase/front/vendor/select2/js/select2.min.js"></script>
    <script src="/_new-codebase/front/modules/log/log.js"></script>
    <script src="/_new-codebase/front/modules/parts-log/parts-log.js"></script>
</body>

</html>