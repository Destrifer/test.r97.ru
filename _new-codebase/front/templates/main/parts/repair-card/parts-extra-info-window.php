<?php

function getManualPartWindowHTML($repairID)
{
    ob_start();
?>
    <div>
        <form class="form extra-info-window" id="manual-part-window">
            <div class="container gutters">
                <div class="row">
                    <div class="col-12">
                        <div class="form__cell extra-info-window__hint">
                            <p>Пожалуйста, введите информацию о запчасти.</p>
                        </div>
                    </div>
                </div>
                <div class="row" data-part>
                    <div class="col-6">
                        <div class="form__cell">
                            <div class="extra-info-window__photo" data-elem="preview">
                                Загрузите фото
                            </div>
                            <input type="file" data-input="photo-file" accept=".jpg,.jpeg,.png">
                            <input type="hidden" name="photo_path" data-input="photo-path">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form__cell">
                            <textarea name="comment" class="form__text extra-info-window__comment" placeholder="Введите наименование с платы..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="form__sep" style="height:50px"></div>
                    </div>
                    <div class="col-6">
                        <div class="form__cell">
                            <button data-action="close-window" class="form__btn form__btn_secondary">Закрыть</button>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form__cell" style="text-align: right">
                            <input type="hidden" name="repair_id" value="<?= $repairID; ?>">
                            <button data-action="add-manual-part" type="submit" class="form__btn">Добавить запчасть</button>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
<?php
    return ob_get_clean();
}


function getExtraInfoWindowHTML(array $parts, $repairID)
{
    ob_start();
?>
    <div>
        <form class="form extra-info-window" id="extra-info-window">
            <div class="container gutters">
                <div class="row">
                    <div class="col-12">
                        <div class="form__cell extra-info-window__hint">
                            <p>Пожалуйста, добавьте дополнительную информацию о запчастях.</p>
                        </div>
                    </div>
                </div>
                <?php foreach ($parts as $part) : ?>
                    <div class="row" data-part data-id="<?= $part['id']; ?>">
                        <div class="col-12">
                            <div class="extra-info-window__part-name">
                                <b><?= $part['name']; ?></b>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form__cell">
                                <div class="extra-info-window__photo" data-elem="preview">
                                    Загрузите фото
                                </div>
                                <input type="file" data-input="photo-file" accept=".jpg,.jpeg,.png">
                                <input type="hidden" name="parts[<?= $part['id']; ?>][photo_path]" data-input="photo-path">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form__cell">
                                <textarea name="parts[<?= $part['id']; ?>][comment]" class="form__text extra-info-window__comment" placeholder="Введите наименование с платы..."></textarea>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="row">
                    <div class="col-12">
                        <div class="form__sep" style="height:50px"></div>
                    </div>
                    <div class="col-6">
                        <div class="form__cell">
                            <button data-action="close-window" class="form__btn form__btn_secondary">Закрыть</button>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form__cell" style="text-align: right">
                            <input type="hidden" name="repair_id" value="<?= $repairID; ?>">
                            <button data-action="save-window" type="submit" class="form__btn">Отправить заказ</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
<?php
    return ob_get_clean();
}


function getPartExtraWindowHTML($partName, $photoPath, $comment)
{
    ob_start();
?>
    <div style="display: none" class="form extra-info-window" data-part-modal>
        <div class="container gutters">
            <div class="row">
                <div class="col-12">
                    <div class="extra-info-window__part-name">
                        <b><?= $partName; ?></b>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form__cell">
                        <div class="extra-info-window__photo" data-elem="preview">
                            <a href="<?= $photoPath; ?>" data-fancybox target="_blank"><img src="<?= $photoPath; ?>" alt=""></a>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form__cell">
                        <textarea readonly class="form__text extra-info-window__comment"><?= $comment; ?></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
    return ob_get_clean();
}
