<?php

namespace models\repaircard;

use models\Counters;
use models\dicts\Dict;
use models\Models;
use models\Parts;
use models\parts\Balance;
use models\repair\Work;
use models\Sender;
use models\Serials;
use models\staff\Staff;
use models\User;
use program\core;
use program\core\Query;

class Repair extends \models\_Model
{

    public static $message = '';
    public static $errors = [];
    private static $db = null;
    private static $cache = [];
    const TABLE_WORK = 'repairs_work';


    public static function init()
    {
        self::$db = \models\_Base::getDB();
    }


    public static function mainDepotHasPart($partID)
    {
        return (int)(Balance::isEnough(Parts::MAIN_DEPOT_ID, $partID, 1) || Balance::isEnough(2, $partID, 1));
    }


    public static function getIssues()
    {
        return array_column(self::$db->exec('SELECT `id`, `name` FROM `issues` ORDER BY `name`'), 'name', 'id');
    }


    public static function isBlocked(array $repair)
    {
        if (User::hasRole('admin', 'slave-admin', 'taker')) {
            return false;
        }
        if ($repair['service_id'] == 33 || User::getData('id') == $repair['service_id']) {
            if (in_array($repair['status_admin'], ['Запчасти в пути', 'Выезд отклонен', 'Выезд подтвержден', 'Есть вопросы', 'Отклонен', 'Оплачен', 'Принят', 'В работе', 'Одобрен акт'])) {
                return false;
            }
        }
        return true;
    }


    public static function getMasters($serviceID)
    {
        $ret = [];
        if ($serviceID == 33) {
            $rows = Staff::getMasters($serviceID);
        } else {
            $rows = self::$db->exec('SELECT `id`, `name`, `surname`, `third_name` AS thirdname, 1 AS is_active FROM `repairmans` WHERE `service_id` = ' . $serviceID . ' ORDER BY `surname`');
        }
        foreach ($rows as $row) {
            $ret[] = [
                'name' => $row['surname'] . ' ' . $row['name'] . ' ' . $row['thirdname'],
                'id' => (isset($row['user']['id'])) ? $row['user']['id'] : $row['id'],
                'block_flag' => !$row['is_active']
            ];
        }
        return $ret;
    }


    public static function getProblems()
    {
        $ret = ['repair' => [], 'nonrepair' => [], 'diag' => []];
        $rows = self::$db->exec('SELECT `id`, `name`, `work_type`, `type_id` FROM `details_problem` ORDER BY `name`');
        foreach ($rows as $row) {
            if (isset($ret[$row['work_type']])) {
                $ret[$row['work_type']][] = $row;
            }
        }
        return $ret;
    }


    public static function getRepairFinal()
    {
        return Dict::getValues(2);
    }


    /**
     * Возвращает список запчастей для проделанной работы
     * 
     * @param array $repair Данные ремонта
     * @param array $work Данные проделанной работы
     * 
     * @return array Список запчастей
     */
    public static function getParts(array $repair, array $work)
    {
        $serial = Serials::getSerial($repair['serial'], $repair['model_id']);
        $model = Models::getModelByID($repair['model_id']);
        $parts = \models\Parts::getParts(['model_id' => $repair['model_id'], 'cat_id' => $model['cat'], 'serial' => $serial['serial']]);
        $includePartIDs = array_column($work, 'part_id');
        $res = [];
        for ($i = 0, $cnt = count($parts); $i < $cnt; $i++) {
            if (!empty($parts[$i]['user_flag']) && !in_array($parts[$i]['id'], $includePartIDs)) {
                continue; // исключить из списка пользовательскую запчасть
            }
            $res[] = $parts[$i];
        }
        return self::sortParts($res);
    }


    /**
     * Сортировка списка запчастей по правилам
     * 
     * @param array $parts Список запчастей
     * 
     * @return bool Отсортированный список
     */
    private static function sortParts(array $parts)
    {
        if (!$parts) {
            return $parts;
        }
        $std = []; // Стандартные
        $main = []; // Оригинальные
        $hasOriginal = []; // Стандартные с оригиналом
        foreach ($parts as $part) {
            if (!empty($part['has_original_flag'])) {
                $hasOriginal[] = $part;
                continue;
            }
            if ($part['attr_id'] == \models\Parts::ORIG_PART) {
                $main[] = $part;
                continue;
            }
            $std[] = $part;
        }
        return array_merge($std, $main, $hasOriginal);
    }


