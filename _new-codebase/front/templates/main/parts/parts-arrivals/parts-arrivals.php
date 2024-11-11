<?php


function datatableResponse(array $rows, array $filter, array $cols, $filterCnt, $totalCnt, $userRole)
{
    $res = [
        'data' => [],
        'draw' => $filter['draw'],
        'recordsTotal' => $totalCnt,
        'recordsFiltered' => $filterCnt
    ];
    for ($i = 0, $cnt = count($rows); $i < $cnt; $i++) {
        $tableRow = [];
        foreach ($cols as $col) {
            if ($col['uri'] == 'operations') {
                $tableRow[] = operationsHTML($rows[$i], $userRole);
            } elseif($col['uri'] == 'arrival_name') {
                $tableRow[] = '<input class="form__text" data-arrival-part-id="'.$rows[$i]['arrival_part_id'].'" data-input="arrival-name" type="text" value="'.$rows[$i][$col['uri']].'" />';
            } else {
                $tableRow[] = $rows[$i][$col['uri']];
            }
        }
        $tableRow['DT_RowId'] = 'row_' . $rows[$i]['id'];
        $tableRow['DT_RowData'] = ['pkey' => $rows[$i]['id']];
        $res['data'][] = $tableRow;
    }
    return $res;
}


function operationsHTML(array $part)
{
    ob_start();
?>
    <ul class="table-controls">
        <li>
            <a href="/parts-log/?part=<?= $part['id']; ?>&depot=<?= $part['depot_id']; ?>" class="table-controls__item table-controls__item_history" title="История" target="_blank"></a>
        </li>
    </ul>
<?php
    return ob_get_clean();
}
