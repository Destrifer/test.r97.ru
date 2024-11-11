<?php

namespace models\geo;

/** 
 * Страны
 */

class Countries extends \models\_Model
{

    const TABLE = 'countries';
    private static $db = null;


    public static function init()
    {
        self::$db = \models\_Base::getDB();
    }


    public static function getCountries(array $filter = [])
    {
        return self::$db->exec('SELECT s.* FROM `' . self::TABLE . '` s  
        ' . self::where($filter) . self::order($filter) . self::limit($filter));
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
        if (!empty($filter['name'])) {
            $where[] = 's.`name` = "' . trim($filter['name']) . '"';
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
}


Countries::init();
