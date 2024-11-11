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
            $rows[$i]['id'],
            $rows[$i]['login'],
            $rows[$i]['nickname'],
            $rows[$i]['service'],
            $rows[$i]['email'],
            $rows[$i]['role_name'],
            statusHTML($rows[$i]),
            operationsHTML($rows[$i]),
            'DT_RowId' => 'row_' . $rows[$i]['id'],
            'DT_RowData' => ['pkey' => $rows[$i]['id']]
        ];
    }
    return $res;
}


function statusHTML(array $user)
{
    $cl = 'green';
    if ($user['status_id'] == 0) {
        $cl = 'grey';
    } else if ($user['status_id'] == 2) {
        $cl = 'red';
    }
    return '<span class="' . $cl . '-font">' . $user['status'] . '</span>';
}


function operationsHTML(array $user)
{
    ob_start();
?>
    <ul class="table-controls" data-controls>
        <li>
            <a href="#" data-user-id="<?= $user['id']; ?>" data-action="change-status" data-value="blocked" style="display: <?= (($user['is_active']) ? '' : 'none'); ?>" class="table-controls__item table-controls__item_block" title="Заблокировать"></a>
            <a href="#" data-user-id="<?= $user['id']; ?>" data-action="change-status" data-value="active" style="display: <?= (($user['is_blocked']) ? '' : 'none'); ?>" class="table-controls__item table-controls__item_restore" title="Разблокировать"></a>
            <a href="/user/?id=<?= $user['id']; ?>" class="table-controls__item table-controls__item_edit" title="Редактировать пользователя"></a>
            <?php if (!in_array($user['role'], ['service', 'admin', 'slave-admin', 'store'])) : ?>
                <a href="/staff/?id=<?= $user['id']; ?>" class="table-controls__item table-controls__item_staff" title="Редактировать персонал"></a>
            <?php endif; ?>
            <a href="/login-like/<?= $user['id']; ?>/" class="table-controls__item table-controls__item_enter" title="Войти как..."></a>
        </li>
    </ul>
<?php
    return ob_get_clean();
}
