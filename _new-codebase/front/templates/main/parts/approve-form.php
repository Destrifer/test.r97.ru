<?php

function approveFormHTML($title, $curVal, $repairID, $action)
{
?>
    <section class="approve-form" data-approve-form="form">
        <h3 class="approve-form__title"><?= $title; ?></h3>
        <div class="approve-form__controls">
            <div class="approve-form__flags">
                <label class="approve-form__flag"><input data-input="is-approved" type="radio" name="is_approved" value="1" <?= ($curVal) ? 'checked' : ''; ?>> <span>Одобрить</span></label>
                <label class="approve-form__flag"><input data-input="is-approved" type="radio" name="is_approved" value="0" <?= (!$curVal) ? 'checked' : ''; ?>> <span>Отклонить</span></label>
            </div>
            <div>
                <a href="#" class="approve-form__btn form__btn" data-action="<?= $action; ?>" data-approve-form="approve-btn">Подтвердить выбор</a>
            </div>
            <input type="hidden" value="<?= $repairID; ?>" data-input="repair-id" />
        </div>
        <div class="approve-form__comment" style="display: none" data-approve-form="approve-comment">
            <textarea data-input="approve-form-comment" placeholder="Комментарий..." class="form__text"></textarea>
        </div>
    </section>
<?php
}
