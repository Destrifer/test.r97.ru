<?php

function sendWindowHTML(array $companies)
{
?>
    <div style="display: none" id="send-window">
        <div class="form" style="min-width: 400px">
            <h3 class="form__title" style="margin-top: 0">Отправка запчастей</h3>
            <div class="container gutters">
                <div class="row">
                    <div class="col-12">
                        <div class="form__field">
                            <label class="form__label">Транспортная компания:</label>
                            <select class="form__select" style="width: 100%" data-input="transport-company-id">
                                <?php optionsHTML($companies, '', ''); ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form__field">
                            <label class="form__label">Трек-номер:</label>
                            <input type="text" value="" data-input="track-num" class="form__text">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form__field">
                            <label class="form__label">Дата отправки:</label>
                            <input type="text" value="<?= date('d.m.Y'); ?>" placeholder="дд.мм.гггг" data-input="send-date" class="form__text">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="form__field form__field_final form__cell-panel" style="display:flex;justify-content: space-between; margin-top: 32px">
                            <button class="form__btn form__btn_secondary" data-on-click="close">Отмена</button>
                            <button class="form__btn" data-on-click="send">Отправить</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
}
