<?php

namespace models;

class Providers extends _Model
{
    
    const TABLE = 'providers';
    public static $message = '';
    private static $db = null;
    private static $cache = ['providers' => []];


    public static function init()
    {
        self::$db = _Base::getDB();
    }


    public static function getProvider($id, $name = '')
    {
        if (!$id && !$name) {
            return [];
        }
        $k = $id . $name;
        if (!isset(self::$cache['providers'][$k])) {
            if ($name) {
                $rows = self::$db->exec('SELECT * FROM `'.self::TABLE.'` WHERE `name` = ?', [trim($name)]);
            } else {
                $rows = self::$db->exec('SELECT * FROM `'.self::TABLE.'` WHERE `id` = ?', [$id]);
            }
            self::$cache['providers'][$k] = (!$rows) ? [] : $rows[0];
        }
        return self::$cache['providers'][$k];
    }


    public static function getProviders()
    {
        return self::$db->exec('SELECT `id`, `name` FROM `'.self::TABLE.'` ORDER BY `name`');
    }


    /* Список id => name */
    public static function getProvidersList($modelID = 0)
    {
        if ($modelID) {
            $rows = self::$db->exec('SELECT `id`, `name` FROM `'.self::TABLE.'` 
            WHERE `id` IN (SELECT `provider_id` FROM `serials` WHERE `model_id` = ?) ORDER BY `name`', [$modelID]);
            return array_column($rows, 'name', 'id');
        }
        return array_column(self::$db->exec('SELECT `id`, `name` FROM `'.self::TABLE.'` ORDER BY `name`'), 'name', 'id');
    }
}


Providers::init();
