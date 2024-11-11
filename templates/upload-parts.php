<?php

use models\Parts;
use models\User;
use program\core\App;

if (!empty(App::$URLParams['action'])) {
    switch (App::$URLParams['action']) {
        case 'import-excel':
            models\parts\ImportExcel::run();
            $_FILES = [];
            break;
        case 'export-excel':
            models\parts\ExportExcel::run();
            exit;
    }
}

$depots = Parts::getDepots();

?>

<!doctype html>
<html>

<head>
    <meta charset=utf-8>
    <title>Загрузка/выгрузка запчастей - Панель управления</title>
    <link href="/css/fonts.css" rel="stylesheet" />
    <link href="/css/style-without-forms.css" rel="stylesheet" />
    <link href="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.min.css" rel="stylesheet" />
    <link href="/_new-codebase/front/vendor/animate.min.css" rel="stylesheet" />
    <link href="/notifier/css/style.css" rel="stylesheet">

    <!-- New codebase -->
    <link href="/_new-codebase/front/vendor/select2/css/select2.min.css" rel="stylesheet">
    <link href="/_new-codebase/front/templates/main/css/form.css" rel="stylesheet" />
    <link href="/_new-codebase/front/templates/main/css/grid.css" rel="stylesheet" />
    <link href="/_new-codebase/front/templates/main/css/layout.css" rel="stylesheet" />

    <style>
        * {
            box-sizing: border-box;
        }

        ol {
            list-style: decimal;
            margin-bottom: 8px;
        }

        ol li {
            margin-bottom: 12px;
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

        <div class="container">
            <div class="row">

                <div class="col-5">
                    <h2>Выгрузка запчастей</h2>
                    <form action="?action=export-excel" method="POST" style="margin-top: 20px;">
                        <div class="form__cell">
                            <label class="form__label">Склад:</label>
                            <select class="select2 nomenu form__select" name="depot">
                                <option value="all">- все -</option>
                                <option value="service-only">- только СЦ -</option>
                                <?php
                                foreach ($depots as $dep) {
                                    echo '<option value="' . $dep['id'] . '">' . $dep['name'] . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form__sep" style="height: 30px"></div>
                        <div>
                            <button type="submit" class="form__btn">Выгрузить в Excel</button>
                        </div>
                    </form>
                </div>

                <div class="col-2"></div>

                <div class="col-5">
                    <h2>Загрузка запчастей</h2>
                    <form action="?action=import-excel#log" method="POST" enctype="multipart/form-data" style="margin-top: 20px;">
                        <div class="form__cell">
                            <label class="form__label"><input type="checkbox" name="change_num_flag" value="1"> Менять количество</label>
                        </div>
                        <div class="form__cell">
                            <label class="form__btn">Выбрать файл и загрузить <input type="file" id="excel-file" name="excel_file" style="display: none"></label>
                        </div>
                    </form>
                    <div>
                        <?php
                        if (models\parts\ImportExcel::$log) {
                            printLog(models\parts\ImportExcel::$log);
                        }
                        ?>
                    </div>
                </div>

            </div>
        </div>

    </main>

    <script src="/_new-codebase/front/vendor/jquery/jquery.min.js"></script>
    <script src='/_new-codebase/front/vendor/mustache/mustache.min.js'></script>
    <script src="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.concat.min.js"></script>
    <script src="/notifier/js/index.js"></script>

    <!-- New codebase -->
    <script src="/_new-codebase/front/vendor/select2/js/select2.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            $('.select2').select2();

            $('#excel-file').on('change', function() {
                this.closest('form').submit();
            });
        });
    </script>
</body>

</html>

<?php
function printLog(array $log)
{
?>
    <section>
        <ol>
            <?php
            foreach ($log as $message) {
                echo '<li>' . $message . '</li>';
            }
            ?>
        </ol>
    </section>
<?php
}
