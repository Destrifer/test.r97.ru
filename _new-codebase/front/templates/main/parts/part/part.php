<?php

function getCatsWindowHTML(array $cats)
{
    $html = '<form id="cats-window-form">
                <div class="cats-window">
                    <div class="cats-window__col">
                    <div>
                        <button data-action="check-all" class="form__btn form__btn_small form__btn_primary" style="margin-right: 24px">Выбрать все</button>
                        <button data-action="uncheck-all" class="form__btn form__btn_small">Снять выбор</button>
                    </div>';
    $c = ceil(count($cats) / 2) + 3;
    $n = 0;
    foreach ($cats as $l => $list) {
        if ($n == $c) {
            $html .= '</div>
                     <div class="cats-window__col">';
        }
        $html .= '<div class="cats-window__block">
                    <h3 class="cats-window__title">' . $l . '</h3>';
        foreach ($list as $cat) {
            $html .= '<label class="cats-window__item">
                        <input type="checkbox" name="cat_id[]" ' . (($cat['checked_flag']) ? 'checked' : '') . ' value="' . $cat['id'] . '" class="cats-window__cb"> 
                        <div class="cats-window__name">' . $cat['name'] . '</div>
                        </label>';
        }
        $html .= '</div>';
        $n++;
    }
    $html .= '</div>
        </div> 
              <div class="cats-window__submits">
                <button class="form__btn form__btn_secondary" data-action="close-cats-window">Отмена</button>
                <button type="submit" class="form__btn" data-action="save-cats-window">Сохранить</button>
              </div>
    </form>';
    return $html;
}


function getDepotsHTML(array $balance, array $depots)
{
    ob_start();
?>
    <form id="depots-form" class="container gutters depots form">
        <div class="row">
            <div class="col-5"><b>Склад</b></div>
            <div class="col-5"><b>Место</b></div>
            <div class="col-2"><b>Остаток</b></div>
        </div>
        <div style="max-height: 250px; overflow-y: auto; overflow-x: hidden">
            <?php foreach ($balance as $depot) : ?>
                <div class="row" data-depot-id="<?= $depot['id']; ?>">
                    <div class="col-5">
                        <div class="form__cell">
                            <?php if ($depot['depot_id'] != 1) : ?>
                                <select name="depot_id[]" class="form__select select2">
                                    <?= getOptionsHTML($depots, $depot['depot_id']); ?>
                                </select>
                            <?php else : ?>
                                <input type="text" readonly class="form__text" value="<?= $depot['depot']; ?>">
                                <input type="hidden" value="<?= $depot['depot_id']; ?>" name="depot_id[]">
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-5">
                        <div class="form__cell">
                            <input type="text" name="place[]" class="form__text" value="<?= $depot['place']; ?>">
                        </div>
                    </div>
                    <div class="col-2">
                        <div class="form__cell">
                            <input type="number" name="qty[]" readonly min="0" class="form__text" placeholder="0" value="<?= $depot['qty']; ?>">
                            <?php
                           /*  if ($depot['depot_id'] != 1) {
                                echo '<div class="model__del-btn model__del-btn_depot" data-action="del-depot" title="Удалить"></div>';
                            } */
                            ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <!-- <div class="row" data-trigger>
            <div class="col-2">
                <div class="form__cell">
                    <div class="model__add-serial-btn" data-action="add-depot">+ Добавить склад</div>
                </div>
            </div>
            <div class="col-10">
            </div>
        </div> -->
    </form>
<?php
    return ob_get_clean();
}


function getPhotosHTML(array $photos)
{
    ob_start();
    foreach ($photos as $path) {
        echo getPhotoHTML($path);
    }
?>
    <div class="photos__item photos__item_add-btn">
        <label class="photos__add-btn">+ <input type="file" data-input="photo-file" style="display: none" accept=".jpg,.jpeg,.png"></label>
    </div>
<?php
    return ob_get_clean();
}


function getPhotoHTML($path)
{
    ob_start(); ?>
    <div class="photos__item photos__item_active" data-photo>
        <a href="<?= $path; ?>" data-photo-link data-fancybox class="photos__body">
            <div class="photos__edit-panel">
                <div class="photos__panel-btn photos__panel-btn_rotate" data-action="rotate-photo" data-direction="left" title="Повернуть налево"></div>
                <div class="photos__panel-btn photos__panel-btn_rotate" data-action="rotate-photo" data-direction="right" title="Повернуть направо"></div>
                <div class="photos__panel-btn photos__panel-btn_del" data-action="del-photo" title="Удалить"></div>
            </div>
            <img src="<?= $path; ?>" alt="" data-photo-img class="photos__img">
        </a>
        <input type="hidden" name="photos[]" data-photo-path value="<?= $path; ?>">
    </div>
<?php
    return ob_get_clean();
}


function getOptionsHTML(array $values, $curVal = null, $first = '- вариант не выбран -')
{
    $html = '';
    if ($first) {
        $html = '<option value="">' . $first . '</option>';
    }
    if (!$values) {
        return $html;
    }
    if (isset($values[0]['id'])) {
        foreach ($values as $v) {
            $selFlag = ($curVal == $v['id']) ? 'selected' : '';
            $html .= '<option value="' . htmlspecialchars($v['id']) . '" ' . $selFlag . '>' . htmlspecialchars($v['name']) . '</option>';
        }
    } else {
        foreach ($values as $id => $v) {
            $selFlag = ($curVal == $id) ? 'selected' : '';
            $html .= '<option value="' . htmlspecialchars($id) . '" ' . $selFlag . '>' . htmlspecialchars($v) . '</option>';
        }
    }
    return $html;
}


function getModelHTML(array $model)
{
    ob_start();
?>
    <div class="col-12" data-model-id="<?= $model['id']; ?>">
        <div class="form__cell">
            <section class="model">
                <div class="model__del-btn model__del-btn_model" data-action="del-model" title="Удалить"></div>
                <div class="model__name"><?= $model['name']; ?></div>
                <div class="model__serials" data-serials>
                    <?= getSerialsHTML($model['serials']); ?>
                </div>
            </section>
        </div>
    </div>
    <?php
    return ob_get_clean();
}


function getSerialsHTML(array $serials)
{
    ob_start();
    if ($serials) :
        foreach ($serials as $serial) : ?>
            <div class="model__serial" data-serial-id="<?= $serial['model_serial_id']; ?>">
                <input type="text" readonly class="form__text" value="<?= $serial['full_model_serial']; ?>">
                <div class="model__del-btn model__del-btn_serial" data-action="del-serial" title="Удалить"></div>
                <input type="hidden" name="serial_ids[]" value="<?= $serial['model_serial_id']; ?>">
            </div>
    <?php
        endforeach;
    else :
        echo '<div class="model__serial" data-serial-id>Номеров нет.</div>';
    endif;
    echo getSerialControlsHTML();
    ?>

<?php
    return ob_get_clean();
}


function getSerialControlsHTML()
{
    return '<div class="model__serial" data-trigger>
                <div class="model__add-serial-btn" data-action="add-serial">+ Добавить номер</div>
                <div class="model__add-serial-btn" data-action="add-all-serials">+ Выбрать все</div>
            </div>';
}
