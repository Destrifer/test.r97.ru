<?php

function getManualRowHTML(array $manualPart, $newFlag = false)
{
    ob_start();
    $partURI = $manualPart['part_data']['id'] . '-1-' . $manualPart['origin'];
?>
    <tr data-elem="part" data-part data-id="<?= $partURI; ?>" data-origin="<?= $manualPart['origin']; ?>">
        <td></td>
        <td><?= $manualPart['part_data']['name']; ?></td>
        <td>
            <input type="number" name="parts[manual][<?= $partURI; ?>][qty]" value="<?= $manualPart['ordered_qty']; ?>" data-input="qty" data-input-filter="int" placeholder="0" min="0" class="parts-order__qty-input">
        </td>
        <td>—</td>
        <td data-elem="depot-num" style="display:none"></td>
        <td>
            <div class="parts-order__controls">
                <div class="parts-order__control parts-order__control_extra" data-elem="extra-btn" data-action="show-extra" title="Информация о заказе"></div>
                <?= getPartExtraWindowHTML($manualPart['part_data']['name'], $manualPart['extra_data']['photo_path'], $manualPart['extra_data']['comment']); ?>
                <div class="parts-order__control parts-order__control_del" data-elem="remove-btn" data-action="remove-part" title="Удалить эту запчасть"></div>
            </div>
            <!-- Data -->
            <input type="hidden" data-input="new-flag" value="<?= (bool)$newFlag; ?>">
            <input type="hidden" name="parts[manual][<?= $partURI; ?>][id]" value="<?= $manualPart['part_data']['id']; ?>">
            <input type="hidden" name="parts[manual][<?= $partURI; ?>][depot_id]" data-input="depot-id" value="1">
        </td>
    </tr>
<?php
    return ob_get_clean();
}


/**
 * HTML для запчасти со склада
 * 
 * @param array $part Информация о запчасти
 * @param bool $newFlag Запчасть не сохранена в заказе
 * 
 * @return string HTML
 */
