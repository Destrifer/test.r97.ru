<?php

function rowHTML(array $row)
{
  echo '<tr>
              <td>' . $row['date'] . '</td>
              <td>' . $row['part'] . '</td>
              <td>' . $row['object2'] . '</td>
              <td>' . $row['operation'] . '</td>
              <td>' . $row['depot'] . '</td>
              <td>' . $row['num'] . '</td>
              <td>' . $row['user'] . '</td>   
         </tr>';
}


function datatableFilter(array $filter, array $cols)
{
  $res = [];
  $keys = ['operation', 'part', 'depot', 'user', 'model', 'date', 'object_id'];
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
  if (isset($filter['order[0][column]'])) {
    $res['sort'] = $cols[$filter['order[0][column]']]['uri'];
  }
  if (isset($filter['order[0][dir]'])) {
    $res['dir'] = $filter['order[0][dir]'];
  }
  return $res;
}


function datatableResponse(array $rows, array $filter, $filterCnt, $totalCnt, $userRole)
{
  $res = [
    'data' => [],
    'draw' => $filter['draw'],
    'recordsTotal' => $totalCnt,
    'recordsFiltered' => $filterCnt
  ];
  for ($i = 0, $cnt = count($rows); $i < $cnt; $i++) {
    if ($userRole != 'service') {
      $rows[$i]['part'] .= ' <a href="/part/?id=' . $rows[$i]['part_id'] . '" target="_blank" class="table-controls__item table-controls__item_no-bg table-controls__item_new-window" title="Открыть страницу в новом окне"></a>';
      if (!empty($rows[$i]['object2_id'])) {
        $rows[$i]['object2'] .= ' <a href="/edit-model/' . $rows[$i]['object2_id'] . '/" target="_blank" class="table-controls__item table-controls__item_no-bg table-controls__item_new-window" title="Открыть страницу в новом окне"></a>';
      }
    }
    $res['data'][] = [
      'DT_RowId' => 'row_' . $rows[$i]['id'],
      'DT_RowData' => ['pkey' => $rows[$i]['id']],
      $rows[$i]['date'],
      $rows[$i]['part_code'],
      $rows[$i]['part'],
      $rows[$i]['object2'],
      $rows[$i]['serial'],
      $rows[$i]['operation'],
      ($rows[$i]['event_type'] == 'in') ? '<span class="green-font">+' . $rows[$i]['num'] . '</span>' : (($rows[$i]['num'] != 0) ? '<span class="red-font">−' . $rows[$i]['num'] . '</span>' : $rows[$i]['num']),
      $rows[$i]['depot'],
      $rows[$i]['balance'],
      $rows[$i]['user']
    ];
  }
  return $res;
}


function emptyRowHTML()
{
  echo '<tr>
            <td colspan="100" class="log__empty-row">
                Данных нет.
            </td>
        </tr>';
}
