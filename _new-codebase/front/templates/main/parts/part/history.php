<?php

function getHistoryHTML()
{
    ob_start();
?>
    <div id="history-form" class="container gutters depots form">
        <div class="row" data-trigger>
            <div class="col-4">
                <div class="form__cell">
                    <div class="history__btn history__btn_in" data-action="add-history-in"><b>+</b> Приход</div>
                    <div class="history__btn history__btn_out" data-action="add-history-out"><b>&#8722;</b> Расход</div>
                </div>
            </div>
        </div>
    </div>
<?php
    return ob_get_clean();
}


function historyTPL(array $reasons, array $models, array $depots, array $balance, array $arrivals)
{
    $depots = array_column($depots, 'name', 'id');
    $depotsOptionsOut = []; // отфильтровать склады для расхода (только с положительным балансом)
    foreach ($balance as $bal) {
        if ($bal['qty']) {
            $depotsOptionsOut[$bal['depot_id']] = $depots[$bal['depot_id']];
        }
    }

    echo '<datalist id="arrivals-list">';
    foreach ($arrivals as $arrival) {
        echo '<option value="' . $arrival['name'] . '"></option>';
    }
    echo '</datalist>';
?>
    <!-- History in tpl -->
    <div class="row history__row history__row-in" id="history-in-tpl" data-history>
        <div class="col-6">
            <div class="form__cell">
                <label class="form__label part__label">№ Прихода (необязательно):</label>
                <input type="text" value="" list="arrivals-list" name="history_in[arrival_name][]" value="" class="form__text">
            </div>
        </div>
        <div class="col-6">
            <div class="form__cell">
                <label class="form__label part__label">Склад:</label>
                <select name="history_in[depot_id][]" class="select2-depots form__select">
                    <?= getOptionsHTML($depots); ?>
                </select>
            </div>
        </div>
        <div class="col-6">
            <div class="form__cell">
                <label class="form__label part__label">Принято на склад:</label>
                <input type="number" required min="1" value="1" name="history_in[num][]" class="form__text">
            </div>
        </div>
        <div class="col-6">
            <div class="form__cell">
                <label class="form__label part__label">Дата и время:</label>
                <input type="text" required name="history_in[date_time][]" class="form__text" value="" data-datetimepicker>
            </div>
        </div>
        <div class="model__del-btn" data-action="del-history" title="Удалить"></div>
    </div>
    <!-- / History in tpl -->

    <!-- History out tpl -->
    <div class="row history__row history__row-out" id="history-out-tpl" data-history>
        <div class="col-12">
            <div class="form__cell">
                <label class="form__label part__label">Склад:</label>
                <select name="history_out[depot_id][]" class="select2-depots form__select">
                    <?= getOptionsHTML($depotsOptionsOut); ?>
                </select>
            </div>
        </div>
        <div class="col-2">
            <div class="form__cell">
                <label class="form__label part__label">Списано со склада:</label>
                <input type="number" min="1" required value="1" data-input="num" name="history_out[num][]" value="" class="form__text">
            </div>
        </div>
        <div class="col-4">
            <div class="form__cell">
                <label class="form__label part__label">Пояснение:</label>
                <select name="history_out[reason_id][]" data-input="reason-id" class="form__select">
                    <?= getOptionsHTML($reasons, '', ''); ?>
                </select>
            </div>
        </div>
        <div class="col-4" data-elem="repair-id-field">
            <div class="form__cell">
                <label class="form__label part__label">№ карточки ремонта:</label>
                <input type="text" value="" name="history_out[repair_id][]" class="form__text">
            </div>
        </div>
        <div class="col-4" data-elem="recip-field" style="display: none">
            <div class="form__cell">
                <label class="form__label part__label">Получатель:</label>
                <input type="text" value="" name="history_out[recip][]" class="form__text">
            </div>
        </div>
        <div class="col-2">
            <div class="form__cell">
                <label class="form__label part__label">Дата и время:</label>
                <input type="text" required name="history_out[date_time][]" class="form__text" value="" data-datetimepicker>
                <div class="model__del-btn" data-action="del-history" title="Удалить"></div>
            </div>
        </div>
        <div class="col-6" data-elem="model-field" style="display: none">
            <div class="form__cell">
                <label class="form__label part__label">Модель:</label>
                <select name="history_out[model_id][]" data-input="history-model-id" class="select2-models form__select">
                    <?= getOptionsHTML($models); ?>
                </select>
            </div>
        </div>
        <div class="col-3" data-elem="serial-field" style="display: none">
            <div class="form__cell">
                <label class="form__label part__label">Серийный номер:</label>
                <input type="text" value="" data-input="history-serial" name="history_out[serial][]" class="form__text">
            </div>
        </div>
        <div class="col-2" data-elem="provider-field" style="display: none">
            <div class="form__cell">
                <label class="form__label part__label">Завод:</label>
                <input type="text" value="" readonly name="history_out[provider_id][]" data-input="history-provider" class="form__text">
            </div>
        </div>
        <div class="col-1" data-elem="order-field" style="display: none">
            <div class="form__cell">
                <label class="form__label part__label">Заказ:</label>
                <input type="text" value="" readonly name="history_out[order_id][]" data-input="history-order" class="form__text">
            </div>
        </div>
    </div>
    <!-- / History out tpl -->
<?php
}
