<?php

use models\cats\Cats;
use models\dicts\Dict;
use models\Models;
use models\Parts;
use models\parts\Arrivals;
use models\parts\NamesRef;
use models\Photos;
use models\User;
use program\core\App;

ini_set('display_errors', 'On');
error_reporting(E_ALL);

define('VER', 10);

require '_new-codebase/front/templates/main/parts/part/part.php';
require '_new-codebase/front/templates/main/parts/part/history.php';

if (!empty($_POST['ajax'])) {
    $response = [];
    switch ($_POST['ajax']) {
        case 'save':
            $response = Parts::save($_POST);
            break;
        case 'upload-photo':
            $res = Parts::uploadTmpPhoto();
            if ($res['path']) {
                $response['photo_html'] = getPhotoHTML($res['path']);
            } else {
                $response['message'] = $res['message'];
            }
            break;
        case 'rotate-photo':
            $response = Photos::rotatePhoto($_POST['photo_path'], $_POST['direction']);
            break;
        case 'save-cats-window':
            $response = Parts::saveModelCats($_POST);
            break;
        case 'get-cats-window':
            echo getCatsWindowHTML(Parts::getModelCats($_POST['part_id']));
            exit;
            break;
        case 'get-serials':
            $response = Parts::getSerials($_POST['model_id']);
            break;
        case 'get-all-serials-html':
            $response['serials_html'] = getSerialsHTML(Parts::getSerials($_POST['model_id']));
            break;
        default:
            $response = ['message' => 'Неверный тип запроса.', 'error_flag' => 1];
    }
    echo json_encode($response);
    exit;
}

if (!empty(App::$URLParams['id'])) {
    $part = Parts::getPartByID2(App::$URLParams['id']);
    $part['extra'] = (!empty($part['extra'])) ? json_decode($part['extra'], true) : [];
    $balance = Parts::getBalance(App::$URLParams['id']);
    $partModels = Parts::getModels(App::$URLParams['id']);
}

$depots = Parts::getDepots();
$groups = Parts::getGroups();
$vendors = Parts::getVendors();
$models = Models::getModels();
$reasons = Dict::getValues(1);
$names = NamesRef::getNames();

