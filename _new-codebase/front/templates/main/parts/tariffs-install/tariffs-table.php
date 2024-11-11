<?php


function rowHTML(array $row)
{
    echo '<tr>
            <td></td>
            <td>' . $row['name'] . '</td>
         </tr>';
}


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
            $rows[$i]['name'],
            '<input readonly type="number" data-cat-id="' . $rows[$i]['id'] . '" data-pass-protect-input data-input="dismant-cost" value="' . $rows[$i]['dismant_cost'] . '" class="form__text" min="0">',
            '<input readonly type="number" data-cat-id="' . $rows[$i]['id'] . '" data-pass-protect-input data-input="install-cost" value="' . $rows[$i]['install_cost'] . '" class="form__text" min="0">',
            'DT_RowId' => 'row_' . $rows[$i]['id'],
            'DT_RowData' => ['pkey' => $rows[$i]['id']]
        ];
    }
    return $res;
}
