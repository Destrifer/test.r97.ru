<?php

function getOptionsHTML(array $values, $curVal = null)
{
    $html = '';
    if (!$values) {
        return $html;
    }
    foreach ($values as $k => $v) {
        $selFlag = ($curVal !== null && $curVal == $k) ? 'selected' : '';
        $html .= '<option value="' . htmlspecialchars($k) . '" ' . $selFlag . '>' . htmlspecialchars($v) . '</option>';
    }
    return $html;
}

function getOptionsReceptHTML(array $values, $curVal = null)
{
    $html = '';
    if (!$values) {
        return $html;
    }
    foreach ($values as $k => $v) {
        $selFlag = ($curVal !== null && $curVal == $k) ? 'selected' : '';
        $warrant = ($k == 1) ? 'id="warranty-status-option"' : '';
        $html .= '<option value="' . $k . '" ' . $selFlag . ' '.$warrant.'>' . $v . '</option>';
    }
    return $html;
}

function getCheckboxesExteriorHTML($name, array $values, $curVals = null)
{
    $html = '';
    if (!$values) {
        return $html;
    }
    foreach ($values as $k => $v) {
        $checkFlag = (is_array($curVals) && in_array($k, $curVals)) ? 'checked' : '';
        $uid = crc32($k);
        $group = ($k == 'НОВЫЙ') ? 'data-group="new"' : 'data-group="old"';
        $html .= '<label for="' . $uid . '" class="form__flag"><input type="checkbox" name="' . $name . '" ' . $group . ' value="' . $k . '" ' . $checkFlag . ' id="' . $uid . '" class="form__checkbox"> ' . $v . '</label>';
    }
    return $html;
}


function getCheckboxesContentsHTML($name, array $values, $curVals = null)
{
    $html = '';
    if (!$values) {
        return $html;
    }
    foreach ($values as $k => $v) {
        $checkFlag = (is_array($curVals) && in_array($k, $curVals)) ? 'checked' : '';
        $uid = crc32($k);
        $group = ($k == 'ПОЛНАЯ') ? 'data-group="full"' : 'data-group="part"';
        $html .= '<label for="' . $uid . '" class="form__flag"><input type="checkbox" name="' . $name . '" ' . $group . ' value="' . $k . '" ' . $checkFlag . ' id="' . $uid . '" class="form__checkbox"> ' . $v . '</label>';
    }
    return $html;
}
