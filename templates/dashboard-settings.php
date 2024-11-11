<?php

use models\dashboard\Settings;
use models\User;
use models\Users;
use program\core\App;

ini_set('display_errors', 'On');
error_reporting(E_ALL);

require '_new-codebase/front/templates/main/parts/dashboard-settings.php';

if (!User::hasRole('admin')) {
    header('Location: /');
    exit;
}

if (isset(App::$URLParams['action'])) {
    switch (App::$URLParams['action']) {
        case 'save':
            foreach(Users::ROLES as $userRole){
                Settings::savePermissions($userRole);
            }
            Settings::saveAllCols();
            header('Location: /dashboard/');
            exit;
    }
}



?>

<!doctype html>
<html>

<head>
    <meta charset=utf-8>
    <title>Настройки дашборда - Панель управления</title>
    <link href="/css/fonts.css" rel="stylesheet" />
    <link href="/css/style-without-forms.css" rel="stylesheet" />
    <script src="/_new-codebase/front/vendor/jquery/jquery-1.7.2.min.js" ></script>

    <script src="/notifier/js/index.js"></script>
    <link rel="stylesheet" type="text/css" href="/notifier/css/style.css">
    <link rel="stylesheet" href="/_new-codebase/front/vendor/animate.min.css" />
    <script src='/_new-codebase/front/vendor/mustache/mustache.min.js'></script>
    <script src="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.concat.min.js"></script>
    <link rel="stylesheet" href="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.min.css" />

    <script  src="/_new-codebase/front/vendor/datatables/1.10.12/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" type="text/css" href="/css/datatables.css">

    <style>
        * {
            box-sizing: border-box;
        }

        .dashboard-settings {
            margin: 32px 0;
        }

        .dashboard-settings__row {
            display: flex;
            align-items: center;
            margin-bottom: 32px;
        }

        .dashboard-settings__input-text {
            display: inline-block;
            background-color: transparent;
            padding: .6em;
            border: none;
            font-size: 16px;
            margin: 0;
        }

        .dashboard-settings__input-text:focus {
            background-color: #fff;
        }

        .dashboard-settings__col-name {
            width: 350px;
            margin-right: 16px;
            border-radius: 3px;
            background-color: #fbfaff;
            border: solid 1px #dbdbdb;
            white-space: nowrap;
            overflow: hidden;
        }

        .dashboard-settings__col-role {
            margin-right: 16px;
        }

        .dashboard-settings__role {
            padding: 8px 16px;
            background-color: #f3f3f3;
            border-radius: 25px;
            color: #020202;
            font-size: 14px;
            line-height: 1.5;
            user-select: none;
            cursor: pointer;
            transition-duration: .1s;
        }

        .dashboard-settings__col-role input[type="checkbox"] {
            display: none;
        }

        .dashboard-settings__col-role [type=checkbox]:checked+div {
            background-color: #78af01;
            color: #fff;
        }

        .submit_button {
            padding: 1em 5em;
            background-color: #78af01;
            color: #fff;
            border-radius: 7px;
        }
    </style>
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
            <h2>Настройки дашброда</h2>

            <form method="POST" action="?action=save" class="adm-catalog" id="params-form">

                <?php
                $perms = [];
                foreach(Users::ROLES as $userRole){
                    $perms[$userRole] = array_flip(Settings::getPermissions($userRole));
                }
                echo getColsHTML(Settings::getAllCols(), $perms);
                ?>

                <div style="margin-top: 72px">
                    <button type="submit" class="submit_button">Сохранить</button>
                </div>
            </form>


        </div>
    </div>
    </div>


</body>

</html>