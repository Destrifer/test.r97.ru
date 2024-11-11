<?php

namespace models\parts;

use models\geo\Countries;
use models\Services;
use program\core\RowSet;

class Depots extends \models\_Model
{

    const TABLE = 'parts2_depots';
    const MAIN_DEPOT_ID = 1;
    private static $db = null;


    public static function init()
    {
        self::$db = \models\_Base::getDB();
    }


    public static function getDepots(array $filter = [])
    {
        $where = self::getWhere($filter);
        $rows = self::$db->exec('SELECT * FROM `' . self::TABLE . '` ' . $where . ' ORDER BY `id`');
        return self::handeRows($rows);
    }


    private static function handeRows(array $rows)
    {
        $services = RowSet::orderBy('user_id', Services::getAllServices());
        $countries = RowSet::orderBy('id', Countries::getCountries());
        for ($i = 0, $cnt = count($rows); $i < $cnt; $i++) {
            $rows[$i]['country'] = '';
            if ($rows[$i]['id'] != self::MAIN_DEPOT_ID) {
                if (!isset($services[$rows[$i]['user_id']])) {
                    continue;
                }
                $service = $services[$rows[$i]['user_id']];
                $rows[$i]['name'] = $service['name'];
                $rows[$i]['country'] = (isset($countries[$service['country']])) ? $countries[$service['country']]['name'] : '';
            }
        }
        return $rows;
    }


    private static function getWhere(array $filter)
    {
        $where = [];
        if (!empty($filter['id'])) {
            if (is_array($filter['id'])) {
                $where[] = '`id` IN (' . implode(',', $filter['id']) . ')';
            } else {
                $where[] = '`id` = ' . $filter['id'];
            }
        }
        if (!empty($filter['name'])) {
            $where[] = '`name` = "' . trim($filter['name']) . '"';
        }
        if (!empty($filter['country_id'])) {
            $where[] = '`user_id` IN (SELECT `user_id` FROM `' . Services::TABLE . '` WHERE `country` = ' . $filter['country_id'] . ')';
        }
        if (!empty($filter['user_id'])) {
            if (is_array($filter['user_id'])) {
                $where[] = '`user_id` IN (' . implode(',', $filter['user_id']) . ')';
            } else {
                $where[] = '`user_id` = ' . $filter['user_id'];
            }
        }
        if ($where) {
            return 'WHERE ' . implode(' AND ', $where);
        }
        return '';
    }


    public static function getDepot(array $filter)
    {
        $where = self::getWhere($filter);
        if (!$where) {
            return [];
        }
        $rows = self::$db->exec('SELECT * FROM `' . self::TABLE . '` ' . $where);
        if (!$rows) {
            return [];
        }
        $rows = self::handeRows($rows);
        return $rows[0];
    }


    public static function getOrCreateDepot($userID)
    {
        $depot = self::getDepot(['user_id' => $userID]);
        if ($depot) {
            return $depot;
        }
        return self::addDepot(['name' => 'Разбор', 'user_id' => $userID]);
    }


    public static function addDepot(array $data)
    {
        $id = self::$db->exec('INSERT INTO `' . self::TABLE . '` (`user_id`, `name`) VALUES (?, ?)', [$data['user_id'], $data['name']]);
        if (!$id) {
            return [];
        }
        return self::getDepot(['id' => $id]);
    }
}

Depots::init();
