<?php

/* Шапка и тело таблицы без данных */
function getTableHTML(array $cols)
{
    ob_start(); ?>
    <div id="table-header">
        <div class="settings-btn settings-btn_save-width" id="save-table-settings-trig" title="Сохранить настройки ширины"></div>
        <div class="settings-btn settings-btn_open" id="cols-settings-trig" title="Настройки столбцов"></div>
        <div data-scroll-sync="header" class="table__header-viewport">
            <table class="table">
                <thead>
                    <tr>
                        <?php
                        foreach ($cols as $uri => $col) {
                            echo '<th title="' . $col['name'] . '" data-sort-col="' . $col['sort_col'] . '" class="th_' . $uri . '" style="min-width:' . $col['width'] . 'px"><span class="th__name">' . $col['name'] . '</span>
                            <div class="table__col-resizer" data-col-resizer></div>
                            </th>';
                        }
                        ?>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <!-- Тело таблицы -->
    <div data-scroll-sync="body" class="table__rows-viewport">
        <table class="table">
            <tbody id="table-body"></tbody>
        </table>
    </div>
<?php return ob_get_clean();
}


/* Сгруппированные строки таблицы */
function getGroupedRowsHTML(array $rowsTree, array $cols, array $statuses = [], $userRole)
{
    ob_start();
    if (!$rowsTree) {
        echo '<tr class="table__row table__row_message">
        <td colspan="100">Ремонты отсутствуют.</td>
    </tr>';
        return ob_get_clean();
    }
    foreach ($rowsTree as $name => $rows) {
        /* Вывод заголовка группировки */
        echo '<tr class="table__row table__row_group-name">
                <td colspan="100">' . $name . '</td>
            </tr>';
        /* Вывод строк */
        echo getRowsHTML($rows, $cols, $statuses, $userRole);
    }
    return ob_get_clean();
}


/* Строки таблицы */
function getRowsHTML(array $rows, array $cols, array $statuses = [], $userRole)
{
    if (!$rows) {
        return '<tr class="table__row table__row_message">
        <td colspan="100">Ремонты отсутствуют.</td>
    </tr>';
    }
    /* Функции для вывода отдельных столбцов */
    $fn = ['status' => 'get_statusesHTML', 'controls' => 'get_controlsHTML', 'anrp_number' => 'get_anrpNumberHTML'];
    if ($userRole == 'admin') {
        $fn['status'] = 'get_adminStatusHTML';
    } elseif ($userRole == 'taker') {
        $fn['status'] = 'get_inspectorStatusHTML';
    } elseif (!in_array($userRole, ['admin', 'slave-admin'])) {
        $fn['status'] = 'get_statusHTML';
    }
    $html = '';
    foreach ($rows as $row) {
        $html .= '<tr data-repair-id="' . $row['id'] . '" data-master-id="' . $row['master_user_id'] . '" class="' . $row['row_color'] . '">';
        /* Отбор столбцов */
        foreach ($cols as $uri => $col) {
            if (!isset($row[$uri])) {
                $html .= '<p>' . $uri . '</p>';
                continue;
            }
            $html .= '<td data-col="' . $uri . '">';
            if (isset($fn[$uri])) {
                $html .= $fn[$uri]($row, $statuses);
            } else {
                $html .= $row[$uri];
            }
            $html .= '</td>';
        }
        $html .= '</tr>';
    }
    return $html;
}


/* Столбец "Номер АНРП" */
function get_anrpNumberHTML(array $row)
{
    if (empty($row['anrp_number'])) {
        return '';
    }
    return ' <a target="_blank" href="/edit-repair/' . $row['anrp_number'] . '/step/2/" class="link__external">' . $row['anrp_number'] . '</a>';
}


