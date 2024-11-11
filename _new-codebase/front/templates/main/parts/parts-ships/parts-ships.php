<?php

function datatableFilter(array $filter)
{
  $res = [];
  $keys = ['operation', 'part', 'depot', 'user', 'model'];
  foreach ($keys as $k) {
    if (!empty($filter[$k])) {
      $res[$k] = $filter[$k];
    }
  }
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
      $rows[$i]['send_date'],
      $rows[$i]['recip'],
      '<ol class="list list_ol"><li>' . implode('</li><li>', $rows[$i]['part_codes']) . '</li></ol>',
      '<ol class="list list_ol"><li>' . implode('</li><li>', $rows[$i]['part_names']) . '</li></ol>',
      '<ol class="list list_ol"><li>' . implode('</li><li>', $rows[$i]['part_nums']) . ' шт.</li></ol>',
      $rows[$i]['model'],
      $rows[$i]['serial'],
      '<a href="/parts-ship/?id=' . $rows[$i]['id'] . '" class="table-controls__item table-controls__item_edit" title="Редактировать"></a>'
    ];
  }
  return $res;
}