$title = (isset($part)) ? 'Редактировать запчасть' : 'Добавить запчасть';

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
    <link href="/_new-codebase/front/modules/part/part.css?v=<?= VER; ?>" rel="stylesheet" />
    <link href="/_new-codebase/front/modules/part/history.css?v=<?= VER; ?>" rel="stylesheet" />
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

        <h2><?= $title; ?></h2>

        <form action="?ajax=save" id="part-form" method="POST">
            <div class="container gutters">
                <div class="row">

                    <div class="col-12">
                        <h3 class="form__title">Наименование запчасти</h3>
                    </div>

                    <div class="col-6">
                        <div class="form__cell">
                            <label class="form__label part__label">Шаблон наименования запчасти:</label>
                            <select class="form__select select2" name="name_id" data-input="name-id">
                                <option value="">-- вариант не выбран --</option>
                                <?php
                                $cur = ($part['name_id']) ?? null;
                                foreach ($names as $name) {
                                    $selected = ($cur == $name['id']) ? 'selected' : '';
                                    echo '<option value="' . $name['id'] . '" ' . $selected . '>' . $name['ru'] . ' (' . $name['en'] . ')</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="form__cell">
                            <label class="form__label part__label" for="extra-name">Описание:</label>
                            <input type="text" name="extra[name]" id="extra-name" data-input="extra-name" value="<?= $part['extra']['name'] ?? ''; ?>" class="form__text">
                        </div>
                    </div>

                    <div class="col-4">
                        <div class="form__cell">
                            <label class="form__label part__label" for="extra-model-id">Модель:</label>
                            <select name="extra[model_id]" id="extra-model-id" class="form__select select2" data-input="extra-model-id">
                                <?= getOptionsHTML($models, $part['extra']['model_id'] ?? 0); ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-4">
                        <div class="form__cell">
                            <label class="form__label part__label" for="extra-cat-id">Категория:</label>
                            <select name="extra[cat_id]" id="extra-cat-id" class="form__select select2" data-input="extra-cat-id">
                                <?= getOptionsHTML(Cats::getCatsList(), $part['extra']['cat_id'] ?? 0); ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-4">
                        <div class="form__cell form__cell_flex" style="justify-content: center;">
                            <label class="form__label part__label">
                                <input type="checkbox" name="extra[is_counted]" value="1" <?= ((!empty($part['extra']['is_counted'])) ? 'checked' : ''); ?> data-input="extra-is-counted"> Посчитано
                            </label>
                        </div>
                    </div>

                    <div class="col-12">
                        <h3 class="form__title">Информация о запчасти</h3>
                    </div>

                    <div class="col-6">
                        <div class="form__cell">
                            <label class="form__label part__label" for="name">Наименование запчасти:</label>
                            <input type="text" readonly name="name" id="name" data-input="name" value="<?= (isset($part['name'])) ? $part['name'] : ''; ?>" class="form__text">
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="form__cell">
                            <label class="form__label part__label" for="name-1s">Наименование запчасти (1С):</label>
                            <input type="text" name="name_1s" id="name-1s" value="<?= (isset($part['name_1s'])) ? $part['name_1s'] : ''; ?>" class="form__text">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-3">
                        <div class="form__cell">
                            <label class="form__label part__label">Код запчасти:</label>
                            <input type="text" readonly value="<?= (isset($part['part_code'])) ? $part['part_code'] : ''; ?>" class="form__text">
                        </div>
                    </div>

                    <div class="col-3">
                        <div class="form__cell">
                            <label class="form__label part__label" for="type-id">Принадлежность:</label>
                            <select name="type_id" id="type-id" class="form__select">
                                <?= getOptionsHTML(Parts::$partTypes, (!isset($part['type_id'])) ? 0 : $part['type_id']); ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-3">
                        <div class="form__cell">
                            <label class="form__label part__label" for="attr-id">Признак запчасти:</label>
                            <select name="attr_id" id="attr-id" data-input="attr-id" class="form__select">
                                <?= getOptionsHTML(Parts::$partAttrs, (!isset($part['attr_id'])) ? 0 : $part['attr_id']); ?>
                            </select>
                            <a href="#" class="part__open-cats-window-link" data-action="open-cats-window" id="open-cats-window-link">Выбрать категории</a>
                        </div>
                    </div>

                    <div class="col-3">
                        <div class="form__cell">
                            <label class="form__label part__label" for="group-id">Группа запчастей:</label>
                            <select name="group_id" id="group-id" class="form__select select2">
                                <?= getOptionsHTML($groups, (!isset($part['group_id'])) ? 0 : $part['group_id']); ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-3">
                        <div class="form__cell">
                            <label class="form__label part__label" for="vendor-id">Производитель:</label>
                            <select name="vendor_id" id="vendor-id" class="form__select select2">
                                <?= getOptionsHTML($vendors, (!isset($part['vendor_id'])) ? 0 : $part['vendor_id']); ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-3">
                        <div class="form__cell">
                            <label class="form__label part__label" for="price">Цена:</label>
                            <input type="text" name="price" id="price" value="<?= (isset($part['price'])) ? (int)$part['price'] : ''; ?>" class="form__text">
                        </div>
                        <label class="form__label"><input type="checkbox" name="own_flag" value="1" <?= (!empty($part['own_flag'])) ? 'checked' : ''; ?>> Закупленная запчасть</label>
                    </div>

                    <div class="col-3">
                        <div class="form__cell">
                            <label class="form__label part__label" for="weight">Вес (кг):</label>
                            <input type="text" name="weight" id="weight" value="<?= (isset($part['weight'])) ? $part['weight'] : ''; ?>" class="form__text">
                        </div>
                    </div>

                    <div class="col-3">
                        <div class="form__cell">
                            <label class="form__label part__label" for="part-num">Партномер:</label>
                            <input type="text" name="part_num" id="part-num" value="<?= (isset($part['part_num'])) ? $part['part_num'] : ''; ?>" class="form__text">
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form__cell">
                            <label class="form__label part__label" for="description">Дополнительные параметры:</label>
                            <textarea name="description" id="description" class="form__text"><?= (isset($part['description'])) ? $part['description'] : ''; ?></textarea>
                        </div>
                    </div>
                </div>


                <div class="row">
                    <div class="col-12">
                        <?= getDepotsHTML(((isset($balance)) ? $balance : []), $depots); ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="form__cell">
                            <label class="form__label part__label">Фото:</label>
                            <p>После добавления/редактирования фото не забудьте сохранить страницу.</p>
                            <div class="photos" id="photos-container">
                                <?= getPhotosHTML((isset($part)) ? $part['photos'] : []); ?>
                            </div>
                        </div>
                    </div>
                </div>



                <div class="row">
                    <div class="col-12">
                        <h3 class="form__title">Приход/расход
                            <?php if (!empty($part['id'])) : ?>
                                <a href="/parts-log/?part=<?= $part['id']; ?>" target="_blank" class="history__link">Открыть историю запчасти</a>
                            <?php endif; ?>
                        </h3>
                    </div>

                    <div class="col-12">
                        <?= getHistoryHTML(); ?>
                    </div>
                </div>

                <div class="row">

                    <div class="col-12">
                        <h3 class="form__title">Модели</h3>
                    </div>

                    <!-- Models list -->
                    <?php
                    if (isset($partModels)) {
                        foreach ($partModels as $model) {
                            echo getModelHTML($model);
                        }
                    }
                    ?>
                    <!-- / Models list -->

                    <!-- Serial tpl -->
                    <div class="model__serial" id="serial-tpl" data-serial-id="">
                        <span data-serial-holder>Загрузка...</span>
                        <div class="model__del-btn model__del-btn_serial" data-action="del-serial" title="Удалить"></div>
                        <input type="hidden" name="serial_ids[]" value="" data-input="serial-id">
                    </div>
                    <!-- / Serial tpl -->

                    <!-- Depot tpl -->
                    <div class="row" id="depot-tpl" data-depot-id="">
                        <div class="col-5">
                            <div class="form__cell">
                                <select name="depot_id[]" class="form__select select2">
                                    <?= getOptionsHTML($depots); ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-5">
                            <div class="form__cell">
                                <input type="text" name="place[]" class="form__text" placeholder="Место хранения..." value="">
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="form__cell">
                                <input type="number" name="qty[]" min="0" class="form__text" placeholder="0" value="">
                                <div class="model__del-btn model__del-btn_depot" data-action="del-depot" title="Удалить"></div>
                            </div>
                        </div>
                    </div>
                    <!-- / Depot tpl -->

                    <?php historyTPL($reasons, $models, $depots, ((isset($balance)) ? $balance : []), Arrivals::getList()); ?>

                    <!-- Model tpl -->
                    <div class="col-12" id="model-tpl" data-model-id="">
                        <div class="form__cell">
                            <div class="model">
                                <div class="model__del-btn model__del-btn_model" data-action="del-model" title="Удалить"></div>
                                <div class="model__name">
                                    <select name="model_id" data-input="model" id="model-id" class="form__select select2">
                                        <?= getOptionsHTML($models); ?>
                                    </select>
                                </div>
                                <div class="model__serials" data-serials>
                                    <?= getSerialControlsHTML(); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- / Model tpl -->

                    <?php if (User::hasRole('admin', 'taker')) : ?>
                        <div class="col-12" data-trigger>
                            <div class="form__cell">
                                <div class="model__add-model-btn" data-action="add-model">+ Добавить модель</div>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>


                <div class="row">

                    <div class="col-12">
                        <div class="form__cell form__field_final">
                            <input type="hidden" name="del_photo_paths" id="del-photo-paths" value="">
                            <input type="hidden" name="del_model_ids" id="del-model-ids" value="">
                            <input type="hidden" name="del_serial_ids" id="del-serial-ids" value="">
                            <input type="hidden" name="del_depot_ids" id="del-depots-ids" value="">
                            <input type="hidden" name="part_id" id="part-id" value="<?= ((isset($part['id'])) ? $part['id'] : ''); ?>">
                            <input type="hidden" name="ajax" value="save">
                            <?php if (User::hasRole('admin', 'taker')) : ?>
                                <button type="submit" class="form__btn form__btn_w100">Сохранить</button>
                            <?php endif; ?>
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
    <script src="/_new-codebase/front/modules/part/part.js?v=<?= VER; ?>"></script>
    <script src="/_new-codebase/front/modules/part/history.js?v=<?= VER; ?>"></script>
</body>

</html>