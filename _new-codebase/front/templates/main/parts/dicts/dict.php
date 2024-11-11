<?php


function valueFormHTML($num = 0, $value = [])
{
   ?>
    <div class="dict__value-block data-params-item" data-value-block id="value-block-tpl">
    <div class="dict__del-btn" title="Удалить параметр" data-action="del-value-block"></div>
    <div class="dict__name">
        <div class="dict__col-1">
            <label class="label">Название пункта:</label>
            <input class="form__text" type="text" value="<?= (isset($value['name'])) ? $value['name'] : ''?>" data-input="name" name="value[<?= $num; ?>][name]">
        </div>
        <div class="dict__col-2">
            <label class="label">Значение:</label>
            <input class="form__text" type="text" value="<?= (isset($value['val'])) ? $value['val'] : ''?>" data-input="val" name="value[<?= $num; ?>][val]">
        </div>
    </div>
    <div class="dict__description">
        <label class="label">Описание:</label>
        <textarea class="form__text" data-input="description" name="value[<?= $num; ?>][description]"><?= (isset($value['description'])) ? $value['description'] : ''?></textarea>
    </div>
</div>
<?php
}
