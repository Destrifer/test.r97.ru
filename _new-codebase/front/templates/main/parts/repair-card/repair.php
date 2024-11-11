<?php


function getRepairFinalOptionsHTML(array $values, $curVal = null)
{
    $html = '<option value="">-- Выберите --</option>';
    if (!$values) {
        return $html;
    }
    foreach ($values as $id => $name) {
        $selFlag = ($curVal == $id) ? 'selected' : '';
        $html .= '<option value="' . $id . '" ' . $selFlag . '>' . htmlspecialchars($name) . '</option>';
    }
    return $html;
}


function getIssuesOptionsHTML(array $values, $curVal = null)
{
    $html = '<option value="">-- Выберите неисправность --</option>';
    if (!$values) {
        return $html;
    }
    foreach ($values as $id => $name) {
        $selFlag = ($curVal == $id) ? 'selected' : '';
        $html .= '<option value="' . $id . '" ' . $selFlag . '>' . htmlspecialchars($name) . '</option>';
    }
    return $html;
}


function getMastersOptionsHTML(array $values, $curVal = null, $useNoMaster = false, $userRole = '')
{
    $html = '<option value="">-- Выберите мастера --</option>';
    if ($useNoMaster) {
        $html .= '<option value="-1" ' . (($curVal == -1) ? 'selected' : '') . '>Без мастера</option>';
    }
    if (!$values) {
        return $html;
    }
    foreach ($values as $row) {
        $disFlag = ($row['block_flag'] || $userRole == 'taker') ? 'disabled' : '';
        if ($disFlag && $curVal != $row['id']) {
            continue;
        }
        $selFlag = ($curVal == $row['id']) ? 'selected' : '';
        $html .= '<option ' . $disFlag . ' value="' . $row['id'] . '" ' . $selFlag . '>' . htmlspecialchars($row['name']) . '</option>';
    }
    return $html;
}


function getRepairTypeOptionsHTML(array $repairTypes, $problemID = 0, $curRepairType = null)
{
    $html = '<option value="">-- Выберите вариант --</option>';
    if (!isset($repairTypes[$problemID])) {
        return $html;
    }
    foreach ($repairTypes[$problemID] as $row) {
        $selFlag = ($curRepairType == $row['repair_type']) ? 'selected' : '';
        $html .= '<option value="' . $row['repair_type'] . '" ' . $selFlag . '>' . htmlspecialchars($row['name']) . '</option>';
    }
    return $html;
}


function getPartsOptionsHTML(array $rows, $curVal = null, $noInUse = false)
{
    $html = '<option value="">-- Выберите вариант --</option>';
    $html .= (!$noInUse) ? '' : '<option value="-1" ' . ($curVal == -1 ? 'selected' : '') . '>Не использовалась</option>';
    if (!$rows) {
        return $html;
    }
    foreach ($rows as $row) {
        $selFlag = ($curVal == $row['id']) ? 'selected' : '';
        $origFlag = (!empty($row['has_original_flag'])) ? 'has_original' : '';
        $html .= '<option value="' . $row['id'] . '" ' . $selFlag . ' class="' . $origFlag . '">'. $row['part_code'] .' - '. htmlspecialchars($row['name']) .'</option>';
    }
	//$html .= '<pre>' .print_r($rows). '</pre>';
    return $html;
}


function getOptionsHTML(array $rows, $curVal = null)
{
    $html = '<option value="">-- Выберите вариант --</option>';
    if (!$rows) {
        return $html;
    }
    foreach ($rows as $row) {
        $selFlag = ($curVal == $row['id']) ? 'selected' : '';
        $html .= '<option value="' . $row['id'] . '" ' . $selFlag . '>' . htmlspecialchars($row['name']) . '</option>';
    }
    return $html;
}


