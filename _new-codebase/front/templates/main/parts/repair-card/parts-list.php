<?php

// Получаем ID ремонта из GET-параметров
$repair_id = (int) $_GET['id']; // Приводим к числу для безопасности

// Загружаем данные о ремонте
$repair = models\Repair::getRepairByID($repair_id);
$repair_status = $repair['status']; // Статус ремонта

function filterFormHTML(
    $userRole,
    array $countries,
    array $depots,
    array $attrs,
    array $types,
    array $providers,
    array $cats,
    array $groups
) {
    $isAdmin = in_array($userRole, ['admin', 'store', 'slave-admin', 'master']);
    $col = ($isAdmin && $userRole != 'master') ? 'col-3' : 'col-4';
?>

    <?php if ($isAdmin && $userRole != 'master') : ?>
        <div class="<?= $col; ?>">
            <div class="form__field form__field_sm">
                <label class="form__label">Склады страны:</label>
                <select class="form__select" data-input="country-id" name="country_id">
                    <option value="">-- все страны --</option>
                    <?php
                    foreach ($countries as $country) {
                        $isSelected = (!empty($country['is_selected'])) ? 'selected' : '';
                        echo '<option value="' . $country['id'] . '" ' . $isSelected . '>' . $country['name'] . '</option>';
                    }
                    ?>
                </select>
            </div>
        </div>
    <?php endif; ?>

    <div class="<?= $col; ?>">
        <div class="form__field form__field_sm" id="depot-filter">
            <label class="form__label">Склад:</label>
            <select class="form__select fselect" name="depot_id[]" multiple>
                <option value="">-- любой --</option>
                <?php if ($isAdmin) : ?>
                    <?php foreach ($depots as $country => $depotsList) : ?>
                        <optgroup label="<?= $country; ?>">
                            <?php optionsHTML($depotsList, null, ''); ?>
                        </optgroup>
                    <?php endforeach; ?>
                <?php else : ?>
                    <?php foreach ($depots as $depotsList) : ?>
                        <?php optionsHTML($depotsList, null, ''); ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
    </div>

    <div class="<?= $col; ?>">
        <div class="form__field form__field_sm">
            <label class="form__label">Признак:</label>
            <select class="form__select" name="attr_id">
                <?php optionsHTML($attrs, null, '-- любой --'); ?>
            </select>
        </div>
    </div>

    <div class="<?= $col; ?>">
        <div class="form__field form__field_sm">
            <label class="form__label">Принадлежность:</label>
            <select class="form__select" name="type_id">
                <?php optionsHTML($types, null, '-- любая --'); ?>
            </select>
        </div>
    </div>

    <?php if ($isAdmin) : ?>
        <div class="col-3">
            <div class="form__field form__field_sm">
                <label class="form__label">Партномер:</label>
                <input type="text" class="form__text" name="part_num">
            </div>
        </div>

        <div class="col-3">
            <div class="form__field form__field_sm">
                <label class="form__label">Завод:</label>
                <select class="form__select select2" name="provider_id">
                    <?php optionsHTML($providers, null, '-- любой --'); ?>
                </select>
            </div>
        </div>

        <div class="col-2">
            <div class="form__field form__field_sm">
                <label class="form__label">Заказ:</label>
                <input type="text" class="form__text" placeholder="-- любой --" name="order">
            </div>
        </div>

        <div class="col-4">
            <div class="form__field form__field_sm">
                <label class="form__label">Категория:</label>
                <select class="fselect" style="display: none" multiple name="cat_id[]">
                    <?php optionsHTML($cats, null, '-- любая --'); ?>
                </select>
            </div>
        </div>

    <?php endif; ?>

    <div class="col-4">
        <div class="form__field form__field_sm">
            <label class="form__label">Поиск:</label>
            <input type="search" class="form__text" placeholder="🔎" name="search">
        </div>
    </div>

    <div class="col-4">
        <div class="form__field form__field_sm">
            <label class="form__label">Группа запчастей:</label>
            <select class="form__select select2" name="group_id">
                <?php optionsHTML($groups, null, '-- любая --'); ?>
            </select>
        </div>
    </div>

    <div class="col-2">
        <div class="form__field form__field_sm form__field_bottom" style="justify-content: flex-end">
            <button type="submit" class="form__btn form__btn_std filter-form__btn">Применить</button>
        </div>
    </div>

    <div class="col-2">
        <div class="form__field form__field_sm form__field_bottom" style="justify-content: flex-end">
            <button class="form__btn form__btn_std form__btn_secondary filter-form__btn" data-action="reset">Сброс</button>
        </div>
    </div>

    <?php if ($isAdmin) : ?>
        <div class="col-3">
            <div class="form__field form__field_sm">
                <label><input type="checkbox" name="show_all" value="1"> Поиск по всем запчастям</label>
            </div>
        </div>

        <div class="col-2">
            <div class="form__field form__field_sm">
                <label><input type="checkbox" name="hide-empty" value="1"> Скрыть 0 шт.</label>
            </div>
        </div>
    <?php endif; ?>

<?php
}


