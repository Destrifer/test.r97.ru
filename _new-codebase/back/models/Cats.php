<?php

namespace models;

use program\core;


class Cats extends _Model
{
    public static $message = '';
    private static $db = null;
    const TABLE = 'cats';

    public static function init()
    {
        self::$db = _Base::getDB();
    }


    public static function getCatByModelID($modelID)
    {
        $rows = self::$db->exec('SELECT * FROM `cats` WHERE `id` = 
        (SELECT `cat` FROM `models` WHERE `id` = ' . $modelID . ')');
        if ($rows) {
            return $rows[0];
        }
        return [];
    }
}


Cats::init();
