<?php

namespace models\parts;


/* Приходы запчастей */

class Arrivals extends \models\_Model
{


    const TABLE_ARRIVALS = 'parts2_arrivals';
    const TABLE_PARTS = 'parts2_arrivals_parts';
    private static $db = null;
    private static $cache = [];


    public static function init()
    {
        self::$db = \models\_Base::getDB();
    }


    public static function save(array $rawData)
    {
        if (empty($rawData['arrival_name']) || empty($rawData['depot_id']) || empty($rawData['add_date']) || empty($rawData['part_id']) || empty($rawData['part_num'])) {
            return ['message' => 'Пожалуйста, заполните все поля.', 'is_error' => 1];
        }
        $arrivalID = self::getArrivalID($rawData['arrival_name']);
        if (!$arrivalID) {
            return ['message' => 'Ошибка приходов: ' . self::$db->getErrorInfo(), 'is_error' => 1];
        }
        $depotID = $rawData['depot_id'];
        $addDate = date('Y-m-d H:i:01', strtotime($rawData['add_date']));
        self::$db->transact('begin');
        foreach ($rawData['part_id'] as $n => $partID) {
            $num = (int)$rawData['part_num'][$n];
            if (empty($num)) {
                continue;
            }
            $r = self::$db->exec(
                'INSERT INTO `' . self::TABLE_PARTS . '` 
            (`part_id`, `part_num`, `arrival_id`, `depot_id`, `add_date`) VALUES (?, ?, ?, ?, ?)',
                [$partID, $num, $arrivalID, $depotID, $addDate]
            );
            if (!$r) {
                self::$db->transact('rollback');
                return ['message' => 'Ошибка прихода запчастей: ' . self::$db->getErrorInfo(), 'is_error' => 1];
            }
            $r = Log::add($partID, $depotID, $num, Balance::count($partID, $depotID), 0, $addDate);
            if (!$r) {
                self::$db->transact('rollback');
                return ['message' => 'Ошибка истории запчастей: ' . self::$db->getErrorInfo(), 'is_error' => 1];
            }
            $r = Balance::add($partID, $num, $depotID);
            if (!$r) {
                self::$db->transact('rollback');
                return ['message' => 'Ошибка баланса: ' . self::$db->getErrorInfo(), 'is_error' => 1];
            }
        }
        self::$db->transact('commit');
        return ['message' => 'Запчасти успешно добавлены.', 'is_error' => 0];
    }



    public static function getArrivalByID($arrivalID)
    {
        if (!isset(self::$cache[$arrivalID])) {
            $rows = self::$db->exec('SELECT * FROM `' . self::TABLE_ARRIVALS . '` WHERE `id` = ?', [$arrivalID]);
            self::$cache[$arrivalID] = ($rows) ? $rows[0] : [];
        }
        return self::$cache[$arrivalID];
    }


    public static function getList(array $filter = [])
    {
        return self::$db->exec('SELECT * FROM `' . self::TABLE_ARRIVALS . '` ORDER BY `name`');
    }


    public static function add(array $rawData)
    {
        $data = self::prepareData($rawData);
        $arrivalID = self::getArrivalID($data['name']);
        if (!$arrivalID) {
            return false;
        }
        return self::$db->exec(
            'INSERT INTO `' . self::TABLE_PARTS . '` 
            (`part_id`, `part_num`, `arrival_id`, `depot_id`, `add_date`) VALUES (?, ?, ?, ?, ?)',
            [$data['part_id'], $data['part_num'], $arrivalID, $data['depot_id'], $data['add_date']]
        );
    }


    public static function updatePartArrivalID($rowID, $arrivalName)
    {
        $arrivalName = trim($arrivalName);
        if (empty($arrivalName)) {
            return self::$db->exec('DELETE FROM `' . self::TABLE_PARTS . '` WHERE `id` = ?', [$rowID]);
        }
        $arrivalID = self::getArrivalID($arrivalName);
        if (!$arrivalID) {
            return false;
        }
        return self::$db->exec('UPDATE `' . self::TABLE_PARTS . '` SET `arrival_id` = ? WHERE `id` = ?', [$arrivalID, $rowID]);
    }


    public static function deletePart($rowID)
    {
        return self::$db->exec('DELETE FROM `' . self::TABLE_PARTS . '` WHERE `id` = ?', [$rowID]);
    }


    private static function getArrivalID($arrivalName)
    {
        $arrivalName = trim($arrivalName);
        $rows = self::$db->exec('SELECT `id` FROM `' . self::TABLE_ARRIVALS . '` WHERE `name` = ?', [$arrivalName]);
        if ($rows) {
            return $rows[0]['id'];
        }
        return self::$db->exec('INSERT INTO `' . self::TABLE_ARRIVALS . '` (`name`) VALUES (?)', [$arrivalName]);
    }


    public static function getPartInfo(array $filter)
    {
        $where = [];
        if (!empty($filter['arrival_id']) && is_numeric($filter['arrival_id'])) {
            $where[] = 'p.`arrival_id` = ' . $filter['arrival_id'];
        }
        if (!empty($filter['part_id'])) {
            $where[] = 'p.`part_id` = ' . $filter['part_id'];
        }
        if (!empty($filter['arrival_dates'])) {
            $where[] = 'p.`add_date` BETWEEN "' . $filter['arrival_dates']['from'] . '" AND "' . $filter['arrival_dates']['to'] . '"';
        }
        if (!$where) {
            return [];
        }
        return self::$db->exec('SELECT p.*, a.`name` AS arrival_name 
        FROM `' . self::TABLE_PARTS . '` p 
        LEFT JOIN `' . self::TABLE_ARRIVALS . '` a 
        ON p.`arrival_id` = a.`id` 
        WHERE ' . implode(' AND ', $where));
    }


    private static function prepareData(array $rawData, array $parts = [])
    {
        $data = [];
        $data['add_date'] = $rawData['add_date'];
        $data['depot_id'] = $rawData['depot_id'];
        $data['name'] = trim($rawData['name']);
        $data['part_id'] = $rawData['part_id'];
        $data['part_num'] = $rawData['part_num'];
        return $data;
    }
}

Arrivals::init();
