<?php

function datatableFilter(array $filter)
{
  $res = [];
  if (isset($filter['draw'])) {
    $res['draw'] = $filter['draw']; // токен для datatables
  }
  if (!empty($filter['search[value]'])) {
    $res['search'] = $filter['search[value]'];
  }
  if (isset($filter['start'])) {
    $res['offset'] = $filter['start'];
  }
  if (isset($filter['length'])) {
    $res['limit'] = $filter['length'];
  }
  return $res;
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
      'DT_RowId' => 'row_' . $rows[$i]['id'],
      'DT_RowData' => ['pkey' => $rows[$i]['id']],
      $rows[$i]['id'],
      $rows[$i]['name'],
      '<a href="/dict/?id=' . $rows[$i]['id'] . '" class="table-controls__item table-controls__item_edit" title="Редактировать"></a>'
    ];
  }
  return $res;
}