function getRepairWorkHTML(array $work, array $problems, array $repairTypes, array $parts)
{
    ob_start();
?>
    <div class="col-12" <?= (!empty($work['tpl_flag'])) ? 'id="repair-block-tpl"' : ''; ?> data-block="repair">
        <div class="repair__block">
            <div class="container gutters">

                <div class="row">

                    <div class="col-12">
                        <header class="repair__block-header">
                            <span class="repair__block-title">Ремонт</span>
                            <span data-name="ordered-flag"><label>
                                    <input type="checkbox" <?= (($work['ordered_flag']) ? 'checked' : ''); ?> data-ordered-flag>
                                    <input type="hidden" data-ordered-flag-input name="repair[ordered_flag][]" value="<?= (($work['ordered_flag']) ? '1' : '0'); ?>">
                                    Заказать запчасть</label>
                            </span>
                            <span data-name="own-flag" style="margin-left: 16px;"><label>
                                    <input type="checkbox" <?= (($work['own_flag']) ? 'checked' : ''); ?> data-own-flag>
                                    <input type="hidden" data-own-flag-input name="repair[own_flag][]" value="<?= (($work['own_flag']) ? '1' : '0'); ?>">
                                    Собственная запчасть</label>
                            </span>
                            <span class="repair__block-del-btn" data-action="del-block">Удалить</span>
                        </header>
                    </div>

                </div>

                <section class="row" data-unordered-form>

                    <div class="col-12" data-name="part-name-input">
                        <div class="form__cell" data-error-target="repair[part_id]">
                            <label class="form__label">Выберите неисправную деталь или модуль:</label>
                            <select class="select2" data-input="part-select" style="width: 100%" name="repair[part_id][]" class="form__select">
                                <?= getPartsOptionsHTML($parts, $work['part_id']); ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6">
                        <div class="form__cell">
                            <label class="form__label">Название элемента и/или позиция плате:</label>
                            <input type="text" name="repair[part_pos][]" value="<?= $work['position']; ?>" class="form__text">
                        </div>
                    </div>

                    <div class="col-12 col-sm-2">
                        <div class="form__cell">
                            <label class="form__label">Количество:</label>
                            <input type="text" name="repair[part_qty][]" data-if-own data-input="qty" data-input-filter="int" value="<?= $work['qty']; ?>" class="form__text">
                        </div>
                    </div>

                    <div class="col-12 col-sm-2">
                        <div class="form__cell">
                            <label class="form__label">Цена:</label>
                            <input type="text" name="repair[part_price][]" data-if-own data-input="price" data-input-filter="float" value="<?= $work['price']; ?>" class="form__text <?= (!empty($work['has_price_flag'])) ? 'brown' : ''; ?>">
                        </div>
                    </div>

                    <div class="col-12 col-sm-2">
                        <div class="form__cell">
                            <label class="form__label">Сумма:</label>
                            <input type="text" data-name="sum" readonly value="<?= $work['sum']; ?>" class="form__text <?= (!empty($work['has_price_flag'])) ? 'brown' : ''; ?>">
                        </div>
                    </div>

                    <div class="col-12 col-sm-6">
                        <div class="form__cell">
                            <label class="form__label">Выберите причину и/или способ устранения:</label>
                            <select name="repair[part_problem_id][]" class="form__select" data-input="problems-select">
                                <?= getOptionsHTML($problems, $work['problem_id']); ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6">
                        <div class="form__cell">
                            <label class="form__label">Выберите вид ремонта:</label>
                            <select name="repair[part_repair_type_id][]" class="form__select" data-input="repair-type-select">
                                <?= getRepairTypeOptionsHTML($repairTypes, $work['problem_id'], $work['repair_type_id']); ?>
                            </select>
                        </div>
                    </div>
                </section>
                <!-- / data-unordered-form -->

                <!-- data-ordered-form -->
                <section class="row inactive" data-ordered-form>

                    <input type="hidden" name="repair[part_id][]" data-input="part-id" value="<?= $work['part_id']; ?>">
                    <input type="hidden" name="repair[part_price][]" value="0">
                    <input type="hidden" data-input="part-type-id" value="<?= $work['part_type_id']; ?>">

                    <div class="col-12 col-sm-2">
                        <div class="form__cell form__cell_flex">
                            <button class="repair__select-btn" data-action="choose-part">Выбрать запчасть</button>
                        </div>
                    </div>

                    <div class="col-12 col-sm-5">
                        <div class="form__cell form__cell_flex">
                            <label class="form__label">Наименование детали или блока:</label>
                            <input type="text" name="repair[part_name][]" data-input="part-name" readonly value="<?= $work['name']; ?>" class="form__text">
                        </div>
                    </div>

                    <div class="col-12 col-sm-2">
                        <div class="form__cell form__cell_flex">
                            <label class="form__label">Количество:</label>
                            <input type="text" name="repair[part_qty][]" data-input="part-num" value="<?= $work['qty']; ?>" class="form__text">
                        </div>
                    </div>


                    <div class="col-12 col-sm-3">
                        <div class="form__cell form__cell_flex">
                            <label class="form__label">Обозначение на плате:</label>
                            <input type="text" name="repair[part_pos][]" value="<?= $work['position']; ?>" class="form__text">
                        </div>
                    </div>

                    <div class="col-12 col-sm-6">
                        <div class="form__cell">
                            <label class="form__label">Выберите причину и/или способ устранения:</label>
                            <select name="repair[part_problem_id][]" class="form__select" data-input="problems-select">
                                <?= getOptionsHTML(filterProblemsByType($problems, $work['part_type_id']), $work['problem_id']); ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6">
                        <div class="form__cell">
                            <label class="form__label">Выберите вид ремонта:</label>
                            <select name="repair[part_repair_type_id][]" class="form__select" data-input="repair-type-select">
                                <?= getRepairTypeOptionsHTML($repairTypes, $work['problem_id'], $work['repair_type_id']); ?>
                            </select>
                        </div>
                    </div>

                </section>
                <!-- / data-ordered-form  -->


            </div>
            <input type="hidden" name="repair[id][]" data-input="id" value="<?= $work['id']; ?>">
            <input type="hidden" name="repair[del_flag][]" data-input="del-flag" value="0">
            <input type="hidden" data-input="part-block-type" name="part_block_type[]" value="repair">
        </div>
    </div>

<?php
    return ob_get_clean();
}


