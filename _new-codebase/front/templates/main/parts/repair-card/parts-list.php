<?php

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

    <div class="<?= $col; ?>">
        <div class="form__field form__field_sm">
            <label class="form__label">Поиск:</label>
            <input type="search" class="form__text" placeholder="🔎" name="search">
        </div>
    </div>

<?php
}

function getPartsListHTML(array $parts, array $summary)
{
    ob_start();
    echo getPartsListItemsHTML($parts, $summary);
    return ob_get_clean();
}

function getPartsListItemsHTML(array $parts, array $summary)
{
    // Отладочный вывод статуса
    echo '<pre>Отладка: Статус внутри getPartsListItemsHTML: ' . print_r($summary['status'] ?? 'Нет данных', true) . '</pre>';

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

            // Вывод статуса перед кнопкой
            echo '<p style="font-weight: bold; color: #333;">Статус ремонта: ' . htmlspecialchars($summary['status'] ?? 'Не задан') . '</p>';

            // Проверка статуса перед отображением кнопки
            if (!isset($summary['status']) || !in_array($summary['status'], ['Подтверждён', 'Выдан', 'Отклонён'])) {
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
        return;
    }
    echo '<div class="parts-list__col parts-list__col_photos">';
    foreach ($photos as $photoPath) {
        echo '<a href="' . $photoPath . '" data-fancybox="group" class="parts-list__col_photo-item" style="background-image: url(' . $photoPath . ')"></a>';
    }
    echo '</div>';
}
