<?php


function datatableResponse(array $rows, array $filter, $filterCnt, $totalCnt)
{
    $res = [
        'data' => [],
        'draw' => $filter['draw'],
        'recordsTotal' => $totalCnt,
        'recordsFiltered' => $filterCnt
    ];
    for ($i = 0, $cnt = count($rows); $i < $cnt; $i++) {
        $res['data'][] = [
            '<a href="/disposal-request/?id=' . $rows[$i]['id'] . '" class="table-controls__item table-controls__item_edit" title="Редактировать"></a>',
            $rows[$i]['add_date'] . ', <i>' . $rows[$i]['add_time'] . '</i>',
            $rows[$i]['depot'] . '<a href="/parts/?depot_id=' . $rows[$i]['depot_id'] . '" target="_blank" class="table-controls__item table-controls__item_no-bg table-controls__item_new-window" title="Открыть склад в новом окне"></a>',
            $rows[$i]['parts_num'],
            'DT_RowId' => 'row_' . $rows[$i]['id'],
            'DT_RowData' => ['pkey' => $rows[$i]['id']]
        ];
    }
    return $res;
}
