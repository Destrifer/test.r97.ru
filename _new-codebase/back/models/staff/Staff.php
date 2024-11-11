<?php

namespace models\staff;

use models\Users;
use program\core\Query;

/** 
 * Персонал
 */

class Staff extends \models\_Model
{

    private static $db = null;
    const TABLE = 'staff';
    private static $cache = [];


    public static function init()
    {
        self::$db = \models\_Base::getDB();
    }


    public static function getStaff(array $filter = [])
    {
        if (isset($filter['id']) && $filter['id'] <= 0) { // без мастера = -1
            return [];
        }
        $rows = self::$db->exec('SELECT s.* FROM `' . self::TABLE . '` s  
        ' . self::where($filter) . self::order($filter) . self::limit($filter));
        $rows = self::handleRows($rows);
        return (!$rows) ? [] : $rows[0];
    }


    public static function getStaffList(array $filter = [])
    {
        $result = [];
        $users = Users::getUsers($filter);
        for ($i = 0, $cnt = count($users); $i < $cnt; $i++) {
            $rows2 = self::$db->exec('SELECT * FROM `' . self::TABLE . '` WHERE `user_id` = ?', [$users[$i]['id']]);
            $rows2 = self::handleRows2($rows2);
            $staff = ($rows2) ? $rows2[0] : [];
            $staff['user'] = $users[$i];
            $result[] = $staff;
        }
        return $result;
    }


    public static function saveStaff(array $rawData)
    {
        $data = self::prepareData($rawData);
        $query = new Query(self::TABLE);
        if (!empty($rawData['id'])) {
            $staffID = $rawData['id'];
            self::$db->exec($query->update($data, $staffID), $query->params);
        } else {
            $staffID = self::$db->exec($query->insert($data), $query->params);
        }
        if (!$staffID) {
            return ['message' => 'Не удалось сохранить: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
        }
        return ['message' => 'Сотрудник успешно сохранен.', 'error_flag' => 0,];
    }


    private static function prepareData(array $rawData)
    {
        $data = [];
        $data['name'] = trim($rawData['name']);
        $data['surname'] = trim($rawData['surname']);
        $data['thirdname'] = trim($rawData['thirdname']);
        $data['salary'] = $rawData['salary'];
        $data['percent'] = $rawData['percent'];
        $data['user_id'] = $rawData['user_id'];
        return $data;
    }


    public static function handleRows2(array $rows)
    {
        for ($i = 0, $cnt = count($rows); $i < $cnt; $i++) {
            $rows[$i]['full_name'] = trim($rows[$i]['surname'] . ' ' . $rows[$i]['name'] . ' ' . $rows[$i]['thirdname']);
        }
        return $rows;
    }


    public static function handleRows(array $rows)
    {
        for ($i = 0, $cnt = count($rows); $i < $cnt; $i++) {
            $rows[$i]['full_name'] = trim($rows[$i]['surname'] . ' ' . $rows[$i]['name'] . ' ' . $rows[$i]['thirdname']);
            $rows[$i]['user'] = Users::getUser(['id' => $rows[$i]['user_id']]);
            $rows[$i]['is_active'] = $rows[$i]['user']['is_active'];
        }
        return $rows;
    }


    private static function order(array $filter)
    {
        $sort = ' ORDER BY s.`name`';
        if (!empty($filter['dir']) && $filter['dir'] != 'asc') {
            $sort .= ' DESC';
        }
        return $sort;
    }


    private static function where(array $filter)
    {
        $where = [];
        if (!empty($filter['id'])) {
            $where[] = 's.`id` = ' . $filter['id'];
        }
        if (!empty($filter['user_id'])) {
            $where[] = 's.`user_id` = ' . $filter['user_id'];
        }
        if (!empty($filter['search'])) {
            $where[] = 's.`name` LIKE "%' . trim($filter['search']) . '%"';
        }
        if ($where) {
            return ' WHERE ' . implode(' AND ', $where);
        }
        return '';
    }


    private static function limit(array $filter)
    {
        if (empty($filter['limit'])) {
            return '';
        }
        if (empty($filter['offset'])) {
            return ' LIMIT 0, ' . $filter['limit'];
        }
        return ' LIMIT ' . $filter['offset'] . ', ' . $filter['limit'];
    }


    public static function getWorkCost($masterID, $totalPrice, $formatFn = null)
    {
        if (empty($masterID) || empty($totalPrice)) {
            return 0;
        }
        if (!$formatFn) {
            $formatFn = function ($val) {
                return number_format($val, 2, ',', ' ');
            };
        }
        if (!isset(self::$cache[$masterID]['percent'])) {
            $rows = self::$db->exec('SELECT `percent` FROM `' . self::TABLE . '` WHERE `id` = ?', [$masterID]);
            if (!$rows) {
                self::$cache[$masterID]['percent'] = '';
                return 0;
            }
            self::$cache[$masterID] = ['percent' => $rows[0]['percent']];
        }
        if (!self::$cache[$masterID]['percent']) {
            return 0;
        }
        $p = round(((float)$totalPrice / 100 * (float)self::$cache[$masterID]['percent']), 2);
        return $formatFn($p);
    }


    public static function getMastersList($serviceID = 0)
    {
        $users = Users::getUsers(['service_id' => $serviceID, 'role_id' => [4]]);
        return array_column($users, 'full_name', 'id');
    }


    public static function getMasters($serviceID = 0)
    {
        $filter = ['service_id' => $serviceID, 'role_id' => [4]];
        $rows = self::$db->exec('SELECT s.* FROM `' . self::TABLE . '` s  
        ' . self::where($filter) . self::order($filter) . self::limit($filter));
        $rows = self::handleRows($rows);
        return $rows;
    }
}

Staff::init();
