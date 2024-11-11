<?php

use models\dicts\Dict;
use models\Parts;
use models\parts\Arrivals;

ini_set('display_errors', 'On');
error_reporting(E_ALL);

define('VER', 1);

require '_new-codebase/front/templates/main/parts/part/part.php';
require '_new-codebase/front/templates/main/parts/common.php';

if (!empty($_POST['ajax'])) {
    $response = [];
    switch ($_POST['ajax']) {

        case 'save':
            $response = Arrivals::save($_POST);
            break;

        default:
            $response = ['message' => 'Неверный тип запроса.', 'error_flag' => 1];
    }
    echo json_encode($response);
    exit;
}

$parts = Parts::getParts(['del_flag' => 0, 'user_flag' => false]);
for ($i = 0, $cnt = count($parts); $i < $cnt; $i++) {
    $defaultModel = Parts::getDefaultModel($parts[$i]['id']);
    if ($defaultModel) {
        $d = [$parts[$i]['part_code'], $defaultModel['model'], $defaultModel['provider'], $defaultModel['order']];
    } else {
        $d = [$parts[$i]['part_code']];
    }
    $parts[$i]['name'] .= '::' . trim(implode(', ', array_filter($d)), ' ,');
}
$depots = Parts::getDepots();
$reasons = Dict::getValues(1);
$arrivals = Arrivals::getList();

$title = 'Добавить приход';

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
    <link href="/_new-codebase/front/vendor/fancybox/jquery.fancybox.min.css" rel="stylesheet" />
    <link href="/_new-codebase/front/vendor/air-datepicker/css/datepicker.min.css?v=<?= VER; ?>" rel="stylesheet" />
    <link href="/_new-codebase/front/vendor/select2/css/select2.min.css" rel="stylesheet">
    <link href="/_new-codebase/front/templates/main/css/form.css?v=<?= VER; ?>" rel="stylesheet" />
    <link href="/_new-codebase/front/templates/main/css/grid.css?v=<?= VER; ?>" rel="stylesheet" />
    <link href="/_new-codebase/front/templates/main/css/layout.css?v=<?= VER; ?>" rel="stylesheet" />
    <link href="/_new-codebase/front/templates/main/css/sec-nav.css?v=<?= VER; ?>" rel="stylesheet" />
    <link href="/_new-codebase/front/modules/part/part.css?v=<?= VER; ?>" rel="stylesheet" />
    <link href="/_new-codebase/front/modules/part/history.css?v=<?= VER; ?>" rel="stylesheet" />
    <style>
        * {
            box-sizing: border-box;
        }

        /* Select2 option style */
        .s2-name {
            font-weight: 600;
            display: block;
        }

        .s2-info {
            font-size: .9em;
            display: block;
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

        <section class="layout__mb_md">
            <?php
            secNavHTML([
                ['name' => 'Все приходы', 'url' => '/parts-arrivals/']
            ]);
            ?>
        </section>

        <h2><?= $title; ?></h2>

        <form action="?ajax=save" id="part-arrival-form" method="POST">
            <div class="container gutters">

                <div class="row">
                    <div class="col-12">
                        <h3 class="form__title">Приход</h3>
                    </div>

                    <div class="col-4">
                        <div class="form__cell">
                            <label class="form__label part__label">№ Прихода:</label>
                            <input type="text" required value="" list="arrivals-list" name="arrival_name" class="form__text">
                            <?php
                            echo '<datalist id="arrivals-list">';
                            foreach ($arrivals as $arrival) {
                                echo '<option value="' . $arrival['name'] . '"></option>';
                            }
                            echo '</datalist>';
                            ?>
                        </div>
                    </div>

                    <div class="col-4">
                        <div class="form__cell">
                            <label class="form__label part__label">Склад:</label>
                            <select name="depot_id" class="select2 form__select">
                                <?= getOptionsHTML($depots); ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-4">
                        <div class="form__cell">
                            <label class="form__label part__label">Дата и время:</label>
                            <input type="text" required name="add_date" class="form__text" value="<?= date('d.m.Y H:i'); ?>" data-datetimepicker>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <h3 class="form__title">Список запчастей</h3>
                    </div>
                </div>

                <div class="row" id="part-tpl" data-part-row>
                    <div class="col-8">
                        <div class="form__cell">
                            <label class="form__label part__label">Запчасть:</label>
                            <select name="part_id[]" class="select2-tpl form__select">
                                <?= getOptionsHTML($parts); ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-4">
                        <div class="form__cell">
                            <label class="form__label part__label">Количество:</label>
                            <input type="number" min="1" required name="part_num[]" class="form__text" value="1">
                        </div>
                        <div class="del-btn-round" data-action="del-part" style="top: 20px;" title="Удалить" hidden></div>
                    </div>
                </div>

                <div class="row" id="triggers">
                    <div class="col-4">
                        <div class="form__cell">
                            <div class="history__btn history__btn_in" data-action="add-part">Добавить запчасть</div>
                        </div>
                    </div>
                </div>

                <div class="row">

                    <div class="col-12">
                        <div class="form__cell form__field_final">
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

    </main>

    <script src="/_new-codebase/front/vendor/jquery/jquery.min.js"></script>
    <script src="/_new-codebase/front/vendor/fancybox/jquery.fancybox.min.js"></script>
    <script src='/_new-codebase/front/vendor/mustache/mustache.min.js'></script>
    <script src="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.concat.min.js"></script>
    <script src="/notifier/js/index.js"></script>

    <!-- New codebase -->
    <script src="/_new-codebase/front/vendor/select2/js/select2.min.js?v=<?= VER; ?>"></script>
    <script src="/_new-codebase/front/vendor/air-datepicker/js/datepicker.min.js?v=<?= VER; ?>"></script>
    <script src="/_new-codebase/front/modules/part-arrival/part-arrival.js?v=<?= VER; ?>"></script>
</body>

</html>