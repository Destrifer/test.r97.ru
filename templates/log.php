<?php

use models\Log;
use models\Users;
use program\core\App;

define('VER', '1');

require '_new-codebase/front/templates/main/parts/form.php';
require '_new-codebase/front/templates/main/parts/log/table.php';

$users = Users::getUsers(['active' => true]);
$logs = Log::get(App::$URLParams);
$events = Log::getEvents();
?>

<!doctype html>
<html>

<head>
    <meta charset=utf-8>
    <title>Лог системы - Панель управления</title>
    <link href="/css/fonts.css" rel="stylesheet" />
    <link href="/css/style.css" rel="stylesheet" />
    <script src="/_new-codebase/front/vendor/jquery/jquery-1.7.2.min.js"></script>
    <script src="/js/main.js"></script>
    <script src="/notifier/js/index.js"></script>
    <link rel="stylesheet" type="text/css" href="/notifier/css/style.css">
    <link rel="stylesheet" href="/_new-codebase/front/vendor/animate.min.css" />
    <script src='/_new-codebase/front/vendor/mustache/mustache.min.js'></script>
    <script src="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.concat.min.js"></script>
    <link rel="stylesheet" href="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.min.css" />

    <!-- New codebase -->
    <link href="/_new-codebase/front/vendor/select2/css/select2.min.css" rel="stylesheet">
    <link href="/_new-codebase/front/templates/main/css/grid.css" rel="stylesheet">
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
            <h2>Лог системы</h2>

            <main class="log" style="margin-top:20px;">

                <form class="form log__filter-form">
                    <div class="container gutters">
                        <div class="row">
                            <div class="col-3">
                                <label>Категория</label>
                                <select class="form__select" name="cat" data-filter>
                                    <?php optionsHTML(Log::$cats, (isset(App::$URLParams['cat']) ? App::$URLParams['cat'] : '')); ?>
                                </select>
                            </div>
                            <div class="col-3">
                                <label>Объект</label>
                                <input type="text" class="form__text" name="object" value="<?= (isset(App::$URLParams['object']) ? App::$URLParams['object'] : ''); ?>" data-filter style="height:55px">
                            </div>
                            <div class="col-3">
                                <label>Действие</label>
                                <select class="form__select select2" name="event" data-filter>
                                    <?php optionsHTML($events, (isset(App::$URLParams['event']) ? App::$URLParams['event'] : '')); ?>
                                </select>
                            </div>
                            <div class="col-3">
                                <label>Пользователь</label>
                                <select class="form__select select2" name="user" data-filter>
                                    <?php optionsHTML($users, (isset(App::$URLParams['user']) ? App::$URLParams['user'] : '')); ?>
                                </select>
                            </div>
                            <div class="col-12 log__filter-btns">
                                <button class="form__btn form__btn_secondary log__filter-btn" type="button" data-action="reset">Сброс</button>
                                <button class="form__btn log__filter-btn" type="button" data-action="apply">Применить</button>
                            </div>
                        </div>
                    </div>
                </form>

                <table class="log__table">
                    <thead>
                        <tr>
                            <th class="log__td-date">Дата</th>
                            <th class="log__td-cat">Категория</th>
                            <th class="log__td-object">Объект</th>
                            <th class="log__td-operation">Действие</th>
                            <th class="log__td-message">Сообщение</th>
                            <th class="log__td-user">Пользователь</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (!$logs) {
                            emptyRowHTML();
                        } else {
                            foreach ($logs as $row) {
                                dataRowHTML($row);
                            }
                        }
                        ?>
                    </tbody>
                </table>


            </main>


        </div>
    </div>
    </div>

    <!-- New codebase -->
    <script src="/_new-codebase/front/vendor/select2/js/select2.min.js"></script>
    <script src="/_new-codebase/front/modules/log/log.js"></script>
</body>

</html>

<?php
function dataRowHTML(array $row)
{
    echo '<tr>
              <td>' . $row['date'] . '</td>
              <td>' . $row['cat'] . '</td>
              <td><a href="' . $row['object']['url'] . '" target="_blank">' . $row['object']['name'] . '</a></td>
              <td>' . $row['event'] . '</td>
              <td>' . $row['message'] . '</td>
              <td>' . $row['user'] . '</td>   
         </tr>';
}