    public static function getRepairTypes($problemID = 0)
    {
        $whereSQL = '';
        if ($problemID) {
            $whereSQL = ' WHERE p.`problem_id` = ' . $problemID;
        }
        $rows = self::$db->exec('SELECT p.*, d.`name` FROM `problem_link` p LEFT JOIN `repair_type` d ON d.`id` = p.`repair_type` ' . $whereSQL . ' ORDER BY d.`name`');
        return core\RowSet::groupBy('problem_id', $rows);
    }


    public static function save($repairID)
    {
     /*   if (User::hasRole('taker') && $_POST['master_id'] != -1) {
            return self::saveTaker($repairID);
        }*/
        self::checkForm();
        if (self::$errors) {
            self::$message = 'Пожалуйста, исправьте ошибки в форме.';
            return ['errors' => self::$errors, 'message' => self::$message, 'error_flag' => 1];
        }
        $query = new Query('repairs');
        $data = self::prepareData();
        $r = self::$db->exec($query->update($data, $repairID), $query->params);
        if ($r) {
            self::saveCounters($repairID);
            $workIDs = self::saveWork($repairID);
            /* Уведомление sc@test.ru */
            if (isset($_POST['notify_admin']) && empty($_SESSION['bell_is_sent_' . $repairID]) && !empty(array_sum($_POST['notify_admin']))) {
                Sender::use('bell')->to([33])->send('Карточка ' . $repairID . ' оформлена на АНРП при наличии запчасти на складе', 'Ремонт #' . $repairID, '/edit-repair/' . $repairID . '/step/2/');
                $_SESSION['bell_is_sent_' . $repairID] = 1;
            }
            if (User::hasRole('master')) {
                $anrpParam = 0;
                if (!empty($data['repair_final'])) {
                    $anrpParam = ($data['repair_final'] == 2) ? 1 : 2;
                }
                \models\services\Settings::saveSettings('repair', $repairID, ['anrp_value' => $anrpParam]);
            }
            \models\Log::repair(2, 'Ремонт.', $repairID);
            return ['message' => 'Ремонт успешно сохранен.', 'error_flag' => 0, 'work_ids' => $workIDs];
        }
        return ['message' => 'Во время сохранения ремонта произошла ошибка, пожалуйста, обратитесь к администратору.', 'error_flag' => 1];
    }


    public static function saveTaker($repairID)
    {
        $query = new Query('repairs');
        $data = [];
        if (isset($_POST['repair_final'])) {
            $data['repair_final'] = $_POST['repair_final'];
        }
        $data['master_user_id'] = -1;
        $r = self::$db->exec($query->update($data, $repairID), $query->params);
        if ($r) {
            return ['message' => 'Ремонт успешно сохранен.', 'error_flag' => 0, 'work_ids' => []];
        }
        return ['message' => 'Во время сохранения ремонта произошла ошибка, пожалуйста, обратитесь к администратору.', 'error_flag' => 1];
    }


    private static function prepareData()
    {
        $typePrice = self::getRepairTypePrice();
        $data = [];
        if ($_POST['service_id'] == 33) {
            $data['master_user_id'] = $_POST['master_id'];
        } else {
            $data['master_id'] = $_POST['master_id'];
        }
        $data['begin_date'] = core\Time::format($_POST['begin_date'], 'Y-m-d');
        $data['bugs'] = trim($_POST['defect_client']);
        $data['disease'] = $_POST['defect_actual'];
        $data['comment'] = trim($_POST['comment']);
        $data['total_price'] = $typePrice['price'];
        $data['repair_type_id'] = $typePrice['repair_type_id'];
        $data['repair_final_cancel'] = trim($_POST['cancel_reason']);
        if (User::hasRole('service') && !empty($_POST['install_flag']) && $_POST['install_status'] < 2) {
            $data['install_status'] = 2;
        }
        if (User::hasRole('service') && $_POST['status'] == 'Принят') {
            $data['status_admin'] = 'В работе';
        }
        if (isset($_POST['master_notes'])) {
            $data['master_notes'] = trim($_POST['master_notes']);
        }
        if (isset($_POST['repair_final'])) {
            $data['repair_final'] = $_POST['repair_final'];
        }
        $data['parts_cost'] = self::getServicePartsCost();
        return $data;
    }


