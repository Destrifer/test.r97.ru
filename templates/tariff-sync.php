<?php

use program\core;

if (isset(core\App::$URLParams['action'])) {

    switch (core\App::$URLParams['action']) {
        case 'save-service-form':
            if (!empty($_POST['service_id'])) {
                models\Tariffs::sychTariff($_POST['service_id']);
            }
            header('Location: /prices/');
            exit;
            break;
    }
}

function check_cats()
{
    global $db;

    if (\models\User::hasRole('admin')) {

        $sql = mysqli_query($db, 'SELECT * FROM `cats` where `service` = 1;');
        while ($row = mysqli_fetch_array($sql)) {
            $count_prices = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `prices` where `cat_id` = ' . $row['id']));
            if ($count_prices['COUNT(*)'] == 0) {
                mysqli_query($db, 'INSERT INTO `prices` (
            `cat_id`
            ) VALUES (
            \'' . mysqli_real_escape_string($db, $row['id']) . '\'
            );') or mysqli_error($db);
            }
        }

        $sql = mysqli_query($db, 'SELECT * FROM `prices`;');
        while ($row = mysqli_fetch_array($sql)) {
            $count_service = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `cats` where `service` = 1 and `id` = ' . $row['cat_id']));
            if ($count_service['COUNT(*)'] == 0) {
                mysqli_query($db, 'DELETE FROM `prices` WHERE `id` = ' . $row['id'] . ' LIMIT 1;') or mysqli_error($db);
            }
        }
    }
}

check_cats();



function content_list()
{

}

function cat($id)
{
    global $db;
    $sql = mysqli_query($db, 'SELECT * FROM `cats` where `id` = \'' . $id . '\' LIMIT 1;');
    while ($row = mysqli_fetch_array($sql)) {
        $content = $row;
    }
    return $content;
}

?>
<!doctype html>
<html>

<head>
    <meta charset=utf-8>
    <title>Синхронизировать тарифы - Панель управления</title>
    <link href="/css/fonts.css" rel="stylesheet" />
    <link href="/css/style.css" rel="stylesheet" />
    <script src="/_new-codebase/front/vendor/jquery/jquery-1.7.2.min.js"></script>
    <script src="/js/jquery-ui.min.js"></script>
    <script src="/js/jquery.placeholder.min.js"></script>
    <script src="/js/jquery.formstyler.min.js"></script>
    <script src="/js/main.js"></script>

    <script src="/notifier/js/index.js"></script>
    <link rel="stylesheet" href="/notifier/css/style.css">
    <link rel="stylesheet" href="/_new-codebase/front/vendor/animate.min.css" />
    <script src='/_new-codebase/front/vendor/mustache/mustache.min.js'></script>
    <script src="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.concat.min.js"></script>
    <link rel="stylesheet" href="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.min.css" />
    <style>
        .service {
            display: flex;
            flex-wrap: wrap;
        }

        .service-row {
            display: block;
            margin: 16px 0;
        }

        .service-col {
            display: inline-block;
            width: 31%;
            margin: 16px 0;
            padding-right: 24px;
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
            <h2>Синхронизировать тарифы</h2>

            <div class="adm-catalog">

                <div class="add">
                    <a style="width: auto;padding-left: 7px;padding-right: 7px;" href="/prices/" class="button">Тарифы</a>
                </div>
                <br>

                <form action="?action=save-service-form" method="POST">
                    <h3 style="font-size: 21px;font-weight: 300;">Выберите СЦ для синхронизации тарифов:</h3>
                    <div style="margin-top: 32px;font-weight: 600"><label><input type="checkbox" data-check-all-flags> Выделить все</label></div>
                    <div>
                        <?php
                        echo getServicesHTML();
                        ?>
                    </div>
                    <div style="margin-top: 32px; margin-bottom: 32px">
                        <button type="submit" style="padding: 0 72px;">Синхронизировать</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            $('[data-check-all-flags]').on('change', function() {
                $('[data-check-flag]').attr('checked', this.checked).trigger('refresh');
            });
        });
    </script>
</body>

</html>


<?php

function getServicesHTML()
{
    global $db;
    $html = '<div class="service-col">';
    $sql = mysqli_query($db, 'SELECT r.`user_id`, r.`name` FROM `requests` r LEFT JOIN `users` u ON u.`id` = r.`user_id` WHERE r.`mod` = 1 AND u.`role_id` = 3 AND u.`status_id` = 1 ORDER BY r.`name`;');
    $n = 0;
    while ($row = mysqli_fetch_assoc($sql)) {
        if ($n == 12) {
            $html .= '</div><div class="service-col">';
            $n = 0;
        }
        $html .= '<label class="service-row"><input data-check-flag="" type="checkbox" name="service_id[]" value="' . $row['user_id'] . '"> ' . $row['name'] . '</label>';
        $n++;
    }
    $html .= '</div>';
    return '<div class="service">' . $html . '</div>';
}