/* Столбец "Статус" (админ) */
function get_adminStatusHTML(array $row, array $statuses)
{
    $html = get_statusesHTML($row, $statuses);
    if ($row['status'] != 'Подтвержден' && $row['status'] != 'Выдан') {
        return $html;
    }
    return $html . '<br>
    <input type="text" class="form__text" data-air-datepicker autocomplete="off" data-approve-date-input value="' . $row['approve_date'] . '">';
}


/* Столбец "Статус" (простой вывод) */
function get_statusHTML(array $row)
{
    return $row['status'];
}


/* Столбец "Статус" (inspector) */
function get_inspectorStatusHTML(array $row)
{
    $html = '';
    if ($row['status'] == 'Подтвержден') {
        $html .= '<option value="Выдан">Выдан</option>
                  <option value="Подтвержден" selected>Подтвержден</option>';
    } elseif ($row['status'] == 'Принят') {
        $html .= '<option value="В работе">В работе</option>
                  <option value="Принят" selected>Принят</option>';
    } elseif ($row['status'] == 'Запрос на выезд') {
        $html .= '<option value="Запрос на выезд" selected>Запрос на выезд</option>
                  <option value="Принят">Принят</option>';
    } elseif ($row['status'] == 'Выезд подтвержден') {
        $html .= '<option value="Выезд подтвержден" selected>Выезд подтвержден</option>
                  <option value="Принят">Принят</option>';
    }
    if ($html) {
        return '<select class="form__select" data-status-select>' . $html . '</select>';
    }
    return $row['status'];
}


/* Столбец "Статус" (все) */
function get_statusesHTML(array $row, array $statuses)
{
    $html = '<option value="">Без статуса</option>';
    $allDisabled = in_array($row['status'], ['Запрос на выезд', 'Запрос на монтаж', 'Запрос на демонтаж']);
    foreach ($statuses as $st) {
        /* Временно убрать статус из списка */
        if ($st['name'] == 'Одобрен акт' && in_array($row['status'], ['Нужны запчасти', 'Запрос у Tesler', 'В обработке', 'Заказ на заводе', 'Ждем з/ч Tesler'])) {
            continue;
        }
        $disFlag = ($allDisabled || in_array($st['name'], ['Выезд подтвержден', 'Выезд отклонен', 'Демонтаж отклонен', 'Демонтаж подтвержден', 'Монтаж отклонен', 'Монтаж подтвержден', 'Подтвержден', 'Есть вопросы'])) ? 'disabled' : '';
        $selFlag = ($st['name'] === $row['status']) ? 'selected' : '';
        $html .= '<option ' . $disFlag . ' value="' . $st['name'] . '" ' . $selFlag . '>' . $st['name'] . '</option>';
    }
    return '<select class="form__select" data-status-select>' . $html . '</select>';
}


/* Столбец "Операции" */
function get_controlsHTML(array $row)
{
    $html = '';
    foreach ($row['controls'] as $uri => $c) {
        if ($uri == 'days') {
            $html .= '<div class="controls__item controls__item_days controls__item_' . $row['days_in']['color'] . '" title="' . $c['name'] . '">' . $row['days_in']['days'] . '</div>';
            continue;
        }
        $act = ($c['action']) ? 'data-control="' . $c['action'] . '"' : '';
        $cl = ($row['attention_flag'] && $uri == 'attention') ? 'active' : '';
        $html .= '<a href="' . $c['url'] . '" ' . $act . ' class="controls__item controls__item_' . $uri . ' ' . $cl . '" title="' . $c['name'] . '"></a>';
    }
    $days = '';
    if (!empty($row['days_in']['time_expired_flag'])) {
        $days = '<div class="tags__item tags__item_expired" data-tag-id="2">Срок истёк</div>';
    }
    $html = '<div class="controls">' . $html . '</div>';
    $html2 = '<div class="tags" data-tags>
        <div class="tags__item tags__item_check" data-tag-id="1" ' . (($row['attention_flag']) ? '' : 'style="display:none"') . '>Проверка</div>
        ' . $days . '
        </div>';
    $html .= $html2;
    return $html;
}
