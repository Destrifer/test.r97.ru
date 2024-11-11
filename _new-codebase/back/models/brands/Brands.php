<?php

namespace models\brands;

use models\Cats;
use models\Models;

/** 
 * Бренды
 */

class Brands extends \models\_Model
{

    private static $db = null;
    const TABLE = 'brands';


    public static function init()
    {
        self::$db = \models\_Base::getDB();
    }


    public static function enable($brandID)
    {
        $brand = self::getBrand(['id' => $brandID]);
        if (!$brand) {
            return false;
        }
        self::$db->exec('UPDATE `' . self::TABLE . '` SET `is_deleted` = 0 WHERE `id` = ' . $brandID);  
        self::$db->exec('UPDATE `' . Models::TABLE . '` SET `is_deleted` = 0 WHERE `brand` = ?', [$brand['name']]);  
        self::$db->exec('UPDATE `' . Cats::TABLE . '` SET `is_deleted` = 0 WHERE `brand_id` = ?', [$brand['id']]);
        return true;
    }


    public static function disable($brandID)
    {
        $brand = self::getBrand(['id' => $brandID]);
        if (!$brand) {
            return false;
        }
        self::$db->exec('UPDATE `' . self::TABLE . '` SET `is_deleted` = 1 WHERE `id` = ' . $brandID);  
        self::$db->exec('UPDATE `' . Models::TABLE . '` SET `is_deleted` = 1 WHERE `brand` = ?', [$brand['name']]);
        self::$db->exec('UPDATE `' . Cats::TABLE . '` SET `is_deleted` = 1 WHERE `brand_id` = ?', [$brand['id']]);
        return true;
    }


    public static function getBrand(array $filter = [])
    {
        $rows = self::$db->exec('SELECT b.* FROM `' . self::TABLE . '` b  
        ' . self::getWhere($filter) . self::getSort($filter) . ' LIMIT 1');
        if (!$rows) {
            return $rows;
        }
        return $rows[0];
    }


    public static function getBrands(array $filter = [])
    {
        $rows = self::$db->exec('SELECT b.* FROM `' . self::TABLE . '` b  
        ' . self::getWhere($filter) . self::getSort($filter) . self::getLimit($filter));
        return $rows;
    }


    public static function count(array $filter = [])
    {
        $rows = self::$db->exec('SELECT COUNT(*) AS cnt     
        FROM `' . self::TABLE . '` b     
        ' . self::getWhere($filter));
        return ($rows) ? $rows[0]['cnt'] : 0;
    }


    private static function getSort(array $filter)
    {
        $sort = ' ORDER BY b.`name`';
        if (!empty($filter['dir']) && $filter['dir'] != 'asc') {
            $sort .= ' DESC';
        }
        return $sort;
    }


    private static function getWhere(array $filter)
    {
        $where = [];
        if (!empty($filter['id'])) {
            $where[] = 'b.`id` = ' . $filter['id'];
        }
        if (!empty($filter['name'])) {
            $where[] = 'b.`name` LIKE "%' . trim($filter['name']) . '%"';
        }
        if (!empty($filter['search'])) {
            $where[] = 'b.`name` LIKE "%' . trim($filter['search']) . '%"';
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


Brands::init();
