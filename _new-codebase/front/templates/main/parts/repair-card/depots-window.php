<?php

function getDepotsWindowHTML(array $depots, $partID, $orderID)
{
    ob_start();
?>
    <form class="form" id="receive-part-form" style="min-width: 800px">
        <h3 class="form__title" style="margin-top: 0">Принять запчасть</h3>

        <div class="container gutters">
            <div class="row">
                <div class="col-12">
                    <div class="form__field">
                        <label class="form__label">Склад:</label>
                        <select class="form__select select2" style="width: 100%" name="depot_id">
                            <?php optionsHTML($depots, 1, ''); ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="form__field form__field_final form__cell-panel" style="display:flex;justify-content: space-between; margin-top: 32px">
                        <input type="hidden" name="part_id" value="<?= $partID; ?>">
                        <input type="hidden" name="order_id" value="<?= $orderID; ?>">
                        <button class="form__btn form__btn_secondary" data-action="close-window">Отмена</button>
                        <button class="form__btn" type="submit">Подтвердить</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
<?php
    return ob_get_clean();
}
