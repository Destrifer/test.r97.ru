<?php

/**
 * Возвращает окно выбора склада для перемещения
 * 
 * @param array $depots Запчасти
 * 
 * @return string HTML таблицы
 */
function getMovePartsTableHTML(array $depots)
{
    ob_start();
?>
    <form class="form" id="move-form">
        <div style="margin-bottom: 32px">
            <h3 class="form__title" style="margin-top: 0">Выберите склад для перемещения</h3>
            <select class="select2" name="target_depot_id" required style="width: 100%">
                <option value="">-- выберите склад --</option>
                <?php
                foreach ($depots as $depot) {
                    echo '<option value="' . $depot['id'] . '">' . $depot['name'] . '</option>';
                }
                ?>
            </select>
        </div>
        <div style="display: flex; justify-content: space-between">
            <button class="form__btn form__btn_secondary" data-action="close-window">Отмена</button>
            <button type="submit" class="form__btn" data-action="apply-move">Переместить</button>
        </div>
    </form>
<?php
    return ob_get_clean();
}
