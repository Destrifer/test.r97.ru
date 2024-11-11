<?php

function getDepotsTableHTML(array $depots)
{
    $html = '';
    foreach ($depots as $depot) {
        $html .= '<tr data-name="depot-row" data-depot-id="' . $depot['id'] . '">
                        <td>' . $depot['id'] . '</td>
                        <td>' . $depot['name'] . '</td>
                        <td>' . $depot['owner'] . '</td>
                        <td>
                        <ul class="table-controls">
                            <li>
                                <a href="/parts/?depot_id=' . $depot['id'] . '" class="table-controls__item table-controls__item_new-window" target="_blank" title="Открыть склад"></a>
                                <a href="/depots/?action=edit&depot-id=' . $depot['id'] . '" class="table-controls__item table-controls__item_edit" title="Редактировать"></a>
                                <a href="/depots/?ajax=del-depot" data-action="del-depot" class="table-controls__item table-controls__item_del" title="Удалить"></a>
                            </li>
                        </ul>
                        </td>
                  </tr>';
    }
    return $html;
}


function getOptionsHTML(array $values, $curVal = null)
{
    $html = '<option value="">- вариант не выбран -</option>';
    if (!$values) {
        return $html;
    }
    foreach ($values as $v) {
        $selFlag = ($curVal == $v['id']) ? 'selected' : '';
        $html .= '<option value="' . htmlspecialchars($v['id']) . '" ' . $selFlag . '>' . htmlspecialchars($v['name']) . '</option>';
    }
    return $html;
}
