<?php

namespace models;

use program\core;

class Plants extends _Model
{
    public static $message = '';
    private static $db = null;

    public static function init()
    {
        self::$db = _Base::getDB();
    }


    /* Список id => name */
    public static function getPlantsList()
    {
        return array_column(self::$db->exec('SELECT `id`, `name` FROM `plants` ORDER BY `name`'), 'name', 'id');
    }
}


Plants::init();
