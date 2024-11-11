<?php


require '_new-codebase/front/templates/main/parts/repair-card/repair-card.php';
require '_new-codebase/front/templates/main/parts/repair-card/repair.php';
require '_new-codebase/front/templates/main/parts/dashboard/ui.php';
require '_new-codebase/front/templates/main/parts/approve-form.php';

use models\cats\Cats;
use models\Clients;
use program\core;
use models\User;
use models\dashboard\UI;
use models\repair\Work;

ini_set('display_errors', 'On');
error_reporting(E_ALL);

define('VER', 21);

if (!empty(core\App::$URLParams['ajax'])) {
    switch (core\App::$URLParams['ajax']) {

        case 'check-part-balance': // проверяет, есть ли запчасть на главном/ИП Кулиджанов складе (для мастеров)
            $res = ['has_part' => models\repaircard\Repair::mainDepotHasPart($_POST['part_id'])];
            break;

        case 'get-repair-types':
            $p = models\repaircard\Repair::getRepairTypes($_POST['problem_id']);
            $res = ['html' => getRepairTypeOptionsHTML(filterRepairTypesByType($p, $_POST['part_type_id']), $_POST['problem_id'])];
            break;

        case 'save-form':
            try {
                $res = models\repaircard\Repair::save(core\App::$URL[1]);
                $repair = models\Repair::getRepairByID(core\App::$URL[1]); // возврат цены и типа ремонта в форму для показа
                $res['repair_type'] = $repair['repair_type'] ?? '';
                $res['total_price'] = $repair['total_price'] ?? 0;
            } catch (Exception $e) {
                $res = ['message' => $e->getMessage()];
            }
            break;

        default:
            $res = ['error' => 'Неверный запрос.'];
            break;
    }
    exit(json_encode($res));
}

photoRedir(core\App::$URL[1]);
if (User::hasRole('service')) {
    models\Counters::delete('approved', core\App::$URL[1], User::getData('id'));
}
disable_notice('/edit-repair/' . core\App::$URL[1] . '/step/2/', User::getData('id'));

try {
    $repair = models\Repair::getRepairByID(core\App::$URL[1]);
} catch (Exception $e) {
    exit('<p>Ремонт не найден. <a href="/">Перейти на главную</a></p>');
}
$cat = Cats::getCats(['id' => $repair['cat_id']]);
$masters = models\repaircard\Repair::getMasters($repair['service_id']);
$issues = models\repaircard\Repair::getIssues();
$work = models\repair\Work::getRepairWorkByID(core\App::$URL[1]);
$problems = models\repaircard\Repair::getProblems();
if ($repair['refuse_doc_flag'] == 'y') {
    $problems['nonrepair'] = filterProblemsByRefuseDoc($problems['nonrepair']);
}
$parts = models\repaircard\Repair::getParts($repair, $work);
$repairTypes = models\repaircard\Repair::getRepairTypes();
$repairFinal = models\repaircard\Repair::getRepairFinal();
$backURL = (!empty($_COOKIE['dashboard:tab'])) ? '/dashboard/?tab=' . $_COOKIE['dashboard:tab'] : '/dashboard/';
$blockedFlag = models\repaircard\Repair::isBlocked($repair);
if ($repair['client_id'] && User::hasRole('admin', 'slave-admin', 'master', 'taker')) {
    $client = Clients::getClientByID($repair['client_id']);
    if ($client['scenario_id'] && isset(Clients::$scenario[$client['scenario_id']])) {
        $scenarioMessage = Clients::$scenario[$client['scenario_id']];
    }
}
?>
<!doctype html>
<html>

