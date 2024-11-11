<?php

namespace models;

use program\core;

/** 
 * v. 0.1
 * 2021-08-16
 */

class Services extends _Model
{
    public static $message = '';
    private static $db = null;
    const TABLE = 'requests';
    private static $cache = [];


    public static function init()
    {
        self::$db = _Base::getDB();
    }


    public static function getServiceUserIDByID($serviceID)
    {
        $rows = self::$db->exec('SELECT `user_id` FROM `requests` WHERE `id` = ?', [$serviceID]);
        if ($rows) {
            return $rows[0]['user_id'];
        }
        return 0;
    }


    public static function getServiceByID($serviceID)
    {
        if (isset(self::$cache[$serviceID])) {
            return self::$cache[$serviceID];
        }
        $rows = self::$db->exec('SELECT * FROM `requests` WHERE `user_id` = ?', [$serviceID]);
        self::$cache[$serviceID] = ($rows) ? $rows[0] : [];
        return self::$cache[$serviceID];
    }


    public static function getAllServices(array $filter = [])
    {
        $where = (!empty($filter['is_active'])) ? ' WHERE `mod` = 1' : '';
        return self::$db->exec('SELECT `id`, `name`, `country`, `user_id` FROM `requests` ' . $where . ' ORDER BY `name`');
    }


    /* Список id => name */
    public static function getServices(array $filter = [])
    {
        $where = (!empty($filter['is_active'])) ? ' WHERE `mod` = 1' : '';
        return array_column(self::$db->exec('SELECT `id`, `name` FROM `requests` ' . $where . ' ORDER BY `name`'), 'name', 'id');
    }


    /* Список user_id => name */
    public static function getServicesList()
    {
        return array_column(self::$db->exec('SELECT `user_id` AS id, `name` FROM `requests` WHERE `mod` = 1 ORDER BY `name`'), 'name', 'id');
    }


    public static function setServiceTariff($serviceID, $tariffID)
    {
        return self::$db->exec('UPDATE `requests` SET `tariff_id` = ? WHERE `user_id` = ?', [$tariffID, $serviceID]);
    }
}


Services::init();
