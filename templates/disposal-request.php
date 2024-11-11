<?php

use models\disposals\Parts;
use models\disposals\Requests;
use models\User;
use program\core\App;

ini_set('display_errors', 'On');
error_reporting(E_ALL);

define('VER', 1);

require '_new-codebase/front/templates/main/parts/common.php';
require '_new-codebase/front/templates/main/parts/form.php';
require '_new-codebase/front/templates/main/parts/disposal-requests/disposal-request.php';


if (!empty(App::$URLParams['ajax'])) {
    $response = [];
    switch (App::$URLParams['ajax']) {

        case 'save':
            $response = Requests::update($_POST);
            break;

        default:
            $response = ['message' => 'Неверный тип запроса.', 'error_flag' => 1];
    }
    echo json_encode($response);
    exit;
}

$request = Requests::getRequest(['id' => App::$URLParams['id']]);
$parts = Parts::getParts(['request_id' => App::$URLParams['id']]);
$userRole = User::getData('role');

if ($userRole == 'service' && $request['user_id'] != User::getData('id')) {
    header('Location: /disposal-requests/');
    exit;
}

$secNav = [
    ['name' => 'Все запросы', 'url' => '/disposal-requests/']
];

$title = 'Запрос на утилизацию';
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

    <!-- New codebase -->
    <link href="/_new-codebase/front/vendor/air-datepicker/css/datepicker.min.css" rel="stylesheet" />
    <link href="/_new-codebase/front/vendor/fancybox/jquery.fancybox.min.css" rel="stylesheet" />
    <link href="/_new-codebase/front/vendor/select2/css/select2.min.css" rel="stylesheet">
    <link href="/_new-codebase/front/templates/main/css/form.css?v=<?= VER; ?>" rel="stylesheet" />
    <link href="/_new-codebase/front/templates/main/css/grid.css?v=<?= VER; ?>" rel="stylesheet" />
    <link href="/_new-codebase/front/templates/main/css/layout.css?v=<?= VER; ?>" rel="stylesheet" />
    <link href="/_new-codebase/front/templates/main/css/table.css?v=<?= VER; ?>" rel="stylesheet" />
    <link href="/_new-codebase/front/templates/main/css/sec-nav.css?v=<?= VER; ?>" rel="stylesheet" />
    <link href="/_new-codebase/front/modules/disposal-requests/disposal-request.css?v=<?= VER; ?>" rel="stylesheet" />
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

        <section class="layout__mb_lg">

            <form action="?ajax=save" class="disposal-request__form" id="disposal-request-form" method="POST">
                <div class="container gutters">
                    <div class="row">

                        <div class="col-12">
                            <div class="form__cell">
                                <p class="disposal-request__info-item"><b>Склад:</b> <?= $request['depot']; ?></p>
                                <p class="disposal-request__info-item"><b>Дата запроса:</b> <?= $request['add_date'] . ' <i>' . $request['add_time'] . '</i>'; ?></p>
                                <p class="disposal-request__info-item"><b>Количество наименований:</b> <?= $request['parts_num'] . ' шт.'; ?></p>
                            </div>
                        </div>

                        <div class="col-12">
                            <table class="table table_middle form">
                                <thead>
                                    <tr>
                                        <th style="width: 10%">Код</th>
                                        <th style="width: 30%">Наименование</th>
                                        <th style="width: 30%">Сообщение для СЦ</th>
                                        <th style="width: 10%">Утилизировать</th>
                                        <th style="width: 10%">Запрошено (шт.)</th>
                                        <th style="width: 10%">Утилизировано (шт.)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($parts as $part) : ?>
                                        <tr data-part>
                                            <td><?= $part['part']['part_code']; ?></td>
                                            <td><?= $part['part']['name']; ?></td>
                                            <td>
                                                <?php commentHTML($part, $userRole); ?>
                                            </td>
                                            <td class="table__center">
                                                <?php disposeFlagHTML($part, $userRole); ?>
                                            </td>
                                            <td class="table__center"><?= $part['num']; ?></td>
                                            <td>
                                                <?php disposedNumHTML($part, $userRole); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <div class="form__sep" style="height: 30px"></div>
                        </div>

                        <input type="hidden" value="<?= $request['id']; ?>" name="id">

                        <?php if ($userRole == 'admin') : ?>
                            <div class="col-12">
                                <div class="form__cell form__field_final">
                                    <button type="submit" class="form__btn form__btn_w100">
                                        Утилизировать
                                    </button>
                                </div>
                                <div class="form__cell">
                                    <div class="form__notif" id="form-notif" style="display:none"></div>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </form>

        </section>

    </main>

    <script src="/_new-codebase/front/vendor/jquery/jquery.min.js"></script>
    <script src="/_new-codebase/front/vendor/fancybox/jquery.fancybox.min.js"></script>
    <script src='/_new-codebase/front/vendor/mustache/mustache.min.js'></script>
    <script src="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.concat.min.js"></script>
    <script src="/notifier/js/index.js"></script>

    <!-- New codebase -->
    <script src="/_new-codebase/front/vendor/air-datepicker/js/datepicker.min.js"></script>
    <script src="/_new-codebase/front/vendor/select2/js/select2.min.js?v=<?= VER; ?>"></script>
    <script src="/_new-codebase/front/modules/disposal-requests/disposal-request.js?v=<?= VER; ?>"></script>

</body>

</html>