<?php

function getFilterItemsHTML(array $activeFilter, array $filtersList)
{
    $html = getFilterItemHTML(['uri' => ''], $filtersList, true);
    foreach ($activeFilter as $item) {
        $html .= getFilterItemHTML($item, $filtersList);
    }
    return $html;
}

/* Ячейка фильтра */
function getFilterItemHTML(array $filterItem, array $filtersList, $templateFlag = false)
{
    ob_start();
    $c = ($templateFlag) ? 'id="filter-item-template" style="display: none"' : '';
?>
    <div class="col-6 filter__cell" <?= $c; ?> data-filter-item="<?= $filterItem['uri']; ?>">
        <div class="filter__select-col">
            <?= getFilterSelectHTML($filtersList, $filterItem['uri']); ?>
        </div>
        <div class="filter__value-col">
            <div data-filter-value-ctr>
                <?php if ($templateFlag) : ?>
                    <input type="text" class="form__text" placeholder="Выберите столбец" readonly>
                <?php else : ?>
                    <?= getFilterInputHTML($filterItem['uri'], $filterItem['type'], $filterItem['values'], $filterItem['val']); ?>
                <?php endif; ?>
            </div>
            <div class="filter__del-btn" data-action="del-filter" title="Удалить фильтр"></div>
        </div>
    </div>
<?php
    return ob_get_clean();
}

/* Выбор столбца для фильтра */
function getFilterSelectHTML(array $filter, $curURI = null)
{
    $html = '<option value="">- Выберите столбец -</option>';
    foreach ($filter as $uri => $f) {
        $selFlag = ($uri == $curURI) ? 'selected' : '';
        $html .= '<option value="' . $uri . '" ' . $selFlag . '>' . $f['name'] . '</div>';
    }
    return '<select class="form__select" data-filter-select>' . $html . '</select>';
}

/* Поле ввода значения фильтра */
function getFilterInputHTML($filterURI, $filterType, $value, $cur = '')
{
    switch ($filterType) {
        case 'select':
            return getSelectHTML($filterURI, $value, $cur);
        case 'select_search':
            return getSelectSearchHTML($filterURI, $value, $cur);
        case 'date_interval':
            return '<input type="text" name="' . $filterURI . '" value="' . $cur . '" data-filter-input class="form__text" data-range="true" data-multiple-dates-separator=" - " data-datepicker placeholder="Выберите интервал..." autocomplete="off">';
        default:
            return '<input type="text" name="' . $filterURI . '" value="' . $cur . '" data-filter-input class="form__text" placeholder="Значение...">';
    }
}

/* Селект с поиском */
function getSelectSearchHTML($name, array $values, $cur = '')
{
    $html = '<option value="">- Выберите вариант -</option>';
    foreach ($values as $val => $text) {
        $selFlag = ($val == $cur) ? 'selected' : '';
        $html .= '<option value="' . $val . '" ' . $selFlag . '>' . $text . '</option>';
    }
    return '<select name="' . $name . '" data-select2-input data-filter-input class="form__select">' . $html . '</select>';
}

function getSelectHTML($name, array $values, $cur = '')
{
    $html = '<option value="">- Выберите вариант -</option>';
    foreach ($values as $val => $text) {
        $selFlag = ($val == $cur) ? 'selected' : '';
        $html .= '<option value="' . $val . '" ' . $selFlag . '>' . $text . '</option>';
    }
    return '<select name="' . $name . '" data-filter-input class="form__select">' . $html . '</select>';
}
