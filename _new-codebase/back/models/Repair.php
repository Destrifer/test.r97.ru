<?php

namespace models;

use models\brands\Brands;
use models\parts\Balance;
use models\parts\Depots;
use models\parts\Log;
use models\parts\Order;
use models\repair\Work;
use models\repaircard\Parts;
use models\staff\Staff;
use models\tariffsinstall\Tariffs;
use program\core;
use program\core\RowSet;
use program\core\Time;

/** 
 * v. 0.1
 * 2020-10-02
 */

class Repair extends _Model
{
    public static $errors = [];
    private static $db = null;
    private static $repairs = [];
    private static $cache = ['repeated' => []];


    public static function init()
    {
        self::$db = _Base::getDB();
    }


    public static function clearCache()
    {
        self::$cache = ['repeated' => []];
        self::$repairs = [];
    }


    public static function setHasQuestions($repairID, $hasQuestionsValue)
    {
        return self::$db->exec('UPDATE `repairs` SET `has_questions` = ?  
        WHERE `id` = ?', [(int)$hasQuestionsValue, $repairID]);
    }


    public static function hasOwnParts($repairID)
    {
        $rows = self::$db->exec('SELECT SUM(`sum`) as sum FROM `repairs_work` WHERE `repair_id` = ? AND `own_flag` = 1', [$repairID]);
        return !empty($rows[0]['sum']) && ($rows[0]['sum'] > 99); // если стоимость собственной запчасти выше 99 рублей
    }


    /**
     * Устанавливает статус "Прочитано" у ремонта
     * 
     * @param int $repairID Ремонт
     * @param array admin: админ прочитал, service: СЦ прочитал
     * 
     * @return void
     */
    public static function setReadStatus($repairID, array $value)
    {
        $sql = [];
        if (isset($value['admin'])) {
            $sql[] = '`status_admin_read` = ' . (($value['admin']) ? 0 : 1);
        }
        if (isset($value['service'])) {
            $sql[] = '`status_user_read` = ' . (($value['service']) ? 0 : 1);
        }
        self::$db->exec('UPDATE `repairs` SET ' . implode(', ', $sql) . ' 
        WHERE `id` = ?', [$repairID]);
    }


    public static function changeRepairFinal($repairID)
    {
        if (!User::hasRole('service', 'admin')) {
            return;
        }
        $repair = self::getRepairByID($repairID);
        if ($repair['service_id'] == 33) { // ИП Кулиджанов
            return;
        }
        $service = Services::getServiceByID($repair['service_id']);
        $settings = \models\services\Settings::getSettings($repair['id'], $repair['service_id'], $service['country']);
        $final = 0;
        if ($settings && $settings['anrp_value'] != 2) { // аппарат оставлен в СЦ
            $final = 2; // Оставляем у себя (Подтвердилось)
        } else {
            $work = Work::getWork($repairID);
            foreach ($work as $w) {
                if ($w['problem_id'] == 5) { // Дефект отсутствует
                    $final = 1; // Выдаем обратно клиенту (Дефект не обнаружен, Ремонт)
                    break;
                }
                if ($w['problem_id'] == 19) { // Попадание жидкости
                    $final = 3; // Выдаем обратно клиенту (Отказано в гарантии)
                    break;
                }
            }
        }
        self::$db->exec('UPDATE `repairs` SET `repair_final` = ' . $final . ' WHERE `id` = ' . $repairID);
    }


    /**
     * Меняет статус ремонта
     * 
     * @param int $repairID Ремонт
     * @param string $status Новый статус
     * 
     * @return bool Флаг ошибки
     */
    public static function changeStatus($repairID, $status)
    {
        $status = trim($status);
        $query = [
            '`status_admin` = "' . $status . '"'
        ];
        $repair = self::getRepairByID($repairID);
        switch ($status) {
            case 'Подтвержден':
                $query[] = '`has_questions` = 0';
                $query[] = '`repair_done` = 1';
                if (empty($repair['app_date']) && !self::isReturned($repairID)) {
                    $query[] = '`app_date` = "' . date('Y.m.d') . '"';
                    $query[] = '`approve_date` = "' . date('Y-m-d') . '"';
                    \models\Log::repair(12, 'Дата подтверждения: ' . date('d.m.Y') . '.', $repairID);
                }
                if (core\Time::isEmpty($repair['ready_date'])) {
                    $query[] = '`ready_date` = "' . date('Y-m-d') . '"';
                }
                if (core\Time::isEmpty($repair['finish_date'])) {
                    $query[] = '`finish_date` = "' . date('Y-m-d') . '"';
                }
                if (User::hasRole('master') || (!User::hasRole('slave-admin') && $repair['master_user_id'] == 0 && $repair['service_id'] == 33 && $repair['master_app_date'] == '')) {
                    $query[] = '`master_app_date` = "' . date('Y.m.d') . '"';
                }
                if ($repair['service_id'] != 33) {
                    Counters::add('approved', $repairID, $repair['service_id']);
                }
                break;
            case 'На проверке':
                $query[] = '`repair_done` = 1';
                break;
            case 'Запрос ПО':
                $query[] = '`status_admin_read` = 0';
                break;
            case 'Монтаж подтвержден':
            case 'Монтаж отклонен':
                $r = self::setInstallApprovedStatus($repair['id'], ($status == 'Монтаж подтвержден'));
                return $r['error_flag'];
            case 'Демонтаж подтвержден':
            case 'Демонтаж отклонен':
                $r = self::setDismantApprovedStatus($repair['id'], ($status == 'Демонтаж подтвержден'));
                return $r['error_flag'];
            case 'Выезд отклонен':
            case 'Выезд подтвержден':
                $r = self::setOutsideApprovedStatus($repair['id'], ($status == 'Выезд подтвержден'));
                return $r['error_flag'];
            case 'Принят':
                $query[] = '`master_user_id` = 0';
                break;
            case 'Есть вопросы':
                $query[] = '`repair_done` = 0';
                $query[] = '`app_date` = ""';
                $query[] = '`ready_date` = "0000-00-00"';
                $query[] = '`approve_date` = "0000-00-00"';
                $query[] = '`has_questions` = 1';
                break;
            case 'Без статуса':
            case 'Отклонен':
                $query[] = '`repair_done` = 0';
                $query[] = '`has_questions` = 0';
                break;
            case 'Выдан':
                $query[] = '`out_date` = "' . date('Y-m-d') . '"';
                $query[] = '`has_questions` = 0';
                break;
        }
        unset(self::$repairs[$repairID]);
        $r = self::$db->exec('UPDATE `repairs` SET  ' . implode(', ', $query) . ' WHERE `id` = ' . $repairID);
        return $r;
    }

    public static function updateOutDate($repairID)
    {
        if (!User::hasRole('service', 'slave-admin')) {
            return;
        }
        $rows = self::$db->exec('SELECT `out_date` FROM `repairs` WHERE `id` = ?', [$repairID]);
        if (!$rows || !core\Time::isEmpty($rows[0]['out_date'])) {
            return;
        }
        self::$db->exec('UPDATE `repairs` SET `out_date` = ? WHERE `id` = ?', [date('Y-m-d'), $repairID]);
    }

    private static function isReturned($repairID)
    {
        $repair = self::getRepairByID($repairID);
        if (!$repair['return_id']) {
            return false;
        }
        $rows = self::$db->exec('SELECT `out` FROM `returns` WHERE `id` = ?', [$repair['return_id']]);
        if (!$rows) {
            return false;
        }
        return ($rows[0]['out'] == 1);
    }


    public static function getRepairByID($repairID)
    {
        if (!isset(self::$repairs[$repairID])) {
            $rows = self::$db->exec('SELECT * FROM `repairs` WHERE `id` = ?', [$repairID]);
            if (!$rows) {
                throw new \Exception('Ремонт #' . $repairID . ' не найден.');
            }
            $rows[0]['status'] = $rows[0]['status_admin'];
            $rows[0]['repair_type'] = self::getRepairType($rows[0]['repair_type_id']);
            $rows[0]['begin_date'] = core\Time::format($rows[0]['begin_date']);
            $rows[0]['finish_date'] = core\Time::format($rows[0]['finish_date']);
            $rows[0]['deadline_date'] = (!core\Time::isEmpty($rows[0]['receive_date'])) ? date('d.m.Y', strtotime($rows[0]['receive_date'] . ' +45 days')) : '';
            self::$repairs[$repairID] = $rows[0];
        }
        return self::$repairs[$repairID];
    }


    public static function getRepairType($repairTypeID)
    {
        switch ($repairTypeID) {
            case 1:
                return 'Блочный';
            case 2:
                return 'Компонентный';
            case 3:
                return 'Замена аксессуара';
            case 4:
                return 'АНРП';
            case 5:
                return 'АТО';
            default:
                return '';
        }
    }


    /* Проверка на заполненность полей перед отправкой от СЦ к админу */
    public static function verifyBeforeSending($repairID, array $repair = [])
    {
        $repair = ($repair) ? $repair : self::getRepairByID($repairID);
        if (empty($repair['model_id']) || empty($repair['client_type'])) {
            //  return '/edit-repair/' . $repair['id'] . '/?errors=1';
        }
        $work = self::$db->exec('SELECT `name`, `problem_id`, `part_id`, `repair_type_id` FROM `repairs_work` WHERE `repair_id` = ?', [$repair['id']]);
        if (!$work) {
            // return '/edit-repair/' . $repair['id'] . '/step/2/?errors=1';
        }
        foreach ($work as $w) {
            if (empty($w['problem_id']) || (empty($w['part_id']) && empty($w['name'])) || empty($w['repair_type_id'])) {
                // return '/edit-repair/' . $repair['id'] . '/step/2/?errors=1';
            }
        }
        return '';
    }

    public static function isANRP($repairID)
    {
        $rows = self::$db->exec('SELECT `repair_type_id` FROM `repairs` WHERE `id` = ?', [$repairID]);
        if (!$rows) {
            return false;
        }
        if ($rows[0]['repair_type_id'] == 4) {
            return true;
        }
        if ($rows[0]['repair_type_id'] == 5) {
            $rows = self::$db->exec('SELECT `problem_id` FROM `repairs_work` WHERE `repair_id` = ?', [$repairID]);
            if (!$rows) {
                return false;
            }
            foreach ($rows as $row) {
                if (in_array($row['problem_id'], [35, 57])) {
                    return true;
                }
            }
        }
        return false;
    }


    public static function isRepeated($repairID, $modelID = 0, $serial = '')
    {
        if (isset(self::$cache['repeated'][$repairID])) {
            return self::$cache['repeated'][$repairID];
        }
        $serial = trim($serial);
        if (!$modelID || empty($serial)) {
            $rows = self::$db->exec('SELECT `model_id`, `serial` FROM `repairs` WHERE `id` = ?', [$repairID]);
            if (empty($rows[0]['serial']) || empty($rows[0]['model_id'])) {
                return false;
            }
            $modelID = $rows[0]['model_id'];
            $serial = $rows[0]['serial'];
        }
        $q = ($repairID) ? ' AND `id` < ' . $repairID : '';
        $rows = self::$db->exec('SELECT COUNT(*) AS cnt FROM `repairs` 
        WHERE `serial` = ? AND `model_id` = ? AND `deleted` = 0' . $q, [$serial, $modelID]);
        if (!$repairID) {
            return $rows[0]['cnt'] > 0;
        }
        self::$cache['repeated'][$repairID] = $rows[0]['cnt'] > 0;
        return self::$cache['repeated'][$repairID];
    }


    public static function getRepeatedRepairs($repairID, $serial = '', $modelID = 0)
    {
        if (empty($serial) || !$modelID) {
            $rows = self::$db->exec('SELECT `model_id`, `serial` FROM `repairs` WHERE `id` = ?', [$repairID]);
            $serial = $rows[0]['serial'];
            $modelID = $rows[0]['model_id'];
        }
        $rows = self::$db->exec('SELECT * FROM `repairs` 
        WHERE `serial` = ? AND `model_id` = ? AND `deleted` = ? AND `id` != ?', [trim($serial), $modelID, 0, $repairID]);
        if (!$rows) {
            return [];
        }
        for ($i = 0, $cnt = count($rows); $i < $cnt; $i++) {
            $rows[$i]['repair_name'] = self::getRepairResult($rows[$i]['id']);
        }
        return $rows;
    }


    /**
     * Возвращает результат ремонта (АНРП/АТО и др.)
     * 
     * @param int $repairID Ремонт
     * 
     * @return string Результат ремонта
     */
    public static function getRepairResult($repairID)
    {
        $rowsN = self::$db->exec('SELECT `repair_name` FROM `details_problem` WHERE `id` IN (SELECT `problem_id` FROM `repairs_work` WHERE `repair_id` = ?)', [$repairID]);
        if (!$rowsN) {
            return '';
        }
        return implode(', ', array_unique(array_column($rowsN, 'repair_name')));
    }


    /**
     * Подтверждает/отклоняет выездной ремонт
     * 
     * @param int $repairID Ремонт
     * @param bool $isApproved Флаг подтверждения
     * @param string $comment Комментарий
     * 
     * @return array Флаг результата и сообщение
     */
    public static function setOutsideApprovedStatus($repairID, $isApproved, $comment = '')
    {
        $repair = self::getRepairByID($repairID);
        if (!$repair) {
            return ['message' => 'Ремонт #' . $repairID . ' не найден.', 'error_flag' => 1];
        }
        if ($isApproved) {
            $cost = Transport::getCost($repair['onway_type'], $repair['service_id'], $repair['cat_id']);
            $newStatus = 'Выезд подтвержден';
            $onway = 1;
        } else {
            $cost = 0;
            $newStatus = 'Выезд отклонен';
            $onway = 0;
        }
        $commentSQL = ($comment) ? '`onway_comment` = "' . trim($comment) . '",' : '';
        $r = self::$db->exec('UPDATE `repairs` SET `onway` = ?, `transport_cost` = ?, ' . $commentSQL . ' `status_admin` = ? WHERE `id` = ?', [$onway, $cost, $newStatus, $repairID]);
        if (!$r) {
            return ['message' => self::$db->getErrorInfo(), 'error_flag' => 1];
        }
        if ($newStatus != $repair['status']) {
            $r = self::$db->exec('UPDATE `repairs` SET `status_admin` = ? WHERE `id` = ?', [$newStatus,  $repairID]);
        }
        if ($comment) {
            \models\repaircard\Support::sendMessage($comment, $repairID);
        }
        self::clearCache();
        return ['message' => $newStatus . '.', 'new_status' => $newStatus, 'error_flag' => 0];
    }


    /**
     * Подтверждает/отклоняет монтаж
     * 
     * @param int $repairID Ремонт
     * @param bool $isApproved Флаг подтверждения
     * @param string $comment Комментарий
     * 
     * @return array Флаг результата и сообщение
     */
    public static function setInstallApprovedStatus($repairID, $isApproved, $comment = '')
    {
        $repair = self::getRepairByID($repairID);
        if (!$repair) {
            return ['message' => 'Ремонт #' . $repairID . ' не найден.', 'error_flag' => 1];
        }
        if ($isApproved) {
            $newStatus = 'Монтаж подтвержден';
            $cost = Tariffs::getInstallCost($repair['cat_id']);
        } else {
            $newStatus = 'Монтаж отклонен';
            $cost = 0;
        }
        $newInstallStatus = ($isApproved) ? 3 : 1;
        $r = self::$db->exec('UPDATE `repairs` SET `install_status` = ?, `install_cost` = ?, `status_admin` = ? WHERE `id` = ?', [$newInstallStatus, $cost, $newStatus, $repairID]);
        if (!$r) {
            return ['message' => self::$db->getErrorInfo(), 'error_flag' => 1];
        }
        if ($newStatus != $repair['status']) {
            $r = self::$db->exec('UPDATE `repairs` SET `status_admin` = ? WHERE `id` = ?', [$newStatus,  $repairID]);
        }
        if ($comment) {
            \models\repaircard\Support::sendMessage($comment, $repairID);
        }
        self::clearCache();
        return ['message' => $newStatus . '.', 'new_status' => $newStatus, 'error_flag' => 0];
    }


    /**
     * Подтверждает/отклоняет демонтаж
     * 
     * @param int $repairID Ремонт
     * @param bool $isApproved Флаг подтверждения
     * @param string $comment Комментарий
     * 
     * @return array Флаг результата и сообщение
     */
    public static function setDismantApprovedStatus($repairID, $isApproved, $comment = '')
    {
        $repair = self::getRepairByID($repairID);
        if (!$repair) {
            return ['message' => 'Ремонт #' . $repairID . ' не найден.', 'error_flag' => 1];
        }
        if ($isApproved) {
            $newStatus = 'Демонтаж подтвержден';
            $cost = Tariffs::getDismantCost($repair['cat_id']);
        } else {
            $newStatus = 'Демонтаж отклонен';
            $cost = 0;
        }
        $r = self::$db->exec('UPDATE `repairs` SET `install_status` = ?, `dismant_cost` = ?, `status_admin` = ? WHERE `id` = ?', [(int)$isApproved, $cost, $newStatus, $repairID]);
        if (!$r) {
            return ['message' => self::$db->getErrorInfo(), 'error_flag' => 1];
        }
        if ($newStatus != $repair['status']) {
            $r = self::$db->exec('UPDATE `repairs` SET `status_admin` = ? WHERE `id` = ?', [$newStatus,  $repairID]);
        }
        if ($comment) {
            \models\repaircard\Support::sendMessage($comment, $repairID);
        }
        self::clearCache();
        return ['message' => $newStatus . '.', 'new_status' => $newStatus, 'error_flag' => 0];
    }


    /**
     * Проверяет, должен ли мастер подтверждать взятие ремонта в работу
     * 
     * @param int $repairID Ремонт
     * @param int $masterID Мастер
     * 
     * @return array Флаг результата и сообщение
     */
    public static function needToConfirmMaster($repairID, $masterID)
    {
        $repair = self::getRepairByID($repairID);
        if ($repair['status_ship_id'] != 3) { // если ремонт не повторный
            return ['need_to_confirm_flag' => 0, 'message' => 'Ремонт #' . $repairID . ' не повторный.'];
        }
        $rows = self::$db->exec(
            'SELECT `master_user_id` FROM `repairs` 
                                WHERE `serial` = ? AND `model_id` = ? AND `deleted` = 0 AND `master_user_id` != 0 AND `id` != ? 
                                ORDER BY `id` DESC LIMIT 1',
            [trim($repair['serial']), $repair['model_id'], $repairID]
        );
        if (!$rows) {
            return ['need_to_confirm_flag' => 0, 'message' => 'Предыдущий ремонт не найден.'];
        }
        $lastMasterID = $rows[0]['master_user_id'];
        if ($lastMasterID == $masterID) { // у ремонта тот же мастер
            return ['need_to_confirm_flag' => 0, 'message' => ''];
        }
        $lastMaster = Staff::getStaff(['id' => $lastMasterID]);
        if (!$lastMaster) {
            return ['need_to_confirm_flag' => 0, 'message' => 'Предыдущий мастер не найден.'];
        }
        return ['need_to_confirm_flag' => 1, 'message' => 'Данный товар поступил повторно и предыдущий ремонт был закрыт мастером ' . $lastMaster['full_name'] . '. Вы уверены, что хотите его взять в работу?'];
    }


    /**
     * Создает ремонт на основе выбранного
     * 
     * @param int $repairID Выбранный ремонт
     * 
     * @return int ID нового ремонта
     */
    public static function createPrototype($repairID)
    {
        $repair = self::getRepairByID($repairID);
        if (!$repair) {
            return 0;
        }
        if (!User::hasRole('admin', 'slave-admin', 'taker') && $repair['service_id'] != User::getData('id')) {
            return 0;
        }
        $newRepairID = self::$db->exec('INSERT INTO `repairs` (`client_type`, `rsc`, `receive_date`, `talon`, 
        `name_shop`, `phone_shop`, `address_shop`, `status_id`, `sell_date`, `client`, `phone`, 
        `address`, `model_id`, `status_ship_id`, `complex`, `visual`, `visual_comment`, `bugs`, `comment`,  
        `refuse_doc_flag`, `status_admin`, `service_id`, `install_status`, `anrp_number`,
        `onway`, `onway_type`) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
            $repair['client_type'],
            $repair['rsc'], $repair['receive_date'], $repair['talon'], $repair['name_shop'], $repair['phone_shop'],
            $repair['address_shop'], $repair['status_id'], $repair['sell_date'], $repair['client'], $repair['phone'],
            $repair['address'], $repair['model_id'], $repair['status_ship_id'], $repair['complex'], $repair['visual'],
            $repair['visual_comment'], $repair['bugs'], $repair['comment'], $repair['refuse_doc_flag'],
            'Принят', $repair['service_id'], $repair['install_status'], $repair['anrp_number'], $repair['onway'], $repair['onway_type']
        ]);
        if ($newRepairID) {
            \models\Log::repair(10, 'Создан на основе ремонта #' . $repairID . '.', $newRepairID);
            return $newRepairID;
        }
        return 0;
    }


    public static function delRepair($repairID, $permFlag = false)
    {
        if (!$permFlag) {
            return self::$db->exec('UPDATE `repairs` SET `deleted` = 1 WHERE `id` = ?', [$repairID]);
        }
        self::$db->exec('DELETE FROM `counters` WHERE `repair_id` = ?', [$repairID]);
        self::$db->exec('DELETE FROM `repairs_parts` WHERE `repair_id` = ?', [$repairID]);
        self::$db->exec('DELETE FROM `feedback_admin` WHERE `repair_id` = ?', [$repairID]);
        self::$db->exec('DELETE FROM `repairs_photo` WHERE `repair_id` = ?', [$repairID]);
        self::$db->exec('DELETE FROM `repairs_work` WHERE `repair_id` = ?', [$repairID]);
        return self::$db->exec('DELETE FROM `repairs` WHERE `id` = ?', [$repairID]);
    }

    public static function setMaster($masterID, $repairID)
    {
        $newStatus = ((!$masterID) ? 'Принят' : 'В работе');
        $repair = Repair::getRepairByID($repairID);
        if ($newStatus != $repair) {
            \models\Log::repair(1, '"' . $repair['status'] . '" на "' . $newStatus . '", при назначении мастера.', $repairID);
        }
        if ($masterID) {
            $master = Staff::getStaff(['id' => $masterID]);
            if (!$master) {
                return false;
            }
            \models\Log::repair(15, $master['full_name'] . '.', $repairID);
        }
        unset(self::$repairs[$repairID]);
        return self::$db->exec('UPDATE `repairs` SET `master_user_id` = ' . $masterID . ', 
        `status_admin` = "' . $newStatus . '", 
        `begin_date` = "' . date('Y-m-d') . '" 
        WHERE `id` = ' . $repairID);
    }


    public static function getMaster($repairID)
    {
        $rows = self::$db->exec('SELECT `master_id`, `master_user_id` FROM `repairs` WHERE `id` = ?', [$repairID]);
        if (!$rows || empty($rows[0]['master_id']) && empty($rows[0]['master_user_id'])) {
            return '';
        }
        if (!empty($rows[0]['master_id'])) {
            $rowsM = self::$db->exec('SELECT `name`, `surname`, `third_name` AS thirdname FROM `repairmans` WHERE `id` = ?', [$rows[0]['master_id']]);
        } else {
            $rowsM = self::$db->exec('SELECT `name`, `surname`, `thirdname` FROM `staff` WHERE `user_id` = ?', [$rows[0]['master_user_id']]);
        }
        if (!$rowsM) {
            return '';
        }
        return trim($rowsM[0]['surname'] . ' ' . $rowsM[0]['name'] . ' ' . $rowsM[0]['thirdname']);
    }


    public static function setApproveDate($date, $repairID)
    {
        $d = explode('.', $date);
        if (count($d) != 3) {
            return false;
        }
        $repair = self::getRepairByID($repairID);
        if ($repair['approve_date'] != '0000-00-00') {
            $log = 'Старая дата: ' . Time::format($repair['approve_date']) . ', новая дата: ' . $date . '.';
        } else {
            $log = 'Старая дата пуста, новая дата: ' . $date . '.';
        }
        unset(self::$repairs[$repairID]);
        \models\Log::repair(16, $log, $repairID);
        return self::$db->exec('UPDATE `repairs` SET 
        `approve_date` = "' . $d[2] . '-' . $d[1] . '-' . $d[0] . '", 
        `ready_date` = "' . $d[2] . '-' . $d[1] . '-' . $d[0] . '", 
        `app_date` = "' . $d[2] . '.' . $d[1] . '.' . $d[0] . '" 
        WHERE `id` = ' . $repairID);
    }


    /**
     * Применяет цену запчастей, отмеченных флагом "Собственная запчасть" к запчастям в ремонте
     * 
     * @param int $repairID Ремонт
     * 
     * @return array Флаг ошибки, сообщение
     */
    public static function applyPartsPrice($repairID)
    {
        $repair = self::getRepairByID($repairID);
        if (!in_array($repair['status_id'], [1, 5])) { // только для гарант. и усл.-гарант.
            return ['message' => '', 'error_flag' => 0];
        }
        $work = Work::getRepairWorkByID($repairID);
        $totalSum = 0;
        $cnt = 0;
        foreach ($work as $w) {
            if ($w['part_id'] <= 0) {
                continue;
            }
            $part = \models\Parts::getPartByID2($w['part_id']);
            if (!empty($part['own_flag'])) {
                $cnt++;
                $sum = $w['qty'] * $part['price'];
                $totalSum += $sum;
                $r = self::$db->exec('UPDATE `' . Work::TABLE . '` SET `price` = ?, `sum` = ? WHERE `id` = ?', [$part['price'], $sum, $w['id']]);
                if (!$r) {
                    return ['message' => 'Ошибка при обновлении запчасти: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
                }
            }
        }
        if ($cnt) {
            $r = self::$db->exec('UPDATE `repairs` SET `parts_cost` = ? WHERE `id` = ?', [$totalSum, $repairID]);
            if (!$r) {
                return ['message' => 'Ошибка при обновлении ремонта: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
            }
            unset(self::$repairs[$repairID]);
        }
        return ['message' => '', 'error_flag' => 0];
    }


    /**
     * Определяет, должен ли СЦ сохранить себе запчасти на склад "Разбор"
     * 
     * @param int $repairID Ремонт
     * 
     * @return bool
     */
    public static function needToSaveParts($repairID)
    {
        if (User::hasRole('taker')) {
            return false;
        }
        $repair = self::getRepairByID($repairID);
        $work = Work::getWork($repairID);
        foreach ($work as $w) {
            if (in_array($w['problem_id'], [60, 5, 3, 14, 15, 16, 18, 19, 41, 43, 44, 50, 59])) {
                return false;
            }
        }
        if (!in_array($repair['repair_type_id'], [4, 5])) {
            return false;
        }
        $service = Services::getServiceByID($repair['service_id']);
        $settings = \models\services\Settings::getSettings($repair['id'], $repair['service_id'], $service['country']);
        if ($repair['service_id'] == 33) { // ИП Кулиджанов
            if ($repair['repair_final'] == 2) {
                $model = Models::getModelByID($repair['model_id']);
                $brand = Brands::getBrand(['name' => $model['brand']]);
                if (empty($brand['user_depot_flag'])) {
                    return false;
                }
            }
        }
        if (!empty($settings['anrp_value']) && $settings['anrp_value'] == 1) {
            return true;
        }
        /* if ($repair['service_id'] == 33) { // ИП Кулиджанов
            if ($repair['repair_final'] == 2) {
                $model = Models::getModelByID($repair['model_id']);
                $brand = Brands::getBrand(['name' => $model['brand']]);
                if (!empty($brand['user_depot_flag'])) {
                    return true;
                }
            }
            return false;
        }
        if (!empty($settings['anrp_value']) && $settings['anrp_value'] == 1) {
            return true;
        } */
        return false;
    }


    /**
     * Возвращает список запчастей, которые СЦ может сохранить на своем складе "Разбор"
     * 
     * @param int $repairID Ремонт
     * 
     * @return array Список запчастей
     */
    public static function getNeedToSaveParts($repairID)
    {

        function isAllowedPart($name)
        {
            return preg_match('/Компресс|Плата подсветки|LCD пан|ЖК Панель/iu', $name);
        }

        $repair = self::getRepairByID($repairID);
        $savedParts = (!empty($repair['saved_parts'])) ? json_decode($repair['saved_parts'], true) : [];
        $serial = Serials::getSerial($repair['serial'], $repair['model_id']);
        $model = Models::getModelByID($repair['model_id']);
        $parts = \models\Parts::getParts(['model_id' => $repair['model_id'], 'cat_id' => $model['cat'], 'serial' => $serial['serial']]);
        $workParts = RowSet::orderBy('part_id', Work::getWork($repairID));
        $res = [];
        for ($i = 0, $cnt = count($parts); $i < $cnt; $i++) {
            $partID = $parts[$i]['id'];
            if (isset($workParts[$partID])) { // находящиеся в ремонте не выводить
                continue;
            }
            if (!empty($parts[$i]['has_original_flag']) || $parts[$i]['user_flag'] || ($parts[$i]['type_id'] == 2 && !isAllowedPart($parts[$i]['name']))) { // компонентные не выводить (за исключением некоторых...)
                continue;
            }
            if (User::hasRole('master')) {
                $parts[$i]['saved_flag'] = false;
            } else if (User::hasRole('admin')) {
                if (!$savedParts) {
                    $parts[$i]['saved_flag'] = true;
                } else {
                    $parts[$i]['saved_flag'] = isset($savedParts[$partID]);
                }
            } else {
                $parts[$i]['saved_flag'] = true;
            }
            $parts[$i]['saved_qty'] = (isset($savedParts[$partID])) ? $savedParts[$partID] : 1;
            $res[] = $parts[$i];
        }
        return $res;
    }


    public static function saveNeedPartsWindow(array $data)
    {
        global $config;
        if (empty($data['repair_id'])) {
            return ['message' => 'Недостаточно параметров.', 'error_flag' => 1];
        }
        if (User::hasRole('service')) {
            if (empty($data['checked'])) {
                return ['message' => 'Пожалуйста, выберите запчасти.', 'error_flag' => 1];
            }
            if (!array_filter($data['part_qty'])) {
                return ['message' => 'Пожалуйста, введите количество.', 'error_flag' => 1];
            }
        } else if (User::hasRole('master')) {
            if (empty($data['checked']) || ($data['parts_total_cnt'] >= $config['min_parts_master'] && count($data['checked']) < $config['min_parts_master'])) {
                return ['message' => 'Выберите исправные позиции поступившие в ремонт.', 'error_flag' => 1];
            }
        } else {
            if (empty($data['checked'])) {
                return ['message' => '', 'error_flag' => 0];
            }
        }
        $repair = self::getRepairByID($data['repair_id']);
        /*  
        Если "Предторговый, полная комплектация", то 
        СЦ обязан выбрать все оригинальные запчасти 
        */
        $needSaveAllFlag = (!User::hasRole('admin') && $repair['status_ship_id'] == 1 && trim($repair['complex'], ' |') == 'ПОЛНАЯ');
        $res = [];
        foreach ($data['part_qty'] as $partID => $qty) {
            if (isset($data['checked'][$partID])) {
                $res[$partID] = $qty;
            } else if ($needSaveAllFlag) {
                $part = \models\Parts::getPartByID2($partID);
                if ($part['attr_id'] == \models\Parts::ORIG_PART) {
                    return ['message' => 'Пожалуйста, выберите все оригинальные запчасти.', 'error_flag' => 1];
                }
            }
        }
        self::$db->exec('UPDATE `repairs` SET `saved_parts` = ? WHERE `id` = ?', [json_encode($res), $data['repair_id']]);
        return ['message' => '', 'error_flag' => 0];
    }


    /**
     * Записывает стоимость монтажа/демонтажа
     * 
     * @param int $repairID Ремонт
     * 
     * @return array Сообщение и флаг ошибки
     */
    public static function updateInstallCost($repairID)
    {
        $repair = self::getRepairByID($repairID);
        if (empty($repair['install_status']) || empty($repair['cat_id'])) {
            return ['message' => '', 'error_flag' => 0];
        }
        $costs = Tariffs::getCosts(['cat_id' => $repair['cat_id']]);
        if (empty($costs[0])) {
            return ['message' => '', 'error_flag' => 0];
        }
        if ($repair['install_status'] == 1) { // демонтаж
            $r = self::$db->exec('UPDATE `repairs` SET `dismant_cost` = ? WHERE `id` = ?', [$costs[0]['dismant_cost'], $repairID]);
        } else { // монтаж и демонтаж
            $r = self::$db->exec('UPDATE `repairs` SET `dismant_cost` = ?, `install_cost` = ? WHERE `id` = ?', [$costs[0]['dismant_cost'], $costs[0]['install_cost'], $repairID]);
        }
        if (!$r) {
            return ['message' => self::$db->getErrorInfo(), 'error_flag' => 1];
        }
        return ['message' => '', 'error_flag' => 0];
    }


    /**
     * Добавляет на склад СЦ "Разбор" сохраненные в ремонте запчасти
     * 
     * @param int $repairID Ремонт
     * 
     * @return array Сообщение и флаг ошибки
     */
    public static function moveSavedPartsToService($repairID)
    {
        $repair = self::getRepairByID($repairID);
        if (empty($repair['saved_parts'])) {
            return ['message' => 'Запчастей нет.', 'error_flag' => 0];
        }
        $savedParts = json_decode($repair['saved_parts'], true);
        self::$db->transact('begin');
        $depotData = ['user_id' => $repair['service_id'], 'name' => 'Разбор'];
        $depot = Depots::getDepot($depotData);
        if (!$depot) {
            $depot = Depots::addDepot($depotData);
        }
        foreach ($savedParts as $partID => $qty) {
            $partID = \models\Parts::createOriginalPart($partID, $repairID);
            if (!$partID) {
                $r = ['message' => 'Ошибка при создании оригинальной запчасти.', 'error_flag' => 1];
                self::$db->transact('rollback');
                return $r;
            }
            $r = Log::collect($partID, $depot['id'], $qty, Balance::count($partID, $depot['id']), $repairID, $repair['model_id'], $repair['serial']);
            if (!$r) {
                $r = ['message' => 'Ошибка логирования: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
                self::$db->transact('rollback');
                return $r;
            }
            $r = Balance::add($partID, $qty, $depot['id']);
            if (!$r) {
                $r = ['message' => 'Ошибка добавления на баланс: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
                self::$db->transact('rollback');
                return $r;
            }
        }
        $r = self::$db->exec('UPDATE `repairs` SET `saved_parts` = "" WHERE `id` = ?', [$repairID]);
        if (!$r) {
            $r = ['message' => self::$db->getErrorInfo(), 'error_flag' => 1];
            self::$db->transact('rollback');
            return $r;
        }
        self::$db->transact('commit');
    }


    public static function rejectRepair($repairID)
    {
        $repair = \models\Repair::getRepairByID($repairID);
        $price = self::getRepairCostByTypeID(4, $repair['model_id'], $repair['service_id']); // АНРП
        self::$db->exec('UPDATE `repairs` SET `total_price` = ?, `repair_type_id` = 4 WHERE `id` = ?', [$price, $repairID]);
    }


    public static function getRepairCostByTypeID($repairTypeID, $modelID, $serviceID)
    {
        if (!$repairTypeID) {
            return 0;
        }
        $rows = self::$db->exec('SELECT `cat` FROM `models` WHERE `id` = ?', [$modelID]);
        if (empty($rows[0]['cat'])) {
            throw new \Exception('Отсутствует категория модели #' . $modelID . '.');
        }
        $modelCatID = $rows[0]['cat'];
        /* Сопоставление полей в таблице тарифов СЦ и общей */
        $types = [
            1 => ['srv' => 'block', 'all' => 'block'],
            2 => ['srv' => 'component', 'all' => 'element'],
            3 => ['srv' => 'access', 'all' => 'acess'],
            4 => ['srv' => 'anrp', 'all' => 'anrp'],
            5 => ['srv' => 'ato', 'all' => 'ato']
        ];
        /* Индивидуальная цена для СЦ */
        $field = $types[$repairTypeID]['srv'];
        $price = self::$db->exec('SELECT `' . $field . '` FROM `prices_service` WHERE `cat_id` = ? AND `service_id` = ? LIMIT 1', [$modelCatID, $serviceID]);
        if (!empty($price[0][$field])) {
            return $price[0][$field];
        }
        /* Общая для всех цена */
        $field = $types[$repairTypeID]['all'];
        $price = self::$db->exec('SELECT `' . $field . '` FROM `' . \models\Tariffs::getServiceTariffTable($serviceID) . '` WHERE `cat_id` = ? LIMIT 1', [$modelCatID]);
        return (isset($price[0][$field])) ? $price[0][$field] : 0;
    }
}


Repair::init();
