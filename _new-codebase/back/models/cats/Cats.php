<?php

namespace models\cats;

/** 
 * Категории моделей техники
 */

class Cats extends \models\_Model
{

    private static $db = null;
    const TABLE = 'cats';


    public static function init()
    {
        self::$db = \models\_Base::getDB();
    }


    public static function getCats(array $filter = [])
    {
        $rows = self::$db->exec('SELECT c.* FROM `' . self::TABLE . '` c  
        ' . self::getWhere($filter) . self::getSort($filter) . self::getLimit($filter));
        for ($i = 0, $cnt = count($rows); $i < $cnt; $i++) {
            $rows[$i]['service_flag'] = $rows[$i]['service'];
            $rows[$i]['outside_flag'] = $rows[$i]['travel'];
        }
        return $rows;
    }


    public static function getCatsList(array $filter = [])
    {
        $rows = self::$db->exec('SELECT c.* FROM `' . self::TABLE . '` c  
        ' . self::getWhere($filter) . self::getSort($filter) . self::getLimit($filter));
        return array_column($rows, 'name', 'id');
    }


    public static function count(array $filter = [])
    {
        $rows = self::$db->exec('SELECT COUNT(*) AS cnt     
        FROM `' . self::TABLE . '` c     
        ' . self::getWhere($filter));
        return ($rows) ? $rows[0]['cnt'] : 0;
    }


    private static function getSort(array $filter)
    {
        $sort = ' ORDER BY c.`name`';
        if (!empty($filter['dir']) && $filter['dir'] != 'asc') {
            $sort .= ' DESC';
        }
        return $sort;
    }


    private static function getWhere(array $filter)
    {
        $where = [];
        if (!empty($filter['id'])) {
            $where[] = 'c.`id` = ' . $filter['id'];
        }
        if (!empty($filter['search'])) {
            $where[] = 'c.`name` LIKE "%' . trim($filter['search']) . '%"';
        }
        if (isset($filter['install_flag'])) {
            $where[] = 'c.`install_flag` = ' . $filter['install_flag'];
        }
        if ($where) {
            return ' WHERE ' . implode(' AND ', $where);
        }
        return '';
    }


    private static function getLimit(array $filter)
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


Cats::init();
