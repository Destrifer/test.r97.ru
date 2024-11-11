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


function operationsHTML(array $part, $userRole = '')
{
    ob_start();
    if ($userRole == 'service') {
?>
        <ul class="table-controls">
            <li>
                <a href="/parts-log/?part=<?= $part['id']; ?>" class="table-controls__item table-controls__item_history" title="История" target="_blank"></a>
                <div data-action="select-part" data-part-id="<?= $part['id']; ?>" data-depot-id="<?= $part['depot_id']; ?>" class="table-controls__item table-controls__item_check" title="Выбрать">&nbsp;</div>
                <?php foreach ($part['photos'] as $n => $photoURL) :
                    $cl = (!$n) ? 'table-controls__item table-controls__item_photo' : '';
                ?>
                    <a href="<?= $photoURL; ?>" data-thumb="<?= $photoURL; ?>" data-fancybox="gallery<?= $part['id']; ?>" class="<?= $cl; ?>" title="Фото"></a>
                <?php endforeach; ?>
            </li>
        </ul>
    <?php
    } else {
    ?>
        <ul class="table-controls">
            <li>
                <?php
                if ($part['del_flag']) {
                    echo '<a href="#" data-action="restore-part" data-part-id="' . $part['id'] . '" class="table-controls__item table-controls__item_restore" title="Отменить удаление"></a>';
                } else {
                    echo '<a href="/del-parts/' . $part['id'] . '/" class="table-controls__item table-controls__item_del" title="Удалить" onclick="return confirm(\'Вы уверены, что хотите удалить #' . $part['part_code'] . '?\')"></a>';
                }
                ?>
                <a href="#" data-action="clone-part" data-part-id="<?= $part['id']; ?>" data-depot-id="<?= $part['depot_id']; ?>" class="table-controls__item table-controls__item_copy" title="Копировать"></a>
                <a href="/parts-log/?part=<?= $part['id']; ?>&depot=<?= $part['depot_id']; ?>" class="table-controls__item table-controls__item_history" title="История" target="_blank"></a>
                <div data-action="select-part" data-part-id="<?= $part['id']; ?>" data-depot-id="<?= $part['depot_id']; ?>" class="table-controls__item table-controls__item_check" title="Выбрать">&nbsp;</div>
                <?php foreach ($part['photos'] as $n => $photoURL) :
                    $cl = (!$n) ? 'table-controls__item table-controls__item_photo' : '';
                ?>
                    <a href="<?= $photoURL; ?>" data-thumb="<?= $photoURL; ?>" data-fancybox="gallery<?= $part['id']; ?>" class="<?= $cl; ?>" title="Фото"></a>
                <?php endforeach; ?>
                <a href="/part/?id=<?= $part['id']; ?>" class="table-controls__item table-controls__item_edit" title="Редактировать"></a>
            </li>
        </ul>
<?php
    }
    return ob_get_clean();
}
