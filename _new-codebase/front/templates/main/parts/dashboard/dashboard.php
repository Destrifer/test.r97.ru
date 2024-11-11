<?php

function getTabsHTML(array $tabs)
{
    $html = '';
    foreach ($tabs as $tab) {
        $activeFlag = ($tab['active_flag']) ? 'tabs__item_active' : '';
        $cnt = ($tab['cnt']) ? ' <span class="tabs__cnt">'.$tab['cnt'].'</span>' : '';
        $specialCnt = ($tab['special_cnt']) ? ' <span class="tabs__cnt tabs__cnt_special">'.$tab['special_cnt'].'</span>' : '';
        $cnt = ($tab['important_cnt']) ? ' <span class="tabs__cnt tabs__cnt_important">'.$tab['important_cnt'].'</span>' : $cnt;
        $html .= '<a class="tabs__item '.$activeFlag.'" style="text-decoration:none;" ' . $activeFlag . ' href="' . $tab['url'] . '">' . $tab['title'] . $cnt.' '.$specialCnt.'</a>';
    }
    return '<nav class="tabs">'.rtrim($html, ' /').'</nav>';
}



function getAdminStatusOptions(array $statuses, $curStatus = null)
{
    $html = '<option value="">Без статуса</option>';
    foreach ($statuses as $st) {
        $selFlag = ($st['name'] === $curStatus) ? 'selected' : '';
        $html .= '<option value="'.$st['name'].'" '.$selFlag.'>'.$st['name'].'</option>';
    }
    return $html;
}