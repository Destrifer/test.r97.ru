<?php

function getNumPerPageHTML($defaultNum)
{
    return '<input type="number" min="5" step="5" data-num-per-page-input id="num-per-page-field" class="form__text" value="' . $defaultNum . '" id="num-per-page">';
}

function getSortHTML(array $defaultSort)
{
    return '<input type="hidden" value="' . $defaultSort['field'] . '" id="sort-field">
            <input type="hidden" value="' . $defaultSort['dir'] . '" id="sort-dir">';
}

function getTabsHTML(array $tabs)
{
    $html = '';
    foreach ($tabs as $tab) {
        $activeFlag = ($tab['active_flag']) ? 'tabs__item_active' : '';
        $cnt = ($tab['cnt']) ? ' <span class="tabs__cnt ' . $tab['cnt_color'] . '">' . $tab['cnt'] . '</span>' : '';
        $specialCnt = ($tab['special_cnt']) ? ' <span class="tabs__cnt tabs__cnt_special">' . $tab['special_cnt'] . '</span>' : '';
        $html .= '<a class="tabs__item ' . $activeFlag . '" ' . $activeFlag . ' data-tab="' . $tab['uri'] . '" href="' . $tab['url'] . '">' . $tab['name'] . $cnt . ' ' . $specialCnt . '</a>';
    }
    return '<nav class="tabs">' . rtrim($html, ' /') . '</nav>';
}

function getColorsLegendHTML(array $colors)
{
    $html = '';
    foreach ($colors as $color => $capt) {
        $html .= '<li class="legend__item">
                        <div class="legend__capt ' . $color . '">' . $capt . '</div>
                  </li>';
    }
    return '<ul class="legend">' . $html . '</ul>';
}

function getPaginationHTML(array $pg)
{
    $html = '';
    foreach ($pg as $p) {
        if ($p['value'] != '...') {
            $activeFlag = ($p['active_flag']) ? 'active' : '';
            $html .= '<a class="table-pagination__item ' . $activeFlag . '" data-page-num="' . $p['value'] . '" href="' . $p['url'] . '">' . $p['value'] . '</a>';
            continue;
        }
        $html .= '<div class="table-pagination__item">' . $p['value'] . '</div>';
    }
    return $html;
}


function getOperationsSlaveAdminHTML($masters = [], $statuses = [])
{
    ob_start();
?>
    <div class="container gutters">
        <div class="row">
            <div class="col-12">
                <div class="table-btn table-btn_mg table-btn_add-filter" data-action="add-filter">Добавить фильтр</div>
                <div class="table-btn table-btn_mg table-btn_del-filter" id="del-filter-btn" style="display: none" data-action="clear-filters">Очистить фильтр</div>
                <div class="table-btn table-btn_mg table-btn_check-all" data-action="check-all">Выбрать все</div>
                <div class="table-btn table-btn_mg table-btn_no-ic" data-action="combine-labels">Объединить наклейки</div>
                <div class="table-btn table-btn_mg  table-btn_no-ic" data-action="combine-receipts">Объединить квитанции</div>
                <div class="table-btn table-btn_mass-change" data-action="toggle-mass-change">Массовое изменение</div>
            </div>
        </div>
        <div class="row" id="mass-change-ctr" style="display: none">
            <?= getMassChangeHTML($masters, $statuses); ?>
        </div>
    </div>
<?
    return ob_get_clean();
}


function getOperationsMasterHTML()
{
    ob_start();
?>
    <div class="container gutters">
        <div class="row">
            <div class="col-12">
                <div class="table-btn table-btn_mg table-btn_add-filter" data-action="add-filter">Добавить фильтр</div>
                <div class="table-btn table-btn_mg table-btn_del-filter" id="del-filter-btn" style="display: none" data-action="clear-filters">Очистить фильтр</div>
            </div>
        </div>
    </div>
<?
    return ob_get_clean();
}



/* Выбор столбца для фильтра */
function getMassChangeHTML(array $masters, array $statuses)
{
    if (!$masters && !$statuses) {
        return '';
    }
    $statusesHTML = '<option value="">Без статуса</option>';
    foreach ($statuses as $st) {
        $statusesHTML .= '<option value="' . $st['name'] . '">' . $st['name'] . '</option>';
    }
    $mastersHTML = '<option value="-1">Без мастера</option>';
    foreach ($masters as $val => $name) {
        $mastersHTML .= '<option value="' . $name['id'] . '">' . $name['full_name'] . '</option>';
    }
    $html = '';
    if ($masters) {
        $html .= '<div class="col-3">
                    <div class="form__cell">
                        <label class="capt">Мастер:</label>
                        <select class="form__select" id="mass-change-master">' . $mastersHTML . '</select>
                    </div>
                 </div>';
    }
    if ($statuses) {
        $html .= '<div class="col-3">
                    <div class="form__cell">
                        <label class="capt">Статус ремонта:</label>
                        <select class="form__select" id="mass-change-status">' . $statusesHTML . '</select>
                    </div>
                 </div>';
    }
    $html .= '<div class="col-3">
                <div class="form__cell">
                    <label class="capt" style="opacity:0">-</label>
                    <button class="form__btn" data-action="mass-change" style="display: block;padding: 16px 13px 16px 13px;border-radius: 0;">Применить к выбранным</button>
                </div>
            </div>';
    return $html;
}
