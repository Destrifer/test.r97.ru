<?php

namespace models;

use program\core;

/** 
 * v. 0.1
 * 2021-07-19
 */

class Clients extends _Model
{
    public static $message = '';
    public static $scenario = [
        1 => 'Тест-ремонт-обратно клиенту (Если нет АНРП)',
        2 => 'Тест-ремонт-на уценку',
        3 => 'Смотрим вложенные документы'
    ];
    private static $db = null;


    public static function init()
    {
        self::$db = _Base::getDB();
    }

    public static function getClientByID($id)
    {
        if (!$id) {
            return [];
        }
        $rows = self::$db->exec('SELECT * FROM `clients` WHERE `id` = ' . $id);
        if (!$rows) {
            return [];
        }
        return $rows[0];
    }
}


Clients::init();