<head>
    <meta charset=utf-8>
    <title>Ремонт - Карточка ремонта</title>
    <link href="/css/fonts.css" rel="stylesheet" />
    <link href="/css/style-without-forms.css?v=1.00" rel="stylesheet" />
    <link rel="stylesheet" href="/notifier/css/style.css">
    <link rel="stylesheet" href="/_new-codebase/front/vendor/animate.min.css" />
    <link rel="stylesheet" href="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.min.css" />
    <link rel="stylesheet" href="/_new-codebase/front/vendor/fancybox/jquery.fancybox.min.css" />

    <!-- New codebase -->
    <style>
        * {
            box-sizing: border-box;
        }
    </style>
    <link href="/_new-codebase/front/modules/dashboard/css/ui.css" rel="stylesheet" />
    <link href="/_new-codebase/front/vendor/air-datepicker/css/datepicker.min.css" rel="stylesheet" />
    <link href="/_new-codebase/front/vendor/select2/css/select2.min.css" rel="stylesheet" />
    <link href="/_new-codebase/front/templates/main/css/grid.css" rel="stylesheet" />
    <link href="/_new-codebase/front/templates/main/css/form.css?v=<?= VER; ?>" rel="stylesheet" />
    <link href="/_new-codebase/front/templates/main/css/repair-card/repair-card.css?v=<?= VER; ?>" rel="stylesheet" />
    <link href="/_new-codebase/front/templates/main/css/repair-card/repair/repair.css?v=<?= VER; ?>" rel="stylesheet" />
    <link href="/_new-codebase/front/templates/main/css/repair-card/save-parts-window.css?v=<?= VER; ?>" rel="stylesheet" />
    <link href="/_new-codebase/front/templates/main/css/notice.css?v=<?= VER; ?>" rel="stylesheet" />
    <link href="/_new-codebase/front/templates/main/css/repair-card/approve-form.css?v=<?= VER; ?>" rel="stylesheet">
    <script src='/_new-codebase/front/vendor/jquery/jquery.min.js'></script>
    <script src='/js/main.js'></script>
    <!-- Aside controls -->
    <link href="/_new-codebase/front/components/aside-controls/css/aside-controls.css" rel="stylesheet" />
</head>

