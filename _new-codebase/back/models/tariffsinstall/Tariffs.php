<?php

namespace models\tariffsinstall;

use program\core\Query;

/** 
 * Тарифы на монтаж/демонтаж техники
 */

class Tariffs extends \models\_Model
{

    private static $db = null;
    const TABLE = 'tariffs_install';


    public static function init()
    {
        self::$db = \models\_Base::getDB();
    }


    public static function getDismantCost($catID)
    {
        $rows = self::$db->exec('SELECT `dismant_cost` FROM `' . self::TABLE . '` WHERE `cat_id` = ' . $catID);
        return ($rows) ? $rows[0]['dismant_cost'] : 0;
    }


    public static function getInstallCost($catID)
    {
        $rows = self::$db->exec('SELECT `install_cost` FROM `' . self::TABLE . '` WHERE `cat_id` = ' . $catID);
        return ($rows) ? $rows[0]['install_cost'] : 0;
    }


    public static function getCosts(array $filter = [])
    {
        $rows = self::$db->exec('SELECT t.* FROM `' . self::TABLE . '` t  
        ' . self::getWhere($filter));
        return $rows;
    }


    public static function create(array $rawData)
    {
        $query = new Query(self::TABLE);
        $data = self::prepareData($rawData);
        return self::$db->exec($query->insert($data), $query->params);
    }


    public static function update(array $rawData, $catID)
    {
        $f = [];
        if (isset($rawData['install_cost'])) {
            $f[] = '`install_cost` = ' . $rawData['install_cost'];
        }
        if (isset($rawData['dismant_cost'])) {
            $f[] = '`dismant_cost` = ' . $rawData['dismant_cost'];
        }
        return self::$db->exec('UPDATE `' . self::TABLE . '` SET ' . implode(',', $f) . ' WHERE `cat_id` = ?', [$catID]);
    }


    private static function prepareData(array $rawData)
    {
        $data = [];
        $data['cat_id'] = $rawData['cat_id'];
        $data['install_cost'] = (isset($rawData['install_cost'])) ? $rawData['install_cost'] : 0;
        $data['dismant_cost'] = (isset($rawData['dismant_cost'])) ? $rawData['dismant_cost'] : 0;
        return $data;
    }


    private static function getWhere(array $filter)
    {
        $where = [];
        if (!empty($filter['cat_id'])) {
            $where[] = 't.`cat_id` = ' . $filter['cat_id'];
        } else if (!empty($filter['cat_ids'])) {
            $where[] = 't.`cat_id` IN (' . implode(',', $filter['cat_ids']) . ')';
        }
        if ($where) {
            return ' WHERE ' . implode(' AND ', $where);
        }
        return '';
    }
}


Tariffs::init();
