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
      'DT_RowId' => 'row_' . $rows[$i]['id'],
      'DT_RowData' => ['pkey' => $rows[$i]['id']],
      $rows[$i]['id'],
      '<input type="text" data-input="ru" data-id="' . $rows[$i]['id'] . '" class="form__text" value="' . $rows[$i]['ru'] . '">',
      '<input type="text" data-input="en" data-id="' . $rows[$i]['id'] . '" class="form__text" value="' . $rows[$i]['en'] . '">'
    ];
  }
  return $res;
}
