<?php

namespace models;

/* Хранение ключ => значение в базе */

class Settings extends _Model
{
    public static $message = '';
    private static $db = null;


    public static function init()
    {
        self::$db = _Base::getDB();
    }


    public static function get($uri)
    {
        $rows = self::$db->exec('SELECT `value` FROM `settings2` WHERE `uri` = ?', [$uri]);
        if (!$rows) {
            return '';
        }
        return $rows[0]['value'];
    }


    public static function save($uri, $value, $name = '')
    {
        if ($name) {
            self::$db->exec('INSERT INTO `settings2` (`uri`, `value`, `name`) VALUES(?, ?, ?) ON DUPLICATE KEY UPDATE    
            `value` = VALUES(value), `name` = VALUES(name)', [trim($uri), trim($value), trim($name)]);
            return;
        }
        self::$db->exec('INSERT INTO `settings2` (`uri`, `value`) VALUES(?, ?) ON DUPLICATE KEY UPDATE    
        `value` = VALUES(value)', [trim($uri), trim($value)]);
    }
}


Settings::init();
