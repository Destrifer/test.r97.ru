<?php

namespace models\disposals;

use program\core\Query;

/** 
 * Запчасти на утилизацию
 */

class Parts extends \models\_Model
{

    private static $db = null;
    const TABLE = 'disposals_parts';


    public static function init()
    {
        self::$db = \models\_Base::getDB();
    }


    public static function getParts(array $filter = [])
    {
        $rows = self::$db->exec('SELECT c.* FROM `' . self::TABLE . '` c  
        ' . self::getWhere($filter) . self::getSort($filter) . self::getLimit($filter));
        for ($i = 0, $cnt = count($rows); $i < $cnt; $i++) {
            $rows[$i]['part'] = \models\Parts::getPartByID2($rows[$i]['part_id']);
            $rows[$i]['approve_disposal_flag'] = ($rows[$i]['comment']) ? false : true;
        }
        return $rows;
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
        $sort = ' ORDER BY c.`id`';
        if (!empty($filter['dir']) && $filter['dir'] != 'asc') {
            $sort .= ' DESC';
        }
        return $sort;
    }


    private static function getWhere(array $filter)
    {
        $where = [];
        if (!empty($filter['request_id'])) {
            $where[] = 'c.`request_id` = ' . $filter['request_id'];
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
            return ' LIMIT ' . $filter['limit'];
        }
        return ' LIMIT ' . $filter['offset'] . ', ' . $filter['limit'];
    }


    public static function create(array $data)
    {
        $query = new Query(self::TABLE);
        return self::$db->exec($query->insert($data), $query->params);
    }


    public static function update(array $data, $rowID)
    {
        $query = new Query(self::TABLE);
        return self::$db->exec($query->update($data, $rowID), $query->params);
    }
}


Parts::init();
