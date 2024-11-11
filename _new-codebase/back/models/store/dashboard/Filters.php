<?php

namespace models\store\dashboard;

use program\core\App;

/** 
 * Фильтры дашборда
 */

class Filters extends \models\_Model
{

    private static $db = null;
    private static $filters = [
        'myNum' => [
            'name' => 'Номер',
            'type' => 'number'
        ],
        'myDate' => [
            'name' => 'Дата',
            'type' => 'date'
        ],
        'mySelect' => [
            'name' => 'Селект',
            'type' => 'select'
        ]
    ];


    public static function init()
    {
        self::$db = \models\_Base::getDB();
    }


    public static function getFilters()
    {
        return self::$filters;
    }
}

Filters::init();
