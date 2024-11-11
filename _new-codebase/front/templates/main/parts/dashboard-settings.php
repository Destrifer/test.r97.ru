<?php

function getColsHTML(array $cols, array $perms)
{
    $html = '';
     foreach ($cols as $uri => $col) {    
        $html .= '<div class="dashboard-settings__row">
            <div class="dashboard-settings__col-name">
                <input class="dashboard-settings__input-text" name="cols[' . $uri . '][name]" style="width: 78%" type="text" value="' . $col['name'] . '">
                <input class="dashboard-settings__input-text" name="cols[' . $uri . '][width]" style="width: 21%" type="number" value="' . $col['width'] . '">
                <input type="hidden" name="cols[' . $uri . '][sort_col]" value="' . $col['sort_col'] . '">
            </div>
            <label class="dashboard-settings__col-role"><input type="checkbox" name="admin[]" ' . ((isset($perms['admin'][$uri])) ? 'checked' : '') . ' value="' . $uri . '"><div class="dashboard-settings__role">Admin</div></label>
            <label class="dashboard-settings__col-role"><input type="checkbox" name="slave-admin[]" ' . ((isset($perms['slave-admin'][$uri])) ? 'checked' : '') . ' value="' . $uri . '"><div class="dashboard-settings__role">Slave admin</div></label>
            <label class="dashboard-settings__col-role"><input type="checkbox" name="inspector[]" ' . ((isset($perms['taker'][$uri])) ? 'checked' : '') . ' value="' . $uri . '"><div class="dashboard-settings__role">Приёмщик</div></label>
            <label class="dashboard-settings__col-role"><input type="checkbox" name="master[]" ' . ((isset($perms['master'][$uri])) ? 'checked' : '') . ' value="' . $uri . '"><div class="dashboard-settings__role">Мастер</div></label>
            <label class="dashboard-settings__col-role"><input type="checkbox" name="service[]" ' . ((isset($perms['service'][$uri])) ? 'checked' : '') . ' value="' . $uri . '"><div class="dashboard-settings__role">РСЦ</div></label>
        </div>';
    }
    return '<div class="dashboard-settings">
    <div class="dashboard-settings__row">
            <div style="width:370px">
                Название/ширина
            </div>
            <div>Учетка</div>
        </div>
        ' . $html . '
    </div>';
}
