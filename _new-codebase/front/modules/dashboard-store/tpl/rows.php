<?php

function rowsTPL(array $rows, array $cols)
{
    $html = '';
    foreach ($rows as $row) {
        $html .= '<tr>';
        foreach ($cols as $col) {
            $html .= '<td>
            ' . $row[$col['uri']] . '
          </td>';
        }
        $html .= '</tr>';
    }
    return $html;
}