function getStoreRowHTML(array $part, $newFlag = false)
{
    ob_start();
    $partURI = $part['part_data']['id'] . '-' . $part['depot_data']['depot_id'] . '-' . $part['origin'];
?>
    <tr data-elem="part" class="<?= (($part['cancel_flag']) ? 'cancel' : ''); ?>" data-part data-id="<?= $part['part_data']['id']; ?>" data-origin="<?= $part['origin']; ?>" data-return-flag="<?= $part['return_flag']; ?>">
        <td><?= $part['part_data']['part_code']; ?></td>
        <td>
            <?php
            echo '<div class="parts-order__part-name">' . $part['part_data']['name'] . '</div>';
            $d = [];
            $d[] = ($part['part_data']['attr']) ? $part['part_data']['attr'] : '';
            $d[] = ($part['part_data']['type']) ? mb_strtolower($part['part_data']['type']) : '';
            $d[] = ($part['alt_flag']) ? 'аналог' : '';
            $d = array_filter($d);
            if ($d) {
                echo '<div class="parts-order__extra" data-elem="extra" style="display:none">' . implode(', ', $d) . '</div>';
            }
            ?>     
        </td>
        <td><input type="number" name="parts[store][<?= $partURI; ?>][qty]" data-input="qty" data-input-filter="int" placeholder="0" min="0" class="parts-order__qty-input" value="<?= $part['ordered_qty']; ?>"></td>
        <td><?= $part['depot_data']['depot']; ?></span> <input type="hidden" name="parts[store][<?= $partURI; ?>][depot_id]" data-input="depot-id" value="<?= $part['depot_data']['depot_id']; ?>"></td>
        <td data-elem="depot-num" style="display:none"><?= $part['depot_data']['qty'] . ' шт.'; ?></td>
        <td style="position: relative">
            <div class="parts-order__controls">
                <?php if (!empty($part['extra_data'])) : ?>
                    <div class="parts-order__control parts-order__control_extra" data-elem="extra-btn" data-action="show-extra" title="Информация о заказе"></div>
                    <?= getPartExtraWindowHTML($part['part_data']['name'], $part['extra_data']['photo_path'], $part['extra_data']['comment']); ?>
                <?php endif; ?>
                <?php if ($part['return_flag'] && !$part['receive_flag']) : ?>
                    <div class="parts-order__control parts-order__control_spinner" data-elem="spinner" title="Запчасть в пути на склад"></div>
                <?php endif; ?>
                <div class="parts-order__control parts-order__control_store" title="Наличие на складах" data-elem="store-btn" data-action="open-store" style="display:none"></div>
                <div class="parts-order__control parts-order__control_cancel <?= (!empty($part['cancel_flag'])) ? 'active' : ''; ?>" data-value="<?= $part['cancel_flag']; ?>" title="Этой запчасти нет" data-elem="cancel-part-btn" data-action="cancel-part" style="display:none"></div>
                <div class="parts-order__control parts-order__control_return <?= (!empty($part['return_flag'])) ? 'disabled' : ''; ?>" data-elem="return-part-btn" data-action="return-part" style="display:none" title="<?= (!empty($part['return_flag'])) ? 'Возвращена на склад' : 'Вернуть на склад'; ?>"></div>
                <div class="parts-order__control parts-order__control_receive <?= (!empty($part['receive_flag'])) ? 'disabled' : ''; ?>" data-elem="receive-part-btn" data-action="receive-part" style="display:none" title="<?= (!empty($part['receive_flag'])) ? 'Возвращена на склад' : 'Вернуть на склад'; ?>"></div>
                <div class="parts-order__control parts-order__control_del" data-elem="remove-btn" data-action="remove-part" title="Удалить эту запчасть"></div>
            </div>
            <a href="/parts-log/?part=<?= $part['part_data']['id']; ?>&depot=<?= $part['depot_data']['depot_id']; ?>" target="_blank" class="ic ic_clock parts-order__log-btn" title="История запчасти"></a>
            <!-- Data -->
            <input type="hidden" name="parts[store][<?= $partURI; ?>][alt_flag]" value="<?= $part['alt_flag']; ?>">
            <input type="hidden" data-input="new-flag" value="<?= (bool)$newFlag; ?>">
            <input type="hidden" data-input="attr-id" value="<?= $part['part_data']['attr_id']; ?>">
            <input type="hidden" data-input="part-id" value="<?= $part['part_data']['id']; ?>">
            <input type="hidden" name="parts[store][<?= $partURI; ?>][id]" value="<?= $part['part_data']['id']; ?>">
        </td>
    </tr>
<?php
    return ob_get_clean();
}


/**
 * HTML для запчасти со склада (изначальный заказ)
 * 
 * @param array $part Информация о запчасти
 * 
 * @return string HTML
 */
function getInitialStoreRowHTML(array $part)
{
    ob_start();
?>
    <tr class="initial" data-elem="part" data-part data-id="<?= $part['part_data']['id']; ?>" data-origin="<?= $part['origin']; ?>">
        <td><?= $part['part_data']['part_code']; ?></td>
        <td>
            <?php
            echo '<div class="parts-order__part-name">' . $part['part_data']['name'] . '</div>';
            $d = [];
            $d[] = ($part['part_data']['attr']) ? $part['part_data']['attr'] : '';
            $d[] = ($part['part_data']['type']) ? mb_strtolower($part['part_data']['type']) : '';
            if (array_filter($d)) {
                echo '<div class="parts-order__extra" data-elem="extra" style="display:none">' . implode(', ', $d) . '</div>';
            }
            ?>    
        </td>
        <td><?= $part['ordered_qty']; ?></td>
        <td><?= $part['depot_data']['depot']; ?></td>
        <td data-elem="depot-num" style="display:none"></td>
        <td style="position: relative">
            <div class="parts-order__controls">
                <?php if (!empty($part['extra_data'])) : ?>
                    <div class="parts-order__control parts-order__control_extra" data-elem="extra-btn" data-action="show-extra" title="Информация о заказе"></div>
                    <?= getPartExtraWindowHTML($part['part_data']['name'], $part['extra_data']['photo_path'], $part['extra_data']['comment']); ?>
                <?php endif; ?>
            </div>
            <a href="/parts-log/?part=<?= $part['part_data']['id']; ?>&depot=<?= $part['depot_data']['depot_id']; ?>" target="_blank" class="ic ic_clock parts-order__log-btn" title="История запчасти"></a>
        </td>
    </tr>
<?php
    return ob_get_clean();
}


