<?php

namespace models\repaircard;

use models\Repair;

class Photos extends \models\_Model
{
    public static $types = [
        1 => 'Фото шильдика с серийным номером',
        8 => 'Фото заявления об отказе в гарантийном ремонте',
        7 => 'Фото гарантийного талона',
        6 => 'Фото кассового чека с наименованием модели',
        4 => 'Фото наименования основной платы',
        5 => 'Фото общего вида основной платы с разъёмами',
        11 => 'Фото собственной запчасти',
        12 => 'Фото чека на покупку запчасти',
        3 => 'Фото дефекта',
        2 => 'Фото шильдика на корпусе LCD',
        9 => 'Фото шильдика на плате LCD №1',
        10 => 'Фото шильдика на плате LCD №2'
    ];
    private static $db = null;

    
    public static function init()
    {
        self::$db = \models\_Base::getDB();
    }


    public static function getPhotosTypes()
    {
        return self::$types;
    }
}

Photos::init();