    private static function getServicePartsCost()
    {
        $cost = 0;
        $types = ['repair', 'diag'];
        foreach ($types as $type) {
            if (!empty($_POST[$type]['part_price'])) {
                for ($i = 0, $cnt = count($_POST[$type]['part_price']); $i < $cnt; $i++) {
                    if (!empty($_POST[$type]['part_qty'][$i])) {
                        $cost += $_POST[$type]['part_price'][$i] * $_POST[$type]['part_qty'][$i];
                    }
                }
            }
        }
        return $cost;
    }


    private static function saveCounters($repairID)
    {
        if (!User::hasRole('service') || empty($_POST['repair']['part_price'])) {
            return;
        }
        if (array_sum($_POST['repair']['part_price']) > 0) {
            Counters::add('has_price', $repairID, User::getData('id'));
        } else {
            Counters::delete('has_price', $repairID, User::getData('id'));
        }
    }


    /** Возвращает цену и тип указанного ремонта */
    private static function getRepairTypePrice()
    {
        $ret = ['repair_type_id' => 0, 'price' => 0];
        $partProblemsIDs = self::getPartProblemsIDs();
        if (!$partProblemsIDs) {
            return $ret;
        }
        /* Типы ремонтов (1-5) получаются по указанным в карточке ремонта part_problem_id */
        $rows = self::$db->exec('SELECT `type_id` FROM `details_problem` WHERE `id` IN (' . core\SQL::IN($partProblemsIDs, false) . ')');
        foreach ($rows as $row) {
            $price = \models\Repair::getRepairCostByTypeID($row['type_id'], $_POST['model_id'], $_POST['service_id']);
            if ($price >= $ret['price']) {
                /* Цена ремонта по максимальному значению. Тип ремонта по максимальной цене */
                $ret['price'] = $price;
                $ret['repair_type_id'] = $row['type_id'];
            }
        }
        return $ret;
    }


    private static function saveWork($repairID)
    {
        $workIDs = [];
        if (!$repairID) {
            return $workIDs;
        }
        $types = ['repair', 'nonrepair', 'diag'];
        foreach ($types as $type) {
            if (!in_array($type, $_POST['part_block_type'])) {
                continue;
            }
            for ($i = 0, $cnt = count($_POST[$type]['part_problem_id']); $i < $cnt; $i++) {
                if (!empty($_POST[$type]['del_flag'][$i])) {
                    Work::deleteWorkByID($_POST[$type]['id'][$i]);
                    continue;
                }
                $data = [];
                $data['repair_id'] = $repairID;
                if (empty($_POST[$type]['part_id'][$i])) {
                    continue;
                }
                if ($_POST[$type]['part_id'][$i] < 0) {
                    $data['name'] = 'Не использовалась';
                } else {
                    $part = Parts::getPartByID2($_POST[$type]['part_id'][$i], false);
                    $data['name'] = ($part) ? $part['name'] : 'Part #' . $_POST[$type]['part_id'][$i] . ' not found.';
                }
                $data['ordered_flag'] = (!empty($_POST[$type]['ordered_flag'][$i])) ? '1' : '';
                $data['own_flag'] = (!empty($_POST[$type]['own_flag'][$i])) ? '1' : '';
                $data['position'] = (!empty($_POST[$type]['part_pos'][$i])) ? trim($_POST[$type]['part_pos'][$i]) : '';
                $data['problem_id'] = $_POST[$type]['part_problem_id'][$i];
                $data['repair_type_id'] = $_POST[$type]['part_repair_type_id'][$i];
                $data['qty'] = (!empty($_POST[$type]['part_qty'][$i])) ? $_POST[$type]['part_qty'][$i] : 0;
                $data['price'] = (!empty($_POST[$type]['part_price'][$i])) ? $_POST[$type]['part_price'][$i] : 0;
                $data['sum'] = (!empty($_POST[$type]['part_price'][$i])) ? $_POST[$type]['part_price'][$i] * $_POST[$type]['part_qty'][$i] : 0;
                $data['part_id'] = (!empty($_POST[$type]['part_id'][$i])) ? $_POST[$type]['part_id'][$i] : -1;
                $workIDs[] = Work::saveWork($data, (!empty($_POST[$type]['id'][$i]) ? $_POST[$type]['id'][$i] : 0));
                // Log::repair(18, 'Запчасть #' . $data['part_id'] . ', со вкладки "Ремонт".', $repairID);
            }
        }
        return $workIDs;
    }




