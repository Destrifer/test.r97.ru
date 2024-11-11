<?php

namespace models;

/* Хранение ключ => значение в базе (JSON) */

class Storage extends _Model
{
    public static $message = '';
    private static $db = null;
    const TABLE = 'storage';


    public static function init()
    {
        self::$db = _Base::getDB();
    }


    public static function get($key)
    {
        $rows = self::$db->exec('SELECT `value` FROM `' . self::TABLE . '` WHERE `key` = ?', [$key]);
        if (empty($rows[0]['value'])) {
            return [];
        }
        return json_decode($rows[0]['value'], true);
    }


    public static function save($key, $value)
    {
        $rows = self::$db->exec('SELECT `id` FROM `' . self::TABLE . '` WHERE `key` = ?', [$key]);
        $d = (is_array($value)) ? json_encode($value) : trim($value);
        if ($rows) {
            return self::$db->exec('UPDATE `' . self::TABLE . '` SET `value` = ? WHERE `id` = ?', [$d, $rows[0]['id']]);
        }
        return self::$db->exec('INSERT INTO `' . self::TABLE . '` (`key`, `value`) VALUES (?, ?)', [$key, $value]);
    }
}


Storage::init();
