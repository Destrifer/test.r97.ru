<?php

function colsTPL(array $cols)
{
    ob_start();
    echo '<tr>';
    foreach ($cols as $col) {
        echo '<th 
                data-col-uri="' . $col['uri'] . '" 
                ' . (($col['is_sortable']) ? 'data-sorting' : '') . ' 
                style="min-width:' . $col['width'] . 'px">
                <span data-col-name>' . $col['name'] . '</span> 
                <span data-resizer></span>
              </th>';
    }
    echo '</tr>';
    ob_get_contents();
}