function getNonRepairWorkHTML(array $work, array $problems, array $repairTypes, array $parts)
{
    ob_start();
?>
    <div class="col-12" <?= (!empty($work['tpl_flag'])) ? 'id="nonrepair-block-tpl"' : ''; ?> data-block="nonrepair">
        <div class="repair__block">
            <div class="container gutters">

                <div class="row">

                    <div class="col-12">
                        <header class="repair__block-header">
                            <span class="repair__block-title">Без ремонта</span>
                            <span class="repair__block-del-btn" data-action="del-block">Удалить</span>
                        </header>
                    </div>

                </div>

                <div class="row">

                    <div class="col-12 col-sm-6" data-name="part-name-input">
                        <div class="form__cell form__cell_flex" data-error-target="nonrepair[part_id]">
                            <label class="form__label">2Выберите неисправную деталь или модуль:</label>
                            <select class="select2" data-input="part-select" style="width: 100%" name="nonrepair[part_id][]" class="form__select">
                                <?= getPartsOptionsHTML($parts, $work['part_id'], true); ?>
                            </select>
                        </div>
                    </div>


                    <div class="col-12 col-sm-6">
                        <div class="form__cell form__cell_flex">
                            <label class="form__label">Название элемента и/или позиция плате:</label>
                            <input type="text" name="nonrepair[part_pos][]" value="<?= $work['position']; ?>" class="form__text">
                        </div>
                    </div>

                    <div class="col-12 col-sm-6">
                        <div class="form__cell">
                            <label class="form__label">Выберите причину и/или способ устранения:</label>
                            <select name="nonrepair[part_problem_id][]" class="form__select" data-input="problems-select">
                                <?= getOptionsHTML($problems, $work['problem_id']); ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6">
                        <div class="form__cell">
                            <label class="form__label">Выберите вид ремонта:</label>
                            <select name="nonrepair[part_repair_type_id][]" class="form__select" data-input="repair-type-select">
                                <?= getRepairTypeOptionsHTML($repairTypes, $work['problem_id'], $work['repair_type_id']); ?>
                            </select>
                        </div>
                    </div>
                </div>

            </div>
            <input type="hidden" name="notify_admin[]" data-input="notify-admin" value="">
            <input type="hidden" name="nonrepair[id][]" data-input="id" value="<?= $work['id']; ?>">
            <input type="hidden" name="nonrepair[del_flag][]" data-input="del-flag" value="0">
            <input type="hidden" data-input="part-block-type" name="part_block_type[]" value="nonrepair">
        </div>
    </div>

<?php
    return ob_get_clean();
}


