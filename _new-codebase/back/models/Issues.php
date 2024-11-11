<?php

namespace models;

/* Фактическая неисправность */

class Issues extends _Model
{
    public static $message = '';
    private static $db = null;

    public static function init()
    {
        self::$db = _Base::getDB();
    }


    /* Список id => name */
    public static function getIssuesList()
    {
        return array_column(self::$db->exec('SELECT `id`, `name` FROM `issues` ORDER BY `name`'), 'name', 'id');
    }
}


Issues::init();