    private static function checkForm()
    {
        if (User::hasRole('taker')) {
            return;
        }
        if (empty($_POST['part_block_type'])) {
            self::addError('work', 'Пожалуйста, добавьте проделанную работу.');
            return;
        }
        if (empty($_POST['master_id'])) {
            self::addError('master_id', 'Пожалуйста, выберите мастера.');
        }
        if (core\Time::isEmpty($_POST['begin_date'])) {
            self::addError('begin_date', 'Пожалуйста, выберите дату начала ремонта.');
        }
        if (!core\Time::isBetween($_POST['begin_date'], '2015-01-01', date('Y-m-d'))) {
            self::addError('begin_date', 'Пожалуйста, введите корректную дату начала ремонта.');
        }
        if (empty($_POST['defect_client'])) {
            self::addError('defect_client', 'Пожалуйста, введите неисправность со слов клиента.');
        }
        if (empty($_POST['defect_actual'])) {
            self::addError('defect_actual', 'Пожалуйста, введите фактическую неисправность.');
        }
        if (User::hasRole('master', 'slave-admin') && empty($_POST['repair_final'])) {
            self::addError('repair_final', 'Пожалуйста, выберите итоги ремонта.');
        }
        if (in_array('repair', $_POST['part_block_type'])) {
            for ($n = 0, $cnt = count($_POST['repair']['part_problem_id']); $n < $cnt; $n++) {
                if (!empty($_POST['repair']['del_flag'][$n])) {
                    continue;
                }
                if (empty($_POST['repair']['ordered_flag'][$n])) {
                    if (empty($_POST['repair']['part_id'][$n])) {
                        self::addError('repair[part_id]', 'Пожалуйста, выберите запчасть.', $n);
                        continue;
                    }
                } else {
                    continue; // не проверять, если заказ запчасти
                }
                if (!empty($_POST['repair']['own_flag'][$n])) {
                    if (empty($_POST['repair']['part_qty'][$n])) {
                        self::addError('repair[part_qty]', 'Пожалуйста, укажите количество запчастей.', $n);
                    }
                    /* if (empty($_POST['repair']['part_price'][$n])) {
                        self::addError('repair[part_price]', 'Пожалуйста, укажите цену запчасти.', $n);
                    } */
                    if (empty($_POST['repair']['part_id'][$n])) {
                        self::addError('repair[part_id]', 'Пожалуйста, выберите запчасть.', $n);
                        continue;
                    }
                }
                if (empty($_POST['repair']['part_problem_id'][$n])) {
                    self::addError('repair[part_problem_id]', 'Пожалуйста, укажите неисправность.', $n);
                    continue;
                }
                if (empty($_POST['repair']['part_repair_type_id'][$n])) {
                    self::addError('repair[part_repair_type_id]', 'Пожалуйста, укажите вид ремонта.', $n);
                    continue;
                }
                if (self::isPartReplacement($_POST['repair']['part_repair_type_id'][$n])) {
                    if (empty($_POST['repair']['part_qty'][$n])) {
                        self::addError('repair[part_qty]', 'Пожалуйста, укажите количество запчастей.', $n);
                    }
                }
            }
        }
        if (in_array('nonrepair', $_POST['part_block_type'])) {
            for ($n = 0, $cnt = count($_POST['nonrepair']['part_problem_id']); $n < $cnt; $n++) {
                if (!empty($_POST['nonrepair']['del_flag'][$n])) {
                    continue;
                }
                if (empty($_POST['nonrepair']['part_problem_id'][$n])) {
                    self::addError('nonrepair[part_problem_id]', 'Пожалуйста, укажите неисправность.', $n);
                    continue;
                }
                if (empty($_POST['nonrepair']['part_repair_type_id'][$n])) {
                    self::addError('nonrepair[part_repair_type_id]', 'Пожалуйста, укажите вид ремонта.', $n);
                }
                if ($_POST['nonrepair']['part_problem_id'][$n] != 44) { // истёк срок гарантийного обслуживания
                    if ($_POST['nonrepair']['part_repair_type_id'][$n] != 5 && empty($_POST['nonrepair']['part_id'][$n])) {
                        self::addError('nonrepair[part_id]', 'Пожалуйста, выберите запчасть.', $n);
                    }
                }
            }
        }
        if (in_array('diag', $_POST['part_block_type'])) {
            for ($n = 0, $cnt = count($_POST['diag']['part_problem_id']); $n < $cnt; $n++) {
                if (!empty($_POST['diag']['del_flag'][$n])) {
                    continue;
                }
                if (empty($_POST['diag']['ordered_flag'][$n])) {
                    if (empty($_POST['diag']['part_id'][$n])) {
                        self::addError('diag[part_id]', 'Пожалуйста, выберите запчасть.', $n);
                        continue;
                    }
                } else {
                    continue; // не проверять, если заказ запчасти
                }
                if (!empty($_POST['diag']['own_flag'][$n])) {
                    if (empty($_POST['diag']['part_qty'][$n])) {
                        self::addError('diag[part_qty]', 'Пожалуйста, укажите количество запчастей.', $n);
                        continue;
                    }
                    /* if (empty($_POST['diag']['part_price'][$n])) {
                        self::addError('diag[part_price]', 'Пожалуйста, укажите цену запчасти.', $n);
                        continue;
                    } */
                    if (empty($_POST['diag']['part_id'][$n])) {
                        self::addError('diag[part_id]', 'Пожалуйста, выберите запчасть.', $n);
                        continue;
                    }
                }
                if (empty($_POST['diag']['part_problem_id'][$n])) {
                    self::addError('diag[part_problem_id]', 'Пожалуйста, укажите неисправность.', $n);
                    continue;
                }
                if (empty($_POST['diag']['part_repair_type_id'][$n])) {
                    self::addError('diag[part_repair_type_id]', 'Пожалуйста, укажите вид тестирования.', $n);
                    continue;
                }
                if (self::isPartReplacement($_POST['diag']['part_repair_type_id'][$n])) {
                    if (empty($_POST['diag']['part_qty'][$n])) {
                        self::addError('diag[part_qty]', 'Пожалуйста, укажите количество запчастей.', $n);
                    }
                }
            }
        }
        $partProblemsIDs = self::getPartProblemsIDs();
        if (!$partProblemsIDs) {
            return;
        }
        if (empty($_POST['cancel_reason'])) {
            foreach ($partProblemsIDs as $problemID) {
                $rows = self::$db->exec('SELECT `repair_name` FROM `details_problem` WHERE `id` = ' . $problemID);
                if (in_array($rows[0]['repair_name'], ['В гарантии отказано', 'Дефект не обнаружен'])) {
                    self::addError('cancel_reason', 'Пожалуйста, заполните причину выдачи акта.');
                    break;
                }
            }
        }
    }