function getInitialManualRowHTML(array $manualPart)
{
    ob_start();
    $partURI = $manualPart['part_data']['id'] . '-1-' . $manualPart['origin'];
?>
    <tr class="initial" data-part data-id="<?= $partURI; ?>" data-origin="<?= $manualPart['origin']; ?>">
        <td></td>
        <td><?= $manualPart['part_data']['name']; ?></td>
        <td><?= $manualPart['ordered_qty']; ?></td>
        <td>—</td>
        <td data-elem="depot-num" style="display:none"></td>
        <td>
            <div class="parts-order__controls">
                <div class="parts-order__control parts-order__control_extra" data-elem="extra-btn" data-action="show-extra" title="Информация о заказе"></div>
                <?= getPartExtraWindowHTML($manualPart['part_data']['name'], $manualPart['extra_data']['photo_path'], $manualPart['extra_data']['comment']); ?>
            </div>
        </td>
    </tr>
<?php
    return ob_get_clean();
}


function getOrderFormHTML(array $order, $repairID, $serviceID)
{
    ob_start();
?>
    <form class="parts-order" data-order>
        <?php if (!empty($order['id'])) : ?>
            <div class="parts-order__title">
                <span class="parts-order__title-item" title="<?= $order['create_time']; ?>">
                    Заказ #<?= $order['id']; ?> от <?= $order['create_date']; ?></span>

                <?php if (!empty($order['status_id'])) : ?>
                    <span class="parts-order__title-item">Статус: <b <?= 'class="' . getStatusClass($order['status_id']) . '"'; ?>><?= $order['status']; ?></b></span>
                <?php endif; ?>

                <?php if (!empty($order['approve_date'])) : ?>
                    <span class="parts-order__title-item" title="<?= $order['approve_time']; ?>">Дата обработки: <?= $order['approve_date']; ?></span>
                <?php endif; ?>

                <?php if (!empty($order['send_date'])) : ?>
                    <span class="parts-order__title-item" title="<?= $order['send_time']; ?>">Дата отправки: <?= $order['send_date']; ?></span>
                <?php endif; ?>

                <?php if (!empty($order['cancel_date'])) : ?>
                    <span class="parts-order__title-item" title="<?= $order['cancel_time']; ?>">Дата одобрения акта: <?= $order['cancel_date']; ?></span>
                <?php endif; ?>

                <?php if (!empty($order['receive_date'])) : ?>
                    <span class="parts-order__title-item" title="<?= $order['receive_time']; ?>">Дата получения: <?= $order['receive_date']; ?></span>
                <?php endif; ?>
            </div>
        <?php else : ?>
            <div class="parts-order__title">
                <span class="parts-order__title-item">Новый заказ</span>
            </div>
        <?php endif; ?>
        <input type="hidden" name="status_id" data-input="status-id" value="<?= ((!empty($order['status_id'])) ? $order['status_id'] : '0'); ?>">
        <input type="hidden" name="order_id" data-input="order-id" value="<?= ((!empty($order['id'])) ? $order['id'] : ''); ?>">
        <input type="hidden" name="repair_id" data-input="repair-id" value="<?= $repairID; ?>">
        <input type="hidden" name="service_id" data-input="service-id" value="<?= $serviceID; ?>">
        <table class="parts-order__table">
            <thead>
                <tr>
                    <th style="width: 5%">Код</th>
                    <th style="width: 35%">Запчасть</th>
                    <th style="width: 15%">Количество</th>
                    <th>Склад</th>
                    <th data-elem="depot-num" style="display:none">Остаток на складе</th>
                    <th style="width: 15%"></th>
                </tr>
            </thead>
            <tbody data-elem="parts-container">
                <tr style="display: none" data-elem="empty">
                    <td colspan="100">
                        <div class="parts-order__empty">Заказ пуст.</div>
                    </td>
                </tr>
                <?php
                if (!empty($order['parts'])) {
                    foreach ($order['parts'] as $part) {
                        if (empty($part['part_data'])) {
                            continue;
                        }
                        if ($part['origin'] == 'store') {
                            echo getStoreRowHTML($part);
                        } else {
                            echo getManualRowHTML($part);
                        }
                    }
                }
                echo '<tr class="parts-order__initial-title-row" data-elem="initial-parts" ' . ((!empty($order['initial_parts'])) ? '' : 'style="display:none"') . '>
                            <td colspan="100">
                                <div class="parts-order__initial-title">Заказ РСЦ</div>
                            </td>
                          </tr>';
                if (!empty($order['initial_parts'])) {
                    foreach ($order['initial_parts'] as $part) {
                        if (empty($part['part_data'])) {
                            continue;
                        }
                        if ($part['origin'] == 'store') {
                            echo getInitialStoreRowHTML($part);
                        } else {
                            echo getInitialManualRowHTML($part);
                        }
                    }
                }
                ?>
            </tbody>
        </table>
        <div class="parts-order__submit-line">
            <button data-action="delete-order" data-elem="delete-btn" class="parts-order__ml form__btn form__btn_small form__btn_secondary" style="display: none">Удалить и вернуть запчасти</button>
            <button data-action="update-order" data-elem="update-btn" class="parts-order__ml form__btn form__btn_small form__btn_secondary" style="display: none">Заменить запчасть</button>
            <button data-action="reopen-order" data-elem="reopen-btn" class="parts-order__ml form__btn form__btn_small form__btn_secondary" style="display: none">Отменить акт</button>
            <button data-action="return-order" data-elem="return-btn" class="parts-order__ml form__btn form__btn_small form__btn_secondary" style="display: none">Отменить неверный заказ СЦ</button>
            <button data-action="cancel-order" data-elem="cancel-btn" class="parts-order__ml form__btn form__btn_small form__btn_secondary" style="display: none">Одобрить акт</button>
            <button data-action="create-order" data-elem="create-btn" class="parts-order__ml form__btn form__btn_small" style="display: none">Отправить запрос</button>
            <button data-action="send-order" data-elem="send-btn" class="parts-order__ml form__btn form__btn_small" style="display: none">Отправить запчасти</button>
            <button data-action="take-parts" data-elem="take-parts-btn" class="parts-order__ml form__btn form__btn_small" style="display: none">Получить запчасти</button>
            <button data-action="save-order" data-elem="save-btn" class="parts-order__ml form__btn form__btn_small form__btn_secondary" style="display: none">Сохранить</button>
            <button data-action="approve-order" data-elem="approve-btn" class="parts-order__ml form__btn form__btn_small" style="display: none">Подтвердить заказ СЦ</button>
            <button data-action="receive-order" data-elem="receive-btn" class="parts-order__ml form__btn form__btn_small" style="display: none">Заказ получен</button>
        </div>
    </form>

<?php
    return ob_get_clean();
}


function getStatusClass($statusID)
{
    if ($statusID == 4) {
        return 'green';
    }
    if ($statusID == 5) {
        return 'red';
    }
    return '';
}
