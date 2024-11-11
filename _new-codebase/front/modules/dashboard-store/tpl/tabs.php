<?php 

function tabsTPL(array $tabs)
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