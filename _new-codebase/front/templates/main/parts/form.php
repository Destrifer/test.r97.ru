<?php

function optionsHTML(array $rows, $cur = null, $first = '-- выбрать --', $valKey = 'name')
{
    if ($first) {
        echo '<option value="">' . $first . '</option>';
    }
    if (!isset($rows[0]['id'])) {
        foreach ($rows as $id => $val) {
            $selected = ($cur !== null && $id == $cur) ? 'selected' : '';
            echo '<option ' . $selected . ' value="' . $id . '">' . $val . '</option>';
        }
    } else {
        foreach ($rows as $row) {
            $selected = ($cur !== null && $row['id'] == $cur) ? 'selected' : '';
            echo '<option ' . $selected . ' value="' . $row['id'] . '">' . $row[$valKey] . '</option>';
        }
    }
}
