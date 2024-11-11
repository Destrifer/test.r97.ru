<?php

namespace models\parts;

use models\User;

class Log extends \models\_Model
{

    const TABLE = 'parts2_log';
    const ADD_EVENT = 11; // принята
    const GET_EVENT = 12; // принята со склада
    const COLLECT_EVENT = 13; // собрана с ремонта
    const RETURN_EVENT = 14; // возврат
    const REJECT_DELETE_REQUEST_EVENT = 15; // отменен запрос на утилизацию
    const USE_EVENT = 21; // забрана в ремонт
    const SHIP_EVENT = 22; // отправлена куда-либо
    const DELETE_EVENT = 23; // списана
    const DELETE_REQUEST_EVENT = 24; // запрос на списание
    const MOVE_EVENT = 25; // перемещена на другой склад
    private static $db = null;


    public static function init()
    {
        self::$db = \models\_Base::getDB();
    }


    /**
     * Отменяет операцию
     * 
     * @param int $id Операция
     * 
     * @return array Флаг ошибки и сообщение
     */
    public static function revert($id)
    {
        $rows = self::$db->exec('SELECT * FROM `' . self::TABLE . '` WHERE `id` = ?', [$id]);
        if (!$rows) {
            return ['message' => '', 'error_flag' => 0];
        }
        $log = $rows[0];
        if ($log['event_id'] >= 20) { // забрана со склада, значит - вернуть
            $r = Balance::add($log['part_id'], $log['num'], $log['depot_id']);
            $message = 'Было возвращено ' . $log['num'] . ' шт. на склад #' . $log['depot_id'];
        } else {
            $r = Balance::delete($log['part_id'], $log['num'], $log['depot_id'], $log['date']);
            $message = 'Было убрано ' . $log['num'] . ' шт. со склада #' . $log['depot_id'];
        }
        if (!$r) {
            return ['message' => Balance::$error, 'error_flag' => 1];
        }
        \models\Log::part(25, $message, $log['part_id']);
        self::$db->exec('DELETE FROM `' . self::TABLE . '` WHERE `id` = ?', [$id]);
        return ['message' => '', 'error_flag' => 0];
    }


    /**
     * Запчасть добавлена на склад
     * 
     * @param int $partID Запчасть
     * @param int $depotID Склад
     * @param int $num Кол-во
     * @param int $balanceBefore Кол-во до операции
     * @param int $invoiceID Накладная
     * 
     * @return void
     */
    public static function add($partID, $depotID, $num, $balanceBefore, $invoiceID = 0, $date = '')
    {
        $balanceBefore = (!empty($balanceBefore)) ? $balanceBefore : 0;
        $date = (!$date) ? date('Y-m-d H:i:s') : $date;
        return self::$db->exec(
            'INSERT INTO `' . self::TABLE . '` 
        (`event_id`, `part_id`, `depot_id`, `num`, `user_id`, `object_id`, `date`, `balance_before`) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            [self::ADD_EVENT, $partID, $depotID, $num, User::getData('id'), $invoiceID, $date, $balanceBefore]
        );
    }