function getDiagWorkHTML(array $work, array $problems, array $repairTypes, array $parts)
{
    ob_start();
?>
    <div class="col-12" <?= (!empty($work['tpl_flag'])) ? 'id="diag-block-tpl"' : ''; ?> data-block="diag">
        <div class="repair__block">
            <div class="container gutters">

                <div class="row">

                    <div class="col-12">
                        <header class="repair__block-header">
                            <span class="repair__block-title">Тестирование</span>
                            <span data-name="ordered-flag"><label>
                                    <input type="checkbox" <?= (($work['ordered_flag']) ? 'checked' : ''); ?> data-ordered-flag>
                                    <input type="hidden" data-ordered-flag-input name="diag[ordered_flag][]" value="<?= (($work['ordered_flag']) ? '1' : '0'); ?>">
                                    Заказать деталь</label>
                            </span>
                            <span data-name="own-flag" style="margin-left: 16px;"><label>
                                    <input type="checkbox" <?= (($work['own_flag']) ? 'checked' : ''); ?> data-own-flag>
                                    <input type="hidden" data-own-flag-input name="diag[own_flag][]" value="<?= (($work['own_flag']) ? '1' : '0'); ?>">
                                    Собственная запчасть</label>
                            </span>
                            <span class="repair__block-del-btn" data-action="del-block">Удалить</span>
                        </header>
                    </div>

                </div>

                <section class="row" data-unordered-form>

                    <div class="col-12" data-name="part-name-input">
                        <div class="form__cell" data-error-target="diag[part_id]">
                            <label class="form__label">Выберите неисправную деталь или модуль:</label>
                            <select class="select2" data-input="part-select" style="width: 100%" name="diag[part_id][]" class="form__select">
                                <?= getPartsOptionsHTML($parts, $work['part_id'], true); ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6">
                        <div class="form__cell">
                            <label class="form__label">Название элемента и/или позиция плате:</label>
                            <input type="text" name="diag[part_pos][]" value="<?= $work['position']; ?>" class="form__text">
                        </div>
                    </div>

                    <div class="col-12 col-sm-2">
                        <div class="form__cell">
                            <label class="form__label">Количество:</label>
                            <input type="text" name="diag[part_qty][]" data-if-own data-input="qty" data-input-filter="int" value="<?= $work['qty']; ?>" class="form__text">
                        </div>
                    </div>

                    <div class="col-12 col-sm-2">
                        <div class="form__cell">
                            <label class="form__label">Цена:</label>
                            <input type="text" name="diag[part_price][]" data-if-own data-input="price" data-input-filter="float" value="<?= $work['price']; ?>" class="form__text <?= (!empty($work['has_price_flag'])) ? 'brown' : ''; ?>">
                        </div>
                    </div>

                    <div class="col-12 col-sm-2">
                        <div class="form__cell">
                            <label class="form__label">Сумма:</label>
                            <input type="text" data-name="sum" readonly value="<?= $work['sum']; ?>" class="form__text <?= (!empty($work['has_price_flag'])) ? 'brown' : ''; ?>">
                        </div>
                    </div>

                    <div class="col-12 col-sm-6">
                        <div class="form__cell">
                            <label class="form__label">Выберите причину и/или способ устранения:</label>
                            <select name="diag[part_problem_id][]" class="form__select" data-input="problems-select">
                                <?= getOptionsHTML($problems, $work['problem_id']); ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6">
                        <div class="form__cell">
                            <label class="form__label">Выберите вид диагностики:</label>
                            <select name="diag[part_repair_type_id][]" class="form__select" data-input="repair-type-select">
                                <?= getRepairTypeOptionsHTML($repairTypes, $work['problem_id'], $work['repair_type_id']); ?>
                            </select>
                        </div>
                    </div>
                </section>
                <!-- / data-unordered-form -->

                <!-- data-ordered-form -->
                <section class="row inactive" data-ordered-form>

                    <input type="hidden" name="diag[part_id][]" data-input="part-id" value="<?= $work['part_id']; ?>">
                    <input type="hidden" name="diag[part_price][]" value="0">
                    <input type="hidden" data-input="part-type-id" value="<?= $work['part_type_id']; ?>">

                    <div class="col-12 col-sm-2">
                        <div class="form__cell form__cell_flex">
                            <button class="repair__select-btn" data-action="choose-part">Выбрать запчасть</button>
                        </div>
                    </div>

                    <div class="col-12 col-sm-5">
                        <div class="form__cell form__cell_flex">
                            <label class="form__label">Наименование детали или блока:</label>
                            <input type="text" name="diag[part_name][]" data-input="part-name" readonly value="<?= $work['name']; ?>" class="form__text">
                        </div>
                    </div>

                    <div class="col-12 col-sm-2">
                        <div class="form__cell form__cell_flex">
                            <label class="form__label">Количество:</label>
                            <input type="text" name="diag[part_qty][]" data-input="part-num" value="<?= $work['qty']; ?>" class="form__text">
                        </div>
                    </div>


                    <div class="col-12 col-sm-3">
                        <div class="form__cell form__cell_flex">
                            <label class="form__label">Обозначение на плате:</label>
                            <input type="text" name="diag[part_pos][]" value="<?= $work['position']; ?>" class="form__text">
                        </div>
                    </div>

                    <div class="col-12 col-sm-6">
                        <div class="form__cell">
                            <label class="form__label">Выберите причину и/или способ устранения:</label>
                            <select name="diag[part_problem_id][]" class="form__select" data-input="problems-select">
                                <?= getOptionsHTML(filterProblemsByType($problems, $work['part_type_id']), $work['problem_id']); ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6">
                        <div class="form__cell">
                            <label class="form__label">Выберите вид диагностики:</label>
                            <select name="diag[part_repair_type_id][]" class="form__select" data-input="repair-type-select">
                                <?= getRepairTypeOptionsHTML($repairTypes, $work['problem_id'], $work['repair_type_id']); ?>
                            </select>
                        </div>
                    </div>

                </section>
                <!-- / data-ordered-form  -->
            </div>
            <input type="hidden" name="diag[id][]" data-input="id" value="<?= $work['id']; ?>">
            <input type="hidden" name="diag[del_flag][]" data-input="del-flag" value="0">
            <input type="hidden" data-input="part-block-type" name="part_block_type[]" value="diag">
        </div>
    </div>

<?php
    return ob_get_clean();
}