<body>
    <?php
    if ($repair['status_admin'] == 'Есть вопросы' && models\User::hasRole('service')) {
        echo '<div class="top-message top-message_alert" style="text-align:center">Пожалуйста, внесите исправления в карточку и отправьте на проверку.</div>';
    }
    ?>

    <div id="user-data-json" style="display: none"><?= json_encode(['id' => models\User::getData('id'), 'role' => models\User::getData('role')]); ?></div>

    <div class="viewport-wrapper">

        <div class="site-header">
            <div class="wrapper">

                <div class="logo">
                    <a href="/dashboard/"><img src="<?= $config['url']; ?>i/logo.png" alt="" /></a>
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

        <div class="wrapper" style="max-width: 1280px">

            <?= top_menu_admin(); ?>

            <div class="adm-tab">

                <?= getSummaryHTML(models\RepairCard::getSummary($repair['id'])); ?>

                <?= menu_dash(); ?>

            </div><!-- .adm-tab -->
            <br>
            <!-- Меню вкладок -->
            <section class="layout__mb_md">
                <?= getTabsHTML(UI::getTabs(User::getData('role'))); ?>
            </section>
            <h2>Процессинг</h2>

            <?php
            $stepsNavHTML = getStepsNavHTML(\models\RepairCard::getStepsNav($repair['id'], 'repair'));
            echo $stepsNavHTML;
            ?>

            <form id="repair-form" method="POST" class="repair_form repair-card">
                <div class="container gutters">
                    <div class="row">

                        <?php if (!empty($scenarioMessage)) : ?>
                            <div class="col-12">
                                <div class="form__cell" style="text-align: center">
                                    <b>План ремонта:
                                        <span style="color: red"><?= $scenarioMessage; ?></span>
                                        <?php if (!empty($repair['anrp_number'])) : ?>
                                            <span class="form__notice" style="margin-left: 16px"><a target="_blank" href="/edit-repair/<?= $repair['anrp_number']; ?>/step/2/">АНРП №<?= $repair['anrp_number']; ?></a></span>
                                        <?php endif; ?>
                                    </b>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="col-12 col-sm-8">
                            <div class="form__cell">
                                <label class="form__label" for="master-id">Мастер:</label>
                                <select name="master_id" data-input="master-id" class="form__select">
                                    <?php
                                    $masterID = ($repair['service_id'] == 33) ? $repair['master_user_id'] : $repair['master_id'];
                                    echo getMastersOptionsHTML($masters, $masterID, ($repair['service_id'] == 33), User::getData('role'));
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-sm-4">
                            <div class="form__cell">
                                <label class="form__label" for="start-date">Дата начала ремонта:</label>
                                <input type="text" value="<?= $repair['begin_date']; ?>" name="begin_date" id="start-date" data-input-filter="date" data-input-filter-max-date="today" placeholder="дд.мм.гггг" data-datepicker value="" class="form__text">
                            </div>
                        </div>

                        <div class="col-12 col-sm-8">
                            <div class="form__cell">
                                <label class="form__label" for="defect-client">Неисправность со слов клиента:</label>
                                <input type="text" name="defect_client" id="defect-client" value="<?= $repair['bugs']; ?>" class="form__text">
                            </div>
                        </div>

                        <div class="col-12 col-sm-4">
                            <div class="form__cell">
                                <label class="form__label">Закончить до:</label>
                                <input type="text" readonly value="<?= $repair['deadline_date']; ?>" class="form__text">
                            </div>
                        </div>

                        <div class="col-12 col-sm-8">
                            <div class="form__cell">
                                <label class="form__label" for="defect-actual">Выявлено мастером:</label>
                                <div data-error-target="defect_actual">
                                    <select name="defect_actual" class="select2" style="width:100%" id="defect-actual" class="form__select">
                                        <?= getIssuesOptionsHTML($issues, $repair['disease']); ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-4">
                            <div class="form__cell">
                                <label class="form__label">Дата окончания ремонта:</label>
                                <input type="text" readonly value="<?= $repair['finish_date']; ?>" class="form__text">
                            </div>
                        </div>

                        <?php if (User::hasRole('admin')) : ?>
                            <div class="col-12 col-sm-4">
                                <div class="form__cell">
                                    <label class="form__label">Тип тарифа:</label>
                                    <input type="text" readonly value="<?= $repair['repair_type']; ?>" id="repair-type" class="form__text">
                                </div>
                            </div>

                            <div class="col-12 col-sm-4">
                                <div class="form__cell">
                                    <label class="form__label">Стоимость без З/Ч:</label>
                                    <input type="text" readonly value="<?= $repair['total_price']; ?>" id="total-price" class="form__text">
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>


                    <div class="row">

                        <div class="col-12">
                            <h3 class="form__title form__title_center">Выберите действие</h3>
                        </div>

                        <div class="col-12">
                            <div class="form__cell-panel form__cell-panel_center" data-error-target="work">
                                <?php if ($repair['refuse_doc_flag'] != 'y') : ?>
                                    <div class="form__cell-panel-item">
                                        <div class="repair__add-work-btn" title="Выбирайте при заказе запчасти и любом ремонте, кроме ремонта пультов." data-action="add-repair">Ремонт</div>
                                    </div>
                                <?php endif; ?>
                                <?php if (!User::hasRole('service') || $repair['refuse_doc_flag'] == 'y') : ?>
                                    <div class="form__cell-panel-item">
                                        <div class="repair__add-work-btn" title="Выбирайте только при отказе клиента от гарантийного ремонта." data-action="add-nonrepair">Без ремонта</div>
                                    </div>
                                <?php endif; ?>
                                <?php if ($repair['refuse_doc_flag'] != 'y') : ?>
                                    <div class="form__cell-panel-item">
                                        <div class="repair__add-work-btn" title="Выбирайте при заказе запчасти, отсутствии дефекта, отказе в гарантии, либо ремонте пультов." data-action="add-diag">
                                            <div>Тестирование <br>
                                                <span class="repair__add-work-capt">(В том числе замена или ремонт аксессуара.)</span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <section id="blocks-container">
                            <?php
                            $workTpl = Work::getEmpty();
                            echo getRepairWorkHTML($workTpl, $problems['repair'], $repairTypes, $parts);
                            echo getNonRepairWorkHTML($workTpl, $problems['nonrepair'], $repairTypes, $parts);
                            echo getDiagWorkHTML($workTpl, $problems['diag'], $repairTypes, $parts);
                            foreach ($work as $w) {
                                if ($w['part_block_type'] == 'repair') {
                                    echo getRepairWorkHTML($w, $problems['repair'], $repairTypes, $parts);
                                } elseif ($w['part_block_type'] == 'diag') {
                                    echo getDiagWorkHTML($w, $problems['diag'], $repairTypes, $parts);
                                } else {
                                    echo getNonRepairWorkHTML($w, $problems['nonrepair'], $repairTypes, $parts);
                                }
                            }
                            ?>
                        </section>
                    </div>

                    <?php
                    if (!empty($cat['install_flag']) && $repair['install_status']) : ?>
                        <div class="row" id="approve-install-section" style="display: none">
                            <div class="col-6">
                                <div class="form__cell">
                                    <div class="form__flags-section">
                                        <label class="form__flag"><input type="checkbox" name="install_flag" value="1" <?= ($repair['install_status'] == 2) ? 'checked' : ''; ?> class="form__checkbox"> Нужен монтаж</label>
                                    </div>
                                </div>
                            </div>

                            <?php if (User::hasRole('admin') && $repair['install_status'] >= 2) : ?>
                                <div class="col-12">
                                    <div class="form__cell">
                                        <?php approveFormHTML('Подтверждение монтажа', $repair['install_status'] == 3, $repair['id'], 'set-install-approved-status'); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-12">
                            <div class="form__cell">
                                <label class="form__label" for="comment">Фактическая неисправность после дефектовки мастером (своими словами):</label>
                                <input type="text" name="comment" id="comment" value="<?= $repair['comment']; ?>" class="form__text">
                            </div>
                        </div>

                        <?php if (User::hasRole('admin', 'service')) : ?>

                            <?php if (!empty($repair['master_notes'])) : ?>
                                <div class="col-6">
                                    <div class="form__cell">
                                        <label class="form__label">Примечания мастера:</label>
                                        <textarea name="master_notes" readonly class="form__text" style="height: 166px;"><?= $repair['master_notes']; ?></textarea>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($repair['repair_final'])) : ?>
                                <div class="col-6">
                                    <div class="form__cell">
                                        <label for="repair-final" class="form__label">Итоги ремонта:</label>
                                        <div class="form__flags-section" id="repair-final">
                                            <?php
                                            foreach ($repairFinal as $id => $name) {
                                                if ($id == $repair['repair_final']) {
                                                    echo '<label class="form__flag">' . $name . '</label>';
                                                }
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                        <?php else : ?>

                            <div class="col-6">
                                <div class="form__cell">
                                    <label class="form__label">Примечания мастера:</label>
                                    <textarea name="master_notes" class="form__text" style="height: 166px;"><?= $repair['master_notes']; ?></textarea>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="form__cell">
                                    <label for="repair-final" class="form__label">Итоги ремонта:</label>
                                    <div class="form__flags-section" id="repair-final">
                                        <?php foreach ($repairFinal as $id => $name) : ?>
                                            <label class="form__flag"><input type="checkbox" <?= (($id == $repair['repair_final']) ? 'checked' : ''); ?> name="repair_final" data-input="repair-final" value="<?= $id; ?>" class="form__checkbox"> <?= $name; ?></label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="col-12">
                            <div class="form__cell">
                                <label class="form__label" style="font-size:.9em; color:red">Если дефект не был выявлен, либо был отказ в гарантии, то в поле ниже необходимо внести пояснение или причину для клиента, которые отобразятся в акте:</label>
                                <input type="text" name="cancel_reason" value="<?= $repair['repair_final_cancel']; ?>" class="form__text">
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="install_status" value="<?= $repair['install_status']; ?>">
                    <input type="hidden" name="repair_type_id" value="<?= $repair['repair_type_id']; ?>">
                    <input type="hidden" name="service_id" value="<?= $repair['service_id']; ?>">
                    <input type="hidden" name="model_id" value="<?= $repair['model_id']; ?>">
                    <input type="hidden" name="status" value="<?= $repair['status_admin']; ?>">

                    <?php if (!$blockedFlag) : ?>
                        <div class="row">

                            <div class="col-12">
                                <div class="form__cell repair-card__controls repair-card__controls_submit">
                                    <button class="form__btn" data-action="save-and-close">Сохранить и закрыть</button>
                                    <button type="submit" class="form__btn">Сохранить</button>
                                </div>
                                <div class="form__cell">
                                    <div class="form__notif" id="form-notif" style="display:none">Пожалуйста, исправьте ошибки в форме.</div>
                                </div>
                            </div>

                        </div>
                    <?php elseif (in_array($repair['status'], ['Запрос на монтаж', 'Запрос на демонтаж', 'Запрос на выезд'])) : ?>
                        <div class="row">
                            <div class="col-12">
                                <div class="form__cell repair-card__controls repair-card__controls_submit" style="font-weight: 600; justify-content:center">
                                    <div class="notice notice__alert">
                                        Ремонт находится в статусе "<?= $repair['status']; ?>", редактирование невозможно, <br> пока администратор не обработает запрос.
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </form>


            <?php
            echo $stepsNavHTML;
            ?>
        </div>
    </div>
    </div>
    <script src='/_new-codebase/front/vendor/mustache/mustache.min.js'></script>
    <script src="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.concat.min.js"></script>
    <script src="/notifier/js/index.js"></script>

    <script>
        let $repairForm,
            $formNotif,
            repairData,
            userData;

        $(document).ready(function() {
            $repairForm = $('#repair-form');
            $formNotif = $('#form-notif');
            repairData = JSON.parse($('#repair-data-json').text());
            userData = JSON.parse($('#user-data-json').text());
        });
    </script>

    <!-- New codebase -->
    <script src="/_new-codebase/front/vendor/fancybox/jquery.fancybox.min.js"></script>
    <script src="/_new-codebase/front/vendor/air-datepicker/js/datepicker.min.js"></script>
    <script src="/_new-codebase/front/vendor/select2/js/select2-mod.js"></script>
    <script src='/_new-codebase/front/components/repair-card/repair-card-new.js?v=<?= VER; ?>'></script>
    <script src='/_new-codebase/front/modules/repair-card/repair/repair.js?v=<?= VER; ?>'></script>
    <script src='/_new-codebase/front/components/input-filter.js?v=<?= VER; ?>'></script>
    <script src="/_new-codebase/front/components/status/status.js?v=<?= VER; ?>"></script>
    <script src="/_new-codebase/front/templates/main/js/approve-form.js?v=<?= VER; ?>"></script>
    <!-- Aside controls -->
    <script src="/_new-codebase/front/components/request.js?v=<?= VER; ?>"></script>
    <script src="/_new-codebase/front/components/aside-controls/js/confirm-approve-window.js?v=<?= VER; ?>"></script>
    <script src="/_new-codebase/front/components/aside-controls/js/save-parts-window.js?v=<?= VER; ?>"></script>
    <script src="/_new-codebase/front/components/aside-controls/js/aside-controls.js?v=<?= VER; ?>"></script>
    <!-- / Aside controls -->
    <div id="aside-controls-json" style="display: none"><?= json_encode(models\RepairCard::getAsideControls($repair['id'])); ?></div>
    <div id="repair-data-json" style="display: none"><?= json_encode(['id' => $repair['id'], 'model_id' => $repair['model_id'], 'serial' => $repair['serial'], 'blocked_flag' => (int)$blockedFlag, 'back_url' => $backURL]); ?></div>
</body>

</html>