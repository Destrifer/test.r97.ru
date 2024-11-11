<?php 

function tableHeadHTML(array $cols)
{
  echo '<thead>
  <tr>';
  foreach ($cols as $col) {
    echo '<th data-orderable="' . $col['orderable_flag'] . '">' . $col['name'] . '</th>';
  }
  echo '</tr>
</thead>';
}
