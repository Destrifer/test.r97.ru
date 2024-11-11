<?php

use models\Parts;
use models\User;
use models\Users;
use program\core\App;

require '_new-codebase/front/templates/main/parts/depots/depots.php';

if (!empty($_POST['ajax'])) {
    $resp = [];
    switch ($_POST['ajax']) {
        case 'del-depot':
            Parts::delDepot($_POST['depot_id']);
            break;
        case 'save':
            $resp = Parts::saveDepot($_POST);
            break;
    }
    echo json_encode($resp);
    exit;
}

?>

<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Склады - Панель управления</title>
    <script src="/_new-codebase/front/vendor/jquery/jquery.min.js"></script>
    <link href="/css/ic.css" rel="stylesheet" />
    <link href="/css/fonts.css" rel="stylesheet" />
    <link href="/css/style-without-forms.css" rel="stylesheet" />
    <link href="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.min.css" rel="stylesheet" />
    <link href="/_new-codebase/front/vendor/animate.min.css" rel="stylesheet" />
    <link href="/notifier/css/style.css" rel="stylesheet">

    <script src="/_new-codebase/front/vendor/datatables/1.10.12/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="/css/datatables.css">

    <!-- New codebase -->
    <link href="/_new-codebase/front/vendor/select2/css/select2.min.css" rel="stylesheet">
    <link href="/_new-codebase/front/templates/main/css/form.css" rel="stylesheet" />
    <link href="/_new-codebase/front/templates/main/css/grid.css" rel="stylesheet" />
    <link href="/_new-codebase/front/templates/main/css/layout.css" rel="stylesheet" />

    <style>
        * {
            box-sizing: border-box;
        }

        .add {
            margin-bottom: 32px;
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

        <?php if (empty(App::$URLParams['action'])) : ?>

            <div class="add">
                <a style="width: auto;padding-left: 7px;padding-right: 7px;margin-right:16px" href="/depots/?action=add" class="button">Добавить склад</a>
            </div>

            <section>
                <table id="depots-table" class="display" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th align="left">№</th>
                            <th align="left">Название</th>
                            <th align="left">Владелец</th>
                            <th align="left">Операции</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?= getDepotsTableHTML(Parts::getDepots()); ?>
                    </tbody>
                </table>
            </section>

        <?php else :
            $depot = [];
            $title = 'Добавить склад';
            $users = Users::getUsers();
            if (App::$URLParams['action'] == 'edit') {
                $depot = Parts::getDepot(App::$URLParams['depot-id']);
                $title = 'Редактировать склад';
            }
        ?>


            <div class="add">
                <?php if (App::$URLParams['action'] != 'add') : ?>
                    <a style="width: auto;padding-left: 7px;padding-right: 7px;margin-right:16px" href="/depots/?action=add" class="button">Добавить склад</a>
                <?php endif; ?>
                <a style="width: auto;padding-left: 7px;padding-right: 7px;margin-right:16px" href="/depots/" class="button">Все склады</a>
            </div>

            <h2><?= $title; ?></h2>

            <form action="?ajax=save" id="depot-form" method="POST">
                <div class="container gutters">
                    <div class="row">

                        <div class="col-6">
                            <div class="form__cell">
                                <label class="form__label part__label" for="name">Название склада:</label>
                                <input type="text" name="name" id="name" value="<?= (isset($depot['name'])) ? $depot['name'] : ''; ?>" class="form__text">
                            </div>
                        </div>


                        <div class="col-6">
                            <div class="form__cell">
                                <label class="form__label part__label" for="user-id">Владелец:</label>
                                <select name="user_id" id="user-id" class="form__select select2">
                                    <?= getOptionsHTML($users, (!isset($depot['user_id'])) ? 0 : $depot['user_id']); ?>
                                </select>
                            </div>
                        </div>

                    </div>


                    <div class="row">

                        <div class="col-12">
                            <div class="form__cell form__field_final">
                                <input type="hidden" name="depot_id" id="depot-id" value="<?= ((isset($depot['id'])) ? $depot['id'] : ''); ?>">
                                <input type="hidden" name="ajax" value="save">
                                <button type="submit" class="form__btn form__btn_w100">Сохранить</button>
                            </div>
                            <div class="form__cell">
                                <div class="form__notif" id="form-notif" style="display:none"></div>
                            </div>
                            <div class="form__sep"></div>
                        </div>

                    </div>

                </div>
            </form>

        <?php endif; ?>

    </main>

    <script src='/_new-codebase/front/vendor/mustache/mustache.min.js'></script>
    <script src="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.concat.min.js"></script>
    <script src="/notifier/js/index.js"></script>

    <!-- New codebase -->
    <script src="/_new-codebase/front/vendor/select2/js/select2.min.js"></script>
    <script src="/_new-codebase/front/modules/depots/depots.js"></script>
</body>

</html>