    private static function getPartProblemsIDs()
    {
        if (!isset(self::$cache['part_problems_ids'])) {
            self::$cache['part_problems_ids'] = [];
            if (!empty($_POST['repair']['part_problem_id'])) {
                self::$cache['part_problems_ids'] = $_POST['repair']['part_problem_id'];
            }
            if (!empty($_POST['nonrepair']['part_problem_id'])) {
                self::$cache['part_problems_ids'] = array_merge(self::$cache['part_problems_ids'], $_POST['nonrepair']['part_problem_id']);
            }
            if (!empty($_POST['diag']['part_problem_id'])) {
                self::$cache['part_problems_ids'] = array_merge(self::$cache['part_problems_ids'], $_POST['diag']['part_problem_id']);
            }
            self::$cache['part_problems_ids'] = array_filter(array_unique(self::$cache['part_problems_ids']));
        }
        return self::$cache['part_problems_ids'];
    }


    private static function isPartReplacement($repairTypeID)
    {
        $rows = self::$db->exec('SELECT `name` FROM `repair_type` WHERE `id` = ? ', [$repairTypeID]);
        if (!$rows || mb_strpos($rows[0]['name'], 'амена') === false) {
            return false;
        }
        return true;
    }


    private static function addError($name, $message, $index = 0)
    {
        self::$errors[$name] = ['message' => $message, 'index' => $index];
    }
}

Repair::init();