/**
 * Фильтрует проблемы по типу запчасти
 * 
 * @param array $problems Причины, дефекты
 * @param int $partTypeID Тип запчасти
 * 
 * @return array Отфильтрованный массив
 */
function filterProblemsByType(array $problems, $partTypeID)
{
    if (!$partTypeID) {
        return $problems;
    }
    $res = [];
    foreach ($problems as $problem) {
        if ($problem['type_id'] == $partTypeID) {
            $res[] = $problem;
        }
    }
    return $res;
}


/**
 * Фильтрует типы ремонта по типу запчасти
 * 
 * @param array $types Типы ремонта
 * @param int $partTypeID Тип запчасти
 * 
 * @return array Отфильтрованный массив
 */
function filterRepairTypesByType(array $types, $partTypeID)
{
    if (!$partTypeID) {
        return $types;
    }
    $res = [];
    foreach ($types as $problemID => $typeList) {
        $res[$problemID] = [];
        foreach ($typeList as $type) {
            if (in_array($type['repair_type'], [6, 7, 18])) { // остаются только замены
                $res[$problemID][] = $type;
            }
        }
    }
    return $res;
}


/**
 * Фильтрует причины ремонта если есть заявление об отказе
 * 
 * @param array $problems Причины ремонта
 * 
 * @return array Отфильтрованный массив
 */
function filterProblemsByRefuseDoc(array $problems)
{
    $res = [];
    foreach ($problems as $problem) {
        if (in_array($problem['id'], [35, 57])) { // остаются только "Отказ от ремонта"
            $res[] = $problem;
        }
    }
    return $res;
}