function old(array $groups, array $providers, array $cats, $userRole = '', $hasStandardFlag = false)
{
    $groupsHTML = '';
    if (count($groups) > 1) {
        $t = '';
        foreach ($groups as $id => $name) {
            $t .= '<option value="' . $id . '">' . $name . '</option>';
        }
    }
    $ch = $std = $adm = $order = '';
    if (in_array($userRole, ['admin', 'store', 'master'])) {
        $ch = '<div class="col-3"><label style="margin-top: 8px;display: block;"><input type="checkbox" data-filter="all-parts-flag"> Поиск по всем запчастям</label></div>';
    }
    if ($hasStandardFlag) {
        $std = '<div class="col-3"><label style="margin-top: 8px;display: block;"><input type="checkbox" data-filter="show-standard-flag"> Показать неоригинальные</label></div>';
    }
    if (in_array($userRole, ['admin', 'store'])) {
        $provOptions = $catsOptions = '';
        foreach ($providers as $row) {
            $provOptions .= '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
        }
        foreach ($cats as $id => $name) {
            $catsOptions .= '<option value="' . $id . '">' . $name . '</option>';
        }
        $adm = '<div class="col-3">
                    <label style="margin-top: 8px;display: block;"><input type="checkbox" data-filter="hide-empty"> Скрыть 0 шт.</label>
                </div>';
        $order = '
               
                 ';
    }
    return '<div class="' . (($groupsHTML) ? 'col-6' : 'col-12') . '">
                     
            </div>
            ' . $groupsHTML .  $order . $ch .  $std .  $adm . '
            <div class="col-12">
                <div class="form__sep" style="height: 15px"></div>
            </div>';
}


function getPartsListHTML(array $parts, $repair_status)
{
    ob_start();
    echo getPartsListItemsHTML($parts, $repair_status);
    return ob_get_clean();
}

function getPartsListItemsHTML(array $parts, $repair_status)
{
    ob_start();
    echo '<div class="row">';
    
    if (!$parts) {
        echo '<div class="col-12"><p style="text-align: center;padding: 32px 0">Запчасти отсутствуют.</p></div>';
    } else {
        foreach ($parts as $part) {
            echo '<div data-part class="col-12 col-sm-6" style="padding-bottom: 32px;"
                 data-has-original-flag="' . ((!empty($part['has_original_flag'])) ? '1' : '0') . '"
                 data-attr-id="' . $part['attr_id'] . '"
                 data-type-id="' . $part['type_id'] . '"
                 data-group-id="' . $part['group_id'] . '"
                 data-origin="store"
                 data-id="' . $part['id'] . '">';

            echo '<div class="parts-list__item ' . ((!empty($part['has_original_flag'])) ? 'parts-list__item_secondary' : '') . '">';
            mainCol($part);
            photosCol($part['photos']);

            // Выводим статус ремонта
            echo '<p style="font-weight: bold; color: #333;">Статус ремонта: ' . htmlspecialchars($repair_status) . '</p>';

            // Скрываем кнопку, если статус "Подтверждён", "Выдан" или "Отклонён"
            if (!in_array($repair_status, ['Подтверждён', 'Выдан', 'Отклонён'])) {
                controlsCol();
            }

            echo '</div></div>';
        }
    }
    
    echo '</div>';
    return ob_get_clean();
}


function mainCol(array $part)
{
    echo '<div class="parts-list__col parts-list__col_main">';
    if ($part['description']) {
        echo '<div data-action="open-info" class="ic ic_info parts-list__extra-btn parts-list__extra-btn_info" title="Дополнительная информация"></div>
              <div class="parts-list__extra-popup" style="display:none" data-elem="popup">' . $part['description'] . '</div>';
    }
    echo '<div data-action="open-log" class="ic ic_clock parts-list__extra-btn parts-list__extra-btn_log" title="История запчасти"></div>
              <div class="parts-list__group-name">' . $part['group'] . '</div>
              <div class="parts-list__part-name" data-elem="name">' . $part['name'] . '</div>
            ' . mainColInfo($part) . '
         </div>';
}


function mainColInfo(array $part)
{
    $extra = '';
    $d = ['<b data-elem="part-code">' . $part['part_code'] . '</b>'];
    $d[] = ($part['attr']) ? mb_strtolower($part['attr']) : '';
    $d[] = ($part['type']) ? mb_strtolower($part['type']) : '';
    $d = array_filter($d);
    $extra = '<div class="parts-info__extra" data-elem="extra" style="display:none">' . implode(', ', $d) . '.</div>';
    return '<div style="display:none" data-elem="qty-data">' . json_encode(array_column($part['balance'], 'qty', 'depot_id')) . '</div>
            ' . $extra . '
            <input type="hidden" data-input="attr-id" value="' . $part['attr_id'] . '">
            <div class="parts-info">
                <div class="parts-info__block" style="width: 100%">
                    ' . getDepotsSelect($part['balance']) . '
                </div>
            </div>';
}


function getDepotsSelect(array $balanceList)
{
    $result = '<select class="select2" style="width: 100%" data-input="depot-id">';
    foreach ($balanceList as $balance) {
        $capt = $balance['depot']['name'];
        if (!empty($balance['is_visible'])) {
            $capt .= ' - ' . $balance['qty'] . ' шт.';
            if (!empty($balance['place'])) {
                $capt .= ' - ' . $balance['place'];
            }
        }
        $result .= '<option value="' . $balance['depot']['id'] . '">' . $capt . '</option>';
    }
    $result .= '</select>';
    return $result;
}


function controlsCol()
{
    echo '<div class="parts-list__col parts-list__col_controls">
            <div>
                <button class="part-order__btn" data-name="order-btn" data-action="order-part">Выбрать</button>
            </div>
          </div>';
}


function photosCol(array $photos)
{
    if (!$photos) {
        echo '';
        return;
    }
    echo '<div class="parts-list__col parts-list__col_photos">';
    foreach ($photos as $photoPath) {
        echo '<a href="' . $photoPath . '" data-fancybox="group" class="parts-list__col_photo-item" style="background-image: url(' . $photoPath . ')"></a>';
    }
    echo '</div>';
}
