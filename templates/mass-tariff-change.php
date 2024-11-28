<?php

use models\Users;
use program\core;
use program\core\App;

// Вывод содержимого $_GET
echo '<pre>';
print_r($_GET);
echo '</pre>';

// Вывод содержимого $_POST
echo '<pre>';
print_r($_POST);
echo '</pre>';

if (isset(core\App::$URLParams['action'])) {

	switch (core\App::$URLParams['action']) {
			case 'save-service-form':
					if (!empty($_POST['service_id']) && !empty($_POST['tariff_id'])) {
							// Получаем результат вызова метода
							$result = models\Tariffs::massChangeTariff($_POST['service_id'], $_POST['tariff_id']);
							
							// Выводим результат, если он есть
							echo '<pre>';
							print_r($result);  // Или var_dump($result), если нужно больше информации
							echo '</pre>';
					} else {
							// Если параметры не переданы, выводим сообщение
							echo "Ошибка: service_id и tariff_id должны быть переданы в POST.";
					}
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

?>
<!doctype html>
<html>

<head>
    <meta charset=utf-8>
    <title>Массово поменять тариф - Панель управления</title>
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
            margin: 16px 0 24px 0;
        }

        .service-col {
            display: inline-block;
            width: 31%;
            margin: 16px 0;
            padding-right: 24px;
        }

        .service-tariff {
            font-size: .8em;
            color: #818181;
            padding-left: 32px;
            font-weight: 300;
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
            <h2>Массово поменять тариф</h2>

            <div class="adm-catalog">

                <div class="add">
                    <a style="width: auto;padding-left: 7px;padding-right: 7px;" href="/prices/" class="button">Тарифы</a>
                </div>
                <br>

                <form action="?action=save-service-form" method="POST">
                    <h3 style="font-size: 21px;font-weight: 300;margin: 32px 0">Показать:
                        <?php
                        $t = (empty(App::$URLParams['tariff'])) ? 0 : App::$URLParams['tariff'];
                        echo (!$t) ? '<b>все</b>' : '<a href="/mass-tariff-change/">все</a>';
                        echo ' / ';
                        echo ($t == 2) ? '<b>тариф 2018</b>' : '<a href="/mass-tariff-change/?tariff=2">тариф 2018</a>';
                        echo ' / ';
                        echo ($t == 1) ? '<b>тариф 2022</b>' : '<a href="/mass-tariff-change/?tariff=1">тариф 2022</a>';
                        echo ' / ';
                        echo ($t == 3) ? '<b>тариф 2023</b>' : '<a href="/mass-tariff-change/?tariff=3">тариф 2023</a>';
                        ?>
                    </h3>
                    <h3 style="font-size: 21px;font-weight: 300;margin-bottom: 16px;">
                        Выберите новый тариф:
                        <select class="nomenu" name="tariff_id" style="margin-bottom: 24px;">
                            <option value="2">Тариф 2018</option>
                            <option value="1">Тариф 2022</option>
                            <option value="3">Тариф 2023</option>
                        </select>
                    </h3>
                    <div style="margin-top: 32px;font-weight: 600"><label><input type="checkbox" data-check-all-flags> Выделить все</label></div>
                    <div>
                        <?php
                        echo getServicesHTML();
                        ?>
                    </div>
                    <div style="margin-top: 32px; margin-bottom: 32px">
                        <button type="submit" style="padding: 0 72px;">Сменить тариф</button>
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
    $tariffs = [1 => 'Тариф 2022', 2 => 'Тариф 2018', 3 => 'Тариф 2023'];
    $html = '<div class="service-col">';
    $tariffWhere = (empty(App::$URLParams['tariff'])) ? '' : ' AND r.`tariff_id` = ' . App::$URLParams['tariff'];
    $sql = mysqli_query($db, 'SELECT r.`user_id`, r.`name`, r.`tariff_id` FROM `requests` r LEFT JOIN `'.Users::TABLE.'` u ON u.`id` = r.`user_id` WHERE r.`mod` = 1 ' . $tariffWhere . ' AND u.`role_id` IN (3,4) AND u.`status_id` = 1 ORDER BY r.`name`;');
    $n = 0;
    while ($row = mysqli_fetch_assoc($sql)) {
        if ($n == 12) {
            $html .= '</div><div class="service-col">';
            $n = 0;
        }
        $html .= '<label class="service-row"><input data-check-flag="" type="checkbox" name="service_id[]" value="' . $row['user_id'] . '"> ' . $row['name'] . ' <br><span class="service-tariff" title="Текущий тариф">' . $tariffs[$row['tariff_id']] . '</span></label>';
        $n++;
    }
    $html .= '</div>';
    return '<div class="service">' . $html . '</div>';
}
