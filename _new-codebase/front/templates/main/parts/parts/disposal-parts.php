<?php
function disposalWindowServiceHTML()
{
?>
    <div id="disposal-window" style="display: none; max-width: 1200px">
        <form class="container gutters form" id="disposal-form">
            <div class="row">
                <div class="col-12">
                    <h3 class="form__title" style="margin-top: 0">Запрос на утилизацию</h3>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="form__field" id="disposal-parts-list">
                        <span>Загрузка списка запчастей...</span>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-2">
                    <div class="form__field form__field_final">
                        <button class="form__btn form__btn_secondary" data-action="cancel-disposal">Отмена</button>
                    </div>
                </div>
                <div class="col-10">
                    <div class="form__field form__field_final" style="text-align: right">
                        <button class="form__btn" data-action="send-disposal-request" type="submit">Отправить запрос</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
<?php
}


function disposalWindowAdminHTML(array $reasons, $activeReasonID = 0)
{
?>
    <div id="disposal-window" style="display: none; max-width: 1200px">
        <form class="container gutters form" id="disposal-form">
            <div class="row">
                <div class="col-12">
                    <h3 class="form__title" style="margin-top: 0">Утилизация запчастей</h3>
                </div>
                <div class="col-7">
                    <div class="form__field">
                        <label for="reason-id">Причина утилизации:</label>
                        <select class="form__select" id="reason-id" name="reason_id">
                            <?php
                            foreach ($reasons as $id => $name) {
                                $sel = ($id == $activeReasonID) ? 'selected' : '';
                                echo '<option value="' . $id . '" ' . $sel . '>' . $name . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col-2">
                    <div class="form__field">
                        <label for="set-num-for-all" title="Выставляет одинаковое кол-во для всех запчастей">Количество:</label>
                        <input type="number" min="0" id="set-num-for-all" data-input="set-num-for-all" value="1" class="form__text">
                    </div>
                </div>
                <div class="col-3">
                    <div class="form__field">
                        <label for="date">Дата и время утилизации:</label>
                        <input type="text" id="date" value="<?= date('d.m.Y H:i'); ?>" data-datetimepicker name="date_time" class="form__text">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="form__field" id="disposal-parts-list">
                        <span>Загрузка списка запчастей...</span>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-2">
                    <div class="form__field form__field_final">
                        <button class="form__btn form__btn_secondary" data-action="cancel-disposal">Отмена</button>
                    </div>
                </div>
                <div class="col-10">
                    <div class="form__field form__field_final" style="text-align: right">
                        <button class="form__btn" data-action="apply-disposal" type="submit">Утилизировать</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
<?php
}


/**
 * Возвращает таблицу утилизируемых запчастей
 * 
 * @param array $parts Запчасти
 * @param bool $maxNumFlag Заполнять количество максимальным значением
 * 
 * @return string HTML таблицы
 */
function getDisposalPartsTableHTML(array $parts, $maxNumFlag = false)
{
    ob_start();
    if (!empty($parts['is_error'])) {
        echo '<p>' . $parts['message'] . '</p>';
        return ob_get_clean();
    }
    if (empty($parts)) {
        echo '<p>Нет доступных к утилизации запчастей на текущую дату.</p>';
        return ob_get_clean();
    }
?>
    <table class="table">
        <thead>
            <tr>
                <th>Запчасть</th>
                <th>Склад</th>
                <th>Остаток на складе</th>
                <th>Списать</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($parts as $part) :
                $partUID = $part['id'] . ':' . $part['depot_id'];
            ?>
                <tr>
                    <td><?= $part['name']; ?></td>
                    <td><?= $part['depot']; ?></td>
                    <td><?= $part['num']; ?></td>
                    <td>
                        <input type="number" data-input="disp-part-num" min="0" max="<?= $part['num']; ?>" name="parts[<?= $partUID; ?>][num]" value="<?= (!$maxNumFlag) ? 1 : $part['num']; ?>" class="form__text">
                        <input type="hidden" name="parts[<?= $partUID; ?>][depot_id]" value="<?= $part['depot_id']; ?>" />
                        <input type="hidden" name="parts[<?= $partUID; ?>][id]" value="<?= $part['id']; ?>" />
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php
    return ob_get_clean();
}
