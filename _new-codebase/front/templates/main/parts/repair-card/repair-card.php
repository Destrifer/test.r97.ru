<?php

echo '<pre>Отладка: $repair перед include repair-card.php: ' . print_r($repair, true) . '</pre>';

function getStepsNavHTML(array $steps)
{
    $html = '<nav class="progress">';
    foreach ($steps as $step) {
        if ($step['cur_flag']) {
            $html .= '<a href="#" class="current">' . $step['name'] . '</a>';
            continue;
        }
        $html .= '<a href="' . $step['url'] . '">' . $step['name'] . '</a>';
    }
    return $html . '</nav>';
}


function getSummaryHTML(array $summary)
{
    $html = '
    
    <aside class="repair-card__summary">
<h3 class="repair-card__summary-title">Ремонт</h3>
<ul>
  <li class="repair-card__summary-item">№ ' . $summary['repair_id'] . ' от ' . $summary['receive_date'] . '</li>';
    if (!empty($summary['statuses'])) {
        $html .= '<li class="repair-card__summary-item"><select id="summary-status-select" data-repair-id="' . $summary['repair_id'] . '" data-status-select class="nomenu repair-card__summary-input">';
        $allDisabled = in_array($summary['status'], ['Запрос на выезд', 'Запрос на монтаж', 'Запрос на демонтаж']);
        foreach ($summary['statuses'] as $status) {
            $selected = ($status == $summary['status']) ? 'selected' : '';
            $disabled = ($allDisabled || in_array($status, ['Выезд подтвержден', 'Выезд отклонен', 'Демонтаж отклонен', 'Демонтаж подтвержден', 'Монтаж отклонен', 'Монтаж подтвержден', 'Подтвержден', 'Есть вопросы'])) ? 'disabled' : '';
            $statusName = (empty($status)) ? 'Без статуса' : $status;
            $html .= '<option ' . $selected . ' value="' . $status . '" ' . $disabled . '>' . $statusName . '</option>';
        }
        $html .= '</select></li>';
        if ($summary['approve_date']) {
            $html .= '<li><input type="text" onfocus="this.select()" class="repair-card__summary-input" style="padding: 1px 3px;" readonly value="' . $summary['approve_date'] . '"></li>';
        }
        $html .= '<li class="repair-card__summary-checkbox-item"><label><input type="checkbox" class="repair-card__summary-ckeckbox" ' . (($summary['has_questions']) ? 'checked' : '') . ' value="1" data-has-questions-checkbox> Есть вопросы</label></li>';
    } else {
        $html .= '<li class="repair-card__summary-item">' . $summary['status'] . '</li>';
    }
    $html .= '</ul>
<h3 class="repair-card__summary-title">Сервис</h3>
<ul>
  <li class="repair-card__summary-item">' . $summary['service_name'] . '</li>
  <li class="repair-card__summary-item" style="max-height:150px;overflow-y:auto">' . $summary['service_address'] . '</li>
</ul>';
    if (!empty($summary['client_name']) || !empty($summary['client_phone']) || !empty($summary['client_address'])) {
        $html .= '<h3 class="repair-card__summary-title">Клиент</h3>
<ul>
<li class="repair-card__summary-item">' . $summary['client_name'] . '</li>
<li class="repair-card__summary-item"><a href="tel:' . $summary['client_phone'] . '" style="color:#fff">' . $summary['client_phone'] . '</a></li>
<li class="repair-card__summary-item">' . $summary['client_address'] . '</li>
</ul>';
    }
    if (!empty($summary['shop_name']) || !empty($summary['shop_phone'])) {
        $html .= '<h3 class="repair-card__summary-title">Магазин</h3>
    <ul>
    <li class="repair-card__summary-item">' . $summary['shop_name'] . '</li>
    <li class="repair-card__summary-item"><a href="tel:' . $summary['shop_phone'] . '" style="color:#fff">' . $summary['shop_phone'] . '</a></li>
    </ul>';
    }
    if (!empty($summary['model_name']) || !empty($summary['serial']) || !empty($summary['defect_client'])) {
        $html .= '<h3 class="repair-card__summary-title">Модель</h3>
    <ul>
    <li class="repair-card__summary-item">' . $summary['model_name'] . '</li>';
        if (!empty($summary['serial'])) {
            $html .= '<li class="repair-card__summary-item repair-card__summary-item_serial">' . $summary['serial'] . '</li>';
        }
        $html .= '<li class="repair-card__summary-item">' . $summary['defect_client'] . '</li>
    </ul>';
    }
    $html .= '</aside>';
    return $html;
}


function getAsideControlsHTML(array $controls = [])
{
    $html = '';
    if (!$controls) {
        return '';
    }
    if (isset($controls['approve'])) {
        $html .= '<a href="#" id="approve-aside-control" title="' . $controls['approve'] . '"><img class="helperzz" src="/img/done (1).png"></a> <br> <br>';
    }
    if (isset($controls['block'])) {
        $html .= '<a href="#" id="block-aside-control" title="' . $controls['block'] . '"><img class="helperzz" src="/img/send-block-btn.png"></a> <br> <br>';
    }
    if (isset($controls['send'])) {
        $html .= '<a href="#" id="send-aside-control" title="' . $controls['send'] . '"><img class="helperzz" src="/img/done (1).png"></a> <br> <br>';
    }
    if (isset($controls['close'])) {
        $html .= '<a href="#" id="close-aside-control" title="' . $controls['close'] . '"><img class="helperzz" src="/img/done (1).png"></a> <br> <br>';
    }
    if (isset($controls['questions'])) {
        $html .= '<a href="#" id="questions-aside-control" title="' . $controls['questions'] . '"><img class="helperzz2" src="/img/question.png"></a> <br> <br>';
    }
    return '<div id="aside-controls" style="position:fixed;left: 20px;top:calc(50% - 151px);">
    ' . $html . '
    </div>';
}