    /**
     * Возврат ошибочно списанной запчасти
     * 
     * @param int $partID Запчасть
     * @param int $depotID Склад
     * @param int $num Кол-во
     * @param int $balanceBefore Кол-во до операции
     * @param int $repairID Ремонт
     * 
     * @return void
     */
    public static function return($partID, $depotID, $num, $balanceBefore, $repairID)
    {
        $balanceBefore = (!empty($balanceBefore)) ? $balanceBefore : 0;
        return self::$db->exec('INSERT INTO `' . self::TABLE . '` 
        (`event_id`, `part_id`, `depot_id`, `num`, `user_id`, `object_id`, `balance_before`) 
        VALUES 
        (' . self::RETURN_EVENT . ', ' . $partID . ', ' . $depotID . ', ' . $num . ', ' . User::getData('id') . ', ' . $repairID . ', ' . $balanceBefore . ')');
    }


    /**
     * Запчасть добавлена на склад во время ремонта (разбор и др.)
     * 
     * @param int $partID Запчасть
     * @param int $depotID Склад
     * @param int $num Кол-во
     * @param int $balanceBefore Кол-во до операции
     * @param int $repairID Ремонт
     * @param int $modelID Модель
     * @param string $serial Серийный номер
     * 
     * @return void
     */
    public static function collect($partID, $depotID, $num, $balanceBefore, $repairID, $modelID = 0, $serial = '')
    {
        $balanceBefore = (!empty($balanceBefore)) ? $balanceBefore : 0;
        return self::$db->exec('INSERT INTO `' . self::TABLE . '` 
        (`event_id`, `part_id`, `depot_id`, `num`, `user_id`, `object_id`, `object2_id`, `balance_before`, `serial`) 
        VALUES 
        (' . self::COLLECT_EVENT . ', ?, ?, ?, ?, ?, ?, ?, ?)', [$partID, $depotID, $num, User::getData('id'), $repairID, $modelID, $balanceBefore, trim($serial)]);
    }


    /**
     * Запчасть забрана со склада в ремонт
     * 
     * @param int $partID Запчасть
     * @param int $depotID Склад
     * @param int $num Кол-во
     * @param int $balanceBefore Кол-во до операции
     * @param int $repairID Ремонт
     * 
     * @return void
     */
    public static function take($partID, $depotID, $num, $balanceBefore, $repairID, $modelID = 0)
    {
        $balanceBefore = (!empty($balanceBefore)) ? $balanceBefore : 0;
        return self::$db->exec('INSERT INTO `' . self::TABLE . '` 
        (`event_id`, `part_id`, `depot_id`, `num`, `user_id`, `object_id`, `object2_id`, `balance_before`) 
        VALUES 
        (' . self::USE_EVENT . ', ?, ?, ?, ?, ?, ?, ?)', [$partID, $depotID, $num, User::getData('id'), $repairID, $modelID, $balanceBefore]);
    }


    /**
     * Отправить запчасти
     * 
     * @param int $partID Запчасть
     * @param int $depotFromID Склад-источник
     * @param int $num Кол-во
     * @param int $balanceBefore Кол-во до операции
     * @param string $serial Серийный номер
     * 
     * @return void
     */
    public static function ship($partID, $depotFromID, $num, $balanceBefore, $shipID, $modelID, $serial, $date = '')
    {
        $balanceBefore = (!empty($balanceBefore)) ? $balanceBefore : 0;
        return self::$db->exec('INSERT INTO `' . self::TABLE . '` 
        (`event_id`, `part_id`, `depot_id`, `num`, `user_id`, `object_id`, `object2_id`, `serial`, `date`, `balance_before`) 
        VALUES 
        (' . self::SHIP_EVENT . ', ' . $partID . ', ' . $depotFromID . ', ' . $num . ', ' . User::getData('id') . ', ' . $shipID . ', ' . $modelID . ', "' . $serial . '", "' . $date . '", ' . $balanceBefore . ')');
    }


    /**
     * Запчасть перемещена между складами
     * 
     * @param int $partID Запчасть
     * @param int $depotFromID Склад-источник
     * @param int $depotToID Склад назначения
     * @param int $qty Кол-во
     * @param int $balanceBefore Кол-во до операции
     * 
     * @return void
     */
    public static function move($partID, $depotFromID, $depotToID, $qty, $balanceBefore)
    {
        $balanceBefore = (!empty($balanceBefore)) ? $balanceBefore : 0;
        return self::$db->exec('INSERT INTO `' . self::TABLE . '` 
        (`event_id`, `part_id`, `depot_id`, `num`, `user_id`, `object_id`, `object2_id`, `date`, `serial`, `balance_before`) 
        VALUES 
        (' . self::MOVE_EVENT . ', ' . $partID . ', ' . $depotFromID . ', ' . $qty . ', '
            . User::getData('id') . ', ' . $depotToID . ', 0, "' . date('Y-m-d H:i:s') . '", "", '
            . $balanceBefore . ')');
    }


    /**
     * Запчасть списана
     * 
     * @param int $partID Запчасть
     * @param int $depotID Склад
     * @param int $num Кол-во
     * @param int $balanceBefore Кол-во до операции
     * 
     * @return void
     */
    public static function delete($partID, $depotID, $num, $balanceBefore, $reasonID = 0, $object2ID = 0, $date = '', $serial = '')
    {
        $balanceBefore = (!empty($balanceBefore)) ? $balanceBefore : 0;
        $date = (!$date) ? date('Y-m-d H:i:s') : $date;
        $object2ID = ($object2ID) ? $object2ID : 0;
        return self::$db->exec('INSERT INTO `' . self::TABLE . '` 
        (`event_id`, `part_id`, `depot_id`, `num`, `user_id`, `object_id`, `object2_id`, `date`, `serial`, `balance_before`) 
        VALUES 
        (' . self::DELETE_EVENT . ', ' . $partID . ', ' . $depotID . ', ' . $num . ', ' . User::getData('id') . ', ' . $reasonID . ', ' . $object2ID . ', "' . $date . '", "' . $serial . '", ' . $balanceBefore . ')');
    }


    /**
     * Запрос на списание запчасти
     * 
     * @param int $partID Запчасть
     * @param int $depotID Склад
     * @param int $num Кол-во
     * @param int $balanceBefore Кол-во до операции
     * @param int $requestID Запрос
     * 
     * @return void
     */
    public static function deleteRequest($partID, $depotID, $num, $balanceBefore, $requestID)
    {
        $balanceBefore = (!empty($balanceBefore)) ? $balanceBefore : 0;
        return self::$db->exec('INSERT INTO `' . self::TABLE . '` 
        (`event_id`, `part_id`, `depot_id`, `num`, `user_id`, `date`, `balance_before`, `object_id`) 
        VALUES 
        (' . self::DELETE_REQUEST_EVENT . ', ' . $partID . ', ' . $depotID . ', ' . $num . ', ' . User::getData('id') . ', "' . date('Y-m-d H:i:s') . '", ' . $balanceBefore . ', ' . $requestID . ')');
    }


    /**
     * Отменен запрос на списание запчасти
     * 
     * @param int $partID Запчасть
     * @param int $depotID Склад
     * @param int $num Кол-во
     * @param int $balanceBefore Кол-во до операции
     * @param int $requestID Запрос
     * 
     * @return void
     */
    public static function rejectDeleteRequest($partID, $depotID, $num, $balanceBefore, $requestID)
    {
        $balanceBefore = (!empty($balanceBefore)) ? $balanceBefore : 0;
        return self::$db->exec('INSERT INTO `' . self::TABLE . '` 
        (`event_id`, `part_id`, `depot_id`, `num`, `user_id`, `date`, `balance_before`, `object_id`) 
        VALUES 
        (' . self::REJECT_DELETE_REQUEST_EVENT . ', ' . $partID . ', ' . $depotID . ', ' . $num . ', ' . User::getData('id') . ', "' . date('Y-m-d H:i:s') . '", ' . $balanceBefore . ', ' . $requestID . ')');
    }
}

Log::init();
